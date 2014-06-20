function cShareMailbox()
{
	this.users = new Array();
}

cShareMailbox.prototype.get_available_users = function(context)
{
	var handler_get_available_users = function(data)
	{
		select_available_users = document.getElementById('em_select_available_users');
	
		//Limpa o select
		for(var i=0; i<select_available_users.options.length; i++)
		{
			select_available_users.options[i] = null;
			i--;
		}

		if ((data) && (data.length > 0))
		{
			// Necessario, pois o IE6 tem um bug que retira o primeiro options se o innerHTML estiver vazio.
			//select_available_users.innerHTML = '#' + data;
			select_available_users.outerHTML = select_available_users.outerHTML;
		
			select_available_users.disabled = false;
			select_available_users_clone = document.getElementById('em_select_available_users').cloneNode(true);
			document.getElementById('em_input_searchUser').value = '';
		}
	}
	cExecute ("$this.ldap_functions.get_available_users2&context="+context, handler_get_available_users);
}

cShareMailbox.prototype.getaclfromuser = function(user)
{
	$("input[type=checkbox][id^=em_input]").each(function()
	{
		$(this).attr("checked",false);
		if( $.trim( $(this).attr("id") ) == "em_input_deleteAcl" ) { $(this).attr( "disabled", true ); }
		if( $.trim( $(this).attr("id") ) == "em_input_writeAcl" ) { $(this).attr( "disabled", true ); }	
	});

	if ( (this.users[user].acls.indexOf('l',0) >= 0) &&
			(this.users[user].acls.indexOf('r',0) >= 0) &&
				(this.users[user].acls.indexOf('s',0) >= 0) )
	{
		$("#em_input_sendAcl").attr('disabled',false );
		$("#em_input_deleteAcl").attr('disabled', false );
        $("#em_input_writeAcl").attr('disabled', false );
		$("#em_input_readAcl").attr('checked', true );
	}
	else
	{
		$("#em_input_sendAcl").attr('disabled', true );
	}
			
	if ( (this.users[user].acls.indexOf('x',0) >= 0) &&
			(this.users[user].acls.indexOf('t',0) >= 0) &&
				(this.users[user].acls.indexOf('e',0) >= 0) )
	{
		$("#em_input_deleteAcl").attr('checked', true);
		$("#em_input_deleteAcl").attr('disabled', false);
	}
	
	if ( (this.users[user].acls.indexOf('w',0) >= 0) &&
			(this.users[user].acls.indexOf('i',0) >= 0) &&
				(this.users[user].acls.indexOf('k',0) >= 0) )
	{
		$("#em_input_writeAcl").attr('checked', true);
        $("#em_input_writeAcl").attr('disabled', false);
	}
	
	if( $.trim( this.users[user].acls ) != "" )
	{	
		if ( this.users[user].acls.indexOf('p',0) >= 0 && this.users[user].acls.indexOf('a',0) >= 0 )
		{
			$("#em_input_sendAcl").attr('disabled', false );
			$("#em_input_sendAcl").attr('checked', true );
		}
	}			
}

cShareMailbox.prototype.setaclfromuser = function()
{
	if( $("#em_select_sharefolders_users option:selected").val() )
	{
		 var user = $("#em_select_sharefolders_users option:selected").val();
		
		if ( $('#em_input_readAcl').is(":checked" ) )
		{
			$("#em_input_sendAcl").attr("disabled", false );
			$("#em_input_deleteAcl").attr("disabled", false );
            $("#em_input_writeAcl").attr("disabled", false );
			
			this.users[user].acls = "lrs";
		}
		else
		{
			$("input[type=checkbox][id^=em_input]").each(function()
			{
				if( $.trim( $(this).attr('id') ) != "em_input_readAcl" )
				{
					$(this).attr('disabled', true );
					$(this).attr('checked', false );
            	}
            });
		}
				
		if ( $("#em_input_deleteAcl").is(":checked") ){ this.users[user].acls += "xtea"; }

		if ( $("#em_input_writeAcl").is(":checked") ){ this.users[user].acls += "wika"; }		
		
		if ( $("#em_input_sendAcl").is(":checked") ){ this.users[user].acls += "pa"; }			
	}
	else
	{
		$.Zebra_Dialog(get_lang('Select a user!'), {
		        'type'				: 'warning',
		        'overlay_opacity'	: '0.5',
				'custom_class'		: 'custom-zebra-filter',
		        'buttons'			: [ get_lang('Close') ]
		});				

		return false;
	}	
}

cShareMailbox.prototype.makeWindow =  function(optionsData)
{
	var windowShare = $("#shareMailbox");

	windowShare.dialog(
	{
			resizable	: false,
			title		: get_lang("Mailbox Sharing"),
			position	: 'center',
			width		: 750,
			height		: 390,
			modal		: true,
			buttons		: [
							{
								text: get_lang("Close"),
								click: function()
								{
									$(this).dialog("destroy");
								},
								style: "margin-top: -2.1em" 
							},
							{
								text: get_lang("Save"),
								click: function()
								{									
									var _this = this;

									$.ajax({
											  url: 'controller.php?' + $.param( {
											  					  action: '$this.imap_functions.setacl',
															      acls: connector.serialize(sharemailbox.users)} 
															      ),
											  success: function( data )
											  {
											    data = connector.unserialize( data );
											      
											    if( data )
												{
												  	write_msg(get_lang('Shared options saved with success'));
												  	$(_this).dialog("destroy");
												}
											  },
											  beforeSend: function( jqXHR, settings ){
											  	connector.showProgressBar();
											  },
											  complete: function( jqXHR, settings ){
											  	connector.hideProgressBar();
											  }
										   });

								},
								style: "margin-top: -2.1em" 
							}
			],
            close:function(event, ui) 
            {
                if( typeof(shortcut) != 'undefined' ) shortcut.disabled = false; 
                $(this).dialog("destroy");

            },
            open: function(event, ui) 
            {
                if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 

				$("#em_input_sendAcl").attr('checked', false);
				$("#em_input_deleteAcl").attr('checked', false);
				$("#em_input_writeAcl").attr('checked', false);
            }
	});

	windowShare.html( DataLayer.render("../prototype/modules/mail/templates/shareMailbox.ejs",{} ) );

	$("#divAccessRight input[type=checkbox]").each(function()
	{
		$(this).parent().tooltip();
	});

	$(".alertSharedFolders").css({
			'position'		:	'absolute',
			'top'			: 	'305px', 
			'float'			:	'right',
			'display'		:	'block',
			'padding'		:	'10px',
			'border'		:	'1px solid #333',
			'clear'			:	'both',
			'color'			:	'red',
			'border-color'	:	'#FFDC73',
			'background'	:	'#FFFFBF'
	});

	for( var i in optionsData )
	{
		$("#em_select_sharefolders_users").append( new Option( optionsData[i].cn, i ) );
		this.users[ i ] = { 'uid' : i , 'acls' : optionsData[i].acls };
	}

	var handlerOrganizations = function(data)
	{
		var userOrganization = $('#user_organization').val();
		
		for( i = 0; i < data.length; i++ )
		{
			$('#em_combo_org').append( new Option( data[i].ou.toUpperCase() ,data[i].dn ) );
			
			if( data[i].ou.indexOf("dc=") != -1 || userOrganization.toUpperCase() == data[i].ou.toUpperCase() )
			{
				sharemailbox.get_available_users( data[i].dn );
			}
		}
	}
	
	cExecute ("$this.ldap_functions.get_organizations2&referral=false", handlerOrganizations);
}

var finderTimeout = '';

cShareMailbox.prototype.optionFinderTimeout = function(obj, event)
{
	if( event.keyCode === 13 )
	{	
		limit = 0;
		sharemailbox.optionFinder(obj.id);
	}	
	return;
}

cShareMailbox.prototype.optionFinder = function(id)
{
		
	var sentence = Element(id).value;
	
	var url = '$this.ldap_functions.get_available_users2&context=' + Element('em_combo_org').value + ( sentence ? '&sentence=' + sentence: '' );

	return userFinder( sentence, 'em_select_available_users', url, 'em_span_searching');
}

cShareMailbox.prototype.add_user = function()
{
	var select_available_users	= $('#em_select_available_users');
	var select_users 			= $('#em_select_sharefolders_users');
	var _this					= this;

	select_available_users.find('option:selected').each(function()
	{
		var _uid = $.trim($(this).val());

		if( _uid === $.trim(User.me.uid) )
		{
			$.Zebra_Dialog(get_lang('Cant share with yourself.'), {
		        'type'				: 'warning',
		        'overlay_opacity'	: '0.5',
				'custom_class'		: 'custom-zebra-filter',
		        'buttons'			: [ get_lang('Close') ]
		    });				
		}
		else
		{
			var newVal 		= $(this).val();
			var flagAppend	= false;

			select_users.find('option').each(function()
			{
				if( $(this).val() == newVal && !flagAppend )
					flagAppend = true;
			});

			if( !flagAppend )
			{
				select_users.append( new Option( $(this).text() , _uid ) );
				_this.users[ _uid ] = { 'uid' : _uid , 'acls' : "" };
			}
		}
	});
}

cShareMailbox.prototype.remove_user = function()
{
	delete this.users[$.trim($("#em_select_sharefolders_users").val())];

	$("#em_select_sharefolders_users option:selected").remove();

	$("input[type=checkbox][id^=em_input]").each(function()
	{
		$(this).attr("checked", false);
		if( $.trim( $(this).attr("id") ) == "em_input_deleteAcl" ) { $(this).attr( "disabled", true ); }
		if( $.trim( $(this).attr("id") ) == "em_input_writeAcl" ) { $(this).attr( "disabled", true ); }	
		if( $.trim( $(this).attr("id") ) == "em_input_sendAcl" ) { $(this).attr( "disabled", true ); }		
	});
}
	
/* Build the Object */
var sharemailbox = new cShareMailbox();