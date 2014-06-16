// BEGIN: FUNCTION RESIZE WINDOW
if (!expresso_offline) {
	var _showBar = showBar;
	var _hideBar = hideBar;
}

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

window.onresize = resizeWindow;

var message = "Não Informado";

//MAILARCHIVER-01
try{
   var ArchiveServices = new web_service_mailarchiver_serpro__ArchiveServices();
   ArchiveServices.url = mail_archive_protocol + "://" + mail_archive_host + ":" + mail_archive_port + "/arcserv/ArchiveServices";
   ArchiveServices.synchronous = true;
}
catch (e){
    var ArchiveServices = null;
}

function config_events(pObj, pEvent, pHandler)
{
    if( typeof pObj == 'object')
    {
        if( pEvent.substring(0, 2) == 'on')
            pEvent = pEvent.substring(2, pEvent.length);

        if ( pObj.addEventListener )
            pObj.addEventListener(pEvent, pHandler, false);
        else if( pObj.attachEvent )
            pObj.attachEvent('on' + pEvent, pHandler );
    }
}

function openMessenger()
{
	var content_folders		= $("#content_folders");
	var content_messenger	= $("#content_messenger");

	content_folders.css("display","none");
	content_messenger.find("div#_plugin").css("display","block");
	content_messenger.find("div#_menu").off("click", openMessenger ).on("click", closeMessenger );

	resizeWindow();
}

function closeMessenger()
{
	var content_folders 	= $("#content_folders");
	var content_messenger	= $("#content_messenger");
	
	content_folders.css("display","block");
	content_messenger.find("div#_plugin").css("display","none");
	content_messenger.find("div#_menu").off("click", closeMessenger ).on("click", openMessenger );

	resizeWindow();
}

function resizeWindow()
{
	var clientWidth 		= $(window).innerWidth();
	var clientHeight 		= $(window).innerHeight() - 8;
	var divScrollMain 		= $("#divScrollMain_"+numBox);
	var table_message 		= Element("table_message");
	var content_folders 	= $("#content_folders");
	var content_messenger 	= $("#content_messenger");

	if( divScrollMain.length )
	{
		divScrollMain.css("height", (clientHeight - ( divScrollMain.position().top + (table_message.clientHeight ? table_message.clientHeight : table_message.offsetHeight))));
	}

	if( typeof(BordersArray) != 'undefined' )
	{
		for( var i = 1; BordersArray.length > 1 && i < BordersArray.length; i++ )
		{
			var div_scroll	= $("#div_message_scroll_"+BordersArray[i].sequence);
			var div 		= $("#content_id_"+BordersArray[i].sequence);

			if( div.length )
			{
				div.css('height',(clientHeight - (div.position().top + (table_message.clientHeight ? table_message.clientHeight : table_message.offsetHeight)+2)) + "px");
				div.css('width', (clientWidth - (div.position().top + 10)) + "px");
			}
			
			if( div_scroll.length )
			{
				div_scroll.css('height', (clientHeight - (div_scroll.position().top + (table_message.clientHeight ? table_message.clientHeight : table_message.offsetHeight)+5)) + "px");
				div_scroll.css('width', (clientWidth - (div_scroll.position().left+15)) + "px");
			}
		}
	}

	if( content_folders.length )
	{
		var positionContent = content_folders.position();

		if( content_messenger.find("div#_menu").length > 0 )
		{
			var _heightBrowser = 0;
			
			// FIREFOX
			if( $.browser.mozilla )
			{
				_heightBrowser = ( parseInt($.browser.version) > 15 ) ? _heightBrowser = $(window).innerHeight() - 335 : _heightBrowser = $(window).innerHeight() - 342;
			}

			// CHROME
			if( $.browser.chrome )
			{
				_heightBrowser = $(window).innerHeight() - 341;
			}

			// MSIE
			if( $.browser.msie )
			{
				_heightBrowser = $(window).innerHeight() - 332;
			}

			if( BordersArray.length > 1 )
			{
				content_folders.css("height", $(window).innerHeight() - ( positionContent.top + $("#table_message").height() + 27 ) );
			}
			else
			{
				content_folders.css("height", $(window).innerHeight() - ( positionContent.top + $("#table_message").height() + 20 ) );
			}

			content_messenger.find("div ul.chat-list").css("height", _heightBrowser );
		}
		else
		{
			content_folders.css('height',(clientHeight - (content_folders.position().top + (content_folders.position().top > $("#search_div").position().top ? 0 : ($("#search_div").height() ? $("#search_div").height() : $("#search_div").height()) + 5))) + "px");
		}
	}
	
	redim_borders(count_borders());
	
	resizeMailList();
}
// END: FUNCTION RESIZE WINDOW

var _beforeunload_ = window.onbeforeunload;

window.onbeforeunload = unloadMess;

function unloadMess(){
    if (typeof BordersArray == 'undefined') return; // We're not on expressoMail
	var ret = null;
	$(".conteudo .new-msg-head-buttons .save").each(function(index) { //Pega todos os botões "Save" de todas as abas
		if($(this).is(':disabled')) { //se a mensagem estiver salva (botão Salvar desabilitado)
			ret = null;
		} else { //se estiver em modo edição (botão Salvar habilitado)
			ret = get_lang('There are still editing posts, really want to leave the page')+'?';
		}
	});
	if (ret) return ret;
}

// Translate words and phrases using user language from eGroupware.
function get_lang(_key) {
	if (typeof(_key) == 'undefined')
		return false;
	var key = _key.toLowerCase();

        if(array_lang[key])
            var _value = array_lang[key];
        else
            var _value = _key+"*";
    
	

	if(arguments.length > 1)
		for(j = 1; typeof(arguments[j]) != 'undefined'; j++)
			_value = _value.replace("%"+j,arguments[j]);
    

    return _value;
}

// Make decimal round, using in size message
function round(value, decimal){
	var return_value = Math.round( value * Math.pow( 10 , decimal ) ) / Math.pow( 10 , decimal );
	return( return_value );
}

// Change the class of message.
// In refresh, the flags UnRead and UnSeen don't exist anymore.
function set_msg_as_read(msg_number, selected, fromMenu){

    if(fromMenu){
        expresso_mail_archive.getMessageMenu(msg_number); 

        var data = expresso_mail_archive.fromMenu;

        if(data.DispositionNotificationTo && $("#"+get_selected_messages()).hasClass("tr_msg_unread")){
            $.Zebra_Dialog(get_lang(' of its messages could not be marked as read, because it contains a read confirmation.'), {
                'type':     'warning',
                'overlay_opacity': '0.5',
                'custom_class': 'custom-zebra-filter',
                'buttons':  ['Fechar']
            });
            return false;
        }
    }
	tr_message = Element(msg_number);
	if (exist_className(tr_message, 'tr_msg_unread'))
		decrement_folder_unseen();
	remove_className(tr_message, 'tr_msg_unread');
	remove_className(tr_message, 'selected_msg');
	
	if( document.getElementById("td_message_unseen_"+msg_number) != null )
		Element("td_message_unseen_"+msg_number).innerHTML = "<img src ='templates/"+template+"/images/seen.gif' title='"+get_lang('Seen')+"'>";
	
	connector.purgeCache();
	return true;
}

function msg_is_read(msg_number, selected){
	tr_message = Element(msg_number);
	return !(tr_message && LTrim(tr_message.className).match('tr_msg_unread'))
}

function set_msg_as_unread(msg_number, isSearch){
	tr_message = Element(msg_number);
	if ((exist_className(tr_message, 'tr_msg_read') || exist_className(tr_message, 'tr_msg_read2')) && (!exist_className(tr_message, 'tr_msg_unread')))
		increment_folder_unseen();
	remove_className(tr_message, 'selected_msg');
	add_className(tr_message, 'tr_msg_unread');
	if(!isSearch)
		Element("td_message_unseen_"+msg_number).innerHTML = "<img src ='templates/"+template+"/images/unseen.gif' title='"+get_lang('Unseen')+"'>";
}

function set_msg_as_flagged(msg_number, isSearch){
	var msg = Element(msg_number);
	remove_className(msg, 'selected_msg');
	add_className(msg, 'flagged_msg');
	if(isSearch)
		Element("td_message_important_"+msg_number.substr(0,msg_number.indexOf('_'))).innerHTML = "<img src ='templates/"+template+"/images/important.png' title='"+get_lang('Important')+"'>";
	else
		Element("td_message_important_"+msg_number).innerHTML = "<img src ='templates/"+template+"/images/important.png' title='"+get_lang('Important')+"'>";
}

function set_msg_as_unflagged(msg_number, isSearch){
	var msg = Element(msg_number);
	remove_className(msg, 'selected_msg');
	remove_className(msg, 'flagged_msg');
	if(isSearch)
		Element("td_message_important_"+msg_number.substr(0,msg_number.indexOf('_'))).innerHTML = "&nbsp;&nbsp;&nbsp;";
	else
		Element("td_message_important_"+msg_number).innerHTML = "&nbsp;&nbsp;&nbsp;";
}

function removeAll(id){
	do
	{
		if (typeof(Element(id)) == 'undefined')
			break;
		//Element(id).parentNode.removeChild(Element(id));
		$('#'+id).remove();
	}
	while(Element(id));
}

function get_current_folder(){
	return current_folder;
}

// Kill current box (folder or page).
function kill_current_box(){
	var box = document.getElementById("table_box");
	if (box != null)
		box.parentNode.removeChild(box);
	else
		return false;
}

//Remove as linhas da tabela sem deletar o corrent_box
function remove_rows(el){
	while (el.rows.length > 0)  {
		el.deleteRow(0);
	} 
	Element("tot_m").innerHTML = 0 
	Element("new_m").innerHTML = 0 
}

// Kill current paging.
function kill_current_paging(){
	var paging = Element("span_paging");
	if (paging != null)
		paging.parentNode.removeChild(paging);
}

function show_hide_span_paging(ID){
	if ((ID != "0") && Element("span_paging")) 
		Element("span_paging").style.display = 'none';
	else
		if (Element("span_paging"))
			Element("span_paging").style.display = '';
}

//Get the current number of messages in a page.
function get_messages_number_in_page(){
	//Get element tBody.
	main = document.getElementById("tbody_box");

	// Get all TR (messages) in tBody.
	main_list = main.childNodes;

	return main_list.length;
}

function download_local_attachment(url) {
	url=encodeURI(url);
	url=url.replace("%25","%");
	if (div_attachment == null){
		var div_attachment = document.createElement("DIV");
		div_attachment.id="id_div_attachment";
		document.body.appendChild(div_attachment);
	}
	div_attachment.innerHTML="<iframe style='display:none;width:0;height:0' name='attachment' src='"+url+"'></iframe>";
	window.onbeforeunload = function(){return unloadMess();}
} 

function download_attachments(msg_folder, msg_number, idx_file, msg_part, encoding, new_file_name, show_iframe){
	div_attachment = document.getElementById("id_div_attachment");
	var params = '';
	if(msg_folder)
		msg_folder = Base64.decode(msg_folder);
	if (div_attachment == null){
		var div_attachment = document.createElement("DIV");
		div_attachment.id="id_div_attachment";
		document.body.appendChild(div_attachment);
	}
	if(new_file_name) {
		var extension = /\.[^.]*$/.exec(new_file_name);
		if (extension == ".eml")
			params = "&newFilename="+new_file_name; //name_of_message.eml
		else // when more than one message
			params = "&newFilename="+escape(new_file_name); //mensagens.zip
	}
	if(encoding)
		params += "&encoding="+encoding;

        div_attachment.innerHTML="<iframe style='display:none;width:0;height:0' name='attachment' src='inc/get_archive.php?msgFolder="+msg_folder+"&msgNumber="+msg_number+"&idx_file="+idx_file+"&indexPart="+msg_part+params+"'></iframe>";

}

function download_all_attachments(msg_folder, msg_number){
	var handler_source = function(data){
		download_attachments(null, null, data, null,null,'anexos.zip');
	}
	cExecute("$this.exporteml.download_all_attachments",handler_source,"folder="+utf8_decoder(msg_folder)+"&num_msg="+msg_number);
}
//ADD forwarded files
function addForwardedFile(id_border,file_name,link,divFiles){
	if(!divFiles)
		var divFiles = document.getElementById("divFiles_"+id_border);

	if (! divFiles)
		return false;

	if (divFiles.lastChild)
		var countDivFiles = parseInt(divFiles.lastChild.id.split('_')[2]) + 1;

	if (! countDivFiles)
		var countDivFiles = 1;

	var divFile = document.createElement('DIV');

	var inputFile = document.createElement("INPUT");
	if (!expresso_offline) {
		if (!is_ie) {
			var tmp_id_border = document.createAttribute('id_border');
			tmp_id_border.value = id_border;

			inputFile.setAttributeNode(tmp_id_border);
			inputFile.id = "inputFile_" + id_border + "_" + countDivFiles;
			inputFile.type = 'file';
			inputFile.size = 50;
			inputFile.maxLength = 255;
			inputFile.name = 'file_' + countDivFiles;
			inputFile.style.display = "none";
		}
		else {
			inputFile = document.createElement("link");

			var tmp_id_border = document.createAttribute('id_border');
			tmp_id_border.value = id_border;

			inputFile.setAttributeNode(tmp_id_border);
			inputFile.id = "inputFile_" + id_border + "_" + countDivFiles;
			inputFile.name = 'file_' + countDivFiles;


		}

	}
	else {
		inputFile.type = 'hidden';
		inputFile.name = 'offline_forward_' + countDivFiles;
	}
	divFile.appendChild(inputFile);

	var a_tmp = new Array();
	a_tmp[0] = "local_";
	a_tmp[1] = 'file_' + countDivFiles;
	a_tmp[2] = file_name;
	s_tmp = escape(connector.serialize(a_tmp));
	var checkbox = document.createElement("INPUT");
	checkbox.type = "checkbox";
	checkbox.id = "checkbox_"+id_border+"_"+countDivFiles;
	checkbox.name = "local_attachments[]";
	checkbox.setAttribute("checked", "checked");

	checkbox.value = s_tmp;
	divFile.appendChild(checkbox);

	var link_attachment = document.createElement("A");
	link_attachment.setAttribute("href", link);

	link_attachment.innerHTML = file_name;
	divFile.appendChild(link_attachment);

	countDivFiles++;
	divFile.id = "divFile_"+id_border+"_"+countDivFiles;
	divFiles.appendChild(divFile);

	return inputFile;
}

// Add Input File Dynamically.
function addFile(id_border){
	divFiles = document.getElementById("divFiles_"+id_border);
	if (! divFiles)
		return false;

	if (divFiles.lastChild)
		var countDivFiles = parseInt(divFiles.lastChild.id.split('_')[2]) + 1;

	if (! countDivFiles)
		var countDivFiles = 1;

	divFile = document.createElement('div');

 	var inputFile = document.createElement("input");
 	inputFile.id        = "inputFile_"+id_border+"_"+countDivFiles;
 	inputFile.name      = "file_"+countDivFiles;
 	inputFile.type      = "file";
 	inputFile.size      = 50;
 	inputFile.maxlength = 255;
 	inputFile.onchange  = function () {
            validateFileExtension(this.value, this.id.replace('input','div'), this.getAttribute('id_border'));
 	};

 	divFile.appendChild(inputFile);

 	var linkFile = document.createElement("a");
 	linkFile.id        = "linkFile_"+id_border+"_"+countDivFiles;
 	linkFile.href      = 'javascript:void(0)';
 	linkFile.onclick   = function () {removeFile("divFile_"+id_border+"_"+countDivFiles); return false;};
 	linkFile.innerHTML = get_lang("Remove");

        divFile.appendChild(linkFile);
        divFile.id = "divFile_"+id_border+"_"+countDivFiles;
        divFiles.appendChild(divFile);

	return inputFile;
}
//	Remove Input File Dynamically.
function removeFile(id){
	var el = Element(id);
	el.parentNode.removeChild(el);
}

function validateFileExtension(fileName, id, id_border){

	var error_flag  = false;

	if ( fileName.indexOf('/') != -1 )
	{
		if (fileName[0] != '/'){ // file name is windows format?
			var file = fileName.substr(fileName.lastIndexOf('\\') + 1, fileName.length);
			if ((fileName.indexOf(':\\') != 1) && (fileName.indexOf('\\\\') != 0)) // Is stored in partition or a network file?
				error_flag = true;
		}
		else // is Unix
			var file = fileName.substr(fileName.lastIndexOf('/') + 1, fileName.length);
	}
	else  // is Firefox 3
		var file = fileName;

	var fileExtension = file.split(".");
	fileExtension = fileExtension[(fileExtension.length-1)];
	for(var i=0; i<denyFileExtensions.length; i++)
	{
		if(denyFileExtensions[i] == fileExtension)
		{
			error_flag = true;
			break;
		}

	}

	if ( error_flag == true )
	{
		alert(get_lang('File extension forbidden or invalid file') + '.');
		removeFile(id);
		addFile(id_border);
		return false;
	}
	return true;
}

var setTimeout_write_msg = 0;
var old_msg = false;	
// Funcao usada para escrever mensagem
// notimeout = True : mensagem nao apaga
function write_msg(msg, notimeout){
	if (setTimeout_write_msg)
		clearTimeout(setTimeout_write_msg);

	var msg_div = Element('em_div_write_msg');
	var old_divStatusBar = Element("divStatusBar");

	if(!msg_div) {
		msg_div = document.createElement('DIV');
		msg_div.id = 'em_div_write_msg';
		msg_div.className = 'em_div_write_msg';
        msg_div.setAttribute("z-index","2000");
        msg_div.style.position = "relative";
		old_divStatusBar.parentNode.insertBefore(msg_div,old_divStatusBar);

	}

	if( document.getElementById('JabberMessenger'))
		loadscript.adIcon();

	msg_div.innerHTML = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tbody><tr><th width="40%"></th><th noWrap class="action_info_th">'+msg+'</th><th width="40%"></th></tr></tbody></table>';
	
	//old_divStatusBar.style.display = 'none';
	msg_div.style.display = '';
	// Nao ponha var na frente!! jakjr
	handle_write_msg = function(){
		try{
			if(!old_msg)
				clean_msg();
			else
				write_msg(old_msg, true);
		}
		catch(e){}
	}
	if(notimeout)
		old_msg = msg;
	else
		setTimeout_write_msg = setTimeout("handle_write_msg();", 5000);
}
// Funcao usada para apagar mensagem sem timeout
function clean_msg(){
	old_msg = false;
	var msg_div = Element('em_div_write_msg');
	var old_divStatusBar = Element("divStatusBar");
	if(msg_div)
		msg_div.style.display = 'none';
	old_divStatusBar.style.display = '';
}

function make_body_reply(body, to, date_day, date_hour){
	to = to.replace("<","&lt;");
	to = to.replace(">","&gt;");
	block_quoted_body ='<div>';
	block_quoted_body += get_lang('At %1, %2 hours, %3 wrote:', date_day, date_hour, to) + '<br type="_moz"></div>';
	block_quoted_body += "<blockquote style=\"border-left: 1px solid rgb(204, 204, 204); margin: 0pt 0pt 0pt 0.8ex; padding-left: 1ex;\">";
	block_quoted_body += body;
	block_quoted_body += "</blockquote>";
	return block_quoted_body;
}

function make_forward_body(body, from, date, subject, to, cc){
	from = from.replace(/</g,"&lt;");
	from = from.replace(/>/g,"&gt;");
	to = to.replace(/</g,"&lt;");
	to = to.replace(/>/g,"&gt;");
	var forward_body = '<div>---------- ' + get_lang('Forwarded message') + ' ----------<br type="_moz"></div><div>';
	forward_body += get_lang('From') + ': ' + from + '<br type="_moz"></div><div>';

    if(date.indexOf('(') !== -1) //Retira a string com calculo da diferença de horas exemplo: (20 minutus atras);
        date = date.substr(0,date.indexOf('('));

    forward_body += get_lang('Date') + ': ' + date + '<br type="_moz"></div><div>';
	forward_body += get_lang('Subject') + ': ' + subject + '<br type="_moz"></div><div>';
	forward_body += get_lang('To') + ': ' + to+ '<br type="_moz"></div><div>';
	if(cc != undefined){
		cc = cc.replace(/</g,"&lt;");
		cc = cc.replace(/>/g,"&gt;");
		forward_body += get_lang('CC') + ': ' + cc+ '<div><br type="_moz"></div><div><br type="_moz"></div><div><br type="_moz"></div>';
	}
	forward_body += body;
	return forward_body;
}

function emMessageSearch(e,value){
	var	e  = is_ie ? window.event : e;
	if(e.keyCode == 13) {
		search_emails(value);
	}
}

function validateEmail(email){
	if (typeof(email) != 'string')
		return false;
	var validName = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/i;
	//emailParts = email.split('@');
	return validName.test(email);
}
function validateDomain(domain){
	var domainReg = /^(([A-Za-z\d][A-Za-z\d-]{0,61}[A-Za-z\d]\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/i;
	return (domainReg.test(domain));
}

function validateUrl(url){
	var urlReg = /([A-Za-z]{2,7}:\/\/)(.*)/i;
	urlParts = url.split(urlReg);
	return (urlParts[1].length > 4 &&  validateDomain(urlParts[2]));
}

function performQuickSearch(keyword){
	if (preferences.quick_search_default=='1')
		emQuickSearch(keyword, 'null', 'null', 'expressoMail');
	else
		search_emails(keyword);
}

function emQuickSearch(emailList, field, ID, Type, force){
	var quickSearchKeyBegin;
	var quickSearchKeyEnd;
	var content = $("#content_id_"+ID);
	
	if(expresso_offline) {
		alert(get_lang('Not allowed in offline mode'));
		return;
	}
	if ((field != 'null') && (ID != 'null'))
	{
		connector.loadScript("QuickCatalogSearch");
		if (typeof(QuickCatalogSearch) == 'undefined'){
			setTimeout('emQuickSearch("'+emailList+'", "'+field+'", "'+ID+'", "'+Type+'", "'+force+'")',500);
			return false;
		}
	}
	else
	{
		connector.loadScript("QuickSearchUser");
		if (typeof(QuickSearchUser) == 'undefined'){
			setTimeout('emQuickSearch("'+emailList+'", "'+field+'", "'+ID+'", "'+Type+'", "'+force+'")',500);
			return false;
		}
	}	

	var handler_emQuickSearch = function(data)
	{
        if (data){
            if ((!data.status) && (data.error == "many results")){
                alert(get_lang('More than %1 results. Please, try to refine your search.',data.maxResult));
                return false;
            }

            if (data.length > 0){
                if ((field != 'null') && (ID != 'null'))
                {
                    QuickCatalogSearch.showList(data, quickSearchKeyBegin, quickSearchKeyEnd, ID, field);
                }
                else
                {
                    QuickSearchUser.showList(data);
                }
            }
            else
                alert(get_lang('None result was found.'));
        }else{
            alert(get_lang('None result was found.'));
        }
		return true;
	}
	if ((field != 'null') && (ID != 'null'))
	{
		content.find(field).focus();
		var i = getPosition(content.find(field)[0]); //inputBox.selectionStart;
		var j = --i;

		// Acha o inicio
    	while ((j >= 0) && (emailList.charAt(j) != ',')){j--};
	    quickSearchKeyBegin = ++j;

	    // Acha o final
    	while ((i <= emailList.length) && (emailList.charAt(i) != ',')){i++};
	    quickSearchKeyEnd = i;

	    // A Chave da Pesquisa
    	var search_for = trim(emailList.substring(quickSearchKeyBegin, quickSearchKeyEnd));
	}
	else
		var search_for = emailList;
	if(preferences.search_characters_number == 'x')
		preferences.search_characters_number = 0;
		
	if (search_for.length < preferences.search_characters_number){
		alert(get_lang('Your search argument must be longer than %1 characters.', preferences.search_characters_number));
		return false;
	}

    if( Type == undefined )
        cExecute ("$this.ldap_functions.quicksearchcontact&search_for="+search_for+"&field="+field+"&ID="+ID, handler_emQuickSearch);
    else
        cExecute ("$this.ldap_functions.quicksearchcontact&search_for="+search_for+"&field="+field+"&ID="+ID+"&Type="+Type, handler_emQuickSearch);
}

function filterbox()
{
	init_filters();
	jQuery('.expresso-window-container').dialog('open');
}

/* 
	Abre o diálogo de edição de filtro na tela de criação de filtro,  
	com os dados da mensagem (remetente e assunto) pré-carregados. 
*/ 
 function filter_from_msg (msg) { 
	var html = DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {}); 
	initialize_container(html);
	outOfficeVerify();
	list_container = create_filter_dialog(); 
	render_new_rule(msg.from.email, html_entities(msg.subject)); 
	$('.expresso-window-container').dialog('open'); 
} 

function sharebox(){

	var handler_imap_getacl = function(data)
	{

		connector.loadScript("finder", "../services/");
		connector.loadScript("sharemailbox");
		
		if (typeof(sharemailbox) == 'undefined')
		{
			setTimeout('sharebox()',500);
			return false;
		}
		
		sharemailbox.makeWindow(data);
	}
	
	cExecute ("$this.imap_functions.getacl", handler_imap_getacl);
}

function configureLabels(data){
    if(get_current_folder().split("_")[0] == "local"){
        alert("_[[Local folders do not provide support for monitoring.]]");
        return true;
    }

	dialogElement = $('.label-configure-win').html(DataLayer.render("../prototype/modules/mail/templates/label_configure.ejs", {}))
	
	dialogElement.dialog({
		width:825,
		height:420,
		title:' Configuração do Marcador',
		resizable:false,
		modal: true,
		closeOnEscape:true,
		close:function(event, ui) {
			//select_all_search_messages(false, 'content_id_'+currentTab);
			event.stopPropagation();
            if(typeof(shortcut) != 'undefined') shortcut.disabled = false;
		},
        open: function(event, ui) 
        {
            if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
        },
		autoOpen:false,
		dialogClass: 'dialog-configure-label'
	});
	init_label({window:dialogElement, selectedItem:data.selectedItem, applyToSelectedMessages:data.applyToSelectedMessages});	
	dialogElement.dialog("open");
}

function configureFollowupflag(){

    if(get_current_folder().split("_")[0] == "local"){
        alert("Pastas locais não fornece suporte para acompanhamento.");
        return true;
    }

	var messages = new Array();
	var selectedMessageIds = new Array();
	var folder_name;
	if (currentTab == 0) {
		selectedMessageIds = get_selected_messages().split(",");
	} else {
		selectedMessageIds = get_selected_messages_search().split(",");
		var id_border = currentTab.replace(/[a-zA-Z_]+/, "");
	}
	
	var roles = get_selected_messages_search_role().split(',');
	for (var i=0; i<selectedMessageIds.length; i++) {
		if (currentTab == 0) {
			folder_name = current_folder;
			var number = selectedMessageIds[i];
		}else{
			var tr = $('[role="'+roles[i]+'"]');
			folder_name = $(tr).attr('name'); 
			var id = $(tr).attr('id'); 
			var number = id.replace(/_[a-zA-Z0-9]+/,"");
		}
		messages.push(onceOpenedHeadersMessages[folder_name][number] || number);
	}

	if(!User.followupflags)
		DataLayer.remove('followupflag', false);
		User.followupflags = DataLayer.get('followupflag', true);
	var data = {
		followupflags: User.followupflags,
		messages: messages
	};
	dialogElement = $('.followupflag-configure-win').html(DataLayer.render("../prototype/modules/mail/templates/followupflag_configure.ejs", data))

	dialogElement.dialog({
		width:532,
		height:420,
		title:get_lang('Follow up'),
		resizable:false,
		modal: true,
		closeOnEscape:true,
		close:function(event, ui) {
			//select_all_search_messages(false, 'content_id_'+currentTab);
			event.stopPropagation();
            if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
		},
        open: function(event, ui) 
        {
            if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
        },
		autoOpen:false
	});	
	init_followup({window:dialogElement, selectedMessages:selectedMessageIds});
	dialogElement.dialog("open");
}

function open_rss(param){
	connector.loadScript("news_edit");
	if (typeof(news_edit) == 'undefined')
	{
		setTimeout('open_rss(\''+param+'\')',500);
		return false;
	}
	news_edit.read_rss(param);
	return true;
}

function editrss(){
	connector.loadScript("news_edit");
	if (typeof(news_edit) == 'undefined')
	{
		setTimeout('editrss()',500);
		return false;
	}
	news_edit.makeWindow();
}




function preferences_mail(){
	location.href="../preferences/preferences.php?appname=expressoMail";
}

function search_emails(value, data)
{
	var resize = resize_borders();
    
    if( !resize )
    {
        var str_continue = '';
        var bolContinue = true;			
		str_continue = '\n' + get_lang('You must manually close one of your tabs before opening a new one');
        
        if ( preferences.auto_close_first_tab == 1 )
        {				                
            var children = Element('border_tr').childNodes;
            var bolDelete = true;
            
            for( var i = 0 ; i < children.length ; i++ )
            {
                if ((children[i].nodeName === 'TD') && (children[i].id!=='border_id_0') && (children[i].id!=='border_blank'))
                {
                    bolDelete = true;
                    
                    var num_child = children[i].id.toString().substr(10);
                    
                    alternate_border(num_child);
                    
                    if( editTest(num_child) )
                    {
                        bolDelete = false;
                    }
                    
                    if( bolDelete || bolContinue )
                    {
						str_fechar = '\n' + get_lang('Reached maximum tab limit. Want to close this tab');
						
						var confirmacao = confirm(str_fechar);
                        
                        if( confirmacao )
                        {
							bolContinue = false;
							delete_border(num_child, 'false');
						
						}
					}
                }
			}				
        }
        else
        {			
			alert( get_lang('Reached maximum tab limit') + str_continue );
        }
    }
    else
    {
		/*// if( $.trim(value) !== "" )
			EsearchE.quickSearchMail(value, null, 'SORTDATE_REVERSE');
		// else*/
		EsearchE.showForms();
	}
	
	$("#em_message_search").val("");
}

function source_msg(id_msg,folder){
	var num_msg = id_msg.substr(0,(id_msg.length - 2));
	var handler_source = function(data){
		download_attachments(null, null, data[0], null,null,data[1]+'.eml');
	}
	cExecute("$this.exporteml.export_msg",handler_source,"folder="+url_decode(folder)+"&msgs_to_export="+num_msg);
}

function url_encode(str){
	if(str === null) return false;
    var hex_chars = "0123456789ABCDEF";
    var noEncode = /^([a-zA-Z0-9\_\-\.])$/;
    var n, strCode, hex1, hex2, strEncode = "";

    for(n = 0; n < str.length; n++) {
        if (noEncode.test(str.charAt(n))) {
            strEncode += str.charAt(n);
        } else {
            strCode = str.charCodeAt(n);
            hex1 = hex_chars.charAt(Math.floor(strCode / 16));
            hex2 = hex_chars.charAt(strCode % 16);
            strEncode += "%" + (hex1 + hex2);
        }
    }
    
    return strEncode;
}

function url_decode(str) {
	var n, strCode, strDecode = "";
	for (n = 0; n < str.length; n++) {
            strDecode += str.charAt(n);
	    //if (str.charAt(n) == "%") {
	    //    strCode = str.charAt(n + 1) + str.charAt(n + 2);
	    //    strDecode += String.fromCharCode(parseInt(strCode, 16));
	    //    n += 2;
	    //} else {
	    //    strDecode += str.charAt(n);
	    //}
	}
	return strDecode;
}
//Método que remove os hexadecimais criados no enconde
//e retorna string corretamente
function url_decode_s(str) {
	    var result = "";

     for (var i = 0; i < str.length; i++) {
          if (str.charAt(i) == "+") result += " ";
          else result += str.charAt(i);
	}
          return unescape(result);
     
}

function Element (el) {
	return	document.getElementById(el);
}

function getPosition(obj)
{
	if(typeof obj.selectionStart != "undefined")
	{
    	return obj.selectionStart;
	}
	else if(document.selection && document.selection.createRange)
	{
		var M = document.selection.createRange();
		try
		{
			var Lp = M.duplicate();
			Lp.moveToElementText(obj);
		}
		catch(e)
		{
			var Lp=obj.createTextRange();
		}

		Lp.setEndPoint("EndToStart",M);
		var rb=Lp.text.length;

		if(rb > obj.value.length)
		{
			return -1;
		}
		return rb;
	}
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

function increment_folder_unseen(){
	var folder_id = get_current_folder();

	var folder_unseen = Element('dftree_'+folder_id+'_unseen');
	var abas_unseen = Element('new_m').innerHTML;
    abas_unseen = abas_unseen.match(/(<font.*?>){0,1} *([0-9]+) *(<\/font>){0,1}/)[2];

	if (folder_unseen){
		//folder_unseen.innerHTML = (parseInt(folder_unseen.innerHTML) + 1);
		/*Incrementa recursivamente o contador de mensagens*/
		$('.selected').parents().find('> span.folder').not('.inbox').each(function(index,ui){
		   var unseen = $(ui).find('.folder_unseen:last');
		   unseen.html(parseInt(unseen.html(),10) + 1);
		});
	}
	else
	{
        $('span.folder.selected').append('<span>[<label id="dftree_'+folder_id+'_unseen" class="folder_unseen" style="color : red; text-align : left;">1</label>]</span>');
	}

	if( abas_unseen == NaN || abas_unseen == undefined )
		abas_unseen = 1;
	else
		abas_unseen = parseInt(abas_unseen) + 1;

	Element('new_m').innerHTML = '<font style="color:red">' + abas_unseen + '</font>';
	
	if ( current_folder.indexOf( 'INBOX' ) !== 0 && current_folder.indexOf( 'local_' ) !== 0 )
	{
		var display_unseen_in_shared_folders = Element('dftree_user_unseen');
		if ( display_unseen_in_shared_folders )
			tree_folders.getNodeById( 'user' ).alter({caption:'<font style=color:red>[</font><span id="dftree_user_unseen" style="color:red">' + ( parseInt( display_unseen_in_shared_folders.innerHTML) + 1 ) + '</span><font style=color:red>]</font>' + get_lang("Shared folders")});
		else
			tree_folders.getNodeById( 'user' ).alter({caption:'<font style=color:red>[</font><span id="dftree_user_unseen" style="color:red">1</span><font style=color:red>]</font>' + get_lang("Shared folders")});
		tree_folders.getNodeById( 'user' )._refresh();
	}
	var display_unseen_in_mailbox = Element('dftree_root_unseen');
	if(!expresso_offline)
		var node_to_refresh = 'root';
	else
		var node_to_refresh = 'local_root';
	tree_folders.getNodeById( node_to_refresh )._refresh();
}

function decrement_folder_unseen(){
	var folder_id = get_current_folder();

	var folder_unseen = Element('dftree_'+folder_id+'_unseen');
	var abas_unseen = Element('new_m').innerHTML;
    abas_unseen = abas_unseen.match( /(<font.*?>){0,1} *([0-9]+) *(<\/font>){0,1}/)[2];

	if(!folder_unseen || !abas_unseen)
		return;

	if ((folder_unseen) && (parseInt(folder_unseen.innerHTML) > 1))
	{
		//folder_unseen.innerHTML = (parseInt(folder_unseen.innerHTML) - 1);
		/*Decrementa recursivamente o contador de mensagens*/
		$('.selected').parents().find('> span.folder').not('.inbox').each(function(index,ui){
		   var unseen = $(ui).find('.folder_unseen:last');
		   unseen.html(parseInt(unseen.html(),10) - 1);
		});	
	}
	else if (parseInt(folder_unseen.innerHTML) <= 1)
	{
		var tmp_folder_name = tree_folders.getNodeById(folder_id).caption.split('<');
		var folder_name = tmp_folder_name[0];
		tree_folders.getNodeById(folder_id).alter({caption: folder_name});
		tree_folders.getNodeById(folder_id)._refresh();
	}
	if (parseInt(abas_unseen) > 1) {
        Element('new_m').innerHTML = '<font style="color:red">' + (parseInt(abas_unseen) - 1) + '</font>';
	} else {
		Element('new_m').innerHTML = '0';
		$(folder_unseen).parent().empty();
	}
	if ( current_folder.indexOf( 'INBOX' ) !== 0 )
	{
		var display_unseen_in_shared_folders = Element('dftree_user_unseen');
		if ( display_unseen_in_shared_folders )
		{
			var unseen_in_shared_folders = parseInt( display_unseen_in_shared_folders.innerHTML );
			unseen_in_shared_folders--;
			if ( unseen_in_shared_folders > 0 )
				tree_folders.getNodeById( 'user' ).alter({caption:'<font style=color:red>[</font><span id="dftree_root_unseen" style="color:red">' + unseen_in_shared_folders + '</span><font style=color:red>]</font>' + get_lang("My Folders")});
			else
				tree_folders.getNodeById( 'user' ).alter({caption:get_lang("Shared folders")});
			tree_folders.getNodeById( 'user' )._refresh();
		}
	}
	var display_unseen_in_mailbox = Element('dftree_root_unseen');
	if ( display_unseen_in_mailbox )
	{
		var unseen_in_mailbox = parseInt( display_unseen_in_mailbox.innerHTML );
		unseen_in_mailbox--;
		//if ( unseen_in_mailbox > 0 )
		//	tree_folders.getNodeById( 'root' ).alter({caption:'<font style=color:red>[</font><span id="dftree_root_unseen" style="color:red">' + unseen_in_mailbox + '</span><font style=color:red>]</font>' + get_lang("My Folders")});
		//else
		if(!expresso_offline)
			var node_to_refresh = 'root';
		else
			var node_to_refresh = 'local_root';
		tree_folders.getNodeById( node_to_refresh ).alter({caption:get_lang("My Folders")});
		tree_folders.getNodeById( node_to_refresh )._refresh();
	}
}

function LTrim(value){
	var w_space = String.fromCharCode(32);
	var strTemp = "";
	var iTemp = 0;

	var v_length = value ? value.length : 0;
	if(v_length < 1)
		return "";

	while(iTemp < v_length){
		if(value && value.charAt(iTemp) != w_space){
			strTemp = value.substring(iTemp,v_length);
			break;
		}
		iTemp++;
	}
	return strTemp;
}

//changes MENU background color.
function set_menu_bg(menu)
{
	// TODO - remover esta função, por hora, apenas um retrun true para preservar menor impacto
	return true;
	menu.style.backgroundColor = 'white';
	menu.style.border = '1px solid black';
	menu.style.padding = '0px 0px';
}
//changes MENU background color.
function unset_menu_bg(menu)
{
	// TODO - remover esta função, por hora, apenas um retrun true para preservar menor impacto
	return true;
	menu.style.backgroundColor = '';
	menu.style.border = '0px';
	menu.style.padding = '1px 0px';
}

function array_search(needle, haystack) {
	var n = haystack.length;
	for (var i=0; i<n; i++) {
		if (haystack[i]==needle) {
			return true;
		}
	}
	return false;
}

function lang_folder(fn) {
 	if (fn.toUpperCase() == "INBOX") return get_lang("Inbox");
 	if (special_folders[fn] && typeof(special_folders[fn]) == 'string') {
 		return get_lang(special_folders[fn]);
 	}
 	return fn;
}

function add_className(obj, className){
	if (obj && !exist_className(obj, className))
		obj.className = obj.className + ' ' + className;
}

function remove_className(obj, className){
	var re = new RegExp("\\s*"+className);
	if (obj)
		obj.className = obj.className.replace(re, ' ');
}

function exist_className(obj, className){
	return ( obj && obj.className.indexOf(className) != -1 )
}

//Verifica se ainda existem mensagens marcadas, se não desmarca
//o selecionar todas.
function remove_chk_box_select_all_messages(){
	var main = Element("tbody_box");
	var main_list = main.childNodes;
	var len_main_list = main_list.length;
	for (i=0; i<len_main_list; i++)
	{
		if (Element("check_box_message_"+main_list[i].id).checked){
				return;
		}
	}
	 document.getElementById("chk_box_select_all_messages").checked = false;
}

function select_all_messages(select)
{
	var main = Element("tbody_box");
	var main_list = main.childNodes;
	var len_main_list = main_list.length;
	var folder = $('#content_folders .folder.selected').attr('title');
	folder = folder ? folder : get_lang('INBOX');
	folder = folder.length > 70 ? '"'+folder.substr(0,70) + "..." +'"': '"'+folder+'"' ;
	var filterFlag = search_box_type != "ALL" ? '"'+get_lang(search_box_type) + "s" +'"': "";
	var div = $('.select-all-messages');	
	if (select)
	{
		for (i=0; i<len_main_list; i++)
		{
			Element("check_box_message_"+main_list[i].id).checked = true;
			remove_className(Element(main_list[i].id), 'selected_msg');
			if(!$("#"+main_list[i].id).hasClass("selected_shortcut_msg")){
				//add_className(Element(main_list[i].id), 'selected_msg selected_shortcut_msg current_selected');
				add_className(Element(main_list[i].id), 'selected_msg');
			} else {
				$("#"+main_list[i].id).addClass("selected_msg");
				//$("#"+main_list[i].id).addClass("selected_shortcut_msg");
				//$("#"+main_list[i].id).addClass("current_selected");		
			}			
			selectedPagingMsgs[main_list[i].id] = true;
		}		
		if (totalSelected() == totalFolderMsgs && totalSelected() > 0){
			allMsgsSelected = true;
				if (total_pages > 1){
					var link = "<a class='select-link' href='#'>_[[Clear selection?]]</a>";
					var info = "_[[All]] <b>"+totalFolderMsgs+"</b> _[[messages]] "+filterFlag+" _[[in]] "+folder+" _[[were selected.]] "+link;
					div.html("<span>"+info+"<span>");
					//div.show();
					$('.select-link').bind('click',function(){
						selectAllFolderMsgs();
						//$('.select-link').unbind('click');
					});
				}			
		}
		else if (!allMsgsSelected && total_pages > 1){
			var link = "<a class='select-link' href='#'>_[[Select all the]] <b>"+totalFolderMsgs+"</b> _[[messages]] "+filterFlag+" _[[in]] "+folder+"?</a>";
			var info = "_[[All]] <b>"+$('#table_box tr').length+"</b> _[[messages on this page were selected.]] "+link;
			div.html("<span>"+info+"<span>");
			//div.show();
			$('.select-link').bind('click',function(){
				selectAllFolderMsgs(true);
				//$('.select-link').unbind('click');
			});					
		}
	}
	else
	{
		for (i=0; i<len_main_list; i++)
		{
			Element("check_box_message_"+main_list[i].id).checked = false;
			remove_className(Element(main_list[i].id), 'selected_msg selected_shortcut_msg');
			$("#"+main_list[i].id).removeClass("selected_msg");
			$("#"+main_list[i].id).removeClass("current_selected");
			selectedPagingMsgs[main_list[i].id] = false;
		}
		if (allMsgsSelected){
			allMsgsSelected = false;
			updateSelectedMsgs();
		}
		else if (totalSelected() > 0 && total_pages > 1){
			var link = "<a class='select-link' href='#'>_[[Clear selection?]]</a>";
			var info = "_[[Were selected]] <b>"+totalSelected()+"</b> _[[messages]] "+filterFlag+" _[[in]] "+folder+". "+link;
			div.html("<span>"+info+"<span>");
			div.show();
			$('.select-link').bind('click',function(){
				selectAllFolderMsgs(false);
				//$('.select-link').unbind('click');
			});			
		}
		else div.html('<span class="none-selected">_[[No selected message.]]</span>');
	}
	resizeWindow();
}

function borkb(size){
	kbyte = 1024;
	mbyte = kbyte*1024;
	gbyte = mbyte*1024;
	if (!size)
		size = 0;
	if (size < kbyte)
		return size + ' B';
	else if (size < mbyte)
		return parseInt(size/kbyte) + ' KB';
	else if (size < gbyte)
		if (size/mbyte > 100)
			return (size/mbyte).toFixed(0) + ' MB';
		else
			return (size/mbyte).toFixed(1) + ' MB';
	else
		return (size/gbyte).toFixed(1) + ' GB';
}

//valida se a primeira data é menor que a segunda data
function validate_date_order(dateStart, dateEnd){
	if ( parseInt( dateEnd.split( "/" )[2].toString() + dateEnd.split( "/" )[1].toString() + dateEnd.split( "/" )[0].toString() ) >= parseInt( dateStart.split( "/" )[2].toString() + dateStart.split( "/" )[1].toString() + dateStart.split( "/" )[0].toString() ) ){
		return true;
	}else{
		return false;
	}
}

function validate_date(date){
    if (date.match(/^[0-3][0-9]\/[0-1][0-9]\/\d{4,4}$/))
    {
        tmp = date.split('/');

        day = new Number(tmp[0]);
        month = new Number(tmp[1]);
        year = new Number(tmp[2]);
        if (month >= 1 && month <= 12 && day >= 1 && day <= 31)
        {
            if (month == 02 && day <= 29)
            {
                return true;
            }
            return true;
        }
        else
            {
                return false;
            }
    }
    else
        {
            return false;
        }
}

function dateMask(inputData, e){
	if(document.all) // Internet Explorer
		var tecla = event.keyCode;
	else //Outros Browsers
		var tecla = e.which;

	if(tecla >= 47 && tecla < 58){ // numeros de 0 a 9 e "/"
		var data = inputData.value;
		if (data.length == 2 || data.length == 5){
			data += '/';
			inputData.value = data;
		}
	} else {
		if(tecla == 8 || tecla == 0) // Backspace, Delete e setas direcionais(para mover o cursor, apenas para FF)
			return true;
		else
			return false;
	}
}

function translateFolder(folderName){

    for (var i = 0; i < folders.length; i++)
    {
        if (folders[i].folder_parent == 'user'
            && folderName == folders[i].folder_id.split(cyrus_delimiter).pop())
        {
            if (folders[i].folder_id.split(cyrus_delimiter).pop() != folders[i].folder_name)
            {
                return folders[i].folder_name;
            }
        }
    }

    return folderName;
}

function useDesktopNotification(){
    return !!parseInt(preferences.notifications);
}
