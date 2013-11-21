var debug = false;
var _bTime = new Date();
_bTime = _bTime.getTime();
var hold_session = false;
var queue_send_errors = false;
var current_page = 1;
var current_folder = '';
var id_menu_folders = '';
var preferences = '';
var contacts = '';
var search_box_type = 'ALL';
var sort_box_type = 'SORTARRIVAL';
var sort_box_reverse = 1;
var last_message_selected = '';
var array_lang = new Array();
var cyrus_delimiter = '';
var ccaddress_array = new Array();
var ccoaddress_array = new Array();
var toaddress_array = new Array();
var tree_folders = '';
var inc_abas_search = 0;
var folders = new Array();
//var global_search = 1; // use numBox instead of this!
var title_app_menu = '<table height="16px" align=center border=0 width=100% cellspacing=0 cellpadding=2>'+
	'<tr><td align=left height=16px width="1%" nowrap class="table_top">&nbsp;'+
	'<a href=# onclick="javascript:new_message(\'new\',\'null\')" align=left>'+
	'<img src="templates/'+template+'/images/menu/createmail.gif">'+
	'&nbsp;Novo</a>&nbsp;&nbsp;'+
	'<a href="#" onclick="javascript:wfolders.makeWindow(\'\', \'change_folder\')" align=left>'+
	'<img src="templates/'+template+'/images/menu/editfolders.png">'+
	'&nbsp;Trocar Pasta</a>&nbsp;&nbsp'+	
	'<a href="#" onclick="javascript:refresh();" align=left>'+
	'<img src="templates/'+template+'/images/menu/checkmail.gif">'+
	'&nbsp;Atualizar</a>&nbsp;&nbsp;'+
	'<a id="link_tools" href="#" align=left>'+
	'<img height="16px" src="templates/'+template+'/images/menu/tools.gif">'+
	'&nbsp;Ferramentas...</a>&nbsp;&nbsp;</td><td style="padding-left:17px" width="1%" id="content_quota" align=left nowrap></td><td class="divAppboxHeader" id="main_title">Expresso Mail</td><td width=* id="div_menu_c3" align="right"></td></tr></table>';

var title_app = '<table height="16px" border=0 width=100% cellspacing=0 cellpadding=2>'+
	'<tr>'+
	'<td style="padding-left:17px" width=33% id="content_quota" align=left></td>'+
	'<td class="divAppboxHeader" width=33% id="main_title">Expresso Mail</td>'+
	'<td width=33% id="div_menu_c3" align=right></td>'+
	'</tr></table>';

var divStatusBar = document.getElementById("divStatusBar");
var denyFileExtensions = new Array('exe','com','reg','chm','cnf','hta','ins','jse','job','lnk','pif','src','scf','sct','shb','vbe','vbs','wsc','wsf','wsh','cer','its','mau','','mda','mar','mdz','prf','pst');
var mobile_device = false;
var previous = 0;
