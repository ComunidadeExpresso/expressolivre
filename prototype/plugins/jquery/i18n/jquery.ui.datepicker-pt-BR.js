/* Brazilian initialisation for the jQuery UI date picker plugin. */
/* Written by Leonildo Costa Silva (leocsilva@gmail.com). */
jQuery(function($){
	$.datepicker.regional['pt-BR'] = {
		closeText: '_[[Close]]',
		prevText: '&#x3c;Anterior',
		nextText: 'Pr&oacute;ximo&#x3e;',
		currentText: '_[[Today]]',
		monthNames: ['_[[January]]','_[[February]]','_[[March]]','_[[April]]','_[[May]]','_[[June]]',
		'_[[July]]','_[[August]]','_[[September]]','_[[October]]','_[[November]]','_[[December]]'],
		monthNamesShort: ['_[[Jan]]','_[[Feb]]','_[[Mar]]','_[[Apr]]','_[[May]]','_[[June]]',
		'_[[July]]','_[[Aug]]','_[[Sept]]','_[[Oct]]','_[[Nov]]','_[[Dec]]'],
		dayNames: ['_[[Sunday]]','_[[Monday]]','_[[Tuesday]]','_[[Wednesday]]','_[[Thursday]]','_[[Friday]]','_[[Saturday]]'],
		dayNamesShort: ['_[[Sun]]','_[[Mon]]','_[[Tue]]','_[[Wed]]','_[[Thu]]','_[[Fri]]','_[[Sat]]'],
		dayNamesMin: ['_[[Sun]]','_[[Mon]]','_[[Tue]]','_[[Wed]]','_[[Thu]]','_[[Fri]]','_[[Sat]]'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['pt-BR']);
});