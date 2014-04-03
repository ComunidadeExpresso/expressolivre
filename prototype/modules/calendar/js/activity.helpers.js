function activityDetails( objActivity, decoded, path, isMail, repeat){

    tasks = {length: 0};
	
    if(path == undefined)
	path = "";
		
    if( !decoded )
	objActivity = DataLayer.decode( "schedulable:group", objActivity );

    if(!isMail)
	objActivity = DataLayer.encode( "schedulable:activity", objActivity );

    /**
    * canDiscardActivityDialog deve ser true se não houver alterações no evento
    */
    canDiscardActivityDialog = true;
    /**
	 * zebraDiscardActivityDialog é uma flag indicando que uma janela de confirmação (Zebra_Dialog)
	 * já está aberta na tela, uma vez que não é possivel acessar o evento ESC utilizado para fechá-la
	 */
    zebraDiscardActivityDialog = false;
	
	var html = DataLayer.render( path+'templates/activity_add.ejs', {
	    activity:objActivity
	});	
			
	if (!UI.dialogs.addActivity) {

	    UI.dialogs.addActivity = jQuery('#sandbox').append('<div title="_[[Create Activity]]" class="new-activity-win active"> <div>').find('.new-activity-win.active').html(html).dialog({
    		resizable: false, 
    		modal:true, 
    		autoOpen: false,
    		width:735, 
    		position: 'center', 
    		close: function(event, ui) {
    			/**
    			 * Remove tooltip possivelmente existente
    			 */
    			if ($('.qtip.qtip-blue.qtip-active').length)
    				$('.qtip.qtip-blue.qtip-active').qtip('destroy');						
    			
                attendees  = {};
    		},
    		beforeClose: function(event, ui) {
    		
    		    if (!canDiscardActivityDialog && !zebraDiscardActivityDialog) {
        			zebraDiscardActivityDialog = true;
        			window.setTimeout(function() {
        			    $.Zebra_Dialog('_[[Your changes in the activity were not saved. Do you want to discard changes?]]', {
        				'type':     'question',
        				'overlay_opacity': '0.5',
        				'buttons':  ['_[[Discard changes]]', '_[[Continue editing]]'],
        				'onClose':  function(clicked) {
        				    if(clicked == '_[[Discard changes]]') {
            					canDiscardActivityDialog = true;
            					/**
            					*Remoção dos anexos do eventos caso seja cancelado a edição
            					*/
            					DataLayer.rollback();

            					var ids = false;
            					$.each($('.attachment-list input'), function (i, input) {
            					    DataLayer.put('attachment', {id: ''+input.value});
            					    DataLayer.remove('attachment', ''+input.value);
            						ids = true;
            					});
            					if(ids)
            						DataLayer.commit();

            					UI.dialogs.addActivity.dialog('close');
        				    }else{
        					   zebraDiscardActivityDialog = false;
        				    }
        										
        				    /**
        				    * Uma vez aberta uma janela de confirmação (Zebra_Dialog), ao fechá-la
        				    * com ESC, para que o evento ESC não seja propagado para fechamento da
        				    * janela de edição de eventos, deve ser setada uma flag indicando que
        				    * já existe uma janela de confirmação aberta.
        				    */
        				    if (!clicked) {
            					window.setTimeout(function() {
            					    zebraDiscardActivityDialog = false;
            					}, 200);
        				    }
        				}
        		    });
        								
        		}, 300);
    	    }
    	    //DataLayer.rollback();
    	    return canDiscardActivityDialog;
    	},
    	dragStart: function(event, ui) {
	    if ($('.qtip.qtip-blue.qtip-active').length)
		$('.qtip.qtip-blue.qtip-active').qtip('destroy');
       }
    });
				
	} else {
	    UI.dialogs.addActivity.html(html);
	}
		
var tabs = UI.dialogs.addActivity.children('.content').tabs({
	select: function(event, ui) { 
		if ($('.qtip.qtip-blue.qtip-active').length)
			$('.qtip.qtip-blue.qtip-active').qtip('destroy');
	}	
	});

var group = DataLayer.get('calendar', objActivity.group);
				
if (group.timezone != objActivity.timezone){
    UI.dialogs.addActivity.find('.group_addactivity_details_lnk_timezone').find('option[value="'+objActivity.timezone+'"]').attr('selected','selected').trigger('change');
    UI.dialogs.addActivity.find('.group_addactivity_details_lnk_timezone').addClass('hidden');
    $('.group-addevent-details-txt-timezone').removeClass('hidden');
			
}

UI.dialogs.addActivity.find('.group_addactivity_details_lnk_timezone').click(function(e){
    $(this).addClass('hidden');
    $('.group-addactivity-details-txt-timezone').removeClass('hidden');
    e.preventDefault();
});

UI.dialogs.addActivity.find('.button.remove').button({
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

UI.dialogs.addActivity.find('.button-add-alarms').click(function(){
            DataLayer.render( 'templates/alarms_add_itemlist.ejs', {type: 2}, function( template ){
            jQuery('.activity-alarms-list').append(template)
            .find('.number').numeric().end()
            .find('.button.remove').button({
                text:false, 
                icons:{
                    primary:'ui-icon-close'
                }
            }).click(function(el) {
                $(this).parent().remove();
            });    
            });
        });

/*Seleciona a agenda padrão para visualização/edição de um evento*/
if(objActivity.id)
    UI.dialogs.addActivity.find('select[name="group"] option[value="'+objActivity.group+'"]').attr('selected','selected').trigger('change');

UI.dialogs.addActivity.find('.button').button();
    UI.dialogs.addActivity.find('.button.add').button({
        icons: {
    	   secondary: "ui-icon-plus"
        }
});

todoStatus('addActivity', (objActivity.activityStatus != undefined) ? objActivity.activityStatus : 1);

UI.dialogs.addActivity.find('select[name="activityStatus"]').attr('disabled', 'disabled');

// ==== validation events ====
UI.dialogs.addActivity.find(".input-group .h1").Watermark("_[[Untitled activity]]");

UI.dialogs.addActivity.find(".number").numeric();

User.preferences.dateFormat.indexOf('-') > 0 ? 
UI.dialogs.addActivity.find(".date").mask("99-99-9999", {
    completed:function(){
	updateMap();
    }
}) : 

UI.dialogs.addActivity.find(".date").mask("99/99/9999", {
    completed:function(){
	updateMap();
    }
});

UI.dialogs.addActivity.find(".menu-addactivity")
.children(".delete").click(function(){
    $.Zebra_Dialog('_[[The exclusion of this activity also has the option to delete your added tasks. What do you want to delete?]]', {
	'type':     'question',
    'width': '400',
	'overlay_opacity': '0.5',
	'buttons':  ['_[[Activity and Task]]', '_[[Only Activity]]', '_[[Cancel]]'],
	'onClose':  function(clicked) {
	    if(clicked == 'Apenas Atividade'){
            canDiscardActivityDialog = true;
            /* Remove por filtro */
            DataLayer.removeFilter('schedulable', {filter: ['AND', ['=', 'id', objActivity.id], ['=', 'calendar', objActivity.group], ['=','user',(objActivity.me.user ? objActivity.me.user.id : objActivity.me.id)], ['=', 'type', '2']],criteria:{type:2}});
            Calendar.rerenderView(true);
            /********************/
            UI.dialogs.addActivity.dialog("close");
	    }else if(clicked == 'Atividade e Tarefas'){
            canDiscardActivityDialog = true;
            DataLayer.removeFilter('schedulable', {filter: ['AND', ['=', 'id', objActivity.id], ['=', 'calendar', objActivity.group], ['=','user',(objActivity.me.user ? objActivity.me.user.id : objActivity.me.id)], ['=', 'type', '2']],criteria:{removeTaskToActivity: true, type:2}});
            Calendar.rerenderView(true);
            UI.dialogs.addActivity.dialog("close");
        }
	}
    });
}).end()
	    
.children(".cancel").click(function(){
    UI.dialogs.addActivity.dialog("close");
}).end()
	    
.children(".save").click(function(){
    /* Validação 
    var msg = false;			
    if(msg = validDateEvent()){
    	$(".new-activity-win.active").find('.messages-validation').removeClass('hidden').find('.message label').html(msg); 
    	return false;
    }
    */
	//Código o qual será chamado para o salvamento da Atividade. 
	var handler_save = function (objsToSave){ 
		UI.dialogs.addActivity.find('select[name="activityStatus"], input[name="allDay"]').removeAttr('disabled'); 
		canDiscardActivityDialog = true; 

		var activity_tst = DataLayer.form( UI.dialogs.addActivity.children().find('form') ); 
		if(objsToSave) 
			objsToSave[objsToSave.length] = activity_tst; 
		
		else  
			objsToSave = activity_tst; 
		DataLayer.put('schedulable', objsToSave); 
		 
		UI.dialogs.addActivity.dialog("close"); 
	} 

	//Verifica se o grupo da atividade mudou 
	if (group.id != UI.dialogs.addActivity.children().find('form select[name="group"] :selected').val() && typeof group.id != 'undefined') { 
		//Janela de dialogo 
		$.Zebra_Dialog('_[[You changed the group activity. Would you also change the group of tasks pertaining to this activity?]]', {
			'type':     'question', 
			'overlay_opacity': '0.5', 
			'modal': true, 
			'buttons':  ['_[[Do not Change]]', '_[[Change]]'],
			'onClose':  function(clicked) { 
				if(clicked == '_[[Do not Change]]') {
					handler_save(); 
				}else{ 
					var objsToSave = []; 
					//percorre as tarefas da atividade para verificar se o grupo mudou 
					for(var t in objActivity.tasks){ 
						if(typeof objActivity.tasks[t].group != 'undefined') 
							if (objActivity.tasks[t].group != UI.dialogs.addActivity.children().find('form select[name="group"] :selected').val()){ 
								objActivity.tasks[t].group = UI.dialogs.addActivity.children().find('form select[name="group"] :selected').val(); 
								objsToSave[objsToSave.length] = {'calendar' : objActivity.tasks[t].group, 'id': t}; 
							} 
					} 
					handler_save(objsToSave); 
					 
				} 
				 
			} 
		}); 
						 
	 
	} else { 
		handler_save(); 
	}

}).end()
		
.children(".export").click(function(){
    UI.dialogs.addActivity.children().find(".form-export").submit();
});

var task_activityHtml = DataLayer.render( path+'templates/task_activity_add.ejs', {	activity: objActivity});
var blkAddTask = UI.dialogs.addActivity.find('#group_addactivity_details8').append(task_activityHtml);

var dates = UI.dialogs.addActivity.find('input.date').datepicker({
    dateFormat: User.preferences.dateFormat.replace(/M/g, 'm').replace(/yyyy/g, 'yy'),
    onSelect : function( selectedDate ){
	updateMap();
    }
});

blkAddTask.find('.add-activity-search .ui-icon-search').click(function(event) {
    blkAddTask.find('.add-activity-search input').keydown();
});
			
			
blkAddTask.find('.add-activity-search input').keydown(function(event) {

    if(event.keyCode == '13' || typeof(event.keyCode) == 'undefined') {	
    // Fazer get das tarefas	
	var filter = 
        {
            filter: 
                ['AND',
                    ['=', 'type', '2'], 
                    ['>=','startTime', $.now()], 
                    ['in', 'calendar', [$('div.new-activity-win.active select[name="groupFilter"]').val()]]
                ], criteria: {filterTasks: true, deepness: 2}
        };
    if($(this).val() != ''){
        filter['filter'].push(['OR',
                            ["i*", "summary", $(this).val()], 
                            ["i*", "description", $(this).val()]
                           ]);
    }
    var result = DataLayer.get('schedulable:taskSearch', filter, true)
	/**
	* TODO: trocar por template
	*/
	blkAddTask.find('ul.search-result-list').empty().css('overflow', 'hidden');
	if (!result) {
	    blkAddTask.find('ul.search-result-list').append('<li><label class="empty">' + '_[[No results found.]]' + '</label></li>');
	}else{
    	for(i=0; i<result.length; i++)
    	    result[i].enabled = (blkAddTask.find('dd.task-activity-list ul.task-activity-list input[value="' +  result[i].id + '"]').length) ? false : true;
    											
    	blkAddTask.find('ul.search-result-list').append(DataLayer.render( path+'templates/task_search_itemlist.ejs', result));

        /* TODO - Verificar id da aitividade*/
    	blkAddTask.find('ul.search-result-list li').click(function(event, ui){
    	    if ($(event.target).is('input')) {
                old_item = $(event.target).parents('li');

                tasks[old_item.find('[name="id"]').val()] = {
                    startDate: old_item.find('[name="taskStartDate"]').val(),
                    startHour: old_item.find('[name="taskStartHour"]').val(),
                    endDate: old_item.find('[name="taskEndDate"]').val(),
                    endHour: old_item.find('[name="taskEndHour"]').val(),
                    dueDate: old_item.find('[name="taskDueDate"]').val(),
                    dueTime: old_item.find('[name="taskDueTime"]').val(),
                    allDay: old_item.find('[name="taskAllDay"]').val(),
                    percentage: old_item.find('[name="taskPercentage"]').val(),
                    status: old_item.find('[name="taskStatus"]').val()
                };

                tasks.length += 1;
        							
		blkAddTask.find('dd.task-activity-list ul.task-activity-list')
		    .append(DataLayer.render(path+'templates/task_add_itemlist.ejs', [{
				idTask: old_item.find('[name="id"]').val(),
				description: old_item.find('.description').html(),
				isWrite: false,
				summary: old_item.find('[name="taskSummary"]').val(),
				dueDate: old_item.find('[name="taskDueDate"]').val(),
				dueTime: old_item.find('[name="taskDueTime"]').val(),
				startDate: old_item.find('[name="taskStartDate"]').val(),
				startHour: old_item.find('[name="taskStartHour"]').val(),
				percentage: old_item.find('[name="taskPercentage"]').val(),
				priority: old_item.find('[name="taskPriority"]').val()
			}]))
        		.scrollTo('max');
		callbackTask(false, path);
                registerStatus();
                blkAddTask.find('.not-activity').addClass('hidden');
                old_item.remove();
    	    }
    	});
    }
    event.preventDefault();
    }
});

/*Carrega as tarefas já existentes em uma atividade*/
if(objActivity.tasks.length){
    tasks = objActivity.tasks;

    for(var i in objActivity.tasks){
        if(i != 'length')
            blkAddTask.find('dd.task-activity-list ul.task-activity-list')
                .append(DataLayer.render(path+'templates/task_add_itemlist.ejs', [{
                    idTask: i,
                    description: tasks[i].description,
                    idTaskToActivity: tasks[i].taskToActivity,
					isWrite: true,
					summary: tasks[i].summary,
					dueDate: tasks[i].dueDate,
					dueTime: tasks[i].dueTime,
					startDate: tasks[i].startDate,
					startHour: tasks[i].startHour,
					percentage: tasks[i].percentage,
					priority: tasks[i].priority
                 }]))
            .scrollTo('max');
    }

    callbackTask(false, path);
    registerStatus();
    blkAddTask.find('.not-activity').addClass('hidden');
}

UI.dialogs.addActivity.find('.row.fileupload-buttonbar-activity .button').filter('.delete').button({
    icons: {
	   primary: "ui-icon-close"
    },
    text: 'Excluir'
}).click(function () {
    $.Zebra_Dialog('_[[Are you sure you want to delete all attachments?]]', {
	'type':     'question',
	'overlay_opacity': '0.5',
	'buttons':  ['Não', 'Sim'],
	'onClose':  function(clicked) {
	    if(clicked == 'Sim'){
		
                var ids = [];
                $.each($('.attachment-list input'), function (i, input) {
                     DataLayer.remove('schedulableToAttachment', {
                        filter: ['=', 'id', ''+input.value]
                        });
                });
                $('div.new-activity-win .attachment-list input').remove();
                $('div.new-activity-win .row.fileupload-buttonbar-activity .attachments-list p').remove();
		$('div.new-activity-win .btn-danger.delete').addClass('hidden');
            }
        }});
}).end()
.filter('.close').button({
    icons: {
	   primary: "ui-icon-close"
    },
    text: false
}).click(function () {
    DataLayer.remove('schedulableToAttachment', $(this).parents('p').find('input[name="fileId[]"]').val());
    $(this).parents('p').remove();
}).end()
.filter('.downlaod-archive').button({
    icons: {
	   primary: "ui-icon-arrowthickstop-1-s"
    },
    text: false
});

extendsFileupload('activity', path);

UI.dialogs.addActivity.find(':input').change(function(event){
    if (event.keyCode != '27' && event.keyCode != '13')
	canDiscardActivityDialog = false;
}).keydown(function(event){
    if (event.keyCode != '27' && event.keyCode != '13')
	canDiscardActivityDialog = false;
});

UI.dialogs.addActivity.dialog('open');
}

function refreshTaskActivity(){
    updateActivity = false;

    var idTask = UI.dialogs.addActivity.find('input[name="idActivity"]').val();

    getSchedulable( idTask );

    var objActivity = DataLayer.get('schedulable:activity', idTask );
    var blkAddTask = UI.dialogs.addActivity.find('#group_addactivity_details8');
    var blkAuto = UI.dialogs.addActivity.find('#group_addactivity_details1');
    
   tasks = objActivity.tasks;
   blkAddTask.find('dd.task-activity-list ul.task-activity-list').html('');
   
   for(var i in tasks)
       DataLayer.remove('schedulable', i, false);

    for(var i in objActivity.tasks){
        if(i != 'length')
            blkAddTask.find('dd.task-activity-list ul.task-activity-list')
                .append(DataLayer.render('templates/task_add_itemlist.ejs', [{
                    idTask: i,
                    description: tasks[i].description,
                    idTaskToActivity: tasks[i].taskToActivity,
					isWrite: true,
					summary: tasks[i].summary,
					dueDate: tasks[i].dueDate,
					dueTime: tasks[i].dueTime,
					startDate: tasks[i].startDate,
					startHour: tasks[i].startHour,
					percentage: tasks[i].percentage,
					priority: tasks[i].priority
                 }]))
            .scrollTo('max');
    }

    callbackTask(blkAddTask);
    registerStatus(blkAuto);
}

function registerStatus(blkAuto){
    if(!blkAuto)
	blkAuto = UI.dialogs.addActivity.find('#group_addactivity_details1');

    var startDate = false;
    var endDate = new Date;
    var dueDate = new Date;
    var percentage = 0;		
	    var statusTasks = {1: 0, 2: 0, 3: 0, 4: 0};
	    var allDay = true;

    var formatString =  User.preferences.dateFormat + " " +  User.preferences.hourFormat;

    if(tasks.length == 0){

	startDate = new Date();
	var configData = (startDate.toString('mm') < 30)  ? {minutes: (30 - parseInt(startDate.toString('mm')))} : {hours: 1, minutes: '-'+startDate.toString('mm')};
	startDate.add(configData); 

	endDate = new Date(dateCalendar.decodeRange(startDate, (!!User.preferences.defaultCalendar ? (   !!Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration ?  (Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration) : (User.preferences.defaultDuration)) : (User.preferences.defaultDuration))));

	blkAuto.find('input[name="startDate"]').val(startDate.toString(User.preferences.dateFormat ));
	blkAuto.find('input[name="startHour"]').val(startDate.toString(User.preferences.hourFormat ));

	blkAuto.find('input[name="endDate"]').val(endDate.toString(User.preferences.dateFormat ));
	blkAuto.find('input[name="endHour"]').val(endDate.toString(User.preferences.hourFormat ));

	blkAuto.find('input[name="dueDate"]').val('');
	blkAuto.find('input[name="dueHour"]').val('');

	blkAuto.find('input[name="percentage"]').val(percentage);

	todoStatus('addActivity', 1);

	UI.dialogs.addActivity.find('select[name="activityStatus"] option[value="'+1+'"]').attr('selected','selected');

	blkAuto.find('input[name="allDay"]').attr('checked', false);

	return true;
    }

    for (var i in tasks){

    	if(i == 'length')
    	    continue;

    	statusTasks[tasks[i].status]++;

    	var stTime = Date.parseExact( tasks[i].startDate + " "+$.trim( tasks[i].startHour) , formatString);
    	var enTime = Date.parseExact( tasks[i].endDate + " "+$.trim( tasks[i].endHour), formatString );

    	startDate = (startDate && startDate.compareTo(stTime) == -1) ? startDate : stTime;
    	endDate = endDate.compareTo(enTime) == -1 ? enTime : endDate;

    	if(tasks[i].dueDate != ''){
    	    var pvTime = Date.parseExact( tasks[i].dueDate + " "+$.trim( tasks[i].dueTime), formatString );
    	    dueDate = dueDate.compareTo(pvTime) == -1 ? pvTime : dueDate;
    	}

        percentage += parseInt(tasks[i].percentage);
    }


    if((percentage > 0) && (tasks.length > 0))
        percentage = parseInt(percentage / tasks.length);
    
    var statusActivity = 1;

    if(statusTasks[4] != 0)
	statusActivity = 4;
    else if(statusTasks[2] != 0)
	statusActivity = 2;
    else if(statusTasks[3] == tasks.length)
	statusActivity = 3;
    else if(statusTasks[1] == tasks.length)
	statusActivity = 1;
    else
	statusActivity = 2;			

    todoStatus('addActivity', statusActivity);

    UI.dialogs.addActivity.find('select[name="activityStatus"] option[value="'+statusActivity+'"]').attr('selected','selected');

    dueDate = dueDate.compareTo(endDate) == -1 ? endDate : dueDate;

    blkAuto.find('input[name="startDate"]').val(startDate.toString(User.preferences.dateFormat ));
    blkAuto.find('input[name="startHour"]').val(startDate.toString(User.preferences.hourFormat ));

    blkAuto.find('input[name="endDate"]').val(endDate.toString(User.preferences.dateFormat ));
    blkAuto.find('input[name="endHour"]').val(endDate.toString(User.preferences.hourFormat ));

    blkAuto.find('input[name="dueDate"]').val(dueDate.toString(User.preferences.dateFormat ));
    blkAuto.find('input[name="dueHour"]').val(dueDate.toString(User.preferences.hourFormat ));

    blkAuto.find('input[name="percentage"]').val(percentage);

    blkAuto.find('input[name="allDay"]').attr('checked', (!!(startDate.compareTo(endDate) == 0) || (startDate.toString('HH:mm') == endDate.toString('HH:mm')) ? true : false));
}

function callbackTask(blkAddTask, path){

    path = path ? path : '';

	if(!blkAddTask)
	    blkAddTask = UI.dialogs.addActivity.find('#group_addactivity_details8');

        blkAddTask.find("li .button").filter(".close.new").button({
            icons: {
            primary: "ui-icon-close"
            },
            text: false
        }).click(function () {

            delete tasks[$(this).parents('li').find('input[name="idtask[]"]').val()]
            tasks.length +=  -1;

            idTaskToActivity = $(this).parents('li').find('input[name="idTaskToActivity[]"]').val()

            if(idTaskToActivity != '')
                DataLayer.remove('taskToActivity', idTaskToActivity);
            
            $(this).parents('li').remove();
                
            if(blkAddTask.find(".task-activity-list li").length == 1)
                blkAddTask.find("li.not-activity    ").removeClass('hidden');

            registerStatus();
        })
        .addClass('tiny disable ui-button-disabled ui-state-disabled')
        .removeClass('new').end()

        .filter(".info.new").button({
            icons: {
                primary:  "ui-icon-notice"
            },
            text: false
        }).click(function () {
            var positionY = $(this).parents("li").offset().top;
             
            var summary = $(this).parents('li').find("div").find(".summary").val();
			var startDate = $(this).parents('li').find("div").find(".startDate").val();
            var startHour = $(this).parents('li').find("div").find(".startHour").val();
			var dueDate = $(this).parents('li').find("div").find(".dueDate").val();
			var dueTime = $(this).parents('li').find("div").find(".dueTime").val();
			var percentage = $(this).parents('li').find("div").find(".percentage").val();
			var priority = $(this).parents('li').find("div").find(".priority").val();
            var email = $(this).parents('li').find("div").find(".mail").text()
                        
            if( $('.qtip.qtip-blue.qtip-active').val() !== ''){
                $(this).qtip({
                    show: {
                        ready: true,
                        solo: true,
                        when: {
                            event: 'click'
                        }
                    },
                    hide: false,
                    content: {
                        text: $('<div></div>').html( DataLayer.render( path + 'templates/activity_information_task.ejs', {
                            'summary' : summary,
							'startDate' : startDate,
							'startHour' : startHour,
							'dueDate' : dueDate,
							'dueTime' : dueTime,
							'percentage' : percentage,
							'priority' : priority
                        } ) ),
                        title: {
                            text:'_[[Information]]',
                            button: '<a class="button close" href="#">' + '_[[Close]]' + '</a>'
                        }
                    },
                style: {
                    name: 'blue',
                        tip: {
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
                        x: 30,
                        y: 0
                    }
                }
            })
            .qtip("api").onShow = function(arg0) {
                $('.qtip-active .button.close').button({
                    icons: {
                        primary: "ui-icon-close"
                    },
                    text: false
                })
                .click(function(){
                    blkAddTask.find('dd.task-activity-list').qtip('destroy');
                });
                                        
                $('.qtip-active .button.cancel').button().click(function(){
                    blkAddTask.find('dd.activity-list [type=checkbox]').attr('checked', false);
                    blkAddTask.find('dd.activity-list').qtip('destroy');
                });

                $('.qtip .button').button();
                                        
            };
         }             
     })
 .addClass('tiny disable ui-button-disabled ui-state-disabled')
 .removeClass('new').end()
 .filter(".edit.new").button({
            icons: {
            primary: "ui-icon-pencil"
            },
            text: false
        }).click(function () {

            var idTask = $(this).parents('li').find('input[name="idtask[]"]').val()
            getSchedulable(idTask);
            var task = DataLayer.get('schedulable:task', idTask);
            taskDetails(task, true, '',true, null, true);
        })
        .addClass('tiny disable ui-button-disabled ui-state-disabled')
        .removeClass('new').end()

        UI.dialogs.addActivity.find('.task-activity-list li').hover(
            function () {
                $(this).addClass("hover-attendee");
                $(this).find('.button').removeClass('disable ui-button-disabled ui-state-disabled').end()
                .find('.activity-options').addClass('hover-attendee');
            },
            function () {
                $(this).removeClass("hover-attendee");
                $(this).find('.button').addClass('disable ui-button-disabled ui-state-disabled').end()
                .find('.activity-options').removeClass('hover-attendee');
            }
        );        
    }
