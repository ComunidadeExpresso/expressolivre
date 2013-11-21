DOMObjects={xmlParse:function(xmlString){var xmlObj=this.xmlRender(xmlString);if(xmlObj){try{if(this.processor==undefined){this.processor=new XSLTProcessor();this.processor.importStylesheet(this.xmlRender('<xsl:stylesheet version="1.0"\
                    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">\
                    <xsl:output method="html" indent="yes"/><xsl:template\
                    match="@*|node()"><xsl:copy><xsl:copy-of\
                    select="@*|node()"/></xsl:copy></xsl:template>\
                    </xsl:stylesheet>'));}
var htmlObj=this.processor.transformToDocument(xmlObj).documentElement;if(htmlObj.tagName.toLowerCase()=='html'){htmlObj=htmlObj.firstChild.firstChild;}
return document.importNode(htmlObj,true);}catch(e){try{var htmlObj=document.importNode(xmlObj.documentElement,true);if(htmlObj.tagName.toLowerCase()=="div"){var div_wrapper=document.createElement('div');div_wrapper.appendChild(htmlObj);if(div_wrapper.innerHTML){div_wrapper.innerHTML=div_wrapper.innerHTML;}
htmlObj=div_wrapper.firstChild;}
return htmlObj;}catch(e){alert("TrophyIM Error: Cannot add html to page "+e.message);}}}},xmlRender:function(xmlString){try{var renderObj=new ActiveXObject("Microsoft.XMLDOM");renderObj.async="false";if(xmlString){renderObj.loadXML(xmlString);}}catch(e){try{if(this.parser==undefined){this.parser=new DOMParser();}
var renderObj=this.parser.parseFromString(xmlString,"application/xml");}catch(e){alert("TrophyIM Error: Cannot create new html for page");}}
return renderObj;},getHTML:function(page)
{return this.xmlParse(HTMLSnippets[page]);},getScript:function(script)
{var newscript=document.createElement('script');newscript.setAttribute('src',script);newscript.setAttribute('type','text/javascript');return newscript;}};TrophyIM={controll:{notificationNewUsers:0},autoConnection:{connect:true},activeChatRoom:{name:[]},chatHistory:{},constants:{stale_roster:false},posWindow:{left:400,top:100},statusConn:{connected:false},_timeOut:{renderRoster:null},removeResult:{idResult:[]},setCookie:function(name,value)
{var expire=new Date();expire.setDate(expire.getDate()+365);document.cookie=name+"="+value+"; expires="+expire.toGMTString();},delCookie:function(name)
{var expire=new Date();expire.setDate(expire.getDate()-365);document.cookie=name+"= ; expires="+expire.toGMTString();delete TrophyIM.cookies[name];},getCookies:function()
{var cObj={};var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++)
{while(cookies[i].charAt(0)==' ')
{cookies[i]=cookies[i].substring(1,cookies[i].length);}
if(cookies[i].substr(0,8)=="trophyim")
{var nvpair=cookies[i].split("=",2);cObj[nvpair[0]]=nvpair[1];TrophyIM.setCookie(nvpair[0],nvpair[1]);}}
return cObj;},load:function()
{if(loadscript.getUserCurrent()==null)
{loadscript.setUserCurrent();}
if(!TrophyIM.statusConn.connected)
{TrophyIM.cookies=TrophyIM.getCookies();setTimeout("TrophyIM.showLogin()",550);}
else
{loadscript.rosterDiv();}},storeData:function()
{if(TrophyIM.connection&&TrophyIM.connection.connected)
{TrophyIM.setCookie('trophyim_bosh_xid',TrophyIM.connection.jid+"|"+
TrophyIM.connection.sid+"|"+TrophyIM.connection.rid);TrophyIM.rosterObj.save();}},showLogin:function()
{if(typeof(JSON)!=undefined&&typeof(Strophe)!=undefined)
{TrophyIM.JSONStore=new TrophyIMJSONStore();if(TrophyIM.JSONStore.store_working&&TrophyIM.cookies['trophyim_bosh_xid'])
{var xids=TrophyIM.cookies['trophyim_bosh_xid'].split("|");TrophyIM.delCookie('trophyim_bosh_xid');TrophyIM.constants.stale_roster=true;TrophyIM.connection=new Strophe.Connection(TROPHYIM_BOSH_SERVICE);TrophyIM.connection.rawInput=TrophyIM.rawInput;TrophyIM.connection.rawOutput=TrophyIM.rawOutput;Strophe.info('Attempting Strophe attach.');TrophyIM.connection.attach(xids[0],xids[1],xids[2],TrophyIM.onConnect);TrophyIM.onConnect(Strophe.Status.CONNECTED);}
else
{loadscript.rosterDiv();var _getUserCurrent=null;_getUserCurrent=loadscript.getUserCurrent();if(_getUserCurrent==null)
{setTimeout("TrophyIM.showLogin()",500);}
else
{TrophyIM.login(Base64.decode(_getUserCurrent.jid),Base64.decode(_getUserCurrent.password));}}}
else
{setTimeout("TrophyIM.showLogin()",500);}},log:function(level,msg)
{if(TrophyIM.logging_div&&level>=TROPHYIM_LOGLEVEL)
{while(TrophyIM.logging_div.childNodes.length>TROPHYIM_LOG_LINES)
{TrophyIM.logging_div.removeChild(TrophyIM.logging_div.firstChild);}
var msg_div=document.createElement('div');msg_div.className='trophyimlogitem';msg_div.appendChild(document.createTextNode(msg));TrophyIM.logging_div.appendChild(msg_div);TrophyIM.logging_div.scrollTop=TrophyIM.logging_div.scrollHeight;}},rawInput:function(data)
{Strophe.debug("RECV: "+data);},rawOutput:function(data)
{Strophe.debug("SEND: "+data);},login:function()
{if(TrophyIM.JSONStore.store_working)
{TrophyIM.JSONStore.delData(['groups','roster','active_chat','chat_history']);}
TrophyIM.connection=new Strophe.Connection(TROPHYIM_BOSH_SERVICE);TrophyIM.connection.rawInput=TrophyIM.rawInput;TrophyIM.connection.rawOutput=TrophyIM.rawOutput;if(arguments.length>0)
{var barejid=arguments[0];var password=arguments[1];TrophyIM.connection.connect(barejid+TROPHYIM_RESOURCE,password,TrophyIM.onConnect);}
else
{var barejid=document.getElementById('trophyimjid').value
var fulljid=barejid+TROPHYIM_RESOURCE;var password=document.getElementById('trophyimpass').value;var button=document.getElementById('trophyimconnect');loadscript.setUserCurrent(barejid,password);if(button.value=='connect')
{button.value='disconnect';TrophyIM.login(barejid,password);_winBuild('window_login_page','remove');}}
TrophyIM.setCookie('trophyimjid',barejid);},logout:function()
{TrophyIM.autoConnection.connect=false;TrophyIM.delCookie('trophyim_bosh_xid');delete TrophyIM['cookies']['trophyim_bosh_xid'];TrophyIM.connection.disconnect();},onConnect:function(status)
{var loading_gif=document.getElementById("JabberIMRosterLoadingGif");if(status==Strophe.Status.CONNECTING)
{loading_gif.style.display="block";Strophe.info('Strophe is connecting.');}
if(status==Strophe.Status.CONNFAIL)
{TrophyIM.delCookie('trophyim_bosh_xid');TrophyIM.statusConn.connected=false;loading_gif.style.display="block";}
if(status==Strophe.Status.DISCONNECTING)
{TrophyIM.statusConn.connected=false;}
if(status==Strophe.Status.DISCONNECTED)
{if(TrophyIM.autoConnection.connect)
{loading_gif.style.display="block";TrophyIM.delCookie('trophyim_bosh_xid');TrophyIM.statusConn.connected=false;setTimeout(function()
{TrophyIM.showLogin();},10000);loadscript.clrAllContacts();loadscript.setStatusJabber(i18n.STATUS_ANAVAILABLE,"unavailable");delete TrophyIM.rosterObj.roster;delete TrophyIM.rosterObj.groups;}}
if(status==Strophe.Status.CONNECTED)
{loadscript.setStatusJabber(i18n.STATUS_AVAILABLE,'available');TrophyIM.statusConn.connected=true;TrophyIM.showClient();}},showClient:function()
{TrophyIM.setCookie('trophyim_bosh_xid',TrophyIM.connection.jid+"|"+
TrophyIM.connection.sid+"|"+TrophyIM.connection.rid);TrophyIM.rosterObj=new TrophyIMRoster();TrophyIM.connection.addHandler(TrophyIM.onVersion,Strophe.NS.VERSION,'iq',null,null,null);TrophyIM.connection.addHandler(TrophyIM.onRoster,Strophe.NS.ROSTER,'iq',null,null,null);TrophyIM.connection.addHandler(TrophyIM.onPresence,null,'presence',null,null,null);TrophyIM.connection.addHandler(TrophyIM.onMessage,null,'message',null,null,null);TrophyIM.connection.send($iq({type:'get',xmlns:Strophe.NS.CLIENT}).c('query',{xmlns:Strophe.NS.ROSTER}).tree());TrophyIM.connection.send($pres().tree());setTimeout(TrophyIM.renderRoster,1000);},clearClient:function()
{if(TrophyIM.logging_div)
{var logging_div=TrophyIM.client_div.removeChild(document.getElementById('trophyimlog'));}
else
{var logging_div=null;}
while(TrophyIM.client_div.childNodes.length>0)
{TrophyIM.client_div.removeChild(TrophyIM.client_div.firstChild);}
return logging_div;},onVersion:function(msg)
{Strophe.debug("Version handler");if(msg.getAttribute('type')=='get')
{var from=msg.getAttribute('from');var to=msg.getAttribute('to');var id=msg.getAttribute('id');var reply=$iq({type:'result',to:from,from:to,id:id}).c('query',{name:"TrophyIM",version:TROPHYIM_VERSION,os:"Javascript-capable browser"});TrophyIM.connection.send(reply.tree());}
return true;},onRoster:function(msg)
{var roster_items=msg.firstChild.getElementsByTagName('item');for(var i=0;i<roster_items.length;i++)
{with(roster_items[i])
{var groups=getElementsByTagName('group');var group_array=[];for(var g=0;g<groups.length;g++)
{if(groups[g].hasChildNodes())
group_array[group_array.length]=groups[g].firstChild.nodeValue;}
if(getAttribute('ask')&&getAttribute('ask').toString()==="subscribe")
{if(getAttribute('subscription').toString()==="none")
{TrophyIM.rosterObj.addContact(getAttribute('jid'),getAttribute('ask'),getAttribute('name'),group_array);}
if(getAttribute('subscription').toString()==="remove")
{TrophyIM.rosterObj.removeContact(getAttribute('jid').toString());}}
else
{if((getAttribute('ask')==null&&getAttribute('subscription').toString()==="remove")||getAttribute('subscription').toString()==="remove")
{TrophyIM.rosterObj.removeContact(getAttribute('jid').toString());}
else
{TrophyIM.rosterObj.addContact(getAttribute('jid'),getAttribute('subscription'),getAttribute('name'),group_array);}}}}
if(msg.getAttribute('type')=='set')
{var _iq=$iq({type:'reply',id:msg.getAttribute('id'),to:msg.getAttribute('from')});TrophyIM.connection.send(_iq.tree());}
return true;},onPresence:function(msg)
{TrophyIM.onPresenceChatRoom(msg);var type=msg.getAttribute('type')?msg.getAttribute('type'):'available';var show=msg.getElementsByTagName('show').length?Strophe.getText(msg.getElementsByTagName('show')[0]):type;var status=msg.getElementsByTagName('status').length?Strophe.getText(msg.getElementsByTagName('status')[0]):'';var priority=msg.getElementsByTagName('priority').length?parseInt(Strophe.getText(msg.getElementsByTagName('priority')[0])):0;if(msg.getAttribute('from').toString().indexOf(TROPHYIM_CHATROOM)<0)
{var _from=Strophe.getBareJidFromJid(msg.getAttribute('from'));var _flag=true;if(TrophyIM.removeResult.idResult.length>0)
{for(var i=0;i<TrophyIM.removeResult.idResult.length;i++)
{if(TrophyIM.removeResult.idResult[i]==_from)
{_flag=false;TrophyIM.removeResult.idResult.splice(i,1);i--;if(show.toLowerCase()==='subscribe')
_flag=true;}}}
if(_flag)
TrophyIM.rosterObj.setPresence(msg.getAttribute('from'),priority,show,status);}
return true;},onPresenceChatRoom:function(msg)
{var xquery=msg.getElementsByTagName("x");var _error=msg.getElementsByTagName("error");if(_error.length>0)
{for(var i=0;i<_error.length;i++)
{if(_error[i].getElementsByTagName("text"))
{var _errorMsg=Strophe.getText(_error[i].getElementsByTagName("text")[0]);if(_errorMsg=="Room creation is denied by service policy")
{alert(i18n.ROOM_CREATION_IS_DENIED_BY_SERVICE_POLICY);}
else
{alert(" Informe ao seu Administrador ERRO : \n"+_errorMsg);}}}}
else
{if(xquery.length>0)
{for(var i=0;i<xquery.length;i++)
{var xmlns=xquery[i].getAttribute("xmlns");if(xmlns.indexOf("http://jabber.org/protocol/muc#user")==0)
{var _from=xquery[i].parentNode.getAttribute('from');var _to=xquery[i].parentNode.getAttribute('to');var nameChatRoom=Strophe.getBareJidFromJid(_from);var nickName=Strophe.getResourceFromJid(_from);var type=(xquery[i].parentNode.getAttribute('type')!=null)?xquery[i].parentNode.getAttribute('type'):'available';var show=(xquery[i].parentNode.firstChild.nodeName=="show")?xquery[i].parentNode.firstChild.firstChild.nodeValue:type;var _idElement=nameChatRoom+"_UserChatRoom__"+nickName;var _UserChatRoom=document.createElement("div");_UserChatRoom.id=_idElement;_UserChatRoom.style.paddingLeft='18px';_UserChatRoom.style.margin='3px 0px 0px 2px';_UserChatRoom.style.background='url("'+path_jabberit+'templates/default/images/'+show+'.gif") no-repeat center left';_UserChatRoom.appendChild(document.createTextNode(nickName));var nodeUser=document.getElementById(_idElement);if(nodeUser==null)
{if(document.getElementById(nameChatRoom+'__roomChat__participants')!=null)
{nameChatRoom=document.getElementById(nameChatRoom+'__roomChat__participants');nameChatRoom.appendChild(_UserChatRoom);}
else
{if(type!='unavailable')
{TrophyIM.makeChatRoom(nameChatRoom,nameChatRoom.substring(0,nameChatRoom.indexOf('@')));nameChatRoom=document.getElementById(nameChatRoom+'__roomChat__participants');nameChatRoom.appendChild(_UserChatRoom);}}}
else
{if(type=='unavailable')
{nodeUser.parentNode.removeChild(nodeUser);}
else if(show)
{nodeUser.style.backgroundImage='url("'+path_jabberit+'templates/default/images/'+show+'.gif")';}}}}}}},onMessage:function(msg)
{var checkTime=function(i)
{if(i<10)i="0"+i;return i;};var messageDate=function(_date)
{var _dt=_date.substr(0,_date.indexOf('T')).split('-');var _hr=_date.substr(_date.indexOf('T')+1,_date.length-_date.indexOf('T')-2).split(':');(_date=new Date).setTime(Date.UTC(_dt[0],_dt[1]-1,_dt[2],_hr[0],_hr[1],_hr[2]));return(_date.toLocaleDateString().replace(/-/g,'/')+' '+_date.toLocaleTimeString());};var data=new Date();var dtNow=checkTime(data.getHours())+":"+checkTime(data.getMinutes())+":"+checkTime(data.getSeconds());var from=msg.getAttribute('from');var type=msg.getAttribute('type');var elems=msg.getElementsByTagName('body');var delay=(msg.getElementsByTagName('delay'))?msg.getElementsByTagName('delay'):null;var stamp=(delay[0]!=null)?"<font style='color:red;'>"+messageDate(delay[0].getAttribute('stamp'))+"</font>":dtNow;var barejid=Strophe.getBareJidFromJid(from);var jidChatRoom=Strophe.getResourceFromJid(from);var jid_lower=barejid.toLowerCase();var contact="";var state="";var chatBox=document.getElementById(jid_lower+"__chatState");var chatStateOnOff=null;var active=msg.getElementsByTagName('active');contact=barejid.toLowerCase();contact=contact.substring(0,contact.indexOf('@'));if(TrophyIM.rosterObj.roster[barejid])
{if(TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'])
{contact=TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'];}}
if(elems.length>0)
{state="";chatStateOnOff=document.getElementById(jid_lower+"__chatStateOnOff");if(active.length>0&chatStateOnOff!=null)
{chatStateOnOff.value='on';}
var _message=document.createElement("div");var _text=Strophe.getText(elems[0]);_text=_text.replace(/onblur/gi,"EVENT_DENY");_text=_text.replace(/onchange/gi,"EVENT_DENY");_text=_text.replace(/onclick/gi,"EVENT_DENY");_text=_text.replace(/ondblclick/gi,"EVENT_DENY");_text=_text.replace(/onerror/gi,"EVENT_DENY");_text=_text.replace(/onfocus/gi,"EVENT_DENY");_text=_text.replace(/onkeydown/gi,"EVENT_DENY");_text=_text.replace(/onkeypress/gi,"EVENT_DENY");_text=_text.replace(/onkeyup/gi,"EVENT_DENY");_text=_text.replace(/onmousedown/gi,"EVENT_DENY");_text=_text.replace(/onmousemove/gi,"EVENT_DENY");_text=_text.replace(/onmouseout/gi,"EVENT_DENY");_text=_text.replace(/onmouseover/gi,"EVENT_DENY");_text=_text.replace(/onmouseup/gi,"EVENT_DENY");_text=_text.replace(/onresize/gi,"EVENT_DENY");_text=_text.replace(/onselect/gi,"EVENT_DENY");_text=_text.replace(/onunload/gi,"EVENT_DENY");_text=_text.replace(/style/gi,"EVENT_DENY");_text=_text.replace(/img /gi,"IMG_DENY ");_text=_text.replace(/script /gi,"SCRIPT_DENY ");_text=_text.replace(/div /gi,"DIV_DENY ");_text=_text.replace(/span /gi,"SPAN_DENY ");_text=_text.replace(/iframe /gi,"IFRAME_DENY ");_message.innerHTML=_text;var scripts=_message.getElementsByTagName('script_deny');for(var i=0;i<scripts.length;i++){_message.removeChild(scripts[i--]);}
var _imgSrc=_message.getElementsByTagName('img_deny');for(var i=0;i<_imgSrc.length;i++){_imgSrc[i].parentNode.removeChild(_imgSrc[i--]);}
var _Div=_message.getElementsByTagName('div_deny');for(var i=0;i<_Div.length;i++){_Div[i].parentNode.removeChild(_Div[i--]);}
var _Span=_message.getElementsByTagName('span_deny');for(var i=0;i<_Span.length;i++){_Span[i].parentNode.removeChild(_Span[i--]);}
var _Iframe=_message.getElementsByTagName('iframe_deny');for(var i=0;i<_Iframe.length;i++){_Iframe[i].parentNode.removeChild(_Iframe[i--]);}
var _aHref=_message.getElementsByTagName('a');for(var i=0;i<_aHref.length;i++){_aHref[i].parentNode.removeChild(_aHref[i--]);}
_message.innerHTML=_message.innerHTML.replace(/^\s+|\s+$|^\n|\n$/g,"");_message.innerHTML=loadscript.getSmiles(_message.innerHTML);if(type=='chat'||type=='normal')
{if(_message.hasChildNodes())
{var message={contact:"["+stamp+"] <font style='font-weight:bold; color:black;'>"+contact+"</font>",msg:"</br>"+_message.innerHTML};TrophyIM.addMessage(TrophyIM.makeChat(from),jid_lower,message);}}
else if(type=='groupchat')
{if(_message.hasChildNodes())
{var message={contact:"["+stamp+"] <font style='font-weight:bold; color:black;'>"+jidChatRoom+"</font>",msg:"</br>"+_message.innerHTML};TrophyIM.addMessage(TrophyIM.makeChatRoom(barejid),jid_lower,message);}}}
else
{if(chatBox!=null)
state=TrophyIM.getChatState(msg);}
var clearChatState=function()
{chatBox.innerHTML='';}
if(chatBox!=null)
{var clearChatStateTimer;chatBox.innerHTML="<font style='font-weight:bold; color:grey; float:right;'>"+state+"</font>";var _composing=msg.getElementsByTagName('composing');if(_composing.length==0)
clearChatStateTimer=setTimeout(clearChatState,2000);else
clearTimeout(clearChatStateTimer);}
return true;},getChatState:function(msg)
{var state=msg.getElementsByTagName('inactive');if(state.length>0)
{return i18n.INACTIVE;}
else
{state=msg.getElementsByTagName('gone');if(state.length>0)
{return i18n.GONE;}
else
{state=msg.getElementsByTagName('composing');if(state.length>0)
{return i18n.COMPOSING;}
else
{state=msg.getElementsByTagName('paused');if(state.length>0)
{return i18n.PAUSED;}}}}
return'';},makeChat:function(fulljid)
{var barejid=Strophe.getBareJidFromJid(fulljid);var titleWindow="";var paramsChatBox={'enabledPopUp':((loadscript.getBrowserCompatible())?"block":"none"),'idChatBox':barejid+"__chatBox",'jidTo':barejid,'path_jabberit':path_jabberit};titleWindow=barejid.toLowerCase();titleWindow=titleWindow.substring(0,titleWindow.indexOf('@'));if(TrophyIM.rosterObj.roster[barejid])
{if(TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'])
{titleWindow=TrophyIM.rosterObj.roster[barejid.toLowerCase()]['contact']['name'];}}
TrophyIM.posWindow.top=TrophyIM.posWindow.top+10;if(TrophyIM.posWindow.top>200)
TrophyIM.posWindow.top=100;TrophyIM.posWindow.left=TrophyIM.posWindow.left+5;if(TrophyIM.posWindow.left>455)
TrophyIM.posWindow.left=400;var _content=document.createElement('div');_content.innerHTML=loadscript.parse("chat_box","chatBox.xsl",paramsChatBox);_content=_content.firstChild;var _messages=_content.firstChild.firstChild;var _textarea=_content.getElementsByTagName('textarea').item(0);var _send=_content.getElementsByTagName('input').item(0);var _chatStateOnOff=_content.getElementsByTagName('input').item(1);var _send_message=function()
{if(!TrophyIM.sendMessage(barejid,_textarea.value))
return false;TrophyIM.addMessage(_messages,barejid,{contact:"<font style='font-weight:bold; color:red;'>"+i18n.ME+"</font>",msg:"<br/>"+_textarea.value});_textarea.value='';_textarea.focus();};var composingTimer_=0;var isComposing_=0;var timeCounter;var setComposing=function()
{var checkComposing=function()
{if(!isComposing_){composingTimer_=0;clearInterval(timeCounter);TrophyIM.sendContentMessage(barejid,'paused');}else{TrophyIM.sendContentMessage(barejid,'composing');}
isComposing_=0;}
if(!composingTimer_){composingTimer_=1;timeCounter=setInterval(checkComposing,4000);}
isComposing_=1;};loadscript.configEvents(_send,'onclick',_send_message);loadscript.configEvents(_textarea,'onkeyup',function(e)
{if(e.keyCode==13){_send_message();composingTimer_=0;clearInterval(timeCounter);}else{if(_chatStateOnOff.value=='on')
setComposing();}});var winChatBox={id_window:"window_chat_area_"+barejid,barejid:barejid,width:387,height:375,top:TrophyIM.posWindow.top,left:TrophyIM.posWindow.left,draggable:true,visible:"display",resizable:true,zindex:loadscript.getZIndex(),title:titleWindow,closeAction:"hidden",content:_content}
_win=_winBuild(winChatBox);loadscript.notification(barejid);loadscript.getPhotoUser(barejid);_textarea.focus();_messages=_win.content().firstChild;while(_messages&&_messages.nodeType!==1)
{_messages=_messages.nextSibling;}
return(_messages);},makeChatRoom:function()
{var jidChatRoom=arguments[0];var titleWindow="ChatRoom - "+unescape(arguments[1]);var paramsChatRoom={'idChatRoom':jidChatRoom+"__roomChat",'jidTo':jidChatRoom,'lang_Send':i18n.SEND,'lang_Leave_ChatRoom':i18n.LEAVE_CHATROOM,'path_jabberit':path_jabberit};TrophyIM.posWindow.top=TrophyIM.posWindow.top+10;if(TrophyIM.posWindow.top>200)
TrophyIM.posWindow.top=100;TrophyIM.posWindow.left=TrophyIM.posWindow.left+5;if(TrophyIM.posWindow.left>455)
TrophyIM.posWindow.left=400;var _content=document.createElement('div');_content.innerHTML=loadscript.parse("chat_room","chatRoom.xsl",paramsChatRoom);_content=_content.firstChild;var _messages=_content.firstChild.firstChild;var _textarea=_content.getElementsByTagName('textarea').item(0);var _send=_content.getElementsByTagName('input').item(0);var _leaveChatRoom=_content.getElementsByTagName('input').item(1);var _send_message=function()
{if(!TrophyIM.sendMessageChatRoom(jidChatRoom,_textarea.value))
return false;_textarea.value='';_textarea.focus();};loadscript.configEvents(_send,'onclick',_send_message);loadscript.configEvents(_leaveChatRoom,'onclick',function()
{TrophyIM.leaveChatRoom(jidChatRoom);if(TrophyIM.activeChatRoom.name.length>0)
{for(var i=0;i<TrophyIM.activeChatRoom.name.length;i++)
{if(TrophyIM.activeChatRoom.name[i].indexOf(jidChatRoom)>=0)
{TrophyIM.activeChatRoom.name[i]="";}}}
setTimeout(function()
{_winBuild("window_chat_room_"+jidChatRoom,"remove");},650);});loadscript.configEvents(_textarea,'onkeyup',function(e)
{if(e.keyCode==13)
{_send_message();}});var winChatRoom={id_window:"window_chat_room_"+arguments[0],barejid:jidChatRoom,width:500,height:450,top:TrophyIM.posWindow.top,left:TrophyIM.posWindow.left,draggable:true,visible:"display",resizable:true,zindex:loadscript.getZIndex(),title:titleWindow,closeAction:"hidden",content:_content}
_win=_winBuild(winChatRoom);_messages=_win.content().firstChild;while(_messages&&_messages.nodeType!==1)
{_messages=_messages.nextSibling;}
return(_messages);},addContact:function(jidTo,name,group)
{var _flag=true;if(TrophyIM.removeResult.idResult.length>0)
{for(var i=0;i<TrophyIM.removeResult.idResult.length;i++)
{if(TrophyIM.removeResult.idResult[i]==jidTo)
{_flag=false;TrophyIM.removeResult.idResult.splice(i,1);i--;}}}
if(_flag)
{var _id=TrophyIM.connection.getUniqueId('add');var newContact=$iq({type:'set',id:_id});newContact=newContact.c('query').attrs({xmlns:'jabber:iq:roster'});newContact=newContact.c('item').attrs({jid:jidTo,name:name});newContact=newContact.c('group').t(group).tree();TrophyIM.connection.send(newContact);}},addMessage:function(chatBox,jid,msg)
{msg.msg=loadscript.getSmiles(msg.msg);var messageDiv=document.createElement("div");messageDiv.style.margin="3px 0px 1em 3px";messageDiv.innerHTML=msg.contact+" : "+msg.msg;chatBox.appendChild(messageDiv);chatBox.scrollTop=chatBox.scrollHeight;},renameContact:function(jid)
{var name=TrophyIM.rosterObj.roster[jid].contact.name;if((name=prompt(i18n.ASK_NEW_NAME_QUESTION+name+"!",name)))
if((name=name.replace(/^\s+|\s+$|^\n|\n$/g,""))=="")
name="";if(name==null||name=="")
name="";var jidTo=jid
var name=(name)?name:TrophyIM.rosterObj.roster[jid].contact.name;var group=TrophyIM.rosterObj.roster[jid].contact.groups[0];TrophyIM.addContact(jidTo,name,group);document.getElementById('itenContact_'+jid).innerHTML=name;},renameGroup:function(jid)
{var group=TrophyIM.rosterObj.roster[jid].contact.groups[0];var presence=TrophyIM.rosterObj.roster[jid].presence;if((group=prompt(i18n.ASK_NEW_GROUP_QUESTION,group)))
if((group=group.replace(/^\s+|\s+$|^\n|\n$/g,""))=="")
group="";if(group==null||group=="")
group="";var jidTo=TrophyIM.rosterObj.roster[jid].contact.jid;var name=TrophyIM.rosterObj.roster[jid].contact.name;var group=(group)?group:TrophyIM.rosterObj.roster[jid].contact.groups[0];TrophyIM.rosterObj.removeContact(jid);TrophyIM.addContact(jidTo,name,group);document.getElementById("JabberIMRoster").innerHTML="";TrophyIM.renderRoster();setTimeout(function()
{for(var i in presence)
{if(presence[i].constructor==Function)
continue;TrophyIM.rosterObj.setPresence(jid,presence[i].priority,presence[i].show,presence[i].status);}},500);},createChatRooms:function()
{var nickName=document.getElementById('nickName_chatRoom_jabberit').value;var nameChatRoom=document.getElementById('name_ChatRoom_jabberit').value;var _from=Base64.decode(loadscript.getUserCurrent().jid)+TROPHYIM_RESOURCE;var _to=escape(nameChatRoom)+"@"+TROPHYIM_CHATROOM+"/"+nickName;var new_room=$pres({from:_from,to:_to}).c("x",{xmlns:Strophe.NS.MUC});TrophyIM.activeChatRoom.name[TrophyIM.activeChatRoom.name.length]=_to;TrophyIM.connection.send(new_room.tree());},joinChatRoom:function(roomName)
{var presence=$pres({from:TrophyIM.connection.jid,to:roomName}).c("x",{xmlns:Strophe.NS.MUC});TrophyIM.connection.send(presence);},leaveChatRoom:function(roomName)
{var room_nick=roomName;var presenceid=TrophyIM.connection.getUniqueId();var presence=$pres({type:"unavailable",id:presenceid,from:TrophyIM.connection.jid,to:room_nick}).c("x",{xmlns:Strophe.NS.MUC});TrophyIM.connection.send(presence);},getListRooms:function()
{if(TrophyIM.statusConn.connected)
{var _error_return=function(element)
{alert("ERRO : Tente novamente !");};var iq=$iq({to:TROPHYIM_CHATROOM,type:"get"}).c("query",{xmlns:Strophe.NS.DISCO_ITEMS});TrophyIM.connection.sendIQ(iq,loadscript.listRooms,_error_return,500);}
else
{alert("ERRO : Sem conexão com o servidor "+TROPHYIM_CHATROOM);}},removeContact:function(jidTo)
{var divItenContact=null;if((divItenContact=document.getElementById('itenContact_'+jidTo)))
{var _id=TrophyIM.connection.getUniqueId();TrophyIM.removeResult.idResult[TrophyIM.removeResult.idResult.length]=jidTo;var delContact=$iq({type:'set',id:_id})
delContact=delContact.c('query').attrs({xmlns:'jabber:iq:roster'});delContact=delContact.c('item').attrs({jid:jidTo,subscription:'remove'}).tree();TrophyIM.connection.send(delContact);loadscript.removeElement(document.getElementById('itenContactNotification_'+jidTo));var spanShow=document.getElementById('span_show_itenContact_'+jidTo)
spanShow.parentNode.removeChild(spanShow);loadscript.removeGroup(divItenContact.parentNode);divItenContact.parentNode.removeChild(divItenContact);}},renderRoster:function()
{var roster_div=document.getElementById('JabberIMRoster');if(roster_div)
{var users=new Array();var loading_gif=document.getElementById("JabberIMRosterLoadingGif");if(loading_gif.style.display=="block")
loading_gif.style.display="none";for(var user in TrophyIM.rosterObj.roster)
{if(TrophyIM.rosterObj.roster[user].constructor==Function)
continue;users[users.length]=TrophyIM.rosterObj.roster[user].contact.jid;}
users.sort();var groups=new Array();var flagGeral=false;for(var group in TrophyIM.rosterObj.groups)
{if(TrophyIM.rosterObj.groups[group].constructor==Function)
continue;if(group)
groups[groups.length]=group;if(group=="Geral")
flagGeral=true;}
if(!flagGeral&&users.length>0)
groups[groups.length]="Geral";groups.sort();for(var i=0;i<groups.length;i++)
{TrophyIM.renderGroups(groups[i],roster_div);}
TrophyIM.renderItensGroup(users,roster_div);}
TrophyIM._timeOut.renderRoster=setTimeout("TrophyIM.renderRoster()",1000);},renderGroups:function(nameGroup,element)
{var _addGroup=function()
{var _nameGroup=nameGroup;var _element=element;var paramsGroup={'nameGroup':_nameGroup,'path_jabberit':path_jabberit}
_element.innerHTML+=loadscript.parse("group","groups.xsl",paramsGroup);}
if(!element.hasChildNodes())
{_addGroup();}
else
{var _NodeChild=element.firstChild;var flagAdd=false;while(_NodeChild)
{if(_NodeChild.childNodes[0].nodeName.toLowerCase()==="span")
{if(_NodeChild.childNodes[0].childNodes[0].nodeValue===nameGroup)
{flagAdd=true;}}
_NodeChild=_NodeChild.nextSibling;}
if(!flagAdd)
{_addGroup();}}},renderItensGroup:function(users,element)
{var addItem=function()
{if(arguments.length>0)
{var objContact=arguments[0];var group=arguments[1];var element=arguments[2];var showOffline=loadscript.getShowContactsOffline();var presence="unavailable";var status="";var statusColor="black";var statusDisplay="none";var _resource="";var _presence=function(objContact)
{if(objContact.presence)
{for(var resource in objContact.presence)
{if(objContact.presence[resource].constructor==Function)
continue;if(objContact.presence[resource].show!='invisible')
presence=objContact.presence[resource].show;if(objContact.contact.subscription!="both")
presence='subscription';if(objContact.presence[resource].status)
{status=" ( "+objContact.presence[resource].status+" ) ";statusDisplay="block";}}}};var _subscription=function(objContact)
{if(objContact.contact.subscription!="both")
{switch(objContact.contact.subscription)
{case"none":status=" (( "+i18n.ASK_FOR_AUTH+" )) ";statusColor="red";break;case"to":status=" (( "+i18n.CONTACT_ASK_FOR_AUTH+" )) ";statusColor="orange";break;case"from":status=" (( "+i18n.AUTHORIZED+" )) ";statusColor="green";break;case"subscribe":status=" (( "+i18n.AUTH_SENT+" )) ";statusColor="red";break;case"not-in-roster":status=" (( "+i18n.ASK_FOR_AUTH_QUESTION+" )) ";statusColor="orange";break;default:break;}
statusDisplay="block";}};if(objContact.contact.subscription!="remove")
{var itensJid=document.getElementById("itenContact_"+objContact.contact.jid);if(itensJid==null)
{var nameContact="";if(objContact.contact.name)
nameContact=objContact.contact.name;else
{nameContact=objContact.contact.jid;nameContact=nameContact.substring(0,nameContact.indexOf('@'));}
_presence(objContact);var paramsContact={divDisplay:"block",id:'itenContact_'+objContact.contact.jid,jid:objContact.contact.jid,nameContact:nameContact,path_jabberit:path_jabberit,presence:presence,spanDisplay:statusDisplay,status:status,statusColor:"black",subscription:objContact.contact.subscription,resource:_resource}
_subscription(objContact);if(group!="")
{var _NodeChild=element.firstChild;while(_NodeChild)
{if(_NodeChild.childNodes[0].nodeName.toLowerCase()==="span")
{if(_NodeChild.childNodes[0].childNodes[0].nodeValue===group)
{_NodeChild.innerHTML+=loadscript.parse("itens_group","itensGroup.xsl",paramsContact);}}
_NodeChild=_NodeChild.nextSibling;}}}
else
{_presence(objContact);var is_open=itensJid.parentNode.childNodes[0].style.backgroundImage;is_open=is_open.indexOf("arrow_down.gif");_subscription(objContact);itensJid.setAttribute('subscription',objContact.contact.subscription);with(document.getElementById('span_show_'+'itenContact_'+objContact.contact.jid))
{if(presence=="unavailable"&&!showOffline)
{style.display="none";}
else
{if(is_open>0)
{style.display=statusDisplay;style.color=statusColor;innerHTML=status;}}}
if(presence=="unavailable"&&!showOffline)
{itensJid.style.display="none";}
else
{if(is_open>0)
{itensJid.style.display="block";}}
itensJid.style.background="url('"+path_jabberit+"templates/default/images/"+presence+".gif') no-repeat center left";}
if(!objContact.presence&&!showOffline)
{if(objContact.contact.subscription!="remove")
{with(document.getElementById('span_show_'+'itenContact_'+objContact.contact.jid))
{style.display="none";}
with(document.getElementById('itenContact_'+objContact.contact.jid))
{style.display="none";}}}}}};var flag=false;for(var i=0;i<users.length;i++)
{if(TrophyIM.rosterObj.roster[users[i]].contact.jid!=Base64.decode(loadscript.getUserCurrent().jid))
{var _subscription=TrophyIM.rosterObj.roster[users[i]].contact.subscription;if(_subscription==="to")
{flag=true;}
if(_subscription==="not-in-roster")
{flag=true;}
if(TrophyIM.rosterObj.roster[users[i]].contact.groups)
{var groups=TrophyIM.rosterObj.roster[users[i]].contact.groups;if(groups.length>0)
{for(var j=0;j<groups.length;j++)
{addItem(TrophyIM.rosterObj.roster[users[i]],groups[j],element);}}
else
{addItem(TrophyIM.rosterObj.roster[users[i]],"Geral",element);}}
else
{addItem(TrophyIM.rosterObj.roster[users[i]],"Geral",element);}}}
if(flag)
{if(TrophyIM.controll.notificationNewUsers==0)
{loadscript.enabledNotificationNewUsers();TrophyIM.controll.notificationNewUsers++;}}
else
{loadscript.disabledNotificationNewUsers();TrophyIM.controll.notificationNewUsers=0;}},rosterClick:function(fulljid)
{TrophyIM.makeChat(fulljid);},setAutorization:function(jidTo,jidFrom,_typeSubscription)
{var _id=TrophyIM.connection.getUniqueId();TrophyIM.connection.send($pres().attrs({from:jidFrom,to:jidTo,type:_typeSubscription,id:_id}).tree());},setPresence:function(_type)
{var presence_chatRoom="";if(_type!='status')
{if(_type=="unavailable"&&TrophyIM.statusConn.connected)
{var loading_gif=document.getElementById("JabberIMRosterLoadingGif");if(TrophyIM._timeOut.renderRoster!=null)
clearTimeout(TrophyIM._timeOut.renderRoster);if(TrophyIM.statusConn.connected)
TrophyIM.connection.send($pres({type:_type}).tree());for(var i=0;i<TrophyIM.connection._requests.length;i++)
{if(TrophyIM.connection._requests[i])
TrophyIM.connection._removeRequest(TrophyIM.connection._requests[i]);}
TrophyIM.logout();loadscript.clrAllContacts();delete TrophyIM.rosterObj.roster;delete TrophyIM.rosterObj.groups;setTimeout(function()
{if(loading_gif.style.display=="block")
loading_gif.style.display="none";},1000);}
else
{if(!TrophyIM.autoConnection.connect)
{TrophyIM.autoConnection.connect=true;TrophyIM.load();}
else
{if(TrophyIM.statusConn.connected)
{if(loadscript.getStatusMessage()!="")
{var _presence=$pres();_presence.node.appendChild(Strophe.xmlElement('show')).appendChild(Strophe.xmlTextNode(_type));_presence.node.appendChild(Strophe.xmlElement('status')).appendChild(Strophe.xmlTextNode(loadscript.getStatusMessage()));TrophyIM.connection.send(_presence.tree());presence_chatRoom=_type;}
else
{TrophyIM.connection.send($pres().c('show').t(_type).tree());presence_chatRoom=_type;}}}}}
else
{var _show="available";var _status="";if(arguments.length<2)
{if(loadscript.getStatusMessage()!="")
_status=prompt(i18n.TYPE_YOUR_MSG,loadscript.getStatusMessage());else
_status=prompt(i18n.TYPE_YOUR_MSG);var _divStatus=document.getElementById("JabberIMStatusMessage");if((_status=_status.replace(/^\s+|\s+$|^\n|\n$/g,""))!="")
_divStatus.firstChild.innerHTML="( "+_status+" )";}
else
{_status=arguments[1];}
for(var resource in TrophyIM.rosterObj.roster[Base64.decode(loadscript.getUserCurrent().jid)].presence)
{if(TrophyIM.rosterObj.roster[Base64.decode(loadscript.getUserCurrent().jid)].presence[resource].constructor==Function)
continue;if(TROPHYIM_RESOURCE===("/"+resource))
_show=TrophyIM.rosterObj.roster[Base64.decode(loadscript.getUserCurrent().jid)].presence[resource].show;}
if(TrophyIM.statusConn.connected)
{var _presence=$pres();_presence.node.appendChild(Strophe.xmlElement('show')).appendChild(Strophe.xmlTextNode(_show));_presence.node.appendChild(Strophe.xmlElement('status')).appendChild(Strophe.xmlTextNode(_status));TrophyIM.connection.send(_presence.tree());presence_chatRoom=_show;}}
if(TrophyIM.activeChatRoom.name.length>0)
{for(i=0;i<TrophyIM.activeChatRoom.name.length;i++)
{if(TrophyIM.activeChatRoom.name[i]!="")
TrophyIM.connection.send($pres({to:TrophyIM.activeChatRoom.name[i]}).c('show').t(presence_chatRoom));}}},sendMessage:function()
{if(arguments.length>0)
{var jidTo=arguments[0];var message_input=arguments[1];message_input=message_input.replace(/^\s+|\s+$|^\n|\n$/g,"");if(message_input!=""){var newMessage=$msg({to:jidTo,from:TrophyIM.connection.jid,type:'chat'});newMessage=newMessage.c('body').t(message_input);newMessage.up();newMessage=newMessage.c('active').attrs({xmlns:'http://jabber.org/protocol/chatstates'});TrophyIM.connection.send(newMessage.tree());return true;}}
return false;},sendMessageChatRoom:function()
{if(arguments.length>0)
{var room_nick=arguments[0];var message=arguments[1];var msgid=TrophyIM.connection.getUniqueId();var msg=$msg({to:room_nick,type:"groupchat",id:msgid}).c("body",{xmlns:Strophe.NS.CLIENT}).t(message);msg.up();TrophyIM.connection.send(msg);return true;}},sendContentMessage:function()
{if(arguments.length>0)
{var jidTo=arguments[0];var state=arguments[1];var newMessage=$msg({to:jidTo,from:TrophyIM.connection.jid,type:'chat'});newMessage=newMessage.c(state).attrs({xmlns:'http://jabber.org/protocol/chatstates'});TrophyIM.connection.send(newMessage.tree());}}};function TrophyIMRoster()
{if(TrophyIM.JSONStore.store_working)
{var data=TrophyIM.JSONStore.getData(['roster','groups']);this.roster=(data['roster']!=null)?data['roster']:{};this.groups=(data['groups']!=null)?data['groups']:{};}
else
{this.roster={};this.groups={};}
this.changes=new Array();if(TrophyIM.constants.stale_roster)
{for(var jid in this.roster)
{this.changes[this.changes.length]=jid;}}
this.addChange=function(jid)
{for(var c=0;c<this.changes.length;c++)
{if(this.changes[c]==jid)
{return;}}
this.changes[this.changes.length]=jid;this.changes.sort();}
this.addContact=function(jid,subscription,name,groups)
{if(subscription==="remove")
{this.removeContact(jid);}
else
{var contact={jid:jid,subscription:subscription,name:name,groups:groups}
var jid_lower=jid.toLowerCase();if(this.roster[jid_lower])
{this.roster[jid_lower]['contact']=contact;}
else
{this.roster[jid_lower]={contact:contact};}
groups=groups?groups:[''];for(var g=0;g<groups.length;g++)
{if(!this.groups[groups[g]])
{this.groups[groups[g]]={};}
this.groups[groups[g]][jid_lower]=jid_lower;}}}
this.getContact=function(jid)
{if(this.roster[jid.toLowerCase()])
{return this.roster[jid.toLowerCase()]['contact'];}}
this.getPresence=function(fulljid)
{var jid=Strophe.getBareJidFromJid(fulljid);var current=null;if(this.roster[jid.toLowerCase()]&&this.roster[jid.toLowerCase()]['presence'])
{for(var resource in this.roster[jid.toLowerCase()]['presence'])
{if(this.roster[jid.toLowerCase()]['presence'][resource].constructor==Function)
continue;var presence=this.roster[jid.toLowerCase()]['presence'][resource];if(current==null)
{current=presence}
else
{if(presence['priority']>current['priority']&&((presence['show']=="chat"
||presence['show']=="available")||(current['show']!="chat"||current['show']!="available")))
{current=presence}}}}
return current;}
this.groupHasChanges=function(group)
{for(var c=0;c<this.changes.length;c++)
{if(this.groups[group][this.changes[c]])
{return true;}}
return false;}
this.removeContact=function(jid)
{if(this.roster[jid])
{var groups=this.roster[jid].contact.groups;if(groups)
{for(var i=0;i<groups.length;i++)
{delete this.groups[groups[i]][jid];}
for(var i=0;i<groups.length;i++)
{var contacts=0;for(var contact in this.groups[groups[i]])
{if(this.groups[groups[i]][contact].constructor==Function)
continue;contacts++;}
if(!contacts)
delete this.groups[groups[i]];}}
if(this.roster[jid])
delete this.roster[jid];}}
this.setPresence=function(fulljid,priority,show,status)
{var barejid=Strophe.getBareJidFromJid(fulljid);var resource=Strophe.getResourceFromJid(fulljid);var jid_lower=barejid.toLowerCase();if(show!=='unavailable'||show!=='error')
{if(!this.roster[jid_lower])
{this.addContact(barejid,'not-in-roster');}
var presence={resource:resource,priority:priority,show:show,status:status}
if(!this.roster[jid_lower]['presence'])
{this.roster[jid_lower]['presence']={};}
this.roster[jid_lower]['presence'][resource]=presence;}}
this.save=function()
{if(TrophyIM.JSONStore.store_working)
{TrophyIM.JSONStore.setData({roster:this.roster,groups:this.groups,active_chat:TrophyIM.activeChats['current'],chat_history:TrophyIM.chatHistory});}}}
function TrophyIMJSONStore(){this.store_working=false;this._newXHR=function(handler){var xhr=null;if(window.XMLHttpRequest){xhr=new XMLHttpRequest();if(xhr.overrideMimeType){xhr.overrideMimeType("text/xml");}}else if(window.ActiveXObject){xhr=new ActiveXObject("Microsoft.XMLHTTP");}
return xhr;}
this.getData=function(vars){if(typeof(TROPHYIM_JSON_STORE)!=undefined){Strophe.debug("Retrieving JSONStore data");var xhr=this._newXHR();var getdata="get="+vars.join(",");try{xhr.open("POST",TROPHYIM_JSON_STORE,false);}catch(e){Strophe.error("JSONStore open failed.");return false;}
xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');xhr.setRequestHeader('Content-length',getdata.length);xhr.send(getdata);if(xhr.readyState==4&&xhr.status==200){try{var dataObj=JSON.parse(xhr.responseText);return this.emptyFix(dataObj);}catch(e){Strophe.error("Could not parse JSONStore response"+
xhr.responseText);return false;}}else{Strophe.error("JSONStore open failed. Status: "+xhr.status);return false;}}}
this.emptyFix=function(obj){if(typeof(obj)=="object"){for(var i in obj){if(obj[i].constructor==Function)
continue;if(i=='_empty_'){obj[""]=this.emptyFix(obj['_empty_']);delete obj['_empty_'];}else{obj[i]=this.emptyFix(obj[i]);}}}
return obj}
this.delData=function(vars){if(typeof(TROPHYIM_JSON_STORE)!=undefined){Strophe.debug("Retrieving JSONStore data");var xhr=this._newXHR();var deldata="del="+vars.join(",");try{xhr.open("POST",TROPHYIM_JSON_STORE,false);}catch(e){Strophe.error("JSONStore open failed.");return false;}
xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');xhr.setRequestHeader('Content-length',deldata.length);xhr.send(deldata);if(xhr.readyState==4&&xhr.status==200){try{var dataObj=JSON.parse(xhr.responseText);return dataObj;}catch(e){Strophe.error("Could not parse JSONStore response");return false;}}else{Strophe.error("JSONStore open failed. Status: "+xhr.status);return false;}}}
this.setData=function(vars)
{if(typeof(TROPHYIM_JSON_STORE)!=undefined)
{var senddata="set="+JSON.stringify(vars);var xhr=this._newXHR();try
{xhr.open("POST",TROPHYIM_JSON_STORE,false);}
catch(e)
{Strophe.error("JSONStore open failed.");return false;}
xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');xhr.setRequestHeader('Content-length',senddata.length);xhr.send(senddata);if(xhr.readyState==4&&xhr.status==200&&xhr.responseText=="OK"){return true;}else{Strophe.error("JSONStore open failed. Status: "+xhr.status);return false;}}}
var testData=true;if(this.setData({testData:testData})){var testResult=this.getData(['testData']);if(testResult&&testResult['testData']==true){this.store_working=true;}}}
if(document.ELEMENT_NODE==null){document.ELEMENT_NODE=1;document.ATTRIBUTE_NODE=2;document.TEXT_NODE=3;document.CDATA_SECTION_NODE=4;document.ENTITY_REFERENCE_NODE=5;document.ENTITY_NODE=6;document.PROCESSING_INSTRUCTION_NODE=7;document.COMMENT_NODE=8;document.DOCUMENT_NODE=9;document.DOCUMENT_TYPE_NODE=10;document.DOCUMENT_FRAGMENT_NODE=11;document.NOTATION_NODE=12;}
if(!document.importNode){document.importNode=function(node,allChildren){switch(node.nodeType){case document.ELEMENT_NODE:var newNode=document.createElement(node.nodeName);if(node.attributes&&node.attributes.length>0){for(var i=0;i<node.attributes.length;i++){newNode.setAttribute(node.attributes[i].nodeName,node.getAttribute(node.attributes[i].nodeName));}}
if(allChildren&&node.childNodes&&node.childNodes.length>0){for(var i=0;i<node.childNodes.length;i++){newNode.appendChild(document.importNode(node.childNodes[i],allChildren));}}
return newNode;break;case document.TEXT_NODE:case document.CDATA_SECTION_NODE:case document.COMMENT_NODE:return document.createTextNode(node.nodeValue);break;}};}
var oldonunload=window.onunload;window.onunload=function()
{if(oldonunload)
{oldonunload();}
TrophyIM.setPresence('unavailable');}