var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');
var smtp = new Tabs(2,'activetab','inactivetab','smtp','smtpcontent','smtpselector','','');
var imap = new Tabs(3,'activetab','inactivetab','imap','imapcontent','imapselector','','');

function initAll()
{
	tab.init();
	smtp.init();
	imap.init();
}
