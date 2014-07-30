User = {
  
    init: function(){
	this.moduleName = 'expressoCalendar'; 
	this.me = DataLayer.dispatch( "me" );
		
		
	this.timezones = Timezone.timezones;
	this.isDaylightSaving = Timezone.daylightSaving;
	this.load();      
    },             
  
    load: function(){

	var defaultPreferences = {
	    dateFormat: "dd/MM/yyyy",
	    hourFormat: "HH:mm",
	    defaultCalView: "month",
	    defaultDuration: 30,
	    backgroundColor: "36C",
	    borderColor: "36C",
	    fontColor: "fff",
	    timezone: 'America/Sao_Paulo',
	    weekStart: 'SUN',
	    useDesktopNotification: 0
	};

	var pref = DataLayer.get( "modulePreference:detail", ['and',[ "=", "user", this.me.id ], [ "=", "module", this.moduleName ]] );

	this.preferences = DataLayer.merge( defaultPreferences, pref.values || {} );
	this.preferenceIds = pref.ids;
    }
}

updateActivity = false;

constantsParticipant = {
    'o' : 'organization',
    'w' : 'write',
    'p' : 'participationRequired',
    'i' : 'inviteGuests',
    'r' : 'read'
}

constantsCalendarShared = {
    'r' : 'read',
    'w' : 'write',
    'd' : 'remove',
    'b' : 'busy',
    's' : 'shared',
    'p' : 'required'
}

UI = {
    dialogs: {
	addEvent: null,
	importCalendar: null,
	sharedCalendar: null,
	copyCalendar: null,
    assingCalendar: null
    }
}

DataLayer.codec( "calendarSignature", "calendar", {
  
    decoder: function(){},
      
    encoder: function( signatures ){
	return $.map( signatures, function( signature ){
	    return {
		events: function( start, end, callback ){
		    var viewKey = start + ':' + end;
		    if( Calendar.currentViewKey !== viewKey )
		    {
				Calendar.currentViewKey = viewKey;
				Calendar.currentView =  DataLayer.encode('schedulable:calendar', DataLayer.dispatch('modules/calendar/schedules', DataLayer.criteria('schedulable:calendar', {start: start,end: end}))  );   //DataLayer.get( 'schedulable:calendar', {start: start,end: end} );
		    }

            if( !!Calendar.currentView[ signature.calendar.id ])
            {
                if(signature.hidden == true  )
                    Calendar.currentView[ signature.calendar.id ].hidden = true;
                else
                    Calendar.currentView[ signature.calendar.id ].hidden = false;
            }

		    var view = Calendar.currentView[ signature.calendar.id ];

		    callback( view && !view.hidden ? view : [] );
		},

		backgroundColor: '#' + signature.backgroundColor || User.preferences.backgroundColor,
		borderColor: '#' + signature.borderColor || User.preferences.borderColor,
		textColor: '#' + signature.fontColor || User.preferences.fontColor,
	    className: [],
		editable:  signature.isOwner
		}
	});
    }

      
});

DataLayer.codec( "calendarToPermission", "detail", {
    decoder: function( evtObj ){
    /*		Encoder.EncodeType = "entity";
		
		if( notArray = $.type(evtObj) !== "array" )
			evtObj = [ evtObj ];
		
		var res = $.map(evtObj, function( form){
			return [$.map(form.user , function( user, i){
				return { 
					calendar: form.calendar,
					user: user,
					acl: form.attendeeAcl[i],
					type: 0
				};
			})];
		});
	
	return notArray ? res[0] : res;
	*/
    },

    encoder: function( evtObj ){
	
	if(evtObj == "")
	    return "";
			
	var notArray = false;
		  
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];
		
	var constantAcl = function(acl){
	    var returns = {};

	    for (var i in constantsCalendarShared){
		returns[constantsCalendarShared[i]] = acl.indexOf(i) >= 0 ? true : false
	    }
	    return returns;
	};

	var res = $.map(evtObj, function( objEvent ){			
	    return {
		id: objEvent.id,
		type: objEvent.type,
		calendar: objEvent.calendar,
		user: objEvent.user,
		acl: constantAcl(objEvent.acl)	,
		aclValues: objEvent.acl,
		owner: objEvent.owner
	    };
	});	
	return notArray ? res[0] : res;
    }
});

DataLayer.codec( "modulePreference", "detail", {
    decoder: function( evtObj ){

	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];
		
	var res = $.map(evtObj, function( form ){

	    var returns = [];

	    for (var name in form)
		returns[ returns.length ] = {
		    name: name,
		    user: User.me.id,
		    value: form[name],
		    module: User.moduleName,
		    id: User.preferenceIds[ name ] || undefined
		};

	    return [returns];
	});
	
	return notArray ? res[0] : res;
    },

    encoder: function( evtObj ){
	var val = {}, id = {};
	for (var i in evtObj){
			
	    if( evtObj[i].value && evtObj[i].id )
	    {
		val[evtObj[i].name] = evtObj[i].value;
		id[evtObj[i].name] = evtObj[i].id;
	    }
	}
	return {
	    values: val,
	    ids: id
	};
	
    }
});

/*Todo Melhorias serço feitas na API*/
//DataLayer.poll( "schedulable" );

DataLayer.listen( "schedulable", function( status, updateData){
  
    if( status === 'serverclient' ){
        DataLayer.commit( false, false, function(){
            //Clean cache client after commit
            DataLayer.remove('schedulable', false);

            Calendar.rerenderView(true);
            if(updateActivity){

                DataLayer.remove('taskToActivity', false);
                refreshTaskActivity();
            }

        });
    }
});

//TODO - Voltar quando tratado put de varios items de um mesmo conceito,
/*
DataLayer.listen( "calendarSignature", function( status ){

    switch( status ){
		case 'serverclient':  DataLayer.commit( false, false, function( received ){
			delete Calendar.currentViewKey;
			Calendar.load();
			refresh_calendars();
		});
		break;
		case 'client':  
		break;
    }
});
*/

DataLayer.listen( "modulePreference", function( status ){

    switch( status ){
	case 'serverclient':
	    DataLayer.commit( false, false, function(){
	    User.load();
	});
	break;
	case 'client':
	    break;
    }

});

DataLayer.listen( "alarm", function( created, updated, deleted ){

    });

/*DataLayer.listen( "calendar", function( status, updateData ){

    if (updateData === false)
	switch( status ){
		case 'serverclient':  DataLayer.commit( false, false, function(){
	    
			Calendar.load();
			refresh_calendars();
	    
		});
		break;
		case 'client':  
		break;
    }
});*/

DataLayer.codec( "calendarSignature", "configure", {
    decoder: function( evtObj ){
	Encoder.EncodeType = "entity";
		
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];
		
	var res = $.map(evtObj, function( form ){
	    return{
		id: form.signature,
		user: User.me.id,	
		calendar: {
		    id: !!!parseInt(form.type) ? Calendar.calendarOf[form.signature].id : Calendar.groupOf[form.signature].id,
		    name: Encoder.htmlEncode(form.name),
		    description: Encoder.htmlEncode(form.description),
		    timezone: form.timezone,
		    defaultDuration: form.duration != "" ? form.duration : 30,
		    location: form.location
		},
		//	isOwner: 1,
		fontColor: Encoder.htmlEncode(form.fontColor.substring(1)),
		backgroundColor: Encoder.htmlEncode(form.backgroundColor.substring(1)),
		borderColor: Encoder.htmlEncode(form.borderColor.substring(1)),
		msgAdd: Encoder.htmlEncode(form.msgAdd),
		msgCancel: Encoder.htmlEncode(form.msgCancel),
		msgUpdate: Encoder.htmlEncode(form.msgUpdate),
		msgReply: Encoder.htmlEncode(form.msgReply),
		msgAlarm: Encoder.htmlEncode(form.msgAlarm),
		calendarSignatureAlarms: $.map( form.alarmTime || [], function( alarmTime, i ){
		    return (!!form.alarmId[i] ?
		    {
			type: form.alarmType[i],	
			unit: form.alarmUnit[i], 
			time: form.alarmTime[i], 
			id: form.alarmId[i]
			} :
{
			type: form.alarmType[i],	
			unit: form.alarmUnit[i], 
			time: form.alarmTime[i]
			});
		})
	    };
		
	});
	return notArray ? res[0] : res;
    },

    encoder: function( evtObj ){}
});

DataLayer.codec( "alarm", "schedulable", {

    decoder: function( evtObj ){},
	
    encoder: function (evtObjt){
	if(evtObjt == '') return false;

	if((notArray = typeof(evtObjt) !== 'array') && (!evtObjt.length))
	    evtObjt = [evtObjt];
			
	var res = $.map(evtObjt, function( objEvent ){	

	    var time = parseInt(objEvent.schedulable.startTime);
											
	    return{
		id: objEvent.schedulable.id,
		event_start: new Date( time ).setTimezoneOffset(Timezone.timezones[objEvent.schedulable.timezone]).toString( User.preferences.dateFormat),
		sendTime: parseInt(objEvent.schedulable.sendTime) / 1000,
		schedulable: {
		    startTime: dateCalendar.formatDate(Timezone.getDateEvent(new Date(time), objEvent.schedulable.timezone, objEvent.schedulable.DayLigth, 'startTime'), User.preferences.hourFormat),
		    id: objEvent.schedulable.id,
		    summary: objEvent.schedulable.summary,
		    time: objEvent.schedulable.time,	
		    unit: (dateCalendar.timeunit[objEvent.schedulable.unit.toLowerCase()]) + ( parseInt(objEvent.schedulable.time) > 1 ? 's' : '' )
		}
	    }
	});
	return res;
    }
});

DataLayer.codec( "suggestion", "duration", {

    decoder: function( evtObj ){
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];



	var meAttendee = function(attendees){
	    for(var i = 0; i < attendees.length; i++)
		if(DataLayer.get('participant', attendees[i]).user == User.me.id)
		    return attendee;
	};

	var res = $.map(evtObj, function( form ){	
	    return {
		participant : meAttendee(form.attendee), 
		startTime: Date.parseExact(form.startDate + " "+$.trim(form.startHour) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
		endTime:  Date.parseExact(form.endDate + " "+$.trim(form.endHour), formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
		allDay: ( !!form.allDay ? 1 : 0 ),
		schedulable: form.idEvent
	    }
	});	
	

	return notArray ? res[0] : res;
    },
	
    encoder: function( evtObj ){}
	
});

DataLayer.codec( "attachment", "detail", {
  
    decoder: function(evtObj){
	
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];

	var res = $.map(evtObj, function( form){
	    return [$.map(form.files , function( files){
		return {
		    source: files
		};
	    })];
	});
	return notArray ? res[0] : res;
    },
      
    encoder: function(){}

      
});

DataLayer.codec( "schedulable", "taskEdit", {

    decoder: function( evtObj ){
	Encoder.EncodeType = "entity";

    if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];

	var pref = User.preferences;
		
	var res = $.map(evtObj, function( form ){
			
	    return DataLayer.merge({
		id: form.idTask,
		percentage: form.percentage,
		type: '2', 
		status: form.taskStatus,
		participants : $.map(form.attendee, function( attendee, i ){
		    if(isNaN(attendee)){
			return{
			    id: attendee,
			    acl: form.attendeeAcl[i],
			    delegatedFrom: !!form.delegatedFrom[i] ? form.delegatedFrom[i] : 0,
			    isOrganizer: (form.attendee_organizer == attendee ? 1 : 0 ),
			    isExternal: !!parseInt(form.attendeeType[i]) ? 1 : 0,
			    acl: 'r'
			};
		    }else{
			if(DataLayer.get('participant', attendee).user == User.me.id){
			    var me = {
				user: User.me.id,
				status: form.status,
				id: attendee,
				isOrganizer: 0,
				receiveNotification : (!!form.receiveNotification ? 1 : 0),
				alarms: typeof(form.alarmTime) != 'undefined' ? 
				$.map( form.alarmTime || [], function( alarmTime, i ){

				    if( alarmTime === "" )
					return( null );

				    return !!form.alarmId[i] ?
				    {
					type: form.alarmType[i], 
					unit: form.alarmUnit[i], 
					time: form.alarmTime[i], 
					id: form.alarmId[i]
					} : 
{
					type: form.alarmType[i],
					unit: form.alarmUnit[i], 
					time: form.alarmTime[i]
					};
				}) : []
			    };
							
			    if(form.startDate){
				var tzId =  DataLayer.get('schedulable', form.idEvent).timezone || User.preferences.timezone,
				formatString = pref.dateFormat + " " + pref.hourFormat;
								
				DataLayer.put('notification', {
				    participant: me.id,
				    type: 'suggestion',
				    startTime: Date.parseExact(form.startDate + (!!form.allDay ? " 00:00": " "+$.trim(form.startHour)) , formatString ).toString('yyyy-MM-dd HH:mm:00'),
				    endTime:  Date.parseExact(form.endDate + ( !!form.allDay ? " 00:00": " "+$.trim(form.endHour)), formatString ).toString('yyyy-MM-dd HH:mm:00'),
				    allDay: ( !!form.allDay ? 1 : 0 ),
				    schedulable: form.idEvent
				});
							
			    }
			    return me;
			}else return(null);
		    };
		})
	    }, (form.group != form.lastGroup? {calendar: form.group, lastCalendar: form.lastGroup} : {}))
	});
	return notArray ? res[0] : res;
    },

    encoder: function( evtObj ){}

});

DataLayer.codec( "schedulable", "preview", {

    decoder: function( evtObj ){
	Encoder.EncodeType = "entity";
	
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];

	var pref = User.preferences;
		
	var Owner = decodeOwnerCalendar(evtObj[0].calendar);	

	var res = $.map(evtObj, function( form ){
			
	    return DataLayer.merge({
		id: form.idEvent,
		participants : $.map(form.attendee, function( attendee, i ){
		    if(isNaN(attendee)){
			return{
			    id: attendee,
			    acl: form.attendeeAcl[i],
			    delegatedFrom: !!form.delegatedFrom[i] ? form.delegatedFrom[i] : 0,
			    isOrganizer: (form.attendee_organizer == attendee ? 1 : 0 ),
			    isExternal: !!parseInt(form.attendeeType[i]) ? 1 : 0,
			    acl: form.attendeeAcl[i].replace('o', '')
			};
		    }else{
			if(DataLayer.get('participant', attendee).user == Owner.id){
			    var me = {
				user: Owner.id,
				status: form.status,
				id: attendee,
				isOrganizer: 0,
				receiveNotification : (!!form.receiveNotification ? 1 : 0),
				alarms: typeof(form.alarmTime) != 'undefined' ? 
				$.map( form.alarmTime || [], function( alarmTime, i ){

				    if( alarmTime === "" )
					return( null );

				    return !!form.alarmId[i] ?
				    {
					type: form.alarmType[i], 
					unit: form.alarmUnit[i], 
					time: form.alarmTime[i], 
					id: form.alarmId[i]
					} : 
{
					type: form.alarmType[i],
					unit: form.alarmUnit[i], 
					time: form.alarmTime[i]
					};
				}) : []
			    };
							
			    if(form.startDate){
				var tzId =  DataLayer.get('schedulable', form.idEvent).timezone || User.preferences.timezone,
				formatString = pref.dateFormat + " " + pref.hourFormat;
								
				DataLayer.put('notification', {
				    participant: me.id,
				    type: 'suggestion',
				    startTime: Date.parseExact(form.startDate + (!!form.allDay ? " 00:00": " "+$.trim(form.startHour)) , formatString ).toString('yyyy-MM-dd HH:mm:00'),
				    endTime:  Date.parseExact(form.endDate + ( !!form.allDay ? " 00:00": " "+$.trim(form.endHour)), formatString ).toString('yyyy-MM-dd HH:mm:00'),
				    allDay: ( !!form.allDay ? 1 : 0 ),
				    schedulable: form.idEvent
				});
							
			    }
			    return me;
			}else return(null);
		    };
		})
	    }, (Owner.id == User.me.id) ? (form.calendar != form.lastCalendar ? {calendar: form.calendar, lastCalendar: form.lastCalendar} : {}) : {})
	});
	return notArray ? res[0] : res;
    },

    encoder: function( evtObj ){

	var notArray = false;
      
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];

	var statusParticipants = {},
	
	statusLabels = [ '', 'accepted', 'tentative', 'cancelled', 'unanswered', 'delegated' ];
	
	var delegateAttendee = {};

	var myCalendar = function(){
		for(var i in Calendar.signatures)
	    	if(Calendar.signatures[i].isOwner == "1" && Calendar.signatures[i].type == "0")
				return Calendar.signatures[i].calendar.id;
	}
	
	for( var i = 0; i < statusLabels.length; i++ )
	    statusParticipants[ statusLabels[i] ] = 0;
	
	var res = $.map(evtObj, function( objEvent ){			
		    
	    if(!(typeof(objEvent) == 'object'))
		return (null);

	    var isAttendee = false;


	    objEvent.calendar = !!objEvent.calendar ? objEvent.calendar : (User.preferences.defaultCalendar ? User.preferences.defaultCalendar : myCalendar());

	    var Owner = decodeOwnerCalendar(objEvent.calendar);

	    var participantInfo = {}, delegatedFrom = {}, me = DataLayer.copy( Owner );
			
	    var constantAcl = function(acl){
		var returns = {};
		for (var i in constantsParticipant){
		    returns[constantsParticipant[i]] = acl.indexOf(i) >= 0 ? true : false
		}
		return returns;
	    };

	    var isShared = !objEvent.id ? false : (!!objEvent.calendar) && parseInt(Calendar.signatureOf[objEvent.calendar].isOwner) ? false : true;
	    var limitAttendee = false;

	    return {
		"class": objEvent["class"],
		id: objEvent.id,
		repeat: encodeRepeat( objEvent.repeat ),
		location: objEvent.location,
		category: objEvent.category,
		calendars: Calendar.calendars,
		calendar: objEvent.calendar,
        occurrences: objEvent.occurrences,
		summary: objEvent.summary,
        type: !!objEvent.type ? objEvent.type : 1,
		description: objEvent.description,
		timezone: objEvent.timezone,
		timezones: Timezone.timezones,
		startDate: Timezone.getDate( (objEvent.DayLigth ? objEvent.DayLigth.event.startTime : objEvent.startTime), 'start', objEvent.allDay , true),
        startHour: objEvent.DayLigth ? Timezone.getHour( objEvent.DayLigth.event.startTime) : Timezone.formateHour( objEvent.startTime ),
        startUnixTime: objEvent.DayLigth ? objEvent.DayLigth.event.startTime : objEvent.startTime,
        endDate: Timezone.getDate( (objEvent.DayLigth ? objEvent.DayLigth.event.endTime : objEvent.endTime), 'end', objEvent.allDay , true),
		endHour: objEvent.DayLigth ? Timezone.getHour( objEvent.DayLigth.event.endTime) : Timezone.formateHour( objEvent.endTime ),
		allDay: !!parseInt( objEvent.allDay ),
		dueDate: objEvent.dueDate,
		dueTime: objEvent.dueTime,
		priority: objEvent.priority,
		attachments: $.map(objEvent.attachments || [], function( attachment, i ){
		    var attach = DataLayer.get('schedulableToAttachment', attachment, false);
		    //TODO - Verificar na API retorno de id sobre os conceitos em que s?o utilizados tabelas de liga??o
		    if(!attach.name) return(null); 
		    
		    var ext = attach.name.split('.');
		    attach.name = attach.name.length < 10 ?  attach.name : ( ext.length == 1 ? attach.name.substr(0, 10) : (attach.name.substr(0, 6) + '.' +  ext[ext.length -1]));
		    attach.size = formatBytes(attach.size);
		    return attach;
		}),						
		attendee: $.map(objEvent.participants || [], function( participant, i ){						

		    if(delegateAttendee[participant])
			return(null);

		    var attend = DataLayer.get('participant', (participant.id || participant));
		    attend.user = DataLayer.get('user', attend.user);

		    statusParticipants[ statusLabels[attend.status] ]++;  	

		    if(attend.user.mail == me.mail)
			isAttendee = true;

		    if( attend.user.id ===  me.id ){
			participantInfo.user = {
			    id: attend.id,
			    status : attend.status,
			    delegatedFrom: attend.delegatedFrom || '0',
			    acl: attend.acl,
			    receiveNotification : attend.receiveNotification,
			    alarms : $.map(attend.alarms || [], function( alarm ){
				var alarm = DataLayer.get('alarm', alarm);
				return (alarm == "" ? (null) : alarm);
			    })
			};
			me = attend;
			return(null);
		    };

		    var person = {
			id: attend.id,
			name: attend.user.name != 'false' ? attend.user.name : '',
			mail: attend.user.mail,
			status : attend.status,
			isExternal: attend.isExternal,
			acl: attend.acl,
			delegatedFrom: attend.delegatedFrom
		    };

		    if(!!parseInt(attend.delegatedFrom)){
			delegatedFrom[attend.delegatedFrom] = DataLayer.copy(person);
			return(null);
		    }

		    if( !!parseInt(attend.isOrganizer )){
			participantInfo.organizer = DataLayer.copy(person);
			return(null);
		    };					

		    return (person);
		}),
		organizer: participantInfo.organizer || me,
		alarms: !!participantInfo.user ? participantInfo.user.alarms : [],
		status: !!participantInfo.user ? participantInfo.user.status : 1,
		acl: constantAcl((me.acl && me.acl != '') ? me.acl : ((!objEvent.id || objEvent.id == '') ? 'row' : 'r')),
		isShared: isShared,
		isAttendee: (isAttendee ? true : (objEvent.id && isShared ? false : true)),
		me: me,
		delegatedFrom: delegatedFrom,
		statusParticipants: (objEvent.sizeAttendees != "") ? objEvent.statusAttendees : statusParticipants,
		sizeAttendeeLimit: (objEvent.sizeAttendees != "") ? objEvent.sizeAttendees : false
	    };
	});
	return (notArray ? res[0] : res);
    }
});

DataLayer.codec( "schedulable", "task", {

    decoder: function( evtObj ){
    Encoder.EncodeType = "entity";
    
    if( notArray = $.type(evtObj) !== "array" )
        evtObj = [ evtObj ];

    var pref = User.preferences;

    var res = $.map(evtObj, function( form ){
    
        var tzId =  form.timezone || Calendar.signatureOf[form.group].calendar.timezone || User.preferences.timezone,

        formatString = pref.dateFormat + " " + pref.hourFormat;

        return DataLayer.merge({
        "class": form["class"],
        startTime: Date.parseExact(form.startDate + " "+$.trim(form.startHour) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
        endTime: (form.dueDate == '' ) ?
            Date.parseExact(form.startDate + " "+$.trim(form.startHour) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00') :
            Date.parseExact(form.dueDate + " "+$.trim(form.dueTime) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
        due: (form.dueDate == '' ) ? 0 : Date.parseExact(form.dueDate + " "+$.trim(form.dueTime) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
        allDay: ( (form.dueDate == '' && $.trim(form.startHour) == '00:00') || $.trim(form.dueTime) == '00:00' ? 1 : 0 ),
        status: form.taskStatus,      
        id: form.idTask,
        location: form.location,
        type: !!form.type ?  form.type : 2,
        percentage: form.percentage,
        priority: form.priority,
        category: form.category,
        summary: form.summary == '' ? '_[[Untitled task]]' : form.summary,
        description: form.description,
        timezone: tzId,
        attachments: $.map(form.attachment || [], function( attachment, i ){
            return {
            attachment: attachment
            }
        }),
        participants: $.map( form.attendee || [], function( attendee, i ){

            if( !attendee || attendee === "" )
            return( null );

            var participant = {};
            participant.user = (attendee!= User.me.id) ? DataLayer.get('participant', attendee).user : attendee ;

            if( participant.user === User.me.id ){
            return DataLayer.merge({
                id: attendee,
                isOrganizer: (form.attendee_organizer == attendee ? 1 : 0 ),
                alarms: participant.alarms = $.map( form.alarmTime || [], function( alarmTime, i ){
                if( alarmTime === "" )
                    return( null );
                return !!form.alarmId[i] ? {
                    type: form.alarmType[i], 
                    unit: form.alarmUnit[i], 
                    time: form.alarmTime[i], 
                    id: form.alarmId[i]
                    }:

                    {
                    type: form.alarmType[i],
                    unit: form.alarmUnit[i], 
                    time: form.alarmTime[i]
                    };
                }),
                status: !!form.status ? form.status : 3
            }, (form.delegatedFrom[i] != '0' && form.delegatedFrom[i] != '')  ? {
                delegatedFrom: form.delegatedFrom[i]
                } : {});
            }else{
                return {
                    id: attendee,
                    isOrganizer: (form.attendee_organizer == attendee ? 1 : 0 ),
                    isExternal: !!parseInt(form.attendeeType[i]) ? 1 : 0,
                    delegatedFrom: (form.delegatedFrom[i] != '0' && form.delegatedFrom[i] != '') ? form.delegatedFrom[i] : '0'
                    };
            };
        })
        }, form.lastGroup ? (( form.lastGroup == form.group ) ? {} : {calendar: form.group, lastGroup: form.lastGroup}) : {calendar: form.group});
    });

    return notArray ? res[0] : res;
    },

    encoder: function( evtObj ){

    var notArray = false;
      
    if( notArray = $.type(evtObj) !== "array" )
        evtObj = [ evtObj ];

    var statusParticipants = {},  isAttendee = false,
   
    statusLabels = [ '', 'accepted', 'tentative', 'cancelled', 'unanswered', 'delegated' ],

    participantInfo = {}, delegatedFrom = {}, me = DataLayer.copy( User.me );
    
    var delegateAttendee = {};
    
    for( var i = 0; i < statusLabels.length; i++ )
        statusParticipants[ statusLabels[i] ] = 0;
    
    var res = $.map(evtObj, function( objEvent ){           
            
        if(!(typeof(objEvent) == 'object'))
            return (null);

        var limitAttendee = false;

        return {
        "class": objEvent["class"],
        id: objEvent.id,
        location: objEvent.location,
        category: objEvent.category,
        calendar: objEvent.calendar,
        taskStatus: objEvent.status,
        groups: Calendar.groups,
        group: objEvent.group ? objEvent.group : objEvent.calendar,
        summary: objEvent.summary,
        type: !!objEvent.type ? objEvent.type : 2,
        description: objEvent.description,
        timezone: objEvent.timezone,
        startUnixTime: objEvent.startTime,
        timezones: Timezone.timezones,
        percentage: (objEvent.percentage) ? objEvent.percentage : '0',
        priority: objEvent.priority,

        startDate: Timezone.getDate( (objEvent.DayLigth ? objEvent.DayLigth.event.startTime : objEvent.startTime), 'start', objEvent.allDay ),
        startHour: objEvent.DayLigth ? Timezone.getHour( objEvent.DayLigth.event.startTime) : Timezone.formateHour( objEvent.startTime ),
        endDate: Timezone.getDate( (objEvent.DayLigth ? objEvent.DayLigth.event.endTime : objEvent.endTime), 'end', objEvent.allDay ),
        endHour: objEvent.DayLigth ? Timezone.getHour( objEvent.DayLigth.event.endTime) : Timezone.formateHour( objEvent.endTime ),
        dueDate: objEvent.DayLigth ? (objEvent.DayLigth.event.due != '0' ? Timezone.getDate( objEvent.DayLigth.event.due , 'end', objEvent.allDay ) : '') : (objEvent.due && objEvent.due != '' ? Timezone.getDate( objEvent.due , 'end', objEvent.allDay ) : ''),
        dueTime: objEvent.DayLigth ? (objEvent.DayLigth.event.due != '0' ? Timezone.getHour( objEvent.DayLigth.event.due) : '') : (objEvent.due && objEvent.due != '' ? Timezone.formateHour( objEvent.due ) : ''),

        allDay: !!parseInt( objEvent.allDay ),
	    historic: !!objEvent.historic ? decodeHistoric(objEvent) : [] ,
        attachments: $.map(objEvent.attachments || [], function( attachment, i ){
            var attach = DataLayer.get('schedulableToAttachment', attachment, false);
            //TODO - Verificar na API retorno de id sobre os conceitos em que s?o utilizados tabelas de liga??o
            if(!attach.name) return(null); 
            
            var ext = attach.name.split('.');
            attach.name = attach.name.length < 10 ?  attach.name : ( ext.length == 1 ? attach.name.substr(0, 10) : (attach.name.substr(0, 6) + '.' +  ext[ext.length -1]));
            attach.size = formatBytes(attach.size);
            return attach;
        }),                     
        attendee: $.map(objEvent.participants || [], function( participant, i ){                        

            if(delegateAttendee[participant])
                return(null);

            var attend = DataLayer.get('participant', (participant.id || participant));
            attend.user = DataLayer.get('user', attend.user);

            statusParticipants[ statusLabels[attend.status] ]++;    

            if(attend.user.mail == User.me.mail)
                isAttendee = true;

            if( attend.user.id ===  me.id ){
                participantInfo.user = {
                    id: attend.id,
                    status : attend.status,
                    delegatedFrom: attend.delegatedFrom || '0',
                    acl: attend.acl,
                    receiveNotification : attend.receiveNotification,
                    alarms : $.map(attend.alarms || [], function( alarm ){
                    var alarm = DataLayer.get('alarm', alarm);
                    return (alarm == "" ? (null) : alarm);
                    })
                };
                me = attend;
                return(null);
            };

            var person = {
                id: attend.id,
                name: attend.user.name != 'false' ? attend.user.name : '',
                mail: attend.user.mail,
                status : attend.status,
                isExternal: attend.isExternal,
                acl: attend.acl,
                delegatedFrom: attend.delegatedFrom,
                isOrganizer: attend.isOrganizer
            };

            if(!!parseInt(attend.delegatedFrom)){
                delegatedFrom[attend.delegatedFrom] = DataLayer.copy(person);

                if( !!parseInt(attend.isOrganizer)){
                    participantInfo.organizer = DataLayer.copy(person);
                }

                return null;
            }

            if( !!parseInt(attend.isOrganizer)){
                participantInfo.organizer = DataLayer.copy(person);
                return null;
            };                  

            return (person);
        }),
        organizer: participantInfo.organizer || me,
		isOrganizer: (participantInfo.organizer || me).id == me.id,
        alarms: !!participantInfo.user ? participantInfo.user.alarms : [],
        status: !!participantInfo.user ? participantInfo.user.status : 1,
        isAttendee: isAttendee,
        me: me,
        delegatedFrom: delegatedFrom,
        statusParticipants: (objEvent.sizeAttendees != "") ? objEvent.statusAttendees : statusParticipants,
        sizeAttendeeLimit: (objEvent.sizeAttendees != "") ? objEvent.sizeAttendees : false
        };
    });
    return (notArray ? res[0] : res);
    }
});

function decodeHistoric ( evt ) {
    var historic = evt.historic;
    var decoded = [];
    var attributeDecoded = {
        'startTime': '_[[Date of start]]',
        'endTime' : '_[[Date of end]]',
        'summary' : '_[[Title]]',
        'description': '_[[Description]]',
        'status': '_[[Status]]',
        'percentage': '_[[Percentage]]',
        'priority': '_[[Priority]]',
        'due' : '_[[Expected completion]]'
    };
    
    var statusDecoded = {
	1: '_[[No shares]]',
	2: '_[[In process]]',
	3: '_[[Finalized]]',
	4: '_[[Canceled]]'
    };

    var decodeDate = function(time){
        return new Date( parseInt( time) ).setTimezoneOffset( Timezone.timezone( evt.timezone ) ).toString( User.preferences.dateFormat+' - '+User.preferences.hourFormat );
    };

    var decodeItem = function(historic){
        switch(historic.attribute){
            case 'startTime':
            case 'endTime' :
            case 'due':
                return {
                    user :$.type(historic.user) == 'object' ? historic.user : DataLayer.get('user', historic.user),
                    attribute : attributeDecoded[historic.attribute],
                    beforeValue : decodeDate(historic.beforeValue),
                    afterValue : decodeDate(historic.afterValue),
                    time: decodeDate(historic.time)
                }
            break;
            case 'participant':
                return{
                    user :$.type(historic.user) == 'object' ? historic.user : DataLayer.get('user', historic.user),
                    attribute : historic.beforeValue == '' ? ('_[[New participant]]') : ('_[[Rem. participant]]'),
                    beforeValue : historic.beforeValue == '' ? '' : historic.beforeValue.mail,
                    afterValue : historic.afterValue == '' ? '' : historic.afterValue.mail,
                    time: decodeDate(historic.time)
                }
            break;
            case 'attachment':
                return{
                    user : $.type(historic.user) == 'object' ? historic.user : DataLayer.get('user', historic.user),
                    attribute : historic.beforeValue == '' ? ('_[[New attachment]]') : ('_[[Rem. attachment]]'),
                    beforeValue : historic.beforeValue,
                    afterValue : historic.afterValue,
                    time: decodeDate(historic.time)
                }
            case 'percentage':
                    return{
                    user :$.type(historic.user) == 'object' ? historic.user : DataLayer.get('user', historic.user),
                    attribute :attributeDecoded[historic.attribute],
                    beforeValue : historic.beforeValue+' %',
                    afterValue : historic.afterValue+' %',
                    time: decodeDate(historic.time)
                }
	    case 'status':
                    return{
                    user :$.type(historic.user) == 'object' ? historic.user : DataLayer.get('user', historic.user),
                    attribute :attributeDecoded[historic.attribute],
                    beforeValue : statusDecoded[historic.beforeValue],
                    afterValue : statusDecoded[historic.afterValue],
                    time: decodeDate(historic.time)
                }
            break;
            default:
                return {
                    user : $.type(historic.user) == 'object' ? historic.user : DataLayer.get('user', historic.user),
                    attribute : attributeDecoded[historic.attribute],
                    beforeValue : historic.beforeValue,
                    afterValue : historic.afterValue,
                    time: decodeDate(historic.time)
                }
            break;

        }
    }

    for(var i = 0; i < historic.length; i++)
        decoded.push(decodeItem(historic[i]));

    return decoded;

}

DataLayer.codec( "schedulable", "taskSearch", {

    decoder: function( evtObj ){

    },

    encoder: function( evtObj ){

    var notArray = false;
      
    if( notArray = $.type(evtObj) !== "array" )
        evtObj = [ evtObj ];

    var res = $.map(evtObj, function( objEvent ){           
            
        if(!(typeof(objEvent) == 'object'))
            return (null);

        return {
            id: objEvent.id,
            summary: objEvent.summary,
            description: !!objEvent.summary ? objEvent.summary : objEvent.description,


            startDate: Timezone.getDate( (objEvent.DayLigth ? objEvent.DayLigth.event.startTime : objEvent.startTime), 'start', objEvent.allDay ),
            startHour: objEvent.DayLigth ? Timezone.getHour( objEvent.DayLigth.event.startTime) : Timezone.formateHour( objEvent.startTime ),
            endDate: Timezone.getDate( (objEvent.DayLigth ? objEvent.DayLigth.event.endTime : objEvent.endTime), 'end', objEvent.allDay ),
            endHour: objEvent.DayLigth ? Timezone.getHour( objEvent.DayLigth.event.endTime) : Timezone.formateHour( objEvent.endTime ),
            dueDate: objEvent.DayLigth ? (objEvent.DayLigth.event.due != '0' ? Timezone.getDate( objEvent.DayLigth.event.due , 'end', objEvent.allDay ) : '') : (objEvent.due && objEvent.due != '' ? Timezone.getDate( objEvent.due , 'end', objEvent.allDay ) : ''),
            dueTime: objEvent.DayLigth ? (objEvent.DayLigth.event.due != '0' ? Timezone.getHour( objEvent.DayLigth.event.due) : '') : (objEvent.due && objEvent.due != '' ? Timezone.formateHour( objEvent.due ) : ''),

            allDay: !!parseInt( objEvent.allDay ),
            percentage: (objEvent.percentage) ? objEvent.percentage : '0',
			status: objEvent.status,
			priority: objEvent.priority
        }
    });
    return (notArray ? res[0] : res);
    }
});

DataLayer.codec( "schedulable", "activity", {

    decoder: function( evtObj ){
    Encoder.EncodeType = "entity";
    
    if( notArray = $.type(evtObj) !== "array" )
        evtObj = [ evtObj ];

    var pref = User.preferences;

    var res = $.map(evtObj, function( form ){
    
        var tzId =  form.timezone || Calendar.signatureOf[form.group].calendar.timezone || User.preferences.timezone,
        formatString = pref.dateFormat + " " + pref.hourFormat;

	var decodeParticipants = function(attend){
	    return [DataLayer.merge(
		{  
		    user: User.me.id, 
		    isOrganizer: 1,
		    acl: 'row',
		    alarms: $.map( form.alarmTime || [], function( alarmTime, i ){
			if( alarmTime === "" )
			    return( null );
			return !!form.alarmId[i] ? {
			    type: form.alarmType[i], 
			    unit: form.alarmUnit[i], 
			    time: form.alarmTime[i], 
			    id: form.alarmId[i]
			    }:

			    {
			    type: form.alarmType[i],
			    unit: form.alarmUnit[i], 
			    time: form.alarmTime[i]
			    };
		    })
		}, attend != '0' ? {id: attend} : {})];
	};

        return DataLayer.merge({
        "class": form["class"],
        startTime: Date.parseExact(form.startDate + " "+$.trim(form.startHour) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
        endTime:  (form.dueDate == '' )? 0 : Date.parseExact(form.dueDate + " "+$.trim(form.dueHour) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
        due: (form.dueDate == '' )? 0 : Date.parseExact(form.dueDate + " "+$.trim(form.dueHour) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
        allDay: ( !!form.allDay ? 1 : 0 ),      
        status: form.activityStatus,      
        id: form.idActivity,
        type: !!form.type ?  form.type : 2,
        percentage: form.percentage,
        priority: form.priority,
        category: form.category,
        summary: form.summary == '' ? '_[[Activity Untitled]]' : form.summary,
        description: form.description,
        timezone: tzId,
        attachments: $.map(form.attachment || [], function( attachment, i ){
            return {
            attachment: attachment
            }
        }),
        participants: decodeParticipants(form.idAttendee),
        taskToActivity:$.map( form.idtask || [], function( task, i ){

            return DataLayer.merge({
                task: task,
                owner: User.me.id
            }, form.idTaskToActivity[i] != '' ? {id: form.idTaskToActivity[i]} : {});

        })
        }, form.lastGroup ? (( form.lastGroup == form.group ) ? {} : {calendar: form.group, lastGroup: form.lastGroup}) : {calendar: form.group});
    });

    return notArray ? res[0] : res;
    },

    encoder: function( evtObj ){

    var notArray = false;
      
    if( notArray = $.type(evtObj) !== "array" )
        evtObj = [ evtObj ];
    
    var res = $.map(evtObj, function( objEvent ){           
            
        if(!(typeof(objEvent) == 'object'))
            return (null);
	
	var historic = [];
	var alarmsActivity = [];
        var decodeTasks = function(obj){

            var tasks = {}, task = {};
			
            for(var i =0; i < obj.taskToActivity.length; i++){

                taskToActivity = DataLayer.get('taskToActivity', obj.taskToActivity[i]); 
                task = taskToActivity.task;

                if($.type(task) != 'object')
                    task = DataLayer.get('schedulable', task);

                //Resolve problemas com atualização na camada Cliente
                if(!$.isNumeric(task.startTime)){
                    DataLayer.remove('schedulable', task.id, false);
                    task = DataLayer.get('schedulable', task.id);
                }

                tasks[task.id] = {
                    taskToActivity: taskToActivity.id,
                    allDay: !!parseInt(task.allDay),

                    startDate: Timezone.getDate( (task.DayLigth ? task.DayLigth.event.startTime : task.startTime), 'start', task.allDay ),
                    startHour: task.DayLigth ? Timezone.getHour( task.DayLigth.event.startTime) : Timezone.formateHour( task.startTime ),
                    endDate: Timezone.getDate( (task.DayLigth ? task.DayLigth.event.endTime : task.endTime), 'end', task.allDay ),
                    endHour: task.DayLigth ? Timezone.getHour( task.DayLigth.event.endTime) : Timezone.formateHour( task.endTime ),
                    dueDate: Timezone.getDate( (task.DayLigth ? task.DayLigth.event.due : task.due), 'end', task.allDay ),
                    dueTime: task.DayLigth ? Timezone.getHour( task.DayLigth.event.due) : Timezone.formateHour( task.due ),
					status: task.status,
					percentage: task.percentage,
					priority: task.priority,
					summary: task.summary,
					group: task.calendar
                }

                tasks[task.id].description = tasks[task.id].startDate + ' - ' + (!!task.summary ? task.summary : task.description);

		historic.push({'task': (!!task.summary ? task.summary : task.description) , 'historic': decodeHistoric(task)});
            }

            tasks.length = obj.taskToActivity.length;
            return tasks;

        };
		
        return {
        "class": objEvent["class"],
        id: objEvent.id,
        location: objEvent.location,
        category: objEvent.category,
        status: objEvent.status,
        groups: Calendar.groups,
        group: objEvent.group ? objEvent.group : objEvent.calendar,
        summary: objEvent.summary,
        type: !!objEvent.type ? objEvent.type : 2,
        description: objEvent.description,
        timezone: objEvent.timezone,
        timezones: Timezone.timezones,
        percentage: (objEvent.percentage) ? objEvent.percentage : '0',
        priority: objEvent.priority,
        startUnixTime: objEvent.startTime,
        startDate: new Date( parseInt(objEvent.startTime) ).setTimezoneOffset( Timezone.timezone( objEvent.timezone ) ).toString( User.preferences.dateFormat ),
        startHour: dateCalendar.formatDate(Timezone.getDateEvent(new Date( parseInt(objEvent.startTime)), objEvent.timezone, objEvent.calendar, objEvent.DayLigth, 'startTime'), User.preferences.hourFormat),
        endDate: new Date( parseInt(objEvent.endTime) - (!!parseInt(objEvent.allDay) ? 86400000 : 0)  ).setTimezoneOffset( Timezone.timezone( objEvent.timezone ) ).toString( User.preferences.dateFormat ),
        endHour: dateCalendar.formatDate(Timezone.getDateEvent(new Date(parseInt(objEvent.endTime)),  objEvent.timezone, objEvent.calendar, objEvent.DayLigth, 'endTime'), User.preferences.hourFormat),
        dueDate: (!objEvent.due || objEvent.due == '' || objEvent.due == '0') ? '' : new Date( parseInt(objEvent.due) ).setTimezoneOffset( Timezone.timezone( objEvent.timezone ) ).toString( User.preferences.dateFormat ),
        dueTime: (!objEvent.due || objEvent.due == '' || objEvent.due == '0') ? '' : dateCalendar.formatDate(Timezone.getDateEvent(new Date( parseInt(objEvent.due)), objEvent.timezone, objEvent.calendar, objEvent.DayLigth, 'startTime'), User.preferences.hourFormat),
        allDay: !!parseInt( objEvent.allDay ),
        historic: historic,
        tasks: objEvent.taskToActivity ? decodeTasks(objEvent) : {},
        attachments: $.map(objEvent.attachments || [], function( attachment, i ){
            var attach = DataLayer.get('schedulableToAttachment', attachment, false);
            //TODO - Verificar na API retorno de id sobre os conceitos em que s?o utilizados tabelas de liga??o
            if(!attach.name) return(null); 
            
            var ext = attach.name.split('.');
            attach.name = attach.name.length < 10 ?  attach.name : ( ext.length == 1 ? attach.name.substr(0, 10) : (attach.name.substr(0, 6) + '.' +  ext[ext.length -1]));
            attach.size = formatBytes(attach.size);
            return attach;
        }),
        me : User.me,
        attendee: $.map(objEvent.participants || [], function( participant, i ){                        

            var attend = DataLayer.get('participant', (participant.id || participant));
            attend.user = DataLayer.get('user', attend.user);
			
            return {
                id: attend.id,
                alarms : $.map(attend.alarms || [], function( alarm ){
                    var alarm = DataLayer.get('alarm', alarm);
					alarmsActivity.push(alarm == "" ? (null) : alarm);
                    return (alarm == "" ? (null) : alarm);
                })
            };
        }),
		alarms: alarmsActivity
        };
    });
    return (notArray ? res[0] : res);
    }
});

function decodeOwnerCalendar(calendar){
    if(calendar && !parseInt(Calendar.signatureOf[calendar].isOwner)){
	var Owner = DataLayer.get('calendarSignature', {
	    filter: ['AND', ['=','calendar', calendar], ['=','isOwner','1']], 
	    criteria: {
		deepness: 2
	    }
	});

    if($.isArray(Owner))
	Owner = Owner[0];
	
    return Owner.user;
}
return User.me;

    
}

function decodeRepeat ( form ) {

    var array = {};

    if( form.repeatId )
        array['id'] = form.repeatId;

    array['frequency'] = form.frequency;

    array['bymonthday'] = array['byyearday'] = array['byday'] = '';

    array['interval'] = 1 ,

        array['endTime'] = array['count'] = array['startTime'] = 0;

    if( form.frequency === 'none' )
        return( array );

    var day = [];

    $("input[type=checkbox][name='repeatweekly[]']:checked").each(function() {
        day[ day.length ] = $(this).val();
    });

    array['byday'] = day.join(',');

    var formatString = User.preferences.dateFormat + " " + User.preferences.hourFormat;

    var date = Date.parseExact( form.startDate + " "+$.trim(form.startHour) , formatString )

    array['startTime'] = date.toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00');

    if( !array['byday'] )
        switch(form.frequency) {
            case 'weekly':
                break;
            case 'daily':
                break;
            case 'monthly':
                array['bymonthday'] = date.getDate();
                break;
            case 'yearly':
                array['byyearday'] = Date.prototype.getDayOfYear(date);
                break;
            default :
                return array;
        }

    if (($(".endRepeat").val() == 'occurrences'))
        array['count'] = $(".occurrencesEnd").val();

    if (($(".endRepeat").val() == 'customDate'))
        array['endTime'] = Date.parseExact( $(".customDateEnd").val() + (" "+$.trim(form.endHour)) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00');

    array['interval']  = $(".eventInterval").val();

    /**
     wkst = [ 'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU' ]
     weekno = number
     minute = number
     second = number
     yearday = number
     monthday = number
     setpos = number
     day = number
     hour = number
     interval = number
     frequency = [ 'monthly', 'daily', 'weekly', 'yearly', 'hourly', 'minutely', 'secondly' ]
     endTime = milliseconds
     */
    return( array );

}



function encodeRepeat( repeat ){
  
    if( !repeat || repeat == '0' )
	    return( false );
    if(typeof(repeat) == "object")
	    return repeat;

    return DataLayer.get( 'repeat', repeat );
}

DataLayer.codec( "schedulable", "detail", {

    decoder: function( evtObj ){
		
	Encoder.EncodeType = "entity";
	
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];

	var pref = User.preferences;

	var res = $.map(evtObj, function( form ){
    
	    var tzId =  form.timezone || Calendar.signatureOf[form.calendar].calendar.timezone || User.preferences.timezone,

	    formatString = pref.dateFormat + " " + pref.hourFormat;

	    var Owner = decodeOwnerCalendar(form.calendar);

	    return DataLayer.merge({
		"class": form["class"],
		startTime: Date.parseExact(form.startDate + " "+$.trim(form.startHour) , formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),
		endTime:  Date.parseExact(form.endDate + " "+$.trim(form.endHour), formatString ).toString(!!form.allDay ? 'yyyy-MM-dd 00:00:00' : 'yyyy-MM-dd HH:mm:00'),  //+ (!!form.allDay ? 86400000 : 0) ,
		allDay: ( !!form.allDay ? 1 : 0 ),       
		id: form.idEvent,
		location: form.location,
		type: 1,
		category: form.category,
		priority: form.priority,
		summary: form.summary == '' ? '_[[Event untitled]]' : form.summary,
		description: form.description,
		timezone: tzId,
		attachments: $.map(form.attachment || [], function( attachment, i ){
		    return {
			attachment: attachment
		    }
		}),
		repeat: this.decodeRepeat( form ),
		participants: $.map( form.attendee || [], function( attendee, i ){

		    if( !attendee || attendee === "" )
			return( null );

		    var participant = {};
		    participant.user = (attendee!= User.me.id) ? DataLayer.get('participant', attendee).user : attendee ;

		    if( participant.user === Owner.id ){
			return DataLayer.merge({
			    id: attendee,
			    isOrganizer: (form.attendee_organizer == attendee ? 1 : 0 ),
			    acl: form.attendee_organizer == attendee ? (form.attendeeAcl[i].indexOf('o') < 0 ? form.attendeeAcl[i]+'o' : form.attendeeAcl[i]) : form.attendeeAcl[i].replace('o', ''),
			    alarms: participant.alarms = $.map( form.alarmTime || [], function( alarmTime, i ){
				if( alarmTime === "" )
				    return( null );
				return !!form.alarmId[i] ? {
				    type: form.alarmType[i], 
				    unit: form.alarmUnit[i], 
				    time: form.alarmTime[i], 
				    id: form.alarmId[i]
				    }:

				    {
				    type: form.alarmType[i],
				    unit: form.alarmUnit[i], 
				    time: form.alarmTime[i]
				    };
			    }),
			    status: !!form.status ? form.status : 3
			}, (form.delegatedFrom[i] != '0' && form.delegatedFrom[i] != '')  ? {
			    delegatedFrom: form.delegatedFrom[i]
			    } : {});
		    }else{
			return DataLayer.merge({
			    id: attendee,
			    acl: form.attendeeAcl[i],
			    isOrganizer: (form.attendee_organizer == attendee ? 1 : 0 ),
			    isExternal: !!parseInt(form.attendeeType[i]) ? 1 : 0,
			    acl: form.attendee_organizer == attendee ? (form.attendeeAcl[i].indexOf('o') < 0 ? form.attendeeAcl[i]+'o' : form.attendeeAcl[i]) : form.attendeeAcl[i].replace('o', '')
			}, (form.delegatedFrom[i] != '0' && form.delegatedFrom[i] != '') ? {
			    delegatedFrom: form.delegatedFrom[i]
			    } : {});
		    };
		})
	    }, form.lastCalendar ? (( form.lastCalendar == form.calendar ) ? {} : {calendar: form.calendar, lastCalendar: form.lastCalendar}) : {calendar: form.calendar});
	});

	return notArray ? res[0] : res;
    },


    encoder: function( evtObj ){
        if(!(!!evtObj))
            return undefined;

        var notArray = false;

        if( notArray = $.type(evtObj) !== "array" )
            evtObj = [ evtObj ];

        var pref = User.preferences;
        var res = [];

        for(var i = 0; i < evtObj.length; i++){
           res.push(DataLayer.encode('schedulable:' + (evtObj[i].type == '1' ?  'preview' : ( !!evtObj[i].taskToActivity ? 'activity': 'task')) , evtObj[i]));
        }

        if( !notArray ){
            var dates = {};
            var typeRepeat = {
                'none': false,
                'daily': '_[[Daily repetition]]',
                'weekly': '_[[Weekly repetition]]',
                'monthly': '_[[Monthly repetition]]',
                'yearly': '_[[Annual repetition]]'
            }

            for (var i=0; i < res.length; i++) {

                res[i].type = (res[i].type == '2' && !!res[i].tasks) ? '3' : res[i].type;

                var startDate = Date.parseExact( res[i]['startDate'], User.preferences.dateFormat );
                var endDate   = Date.parseExact( res[i]['endDate'], User.preferences.dateFormat );

                var duration = parseInt( endDate.getTime() ) - parseInt( startDate.getTime() );

                var occurrences = [ startDate.getTime() ];

                if( res[i].occurrences )
                {
                    occurrences = res[i].occurrences;
                }

                for( var ii = 0; ii < occurrences.length; ii++ )
                {
                    var currentDate = new Date( occurrences[ii] );
                    var counter = currentDate.clone();

                    res[i].startDate = currentDate.toString( User.preferences.dateFormat );
                    res[i].endDate = new Date( occurrences[ii] + duration ).toString( User.preferences.dateFormat );

                    if(res[i].repeat)
                        res[i].repeat = typeRepeat[res[i].repeat.frequency];
                }
            }
	    }

	    return notArray ? res[0] : res;
    }

});

DataLayer.codec( "schedulable", "list", {

    decoder: function( evtObj ){},


    encoder: function( evtObj ){
        if(!(!!evtObj))
            return undefined;

        var notArray = false;

        if( notArray = $.type(evtObj) !== "array" )
            evtObj = [ evtObj ];

        var pref = User.preferences;
        var res = [];

        for(var i = 0; i < evtObj.length; i++){
            res.push(DataLayer.encode('schedulable:' + (evtObj[i].type == '1' ?  'preview' : ( !!evtObj[i].taskToActivity ? 'activity': 'task')) , evtObj[i]));
        }

        if( !notArray ){
            var dates = {};
            var typeRepeat = {
                'none': false,
                'daily': '_[[Daily repetition]]',
                'weekly': '_[[Weekly repetition]]',
                'monthly': '_[[Monthly repetition]]',
                'yearly': '_[[Annual repetition]]'
            }

            for (var i=0; i < res.length; i++) {

                res[i].type = (res[i].type == '2' && !!res[i].tasks) ? '3' : res[i].type;

                var startDate = Date.parseExact( res[i]['startDate'], User.preferences.dateFormat );
                var endDate   = Date.parseExact( res[i]['endDate'], User.preferences.dateFormat );

                var duration = parseInt( endDate.getTime() ) - parseInt( startDate.getTime() );

                var occurrences = [ startDate.getTime() ];

                if( res[i].occurrences )
                {
                    occurrences = res[i].occurrences;
                }

                for( var ii = 0; ii < occurrences.length; ii++ )
                {
                    var currentDate = new Date(parseInt(occurrences[ii]));
                    var counter = currentDate.clone();

                    var res2 = $.extend( {}, res[i], {
                        'startDate': currentDate.toString( User.preferences.dateFormat ),
                        'endDate': new Date( occurrences[ii] + duration ).toString( User.preferences.dateFormat )
                    } );

                    if(res[i].repeat)
                        res2.repeat = typeRepeat[res[i].repeat.frequency];

                    while (counter.compareTo( currentDate ) == 0) {

                        if (!dates[counter.toString(User.preferences.dateFormat)])
                            dates[counter.toString(User.preferences.dateFormat)] = {
                                startDate:false,
                                events:[]
                            };
                        if (!dates[counter.toString(User.preferences.dateFormat)].startDate)
                            dates[counter.toString(User.preferences.dateFormat)].startDate = counter.toString(User.preferences.dateFormat);

                        dates[counter.toString(User.preferences.dateFormat)].events.push(res2);
                        counter.addDays(-1);
                    }
                }
            }
            res = {
                events_list: dates,
                count : res.length
            };
        }

        return notArray ? res[0] : res;
    }

});


DataLayer.codec( "schedulable", "print", {

    decoder: function( evtObj ){

    },


    encoder: function( evtObj ){
    
    if(!(!!evtObj))
        evtObj = [];

    var notArray = false;
          
    if( notArray = $.type(evtObj) !== "array" )
        evtObj = [ evtObj ];

    var pref = User.preferences;
    var res = [];

    for(var i = 0; i < evtObj.length; i++){
       res.push(DataLayer.encode('schedulable:' + (evtObj[i].type == '1' ?  'preview' : ( !!evtObj[i].taskToActivity ? 'activity': 'task')) , evtObj[i]));
    }

    if( !notArray ){
        var dates = {};

        var typeRepeat = {
        'none': false,
        'daily': '_[[Daily repetition]]',
        'weekly': '_[[Weekly repetition]]',
        'monthly': '_[[Monthly repetition]]',
        'yearly': '_[[Annual repetition]]'
        }

        var orderByStartUnixTime = function( a , b )
        {
            return parseInt(a.startUnixTime) > parseInt(b.startUnixTime);
        }

        for (var i=0; i < res.length; i++) {

            if(Calendar.currentView[res[i].calendar].hidden == true)
                continue;

			res[i].type = (res[i].type == '2' && !!res[i].tasks) ? '3' : res[i].type;
		
            var startDate = Date.parseExact( res[i]['startDate'], User.preferences.dateFormat );
            var endDate   = Date.parseExact( res[i]['endDate'], User.preferences.dateFormat );

            var duration = parseInt( endDate.getTime() ) - parseInt( startDate.getTime() );

            var occurrences = [  res[i].startUnixTime ];

            if( res[i].occurrences )
            {
                occurrences = res[i].occurrences;
            }

            for( var ii = 0; ii < occurrences.length; ii++ )
            {
                var clientOffSet = new Date().getTimezoneOffset() * 60 * 1000;
                var currentDate = new Date( parseInt(occurrences[ii]) + clientOffSet );
                var counter = currentDate.clone();
                        
                var res2 = $.extend( {}, res[i], {
                    'startDate': currentDate.toString( User.preferences.dateFormat ), 
                    'endDate': new Date( parseInt(occurrences[ii]) + duration ).toString( User.preferences.dateFormat )
                });

                res2.startUnixTime = parseInt(occurrences[ii]);

                if(res[i].repeat)
                    res2.repeat = typeRepeat[res[i].repeat.frequency];
                var index = dateCalendar.dayNames[counter.getDay()] +' '+counter.toString('dd/MM');

                if(!dates[index] || !$.isArray(dates[index].events))
                    dates[index] = {events:  []};                    

				res2['isOneDay'] = res2.startDate == res2.endDate ? true : false;

                dates[index].events.push(res2);

                dates[index].events = dates[index].events.sort(orderByStartUnixTime);
                  
            }
        }

        var calendarView = $('#calendar').fullCalendar('getView');
        var start = calendarView.start.getTime();
        var end = (calendarView.end.getTime() - (calendarView.name == 'month' ? 86400000 : 0 ));
        var next = start;
        var response = [];
        while (next){
            var index = dateCalendar.dayNames[new Date(next).getDay()] +' '+ new Date(next).toString('dd/MM');
            
            var event = {};
            event[index] = {events: dates[index] ? dates[index].events : false};

            response[response.length] = event;
            next = (next + 86400000) > end ? false : (next + 86400000);
        }
        res = {events: response};
    }

    return notArray ? res[0] : res;
    }

});

DataLayer.codec( "participant", "detail", {
  
    encoder: function( participants ){

	var result = {};
	for( var i = 0; i< participants.length; i++ ){
	    result[ participants[i].user.id || participants[i].user ] = participants[i];
	}
	return( result );
    }
});

DataLayer.codec( "calendar", "list", {
  
    encoder: function( calendars ){

	var result = {
	    my_calendars: [], 
	    others_calendars: []
	};

	for( var i = 0; i < calendars.length; i++ )
	{
	    !!Calendar.signatureOf[ calendars[i].id ].isOwner ?
	    result.my_calendars.push( calendars[i] ) :
	    result.others_calendars.push( calendars[i] );
	}

	return {
	    agendas_usuario: result
	};

    }

});

DataLayer.codec( "schedulable", "calendar", {

    decoder:function( evtObj ){
	
	if( notArray = $.type(evtObj) !== "array" )
	    evtObj = [ evtObj ];
	
	var res = $.map(evtObj, function( evt ){

	    return {
		id: evt.id,
		summary: evt.title,
		startTime: evt.start.getTime(),
		endTime: ( !!evt.allDay ? (( evt.end || evt.start ).getTime() + 86400000) :( evt.end || evt.start ).getTime()),
		allDay: ( !!evt.allDay ? 1 : 0 )
	    /*calendar: evt.id && User.calendarOf[ evt.id ].id*/ };
	});

	return notArray ? res[0] : res;
    },

    encoder: function( evtObj, filter ){
      
	if( !evtObj )
	    return( false );

	var filtered = evtObj;
	// 
	var grouped = {};

	$.map(filtered, function( evt ){
		
	    if(!(typeof(evt) == 'object') || (evt.id.indexOf('java') >= 0))
			return (null);

	    evt.calendar = evt.calendar || "1";

	    if( !grouped[ evt.calendar ] )
			grouped[ evt.calendar ] = [];
			
	    var calendar = DataLayer.get('calendar', evt.calendar);
		
		var taskEditable = function(idTask){

            return evt.editable == '1' ?
                {
                    editable: true,
                    disableResizing: false,
                    disableDragging: false,
                    className: 'fullcalendar-context-menu  event-type-2 event-id-'+idTask+' calendar-id-'+calendar.id
                }
                :
                {
                    editable: false,
                    disableResizing: true,
                    disableDragging: true,
                    className: 'blocked-event-permision  fullcalendar-not-context-menu event-id-'+idTask
                };
		}
		
	    var eventEditable = function(idEvent, isRecurrence, Recurrence){
			if(Calendar.signatureOf[calendar.id].isOwner == "1"){

                return (evt.editable == '1' ) ?
                {
                    selectable: true,
                    editable: true,
                    className: 'fullcalendar-context-menu  event-type-1 event-id-'+idEvent+' calendar-id-'+calendar.id+ (isRecurrence ? ' isRecurrence Recurrence-id-'+Recurrence : '')
                } : {
                    editable: false,
                    selectable: true ,
                    className: 'blocked-event-permision  fullcalendar-not-context-menu event-id-'+idEvent
                };

			}else{
				var aclSignature = Calendar.signatureOf[calendar.id].permission;
					
				var mountClass =  function(acl){
					var returns = ""
					returns += acl['write'] ? "" :  'blocked-event-permision ';
					returns += acl['busy'] ? 'fullcalendar-not-context-menu ' : (acl['read']  ?  'fullcalendar-context-menu '+ (isRecurrence ? ' isRecurrence Recurrence-id-'+Recurrence : '') : '');
					returns += 'event-id-'+idEvent+' calendar-id-'+calendar.id;
					return returns;
				}
				
				return DataLayer.merge({
					editable: evt.editable,
                        disableResizing : (((aclSignature.acl['busy'] && !aclSignature.acl['write']) || (!aclSignature.acl['write'] && aclSignature.acl['read'])) ? true : false),
					disableDragging  : (((aclSignature.acl['busy'] && !aclSignature.acl['write']) || (!aclSignature.acl['write'] && aclSignature.acl['read'])) ? true: false),
					className: mountClass(aclSignature.acl)
					}, aclSignature.acl['busy'] ? 
					{
						title: '_[[Busy]]',
						selectable: false
					} : {
						selectable: true
					}
				);	
			}
	    }

		var duration = parseInt( evt.DayLigth.calendar.endTime ) - parseInt( evt.DayLigth.calendar.startTime ), isRepeat = false;

		var occurrences = [];
		
		if( evt.occurrences )
		{
			isRepeat = true;
			occurrences = evt.occurrences;
	    }else
			occurrences[ occurrences.length ] = evt.DayLigth.calendar.startTime;

	    //occurrences = DataLayer.unique( occurrences ).sort();
		var typeEvent;  
	    for( var i = 0; i < occurrences.length; i++ )
		{
            typeEvent = (evt.type == 2 && evt.taskToActivity ? 3 : evt.type)
    		grouped[ evt.calendar ].push( DataLayer.merge(
    		{
				id: evt.URI || evt.id+ '-' + i,
				title: Encoder.htmlDecode(evt.summary),
				start: Timezone.getDateObjCalendar( occurrences[i], 'start', evt.allDay),
                end: Timezone.getDateObjCalendar( (parseInt( occurrences[i] ) + duration), 'end', evt.allDay),
				allDay: parseInt( evt.allDay ),
				isRepeat: isRepeat,
				occurrence: occurrences[i],
				type: typeEvent,
				calendar: evt.calendar,
                unanswered: evt.unanswered,
				status: evt.status
				}, (parseInt(typeEvent) == 1 ? eventEditable(evt.id, isRepeat, i ) : (parseInt(typeEvent) == 2 ? taskEditable(evt.id) : {editable: false, disableResizing: true, disableDragging: true}))));
        }
	});

	return(/* notArray ? filtered[0] :*/ grouped );
    },
    
    criteria: function( filter ){
      
        if( $.type(filter.start) !== 'date' )
            filter.start = new Date( filter.start * 1000 );
        if( $.type(filter.end) !== 'date' )
            filter.end = new Date( filter.end * 1000 );

        var timezone = {};
        for(var i in Calendar.signatureOf)
            timezone[i] = Calendar.signatureOf[i].calendar.timezone;

        return {
            timezones: timezone,
            rangeStart: filter.start.getTime(),
            rangeEnd: filter.end.getTime(),
            calendar:  (Calendar.calendarIds.concat( Calendar.groupIds ))
        }
    }
});


DataLayer.codec( "preference", "detail", {
  
    decoder:function( pref ){
	
	var res = [];

	pref.defaultAlarm = $.map( pref.alarmTime || [], function( alarmTime, i ){
		  
	    return {
		type: pref.alarmType[i], 
		time: alarmTime,
		unit: pref.alarmUnit[i]
		};
	});

	$.each( pref, function( i, el ){

	    res[ res.length ] = {
		name: i, 
		value: el
	    };

	});
		
	return( res );
      
    },

    encoder:function( pref ){
	return( pref );
    }

});

DataLayer.codec( "schedulable", "export", {
  
    decoder: function(){},
      
    encoder: function( signatures ){},
      
    criteria: function( filter ){
	
	if( isCal  = filter && filter.calendar )
	    filter = filter.calendar;
	return {
	    filter: filter ? [  "=", ( isCal ? "calendar" : "id" ), filter ] : false,
	    criteria: {
		format: 'iCal', 
		deepness: 2
	    }
	};
    }
});

User.init();

// DataLayer.decoder( "participant", "detail", function( form ){
//  
// //     if( $.type( attObj ) !== "array" ){
// // 	notArray = true;
// // 	attObj = [ attObj ];
// //     }
// 
//     
// 
//     return( participants );
// 
// });

// DataLayer.decoder( "alarm", "detail", function( form ){
// 
// //       if( $.type( attObj ) !== "array" ){
// // 	    notArray = true;
// // 	    attObj = [ attObj ];
// //     }
// 
//     var alarms = [];
// 
//     if( form.alarmType /*&& !form.*/ )
// 	for( var i = 0; i < form.alarmType.length; i++ )
// 	{
// 	    if( form.alarmTime[i] === "" )
// 		continue;
// 	  
// 	    alarms[i] = { type: form.alarmType[i], 
// 			  unit: form.alarmUnit[i], 
// 			  time: form.alarmTime[i] };
// 	}
// 
//     return( alarms );
// 
// });

