MsgsCallbackFollowupflag = {

    '#FollowupflagMessageIdError': function(){
	alert('_[[Unable flagging this message. \nError details: message does not contain the attribute message-id]]' + '.');
    },
    '#FollowupflagLimitError': function(){
	alert('_[[Unable flagging this message. \nError details: flags limit reached for this folder]]' + '.');
    },
    '#FollowupflagParamsError': function(){
	alert('_[[Unable flagging this message. \nError details: message does not contain all the required attributes]]' + '.');
    }
    
}

function updateCacheFollowupflag(msgNumber, msgFolder, op){
	if(op){
		if(typeof msgNumber == 'object'){
			var extend = DataLayer.get('followupflagged', {
				filter: ['AND', ['IN', 'messageNumber', msgNumber], ['IN', 'folderName', msgFolder]],
				criteria: {deepness: 1}
			}, true);
		} else{
			var extend = DataLayer.get('followupflagged', {
				filter: ['AND', ['=', 'messageNumber', msgNumber], ['=', 'folderName', msgFolder]],
				criteria: {deepness: 1}
			}, true);
		}

		
		if(extend != "" || extend != 'undefined' || extend != []){
			for(var i = 0; i < extend.length; i++){
				if(!(onceOpenedHeadersMessages[extend[i].folderName])){
					onceOpenedHeadersMessages[extend[i].folderName] = {};
				}
				if(!(onceOpenedHeadersMessages[extend[i].folderName][extend[i].messageNumber])){
					onceOpenedHeadersMessages[extend[i].folderName][extend[i].messageNumber] = [];	
				}
				onceOpenedHeadersMessages[extend[i].folderName][extend[i].messageNumber]['followupflagged'] = {};
				DataLayer.merge(onceOpenedHeadersMessages[extend[i].folderName][extend[i].messageNumber]['followupflagged'], extend[i]);
				
				/*
				if(onceOpenedHeadersMessages[extend[i].folderName]){
					if(onceOpenedHeadersMessages[extend[i].folderName][extend[i].messageNumber]){
						onceOpenedHeadersMessages[extend[i].folderName][extend[i].messageNumber]['followupflagged'] = {};
						DataLayer.merge(onceOpenedHeadersMessages[extend[i].folderName][extend[i].messageNumber]['followupflagged'], extend[i]);
					}
				}*/

			}
		}
	}else{
		onceOpenedHeadersMessages[msgFolder][msgNumber]['followupflagged'] = undefined;	
	}
}

function init_followup(data){

	winElement = data.window;
	selectedMessageIds = data.selectedMessages;
	folder = current_folder;

	 winElement.find('input[name="alarmDate"]').change(function(event){
	 	winElement.find('input[name="alarmTime"]').attr('disabled', ( $(this).val() == "" ? 'disabled' : false));
	 });

	 winElement.find('input[name="alarmDate"]').keyup(function(event){
	 	winElement.find('input[name="alarmTime"]').attr('disabled', ( $(this).val() == "" ? 'disabled' : false)).val('');
	 });

	winElement.find('.button').button()
	
	.filter('.menu-configure-followupflag .cancel').click(function(){
		winElement.dialog("close");
		$.each(selectedMessageIds, function(index, value){	
			$('tr#' + value + ' .td-followup-flag')//.find('img').remove();
		});
		
	}).end()
	
	.filter('.menu-configure-followupflag .save').click(function(){	
		var saveFollowupflagged = function(){
			var idFollowupflagged = winElement.find('[name="followupflagId"]').val();
			idFollowupflagged = idFollowupflagged.split(',');
			for (x=0; x<idFollowupflagged.length; x++){
				(idFollowupflagged[x] == "false") ? idFollowupflagged[x] = false : idFollowupflagged;
			}
			for(i=0; i<selectedMessageIds.length; i++){
					var isDone = winElement.find('[name="done"]').is(':checked') ? 1 : 0;
					var alarmDate = false;
					var doneDate  = false;
					var folder_name;
					var folders = [];
					var messages = [];
					var roles = get_selected_messages_search_role().split(',');
					for (var i=0; i < selectedMessageIds.length; i++ ){
						if (currentTab == 0) {
							folder_name = current_folder;
							var messageNumber = selectedMessageIds[i];
						}else{
							var tr = $('[role="'+roles[i]+'"]');
							folder_name = $(tr).attr('name'); 
							var id = $(tr).attr('id'); 
							var messageNumber = id.replace(/_[a-zA-Z0-9]+/,"");
						}
						folders.push(folder_name);
						
							var followupflagged = DataLayer.merge({
								uid : User.me.id,
								followupflagId : followupflagId, 
								folderName : folder_name, 
								messageNumber : messageNumber,
								isDone: isDone,
								isSent: 0,
								backgroundColor : backgroundColor
							}, !!idFollowupflagged[i] ? {id: idFollowupflagged[i]} : {});
						
						if (alarmDate = winElement.find('[name="alarmDate"]').datepicker("getDate")) {
							if (alarmTime = winElement.find('[name="alarmTime"]').datepicker("getDate")) {
								alarmDate.set({hour:alarmTime.getHours(), minute:alarmTime.getMinutes()});
							}
							followupflagged.alarmDeadline = alarmDate.toString('yyyy-MM-dd HH:mm:ss');
						}

						if (doneDate = winElement.find('[name="doneDate"]').datepicker("getDate")) {
							if (doneTime = winElement.find('[name="doneTime"]').datepicker("getDate")) {
								doneDate.set({hour:doneTime.getHours(), minute:doneTime.getMinutes()});
							}
							followupflagged.doneDeadline = doneDate.toString('yyyy-MM-dd HH:mm:ss');
						}
						
						/**
						 * Aplica o ícone correspondente na lista de mensagens do expressoMail
						 */
						if(current_folder == folder_name){
							var flagged = $('#td_message_followup_' + messageNumber + ', tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).find(".flag-edited");
						} else{
							var flagged = $('tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).find(".flag-edited");
						}
						if(isDone){
							flagged.find("img").attr("src", "../prototype/modules/mail/img/flagChecked.png").css("margin-left","-3px");
						}else{
							flagged.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"});			
						}
						
						var followupflagName = winElement.find('[name="name"] option:selected').text();
						if(current_folder == folder_name){
							$('#td_message_followup_' + messageNumber + ', ' + 
							'tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).attr('title', followupflagName).find(".flag-edited").css("background", backgroundColor); 
						}else{
							$('tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).attr('title', followupflagName).find(".flag-edited").css("background", backgroundColor); 
						}  
						/**
						 * Salva ou, caso já exista, atualiza
						 */
						DataLayer.put('followupflagged', followupflagged);
					}
					
					DataLayer.commit(false, false, function(data){
						winElement.find('.menu-configure-followupflag .delete').button("option", "disabled", false);
						updateCacheFollowupflag(selectedMessageIds, folders, true);
						winElement.dialog("close");
						alarmFollowupflagged('followupflagAlarms');
						var fail = 'success';
						$.each(data,function(index,value){
							if (typeof value == 'string'){
								fail = value;
							}				
						});
						if (fail == '#FollowupflagMessageIdError'){
							 alert('_[[One or more messages could not be posted. \nError details: message contains the message-id attribute]]' + '.');
						}
						else if (fail == '#FollowupflagLimitError'){
							 alert('_[[One or more messages could not be posted. \nError details: flags limit reached for this folder]]' + '.');
						}
						/*DIVIDE O ARRAY EM ARRAYS MENORES*/
						var splice = function(arr){
							var newArray = [];
							while (arr.length > 500){
								newArray[newArray.length] = arr.splice(0,500);
							}
							if (arr.length) 
								newArray[newArray.length] = arr;
							return newArray;
						}
						if (selectedMessageIds.length > 500){
							var arrayIndex = selectedMessageIds;
							$.each(splice(arrayIndex),function(iterator,subarray){
								var flaggeds = DataLayer.get('followupflagged', {filter: [
										'AND', 
										['IN', 'messageNumber', subarray], 
										['IN', 'folderName', folder_name]
								]});
								$.each(subarray,function(index,value){	
									var flagged = false;
									$.each(flaggeds,function(i,v){
										if (v.messageNumber == value)
											flagged = true;
									});
									if (!flagged)
										$('#td_message_followup_'+value).find(".flag-edited").css("background","#cccccc").find('img').remove();						
									$('tr#' + value + ' .td-followup-flag').find('img').remove();
								});
							});	
						}					
					});
					winElement.find('.menu-configure-followupflag .save').button("option", "disabled", true);
					
			
			}
			selectAllFolderMsgs(false);
		}
		winElement.find('[name="name"]').next().data("autocomplete")._trigger("change");
		var backgroundColor = winElement.find('[name="backgroundColor"]').val();
		var followupflagId  = winElement.find('[name="name"] option:selected').val();
		if (followupflagId == 'custom') {
			DataLayer.put('followupflag', {name:winElement.find('[name="name"] option:selected').text(), uid:User.me.id});
			DataLayer.commit(false, false, function(data){
				$.each(data, function(index, value) {
					if(typeof value == 'object'){
						followupflagId = value.id;
					}
				});
				winElement.find('[name="name"] option[value="custom"]').val(followupflagId);
				saveFollowupflagged();
			});
		}else{
			saveFollowupflagged();
		}	

	}).end()
	
	.filter('.menu-configure-followupflag .delete').click(function(){
		if (selectedMessageIds.length == 0) $(this).button("option", "disabled", true);
		/** TODO Mudar quando melhorias forem implementadas na API de atualização do cache */
		DataLayer.remove('followupflagged', false);
		DataLayer.get('followupflagged');
		var roles = get_selected_messages_search_role().split(',');
		for (var i=0; i < selectedMessageIds.length; i++ ){
				if (currentTab == 0) {
					folder_name = current_folder;
					var messageNumber = selectedMessageIds[i];
				}else{
					var tr = $('[role="'+roles[i]+'"]');
					folder_name = $(tr).attr('name'); 
					var id = $(tr).attr('id'); 
					var messageNumber = id.replace(/_[a-zA-Z0-9]+/,"");
				}				
				
			if(onceOpenedHeadersMessages[folder_name][messageNumber]['followupflagged']){
				if(onceOpenedHeadersMessages[folder_name][messageNumber]['followupflagged'].id){
					var flag_id = onceOpenedHeadersMessages[folder_name][messageNumber]['followupflagged'].id;
					DataLayer.remove('followupflagged', flag_id );
					/**
					 * TODO - corrigir o formato do ID no DataLayer, para que seja utilizado o ID composto
					 * ao invés do ID do PostgreSQL atualmente em uso.
					 */
					 
					/**
					 * # hack necessário enquanto o DataLayer não reconhece o ID composto. Trocar o 
					 * código abaixo pela chamada trivial de DataLayer.remove('followupflagged', idCompost)
					 */
					// var data = {};
					// data[ 'followupflagged://' + folder_name + '/' + messageNumber + '#' + flag_id ] = false;
					// DataLayer.dispatch('Sync', data, false, true);
					
					if(current_folder == folder_name){
						flag = $('#td_message_followup_' + messageNumber + ', ' + 
						  'tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).attr('title', '').find(".flag-edited").css("background", '#CCC');
						$('#td_message_followup_' + messageNumber + ', ' + 
							'tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).find(".flag-edited")
							.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"});
					}else{
						flag = $('tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).attr('title', '').find(".flag-edited").css("background", '#CCC');
						$('tr[role="'+messageNumber+'_'+folder_name+'"] #td_message_followup_search_' + messageNumber).find(".flag-edited")
							.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"});
					}
					updateCacheFollowupflag(messageNumber, folder_name, false);
				}
			}
			
			
		}
		DataLayer.commit(false, false, function(){
			winElement.dialog("close");
			alarmFollowupflagged('followupflagAlarms');
			
			$.each(selectedMessageIds, function(index, value){	
				$('tr#' + value + ' .td-followup-flag').find('img')//.remove();
			});
			
			selectAllFolderMsgs(false);	
		});


			
	});
	
	/**
	 * Se houver mudança, habilita o botão "Save"
	 */
	winElement.find(':input').change(function(event){
		if (event.keyCode != '27' && event.keyCode != '13')
			winElement.find('.menu-configure-followupflag .save').button("option", "disabled", false);
	}).keydown(function(event){
		if (event.keyCode != '27' && event.keyCode != '13')
			winElement.find('.menu-configure-followupflag .save').button("option", "disabled", false);
	});
	
	winElement.find('.date').datepicker();
	winElement.find('.time').timepicker({});
	winElement.find('[name="name"]').combobox()
	//pega o botão criado
	.next().next().click(function (event, ui){ 	
		$(".ui-autocomplete.ui-menu li").css("position","relative");
		$(".ui-autocomplete.ui-menu li a:gt(5)").append("<span class='ui-icon ui-icon-only ui-icon-close delete_followupflag'></span>").find("span").click(function(event){
			var id = $('.followupflag-configure').find('option')[$(this).parents('li').index()].value;
			var nameFollowupflag = $('.followupflag-configure').find('option')[$(this).parents('li').index()].text;
			var removeLi = $(this).parents("li");		
			
			$.Zebra_Dialog('_[[All messages flagged with the flag type ]]' + '<strong>'+ nameFollowupflag + '</strong>' + '_[[will be removed. This action cannot be undone. Do you want to continue?]]', {
				'type':     'question',
				'custom_class': (is_ie ? 'configure-zebra-dialog custom-zebra-filter' : 'custom-zebra-filter'),
				'title':    'Atenção',
				'buttons': ['Sim','Não'],		
				'overlay_opacity': '0.5',
				'onClose':  function(caption) {
					if(caption == 'Sim'){
						var listFollowupflag = DataLayer.get('followupflagged', ['=', 'followupflagId', id]);
						for (var i=0; i < listFollowupflag.length; i++)
							DataLayer.remove('followupflagged', listFollowupflag[i].id);

						DataLayer.remove('followupflag',false);					
						DataLayer.get('followupflag');

						DataLayer.remove('followupflag', ''+id);						
						DataLayer.commit(false, false, function(data){
							$('[title="'+nameFollowupflag+'"]').attr('title', '').find('div').css({backgroundColor:'#CCC'});
							$(removeLi).remove();
							$('option[value="'+ id +'"]').remove();
							$('.ui-autocomplete.ui-menu li:first');	
							$('.followupflag-configure').find('option:first').attr("selected","selected");							
							$('.ui-autocomplete-input').val($('.followupflag-configure').find('option:selected').text());
							
							for(var i=0; i<listFollowupflag.length; i++){
								if(listFollowupflag[i].id == winElement.find('[name="followupflagId"]').val()){
									winElement.find('[name="followupflagId"]').val("");
								}
							}						
						});			
						event.stopImmediatePropagation();
					}
				}
			});	
			if(is_ie)
				$(".ZebraDialogOverlay").css("z-index","1006");	
		});

	});
	winElement.find('.ui-corner-right.ui-button-icon').attr('title', '_[[Show all items]]');

	winElement.find('[name="alarmDate"],[name="alarmTime"]').attr("disabled","disabled");
	
	winElement.find('.menu-configure-followupflag .delete').button("option", "disabled", true);	
	var idFollowupflag = winElement.find('[name="followupflagId"]').val();
		idFollowupflag = idFollowupflag.split(',');
	
	$.each(idFollowupflag, function(index,value){
		if (value != "false" ){
			winElement.find('.menu-configure-followupflag .delete').button("option", "disabled", false);
		}
	});

	winElement.find('[name="alarm"]').click(function(){
		if($(this).is(":checked")){
			winElement.find('[name="alarmDate"]').removeAttr("disabled");			
		}else{
			winElement.find('[name="alarmDate"],[name="alarmTime"]').attr("disabled","disabled").val('');
		}
	});

	if(winElement.find('[name="alarm"]').is(":checked")){
		winElement.find('[name="alarmDate"],[name="alarmTime"]').removeAttr("disabled");
	}
	
	winElement.find('[name="done"]').click(function(){
		if($(this).is(":checked")){
			winElement.find(".input-done input").attr("disabled","disabled");
		}else{
			winElement.find(".input-done input").removeAttr("disabled");
		}
	});


	winElement.find(".followupflag-color-fields").hide();
	winElement.find(".followupflag-color.sample-list .sample-item").click(function(){
		winElement.find('.menu-configure-followupflag .save').button("enable");
		winElement.find(".followupflag-color.sample-list .sample-item").removeClass("selected");
		$(this).addClass("selected");
		var color = $(this).attr('alt');
		winElement.find('[name="backgroundColor"]').css('background-color', color).val(color)
	});
	
	winElement.find(".followupflag-color.sample-list .sample-item.selected").trigger('click');
				
	winElement.find('[name="setColor"]').change(function(){	
		if(winElement.find('[name="setColor"]').val() == "default"){
			winElement.find(".followupflag-color.sample-list").show("fast");
			winElement.find(".followupflag-color-fields").hide();
			winElement.find(".followupflag-color.sample-list .sample-item.selected").trigger('click');
		} else if(winElement.find('[name="setColor"]').val() == "custom"){	
			winElement.find(".followupflag-color-fields").show("fast");
			winElement.find(".followupflag-color.sample-list").hide();
			winElement.find(".colorwell").focus();
		}		
	});
	
	if(winElement.find('[name="setColor"] option:selected').val() == "custom"){
		winElement.find('[name="setColor"]').trigger("change");
	}

	var colorpickerPreviewChange = function(color) {
		winElement.find('.menu-configure-followupflag .save').button("enable");
		winElement.find('.colorwell-selected').val(color).css('background-color', color);
		winElement.find('.flag-color-preview').css('background',color);
	} 

	var f = $.farbtastic(winElement.find('.colorpicker'), colorpickerPreviewChange);
	var selected;					
	winElement.find('.colorwell').each(function () {
		f.linkTo(this);
	}).focus(function() {
		if (selected) {
			$(selected).removeClass('colorwell-selected');
		}
		$(selected = this).addClass('colorwell-selected');
		f.linkTo(this, colorpickerPreviewChange);
		f.linkTo(colorpickerPreviewChange);
		
	});
	if(winElement.find('[name="setColor"] option:selected').val() == "custom"){
		winElement.find(".colorwell").focus();
	}

}


/**
 * constrói as três possíveis janelas de alerta, utilizando o mesmo template
 * para o parametro alert_type, espera-se vazio, followupflagAlarms ou filtersAlarms
 * vazio: quando serão carregadas todas as modais de alarmes
 * followupflagAlarms: quando serão carregadas as modais referentes à sinalizações
 * filtersAlarms: quando será carregada a modal de filtros (nesse caso o parametro filter_list deve conter a lista de mensagens a ser exibida na modal)
 */
function alarmFollowupflagged(alert_type, filter_list){
	var currentDate = new Date().toString("dd/MM/yyyy");
	var data = {alarmDeadline: false, doneDeadline: false, filtersAlarms: false};

	switch(alert_type){
		case 'followupflagAlarms':
			$('.doneDeadline').remove();
			$('.alarmDeadline').remove();
			data.alarmDeadline = $.cookie("fadeAlarm") != currentDate ? true : false;
			data.doneDeadline = $.cookie("fadeCompleted") != currentDate ? true : false;
		break;
		case 'filtersAlarms':
			$('.filtersDeadline').remove();
			data.filtersAlarms = $.cookie("fadeFilterAlarm") != currentDate ? true : false;
		break;
		default:
			$('.gray').remove();
			data.alarmDeadline = $.cookie("fadeAlarm") != currentDate ? true : false;
			data.doneDeadline = $.cookie("fadeCompleted") != currentDate ? true : false;
			data.filtersAlarms = $.cookie("fadeFilterAlarm") != currentDate ? true : false;
		break;
	};

	var startDate = (new Date()).set({hour:0, minute:0, second:0}).toString('yyyy-MM-dd 00:00:00');
	var endDate = (new Date()).set({hour:0, minute:0, second:0}).addHours(24).toString('yyyy-MM-dd 00:00:00');

	if(data.alarmDeadline){
		var decodeAlarms = {'sent': [], 'task':[]};	
		alarms = DataLayer.get('followupflagged', 
			{
				filter: ['AND', ['<', 'alarmDeadline', endDate], ['=','isSent','0'], ['=','isDone','0']], 
				criteria: {deepness: 1}
			});

		if(alarms.length > 0){
			var itens = [];
			for(var i = 0; i < alarms.length; i++){

				var date = Date.parseExact(alarms[i]['alarmDeadline'], 'yyyy-MM-dd HH:mm:ss');
           		alarms[i]['alarmDeadline'] = date.toString('dd/MM HH:mm');

				var nameFollowupflag = alarms[i]['followupflag']['id'] < 7 ? get_lang(alarms[i]['followupflag']['name']) : alarms[i]['followupflag']['name'];
				var li_alarm = alarms[i]['alarmDeadline'] + ' - ' + nameFollowupflag + ' - ' + alarms[i]['message']['headers']['subject'];

				if(alarms[i]['doneDeadline'] != ''){
					var dateDone = Date.parseExact(alarms[i]['doneDeadline'], 'yyyy-MM-dd HH:mm:ss');
					if(dateDone.getTime() < $.now())
						continue;
				}

				if(date.getTime() <= $.now())
					decodeAlarms.sent.push({
						"msg_number" : alarms[i]['messageNumber'],
						"msg_folder" : alarms[i]['folderName'],
						"a"			 : truncate(li_alarm, 34),
						'id' : alarms[i].id
					});
           		else
           			decodeAlarms.task.push({
						a: truncate(li_alarm, 34),
						sentTime:  date.getTime() / 1000,
						id: alarms[i].id,
						'msg_folder': alarms[i].folderName,
						'msg_number': alarms[i].messageNumber
					});
			}

			if(decodeAlarms.task.length)
				alarmDeadline.load(decodeAlarms.task);

			if(decodeAlarms.sent.length)
				data.alarmDeadline = {
						alarms: decodeAlarms.sent,
						title: '_[[Flagged]]',
						caption: (itens.length == 1) ? '_[[You have one undone message today]]' + ':' : '_[[You have $decodeAlarms.sent.length$ follow ups due for today]]' + ':',
						type: 'alarmDeadline'
					};
			else
				data.alarmDeadline = false;
		}else
			data.alarmDeadline = false;
	}

	if(data.doneDeadline){
		alarms = DataLayer.get('followupflagged', {filter: ['AND', ['>', 'doneDeadline', startDate], ['<', 'doneDeadline', endDate]], criteria: {deepness: 1}});

		if(alarms.length > 0){
			var itens = [];
			for(var i = 0; i < alarms.length; i++){

			    var date = Date.parseExact(alarms[i]['doneDeadline'], 'yyyy-MM-dd HH:mm:ss');
			    alarms[i]['doneDeadline'] = date.toString('dd/MM HH:mm');

			    var nameFollowupflag = alarms[i]['followupflag']['id'] < 7 ? get_lang(alarms[i]['followupflag']['name']) : alarms[i]['followupflag']['name'];
			    var li_alarm = alarms[i]['doneDeadline'] + ' - ' + nameFollowupflag + ' - ' + truncate(alarms[i]['message']['headers']['subject'], 15);

			    itens.push({
				    a: truncate(li_alarm, 34),
				    id: alarms[i].id,
				    'msg_folder': alarms[i].folderName,
				    'msg_number': alarms[i].messageNumber
			    });
			}
		    data.doneDeadline = {
			alarms: itens,
			title: '_[[Done]]',
			caption: (itens.length == 1) ? '_[[You have one message in conclusion today]]' + ':' : '_[[You have $itens.length$ flagged messages for today]]' + ':',
			type: 'doneDeadline'
		    };
		}else
		    data.doneDeadline = false;
	}

	if(data.filtersAlarms){

		alarms = filter_list;

		if(alarms.length > 0){
		    var itens = [];

		    for(var i=0; i<alarms.length; i++){
			alarms[i]['udate'] =  new Date(alarms[i]['udate']*1000).toString('dd/MM HH:mm');
			var li_alarm = alarms[i]['udate'] + ' - ' + alarms[i]['from'] + ' - ' + alarms[i]['subject'];

			itens.push({
				'msg_number' : alarms[i]['msg_number'],
				'msg_folder' : alarms[i]['msg_folder'],
				a	     : truncate(html_entities(li_alarm), 34),
				id : alarms[i].id
			}); 				
		}

		data.filtersAlarms = {
				alarms: itens,
				title: '_[[Filter by sender]]',
				caption: (itens.length == 1) ? '_[[You have an archived message:]]' : '_[[You have $itens.length$ messages archived]]' + ':',
				type: 'filtersDeadline',
				captions: {
				    singular:'You have one undone message today:', 
				    plural:"You have %1 undone messages today:"
				}
			};

		}else
		    data.filtersAlarms = false;
	}

	for (var i in data)
		if(data[i] != false)
			showAlarmsModal(data[i]);

	// controle de qual janela de alarme estará maximizada
	$('.gray').find('.content-alarm').hide();
	$('.gray').find('.header-alarm [name="header-icon"]').removeClass('minimize-alarm').addClass('maximize-alarm');
	
	if($('.gray').length > 0){
		if($('.gray').hasClass('filtersDeadline')){
			$('.filtersDeadline').find('.content-alarm').show();
			$('.filtersDeadline .header-alarm [name="header-icon"]').removeClass('maximize-alarm').addClass('minimize-alarm');
		}else if($('.gray').hasClass('alarmDeadline')){
			$('.alarmDeadline').find('.content-alarm').show();
			$('.alarmDeadline .header-alarm [name="header-icon"]').removeClass('maximize-alarm').addClass('minimize-alarm');	
		}else if($('.gray').hasClass('doneDeadline')){
			$('.doneDeadline').find('.content-alarm').show();
			$('.doneDeadline .header-alarm [name="header-icon"]').removeClass('maximize-alarm').addClass('minimize-alarm');	
		}
	}
}

function showAlarmsModal(alarm){
	
	var ok_function = function(event, type, type_cookie){
		if($(event.target).parents('.'+type).find('[name="stopAlert"]').is(':checked')){
			$.cookie(type_cookie, (new Date).toString("dd/MM/yyyy"), { 
				expires: 1 
			});
		}
	}

	// carrega o template dos alarmes e cria a modal utilizando o plugin freeow
	var dialogText = DataLayer.render("../prototype/modules/mail/templates/followupflag_alarm_list.ejs", alarm);
	var titulo = '<div class="header-alarm"><span class="img_title"></span><span class="title-alarm"><strong>'+alarm.title+'</strong></span><span name="header-icon" class="maximize-alarm"></span></div>';
	
	$("#freeow").freeow(titulo, dialogText, {
		classes: ["gray", alarm.type],
		autoHide: false, 
		startStyle: null,
		onClick: function(event){
			var type = '';
			var type_cookie = '';
			if($(this).hasClass('alarmDeadline')){
				type = 'alarmDeadline';
				type_cookie = 'fadeAlarm';
			}else if($(this).hasClass('doneDeadline')){
				type = 'doneDeadline';
				type_cookie = 'fadeCompleted';
			}else if($(this).hasClass('filtersDeadline')){
				type = 'filtersDeadline';
				type_cookie = 'fadeFilterAlarm';
			}
			if($(event.target).hasClass('stop-alert-alarm')){
				return;
			}
			if($(event.target).hasClass('minimize-alarm')){
				$('.'+type).find('.content-alarm').hide();
				$(event.target).removeClass('minimize-alarm').addClass('maximize-alarm');
				return;
			}
			if($(event.target).hasClass('maximize-alarm')){
				$('.'+type).find('.content-alarm').show();
				$(event.target).removeClass('maximize-alarm').addClass('minimize-alarm');
				return;
			}
			if($(( !!$.browser.safari ) ? event.target.parentElement : event.target).hasClass('confirm-alarm')){
				ok_function(event, type, type_cookie);
				$('.'+type).remove();
				return;
			}
			return false;
		}
	});
	// elementos do freeow desnecessários
	$('.gray .background .content p').remove();
	$('.gray .icon').remove();
	$('.gray .close').remove();

	$('div.gray.alarmDeadline .button.delete').button({
		text: false,
		icons:{
			primary: 'ui-icon-close'
		}
	})
	
	// botão ok da modal com jquery button
	$('.content-alarm button').button();
}

function cancelAlarm(element, idAlarm, messageNumber, folderName){

	$(element).parents('li').remove();

	var view = 'div.gray.alarmDeadline';
	var length = $(view).find('ul.message-list li').length;

	if(length > 0){
		var msg = '';
		if(length == 1)
			msg = '_[[You have a follow up due for today]]' + ':';
		else
			msg = '_[[You have $length$ follow ups due for today]]' + ':';

		$(view).find('span.subtitle-alarm strong').html(msg);
	}else
		$(view).remove();

	DataLayer.put('followupflagged', 
		{
			id: idAlarm , 
			isSent: '1',
			folderName: folderName,
			messageNumber: messageNumber,
			uid: User.me.uid
		});

	DataLayer.commit();

}

alarmDeadline = {


	load: function(alarm){
		var currentDate = new Date().toString("dd/MM/yyyy")
		if($.cookie("fadeAlarm") != currentDate)
			for(var i = 0; i < alarm.length; i++)
			    	this.addAlarm( alarm[i] );
	},

	addAlarm: function(alarm){

		    DataLayer.task( parseInt(alarm['sentTime']) , function( timestamp ){
		    	var view = 'div.gray.alarmDeadline';

		    	if(!$(view+' li.message-item.'+alarm.id).length){

			    	var currentDate = new Date().toString("dd/MM/yyyy")
					if($.cookie("fadeAlarm") != currentDate)
				    	
				    	if($('div.gray.alarmDeadline').length){
				    		
				    		$(view).find('ul.message-list').append(DataLayer.render("../prototype/modules/mail/templates/followupflag_alarmDeadline_add_item_list.ejs", alarm))

				    		var length = $(view).find('ul.message-list li').length;
				    		var msg = '';
				    		if(length == 1)
				    			msg = '_[[You have a follow up due for today]]' + ':';
				    		else
				    			msg = '_[[You have $length$ follow ups due for today]]' + ':';

				    		$(view).find('span.subtitle-alarm strong').html(msg);

				    		$(view+' .button.delete').button({
								text: false,
								icons:{
									primary: 'ui-icon-close'
								}
							});

				    	}else{
				    		var item = {
								alarms: [alarm],
								title: '_[[Follow ups]]',
								caption: '_[[You have one undone message today]]' + ':',
								type: 'alarmDeadline'
							};
							showAlarmsModal(item);
				    	}
				    }
				
		    });
	}

}

if(preferences['use_alert_filter_criteria'] == "1")
{
    $('#main_table').ready(function(){
        handlerMessageFilter = function (data) {
            alarmFollowupflagged(null, data);
        }
        /* Busca  nas pastas indexadas para ver se há novas mensagens com a flag $FilteredMessage */
        cExecute ("$this.imap_functions.getFlaggedAlertMessages&folders="+fromRules, handlerMessageFilter);
    });
}


