/*
	Todo Otimizar caulculo de timezone
	Objeto Date.toString() retorna a data com inserção do offset
	Objeto Date.getTime() retorna a data sem inserção do offset
*/

var objTime = DataLayer.dispatch( "timezones" );

Timezone = {
	currentOffset : new Date().getUTCOffset(),
	daylightSaving: isNaN(parseInt( objTime.isDaylightSaving )) ? 0 : parseInt( objTime.isDaylightSaving ),

    start:false,
    end:false,

	timezones: objTime.timezones,	  

    timezone: function( tzId ){

		return this.timezones[ tzId || User.preferences.timezone ];

	},
	
	getDateCalendar: function( date, tzId, isDayLigth ){

        return date;

	},
	
	getDateEvent: function(date, tzId, isDayLigth){
        if(!tzId)
            return date;

        var timezone = this.timezone( tzId );

        if(!timezone)
            return date;

        date = this.normalizeDate(date, false, true);

        return date.add({hours: (parseInt(timezone.charAt(0) + timezone.charAt(2)) + Timezone.diff( isDayLigth )) });
	},

    diff: function(isDayLigth){

        if( !parseInt( isDayLigth ) ){
            return 0;
        }else{

            return (parseInt( isDayLigth ) == 1 ? 1 : -1);
        }

    },

	normalizeDate: function(date, current, inverse){

		var offsetDate = !!current ? this.currentOffset : date.getUTCOffset();
		return date.add({hours: (parseInt(offsetDate.charAt(0) + offsetDate.charAt(2)) * (!!inverse ? -1 : 1) )});

	},
	
	getDateMapDisponibility: function(date){

		return this.normalizeDate(date, false, true);

	},

    getHour: function(time){

        return dateCalendar.formatDate( Timezone.normalizeDate( new Date( parseInt( time ) ), false, true) , User.preferences.hourFormat);

    },

    formateHour: function(time){

        return dateCalendar.formatDate(  new Date( parseInt( time ) ) , User.preferences.hourFormat);

    },

    getDate: function( time, rang, isAllDay , notNormalize){

        return  (notNormalize && !isAllDay) ? new Date( parseInt( time ) - ((rang == 'end' && isAllDay && parseInt(isAllDay) == 1) ? 86400000 : 0)).toString( User.preferences.dateFormat ) : Timezone.normalizeDate(new Date( parseInt( time ) - ((rang == 'end' && isAllDay && parseInt(isAllDay) == 1) ? 86400000 : 0)), false, true).toString( User.preferences.dateFormat );

    },

    getDateObj: function( time, rang, isAllDay){

        return Timezone.normalizeDate(new Date( parseInt( time ) - ((rang == 'end' && isAllDay && parseInt(isAllDay) == 1) ? 86400000 : 0) ), false, true);

    },

    getDateObjCalendar: function( time, rang, isAllDay){

        return  Timezone.normalizeDate(new Date( parseInt( time ) - ((rang == 'end' && isAllDay && parseInt(isAllDay) == 1) ? 86400000 : 0)), false, true);

    }


}