function getColorAttendee(keyColor){
	
	var colors = ['#3b7847', '#98993d' , '#130aff' , '#d95a0d', '#d9990d', '#8cd90d', '#0dd9b9', '#123456', '#000000', '#5f04c3', '#c3043a', '#faa26b', '#cee4d1',  '#adadad', '#707070'];
	
	return  colors[keyColor % 15] ;
	
}


function updateMap(viewName){
	var start_date = $(".new-"+viewName+"-win.active .start-date").val();
	var end_date   = $(".new-"+viewName+"-win.active .end-date").val();
	var start_time = $(".new-"+viewName+"-win.active .start-time").val();
	var end_time   = $(".new-"+viewName+"-win.active .end-time").val();
	var isAllDay   = $('.new-'+viewName+'-win.active input[name="allDay"]').is(':checked');
	
	var formatString = User.preferences.dateFormat + " " + User.preferences.hourFormat;
	
	var startDate = Date.parseExact(  start_date + " " + $.trim(start_time) , formatString );
	var endDate = Date.parseExact(  end_date + " " + $.trim(end_time) , formatString );
	
	
	var event = $('.new-'+viewName+'-win .map_disponibility').fullCalendar('clientEvents', 'map')[0];
	
	
		if(!!event){	
			event.start = startDate;
			event.end = endDate;
			event.allDay = isAllDay;
			$('.new-'+viewName+'-win .map_disponibility').fullCalendar( 'updateEvent', event )
		}
		
		
}


function disponibily(objEvent, path, attendees, viewName){
	var formatString = User.preferences.dateFormat + " " + User.preferences.hourFormat;
	var startDate = Date.parseExact( objEvent.startDate + " " + $.trim(objEvent.startHour) , formatString );
	var endDate = Date.parseExact( objEvent.endDate + " " + $.trim(objEvent.endHour) , formatString );

    var dialogName = {event: 'addEvent', task: 'addTask', activity: 'addActivity'}
	 
	var mapHtml = DataLayer.render( path+'templates/availability_map.ejs', {});
	$('#calendar_add'+viewName+'_details7').html(mapHtml);
	$('.new-'+viewName+'-win .map_disponibility').fullCalendar(DataLayer.merge({
		height : 300,
		timeFormat: User.preferences.hourFormat,
		axisFormat: User.preferences.hourFormat,
		firstDay: dateCalendar.dayOfWeek[User.preferences.weekStart],
		editable: false,
		selectable: true,
		selectHelper: false,
		droppable: false,
		allDayText: '_[[All day]]',
		buttonText: {
			today: '_[[today]]'
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
		select: function( start, end, allDay, event, view ){
		
			var event2 = $("#new_event_map").fullCalendar('clientEvents', 'map')[0];
			event2.start = start;
			event2.end = end;
			event2.allDay = allDay;
			$("#new_event_map").fullCalendar( 'updateEvent', event2 );
			
			$('[name="startDate"]').val(start.toString(User.preferences.dateFormat));
			$('[name="endDate"]').val(end.toString(User.preferences.dateFormat));
			if(!allDay){
				$('[name="allDay"]').attr("checked", false); 
				UI.dialogs[dialogName[viewName]].find('.start-time, .end-time').removeClass('hidden');
				
				$('[name="startHour"]').val(start.toString(User.preferences.hourFormat));
				if(end){
					$('[name="endHour"]').val(end.toString(User.preferences.hourFormat));
				}
			}else{
				$('[name="allDay"]').attr("checked", true); 
				UI.dialogs[dialogName[viewName]].find('.start-time, .end-time').addClass('hidden');
			}
		},
		events: [
			{
				id : 'map',
				title: '_[[Availability]]',
				start: startDate,
				end: endDate,
				editable : true,
				allDay : (objEvent.allDay ? true : false),
				className : "map-event",
				backgroundColor : "transparent !important",
				editable : objEvent.acl ? (objEvent.acl.write || objEvent.acl.organization) : true
			}
		],
		eventDrop : function(event,dayDelta,minuteDelta,allDay,revertFunc){
			if(event.allDay){
				$('[name="allDay"]').attr("checked", true); 
				UI.dialogs[dialogName[viewName]].find('.start-time, .end-time').addClass('hidden');
				
				$('[name="startDate"]').val(dateCalendar.toString(event.start, User.preferences.dateFormat));				
				$('[name="endDate"]').val(dateCalendar.toString((event.end ? event.end : event.start), User.preferences.dateFormat));

			}else{
				$('[name="allDay"]').attr("checked", false); 
				UI.dialogs[dialogName[viewName]].find('.start-time, .end-time').removeClass('hidden');
			
				$('[name="startHour"]').val(dateCalendar.formatDate(event.start, User.preferences.hourFormat));
				$('[name="startDate"]').val(dateCalendar.toString(event.start, User.preferences.dateFormat));
				
				$('[name="endHour"]').val(dateCalendar.formatDate((event.end ? event.end : new Date(dateCalendar.decodeRange(event.start, 120))), User.preferences.hourFormat));

				$('[name="endDate"]').val(dateCalendar.toString(event.end ? event.end : event.start , User.preferences.dateFormat));
			}
		},
		eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			if(event.end)
				$('[name="endHour"]').val(dateCalendar.formatDate(event.end, User.preferences.hourFormat));
			
			$('[name="endDate"]').val(dateCalendar.toString(event.end ? event.end : event.start , User.preferences.dateFormat));
				
		},
	defaultView : "agendaWeek"
	}, dateCalendar));
	var eventSource = new Array();
	var updateMapView = function(){
		updateMap(viewName);
		var view = $('.new-'+viewName+'-win .map_disponibility').fullCalendar('getView');
		var map = {};
		$('.new-'+viewName+'-win .map_disponibility').fullCalendar( 'removeEventSource', eventSource );
		eventSource = new Array();	
		map = {startTime : view.start.getTime(), endTime : view.end.getTime(), attendees: {}, timezone: (objEvent.timezone || User.preferences.timezone)};
		map.attendees[User.me.id] = {id : User.me.id, name : User.me.name};
		var count = 0;
		for (var idAttendee in attendees){
			if(attendees[idAttendee]){
				map.attendees[idAttendee] = {id : idAttendee, name : attendees[idAttendee], color: getColorAttendee(count)};
				count++;
			}
		}
		map_events = DataLayer.dispatch("mapDisponibility", map, false, false);
		for(var map_attende in map_events){
			for(var event_by_attende in map_events[map_attende]){
				var endTime = Timezone.getDateMapDisponibility(new Date(parseInt(map_events[map_attende][event_by_attende].endTime)));
				if(!!parseInt(map_events[map_attende][event_by_attende].allDay))
					endTime.add({day: -1});

				eventSource.push(
				{
					id : "map_busy_events", 
					title : map.attendees[map_attende].name,
					start: Timezone.getDateMapDisponibility(new Date(parseInt(map_events[map_attende][event_by_attende].startTime))),
					end: endTime,
					editable : false,
					allDay : !!parseInt(map_events[map_attende][event_by_attende].allDay),
					backgroundColor : map.attendees[map_attende].color + " !important",
					borderColor : map.attendees[map_attende].color + " !important"
				});
			}
		}
		if(eventSource.length)
			$('.new-'+viewName+'-win .map_disponibility').fullCalendar( 'addEventSource', eventSource );
	}; 
	$('[href="#calendar_add'+viewName+'_details7"]').click(function(eventData, eventObject){
		$('.new-'+viewName+'-win .map_disponibility').fullCalendar( 'gotoDate', Date.parseExact($('[name="startDate"]').val() + " " + $.trim($('[name="startHour"]').val()), formatString) , formatString);
		  updateMapView();
	});	
	$('.new-'+viewName+'-win .map_disponibility').find(".fc-button-prev, .fc-button-next, .fc-button-today").click(function(){
		updateMapView();
	});
}
