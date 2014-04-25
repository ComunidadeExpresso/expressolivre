/*
 *      jquery.xmpp.js
 *
 *      Copyright 2011 Alvaro Garcia <maxpowel@gmail.com>
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

(function($) {

    $.xmpp ={
        rid:null,
        sid:null,
        jid:null,
        url: null,
        uri: null,
        myPresence: "unavailable",
        domainJabber: "",
        listening: false,
        hasConn: false,
        onRoster: null,
        onMessage: null,
        onIq: null,
        onComposing: null,
        onPresence: null,
        onError: null,
        connections: 0,
        resource: null,
        connected: false,
        wait: 60,
        inactivity: 60,
        _jsTimeout: null, //Used to save the javascript timeout
        _timeoutMilis: 500,
        __lastAjaxRequest: null,
        
        /**
        * Connect to the server
        * @params Object
        * {jid:"user@domain.com",
        * password:"qwerty",
        * resource:"Chat",
        * url:"/http-bind",
        * wait: 60,
        * inactivity: 60,
        * onDisconnect:function(){},
        * onConnect: function(data){},
        * onIq: function(iq){},
        * onMessage: function(message){},
        * onPresence: function(presence){}
        * onError: function(error, data){}
        * }
        **/
        connect: function(options){
            this.rid = Math.round(Math.random()*Math.pow(10,10));
            this.jid = options.username+"@"+options.domain;
            var domain = options.domain;
            this.domainJabber = options.domain;
            this.hasConn = true;
            var xmpp = this;
            if(options.url == null)
                this.url = '/http-bind'
            else
                this.url = options.url;
                
            if(!isNaN(options.wait)){
                this.wait = options.wait;
            }

            this._timeoutMilis = xmpp.wait * 1000;
            
            if(!isNaN(options.inactivity)){
                this.inactivity = options.inactivity
            }

            this.uri = this.jid;
            if(options.resource == null)
                this.resource = "";
            else{
                this.resource = options.resource;
                this.uri += "/" + this.resource;
            }

            //Events
            this.onMessage      = options.onMessage;
            this.onIq           = options.onIq;
            this.onPresence     = options.onPresence;
            this.onError        = options.onError;
            this.onDisconnect   = options.onDisconnect;
            this.onConnect      = options.onConnect;

            // My Events - Celepar / Prognus
            this.onRoster       = options.onRoster;
            this.onComposing    = options.onComposing;

            //Init connection
            var msg = "<body rid='"+this.rid+"' xmlns='http://jabber.org/protocol/httpbind' to='"+domain+"' xml:lang='en' wait='"+this.wait+"' inactivity='"+this.inactivity+"' hold='1' content='text/xml; charset=utf-8' ver='1.6' xmpp:version='1.0' xmlns:xmpp='urn:xmpp:xbosh'/>";

            $.post(this.url,msg,function(data){
                var response = $(xmpp.fixBody(data));
                xmpp.sid = response.attr("sid");

                if(response.find("mechanism:contains('PLAIN')").length){
                    xmpp.loginPlain(options);
                }else if(response.find("mechanism:contains('DIGEST-MD5')").length){
                    xmpp.loginDigestMD5(options);
                }else{
                    if(xmpp.onError != null){
                        xmpp.onError({error:"No auth method supported", data:data});
                    }
                }
            }, 'text');
        },
        
        /**
        * Attach to existing session
        * @params Object
        * {jid:"",
        * sid:"",
        * rid:"",
        * resource:"",
        * url:"",
        * wait: 60,
        * onDisconnect:function(){},
        * onConnect: function(data){},
        * onIq: function(iq){},
        * onMessage: function(message){},
        * onPresence: function(presence){}
        * onError: function(error, data){}
        * }
        **/
        attach: function(options){
            this.jid = options.username+"@"+options.domain;
            this.sid = options.sid;
            this.rid = options.rid;

            var xmpp = this;
            if(options.url == null)
                this.url = '/http-bind'
            else
                this.url = options.url;

            this.uri = this.jid;
            if(options.resource == null)
                this.resource = "";
            else{
                this.resource = options.resource;
                this.uri += "/" + this.resource;
            }

            //Events
            this.onMessage      = options.onMessage;
            this.onIq           = options.onIq;
            this.onPresence     = options.onPresence;
            this.onError        = options.onError;
            this.onDisconnect   = options.onDisconnect;
            this.onConnect      = options.onConnect;

            if(!isNaN(options.wait)){
                this.wait = options.wait
            }

            this._timeoutMilis = xmpp.wait * 1000;

            if(options.onConnect != null)
                xmpp.connected = true;

            options.onConnect();
            xmpp.listen();
        },
        
        /**
         * Disconnect from the server using synchronous Ajax
         * @params function callback
         **/
        disconnectSync: function(callback){
            var xmpp = this;
            xmpp.rid = xmpp.rid + 1;
            this.listening = true;
            xmpp.connections = xmpp.connections + 1;
            var msg = "<body rid='"+ this.rid +"' xmlns='http://jabber.org/protocol/httpbind' sid='"+ this.sid +"' type='terminate'><presence xmlns='jabber:client' type='unavailable'/></body>";
            $.ajax({
                type: 'POST',
                url: this.url,
                data: msg,
                success: function(data){
                    xmpp.connections = xmpp.connections - 1;
                    xmpp.messageHandler(data);
                    xmpp.listening = false;
                    //Do not listen anymore!
                    //Two callbacks
                    if(callback != null)
                    callback(data);
                    if(xmpp.onDisconnect != null)
                    xmpp.connected = false;
                    xmpp.onDisconnect(data);
                    xmpp.myPresence = "unavailable";
                },
                dataType: 'text',
                async:false
            });
        },
        
        /**
         * Disconnect from the server
         * @params function callback
         **/
        disconnect: function(callback){
            this.hasConn = false;
            var xmpp = this;
            xmpp.rid = xmpp.rid + 1;
            this.listening = true;
            xmpp.connections = xmpp.connections + 1;
            var msg = "<body rid='"+ this.rid +"' xmlns='http://jabber.org/protocol/httpbind' sid='"+ this.sid +"' type='terminate'><presence xmlns='jabber:client' type='unavailable'/></body>";
            $.post(this.url,msg,function(data){
                xmpp.connections = xmpp.connections - 1;
                xmpp.messageHandler(data);
                xmpp.listening = false;
                xmpp.myPresence = "unavailable";
                //Do not listen anymore!

                //Two callbacks
                if(callback != null)
                    callback(data);

                if(xmpp.onDisconnect != null)
                    xmpp.connected = false;
                    xmpp.onDisconnect(data);

            }, 'text');
        },
        /**
         * Do a MD5 Digest authentication
         **/
        loginDigestMD5: function(options){
            var xmpp = this;
            this.rid++;
            var msg = "<body rid='"+this.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+this.sid+"'><auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='DIGEST-MD5'/></body>";
            $.post(this.url,msg,function(data){
                var response = $(data);

                var domain = options.domain;
                var username = options.username;

                //Code bases on Strophe
                var attribMatch = /([a-z]+)=("[^"]+"|[^,"]+)(?:,|$)/;

                var challenge = Base64.decode(response.text());

                var cnonce = MD5.hexdigest("" + (Math.random() * 1234567890));
                var realm = "";
                var host = null;
                var nonce = "";
                var qop = "";
                var matches;

                while (challenge.match(attribMatch)) {
                    matches = challenge.match(attribMatch);
                    challenge = challenge.replace(matches[0], "");
                    matches[2] = matches[2].replace(/^"(.+)"$/, "$1");
                    switch (matches[1]) {
                    case "realm":
                            realm = matches[2];
                            break;
                    case "nonce":
                            nonce = matches[2];
                            break;
                    case "qop":
                            qop = matches[2];
                            break;
                    case "host":
                            host = matches[2];
                            break;
                    }
                }

                var digest_uri = "xmpp/" + domain;
                if (host !== null) {
                    digest_uri = digest_uri + "/" + host;
                }

                var A1 = MD5.hash(username + ":" + realm + ":" + options.password) +
                        ":" + nonce + ":" + cnonce;

                var A2 = 'AUTHENTICATE:' + digest_uri;

                var responseText = "";
                responseText += 'username=' + xmpp._quote(username) + ',';
                responseText += 'realm=' + xmpp._quote(realm) + ',';
                responseText += 'nonce=' + xmpp._quote(nonce) + ',';
                responseText += 'cnonce=' + xmpp._quote(cnonce) + ',';
                responseText += 'nc="00000001",';
                responseText += 'qop="auth",';
                responseText += 'digest-uri=' + xmpp._quote(digest_uri) + ',';
                responseText += 'response=' + xmpp._quote(
                MD5.hexdigest(MD5.hexdigest(A1) + ":" +
                                            nonce + ":00000001:" +
                                            cnonce + ":auth:" +
                                            MD5.hexdigest(A2))) + ',';
                responseText += 'charset="utf-8"';

                xmpp.rid++;
                var msg ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><response xmlns='urn:ietf:params:xml:ns:xmpp-sasl'>"+Base64.encode(responseText)+"</response></body>";
                $.post(this.url,msg,function(data){
                    var response = $(xmpp.fixBody(data));
                    if(!response.find("failure").length){
                        xmpp.rid++;
                        var msg ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><response xmlns='urn:ietf:params:xml:ns:xmpp-sasl'/></body>";
                        $.post(this.url,msg,function(data){
                            var response = $(xmpp.fixBody(data));
                            if(response.find("success").length){
                                xmpp.rid++;
                                var msg ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"' to='"+domain+"' xml:lang='en' xmpp:restart='true' xmlns:xmpp='urn:xmpp:xbosh'/>";
                                $.post(this.url,msg,function(data){
                                    xmpp.rid++;
                                    var msg ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><iq type='set' id='_bind_auth_2' xmlns='jabber:client'><bind xmlns='urn:ietf:params:xml:ns:xmpp-bind'><resource>" + xmpp.resource +"</resource></bind></iq></body>";
                                    $.post(this.url,msg,function(data){
                                        xmpp.rid++;
                                        var msg = "<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><iq type='set' id='_session_auth_2' xmlns='jabber:client'><session xmlns='urn:ietf:params:xml:ns:xmpp-session'/></iq></body>";
                                        $.post(this.url,msg,function(data){
                                            if(options.onConnect != null)
                                                xmpp.connected = true;

                                            options.onConnect(data);
                                            xmpp.listen();
                                        }, 'text');
                                    }, 'text');
                                }, 'text');
                            }else{
                                if(xmpp.onError != null)
                                    xmpp.onError({error: "Invalid credentials", data:data});
                            }
                        }, 'text');
                    }else{
                        if(xmpp.onError != null)
                            xmpp.onError({error: "Invalid credentials", data:data});
                    }
                }, 'text');

            }, 'text');
        },

        /**
         * Returns the quoted string
         * @prams string
         * @return quoted string
         **/
        _quote: function(string){
                return '"'+string+'"';
        },

        /**
         * Do a plain authentication PHP
         **/
        loginPlainPHP: function(options)
        {
            this.rid++;
            var domain  = options.domain;
            var xmpp    = this;
            var text    = "";
            var url     = this.url;

            $.post( "bind.php", url, function(data)
            {
                var auth       = $.trim(data);
                var php_text   = "<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>"+auth+"</auth></body>";

                $.post( xmpp.url, php_text , function(data)
                {
                    var response = $(xmpp.fixBody(data));

                    if( response.find("success").length )
                    {
                        xmpp.rid++;
                        text ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"' to='"+domain+"' xml:lang='en' xmpp:restart='true' xmlns:xmpp='urn:xmpp:xbosh'/>";
                        $.post( url, text, function(data)
                        {
                            //xmpp.messageHandler(data);
                            xmpp.rid++;
                            text ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><iq type='set' id='_bind_auth_2' xmlns='jabber:client'><bind xmlns='urn:ietf:params:xml:ns:xmpp-bind'><resource>" + xmpp.resource +"</resource></bind></iq></body>";
                            $.post( url, text, function(data)
                            {
                                //xmpp.messageHandler(data);
                                xmpp.rid++;
                                text = "<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><iq type='set' id='_session_auth_2' xmlns='jabber:client'><session xmlns='urn:ietf:params:xml:ns:xmpp-session'/></iq></body>";
                                $.post(url,text,function(data)
                                {
                                    if(options.onConnect != null)
                                    {
                                        xmpp.connected = true;
                                    }

                                    options.onConnect(data);

                                    xmpp.listen();

                                }, 'text');
                            }, 'text');
                        }, 'text');
                    }
                    else
                    {
                        if(options.onError != null)
                        {
                            options.onError({error: "Invalid credentials", data:data});
                        }
                    }
                }, 'text');


            },'text');
        },

        /**
         * Do a plain authentication Javascript
         **/
        loginPlain: function(options)
        {
            if((this.jid != null && $.trim(this.jid) != "") && ( options.password != undefined && $.trim(options.password) != ""))
            {    
                this.rid++;
                var user    = options.username;
                var domain  = options.domain;
                var xmpp    = this;
                var text    = "<body rid='"+this.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+this.sid+"'><auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>"+options.password+"==</auth></body>";
                var url     = this.url;

                $.post(this.url,text,function(data)
                {
                    var response = $(xmpp.fixBody(data));

                    if( response.find("success").length )
                    {
                        xmpp.rid++;
                        text ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"' to='"+domain+"' xml:lang='en' xmpp:restart='true' xmlns:xmpp='urn:xmpp:xbosh'/>";
                        $.post( url, text, function(data)
                        {
                            //xmpp.messageHandler(data);
                            xmpp.rid++;
                            text ="<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><iq type='set' id='_bind_auth_2' xmlns='jabber:client'><bind xmlns='urn:ietf:params:xml:ns:xmpp-bind'><resource>" + xmpp.resource +"</resource></bind></iq></body>";
                            $.post( url, text, function(data)
                            {
                                //xmpp.messageHandler(data);
                                xmpp.rid++;
                                text = "<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'><iq type='set' id='_session_auth_2' xmlns='jabber:client'><session xmlns='urn:ietf:params:xml:ns:xmpp-session'/></iq></body>";
                                $.post(url,text,function(data)
                                {
                                    if(options.onConnect != null)
                                    {
                                        xmpp.connected = true;
                                    }

                                    options.onConnect(data);

                                    xmpp.listen();

                                }, 'text');
                            }, 'text');
                        }, 'text');
                    }
                    else
                    {
                        if(options.onError != null)
                        {
                            options.onError({error: "Invalid credentials", data:data});
                        }
                    }
                }, 'text');
            }
            else
            {
                this.loginPlainPHP(options);
            }
        },

        /**
         * Disconnected cause a network problem
         **/
         __networkError: function(){
             //Notify the errors and change the state to disconnected
             if($.xmpp.onError != null){
                 $.xmpp.onError({error:"Network error"})
             }
             
             if($.xmpp.onDisconnect != null){
                 $.xmpp.onDisconnect()
             }
             APIAjax.abortAll();
             //$.xmpp.__lastAjaxRequest.abort();
             $.xmpp.connections = $.xmpp.connections - 1;
             $.xmpp.listening = false;
             $.xmpp.connected = false
             
         },
         
        /**
         * Wait for a new event
         **/
        listen: function()
        {
            var xmpp = this;
            
            if( APIAjax.length() == 0 && ( xmpp.sid != null && xmpp.rid != null ) && this.hasConn )
            {
                xmpp.rid = xmpp.rid + 1;
                xmpp.connections = xmpp.connections + 1;
                
                APIAjax
                .url(xmpp.url)
                .params("<body rid='"+xmpp.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+xmpp.sid+"'></body>")
                .done(function(data,x,y,z)
                {
                    xmpp.connections    = xmpp.connections - 1;
                    xmpp.listening      = false;

                    var body = $(xmpp.fixBody(data));

                    if( body.children().length > 0 )
                    { 
                        xmpp.messageHandler(data);
                    }
                       
                    xmpp.listen();
                })
                .fail(function(XMLHttpRequest, textStatus, errorThrown)
                {
                    xmpp.__networkError();

                    xmpp.onError({"error": errorThrown, "data":textStatus});
                })
                .execute();
            }
            
        },

        /**
         * Send a raw command
         * @params String Raw command as plain text
         * @params callback function callback
         **/
        sendCommand: function(rawCommand, callback){
            var self = this;

            this.rid = this.rid + 1;
            this.listening = true;
            this.connections = this.connections + 1;
            var command = "<body rid='"+this.rid+"' xmlns='http://jabber.org/protocol/httpbind' sid='"+this.sid+"'>"+ rawCommand+"</body>";

            APIAjax
            .url(self.url)
            .params(command)
            .done(function(data){
                self.connections = self.connections - 1;
                self.messageHandler(data);
                self.listening = false;
                self.listen();
                if(callback != null)
                        callback(data);
            })
            .fail(function(){
                
                self.__networkError();

                APIAjax.abortAll();
            })
            .execute();
        },

        /**
        * Send a text message
        * @params Object
        *         {body: "Hey dude!",
        *           to: "someone@somewhere.com"
        *           resource: "Chat",
        *          otherAttr: "value"
        *           }
        * @params data: Extra information such errors
        * @params callback: function(){}
        **/
        sendMessage: function(options, data, callback){
            var toJid = options.to;
            var body = options.body;

            if(options.resource != null)
                    toJid = toJid+"/"+options.resource;
            else if(this.resource != "")
                    toJid = toJid+"/"+this.resource;

            //Remove used paramteres
            delete options.to;
            delete options.body;
            delete options.resource;

            var msg = "<message type='chat' to='"+toJid+"' xmlns='jabber:client'><body>"+body+"</body></message>";

            this.sendCommand(msg,callback);
        },

        /**
         * Change the presence and status
         * @params object { show : "status_user"}, are: null, away, dnd
         * @params object { status : "your message here"}, user defined messages
         * @params callback: function(){}
         * @modified - Celepar / Prognus
         **/
        setPresence: function( type, callback )
        {
            var msg = "<presence xmlns='jabber:client'>";
            
            if( type != null )
            {
                if( type.show && ( type.show != null && type.show != "available" ) )
                {
                    msg += "<show>"+type.show+"</show>";
                }
                
                if( type.status )   
                {
                    msg += "<status>"+type.status+"</status>";
                }
            }

            msg += "</presence>";

            if( type == null )
                this.myPresence = "available";
            else
                this.myPresence = ( type.show  == null ) ? "available" : type.show ;
            
            this.sendCommand( msg , callback );
        },

        /**
         * Get if you are connected
         **/
        isConnected: function(){
            return this.connected;
        },

        /**
         * Get presence user connected
         **/
        getMyPresence : function()
        {
            return this.myPresence;
        },

        /**
         * Get roster request
         **/
        getRoster: function( callback )
        {
            var msg = "<iq type='get'><query xmlns='jabber:iq:roster'/></iq>";

            this.sendCommand( msg );
        },
        
        /**
         * Get domain Jabber
         **/

        getDomain: function()
        {
            return this.domainJabber;
        },

        /**
         * Add Contact
         * @params object { to, name, group }
         * @create - Celepar / Prognus
         **/
        addContact: function( contact )
        {
            if( $.trim(this.jid) != $.trim(contact.to) )
            {
                var msg =   "<iq from='"+this.jid+"/"+this.resource+"' type='set' id='set1'>";
                msg +=      "<query xmlns='jabber:iq:roster'>";
                msg +=      "<item jid='"+contact.to+"' name='"+contact.name+"'>";
                
                // if exist Group   
                if( $.trim(contact.group) != "" && contact.group != null )    
                {
                    msg += "<group>"+contact.group+"</group>";
                }

                msg +=  "</item></query>";
                msg +=  "</iq>";

                this.sendCommand(msg);
            }
        },

        /**
        * Delete Contact
        * @params object { to }
        * @create - Celepar / Prognus
        **/
        deleteContact: function( contact )
        {
            var msg =   "<iq from='"+this.jid+"' id='delete' type='set'>" +
                            "<query xmlns='jabber:iq:roster'>" +
                                "<item jid='"+contact.to+"' subscription='remove'/>" +
                            "</query>" +
                        "</iq>";
            
            this.sendCommand( msg );
        },

        /**
        * Update Contact
        * @params object { jid, name, subscription, group }
        * @create - Celepar / Prognus
        **/
        updateContact: function( contact )
        {
            var msg = "<iq from='"+this.jid+"' type='set' id='updateContact'>" +
                            "<query xmlns='jabber:iq:roster'>" +
                                "<item jid='"+contact.jid+"' name='"+contact.name+"' subscription='"+contact.subscription+"'>" +
                                    "<group>"+contact.group+"</group>"+
                                "</item>" +
                            "</query>" +
                        "</iq>";
            
            this.sendCommand( msg );
        },

        /**
        * Subscription Contact
        * @params object { to , type }
        * @create - Celepar / Prognus
        **/
        subscription: function( contact )
        {
            if( this.jid != $.trim(contact.to) )
            {    
                var msg   = "<presence from='"+this.jid+"' to='"+contact.to+"' type='"+contact.type+"'></presence>";

                this.sendCommand( msg );
            }
        },

        /**
        * Typing the message
        * @params object { isWriting : "value"}
        * @modified - Celepar / Prognus            
        **/
        isWriting: function(options)
        {
            var msg = "";
            
            switch( options.isWriting )
            {
                case "active":
                    msg = "<message type='chat' to='"+options.to+"' from='"+this.jid+"/"+this.resource+"'><active xmlns='http://jabber.org/protocol/chatstates'/></message>";
                    break;
                
                case "inactive":
                    msg = "<message type='chat' to='"+options.to+"' from='"+this.jid+"/"+this.resource+"'><inactive xmlns='http://jabber.org/protocol/chatstates'/></message>";
                    break;

                case "composing":
                    msg = "<message type='chat' to='"+options.to+"' from='"+this.jid+"/"+this.resource+"'><composing xmlns='http://jabber.org/protocol/chatstates'/></message>";
                    break;

                case "gone":
                    msg = "<message type='chat' to='"+options.to+"' from='"+this.jid+"/"+this.resource+"'><gone xmlns='http://jabber.org/protocol/chatstates'/></message>";
                    break;
                    
                case "paused":
                    msg = "<message type='chat' to='"+options.to+"' from='"+this.jid+"/"+this.resource+"'><paused xmlns='http://jabber.org/protocol/chatstates'/></message>";
                    break;
                    
                case "x" :
                    msg  = "<message type='chat' to='"+options.to+"' from='"+this.jid+"/"+this.resource+"'>";
                    msg += "<x xmlns='jabber:x:event'><offline/><composing/><delivered/><displayed/></x>";
                    msg += "</message>";
                    break;
            }
            
            this.sendCommand( msg );
        },

        /**
        * Get who is online
        * When presence it received the event onPresence is triggered
        **/
        getPresence: function(){
            var msg = "<presence/>";
            var self = this;
            this.sendCommand(msg,function(data){
                self.messageHandler(data,self)
            });
        },

        messageHandler: function( data, context )
        {
            var xmpp        = this;
            var response    = $(xmpp.fixBody(data));

            $.each(response.find("message"),function(i,element)
            {
                try
                {
                    var e           = $(element);
                    var active      = $(element).find("active");
                    var composing   = $(element).find("composing");
                    var inactive    = $(element).find("inactive");
                    var gone        = $(element).find("gone");
                    var paused      = $(element).find("paused");
                    var body        = $(element).find("div");
                    
                    if( body.length > 0 )
                    {
                        xmpp.onMessage(
                        {
                            "from"  : e.attr('from'),
                            "to"    : e.attr('to'),
                            "body"  : body.html()
                        });
                    }
                    else
                    {
                        var composingMessage = function( to , from, state , type )
                        {
                            xmpp.onComposing(
                            {
                                "from"  : from,
                                "to"    : to,
                                "state" : state,
                                "type"  : type
                            });                            
                        };

                        // Active Chat
                        if( active.length > 0 )
                        {
                            composingMessage( e.attr('to'), e.attr('from'), 'active', e.attr('type') );
                        }

                        // Composing message
                        if( composing.length > 0 )
                        {
                            composingMessage( e.attr('to'), e.attr('from'), 'composing', e.attr('type') );
                        }

                        // Inactive chat
                        if( inactive.length > 0 )
                        {
                            composingMessage( e.attr('to'), e.attr('from'), 'inactive', e.attr('type') );
                        }
                        
                        // Paused message
                        if( paused.length > 0 )
                        {
                            composingMessage( e.attr('to'), e.attr('from'), 'paused', e.attr('type') );
                        }

                        // Gone chat
                        if( gone.length > 0 )
                        {
                            composingMessage( e.attr('to'), e.attr('from'), 'gone', e.attr('type') );
                        }
                    }

                }catch(e){}
            });

            $.each(response.find("query").find("item"),function(i,element)
            {
                try
                {
                    var e = $(element);

                    if( $.trim(e.attr('subscription') ) != "remove" )
                    {    
                        var _show = "unavailable";

                        if( response.find("iq").attr("type") == "set" )
                        {
                            if( e.attr('subscription') != "none" )
                            {
                                _show = "available";
                            }
                        }

                        xmpp.onRoster(
                        {
                            "ask"           : e.attr("ask"),
                            "jid"           : e.attr("jid"),
                            "name"          : e.attr("name"),
                            "subscription"  : e.attr("subscription"),
                            "show"          : _show,
                            "group"         : ($.trim($(this).find("group").html()) ) ? $(this).find("group").html() : ""
                        });
                    }

                }catch(e){}
            });

            $.each(response.find("iq"),function(i,element)
            {
                try
                {
                    var e = $(element);

                    if( e.find("query") && $.trim(e.find("query").attr("xmlns")) == "jabber:iq:roster" )
                    {
                        e.find("item").each(function()
                        {
                            if( $.trim(e.attr('subscription') ) != "remove" )
                            {    
                                xmpp.onRoster(
                                {
                                    "jid"           : e.attr("jid"),
                                    "name"          : e.attr("name"),
                                    "subscription"  : e.attr("subscription"),
                                    "group"         : ($.trim($(this).find("group").html()) ) ? $(this).find("group").html() : ""
                                });
                            }
                        });
                    }
                    else
                    {
                        xmpp.onIq( e );
                    }

                }catch(e){}
            });

            $.each(response.find("presence"),function(i,element)
            {
                try
                {   
                    var e = $(element);

                    // New Contacts
                    if( e.attr('type') && $.trim(e.attr('type')) != "unavailable" )
                    {
                        xmpp.onRoster(
                        {
                            "jid"           : e.attr("from"),
                            "name"          : "",
                            "show"          : e.attr("type"),
                            "subscription"  : ( $.trim(e.attr("type") ) === "subscribed" ) ? "both" : e.attr("type"),
                            "group"         : ""
                        });

                        // Presence automatic
                        if( $.trim(e.attr('type')) == "subscribe" )
                        {    
                            xmpp.subscription({"to": e.attr("from") , "type": "subscribed"});
                        }

                        // Presence automatic
                        if( $.trim(e.attr('type')) == "subscribed" )
                        {    
                            xmpp.subscription({"to": e.attr("from") , "type": "subscribe"});
                        }
                    }
                    else
                    {    
                        var _statusContact = "";

                        if( e.find("status").length > 0  )
                        {    
                            _statusContact = e.find("status").html();
                            _statusContact = _statusContact.replace(")","");
                            _statusContact = _statusContact.replace("(","");
                        }

                        xmpp.onPresence(
                        {
                            "from"      : e.attr("from"),
                            "to"        : e.attr("to"),
                            "show"      : (e.find("show").html() ) ? e.find("show").html() : ( e.attr("type") ? e.attr("type") : "available"),
                            "status"    : _statusContact
                        });
                    }
                    
                }catch(e){}
            });
        },

        /**
        * Replaces <body> tags because jquery does not "parse" this tag
        * @params String
        * @return String
        * @modified - Celepar / Prognus            
        **/

        fixBody: function(html)
        {
            html = html.replace(/onblur/gi,"EVENT_DENY");
            html = html.replace(/onchange/gi,"EVENT_DENY");
            html = html.replace(/onclick/gi,"EVENT_DENY");
            html = html.replace(/ondblclick/gi,"EVENT_DENY");
            html = html.replace(/onerror/gi,"EVENT_DENY");
            html = html.replace(/onfocus/gi,"EVENT_DENY");
            html = html.replace(/onkeydown/gi,"EVENT_DENY");
            html = html.replace(/onkeypress/gi,"EVENT_DENY");
            html = html.replace(/onkeyup/gi,"EVENT_DENY");
            html = html.replace(/onmousedown/gi,"EVENT_DENY");
            html = html.replace(/onmousemove/gi,"EVENT_DENY");
            html = html.replace(/onmouseout/gi,"EVENT_DENY");
            html = html.replace(/onmouseover/gi,"EVENT_DENY");
            html = html.replace(/onmouseup/gi,"EVENT_DENY");
            html = html.replace(/onresize/gi,"EVENT_DENY");
            html = html.replace(/onselect/gi,"EVENT_DENY");
            html = html.replace(/onunload/gi,"EVENT_DENY");
            
            // Events CSS
            html = html.replace(/style/gi,"EVENT_DENY");

            // Tags HTML
            html = html.replace(/img /gi,"IMG_DENY ");
            html = html.replace(/script /gi,"SCRIPT_DENY ");
            html = html.replace(/div /gi,"DIV_DENY ");
            html = html.replace(/span /gi,"SPAN_DENY ");
            html = html.replace(/iframe /gi,"IFRAME_DENY ");
            
            
            html = html.replace(/<\/body>/ig, "</div>")
            html = html.replace(/<body/ig, "<div class='body'")
            
            return html;
        },

        /**
        * Handles XMPP Ping request and sends Pong as defined by XEP-0199: XMPP Ping
        *   (http://xmpp.org/extensions/xep-0199.html)
        * @param String
        **/
        handlePing: function(e){
            var xmpp = this;
            var id = e.attr('id');
            var from = e.attr('from');
            var to = e.attr('to');
            xmpp.sendCommand("<iq from='"+to+"' to='"+from+"' id='"+id+"' type='result'/>");
        }
    }
})(jQuery);

//Dependencies, you can use an external file
// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com
var Base64 = (function () {
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    var obj = {
        /**
         * Encodes a string in base64
         * @param {String} input The string to encode in base64.
         **/
        encode: function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            do {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) +
                    keyStr.charAt(enc3) + keyStr.charAt(enc4);
            } while (i < input.length);

            return output;
        },

        /**
         * Decodes a base64 string.
         * @param {String} input The string to decode.
         **/
        decode: function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            do {
                enc1 = keyStr.indexOf(input.charAt(i++));
                enc2 = keyStr.indexOf(input.charAt(i++));
                enc3 = keyStr.indexOf(input.charAt(i++));
                enc4 = keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }
            } while (i < input.length);

            return output;
        }
    };

    return obj;
})();


/**
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
 * Digest Algorithm, as defined in RFC 1321.
 * Version 2.1 Copyright (C) Paul Johnston 1999 - 2002.
 * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
 * Distributed under the BSD License
 * See http://pajhome.org.uk/crypt/md5 for more info.
 **/

var MD5 = (function () {
    /**
     * Configurable variables. You may need to tweak these to be compatible with
     * the server-side, but the defaults work in most cases.
     **/
    var hexcase = 0;  /* hex output format. 0 - lowercase; 1 - uppercase */
    var b64pad  = ""; /* base-64 pad character. "=" for strict RFC compliance */
    var chrsz   = 8;  /* bits per input character. 8 - ASCII; 16 - Unicode */

    /**
     * Add integers, wrapping at 2^32. This uses 16-bit operations internally
     * to work around bugs in some JS interpreters.
     **/
    var safe_add = function (x, y) {
        var lsw = (x & 0xFFFF) + (y & 0xFFFF);
        var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
    };

    /**
     * Bitwise rotate a 32-bit number to the left.
     **/
    var bit_rol = function (num, cnt) {
        return (num << cnt) | (num >>> (32 - cnt));
    };

    /**
     * Convert a string to an array of little-endian words
     * If chrsz is ASCII, characters >255 have their hi-byte silently ignored.
     **/
    var str2binl = function (str) {
        var bin = [];
        var mask = (1 << chrsz) - 1;
        for(var i = 0; i < str.length * chrsz; i += chrsz)
        {
            bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (i%32);
        }
        return bin;
    };

    /**
     * Convert an array of little-endian words to a string
     **/
    var binl2str = function (bin) {
        var str = "";
        var mask = (1 << chrsz) - 1;
        for(var i = 0; i < bin.length * 32; i += chrsz)
        {
            str += String.fromCharCode((bin[i>>5] >>> (i % 32)) & mask);
        }
        return str;
    };

    /**
     * Convert an array of little-endian words to a hex string.
     **/
    var binl2hex = function (binarray) {
        var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
        var str = "";
        for(var i = 0; i < binarray.length * 4; i++)
        {
            str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) +
                hex_tab.charAt((binarray[i>>2] >> ((i%4)*8  )) & 0xF);
        }
        return str;
    };

    /**
     * Convert an array of little-endian words to a base-64 string
     **/
    var binl2b64 = function (binarray) {
        var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
        var str = "";
        var triplet, j;
        for(var i = 0; i < binarray.length * 4; i += 3)
        {
            triplet = (((binarray[i   >> 2] >> 8 * ( i   %4)) & 0xFF) << 16) |
                (((binarray[i+1 >> 2] >> 8 * ((i+1)%4)) & 0xFF) << 8 ) |
                ((binarray[i+2 >> 2] >> 8 * ((i+2)%4)) & 0xFF);
            for(j = 0; j < 4; j++)
            {
                if(i * 8 + j * 6 > binarray.length * 32) { str += b64pad; }
                else { str += tab.charAt((triplet >> 6*(3-j)) & 0x3F); }
            }
        }
        return str;
    };

    /**
     * These functions implement the four basic operations the algorithm uses.
     **/
    var md5_cmn = function (q, a, b, x, s, t) {
        return safe_add(bit_rol(safe_add(safe_add(a, q),safe_add(x, t)), s),b);
    };

    var md5_ff = function (a, b, c, d, x, s, t) {
        return md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);
    };

    var md5_gg = function (a, b, c, d, x, s, t) {
        return md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);
    };

    var md5_hh = function (a, b, c, d, x, s, t) {
        return md5_cmn(b ^ c ^ d, a, b, x, s, t);
    };

    var md5_ii = function (a, b, c, d, x, s, t) {
        return md5_cmn(c ^ (b | (~d)), a, b, x, s, t);
    };

    /**
     * Calculate the MD5 of an array of little-endian words, and a bit length
     **/
    var core_md5 = function (x, len) {
        /* append padding */
        x[len >> 5] |= 0x80 << ((len) % 32);
        x[(((len + 64) >>> 9) << 4) + 14] = len;

        var a =  1732584193;
        var b = -271733879;
        var c = -1732584194;
        var d =  271733878;

        var olda, oldb, oldc, oldd;
        for (var i = 0; i < x.length; i += 16)
        {
            olda = a;
            oldb = b;
            oldc = c;
            oldd = d;

            a = md5_ff(a, b, c, d, x[i+ 0], 7 , -680876936);
            d = md5_ff(d, a, b, c, x[i+ 1], 12, -389564586);
            c = md5_ff(c, d, a, b, x[i+ 2], 17,  606105819);
            b = md5_ff(b, c, d, a, x[i+ 3], 22, -1044525330);
            a = md5_ff(a, b, c, d, x[i+ 4], 7 , -176418897);
            d = md5_ff(d, a, b, c, x[i+ 5], 12,  1200080426);
            c = md5_ff(c, d, a, b, x[i+ 6], 17, -1473231341);
            b = md5_ff(b, c, d, a, x[i+ 7], 22, -45705983);
            a = md5_ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
            d = md5_ff(d, a, b, c, x[i+ 9], 12, -1958414417);
            c = md5_ff(c, d, a, b, x[i+10], 17, -42063);
            b = md5_ff(b, c, d, a, x[i+11], 22, -1990404162);
            a = md5_ff(a, b, c, d, x[i+12], 7 ,  1804603682);
            d = md5_ff(d, a, b, c, x[i+13], 12, -40341101);
            c = md5_ff(c, d, a, b, x[i+14], 17, -1502002290);
            b = md5_ff(b, c, d, a, x[i+15], 22,  1236535329);

            a = md5_gg(a, b, c, d, x[i+ 1], 5 , -165796510);
            d = md5_gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
            c = md5_gg(c, d, a, b, x[i+11], 14,  643717713);
            b = md5_gg(b, c, d, a, x[i+ 0], 20, -373897302);
            a = md5_gg(a, b, c, d, x[i+ 5], 5 , -701558691);
            d = md5_gg(d, a, b, c, x[i+10], 9 ,  38016083);
            c = md5_gg(c, d, a, b, x[i+15], 14, -660478335);
            b = md5_gg(b, c, d, a, x[i+ 4], 20, -405537848);
            a = md5_gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
            d = md5_gg(d, a, b, c, x[i+14], 9 , -1019803690);
            c = md5_gg(c, d, a, b, x[i+ 3], 14, -187363961);
            b = md5_gg(b, c, d, a, x[i+ 8], 20,  1163531501);
            a = md5_gg(a, b, c, d, x[i+13], 5 , -1444681467);
            d = md5_gg(d, a, b, c, x[i+ 2], 9 , -51403784);
            c = md5_gg(c, d, a, b, x[i+ 7], 14,  1735328473);
            b = md5_gg(b, c, d, a, x[i+12], 20, -1926607734);

            a = md5_hh(a, b, c, d, x[i+ 5], 4 , -378558);
            d = md5_hh(d, a, b, c, x[i+ 8], 11, -2022574463);
            c = md5_hh(c, d, a, b, x[i+11], 16,  1839030562);
            b = md5_hh(b, c, d, a, x[i+14], 23, -35309556);
            a = md5_hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
            d = md5_hh(d, a, b, c, x[i+ 4], 11,  1272893353);
            c = md5_hh(c, d, a, b, x[i+ 7], 16, -155497632);
            b = md5_hh(b, c, d, a, x[i+10], 23, -1094730640);
            a = md5_hh(a, b, c, d, x[i+13], 4 ,  681279174);
            d = md5_hh(d, a, b, c, x[i+ 0], 11, -358537222);
            c = md5_hh(c, d, a, b, x[i+ 3], 16, -722521979);
            b = md5_hh(b, c, d, a, x[i+ 6], 23,  76029189);
            a = md5_hh(a, b, c, d, x[i+ 9], 4 , -640364487);
            d = md5_hh(d, a, b, c, x[i+12], 11, -421815835);
            c = md5_hh(c, d, a, b, x[i+15], 16,  530742520);
            b = md5_hh(b, c, d, a, x[i+ 2], 23, -995338651);

            a = md5_ii(a, b, c, d, x[i+ 0], 6 , -198630844);
            d = md5_ii(d, a, b, c, x[i+ 7], 10,  1126891415);
            c = md5_ii(c, d, a, b, x[i+14], 15, -1416354905);
            b = md5_ii(b, c, d, a, x[i+ 5], 21, -57434055);
            a = md5_ii(a, b, c, d, x[i+12], 6 ,  1700485571);
            d = md5_ii(d, a, b, c, x[i+ 3], 10, -1894986606);
            c = md5_ii(c, d, a, b, x[i+10], 15, -1051523);
            b = md5_ii(b, c, d, a, x[i+ 1], 21, -2054922799);
            a = md5_ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
            d = md5_ii(d, a, b, c, x[i+15], 10, -30611744);
            c = md5_ii(c, d, a, b, x[i+ 6], 15, -1560198380);
            b = md5_ii(b, c, d, a, x[i+13], 21,  1309151649);
            a = md5_ii(a, b, c, d, x[i+ 4], 6 , -145523070);
            d = md5_ii(d, a, b, c, x[i+11], 10, -1120210379);
            c = md5_ii(c, d, a, b, x[i+ 2], 15,  718787259);
            b = md5_ii(b, c, d, a, x[i+ 9], 21, -343485551);

            a = safe_add(a, olda);
            b = safe_add(b, oldb);
            c = safe_add(c, oldc);
            d = safe_add(d, oldd);
        }
        return [a, b, c, d];
    };


    /**
     * Calculate the HMAC-MD5, of a key and some data
     **/
    var core_hmac_md5 = function (key, data) {
        var bkey = str2binl(key);
        if(bkey.length > 16) { bkey = core_md5(bkey, key.length * chrsz); }

        var ipad = new Array(16), opad = new Array(16);
        for(var i = 0; i < 16; i++)
        {
            ipad[i] = bkey[i] ^ 0x36363636;
            opad[i] = bkey[i] ^ 0x5C5C5C5C;
        }

        var hash = core_md5(ipad.concat(str2binl(data)), 512 + data.length * chrsz);
        return core_md5(opad.concat(hash), 512 + 128);
    };

    var obj = {
        /**
         * These are the functions you'll usually want to call.
         * They take string arguments and return either hex or base-64 encoded
         * strings.
         **/
        hexdigest: function (s) {
            return binl2hex(core_md5(str2binl(s), s.length * chrsz));
        },

        b64digest: function (s) {
            return binl2b64(core_md5(str2binl(s), s.length * chrsz));
        },

        hash: function (s) {
            return binl2str(core_md5(str2binl(s), s.length * chrsz));
        },

        hmac_hexdigest: function (key, data) {
            return binl2hex(core_hmac_md5(key, data));
        },

        hmac_b64digest: function (key, data) {
            return binl2b64(core_hmac_md5(key, data));
        },

        hmac_hash: function (key, data) {
            return binl2str(core_hmac_md5(key, data));
        },

        /**
         * Perform a simple self-test to see if the VM is working
         **/
        test: function () {
            return MD5.hexdigest("abc") === "900150983cd24fb0d6963f7d28e17f72";
        }
    };

    return obj;
})();
