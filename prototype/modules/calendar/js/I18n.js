// $.fullCalendar.setDefaults = function(d) {
//     alert(dump( $.fullCalendar.defaults ) );
//     $.fn.fullCalendar.defaults = $.extend(true, $.fn.fullCalendar.defaults, d);
//     alert(dump($.fn.fullCalendar.defaults ) );
// }

$.fullCalendar.applyLocale = function(locale) {

	setDefaults({

	isRTL:  locale.isRTL,

	firstDay: locale.firstDay,

	monthNames: locale.monthNames,

	monthNamesShort: locale.monthNamesShort,

	dayNames: locale.dayNames,

	dayNamesShort: locale.dayNamesShort,

	buttonText: {

	today: locale.currentText

	}

      });
}

