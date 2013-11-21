/**********************************************************************************\
* Written by Joao Alfredo Knopik Junior (JakJr) <joao.alfredo@gmail.com>          *
* ------------------------------------------------------------------------------- *
*  This program is free software; you can redistribute it and/or modify it        *
*  under the terms of the GNU General Public License as published by the	      *
*  Free Software Foundation; either version 2 of the License, or (at your option) * 
*  any later version.                                                             *
\**********************************************************************************/
// variavel global que salva o contato atual selecionado.
var actualSelectedContact = 0;
var setTimeOutLayer = 0;
var div_message_scroll = 0;
var tst_i = 0;
function search_contacts(key_pressed, fld_id)
{
	div_message_scroll = Element('div_message_scroll_'+ fld_id.substring(fld_id.length - 1, fld_id.length));

	var string_contacts = contacts
	if (Element(fld_id))
		var mail = Element(fld_id).value;
	else
		return;
	
	var array_contacts = string_contacts.split(",");
	var tmp = mail.split(",");
	mail = trim(tmp[tmp.length - 1]);
	tmp_mail = mail;
	
	seekDot = /\./gi;
	mail = mail.replace(seekDot, "[.]");
	mail = mail.replace('"', "&quot;");
	mail = mail.replace("\n", "");
	mail = mail.replace("\r", "");
	mail = mail.replace("\t", "");
	posX = findPosX(document.getElementById(fld_id));
	posY = findPosY(document.getElementById(fld_id));

	if ((!string_contacts) || (string_contacts.length==0) || (mail.length==0)){
		hideTip();
		return;
	}
		
	if (mail.length == 0){
		hideTip();
		return;
	}
	
	var RegExp_name = new RegExp(mail, "i");
	var RegExp_mail = new RegExp(mail+".*@", "i");

	var match_contacts = new Array();
	var match_index = 0;

	for (var i=0; i<array_contacts.length; i++){
		tmp = array_contacts[i].split(";");
		if ( (RegExp_name.test(tmp[0])) || (RegExp_mail.test(tmp[1])) ){
			if (tmp[1])
			{
				var _tmp1 = RegExp_mail.exec(tmp[1]);
				if ( _tmp1 )
					tmp[1] = tmp[1].replace(RegExp_mail, _tmp1[0].bold());
				var _tmp0 = RegExp_name.exec(tmp[0]);
				if ( _tmp0 )
					tmp[0] = tmp[0].replace(RegExp_name, _tmp0[0].bold());
				match_contacts[match_index] = '&quot;' + tmp[0] + '&quot; &lt;' + tmp[1] + '&gt;';
				match_index++;
			}
		}
	}

	if (match_contacts.length == 0){
		hideTip();
		return;
	}

	var table_contacts_header = "<table style='font-family:arial;font-size:12;color:#0000CF' border=0 cellpadding=0 cellspacing=0>";
	var table_contacts_foot = "</table>";
	var lines = '';
	var REG_EXP = /^[^\#|^\$|^\%|^\!|^\?|^\"|^\']+$/;
	var match_cont = "";
	var limit_index;
	
	match_contacts.length > 30? limit_index = 30 : limit_index = match_contacts.length; 
	
	for (var i=0; i<limit_index; i++)
	{
		match_contacts[i] = unescape(match_contacts[i]); 
		var aux = match_contacts[i].split("");
		for(var j in aux){
			if(REG_EXP.test(aux[j])){
				match_cont += aux[j];
			}else{
				match_cont += "";
			}
		}
		//lines = lines + "<tr><td id=td_DD_"+i+" onClick=\"javascript:hideTip();makeMailList('"+match_contacts[i]+"','"+fld_id+"');document.getElementById('" + fld_id + "').focus();\" onmouseover=\"selectContact("+i+")\">" + match_contacts[i] + "</td></tr>"
		lines = lines + "<tr><td id=td_DD_"+i+" onMouseDown=\"javascript:hideTip();makeMailList('"+match_cont+"','"+fld_id+"');setTimeout('document.getElementById(\\'"+fld_id+"\\').focus()',300);\" onmouseover=\"selectContact("+i+")\">" + match_cont + "</td></tr>"
		match_cont = "";
	}

    //Removido pois retirava o focus no ie	a cada letra digitada
	//if (document.getElementById('tipDiv')){ 
	//	document.getElementById('tipDiv').focus(); 
 	//} 

	// treat especials keys
	// key ENTER
	if ((key_pressed == 13) && (document.getElementById('tipDiv').style.visibility))
	{
		//Bug, sometimes the actualSelectedContact do not exist.
		try{
			makeMailList(document.getElementById('td_DD_' + actualSelectedContact).innerHTML,fld_id);
			hideTip();
		}
		catch(e){}
		return;
	}
	// key lostfocus
	if ((key_pressed == 'lostfocus') && (document.getElementById('tipDiv').style.visibility)){
		hideTip();
		return;
	}
	
	// key LEFT and RIGHT (when pressed, keeps the actual selection preventing lost of focus selection on pressing "up" or "down" keys) 
	if (((key_pressed == 37) && (document.getElementById('tipDiv').style.visibility)) || ((key_pressed == 39) && (document.getElementById('tipDiv').style.visibility))) 
	{ 
		selectContact(actualSelectedContact); 
		return; 
	} 

	// key DOWN
	if ((key_pressed == 40) && (document.getElementById('tipDiv').style.visibility)){
		if (actualSelectedContact != (match_contacts.length - 1)){
			selectContact(actualSelectedContact + 1);
		}
		return;
	}
	// key UP
	if ((key_pressed == 38) && (document.getElementById('tipDiv').style.visibility)){
		if (actualSelectedContact != 0){			
			selectContact(actualSelectedContact - 1);
			return;
		}
	}

	if (lines != ''){
		table_contacts = table_contacts_header + lines + table_contacts_foot
		doTooltip(posX, posY, table_contacts)
	}
	else
		hideTip();
		
	return true;
}

function makeMailList(mail,fld_id)
{
	list = Element(fld_id);
	for (var i = list.value.length; ((i!=0) && (list.value.substring(i-1,i)!=',')); i--){}
	mail = mail.replace(/&lt;/g,"<");
	mail = mail.replace(/&gt;/g,">");
	mail = mail.replace(/<[bB]>/g,"");
	mail = mail.replace(/<\/[bB]>/g,"");
	if (i == 0)
		list.value = list.value.substring(0,i) + mail + ', ';
	else
		list.value = list.value.substring(0,i) + ' ' + mail + ', ';
}

function selectContact(newContact){	
	//Desabilitar o atual
	var elAtual = document.getElementById('td_DD_' + actualSelectedContact);
	if(elAtual)
		elAtual.bgColor = "#efefef";	
		
	//Habilitar o novo.		
	var elNew = document.getElementById('td_DD_' + newContact).bgColor = "#c0e0ff";
	if(elNew)
		elNew.bgColor = "#c0e0ff";

	actualSelectedContact = newContact;
}

function doTooltip(x,y,msg) {
	if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
	Tooltip.show(x,y,msg);
}

function hideTip() {
	if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
	actualSelectedContact=0;
	Tooltip.hide();
}

function findPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

function trim(inputString) {
   if (typeof inputString != "string") 
   	return inputString;
      
   var retValue = inputString;
   var ch = retValue.substring(0, 1);
   while (ch == " ") { 
	  retValue = retValue.substring(1, retValue.length);
	  ch = retValue.substring(0, 1);
   }
   ch = retValue.substring(retValue.length-1, retValue.length);
   while (ch == " ") { 
	  retValue = retValue.substring(0, retValue.length-1);
	  ch = retValue.substring(retValue.length-1, retValue.length);
   }
   while (retValue.indexOf("  ") != -1) { 
	  retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length); 
   }
   return retValue; 
}

var Tooltip = {
    overlaySelects: true,  // iframe shim for select lists (ie win)
    offX: 0,
    offY: 50,
    tipID: "tipDiv",
    showDelay: 0,
    hideDelay: 0,
    
    ovTimer: 0, // for overlaySelects
    ready:false, timer:null, tip:null, shim:null, supportsOverlay:false,
  
    init: function() {
            var el_dropdowncontact = document.createElement("DIV");
			el_dropdowncontact.id = this.tipID;
			document.body.appendChild(el_dropdowncontact);
            this.supportsOverlay = this.checkOverlaySupport();
            this.ready = true;
    },
    
    show: function(x, y, msg) {
        this.tip = document.getElementById( this.tipID );
        this.writeTip(msg);
        
        this.positionTipStatic(x,y);
   		this.handleOverlay(1, this.showDelay, x+this.offX,y+this.offY);
		document.getElementById('td_DD_0').bgColor = "#c0e0ff";
      	this.timer = setTimeout("Tooltip.toggleVis('" + this.tipID + "', 'visible')", this.showDelay);
    },
    
    writeTip: function(msg) {
        if ( this.tip && typeof this.tip.innerHTML != "undefined" ) this.tip.innerHTML = msg;
        this.tip.style.width = 'auto';
    },
    
    positionTipStatic: function(x,y) {
        if ( this.tip && this.tip.style ) {
			this.tip.style.left = x + this.offX + "px";
			this.tip.style.top = y + this.offY + "px";
        }
     },

	scrollChanged: function() {
		Element('tipDiv').style.visibility = 'hidden';
		Tooltip.hide();
	},

    hide: function() {
        if (this.timer) { clearTimeout(this.timer);	this.timer = 0; }
		this.handleOverlay(0, this.hideDelay);
        this.timer = setTimeout("Tooltip.toggleVis('" + this.tipID + "', 'hidden')", this.hideDelay);
        this.tip = null; 
    },
    
  toggleVis: function(id, vis) { // to check for el_dropdowncontact, prevent (rare) error
      var el_dropdowncontact = document.getElementById(id);
      if (el_dropdowncontact) el_dropdowncontact.style.visibility = vis;
  },

	// check need for and support of iframe shim
	checkOverlaySupport: function() {
		return (is_ie);
	}, 
    
	handleOverlay: function(bVis, d, x ,y) {
		var _scrollY = div_message_scroll ? div_message_scroll.scrollTop : 0;
        if(_scrollY > 0 && this.tip) {
	        this.tip.style.top = (findPosY(this.tip) - _scrollY)+ "px";
	    }
		if ( this.overlaySelects && this.supportsOverlay ) {
			if (this.ovTimer) { clearTimeout(this.ovTimer); this.ovTimer = 0; }
			switch (bVis) {
			case 1 :
				if ( !document.getElementById('tipShim') ) 
					document.body.insertAdjacentHTML("beforeEnd", '<iframe id="tipShim" src="about:blank" style="position:absolute; left:' + x + '; top:' + y + '; z-index:500; visibility:hidden" scrolling="no" frameborder="0"></iframe>');
					this.shim = document.getElementById('tipShim'); 
					if (this.shim && this.tip) {
						this.shim.style.width = this.tip.offsetWidth + "px";
                        this.shim.style.height = this.tip.offsetHeight + "px";
                        this.shim.style.top = findPosY(this.tip)+ "px";
					}
					this.ovTimer = setTimeout("Tooltip.toggleVis('tipShim', 'visible')", d);
				break;
			case 0 :
				this.ovTimer = setTimeout("Tooltip.toggleVis('tipShim', 'hidden')", d);
				if (this.shim) this.shim = null;
				break;
			}
		}
	}
}
Tooltip.init();
// Criar um estilo no html (tpl) com os seguintes parametros:
//div#tipDiv {
//  position:absolute; visibility:hidden; left:0; top:0; z-index:10000;
//  background-color:#EFEFEF; border:1px solid #337;
//  width:220px; padding:3px;
//  color:#000; font-size:11px; line-height:1.2;
//  cursor: default;
