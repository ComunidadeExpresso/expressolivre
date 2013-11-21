function modal(id)
{
	try{
		//content = Element(id).innerHTML;
		content = Element(id).cloneNode(true);
//		title   = Element(id + '_title').value;
		width   = 323;
		height  = 200;
//		close_action = Element(id + '_close_action').value;
//		create_action = Element(id + '_create_action').value;
//		save_action = Element(id + '_save_action').value;
//		onload_action = Element(id + '_onload_action').value
	}
	catch(e){
		alert(e);
	}
	var objBody = document.getElementsByTagName("body").item(0);
	
	/* the Overlay */ // create overlay div and hardcode some functional styles
	var objOverlay = document.createElement("div");
	objOverlay.setAttribute('id','overlay');
	objOverlay.style.position = 'absolute';
	objOverlay.style.top = '0';
	objOverlay.style.left = '0';
	objOverlay.style.zIndex = '90';
 	objOverlay.style.width = '100%';
	
	var arrayPageSize = getPageSize();
	var arrayPageScroll = getPageScroll();

	// set height of Overlay to take up whole page and insert. Show at the end of this function.
	objOverlay.style.height = (arrayPageSize[1] + 'px');
	objOverlay.style.display = 'none';
	objBody.insertBefore(objOverlay, objBody.firstChild);


	/* the div */ // create lightbox div, same note about styles as above
	
	var objLightbox = document.createElement("div");
	objLightbox.setAttribute('id','lightbox');
	
	objLightbox.style.position = 'absolute';
	objLightbox.style.display = 'none';
	objLightbox.style.zIndex = '100';	

	
	var objLightbox_height = height;
	var objLightbox_width = width;
	
	objLightbox.style.height = objLightbox_height + "px";
	objLightbox.style.width = objLightbox_width + "px";
	
	var lightboxTop = ((arrayPageSize[3] - objLightbox_height) / 2);
	var lightboxLeft = ((arrayPageSize[0] - objLightbox_width) / 2 );
	
	objLightbox.style.top = (lightboxTop < 0) ? "0px" : lightboxTop + "px";
	objLightbox.style.left = (lightboxLeft < 0) ? "0px" : lightboxLeft + "px";
	
	objBody.insertBefore(objLightbox, objOverlay.nextSibling);
		
		
	// create caption
	/*var objCaption = document.createElement("div");
	objCaption.setAttribute('id','lightboxCaption');
	objCaption.innerHTML = title;
	objLightbox.appendChild(objCaption);*/

	// create warnings
	var objWarning = document.createElement("div");
	objWarning.setAttribute('id','lightboxWarning');
	objWarning.style.height = '21px';
	objLightbox.appendChild(objWarning);

	// Create Content
	var objContent = document.createElement("div");
	objContent.setAttribute('id','lightboxContent');
	objContent.style.height = (objLightbox_height - 65) + "px";
	objContent.innerHTML = content.innerHTML;
	objLightbox.appendChild(objContent);

	//Show the modal.
	objOverlay.style.display = 'block';
	objLightbox.style.display = '';
}

function close_lightbox()
{
	var objBody = document.getElementsByTagName("body").item(0);
	var lightbox = document.getElementById('overlay');
	lightbox_div = document.getElementById('lightbox');
	objBody.removeChild(lightbox);
	objBody.removeChild(lightbox_div);
	return;
}

function getPageSize(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}
	
	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}

function getPageScroll(){

	var yScroll;

	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll) 
	return arrayPageScroll;
}

function Element(id)
{
	return document.getElementById(id);
}

function add_css(){
	var headID = document.getElementsByTagName("head")[0];         
	var cssNode = document.createElement('link');
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	if (navigator.userAgent.toLowerCase().indexOf("msie") != -1)
		cssNode.href = "./js/modal/css/modal_ie.css";
	else
		cssNode.href = "./js/modal/css/modal_fx.css";
	
	cssNode.media = 'screen';
	headID.appendChild(cssNode);
	return;
	
}

/*function load_lang(){
	cExecute ('$this/js/modal/inc/load_lang', handler_load_lang);
}*/

var global_langs = new Array();
function handler_load_lang(data)
{
	global_langs = eval(data);
}

function get_lang(key_raw)
{
	key = key_raw.replace(/ /g,"_");

	try{
		lang = eval("global_langs."+key.toLowerCase());
		return lang;
	}
	catch(e){
		return key_raw + '*';
	}
}

function make_msg(msg, type)
{
	var html_msg = 
	'<table cellspacing="0" cellpadding="0" border="0"><tbody>'+
	'<tr><td class="zIVQRb_'+type+'"/><td class="Ptde9b_'+type+'"/><td class="Qrjz3e_'+type+'"/></tr>'+
	'<tr><td class="Ptde9b_'+type+'"/><td class="m14Grb_'+type+'">' + msg + '</td><td class="Ptde9b_'+type+'"/></tr>' +
	'<tr><td class="Gbtri_'+type+'"/><td class="Ptde9b_'+type+'"/><td class="gmNpMd_'+type+'"/></tr></tbody></table>';
	return html_msg;
}

function write_msg(msg, type, keepAlive)
{
	try
	{
		clearTimeout(setTimeout_write_msg);
	}
	catch(e){}
		
	var objLightbox = Element("lightbox");
	if (objLightbox != null)
	{
		var old_divStatusBar = Element("lightboxWarning");
		var bgColor = "#EEEEEE";
	}
	else
	{
		var old_divStatusBar = Element("divStatusBar");
		var bgColor = "#f7f8fa";
	}
	
	var msg_div = Element('em_div_write_msg');
	if(!msg_div) {
		msg_div = document.createElement('DIV');
		msg_div.id = 'em_div_write_msg';
		//msg_div.style.height = '2px';
		msg_div.style.background = bgColor;
		msg_div.style.display = 'none';
		msg_div.align = "center";
		msg_div.valign = "middle";
	}
	
	msg_div.innerHTML = make_msg(msg, type);
	
	clean_msg();
	old_divStatusBar.parentNode.insertBefore(msg_div,old_divStatusBar);
	
	old_divStatusBar.style.display = 'none';
	msg_div.style.display = '';
	
	if( !keepAlive )
		setTimeout_write_msg = setTimeout("clean_msg();", 4000);
}

function clean_msg()
{
	var msg_div = Element('em_div_write_msg');
	if ( (msg_div) && (msg_div.style.display != 'none') )
	{
		// Msg esta em outra tela. Precisa apaga-la.
		/*
		if ( (Element("lightbox") != null) && (msg_div.nextSibling.id == 'lightboxWarning') ) {}
		else if ( (Element("lightbox") == null) && (msg_div.nextSibling.id == 'divStatusBar') ) {}
		else
		{*/
			msg_div.style.display = 'none';
			msg_div.nextSibling.style.display = '';
//		}
	}
}

if (document.all)
{
	navigator.userAgent.toLowerCase().indexOf('msie 5') != -1 ? is_ie5 = true : is_ie5 = false;
	is_ie = true;
}

add_css();