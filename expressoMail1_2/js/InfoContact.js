 function emInfoContact()
{
	this.email = "";
	this.timeout = null;
	this.timeout_hide = null;
	this._mousemove = document.onmousemove;
	this.td;
	this.createCard();
	this.folder = get_current_folder();
}

emInfoContact.prototype.createCard = function(){
	var pic= new Image(); 
	pic.src="./templates/"+template+"/images/card.gif"; 
	card = document.createElement("DIV");
	card.id = "card_cc";
	card.style.display = "none";
	card.style.width = "244px";
	card.style.backgroundImage = "url("+pic.src+")";
	card.style.height = "134px";
	card.style.position = "absolute";
	card.innerHTML = "<table onmouseout='InfoContact.timeout_hide=setTimeout(\"InfoContact.hide()\",50);' onmouseover='clearTimeout(InfoContact.timeout_hide);' cellpadding=0 cellspacing=0 width='100%' height='100%'><tr><td valign='center' align='center' id='card_cc_td'></td></tr></table>";
	document.body.appendChild(card);
}
emInfoContact.prototype.begin = function(td, email){

	var card = Element("card_cc");
	if(email.match(/<([^<]*)>[\s]*$/))
		email = email.match(/<([^<]*)>[\s]*$/)[1];
	
	if(this.td != td){
		this.email = email;
		this.td = td;
		clearTimeout(this.timeout);
	}
	this.timeout = setTimeout("InfoContact.search('"+email+"')",1000);		
}

emInfoContact.prototype.label = function (text){
	InfoContact.hide();
	var div_label = Element("div_label");
	if(!div_label) {
		div_label = document.createElement("DIV");
		div_label.id = "div_label";
		div_label.style.padding = "2px";
		div_label.style.display = "none";
		div_label.style.position = "absolute";
		div_label.style.border = "1px solid black";
		div_label.style.backgroundColor="#FFFFDC";
		document.body.appendChild(div_label);
	}
	div_label.innerHTML = text;
	div_label.style.top = (findPosY(this.td) + 20 - Element("divScrollMain_"+numBox).scrollTop)+"px";
	div_label.style.left = (findPosX(this.td) + 20)+"px";
	div_label.style.display = '';
	setTimeout("InfoContact.hide()",1000);
}

emInfoContact.prototype.connectVoip = function (phoneUser, typePhone){
	var handler_connectVoip = function(data){
		if(!data) {
			alert(get_lang("Error contacting VoIP server."));
		}
		else{
			alert(get_lang("Requesting a VoIP call")+":\n"+data);
		}
	}
	cExecute ("$this.functions.callVoipConnect&to="+phoneUser+"&typePhone="+typePhone, handler_connectVoip);
}

emInfoContact.prototype.show = function (data){
	if (this.folder != get_current_folder()){
		this.folder = get_current_folder();
		return false;
	}	
	var _this = this;
	var card = Element("card_cc");
	card.style.left = (findPosX(this.td) + 20)+"px";
	var divScroll = Element("divScrollMain_"+numBox);
	var y = findPosY(this.td) + 20 - (divScroll ? divScroll.scrollTop : 0);
	var w_height = is_ie ? document.body.clientHeight + document.body.scrollTop : window.innerHeight + window.pageYOffset;
	if(y + 160 > w_height)
		card.style.top =  (y - 160)+"px";	
	else
		card.style.top = y+"px";		
	card.style.display = '';
	var cn = data.cn;
	if(cn && cn.toString().length > 35)
		cn = cn.toString().substring(0,30) + "...";
	
		var phoneUser;

		data.telefone ? phoneUser = data.telefone : phoneUser ="<br />";

		data.mobile ? phoneUser += "<br />&nbsp;"+data.mobile :  phoneUser += "<br />";

		data.employeeNumber ? employeeNumber = data.employeeNumber : employeeNumber ="";

		data.ou ? ou = data.ou :  ou = "";



	if(preferences.voip_enabled) {
		phoneUser = '';
		if(data.telefone)
			phoneUser = "<a title=\""+get_lang("Call to Comercial Number")+"\" href=\"#\" onclick=\"InfoContact.connectVoip('"+ data.telefone+"', 'com')\">"+ data.telefone+"</a>";
		if(data.mobile){
			phoneUser += "<br />&nbsp;<a title=\""+get_lang("Call to Mobile Number")+"\" href=\"#\" onclick=\"InfoContact.connectVoip('"+data.mobile+"', 'mob')\">"+data.mobile+"</a>";
		}
	}

		Element("card_cc_td").innerHTML =
						"<table cellpadding=0 cellspacing=0 border=0 height='100%' width='100%'><tr>"+
						"<td  style='padding-top:4px' align='center' valign='center' colspan ='2'><img src='templates/"+template+"/images/"+(data.type)+"_catalog.png' /><font size=1 color=BLACK>&nbsp;<b>"+get_lang("Sender's Information")+"</b></font>"+_this.verifyIM(data.uid,data.email)+"</td></tr>"+

						"<tr><td align='center' style='width:70px;height:93px;padding-left:6px' align='center' valign='center'>"+
						"<img style='float:left' src='./inc/show_img.php?email="+data.email+"'></td>"+
						"<td style='padding-left:2px' width='70%' align='left' valign='top'>"+
						"<br /><img style='float:left'align='center' src='templates/"+template+"/images/phone.gif' />&nbsp;<font  size=1  color=BLACK>"+(phoneUser ? phoneUser : get_lang("None") )+"</font><br />"+
						"<br /><font size=1 color=BLACK>"+cn+"</font><br /><b>"+employeeNumber+"</b>"+
						"<br/>"+ou+"</td></tr>"+
						"<tr><td  style='padding-bottom:4px' align='center' valign='center' colspan ='2' nowrap><span title='"+get_lang("Write message")+"' style='cursor:pointer' onclick='InfoContact.sendMail(\""+cn+"\",\""+data.email+"\")'><font size=1 color=DARKBLUE><u>"+data.email+"</u></font></span>"+
										"</td></tr></table>";


	this.timeout_hide = setTimeout("InfoContact.hide()",1000);	
}

emInfoContact.prototype.search = function (email){
	var _this = this;
	var trustedDomain = false;
	//	If "preferences.notification_domains" was setted, then verify if "mail" has a trusted domain.	
	if (preferences.notification_domains != undefined && preferences.notification_domains != "") {
		var domains = preferences.notification_domains.split(',');
		for (var i = 0; i < domains.length; i++) {
			if (email.toString().match(domains[i]))
				trustedDomain = true;
		}
	}
	else
		trustedDomain = true;

	var handler_search = function(data){
		if(data != null){
			_this.show(data);
		}
		else
			_this.label(email);			
	}
	
	if (trustedDomain)
		cExecute ("$this.ldap_functions.getUserByEmail&email="+email, handler_search);
	else
		_this.label(email);
}

emInfoContact.prototype.hide = function(){
	this.email = "";
	if(Element("div_label"))
		Element("div_label").style.display = 'none';

	if(Element("card_cc")) 
		Element("card_cc").style.display = "none";	
}

emInfoContact.prototype.sendMail = function(name, email){
	Element("msg_number").value = "\""+ name+"\" <"+email+">";
	InfoContact.hide();
	new_message_to(email);
}

emInfoContact.prototype.openChat = function(event, email){
	IM.action_button(event, '1', email ,false);
}

emInfoContact.prototype.verifyIM = function(uid, email){

	if ( !window.IM || !document.getElementById('myStatus') )
		return  "";

	var status = IM.infoContact(uid);
	var _return = '<br/>';

	if ( status )
	{
		_return += '<img align="center" src="'+status.src+'" />';
		_return	+= '<span onclick="IM.action_button(event,\''+status.jid+'\');"><font size="1" color=';
		
		if( status.src != img_unavailable.src)
			_return 	+= '"DARKBLUE"><u style="cursor:pointer;">'+get_lang("User connected")+"</u>";
		else
			_return 	+= '"BLACK">'+get_lang("User not connected");

		_return	+= "</font></span><br />";
	}

	return _return;
}
/* Build the Object */
var emInfoContact;
InfoContact = new emInfoContact();
