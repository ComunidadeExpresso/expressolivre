function validDateTask(){
	
	var errors = {
		'emptyInitData': '_[[Please enter a start date]]',
		'emptyInitHour': '_[[Please enter a start time]]',
		
		'invalidInitData' : '_[[Invalid start date]]',

		'equalData' : '_[[Start time equal to the end]]',
		'theirData' : '_[[End date less than the initial]]',
		'theirHour' : '_[[Final hour less than the initial]]',
		
		'dueInitDate' : '_[[End forecast lower than start date]]',
		'dueEndDate'  : '_[[End forecast lower than end date]]',
		'dueTime'	  : '_[[Must be set the time of end forecast]]',
		'dueDate'  	  : '_[[Must be set the end forecast]]',
		'invalidDueData' : '_[[Forecast end date invalid]]'
	};

    var start_date = $(".new-task-win.active .start-date").val();
    var start_time = $(".new-task-win.active .start-time").val();
	var due_date   = $(".new-task-win.active .date-prevision").val();
	var due_time   =  $.trim($(".new-task-win.active .time-prevision").val());

    if(start_date == "")
		return errors['emptyInitData'];
	else if(due_date != "" && due_time == "")
		return errors['dueTime'];
	else if(due_date == "" && due_time != "")
		return errors['dueDate'];

    var formatString = User.preferences.dateFormat + " " + User.preferences.hourFormat;
    var startDate = Date.parseExact( start_date + " " + $.trim(start_time) , formatString );

    if(startDate == null || startDate.getTime() < 0 )
		return errors['invalidInitData'];

    if(due_date != '' && due_time != '')
    {
        var dueDate = Date.parseExact( due_date + " " + $.trim(due_time) , formatString );

        if(dueDate.compareTo(startDate) == -1)
            return errors['dueInitDate'];
    }

    return false;
}

function todoStatus(view, status){
	if (status == "1"){
		UI.dialogs[view].find('.subitem .span_done').removeClass('finished noAction inProcess canceled').addClass('noAction');
	}else if (status == "2"){
		UI.dialogs[view].find('.subitem .span_done').removeClass('finished noAction inProcess canceled').addClass('inProcess');
	}else if (status == "3"){
		UI.dialogs[view].find('.subitem .span_done').removeClass('finished noAction inProcess canceled').addClass('finished');
	}else if (status == "4"){
		UI.dialogs[view].find('.subitem .span_done').removeClass('finished noAction inProcess canceled').addClass('canceled');
	}
}

function taskDetails(objTask, decoded, path, isMail, repeat, isActivityView) {

    $('.qtip.qtip-blue').remove();

    attendees = {};

    if (path == undefined) path = "";

    if (!decoded) objTask = DataLayer.decode("task:calendar", objTask);

    if (!isMail) objTask = DataLayer.encode("schedulable:task", objTask);

    if (typeof (objTask.id) == 'undefined') {
        objTask.alarms = Calendar.signatureOf[User.preferences.defaultCalendar || Calendar.groupIds[0]].defaultAlarms || false;
        objTask.useAlarmDefault = 1;
    }


    if(objTask.me.id == User.me.id){
        objTask.me.id = DataLayer.put('participant', {
            user: objTask.me.id, 
            mail: objTask.me.mail
        });
        objTask.organizer.id = objTask.me.id;
    }

    var dependsDelegate = function(reference, inverse){
        if(inverse){
            if(reference.find('input[name="attendee[]"]').val() == blkAddAtendee.find('li.organizer input[name="attendee_organizer"]').val())
                blkAddAtendee.find('li.organizer input[name="attendee_organizer"]').val(blkAddAtendee.find('.me input[name="attendee[]"]').val());
        }else{
            if(blkAddAtendee.find('.me input[name="attendee[]"]').val() == blkAddAtendee.find('li.organizer input[name="attendee_organizer"]').val())
                blkAddAtendee.find('li.organizer input[name="attendee_organizer"]').val(reference.find('input[name="attendee[]"]').val());
        }
    };

    var removeOthers = function(){
        var other = blkAddAtendee.find('.delegate.attendee-permissions-change-button');
        if(other.lenght)
            dependsDelegate(other.parents('li'), true);

        blkAddAtendee.find('.delegate').removeClass('attendee-permissions-change-button');
        blkAddAtendee.find('.ui-icon-transferthick-e-w').removeClass('attendee-permissions-change');
    
    };

    var callbackAttendee = function(){
        var checked = false;
        blkAddAtendee.find("li.not-attendee").addClass('hidden');
        
        blkAddAtendee.find("li .button").filter(".close.new").button({
            icons: {
            primary: "ui-icon-close"
            },
            text: false
        }).click(function () {
            DataLayer.remove('participant', $(this).parents('li').find('[type=checkbox]').val());
            if($(this).parent().find('.button.delegate').hasClass('attendee-permissions-change-button')){
                removeOthers();
                blkAddAtendee.find('.request-update').addClass('hidden');
                blkAddAtendee.find('.status option').toggleClass('hidden');
                        
                blkAddAtendee.find('option[value=1]').attr('selected','selected').trigger('change');
            }
                
            $(this).parents('li').remove();
                
            if(blkAddAtendee.find(".attendee-list li").length == 1)
                blkAddAtendee.find("li.not-attendee").removeClass('hidden');
        })
        .addClass('tiny disable ui-button-disabled ui-state-disabled')
        .removeClass('new').end()
        
        .filter(".delegate.new").button({
            icons: {
                primary: "ui-icon-transferthick-e-w"
            },
            text: false
        }).click(function () {
            var me = $(this).parents('li');
            if($(this).hasClass('attendee-permissions-change-button')){
                $(this).removeClass('attendee-permissions-change-button')   
                .find('.ui-icon-transferthick-e-w').removeClass('attendee-permissions-change').end();               
            
                me.find('input[name="delegatedFrom[]"]').val('');
                dependsDelegate(me, true);
                        
                blkAddAtendee.find('.request-update').addClass('hidden');
                blkAddAtendee.find('.status option').toggleClass('hidden');

                blkAddAtendee.find('option[value=1]').attr('selected','selected').trigger('change');
                    
            }else{
                removeOthers();
                
                $(this).addClass('attendee-permissions-change-button')   
                .find('.ui-icon-transferthick-e-w').addClass('attendee-permissions-change').end();               
                
                me.find('input[name="delegatedFrom[]"]').val(blkAddAtendee.find('.me input[name="attendee[]"]').val());
                
                dependsDelegate(me, false);
                    
                blkAddAtendee.find('.request-update').removeClass('hidden');
                if(blkAddAtendee.find('.status option.hidden').length == 1)
                    blkAddAtendee.find('.status option').toggleClass('hidden');
                    
                blkAddAtendee.find('option[value=5]').attr('selected','selected').trigger('change');
            }
        })
        .addClass('tiny disable ui-button-disabled ui-state-disabled')
        .removeClass('new').end()            
            
        .filter(".open-delegate.new").click(function(){
            if($(this).hasClass('ui-icon-triangle-1-e')){
                $(this).removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
                $(this).parents('li').find('.list-delegates').removeClass('hidden');
            }else{
                $(this).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
                $(this).parents('li').find('.list-delegates').addClass('hidden');
            }
            
        }).removeClass('new');

        UI.dialogs.addTask.find('.attendees-list li').hover(
            function () {
                $(this).addClass("hover-attendee");
                $(this).find('.button').removeClass('disable ui-button-disabled ui-state-disabled').end()
                .find('.attendee-options').addClass('hover-attendee');
            },
            function () {
                $(this).removeClass("hover-attendee");
                $(this).find('.button').addClass('disable ui-button-disabled ui-state-disabled').end()
                .find('.attendee-options').removeClass('hover-attendee');
            }
        );        
    }

    /**
     * canDiscardTaskDialog deve ser true se não houver alterações no task
     */
    canDiscardTaskDialog = true;
    /**
     * zebraDiscardTaskDialog é uma flag indicando que uma janela de confirmação (Zebra_Dialog)
     * já estão aberta na tela, uma vez que não é possivel acessar o task ESC utilizado para fechá-la
     */
    zebraDiscardTaskDialog = false;

    var html = DataLayer.render(path + 'templates/task_add.ejs', {
        task: objTask
    });

    if (!UI.dialogs.addTask) {

        UI.dialogs.addTask = jQuery('#sandbox').append('<div title="'+'_[[Create Task]]'+'" class="new-task-win active"> <div>').find('.new-task-win.active').html(html).dialog({
            resizable: false,
            modal: true,
            autoOpen: false,
            width: "auto",
            position: 'center',
            close: function (event, ui) {
                /**
                 * Remove tooltip possivelmente existente
                 */
                if ($('.qtip.qtip-blue.qtip-active').length) $('.qtip.qtip-blue.qtip-active').qtip('destroy');
                attendees = {};
            },
            beforeClose: function (event, ui) {

                if (!canDiscardTaskDialog && !zebraDiscardTaskDialog) {
                    zebraDiscardTaskDialog = true;
                    window.setTimeout(function () {
                        $.Zebra_Dialog('_[[Your changes in the task were not saved. Do you want to discard changes?]]', {
                            'type': 'question',
                            'overlay_opacity': '0.5',
                            'buttons': ['_[[Discard changes]]', '_[[Continue editing]]'],
                            'onClose': function (clicked) {
                                if (clicked == '_[[Discard changes]]') {
                                    canDiscardTaskDialog = true;
                                    /**
                                     *Remoção dos anexos da task caso seja cancelado a edição
                                     */
                                    DataLayer.rollback();

                                    var ids = false;
                                    $.each($('.attachment-list input'), function (i, input) {
                                        DataLayer.put('attachment', {
                                            id: '' + input.value
                                        });
                                        DataLayer.remove('attachment', '' + input.value);
                                        ids = true;
                                    });
                                    if (ids) DataLayer.commit();

                                    UI.dialogs.addTask.dialog('close');
                                } else {
                                    zebraDiscardTaskDialog = false;
                                }

                                /**
                                 * Uma vez aberta uma janela de confirmação (Zebra_Dialog), ao fechá-la
                                 * com ESC, para que o task ESC nÃo seja propagado para fechamento da
                                 * janela de edição de tasks, deve ser setada uma flag indicando que
                                 * já existe uma janela de confirmação aberta.
                                 */
                                if (!clicked) {
                                    window.setTimeout(function () {
                                        zebraDiscardTaskDialog = false;
                                    }, 200);
                                }
                            }
                        });

                    }, 300);

                }
                //DataLayer.rollback();
                return canDiscardTaskDialog;
            },
            dragStart: function (task, ui) {
                if ($('.qtip.qtip-blue.qtip-active').length) $('.qtip.qtip-blue.qtip-active').qtip('destroy');
            }
        });

    } else {
        UI.dialogs.addTask.html(html);
    }

    var tabs = UI.dialogs.addTask.children('.content').tabs({
        select: function (task, ui) {
            if ($('.qtip.qtip-blue.qtip-active').length) $('.qtip.qtip-blue.qtip-active').qtip('destroy');
        }
    });
    var group = DataLayer.get('calendar', objTask.group);

    if (group.timezone != objTask.timezone) {
        UI.dialogs.addTask.find('.calendar-addtask-details-txt-timezone').find('option[value="' + objTask.timezone + '"]').attr('selected', 'selected').trigger('change');
        UI.dialogs.addTask.find('.calendar_addtask_details_lnk_timezone').addClass('hidden');
        $('.calendar-addtask-details-txt-timezone').removeClass('hidden');

    }

    UI.dialogs.addTask.find('.calendar_addtask_details_lnk_timezone').click(function (e) {
        $(this).addClass('hidden');
        $('.calendar-addtask-details-txt-timezone').removeClass('hidden');
        e.prtaskDefault();
    });

    UI.dialogs.addTask.find('.button.remove').button({
    text:false, 
    icons:{
        primary:'ui-icon-close'
    }
    }).click(function(el){
        var id;
        if( id = $(this).parent().find('input[name="alarmId[]"]').val())
        DataLayer.remove('alarm', id);
        $(this).parent().remove().find('li').is(':empty');
    });

    /*Seleciona a agenda padrão para visualização edição de um task*/
    if (objTask.id) UI.dialogs.addTask.find('select[name="group"] option[value="' + objTask.group + '"]').attr('selected', 'selected').trigger('change');
	
	UI.dialogs.addTask.find(':input').change(function(event){
    if (event.keyCode != '27' && event.keyCode != '13')
	canDiscardTaskDialog = false;
	}).keydown(function(event){
		if (event.keyCode != '27' && event.keyCode != '13')
		canDiscardTaskDialog = false;
	});

    /* Checkbox allday */
    UI.dialogs.addTask.find('input[name="allDay"]').click(function () {
        $(this).attr("checked") ? UI.dialogs.addTask.find('.start-time, .end-time').addClass('hidden') : UI.dialogs.addTask.find('.start-time, .end-time').removeClass('hidden');
        updateMap(true);
    });
	
	todoStatus('addTask', (objTask.taskStatus  != undefined) ? objTask.taskStatus  : 1);
	
	//Conclusão das Tarefas
	var conclusionTask = function(e){
		var percentageTask = UI.dialogs.addTask.find('input[name="percentage"]');
		if( percentageTask.val() == "")
			percentageTask.val(0);
		percentageTask.blur().focus();
		
		var percentageValue = parseInt(percentageTask.val());
		var statusTask = UI.dialogs.addTask.find('select[name=taskStatus]');
		
		if(percentageValue <= 0){
			statusTask.find('option.taskStatus-noAction').attr('selected', 'selected');
			todoStatus('addTask', 1);		
		}else if(percentageValue == 100){
			statusTask.find('option.taskStatus-finished').attr('selected', 'selected');
			todoStatus('addTask', 3);
		}else{
			statusTask.find('option.taskStatus-inProcess').attr('selected', 'selected');
			todoStatus('addTask', 2);
		}		
		setTimeout(function(){
			percentageTask[0].selectionStart = percentageTask.val().length;
			percentageTask[0].selectionEnd = percentageTask.val().length;
		}, 10)
	}
	UI.dialogs.addTask.find('input[name="percentage"]').spinner({ min: 0, max: 100 }).keyup(conclusionTask).next().find(".ui-spinner-button").click(conclusionTask);	

	//Status das Tarefas
	UI.dialogs.addTask.find('select[name=taskStatus]').change(function(){
		var statusSelected = $('select[name=taskStatus] option:selected').val();
		var percentageTask = UI.dialogs.addTask.find('input[name="percentage"]');
		
		if (statusSelected == "1"){
			percentageTask.val(0);
		}else if(statusSelected == "2"){
			percentageTask.val(percentageTask.val() != 0 ? (percentageTask.val() == 100 ? 99: percentageTask.val()) : 1);
		}else if(statusSelected == "3"){
			percentageTask.val(100);
		}else if(statusSelected == "4"){
			percentageTask.val(percentageTask.val() != 100 ? percentageTask.val() : 99);
		}
		todoStatus('addTask', statusSelected);
	});
	
    UI.dialogs.addTask.find('.button').button();
    UI.dialogs.addTask.find('.button.add').button({
        icons: {
            secondary: "ui-icon-plus"
        }
    });

    // ==== validation tasks ====
    UI.dialogs.addTask.find(".input-group .h1").Watermark("_[[Untitled task]]");
    if (User.preferences.hourFormat.length == 5) {
        UI.dialogs.addTask.find(".end-time, .start-time, .time-prevision").mask("99:99", {
            completed: function () {
                updateMap();
            }
        });
    } else {
        $.mask.definitions['{'] = '[ap]';
        $.mask.definitions['}'] = '[m]';
        UI.dialogs.addTask.find(".end-time, .start-time, .time-prevision").mask("99:99 {}", {
            completed: function () {
                $(this).val(date.Calendar.defaultToAmPm($(this).val()));
                $(this).timepicker("refresh");
                $(this).val($(this).val().replace(/[\.]/gi, ""));
                updateMap();
            }
        });
    }
    UI.dialogs.addTask.find(".number").numeric();
    User.preferences.dateFormat.indexOf('-') > 0 ? UI.dialogs.addTask.find(".date").mask("99-99-9999", {
        completed: function () {
            updateMap();
        }
    }) : UI.dialogs.addTask.find(".date").mask("99/99/9999", {
        completed: function () {
            updateMap();
        }
    });

    UI.dialogs.addTask.find(".menu-addtask").children(".delete").click(function () {
        $.Zebra_Dialog('_[[Are you sure you want to delete this task?]]', {
            'type': 'question',
            'overlay_opacity': '0.5',
            'buttons': ['_[[No]]', '_[[Yes]]'],
            'onClose': function (clicked) {
                if (clicked == '_[[Yes]]') {
                    canDiscardTaskDialog = true; /* Remove por filtro */
                    DataLayer.removeFilter('schedulable', {
                        filter: ['AND', ['=', 'id', objTask.id],
                            ['=', 'calendar', objTask.group],
                            ['=', 'user', (objTask.me.user ? objTask.me.user.id : objTask.me.id)]
                        ]
                    });
                    Calendar.rerenderView(true); /********************/
                    UI.dialogs.addTask.dialog("close");
                }
            }
        });
    }).end()

    .children(".cancel").click(function () {
        UI.dialogs.addTask.dialog("close");
    }).end()

    .children(".save").click(function () { /* Validação */
        UI.dialogs.addTask.find('input[name="summary"]').focus();
		
	if (msg = validDateTask()) {
            $(".new-task-win.active").find('.messages-validation').removeClass('hidden').find('.message label').html(msg);
            return false;
        }
	canDiscardTaskDialog = true;
	
	if(isActivityView)
	    updateActivity = true;

        UI.dialogs.addTask.children().find('form.form-addtask').submit();
        UI.dialogs.addTask.dialog("close");

    }).end()

    .children(".export").click(function () {
        UI.dialogs.addTask.children().find(".form-export").submit();
    });

    var attendeeHtml = DataLayer.render(path + 'templates/attendees_task.ejs', {
        task: objTask
    });

    // load template of attendees
    var blkAddAtendee = UI.dialogs.addTask.find('#calendar_addtask_details6').append(attendeeHtml);

    if(objTask.attendee.length)
        callbackAttendee();

    /*
     *   Opções de delegação do participante/organizer
     */      
    blkAddAtendee.find(".button.participant-delegate").button({
        icons: {
        primary: "ui-icon-transferthick-e-w"
        },
        text: false
    }).click(function () {
        if($(this).hasClass('attendee-permissions-change-button')){
        if(!$(this).hasClass('disable')){
            $(this).removeClass('attendee-permissions-change-button')   
            .find('.ui-icon-transferthick-e-w').removeClass('attendee-permissions-change').end();               
            blkAddAtendee.find('.block-add-attendee.search').addClass('hidden').find('dd').addClass('hidden');
            blkAddAtendee.find('.block-add-attendee.search dt').html('Adicionar outros contatos');
        }
        }else{                                  
        $(this).addClass('attendee-permissions-change-button')   
        .find('.ui-icon-transferthick-e-w').addClass('attendee-permissions-change').end();               
        blkAddAtendee.find('.block-add-attendee.search dt').html('Delegar participação para');
        blkAddAtendee.find('.block-add-attendee.search').removeClass('hidden').find('dd').removeClass('hidden');
        blkAddAtendee.find('.block-add-attendee.search input.search').focus();
        }
    })
    .addClass('tiny');

    var dates = UI.dialogs.addTask.find('input.date').datepicker({
        dateFormat: User.preferences.dateFormat.replace(/M/g, 'm').replace(/yyyy/g, 'yy'),
        onSelect: function (selectedDate) {
            updateMap();
        }
    });

    UI.dialogs.addTask.find('input.time').timepicker({
        closeText: 'Ok',
        hourGrid: 4,
        minuteGrid: 10,
        ampm: ((User.preferences.hourFormat.length > 5) ? true : false),
        timeFormat: "hh:mm tt",
        onSelect: function (selectedDateTime) {
        	if ((selectedDateTime.value == '__:__') || (selectedDateTime.value == '__:__ __'))
				selectedDateTime.value = "";
			  
            if (!(User.preferences.hourFormat.length == 5)) $(this).val(selectedDateTime.replace(/[\.]/gi, ""));
            updateMap();
        },
        onClose: function (selectedDateTime) {
            if (!(User.preferences.hourFormat.length == 5)) $(this).val(selectedDateTime.replace(/[\.]/gi, ""));
        },
        beforeShow: function (selectedDateTime) {
			if ((selectedDateTime.value == '__:__') || (selectedDateTime.value == '__:__ __'))
				selectedDateTime.value = "";
        }
    });
    
    UI.dialogs.addTask.find(".attendee-list-add .add-attendee-input input").Watermark("_[[enter an email to invite]]");
    /* 
    * Trata a edição de um novo participante adicionado
    */
    var hasNewAttendee = false;

    blkAddAtendee.find('.attendee-list-add .add-attendee-input span').click(function(data){
	blkAddAtendee.find('.attendee-list-add .add-attendee-input input').keydown();
    });

    blkAddAtendee.find('.attendee-list-add .add-attendee-input input').keydown(function(event) {
	if (event.keyCode == '13' && $(this).val() != '' || (event.keyCode == undefined && $(this).val() != '')) {
	    Encoder.EncodeType = "entity";
	    $(this).val(Encoder.htmlEncode($(this).val()));

	    newAttendeeEmail = false;
	    newAttendeeName  = false;
	    skipAddNewLine   = false;

	    var info = $(this).val();

	    /**
	    * email válido?
	    */
	    info.match(/^[\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*@(([\w-]+\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/) ? 
	    newAttendeeEmail = info : newAttendeeName = info;

	    /**
	    * 1) busca no banco para saber se o usuário já existe
	    *		1.1) se existe, atualiza as info na lista de participantes e nao abre o tooltip
	    *		1.2) se não existe
	    *			a) salva como novo usuario externo no banco (apenas com email)
	    *			b) exibe tooltip pedindo o nome
	    *			c) se o usuário preenche tooltip e salva, atualiza com o nome o usuário recém criado
	    *			d) se o usuário cancela o tooltip, fica o usuário salvo apenas com email e sem nome
	    */

	    var user = DataLayer.get('user', ["=", "mail", $(this).val()]);
	    if(!!user && user[0].id)
		attendees[user[0].id] = {
		    name: user[0].name
		    };

	    /**
	    * guarda o último tooltip aberto referente à lista de participantes 
	    */
	    lastEditAttendeeToolTip = [];

	    /**
	    * Valida email e salva um participante externo 
	    */
	    var saveContact = function() {
		Encoder.EncodeType = "entity";

		var currentTip = $('.qtip-active');
		newAttendeeName  = currentTip.find('input[name="name"]').val();
		newAttendeeEmail = currentTip.find('input[name="mail"]').val();

		if (!(!!newAttendeeEmail.match(/^[\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*@(([\w-]+\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/))) {
		    currentTip.find('.messages').removeClass('hidden').find('.message label').html('_[[Invalid Email.]]');
		    return false;
		}

		DataLayer.put('user', {
		    id:userId, 
		    name:newAttendeeName, 
		    mail:newAttendeeEmail, 
		    isExternal:isExternal
		});

		lastEditAttendeeToolTip.find('label')
		.filter('.name').html(Encoder.htmlEncode(newAttendeeName)).attr('title', Encoder.htmlEncode(newAttendeeName)).end()
		.filter('.mail').html(Encoder.htmlEncode(newAttendeeEmail)).attr('title', Encoder.htmlEncode(newAttendeeEmail));

		blkAddAtendee.find('.attendee-list-add .add-attendee-input input').val('');
		return true;
	    }

	    /**
	    * Formata e adequa um tootip abert para edição de um participante na lista
	    */
	    var onShowToolTip = function(arg0) {
		$('.qtip-active .button.close').button({
		    icons: {
			primary: "ui-icon-close"
		    },
		    text: false
		});
		$('.qtip-active .button').button()
		.filter('.save').click(function(event, ui) {
		    if(saveContact())
			lastEditAttendeeToolTip.qtip("destroy");
		    else
			return false;
		}).end()
		.filter('.cancel').click(function(event, ui) {
		    lastEditAttendeeToolTip.qtip("destroy");
		})

		/** 
		* Trata o ENTER no campo da tooltip, equivalente a salvar 
		* o novo convidado.
		*/
		$('.qtip-active input').keydown(function(event) {
		    if (event.keyCode == '13') {						
			if (saveContact())						
			    lastEditAttendeeToolTip.qtip("destroy");

			lastEditAttendeeToolTip.qtip("destroy");
			event.preventDefault();
		    }
		})
		.filter('[name="name"]').Watermark("_[[enter the contact name]]").end()
		.filter('[name="mail"]').Watermark("_[[inform the contact email]]");
	    }

	    /**
	    * Se o email digitado já foi adicionado na lista,
	    * o usuário deve ser avisado e um botão de edição deve ser exibido
	    */
	    if(blkAddAtendee.find('label.mail[title="' + newAttendeeEmail + '"]').length) {
		hasNewAttendee  = false;
		newAttendeeName = blkAddAtendee.find('label.mail[title="' + newAttendeeEmail + '"]').parents('li').find('label.name').attr('title');

		blkAddAtendee.find('.email-validation').removeClass('hidden')
		.find('.message label').html('_[[The above user has been added!]]' + '<a class="small button">' + '_[[Edit]]' + '</a>')
		.find(".button").button().click(function () { 
		    /**
		    * Se o usuário optar por editar o participante anteriormente adicionado,
		    * uma tooltip deve ser aberta para este participante, viabilizando a edição
		    */
		    blkAddAtendee.find("ul.attendee-list").scrollTo('label.mail[title="' + newAttendeeEmail + '"]');
		    /**
		    * Remove tooltip possivelmente existente
		    */
		    if (lastEditAttendeeToolTip.length && lastEditAttendeeToolTip.data('qtip'))
			lastEditAttendeeToolTip.qtip('destroy');

		    lastEditAttendeeToolTip = blkAddAtendee.find('label.mail[title="' + newAttendeeEmail + '"]').parents('li');
		    lastEditAttendeeToolTip.qtip({
			show: {
			    ready: true, 
			    solo: true, 
			    when: {
				event: 'click'
			    }
			},
		    hide: false,
		    content: {
			text: $('<div></div>').html( DataLayer.render( path+'templates/attendee_quick_edit.ejs', {
			    attendee:{
				name:newAttendeeName, 
				mail:newAttendeeEmail
			    }
			} ) ), 
		    title: {
			text:'_[[Details of the participant]]',
			button: '<a class="button close" href="#">' + '_[[Close]]' +'</a>'
		    }
		    },
		    style: {
			name: 'blue', 
			tip: {
			    corner: 'leftMiddle'
			}, 
			border: {
			    width: 4, 
			    radius: 8
			}, 
			width: {
			    min: 230, 
			    max:230
			}
		    },
		position: {
		    corner: {
			target: 'rightMiddle',
			tooltip: 'leftMiddle'
		    },
		    adjust: {
			x:0, 
			y:0
		    }
		}
		});
	    lastEditAttendeeToolTip.qtip("api").onShow = onShowToolTip;
	});
    skipAddNewLine = true;
    } else {
	hasNewAttendee  = true;
	blkAddAtendee.find('.email-validation').addClass('hidden');
    }


    var isExternal = (!!user && !(!!user.isExternal)) ? 0 : 1;

    /**
    * Remove tooltip possivelmente existente
    */
    if (lastEditAttendeeToolTip.length && lastEditAttendeeToolTip.data('qtip'))
	lastEditAttendeeToolTip.qtip('destroy');

    userId = '';
    var newAttendeeId = '';

    if (user){
	if (!skipAddNewLine) {
	    user[0].id =  DataLayer.put('participant', {
		user: user[0].id, 
		isExternal: isExternal, 
		acl: 'r'
	    });
	    user[0].acl = 'r';
	    user[0].isDirty = !!!objTask.id;

	    blkAddAtendee.find('dd.attendee-list ul.attendee-list').append(
		DataLayer.render(path+'templates/participants_add_itemlist.ejs', user)
		)
	    .scrollTo('max');
	    callbackAttendee();
	}

	$(this).val('');

    } else if (!skipAddNewLine) {
	/**
	* a) salva como novo usuario externo no banco (apenas com email) e...
	* adiciona novo contato externo à lista de convidados
	*/

	userId = DataLayer.put('user', {
	    name: newAttendeeName, 
	    mail: newAttendeeEmail, 
	    isExternal: isExternal
	});
	newAttendeeId = DataLayer.put('participant', {
	    user: userId, 
	    isExternal: isExternal
	});


	blkAddAtendee.find('dd.attendee-list ul.attendee-list').append(
	    DataLayer.render(path+'templates/participants_add_itemlist.ejs', [{
		id:newAttendeeId, 
		name: newAttendeeName, 
		mail: newAttendeeEmail, 
		isExternal: 1, 
		isDirty: !!!objTask.id
		}])
	    ).scrollTo('max');
	callbackAttendee();

	/** 
	* Adiciona tootip para atualização dos dados do contato externo
	* recém adicionado.
	*/
	lastEditAttendeeToolTip = blkAddAtendee.find('dd.attendee-list li:last');
	lastEditAttendeeToolTip.qtip({
	    show: {
		ready: true, 
		solo: true, 
		when: {
		    event: 'click'
		}
	    },
	hide: false,
	content: {
	    text: $('<div></div>').html( DataLayer.render( path+'templates/attendee_quick_edit.ejs', {
		attendee:{
		    name:newAttendeeName, 
		    mail:newAttendeeEmail
		}
	    } ) ), 
	title: {
	    text:'_[[Details of the participant]]',
	    button: '<a class="button close" href="#">' + '_[[Close]]' + '</a>'
	}
	},
	style: {
	    name: 'blue', 
	    tip: {
		corner: 'leftMiddle'
	    }, 
	    border: {
		width: 4, 
		radius: 8
	    }, 
	    width: {
		min: 230, 
		max:230
	    }
	},
    position: {
	corner: {
	    target: 'rightMiddle',
	    tooltip: 'leftMiddle'
	},
	adjust: {
	    x:0, 
	    y:0
	}
    }
    });

    lastEditAttendeeToolTip.qtip("api").onShow = onShowToolTip;

    $(this).val('');


    }
    event.preventDefault();
    }

    });

    blkAddAtendee.find('.add-attendee-search .ui-icon-search').click(function (evt) {
        blkAddAtendee.find('.add-attendee-search input').keydown();
    });

    blkAddAtendee.find('.add-attendee-search input').keydown(function (evt) {

        if (evt.keyCode == '13' || typeof (evt.keyCode) == 'undefined') {
            var result = DataLayer.get('user', ["*", "name", $(this).val()], true);

            /**
             * TODO: trocar por template
             */
            blkAddAtendee.find('ul.search-result-list').empty().css('overflow', 'hidden');
            if (!result) {
                blkAddAtendee.find('ul.search-result-list').append('<li><label class="empty">'+'_[[No results found.]]'+'</label></li>');
            }

            for (i = 0; i < result.length; i++)
            result[i].enabled = (blkAddAtendee.find('dd.attendee-list ul.attendee-list label.mail[title="' + result[i].mail + '"]').length) ? false : true;

            blkAddAtendee.find('ul.search-result-list').append(DataLayer.render(path + 'templates/participants_search_itemlist.ejs', result));

            blkAddAtendee.find('ul.search-result-list li').click(function (event, ui) {
                if ($(event.target).is('input')) {
                    old_item = $(event.target).parents('li');
                    newAttendeeId = DataLayer.put('participant', {
                        user: old_item.find('.id').html(),
                        isExternal: old_item.find('.isExternal').html()
                    });

                    attendees[old_item.find('.id').html()] = old_item.find('.name').html();

                    blkAddAtendee.find('dd.attendee-list ul.attendee-list').append(DataLayer.render(path + 'templates/participants_add_itemlist.ejs', [{
                        id: newAttendeeId,
                        name: old_item.find('.name').html(),
                        mail: old_item.find('.mail').html(),
                        isExternal: old_item.find('.isExternal').html(),
                        notEvent: true,
                        isDirty: !! !objTask.id,
						isDelegate: !!(objTask.me.id != objTask.organizer.id)
                    }])).scrollTo('max');
                    /**
                     * Delegação de participação de um participante com permissão apenas de leitura
                     *
                     */
                    if (objTask.me.id != objTask.organizer.id) {

                        blkAddAtendee.find('.block-add-attendee.search').addClass('hidden');
                        blkAddAtendee.find('.block-add-attendee.search dt').html('_[[Add other contacts]]');

                        blkAddAtendee.find('.status option').toggleClass('hidden');
                        blkAddAtendee.find('option[value=5]').attr('selected', 'selected').trigger('change');
                        blkAddAtendee.find('.request-update').removeClass('hidden');

                        blkAddAtendee.find('dd.attendee-list ul.attendee-list li .button.close').parents('li').find('input[name="delegatedFrom[]"]').val(blkAddAtendee.find('.me input[name="attendee[]"]').val());

                        blkAddAtendee.find('.me .participant-delegate').addClass('disable ui-button-disabled ui-state-disabled');
                        blkAddAtendee.find(".button.close").button({
                            icons: {
                                primary: "ui-icon-close"
                            },
                            text: false
                        }).click(function () {

                            $(this).parents('li').find('input[name="delegatedFrom[]"]').val('');
                            blkAddAtendee.find('.request-update').addClass('hidden');
                            blkAddAtendee.find('.status option').toggleClass('hidden');
                            blkAddAtendee.find('option[value=1]').attr('selected', 'selected').trigger('change');
                            blkAddAtendee.find('.me .participant-delegate').removeClass('disable ui-button-disabled ui-state-disabled attendee-permissions-change-button').find('.ui-icon-person').removeClass('attendee-permissions-change').end();

                            DataLayer.remove('participant', $(this).parents('li').find('[type=checkbox]').val());
                            $(this).parents('li').remove();
                        }).addClass('tiny');
                    } else {
                        callbackAttendee();
                        old_item.remove();
                    }
                }
            });

            evt.preventDefault();
        }
    });

    UI.dialogs.addTask.find('.row.fileupload-buttonbar-task .button').filter('.delete').button({
        icons: {
            primary: "ui-icon-close"
        },
        text: 'Excluir'
    }).click(function () {
        $.Zebra_Dialog('_[[Are you sure you want to delete all attachments?]]', {
            'type': 'question',
            'overlay_opacity': '0.5',
            'buttons': ['_[[No]]', '_[[Yes]]'],
            'onClose': function (clicked) {
                if (clicked == '_[[Yes]]') {

                    var ids = [];
                    $.each($('.attachment-list input'), function (i, input) {
                        DataLayer.remove('schedulableToAttachment', {
                            filter: ['=', 'id', '' + input.value]
                        });
                    });
                    $('div.new-task-win .attachment-list input').remove();
                    $('div.new-task-win .row.fileupload-buttonbar .attachments-list p').remove();
		    $('div.new-task-win .btn-danger.delete').addClass('hidden');
                }
            }
        });
    }).end().filter('.close').button({
        icons: {
            primary: "ui-icon-close"
        },
        text: false
    }).click(function () {
        DataLayer.remove('schedulableToAttachment', $(this).parents('p').find('input[name="fileId[]"]').val());
        $(this).parents('p').remove();
    }).end().filter('.downlaod-archive').button({
        icons: {
            primary: "ui-icon-arrowthickstop-1-s"
        },
        text: false
    });

    extendsFileupload('task', path);

    disponibily(objTask, path, attendees, 'task');

    UI.dialogs.addTask.find('.button-add-alarms').click(function () {
        var li_attach = DataLayer.render(path + 'templates/alarms_add_itemlist.ejs', {
            type: 1
        });

        jQuery('.task-alarms-list').append(li_attach).find('.button.remove').button({
            text: false,
            icons: {
                primary: 'ui-icon-close'
            }
        }).click(function (el) {
            $(this).parent().remove().find('li').is(':empty')
        });
        // valicacao de campos numericos
        $('.number').numeric();
    });

    UI.dialogs.addTask.find(':input').change(function(event){
	if (event.keyCode != '27' && event.keyCode != '13')
	    canDiscardTaskDialog = false;
    }).keydown(function(event){
	if (event.keyCode != '27' && event.keyCode != '13')
	    canDiscardTaskDialog = false;
    });

    UI.dialogs.addTask.dialog('open');
}