function initUserInterface(startTab)
{
	if (!(is_gecko || is_ie6up))
	{
		alert('OPSS !! Desculpe, mas seu navegador não suporta o Workflow.\nInstale o Mozilla FireFox 1.0+ ou Internet Explorer 6.0+.');
		return;
	}

	initBorders(5);
	document.getElementById('main_body').style.display = '';

	if (startTab == null)
		startTab = 1;

	/* converte de string para inteiro (necessário para o switch da função 'changeTab' funcione corretamente) */
	if (typeof(startTab) == 'string')
		startTab = parseInt(startTab);

	changeTab(startTab);
}

function changeTab(newTab)
{
	if (alternate_border(newTab) != 0)
		return;

	switch (newTab)
	{
		case 0:
			draw_inbox_folder(0);
			break;

		case 1:
			draw_processes_folder();
			break;

		case 2:
			draw_instances_folder();
			break;

		case 3:
			draw_externals_folder();
			break;

		case 4:
			createOrgchartLayout();
			break;

		default:
			draw_processes_folder();
			break;
	}
}

Event.observe(window, 'load', function() {
	initUserInterface($F('workflowUserInterfaceStartTab'));
});
