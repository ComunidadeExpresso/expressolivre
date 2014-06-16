/**
 * Estrutura que guarda em cache as mensagens abertas, a exemplo da estrutura utilizada
 * na funcionalidade de anexar mensagens.
 * Isto reduz a necessidade de acessar estruturas da tela para obter informações sobre 
 * as mensagens, como tem sido para encaminhar, responder, etc.
 * Manter os dados 'limpos' em uma estrutura 'somente-leitura' permite maior flexibilidade
 * para mudar a tela e menos processamento de limpeza de dados (por exemplo retirar tags de 
 * formatação, etc.) 
 */
 
focusIn = "";
canMakeBox = true;
fastSearch = false;
selectedPagingMsgs = {};
allMsgsSelected = false;
totalFolderMsgs = 0;
var jqXHR = new Array();
var idattachbycontent = 0;

onceOpenedHeadersMessages = {};
var cache = new Array();
var openTab = {
	'type' : [], // 8 types of tabs, further info. see expressolivre.org/dev/wiki/mail/Documentacao
	'content_id' : [],
	'imapUid' : [], // Stores the imap email number of current tab
	'countFile' : [0,0,0,0,0,0,0,0,0,0], // Stores the number of files attached in current tab
	'imapBox' : [], // Stores the folder name
	'toPreserve' : [], // Check if the message should be removed from draft after send
	'condition' : [] // Will store the search condition if the tab is the result of a search
};

var autoSaveControl = {
    'timer' : [], // The timeout timer for autosave function
    'status' : [] // The status autosave 
};

var tabTypes = {
	'new':4,
	'forward':6,
	'reply_with_history':7,
	'reply_to_all_with_history':8,
	'reply_without_history':9,
	'reply_to_all_without_history':10,
	'edit':5
	};

var currentTab,numBox = 0; // Open Tab and num of mailboxes opened at context
// Objeto Map, talvez o ideal fosse adicionar este objeto à Api do egroupware, e carregá-lo
// aqui no expressoMail.

function draw_tree_labels()
{
    console.log("function draw_tree_labels");

    labels = DataLayer.get('label');

    labels = orderLabel( labels );

    if(!$("#MyMarckersList").length)
		var myLabels = $('#content_folders').append("<div id='MyMarckersList' class='acc-list list-label' ></div>").find("#MyMarckersList");
	else
		var myLabels = $("#MyMarckersList");

    myLabels.html("<div class='my-labels' style='background-image: url(../prototype/modules/mail/img/mail-sprites.png); background-position: 0 -1711px; background-repeat: no-repeat;'>" +
				"<a class='title-my-labels' style='margin-left: 15px;' tabindex='0' role='button' aria-expanded='false' title='"+get_lang("My Labels")+"'>"+get_lang("My Labels")+"</a>" +
				"<span class='status-list-labels ui-icon ui-icon-triangle-1-s'></span></div>")
	.append(DataLayer.render("../prototype/modules/mail/templates/label_list.ejs", {labels: labels} ))
	.find("li.label-item").css({"background-color":"#ffffff", "border-color":"#CCCCCC", "color":"#444444"}).click(function(event,ui){
		if($(event.target).is('.square-color')){
				$(this).each(function(){
					configureLabels({selectedItem: $(this).attr('class').match(/label-item-([\d]+[()a-zA-Z]*)/)[1]});
					var id_label_item = $(this).attr('class').match(/label-item-([\d]+[()a-zA-Z]*)/)[1];
					$(".label-list-container .label-list").find(".label-item-"+id_label_item).trigger("click");
				});
		} else {
				var labelId = $(this).attr('class').match(/label-item-([\d]+[()a-zA-Z]*)/)[1];
				search_emails("UNDELETED KEYWORD \"$Label"+labelId+"\"");
		}
	}).find(".square-color").css("display","");

	$("#MyMarckersList a.title-my-labels").click(function() {	
		if($("#MyMarckersList ul.label-list").css("display") == "none"){
			$("#MyMarckersList ul.label-list").show();
		}else{
			$("#MyMarckersList ul.label-list").hide();
		}
		$('#MyMarckersList .status-list-labels').toggleClass("ui-icon-triangle-1-s");
		$('#MyMarckersList .status-list-labels').toggleClass("ui-icon-triangle-1-n");	
	 });
	
	if (!labels){
		$(".my-labels").hide();
	}
}

function force_update_menu(data)
{
    update_menu( data, true );
}

function update_menu(data, forceLoadFolders)
{
	if ( data && data.imap_error )
	{
		connector.newRequest('error.html', 'templates/'+template+'/error.html', 'GET',
			function(data)
			{
				var target = document.getElementById('divAppbox');
				if ( target )
					target.innerHTML = data;
			}
		);
		
        return false;
	}
	else
    {
        draw_quota(data);

        folders = data;
        
        draw_new_tree_folder( false, forceLoadFolders );

        if( preferences['use_followupflags_and_labels'] == "1" ) draw_tree_labels();
    }
}

var handler_draw_box = function(data){
    populateSelectedMsgs(data.messagesIds);
	draw_box(data, 'INBOX', true);
}

// Action on change folders.
function change_folder(folder, folder_name){
	if (parseInt(preferences.use_dynamic_contacts) && $(".to").length && $(".to").data( "autocomplete" ).menu.active){
        $(".to").data( "autocomplete" ).close();
    }
    if (openTab.imapBox[0] != folder)
	{
		selectAllFolderMsgs(false);
		current_folder = folder;
		var handler_draw_box = function(data)
		{
            populateSelectedMsgs(data.messagesIds);
			if(!verify_session(data))
				return;
			alternate_border(0);
			var title = lang_folder(folder_name);
			if (title.length > 18) title = title.substring(0,18) + "...";
			Element("border_id_0").innerHTML = "&nbsp;" + title + '&nbsp;<font face="Verdana" size="1" color="#505050">[<span id="new_m">&nbsp;</span> / <span id="tot_m"></span>]</font>';
			draw_box(data, folder, true);
			draw_paging(data.num_msgs);
			Element("tot_m").innerHTML = data.num_msgs;
			$('#new_m').html(data.tot_unseen > 0 ? data.tot_unseen : "0").css("color","red");
			//$("#new_m").html(($(".selected").find(".folder_unseen").html() != "0" && $(".selected").find(".folder_unseen").html() != null)? $(".selected").find(".folder_unseen").html() : "0").css("color", "red");
			//update_menu();
			$(".folders-loading").removeClass("folders-loading");
			return true;
		}

		//MAILARCHIVE
        //se for pasta local
        if (/^local_messages/.test(current_folder)) {
            $(".folders-loading").removeClass("folders-loading"); //remove o icone de loading ao clicar nas pastas locais
        }
		proxy_mensagens.messages_list(current_folder,1,preferences.max_email_per_page,sort_box_type,search_box_type,sort_box_reverse,preferences.preview_msg_subject,preferences.preview_msg_tip,handler_draw_box);
	}
	else{
		$(".folders-loading").removeClass("folders-loading");
		alternate_border(0);
	}
}

function open_folder(folder, folder_name)
{
	if( current_folder!= folder )
    {
		current_folder = folder;
		var handler_draw_box = function(data)
        {
			if(!verify_session(data)) return false;

			numBox++;
			
            create_border(folder_name,numBox.toString());
			
            draw_box(data, current_folder, false);
			
            alternate_border(numBox);
			
            return true;
		}
		cExecute ("$this.imap_functions.get_range_msgs2&folder="+current_folder+"&msg_range_begin=1&msg_range_end="+preferences.max_email_per_page+"&sort_box_type="+sort_box_type+ "&search_box_type="+ search_box_type +"&sort_box_reverse="+sort_box_reverse+"", handler_draw_box);
	}
	else
    {
		alternate_border(numBox);
    }

	return true;
}

var lastPage = 1;
var numPages = 5;
var last_folder = 'INBOX';
function draw_paging(num_msgs){
	num_msgs = parseInt(num_msgs);
	total_pages = 1;

	if(last_folder != current_folder){
		lastPage = 1;
		current_page = 1;
		last_folder = current_folder;
  	}

	if(num_msgs > parseInt(preferences.max_email_per_page)) {
		total_pages = parseInt(num_msgs/preferences.max_email_per_page);
		if((num_msgs/preferences.max_email_per_page) > total_pages)
			total_pages++;
	}

	if(total_pages == 1) {
		if(span_paging = document.getElementById("span_paging")) {
			span_paging.parentNode.removeChild(span_paging);
		}
		return;
	}
  	span_paging = document.getElementById("span_paging");
	if(!span_paging){
		span_paging = document.createElement("DIV");
		span_paging.id = "span_paging";
		span_paging.className = "boxHeaderText";
		span_paging.align="right";
		document.getElementById("div_menu_c3").appendChild(span_paging);

        span_select_all_message = document.getElementById("span_paging");
        if($('.select-all-messages').length == 0)
            drawSelectMsgsTable();
        else
            $('.select-all-messages').show();
	}
	span_paging.style.width="100%";
  	span_paging.innerHTML="";
  	msg_range_begin = 1;
	msg_range_end = preferences.max_email_per_page;
  	if(current_page != 1)
    {
	  	lnk_page = document.createElement("A");
		lnk_page.setAttribute("href", "javascript:current_page=1; draw_paging("+num_msgs+"); proxy_mensagens.messages_list(get_current_folder(),"+msg_range_begin+","+msg_range_end+",'"+sort_box_type+"','"+search_box_type+"',"+sort_box_reverse+","+preferences.preview_msg_subject+","+preferences.preview_msg_tip+",function handler(data){alternate_border(0); draw_box(data, get_current_folder());});");
  	}
  	else
    {
  	 	lnk_page = document.createElement("SPAN");
  	}
  	
    span_paging.appendChild(lnk_page);

  	lnk_page.innerHTML = "&lt;&lt;";
	lnk_page.title = get_lang("First");
  	span_paging.innerHTML += "&nbsp;";

  	if(current_page == lastPage + numPages)
  		lastPage = current_page - 1;
  	else if((lastPage != 1 && lastPage == current_page) || current_page == total_pages)
  		lastPage = current_page - (numPages - 1);
  	else if(current_page == 1)
  	 	lastPage = 1;

	if(lastPage < 1)
		lastPage = 1;
	else if(lastPage > 1 && (lastPage > (total_pages -(numPages - 1))))
		lastPage = total_pages -(numPages - 1);

	var	hasMarked = false;

  	for(i = lastPage; i <= total_pages; i++) {

  		if(current_page == i || (i == total_pages && !hasMarked)) {
  			lnk_page = document.createElement("SPAN");			
  			span_paging.appendChild(lnk_page);
			lnk_page.style.color = "red";			
  			lnk_page.innerHTML = "&nbsp;<b>"+i+"</b>&nbsp;";
  			hasMarked = true;
  			continue;
  		}
  		else{
  			lnk_page = document.createElement("A");
  			span_paging.appendChild(lnk_page);
  			msg_range_begin = ((i*preferences.max_email_per_page)-(preferences.max_email_per_page-1));
			msg_range_end = (i*preferences.max_email_per_page);
			lnk_page.setAttribute("href", "javascript:current_page="+i+"; draw_paging("+num_msgs+"); proxy_mensagens.messages_list(get_current_folder(),"+msg_range_begin+","+msg_range_end+",'"+sort_box_type+"','"+search_box_type+"',"+sort_box_reverse+","+preferences.preview_msg_subject+","+preferences.preview_msg_tip+",function handler(data){alternate_border(0); draw_box(data, get_current_folder());});");
  		}
  		lnk_page.innerHTML = "&nbsp;...&nbsp;";
  		if(i == (lastPage + numPages))
  				break;
  		else if(lastPage == 1 || i != lastPage)
  			lnk_page.innerHTML = "&nbsp;"+i+"&nbsp;";
  		span_paging.innerHTML += "&nbsp;";
  	}

 	if(current_page != total_pages)
    {
  		lnk_page = document.createElement("A");
  		msg_range_begin = ((total_pages*preferences.max_email_per_page)-(preferences.max_email_per_page-1));
		msg_range_end = (total_pages*preferences.max_email_per_page);
		lnk_page.setAttribute("href", "javascript:current_page="+total_pages+"; draw_paging("+num_msgs+"); proxy_mensagens.messages_list(get_current_folder(),"+msg_range_begin+","+msg_range_end+",'"+sort_box_type+"','"+search_box_type+"',"+sort_box_reverse+","+preferences.preview_msg_subject+","+preferences.preview_msg_tip+",function handler(data){alternate_border(0); draw_box(data, get_current_folder());});");
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

// stores the percentages of the cells
var colSizes = [ ["3%","2%","1%","1%","1%","1%","2%","2%","2%","20%","45%","11%","11%"], ["2%","1%","1%","1%","1%","1%","1%","20%","20%","33%","10%","9%"] ];
// stores the alignments of the cells
var colAligns = [ ['','','','','','','','left','left','center','center'], ['','','','','','left','left','left','center','center'] ];
var objCell = null;
var adjCell = null;
var cellClick = null;
var cellResized = false;
var maxWidth=0;
var minWidth=0;
var bodyWidth=0;
var cssForResizing=false;

function changeCursorState(ev) {
	if (cellResized)
		cellResized = false;
	var el = ev.target||ev.srcElement;
	if (el.tagName != "TD")
		el=getFirstParentOfType(el, "TD")
	el.style.cursor ="hand";
	el.style.cursor ="pointer";
	if (!cssForResizing)
		return;
	var cellMouseX = (ev.clientX-parseInt(getColumnPosition(el, ev)));
	var cellWidth = el.offsetWidth;
	var tbl = getFirstParentOfType(el,"TABLE");
	if (tbl.emptyBody)
		return;
	if ((((cellWidth-cellMouseX)<3)||((el.style.cursor=="col-resize")&&(detectLeftButton())))&&((el.className=="th_resizable")&&(el.cellIndex+1<el.parentNode.cells.length))){
		if (tbl) tbl.style.cursor = "col-resize";
		el.style.cursor ="col-resize";
	}
	else{
		if (tbl) tbl.style.cursor = "pointer";
		el.style.cursor ="hand";
		el.style.cursor ="pointer";
	}
}

function getStyle(el,styleProp) {
	var x = document.getElementById(el);
	if (x.currentStyle)
		var y = x.currentStyle[styleProp];
	else if (window.getComputedStyle)
		var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
	return y;
}

function resizeColumn(ev) {
	if (!objCell || !adjCell) {
		stopColResize(ev);
		return;
	}
	if (document.getElementById("table_message_header_box_"+numBox).emptyBody)
		return;
    objCell.style.cursor = "col-resize";
    $(objCell).next().css('cursor','col-resize');

	if (objCell.style.cursor == "col-resize" && detectLeftButton(ev)) {
		var cellIndex = objCell.cellIndex;
		var adjIndex = adjCell.cellIndex;
		var cellOffset = parseInt(getColumnPosition(objCell, ev));
		var cellWidth = (ev.clientX-cellOffset);

		var tbl = getMessagesTable(objCell);
		var trM = tbl.rows;
		var tblType = ( (tbl.id=="table_box") ? 0 : 1);

		if (cellWidth < minWidth)
			cellWidth = minWidth;
		var adjWidth = maxWidth - cellWidth;
		if (adjWidth < minWidth) {
			cellWidth = (cellWidth+adjWidth)-minWidth;
			adjWidth = minWidth;
		}

		colSizes[tblType][adjIndex] = (colSizes[tblType][adjIndex]=="*" ? "*" : getPct(is_ie ? adjWidth : adjWidth-2));
		colSizes[tblType][cellIndex] = (colSizes[tblType][cellIndex]=="*" ? "*" : getPct(cellWidth));

		document.getElementById("table_message_ruler").style.left = cellOffset+cellWidth;
        syncColumns();
	}
	ev.returnValue = false;
	return (false);
}

function getPct(tdWidth) {
	var suf = tdWidth.toString().substr(tdWidth.length-1,1);
	if ( suf != "%" && suf != "*")
		return (Math.round((tdWidth/bodyWidth)*10000)/100).toString()+"%";
	else
		return tdWidth;
}

function detectLeftButton(ev) {
    ev = ev || window.event;
    var button = ev.which || ev.button;
    return (button == 1);
}

function startColResize(ev) {
	var el = ev.target||ev.srcElement;
	if (el.tagName != "TD")
		el=getFirstParentOfType(el, "TD")
	if (is_ie)
		ev.cancelBubble = true;
	else
		ev.stopPropagation();
	if (!el) return;
	if (!is_ie) ev.preventDefault();
	if (document.getElementById("table_message_header_box_"+numBox).emptyBody)
		return;
	if (el.style.cursor == "col-resize" && detectLeftButton(ev)) {
		var incIndex = ( (el.parentNode.cells.length > el.cellIndex + 1) ? 1 : -1 );
		var msgTable = getMessagesTable(el);
		var bodyRows = msgTable.rows;
		objCell = el;
		adjCell = el.parentNode.cells[el.cellIndex+incIndex];
		var tbl = getFirstParentOfType(el, "TABLE");
		if (is_ie) {
			tbl.detachEvent("onmousemove",changeCursorState);
			document.attachEvent("onmousemove",resizeColumn);
			document.attachEvent("onmouseup",stopColResize);
			document.attachEvent("onselect",selectCancel);
			tbl.attachEvent("onselectstart",selectCancel);
		}
		else {
			tbl.removeEventListener("mousemove",changeCursorState,false);
			document.addEventListener("mousemove",resizeColumn,false);
			document.addEventListener("mouseup",stopColResize,false);
			document.addEventListener("select",selectCancel,false);
			tbl.addEventListener("selectstart",selectCancel,false);
		}
		cellClick = objCell.onclick;
		objCell.onclick = "";
		maxWidth = objCell.offsetWidth+adjCell.offsetWidth;
			bodyWidth = msgTable.offsetWidth;
		minWidth = Math.round(bodyWidth/80);
		document.getElementById("table_message_ruler").style.display = "block";
		document.getElementById("table_message_ruler").style.left = ev.clientX;
		if (document.getElementById("content_id_"+numBox)==null)
			document.getElementById("table_message_ruler").style.height = document.getElementById("content_id_"+currentTab).offsetHeight;
		else
			document.getElementById("table_message_ruler").style.height = document.getElementById("content_id_"+numBox).offsetHeight;
	}
}

function stopColResize(ev) {
	if (!objCell)
		return;
	if (is_ie)
		ev.cancelBubble = true;
	else
		ev.stopPropagation();
	var tbl = getFirstParentOfType(objCell, "TABLE");
	if (is_ie) {
		tbl.detachEvent("onselectstart",selectCancel);
		document.detachEvent("onselect",selectCancel);
		document.detachEvent("onmousemove",resizeColumn);
		document.detachEvent("onmouseup",stopColResize);
		tbl.attachEvent("onmousemove",changeCursorState);
	}
	else {
		tbl.removeEventListener("selectstart",selectCancel,false);
		document.removeEventListener("select",selectCancel,false);
		document.removeEventListener("mousemove",resizeColumn,false);
		document.removeEventListener("mouseup",stopColResize,false);
		tbl.addEventListener("mousemove",changeCursorState,false);
	}
	var trM = getMessagesTable(objCell).rows;
	var adjIndex = adjCell.cellIndex;
	document.getElementById("table_message_ruler").style.display = "none";
	maxWidth = 0;
	objCell.onclick = cellClick;
	objCell = null;
	adjCell = null;
	cellResized = true;
	setColSizesCookie();
	syncColumns();
}

function getMessagesTable(el) {
	var hT;
	if (el.tagName == "TABLE")
		hT = el;
	else
		hT = getFirstParentOfType(el,"TABLE");
	return (hT.parentNode.nextSibling.childNodes[0].className !== "local-messages-search-warning"?
		hT.parentNode.nextSibling.childNodes[0] : hT.parentNode.nextSibling.childNodes[1]);
}

function getColumnPosition(oNode, pNode){
	if (!pNode && !is_webkit){
		var pos = getOffset(oNode);
		return [pos.left, pos.top];
	}
	pNode = pNode||document.body;

	var oCurrentNode = oNode;
	var iLeft = 0;
	var iTop = 0;

	while ((oCurrentNode)&&(oCurrentNode != pNode)){
		iLeft+=oCurrentNode.offsetLeft-oCurrentNode.scrollLeft;
		iTop+=oCurrentNode.offsetTop-oCurrentNode.scrollTop;
		oCurrentNode=oCurrentNode.offsetParent;
	}

	return [iLeft, iTop];
}

function getFirstParentOfType(obj, tag){
	while (obj&&obj.tagName != tag&&obj.tagName != "BODY"){
		obj=obj.parentNode;
	}
	return obj;
}

function syncColumns() {
	var thisCell;
	var tbl = (document.getElementById("content_id_"+numBox)==null ? 1 : 0);
	if (objCell)
		thisCell = objCell;
	else
		thisCell = document.getElementById("table_message_header_box_"+numBox).rows[0].cells[0];
	var emptyBody = document.getElementById("table_message_header_box_"+numBox).emptyBody;
	var tbH = getFirstParentOfType(thisCell,"TABLE");
	var tbM = getMessagesTable(thisCell);
	var trM = tbM.rows;
	var _mouse_over;
	var _mouse_out;
	var _cell;
	for (var r=0;r<trM.length;r++) {
		for (var c=0;c<trM[r].cells.length;c++) {
			_cell = trM[r].cells[c];
			_cell.setAttribute("width",colSizes[tbl][c]);
			if (is_mozilla && !is_webkit) {
				if (!emptyBody) {
					document.getElementById("colgroup_main_"+numBox).childNodes[c].setAttribute("width",colSizes[tbl][c]);
				}
				document.getElementById("colgroup_head_"+numBox).childNodes[c].setAttribute("width",colSizes[tbl][c]);
				if (tbH.rows[0].cells[c].className=="th_resizable") {
					// lots of stupid fixes for FF to redraw cell content
					if (_cell.childNodes && _cell.childNodes.length>1) {
						_mouse_over = _cell.childNodes[1].onmouseover;
						_mouse_out = _cell.childNodes[1].onmouseout;
					}
					_cell.innerHTML = trM[r].cells[c].innerHTML;
					if (_cell.childNodes && _cell.childNodes.length>1) {
						_cell.childNodes[1].onmouseover = _mouse_over;
						_cell.childNodes[1].onmouseout = _mouse_out;
					}
					tbH.rows[0].cells[c].innerHTML = tbH.rows[0].cells[c].innerHTML;
				}
			}
		}
	}
	 
    //bug do firefox ao redefinir os tamanhos das colunas
	if (is_mozilla && !is_webkit)
    {  
	    if(trM[r])
	    for (var c=0;c<trM[r].cells.length;c++) 
		document.getElementById("colgroup_main_"+numBox).childNodes[c].setAttribute("width",colSizes[tbl][c]);

	}
	if (is_webkit || is_ie){
			var arrHeader = $('.message_header td');
			var arrBody = $('#tbody_box :first td');
			for(i=0;i<arrHeader.length;i++){
					if(arrBody[i] !== undefined && arrBody[i].width !== undefined)
					arrHeader[i].width = arrBody[i].width;
			}			
	}
}

function resizeMailList() {
	if (document.getElementById("table_message_header_box_"+numBox)==null)
		return false;
	var innerWidth = (window.innerWidth?window.innerWidth:document.body.clientWidth);
	var scrollWidth = (innerWidth - 20 - getColumnPosition(Element("exmail_main_body"),"BODY")[0]);
	document.getElementById("table_message_header_box_"+numBox).style.width = (scrollWidth-2)+'px';
	if (document.getElementById("table_resultsearch_"+numBox)==null)
		document.getElementById("table_box").style.width = scrollWidth+'px';
	else
		document.getElementById("table_resultsearch_"+numBox).style.width = scrollWidth+'px';
	syncColumns();
	//Alinhamento das colunas data e tamanho na pesquisa
	if ( numBox > 0){
		if (is_mozilla && !is_webkit){
			$('#table_message_header_box_'+numBox).attr('style','width:99.5%');
			$('#colgroup_head_'+numBox).find('col').each(function(index,value){
				 $(this).attr('width',colSizes[1][index]);
			});
			$('#colgroup_main_'+numBox).find('col').each(function(index,value){
				 $(this).attr('width',colSizes[1][index]);
			});	
		}	
		else if (is_webkit){
			$('#table_message_header_box_'+numBox).removeAttr('style');
			$('#table_message_header_box_'+numBox).css('table-layout','auto');
		}
		else{
			$('#table_message_header_box_'+numBox).css('table-layout','auto');
		}
		$('#table_message_header_box_'+numBox).find('td').each(function(index,value){
		     $(this).width(colSizes[1][index]);
		});
		$('#divScrollMain_'+numBox).find('tr:first').find('td').each(function(index,value){
		     $(this).width(colSizes[1][index]);
		});			
	}
	$('#table_resultsearch_'+numBox).removeAttr('style');
}

function selectCancel(ev) {
	return (false);
}

function buildColGroup(tbl) {
	var col_element;
	var colgr_element = document.createElement("COLGROUP");
	if (tbl==null) tbl = 0;
	for (i=0;i<colSizes[tbl].length;i++) {
		col_element = document.createElement("COL");
		col_element.setAttribute("align", colAligns[tbl][i]);
		colgr_element.appendChild(col_element);
	}
	return (colgr_element);
}

function createTDElement(table_list,col_index,class_name,td_align,td_id) {
	var td_element = document.createElement("TD");
	td_element.setAttribute("width",colSizes[table_list][col_index]);
	if (class_name) td_element.className = class_name;
	if (td_align) td_element.align = td_align;
	if (td_id) td_element.id = td_id;
	return (td_element);
}

function prepareColSizesArray() {
	var colSizesCookie = getColSizesCookie();
	if (colSizesCookie) {
		try {
			var colSizesA = colSizesCookie.split(":");
			colSizes = [colSizesA[0].split(","),colSizesA[1].split(",")];
		}
		catch(e) {}
	}
}

function setColSizesCookie() {
	var str = "maillist_colsizes=" + colSizes[0].join() + ":" + colSizes[1].join();
	var date = new Date();
	date.setTime(date.getTime()+(365*24*60*60*1000));
	str += ("; expires="+date.toGMTString());
	document.cookie = str;
}

function getColSizesCookie() {
	var search = "maillist_colsizes=";
	if (document.cookie.length > 0) {
		var offset = document.cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			var end = document.cookie.indexOf(";", offset);
			if (end == -1)
				end = document.cookie.length;
			return document.cookie.substring(offset, end);
		}
	}
	return null;
}


/*Cria a div que permite a seleção de todas as mensagens*/
function drawSelectMsgsTable(){
	var div = $('<div>');	
	div.html('<span class="none-selected">_[[No selected message.]]</span>');
	div.attr('class','select-all-messages'); 
	$('#content_id_0').first().prepend(div);
}

function totalSelected(){
	var total = 0;
	for(var obj in selectedPagingMsgs)
		if (selectedPagingMsgs[obj] == true)
			total++;
	return total;
};

/*Atualiza o array de mensagens selecionadas*/
function updateSelectedMsgs(selected,msg_number){ 
	var folder = $('#content_folders .folder.selected').attr('title');
	folder = folder ? folder : get_lang('INBOX');
	folder = folder.length > 70 ? '"'+folder.substr(0,70) + "..." +'"': '"'+folder+'"' ;
	var div = $('.select-all-messages');
	var filterFlag = search_box_type != "ALL" ? '"' + get_lang(search_box_type) + "s" + '"': "";
	/*Seleciona as mensagens ao navegar pelas páginas*/
	if (allMsgsSelected && msg_number == undefined){
		$('.checkbox').each(function(){
			$(this).attr('checked', true);
			$(this).parent().parent().addClass('selected_msg');
		});
	}
	else if (msg_number == undefined){	
		for(var obj in selectedPagingMsgs){
			if (selectedPagingMsgs[obj] == true){
				$('#check_box_message_'+obj).attr('checked', true);
				$('#check_box_message_'+obj).parent().parent().addClass('selected_msg');
			}
		}
	}
	if (selected && msg_number != undefined){
		selectedPagingMsgs[msg_number] = true;
		$(this).parent().parent().addClass('selected_msg');		
	}
	else if(!selected && msg_number != undefined){
		selectedPagingMsgs[msg_number] = false;
		$(this).parent().parent().removeClass('selected_msg');
		allMsgsSelected = false;
		$('#chk_box_select_all_messages').attr('checked',false);
	}
	var tSelected = totalSelected();
	/*Todas as mensagens selecionadas uma a uma*/
	if (tSelected > 0 && tSelected == totalFolderMsgs){
		allMsgsSelected = true;
		$('#chk_box_select_all_messages').attr('checked',true);
		if (total_pages > 1){
			var link = "<a class='select-link' href='#'>_[[Clear selection?]]</a>";
			var info = "_[[All]] <b>"+totalFolderMsgs+"</b> _[[messages]] "+filterFlag+" _[[in]] "+folder+" _[[were selected.}} "+link;
			div.html("<span>"+info+"<span>");
			div.show();
			$('.select-link').bind('click',function(){selectAllFolderMsgs();$('.select-link').unbind('click');});
		}
	}
	/*Se foram selecionadas algumas mensagens*/
	else if (tSelected > 0 && !allMsgsSelected && total_pages > 1){
		$('#chk_box_select_all_messages').attr('checked',false);
		var link = "<a class='select-link' href='#'>_[[Clear selection?]]</a>";
		if (tSelected == 1){
			var info = "_[[Was selected]] <b>"+tSelected+"</b> _[[messages]] "+filterFlag+" _[[in]] "+folder+". "+link;
		}
		else{
			var info = "_[[Were selected]] <b>"+tSelected+"</b> _[[messages]] "+filterFlag+" _[[in]] "+folder+". "+link;
		}
		div.html("<span>"+info+"<span>");
		div.show();
		$('.select-link').bind('click',function(){
            selectAllFolderMsgs();
            $('.select-link').unbind('click');
        });		
	}
	else if (allMsgsSelected && total_pages > 1){
		var link = "<a class='select-link' href='#'>_[[Clear selection?]]</a>";
		var info = "_[[All]] <b>"+totalFolderMsgs+"</b> _[[messages]] "+filterFlag+" em "+folder+" _[[were selected.]] "+link;
		div.html("<span>"+info+"<span>");
		div.show();
		$('.select-link').bind('click',function(){
            selectAllFolderMsgs();
            $('.select-link').unbind('click');
        });
	}
	else if (totalFolderMsgs > parseInt(preferences.max_email_per_page)){
		div.html('<span class="none-selected">_[[No selected message.]]</span>');
	}
	else{
		div.hide();
	}  
    resizeWindow();
}

/*Seleciona ou desseleciona todas as mensagens da pasta*/
function selectAllFolderMsgs(select){
	var folder = $('#content_folders .folder.selected').attr('title');
	folder = folder ? folder : get_lang('INBOX');
	folder = folder.length > 70 ? '"'+folder.substr(0,70) + "..." +'"': '"'+folder+'"' ;
	var filterFlag = search_box_type != "ALL" ? get_lang(search_box_type) + "s": "";
	var div = $('.select-all-messages');
	if (select){
		allMsgsSelected = true;
		var link = "<a class='select-link' href='#'>_[[Clear selection?]]</a>";
		var info = "_[[All]] <b>"+totalFolderMsgs+"</b> _[[messages]] "+filterFlag+" em "+folder+" _[[were selected.]] "+link;
		div.html("<span>"+info+"<span>");
		//div.show();
		$('.select-link').bind('click',function(){
            selectAllFolderMsgs();
            $('.select-link').unbind('click');
        });
		for(var obj in selectedPagingMsgs){
			selectedPagingMsgs[obj] = true;
		}
	
	}else{
		allMsgsSelected = false;
		var checkbox = $("#content_id_0").find("input:checkbox"); 
        $.each(checkbox, function(i, v){ 
            $(v).removeAttr("checked"); 
            $(v).parents("tr:first").removeClass("selected_msg selected_shortcut_msg"); 
        }); 
		for(var obj in selectedPagingMsgs){
			selectedPagingMsgs[obj] = false;
		}
		div.html('<span class="none-selected">_[[No selected message.]]</span>');
	}
    resizeWindow();
}

/*Carrega o array de mensagens da pasta*/
function populateSelectedMsgs(data){
	this.selectedPagingMsgs = {};
	this.totalFolderMsgs = 0;
	var total = 0;
	$.each(data, function(index, value){
		if(value != undefined){
			selectedPagingMsgs[value] = false;
			total++;
		}
	});
	delete selectedPagingMsgs[undefined];
	allMsgsSelected = false;
	this.totalFolderMsgs = total;
}

// Draw the inbox and another folders
function draw_box(headers_msgs, msg_folder, alternate){
	/*
	 * When the paging response is not in the correct folder you need to change folder
	 * This occurs when the Ajax response is not fast enough and the user click in outher
	 * folder before finishing the Ajax request
	 */
	if (msg_folder != headers_msgs['folder']) {
		if (headers_msgs['folder']) {
			array_folder = headers_msgs['folder'].split('/');

			if (array_folder.length > 1) {
				name_folder = array_folder[1];
			}
			else {
				name_folder = headers_msgs['folder'];
			}
			current_folder = headers_msgs['folder'];
			Element("border_id_0").innerHTML = "&nbsp;" + lang_folder(name_folder) + '&nbsp;<font face="Verdana" size="1" color="#505050">[<span id="new_m">&nbsp;</span> / <span id="tot_m"></span>]</font>';

			Element('new_m').innerHTML = headers_msgs['tot_unseen'] ? '<font color="RED">'+headers_msgs['tot_unseen']+'</font>' : 0;
			Element("tot_m").innerHTML = headers_msgs['num_msgs'];

			tree_folders.getNodeById(headers_msgs['folder'])._select();
		}
	}
	/**
	 * Preenche a estrutura de cache de mensagens para posterior consulta de 
	 * informações sobre as mensagens no escopo global.
	 */
	for (var i=0; i<headers_msgs.length; i++) { 
		if (!onceOpenedHeadersMessages[current_folder])
			onceOpenedHeadersMessages[current_folder] = {};
		onceOpenedHeadersMessages[current_folder][headers_msgs[i].msg_number] = headers_msgs[i];
	}

	if (alternate)
		kill_current_box();

	if(is_ie)
		document.getElementById("border_table").width = "99.5%";

	numBox = 0; //As pastas sempre estarão na aba 0
	
	openTab.content_id[numBox] = document.getElementById("content_id_"+numBox);
	openTab.content_id[numBox].innerHTML = "";
	openTab.imapBox[numBox] = msg_folder;
	openTab.type[numBox] = 0;

	table_message_header_box = document.getElementById("table_message_header_box_"+numBox);
	if (table_message_header_box == null) {
		var table_element = document.createElement("TABLE");
		var colgr_element = buildColGroup();
		colgr_element.setAttribute("id","colgroup_head_"+numBox);
		var tbody_element = document.createElement("TBODY");
		//add events for column resizing
        $(table_element).mousemove(changeCursorState);
        $(table_element).mousedown(startColResize);

		table_element.setAttribute("id", "table_message_header_box_"+numBox);
		table_element.className = "table_message_header_box";
		if (!is_ie)
			table_element.style.width = "98.8%";
		//if table is empty
		table_element.emptyBody = false;

		tr_element = document.createElement("TR");
		tr_element.className = "message_header";

		td_element1 = createTDElement(0,0);
		chk_box_element = document.createElement("INPUT");
		chk_box_element.id  = "chk_box_select_all_messages";
		chk_box_element.setAttribute("type", "checkbox");
		chk_box_element.className = "checkbox";
		chk_box_element.onclick = function(){select_all_messages(this.checked);};
		chk_box_element.onmouseover = function () {this.title=get_lang('Select all messages from this page.'); };
		chk_box_element.onkeydown = function (e){
			if (is_ie)
			{
				if ((window.event.keyCode) == 46)
					proxy_mensagens.delete_msgs(get_current_folder(),'selected','null');
			}
			else
			{
				if ((e.keyCode) == 46)
					proxy_mensagens.delete_msgs(get_current_folder(),'selected','null');
			}
		};

		td_element1.appendChild(chk_box_element);

		td_element2 = createTDElement(0,1);
		td_element3 = createTDElement(0,8,"th_resizable","left","message_header_SORTFROM_"+numBox);
		td_element3.onclick = function () {sort_box(search_box_type,'SORTFROM');};
		folder_ = special_folders['Sent'];
		current_ = get_current_folder();
		if ((preferences.from_to_sent == "1") && (current_.substr(current_.length - folder_.length, folder_.length) == folder_)) {
        	td_element3.innerHTML = get_lang("To");
    	}else{
			td_element3.innerHTML = get_lang("From");
    	}

		td_element4 = createTDElement(0,9,"th_resizable","left","message_header_SORTSUBJECT_"+numBox);
		td_element4.onclick = function () {sort_box(search_box_type,'SORTSUBJECT');};
		td_element4.innerHTML = get_lang("Subject");

		td_element5 = createTDElement(0,10,"th_resizable","center","message_header_SORTARRIVAL_"+numBox);
		td_element5.onclick = function () {sort_box(search_box_type,'SORTARRIVAL');};
		td_element5.innerHTML = get_lang("Date");

		td_element6 = createTDElement(0,11,"th_resizable","center","message_header_SORTSIZE_"+numBox);
		td_element6.onclick = function () {sort_box(search_box_type,'SORTSIZE');}
		td_element6.innerHTML = get_lang("Size");

		tr_element.appendChild(td_element1);
		tr_element.appendChild(td_element2);

		var td_element21 = createTDElement(0,2);
		td_element21.innerHTML = "&nbsp;";

		var td_element22 = createTDElement(0,3);
		td_element22.innerHTML = "&nbsp;";

		var td_element23 = createTDElement(0,4);
		td_element23.innerHTML = "&nbsp;";

		var td_element24 = createTDElement(0,5);
		td_element24.innerHTML = "&nbsp;";
			
		
		var td_element25 = createTDElement(0,7);
		td_element25.innerHTML = "&nbsp;";
		
		var td_element27 = createTDElement(0,8);
		td_element25.innerHTML = "&nbsp;";
		
		var td_element26 = createTDElement(0,6);
		td_element26.innerHTML = "&nbsp;";
		

		tr_element.appendChild(td_element21);
		tr_element.appendChild(td_element22);
		tr_element.appendChild(td_element23);
		tr_element.appendChild(td_element24);
		tr_element.appendChild(td_element26);
		tr_element.appendChild(td_element27);
		tr_element.appendChild(td_element25);
		
		tr_element.appendChild(td_element3);
		tr_element.appendChild(td_element4);
		tr_element.appendChild(td_element5);
		tr_element.appendChild(td_element6);

		tbody_element.appendChild(tr_element);
		table_element.appendChild(tbody_element);
		table_element.appendChild(colgr_element);

		var _divScroll = document.getElementById("divScrollHead_"+numBox);

		if(!_divScroll){
			_divScroll = document.createElement("DIV");
			_divScroll.id = "divScrollHead_"+numBox;
		}

		_divScroll.style.overflowY = "hidden";
		_divScroll.style.overflowX = "hidden";
		_divScroll.style.width	="100%";

		if (is_mozilla){
			_divScroll.style.width	="99.3%";
		}

		_divScroll.appendChild(table_element);
		openTab.content_id[numBox].appendChild(_divScroll);

		var table_layout = (getStyle("table_message_header_box_"+numBox,"table-layout") || getStyle("table_message_header_box_"+numBox,"tableLayout"));
		cssForResizing = (table_layout=="fixed");
		if (cssForResizing)
			prepareColSizesArray();
	}
	draw_header_box();
	var table_element = document.createElement("TABLE");
	var colgr_element = buildColGroup();
	colgr_element.setAttribute("id","colgroup_main_"+numBox);

	var tbody_element = document.createElement("TBODY");
	table_element.id = "table_box";
	table_element.className = "table_box";
	table_element.borderColorDark = "#bbbbbb";
	table_element.frame = "void";
	table_element.rules = "rows";
	table_element.cellPadding = "0";
	table_element.cellSpacing = "0";

	if (is_ie)
		table_element.style.cursor = "hand";

	tbody_element.setAttribute("id", "tbody_box");
	table_element.appendChild(tbody_element);
	table_element.appendChild(colgr_element);

	var _divScroll = document.getElementById("divScrollMain_"+numBox);

	if(!_divScroll){
		_divScroll = document.createElement("DIV");
		_divScroll.id = "divScrollMain_"+numBox;
	}

	_divScroll.style.overflowY = "scroll";
	_divScroll.style.overflowX = "hidden";
	_divScroll.style.width	="100%";

	if (is_mozilla){
		_divScroll.style.overflow = "-moz-scrollbars-vertical";
		_divScroll.style.width	="100%";
	}
	_divScroll.appendChild(table_element);
	openTab.content_id[numBox].appendChild(_divScroll);
	
	var f_unseen = 0;

	document.getElementById("table_message_header_box_"+numBox).emptyBody = false;
	
	if (headers_msgs.num_msgs == 0)
		showEmptyBoxMsg(tbody_element);

	for (var i=0; i < headers_msgs.length; i++){
			if ((headers_msgs[i].Unseen == 'U') || (headers_msgs[i].Recent == 'N'))
				f_unseen++;
                        tr_element = make_tr_message(headers_msgs[i], msg_folder, headers_msgs.offsetToGMT);
			if (tr_element){
				tbody_element.appendChild(tr_element);
				add_className(tr_element, i%2 != 0 ? 'tr_msg_read2' : 'tr_msg_read');
			}
			//_dragArea.makeDragged(tr_element, headers_msgs[i].msg_number, headers_msgs[i].subject, true);
			$(tr_element).draggable({
				start : function(){
                    $('.upper, .lower').show();
                    $(".lower").css("top", ($("#content_folders").height()-18) + $("#content_folders").offset().top);
					if($(".shared-folders").length){
						$(".shared-folders,.head_folder").parent().find('.folder:not(".shared-folders")').droppable({
							over : function(a, b){						
								//SETA BORDA EM VOLTA DA PASTA
                                $(b.helper).find(".draggin-folder,.draggin-mail").css("color", "green");
								over = $(this);
								$(this).addClass("folder-over");
								if(($(this)[0] != $(this).parent().find(".head_folder")[0]))
									if($(this).prev()[0])
										if($(this).parent().find(".expandable-hitarea")[0] == $(this).prev()[0]){
											setTimeout(function(){
												if(over.hasClass("folder-over"))
													over.prev().trigger("click");
											}, 500);
											
										}
							},
							out : function(a,b){
								//RETIRA BORDA EM VOLTA DA PASTA
                                $(b.helper).find(".draggin-folder,.draggin-mail").css("color", "");
								$(this).removeClass("folder-over");
							},
							//accept: ".draggin_mail",
							drop : function(event, ui){
								$(this).css("border", "0");
								if($(this).parent().attr('id') == undefined){
									var folder_to = 'INBOX';
									var to_folder_title = get_lang("Inbox");
								}else{
									var folder_to = $(this).parent().attr('id');
									var to_folder_title = $(this).attr('title');
								}		
								var folder_to_move = ui.draggable.parent().attr('id');
								var border_id = ui.draggable.find("input[type=hidden]").attr("name");
								// Mensagens : SE O DROP VIER DA LISTA DE MENSAGENS :
								if(folder_to_move == "tbody_box"){
									move_msgs2(get_current_folder(), 'selected', 0, folder_to, to_folder_title, true, true);
									return refresh();
								}
							}
						});
					}
				},
				stop :function(){
                    $('.upper, .lower').hide();
					$(".shared-folders").parent().find(".folder").droppable("destroy");
				},
				//helper: 'clone',
 				helper: function(event){
					$(this).addClass("selected_msg").find("input:checkbox").attr("checked", true);
					updateSelectedMsgs($(this).find("input:checkbox").is(':checked'),$(this).attr("id"));
					if ( $("#tbody_box .selected_shortcut_msg").length > 1) {
						$("#tbody_box .selected_shortcut_msg").addClass("selected_msg").find('.checkbox').attr('checked',true);
						$.each( $(".selected_shortcut_msg"), function(index, value){
			            	updateSelectedMsgs($(value).find(":checkbox").is(':checked'),$(value).attr("id"));
				        });
					}
					
					if(totalSelected() > 1 )
						return $("<tr><td>"+DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : (totalSelected()+" mensagens selecionadas"), type: "messages"})+"</td></tr>");				
					if(	$(this).find(".td_msg_subject").text().length > 18 )
						return $("<tr><td>"+DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : $(this).find(".td_msg_subject").text().substring(0,18) + "...", type: "messages"})+"</td></tr>");
					else
						return $("<tr><td>"+DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : $(this).find(".td_msg_subject").text(), type: "messages"})+"</td></tr>");
				}, 
				iframeFix: true,
				delay: 150,
				cursorAt: {top: 5, left: 56},
				refreshPositions: true,
				containment: "#divAppbox"
			}).bind('contextmenu',function(event){
				if (!($(event.target).hasClass('td-label'))){
					if($(this).find("input:checkbox").attr("checked") != "checked"){

                        $(this).find("input:checkbox").trigger('click');
			
                        $(this).addClass("selected_msg");
                }
                updateSelectedMsgs($(this).find("input:checkbox").is(':checked'),$(this).attr("id"));
                } else if ( typeof $(event.target).attr("style") == "undefined" || $(event.target).attr("style").match(/background/g) == null ) {
                    if($(this).find("input:checkbox").attr("checked") != "checked"){

                        $(this).find("input:checkbox").trigger('click');
            
                        $(this).addClass("selected_msg");
                    }
                    updateSelectedMsgs($(this).find("input:checkbox").is(':checked'),$(this).attr("id"));
                }

			});
	}

	if ((preferences.use_shortcuts == '1') && (headers_msgs[0]))
		select_msg(headers_msgs[0].msg_number, 'null', true);
		
	
	var tdFolders  =  Element("folderscol");
	if ( !currentTab )
		alternate_border(numBox);
	draw_footer_box(headers_msgs.num_msgs);
	Element('main_table').style.display = '';
	resizeWindow();
	if(debug) {
		var _eTime = new Date();
		_eTime = _eTime.getTime();
		alert("Carregou em "+(_eTime - _bTime)+" ms");
	}
	
	var msg_folder = Element('msg_folder').value;
	var msg_number = Element('msg_number').value;
	if(!msg_folder && msg_number) {
        if ((msg_number.toString().indexOf('@') != -1) || !msg_number.toString().match(/[0-9]/)){
            new_message_to(msg_number);
        }    
        else
            new_message('new','null');
	}
	else if(msg_folder && msg_number){
		$.ajax({
			  url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
							      msg_number: msg_number, 
							      msg_folder: msg_folder,
							      decoded: true } ),
			  success: function( data ){
			      data = connector.unserialize( data );
			      
			      if( data )
				  show_msg( data );
			  },
			  beforeSend: function( jqXHR, settings ){
			  	connector.showProgressBar();
			  },
			  complete: function( jqXHR, settings ){
			  	connector.hideProgressBar();
			  }

		});
		Element('msg_folder').value = '';
		Element('msg_number').value = '';
	}
	connector.loadScript("InfoContact");
	// Loading Quota View Folder Component (if exists...)
	if(Element("table_quota"))
		connector.loadScript("InfoQuota");
	
	// creates the dotted ruler which helps user to visualize resizing of columns
	var ruler_element = document.getElementById("table_message_ruler");
	if (ruler_element == null) {
		ruler_element = document.createElement("DIV");
		ruler_element.setAttribute("id", "table_message_ruler");
		ruler_element.className = "table_message_ruler";
		ruler_element.style.top = getColumnPosition(document.getElementById("content_id_0"),"BODY")[1];
		document.getElementById("exmail_main_body").appendChild(ruler_element);
    }

    if($('.select-all-messages').length == 0)
        drawSelectMsgsTable();
    else
        $('.select-all-messages').show();

		updateSelectedMsgs();
        resizeWindow();
}

function showEmptyBoxMsg(tbody_element) {
	document.getElementById("table_message_header_box_"+numBox).emptyBody = true;
        var div_pasta = document.getElementById("div_msg_info");
        if (!div_pasta){
            div_info = document.createElement("div");
            div_info.setAttribute("id", "div_msg_info");
            div_info.setAttribute("background", "#FFF");
            h3_info = document.createElement("h3");
            h3_info.style.padding = "10px";
            h3_info.setAttribute("id", "msg_info");
            h3_info.align = "center";
            h3_info.innerHTML = get_lang("This mail box is empty");
            div_info.appendChild(h3_info);
            tbody_element.parentNode.parentNode.appendChild(div_info);
        }
}

function html_entities(string) {
		return String(string).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Passar o parâmetro offset para esta função
function make_tr_message(headers_msgs, msg_folder, offsetToGMT){
		headers_msgs.subject = html_entities(headers_msgs.subject);
		if (typeof offsetToGMT == 'undefined')
		{
			// In older local messages headers_msgs.offsetToGMT is undefined.
			offsetToGMT = typeof headers_msgs.offsetToGMT != 'undefined'?headers_msgs.offsetToGMT:0;
		}
		var tr_element = document.createElement('tr');
		if(typeof(preferences.line_height) != 'undefined')
			tr_element.style.height = preferences.line_height;
		tr_element.id = headers_msgs.msg_number;

		tr_element.msg_sample = "";
        tr_element.tip = "";
		
		if(headers_msgs.msg_sample && preferences.preview_msg_subject == "1" && headers_msgs.msg_sample.body !== "")
		{
			if (cssForResizing) //Colunas redimensionaveis - nao trunca
				tr_element.msg_sample = html_entities(Base64.decode(headers_msgs.msg_sample.body).substr(0,180) + "..."); //trecho do body que sera exibido com o assunto;
			else
				tr_element.msg_sample = html_entities(Base64.decode(headers_msgs.msg_sample.body).substr(0,120) + "..."); //trecho do body que sera exibido com o assunto;

                if(proxy_mensagens.is_local_folder(current_folder)) {
                    // preferencia da pre-visualizacao
                    if (cssForResizing) {//Colunas redimensionaveis - nao trunca
                        tr_element.msg_sample = headers_msgs.msg_sample.body.substr(0,180) + "..."; //trecho do body que sera exibido com o assunto; //blabla
                    } else {
                        tr_element.msg_sample = headers_msgs.msg_sample.body.substr(0,120) + "..."; //trecho do body que sera exibido com o assunto;
                    }

                    // preferencia dos tooltips
                    if(headers_msgs.msg_sample && preferences.preview_msg_tip == "1" && headers_msgs.msg_sample.body !== "") {
                        tr_element.tip = headers_msgs.msg_sample.body.substr(3,300) + "..."; //trecho do body que sera exibido no tool-tip;
                    }

                }

            /*}*/ else { //IMAP
                 // preferencia da pre-visualizacao
			     if (cssForResizing) { //Colunas redimensionaveis - nao trunca
                    /*problema no decode, se ele for utilizado, nao lista as mensagens -> CORRIGIDO em mail_archiver.js*/
                    //if (headers_msgs.msg_sample.body.length > 0)
				    tr_element.msg_sample = Base64.decode(headers_msgs.msg_sample.body).substr(0,180) + "..."; //trecho do body que sera exibido com o assunto; //blabla
			        tr_element.msg_sample = html_entities(tr_element.msg_sample); 
                 } else {
				    //if (headers_msgs.msg_sample.body.length > 0)
					tr_element.msg_sample = Base64.decode(headers_msgs.msg_sample.body).substr(0,120) + "..."; //trecho do body que sera exibido com o assunto;
		            tr_element.msg_sample = html_entities(tr_element.msg_sample);  
                  }
            }
        }
        // preferencia dos tooltips
        if(headers_msgs.msg_sample && preferences.preview_msg_tip == "1" && headers_msgs.msg_sample.body !== "") {
            tr_element.tip = Base64.decode(headers_msgs.msg_sample.body).substr(3,300) + "..."; //trecho do body que sera exibido no tool-tip;
        }
		
		if ((headers_msgs.Unseen == 'U') || (headers_msgs.Recent == 'N')){
			if ((headers_msgs.Flagged == 'F') || ( headers_msgs.Importance !== undefined && headers_msgs.Importance.toLowerCase().indexOf("high")!=-1 ) )
				add_className(tr_element, 'flagged_msg');
			add_className(tr_element, 'tr_msg_unread');
		}
		else{
			if ((headers_msgs.Flagged == 'F') || ( headers_msgs.Importance !== undefined && headers_msgs.Importance.toLowerCase().indexOf("high")!=-1 ) )
				add_className(tr_element,'flagged_msg');
		}

		if ((headers_msgs.Unseen == 'U') || (headers_msgs.Recent == 'N'))
			add_className(tr_element, 'tr_msg_unread');

		if (headers_msgs.Flagged == 'F')
			add_className(tr_element,'flagged_msg');

		td_element1 = createTDElement(0,0,"td_msg");
		chk_box_element = document.createElement("INPUT");
		chk_box_element.setAttribute("type", "checkbox");
		chk_box_element.className = "checkbox";
		chk_box_element.setAttribute("id", "check_box_message_"+headers_msgs.msg_number);
		
		$(chk_box_element).click(function(e){
			updateSelectedMsgs($(this).is(':checked'),headers_msgs.msg_number);
			$(".selected_shortcut_msg").removeClass("current_selected_shortcut_msg selected_shortcut_msg");
			$(".current_selected_shortcut_msg").removeClass("current_selected_shortcut_msg selected_shortcut_msg");
			if(preferences.use_shortcuts == '1')
				$(this).parents("tr:first").addClass("current_selected_shortcut_msg selected_shortcut_msg");
			changeBgColor(e,headers_msgs.msg_number);
			$(this).blur();
		});

		td_element1.appendChild(chk_box_element);

		td_element2 = createTDElement(0,1,"td_msg");
		if (headers_msgs.attachment && (headers_msgs.attachment == 1 || headers_msgs.attachment.number_attachments> 0))
			$(td_element2).addClass('expressomail-sprites-clip');

		td_element21 = createTDElement(0,2,"td_msg",null,"td_message_answered_"+headers_msgs.msg_number);

		if ((headers_msgs.Forwarded == 'F')  || (headers_msgs.Draft == 'X' && headers_msgs.Answered == 'A')){
			$(td_element21).on('click',function(){search_emails(headers_msgs.subject.replace(/^(re: ?|fw: ?|enc: ?|res: ?|fwd: ?)*/gi,''),true);});
                        td_element21.innerHTML = "<img src ='templates/"+template+"/images/forwarded.png' title='"+get_lang('Forwarded')+"'>";
			headers_msgs.Draft = ''
			headers_msgs.Answered = '';
			headers_msgs.Forwarded = 'F';
		}
		else if (headers_msgs.Draft == 'X')
			td_element21.innerHTML = "<img src ='templates/"+template+"/images/draft.png' title='"+get_lang('Draft')+"'>";
		else if (headers_msgs.Answered == 'A'){
                        $(td_element21).on('click',function(){search_emails(headers_msgs.subject.replace(/^(re: ?|fw: ?|enc: ?|res: ?|fwd: ?)*/gi,''),true);});
			td_element21.innerHTML = "<img src ='templates/"+template+"/images/answered.png' title='"+get_lang('Answered')+"'>";
                    }else
			td_element21.innerHTML = "&nbsp;&nbsp;&nbsp;";

		td_element22 = createTDElement(0,1,"td_msg",null,"td_message_signed_"+headers_msgs.msg_number);
		switch(headers_msgs.ContentType)
		{
			case "signature":
			{
				td_element22.innerHTML = "<img src ='templates/"+template+"/images/signed_msg.gif' title='" + get_lang('Signed message') + "'>";
				break;
			}
			case "cipher":
			{
				td_element22.innerHTML = "<img src ='templates/"+template+"/images/lock.gif' title='" + get_lang('Crypted message') + "'>";
				break;
			}
			default:
			{
				break;
			}
		}

		td_element23 = createTDElement(0,4,"td_msg",null,"td_message_important_"+headers_msgs.msg_number);

		if ( (headers_msgs.Flagged == 'F') || ( headers_msgs.Importance !== undefined && headers_msgs.Importance.toLowerCase().indexOf("high") != -1 )) 
		{
			td_element23.innerHTML = "<img src ='templates/"+template+"/images/important.png' title='"+get_lang('Important')+"'>";
		}
		else
			td_element23.innerHTML = "&nbsp;&nbsp;&nbsp;";

		td_element24 = createTDElement(0,5,"td_msg",null,"td_message_sent_"+headers_msgs.msg_number);
		td_element24.innerHTML = "&nbsp;&nbsp;&nbsp;";
		// preload image
		var _img_sent = new Image();
		_img_sent.src 	 = "templates/"+template+"/images/sent.gif";



		td_element25 = createTDElement(0,7,"td_msg",null,"td_message_unseen_"+headers_msgs.msg_number);
		if ((headers_msgs.Unseen == 'U') || (headers_msgs.Recent == 'N'))
			td_element25.innerHTML = "<img src ='templates/"+template+"/images/unseen.gif' title='"+get_lang('Unseen')+"'>";
		else
			td_element25.innerHTML = "<img src ='templates/"+template+"/images/seen.gif' title='"+get_lang('Seen')+"'>";


		td_element3 = createTDElement(0,8,"td_msg td_resizable","left","td_from_"+ headers_msgs.msg_number);
		var _onclick = function(){ if (InfoContact) InfoContact.hide();proxy_mensagens.get_msg(headers_msgs.msg_number, msg_folder,true, show_msg);};
		td_element3.onclick = _onclick;
		td_element3.innerHTML = '&nbsp;';

		test = true;
		if(msg_folder.indexOf(special_folders['Sent']) !=-1 ||msg_folder.indexOf(preferences.save_in_folder) !=-1 || msg_folder.replace("local_","INBOX"+cyrus_delimiter).indexOf(preferences.save_in_folder) !=-1)
		    test = false;
		
		if( (msg_folder.indexOf(special_folders['Sent']) !=-1) && (headers_msgs.from != undefined) && headers_msgs.from.email.toLowerCase() == Element("user_email").value.toLowerCase() && (preferences.from_to_sent == "1") && !(msg_folder.substr(0,5) == "user/"))
		{
			td_element3.onmouseover = function () { 
					var title_to = ''; 
					$.each(headers_msgs.to, function(index, value) { 
							if(index == (headers_msgs.to.length - 1)){ 
									title_to = title_to + value.email; 
							} 
							else { 
									title_to = title_to + value.email + ', '; 
							} 
					}); 
					this.title = title_to; 
			};
			
			if (headers_msgs.Draft == 'X')
				td_element3.innerHTML += "<span style=\"color:red\">("+get_lang("Draft")+") </span>";
			else{
				if(headers_msgs.to && headers_msgs.to[0] && headers_msgs.to[0].email != null && headers_msgs.to[0].email.toLowerCase() != Element("user_email").value) 
					td_element24.innerHTML = "<img align='center' src ='templates/"+template+"/images/sent.gif' title='"+get_lang('Sent')+"'>";

				if (headers_msgs.to && headers_msgs.to[0]) {
					if (headers_msgs.to[0].name != null) 
							td_element3.innerHTML += headers_msgs.to[0].name; 
					else if(headers_msgs.to[0].email != null) { 
							td_element3.innerHTML += headers_msgs.to[0].email;
					}
					else {
						td_element3.innerHTML += get_lang("without destination");
					}
				}
			}
		}
		else{
			if (headers_msgs.Draft == 'X'){
				td_element3.innerHTML = "<span style=\"color:red\">("+get_lang("Draft")+") </span>";
			}
			else{
				var spanSender = document.createElement("SPAN");
				spanSender.setAttribute('class','span-sender');
				spanSender.onmouseover = function(event)
				{
					InfoContact.begin( this , headers_msgs.reply_toaddress );
				};
				folder = special_folders['Sent'];
				current = get_current_folder();
				if ((preferences.from_to_sent == "1") && (current.substr(current.length - folder.length, folder.length) == folder)){
					spanSender.onmouseover = function()
					{ 
						var title_to = ''; 
						$.each(headers_msgs.to, function(index, value) { 
								if(index == (headers_msgs.to.length - 1)){ 
										title_to = title_to + value.email; 
								} 
								else { 
										title_to = title_to + value.email + ', '; 
								} 
						}); 
						this.title = title_to; 
					};
					if (headers_msgs.to && headers_msgs.to[0] != null) {
						if (headers_msgs.to[0].name != null){
							spanSender.innerHTML += headers_msgs.to[0].name;
						}else if(headers_msgs.to[0].email != null) {
							spanSender.innerHTML += headers_msgs.to[0].email;
						}else {
                            spanSender.innerHTML += get_lang("without destination");
                        }
					}
				}else if(headers_msgs.from !== undefined){
				spanSender.innerHTML =  headers_msgs.from.name != null ? headers_msgs.from.name : headers_msgs.from.email;
				}
				if (spanSender.innerHTML.indexOf(" ") == '-1' && spanSender.innerHTML.length > 25){
					spanSender.innerHTML = spanSender.innerHTML.substring(0,25) + "...";
				}
				else if (spanSender.innerHTML.length > 40 ){
					spanSender.innerHTML = spanSender.innerHTML.substring(0,40) + "...";
				}
				td_element3.appendChild(spanSender);
			}
		}

		td_element4 = createTDElement(0,9,"td_msg td_resizable","left");
		td_element4.className += " td_msg_subject";
		td_element4.onclick = _onclick;
		td_element4.innerHTML = !is_ie ? "<a nowrap id='a_message_"+tr_element.id+"'>&nbsp;" : "&nbsp;";

		if ((headers_msgs.subject)&&(headers_msgs.subject.length > 50))
		{
			if (cssForResizing)
				//Colunas redimensionaveis - nao trunca
				td_element4.innerHTML += headers_msgs.subject + "<span style=\"color:#b3b3b3;\">  " + tr_element.msg_sample +"</span>";
			else {
				//Modificacao para evitar que o truncamento do assunto quebre uma NCR - #1189
				pos = headers_msgs.subject.indexOf("&",45);
				if ((pos > 0) && (pos <= 50) && ((headers_msgs.subject.charAt(pos+5) == ";") || (headers_msgs.subject.charAt(pos+6) == ";")))
					td_element4.innerHTML += headers_msgs.subject.substring(0,pos+6) + "..." + "<span style=\"color:#b3b3b3;\">  " + tr_element.msg_sample +"</span>";
				else
					td_element4.innerHTML += headers_msgs.subject.substring(0,50) + "..." + "<span style=\"color:#b3b3b3;\">  " + tr_element.msg_sample +"</span>";//modificacao feita para exibir o trecho do body ao lado do assunto da mensagem;
			}
		}
		else
		{
			td_element4.innerHTML += ($.trim(headers_msgs.subject) == "" ? "("+get_lang("No Subject")+")" : headers_msgs.subject) + "<span style=\"color:#b3b3b3;\">  " + tr_element.msg_sample + "</span>";//modificacao feita para exibir o trecho do body ao lado do assunto da mensagem;
		}

		td_element4.title=tr_element.tip;
		if(!is_ie){
			td_element4.innerHTML += "</a>";
		}
		
		td_element5 = createTDElement(0,10,"td_msg td_resizable","center");
		td_element5.onclick = _onclick;
		
		td_element27 = createTDElement(0,7,"td_msg",null,"td_message_labels_"+headers_msgs.msg_number);
		$(td_element27).addClass("td-label");

		if (headers_msgs.labels) {
			$(td_element27).css({'background-image':'url(../prototype/modules/mail/img/mail-sprites.png)','background-position': '0 -1706px',"margin-left":"0",'margin-top':'3px','background-repeat':'no-repeat'});
			updateLabelsColumn(headers_msgs);		
		}
		
		  td_element26 = createTDElement(0,6,"td_msg","center","td_message_followup_"+headers_msgs.msg_number);
		  $(td_element26).addClass("td-followup-flag");

        if((get_current_folder().split("_")[0] != "local") && (preferences['use_followupflags_and_labels'] == '1')){
		  td_element26.innerHTML = '<div class="flag-edited" style="width:8px;height:6px;"><img src="../prototype/modules/mail/img/flagEditor.png"></div>';
	    } else {
            td_element26.innerHTML = "";
        }
        if (preferences['use_followupflags_and_labels'] == '1'){
    		if (headers_msgs.followupflagged) {
    			if(headers_msgs.followupflagged.followupflag.id < 7){
    				var nameFollowupflag = get_lang(headers_msgs.followupflagged.followupflag.name);
    			}else{
    				var nameFollowupflag = headers_msgs.followupflagged.followupflag.name;
    			}
    			$(td_element26).attr('title', nameFollowupflag)
    			.find(".flag-edited").css("background",headers_msgs.followupflagged.backgroundColor);
    			if(headers_msgs.followupflagged.isDone == "1"){
    				$(td_element26).find(".flag-edited").find("img")
    				.attr("src", "../prototype/modules/mail/img/flagChecked.png")
    				.css("margin-left","-3px");
    			}
    		} else {
    			$(td_element26).find(".flag-edited").css("background","#cccccc");
    		}
		}
		/**
		 * Clique para aplicar sinalizador
		 */
		$(td_element26).click(function() {	
			var messageClickedId = $(this).attr('id').match(/td_message_followup_([\d]+)/)[1];

            var loading = $('#td_message_followup_' + messageClickedId + ', ' + 
            'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
            .find('img[alt=Carregando]');


            //Verificar se está carregando a bandeira.
            //Caso esteja ele sai da função até que seja carregado. 
            if( loading.length ) {
                return false;
            }

			var followupColor = $('#td_message_followup_' + messageClickedId).find(".flag-edited").css('backgroundColor');
			
			$('#td_message_followup_' + messageClickedId + ', ' + 
			'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
			.html('<img alt="Carregando" title="Carregando" style="margin-left:-3px; margin-top:-4px; width:13px; height:13px;" src="../prototype/modules/mail/img/ajax-loader.gif" />');
			
			$('#td_message_followup_' + messageClickedId + ', ' + 
			'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").css("background", "transparent");
			

			/**
				* Hack:
				* headers_msgs.followupflagged.id não vai funcionar porque já foi feito DataLayer.commit()
				* por isso o id deve ser obtido do banco
				* também para verificar se há ou não sinalizador nesta mensagem
			*/

			DataLayer.remove('followupflagged', false);
			var flagged = DataLayer.get('followupflagged', {filter: [
				'AND', 
				['=', 'messageNumber', messageClickedId], 
				['=', 'folderName', msg_folder]
			]});
			
			if (!flagged) {
				/**
				 * Aplica followupflag de Acompanhamento
				 */
				headers_msgs.followupflagged = {
					uid : User.me.id,
					folderName : msg_folder, 
					messageNumber : messageClickedId, 
					alarmTime : false, 
					backgroundColor : '#FF2016',
					followupflagId: '1'
				};
				headers_msgs.followupflagged.id = DataLayer.put('followupflagged', headers_msgs.followupflagged);
				DataLayer.commit(false, false, function(data){
					var fail = 'success';
					$.each(data, function(index, value) {
						if(typeof value === 'string'){
							fail = value;
						}
					});
				
					$('#td_message_followup_' + messageClickedId + ', ' + 
					'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
					.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"})
					.find('img').remove();
					
					
					if(fail != 'success'){
					    var msgFlag =  $('#td_message_followup_' + messageClickedId + ', ' + 
                        'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").css("background", "#CCCCCC");

                        msgFlag.find('img').remove();
                        
                        //Insere a imagem da flag quando ocorre erro ao marcar a msg
                        msgFlag.append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");

                        MsgsCallbackFollowupflag[fail]();
					}else{
					    $('#td_message_followup_' + messageClickedId + ', ' + 
					    'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).attr('title', get_lang('Follow up')).find(".flag-edited").css("background", headers_msgs.followupflagged.backgroundColor)
						.append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");
					    updateCacheFollowupflag(messageClickedId, msg_folder, true);
					}				
				});
				
			
			} else if (onceOpenedHeadersMessages[msg_folder][messageClickedId]['followupflagged'].followupflag.name == 'Follow up') {
				/**
				 * Remove followupflag de Acompanhamento
				 */
				 $(this).find(".flag-edited").css("background", "#cccccc");
				DataLayer.remove('followupflagged', flagged[0].id );
				DataLayer.commit(false, false, function(){
					updateCacheFollowupflag(messageClickedId, msg_folder, false);
					$('#td_message_followup_' + messageClickedId + ', ' + 
					'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
					.find('img').remove();
					
					$('#td_message_followup_' + messageClickedId + ', ' + 
					  'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).attr('title', '').find(".flag-edited").css("background", '#CCC');
				
					$('#td_message_followup_' + messageClickedId + ', ' + 
						'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").html('<img src="../prototype/modules/mail/img/flagEditor.png">')
                        .css({"width":"8px","height":"6px"/*"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"*/});
				});	

			} else {
				$('#td_message_followup_' + messageClickedId + ', ' + 
				'tr[role="'+messageClickedId+'_'+msg_folder+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
				.css("backgroundColor", followupColor)
                .find('img').remove(); //remove imagem carregando da bandeira

                $('#td_message_followup_' + messageClickedId).find('.flag-edited').append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");

				
			   //Pega id do checkbox
               var id = $(tr_element).addClass('selected_msg').find(':checkbox').attr('id');
                
                //Verifica se o checkbox está selecionado
                if($('#' + id).attr('checked') != 'checked')
                    $(tr_element).addClass('selected_msg').find(':checkbox').trigger('click');
				
				/**
				 * Hack - Força a atualização da seleção da mensagem, devido a problema na 
				 * function de seleção atribuida ao evento onclick do checkbox
				 */
				updateSelectedMsgs(true,messageClickedId);
				
				configureFollowupflag();
			}
		});	
		
		var norm = function (arg) {return (arg < 10 ? '0'+arg : arg);};
		var weekDays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

                var today = new Date();
		today.setHours(23);
		today.setMinutes(59);
                today.setSeconds(59);
                today.setMilliseconds(999)

                var udate_local = null;
                var date_msg = null;

                // old local messages can capture headers_msgs.udate as "hh:mm" or "dd/mm/yyyy"
                if ( (headers_msgs.udate !== undefined) && (headers_msgs.udate.toString().match(/\d{2}:\d{2}/) || headers_msgs.udate.toString().match(/\d{2}\/\d{2}\/\d{4}/)) )
                {
                    temp_msg_day = headers_msgs.msg_day.split('/');
                    temp_msg_hour = headers_msgs.msg_hour.split(':');
                    date_msg = new Date(temp_msg_day[2], temp_msg_day[1]-1, temp_msg_day[0], temp_msg_hour[0], temp_msg_hour[1]);
                }
                else
                    {
                        // The new date implementation
                        // Using offset between user defined timezone and GMT
                        // Date object converts time to local timezone, so we have to adjust it
                        udate_local = headers_msgs.udate*1000 + offsetToGMT*1000 + today.getTimezoneOffset()*60*1000;
                        date_msg = new Date(udate_local);
                    }

		if (preferences.show_date_numerical == 0 || typeof(preferences.show_date_numerical) == 'undefined') { 	
			if (today.getTime() - date_msg.getTime() < 86400000)
				td_element5.innerHTML = norm(date_msg.getHours()) + ':' + norm(date_msg.getMinutes());
			else
				if (today.getTime() - date_msg.getTime() < 172800000)
					td_element5.innerHTML = get_lang('Yesterday');
				else if (today.getTime() - date_msg.getTime() < 259200000)
					td_element5.innerHTML = get_lang(weekDays[date_msg.getDay()]);
				else{
					td_element5.innerHTML = norm(date_msg.getDate()) + '/' + norm(date_msg.getMonth()+1) + '/' +date_msg.getFullYear();					
					td_element5.title = norm(date_msg.getDate()) + '/' + norm(date_msg.getMonth()+1) + '/' +date_msg.getFullYear();
					td_element5.alt = td_element5.title;
				}
		}else{
			td_element5.innerHTML = norm(date_msg.getDate()) + '/' + norm(date_msg.getMonth()+1) + '/' +date_msg.getFullYear();
			td_element5.title = norm(date_msg.getDate()) + '/' + norm(date_msg.getMonth()+1) + '/' +date_msg.getFullYear();
			td_element5.alt = td_element5.title;
		}
		td_element6 = createTDElement(0,11,"td_msg td_resizable","center");
		td_element6.onclick = _onclick;
		td_element6.setAttribute("noWrap","true");
		td_element6.innerHTML = borkb(headers_msgs.Size);

		tr_element.appendChild(td_element1);
		tr_element.appendChild(td_element2);
		tr_element.appendChild(td_element21);
		tr_element.appendChild(td_element22);
		tr_element.appendChild(td_element23);
		tr_element.appendChild(td_element24);
		tr_element.appendChild(td_element26);
		tr_element.appendChild(td_element27);
		tr_element.appendChild(td_element25);

		tr_element.appendChild(td_element3);
		tr_element.appendChild(td_element4);
		tr_element.appendChild(td_element5);
		tr_element.appendChild(td_element6);
		return tr_element;
}

function sort_box(search, sort,clean_selected){
	if (typeof(clean_selected) == "undefined")
		selectAllFolderMsgs(false);
	if (cellResized)
		return;
	var message_header = Element("message_header_"+search);
	var handler_draw_box = function(data){

        if (typeof(clean_selected) == "undefined")
            populateSelectedMsgs(data.messagesIds);

        draw_box(data, current_folder,true);
		//Mostrar as msgs nao lidas de acordo com o filtro de relevancia
        var msgs_unseen = 0;
		draw_paging(data.num_msgs);
		Element("new_m").innerHTML = '<font style="color:'+(data.tot_unseen == 0 ? '': 'red')+'">' + data.tot_unseen + '</font>';
		Element("tot_m").innerHTML = data.num_msgs;
	}

	if(sort_box_type == sort && search_box_type == search){
		sort_box_reverse = sort_box_reverse ? 0 : 1;
	}
	else if(sort_box_type != sort){
		if ( (sort == 'SORTFROM') || (sort == 'SORTSUBJECT') )
			sort_box_reverse = 0;
		else
			sort_box_reverse = 1;
	}

	// Global variable.
	sort_box_type = sort;
	search_box_type = search;

	proxy_mensagens.messages_list(current_folder,1,preferences.max_email_per_page,sort,search,sort_box_reverse,preferences.preview_msg_subject,preferences.preview_msg_tip,handler_draw_box);
	current_page = 1;
	//Comentado para nao sobrepor o numero de msgs nao lidas ao utilizar os filtros
	//update_menu();
}

function draw_header_box(){
	var types = {'SORTFROM': 'From', 'SORTSUBJECT': 'Subject', 'SORTARRIVAL': 'Date', 'SORTSIZE': 'Size'};
    type_name = ( types[sort_box_type] ? get_lang(types[sort_box_type]) : get_lang("Date") );
	folder = special_folders['Sent'];
	current = get_current_folder();
	if ((preferences.from_to_sent == "1") && (current.substr(current.length - folder.length, folder.length) == folder)) {
        $("#message_header_SORTFROM_"+numBox).html(get_lang("To"));
        if (sort_box_type == "SORTFROM")
        	type_name = get_lang("To");
    }else{
	   $("#message_header_SORTFROM_"+numBox).html(get_lang("From"));
    }
	$("#message_header_SORTSUBJECT_"+numBox).html(get_lang("Subject"));
	$("#message_header_SORTARRIVAL_"+numBox).html(get_lang("Date"));
	$("#message_header_SORTSIZE_"+numBox).html(get_lang("Size"));
	document.getElementById("message_header_"+(sort_box_type.lastIndexOf("SORT") != "-1" ? sort_box_type : "SORTARRIVAL")+"_"+numBox ).innerHTML = "<B>"+type_name+"</B><img src ='templates/"+template+"/images/arrow_"+(sort_box_reverse == 1 ? 'desc' : 'asc')+"endant.gif'>";
}

function verifyOption(name, id){
	var str = name + '' + id;
	if(!Element(str).style.display == ""){
		var option_reply_options = document.getElementById('msg_opt_reply_options_'+id);
		option_reply_options.value = 'show';
		option_reply_options.src= '../expressoMail/templates/default/images/down.png';
		Element('tr_other_options_'+id).style.display = 'none';
		var option_more_options = document.getElementById('msg_opt_more_options_'+id);
		option_more_options.value = 'show';
		option_more_options.src= '../expressoMail/templates/default/images/down.png';
		Element('tr_other_more_options_'+id).style.display = 'none';
		var option_mark_as_options = document.getElementById('msg_opt_mark_options_'+id);
		option_mark_as_options.value = 'show';
		option_mark_as_options.src= '../expressoMail/templates/default/images/down.png';
		Element('tr_other_mark_options_'+id).style.display = 'none';
	}
}

/*Busca a primeira mensagem na pagina*/
function firstRow(){
	var thisCell;
    if (objCell)
        thisCell = objCell;
    else
	    thisCell = document.getElementById("table_message_header_box_0").rows[0].cells[0];
    var tbM = getMessagesTable(thisCell);
	proxy_mensagens.get_msg(tbM.rows[0].getAttribute('id'),get_current_folder(),true,show_msg);
}

/*Busca a ultima mensagem na pagina*/
function lastRow(){
	var thisCell;
    if (objCell)
        thisCell = objCell;
    else
	    thisCell = document.getElementById("table_message_header_box_0").rows[0].cells[0];
    var tbM = getMessagesTable(thisCell);
	proxy_mensagens.get_msg(tbM.rows[tbM.rows.length - 1].getAttribute('id'),get_current_folder(),true,show_msg);
}

var msg_selected;
function draw_message(info_msg, ID){
	// remove a flag $FilteredMessage da mensagem ao ser lida
	if(info_msg.Unseen == "U" && preferences['use_alert_filter_criteria'] == "1"){
		$.each(fromRules, function(index, value) {
			if(value == info_msg.msg_folder){
				cExecute ("$this.imap_functions.removeFlagMessagesFilter&folder="+info_msg.msg_folder+"&msg_number="+info_msg.msg_number, function(){}); 
				return false;
			}
		});
	}
	var content = document.getElementById('content_id_' + ID);

	var menuHidden = Element("folderscol").style.display == 'none' ? true : false;
	 //////////////////////////////////////////////////////////////////////////////////////////////////////
	//Make the next/previous buttom.
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	var next_previous_msg_td = document.createElement("TD");
	next_previous_msg_td.setAttribute("noWrap","true");
	next_previous_msg_td.align = "right";
	next_previous_msg_td.style.fontSize = "10px";
	next_previous_msg_td.width = "40px";
	var img_next_msg = document.createElement("IMG");
	img_next_msg.id = 'msg_opt_next_' + ID;
	img_next_msg.src = './templates/'+template+'/images/down.button.png';
        if(preferences.use_shortcuts == '1')
            img_next_msg.title = get_lang('Next Shortcut:Control + Down');
        else
            img_next_msg.title = get_lang('Next');
	img_next_msg.style.cursor = 'pointer';

        var folder_id = ID.match(/\d+/)[0];
        var folder;

        //Correção para fazer funcionar e-mails assinados no formato encapsulado.
       // folder_id = info_msg.original_ID ? info_msg.original_ID: info_msg.msg_number;
        //if ((folder = document.getElementById(info_msg.msg_number)) == null)
	if ((folder = Element(info_msg.original_ID)) == null)
		folder = Element(info_msg.msg_number);

	if (folder){ // mensagem local criptografada nao tem ID da pasta local
		if (folder.nextSibling){
			var nextMsgBox = folder.nextSibling.name?folder.nextSibling.name:info_msg.msg_folder;

			if (nextMsgBox == "INBOX" + cyrus_delimiter + "decifradas")// teste para ver se a mensagem vem da pasta oculta decifradas
					nextMsgBox = get_current_folder();

			img_next_msg.onclick = function()
			{
                if(info_msg.msg_number.match("s[0-9]+")){
                    var msg_next = $('#'+info_msg.msg_number).next();
                    info_msg.next_message = msg_next.attr("id");
                    info_msg.nextMsgBox = msg_next.attr("name");
                    nextMsgBox = msg_next.attr("name");
                }
				currentTab = ID;
				openTab.type[ID] = 2;
				proxy_mensagens.get_msg(info_msg.next_message,nextMsgBox,true,show_msg);
				//select_msg('null', 'down', true);
				if (!msg_selected){
					if(!!parseInt(preferences.use_shortcuts))
                        select_msg('null', 'down', true);
					msg_selected = true;
				}
			};
		}
		/*Ultima mensagem de cada página, exceto a ultima*/
        else if( (current_page < total_pages) && !info_msg.msg_number.match("s[0-9]+") ){
		   
		   img_next_msg.onclick = function()
			{

			   current_page++;
			   var tot_msgs = parseInt(Element("tot_m").innerHTML);
			   var range_begin = preferences.max_email_per_page*( current_page - 1 ) + 1;
			   var range_end; 
			   if ( range_begin + parseInt(preferences.max_email_per_page - 1) > tot_msgs ) 
			        range_end = tot_msgs;
			   else     
			        range_end = range_begin + parseInt(preferences.max_email_per_page  - 1);  
			   var creatBoxProximo = function (data){
					    draw_box(data, get_current_folder());
					    firstRow();
			        };      			  
		       proxy_mensagens.messages_list(get_current_folder(),range_begin,range_end,sort_box_type,search_box_type,sort_box_reverse,'','', creatBoxProximo);
			
			};	   
		}
		else
		{
			img_next_msg.src = "./templates/"+template+"/images/down.gray.button.png";
			img_next_msg.style.cursor = 'default';

		}
	}
	else
	{
		img_next_msg.src = "./templates/"+template+"/images/down.gray.button.png";
		img_next_msg.style.cursor = 'default';
		// testa se a mensagem e local 
 	    if (!proxy_mensagens.is_local_folder(get_current_folder()) && !(info_msg.msg_folder == "INBOX" + cyrus_delimiter + "decifradas")) // testa se a mensagem e local
		{
			img_next_msg.onclick = function()
				{
					delete_border(ID);
				};
		}
	}
	var img_space = document.createElement("SPAN");
	img_space.innerHTML = "&nbsp;";
	var img_previous_msg = document.createElement("IMG");
	img_previous_msg.id = 'msg_opt_previous_' + ID;
	img_previous_msg.src = './templates/'+template+'/images/up.button.png';
        if(preferences.use_shortcuts == '1')
            img_previous_msg.title = get_lang('Previous Shortcut:Control + Up');
        else
            img_previous_msg.title = get_lang('Previous');
	img_previous_msg.style.cursor = 'pointer';


	if (folder){ // mensagem local criptografada nao tem ID da pasta local
		if (folder.previousSibling)
		{
			var previousMsgBox = folder.previousSibling.name?folder.previousSibling.name:info_msg.msg_folder;

			if (previousMsgBox == "INBOX" + cyrus_delimiter + "decifradas") // teste para ver se a mensagem vem da pasta oculta decifradas
					previousMsgBox = get_current_folder();

			img_previous_msg.onclick = function()
			{
                if(info_msg.msg_number.match("s[0-9]+")){
                    var msg_prev = $('#'+info_msg.msg_number).prev();
                    info_msg.prev_message = msg_prev.attr("id");
                    info_msg.previousMsgBox = msg_prev.attr("name");
                    previousMsgBox = msg_prev.attr("name");
                }
				currentTab = ID;
				openTab.type[ID] = 2;
				proxy_mensagens.get_msg(info_msg.prev_message,previousMsgBox,true,show_msg);
				//select_msg('null', 'up', false);
				if (!msg_selected){
                    if(!!parseInt(preferences.use_shortcuts))
					    select_msg('null', 'up', true);
					msg_selected = true;
				}
			};
		}
		//primeira mensagem de cada página, exceto a primeira
		else if(current_page > 1){
		      
		      img_previous_msg.onclick = function()
		      {
			     
			     current_page--;
			     var range_begin = (current_page - 1)*preferences.max_email_per_page + 1; 
			     var range_end = current_page*preferences.max_email_per_page;         			     
			     var creatBoxAnterior = function (data){
				    	  draw_box(data, get_current_folder());
					      lastRow();
					 };      			  
		         proxy_mensagens.messages_list(get_current_folder(),range_begin,range_end,sort_box_type,search_box_type,sort_box_reverse,'','', creatBoxAnterior);	         
	          };
	    }
		else
		{
			img_previous_msg.src = "./templates/"+template+"/images/up.gray.button.png";
			img_previous_msg.style.cursor = 'default';
		}
	}
	else
	{
		img_previous_msg.src = "./templates/"+template+"/images/up.gray.button.png";
		img_previous_msg.style.cursor = 'default';
		// testa se a mensagem e local 
 	    if (!proxy_mensagens.is_local_folder(get_current_folder()) && !(info_msg.msg_folder == "INBOX" + cyrus_delimiter + "decifradas")) // testa se a mensagem e local
		{
			img_previous_msg.onclick = function()
			{
				delete_border(ID);
			};
		}
	}
	next_previous_msg_td.appendChild(img_previous_msg);
	next_previous_msg_td.appendChild(img_space);
	next_previous_msg_td.appendChild(img_next_msg);
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	//Make the header message.
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	var table_message = document.createElement("TABLE");
	var tbody_message = document.createElement("TBODY");
	table_message.border = "0";
	//table_message.width = "100%";
	//k!
	table_message.setAttribute("class", "expressomail-message-body");
	table_message.setAttribute("className", "expressomail-message-body");
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	//Make the options message.
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	var tr0 = document.createElement("TR");
	tr0.className = "tr_message_header";
	var td0 = document.createElement("TD");
	var table_message_options = document.createElement("TABLE");
	table_message_options.width = "100%";
	table_message_options.border = '0';
	table_message_options.className = 'table_message';
	var tbody_message_options = document.createElement("TBODY");
	var tr = document.createElement("TR");
	var td = document.createElement("TD");
	td.setAttribute("noWrap","true");
	td.style.fontSize = "10px";
	var _name = '';
	var _maxChar = menuHidden ? 40 : 15;

    if(info_msg.from){
    	if (info_msg.from.name)
    	{
    		var spanName = document.createElement("SPAN");
    			spanName.innerHTML = info_msg.from.name;
    		_name = spanName.innerHTML.length > _maxChar ? spanName.innerHTML.substring(0,_maxChar) + "..." : spanName.innerHTML;
    	}
    	else
    		_name = info_msg.from.email.length > _maxChar ? info_msg.from.email.substring(0,_maxChar) + "..." : info_msg.from.email;
    }

	td.innerHTML = _name.bold() + ', ' + info_msg.smalldate;
	//k!
	if (info_msg.attachments && info_msg.attachments.length > 0){
        $(td).addClass('expressomail-sprites-clip').css({'cursor':'pointer','title': info_msg.attachments[0].name}).click(function(){
            $("#option_hide_more_"+ID).click();
        });
        td.innerHTML = "&nbsp&nbsp" + td.innerHTML;
    }    
    if (typeof(info_msg.signature) == 'string')
	{
		if (info_msg.signature != "void")
			td.innerHTML += '&nbsp;<img style="cursor:pointer" onclick="alert(\''+ get_lang("This message is signed, and you can trust.") + info_msg.signature +'\');" src="templates/'+template+'/images/signed.gif">';
		else
			td.innerHTML += "&nbsp;<img style='cursor:pointer' onclick='alert(\""+get_lang("This message is signed, but it is invalid. You should not trust on it.")+"\");' title='"+get_lang("Voided message")+"' src='templates/"+template+"/images/invalid.gif'>";
	}

	if (info_msg.DispositionNotificationTo)
	{
		td.innerHTML += '&nbsp;<img id="disposition_notification_'+ID+'" style="cursor:pointer" alt="'+ get_lang('Message with read notification') + '" title="'+ get_lang('Message with read notification') + '" src="templates/'+template+'/images/notification.gif">';
	}

	if (info_msg.Flagged == 'F')
	{
		td.innerHTML += '&nbsp;<img id="disposition_important_'+ID+'" style="cursor:pointer" alt="'+ get_lang('Important message') + '" title="'+ get_lang('Important message') + '" src="templates/'+template+'/images/important.png">';
	}
	// NORMAL MSG
	if(info_msg.Draft != 'X')
	{
	var options = document.createElement("TD");
	options.width = "30%";
	options.setAttribute("noWrap","true");
	options.style.fontSize = "10px";
		
		//BEGIN: DESENHA MOSTRA DETALHES, OCULTAR DETALHES
	var option_hide_more = document.createElement("SPAN");
	option_hide_more.className = 'message_options';
        option_hide_more.onmouseover=function () {this.className='message_options_active';};
        option_hide_more.onmouseout=function () {this.className='message_options'};
	options.align = 'right';
	option_hide_more.value = 'more_options';
	option_hide_more.id = 'option_hide_more_'+ID;
	option_hide_more.onclick = function(){
		if (this.value == 'more_options'){
			this.innerHTML = "<b><u>"+get_lang('Hide details')+"</u></b>";
			this.value = 'hide_options';
			Element('table_message_others_options_'+ID).style.display = '';
		}
		else{
			this.innerHTML = get_lang('Show details');
			this.value = 'more_options';
			Element('table_message_others_options_'+ID).style.display = 'none';
		}
		resizeWindow();
	};
		//END: DESENHA MOSTRA DETALHES, OCULTAR DETALHES
		
		//OPCAO PARA MARCAR COMO
		var option_mark_as = document.createElement("SPAN");
		option_mark_as.innerHTML = "<b>"+get_lang('Mark as')+"</b>";
		option_mark_as.className = 'message_options';
		option_mark_as.onmouseover=function () {this.className='message_options_active';};
		option_mark_as.onmouseout=function () {this.className='message_options'};
		option_mark_as.onclick = function(){
			verifyOption('tr_other_mark_options_', ID);
			var thi = document.getElementById('msg_opt_mark_options_'+ID);
			if (thi.value != 'hide'){
				thi.value = 'hide';
				option_mark_as_options.src= '../expressoMail/templates/default/images/pressed.png';
				Element('tr_other_mark_options_'+ID).style.display = '';

			}
			else{
				thi.value = 'show';
				option_mark_as_options.src= '../expressoMail/templates/default/images/down.png';
				Element('tr_other_mark_options_'+ID).style.display = 'none';
			}
		};
		//DESENHA OPCOES DO MARCAR COMO
	var option_mark_as_unseen = document.createElement("SPAN");
		option_mark_as_unseen.className = "reply_options";
	    option_mark_as_unseen.onclick = function () {changeLinkState(this,'seen');
		proxy_mensagens.proxy_set_message_flag(folder_id,'unseen',null,ID.split("_r_")[0]);
		write_msg(get_lang('Message marked as ')+get_lang("Unseen"));
	};
		option_mark_as_unseen.onmouseover=function () {this.className='reply_options_active'};
		option_mark_as_unseen.onmouseout=function () {this.className='reply_options'};
	option_mark_as_unseen.innerHTML = get_lang("Unseen");

	var option_mark_important = document.createElement("SPAN");
		option_mark_important.className = 'reply_options';		
		option_mark_important.onmouseover=function () {this.className='reply_options_active';};
		option_mark_important.onmouseout=function () {this.className='reply_options'};

	if (info_msg.Flagged == "F"){
		option_mark_important.onclick = function() {
			var _this = this;
            changeLinkState(_this, 'important');
			proxy_mensagens.proxy_set_message_flag(folder_id,'unflagged',null,ID.split("_r_")[0]);
            write_msg(get_lang('Message marked as ')+get_lang("Normal"));
		};
        option_mark_important.innerHTML = get_lang("Normal");
	}
	else{
		option_mark_important.onclick = function() {changeLinkState(this,'normal',null,ID.substr(0, ID.length-2));
			proxy_mensagens.proxy_set_message_flag(folder_id,'flagged',null,ID.split("_r_")[0]);
			write_msg(get_lang('Message marked as ')+get_lang("Important"));
		};
		option_mark_important.innerHTML = get_lang("Important");
	}
		//option_mark.appendChild(option_mark_as_unseen);
		//option_mark.appendChild(option_mark_important);

		
	option_hide_more.innerHTML = get_lang('Show details');
		option_hide_more.title = (preferences.use_shortcuts == '1') ? get_lang('Shortcut: %1', 'O') : get_lang('Show details');
	
		var space3 = document.createElement("SPAN");
		space3.innerHTML = '&nbsp;|&nbsp;';
		
		var option_mark_as_options = document.createElement('IMG');
		option_mark_as_options.id = 'msg_opt_mark_options_'+ID;
		option_mark_as_options.src = '../expressoMail/templates/default/images/down.png';
		option_mark_as_options.value = 'show';

		option_mark_as_options.onmouseover = function(){
			option_mark_as_options.src= '../expressoMail/templates/default/images/over.png';
			option_mark_as.className = 'message_options_active';
		};
		option_mark_as_options.onmouseout = function(){
			option_mark_as.className = 'message_options';
			if (this.value == 'show')
			{
				option_mark_as_options.src= '../expressoMail/templates/default/images/down.png';
			}
			else
			{
				option_mark_as_options.src= '../expressoMail/templates/default/images/pressed.png';
			}
		};
		option_mark_as_options.onclick = function(){
			verifyOption('tr_other_mark_options_', ID);
			if (this.value != 'hide'){
				this.value = 'hide';
				option_mark_as_options.src= '../expressoMail/templates/default/images/pressed.png';
				Element('tr_other_mark_options_'+ID).style.display = '';

			}
			else{
				this.value = 'show';
				option_mark_as_options.src= '../expressoMail/templates/default/images/down.png';
				Element('tr_other_mark_options_'+ID).style.display = 'none';
			}
		};
		
	options.appendChild(option_hide_more);
		options.appendChild(space3);
		options.appendChild(option_mark_as_options);				
		options.appendChild(option_mark_as);
		//FIM OPCAO PARA MARCAR COMO

	var space0 = document.createElement("SPAN");
	space0.innerHTML = '&nbsp;|&nbsp;';
	var space1 = document.createElement("SPAN");
	space1.innerHTML = '&nbsp;|&nbsp;';
	var space2 = document.createElement("SPAN");
	space2.innerHTML = '&nbsp;|&nbsp;';

		//OPCAO PARA MAIS ACOES
		var option_more = document.createElement("SPAN");
		option_more.id = 'msg_opt_more_actions_'+ID;
		option_more.className = 'message_options';
		//option_more.onclick = function(){new_message('forward', ID);};
		option_more.onmouseover=function () {this.className='message_options_active';};
        option_more.onmouseout=function () {this.className='message_options'};
		option_more.onclick = function(){
			verifyOption('tr_other_more_options_', ID);
			if (option_more_options.value != 'hide'){
				option_more_options.value = 'hide';
				option_more_options.src= '../expressoMail/templates/default/images/pressed.png';
				Element('tr_other_more_options_'+ID).style.display = '';

			}
			else{
				option_more_options.value = 'show';
				option_more_options.src= '../expressoMail/templates/default/images/down.png';
				Element('tr_other_more_options_'+ID).style.display = 'none';
			}
		};
		option_more.innerHTML = get_lang('More Actions');
		
		//CRIA IMG DE MAIS ACOES
		var option_more_options = document.createElement('IMG');
		option_more_options.id = 'msg_opt_more_options_'+ID;
		option_more_options.src = '../expressoMail/templates/default/images/down.png';
		option_more_options.value = 'show';

		option_more_options.onmouseover = function(){
			option_more.className = 'message_options_active';
			option_more_options.src= '../expressoMail/templates/default/images/over.png';
		};
		option_more_options.onmouseout = function(){
			option_more.className = 'message_options';
			if (this.value == 'show')
			{
				option_more_options.src= '../expressoMail/templates/default/images/down.png';
			}
			else
			{
				option_more_options.src= '../expressoMail/templates/default/images/pressed.png';
			}
		};
		option_more_options.onclick = function(){
			verifyOption('tr_other_more_options_',ID);
			
			if (this.value != 'hide'){
				this.value = 'hide';
				option_more_options.src= '../expressoMail/templates/default/images/pressed.png';
				Element('tr_other_more_options_'+ID).style.display = '';

			}
			else{
				this.value = 'show';
				option_more_options.src= '../expressoMail/templates/default/images/down.png';
				Element('tr_other_more_options_'+ID).style.display = 'none';
			}
		};
	options.appendChild(space1);
		options.appendChild(option_more_options);
		options.appendChild(option_more);
		//FIM OPCAO PARA MAIS ACOES
		
		//OPCAO PARA RESPONDER
	var option_reply = document.createElement("SPAN");
	option_reply.id = 'msg_opt_reply_'+ID;
	option_reply.className = 'message_options';
	option_reply.onclick = function(){
		
		new_message(($.cookie("option_reply")) ? $.cookie("option_reply") : "reply_with_history", ID);
		
	};
	option_reply.innerHTML = get_lang('Reply');
	option_reply.onmouseover=function () {this.className='message_options_active';};
	option_reply.onmouseout=function () {this.className='message_options'};
		option_reply.title = (preferences.use_shortcuts == '1') ? get_lang('Shortcut: %1', 'R') : get_lang('Reply');

	options.appendChild(space2);

	var option_reply_options = document.createElement('IMG');
	option_reply_options.id = 'msg_opt_reply_options_'+ID;
	option_reply_options.src = '../expressoMail/templates/default/images/down.png';
	option_reply_options.value = 'show';

	option_reply_options.onmouseover = function(){
		option_reply_options.src= '../expressoMail/templates/default/images/over.png';
	};
	option_reply_options.onmouseout = function(){
		if (this.value == 'show')
		{
			option_reply_options.src= '../expressoMail/templates/default/images/down.png';
		}
		else
		{
			option_reply_options.src= '../expressoMail/templates/default/images/pressed.png';
		}
	};
	option_reply_options.onclick = function(){
			verifyOption('tr_other_options_', ID);
		if (this.value != 'hide'){
			this.value = 'hide';
			option_reply_options.src= '../expressoMail/templates/default/images/pressed.png';
			Element('tr_other_options_'+ID).style.display = '';

		}
		else{
			this.value = 'show';
			option_reply_options.src= '../expressoMail/templates/default/images/down.png';
			Element('tr_other_options_'+ID).style.display = 'none';
		}
	};
	options.appendChild(option_reply_options);
	options.appendChild(option_reply);
		//FIM OPCAO PARA RESPONDER
		
		//OPCAO PARA ENCAMINHAR
		var option_forward = document.createElement("SPAN");
		option_forward.id = 'msg_opt_forward_'+ID;
		option_forward.className = 'message_options';
		option_forward.innerHTML = get_lang('Forward');
		option_forward.onclick = function(){new_message('forward', ID);};
		option_forward.onmouseover=function () {this.className='message_options_active';};
        option_forward.onmouseout=function () {this.className='message_options'};
		option_forward.title = (preferences.use_shortcuts == '1') ? get_lang('Shortcut: %1', 'E') : get_lang('Forward');
		
		var space9 = document.createElement("SPAN");
		space9.innerHTML = '&nbsp;|&nbsp;';
		
		options.appendChild(space9);
		options.appendChild(option_forward);
		//FIM DA OPCAO PARA ENCAMINHAR
		
		//OPCAO PARA DELETAR
		var option_delete = document.createElement("SPAN");
		option_delete.id = 'msg_opt_delete_'+ID;
		option_delete.className = 'message_options';
		option_delete.onclick = function(){proxy_mensagens.delete_msgs('null','selected','null', info_msg.prev_message);};
		option_delete.innerHTML = get_lang('Delete');
		option_delete.onmouseover=function () {this.className='message_options_active';};
		option_delete.onmouseout=function () {this.className='message_options'};
		option_delete.title = (preferences.use_shortcuts == '1') ? get_lang('Shortcut: %1', 'Delete') : get_lang('Delete');

		var space6 = document.createElement("SPAN");
		space6.innerHTML = '&nbsp;|&nbsp;';
		
		options.appendChild(space6);
		options.appendChild(option_delete);
		//FIM OPCAO PARA DELETAR

	tr.appendChild(td);
		//tr.appendChild(option_mark);
	tr.appendChild(options);
	tr.appendChild(next_previous_msg_td);
	tbody_message_options.appendChild(tr);

	////////// OTHER OPTIONS ////////////////////
	var tr_other_options = document.createElement("TR");
		var tr_other_mark_options = document.createElement("TR");
		var tr_other_more_options = document.createElement("TR");
		
		tr_other_mark_options.id = 'tr_other_mark_options_' + ID;
		tr_other_mark_options.style.display = 'none';
		tr_other_mark_options.style.backgroundColor = '#205C8E';
	tr_other_options.id = 'tr_other_options_' + ID;
	tr_other_options.style.display = 'none';
		tr_other_options.style.backgroundColor = '#205C8E';
		tr_other_more_options.id = 'tr_other_more_options_' + ID;
		tr_other_more_options.style.display = 'none';
		tr_other_more_options.style.backgroundColor = '#205C8E';		

	var td_other_options = document.createElement("TD");
		var td_other_mark_options = document.createElement("TD");
		var td_other_more_options = document.createElement("TD");
		
	td_other_options.colSpan = '3';
		td_other_mark_options.colSpan = '3';
		td_other_more_options.colSpan = '3';

	var div_other_options = document.createElement("DIV");
		var div_other_mark_options = document.createElement("DIV");
		var div_other_more_options = document.createElement("DIV");

		//var option_mark_as_unseen = '<span class="message_options" onclick="proxy_mensagens.proxy_set_messages_flag(\'unseen\','+info_msg.msg_number+');write_msg(\''+get_lang('Message marked as ')+get_lang("Unseen")+'.\');">'+get_lang("Unseen")+'</span>, ';
		//var option_mark_as_important			= '<span class="message_options" onclick="proxy_mensagens.proxy_set_messages_flag(\'flagged\','+info_msg.msg_number+');write_msg(\''+get_lang('Message marked as ')+get_lang("Important")+'.\');">'+get_lang("Important")+'</span>, ';
		//var option_mark_as_normal				= '<span class="message_options" onclick="proxy_mensagens.proxy_set_messages_flag(\'unflagged\','+info_msg.msg_number+');write_msg(\''+get_lang('Message marked as ')+get_lang("Normal")+'.\');">'+get_lang("Normal")+'</span> | ';
		var block_user = '<span onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options" onclick ="block_user_email(\''+info_msg.from.email+'\');">'+get_lang("Block Sender")+'</span> | ';	
		//var option_forward = '<span onclick="new_message(\'forward\',\''+ ID+'\');" onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options">'+get_lang("Forward")+'</span> | ';
		var option_move	= '<span onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options" onclick=wfolders.makeWindow("'+ID+'","move_to");>'+get_lang("Move")+'</span> | ';
		var option_print = '<span onclick="print_all()" onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options">'+get_lang("Print")+'</span> | ';
		var option_export = '<span onclick="proxy_mensagens.export_all_messages()" onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options">'+get_lang("Export")+'</span> | ';
		var report_error = '<span onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options" onclick=reports_window("'+currentTab+'");>'+get_lang("Truncated message?")+'</span> | '; 
		// Opção do menu 'Mais Ações' para criar filtro a partir da mensagem aberta:  
		var option_create_filter = '<span onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options" onclick=filter_from_msg(onceOpenedHeadersMessages[\'' + html_entities(info_msg.msg_folder) + '\'][' + info_msg.msg_number + ']);>' + get_lang("Create filter from message") + '</span> | '; 
		div_other_more_options.innerHTML += option_create_filter + option_move + option_print + option_export + block_user +  report_error;
		
		
		// CRIAÇÃO DE OPÇÕES DE RESPONDER MENSAGEM
		var space_replay1 = document.createElement('SPAN');
		space_replay1.innerHTML = '&nbsp;|&nbsp;';
		
		var space_replay2 = document.createElement('SPAN');
		space_replay2.innerHTML = '&nbsp;|&nbsp;';
		
		var space_replay3 = document.createElement('SPAN');
		space_replay3.innerHTML = '&nbsp;|&nbsp;';
		
		// RESPONDER A TODOS
		var option_reply_to_all = document.createElement('SPAN');
		option_reply_to_all.onmouseover = function () {this.className = "reply_options_active";};
		option_reply_to_all.onmouseout = function () {this.className= "reply_options";};
		option_reply_to_all.className = "reply_options";
		option_reply_to_all.onclick = function(){
			new_message('reply_to_all_with_history', ID);
			$.cookie ("option_reply", "reply_to_all_with_history", { expires: 5});
		};
		option_reply_to_all.title = (preferences.use_shortcuts == '1') ? get_lang('Shortcut: %1', 'T') : '';
		option_reply_to_all.innerHTML = get_lang("Reply to all");
		
		// RESPONDER SEM HISTORICO
		var option_reply_without_history = document.createElement('SPAN');
		option_reply_without_history.onmouseover = function () {this.className = "reply_options_active";};
		option_reply_without_history.onmouseout = function () {this.className= "reply_options";};
		option_reply_without_history.className = "reply_options";
		option_reply_without_history.onclick = function(){
			new_message('reply_without_history', ID);
			$.cookie ("option_reply", "reply_without_history", { expires: 5});
		};
		option_reply_without_history.innerHTML = get_lang("Reply without history");
		
		// RESPONDER A TODOS SEM HISTORICO
		var option_reply_to_all_without_history = document.createElement('SPAN');
		option_reply_to_all_without_history.onmouseover = function () {this.className = "reply_options_active";};
		option_reply_to_all_without_history.onmouseout = function () {this.className= "reply_options";};
		option_reply_to_all_without_history.className = "reply_options";
		option_reply_to_all_without_history.onclick = function(){
			new_message('reply_to_all_without_history', ID);
			$.cookie ("option_reply", "reply_to_all_without_history", { expires: 5});
		};
		option_reply_to_all_without_history.innerHTML = get_lang("Reply to all without history");
		
		// RESPONDER COM HISTÓRICO
		var option_reply_with_history = document.createElement('SPAN');
		option_reply_with_history.onmouseover = function () {this.className = "reply_options_active";};
		option_reply_with_history.onmouseout = function () {this.className= "reply_options";};
		option_reply_with_history.className = "reply_options";
		option_reply_with_history.onclick = function(){
			new_message('reply_with_history', ID);
			$.cookie ("option_reply", "reply_with_history", { expires: 5});
		};
		option_reply_with_history.innerHTML = get_lang("Reply with history");
		
		// APENDAR OPÇÕES DE RESPONDER
		div_other_options.appendChild(option_reply_to_all);
		div_other_options.appendChild(space_replay1);
		div_other_options.appendChild(option_reply_to_all_without_history);
		div_other_options.appendChild(space_replay2);
		div_other_options.appendChild(option_reply_with_history);
		div_other_options.appendChild(space_replay3);
		div_other_options.appendChild(option_reply_without_history);

	if (use_spam_filter) {
			if(info_msg.msg_folder == 'INBOX'+cyrus_delimiter+'Spam' || (info_msg.msg_folder.match(/^user/) && info_msg.msg_folder.match(/Spam$/))){
				div_other_mark_options.innerHTML += '<span onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options" onclick="nospam('+info_msg.msg_number+',\'null\', \''+info_msg.msg_folder+'\');">'+get_lang("Not Spam")+'</span> | ';
	}
			else{
				div_other_mark_options.innerHTML += '<span onmouseover="this.className=\'reply_options_active\'" onmouseout="this.className=\'reply_options\'" class="reply_options" onclick="spam(\''+info_msg.msg_folder+'\', '+info_msg.msg_number+',\'null\');">'+get_lang("Spam")+'</span> | ';
			}
		}
		var space5 = document.createElement("SPAN");
		space5.innerHTML = '&nbsp;|&nbsp;';
	
		div_other_mark_options.appendChild(option_mark_as_unseen);
		var space4 = document.createElement("SPAN");
		space4.innerHTML = '&nbsp;|&nbsp;';
		div_other_mark_options.appendChild(space4);
		div_other_mark_options.appendChild(option_mark_important); 
		var space7 = document.createElement("SPAN");
		space7.innerHTML = '&nbsp;|&nbsp;';
		div_other_mark_options.appendChild(space7);
	
	td_other_options.align = 'right';
	td_other_options.style.paddingTop = '3px';
	td_other_options.appendChild(div_other_options);

		td_other_mark_options.align = 'right';
		td_other_mark_options.style.paddingTop = '3px';
		td_other_mark_options.appendChild(div_other_mark_options);
		
		td_other_more_options.align = 'right';
		td_other_more_options.style.paddingTop = '3px';
		td_other_more_options.appendChild(div_other_more_options);


	tr_other_options.appendChild(td_other_options);
	tbody_message_options.appendChild(tr_other_options);
		
		tr_other_more_options.appendChild(td_other_more_options);
		tbody_message_options.appendChild(tr_other_more_options);
		
		tr_other_mark_options.appendChild(td_other_mark_options);
		tbody_message_options.appendChild(tr_other_mark_options);
	////////// END OTHER OPTIONS ////////////////

		////////// BEGIN SIGNATURE //////////////////
	if (info_msg.signature && info_msg.signature.length > 0)
	{
            var tr_signature = document.createElement("TR");
            var td_signature = document.createElement("TD");
            td_signature.className = 'tr_message_header';
            tr_signature.id = 'tr_signature_'+ID;
            td_signature.colSpan = "5";
            tr_signature.style.display = 'none';
            for (i in info_msg.signature)
                {
                    if(typeof(info_msg.signature[i]) == 'object')
                        {
                            var aux = '';
                            for (ii in info_msg.signature[i])
                                {
                                    if(info_msg.signature[i][ii].indexOf("###") > -1)
                                        {
                                         aux += get_lang(info_msg.signature[i][ii].substring(0,info_msg.signature[i][ii].indexOf("###"))) + info_msg.signature[i][ii].substring(info_msg.signature[i][ii].indexOf("###")+3);
                                        }
                                    else
                                        {
                                         aux += info_msg.signature[i][ii];
                                        }
                                }
                            td_signature.innerHTML += "<a onclick=\"javascript:alert('" + aux + "')\"><b><font color=\"#0000FF\">" + get_lang("More") + "...</font></b></a>";
                            continue;
                        }
                    if(info_msg.signature[i].indexOf("#@#") > -1)
                        {
                         td_signature.innerHTML += '<span style=color:red><strong>'+get_lang(info_msg.signature[i].substring(0,info_msg.signature[i].indexOf("#@#")))+'</strong> '+info_msg.signature[i].substring(info_msg.signature[i].indexOf("#@#")+3)+'</span> <br /> ';
                        }
                            if(info_msg.signature[i].indexOf("###") > -1)
                                {
                                    td_signature.innerHTML += '<span><strong>'+get_lang(info_msg.signature[i].substring(0,info_msg.signature[i].indexOf("###")))+'</strong> '+info_msg.signature[i].substring(info_msg.signature[i].indexOf("###")+3)+'</span> <br /> ';
                                }
                }
            var signature_status_pos = info_msg.signature[0].indexOf('Message untouched');
            td_signature.id = "td_signature_"+ID;
            if(signature_status_pos < 0 )
                {
                    td.innerHTML += '&nbsp;<img style="cursor:pointer" src="templates/'+template+'/images/signed_error.gif" title="'+get_lang("Details")+'">';
                    tr_signature.style.display = '';
                }
            else
                {
                    td.innerHTML += '&nbsp;<img style="cursor:pointer" src="templates/'+template+'/images/signed_table.gif" title="'+get_lang("Details")+'">';
                }
            td.onclick = function(){
            var _height = Element("div_message_scroll_"+ID).style.height;
            _height = parseInt(_height.replace("px",""));
            var _offset = 130;
            if (this.value == 'more_cert'){
                this.value = 'hide_cert';
                Element("div_message_scroll_"+ID).style.height = (_height + _offset)+"px";
                Element('tr_signature_'+ID).style.display = 'none';
                Element('td_signature_'+ID).style.display = 'none';

            }
            else{
                this.value = 'more_cert';
                Element("div_message_scroll_"+ID).style.height = (_height - _offset)+"px";
                Element('tr_signature_'+ID).style.display = '';
                Element('td_signature_'+ID).style.display = '';
            }
	};

            tr_signature.appendChild(td_signature);
            tbody_message_options.appendChild(tr_signature);
	}
	//////////// END SIGNATURE ////////////////

	table_message_options.appendChild(tbody_message_options);
	td0.appendChild(table_message_options);
	tr0.appendChild(td0);
	tbody_message.appendChild(tr0);
	}
	// IF DRAFT
	else
	{
		var options = document.createElement("TD");
		//options.width = "1%";
		options.setAttribute("noWrap","true");
		var option_edit	  = ' | <span class="message_options" onclick="new_message(\'edit\',\''+ID+'\',\''+info_msg.Flagged+'\');">'+get_lang('Edit')+'</span>';
		var option_print = ' | <span class="message_options" onclick="print_msg(\''+info_msg.msg_folder+'\',\''+info_msg.msg_number+'\',\''+ID+'\');">'+get_lang('Print')+'</span>';
		var option_hide_more = document.createElement("SPAN");
		option_hide_more.className = 'message_options';
		options.align = 'right';
		option_hide_more.value = 'more_options';
		option_hide_more.id = 'option_hide_more_'+ID;
		option_hide_more.innerHTML = get_lang('Show details');
		option_hide_more.onclick = function(){
			var _height = Element("div_message_scroll_"+ID).style.height;
			_height = parseInt(_height.replace("px",""));
			var _offset = 35;
			if (this.value == 'more_options'){
				this.innerHTML = "<b><u>"+get_lang('Hide details')+"</u></b>";
				this.value = 'hide_options';
				Element("div_message_scroll_"+ID).style.height = (_height - _offset)+"px";
				Element('table_message_others_options_'+ID).style.display = '';
			}
			else{
				this.innerHTML = get_lang('Show details');
				this.value = 'more_options';
				Element("div_message_scroll_"+ID).style.height = (_height + _offset)+"px";
				Element('table_message_others_options_'+ID).style.display = 'none';
			}
		};
		options.appendChild(option_hide_more);
		options_actions = document.createElement('SPAN');
		options_actions.innerHTML = option_edit + option_print;
		options.appendChild(options_actions);
		tr.appendChild(td);
		tr.appendChild(options);
		tr.appendChild(next_previous_msg_td);
		tbody_message_options.appendChild(tr);
		table_message_options.appendChild(tbody_message_options);
		td0.appendChild(table_message_options);
		tr0.appendChild(td0);
		tbody_message.appendChild(tr0);

		var important_message = document.createElement("INPUT");
		important_message.id = "is_important_"+ID;
		important_message.name = "is_important";
		important_message.type = "HIDDEN";
		important_message.value = (info_msg.Importance == "" || info_msg.Importance == "Normal") ? "0": "1";

		options.appendChild(important_message);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	// END options message.
	//////////////////////////////////////////////////////////////////////////////////////////////////////

	var table_message_others_options = document.createElement("TABLE");
	table_message_others_options.id = 'table_message_others_options_' + ID;
	table_message_others_options.width = "100%";
	table_message_others_options.style.display = 'none';
	if(navigator.appName.indexOf('Internet Explorer')>0){
		table_message_others_options.className = "table_message_options_ie";
	}else{
		table_message_others_options.className = "table_message_options";
	}
	
	var tbody_message_others_options = document.createElement("TBODY");
	var tr1 = document.createElement("TR");
	tr1.className = "tr_message_header";
    if(info_msg.from){
    	var td1 = document.createElement("TD");
    	td1.innerHTML = get_lang("From: ");
    	td1.appendChild(deny_email(info_msg.from.email));
    	td1.width = "7%";
    }

	if (info_msg.sender){
		var tr111 = document.createElement("TR");
		tr111.className = "tr_message_header";
		var td111 = document.createElement("TD");
		td111.innerHTML = get_lang("Sent by")+": ";
		td111.appendChild(deny_email(info_msg.sender.email));
		td111.setAttribute("noWrap","true");
		var sender = document.createElement("TD");
		sender.id = "sender_"+ID;
		var sender_values = document.createElement("INPUT");
		sender_values.id = "sender_values_"+ID;
		sender_values.type = "HIDDEN";
		sender_values.value = info_msg.sender.full; //Veio do IMAP, sem images nem links.
		sender.innerHTML += draw_plugin_cc(ID, info_msg.sender.full);
		sender.className = "header_message_field";
		tr111.appendChild(td111);
		tr111.appendChild(sender);
		tr111.appendChild(sender_values);
		tbody_message_others_options.appendChild(tr111);
	}

	var from = document.createElement("TD");
	from.id = "from_"+ID;
    if(info_msg.from){
	   from.innerHTML = info_msg.from.full;
    }
	if (info_msg.Draft != "X"){
		from.innerHTML += draw_plugin_cc(ID, info_msg.from);
		tbody_message_others_options.appendChild(tr1);
	}
	from.className = "header_message_field";
	var from_values = document.createElement("INPUT");
	from_values.id = "from_values_"+ID;
	from_values.type = "HIDDEN";
    if(info_msg.from){
    	from_values.value = info_msg.from.full; //Veio do IMAP, sem images nem links.
    }

	var local_message = document.createElement("INPUT");
	local_message.id = "is_local_"+ID;
	local_message.name = "is_local";
	local_message.type = "HIDDEN";
	local_message.value = (info_msg.local_message)?"1":"0";

    if(info_msg.from){
	   tr1.appendChild(td1);
    }
	tr1.appendChild(from);
	tr1.appendChild(from_values);
	tr1.appendChild(local_message);

	if (info_msg.reply_to){
		var tr11 = document.createElement("TR");
		tr11.className = "tr_message_header";
		var td11 = document.createElement("TD");
		td11.innerHTML = get_lang("Reply to")+": ";
		td11.setAttribute("noWrap","true");
		var reply_to = document.createElement("TD");
		reply_to.id = "reply_to_"+ID;

		var reply_to_values = document.createElement("INPUT");
		reply_to_values.id = "reply_to_values_"+ID;
		reply_to_values.type = "HIDDEN";
		reply_to_values.value = info_msg.reply_to; //Veio do IMAP, sem images nem links.
		$.each(break_comma(info_msg.reply_to), function(index, value){
			reply_to.innerHTML += draw_plugin_cc(ID, value);	
		})
		reply_to.className = "header_message_field";
		tr11.appendChild(td11);
		tr11.appendChild(reply_to);
		tr11.appendChild(reply_to_values);
		tbody_message_others_options.appendChild(tr11);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	var tr2 = document.createElement("TR");
	tr2.className = "tr_message_header";
	var td2 = document.createElement("TD");
	td2.width = "7%";
	td2.innerHTML = get_lang("To: ");
	var to = document.createElement("TD");
	to.id = "to_"+ID;

	var to_values = document.createElement("INPUT");
	to_values.id = "to_values_"+ID;
	to_values.type = "HIDDEN";
	to_values.value = info_msg.toaddress2; //Veio do IMAP, sem images nem links.
	// Salva a pasta da mensagem
	var input_current_folder = document.createElement('input');
	input_current_folder.id = "input_folder_"+ID;
	input_current_folder.name = "input_folder";
	input_current_folder.type = "hidden";
	input_current_folder.value = info_msg.msg_folder;
	td2.appendChild(input_current_folder);
	// fim
	// ALEXANDRE LUIZ CORREIA
	if(info_msg.toaddress2 != null)
	{
		toaddress_array[ID] = break_comma(info_msg.toaddress2);
		var notValidUser = false;
		if (toaddress_array[ID].length > 1)
		{
			to.innerHTML += draw_plugin_cc(ID, toaddress_array[ID][0]);
			var div_toaddress = document.createElement("SPAN");
			div_toaddress.id = "div_toaddress_"+ID;
			div_toaddress.style.display="";
			div_toaddress.innerHTML += " (<a STYLE='color: RED;' onclick=javascript:show_div_address_full('"+ID+"','to');>"+get_lang('more')+"</a>)";
			to.appendChild(div_toaddress);
		}
		else
		{
			toAdd = toaddress_array[ID].toString()
			if( trim(toAdd) != "" ) {
				toAdd = toAdd.replace("<","&lt;").replace(">","&gt;");
			} else {
				toAdd = get_lang("without destination");
				notValidUser = true;
			}

			to.innerHTML += draw_plugin_cc(ID,toAdd, notValidUser, notValidUser);
		}

		to.className = "header_message_field";
		tr2.appendChild(td2);
		tr2.appendChild(to);
		tr2.appendChild(to_values);
	}

	tbody_message_others_options.appendChild(tr2);

	if (info_msg.cc){
		var tr3 = document.createElement("TR");
		tr3.className = "tr_message_header";
		var td3 = document.createElement("TD");
		td3.innerHTML = "CC: ";
		var cc = document.createElement("TD");
		cc.id = "cc_"+ID;

		var cc_values = document.createElement("INPUT");
		cc_values.id = "cc_values_"+ID;
		cc_values.type = "HIDDEN";
		cc_values.value = info_msg.cc;

		ccaddress_array[ID] = break_comma(info_msg.cc);
		if (ccaddress_array[ID].length > 1){
			var div_ccaddress = document.createElement("SPAN");
			div_ccaddress.id = "div_ccaddress_"+ID;
			var div_ccaddress_full = document.createElement("SPAN");
			div_ccaddress_full.id = "div_ccaddress_full_"+ID;
			div_ccaddress.style.display="";
			cc.innerHTML = draw_plugin_cc(ID, ccaddress_array[ID][0]);
			div_ccaddress.innerHTML += " (<a STYLE='color: RED;' onclick=javascript:show_div_address_full('"+ID+"','cc');>"+get_lang('more')+"</a>)";
			cc.appendChild(div_ccaddress);
		}
		else{
			cc.innerHTML = draw_plugin_cc(ID, info_msg.cc);
		}
		cc.className = "header_message_field";
		tr3.appendChild(td3);
		tr3.appendChild(cc);
		tr3.appendChild(cc_values);
		tbody_message_others_options.appendChild(tr3);
	}

	/*
	 * @AUTHOR Rodrigo Souza dos Santos
	 * @MODIFY-DATE 2008/09/11
	 * @BRIEF Adding routine to create bcc field if there is one.
	 */
	if (info_msg.bcc)
	{
		var tr3 = document.createElement("tr");
		tr3.className = "tr_message_header";
		var td3 = document.createElement("td");
		td3.innerHTML = get_lang("BCC") + " : ";
		var cco = document.createElement("td");
		cco.id = "cco_"+ID;

		var cco_values = document.createElement("input");
		cco_values.id = "cco_values_"+ID;
		cco_values.type = "hidden";
		cco_values.value = info_msg.bcc;

		ccoaddress_array[ID] = info_msg.bcc.split(",");
		if (ccoaddress_array[ID].length > 1){
			var div_ccoaddress = document.createElement("SPAN");
			div_ccoaddress.id = "div_ccoaddress_"+ID;
			var div_ccoaddress_full = document.createElement("SPAN");
			div_ccoaddress_full.id = "div_ccoaddress_full_"+ID;
			div_ccoaddress.style.display="";

			//cco.innerHTML = draw_plugin_cc(ID, ccoaddress_array[ID][0]);
			cco.innerHTML = ccoaddress_array[ID][0];
			div_ccoaddress.innerHTML += " (<a STYLE='color: RED;' onclick=javascript:show_div_address_full('"+ID+"','cco');>"+get_lang('more')+"</a>)";
			cco.appendChild(div_ccoaddress);
		}
		else{
			//cco.innerHTML = draw_plugin_cc(ID, info_msg.cco);
			cco.innerHTML = info_msg.bcc;
		}
		cco.className = "header_message_field";
		tr3.appendChild(td3);
		tr3.appendChild(cco);
		tr3.appendChild(cco_values);
		tbody_message_others_options.appendChild(tr3);
	}

	var tr4 = document.createElement("TR");
	tr4.className = "tr_message_header";
	var td4 = document.createElement("TD");
	td4.innerHTML = get_lang("Date: ");
	var date = document.createElement("TD");
	date.id = "date_"+ID;
	date.innerHTML = info_msg.fulldate;
	var date_day = document.createElement("INPUT");
	date_day.id = "date_day_"+ID;
	date_day.type = "HIDDEN";
	date_day.value = info_msg.msg_day;
	var date_hour = document.createElement("INPUT");
	date_hour.id = "date_hour_"+ID;
	date_hour.type = "HIDDEN";
	date_hour.value = info_msg.msg_hour
	date.className = "header_message_field";
	tr4.appendChild(td4);
	tr4.appendChild(date);
	tr4.appendChild(date_day);
	tr4.appendChild(date_hour);
	tbody_message_others_options.appendChild(tr4);

	var tr5 = document.createElement("TR");
	tr5.className = "tr_message_header";
	var td5 = document.createElement("TD");
	td5.innerHTML = get_lang("Subject");
	var subject = document.createElement("TD");
	subject.id = "subject_"+ID;
	subject.innerHTML = html_entities(info_msg.subject); 
	subject.className = "header_message_field";
	if($("#expressoCalendarid")[0]){
		var new_task_logo = document.createElement("IMG");
		new_task_logo.title = "Criar uma nova tarefa a partir deste email.";
		new_task_logo.alt = "Criar uma nova tarefa a partir deste email.";
		new_task_logo.src = "./templates/default/images/big-task.png";
		new_task_logo.style.cursor = "pointer";
		new_task_logo.style.marginLeft = "5px";
		new_task_logo.onclick = function(){
		import_implements_calendar();
		
		
			DataLayer.dispatchPath = "../prototype/";
			var path = "../prototype/modules/calendar/";
			taskDetails(decodeCreateSchedulable('task', ID), true, path, true);
		}
		
		var new_event_logo = document.createElement("IMG");
		new_event_logo.title = "Criar evento a partir deste email";
		new_event_logo.alt = "Criar evento a partir deste email";
		//new_event_logo.src = "./templates/default/images/calendar_add.png";
		new_event_logo.src = "./templates/default/images/big-event.png";
		new_event_logo.style.cursor = "pointer";
		new_event_logo.style.marginLeft = "5px";
		new_event_logo.onclick = function(){
		import_implements_calendar();
		
		
			DataLayer.dispatchPath = "../prototype/";
			var path = "../prototype/modules/calendar/";

			eventDetails(decodeCreateSchedulable('event', ID), true, path, true);
		}
		subject.appendChild(new_event_logo);
		subject.appendChild(new_task_logo);
	}
	tr5.appendChild(td5);
	tr5.appendChild(subject);
	tbody_message_others_options.appendChild(tr5);
	//k!
	
	
	var update_labeleds_msg = function(){
		//TODO Mudar quando API abstrair atualizações no cache
		DataLayer.remove('labeled', false);
		//DataLayer.get('labeled');
		var labels = DataLayer.get("labeled", {filter: [
				'AND',
					['=', 'folderName', current_folder], 
					['=','messageNumber',folder_id]], 
				criteria : {deepness: 2}} );
		
		if(labels.length != 0){
			var tr8 = document.createElement("TR");
			tr8.className = "tr_message_header";
			var td8 = document.createElement("TD");
			td8.innerHTML = get_lang("Labels: ");
			var markers = document.createElement("TD");
			markers.id = "markers_"+ID;
			
			for(var i=0; i<labels.length; i++){
				fontColor = labels[i].label.fontColor;
				borderColor = labels[i].label.borderColor;
				backgroundColor = labels[i].label.backgroundColor;
				nameLabel = labels[i].label.name;
				id = labels[i].id;
				markers.innerHTML+= "<div  style='height: 15px; background:"+backgroundColor+"; float: left; -webkit-border-radius: 3px; -moz-border-radius: 3px; margin:0 0 1px 1px; border: 1px solid "+borderColor+"'><span style='color: "+fontColor+"; margin: 5px;'>"+nameLabel+"</span><span class='removeLabeledMsg' id='"+id+"' title='"+get_lang("Remove Label")+"'>x</span></div>";
				
			}
			$(markers).find('span.removeLabeledMsg').click(function(event){
				var id_labeled = $(event.target).attr("id");
				//TODO Mudar quando API abstrair atualizações no cache
				//DataLayer.remove('labeled', false);
				//DataLayer.get('labeled');
				DataLayer.remove('labeled', id_labeled);
				DataLayer.commit(false, false, function(){
					var index_folder = id_labeled.lastIndexOf('/');
					var folder_name = id_labeled.slice(0,index_folder);
					var index_number = id_labeled.lastIndexOf('#');
					var msg_number = id_labeled.slice(index_folder + 1,index_number);
					updateLabelsColumn({msg_number:msg_number, boxname:folder_name, labels:false});
					update_labeleds_msg();
					tbody_message_others_options.removeChild(tr8);
				});
				
			});
			markers.className = "header_message_field";
			tr8.appendChild(td8);
			tr8.appendChild(markers);
			tbody_message_others_options.appendChild(tr8);
		}
	}

	if ( info_msg.attachments && info_msg.attachments.length > 0 )
	{
		//Código no padrão expresso 2.2
	var tr6 = document.createElement("TR");
		tr6.className = "tr_message_header";
		var td6 = document.createElement("TD");
		td6.innerHTML = get_lang("Attachments: ");
		
		
		var attachments = document.createElement("TD");
		td6.valign = "top";
		attachments.align = 'left';
		if(info_msg.attachments.length >= 1){
			if(info_msg.attachments.length > 1) {
				var link_attachment	 = document.createElement("A");
				 if(proxy_mensagens.is_local_folder(current_folder))
					link_attachment.setAttribute("href", "javascript:expresso_local_messages.download_all_local_attachments('"+info_msg.msg_folder+"','"+info_msg.msg_number+"')");
				else
					link_attachment.setAttribute("href", "javascript:download_all_attachments('"+info_msg.msg_folder+"','"+info_msg.msg_number+"')");
				link_attachment.innerHTML = " "+info_msg.attachments.length+' '+get_lang('files')+' :: '+get_lang('Download all atachments');
                    attachments.appendChild(link_attachment);
			}
			if(parseInt(preferences.remove_attachments_function))
			{
                    attachments.appendChild(document.createTextNode('  '));
                    var del_attachments = document.createElement("A");
                    del_attachments.setAttribute("href", "javascript:remove_all_attachments('"+info_msg.msg_folder+"','"+info_msg.msg_number+"')");
                    del_attachments.innerHTML = get_lang('remove all attachments');
                    attachments.appendChild(del_attachments);
			}
                attachments.appendChild(document.createElement('BR'));
        }
		attachments.id = "attachments_" + ID;
		var parserImport = false;
		for (var i=0; i<info_msg.attachments.length; i++)
		{
			var import_url = '$this.db_functions.import_vcard&msg_folder='+info_msg.msg_folder+"&msg_number="+info_msg.msg_number+"&msg_part="+info_msg.attachments[i].pid+"&idx_file="+i+"&encoding="+info_msg.attachments[i].encoding;
			var link_attachment = document.createElement("a");
			link_attachment.setAttribute("class", "type_images");
			link_attachment.style.display = "block";
			link_attachment.setAttribute("href", proxy_mensagens.link_anexo(info_msg,i));		
			link_attachment.innerHTML = url_decode(info_msg.attachments[i].name) + " ("+borkb(info_msg.attachments[i].fsize)+")";
			//link_attachment.innerHTML += " ("+borkb(info_msg.attachments[i].fsize)+")";

			//k trocar por match???
			if((url_decode(info_msg.attachments[i].name).indexOf(".ics")!=-1) || (url_decode(info_msg.attachments[i].name).indexOf(".vcard")!=-1))
			{
				//Link para importar calendário
				var link_import_attachment = new Image();
				link_import_attachment.src = "templates/"+template+"/images/new.png";
				link_import_attachment.setAttribute("onclick","javascript:import_calendar('"+info_msg.msg_folder+"&msg_number="+info_msg.msg_number+"&msg_part="+info_msg.attachments[i].pid+"&idx_file="+i+"&encoding="+info_msg.attachments[i].encoding+"'); return false;");
				link_import_attachment.title = get_lang("Import to calendar");
				link_import_attachment.style.display = "inline";
				link_import_attachment.align = "top";
				link_import_attachment.style.marginLeft = "5px";
				link_import_attachment.style.cursor = "pointer";
				link_attachment.appendChild(link_import_attachment);
				parserImport = true;
			}

            if((url_decode(info_msg.attachments[i].name).indexOf(".eml") != -1))
			{
				//Link para importar calendário
				var link_open_msg = new Image();
				link_open_msg.src = "templates/"+template+"/images/email.png";
				//link_open_msg.setAttribute("onclick","javascript:import_calendar('"+info_msg.msg_folder+"&msg_number="+info_msg.msg_number+"&msg_part="+info_msg.attachments[i].pid+"&idx_file="+i+"&encoding="+info_msg.attachments[i].encoding+"'); return false;");
				link_open_msg.setAttribute("onclick","javascript:open_msg_part('"+info_msg.msg_folder+"&msg_number="+info_msg.msg_number+"&msg_part="+info_msg.attachments[i].pid+"'); return false;");
				link_open_msg.title = get_lang("Open message");
				link_open_msg.align = "top";
				link_open_msg.style.marginLeft = "5px";
				link_open_msg.style.cursor = "pointer";
				link_attachment.appendChild(link_open_msg);
			}
		
            //link_attachment.innerHTML += '<br/>';
            attachments.appendChild(link_attachment);
            }
		tr6.appendChild(td6);
        tr6.appendChild(attachments);
		tbody_message_others_options.appendChild(tr6);
	}

	if (parserImport){
		$.ajax({
			url: "controller.php?action="+import_url+'&from_ajax=true&id_user='+User.me.id+'&readable=true&cirus_delimiter='+cyrus_delimiter+'&analize=true&uidAccount='+decodeOwner(),
			async: true,
			success: function(data){
				data = connector.unserialize(data);

				if(typeof(data) == "object"){
				    var calendarPermission = data.calendar;
				    data = data.action;
				}
					
				switch(parseInt(data)){
				case 5:
					$('#content_id_' + currentTab + ' .type_images').append('<img class="loader" src="templates/default/images/ajax-loader.gif" align="top" style="margin-left: 5px; cursor: pointer; display: inline">');
					$.ajax({
						url: "controller.php?action="+import_url+'&from_ajax=true&selected=true',
						success: function(msg){
							$('#content_id_' + currentTab + ' .type_images').append('<img src="../prototype/modules/mail/img/flagDone.png" align="top" style="margin: 3px 0 0 5px; cursor: pointer; display: inline">').parent().find('.loader').remove();
							write_msg( ( ( connector.unserialize(msg)) == "ok") ? "Seu evento foi Atualizado com sucesso" : "Ocorreu um erro ao atualizar evento" );
						}
					});
					return;
					break;		
				case 4:
					$('#content_id_' + currentTab + ' .type_images').append('<img src="../prototype/modules/mail/img/flagDone.png" align="top" style="margin: 3px 0 0 5px; cursor: pointer; ">');
					write_msg("Seu evento encontra-se atualizado.");
					return;
					break;
				case 12:
					write_msg('Este evento não existe mais.');
					return;
					break;			
				}
			}
		});
	}
	//k!!
	
	var div = document.createElement("DIV");
	div.id = "div_message_scroll_"+ID;
        div.style.background = 'WHITE';
        div.style.overflow = "auto";
	table_message_others_options.appendChild(tbody_message_others_options);
	var tr = document.createElement("TR");
		tr.className = "tr_message_header";
	var td = document.createElement("TD");
		td.colspan = '2';
	td.style.fontSize = '10pt'; 
 	td.style.fontFamily = 'Arial,Verdana'; 
 	td.style.verticalAlign = 'top'; 
 	td.style.height = '100%';
	div.appendChild(table_message_others_options);
	var imgTag = info_msg.body.match(/(<img[^>]*src[^>=]*=['"]?[^'">]*["']?[^>]*>)|(<[^>]*(style[^=>]*=['"][^>]*background(-image)?:[^:;>]*url\()[^>]*>)/gi);
	var newBody = info_msg.body;
	if(!info_msg.showImg && imgTag)
	{
		var domains = '';
		var blocked = false;
		var forbidden = true;

		if (preferences.notification_domains != null && typeof(preferences.notification_domains) != 'undefined')
		{
			domains = preferences.notification_domains.split(',');
			for(var j = 0; j < imgTag.length; j++)
			{
				for (var i = 0; i < domains.length; i++)
				{
					if (imgTag[j].match(/cid:([\w\d]){5,}/) || imgTag[j].match(/src=\"\.\/inc\/get_archive\.php/g)) 
					{
						forbidden = false;
						continue;
					}
					imgSource = imgTag[j].match(/=['"](http:\/\/)+[^'"\/]*/);
					if (imgSource && imgSource.toString().substr(5).match(domains[i]))
						forbidden = false;
				}
				if (forbidden)
				{
					newBody = newBody.replace(imgTag[j],"<img src='templates/"+template+"/images/forbidden.jpg'>");
					blocked=true;
				}
			}
			if (blocked)
			{
				var showImgLink = document.createElement('DIV');
				showImgLink.id="show_img_link_"+ID;
				showImgLink.onclick = function(){show_msg_img(info_msg.msg_number,info_msg.msg_folder)};
				showImgLink.className="show_img_link";
				showImgLink.innerHTML = get_lang("Show images from")+": "+info_msg.from.email;
				td.appendChild(showImgLink);
			}
		}
	}
	td.appendChild(div);
	tr.appendChild(td)
	tbody_message.appendChild(tr);


	//////////////////////////////////////////////////////////////////////////////////////////////////////
	//Make the body message.
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	var tr = document.createElement("TR");
	tr.className = "tr_message_body";
	var td = document.createElement("TD");
	//td.setAttribute("colSpan","2");
	
	//Comentado pois estes replaces e a tentativa de remover as tags <span> vazias faz com que seja 
	//eliminado os estilos aplicados no corpo do texto quando utilizado o Firefox.
	//newBody = newBody.replace("<body","<span");
	//newBody = newBody.replace("<BODY","<span");
	//while ( ( /<span[^>]*><span[^>]*>/ig ).test( newBody ) )
	//	newBody = newBody.replace( /(<span[^>]*>)<span[^>]*>/ig, '$1' );

	var _body = document.createElement( 'div' );
	_body.id = 'body_' + ID;
	_body.innerHTML = newBody;
	_body.style.marginLeft = '5px';

	var _elements = _body.getElementsByTagName( '*' );
	for( var i = 0; i < _elements.length; i++ )
		if ( _elements[ i ].attributes && _elements[ i ].attributes.getNamedItem( 'id' ) )
			_elements[ i ].attributes.removeNamedItem( 'id' );	
	
	div.appendChild( _body );
	
	 //window.setTimeout(function() { $("#div_message_scroll_"+ID).focus() },250);
	
	function mailto( link )
	{
		var mail = link.href.substr( 7 );
		link.onclick = function( )
		{
			new_message_to( mail );
			return false;
		};
	}
	var links = div.getElementsByTagName( 'a' );
	for ( var i = 0; i < links.length; i++ ){
		try{
			if ( links.item( i ).href.indexOf( 'mailto:' ) === 0 ){
				mailto( links.item( i ) );
			}
			else{
				var anchor_pattern = "http://"+location.host+location.pathname+"#";

				if ( ( links.item( i ).href.indexOf( 'javascript:' ) !== 0 ) &&
					(links.item( i ).href.indexOf(anchor_pattern) !== 0) ) //se não for âncora
						links.item( i ).setAttribute( 'target', '_blank' );
			}
		}catch(e){
		}
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	//Make the thumbs of the message.
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	//k
	
	if ((info_msg.thumbs)&&(info_msg.thumbs.length > 0)){
		var thumbs = jQuery.parseJSON(info_msg.thumbs);
		var div_thumbs = document.createElement("div");
		
		div_thumbs.setAttribute("class", "expressomail-thumbs");
		div_thumbs.setAttribute("className", "expressomail-thumbs"); //for IE
		
		var div_thumbs_lbl = document.createElement("DIV");
		div_thumbs_lbl.setAttribute("class", "expressomail-thumbs-label");
		div_thumbs_lbl.setAttribute("className", "expressomail-thumbs-label"); //for IE
		
		var div_thumbs_lbl_sp = document.createElement("SPAN");
		div_thumbs_lbl_sp.setAttribute("class", "message_options");
		div_thumbs_lbl_sp.setAttribute("className", "message_options"); //for IE
		var div_thumbs_lbl_st = document.createElement("STRONG");
		div_thumbs_lbl_st.innerHTML = info_msg.attachments.length+" "+get_lang("attachment")+(info_msg.attachments.length > 1 ? "s" : "")+" "+get_lang("in this message");
		var div_thumbs_lbl_a  = document.createElement("A");

		if(info_msg.thumbs.length > 1){
			 if(proxy_mensagens.is_local_folder(current_folder))
				div_thumbs_lbl_a.setAttribute("href", "javascript:expresso_local_messages.download_all_local_attachments('"+info_msg.msg_folder+"','"+info_msg.msg_number+"')");
			else
				div_thumbs_lbl_a.setAttribute("href", "javascript:download_all_attachments('"+info_msg.msg_folder+"','"+info_msg.msg_number+"')");
			div_thumbs_lbl_a.innerHTML = get_lang('Download all atachments');
		} else {
			div_thumbs_lbl_a.setAttribute("style", "display:none; visibility:hidden;");
		}

		div_thumbs_lbl_sp.appendChild(div_thumbs_lbl_st);
		div_thumbs_lbl_sp.appendChild(document.createTextNode(' :: '));
		div_thumbs_lbl_sp.appendChild(div_thumbs_lbl_a);
		div_thumbs_lbl.appendChild(div_thumbs_lbl_sp);

		var div_thumbs_lbl_sp2   = document.createElement("SPAN");
		div_thumbs_lbl_sp2.setAttribute("class", "message_tips");
		div_thumbs_lbl_sp2.setAttribute("className", "message_tips"); //for IE
		div_thumbs_lbl_sp2.innerHTML = get_lang("<strong>Tip:</strong> <span>For faster save, click over the image with <em>right button</em>.</span>");

		var ul_thumbs_list = document.createElement("UL");
		ul_thumbs_list.setAttribute("class", "expressomail-thumbs-list");
		ul_thumbs_list.setAttribute("className", "expressomail-thumbs-list"); //for IE

	                var msg = info_msg.msg_number; 
	                var fdr = info_msg.msg_folder; 
					var i = 0;
				//verifica se está no novo padrão de montagem das mensagens ou no antigo, necessário
				//para exibir as imagens no arquivamento local arquivamento local.
				if(thumbs){
	                jQuery.each(thumbs, function(i, thumb) {
							if(fdr.indexOf("local_") >= 0){
								var href = info_msg.array_attach[i].url + '&image=true'; 
								var src  = info_msg.array_attach[i].url+ '&image=thumbnail'; 
								i++;
							}else{
								var href = './inc/get_archive.php?msgFolder=' + fdr + '&msgNumber=' + msg + '&indexPart=' + thumb.pid + '&image=true'; 
								//var href = thumb.url; 
								var src  = 'inc/get_archive.php?msgFolder=' + fdr + '&msgNumber=' + msg + '&indexPart=' + thumb.pid + '&image=thumbnail'; 
	                        }
	                        var msgid= fdr+";;"+msg+";;"+i+";;"+thumb.pid+";;"+thumb.encoding; 
	                        var image_info = '{"folder":"'+fdr+'","message":"'+msg+'","thumbIndex":"'+i+'","pid":"'+thumb.pid+'","encoding":"'+thumb.encoding+'","type":"'+thumb.type+'"}'; 
	                         
	                        var image= '<img id="' + msgid + '" title="' +  
	                                                        get_lang('Click here do view (+)') + '" src="' + src + '" style="width:auto;height:100%;" />'; 
	                                                 
	                        var content = '<a title="'+thumb.name+ '" rel="thumbs'+ID+'" class="expressomail-thumbs-link" onMouseDown="save_image(event,this,\'' +thumb.type+'\')" href="'+href+'" onclick="window.open(\''+href+'\',\'mywindow\',\'width=700,height=600,scrollbars=yes\');return false;">'+image+'</a>'; 
	                        content += '<input id="thumb_'+ID+'_'+i+'" type="hidden" value="' +escape(image_info) +'" />'; 
	                        jQuery(ul_thumbs_list).append('<li>'+content+'</li>'); 
	                         
	                });    
		
		div_thumbs.appendChild(div_thumbs_lbl);
		div_thumbs.appendChild(div_thumbs_lbl_sp2);
		div_thumbs.appendChild(ul_thumbs_list);
		
				}else{
					div_thumbs.appendChild(div_thumbs_lbl);
					div_thumbs.appendChild(div_thumbs_lbl_sp2);
					div_thumbs.innerHTML = div_thumbs.innerHTML + info_msg.thumbs;
					
					
				}
		
		div.appendChild(div_thumbs);
	}
	//k!!
	//////////////////////////////////////////////////////////////////////////////////////////////////////
	table_message.appendChild(tbody_message);
	content.appendChild(table_message);
	resizeWindow();
	var msg_number = document.createElement('INPUT');
	msg_number.id = "msg_number_" + ID;
	msg_number.type = "hidden";
	msg_number.value = info_msg.msg_number;
	content.appendChild(msg_number);
	//////////////////////////////////////////////////////////////////////////////////////////////////////

	//Exibe o cabecalho da mensagem totalmente aberto caso esteja setado nas preferencias do usuario
	if (preferences.show_head_msg_full == 1)
	{
		option_hide_more.onclick();
		if (Element('div_toaddress_'+ID) != null)
			show_div_address_full(ID,'to');
		if (Element('div_ccaddress_'+ID) != null)
			show_div_address_full(ID,'cc');
	}

	/*
	 * TODO: implementar o controle como preferência do usuário
	 *
	 */
	var jcarousel = false;
	if (jcarousel) {
		//carousel 
		jQuery(document).ready(function() {
	 		jQuery('.expressomail-thumbs-list').attr('id', 'expressomail-thumbs-list'+ID) 
	 	    .addClass('jcarousel-skin-default').jcarousel(); 

			jQuery('.expressomail-thumbs-link img').attr('style', ''); 
		
		//fancybox
			jQuery(".expressomail-thumbs-list li a").attr('onclick', 'return true;');
			jQuery(".expressomail-thumbs-list li a").fancybox({
				'hideOnContentClick': true,
					'type': 'image', 
					'titlePosition': 'over',                                         
					'titleFormat' : function(name, currentArray, currentIndex, currentOpts) {                        
							var image_info = $('#thumb_'+ID+'_'+currentIndex).val(); 

							return '<div id="fancybox-title" class="fancybox-title-over" style="width: 100%; display: block;">' + 
										'<div id="fancybox-title-over">' + 
											'<a title="Anterior" onclick="javascript:$.fancybox.prev();" style="float:left;">' + 
													'<img src="./templates/'+template+'/images/left_arrow_white.png" width="30" height="30" />' + 
											'</a>'+ 
											'<a title="Baixar imagem" onclick="javascript:save_image2(\''+image_info+'\');" style="padding:0 5px;">' + 
													'<img src="./templates/'+template+'/images/image_down.png" width="22" height="22" />' + 
											'</a>'+ 
											'<a title="Baixar todas de uma vez" onclick="javascript:download_all_attachments(\''+info_msg.msg_folder+"','"+info_msg.msg_number+'\')" style="padding:0 5px;">'+ 
													'<img src="./templates/'+template+'/images/package_down.png" width="26" height="26" />' + 
											'</a>'+ 
											'<span style="margin-left:5px; margin-top:7px; position:absolute;">'+name+'</span>'+ 
											'<a title="Próxima" onclick="javascript:$.fancybox.next();" style="float:right;">' + 
												'<img src="./templates/'+template+'/images/right_arrow_white.png" width="30" height="30" />' + 
											'</a>' + 
										'</div>' + 
									'</div>'; 
					} 
			});
		});
	}  
	if ( is_webkit ){ //Corrige o bug de foco no Chrome
	       var ev = document.createEvent('MouseEvents');
           ev.initEvent(
				'click'    
				,false 
				,true
			);
	       var divScroll = Element('div_message_scroll_'+ID);
			if (divScroll) {
				divScroll.setAttribute('tabindex','-1');
				divScroll.onclick = function() {setTimeout(function(){$("#div_message_scroll_"+ID).focus();},0);};
				divScroll.dispatchEvent(ev);
			}
		}
	else  $("#div_message_scroll_"+ID).focus();
	update_labeleds_msg();

	resizeWindow(); 
	
	$("#div_message_scroll_"+ID).scrollTo( 0, 400, {queue:true} );
}
 
function changeLinkState(el,state){
	el.innerHTML = get_lang(state);
	switch (state){
		case 'important':
			{
				el.onclick = function(){changeLinkState(el,'normal');proxy_mensagens.proxy_set_message_flag(currentTab.substr(0,currentTab.indexOf("_r")),'flagged');write_msg(get_lang('Message marked as ')+get_lang("Important"))}
				break;
			}
		case 'normal':
			{
				el.onclick = function(){
					var _this = this;
					proxy_mensagens.proxy_set_message_flag(currentTab.substr(0,currentTab.indexOf("_r")),'unflagged', function(success){
						if (success) {
							changeLinkState(_this, 'important');
							write_msg(get_lang('Message marked as ') + get_lang("Normal"));
						}
					} );
				}
				break;
			}
		case 'unseen':
			{
				el.onclick = function(){changeLinkState(el,'seen');proxy_mensagens.proxy_set_message_flag(currentTab.substr(0,currentTab.indexOf("_r")),'unseen');write_msg(get_lang('Message marked as ')+get_lang("unseen"))}
				break;

			}
		case 'seen':
			{
				el.onclick = function(){changeLinkState(el,'unseen');proxy_mensagens.proxy_set_message_flag(currentTab.substr(0,currentTab.indexOf("_r")),'seen');write_msg(get_lang('Message marked as ')+get_lang("seen"))}
				break;

			}
		default:
			{
				break;
			}
	}
}

function mySplit( val ) {
	return val.split( /,\s*/ );
}

function extractLast( term ) {
	return mySplit( term ).pop();
}

//DESENHO DAS CAIXA DE EMAIL
function draw_email_box(input_data, location, personal, shared){
	if($.trim(input_data) != ""){
		var box_data = valid_emails(input_data);
		var html = DataLayer.render("../prototype/modules/mail/templates/emailBox.ejs", box_data);
		var newBox = location.before(html).prev();
		box_actions(newBox);
		if((preferences.expressoMail_ldap_identifier_recipient || personal)&& $(newBox).hasClass("invalid-email-box")){
			//$(newBox).find(".loading").css("background-image", "../prototype/modules/mail/img/ajax-loader.gif");
			show_detais(newBox, input_data, personal, shared);
		}else{
			$(newBox).find(".box-loading").remove();
		}
	}
}

function valid_emails(email){
	var ContactBox = {name:"", email:"", valid : false};
	var reSimpleEmail = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)+$/;
	var reComplexEmail = /<([^<]*)>[\s]*$/;
	var validation = email.split('"');
	
	//FUNÇÃO QUE VALIDA OS DADOS QUANDO O EMAIL É DIGITADO COM ("NOME SOBRENOME" <Email@dominio.com>)
	var complexValidation = function(complexMail){
		var Objct = {};
		if($.trim(complexMail[1]).match(reComplexEmail)){
			if($.trim(complexMail[1]).match(reComplexEmail).length){
				Objct['email'] = $.trim(complexMail[1]).match(reComplexEmail)[1];
			}
		}else{
			Objct['email'] = $.trim(complexMail[1]);
		}
		Objct['name'] = complexMail[0];
		Objct['valid'] = reSimpleEmail.test(Objct['email'].toLowerCase());
		return Objct;
	}
	switch (validation.length) {
		//PEGA TODO O CONTEUDO E SETA COMO SE FOSSE O EMAIL
		case 1:
			validation.unshift("");
			ContactBox = complexValidation(validation);
			break;
		//CORRIGI ERRO DE DIGITAÇÃO COMO ( huahua"<huhau@hauhau.com>) ou (hahahaha"huahua@email.com) ou ainda (hahahaha"huahua@ema  il.com)
		case 2: 
			ContactBox = complexValidation(validation);
			break;
		//RECEBE O EMAIL CORRETAMENTE SÓ VALIDA POSSIVEIS ERROS COMO O DE CIMA E OS CORRIGI CASO ACONTEÇAM
		case 3:
			//RETIRA O PRIMEIRO INDICE QUE FICOU "INUTIL"
			validation.shift();
			ContactBox = complexValidation(validation);
			break;
		//SE EXISTIREM MAIS DO QUE 2 (")
		default:
			if($.trim(validation[validation.length-1]).match(reComplexEmail)){
				if($.trim(validation[validation.length-1]).match(reComplexEmail).length){
					ContactBox.mail = $.trim(validation[validation.length-1]).match(reComplexEmail)[1];
				}
			}else{
				ContactBox.mail = $.trim(complexMail[1]);
			}
			ContactBox.valid = reSimpleEmail.test(ContactBox.mail.toLowerCase());	
	}
	return ContactBox;
}

//EVENTO DOS INPUTS PARA - CC - CCO
function input_keydowns(input, ID){
	var f9 = false;
	input.keydown(function(e){
		f9 = false;
		focusIn = input;
		var focusing = input.parent().find(".email-text");
		//SE OS CONTATOS DINAMICOS ESTAO ATIVOS
		if(parseInt(preferences.use_dynamic_contacts) && !input.hasClass("box-input")){
			//SELECIONA O CONTATO E EVITA OUTROS COMANDOS
			if ( e.keyCode === $.ui.keyCode.TAB && $( this ).data( "ui-autocomplete" ).menu.active ) {
				e.preventDefault();
				return false;
			}		
			
			//FECHA OS CONTATOS DINÂMICOS
			if( (e.keyCode == 27) && $( this ).data( "ui-autocomplete" ).menu.active ){
				   e.stopPropagation();
				   e.preventDefault();
			}
            if ( (e.keyCode == 8) && (input.val().length == 1) ){
                $( this ).data( "ui-autocomplete" ).close();
            }			
			//SELECIONA O CONTATO E EVITA OUTROS COMANDOS
			if(e.keyCode == $.ui.keyCode.ENTER && $( this ).data( "ui-autocomplete" ).menu.active){
				e.preventDefault();
				return false;
			}
			
			if(e.keyCode == $.ui.keyCode.DELETE && $( this ).data( "ui-autocomplete" ).menu.active){
				if($($( this ).data( "ui-autocomplete" ).menu.element).find(".ui-state-hover").parents("li:first").hasClass("dynamic-recent"))
					$($( this ).data( "ui-autocomplete" ).menu.element).find(".ui-state-hover").next().trigger("click");
				return false;
			}
		}
		//BUSCA COM A TECLA F9
		if((e.keyCode) == 120){
			f9 = true;
			emQuickSearch($(this).val(), "."+$(this).parents("tr:first").attr('class').split("-")[0], ID, undefined, true);
			e.preventDefault();
			return false;
		}

		//AO DIGITAR ENTER, ";", "," "	"
		if(e.keyCode == 13 || e.keyCode == 9 || (e.keyCode == 191 && !e.shiftKey) ){
			if(e.keyCode == 13){
				e.preventDefault();
			}
			if(input.val() != ""){
				if(e.keyCode == 188 && !e.shiftKey && input.val().split("\"").length > 1){
                    return;
				}
				if(e.keyCode != 9)
					e.preventDefault();

                draw_email_box(input.val(), input);

				if(input.hasClass("box-input")){
					input.remove();
					focusing.focus();
					return;
				}
			}
            if(input.length)
                input.val("");
		}
		if((e.keyCode == 8 || e.keyCode == 37) && input.val() == "" && input.prev().hasClass("box")){
			e.preventDefault();
			input.prev().focus();
			return;
		}

		//AO DIGITAR ">"
		if(e.keyCode == 190 && e.shiftKey && input.val().length == getPosition(input[0])){
			input.val(input.val()+">");
			draw_email_box(input.val(), input);
			e.preventDefault();
			input.val("");
			if(input.hasClass("box-input")){
				input.remove();
				focusing.focus();
				return;
			}
		}
		setTimeout(function(){
            // CASO FOR PRESSIONADO "," OU ";", É CRIADA UMA CAIXINHA.
			if(input.val()[input.val().length-1] == ";"){
				draw_email_box(input.val().substring(0, input.val().length-1), input);
				input.val("");
			} else if(input.val()[input.val().length-1] == ","){
                draw_email_box(input.val().substring(0, input.val().length-1), input);
                input.val("");
            }
		}, 100);
		//INPUT AUTO RESIZE	
		setTimeout(function(){
			input_search = $(input).val();
			var vchar = input.val().charAt(input.val().length-1);
			var maiusculas = RegExp("[A-Z]");
			/*Se o ultimo caracter for ">" é porque o campo está sendo editado (a partir de duplo clique)*/
			var tamanho = 0;
			if(vchar == ">"){
				/*Faz um calculo prévio do tamanho do campo de acordo com o tamanho de cada caracter da string*/
				for(i=0; i<input.val().length; i++){
					/*Se o caracter for maiúsculo, o valor de pixel é maior*/
					if(maiusculas.test(input.val().substr(i, 1)) == true){
						tamanho += 9;
					}
					else{
						tamanho += 7.2;
					}
				}
				input.css("width", tamanho);
			}
			/*Ao inserir novo contato, não existe a necessidade de calcular tamanho do campo*/
			else{
				input.css("width", 15+(input.val().length * 9));
			}
			input.parent().scrollTo(":last");	
		}, 10);
	})
	//AO SAIR DO FOCO MONTAGEM DA CAIXA DE EMAIL
	.focusout(function(e){
		var these = $(this);
		// Função para montar a caixinha de e-mail.
		function makeBoxMail(){
			if(canMakeBox && !fastSearch){
				if(!(	f9	||	click	||	$(this).parents("tr:first").find("button").hasClass("ui-state-active")	)){
					if($(input).val() != "")
						draw_email_box(input.val(), input);
					if(input.hasClass("box-input"))
						input.remove();
					$(input).val("");
					input_search = "";
				}
				f9 = false;
				click = false;
			}
			canMakeBox = true;
			fastSearch = false;
		}

		setTimeout(makeBoxMail,250);
	})
	//AO COLAR UM TEXTO NO CAMPO
	.bind("paste", function(e){
		//$(this).trigger("keydown");
		var pthis = $(this);
		setTimeout(function() {
			if(pthis.val().split('"').length > 1){
				var str = break_comma(pthis.val());//.replace(/[,;\t]/gi, ",");	
			}else{
				var str = pthis.val().replace(/[,;\t\n]/gi, ",");
				str = str.split(",");
			}

			if(str.length != 1){
				$.each(str, function(index, value){
					draw_email_box(value, pthis);
				});
			}else if(str[0].split(" ").length == 1){
				draw_email_box(str[0], pthis);
			}else{
				$(pthis).val(str[0]);				
				return false;
			}
			$(pthis).val("");
		}, 50);
	});
	//SE FOR EDIÇÃO DE EMAILS RECALCULA O INPUT E SETA O FOCO
	if(input.hasClass("box-input")){
		input.css("max-width",parseInt(input.parents(".email-area:first").css("width"))-15);
		input.trigger("keydown");
		input.focus();
	}
}


var input_search = "";
var click = false;
//EVENTOS DA CAIXA
function box_actions(box){
	//AO PRESSIONAR UMA TECLA COM A CAIXA SELECIONADA	
	box.keydown(function(e){
		switch (e.keyCode) {
			case $.ui.keyCode.LEFT:
				//VERIFICA SE EXISTE ALGUMA CAIXA A ESQUERDA
				if($(this).prev().hasClass("box"))
					$(this).removeClass("box-selected").prev().focus();
				break;
			case $.ui.keyCode.RIGHT:
				//VERIFICA SE EXISTE ALGUMA CAIXA A DIREITA 
				if($(this).next().hasClass("box"))
					$(this).removeClass("box-selected").next().focus();
				//SENAO FOCO O INPUT DO EMAIL
				else
					$(this).removeClass("box-selected").next().focus();
				break;
			case $.ui.keyCode.HOME:
				//SELECIONO A PRIMEIRA CAIXA
				e.preventDefault();
				$(this).parents(".email-area").find("div:first").focus();
				break;
			case $.ui.keyCode.END:
				//SELECIONO A ULTIMA CAIXA
				e.preventDefault();
				$(this).parents(".email-area").find("div:last").focus();
				break;
			case $.ui.keyCode.DELETE:
				//VERIFICA SE EXISTE ALGUMA CAIXA A DIREITA
				if($(this).next().hasClass("box"))
					$(this).next().focus();
				//SENAO FOCO O INPUT DO EMAIL
				else
					$(this).next().focus();
				//REMOVO ESTA CAIXA
				$(this).remove();
				break;
			case $.ui.keyCode.BACKSPACE:
				//VERIFICA SE EXISTE ALGUMA CAIXA A ESQUERDA
				if($(this).prev().hasClass("box"))
					$(this).removeClass("box-selected").prev().focus();
				//SENAO HOUVER VERIFICA SE EXISTE ALGUMA CAIXA A DIREITA
				else if($(this).next().hasClass("box"))
					$(this).next().focus();
				//SENAO HOUVER NEM A DIREITA NEM A ESQUERDA SETO O FOCO NO INPUT DO EMAIL
				else
					$(this).next().focus();
				//REMOVO ESTA CAIXA	
				$(this).remove();
				e.preventDefault();
				break;
			case $.ui.keyCode.ENTER:
				e.preventDefault();
				$(this).trigger("dblclick");
				break;
		}
	})
	//AO FAZER UM DUPLO CLICK NA CAIXA
	.dblclick(function(e){
		var input = $(this).find("input").clone();
		input.css("display" , "inline-block");
		$(this).before(input);
		$(this).remove();
		$(input).focus();
		input_keydowns(input, currentTab);
	//CLICK SIMPLES NA CAIXA
	}).click(function(){
		$(this).focus();
	//AO DAR O FOCO NA CAIXA
	}).focus(function(){
		$(this).parent().find("div").removeClass("box-selected");
		$(this).addClass("box-selected");
	}).focusout(function(){
		$(this).removeClass("box-selected");
	}).draggable({
		revert: 'invalid',
		helper : 'clone',
		stack: "body",
		containment : ".new-msg-head-data",
		start: function(e, ui){
			$(this).parent().droppable( "disable" );
		},
		stop : function(e, ui){
			$(this).parent().droppable( "enable" );
		}
	});

}

//MOSTRA OS DETALHES DAS CAIXA DE EMAIL NOS CAMPOS PARA - CC - CCO
function show_detais(box, value, personal, shared){
	var ldap_id = preferences.expressoMail_ldap_identifier_recipient;
	var group = (personal != undefined ? (personal == "G" ? true : false) : false);
	shared = shared ? shared : false;
	if(group){
		REST.get("/"+ (shared ? "shared" : "") +"group/"+value, {}, function(data){
			if(!data.error){
				if(data.collection.error)
					box.find(".box-loading").remove();
				else{
                    box.find(".box-loading").removeClass("box-loading").addClass("box-info");
					box.addClass("box-"+value).removeClass("invalid-email-box");
					loadGroupBox(data.collection, ".box-"+value);
					box.unbind("dblclick").bind("dblclick", function(e){
						new $.Zebra_Dialog(get_lang("Impossible editing this contact, but it's possible to remove it"), {
							'buttons':  false,
							'modal': false,
							'position': ['right - 20', 'top + 20'],
							'auto_close': 3000,
							'custom_class': 'custom-zebra-filter'
						});
					}).find(".box-input").val("\""+data.collection.data[1].value+"\" <"+$.trim(data.collection.data[2].value)+">");
					box.find(".email-box-value").html( (data.collection.data[1].value.length > 18 ? data.collection.data[1].value.substring(0, 15)+"...": data.collection.data[1].value))
				}
			}else{
				box.find(".box-loading").remove();
			}
		});
		return;
	}
	
	if(personal){
		REST.get("/"+ (shared ? "shared" : "") +"personalContact/"+value, {}, function(data){
			if(!data.error){
				if(data.collection.error)
					box.find(".box-loading").remove();
				else{
                    var item = normalizeContact(data.collection.itens[0].data);
					box.find(".box-loading").removeClass("box-loading").addClass("box-info");
					box.addClass("box-"+value).removeClass("invalid-email-box");
					loadExtraLDAPBox(item, ".box-"+value);
					box.unbind("dblclick").bind("dblclick", function(e){
						new $.Zebra_Dialog(get_lang("Impossible editing this contact, but it's possible to remove it"), {
							'buttons':  false,
							'modal': false,
							'position': ['right - 20', 'top + 20'],
							'auto_close': 3000,
							'custom_class': 'custom-zebra-filter'
						});
					}).find(".box-input").val("\""+item.name+"\" <"+$.trim(item.email)+">");

					box.find(".email-box-value").html(normalizeBoxName(item.name, item.value));
				}
			}else{
				box.find(".box-loading").remove();
			}
		});
		return;
	}
	
	REST.get("/usersldap", {field : ldap_id,value: value}, function(data){
		if(!data.error){
			if(data.collection.error)
				box.find(".box-loading").remove();
			else{
                var item = normalizeContact(data.collection.itens[0].data);
				box.find(".box-loading").removeClass("box-loading").addClass("box-info");
				box.addClass("box-"+value).removeClass("invalid-email-box");
				loadExtraLDAPBox(item, ".box-"+value);
				box.unbind("dblclick").bind("dblclick", function(e){
					new $.Zebra_Dialog(get_lang("Impossible editing this contact, but it's possible to remove it"), {
						'buttons':  false,
						'modal': false,
						'position': ['right - 20', 'top + 20'],
						'auto_close': 3000,
						'custom_class': 'custom-zebra-filter'
					});
				}).find(".box-input").val("\""+item.name+"\" <"+$.trim(item.email)+">");

                box.find(".email-box-value").html(normalizeBoxName(item.name, item.value));
				if(item.vacationActive == "TRUE"){
					box.addClass("out-office-box");
				}
			}
		}else{
			box.find(".box-loading").remove();
		}
	});
}

function normalizeBoxName(name, mail){
    var emailBoxValue = name.length > 18 ? name.substring(0, 15)+"...": name;
    emailBoxValue = emailBoxValue.length > 2 ? emailBoxValue : email.substr(0,email.indexOf('@'));
    emailBoxValue = emailBoxValue > 18 ? emailBoxValue.substr(0,15) + "..." : emailBoxValue;

    return emailBoxValue;
}

function normalizeContact(data){
    var item = {};
    $.each(data, function(j, e){
       item[e.name] = e.value;
    });

    return item;
}

function normalizeContacts(data){
    var decoded = [];

    if(!$.isArray(data)){data = [data];}

    for(var i = 0; i < data.length; i++){
        var item = {};
        $.each(data[i].data, function(j, e){
            item[e.name] = e.value;
        });
        decoded.push(item);
    }
    return decoded;
}

dynamicData = false;
currentTypeContact = '';

//FUNÇÃO QUE "SETA" OS BINDS DOS CAMPOS PARA - CC - CCO
function input_binds(div, ID){

	//AO CLICAR NA DIV SETA O FOCO NO INPUT
	div.click(function(e){
		if(e.target == $(this)[0]){
			$(this).find("textarea:first").focus();
			$(this).find("div").removeClass("box-selected");
		}
	})
	
	//AO SAIR DO FOCO DA DIV ELE RETIRA TODAS AS CLASSES DE CAIXAS SELECIONADAS
	.focusout(function(e){
		if(!$(e.target).parents(".email-area:first").length)
			$(this).find("div").removeClass("box-selected");
	}).droppable({
		hoverClass: "box-draggable-hover",
		accept : ".box",
		drop : function(e, ui){
			ui.draggable.parent().droppable( "enable" );
			var box = ui.draggable.clone().removeClass("box-selected");
			box_actions(box);
			if(box.find(".box-info").length){
				box.unbind("dblclick").bind("dblclick", function(e){
					new $.Zebra_Dialog('<strong>Impossivel editar</strong> um contato do ldap\n' +
						'<strong>Porém</strong>é possivel remove-lo', {
						'buttons':  false,
						'modal': false,
						'position': ['right - 20', 'top + 20'],
						'auto_close': 3000,
						'custom_class': 'custom-zebra-filter'
					});
				});
			}
			$(this).prepend(box);
			ui.draggable.remove();
		}
	});
	
	//MAKE KEYDOWN
	input_keydowns(div.find("textarea:first"), ID);
	
	
	//VERIFICA PREFERENCIA DE CONTATOS DINÂMICOS ESTA ATIVA
	if(parseInt(preferences.use_dynamic_contacts)){

        //REST.get("/usercontacts", false, updateDynamicContact);

		//PREPARAÇÃO DA ARRAY DOS CONTATOS DINÂMICOS

        var decodeType = {
            '/dynamiccontacts': {
                css: 'recent',
                img: 'recent',
                text: 'Contato Recente'
                },
            '/personalContact':{
                css: 'personal',
                img: 'personal',
                text: 'Contato pessoal'
		    },
            '/sharedcontact':{
                css: 'personal',
                img: 'sharedcontact',
                text: 'Contato compartilhado'
            },
            '/groups':{
                css: 'group',
                img: 'group',
                text: 'Grupo pessoal'
            },
            '/sharedgroup':{
                css: 'group',
                img: 'sharedgroup',
                text: 'Grupo compartilhado'
            }
	    }

        div.find("textarea").autocomplete(
        {
            source: function(request, response){
                if ($.trim(request.term).length == 0)
                    return false;
                if ( request.term in cache ) {
                    response( cache[ request.term ] );
                    return;
                }

                if(dynamicData === false){
                    updateDynamicContact();
                }

                var data = $.ui.autocomplete.filter(dynamicData, request.term ).slice(0, 50);
                cache[ request.term ] = data;
                response( data );

            },
            focus: function() {
                return false;
            },

            //EVENTO AO SELECIONAR UM CONTATO DINÂMICO
            select: function( event, ui ) {
                canMakeBox = true;

                event.preventDefault();
                $(this).val("");

                var isShared = (ui.item.type.substring(0,7) == "/shared");

                switch (ui.item.typel){
                    case '/personalContact':
                        draw_email_box(""+ui.item.id, $(this), true, isShared);
                        break;
                    case '/groups':
                        draw_email_box(""+ui.item.id, $(this), "G", isShared);
                        break;
                    default:
                        draw_email_box(ui.item.name ? ("\""+ui.item.name+"\" <"+ui.item.mail+">") : ui.item.mail, $(this));
                }

                return false;
            },
            autoFocus: true,
            position : { my: "left top", at: "left bottom", collision: "fit" },
            delay : 300,
            minLength: 0
        }).bind('autocompleteopen', function(event, ui) {
            $(this).data('is_open',true);
        }).bind('autocompleteclose', function(event, ui) {
            canMakeBox = false;
            $(this).data('is_open',false);
            $(this).blur().focus();
        }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            
            var autocomplete = $(this)[0].element;

            ul.css({"width":'50%',"min-width":'600px', "max-height" : "180px", "overflow-y" : "auto", "min-height": "30px"});

            item.raty = ((item.number_of_messages*10)/topContact) > 1 ? ((item.number_of_messages*10)/topContact) : 1;

            if ( item.typel != currentTypeContact) {
                if((item.typel == "/groups" && $(ul).find(".dynamic-recent").length) || (item.typel == "/personalContact" && ($(ul).find(".dynamic-group").length || $(ul).find(".dynamic-recent").length))){
                    currentTypeContact = item.typel;
                    item.asDiv = true;
                }else{
                    currentTypeContact = item.typel;
                }
            }

            var li = '';
            if(item.asDiv){
                li = '<li class="dynamic-separator"><div class="line-separator">&nbsp;</div></li>';
            }
            li += '<li class="dynamic-'+ decodeType[item.type].css +'">';
            li += '<a style="width:'+(item.type == '/dynamiccontacts' ? '91%' : '97.5%')+';  display: inline-block; background: none;">';
            li += '<img style="position:relative; top:2px; "src="../prototype/modules/mail/img/'+ decodeType[item.type].img +'.png" title="'+ decodeType[item.type].text +'"/>';
            li += ($.trim(item.name) != "" ? ((item.name.length > 20 ? item.name.substring(0,17)+"..." : item.name) + " - " ) : '')  + item.mail;
            li += item.type == '/dynamiccontacts' ? '<div class="dynamic-stars" style="display: inline-block;float: right;" id="'+item.raty+'_'+item.id+'"/>' : ''
            li += '</a>';
            li += '<span style="width:16px; height:16px; top:1px; left:7px; '+ (item.type == '/dynamiccontacts' ? '': 'display:none') +'">Excluir contato recente</span>';
            li += '</li>';


            li = $( li )
                .data( "item.autocomplete", item )
                //.append( li )
                .appendTo( ul );

            li.find("span").button({
                icons : {
                    primary : "ui-icon-close"
                },
                text: false
            }).click(function(event){
                    if(!event.keyCode)
                        autocomplete.autocomplete( "close" );

                    canMakeBox = false;
                    $.Zebra_Dialog('Deseja remover <b>'+(item.name ? (item.name.length <= 30 ? item.name: item.name.substr(0,27)+"...")+" - " : "")+ item.mail+'</b>?', {
                        'type':     'question',
                        'custom_class': (is_ie ? 'configure-zebra-dialog custom-zebra-filter' : 'custom-zebra-filter'),
                        'buttons': ['Sim','Não'],
                        'overlay_opacity': '0.5',
                        'onClose':  function(caption) {
                            if(caption == 'Sim'){

                                REST['delete']("/dynamiccontact/"+item.id);
                                updateDynamicContact();
                                cache = new Array();
                            }else if(caption == 'Não'){
                                $(focusIn).focus();
                            }
                        }
                    });
                });

            li.find(".dynamic-stars").jRating({
                step:true,
                length : 5, // nb of stars
                decimalLength: 2, // number of decimal in the rate
                rateMax: 10,
                isDisabled:true,
                bigStarsPath : '../prototype/plugins/jquery.jrating/icons/stars.png', // path of the icon stars.png
                smallStarsPath : '../prototype/plugins/jquery.jrating/icons/small.png' // path of the icon small.png
            });

            if($(ul).find("li:last").hasClass("dynamic-separator")){
                $(ul).find("li:last").remove();
            }

            $(ul).scroll(function(){
                canMakeBox = false;
            });

            return li;
        };
	}

	//FUNÇÃO DOS BOTÕES PARA - CC - CCO
	div.parents("tr:first").find("button").button().click(function(){
		click = true;
		fastSearch = true;
		canMakeBox = false;
		if(!$(":focus").hasClass("new-message-input"))
			emQuickSearch(($(this).parents("tr:first").find("textarea").val() ? $(this).parents("tr:first").find("textarea").val() : input_search), "."+$(this).parents("tr:first").attr('class').split("-")[0], ID, undefined, true);
	});
}

/* 
	Anexa uma mensagem a mensagem sendo enviada. 
	Parâmetros: 
		folder_name: nome da pasta na qual a mensagem sendo anexada se encontra. 
		message_number: id da mensagem sendo anexada. 
*/ 
function attach_message (folder_name, message_number) { 
    var ID = currentTab; 
    var fileUploadMSG = $('#fileupload_msg'+ID); 
    fileUploadMSG.find(' .attachments-list').show(); 
    var att = new Object(); 

   

    var attach = {}; 
    attach.fileName = onceOpenedHeadersMessages[folder_name][message_number].subject + '.eml'; 
    attach.fullFileName = onceOpenedHeadersMessages[folder_name][message_number].subject + '.eml'; 
    if(attach.fileName.length > 20){ 
        attach.fileName = attach.fileName.substr(0, 17) + "... " + attach.fileName.substr(attach.fileName.length - 9, attach.fileName.length); 
    } 
    attach.error = false; 
    attach.OK = true;
    if (folder_name.indexOf('local_messages_') == 0){
        attach.fileSize = formatBytes(onceOpenedHeadersMessages[folder_name][message_number].size);
    } else {
        attach.fileSize = formatBytes(onceOpenedHeadersMessages[folder_name][message_number].Size); 
    }
    var upload = $(DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist.ejs", { 
        file: attach 
    })); 
    $("#content_id_" + currentTab + " .save").button("enable"); 
    upload.find('.att-box-loading').remove(); 
 
    upload.find('.att-box-delete').click(function() 
    { 
        $("#content_id_" + currentTab + " .save").button("enable"); 
        var idAttach = $(this).parent().find('input[name="fileId[]"]').val(); 
        fileUploadMSG.find(' .attachments-list').find('input[value="' + idAttach + '"]').remove(); 
        delAttachment(ID, idAttach); 
        $(this).parent().qtip("destroy"); 
        $(this).parent().remove(); 
        if(!fileUploadMSG.find(' .attachments-list').find(".att-box").length) 
        { 
            fileUploadMSG.find(' .attachments-list').hide(); 
        } 
    }); 
 
    var addtip = function(attach){
        fileUploadMSG.find('.attachments-list .att-box:last').qtip( 
        { 
            content: DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist_tooltip.ejs", { 
                attach: attach 
            }), 
            position: { 
                corner: { 
                    tooltip: 'bottomMiddle', 
                    target: 'topMiddle' 
                }, 
                adjust: { 
                    resize: true, 
                    scroll: true 
                } 
            }, 
            show: { 
                when: 'mouseover', 
                // Don't specify a show event 
                ready: false // Show the tooltip when ready 
            }, 
            hide: 'mouseout', 
            // Don't specify a hide event 
            style: { 
                border: { 
                    width: 1, 
                    radius: 5 
                }, 
                width: { 
                    min: 75, 
                    max: 1000 
                }, 
                padding: 5, 
                textAlign: 'center', 
                tip: true, 
                // Give it a speech bubble tip with automatic corner detection 
                name: 'blue' // Style it according to the preset 'cream' style 
            } 
        }); 
    } 

    var idATT = "";
    if(folder_name.indexOf("local_messages_") != 0)
    {
        att.folder = folder_name; 
        att.uid = message_number; 
        att.type = 'imapMSG'; 
        att.name = Base64.encode(onceOpenedHeadersMessages[folder_name][message_number].subject + '.eml'); 
        idATT = JSON.stringify(att);
        addAttachment(ID, idATT);

        fileUploadMSG.find('.attachments-list').append(upload);
        addtip(attach);
    }
    else
    {
        var folder_trash = "INBOX"+cyrus_delimiter+special_folders["Trash"];
        expresso_mail_archive.unarchieveToAttach(folder_name, folder_trash, message_number, function(data){
            $.ajax({
                url: "controller.php?action=$this.imap_functions.get_info_msg_archiver",
                data:  {"idMsgs":data.idsMsg},
                type: 'POST',
                async: false,
                success: function(data){
                    data = JSON.parse(connector.unserialize(data));
                    data = data[0];
                    att.folder = folder_trash;
                    att.uid = data.uid; 
                    att.type = 'imapMSG'; 
                    att.name = Base64.encode(onceOpenedHeadersMessages[folder_name][message_number].subject + '.eml'); 
                    idATT = JSON.stringify(att);
                    addAttachment(ID, idATT);
                    fileUploadMSG.find('.attachments-list').append(upload);
                    addtip(attach);
                },
            });
        }); 
    }
    upload.append('<input type="hidden" name="fileId[]" value=\'' + idATT + '\'/>');
    upload.find('.att-box-loading').remove();     
}

function organize_input_focus( start , borderID )
{
    var order = ["input_aux_to","input_aux_cc","input_aux_cco","input_aux_reply_to","input_subject"];
    var content = $('#content_id_'+borderID);

    for( var i = start ; i < order.length  ; i++ )
    {
        if( order[ i + 1 ] === undefined)
        {
            RichTextEditor.focus(borderID);
            break;
        }
        else if( !content.find('[name="'+order[ i + 1 ]+'"]').is(':hidden') )
        {
            content.find('[name="'+order[ i + 1 ]+'"]').focus();
            break;
        }
    }
}

function draw_new_message(border_ID){
	connector.loadScript("color_palette");
	connector.loadScript('wfolders');
	connector.loadScript("ccQuickAdd"); 
	
	if(typeof(RichTextEditor) == 'undefined' || typeof(ColorPalette) == 'undefined' || typeof(wfolders) == 'undefined')
		return false;

	if(typeof($.fn.elastic) == "undefined"){
		$.lazy({
			src: '../prototype/plugins/jquery-elastic/jquery.elastic.source.js',
			name: 'elastic'
		});	
	}
	var ID = create_border("",border_ID);
	
	if (ID == 0)
		return 0;
	else if(ID == 'maximo')
		return 'maximo';
		
	hold_session = true;

	if ($("#footer_menu").length){
		$("#footer_menu").css('display','none');
	}
	var content = $("#content_id_"+ID).html(DataLayer.render("../prototype/modules/mail/templates/new_message.ejs", {id: ID}));    
	RichTextEditor.loadEditor2(ID);

	content.find('[name="input_aux_to"],[name="input_aux_cc"],[name="input_aux_cco"],[name="input_aux_reply_to"],[name="input_subject"]').keydown( function( e )
	{
		if (e.which === 9)
		{
			if( this.name == 'input_aux_to' ) start = 0;
			else if( this.name == 'input_aux_cc' ) start = 1;
			else if( this.name == 'input_aux_cco' ) start = 2;
			else if( this.name == 'input_aux_reply_to' ) start = 3;
			else if( this.name == 'input_subject' ) start = 4;
			organize_input_focus(start , ID );
			e.preventDefault();
		}
	});

	draw_from_field(content.find(".from-select")[0], content.find(".from-tr")[0]);
	
	var check_input = function(field){
		var check = field.attr("checked");
		field.attr("checked", (!check ? true : false));
		return (!check ? true : false);
	}
	
	//AÇÃO GENERICA PARA ADICIONAR/REMOVER
	var change_text = function(field, text, to_text){
		var text = (field.html() == text ? to_text : text);
		field.html(text);
	}
	
	//AÇÃO GENERICA PARA ADICIONAR/REMOVER CC & CCO
	var show_hide = function(field, button){
		button.toggleClass("expressomail-button-icon-ative");
		field.toggle();
		field.find("textarea").val("").parent().find("input").focus();
		field.find(".email-area div").remove();
		if(!field.find("textarea").hasClass("track")){
			field.find("textarea").css({"max-height" : "115px", "overflow-y" : "hidden", "max-width" : parseInt(content.find(".email-area").css("width"))-28}).addClass("track").focus();	
			input_binds(field.find(".email-area"), ID);
		}
	} 
	
	input_binds(content.find('[name="input_aux_to"]').css("max-width" , parseInt(content.find(".email-area").css("width"))-28).focus().parent().css({"max-height" : "115px", "overflow-y" : "auto"}), ID);	
	
	//Botão TextoRico/TextoSimples
	content.find(".new-msg-head-right-buttons").find(".button").button().filter(".rich-button").click(function(){
		/*Se o texto do botão for "Texto simples" exibirá a mensagem antes de alterar para texto simples*/
		if($(".rich-button").find("span").text() == get_lang("Simple Text")){
			$.Zebra_Dialog(get_lang("Convert this message into plain text can make parts of it are removed. Continue?"), {
	            'type':     'warning',
	            'overlay_opacity': '0.5',
	            'buttons':  [get_lang('Yes'),get_lang('No')],
	            'width' : 380,
				'custom_class': 'custom-zebra-filter',
	            'onClose':  function(clicked) {
	                if(clicked == get_lang('Yes')){
	            		RichTextEditor.setPlain(check_input(content.find('[name="textplain_rt_checkbox"]')), ID);
		            	$(".rich-button").find("span").text(get_lang("Rich Text"));
	                } 
	            }
			})
		}
		/*Se o texto do botão for "Texto rico" simplesmente altera para texto rico*/
		else{
			RichTextEditor.setPlain(check_input(content.find('[name="textplain_rt_checkbox"]')), ID);
			$(".rich-button").find("span").text(get_lang("Simple Text"));
		}
	})
	
	//Botão Adicionar/Remover CCO
	.end().filter(".cco-button").click(function(){
		show_hide(content.find(".cco-tr"), $(this));
		change_text($(this).find(".ui-button-text"), get_lang("Add BCC"), get_lang('Remove CCo'));
	})
	//Botão Adicionar/Remover CC
	.end().filter(".cc-button").click(function(){
		show_hide(content.find(".cc-tr"), $(this));
		change_text($(this).find(".ui-button-text"), get_lang("Add CC"), get_lang('Remove CC'));
	})
    //Botão Responder a
    .end().filter(".reply-to-button").click(function(){
        show_hide(content.find(".reply-to-tr"), $(this));
    });
	
	//BOTAO ENVIAR
	content.find(".send").button({
		icons : {
			primary : "expressomail-icon-send"
		}
	}).click(function(){
		send_message(ID,preferences.save_in_folder,null);
	})
	//BOTAO SALVAR E ENVIAR
	.end().find(".save-and-send").button({
		icons : {
			primary : "expressomail-icon-send"
		}
	}).click(function(){
		wfolders.makeWindow(ID,"send_and_file");
	})
	//BOTAO SALVAR
	.end().find(".save").button({
		icons : {
			primary : "expressomail-icon-save"
		}
	}).click(function(){
		save_msg(ID);
        refresh();
	})
	//BOTAO CONF. LEITURA
	.end().find(".return-recept").button({
		icons : {
			primary : "expressomail-icon-read-confirmation"
		}
	}).click(function(){
		check_input(content.find('[name="input_return_receipt"]'));
		$(this).toggleClass("expressomail-button-icon-ative");
	})
	//BOTAO IMPORTANTE
	.end().find(".important").button({
		icons : {
			primary : "expressomail-icon-important"
		}
	}).click(function(){
		check_input(content.find('[name="input_important_message"]'));
		$(this).toggleClass("expressomail-button-icon-ative");
	})
	//BOTAO ASS. DIGITAL
	.end().find(".return_digital").button({
		icons : {
			primary : "expressomail-icon-signature"
		}
	}).click(function(){
		check_input(content.find('[name="input_return_digital"]'));
		$(this).toggleClass("expressomail-button-icon-ative");
	})
	//BOTAO EMAIL CRYPT
	.end().find(".return_cripto").button({
		icons : {
			primary : "expressomail-icon-encryption"
		}
	}).click(function(){
		check_input(content.find('[name="input_return_cripto"]'));
		$(this).toggleClass("expressomail-button-icon-ative");
	});
	
	content.find(".attachment td").filter(".value").prepend(DataLayer.render("../prototype/modules/mail/templates/attachment.ejs", {ID:ID}));
	var fileUploadMSG = $('#fileupload_msg'+ID);
	var maxAttachmentSize = (preferences.max_attachment_size !== "" && preferences.max_attachment_size != 0) ? (parseInt(preferences.max_attachment_size.replace('M', '')) * 1048576 ) : 41943040;
	
	content.find(".new-msg-head-data").scroll(function(){
		$.each(fileUploadMSG.find(".attachments-list .att-box"), function(index, value){
			$(this).qtip("api").updatePosition();
			$(this).qtip("api").updateWidth();
		});
	});
	$("#fileupload_msg"+ID+"_droopzone").click(function(){
		$(this).removeClass('in hover');
    	$(this).hide();
    	$(this).prev().show();
	});


    fileUploadMSG.find(".button").button().filter(".fileinput-button").find(".ui-button-text").css("margin-top","2px").find("input:file").fileupload({
		//singleFileUploads : true,fileUploadMSG
		sequentialUploads: true, 
		type: 'post',
		dataType : 'json',
		url: "../prototype/post.php",
		forceIframeTransport: false,
		dropZone : $("#fileupload_msg"+ID+"_droopzone"),
		formData: function(form) {
			return [
				{
					name : "mailAttachment[0][source]",
					value : "files0"
				},
				{
					name : "mailAttachment[0][disposition]",
					value : $(form[0]['attDisposition'+$(form[0]['abaID']).val()]).val()
				},
				{
					name: "MAX_FILE_SIZE",
					value : maxAttachmentSize
				}
			];
		},	
		add: function (e, data) {
            var iterator = idattachbycontent;
			if(!maxAttachmentSize || data.files[0].size < maxAttachmentSize || is_ie) {
				setTimeout(function() {
                    $('#attDisposition'+ID).val('attachment');
					jqXHR[iterator] = data.submit();
				}, 100);
			}
			fileUploadMSG.find(' .attachments-list').show();
			$.each(data.files, function (index, file) {
				var attach = {};
				attach.fullFileName = file.name;
				attach.fileName = file.name;
				if(file.name.length > 20)
					attach.fileName = file.name.substr(0, 17) + " ... " + file.name.substr(file.name.length-6, file.name.length);
				attach.fileSize = formatBytes(file.size);
				if(maxAttachmentSize && file.size > maxAttachmentSize)
					attach.error = 'Tamanho de arquivo nao permitido'
				else
					attach.error = true;
				var upload = $(DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist.ejs", {file : attach}));				
				upload.find('.att-box-delete').click(function(){
                    $("#content_id_"+currentTab+" .save").button("enable");
					var idAttach = $(this).parent().find('input[name="fileId[]"]').val();
					fileUploadMSG.find(' .attachments-list').find('input[value="'+idAttach+'"]').remove();
                    delAttachment(ID, idAttach);
                    $(this).parent().qtip("destroy");
					$(this).parent().remove();
					if(!fileUploadMSG.find(' .attachments-list').find(".att-box").length){
						fileUploadMSG.find(' .attachments-list').hide();
					}
                    if(jqXHR){
                        jqXHR[iterator].abort();
                    }
				});
                   
                fileUploadMSG.find('.attachments-list').append(upload);

              	fileUploadMSG.find('.attachments-list .att-box:last').qtip({
                	content: DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist_tooltip.ejs", {attach : attach}),
					position: {
						corner: {
							tooltip: 'bottomMiddle',
							target: 'topMiddle'
						},
						adjust: {
			               resize: true,
			               scroll: true,
			               screen: true
			            }
					},
					show: {
						when: 'mouseover', // Don't specify a show event
						ready: false // Show the tooltip when ready
					},
					hide: 'mouseout', // Don't specify a hide event
					style: {
						border: {
							width: 1,
							radius: 5
						},
						width: {
							 min: 75,
							 max : 1000
						},
						padding: 3, 
						textAlign: 'left',
						tip: true, // Give it a speech bubble tip with automatic corner detection
						name: (typeof(attach.error) == 'boolean' ? 'light' : 'red') // Style it according to the preset 'cream' style
					}
                })/*.progressbar({
                    value : 1
                })*/;

                fileUploadMSG.find('.attachments-list .att-box:last').css('width', fileUploadMSG.find('.attachments-list .att-box:last div:first').css('width'));

				if(!maxAttachmentSize || file.size < maxAttachmentSize){
					if(data.fileInput){
						fileUploadMSG.find('.fileinput-button.new').append(data.fileInput[0]).removeClass('new');
						fileUploadMSG.find('.attachments-list').find('[type=file]').addClass('hidden');	
					}
				}else
					fileUploadMSG.find(' .fileinput-button.new').removeClass('new');

				idattachbycontent++
			});
			
		},
		done: function(e, data){
            $("#content_id_"+currentTab+" .save").button("enable");
            var attach_box = fileUploadMSG.find('.att-box-loading:first').parents('.att-box');
            var attach = {
                fullFileName : attach_box.find(".att-box-fullfilename").text(),
                fileSize : attach_box.find(".att-box-filesize").text(),
                error : false
            };
			if(!!data.result && data.result != "[]" ){
				var newAttach = data.result;                             
				if(!newAttach.mailAttachment.error || newAttach.rollback !== false){
					attach_box.append('<input type="hidden" name="fileId[]" value="'+newAttach['mailAttachment'][0][0].id+'"/>');
					addAttachment(ID,newAttach['mailAttachment'][0][0].id);
                }else {
				    attach_box.addClass('invalid-email-box');
                    attach.error = newAttach.mailAttachment.error ? newAttach.mailAttachment.error : 'Erro ao anexar...';//.append(newAttach.mailAttachment.error).addClass('message-attach-error');   
                }
			}else {
				attach_box.addClass('invalid-email-box');//.qtip("api").updateContent("oi", true);
                attach.error = 'Erro ao anexar...';
			}
            attach_box.qtip("destroy").qtip({
                content: DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist_tooltip.ejs", {attach : attach}),
                position: {
                    corner: {
                        tooltip: 'bottomMiddle',
                        target: 'topMiddle'
                    },
                    adjust: {
                       resize: true,
                       scroll: true,
                       screen: true
                    }
                },
                show: {
                    when: 'mouseover', // Don't specify a show event
                    ready: false // Show the tooltip when ready
                },
                hide: 'mouseout', // Don't specify a hide event
                style: {
                    border: {
                        width: 1,
                        radius: 5
                    },
                    width: {
						 min: 75,
						 max : 1000
					},
                    padding: 3, 
                    textAlign: 'left',
                    tip: true, // Give it a speech bubble tip with automatic corner detection
                    name: (attach.error == false ? 'blue' : 'red')// Style it according to the preset 'cream' style
                }
            });/*
            fileUploadMSG.find(".att-box-loading:first").parents(".att-box").removeAttr("style").progressbar("destroy").find("div:first").removeAttr("style");
            */
            fileUploadMSG.find('.att-box-loading:first').remove();
		}/*,
        progress : function(e, data){
            var progress = parseInt(data.loaded / data.total * 100, 10);
            fileUploadMSG.find('.att-box-loading:first').parents(".att-box:first").progressbar({
                value : progress
            });
        }*/
	}).css({
        "height" : "20px", 
        // "width": (is_webkit ? "205px" : "100px"),
        "width" : "100px",
        "margin-top" : ($.browser.mozilla ? "3px" : "0"),
        "margin-right" : ($.browser.mozilla ? "15px" : "0"),
        "border-width": "0 0 0px 0px", 
        "transform" : "rotate(-360deg) translate(5px, -0.5px) scale(1.1)",  
        "-webkit-transform" : "rotate(-360deg) translate(0px, 0px)"
	});/*.end().end().end().*/

    fileUploadMSG.find(".message-attach-link").click(function(){
		jQuery('#message-attach-dialog').html(DataLayer.render("../prototype/modules/attach_message/attach_message.ejs", {}));
		var lastFolderSelected = $('#content_folders .folder.selected');
		$( "#mailpreview_container span.ui-icon-close" ).click();
		jQuery('#message-attach-dialog').dialog({
			width:945,
			height:550,
			resizable:false,
			modal: true,
			closeOnEscape:true,
			close:function(event, ui) 
            {
                event.stopPropagation();
                 if(typeof(shortcut) != 'undefined') shortcut.disabled = false;
                 lastFolderSelected.toggleClass('selected');
            },
            open: function(event, ui) 
            {
                if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
            },
			autoOpen:false
	});
	jQuery.getScript("../prototype/modules/attach_message/attach_message.js", function(){
		jQuery('#message-attach-dialog').dialog('open');
		jQuery('#message-attach-attach-btn').unbind('click');
		jQuery('#message-attach-attach-btn').click(function(event){
			jQuery.each(selectedMessages, function(folder_name, messages) {
				jQuery.each(selectedMessages[folder_name], function(message_number, message) { 
					if (message) {
						/* Anexa a mensagem especificada (por folder e id_msg) 
						   na mensagem sendo criada.*/
						attach_message(folder_name, message_number);
					}
				});
			});
			jQuery('#message-attach-dialog').dialog('close');
		});
		jQuery('#message-attach-cancel-btn').click(function(event){
			jQuery('#message-attach-dialog').dialog('close');
		});			
	});
	});
	$(document).bind('drop dragover', function (e) {
	    e.preventDefault();
	});
	//DRAG and DROP DE ARQUIVOS NÃO FUNCIONA NO IE
	if(!is_ie){
		$("body").bind('dragenter', function (e) {
		    var dropZone = $("#fileupload_msg"+ID+"_droopzone");
        	var timeout = window.dropZoneTimeout;
			dropZone.show();
			dropZone.prev().hide();
		    if (!timeout) {
		        dropZone.addClass('in');
		    } else {
		        clearTimeout(timeout);
		    }
		    if (e.target === dropZone[0]) {
		        dropZone.addClass('hover');
		    } else {
		        dropZone.removeClass('hover');
		    }
		    $(window).unbind('dragleave');
		    window.dropZoneTimeout = setTimeout(function () {
        		$(window).bind('dragleave', function (e) {
					window.dropZoneTimeout = null;
		        	dropZone.removeClass('in hover');
		        	dropZone.hide();
		        	dropZone.prev().show();
				});
		    }, 1);
		}).bind('dragleave', function (e) {
			var dropZone = $("#fileupload_msg"+ID+"_droopzone");
			window.dropZoneTimeout = setTimeout(function () {
        		$(window).bind('dragleave', function (e) {
					window.dropZoneTimeout = null;
		        	dropZone.removeClass('in hover');
		        	dropZone.hide();
		        	dropZone.prev().show();
				});
		    }, 1);
		}).bind('drop', function(e){
			var dropZone = $("#fileupload_msg"+ID+"_droopzone");
			window.dropZoneTimeout = null;
        	dropZone.removeClass('in hover');
        	dropZone.hide();
        	dropZone.prev().show();
		});
	}	
	return ID;
}

//	Verify if any user is sharing his name/email address
//	for use in the new messages's "From " field.
function draw_from_field(sel_from,tr1_1){

	var el_shared_users = Element("el_shared_users");
	/* Recupera a pasta atual do usuário (selecionada) */
    if ((typeof(folder) == "object") && folder.id){ 
        folder = folder.id; 
    } 
	var user_shared = folder.split(cyrus_delimiter);
	/* Verifica se está nas próprias pastas */
	if(user_shared[0] == "INBOX"){
		user_shared = User.me.uid;
	}else{
		user_shared = user_shared[1];
	}	
	// The element was loaded and populated...so return.
	if(el_shared_users){
		// Nothing to work...so return.
		if(el_shared_users.options.length == 0)
			return;
		tr1_1.style.display = '';
		for (var x = 0; x < el_shared_users.options.length; x++) {
			var _option = document.createElement("OPTION");
			_option.text = el_shared_users.options[x].text;
			_option.value = el_shared_users.options[x].value;
			/* Faz o tratamento do nome da pasta para fazer a verificação e selecionar o valor no select */
			var str_begin_name = _option.text.indexOf('<') + 1;
			var str_end_name = _option.text.indexOf('@');
			var user_selected_name = _option.text.substring(str_begin_name, str_end_name);
			/* Verifica se é o usuário da pasta selecionada */			
			if(user_selected_name == user_shared){
				_option.selected = 'selected';
			}
			sel_from.options[sel_from.options.length] = _option	;
		}
		return;
	}
	// Get the shared folders.....
 	var sharedFolders = new Array();
	for(var i = 0; i < folders.length; i++) {
		var x = folders[i].folder_id;
	  	if (folders[i].folder_parent == 'user'){
	  		sharedFolders[sharedFolders.length] = x;
	  	}
	}

	var matchUser = '#';
	var sharedUsers = new Array();
  	// Filter the shared folders (only root folders) .....
   	for(var i = 0; i < sharedFolders.length; i++) {
		matchUser = sharedFolders[i];
		sharedUsers[sharedUsers.length] = matchUser.substring(("user"+cyrus_delimiter).length,matchUser.length);
	}

	// Handler function for cExecute
	var h_user = function(data) {
		if(data.length > 0) {
			tr1_1.style.display = '';
            var mycn = typeof(data.myname != 'undefined') ? data.myname : '';
			var _option = document.createElement("OPTION");
			_option.text =  '"'+mycn+'" <'+Element("user_email").value+'>';
			_option.value  = mycn+";"+Element("user_email").value;
			/* Verifica se é o usuário logado */
			if(user_shared == User.me.uid)
				_option.selected = 'selected';
			sel_from.options[sel_from.options.length] = _option;

			var options = '';
            var cn = '';
			for (var x = 0; x < data.length; x++)	{
                                cn = typeof(data[x].cn[0] != 'undefined') ? data[x].cn[0] : '';
				var _option = document.createElement("OPTION");
				_option.text = '"'+cn+'" <'+data[x].mail[0]+'>';
				_option.value = cn+';'+data[x].mail[0]+';'+data[x].save_shared[0]+';'+data[x].uid[0];
				/* Faz o tratamento do nome da pasta para fazer a verificação e selecionar o valor no select */
				var str_begin_name = _option.text.indexOf('<') + 1;
				var str_end_name = _option.text.indexOf('@');
				var user_selected_name = _option.text.substring(str_begin_name, str_end_name); 
				/* Verifica se é o usuário da pasta selecionada */
				if(user_selected_name == user_shared){
					_option.selected = 'selected';
				}
				sel_from.options[sel_from.options.length] = _option	;
			}
		}
		var shared_users_from = Element("el_shared_users");
		if(!shared_users_from) {
			shared_users_from = sel_from.cloneNode(true);
			shared_users_from.id = "el_shared_users";
			shared_users_from.style.display = 'none';
			document.body.appendChild(shared_users_from);
		}
	}
	// First time, so execute.....
	cExecute ("$this.ldap_functions.getSharedUsersFrom&uids="+sharedUsers.join(';'), h_user);
}

function changeBgColorToON(all_messages, begin, end){
	var _tab_prefix = getTabPrefix();
	var _msg_id;
	for (begin; begin<=end; begin++)
	{
		_msg_id = getMessageIdFromRowId(all_messages[begin].id);
		add_className(all_messages[begin], 'selected_msg');
		Element(_tab_prefix + "check_box_message_" + _msg_id).checked = true;
		updateSelectedMsgs(true,_msg_id);
	}
}

function updateBoxBgColor(box){
	// Set first TR Class
	var _className = 'tr_msg_read2';
	for(var i = 0; i < box.length;i++){
		if(exist_className(box[i],_className))
			remove_className(box[i], _className);
		_className = (_className == 'tr_msg_read2' ? 'tr_msg_read' : 'tr_msg_read2');
		if(!exist_className(box[i],_className))
			add_className( box[i], _className);
	}
}
function changeBgColor(event, msg_number) {
	var _element_id = msg_number.toString();
	var first_order, last_order;

	if (typeof(currentTab)!='number') {
		_element_id = _element_id+'_s'+numBox;
	}
	actual_tr = Element(_element_id);

	if (event.shiftKey)
	{
		var last_tr = Element(last_message_selected);
		if(!last_tr)
			last_tr = actual_tr;

		var all_messages = actual_tr.parentNode.childNodes;

		for (var i=0; i < all_messages.length; i++)
		{
			if (actual_tr.id == all_messages[i].id)
				first_order = i;
			if (last_tr.id == all_messages[i].id)
				last_order = i;
		}

		if (parseInt(first_order) > parseInt(last_order))
			changeBgColorToON(all_messages, last_order, first_order);
		else
			changeBgColorToON(all_messages, first_order, last_order);
	}else if(event.target != document.getElementById(getTabPrefix()+'check_box_message_' + msg_number)){
		if($(event.target).attr("checked") == "checked"){
			$(event.target).parents("tr:first").addClass("selected_msg");
		}else{
			$(event.target).parents("tr:first").removeClass("selected_msg");
		} 
	}
	else{
		//if ( exist_className(actual_tr, 'selected_msg') )
		if ( document.getElementById(getTabPrefix()+'check_box_message_' + msg_number).checked ){
			if( document.getElementById("chk_box_select_all_messages").checked) {
				add_className(actual_tr, 'selected_msg selected_shortcut_msg'); 
			}else
			add_className(actual_tr, 'selected_msg');
		}else{
			if( document.getElementById("chk_box_select_all_messages").checked){
				remove_className(actual_tr, 'selected_msg selected_shortcut_msg');
				remove_className(actual_tr, 'selected_msg');
				remove_chk_box_select_all_messages();
				if(actual_tr.className == 'selected_msg')
			remove_className(actual_tr, 'selected_msg');
			}else
				remove_className(actual_tr, 'selected_msg');
		}
	}
	last_message_selected = _element_id;
}


function build_quota( data )
{
    // MAILARCHIVE
    // se for a pasta de mensagens locais
    if( proxy_mensagens.is_local_folder(current_folder) ) { return; }

    var content_quota   = $("#content_quota");

    if( !data['quota_limit'] )
    {
        content_quota.html('<span><font size="2" style="color:red"><strong>'+get_lang("Without Quota")+'</strong></font></span>');
    }
    else
    {
        var quota_limit     = data['quota_limit'];
        var quota_used      = data['quota_used'];
        var value           = data['quota_percent'];

        content_quota.html('');
        content_quota.css({'height':'15px'});

        var divDrawQuota = $("<div>");
        divDrawQuota.width(121);
        divDrawQuota.height(15);
        divDrawQuota.css({"background": "url(../phpgwapi/templates/"+template+"/images/dsunused.gif)","float":"left","margin-right":"5px", "cursor":"pointer"});
        divDrawQuota.on("click", function()
        {
            var showQuota = function(data)
            {
                var windowQuota = $("#window_InfoQuota");

                windowQuota.dialog({
                    modal       : true,
                    width       : 500,
                    height      : 400,
                    title       : get_lang("View Quota Usage in Folders"),
                    buttons     : [
                                   {
                                        text : get_lang("Close"),
                                        click : function()
                                        {
                                            $(this).dialog("destroy");
                                        }
                                   }]               

                });

                windowQuota.next().css("background-color", "#E0EEEE");
                windowQuota.html( new EJS( {url: 'templates/default/infoQuota.ejs'} ).render({ folders : data }));
                windowQuota.find("div[quota_percent]").each(function()
                {
                    var divQuotaUsed = $("<div>");
                    divQuotaUsed.width(parseInt($(this).attr("quota_percent"))+"%");
                    divQuotaUsed.height(15);

                    if( parseInt($(this).attr("quota_percent")) > 90 )
                    {
                        imageBackground = "url(./templates/"+template+"/images/dsalert.gif)";
                    }
                    else if( parseInt($(this).attr("quota_percent")) > 80 )
                    {
                        imageBackground = "url(./templates/"+template+"/images/dswarn.gif)";
                    }
                    else
                    {
                        imageBackground = "url(./templates/"+template+"/images/dsused.gif)";
                    }

                    divQuotaUsed.css({"background": imageBackground });

                    $(this).append(divQuotaUsed);
                }); 
            }

            cExecute ("$this.imap_functions.get_quota_folders", showQuota );
        });

        var divQuotaUsed = $("<div>");
        divQuotaUsed.width(value+"%");
        divQuotaUsed.height(15);

        var imageBackground = "";

        if( value > 90 )
        {
            if( value >= 100 )
                write_msg(get_lang("Your Mailbox is 100% full! You must free more space or will not receive messages."));
            else
                write_msg(get_lang("Warning: Your Mailbox is almost full!"));
            
            imageBackground = "url(./templates/"+template+"/images/dsalert.gif)";
        }
        else if( value > 80 )
        {
            imageBackground = "url(./templates/"+template+"/images/dswarn.gif)";
        }
        else
        {
            imageBackground = "url(./templates/"+template+"/images/dsused.gif)";
        }
        
        divQuotaUsed.css({"background": imageBackground });
        divDrawQuota.append(divQuotaUsed);

        var spanInfoQuota = $("<span>");
        spanInfoQuota.attr("class","boxHeaderText");
        spanInfoQuota.html( value + "% ( "+borkb(quota_used*1024)+" / "+borkb(quota_limit*1024)+" )" );

        content_quota.append(divDrawQuota);
        content_quota.append(spanInfoQuota);
    }
}

function draw_quota(data){
	build_quota(data);
}

function update_quota(folder_id){
	cExecute ("$this.imap_functions.get_quota&folder_id="+folder_id,build_quota);
}

function draw_search(headers_msgs){
	Element("border_id_0").innerHTML = "&nbsp;&nbsp;" + get_lang('Search Result') + "&nbsp;&nbsp;";

	var tbody = Element('tbody_box');
	for (var i=0; i<(headers_msgs.length); i++){
            // passa parâmetro offset
		var tr = this.make_tr_message(headers_msgs[i], headers_msgs[i].msg_folder);
		if (tr)
			tbody.appendChild(tr);
	}
}

function draw_search_header_box(){
	var table_message_header_box = Element("table_message_header_box");
	table_message_header_box.parentNode.removeChild(table_message_header_box);

	var content_id_0 = Element("content_id_0");
	var table_element = document.createElement("TABLE");
	var tbody_element = document.createElement("TBODY");
	table_element.setAttribute("id", "table_message_header_box");
	table_element.className = "table_message_header_box";
	tr_element = document.createElement("TR");
	tr_element.className = "message_header";
	td_element1 = document.createElement("TD");
	td_element1.setAttribute("width", "1%");
	chk_box_element = document.createElement("INPUT");
	chk_box_element.id  = "chk_box_select_all_messages";
	chk_box_element.setAttribute("type", "checkbox");
	chk_box_element.className = "checkbox";
	chk_box_element.onclick = function(){select_all_messages(this.checked);};
	chk_box_element.onmouseover = function () {this.title=get_lang('Select all messages.')};
	chk_box_element.onkeydown = function (e){
		if (is_ie)
		{
			if ((window.event.keyCode) == 46)
			{
				//delete_all_selected_msgs_imap();
				proxy_mensagens.delete_msgs(get_current_folder(),'selected','null');
			}
		}
		else
		{
			if ((e.keyCode) == 46)
			{
				//delete_all_selected_msgs_imap();
				proxy_mensagens.delete_msgs(get_current_folder(),'selected','null');
			}
		}
	};

	td_element1.appendChild(chk_box_element);
	td_element2 = document.createElement("TD");
	td_element2.setAttribute("width", "3%");
	td_element3 = document.createElement("TD");
	td_element3.setAttribute("width", "30%");
	td_element3.id = "message_header_SORTFROM";
	td_element3.align = "left";
	td_element3.innerHTML = get_lang("From");
	td_element4 = document.createElement("TD");
	td_element4.setAttribute("width", "49%");
	td_element4.id = "message_header_SORTSUBJECT";
	td_element4.align = "left";
	td_element4.innerHTML = get_lang("Subject");
	td_element5 = document.createElement("TD");
	td_element5.setAttribute("width", "10%");
	td_element5.id = "message_header_SORTARRIVAL";
	td_element5.align = "center";
	td_element5.innerHTML = "<B>"+get_lang("Date")+"</B>";
	td_element5.innerHTML += "<img src ='templates/"+template+"/images/arrow_descendant.gif'>";
	td_element6 = document.createElement("TD");
	td_element6.setAttribute("width", "10%");
	td_element6.id = "message_header_SORTSIZE";
	td_element6.align = "right";
	td_element6.innerHTML = get_lang("Size");
	tr_element.appendChild(td_element1);
	tr_element.appendChild(td_element2);
	tr_element.appendChild(td_element3);
	tr_element.appendChild(td_element4);
	tr_element.appendChild(td_element5);
	tr_element.appendChild(td_element6);

	tbody_element.appendChild(tr_element);
	table_element.appendChild(tbody_element);
	content_id_0.appendChild(table_element);
}

function draw_search_division(msg){
	var tbody = Element('tbody_box');
	var tr = document.createElement("TR");
	var td = document.createElement("TD");
	td.colSpan = '7';
	td.width = '100%';

	var action_info_table = document.createElement("TABLE");
	var action_info_tbody = document.createElement("TBODY");

	action_info_table.className = "action_info_table";
	action_info_table.width = "100%";

	var action_info_tr = document.createElement("TR");

	var action_info_th1 = document.createElement("TH");
	action_info_th1.width = "40%";
	action_info_th1.innerHTML = "&nbsp;";

	var action_info_th2 = document.createElement("TH");

	action_info_th2.innerHTML = msg;
	action_info_th2.className = "action_info_th";
	action_info_th2.setAttribute("noWrap", "true");

	var action_info_th3 = document.createElement("TH");
	action_info_th3.width = "40%";
	action_info_th3.innerHTML = "&nbsp;";

	action_info_tr.appendChild(action_info_th1);
	action_info_tr.appendChild(action_info_th2);
	action_info_tr.appendChild(action_info_th3);
	action_info_tbody.appendChild(action_info_tr);
	action_info_table.appendChild(action_info_tbody);

	td.appendChild(action_info_table);
	tr.appendChild(td);
	tbody.appendChild(tr);
}

function draw_search_box(){
	var content_id_0 = Element("content_id_0");
	var table = document.createElement("TABLE");
	table.id = "table_box";
	table.width = 'auto';
	var tbody = document.createElement("TBODY");
	tbody.id = "tbody_box";

	table.className = "table_box";
	table.setAttribute("frame", "below");
	table.setAttribute("rules", "none");
	table.setAttribute("cellpadding", "0");
	table.onkeydown = function (e){
		if (is_ie)
		{
			if ((window.event.keyCode) == 46)
			{
				//delete_all_selected_msgs_imap();
				proxy_mensagens.delete_msgs(get_current_folder(),'selected','null');
			}
		}
		else
		{
			if ((e.keyCode) == 46)
			{
				//delete_all_selected_msgs_imap();
				proxy_mensagens.delete_msgs(get_current_folder(),'selected','null');
			}
		}
	};
	if (is_ie)
		table.style.cursor = "hand";

	table.appendChild(tbody);
	content_id_0.appendChild(table);
}
	var idx_cc = 0;

function draw_plugin_cc(ID, addrs, notValidUser){
	connector.loadScript("ccQuickAdd");

	var array_addrs = '';
	var array_name 	= '';
	var cc_data = new Array();
	if(typeof(addrs.name) != 'undefined') {
		array_name 	= LTrim(addrs.name).split(" ");
		array_addrs = new Array(addrs.email);
	}
	else {
		array_addrs = (typeof addrs == 'object' ? addrs.toString().split("\" ") : addrs.split("\" "));
		array_name 	= LTrim(array_addrs[0]).replace('"','').split(" ");
	}

	var _split = array_name[0].split('@');
	cc_data[0] = _split[0];
	cc_data[1] = _split[0];
	cc_data[2] = '';

	for (i=1; i < array_name.length; i++)
		cc_data[2] += array_name[i] + " ";


	if(array_addrs.length > 1)
		cc_data[3] = array_addrs[1] ? array_addrs[1].replace("&lt;",'').replace("&gt;",'') : '';
	else
		cc_data[3] = array_addrs[0];

	var onclick = '';		
	$.each(cc_data, function(index, value){
		onclick += "'"+value+"',";
	});
	onclick = onclick.substr(0, onclick.length-1);
	
	var to_addybook_add = "<SPAN id='insert_plugin_"+idx_cc+"_"+ID+"'>";
	to_addybook_add += addrs;

	if(!!!notValidUser)
	{
		var sm_envelope_img1 = '<img style="cursor:'+ (is_ie ? 'hand' : 'pointer') +'" title="' + get_lang("Add Contact") +
			'" onclick="ccQuickAddOne.showList(['+onclick+'])" src="./templates/'+template+'/images/user_card.png">';
		to_addybook_add +=  sm_envelope_img1;
	}


	idx_cc++;
	to_addybook_add += "</SPAN>";
	return to_addybook_add;
}

function deny_email(email)
{
	var dn_em 	= document.createElement("SPAN");
		dn_em.id = "tt_d";
		dn_em.onclick = function(){block_user_email(email); /*filter.new_rule(email);*/};
		dn_em.setAttribute("title",get_lang("Block Sender"));
		dn_em.style.cursor = "pointer";
		dn_em.innerHTML = "<script src='../prototype/modules/filters/filters.js'></script><img align='top' src='./templates/"+template+"/images/deny.gif'>";
	return dn_em;
}

function show_div_address_full(id, type) {
	var div_address_full = Element("div_"+type+"address_full_"+id);
	if(!div_address_full) {
		div_address_full = document.createElement("SPAN");
		div_address_full.id = "div_"+type+"address_full_"+id;
		div_address_full.style.display="none";
		var _address = eval(type+"address_array['"+id+"']");
		var isOverLimit = (_address.length > 100);

		if(isOverLimit) {
			alert("Esse campo possui muitos endereços ("+_address.length+" destinatários).\r\n"+
			"Para evitar o travamento do navegador, o botão 'Adicionar Contato' foi desabilitado!");
		}

		for(var idx = 1 ; idx  < _address.length;idx++) {
			div_address_full.innerHTML += isOverLimit ?  '<br />'+_address[idx] : ','+draw_plugin_cc(id,_address[idx]);
		}
		div_address_full.innerHTML += " (<a STYLE='color: RED;' onclick=document.getElementById('div_"+type+"address_full_"+id+"').style.display='none';document.getElementById('div_"+type+"address_"+id+"').style.display='';>"+get_lang('less')+"</a>)";
		Element(type+"_"+id).appendChild(div_address_full);
	}
	Element('div_'+type+'address_'+id).style.display='none';
	div_address_full.style.display='';
}

function verifyContext(type) {
    var folderN = $(".menu-sel").attr("role") ? $(".menu-sel").attr("role") : get_current_folder();
    if(type == "unarchive"){
        expresso_mail_archive.unarchieve(folderN, null, null);
    } else {
        archive_msgs(folderN,null,null)
    }
}

function draw_footer_box(num_msgs){
	folder = get_current_folder();
	connector.loadScript('wfolders');
	var span_R = Element("table_message");
	var span_options = Element("span_options");
	if(!span_options) {
		span_options = document.createElement("TD");
		span_options.style.fontSize = "12";
		span_options.id = "span_options";
		span_R.appendChild(span_options);
	}

	var change_font_color = 'onmouseover="var last_class = this.className;'+
				'if (this.className != \'message_options_over\')'+
				'this.className=\'message_options_active\'; '+
				'this.onmouseout=function(){this.className=last_class;}"';

	span_options.innerHTML =
		'<span class="message_options_trash"><span ' + change_font_color + ' title="'+get_lang("Delete")+'" class="message_options" onclick=proxy_mensagens.delete_msgs(\'null\',\'selected\',\'null\')>'+get_lang("Delete")+'</span></span>'+
		'<span class="message_options_move"><span ' + change_font_color + ' title="'+get_lang("Move")+'" class="message_options" onclick=wfolders.makeWindow(\"\",\"move_to\")>'+get_lang("Move")+'</span></span>'+
   		((expresso_offline)?" ":'<span class="message_options_print"><span ' + change_font_color + ' title="'+get_lang("Print")+'" class="message_options" onclick=print_all()>'+get_lang("Print")+'</span></span>')+
//		'<span class="message_options_print"><span ' + change_font_color + ' title="'+get_lang("Print")+'" class="message_options" onclick=print_all()>'+get_lang("Print")+'</span></span>'+
		((expresso_offline)?" ":'<span class="message_options_export"><span ' + change_font_color + ' title="'+get_lang("Export")+'" class="message_options" onclick="proxy_mensagens.export_all_messages()">'+get_lang("Export")+'</span></span>') +
		((expresso_offline)?" ":'<span class="message_options_import"><span ' + change_font_color + ' title="'+get_lang("Import")+'" class="message_options" onclick="import_window()">'+get_lang("Import")+'</span></span>');

    
    //Link arquivar e desarquivar com ação
    //MAILARCHIVER
    if(preferences.use_local_messages==1){
        if(expresso_mail_archive.enabled){
           if(proxy_mensagens.is_local_folder(current_folder))//Unarchive link
             span_options.innerHTML += '&nbsp; <span title="'+get_lang("Unarchive")+'" class="message_options" onclick="verifyContext(\'unarchive\')">'+get_lang("Unarchive")+'</span>';
           else//Archive link                 
             span_options.innerHTML += '&nbsp; <span title="'+get_lang("Archive")+'" class="message_options" onclick="verifyContext(\'archive\')">'+get_lang("Archive")+'</span>';
        }
    }

	if (use_spam_filter) {
		if ( current_folder == 'INBOX'+cyrus_delimiter+'Spam' )	{
			span_options.innerHTML += ' | <span ' + change_font_color + ' title="'+get_lang("Not Spam")+'" class="message_options" onclick="nospam(\'selected\',\'null\',\'null\')">'+get_lang("Not Spam")+'</span>';
		}
		else {
			span_options.innerHTML += ' | <span ' + change_font_color + ' title="'+get_lang("Mark as Spam")+'" class="message_options" onclick="spam(\'null\', \'selected\',\'null\')">'+get_lang("Mark as Spam")+'</span>';
		}
	}
	var span_D = Element("span_D");
	if(!span_D){
		span_D = document.createElement("TD");
		span_D.align = "right";
		span_D.style.fontSize = "12";
		span_D.id = "span_D";
		span_R.appendChild(span_D);
	}
    
    var answer = '<span ' + change_font_color + ' id="span_flag_ANSWERED" class="'+(search_box_type == 'ANSWERED' ? 'message_options_over' : 'message_options')+'" title="'+get_lang("title_answered")+'" onclick="if(\'ANSWERED\' == \''+search_box_type+'\') return false;sort_box(\'ANSWERED\',\''+sort_box_type+'\')">'+get_lang("l_answered")+'</span>, ';

	span_D.innerHTML =
   		 get_lang("List")+': '+
   	'<span ' + change_font_color + ' id="span_flag_SORTARRIVAL" class="'+(search_box_type == 'ALL' ? 'message_options_over' : 'message_options')+'" title="'+get_lang("All")+'" onclick="if(\'ALL\' == \''+search_box_type+'\') return false;sort_box(\'ALL\',\''+sort_box_type+'\')">'+get_lang("All")+'</span>, '+
   	'<span ' + change_font_color + ' id="span_flag_UNSEEN" class="'+(search_box_type == 'UNSEEN' ? 'message_options_over' : 'message_options')+'" title="'+get_lang("l_unseen")+'" onclick="if(\'UNSEEN\' == \''+search_box_type+'\') return false;sort_box(\'UNSEEN\',\''+sort_box_type+'\')">'+get_lang("l_unseen")+'</span>, '+
  	'<span ' + change_font_color + ' id="span_flag_SEEN" class="'+(search_box_type == 'SEEN' ? 'message_options_over' : 'message_options')+'" title="'+get_lang("l_seen")+'" onclick="if(\'SEEN\' == \''+search_box_type+'\') return false;sort_box(\'SEEN\',\''+sort_box_type+'\')">'+get_lang("l_seen")+'</span>, '+
   	answer+
   	'<span ' + change_font_color + ' id="span_flag_FLAGGED" class="'+(search_box_type == 'FLAGGED' ? 'message_options_over' : 'message_options')+'" title="'+get_lang("l_important")+'" onclick="if(\'FLAGGED\' == \''+search_box_type+'\') return false;sort_box(\'FLAGGED\',\''+sort_box_type+'\')">'+get_lang("l_important")+'</span>&nbsp;&nbsp;';
    if(!proxy_mensagens.is_local_folder(current_folder)){
        draw_paging(num_msgs);
        Element("tot_m").innerHTML = num_msgs;
    }
}
