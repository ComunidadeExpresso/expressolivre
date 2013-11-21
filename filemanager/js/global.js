var title_app = '<table height="16px" border=0 width=100% cellspacing=0 cellpadding=2>'+
'<tr class="divAppboxHeader">'+
'<td style="padding-left:17px" width=33% id="content_quota" align=left></td>'+
'<td width=33% id="main_title">Expresso FileManager</td>'+
'<td width=33% id="div_menu_c3" align=right></td>'+
'</tr></table>';

var currentPath = "";
var oldValue;

var templatePath = './filemanager/templates/default/';

var menuTimeout;
var DocX,DocY;
var criteria='name';
var order_type = '1'; // Ascending is 1, descending is 0
var crypt; // Used to send encrypted stuff

// Store permissions of current path use it BUT DO NOT rely on it
var permissions = new Array();
var preferences = new Array();
var folders = new Array();

var KEY_ENTER = 13;

var denyFileExtensions = new Array('exe','com','reg','chm','cnf','hta','ins',
                                        'jse','job','lnk','pif','src','scf','sct','shb',
                                        'vbe','vbs','wsc','wsf','wsh','cer','its','mau',
                                        'mda','mar','mdz','prf','pst');

var last_folder = currentPath;
var current_folder = currentPath;
var current_page = current_page ? current_page : 1;
var lastPage = lastPage ? lastPage : 1;
var numPages = numPages ? numPages : 5;

