function contentMenu(){
	$('.fullcalendar-not-context-menu').bind('contextmenu', function(event){
		event.preventDefault();
	});
	 
	$('.fullcalendar-context-menu').bind('contextmenu', function(event){
		event.preventDefault();
                
		var classes = $(this).attr('class').split(" ");
		var idEvent = false, typeEvent = false,idRecurrence = false, idCalendar = false;
                
		//recupera o id do calendar eo id do evento para tratamento
		for(var i = 0; i < classes.length; i++){
			if (classes[i].indexOf("event-id-") >= 0 ){
				idEvent = classes[i].replace(/[a-zA-Z-]+/g, '');
				continue;
			}else if (classes[i].indexOf("Recurrence-id-") >= 0 ){
				idRecurrence = classes[i].replace(/[a-zA-Z-]+/g, '');
				continue;
			}else if (classes[i].indexOf("calendar-id-") >= 0 ){
				idCalendar = classes[i].replace(/[a-zA-Z-]+/g, '');
				continue;
			}else if (classes[i].indexOf("event-type-") >= 0 ){
				typeEvent = classes[i].replace(/[a-zA-Z-]+/g, '');
				continue;
			}
		}

		var schedulable = DataLayer.get('schedulable', idEvent);
		var isRecurrence = DataLayer.get('repeat', schedulable.repeat).frequency || false;

		var top = $('#divAppbox').scrollTop();

		var template = DataLayer.render( 'templates/menu_context_event.ejs', 
		{
			event: schedulable.id ,
			top: (event.clientY - 135 + top), 
			left: (event.clientX - 445), 
		    signature: Calendar.signatureOf[idCalendar],
			calendars: Calendar[parseInt(typeEvent) == 1 ? 'calendars' : 'groups'], 
			isRecurrence: (!isRecurrence || isRecurrence == 'none') ? false : true,
			idRecurrence: idRecurrence,
			typeEvent: typeEvent
		});

		$('#context-menu-event').html(template);

		var method = function(value){
			switch (value){
				case "ocurrency":
					return '2'
				case "copy":
					return '1';
				case "move": 
					return '0';
			}
		}

		$('#context-menu-event').find('li.menu-item').hover(
			function () {
				$(this).addClass("li-hover").find('a').addClass('ui-state-hover');
				if($(this).hasClass('copy') || $(this).hasClass('move')) {
					$(this).parents().find('.calendar-copy-move input[name="typeEvent"]').val( method($(this).attr('class').split(" ")[0]));
					$(this).parents().find('.calendar-copy-move').show();
					if($(this).hasClass('move'))
						$('.calendar-list.calendar-already').hide();
					else
						$('.calendar-list.calendar-already').show();	
				}
			},
			function () {
				$(this).removeClass("li-hover").find('a').removeClass('ui-state-hover');
				if(!$(this).hasClass('copy') && !$(this).hasClass('move') )
					$(this).parents().find('.calendar-copy-move').hide()
			}
		);	
		
		$('#context-menu-event').find('li.calendar-list').hover(
			function () {
				$(this).addClass("li-hover").find('a').addClass('ui-state-hover');
			},
			function () {
				$(this).removeClass("li-hover").find('a').removeClass('ui-state-hover');
			}
		);	
	
		event.preventDefault();
	});
}