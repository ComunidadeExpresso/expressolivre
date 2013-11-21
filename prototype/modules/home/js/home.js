$(document).ready(function() {
	$("#divAppboxHeader").css({fontStyle:'normal'});

//	var carlendarResource = DataLayer.read("/calendarlast");
//	var tmplCalendar = DataLayer.render("prototype/modules/home/templates/calendarlast.ejs", {resource: carlendarResource});


	var mailResource = DataLayer.read("/maillast");
	var tmplMail = DataLayer.render("prototype/modules/home/templates/maillast.ejs", {resource: mailResource});

//	var newsResource = DataLayer.read("/newslast");
//	var tmplNews = DataLayer.render("prototype/modules/home/templates/newslast.ejs", {resource: newsResource});


//	$(".portlets").append(tmplCalendar);
	$(".portlets").append(tmplMail);
//	$(".portlets").append(tmplNews);


	jQuery('.date .timable').each(function (i) {
	    jQuery(this).countdown({
		    since: new Date(parseInt($(this).text()) * 1000), 
		    significant: 1,
		    layout: 'h&aacute; {d<}{dn} {dl} {d>}{h<}{hn} {hl} {h>}{m<}{mn} {ml} {m>}{s<}{sn} {sl}{s>}', 
		    description: ' atr&aacute;s'
	    });					
	});


});
