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
			'_[[You have a scheduled appointment]]' + ':',
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
                },function( html ){
                        if($('.ZebraDialog').length){
                            var cssTop = parseInt($('.ZebraDialog').css('top'));
                            if(!$('.ZebraDialog_Body').find('#event_' + eventDay.id).length){
                                $('.ZebraDialog_Body div:eq(0)').append('<hr/>' + html);
                                $('.ZebraDialog').css('top', cssTop-60);
                            }
                        }else{
                            $.Zebra_Dialog(html , {
			    'type':     'question',
                                'overlay_opacity': '0.5',
                                'buttons':  ['_[[Close]]'],
                                'onClose':  function(clicked) {}
                            });
                        }
                    });
                }
	        });
	    }
    }
}
Alarms.load();