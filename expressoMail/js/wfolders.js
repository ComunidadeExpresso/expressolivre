	function cWFolders()
	{
		this.arrayWin = new Array();
		this.el;
		this.alert = false;
	}

	cWFolders.prototype.load = function( border_id, type )
	{
		var folder 		= {};
		var textButton	= "";
		var titleWindow	= "";
		
		switch( type )
		{
			case 'save' :
				textButton = get_lang('Save');
				break;
					
			case 'send_and_file' :
				textButton = titleWindow = get_lang('Send and file');
				break;
					
			case 'move_to' :
				textButton = titleWindow = get_lang('Move');	
				break;
			
			case 'change_folder' :
				textButton = titleWindow = get_lang('Change folder');
				break;
				
			default :
				textButton = titleWindow = get_lang(type);
		}
		
		var winSaveFile = $("#sendFileMessages");
			winSaveFile.html( DataLayer.render( BASE_PATH + "modules/mail/templates/sendFileMessages.ejs", {}));
			winSaveFile.dialog(
					{
						height		: 250,
						width		: 300,
						resizable	: false,
						title		: titleWindow,
						modal		: true,
						buttons		: [
										 {
										 	text	: get_lang("Close"), 
										 	click	: function()
										 	{
												winSaveFile.dialog("close");
                                                winSaveFile.dialog("destroy");
										 	}
										 },
										 {
										 	text	: textButton, 
										 	click	: function()
										 	{
												if (type == 'save')
												{
													save_as_msg(border_id, folder.id, folder.caption,true);
												}
												else if (type == 'send_and_file')
												{
													send_message( border_id, folder.id, folder.caption);
													wfolders.alert = true;
												}
												else if (type == 'move_to')
												{
													var msg_number =  ( border_id ? border_id.replace('_r','') : 'selected');
													
													if (border_id.match('search'))
														move_search_msgs(border_id, folder.id, folder.caption);	
													else{
														proxy_mensagens.proxy_move_messages('null',msg_number, border_id, folder.id, folder.caption);
														wfolders.alert = true;
													}
												}
												else if (type == 'change_folder')
												{
													change_folder(folder.id, folder.caption);
													wfolders.alert = true;
												}

										 		winSaveFile.dialog("close").dialog("destroy");
										 	}
										 }
									],
                                    close:function(event, ui) 
                                    {
                                        if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
                                    },
                                    open: function(event, ui) 
                                    {
                                        if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
                                    }	 	
					});	
			
		winSaveFile.next().css("background-color", "#E0EEEE");
			
		/*Insere a árvore de diretórios*/
		var foldersTree = jQuery("#foldertree-container-sendFileMessage")
		.removeClass('empty-container')
		//Adicionado parametro cp_tree3 para mensagens locais (MailArchiver)
		.html(DataLayer.render(BASE_PATH + 'modules/mail/templates/foldertree.ejs', {folders: [cp_tree1, cp_tree2, cp_tree3 ]}))
		.find("#foldertree").treeview()
		.click(function(event)
		{
			//request new selected folder messages
			var target = $(event.target);

			if( target.is('.collapsable-hitarea, .expandable-hitarea, .lastCollapsable, .lastExpandable, .treeview') )
			    return;

			if( !target.attr('id') )
			    target = target.parent();

	        if (target.attr('id') == "foldertree") return;
			
			folder.id 		= target.attr('id');
			folder.child 	= target.find('.folder');
			folder.caption	= target.find('span').attr('title');			
			
			$('.filetree span.folder.selected').removeClass('selected');
			if(!target.is('#foldertree > .expandable, #foldertree > .collapsable'))
				$(target).children('.folder').addClass('selected');
			
			selectedFolder = {
			    id: folder.id, 
			    name: folder.child.attr('title'),
			    'class': folder.child.attr('class')
			};
		});
	}
	
	cWFolders.prototype.makeWindow = function(border_id, type)
	{
        if(type == "move_to"){
            if( (currentTab == 0) || (currentTab.toString().indexOf("search") >= 0)){
                
            	//Verifica em qual aba o usuário está
                var selected_msg = (currentTab == 0) ? get_selected_messages() : get_selected_messages_search();

                if ((parseInt(selected_msg) > 0 || selected_msg.length > 0)||(type != "move_to")){
                    this.load( border_id, type, false);
                }else
                    write_msg(get_lang('No selected message.'));
            } else{
                if(typeof (currentTab) == "string" && currentTab.indexOf("local") != -1){
                    alert(get_lang("Unable to handle local messages from a search. This is allowed only for non-local messages."));
                    return true;
                }
                this.load( border_id, type, false);
            }
        }else {
            if(typeof (currentTab) == "string" && currentTab.indexOf("local") != -1){
                alert(get_lang("Unable to handle local messages from a search. This is allowed only for non-local messages."));
                return true;
            }
            this.load( border_id, type, false);
        }
    }
	
/* Build the Object */
	var wfolders;
	wfolders = new cWFolders();
