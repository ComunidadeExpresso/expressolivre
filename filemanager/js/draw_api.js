
var offset;
var showHidden 	= null;
var Xtools		= null; 
var zIndex		= 1000;

function getPointer(e) {
	if (is_ie) {
		DocX = event.clientX + document.body.scrollLeft;
		DocY = event.clientY + document.body.scrollTop;
	} else {
		DocX = e.pageX;
		DocY = e.pageY;
	}
}

function loadHistory(filename)
{
	var handlerLoadHistory = function(data)
	{
		loadXtools();

		var response	=  unserialize(data);
		var _xml		= Xtools.xml('file');
		var _historyFile	= _xml.documentElement; 

		if( typeof(response)  == "object" )
		{	
			for( var i = 0; i < response.length; i++ )
			{
				var _info = _historyFile.appendChild(  _xml.createElement("info") );

				var	_created = _xml.createElement('created'); 
				_created.appendChild( _xml.createTextNode(response[i]['created']) );
				_info.appendChild( _created );

				var	_version = _xml.createElement('version');
				_version.appendChild( _xml.createTextNode(response[i]['version']) );
				_info.appendChild(_version);

				var	_who = _xml.createElement('who');
				_who.appendChild( _xml.createTextNode(response[i]['who']) );
				_info.appendChild( _who );

				var _operation	= _xml.createElement('operation');
				_operation.appendChild( _xml.createTextNode( response[i]['operation']) );
				_info.appendChild( _operation );
			}

			var pArgs = 
			{
				'lang_created'		: get_lang("Created"),
				'lang_operation'	: get_lang("Operation"),
				'lang_version'		: get_lang("Version"),
				'lang_who'			: get_lang("Who"),
				'lang_history'		: get_lang("File history"),
				'height'			: 300,
				'path_filemanager'	: path_filemanager,
				'width'				: 450			
			};

			var code = Xtools.parse( _historyFile, "historyFile.xsl", pArgs );

			draw_window( code , 450, 300 );
		}
		else
			alert( data );
	};
	
	cExecute_('./index.php?menuaction=filemanager.uifilemanager.history&file='
		+ base64_encode(filename)+"&path="+base64_encode(currentPath), handlerLoadHistory );
}

function loadXtools()
{
	if( Xtools == null )
		Xtools = new xtools( path_filemanager + "tp/expressowindow/" );
}

function loadPermissions(data)
{
	var permission = parseInt(data);
	var ACL_READ = 1;
	var ACL_ADD = 2;
	var ACL_EDIT = 4;
	var ACL_DELETE = 8;
	var ACL_PRIVATE = 16;

	permissions['read']		= (permission & ACL_READ);
	permissions['add']		= (permission & ACL_ADD);
	permissions['edit']		= (permission & ACL_EDIT);
	permissions['delete']	= (permission & ACL_DELETE);
	permissions['private']	= (permission & ACL_PRIVATE);
	toolbar.load();
}

function close_window()
{
	_winBuild( 'dwindow' + ( zIndex-1 ) , "remove" );
}

function draw_window( )
{
	if( arguments.length > 0 )
	{
		var htmlData	= arguments[0];
		var sizeW		= ( arguments[1] ) ? arguments[1] : 420 ;
		var sizeH		= ( arguments[2] ) ? arguments[2] : 200 ;
		var titleAction	= ( arguments[3] ) ? " - " + arguments[3] : "";
		var id_window	= ( arguments[4] ) ? "dwindow" + arguments[4] : "dwindow" + zIndex;
		
		var _janela = 
		{
			id_window       : id_window,
			width           : sizeW,
			height          : sizeH,
			top             : 200,
			left            : ( ( screen.width / 2 ) - ( sizeW / 2) ),
			draggable       : true,
			visible         : "display",
			resizable       : true,
			zindex          : ++zIndex,
			title           : "Expresso - " + get_lang("filemanager") + titleAction,
			closeAction 	: "remove",
			content         : htmlData
		};
	
		_winBuild( _janela );
	}
}

function draw_menu()
{
	var _options	= arguments[0];
	var _parent		= arguments[1];
	var dt			= new Date();	
	
	if( showHidden == null )
		showHidden = new ShowHidden( 200 );

	var _itens = "";
		
	for( var i in _options )
	{
		if( _options[i].constructor == Function )
			continue;

		_itens += '<img src="' + _options[i][2] + '" '+ _options[i][3] +'/>';
		_itens += '<a href='+_options[i][1]+' style="cursor:pointer; margin:3px;">' + _options[i][0] + '</a><br/>'
	}

	var _optionsItens 			= document.createElement("div");
	_optionsItens.id			= "fastMenuFileManager";
	_optionsItens.className		= "x-menu";
	_optionsItens.style.zIndex	= zIndex++;
	_optionsItens.innerHTML		= _itens;
	_optionsItens.onclick		= function(){ showHidden.hiddenObject(false); };
	_optionsItens.onmouseout	= function(){ showHidden.hiddenObject(false); };
	_optionsItens.onmouseover	= function(){ showHidden.hiddenObject(true); };	
							  
	showHidden.action('onmouseover', 'onmouseout', _optionsItens);

	_parent.parentNode.appendChild( _optionsItens );
}

function resizeDiv(){
	defaultHeight = document.body.clientHeight ? document.body.clientHeight : document.body.offsetHeight;
	document.getElementById('fmFileWindow').style.height = defaultHeight-170;
	document.getElementById('content_folders').style.height = defaultHeight - (is_ie ? 230 : 215);

}
var headerMsgLock = false;
var labelBefore = "";
function write_msg (message){
	connector.hideProgressBar();
	if (headerMsgLock){
		setTimeout('write_msg("'+message+'");', 300);
		return;
	}
	headerMsgLock = true;
	headerDiv = document.getElementById("main_title");
	labelBefore = headerDiv.innerHTML;
	headerDiv.innerHTML = '<table width=100% cellspacing="0" cellpadding="0" border="0"><tbody><tr><th width="40%"></th><th noWrap class="action_info_th">'+message+'</th><th width="40%"></th></tr></tbody></table>';
	setTimeout('headerMsgLock = false; document.getElementById("main_title").innerHTML = labelBefore;', 3000);
}

function write_error (message){
	connector.hideProgressBar();
	if (headerMsgLock){
		setTimeout('write_error("'+message+'");', 300);
		return;
	}
	headerMsgLock = true;
	headerDiv = document.getElementById("main_title");
	labelBefore = headerDiv.innerHTML;
	headerDiv.innerHTML = '<table width=100% cellspacing="0" cellpadding="0" border="0"><tbody><tr><th width="40%"></th><th noWrap class="action_error_th">'+message+'</th><th width="40%"></th></tr></tbody></table>';
	setTimeout('headerMsgLock = false; document.getElementById("main_title").innerHTML = labelBefore;', 3000);
}

function displayMessages(){
	var messages = document.getElementById("allMessages");

	for (i=0; i < messages.childNodes.length; i++){
		if (messages.childNodes[i].innerHTML.indexOf(get_lang('Error:')) == 0)
			write_error(messages.childNodes[i].innerHTML);
		else
			write_msg(messages.childNodes[i].innerHTML);
	}
}

function loadPreferences(){
	preferencesEl = document.getElementById('userPreferences');
	preferences = unserialize(preferencesEl.value);
	preferencesEl.parentNode.removeChild(preferencesEl);
}
function reloadFiles(newCriteria){
	if (newCriteria == criteria)
		order_type = (order_type=='1'?'0':'1')
	else
		criteria = newCriteria;
	toolbar.control('reload');
}

function initDrawApi()
{
	var SecEl = document.getElementById('userKey');
	crypt = new crypt(SecEl.value);
	
	SecEl.parentNode.removeChild(SecEl);

	loadPreferences();
	preferences.files_per_page = (preferences.files_per_page != undefined) ? preferences.files_per_page : 10;
	offset = (current_page-1)*preferences.files_per_page;

	currentPath = document.getElementById('currentPath').value;
	document.getElementById('divAppboxHeader').innerHTML = title_app;
	displayMessages();
	resizeDiv();
	window.onresize = resizeDiv;
	document.body.style.overflow = "hidden";
	cExecute_('./index.php?menuaction=filemanager.uifilemanager.get_folders_list', handler.draw_folders_list );
}

function folderList()
{
	this.td = '<td style="padding-left: 2px; padding-right: 2px;" valign="middle">';
}

folderList.prototype.init = function()
{
	this.element = document.getElementById('fmFileWindow');
}

folderList.prototype.clear = function()
{
	this.element.innerHTML = "";
}

folderList.prototype.drawSearch = function(data)
{
	var fl = folderList;
	var files = unserialize(data);
	if (files == null)
	{
		alert(get_lang('No results found'));
		return
	}
	toolbar.clear();
	fl.init();
	fl.clear();
	var newpage;
	var sp_hd = '<span name="head" style="cursor: pointer; cursor: hand;"';
	var sp_tl = '</span></td>';
	newpage = '<table cellspacing="0" cellpadding="2" width="100%"><tbody><tr class="message_header">';
	newpage += fl.td+'<input onclick="selectAll(this)" type="checkbox"></td><td></td><td></td>';
	newpage += fl.td+sp_hd+' id="name">'+get_lang('file name')+sp_tl;
	newpage += fl.td+sp_hd+' id="folder">'+get_lang('folder')+sp_tl;
	if (preferences.mime_type =='1')
		newpage += fl.td+sp_hd+' id="mime_type">'+get_lang('mime type')+sp_tl;
	if (preferences.size =='1')
		newpage += fl.td+sp_hd+' id="size">'+get_lang('size')+'</a>'+sp_tl;
	if (preferences.created =='1')
		newpage += fl.td+sp_hd+' id="created">'+get_lang('created')+'</a>'+sp_tl;
	if (preferences.modified =='1')
		newpage += fl.td+sp_hd+' id="modified">'+get_lang('modified')+'</a>'+sp_tl;
	if (preferences.createdby_id =='1')
		newpage += fl.td+sp_hd+' id="createdby_id">'+get_lang('created by')+'</a>'+sp_tl;
	if (preferences.modifiedby_id =='1')
		newpage += fl.td+sp_hd+' id="modifiedby_id">'+get_lang('modified by')+'</a>'+sp_tl;
	if (preferences.comment =='1')
		newpage += fl.td+sp_hd+' id="comment">'+get_lang('comment')+'</a>'+sp_tl;
	if (preferences.version =='1') newpage += fl.td+sp_hd+' id="version">'+get_lang('version')+'</a>'+sp_tl+'</tr>';

	for (var i=0; i < files.length; i++)
	{
		newpage += '<tr>'+fl.td;
		newpage += '<input name="fileman" value="'+files[i].name+'" type="checkbox"></td>';
		newpage += '<td></td><td></td>';
		newpage += fl.td+'<img src="'+files[i].icon+'">';
		newpage += '<a id="name_'+files[i].name+'" href="./index.php?menuaction=filemanager.uifilemanager.view&file='+base64_encode(files[i].name)+'&path='+base64_encode(files[i].directory)+'" target="_blank">'+files[i].name+'</a>&nbsp;</td>';
		newpage += fl.td+files[i].directory+'</td>';
		if (preferences.mime_type =='1') newpage += fl.td+files[i].mime_type+'</td>';
		if (preferences.size =='1') newpage += fl.td+borkb(files[i].size)+'</td>';
		if (preferences.created =='1') newpage += fl.td+files[i].created+'</td>';
		if (preferences.modified =='1') newpage += fl.td+files[i].modified+'</td>';
		if (preferences.createdby_id =='1') newpage += fl.td+files[i].createdby_id+'</td>';
		if (preferences.modifiedby_id =='1') newpage += fl.td+files[i].modifiedby_id+'</td>';
		if (preferences.comment =='1') newpage += fl.td+'<span id="'+files[i].name+'">'+files[i].comment+'</span></td>';
		if (preferences.version =='1') newpage += fl.td+'<span>'+files[i].version+'</span></td></tr>';

	}
	fl.element.innerHTML = newpage;
	fl.drawStripes();
}
folderList.prototype.createLine = function(file){
	var fl = folderList;
	//retBuff = '<tr id="line_'+file.name+'" onmouseout="clearTimeout(menuTimeout)" onmousedown="_dragArea.dragEl=this;_dragArea.operation=\'drag\'">';
	retBuff = '<tr id="line_'+file.name+'">';
	retBuff += fl.td;
	retBuff += '<input name="fileman" value="'+file.name+'" type="checkbox"></td>';
	if (permissions['private']) {
		retBuff += '<td><div id="restrict_'+file.name+'" onclick="setRestricted(\''+file.name+'\')" ';
		retBuff += 'style="background-image:url('+templatePath+'images/button_'+(file.pub == '1'?'lock':'unlock')+'.png);background-repeat: repeat-none;width:15px;height:12px;"></div></td>';
	}
	else
		retBuff += '<td></td>';

	switch (file.mime_type)
	{
		case 'text/html':
			if( file.size > 0 )
				retBuff += '<td><a href="./index.php?menuaction=filemanager.uifilemanager.export&file='+base64_encode(file.name)+'&path='+base64_encode(currentPath)+'"><div class="exportButton" alt="'+get_lang('export')+'" title="'+get_lang('export')+'"></div></a></td>';
			else
				retBuff += '<td><a href="javascript:void();"></a></td>';
			break;
		case 'application/zip':
			retBuff += '<td><div class="exportButton" onclick="unarchive(\''+file.name+'\')" alt="'+get_lang('unarchive')+'" title="'+get_lang('unarchive')+'"></a></td>'
			break;
		default:
			retBuff += '<td></td>';
	}
	retBuff += fl.td+'<div style="background-image:url('+(file.icon)+'); background-repeat: no-repeat; height:16px; padding-left: 18px; overflow: hidden;">';
	if((file.mime_type).toUpperCase().indexOf('IMAGE') == 0)
		var mousefunc = 'draw_card(\'preview\',\''+file.name+'\')'
	else
		var mousefunc = 'hide_card()';
	retBuff += '<span class="fileLink" onmouseover="'+mousefunc+'" id="name_'+file.name+'" onclick="window.open(\'./index.php?menuaction=filemanager.uifilemanager.view&file='+base64_encode(file.name)+'&path='+base64_encode(currentPath)+'\');">'+file.name+'</span></div></td>';
	if (preferences.mime_type =='1') retBuff += fl.td+file.mime_type+'</td>';
	if (preferences.size =='1') retBuff += fl.td+borkb(file.size)+'</td>';
	var now = new Date();
	var midnight = Date.parse(now.toDateString());
	var dtString = "";
	
	if (preferences.created =='1')
	{
		retBuff += fl.td+file.created+'</td>';
	}
	
	if ( preferences.modified =='1' )
	{
		retBuff += fl.td+file.modified+'</td>';
	}
	if (preferences.owner =='1'){
		retBuff += fl.td;
		retBuff += '<div onmouseover="draw_card(\'user\',\''+file.owner+'\')">'+file.owner+'</div></td>';
	}
	if (preferences.createdby_id =='1'){
		retBuff += fl.td;
		retBuff += '<div onmouseover="draw_card(\'user\',\''+file.createdby_id+'\')">'+file.createdby_id+'</div></td>';
	}
	if (preferences.modifiedby_id =='1'){
		retBuff += fl.td;
		retBuff += '<div onmouseover="draw_card(\'user\',\''+file.modifiedby_id+'\')">'+file.modifiedby_id+'</div></td>';
	}
	if (preferences.comment =='1') retBuff += fl.td+'<input id="'+file.name+'" class="inputComment" onkeydown="enterComments(event,this)" onclick="presetComments(this)" onblur="setComments(this)" value="'+(file.comment==null?'':file.comment)+'" alt="'+get_lang('Click to change comments')+'" title="'+get_lang('Click to change comments')+'"></input></td>';
	if (preferences.version =='1') retBuff += fl.td+'<span onclick="loadHistory(\''+file.name+'\')">'+file.version+'</span></td>';
	retBuff += "</tr>";
	return retBuff;
}
folderList.prototype.updateQuota = function(quotaSize,usedSpace){
	if (parseInt(quotaSize) != 0){
		var contentQuota = document.getElementById('content_quota');
		if (contentQuota != null)
			contentQuota.innerHTML = '<table width="102" cellspacing="0" cellpadding="0" border="0" id="table_quota"><tbody><tr><td width="102" nowrap="true" height="15" background="../phpgwapi/templates/default/images/dsunused.gif"><table cellspacing="0" cellpadding="0" border="0" style="width: '+parseInt((usedSpace/quotaSize)*100)+'%;"><tbody><tr><td height="15" class="dsused"/></tr></tbody></table></td><td nowrap="true" align="center"><span class="boxHeaderText">'+parseInt(usedSpace/quotaSize*100)+'% ('+borkb(usedSpace)+'/'+borkb(quotaSize)+')</span></td></tr></tbody></table></td></tr></table>';
	}
}
folderList.prototype.drawFiles = function(data){
	var fl = folderList;
	var returnData = unserialize(data);
	loadPermissions(returnData.permissions);
	var files = returnData.files;
	draw_paging(returnData.files_count,data);
	fl.init();
	fl.clear();
	var newpage = '';
	if (preferences.viewIcons == 1){
		for (var i = 0; i < files.length; i++)
		{
			newicon = '<div class="icon">';
			newicon += '<a href="./index.php?menuaction=filemanager.uifilemanager.view&file='+base64_encode(files[i].name)+'&path='+base64_encode(currentPath)+'" target="_blank">';
			newicon += '<div style="width:64; height:64; background-image:url('+files[i].icon+'); background-repeat: no-repeat;"></div>';
			newicon += '<span class="iconCaption">'+files[i].name+'</span>';
			newicon += '</a></div>';
			newpage += newicon;
		}

		fl.element.innerHTML = newpage;
	}
	else
	{
		var sp_hd = '<span name="head" style="cursor: pointer; cursor: hand;" onclick="reloadFiles(\'';
		var sp_tl = '</span></td>';
		newpage = '<table cellspacing="0" cellpadding="2" width="100%"><tbody><tr class="message_header">';
		newpage += fl.td+'<input onclick="selectAll(this)" type="checkbox"></td><td></td><td></td>';
		newpage += fl.td+sp_hd+'name\')" id="name">'+get_lang('file name')+sp_tl;
		if (preferences.mime_type =='1')
			newpage += fl.td+sp_hd+'mime_type\')" id="mime_type">'+get_lang('mime type')+sp_tl;
		if (preferences.size =='1')
			newpage += fl.td+sp_hd+'size\')" id="size">'+get_lang('size')+'</a>'+sp_tl;
		if (preferences.created =='1')
			newpage += fl.td+sp_hd+'created\')" id="created">'+get_lang('created')+'</a>'+sp_tl;
		if (preferences.modified =='1')
			newpage += fl.td+sp_hd+'modified\')" id="modified">'+get_lang('modified')+'</a>'+sp_tl;
		if (preferences.owner =='1')
			newpage += fl.td+sp_hd+'owner\')" id="owner">'+get_lang('owner')+'</a>'+sp_tl;
		if (preferences.createdby_id =='1')
			newpage += fl.td+sp_hd+'createdby_id\')" id="createdby_id">'+get_lang('created by')+'</a>'+sp_tl;
		if (preferences.modifiedby_id =='1')
			newpage += fl.td+sp_hd+'modifiedby_id\')" id="modifiedby_id">'+get_lang('modified by')+'</a>'+sp_tl;
		if (preferences.comment =='1')
			newpage += fl.td+sp_hd+'comment\')" id="comment">'+get_lang('comment')+'</a>'+sp_tl;
		if (preferences.version =='1') newpage += fl.td+sp_hd+'version\')" id="version">'+get_lang('version')+'</a>'+sp_tl+'</tr>';
		if (files != null)
			for (var i = 0; i < files.length; i++)
			{
				newpage += fl.createLine(files[i]);
			}
		else
			newpage = "<b>"+get_lang('no files in this directory.')+"</b>";
		fl.element.innerHTML = newpage;
		header = document.getElementsByName('head');
		for (var i=0; i < header.length; i++)
		{
			if (header[i].id == criteria){
				header[i].style.fontWeight = 'bold';
				arrow = document.createElement('IMG');
				if (order_type == '1')
					arrow.src = templatePath+'images/arrow_ascendant.gif';
				else
					arrow.src = templatePath+'images/arrow_descendant.gif';
				header[i].appendChild(arrow);
			}
		}
		fl.drawStripes();
	}
	folderList.updateQuota(returnData.quota.quotaSize,returnData.quota.usedSpace);
}
folderList.prototype.drawStripes = function(){
	var classTr = "tr_msg_read";
	folderList.init();
	var elements = folderList.element.firstChild.firstChild.childNodes;
	for (var i = 1; i < elements.length; i++){
		elements[i].className = classTr;
		classTr = (classTr == "tr_msg_read"?"tr_msg_read2":"tr_msg_read");
	}
}
var folderList = new folderList();

function toolbar()
{
	var element;
}
toolbar.prototype.clear = function (){
	this.element.innerHTML = "";
}
toolbar.prototype.load = function (){
	this.element = document.getElementById('fmMenu');
	if (permissions['read'] == 0){
		this.element.innerHTML = "";
		return;
	}
	var pageContent = '<table><tbody><tr>';
	var createButton = function(name) {
		return '<td name="'+name+'" class="toolButton" onclick="toolbar.control(\''+name+'\');" title="'+name+'"><img src="'+templatePath+'images/button_'+name+'.png" alt="'+name+'"><small>'+get_lang(name.replace('_',' '))+'</small></td>';
	}

	if (permissions['edit'] != 0){
		pageContent += createButton('edit');
		pageContent += createButton('rename');
	}
	if (permissions['delete'] != 0){
		pageContent += createButton('delete');
		pageContent += createButton('move_to');
	}
	pageContent += createButton('copy_to');

	this.element.innerHTML = pageContent+'</tr></tbody></table>';

}

toolbar.prototype.getCheckedFiles = function()
{
	filesUrl = "";
	var one_checked = false;
	var files = document.getElementsByName('fileman');
	var j=0;
	
	for (i = 0; i <  files.length; i++)
	{	
		if (files[i].checked)
		{
			one_checked = true;
			filesUrl += "&fileman["+j+"]="+base64_encode(files[i].value);
			j++;
		}
	}
	
	if (!one_checked)
	{
		write_msg(get_lang('Please select a file'));
		return;
	}
	
	return filesUrl;
}

toolbar.prototype.control = function ()
{
	if( arguments.length == 0 )
		return;

	var _arg 	= arguments[0];
	var _parent	= ( arguments[1] ) ? arguments[1] : null ; 

	if( _arg == 'archive' )
	{	
		var filesUrl		= this.getCheckedFiles();
		var password	= prompt(get_lang('Please, type a strong password (suggestion: at least 8 characters, letters and numbers) or leave it empty to archive only'));
		
		if ( password == null)
			return;
		
		if ( password.length > 0 )
		{
			var password2 = prompt(get_lang('Please, retype your password'));
			
			if ( password != password2 )
			{
				alert(get_lang('Error:')+get_lang('passwords are differents'));
				return;
			}
		}
		
		var pswd = crypt.encode( password );
		
		cExecute_('./index.php?menuaction=filemanager.vfs_functions.archive&pswd='
			+ base64_encode(pswd.toString())+'&path='+base64_encode(currentPath)+filesUrl,handler.archive);
	}		

	if( _arg == 'delete' )
	{	
		var filesUrl = this.getCheckedFiles();
		cExecute_('./index.php?menuaction=filemanager.vfs_functions.delete&path='+base64_encode(currentPath)+filesUrl,handler.del);
	}	

	if( _arg == 'edit' )
	{	
		var files				= document.getElementsByName('fileman');
		var one_checked	= false;
		
		for (i = 0; i <  files.length; i++)
		{	
			if ( files[i].checked )
			{
				one_checked = true;
				var filename=files[i].value;
			}
		}
		
		if (one_checked)
		{
			var address = (document.location.toString() ).split("&");
			;
			document.location = address[0]+"&"+_arg+".x=1&filename="+base64_encode(filename)+".&path="+base64_encode(currentPath);
		}
		else
		{
			write_msg( get_lang('Please select a file') );
		}
	}		

	if( _arg == 'new' )
	{
		var _address =  ( document.location.toString() ).split("?");

		var itens = [
		[ get_lang('empty file'),'javascript:newEmptyFile()',templatePath+'images/group_close.gif', '' ],
		[ get_lang('File from model'), _address[0]+"?menuaction=filemanager.uifilemanager.fileModels", templatePath+'images/group_close.gif', '' ],
		[ get_lang('Upload'), 'javascript:newUpload()', templatePath+'images/group_close.gif', '' ],
		[ get_lang('Advanced Upload'), 'javascript:newAdvancedUpload()', templatePath+'images/group_close.gif', '' ]
		];

		draw_menu( itens, _parent );
	}

	if( _arg == 'reload')
	{		
		last_folder = last_folder ? last_folder : currentPath;
		current_folder = ( current_folder != "" ) ? current_folder : currentPath;
		cExecute_('./index.php?menuaction=filemanager.uifilemanager.dir_ls&path='+base64_encode(currentPath)+'&criteria='+criteria+'&otype='+order_type+'&limit='+preferences.files_per_page+'&offset='+offset,folderList.drawFiles);
	}	

	if( _arg == 'tools' )
	{		
		var itens = [
		[ get_lang('Preferences'), 'preferences/preferences.php?appname=filemanager', templatePath+'images/preferences.png', 'width="16px" height="16px"' ],
		[ get_lang('Edit Folders'), 'javascript:editFolders()', templatePath+'images/button_createdir.png', 'width="16px" height="16px"'],
		[ get_lang('Share Folders'), './index.php?menuaction=preferences.uiaclprefs.index&acl_app=filemanager', templatePath+'images/mime16_directory.png', 'width="16px" height="16px"'],
		[ get_lang('View'), 'javascript:EditColumns()', templatePath+'images/editpaste.png', 'width="16px" height="16px"' ],
		[ get_lang('Archive'), 'javascript:toolbar.control("archive")', templatePath+'images/button_zip.png', 'width="16px" height="16px"' ]
		];

		draw_menu( itens, _parent );
	}

	if( _arg == 'rename' )
	{
		var files			= document.getElementsByName('fileman');
		var flagCheked	= false;
		
		for( var i = 0; i < files.length; i++ )
		{
			if( files[i].checked )
			{
				files[i].checked		= false;
				var _span			= document.getElementById( 'name_' + files[i].value );
				var	_parentNode	= _span.parentNode;
					_parentNode.style.height = (parseInt(_parentNode.style.height) + 4 );
				
				var	_input			= document.createElement("input"); 
					_input.id		= 'input_'+files[i].value;
					_input.size		= "35";
					_input.zIndex	= "99999";
					_input.value		= _span.innerHTML;
					_input.type		= 'text';

				// OnkeyUp
				configEvents( _input, "onkeyup", function(e)
				{
					if( e.keyCode == 13 ) 
					{
						_parentNode.style.height = (parseInt(_parentNode.style.height) - 4 );
						
						handler.rename( _input, _span );
					}
				});
				
				//OnBlur
				configEvents( _input, "onblur", function(e)
				{
					_parentNode.style.height = (parseInt(_parentNode.style.height) - 4 );
					handler.rename( _input, _span );
				});
				
				if( _parentNode != null )
				{	
					// Remove Span
					if( _span != null )
						_parentNode.removeChild( _span );
						
					// Add Input
					if( _input != null ) 
						_parentNode.appendChild( _input );
				}
				
				_input.focus();
			}
		}
	}	
	
	if ( ( _arg == 'move_to' ) || ( _arg == 'copy_to' ) )
	{	
		var filesUrl = this.getCheckedFiles();

		if ( filesUrl != undefined )
		{
			DocY -= ( folders.length * 30 );

			var action = ( ( _arg == 'move_to') ? get_lang('move to:') : get_lang('copy to:') );

			loadXtools();

			var _xml 	= Xtools.xml('files');
			var _files	= _xml.documentElement; 
			var _links	= _xml.createElement('links');

			for( var i = 0 ; i < folders.length ; i++ )
			{	
				var _lk = _xml.createElement('lk');
					_lk.setAttribute('function', "javascript:"+escape(_arg)+"('"+folders[i]+"','"+filesUrl+"'); close_window();" );
					_lk.appendChild( _xml.createTextNode( folders[i].replace( my_home_filemanager , get_lang("My folder") ) ) );
				
				_links.appendChild( _lk );
			}

			_files.appendChild( _links );

			var img_1 = path_filemanager + "templates/default/images/button_copy_to.png";
			var img_2 = path_filemanager + "templates/default/images/button_move_to.png";
			var img_3 = templatePath + "images/group_close.gif";	

			var pArgs = 
			{
				'action'		: action,
				'img'		: ( ( _arg == 'move_to') ? img_2 : img_1 ),
				'img_1'		: img_3,
				'width'		: 380,
				'height'		: 200
			};

			var code = Xtools.parse( _files, "copy_move_files.xsl", pArgs );

			draw_window( code , 380, 200 );
		}
	}
}

var toolbar = new toolbar();

function unarchive(filename)
{
	var password = crypt.encode(prompt(get_lang('Please, type archive password or leave it empty if it is not encrypted')));

	if (password != null )
	{
		cExecute_('./index.php?menuaction=filemanager.vfs_functions.unarchive&pswd='
			+ base64_encode(password)+'&path='+base64_encode(currentPath)+'&file='+base64_encode(filename), handler.archive);
	}
	else
		return;
}

function move_to(to,filesUrl)
{
	cExecute_( './index.php?menuaction=filemanager.vfs_functions.moveto&from='
		+ base64_encode(currentPath)+'&to='+base64_encode(to)+filesUrl, handler.moveto );
}

function copy_to(to,filesUrl)
{
	cExecute_('./index.php?menuaction=filemanager.vfs_functions.copyto&from='
		+ base64_encode(currentPath)+'&to='+base64_encode(to)+filesUrl, handler.copyto );
}

function draw_card(type,name)
{
	clearTimeout(menuTimeout);
	switch(type){
		case 'preview':
			var url = './index.php?menuaction=filemanager.vfs_functions.summary&file='+base64_encode(name)+"&path="+base64_encode(currentPath);
			var htmlData = '<img src=\"'+url+'\">';
			menuTimeout = setTimeout("draw_window_card(\'"+htmlData+"\')",500);
			break;
		case 'user':
			var url = './index.php?menuaction=filemanager.user.card&lid='+base64_encode(name);
			menuTimeout = setTimeout("cExecute_('"+url+"',draw_window_card)",500);
			break;
		default:
			break;
	}

}
function editFolders( operation )
{
	
	if( operation == 'new' || operation == 'remove')
	{
		var _selectFolders	= document.getElementById('folders_box');
		var Dfolder			= "";
		var parentDir		= "";
		
		for( var i = 0 ; i < _selectFolders.options.length; i++ )
		{
			if( _selectFolders.options[i].selected )
			{
				Dfolder 	= _selectFolders.options[i].value;
				parentDir	= _selectFolders.options[i].value;
			}
		}
	}
	
	if ( operation == 'new' )
	{
		var name = prompt(get_lang('Enter with the name of new file/directory'), '');
		
		if ( name != null && name != '' )
		{
			if( parentDir == "" )
				parentDir = my_home_filemanager;
				
			var parentDir_en = base64_encode( parentDir );
			cExecute_('./index.php?menuaction=filemanager.uifilemanager.createdir&path='
				+ parentDir_en+'&filename='+base64_encode(name), handler.refreshDir);
				
			currentPath = parentDir + '/' + name;
			close_window();
		}
	}
	else
	{
		if ( operation == 'remove' )
		{
			if( Dfolder != "" )
			{	
				if ( confirm(get_lang('Do you really want to remove folder: %1?',
					Dfolder.replace(my_home_filemanager, get_lang("My folder"))), '') )
					{
					var Dfolder_en = base64_encode( Dfolder );
					
					cExecute_('./index.php?menuaction=filemanager.uifilemanager.removedir&path=' + Dfolder_en ,handler.refreshDir );
					var lastIndex = Dfolder.lastIndexOf('/');
					currentPath = Dfolder.substr(0,lastIndex);
					close_window();
				}
			}
			else
				alert( get_lang("You must choose a folder !") );
		}
		else
		{
			loadXtools();
			
			var _xml 		= Xtools.xml("root");
			var _doc		= _xml.documentElement;
			var _folders	= _xml.createElement("folders");
			
			for( var i = 0; i < folders.length; i++ )
			{
				if( folders[i].indexOf(my_home_filemanager) > -1 )
				{
					var fd = _xml.createElement('name');
					fd.setAttribute('value', folders[i] );
					fd.appendChild( _xml.createTextNode(folders[i].replace(my_home_filemanager, get_lang("My folder"))) );
					_folders.appendChild(fd);
				}
			}
			
			_doc.appendChild( _folders );
			
			var pArgs =
			{
				'lang_new_folder'		: get_lang('new folder'),
				'lang_remove_folder'		: get_lang('remove folder'),
				'path_filemanager'		: path_filemanager,
				'onclick_new_folder'		: 'editFolders("new")',
				'onclick_remove_folder'	: 'editFolders("remove")'
			};
			
			var code = Xtools.parse( _doc, "edit_folders.xsl", pArgs );
			
			draw_window( code, 310, 230 );
		}
	}
}

function draw_window_card(content)
{
	var menu = document.getElementById('menu_newFile');
	if (menu == null)
	{
		menu = document.createElement('DIV');
		menu.className = 'menubox';
		menu.id = "menu_newFile";
		menu.style.left = DocX;
		menu.style.top = (DocY+20)+"px";
		menu.width = "100%";
		menu.onmouseout = function () {
			menuTimeout = setTimeout("hide_card()",50);
		}
		menu.onmouseover = function () {
			clearTimeout(menuTimeout);
		};
		menu.style.zIndex='1';
		document.getElementById('divAppbox').appendChild(menu);
	}
	else
	{
		menu.style.left = DocX;
		menu.style.top = (DocY+20)+"px";
	}
	menu.innerHTML = content;
	menu.style.visibility = 'visible';
	clearTimeout(menuTimeout);
	menuTimeout = setTimeout("hide_card()",4000);
}
function hide_card()
{
	var menuNewFile = document.getElementById('menu_newFile');
	
	if ( menuNewFile != null )
		menuNewFile.style.visibility = 'hidden';
}

function draw_paging( num_files, data)
{
	num_files = parseInt(num_files);
	var total_pages = 1;

	if( last_folder != current_folder )
	{
		lastPage = 1;
		current_page = 1;
		last_folder = current_folder;
	}

	if( num_files > parseInt(preferences.files_per_page) ) 
	{
		total_pages = parseInt(num_files/preferences.files_per_page);
		
		if( (num_files/preferences.files_per_page) > total_pages )
			total_pages++;
	}

	if( total_pages == 1) 
	{
		if( span_paging = document.getElementById("span_paging") )
		{
			span_paging.parentNode.removeChild(span_paging);
		}
		
		return;
	}

	span_paging = document.getElementById("span_paging");

	if( !span_paging )
	{
		span_paging = document.createElement("DIV");
		span_paging.id = "span_paging";
		span_paging.className = "boxHeaderText";
		span_paging.align="right";
		document.getElementById("div_menu_c3").appendChild(span_paging);
	}
	span_paging.style.width="100%";
	span_paging.innerHTML="";
	files_range_begin = 1;
	files_range_end = preferences.files_per_page;
	
	if ( current_page != 1) 
	{
		lnk_page = document.createElement("A");
		lnk_page.setAttribute("href", "javascript:current_page=1;offset=0;toolbar.control('reload');");
	}
	else
	{
		lnk_page = document.createElement("SPAN");
	}
	
	span_paging.appendChild(lnk_page);

	lnk_page.innerHTML = "&lt;&lt;";
	lnk_page.title = get_lang("First");
	span_paging.innerHTML += "&nbsp;";

	if( current_page == lastPage + numPages)
		lastPage = current_page - 1;
	else if( (lastPage != 1 && lastPage == current_page) || current_page == total_pages )
		lastPage = current_page - (numPages - 1);
	else if( current_page == 1 )
		lastPage = 1;

	if(lastPage < 1)
		lastPage = 1;
	else if( lastPage > 1 && (lastPage > (total_pages -(numPages - 1))) )
		lastPage = total_pages -(numPages - 1);

	var	hasMarked = false;

	for( i = lastPage; i <= total_pages; i++) 
	{
		if( current_page == i || (i == total_pages && !hasMarked)) 
		{
			lnk_page = document.createElement("SPAN");
			span_paging.appendChild(lnk_page);
			lnk_page.innerHTML = "&nbsp;<b>"+i+"</b>&nbsp;";
			hasMarked = true;
			continue;
		}
		else
		{
			lnk_page = document.createElement("A");
			span_paging.appendChild(lnk_page);
			files_range_begin = ((i*preferences.files_per_page)-(preferences.files_per_page-1));
			files_range_end = (i*preferences.files_per_page);
			lnk_page.setAttribute("href", "javascript:current_page="+i+";offset=((current_page-1)*preferences.files_per_page);toolbar.control('reload');");
		}

		lnk_page.innerHTML = "&nbsp;...&nbsp;";

		if( i == (lastPage + numPages) )
			break;
		else if(lastPage == 1 || i != lastPage)
			lnk_page.innerHTML = "&nbsp;"+i+"&nbsp;";

		span_paging.innerHTML += "&nbsp;";                                                                            
	}

	if( current_page != total_pages )  
	{
		lnk_page = document.createElement("A");
		files_range_begin = ((total_pages*preferences.files_per_page)-(preferences.files_per_page-1));
		files_range_end = (total_pages*preferences.files_per_page);
		lnk_page.setAttribute("href", "javascript:current_page="+total_pages+";offset=((current_page-1)*preferences.files_per_page);toolbar.control('reload');");
	}
	else
	{
		lnk_page = document.createElement("SPAN");
	}
	
	span_paging.innerHTML += "&nbsp;";
	span_paging.appendChild(lnk_page);

	lnk_page.title = get_lang("Last");
	lnk_page.innerHTML = "&gt;&gt;";
}