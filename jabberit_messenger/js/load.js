(function()
{
	var _autoStatusTime = 60000; // 1 minuto
	var _autoStatus;
	var _conn		= new JITConnector(path_jabberit);
	var Xtools 		= new xtools(path_jabberit);
	var _showhidden = new ShowHidden(300);
	var _win_contacts = null;
	var myWindow	  = null;
	var flagMouseOver = '';
	var flagOpenIM	  = '';
	var flagOpenPopup = '';
	var flagAwayIM	  = '';
	var itensSubMenu  = [];
    
	// Sniffer Browser
	var agt	= navigator.userAgent.toLowerCase();
    var is_major = parseInt(navigator.appVersion);
    var is_minor = parseFloat(navigator.appVersion);    
    var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
    var is_gecko = (agt.indexOf('gecko') != -1);
    var is_gecko1_5 = (agt.indexOf('firefox/1.5') != -1 || agt.indexOf('firefox/2') != -1 || agt.indexOf('iceweasel/2') != -1);
    var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
    var is_ie3    = (is_ie && (is_major < 4));
    var is_ie4    = (is_ie && (is_major == 4) && (agt.indexOf("msie 4")!=-1) );
    var is_ie4up  = (is_ie && (is_major >= 4));
    var is_ie5    = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.0")!=-1) );
    var is_ie5_5  = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.5") !=-1));
    var is_ie5up  = (is_ie && !is_ie3 && !is_ie4);
    var is_ie5_5up =(is_ie && !is_ie3 && !is_ie4 && !is_ie5);
    var is_ie6    = (is_ie && (is_major == 4) && (agt.indexOf("msie 6.")!=-1) );
    var is_ie6up  = (is_ie && !is_ie3 && !is_ie4 && !is_ie5 && !is_ie5_5);    
    var is_win   = ( (agt.indexOf("win")!=-1) || (agt.indexOf("16bit")!=-1) );

	var _ldap = new JITLdap( _conn, Xtools, is_ie );

	function addContacts()
	{
		_ldap.load(myWindow);
	}

	function addIcon()
	{
		
		var StatusBar = document.getElementById('divStatusBar');

		/**
		 * @quando estiver habilitada a opção fora de escritório nos filtros. 
		 */
		
		if( div_write_msg = document.getElementById('em_div_write_msg') )
		{		
			var StatusBarIM = document.getElementById('JabberMessenger');
			div_write_msg.parentNode.insertBefore(StatusBarIM, div_write_msg);
			StatusBarIM.style.paddingLeft = '33px';
			return;			
		}
		
		if( StatusBar )
		{
			StatusBar.style.paddingLeft = '33px';
			
			var _div = document.createElement('div');
			_div.appendChild(StatusBar.parentNode.removeChild(StatusBar.previousSibling));
			StatusBar.parentNode.insertBefore(_div,StatusBar);
			
			var divJabber = document.createElement('div');
				divJabber.setAttribute('id', 'JabberMessenger');
			
			var _status = top.document.createElement('div');
				_status.setAttribute('id', 'jabberit_login');
				_status.style.background = 'no-repeat';
				_status.style.backgroundImage = 'url(' + jabberit_group_open.src + ')';
				_status.style.float = 'left';
				_status.style.height = '15px';
				_status.style.left = '7px';
				_status.style.margin = '8 0 0 10px';
				_status.style.padding = '0px';
				_status.style.position = 'absolute';
				_status.style.width = '15px';
				_status.style.cursor = 'pointer';

			divJabber.insertBefore(_status,divJabber.firstChild);

			var _menu;
				_menu = top.document.createElement('div');
				_menu.setAttribute('id','fast_menu_jabberit');
				_menu.className = "x-menu";
				_menu.style.zIndex = '99999';
				_menu.onmouseout = function(){ _showhidden.hiddenObject(false); };
				_menu.onmouseover = function(){ _showhidden.hiddenObject(true); };
				
			divJabber.insertBefore(_menu, divJabber.firstChild);

			var _menu_div = top.document.createElement('div');
				_menu_div.setAttribute('id','status_Jabber_Expresso');
				_menu_div.style.background = 'no-repeat';
				_menu_div.style.backgroundImage = 'url(' + jabberit_add_user.src + ')';
				_menu_div.style.float = 'left';
				_menu_div.style.height = '18px';
				_menu_div.style.left = '19px';
				_menu_div.style.margin = '0 0 0 10px';
				_menu_div.style.padding = '0px';
				_menu_div.style.position = 'absolute';
				_menu_div.style.width = '18px';
				_menu_div.style.cursor = 'pointer';
				_menu_div.style.zindex = '999999';
			
			statusJabberExpresso = _menu_div;
			
			divJabber.insertBefore(_menu_div, divJabber.firstChild);
			StatusBar.insertBefore(divJabber, StatusBar.firstChild);			
			
			configEvents(_menu_div,'onclick', windowShow);
            configEvents( _status, 'onclick', function(){fastMenu(_status);});
		}
	}
	
    function fastMenu()
    {
        if( arguments.length > 0 )
        {
            var element = arguments[0];

            var _options = [
	                            ['Add Contact', 'loadscript.addContacts();'],
	                            ['Help', 'loadscript.helpJabberit();'],
	                            ['Preferences', 'loadscript.preferences();']
	                       ];
            
            var _itens = "";
		
            for( var i in _options )
            {
                if( _options[i].constructor == Function )
                    continue;
			
                _itens += '<img src="'+jabberit_group_close.src +'"/>';
                _itens += '<span style="cursor:pointer; margin:3px;" onclick='+_options[i][1]+'>';
                _itens += jabberitGetLang( _options[i][0] ) + '</span><br/>';
            }
		
            var _optionsItens 		= document.createElement("div");
            	_optionsItens.id	= "fastMenu_Jabber";				
            	_optionsItens.style.marginTop	= "19px";
            	_optionsItens.style.marginLeft	= "-8px";
            	_optionsItens.className		= "x-menu";
            	_optionsItens.style.zIndex	= '999999';
            	_optionsItens.innerHTML		= _itens;
            	_optionsItens.onclick		= function(){
                _showhidden.hiddenObject(false);
            };
            
            _optionsItens.onmouseout = function(){
                _showhidden.hiddenObject(false);
            };
            
            _optionsItens.onmouseover = function(){
                _showhidden.hiddenObject(true);
            };	
									  
            _showhidden.action('onmouseover', 'onmouseout', _optionsItens);
			
            element.parentNode.appendChild( _optionsItens );
        }
    }

    function addUser()
	{
		_ldap.addUser();
	}
	
	function autoStatus()
	{
		if ( _autoStatus )
			clearTimeout(_autoStatus);

		var _div_status = document.getElementById('status_Jabber_Expresso');
		if ( _div_status )
		{
			var _status = _div_status.style.backgroundImage;
			_status = _status.substr(_status.lastIndexOf('/') + 1);
			_status = _status.substr(0, _status.indexOf('.'));
			if ( _status == 'xa' && _div_status.getAttribute('autoStatus') )
			{
				_div_status.removeAttribute('autoStatus');
				changeStatusJava("2");
			}
		}

		var TimeStatus = flagAwayIM.split(':');

		if( TimeStatus[1] )
			_autoStatus = setTimeout(autoStatusHandler, parseInt(TimeStatus[1])*_autoStatusTime);
		else
			_autoStatus = setTimeout(autoStatusHandler, parseInt(_autoStatusTime));
	}

	function autoStatusHandler()
	{
		var _div_status = document.getElementById('status_Jabber_Expresso');
		if ( _div_status )
		{
			var _status = _div_status.style.backgroundImage;
			_status = _status.substr(_status.lastIndexOf('/') + 1);
			_status = _status.substr(0, _status.indexOf('.'));
			if ( _status == 'available' )
			{
				_div_status.setAttribute('autoStatus','true');
				changeStatusJava("5");
			}
		}
	}

	function changeStatusJava()
	{
  		if(arguments.length > 0 )
  		{
	 		try
	 		{
	 			var status	= arguments[0];
	 			var msg	   	= ( arguments[1] ) ? arguments[1] : "";
	 		
	 			// If Layer;
	 			if( elementIframe = document.getElementById('iframe_applet_jabberit') )
	 			{
					if( !is_ie )
						elementIframe.contentDocument.applets[0].changeStatusfromExpresso(status, msg);
					else
						elementIframe.contentWindow.document.applets[0].changeStatusfromExpresso(status, msg);
				}
				else // If Pop-Up
				{
					myWindow.document.applets[0].changeStatusfromExpresso(status, msg);
				}
				
			}
			catch(e)
			{
				if( confirm('Deseja conectar o IM ?') )
					windowShow();
			}
	   	}		
	}

	function configEvents(pObj, pEvent, pHandler)
	{
		if ( typeof pObj == 'object' )
		{
			if ( pEvent.substring(0, 2) == 'on' )
				pEvent = pEvent.substring(2, pEvent.length );

			if ( arguments.length == 3 )
			{
				if ( pObj.addEventListener )
					pObj.addEventListener(pEvent, pHandler, false );
				else if ( pObj.attachEvent )
					pObj.attachEvent( 'on' + pEvent, pHandler );
			}
			else if ( arguments.length == 4 )
			{
				if ( pObj.removeEventListener )
					pObj.removeEventListener( pEvent, pHandler, false );
				else if ( pObj.detachEvent )
					pObj.detachEvent( 'on' + pEvent, pHandler );
			}
		}
	}

	function closeWindow()
	{
		var url_img 		= path_jabberit + 'templates/default/images/unavailable.gif';
		var elementStatus	= getElementStatus('status_Jabber_Expresso');

		elementStatus.style.backgroundImage = 'url(' + url_img +')';

		myWindow = null;
	}

	function getElementStatus()
	{
		return document.getElementById('status_Jabber_Expresso');
	}

	function getPreferences()
	{
		if( flagOpenIM == '' )
		{
			_conn.go("$this.preferences.getPreferences",
						function(data)
						{
							var autoConnect = '';
							flagOpenIM = data;

							if( data.indexOf(';') != -1)
							{
								var temp = data.split(';');
								autoConnect = flagOpenIM = temp[0];
								
								// Open as Pop-Up
								flagOpenPopup = 'openWindowJabberitPopUp:false';
								
								if( temp[1] )
								{ 
									if( temp[1] == 'openWindowJabberitPopUp:true' || temp[1] == 'openWindowJabberitPopUp:false')
										flagOpenPopup = temp[1];
								}
								
								// Away
								flagAwayIM = 'flagAwayIM:5';
								
								if( temp[2] )
								{
									flagAwayIM = temp[2];
								}
							}
							else
							{
								autoConnect = flagOpenIM;
							}
							
							if( autoConnect == 'openWindowJabberit:true' )
							{
								setTimeout('loadscript.windowHidden();', 2500);
							}
					 	});
		}

	}

	function helpJabberit()
	{
		var myWindowHelp = window.open( path_jabberit + 'help.php', 'HelpjabberIM', 'width=800,height=495,top=50,left=50,scrollbars=yes');
	}

	function keyPressSearch()
	{
		var ev = arguments[0];
		var element = arguments[1];

		if ( ev.keyCode == 13 )
			if( element.value.length >= 4 )
				_ldap.search( element.value );	
			else
				alert(jabberitGetLang('Your search argument must be longer than 4 characters.'));
	}
	
	function openPopup()
	{
		var widPopup = '220';

		if( is_ie )
			widPopup = '250';

		try{

			if( myWindow == null )
			{	
				myWindow = window.open('','JabberIM','width='+widPopup+',height=400,top=50,left=50,toolbar=0,menubar=0,resizable=0,scrollbars=0,status=0,location=0,titlebar=0');
				myWindow.close();
				myWindow = window.open(path_jabberit + 'client.php','JabberIM','width='+widPopup+',height=400,top=50,left=50,toolbar=0,menubar=0,resizable=0,scrollbars=0,status=0,location=0,titlebar=0');
				myWindow.blur();
				configEvents( myWindow, 'onbeforeunload', closeWindow );
			}
			else
			{
				for( var i = 15 ; i > 0 ; i-- )
				{
					myWindow.moveBy(i,0); myWindow.moveBy(-i,0);
				}
				myWindow.focus();
			}
		}
		catch(e)
		{
			delete myWindow;
			myWindow = window.open(path_jabberit + 'client.php','JabberIM','width='+widPopup+',height=400,top=50,left=50,toolbar=0,menubar=0,resizable=0,scrollbars=0,status=0,location=0,titlebar=0');
			myWindow.blur();
			configEvents( myWindow, 'onbeforeunload', closeWindow );
		}
	}

	function openWindow()
	{
		if( document.getElementById(_win_contacts.id_window + "__content") == null )
		{
			
			var _params = {
							'path' : path_jabberit,
							'width' : ( is_ie ) ? '100%' : '220px'
			};
			
			_win_contacts.content = Xtools.parse(Xtools.xml('contacts_jabberit'), 'contacts_jabberit.xsl', _params );
			
			winBuild( _win_contacts );
		}
	}

	function preferences()
	{
		var _params = {
					   'path'  : path_jabberit,
					   'lang1' : jabberitGetLang('Your Preferences'),
					   'lang2' : jabberitGetLang('Connection'),	
					   'lang3' : jabberitGetLang('Enable Auto Login IM'),
					   'lang4' : jabberitGetLang('Users OffLine'),
					   'lang5' : jabberitGetLang('Show friends Offline'),
					   'lang6' : jabberitGetLang('Save'),
					   'lang7' : jabberitGetLang('Cancel'),
					   'lang8' : jabberitGetLang('Window'),
					   'lang9' : jabberitGetLang('Open as Pop-Up Window'),
					   'lang10' : jabberitGetLang('Away'),
					   'lang11' : jabberitGetLang('Set status to away after'),
					   'lang12' : jabberitGetLang('minutes'),						   
					   'langYes': jabberitGetLang('Yes'),
					   'langNo' : jabberitGetLang('No')											   
	  	};
		
		var _win_preferences = { 
									id_window	 : "jabberit_preferences",
									width		 : 430,
									height		 : 330,
									top		 	 : 150,
									left		 : 100,
									draggable	 : true,
									visible	 	 : "display",
									resizable	 : true,
									zindex		 : _ZINDEX++,
									title		 : 'Expresso Messenger - ' + jabberitGetLang('Preferences'),
									closeAction  : "remove",
									content		 : Xtools.parse(Xtools.xml('preferences_jabberit'), 'preferences_jabberit.xsl', _params)
		};

		winBuild(_win_preferences);

		// Element openWindowJabberit
		var value1			= flagOpenIM.split(':');
		var element1		= document.getElementById(value1[0]);
		var valueSelect1	= value1[1];
		
		for(var i = 0; i < element1.options.length; i++)
			if( element1.options[i].value == valueSelect1 )
				element1.options[i].selected = true;

		
		// Element openWindowJabberitPopUp
		var value2			= flagOpenPopup.split(':');
		var element2		= document.getElementById(value2[0]);
		var valueSelect2	= value2[1];
			
		for(var i = 0; i < element2.options.length; i++)
			if( element2.options[i].value == valueSelect2 )
				element2.options[i].selected = true;
				
		// Element flagAwayIM
		var value3		= flagAwayIM.split(':');
		var element3	= document.getElementById(value3[0]);
		element3.value	= value3[1];
		
		
		var _pButtons =
		{
			'lang1' : jabberitGetLang('Save'),
			'lang2' : jabberitGetLang('Close'),
			'onclickClose' : 'winBuild("jabberit_preferences","remove");',
			'onclickSubmit' : 'javascript:loadscript.setPrefe();'
		}; 
		
		document.getElementById('buttons_preferences_jabberit').innerHTML = Xtools.parse(Xtools.xml('buttons_main'), 'buttons.xsl', _pButtons);
		
	}

	function searchUser()
	{
		var element = arguments[0].previousSibling;
		
		if( element.value.length >= 4 )
			_ldap.search( element.value );	
		else
			alert(jabberitGetLang('Your search argument must be longer than 4 characters.'));
	}

	function setItensStatusMenu()
	{
		var applet = "";
		
		try
		{
			// Layer
			if( elementIframe = document.getElementById('iframe_applet_jabberit') )
			{
				if( !is_ie )
					applet = elementIframe.contentDocument.applets[0];
				else
					applet = elementIframe.contentWindow.document.applets[0];
			}
			else // Pop-up
			{
				applet = myWindow.document.applets[0];
			}
			
			for( i = 1; i < 6; i++ )
				itensSubMenu[i] = applet.getStatusMessages(i);
			
		}catch(e){}
	}
	
	function setPreferences()
	{
		// Element openWindowJabberit
		var elementOpenW = document.getElementById('openWindowJabberit');
		var value = '';
		var flagReload = false;
		
		for(var i = 0 ; i < elementOpenW.options.length; i++)
			if( elementOpenW.options[i].selected == true)
			{
				value = 'preferences1=openWindowJabberit:' + elementOpenW.options[i].value;
				flagOpenIM = 'openWindowJabberit:' + elementOpenW.options[i].value;
			}

		// Element openWindowJabberitPopUp
		var elementOpenPop = document.getElementById('openWindowJabberitPopUp');	

		for(var i = 0; i < elementOpenPop.options.length; i++ )
			if( elementOpenPop.options[i].selected ==  true )
			{
				value += '&preferences2=openWindowJabberitPopUp:' + elementOpenPop.options[i].value;
				
				if( flagOpenPopup != 'openWindowJabberitPopUp:' + elementOpenPop.options[i].value)
					flagReload = true;
					
				flagOpenPopup = 'openWindowJabberitPopUp:' + elementOpenPop.options[i].value;
			}
		
		// Element flagAwayIM
		var elementFlagIM = document.getElementById('flagAwayIM');
		
		if( elementFlagIM.value.length > 0 && parseInt(elementFlagIM.value) > 0 )
		{
			flagAwayIM = 'flagAwayIM:' + elementFlagIM.value;
			value += '&preferences3=flagAwayIM:' + elementFlagIM.value;
		}
		else
		{
			alert(jabberitGetLang('Enter a value greater than or equal to 1!'));
			return false;
		}

		_conn.go('$this.preferences.setPreferences',
				 function(data)
				 {
					if(data == 'true')
					{
						if( flagReload )
						{
							window.location.reload();
							myWindow.close();							
						}
					}
					else{ alert(jabberitGetLang('Error saving your preferences!')); }

					winBuild('jabberit_preferences', 'remove');
				 },
				 value);
	}
	
	function windowConf(pLeft)
	{
		if( !is_ie )
			var sizeW = { w : 234, h : 432 };
		else
			var sizeW = { w : 264, h : 430 };
		
		_win_contacts = {
							id_window	 : "jabberit_contacts",
							width		 : sizeW.w,
							height		 : sizeW.h,
							top		 	 : 60,
							left 		 : -1500,
							leftOld		 : 50,
							draggable	 : true,
							visible	 	 : "display",
							resizable	 : true,
							zindex		 : _ZINDEX++,
							title		 : "Expresso Messenger",
							closeAction  : "hidden",
							content		 : ""
		};
		
	}
	
	function windowHidden()
	{
		if( _win_contacts == null )
		{
			windowConf(-1500);
			windowContacts();
		}
		else
			winBuild( _win_contacts.id_window , "display" );
	}
	
	function windowShow()
	{
		if( _win_contacts == null )
		{
			windowConf(70);
			windowContacts();			
		}
		else
			winBuild( _win_contacts.id_window , "display" );
	}
	
	function windowContacts()
	{
		if( flagOpenPopup === 'openWindowJabberitPopUp:true' )
		{
			openPopup();
		}
		else
		{
			openWindow();
		}
	}

	function Load()
	{
		addIcon();
		getPreferences();
		
		// AutoStatus Away		
		autoStatus();
		configEvents(document, 'onmousemove', autoStatus);
		configEvents(document, 'onkeypress', autoStatus);
	}

	Load.prototype.adIcon			= addIcon;
	Load.prototype.addContacts		= addContacts;
	Load.prototype.addUser			= addUser;
	Load.prototype.autoStatusIM		= autoStatus;
	Load.prototype.chgStatusJava	= changeStatusJava;
	Load.prototype.closeW			= closeWindow;
	Load.prototype.getElement		= getElementStatus;
	Load.prototype.helpJabberit		= helpJabberit;
	Load.prototype.keyPress			= keyPressSearch;
	Load.prototype.openPopup		= openPopup;
	Load.prototype.preferences		= preferences;	
	Load.prototype.search			= searchUser;
	Load.prototype.setPrefe 		= setPreferences;
	Load.prototype.windowHidden		= windowHidden;
	
	configEvents(window, 'onload', function(){ window.loadscript = new Load; });

})();

// Functions OnMouseOver e OnMouseOut

function elementOnMouseOut()
{
	if( arguments.length > 0 )
	{
		var _element = arguments[0];
			_element.style.backgroundColor = '';
			_element.style.border = '';
			if( !arguments[1] )
				_element.className = '';
	}
}

function elementOnMouseOver()
{
	if( arguments.length > 0 )
	{
		var _element = arguments[0];
			_element.className = 'x-menuOnMouseOver';
	}
}