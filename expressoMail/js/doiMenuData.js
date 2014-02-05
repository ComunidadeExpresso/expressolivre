    function labeledMessages(isLabel){
        if(get_current_folder().split("_")[0] == "local"){
            alert("Pastas locais não fornecem suporte para adição de marcadores.")
            return true;
        }
    			if (isLabel) {
    				var label = {
    					id: isLabel
    				}	
    				var lableleds = new Array();
    				var msg_folder = current_folder;
    				var messages = new Array();
    				var messagesCache = [];
                    if(currentTab == 0){
    					var id = get_selected_messages().split(',');
    					for (i = 0; i < id.length; i++){
    						messages.push({
    							msg_number: id[i],
    							msg_folder: msg_folder
    						});
                            messagesCache[messagesCache.length] = id[i];
    					}
    				}else{
    					// var id = get_selected_messages_search().split(',');
    					// var id_border = currentTab.replace(/[a-zA-Z_]+/, "");
    					// for (i = 0; i < id.length; i++){
    						// var tr = Element(id[i]+'_s'+id_border);
    						// msg_folder = tr.getAttribute('name'); 
    						// messages.push({
    							// "msg_number": id[i],
    							// "msg_folder": msg_folder,
    						// });
    					// }	
    					
    					var roles = get_selected_messages_search_role().split(',');
    					for (i = 0; i < roles.length; i++){
    						var tr = $('[role="'+roles[i]+'"]');
    						msg_folder = $(tr).attr('name'); 
    						var id = $(tr).attr('id'); 
    						messages.push({
    							"msg_number": id.replace(/_[a-zA-Z0-9]+/,""),
    							"msg_folder": msg_folder
    						});
    					}
    				}
                    /*TODAS AS MENSAGENS QUE POSSUEM MARCADORES*/
                    var msgsLabeled = DataLayer.get('labeled',{ filter:[
                                'AND',
                                ['=', 'labelId', label.id],
                                ['=', 'folderName', msg_folder],
                                ['IN', 'messageNumber', messagesCache]
                                ], criteria: {deepness: '2'}});                  

                    /*VERIFICA SE A MENSAGEM msg POSSUI MARCADORES*/
                    var hasLabel = function (msg){
                        for(var index=0; index<msgsLabeled.length; index++){
                            if (msgsLabeled){
                                if (msgsLabeled[index].messageNumber == msg.msg_number && msgsLabeled[index].folderName == msg.msg_folder){
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
    				for (var i=0; i<messages.length; i++) {
    					if (!hasLabel(messages[i])) {
                            lableleds.push(
    							DataLayer.put('labeled', {
    								labelId:label.id, 
    								folderName:messages[i].msg_folder, 
    								messageNumber:messages[i].msg_number
    							})
    						);
    						
    					}
    				}
    				DataLayer.commit(false, false, function(data){
                        var ids = [];
                        for (var i=0; i < messages.length; i++) {
                            ids[ ids.length ] = messages[i].msg_number;
                        }

                        var labels = DataLayer.get('labeled',{ filter:[
                                'AND',
                                ['=', 'folderName', msg_folder],
                                ['IN', 'messageNumber', ids]
                                ], criteria: {deepness: '2'}});

                        var labelsIndex = {};
                        $.each( labels, function(i, e){

                            if( labelsIndex[ e.messageNumber ] ){
                                labelsIndex[ e.messageNumber ][ 'labels' ].push( e.label );
                            }else{
                                labelsIndex[ e.messageNumber ] = e;
                                labelsIndex[ e.messageNumber ][ 'labels' ] = [];
                                labelsIndex[ e.messageNumber ][ 'labels' ].push( e.label );
                            }
                        });
                        $.each( labelsIndex, function(i, e){
                            /* the force parameter verifies the client's cache */
                            updateLabelsColumn({msg_number: e.messageNumber, boxname: e.folderName, labels: e.labels, forceIcon: true });
                        });
    				});
    			}
    }

    function openListUsers(border_id) {
    	connector.loadScript("QuickCatalogSearch");
    	if (typeof(QuickCatalogSearch) == 'undefined'){
    					setTimeout('openListUsers('+border_id+')',500);
    					return false;
    				}
    	QuickCatalogSearch.showCatalogList(border_id);
    }


    /**
     * Cria a lista de marcadores para o submenu "Marcadores"
     */
    function getLabels(){
        var labels = DataLayer.get('label');

        labels = orderLabel( labels );

    	var menuLabelItems = {};
    		menuLabelItems["new"] = {
    			"name" : get_lang('New Label'),
    			callback:function() {configureLabels({applyToSelectedMessages:true});}
    		};
    	for(var i=0; i<labels.length; i++) {
    		menuLabelItems["label"+labels[i].id] = {
    			"name" : labels[i].name
    		}
    	}
    	return menuLabelItems;
    }

    /**
     *	Carrega o menu de opção de uma mensagem
     */
    function loadMenu(){
        var archive = "";

        if( preferences['use_followupflags_and_labels'] == "1" )
            var labelItems = getLabels();

        var revertSortBox = function(){
            if (search_box_type == "UNSEEN") sort_box_reverse = sort_box_reverse ? 0 : 1; 
        }

    	$.contextMenu({
    		selector: ".table_box tbody tr",
            build: function($trigger, e) {
                if(typeof (currentTab) == "string" && currentTab.indexOf("local") != -1){  
                    alert("Impossível manipular mensagens locais a partir de uma busca. Isso é permitido apenas para mensagens não locais.");
                    return true;
                }
                if(use_local_messages != 0){
                    if( current.indexOf("local") != -1){
                       archive = {"name": get_lang("Unarchive"), "icon": "archive", callback: function(key, opt){ proxy_mensagens.unarchive_message('inbox', 'get_selected_messages'); }}
                    } else {
                        archive = {"name": get_lang("Archive"), "icon": "archive", callback: function(key, opt){ proxy_mensagens.unarchive_message('inbox', 'get_selected_messages'); }}
                    }
                }

                if( preferences['use_followupflags_and_labels'] == "1" )
                    var itensNotLocal = {
                        "label": { "name": get_lang("Labels"), "items": labelItems},
                        "follouwpflag":{"name": get_lang("Follow up"), callback: function(key, opt){ configureFollowupflag(); } },
                        "sep2": "---------"
                    }

                var items= {
                    "flagSeen":      {"name": get_lang("Mark as") + " " + get_lang('seen'), "icon": "seen", callback: function(key, opt){ revertSortBox(); proxy_mensagens.proxy_set_messages_flag('seen','get_selected_messages'); }},
                    "flagUnseen":    {"name": get_lang("Mark as") + " " + get_lang('unseen'), "icon": "unseen", callback: function(key, opt){ revertSortBox(); proxy_mensagens.proxy_set_messages_flag('unseen','get_selected_messages'); }},
                    "flagFlagged":   {"name": get_lang("Mark as") + " " + get_lang('important'), "icon": "important", callback: function(key, opt){ revertSortBox(); proxy_mensagens.proxy_set_messages_flag('flagged','get_selected_messages'); }},
                    "flagUnflagged": {"name": get_lang("Mark as") + " " + get_lang('normal'), callback: function(key, opt){ revertSortBox(); proxy_mensagens.proxy_set_messages_flag('unflagged','get_selected_messages'); }},
                    "sep1": "---------"
                }
				
				// Desabilita a opção de criar filtro a partir da mensagem, caso mais de uma mensagem esteja selecionada: 
				var is_filterFromMsg_disabled = function () { 
					var base_selector = ".table_box tbody tr.selected_msg"; 
					return ($(base_selector).length > 1) || ($(base_selector + " td span").text().indexOf(get_lang("Draft")) > -1); 
				} 
				
                var lastItens = {
                    "move": {"name": get_lang("Move to")+"...", "icon": "move", callback: function(key, opt){ wfolders.makeWindow('', 'move_to'); }},
                    "remove": {"name": get_lang("Delete"),      "icon": "delete", callback: function(key, opt){ proxy_mensagens.delete_msgs('null','selected','null'); }},
                    "export": {"name": get_lang("Export"),      "icon": "export", callback: function(key, opt){ proxy_mensagens.export_all_messages(); }},
					/*Abre o diálogo de criação de filtro a partir da mensagem:*/ 
					"filterFromMsg": { 
						"name": get_lang("Create filter from message"),  
						"icon": "filter",  
						callback: function (key, opt) { 
							var msg_number = get_selected_messages(); 
							var msg = onceOpenedHeadersMessages[current_folder][msg_number]; 
							if (msg !== undefined) 
							{ 
								filter_from_msg(msg); 
							} 
						}, 
						disabled: is_filterFromMsg_disabled() 
					}, 
                    "archive": archive
                }

                var realItens = {};

                if(currentTab == 0){
                    if(get_current_folder().split("local").length > 1){
                        realItens = $.extend(items, lastItens);
                    }else{
                        realItens = $.extend(items, itensNotLocal);
                        realItens = $.extend(realItens, lastItens);
                    }
                }else if(currentTab.split("local").length > 1){
                    realItens = $.extend(items, lastItens);
                }else{
                    realItens = $.extend(items, itensNotLocal);
                    realItens = $.extend(realItens, lastItens);
                }

        		return { 
                    callback: function(key, options) {
        			//TODO - default actions
        			
        			/** 
        			 * Apply labels to selected messages
        			 */
            			var isLabel = key.match(/label(.*)/);
            			if (isLabel && isLabel.length > 1) {
            				labeledMessages(isLabel[1]);
            			}
            			selectAllFolderMsgs(false);
            		},
            		items: realItens
                }
            }
    	});
    }
    /*FIM*/
    loadMenu();

    if ( !expresso_offline )
    {
    	var menuToolsItems = {
    		"i01": {"name": get_lang("Preferences"), "icon": "preferences-mail", callback: preferences_mail },
    		"i02": {"name": get_lang("Search"), "icon": "search-mail", callback: function(key, opt){ search_emails(""); }},
    		"103": {"name": get_lang("Edit filters"), "icon": "filter", callback: filterbox },
    		"i05": {"name": get_lang("Share mailbox"), "icon": "share-mailbox", callback: sharebox } 
    	};
        
        if ( preferences['use_followupflags_and_labels'] == "1" )
        {
            menuToolsItems["i06"] = {"name": get_lang("Labels"), "icon": "tag", callback: configureLabels };
        }
        
        menuToolsItems["i08"] = {"name": get_lang("Empty trash"), "icon": "empty-trash", callback: function(key, opt){ empty_trash_imap(); }};   		
    	
        if( use_local_messages == 1 )
        {
            menuToolsItems["i09"] = {"name": "MailArchive Admin", "icon": "config", callback: function(key, opt){ window.open(mail_archive_url); }}
    	}
    }
    else
    {
    	var menuToolsItems = {
    		"i01": {"name": get_lang("Search"), "icon": "search-mail", callback: function(key, opt){ search_emails(""); }}
    	}
    }
    
    $.contextMenu({
    	selector: "#link_tools",
    	trigger: 'hover',
    	className: 'context-menu-tools',
    	position: function($menu, x, y){
    		$menu.$menu.position({ my: "center top", at: "center bottom", of: this, offset:"0 0"});
    	},
    	determinePosition: function($menu, x, y){
    		$menu.css('display', 'block').position({ my: "center top", at: "center bottom", of: this}).css('display', 'none');
    	},
    	delay:500,
    	autoHide:true,
    	events: {
    		show: function(opt) {
    			var $trigger = $(opt.selector).css({'background-color': '#ffffff', 'border': '1px solid #CCCCCC'});
    			$('.context-menu-tools.context-menu-list.context-menu-root').css({'width': $trigger.css('width') });
    			$('.context-menu-tools.context-menu-list').css({'background': '#ffffff'})
    			.find(".context-menu-item").css({'background-color': '#ffffff'}).hover(
    				function(){
    					$(this).css({'background-color': '#CCCCCC'});
    				}, 
    				function(){
    					$(this).css({'background-color': '#ffffff'});
    				}
    			);
    			return true;
    		},
    		hide: function(opt) {
    			$(opt.selector).css({'background-color': '', 'border': 'none'});
    			return true;
    		}
    	},
    	callback: function(key, options) {
    		//TODO - default actions

    	},
    	items: menuToolsItems
    });
    var reComplexEmail = /<([^<]*)>[\s]*$/;
    $.contextMenu({
    	selector: ".box",
    	autoHide:true,
    	items: {
    		"add" : {name: get_lang("Quick Add"), icon : "quick-add",callback: function(key, opt){ var fname = $(opt.$trigger).find("input").val().split('"')[1];ccQuickAddOne.showList(','+fname+', ,'+$.trim($(opt.$trigger).find("input").val()).match(reComplexEmail)[1]); }},
    		"remove" : {name:get_lang("Remove recipient"), icon:"delete-box",callback: function(key, opt){ $(opt.$trigger).remove(); }},
    		"sep1": "---------",
    		"quick_search" : {name:get_lang("Quick search of messages"), icon: "quick-search-contact",callback: function(key, opt){ search_emails($.trim($(opt.$trigger).find("input").val()).match(reComplexEmail)[1]); }},
    		"full_search" : {name:get_lang("Search messages of ..."), icon: "quick-search-contact",callback: function(key, opt){ search_emails("", $.trim($(opt.$trigger).find("input").val()).match(reComplexEmail)[1]);}}		
    	}
    });

    function updateLabelsColumn(messageInfo) {
    	var msg_number = messageInfo.msg_number;
    	//uid é o numero da mensagem quando os dados são carregados na busca rapida.
    	if(messageInfo.uid != '' && messageInfo.uid != 'undefined' && messageInfo.uid != null){
    		msg_number = messageInfo.uid;
    	}
    	var msg_folder = current_folder;
    	if(messageInfo.boxname != '' && messageInfo.boxname != 'undefined' && messageInfo.boxname != null){
    		msg_folder = messageInfo.boxname;
    	}
    	
    	var menuItems = {};
    	if (messageInfo.labels && !messageInfo.forceIcon ) {
    		if($.isArray(messageInfo.labels)){
    			var labels = messageInfo.labels;
    			messageInfo.labels = {};
    			for(var i in labels)
    				messageInfo.labels[labels[i].id] = {backgroundColor: labels[i]['backgroundColor'],
    					borderColor: labels[i]['borderColor'],
    					fontColor: labels[i]['fontColor'], id: labels[i]['id'], name: labels[i]['name'], 
    					uid: labels[i]['uid'] }
    		}	
    		menuItems = messageInfo.labels;
    	} else {
            var labeleds =  (messageInfo.forceIcon ? messageInfo.labels : DataLayer.get('labeled', {
    			criteria: {deepness: 2},
                filter: [
    				'AND',
    				['=', 'folderName', msg_folder], 
    				['=', 'messageNumber', msg_number]
    			]
    			
    		}) );

    		if (labeleds) {
                if(current_folder == msg_folder || !current_folder){
                    $('#td_message_labels_' + msg_number +', tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number)
                        .html('').css({'background-image':'url(../prototype/modules/mail/img/mail-sprites.png)','background-position': '0 -1706px',"margin-left":"0",'margin-top':'3px','background-repeat':'no-repeat'});
                }else{
                    $('tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number)
                        .html('').css({'background-image':'url(../prototype/modules/mail/img/mail-sprites.png)','background-position': '0 -1706px',"margin-left":"0",'margin-top':'3px','background-repeat':'no-repeat'});
                }

                for (var i=0; i < labeleds.length; i++){
                    menuItems[ (labeleds[i].id ? labeleds[i].id : labeleds[i].label.id) ] = labeleds[i].label ? labeleds[i].label : labeleds[i];
                }
    		} else {
    			$('#td_message_labels_' + msg_number +', tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number)
    			.html('').css("background", "");
    			//$.contextMenu( 'destroy', '#td_message_labels_' + msg_number +', #td_message_labels_search_' + msg_number+':first');
    			$.contextMenu( 'destroy', '#td_message_labels_' + msg_number +', tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number);
    			return false;
    		}
    	}
    	var menuItensLabel = {};
    	for(index in menuItems){
    		menuItensLabel[index] = {type: "label", customName: menuItems[index].name, id: msg_folder+"/"+msg_number+"#"+index};		
    	}


    	$.contextMenu.types.label = function(item, opt, root) {
            $('<span>'+item.customName+'</span><span class="removeLabeled" title="'+get_lang("Remove Label")+'">x</span>')
                .appendTo(this);
    		$(this).find('span.removeLabeled').click(function(){
    			//TODO Mudar quando API abstrair atualizações no cache
    			DataLayer.remove('labeled', false);
    			DataLayer.get('labeled');
    			DataLayer.remove('labeled', item.id);
    			DataLayer.commit(false, false, function(){
    				updateLabelsColumn({msg_number:msg_number, boxname:msg_folder, labels:false});
    			});
    		});
    	};
    	if(current_folder == msg_folder || !current_folder){
    		$.contextMenu( 'destroy', '#td_message_labels_' + msg_number +', tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number);
    		$.contextMenu({
    			selector: '#td_message_labels_' + msg_number +', tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number,
    			trigger: 'hover',
    			delay:100,
    			autoHide:true,
    			callback: function(key, options) {
    				//TODO - default actions
    			},
    			items: menuItensLabel
    		});
    	}else{
    		$.contextMenu( 'destroy', 'tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number);

    		$.contextMenu({
    			selector: 'tr[role="'+msg_number+'_'+msg_folder+'"] #td_message_labels_search_' + msg_number,
    			trigger: 'hover',
    			delay:100,
    			autoHide:true,
    			callback: function(key, options) {
    				//TODO - default actions
    			},
    			items: menuItensLabel 
    		});	
    	}
    }

    function loadExtraLDAPBox(data, element){

        if(!!data[0]){
            var decoded = {};

            $.each(data, function(i, e){
                decoded[e.name] = e.value;
            });

            data = decoded;
        }

        menuItensLabel = {};
        menuItensLabel["Name"] = {name: "<b>"+data.name+"</b>", disabled: true};
        menuItensLabel["Email"] = {name: data.email, disabled: true};
        if(!!data.telephone){
            menuItensLabel["TelefoneLabel"] = {name: "<b>"+get_lang("Telephone")+"</b>", disabled: true};
            menuItensLabel["TelefoneValue"] = {name: data.telephone, disabled: true};
        }
        if(data.vacationActive){
            if(data.vacationActive == "TRUE"){
                menuItensLabel["outOffice"] = {name: "<b>"+get_lang("Out of office")+"</b>", disabled: true};
                menuItensLabel["outOfficeValue"] = {name: data.vacationInfo.substring(0, 20), disabled: true};
            }
        }
        $.contextMenu({
            selector: "#content_id_"+currentTab+" "+element+" .box-info",
            trigger: 'hover',
            delay:100,
            autoHide:true,
            items: menuItensLabel
        }); 
    }

    function loadGroupBox(data, element){
    	menuItensLabel = {};
    	menuItensLabel["ContactGroupLabelAll"] = {name:"<b>"+get_lang("Group contacts")+"</b>", disabled: true};
    	menuItensLabel["sep1"] = "---------";
    	if(data.itens){
    		var aux = 0;
    		var ctcName = "";
    		for(var item in data.itens){
    			if(parseInt(item) <= 4){
    				ctcName = data.itens[item].data[0].value;
    				ctcName = ctcName.length > 2 ? ctcName : data.itens[item].data[2].value.substr(0,data.itens[item].data[2].value.indexOf("@"));
    				menuItensLabel["ContactGroupLabel"+item] = {name: "<b>"+ctcName+"</b>", disabled: true};
    				menuItensLabel["ContactGroupValue"+item] = {name: data.itens[item].data[2].value, disabled: true};
    			}else{
    				aux++;
    				if(aux == 1)
    					menuItensLabel["MoreContactGroupValue"] = {name : get_lang("And more %1 contact", aux), disabled: true };
    				else
    					menuItensLabel["MoreContactGroupValue"] = {name : get_lang("And more %1 contact", aux)+"s", disabled: true };
    			}
    		}
    	}
    	$.contextMenu({
    		selector: "#content_id_"+currentTab+" "+element+" .box-info",
    		trigger: 'hover',
    		delay:100,
    		autoHide:true,
    		items: menuItensLabel
    	});	
    }





