/**
 * @author diogenes
 */

	function messages_proxy() {
		
	}
/**
 * Retorna os cabeçalhos das mensagens a serem desenhadas na caixa de email.
 */
    messages_proxy.prototype.messages_list = function(folder,msg_range_begin,emails_per_page,sort_box_type,search_box_type,sort_box_reverse,preview_msg_subject,preview_msg_tip,call_back) {
        if(this.is_local_folder(folder)) {
            //MAILARCHIVER-01
            var baseFolder = folder.replace('local_messages_', ''); 
            var tree_name;
            var drawinginfo = {};
            expresso_mail_archive.update_counters = false;
            expresso_mail_archive.currentfolder = baseFolder;
            expresso_mail_archive.getFolderInfo(expresso_mail_archive.currentfolder);
            expresso_mail_archive.drawdata = drawinginfo;

            if(tree_name == 'tree_folders'){ //only the 'tree_folders' must show messages. 'folders_tree' are just for managment operation
                var exp_dfs = new Array(baseFolder,msg_range_begin,emails_per_page,sort_box_type,search_box_type, sort_box_reverse,preview_msg_subject,preview_msg_tip);
                expresso_mail_archive.queryconfig.setExpressoDefaults(exp_dfs);
                expresso_mail_archive.listMessages();
            }
            else{
                //window.alert('sem arvore para atualizar, com sort_box_type =' + sort_box_type + ' e search_box_type= ' + search_box_type + ' reverse -- ' + sort_box_reverse);
                var exp_dfs = new Array(baseFolder,msg_range_begin,emails_per_page,sort_box_type,search_box_type,sort_box_reverse,preview_msg_subject,preview_msg_tip);
                expresso_mail_archive.queryconfig.setExpressoDefaults(exp_dfs);
                //Para recontagem de mensagens para aba e paginação.
                expresso_mail_archive.tot_msgs_tab = 0;
                expresso_mail_archive.tot_unseen_msgs_tab = 0;
                expresso_mail_archive.listAllMessagesByFolder(folder.replace("local_messages_",""), search_box_type);
                selectAllFolderMsgs(false);
                populateSelectedMsgs(expresso_mail_archive.allmessagesbyfolder);
                expresso_mail_archive.listMessages();
            }

        }else {
            $.ajax({
                url: "controller.php?" + $.param( {action: "$this.imap_functions.get_range_msgs3", 
                                    folder: folder, 
                                    msg_range_begin: msg_range_begin, 
                                    msg_range_end: emails_per_page, 
                                    sort_box_type: sort_box_type, 
                                    search_box_type: search_box_type, 
                                    sort_box_reverse: sort_box_reverse } ),

                  success: function( data ){
                    data = connector.unserialize(data);
                    
                    if( data )
                        call_back( data );
                  },
                  beforeSend: function( jqXHR, settings ){
                    connector.showProgressBar();
                  },
                  complete: function( jqXHR, settings ){
                    connector.hideProgressBar();
                  }
              
            });
        }
    }
	
	

	messages_proxy.prototype.get_msg = function(msg_number,msg_folder,set_flag,call_back, openSameBorder) {
		if(this.is_local_folder(msg_folder)) {
            //MAILARCHIVER-02
            expresso_mail_archive.getMessage(msg_number);
		}else{
		    $.ajax({
			      url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
								  msg_number: msg_number, 
								  msg_folder: msg_folder,
								  sort_box_type : sort_box_type,
								  search_box_type : search_box_type,
								  sort_box_reverse: sort_box_reverse
								  } ),
                  async: false,
			      success: function( data ){
				  data = connector.unserialize( data );
				  
                  if(typeof(openSameBorder) == "undefined"){
                    openSameBorder = true;
                  }
                  data.openSameBorder = openSameBorder;
				  if( data )
				      call_back( data );
			      },
				  beforeSend: function( jqXHR, settings ){
				  	connector.showProgressBar();
				  },
				  complete: function( jqXHR, settings ){
				  	connector.hideProgressBar();
				  }

		    });
		}

	}
    
	function closeBorders(){
        folder64 = Base64.encode(get_current_folder());
        folder64 = folder64.replace("=","");

        var msgs = get_selected_messages();
        msgs = msgs.split(",");

        for(var i = 0; i < msgs.length; i++){
            var borderId = "border_id_"+msgs[i]+"_r_"+folder64;
            var id = msgs[i]+"_r_"+folder64;
            if($("#"+borderId).length){
                delete_border(id);
            }
        }
    }

	messages_proxy.prototype.delete_msgs = function(folder, msgs_number, border_ID, id_delete_msg) {
        closeBorders();

		if (folder == 'null')
			folder = get_current_folder();

        //Validação para recuperar o id caso não seja aba de listagem
		if (msgs_number == 'selected' && currentTab != 0) //Recupera apenas o id da mensagem aberta
			msgs_number = currentTab.substr(0,currentTab.indexOf('_r'));
        else if (msgs_number == 'selected' && currentTab == 0)
            msgs_number = get_selected_messages();

		if(currentTab != 0 && currentTab.indexOf("search_")  >= 0){
			var content_search = document.getElementById('content_id_'+currentTab);
			action_msg_selected_from_search(content_search.id, 'delete');
			refresh();
			return;
		}
			
		if (!this.is_local_folder(folder)){
			delete_msgs(folder, msgs_number, border_ID, undefined, undefined, id_delete_msg);
		}else {    
            //MAILARCHIVER-03
            var msg_to_delete = Element(msgs_number);
            //user has preference to show previous message on deleting one
            if (isNaN(currentTab) && parseInt(preferences.delete_and_show_previous_message) && msg_to_delete) {
                    if (msg_to_delete.previousSibling){ 
                            var previous_msg = id_delete_msg ? id_delete_msg : msg_to_delete.previousSibling.id;

                            //user has preference to maintain default folder structure at local folders, so we have trash folder
                            if(preferences.auto_create_local == 1){
                                //user has preference to "save" delete messasge on trash folder, so move it to there
                                if (((preferences.save_deleted_msg == true)) && (folder != expresso_mail_archive.specialfolders.trash)){
                                    expresso_mail_archive.folder_destination = 'trash';
                                    expresso_mail_archive.moveMessages(expresso_mail_archive.folder_destination, msgs_number);
                                    delete_border(currentTab,'false'); 
                                    expresso_mail_archive.getMessage(previous_msg);
                                }
                                //user does not want to save messages deleted on trash. purge them imediatly
                                else{
                                    expresso_mail_archive.deleteMessages(msgs_number);
                                    delete_border(currentTab,'false'); 
                                    expresso_mail_archive.getMessage(previous_msg);
                                }                                                                                
                            }
                            //maybe, we do not have trash.
                            else{
                                //user has preference to "save" delete messasge on trash folder, so move it to there
                                if (((preferences.save_deleted_msg == true)) && (folder != expresso_mail_archive.specialfolders.trash)){
                                    expresso_mail_archive.createFolder("","Trash");
                                    expresso_mail_archive.folder_destination = 'trash';
                                    expresso_mail_archive.moveMessages(expresso_mail_archive.folder_destination, msgs_number);
                                    delete_border(currentTab,'false');
                                    expresso_mail_archive.getMessage(previous_msg);
                                }
                                //user does not want to save messages deleted on trash. purge them imediatly
                                else{
                                    expresso_mail_archive.deleteMessages(msgs_number);
                                    delete_border(currentTab,'false'); 
                                    expresso_mail_archive.getMessage(previous_msg)
                                }                                          
                            }
                    } 
                    //there is no previous message existing to show. Just delete de view context
                    else{ 
                           //user has preference to maintain default folder structure at local folders, so we have trash folder
                           if(preferences.auto_create_local == 1){
                                if (((preferences.save_deleted_msg == true)) && (folder != expresso_mail_archive.specialfolders.trash)){
                                    expresso_mail_archive.folder_destination = 'trash';
                                    expresso_mail_archive.moveMessages(expresso_mail_archive.folder_destination, msgs_number);
                                    delete_border(currentTab,'false'); 
                                }
                                //user does not want to save messages deleted on trash. purge them imediatly
                                else{
                                    expresso_mail_archive.deleteMessages(msgs_number);
                                    delete_border(currentTab,'false'); 
                                }                                       
                           }
                            //maybe, we do not have trash. Purge message so.
                            else{
                                //user has preference to "save" delete messasge on trash folder, so move it to there
                                if (((preferences.save_deleted_msg == true)) && (folder != expresso_mail_archive.specialfolders.trash)){
                                    expresso_mail_archive.createFolder("","Trash");
                                    expresso_mail_archive.folder_destination = 'trash';
                                    expresso_mail_archive.moveMessages(expresso_mail_archive.folder_destination, msgs_number);
                                    expresso_mail_archive.getMessage(previous_msg);
                                }
                                //user does not want to save messages deleted on trash. purge them imediatly
                                else{
                                    expresso_mail_archive.deleteMessages(msgs_number);
                                }                                    
                            }
                    } 
            } 
            //user has no preferece to show previous message on deleting
            else{ 
                if(preferences.auto_create_local == 1){
                    //user has preference to "save" delete messasge on trash folder, so move it to there
                    if (((preferences.save_deleted_msg == true)) && (folder.replace('messages_','') != expresso_mail_archive.specialfolders.trash)){
                        expresso_mail_archive.folder_destination = 'trash';
                        expresso_mail_archive.moveMessages(expresso_mail_archive.folder_destination, msgs_number);
                        delete_border(currentTab,'false');
                    }
                    //user does not want to save messages deleted on trash (or is cleaning it). purge them imediatly
                    else{
                        expresso_mail_archive.deleteMessages(msgs_number);
                        delete_border(currentTab,'false');
                    }                            
                }
                //maybe, we do not have trash. Purge message so.
                else{
                    //user has preference to "save" delete messasge on trash folder, so move it to there
                    if (((preferences.save_deleted_msg == true)) && (folder.replace('messages_','') != expresso_mail_archive.specialfolders.trash)){
                        expresso_mail_archive.createFolder("","Trash");
                        expresso_mail_archive.folder_destination = 'trash';
                        expresso_mail_archive.moveMessages(expresso_mail_archive.folder_destination, msgs_number);
                    }
                    //user does not want to save messages deleted on trash. purge them imediatly
                    else{
                        expresso_mail_archive.deleteMessages(msgs_number);
                    }     
                }
            }
		}

        //Código adicionado para o correto funcionamento da seleção independente de paginação.
        $.each(msgs_number.split(","), function(index, value){
            delete selectedPagingMsgs[value];
        });
//      Inserida verificação ao decrementar variável para que a mesma não seja decrementada mais de uma vez em outros lugares.
        var isTrash = folder.split("/");
        if(isTrash[isTrash.length - 1] == 'Trash' || !!!parseInt(preferences.save_deleted_msg))
            totalFolderMsgs = totalFolderMsgs - msgs_number.split(",").length;
        selectAllFolderMsgs(false);
        updateSelectedMsgs();
	}
	
	messages_proxy.prototype.link_anexo = function (info_msg,numero_ordem_anexo) {

		if(info_msg.local_message==true) {
            //MAILARCHIVER-04
            return "javascript:download_local_attachment('"+ mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/temp/download/' +info_msg.attachments[numero_ordem_anexo].pid + "')";
		}
		else {
			return "javascript:download_attachments('"+Base64.encode(info_msg.msg_folder)+"','"+info_msg.msg_number+"',"+numero_ordem_anexo+",'"+info_msg.attachments[numero_ordem_anexo].pid+"','"+info_msg.attachments[numero_ordem_anexo].encoding+"','"+info_msg.attachments[numero_ordem_anexo].name+"')";
		}
	}

	messages_proxy.prototype.proxy_source_msg = function (id_msg,folder) {
		if(!this.is_local_folder(folder)) {
			source_msg(id_msg,folder);
		}
		else {
			var num_msg = id_msg.substr(0,(id_msg.length - 2));
			expresso_local_messages.get_source_msg(num_msg);
		}
	}
	
	messages_proxy.prototype.proxy_set_messages_flag = function (flag,msg_number){
        //MAILARCHIVER

        if(this.is_local_folder(get_current_folder())) {
        
            var msglist = get_selected_messages();    
            var arrlist = msglist.split(",");
            var operation;
            var strtag;
            
            for(var i in arrlist){
                var exit = true;

                switch(flag.toLowerCase()){
                    case 'unseen':
                        strtag = "unseen";
                        operation = 0;
                        set_msg_as_unread(arrlist[i]);
                        break;
                    case 'seen':
                        strtag = "unseen";
                        operation = 1;
                        exit = set_msg_as_read(arrlist[i], false, true);
                        break;
                    case 'flagged':
                        strtag = "flagged, importance_high";
                        operation = 0;
                        set_msg_as_flagged(arrlist[i]);
                        break;
                    case 'unflagged':
                        strtag = "flagged, importance_high";
                        operation = 1;
                        set_msg_as_unflagged(arrlist[i]);
                        break;
                }

                if(exit){
                    expresso_mail_archive.currenttag = flag;
                    var tl = expresso_mail_archive.pattern.tagConfig(strtag, arrlist[i], operation);
                    expresso_mail_archive.taglist = tl;                             
                    expresso_mail_archive.progressbar = window.setTimeout("expresso_mail_archive.tagMessage()",1);
                }
            }
            
        }
        else {
            set_messages_flag(flag,msg_number);
            // Verifica se a pasta que esta selecionada contem a opção "Não lidas" ativada
            // caso exista, ele chama novamente a opção "Não lidas" para atualizar a pasta.
            if('UNSEEN' == search_box_type)
                return sort_box('UNSEEN','SORTARRIVAL');
        }
	}
	
	messages_proxy.prototype.proxy_set_message_flag = function (msg_number,flag,func_after_flag_change,msgid){
        var msg_number_folder = Element("new_input_folder_"+msg_number+"_r"); //Mensagens respondidas/encaminhadas
		if(!msg_number_folder)
			var msg_number_folder = Element("input_folder_"+msg_number+"_r"); //Mensagens abertas
		var folder = msg_number_folder ?  msg_number_folder.value : get_current_folder();

        //MAILARCHIVER
        if(this.is_local_folder(folder)) {
            if(!msgid)
                msgid = msg_number;
            var taglist = flag;
            var operation;
    
            switch(flag.toLowerCase()){
                case 'unseen':
                    strtag = "unseen";
                    operation = 0;
                    set_msg_as_unread(msgid);

                    break;
                case 'seen':
                    strtag = "unseen";
                    operation = 1;
                    set_msg_as_read(msgid);
                    break;
                case 'flagged':
                    strtag = 'flagged, importance_high';
                    operation = 0;
                    set_msg_as_flagged(msgid);
                    break;
                case 'unflagged':
                    strtag = "flagged, importance_high";
                    operation = 1;
                    set_msg_as_unflagged(msgid);
                    break;
                case 'forwarded':
                    strtag = "forwarded";
                    operation = 0;
                    set_msg_as_flagged(msgid);
                case 'answered':
                    strtag = "answered";
                    operation = 0;
                    set_msg_as_flagged(msgid);                                  
                    
            }    
            expresso_mail_archive.drawdata = null;
            var tl = expresso_mail_archive.pattern.tagConfig(strtag, msgid, operation);
            expresso_mail_archive.currenttag = flag;
            expresso_mail_archive.taglist = tl;
            expresso_mail_archive.progressbar = window.setTimeout("expresso_mail_archive.tagMessage()",1);
        }
        else {
            set_message_flag(msg_number,flag, func_after_flag_change);
        }
	}
	
	messages_proxy.prototype.is_local_folder = function(folder) {
		if(typeof(folder) == "undefined" || folder.indexOf("local_")==-1)
			return false;
		return true;
	}
	
	/*
	messages_proxy.prototype.proxy_rename_folder = function(){
		var specialFolders = special_folders[ttree.getFolder().split(cyrus_delimiter)[ttree.getFolder().split(cyrus_delimiter).length-1]]; 
		if (ttree.getFolder() == 'INBOX' || specialFolders ) {
			alert(get_lang("It's not possible rename the folder: ") + lang_folder((specialFolders ? specialFolders : ttree.getFolder()))+ '.');
			return false;
		}
		if(ttree.getFolder() == 'root') {
			alert(get_lang("It's not possible rename this folder!"));
			return false;
		}
		if(!specialFolders && ttree.getFolder() == get_current_folder()){
				alert(get_lang("It's not possible rename this folder, because it is being used in the moment!"));
				return false;
		}
                
		if (this.is_local_folder(ttree.getFolder())) {
			folder = prompt(get_lang("Enter a name for the box"), "");
			if(folder.match(/[\/\\\!\@\#\$\%\&\*\+\(\)]/gi)){
			alert(get_lang("It's not possible rename this folder. try other folder name"));
			return false;
			}
			if(trim(folder) == "" || trim(folder) == null){
				alert(get_lang("you have to enter the name of the new folder"));
				return false;
			}
			var temp = expresso_local_messages.rename_folder(folder, ttree.FOLDER.substr(6));
			if (!temp) 
				alert(get_lang("cannot rename folder. try other folder name"));
			ttreeBox.update_folder();
		}
		else {
			ttreeBox.validate("rename");
		}
		
	}
	*/
	/*
	messages_proxy.prototype.proxy_create_folder = function() {
		if (folders.length == preferences.imap_max_folders){ 
 		    alert(get_lang("Limit reached folders")); 
 		    return false; 
 		} 
		
		if (this.is_local_folder(ttree.FOLDER)) {
			folder = prompt(get_lang('Enter the name of the new folder:'), "");

                        if(folder == null)
                            return;


			if(trim(folder) == ""){
				alert(get_lang("you have to enter the name of the new folder"));
				return false;
			}
			if(folder.match(/[\/\\\!\@\#\$\%\&\*\+\(\)]/gi)){
			    alert(get_lang("cannot create folder. try other folder name"));
			    return false;
			}
			if(ttree.FOLDER=="local_root")
				var temp = expresso_local_messages.create_folder(folder);
			else
				var temp = expresso_local_messages.create_folder(ttree.FOLDER.substr(6)+"/"+folder);
			if (!temp) 
				alert(get_lang("cannot create folder. try other folder name"));
			ttreeBox.update_folder(true);
		}
		else			
			if(ttree.FOLDER == "INBOX")
				alert(get_lang("It's not possible create inside: ") + lang_folder(ttree.FOLDER)+".");
			else if (!this.is_local_folder(ttree.FOLDER))
				ttreeBox.validate("newpast");
			else 
				alert(get_lang("It's not possible create inside: ") + lang_folder(ttree.FOLDER.substr(6))+".");
	}
	*/
	/*
	messages_proxy.prototype.proxy_remove_folder = function() {
		if (this.is_local_folder(ttree.FOLDER)) {
			if(ttree.FOLDER == 'local_root') {
				alert(get_lang("Select a folder!"));
				return false;
			}
			if (ttree.FOLDER == 'local_Inbox' || (preferences.auto_create_local == '1' && (ttree.FOLDER == 'local_Sent' || ttree.FOLDER == 'local_Drafts' || ttree.FOLDER == 'local_Trash'))) {
				alert(get_lang("It's not possible delete the folder: ")  + lang_folder(ttree.FOLDER.substr(6)) + '.');
				return false;
			}
                        if(ttree.FOLDER.match("^local_.*$") && ttree.FOLDER == get_current_folder()){
                            alert(get_lang("It's not possible rename this folder, because it is being used in the moment!"));
                            return false;
                        }

			if(ttree.FOLDER.indexOf("/")!="-1") {
				final_pos = ttree.FOLDER.lastIndexOf("/");
				new_caption = ttree.FOLDER.substr(final_pos+1);
			}
			else {
				new_caption = ttree.FOLDER.substr(6);
			}
			var string_confirm = get_lang("Do you wish to exclude the folder ") + new_caption + "?";

			if (confirm(string_confirm)) {
				var flag = expresso_local_messages.remove_folder(ttree.FOLDER.substr(6));
				if (flag) {
					write_msg(get_lang("The folder %1 was successfully removed", new_caption));
					draw_tree_local_folders();
					ttreeBox.update_folder(true);
				}
				else 
					alert(get_lang("Delete your sub-folders first"));
				
			}
		}
		else
			ttreeBox.del();
	}*/

	messages_proxy.prototype.proxy_move_messages = function (folder, msgs_number, border_ID, new_folder, new_folder_name) {
		
        closeBorders();

        if (! folder || folder == 'null')
			folder = Element("input_folder_"+msgs_number+"_r") ? Element("input_folder_"+msgs_number+"_r").value : (openTab.imapBox[currentTab] ? openTab.imapBox[currentTab]:get_current_folder());

        //MAILARCHIVER-08
        if (this.is_local_folder(folder)){
            expresso_mail_archive.update_counters = true;
            //Folder de onde sai a mensagem Ã© local (armazenamento local)
            if (folder == new_folder){
                write_msg(get_lang('The origin folder and the destination folder are the same.'));
				return;
            }                    

            //Validação para recuperar o id caso não seja aba de listagem
            if (msgs_number == 'selected' && currentTab != 0) //Recupera apenas o id da mensagem aberta
                msgs_number = currentTab.substr(0,currentTab.indexOf('_r'));
            else
                msgs_number = get_selected_messages();

            if (new_folder == 'local_root'){
                alert(get_lang("Select a folder!"));
            }
                        
            if (parseInt(msgs_number) > 0 || msgs_number.length > 0) {
                if (this.is_local_folder(new_folder)){
                    //esta tirando de um folder local para outro folder local
                    expresso_mail_archive.moveMessages(new_folder.replace("local_messages_",""), msgs_number);
                    if(currentTab != 0)
                        delete_border(currentTab,'false'); 
                }
                else{
                    //esta tirando de um folder local para um folder IMAP (desarquivamento)
                    if(!new_folder){
                        new_folder = 'INBOX';
                    }
                    if(currentTab.toString().indexOf("_r") != -1){
                        msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf("_r"));
                    }                      
                    expresso_mail_archive.unarchieve(folder, new_folder, msgs_number);
                }
            }
            else{
                write_msg(get_lang('No selected message.'));
            }
        }else{
            if (this.is_local_folder(new_folder)){
                //esta tirando de um folder nÃ£o local para um folder local (arquivamento)
                if(msgs_number=='selected'){
                    archive_msgs(folder, new_folder);
                }
                else{
                    archive_msgs(folder, new_folder, msgs_number);
                }
            }
            else{
                //esta tirando de um folder nÃ£o local para outro folder nÃ£o local (move)
                move_msgs(folder, msgs_number, border_ID, new_folder, new_folder_name);
            }
        }	

        //Adicionado código para o correto funcionamento da seleção independente de paginação.
        $.each(msgs_number.split(","), function(index, value){
            delete selectedPagingMsgs[value];
        });
        totalFolderMsgs = totalFolderMsgs - msgs_number.split(",").length;
        selectAllFolderMsgs(false);
        updateSelectedMsgs();
	}
	
	messages_proxy.prototype.proxy_move_search_messages = function(border_id, new_folder, new_folder_name) {
		
		
		/*
		
		
		if ((this.is_local_folder(folder)) && (this.is_local_folder(new_folder))) { //Move entre pastas não locais...
			if (folder == new_folder){
				write_msg(get_lang('The origin folder and the destination folder are the same.'));
				return;
			}
			if(msgs_number=='selected')
				msgs_number = get_selected_messages();
			if (new_folder == 'local_root')
				alert(get_lang("Select a folder!"));
			if (parseInt(msgs_number) > 0 || msgs_number.length > 0) {
				expresso_local_messages.move_messages(new_folder.substr(6), msgs_number);
				this.aux_interface_remove_mails(msgs_number, new_folder_name, border_ID);
			}
			else 
				write_msg(get_lang('No selected message.'));
		}
		else 
			if ((!this.is_local_folder(folder)) && (!this.is_local_folder(new_folder))) { //Move entre pastas locais...
				move_msgs(folder, msgs_number, border_ID, new_folder, new_folder_name);
			}
			else if ((!this.is_local_folder(folder)) && (this.is_local_folder(new_folder))) {
				archive_msgs(folder,new_folder);
			}
			else {
				write_msg(get_lang("you can't move mails from local to server folders"));
			}*/
	}
	
	messages_proxy.prototype.aux_interface_remove_mails = function(msgs_number,new_folder_name,border_ID,previous_msg) {
		if(!msgs_number)
			msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf("_r")); 
			
		if(msgs_number === ""){
			write_msg(get_lang('No selected message.')); 
			return;
		}
		
		Element('chk_box_select_all_messages').checked = false;
		mail_msg = Element("tbody_box");
		msgs_number = msgs_number.split(",");
		var msg_to_delete;
		this.previous = 0;
		for (var i=0; i<msgs_number.length; i++){
			msg_to_delete = Element(msgs_number[i]);
			if (msg_to_delete){
				if ( (msg_to_delete.style.backgroundColor != '') && (preferences.use_shortcuts == '1') )
					select_msg('null', 'down');
					
				  if (parseInt(preferences.delete_and_show_previous_message) && msg_to_delete && currentTab.toString().indexOf("_r") > 0)
				for(var ii=0; ii < mail_msg.rows.length; ii++){
					if(mail_msg.rows[ii] === msg_to_delete){
						if(ii == 0){
							break;
						}else{
							this.previous = mail_msg.rows[(ii - 1)].attributes[0];
							this.previous = parseInt(this.previous.value); 
							break;
						}
					}
				}
				mail_msg.removeChild(msg_to_delete);
			}
		}
		new_folder_name = this.get_folder_name(new_folder_name);
		if (msgs_number.length == 1)
			write_msg(get_lang("The message was moved to folder ") + new_folder_name);
		else
			write_msg(get_lang("The messages were moved to folder ") + new_folder_name);

		if (parseInt(preferences.delete_and_show_previous_message) && msg_to_delete && this.previous){
			proxy_mensagens.get_msg(this.previous, folder, true, show_msg);
		}else if(currentTab != 0){
		if (border_ID != '' && border_ID != 'null'){
				delete_border(border_ID,'false');
			}else{
				delete_border(currentTab,'false');
			}
		}
		if(folder == get_current_folder())
			Element('tot_m').innerHTML = parseInt(Element('tot_m').innerHTML) - msgs_number.length;			
	}

	messages_proxy.prototype.get_folder_name = function(new_folder_name){
		switch (new_folder_name) {
			case 'local_Inbox':
				return 'Local_Caixa de Entrada';
			case 'local_Sent':
				return 'Local_Enviados';
			case 'local_Trash':
				return 'Local_Lixeira';
			case 'local_Drafts':
				return 'Local_Rascunhos';
			default:
				return new_folder_name;
		}
	}
	
   	messages_proxy.prototype.msg_img = function(msgs_number,folder_name,call_back) {
     if(this.is_local_folder(folder_name)){
         var msg = expresso_local_messages.get_local_mail(msgs_number);
		 eval('call_back(msg)');
     }
     else
	 $.ajax({
		  url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
						      msg_number: msgs_number, 
						      msg_folder: folder_name,
						      decoded: true } ),
		  success: function( data ){
		      data = connector.unserialize( data );
		      
		      if( data )
			  call_back( data );
		  },
		  beforeSend: function( jqXHR, settings ){
		  	connector.showProgressBar();
		  },
		  complete: function( jqXHR, settings ){
		  	connector.hideProgressBar();
		  }

	});
    }

    messages_proxy.prototype.export_all_messages = function(folder){
        if(typeof (currentTab) == "string" && currentTab.indexOf("local") != -1){  
            alert("_[[Unable to handle local messages from a search. This is allowed only for non-local messages.]]");
            return true;
        }
        if (!folder) {
            folder = get_current_folder();
        }

        if(!this.is_local_folder(folder)){
            export_all_selected_msgs();
        }else{
            expresso_mail_archive.download_msg_source();
        }
	}
	
	messages_proxy.prototype.archive_message = function(folder, msgs_number) {
		if(msgs_number == 'get_selected_messages')
			msgs_number = get_selected_messages();
			
		expresso_mail_archive.Archive(folder, "inbox", msgs_number);
	}

    messages_proxy.prototype.unarchive_message = function(folder, msgs_number) {

        if(msgs_number == 'get_selected_messages')
            msgs_number = get_selected_messages();

        expresso_mail_archive.unarchieve(folder, "inbox", msgs_number);
        $('.select-link').trigger('click',function(){selectAllFolderMsgs();$('.select-link').unbind('click');});
    }
	
	var proxy_mensagens;
	proxy_mensagens = new messages_proxy();
