var target;
var target_desc;
var source;

function genericListSetLists(source_id,target_id)
{
	target   		= window.opener.document.getElementById(target_id);
	target_desc 	= window.opener.document.getElementById(target_id + "_desc");
	source 			= window.document.getElementById(source_id);
}

function genericCheckShortcuts(e)
{
	var whichCode = (e.which) ? e.which : e.keyCode;
	var handled = false;

	if (whichCode == 13) /* ENTER */
		handled = true;

	if (whichCode == 27) /* ESC */
	{
		document.getElementById("closeButton").onclick();
		handled = true;
	}

	if (whichCode == 38) /* key up */
	{
		if (source.selectedIndex > 0)
			if (!source[source.selectedIndex - 1].disabled)
				source.selectedIndex--;
		handled = true;
	}

	if (whichCode == 40) /* key down */
	{
		if (source.selectedIndex < source.length - 1)
			if (!source[source.selectedIndex + 1].disabled)
				source.selectedIndex++;
		handled = true;
	}

	return handled;
}

function genericListOptionFinder(oText, e)
{
	if (genericCheckShortcuts(e))
		return true;

	var searchText = oText.value.toUpperCase();
	var textSize = searchText.length;
	for(i = 0; i < source.length; i++)
	if(source[i].text.substring(0 ,textSize).toUpperCase() == searchText)
	{
		source.value = source[i].value;
		break;
	}
}

function genericListAdd()
{
	for (var i = 0 ; i < source.length ; i++)
		if (source.options[i].selected)
		{
			target.value = source.options[i].value;
			target_desc.value = source.options[i].text;
			break;
		}
}

function genericListRemove(target_name, target_desc_name)
{
	var target = document.getElementById(target_name);
	var target_desc = document.getElementById(target_desc_name);
	target.value = '';
	target_desc.value = '';
}

function openGenericList(target, option)
{
	newWidth   = 350;
	newHeight  = 500;
	newScreenX = screen.width - newWidth;
	newScreenY = 0;
	page = 'index.php?menuaction=workflow.ui_generic_select.form';
	if (target)
		page += "&target_element=" + target;

	if (option)
		page += "&" + option;

	window.open(page,'','width='+newWidth+',height='+newHeight+',screenX='+newScreenX+',left='+newScreenX+',screenY='+newScreenY+',top='+newScreenY+',toolbar=no,scrollbars=no,resizable=no');
}
