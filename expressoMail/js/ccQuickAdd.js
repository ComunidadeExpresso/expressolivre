/* Build the Object */
var	ccQuickAddOne = {
	send : function(data){
		var email = data[4];
		var handler = function (responseText)
		{
			var data = responseText;
			if (!data || typeof(data) != 'object'){
				write_msg("Problema ao contactar servidor");
				return;
			}else if (data['status'] == 'alreadyExists'){
				alert(data['msg']);
				return;
			}else if (data['status'] != 'ok'){
				return;
			}
			
			var exist = 0;
			$.each(dynamicContacts, function(x, valuex){
				if(valuex.mail == email){
					exist = valuex.id;
					return false;
				}
			});
			if(exist){
				REST['delete']("/dynamiccontact/"+exist);
				write_msg(get_lang("The contact was moved from recent contacts to personal contacts successful."));
			}else{
				write_msg(get_lang("Contact added successfully."));
			}			
		};
		
		var data2 = new Array();
		for( var i in data )
			data2[ data2.length ] = data[i];
		
		var sdata	= 'add='+escape(connector.serialize(data2));
		var CC_url	= '../index.php?menuaction=contactcenter.ui_data.data_manager&method=';
		connector.newRequest('cQuickAdd.Send', CC_url+'quick_add', 'POST', handler, sdata);
		updateDynamicPersonalContacts();
		cache = new Array();
		return true;
	},
	showList: function(data){
		var	cc_data = ((typeof data) == 'object' ) ? data : data.split(',');
		quickContact = $("#quickAddContact").html( DataLayer.render( BASE_PATH + "modules/mail/templates/quickAddContact.ejs",{ nick : cc_data[0], fname : cc_data[1], lname : cc_data[2], email : cc_data[3] }));
		quickContact.dialog({
			resizable	: false,
			title		: get_lang("Quick Add"),
			modal		: true,
			buttons		: [
				{
					text: get_lang("Cancel"),
					click: function()
					{
						$(this).dialog("close");
					} 
				},
				{
					text: get_lang("Save"),
					click: function()
					{
						data = {
							nick: $(this).find('#quickAddOne_nickName').val(),
							name:  $(this).find('#quickAddOne_firstName').val(),
							last: $(this).find('#quickAddOne_lastName').val(),
							telefone: "",
							email: $(this).find('#quickAddOne_email').val()
						};
						$(this).find('input').removeClass("required-fail");
						if($.trim(data.name) ==""){
							$(this).find('#quickAddOne_firstName').addClass("required-fail");
							return false;
						}else if(data.email ==""){
							$(this).find('#quickAddOne_email').addClass("required-fail");
							return false;
						}else if(!validateEmail(data.email)){
							$(this).find('#quickAddOne_email').addClass("required-fail");
							new $.Zebra_Dialog(get_lang("QuickAddInvalidMail", data.email),{
								'buttons':  false,
								'modal': false,
								'position': ['right - 20', 'top + 20'],
								'auto_close': 3000,
								'custom_class': 'custom-zebra-filter'
							});
							return false;
						}
						if(ccQuickAddOne.send(data))
							$(this).dialog("close");
					}
				}
			],
            open: function(event, ui) 
            {
                if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
            },
            close: function(event, ui) 
            {
                if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
            }
		});
		quickContact.next().css("background-color", "#E0EEEE").find("button").addClass("button").addClass("small");
	}
};
