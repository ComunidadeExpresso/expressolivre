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

function validDateEvent(){
	
	var errors = {
		'emptyInitData': '_[[Please, enter a start date]]',
		'emptyEndData': '_[[Please, enter an end date]]',
		'emptyInitHour': '_[[Please, enter a start time]]',
		'emptyEndHour': '_[[Please, enter a final time]]',
		
		'invalidInitData' : '_[[Invalid start date]]',
		'invalidEndData' : '_[[Invalid end date]]',
		
		'equalData' : '_[[Start time equal to the end]]',
		'theirData' : '_[[End date less than the initial]]',		
		'theirHour' : '_[[Final hour less than the initial]]',
		
		'emptyOcurrence' : '_[[Please, enter the number of occurrences]]',
		'invalidOcurrence' : '_[[Please, enter a valid value for the number of occurrences]]',
		
		'emptyInterval' : '_[[Please, enter the interval]]',
		'invalidInterval' : '_[[Please, enter a valid value for the interval]]'
	};

    var start_date = $(".new-event-win.active .start-date").val();
    var end_date   = $(".new-event-win.active .end-date").val();
    var start_time = $(".new-event-win.active .start-time").val();
    var end_time   = $(".new-event-win.active .end-time").val();
    var isAllDay   = $('.new-event-win.active input[name="allDay"]').is(':checked');
    var customDate = $(".endRepeat").val() == "customDate";
    var occurrences = $(".endRepeat").val() == "occurrences";
    var eventInterval = $('.eventInterval').val();
    
    if(start_date == "")
		return errors['emptyInitData'];
    else if(end_date == "")
		return errors['emptyEndData'];
    else if(!isAllDay && start_time == "")
		return errors['emptyInitHour'];
    else if(!isAllDay && end_time == "")
		return errors['emptyEndHour'];
	
    var formatString = User.preferences.dateFormat + " " + User.preferences.hourFormat;
		
    var startDate = Date.parseExact( start_date + " " + $.trim(start_time) , formatString );
    var endDate = Date.parseExact( end_date + " " + $.trim(end_time) , formatString );

    if(startDate == null || startDate.getTime() < 0 )
		return errors['invalidInitData'];
    if(endDate == null || endDate.getTime() < 0)
		return errors['invalidEndData'];
	
	if(isAllDay){
		startDate.clearTime();
		endDate.clearTime();
		if(endDate.compareTo(startDate) == -1)
			return errors['theirData'];
	}else{
		var condition = endDate.compareTo(startDate);
		if(condition != 1){
			if(condition < 0){
				startDate.clearTime();
				endDate.clearTime();
				condition = endDate.compareTo(startDate);				
				return (errors[ condition == 0 ? 'theirHour' : 'theirData'] );
			}
			else
				return errors['equalData'];
		}
	}
    
    if (customDate)    
		if ( !($('.new-event-win.active .customDateEnd').val().length) )
		   return errors['emptyEndData'];

    if (occurrences){
		if ( !($('.occurrencesEnd').val().length) ) 
		   return errors['emptyOcurrence'];
		else if (parseInt($('.occurrencesEnd').val(),10) <= 0 || parseInt($('.occurrencesEnd').val(),10).toString() == "NaN")
		   return errors['invalidOcurrence'];
	}

    if (!($('.new-event-win.active p.input-group.finish_event.repeat-in').hasClass('hidden'))){
        if (!eventInterval.length)
            return errors['emptyInterval'];
        else if (parseInt(eventInterval,10) < 1 || parseInt(eventInterval,10).toString() == "NaN")
            return errors['invalidInterval'];
    }    
    return false;
}

/**
 * Renderiza relatórios dos Eventos.... etc.
 */
function printNow(){
	if($("#calendar").fullCalendar('getView').name == "agendaWeek" || $("#calendar").fullCalendar('getView').name == "basicWeek" || $("#calendar").fullCalendar('getView').name == "year")
		alert('_[[The printing view is best viewed with the preference "landscape" selected in your browser.]]');
		
	var window_print = window.open('','ExpressoCalendar','width=800,height=600,scrollbars=yes');        
	window_print.document.open();

	var start = $("#calendar").fullCalendar('getView').visStart.getTime()/1000;
	var end = $("#calendar").fullCalendar('getView').visEnd.getTime()/1000;
	var criteria = DataLayer.criteria("schedulable:calendar", {'start':start, 'end':end} );

    var data = DataLayer.encode('schedulable:print', DataLayer.dispatch('modules/calendar/schedules', criteria )  );

	if($("#calendar").fullCalendar('getView').name == "month"){				
		window_print.document.write(DataLayer.render('templates/calendar_month_print.ejs', {
			'InfoPage' : $("#calendar").fullCalendar('getView').title,
			'days' : data
		} ));
	}
	if($("#calendar").fullCalendar('getView').name == "agendaDay" || $("#calendar").fullCalendar('getView').name == "basicDay"){				
		window_print.document.write(DataLayer.render('templates/calendar_day_print.ejs', {
			'InfoPage' : $("#calendar").fullCalendar('getView').title,
			'days' : data
		} ));
	}
	if($("#calendar").fullCalendar('getView').name == "agendaWeek" || $("#calendar").fullCalendar('getView').name == "basicWeek"){
		window_print.document.write(DataLayer.render('templates/calendar_week_print.ejs', {
			'InfoPage' : $("#calendar").fullCalendar('getView').title,
			'days' : data
		}));
		
		var aux = 0;
        setTimeout(function () {
            $(window_print.document).find(".all-day").each(function () {
			if($(this).height() > aux)
				aux = $(this).height();
		});
		$(window_print.document).find(".all-day").each(function(){
			$(this).height(aux);
		});
		$(window_print.document).find(".all-day-line .write").height(aux);
		aux = 0;
		},20);
	}
	if($("#calendar").fullCalendar('getView').name == "year"){	
		window_print.document.write(DataLayer.render('templates/calendar_year_print.ejs', {
			'html' : $('#calendar .fc-content').html(),
			'header': $('#calendar').find('.fc-header-center h2').text()
		} ));
	}		
	window_print.document.close();
	window_print.print();
}

function printEvents(){
	//var html = DataLayer.render( path + 'templates/attendee_permissions.ejs', {} );
	var print = $('.fc-header-right').find('.fc-button.fc-button-year').clone();

	$('.fc-header-right').find('.fc-button-year').toggleClass('fc-corner-right');
	print.addClass('fc-corner-right');
	print.addClass('fc-button-print');
	print.removeClass('fc-button-year');
	print.removeClass('fc-corner-left');
	print.removeClass('fc-state-active');
	print.find('.fc-button-content').html('_[[Print]]');
	$('.fc-header-right').append(print);
	$('.fc-button-print').click(function(){
	    printNow();
	});
}

/**
 * TODO - repeat foi adicionado pois melhorias devem ser feitas no rollback do
 *DataLayer, repeat somente é usado quando se trata da criação de um evento
 *pela edição de uma ocorrência.
 **/

function eventDetails(objEvent, decoded, path, isMail, repeat, buttonClicked) {
    $('.qtip.qtip-blue').remove();

    attendees = {};

    if (!!objEvent.participants){
        $.each(objEvent.participants ,function(index, value) {

            var part = DataLayer.get('participant' , value );
            var user = DataLayer.get('user' , part.user );

            attendees[part.user] = user.name;
        });
    }

    if( path == undefined ) path = "";
	
    if (!decoded){
        objEvent = DataLayer.decode("schedulable:calendar", objEvent);
    }

	var dtstamp = objEvent.dtstamp;
    
    if (!isMail) {
        objEvent = DataLayer.encode("schedulable:preview", objEvent);
    }
	
	if (!dtstamp)
		var date = new Date();
	else
		var date = new Date(parseInt(dtstamp));
	
	objEvent.creationDate = [];
	objEvent.creationDate[0] = dateFormat(parseInt(dtstamp),'dd/mm/yyyy');
	objEvent.creationDate[1] = date.getHours();
	objEvent.creationDate[2] = date.getMinutes();	
    
    if (typeof(objEvent.id) == 'undefined') {
		objEvent.alarms = Calendar.signatureOf[User.preferences.defaultCalendar || Calendar.calendarIds[0] ].defaultAlarms || false;
		objEvent.useAlarmDefault = 1;
    }
	
    /**
	 * canDiscardEventDialog deve ser true se não houver alterações no evento
	 */
    canDiscardEventDialog = true;
    /**
	 * zebraDiscardEventDialog é uma flag indicando que uma janela de confirmação (Zebra_Dialog)
	 * já está aberta na tela, uma vez que não é possivel acessar o evento ESC utilizado para fechá-la
	 */
    zebraDiscardEventDialog = false;
	
    /**
		ACLs do participant
	*/
    acl_names = {
	'w': 'acl-white',
	'i': 'acl-invite-guests',
	'p': 'acl-participation-required'
    };

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
	if(other.lenght){
	    dependsDelegate(other.parents('li'), true);
	}
	blkAddAtendee.find('.delegate').removeClass('attendee-permissions-change-button');
	blkAddAtendee.find('.ui-icon-transferthick-e-w').removeClass('attendee-permissions-change');
	
    };

    var callbackAttendee = function(){
	//Cria qtip de permissões pelo click do checkbox
	var checked = false;
	blkAddAtendee.find("li.not-attendee").addClass('hidden');
	
	blkAddAtendee.find("li .button").filter(".close.new").button({
	    icons: {
		primary: "ui-icon-close"
	    },
	    text: false
	}).click(function () {
        var participant = DataLayer.get('participant' , $(this).parents('li').find('[type=checkbox]').val()); 
        DataLayer.remove('participant', participant.id);
        
	    if($(this).parent().find('.button.delegate').hasClass('attendee-permissions-change-button')){
		removeOthers();
		blkAddAtendee.find('.request-update').addClass('hidden');
		blkAddAtendee.find('.status option').toggleClass('hidden');
				
		blkAddAtendee.find('option[value=1]').attr('selected','selected').trigger('change');
	    }
			
	    $(this).parents('li').remove();
			
	    if(blkAddAtendee.find(".attendee-list li").length == 1)
		blkAddAtendee.find("li.not-attendee").removeClass('hidden');
     delete attendees[participant.user];
            }).addClass('tiny disable ui-button-disabled ui-state-disabled').removeClass('new').end().filter(".delegate.new").button({
	    icons: {
		primary: "ui-icon-transferthick-e-w"
	    },
	    text: false
	}).click(function () {
	    var me = $(this).parents('li');
	    if($(this).hasClass('attendee-permissions-change-button')){
                    $(this).removeClass('attendee-permissions-change-button').find('.ui-icon-transferthick-e-w').removeClass('attendee-permissions-change').end();
		
		me.find('input[name="delegatedFrom[]"]').val('');
		dependsDelegate(me, true);
				
		blkAddAtendee.find('.request-update').addClass('hidden');
		blkAddAtendee.find('.status option').toggleClass('hidden');

		blkAddAtendee.find('option[value=1]').attr('selected','selected').trigger('change');
				
	    }else{
		removeOthers();
			
                    $(this).addClass('attendee-permissions-change-button').find('.ui-icon-transferthick-e-w').addClass('attendee-permissions-change').end();
		
		me.find('input[name="delegatedFrom[]"]').val(blkAddAtendee.find('.me input[name="attendee[]"]').val());
		
		dependsDelegate(me, false);
			
		blkAddAtendee.find('.request-update').removeClass('hidden');
		if(blkAddAtendee.find('.status option.hidden').length == 1)
		    blkAddAtendee.find('.status option').toggleClass('hidden');
			
		blkAddAtendee.find('option[value=5]').attr('selected','selected').trigger('change');
	    }
            }).addClass('tiny disable ui-button-disabled ui-state-disabled').removeClass('new').end().filter(".edit.new").button({
	    icons: {
		primary: "ui-icon-key"
	    },
	    text: false
	}).click(function() {
			
	    if(!!!checked)
		$(this).parents('li').find('[type=checkbox]').attr('checked', (!$(this).parent().find('[type=checkbox]').is(':checked'))).end();
			
	    var aclsParticipant =  $(this).parents('li').find('input[name="attendeeAcl[]"]').val();
	    checked = false;
			
	    if( $('.qtip.qtip-blue.qtip-active').val() !== ''){
		blkAddAtendee.find('dd.attendee-list').qtip({
		    show: {
		    ready: true, 
	    solo: true, 
	    when: {
		    event: 'click'
		    }
		},
		hide: false,
		content: {
		text: $('<div></div>').html( DataLayer.render( path + 'templates/attendee_permissions.ejs', {} ) ), 
		title: {
		text:'_[[Permissions]]', 
		button: '<a class="button close" href="#">_[[Close]]</a>'
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
                    }).qtip("api").onShow = function (arg0) {
	    $('.qtip-active .button.close').button({
		icons: {
		    primary: "ui-icon-close"
		},
		text: false
                        }).click(function () {
		blkAddAtendee.find('dd.attendee-list').qtip('destroy');
	    });
					
	    $('.qtip-active .button.save').button().click(function(){
						
		var acl = '';
		$('.qtip-active').find('[type=checkbox]:checked').each(function(i, obj) {
		    acl+= obj.value;
		});

		blkAddAtendee.find('dd.attendee-list [type=checkbox]:checked').siblings('input[name="attendeeAcl[]"]').each(function(i, obj) { 
		    obj.value = 'r'+acl;
		}).parents('li').find('.button.edit').addClass('attendee-permissions-change-button')   
		.find('.ui-icon-key').addClass('attendee-permissions-change');               
						
		blkAddAtendee.find('dd.attendee-list [type=checkbox]').attr('checked', false);
						
		blkAddAtendee.find('dd.attendee-list').qtip('destroy');
					
	    });
	    $('.qtip-active .button.cancel').button().click(function(){
		blkAddAtendee.find('dd.attendee-list [type=checkbox]').attr('checked', false);
		blkAddAtendee.find('dd.attendee-list').qtip('destroy');
	    });
					
	    if(aclsParticipant)
		for(var i = 1; i < aclsParticipant.length; i++){
		    $('.qtip-active').find('input[name="'+acl_names[aclsParticipant.charAt(i)]+'"]').attr('checked', true);
		}
							
	    $('.qtip .button').button();
					
	};
	}else{
	    if(!$('.new-event-win dd.attendee-list').find('[type=checkbox]:checked').length){
		blkAddAtendee.find('dd.attendee-list').qtip('destroy');
	    }else{
		$('.qtip-active .button.save .ui-button-text').html('_[[Apply to All]]')
	    }
			
                };
            }).addClass('tiny disable ui-button-disabled ui-state-disabled').removeClass('new').end().filter(".open-delegate.new").click(function () {
    if($(this).hasClass('ui-icon-triangle-1-e')){
	$(this).removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
	$(this).parents('li').find('.list-delegates').removeClass('hidden');
    }else{
	$(this).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
	$(this).parents('li').find('.list-delegates').addClass('hidden');
    }
		
}).removeClass('new');
	
	
blkAddAtendee.find("li input[type=checkbox].new").click(function(){
    if(!$('.new-event-win dd.attendee-list').find('[type=checkbox]:checked').length){
	blkAddAtendee.find('dd.attendee-list').qtip('destroy');
    }else{
	checked = true;
	$(this).parents('li').find('.button.edit').click();
    }
}).removeClass('new');
	
UI.dialogs.addEvent.find('.attendees-list li').hover(
    function () {
	$(this).addClass("hover-attendee");
                $(this).find('.button').removeClass('disable ui-button-disabled ui-state-disabled').end().find('.attendee-options').addClass('hover-attendee');
    },
    function () {
	$(this).removeClass("hover-attendee");
                $(this).find('.button').addClass('disable ui-button-disabled ui-state-disabled').end().find('.attendee-options').removeClass('hover-attendee');
    }
    );
	
		
}

    var html = DataLayer.render(path + 'templates/event_add.ejs',{event: objEvent});
		
if (!UI.dialogs.addEvent) {
    UI.dialogs.addEvent = jQuery('#sandbox').append("<div title='_[[Create Event]]' class='new-event-win active'> <div>").find('.new-event-win.active').html(html).dialog({
	resizable: false, 
	modal:true, 
	autoOpen: false,
	width:"auto", 
	position: 'center', 
	close: function(event, ui) {
		/**
		 * Remove tooltip possivelmente existente
		 */
		if ($('.qtip.qtip-blue.qtip-active').length)
			$('.qtip.qtip-blue.qtip-active').qtip('destroy');						
		attendees  = {};
		DataLayer.rollback();//Limpa cache do datalayer
	},
	beforeClose: function(event, ui) {

	    if (!canDiscardEventDialog && !zebraDiscardEventDialog) {
		zebraDiscardEventDialog = true;
		window.setTimeout(function() {
		    $.Zebra_Dialog('_[[Your changes at the event were not saved. Do you want to discard the changes?]]', {
			'type':     'question',
			'overlay_opacity': '0.5',
			'buttons':  ['_[[Discard changes]]', '_[[Continue editing]]'],
			'onClose':  function(clicked) {
			    if(clicked == '_[[Discard changes]]') {
				canDiscardEventDialog = true;
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
			
				
                                                                                
				UI.dialogs.addEvent.dialog('close');
			    }else{
				zebraDiscardEventDialog = false;
			    }
									
			    /**
			    * Uma vez aberta uma janela de confirmação (Zebra_Dialog), ao fechá-la
			    * com ESC, para que o evento ESC não seja propagado para fechamento da
			    * janela de edição de eventos, deve ser setada uma flag indicando que
			    * já existe uma janela de confirmação aberta.
			    */
			    if (!clicked) {
				window.setTimeout(function() {
				    zebraDiscardEventDialog = false;
				}, 200);
			    }
			}
		    });
							
		}, 300);

	    }
	    //DataLayer.rollback();
	    return canDiscardEventDialog;
	},
	dragStart: function(event, ui) {
		if ($('.qtip.qtip-blue.qtip-active').length)
			$('.qtip.qtip-blue.qtip-active').qtip('destroy');
	}
    });
			
} else {
    UI.dialogs.addEvent.html(html);
}
		
var tabs = UI.dialogs.addEvent.children('.content').tabs({
	select: function(event, ui) { 
		if ($('.qtip.qtip-blue.qtip-active').length)
			$('.qtip.qtip-blue.qtip-active').qtip('destroy');
	}	
	});
var calendar = DataLayer.get('calendar', objEvent.calendar);
				
if ( (calendar.timezone != objEvent.timezone) && objEvent.id){
    UI.dialogs.addEvent.find('.calendar-addevent-details-txt-timezone').find('option[value="'+objEvent.timezone+'"]').attr('selected','selected').trigger('change');
    UI.dialogs.addEvent.find('.calendar_addevent_details_lnk_timezone').addClass('hidden');
    $('.calendar-addevent-details-txt-timezone').removeClass('hidden');
			
}


dateSameValue = function(){
    UI.dialogs.addEvent.find('input.start-date').datepicker({
        dateFormat: User.preferences.dateFormat.replace(/M/g, 'm').replace(/yyyy/g, 'yy'),
        onSelect : function (selectedDate){
            endDate = $(".end-date").val();
            storeSelectedDate = selectedDate;

            if ( User.preferences.dateFormat == "dd/MM/yyyy" || User.preferences.dateFormat == "dd-MM-yyyy" ) {
                if ( User.preferences.dateFormat == "dd/MM/yyyy" ) {
                    selectedDate = selectedDate.split("/");
                    endDate = endDate.split("/");
                } else if( User.preferences.dateFormat == "dd-MM-yyyy" ){
                    selectedDate = selectedDate.split("-");
                    endDate = endDate.split("-");
                }

                newDt = new Date(selectedDate[2],selectedDate[1], selectedDate[0]);
                endDate = new Date(endDate[2],endDate[1], endDate[0]);

            } else if ( User.preferences.dateFormat == "MM/dd/yyyy" ) {
                selectedDate = selectedDate.split("/");
                endDate = endDate.split("/");

                newDt = new Date(selectedDate[2],selectedDate[0], selectedDate[1]);
                endDate = new Date(endDate[2],endDate[0], endDate[1]);
            }

            if( newDt > endDate )
                $(".end-date").val(storeSelectedDate);
        },
        onClose: function(){
                UI.dialogs.addEvent.find(".end-date").val(UI.dialogs.addEvent.find(".start-date").val());
        }
    });
}

DataLayer.render( path+'templates/event_repeat.ejs', {
    event:objEvent
}, function( repeatHtml ){

    UI.dialogs.addEvent.find('#calendar_addevent_details3').html(repeatHtml);

    dateSameValue();
    $(".date").datepicker({
		dateFormat: User.preferences.dateFormat.replace(/M/g, 'm').replace(/yyyy/g, 'yy')
	});


    if(objEvent.repeat) 
    {
	if( objEvent.repeat['id'] )
	{
	    $("[name='repeatId']:last").val( objEvent.repeat['id'] );
	}

	if( objEvent.repeat['frequency'] !== 'none' )
	{
	    if( objEvent.repeat['startTime'] && objEvent.repeat['startTime'] !== "0" )
	    {
		$("[name='startOptions'] [value='customDate']:last").attr( 'selected', 'selected' );
		$("[name='start']:last").val(new Date( parseInt(objEvent.repeat['startTime']) ).toString( User.preferences.dateFormat ) );
	    }
	    else
	    {
		$("[name='start']:last").val($("[name='startDate']:last").val());     
		$("[name='start']:last").readOnly=true;
		$("[name='start']:last").datepicker("disable");
	    }
			      
	    $(".finish_event").removeClass("hidden");

	    if(objEvent.repeat['endTime'] && objEvent.repeat['endTime'] !== "0" ) 
	    {
		//$("[name='occurrences']").addClass("hidden");
		$(".customDateEnd").removeClass("hidden");
		$(".endRepeat option[value='customDate']").attr('selected', 'selected')						
		$(".customDateEnd").val( new Date( parseInt(objEvent.repeat['endTime']) )/*.setTimezoneOffset( Timezone.timezone( objEvent.timezone ) )*/.toString( User.preferences.dateFormat ) );  
	    }
	    else if (objEvent.repeat['count'] && objEvent.repeat['count'] !== "0" ) {
		$(".endRepeat option[value='occurrences']").attr('selected', 'selected');						
		$(".occurrencesEnd").removeClass("hidden");
		$(".occurrencesEnd").val(objEvent.repeat['count']);						
	    }
			      
	    switch ( objEvent.repeat['frequency'] )
	    {
		case "daily":
		    $(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Day]]' + '(s)')
		    .end().find(".eventInterval").val( objEvent.repeat['interval'] || "1" );
		    $(".frequency option[value='daily']").attr('selected', 'selected');
		    break;
		case "weekly":
		    $(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Week]]'+'(s)')
		    .end().find(".eventInterval").val( objEvent.repeat['interval'] || "1" );
		    
		    $(".frequency option[value='weekly']").attr('selected', 'selected');
					    
		    $(".event-repeat-weekly").removeClass("hidden");
					    
		    var day = [];
					    
		    if( objEvent.repeat.byday )
			day = objEvent.repeat.byday.split(',');
					    
		    for(i=0; i<day.length; i++) 
			$(".event-repeat-weekly [value='" + day[i] + "']").attr("checked","checked");
					    
		    break;
		case "monthly":
		    $(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Month]]' + '(s)')
		    .end().find(".eventInterval").val( objEvent.repeat['interval'] || "1" );
		    
		    $(".frequency option[value='monthly']").attr('selected', 'selected')
		    
		    $(".event-repeat-monthly:last").removeClass("hidden").find("input[type=radio][name=repeatmonthyType]").click(function(){
				if($("input[type=radio][name=repeatmonthyType]:checked").val() == "1")
				    $(".event-repeat-weekly:last").removeClass("hidden");
				else
				    $(".event-repeat-weekly:last").addClass("hidden");
		    });
		    
					    
			if( objEvent.repeat && objEvent.repeat.bymonthday != ''){

				$("input[type=radio][name=repeatmonthyType][value=0]").attr('checked', 'checked');

			}else if(objEvent.repeat){

				$("input[type=radio][name=repeatmonthyType][value=1]").attr('checked', 'checked');

				var days = objEvent.repeat.byday.split(',');

				$.each(days, function(i, e){
					$(".event-repeat-weekly:last").find('input[name="repeatweekly[]"][value="'+e+'"]').attr('checked', 'checked');
				});

			}


		    if($("input[type=radio][name=repeatmonthyType]:checked").val() == "1")
				$(".event-repeat-weekly:last").removeClass("hidden");
		    else
				$(".event-repeat-weekly:last").addClass("hidden");
		    break;
		case "yearly":
		    $(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Year]]' + '(s)')
		    .end().find(".eventInterval").val( objEvent.repeat['interval'] || "1" );
		    $(".frequency option[value='yearly']").attr('selected', 'selected')
		    break;	
	    }
	}
    }
    else {
	$(".endRepeat option[value='never']").attr('selected', 'selected');
    }


    $(".event-repeat-container:last").find(".repeat-in").find("[name=startOptions]").change(function(){                                       

	if($(this).find("option:selected").val() == "Today"){
	    $("[name='start']:last").val($("[name='startDate']:last").val());
	    $("[name='start']:last").readOnly=true;
	    $("[name='start']:last").datepicker("disable");
	}
	else{
	    $("[name='start']:last").readOnly=false;
	    $("[name='start']:last").datepicker("enable");
	}
    });
    $(".event-repeat-container:last").find(".repeat-in").find("[name=endOptions]").change(function(){                                       
	if($(this).find("option:selected").val() == "never"){
	    $("[name='occurrences']").addClass("hidden");
	    $("[name='end']:last").addClass("hidden");
	}
	else if($(this).find("option:selected").val() == "customDate"){
	    $("[name='occurrences']").addClass("hidden");
	    $("[name='end']:last").removeClass("hidden");    
	}
	else{
	    $("[name='end']:last").addClass("hidden");
	    $("[name='occurrences']").removeClass("hidden");                                        
	}
    });
                        
    $("[name='frequency']:last").change(function () {
	$(".frequency-option").addClass("hidden");
	if($(this).val() == "none"){
	    $(".repeat-in").addClass("hidden");
	    return;
	}else{
	    $(".repeat-in").removeClass("hidden");
	    $("[name='start']:last").val($("[name='startDate']:last").val());
	}
                 
				 
	switch($(this).val()){
	    case "daily":
		$(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Day]]' + '(s)');
		break;
	    case "weekly":
		$(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Week]]' + '(s)');
		$(".event-repeat-weekly:last").removeClass("hidden");
		break;
	    case "monthly":
		$(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Month]]' + '(s)');
		$(".event-repeat-monthly:last").removeClass("hidden").find("input[type=radio][name=repeatmonthyType]").click(function(){
		    if($("input[type=radio][name=repeatmonthyType]:checked").val() == "1")
			$(".event-repeat-weekly:last").removeClass("hidden");
		    else
			$(".event-repeat-weekly:last").addClass("hidden");
		});
		if($("input[type=radio][name=repeatmonthyType]:checked").val() == "1")
		    $(".event-repeat-weekly:last").removeClass("hidden");
		else
		    $(".event-repeat-weekly:last").addClass("hidden");
		break;
	    default:
		$(".event-repeat-container:last").find(".repeat-in").find(".interval").html('_[[Year]]' + '(s)');
		break;
	}
				
    });
});

UI.dialogs.addEvent.find('.calendar_addevent_details_lnk_timezone').click(function(e){
    $(this).addClass('hidden');
    $('.calendar-addevent-details-txt-timezone').removeClass('hidden');
    e.preventDefault();
});
		
UI.dialogs.addEvent.find('.button.remove').button({
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

var myCalendar = function(){
	for(var i in Calendar.signatures)
	    if(Calendar.signatures[i].isOwner == "1")
		return Calendar.signatures[i].calendar.id;
}

/*Seleciona a agenda padrão para visualização/edição de um evento*/
if(objEvent.id)
    UI.dialogs.addEvent.find('option[value="'+objEvent.calendar+'"]').attr('selected','selected').trigger('change');

/*Adicionar alarms padrões, quando alterado a agenda do usuário*/		
UI.dialogs.addEvent.find('select[name="calendar"]').change(function(){
    if((typeof($('input[name = "idEvent"]').val()) == 'undefined') || (!!!$('input[name = "idEvent"]').val())) {
	$('input[name = "isDefaultAlarm[]"]').parent().remove();
	UI.dialogs.addEvent.find('input[name="defaultAlarm"]').parent().removeClass('hidden');
	var calendarSelected = Calendar.signatureOf[$(this).val()];
	calendarSelected.useAlarmDefault = 1;
	if(calendarSelected.defaultAlarms != ""){
	    var li_attach = DataLayer.render(path+'templates/alarms_add_itemlist.ejs', {
		alarm:calendarSelected
	    });
	    jQuery('.event-alarms-list').append(li_attach).find('.button.remove').button({
		text:false, 
		icons:{
		    primary:'ui-icon-close'
		}
	    }).click(function(el) {
	    $(this).parent().remove().find('li').is(':empty');
	});

    }else{
	UI.dialogs.addEvent.find('input[name="defaultAlarm"]').parent().addClass('hidden');
    }
}

    var participant =  UI.dialogs.addEvent.find('dd.me input[name="attendee[]"]').val();
    var calendar = $(this).val();
    
    if( !parseInt(Calendar.signatureOf[calendar].isOwner) ){
	var signature = Calendar.signatureOf[calendar];
	var organizer = DataLayer.get('calendarSignature', {
	    filter: ['AND', ['=','calendar',signature.calendar.id], ['=','isOwner','1']], 
	    criteria: {
		deepness: 2
	    }
	});
			    
    if($.isArray(organizer))
	organizer = organizer[0];
    DataLayer.put('participant', {
	id: participant, 
	user: organizer.user.id, 
	mail: organizer.user.mail
	});
			    
    UI.dialogs.addEvent.find('dt.me').html(organizer.user.name);
    UI.dialogs.addEvent.find('li.organizer input[name="attendee_organizer"]').val(participant);
    UI.dialogs.addEvent.find('li.organizer label').filter('.name').html(organizer.user.name).end()
    .filter('.mail').html(organizer.user.mail).attr('title',organizer.user.mail);

}else{
    UI.dialogs.addEvent.find('dt.me').html(User.me.name);
    DataLayer.put('participant', {
	id: participant, 
	user: User.me.id, 
	mail: User.me.mail
	});
    UI.dialogs.addEvent.find('li.organizer input[name="attendee_organizer"]').val(participant);
    UI.dialogs.addEvent.find('li.organizer label').filter('.name').html(User.me.name).end()
    .filter('.mail').html(User.me.mail).attr('title',User.me.mail);
}

});

/*Checkbox adicionar alarms padrões*/
UI.dialogs.addEvent.find('input[name="defaultAlarm"]').click(function(){
    if($(this).attr("checked")){
	$('input[name="isDefaultAlarm[]"]').parent().remove();
	var calendarSelected = Calendar.signatureOf[$('select[name="calendar"]').val()];
	calendarSelected.useAlarmDefault = 1;
	if(calendarSelected.defaultAlarms != ""){
	    var li_attach = DataLayer.render(path+'templates/alarms_add_itemlist.ejs', {
		alarm:calendarSelected
	    });
	    jQuery('.event-alarms-list').append(li_attach).find('.button.remove').button({
		text:false, 
		icons:{
		    primary:'ui-icon-close'
		}
	    }).click(function(el) {
	    var id;
	    if( id = $(this).parent().find('input[name="alarmId[]"]').val())
		DataLayer.remove('alarm', id);
	    $(this).parent().remove().find('li').is(':empty') 
	});
    }
} else {
    $('input[name="isDefaultAlarm[]"]').parent().remove();
}
});
/* Checkbox allday */
UI.dialogs.addEvent.find('input[name="allDay"]').click(function(){
    $(this).attr("checked") ? 
    UI.dialogs.addEvent.find('.start-time, .end-time').addClass('hidden') :
    UI.dialogs.addEvent.find('.start-time, .end-time').removeClass('hidden'); 
    updateMap(true);
});

UI.dialogs.addEvent.find('.button').button();
UI.dialogs.addEvent.find('.button.add').button({
    icons: {
	secondary: "ui-icon-plus"
    }
});

// ==== validation events ====
UI.dialogs.addEvent.find(".input-group .h1").Watermark("_[[Untitiled event]]");
if(User.preferences.hourFormat.length == 5) {
    UI.dialogs.addEvent.find(".end-time, .start-time").mask("99:99", {
	completed: function(){
	    updateMap();
	}
    });
} else {
    $.mask.definitions['{']='[ap]';
    $.mask.definitions['}']='[m]';
    UI.dialogs.addEvent.find(".end-time, .start-time").mask("99:99 {}", {
	completed:function(){
	    $(this).val(date.Calendar.defaultToAmPm($(this).val()));
	    $(this).timepicker("refresh");
	    $(this).val($(this).val().replace(/[\.]/gi, ""));
	    updateMap();
	}
    });
}
UI.dialogs.addEvent.find(".number").numeric();
User.preferences.dateFormat.indexOf('-') > 0 ? 
UI.dialogs.addEvent.find(".date").mask("99-99-9999", {
    completed:function(){
	updateMap();
    }
}) : 
UI.dialogs.addEvent.find(".date").mask("99/99/9999", {
    completed:function(){
	updateMap();
    }
});

UI.dialogs.addEvent.find(".menu-addevent")
.children(".delete").click(function(){
    $.Zebra_Dialog('_[[Are you sure you want to delete this event?]]', {
	'type':     'question',
	'overlay_opacity': '0.5',
	'buttons':  ['_[[No]]', '_[[Yes]]'],
	'onClose':  function(clicked) {
	    if(clicked == '_[[Yes]]'){
		canDiscardEventDialog = true;
		/* Remove por filtro */
		DataLayer.removeFilter('schedulable', {filter: ['AND', ['=', 'id', objEvent.id], ['=', 'calendar', objEvent.calendar], ['=','user',(objEvent.me.user ? objEvent.me.user.id : objEvent.me.id)]]});
		Calendar.rerenderView(true);
		/********************/
		UI.dialogs.addEvent.dialog("close");
	    }
	}
    });
}).end()
	    
.children(".cancel").click(function(){
    UI.dialogs.addEvent.dialog("close");
}).end()
	    
.children(".save").click(function(){
    /* Validação */
    var msg = false;			
    if(msg = validDateEvent()){
	$(".new-event-win.active").find('.messages-validation').removeClass('hidden').find('.message label').html(msg); 
	return false;
    }
			
    canDiscardEventDialog = true;
			
    var exit = function(event){
	if(event)
	    DataLayer.remove('schedulable', event, false); 

	UI.dialogs.addEvent.children().find('form.form-addevent').submit();
	UI.dialogs.addEvent.dialog("close");
    }
                        
    if(repeat){
	DataLayer.remove('repeat', false);
	DataLayer.put('repeat', repeat);
	DataLayer.commit('repeat', false, exit(repeat.schedulable));
    }else
	exit();
}).end()
		
.children(".export").click(function(){
    UI.dialogs.addEvent.children().find(".form-export").submit();
});

dateSameValue();

var fixHour = function(){
    currentTimeStart = UI.dialogs.addEvent.find("input.start-time").val();
    UI.dialogs.addEvent.find("input.start-time").val(currentTimeStart.replace(".","").replace(".",""));
}   

var setTime = function( selectedDateTime ) {
    if ((selectedDateTime.value == '__:__') || (selectedDateTime.value == '__:__ __'))
          selectedDateTime.value = "";
    if(!(User.preferences.hourFormat.length == 5))
        $(this).val(selectedDateTime.replace(/[\.]/gi, ""));                                
    updateMap();

    if( Date.parse(selectedDateTime) < Date.parse(oldTime) ) return true;
    
    var time = selectedDateTime.split(":");

    var hh = time[0];
    var mm = time[1].substring(0, 2);
   
     
    dt = new Date();
    dt.setHours(hh, mm);
    var startHours = dt.getHours();
    add = parseInt(User.preferences.defaultDuration) + parseInt($("input.end-time").val().split(":")[1].substring(0,2));
    dt.addMinutes(add);
    

    var minutes = dt.getMinutes().toString();
    var hours = dt.getHours();
UI.dialogs.addEvent.find("input.start-time").val(selectedDateTime.replace(".","").replace(".",""));
    
    
    if (time[1].indexOf("p.m.") != -1 ) {
        var startHours = startHours == 12 ? 12 : startHours;
        var startHours = startHours == 1 ? 13 : startHours;
        var startHours = startHours == 2 ? 14 : startHours;
        var startHours = startHours == 3 ? 15 : startHours;
        var startHours = startHours == 4 ? 16 : startHours;
        var startHours = startHours == 5 ? 17 : startHours;
        var startHours = startHours == 6 ? 18 : startHours;
        var startHours = startHours == 7 ? 19 : startHours;
        var startHours = startHours == 8 ? 20 : startHours;
        var startHours = startHours == 9 ? 21 : startHours;
        var startHours = startHours == 10 ? 22 : startHours;
        var startHours = startHours == 11 ? 23 : startHours;
    } else if (time[1].indexOf("a.m.") ) {
        var startHours = startHours == 12 ? 00 : startHours;
    }


    dtFormat = (time[1].indexOf("p.m.") != -1 || time[1].indexOf("a.m.") != -1) ? ((((User.preferences.defaultDuration / 60) + startHours) >= 12 && (startHours + (User.preferences.defaultDuration / 60)) < 24) ? " pm" : " am") : "";
       
    var newHours = "";
    if(dtFormat){
        hours = hours == 13 ? 01 : hours;
        hours = hours == 14 ? 02 : hours;
        hours = hours == 15 ? 03 : hours;
        hours = hours == 16 ? 04 : hours;
        hours = hours == 17 ? 05 : hours;
        hours = hours == 18 ? 06 : hours;
        hours = hours == 19 ? 07 : hours;
        hours = hours == 20 ? 08 : hours;
        hours = hours == 21 ? 09 : hours;
        hours = hours == 22 ? 10 : hours;
        hours = hours == 23 ? 11 : hours;
        hours = hours == 24 ? 12 : hours;
    } else {
        newHours = hours <= 9 ? "0" : "";
    }

    minutes = minutes.length == 1 ? "0"+minutes+dtFormat : minutes+dtFormat;
    newHours +=  hours.toString() + ":" +minutes;
    UI.dialogs.addEvent.find("input.end-time").val(newHours);

}

    UI.dialogs.addEvent.find(".start-date").focusout(function(data){ 
        UI.dialogs.addEvent.find(".end-date").val($(this).val());
    });

    var oldTime = UI.dialogs.addEvent.find('input.start-time').val();
    $(".start-time").focusout(function(data){
        if($("#calendar").fullCalendar('getView').name == "month" || buttonClicked) {
            setTime( $(this).val() );
        }
    });

    UI.dialogs.addEvent.find('input.start-time').timepicker({
        closeText: 'Ok',
        hourGrid: 4,
        minuteGrid: 10,
        ampm : ((User.preferences.hourFormat.length > 5) ? true: false),
        timeFormat: "hh:mm tt",
        onSelect: function( selectedDateTime ){
            if($("#calendar").fullCalendar('getView').name == "month" || buttonClicked) {
                if ( selectedDateTime.indexOf("p.m.") == -1 || !selectedDateTime.indexOf("a.m.") == -1 ) {
                    var selectedTime = selectedDateTime.split(":");
                    var endTime = UI.dialogs.addEvent.find(".end-time").val();
                    endTime = endTime.split(":");

                    var t1 = new Date(false,false,false,selectedTime[0],selectedTime[1],false);
                    var t2 = new Date(false,false,false,endTime[0],endTime[1],false);

                    if (t1 < t2) return true;
                }

                setTime( selectedDateTime );
            }
        },
        onClose : function (selectedDateTime){
            fixHour();
        }

    });



UI.dialogs.addEvent.find('input.end-time').timepicker({ 
    closeText: 'Ok',
    hourGrid: 4,
    minuteGrid: 10,
    ampm : ((User.preferences.hourFormat.length > 5) ? true: false),
    timeFormat: "hh:mm tt",
    onSelect: function (selectedDateTime){
    	if ((selectedDateTime.value == '__:__') || (selectedDateTime.value == '__:__ __'))
			  selectedDateTime.value = "";
		if(!(User.preferences.hourFormat.length == 5))
	    	$(this).val(selectedDateTime.replace(/[\.]/gi, ""));								
		updateMap();

        if ( selectedDateTime.indexOf("p.m.") == -1 || !selectedDateTime.indexOf("a.m.") == -1 ) {
            var startT = UI.dialogs.addEvent.find('input.start-time');

            var lessZeroTime = function( sTime ) {
                sTime = sTime.split(":");
                sTime = new Date(false,false,false,sTime[0],sTime[1],false);
                sTimeReady = parseInt(sTime.getHours() - (User.preferences.defaultDuration / 60));

                if( sTimeReady < 0 )
                    return false; // If the value is less than 0, return false.
                    
                return sTimeReady.toString().length == 1 ? "0"+sTimeReady+":00" : sTimeReady+":00";
            }

            var hEnd = new Date(false,false,false,selectedDateTime.split(":")[0],selectedDateTime.split(":")[1],false);
            var hStart = new Date(false,false,false,startT.val().split(":")[0],startT.val().split(":")[1],false);
            
            if ( hEnd <= hStart ){
                var lessTime = lessZeroTime( selectedDateTime );
                if ( !lessTime ){
                    startT.val( "00:00" );
                    return true;
                }
                startT.val( lessTime );
            }
        }
    },
    onClose : function (selectedDateTime){
	if(!(User.preferences.hourFormat.length == 5))
	    $(this).val(selectedDateTime.replace(/[\.]/gi, ""));
    fixHour();
    },

    beforeShow: function (selectedDateTime) {
		if ((selectedDateTime.value == '__:__') || (selectedDateTime.value == '__:__ __'))
			selectedDateTime.value = "";
    }
});
//}

UI.dialogs.addEvent.find('.button-add-alarms').click(function(){
    var li_attach = DataLayer.render(path+'templates/alarms_add_itemlist.ejs', {});

    jQuery('.event-alarms-list').append(li_attach).find('.button.remove').button({
	text:false, 
	icons:{
	    primary:'ui-icon-close'
	}
    }).click(function(el) {
    $(this).parent().remove().find('li').is(':empty')
});
// valicacao de campos numericos
$('.number').numeric();
});
	    
		 
UI.dialogs.addEvent.find('.button.suggestion-hours').button({
    icons: {
	primary: "ui-icon-clock"
    },
    text: 'Sugerir horário'
}).click(function () {
    $(this).siblings('input').removeAttr('disabled')
    .end().parents().find('input[name="allDay"]').removeAttr('disabled');		
});


if(!repeat)
    if(objEvent.me.id == User.me.id){
	objEvent.me.id = DataLayer.put('participant', {
	    user: objEvent.me.id, 
	    mail: objEvent.me.mail
	    });
	objEvent.organizer.id = objEvent.me.id;
    }

var attendeeHtml = DataLayer.render( path+'templates/attendee_add.ejs', {
    event:objEvent
});		
	
// load template of attendees
var blkAddAtendee = UI.dialogs.addEvent.find('#calendar_addevent_details6').append(attendeeHtml);
if(objEvent.attendee.length) 
	 	callbackAttendee(); 
/**
Opções de delegação do participante/organizer
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
	    blkAddAtendee.find('.block-add-attendee.search').addClass('hidden');
	    blkAddAtendee.find('.block-add-attendee.search dt').html('_[[Add other contacts]]');
	}
    }else{									
	$(this).addClass('attendee-permissions-change-button')   
	.find('.ui-icon-transferthick-e-w').addClass('attendee-permissions-change').end();               
	blkAddAtendee.find('.block-add-attendee.search dt').html('_[[Delegate participation to]]');
	blkAddAtendee.find('.block-add-attendee.search').removeClass('hidden');
	blkAddAtendee.find('.block-add-attendee.search input.search').focus();
    }
})
.addClass('tiny');		
			
//show or hidden permissions attendees
//blkAddAtendee.find('.block-attendee-list #attendees-users li').click(show_permissions_attendees); 

UI.dialogs.addEvent.find(".attendee-list-add .add-attendee-input input").Watermark("_[[enter an email invite]]");
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
	    .find('.message label').html('_[[The above user has been added!]]' + ' <a class=\"small button\">' + '_[[Edit]]' + '</a>')
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
		    button: '<a class="button close" href="#">_[[Close]]</a>'
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
	user[0].acl = objEvent.acl;
	user[0].isDirty = !!!objEvent.id;
	user[0].isDelegate = (objEvent.id && (objEvent.me.status == '5'));

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
	    acl: objEvent.acl,
	    isDirty: !!!objEvent.id,
        isDelegate: (objEvent.id && (objEvent.me.status == '5'))
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
	button: '<a class="button close" href="#">_[[Close]]</a>'
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

/** 
* Trata a busca de usuários para adição de participantes
*/
blkAddAtendee.find('.add-attendee-search .ui-icon-search').click(function(event) {
    blkAddAtendee.find('.add-attendee-search input').keydown();
});
			
			
blkAddAtendee.find('.add-attendee-search input').keydown(function(event) {

    if(event.keyCode == '13' || typeof(event.keyCode) == 'undefined') {
	var result = DataLayer.get('user', { 'filter' :  ["*", "name", $(this).val()], criteria: { 'externalCatalogs' : true , 'personalContacts' : true }  }, true);

	/**
        * TODO: trocar por template
        */
	blkAddAtendee.find('ul.search-result-list').empty().css('overflow', 'hidden');
	if (!result) {
	    blkAddAtendee.find('ul.search-result-list').append('<li><label class="empty">_[[No results found.]]</label></li>');
	}

	for(i=0; i<result.length; i++)
	    result[i].enabled = (blkAddAtendee.find('dd.attendee-list ul.attendee-list label.mail[title="' +  result[i].mail + '"]').length) ? false : true;
											
	blkAddAtendee.find('ul.search-result-list').append(DataLayer.render( path+'templates/participants_search_itemlist.ejs', result));

	blkAddAtendee.find('ul.search-result-list li').click(function(event, ui){
	    if ($(event.target).is('input')) {
		old_item = $(event.target).parents('li');

        var  userId = old_item.find('.id').html();

        if(userId == '')
        {
           var  userId = DataLayer.put('user', {
                name: old_item.find('.name').html(),
                mail: old_item.find('.mail').html(),
                isExternal: '1'
            });
        }

		newAttendeeId = DataLayer.put('participant', {
		    user: userId,
		    isExternal: old_item.find('.isExternal').html()
		});
							
		attendees[old_item.find('.id').html()] = old_item.find('.name').html();
							
		blkAddAtendee.find('dd.attendee-list ul.attendee-list')
		.append(DataLayer.render(path+'templates/participants_add_itemlist.ejs', [{
		    id: newAttendeeId, 
		    name: old_item.find('.name').html(), 
		    mail: old_item.find('.mail').html(), 
		    isExternal: old_item.find('.isExternal').html(), 
		    acl: objEvent.acl,
		    isDirty: !!!objEvent.id,
            isDelegate: (objEvent.id && (objEvent.me.status == '5'))
		    }]))
		.scrollTo('max');
		/**
							* Delegação de participação de um participante com permissão apenas de leitura
							*
							*/
		if(!objEvent.acl.organization && !objEvent.acl.write && !objEvent.acl.inviteGuests && objEvent.acl.read ){
								
		    blkAddAtendee.find('.block-add-attendee.search').addClass('hidden');
		    blkAddAtendee.find('.block-add-attendee.search dt').html('_[[Add other contacts]]');
								
		    blkAddAtendee.find('.status option').toggleClass('hidden');
		    blkAddAtendee.find('option[value=5]').attr('selected','selected').trigger('change');
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
			blkAddAtendee.find('option[value=1]').attr('selected','selected').trigger('change');			
			blkAddAtendee.find('.me .participant-delegate').removeClass('disable ui-button-disabled ui-state-disabled attendee-permissions-change-button')
			.find('.ui-icon-person').removeClass('attendee-permissions-change').end();               	
									
			DataLayer.remove('participant', $(this).parents('li').find('[type=checkbox]').val());
			$(this).parents('li').remove();
		    })
		    .addClass('tiny');
		}else{
		    callbackAttendee();
		    old_item.remove();
		}
	    }
	});

	event.preventDefault();
    }
});

//$('.block-add-attendee .search-result-list').selectable();

UI.dialogs.addEvent.find('.row.fileupload-buttonbar .button').filter('.delete').button({
    icons: {
	primary: "ui-icon-close"
    },
    text: 'Excluir'
}).click(function () {
    $.Zebra_Dialog('_[[Are you sure you want to delete all attachments?]]', {
	'type':     'question',
	'overlay_opacity': '0.5',
	'buttons':  ['_[[No]]', '_[[Yes]]'],
	'onClose':  function(clicked) {
	    if(clicked == '_[[Yes]]'){
		
                var ids = [];
                $.each($('.attachment-list input'), function (i, input) {
                     DataLayer.remove('schedulableToAttachment', {
                        filter: ['=', 'id', ''+input.value]
                        });
                });
                $('div.new-event-win .attachment-list input').remove();
                $('div.new-event-win .row.fileupload-buttonbar .attachments-list p').remove();
		$('div.new-event-win .btn-danger.delete').addClass('hidden');
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

extendsFileupload('event', path);
	
if(objEvent.isShared){
		
    var acls = Calendar.signatureOf[objEvent.calendar].permission.acl;
		
    if(!acls.write){
	UI.dialogs.addEvent.find(':input').attr('disabled', 'disabled');
	UI.dialogs.addEvent.find('.button').hide();
    }
		
    if(acls.remove)
	UI.dialogs.addEvent.find('.button.remove').show();
    
    UI.dialogs.addEvent.find('.button.cancel').show();	
}

disponibily(objEvent, path, attendees, 'event');

/*Seleciona a agenda padrão para criação de um evento*/
if(!objEvent.id){
    var selectedCalendar = (objEvent.calendar != undefined) ? objEvent.calendar : (User.preferences.defaultCalendar ? User.preferences.defaultCalendar : myCalendar());
    UI.dialogs.addEvent.find('option[value="'+selectedCalendar+'"]').attr('selected','selected').trigger('change');
}
UI.dialogs.addEvent.find(':input').change(function(event){
    if (event.keyCode != '27' && event.keyCode != '13')
	canDiscardEventDialog = false;
}).keydown(function(event){
    if (event.keyCode != '27' && event.keyCode != '13')
	canDiscardEventDialog = false;
});	

UI.dialogs.addEvent.dialog('open');

}

/**
 * Classe para adicionar uma Nova Aba "tab"
 */
$newTab = (function(){
    return tab = {
        'createTab':function(tabId,tabTitle){
            var tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>",
                li = $( tabTemplate.replace( /#\{href\}/g, "#" + tabId ).replace( /#\{label\}/g, tabTitle ) );

            $tabs.find(".ui-tabs-nav").append(li);
            $tabs.append("<div id='"+tabId+"'></div>");
            $tabs.tabs("refresh");
            $tabs.tabs({active:-1});
        },
        'removeTab':function(){
            var panelId = $tabs.find('ul > .ui-state-active').remove().attr("aria-controls");
            $("#"+panelId).remove();
            $tabs.tabs("refresh");
        }
    };
}());

function add_tab_preferences(){
    var tab_title = "Preferências",tab_id = "preference_tab";
    if (!(document.getElementById('preference_tab'))){
        $newTab.createTab(tab_id,tab_title);
	DataLayer.render( 'templates/preferences_calendar.ejs', {
	    preferences:User.preferences, 
	    calendars: Calendar.calendars,
        signatureOf : Calendar.signatureOf
	    }, function( template ){
	    var tabPrefCalendar = jQuery('#preference_tab').html( template ).find('.preferences-win');
		
	    tabPrefCalendar.find('select[name="defaultCalendar"] option[value="'+User.preferences.defaultCalendar+'"]').attr('selected','selected').trigger('change'); 
        tabPrefCalendar.find('select[name="dafaultImportCalendar"] option[value="'+User.preferences.dafaultImportCalendar+'"]').attr('selected','selected').trigger('change'); 

	    DataLayer.render( 'templates/timezone_list.ejs', {}, function( timezones_options ){
		tabPrefCalendar.find('select[name="timezone"]').html(timezones_options).find('option[value="'+User.preferences.timezone+'"]').attr('selected','selected').trigger('change');
	    });
		
                tabPrefCalendar.find('.button').button().filter('.save').click(function (evt) {
		tabPrefCalendar.find('form').submit();
		$('#calendar').fullCalendar('render');
		$('.block-vertical-toolbox .mini-calendar').datepicker( "refresh" );
                    $newTab.removeTab();
		location.reload();
	    }).end().filter('.cancel').click(function(evt){
                    $newTab.removeTab();
	    });
			
	    tabPrefCalendar.find('.number').numeric();
			
	    tabPrefCalendar.find('input.time').timepicker({ 
		closeText: 'Ok',
		hourGrid: 4,
		minuteGrid: 10,
		ampm : (parseInt($("select[name=hourFormat] option:selected").val().length) > 5 ? true : false), //((User.preferences.hourFormat.length > 5) ? true: false),
		timeFormat: "hh:mm tt",
		onSelect: function (selectedDateTime){
		    if(!(User.preferences.hourFormat.length == 5)) {
			$(this).val(selectedDateTime.replace(/[\.]/gi, ""));
		    }
		},
		onClose : function (selectedDateTime){
		    if(!(User.preferences.hourFormat.length == 5)) {
			$(this).val(selectedDateTime.replace(/[\.]/gi, ""));
		    }
		}
	    });
			
	    $.mask.definitions['{']='[ap]';
	    $.mask.definitions['}']='[m]';
	    tabPrefCalendar.find("input.time").mask( ((User.preferences.hourFormat.length > 5) ? "99:99 {}" : "99:99"), {
		completed:function(){
		    $(this).val(dateCalendar.defaultToAmPm($(this).val()));
		    $(this).timepicker("refresh");
		    $(this).val($(this).val().replace(/[\.]/gi, ""));					
		}
	    });
			                                   
	    tabPrefCalendar.find("select[name=hourFormat]").change( function() { // evento ao selecionar formato de hora
         	
		tabPrefCalendar.find("input.time").timepicker("destroy");

		tabPrefCalendar.find('input.time').timepicker({ 
		    closeText: 'Ok',
		    hourGrid: 4,
		    minuteGrid: 10,
		    ampm : (parseInt($("select[name=hourFormat] option:selected").val().length) > 5 ? true : false),
		    timeFormat: "hh:mm tt",
		    onSelect: function (selectedDateTime){
			if(!(User.preferences.hourFormat.length == 5)) {
			    $(this).val(selectedDateTime.replace(/[\.]/gi, ""));
			}							
		    },
		    onClose : function (selectedDateTime){
			if(!(User.preferences.hourFormat.length == 5)) {
			    $(this).val(selectedDateTime.replace(/[\.]/gi, ""));
			}
		    }
		});
                             	
		var defaultStartHour = tabPrefCalendar.find("input[name=defaultStartHour]").val().trim();
		var defaultEndHour = tabPrefCalendar.find("input[name=defaultEndHour]").val().trim();
              	
		tabPrefCalendar.find("input.time").mask( (($("select[name=hourFormat] option:selected").val().trim().length > 5) ? "99:99 {}" : "99:99") );
                
		if (parseInt($("select[name=hourFormat] option:selected").val().length) > 5) { // am/pm
		    tabPrefCalendar.find("input[name=defaultStartHour]").val(dateCalendar.defaultToAmPm(defaultStartHour));
                        tabPrefCalendar.find("input[name=defaultEndHour]").val(dateCalendar.defaultToAmPm(defaultEndHour));
					
		} else { //24h
		    tabPrefCalendar.find("input[name=defaultStartHour]").val(dateCalendar.AmPmTo24(defaultStartHour));
		    tabPrefCalendar.find("input[name=defaultEndHour]").val(dateCalendar.AmPmTo24(defaultEndHour));
		}
	    });			
                        
                        
			
	});		
    } else {
            $tabs.tabs({active: $(".ui-tabs-nav li#"+tab_id).index()});
		
	return true;
    }
}

function add_tab_configure_calendar(calendar, type) {

    $('.qtip.qtip-blue').remove();

    var calendars = [];
    var signatures = [];
    var previewActiveCalendarConf = 0;
	var calendarAlarms = [];
	
	for (var i=0; i<Calendar.signatures.length; i++) {
		if(parseInt(Calendar.signatures[i].calendar.type) == type){
		   calendars.push(Calendar.signatures[i].calendar);
		   signatures.push(Calendar.signatures[i]);
		   length = signatures.length - 1;
		   signatures[length].numberDefaultAlarm = signatures[length].defaultAlarms != '' ?  signatures[length].defaultAlarms.length: 0;
		   if (calendar && calendars[length].id == calendar)
			   previewActiveCalendarConf = length;
		}
   }
	var tab_selector = ['configure_tab', 'configure_tab_group'];	
    if(!(document.getElementById(tab_selector[type])))
    {
	$('.positionHelper').css('display', 'none');
	$('.cal-list-options-btn').removeClass('fg-menu-open ui-state-active');
	if(type == 0){
		var tab_title = "_[[Agendas Settings]]";
	}else{
		var tab_title = "_[[Group Settings]]";
	}
        $newTab.createTab(tab_selector[type],tab_title);
		
	var dataColorPicker = {
	    colorsSuggestions: colors_suggestions()
	};
		
		
		
	var populateAccordionOnActive = function(event, ui) {
	    var nowActive = (typeof(event) == 'number') ? event : $(event.target).accordion( "option", "active" );
	    if (nowActive === false)
			return;
	    dataColorPicker.colorsDefined = {
		border: '#'+signatures[nowActive].borderColor, 
		font:'#'+signatures[nowActive].fontColor, 
		background:'#'+signatures[nowActive].backgroundColor
	    };
	    if (!jQuery('.accordion-user-calendars .ui-accordion-content').eq(nowActive).has('form')) {
		return true;
	    }

	    DataLayer.render( 'templates/configure_calendars_itemlist.ejs', {
		user:User, 
		type:0,
		calendar:calendars[nowActive], 
		signature:signatures[nowActive]
		}, function( form_template ){
		var form_content = jQuery('#'+tab_selector[type]+' .accordion-user-calendars .ui-accordion-content').eq(nowActive).html( form_template ).find('form');
		form_content.find('.preferences-alarms-list .button').button({
		    text:false, 
		    icons:{
			primary:'ui-icon-close'
		    }
		});
	    form_content.find('.button').button();
	    jQuery('.preferences-alarms-list').find('.button.remove').click(function(el){
			calendarAlarms[calendarAlarms.length] = $(this).parent('li').find('input[name="alarmId[]"]').val();
			$(this).parent().remove();
		});
	
		DataLayer.render( 'templates/timezone_list.ejs', {}, function( timezones_options ){
		    var valueTimeZone = calendars[nowActive].timezone;
		    form_content.find('select[name="timezone"]').html(timezones_options).find('option[value="'+valueTimeZone+'"]').attr('selected','selected').trigger('change');
		});

		form_content.find('.button-add-alarms').click(function(){
		    DataLayer.render( 'templates/alarms_add_itemlist.ejs', {type: (parseInt(type) == 1 ? '4' : type) }, function( template ){
            form_content.find('.preferences-alarms-list').append(template)
			.find('li:last label:eq(0)').remove().end()
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


	    /**
				 * Set color picker
				 */
	    DataLayer.render( 'templates/calendar_colorpicker.ejs', dataColorPicker, function( template ){
		form_content.find('.calendar-colorpicker').html( template );

		var f = $.farbtastic(form_content.find('.colorpicker'), colorpickerPreviewChange);
		var selected;
		var colorpicker = form_content.find('.calendar-colorpicker');
					
		var colorpickerPreviewChange = function(color) {
		    var pickedup = form_content.find('.colorwell-selected').val(color).css('background-color', color);

		    var colorpicker = form_content.find('.calendar-colorpicker');

		    if (pickedup.is('input[name="backgroundColor"]')) {
			colorpicker.find('.fc-event-skin').css('background-color',color);
		    } else if (pickedup.is('input[name="fontColor"]')) {
			colorpicker.find('.fc-event-skin').css('color',color);
		    } else if (pickedup.is('input[name="borderColor"]')) {
			colorpicker.find('.fc-event-skin').css('border-color',color);
		    }
		} 
					
		form_content.find('.colorwell').each(function () {
		    f.linkTo(this);

		    if ($(this).is('input[name="backgroundColor"]')) {
			colorpicker.find('.fc-event-skin').css('background-color', $(this).val());
		    } else if ($(this).is('input[name="fontColor"]')) {
			colorpicker.find('.fc-event-skin').css('color', $(this).val());
		    } else if ($(this).is('input[name="borderColor"]')) {
			colorpicker.find('.fc-event-skin').css('border-color', $(this).val());
		    }
		})
		.focus(function() {
		    if (selected) {
			$(selected).removeClass('colorwell-selected');
		    }

		    $(selected = this).addClass('colorwell-selected');
		    f.linkTo(this, colorpickerPreviewChange);
		    f.linkTo(colorpickerPreviewChange);

		});

		form_content.find('select.color-suggestions').change(function() {
		    var colors;

		    if(colors = dataColorPicker.colorsSuggestions[$(this).val()]) {	
			colorpicker
			.find('input[name="fontColor"]').val(colors.font).focus().end()	
			.find('input[name="backgroundColor"]').val(colors.background).focus().end()
			.find('input[name="borderColor"]').val(colors.border).focus().end()

			.find('.fc-event-skin').css({
			    'background-color':dataColorPicker.colorsSuggestions[$(this).val()].background,
			    'border-color':dataColorPicker.colorsSuggestions[$(this).val()].border,
			    'color':dataColorPicker.colorsSuggestions[$(this).val()].font 
			});
		    }
		});

		/**
					 * Trata a mudança dos valores dos campos de cores.
					 * Se mudar um conjunto de cores sugerido,
					 * este vira um conjunto de cores personalizado.
					 */
		form_content.find('.colorwell').change(function (element, ui) {
		    if (true) {
			form_content.find('select.color-suggestions')
			.find('option:selected').removeAttr('selected').end()
			.find('option[value="custom"]').attr('selected', 'selected').trigger('change');
		    }
		});
	    });	//END set colorpicker

	    form_content.find('.phone').mask("+99 (99) 9999-9999");
	    form_content.find('.number').numeric();

	}); //END DataLayer.render( 'templates/configure_calendars_itemlist.ejs' ...

// === validations preferences ==== 

			
} //END populateAccordionOnActive(event, ui)
		

DataLayer.render( 'templates/configure_calendars.ejs', {
    user:User, 
	type: type,
    calendars:calendars, 
    signatures:signatures
}, function( template ){
    var template_content = jQuery('#'+tab_selector[type]).html( template ).find('.configure-calendars-win');
    template_content.find('.button').button().filter('.save').click(function(evt){
	if(calendarAlarms.length)
		DataLayer.removeFilter('calendarSignatureAlarm', {filter: ['IN','id', calendarAlarms]});	
	template_content.find('form').submit();
                $newTab.removeTab();
	DataLayer.commit( false, false, function( received ){
	    delete Calendar.currentViewKey;
	    Calendar.load();
	    refresh_calendars();
	});
	if(calendarAlarms.length)
		Calendar.load();
    }).end().filter('.cancel').click(function(evt){
                $newTab.removeTab();
    });

    /**
			 * Muda a estrutura do template para a aplicação do plugin accordion
			 */
    template_content.find('.header-menu-container').after('<div class="accordion-user-calendars"></div>').end().find('.accordion-user-calendars')
    .append(template_content.children('fieldset'));
			
    template_content.find('.accordion-user-calendars').children('fieldset').each(function(index) {
	$(this).before($('<h3></h3>').html($(this).children('legend')));
    });
			
    template_content.find('.accordion-user-calendars').accordion({ 
                heightStyle: "content",
	collapsible: true, 
	clearStyle: true,
	active: previewActiveCalendarConf, 
                beforeActivate:populateAccordionOnActive
    });
    populateAccordionOnActive(previewActiveCalendarConf);
});

} else {
	$('.positionHelper').css('display','none');
    $('.cal-list-options-btn').removeClass('fg-menu-open ui-state-active');
        $tabs.tabs("option","select","#"+tab_selector[type]);
        $('.accordion-user-calendars').accordion({activate: previewActiveCalendarConf});
		
    return true;
}

}

function getSelectedCalendars( reverse, type ){

    var selector = "";

    switch(type)
    {
        case 0:
            selector = "div.my-calendars .calendar-view, div.signed-calendars .calendar-view";
            break;
        case 1:
            selector = "div.my-groups-task .calendar-view";
            break;
        case 2:
            selector = ".calendar-view";
            break;
    }

//  var selector = !!type ? "div.my-groups-task .calendar-view" : "div.my-calendars .calendar-view, div.signed-calendars .calendar-view";
    var returns = [];

    $.each( $(selector), function(i , c){

        if( reverse ? !c.checked : c.checked )
            returns.push( c.value );

    });

    if (!returns.length)
	    return false;

    return returns;
}

/**
 * TODO - transformar em preferência do módulo e criar telas de adição e exclusão de conjunto de cores
 */
function colors_suggestions(){
    return [
    {
	name:'_[[Default]]', 
	border:'#3366cc', 
	font:'#ffffff', 
	background:'#3366cc'
    },

    {
	name:'_[[Koala]]', 
	border:'#123456', 
	font:'#ffffff', 
	background:'#385c80'
    },

    {
	name:'_[[Tomato]]', 
	border:'#d5130b', 
	font:'#111111', 
	background:'#e36d76'
    },

    {
	name:'_[[Lemon]]', 
	border:'#32ed21', 
	font:'#1f3f1c', 
	background:'#b2f1ac'
    },

    {
	name:'_[[High contrast]]', 
	border:'#000000', 
	font:'#ffffff', 
	background:'#222222'
    }
    ]		
}

function remove_event(eventId, idCalendar, type){
    $.Zebra_Dialog('_[[Are you sure you want to delete?]]', {
	'type':     'question',
	'overlay_opacity': '0.5',
	'buttons':  ['_[[No]]', '_[[Yes]]'],
	'onClose':  function(clicked) {
	    if(clicked == '_[[Yes]]'){

		var schedulable = getSchedulable( eventId, '');
		schedulable.calendar = ''+idCalendar;
		var schudableDecode = DataLayer.encode( "schedulable:preview", schedulable);
		var me = schudableDecode.me.user ? schudableDecode.me.user.id : schudableDecode.me.id;
        var filter = {filter: ['AND', ['=','id',eventId], ['=','calendar',idCalendar], ['=','user', me] ] };
        if(type)
            filter.filter.push(['=','type',type]);
		DataLayer.removeFilter('schedulable', filter);
		Calendar.rerenderView(true);
	    }
	}
    });	
}

function mount_exception(eventID, exception){

    getSchedulable( eventID.toString() , '');
    var schedulable = DataLayer.get('schedulable', eventID.toString());
    var edit = { repeat: (DataLayer.get('repeat', schedulable.repeat)) };

    edit.repeat.startTime = new Date(parseInt(edit.repeat.startTime)).toString('yyyy-MM-dd HH:mm:00');
    edit.repeat.endTime = parseInt(edit.repeat.count) > 0 ? '0' : new Date(parseInt(edit.repeat.endTime)).toString('yyyy-MM-dd HH:mm:00');
    
    edit.repeat.exceptions = ( exception );
    
    return edit.repeat;
}

function remove_ocurrence(eventId, idRecurrence){
    $.Zebra_Dialog('_[[Are you sure you want to delete this ocurrence?]]', {
	'type':     'question',
	'overlay_opacity': '0.5',
	'buttons':  ['_[[No]]', '_[[Yes]]'],
	'onClose':  function(clicked) {
	    if(clicked == '_[[Yes]]'){
		var repeat = mount_exception(eventId, idRecurrence);
		DataLayer.remove('repeat', false);
		DataLayer.put('repeat', repeat);
		DataLayer.commit(false, false, function(data){
		    Calendar.rerenderView(true);
		});
	    }
	}
    });	
}


function remove_calendar(type){
    /* Pode ser assim $('.cal-list-options-btn.ui-state-active').attr('class').replace(/[a-zA-Z-]+/g, ''); */
	if(!!parseInt(type))
		var title = '_[[All tasks of this group will be removed. Proceed with this operation?]]';
	else
		var title = '_[[All events of this calendar will be removed. Proceed with the operation?]]';
    $.Zebra_Dialog(title, {
	'type':     'question',
	'overlay_opacity': '0.5',
	'buttons':  ['_[[No]]', '_[[Yes]]'],
	'onClose':  function(clicked) {
	    if(clicked == '_[[Yes]]'){
		var idCalendar =  $('.cal-list-options-btn.ui-state-active').attr('class').match(/[0-9]+/g);
				
		DataLayer.remove('calendarSignature', Calendar.signatureOf[idCalendar[0]].id );
				
		if(idCalendar == User.preferences.defaultCalendar)
		    DataLayer.remove( 'modulePreference', User.preferenceIds['defaultCalendar']);
			
		DataLayer.commit( false, false, function( received ){
		    delete Calendar.currentViewKey;
		    Calendar.load();
		    refresh_calendars(type);
		});
	    }
	    $('.positionHelper').css('display', 'none');
	
	}
    });	
}

function refresh_calendars(type){

    var colorsSuggestions = colors_suggestions();
    var buttons_colors = "";
    for(var i = 0; i < colorsSuggestions.length; i++){
	buttons_colors += "<a class=\"cal-colors-options-btn ui-icon ui-button-icon-primary signed-cal-colors-options-btn-"+i+"\"  style=\"background-color:"+colorsSuggestions[i]['background']+"; border-color:"+colorsSuggestions[i]['border']+"; color:"+colorsSuggestions[i]['font']+"\">&bull;</a>";
    }

    //DataLayer.render( 'templates/calendar_list.ejs', 'calendar:list', ["IN", "id", Calendar.calendarIds], function( html ){
    DataLayer.render( 'templates/calendar_list.ejs', Calendar, function( html ){
	
	var meu_container = $(".calendars-list").html( html );
	
	var doMenu = function(){
		$('ul.list-calendars .cal-list-options-btn').each(function(){ 
			$(this).menu({   
			content: $(this).next().html(), 
			width: '120', 
			positionOpts: { 
				posX: 'left',  
				posY: 'bottom', 
				offsetX: 0, 
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
		});
	}
	
	doMenu();
	var currentToolTip = null;
        $page.on('scroll','#divAppbox', function () {
		if ($('.cal-list-options-btn.fg-menu-open.ui-state-active')){			
			var offset = $('.cal-list-options-btn.fg-menu-open.ui-state-active').offset();
			if (offset)
			    $('.positionHelper').css('top',offset.top);
		}

		if ($('.button.config-menu.fg-menu-open')){
			var offset = $('.button.config-menu.fg-menu-open').offset();
			if (offset)
			    $('.positionHelper').css('top',offset.top);
		}		

		
		if ($(".new-group.qtip-active").length || $(".new-calendar.qtip-active").length)			
		    $('.qtip-active').css('top',currentToolTip.offset().top - 50);
		
	});

        $page.on('click','ul.list-calendars .cal-list-options-btn', function () {
            doMenu();
        });
	

    /***************************************New Calendar***************************************/
	meu_container.find(".button.new-calendar").button({
	    icons: {
		primary: "ui-icon-plus"
	    },
	    text: false
	}).click(function () {
		currentToolTip = $(this);
        var typeCalendar = !!parseInt($(this).attr('class').match(/[0-9]+/g)) ? 
            {type: 'new-group', title: '_[[New Group]]', typeValue: 1, prompt: '_[[Group Name]]'} : 
            {type: 'new-calendar', title: '_[[New Calendar]]', typeValue: 0, prompt: '_[[Calendar Name]]'}
		
	    if(!$('.qtip.qtip-blue.qtip-active.'+typeCalendar.type).length){

            $('.qtip.qtip-blue').remove();

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
        		  text: $('<div></div>').html( DataLayer.render( 'templates/calendar_quick_add.ejs', {} ) ), 
        		  title: {
        		      text: typeCalendar.title, 
        		      button: '<a class="button close" href="#">_[[Close]]</a>'
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
                	    y: -12
						
        	       }
        	    }
    	    })
        	.qtip("api").onShow = function(arg0) {
				
        	    $('.qtip-active .button.close').button({
        		  icons: { primary: "ui-icon-close" },
        		  text: false
        	    })
        	    .click(function(){
        			$('.qtip.qtip-blue').remove();
        	    });
        	    
                $('.qtip-active').addClass(typeCalendar.type);

        	    $('.qtip-active .button.cancel').button().click(function(){
        			$('.qtip.qtip-blue').remove();
        	    });
        			

        	    $('.qtip-active .button.save').button().click(function(){
                    if(!typeCalendar.typeValue)
                		for(var i = 0; i < Calendar.calendars.length; i++){
                		    if(Calendar.calendars[i].location == ( User.me.uid + '/' + $('.qtip-active input').val())){	
                    			$.Zebra_Dialog('_[[The name of this agenda is already being used in a another agenda URL. Please enter another name for schedule.]]',{
                    			    'overlay_opacity': '0.5',
                    			    'type': 'warning'
                    			});
                    			$('.qtip.qtip-blue').remove();
                    			return;
                		    }
                		}
        				
            		var selected;
            		var color = $('.cal-colors-options-btn').each(function(index){
            		    if ($(this).is('.color-selected'))
            			 selected = index;
            		});
            		DataLayer.put( "calendarSignature", {
            		    user: User.me.id,
            		    calendar: {
            			name: Encoder.htmlEncode($('.qtip-active input').val()),
            			timezone: User.preferences.timezone,
                        type: typeCalendar.typeValue			
            		    },
            		    isOwner: 1,
            		    fontColor: colorsSuggestions[selected]['font'].substring(1) ,
            		    backgroundColor: colorsSuggestions[selected]['background'].substring(1) ,
            		    borderColor: colorsSuggestions[selected]['border'].substring(1)
            		});
            		DataLayer.commit( false, false, function( received ){
            		    delete Calendar.currentViewKey;
            		    Calendar.load();
            		    refresh_calendars();
            		});
            		$('.qtip.qtip-blue').remove();
        	    });
       
        	    $(".qtip-active input").Watermark(typeCalendar.prompt);
        			
        	    $('.qtip-active').keydown(function(event) {
        		    if (event.keyCode == '27') 
        		      meu_container.find(".button.new").qtip('destroy');
        	    });
        			
        	    $('.colors-options').prepend(buttons_colors);
        	    $('.colors-options .signed-cal-colors-options-btn-0').addClass('color-selected');
        					
        	    var buttons = $('.cal-colors-options-btn').button();
        			
        	    buttons.click(function(){
            		buttons.removeClass('color-selected');
            		$(this).addClass('color-selected');
        	    });
        	}				
	   }
    });

    $("img.cal-list-img").click(function(evt) {
	   $(".cal-list-options_1").toggleClass( "hidden" );
    });

    $(".my-groups-task a.title-my-calendars").click(function() {
        $(".my-groups-task ul.my-list-calendars").toggleClass("hidden")
        $('.my-groups-task .status-list').toggleClass("ui-icon-triangle-1-s");
        $('.my-groups-task .status-list').toggleClass("ui-icon-triangle-1-e");
    });

    $(".my-calendars a.title-my-calendars").click(function() {
    	$(".my-calendars ul.my-list-calendars").toggleClass("hidden")
    	$('.my-calendars .status-list').toggleClass("ui-icon-triangle-1-s");
    	$('.my-calendars .status-list').toggleClass("ui-icon-triangle-1-e");
    });
		
    $(".signed-calendars a.title-signed-calendars").click(function() {
	   $(".signed-calendars ul.signed-list-calendars").toggleClass( "hidden");
    });

    $("ul li.list-calendars-item").click(function(evt) {
	
	});   

    $("ul li.list-calendars-item .ui-corner-all").click(function(evt) {
	//alert('teste');
	});   
        
    meu_container.find(".button.new-calendar-shared").button({
	icons: {
	    primary: "ui-icon-plus"
	},
	text: false
    }).click(function (event) {
	show_modal_search_shared();
    });
		

    meu_container.find('.title-signed-calendars').click(function(evt){
	var status = $(this).parent().find('.status-list-shared');
			
	if(status.hasClass('ui-icon-triangle-1-s'))
	    status.removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
	else
	    status.removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
    });
		
    $('.calendar-view').click(function(evt){

        var checkBox = $(this);
        var calendarId = $(this).val();

        Calendar.signatureOf[ calendarId ].hidden =  (checkBox.is(':checked') ? 0 : 1 );

        DataLayer.put('calendarSignature', {id: Calendar.signatureOf[ calendarId ].id , hidden: Calendar.signatureOf[ calendarId ].hidden }  );
        DataLayer.commit();


            if ($tabs.tabs('option', 'active') == 0) {

             if(Calendar.currentView && !!Calendar.currentView[ calendarId ]){

                 Calendar.currentView[ calendarId ].hidden = !checkBox.is(':checked');
                 $('#calendar').fullCalendar( 'refetchEvents' );
             }

         }else{
                type = $tabs.tabs('option', 'active');
             type = type > 2 ? 2 : (type - 1)

             pageselectCallback('', 0, false, type);
         }
    });
});
}

function add_events_list(keyword, type) {

    Calendar.lastView = $tabs.tabs('option', 'active');
    if ($.trim(keyword) == "") return;
    var tab_title = "";	
    if (keyword){
		type = 2;
		if(keyword.length < 10)
			tab_title = keyword; 
		else
			tab_title = keyword.substr(0,10) + '..."'; 
    }else{
		if(type){
			if(!!parseInt(type))
				tab_title = "_[[Task List]]";
			else
				tab_title = "_[[List of events]]";
		}
    }
	var tab_selector = ['tab_events_list_', 'tab_tasks_list_', 'tab_all_list_'];
    keyword = ( keyword || '' ).replace( /\s+/g, "_" );
	
    if (!(document.getElementById(tab_selector[type] + (Base64.encode(keyword)).replace(/[^\w\s]/gi, "")))) {
	Encoder.EncodeType = "entity";
        var k = Base64.encode(keyword).replace(/[^\w\s]/gi, "");
        $tabs.find('ul.ui-tabs-nav').append('<li><a href="#' + tab_selector[type] + k + '">' + Encoder.htmlEncode(tab_title) + '</a><span class="ui-icon ui-icon-close">Remove Tab</span></li>');
        $tabs.append('<div id="' + tab_selector[type] + k + '"></div>');
    }
    else /* Tab already opened */
    {
		//$tabs.tabs("option", "selected", 2);
	}
	
    pageselectCallback(keyword, 0, false, type); // load page 1 and insert data on event_list.ejs
	
    $('.preferences-win.active .button.save, .preferences-win.active .button.cancel, .preferences-win.active .button.import, .preferences-win.active .button.export').button();
}

function paginatorSearch(currentView){
    $(currentView+' .header-paginator .fc-header-left .fc-button').hover(
	function(){
	    $(this).addClass('fc-state-hover');
	},
	function(){
	    $(this).removeClass('fc-state-hover');
	}).mousedown(function(){
	$(this).addClass('fc-state-down');
    }).mouseup(function(){
	$(this).removeClass('fc-state-down');
	$('.events-list.events-list-win.active').removeClass('active');
	var paginator = $(this).attr('class');
	if(paginator.indexOf('next') > 0){
	    if(parseInt($(currentView+' [name = results]').val()) > 25)
		pageselectCallback($(currentView+' [name = keyword]').val(), ((parseInt($(currentView+' [name = page_index]').val())) +1), false,  2);
	}else{
	    if(parseInt($(currentView+' [name = page_index]').val()) > 0)
		pageselectCallback($(currentView+' [name = keyword]').val(), ((parseInt($(currentView+' [name = page_index]').val())) -1), false, 2);
	}
    });
}

function mountTitleList(page_index ,view){
    switch (view){
	case 'agendaDay':
	case 'basicDay':
	    var date = new Date().add({
		days: page_index
	    });
	    return (dateCalendar.dayNames[date.getDay()])+", "+(date.toString('dd MMM yyyy'));
	case 'agendaWeek':
	    var dateStart = new Date().moveToDayOfWeek(dateCalendar.dayOfWeek[User.preferences.weekStart]);
	    dateStart.add({
		days: (7 * page_index)
		});
	    var dateEnd = new Date().moveToDayOfWeek(dateCalendar.dayOfWeek[User.preferences.weekStart]);
	    dateEnd.add({
		days: (page_index * 7)+7
		});
	    if(dateStart.toString('MM') != dateEnd.toString('MM'))
		return dateStart.toString('dd')+' de '+dateCalendar.monthNamesShort[dateStart.getMonth()]+' a '+dateEnd.toString('dd')+' de '+dateCalendar.monthNames[dateEnd.getMonth()]+' - '+dateEnd.toString('yyyy');
	    return +dateStart.toString("dd")+" a "+dateEnd.toString("dd")+" de "+dateCalendar.monthNames[dateEnd.getMonth()]+" - "+dateEnd.toString('yyyy');
	case 'month':
	    var date = new Date().add({
		months: page_index
	    }) 
	    return dateCalendar.monthNames[date.getMonth()]+" "+date.toString("yyyy");
	case 'year':
	    var date = new Date().add({
		years: page_index
	    });
	    return date.toString("yyyy");
    }
}

function paginatorList(currentView, view, type){
    $(currentView+' .events-list.events-list-win.active .list-events-paginator .fc-header-title').html('<h2>'+mountTitleList( parseInt($(currentView+' [name = page_index]').val()),view)+'</h2>');
    $(currentView+' .events-list.events-list-win.active .header-paginator .fc-header-right .fc-button').removeClass('fc-state-active')
    if(view == 'basicDay')
	$(currentView+' .events-list.events-list-win.active .header-paginator .fc-header-right .fc-button-agendaDay').addClass('fc-state-active');
    else
	$(currentView+' .events-list.events-list-win.active .header-paginator .fc-header-right .fc-button-'+view).addClass('fc-state-active');
    $(currentView+' .events-list.events-list-win.active .header-paginator .fc-header-right').addClass('list-right');
		
    $(currentView+' .header-paginator .fc-header-right .fc-button').hover(
	function(){
	    $(this).addClass('fc-state-hover');
	},

	function(){
	    $(this).removeClass('fc-state-hover');
	}).mousedown(function(){
	$(currentView+' .events-list.events-list-win.active .header-paginator .fc-header-right .fc-button').removeClass('fc-state-active')
	$(this).addClass('fc-state-active');
    }).mouseup(function(){
	var goView = $(this).attr('class');
	if(goView.indexOf('agendaDay') > 0)
	    pageselectCallback($(currentView+' [name = keyword]').val(), 0, 'agendaDay', type);
	else if(goView.indexOf('month') > 0)
	    pageselectCallback($(currentView+' [name = keyword]').val(), 0, 'month', type);
	else if(goView.indexOf('year') > 0)
	    pageselectCallback($(currentView+' [name = keyword]').val(), 0, 'year', type);
	else if(goView.indexOf('agendaWeek') > 0)
	    pageselectCallback($(currentView+' [name = keyword]').val(), 0, 'agendaWeek', type);

    });

    $(currentView+' .header-paginator .fc-header-left .fc-button').hover(
	function(){
	    $(this).addClass('fc-state-hover');
	},
	function(){
	    $(this).removeClass('fc-state-hover');
	}).mousedown(function(){
	$(this).addClass('fc-state-down');
    }).mouseup(function(){
	$(this).removeClass('fc-state-down');
	var paginator = $(this).attr('class');
	if(paginator.indexOf('next') > 0)
	    pageselectCallback($(currentView+' [name = keyword]').val(), ((parseInt($(currentView+' [name = page_index]').val())) +1), view, type);
	else
	    pageselectCallback($(currentView+' [name = keyword]').val(), ((parseInt($(currentView+' [name = page_index]').val())) -1), view, type);
    });
    if (currentView == "#tab_events_list_" || currentView == "#tab_tasks_list_")
    	$(currentView+' .events-list.events-list-win.active .header-paginator .fc-header-left').find('span.fc-button-agendaWeek, span.fc-button-agendaDay').remove();
}

function printEventList(view){
	$('.fc-button-print.print-list-events').click(function(){
		var window_print = window.open('','ExpressoCalendar','width=800,height=600,scrollbars=yes');
		var listEvents = $(view).clone();
		listEvents.find('.fc-button').remove();
		listEvents.find('.details-event-list').remove();
		listEvents.find('.list-events-paginator').remove();
		listEvents = listEvents.html();
		type = $(this).parents('.ui-tabs-panel').attr("id").split("_")[1];

		var data = {
			type : type == "tasks" ? "task-list" : ( type == "events" ? "event-list" : "search"),
			html : listEvents,
			InfoPage : $(this).parents('table.header-paginator').find( '.fc-header-title' ).text()
		}
		window_print.document.open();		
		window_print.document.write(DataLayer.render('templates/calendar_list_print.ejs', data));
		window_print.document.close();
		window_print.print();
	});
}

function paginatorListEvent(currentView, typeView, view, type){
    if(!!$(currentView).find('.fc-calendar').length)
	return;
    $(currentView+' .events-list.events-list-win.active').prepend($('.fc-header:first').clone());
    //Remove contudo nao utilizado
    $(currentView+' .events-list.events-list-win.active .fc-header .fc-button-today').remove();
    $(currentView+' .events-list.events-list-win.active .fc-header .fc-button-basicWeek').remove();
    $(currentView+' .events-list.events-list-win.active .fc-header .fc-button-basicDay').removeClass("fc-button-basicDay").addClass('fc-button-agendaDay');			
		
    //Adiciona e remove as classes para esta visualizacao
    $(currentView+' .events-list.events-list-win.active .fc-header .fc-header-center').addClass('list-events-paginator');
    $(currentView+' .events-list.events-list-win.active .fc-header .list-events-paginator').removeClass('fc-header-center');		
    		
    //Adicionar class no header padronizar com a tela principal
	$(currentView+' .events-list.events-list-win.active .fc-header .fc-button-print').addClass('print-list-events');		
	$(currentView+' .events-list.events-list-win.active .fc-header').addClass('header-paginator');
    $(currentView+' .events-list.events-list-win.active .header-paginator').removeClass('fc-header');	
	
	printEventList(currentView);
	
    if(typeView == 'search'){
	$(currentView+' .events-list.events-list-win.active .header-paginator .fc-header-right span.fc-button:not(.fc-button-print)').remove();
	$(currentView+' .events-list.events-list-win.active .list-events-paginator .fc-header-title').html('<h2>Resultados para: '+$(currentView+' [name = keyword]').val()+'</h2>');
	if((parseInt($(currentView+' [name = page_index]').val()) == 0) && (parseInt($(currentView+' [name = results]').val()) <= 25))
	    return;
	paginatorSearch(currentView);
    }else
	paginatorList(currentView, view, type);
}

function mountCriteriaList(view, page_index, calerdars_selecteds){
    var rangeStart , rangeEnd;
    switch (view){
	case 'basicDay':
	case 'agendaDay':
	    rangeStart = new Date.today().add({ days: page_index }).getTime();
	    rangeEnd = rangeStart + 86400000;
	    break;
	case 'agendaWeek':
	    var dateStart = new Date().moveToDayOfWeek(dateCalendar.dayOfWeek[User.preferences.weekStart]); 
	    var dateEnd = new Date().moveToDayOfWeek(dateCalendar.dayOfWeek[User.preferences.weekStart]);
	    dateEnd.setHours(0,0,0);
	    dateStart.setHours(0,0,0);
	    rangeStart = dateStart.add({ days: (7 * page_index)	}).getTime();
	    rangeEnd = dateEnd.add({ days: (7 * page_index)+7 }).getTime();
	    break;
	case 'month':
	    var date = Date.today().add({ months: page_index })
	    rangeStart = date.moveToFirstDayOfMonth().getTime();
	    rangeEnd = date.moveToLastDayOfMonth().getTime() + 86400000;
	    break;
	case 'year':
	    var dateStart = new Date().add({ years: page_index });
	    var dateEnd = new Date().add({ years: page_index });
	    dateEnd.setHours(0,0,0);
	    dateStart.setHours(0,0,0);
	    if(dateStart.getMonth() != 0)
		    dateStart.moveToMonth(0, -1)
	    if(dateEnd.getMonth() != 11)
            dateEnd.moveToMonth(11)

        rangeStart = dateStart.moveToFirstDayOfMonth().getTime();
        rangeEnd = dateEnd.moveToLastDayOfMonth().getTime() + 86400000;
	    break;  
    }
			
    var timezone = {};
    for(var i in Calendar.signatureOf)
	    timezone[i] = Calendar.signatureOf[i].calendar.timezone;
	
    return  {
        rangeStart: rangeStart,
        rangeEnd: rangeEnd,
	    order: 'startTime', 
	    timezones: timezone,
        calendar: calerdars_selecteds
	};
}

function pageselectCallback(keyword, page_index, view, type){
    $('.qtip.qtip-blue').remove();
	var tab_selector = ['tab_events_list_', 'tab_tasks_list_', 'tab_all_list_'];
	var tab_title = ['_[[List of events]]', '_[[Task List]]'];
	var label_noselect_calendar = ['_[[Please select at least one agenda.]]', '_[[Please select at least one group.]]', '_[[Please select at least one book or group.]]'];
	var label_nofound_search = ['_[[No result found corresponding to your search event.]]', '_[[Not found any task or activity corresponding to your search.]]', '_[[Not found any event or task or activity corresponding to your search.]]'];
	var label_nofound = ['_[[No events found in this range.]]', '_[[No tasks or activities in this range were found.]]', '_[[No events or tasks or activities in this range were found.]]'];
	var selecteds = getSelectedCalendars(false, type);
    
	if(!selecteds && (keyword != '' && keyword != null)){	
        jQuery('#'+tab_selector[type] + ((Base64.encode(keyword)).replace(/[^\w\s]/gi, "")|| '')).html(
            '<div title="'+tab_title[type]+'" class="events-list events-list-win active empty">' +
            '<label>'+label_noselect_calendar[type]+'</label>' +
            '</div>'
        );
    }else{
        var criteria = null;
        if(keyword == '' || keyword == null){

            criteria = mountCriteriaList(!!view ? view : User.preferences.defaultCalView, page_index, selecteds);

        }else{

            var timezone = {};
            for(var i in Calendar.signatureOf)
                timezone[i] = Calendar.signatureOf[i].calendar.timezone;

            criteria =  {

                searchEvent: true,
                order: 'startTime',
                offset: (25 * page_index),
                limit: (((25 * page_index) + 25) + 1),
                summary: keyword,
                description: keyword,
                calendar: selecteds,
                timezones: timezone

            };
        }
	
        var results = DataLayer.encode('schedulable:list', DataLayer.dispatch('modules/calendar/schedules', criteria));
	//var results = DataLayer.get('schedulable:detail', criteria);
        keyword = ( keyword || '' ).replace( /\s+/g, "_" );
	}
// não há resultados	

var currentView = '#'+tab_selector[type] + ((Base64.encode(keyword)).replace(/[^\w\s]/gi, "") || '');

if ((((typeof(results) == 'undefined') || (!results.events_list )) && selecteds) &&(keyword != '' && keyword != null)) {
    $(currentView).html(
		'<div title="'+title+'" class="events-list events-list-win active empty">' +
		'<label>'+label_nofound_search[type]+'</label>' +
		'</div>'
	);
// há resultados e Agendas Selecionadas
} else{ 
    if(typeof(results) != 'undefined'){
		results['page_index'] = page_index;
		results['keyword'] = keyword;
		results['tab_title'] = tab_title[type];	
		DataLayer.render( 'templates/event_list.ejs', results, function( html ){
			
			$(currentView).html( html );
			$('.events-list-win .menu-container .button').button();
															
			$(".event-details-item").parent().click(function(event){
			event.stopImmediatePropagation();
            var container = $(this).siblings("div.details-event-list");


            //lazy data
            if( container.hasClass('hidden') ){

                //only first click
                if(!container.find('fieldset').length){

                   $(this).append( '<span style="width: 20px;" class="load-event-detail"><img style="width: 20px;" src="'+DataLayer.dispatchPath+'/modules/calendar/img/loading.gif"></img></span>');

                    var schedulable = container.find('input[name="eventid"]').val();
                    schedulable = DataLayer.encode('schedulable:detail', [getSchedulable( schedulable, '' )]);

                    schedulable = $.isArray( schedulable ) ? schedulable[0] : schedulable;

                    container.prepend( DataLayer.render( 'templates/event_detail_list.ejs', {'_event': schedulable}));

                    $(this).find('span.load-event-detail').remove();
                }
            }

            container.toggleClass("hidden")
			.find('.button.delete').click(function(event){
				var eventId = $(this).siblings('[name="eventid"]').val();
				var calendarId = $(this).siblings('[name="calendarid"]').val();
				remove_event(eventId, calendarId, ( $(this).siblings('[name="eventtype"]').val() ));
				event.stopImmediatePropagation()
			})
			.end().find('.button.edit').click(function(event){

                var schedulable = $(this).siblings('[name="eventid"]').val();
                switch($(this).siblings('[name="eventtype"]').val()){

                    case '1':
                        eventDetails( getSchedulable( schedulable, '' ), true );
                    break;
                    case '2':
                        taskDetails( getSchedulable( schedulable, '' ), true );
                    break;
                    case '3':
                        activityDetails( getSchedulable( schedulable, '' ), true );
                    break; 
                }
				event.stopImmediatePropagation()
			})
			.end().find('.button.print').click(function(event){	
				var window_print = window.open('','ExpressoCalendar','width=800,height=600,scrollbars=yes');
				var html = $(this).parents("td:first").clone();
				html.find(".menu-container.footer-container").remove();
				html.find(".fc-header-title").remove();
				var html = html.html();
				var data = {
					type : $(this).parents('.details-event-list').hasClass("details-event") ? "event-detail" : "task-detail",
					html : html,
					InfoPage : 'Detalhes: '+$(this).parents('tr.start-date').find('td span a').text()
				}
				window_print.document.open();		
				window_print.document.write(DataLayer.render('templates/calendar_list_print.ejs', data));
				window_print.document.close();
				window_print.print();
				
				event.stopImmediatePropagation()
			});

			});
			paginatorListEvent(currentView, (keyword == '' || keyword == null) ? 'list' : 'search',  !!view ? view : User.preferences.defaultCalView, type);
		});
    }else{
		$(currentView).html(
			'<div title="'+title+'" class="events-list events-list-win active empty">' +
			'<input type="hidden" name="page_index" value="'+page_index+'"></inpunt>'+
			'<input type="hidden" name="keyword" value="'+keyword+'"></inpunt>'+
			'<label class="empty-result">'+label_nofound[type]+'</label>' +
			'</div>'
			);
		paginatorListEvent(currentView, 'list', !!view ? view : User.preferences.defaultCalView, type);
    }
}
    if (currentView != '#' + tab_selector[type]){
        $tabs.tabs('refresh');
        $tabs.tabs('option', 'active', -1);
    }
}

function show_modal_import_export(tab, calendarId, typeView){
    $('.qtip.qtip-blue').remove();
    DataLayer.render( 'templates/import_export.ejs', {
	calendars: typeView == 0 ? Calendar.calendars : Calendar.groups, 
	owner: User.me.id,
	typeView: typeView
	}, function( html ){

	if (!UI.dialogs.importCalendar) {
	    UI.dialogs.importCalendar = jQuery('#div-import-export-calendar')
	    .append('<div title="' + '_[[Import and Export]]' + '"' + (typeView == 0 ? '_[[Events]]' : '_[[Tasks]]') + '" class="import-export import-export-win active"> <div>')
	    .find('.import-export-win.active').html(html).dialog({
		resizable: false, 
		modal:true, 
		width:500, 
		position: 'center'
	    });
			
	} else {
	    UI.dialogs.importCalendar.html(html);
	}
		
	var tabsImportExport = UI.dialogs.importCalendar.find(".tabs-import-export").tabs({
	    selected: tab
	});
        
	UI.dialogs.importCalendar.find('.button').button();

	tabsImportExport.find('option[value="'+calendarId+'"]').attr('selected','selected').trigger('change');
		
	var form = false;
	$('.import-event-form').fileupload({
	    sequentialUploads: true,
	    add: function (e, data) {
            form = data
            var name = form.files[0].name;
            $('.import-event-form').find('input[type="file"]').hide();
            $('.import-event-form').find('span.file-add').removeClass('hidden');
            $('.import-event-form').find('span.file-add').append('<span>'+ name +'</span><a class="button remove-attachment tiny"></a>');
            $('.import-event-form').find('.button.remove-attachment').button({
                icons: {
                primary: "ui-icon-close"
                },
                text: false
            }).click(function (event){
                $('.import-event-form').find('input[type="file"]').show();
                $('.import-event-form').find('span.file-add').addClass('hidden').html('');
                form = false;
            });

	    },
        submit:function(e, data){

            $('div.import-export').find('a.button').button('option', 'disabled', true)
            $('.import-event-form').find('span.file-add').append('<img src="../prototype/modules/calendar/img/ajax-loader.gif">');

        },
	    done: function(e, data){
            var msg = '';
            var type = '';

            if(!!data.result && data.result == '[][""]' || data.result.indexOf('Error') >= 0 ){
                msg = '_[[Error when performing the import, please check the file]]' + ' .ics';
                type = 'warning';

                $('div.import-export').find('a.button').button('option', 'disabled', false)
                $('.import-event-form').find('span.file-add img ').remove();

            }else{

                if(data.result.indexOf('schedulable') >= 0){
                    msg = '_[[Import was successful!]]';
                    type = 'confirmation';
                    Calendar.rerenderView(true);
                }else{
                        var res = JSON.parse(data.result || "[[]]");
                    var asData = false;

                    for(var i = 0; i < res.length; i++)
                        if(res[i].length > 0)
                            asData = true;

                    if(asData){
                        msg = '_[[Import was successful!]]';
                        type = 'confirmation';
                        Calendar.rerenderView(true);
                    }else{
                        msg = '_[[No new events were found in the import!]]';
                        type = 'information';
                    }
                }

                UI.dialogs.importCalendar.dialog("close");
            }

            $.Zebra_Dialog(msg, {
                'type':     type,
                'overlay_opacity': '0.5',
                'buttons':  ['_[[Close]]']
            });
	    }
	});

	UI.dialogs.importCalendar.find(".menu-import-event")        
	    .children(".import").click(function(data){
            $('.import-event-form fieldset.import-calendar', UI.dialogs.importCalendar).append(
                '<input type="hidden" name="params[calendar_timezone]" value="'+
                Calendar.signatureOf[$('.import-event-form option:selected').val()].calendar.timezone
                +'"/>')
            if(form)
                form.submit();
	});
            
	UI.dialogs.importCalendar.find(".menu-export-event")        
	.children(".export").click(function(){
	      
	    $('.export-event-form', UI.dialogs.importCalendar).submit();
	    UI.dialogs.importCalendar.dialog("close");
	/**
			 * TODO - implementar ação de exportação
			 */
	});
       
	UI.dialogs.importCalendar.find(".menu-container")
	.children(".cancel").click(function(){
	    UI.dialogs.importCalendar.dialog("close");
	});    
		
	UI.dialogs.importCalendar.dialog("open");
    });
}

function copyAndMoveTo(calendar, event, idRecurrence, type, evt ){
    /**
     * Types
     * 0 = Move
     * 1 = Copy Event end Repet
     * 2 = Copy Ocurrence
     * 3 = Copy to edit ocurrence
     * 
     **/
    if(!type)
	type = $('.calendar-copy-move input[name="typeEvent"]').val();

    getSchedulable(event,'');
    var schedulable = DataLayer.get('schedulable', event.toString());
    schedulable['class'] = '1';
        
    calendar = !!calendar ? calendar : schedulable.calendar;

    owner = decodeOwnerCalendar(calendar);
        
    if(typeof(schedulable) == "array")
	schedulable = schedulable[0];
	
    //Move eventos entre agendas
    if(parseInt(type) == 0){
		
	schedulable.lastCalendar = schedulable.calendar;
    schedulable.calendar = calendar;
	DataLayer.put('schedulable', schedulable);
	
	DataLayer.commit();
    //copia eventos entre agendas
    }else{
	
	var newSchedulable = schedulable;
	
	delete newSchedulable.id;
	delete newSchedulable.uid;
	delete newSchedulable.sequence;
	delete newSchedulable.dtstamp;
		
	delete schedulable.DayLigth;
	delete schedulable.rangeStart
	delete schedulable.rangeEnd;
	delete schedulable.lastUpdate;
                
	delete schedulable.calendar;
                
	if(schedulable.repeat && type == "1" ){
	    var repeat = DataLayer.get('repeat', schedulable.repeat);
	    delete repeat.schedulable;
	    delete repeat.id;
	    repeat.startTime = repeat.startTime == '' ? '' : new Date(parseInt(repeat.startTime)).toString('yyyy-MM-dd HH:mm:00');
	    repeat.endTime = repeat.endTime == '' ? '' : new Date(parseInt(repeat.endTime)).toString('yyyy-MM-dd HH:mm:00');
                    
	    var exceptions = DataLayer.get('repeatOccurrence', {
		filter: ['AND', ['=','repeat', schedulable.repeat], ['=','exception','1']]
		}, true);
	    if(exceptions){
		repeat.exceptions = '';
		for(var i in exceptions )
		    repeat.exceptions += exceptions[i].occurrence + ((exceptions.length -1) == parseInt(i) ? '' : ',');
                            
	    }
                    
                    
	    schedulable.repeat = repeat;
	}else{
	    if(!!idRecurrence){
		newSchedulable.endTime = parseInt(schedulable.occurrences[idRecurrence]) + (parseInt(newSchedulable.endTime) - parseInt(newSchedulable.startTime));
		newSchedulable.startTime = schedulable.occurrences[idRecurrence];
	    }
	    delete schedulable.repeat;
	}
	delete schedulable.occurrences;
                
	schedulable.calendar = DataLayer.copy(calendar);
		
	var participants = DataLayer.copy(schedulable.participants);
	delete schedulable.participants;

    if(schedulable['type'] == '2')
        delete schedulable['historic'];

        schedulable.participants =  $.map( participants, function( attendee, i ){

            var participant = DataLayer.get('participant', attendee, false);

            if(typeof(participant) == 'array')
                participant = participant[0];

            if(owner.id != participant.user)
                delete participant.status;

            delete participant.delegatedFrom;
            delete participant.id;
            delete participant.schedulable;

            participant.id = DataLayer.put('participant', participant);

            return  (parseInt(type) == 3) ? participant.id : participant ;
        });

	//Edit ocurrence
	if(parseInt(type) == 3){
	    newSchedulable.endTime = !!evt.end  ? evt.end.getTime() :  ((evt.start).getTime() + 86400000);
	    newSchedulable.startTime = evt.start.getTime(); 
                    
	    return newSchedulable;
	}
	newSchedulable.endTime = new Date(parseInt(newSchedulable.endTime) - (parseInt(newSchedulable.allDay) ? 86400000 : 0)).toString('yyyy-MM-dd HH:mm:00');
	newSchedulable.startTime = new Date(parseInt(newSchedulable.startTime)).toString('yyyy-MM-dd HH:mm:00');
	
	DataLayer.put('schedulable', newSchedulable);

    }
}

function messageHelper(msg, isShow){
    if(isShow)
	new $.Zebra_Dialog('<span style="width: 50px; height: 50px;">'+
			    '<img src="'+DataLayer.dispatchPath+'/modules/calendar/img/loading.gif"></img>'+
			'</span><label class="messagesHelpers"> '+ msg +' </label>' , {
			'buttons':  false,
			'modal': true,
			'overlay_opacity': '0.5',
			'keyboard': false,
			'overlay_close': false,
			'type': false,
			'custom_class': 'messagesHelpersExpressoCalendar'
			}
		    );
    else{
	$('.messagesHelpersExpressoCalendar').remove();
	$('.ZebraDialogOverlay').remove();
    }
}

function extendsFileupload(view, path){
    var viewName = 'div.new-'+view+'-win';
    
    path = !!path ? path : '';
    
    var maxSizeFile = 2000000;
    $('#fileupload'+view).fileupload({
	sequentialUploads: true,
	add: function (e, data) {
	    if(data.files[0].size < maxSizeFile)
		data.submit();
	},
	change: function (e, data) {
	    $.each(data.files, function (index, file) {
		var attach = {};
		attach.fileName = file.name;
		var ext = file.name.split('.');
		if(file.name.length > 10)
		    attach.fileName = ext.length == 1 ? file.name.substr(0, 10) :  file.name.substr(0, 6) + '.' + ext[ext.length -1];
		attach.fileSize = formatBytes(file.size);
		if(file.size > maxSizeFile)
		    attach.error = 'Tamanho de arquivo nao permitido!!'
				
		$(viewName+' .attachments-list').append(DataLayer.render(path+'templates/attachment_add_itemlist.ejs', {
		    file : attach
		}));
				
		if(file.size < maxSizeFile){
		    $(viewName+' .fileinput-button.new').append(data.fileInput[0]).removeClass('new');
		    $(viewName+' .attachments-list').find('[type=file]').addClass('hidden');
					
		}else
		    $(viewName+' .fileinput-button.new').removeClass('new');
				
				
		$(viewName+' .attachments-list').find('.button.close').button({
		    icons: {
			primary: "ui-icon-close"
		    },
		    text: false
		}).click(function(){
		    var idAttach = $(this).parent().find('input[name="fileId[]"]').val();
		    $(viewName+' .attachment-list').find('input[value="'+idAttach+'"]').remove();
		    $(this).parent().remove();
		
		    if(!$(viewName+' .attachment-list input').length)
			$(viewName+' .btn-danger.delete').addClass('hidden');
		
		});	
				
	    })
	},
	done: function(e, data){
	    var currentUpload = $(viewName+' .progress.after-upload:first').removeClass('after-upload').addClass('on-complete').hide();

	    if(!!data.result && data.result != "[]"){
		$(viewName+' .btn-danger.delete').removeClass('hidden');
		var newAttach = (attch = jQuery.parseJSON(data.result)) ? attch : jQuery.parseJSON(data.result[0].activeElement.childNodes[0].data);
		$(viewName+' .attachment-list').append('<input tyepe="hidden" name="attachment[]" value="'+newAttach['attachment'][0][0].id+'"/>');
		currentUpload.removeClass('on-complete').parents('p')
		.append('<input type="hidden" name="fileId[]" value="'+newAttach['attachment'][0][0].id+'"/>')
		.find('.status-upload').addClass('ui-icon ui-icon-check');
	    }else
		currentUpload.removeClass('on-complete').parents('p').find('.status-upload').addClass('ui-icon ui-icon-cancel');
	}
    });
    $('.attachments-list .button').button();    

    if(!!window.FormData)			
	$('#fileupload'+view).bind('fileuploadstart', function () {
	    var widget = $(this),
	    progressElement = $('#fileupload-progress-'+view).fadeIn(),
	    interval = 500,
	    total = 0,
	    loaded = 0,
	    loadedBefore = 0,
	    progressTimer,
	    progressHandler = function (e, data) {
		loaded = data.loaded;
		total = data.total;
	    },
	    stopHandler = function () {
		widget
		.unbind('fileuploadprogressall', progressHandler)
		.unbind('fileuploadstop', stopHandler);
		window.clearInterval(progressTimer);
		progressElement.fadeOut(function () {
		    progressElement.html('');
		});
	    },
	    formatTime = function (seconds) {
		var date = new Date(seconds * 1000);
		return ('0' + date.getUTCHours()).slice(-2) + ':' +
		('0' + date.getUTCMinutes()).slice(-2) + ':' +
		('0' + date.getUTCSeconds()).slice(-2);
	    },
	    /* formatBytes = function (bytes) {
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
        },*/
	    formatPercentage = function (floatValue) {
		return (floatValue * 100).toFixed(2) + ' %';
	    },
	    updateProgressElement = function (loaded, total, bps) {
		progressElement.html(
		    formatBytes(bps) + 'ps | ' +
		    formatTime((total - loaded) / bps) + ' | ' +
		    formatPercentage(loaded / total) + ' | ' +
		    formatBytes(loaded) + ' / ' + formatBytes(total)
		    );
	    },
	    intervalHandler = function () {
		var diff = loaded - loadedBefore;
		if (!diff) {
		    return;
		}
		loadedBefore = loaded;
		updateProgressElement(
		    loaded,
		    total,
		    diff * (1000 / interval)
		    );
	    };
	    widget
	    .bind('fileuploadprogressall', progressHandler)
	    .bind('fileuploadstop', stopHandler);
	    progressTimer = window.setInterval(intervalHandler, interval);
	});
    
}
