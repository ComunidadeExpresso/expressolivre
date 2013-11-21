Alarms = {
    load: function(){
	var eventsDay = DataLayer.get('alarm:schedulable',['=', 'date', Date.today().getTime()]);
	if(eventsDay)
	    for(var i = 0; i < eventsDay.length; i++){
		this.addAlarm( eventsDay[i] );
	    }
    },

    addAlarm: function( eventDay ){            
	if(!DataLayer.tasks[parseInt(eventDay.sendTime)]){
	    DataLayer.task( parseInt(eventDay.sendTime) , function( timestamp ){

		if(!activePage && useDesktopNotification() && desktopNotification.verifyComplement()){

		    desktopNotification.sentNotification(
			'../expressoCalendar/templates/default/images/navbar.png',
			'Você possui um compromisso agendado:', 
			eventDay.event_start + ' ' + eventDay.schedulable.startTime + ' - ' + eventDay.schedulable.summary
			);

		/* TODO
		    var onClose = function(){
			if(window.location.pathname.indexoOf('expressoCalendar') >= 0)
			    window.location = url_host + '/expressoCalendar';
		    };
		*/
		    desktopNotification.showNotification(false, function(){
				window.focus();
				this.cancel();
			});

		}else{

		    var path = User.moduleName == 'expressoCalendar' ? '' : '../prototype/modules/calendar/';
		    DataLayer.render(path+'templates/alarm.ejs',{
			event: eventDay
		    }, function( html ){                                
			$.Zebra_Dialog(html , {
			    'type':     'question',
			    'overlay_opacity': '0.5',
			    'buttons':  ['Fechar'],
			    'onClose':  function(clicked) {}
			});
		    });
		}

	    });
	}
    }
}
Alarms.load();