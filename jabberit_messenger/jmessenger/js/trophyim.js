/** Object: DOMObjects
 *  This class contains builders for all the DOM objects needed by TrophyIM
 */
DOMObjects = {
    /** Function: xmlParse
     *  Cross-browser alternative to using innerHTML
     *  Parses given string, returns valid DOM HTML object
     *
     *  Parameters:
     *    (String) xml - the xml string to parse
     */
    xmlParse : function(xmlString) {
        var xmlObj = this.xmlRender(xmlString);
        if(xmlObj) {
            try { //Firefox, Gecko, etc
                if (this.processor == undefined) {
                    this.processor = new XSLTProcessor();
                    this.processor.importStylesheet(this.xmlRender(
                        '<xsl:stylesheet version="1.0"\
                    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">\
                    <xsl:output method="html" indent="yes"/><xsl:template\
                    match="@*|node()"><xsl:copy><xsl:copy-of\
                    select="@*|node()"/></xsl:copy></xsl:template>\
                    </xsl:stylesheet>'));
                }
                var htmlObj =
                this.processor.transformToDocument(xmlObj).documentElement;
                //Safari has a quirk where it wraps dom elements in <html><body>
                if (htmlObj.tagName.toLowerCase() == 'html') {
                    htmlObj = htmlObj.firstChild.firstChild;
                }
                return document.importNode(htmlObj, true);
            } catch(e) {
                try { //IE is so very very special
                    var htmlObj = document.importNode(xmlObj.documentElement, true);
                    if (htmlObj.tagName.toLowerCase() == "div") {
                        var div_wrapper = document.createElement('div');
                        div_wrapper.appendChild(htmlObj);
                        if(div_wrapper.innerHTML) {
                            div_wrapper.innerHTML = div_wrapper.innerHTML;
                        }
                        htmlObj = div_wrapper.firstChild;
                    }
                    return htmlObj;
                } catch(e) {
                    alert("TrophyIM Error: Cannot add html to page " + e.message);
                }
            }
        }
    },
    /** Function: xmlRender
     *  Uses browser-specific methods to turn given string into xml object
     *
     *  Parameters:
     *    (String) xml - the xml string to parse
     */
    xmlRender : function(xmlString) {
        try {//IE
            var renderObj = new ActiveXObject("Microsoft.XMLDOM");
            renderObj.async="false";
            if(xmlString) {
                renderObj.loadXML(xmlString);
            }
        } catch (e) {
            try { //Firefox, Gecko, etc
                if (this.parser == undefined) {
                    this.parser = new DOMParser();
                }
                var renderObj = this.parser.parseFromString(xmlString,
                    "application/xml");
            } catch(e) {
                alert("TrophyIM Error: Cannot create new html for page");
            }
        }

        return renderObj;
    },
    /** Function: getHTML
     *  Returns named HTML snippet as DOM object
     *
     *  Parameters:
     *    (String) name - name of HTML snippet to retrieve (see HTMLSnippets
     *    object)
     */
    getHTML : function(page)
    {
        return this.xmlParse(HTMLSnippets[page]);
    },
	
    /** Function: getScript
     *  Returns script object with src to given script
     *
     *  Parameters:
     *    (String) script - name of script to put in src attribute of script
     *    element
     */
    getScript : function(script)
    {
        var newscript = document.createElement('script');
        newscript.setAttribute('src', script);
        newscript.setAttribute('type', 'text/javascript');
        return newscript;
    }
};

/** Object: TrophyIM
 *
 *  This is the actual TrophyIM application.  It searches for the
 *  'trophyimclient' element and inserts itself into that.
 */
TrophyIM = {
		

    controll : {
        notificationNewUsers : 0
    },	
    
    /** AutoConnection
	* 
	*/	
		
    autoConnection : {
        connect : true
    },

    /** Active Chat Room
	 * 
	 */
	
    activeChatRoom : {
        name : []
    },
	
    /** Object: chatHistory
    *
    *  Stores chat history (last 10 message) and current presence of active
    *  chat tabs.  Indexed by jid.
    */
	
    chatHistory : {},
	
    /** Constants:
    *
    *    (Boolean) stale_roster - roster is stale and needs to be rewritten.
    */
	
    constants : {
        stale_roster: false
    },
	
    /** PosWindow
	 * 
	 */	
    posWindow : {
        left : 400, 
        top : 100
    },	
		
    /** StatusConnection
	 * 
	 */

    statusConn : {
        connected : false
    },
	
    /** TimeOut Render Roster
	 * 
	 * 
	 */
	
    _timeOut : {
        renderRoster : null
    },
	
	
    /** Remove Contact ( type = set )
	 * 
	 * 
	 */
	
    removeResult : {
        idResult : []
    },
	
    /** Function: setCookie
     *
     *  Sets cookie name/value pair.  Date and path are auto-selected.
     *
     *  Parameters:
     *    (String) name - the name of the cookie variable
     *    (String) value - the value of the cookie variable
     */
    
    setCookie : function(name, value)
    {
        var expire = new Date();
        expire.setDate(expire.getDate() + 365);
        document.cookie = name + "=" + value + "; expires=" + expire.toGMTString();
    },
    
    /** Function: delCookie
     *
     *  Deletes cookie
     *
     *  Parameters:
     *    (String) name) - the name of the cookie to delete
     */
    
    delCookie : function(name)
    {
        var expire = new Date();
        expire.setDate(expire.getDate() - 365);
        document.cookie = name + "= ; expires=" + expire.toGMTString();
        delete TrophyIM.cookies[name];
    },
    
    /** Function: getCookies
     *
     *  Retrieves all trophyim cookies into an indexed object.  Inteneded to be
     *  called once, at which time the app refers to the returned object instead
     *  of re-parsing the cookie string every time.
     *
     *  Each cookie is also re-applied so as to refresh the expiry date.
     */
    
    getCookies : function()
    {
        var cObj = {};
        var cookies = document.cookie.split(';');
        
        for(var i = 0 ; i < cookies.length; i++ )
        {
            while ( cookies[i].charAt(0) == ' ')
            { 
                cookies[i] = cookies[i].substring(1,cookies[i].length);
            }
        	
            if (cookies[i].substr(0, 8) == "trophyim")
            {
                var nvpair = cookies[i].split("=", 2);
                cObj[nvpair[0]] = nvpair[1];
                TrophyIM.setCookie(nvpair[0], nvpair[1]);
            }
        }
        
        return cObj;
    },
	
    /** Function: load
     *
     *  This function searches for the trophyimclient div and loads the client
     *  into it.
     */

    load : function()
    {
        if( loadscript.getUserCurrent() == null )
        {
            loadscript.setUserCurrent();	 
        }	 

        if ( !TrophyIM.statusConn.connected )
        {
            TrophyIM.cookies = TrophyIM.getCookies();

            //Wait a second to give scripts time to load
            setTimeout( "TrophyIM.showLogin()", 550 );
        }
        else
        {
            loadscript.rosterDiv();
        }
    },

    /** Function: storeData
     *
     *  Store all our data in the JSONStore, if it is active
     */
     
    storeData : function()
    {
        if ( TrophyIM.connection && TrophyIM.connection.connected )
        {
            TrophyIM.setCookie('trophyim_bosh_xid', TrophyIM.connection.jid + "|" +
                TrophyIM.connection.sid + "|" +  TrophyIM.connection.rid);
            TrophyIM.rosterObj.save();
        }
    },
    
    /**  Function: showlogin
     *
     *   This function clears out the IM box and either redisplays the login
     *   page, or re-attaches to Strophe, preserving the logging div if it
     *   exists, or creating a new one of we are re-attaching.
     */
     
    showLogin : function()
    {
        /**
         * 
         * JSON is the last script to load, so we wait on it
		 * Added Strophe check too because of bug where it's sometimes missing
		 * 
		 */

        if ( typeof(JSON) != undefined && typeof(Strophe) != undefined )
        {
            TrophyIM.JSONStore = new TrophyIMJSONStore();
        	
            if ( TrophyIM.JSONStore.store_working && TrophyIM.cookies['trophyim_bosh_xid'] )
            {
                var xids = TrophyIM.cookies['trophyim_bosh_xid'].split("|");
                TrophyIM.delCookie('trophyim_bosh_xid');
                TrophyIM.constants.stale_roster = true;
    			
                TrophyIM.connection				= new Strophe.Connection(TROPHYIM_BOSH_SERVICE);
                TrophyIM.connection.rawInput	= TrophyIM.rawInput;
                TrophyIM.connection.rawOutput	= TrophyIM.rawOutput;
                //Strophe.log = TrophyIM.log;
                Strophe.info('Attempting Strophe attach.');
                TrophyIM.connection.attach(xids[0], xids[1], xids[2], TrophyIM.onConnect);
                TrophyIM.onConnect(Strophe.Status.CONNECTED);
            }
            else
            {
                // List Contact
                loadscript.rosterDiv();

                // Get User Current;
                var _getUserCurrent = null;
                _getUserCurrent = loadscript.getUserCurrent();
				
                if( _getUserCurrent == null )
                {	
                    setTimeout( "TrophyIM.showLogin()", 500 );
                }
                else
                {
                    TrophyIM.login( Base64.decode( _getUserCurrent.jid ), Base64.decode( _getUserCurrent.password ));
                }
            }
        }
        else
        {
            setTimeout("TrophyIM.showLogin()", 500);
        }
    },
    
    /** Function: log
     *
     *  This function logs the given message in the trophyimlog div
     *
     *  Parameter: (String) msg - the message to log
     */
    
    log : function(level, msg)
    {
        if (TrophyIM.logging_div && level >= TROPHYIM_LOGLEVEL)
        {
            while(TrophyIM.logging_div.childNodes.length > TROPHYIM_LOG_LINES)
            {
                TrophyIM.logging_div.removeChild( TrophyIM.logging_div.firstChild );
            }
            
            var msg_div = document.createElement('div');
            msg_div.className = 'trophyimlogitem';
            msg_div.appendChild(document.createTextNode(msg));
            
            TrophyIM.logging_div.appendChild(msg_div);
            TrophyIM.logging_div.scrollTop = TrophyIM.logging_div.scrollHeight;
        }
    },
	
    /** Function: rawInput
     *
     *  This logs the packets actually recieved by strophe at the debug level
     */
    rawInput : function (data)
    {
        Strophe.debug("RECV: " + data);
    },
	
    /** Function: rawInput
     *
     *  This logs the packets actually recieved by strophe at the debug level
     */
    rawOutput : function (data)
    {
        Strophe.debug("SEND: " + data);
    },
	
    /** Function: login
     *
     *  This function logs into server using information given on login page.
     *  Since the login page is where the logging checkbox is, it makes or
     *  removes the logging div and cookie accordingly.
     *
     */
    login : function()
    {
        if ( TrophyIM.JSONStore.store_working )
        { 
            //In case they never logged out
            TrophyIM.JSONStore.delData(['groups','roster', 'active_chat', 'chat_history']);
        }

        TrophyIM.connection				= new Strophe.Connection(TROPHYIM_BOSH_SERVICE);
        TrophyIM.connection.rawInput	= TrophyIM.rawInput;
        TrophyIM.connection.rawOutput	= TrophyIM.rawOutput;
        //Strophe.log 					= TrophyIM.log;
        
        if ( arguments.length > 0 )
        {
            var barejid = arguments[0];
            var password = arguments[1];
			
            TrophyIM.connection.connect( barejid + TROPHYIM_RESOURCE, password, TrophyIM.onConnect );
        }
        else
        {
			
            var barejid		= document.getElementById('trophyimjid').value
            var fulljid		= barejid + TROPHYIM_RESOURCE;
            var password	= document.getElementById('trophyimpass').value;
            var button		= document.getElementById('trophyimconnect');
			
            loadscript.setUserCurrent( barejid, password);
			
            if ( button.value == 'connect' )
            {
                button.value = 'disconnect';
                //TrophyIM.connection.connect( fulljid , password, TrophyIM.onConnect );
				
                TrophyIM.login( barejid, password );
                _winBuild('window_login_page', 'remove');
            }
        }

        TrophyIM.setCookie('trophyimjid', barejid);
    },
	
    /** Function: logout
     *
     *  Logs into fresh session through Strophe, purging any old data.
     */
    logout : function()
    {
        TrophyIM.autoConnection.connect = false;
    	
        TrophyIM.delCookie('trophyim_bosh_xid');
        
        delete TrophyIM['cookies']['trophyim_bosh_xid'];
        
        TrophyIM.connection.disconnect();
    },
	
    /** Function onConnect
     *
     *  Callback given to Strophe upon connection to BOSH proxy.
     */
    onConnect : function(status)
    {
        var loading_gif = document.getElementById("JabberIMRosterLoadingGif");
		
        if( status == Strophe.Status.CONNECTING )
        {
            loading_gif.style.display = "block";
            Strophe.info('Strophe is connecting.');
        }
		
        if( status == Strophe.Status.CONNFAIL )
        {
            TrophyIM.delCookie('trophyim_bosh_xid');
            TrophyIM.statusConn.connected = false;
            loading_gif.style.display = "block";
        }
		
        if( status == Strophe.Status.DISCONNECTING )
        {
            TrophyIM.statusConn.connected = false;
        }
		
        if( status == Strophe.Status.DISCONNECTED )
        {
            if( TrophyIM.autoConnection.connect )
            {
                loading_gif.style.display = "block";
				
                TrophyIM.delCookie('trophyim_bosh_xid');
	            
                TrophyIM.statusConn.connected = false;
	            
                setTimeout(function()
                {
                    TrophyIM.showLogin();
		            
                },10000);
				
                loadscript.clrAllContacts();	
	            
                loadscript.setStatusJabber(i18n.STATUS_ANAVAILABLE,"unavailable");
	            
                delete TrophyIM.rosterObj.roster;
                delete TrophyIM.rosterObj.groups;
            }
        }
		
        if( status == Strophe.Status.CONNECTED )
        {
            loadscript.setStatusJabber(i18n.STATUS_AVAILABLE,'available');
            TrophyIM.statusConn.connected = true;
            TrophyIM.showClient();
        }
    },

    /** Function: showClient
     *
     *  This clears out the main div and puts in the main client.  It also
     *  registers all the handlers for Strophe to call in the client.
     */
    showClient : function()
    {
        TrophyIM.setCookie('trophyim_bosh_xid', TrophyIM.connection.jid + "|" +
            TrophyIM.connection.sid + "|" +  TrophyIM.connection.rid);
		
        TrophyIM.rosterObj = new TrophyIMRoster();
        TrophyIM.connection.addHandler(TrophyIM.onVersion, Strophe.NS.VERSION, 'iq', null, null, null);
        TrophyIM.connection.addHandler(TrophyIM.onRoster, Strophe.NS.ROSTER, 'iq', null, null, null);
        TrophyIM.connection.addHandler(TrophyIM.onPresence, null, 'presence', null, null, null);
        TrophyIM.connection.addHandler(TrophyIM.onMessage, null, 'message', null, null,  null);
        
        //Get roster then announce presence.
        TrophyIM.connection.send($iq({
            type: 'get', 
            xmlns: Strophe.NS.CLIENT
            }).c('query', {
            xmlns: Strophe.NS.ROSTER
            }).tree());
        TrophyIM.connection.send($pres().tree());
        setTimeout( TrophyIM.renderRoster, 1000);
    },
	
    /** Function: clearClient
     *
     *  Clears out client div, preserving and returning existing logging_div if
     *  one exists
     */
     
    clearClient : function()
    {
        if(TrophyIM.logging_div)
        {
            var logging_div = TrophyIM.client_div.removeChild(document.getElementById('trophyimlog'));
        }
        else
        {
            var logging_div = null;
        }
        
        while(TrophyIM.client_div.childNodes.length > 0)
        {
            TrophyIM.client_div.removeChild(TrophyIM.client_div.firstChild);
        }
        
        return logging_div;
    },
    
    /** Function: onVersion
     *
     *  jabber:iq:version query handler
     */
     
    onVersion : function(msg)
    {
        Strophe.debug("Version handler");
        if (msg.getAttribute('type') == 'get')
        {
            var from = msg.getAttribute('from');
            var to = msg.getAttribute('to');
            var id = msg.getAttribute('id');
            var reply = $iq({
                type: 'result', 
                to: from, 
                from: to, 
                id: id
            }).c('query',

            {
                name: "TrophyIM", 
                version: TROPHYIM_VERSION, 
                os:
                "Javascript-capable browser"
            });
            TrophyIM.connection.send(reply.tree());
        }
        return true;
    },
    
    /** Function: onRoster
     *
     *  Roster iq handler
     */
    
    onRoster : function(msg)
    {
        var roster_items = msg.firstChild.getElementsByTagName('item');
		
        for (var i = 0; i < roster_items.length; i++)
        {
            with(roster_items[i])
            {
                var groups 		= getElementsByTagName('group');	
                var group_array	= [];
				
                for( var g = 0 ; g < groups.length; g++ )
                {
                    if( groups[g].hasChildNodes() )
                        group_array[group_array.length] = groups[g].firstChild.nodeValue;
                }

                if( getAttribute('ask') && getAttribute('ask').toString() === "subscribe" ) 
                {
                    if( getAttribute('subscription').toString() === "none" )
                    {
                        TrophyIM.rosterObj.addContact( getAttribute('jid'), getAttribute('ask'), getAttribute('name'), group_array );
                    }
					
                    if( getAttribute('subscription').toString() === "remove" )
                    {
                        TrophyIM.rosterObj.removeContact( getAttribute('jid').toString() );
                    }
                }
                else
                {
                    if( ( getAttribute('ask') == null && getAttribute('subscription').toString() === "remove" ) || getAttribute('subscription').toString() === "remove" )
                    {
                        TrophyIM.rosterObj.removeContact( getAttribute('jid').toString() );
                    }
                    else
                    {
                        TrophyIM.rosterObj.addContact( getAttribute('jid'), getAttribute('subscription'), getAttribute('name'), group_array );
                    }
                }
                }
        }

        if ( msg.getAttribute('type') == 'set' )
        {
            var _iq = $iq({
                type: 'reply', 
                id: msg.getAttribute('id'), 
                to: msg.getAttribute('from')
                });
            TrophyIM.connection.send( _iq.tree());
        }

        return true;
    },
    
    /** Function: onPresence
     *
     *  Presence Handler
     */
    
    onPresence : function(msg)
    {
        // Get Presences ChatRoom
        TrophyIM.onPresenceChatRoom( msg );

        var type		= msg.getAttribute('type') ? msg.getAttribute('type') : 'available';
        var show 		= msg.getElementsByTagName('show').length ? Strophe.getText(msg.getElementsByTagName('show')[0]) : type;
        var status 		= msg.getElementsByTagName('status').length ? Strophe.getText(msg.getElementsByTagName('status')[0]) : '';
        var priority	= msg.getElementsByTagName('priority').length ? parseInt(Strophe.getText(msg.getElementsByTagName('priority')[0])) : 0;

        if( msg.getAttribute('from').toString().indexOf( TROPHYIM_CHATROOM ) < 0 )
        {	
            var _from = Strophe.getBareJidFromJid( msg.getAttribute('from') );
            var _flag = true;

            if( TrophyIM.removeResult.idResult.length > 0 )
            {
                for( var i = 0 ; i < TrophyIM.removeResult.idResult.length; i++ )
                {
                    if( TrophyIM.removeResult.idResult[i] == _from )
                    {
                        _flag = false;
    					
                        TrophyIM.removeResult.idResult.splice(i,1);
    					
                        i--;
    					
                        if( show.toLowerCase() === 'subscribe' )
                            _flag = true;
                    }
                }
            }
    		
            if( _flag )
                TrophyIM.rosterObj.setPresence( msg.getAttribute('from'), priority, show, status );
        }

        return true;
    },

    /** Function : onPresenceChatRoom
     * 
     * Presence ChatRoom Handler
     */
    
    onPresenceChatRoom : function(msg)
    {
        var xquery      = msg.getElementsByTagName("x");
        var _error      = msg.getElementsByTagName("error");
       
       
        if( _error.length > 0 )
        {    
            /* Room creation is denied by service policy;
             *
             *  <error code='403' type='auth'>
             *      <forbidden xmlns='urn:ietf:params:xml:ns:xmpp-stanzas'/>
             *      <text xmlns='urn:ietf:params:xml:ns:xmpp-stanzas'>Room creation is denied by service policy</text>
             *  </error>       
             */
            
            for ( var i = 0; i < _error.length; i++ )
            {
                if ( _error[i].getElementsByTagName("text") )
                {    
                    var _errorMsg = Strophe.getText( _error[i].getElementsByTagName("text")[0] );
                    
                    if( _errorMsg == "Room creation is denied by service policy" )
                    {
                        alert( i18n.ROOM_CREATION_IS_DENIED_BY_SERVICE_POLICY );
                    }
                    else
                    {
                        alert( " Informe ao seu Administrador ERRO : \n" + _errorMsg );
                    }    
                        
                }
            }
        }
        else
        {
            if ( xquery.length > 0 )
            {
                for ( var i = 0; i < xquery.length; i++ )
                {
                    var xmlns = xquery[i].getAttribute("xmlns");

                    if( xmlns.indexOf("http://jabber.org/protocol/muc#user") == 0 )
                    {
                        var _from	= xquery[i].parentNode.getAttribute('from');
                        var _to		= xquery[i].parentNode.getAttribute('to');

                        // Get NameChatRoom
                        var nameChatRoom	= Strophe.getBareJidFromJid( _from );

                        // Get nickName
                        var nickName		= Strophe.getResourceFromJid( _from );

                        // Get Type/Show
                        var type	= ( xquery[i].parentNode.getAttribute('type') != null ) ? xquery[i].parentNode.getAttribute('type') : 'available' ;
                        var show	= ( xquery[i].parentNode.firstChild.nodeName == "show" ) ? xquery[i].parentNode.firstChild.firstChild.nodeValue : type;

                        var _idElement = nameChatRoom + "_UserChatRoom__" + nickName;

                        var _UserChatRoom					= document.createElement("div");
                        _UserChatRoom.id				= _idElement;
                        _UserChatRoom.style.paddingLeft = '18px';
                        _UserChatRoom.style.margin 		= '3px 0px 0px 2px';
                        _UserChatRoom.style.background	= 'url("'+path_jabberit+'templates/default/images/' + show + '.gif") no-repeat center left';
                        _UserChatRoom.appendChild( document.createTextNode( nickName ) );

                        var nodeUser = document.getElementById( _idElement );	

                        if( nodeUser == null )
                        {
                            if( document.getElementById( nameChatRoom + '__roomChat__participants' ) != null )
                            {
                                nameChatRoom = document.getElementById( nameChatRoom + '__roomChat__participants' );
                                nameChatRoom.appendChild( _UserChatRoom );
                            }
                            else
                            {
                                if( type != 'unavailable' )
                                {
                                    TrophyIM.makeChatRoom( nameChatRoom, nameChatRoom.substring(0, nameChatRoom.indexOf('@')));
                                    nameChatRoom = document.getElementById( nameChatRoom + '__roomChat__participants' );
                                    nameChatRoom.appendChild( _UserChatRoom );
                                }
                            }
                        }
                        else
                        {
                            if( type == 'unavailable' )
                            {
                                nodeUser.parentNode.removeChild( nodeUser );
                            }
                            else if( show )
                            {
                                nodeUser.style.backgroundImage =  'url("'+path_jabberit+'templates/default/images/' + show + '.gif")';
                            }
                        }
                    }
                }
            }
        }
    },    
    
    /** Function: onMessage
     *
     *  Message handler
     */
    
    onMessage : function(msg)
    {
        var checkTime = function(i)
        {
            if ( i < 10 ) i= "0" + i;
    		
            return i;
        };
    	
        var messageDate = function( _date )
        {
            var _dt = _date.substr( 0, _date.indexOf( 'T' ) ).split( '-' );
            var _hr = _date.substr( _date.indexOf( 'T' ) + 1, _date.length - _date.indexOf( 'T' ) - 2 ).split( ':' );
			
            ( _date = new Date ).setTime( Date.UTC( _dt[0], _dt[1] - 1, _dt[2], _hr[0], _hr[1], _hr[2] ) );

            return ( _date.toLocaleDateString( ).replace( /-/g, '/' ) + ' ' + _date.toLocaleTimeString( ) );
        };

        var data	= new Date();
        var dtNow	= checkTime(data.getHours()) + ":" + checkTime(data.getMinutes()) + ":" + checkTime(data.getSeconds());
    	
        var from	= msg.getAttribute('from');
        var type	= msg.getAttribute('type');
        var elems	= msg.getElementsByTagName('body');
        var delay	= ( msg.getElementsByTagName('delay') ) ? msg.getElementsByTagName('delay') : null;
        var stamp	= ( delay[0] != null ) ? "<font style='color:red;'>" + messageDate(delay[0].getAttribute('stamp')) + "</font>" :  dtNow;

        var barejid		= Strophe.getBareJidFromJid(from);
        var jidChatRoom	= Strophe.getResourceFromJid(from);
        var jid_lower	= barejid.toLowerCase();
        var contact		= "";
        var state		= "";

        var chatBox	= document.getElementById(jid_lower + "__chatState");
        var chatStateOnOff = null;
        var active	= msg.getElementsByTagName('active');
		
        contact	= barejid.toLowerCase();
        contact	= contact.substring(0, contact.indexOf('@'));
            
        if( TrophyIM.rosterObj.roster[barejid] )
        {
            if( TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'] )
            {
                contact = TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'];
            }
        }

        // Message with body are "content message", this means state active
        if ( elems.length > 0 )
        {
            state = "";
			
            // Set notify chat state capability on when sender notify it themself
            chatStateOnOff = document.getElementById(jid_lower + "__chatStateOnOff");
			
            if (active.length > 0 & chatStateOnOff != null )
            {
                chatStateOnOff.value = 'on';
            }

            // Get Message
            var _message	= document.createElement("div");
            var _text		= Strophe.getText( elems[0] );
			
            // Events Javascript
            _text = _text.replace(/onblur/gi,"EVENT_DENY");
			
            _text = _text.replace(/onchange/gi,"EVENT_DENY");
			
            _text = _text.replace(/onclick/gi,"EVENT_DENY");
			
            _text = _text.replace(/ondblclick/gi,"EVENT_DENY");
			
            _text = _text.replace(/onerror/gi,"EVENT_DENY");
			
            _text = _text.replace(/onfocus/gi,"EVENT_DENY");
			
            _text = _text.replace(/onkeydown/gi,"EVENT_DENY");
			
            _text = _text.replace(/onkeypress/gi,"EVENT_DENY");
			
            _text = _text.replace(/onkeyup/gi,"EVENT_DENY");
			
            _text = _text.replace(/onmousedown/gi,"EVENT_DENY");
			
            _text = _text.replace(/onmousemove/gi,"EVENT_DENY");
			
            _text = _text.replace(/onmouseout/gi,"EVENT_DENY");
			
            _text = _text.replace(/onmouseover/gi,"EVENT_DENY");
			
            _text = _text.replace(/onmouseup/gi,"EVENT_DENY");
			
            _text = _text.replace(/onresize/gi,"EVENT_DENY");
			
            _text = _text.replace(/onselect/gi,"EVENT_DENY");
			
            _text = _text.replace(/onunload/gi,"EVENT_DENY");
			
            // Events CSS
            _text = _text.replace(/style/gi,"EVENT_DENY");

            // Tags HTML
            _text = _text.replace(/img /gi,"IMG_DENY ");
			
            _text = _text.replace(/script /gi,"SCRIPT_DENY ");
			
            _text = _text.replace(/div /gi,"DIV_DENY ");
			
            _text = _text.replace(/span /gi,"SPAN_DENY ");
			
            _text = _text.replace(/iframe /gi,"IFRAME_DENY ");
			
            _message.innerHTML = _text;
			
            ////////// BEGIN XSS //////////////////////////////////////////////////
            // Delete Tags <SCRIPT>
            var scripts = _message.getElementsByTagName('script_deny');
            for (var i = 0; i < scripts.length; i++){
                _message.removeChild(scripts[i--]);
            }
            ////////////////////////////////////////////////////
			
            // Delete Tags <IMG>
            var _imgSrc = _message.getElementsByTagName('img_deny');
            for (var i = 0; i < _imgSrc.length; i++){
                _imgSrc[i].parentNode.removeChild( _imgSrc[i--] );
            }
            ////////////////////////////////////////////////////
			
            // Delete Tags <DIV>
            var _Div = _message.getElementsByTagName('div_deny');
            for (var i = 0; i < _Div.length; i++){
                _Div[i].parentNode.removeChild( _Div[i--] );
            }
            ////////////////////////////////////////////////////
			
            // Delete Tags <SPAN>
            var _Span = _message.getElementsByTagName('span_deny');
            for (var i = 0; i < _Span.length; i++){
                _Span[i].parentNode.removeChild( _Span[i--] );
            }
            ////////////////////////////////////////////////////

            // Delete Tags <IFRAME>
            var _Iframe = _message.getElementsByTagName('iframe_deny');
            for (var i = 0; i < _Iframe.length; i++){
                _Iframe[i].parentNode.removeChild( _Iframe[i--] );
            }

            // Delete Tags <A HREF>
            var _aHref = _message.getElementsByTagName('a');
            for (var i = 0; i < _aHref.length; i++){
                _aHref[i].parentNode.removeChild( _aHref[i--] );
            }
			
            _message.innerHTML = _message.innerHTML.replace(/^\s+|\s+$|^\n|\n$/g, "");
            ////////// END XSS //////////////////////////////////////////////////
			
            // Get Smiles
            _message.innerHTML = loadscript.getSmiles( _message.innerHTML );

            if (type == 'chat' || type == 'normal')
            {
                if ( _message.hasChildNodes() )
                {
                    var message = 
                    {
                        contact : "[" + stamp + "] <font style='font-weight:bold; color:black;'>" + contact + "</font>",
                        msg		: "</br>" + _message.innerHTML
                    };
					
                    TrophyIM.addMessage( TrophyIM.makeChat( from ), jid_lower, message );
                }
            }
            else if( type == 'groupchat')
            {
                if ( _message.hasChildNodes() )
                {
                    var message = 
                    {
                        contact : "[" + stamp + "] <font style='font-weight:bold; color:black;'>" + jidChatRoom + "</font>",
                        msg     : "</br>" + _message.innerHTML
                    };

                    TrophyIM.addMessage( TrophyIM.makeChatRoom( barejid ), jid_lower, message );
                }
            }
        }
        // Message without body are "content message", this mean state is not active
        else
        {
            if( chatBox != null )
                state = TrophyIM.getChatState(msg);			
        }
		
        // Clean chat status message some time later 		
        var clearChatState = function()
        {
            chatBox.innerHTML='';
        }
		
        if (chatBox != null)
        {
            var clearChatStateTimer; 
			
            chatBox.innerHTML = "<font style='font-weight:bold; color:grey; float:right;'>" + state + "</font>"; 
			
            var _composing =  msg.getElementsByTagName('composing'); 
			
            if ( _composing.length == 0 )
				
                clearChatStateTimer = setTimeout(clearChatState, 2000); 
            else 
                clearTimeout(clearChatStateTimer);			
        }

        return true;
    },

    /** Function: getChatState
	 *
	 *  Parameters:
	 *    (string) msg - the message to get chat state
	 *    (string) jid - the jid of chat box to update the chat state to.
  	 */
    getChatState : function(msg)
    {
        var	state =  msg.getElementsByTagName('inactive');
         	
        if ( state.length > 0 )
        {
            return i18n.INACTIVE;
        }
        else
        {
            state = msg.getElementsByTagName('gone');
            if ( state.length > 0 )
            {
                return i18n.GONE;
            }
            else
            {
                state = msg.getElementsByTagName('composing'); 
                if ( state.length > 0 )
                {
                    return i18n.COMPOSING;
                }
                else
                {
                    state =  msg.getElementsByTagName('paused');
                    if ( state.length > 0 )
                    {
                        return i18n.PAUSED;
                    }
                }
            }
        }
		
        return '';
    },

    /** Function: makeChat
     *
     *  Make sure chat window to given fulljid exists, switching chat context to
     *  given resource. 
     */
     
    makeChat : function(fulljid)
    {
        var barejid		= Strophe.getBareJidFromJid(fulljid);
        var titleWindow	= "";

        var paramsChatBox =
        {
            'enabledPopUp'	: ( ( loadscript.getBrowserCompatible() ) ? "block" : "none" ),
            'idChatBox' 	: barejid + "__chatBox",
            'jidTo'			: barejid,
            'path_jabberit' : path_jabberit
        };

        titleWindow = barejid.toLowerCase();
        titleWindow = titleWindow.substring(0, titleWindow.indexOf('@'));

        if( TrophyIM.rosterObj.roster[barejid] )
        {
            if( TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'] )
            {
                titleWindow = TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'];
            }
        }

        // Position Top
        TrophyIM.posWindow.top	= TrophyIM.posWindow.top + 10; 
        if( TrophyIM.posWindow.top > 200 )
            TrophyIM.posWindow.top	= 100;
        
        // Position Left
        TrophyIM.posWindow.left	= TrophyIM.posWindow.left + 5;
        if( TrophyIM.posWindow.left > 455 )
            TrophyIM.posWindow.left	= 400;
        
        var _content = document.createElement( 'div' );
        _content.innerHTML = loadscript.parse( "chat_box", "chatBox.xsl", paramsChatBox);
        _content = _content.firstChild;
        
        var _messages		= _content.firstChild.firstChild;
        var _textarea		= _content.getElementsByTagName( 'textarea' ).item( 0 );
        var _send			= _content.getElementsByTagName( 'input' ).item( 0 );
        var _chatStateOnOff	= _content.getElementsByTagName( 'input' ).item( 1 );

        var _send_message = function( )
        {
            if ( ! TrophyIM.sendMessage( barejid, _textarea.value ) )
                return false;

            // Add Message in chatBox;
            TrophyIM.addMessage( _messages, barejid, {
                contact : "<font style='font-weight:bold; color:red;'>" + i18n.ME + "</font>",
                msg : "<br/>" + _textarea.value
            } );

            _textarea.value = '';
            _textarea.focus( );
        };
		
        var composingTimer_ = 0;
        var isComposing_ = 0;
        var timeCounter;

        var setComposing = function( )
        {
            var checkComposing = function()
            {
                if (!isComposing_) {
                    // User stopped composing
                    composingTimer_ = 0;
                    clearInterval(timeCounter);
                    TrophyIM.sendContentMessage(barejid, 'paused');
                } else {
                    TrophyIM.sendContentMessage(barejid, 'composing');
                }
                isComposing_ = 0; // Reset composing
            }

            if (!composingTimer_) {
                /* User (re)starts composing */
                composingTimer_ = 1;
                timeCounter = setInterval(checkComposing,4000);
            }
            isComposing_ = 1;
        };

        loadscript.configEvents( _send, 'onclick', _send_message );
        loadscript.configEvents( _textarea, 'onkeyup', function( e )
        {
            if ( e.keyCode == 13 ){
                _send_message( );
                // User stopped composing
                composingTimer_ = 0;
                clearInterval(timeCounter);
            }else{
                if (_chatStateOnOff.value == 'on')
                    setComposing();
            }
        } );        

        var winChatBox = 
        {
            id_window		: "window_chat_area_" + barejid,
            barejid		: barejid,
            width		: 387,
            height		: 375,
            top			: TrophyIM.posWindow.top,
            left		: TrophyIM.posWindow.left,
            draggable		: true,
            visible		: "display",
            resizable		: true,
            zindex		: loadscript.getZIndex(),
            title		: titleWindow,
            closeAction         : "hidden",
            content		: _content	
        }
    	
        _win = _winBuild(winChatBox);

        // Notification New Message
        loadscript.notification(barejid);
    	
        // Photo User;
        loadscript.getPhotoUser(barejid);
   		
        _textarea.focus( );
        
        _messages = _win.content( ).firstChild; 
 	
        while ( _messages && _messages.nodeType !== 1 ) 
        { 
            _messages = _messages.nextSibling; 
        } 
        
        return ( _messages );         
    },

    /** Function: makeChatRoom
    *
    *
    *
    */
    
    makeChatRoom : function()
    {
        var jidChatRoom = arguments[0];
        var titleWindow	= "ChatRoom - " + unescape(arguments[1]);
    	
        var paramsChatRoom =
        {
            'idChatRoom' 	: jidChatRoom + "__roomChat",
            'jidTo'			: jidChatRoom,
            'lang_Send'		: i18n.SEND,
            'lang_Leave_ChatRoom' : i18n.LEAVE_CHATROOM,
            'path_jabberit' : path_jabberit
        };

        // Position Top
        TrophyIM.posWindow.top	= TrophyIM.posWindow.top + 10; 
        if( TrophyIM.posWindow.top > 200 )
            TrophyIM.posWindow.top	= 100;
        
        // Position Left
        TrophyIM.posWindow.left	= TrophyIM.posWindow.left + 5;
        if( TrophyIM.posWindow.left > 455 )
            TrophyIM.posWindow.left	= 400;

        var _content = document.createElement( 'div' );
        _content.innerHTML = loadscript.parse( "chat_room", "chatRoom.xsl", paramsChatRoom );
        _content = _content.firstChild;
    	
        var _messages		= _content.firstChild.firstChild;
        var _textarea		= _content.getElementsByTagName( 'textarea' ).item( 0 );
        var _send			= _content.getElementsByTagName( 'input' ).item( 0 );
        var _leaveChatRoom	= _content.getElementsByTagName( 'input' ).item( 1 );
        
        var _send_message = function( )
        {
            if ( ! TrophyIM.sendMessageChatRoom( jidChatRoom, _textarea.value ) )
                return false;
        	
            _textarea.value = '';
        	
            _textarea.focus( );
        };
        
        loadscript.configEvents( _send, 'onclick', _send_message );
        loadscript.configEvents( _leaveChatRoom, 'onclick', function( )
        {
            TrophyIM.leaveChatRoom( jidChatRoom );
        	
            if( TrophyIM.activeChatRoom.name.length > 0 )
            {
                for( var i = 0;  i < TrophyIM.activeChatRoom.name.length ; i++ )
                {
                    if( TrophyIM.activeChatRoom.name[i].indexOf( jidChatRoom ) >= 0 )
                    {
                        TrophyIM.activeChatRoom.name[i] = "";
                    }
                }
            }
        	
            setTimeout( function()
            {
                _winBuild("window_chat_room_" + jidChatRoom, "remove");
        		
            }, 650 );
        	
        });
        
        loadscript.configEvents( _textarea, 'onkeyup', function( e )
        {
            if ( e.keyCode == 13 )
            {
                _send_message( );
            }
        });        
        
        var winChatRoom = 
        {
            id_window		: "window_chat_room_" + arguments[0],
            barejid		: jidChatRoom,
            width			: 500,
            height			: 450,
            top			: TrophyIM.posWindow.top,
            left			: TrophyIM.posWindow.left,
            draggable		: true,
            visible		: "display",
            resizable		: true,
            zindex			: loadscript.getZIndex(),
            title			: titleWindow,
            closeAction	: "hidden",
            content		: _content 	
        }
    	
        _win = _winBuild(winChatRoom);
    	
        _messages = _win.content( ).firstChild; 
 	
        while ( _messages && _messages.nodeType !== 1 ) 
        { 
            _messages = _messages.nextSibling; 
        } 
        
        return ( _messages );         

    	
    },
    
    /** Function addContacts
	 * 
	 *  Parameters:
	 *		(string) jidFrom 	 
	 *  	(string) jidTo 
	 * 		(string) name
	 * 		(string) group	 
	 */
	
    addContact : function( jidTo, name, group )
    {
        var _flag = true;

        if( TrophyIM.removeResult.idResult.length > 0 )
        {
            for( var i = 0 ; i < TrophyIM.removeResult.idResult.length; i++ )
            {
                if( TrophyIM.removeResult.idResult[i] == jidTo )
                {
                    _flag = false;
					
                    TrophyIM.removeResult.idResult.splice(i,1);
					
                    i--;
                }
            }
        }
		
        if( _flag )
        {	
            // Add Contact
            var _id = TrophyIM.connection.getUniqueId('add'); 
            var newContact = $iq({
                type: 'set', 
                id: _id
            });
            newContact = newContact.c('query').attrs({
                xmlns : 'jabber:iq:roster'
            });
            newContact = newContact.c('item').attrs({
                jid: jidTo, 
                name:name
            });
            newContact = newContact.c('group').t(group).tree();
	
            TrophyIM.connection.send(newContact);
        }
    },

    /** Function: add
     *
     *  Parameters:
     *    (string) msg - the message to add
     *    (string) jid - the jid of chat box to add the message to.
     */
	
    addMessage : function( chatBox, jid, msg )
    {
        // Get Smiles
        msg.msg = loadscript.getSmiles( msg.msg );
 
        var messageDiv	= document.createElement("div");
        messageDiv.style.margin = "3px 0px 1em 3px";
        messageDiv.innerHTML	= msg.contact + " : " + msg.msg ;
    		
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    },
	
    /** Function : renameContact
     * 
     * 
     */
    
    renameContact : function( jid )
    {
        // Name
        var name		= TrophyIM.rosterObj.roster[jid].contact.name;

        if(( name = prompt(i18n.ASK_NEW_NAME_QUESTION + name + "!", name )))
            if(( name = name.replace(/^\s+|\s+$|^\n|\n$/g,"")) == "" )
                name = "";

        if( name == null || name == "")
            name = "";
		
        var jidTo = jid
        var name  = ( name ) ? name : TrophyIM.rosterObj.roster[jid].contact.name;
        var group = TrophyIM.rosterObj.roster[jid].contact.groups[0];
    	
        TrophyIM.addContact( jidTo, name, group );
    	
        document.getElementById('itenContact_' + jid ).innerHTML = name;
    },
    
    /** Function : renameGroup
     * 
     * 
     */

    renameGroup : function( jid )
    {
        var group		= TrophyIM.rosterObj.roster[jid].contact.groups[0];
        var presence	= TrophyIM.rosterObj.roster[jid].presence;
    	
        // Group
        if(( group = prompt( i18n.ASK_NEW_GROUP_QUESTION, group )))
            if(( group = group.replace(/^\s+|\s+$|^\n|\n$/g,"")) == "" )
                group = "";

        if( group == null || group == "")
            group = "";

        var jidTo = TrophyIM.rosterObj.roster[jid].contact.jid;
        var name  = TrophyIM.rosterObj.roster[jid].contact.name;
        var group = ( group ) ? group : TrophyIM.rosterObj.roster[jid].contact.groups[0];

        TrophyIM.rosterObj.removeContact( jid );
		
        TrophyIM.addContact( jidTo, name, group );
    	
        document.getElementById("JabberIMRoster").innerHTML = "";
		
        TrophyIM.renderRoster();
    	
        setTimeout(function()
        {
            for( var i in presence )
            {
                if ( presence[ i ].constructor == Function )
                    continue;
    				
                TrophyIM.rosterObj.setPresence( jid, presence[i].priority, presence[i].show, presence[i].status);
            }
        },500);
    },

    /** Function createChatRooms
     * 
     * 
     */
    
    createChatRooms : function()
    {
        var nickName	 = document.getElementById('nickName_chatRoom_jabberit').value;
        var nameChatRoom = document.getElementById('name_ChatRoom_jabberit').value; 
    	
        var _from	= Base64.decode( loadscript.getUserCurrent().jid ) + TROPHYIM_RESOURCE; 
        var _to		= escape( nameChatRoom ) + "@" + TROPHYIM_CHATROOM + "/" + nickName ;
        var new_room	= $pres( {
            from: _from, 
            to: _to
        } ).c( "x", {
            xmlns: Strophe.NS.MUC
            } );

        TrophyIM.activeChatRoom.name[ TrophyIM.activeChatRoom.name.length ] = _to; 
		
        TrophyIM.connection.send( new_room.tree() );
    },
    
    /** Function : joinRoom
     * 
     * 
     */
    
    joinChatRoom : function( roomName )
    {
        var presence = $pres( {
            from: TrophyIM.connection.jid, 
            to: roomName
        } ).c("x",{
            xmlns: Strophe.NS.MUC
            });
        
        TrophyIM.connection.send( presence );
    },
    
    /** Function : Leave Chat Room
     * 
     * 
     */
    
    leaveChatRoom : function( roomName )
    {
        var room_nick	= roomName;
    	
        var presenceid	= TrophyIM.connection.getUniqueId();
        
        var presence	= $pres( {
            type: "unavailable", 
            id: presenceid, 
            from: TrophyIM.connection.jid, 
            to: room_nick
        } ).c("x",{
            xmlns: Strophe.NS.MUC
            });
        
        TrophyIM.connection.send( presence );        
    },
    
    /** Function : getlistRooms
     * 
     * 
     */
    
    getListRooms : function()
    {
        if( TrophyIM.statusConn.connected )
        {
            var _error_return = function(element)
            {
                alert("ERRO : Tente novamente !");
            };
	    	
            var iq = $iq({
                to: TROPHYIM_CHATROOM, 
                type: "get"
            }).c("query",{
                xmlns: Strophe.NS.DISCO_ITEMS
                });    		
	    		
            TrophyIM.connection.sendIQ( iq, loadscript.listRooms, _error_return, 500 );
        }
        else
        {
            alert( "ERRO : Sem conexão com o servidor " + TROPHYIM_CHATROOM );
        }
    },
    
    /** Function: removeContact
     * 
     *  Parameters:
     *  	(string) jidTo
     */
    
    removeContact : function( jidTo )
    {
        var divItenContact	 = null;

        if( ( divItenContact = document.getElementById('itenContact_' + jidTo )))
        {	
            // Remove Contact
            var _id	= TrophyIM.connection.getUniqueId(); 	
    		
            // Controller Result
            TrophyIM.removeResult.idResult[ TrophyIM.removeResult.idResult.length ] = jidTo;

            var delContact	= $iq({
                type: 'set', 
                id: _id
            })
            delContact	= delContact.c('query').attrs({
                xmlns : 'jabber:iq:roster'
            });
            delContact	= delContact.c('item').attrs({
                jid: jidTo, 
                subscription:'remove'
            }).tree();

            TrophyIM.connection.send( delContact );
        	
            loadscript.removeElement( document.getElementById('itenContactNotification_' + jidTo ) );
    		
            var spanShow = document.getElementById('span_show_itenContact_' + jidTo )
            spanShow.parentNode.removeChild(spanShow);
    		
            loadscript.removeGroup( divItenContact.parentNode );
    		
            divItenContact.parentNode.removeChild(divItenContact);
        }
    },
    
    /** Function: renderRoster
     *
     *  Renders roster, looking only for jids flagged by setPresence as having
     *  changed.
     */
    
    renderRoster : function()
    {
        var roster_div = document.getElementById('JabberIMRoster');
		
        if( roster_div )
        {
            var users = new Array();
			
            var loading_gif = document.getElementById("JabberIMRosterLoadingGif");
			
            if( loading_gif.style.display == "block" )
                loading_gif.style.display = "none";
				
            for( var user in TrophyIM.rosterObj.roster )
            {
                if ( TrophyIM.rosterObj.roster[ user ].constructor == Function )
                    continue;

                users[users.length] = TrophyIM.rosterObj.roster[user].contact.jid;
            }

            users.sort();
			
            var groups 		= new Array();
            var flagGeral	= false;
			
            for (var group in TrophyIM.rosterObj.groups)
            {
                if ( TrophyIM.rosterObj.groups[ group ].constructor == Function )
                    continue;
				
                if( group )
                    groups[groups.length] = group;
				
                if( group == "Geral" )
                    flagGeral = true;
            }
            
            if( !flagGeral && users.length > 0 )
                groups[groups.length] = "Geral";
				
            groups.sort();
			
            for ( var i = 0; i < groups.length; i++ )
            {
                TrophyIM.renderGroups( groups[i] , roster_div );	
            }
			
            TrophyIM.renderItensGroup( users, roster_div );
        }
			
        TrophyIM._timeOut.renderRoster = setTimeout("TrophyIM.renderRoster()", 1000 );		
    },
	
    /** Function: renderGroups
     *
     *
     */
	
    renderGroups: function( nameGroup, element )
    {
        var _addGroup = function()
        {
            var _nameGroup	= nameGroup;
            var _element	= element;

            var paramsGroup = 
            {
                'nameGroup' 	: _nameGroup,
                'path_jabberit' : path_jabberit
            }
			
            _element.innerHTML += loadscript.parse("group","groups.xsl", paramsGroup);
        }

        if( !element.hasChildNodes() )
        {
            _addGroup();
        }
        else
        {
            var _NodeChild	= element.firstChild;
            var flagAdd		= false;
			
            while( _NodeChild )
            {
                if( _NodeChild.childNodes[0].nodeName.toLowerCase() === "span" )
                {
                    if( _NodeChild.childNodes[0].childNodes[0].nodeValue === nameGroup )
                    {
                        flagAdd = true;
                    }
                }
				
                _NodeChild = _NodeChild.nextSibling;
            }

            if( !flagAdd )
            {
                _addGroup();
            }
        }
    },

    /** Function: renderItensGroup
     *
     *
     */

    renderItensGroup : function( users, element )
    {
        var addItem = function()
        {
            if( arguments.length > 0 )
            {
                // Get Arguments
                var objContact	= arguments[0];
                var group		= arguments[1];
                var element		= arguments[2];
                var showOffline	= loadscript.getShowContactsOffline();
				
                // Presence e Status
                var presence 		= "unavailable";
                var status	 		= "";
                var statusColor		= "black";
                var statusDisplay	= "none";
				
                var _resource	= "";
				
                // Set Presence 
                var _presence = function(objContact)
                {
                    if (objContact.presence)
                    {
                        for (var resource in objContact.presence)
                        {
                            if ( objContact.presence[resource].constructor == Function )
                                continue;

                            if( objContact.presence[resource].show != 'invisible' )
                                presence = objContact.presence[resource].show;

                            if( objContact.contact.subscription != "both") 
                                presence = 'subscription';
							
                            if( objContact.presence[resource].status )
                            {
                                status = " ( " + objContact.presence[resource].status + " ) ";
                                statusDisplay	= "block";
                            }
                        }
                    }
                };
				
                // Set Subscription
                var _subscription = function( objContact )
                {
                    if( objContact.contact.subscription != "both" )
                    {
                        switch( objContact.contact.subscription )
                        {
                            case "none" :
								
                                status 		= " (( " + i18n.ASK_FOR_AUTH  + " )) ";
                                statusColor	= "red";
                                break;
	
                            case "to" :
								
                                status 		= " (( " + i18n.CONTACT_ASK_FOR_AUTH  + " )) ";
                                statusColor	= "orange";
                                break;
	
                            case "from" :
								
                                status 		= " (( " + i18n.AUTHORIZED + " )) ";
                                statusColor = "green";
                                break;
								
                            case "subscribe" :
								
                                status		= " (( " + i18n.AUTH_SENT  + " )) ";
                                statusColor	= "red";	
                                break;

                            case "not-in-roster" :
								
                                status		= " (( " + i18n.ASK_FOR_AUTH_QUESTION  + " )) ";
                                statusColor	= "orange";	
                                break;
								
                            default :
								
                                break;
                        }

                        statusDisplay = "block";
                    }
                };

                if( objContact.contact.subscription != "remove")
                {
                    var itensJid	= document.getElementById( "itenContact_" + objContact.contact.jid );
					
                    if( itensJid == null )
                    {
                        // Name
                        var nameContact = "";					
						
                        if ( objContact.contact.name ) 
                            nameContact = objContact.contact.name;
                        else
                        {
                            nameContact = objContact.contact.jid;
                            nameContact = nameContact.substring(0, nameContact.indexOf('@'));
                        }
						
                        // Get Presence
                        _presence(objContact);
						
                        var paramsContact =
                        {
                            divDisplay		: "block", 
                            id		  		: 'itenContact_' + objContact.contact.jid ,
                            jid		  		: objContact.contact.jid,
                            nameContact 	: nameContact,
                            path_jabberit	: path_jabberit,
                            presence	  	: presence,
                            spanDisplay		: statusDisplay,
                            status	  		: status,
                            statusColor		: "black",
                            subscription	: objContact.contact.subscription,
                            resource		: _resource
                        }
						
                        // Get Authorization
                        _subscription( objContact );
						
                        if( group != "")
                        {
                            var _NodeChild		= element.firstChild;
							
                            while( _NodeChild )
                            {
                                if( _NodeChild.childNodes[0].nodeName.toLowerCase() === "span" )
                                {
                                    if( _NodeChild.childNodes[0].childNodes[0].nodeValue === group )
                                    {
                                        _NodeChild.innerHTML += loadscript.parse("itens_group", "itensGroup.xsl", paramsContact);
                                    }
                                }
	
                                _NodeChild = _NodeChild.nextSibling;
                            }
                        }	
                    }
                    else
                    {
                        // Get Presence
                        _presence(objContact);
	
                        var is_open = itensJid.parentNode.childNodes[0].style.backgroundImage;	
                        is_open = is_open.indexOf("arrow_down.gif");
	
                        // Get Authorization
                        _subscription( objContact );
						
                        // Set subscription
                        itensJid.setAttribute('subscription', objContact.contact.subscription );
						
                        with ( document.getElementById('span_show_' + 'itenContact_' + objContact.contact.jid ) )
                        {
                            if( presence == "unavailable" && !showOffline )
                            {
                                style.display = "none";
                            }
                            else
                            {
                                if( is_open > 0 )
                                {
                                    style.display	= statusDisplay;
                                    style.color		= statusColor;
                                    innerHTML		= status;
                                }
                            }
                            }
						
                        if( presence == "unavailable" && !showOffline )
                        {
                            itensJid.style.display = "none";
                        }
                        else
                        {
                            if( is_open > 0 )
                            {
                                itensJid.style.display = "block";
                            }
                        }
						
                        itensJid.style.background	= "url('"+path_jabberit+"templates/default/images/" + presence + ".gif') no-repeat center left";
                    }
	
                    // Contact OffLine
                    if( !objContact.presence && !showOffline )
                    {
                        if( objContact.contact.subscription != "remove" )
                        {
                            with ( document.getElementById('span_show_' + 'itenContact_' + objContact.contact.jid ))
                            {
                                style.display 	= "none";
                                }
		
                            with ( document.getElementById('itenContact_' + objContact.contact.jid ) )
                            {
                                style.display	= "none";
                                }
                        }
                    }
                }
            }
        };
		
        var flag = false;
		
        for( var i = 0 ; i < users.length; i++ )
        {
            if( TrophyIM.rosterObj.roster[users[i]].contact.jid != Base64.decode( loadscript.getUserCurrent().jid) )
            {
                var _subscription = TrophyIM.rosterObj.roster[users[i]].contact.subscription;
				
                if( _subscription === "to" )
                {
                    flag = true;
                }
				
                if(  _subscription === "not-in-roster")
                {
                    flag = true;
                }
				
                if( TrophyIM.rosterObj.roster[users[i]].contact.groups )
                {
                    var groups = TrophyIM.rosterObj.roster[users[i]].contact.groups;
					
                    if( groups.length > 0 )
                    {
                        for( var j = 0; j < groups.length; j++ )
                        {
                            addItem( TrophyIM.rosterObj.roster[users[i]], groups[j], element );
                        }
                    }
                    else
                    {
                        addItem( TrophyIM.rosterObj.roster[users[i]], "Geral", element );
                    }
                }
                else
                {
                    addItem( TrophyIM.rosterObj.roster[users[i]], "Geral", element );
                }
            }
        }
		
        if( flag )
        {
            if ( TrophyIM.controll.notificationNewUsers == 0 )
            {
                loadscript.enabledNotificationNewUsers();
                TrophyIM.controll.notificationNewUsers++;
            }
        }
        else
        {
            loadscript.disabledNotificationNewUsers();
            TrophyIM.controll.notificationNewUsers = 0;
        }
    },

    /** Function: rosterClick
     *
     *  Handles actions when a roster item is clicked
     */
    
    rosterClick : function(fulljid)
    {
        TrophyIM.makeChat(fulljid);
    },

    /** Function SetAutorization
	 * 
	 */

    setAutorization : function( jidTo, jidFrom, _typeSubscription )
    {
        var _id	= TrophyIM.connection.getUniqueId();
    	
        TrophyIM.connection.send($pres( ).attrs({
            from: jidFrom, 
            to: jidTo, 
            type: _typeSubscription, 
            id: _id
        }).tree());
    },

    /** Function: setPresence
     *
     */

    setPresence : function( _type )
    {
        var presence_chatRoom = "";
		
        if( _type != 'status')
        {
            if( _type == "unavailable" &&  TrophyIM.statusConn.connected )
            {
                var loading_gif = document.getElementById("JabberIMRosterLoadingGif");
				
                if( TrophyIM._timeOut.renderRoster != null )
                    clearTimeout(TrophyIM._timeOut.renderRoster);
				
                if( TrophyIM.statusConn.connected )
                    TrophyIM.connection.send($pres({
                        type : _type
                    }).tree());
				
                for( var i = 0; i < TrophyIM.connection._requests.length; i++ )
                {
                    if( TrophyIM.connection._requests[i] )
                        TrophyIM.connection._removeRequest(TrophyIM.connection._requests[i]);
                }
				
                TrophyIM.logout();
				
                loadscript.clrAllContacts();
		    	
                delete TrophyIM.rosterObj.roster;
                delete TrophyIM.rosterObj.groups;
		    	
                setTimeout(function()
                {
                    if( loading_gif.style.display == "block" )
                        loading_gif.style.display = "none";
                }, 1000);
            }
            else
            {
                if( !TrophyIM.autoConnection.connect )
                {
                    TrophyIM.autoConnection.connect = true;
                    TrophyIM.load();
                }
                else
                {
                    if( TrophyIM.statusConn.connected )
                    {
                        if( loadscript.getStatusMessage() != "" )
                        {
                            var _presence = $pres( );
                            _presence.node.appendChild( Strophe.xmlElement( 'show' ) ).appendChild( Strophe.xmlTextNode( _type ) );
                            _presence.node.appendChild( Strophe.xmlElement( 'status' ) ).appendChild( Strophe.xmlTextNode( loadscript.getStatusMessage() ));
							
                            TrophyIM.connection.send( _presence.tree() );
							
                            presence_chatRoom = _type;
                        }
                        else
                        {
                            TrophyIM.connection.send($pres( ).c('show').t(_type).tree());
							
                            presence_chatRoom = _type;
                        }
                    }
                }
            }
        }
        else
        {
            var _show	= "available";
            var _status	= "";
			
            if( arguments.length < 2 )
            {
                if( loadscript.getStatusMessage() != "" )
                    _status = prompt(i18n.TYPE_YOUR_MSG, loadscript.getStatusMessage());
                else
                    _status = prompt(i18n.TYPE_YOUR_MSG);
				
                var _divStatus = document.getElementById("JabberIMStatusMessage");
				
                if( ( _status = _status.replace(/^\s+|\s+$|^\n|\n$/g,"") ) != "")
                    _divStatus.firstChild.innerHTML	= "( " + _status + " )";
            } 
            else
            {
                _status = arguments[1];
            }

            for( var resource in TrophyIM.rosterObj.roster[Base64.decode(loadscript.getUserCurrent().jid)].presence )
            {
                if ( TrophyIM.rosterObj.roster[Base64.decode(loadscript.getUserCurrent().jid)].presence[ resource ].constructor == Function )
                    continue;
    			
                if ( TROPHYIM_RESOURCE === ("/" + resource) )
                    _show = TrophyIM.rosterObj.roster[Base64.decode(loadscript.getUserCurrent().jid)].presence[resource].show;
            }

            if ( TrophyIM.statusConn.connected )
            {
                var _presence = $pres( );
                _presence.node.appendChild( Strophe.xmlElement( 'show' ) ).appendChild( Strophe.xmlTextNode( _show ) );
                _presence.node.appendChild( Strophe.xmlElement( 'status' ) ).appendChild( Strophe.xmlTextNode( _status ) );
				
                TrophyIM.connection.send( _presence.tree() );
				
                presence_chatRoom = _show;
            }
        }
		
        // Send Presence Chat Room
        if( TrophyIM.activeChatRoom.name.length > 0 )
        {
            for( i = 0; i < TrophyIM.activeChatRoom.name.length; i++ )
            {
                if( TrophyIM.activeChatRoom.name[i] != "" )
                    TrophyIM.connection.send($pres( {
                        to : TrophyIM.activeChatRoom.name[i]
                        } ).c('show').t( presence_chatRoom ) );
            }
        }

    },
	
    /** Function: sendMessage
     *
     *  Send message from chat input to user
     */
     
    sendMessage : function()
    {
        if (arguments.length > 0)
        {
            var jidTo = arguments[0];
            var message_input = arguments[1];
			
			
            message_input = message_input.replace(/^\s+|\s+$|^\n|\n$/g, "");
			
            if (message_input != "") {
			
                // Send Message
                var newMessage = $msg({
                    to: jidTo,
                    from: TrophyIM.connection.jid,
                    type: 'chat'
                });
                newMessage = newMessage.c('body').t(message_input);
                newMessage.up();
                newMessage = newMessage.c('active').attrs({
                    xmlns: 'http://jabber.org/protocol/chatstates'
                });
                // Send Message
                TrophyIM.connection.send(newMessage.tree());
				
                return true;
            }
        }
		
        return false;
    },

    /** Function: sendMessage
    *
    *  Send message to ChatRoom
    */
    
    sendMessageChatRoom : function( )
    {
        if( arguments.length > 0 ) 
        {
            var room_nick	= arguments[0];
            var message		= arguments[1];
            var msgid		= TrophyIM.connection.getUniqueId();
            var msg			= $msg({
                to: room_nick, 
                type: "groupchat", 
                id: msgid
            }).c("body",{
                xmlns: Strophe.NS.CLIENT
                }).t(message);
	        
            msg.up();//.c("x", {xmlns: "jabber:x:event"}).c("composing");
	        
            TrophyIM.connection.send(msg);
	        
            return true;
        }
    },
    
    /** Function: sendContentMessage
     *
     *  Send a content message from chat input to user
     */
    sendContentMessage : function()
    {
        if( arguments.length > 0 )
        {
            var jidTo = arguments[0];
            var state = arguments[1];

            var newMessage = $msg({
                to: jidTo, 
                from: TrophyIM.connection.jid, 
                type: 'chat'
            });
            newMessage = newMessage.c(state).attrs({
                xmlns : 'http://jabber.org/protocol/chatstates'
            });
            // Send content message
            TrophyIM.connection.send(newMessage.tree());
        }
    }
};

/** Class: TrophyIMRoster
 *
 *
 *  This object stores the roster and presence info for the TrophyIMClient
 *
 *  roster[jid_lower]['contact']
 *  roster[jid_lower]['presence'][resource]
 */
function TrophyIMRoster()
{
    /** Constants: internal arrays
     *    (Object) roster - the actual roster/presence information
     *    (Object) groups - list of current groups in the roster
     *    (Array) changes - array of jids with presence changes
     */
    if (TrophyIM.JSONStore.store_working)
    {
        var data = TrophyIM.JSONStore.getData(['roster', 'groups']);
        this.roster = (data['roster'] != null) ? data['roster'] : {};
        this.groups = (data['groups'] != null) ? data['groups'] : {};
    }
    else
    {
        this.roster = {};
        this.groups = {};
    }
    this.changes = new Array();
    
    if (TrophyIM.constants.stale_roster)
    {
        for (var jid in this.roster)
        {
            this.changes[this.changes.length] = jid;
        }
    }

    /** Function: addChange
	 *
	 *  Adds given jid to this.changes, keeping this.changes sorted and
	 *  preventing duplicates.
	 *
	 *  Parameters
	 *    (String) jid : jid to add to this.changes
	 */
	 
    this.addChange = function(jid)
    {
        for (var c = 0; c < this.changes.length; c++)
        {
            if (this.changes[c] == jid)
            {
                return;
            }
        }
		
        this.changes[this.changes.length] = jid;
		
        this.changes.sort();
    }
	
    /** Function: addContact
     *
     *  Adds given contact to roster
     *
     *  Parameters:
     *    (String) jid - bare jid
     *    (String) subscription - subscription attribute for contact
     *    (String) name - name attribute for contact
     *    (Array)  groups - array of groups contact is member of
     */
    
    this.addContact = function(jid, subscription, name, groups )
    {
        if( subscription === "remove" )
        {
            this.removeContact(jid);
        }
        else
        {
            var contact		= {
                jid:jid, 
                subscription:subscription, 
                name:name, 
                groups:groups
            }
            var jid_lower	= jid.toLowerCase();
	
            if ( this.roster[jid_lower] )
            {
                this.roster[jid_lower]['contact'] = contact;
            }
            else
            {
                this.roster[jid_lower] = {
                    contact:contact
                };
            }

            groups = groups ? groups : [''];
	        
            for ( var g = 0; g < groups.length; g++ )
            {
                if ( !this.groups[groups[g]] )
                {
                    this.groups[groups[g]] = {};
                }
	            
                this.groups[groups[g]][jid_lower] = jid_lower;
            }
        }
    }
    
    /** Function: getContact
     *
     *  Returns contact entry for given jid
     *
     *  Parameter: (String) jid - jid to return
     */
     
    this.getContact = function(jid)
    {
        if (this.roster[jid.toLowerCase()])
        {
            return this.roster[jid.toLowerCase()]['contact'];
        }
    }

    /** Function: getPresence
	*
	*  Returns best presence for given jid as Array(resource, priority, show,
	*  status)
	*
	*  Parameter: (String) fulljid - jid to return best presence for
	*/
	 
    this.getPresence = function(fulljid)
    {
        var jid = Strophe.getBareJidFromJid(fulljid);
        var current = null;
		    
        if (this.roster[jid.toLowerCase()] && this.roster[jid.toLowerCase()]['presence'])
        {
            for (var resource in this.roster[jid.toLowerCase()]['presence'])
            {
                if ( this.roster[jid.toLowerCase()]['presence'][ resource ].constructor == Function )
                    continue;
    			
                var presence = this.roster[jid.toLowerCase()]['presence'][resource];
                if (current == null)
                {
                    current = presence
                }
                else
                {
                    if(presence['priority'] > current['priority'] && ((presence['show'] == "chat"
                        || presence['show'] == "available") || (current['show'] != "chat" ||
                        current['show'] != "available")))
                        {
                        current = presence
                    }
                }
            }
        }
        return current;
    }

    /** Function: groupHasChanges
	 *
	 *  Returns true if current group has members in this.changes
	 *
	 *  Parameters:
	 *    (String) group - name of group to check
	 */
	 
    this.groupHasChanges = function(group)
    {
        for (var c = 0; c < this.changes.length; c++)
        {
            if (this.groups[group][this.changes[c]])
            {
                return true;
            }
        }
        return false;
    }
	
    /** Function removeContact
	 *
	 * Parameters
	 *	 (String) jid		
	 */
	 
    this.removeContact = function(jid)
    {
        if( this.roster[ jid ] )
        { 
            var groups = this.roster[ jid ].contact.groups;
			
            if( groups )
            {
                for ( var i = 0; i < groups.length; i++ )
                {
                    delete this.groups[ groups[ i ] ][ jid ];
                }
	
                for ( var i = 0; i < groups.length; i++ )
                {
                    var contacts = 0;
                    for ( var contact in this.groups[ groups[ i ] ] )
                    {
                        if ( this.groups[ groups[ i ] ][ contact ].constructor == Function )
                            continue;
		    			
                        contacts++;
                    }
		
                    if ( ! contacts )
                        delete this.groups[ groups[ i ] ];
                }
            }
	
            // Delete Object roster
            if( this.roster[jid] )
                delete this.roster[jid];
        }
    }
	 
    /** Function: setPresence
     *
     *  Sets presence
     *
     *  Parameters:
     *    (String) fulljid: full jid with presence
     *    (Integer) priority: priority attribute from presence
     *    (String) show: show attribute from presence
     *    (String) status: status attribute from presence
     */
    
    this.setPresence = function(fulljid, priority, show, status)
    {
        var barejid		= Strophe.getBareJidFromJid(fulljid);
        var resource	= Strophe.getResourceFromJid(fulljid);
        var jid_lower	= barejid.toLowerCase();
        
        if( show !== 'unavailable' || show !== 'error' )
        {
            if (!this.roster[jid_lower])
            {
                this.addContact( barejid, 'not-in-roster' );
            }
            
            var presence =
            {
                resource	: resource,
                priority	: priority,
                show		: show,
                status		: status
            }
            
            if (!this.roster[jid_lower]['presence'])
            {
                this.roster[jid_lower]['presence'] = {};
            }
            
            this.roster[jid_lower]['presence'][resource] = presence;	
        }
    }

    /** Fuction: save
	 *
	 *  Saves roster data to JSON store
	 */
	
    this.save = function()
    {
        if (TrophyIM.JSONStore.store_working)
        {
            TrophyIM.JSONStore.setData({
                roster:this.roster,
                groups:this.groups, 
                active_chat:TrophyIM.activeChats['current'],
                chat_history:TrophyIM.chatHistory
                });
        }
    }

}
/** Class: TrophyIMJSONStore
 *
 *
 *  This object is the mechanism by which TrophyIM stores and retrieves its
 *  variables from the url provided by TROPHYIM_JSON_STORE
 *
 */
function TrophyIMJSONStore() {
    this.store_working = false;
    /** Function _newXHR
     *
     *  Set up new cross-browser xmlhttprequest object
     *
     *  Parameters:
     *    (function) handler = what to set onreadystatechange to
     */
    this._newXHR = function (handler) {
        var xhr = null;
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
            if (xhr.overrideMimeType) {
                xhr.overrideMimeType("text/xml");
            }
        } else if (window.ActiveXObject) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
        return xhr;
    }
    /** Function getData
     *  Gets data from JSONStore
     *
     *  Parameters:
     *    (Array) vars = Variables to get from JSON store
     *
     *  Returns:
     *    Object with variables indexed by names given in parameter 'vars'
     */
    this.getData = function(vars) {
        if (typeof(TROPHYIM_JSON_STORE) != undefined) {
            Strophe.debug("Retrieving JSONStore data");
            var xhr = this._newXHR();
            var getdata = "get=" + vars.join(",");
            try {
                xhr.open("POST", TROPHYIM_JSON_STORE, false);
            } catch (e) {
                Strophe.error("JSONStore open failed.");
                return false;
            }
            xhr.setRequestHeader('Content-type',
                'application/x-www-form-urlencoded');
            xhr.setRequestHeader('Content-length', getdata.length);
            xhr.send(getdata);
            if (xhr.readyState == 4 && xhr.status == 200) {
                try {
                    var dataObj = JSON.parse(xhr.responseText);
                    return this.emptyFix(dataObj);
                } catch(e) {
                    Strophe.error("Could not parse JSONStore response" +
                        xhr.responseText);
                    return false;
                }
            } else {
                Strophe.error("JSONStore open failed. Status: " + xhr.status);
                return false;
            }
        }
    }
    /** Function emptyFix
     *    Fix for bugs in external JSON implementations such as
     *    http://bugs.php.net/bug.php?id=41504.
     *    A.K.A. Don't use PHP, people.
     */
    this.emptyFix = function(obj) {
        if (typeof(obj) == "object") {
            for (var i in obj) {
                if ( obj[i].constructor == Function )
                    continue;
    			
                if (i == '_empty_') {
                    obj[""] = this.emptyFix(obj['_empty_']);
                    delete obj['_empty_'];
                } else {
                    obj[i] = this.emptyFix(obj[i]);
                }
            }
        }
        return obj
    }
    /** Function delData
     *    Deletes data from JSONStore
     * 
     *  Parameters:
     *    (Array) vars  = Variables to delete from JSON store
     *
     *  Returns:
     *    Status of delete attempt.
     */
    this.delData = function(vars) {
        if (typeof(TROPHYIM_JSON_STORE) != undefined) {
            Strophe.debug("Retrieving JSONStore data");
            var xhr = this._newXHR();
            var deldata = "del=" + vars.join(",");
            try {
                xhr.open("POST", TROPHYIM_JSON_STORE, false);
            } catch (e) {
                Strophe.error("JSONStore open failed.");
                return false;
            }
            xhr.setRequestHeader('Content-type',
                'application/x-www-form-urlencoded');
            xhr.setRequestHeader('Content-length', deldata.length);
            xhr.send(deldata);
            if (xhr.readyState == 4 && xhr.status == 200) {
                try {
                    var dataObj = JSON.parse(xhr.responseText);
                    return dataObj;
                } catch(e) {
                    Strophe.error("Could not parse JSONStore response");
                    return false;
                }
            } else {
                Strophe.error("JSONStore open failed. Status: " + xhr.status);
                return false;
            }
        }
    }
    /** Function setData
     *    Stores data in JSONStore, overwriting values if they exist
     *
     *  Parameters:
     *    (Object) vars : Object containing named vars to store ({name: value,
     *    othername: othervalue})
     *
     *  Returns:
     *    Status of storage attempt
     */
    this.setData = function(vars)
    {
        if ( typeof(TROPHYIM_JSON_STORE) != undefined )
        {
            var senddata = "set=" + JSON.stringify(vars);
            var xhr = this._newXHR();
            try
            {
                xhr.open("POST", TROPHYIM_JSON_STORE, false);
            }
            catch (e)
            {
                Strophe.error("JSONStore open failed.");
                return false;
            }
            xhr.setRequestHeader('Content-type',
                'application/x-www-form-urlencoded');
            xhr.setRequestHeader('Content-length', senddata.length);
            xhr.send(senddata);
            if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText ==
                "OK") {
                return true;
            } else {
                Strophe.error("JSONStore open failed. Status: " + xhr.status);
                return false;
            }
        }
    }
    
    var testData = true;
    
    if (this.setData({
        testData:testData
    })) {
        var testResult = this.getData(['testData']);
        if (testResult && testResult['testData'] == true) {
            this.store_working = true;
        }
    }
}
/** Constants: Node types
 *
 * Implementations of constants that IE doesn't have, but we need.
 */
if (document.ELEMENT_NODE == null) {
    document.ELEMENT_NODE = 1;
    document.ATTRIBUTE_NODE = 2;
    document.TEXT_NODE = 3;
    document.CDATA_SECTION_NODE = 4;
    document.ENTITY_REFERENCE_NODE = 5;
    document.ENTITY_NODE = 6;
    document.PROCESSING_INSTRUCTION_NODE = 7;
    document.COMMENT_NODE = 8;
    document.DOCUMENT_NODE = 9;
    document.DOCUMENT_TYPE_NODE = 10;
    document.DOCUMENT_FRAGMENT_NODE = 11;
    document.NOTATION_NODE = 12;
}

/** Function: importNode
 *
 *  document.importNode implementation for IE, which doesn't have importNode
 *
 *  Parameters:
 *    (Object) node - dom object
 *    (Boolean) allChildren - import node's children too
 */
if (!document.importNode) {
    document.importNode = function(node, allChildren) {
        switch (node.nodeType) {
            case document.ELEMENT_NODE:
                var newNode = document.createElement(node.nodeName);
                if (node.attributes && node.attributes.length > 0) {
                    for(var i = 0; i < node.attributes.length; i++) {
                        newNode.setAttribute(node.attributes[i].nodeName,
                            node.getAttribute(node.attributes[i].nodeName));
                    }
                }
                if (allChildren && node.childNodes &&
                    node.childNodes.length > 0) {
                    for (var i = 0; i < node.childNodes.length; i++) {
                        newNode.appendChild(document.importNode(
                            node.childNodes[i], allChildren));
                    }
                }
                return newNode;
                break;
            case document.TEXT_NODE:
            case document.CDATA_SECTION_NODE:
            case document.COMMENT_NODE:
                return document.createTextNode(node.nodeValue);
                break;
        }
    };
}

/**
 *
 * Bootstrap self into window.onload and window.onunload
 */

var oldonunload = window.onunload;

window.onunload = function()
{
    if( oldonunload )
    {
        oldonunload();
    }
	
    TrophyIM.setPresence('unavailable');
}