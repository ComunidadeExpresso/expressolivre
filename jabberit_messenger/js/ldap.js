(function()
{
	var _conn;
	var Xtools;
	var _myWindow;
	var _is_ie;

	function addUser()
	{
		var _name = document.getElementById('im_name');
		var _group = document.getElementById('im_group');
		var jidUser = document.getElementById('im_jidUser').value; 
		var	_jid = document.getElementById('im_jid').value;

		if ( _jid )
		{
			_name.value = _name.value.replace(/^( )*|( )*$/g, '');
			_group.value = _group.value.replace(/^( )*|( )*$/g, '');

			if ( !(_name.value) || !(_group.value) )
			{
				if ( !(_name.value) || !(_group.value) )
				{
					alert( jabberitGetLang('ATTENTION') + "!!\n" +
						   jabberitGetLang("Enter a NAME") + " / " +
						   jabberitGetLang("Enter a GROUP") + ".");
				}
			}
			else
			{
				var _uid;
				
				if ( (_uid = document.getElementById('im_uid')) )
					_uid = _uid.value;
				else
					_uid = _jid.substr(0, (_jid.indexOf('@') - 1));

				_conn.go('$this.contacts_im.verifyAddNewContact',
						 function(data)
						 {
							data = eval(data);

							if( !data )
							{
								alert("*********** " + jabberitGetLang("Attention") + "!! ***********\n" + 
									  jabberitGetLang("This user is part of a small group!") + 
									  "\n" + jabberitGetLang("Contact was not added!"));
							}
							else
							{	
								var result;
		
								try
								{
						 			if( elementIframe = document.getElementById('iframe_applet_jabberit') )
						 			{
										if( !_is_ie )
											result = elementIframe.contentDocument.applets[0].addContact( jidUser, _name.value, _group.value );
										else
											result = elementIframe.contentWindow.document.applets[0].addContact( jidUser, _name.value, _group.value );
									}
									else // If Pop-Up
									{
										result = _myWindow.document.applets[0].addContact( jidUser, _name.value, _group.value );
									}
								}
								catch(e)
								{
									alert("*********** " + jabberitGetLang("Attention") + "!! ***********\n" +
										   jabberitGetLang('The module is not loaded') + "! " + 
										   "\n" + jabberitGetLang('Contact was not added!')) ;
								}
							}
							
							winBuild("add_user_info","remove");
							
						 },"uid="+_uid);
				}
			}
	}

	function _config(pObj, pEvent, pHandler)
	{
		if ( typeof pObj == 'object' )
		{
			if ( pEvent.substring(0, 2) == 'on' )
				pEvent = pEvent.substring(2, pEvent.length);

			if ( pObj.addEventListener )
				pObj.addEventListener(pEvent, pHandler, false);
			else if ( pObj.attachEvent )
				pObj.attachEvent('on' + pEvent, pHandler);
		}
	}

	function _load()
	{
		if( arguments.length > 0 )
			_myWindow = arguments[0];

		var _params =	{
				'lang1':jabberitGetLang('Name of Contacts'),
				'lang2':jabberitGetLang('Search'),
				'lang3':jabberitGetLang('Search result'),
				'lang4':jabberitGetLang('Nickname'),
				'lang5':jabberitGetLang('group')
		};

		var _win_addUser = { 
				id_window	 : "add_user_im",
				width		 : 440,
				height		 : 350,
				top		 	 : 80,
				left		 : 200,
				draggable	 : true,
				visible	 	 : "display",
				resizable	 : true,
				zindex		 : _ZINDEX++,
				title		 : 'Expresso Messenger - ' + jabberitGetLang('Search users'),
				closeAction  : "remove",
				content		 : Xtools.parse(Xtools.xml('userinfo'), 'add_user.xsl', _params)
		};
		
		winBuild( _win_addUser );
	}

	function _search()
	{
		var _loading = document.getElementById('__span_load_im');		
		var _target	 = document.getElementById('im_ldap_user');
	
		_loading.style.display = "block";
	
		_conn.go('$this.contacts_im.list_contacts',		
				function( data )
				{
					if( data )
					{
						var _pList = {
										'lang_addContact'	: "Adicionar Contatos" ,
										'lang_empty' 		: "Nenhum resultado encontrado !",
										'lang_error'		: "Tente Novamente!",
										'lang_many_results' : "Muitos Resultados ! Por favor tente refinar sua busca !"
									 };	
						
						_target.innerHTML = Xtools.parse( data, 'list_ldap_contacts.xsl', _pList);
						
						// Show Contact
						function _show(Obj)
						{
							var data;
							var _element = ( Obj.target ) ? Obj.target : Obj.srcElement;
							var groups;
			
							if ( !_element || _element.id == "" )
								return false;

							
							var _params	=	{
									'lang1':jabberitGetLang('Name of Contacts'),
									'lang2':jabberitGetLang('Search'),
									'lang3':jabberitGetLang('Resultado da Busca'),
									'lang4':jabberitGetLang('nickname'),
									'lang5':jabberitGetLang('group')
							};

							var _win_addUser = { 
									id_window	 : "add_user_info",
									width		 : 400,
									height		 : 190,
									top		 	 : 85,
									left		 : 220,
									draggable	 : true,
									visible	 	 : "display",
									resizable	 : true,
									zindex		 : _ZINDEX++,
									title		 : 'Expresso Messenger - ' + jabberitGetLang('Add Contact'),
									closeAction  : "remove",
									content		 : Xtools.parse(Xtools.xml('adduser'), 'add_user.xsl', _params)
							};

							winBuild( _win_addUser );

				 			try
				 			{
				 				var elementIframe = document.getElementById('iframe_applet_jabberit');
				 				
				 				// If Layer;
				 				if( elementIframe != null )
					 			{
									if( !_is_ie )
										groups = elementIframe.contentDocument.applets[0].getGroupsToExpresso();
									else
										groups = elementIframe.contentWindow.document.applets[0].getGroupsToExpresso();
								}
								else // If Pop-Up
								{
									groups = _myWindow.document.applets[0].getGroupsToExpresso();
								}
						
								if( typeof(groups) == 'object')
								{
									data = groups + ";";
									data = data.substring(0,(data.length-2));
								}
								else			
									data = groups.substring(0,(groups.length-1));
								
								setTimeout(function(){showUser(data, _element);}, 250);
							}
							catch(e)
							{
								alert("*********** " + jabberitGetLang("Attention") + "!! ***********\n" + 
									  jabberitGetLang("The module is not loaded") + "!\n" ); 
							}
						}
					}
					
					var _member = _target.firstChild;
			
					while ( _member )
					{
						if( _member.getAttribute('photo') === '1' )
						{
							var jid = _member.getAttribute('jid'); 
							var ou = _member.getAttribute('ou');

							var _img_path = path_jabberit + 'inc/webservice.php?' + Date.parse( new Date );
								_img_path += '&phpPhoto=' + jid + '&phpOu=' + ou;

							_member.style.backgroundImage = 'url(' + _img_path + ')';
						}
						_config(_member, 'onclick', _show);
						
						_member = _member.nextSibling;
					}

					_loading.style.display = "none";						
				},
				'name='+ arguments[0]
			    );
	}

	function showUser(pData, pElement)
	{
		var jidUser = "";
		
		if ( pElement.getAttribute('value'))
		{
			jidUser = pElement.getAttribute('jid');
			pElement = m.getAttribute('value');
		}
		else
		{
			jidUser = pElement.parentNode.getAttribute('jid');
			pElement = pElement.parentNode.getAttribute('value');
		}

		document.getElementById('im_jidUser').value = jidUser;
		document.getElementById('im_jid').value 	= pElement.substr(0, pElement.indexOf(';'));
		document.getElementById('im_uid').value 	= pElement.substr((pElement.indexOf(';')+1));

		var fname = document.getElementById(pElement).innerHTML;
		fname = fname.substr(0, fname.indexOf(' '));
		document.getElementById('im_name').value = fname;

		if( pData)
		{
			document.getElementById('im_group').setAttribute('selectboxoptions', pData);

			if( document.getElementById('selectBox0') == null )
				editS.create(document.getElementById('im_group'));
		}
		
		var _pButtons = {
							'lang1' : jabberitGetLang('add'),
							'lang2' : jabberitGetLang('close'),
							'onclickClose' : 'winBuild("add_user_info","remove")',
							'onclickSubmit' : 'loadscript.addUser()'
						}; 
		
		document.getElementById('buttons_adduser').innerHTML = Xtools.parse(Xtools.xml('buttons_main'), 'buttons.xsl', _pButtons); 
	}
	
	function LDAP()
	{
		_conn		= arguments[0];
		Xtools		= arguments[1];
		_is_ie		= arguments[2];
	}

	LDAP.prototype.addUser	= addUser;
	LDAP.prototype.load		= _load;
	LDAP.prototype.search	= _search;
	window.JITLdap = LDAP;
}
)();