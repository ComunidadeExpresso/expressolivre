/**
 * @author diogenes
 */

	/**
	 *  @param folders:
	 * 		1 - rowid of folder in sqlite
	 * 		2 - folder name in sqlite
	 * 		3 - ids to not download(for auto archiving) or ids to download(for archiving)
	 * 		4 - true for auto archiving and false for normal archiving.
	 */
	function mail_sync() {
		if (typeof(google) == 'undefined')
			return false;
//		this.dbGears = null;
//		this.localServer = null;
//		this.store = null;
		this.folders = new Array();
		this.messages = null;
		this.attachs = null;
		this.url_attachs = null;
		this.working = false;
		this.is_auto = null;
		this.errors = new Array();
//		this.main_title = null;
		
		this.main_title = null;
		this.dbGears = google.gears.factory.create('beta.database');
		this.localServer = google.gears.factory.create('beta.localserver');
		this.store = this.localServer.createStore('test-store');
		this.update_folder = false;
	}
	
	mail_sync.prototype.open_conn = function(){
		var db_in_other_use = true;
		var start_trying = new Date().getTime();
		while (db_in_other_use) {
			try {
				this.dbGears.open('database-test');
				db_in_other_use = false;
			} 
			catch (ex) {
				if(new Date().getTime()-start_trying>10000) { //too much time trying, throw an exception
					throw ex;
				}
			}
		}
	}
	
	mail_sync.prototype.start_sync = function() {
		if(this.working) {
			//Já está sincronizando...
			return;
		}
		
		this.open_conn();

		var rs = this.dbGears.execute("select id_folder,folder_name from folders_sync where uid_usuario=?",[account_id]);
		if(!rs.isValidRow()) {
			this.dbGears.close();
			return;
		}
			

		this.working=true;
		this.messages = null;
		this.attachs = null;
		this.url_attachs = null;

		document.getElementById('main_title').innerHTML = get_lang("Creating folders structure");
		while(rs.isValidRow()) {
			var temp = new Array();
			temp[0] = rs.field(0);
			temp[1] = rs.field(1);
			temp[2] = null;
			temp[3] = false;
			var rs2 = this.dbGears.execute("select mail.original_id from mail inner join folder on mail.id_folder=folder.rowid where folder.folder = ? and mail.uid_usuario=?",[temp[1]==get_lang('Inbox')?'Inbox':temp[1],account_id]);
			while(rs2.isValidRow()) {
				if(temp[2]==null)
					temp[2]=rs2.field(0)+",";
				else
					temp[2]+=rs2.field(0)+",";
				rs2.next();
			}
			this.folders.push(temp);
			
			
			//Criando estrutura de pastas...
			var folders_to_check = temp[0].split("/");//Todas as pastas tem ao menos INBOX/
			if (folders_to_check.length == 1) 
				var actual_check = "Inbox";
			else {
				folders_to_check = folders_to_check.slice(1);
				var actual_check = folders_to_check[0];
			}
			for (var i in folders_to_check) {
				var rs2 = this.dbGears.execute("select rowid from folder where folder=? and uid_usuario=?", [actual_check, account_id]);
				
				if (!rs2.isValidRow()) {
					if((!preferences.hide_folders) || (preferences.hide_folders=="0"))
						this.update_folder = true; //Precisa atualizar as pastas na arvore de pastas
					this.dbGears.execute("insert into folder (folder,uid_usuario) values (?,?)", [actual_check, account_id]);
				}
				if(parseInt(i)+1<folders_to_check.length)
					actual_check += "/"+folders_to_check[parseInt(i)+1];
			}
			
			rs.next();
		}
		this.dbGears.close();
		this.syncronize_folders();
	}
	
	mail_sync.prototype.archive_msgs = function(folder,folder_dest,ids) {

		//this.main_title = document.getElementById('main_title').innerHTML;
		var temp = new Array();
		temp[0] = folder;
		temp[1] = folder_dest.substr(6);
		temp[2] = ids;
		temp[3] = true;
		this.folders.push(temp);
		Element('chk_box_select_all_messages').checked = false;
		
		array_ids = ids.split(",");
		for (var i in array_ids) {
			if (Element("check_box_message_" + array_ids[i])) 
				Element("check_box_message_" + array_ids[i]).checked = false;
			remove_className(Element(array_ids[i]), 'selected_msg');
		}
		if(!this.working) {
			this.working=true;
			this.syncronize_folders();
		}
		else {
			write_msg(get_lang("An archiving is in process, but dont worry, expresso will process this messages after the actual archiving"));
		}
	}
	
	mail_sync.prototype.syncronize_folders = function() {
		if (this.folders.length == 0) {
			document.getElementById('main_title').innerHTML = get_lang("Deleting downloadeds msgs...");
			expresso_mail_sync.remove_archived_mails();
			if((!preferences.hide_folders) || (preferences.hide_folders=="0")) {
				if (expresso_mail_sync.update_folder) {
					//ttreeBox.update_folder();
					expresso_mail_sync.update_folder = false;
				}
				else 
					draw_tree_local_folders();
			}
			expresso_mail_sync.working=false;
			return;
		}
		var folder_to_sync = this.folders.pop();
		folder_to_sync[1] = folder_to_sync[0].toUpperCase()=="INBOX" && !folder_to_sync[3]?"Inbox":folder_to_sync[1];
		
		if(folder_to_sync[3]) { //Em caso de arquivamento normal, pode ser que a pasta inbox ainda não tenha sido criada.
			expresso_mail_sync.open_conn()
			if(folder_to_sync[1]=="Inbox" && !expresso_mail_sync.has_inbox_folder()) {
				if((!preferences.hide_folders) || (preferences.hide_folders=="0"))
					expresso_mail_sync.update_folder = true; //Precisa atualizar as pastas na arvore de pastas
				expresso_mail_sync.dbGears.execute("insert into folder (folder,uid_usuario) values (?,?)",["Inbox",account_id]);
			}
			expresso_mail_sync.dbGears.close();
			
			
		}
		
		var start_sync_mails = function(data) {
			if (!data) { //Erro ao pegar lista de e-mails a serem baixados
				write_msg(get_lang("Problems while downloading mails, please try later"));
				expresso_mail_sync.working=false;
				window.setTimeout("eval('document.getElementById(\"main_title\").innerHTML =\"Expresso Mail\"')",3000);
				return;
			}
			expresso_mail_sync.messages=data;
			if(expresso_mail_sync.is_auto)
				document.getElementById('main_title').innerHTML = get_lang("Auto archiving")+" "+lang_folder(folder_to_sync[1])+": 0 / "+data.length;
			else
				document.getElementById('main_title').innerHTML = get_lang("Archiving messages on folder")+" "+lang_folder(folder_to_sync[1])+": 0 / "+data.length;

			expresso_mail_sync.syncronize_mails(folder_to_sync[1]);
		}
		

		if (folder_to_sync[3]) {
			document.getElementById('main_title').innerHTML = get_lang("Starting to archive messages");
			cExecute("$this.imap_functions.get_info_msgs&folder=" + folder_to_sync[0] + "&msgs_number=" + folder_to_sync[2], start_sync_mails);
			this.is_auto = false;
		}
		else {
			document.getElementById('main_title').innerHTML = get_lang("Starting to sync folder")+" "+lang_folder(folder_to_sync[1]);
			var params = "folder="+folder_to_sync[0]+"&mails="+folder_to_sync[2];
			cExecute("$this.imap_functions.msgs_to_archive", start_sync_mails,params);
			this.is_auto = true;
		}
			
	}
	
	mail_sync.prototype.syncronize_mails = function(folder_dest) {
		if (expresso_mail_sync.messages == null || expresso_mail_sync.messages.length == 0) {
			expresso_mail_sync.syncronize_folders();
			return;
		}
		
		msg_to_sync = expresso_mail_sync.messages.pop();
		
		//refresh loading
		var value_to_change = document.getElementById('main_title').innerHTML.match(/\d+ \//);
		value_to_change += "";
		var new_value = value_to_change.replace(" /","");
		new_value = parseInt(new_value) + 1;
		document.getElementById('main_title').innerHTML = document.getElementById('main_title').innerHTML.replace(value_to_change,new_value+" /");
		msg_to_sync = connector.unserialize(msg_to_sync);
		
		var source_msg = new Array();
		source_msg['url'] = msg_to_sync.url_export_file;

		if (typeof(msg_to_sync['array_attach'])=='object'&&(msg_to_sync['array_attach'] instanceof Array))
			expresso_mail_sync.attachs = msg_to_sync['array_attach'].slice();
		else
			expresso_mail_sync.attachs = new Array();
		expresso_mail_sync.attachs.push(source_msg);
		expresso_mail_sync.download_attachs(msg_to_sync,folder_dest);
	}
	
	mail_sync.prototype.download_attachs = function(msg,folder_dest) {
		if (expresso_mail_sync.attachs == null || expresso_mail_sync.attachs.length == 0) {
			expresso_mail_sync.insert_mail(msg, folder_dest);
			
			return;
		}
		
		attach_to_capt = expresso_mail_sync.attachs.pop();
		
		var call_back = function(url,success,captureId) {
			if (!success) {
				/*
				 * 0 - original id
				 * 1 - message subject
				 * 2 - original folder
				 */
				var mail_error = new Array();
				mail_error[0] = msg.msg_number;
				mail_error[1] = msg.subject;
				mail_error[2] = msg.msg_folder;
				expresso_mail_sync.errors.push(mail_error);
				alert('erro ao baixar: '+url);
				
				/*if (typeof(msg['array_attach']) == 'object' && (msg['array_attach'] instanceof Array)) {
					for (var i in msg['array_attach']) { //remove os anexos que já foram baixados para essa mensagem...
						expresso_mail_sync.store.remove(msg['array_attach'][i]['url']);
					}
				}*/
				expresso_mail_sync.syncronize_mails(folder_dest); //Pula para o próximo e-mail.
			}
			else {
				expresso_mail_sync.download_attachs(msg, folder_dest);//continua baixando o próximo anexo
			}
		}
		expresso_mail_sync.store.capture(attach_to_capt['url'],call_back);
	}
	
	mail_sync.prototype.insert_mail = function(msg,folder) {
		try {

			expresso_mail_sync.open_conn();
		
			var msg_info = msg;
			var msg_header = msg['header'];
			var anexos = msg['array_attach'];
			
			
			var unseen = 0;
			var flagged = 0; 
	                var answered = 0;
			var login = msg_info.login;
			var original_id = msg_info.msg_number;
			var original_folder = msg_info.msg_folder=='INBOX/Lixeira/tmpMoveToLocal'?msg_info.msg_folder+(Date.parse(new Date)):msg_info.msg_folder;

			//Os campos abaixo precisam estar isolados para busca de mensagens em cima deles.
			var from = connector.serialize(msg_info.from);
			var subject = msg_info.subject;
			var body = msg_info.body;
			var to = connector.serialize(msg_info.toaddress2);
			var cc = connector.serialize(msg_info.cc);
			var size = msg_header.Size;
	
			//Zero os campos que foram isolados para não ter informações duplicadas
			msg_info.from = null;
			msg_info.subject = null;
			msg_info.body = null;
			msg_info.to = null;
			msg_info.cc = null;
			msg_header.Size=null;

			/**
			 * The importance attribute can be empty, and javascript consider as null causing nullpointer.
			 */
			if((msg_header.Importance == null) ||  (msg_header.Importance == ""))
				msg_header.Importance = "Normal";
			
			
			var mail = connector.serialize(msg_info);
			var header = connector.serialize(msg_header);
	
			var timestamp = msg_info.timestamp;
			var id_folder;
			
			var rs = this.dbGears.execute("select rowid from folder where folder=? and uid_usuario=?", [folder, account_id]);
			id_folder = rs.field(0);
	
			if(msg_info.Unseen=="U")
				unseen = 1;
			this.dbGears.execute("insert into mail (mail,original_id,original_folder,header,timestamp,uid_usuario,unseen,id_folder,ffrom,subject,fto,cc,body,size,flagged,answered) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",[mail,original_id,original_folder,header,timestamp,login,unseen,id_folder,from,subject,to,cc,body,size,flagged,answered]);


			
			//Preenche os anexos.
			var id_mail = this.dbGears.lastInsertRowId;

			for (var i = 0; i < anexos.length; i++) {
				this.dbGears.execute("insert into anexo (id_mail,nome_anexo,url,pid) values (?,?,?,?)", [id_mail, anexos[i]['name'],anexos[i]['url'],anexos[i]['pid']]);
			}
			
			var check_remove;
			if(this.is_auto)
				check_remove = preferences.keep_after_auto_archiving;
			else
				check_remove = preferences.keep_archived_messages;
			
			
			
			if(check_remove!=1)
				this.dbGears.execute("insert into msgs_to_remove (id_msg,folder,uid_usuario) values (?,?,?)",[original_id,original_folder,account_id]);
						
		} catch (error) {
			/*
			 * 0 - original id
			 * 1 - message subject
			 * 2 - original folder
			 */
			var mail_error = new Array();
			mail_error[0] = original_id;
			mail_error[1] = subject;
			mail_error[2] = original_folder;
			this.errors.push(mail_error);
			
			if (typeof(msg_info['array_attach'])=='object'&&(msg_info['array_attach'] instanceof Array)) { 
				for(var i in msg_info['array_attach']) { //remove os anexos que já foram baixados para essa mensagem...
					this.store.remove(msg_info['array_attach'][i]['url']);
				}
			}

		}
		this.dbGears.close();
		this.syncronize_mails(folder);
	}
	
	mail_sync.prototype.has_inbox_folder = function() {//This function considers that the connection with base is already opened.
		var rs = this.dbGears.execute("select rowid from folder where folder='Inbox' and uid_usuario=?",[account_id]);
		if(rs.isValidRow())
			return true;
		else 
			return false;
	}
	
	mail_sync.prototype.remove_archived_mails = function() {
		
		expresso_mail_sync.open_conn();

		var rs = this.dbGears.execute("select distinct(folder) from msgs_to_remove where uid_usuario=?",[account_id]);
		if(rs.isValidRow()) {
			var folder_to_remove_msgs = rs.field(0);
			var rs2 = this.dbGears.execute("select id_msg from msgs_to_remove where folder=? and uid_usuario=?",[folder_to_remove_msgs,account_id]);
			
			var msgs = null;
			while(rs2.isValidRow()) {
				if(msgs==null)
					msgs = rs2.field(0);
				else
					msgs += ","+rs2.field(0);
				rs2.next();
			}
			
			
			var handler_delete_msgs = function(data){
				if (current_folder == data.folder) {
					mail_msg = Element("tbody_box");
					var msg_to_delete;
					for (var i = 0; i < data.msgs_number.length; i++) {
						msg_to_delete = Element(data.msgs_number[i]);
						if (msg_to_delete) {
							mail_msg.removeChild(msg_to_delete);
						}
					}
					Element('tot_m').innerHTML = parseInt(Element('tot_m').innerHTML) - data.msgs_number.length;
				}
				
				refresh();
				expresso_mail_sync.remove_archived_mails();
			}
			
			cExecute ("$this.imap_functions.delete_msgs&folder="+folder_to_remove_msgs+"&msgs_number="+msgs, handler_delete_msgs);
			this.dbGears.execute("delete from msgs_to_remove where folder=? and uid_usuario=?",[folder_to_remove_msgs,account_id]);
			this.dbGears.close();
			
		}
		else {
			this.dbGears.close();
			document.getElementById('main_title').innerHTML = get_lang("End of archive messages");
			if(this.errors.length>0) {
				//TODO: Tratar melhor quando existirem erros...
				write_msg(get_lang("at least, one of selected mails is already archived, expresso tried to archive the others, check them later"));
				this.errors=new Array();
			}
			window.setTimeout("eval('document.getElementById(\"main_title\").innerHTML =\"Expresso Mail\"')",3000);
		}
		
	}
	
	mail_sync.prototype.configure_sync = function(folders,formul) {
		this.dbGears = google.gears.factory.create('beta.database');
		this.localServer = google.gears.factory.create('beta.localserver');
		this.store = this.localServer.createStore('test-store');	
		this.dbGears.open('database-test');
		this.dbGears.execute("delete from folders_sync where uid_usuario=?",[account_id]);
		for (var i=0;i<folders.length;i++) {
			var pos = folders[i].value.indexOf("/");
			if(pos=="-1")
				var folder_name = folders[i].text;
			else
				var folder_name = folders[i].value.substr(pos+1);

			folders[i].value = folders[i].value.replace(/#/g,' '); //Whitespaces has the # symbol in combo.
			folder_name = folder_name.replace(/#/g,' '); //Whitespaces has the # symbol in combo.
			
			this.dbGears.execute("insert into folders_sync (id_folder,folder_name,uid_usuario) values (?,?,?)",[folders[i].value,folder_name,account_id]);
		}
		this.dbGears.close();
		formul.submit();
	}
	
	mail_sync.prototype.fill_combos_of_folders = function(combo_sync) {
		var folders = expresso_local_messages.get_folders_to_sync();

		for(var i=0;i<folders.length;i++) {
			var option = document.createElement('option');
			option.value = folders[i][0];
			if (folders[i][1].indexOf("/") != "-1") {
				final_pos = folders[i][1].lastIndexOf("/");
				option.text =folders[i][1].substr(final_pos+1);
			}
			else
				option.text = folders[i][1];
			try {
				combo_sync.add(option,null);
			}catch (ex) {// I.E
				combo_sync.add(option);
			}

		}
	}
	
	mail_sync.prototype.add_folder = function (select_sync_folders,select_available_folders)
	{
		var count_available_folders = select_available_folders.length;
		var count_sync_folders = select_sync_folders.options.length;
		var new_options = '';
		
		for (i = 0 ; i < count_available_folders ; i++)
		{
			if (select_available_folders.options[i].selected)
			{
				if(document.all)
				{
					if ( (select_sync_folders.innerHTML.indexOf('value='+select_available_folders.options[i].value)) == '-1' )
					{
						new_options +=  '<option value='
									+ select_available_folders.options[i].value
									+ '>'
									+ select_available_folders.options[i].text
									+ '</option>';
					}
				}
				else
				{
					if ( (select_sync_folders.innerHTML.indexOf('value="'+select_available_folders.options[i].value+'"')) == '-1' )
					{
						new_options +=  '<option value='
									+ select_available_folders.options[i].value
									+ '>'
									+ select_available_folders.options[i].text
									+ '</option>';
					}
				}
			}
		}
	
		if (new_options != '')
		{
			select_sync_folders.innerHTML = '&nbsp;' + new_options + select_sync_folders.innerHTML;
			select_sync_folders.outerHTML = select_sync_folders.outerHTML;
		}
	}
	
	mail_sync.prototype.remove_folder = function(select_sync_folders)
	{
		for(var i = 0;i < select_sync_folders.options.length; i++)
			if(select_sync_folders.options[i].selected)
				select_sync_folders.options[i--] = null;
	}
	
	var expresso_mail_sync;
	expresso_mail_sync = new mail_sync();
	
