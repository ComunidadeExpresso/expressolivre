window.document.workflow_form.onsubmit = selectAll;

function selectAll()
{
	var formulario = document.getElementsByTagName('SELECT');
	for (var i = 0; i < formulario.length; i++)
	{
		if (formulario[i].type == 'select-multiple')
		{
			var userList = formulario[i];
			for(var j = 0; j < userList.length; j++)
				userList.options[j].selected = true;
		}
	}
}

function openParticipants(newWidth, newHeight, target_element, option)
{
	newScreenX = screen.width - newWidth;
	newScreenY = 0;

	var page = 'index.php?menuaction=workflow.ui_participants.form';
	if (target_element)
		page += "&target_element=" + target_element;
	if (option)
		page += "&" + option;

	window.open(page,'','width='+newWidth+',height='+newHeight+',screenX='+newScreenX+',left='+newScreenX+',screenY='+newScreenY+',top='+newScreenY+',toolbar=no,scrollbars=no,resizable=no');
}

function delUsers(target_element)
{
	target = window.document.getElementById(target_element);
	for(var i = 0;i < target.options.length; i++)
	{
		if(target.options[i].selected)
		{
			target.options[i--] = null;
		}
	}
}
