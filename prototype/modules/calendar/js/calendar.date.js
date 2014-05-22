dateCalendar = {

    monthNamesShort:
        [
            '_[[Jan]]',
            '_[[Feb]]',
            '_[[Mar]]',
            '_[[Apr]]',
            '_[[May]]',
            '_[[June]]',
            '_[[July]]',
            '_[[Aug]]',
            '_[[Sept]]',
            '_[[Oct]]',
            '_[[Nov]]',
            '_[[Dec]]'
        ],

    //MonthOfNumber
    monthNames:
        [
            '_[[January]]',
            '_[[February]]',
            '_[[March]]',
            '_[[April]]',
            '_[[May]]',
            '_[[June]]',
            '_[[July]]',
            '_[[August]]',
            '_[[September]]',
            '_[[October]]',
            '_[[November]]',
            '_[[December]]'
        ],
    //weekOfDay
    dayNames:
        [
            '_[[Sunday]]',
            '_[[Monday]]',
            '_[[Tuesday]]',
            '_[[Wednesday]]',
            '_[[Thursday]]',
            '_[[Friday]]',
            '_[[Saturday]]'
        ],
    dayNamesShort:
        [
            '_[[Sun]]',
            '_[[Mon]]',
            '_[[Tue]]',
            '_[[Wed]]',
            '_[[Thu]]',
            '_[[Fri]]',
            '_[[Sat]]'
        ],
    dayNamesShortest:
        [
            '_[[SundayShort]]',
            '_[[MondayShort]]',
            '_[[TuesdayShort]]',
            '_[[WednesdayShort]]',
            '_[[ThursdayShort]]',
            '_[[FridayShort]]',
            '_[[SaturdayShort]]'
        ],

    dayOfWeek:
    {
        'SUN': 0,
        'MON': 1,
        'TUE': 2,
        'WED': 3,
        'THU': 4,
        'FRI': 5,
        'SAT': 6
    },

	timeunit :
	{
		'h': '_[[time]]',
		'd': '_[[day]]',
		'm': '_[[minute]]'
	},
	
	alarmtype:{
		'alert' : '_[[alert]]',
		'mail' : '_[[mail]]',
		'sms': 'sms'
	},
	
	defaultToAmPm : function (Hour)
	{
		var HourAmPm = Hour.split(":");
		if(HourAmPm[0] == 0)
			HourAmPm[0] = 12;	
		if(HourAmPm[0] < 12){
			Hour += (Hour.length == 5) ? " am" : "";
		}else if(HourAmPm[0] == 12){
			Hour += (Hour.length == 5) ? " pm" : "";
		}else
			Hour = (((HourAmPm[0]-12)>=10) ? "" : "0") +(HourAmPm[0]-12)+":"+HourAmPm[1]+ ((Hour.length == 5) ? " pm" : "");
		return Hour;
	},
	
	AmPmTo24 : function (Hour)
	{
		var Hour24h = Hour.trim();
		var AmPm;
		if (Hour24h.length == 0) return;
		if (Hour24h.length > 5) {
		  AmPm = Hour24h.slice(-2);
		  Hour24h = Hour24h.substring(0,5);
		}
		
		var Hour24h = Hour24h.split(":");
		
		if (Hour24h[0] == 12)
		  Hour24h[0] = "00";

		if (AmPm === "pm") {
		  Hour24h[0] = parseInt(Hour24h[0]) + 12;
		} 
		
		return Hour24h[0] + ":" + Hour24h[1];
	},
	
    // 01:00 retorna 1, 10:00 retorna 10, 22:00 retorna 10
    getShortestTime : function(Hour) {
                var _hour = this.defaultToAmPm(Hour);
                if (_hour[0] == 0)
                  _hour = _hour[1];
                else
                  _hour = _hour.substring(0,2);
                
                return _hour;
	},
	
	formatDate: function( date, format ){
	
		return dateFormat( date, format.replace(/m/g, 'M') );

	},

	toString: function(date, format){
			return dateFormat( date, format.replace(/M/g, 'm') );
	},
	
	decodeRange: function(date, range){
			return (parseInt(date.getTime()) + (range * 60000));
	}
		
}

