
(function()
{var Xtools=null;var conn=null;function addContact()
{if(arguments.length>0)
{var jidFrom=Base64.decode(loadscript.getUserCurrent().jid);var jidTo=arguments[0];var group="";var name=jidTo.substring(0,jidTo.indexOf('@'));var _groups="";if(TrophyIM.rosterObj)
{for(var group in TrophyIM.rosterObj.groups)
{_groups+=group+";";}
_groups=_groups.substring(0,(_groups.length-1));}
var _paramNewUser={'name_contact':name,'jidFrom':jidFrom,'jidTo':jidTo,'selectBoxOptions':_groups};var winNewUser={id_window:"new_user_jabberit",width:320,height:165,top:100,left:350,draggable:true,visible:"display",resizable:true,zindex:loadscript.getZIndex(),title:'Expresso Messenger - '+i18n.NEW_USER,closeAction:"remove",content:Xtools.parse(Xtools.xml('new_user'),'newUser.xsl',_paramNewUser)};_winBuild(winNewUser);loadscript.setSelectEditable(document.getElementById('name_group_new_user_jabberit'),5,99);var _pButtons={'lang1':i18n.ADD,'lang2':i18n.CLOSE,'onclickClose':'_winBuild("new_user_jabberit","remove");','onclickSubmit':'loadscript.addNewUser();'};document.getElementById('buttons_newuser').innerHTML=Xtools.parse(Xtools.xml('buttons_main'),'buttons.xsl',_pButtons);}
else
{var jidFrom=Base64.decode(loadscript.getUserCurrent().jid);var jidTo=getElement('user_jid_jabberIM').value;var name=getElement('user_name_jabberIM').value;var group=getElement('user_group_jabberIM').value;_winBuild('add_user_info','remove');if(jidFrom!=jidTo)
{TrophyIM.setAutorization(jidTo,jidFrom,'subscribe');TrophyIM.addContact(jidTo,name,group);}}}
function addNewUser()
{var name=getElement('name_new_user_jabberit').value;var group=getElement('name_group_new_user_jabberit').value;var jidFrom=getElement('jidFrom_new_user_jabberit').value;var jidTo=getElement('jidTo_new_user_jabberit').value;if((name=name.replace(/^\s+|\s+$|^\n|\n$/g,""))=="")
name="";if(name==null||name=="")
name="";if((group=group.replace(/^\s+|\s+$|^\n|\n$/g,""))=="")
group="";if(group==null||group=="")
group="";if(jidFrom!=jidTo)
{TrophyIM.rosterObj.removeContact(jidTo);loadscript.removeElement(getElement('itenContact_'+jidTo));loadscript.removeElement(getElement('span_show_itenContact_'+jidTo));loadscript.removeElement(getElement('itenContactNotification_'+jidTo));TrophyIM.addContact(jidTo,name,group);TrophyIM.setAutorization(jidTo,jidFrom,'subscribe');_winBuild("new_user_jabberit","remove");}}
function getElement(elementId)
{return document.getElementById(elementId);}
function search()
{var _input=document.getElementById('search_user_jabber');var _span=document.getElementById('span_searching_im');var _div=document.getElementById('list_users_ldap_im');_span.style.display="block";if(_input.value.substring((_input.value.length-1),_input.value.length)==="*")
_input.value=_input.value.substring(0,(_input.value.length-1));if(_input.value.substring(0,1)==="*")
_input.value=_input.value.substring(1,_input.value.length);conn.go('p.cc.getListContacts',function(data)
{var _paramsVar={'lang_addContact':i18n.ADD_CONTACTS,'lang_empty':i18n.NONE_RESULT_WAS_FOUND,'lang_error':i18n.TRY_AGAIN,'lang_many_results':i18n.MANY_RESULTS_PLEASE_TRY_TO_REFINE_YOUR_SEARCH};_div.innerHTML=Xtools.parse(data,'listLdapContacts.xsl',_paramsVar);var _newUser=_div.firstChild;while(_newUser)
{if(_newUser.getAttribute('photo')==='1')
{var jid=_newUser.getAttribute('jid');var ou=_newUser.getAttribute('ou');var _img_path=path_jabberit+'inc/WebService.php?'+Date.parse(new Date);_img_path+='&photo_session='+jid+'&ou='+ou;_newUser.style.backgroundImage='url('+_img_path+')';}
loadscript.configEvents(_newUser,'onclick',showContact);_newUser=_newUser.nextSibling;}
_input.focus();_input.value="";_span.style.display="none";},'name='+_input.value);}
function showContact(Element)
{var element=(Element.target)?Element.target:Element.srcElement;var infoUser=null;var img=document.createElement('img');var _groups="";if(TrophyIM.rosterObj)
{for(var group in TrophyIM.rosterObj.groups)
{_groups+=group+";";}
_groups=_groups.substring(0,(_groups.length-1));}
if(element.getAttribute('value'))
{var infoUser={'email':element.getAttribute('value').substring(0,element.getAttribute('value').indexOf(';')),'jid':element.getAttribute('jid'),'lang_group':"Grupo",'lang_name_contact':"Contato",'group':element.getAttribute('ou'),'name':element.getAttribute('name').substring(0,element.getAttribute('name').indexOf(' ')),'ou':element.getAttribute('ou'),'selectBoxOptions':_groups,'uid':element.getAttribute('value').substring(element.getAttribute('value').indexOf(';')+1)};}
else if(element.parentNode.getAttribute('value'))
{var infoUser={'email':element.parentNode.getAttribute('value').substring(0,element.parentNode.getAttribute('value').indexOf(';')),'jid':element.parentNode.getAttribute('jid'),'lang_group':"Grupo",'lang_name_contact':"Contato",'group':element.parentNode.getAttribute('ou'),'name':element.parentNode.getAttribute('name').substring(0,element.parentNode.getAttribute('name').indexOf(' ')),'ou':element.parentNode.getAttribute('ou'),'selectBoxOptions':_groups,'uid':element.parentNode.getAttribute('value').substring(element.parentNode.getAttribute('value').indexOf(';')+1)};}
var winAddUser={id_window:"add_user_info",width:370,height:200,top:85,left:220,draggable:true,visible:"display",resizable:true,zindex:loadscript.getZIndex(),title:'Expresso Messenger - '+i18n.ADD_CONTACT,closeAction:"remove",content:Xtools.parse(Xtools.xml('adduser'),'addUser.xsl',infoUser)};_winBuild(winAddUser);var _pButtons={'lang1':i18n.ADD,'lang2':i18n.CLOSE,'onclickClose':'_winBuild("'+winAddUser.id_window+'","remove");','onclickSubmit':'loadscript.addContact(this);'};document.getElementById('buttons_adduser').innerHTML=Xtools.parse(Xtools.xml('buttons_main'),'buttons.xsl',_pButtons);loadscript.setSelectEditable(document.getElementById('user_group_jabberIM'),35,155);var _img=null;if(element.style.backgroundImage)
_img=element.cloneNode(false);if(element.parentNode.style.backgroundImage)
_img=element.parentNode.cloneNode(false);if(_img!=null)
{_img.style.width='60px';_img.style.height='80px';_img.style.display='block';_img.style.backgroundRepeat='no-repeat';}
else
{_img=document.createElement("img");_img.style.width='60px';_img.style.height='80px';_img.style.display='block';_img.src=path_jabberit+"templates/default/images/photo.png";}
with(document.getElementById('photo_user_ldap_jabber'))
{if(hasChildNodes())
while(hasChildNodes())
{removeNode(firstChild);}
appendChild(_img);}}
function showForm()
{var _paramsWindAddUser={'lang_group':i18n.GROUP,'lang_load':i18n.LOAD,'lang_name_contact':i18n.NAME_CONTACT,'lang_result':i18n.SEARCH_RESULT,'path':path_jabberit};var windAddUser={id_window:"add_user_im",width:440,height:350,top:80,left:200,draggable:true,visible:"display",resizable:true,zindex:loadscript.getZIndex(),title:'Expresso Messenger - '+i18n.SEARCH_USERS,closeAction:"remove",content:Xtools.parse(Xtools.xml('userinfo'),'addUser.xsl',_paramsWindAddUser)};_winBuild(windAddUser);}
function loadAddUser()
{if(arguments.length>0)
{Xtools=arguments[0];conn=arguments[1];}}
loadAddUser.prototype.add=addContact;loadAddUser.prototype.newUser=addNewUser;loadAddUser.prototype.search=search;loadAddUser.prototype.show=showForm;window.addUserIM=loadAddUser;})();