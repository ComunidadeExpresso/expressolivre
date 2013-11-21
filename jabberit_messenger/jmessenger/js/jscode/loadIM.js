(function()
    {
        var _autoStatus;
        var _autoStatusTime 		= 60000; // 1 minuto
        var addUser			= null;
        var conn			= null;
        var fullName			= "";
        var path			= "";
        var path_phpgwapi               = "";
        var _preferencesIM		= "";
        var selectEditable		= null;
        var showhidden 			= null;
        var statusUserIM		= "";
        var _statusMessage		= "";
        var timeoutId			= null;
        var userCurrent			= null;
        var _timeOutNotification	= "";
        var Xtools			= null;
        var zIndex			= 9001;
        var windowPopUp			= [];
	
        // Images
        var add_user = new Image();
        add_user.src = path_jabberit + 'templates/default/images/adduser_azul.png';

        var arrow_down = new Image();
        arrow_down.src = path_jabberit + 'templates/default/images/arrow_down.gif'; 

        var arrow_right	= new Image();
        arrow_right.src = path_jabberit + 'templates/default/images/arrow_right.gif';
	
        function actionButton()
        {
            if( arguments.length > 0 )
            {
                var e 			= arguments[0];
                var _element	= ( e.target ) ? e.target : e.srcElement;
                var jid 	= arguments[1];
                var coord	= null;

                if ( !e )
                    e = window.event;

                var _X = e.clientX + document.body.scrollLeft - document.body.clientLeft;
                var _Y = e.clientY + document.body.scrollTop  - document.body.clientTop;
				
                coord = {
                    X : _X, 
                    Y : _Y
                };
		

                var _onContextMenu = function()
                {
                    return false;
                };
			
                window.document.oncontextmenu	= _onContextMenu;
			
                if( e.button )
                {
                    if( e.button > 1 )
                        optionsItensContact( jid, coord );
                    else
                        TrophyIM.rosterClick(jid);
                }	
                else if( e.which )
                {
                    if( e.which > 1 )
                        optionsItensContact( jid, coord );
                    else
                    if( e.target.id )
                        TrophyIM.rosterClick(jid);
                }
			
                setTimeout(function()
                {
                    window.document.oncontextmenu = function()
                    {
                        return true;
                    };
				
                },500);
            }
        }

        function addContact()
        {
            if( arguments.length > 0 )
                addUser.add();
            else
                addUser.show();
        }
	
        function addIcon()
        {
		
            var div_write_msg	= ( getElement('em_div_write_msg') != null ) ? getElement('em_div_write_msg') : null ;
            var StatusBar		= ( getElement('divStatusBar') != null ) ? getElement('divStatusBar') : null ;
            var StatusBarIM		= ( getElement('JabberMessenger') != null ) ?  getElement('JabberMessenger') : null;
		
            /**************************************************************************
		 * 
		 * Quando estiver habilitada a opção fora de escritório nos filtros.
		 *  
		 */

            if( ( div_write_msg && StatusBarIM ) != null )
            {		
                div_write_msg.parentNode.insertBefore(StatusBarIM, div_write_msg);
                StatusBarIM.style.paddingLeft = '33px';
                return;
            }
		
            /**************************************************************************/
		
            if ( !StatusBarIM )
            {
                StatusBarIM = document.createElement('div');
                StatusBarIM.setAttribute('id', 'JabberMessenger');
            }
		
            if( StatusBar )
            {
                StatusBar.style.paddingLeft = '33px';
			
                var _div = document.createElement('div');
                _div.appendChild(StatusBar.parentNode.removeChild(StatusBar.previousSibling));
			
                StatusBar.parentNode.insertBefore( _div, StatusBar);
			
                var _fastMenu = top.document.createElement('div');
                _fastMenu.setAttribute('id', 'fast_menu_jabber_expresso');
                _fastMenu.style.background		= 'no-repeat';
                _fastMenu.style.backgroundImage = 'url(' + arrow_down.src + ')';
                _fastMenu.style.float			= 'left';
                _fastMenu.style.height			= '15px';
                _fastMenu.style.left			= '7px';
                _fastMenu.style.margin			= '8 0 0 10px';
                _fastMenu.style.padding			= '0px';
                _fastMenu.style.position		= 'absolute';
                _fastMenu.style.width			= '15px';
                _fastMenu.style.cursor			= 'pointer';

                StatusBarIM.insertBefore( _fastMenu, StatusBarIM.firstChild );
			
                // Add event onclick element _fastMenu
                configEvents( _fastMenu, 'onclick', function(){
                    fastMenu(_fastMenu);
                });

                var _statusJabber = top.document.createElement('div');
                _statusJabber.setAttribute('id','status_jabber_expresso');
                _statusJabber.style.background		= 'no-repeat';
                _statusJabber.style.backgroundImage = 'url(' + add_user.src +')';
                _statusJabber.style.float			= 'left';
                _statusJabber.style.height			= '18px';
                _statusJabber.style.left			= '19px';
                _statusJabber.style.margin			= '0 0 0 10px';
                _statusJabber.style.padding			= '0px';
                _statusJabber.style.position		= 'absolute';
                _statusJabber.style.width			= '18px';
                _statusJabber.style.cursor			= 'pointer';
                _statusJabber.style.zindex			= '999999';
			
                StatusBarIM.insertBefore( _statusJabber, StatusBarIM.firstChild );
			
                StatusBar.insertBefore( StatusBarIM, StatusBar.firstChild );

                // Add event onclick element _statusJabber
                if( _preferencesIM[0] == "openWindowJabberit:true" )
                {
                    configEvents( _statusJabber, 'onclick', function(){
                        rosterDiv();
                    });
                }
                else
                {
                    configEvents( _statusJabber, 'onclick', function(){
                        TrophyIM.load();
                    });
                }
            }
        }

        function addNewUser()
        {
            addUser.newUser();
        }
	
        function autoStatus()
        {
            var _div_status = ( getElement('status_jabber_expresso') != null ) ?  getElement('status_jabber_expresso') : null;
		
            if ( _autoStatus )
                clearTimeout(_autoStatus);

            if ( _div_status != null )
            {
                var _status = _div_status.style.backgroundImage;
                _status = _status.substr(_status.lastIndexOf('/') + 1);
                _status = _status.substr(0, _status.indexOf('.'));
				
                if( _status == "xa" && _div_status.getAttribute('autoStatus') )
                {
                    if( getStatusMessage() != "")
                        TrophyIM.setPresence("available", getStatusMessage());
                    else
                        TrophyIM.setPresence("available");
				
                    _div_status.removeAttribute('autoStatus');
                    loadscript.setStatusJabber("Disponível","available");
                }
            }
		
            var TimeStatus = _preferencesIM[2].split(':');

            if( TimeStatus[1] )
                _autoStatus = setTimeout( function(){
                    autoStatusHandler();
                }, parseInt(TimeStatus[1]) * _autoStatusTime );
            else
                _autoStatus = setTimeout( function(){
                    autoStatusHandler();
                }, parseInt(_autoStatusTime));
        }
	
        function autoStatusHandler()
        {
            var _div_status = ( getElement('status_jabber_expresso') != null ) ?  getElement('status_jabber_expresso') : null;
		
            if ( _div_status != null )
            {
                var _status = _div_status.style.backgroundImage;
                _status = _status.substr(_status.lastIndexOf('/') + 1);
                _status = _status.substr(0, _status.indexOf('.'));
			
                if( _status == "available" )
                {
                    if(getStatusMessage() != "")
                        TrophyIM.setPresence("xa", getStatusMessage());
                    else
                        TrophyIM.setPresence("xa");

                    _div_status.setAttribute('autoStatus','true');
				
                    loadscript.setStatusJabber("Não Disponível","xa");
                }
            }
        }

        function clrAllContacts()
        {
            getElement("JabberIMRoster").innerHTML = "";
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

        function disabledNotificationNewUsers()
        {
            var _notification	= getElement('notification_new_users_jabber') ;
            var _statusJabber	= getElement('status_jabber_expresso');

            _notification.style.display = 'none';
		
            _statusJabber.style.background = "url('"+path_jabberit+"templates/default/images/" + statusUserIM + ".gif') no-repeat";
		
            if( _timeOutNotification )
                clearTimeout(_timeOutNotification );
		
            TrophyIM.controll.notificationNewUsers = 0;
        }
	
        function enabledNotificationNewUsers()
        {
            var _notification	= getElement('notification_new_users_jabber') ;
            var _statusJabber	= getElement('status_jabber_expresso');
	
            if( _notification && _statusJabber )
            {	
                if ( _notification.style.display == 'none' )
                {
                    _notification.style.display = 'block';
				
                    _statusJabber.style.background = "url('"+path_jabberit+"templates/default/images/alert_mini.png') no-repeat";
				
                    if( _timeOutNotification )
                        clearTimeout(_timeOutNotification );

                    _timeOutNotification = setTimeout( function(){
                        enabledNotificationNewUsers();
                    }, 2000 );
                }
                else
                {
                    _notification.style.display = 'none';
				
                    _statusJabber.style.background = "url('"+path_jabberit+"templates/default/images/" + statusUserIM + ".gif') no-repeat";
				
                    if( _timeOutNotification )
                        clearTimeout(_timeOutNotification );

                    _timeOutNotification = setTimeout( function(){
                        enabledNotificationNewUsers();
                    }, 800 );
                }
            }
        }

        function fastMenu()
        {
            if( arguments.length > 0 )
            {
                var element = arguments[0];

                if( showhidden == null )
                    showhidden = new ShowHidden(300);

                var _options =	[
                ['Adicionar Contato', 'loadscript.addContact();' ],
                ['Preferências', 'loadscript.preferences();']
                ];

                if( im_chatroom == "false" )	
                {	
                    _options[2] = [ 'Sala(s) de Bate-Papo','loadscript.getListRooms();'];
                }

                var _itens = "";
			
                for( var i in _options )
                {
                    if( _options[i].constructor == Function )
                        continue;
				
                    _itens += '<img src="'+arrow_right.src+'"/>';
                    _itens += '<span style="cursor:pointer; margin:3px;" onclick='+_options[i][1]+'>';
                    _itens += _options[i][0] + '</span><br/>';
                }
			
                var _optionsItens = document.createElement("div");
                _optionsItens.id	= "fastMenu_Jabber";				
                _optionsItens.style.marginTop	= "19px";
                _optionsItens.style.marginLeft	= "-8px";
                _optionsItens.className		= "x-menu";
                _optionsItens.style.zIndex	= '999999';
                _optionsItens.innerHTML		= _itens;
                _optionsItens.onclick		= function(){
                    showhidden.hiddenObject(false);
                };
                _optionsItens.onmouseout	= function(){
                    showhidden.hiddenObject(false);
                };
                _optionsItens.onmouseover	= function(){
                    showhidden.hiddenObject(true);
                };	
										  
                showhidden.action('onmouseover', 'onmouseout', _optionsItens);
				
                element.parentNode.appendChild( _optionsItens );
            }
        }
	
        function getElement( elementId )
        {
            return document.getElementById( elementId );
        }
	
        function getBrowserCompatible()
        {
            return SnifferBrowser.isLoadApp('firefox');
        }
	
        function getPhotoUser( jid )
        {
            try
            {
                var _divPhoto = getElement( jid + '__photo' );
	
                if( _divPhoto.style.backgroundImage.indexOf('photo.png') > 0 )
                {
                    var _imgUser  = path_jabberit + 'inc/WebService.php?' + Date.parse( new Date );
                    _imgUser += '&photo_ldap=' + jid;
	
                    _divPhoto.style.backgroundImage = 'url(' + _imgUser + ')';
                }
            }catch(e){}
        }
	
        function getShowContactsOffline()
        {
            if( _preferencesIM[3] )
            {
                var showOffline = _preferencesIM[3].split(":");
			
                if( showOffline[1] === "true")
                    return true;
                else
                    return false;
            }
		
            return true;
        }
	
        function getSmiles( String )
        {
            String = String.replace( /:\)|:-\)/g	, " <img src='"+path_jabberit+"templates/default/images/smiles/1.gif'/> ");
            String = String.replace( /:-D/g			, " <img src='"+path_jabberit+"templates/default/images/smiles/2.gif'/> ");
            String = String.replace( /;-\)/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/3.gif'/> ");
            String = String.replace( /=-O/g			, " <img src='"+path_jabberit+"templates/default/images/smiles/4.gif'/> ");
            String = String.replace( /:P/g			, " <img src='"+path_jabberit+"templates/default/images/smiles/5.gif'/> ");
            String = String.replace( /8-\)/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/6.gif'/> ");
            String = String.replace( /\>:o/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/7.gif'/> ");
            String = String.replace( /:-\$/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/8.gif'/> ");
            String = String.replace( /:s|:-X/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/9.gif'/> ");
            String = String.replace( /:-\(/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/10.gif'/> ");
            String = String.replace( /:\'\(/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/11.gif'/> ");
            String = String.replace( /:\|/g			, " <img src='"+path_jabberit+"templates/default/images/smiles/12.gif'/> ");
            String = String.replace( /O:-\)/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/13.gif'/> ");
            String = String.replace( /\*\*@#%/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/14.gif'/> ");
            String = String.replace( /\(I\)/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/15.gif'/> ");
            String = String.replace( /C28I/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/16.gif'/> ");
            String = String.replace( /CS2A/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/17.gif' style='width:42px;height:36px;'/> ");
            String = String.replace( /\(CzzzzI\)/g		, " <img src='"+path_jabberit+"templates/default/images/smiles/18.gif'/> ");                
 
            return String;
        }
	
        function getStatusUserIM()
        {
            return statusUserIM;
        }
	
        function getStatusMessage()
        {
            return _statusMessage;
        }
	
        function getUserCurrent()
        {
            return userCurrent;
        }
	
        function getZindex()
        {
            return zIndex++;
        }
	
        function groupsHidden()
        {
            if( arguments.length > 0 )
            {
                var _element = arguments[0];
                _element.style.background = "url('"+path_jabberit+"templates/default/images/arrow_right.gif') no-repeat center left";
                _element.onclick = function(){
                    groupsVisible(_element);
                };
				
                // Hidden all
                var _elementNext = _element.nextSibling;
				
                while( _elementNext )
                {	
                    if( _elementNext.nodeType == 1 )
                        _elementNext.style.display = "none";
					
                    _elementNext = _elementNext.nextSibling;
                }
            }
        }
	
        function groupsVisible()
        {
            if( arguments.length > 0 )
            {
                var _element = arguments[0];
                _element.style.background = "url('"+path_jabberit+"templates/default/images/arrow_down.gif') no-repeat center left";
                _element.onclick = function(){
                    groupsHidden(_element);
                };

                // Display all
                var _elementNext = _element.nextSibling;
				
                while( _elementNext )
                {	
                    if( _elementNext.nodeType == 1 && _elementNext.nodeName.toLowerCase() == "div" )
                    {
                        var is_off = _elementNext.style.backgroundImage.indexOf("unavailable");	

                        if( is_off > 0 && !getShowContactsOffline())
                        {
                            _elementNext.style.display = "none";
                            getElement("span_show_" + _elementNext.id ).style.display = "none";
							
                        }
                        else
                        {
                            _elementNext.style.display = "block";
                            getElement("span_show_" + _elementNext.id ).style.display = "block";
                        }
                    }

                    _elementNext = _elementNext.nextSibling;
                }
            }
        }
	
        function keyPressSearch()
        {
            if( arguments.length > 0 )
            {
                var ev 		= arguments[0];
                var element	= arguments[1];
	
                if ( ev.keyCode == 13 )
                    if( element.value.length >= 3 )
                        searchUser( element.value );	
                    else
                        alert( i18n.YOUR_SEARCH_ARGUMENT_MUST_BE_LONGER_THAN_3_CHARACTERS + '.' );
            }
        }

	
        function loginPage()
        {
            var paramsLoginPage = 
            {
                'username' : ((( Base64.decode(getUserCurrent().jid) )) ? Base64.decode(getUserCurrent().jid) : ""),
                'password' : ((( Base64.decode(getUserCurrent().password) )) ? Base64.decode(getUserCurrent().password) : "") 
            }
		
            var winLoginPage =
            {	
                id_window		: "window_login_page",
                width			: 260,
                height			: 120,
                top			: 100,
                left			: 400,
                draggable		: true,
                visible		: "display",
                resizable		: true,
                zindex			: zIndex++,
                title			: "Expresso Messenger - Login",
                closeAction	: "remove",
                content		: Xtools.parse(Xtools.xml("login_page"), "loginPage.xsl", paramsLoginPage)	
            };

            _winBuild( winLoginPage );
        }

        function loadScripts(pFiles)
        {
            // Load JavaScript
            var loadJavaScript = function(pJs)
            {
                var newScript = document.createElement("script");
                newScript.setAttribute("type", "text/javascript");
                newScript.setAttribute("src", pJs );
				
                return newScript;
            };
		
            // Load CSS
            var loadStyleSheet = function(pCss)
            {
                var newStyle = document.createElement("link");
                newStyle.setAttribute("rel", "stylesheet");
                newStyle.setAttribute("type", "text/css");
                newStyle.setAttribute("href", pCss);
				
                return newStyle;
            };
		
            for(var i = 0; i < pFiles.length; i++)
            {
                if( pFiles[i].indexOf(".js") > -1 )
                    document.getElementsByTagName("head")[0].appendChild(loadJavaScript(pFiles[i]));
				
                if( pFiles[i].indexOf(".css") > -1 )
                    document.getElementsByTagName("head")[0].appendChild(loadStyleSheet(pFiles[i]));
            }
        }
	
        function notificationNewMessage()
        {
            var _doc		= document;
            var _id			= arguments[0];
            var _win_name	= _id.replace( /\W/g, '' ); 
		
            if ( windowPOPUP( _id ) )
            {
                _doc = windowPopUp[_win_name].document;
            }
		
            var oldTitle 	= _doc.title; 
            var newTitle 	= "## NOVA MENSAGEM ##"; 

            if( timeoutId == null )
            {
                timeoutId = setInterval(function()
                {
                    _doc.title = ( _doc.title == newTitle ) ? oldTitle : newTitle;
                }, 1000);
			
                configEvents( _doc, 'onclick', function()
                { 
                    clearInterval(timeoutId);
                    _doc.title	= oldTitle;
                    timeoutId		= null;
                });
			
                configEvents( _doc, 'onkeypress', function()
                { 
                    clearInterval(timeoutId);
                    _doc.title	= oldTitle;
                    timeoutId		= null;
                });
            }
        }

        function optionsItensContact()
        {
            if( arguments.length > 0 )
            {
                var jid 	= arguments[0];
                var coord	= arguments[1];
                var element = getElement('itenContact_' + jid );
                var action	= ( element.getAttribute("subscription") === "not-in-roster" ) ? "Adicionar" : "Autorizar"; 	
			
                if( showhidden == null )
                    showhidden = new ShowHidden(300);

                var _options = [
                [ action , 'loadscript.setAutorization(\''+jid+'\')'],
                ['Remover' , 'loadscript.removeContact(\''+jid+'\')'],
                ['Renomear' , 'loadscript.renameContact(\''+jid+'\')'],
                ['Trocar grupo' , 'loadscript.renameGroup(\''+jid+'\')']
                ];

                var _itens = "";
			
                for( var i in _options )
                {
                    if( typeof(_options[i]) == "object")
                    {
                        _itens += '<img src="'+arrow_right.src+'"/>';
                        _itens += '<span style="cursor:pointer;margin:3px;font-weight:normal;" onclick='+_options[i][1]+'>';
                        _itens += _options[i][0] + '</span><br/>';
                    }
                }
			
                var _optionsItens = document.createElement("div");
                _optionsItens.className		= "x-menu";
                _optionsItens.style.top		= coord.Y;
                _optionsItens.style.left	= ( coord.X - element.offsetLeft );
                _optionsItens.style.zIndex	= getZindex();
                _optionsItens.innerHTML		= _itens;  
                _optionsItens.onclick		= function(){
                    showhidden.hiddenObject(false);
                };
                _optionsItens.onmouseout	= function(){
                    showhidden.hiddenObject(false);
                };	
                _optionsItens.onmouseover	= function(){
                    showhidden.hiddenObject(true);
                };
				
                showhidden.action('onmouseover', 'onmouseout', _optionsItens);
				
                window.document.body.appendChild(_optionsItens);
            }
        }

        function parse()
        {
            if( arguments.length == 2 )
                return Xtools.parse(Xtools.xml(arguments[0]), arguments[1] );
		
            if( arguments.length === 3 )
                return Xtools.parse(Xtools.xml(arguments[0]), arguments[1], arguments[2] );
        }
	
        function preferences()
        {
            var paramPreferences =
            {
                'path'  : path,
                'lang1' : 'Suas Preferências',
                'lang2' : 'Conexão',	
                'lang3' : 'Conectar Automaticamente o IM',
                'lang4' : 'Usuários OffLine',
                'lang5' : 'Exibir amigos Offline',
                'lang6' : 'Salvar',
                'lang7' : 'Cancelar',
                'lang8' : 'Janela de Contatos',
                'lang9' : 'Abrir janela como Pop-up',
                'lang10' : 'Ausente',
                'lang11' : 'Definir status de ausente depois de',
                'lang12' : 'minutos',	
                'lang13' : 'Mostrar Contatos',	
                'lang14' : 'Mostrar contatos desconectados',
                'langYes': 'Sim',
                'langNo' : 'Não'											   
            };
		
		
            var _win_preferences =
            { 
                id_window	 : "jabberit_preferences",
                width		 : 430,
                height		 : 350,
                top		 	 : 150,
                left		 : 100,
                draggable	 : true,
                visible	 	 : "display",
                resizable	 : true,
                zindex		 : zIndex++,
                title		 : 'Expresso Messenger - Preferências',
                closeAction  : "remove",
                content		 : Xtools.parse(Xtools.xml('preferences'), 'preferences.xsl', paramPreferences)
            };

            _winBuild(_win_preferences);
		
		
            var _pButtons = {
                'lang1' : 'Salvar',
                'lang2' : 'Fechar',
                'onclickClose' : '_winBuild("jabberit_preferences","remove");',
                'onclickSubmit' : 'javascript:loadscript.setPreferences();'
            }; 
		
            document.getElementById('buttons_preferences_jabberit').innerHTML = Xtools.parse(Xtools.xml('buttons_main'), 'buttons.xsl', _pButtons);
		
            // Element openWindowJabberit
            var value1			= _preferencesIM[0].split(':');
            var element1		= document.getElementById(value1[0]);
            var valueSelect1	= value1[1];
		
            for(var i = 0; i < element1.options.length; i++)
                if( element1.options[i].value == valueSelect1 )
                    element1.options[i].selected = true;

            // Element openWindowJabberitPopUp
            var value2			= _preferencesIM[1].split(':');
			
            // Element flagAwayIM
            var value3		= _preferencesIM[2].split(':');
            var element3	= document.getElementById(value3[0]);
            element3.value	= value3[1];
		
            // Element showContactsOfflineJabberit
            var value4			= _preferencesIM[3].split(':');
            var element4		= document.getElementById(value4[0]);
            var valueSelect4	= value4[1];
            for(var i = 0; i < element4.options.length; i++)
                if( element4.options[i].value == valueSelect4 )
                    element4.options[i].selected = true;
        }
	
        function removeContact( jid )
        {
            TrophyIM.removeContact( jid );
        }
	
        function removeElement( )
        {
            if( arguments.length > 0 )
            {
                var _element = arguments[0] 
			
                if( _element != null )
                {
                    _element.parentNode.removeChild( _element );
                }
            }
        }
	
        function removeGroup()
        {
            var _parent = arguments[0];
		
            if( _parent.childNodes.length <= 2 )
                _parent.parentNode.removeChild(_parent);
        }
	
        function renameContact()
        {
            if( arguments.length > 0 )
            {
                var _jid	= arguments[0];
			
                TrophyIM.renameContact( _jid );
            }
        }
	
        function renameGroup()
        {
            if( arguments.length > 0 )
            {
                var _jid 	= arguments[0];
			
                TrophyIM.renameGroup( _jid );
            }
        }
	
        function rosterDiv()
        {
            var _rosterDiv = function()
            {

                var winRosterDiv = 
                {
                    id_window		: "window_Roster_im",
                    width			: 250,
                    height			: 410,
                    top			: 50,
                    left			: -1500,
                    leftOld		: 50,
                    draggable		: true,
                    visible		: "display",
                    resizable		: true,
                    zindex			: zIndex++,
                    title			: "Expresso Messenger - Contatos",
                    closeAction	: "hidden",
                    content		: ""	
                };

                if( _preferencesIM[0] == "openWindowJabberit:false" )
                {
                    winRosterDiv.left		= 50;
                    winRosterDiv.leftOld	= -1500;
                }
			
                if( SnifferBrowser.isLoadApp() )
                {	
                	var _idUser	= Base64.decode(getUserCurrent().jid);
				
                    var paramListContact = 
                    {
                        'idUser'	: _idUser,
                        'full_name'	: (( fullName.length < 25 ) ? fullName : ( fullName.substring( 0, 25) + "...")),
                        'path_jabberit' : path_jabberit,
                        'help_expresso'	: help_expresso,
                        'zIndex_'		: zIndex++
                    };
		
                    winRosterDiv.content = Xtools.parse(Xtools.xml("contacts_list"),"contactsList.xsl", paramListContact)	
				
                }
                else
                {
                    var paramList = 
                    {
                        'path' : path_phpgwapi
                    };
				
                    winRosterDiv.width		= 280;
                    winRosterDiv.height		= 430;
                    winRosterDiv.content	= Xtools.parse( Xtools.xml("navigator"), path_phpgwapi + "templates/default/xsl/navigatorCompatible.xsl" , paramList);
                }
			
                _winBuild( winRosterDiv );				

                // Photo User
                getPhotoUser(_idUser);
            }
		
            setTimeout( function(){
                _rosterDiv();
            }, 200 );
        }

        function searchUser()
        {
            var _input	= getElement('search_user_jabber');
		
            if( _input.value.length >= 3 )
                addUser.search();
            else
                alert( i18n.YOUR_SEARCH_ARGUMENT_MUST_BE_LONGER_THAN_3_CHARACTERS + '.' );
        }
	
        function setAutorization()
        {
            var divItenContact = null;
		
            if( arguments.length > 0 )
            {
                var jidTo = arguments[0];
		
                if( getElement('itenContact_' + jidTo) )
                    divItenContact = getElement('itenContact_' + jidTo );
            }
		
            if( divItenContact )
            {	
                var subscription = divItenContact.getAttribute('subscription');

                switch(subscription)
                {
                    case 'from':
					
                        TrophyIM.setAutorization( jidTo, Base64.decode(this.getUserCurrent().jid ), 'subscribe');
                        break;

                    case 'subscribe' :
					
                        TrophyIM.setAutorization( jidTo, Base64.decode(this.getUserCurrent().jid ), 'subscribed');
                        break;
    				
                    case 'none' :					
    				
                        TrophyIM.setAutorization( jidTo, Base64.decode(this.getUserCurrent().jid ), 'subscribe');
                        break;
    				
                    case 'to' :				
    				
                        TrophyIM.setAutorization( jidTo, Base64.decode(this.getUserCurrent().jid ), 'subscribed');    				
                        removeElement( getElement('itenContactNotification_' + jidTo ) );    				
                        break;

                    case 'not-in-roster':
	   				
                        TrophyIM.setAutorization( jidTo, Base64.decode(this.getUserCurrent().jid), 'subscribed');
                        addUser.add( jidTo );
                        break;
                }
            }
        }
	
        function setMessageStatus()
        {
            if( arguments.length > 0 )
            {
                var _element = arguments[0];
                var _parent	 = _element.parentNode;			

                if( _element.nodeName.toLowerCase() == "label")
                {
                    var _input				= document.createElement("input"); 
                    _input.size			= "35";
                    _input.maxlength	= "35";
                    _input.style.border = "0";
                    _input.value	 	= _element.innerHTML;
					
                    // OnkeyUp
                    configEvents( _input, "onkeyup", function(e)
                    {
                        if( e.keyCode == 13 ) loadscript.setMessageStatus(_input, _element);
                    }
                    );
				
                    // Onblur	
                    configEvents(_input, "onblur", function(){
                        loadscript.setMessageStatus(_input, _element)
                        });		

			    
                    if( _parent != null )
                    {	
                        // Remove label
                        if( _element != null )
                            _parent.removeChild( _element );
						
                        // Add Input
                        if( _input != null ) 
                            _parent.appendChild( _input );
                    }

                    _input.focus();
                    _input.select();
                }
                else
                {
                    var _label		= arguments[1];
                    _statusMessage	= _element.value.replace(/^\(+|\)+$/g,"");
				
                    if( ( _statusMessage = _statusMessage.replace(/^\s+|\s+$|^\n|\n$/g,"") ) != "")
                        _label.innerHTML = "( " + _statusMessage + " )";
                    else
                        _label.innerHTML = "( " + i18n.TYPE_YOUR_MESSAGE_HERE_STATUS + " )";
				
                    if( _parent != null )
                    {	
                        // Remove Input
                        if( _element != null )
                            _parent.removeChild( _element );
						
                        // Add Label
                        if( _label != null ) 
                            _parent.appendChild( _label );
                    }
				
                    // Send Status Message
                    _statusMessage = ( ( _statusMessage !=  i18n.TYPE_YOUR_MESSAGE_HERE_STATUS ) ? _statusMessage : "" );				
				
                    TrophyIM.setPresence("status", _statusMessage );
                }	
            }
        }
	
        function setPreferences()
        {
            // Element openWindowJabberit
            var elementOpenW	= document.getElementById('openWindowJabberit');
            var value			= '';
		
            for(var i = 0 ; i < elementOpenW.options.length; i++)
                if( elementOpenW.options[i].selected == true)
                {
                    value = 'preferences1=openWindowJabberit:' + elementOpenW.options[i].value;
                    _preferencesIM[0] = 'openWindowJabberit:' + elementOpenW.options[i].value;
                }

            // Element openWindowJabberitPopUp
            value += '&preferences2=openWindowJabberitPopUp:false';
            _preferencesIM[1] = 'openWindowJabberitPopUp:false';
		
            // Element flagAwayIM
            var elementFlagIM = document.getElementById('flagAwayIM');
		
            if( elementFlagIM.value.length > 0 && parseInt(elementFlagIM.value) > 0 )
            {
                _preferencesIM[2] = 'flagAwayIM:' + elementFlagIM.value;
                value += '&preferences3=flagAwayIM:' + elementFlagIM.value;
            }
            else
            {
                alert('Informe um valor igual ou maior que 1!');
                return false;
            }

            // Element showContactsOfflineJabberit
            var elementShowOffline	= document.getElementById('showContactsOfflineJabberit');
		
            for(var i = 0 ; i < elementShowOffline.options.length; i++)
                if( elementShowOffline.options[i].selected == true)
                {
                    _preferencesIM[3] = 'showContactsOfflineJabberit:' + elementShowOffline.options[i].value;
                    value += '&preferences4=showContactsOfflineJabberit:' + elementShowOffline.options[i].value;
                }
		
            // Save Preferences
            conn.go('p.pf.setPreferences',
                function(data)
                {
                    if( data == 'false' )
                    {
                        alert('Erro salvando suas preferências!');
                    }

                    _winBuild('jabberit_preferences', 'remove');
                },
                value);
        }
	
        function setPresence()
        {
            if( arguments.length > 0 )
            {
                var element = arguments[0];
			
                if( showhidden == null )
                    showhidden = new ShowHidden(300);
			
                var _status = [
                ['Afastado', 'away', '<img src="'+path_jabberit+'templates/default/images/away.gif" />'],
                ['Disponível', 'available', '<img src="'+path_jabberit+'templates/default/images/available.gif" />'],
                ['Livre p/ Conversa', 'chat', '<img src="'+path_jabberit+'templates/default/images/chat.gif" />'],
                ['Não Disponível', 'xa', '<img src="'+path_jabberit+'templates/default/images/xa.gif" />'],
                ['Ocupado', 'dnd', '<img src="'+path_jabberit+'templates/default/images/dnd.gif" />'],
                ['Desconectado', 'unavailable', '<img src="'+path_jabberit+'templates/default/images/unavailable.gif" />'],
                ['Mensagem de Status...', 'status', '<img src="'+path_jabberit+'templates/default/images/message_normal.gif" />'],				                
                ];
			
                var _itens = "";
			
                for( var i in _status )
                {
                    if( typeof( _status[i]) == "object" )
                    {
                        _itens += '<span style="cursor:pointer;" onclick="TrophyIM.setPresence(\''+_status[i][1]+'\'); loadscript.setStatusJabber(\''+_status[i][0]+'\',\''+_status[i][1]+'\');">';
                        _itens += _status[i][2]+ "<span style='margin:3px;'>" + _status[i][0] + "</span></span><br/>";
                    }
                }
			
                var _statusItens = document.createElement("div");
                _statusItens.style.marginTop	= "65px";
                _statusItens.style.marginLeft	= "67px";
                _statusItens.className		 	= "x-menu";
                _statusItens.style.zIndex	 	= '99999';
                _statusItens.innerHTML		 	= _itens;  
                _statusItens.onclick 		 	= function(){
                    showhidden.hiddenObject(false);
                };
										  
                showhidden.action('onmouseover', 'onmouseout', _statusItens);
				
                element.parentNode.onmouseout	= function(){
                    showhidden.hiddenObject(false);
                };
                element.parentNode.onmouseover	= function(){
                    showhidden.hiddenObject(true);
                };
                element.parentNode.appendChild(_statusItens);
            }
        }

        function setSelectEditable(element, top, left )
        {
            if( getElement('selectBox0') == null )
                selectEditable.create(element, top, left );
        }

        function setStatusJabber()
        {
            if( arguments.length > 0 )
            {
                if( arguments[1] != 'status' )
                {
                    var _text	= arguments[0];
                    var _img	= statusUserIM = arguments[1];
				
                    getElement('statusJabberText').innerHTML		= _text;
                    getElement('statusJabberImg').style.background	= "url('"+path_jabberit+"templates/default/images/"+_img+".gif')";
                    getElement('status_jabber_expresso').style.background = "url('"+path_jabberit+"templates/default/images/"+_img+".gif') no-repeat";
                }
            }	
        }

        function _setUserCurrent( _user )
        {
            userCurrent = 
            {
                'jid'		: _user.jid.substring(11, _user.jid.length),
                'password'	: _user.password.substring(11, _user.password.length)
            }		
        }
	
        function setUserCurrent()
        {
            if( getUserCurrent() == null )
            {
                conn.go('p.ff.data_0',
                    function(_User)
                    {
                        conn.go('p.ff.data_1',
                            function(_pass)
                            {
                                _setUserCurrent( {
                                    jid : _User, 
                                    password : _pass
                                } );
                            });
                    });
            }
        }

        var _stylesheets = [ ];
        var _links = document.getElementsByTagName( 'link' );
	
        for ( var i = 0; i < _links.length; i++ )
            if ( _links.item( i ).type && _links.item( i ).type.toLowerCase( ) == 'text/css' )
                _stylesheets[ _stylesheets.length ] = _links.item( i );  
	
        function windowPOPUP()
        {
            var _id = arguments[0];
            var _win_name = _id.replace( /\W/g, '' ); 

            if ( arguments.length == 1 )
            {
                if ( windowPopUp[_win_name] )
                    return true;
                else
                    return false;
            }
		
            if( arguments.length == 2 )
            {	
                if( !windowPopUp[_win_name] )
                {
                    windowPopUp[_win_name] = window.open( '', _win_name + '__popup', 'height=355,width=380,top=50,left=50,toolbar=no,menubar=no,resizable=no,scrollbars=no,status=no,location=no,titlebar=no');
                    var tmp = windowPopUp[_win_name].document;
				
                    tmp.write('<html><head>');
                    tmp.write('</head><body>');
                    tmp.write('</body></html>');
                    tmp.close();
				
                    for ( var i = 0; i < _stylesheets.length; i++ )
                        tmp.documentElement.getElementsByTagName( 'head' ).item(0).appendChild( _stylesheets[ i ].cloneNode( true ) );

                    var divPOP = getElement( _id + "__popUp" );
                    divPOP.style.background = "url('"+path_jabberit+"templates/default/images/icon_down.png') no-repeat";
                    divPOP.innerHTML = "PopIn";
					
                    function _close( )
                    {
                        windowPopUp[_win_name].close();
                        configEvents( divPOP ,'onclick', _close, true );
                    }
					
                    configEvents( divPOP ,'onclick', _close );

                    var _content = tmp.documentElement.getElementsByTagName( 'body' ).item(0).appendChild( getElement(_id + '__chatBox' ).parentNode );

                    _content.firstChild.scrollTop = _content.firstChild.scrollHeight;

                    configEvents( windowPopUp[_win_name] ,'onbeforeunload', function()
                    {
                        delete windowPopUp[_win_name];
                        divPOP.style.background = "url('"+path_jabberit+"templates/default/images/icon_up.png') no-repeat";
                        divPOP.innerHTML = "PopUp";
                        divPOP.onclick	= function(){
                            loadscript.windowPOPUP( _id , true );
                        };
                        _winBuild( 'window_chat_area_' + _id, "display" ).content( true );
                    });

                    _winBuild( 'window_chat_area_' + _id, 'hidden' );
                }
            }
        }
	
        function windowNotificationNewUsers()
        {
            var _users = Xtools.xml('notification_new_users');
		
            for( var user in TrophyIM.rosterObj.roster )
            {
                if ( TrophyIM.rosterObj.roster[ user ].constructor == Function )
                    continue;

                if( TrophyIM.rosterObj.roster[ user ].contact.jid != Base64.decode( loadscript.getUserCurrent().jid) )
                {
                    var _subscription = TrophyIM.rosterObj.roster[user].contact.subscription;
				
                    if ( _subscription == 'to' || _subscription == 'not-in-roster' )
                    {
                        var _user	= _users.createElement('user');
                        var _jid 	= _users.createElement('jid');
                        var _status	= _users.createElement('status');
                        _jid.appendChild( _users.createTextNode(TrophyIM.rosterObj.roster[user].contact.jid) );
                        _status.appendChild( _users.createTextNode( _subscription ) );
                        _user.appendChild( _jid );
                        _user.appendChild( _status );
                        _users.documentElement.appendChild( _user );
                    }
                }
            }
		
            var paramsNotification = 
            {
                'lang_1' : "Notificação",	
                'lang_2' : "O(s) usuário(s) abaixo pedem sua autorização.",
                'lang_3' : "Autorizar",
                'lang_4' : "Remover"
            };
		
            var winNotification =
            {	
                id_window		: "window_notification_new_users",
                width			: 400,
                height			: 300,
                top			: 100,
                left			: 400,
                draggable		: true,
                visible		: "display",
                resizable		: true,
                zindex			: zIndex++,
                title			: "Expresso Messenger - Notificação de Novos Usuários",
                closeAction	: "remove",
                content		: Xtools.parse( _users , "notificationNewUsers.xsl", paramsNotification )	
            };

            _winBuild( winNotification );
        }

        function createChatRooms()
        {
            _winBuild("window_List_Rooms_jabberit_messenger","remove");
		
            var paramCreateChatRoom = 
            {
                'lang_nameChatRoom'	: "Nome da Sala",
                'lang_nickName'		: "Apelido"
            };
		
            var winCreateChatRooms =
            {	
                id_window		: "window_create_chat_rooms",
                width			: 360,
                height			: 160,
                top			: 100,
                left			: 410,
                draggable		: true,
                visible		: "display",
                resizable		: true,
                zindex			: loadscript.getZIndex(),
                title			: "Expresso Messenger - Criar Sala de Bate Papo",
                closeAction	: "remove",
                content		: Xtools.parse( Xtools.xml("create_chat_room"), "createChatRoom.xsl", paramCreateChatRoom )
            };
		
            _winBuild( winCreateChatRooms );

            var _pButtons =
            {
                'lang1' 		: 'Ingressar',
                'lang2'			: 'Fechar',
                'onclickClose'	: '_winBuild("window_create_chat_rooms","remove");',
                'onclickSubmit'	: 'TrophyIM.createChatRooms(); _winBuild("window_create_chat_rooms","remove");'
            }; 

            // Add Buttons
            document.getElementById('buttons_createChatRoom').innerHTML = Xtools.parse(Xtools.xml('buttons_main'), 'buttons.xsl', _pButtons);
		
        }
	
        function listRooms( element )
        {
            element = element.getElementsByTagName( 'item' );

            var _roomsCount	= 0;
            var _xml		= Xtools.xml('listRooms');
            var _listRooms	= _xml.documentElement; 
		
            var show = function( )
            {
                if ( _roomsCount != element.length )
                    return false;
			
                var paramsListRooms = 
                {
                    "path_jabberit" : path_jabberit	
                };
			
                var winListRooms =
                {	
                    id_window		: "window_List_Rooms_jabberit_messenger",
                    width			: 450,
                    height			: 300,
                    top			: 100,
                    left			: 400,
                    draggable		: true,
                    visible		: "display",
                    resizable		: true,
                    zindex			: loadscript.getZIndex(),
                    title			: "Expresso Messenger - Salas de Bate Papo",
                    closeAction	: "remove",
                    content		: Xtools.parse( _xml, "listRooms.xsl", paramsListRooms )
                };
			
                _winBuild( winListRooms );
			
                var _pButtons =
                {
                    'lang1' 		: 'Criar Nova Sala',
                    'lang2'			: 'Fechar',
                    'onclickClose'	: '_winBuild("window_List_Rooms_jabberit_messenger","remove");',
                    'onclickSubmit'	: 'loadscript.createChatRooms();'
                }; 

                // Add Buttons
                document.getElementById('buttons_addChatRoom').innerHTML = Xtools.parse(Xtools.xml('buttons_main'), 'buttons.xsl', _pButtons);
            };

            var _add_room = function( _room )
            {
                _roomsCount++;

                var _ROOM 		= _xml.createElement('room'); 
                var _JIDROOM	= _xml.createElement('jidRoom');
                var nameRoom	= _room.getAttribute( 'from' );

                _ROOM.setAttribute( 'nameRoom', unescape((nameRoom.substring(0, nameRoom.indexOf("@"))).toUpperCase()) );
                _JIDROOM.appendChild( _xml.createTextNode(nameRoom) );
                _ROOM.appendChild( _JIDROOM );

                // Get fields elements;
                var _fields = _room.getElementsByTagName( 'field' );

                for ( var f = 0; f < _fields.length; f++ )
                {
                    if ( _fields[ f ].getAttribute( 'var' ) )
                    {
                        if ( _fields[ f ].firstChild.hasChildNodes( ) && _fields[ f ].getAttribute( 'var' ) == 'muc#roominfo_description' )
                        {
                            var _description	= _xml.createElement("description");
                            _description.appendChild( _xml.createTextNode( _fields[ f ].firstChild.firstChild.nodeValue ) );
                            _ROOM.appendChild( _description );
                        }

                        if ( _fields[ f ].firstChild.hasChildNodes( ) && _fields[ f ].getAttribute( 'var' ) == 'muc#roominfo_occupants' )
                        {
                            var _occupants = _xml.createElement("occupants")
                            _occupants.appendChild( _xml.createTextNode( _fields[ f ].firstChild.firstChild.nodeValue) );
                            _ROOM.appendChild( _occupants );
                        }
                    }
                }

                // Get feature elements;
                var _feature = _room.getElementsByTagName( 'feature' );
			
                for( var f = 0 ; f < _feature.length; f++ )
                {
                    if ( _feature[ f ].getAttribute( 'var' ) )
                    {
                        if( _feature[ f ].getAttribute( 'var' ) == 'muc_unsecured' )
                        {
                            var _password = _xml.createElement("password");
                            _password.appendChild( _xml.createTextNode("false") );
                            _ROOM.appendChild( _password );
                        }
                        else if( _feature[ f ].getAttribute( 'var' ) == 'muc_passwordprotected' )
                        {
                            var _password = _xml.createElement("password");
                            _password.appendChild( _xml.createTextNode("true") );
                            _ROOM.appendChild( _password );
                        }	
                    }
                }
			
                _listRooms.appendChild( _ROOM );
			
                show( );
            };
		
            var _get_room_info = function( _room )
            {
                TrophyIM.connection.sendIQ(
                    $iq( {
                        "to" : _room, 
                        "type" : "get"
                    } ).c( "query",{
                        xmlns: Strophe.NS.DISCO_INFO
                    } ),
                    _add_room,
                    function( a )
                    {
                        _roomsCount++;
		        	
                        show( );
		        		
                    }, 500 );
            };

            if( element.length > 0 )
            {
                for ( var i = 0; i < element.length; i++ )
                {
                    _get_room_info( element[ i ].getAttribute( 'jid' ) );
                }
            }
            else
            {
                show();
            }
        }
	
        function getListRooms()
        {
            TrophyIM.getListRooms();	
        }
	
        function joinRoom( jidRoom, nameRoom )
        {
            var append_nick = function( room, nick )
            {
                var room_nick = room;
	        
                if ( nick ) 
                {
                    room_nick += "/" + nick; 
                }
	        
                return room_nick;
            }
		
            if( document.getElementById( 'window_chat_room_' + jidRoom + '__main' ) != null )
            {
                _winBuild('window_chat_room_' + jidRoom, 'display');
            }
            else
            {
                var nickName = Base64.decode( loadscript.getUserCurrent().jid );
                nickName = nickName.substring(0, nickName.indexOf('@'));
		    
                var _prompt = prompt("Deseja informar um Apelido ?", nickName );
		    
                if( _prompt )
                { 	
                    _prompt = _prompt.replace(/^\s+|\s+$|^\n|\n$/g,"");
			    
                    var room_nick 	= append_nick( jidRoom, nickName );
			    
                    var nickChat	= nickName.toString();
			    	
                    if( _prompt && _prompt != "" )
                    {
                        nickChat		= _prompt.toString();
                        var room_nick	= append_nick( jidRoom, _prompt );
                    }
			    
                    TrophyIM.makeChatRoom( jidRoom , nameRoom );

                    TrophyIM.activeChatRoom.name[ TrophyIM.activeChatRoom.name.length ] = room_nick; 
			    
                    TrophyIM.joinChatRoom( room_nick);
			 
                    setTimeout(function()
                    {
                        var _message	=  nickName.toUpperCase() + " entrou como : "  + nickChat;
				   
                        TrophyIM.sendMessageChatRoom( jidRoom , _message );
				    
                    }, 500);
			    
                }
            }
	    
            _winBuild("window_List_Rooms_jabberit_messenger","remove");
        }
	
        function loadIM()
        {
            if( arguments.length > 0 )
            {
                var files = [
                path_jabberit + 'templates/default/css/button.css',
                path_jabberit + 'templates/default/css/common.css',
                path_jabberit + 'templates/default/css/selectEditableStyle.css',
                path_jabberit + 'templates/default/css/' + theme_jabberit
                ];
                // FullName
                fullName = arguments[0];
			
                // Preferences
                _preferencesIM = arguments[1].split(";");
                        
                //Path Phpgwapi
                path_phpgwapi = arguments[2];
                        
                if( !_preferencesIM[3] ) _preferencesIM[3] = "showContactsOfflineJabberit:true";
			
                loadScripts(files);
			
                setTimeout(function()
                {
                    // Object Xtools	
                    if( Xtools == null )
                        Xtools = new xtools(path_jabberit);
				
                    // Object Conector
                    if( conn == null )
                        conn = new AjaxConnector(path_jabberit);
				
                    // Object Add User
                    if( addUser == null )
                        addUser = new addUserIM(Xtools, conn);

				
                    // Object SelectEditable
                    if( selectEditable == null )
                        selectEditable = new SelectEditable();
					
                    // Add Jabber in StatusBar;
                    addIcon();
				
                    // Auto Connect
                    setTimeout(function()
                    {
                        if( _preferencesIM[0] === 'openWindowJabberit:true' )
                        {
                            if( SnifferBrowser.isLoadApp() )
                            {
                            	TrophyIM.load();
                            }
                        }
					
                    },1500);
				
                    // Auto Status
                    autoStatus();
                    configEvents( document, 'onmousemove', autoStatus );
                    configEvents( document, 'onkeypress', autoStatus );
				
                }, 2000);
            }
        }

        loadIM.prototype.adIcon				= addIcon;
        loadIM.prototype.actionButton		= actionButton;
        loadIM.prototype.addContact			= addContact;
        loadIM.prototype.addNewUser			= addNewUser;
        loadIM.prototype.clrAllContacts		= clrAllContacts;
        loadIM.prototype.configEvents		= configEvents;
        loadIM.prototype.createChatRooms	= createChatRooms;
        loadIM.prototype.disabledNotificationNewUsers	= disabledNotificationNewUsers;
        loadIM.prototype.enabledNotificationNewUsers	= enabledNotificationNewUsers;	
        loadIM.prototype.getListRooms		= getListRooms;	
        loadIM.prototype.getBrowserCompatible	= getBrowserCompatible;
        loadIM.prototype.getPhotoUser		= getPhotoUser;
        loadIM.prototype.getSmiles			= getSmiles;
        loadIM.prototype.getStatusUserIM	= getStatusUserIM;
        loadIM.prototype.getStatusMessage	= getStatusMessage;
        loadIM.prototype.getShowContactsOffline	= getShowContactsOffline;
        loadIM.prototype.getUserCurrent		= getUserCurrent;
        loadIM.prototype.getZIndex			= getZindex;
        loadIM.prototype.groupsHidden		= groupsHidden;
        loadIM.prototype.groupsVisible		= groupsVisible;
        loadIM.prototype.joinRoom			= joinRoom;	
        loadIM.prototype.keyPressSearch		= keyPressSearch;	
        loadIM.prototype.listRooms			= listRooms;
        loadIM.prototype.loginPage			= loginPage;
        loadIM.prototype.notification		= notificationNewMessage;
        loadIM.prototype.parse				= parse;
        loadIM.prototype.preferences		= preferences;
        loadIM.prototype.searchUser			= searchUser
        loadIM.prototype.setAutorization	= setAutorization;
        loadIM.prototype.setMessageStatus	= setMessageStatus;
        loadIM.prototype.setPreferences		= setPreferences;
        loadIM.prototype.setPresence		= setPresence;
        loadIM.prototype.setStatusJabber	= setStatusJabber;
        loadIM.prototype.setSelectEditable	= setSelectEditable;
        loadIM.prototype.setUserCurrent		= setUserCurrent;
        loadIM.prototype.removeContact		= removeContact;
        loadIM.prototype.removeElement		= removeElement;
        loadIM.prototype.removeGroup		= removeGroup;
        loadIM.prototype.renameContact		= renameContact;
        loadIM.prototype.renameGroup		= renameGroup;
        loadIM.prototype.rosterDiv			= rosterDiv;
        loadIM.prototype.windowNotificationNewUsers  = windowNotificationNewUsers;
        loadIM.prototype.windowPOPUP		= windowPOPUP;
	
        window.LoadIM = loadIM;
	
        // Necessário para não ocasionar problema no ExpressoMail
        // quando os itens abaixo não são criados pelo próprio ExpressoMail
        if( SnifferBrowser.isLoadApp('msie') )
        {
            configEvents( window, 'onload', function( )
            {
                if ( ! document.getElementById( 'cc_msg_err_serialize_data_unknown' ) )
                {
                    var fix	= document.createElement('input');
                    fix.type	= 'hidden';
                    fix.id		= 'cc_msg_err_serialize_data_unknown';
				
                    document.appendChild( fix );
                }
                if ( ! window.showMessage )
                    window.showMessage = function(){};
            });
        }
	
    })();