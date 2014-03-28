	function cShareMailbox()
	{
		var users;
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

		Element('em_input_readAcl').checked		= false;
		Element('em_input_deleteAcl').checked	= false;
		Element('em_input_writeAcl').checked	= false;
		Element('em_input_sendAcl').checked		= false;
		
	    Element('em_input_deleteAcl').disabled	= true;
	    Element('em_input_writeAcl').disabled	= true;
		
		if ((this.users[user].acls.indexOf('l',0) >= 0) &&
			(this.users[user].acls.indexOf('r',0) >= 0) &&
			
			(this.users[user].acls.indexOf('s',0) >= 0) )
		{
			Element('em_input_sendAcl').disabled = false;
			Element('em_input_deleteAcl').disabled = false;
	        Element('em_input_writeAcl').disabled = false ;
			Element('em_input_readAcl').checked = true;
		}
		else
			Element('em_input_sendAcl').disabled = true;
		
		
		if ((this.users[user].acls.indexOf('x',0) >= 0) &&
			(this.users[user].acls.indexOf('t',0) >= 0) &&
			(this.users[user].acls.indexOf('e',0) >= 0) )
		{
			Element('em_input_deleteAcl').checked = true;
			Element('em_input_deleteAcl').disabled = false;
		}
		
		if ((this.users[user].acls.indexOf('w',0) >= 0) &&
			(this.users[user].acls.indexOf('i',0) >= 0) &&
			(this.users[user].acls.indexOf('k',0) >= 0) )
		{
			Element('em_input_writeAcl').checked = true;
	        Element('em_input_writeAcl').disabled = false
		}
		
		if (this.users[user].acls != "false" && this.users[user].acls.indexOf('p',0) >= 0 && this.users[user].acls.indexOf('a',0) >= 0)
		{
			Element('em_input_sendAcl').disabled = false;
			Element('em_input_sendAcl').checked = true;
		}			
	}
	
	cShareMailbox.prototype.setaclfromuser = function()
	{
		var acl		= '';
		var select 	= Element('em_select_sharefolders_users');

		if( select.selectedIndex == "-1" )
		{
			$.Zebra_Dialog(get_lang('Select a user!'), {
			        'type'				: 'warning',
			        'overlay_opacity'	: '0.5',
					'custom_class'		: 'custom-zebra-filter',
			        'buttons'			: [ get_lang('Close') ]
			});				

			return false;
		}
		else
		{
			var user = select.options[select.selectedIndex].value;
			
			if ( Element('em_input_readAcl').checked ) 
			{
				Element('em_input_sendAcl').disabled	= false;
				Element('em_input_deleteAcl').disabled	= false;
	            Element('em_input_writeAcl').disabled	= false;
				acl = 'lrs';
			}
			else
			{
				Element('em_input_sendAcl').disabled	= true;
				Element('em_input_sendAcl').checked		= false;
				Element('em_input_deleteAcl').disabled	= true;
	            Element('em_input_deleteAcl').checked	= false;
	            Element('em_input_writeAcl').disabled	= true;
	            Element('em_input_writeAcl').checked	= false;
			}
					
			if (Element('em_input_deleteAcl').checked)
				acl += 'xtea';

			if (Element('em_input_writeAcl').checked) {
				acl += 'wika';			
			}		
			if (Element('em_input_sendAcl').checked){
				acl += 'pa';			
			}

			this.users[user].acls = acl;
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
										$.ajax({
												  url: 'controller.php?' + $.param( {
												  					  action: '$this.imap_functions.setacl',
																      acls: connector.serialize(sharemailbox.users)} 
																      ),
												  success: function( data ){
												      data = connector.unserialize( data );
												      
												      if( data )
													  {
													  	write_msg(get_lang('Shared options saved with success'));
													  	$(div).dialog("close");
				
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
				beforeClose	: function()
				{ 
					$(this).remove( $(this).firstChild() );
				},
                close:function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
                    $(this).dialog("destroy");
                },
                open: function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 

					var handlerOrganizations = function(data)
					{
						var userOrganization = $('#user_organization').val();
						
						for( i = 0; i < data.length; i++ )
						{
							$('#em_combo_org').append( new Option( data[i].ou ,data[i].dn ) );
							
							if( data[i].ou.indexOf("dc=") != -1 || userOrganization.toUpperCase() == data[i].ou.toUpperCase() )
							{
								console.log( $('#em_combo_org') );

								
								//$('#em_combo_org').options[i].selected = true;
								
								//sharemailbox.get_available_users(data[i].dn);
							}
						}
					}
					
					cExecute ("$this.ldap_functions.get_organizations2&referral=false", handlerOrganizations);

					$("#em_input_sendAcl").attr('checked', false);
					$("#em_input_deleteAcl").attr('checked', false);
					$("#em_input_writeAcl").attr('checked', false);

					var selectSharedFolders = $("#em_select_sharefolders_users");

					/*
					var selectSharedFolders = Element('em_select_sharefolders_users');
					this.users = optionsData;
					for( var i in optionsData )	
						selectSharedFolders.options[selectSharedFolders.options.length] = new Option(optionsData[i].cn, i, false, false);
					*/
                }
		});

		windowShare.html( new EJS( {url: 'templates/default/shareMailbox.ejs'} ).render());
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
		var new_options 			= "";
		var select_available_users	= $('#em_select_available_users');
		var select_users 			= $('#em_select_sharefolders_users');

		select_available_users.find('option:selected').each(function(){

			if( $.trim($(this).val()) === $.trim(User.me.uid) )
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
					select_users.append( new Option( $(this).text() , $(this).val() ) );
			}

		});


		/*for( i = 0 ; i < select_available_users.children('option').length ; i++ )
		{
				
			// 	var newobject = new Object;
			// 	newobject.cn = select_available_users.options[i].text;
			// 	newobject.acls = "";
			// 	this.users[select_available_users.options[i].value] = newobject;
			// }
		}//fim for*/

		if ( new_options != '' )
		{
			select_users.innerHTML = '#' + new_options + select_users.innerHTML;
			select_users.outerHTML = select_users.outerHTML;
		}
	}

	cShareMailbox.prototype.remove_user = function()
	{
		select_users = document.getElementById('em_select_sharefolders_users');
	
	    var acl = '';
		var select 	= Element('em_select_sharefolders_users');
		var user = select.options[select.selectedIndex].value;
		
		delete this.users[user];

		select.options[select.selectedIndex] = null;

		Element('em_input_readAcl').checked = false;
		Element('em_input_deleteAcl').checked = false;
		Element('em_input_writeAcl').checked = false;
		Element('em_input_sendAcl').checked = false;

	
	}
		
/* Build the Object */
var sharemailbox = new cShareMailbox();
