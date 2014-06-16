var time_refresh = 150000;

//MAILARCHIVER-01
// Intervalo de tempo de verificacao de comunicacao com o MailArchiver (milisegundos)
var check_services_interval = 2000;
// Numero de tentativas de reconexao
var check_services_tryouts = 1;
// Limite de tentativas de reconexao
var check_services_tryouts_limit = 5;


// tempo do auto save (em milisegundos) 
// 20000 = 20 segundos
var autosave_time = 40000;
var results_search_messages = ""; 
var cabecalho = '<h4>ExpressoLivre - ExpressoMail</h4>';
var auxNotificationCriteriaVetor = [];
var auxNotification;

var countNewMessage = 0;
var notifyNewMessageFilter = [];

var dynamicPersonalContacts = new Array();
var dynamicPersonalGroups = new Array();
var dynamicContacts = new Array();
var topContact = 0;

//Os IE's < 9 não possuem suporte a trim() introduzida no JavaScript 1.8.1
if(!String.prototype.trim){  
	String.prototype.trim = function(){
		return this.replace(/^\s+|\s+$/g,'');
	} 
}

function mount_url_folder(folders){
	return folders.join(cyrus_delimiter);
} 

function updateDynamicContact(userContacts){

    if(!userContacts){
        userContacts = REST.get("/usercontacts", false, updateDynamicContact);
        return true;
    }


    if(userContacts.collection && !userContacts.collection.error){
        dynamicData = normalizeContacts(userContacts.collection.itens);
    }else{
        dynamicData = [];
    }

    if(dynamicData){
        var dynamicContactsList = [];
        $.each(dynamicData, function(index, dynamic){

            var dataType = userContacts.collection.itens[index].dataType;

            dynamic['value'] = (dynamic.name ? dynamic.name +' - ': '') + dynamic.mail;
            dynamic['type'] = dataType
            dynamic['typel'] = (dataType.substring(0,7) == "/shared" ? "/"+dataType.substring(7,dataType.length)+"s" : dataType);

            topContact = dynamic.number_of_messages > topContact ? dynamic.number_of_messages : topContact;

            dynamicContactsList.push(dynamic);
        });
    }

    dynamicData = dynamicContactsList;
}


function updateDynamicPersonalContacts(){
	dynamicPersonalContacts = new Array();

    var personalContacts = REST.get("/personalContact");

    if(personalContacts.collection && !personalContacts.collection.error){
        var contactsData = normalizeContacts(personalContacts.collection.itens);
        if(contactsData){
            $.each(contactsData, function(index, value){
                var contact = {
                    id : parseInt(value.id),
                    name : value.name,
                    email: value.email,
                    value: value.name+ " - " + value.email,
                    type: "P"
                };
                dynamicPersonalContacts.push(contact);
            });
        }
    }
}


function updateDynamicPersonalGroups(){
	dynamicPersonalGroups = new Array();

    var groups = REST.get("/groups");

    if(groups.collection && !groups.collection.error){
        var contactsData = normalizeContacts(groups.collection.itens);
        if(contactsData.collecion && !contactsData.collecion.error){
            $.each(contactsData, function(index, value){
                var contact = {
                    id : parseInt(value.id),
                    owner : value.owner,
                    name : value.name,
                    email: value.email,
                    type: "G"
                };
                dynamicPersonalGroups.push(contact);
            });
        }
    }
}


function init()
{
	if ( !is_ie ) Element('tableDivAppbox').width = '100%';

    //MailArchiver save offset to gmt user preference data to list correctly date/time from messages list
    var save_gmtoffset = function(data){
        gmtoffset = data;
    }
		
    if(preferences.show_name_print_messages == "1") {
        var getUserName = document.getElementById("user_info");
        var userName = getUserName.innerHTML;
        var position = userName.indexOf("-");
        var userName = userName.substring(3, position);
        cabecalho = '<h4>' + userName;
    }

	current_folder = "INBOX";

    cExecute ("$this.imap_functions.get_range_msgs2&folder=INBOX&msg_range_begin=1&msg_range_end="+preferences.max_email_per_page+"&sort_box_type=SORTARRIVAL&search_box_type=ALL&sort_box_reverse=1", handler_draw_box);

    // Insere a applet de criptografia
    if( preferences.use_signature_digital_cripto == '1' ){ loadApplet(); }
    
    // Fim da inserção da applet
    cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
    
    if($.cookie('collapse_folders') == "true"){
        if(!is_ie)
            $("#folderscol").addClass( "hidden");
        else
            $("#folderscol").hide();
        $(".collapse_folders").addClass("ui-icon ui-icon-triangle-1-e").children().attr('title', get_lang("Expand"));
        refresh();
        resizeWindow();
    }
    else{
        $(".collapse_folders").addClass("ui-icon ui-icon-triangle-1-w").children().attr('title', get_lang("Hide"));
        refresh();
        resizeWindow();
    }

    $(".collapse_folders_td").attr('title', get_lang("Hide/Expand")).click(function(){
        if($("#folderscol").css("display") != "none"){
            if(!is_ie)
                $("#folderscol").addClass( "hidden");
            else
                $("#folderscol").hide();

            $(".collapse_folders").removeClass("ui-icon-triangle-1-w");
            $(".collapse_folders").addClass("ui-icon-triangle-1-e");
            $(".collapse_folders").parent().attr('title', get_lang("Expand"));
            $.cookie('collapse_folders', "true");
            refresh();
            resizeWindow();
        }else{
            if(!is_ie)
                $("#folderscol").removeClass( "hidden");
            else
                $("#folderscol").show();
            $(".collapse_folders").removeClass("ui-icon-triangle-1-e");
            $(".collapse_folders").addClass("ui-icon-triangle-1-w");
            $(".collapse_folders").parent().attr('title', get_lang("Hide"));
            $.cookie('collapse_folders', "false");
            refresh();
            resizeWindow();
        }
        resizeWindow();
    }).hover(
        function(){
            $(this).addClass("collapse_folders_td_over");
        },
        function(){
            $(this).removeClass("collapse_folders_td_over");
        }
    );

    if(parseInt(preferences.use_dynamic_contacts)){ updateDynamicContact(); }

    var handler_automatic_trash_cleanness = function(data)
    {
		if( data != false ){ write_msg(data.length +' '+ $("#txt_clear_trash").val()); }
	}

	// Versão
	$('#divAppboxHeader').html('<table height="16px" border=0 width=100% cellspacing=0 cellpadding=2><tr>'+
	'<td style="padding-left:17px" width=33% id="content_quota" align=left></td>'+
	'<td class="divAppboxHeader" width=33% id="main_title">Expresso Mail</td>'+
	'<td width=33% id="div_menu_c3" align=right></td>'+
	'</tr></table>');

	// Get cyrus delimiter
	cyrus_delimiter = Element('cyrus_delimiter').value;

	cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);	
	
	setTimeout('auto_refresh()', time_refresh);
	
	$("#divAppbox").css("padding-left", "0px");

	// Inicia Messenger
	setTimeout( function(){ init_messenger(); }, 1000 );
}

function init_offline(){
        current_folder = 'local_Inbox';
	if (account_id != null) {
		if (!is_ie)
			Element('tableDivAppbox').width = '100%';
		else
			connector.createXMLHTTP();
		Element('divStatusBar').innerHTML = '<table height="16px" border=0 width=100% cellspacing=0 cellpadding=2>' +
		'<tr>' +
		'<td style="padding-left:17px" width=33% id="content_quota" align=left></td>' +
		'<td width=33% height=16px align=center nowrap><font face=Verdana, Arial, Helvetica, sans-serif color=#000066 size=2>' +
		'<b>ExpressoMail Offline</b><font size=1><b> - Versão 1.0</b></font></td>' +
		'<td width=33% id="div_menu_c3" align=right></td>' +
		'</tr></table>';

		proxy_mensagens.messages_list('local_Inbox', 1, preferences.max_email_per_page, 'SORTARRIVAL', null, 1,1,1, function handler(data){
			draw_box(data, 'local_Inbox');
		})

		// Get cyrus delimiter
		cyrus_delimiter = Element('cyrus_delimiter').value;

	}
}

function init_messenger()
{
	 // Function Remove Plugin
	 var remove_plugin_im = function()
	 {
		// Remove tr/td/div
		$("#content_messenger").parent().parent().remove();
	
		// Remove Input
		$("input[name=expresso_messenger_enabled]").remove();

		// Div bar
		$("#messenger-conversation-bar-container").parent().remove();

		// Resize Window
		resizeWindow();
	 };

	 if( $("input[name=expresso_messenger_enabled]").length > 0 )
	 {	
		if( $("input[name=expresso_messenger_enabled]").attr("value") === "true" )
		{
			if( parseInt($.browser.version) > 7 )
			{	
				$.ajax({
					"type"		: "POST",
					"url"		: "../prototype/plugins/messenger/auth_messenger.php",
					"dataType"	: "json",
					"success" 	: function(data)
					{
						if( !data['error'] )
						{
							if( $.trim(data['dt__b']) != "" )
							{	
								// Append divs
								$("#content_messenger").append("<div id='_plugin'></div>");
								$("#content_messenger").append("<div id='_menu'></div>");
								
								// Div content_messenger#_menu
								$("#content_messenger").find("div#_menu").css({"cursor":"pointer","text-align":"center"})
								$("#content_messenger").find("div#_menu").append($("<img>").attr("src","templates/default/images/chat-icon-disabled.png")
								.attr("title", get_lang("Expresso Messenger disabled"))
								.attr("alt", get_lang("Expresso Messenger disabled")));
								
								// Div content_messenger#_plugin
								$("#content_messenger").find("div#_plugin").css({"width":"225px","display":"none"});

								// Load IM
								$("#content_messenger").find("div#_menu").find("img").on("click", function()
								{
									// OpenMessenger
									$("#content_messenger").find("div#_menu").on("click", openMessenger );

									// Load IM
									$("#content_messenger").find("div#_plugin").im({
										"resource" 	: data['dt__a'],
										"url"		: data['dt__b'],
										"domain"	: data['dt__c'],
										"username"	: data['dt__d'],
										"auth"		: data['dt__e'],
										"debug"		: false,
										"soundPath"	: "../prototype/plugins/messenger/",
										"height"	: 270
									}).fadeIn(3000);

									//Full Name Expresso Messenger
									var fullName = $("input[name=messenger_fullName]").val();
									
									$(".chat-title").find(".chat-name")
										.html( fullName.substring(0,20) + "..." )
										.attr("alt", fullName)
										.attr("title", fullName);

									$("#conversation-bar-container")
										.css("overflow","hidden")
										.css("bottom","1px");

									$(this).off("click");
									
								});
								
								// Resize Window
								resizeWindow();
							}
							else
							{
								// Error Load Plugin Jabber;
								write_msg( get_lang("ERROR: The IM service, inform the administrator") );
								
								// Remove Plugin;
								remove_plugin_im();
							}
						}
						else
						{
							// Remove Plugin
							remove_plugin_im();
						}
					}
				});
			}
			else
			{
				// Msg update browser
				write_msg( get_lang("Your browser is not compatible to use the Express Messenger") );

				// Remove Plugin
				remove_plugin_im();
			}
		}//
	}
	else
	{
		// Remove Plugin
		remove_plugin_im();
	}
}

/**
 * Carrega a applet java no objeto search_div
 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
 */

function loadApplet(){

	var search_div = Element('search_div');
	var applet = null;
	if (navigator.userAgent.match('MSIE')){
		applet = document.createElement('<object style="display:yes;width:0;height:0;vertical-align:bottom;" id="cert_applet" ' +
			'classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"></object>');

		var parameters = {
			type:'application/x-java-applet;version=1.5',
			code:'ExpressoSmimeApplet',
			codebase:'/security/',
			mayscript:'true',
			token: token_param,
			locale: locale,
			archive:'ExpressoCertMail.jar,' +
				'ExpressoCert.jar,' +
				'bcmail-jdk15-142.jar,' +
				'mail.jar,' +
				'activation.jar,' +
				'bcprov-jdk15-142.jar,' +
				'commons-codec-1.3.jar,' +
				'commons-httpclient-3.1.jar,' +
				'commons-logging-1.1.1.jar'
			//debug:'true'
		}

		if (parameters != 'undefined' && parameters != null){
			for (var parameter in parameters) {
				var param = document.createElement("PARAM");
				param.setAttribute("name",parameter);
				param.setAttribute("value",parameters[parameter]);
				applet.appendChild(param);
			}
		}
	}
	else
	{
		applet = document.createElement('embed');
		applet.innerHTML = '<embed style="display:yes;width:0;height:0;vertical-align:bottom;" id="cert_applet" code="ExpressoSmimeApplet.class" ' +
			'codebase="/security/" locale="'+locale+'"'+
			'archive="ExpressoCertMail.jar,ExpressoCert.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar,commons-codec-1.3.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar" ' +
			'token="' + token_param + '" ' +
			'type="application/x-java-applet;version=1.5" mayscript > ' +
			//'type="application/x-java-applet;version=1.5" debug="true" mayscript > ' +
			'<noembed> ' +
			'No Java Support. ' +
			'</noembed> ' +
			'</embed> ';
	}
	
	if( applet != null )
	{
		applet.style.top	= "-100px";
		applet.style.left	= "-100px";
		window.document.body.insertBefore( applet, document.body.lastChild );
	}
	
}

function disable_field(field,condition) {
	var comando = "if ("+condition+") { document.getElementById('"+field.id+"').disabled=true;} else { document.getElementById('"+field.id+"').disabled=false; }";
	eval(comando);
}
/*
	funcão que remove todos os anexos...
*/
function remove_all_attachments(folder,msg_num) {

	var call_back = function(data) {
		if(!data.status) {
			alert(data.msg);
		}
		else {
			msg_to_delete = Element(msg_num);
			change_tr_properties(msg_to_delete, data.msg_no);
			msg_to_delete.childNodes[1].innerHTML = "";
			write_msg(get_lang("Attachments removed"));
			folderName = Base64.encode(folder);
			folderName = folderName.replace(/=/gi, '');
			delete_border(msg_num+'_r_'+folderName,'false'); //close email tab

        }
	};

	$.Zebra_Dialog(get_lang("delete all attachments confirmation"), {
		'type':     'question',
		'buttons':  [get_lang("No"), get_lang("Yes")],
		'overlay_opacity' : 0.5,
		'width' : 500,
		'custom_class': 'custom-zebra-filter',
		'onClose':  function(caption) {
			if(caption == get_lang("Yes"))
				cExecute ("$this.imap_functions.remove_attachments&folder="+folder+"&msg_num="+msg_num, call_back);
		}
	});
}
function watch_changes_in_msg(border_id)
{
	if (document.getElementById('border_id_'+border_id))
	{
		function keypress_handler ()
		{
			away=false;
			var save_link = content.find(".save");
			save_link.onclick = function onclick(event) {openTab.toPreserve[border_id] = true;save_msg(border_id);} ;
			save_link.button({disabled: false});
			$(".header-button").button();
		};
		var content = $("#content_id_"+border_id);
		
		var subject_obj = content.find(".subject");
		if ( subject_obj.addEventListener )
				subject_obj.addEventListener('keypress', keypress_handler, false);
		else if ( subject_obj.attachEvent )
			subject_obj.attachEvent('onkeypress', keypress_handler);

		var to_obj = content.find('[name="input_to"]');
		if ( to_obj.addEventListener )
				to_obj.addEventListener('keypress', keypress_handler, false);
		else if ( to_obj.attachEvent )
			to_obj.attachEvent('onkeypress', keypress_handler);
			
		var cc_obj = content.find('[name="input_cc"]');
		if ( cc_obj.addEventListener )
				cc_obj.addEventListener('keypress', keypress_handler, false);
		else if ( cc_obj.attachEvent )
			cc_obj.attachEvent('onkeypress', keypress_handler);
		
		if(content.find('[name="input_cco"]').length){
			var cco_obj = content.find('[name="input_cco"]');
			if ( cco_obj.addEventListener )
				cco_obj.addEventListener('keypress', keypress_handler, false);
			else if ( cco_obj.attachEvent )
				cco_obj.attachEvent('onkeypress', keypress_handler);
		}
			
        var txtarea_obj = Element('body_'+border_id);
        if (txtarea_obj){
          if ((preferences.plain_text_editor == 1)||(Element('body_')+border_id).checked){
		    if ( txtarea_obj.addEventListener )
			   txtarea_obj.addEventListener('keypress', keypress_handler, false);
		    else if ( txtarea_obj.attachEvent )
			   txtarea_obj.attachEvent('onkeypress', keypress_handler);
          }
        }
	}
}

function show_msg_img(msg_number,folder){
	var call_back = function(data){
	   data.showImg = true;
	   if (!Element(data.msg_number)){
		   trElement = document.createElement('DIV');
		   trElement.id = data.msg_number;
		   Element("tbody_box").appendChild(trElement);
	   }
	   show_msg(data);
	}

	proxy_mensagens.msg_img(msg_number,folder,call_back);

}

function show_msg(msg_info){

	if(!verify_session(msg_info))
		return;
	if (typeof(msg_info) != 'object')
		alert(get_lang("Error in show_msg param is not object"));

	if (msg_info.status_get_msg_info == 'false')
	{
		write_msg(get_lang("Problems reading your message")+ ".");
		return;
	}

	var handler_sendNotification = function(data){
		if (data)
			write_msg(get_lang("A read confirmation was sent."));
		else
			write_msg(get_lang("Error in SMTP sending read confirmation."));
	}

	if(msg_info.source)
	{
		// Abrindo um e-mail criptografado
		// Verifica se existe o objeto applet
		if (!Element('cert_applet'))
		{
			// se não existir, mostra mensagem de erro.
			write_msg(get_lang('The preference "%1" isn\'t enabled.', get_lang('Enable digitally sign/cipher the message?')));
		}
		else
		{
			// Passa os dados para a applet
			Element('cert_applet').doButtonClickAction('decript', msg_info.msg_number, msg_info.source, msg_info.msg_folder); 
		}
		
		return;

	}

	if (msg_info.status_get_msg_info == 'false')
	{
		write_msg(get_lang("Problems reading your message")+ ".");
		return;
	}

	if (msg_info.status == 'false'){
		eval(msg_info.command_to_exec);
	}
	else{
		var ID = msg_info.original_ID ? msg_info.original_ID : msg_info.msg_number;
		        
		var folderName = msg_info.msg_folder;
		folderName = Base64.encode(folderName);
		folderName = folderName.replace(/=/gi, '');
		var id_msg_read = ID+"_r_"+folderName;
        
        //Evita a tentativa de criação de uma aba cujo ID já existe
        if (Element("border_id_"+id_msg_read) && currentTab > 0) 
		    id_msg_read += "n";
    
		if (preferences.use_shortcuts == '1') 
	          select_msg(ID, 'null'); 
			  
		// Call function to draw message
		// If needed, delete old border
                var isPartMsg = false;
                for(var ii = 0; ii < partMsgs.length; ii++)
                     if(partMsgs[ii] == ID) isPartMsg = true;    
					 
					if(msg_info.alarm == false){
						if ((openTab.type[currentTab] == 2 || openTab.type[currentTab] == 3) && isPartMsg === false && msg_info.openSameBorder == true){
                            delete_border(currentTab,'false');
                        }
							
					}
					
		if(Element("border_id_" + id_msg_read)) {
			alternate_border(id_msg_read);
			resizeWindow();
		}
		else
		{
			var border_id = create_border(msg_info.subject, id_msg_read);
			if(border_id && border_id != "maximo")
			{
				openTab.type[border_id] = 2;
				openTab.imapBox[border_id] = msg_info.msg_folder;
				draw_message(msg_info,border_id);
			}
			else
				return;
		}

		var domains = "";
		if ((msg_info.DispositionNotificationTo) && (!msg_is_read(ID) || (msg_info.Recent == 'N')))
		{
			if (preferences.notification_domains != undefined && preferences.notification_domains != "")
			{
				domains = preferences.notification_domains.split(',');
			}
			else
			{
				var confNotification = true;
			 }
			for (var i = 0; i < domains.length; i++)
				if (Base64.decode(msg_info.DispositionNotificationTo).match("@"+domains[i]))
				{
					var confNotification = true;
					break;
				}
				if (confNotification == undefined)
					var confNotification = confirm(get_lang("The sender:\n%1\nwaits your notification of reading. Do you want to confirm this?",Base64.decode(msg_info.DispositionNotificationTo)), "");

			if (confNotification) {
			/* Adequação a nova funcionalidade. Agora, a confirmação de leitura é uma preferência do usuário. */
				if(preferences.confirm_read_message) {
					$.Zebra_Dialog(get_lang("Would you like to send the read receipt?"), {
						'type':     'question',
						'title':    get_lang('Read receipt'),
						'buttons':  [get_lang("No"), get_lang("Yes")],
						'overlay_opacity' : 0.5,
						'custom_class': 'custom-zebra-filter',
						'onClose':  function(caption) {
							if(caption == get_lang("Yes"))
								cExecute ("$this.imap_functions.send_notification&notificationto="+msg_info.DispositionNotificationTo+"&date="+msg_info.udate+"&subject="+url_encode(msg_info.subject), handler_sendNotification);								
							else 
								write_msg(get_lang("Confirmation message is not sent"));
						}
					});
				}
				else
					cExecute ("$this.imap_functions.send_notification&notificationto="+msg_info.DispositionNotificationTo+"&date="+msg_info.udate+"&subject="+url_encode(msg_info.subject), handler_sendNotification);								
			}
				
		}
		if (msg_info.showImg)
		{
			$("#body_"+id_msg_read).html(msg_info.body);
			$('#show_img_link_'+id_msg_read).remove();
		}
		//Change msg class to read.
		if (!msg_is_read(ID))
		{
            //MAILARCHIVER-01 TAG MESSAGE AS SEEN
            if (proxy_mensagens.is_local_folder(get_current_folder())){
                expresso_mail_archive.drawdata = null //no draw action
                var tl = expresso_mail_archive.pattern.tagConfig('unseen', ID, 1);
                expresso_mail_archive.taglist = tl;
                expresso_mail_archive.progressbar = window.setTimeout("expresso_mail_archive.tagMessage()",1);                            
            }
            set_msg_as_read(ID, true);
			if (msg_info.cacheHit || (!proxy_mensagens.is_local_folder(get_current_folder()) && msg_info.original_ID))
			{
            	set_message_flag(ID, "seen"); // avoid caducous (lazy) data
			}
		}
	}
	setTimeout('resizeWindow()',300);
}

function auto_refresh(){
	refresh(preferences.alert_new_msg, preferences.notifications);
	setTimeout('auto_refresh()', time_refresh);
}

function notificationFilter(data, notifyCriteria){
	
	if(parseInt(notifyCriteria)  && data.length > 0 && !activePage ){
	
		var howManyCriteria = $('div.gray.filtersDeadline .message-list li').length;
		var differenceOfNewCriteria = data.length - howManyCriteria;
		
		if(differenceOfNewCriteria > 0){
			for(var i=data.length - differenceOfNewCriteria; i < data.length; i++){
                var msg_folder = data[i].msg_folder;
                var msg_number = data[i].msg_number;
				desktopNotification.sentNotification("",get_lang("Filter criteria"),  truncate(new Date(data[i].udate).toString('dd/MM HH:mm') + ' - ' + data[i].from+' - '+data[i].subject, 75));
				desktopNotification.showNotification(function(){
                    cExecute ("$this.imap_functions.removeFlagMessagesFilter&folder="+msg_folder+"&msg_number="+msg_number, function(){});
                }, function(){
					window.focus();
					this.cancel();
				});
			}
		}
	}
}

function refresh(alert_new_msg, notifyPermission){

    //Não deixa atualizar caso o usuario esteja em uma aba de nova mesnagem , reponder ou encaminhar;
    //Evita o travamento da escrita no CKEDITOR.
    if(typeof(currentTab) !== 'undefined' && currentTab.toString().indexOf("_r_") === -1 && currentTab !== 0 )
        return;

	getFromAlertRules();
	var handler_refresh = function(data){

        if(preferences['use_alert_filter_criteria'] == "1")
        {
            var handlerMessageFilter = function (data) {
                notificationFilter(data, notifyPermission);
                alarmFollowupflagged('filtersAlarms', data);

            }
            /* Busca  nas pastas indexadas para ver se há novas mensagens com a flag $FilteredMessage */
            cExecute ("$this.imap_functions.getFlaggedAlertMessages&folders="+fromRules, handlerMessageFilter);
        }

		if(data['msg_range_end'])
			if(data['msg_range_end'] > 0)
				current_page = data['msg_range_end']/preferences.max_email_per_page;
		if(!verify_session(data))
			return;
		var total_messages_element = Element('tot_m');
			
		var box = Element("tbody_box");
		if (box.childNodes.length == 0)
			showEmptyBoxMsg(box);

		if (data.length > 0 || countNewMessage > 0){
			for(var i=0;i< data.length;i++){
				if (!onceOpenedHeadersMessages[current_folder])
					onceOpenedHeadersMessages[current_folder] = {};
				onceOpenedHeadersMessages[current_folder][data[i].msg_number] = data[i];
			}
			Element("table_message_header_box_0").emptyBody = false;
			table_element = Element("table_box");
			var msg_info = document.getElementById('msg_info');
			if (msg_info != null)
			{
				var msg_tr = msg_info.parentNode.parentNode;
				msg_tr.removeChild(msg_info.parentNode);
				if (!Element("colgroup_main_"+numBox)) {
					var colgr_element = buildColGroup();
					colgr_element.setAttribute("id","colgroup_main_"+numBox);
					table_element.appendChild(colgr_element);
				}
			}

			var box = Element("tbody_box");
			//table_element.insertBefore(box, Element("colgroup_main_"+numBox)); // keeps colgroup as the last child
			//table_element.appendChild(Element("colgroup_main_"+numBox));
			
			
			if (!$("#colgroup_main_0").size()){
				$(table_element).append(Element("colgroup_main_"+numBox));
			}

			if (data.msgs_to_delete.length > 0){
				for (var i=0; i<data.msgs_to_delete.length; i++){
					if ( (data.msgs_to_delete[i] != undefined) && (data.msgs_to_delete[i] != "")){
						removeAll(data.msgs_to_delete[i]);
					}
				}
			}
			if (data[0] && data[0].msg_folder != current_folder) // Bad request
				return false;
				
			for (var i=0; i<data.length; i++){
				var existent = document.getElementById(data[i].msg_number);
				if (!existent)
				{
					selectedPagingMsgs[data[i].msg_number] = false;
                    if(data.new_msgs != 0)
					    totalFolderMsgs++;
					updateSelectedMsgs(false,data[i].msg_number);
					var new_msg = this.make_tr_message(data[i], current_folder, data.offsetToGMT);
					$(new_msg).draggable({
						start : function(){
		                    $('.upper, .lower').show();
                    		$(".lower").css("top", ($("#content_folders").height()-18) + $("#content_folders").offset().top);
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
									},
									out : function(a,b){

										//RETIRA BORDA EM VOLTA DA PASTA
										$(b.helper).find(".draggin-folder,.draggin-mail").css("color", "");
										$(this).removeClass("folder-over");
									},
									//accept: ".draggin_mail",
									drop : function(event, ui){
										$(this).css("border", "");
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
						helper: function(event){
							$(this).addClass("selected_msg").find("input:checkbox").attr("checked", true);
							updateSelectedMsgs($(this).find("input:checkbox").is(':checked'),$(this).attr("id"));
							if ( $("#tbody_box .selected_shortcut_msg").length > 1) {
								$("#tbody_box .selected_shortcut_msg").addClass("selected_msg").find('.checkbox').attr('checked',true);
								$.each( $(".selected_shortcut_msg"), function(index, value){
					            	updateSelectedMsgs($(value).find(":checkbox").is(':checked'),$(value).attr("id"));
						        });
							}
							if(totalSelected() > 1)
								return $("<tr><td>"+DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : (totalSelected()+" "+get_lang("selected messages")), type: "messages"})+"</td></tr>");
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
					}).bind("contextmenu", function(){
						if (!(($(event.target).find('img').length > 0) && ($(event.target).hasClass('td-label')))){
							if($(this).find("input:checkbox").attr("checked") != "checked"){
								$(this).find("input:checkbox").trigger('click');
								$(this).addClass("selected_msg");
							}
							updateSelectedMsgs($(this).find("input:checkbox").is(':checked'),$(this).attr("id"));
						}
					});
					//_dragArea.makeDragged(new_msg, data[i].msg_number, data[i].subject, true);
					
					if( data[i].next_msg_number != undefined && data[i].next_msg_number != null ){
						try {
							box.insertBefore(new_msg, box.childNodes[data[i].msg_key_position]);					
						}
						catch (e){
							box.insertBefore(new_msg, box.firstChild);
						}
					}
					else if (data[i].Recent == 'N'){
						box.insertBefore(new_msg,box.firstChild);
					}
					else {
						box.appendChild(new_msg);
					}
				}
			}
            var box = Element("tbody_box");
            if(box.childNodes.length > 1){
                updateBoxBgColor(box.childNodes);
            }

			if(parseInt(preferences.use_shortcuts))
				select_msg("null","reload_msg","null");
		
			if(parseInt(alert_new_msg) && data.new_msgs > 0 && activePage)
				alert(data['new_msgs'] > 1 ? get_lang("You have %1 new messages", data['new_msgs']) + "!" : get_lang("You have 1 new message") +"!");
			
			
			if(parseInt(notifyPermission)  && (data.new_msgs > 0 || !!countNewMessage) && !activePage ){
			
				countNewMessage += data.length;
				
                param = (countNewMessage > 1 ? (get_lang("You have %1 new messages", countNewMessage) + "!") : (get_lang("You have 1 new message") + "!"));
				desktopNotificationAux = desktopNotification.sentNotification("",get_lang("Notification"), param);
				
				if(!auxNotification)
					desktopNotification.cancelByReference(auxNotification);
				
				desktopNotification.showNotification(function(){
					countNewMessage = 0;
				}, function(){
					window.focus();
					this.cancel();
					countNewMessage = 0;
				});
				
				auxNotification = desktopNotificationAux;
			}
			build_quota(data['quota']);
		}
		if(data.new_msgs){
			total_messages_element.innerHTML = parseInt( total_messages_element.innerHTML ) + data.new_msgs;
		}else if(data.tot_msgs){
			total_messages_element.innerHTML = data.tot_msgs >=0  ? data.tot_msgs : 0;
		}
		// Update Box BgColor
		var box = Element("tbody_box");
		if(box.childNodes.length > 1){
			updateBoxBgColor(box.childNodes);
		}
		
		//Sincroniza mensagens lidas e não lidas em clientes de emails diferentes
		if(data.unseens && data.unseens.length != 0)
			synchronize(data.unseens);
		
		connector.purgeCache();
		cExecute("$this.imap_functions.get_folders_list&onload=true", force_update_menu);
		resizeMailList();

	}
	
	msg_range_end = (current_page*preferences.max_email_per_page);
	msg_range_begin = (msg_range_end-(preferences.max_email_per_page)+1);


	//Get element tBody.
	main = Element("tbody_box");
	if(!main)
		return;

	// Get all TR (messages) in tBody.
	main_list = main.childNodes;
	var tmp = '';
	var string_msgs_in_main = '';

	var len = main_list.length;
	for (var j=0; j < len; j++)
		tmp += main_list[j].id + ',';

	string_msgs_in_main = tmp.substring(0,(tmp.length-1));
	if(!expresso_offline)
		$.ajax({
			  url: 'controller.php?' + $.param( {action: '$this.imap_functions.refresh',
							      folder: current_folder,
							      msgs_existent: string_msgs_in_main,
							      msg_range_begin: msg_range_begin,
							      msg_range_end: msg_range_end,
							      sort_box_type: sort_box_type,
							      search_box_type: search_box_type,
							      sort_box_reverse: sort_box_reverse } ),
			  success: function( data ){
			      data = connector.unserialize( data );
			      
			      if( data )
				  handler_refresh( data );

			  },
              async: false,
			  beforeSend: function( jqXHR, settings ){
				connector.showProgressBar();
			  },
			  complete: function( jqXHR, settings ){
			  	connector.hideProgressBar();
                
			  }

		});

	var msgs = $("#tbody_box tr");
	//Se a classe abaixo (somente ela) não existir a barra de seleção azul deve voltar ao topo.
	if(!msgs.hasClass("current_selected_shortcut_msg"))
	{
        if(preferences.use_shortcuts == '1')
        {
            msgs.first().addClass("current_selected_shortcut_msg selected_shortcut_msg");
        }
	}
}

function synchronize(unseens){
	$('.tr_msg_unread').each(function(i, v){
		$(this).find('td:eq(8) img').attr('src', 'templates/default/images/seen.gif');
		$(this).removeClass('tr_msg_unread');
		if($.inArray(parseInt($(this).attr('id')), unseens) == -1){
			$(this).find('td:eq(8) img').attr('src', 'templates/default/images/seen.gif');
			$(this).removeClass('tr_msg_unread');
		}
	});
	$.each(unseens, function(i, v){
		$('tr#' + v).addClass('tr_msg_unread');
		$('tr#' + v + ' td:eq(8)').find('img').attr('src', 'templates/default/images/unseen.gif');
	});
}

function delete_msgs(folder, msgs_number, border_ID, show_success_msg,archive, prev_message){	
            if( preferences.use_local_messages == 1 && expresso_local_messages.isArchiving( msgs_number, folder ) ){
		alert( get_lang("Impossible to delete messages that are still being archived.") );
			  return;
			}
				
 		        var userTrashFolder = ''; 

 		        if (arguments.length <= 6 && typeof(show_success_msg) == "undefined") show_success_msg = true; 
 		        if (folder == 'null') folder = current_folder; 
 		 
 		        if(folder.substr(0,4) == 'user') 
 		        { 
					var arrayFolder = folder.split(cyrus_delimiter); 
					userTrashFolder = 'user'+cyrus_delimiter+arrayFolder[1]+cyrus_delimiter+special_folders['Trash'];
					var has_folder = false;//Folder.get( userTrashFolder, false );
					var folders = DataLayer.get("folder");
					$.each(folders,function(index,value){
						if(value && value.id == userTrashFolder) 
							has_folder = true;
					});

					if(!has_folder){
						create_new_folder(special_folders['Trash'], 'user'+cyrus_delimiter+arrayFolder[1]);
					}
					
 		        } 
				else userTrashFolder = mount_url_folder(["INBOX",special_folders["Trash"]]); 
 	 
 		        if(openTab.type[currentTab] == 1) 
					return move_search_msgs('content_id_'+currentTab,userTrashFolder,special_folders['Trash']); 
					
				if(currentTab.toString().indexOf("_r") != -1) 
					msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf("_r")); 
 		         
 		        if (!archive && (parseInt(preferences.save_deleted_msg)) && (folder != userTrashFolder)){ 
 		            move_msgs2(folder, ""+msgs_number, border_ID, userTrashFolder,special_folders['Trash'],show_success_msg, undefined, prev_message ); 
					return;
				}

	var handler_delete_msgs = function(data){

		Element('chk_box_select_all_messages').checked = false;
		if (currentTab)
			mail_msg = Element("tbody_box_"+currentTab);
		else
			mail_msg = Element("tbody_box");

		if ( preferences.use_shortcuts == '1') {
				//Last msg is selected
				if (mail_msg && exist_className(mail_msg.childNodes[mail_msg.childNodes.length-1], 'selected_shortcut_msg') ) {
					select_msg('null', 'up', true);
				}
				else {
					if (!select_msg('null', 'down', true)) {
						select_msg('null', 'up', true);
					}
				}
			}

		if(data.status == false){
			write_msg(get_lang("You don't have permission for this operation in this shared folder!"));
			return false;
		}

		//Se pref. usar mensagens locais ativaada e não for operação de arquivamento exibe mensagem de remoção.
		if(preferences.use_local_messages == 1){
			if(!expresso_mail_archive.isArchiveOperation){
				if (show_success_msg){
					if (data.msgs_number.length == 1)
						write_msg(get_lang("The message was deleted."));
					else
						write_msg(get_lang("The messages were deleted."));
				}
			}
		}
		else{
			if (show_success_msg){
				if (data.msgs_number.length == 1)
					write_msg(get_lang("The message was deleted."));
				else
					write_msg(get_lang("The messages were deleted."));
			}
		}

		if (openTab.type[currentTab] > 1){
			var msg_to_delete = Element(msgs_number);
			if (parseInt(preferences.delete_and_show_previous_message) && msg_to_delete) {
				if (msg_to_delete.previousSibling){
 					var previous_msg = prev_message ? prev_message : msg_to_delete.previousSibling.id;
					if(previous_msg){
						 $.ajax({
							  url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
												  msg_number: previous_msg, 
												  msg_folder: current_folder,
												  decoded: true } ),
							  success: function( data ){
								  data = connector.unserialize( data );
								  
								  if( data ){
								  	data.openSameBorder = true;
								  	show_msg( data );
								  }
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
				else{
                    delete_border(currentTab,'false');
                }
					
			}
			else{
                delete_border(currentTab,'false');
            }
				
		}
		for (var i=0; i<data.msgs_number.length; i++){
				var msg_to_delete = Element(data.msgs_number[i]);
				if (msg_to_delete){
						removeAll(msg_to_delete.id);
						 delete selectedPagingMsgs[data.msgs_number[i]]; 
				}
                // removido decremento da variável pois estava decrementando em outros lugares
				// totalFolderMsgs = totalFolderMsgs - data.msgs_number.length;
		}
		$.each(data.msgs_number, function(index, value){
	    	msgFolder =  Base64.encode(get_current_folder());
	    	tabToRemove = value + "_r_" + msgFolder.replace(/=/g,"");
	    	if ($("#"+tabToRemove).length != 0){
	    		delete_border(tabToRemove,'false');
	    	}
	    });
		Element('tot_m').innerHTML = parseInt(Element('tot_m').innerHTML) - data.msgs_number.length;
		refresh();
	}
	
	if (msgs_number.length > 0 || parseInt(msgs_number) > 0){
		params_to_delete = "&folder="+folder;
		params_to_delete += "&msgs_number="+msgs_number;
		params_to_delete += "&border_ID="+border_ID;
		params_to_delete += "&sort_box_type="+sort_box_type;
		params_to_delete += "&search_box_type="+search_box_type;
		params_to_delete += "&sort_box_reverse="+sort_box_reverse;

		cExecute ("$this.imap_functions.delete_msgs", handler_delete_msgs, params_to_delete);
	}
	else{
		write_msg(get_lang('No selected message.'));
	}
}

  
function move_search_msgs(border_id, new_folder, new_folder_name, action){

	var msg_to_delete = "";
	var msg_to_move = "";
	var selected_messages = '';
	var temp_msg;
	var remove_currente_folder = "";
	var id_border = currentTab.replace(/[a-zA-Z_]+/, "");

	//Validação para não poder manipular mensagens locais na busca
	if (currentTab.indexOf('search_local') != -1 || proxy_mensagens.is_local_folder(new_folder))
	{
		alert(get_lang("You cant manipulate local messages on search"));
		return;
	}

	var delete_msg = false;
	
	if(new_folder_name == special_folders['Trash']){
		delete_msg = true;
		}
	selected_messages = get_selected_messages_search();
	
	if( preferences.use_local_messages == 1 && expresso_local_messages.isArchiving( selected_messages, folder ) ){
		alert( get_lang("Impossible to move messages that are still being archived.") );
	  return;
	}
	var handler_move_search_msgs = function(data){
		if(!data || !data.msgs_number)
			return;
		else if(data.deleted) {
			if(data.no_move && data.move)
				alert(get_lang("Unable to remove the message (s) of shared folders which you do not have permission."));
			else if (data.msgs_number.length == 1)
				write_msg(get_lang("The message was deleted."));
			else
				write_msg(get_lang("The messages were deleted."));
		}else if(data.status == false && data.move ){
					alert(get_lang("Unable to remove the message (s) of shared folders which you do not have permission."));
		}else if(data.status == false){
					alert(get_lang("You don't have permission for this operation in this shared folder!"));
					return false;
		}else{
			if (data.msgs_number.length == 1)
				write_msg(get_lang("The message was moved to folder ") + lang_folder(data.new_folder_name));
			else
				write_msg(get_lang("The messages were moved to folder ") + lang_folder(data.new_folder_name));
		}

		if(data.no_move){
			var no_remove = data.no_move.split(',');
			var continua = true;
			
		selected_messages = selected_messages.split(",");
		for (i = 0; i < selected_messages.length; i++){
				for(j = 0; j < no_remove.length; j++)
					if(selected_messages[i] == no_remove[j])
						continua = false;
				if(continua)	
					removeAll(selected_messages[i]+'_s'+id_border);
				continua = true;
		}
		}else{
			selected_messages = selected_messages.split(",");
			for (i = 0; i < selected_messages.length; i++){
				removeAll(selected_messages[i]+'_s'+id_border);
		
			}
		}
		
		// Update Box BgColor
		var box = Element("tbody_box_"+getNumBoxFromTabId(currentTab)).childNodes;
		if(box.length > 1){
			updateBoxBgColor(box);
		}
		connector.purgeCache();

		
		if(remove_currente_folder != ""){
			var mail_msg = Element('tbody_box').childNodes;
			remove_currente_folder = remove_currente_folder.substring(0,(remove_currente_folder.length-1));
			remove_currente_folder = remove_currente_folder.split(",");
			for(i = 0; i < remove_currente_folder.length; i++)
				removeAll(remove_currente_folder[i]);

			// Update Box BgColor
			var box = Element("tbody_box");
			if(box.childNodes.length > 0){
				updateBoxBgColor(box.childNodes);
			}
			if(folder == get_current_folder()){
				Element('tot_m').innerHTML = parseInt(Element('tot_m').innerHTML) - remove_currente_folder.length;
			}
		}
		
		draw_new_tree_folder();
		EsearchE.refresh();
	}

	if (selected_messages){
		
		var selected_param = "";
		if (selected_messages.indexOf(',') != -1)
		{
			selected_msg_array = selected_messages.split(",");
			for (i = 0; i < selected_msg_array.length; i++){
				var tr = Element(selected_msg_array[i]+'_s'+id_border);
				if(tr.getAttribute('name') == current_folder)
					remove_currente_folder += tr.id.replace(/_[a-zA-Z0-9]+/,"")+',';
				
				if ((action == 'delete' && tr.getAttribute('name') == mount_url_folder(["INBOX",special_folders["Trash"]])) || !(parseInt(preferences.save_deleted_msg)))
				{
					msg_to_delete +=   ','+special_folders['Trash']+';'+tr.id.replace(/_[a-zA-Z0-9]+/,"");
				}
				else if (!(tr.getAttribute('name') == new_folder && action != 'delete'))
				{
					msg_to_move = (tr.getAttribute('name') == null?get_current_folder():tr.getAttribute('name'));
					selected_param += ','+msg_to_move+';'+tr.id.replace(/_[a-zA-Z0-9]+/,"");
				}else{
					write_msg(get_lang('At least one message have the same origin'));
					return false;
				}
			}
		}
		else
		{
			var tr=Element(selected_messages+'_s'+id_border);
			if(tr.getAttribute('name') == current_folder)
					remove_currente_folder += tr.id.replace(/_[a-zA-Z0-9]+/,"")+',';
			if((action == 'delete' && tr.getAttribute('name') == mount_url_folder(["INBOX",special_folders["Trash"]])) || !(parseInt(preferences.save_deleted_msg))){
				msg_to_delete = special_folders['Trash']+';'+tr.id.replace(/_[a-zA-Z0-9]+/,"");
			}else if (!(tr.getAttribute('name') == new_folder && action != 'delete')){
				trfolder = (tr.getAttribute('name') == null?get_current_folder():tr.getAttribute('name'));
				selected_param=trfolder+';'+tr.id.replace(/_[a-zA-Z0-9]+/,"");
			}else{
				write_msg(get_lang('The origin folder and the destination folder are the same.'));
				return false;
			}
		}
		var params = "";
		if(msg_to_delete != "" && msg_to_move != ""){
			params += "&selected_messages_move="+url_encode(selected_param);
			params += "&new_folder_move="+url_encode(new_folder);
			params += "&new_folder_name_move="+url_encode(new_folder_name);
		
			new_folder = mount_url_folder(["INBOX",special_folders["Trash"]]);
			new_folder_name = special_folders['Trash'];
			params += "&selected_messages_delete="+url_encode(msg_to_delete);
			params += "&new_folder_delete="+url_encode(new_folder);
			cExecute ("$this.imap_functions.move_delete_search_messages", handler_move_search_msgs, params);
		}else if(msg_to_delete != ""){
			new_folder = mount_url_folder(["INBOX",special_folders["Trash"]]);
			new_folder_name = special_folders['Trash'];
			params += "&delete=true";
			params += "&selected_messages="+url_encode(msg_to_delete);
			params += "&new_folder="+url_encode(new_folder);
			cExecute ("$this.imap_functions.move_search_messages", handler_move_search_msgs, params);
		}else{
			params = "&selected_messages="+url_encode(selected_param);
			params += "&delete=false";
			params += "&new_folder="+url_encode(new_folder);
			params += "&new_folder_name="+url_encode(new_folder_name);
			cExecute ("$this.imap_functions.move_search_messages", handler_move_search_msgs, params);
		}
	}
	else
		write_msg(get_lang('No selected message.'));
}

function move_msgs2(folder, msgs_number, border_ID, new_folder, new_folder_name,show_success_msg, not_opem_previus, prev_message){
	not_opem_previus = typeof(not_opem_previus) != 'undefined' ? not_opem_previus : false;
	var folder_error = new_folder_name;
        if( preferences.use_local_messages == 1 && expresso_local_messages.isArchiving( msgs_number, folder ) ){
		alert( get_lang("Impossible to move messages that are still being archived.") );
	    return;
	}

	if (! folder || folder == 'null')
		folder = Element("input_folder_"+msgs_number+"_r") ? Element("input_folder_"+msgs_number+"_r").value : (openTab.imapBox[currentTab] ? openTab.imapBox[currentTab]:get_current_folder());
	if(openTab.type[currentTab] == 1)
		return move_search_msgs('content_id_'+currentTab,new_folder,new_folder_name);

	var handler_move_msgs = function(data){
		if(typeof(data) == 'string')
			if (data.match(/^(.*)TRYCREATE(.*)$/)){
				var move_to_folder = data.match(/^(.*)Spam(.*)$/) ? "Spam" : special_folders['Trash'];
				alert(get_lang('There is not %1 folder, Expresso is creating it for you... Please, repeat your request later.', folder_error));
				ttree.FOLDER = 'root';
				create_new_folder(move_to_folder,"INBOX");
				return false;
			}else{
				write_msg(get_lang('Error moving message.')+" "+get_lang('Permission denied to folder "%1".', new_folder_name));
				/*Verifica se a pasta destino é a "Trash" e se a pasta origem e destino são do mesma estrutura compartilhada*/
				if(new_folder_name == "Trash" && folder.split("/")[1] == new_folder.split("/")[1]){
					alert(get_lang("You can not remove the message with the preference 'Send to Trash' enabled. There is no permission to move messages to the trash folder."));
				}
				return false;
			}
		//Este bloco verifica as permissoes ACL sobre pastas compartilhadas
		if(data.status == false){
			write_msg(get_lang("You don't have permission for this operation in this shared folder!"));
			return false;
		}

		mail_msg = ( Element("divScrollMain_"+numBox) ) ? Element("divScrollMain_"+numBox).firstChild.firstChild : Element("divScrollMain_0").firstChild.firstChild;

        var showMsg = function(){

            //Se pref. usar mensagens locais ativada e não for operação de arquivamento exibe mensagem de remoção.
            if(preferences.use_local_messages == 1){
                if(!expresso_mail_archive.isArchiveOperation){
                    if (data.msgs_number.length == 1 || typeof(data.msgs_number) == 'string' )
                        write_msg(get_lang("The message was moved to folder ") + lang_folder(data.new_folder_name));
                    else
                        write_msg(get_lang("The messages were moved to folder ") + lang_folder(data.new_folder_name));
                }
            } else {
                if (data.msgs_number.length == 1 || typeof(data.msgs_number) == 'string' )
                    write_msg(get_lang("The message was moved to folder ") + lang_folder(data.new_folder_name));
                else
                    write_msg(get_lang("The messages were moved to folder ") + lang_folder(data.new_folder_name));
            }
        }

		if (openTab.type[currentTab] > 1)
		{
			msg_to_delete = Element(msgs_number) || make_tr_message(onceOpenedHeadersMessages[folder][msgs_number],folder);
			if (parseInt(preferences.delete_and_show_previous_message) && msg_to_delete)
			{
				if (msg_to_delete.previousSibling)
				{
					var previous_msg = prev_message ? prev_message : msg_to_delete.previousSibling.id;
					
					//cExecute("$this.imap_functions.get_info_msg&msg_number="+previous_msg+"&msg_folder=" + current_folder, show_msg);
					if(!not_opem_previus){
						if(previous_msg){
							/*
							$.ajax({
								  url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
													  msg_number: previous_msg, 
													  msg_folder: folder,
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
							});*/
							proxy_mensagens.get_msg(previous_msg,folder,null,show_msg);
						}
					}
				}
				//se houver pagina anterior a paginação deve ser refeita
				else 
				{
					var border_id = $("#border_id_"+currentTab).prev().attr("id").split("_").slice(2, 3).join("")
					var folderName = current_folder;
					if(prev_message || current_page > 1)
					{
						/*
						$.ajax({
							url: 'controller.php?' + $.param({
								action: '$this.imap_functions.get_info_msg',
								msg_number: prev_message, 
								msg_folder: folderName,
								decoded: true 
							}),
							success: function( data ){
								data = connector.unserialize( data );
								delete_border(currentTab,'false');
								if( data )
								{
									show_msg( data );
								}
							},
							beforeSend: function( jqXHR, settings ){
								connector.showProgressBar();
							},
							complete: function( jqXHR, settings ){
								connector.hideProgressBar();
							}
						});*/
				        current_page--;
				        var range_begin = (current_page - 1)*preferences.max_email_per_page + 1; 
				        var range_end = current_page*preferences.max_email_per_page;
				     	var creatBoxAnterior = function (data){
					    	  draw_box(data, get_current_folder());
						      lastRow();
						 };   			  
			         	proxy_mensagens.messages_list(get_current_folder(),range_begin,range_end,sort_box_type,search_box_type,sort_box_reverse,'','', creatBoxAnterior);
					}
					else 
					{
						delete_border(currentTab,'false');
					}
				}
			}
			else
			{
				if (msg_to_delete.id === String(currentTab).split('_')[0])
				{
					delete_border(currentTab,'false');
				}
			}
			
			if(msg_to_delete && Element(msg_to_delete.id))
			{
				mail_msg.removeChild(msg_to_delete);
			}
			if (preferences.use_shortcuts == '1' && msg_to_delete && !msg_to_delete.previousSibling){
				if( $("#tbody_box .current_selected_shortcut_msg").length == 0 ){
					select_msg('null','reload_msg','null');
				}
			}
			// Update Box BgColor
			var box = Element("tbody_box");
			if(box.childNodes.length > 0)
			{
				updateBoxBgColor(box.childNodes);
			}
			if(folder == get_current_folder())
			{
				Element('tot_m').innerHTML = parseInt(Element('tot_m').innerHTML) - 1;
			}

            showMsg();
			return;
		}

		Element('chk_box_select_all_messages').checked = false;
		if (! mail_msg)
				mail_msg = Element("tbody_box");
		data.msgs_number = data.msgs_number.split(",");

		var msg_to_delete;
		if( typeof(msgs_number) == 'string' )
			all_search_msg = msgs_number.split(',');
		else if( typeof(msgs_number) == 'number')
			all_search_msg = msgs_number;

		for (var i=0; i <= all_search_msg.length; i++)
		{
			msg_to_delete = Element(folder+';'+all_search_msg[i]);
			if (msg_to_delete)
				msg_to_delete.parentNode.removeChild(msg_to_delete);
		}

		if ( preferences.use_shortcuts == '1') {
			var all_messages = Element('tbody_box').childNodes;
			// verificar se a msg selecionada com o checkbox é a mesma selecionada com o shortcut
			var msg_list_shortcut = get_selected_messages_shortcut().split(',');
			if(data.msgs_number.length > 0 && msg_list_shortcut.length > 0 && data.msgs_number.toString() == msg_list_shortcut.toString()){
				//Last msg is selected
				if ( exist_className(all_messages[all_messages.length-1], 'selected_shortcut_msg') ) {
					select_msg('null', 'up', true);
				}
				else {
					if (!select_msg('null', 'down', true)) {
						select_msg('null', 'up', true);
					}
				}
			}
		}
		for (var i=0; i<data.msgs_number.length; i++)
		{
			msg_to_delete = Element(data.msgs_number[i]);
			if (msg_to_delete)
				mail_msg.removeChild(msg_to_delete);	
		}

        showMsg();

		if (data.border_ID.indexOf('r') != -1){
			if (parseInt(preferences.delete_and_show_previous_message) && folder == get_current_folder()){
				delete_border(data.border_ID,'false');
				show_msg(data.previous_msg);
				}
			else{
                delete_border(data.border_ID,'false');
            }
				
		}
		if(folder == get_current_folder()){
			var n_total_msg = parseInt(Element('tot_m').innerHTML) - data.msgs_number.length;
			n_total_msg = n_total_msg >= 0 ? n_total_msg : 0;
			draw_paging(n_total_msg);
			Element('tot_m').innerHTML = n_total_msg;
            // removido decremento da variável pois estava decrementando em outros lugares
			//totalFolderMsgs -= data.msgs_number.length;
		}
		refresh();
	}

	if (folder == new_folder){
		write_msg(get_lang('The origin folder and the destination folder are the same.'));
		return;
	}  

	try{
        if(Element('input_folder_'+currentTab))
            if (proxy_mensagens.is_local_folder(Element('input_folder_'+currentTab).getAttribute('value')) && !border_ID) {
                alert(get_lang("You cant manipulate local messages on search"));
                return;
            }
	} catch (e) {} 
    //Validação para recuperar o id caso não seja aba de listagem
	if (currentTab == 0 && msgs_number == "selected")
		msgs_number = get_selected_messages();
	else if (currentTab != 0 && msgs_number == "selected")
		msgs_number = currentTab.substr(0,currentTab.indexOf('_r'));

	if(openTab.type[currentTab] == 1){
		return move_search_msgs('content_id_'+currentTab,new_folder,new_folder_name);
		}

	// se a aba estiver aberta e selecionada, apenas a msg da aba é movida
	if(currentTab.toString().indexOf("_r") != -1 && currentTab == border_ID)
        {
                //se a aba for aberta atraves de uma pesquisa
                if(currentTab.toString().indexOf('_s') != -1)
                   msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf('_s'));
                else
                    msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf('_r'));
	}
//	if (msgs_number) {
//		refresh();
//	}
    if (!msgs_number){
		$("#"+get_current_folder()).find('span:first').addClass('selected');
	}

	if (proxy_mensagens.is_local_folder(folder)){
        
		expresso_mail_archive.update_counters = true;
        //Folder de onde sai a mensagem é local (armazenamento local)

        if(msgs_number=='selected'){
            msgs_number = get_selected_messages();
        }

        if (new_folder == 'local_root'){
            alert(get_lang("Select a folder!"));
        }
                    
        if (parseInt(msgs_number) > 0 || msgs_number.length > 0) {
            if (proxy_mensagens.is_local_folder(new_folder)){
                //esta tirando de um folder local para outro folder local
                //expresso_mail_archive.moveMessages(new_folder.substr(6), msgs_number);
                expresso_mail_archive.moveMessages(new_folder.replace('local_messages_', ''), msgs_number);
                if(currentTab != 0)
                	delete_border(border_ID);
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
                if(currentTab != 0)
                	delete_border(border_ID);
            }
        }
        else{
            write_msg(get_lang('No selected message.'));
        }

	}
	else{
        if (proxy_mensagens.is_local_folder(new_folder)){
            //esta tirando de um folder não local para um folder local (arquivamento)
            if(msgs_number=='selected'){
                archive_msgs(folder, new_folder);
            }
            else{
                archive_msgs(folder, new_folder, msgs_number);
            }
        }
        else{
            //esta tirando de um folder não local para outro folder não local (move)
            //move_msgs(folder, msgs_number, border_ID, new_folder, new_folder_name);
			if (parseInt(msgs_number) > 0 || msgs_number.length > 0){
				// remove a flag $FilteredMessage da mensagem, depois move

				var handler_removeFlag = function(){
					$.ajax({
						type: "POST",
						url: 'controller.php?action=$this.imap_functions.move_messages',
						data: {
						    folder: folder,
						    msgs_number: ""+msgs_number,
						    border_ID: border_ID,
						    sort_box_type: sort_box_type,
						    search_box_type: search_box_type,
						    sort_box_reverse: sort_box_reverse,
						    reuse_border: border_ID,
						    new_folder: new_folder,
						    new_folder_name: new_folder_name,
						    get_previous_msg: ( !not_opem_previus ? preferences.delete_and_show_previous_message : false ) ? 1 : 0,
						    decoded: true },
						async: true,
						success: function( data ){
						    data = connector.unserialize( data );
						    $.each(msgs_number.split(","), function(index, value){
						    	delete selectedPagingMsgs[value];
						    	msgFolder =  Base64.encode(get_current_folder());
						    	tabToRemove = value + "_r_" + msgFolder.replace(/=/g,"");
						    	if ($("#"+tabToRemove)){
						    		//delete_border(tabToRemove,'false');
						    	}
						    });
						    totalFolderMsgs -= msgs_number.split(",").length;

						    //TESTE
						    selectAllFolderMsgs();
			       			$('.select-link').unbind('click');

						    if( data )
							handler_move_msgs( data );
						},
						beforeSend: function( jqXHR, settings ){
						  	connector.showProgressBar();
					    },
					    complete: function( jqXHR, settings ){
						  	connector.hideProgressBar();
					  }

				    });
				}
				var hasFolder = false;
                if(preferences['use_alert_filter_criteria'] == "1")
                {
                    $.each(fromRules, function(index, value) {
                        if(value == folder){
                            hasFolder = true;
                            cExecute ("$this.imap_functions.removeFlagMessagesFilter&folder="+folder+"&msg_number="+msgs_number, handler_removeFlag);
                            return false;
                        }
                    });
                }
				if(!hasFolder){
					handler_removeFlag();
				}
				
			}else
				write_msg(get_lang('No selected message.'));
        }
    }	

    //Código adicionado para o correto funcionamento da seleção independente de paginação.
    $.each(msgs_number.split(","), function(index, value){
        delete selectedPagingMsgs[value];
    });
    // removido decremento da variável pois estava decrementando em outros lugares
    //totalFolderMsgs = totalFolderMsgs - msgs_number.split(",").length;
    selectAllFolderMsgs(false);
    updateSelectedMsgs();
}

function move_msgs(folder, msgs_number, border_ID, new_folder, new_folder_name, not_opem_previus) {
	move_msgs2(folder, msgs_number, border_ID, new_folder, new_folder_name,true, not_opem_previus);
}

function normalizeMsgNumber( msgNumber ){
  
      if (msgNumber || msgNumber == 'selected')
	  msgNumber = get_selected_messages();


      // se a aba estiver aberta e selecionada, apenas a msg da aba é movida
      if(currentTab.toString().indexOf("_r") != -1)
      {
	    //se a aba for aberta atraves de uma pesquisa
	    if(currentTab.toString().indexOf('_s') != -1)
		msgNumber = currentTab.toString().substr(0,currentTab.toString().indexOf('_s'));
	    else
		msgNumber = currentTab.toString().substr(0,currentTab.toString().indexOf('_r'));
      }
      
      if (parseInt(msgNumber) <= 0 || msgNumber.length <= 0){
	    write_msg(get_lang('No selected message.'));
	    return( false );
      }
      
      return( msgNumber );

}

function archive_search_msgs(folder_dest) {
	
	var id_msgs = "";
	id_msgs = get_selected_messages_search();
	var msg_to_arquive = "";
	var messages = "";
	var id_border = currentTab.replace(/[a-zA-Z_]+/, "");

	if ( parseInt(id_msgs) <= 0 || id_msgs.length <= 0 )
	    return write_msg(get_lang('No selected message.'));
	
	if(folder_dest=='local_root' || folder_dest==null) //Caso seja o primeiro arquivamento...
	    folder_dest = 'local_Inbox';

	id_msgs = expresso_local_messages.checkArchived( id_msgs, folder_dest );

	if( !id_msgs ){
	    write_msg( get_lang("All messages have been filed earlier.") );
	    return;
	}
  
	document.getElementById("overlay").style.visibility = "visible";

	var handler_arquivar_mensagens = function(data) {
	  
	    var msgs_info = [];
	  
	    for( var i = 0; i < data.length; i++ )
		msgs_info[i] = connector.unserialize( data[i] );

	    //vejo se já tinha mensagens locais
	    var h = expresso_local_messages.has_local_mails();
	    
	    expresso_local_messages.insert_mails( msgs_info, folder_dest, function( s, f ){complete_archiving( s, f, h )} );

	    document.getElementById("overlay").style.visibility = "hidden";
	}

	id_msgs =  id_msgs.split(',');
	for (i = 0; i < id_msgs.length; i++){
		var tr = Element(id_msgs[i]+'_s'+id_border);
		msg_to_arquive = (tr.getAttribute('name') == null?get_current_folder():tr.getAttribute('name'));
		messages += ','+msg_to_arquive+';'+tr.id.replace(/_[a-zA-Z0-9]+/,"");
	}
	
	$.ajax({
		  url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
						      msg_number: messages, 
						      msg_folder: folder_dest,
						      decoded: true } ),
		  success: function( data ){
		      data = connector.unserialize( data );
		      
		      if( data )
			  handler_arquivar_mensagens( data );
		  },
		  beforeSend: function( jqXHR, settings ){
		  	connector.showProgressBar();
		  },
		  complete: function( jqXHR, settings ){
		  	connector.hideProgressBar();
		  }

	});
}


 function archive_msgs(folder,folder_dest,id_msgs) {

    if(typeof (currentTab) == "string" && currentTab.indexOf("local") != -1){  
        alert(get_lang("Unable to handle local messages from a search. This is allowed only for non-local messages."));
        return true;
    }
    write_msg(get_lang('Starting to archive messages'));

    if(currentTab.toString().indexOf("_r") != -1){
        id_msgs = currentTab.toString().substr(0,currentTab.toString().indexOf("_r"));
    }

    if(!id_msgs)
        id_msgs = get_selected_messages();

    if(folder_dest=='local_root' || folder_dest==null)//Default archive destiny = local_inbox folder
        folder_dest = 'local_inbox';
    
    if (parseInt(id_msgs) > 0 || id_msgs.length > 0){       
        //expresso_mail_archive.Archive(folder,folder_dest,id_msgs);
        window.setTimeout("expresso_mail_archive.Archive('" + folder + "','" + folder_dest + "','" + id_msgs + "')", 1);
        selectAllFolderMsgs(false);
    }
    else
        write_msg(get_lang('No selected message.'));


	/*
	if(proxy_mensagens.is_local_folder(folder)) {
		write_msg(get_lang("You cant archive local mails"));
		return;
	}

	if(currentTab.toString().indexOf("_r") != -1)
        id_msgs = currentTab.toString().substr(0,currentTab.toString().indexOf("_r"));
		
    if(currentTab.toString().indexOf("_s") != -1)
		id_msgs = currentTab.toString().substr(0,currentTab.toString().indexOf("_s"));

	if(!id_msgs){
		if (currentTab != 0 && currentTab.indexOf("search_")  >= 0){
			archive_search_msgs(folder_dest);
			return;
		}else
			id_msgs = get_selected_messages();
	}	

	if ( parseInt(id_msgs) <= 0 || id_msgs.length <= 0 )
	    return write_msg(get_lang('No selected message.'));
	
	if(folder_dest=='local_root' || folder_dest==null) //Caso seja o primeiro arquivamento...
		folder_dest = 'local_Inbox';

	id_msgs = expresso_local_messages.checkArchived( id_msgs, folder_dest );

	 if( !id_msgs ){
	      write_msg( get_lang("All messages have been filed earlier.") );
	      return;
	  }
  
	document.getElementById("overlay").style.visibility = "visible";

	var handler_arquivar_mensagens = function(data) {
	  
	    //var msgs_info = [];
	  
	    //for( var i = 0; i < data.length; i++ )
		//msgs_info[i] = connector.unserialize( data[i] );

	    //vejo se já tinha mensagens locais
	    //var h = expresso_local_messages.has_local_mails();
	    
	    //expresso_local_messages.insert_mails( msgs_info, folder_dest, function( s, f ){complete_archiving( s, f, h )} );

	    expresso_mail_archive.Archive(folder,folder_dest,id_msgs);
	    document.getElementById("overlay").style.visibility = "hidden";
	}

	$.ajax({
		  url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
						      msg_number: id_msgs, 
						      msg_folder: folder,
						      decoded: true } ),
		  success: function( data ){
		      data = connector.unserialize( data );
		      
		      if( data )
			  handler_arquivar_mensagens( data );
		  },
		  beforeSend: function( jqXHR, settings ){
		  	connector.showProgressBar();
		  },
		  complete: function( jqXHR, settings ){
		  	connector.hideProgressBar();
		  }

	});
	*/
	
}

function complete_archiving( success, fails, has_local_messages_before )
{
    var msgs_to_remove = {};
    var target = mount_url_folder(["INBOX",special_folders["Trash"],'tmpMoveToLocal']);
    
    success = expresso_local_messages.select_mail( [ 'original_id', 'original_folder' ], success );

    for (var i = 0; i < success.length; i++) {
	
	var msg_info = success[i];

	//////////////// deselecionando ////////////////
	Element('chk_box_select_all_messages').checked = false;

	if ( Element("check_box_message_" + msg_info.original_id) ) 
	{
		Element("check_box_message_" + msg_info.original_id).checked = false;
	}
	if ( Element(msg_info.original_id) )
	{
		remove_className(Element(msg_info.original_id), 'selected_msg');
	}
	/////////////////////////////////////////////////

	//As mensagens arquivadas devem ser removidas, caso o usuario tenha isso configurado.
	msgs_to_remove[ msg_info.original_folder ] = msgs_to_remove[ msg_info.original_folder ] || [];
	msgs_to_remove[ msg_info.original_folder ].push( msg_info.original_id );
    }

    if (preferences.keep_archived_messages == 0) {
	    //Remove as mensagens
	    for( var folder in msgs_to_remove ){
		if(folder != 'undefined')
			delete_msgs(folder, msgs_to_remove[folder],'null',false,true);
	    }
    }

    if( !has_local_messages_before && expresso_local_messages.has_local_mails() )
	    ttreeBox.update_folder();
    else
	    update_menu();
}

function action_msg_selected_from_search(aba, evento){
	if(evento == "delete")
		move_search_msgs(aba,'INBOX/Trash', 'Trash', 'delete');
}

function get_all_messages_search(){
	var aba = document.getElementById('content_id_'+currentTab);
	var messages = "";
	jQuery(function() {
 	    jQuery("#"+aba.id+" tr").each(function(i, main_list) { 
				messages += main_list.id.replace(/_[a-zA-Z0-9]+/,"") + ',' ;
 	    });              
 	     
	});
	if(messages.indexOf(',') == 0)
		messages = messages.substring(1,(messages.length));
	return messages.substring(0,(messages.length-1));
}

function get_selected_messages_search(){
	var aba = document.getElementById('content_id_'+currentTab);
	var selected_messages = "";
 	    jQuery("#"+aba.id+" tr").each(function(i, main_list) { 
		var check_box = main_list.firstChild.firstChild;
			if(check_box && check_box.checked) {
				selected_messages += main_list.id.replace(/_[a-zA-Z0-9]+/,"") + ',' ;
			};
 	    });              
 	     
	if (selected_messages != ""){
		if(selected_messages.indexOf(',') == 0)
			selected_messages = selected_messages.substring(1,(selected_messages.length));
		selected_messages = selected_messages.substring(0,(selected_messages.length-1));
		return selected_messages;
	}else{
		return false;
	}
}

function get_selected_messages_search_role(){
	var aba = document.getElementById('content_id_'+currentTab);
	var selected_messages = "";
 	jQuery("#"+aba.id+" tr").each(function(i, main_list) { 
	var check_box = main_list.firstChild.firstChild;
		if(check_box && check_box.checked && check_box.id != 'chk_box_select_all_messages_search') {
			selected_messages += main_list.role + ',' ;
		};
	});              

	if (selected_messages != ""){
		if(selected_messages.indexOf(',') == 0)
			selected_messages = selected_messages.substring(1,(selected_messages.length));
		selected_messages = selected_messages.substring(0,(selected_messages.length-1));
		return selected_messages;
	}else{
		return false;
	}
}

function get_selected_messages_shortcut(){
	var main;
	try{
		main = document.getElementById("divScrollMain_"+numBox).firstChild.firstChild;
	}catch(e){
	};

	if (! main)
		main = Element("tbody_box_"+numBox);

	if (! main)
		main = Element("tbody_box");

	// Get all TR (messages) in tBody.
	var main_list = main.childNodes;
	var selected_messages_by_shortcuts = '';
	var j = 0;
	for (j; j<(main_list.length); j++)
	{

		if ( exist_className(Element(main_list[j].id), 'selected_shortcut_msg') )
		{
			selected_messages_by_shortcuts += main_list[j].id + ',';
		}

	}
	selected_messages_by_shortcuts = selected_messages_by_shortcuts.substring(0,(selected_messages_by_shortcuts.length-1));

	return selected_messages_by_shortcuts;

}

/*function get_selected_messages(){
	var main;
	try{
		main = document.getElementById("divScrollMain_"+numBox).firstChild.firstChild;
	}catch(e){
	};

	if (! main)
		main = Element("tbody_box_"+numBox);

	if (! main)
		main = Element("tbody_box");

	// Get all TR (messages) in tBody.
	var main_list = main.childNodes;

	var _tab_prefix = getTabPrefix();
	var selected_messages = '';
	var selected_messages_by_shortcuts = '';
	var j = 0;
	for (j; j<(main_list.length); j++)
	{

		if ( (!isNaN(parseInt(numBox))) && (numBox == 0)) { 
                        check_box = Element("check_box_message_" + main_list[j].id); 
                } else { 
                        id_mensagem = main_list[j].id.split('_'); 
						check_box = Element("search_" + numBox + "_check_box_message_" + id_mensagem[0]); 
                }        
 		                 
 		if ( (check_box) && (check_box.checked) ) 
			selected_messages += main_list[j].id + ',';

		if (preferences.use_shortcuts == '1')
		{
			if ( exist_className(Element(main_list[j].id), 'selected_shortcut_msg') )
			{
				selected_messages_by_shortcuts += main_list[j].id + ',';
			}
		}
	}
	selected_messages = selected_messages.substring(0,(selected_messages.length-1));

 		         
 		        /* Verifica se está na tela de pesquisa. */ /*
 		        if(selected_messages.indexOf("_") != -1) 
 		        { 
 		                results_search_messages = selected_messages; 
 		                /* Retira a informação da aba */ /*
 		                selected_messages = selected_messages.substring(0,selected_messages.indexOf("_"));
	}
	

	if (preferences.use_shortcuts == '1')
	{
		selected_messages_by_shortcuts = selected_messages_by_shortcuts.substring(0,(selected_messages_by_shortcuts.length-1));

		var array_selected_messages_by_shortcuts = selected_messages_by_shortcuts.split(",");
		var array_selected_messages = selected_messages.split(",");
		/*
		if ((array_selected_messages.length == 0) && (array_selected_messages_by_shortcuts.length > 0))
		{
			return selected_messages_by_shortcuts;
		}*/
		/*Se houver mais de uma mensagem selecionada por atalho*//*
		if (array_selected_messages_by_shortcuts.length > 1){
			if (selected_messages == "")
			   return selected_messages_by_shortcuts;
			else 
			   return selected_messages + "," + selected_messages_by_shortcuts;
		}
	}	
	if (selected_messages == '')
		return false;
	else
		return selected_messages;
}*/

function get_selected_messages(){
	var selectedMsg = new Array();
	$.each(selectedPagingMsgs, function(index, value){
		if(value)
			selectedMsg.push(index);
	});
	return ""+selectedMsg;
}
function clear_selected_messages(){
    selectedPagingMsgs = {};
}

function replaceAll(string, token, newtoken) {
	while (string.indexOf(token) != -1) {
 		string = string.replace(token, newtoken);
	}
	return string;
}

function new_message_to(email) {
	var new_border_ID = new_message('new','null');		
	if (!new_border_ID){
		var msgId;
		setTimeout(function(){
			msgId = $('#border_tr td.menu-sel').attr('id').toString();
			content = $("#content_id_"+msgId.substr(msgId.lastIndexOf("_")+1,msgId.length));
			/*Envio de email para um grupo*/
			if (email.indexOf('@') == -1){
				var groups = REST.get('/groups');
					$.each(normalizeContacts(groups.collection.itens),function(index,group){
						if (group.name.toLocaleLowerCase() == email.toLocaleLowerCase()){
							email = group.id;
						}
					});
				draw_email_box(email, content.find('.to').filter('textarea:first'),"G");	
			}
			else
				draw_reply_boxes_by_field("to",email,content);
		},500);
	}
	else{
		var content = $("#content_id_"+new_border_ID); 
		if (email.indexOf('@') == -1){
			var groups = REST.get('/groups');
				$.each(normalizeContacts(groups.collection.itens),function(index,group){
					if (group.name.toLocaleLowerCase() == email.toLocaleLowerCase()){
						email = group.id;
					}
				});
			draw_email_box(email, content.find('.to').filter('textarea:first'),"G");	
		}
		else
			draw_reply_boxes_by_field("to",email,content);
	}	
}

function new_message(type, border_ID, flagged)
{
    action_tab_id = border_ID.replace('_r', '');
    if (openTab.type[action_tab_id])
    {
        // if there's a reply already open, just switch to it
        if (openTab.type[action_tab_id] == tabTypes[type])
        {
            alternate_border(action_tab_id);
            resizeWindow();
            return action_tab_id;
        }
        else
        {
            // if not, ask the user:
            var a_types = {
                6: get_lang("Forward"),
                7: get_lang("Reply"),
                8: get_lang("Reply to all with history"),
                9: get_lang("Reply without history"),
                10: get_lang("Reply to all without history")
            };
            var response = confirm(
                get_lang(
                    "Your message to %1 has not been saved or sent. " +
                    "To %2 will be necessary open it again. Discard your message?",
                    a_types[openTab.type[action_tab_id]].toUpperCase(),
                    a_types[tabTypes[type]].toUpperCase()
                )
           );
           if (response)
           {
               alternate_border(action_tab_id);
               resizeWindow();
               delete_border(currentTab);
           }
           else
           {
               return action_tab_id;
           }
        }
    }

    if (RichTextEditor.editorReady === false) return false;

    RichTextEditor.editorReady = false;


    if (Element('show_img_link_' + border_ID))
    {
        show_msg_img(border_ID.match(/^\d*/)[0], Element('input_folder_' + border_ID).value);
    }
    var new_border_ID = draw_new_message((type == 'new') ? parseInt(border_ID.replace('_r','')) : border_ID.replace('_r',''));

    // Does this block has any purpose at all?
    if (typeof (openTab.type[new_border_ID]) != "undefined")
    {
        if (tabTypes[type] == openTab.type[new_border_ID])
        {
            if (type != 'edit')
            {
                delete_border(currentTab);
                new_border_ID = draw_new_message(border_ID);
            }
        }
    }
    if (new_border_ID == 'maximo')
    {
        RichTextEditor.editorReady = true;
        return false;
    }
    if (new_border_ID == false)
    {
        RichTextEditor.editorReady = true;
        setTimeout('new_message(\'' + type + '\',\'' + border_ID + '\',\'' + flagged + '\');', 500);
        return false;
    }
    openTab.type[new_border_ID] = tabTypes[type];

    // Salva a pasta da mensagem respondida ou encaminhada:
    var folder_message = Element("input_folder_" + border_ID);
    if (folder_message)
    {
        var input_current_folder = document.createElement('input');
        input_current_folder.id = "new_input_folder_" + border_ID;
        input_current_folder.name = "input_folder";
        input_current_folder.type = "hidden";
        input_current_folder.value = folder_message.value;
        Element("content_id_" + new_border_ID).appendChild(input_current_folder);
    } //Fim.
    var title = '';
    data = [];


    if (Element("from_" + border_ID))
    {
        if (document.getElementById("reply_to_" + border_ID))
        {
            data.to = document.getElementById("reply_to_values_" + border_ID).value;
            data.to = data.to.replace(/&lt;/gi, "<");
            data.to = data.to.replace(/&gt;/gi, ">");
        }
        else
        {
            if (document.getElementById("sender_values_" + border_ID))
            {
                data.to = document.getElementById("sender_values_" + border_ID).value;
                data.to = data.to.replace(/&lt;/gi, "<");
                data.to = data.to.replace(/&gt;/gi, ">");
            }
            else if (document.getElementById("from_values_" + border_ID))
            {
                data.to = document.getElementById("from_values_" + border_ID).value;
                data.to = data.to.replace(/&lt;/gi, "<");
                data.to = data.to.replace(/&gt;/gi, ">");
            }
        }
        if (document.getElementById("to_values_" + border_ID))
        {
            data.to_all = document.getElementById("to_values_" + border_ID).value;
            data.to_all_alternative = document.getElementById("user_email_alternative").value;
            data.to_all = data.to_all.replace(/\n/gi, " ");
            data.to_all = data.to_all.replace(/&lt;/gi, "<");
            data.to_all = data.to_all.replace(/&gt;/gi, ">");
            var _array_to_all = data.to_all.split(",");
            var _array_to_alternative = data.to_all_alternative.split(",");
        }
    }
    if (document.getElementById("cc_" + border_ID))
    {
        data.cc = document.getElementById("cc_values_" + border_ID).value;
        data.cc = data.cc.replace(/&lt;/gi, "<");
        data.cc = data.cc.replace(/&gt;/gi, ">");
        var _array_cc = data.cc.split(",");
    }
    if (document.getElementById("cco_" + border_ID))
    {
        data.cco = document.getElementById("cco_values_" + border_ID).value;
        data.cco = data.cco.replace(/&lt;/gi, "<");
        data.cco = data.cco.replace(/&gt;/gi, ">");
    }
    if ($("#subject_" + border_ID)) data.subject = $("#subject_" + border_ID).text();
    if (data.subject == get_lang("(no subject)   ")) data.subject = '';
    if (document.getElementById("body_" + border_ID)) data.body = document.getElementById("body_" + border_ID).innerHTML;
    if (document.getElementById("from_values_" + border_ID)) data.from = document.getElementById("from_values_" + border_ID).value;
    if (Element('date_' + border_ID))
    {
        data.date = Element('date_' + border_ID).innerHTML;
    }
    if (Element('date_day_' + border_ID))
    {
        data.date_day = Element('date_day_' + border_ID).value;
    }
    if (Element('date_hour_' + border_ID))
    {
        data.date_hour = Element('date_hour_' + border_ID).value;
    }

    var signature = RichTextEditor.getSignatureDefault();

    if (type != "new" && type != "edit" && document.getElementById("is_local_" + border_ID) != null) data.is_local_message = (document.getElementById("is_local_" + border_ID).value == "1") ? true : false;

    if (typeof ($.fn.elastic) == "undefined")
    {
        $.lazy(
        {
            src: '../prototype/plugins/jquery-elastic/jquery.elastic.source.js',
            name: 'elastic'
        });
    }
    var content = $("#content_id_" + new_border_ID);

    //It is verifying if the message was modified or not.
    var btnSaveVerify = function ()
    {
        $("#content_id_" + currentTab + " .save").button("disable");

        dataBtn = new Array(".to", ".cc", ".cco", "input[name=input_subject]", ".reply-to");

        for (var i in dataBtn)
        {
            $("#content_id_" + currentTab + " " + dataBtn[i]).keydown(function ()
            {
                $("#content_id_" + currentTab + " .save").button("enable");
                autoSaveControl.status[currentTab] = false;
            });
        }

    }

    switch (type)
    {
        case "reply_without_history":
            btnSaveVerify();

            RichTextEditor.replyController = true; //Seta o editor como modo reply
            content.find('[name="input_to"]').val(data.to);

            draw_reply_boxes_by_field("to", data.to, content);

            title = "Re: " + html_entities(data.subject);
            content.find(".subject").val("Re: " + data.subject);
            useOriginalAttachments(new_border_ID, border_ID);
            content.find('[name="msg_reply_from"]').val($("#msg_number_" + border_ID).val());

            // Insert the signature automaticaly at message body if use_signature preference is set
            if (preferences.use_signature == "1")
            {
                RichTextEditor.setInitData(new_border_ID, '<div><br type="_moz"></div>' + signature);
            }
            break;
        case "reply_with_history":
            btnSaveVerify();

            RichTextEditor.replyController = true; //Seta o editor como modo reply 
            title = "Re: " + html_entities(data.subject);
            content.find(".subject").val("Re: " + data.subject);
            content.find('[name="input_to"]').val(data.to);

            draw_reply_boxes_by_field("to", data.to, content);
            content.find('[name="msg_reply_from"]').val($("#msg_number_" + border_ID).val());
            block_quoted_body = make_body_reply(data.body, data.from, data.date_day, data.date_hour);

            useOriginalAttachments(new_border_ID, border_ID);

            // Insert the signature automaticaly at message body if use_signature preference is set
            if (preferences.use_signature == "1")
            {
                var body_text = '<div><br type="_moz"></div>' + signature + '<div><br type="_moz"></div>' + block_quoted_body;
                if (preferences.plain_text_editor == "1")
                {
                    body_text = "\n\n" + remove_tags(body_text);
                    $("#body_" + new_border_ID).val(body_text, true);
                }
                else
                {
                    RichTextEditor.setInitData(new_border_ID, body_text);
                }
            }
            else
            {
                body_text = '<div><br type="_moz"></div>' + block_quoted_body;
                if (preferences.plain_text_editor == "1")
                {
                    body_text = "\n\n" + remove_tags(body_text);
                    $("#body_" + new_border_ID).val(body_text, true);
                }
                else
                {
                    RichTextEditor.setInitData(new_border_ID, body_text);
                }
            }
            break;
        case "reply_to_all_without_history":
            btnSaveVerify();

            RichTextEditor.replyController = true; //Seta o editor como modo reply
            // delete user email from to_all array.
            data.to_all = new Array();
            data.to_all = removeUserEmail(_array_to_all);
            data.to_all = removeAlternative(data.to_all, _array_to_alternative);
            content.find('[name="msg_reply_from"]').val($("#msg_number_" + border_ID).val());

            data.to_all = data.to_all.join(",");

            title = "Re: " + html_entities(data.subject);
            content.find(".subject").val("Re: " + data.subject);

            if (data.to.indexOf(Element("user_email").value) > 0)
            {
                draw_reply_boxes_by_field("to", data.to_all, content);
                content.find('[name="input_to"]').val(data.to_all);
            }
            else
            {
                draw_reply_boxes_by_field("to", data.to + ',' + data.to_all, content);
                content.find('[name="input_to"]').val(data.to + ',' + data.to_all);
            }

            if (data.cc)
            {
                data.cc = new Array();
                data.cc = removeUserEmail(_array_cc);
                data.cc = removeAlternative(data.cc, _array_to_alternative);
                if (data.cc != get_lang("undisclosed-recipient")) data.cc = data.cc.join(",");
                else data.cc = "";
                if (data.cc != "")
                {
                    content.find('[name="input_cc"]').val(data.cc);
                    input_binds(content.find('[name="input_cc"]').parent(), new_border_ID);
                    content.find(".cc-tr").show(); //cc-button
                    //document.getElementById("a_cc_link" + new_border_ID).value = data.cc;
                    content.find(".cc-button").toggleClass("expressomail-button-icon-ative");
                    content.find(".cc-button").find("span").html("Remover CC");
                    draw_reply_boxes_by_field("cc", data.cc, content);
                }
            }

            useOriginalAttachments(new_border_ID, border_ID);
            if (preferences.use_signature == "1")
            {
                RichTextEditor.setInitData(new_border_ID, '<div><br type="_moz"></div>' + signature, true);
            }

            break;
        case "reply_to_all_with_history":
            btnSaveVerify();

            RichTextEditor.replyController = true; //Seta o editor como modo reply 
            //delete user email from to_all array.
            data.to_all = new Array();
            data.to_all = removeUserEmail(_array_to_all);
            data.to_all = removeAlternative(data.to_all, _array_to_alternative);
            content.find('[name="msg_reply_from"]').val($("#msg_number_" + border_ID).val());

            if (data.to_all != get_lang("undisclosed-recipient")) data.to_all = data.to_all.join(",");
            else data.to_all = "";

            title = "Re: " + html_entities(data.subject);

            if (data.to.indexOf(Element("user_email").value) > 0)
            {
                draw_reply_boxes_by_field("to", data.to_all, content);
                content.find('[name="input_to"]').val(data.to_all);
            }
            else
            {
                draw_reply_boxes_by_field("to", data.to + ',' + data.to_all, content);
                content.find('[name="input_to"]').val(data.to + ',' + data.to_all);
            }

            if (data.cc)
            {
                data.cc = new Array();
                data.cc = removeUserEmail(_array_cc);
                data.cc = removeAlternative(data.cc, _array_to_alternative);
                if (data.cc != get_lang("undisclosed-recipient")) data.cc = data.cc.join(",");
                else data.cc = "";
                if (data.cc != "")
                {
                    content.find('[name="input_cc"]').val(data.cc);
                    input_binds(content.find('[name="input_aux_cc"]').parent(), new_border_ID);
                    content.find(".cc-tr").show();
                    content.find(".cc-button").toggleClass("expressomail-button-icon-ative");
                    content.find(".cc-button").find("span").html("Remover CC");

                    draw_reply_boxes_by_field("cc", data.cc, content);
                }
            }
            content.find(".subject").val("Re: " + data.subject);

            block_quoted_body = make_body_reply(data.body, data.from, data.date_day, data.date_hour);

            useOriginalAttachments(new_border_ID, border_ID);

            if (preferences.use_signature == "1")
            {
                var body_text = '<div><br type="_moz"></div><div><br type="_moz"></div>' + signature + '<div><br type="_moz"></div>' + block_quoted_body;
                if (preferences.plain_text_editor == "1")
                {
                    body_text = "\n\n" + remove_tags(body_text);
                    $("#body_" + new_border_ID).val(body_text, true);
                }
                else
                {
                    RichTextEditor.setInitData(new_border_ID, body_text);
                }
            }
            else
            {
                var body_text = '<div><br type="_moz"></div><div><br type="_moz"></div>' + block_quoted_body;
                if (preferences.plain_text_editor == "1")
                {
                    body_text = "\n\n" + remove_tags(body_text);
                    $("#body_" + new_border_ID).val(body_text, true);
                }
                else
                {
                    RichTextEditor.setInitData(new_border_ID, body_text);
                }
            }

            break;
        case "forward":
            btnSaveVerify();

            title = "Fw: " + html_entities(data.subject);
            content.find(".subject").val("Fw: " + data.subject);
            var divFiles = Element("divFiles_" + new_border_ID);
            var campo_arquivo;
            content.find('[name="msg_forward_from"]').val($("#msg_number_" + border_ID).val());

            if (Element("attachments_" + border_ID)) addOriginalAttachments(new_border_ID, border_ID);
            RichTextEditor.dataReady(new_border_ID, 'forward');
            // Insert the signature automaticaly at message body if use_signature preference is set
            if (preferences.use_signature == "1")
            {
                var body_text = '<div><br type="_moz"></div><div><br type="_moz"></div>' + signature + '<div><br type="_moz"></div>' + make_forward_body(data.body, data.to, data.date, data.subject, data.to_all, data.cc);
                if (preferences.plain_text_editor == "1")
                {
                    body_text = "\n\n" + remove_tags(body_text);
                    $("#body_" + new_border_ID).val(body_text);
                }
                else
                {
                    RichTextEditor.setInitData(new_border_ID, body_text);
                }
            }
            else
            {
                var body_text = '<div><br type="_moz"></div><div><br type="_moz"></div>' + make_forward_body(data.body, data.to, data.date, data.subject, data.to_all, data.cc);
                if (preferences.plain_text_editor == "1")
                {
                    body_text = "\n\n" + remove_tags(body_text);
                    $("#body_" + new_border_ID).val(body_text);
                }
                else
                {
                    RichTextEditor.setInitData(new_border_ID, body_text);
                }
            }
            RichTextEditor.dataReady(new_border_ID, 'forward');

            break;
        case "new":

            btnSaveVerify();

            title = get_lang("New Message");
            if (Element('msg_number').value)
            {
                var _to = Element('msg_number').value;
                var reEmail = /^[A-Za-z\d_-]+(\.[A-Za-z\d_-]+)*@(([A-Za-z\d][A-Za-z\d-]{0,61}[A-Za-z\d]\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/;
                if (!reEmail.test(_to))
                {
                    var array_contacts = contacts.split(',');
                    for (i = 0; i < array_contacts.length; i++)
                    {
                        if (array_contacts[i].lastIndexOf(_to) != "-1")
                        {
                            var _group = array_contacts[i].split(";");
                            _to = '"' + _group[0] + '" <' + _group[1] + '>';
                            break;
                        }
                    }
                }
                content.find('[name="input_to"]').val(_to + ',');
                draw_email_box(_to, content.find(".to").filter("input"));
                Element('msg_number').value = '';
            }
            RichTextEditor.dataReady(new_border_ID, 'new');
            // Insert the signature automaticaly at message body if use_signature preference is set
            if (preferences.use_signature == "1")
            {
                var signature_text = '<div><br type="_moz"></div><div><br type="_moz"></div>' + signature;
                if (preferences.plain_text_editor == "1")
                {
                    signature_text = "\n\n" + remove_tags(signature_text);
                    $("#body_" + new_border_ID).val(signature_text);
                }
                else
                {
                    RichTextEditor.setInitData(new_border_ID, signature_text);
                }
            }

            RichTextEditor.dataReady(new_border_ID, 'new');

            break;
        case "edit":
            btnSaveVerify();

            if (flagged == 'F') $(".important").addClass("expressomail-button-icon-ative");

            openTab.imapBox[new_border_ID] = folder_message.value;
            document.getElementById('font_border_id_' + new_border_ID).innerHTML = data.subject;
            title = "Edição: " + html_entities(data.subject);

            data.to = Element("to_values_" + border_ID).value;
            if (data.to != get_lang("without destination"))
            {
                data.to = data.to.replace(/&lt;/gi, "<");
                data.to = data.to.replace(/&gt;/gi, ">");
            }
            else
            {
                data.to = "";
            }

            draw_reply_boxes_by_field("to", data.to, content);

            content.find('[name="input_to"]').val(data.to);
            if (data.cc)
            {
                data.cc = data.cc.replace(/&lt;/gi, "<");
                data.cc = data.cc.replace(/&gt;/gi, ">");
                content.find('[name="input_cc"]').val(data.cc);
                input_binds(content.find('[name="input_cc"]').parent(), new_border_ID);
                content.find(".cc-tr").show();
                content.find(".cc-button").toggleClass("expressomail-button-icon-ative");
                content.find(".cc-button").find("span").html(get_lang('Remove CC'));
                draw_reply_boxes_by_field("cc", data.cc, content);
            }
            if (data.cco)
            {
                if (content.find('[name="input_cco"]').length)
                {
                    content.find('[name="input_cco"]').val(data.cco);
                    content.find(".cco-tr").show();
                    content.find(".cco-button").toggleClass("expressomail-button-icon-ative");
                    content.find(".cco-button").find("span").html(get_lang('Remove CCo'));
                    input_binds(content.find('[name="input_cco"]').parent(), new_border_ID);
                    draw_reply_boxes_by_field("cco", data.cco, content);
                }
            }
            content.find(".subject").val(data.subject);

            if ($("#disposition_notification_" + border_ID).length)
            {
                content.find('[name="input_return_receipt"]').attr("checked", true);
                content.find(".return-recept").toggleClass("expressomail-button-icon-ative");
                //Element("return_receipt_" + new_border_ID).checked = true;
            }

            var element_important_message = Element("important_message_" + new_border_ID);
            if (element_important_message)
            {

                if ($("#disposition_important_" + border_ID).length)
                {
                    content.find('[name="input_important_message"]').attr("checked", true);
                    content.find(".important").toggleClass("expressomail-button-icon-ative");
                }
            }

            if (Element("attachments_" + border_ID)) addOriginalAttachments(new_border_ID, border_ID);

            if (preferences.plain_text_editor == "1")
            {
                data.body = remove_tags(data.body);
                $("#body_" + new_border_ID).val(data.body);
            }
            else
            {
                RichTextEditor.setInitData(new_border_ID, data.body, 'edit');
            }

            uidsSave[new_border_ID].push(new_border_ID);
            close_delete(border_ID);

            break;
        default:
    }

    var txtarea = $('#body_' + new_border_ID);
    var height = document.body.scrollHeight - 330;
    txtarea.css("overflowY", "auto");
    txtarea.css("height", height);
    $("#border_id_" + new_border_ID).attr("title", title);
    set_border_caption("border_id_" + new_border_ID, title);
    resizeWindow();
    return new_border_ID; //Preciso retornar o ID da nova mensagem.
}

//DESENHA OS RETANGULOS PARA OS E-MAIL NA OPÇÃO REPLY
function draw_reply_boxes_by_field(field, value, context){
	array = break_comma(value);
	$.each(array, function(index, value){
		draw_email_box(value, context.find("."+field).filter("textarea:first"));
	});
}

//Remove o email do usuario ao responder a todos
function removeUserEmail(emailList){
      var userEmail = Element("user_email").value;
      var array_emails = Array();
      var j = 0;
      for (var i=0;i<emailList.length;i++){
			if (emailList[i].indexOf(userEmail) < 0){
			   array_emails[j++] = emailList[i];
			}
	  }  
 return array_emails;
}

//Remove os emails alternativos ao responder a todos
function removeAlternative(value_to_all, _array_to_alternative){
	for(i = 0; i < _array_to_alternative.length; i++) {
		for(k = 0; k < value_to_all.length; k++){
			if(value_to_all[k].match(/<([^<]*)>[\s]*$/)){
				if(value_to_all[k].match(/<([^<]*)>[\s]*$/)[1].toLowerCase() == _array_to_alternative[i].toLowerCase()){
					value_to_all.splice( k , 1);
					k--;
				}
			}else if(value_to_all[k].replace(/^\s+|\s+$/g,"").toLowerCase() == _array_to_alternative[i].toLowerCase()){
					value_to_all.splice( k , 1);
					k--;
			}
		}
	}
	return value_to_all;
}

function useOriginalAttachments(new_border_ID,old_id_border)
{   
	if (Element("attachments_" + old_id_border))
    {
        var fileUploadMSG = $('#fileupload_msg'+new_border_ID);         
        var attachments = $("#attachments_" + old_id_border).find("a");	
		if(openTab.imapBox[new_border_ID].split("local").length > 1 && attachments.length > 0){
			alert(get_lang("It is not possible to use the attachments of local messages, to have access please unarchive the attachments"));
			return false;
		}
		
        var imagens = block_quoted_body.match(/<img[^>]*>/g);
		var arrayAttachments = [];
		var arrayAttachmentsA = [];
		
		//-------------------
		    for (var i = 0; i < attachments.length; i++){
                            if((attachments[i].tagName=="SPAN") || (attachments[i].tagName=="IMG") || ((attachments[i].href.indexOf("javascript:download_local_attachment")==-1)&&(attachments[i].href.indexOf("javascript:download_attachments")==-1)))
                                    continue;
                                if(attachments[i].href.split("local") > 1){
                                	var arrayAtt = attachments[i].href.replace("javascript:download_local_attachment(", "").replace(")", "").split(',');                                 
                                }else{
                                	var arrayAtt = attachments[i].href.replace("javascript:download_attachments(", "").replace(")", "").split(',');                                 
                                }
                                
                                var att = new Object();
                                var regex = new RegExp( "'", "g" );
                                att.folder = utf8_decoder(Base64.decode(arrayAtt[0].replace(regex,"")));
                                att.uid = arrayAtt[1].replace(regex,"");
                                att.part = arrayAtt[3].replace(regex,"");
                                att.type = 'imapPart';
				var idATT = JSON.stringify(att);
				
				if(block_quoted_body.indexOf('src="./inc/get_archive.php?msgFolder='+att.folder+'&amp;msgNumber='+att.uid+'&amp;indexPart='+att.part+'"') !== -1)
				{
				    addAttachment( new_border_ID , idATT);  

				    var attach = {};
				    attach.fileName =  attachments[i].text.substring(0, attachments[i].text.lastIndexOf('('));
                    attach.fullFileName = attach.fileName;
				    if(attach.fileName.length > 20)
					attach.fileName = attach.fileName.substr(0, 17) + " ... " + attach.fileName.substr(attach.fileName.length-9, attach.fileName.length);

				    attach.fileSize =  attachments[i].text.substring(( attachments[i].text.lastIndexOf('(')+1), attachments[i].text.lastIndexOf(')'));

                    attach.error = false;
                    fileUploadMSG.find(' .attachments-list').show();
				    var upload = $(DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist.ejs", {file : attach}));
				    upload.append('<input type="hidden" name="fileId[]" value=\''+idATT+'\'/>');
                    upload.find(".att-box-loading").remove();
				    upload.find('.att-box-delete').click(function(){
					    var idAttach = $(this).parent().find('input[name="fileId[]"]').val();
					    var content_body = RichTextEditor.getData('body_'+new_border_ID);
					    var imagens = content_body.match(/<img[^>]*>/g);
					    var att = JSON.parse(idAttach);
					    if(imagens != null)
					    {   
						for (var x = 0; x < imagens.length; x++)
						    if(imagens[x].indexOf('src="./inc/get_archive.php?msgFolder='+att.folder+'&amp;msgNumber='+att.uid+'&amp;indexPart='+att.part) !== -1)
							content_body = content_body.replace(imagens[x],'');

						RichTextEditor.setData('body_'+new_border_ID,content_body);    
					    }       

					    fileUploadMSG.find('.attachments-list').find('input[value="'+idAttach+'"]');
					    delAttachment(new_border_ID,idAttach); 
					    $(this).parent().qtip("destroy");
                        $(this).parent().remove();
                        if(!fileUploadMSG.find(' .attachments-list').find(".att-box").length){
                            fileUploadMSG.find(' .attachments-list').hide();
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
                            name: 'blue' // Style it according to the preset 'cream' style
                        }
                    });
				}
				else
				{   
				    arrayAttachments.push(idATT);
				    arrayAttachmentsA.push(attachments[i]);
				}
        }
		//-------------------
		
		if(arrayAttachments.length > 0)
		{
		
		    var orignialAtt = fileUploadMSG.find('.button-files-upload').append(' <button tabindex="-1" class="message-add-original-att button-small">_[[Attach original files]]</button>').find(".message-add-original-att").button();
		    orignialAtt.click(function(event ){

			for (var i = 0; i < arrayAttachments.length; i++){

				    var att = JSON.parse(arrayAttachments[i]);
				    addAttachment( new_border_ID , arrayAttachments[i]);  

				    var attach = {};
				    attach.fileName =  arrayAttachmentsA[i].text.substring(0, arrayAttachmentsA[i].text.lastIndexOf('('));
                    attach.fullFileName = attach.fileName;
				    if(attach.fileName.length > 20)
					attach.fileName = attach.fileName.substr(0, 17) + " ... " + attach.fileName.substr(attach.fileName.length-9, attach.fileName.length);

				    attach.fileSize =  arrayAttachmentsA[i].text.substring(( arrayAttachmentsA[i].text.lastIndexOf('(')+1), arrayAttachmentsA[i].text.lastIndexOf(')'));
                    attach.error = false;
                    fileUploadMSG.find(' .attachments-list').show();
				    var upload = $(DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist.ejs", {file : attach}));
				    upload.find('.att-box-loading').remove(); 
				    upload.append('<input type="hidden" name="fileId[]" value=\''+arrayAttachments[i]+'\'/>');
				    upload.find('.att-box-delete').click(function(){
					    var idAttach = $(this).parent().find('input[name="fileId[]"]').val();
					    var content_body = RichTextEditor.getData('body_'+new_border_ID);
					    var imagens = content_body.match(/<img[^>]*>/g);
					    var att = JSON.parse(idAttach);
					    if(imagens != null)
					    {   
						for (var x = 0; x < imagens.length; x++)
						    if(imagens[x].indexOf('src="./inc/get_archive.php?msgFolder='+att.folder+'&amp;msgNumber='+att.uid+'&amp;indexPart='+att.part) !== -1)
							content_body = content_body.replace(imagens[x],'');

						RichTextEditor.setData('body_'+new_border_ID,content_body);    
					    }       

					    fileUploadMSG.find('.attachments-list').find('input[value="'+idAttach+'"]');
					    delAttachment(new_border_ID,idAttach); 
					    $(this).parent().qtip("destroy");
                        $(this).parent().remove();
                        if(!fileUploadMSG.find(' .attachments-list').find(".att-box").length){
                            fileUploadMSG.find(' .attachments-list').hide();
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
                            name: 'blue' // Style it according to the preset 'cream' style
                        }
                    });
			}

		    $(this).remove();
		    });
		}

         }
                
}

function addOriginalAttachments(new_border_ID,old_id_border)
{   
    var fileUploadMSG = $('#fileupload_msg'+new_border_ID);
    var attachments = $("#attachments_" + old_id_border).find("a");			

	if(openTab.imapBox[new_border_ID].split("local").length > 1 && attachments.length > 0){
		alert(get_lang("It is not possible to use the attachments of local messages, to have access please unarchive the attachments"));
		return false;
	}

    for (var i = 0; i < attachments.length; i++){
            if((attachments[i].tagName=="SPAN") || (attachments[i].tagName=="IMG") || ((attachments[i].href.indexOf("javascript:download_local_attachment")==-1)&&(attachments[i].href.indexOf("javascript:download_attachments")==-1)))
                    continue;
                fileUploadMSG.find(' .attachments-list').show();
                if(attachments[i].href.split("local").length > 1){
                	var arrayAtt = attachments[i].href.replace("javascript:download_local_attachment(", "").replace(")", "").split(',');                                 	
                }else{
                	var arrayAtt = attachments[i].href.replace("javascript:download_attachments(", "").replace(")", "").split(',');                                 	
                }
                var att = new Object();
                var regex = new RegExp( "'", "g" );
                att.folder = utf8_decoder(Base64.decode(arrayAtt[0].replace(regex,"")));
                att.uid = arrayAtt[1].replace(regex,"");
                att.part = arrayAtt[3].replace(regex,"");
                att.type = 'imapPart';
                var idATT = JSON.stringify(att);
                addAttachment( new_border_ID , idATT);
                
                var attach = {};
                var attachText = (is_ie ? attachments[i].innerText : attachments[i].text);
                attach.fileName =  attachText.substring(0, attachText.lastIndexOf('('));
                attach.fullFileName = attach.fileName;
                if(attach.fileName.length > 20)
                    attach.fileName = attach.fileName.substr(0, 17) + " ... " + attach.fileName.substr(attach.fileName.length-9, attach.fileName.length);
                attach.fileSize =  attachText.substring((attachText.lastIndexOf('(')+1),attachText.lastIndexOf(')'));
                attach.error = false;

                var upload = $(DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist.ejs", {file : attach}));
                upload.find('.att-box-loading').remove(); 
                upload.find('.att-box-delete').click(function(){
                    var idAttach = $(this).parent().find('input[name="fileId[]"]').val();
                    var content_body = RichTextEditor.getData('body_'+new_border_ID);
                    var imagens = content_body.match(/<img[^>]*>/g);
                    var att = JSON.parse(idAttach);
                    if(imagens != null)
                    {   
                        for (var x = 0; x < imagens.length; x++)
                            if(imagens[x].indexOf('src="./inc/get_archive.php?msgFolder='+att.folder+'&amp;msgNumber='+att.uid+'&amp;indexPart='+att.part) !== -1)
                                content_body = content_body.replace(imagens[x],'');

                         RichTextEditor.setData('body_'+new_border_ID,content_body);    
                    }       

                    fileUploadMSG.find(' .attachments-list').find('input[value="'+idAttach+'"]');
                    delAttachment(new_border_ID,idAttach); 
                    $(this).parent().qtip("destroy");
                    $(this).parent().remove();
                    if(!fileUploadMSG.find(' .attachments-list').find(".att-box").length){
                        fileUploadMSG.find(' .attachments-list').hide();
                    }
                });	


                upload.append('<input type="hidden" name="fileId[]" value=\''+idATT+'\'/>');
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
                        name: 'blue' // Style it according to the preset 'cream' style
                    }
                });

    }	                
}

function send_message_return(data, ID){
	
	if (typeof(data) == 'object' && data.load){
		cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
	}
	watch_changes_in_msg(ID);

	var content = $("#content_id_"+ID);
	var sign = false;
	var crypt = false;
	var reComplexEmail = /<([^<]*)>[\s]*$/;
	if ((preferences.use_assinar_criptografar != '0') && (preferences.use_signature_digital_cripto != '0')){
		var checkSign = document.getElementById('return_digital_'+ID)
		if (checkSign.checked){
			sign = true;
		}

		var checkCript = document.getElementById('return_cripto_'+ID);
		if (checkCript.checked){
			crypt = true;
		}
	}

	if (typeof(data) == 'object' && !data.success)
	{
		connector = new  cConnector();

		if (sign || crypt){
			var operation = '';
			if (sign){
				operation = 'sign';
			}
			else { // crypt
				//TODO: Colocar mensagem de erro, e finalizar o método.
				operation = 'nop';
			}
		}

		if (data.body){
			Element('cert_applet').doButtonClickAction(operation, ID, data.body);
		}
		else {
			alert(data.error);
		}

		return;
	}
	if(data && data.success == true ){
		// if send ok, set a flag as answered or forwarded
		var msg_number_replied = content.find('[name="msg_reply_from"]');
		var msg_number_forwarded = content.find('[name="msg_forward_from"]');

		if (msg_number_replied.val()){
			proxy_mensagens.proxy_set_message_flag(msg_number_replied.val(), 'answered');
		}
		else if (msg_number_forwarded.val()){
			proxy_mensagens.proxy_set_message_flag(msg_number_forwarded.val(), 'forwarded');
		}
		if(expresso_offline){
			write_msg(get_lang('Your message was sent to queue'));
			delete_border(ID,'true');
			return;
		}else{
			if (wfolders.alert) {
				write_msg(get_lang('Your message was sent and save.'));
				wfolders.alert = false;
			}
			else {
				write_msg(get_lang('Your message was sent.'));
			}
		}

		//REFAZER ISTO COM UMA CHAMADA ASSINCRONA PARA REGISTRAR E ATUALIZAR A LISTA DOS NOVOS CONTATOS DINAMICOS
		// If new dynamic contacts were added, update the autocomplete ....
		/*if(data.new_contacts){
			var ar_contacts = data.new_contacts.split(',;');
			for(var j in ar_contacts){
				// If the dynamic contact don't exist, update the autocomplete....
				if((contacts+",").indexOf(";"+ar_contacts[j]+",") == -1)
					contacts += ",;" + ar_contacts[j];
			}
		}
		var dynamicPersonalContacts = new Array();
		var dynamicPersonalGroups = new Array();
		var dynamicContacts = new Array();
		var dynamicContactList = new Array();

		*/
		delete_border(ID,'true');
        if(parseInt(preferences.use_dynamic_contacts)){
            var arrayTo = content.find(".to-tr").find(".box").clone();
            save_dynamic_contacts(arrayTo);
            var arrayCC = content.find(".cc-tr").find(".box").clone();
            save_dynamic_contacts(arrayCC);
            var arrayCCo = content.find(".cco-tr").find(".box").clone();
            save_dynamic_contacts(arrayCCo);
            updateDynamicContact();
        }

		cache = new Array();
 	}
	else{
		if(data == 'Post-Content-Length')
			write_msg(get_lang('The size of this message has exceeded  the limit (%1B).',Element('upload_max_filesize').value));
		else if(data){
			var error_mail = $.trim(data.split(":")[data.split(":").length-1]);
			var array = content.find(".to-tr").find(".box");
			//$(value).find("input").val()

            $(content).find('button.send').button('option', 'disabled', false);

			$.each(array, function(index, value){
				if(error_mail == $(value).find("input").val().match(reComplexEmail)[1])
					$(value).addClass("invalid-email-box");
			});
			if ( content.find('[name="input_cco"]').length){
				if(content.find(".cco-tr").css("display") != "none"){
					var array = content.find(".cco-tr").find(".box");
					$.each(array, function(index, value){
						if(error_mail == $(value).find("input").val().match(reComplexEmail)[1])
							$(value).addClass("invalid-email-box");
					});
				}
			}
			if(content.find(".cc-tr").css("display") != "none")
			{
				var array = content.find(".cc-tr").find(".box");
				$.each(array, function(index, value){
					if(error_mail == $(value).find("input").val().match(reComplexEmail)[1])
						$(value).addClass("invalid-email-box");
				});				
			} 
			write_msg(data);
		}else
			write_msg(get_lang("Connection failed with %1 Server. Try later.", "Web"));
		
		var save_link = $("#content_id_"+ID).find(".save")[0];
		save_link.onclick = function onclick(event) {openTab.toPreserve[ID] = true;save_msg(ID);} ;
		$("#save_message_options_"+ID).button({disabled: false});
		//save_link.className = 'message_options';
	}
	if(!expresso_offline)
		connector.hideProgressBar();
}

/*Função que grava o destinatário nos contatos dinâmicos*/
function save_dynamic_contacts(array){
		
    $.each(array, function(i, value){
        var stop = false;
        $.each(dynamicPersonalContacts, function(x, valuex){
            if(valuex.email == $(value).find("input").val().match(reComplexEmail)[1]){
                stop = true;
                return false;
            }
        });
        $.each(dynamicPersonalGroups, function(x, valuex){
            if(valuex.email == $(value).find("input").val().match(reComplexEmail)[1]){
                stop = true;
                return false;
            }
        });
        if(!stop){
            var exist = 0;
            $.each(dynamicData, function(x, valuex){
                if(valuex.mail == $(value).find("input").val().match(reComplexEmail)[1]){
                    exist = valuex.id;
                    return false;
                }
            });
            if(exist){
                REST.put("/dynamiccontact/"+exist, {name: $(value).find("input").val().split('"')[1], mail:$(value).find("input").val().match(reComplexEmail)[1]});
            }else{
                REST.post("/dynamiccontacts", {name: $(value).find("input").val().split('"')[1], mail:$(value).find("input").val().match(reComplexEmail)[1]});
            }
        }
    });

}

/**
 * Método chamado pela applet para retornar o resultado da assinatura/decifragem do e-mail.
 * para posterior envio ao servidor.
 * @author Mário César Kolling <mario.kolling@serpro.gov.br>, Bruno Vieira da Costa <bruno.vieira-costa@serpro.gov.br>
 * @param smime O e-mail decifrado/assinado
 * @param ID O ID do e-mail, para saber em que aba esse e-mail será mostrado.
 * @param operation A operação que foi realizada pela applet (assinatura ou decifragem)
 */
function appletReturn(smime, ID, operation, folder){

	if (!smime){ // Erro aconteceu ao assinar ou decifrar e-mail
		connector = new  cConnector();
		connector.hideProgressBar();
		return;
	}

	if(operation=='decript')
	{
		var handler = function(data){

			if(data.msg_day == '')
			{
				header=expresso_local_messages.get_msg_date(data.original_ID, proxy_mensagens.is_local_folder(get_current_folder()));

				data.fulldate=header.fulldate;
				data.smalldate=header.smalldate;
				data.msg_day = header.msg_day;
				data.msg_hour = header.msg_hour;

                      }
			this.show_msg(data);
		}
		para="&source="+smime+"&ID="+ID+"&folder="+folder;
		cExecute ("$this.imap_functions.show_decript&", handler, para);
	}else
	{
		ID_tmp = ID;
		// Lá a variável e chama a nova função cExecuteForm
		// Processa e envia para o servidor web
		// Faz o request do connector novamente. Talvez implementar no connector
		// para manter coerência.

		var handler_send_smime = function(data){
			send_message_return(data, this.ID_tmp); // this is a hack to escape quotation form connector bug
		};

		var textArea = document.createElement("TEXTAREA");
		textArea.style.display='none';
		textArea.id = 'smime';
		textArea.name = "smime";
		textArea.value += smime;

		// Lá a variável e chama a nova função cExecuteForm
		// Processa e envia para o servidor web
		// Faz o request do connector novamente. Talvez implementar no connector
		// para manter coerência.
		if (is_ie){
			var i = 0;
			while (document.forms(i).name != "form_message_"+ID){i++}
			form = document.forms(i);
		}
		else
			form = document.forms["form_message_"+ID];

		form.appendChild(textArea);

		cExecuteForm ("$this.imap_functions.send_mail", form, handler_send_smime, ID);
	}
}

/* 
 * Método que verifica se existe algum item de 'words' em 'body'.
 * Se houver, retorna a primeira ocorrência encontrada de words, caso contrário retorna false.
 */
function verifyBodyWords(body, words)
{
    var forwStartPT = body.search(/\<div\>\n*\s*---------- Mensagem encaminhada ----------/);
    if(forwStartPT >= 0)
        body = body.substr(0,forwStartPT);

    var forwStartEN = body.search(/\<div\>\n*\s*---------- Forwarded message ----------/);
    if(forwStartEN >= 0)
        body = body.substr(0,forwStartEN);

    var forwStartES = body.search(/\<div\>\n*\s*---------- Mensaje reenviada ----------/);
    if(forwStartES >= 0)
        body = body.substr(0,forwStartES);

    var replyStartPT = body.search(/\<div\>\n*\s*Em (\d{1,2})\/(\d{1,2})\/(\d{4}) \&agrave\;s (\d{1,2}):(\d{1,2}) horas, (.)+ escreveu:/);
    if(replyStartPT >= 0)
        body = body.substr(0,replyStartPT);

    var replyStartEN = body.search(/\<div\>\n*\s*At (\d{1,2})\/(\d{1,2})\/(\d{4}), (\d{1,2}):(\d{1,2}) hours, (.)+ wrote:/);
    if(replyStartEN >= 0)
        body = body.substr(0,replyStartEN);

    var replyStartES = body.search(/\<div\>\n*\s*En (\d{1,2})\/(\d{1,2})\/(\d{4}) las (\d{1,2}):(\d{1,2}) horas, (.)+ escribi\&oacute\;:/);
    if(replyStartES >= 0)
        body = body.substr(0,replyStartES);

    for(i = 0; i < words.length; i++) {
		if(body.search(words[i]) != -1) {
			return words[i]; 
		}
	}

	return false;
}


function send_message(ID, folder, folder_name){
	var content_body  = RichTextEditor.getData('body_'+ID);     
	/* 
		Funcionalidade que verifica se o usuário escreveu a palavra anexo no corpo da mensagem e não anexou nenhum arquivo.
		Esta funcionalidade é ativada nas preferências do módulo ExpressoMail.
	*/	
	/* Lista de palavras que vão ser procuradas no corpo do email, referente às variantes da palavra anexo em português, inglês e espanhol. */
	if(language == "pt-br") //Português brasileiro
        var words = ['anexando', 'anexos', 'anexadas', 'anexados', 'anexei',  'anexaste', 'anexastes', 'anexamos', 'anexaram', 'anexas', 'anexado', 'anexada', 'anexo', 'anexa'];
    else if(language == "es-es") //Espanhol
		var words = ['anexo','adjunto', 'adjuntos', 'adjuntado','adjuntamos'];
	else //Inglês ('en')
        var words = ['attach', 'attachment', 'attached', 'annex', 'appending', 'appendage', 'annexe', 'appendix'];

	if($('#fileupload_msg'+ID).find('.att-box').length == 0 && preferences.alert_message_attachment == '1' ) {
		var bodyWord = verifyBodyWords(content_body, words);
		if(bodyWord) {		
			$.Zebra_Dialog(get_lang('You wrote "%1" in your message, but there are no files attached. Send it anyway?', bodyWord), {
				'type':     'question',
				'overlay_opacity': '0.5',
				'buttons':  [get_lang('Yes'), get_lang('No')],
				'width' : 500,
				'custom_class': 'custom-zebra-filter',
				'onClose':  function(clicked) {
					if(clicked == get_lang('Yes')){
						send_valided_message(ID, folder, folder_name);				
						return;
					} else {
						return;
					}
				}
			});
		}
		else
			send_valided_message(ID, folder, folder_name);	
	}
	else if(!zebraDiscardEventDialog && $('#fileupload_msg'+ID).find('.att-box-loading').length)
	{
		zebraDiscardEventDialog = true;
		window.setTimeout(function() {
			$.Zebra_Dialog('_[[Attachments are being sent to the server. If you send your message now these files will be lost.]]', {
				'type':     'question',
				'overlay_opacity': '0.5',
				'custom_class': 'custom-zebra-filter',
				'buttons':  ['_[[Discard attachments and send]]', '_[[Continue editing and wait attachments]]'],
				'width' : 500,
				'onClose':  function(clicked) {
					if(clicked == '_[[Discard attachments and send]]' ){ 
						$.each($('#fileupload_msg'+ID).find('.att-box'), function(index, value){
							if($(value).find(".att-box-loading").length)
								$(value).find('.att-box-delete').trigger("click");
						});
						send_valided_message(ID, folder, folder_name);
					}
					window.setTimeout(function() {
						zebraDiscardEventDialog = false;
					}, 500);
				}
			})
		}, 300); 
	}else {
		send_valided_message(ID, folder, folder_name);
		}
}

function send_valided_message(ID, folder, folder_name)
{ 
    if (preferences.auto_save_draft == 1)
       autoSaveControl.status[ID] = true;

    var content = $("#content_id_"+ID);
    var save_link = $("#content_id_"+ID).find(".save");
    var onClick = save_link.onclick;
    save_link.onclick = '';
    save_link.button({disabled: true});

    var _subject = trim(content.find(".subject").val());
    if((_subject.length == 0) && !confirm(get_lang("Send this message without a subject?"))) {
        save_link.click(onClick);
        content.find(".subject").focus();
        return;
    }

    var stringReply = "";
    draw_email_box(content.find(".reply-to-tr").find("textarea:first").val(), content.find(".reply-to-tr").find("textarea:first"));
    content.find(".reply-to-tr").find("textarea:first").val("");
    var array = content.find(".reply-to-tr").find(".box");
    $.each(array, function(index, value){
        stringReply += $(value).find("input").val() + ",";
    }); 
    var stringToEmail = "";
    draw_email_box(content.find(".to-tr").find("textarea:first").val() || content.find(".to-tr").find("input:visible").val(), content.find(".to-tr").find("textarea:first"));
    content.find(".to-tr").find("textarea:first").val("");
    content.find(".to-tr").find("input:visible").val("");
    var array = content.find(".to-tr").find(".box");
    $.each(array, function(index, value){
        stringToEmail += $(value).find("input").val() + ",";
    });
    var stringEmail = "";
    stringEmail = stringToEmail;
    var stringCCoEmail = "";
    if ( content.find('[name="input_cco"]').length){
        if(content.find(".cco-tr").css("display") != "none"){
            draw_email_box(content.find(".cco-tr").find("textarea:first").val() || content.find(".cco-tr").find("input:visible").val(), content.find(".cco-tr").find("textarea:first"));
            content.find(".cco-tr").find("textarea:first").val("");
            content.find(".cco-tr").find("input:visible").val("");
            var array = content.find(".cco-tr").find(".box");
            $.each(array, function(index, value){
                stringCCoEmail += $(value).find("input").val() + ",";
            });
        }
    }
    stringEmail += stringCCoEmail;
    var stringCCEmail = "";
    if(content.find(".cc-tr").css("display") != "none")
    {
        draw_email_box(content.find(".cc-tr").find("textarea:first").val() || content.find(".cc-tr").find("input:visible").val(), content.find(".cc-tr").find("textarea:first"));
        content.find(".cc-tr").find("textarea:first").val("");
        content.find(".cc-tr").find("input:visible").val("");
        var array = content.find(".cc-tr").find(".box");
        $.each(array, function(index, value){
            stringCCEmail += $(value).find("input").val() + ",";
        });
    }
    stringEmail +=  stringCCEmail;

    var mailData = new Object();
    mailData.body = Base64.encode(RichTextEditor.getData('body_'+ID));
    mailData.folder = folder;
    mailData.type = RichTextEditor.plain[ID] ? 'plain' : 'html';
    mailData.uids_save = uidsSave[ID].toString();
    mailData.save_folder = (openTab.imapBox[ID] && openTab.type[ID] < 6) ? openTab.imapBox[ID]: "INBOX" + cyrus_delimiter + draftsfolder;
    mailData.attachments = listAttachment(ID);
    mailData.messageNum = currentTab;
    mailData.input_subject = trim(content.find(".subject").val());
    mailData.input_reply_to = stringReply;
    mailData.input_to = stringToEmail;
    mailData.input_cco = stringCCoEmail;
    mailData.input_cc = stringCCEmail;
    mailData.input_cc = stringCCEmail;
    mailData.abaID = $(content).find('[name="abaID"]').val();
    mailData.input_important_message = $(content).find('input:checkbox:checked[name="input_important_message"]').val();
    mailData.input_return_receipt = $(content).find('input:checkbox:checked[name="input_return_receipt"]').val();
    mailData.msg_forward_from = $(content).find('[name="msg_forward_from"]').val();
    mailData.msg_reply_from = $(content).find('[name="msg_reply_from"]').val();
    
    if ($(content).find('select[name="input_from"]').val())
        mailData.input_from = $(content).find('select[name="input_from"]').val();
    
    if(stringEmail != ""){
    $.ajax({
        url: "controller.php?action=$this.imap_functions.send_mail",
        data:  mailData,
        type: 'POST',
        async: false,
        beforeSend: function(jqXHR, settings){
            write_msg( get_lang( 'Sending the message...' ) );
            $(content).find('button.send').button('option', 'disabled', true);
        },
        success: function(data){
            send_message_return(connector.unserialize(data),ID);
        },
        error: function(){
            write_msg(get_lang( 'Error trying to send the message. Retry in a few seconds...' ) );
            $(content).find('button.send').button('option', 'disabled', false);
            var interval = setInterval(function(){
                send_valided_message(ID,folder,folder_name);
                clearInterval(interval);
            },15000);
            return;
        }
    });
	}else{
		write_msg(get_lang("message without receiver")); 
	}
}

function is_valid_email(campo){	
	var invalidEmail = searchEmail(campo);
	var semicolon = campo.split(";");
	
	if((campo.replace(/^\s+|\s+$/g,"")) != ""){
			if(invalidEmail[0] == true){
				write_msg("_[[SMTP Error: The following recipient addresses failed:]]"+ invalidEmail[1]);
				return false;
			}else{
				if(semicolon.length > 1){
					var stringError = "_[[SMTP Error: The addresses must be separated only by commas:]]";
					for(var i= 0; i < semicolon.length; i++){
						stringError = stringError + semicolon[i];
						if(i+1 < semicolon.length)
							stringError = stringError + " ; ";
					}
					write_msg(stringError);
					return false;
				}else {
					return true;
				}
			}
	}
	else{
		write_msg(get_lang("Message without receiver"));
		return false;
	}		
}
function change_tr_properties(tr_element, newUid, newSubject){
	message_id=tr_element.id;
	var td_from = document.getElementById('td_from_'+message_id);
	if (typeof(newSubject) != 'undefined')
		td_from.nextSibling.innerHTML = newSubject;
	tr_element.id = newUid;

	var openNewMessage = function () {
		$.ajax({
			  url: 'controller.php?' + $.param( {action: '$this.imap_functions.get_info_msg',
							      msg_number: newUid, 
							      msg_folder: current_folder,
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
	};
	for (var i=2; i < 10; i++){
		if (typeof(tr_element.childNodes[i].id) != "undefined")
			tr_element.childNodes[i].id = tr_element.childNodes[i].id.replace(message_id,newUid);
		tr_element.childNodes[i].onclick = openNewMessage;
	}
}


function autoSave( ID ){
    var content = $("#content_id_"+ID);
	saveButtonDisabled = $("#content_id_"+ID).find(".save").button('option','disabled');
    if(autoSaveControl.status[ID] === false && !saveButtonDisabled)
        save_msg(ID);
}

function save_msg(border_id){

    //seta o status do auto_save = true
   if (preferences.auto_save_draft == 1)
       autoSaveControl.status[border_id] = true;
   ///////////////////////////////////////////
    var content = $("#content_id_"+border_id);
	content.find(".save").button('option','disabled',true);

    var array = content.find(".reply-to-tr").find(".box");
    var stringReplyToEmail = "";
    $.each(array, function(index, value){
        stringReplyToEmail += $(value).find("input").val() + ",";
    });
    content.find('[name="input_reply_to"]').val(stringReplyToEmail);

   	var stringEmail = "";
	var array = content.find(".to-tr").find(".box");
	$.each(array, function(index, value){
		stringEmail += $(value).find("input").val() + ",";
	});
	content.find('[name="input_to"]').val(stringEmail);
	stringEmail = "";
	if ( content.find('[name="input_cco"]').length){
		if(content.find(".cco-tr").css("display") != "none"){
			var array = content.find(".cco-tr").find(".box");
			$.each(array, function(index, value){
				stringEmail += $(value).find("input").val() + ",";
			});
			content.find('[name="input_cco"]').val(stringEmail);
		}
	}
	
	stringEmail = "";	
	if(content.find(".cc-tr").css("display") != "none")
	{
		var array = content.find(".cc-tr").find(".box");
		$.each(array, function(index, value){
			stringEmail += $(value).find("input").val() + ",";
		});
		content.find('[name="input_cc"]').val(stringEmail);
	}
	
   var idJavascript = saveBorderError[border_id];
   
   if(saveBorderError[border_id] !== false)
   	DataLayer.put('message',DataLayer.merge(DataLayer.form("#form_message_"+border_id), {id: idJavascript }));
   else
       idJavascript = DataLayer.put('message',DataLayer.form("#form_message_"+border_id));  

   uidsSave[border_id] = [];
   DataLayer.commit(false,false,function(data){
       if(data != null && data['message://'+idJavascript] !== undefined && data['message://'+idJavascript].id !== undefined )
       {
			uidsSave[border_id].push(data['message://'+idJavascript].id);
			saveBorderError[border_id] = false;
			write_msg('_[[Message saved successfully!]]');
			if(folder == 'INBOX'+cyrus_delimiter+'Drafts'){
				refresh();
			}
       }
       else
       {
       	saveBorderError[border_id] = idJavascript;
       	write_msg('_[[Error saving your message! Retry in a few seconds.]]');    
       }
   });

}

function set_messages_flag_search_local(flag)
{
	// Verificar chamadas
}

function set_messages_flag_search(flag){
	
	var id_border = currentTab.replace(/[a-zA-Z_]+/, "");
	var msgs_flag = this.get_selected_messages_search();
	if (!msgs_flag){
		write_msg(get_lang('No selected message.'));
		return;
	}
	var selected_param = "";
	msgs_to_flag = msgs_flag.split(",");
	search = true;
	for (i = 0; i < msgs_to_flag.length; i++){
		var tr = Element(msgs_to_flag[i]+'_s'+id_border);
		var msg_to_flag = (tr.getAttribute('name') == null?get_current_folder():tr.getAttribute('name'));
		selected_param += ','+msg_to_flag+';'+tr.id.replace(/_[a-zA-Z0-9]+/,"");
	}
	
	var handler_set_messages_flag = function(data){
		var errors = false;
		var notErrors = false;
		for (var i = 0; i < data.length; i++){
			var notArray = true;
			if(data[i].msgs_to_set != ''){
				var msgs = [];
				if(data[i].msgs_to_set.indexOf(',') > 0){
					msgs = data[i].msgs_to_set.split(',')
					notArray = false;
				}else
					msgs[0] = data[i].msgs_to_set;
					
				for (var j = 0; j < msgs.length; j++){
					switch(data[i].flag){
						case "unseen":
							set_msg_as_unread(msgs[j]+'_s'+id_border, true);
							set_msg_as_unread(msgs[j], true); // Atualiza msg na aba principal.
							Element("search_"+id_border+"_check_box_message_"+msgs[j]).checked = false;
							break;
						case "seen":
							set_msg_as_read(msgs[j]+'_s'+id_border, true);
							set_msg_as_read(msgs[j], true); //Atualiza msg na aba principal.
							Element("search_"+id_border+"_check_box_message_"+msgs[j]).checked = false;
							break;
						case "flagged":
							set_msg_as_flagged(msgs[j]+'_s'+id_border, true);
							document.getElementById("search_"+id_border+"_check_box_message_"+msgs[j]).checked = false;
							break;
						case "unflagged":
							set_msg_as_unflagged(msgs[j]+'_s'+id_border, true);
							Element("search_"+id_border+"_check_box_message_"+msgs[j]).checked = false;
							break;
					}
					notErrors = true;
				}
			}else{
				errors = true;
			}
		}
		
		draw_tree_folders(folders);
		Element('chk_box_select_all_messages_search').checked = false;
		refresh();
		
		if(errors && notErrors)
			write_msg(get_lang('Some messages were not marked with success!'));
		else if(notErrors)
			write_msg(get_lang('The messages were marked with success!'));
		else
			write_msg(get_lang('Error marking messages.'));
	}
	cExecute ("$this.imap_functions.set_messages_flag_from_search&msg_to_flag="+selected_param+"&flag="+flag, handler_set_messages_flag);
}

// Get checked messages
function set_messages_flag(flag, msgs_to_set){	
	if(currentTab != 0 && currentTab.indexOf("search_local")  >= 0){
		return set_messages_flag_search_local(flag);
	}
	if (currentTab != 0 && currentTab.indexOf("search_")  >= 0){
		return set_messages_flag_search(flag);
	}
	
	var handler_set_messages_flag = function (data){
		if(!verify_session(data))
			return;
		var msgs_to_set = data.msgs_to_set.split(",");


        if(data.disposition_notification_to){
            $.Zebra_Dialog(data.disposition_notification_to.length == 1 ? get_lang('One of his messages can not be marked as read, because it contains a read confirmation.') : data.disposition_notification_to.length + get_lang(' of its messages could not be marked as read, because it contains a read confirmation.'), {
                'type':     'warning',
                'overlay_opacity': '0.5',
				'custom_class': 'custom-zebra-filter',
                'buttons':  [get_lang('Close')]
            });
        }

		if(!data.status) {
			write_msg(data.msg);
			Element('chk_box_select_all_messages').checked = false;
			for (var i = 0; i < msgs_to_set.length; i++) {
				Element("check_box_message_" + msgs_to_set[i]).checked = false;
				remove_className(Element(msgs_to_set[i]), 'selected_msg');
			}
			if(!data.msgs_unflageds)
				return;
				
			else
				if(data.msgs_not_to_set != "")
					write_msg(get_lang("Error processing some messages."));
					
				msgs_to_set = data.msgs_unflageds.split(",");
		}

		for (var i=0; i<msgs_to_set.length; i++){
			if (preferences.use_cache == 'True')
			{
				if (current_folder == '')
					current_folder = 'INBOX';
				var setFlag = function(msgObj) {
					switch(data.flag){
						case "unseen":
							msgObj.Unseen = "U";
							break;
						case "seen":
							msgObj.Unseen = "";
							break;
						case "flagged":
							msgObj.Flagged = "F";
							break;
						case "unflagged":
							msgObj.Flagged = "";
							break;
					}
				}
			}
			if(Element("check_box_message_" + msgs_to_set[i])){
				switch(data.flag){
					case "unseen":
						    set_msg_as_unread(msgs_to_set[i]);
						if(results_search_messages != "") 
							set_msg_as_unread(results_search_messages, true);
						Element("check_box_message_" + msgs_to_set[i]).checked = false;
						break;
					case "seen":
						set_msg_as_read(msgs_to_set[i], false);
						if(results_search_messages != "") 
							set_msg_as_read(results_search_messages, false, true);
						Element("check_box_message_" + msgs_to_set[i]).checked = false;

                        if(preferences['use_alert_filter_criteria'] == "1")
                        {
                            // remove a flag $FilteredMessage da mensagem ao ser marcada como lida
                            $.each(fromRules, function(index, value) {
                                if(value == folder){
                                    cExecute ("$this.imap_functions.removeFlagMessagesFilter&folder="+folder+"&msg_number="+msgs_to_set, function(){});
                                    return false;
                                }
                            });
                        }
						break;
					case "flagged":
						    set_msg_as_flagged(msgs_to_set[i]);
						if(results_search_messages != "") 
							set_msg_as_flagged(results_search_messages, true);
						document.getElementById("check_box_message_" + msgs_to_set[i]).checked = false;
						break;
					case "unflagged":
						    set_msg_as_unflagged(msgs_to_set[i]);
						if(results_search_messages != "") 
 	                        set_msg_as_unflagged(results_search_messages, true);
						Element("check_box_message_" + msgs_to_set[i]).checked = false;
						break;
				}
			}
		}
		Element('chk_box_select_all_messages').checked = false;
	}

	var folder = get_current_folder();
	if (msgs_to_set == 'get_selected_messages')
		var msgs_to_set = this.get_selected_messages();
	else
		folder = Element("input_folder_"+msgs_to_set+"_r").value;
	
	if (msgs_to_set)
		$.ajax({
			  type: "POST",
			  url: "controller.php?action=$this.imap_functions.set_messages_flag",
		 	  data: {
		 	  	folder: folder,
				msgs_to_set: msgs_to_set,
				flag: flag, 
				decoded: true 
			  },
			  success: function( data ){
			      data = connector.unserialize( data );

			      selectAllFolderMsgs();
			      $('.select-link').unbind('click');

			      if( data )
				  handler_set_messages_flag( data );
			  },
			  beforeSend: function( jqXHR, settings ){
			  	connector.showProgressBar();
			  },
			  complete: function( jqXHR, settings ){
			  	connector.hideProgressBar();
			  }

		});
	else
		write_msg(get_lang('No selected message.'));
}

// By message number
function set_message_flag(msg_number, flag, func_after_flag_change){
	var msg_number_folder = Element("new_input_folder_"+msg_number+"_r"); //Mensagens respondidas/encaminhadas
	if(!msg_number_folder)
		var msg_number_folder = Element("input_folder_"+msg_number+"_r"); //Mensagens abertas
	
	var handler_set_messages_flag = function (data){
		if(!verify_session(data))
			return;
		if(!data.status) {
			write_msg(get_lang("this message cant be marked as normal"));
			return;
		}
		else if(func_after_flag_change) {
			func_after_flag_change(true);
		}
		if (data.status && Element("td_message_answered_"+msg_number)) {
			
			switch(flag){
				case "unseen":
					set_msg_as_unread(msg_number);
					break;
				case "seen":
					set_msg_as_read(msg_number);
					break;
				case "flagged":
					set_msg_as_flagged(msg_number);
					break;
				case "unflagged":
					set_msg_as_unflagged(msg_number);
					break;
				case "answered":
					Element("td_message_answered_"+msg_number).innerHTML = '<img src=templates/'+template+'/images/answered.png title=Respondida>';
					break;
				case "forwarded":
					Element("td_message_answered_"+msg_number).innerHTML = '<img src=templates/'+template+'/images/forwarded.png title=Encaminhada>';
					break;
			}				
		} else {
			refresh();
		}
	}
	$.ajax({
		  url: 'controller.php?' + $.param( {action: '$this.imap_functions.set_messages_flag',
						      folder: ( msg_number_folder ?  msg_number_folder.value : get_current_folder() ),
						      msgs_to_set: msg_number,
						      flag: flag,
						      decoded: true } ),
		  success: function( data ){
		      data = connector.unserialize( data );
		      
		      if( data )
			  handler_set_messages_flag( data );
		  },
		  beforeSend: function( jqXHR, settings ){
			  	connector.showProgressBar();
		  },
		  complete: function( jqXHR, settings ){
			  	connector.hideProgressBar();
		   }

	});
}

function print_search_msg(){		
	var folder = "<h2>&nbsp;Resultado da Pesquisa&nbsp;<font color=\"#505050\" face=\"Verdana\" size=\"1\"></h2>";
	msgs_number = get_selected_messages_search();
	var tbody = Element('divScrollMain_'+numBox).firstChild.firstChild.innerHTML;
	var id_border = currentTab.replace(/[a-zA-Z_]+/, "");
	
	if(msgs_number){
		msgs_number = msgs_number.split(",");
		var tbody = "";
		for(var i = 0; i < msgs_number.length; i++){
			tbody += "<tr id=\""+msgs_number[i]+"_s"+id_border+"\" class=\"tr_msg_unread tr_msg_read2\">"+ Element(msgs_number[i]+'_s'+id_border).innerHTML+"</tr>";
		}
	}else{
		msgs_number = get_all_messages_search();
		msgs_number = msgs_number.split(",");
		var tbody = "";
		for(var i = 0; i < msgs_number.length; i++){
			tbody += "<tr id=\""+msgs_number[i]+"_s"+id_border+"\" class=\"tr_msg_unread tr_msg_read2\">"+ Element(msgs_number[i]+'_s'+id_border).innerHTML+"</tr>";
		}
	}
	
	var print_width = screen.width - 200; 
	var x = ((screen.width - print_width) / 2); 
	var y = ((screen.height - 400) / 2) - 35; 
	var window_print = window.open('','ExpressoMail','width='+print_width+',height=400,resizable=yes,scrollbars=yes,left='+x+',top='+y); 
	seekDot = (is_ie ? /width=24/gi : /width="24"/gi); 

	var thead = "<tr class=\"message_header\">    <td width=\"3%\"></td><td width=\"2%\"></td><td width=\"1%\"></td><td width=\"1%\"></td><td width=\"1%\"></td><td width=\"1%\"></td><td width=\"2%\"></td><td id=\"message_header_FOLDER_0\" class=\"th_resizable\" align=\"left\" width=\"20%\">Pasta</td><td id=\"message_header_SORTFROM_0\" class=\"th_resizable\" align=\"left\" width=\"20%\">De</td><td id=\"message_header_SORTSUBJECT_0\" class=\"th_resizable\" align=\"left\" width=\"*\">Assunto</td><td id=\"message_header_SORTARRIVAL_0\" class=\"th_resizable\" align=\"center\" width=\"11%\"><b>Data</b><img src=\"templates/default/images/arrow_descendant.gif\"></td><td id=\"message_header_SORTSIZE_0\" class=\"th_resizable\" align=\"left\" width=\"11%\">Tamanho</td></tr>";
	tbody = tbody.replace(seekDot, "style='display:none'"); 
	seekDot = (is_ie ? /width=16/gi : /width="16"/gi); 

	tbody = tbody.replace(seekDot, "style='display:none'"); 
	seekDot = (is_ie ? /width=12/gi : /width="12"/gi); 

	tbody = tbody.replace(seekDot, "style='display:none'"); 
	while (1){ 
		try{ 
			window_print.document.open(); 
	 	    var html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html>' 
	 	    + '<head><link rel="stylesheet" type="text/css" href="templates/'+template+'/print.css"/></head>' 
	 	    + cabecalho + '</h4><hr />' 
	 	    + '<h2>'+folder+'</h2><hr/><blockquote><font size="2">' 
	 	    + '<table width="100%" cellpadding="0" cellspacing="0">' 
	 	    + '<thead>' + thead + '</thead><tbody>' + tbody + '</tbody>' 
	 	    + '</table></font></blockquote></body></html>'; 
	 	    window_print.document.write(html); 
	 	    window_print.document.close(); 
	 	    break; 
		} 
		catch(e){ 
			//alert(e.message); 
		} 
	} 
	window_print.document.close(); 
	window_print.print(); 
}


/*PERMITE A IMPRESSÃO DE UMA LISTA DE MENSAGENS E SEU CONTEÚDO*/
function print_messages_bodies(){
	var messages = {};
	messages[get_current_folder()] = get_selected_messages().split(',');

	var print_bodies = function (data){
		var print_width = screen.width - 200;
		var x = ((screen.width - print_width) / 2);
		var y = ((screen.height - 400) / 2) - 35;
		var window_print = window.open('','ExpressoMail','width='+print_width+',height=400,resizable=yes,scrollbars=yes,left='+x+',top='+y);
		if(window_print == null) {
			alert(get_lang("The Anti Pop-Up is enabled. Allow this site (%1) for print.",document.location.hostname));
			return;
		}
		var header = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html>'
		+ '<head><link rel="stylesheet" type="text/css" href="templates/'+template+'/print.css"/></head>';
		window_print.document.write(header);
		var printData ='<body style="overflow:auto">' + cabecalho + '</h4><hr />';
		window_print.document.write(printData);
		var newRow = function(label,data){
			return "<tr><td width=7%><font size='2'><b>"+label+" </b></font></td><td><font size='2'>"+data+"</font></td></tr>";
		}
		/*INSERE AS MENSAGENS NO DOCUMENTO DE IMPRESSÃO*/
		$.each(data,function(index,message){
			var html = "<table><tbody>";
			if(message.sender)
				for(var i=0; i<message.sender.length; i++){	
					html += newRow(get_lang('Sent by')+":",message.sender[i]);
				}
			if(message.from)
				for(var i=0; i<message.from.length; i++){	
					html += newRow(get_lang('From')+":",message.from[i]);
				}
			if(message.toaddress2) {
				html += newRow(get_lang('To')+":",message.toaddress2);
			}
			if (message.cc) {
				html += newRow(get_lang('Cc')+":",message.cc);
			}
			if (message.bcc) {
				html += newRow(get_lang('Cco')+":",message.bcc);
			}
			if(message.smalldate)
				html += newRow(get_lang('Date')+":",message.smalldate);
			/*DATA NAS MESAGENS LOCAIS*/
			else if(message.udate){
				var norm = function (arg) {return (arg < 10 ? '0'+arg : arg);};
				var weekDays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
			    var today = new Date();
				today.setHours(23);
				today.setMinutes(59);
                today.setSeconds(59);
                today.setMilliseconds(999);
	            udate_local = message.udate*1000 + today.getTimezoneOffset()*60*1000;
	            date_msg = new Date(udate_local);				
				if (preferences.show_date_numerical == 0 || typeof(preferences.show_date_numerical) == 'undefined') { 	
					if (today.getTime() - date_msg.getTime() < 86400000)
						html += newRow(get_lang('Date')+":",norm(date_msg.getHours()) + ':' + norm(date_msg.getMinutes()));
					else
						if (today.getTime() - date_msg.getTime() < 172800000)
							newRow(get_lang('Date')+":",get_lang('Yesterday'));
						else if (today.getTime() - date_msg.getTime() < 259200000)
							html += newRow(get_lang('Date')+":",get_lang(weekDays[date_msg.getDay()]));
						else
							html += newRow(get_lang('Date')+":",norm(date_msg.getDate()) + '/' + norm(date_msg.getMonth()+1) + '/' +date_msg.getFullYear());					
				}else
					html += newRow(get_lang('Date')+":",norm(date_msg.getDate()) + '/' + norm(date_msg.getMonth()+1) + '/' +date_msg.getFullYear());
			}	
			html += newRow(get_lang('Subject')+":",message.subject);		
			/*LISTA DE ANEXOS*/
			if (message.attachments && message.attachments.length) {
	 	        var img = '<img style="margin-bottom : -5px; cursor : pointer;" src="templates/'+template+'/images/new.png">';
	 	        var atts = "";
	 	        $.each(message.attachments,function(index,attach){
	 	        	atts += " | " + attach.name + "("+ formatBytes(attach.fsize) +") " + img + " | ";
	 	        });
	 	        html += newRow(get_lang('Attachments: '),atts);       
	 	    }
	 	    /*ANEXOS LOCAIS*/
	 	    else if (message.attachment && message.attachment.number_attachments > 0) {
	 	        var img = '<img style="margin-bottom : -5px; cursor : pointer;" src="templates/'+template+'/images/new.png">';
	 	        var atts = "";
	 	        $.each(message.attachment.names.split(','),function(index,attach){
	 	        	atts += " | " + attach + img + " |";
	 	        });
	 	        html += newRow(get_lang('Attachments: '),atts);       
	 	    }
			html += '</tbody></table>';
			if (message.body){
				html += "<hr />" + message.body;
	 	    }
	 	    else if (message._return){
	 	    	html += "<hr />" + message._return;
	 	    }
	 	    html += "<hr />";
			window_print.document.write(html);
		});
		window_print.document.close();
		window_print.print();
	}
			/*MENSAGENS LOCAIS*/
	if ( proxy_mensagens.is_local_folder(get_current_folder()) ){
		var msgs = Array();
		var _msg = {};
		$.each(get_selected_messages().split(','),function(index,value){
			_msg.header = expresso_mail_archive.getMessageHeaders(value); 
			_msg.body = expresso_mail_archive.getMessageBodies([value]);
			msgs.push($.extend({},_msg.header,_msg.body[0]));
		});
		print_bodies(msgs);
	}
	else{
		$.ajax({			
				type: "POST",
				url: "controller.php?action=$this.imap_functions.getMessages",
				data: {
					details: "all",
	                messages : messages,
				},
				success: function(data){
					data = connector.unserialize(data);
					if(data){
	                  print_bodies(data[get_current_folder()]);
					}
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

function newTbody(messages){
	var tbody = $("<tbody>");
	$.each(messages,function(index,msg){
		var newTr = make_tr_message(msg,get_current_folder());
		if (msg.attachments && msg.attachments.length){
			$(newTr).find('td').eq(1).css({'background-image':'url(templates/'+template+'/images/mail-gifs.gif)','background-position': '0 -242px'});
		}		
		tbody.append(newTr);
	});
	return tbody.html();
}

function print_messages_list(){
	var print_list = function(tbody,folder){
		var print_width = screen.width - 200;
		var x = ((screen.width - print_width) / 2);
		var y = ((screen.height - 400) / 2) - 35;
		var window_print = window.open('','ExpressoMail','width='+print_width+',height=400,resizable=yes,scrollbars=yes,left='+x+',top='+y);
		seekDot = (is_ie ? /width=24/gi : /width="24"/gi);
		//thead = thead.replace(seekDot, "style='display:none'"); 
		var thead = "<tr class=\"message_header\"> <td width=\"3%\"></td><td width=\"2%\"></td><td width=\"1%\"></td><td width=\"1%\"></td><td width=\"1%\"></td><td width=\"1%\"></td><td width=\"2%\"></td><td width=\"2%\"></td><td width=\"2%\"></td><td id=\"message_header_SORTFROM_0\" class=\"th_resizable\" align=\"left\" width=\"20%\">De</td><td id=\"message_header_SORTSUBJECT_0\" class=\"th_resizable\" align=\"left\" width=\"*\">Assunto</td><td id=\"message_header_SORTARRIVAL_0\" class=\"th_resizable\" align=\"center\" width=\"11%\"><b>Data</b><img src=\"templates/default/images/arrow_descendant.gif\"></td><td id=\"message_header_SORTSIZE_0\" class=\"th_resizable\" align=\"left\" width=\"11%\">Tamanho</td></tr>";
		tbody = tbody.replace(seekDot, "style='display:none'");
		seekDot = (is_ie ? /width=16/gi : /width="16"/gi);
		//thead = thead.replace(seekDot, "style='display:none'"); 
		tbody = tbody.replace(seekDot, "style='display:none'");
		seekDot = (is_ie ? /width=12/gi : /width="12"/gi);
		//thead = thead.replace(seekDot, "style='display:none'"); 
		tbody = tbody.replace(seekDot, "style='display:none'");
		while (1){
			try{
				window_print.document.open();
				var html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html>'
				+ '<head><link rel="stylesheet" type="text/css" href="templates/'+template+'/print.css"/></head>'
		 	    + cabecalho + '</h4><hr />' 
				+ '<h2>'+$('#border_id_0').html()+'</h2><hr/><blockquote><font size="2">'
				+ '<table width="100%" cellpadding="0" cellspacing="0">'
				+ '<thead>' + thead + '</thead><tbody>' + tbody + '</tbody>'
				+ '</table></font></blockquote></body></html>';
				window_print.document.write(html);
				window_print.document.close();
				break;
			}
			catch(e){
				//alert(e.message);
			}
		}
		window_print.document.close();
		window_print.print();
	}
	msgs_number = get_selected_messages();
	if(msgs_number == false){
		var tbody = Element('divScrollMain_0').firstChild.firstChild.innerHTML;
		print_list(tbody);
	}else{
		var messages = {};
		messages[get_current_folder()] = msgs_number.split(',');
		/*MENSAGENS LOCAIS*/
		if ( proxy_mensagens.is_local_folder(get_current_folder()) ){
			var msgs = Array();
			$.each(msgs_number.split(','),function(index,value){
				msgs.push(expresso_mail_archive.getMessageHeaders(value));
			});
			print_list(newTbody(msgs));
		}
		else{
			$.ajax({			
				type: "POST",
				url: "controller.php?action=$this.imap_functions.getMessages",
				data:{
		            details : "all",
		            messages : messages,
				},
				success: function(data){
					data = connector.unserialize(data);
					if(data){
						data = data[get_current_folder()];
		              	print_list(newTbody(data));
					}
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
}

function print_all(){
    if(typeof (currentTab) == "string" && currentTab.indexOf("local") != -1){  
        alert(get_lang("Unable to handle local messages from a search. This is allowed only for non-local messages."));
        return true;
    }
	if (openTab.type[currentTab] == 2)
		return print_msg(current_folder,currentTab.substr(0,currentTab.indexOf("_r")),currentTab);

	if (currentTab != 0 && currentTab.indexOf("search_")  >= 0){
		return print_search_msg();
	}

	if (get_selected_messages() == false){
		return print_messages_list();
	}
    var msg = '_[[Some messages were selected for printing. Do you want to print the contents of each one? Otherwise, only a list of selected messages will be printed.]]';
    $.Zebra_Dialog(msg, {
	    'type':     'question',
	    'buttons': ['Sim','Não'],
	    'overlay_opacity': '0.5',
		'custom_class': 'custom-zebra-filter',
	    'onClose':  function(caption) {
	        if(caption == 'Sim'){
	        	return print_messages_bodies();
	        }
	        else{
	        	return print_messages_list();	
	        }
	    }
	});
}

function print_msg(msg_folder, msg_number, border_ID){
	var div_toaddress_full = Element("div_toaddress_full_"+border_ID);
	var div_ccaddress_full = Element("div_ccaddress_full_"+border_ID);
	var div_ccoaddress_full = Element("div_ccoaddress_full_"+border_ID);
	var printListTO = (div_toaddress_full && div_toaddress_full.style.display != 'none') || toaddress_array[border_ID].length == 1 ? true : false;	
	var printListCC = (div_ccaddress_full && div_ccaddress_full.style.display != 'none') || !div_ccaddress_full ? true : false;
	var printListCCO = (div_ccoaddress_full && div_ccoaddress_full.style.display != 'none') || !div_ccoaddress_full ? true : false;	
	var sender		= Element('sender_values_'+border_ID) ? Element('sender_values_'+border_ID).value : null;
	var from		= Element('from_values_'+border_ID) ? Element('from_values_'+border_ID).value : null;
	var to			= Element('to_values_'+border_ID) ? Element('to_values_'+border_ID).value :null;
	var cco			= Element('cco_values_'+border_ID) ? Element('cco_values_'+border_ID).value : null;
	var cc 			= Element('cc_values_'+border_ID) ? Element('cc_values_'+border_ID).value : null;		
	var date		=  Element('date_'+border_ID);	
	var subject		= Element('subject_'+border_ID);
	var attachments	= Element('attachments_'+border_ID);
	var body		= Element('body_'+border_ID);

	
	var att = '';
	
	var countAtt = 0; 
 		         
 	if(attachments !== null) 
 	{ 
		countAtt = attachments.getElementsByTagName('a').length; 
		if(countAtt === 1)  
			att =  attachments.getElementsByTagName('a')[0].innerHTML; 
		else if(countAtt > 1) 
			for (var i = 1; i <attachments.getElementsByTagName('a').length; i++) 
				att += " | " + attachments.getElementsByTagName('a')[i].innerHTML; 
 	} 
 	        
	var body = Element('body_'+border_ID); 
	
	var print_width = screen.width - 200;
	var x = ((screen.width - print_width) / 2);
	var y = ((screen.height - 400) / 2) - 35;
	var window_print = window.open('','ExpressoMail','width='+print_width+',height=400,resizable=yes,scrollbars=yes,left='+x+',top='+y);
	if(window_print == null) {
		alert(get_lang("The Anti Pop-Up is enabled. Allow this site (%1) for print.",document.location.hostname));
		return;
	}

	//needed to get the names of the attachments... only.
	if(attachments != null)
	{
		var a = attachments.childNodes;
		var attachs = "";
		var show_attachs = "";
                var ii = a.length >2?2:1;
		for(i=ii;i<a.length;i++)
		{
			if(a[i].tagName && a[i].tagName == "A")
			{
				attachs += a[i].innerHTML;
			}
		}
		show_attachs = "<tr><td width=7%><font size='2'>" + get_lang('Attachments: ')+ " </font></td><td><font size='2'>"+attachs+"</font></td></tr>";
	} else{
		show_attachs = "";
	}       
	var current_path = window.location.href.substr(0,window.location.href.lastIndexOf("/"));
	var head = '<head><title></title><link href="'+current_path+'/templates/default/main.css" type="text/css" rel="stylesheet"/></head>';
	window_print.document.write(head);

	while (1){
		try{
			var html ='<body style="overflow:auto">';
			html += cabecalho + '</h4><hr />';
			html += '<table><tbody>';
			if(sender)
				html += "<tr><td width=7% noWrap><font size='2'>" + get_lang('Sent by') + ": </font></td><td><font size='2'>"+sender+"</font></td></tr>";
			if(from)
				html += "<tr><td width=7%><font size='2'>" + get_lang('From') + ": </font></td><td><font size='2'>"+from+"</font></td></tr>";
			if(to) {
				if(!printListTO)
					to = 'Os destinatários não estão sendo exibidos para esta impressão';
				html += "<tr><td width=7%><font size='2'>" + get_lang('To') + ": </font></td><td><font size='2'>"+to+"</font></td></tr>";
			}
			if (cc) {
				if(!printListCC)
					cc = 'Os destinatários não estão sendo exibidos para esta impressão';
				html += "<tr><td width=7%><font size='2'>" + get_lang('Cc') + ": </font></td><td><font size='2'>"+cc+"</font></td></tr>";
			}
			if (cco) {
				if(!printListCCO)
					cco = 'Os destinatários não estão sendo exibidos para esta impressão';
				html += "<tr><td width=7%><font size='2'>" + get_lang('Cco') + ": </font></td><td><font size='2'>"+cco+"</font></td></tr>";
			}
			if(date)
				html += "<tr><td width=7%><font size='2'>" + get_lang('Date') + ": </font></td><td><font size='2'>"+date.innerHTML+"</font></td></tr>";
			
			html += "<tr><td width=7%><font size='2'>" + get_lang('Subject')+ ": </font></td><td><font size='2'>"+subject.innerHTML+"</font></td></tr>";
			//html += show_attachs; //to show the names of the attachments
			if (countAtt > 0) { 
 	            html += "<tr><td width=7%><font size='2'>" + get_lang('Attachments: ') + "</font></td><td><font size='2'>"+att+"</font></td></tr>";       
 	        }
			html += "</tbody></table><hr />";
			window_print.document.write(html + body.innerHTML);

				var tab_tags = window_print.document.getElementsByTagName("IMG");
                        var link = location.href.replace(/\/expressoMail\/(.*)/, "");
				for(var i = 0; i < tab_tags.length;i++){
                                var _img = tab_tags[i].cloneNode(true);
                                if(tab_tags[i].src.toUpperCase().indexOf('INC/GET_ARCHIVE.PHP?MSGFOLDER=') > -1)
                                    _img.src = link + '/expressoMail/'+tab_tags[i].src.substr(tab_tags[i].src.toUpperCase().indexOf('INC/GET_ARCHIVE.PHP?MSGFOLDER='));

					tab_tags[i].parentNode.replaceChild(_img,tab_tags[i]);
				}
                        
			break;
		}
		catch(e){
			//alert(e.message);
		}
	}
	window_print.document.close();
	window_print.print();
}

function empty_trash_imap(shared, button, type){
	if(shared){
		var folder_part = $(button).parents("li:first").attr("id").split(cyrus_delimiter);
		var folder = folder_part[0]+cyrus_delimiter+folder_part[1];
	}

	var handler_empty_trash = function(data){
		Element('chk_box_select_all_messages').checked = false;
		if(!verify_session(data))
			return;
		//tree_folders.getNodeById(mount_url_folder(["INBOX",special_folders["Trash"]])).alter({caption: get_lang("Trash")});
		//tree_folders.getNodeById(mount_url_folder(["INBOX",special_folders["Trash"]]))._refresh();
		update_quota(get_current_folder());
		draw_new_tree_folder();
        if( preferences['use_followupflags_and_labels'] == "1" )
		    draw_tree_labels();
		if (data){
			if(typeof(data) == "object"){
				if(data[1] == "Permission denied"){
					cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
					return write_msg(get_lang("Permission denied"));
				}
			}
			write_msg(get_lang('Your Trash folder was empty.'));
			if (get_current_folder() == mount_url_folder(["INBOX",special_folders["Trash"]]) || get_current_folder() == mount_url_folder([folder,special_folders["Trash"]])){
				draw_paging(0);
                totalFolderMsgs = 0;
                updateSelectedMsgs();
				remove_rows(document.getElementById("table_box"));				
				Element('tot_m').innerHTML = 0;
				Element('new_m').innerHTML = 0;
			}
			refresh();
		}
		else
			write_msg(get_lang('ERROR emptying your Trash folder.'));
	}
	
	$.Zebra_Dialog(get_lang('Do you really want to empty your trash folder?'), {
		'type':     'question',
		'title':    get_lang('Empty Trash'),
		'buttons':  [get_lang("No"), get_lang("Yes")],
		'overlay_opacity' : 0.5,
		'custom_class': 'custom-zebra-filter',
		'onClose':  function(caption) {

			if(caption == get_lang("Yes")){
                if(type && (type.id == "local_messages_trash")){
                    expresso_mail_archive.deleteAllMessages(type.id);
                    cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
                } else {
                    cExecute ("$this.imap_functions.empty_folder&clean_folder="+"imapDefaultTrashFolder"+(shared ? "&shared="+folder : ""), handler_empty_trash);
                } 
			}
		}
	});
}

function empty_spam_imap(shared, button, type){
	if(shared){
		var folder_part = $(button).parents("li:first").attr("id").split(cyrus_delimiter);
		var folder = folder_part[0]+cyrus_delimiter+folder_part[1];
	}
	var handler_empty_spam = function(data){
		Element('chk_box_select_all_messages').checked = false;
		if(!verify_session(data))
			return;
		if (get_current_folder() == mount_url_folder(["INBOX",special_folders["Spam"]]) || get_current_folder() == mount_url_folder([folder,special_folders["Spam"]])){
			draw_paging(0);
			remove_rows(document.getElementById("table_box"));
		}
		//tree_folders.getNodeById(mount_url_folder(["INBOX",special_folders["Spam"]])).alter({caption: get_lang("Spam")});
		//tree_folders.getNodeById(mount_url_folder(["INBOX",special_folders["Spam"]]))._refresh();
		draw_new_tree_folder();
        if( preferences['use_followupflags_and_labels'] == "1" )
		    draw_tree_labels();
		update_quota(get_current_folder());
		if (data){
			if(typeof(data) == "object"){
				if(data[1] == "Permission denied"){
					cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
					return write_msg(get_lang("Permission denied"));
				}else{
					write_msg(get_lang('Your Spam folder was empty.'));		
				}
			}else{
				write_msg(get_lang('Your Spam folder was empty.'));	
			}
			refresh();
		}
		else
			write_msg(get_lang('ERROR emptying your Spam folder.'));
	}
	
	$.Zebra_Dialog(get_lang('Do you really want to empty your spam folder?'), {
		'type':     'question',
		'title':    get_lang('Empty Spam'),
		'buttons':  [get_lang("No"), get_lang("Yes")],
		'overlay_opacity' : 0.5,
		'custom_class': 'custom-zebra-filter',
		'onClose':  function(caption) {
			if(caption == get_lang("Yes")){
                if(type.id == "local_messages_spam"){ 
                    expresso_mail_archive.deleteAllMessages(type.id);
                    cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
                } else {
                    cExecute ("$this.imap_functions.empty_folder&clean_folder="+"imapDefaultSpamFolder"+(shared ? "&shared="+folder : ""), handler_empty_spam);
                } 
			}
		}
	});
}

function export_all_selected_msgs(){
	if(get_current_folder().split("_")[0] == "local"){

        if(get_selected_messages().indexOf(",") != -1){
            expresso_mail_archive.getSomeMsgs(get_selected_messages().split(","));
        } else {
            expresso_mail_archive.getSomeMsgs([get_selected_messages()]);
        }

    } else {
        
    if (openTab.type[currentTab] > 1){	    
		source_msg(currentTab,openTab.imapBox[currentTab]);
		return;
	}
	var search = false;		

	if(currentTab != 0 && currentTab.indexOf("search_")  >= 0){
		var id_border = currentTab.replace(/[a-zA-Z_]+/, "");
		var msgs_to_export = this.get_selected_messages_search();
		if (!msgs_to_export){
			write_msg(get_lang('No selected message.'));
			return;
		}
		var selected_param = "";
		msgs_to_export = msgs_to_export.split(",");
		search = true;
		for (i = 0; i < msgs_to_export.length; i++){
			var tr = Element(msgs_to_export[i]+'_s'+id_border);
			msg_to_move = (tr.getAttribute('name') == null ? get_current_folder() : tr.getAttribute('name'));
			selected_param += ','+msg_to_move+';'+tr.id.replace(/_[a-zA-Z0-9]+/,"");
		}
	}else{
		var msgs_to_export = this.get_selected_messages();
	}
	var handler_export_all_selected_msgs = function(data){

		if(!data){
			write_msg(get_lang('Error compressing messages (ZIP). Contact the administrator.'));
		}
		else{
			var filename = 'mensagens.zip'; 
			if (data[0].match(/\.eml$/gi)) { 
                filename = data[1]+'.eml'; 
			} 

            if(typeof data == "object")
			     download_attachments(null, null, data[0], null,null,filename);
            else 
                 download_attachments(null, null, data, null,null,filename);
		}
	}

	if(search){
		cExecute ("$this.exporteml.makeAll", handler_export_all_selected_msgs, "folder=false&msgs_to_export="+selected_param);
	}else if (msgs_to_export) {
		cExecute ("$this.exporteml.makeAll", handler_export_all_selected_msgs, "folder="+get_current_folder()+"&msgs_to_export="+msgs_to_export);
		write_msg(get_lang('You must wait while the messages will be exported...'));
	}
	else
		write_msg(get_lang('No selected message.'));
    }
}

function select_all_search_messages(select, aba){

	if(select){
		jQuery("#"+aba+" tr").each(function(i, o) {
		
			o.firstChild.firstChild.checked = true;
			add_className(o, 'selected_msg');
		});		
	}else{
		jQuery("#"+aba+" tr").each(function(i, o) {
		
			o.firstChild.firstChild.checked = false;
			remove_className(o, 'selected_msg');
		});
	}
}

function verify_session(data){

	if(data && data.imap_error) {
		if(data.imap_error == "nosession")
			write_msg(get_lang("your session could not be verified."));
		else
			write_msg(data.imap_error);
		// Hold sesion for edit message.
		//if(!hold_session)
		//	location.href="../login.php?cd=10&phpgw_forward=%2FexpressoMail%2Findex.php";
		return false;
	}
	else
		return true;
}

// Save image file.
function save_image(e,thumb,file_type){
	file_type = file_type.replace("/",".");
	thumb.oncontextmenu = function(e) {
		return false;
	}
	var _button = is_ie ? window.event.button : e.which;
	var	_target = is_ie ? event.srcElement : e.target;

	if(_button == 2 || _button == 3) {
		var _params = _target.id.split(";;");
		download_attachments(Base64.encode(_params[0]),_params[1],_params[2],_params[3],_params[4],file_type);
		if($(_target).parent().attr("href").split("http").length > 1){
			var part_find = thumb.toString().split("#");
			var part2_find = part_find[1].split("/temp");
			var part_id_dwl = part2_find[0] + '/temp/download' + part2_find[1]; 
			download_local_attachment(part_id_dwl);
			return;
		}else{
			var _params = _target.id.split(";;");	
			download_attachments(_params[0],_params[1],_params[2],_params[3],_params[4],file_type);
		}
	}
}

function save_image2(info){ 
	var obj = jQuery.parseJSON(unescape(info)); 
	download_attachments(obj.folder, obj.message, obj.thumbIndex, obj.pid, obj.encoding, obj.type.replace("/",".")); 
} 

function nospam(msgs_number, border_ID, folder){
	if (folder == 'null')
		folder = get_current_folder();
	var new_folder = '';
	if(folder.substr(0,4) == 'user'){
		arrayFolder = folder.split(cyrus_delimiter);
		new_folder = 'user'+cyrus_delimiter+arrayFolder[1];
	}
	else{
		new_folder = 'INBOX';
	}
	var new_folder_name = get_lang('INBOX');
	var handler_move_msgs = function(data){
		if (msgs_number == 'selected')
			set_messages_flag("unseen", "get_selected_messages");
		else
			proxy_mensagens.proxy_set_message_flag(msgs_number, "unseen");
   		proxy_mensagens.proxy_move_messages(folder, msgs_number, border_ID, new_folder, new_folder_name);

		if (openTab.type[currentTab] > 1)
			delete_border(currentTab,'false');
	}

	if(currentTab.toString().indexOf("_r") != -1)
		msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf("_r"));
	else if(msgs_number == 'selected')
		msgs_number = get_selected_messages();

	//TODO: REFATORAR O CÓDIGO PARA EVITAR recodificação
	if(currentTab == 0)
		msgs_number = get_selected_messages();
	if (typeof currentTab == "string" && currentTab.indexOf("search_") != "-1"){
		msgs_number = "";
		var checked = $("#divScrollMain_"+currentTab.substr(currentTab.indexOf('_')+1,255)).find("tr input:checked");
		$.each(checked,function(index,value){
			if (value){
				if (index == 0)
					msgs_number = $(value).parents("tr").attr("id");
				else
					msgs_number += "," + $(value).parents("tr").attr("id");
			}
		});
	}
	if (parseInt(msgs_number) > 0 || msgs_number.length > 0)
		cExecute ("$this.imap_functions.spam&folder="+folder+"&spam=false"+"&msgs_number="+msgs_number+"&border_ID="+border_ID+"&sort_box_type="+sort_box_type+"&sort_box_reverse="+sort_box_reverse+"&reuse_border="+border_ID+"&new_folder="+new_folder+"&new_folder_name="+new_folder_name+"&get_previous_msg="+0+"&cyrus_delimiter="+cyrus_delimiter, handler_move_msgs);
	else
		write_msg(get_lang('No selected message.'));
}

function spam(folder, msgs_number, border_ID){
	if (folder == 'null')
		folder = get_current_folder();
	var new_folder = '';
	if(folder.substr(0,4) == 'user')
	{       
		arrayFolder = folder.split(cyrus_delimiter);
		new_folder = 'user'+cyrus_delimiter+arrayFolder[1]+cyrus_delimiter+special_folders['Spam'];
	}
	else
	{
		new_folder = mount_url_folder(["INBOX",special_folders["Spam"]]);
	}
	var new_folder_name = 'Spam';
	var not_opem_previus = true;
	var handler_move_msgs = function(data){
		proxy_mensagens.proxy_move_messages(folder, msgs_number, border_ID, new_folder, new_folder_name, not_opem_previus);
		if (openTab.type[currentTab] > 1){
			if(preferences.delete_and_show_previous_message == 1)
			delete_border(currentTab,'false');
	}
	}

	if(currentTab.toString().indexOf("_r") != -1)
		msgs_number = currentTab.toString().substr(0,currentTab.toString().indexOf("_r"));
	else if(currentTab != 0 && currentTab.indexOf("search_")  >= 0){
		var content_search = document.getElementById('content_id_'+currentTab);mount_url_folder(["INBOX",special_folders['Trash']]), 'Trash',
		move_search_msgs('content_id_'+currentTab,  mount_url_folder(["INBOX",special_folders["Spam"]]), special_folders['Spam']);
		refresh();
		return;
	}else if(msgs_number == 'selected')
		msgs_number = get_selected_messages();

	//TODO: REFATORAR O CÓDIGO PARA EVITAR recodificação
	if(currentTab == 0)
		msgs_number = get_selected_messages();

	if(parseInt(msgs_number) > 0 || msgs_number.length > 0)
		cExecute ("$this.imap_functions.spam&folder="+folder+"&spam=true"+"&msgs_number="+msgs_number+"&border_ID="+border_ID+"&sort_box_type="+sort_box_type+"&sort_box_reverse="+sort_box_reverse+"&reuse_border="+border_ID+"&new_folder="+new_folder+"&new_folder_name="+new_folder_name+"&get_previous_msg="+0+"&cyrus_delimiter="+cyrus_delimiter, handler_move_msgs);
	else
		write_msg(get_lang('No selected message.'));
}

function import_window()
{
    if(typeof (currentTab) == "string" && currentTab.indexOf("local") != -1){  
        alert("_[[Unable to handle local messages from a search. This is allowed only for non-local messages.]]");
        return true;
    }
	var folder = {};
	var importEmails = $("#importEmails");
		importEmails.html( DataLayer.render( BASE_PATH + "modules/mail/templates/importEmails.ejs", {}));
		importEmails.dialog(
		{
			height		: 280,
			width		: 500,
			resizable	: false,
			title		: get_lang('zip mails to import'),
			modal		: true,
			buttons		: [
							 {
							 	text	: get_lang("Close"), 
							 	click	: function()
							 	{
							 		importEmails.dialog("close").dialog("destroy");
							 	}
							 },
							 {
							 	text	: get_lang("Import"), 
							 	click	: function()
							 	{
							 		var input_file	 = importEmails.find("input[type=file]");
							 		var input_hidden = importEmails.find("input[name=folder]"); 
							 		
							 		if( input_file.attr("value") == "" )
							 		{
							 			$.Zebra_Dialog(get_lang("You must choose a file") + " !",{
							 				'type'				: 'warning',
							 				'overlay_opacity'	: '0.5',
											'custom_class': 'custom-zebra-filter',
											'buttons'			: [get_lang("Close")],
							 				'onClose'			:  function(){
							 					$("#importMessageAccordion").accordion('activate',0);	
							 				}
							 			});
							 		}
							 		else
							 		{
								 		if( input_hidden.attr("value") == "" )
											$.Zebra_Dialog( get_lang("You must choose a folder") + " !" , {
												'type'				: 'warning',
												'overlay_opacity'	: '0.5',
												'custom_class': 'custom-zebra-filter',
												'buttons'			: [get_lang("Close")],
								 				'onClose'			:  function(){
								 					$("#importMessageAccordion").accordion('activate',1);	
								 				}
											});
								 		else
								 		{
								 			var handler_return = function(data)
								 			{
								 				write_msg(get_lang('The import was executed successfully.'));								 				
								 				return_import_msgs( data, folder );
								 				if(typeof(shortcut) != 'undefined') shortcut.disabled = false;
								 			}
								 			
								 			var formSend =  document.getElementById("importFileMessages");
								 			
								 			importEmails.dialog("destroy");
								 			
								 			write_msg(get_lang('You must wait while the messages will be imported...'));
											
											var local_folder = input_hidden.attr("value"); //recupera a pasta selecionada
											if(local_folder.indexOf("local_") == 0){ //verifica se a pasta selecionada é uma pasta local
												var fdata = local_folder.substr(15, local_folder.length); //recupera somente a estrutura da pasta ou o id
												expresso_mail_archive.getFolderInfo(fdata); //recuperar a estrutura da pasta, 
																							//para realizar a importação de mensagens
												fdata = expresso_mail_archive.folder.path;

												//o mailarchiver não provê um serviço para a importação de mensagens nas pastas locais,
												//mas sim uma modal, da qual são extraídas as informações e as urls para submitar os dados
												//para o arquivamento de mensagens em pastas locais.
												var url_src = mail_archive_protocol + '://' + mail_archive_host + ':' + mail_archive_port + '/arcserv/import?prt=' + mail_archive_protocol + '&por=' + mail_archive_port+ '&fid='+ encodeURIComponent(fdata) + '&sid=' + expresso_mail_archive.session.id;
												var _html = DataLayer.render(url_src);
												var regex_url = RegExp('\<iframe(.)*src=\"([^\"]*)"(.)*\>');
												var obj_params = $.parseQuery( regex_url.exec(_html)[2].split('?')[1] || '' );
												$(formSend).append('<input id="ma_import_flat" type="checkbox" value="false" name="flat" style="display:none"> ' +
													'<input id="sessionId" type="hidden" value="'+obj_params['sessvalue']+'" name="sessionId">' +
													'<input id="base" type="hidden" value="'+obj_params['basevalue']+'" name="base">' +
													'<input id="lang" type="hidden" value="'+obj_params['langvalue']+'" name="lang">');

												var importEmailsLocalDialog = $('#importEmailsLocal');
												importEmailsLocalDialog.css("overflow","hidden");
												importEmailsLocalDialog.dialog(
												{
													autoOpen    : false,
													height		: 200,
													width		: 350,
													resizable	: false,
													title		: get_lang('Local Archive'),
													modal		: true,
													buttons		: [
																	 {
																	 	text	: get_lang("Close"), 
																	 	click	: function()
																	 	{
																	 		importEmailsLocalDialog.dialog("close").dialog("destroy");
																	 	}
																	 }
																]
												});

												importEmailsLocalDialog.html('<iframe frameborder="0" scrolling="no" id="frameResult" name="frameResult" ></iframe>')
												$(formSend).attr("action", mail_archive_protocol + '://' + mail_archive_host + ':' + mail_archive_port + '/arcserv/import');
												$(formSend).attr("target", "frameResult");
												$(formSend).submit();
												importEmailsLocalDialog.dialog( "open" );
												if(typeof(shortcut) != 'undefined') 
													shortcut.disabled = false;
											} else {
												cExecuteForm('$this.imap_functions.import_msgs', formSend , handler_return );
											}

								 		}
							 		}
							 	}
							 }
						],
                open: function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
                },
                close: function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
                },
                destroy: function(event,ui)
                {
                	if(typeof(shortcut) != 'undefined') shortcut.disabled = false;
                }
		});

	importEmails.css("overflow","hidden");
		
	importEmails.find("input[type=file]").change(function()
	{ 
		var deniedExtension = true;
		var fileExtension 	= ["eml","zip"];
		var fileName 		= importEmails.find("input[type=file]").attr('value');
			fileName 		= fileName.split(".");
		
		if( fileName[(fileName.length-1)] )
		{
			for( var i in fileExtension )
			{
				if( fileExtension[i].toUpperCase() === fileName[(fileName.length-1)].toUpperCase() )
				{
					deniedExtension = false;
					break;
				}
			}
		}

		if( deniedExtension )
		{
			$.Zebra_Dialog( get_lang('File extension forbidden or invalid file') , {
				'type'				: 'warning',
				'overlay_opacity'	: '0.5',
				'custom_class': 'custom-zebra-filter',
				'buttons'			: [get_lang("Close")],
			});
		}

		$("#lblFileName").html( ( !deniedExtension ) ? importEmails.find("input[type=file]").attr('value') : "" );
		
	});	
		
	$("#importMessageAccordion").accordion();	

	var foldersTree = jQuery("#foldertree-container-importMessage")
	.removeClass('empty-container')
	.html(DataLayer.render(BASE_PATH + 'modules/mail/templates/foldertree.ejs', {folders: [cp_tree1, cp_tree2, cp_tree3, [] ]}))
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
		
		$('#foldertree-container-importMessage .filetree span.folder.selected').removeClass('selected');
		if(!target.is('#foldertree > .expandable, #foldertree > .collapsable'))
			$(target).children('.folder').addClass('selected');
		
		selectedFolder = {
		    id: folder.id, 
		    name: folder.child.attr('title'),
		    'class': folder.child.attr('class')
		};
		
		importEmails.find("input[name=folder]").attr("value", folder.id);
	});
	
	$("#lblFileName").prev().css("margin-left", "10px");
	$("#lblFileName").prev().css("font-weight", "bold");
}

function return_import_msgs(data, folder)
{
	if(data && data.error){
		write_msg(data.error);
	}
	else
	{
		if(data == 'Post-Content-Length')
			write_msg(get_lang('The size of this message has exceeded  the limit (%1B).', preferences.max_attachment_size ? preferences.max_attachment_size : Element('upload_max_filesize').value));
		else
		{	/*
			* @author Rommel Cysne (rommel.cysne@serpro.gov.br)
			* @date 2009/05/15
			* Foi colocado um teste para verificar se a pasta selecionada, passada como parametro,
			* eh uma pasta local (qualquer uma)
			*/
			var er = /^local_/;
			if ( er.test(folder.id) )
			{
				alert( "_[[Messages can not be imported into local folders]]" );
				//archive_msgs('INBOX/Lixeira/tmpMoveToLocal',wfolders_tree._selected.id,data);
				//cExecute('$this.imap_functions.delete_mailbox',function(){},'del_past=INBOX/Lixeira/tmpMoveToLocal');
			}
			else
			{
				if( openTab.imapBox[0] == folder.id )
				{
					openTab.imapBox[0] = '';
					change_folder(folder.id, folder.caption);
				}
				
				refresh();
			}
		}
	}

}

//Normalização dos dados evita ter que reemplementar toda as funcionalidades do calendar
function decodeCreateSchedulable(type, ID){
	var startEvent = new Date();  
	if(startEvent.toString('mm') < 30) 
		startEvent.add({minutes: (30 - parseInt(startEvent.toString('mm')))}); 
	else 
		startEvent.add({hours: 1, minutes: '-'+startEvent.toString('mm')}); 
	var endEvent = function(date){ 
	if(!!User.preferences.defaultCalendar){ 
		return  new Date(parseInt(date.getTime()) + 
			(!!Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration ?  
			(Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration * 60000) :
			(User.preferences.defaultDuration * 60000)
		));
	}else 
		return new Date(parseInt(date.getTime()) + (User.preferences.defaultDuration * 60000)); 
	};       

	var emails = $("#to_values_"+ID).val().split(',');
	
	var schedulable = {};
	
	schedulable = { 
		acl: {inviteGuests: true, organization: true ,participationRequired: false, read: true, write: true},
		alarms: [],
		allDay: false,
		attachments: [],
		isAttendee: true,
		attendee: '',//TODOOOOOO,
		category: '',
		'class': '1',
		delegatedFrom: {},
		endDate: startEvent.toString(User.preferences.dateFormat),
		startDate: endEvent(startEvent).toString(User.preferences.dateFormat),
		startHour: startEvent.toString(User.preferences.hourFormat),
		endHour: endEvent(startEvent).toString(User.preferences.hourFormat),
		timezone: User.preferences.defaultCalendar ? Calendar.signatureOf[User.preferences.defaultCalendar].calendar.timezone : User.preferences.timezone,
		summary : $("#subject_"+ID).text(),
		description : $("#body_" + ID).text(),
		startTime: startEvent.getTime(),
		endTime: endEvent(startEvent),
		timezones: Timezone.timezones,
		me: {name: User.me.name, mail: User.me.mail, acl: 'rowi', status: '1', delegatedFrom: '0', id: User.me.id},
		organizer: {name: User.me.name, mail: User.me.mail, acl: 'rowi', status: '1', delegatedFrom: '0' , id: User.me.id},
		statusParticipants: {accepted: 0, cancelled: 0, tentative: 0, unanswered:emails.length},
		location: '',
		attendee: $.map(emails, function( mail ){			
			var contact = false;
			var decoded =  Encoder.htmlDecode(mail);
			var newAttendeeName = "";

			var name = decoded.match(/"([^"]*)"/) ? decoded.match(/"([^"]*)"/)[1] : '';
			var mail = decoded.match(/<([^<]*)>[\s]*$/) ? decoded.match(/<([^<]*)>[\s]*$/)[1].toLowerCase() : decoded; 


			var user = DataLayer.get('user', ["=", "mail", mail], true);
			
			if( $.type(user) == "array" )
				user = user[0];

			if(user != ''){
				//user = {name : name , mail : mail};
				if(User.me.mail == user.mail)
					return(null);
				user.isExternal = (!!user && !(!!user.isExternal)) ? 0 : 1;

				return  DataLayer.merge({
					name: user.name,
					mail: user.mail,
					acl:  'r',
					delegatedFrom: '0',
					status: '4',
					isExternal: user.isExternal
				}, !!user.id ? {id : DataLayer.put('participant', {user: user.id, isExternal: user.isExternal})} : {id: DataLayer.put('participant', {user: user})}); 
			}else if(mail.match(/[\w-]+(\.[\w-]+)*@(([A-Za-z\d][A-Za-z\d-]{0,61}[A-Za-z\d]\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])/)){
				
				var userId = DataLayer.put('user', {
				name: name, 
				mail: mail, 
				isExternal: '1'
				});
				var newAttendeeId = DataLayer.put('participant', {
				user: userId, 
				isExternal: '1'
				});

				return  {
					id: newAttendeeId,
					name: name,
					mail: mail,
					acl:  'r',
					delegatedFrom: '0',
					status: '4',
					isExternal: '1'
				};
				
			}else
				return (null);
		})
	};

	schedulable  = DataLayer.merge(schedulable, 
		type == 'event' ? 
		{calendar: User.preferences.defaultCalendar ? User.preferences.defaultCalendar : Calendar.calendars[0], calendars: Calendar.calendars}
		: {group: Calendar.groups[0].id, groups: Calendar.groups, percentage: 0, isOrganizer: true}
	);
	
	return schedulable;
	    

}

function import_implements_calendar(){

	if(typeof(Encoder) == "undefined"){
		var lang =  !!User.me.lang ? User.me.lang : 'pt_BR';
		$.ajax({url: "../prototype/modules/calendar/scripts_import_mail.php?lang="+lang, async: false });
		DataLayer.dispatchPath = "../prototype/";	
	}
}


function select_import_folder(){
	//Begin: Verify if the file extension is allowed.
	var imgExtensions = new Array("eml","zip");
	var inputFile = document.form_import.file_1;
	if(!inputFile.value){
		alert(get_lang('File extension forbidden or invalid file') + '.');
		return false;
	}
	var fileExtension = inputFile.value.split(".");
	fileExtension = fileExtension[(fileExtension.length-1)];
	var deniedExtension = true;
	for(var i=0; i<imgExtensions.length; i++) {
		if(imgExtensions[i].toUpperCase() == fileExtension.toUpperCase()) {
			deniedExtension = false;
			break;
		}
	}
	if(deniedExtension) {
		alert(get_lang('File extension forbidden or invalid file') + '.');
		return false;
	}
	arrayJSWin['import_window'].close();
		connector.loadScript('wfolders');

	if ( typeof(wfolders) == "undefined" )
		setTimeout( 'select_import_folder()', 500 );
	else
		wfolders.makeWindow('null','import');
}
    //Verifica o contexto de importação
    function decodeOwner(){
	owner = User.me.id;
	var imapBox = (openTab.imapBox[currentTab].indexOf( 'user' ) >= 0 ? openTab.imapBox[currentTab].split(cyrus_delimiter) : [] );
	if(imapBox.length > 1){
	    var user = DataLayer.get('user', {filter: ['=','uid',imapBox[1]]});		    
	    owner = $.isArray(user) ? user[0].id : user.id;
	}	
	return owner;
    }

   function import_calendar(data){
        var import_url = '$this.db_functions.import_vcard&msg_folder='+data;
        var logUser;
        var up;
        var owner;

        function handler_import_calendar(data){
	    if(data === true){
		write_msg(get_lang("The event was imported successfully."));
	    }
	    else if( data['url'] )
	    {
		var form = document.createElement( "form" );

		form.setAttribute( "action", DEFAULT_URL + data['url'] + '&isPost=true' );
		form.setAttribute( "method", "POST" );

		document.body.appendChild( form );

		form.submit();
	    }
	    else
		write_msg(get_lang(data));
        }   
	if(defaultCalendar == "expressoCalendar" && $("#expressoCalendarid")[0]){
	    import_implements_calendar();
		$( "#import-dialog" ).dialog({
		    autoOpen: false,
		    height: 220,
		    modal: true,
		    resizable : false,
		    open: function(event, ui) {
			if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
		    },
		    close: function(event, ui){
			event.stopPropagation();
			if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
		    },
		    closeOnEscape: true
		});
                   
		$.ajax({
		    url: "controller.php?action="+import_url+'&from_ajax=true&id_user='+User.me.id+'&readable=true&cirus_delimiter='+cyrus_delimiter+'&analize=true&uidAccount='+decodeOwner(),
		    async: false,
		    success: function(data){
			data = connector.unserialize(data);
			var createDialog = function(typeImport, propaget){
                                               
			    if(typeof(typeImport) == "object"){
				var calendarIds = !!typeImport.calendar ? typeImport.calendar : Calendar[typeImport.type];
				typeImport = typeImport.action;
			    }
                                                   
			    switch(parseInt(typeImport)){
				case 1:
				case 7:
				case 10:
				    $("#select-agenda").html('');

				    var options = '';

				    if(calendarIds){
					for(var i = 0; i < calendarIds.length; i++)
					    options += '<option value="'+calendarIds[i]+'">'+Calendar.signatureOf[calendarIds[i]].calendar.name+'</option>'
				    }

				    $("#select-agenda").append(options);
				    $("#select-agenda").css("display", "block");

				    $("#import-dialog" ).dialog({
					buttons: {
					    Cancel: function() {
						$( this ).dialog( "close" );
					    },
					    "Importar" : function(){
						    $.ajax({
							url: "controller.php?action="+import_url+'&from_ajax=true&selected='+$("#select-agenda option:selected").val()+'&status='+$("#select-status option:selected").val()+'&uidAccount='+decodeOwner()+'&cirus_delimiter='+cyrus_delimiter,
							success: function(msg){
								var alt = ( (msg = connector.unserialize(msg)) == "ok") ? "_[[Imported successfully to]]" + " " : "_[[An error occurred while importing the event/task to the calendar]]" + " ";
							    alert( alt + $("#select-agenda option:selected").text() );
							}
						    });
						    $( this ).dialog( "close" );
					    }
					}
				    });
    
				    if(typeImport == 7){
					$("#import-dialog").find('#select-status option[value=1]').attr('selected','selected').trigger('change');
					$("#import-dialog").find('#select-status').attr('disabled','disabled');
				    }
				    break;
				case 3:
				    $.ajax({
					url: "controller.php?action="+import_url+'&from_ajax=true&selected=true',
					success: function(msg){
						alert( ( ( connector.unserialize(msg)) == "ok") ? "_[[Your event/task was removed]]" + " " : "_[[An error occurred while removing the event/task]]" );
					}
				    });
				    return;
				    break; 
				case 5:
				    $.ajax({
					url: 'controller.php?action='+import_url+'&from_ajax=true&selected=true&cirus_delimiter='+cyrus_delimiter,
					success: function(msg){
						 alert( ( ( connector.unserialize(msg)) == "ok") ? "_[[Your event/task has been updated successfully]]" : "_[[An error occurred while updating event/task]]" );
					}
				    });
				    return;
				    break; 
				case 6:
					var acceptedSuggestion = confirm("_[[Do you want to update the event/task according to the suggestion?]]");
					$.ajax({
					    url: "controller.php?action="+import_url+'&from_ajax=true&id_user='+User.me.id+'&selected=true&cirus_delimiter='+cyrus_delimiter+'&acceptedSuggestion='+acceptedSuggestion+"&from="+document.getElementById('from_values_'+currentTab).value+'&uidAccount='+decodeOwner(),
					    success: function(msg){
						if(acceptedSuggestion)
							alert( ( ( connector.unserialize(msg)) == "ok") ? "_[[Event/task successfully updated]]" + " " : "_[[An error occurred while updating the event]]" );
						}
					});
					return;
					break;
					case 4:
					case 9:
						alert('_[[Your event/task does not have changes!]]');
					    return;
					    break;
					case 11:
						 alert('_[[This event / task had already been caried for some of the participants and is now available on your shared calendar!]]');
					    return;
					    break;
					default:
					    up = true;
					    $("#select-agenda").css("display", "none");
					    $("#import-dialog" ).children("p:first-child").css("display", "none");
					    $("#import-dialog" ).dialog({
						height: 160,
						title: 'Atualizar Evento/Tarefa',
						buttons: {
						    Cancel: function() {
							$( this ).dialog( "close" );
						    },
						    "Atualizar": function() {
							$.ajax({
							    url: "controller.php?action="+import_url+'&from_ajax=true&cirus_delimiter='+cyrus_delimiter+'&selected='+ (parseInt(typeImport) == 2 || parseInt(typeImport) == 4 ? 'true' : $("#select-agenda option:selected").val()) +'&status='+$("#select-status option:selected").val()+'&uidAccount='+decodeOwner(),
							    success: function(msg){
									lert( ( (msg = connector.unserialize(msg)) == "ok") ? "_[[Updated successfully]]" : "_[[An error occurred while updating the event]]" );
							    }
							});
							$( this ).dialog( "close" );
						    }
						},
						close:function(event, ui) 
						{
						    if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
						},
						open: function(event, ui) 
						{
						    if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
						}
					    });
					    $(".ui-dialog-buttonpane").find(".ui-button:last").children().html("Atualizar");
				    }
				    $( "#import-dialog" ).dialog( "open" );
                                                                                                                                   
				};
				createDialog(data, true);
                                   
			    },
			    beforeSend: function( jqXHR, settings ){
				connector.showProgressBar();
			    },
			    complete: function( jqXHR, settings ){
				connector.hideProgressBar();
			    }
			});    
                    }
                    else
                    {
                    	$.Zebra_Dialog(get_lang("Do you confirm this import to your Calendar?"), {
							'type':     'question',
							'buttons':  [get_lang("No"), get_lang("Yes")],
							'overlay_opacity' : 0.5,
							'custom_class': 'custom-zebra-filter',
							'onClose':  function(caption) {
								if(caption == get_lang("Yes"))
									cExecute( import_url + "&from_ajax=true", handler_import_calendar);
							}
						});
                    }
    }
    
function open_msg_part(data){
	var handler_open_msg_part = function (data)
        {
            if(data.append == 1)
            {
                proxy_mensagens.get_msg(data.msg_number,data.msg_folder,false,function (data) {
                    if (onceOpenedHeadersMessages[data.msg_folder] == undefined) 
                    {
                        onceOpenedHeadersMessages[data.msg_folder] = [];
                    };
                    onceOpenedHeadersMessages[data.msg_folder][data.msg_number] = data;
                    show_msg(data);
                },false);
                partMsgs.push(data.msg_number);
	}
            else
               write_msg(data.append);
	}
        cExecute('$this.imap_functions.ope_msg_part&msg_folder='+data+'&save_folder=INBOX'+cyrus_delimiter+special_folders['Trash'] , handler_open_msg_part);	
}
function hack_sent_queue(data,rowid_message) {

	if (data.success != true) {
		queue_send_errors = true;
		expresso_local_messages.set_problem_on_sent(rowid_message,data);
	}
	else {
		expresso_local_messages.set_as_sent(rowid_message);
		if(document.getElementById('_action')) { //Não posso manter esse elemento, pois o connector irá criar outro com o mesmo id para a próxima mensagem.
			el =document.getElementById('_action');
			father = el.parentNode;
			father.removeChild(el);
		}
		send_mail_from_queue(false);
	}
}

function send_mail_from_queue(first_pass) {
	if(first_pass)
		modal('send_queue');
	var num_msgs = expresso_local_messages.get_num_msgs_to_send();
	if (num_msgs <= 0) {
		close_lightbox();
		return;
	}
	document.getElementById('text_send_queue').innerHTML = get_lang('Number of messages to send:')+' '+num_msgs;
	var handler_send_queue_message = function(data,rowid_message) {
		hack_sent_queue(data,this.ID_tmp);
	}
	var msg_to_send = expresso_local_messages.get_form_msg_to_send();
	if(!is_ie)
		ID_tmp = msg_to_send.rowid.value;
	else {//I.E kills me of shame...
		for (var i=0;i<msg_to_send.length;i++) {
			if(msg_to_send.elements[i].name=='rowid') {
				ID_tmp = msg_to_send.elements[i].value;
				break;
			}
		}
	}
	expresso_local_messages.set_as_sent(ID_tmp);
	cExecuteForm("$this.imap_functions.send_mail", msg_to_send, handler_send_queue_message,"queue_"+ID_tmp);
	send_mail_from_queue(false);
}

function check_mail_in_queue() {
	var num_msgs = expresso_local_messages.get_num_msgs_to_send();
	if(num_msgs>0) {
		control = confirm(get_lang('You have messages to send. Want you to send them now?'));
		if(control) {
			send_mail_from_queue(true);
		}
		return true;
	}
	else {
		return false;
	}
}

function force_check_queue() {
	if(!check_mail_in_queue()) {
		write_msg(get_lang("No messages to send"));
	}
}

function create_new_local_folder(parentFolderId, name){

        parentFolderId = parentFolderId.split("_");
        var parentName = parentFolderId[2];

    expresso_mail_archive.createFolder(parentName, name);

}

function create_new_folder(name_folder, base_path){
	//Limit reached folders
	if(preferences.imap_max_folders){
		if(cp_tree1.length == parseInt(preferences.imap_max_folders)){
			$(".folders-loading").removeClass("folders-loading");
			cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
			return write_msg(get_lang("Limit reached folders"));
		}
	}
	
	$.ajax({
		url : "controller.php?action=$this.imap_functions.create_mailbox",
		type : "POST",
		async : false,
		data : "newp="+name_folder+"&base_path="+base_path,
		success : function(data){
			data = connector.unserialize(data);
			if(data == "Mailbox already exists"){
				write_msg(get_lang("Mailbox already exists"));
			}else if(data.substring(data.indexOf("Permission"), data.length) == "Permission denied"){
				$(".folders-loading").removeClass("folders-loading");
				cExecute("$this.imap_functions.get_folders_list&onload=true", update_menu);
				return write_msg(get_lang("Permission denied"));
			}
			cExecute("$this.imap_functions.get_folders_list&onload=true", force_update_menu);
		},
		beforeSend: function( jqXHR, settings ){
		  	connector.showProgressBar();
		},
		  complete: function( jqXHR, settings ){
		  	connector.hideProgressBar();
		}
	});
}

function searchEmail(emailString){
		var arrayInvalidEmails = new  Array();
		arrayInvalidEmails[1] = '';
		var email;
		var arrayEmailsFull = new Array();
		arrayEmailsFull = emailString.split(',');
		var er_Email =  new RegExp("<(.*?)>"); 
                // TODO Use validateEmail of common functions !
		var er_ValidaEmail = new RegExp("^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})$");

		for (i=0; i < arrayEmailsFull.length; i++){
			email = er_Email.exec(arrayEmailsFull[i]);
			tempEmail = email == null  ? arrayEmailsFull[i]:email[1];
			tempEmail = tempEmail.replace(/^\s+|\s+$/g,"");
			
			if (tempEmail != '') {
				singleEmail = er_ValidaEmail.exec(tempEmail);
				if (singleEmail == null) {
					arrayInvalidEmails[0] = true;
					arrayInvalidEmails[1] += (email == null ? arrayEmailsFull[i] : email[1]) + "";
				}
			}
		}

		return arrayInvalidEmails;
}

/* Funçãoo que chama a tela para o usuário reportar um erro no email. */
function reports_window(currentTab)
{ 
	var _window = $("#error_reporter");

	_window.dialog(
	{
		height		: 370,
		width		: 520,
		resizable	: false,
		title		: get_lang("More information about the problem (optional)"),
		modal		: true,
		buttons		: [
						 {
						 	text	: get_lang("Close"), 
						 	click	: function()
						 	{
						 		$(this).dialog("destroy");
						 	}
						 },
						 {
						 	text	: get_lang('Report error'),
						 	click	: function()
						 	{
						 		var msgId 		= currentTab.substr(0, currentTab.indexOf("_"));
						 		var msgUser		= $(this).find('textarea').val();
						 		var msgFolder	= get_current_folder();
						 		var _this 		= $(this);

						 		var handleReportsWindow = function()
						 		{
						 			$(_this).dialog("destroy");
						 		}

								cExecute ("$this.imap_functions.report_mail_error&params="+msgId+";;"+msgUser+";;"+msgFolder, handleReportsWindow);
						 	}
						 }
					 ]	 	
	});				 

	_window.html( new EJS( {url: 'templates/default/emailTruncate.ejs'} ).render());
} 


DataLayer.codec( "message", "detail", {
  
	decoder:function( form ){
            var border_id = form.abaID;  
            //Defininindo pasta a ser salva mensagem
			var user_selected = $('#content_id_'+border_id).find('.from-select option:selected').text();
			var str_begin_name = user_selected.indexOf('<') + 1;
			var str_end_name = user_selected.indexOf('@');
			var user_selected_name = user_selected.substring(str_begin_name, str_end_name);
			
			if(user_selected.length > 0)
				var user_selected_email = user_selected.match(/<([^<]*)>[\s]*$/)[1];	
			else 
				var user_selected_email = User.me.mail;
			
			if(user_selected_email == User.me.mail){
				var prefix = 'INBOX';
			}else{
				var prefix = 'user' + cyrus_delimiter+user_selected_name;
				var has_folder = false;//Folder.get( (prefix + cyrus_delimiter + draftsfolder), false );
				var folders = DataLayer.get("folder");
				$.each(folders,function(index,value){
					if(value && value.id == prefix) 
						has_folder = true;
				});
                if(!has_folder){
					create_new_folder(draftsfolder, prefix);
				}
			}
			
            var folder_id = (openTab.imapBox[border_id] && openTab.type[border_id] < 6) ? openTab.imapBox[border_id]: prefix + cyrus_delimiter + draftsfolder;
            form.folder = folder_id;
            form.body = RichTextEditor.getData("body_"+border_id);
			form.type =  RichTextEditor.plain[border_id] ? 'plain' : 'html';	    
            form.attachments = listAttachment(border_id);
            form.uidsSave = uidsSave[border_id].toString();
            return( form );
      
	},

	encoder:function( pref ){
              
		return( pref );
	}

});

DataLayer.codec( "mailAttachment", "detail", {
  
	decoder: function(evtObj){
	
		if( notArray = $.type(evtObj) !== "array" )
			evtObj = [ evtObj ];

		var res = $.map(evtObj, function( form){
			return [$.map(form.files , function( files){
					return {source: files , disposition : form['attDisposition'+form.abaID]};
				})];
		});
	return notArray ? res[0] : res;
	},
      
	encoder: function(){}

      
});

function formatBytes(bytes) {
	if (bytes >= 1000000000) {
		return (bytes / 1000000000).toFixed(2) + ' GB';
	}
	if (bytes >= 1000000) {
		return (bytes / 1000000).toFixed(2) + ' MB';
	}
	if (bytes >= 1000) {
		return (bytes / 1000).toFixed(2) + ' KB';
	}
	return bytes + ' B';
};

function truncate(text, size){
	var result = text;
	if(text.length > size){
		result = text.substring(0,size) + '...';
	}
	return result;
}

/*
* @author Marcos Luiz Wilhelm (marcoswilhelm@gmail.com)
* @date 2012/07/17
* @brief Break out emails only with comma out of quote marks
*/
function break_comma (originalText){
	var quotesMarks = false;
	var completeString = "";
	var brokenEmails = new Array();
	originalText+=",";
	for(i=0; i<originalText.length; i++){
		var character = originalText.substr(i,1);
		if(character == "\""){
			quotesMarks = !quotesMarks;
		}
		if(!quotesMarks){
			if(character == ","){
				brokenEmails.push(completeString);
				completeString = "";
			}
			else
				completeString+=character;	
		}
		else
			completeString+=character;
	}
	return brokenEmails;
}

/*
* @author Marcos Luiz Wilhelm (marcoswilhelm@gmail.com)
* @date 2012/11/26
* @Remove HTML tags in the email body when the simple editor is used.
*/
function remove_tags (body){
	var div = $("<div>").attr("display", "none");
	div.html(body);
    div.html($.trim(div.text().replace(/[\t]+/g, '').replace(/[\n]+/g, '\n')));
	return div.text();
}

DataLayer.links('message');
DataLayer.poll('message',30);

//MAILARCHIVER-04
function services_retry(){
    try{
        connector.purgeCache(); 
        //window.alert('expresso var dump:\nenabled = ' + expresso_mail_archive.enabled + '\ntimer = ' + expresso_mail_archive.timer +'\ncounter = ' + expresso_mail_archive.counter);

        if ((expresso_mail_archive.enabled == null) && (expresso_mail_archive.timer == null) && (expresso_mail_archive.counter > 0)){
            connector.hideProgressBar();
            //connector.resetProgressBarText();
            write_msg(get_lang('Sorry, but you need to reload this web page. Click at reload page at web browsing top navigation.'));
            return;
        }

        if (arguments.length == 0){
           write_msg(get_lang('Trying to communicate with Mail Archiver...'));
        }

        connector.showProgressBar();
        var head = document.getElementById('send_queue');
        
        var script_xdr= document.createElement('script');
        var script_xdr_tag_id = 'mail_archiver_retry_xdr';    
        
        var script_request= document.createElement('script');
        var script_request_tag_id = 'mail_archiver_retry_request';                
        
        var script_cors= document.createElement('script');
        var script_cors_tag_id = 'mail_archiver_retry_cors';
        
        var script_utils= document.createElement('script');
        var script_utils_tag_id = 'mail_archiver_retry_utils';

        if(document.getElementById(script_xdr_tag_id)){
            document.getElementById(script_xdr_tag_id).parentNode.removeChild(document.getElementById(script_xdr_tag_id));
        }

        if(document.getElementById(script_request_tag_id)){
            document.getElementById(script_request_tag_id).parentNode.removeChild(document.getElementById(script_request_tag_id));
        }

        if(document.getElementById(script_cors_tag_id)){
            document.getElementById(script_cors_tag_id).parentNode.removeChild(document.getElementById(script_cors_tag_id));
        }
        
        if(document.getElementById(script_utils_tag_id)){
            document.getElementById(script_utils_tag_id).parentNode.removeChild(document.getElementById(script_utils_tag_id));
        }        

        //IE XDR ADAPTER
        script_xdr.type= 'text/javascript';
        script_xdr.src=  mail_archive_protocol + '://' + mail_archive_host + ':' + mail_archive_port  + '/arcservutil/cxf-addon-xdr-adapter.js';
        script_xdr.id= script_xdr_tag_id;
        head.appendChild(script_xdr);

        //CXF TRANSPORT OBJECT
        script_request.type= 'text/javascript';
        script_request.src= mail_archive_protocol + '://' + mail_archive_host + ':' + mail_archive_port  + '/arcservutil/cxf-addon-cors-request-object.js';
        script_request.id= script_request_tag_id;
        head.appendChild(script_request);

        //CXF CORS OBJECT
        script_cors.type= 'text/javascript';
        script_cors.src= mail_archive_protocol + '://' + mail_archive_host + ':' + mail_archive_port  + '/arcservutil/cxf-addon-cors-utils.js';
        script_cors.id= script_cors_tag_id;
        head.appendChild(script_cors);

        //CXF CORE
        script_utils.type= 'text/javascript';
        script_utils.src= mail_archive_protocol + '://' + mail_archive_host + ':' + mail_archive_port  + '/arcserv/ArchiveServices?js&nojsutils';
        script_utils.id= script_utils_tag_id;
        head.appendChild(script_utils);

        ttintval = window.setTimeout('check_services_restart()', 1000);
    }
    catch (e){
        write_msg(get_lang('Sorry, but Mail Archiver still seems to be sleeping. Check out your system services!'));
        connector.hideProgressBar();
        //connector.resetProgressBarText();
        check_services_tryouts = 1;
    }
}

//MAILARCHIVER-05
function check_services_restart(){
    try{
		write_msg(get_lang('Wait: attempt %1 from %2...', check_services_tryouts, check_services_tryouts_limit));
        if(check_services_tryouts <= 5){
            try{
                ArchiveServices = new web_service_mailarchiver_serpro__ArchiveServices();
                ArchiveServices.url = mail_archive_protocol + "://" + mail_archive_host + ":" + mail_archive_port + "/arcserv/ArchiveServices";
                window.clearInterval(ttintval);
                expresso_mail_archive.Restart(expresso_mail_archive);
            }
            catch (e){
                check_services_tryouts++;
                window.clearInterval(ttintval);
                ttintval = window.setTimeout('services_retry(true)',1);
            }
        }
        else{
            write_msg(get_lang('Sorry, but Mail Archiver still seems to be sleeping. Check out your system services!'));
            connector.hideProgressBar();
            //connector.resetProgressBarText();
            check_services_tryouts = 1;
        }
    }
    catch (e){
        write_msg(get_lang('Sorry, but Mail Archiver still seems to be sleeping. Check out your system services!'));
        connector.hideProgressBar();
        //connector.resetProgressBarText();
        check_services_tryouts = 1;
    }
}

