/* http://keith-wood.name/countdown.html
   Brazilian initialisation for the jQuery countdown extension
   Translated by Marcelo Pellicano de Oliveira (pellicano@gmail.com) Feb 2008. */
(function($) {
	$.countdown.regional['pt-BR'] = {
		labels: ['anos', 'meses', 'semanas', 'dias', 'horas', 'minutos', 'segundos'],
		labels1: ['ano', 'mês', 'semana', 'dia', 'hora', 'minuto', 'segundo'],
		compactLabels: ['a', 'm', 's', 'd'],
		whichLabels: null,
		timeSeparator: ':', isRTL: false};
	$.countdown.setDefaults($.countdown.regional['pt-BR']);
})(jQuery);
