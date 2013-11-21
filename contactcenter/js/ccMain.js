var last_id = 0;	
var preferences = null;
function openwindow(url){ 	
	var window_features = 	"scrollbars=yes,resizable=yes,location=no,menubar=no," + 
						"personalbar=no,status=no,titlebar=no,toolbar=no," + 
						"screenX=0,screenY=0,top=0,left=0,width=" + 
						screen.width + ",height=" + screen.height/5*3; 
			
	window.open(url,'', window_features); 
}

var Main_pre_load = document.body.onload;
var ccSearch, ccTree;
var Main_load = function () 
	{
		Connector.setProgressBox(Element('cc_loading'), true);
		Connector.setProgressHolder(Element('cc_loading_inner'));		
		/* Associate the Quick Add Button with the Plugin */
					

		/* Create the Search Object */
		var search_params = new Array();
		search_params['holder'] = Element('cc_panel_search_call');
		search_params['total_width'] = (v_label != false &  v_atrib != false)? '485px' : '335px';
		search_params['input_width'] = '200px';
		search_params['progress_top'] = '150px';
		search_params['progress_left'] = '-260px';
		search_params['progress_color'] = '#3978d6';
		search_params['progress_width'] = '250px';
		search_params['conn_1_msg'] = Element('cc_loading_1').value;
		search_params['conn_2_msg'] = Element('cc_loading_2').value;
		search_params['conn_3_msg'] = Element('cc_loading_3').value;
		search_params['button_text'] = Element('cc_panel_search_text').value;
		search_params['Connector'] = Connector;

		ccSearch = new ccSearchClass(search_params);
		ccSearch.DOMresult.style.visibility = 'hidden';
		ccSearch.onSearchFinish = ccSearchUpdate;
			
		Connector.setProgressBox(Element('cc_loading'), true);
		Connector.setProgressHolder(Element('cc_loading_inner'));

		try
		{
			function handlerInitValues(sdata)
			{
				var data = unserialize(sdata);
				preferences = data.preferences;
				if( boolData = eval(data.visible_all_ldap) )
				{
					ccTree = new ccCatalogTree({name: 'ccTree', id_destination: 'cc_tree', afterSetCatalog: 'ccSearchHidePanel(); updateCards()'});
					showCards('all',getActualPage());
					selectLetter('27');
				}	
				else
				{
					ccTree = new ccCatalogTree({name: 'ccTree', id_destination: 'cc_tree', afterSetCatalog: 'ccSearchHidePanel(); clearCards();'});
					var ccQtdCompartilhado = function(responseText) {					
														data = unserialize(responseText);
														qtd_compartilhado = data[0];
													}
					Connector.newRequest('fulfilQtdCompartilhado', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_qtds_compartilhado', 'POST', ccQtdCompartilhado);
				}
			}

			Connector.newRequest('handlerInitValues', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_init_values', 'GET', handlerInitValues);		
			
			ccTree.Connector = Connector;						
		}
		catch(e){}

		/* Create the Tree Object */			
		//ccTree = new ccCatalogTree({name: 'ccTree', id_destination: 'cc_tree', afterSetCatalog: 'ccSearchHidePanel(); updateCards()'});
		//ccTree.Connector = Connector;						
	}
	var menuStarted = false;
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
	var _timeout = '';
	var menu = function () {
		 
	 if(! this.menuStarted)
		this.menuStarted = true;
		
		submenu = [];
		textmenu = [];
			
		textmenu[0] = ["cc_msg_contact_qa","cc_msg_contact_full","cc_msg_contact_sh","cc_msg_group"];
		textmenu[1] = ["cc_quick_add", "cc_full_add_button", "cc_full_add_button_sh", "cc_add_group_button"];
		function show(){
			clearTimeout(_timeout);
			button = document.getElementById("cc_button_new");
				
			this.style.top = 19 + findPosY(button) + "px"; 
			this.style.visibility='visible'; 
		}
		function hide(){ _timeout = setTimeout("menu.style.visibility='hidden';",200); };
			
		if(document.getElementById) {
			menu = document.getElementById("Layer1");
				
			for (i=0; i< textmenu[0].length; i++) {
				textmenu[0][i] = "<span onclick= 'menu.onmouseout();'>" + document.getElementById(textmenu[0][i]).value + "</span><br>";
				submenu[i] = document.createElement("DIV");				
				submenu[i].innerHTML = textmenu[0][i];
				submenu[i].id = textmenu[1][i];
				submenu[i].onmouseover = function () {this.style.backgroundColor = 'LIGHTYELLOW';this.style.color = 'DARKBLUE';};
				submenu[i].onmouseout   = function () {  this.style.backgroundColor = '#DCDCDC'; this.style.color = '#006699';};					
				submenu[i].setAttribute("className", "special");
				submenu[i].setAttribute("class", "special");
				submenu[i].style.padding = "5px";					  
				menu.appendChild(submenu[i]);
			}			
				
			menu.onmouseover = show;
			menu.onmouseout = hide;
		}
	 		
		ccQuickAdd.associateAsButton(Element('cc_quick_add'));
		ccAddGroup.associateAsButton(Element('cc_add_group_button'));
		Element("cc_full_add_button").onclick = newContact;
		Element("cc_full_add_button_sh").onclick = newSharedContact;
			 
			
		ccQuickAdd.afterSave = function ()
		{
			updateCards();
		}			
			
		ccAddGroup.load = function () 
		{				
			editGroup();			
		}
			
		ccAddGroup.afterSave = function ()
		{
			updateCards();
		}
			
		return true;
	}

	if (is_ie)
	{
			
		document.body.onload = function (e)
		{ 			
			Main_pre_load();								
			Main_load();					
								
		}
	}
	else
	{
                
		Main_load();	
			
	}	

// BEGIN: FUNCTION RESIZE WINDOW
var _showBar = showBar;
var _hideBar = hideBar;

function __showBar(){
	_showBar();
	resizeWindow();
}

function __hideBar(){
	_hideBar();
	resizeWindow();
}
showBar = __showBar;
hideBar = __hideBar;
	
var _onResize	= window.onresize;
window.onresize = resizeWindow;
var defaultHeight = 0;
function setDefaultHeight(){
	
	var bar = Element("toolbar");
	var offset = 0;
	if(bar.style.visibility != 'hidden') 
		offset = (bar.offsetHeight ? bar.offsetHeight :  bar.clientHeight);	

	var screenHeight = document.body.clientHeight ? document.body.clientHeight : document.body.offsetHeight;
	defaultHeight = screenHeight - offset;	
	Element("cc_tree").style.height 		= defaultHeight - 68;	
	Element("cc_left_main").style.height 	= defaultHeight - 68;	
}

function resizeWindow(){
	setDefaultHeight();
	if(Element("divScrollMain"))
		Element("divScrollMain").style.height 	= defaultHeight - 108;	
	if (!is_ie)
		Element('tableDivAppbox').width = '100%';
}
document.body.scroll="no";
document.body.style.overflow ="hidden";
setDefaultHeight();
Element('cc_main').style.height = defaultHeight;
var lang_warn_firefox = Element('cc_msg_warn_firefox');
var lang_firefox_msg1 = Element('cc_msg_firefox_half1');
var lang_firefox_msg2 = Element('cc_msg_firefox_half2');
var lang_install_now = Element('cc_msg_install_now');
var lang_install_new_firefox = Element('cc_msg_install_new_firefox');
var lang_close = Element('cc_msg_close');
function buildWarningMsg(_version) {
	var screenWidth = document.body.clientWidth ? document.body.clientWidth: document.body.offsetWidth;
	var _div = document.createElement("DIV");
	_div.innerHTML += "<DIV id='warning_msg' style='background:LIGHTYELLOW;position:absolute;"+
	"border:1px solid black;left:"+(screenWidth - 330)+";top:10px;width:300px;padding:10px;"+
	(document.body.clientWidth ? "-moz-border-radius: 9px 9px 9px 9px;'>" : "")+
	    "<font color='RED' size='2'>"+lang_warn_firefox +  "("+_version+")</font><BR>"+
	    "<font color='black' size='2'><p style='text-align:justify'>&nbsp;"+lang_firefox_msg1+
	    lang_firefox_msg2 + ".</p></font><div style='width:100%' align='center'>"+
	    "<a title='"+lang_install_now+"' href='http://br.mozdev.org/firefox/download.html' target='_blank'>"+lang_install_new_firefox+ "</a>"+	
	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
	    "<a title='"+lang_close+"' href='javascript:void(0)' onclick='javascript:myOpacity.toggle()'>"+lang_close+"</a></div>"+

	"</DIV>";

	document.body.appendChild(_div);

	myOpacity = new fx.Opacity('warning_msg', {duration: 600});
	document.getElementById("warning_msg").style.visibility = 'hidden';		
	myOpacity.now = 0;
	setTimeout("myOpacity.toggle()",3000);
}
// Verifica versão do Firefox
var agt=navigator.userAgent.toLowerCase();
if(agt.indexOf('firefox/1.0') != -1 && agt.indexOf('firefox/0.'))
	buildWarningMsg(agt.substring(agt.indexOf('firefox/')+8,agt.indexOf('firefox/')+13));
