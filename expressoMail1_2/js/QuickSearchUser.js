	function emQuickSearchUser ()
	{
		this.divElement = null;
	}

	emQuickSearchUser.prototype.showList = function(data)
	{
		var div = document.createElement("div");
			div.style.margin 	= "5px";
			div.style.overflow	= "hidden";
			div.innerHTML	 	=	'<div id="div_QuickSearchUser" class="quicksearchcontacts" style="margin:3px;border:1px solid #cecece;">' + 
										'<table class="quicksearchcontacts"><tbody id="table_QuickSearchUser">' + data + '</tbody></table>' +
									'</div>';
	
		$(div).dialog(
		{
			resizable	: false,
			title		: get_lang('The results were found in the Global Catalog'),
			position	: 'center',
			width		: 620,
			height		: 390,
			modal		: false,
			buttons		: [
							{
								text: get_lang("Close"),
								click: function()
								{
									$(this).dialog("close");
									Element("em_message_search").value = "";									
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
                $(this).dialog("destroy");
            }
		});	
		
		this.divElement = div.parentNode;
	}
	
	emQuickSearchUser.prototype.closeWindow = function() 
	{
		Element("em_message_search").value = "";
		
		if( this.divElement != null )
		{
			$(this.divElement.parentNode).dialog("destroy");
			this.divElement.parentNode.removeChild(this.divElement);
			this.divElement = null;
		}
		
	}
	
	emQuickSearchUser.prototype.create_new_message = function (cn, mail, uid)
	{
		QuickSearchUser.closeWindow();
		var ldap_id = preferences.expressoMail_ldap_identifier_recipient;
		if (openTab.type[currentTab] != 4){
            new_message("new","null");
		}
		
		if(ldap_id){
			draw_email_box(uid, $("#content_id_"+currentTab).find(".to.email-text"));
		}else{
			draw_email_box("\""+cn+"\" <"+mail+">", $("#content_id_"+currentTab).find(".to.email-text"));
		}
	}

/* Build the Object */
var QuickSearchUser = new emQuickSearchUser();