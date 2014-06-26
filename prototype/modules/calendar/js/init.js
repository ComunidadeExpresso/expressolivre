$(document).ready(function() {
	$("#wrap").css("visibility","visible");
	//Remove o icone de configuraçõe padrão antigo do expresso
	$('#sideboxdragarea').addClass('hidden');

	refresh_calendars();
    $page = $('#tableDivAppbox');
	$tabs = $('#tabs').tabs({
        appendTo:function(event,ui){
            Calendar.lastView = $tabs.tabs('option', 'active');
			$('#tabs .events-list-win.active').removeClass('active');
            $tabs.tabs('option', 'active', '#' + ui.panel.id);
		},
		remove: function( event, ui ) {
            $tabs.tabs('option', 'active', Calendar.lastView);
		},
        beforeActivate: function(event, ui){

            if($('#tabs').tabs('option', 'active') == 1){
                delete Calendar.currentViewKey;
                $('#calendar').fullCalendar('refetchEvents');
            }
		}
    }).tabs('option', 'tabTemplate', "<li><a href='#{href}'>#{label}</a><span class='ui-icon ui-icon-close'>Remove Tab</span></li>" );

	/**
	  * Make a button to close the tab
	  */
    $page.on("click", "span.ui-icon-close", function() {

        var index = $('li', $tabs).index($(this).parent());

        if($tabs.tabs('option', 'active') == index){
            if($tabs.find('ul li').size() == 2 && Calendar.lastView != 1)
                $tabs.tabs('option', 'active', 0);
            $tabs.tabs('option', 'active', Calendar.lastView);
		}
        if($tabs.tabs('option', 'active') == 0 || $tabs.tabs('option', 'active') == 1)
            Calendar.lastView = $tabs.tabs('option', 'active');
        if(index != -1){
            var tab = $tabs.find(".ui-tabs-nav li:eq(" + index + ") a").attr('href');
            $tabs.find('div' + tab).remove();
            $tabs.find(".ui-tabs-nav li:eq(" + index + ")").remove();
        }
        $tabs.tabs("refresh");
    });
			
	$('.button.config-menu').button({
	    icons: {
		primary: "ui-icon-gear",
		secondary: "ui-icon-triangle-1-s"
	    },
	    text: false
	});
      $('.button.add').button({
	      icons: {
		      secondary: "ui-icon-plus"
	      }
    });

		var miniCalendar = $('.block-vertical-toolbox .mini-calendar').datepicker({
			dateFormat: 'yy-m-d',
			//dateFormat: 'DD, d MM, yy',
			//inline: true,
			firstDay: dateCalendar.dayOfWeek[User.preferences.weekStart],
		onSelect:function(dateText,inst){
            $tabs.tabs({active: 0});
				var toDate = $('.block-vertical-toolbox .mini-calendar').datepicker("getDate").toString('yyyy-MM-dd').split('-');
				$('#calendar').fullCalendar('gotoDate', toDate[0], parseInt(toDate[1]-1), toDate[2] );
				$('#calendar').fullCalendar( 'changeView', 'agendaDay' );
			}			
		})
		.find('.ui-icon-circle-triangle-e').removeClass('ui-icon-circle-triangle-e').addClass('ui-icon-triangle-1-e').end()
		.find('.ui-icon-circle-triangle-w').removeClass('ui-icon-circle-triangle-w').addClass('ui-icon-triangle-1-w');
		
		//Onclick do mês
    $page.on('click','.ui-datepicker-title .ui-datepicker-month',function(){
		$tabs.tabs("option","active", 0);
			$('#calendar').fullCalendar('gotoDate',$(this).siblings('span').html(), Date.getMonthNumberFromName($(this).html() == 'Março' ? 'Mar' : $(this).html()),'01');
 			$('#calendar').fullCalendar( 'changeView', 'month');
		});
		//Onclick do ano
    $page.on('click','.ui-datepicker-title .ui-datepicker-year',function(){
		$tabs.tabs("option","active", 0);
			$('#calendar').fullCalendar('gotoDate',$(this).html(), '0', '01');
			$('.fc-button-year').click();
		});
		
	//Onclick em um dia do calendário anual
	$page.on("click",".fc-day-number",function(){
			
		var date = $(this).parents('[class*="fc-day-"]').attr('class').match(/fc-day-(\d{4})-(\d{2})-(\d{2})/);

		if (date) date.shift();
		else return false;

		$('#calendar').fullCalendar('gotoDate',date[0],date[1]-1,date[2]);
		$('#calendar').fullCalendar( 'changeView', 'agendaDay' );
	});	

	$('.main-search input.search').keydown(function(event){
		if(event.keyCode == 13) {
			Encoder.EncodeType = "entity";
			//$(this).val($(this).val());
					
			add_events_list($(this).val());
			$(this).val('');
		}
	});
	
	//efetua pesquisas pelo click na lupa de pesquisa
	$('.main-search span.ui-icon-search').click(function(event){
			add_events_list($(this).parent().find('input.search').val());
			$(this).parent().find('input.search').val();
	});
	
	$('.block-horizontal-toolbox .main-config-menu').menu({
		content: $('.main-config-menu-content').html(),
		positionOpts: {
			posX: 'left', 
			posY: 'bottom',
			offsetX: -140,
			offsetY: 0,
			directionH: 'right',
			directionV: 'down', 
			detectH: true, // do horizontal collision detection  
			detectV: true, // do vertical collision detection
			linkToFront: false
		},
		flyOut: true,
		showSpeed: 100,
		crumbDefaultText: '>'
	});
	      
		$('#trash').droppable({
			drop: function(event, ui){
				// 		calendar.fullCalendar( 'removeEvents', ui.draggable.attr('event-id') );
				DataLayer.remove( "event", ui.draggable.attr('event-id') );
				$(this).switchClass('empty','full');
			},
			tolerance: "touch"
		});

      /* initialize the calendar
      -----------------------------------------------------------------*/
		$(".button.add.add-event").click(function(event){
			var startEvent = new Date();
			var configData = (startEvent.toString('mm') < 30)  ? {minutes: (30 - parseInt(startEvent.toString('mm')))} : {hours: 1, minutes: '-'+startEvent.toString('mm')};
			startEvent.add(configData); 

			eventDetails({ 
				startTime: startEvent.getTime(),
				endTime: dateCalendar.decodeRange(startEvent,
                (User.preferences.defaultCalendar ? (	Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration ?
						(Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration) : (User.preferences.defaultDuration)) : (User.preferences.defaultDuration)))
			}, true, undefined, undefined, undefined, true );
		});
		
		var currentToolTip = null;
		$page.on('scroll','#divAppbox',function(){
			if ($(".new-task").length)			
				currentToolTip.qtip('destroy');
		});
		
		/* Quick add task
      	-----------------------------------------------------------------*/
		$(".button.add.add-task").click(function(event){
			currentToolTip = $(this);
			var componente = $(this);
			
            if(!$('div.qtip.qtip-blue.new-task').length){

                $('div.qtip.qtip-blue').remove();

    			$(componente).qtip({
    			    show: {
    			    	ready: true, 
    		    		solo: true, 
    		    		when: {
    			    		event: 'click'
    			    	}
    				},
    				hide: false,
    				content: {
    					text: $('<div></div>').html( DataLayer.render( 'templates/task_quick_add.ejs', {"componente" : componente} ) ),
    					title: {
    						text:'_[[New Task]]',
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
    						min: 225, 
    					    max:225
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
    		    })
    	    	.qtip("api").onShow = function(arg0) {

	    		/*------------------------------------------------------------------------*/
	    		/*               Seta os valores padrões nos inputs do qtip               */
	    		 $('div.qtip div.add-simple-task input.task').Watermark("_[[Untitled task]]");
			     $('div.qtip div.add-simple-task textarea').Watermark("_[[Description]]");
    			/*------------------------------------------------------------------------*/

    			    $('.qtip-active .button.close').button({
    				icons: {
    				    primary: "ui-icon-close"
    				},
    				text: false
    			    })
    			    .click(function(){
    					$(componente).qtip('destroy');
    			    });
    							
    			    $('.qtip-active .button.save').button().click(function(){
    			    	
    			    	var title = $('div.qtip div.add-simple-task input.task').val();
    					var description = $('div.qtip div.add-simple-task textarea').val();

    					var calendar, timezone = '';

    					for (var i = 0; i < Calendar.signatures.length; i++){
    						if(Calendar.signatures[i].type == 1 && Calendar.signatures[i].calendar.type == 1){
    							calendar = Calendar.signatures[i].calendar.id;
    							timezone = Calendar.signatures[i].calendar.timezone;
    							break;
    						}
    					}

    			    	DataLayer.put('schedulable', 
    		    		{
    		    			summary: title, 
    		    			description: description, 
    		    			type: '2', 
    		    			calendar: calendar, 
    		    			timezone: timezone,
    		    			'class':'1',
							status: '1',
    		    			startTime: new Date().toString('yyyy-MM-dd 00:00:00'),
    		    			endTime: new Date().toString('yyyy-MM-dd 00:00:00'),
    		    			allDay: '1',
                            priority: '1',
					participants: 
					[{  
					    user: User.me.id, 
					    isOrganizer: 1,
					    acl: 'row'
					}]
    		    		});
    			    	
                        $(componente).qtip('destroy');

                    });

    			    $('.qtip-active .button.advanced').button().click(function(){

    			    	var startEvent = new Date();
    					var configData = (startEvent.toString('mm') < 30)  ? {minutes: (30 - parseInt(startEvent.toString('mm')))} : {hours: 1, minutes: '-'+startEvent.toString('mm')};
    					startEvent.add(configData);
    			
						var componente = $(this);
                        var description = $('div.qtip div.add-simple-task textarea[name="description"]').val();

    					taskDetails({
                            summary: $('div.qtip div.add-simple-task input[name="summary"]').val(),
                            description: description == 'Descrição' ? '' : description,
    						startTime: startEvent.getTime(),
    						endTime: dateCalendar.decodeRange(startEvent, (!!User.preferences.defaultCalendar ? (	!!Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration ?  
    							(Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration) : (User.preferences.defaultDuration)) : (User.preferences.defaultDuration)))
    					}, true );

                        $(componente).qtip('destroy');
    			    });
    								
    				$('.qtip-active .button.cancel').button().click(function(){
    					$(componente).qtip('destroy');
    			    });
    							
    			    $('.qtip-active .button').button();
    			
    			$('div.qtip.qtip-blue.qtip-active').addClass('new-task');
            }
	}
		});

	$(".button.add.add-activity").click(function(event){
			var startEvent = new Date();
			var configData = (startEvent.toString('mm') < 30)  ? {minutes: (30 - parseInt(startEvent.toString('mm')))} : {hours: 1, minutes: '-'+startEvent.toString('mm')};
			startEvent.add(configData); 
			
			activityDetails({ 
				startTime: startEvent.getTime(),
				endTime: dateCalendar.decodeRange(startEvent, (!!User.preferences.defaultCalendar ? (	!!Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration ?  
						(Calendar.signatureOf[User.preferences.defaultCalendar].calendar.defaultDuration) : (User.preferences.defaultDuration)) : (User.preferences.defaultDuration)))
			}, true );

	});

	var calendar = $('#calendar').fullCalendar(DataLayer.merge({ 

		defaultView: User.preferences.defaultCalView,
		timeFormat: User.preferences.hourFormat,
		axisFormat: User.preferences.hourFormat,
		eventSources: Calendar.sources,
		
		header: {
			left: 'prev,next today,agendaWeek,' + ((User.preferences.defaultCalView == "basicDay") ? "basicDay" : "agendaDay"),
			center: 'title',
			right:  ((User.preferences.defaultCalView == "basicDay") ? "basicDay" : "agendaDay") +',agendaWeek,month,year'
		},
		firstHour: dateCalendar.getShortestTime(User.preferences.defaultStartHour ? User.preferences.defaultStartHour : '6'),
		firstDay: dateCalendar.dayOfWeek[User.preferences.weekStart],
		editable: true,
		selectable: true,
		selectHelper: true,
		droppable: true, // this allows things to be dropped onto the calendar !!!
		timeFormat: {
			agenda: 'HH:mm{ - HH:mm}',
			'': 'HH:mm{ - HH:mm} }'
		},
		titleFormat: {
			month: 'MMMM yyyy',                             
			week: "dd[ yyyy] { 'a'[ MMM] dd 'de' MMMM '-' yyyy}", 
			day: 'dddd,  dd MMM , yyyy'
		},
		columnFormat:{
			month: 'ddd',    
			week: 'ddd dd/MM', 
			day: 'dddd dd/MM'  
		},
		
		allDayText: '_[[All day]]',
		buttonText: {
			today: '_[[Today]]',
			month: '_[[Month]]',
			week: '_[[Week]]',
			day: '_[[Day]]',
			year: '_[[Year]]'
		},

        eventRender: function( event, element, view ){
		    $('#calendar td.fc-year-have-event').removeClass('fc-year-have-event');

			var img_icon = "";
			var status_ball = ""; //nome da imagem a ser inserida
			var status_img = "";

            if( !!parseInt(event.unanswered) && event.type == 1 ){

                img_icon = "mini-attention.png";

            }else{

                if(event.type == 1)
                    img_icon = "mini-event.png";
                if(event.type == 2)
                    img_icon = "mini-task.png";
                if(event.type == 3)
                    img_icon = "mini-activity.png";

                //se for diferente de evento(type 1).
                if(event.type != 1){
                    if(event.status == "1"){
                        status_ball = "gray.png";
                    }else if(event.status == "2"){
                        status_ball = "yellow.png";
                    }else if(event.status == "3"){
                        status_ball = "green.png";
                    }else if(event.status == "4"){
                        status_ball = "red.png";
                    }

                    status_img = '<img style="width: 8px; height: 8px; margin-bottom: 2px;" src="../prototype/modules/calendar/img/' + status_ball + '"></img>';
                }

            }


			//html que exibe a imagem do type e do status
    	    element.find(".fc-event-inner.fc-event-skin").prepend($('<img style="width: 11px; height: 13px;" class="" src="../prototype/modules/calendar/img/' + img_icon + '"></img>' + status_img));
		},

		select: function( start, end, allDay, event, view ){
			if (view.name == "month") {
				if (User.preferences.defaultStartHour) {
				  _start = User.preferences.defaultStartHour;
				  
				  if (_start.length > 4) 
			        _start = _start.trim().substring(0,5); // remove o am/pm
			      
			      _start = _start.split(":");
			      start.setHours(_start[0]);
			      start.setMinutes(_start[1]);
			    }
				  
				if (User.preferences.defaultEndHour) {
				  _end = User.preferences.defaultEndHour;
				  if (_end.length > 4)
			        _end = _end.trim().substring(0,5); // remove o am/pm
			      			      
			      _end = _end.split(":");			   
			      end.setHours(_end[0]);			    
			      end.setMinutes(_end[1]);
				}		    
			} // END if (view.name == "month")
			
			eventDetails( { 'start': start,
					'end': end,
					'allDay': allDay } );
		},

		eventDrop: function( evt, event, view  ){
			evt.id = evt.id.split('-')[0];
            if(!evt.disableDragging){
                            
                if(evt.isRepeat){
                    var schedulable = copyAndMoveTo(evt.calendar , evt.id , false, "3", evt);
                                 
                    var repeat = mount_exception(evt.id, evt.occurrence);
                    DataLayer.remove('repeat', false);
                    DataLayer.put('repeat', repeat);
                    DataLayer.commit('repeat', false, function(data){
                                    
                        DataLayer.remove('schedulable', repeat.schedulable, false);
                        DataLayer.put('schedulable', schedulable);
                                     
                     });

                }else{
                    DataLayer.put( "schedulable:calendar", evt );

				    event.editable = false;
				    event.className = "blocked-event";
				    calendar.fullCalendar( 'updateEvent', evt );
                }
                             
			}else
                Calendar.rerenderView(true);
		},

		eventResize: function( evt, event, view ){
            evt.id = evt.id.split('-')[0];
			if(!evt.disableDragging){
                if(evt.isRepeat){
                    var schedulable = copyAndMoveTo(evt.calendar , evt.id , false, "3", evt);

                    //Normaliza a data para o backend
                    schedulable.startTime = new Date(parseInt(schedulable.startTime)).toString('yyyy-MM-dd hh:mm:00');
                    schedulable.endTime = new Date(parseInt(schedulable.endTime)).toString('yyyy-MM-dd hh:mm:00');

                    var repeat = mount_exception(evt.id, evt.occurrence);

                    DataLayer.remove('repeat', false);
                    DataLayer.put('repeat', repeat);
                    DataLayer.commit('repeat', false, function(data){

                        DataLayer.remove('schedulable', repeat.schedulable, false);
                        DataLayer.put('schedulable', schedulable);

                    });
                }else{

                    DataLayer.put( "schedulable:calendar", evt );
                    evt.editable = false;
                    evt.className = "blocked-event";
                    calendar.fullCalendar( 'updateEvent', evt );
                }
                        
			}else
				Calendar.rerenderView(true);
		},

		eventClick: function( evt, event, view ){
			evt.id = evt.id.split('-')[0];
            
            switch (parseInt(evt.type)){
                case 1:
                    if(evt.selectable){
                        if(evt.isRepeat && evt.editable ){
                            $.Zebra_Dialog(evt.title + ' _[[is a repeating event]]' + '.', {
                                'type':     'question',
                                'overlay_opacity': '0.5',
                                'custom_class':  'occurrence-zebra',
                                'width': 444,
                                'buttons':  ['_[[Edit all occurrences]]', '_[[Edit this event]]', '_[[Cancel]]'],
                                'onClose':  function(clicked) {
                                    if(clicked == '_[[Edit all occurrences]]') {
                                        var schedule = getSchedulable(evt.id, '');
                                        schedule.calendar = evt.calendar;
                                        eventDetails( schedule , true);

                                    }else if (clicked == '_[[Edit this event]]'){
                                        /*
                                        * TODO - repeat foi adicionado pois melhorias devem ser feitas no rollback do
                                        *DataLayer, repeat somente é usado quando se trata da criação de um evento
                                        *pela edição de uma ocorrência.
                                        */      
                                        var repeat = mount_exception(evt.id, evt.occurrence);

                                        $('.calendar-copy-move input[name="typeEvent"]').val("3");
                                        
                                        eventDetails(copyAndMoveTo(evt.calendar , evt.id , false, "3", evt), true, '', false, repeat);
                                    }       
                                }
                            });
                        }else{
                            var schedulable = getSchedulable(evt.id, '');
                            schedulable.calendar = evt.calendar;
                            eventDetails( schedulable, true);
                            }
                    }
                    break;
                case 2:
                    var task = getSchedulable(evt.id, '');
                    task.group = evt.calendar;
                    taskDetails( task, true);
                    break;
                case 3:
					DataLayer.remove('taskToActivity', false);//Limpa o cache
                    var activity = getSchedulable(evt.id, '');
                    activity.group = evt.calendar;
                    activityDetails( activity, true);
                    break;
            }
		},
		
		eventAfterRender: function(event, element, view){

			contentMenu();

		}
	}, dateCalendar));	

	$('#calendar .fc-header-left .fc-button-agendaWeek, #calendar .fc-header-left .fc-button-agendaDay').click(function(){
		$(this).parent().find(".fc-button-today").click();
	});
		
	contentMenu();
	 
	 $('body').click(function(){
		$('#context-menu-event').html('');
	 });


    if( $.browser.msie ){

        $('body').css('overflow-y','hidden');


    }else{

        if($(window).height() < $('body').height()){
            var hei = $('body').height() - $(window).height();
            hei = $('#divAppbox').height() - hei;
            $('#divAppbox').css('max-height', hei);
            $('#divAppbox').css('min-height', hei);
            $('body').css('overflow-y','hidden');
            delete hei;
        }

        $(window).resize(function(){
            $('#divAppbox').css('max-height', $(window).height() - 104);
            $('#divAppbox').css('min-height', $(window).height() - 104);
            $('#divAppbox').css('overflow-x', 'auto');
            $('#divAppbox').css('overflow-y', 'scroll');
        });
    }
	  


    if( $.browser.msie ){
        //$('#divAppbox').css('width', $(window).width());
    	$('#divAppbox').css({'height':'600px','max-width':'100%'});
    }


	//Todo chamada do metodo que adiciona ao full calendar o botao de listagem de eventos  
	printEvents();
});

function getSchedulable(id, codec){

    var schedule = DataLayer.get(('schedulable' + (codec != '' ? ':'+codec : '')), id, false );

    if(schedule == false)
        DataLayer.get(('schedulable' + (codec != '' ? ':'+codec : '')), {filter: ['id', '=', id], criteria: {deepness: 2, findOne: 1, schedulable: id}} );

    return DataLayer.get(('schedulable' + (codec != '' ? ':'+codec : '')), id.toString());
}

function useDesktopNotification(){

	return !!parseInt(User.preferences.useDesktopNotification);

}