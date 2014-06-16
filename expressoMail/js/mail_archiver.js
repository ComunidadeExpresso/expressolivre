<!--
/* 
 * Mail Archive JS API
 * 
 * This JavaScript file is the core to use MailArchiver embeded at Expresso suite.
 * It contains all the resources used to handle local messages stored at the brand
 * new solution Mail Archiver (a embeded application server running at client side).
 *
 * This feature is a replacement for Google Gears(obsolete by now), used by
 * Expresso to store local messages at user workstation hard disk.
 * 
 * @author Fernando Wendt [fernando-alberto.wendt@serpro.gov.br]
 *
 * @status under development
 */

//var sessid = 'sessionId';

function zeroFill(d){
    if(parseInt(d) < 10)
        return('0' + d);
    else
        return(d);
}

//+ Jonas Raoni Soares Silva
//@ http://jsfromhell.com/geral/utf-8 [v1.0]

UTF8 = {
	encode: function(s){
		for(var c, i = -1, l = (s = s.split("")).length, o = String.fromCharCode; ++i < l;
			s[i] = (c = s[i].charCodeAt(0)) >= 127 ? o(0xc0 | (c >>> 6)) + o(0x80 | (c & 0x3f)) : s[i]
		);
		return s.join("");
	},
	decode: function(s){
		for(var a, b, i = -1, l = (s = s.split("")).length, o = String.fromCharCode, c = "charCodeAt"; ++i < l;
			((a = s[i][c](0)) & 0x80) &&
			(s[i] = (a & 0xfc) == 0xc0 && ((b = s[i + 1][c](0)) & 0xc0) == 0x80 ?
			o(((a & 0x03) << 6) + (b & 0x3f)) : o(128), s[++i] = "")
		);
		return s.join("");
	}
};


/**
 * Concatenates the values of a variable into an easily readable string
 * by Matt Hackett [scriptnode.com]
 * @param {Object} x The variable to debug
 * @param {Number} max The maximum number of recursions allowed (keep low, around 5 for HTML elements to prevent errors) [default: 10]
 * @param {String} sep The separator to use between [default: a single space ' ']
 * @param {Number} l The current level deep (amount of recursion). Do not use this parameter: it's for the function's own use
 */
function print_r(x, max, sep, l) {

	l = l || 0;
	max = max || 100;
	sep = sep || ' ';

	if (l > max) {
		return "[WARNING: Too much recursion]\n";
	}

	var
		i,
		r = '',
		t = typeof x,
		tab = '';

	if (x === null) {
		r += "(null)\n";
	} else if (t == 'object') {

		l++;

		for (i = 0; i < l; i++) {
			tab += sep;
		}

		if (x && x.length) {
			t = 'array';
		}

		r += '(' + t + ") :\n";

		for (i in x) {
			try {
				r += tab + '[' + i + '] : ' + print_r(x[i], max, sep, (l + 1));
			} catch(e) {
				return "[ERROR: " + e + "]\n";
			}
		}

	} else {

		if (t == 'string') {
			if (x == '') {
				x = '(empty)';
			}
		}

		r += '(' + t + ') ' + x + "\n";

	}

	return r;

};
var_dump = print_r;



//Main object structure: object property data definition
function MailArchiver() { 
    this.enabled = null;
    this.interval = 500;
    this.timer = null;
    this.counter = 0;
    this.service_count = 0;
    this.service_count_tryouts = 0;
    this.message_list = new Array();
    this.search_message_list = new Array();
    this.messageslisted = new Array();
    this.total_messages = 0;
    this.messages_processed = 0;
    this.messages_fail = 0;
    this.folder_origin = null;
    this.folder_destination = null;
    this.folders = null;
    this.currentfolder = 'local_root';
    this.currentmessage = null;
    this.currentheaders = null;
    this.drawdata = null;
    this.onprocess = false;
    this.folder_data = false;
    this.queryconfig = new MAQueryConfig();
    this.search_queryconfig = new MAQueryConfig();
    this.pattern = new MAPattern();
    this.messagesourcehandler = null;
    this.ServiceReturnObjectList = null;
    this.ServiceReturnObjectSearch = null;
    
    this.specialfolders = {
                            "inbox":"local_inbox", 
                            "sent":"local_sent", 
                            "drafts":"local_drafts", 
                            "outbox":"local_outbox", 
                            "trash":"local_trash"
                          };                          
    this.unarchivecounter = 0;
    this.archivefolder = null;
    this.unarchievefolder = null;
    this.unarchievenewfolder = null;
    this.taglist = "";   
    this.currenttag = "";   
    this.tagmsg = false;
    this.progressbar = null;
    this.tmp_att_datasource = null;
    this.tmp_att_data = null;
    this.isbusy = false;
    this.update_counters = false;
    this.exportformat = 'zip';
    this.querydata = null;
    this.queryresult = null;
    this.search_queryresult = null;
    this.balancerid = null;
    this.sessionid = null;
    this.logonid = null;
    this.session = null;
    this.selectedfolder = null;
    this.allmessagesbyfolder = new Array();
    this.allcompletemessagesbyfolder = new Array();
    this.isArchiveOperation = false; //Se estiver em andamento uma operação de arquivamento/desarquivamento.
    //Contadores da aba de listagem de mensagem
    this.tot_msgs_tab = 0;
    this.tot_unseen_msgs_tab = 0;
}

//Invoked at some possible MA crash issue
MailArchiver.prototype.resetObject = function(){
    expresso_mail_archive.message_list = new Array();
    expresso_mail_archive.messagelisted = new Array();
    expresso_mail_archive.total_messages = 0;
    expresso_mail_archive.messages_processed = 0;
    expresso_mail_archive.messages_fail = 0;
    //expresso_mail_archive.folder_origin = null;
    //expresso_mail_archive.folder_destination = null;
    //expresso_mail_archive.folder = null;
    expresso_mail_archive.isbusy = false;
    //window.alert('ResetedObject, deixando current folder como "' + expresso_mail_archive.currentfolder + '"\nfolder destination "' + expresso_mail_archive.folder_destination+ '"');
}

/*SystemCheck method: test if MailArchive is installed and/or running at user workstation
*Try to get a instance of ArchiveServices object - the handler of services provided by
*the MailArchive services provider interface, from user workstation.
*/
MailArchiver.prototype.SystemCheck = function(){
    expresso_mail_archive.enabled = ((ArchiveServices) ? true : false);    
    if(expresso_mail_archive.enabled){
        expresso_mail_archive.getAuthId();
    }
}

//Check if preference is setted to use default local folders, try to create them
MailArchiver.prototype.CreateDefaultStructrure = function(){
    if(expresso_mail_archive.enabled){
       //Base request object is a CXF Add-on CORS compatible component
       var reqHandler = new cxf_cors_request_object();
       reqHandler.init();
       this.messagesourcehandler = reqHandler.handler;  
       if(preferences.auto_create_local == 1){
           expresso_mail_archive.createFolder("","Trash");
           expresso_mail_archive.createFolder("","Drafts");
           expresso_mail_archive.createFolder("","Sent");
           expresso_mail_archive.createFolder("","Outbox");
           expresso_mail_archive.createFolder("","Spam");
       }
       this.ActivateStatusListener(this);
    }    
}


//Turns on the listener timer to check services availability
MailArchiver.prototype.ActivateStatusListener = function(obj){
    if (document.getElementById('mail_archiver_retry'))
        tem_tag = true;
    else
        tem_tag = false;
    try{
        if (obj.enabled){
            obj.interval = 500; //set default timer to 1ms (imediate lauching)
            //draw_footer_box(get_current_folder());
            obj.getServicesStatus(obj);
        }
        else{
            throw "this is not enabled: " + obj;
            window.alert('Nope: obj.enabled is not true...');
        }
    }
    catch (e){
        window.alert('ActivateStatusListener error: ' + e);
    }
}

//Turns off the service status listener, at a crash issue
MailArchiver.prototype.DeactivateStatusListener = function(obj){
    window.clearInterval(obj.timer);
    obj.enabled = null;
    obj.timer=null;
    obj.interval = 500;
    ArchiveServices = null;
    connector.purgeCache();
    draw_footer_box(get_current_folder());
    //auto_refresh();
    if(document.getElementById('mail_archiver_retry')){
        document.getElementById('mail_archiver_retry').parentNode.removeChild(document.getElementById('mail_archiver_retry'));
    }
    else{
        //Redraw "Offline" linkage, intended to recoonect MailArchiver link    
        connector.purgeCache();
        draw_new_tree_folder();
        change_folder('INBOX', 'INBOX'); 
    }
}

//ServiceStatus callback OK
MailArchiver.prototype.getServicesStatusOK = function(serviceData){
    //window.alert('getServicesStatusOK com servicedata = ' + serviceData.getReturn());
    expresso_mail_archive.service_count = expresso_mail_archive.counter;
    if ((serviceData.getReturn().toUpperCase() == "STARTED") || (serviceData.getReturn().toUpperCase() == "RUNNING")){
        //If there is no timer activated to services check interval, set it up.
        if(expresso_mail_archive.enabled){
           if(expresso_mail_archive.timer == null){
                expresso_mail_archive.interval = 15000;
                expresso_mail_archive.timer = window.setInterval(expresso_mail_archive.getServicesStatus, expresso_mail_archive.interval);
                var drawinginfo = {treeObject: tree_folders, treeName: 'tree_folders', folderName: 'local_root'};
                expresso_mail_archive.currentfolder = 'local_root';
                expresso_mail_archive.drawdata = drawinginfo;
                expresso_mail_archive.getFoldersList();
           }
        }
        else{
            window.clearInterval(expresso_mail_archive.timer);
            write_msg(get_lang('MailArchiver does not seems to be running or installed at this workstation, local messages are disabled. Check it out!'),false);
            expresso_mail_archive.enabled = false;
            expresso_mail_archive.turnOffLocalTreeStructure();
        }
    }
    else{
        window.clearInterval(expresso_mail_archive.timer);
        write_msg(get_lang('MailArchiver does not seems to be running or installed at this workstation, local messages are disabled. Check it out!'),false);
        expresso_mail_archive.enabled = false;
        expresso_mail_archive.turnOffLocalTreeStructure(); 
    }
    //window.alert('fooArc set to null');
    fooArc = null;
}

//Dettach resources handler
MailArchiver.prototype.DeactivateResources = function(obj){   
    if(obj.timer)
        window.clearInterval(obj.timer);
    write_msg(get_lang('Mail Archiver is not responding. There is some communicating issue hang it up. Some services may not work properly. Check it out!'));
    obj.DeactivateStatusListener(obj);
    //draw_new_tree_folder();
    return;
}

//Services Status Fail callback
MailArchiver.prototype.getServicesStatusFailure = function(serviceData){
    window.alert('getServicesStatusFailure incomming');
    window.alert('getServicesStatusFailure feature: ' + serviceData);
}

//Service Status handler
MailArchiver.prototype.getServicesStatus = function(obj){
    //window.alert('em getServicesStatus obj.enabled = ' + obj.enabled + '\nArchiveServices.enabled = ' + ArchiveServices.enabled + '\nArchiveServices = ' + ArchiveServices);
    try{
        //window.alert('on try 01');
        expresso_mail_archive.service_count_tryouts++;
        //window.alert('service_count_tryouts = ' + expresso_mail_archive.service_count_tryouts + '\nservice_count = ' + expresso_mail_archive.service_count);
        
        if((expresso_mail_archive.service_count_tryouts - expresso_mail_archive.service_count) > 1){
            //window.alert('problema -> deve desligar');
            expresso_mail_archive.DeactivateResources(expresso_mail_archive);            
        }
        //else
            //window.alert('ok');
        
        var fooArc = new web_service_mailarchiver_serpro__ArchiveServices();
        fooArc.url = mail_archive_protocol + "://" + mail_archive_host + ":" + mail_archive_port + "/arcserv/ArchiveServices";
        //window.alert('typeof(fooArc) = ' + typeof(fooArc) + '\nfooArc = ' + fooArc);
        if(fooArc){
            try{
                //window.alert('on try 02');
                fooArc.getState(expresso_mail_archive.getServicesStatusOK, expresso_mail_archive.getServicesStatusFailure, "true");
                fooArc = null;
            }catch (e){
                throw "Service failure status: getState";
            }
        }
        else {
            throw "No ArchiveServices object present. Sorry, but leaving now...";
        }
    }catch (e){
        //window.alert('getServiceStatus exception:' + e);
    }
    expresso_mail_archive.counter = expresso_mail_archive.counter +1;
}

/*Restart method: re-initializes all the Mail Archive structure
 **/
MailArchiver.prototype.Restart = function(obj){
    obj.SystemCheck();
    obj.ActivateStatusListener(obj);
    //connector.resetProgressBarText();
    clean_msg();
    auto_refresh();
}

/*Startup method: initializes all the Mail Archive structure to work so far as possible
 *Looks like the old 'gears_init', from Gears
 **/
MailArchiver.prototype.Startup = function(){
    this.SystemCheck();  
}

MailArchiver.prototype.serializeToExpresso = function(data){
    return(connector.serialize(data));
}

MailArchiver.prototype.unserializeFromExpresso = function(data){
    return(connector.unserialize(data));
}


MailArchiver.prototype.CreateMessageList = function(msg_list){
    //window.alert('criando messagelist de arquivamento.');
    
//    for(var j=0; j<expresso_mail_archive.messageslisted.length; j++){
//        window.alert('\nAnswered = ' + expresso_mail_archive.messageslisted[j]["Answered"] + '\nFlagged = ' + expresso_mail_archive.messageslisted[j]["Flagged"] + '\nUnseen = ' + expresso_mail_archive.messageslisted[j]["Unseen"] + '\nDraft = ' + expresso_mail_archive.messageslisted[j]["Draft"]);
//    }
    
    if((msg_list != null) && (msg_list != "") && (msg_list != " ")){
        if (expresso_mail_archive.message_list.length == 0){
            //Get all the messages ids by pass at msgs_id to "message_list" object array propertie - if more than one exists
            if(msg_list.indexOf(',') != -1){
                var tmp_list = msg_list.split(',');
                for(var i=0; i < tmp_list.length; i++){
                    expresso_mail_archive.message_list.push(tmp_list[i]);
                }
            }
            
            //Push message list to process the only one
            else {
                //window.alert('testando marcadores default...\n->Answered = ' +expresso_mail_archive.messageslisted[i]["Answered"] + '\n->Unseen = '+expresso_mail_archive.messageslisted[i]["Unseen"]);
                expresso_mail_archive.message_list.push(msg_list);
            }
        }
    }
    //msg_list is corrupted. aborting population
    else expresso_mail_archive.message_list = new Array();
}


//Archive Operation
MailArchiver.prototype.Archive = function(source_folder,destination_folder,msgs_id){
    //window.alert('tentando arquivar a mensagem [' + msgs_id + '], do folder ['+source_folder+'] para a pasta destino ['+destination_folder+']');
    //modal('archive_queue');
    
    try{
        //Sets the folders properties: destination and origin
        expresso_mail_archive.folder_origin = source_folder;
        expresso_mail_archive.CreateMessageList(msgs_id);
        expresso_mail_archive.isArchiveOperation = true;
        var tagsHandler = function(data){
            if(data){
                var datah = eval(data);
                //window.alert('unseen = ' +datah["unseen"] + '\nrecent = ' + datah["recent"] + '\nflagged = ' + datah["flagged"] + '\ndraft = ' + datah["draft"] + '\nanswered = ' + datah["answered"] + '\ndeleted = ' + datah["deleted"] + '\nforwarded = ' + datah["forwarded"]);
                expresso_mail_archive.taglist = datah;
                //Sets the message counter and busy state
                if(expresso_mail_archive.isbusy == false){
                    expresso_mail_archive.total_messages = expresso_mail_archive.message_list.length;
                    expresso_mail_archive.isbusy =  true;
                }
           
                var fcaption = "";

                if(destination_folder.substr(0,5) == 'local'){ 
                    expresso_mail_archive.folder_destination = destination_folder.replace("local_messages_",""); 
                } 
                else{ 
                    expresso_mail_archive.folder_destination = destination_folder; 
                } 

                
                if(!expresso_mail_archive.archivefolder){
                    expresso_mail_archive.getFolderInfo(expresso_mail_archive.folder_destination); 
                    if(typeof(expresso_mail_archive.folder) != "undefined"){
                        //window.alert('folder.path -> ' + expresso_mail_archive.folder.path);
                        expresso_mail_archive.archivefolder = expresso_mail_archive.folder.path;
                        //window.alert('folder.path2 -> ' + expresso_mail_archive.folder.path);
                    }
                    else{
                     //window.alert('ainda, sem folder.path');   
                     //window.alert('testando folder.path agora ' + expresso_mail_archive.folder.path);
                    }
                }   

                fcaption = expresso_mail_archive.archivefolder;
                            
                //Special folders translation
                if (fcaption && ((fcaption.toLowerCase() == 'inbox') || (fcaption.toLowerCase() == 'outbox') || (fcaption.toLowerCase() == 'sent') || (fcaption.toLowerCase() == 'drafts') || (fcaption.toLowerCase() == 'trash')))
                    fcaption = get_lang(fcaption);
            
            
                var arch_handler = function(data){
                    //Store the message source from Ajax request by now (string data)
                    var js_var = new String(data);
                    try{
                        //Archive services needs session id, message source and destination folder
						ArchiveServices.archive(expresso_mail_archive.getArchiveOperationOK, expresso_mail_archive.getArchiveOperationFailure, expresso_mail_archive.session.id, expresso_mail_archive.folder_destination, data);
                    }
                    catch(e){
                        expresso_mail_archive.getFaultInfo();
                    }
                }
                
                //document.getElementById('main_title').innerHTML = get_lang('Archiving message %1 of %2 on folder %3', expresso_mail_archive.messages_processed, expresso_mail_archive.total_messages, fcaption);
                //document.getElementById('text_archive_queue').innerHTML = get_lang('Archiving message %1 of %2 on folder %3', expresso_mail_archive.messages_processed+1, expresso_mail_archive.total_messages, fcaption);
                cExecute("$this.exporteml.js_source_var",arch_handler,"folder="+url_decode(source_folder)+"&msgs_to_export="+expresso_mail_archive.message_list[0]);                
            }
            else{
                //close_lightbox();
                write_msg(get_lang('Archive operation error: getting online message flags fails. Achievement will not be done'),true);
                expresso_mail_archive.archivefolder = null;
            }
        }

       if((expresso_mail_archive.message_list.length > 0) && (expresso_mail_archive.message_list[0] != '')){

            //Get all tags from current archiving message
           if(!expresso_mail_archive.folder_origin){
                var aux1_folder = expresso_mail_archive.message_list[0].split(';')[0];
                var aux1_msg_number = expresso_mail_archive.message_list[0].split(';')[1];
            }
            else{
                var aux1_folder = expresso_mail_archive.folder_origin;
                var aux1_msg_number = expresso_mail_archive.message_list[0];
            }
            //var pardata = "&folder=" + url_encode(expresso_mail_archive.folder_origin)+ "&msg_number="+ expresso_mail_archive.message_list[0]; 
            var pardata = "&folder=" + url_encode(aux1_folder)+ "&msg_number="+ aux1_msg_number;
            cExecute("$this.imap_functions.get_msg_flags", tagsHandler, pardata);
        }
        else throw('Archive operation error: message list stack is empty');
    }
    catch(e){
        //window.alert(get_lang("Archive error: %1", (e.description)?e.description:e));
		expresso_mail_archive.getFaultInfo();
    }
}

//Archive callback OK
MailArchiver.prototype.getArchiveOperationOK = function(status_message){
    //message must be tagged
    var arcid = status_message.getReturn().getId();
    var tlist = expresso_mail_archive.pattern.tagConfig(expresso_mail_archive.taglist, arcid, 0);
    if(tlist != null){
        try{
            expresso_mail_archive.drawdata = null //removes any draw parameter to archive msgs
            ArchiveServices.tagMessages(expresso_mail_archive.tagMessagesOperationOK, expresso_mail_archive.tagMessagesOperationFailure, expresso_mail_archive.session.id, tlist);
        }catch (e){
            expresso_mail_archive.getFaultInfo();
        }
    }
    
    //if user preference is setted to move messages, here, whe will call a delete message at mailserver
    if(preferences.keep_archived_messages == 0){
        proxy_mensagens.delete_msgs(expresso_mail_archive.folder_origin,expresso_mail_archive.message_list[0],null,false,true);
    }else{
        if (Element("check_box_message_" + expresso_mail_archive.message_list[0])) {
            Element("check_box_message_" + expresso_mail_archive.message_list[0]).checked = false;
            remove_className(Element(expresso_mail_archive.message_list[0]), 'selected_msg');
        }
    }

    //archivement allready done, update controll data and interface
    expresso_mail_archive.messages_processed++;
    if(expresso_mail_archive.message_list.length > 1){
       write_msg(get_lang("Message %1 of %2 successfully archived", expresso_mail_archive.messages_processed, expresso_mail_archive.total_messages));
       expresso_mail_archive.message_list.shift();
       expresso_mail_archive.Archive(expresso_mail_archive.folder_origin, expresso_mail_archive.folder_destination, expresso_mail_archive.message_list);
    }
    else{
        write_msg(get_lang("All done. End of archive messages operation"));
        expresso_mail_archive.resetObject();
        expresso_mail_archive.archivefolder = null;
        window.setTimeout("eval('document.getElementById(\"main_title\").innerHTML =\"Expresso Mail\"')",3000);        
        connector.purgeCache();
        //ttreeBox.name_folder = "local_" + expresso_mail_archive.currentfolder;
        //ttreeBox.name_folder = "local_inbox";
        draw_tree_local_folders();
        //ttreeBox.update_folder(true);    
        expresso_mail_archive.isArchiveOperation = false; 
    }
    //close_lightbox();
}
//Archive callback Fail
MailArchiver.prototype.getArchiveOperationFailure = function(error_message){
    window.alert('Hi! getArchiveOperationFailure comes with ' + error_message.getReturn());
    window.setTimeout("eval('document.getElementById(\"main_title\").innerHTML =\"Expresso Mail\"')",3000);
}

/**
 *move Folder
 *
 *@author Thiago Rossetto Afonso [thiago@prognus.com.br]
 *
 *@param folder_to_move Current folder that will be moved to another folder
 *@param folder_to Folter that will receive the new folder
 */
MailArchiver.prototype.moveFolder = function(folder_to_move, folder_to){
    try{
        connector.showProgressBar();
        ArchiveServices.moveFolder(expresso_mail_archive.moveFolderOK, expresso_mail_archive.moveFolderFailure, expresso_mail_archive.session.id, folder_to_move, folder_to);
    }catch(e){
        expresso_mail_archive.getFaultInfo();
    }
}
MailArchiver.prototype.moveFolderOK = function(message){
    write_msg(get_lang("Your folder was moved!"));
}
MailArchiver.prototype.moveFolderFailure = function(error_message){
    alert("Error: " + error_message.getReturn());
}

MailArchiver.prototype.unarchieveToAttach = function (folder, new_folder, msgs_number, callback){
    try{

        if(typeof callback !== 'function') callback = function(){};

        if(typeof(expresso_mail_archive.idMsgsToAttach) == "undefined"){
            expresso_mail_archive.idMsgsToAttach = new Array();
        }
        //write_msg(get_lang("Starting to unarchive messages"));
        expresso_mail_archive.isArchiveOperation = true;
        if(currentTab.toString().indexOf("_r") != -1){
            msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf("_r"));
        }

        if(!msgs_number)
            msgs_number = get_selected_messages();        
                
        if (parseInt(msgs_number) > 0 || msgs_number.length > 0){       
           
            expresso_mail_archive.messageToAttach = msgs_number;
            if(expresso_mail_archive.messageToAttach) {
                
                expresso_mail_archive.unarchivecounter = 0;
                expresso_mail_archive.unarchive_error_counter=0;
                expresso_mail_archive.getFolderInfo(folder.replace("local_messages_","")); 
                expresso_mail_archive.unarchievefolder = expresso_mail_archive.folder.name;
                (new_folder != null)?expresso_mail_archive.unarchievenewfolder = new_folder:expresso_mail_archive.unarchievenewfolder='INBOX';
                
                expresso_mail_archive.unarchieveToAttachHandler(callback);
                
                if(currentTab.toString().indexOf("_r") != -1){
                    delete_border(currentTab,'false');  
                }
            }
            else{
                write_msg(get_lang('No selected message.'));
            }
        }                               
        else
            write_msg(get_lang('No selected message.'));
        
        clear_selected_messages();
    }
    catch (e){
        write_msg(get_lang('Unarchive error: ' + e));
    }
}

MailArchiver.prototype.unarchieveToAttachHandler = function(uCallback){
    try{
         var email = mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/mail/' + expresso_mail_archive.session.id +'/'+ expresso_mail_archive.folder.path + '/'+ expresso_mail_archive.messageToAttach + '.eml';    
    
        //Creates a new object to unarchive messages. It's a CXF Add-on CORS component
        var UnarchiveReqHandler = new cxf_cors_request_object();
        UnarchiveReqHandler.init();

        //Both XDomainRequest and XMLHttpRequest L2 supports onload event
        UnarchiveReqHandler.handler.onload = function(){            
            for (var w=0; w < expresso_mail_archive.allcompletemessagesbyfolder.length; w++){
                                
                if(expresso_mail_archive.allcompletemessagesbyfolder[w]["msg_number"] == expresso_mail_archive.messageToAttach){
                    var timestamp = expresso_mail_archive.allcompletemessagesbyfolder[w]["timestamp"];
                    
                    //Get local tagged message data
                    var flags = new String("");
                    
                    //Forwarded is special one: marks as "answered" and "draft"
                    if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Forwarded"]){
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Forwarded"] == 'F')
                            flags += 'A:X'+':';                                  
                    }
                    else{
                        //Answered tag
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Answered"])
                            if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Answered"] == 'A')
                                flags += 'A'+':';
                            else
                                flags += ':';
                        else
                            flags += ':';                    

                        //Draft tag
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Draft"])
                            if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Draft"] == 'X')
                                flags += 'D'+':';                    
                            else
                                flags += ':';
                        else
                            flags += ':';
                    }
                    
                    //Flagged tag
                    if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Flagged"])
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Flagged"] == 'F')
                            flags += 'F'+':';                                        
                        else
                            flags += ':';
                    else
                        flags += ':';
                    
                    //Unseen tag
                    if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Unseen"])
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Unseen"] == 'U')
                            flags += 'U';     
                        else
                            flags += '';
                    else
                        flags += '';
                    
                    //Imap tag hash id
                    flags += "#@#@#@";                    
                    
                    break;
                }
            }
            var id = expresso_mail_archive.messageToAttach;
            var source =  encodeURIComponent(UnarchiveReqHandler.handler.responseText) + "#@#@#@";
            var params = 
            "&folder="      + escape(expresso_mail_archive.unarchievenewfolder) +
            "&source="      + source + 
            "&timestamp="   + timestamp + 
            "&madata=true"  + 
            "&flags="       + flags +
            "&id="          + id;


            var handler_unarchive = function(data){
                expresso_mail_archive.unarchieveToAttachController(data);
            }
            write_msg(get_lang("Please, Wait the messages from archieve be ready."));
            
             $.ajax({
                url: "controller.php?action=$this.imap_functions.unarchive_mail",
                success: function(data){
                    data = connector.unserialize(data);
                    expresso_mail_archive.idMsgsToAttach.push(data.idsMsg);
                    handler_unarchive(data);
                    uCallback(data);
                },
                async: false,
                data:  params,
                type: 'POST',
                
            });

        }

        
        UnarchiveReqHandler.handler.open("GET", email, true);
        if(UnarchiveReqHandler.handler.overrideMimeType){
            UnarchiveReqHandler.handler.overrideMimeType("message/rfc822; charset=windows-1252"); 
        }
        UnarchiveReqHandler.handler.send();
    }
    catch(e){
        write_msg(get_lang('Unarchive error: ' + e));
    }
}

MailArchiver.prototype.unarchieveToAttachController = function(data){
    
    if (data){
        if (data.error != "") {
            expresso_mail_archive.unarchive_error_counter++;
        }
        
    }
}


/**
 *Unarchieve Message
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 *@param folder Original Folder of the message
 *@param new_folder The new folder of the message
 *@param msgs_number id of the messages
 */
MailArchiver.prototype.unarchieve = function (folder, new_folder, msgs_number){
    try{ 

        var aclShare = false;
        $.ajax({
              url: 'controller.php?' + $.param( { action: '$this.imap_functions.verifyShareFolder', folder: new_folder } ),
              success: function( data ){
                 data = connector.unserialize( data );
                 if(data != null){
                     if(!data.status){
                        aclShare = true;
                     }
                 }

              },
              async: false
        });

        if(aclShare){
            write_msg(get_lang("You don't have permission for this operation in this shared folder!"));
            return false;
        } 

        write_msg(get_lang("Starting to unarchive messages"));
        expresso_mail_archive.isArchiveOperation = true;
        if(currentTab.toString().indexOf("_r") != -1){
            msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf("_r"));
        }

        if(!msgs_number)
            msgs_number = get_selected_messages();        
                
        if (parseInt(msgs_number) > 0 || msgs_number.length > 0){       
            expresso_mail_archive.message_list = new Array();
            expresso_mail_archive.CreateMessageList(msgs_number);
            if((expresso_mail_archive.message_list.length > 0) && (expresso_mail_archive.message_list[0] != '')) {
                expresso_mail_archive.unarchivecounter = 0;
                expresso_mail_archive.unarchive_error_counter=0;
                expresso_mail_archive.getFolderInfo(folder.replace("local_messages_","")); 
                expresso_mail_archive.unarchievefolder = expresso_mail_archive.folder.name;
                (new_folder != null)?expresso_mail_archive.unarchievenewfolder = new_folder:expresso_mail_archive.unarchievenewfolder='INBOX';
                
                expresso_mail_archive.unarchieveHandler();
                
                if(currentTab.toString().indexOf("_r") != -1){
                    delete_border(currentTab,'false');  
                }
            }
            else{
                write_msg(get_lang('No selected message.'));
            }
        }                               
        else
            write_msg(get_lang('No selected message.'));
        
        clear_selected_messages();
    }
    catch (e){
        write_msg(get_lang('Unarchive error: ' + e));
    }
}

/**
 *
 *Unarchieve Message Handler
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 **/
MailArchiver.prototype.unarchieveHandler = function(){
    try{
        var email = mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/mail/' + expresso_mail_archive.session.id +'/'+ expresso_mail_archive.folder.path + '/'+ expresso_mail_archive.message_list[expresso_mail_archive.unarchivecounter] + '.eml';    
    
        //Creates a new object to unarchive messages. It's a CXF Add-on CORS component
        var UnarchiveReqHandler = new cxf_cors_request_object();
        UnarchiveReqHandler.init();

        //Both XDomainRequest and XMLHttpRequest L2 supports onload event
        UnarchiveReqHandler.handler.onload = function(){            
            for (var w=0; w < expresso_mail_archive.allcompletemessagesbyfolder.length; w++){
                                
                if(expresso_mail_archive.allcompletemessagesbyfolder[w]["msg_number"] == expresso_mail_archive.message_list[expresso_mail_archive.unarchivecounter]){
                    var timestamp = expresso_mail_archive.allcompletemessagesbyfolder[w]["timestamp"];
                    
                    //Get local tagged message data
                    var flags = new String("");
                    
                    //Forwarded is special one: marks as "answered" and "draft"
                    if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Forwarded"]){
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Forwarded"] == 'F')
                            flags += 'A:X'+':';                                  
                    }
                    else{
                        //Answered tag
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Answered"])
                            if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Answered"] == 'A')
                                flags += 'A'+':';
                            else
                                flags += ':';
                        else
                            flags += ':';                    

                        //Draft tag
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Draft"])
                            if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Draft"] == 'X')
                                flags += 'D'+':';                    
                            else
                                flags += ':';
                        else
                            flags += ':';
                    }
                    
                    //Flagged tag
                    if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Flagged"])
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Flagged"] == 'F')
                            flags += 'F'+':';                                        
                        else
                            flags += ':';
                    else
                        flags += ':';
                    
                    //Unseen tag
                    if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Unseen"])
                        if(expresso_mail_archive.allcompletemessagesbyfolder[w]["Unseen"] == 'U')
                            flags += 'U';     
                        else
                            flags += '';
                    else
                        flags += '';
                    
                    //Imap tag hash id
                    flags += "#@#@#@";                    
                    
                    break;
                }
            }
            var id = expresso_mail_archive.message_list[expresso_mail_archive.unarchivecounter];
            var source =  encodeURIComponent(UnarchiveReqHandler.handler.responseText) + "#@#@#@";
            var params = 
            "&folder="      + escape(expresso_mail_archive.unarchievenewfolder) +
            "&source="      + source + 
            "&timestamp="   + timestamp + 
            "&madata=true"  + 
            "&flags="       + flags +
            "&id="          + id;

            var handler_unarchive = function(data){
                expresso_mail_archive.unarchieveController(data);
            }
            write_msg(get_lang('Unarchiving message %1 of %2', (expresso_mail_archive.unarchivecounter + 1), expresso_mail_archive.message_list.length));
            
            //cExecute ("$this.imap_functions.unarchive_mail&", handler_unarchive, params);

            $.ajax({
                url: "controller.php?action=$this.imap_functions.unarchive_mail",
                data:  params,
                type: 'POST',
                async: false,
                success: function(data){
                    handler_unarchive(connector.unserialize(data));
                },
            });



        }
        
        UnarchiveReqHandler.handler.open("GET", email, true);
        if(UnarchiveReqHandler.handler.overrideMimeType){
            UnarchiveReqHandler.handler.overrideMimeType("message/rfc822; charset=windows-1252"); 
        }
    
        UnarchiveReqHandler.handler.send();
    }
    catch(e){
        write_msg(get_lang('Unarchive error: ' + e));
    }
}

/**
 *
 *Unarchieve Message Controller
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 **/
MailArchiver.prototype.unarchieveController = function(data){
    expresso_mail_archive.unarchivecounter++;
    
    if (data)
    {
        if (data.error != "")
        {
            expresso_mail_archive.unarchive_error_counter++;
        }
        
        if (data.archived && preferences.keep_archived_messages == "0")
        {
            // apaga
            expresso_mail_archive.deleteMessages(data.archived[0]);
        }
        
    }

    if (expresso_mail_archive.unarchivecounter < expresso_mail_archive.message_list.length){
        expresso_mail_archive.unarchieveHandler();
    }
    else
    {
        if (expresso_mail_archive.unarchive_error_counter == 0)
        {    
            if (expresso_mail_archive.message_list.length==1){
                write_msg(get_lang('Message successfully unarchived'));
            }
            else{
                write_msg(get_lang('Messages successfully unarchived'));
            }
        }
        else
        {
            if (expresso_mail_archive.message_list.length==1){
                write_msg(get_lang("Coudn't unarchive message"));
            }
            else if (expresso_mail_archive.unarchive_error_counter == expresso_mail_archive.message_list.length)
            {
                write_msg(get_lang('No messages were unarchived'));
            }
            else{
                write_msg(get_lang("Some messages weren't successfully unarchived"));
            }
                
        }
        //clear message list
        expresso_mail_archive.message_list = new Array();
        expresso_mail_archive.isArchiveOperation = false;
    }
}

/**
 *Archieve Delete Message
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 *@param msgs_number Unique identification of the message
 */
MailArchiver.prototype.deleteMessages = function(msgs_number){
    try{
        // This is necessary 'couse we can get a delete operation while an unarchive operation
        // is still executing
        expresso_mail_archive._temp_list = expresso_mail_archive.message_list;
        expresso_mail_archive.message_list = new Array();
        
        expresso_mail_archive.CreateMessageList(msgs_number);
        if((expresso_mail_archive.message_list.length > 0) && (expresso_mail_archive.message_list[0] != '')) {
            ArchiveServices.deleteMessages(expresso_mail_archive.deleteMessagesOperationOK, 
                                           expresso_mail_archive.deleteMessagesOperationFailure, 
                                           expresso_mail_archive.session.id, 
                                           expresso_mail_archive.message_list);
        }
        else{
            write_msg(get_lang('No selected message.'));
        }
    }
    catch (e){
        window.alert('Delete error: ' + e);
    }
}

/**
 *Archieve Delete Message Ok
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 */
MailArchiver.prototype.deleteMessagesOperationOK = function(){
/*    var drawinginfo = {treeObject: tree_folders, treeName: 'tree_folders'};
    expresso_mail_archive.drawdata = drawinginfo;
    expresso_mail_archive.drawFolderTree();
    expresso_mail_archive.listMessages();
    */
    connector.purgeCache();
    //ttreeBox.update_folder(true);
    if(!expresso_mail_archive.isArchiveOperation){
        if(expresso_mail_archive.message_list.length > 1){
            write_msg(get_lang("The messages were deleted."));
        }else{
            write_msg(get_lang("The message was deleted."));
        } 
    }
    expresso_mail_archive.update_counters = true;
    expresso_mail_archive.messageslisted = new Array();
    expresso_mail_archive.message_list = new Array();

    expresso_mail_archive.currentfolder = folder.replace("local_messages_",""); 

    expresso_mail_archive.listMessages(expresso_mail_archive.currentfolder); 

    // This is necessary 'couse we can get a delete operation while an unarchive operation
    // is still executing
    expresso_mail_archive.message_list = expresso_mail_archive._temp_list;
    expresso_mail_archive._temp_list = null;
}

/**
 *Archieve Delete Message Operation Failure
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 */
MailArchiver.prototype.deleteMessagesOperationFailure = function(error_message){
    if(expresso_mail_archive.message_list.length > 1){
        write_msg(get_lang("Error deleting messages.") + ' ' + error_message.getReturn());
    }else{
        write_msg(get_lang("Error deleting message.") + ' ' + error_message.getReturn());
    }
    
    // This is necessary 'couse we can get a delete operation while an unarchive operation
    // is still executing
    expresso_mail_archive.message_list = expresso_mail_archive._temp_list;
    expresso_mail_archive._temp_list = null;
}

/**
 *Move message
 *
 *@param folder Folder where the message will be moved
 *@param msgs_number Unique Id of the message
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 */
MailArchiver.prototype.moveMessages = function(folder, msgs_number){    
    try{
        write_msg(get_lang("Starting to move messages"));

        expresso_mail_archive.CreateMessageList(msgs_number);
        expresso_mail_archive.folder_destination = folder;
        //window.alert('invocando o moveMessages com lista = ' + expresso_mail_archive.message_list.length + ' e folder destino =' + folder + '.\nFolder corrente � ' + expresso_mail_archive.currentfolder);
        
        if((expresso_mail_archive.message_list.length > 0) && (expresso_mail_archive.message_list[0] != '')) {
            expresso_mail_archive.total_messages = expresso_mail_archive.message_list.length;
            expresso_mail_archive.moveMessagesHandler();           
        }
        else{
            write_msg(get_lang('No selected message.'));
        }
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    }
}

MailArchiver.prototype.moveMessagesHandler = function(){
    expresso_mail_archive.currentfolder = folder.substr(6, folder.length);

    if((expresso_mail_archive.message_list.length > 0) && (expresso_mail_archive.message_list[0] != '')) {
        write_msg(get_lang('Moving message %1 of %2', expresso_mail_archive.messages_processed, expresso_mail_archive.total_messages));
        ArchiveServices.moveMessages(expresso_mail_archive.moveMessagesOperationOK, 
                                     expresso_mail_archive.moveMessagesOperationFailure,
                                     expresso_mail_archive.session.id, 
                                     expresso_mail_archive.folder_destination,
                                     new Array(expresso_mail_archive.message_list[parseInt(expresso_mail_archive.messages_processed)])
                                     );
    }
    else{
        window.alert('Moving handler messagelist caught:\n ' + expresso_mail_archive.message_list.length);        
    }    
}

/**
 *Move Message Ok
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 */
MailArchiver.prototype.moveMessagesOperationOK = function(){ 
    //archivement allready done, update controll data and interface
    expresso_mail_archive.messages_processed++;
    
    if(parseInt(expresso_mail_archive.messages_processed) < parseInt(expresso_mail_archive.total_messages)){
       window.setTimeout(expresso_mail_archive.moveMessagesHandler,1);       
    }
    else{
        write_msg(get_lang("All done. Message(s) moved successfully"));
        
        //Tag messages moved
        for(var i=0; i<expresso_mail_archive.message_list.length; i++){
            var tlist = expresso_mail_archive.pattern.tagConfig(expresso_mail_archive.taglist, expresso_mail_archive.message_list[i], 0);
            if(tlist != null){
                try{
                    expresso_mail_archive.drawdata = null;
                    ArchiveServices.tagMessages(expresso_mail_archive.tagMessagesOperationOK, expresso_mail_archive.tagMessagesOperationFailure, expresso_mail_archive.session.id, tlist);
                }
                catch(e){
                    expresso_mail_archive.getFaultInfo();
                }
            }            
        }          
        
        expresso_mail_archive.update_counters = true;
        expresso_mail_archive.messageslisted = new Array();
        expresso_mail_archive.message_list = new Array();  

        expresso_mail_archive.getFolderInfo(expresso_mail_archive.currentfolder); 
        expresso_mail_archive.getFolderInfo(expresso_mail_archive.folder_destination); 
        expresso_mail_archive.listMessages(expresso_mail_archive.currentfolder);  
              
        expresso_mail_archive.resetObject();
    }
}

/**
 *Move Message Failure
 *
 *@author Cassiano Dal Pizzol [cassiano.dalpizzol@serpro.gov.br]
 *
 */
MailArchiver.prototype.moveMessagesOperationFailure = function(error_message){
    if(expresso_mail_archive.message_list.length > 1){
        write_msg(get_lang("Error moving messages.") + ' ' + error_message.getReturn());
    }else{
        write_msg(get_lang("Error moving message.") + ' ' + error_message.getReturn());
    } 
}


//getFolderList Operation
MailArchiver.prototype.getFoldersList = function(basefolder){   
    try{
        connector.showProgressBar();
        //window.alert('no expresso_mail_archive.getFoldersList com basefolder = ' + basefolder);
        if(basefolder == 'local_root')
            basefolder = "";
        //window.alert('folderlist com basefolder = ' + basefolder);
        //ArchiveServices.listFolders(expresso_mail_archive.getFoldersListOperationOK, expresso_mail_archive.getFoldersListOperationFailure, sessid, basefolder);
        ArchiveServices.listFolders(expresso_mail_archive.getFoldersListOperationOK, expresso_mail_archive.getFoldersListOperationFailure, expresso_mail_archive.session.id, basefolder);
        
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    } 
}

//getFolderList callback OK
MailArchiver.prototype.getFoldersListOperationOK = function(folderlist){
    //Internal variable used to map all XML data return from WS invokated
    var lfolders = new Array();

    
    //Mapping XML data to a handler data structure
    if(folderlist.getReturn().length > 0){
        var lfolders = new Array();
        for(i=0; i<folderlist.getReturn().length; i++){
            (folderlist.getReturn()[i].getFolderCount() > 0 ) ? folderChild = 1 : folderChild = 0;

            //Store folder data at this format: {folder name, number of messages contained, folder has child nodes, folder id, folder parent id, folder full path}
            //var folderData = new Array(folderlist.getReturn()[i].getName(), folderlist.getReturn()[i].getMessageCount(), folderChild, folderlist.getReturn()[i].getId(), folderlist.getReturn()[i].getParentId(), folderlist.getReturn()[i].getPath());
            var folderData = {name: folderlist.getReturn()[i].getName(), messages: folderlist.getReturn()[i].getMessageCount(), haschild: folderChild, id: folderlist.getReturn()[i].getId(), parentid: folderlist.getReturn()[i].getParentId(), path: folderlist.getReturn()[i].getPath(), unseen: folderlist.getReturn()[i].getUnseenCount()};
            //window.alert('folder ' + folderData.name + ' tem ' + folderData.messages + ' mensagens, sendo ' + folderData.unseen + ' n�o lidas');
            lfolders.push(folderData);
        }

        //Sets folders property at main object
        expresso_mail_archive.folders = lfolders;
        
        //Sets current folder, only if no one is setted
        //if(expresso_mail_archive.currentfolder == null){
            if(expresso_mail_archive.folders[0]["parentid"] == "home"){ // change from "" to "home"(12/12/2011)
                expresso_mail_archive.currentfolder = 'local_root';
            }
            else
                expresso_mail_archive.currentfolder = expresso_mail_archive.folders[0]["parentid"];
        //}
        
        
        //Folders dumping :)
        /*var strFolders = '---';
        for (var w=0; w < expresso_mail_archive.folders.length; w++){
            strFolders += '\nFolder ' + w + ' -->';
            for (x in expresso_mail_archive.folders[w]){
                strFolders += '\n----------' + x + ':= ' + expresso_mail_archive.folders[w][x];
            }
            strFolders += '\n<---';
        }
        strFolders += '\n---';
        alert(lfolders.length + ' folders mapeados, com default = ' + expresso_mail_archive.currentfolder + '\nfolders list:\n\n' + strFolders);
        */
        //window.alert('foldersListOperationOK com drawdata:\n' + expresso_mail_archive.drawdata);
        //If something UI related have been flagged, handle it
        if (expresso_mail_archive.drawdata){
            //window.alert('com drawdata e treeName = ' + expresso_mail_archive.drawdata.treeName);
            expresso_mail_archive.drawFolderTree();
        }
    } else {
        expresso_mail_archive.folders = 0;
    }
    connector.hideProgressBar();
}

//getFolderList callback Fail
MailArchiver.prototype.getFoldersListOperationFailure = function(error, http_msg){
    window.alert('List folders mistake:' + error + '\nhttp_error = ' + http_msg);
    connector.hideProgressBar();
}

//drawFolderTree operation, if requested to
MailArchiver.prototype.drawFolderTree = function(){  
    var localBaseName  = 'local_';
    var objectTree = expresso_mail_archive.drawdata.treeObject;
    var evalobjectTree = (typeof(expresso_mail_archive.drawdata.treeObject) == 'object') ? expresso_mail_archive.drawdata.treeObject: eval( '(' + expresso_mail_archive.drawdata.treeName + ')');
        
    if((expresso_mail_archive.drawdata.treeName == 'search_folders') && (Element('dftree_search_folders')))
        evalobjectTree = folders_tree;
    else{
        if((expresso_mail_archive.drawdata.treeName == 'folders_tree') && (Element('dftree_folders_tree')))
            evalobjectTree = folders_tree;
    }
        
    //Add nodes to tree    
    if(typeof(evalobjectTree) == 'object'){
        //window.alert('caiu pra dentro, com ' + expresso_mail_archive.folders.length + ' folders.');
        evalobjectTree._drawn = true;
        if(expresso_mail_archive.folders.length > 0){
            for(i=0; i<expresso_mail_archive.folders.length; i++){
                var folder_caption;
                //window.alert('iterando nodo ' + expresso_mail_archive.folders[i]["name"]);
            
                //Special folders treatment: Inbox, Outbox, Drafts, Sent and Trash folders               
                if((expresso_mail_archive.folders[i]["name"] == 'Inbox') || (expresso_mail_archive.folders[i]["name"] == 'Outbox') || (expresso_mail_archive.folders[i]["name"] == 'Drafts') || (expresso_mail_archive.folders[i]["name"] == 'Sent') || (expresso_mail_archive.folders[i]["name"] == 'Trash')){
                    folder_caption = get_lang(expresso_mail_archive.folders[i]["name"]);
                }
                else{
                    folder_caption = expresso_mail_archive.folders[i]["name"];
                }
                
                var onClickVar = '';                               
                if((evalobjectTree.name != "folders_tree") && (evalobjectTree.name != "search_folders")){
                    onClickVar =  "change_folder('"+ localBaseName + expresso_mail_archive.folders[i]["id"] +"','"+ localBaseName + expresso_mail_archive.folders[i]["id"]+"', '" + expresso_mail_archive.drawdata.treeName + "')";
                    if(expresso_mail_archive.folders[i]["unseen"] != "0")
                        folder_caption = folder_caption + '<font style=color:red>&nbsp(</font><span id="dftree_local_'+expresso_mail_archive.folders[i]["id"]+'_unseen" style=color:red>'+expresso_mail_archive.folders[i]["unseen"]+'</span><font style=color:red>)</font>'
                }
     
                var n_demo = new dNode({id: localBaseName + expresso_mail_archive.folders[i]["id"], caption: folder_caption, onClick: onClickVar, plusSign:expresso_mail_archive.folders[i]["haschild"]});
                //var n_demo = new dNode({id: localBaseName + expresso_mail_archive.folders[i]["name"], caption: folder_caption, onClick: "change_folder('"+ localBaseName + expresso_mail_archive.folders[i]["name"] +"','"+ localBaseName + expresso_mail_archive.folders[i]["name"]+"', '" + expresso_mail_archive.drawdata.treeName + "')", plusSign:expresso_mail_archive.folders[i]["haschild"]});
            
                //Adjust the id node names
                if(expresso_mail_archive.currentfolder){
                    if(expresso_mail_archive.currentfolder.substr(0,5) != 'local'){
                        evalobjectTree.add(n_demo, localBaseName + expresso_mail_archive.currentfolder);
                    }
                    else{
                        evalobjectTree.add(n_demo,expresso_mail_archive.currentfolder);
                    }
                }
                else {
                    expresso_mail_archive.currentfolder = 'local_root';
                    evalobjectTree.add(n_demo,expresso_mail_archive.currentfolder);
                }            
            }
        }
        //Set special folders icons   
        if (document.getElementById('llocal_senttree_folders')){
            document.getElementById('llocal_senttree_folders').style.backgroundImage="url(../phpgwapi/templates/"+template+"/images/foldertree_sent.png)";
        }
        if (document.getElementById('llocal_trashtree_folders')){
            document.getElementById('llocal_trashtree_folders').style.backgroundImage="url(../phpgwapi/templates/"+template+"/images/foldertree_trash.png)";
        }
        if (document.getElementById('llocal_draftstree_folders')){
            document.getElementById('llocal_draftstree_folders').style.backgroundImage="url(../phpgwapi/templates/"+template+"/images/foldertree_draft.png)";
        }
        if (document.getElementById('llocal_outboxtree_folders')){
            document.getElementById('llocal_outboxtree_folders').style.backgroundImage="url(../phpgwapi/templates/"+template+"/images/foldertree_sent.png)";
        }         
    }
    else {
        //does nothing
    }
}

//getFaultInfo operation
MailArchiver.prototype.getFaultInfo = function(){
    try{
        //ArchiveServices.getFaultInfo(expresso_mail_archive.getFaultInfoOperationOK, expresso_mail_archive.getFaultInfoOperationFailure, sessid);
         ArchiveServices.getFaultInfo(expresso_mail_archive.getFaultInfoOperationOK, expresso_mail_archive.getFaultInfoOperationFailure, expresso_mail_archive.session.id);
         connector.hideProgressBar();
    }    
    catch(e){
        if (!expresso_mail_archive.enabled)
            write_msg(get_lang('MailArchiver does not seems to be running or installed at this workstation, local messages are disabled. Check it out!'),false);
        else{
            window.clearInterval(expresso_mail_archive.timer);
            write_msg(get_lang('There is something wrong with MailArchiver environment. Contact you support'), false);
            expresso_mail_archive.enabled = false;
            expresso_mail_archive.turnOffLocalTreeStructure();
        }
        connector.hideProgressBar();
    }
}

//getFaultInfo callback OK
MailArchiver.prototype.getFaultInfoOperationOK = function(faultinfo){
    try{
        write_msg(get_lang('MailArchiver remote service reports the following error:', false) + faultinfo.getReturn().getSoapFaultString());
    } catch(e){
        write_msg(get_lang('The archive service reports a unknown error. Try to refresh your browser screen', false));
    }
    expresso_mail_archive.resetObject();
    expresso_mail_archive.archivefolder = null;
    window.setTimeout("eval('document.getElementById(\"main_title\").innerHTML =\"Expresso Mail\"')",3000);        
    connector.purgeCache();
    expresso_mail_archive.turnOffLocalTreeStructure();
}

//getFaultInfo callback Fail
MailArchiver.prototype.getFaultInfoOperationFailure = function(errorCode, errorDesc){
    try{
        write_msg(get_lang('SoapFault capture fails at:' + errorCode + ' | ' + errorDesc, false));
    } catch(e){
        write_msg(get_lang('Service error mapping', false));
    }    
}

//createFolder operation
MailArchiver.prototype.createFolder = function(parentFolder, folderName){
    if(typeof(folderName) == "undefined"){
        return false;
    }
    
    try{
        //ArchiveServices.createFolder(expresso_mail_archive.createFolderOperationOK, expresso_mail_archive.createFolderOperationFailure, sessid, parentFolder, folderName);
        ArchiveServices.createFolder(expresso_mail_archive.createFolderOperationOK, expresso_mail_archive.createFolderOperationFailure, expresso_mail_archive.session.id, parentFolder, folderName);
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    } 
    
}

//createFolder callback OK
MailArchiver.prototype.createFolderOperationOK = function (folderObject){
    //window.alert('callback de createfolderoperationok');
    draw_tree_local_folders();
    //expresso_mail_archive.drawdata.treeObject._drawn = true;
    //ttreeBox.update_folder();    
}

//createFolder callback Fail
MailArchiver.prototype.createFolderOperationFailure = function (message){
    window.alert('Folder creation fails...\n->' + message.getReturn()); 
}

//deleteFolder operation
MailArchiver.prototype.deleteFolder = function(folderId, folderName){
    try{expresso_mail_archive.drawdata.folderName = folderName;
        ArchiveServices.deleteFolder(expresso_mail_archive.deleteFolderOperationOK, expresso_mail_archive.deleteFolderOperationFailure, expresso_mail_archive.session.id, folderId, true);
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    } 
    
}

//deleteFolder callback OK
MailArchiver.prototype.deleteFolderOperationOK = function (folderObject){
    write_msg(get_lang("The local folder %1 was successfully removed", expresso_mail_archive.drawdata.folderName));
    connector.purgeCache();
    //ttreeBox.name_folder = "root";
    //ttreeBox.update_folder();
    draw_tree_local_folders();
    //ttreeBox.update_folder();	
}

//deleteFolder callback Fail
MailArchiver.prototype.deleteFolderOperationFailure = function (message){
    expresso_mail_archive.getFaultInfo();
}

//renameFolder operation
MailArchiver.prototype.renameFolder = function(folderId, newFolderName){
    try{
        //ArchiveServices.renameFolder(expresso_mail_archive.renameFolderOperationOK, expresso_mail_archive.renameFolderOperationFailure, sessid, folderId, newFolderName);
        ArchiveServices.renameFolder(expresso_mail_archive.renameFolderOperationOK, expresso_mail_archive.renameFolderOperationFailure, expresso_mail_archive.session.id, folderId, newFolderName);
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    } 
    
}

//renameFolder callback OK
MailArchiver.prototype.renameFolderOperationOK = function (returnService){
    var evalobjectTree = eval(expresso_mail_archive.drawdata.treeName);
    evalobjectTree.update_folder();
}

//renameFolder callback Fail
MailArchiver.prototype.renameFolderOperationFailure = function (ServiceFault){
    expresso_mail_archive.getFaultInfo();
}

//getFolderInfo operation
MailArchiver.prototype.getFolderInfo = function (folderId){
    try{
        if(folderId == 'local_root')
            folderId = "";
        var exp_verifyId = RegExp("^messages\_[0-9|a-z]+\-[0-9|a-z|\-]+$");
        if(exp_verifyId.test(folderId))
            folderId = folderId.replace("messages_", "");
        ArchiveServices.getFolderInfo(expresso_mail_archive.getFolderInfoOperationOK, expresso_mail_archive.getFolderInfoOperationFailure, expresso_mail_archive.session.id, folderId);
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    }     
}

//getFolderInfo callback OK
MailArchiver.prototype.getFolderInfoOperationOK = function(returnService){
   var folder_info = {id : returnService.getReturn().getId(), name : returnService.getReturn().getName(), parent : returnService.getReturn().getParentId(), numfolders: returnService.getReturn().getFolderCount(), nummessages: returnService.getReturn().getMessageCount(), unseen: returnService.getReturn().getUnseenCount(), path: returnService.getReturn().getPath()};
   expresso_mail_archive.folder = folder_info;
   expresso_mail_archive.updateCounter();
}

//getFolderInfo callback Fail
MailArchiver.prototype.getFolderInfoOperationFailure = function (ServiceFault){
    window.alert('getFolderInfo service fails...\n->' + ServiceFault.getReturn()); 
    expresso_mail_archive.folder_data = true;
}

function foo(){
    var a = 0;
    a++;
}


/**
 *delete All Messages
 *
 *@author Thiago Rossetto Afonso [thiago@prognus.com.br]
 *
 *@param folderId - folder id to get all messages that it has
 */

MailArchiver.prototype.deleteAllMessages = function(folderId){
    try{    
        var objfolder = new Object();
        objfolder.folder = folderId; 

        var query_data = expresso_mail_archive.queryconfig.query(objfolder);

        ArchiveServices.listMessages(expresso_mail_archive.deleteMsgsOperationOK, expresso_mail_archive.deleteMsgsOperationFailure, expresso_mail_archive.session.id, query_data);
    }catch(e){
        expresso_mail_archive.getFaultInfo();
    }
}
MailArchiver.prototype.deleteMsgsOperationOK = function(returnService){
    var msgsArray = new Array();
    var msgs = "";
    for(var i=0; i< returnService.getReturn().length; i++){
        msgsArray.push(returnService.getReturn()[i]._id);    
    }  
    msgs = msgsArray.join(",");

    MailArchiver.prototype.deleteMessages(msgs);
}
MailArchiver.prototype.deleteMsgsOperationFailure = function(){
    alert("Your Messages weren't deleted.");
}

//listMessages operation
//OLD local_messages.prototype.get_local_range_msgs = function(folder,msg_range_begin,emails_per_page,sort,sort_reverse,search,preview_msg_subject,preview_msg_tip) {
//ONLINE messages_proxy.prototype.messages_list = function(folder,msg_range_begin,emails_per_page,sort_box_type,search_box_type,sort_box_reverse,preview_msg_subject,preview_msg_tip,call_back,tree_name) {
//sys call = proxy_mensagens.messages_list(current_folder,1,preferences.max_email_per_page,sort,search,sort_box_reverse,preferences.preview_msg_subject,preferences.preview_msg_tip,handler_draw_box);
MailArchiver.prototype.listMessages = function(folderId){
    try{
        connector.showProgressBar();
        var getcurrent = get_current_folder();       
        //tree_folders.getNodeById(get_current_folder())._select();
        var folderid;
        if(typeof(folderId) != "undefined"){
            folderid = folderId;
        } else {
            folderid = this.currentfolder;    
        }
        var objfolder = new Object();
        var exp_verifyId = RegExp("^messages\_[0-9|a-z]+\-[0-9|a-z|\-]+$");
        if(exp_verifyId.test(folderid))
            folderid = folderid.replace("messages_", "");
        objfolder.folder = folderid;
        //var testing_data_xml = '<?xml version="1.0" encoding="UTF-8"?><query lowerIndex="0" upperIndex="50"><folder id="'+folderid+'"/><order asc="date"/><order desc="subject"/></query>';
        //var testing_data_json_mapped = '{"query":{"@lowerIndex":"0", "@upperIndex":"50", "folder":[{"@id":"'+folderid+'"}], "order":[{"@asc":"date", "@desc":"subject"}]}}';
        var query_data = expresso_mail_archive.queryconfig.query(objfolder);

        expresso_mail_archive.getFolderInfo(folderid); 
        expresso_mail_archive.currentfolder = folderid;
        

        //ArchiveServices.listMessages(expresso_mail_archive.listMessagesOperationOK, expresso_mail_archive.listMessagesOperationFailure, sessid, query_data);
        ArchiveServices.listMessages(expresso_mail_archive.listMessagesOperationOK, expresso_mail_archive.listMessagesOperationFailure, expresso_mail_archive.session.id, query_data);
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    }
}

//listMessages callback OK
MailArchiver.prototype.listMessagesOperationOK = function(returnService){
    var msglist = new Array();
    expresso_mail_archive.ServiceReturnObjectList = new Array();
    //window.alert('messages data\n\n' + print_r(returnService.getReturn().length));
    /*window.alert('Dados da mensagem:\n->From ' + returnService.getReturn()[0].getFrom()
                                      +'\n->To ' + returnService.getReturn()[0].getTo()
                                      +'\n->Subject ' + returnService.getReturn()[0].getSubject()
                                      +'\n->Date ' + returnService.getReturn()[0].getDate()
                                      +'\n->Date ' + returnService.getReturn()[0].getSize()
                                      +'\n->ID ' + returnService.getReturn()[0].getId());
    */
    for(var i=0; i< returnService.getReturn().length; i++){
        var ExpSerialized = expresso_mail_archive.pattern.toExpressoHeader(returnService.getReturn()[i]);
        var msgitem = connector.unserialize(ExpSerialized);
        
        // correção para por no padrao certo, se nao fizer isso buga a listagem
        msgitem.msg_sample = { "body": msgitem.msg_sample };

        msglist.push(msgitem);//usando connector.unserialize sobre o header mapeado no layout abaixo
        expresso_mail_archive.ServiceReturnObjectList.push(returnService.getReturn()[i]); //add each one service return data into this embeded object array
    }
    //msglist["num_msgs"] = returnService.getReturn().length;
    //window.alert('service return length = ' + returnService.getReturn().length + '\nfolder.nummessages = ' + expresso_mail_archive.folder.nummessages);
    
    //window.alert('folder counters:\n' + print_r(tab_counters));

    //msglist["num_msgs"] = expresso_mail_archive.folder.nummessages;
    //window.alert('num msgs');
    //(expresso_mail_archive.folder.unseen) ? msglist["tot_unseen"] = expresso_mail_archive.folder.unseen : msglist["tot_unseen"] = 0;
//    window.alert('num unseen');
    //window.alert('print_r\n\n' + print_r(msglist));
   
    //expresso_mail_archive.updateCounter(returnService.getReturn().length, msg_unseen_count);    
    expresso_mail_archive.messageslisted = msglist;
    expresso_mail_archive.drawdata = {messagesList:msglist};
    expresso_mail_archive.drawMessagesList();
    //expresso_mail_archive.drawMessagesList(returnService.getReturn().length, msg_unseen_count);
    //expresso_mail_archive.drawMessagesList(msglist["num_msgs"], msg_unseen_count);
    //expresso_mail_archive.drawMessagesList(expresso_mail_archive.tot_msgs_tab, expresso_mail_archive.tot_unseen_msgs_tab);
    connector.hideProgressBar();
}

//listMessages callback Fail
MailArchiver.prototype.listMessagesOperationFailure = function(ServiceFault){
    window.alert('listmessages FALHOU!\n' + ServiceFault.getReturn());
}


MailArchiver.prototype.getMessagesByFolder = function(folderid, searchType){
    try{
        if(searchType == "ALL")
            var query_messages = '{"query":{"folder":[{"@id":"'+folderid+'"}], "order":[{"@asc":"date"}]}}';
        else
            var query_messages = '{"query":{"folder":[{"@id":"'+folderid+'"}], "tags":[{"@contains":"'+searchType+'"}], "order":[{"@asc":"date"}]}}';
        ArchiveServices.listMessages(expresso_mail_archive.getMessagesByFolderOperationOK, expresso_mail_archive.getMessagesByFolderOperationFailure, expresso_mail_archive.session.id, query_messages);
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    }
}

MailArchiver.prototype.getMessagesByFolderOperationOK = function(returnService){
    var msglist = new Array();
    var msglistcomplete = new Array();
    var msgAll = new Array();
    for(var i=0; i< returnService.getReturn().length; i++){
        //Array montado apenas com ids para tratamento de seleção de mensagens independente de paginação
        msglist.push(returnService.getReturn()[i]._id);

        msgAll.push(returnService.getReturn()[i]);

        //Incremento de contadores para atualização da aba de listagem
        if(returnService.getReturn()[i]._tags.indexOf("unseen") != -1){
            expresso_mail_archive.tot_unseen_msgs_tab++;
        }
        expresso_mail_archive.tot_msgs_tab++;
        //Array montado para operação de desarquivamento
        var ExpSerialized = expresso_mail_archive.pattern.toExpressoHeader(returnService.getReturn()[i]);
        msglistcomplete.push(connector.unserialize(ExpSerialized));
    }
    expresso_mail_archive.allmessagesbyfolder = msglist;
    expresso_mail_archive.allcompletemessagesbyfolder = msglistcomplete;
    expresso_mail_archive.msgAll = msgAll;
}

MailArchiver.prototype.getMessagesByFolderOperationFailure = function(ServiceFault){
    window.alert('listAllmessagesByFolder FALHOU!\n' + ServiceFault.getReturn());
}


//List all messages by folder given and set a array with all messages ids
MailArchiver.prototype.listAllMessagesByFolder = function(folderid, searchType){
    try{
        if(searchType == "ALL")
            var query_messages = '{"query":{"folder":[{"@id":"'+folderid+'"}], "order":[{"@asc":"date"}]}}';
        else
            var query_messages = '{"query":{"folder":[{"@id":"'+folderid+'"}], "tags":[{"@contains":"'+searchType+'"}], "order":[{"@asc":"date"}]}}';
        ArchiveServices.listMessages(expresso_mail_archive.listAllMessagesByFolderOperationOK, expresso_mail_archive.listAllMessagesByFolderOperationFailure, expresso_mail_archive.session.id, query_messages);
    }
    catch (e){
        expresso_mail_archive.getFaultInfo();
    }
}

//listAllMessagesByFolder callback OK
MailArchiver.prototype.listAllMessagesByFolderOperationOK = function(returnService){
    var msglist = new Array();
    var msglistcomplete = new Array();
    for(var i=0; i< returnService.getReturn().length; i++){
        //Array montado apenas com ids para tratamento de seleção de mensagens independente de paginação
        msglist.push(returnService.getReturn()[i]._id);
        //Incremento de contadores para atualização da aba de listagem
        if(returnService.getReturn()[i]._tags.indexOf("unseen") != -1){
            expresso_mail_archive.tot_unseen_msgs_tab++;
        }
        expresso_mail_archive.tot_msgs_tab++;
        //Array montado para operação de desarquivamento
        var ExpSerialized = expresso_mail_archive.pattern.toExpressoHeader(returnService.getReturn()[i]);
        msglistcomplete.push(connector.unserialize(ExpSerialized));
    }
    expresso_mail_archive.allmessagesbyfolder = msglist;
    expresso_mail_archive.allcompletemessagesbyfolder = msglistcomplete;
}

//listAllMessagesByFolder callback Fail
MailArchiver.prototype.listAllMessagesByFolderOperationFailure = function(ServiceFault){
    window.alert('listAllmessagesByFolder FALHOU!\n' + ServiceFault.getReturn());
}

MailArchiver.prototype.getFolderMessagesNumber = function(){
    var n = 0;
    for(var i=0; i < expresso_mail_archive.folders.length; i++){
        if(expresso_mail_archive.folders[i]["id"] == expresso_mail_archive.currentfolder){
            n = expresso_mail_archive.folders[i]["messages"];
        }
    }
    return(n);
}

MailArchiver.prototype.drawMessagesList = function(){
    //window.alert('no drawMessagesList com folder = ' + expresso_mail_archive.currentfolder  + '\nudatecounters = ' + expresso_mail_archive.udatecounters);
    var data_to_draw = expresso_mail_archive.drawdata.messagesList;  
    //window.alert('data to draw =  ' + data_to_draw.length + '\ndata_to_draw[0].from.full = ' + print_r(data_to_draw));
    //window.alert('Current folder = ' + expresso_mail_archive.currentfolder + ' com ' + expresso_mail_archive.folders.length + ' folders.');
    var fcaption = expresso_mail_archive.currentfolder;
    
    /*for (var w=0; w < expresso_mail_archive.folders.length; w++){
        //window.alert('comparando folder "' + expresso_mail_archive.folders[w]["id"] + '" com o corrente "' + expresso_mail_archive.currentfolder + '"\nEnquanto que o folder.name obtido do getfolderinfo é "' + expresso_mail_archive.folder.name + '"');
        if(expresso_mail_archive.folders[w]["id"] == expresso_mail_archive.currentfolder){
            fcaption = expresso_mail_archive.folders[w]["name"];
            window.alert('folder found!\n -> ' + fcaption);
            break;
        }
    }*/
    
    var fcaption = expresso_mail_archive.folder.name;
    
    /*window.alert('fcaption = ' + fcaption);
    var udate_tree_folder = expresso_mail_archive.drawdata.treefolder;
    window.alert('atualizar contador da �rvore : ' + udate_tree_folder);
    var udate_tab_folder = expresso_mail_archive.drawdata.tabfolder;
    window.alert('atualizar contador da tab : ' + udate_tab_folder);*/
    //var folder_num_msgs = expresso_mail_archive.getFolderMessagesNumber();
    var folder_num_msgs = expresso_mail_archive.folder.nummessages;
    //window.alert('num = ' + folder_num_msgs);

    Element("border_id_0").innerHTML = "&nbsp;" + lang_folder(fcaption) + '&nbsp;<font face="Verdana" size="1" color="#505050">[<span id="new_m">&nbsp;</span> / <span id="tot_m"></span>]</font>';
    draw_box(data_to_draw, 'local_' + expresso_mail_archive.currentfolder, true);
    //draw_paging(expresso_mail_archive.drawdata.messagesList.length);
    //draw_paging(preferences.max_email_per_page);
    draw_paging(expresso_mail_archive.tot_msgs_tab);    
    Element("tot_m").innerHTML = expresso_mail_archive.tot_msgs_tab; //folder_num_msgs;
    Element('new_m').innerHTML = '<font color="RED">'+expresso_mail_archive.tot_unseen_msgs_tab+'</font>';
}

MailArchiver.prototype.getMessageHeaders = function(msgId){
    var msgfound = false;
    var headerMsg;
        
    //Get message header info, from message list operation previously invoked
    for (var w=0; w < expresso_mail_archive.messageslisted.length; w++){
        if(expresso_mail_archive.messageslisted[w]["msg_number"] == msgId){
            headerMsg = expresso_mail_archive.messageslisted[w];
            msgfound = true;
            break;
        }
    }
    
    //Message does not exists at default message list previouslly invoked. Then, will be tryed to get it at possible search criteria existing tab.
    if(!msgfound){
        //Try to discover if desired message is at a search local data tab
        if(currentTab.toString().indexOf("search_local") != -1){
	    var msgId2 = msgId.substr(0,msgId.indexOf("_s"));            
            for (var w=0; w < expresso_mail_archive.search_message_list.length; w++){
                if(expresso_mail_archive.search_message_list[w]["msg_number"] == msgId2){
                    headerMsg = expresso_mail_archive.search_message_list[w];
                    msgfound = true;
                    break;
                }
            }
       
        }
    }
    
    //The message was found, as well the her headers
    if(msgfound){
        expresso_mail_archive.currentheaders = headerMsg;
        expresso_mail_archive.subjectPreview = headerMsg.subject;
        return(headerMsg);
    }
    //The message was not found   
    else{
        headerMsg = null;
        expresso_mail_archive.currentheaders = headerMsg;        
        return;
    }
}

MailArchiver.prototype.getMessageBodies = function(msgdata){
    try{
        var temp;
        var bodies = Array();
        var callback = function(data){
            temp = data;
        };
        for(var i=0; i<msgdata.length; i++){
            expresso_mail_archive.currentmessage = expresso_mail_archive.getMessageHeaders(msgdata[i])["msg_number"];
            ArchiveServices.getMessageBody(callback, expresso_mail_archive.getMessageOperationFailure,expresso_mail_archive.session.id, expresso_mail_archive.currentmessage);
            bodies.push(temp);
        }
        expresso_mail_archive.bodyPreview = temp.getReturn();
        return bodies;
    }
    catch(e){
        expresso_mail_archive.getFaultInfo();
    }
}


MailArchiver.prototype.getPreviewToAttach = function(id){
    expresso_mail_archive.getMessageInfo(id);
    expresso_mail_archive.getMessageBodies([id]);
}

MailArchiver.prototype.getSomeMsgs = function(msgIds){
    try{
       expresso_mail_archive.getSomeMsg = new Array();
       for(var i=0; i < msgIds.length; i++){
           expresso_mail_archive.getSomeMsg.push(expresso_mail_archive.getMessageHeaders(msgIds[i]));
       }
       
   }catch(e){
        alert("Erro ao obter mensagens");
   }
} 


//** Esse metodo é responsavel da iteração com o ContextMenu pois o getMessageMenu está com um show_msg, wtf??? LOL!!!  *//
MailArchiver.prototype.getMessageMenu = function(msgdata){
    try{
        if((msgdata.length <= 0) || (msgdata == null)){
            return;
        }
        expresso_mail_archive.currentmessage = expresso_mail_archive.getMessageHeaders(msgdata)["msg_number"];
        ArchiveServices.getMessageBody(expresso_mail_archive.getMessageMenuOperationOK, expresso_mail_archive.getMessageMenuOperationFailure, expresso_mail_archive.session.id, expresso_mail_archive.currentmessage);
    }
    catch(e){
        expresso_mail_archive.getFaultInfo();
    }
}

MailArchiver.prototype.getMessageMenuOperationOK = function(returnService){    
    if(returnService.getReturn().length <= 0){
        window.alert('Oh no: service return data is zero length...');
        return;
    }    
    var msgBody = returnService.getReturn();
    //var msgHeaders = expresso_mail_archive.getMessageHeaders(expresso_mail_archive.currentmessage);
    var msgHeaders = expresso_mail_archive.currentheaders;
    var expSerializedMessage = expresso_mail_archive.pattern.toExpressoMail(msgHeaders, msgBody);
    
    // Unset \\Unseen flag
    for (i=0; i < expresso_mail_archive.messageslisted.length; i++)
    {
        if (expresso_mail_archive.messageslisted[i].msg_number == expresso_mail_archive.currentmessage)
        {
            expresso_mail_archive.messageslisted[i]['Unseen'] = '';
        }
    }
    
    //window.alert('Serialized data\n\n' + print_r(expSerializedMessage));
    //expresso_mail_archive.showEmbededImage(msgBody);
    
    expresso_mail_archive.fromMenu = expSerializedMessage;
}
MailArchiver.prototype.getMessageMenuOperationFailure = function(ServiceFault){
    window.alert('Message fails do be loaded.');
}


MailArchiver.prototype.getMessage = function(msgdata){
    try{
        if((msgdata.length <= 0) || (msgdata == null)){
            return;
        }
        expresso_mail_archive.currentmessage = expresso_mail_archive.getMessageHeaders(msgdata)["msg_number"];
        ArchiveServices.getMessageBody(expresso_mail_archive.getMessageOperationOK, expresso_mail_archive.getMessageOperationFailure, expresso_mail_archive.session.id, expresso_mail_archive.currentmessage);
    }
    catch(e){
        expresso_mail_archive.getFaultInfo();
    }
}

MailArchiver.prototype.getMessageOperationOK = function(returnService){    
    if(returnService.getReturn().length <= 0){
        window.alert('Oh no: service return data is zero length...');
        return;
    }    
    expresso_mail_archive.te = returnService.getReturn();
    var msgBody = returnService.getReturn();
    //var msgHeaders = expresso_mail_archive.getMessageHeaders(expresso_mail_archive.currentmessage);
    var msgHeaders = expresso_mail_archive.currentheaders;
    var expSerializedMessage = expresso_mail_archive.pattern.toExpressoMail(msgHeaders, msgBody);
    
    // Unset \\Unseen flag
    for (i=0; i < expresso_mail_archive.messageslisted.length; i++)
    {
        if (expresso_mail_archive.messageslisted[i].msg_number == expresso_mail_archive.currentmessage)
        {
            expresso_mail_archive.messageslisted[i]['Unseen'] = '';
        }
    }
    
    //window.alert('Serialized data\n\n' + print_r(expSerializedMessage));
    //expresso_mail_archive.showEmbededImage(msgBody);

    //� necess�rio fazer o encode com Base64 no destinat�rio para ser possivel enviar e-mail
    if(expSerializedMessage.DispositionNotificationTo != null){
        expSerializedMessage.DispositionNotificationTo = Base64.encode(expSerializedMessage.DispositionNotificationTo);
    }
    
    show_msg(expSerializedMessage);    
    window.setTimeout("expresso_mail_archive.setEmbeddedLink()", 1);
}
MailArchiver.prototype.getMessageOperationFailure = function(ServiceFault){
    window.alert('Message fails do be loaded.');
}

/*
 *getMessageServiceObjet: search for a remote webservice object in memory to use
 *draw interface. Look at messages list first, then, search list. Returns desired
 *object, or null (if not found)
 **/
MailArchiver.prototype.getMessageServiceObject = function(msgid){
    var flag_found = false;
    var rtn_obj = null;
    
    //Message list
    if(expresso_mail_archive.ServiceReturnObjectList != null){
        for(var i=0; i < expresso_mail_archive.ServiceReturnObjectList.length; i++){
            if (expresso_mail_archive.ServiceReturnObjectList[i].getId() == msgid){
                flag_found = true;
                rtn_obj = expresso_mail_archive.ServiceReturnObjectList[i];
            }
        }
    }
       
    //Search list
    if(!flag_found){
        if(expresso_mail_archive.ServiceReturnObjectSearch != null){
            for(var i=0; i < expresso_mail_archive.ServiceReturnObjectSearch.length; i++){
                if (expresso_mail_archive.ServiceReturnObjectSearch[i].getId() == msgid){
                    flag_found = true;
                    rtn_obj = expresso_mail_archive.ServiceReturnObjectSearch[i];
                }
            }
        }        
    }
    return(rtn_obj);
}


MailArchiver.prototype.tagMessage = function(taglist){
    try{
        if(!taglist)
            taglist = expresso_mail_archive.taglist;
        //ArchiveServices.tagMessages(expresso_mail_archive.tagMessagesOperationOK, expresso_mail_archive.tagMessagesOperationFailure, sessid, taglist);
        ArchiveServices.tagMessages(expresso_mail_archive.tagMessagesOperationOK, expresso_mail_archive.tagMessagesOperationFailure, expresso_mail_archive.session.id, taglist);
    } catch (e){
        //window.alert('TagMessage fails at: ' + (e.description)?e.description:e);
        expresso_mail_archive.getFaultInfo();
    }     
}

MailArchiver.prototype.tagMessagesOperationOK = function(serviceData){
    //ajustar os contadores de lida/nao lida etc...
    if((expresso_mail_archive.currenttag != '') && (expresso_mail_archive.tagmsg == true)){
        write_msg(get_lang('Messages marked as "%1"', get_lang(expresso_mail_archive.currenttag)));
        expresso_mail_archive.tagmsg = false;
    }
    
    expresso_mail_archive.currenttag = "";
    expresso_mail_archive.taglist = "";

    if((expresso_mail_archive.drawdata) && (get_current_folder() == expresso_mail_archive.folder_origin)){
        expresso_mail_archive.drawFolderTree();
        //expresso_mail_archive.drawdata = null;
        window.clearTimeout(expresso_mail_archive.progressbar);
    }
    if(!expresso_mail_archive.isArchiveOperation){
        expresso_mail_archive.listMessages();
    }
}

MailArchiver.prototype.tagMessagesOperationFailure = function(serviceData){
    var str_tag_fail = serviceData.getReturn();
    expresso_mail_archive.getFaultInfo();
}

MailArchiver.prototype.download_all_msg_attachments = function(msgid){
    var default_format = 'zip'; //tar, jar, gzip, bz2 supports too
    var url = expresso_mail_archive.pattern.download_compressed_attachments(msgid, default_format);
    //window.open(url,"mywindow","width=1,height=1,scrollbars=no");
    location.href = url;
}

MailArchiver.prototype.download_msg_source = function (format){
    //Default export format is zip
    if(!format)
        format = 'zip';
    
    expresso_mail_archive.exportformat = format;
    
    if (openTab.type[currentTab] > 1){
	var msgs_id = currentTab.substring(0,currentTab.length-2,currentTab);
    }else{
	var msgs_id = get_selected_messages();
    }    
    
    var vetmsg = msgs_id.split(",");
    if(vetmsg.length > 0){
        var arr_msg = new Array();
        for(var k=0; k<vetmsg.length; k++){
            arr_msg.push(vetmsg[k]);
        }            
    }
    else var arr_msg = new Array(msgs_id);
   
    var messages_array = {"format": format, "type": 'messages', "messages": arr_msg};
    var texp = expresso_mail_archive.pattern.zipConfig(messages_array);
    
    if(texp != null){
        try{
            //ArchiveServices.zipMessages(expresso_mail_archive.download_mgs_sourceOperationOK, expresso_mail_archive.download_msg_sourceOperationFailure, sessid, texp);
            ArchiveServices.zipMessages(expresso_mail_archive.download_mgs_sourceOperationOK, expresso_mail_archive.download_msg_sourceOperationFailure, expresso_mail_archive.session.id, texp);
        }
        catch(e){
            //window.alert('Export local messages fails: ' + (e.description)?e.description:e);
            expresso_mail_archive.getFaultInfo();
        }
    }
}

MailArchiver.prototype.download_mgs_sourceOperationOK = function(serviceData){
  if(serviceData.getReturn().length > 0){
      window.location.href = mail_archive_protocol + "://" + mail_archive_host + ":" + mail_archive_port + "/temp/mails_" + serviceData.getReturn() + '.' + expresso_mail_archive.exportformat;
  }
  else{
      
  }
}

MailArchiver.prototype.download_mgs_sourceOperationFailure = function(serviceFail){
  window.alert('ZipMessages FAIL:' + serviceFail.getReturn());  
}

MailArchiver.prototype.export_local_messages = function(folderid, recursive, format){
    //Default export format is zip
    if(!format)
        format = 'zip';
    
    expresso_mail_archive.exportformat = format;    
    if(folderid == 'root')//export root local folder
        var messages_array = {"format": format, "type": 'folder', "recursive" : true, "messages": ""};
    else//export local folder entry
        var messages_array = {"format": format, "type": 'folder', "recursive" : recursive, "messages": folderid};
    var texp = expresso_mail_archive.pattern.zipConfig(messages_array);
    
    if(texp != null){
        try{
            ArchiveServices.zipMessages(expresso_mail_archive.download_mgs_sourceOperationOK, expresso_mail_archive.download_msg_sourceOperationFailure, expresso_mail_archive.session.id, texp);
        }
        catch(e){
            //window.alert('Export local messages fails: ' + (e.description)?e.description:e);
            expresso_mail_archive.getFaultInfo();
        }
    }    
}

MailArchiver.prototype.updateCounter = function(a,b){
    //window.alert('updateCounter reached');

    if(((a) && (b)) || ((parseInt(a)==0)&&(parseInt(b)==0))){
        //Element("tot_m").innerHTML = a;
        Element('new_m').innerHTML = (b >= 0) ? '<font color="RED">'+b+'</font>' : 0; 
        return;
    }
    
    if(expresso_mail_archive.update_counters == true){
        connector.purgeCache();
        var elm_tree = Element('llocal_'+expresso_mail_archive.folder.id+'tree_folders');
        
        if(elm_tree){
        
        //search appropriate "unseen" span to handle with
        for(var j=0; j<elm_tree.childNodes.length; j++){
            if (elm_tree.childNodes[j].nodeName.toLowerCase() == 'span'){
                var elm = elm_tree.childNodes[j];
                break;
            }        
        }
    
        if(expresso_mail_archive.folder.unseen > 0){
            if (elm){
                elm.innerHTML = expresso_mail_archive.folder.unseen;
            }                
            else{
                var htm_el = document.createElement('font');
                htm_el.style.color = 'red';
                document.getElementById('llocal_'+expresso_mail_archive.folder.id+'tree_folders').appendChild(htm_el);    
                htm_el.innerHTML = '&nbsp(';
                
                var spn_el = document.createElement('span');
                spn_el.id = 'dftree_'+expresso_mail_archive.folder.id+'_unseen';
                spn_el.style.color = 'red';
                document.getElementById('llocal_'+expresso_mail_archive.folder.id+'tree_folders').appendChild(spn_el);
                spn_el.innerHTML = expresso_mail_archive.folder.unseen;
                
                var htm2_el = document.createElement('font');
                htm2_el.style.color = 'red';
                document.getElementById('llocal_'+expresso_mail_archive.folder.id+'tree_folders').appendChild(htm2_el);    
                htm2_el.innerHTML = ')';
            }
        }
        else{
            if(elm){
                var spn = elm.parentNode.parentNode;
                elm.parentNode.removeChild(elm.previousSibling);
                elm.parentNode.removeChild(elm.nextSibling);
                elm.parentNode.removeChild(elm);
            }
        }

        //reset pointer to re-do working to move messages (2 folders envolved)
        if (expresso_mail_archive.folder_destination != null){
            if(expresso_mail_archive.currentfolder == expresso_mail_archive.folder_destination){
                expresso_mail_archive.update_counters = false;
            }
            else{
                //window.alert('aqui, queima o folder_destination');
                //expresso_mail_archive.folder_destination = null;
            }            
        }
        else{
            expresso_mail_archive.update_counters = false;
        }
    }
    }
    //no counter needs 
    else return;
}

MailArchiver.prototype.getBase64PartData = function(partid, field){
    var spid = new String(partid);
    if(parseInt(spid.length) > 0){
        expresso_mail_archive.tmp_att_datasource = field;
        try{
            //ArchiveServices.getRawBinaryBody(expresso_mail_archive.getBase64PartDataOK, expresso_mail_archive.getBase64PartDataFailure, sessid, partid);
            ArchiveServices.getRawBinaryBody(expresso_mail_archive.getBase64PartDataOK, expresso_mail_archive.getBase64PartDataFailure, expresso_mail_archive.session.id, partid);
        }
        catch(e){
            //window.alert('Fail to getRawBinaryBody data from part id ' + partid + ':\n' + (e.description)?e.description:e);
            expresso_mail_archive.getFaultInfo();
        }
    }
    else {
        return(null);
    }
}

MailArchiver.prototype.getBase64PartDataOK = function(serviceData){
    var encdata = serviceData.getReturn();
    if(encdata.length <= 0){
        window.alert('Oh no: service return data is zero length...');
        this.tmp_att_data = null;
    }    
    else{
        expresso_mail_archive.tmp_att_datasource.value = encdata;
    }
}

MailArchiver.prototype.getBase64PartDataFailure = function(serviceReturn){
    window.alert('There are erros on getting binary data part:' + serviceReturn.getReturn());
}

MailArchiver.prototype.search = function(folders,fields){
    try{
        connector.showProgressBar();
        if((folders) && (fields)){
            expresso_mail_archive.search_queryconfig.reset();
            var folderlist = expresso_mail_archive.search_queryconfig.pharseFolders(folders);
            var filters = eval('(' + expresso_mail_archive.queryconfig.pharseSearchFields(fields) + ')');



            if(folderlist.indexOf("#") != -1)
                filters.folder = folderlist.substr(0, folderlist.indexOf("#"));
            else
                filters.folder = folderlist;
            if(folderlist.toLowerCase().indexOf("#recursive") != -1)
                expresso_mail_archive.search_queryconfig.folder_recursive = true;
            else
                expresso_mail_archive.search_queryconfig.folder_recursive = false;

            expresso_mail_archive.querydata = expresso_mail_archive.search_queryconfig.query(filters);



            //var query_data = '<?xml version="1.0" encoding="UTF-8"?><query subject="teste"><folder id="inbox"/><order asc="date"/></query>';
            ArchiveServices.listMessages(expresso_mail_archive.searchOperationOK, expresso_mail_archive.searchOperationFailure, expresso_mail_archive.session.id, expresso_mail_archive.querydata);        
        }
        //changing order view criteria (sorting result)
        else{    
            ArchiveServices.listMessages(expresso_mail_archive.searchOperationOK, expresso_mail_archive.searchOperationFailure, expresso_mail_archive.session.id, expresso_mail_archive.querydata);        
        }
    }catch (e){
        expresso_mail_archive.getFaultInfo();
    }
}

MailArchiver.prototype.searchOperationOK = function(returnService){
    if(returnService.getReturn().length > 0){
        var msglist = new Array();
        var msglist2 = new Array();
        expresso_mail_archive.ServiceReturnObjectSearch = new Array();

        for(var i=0; i< returnService.getReturn().length; i++){
            var ExpSerialized = expresso_mail_archive.pattern.toExpressoSearchHeader(returnService.getReturn()[i]);
            var ExpSerialized2 = expresso_mail_archive.pattern.toExpressoHeader(returnService.getReturn()[i]);
            var msgitem = connector.unserialize(ExpSerialized);
            var msgitem2 = connector.unserialize(ExpSerialized2);
      
            msglist.push(msgitem);//usando connector.unserialize sobre o header mapeado no layout abaixo
            msglist2.push(msgitem2);
            expresso_mail_archive.ServiceReturnObjectSearch.push(returnService.getReturn()[i]); //add each one service return data into this embeded object array
            
        }
        msglist["num_msgs"] = returnService.getReturn().length;
        msglist2["num_msgs"] = returnService.getReturn().length;
            
        //expresso_mail_archive.queryresult = msglist;        
        expresso_mail_archive.search_queryresult = msglist; 
        //expresso_mail_archive.messageslisted = msglist2;
        expresso_mail_archive.search_message_list = msglist2;
        //window.alert('temos ' + expresso_mail_archive.search_message_list.length + ' mensagens de resultado da pesquisa mapeadas em mem�ria');
    }
    else{
        expresso_mail_archive.ServiceReturnObjectSearch = null;
        expresso_mail_archive.search_queryresult        = null;
        expresso_mail_archive.search_message_list       = null;
        expresso_mail_archive.messageslisted            = null;
        expresso_mail_archive.queryresult               = null;
    } 
    connector.hideProgressBar();
}

MailArchiver.prototype.searchOperationFailure = function(){
    write_msg(get_lang('MailArchiver search operation fails', true));
    expresso_mail_archive.ServiceReturnObjectSearch = null;
    expresso_mail_archive.search_queryresult        = null;
    expresso_mail_archive.search_message_list       = null;
    expresso_mail_archive.messageslisted            = null;
    expresso_mail_archive.queryresult               = null;
    connector.hideProgressBar();
}

MailArchiver.prototype.getAuthId = function(){
    var handler_get_logon = function(data){
        if(data){
            expresso_mail_archive.balancerid = data[0];
            expresso_mail_archive.sessionid = data[1];
            expresso_mail_archive.logonid = new Array(data[2],data[3]);
            expresso_mail_archive.login();
        }
        else{
            window.alert('Login credentials call failure');
            expresso_mail_archive.session = null;
        }
    }
    cExecute("$this.user.get_mailarchiver_authid",handler_get_logon);     
}

MailArchiver.prototype.login = function(){
    try{
        ArchiveServices.login(expresso_mail_archive.loginOperationOK, expresso_mail_archive.loginOperationFailure, expresso_mail_archive.logonid[0], expresso_mail_archive.logonid[1], expresso_mail_archive.sessionid, expresso_mail_archive.balancerid);
    }
    catch(e){
        expresso_mail_archive.getFaultInfo();
    }
}

MailArchiver.prototype.loginOperationOK = function(returnService){
    if((typeof(returnService.getReturn()) == 'object') && (returnService.getReturn() != null)){
        expresso_mail_archive.session = new Object();
        expresso_mail_archive.session.expiration = returnService.getReturn().getExpiration();
        expresso_mail_archive.session.id = returnService.getReturn().getId();
        expresso_mail_archive.session.permissions = returnService.getReturn().getPermissions();
        expresso_mail_archive.CreateDefaultStructrure();
    }
    else{
        //not logged in MailArchiver
        expresso_mail_archive.session = null;
        write_msg(get_lang('MailArchiver user login fail', true));
        expresso_mail_archive.enabled = false;
        expresso_mail_archive.timer=null;
        expresso_mail_archive.interval = 500;
        ArchiveServices = null;
        connector.purgeCache();        
        draw_tree_folders();
        //expresso_mail_archive.DeactivateStatusListener(expresso_mail_archive);
    }
}

MailArchiver.prototype.getFolderCounters = function(){
    var vetreturn = new Array();
    /*for(var k=0; k<expresso_mail_archive.folders.length; k++){
        if(expresso_mail_archive.folders[k]["id"] == expresso_mail_archive.currentfolder){
            vetreturn.push(expresso_mail_archive.folders[k]["messages"], expresso_mail_archive.folders[k]["unseen"]);
            break;
        }
    }*/
    vetreturn.push(expresso_mail_archive.folder.nummessages, expresso_mail_archive.folder.unseen);
    return(vetreturn);
}

MailArchiver.prototype.setEmbeddedLink = function(){
    var img_objects = document.getElementsByTagName('img');
    for(var k = 0; k < img_objects.length; k++){
        if(img_objects[k].name.indexOf('embedded_img_') != -1) {
            var obj_link = img_objects[k].name.split('embedded_img_');
            var txt_link = obj_link[1];
            var img_base_link = mail_archive_protocol + "://" + mail_archive_host + ":" + mail_archive_port + "/temp/" + txt_link;
            img_objects[k].src = img_base_link;
        }
    }
}
/*
MailArchiver.prototype.getFolderPath = function(folder){
    try{
        ArchiveServices.getFolderInfo(expresso_mail_archive.getFolderPathOperationOK, expresso_mail_archive.getFolderPathOperationFailure, expresso_mail_archive.session.id, folder);
    }
    catch(e){
        window.alert('getFolderPath fails');
    }
}

MailArchiver.prototype.getFolderPathOperationOK = function(serviceReturn){
    window.alert('service return from getFolderPathOperationOK = ' + serviceReturn().getFolderPath());
    if(serviceReturn().length>0){
        window.alert('ok');
    }
    else{
        window.alert('nope');
    }
}

MailArchiver.prototype.getFolderPathOperationFailure = function(serviceFault){
    window.alert('fails at gettting folder path');
}
*/

MailArchiver.prototype.getMessageInfo = function(msgId){
    try{
        ArchiveServices.getMessageInfo(expresso_mail_archive.getMessageInfoOperationOK, expresso_mail_archive.getMessageInfoOperationFailure, expresso_mail_archive.session.id, msgId);
    }
    catch(e){
        window.alert('getMessageInfo fails');
    }    
}

MailArchiver.prototype.getMessageInfoOperationOK = function(serviceReturn){
    //window.alert('service return from getMessageInfoOperationOK = ' + serviceReturn.getReturn() + '\n' + typeof(serviceReturn.getReturn()) + '\n' + print_r(serviceReturn.getReturn()));
    if(typeof(serviceReturn.getReturn() == 'object')){
        var ExpSerialized2 = expresso_mail_archive.pattern.toExpressoHeader(serviceReturn.getReturn());
        var msgitem2 = connector.unserialize(ExpSerialized2);
        if(!expresso_mail_archive.messageslisted){
            expresso_mail_archive.messageslisted = [];
        }
        expresso_mail_archive.messageslisted.push(msgitem2);    
        expresso_mail_archive.getMessageHeaders(serviceReturn.getReturn().getId());

    }
    else{
        window.alert('getMessageInfoOperationOK nope');
    }
}

MailArchiver.prototype.turnOffLocalTreeStructure = function (){
     draw_new_tree_folder();  
}

MailArchiver.prototype.getMessageInfoOperationFailure = function(serviceFault){
    window.alert('fails at gettting message info');
}

MailArchiver.prototype.loginOperationFailure = function(ServiceFaillure){
    write_msg(get_lang('MailArchiver login operation fail', true));
}

MailArchiver.prototype.logout = function(){
    write_msg(get_lang('MailArchiver user logged out', true));
}
/*
 *The all pourpose JavaScript variable, used by all related functionalityes at
 *Expresso Mail to handle this object at user gui.
 */

var expresso_mail_archive;
expresso_mail_archive = new MailArchiver();
-->
