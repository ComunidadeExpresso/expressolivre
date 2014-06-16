var BordersArray = new Array();
BordersArray[0] = new setBorderAttributes(0);
var countBorders = 0; 
var partMsgs = new Array(); 
var msgAttachments = new Array();
var uidsSave = new Array(); 
var zebraDiscardEventDialog = false;
var saveBorderError = new Array(); 

function setBorderAttributes(ID)
{
    this.border_id = "border_id_"+ID;
    this.sequence = ID; 
}


function alternate_border(ID)
{
	//Controle da seleção de mensagens   

    msg_selected = false;//Controle da seleção de mensagens
    if(isNaN(ID))
        if(preferences.use_shortcuts == "1")
            select_msg((ID.split("_"))[0]);
    if( document.getElementById("div_menu_c3") != null )
    {
        //div que contém divs de paginação de todas abas de listagem possíveis (listagem de pasta e listagem de pesquisa)
        var node = document.getElementById("div_menu_c3").firstChild;
        //Se for diferente da aba de listagem, remove a paginação
        if(currentTab != 0){
            //Enquanto node for true (retorna false se não houver mais elementos)
            while ( node ) {
                node.style.display = "none";
                node = node.nextSibling;
            }
        }
        
        if( document.getElementById("span_paging"+ID) != null )
            document.getElementById("span_paging"+ID).style.display = "block";
    }
    
    if ( typeof win == 'object' && win.close && win.close.constructor == Function ){
            var search_win = document.getElementById( 'window_QuickCatalogSearch' );
            if(search_win){
                search_win.style.visibility = 'hidden';
            }
            win.close( );
        }
        
    if (! Element('border_id_'+ID))
        return false; // Not possible to alternate
    show_hide_span_paging(ID);
    spanD = Element("span_D");
    if (spanD)
        spanD.style.display = (openTab.type[ID] == 0 ? '' : 'none');

    var footer_menu = Element("footer_menu");   
    var aba = Element('border_id_'+ID);
    if (footer_menu != null) {
        footer_menu.style.display = (openTab.type[ID] != 4 ? '' : 'none');
        var alternate_menu = document.getElementById('localOption');
        
        if(alternate_menu != null && alternate_menu != 'undefined'){ //Quando Carregado o expresso mail
            if(openTab.imapBox[ID]!= null && openTab.imapBox[ID]!= 'undefined' ){ //Quando abrir uma Nova Mensagem
                if((openTab.imapBox[ID].indexOf("local_") >= 0)){                       
                        alternate_menu.title = get_lang("Unarchive");
                        alternate_menu.removeAttribute("onclick");
                        if(!is_ie)
                            alternate_menu.setAttribute("onclick",  'expresso_local_messages.unarchive_msgs(\''+openTab.imapBox[ID]+'\', null)');
                        else{
                            alternate_menu.onclick = function(){
                                expresso_local_messages.unarchive_msgs(openTab.imapBox[ID], null);
                            }
                        }
                        alternate_menu.innerHTML = get_lang("Unarchive");
                        
                    }else{
                        alternate_menu.title = get_lang("Archive");
                        alternate_menu.removeAttribute("onclick");
                        if(!is_ie)
                            alternate_menu.setAttribute("onclick", 'archive_msgs(\''+openTab.imapBox[ID]+'\', null)');      
                        else{
                            alternate_menu.onclick = function(){
                                archive_msgs(openTab.imapBox[ID], null);
                            }   
                        }
                        alternate_menu.innerHTML = get_lang("Archive");
                    }
                    
            }
        }
        
        if((aba.id.indexOf("_r") < 0) && (aba.id.indexOf("_0") < 0) && (aba.id.indexOf("id_search_") < 0) && (aba.id.indexOf("_s") < 0)){
            spanD.style.display = 'none';
            footer_menu.style.display = 'none';         
        }
    }


    var len = BordersArray.length;
    for (var i=0; i < len; i++)
    {
        m = document.getElementById(BordersArray[i].border_id);
        if ((m)&&(m.className == 'menu-sel'))
        {
            m.className = 'menu';
            c = document.getElementById("content_id_"+BordersArray[i].sequence);
            c.style.display = 'none';
            if(Element("font_border_id_"+BordersArray[i].sequence))
                Element("font_border_id_"+BordersArray[i].sequence).className = 'font-menu';    

        }
    }

    m = Element("border_id_"+ID);
    if (m)
        m.className = 'menu-sel';
    if(Element("font_border_id_" + ID))
        Element("font_border_id_" + ID).className = 'font-menu-sel';
    var c = Element("content_id_"+ID)
    if (c)
        c.style.display = '';


    // hide the DropDrowContact, if necessary
    window_DropDownContacts = Element('tipDiv');
    if ((window_DropDownContacts)&&(window_DropDownContacts.style.visibility != 'hidden')){
        window_DropDownContacts.style.visibility = 'hidden';
    }

    numBox = getNumBoxFromTabId(ID);
    if (typeof(ID)=='number') {
                 numBox = ID;
         }
         else {
             if (ID.match("search_"))
             {
                 if (ID.match("search_local_msg"))
                 {
                         var p = ID.search(/[0-9]/);
                         numBox =  ID.substr(p);
                 }
                 else
                 {
                         numBox = ID.substr(7);
                 }
             }
         }
    currentTab=ID;
    if( document.getElementById('to_'+ID) && document.getElementById('to_'+ID).type == "textarea"){ 
        document.getElementById('to_'+ID).focus(); 
    }
    if (ID == 0){ 
        updateSelectedMsgs();
    }
    RichTextEditor.setEditable(ID);
    resizeWindow();

    return ID;
}

function create_border(borderTitle, id_value, search)
{
    borderTitle = ( ( borderTitle && borderTitle.constructor == String && borderTitle.length > 0 ) ? borderTitle : ' ' );
    borderTitle = html_entities(borderTitle);    
    var resize = false;
        resize = resize_borders();
        if (!resize){
            var str_continue = '';
            var bolContinue = true;
            str_continue = '\n' + get_lang('You must manually close one of your tabs before opening a new one');
            if (preferences.auto_close_first_tab == 1){
                var children = Element('border_tr').childNodes;
                var bolDelete = true;
                for (var i=0; i<children.length; i++) {
                    if ((children[i].nodeName === 'TD') && (children[i].id!=='border_id_0') && (children[i].id!=='border_blank'))
                    {
                        bolDelete = true;
                        var num_child = children[i].id.toString().substr(10);
                        alternate_border(num_child);
                        if (editTest(num_child)){
                            bolDelete = false;
                        }
                        if (bolDelete || bolContinue){
                            str_fechar = '\n' + get_lang('Reached maximum tab limit. Want to close this tab');
                            var confirmacao = confirm(str_fechar);
                            if(confirmacao){
                            bolContinue = false;
                            delete_border(num_child, 'false');
                            break;
                            }else{
                                return 'maximo';
                        }
                    }
                }
            }
            }else{          
                alert(get_lang('Reached maximum tab limit') + str_continue );
                return 'maximo';
            }
        }
    
    if (! id_value){ // Is new message?
        var ID = parseInt(BordersArray[(BordersArray.length-1)].sequence) + 1;
            if(isNaN(ID)){
                var aux = BordersArray[(BordersArray.length-1)].sequence.split("_");
                ID = parseInt(aux[1]) + 1;
            }
        }else
    {
        if (Element("border_id_"+id_value)) // It's opened already!
            return alternate_border(id_value);
        
        var ID = id_value;
        if(isNaN(ID) && ID.indexOf("search_local") >= 0){
            if(current_folder.indexOf("local") >= 0)
                openTab.imapBox[ID] = current_folder;
            else
                openTab.imapBox[ID] = 'local_search';
        }else if(isNaN(ID) && ID.indexOf("search_") >= 0){
            if(current_folder.indexOf("local") < 0)
                openTab.imapBox[ID] = current_folder;
            else
                openTab.imapBox[ID] = 'search';
        }else if( (currentTab != 0) && isNaN(currentTab) && (currentTab.indexOf("search") >= 0) && (ID.indexOf("msg") < 0) ) {
            var id_border = currentTab.replace(/[a-zA-Z_]+/, "");
            ID_TR = ID.toString().substr(0,ID.toString().indexOf("_r"));
            var tr = Element(ID_TR) ? Element(ID_TR) : Element(ID_TR+"_s"+id_border);
            openTab.imapBox[ID] = (tr.getAttribute('name') == null?get_current_folder():tr.getAttribute('name'));
        }else
            openTab.imapBox[ID] = current_folder;
    }
    td = document.createElement("TD");
    td.id="border_id_" + ID;
    if(resize) 
    {
        td.setAttribute("width", parseInt(resize)+"px");
        td.style.width = parseInt(resize)+"px";
    }
    else
        td.setAttribute("width", "200px");

    td.setAttribute("align", "right");
    td.onclick = function(){alternate_border(ID);resizeWindow()};
    td.setAttribute("noWrap","true");
    td.setAttribute("role",get_current_folder());
    td.title = borderTitle;
    borderTitle = borderTitle ?  borderTitle : id_value ? get_lang("No Subject") : " "  ;
    td.value = borderTitle;
    if (borderTitle.length > 21)
        borderTitle = borderTitle.substring(0,21) + "...";

    if ( resize )
        borderTitle = borderTitle.substring(0, resize*0.08);
    
    var cc = search;
    if(!cc){
        if(isNaN(ID)){
            var is_local = ID.match('.*_local_.*');
            if(!is_local)
                cc = document.getElementById("em_message_search").value;
            else{
                if (currentTab == 0)
                    cc = "";
                else
                    cc = document.getElementsByName(currentTab)[0].value;
            }
        }else{
            cc ="";
        }
    }
    td.innerHTML = "<div><div id='font_border_id_" + ID+"' class='font-menu'>" +
                                borderTitle +
                            "</div>\n\
                            <div style='float:right;'>\n\
                                <img onmousedown='javascript:return false' style='cursor:pointer' onclick=delete_border('" + ID + "','false') src='templates/"+template+"/images/close_button.gif'/>\n\ " +
                            "</div>\n\ " + 
                            "<input type=\"hidden\" name=\""+ ID+"\" value=\""+cc+"\"></div>";      
    bb = document.getElementById("border_blank");
    parent_bb = bb.parentNode; //Pego o tbody
    parent_bb.insertBefore(td, bb);

    if((typeof(id_value) == 'string') && id_value.match(/_r/)){
        $(td).draggable({
            start : function(){
                $('.upper, .lower').show();
                $(".lower").css("top", ($("#content_folders").height()-18) + $("#content_folders").offset().top);
                /* Habilitar anexar mensagem por drag-and-drop, se a aba atual for editável,  
                ou seja, nem de leitura (2), nem de lista de mensagens (0):*/ 
                var current_tab_type = openTab.type[currentTab]; 
                if (current_tab_type != 0 && current_tab_type != 2){ 
                    var dropzone = $("#fileupload_msg" + currentTab + "_droopzone"); 
                    dropzone.show(); 
                    dropzone.prev().hide(); 
                    dropzone.droppable({ 
                        over: function (event, ui){ 
                            dropzone.addClass('hover in'); 
                            $(ui.helper).find(".draggin-folder,.draggin-mail").css("color", "green"); 
                        }, 
                        out: function (event, ui) { 
                            dropzone.removeClass('hover in'); 
                            $(ui.helper).find(".draggin-folder,.draggin-mail").css("color", ""); 
                        }, 
                        drop: function (event, ui) { 
                            var border_id = ui.draggable.find("input[type=hidden]").attr("name"); 
                            var id_msg = border_id.split("_")[0]; 
                            var folder = $("#input_folder_"+border_id+"_r")[0] ? $("#input_folder_"+border_id+"_r").val() : (openTab.imapBox[border_id] ? openTab.imapBox[border_id]:get_current_folder()); 
                            attach_message(folder, id_msg); /* Anexa a mensagem especificada (por folder e id_msg) 
                                                               na mensagem sendo criada.*/ 
                        } 
                    }); 
                } 
                if($(".shared-folders").length){
                    $(".shared-folders").parent().find('.folder:not(".shared-folders")').droppable({
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
                            //$("#content_folders").stop().scrollTo($(this), {axis:'y', margin:true, offset:-50, duration:400});
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
                            if(folder_to_move == "border_tr"){
                                var id_msg = border_id.split("_")[0];
                                folder = $("#input_folder_"+border_id+"_r")[0] ? $("#input_folder_"+border_id+"_r").val() : (openTab.imapBox[border_id] ? openTab.imapBox[border_id]:get_current_folder());
                                move_msgs2(folder, id_msg, border_id, folder_to, to_folder_title,true);
                                return refresh();
                            }
                        }
                    });
                }
            },
            stop :function(){
                $('.upper, .lower').hide();
                $(".shared-folders").parent().find(".folder").droppable("destroy");
                /* Habilitar anexar mensagem por drag-and-drop, se a aba atual for editável,  
                ou seja, nem de leitura (2), nem de lista de mensagens (0):*/ 
                var current_tab_type = openTab.type[currentTab]; 
                if (current_tab_type != 0 && current_tab_type != 2){ 
                    var dropzone = $("#fileupload_msg" + currentTab + "_droopzone"); 
                    dropzone.hide(); 
                    dropzone.prev().show(); 
                } 
            },
            helper: function(event){
                if( borderTitle.length > 18 )
                    return $("<td>"+DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : borderTitle.substring(0,18) + "...", type: "messages"})+"</td>");
                else
                    return $("<td>"+DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : borderTitle, type: "messages"})+"</td>");
            },
            delay: 150,
            cursorAt: {top: 5, left: 56},
            refreshPositions: true ,
            containment: "#divAppbox"
        });
    }
        //_dragArea.makeDragged(td, id_value,td.value);

    BordersArray[BordersArray.length] = new setBorderAttributes(ID);

    var div = document.createElement("DIV");
    div.id = "content_id_" + ID;
    div.className = "conteudo";
    div.style.display='';

    div.style.overflow = "hidden";

    Element("exmail_main_body").insertBefore(div,Element("footer_menu"));       
    if (!is_ie)
        resizeWindow();
    alternate_border(ID);
    uidsSave[ID] = [];
    saveBorderError[ID] = false;
    return ID;
}

function resize_borders()
{
    var numBorders = count_borders();

    if (numBorders > 8)
        return false;

    return redim_borders(numBorders+1);
}

function count_borders()
{
    var numBorders = 0;
    var children = Element('border_tr').childNodes;
    for (var i=0; i<children.length; i++) {
        if ((children[i].nodeName === 'TD') && (children[i].id!=='border_id_0') && (children[i].id!=='border_blank'))
        numBorders++;
    }

    return numBorders;
}

function redim_borders(numBorders)
{
    var children = Element('border_tr').childNodes;
    var clientWidth = (window.document.body.clientWidth - findPosX(Element("exmail_main_body"))) - Element("border_id_0").clientWidth - 30;
    var newWidthTD = (clientWidth/numBorders)-6;
    newWidthTD = newWidthTD > 200 ? 200 : (newWidthTD < 50 ? 50 : newWidthTD);
    children = Element('border_tr').childNodes;
    for (var i=0; i<children.length; i++) {
        if ((children[i].nodeName === 'TD') && (children[i].id!=='border_id_0') && (children[i].id!=='border_blank')){
            $(children[i]).css("width", newWidthTD);
            $(children[i]).find('div:first').css("width", newWidthTD);
            set_border_caption(children[i].id, children[i].title, newWidthTD);
        }
    }
    return newWidthTD;
}




function set_border_caption(border_id, title, border_width)
{
        var border = document.getElementById(border_id);
        if (border_width == null)
        {
            border_width = border.clientWidth;
        }
    var caption = "";
    if (border != null){
        Element("font_"+border.id).style.width = (border_width - 35)+'px';
        Element("font_"+border.id).innerHTML = title;
    }
    return(title);
}


function draftTests(ID, msg_sent){
        if( openTab.toPreserve[ID] = false)
        {
            close_delete(ID, msg_sent);
            delete(openTab.type[ID]);
        }    
        else  
        {
             var msg = '_[[There are unsaved changes in the message.]]';
             var buttons = ['_[[Discard changes]]', '_[[Save and close]]' ,'_[[cancel]]'];
             var width = 371;
             if($('#fileupload_msg'+ID).find('.in-progress').length)
             {
                 msg = '_[[Attachments are being sent to the server]]';
                 buttons = ['_[[Discard changes and attachments]]', '_[[Save current state close]]', '_[[Continue editing]]'];
                 width = 560;
             }
             zebraDiscardEventDialog = true;
             window.setTimeout(function() {
                $.Zebra_Dialog(msg, {
                            'type':     'question',
                            'overlay_opacity': '0.5',
                            'custom_class': 'custom-zebra-filter',
                            'buttons':  buttons,
                            'width' : width,
                            'onClose':  function(clicked) {
                                    if(clicked == '_[[cancel]]'){
                                        if (RichTextEditor.plain[id] != true) 
                                            setTimeout("RichTextEditor.focus("+ID+")",100);                  
                                        else  
                                            $('#body_'+ID).focus(); 
                                    }
                                    if(clicked == '_[[Discard changes]]' || clicked == '_[[Discard changes and attachments]]' ) {
                                        if (openTab.imapBox[ID] && !openTab.toPreserve[ID])
                                            openTab.toPreserve[ID] = false;

                                        delete(openTab.type[ID]);
                                        close_delete(ID, msg_sent);
                                       
                                    }
                                    else if(clicked == '_[[Save and close]]' || clicked == '_[[Save current state close]]')
                                    {
                                        save_msg(ID);
                                        openTab.toPreserve[ID] = false;
                                    
                                        close_delete(ID, msg_sent);
                                        delete(openTab.type[ID]);
                                    }    
                                    else{

                                        Element("border_id_"+ID).onclick = function () {alternate_border(ID);}; 
                                        var setFocus = function(ID){
                                                        if ($.trim($("#to_"+ID).val()) == "")
                                                                $("#to_"+ID).focus();
                                                        else if ($("#tr_cc_"+ID).css('display') != 'none' && $.trim($("#cc_"+ID).val()) == "")
                                                                $("#cc_"+ID).focus();
                                                        else if ($("#tr_cco_"+ID).css('display') != 'none' && $.trim($("#cco_"+ID).val()) == "")
                                                                $("#cco_"+ID).focus();      
                                                        else if ($.trim($("#subject_"+ID).val()) == "")
                                                                $("#subject_"+ID).focus();
                                                        else{
                                                                if (RichTextEditor.plain[id] != true) 
                                                                        setTimeout("RichTextEditor.focus("+ID+")",100);                  
                                                                else  
                                                                        $('#body_'+ID).focus(); 
                                                        }
                                                }
                                                setFocus(ID);  
                                    }
                                    window.setTimeout(function() {
                                            zebraDiscardEventDialog = false;
                                    }, 500);
                            }})}, 300);    
        }
    
}

function editTest(ID){
    var body = document.getElementById('body_'+ ID);
    var content = $('#content_id_'+ ID);
    if (body)
    {
        var save_link = content.find(".save");
        if (openTab.toPreserve[ID] == undefined)
                openTab.toPreserve[ID] = false;
        if (((! openTab.toPreserve[ID] && ! ID.toString().match("_r")) || ((body.contentWindow) == 'object' && body.contentWindow.document.designMode.toLowerCase() == 'on')) && (save_link.onclick != ''))
        {
            return true;
        }
    }
    return false;
}

function delete_border(ID, msg_sent)
{
    var borderElem = Element("border_id_" + ID);
    if (borderElem){
        borderElem.onclick = null;
    }else{
        return false;
    }

    if($("#content_id_"+ID+" textarea[name=input_to]").length ){
        if($("#content_id_"+ID+" .save").is(':disabled') ) {
             close_delete(ID, msg_sent);
        } else if( $("#content_id_"+ID+" .save").is(':enabled') ) {
             return(draftTests(ID, msg_sent));
        }
    } else {
        close_delete(ID, msg_sent);
    }

    delete(openTab.type[ID]);
    //refresh();
    return true;
     
    /*var bolExecuteClose = true;
    var borderElem = Element("border_id_" + ID);
    if (borderElem){
            borderElem.onclick = null; // It's avoid a FF3 bug
        }else{
            return false;
        }
    if (msg_sent == 'false')
    {
            if (editTest(ID)){
                bolExecuteClose = false;
                if(zebraDiscardEventDialog === false)
                    return(draftTests(ID, msg_sent));
            }
    }
    
        
        if (bolExecuteClose)
        {
            close_delete(ID, msg_sent);
        }
        delete(openTab.type[ID]);
        return true;*/
         
}

function close_delete(ID, msg_sent)
{
    openTab.toPreserve[ID] = false;
        // Limpa o autosave
            if (preferences.auto_save_draft == 1 && autoSaveControl.timer[ID] !== null )
            {
                autoSaveControl.status[ID] = null;
                clearInterval(autoSaveControl.timer[ID]);
            }
        ////////////////////////////////
        
    hold_session = false;
    if (exist_className(Element('border_id_'+ID),'menu-sel'))
    {
        if (BordersArray[BordersArray.length-2].sequence == ID)
            this.alternate_border(0);
        else
            this.alternate_border(BordersArray[BordersArray.length-2].sequence);
    }

    // Remove TD, title
    border = Element('border_id_' + ID);
    border.parentNode.removeChild(border);
    var j=0;
    var new_BordersArray = new Array();
    for (i=0;i<BordersArray.length;i++)
        if (document.getElementById(BordersArray[i].border_id) != null){
            new_BordersArray[j] = BordersArray[i];
            j++;
        }
    if(j == 1)
        Element("footer_menu").style.display = '';
    BordersArray = new_BordersArray;

    // Remove Div Content
    content = Element('content_id_' + ID);
    content.parentNode.removeChild(content);
    if(is_webkit)
        resizeWindow();
    RichTextEditor.destroy( 'body_'+ID );
        delete msgAttachments[ID];
        //Caso for uma mensagem anexada tem que deletar ela da lixeira apos fechar a aba
            var isPartMsg = false;
            if(!parseInt(id2))
                return;
            var id2 = ID.replace('_r','');
            for(var ii = 0; ii < partMsgs.length; ii++)
               if(partMsgs[ii] == id2){           
                  isPartMsg = true;
                  partMsgs[ii] = null;
               }     

            if(isPartMsg === true){
                var handler_delete_msg = function(){};
                cExecute ("$this.imap_functions.delete_msgs&folder=INBOX"+cyrus_delimiter+trashfolder+"&msgs_number="+id2,handler_delete_msg);
            }
        ///////////////////////////////////////////////////////////////////////////////////
        
    return true;
}

function getTabPrefix() { // define o prefixo para os checkboxes das mensagens
    if (typeof(currentTab)!='number')
        return currentTab+"_";
    else
        return "";
}

function getMessageIdFromRowId(row_id) { // extrai o id da mensagem do id da linha
    var p = row_id.search("_s");
    if (p>0)
        return row_id.substr(0,p);
    else
        return row_id;
}

function getNumBoxFromTabId(tab_id) { // extrai o numBox do id da tab
    if (typeof(tab_id)=='number') {
        return tab_id;
    }
    else {
        var p = tab_id.search(/[0-9]/);
        return tab_id.substr(p);
    }
}

function addAttachment(ID, att)
{
    if(typeof(msgAttachments[ID]) == 'undefined')
            msgAttachments[ID] = [];

        msgAttachments[ID].push(att);
}

function delAttachment(ID, att)
{
    
	if(msgAttachments[ID] == undefined) return;
    var len = msgAttachments[ID].length;
    for(var i = 0; i < len; i++)
    {
        if(msgAttachments[ID][i] == att)
        {
            delete msgAttachments[ID][i];
            break;
        }
    }
}

function listAttachment(ID)
{
    return (typeof(msgAttachments[ID]) == 'undefined') ? '' : JSON.stringify(msgAttachments[ID]);
}
