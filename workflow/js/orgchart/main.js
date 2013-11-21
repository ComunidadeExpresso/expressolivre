var refreshAreas = new Array();
var refreshEmployees = new Array();

function init_orgchart(start_tab)
{

	if ((!is_gecko) && (!is_ie6up))
	{
		alert('OPSS !! Desculpe, mas seu navegador não suporta o Workflow.\nInstale o Mozilla FireFox 1.0+ ou Internet Explorer 6.0+.');
	}
	else
	{
		initBorders(1);
		if (start_tab == null)
			start_tab = 0;

		var main_body = document.getElementById("main_body");
		main_body.style.display = '';
		if (alternate_border(start_tab) == 0)
		{
			createMenu();
			lb_initialize();
			listOrganizations();
		}
	}
}
