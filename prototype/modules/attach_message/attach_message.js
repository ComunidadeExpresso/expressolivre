// $.storage = new $.store();
 
isOffline = /[A-z0-9-_\/\.]*:offline\?(.*)/;
breakParams = /[&=]/;
dots = /\./gi;
dashes = /\//gi;
flags = [ 'Attachment', 'Forwarded' ,'Recent', 'Unseen',  'Answered',  'Draft',  'Deleted', 'Flagged', 'Followupflag', 'Label' ];

$.ajaxPrefilter(function( options, originalOptions, jqXHR ){

      var offlineAction = isOffline.exec( options.url );

      if( offlineAction )
      {
	  offlineAction = offlineAction[1] || "";
	
	  jqXHR.abort();

	  var params = {};
	  
	  if( offlineAction )
	      offlineAction +=  options.data ? "&" +  options.data : "";

	  offlineAction = offlineAction.split( breakParams );

	  for( var i = 0; i < offlineAction.length; )
	      params[ offlineAction[i++] ] = offlineAction[i++];

	  rest = params["q"].split("/");

	  if( !(rest.length % 2) )
	      var id = rest.pop();

	  var concept = rest.pop();

	  for( var i = 0; i < rest.length; )
	    params[ rest[i++] ] = rest[ i++ ];

	  switch( concept )
	  {
	    case "message":
	    {
		if( id ){
		    var mail = expresso_local_messages.get_local_mail( id );
		    mail.eml = expresso_local_messages.get_src( mail.url_export_file );

		    ( options.success || options.complete )( mail );
		    return;
		}

		var msgs = expresso_local_messages.get_local_range_msgs( params["folder"].replace(dots, "/").replace("local_messages/", ""),
									  params["rows"] * ( params["page"] - 1 ) + 1,
									  params["rows"], "SORTARRIVAL", (params["sord"] == "desc"),
									  "ALL", 1, 1 );

		for( var i = 0; i < msgs.length; i++ )
		{
		      msgs[i].size = msgs[i].Size;
		      msgs[i].timestamp = msgs[i].udate * 1000;
		      msgs[i].flags = [];

		      for( var ii = 0; ii < flags.length; ii++ )
			  if( f = $.trim( msgs[i][ flags[ii] ] ) )
			      msgs[i].flags[ msgs[i].flags.length ] =  f;

		      msgs[i].flags = msgs[i].flags.join(',');
		}

		( options.success || options.complete )( {"rows": msgs, 
							   "records": msgs.length,
							   "page": params["page"], 
							   "total": Math.ceil( msgs.num_msgs / params["rows"] )} );
	    }
	  }
      }
});

var BASE_PATH = '../prototype/';
//BASE_PATH = '../';
//encontra os pais de todas as pastas e cria uma nova estrutura adicionando os filhos a um array no atributo 'children' do respectivo pai
unorphanize = function(root, element) {
	var ok = false;
	for (var i=0; i<root.length; i++) {
		if (root[i].id == element.parentFolder) {
			element.children = new Array(); 
			root[i].children.push(element);
			return true;
		} else if (ok = unorphanize(root[i].children, element)) {
			break;
		}
	}

	return ok;
}

/* --- helpers --- */
bytes2Size = function(bytes) {
	var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
	if (bytes == 0) return '0 Bytes';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	var size = (i<2) ? Math.round((bytes / Math.pow(1024, i))) : Math.round((bytes / Math.pow(1024, i)) * 100)/100;
	return  size + ' ' + sizes[i];
}

flags2Class = function(cellvalue, options, rowObject) {
	var classes = '';	
	cellvalue = cellvalue.split(',');
	cell = {
			Unseen: parseInt(cellvalue[0])  ? 'Unseen' : 'Seen', 
			Answered: parseInt(cellvalue[1]) ? 'Answered' : (parseInt(cellvalue[2]) ? 'Forwarded' : ''), 
			Flagged: parseInt(cellvalue[3]) ? 'Flagged' : '',
			Recent: parseInt(cellvalue[4])  ? 'Recent' : '', 			
			Draft: parseInt(cellvalue[5]) ? 'Draft' : ''		
		};
	for(var flag in cell){
		classes += '<span class="flags '+ (cell[flag]).toLowerCase() + '"' + (cell[flag] != "" ? 'title="'+ get_lang(cell[flag])+'"' : '')+'> </span>';	
	}
	if(rowObject.labels){	
		var titles = [];
		var count = 0;
		 for(i in rowObject.labels){
			titles[count] = " "+rowObject.labels[i].name;
			count++;
		}
		titles = titles.join();
		classes += '<span class="flags labeled" title="'+titles+'"> </span>';
	}else{
		classes += '<span class="flags"> </span>';
	}
	
	if(rowObject.followupflagged){		
		if(rowObject.followupflagged.followupflag.id < 7){
			var nameFollowupflag = get_lang(rowObject.followupflagged.followupflag.name);
		}else{
			var nameFollowupflag = rowObject.followupflagged.followupflag.name;
		}
		if(rowObject.followupflagged.isDone == 1){
			classes += '<span class="flags followupflagged" title="'+nameFollowupflag+'" style="background:'+rowObject.followupflagged.backgroundColor+';"><img style=" margin-left:-3px;" src="../prototype/modules/mail/img/flagChecked.png"></span>';
		}else{			
			classes += '<span class="flags followupflagged" title="'+nameFollowupflag+'" style="background:'+rowObject.followupflagged.backgroundColor+';background-image:url(../prototype/modules/mail/img/mail-sprites.png);background-position: 0 -864px;"</span>';
		}
		
	}

	return classes;
}

function numberMonths (months){
	switch(months){
		case 'Jan':
			return 1;
		case 'Feb':
			return 2;
		case 'Mar':
			return 3;
		case 'Apr':
			return 4;
		case 'May':
			return 5;
		case 'June':
			return 6;
		case 'July':
			return 7;
		case 'Aug':
			return 8;
		case 'Sept':
			return 9;
		case 'Oct':
			return 10;
		case 'Nov':
			return 11;
		case 'Dec':
			return 12;
	}	
}

NormaliseFrom = function(cellvalue, options, rowObject) {
	rowObject['flags'] = rowObject['flags'].split(",");
	if(rowObject['flags'][rowObject['flags'].length-1] ==  1){
		return get_lang(special_folders["Drafts"]);
	}
	return cellvalue;	
}

NormaliseSubject = function(cellvalue, options, rowObject) {
	return html_entities(cellvalue);
}

date2Time = function (timestamp) {
	date = new Date();
	dat = new Date(timestamp);
	if ((date.getTime() - timestamp) < (24*60*60*1000)) {
		return '<span class="timable" title="'+dat.getTime()+'"></span>';
	} else {
		date = new Date(timestamp);
		if(is_ie){
			var b = date.toString().split(' ');
			var c = b[2] + "/" + numberMonths(b[1]) + "/" + b[5];
			return '<span class="datable">' + c + '</span>';
		}else{
			var b = date.toISOString().split("T")[0].split("-");
			var c = b[2] + "/" + b[1] + "/" + b[0];
			return '<span class="datable">' + c + '</span>';
		}
	}
}

changeTabIndex = function (elements) {
//	jQuery('#foldertree').attr('tabIndex', '1').focus();
}


selectedMessagesCount = function() {
	var byte_size = 0, total_messages = 0;
	for (var folder in selectedMessages) {
		for (var message in selectedMessages[folder]) {
			if (selectedMessages[folder][message]) {
				byte_size += parseInt(onceOpenedHeadersMessages[folder][message].size);
				total_messages++;
			}
		}
	}
	$("#selected_messages_number").html(total_messages).next().html(bytes2Size(byte_size));
	return total_messages;
}

var msgAttacherGrid = $("#message_attacher_grid"), msgsTotal = $("#selected_messages_number");
var lastLoadedMessages = [];
var selectedMessages   = {};
var selectedFolder     = {};

function mount_children_localfolders_list(folder){ 
    folder.children = new Array();
    folder.id_search = folder.id;
    folder.id = 'local_messages_'+folder.id,
    folder.commonName =  folder.name,
    folder.parentId = folder.parentid,
    folder.type = 'localFolder',
    folder.name = folder.id,
    folder.messageCount = {
                             total: folder.messages,
                             unseen: folder.unseen
                          }

    if(folder.haschild){
        expresso_mail_archive.getFoldersList(folder.id_search);
        folder.children = expresso_mail_archive.folders;

        for(var i = 0; i < folder.children.length; i++){
            mount_children_localfolders_list(folder.children[i]);
        }

    }
}
function adaptOffline( data )
{
    if( preferences.use_local_messages == 1 || expresso_offline)
    {
	var folders = expresso_local_messages.list_local_folders();
	
	var stripParents = /^(.*)\/([^\/]*)/;

	$.each( folders, function( i, folder ){
		
		  if(typeof(folder) == 'undefined')  return;
		  
	      var id = 'local_messages/' + folder[0];

	      var parts = stripParents.exec( id );
	  
	      data[data.length] = {'id' : id,
				    'commonName' : parts[2],
				    'parentFolder' : parts[1]};
	});
    }
	
    return( data );
}

function archive_flag( flagObj ){

    var flags = {};
    var returns = '';

    $.each( flagObj.tag , function(i, flag){
        flags[ flag['@value'] ] = true;
    });

	returns += (flags['seen'] ? '0,' : '1,') ;
	returns += (flags['answered'] && !flags['forwarded'] ? '1,' : '0,') ;
	returns += (flags['forwarded'] ? '1,' : '0,') ;
	returns += (flags['flagged'] || flags['importance_high'] ? '1,' : '0,') ;
	returns += '0,0';
            
    return returns;
}

function archive_flag_search( flagObj ){
    var flags = {};
    var returns = '';

    flagObj = flagObj.split("");
    $.each( flagObj , function(i, flag){
        flags[ flag ] = true;
    });

    returns += (flags['U'] ? '1,' : '0,') ;
    returns += (flags['A'] && !flags['X'] ? '1,' : '0,') ;
    returns += (flags['X'] ? '1,' : '0,') ;
    returns += (flags['F'] ? '1,' : '0,') ;
    returns += '0,0';
            
    return returns;
}


$mailpreview_tabs_label_length = 15;

/* --- jQuery handlers --- */

jQuery('#buttons-container .button').button();

jQuery.ajax({
	url: BASE_PATH + "REST.php?q=folder",
	dataType: 'json',

	success: function( data ){
		var tree1 = new Array();
		var tree2 = new Array();
		var tree3 = new Array();

        if(preferences.use_local_messages == 1){
            if(expresso_mail_archive.folders && !expresso_offline && expresso_mail_archive.enabled == true) { //MailArchive
                //pega pastas locais do mailarchiver e insere no array de pastas
                    expresso_mail_archive.getFoldersList("home");
                    treeFolders = expresso_mail_archive.folders;

                    for(var i=0; i<treeFolders.length; i++ ){
                        mount_children_localfolders_list(treeFolders[i]);
                    }
                    
                    for(var i = 0; i < treeFolders.length; i++){
                        data.push(treeFolders[i]);
                    }
            }
        }

		for (var i=0; i<data.length; i++) {

			if (/^INBOX/.test(data[i].id)) {
				if (!unorphanize(tree1, data[i])) {
					data[i].children = new Array();
					tree1.push(data[i]);
				}
			}
			else if (/^user/.test(data[i].id)) {
				if (!unorphanize(tree2, data[i])) {
					data[i].children = new Array();
					tree2.push(data[i]);
				}
			}
			else if (/^local_messages/.test(data[i].id)) {
					tree3.push(data[i]);
			}
			
		}

		var firstFolder = jQuery("#foldertree-container")
		.removeClass('empty-container')
		.html(DataLayer.render(BASE_PATH + 'modules/mail/templates/foldertree.ejs', {folders: [tree1, tree2, tree3]}))
		.find("#foldertree").treeview()
		.click(function(event){

			//request new selected folder messages
			var target = $(event.target);

			if( target.is('.collapsable-hitarea, .expandable-hitarea, .lastCollapsable, .lastExpandable, .treeview') )
			    return;

			if( !target.attr('id') )
			    target = target.parent();

            if (target.attr('id') == "foldertree") return;
			
			var targetId = target.attr('id');
			var child = target.find('.folder');
              
			$('.filetree span.folder.selected').removeClass('selected');
			if(!target.is('#foldertree > .expandable, #foldertree > .collapsable'))
				$(target).children('.folder').addClass('selected');
			
			selectedFolder = {
			    id: targetId, 
			    name: child.attr('title'),
			    'class': child.attr('class')
			};

			var grid = $("#message_attacher_grid"), offlineCase = "";
			
            if(targetId.indexOf( 'local_messages' ) == 0){
                 //Entrar caso: Clicar em uma pasta que seja do arquivamento local
                targetId = targetId.split("_")[2];

                expresso_mail_archive.getMessagesByFolder(targetId,"ALL");

                msgAll = expresso_mail_archive.msgAll;                

                var msgs = new Array();
                var from = '';
                var flag = '';
                
                $.each(msgAll, function(i, msg){
                    from = $.parseJSON(msg['_from']);

                    if(!from){
                        from = {"mailbox":{"name":"null","route":"null","localPart":"null","domain":"null"}};
                    }

                    flag = $.parseJSON(msg['_tags']);
                    id = msg['_id'];

                    var message = {};
                    
                    
                    message['msg_number'] = id;
                    message['flags'] = archive_flag( flag );
                    message['from.name'] = from['mailbox']['@name'];
                    message['subject'] = msg['_subject'];
                    message['timestamp'] = msg['_receivedDate'];
                    message['size'] = msg['_size'];
                    message['id'] = id;
                    msgs.push( message );

                });

            grid.jqGrid("clearGridData", true);
            grid.jqGrid('setGridParam',{datatype: "local",data: msgs})
                    .trigger("reloadGrid")
                    .jqGrid('setCaption', '<span class="'+child.attr('class')+'">'+child.attr('title')+'</span>');

            } else {
                if( !targetId.indexOf( 'local_messages/' ) )
                    offlineCase = ":offline";

                grid.jqGrid('setGridParam',{datatype: "json", url:BASE_PATH + 'REST.php'+offlineCase+'?q=folder/'+targetId.replace(dashes, '.')+'/message'})
                    .trigger("reloadGrid")
                    .jqGrid('setCaption', '<span class="'+child.attr('class')+'">'+child.attr('title')+'</span>');
            }
		})
		.find('span:first-child');
		$('span.folder.inbox').parents(".ui-dialog").find("li#INBOX span").addClass('selected');
		selectedFolder = {
			id: firstFolder.parent().attr('id'), 
			name: firstFolder.attr('title'),
			'class': firstFolder.attr('class')
		};

		//jqgrid
		jQuery("#mailgrid-container")
		.removeClass('empty-container')
		.html(DataLayer.render(BASE_PATH + 'modules/mail/templates/messagegrid.ejs', {}))
		.find("#message_attacher_grid")
		.jqGrid({
			url:BASE_PATH + 'REST.php?q=folder/INBOX/message',
			datatype: "json",
			mtype: 'GET',
			colNames:['#',' ', 'De', 'Assunto', 'Data', 'Tamanho'],
			colModel:[
				{name:'msg_number',index:'msg_number', width:45, hidden:true, sortable:false},
				{name:'flags',index:'msg_number',edittype: 'image', width:100, sortable:false, formatter:flags2Class, title :false},
				{name:'from.name',index:'msg_number', width:70, sortable:false, formatter:NormaliseFrom},
				{name:'subject',index:'subject', width:245, sortable:false,formatter:NormaliseSubject},
				{name:'timestamp',index:'timestamp', width:65, align:"center", sortable:false, formatter:date2Time},
				{name:'size',index:'size', width:55, align:"right", sortable:false, formatter:bytes2Size}
			],
			jsonReader : {
				  root: function(obj){
				  	obj['data'] = {};
		            obj.data = {rows : obj.rows};
				  	if(obj.rows){
				  		if(!obj.rows[0].flag){
					  		var msgs = [];
						  	$.each(obj.rows, function(i, msg){
								flag = msg['flags'];
		                        id = msg['msg_number'];
		                        var message = {};
		                     
		                        message['msg_number'] = id;
		                        message['flags'] = flag;
		                        message['to'] = {
		                                name: msg['toaddress2'],
		                                email: msg['toaddress2']
		                        };
		
		                        if(msg['from'] != undefined &&  msg['header']['from'] != undefined){
		                                message['from'] = {
		                                        'email' : msg['from']['email'],
		                                        'name' : msg['header']['from']['name']
		                                }
		                            }else{
		                                message['from'] = {
		                                        'email' : '',
		                                        'name' : 'Rascunho'
		                                }
		                            }
		                        //message['from']['name'] = msg['from']['name'];
		                        message['subject'] = msg['subject'] ? msg['subject'] : "(sem assunto)";
		                        message['timestamp'] = parseInt(msg['timestamp']);
		                        message['size'] = msg['size'];
		                        message['id'] = id;
		                        msgs.push( message );
			                });
							obj['data'] = {};
			                obj.data = {rows : msgs};
			            }
	                }
				  	return obj.data.rows;
				  },
				  page: "page",
				  total: "total",
				  records: "records",
				  repeatitems: false,
				  id: "0"
			},
			hidegrid:false,
			rowNum:10,
			rowList:[10,25,50],
			pager: '#message_attacher_grid_pager',
			sortname: 'id',
			viewrecords: true,
			sortorder: "desc",
			multiselect: true,
			autowidth: true,
			loadComplete: function(data) {
				lastLoadedMessages = data.rows;
				$("#mailgrid-container").find(".loading").hide();

				// aplica o contador
				jQuery('.timable').each(function (i) {
					jQuery(this).countdown({
						since: new Date(parseInt(this.title)), 
						significant: 1,
						layout: 'h&aacute; {d<}{dn} {dl} {d>}{h<}{hn} {hl} {h>}{m<}{mn} {ml} {m>}{s<}{sn} {sl}{s>}', 
						description: ' atr&aacute;s'
					});					
				});
				
				// reconstrói a seleção das mensagens mesmo depois da mudança de pasta
				if (selectedMessages[selectedFolder.id]) {
					for (var message in selectedMessages[selectedFolder.id]){
						for (var j=0; j<data.rows.length; j++){	
							if (selectedMessages[selectedFolder.id][message] && message == data.rows[j].msg_number) {
								jQuery("#message_attacher_grid").setSelection(jQuery("#message_attacher_grid").getDataIDs()[j], false);
							}
						}
					}
				}
				$('#cb_message_attacher_grid').css('display', 'none');
				
			},
            onSelectRow: function(id, selected)
            {
                /* Funções auxiliares:*/

                /*
                    Marca a mensagem como selecionada no grid de anexar mensagena.
                    Parâmetros:
                        folder: folder em que a mensagem a ser selecionada se encontra.
                        msg_number: id da mensagem a ser selecionada.
                */
                var mark_as_selected = function (folder, msg_number) {
                    if(!selectedMessages[folder])
                    {
                        selectedMessages[folder] = {};
                    }
                    selectedMessages[folder][msg_number] = true;
                }

                /*
                    Adiciona uma mensagem ao cache de mensagens.
                        cache: vetor de cache de mensagens.
                        msg: mensagem a ser armazenada.
                */
                var add_msg_to_cache = function (cache, msg) {
                    if(!cache[selectedFolder.id])
                    {
                        cache[selectedFolder.id] = {};
                    }
                    cache[selectedFolder.id][msg.msg_number] = msg;
                }

                /*
                    Adiciona uma aba de preview para cada mensagem selecionada.
                        subject: assunto da mensagem.
                        body: corpo da mensagem.
                */
                var add_preview_tab = function (subject, body) {
                    // Trunca o assunto, para caber na aba de preview:
                    var tabPanelTemplateLabel = html_entities(subject);
                    if(tabPanelTemplateLabel.length > $mailpreview_tabs_label_length + 3)
                    {
                        tabPanelTemplateLabel = tabPanelTemplateLabel.substring(0, $mailpreview_tabs_label_length) + '...';
                    }

                    // Se a aba não tiver sido adicionada:
                    if(!$('#' + tabPanelTemplateId).length)
                    {
                        // adiciona-a:
                        $mailpreview_tabs
                        .tabs("add", '#' + tabPanelTemplateId, tabPanelTemplateLabel)
                        .find('.message.empty-container')
                        .hide()
                        .end()
                        .find('#' + tabPanelTemplateId)
                        .html(body)
                        .prepend('<div class="mailpreview-message-info">' + get_lang('Subject') + ': ' + html_entities(subject) + '</div>')
                        .find('[class^="ExpressoCssWrapper"]')
                        .addClass("mailpreview-message-body");
                    }
                    else
                    {
                        // Senão, só a seleciona:
                        $mailpreview_tabs
                        .tabs('select', '#' + tabPanelTemplateId)
                        .find('#' + tabPanelTemplateId + ', [href="#' + tabPanelTemplateId + '"]')
                        .removeClass('preview-message-unselected');
                    }
                }

                var message = false;
                for(var i = 0; i < lastLoadedMessages.length; i++)
                {
                    if(lastLoadedMessages[i].msg_number == id)
                    {
                        message = lastLoadedMessages[i];
                        break;
                    }
                }
                var tabPanelTemplateId = 'mailpreview_tab_' + selectedFolder.id.replace(/[.\/]/g, '_') + '_' + message.msg_number;
                var tabPanelTemplateId = tabPanelTemplateId.replace(/[\s\/]/g, '-');

                if(selected)
                {
                    // Se a já mensagem (com preview) já estiver no cache:
                    if(onceOpenedHeadersMessages[selectedFolder.id] && onceOpenedHeadersMessages[selectedFolder.id][message.msg_number]  && onceOpenedHeadersMessages[selectedFolder.id][message.msg_number].body)
                    {

                        mark_as_selected(selectedFolder.id, message.msg_number);

                        add_preview_tab(
                            onceOpenedHeadersMessages[selectedFolder.id][message.msg_number].subject,
                            onceOpenedHeadersMessages[selectedFolder.id][message.msg_number].body
                        );
                    }
                    else // Mensagem não está no cache:
                    {
                        jQuery('#mailpreview_container').block(
                        {
                            message: '<div id="loading-content"><div class="image"></div></div>',
                            css: {
                                backgroundImage: 'url(' + BASE_PATH + 'modules/attach_message/images/loading.gif)',
                                backgroundRepeat: 'no-repeat',
                                backgroundPosition: 'center',
                                backgroundColor: 'transparent',
                                width: '32px',
                                height: '32px',
                                border: 'none'
                            },
                            overlayCSS: {
                                backgroundColor: '#CCC',
                                opacity: 0.5
                            }
                        });

                        mark_as_selected(selectedFolder.id, message.msg_number);

                        // Se for mensagem local:
                        if(selectedFolder['id'].indexOf("local_messages_") != -1)
                        {
                            expresso_mail_archive.getPreviewToAttach(id);

                            var body = expresso_mail_archive.bodyPreview;
                            var subject = expresso_mail_archive.subjectPreview;

                            add_preview_tab(subject, body);

                            $('#mailpreview_container').unblock();

                            add_msg_to_cache(onceOpenedHeadersMessages, message);
                        }
                        else
                        {
                            proxy_mensagens.get_msg(id, selectedFolder.id, '', function (data) {
                                message = $.extend(true, message, data);

                                add_preview_tab(message.subject, message.body);

                                $('#mailpreview_container').unblock();

                                add_msg_to_cache(onceOpenedHeadersMessages, message);


                            })
                        }
                    }
                }
                else
                {
                    /**
                     * if you wants to remove tab on unselect message,
                     * but still needs to uselect message on remove tab.
                     *
                     */
                    selectedMessages[selectedFolder.id][message.msg_number] = false;
                    $mailpreview_tabs
                    .find('#' + tabPanelTemplateId + ', [href="#' + tabPanelTemplateId + '"]')
                    .addClass('preview-message-unselected');
                }

                selectedMessagesCount();
            },
            
			caption: '<span class="'+selectedFolder['class']+'">'+selectedFolder.name+'</span>'
		});

        var search_local_messsages = function(param, folder){
            if(preferences.use_local_messages != 0)
            {
                folder = folder.split("_")[2];

                var local_folders   = [];
                expresso_mail_archive.search_queryresult = null;
                
                local_folders.push(folder);
                
                tmp = [];

                groupResult = [];
                    expresso_mail_archive.search(local_folders, "SUBJECT " +  "<=>" +url_encode(param) + "##");
                        groupResult.push( expresso_mail_archive.search_queryresult );
                    expresso_mail_archive.search(local_folders, "FROM " + "<=>" + url_encode(param) + "##");
                        groupResult.push( expresso_mail_archive.search_queryresult );
                    expresso_mail_archive.search(local_folders, "TO " + "<=>" + url_encode(param) + "##");
                        groupResult.push( expresso_mail_archive.search_queryresult );
                    expresso_mail_archive.search(local_folders, "CC " + "<=>" + url_encode(param) + "##");
                        groupResult.push( expresso_mail_archive.search_queryresult );
                    
                if($("#gbox_message_attacher_grid .attach-message-search-checkbox").is(":checked")){
                    expresso_mail_archive.search(local_folders, "BODY " + "<=>" + url_encode(param) + "##");
                        groupResult.push( expresso_mail_archive.search_queryresult );
                }

                
                 $.each(groupResult, function(i, result){
                        if(result != null){

                                var existsMessage = true;

                                $.each(result, function(i, each){

                                        $.each(tmp, function(i, ids){

                                            if(each.msg_number == ids.msg_number){

                                                    existsMessage = false;
                                                    return false;

                                            }

                                        });

                                        if(existsMessage) tmp.push(each);
                                });

                        }
                });


                if(tmp == null)
                {
                    tmp = new Object();
                    tmp.length = 0;
                }

                msgs = [];

                $.each(tmp, function(i, msg){

                    flag = msg['flag'];
                    id = msg['msg_number'];

                    var message = {};

                    var stamp =  msg['timestamp'];
                    stamp = stamp.toString() + "000";
                    stamp = parseInt(stamp);

                    message['msg_number'] = id;
                    message['flags'] = archive_flag_search(flag);//"1,1,1,1,1,1";//
                    message['from.name'] = msg['from'];
                    message['subject'] = msg['subject'];
                    message['timestamp'] = stamp;
                    message['size'] = msg['size'];
                    message['id'] = id;
                    msgs.push( message );
                });
                return msgs;
            }
        }
        var search_imap_messages = function(param, folder){
        	var grid = $("#message_attacher_grid");

        	DataLayer.storage.cache = {};
        	if( $(".attach-message-search-checkbox:checked").length > 0 ){
	            var filters = [
	        		[
	        		 	'from',
	        			'*',
	        			param
	        		],       	
	        		[
	            		'OR',
	            		'to',
	            		'*',
	            		param
	            	],
	        		[
	            		'OR',
	            		'subject',
	            		'*',
	            		param
	            	],
	            	[
	            		'OR',
	            		'folder',
	            		'*',
	            		folder
	            	],
	            	[
	            		'OR',
	            		'body',
	            		'*',
	            		param
	            	]
	            ];
	        }else{
	        	var filters = [
	        		[
	        		 	'from',
	        			'*',
	        			param
	        		],       	
	        		[
	            		'OR',
	            		'to',
	            		'*',
	            		param
	            	],
	        		[
	            		'OR',
	            		'subject',
	            		'*',
	            		param
	            	],
	            	[
	            		'OR',
	            		'folder',
	            		'*',
	            		folder
	            	]
	            ];
	        }
			
            var data = DataLayer.get( 'message', { filter: filters, criteria: { properties: { context: { folder: folder } } } }, true );

            if(DataLayer.criterias['message:jqGridSearch']){
				delete DataLayer.criterias['message:jqGridSearch'];	
			}

        	DataLayer.register( 'criterias', 'message:jqGridSearch', function( crit ){
			    crit.properties = { context: { folder: folder } };

			    return { filter: [ "msgNumber", "IN", data ], criteria: crit };
			});
            if(typeof(data) == 'object'){
				grid.jqGrid("clearGridData", true);
				grid.jqGrid('setGridParam',{datatype: "json", url: 'message:jqGridSearch'}).trigger("reloadGrid");

            }else{
            	$("#mailgrid-container").find(".loading").hide();
            	grid.jqGrid("clearGridData", true);
            }
        }

		var search_messages = function(param){
            var grid = $("#message_attacher_grid");
            var folder = $("#foldertree li span.selected").parent().attr("id");
            
            if(param == ""){
                $('#foldertree [id="'+folder+'"]').trigger("click");
                return;
            }
            $(".attach-message-search-input").val("");
            if(folder.indexOf("local_messages_") == 0){
                // Pesquisa pelas mensagens locais...
                msgs = search_local_messsages(param, folder);
            } else {
                // Pesquisa pelo Imap...
                return search_imap_messages(param, folder);
            }

            // Monta as mensagens na grid...
            grid.jqGrid("clearGridData", true);
            grid.jqGrid('setGridParam',{datatype: "local",data: msgs})
            .trigger("reloadGrid");
            //.jqGrid('setCaption', '<span class="'+child.attr('class')+'">'+child.attr('title')+'</span>');
		}

		var title = [get_lang("First page"), get_lang("Prev page"), get_lang("Next page"), get_lang("Last page")];
		$("#first_message_attacher_grid_pager").attr("title",title[0]);
		$("#prev_message_attacher_grid_pager").attr("title",title[1]);
		$("#next_message_attacher_grid_pager").attr("title",title[2]);
		$("#last_message_attacher_grid_pager").attr("title",title[3]);
		$("#mailgrid-container .ui-jqgrid-titlebar")
		.append( DataLayer.render("../prototype/modules/attach_message/attachment_search.ejs") ).find(".ui-jqgrid-titlebar-close").hide()
		.end().find(".attach-message-search-input").Watermark("Pesquisa...").keydown(function(e){
			var param = $(this).val();
			if($.ui.keyCode.ENTER == e.keyCode){
				$("#mailgrid-container").find(".loading").show("fast", function(){
					search_messages(param);
				});
			}
			
		}).end().find(".attach-message-search-checkbox").click(function(){
            var msg;
            if($(this).is(":checked")){
                msg = get_lang("take off this option to disregard the message body in the search.");
            } else {
                msg = get_lang("take on this option to regard the message body in the search.");
            }
            $(".attach-message-search-div .button-body-msg-title").attr("title",msg);
        });

		$("#attach-message-search").button({
			text: false,
			icons: {
				primary: "ui-icon-search"
			}
		}).next().button({
			text: false,
			icons: {
				primary: "ui-icon-script"
			}
		});
		$("#attach-message-search").parent().buttonset();

		$("#attach-message-search").click(function(){
			var param = $(this).parents(".attach-message-search-div:first").find(".attach-message-search-input").val();
			$("#mailgrid-container").find(".loading").show("fast", function(){
				search_messages(param);
			});	
		});
	}
});


var $mailpreview_tabs = $( "#mailpreview_container").tabs({
	tabTemplate: "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close'> Fechar </span></li>",
	panelTemplate: '<div class="message mailpreview-message"></div>',
	add: function( event, ui ) {
		$mailpreview_tabs.tabs('select', '#' + ui.panel.id);
	
		if ($('#mailpreview_tabs_default_empty').length && $mailpreview_tabs.tabs("length") > 1) {	
			$mailpreview_tabs.tabs('remove', '#mailpreview_tabs_default_empty');
		}
	},
	remove: function(event, ui) {
		if (!$mailpreview_tabs.tabs("length") && !$('#mailpreview_tabs_default_empty').length) {
			/**
			 * TODO: internacionalizar a string 'Nenhuma aba'
			 */
			$mailpreview_tabs.tabs('add', '#mailpreview_tabs_default_empty', 'Nenhuma aba')
			.find('#mailpreview_tabs_default_empty').removeClass('mailpreview-message').addClass('empty-container')
			.html('<span class="message">' + get_lang('select a message to preview') + '</span>').end()
			.find('.ui-tabs-nav li:first .ui-icon-close').remove();
		}
	}
});

$( "#mailpreview_container span.ui-icon-close" ).off("click");
$( "#mailpreview_container span.ui-icon-close" ).on( "click", function(e) {
	var index = $("li", $mailpreview_tabs).index($(this).parent());
	$mailpreview_tabs.tabs("remove", index);
	e.stopImmediatePropagation();
});

if (!$mailpreview_tabs.tabs("length") && !$('#mailpreview_tabs_default_empty').length) {
			/**
			 * TODO: internacionalizar a string 'Nenhuma aba'
			 */
			$mailpreview_tabs.tabs('add', '#mailpreview_tabs_default_empty', 'Nenhuma aba')
			.find('#mailpreview_tabs_default_empty').removeClass('mailpreview-message').addClass('empty-container')
			.html('<span class="message">' + get_lang('select a message to preview') + '</span>').end()
			.find('.ui-tabs-nav li:first .ui-icon-close').remove();
}