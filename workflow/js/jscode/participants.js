var participantsClone = null;
var chkOnlyVisibleAccountsClone = null;
var searchTimer = null;
var globalSearchEnter = true;

function callAjax(action, mode, handler, parameters)
{
	var url = '$this.' + action + '.' + mode;
	if (parameters)
		cExecute(url, handler, $H(parameters).toQueryString());
	else
		cExecute(url, handler);
}

function getSectors()
{
	function resultGetSectors(data)
	{
		setSelectValue($('sector'), data['sectors']);
		$('sector').onchange = getParticipants;
		resultGetParticipants(data['participants']);
	}

	var params = {
		organization: $F('organization'),
		onlyVisibleAccounts: $F('onlyVisibleAccounts'),
		entities: $F('entities'),
		id: $F('id'),
		usePreffix: $F('usePreffix'),
		useCCParams: $F('useCCParams')
	};
	callAjax('bo_participants', 'getSectors', resultGetSectors, params);
}

function getParticipants()
{
	var params = {
		context: $F('sector'),
		onlyVisibleAccounts: $F('onlyVisibleAccounts'),
		entities: $F('entities'),
		id: $F('id'),
		usePreffix: $F('usePreffix'),
		useCCParams: $F('useCCParams')
	};
	callAjax('bo_participants', 'getEntities', resultGetParticipants, params);
}

function resultGetParticipants(data)
{
	$('search').value = '';
	setSelectValue($('participants'), data);
	participantsClone = data;
	if($('onlyVisibleAccounts'))
		chkOnlyVisibleAccountsClone = $('onlyVisibleAccounts').checked;
}

function searchParticipantsTimer(e)
{
	if (checkShortcuts((e) ? e : window.event))
		return true;

	if (searchTimer)
		clearTimeout(searchTimer);

	searchTimer = setTimeout(function(){searchParticipants($F('search'));}, 250);
}

function searchParticipants(searchString)
{
	var reg = new RegExp("<option[^>]*>[^<]*" + searchString + "[^<]*<\/option>", "gi");
	setSelectValue($('participants'), participantsClone.match(reg));
	if($('onlyVisibleAccounts'))
		$('onlyVisibleAccounts').checked = chkOnlyVisibleAccountsClone;

	var participants = $('participants');
	if (participants.options[0])
		participants.selectedIndex = 0;
}

function checkShortcuts(e)
{
	var whichCode = (e.which) ? e.which : e.keyCode;
	var handled = false;

	if (whichCode == 13) /* ENTER */
	{
		$('addUserLink').onclick();
		handled = true;
	}

	if (whichCode == 27) /* ESC */
	{
		$('exitLink').onclick();
		handled = true;
	}

	if (whichCode == 38) /* key up */
	{
		var participants = $('participants');
		if (participants.selectedIndex > 0)
			if (!participants[participants.selectedIndex - 1].disabled)
				participants.selectedIndex--;
		handled = true;
	}

	if (whichCode == 40) /* key down */
	{
		var participants = $('participants');
		if (participants.selectedIndex < participants.length - 1)
			if (!participants[participants.selectedIndex + 1].disabled)
				participants.selectedIndex++;
		handled = true;
	}

	return handled;
}

if (Event.observe)
{
	Event.observe(window, 'load', function() {
		if (typeof Prototype == 'undefined')
			return;
		/* atribui as ações aos eventos */
		var obj = $('organization');
		if (obj)
			obj.onchange = getSectors;

		obj = $('sector');
		if (obj)
			obj.onchange = getParticipants;

		obj = $('search');
		if (obj)
			obj.onkeydown = searchParticipantsTimer;

		obj = $('participants');
		if (obj){
			participantsClone = obj.innerHTML;
			if($('onlyVisibleAccounts'))
				chkOnlyVisibleAccountsClone = $('onlyVisibleAccounts').checked;
		}

		obj = $('exitLink');
		if (obj)
			obj.onclick = function(){window.close();};

		obj = $('addUserLink');
		if (obj)
			obj.onclick = addUser;

		obj = $('search');
		if (obj)
			obj.focus();

		obj = $('onlyVisibleAccounts');
		if (obj)
			obj.onclick = checkOnlyVisibleAccounts;

		obj = $('useGlobalSearch');
		if (obj)
		{
			obj.onclick = toggleFullSearch;
			toggleFullSearch();
		}
	});
};

function checkOnlyVisibleAccounts()
{
	if(!$('useGlobalSearch').checked)
		getParticipants();
	else
		toggleFullSearch();
}

function participantsFilterName(name)
{
	if (!$('useGlobalSearch').checked)
		return name;

	return name.substr(0, name.lastIndexOf('(') - 1);
}

function addUser()
{
	var participants = $('participants');
	var target = window.opener.document.getElementById($F('target'));
	var previous = '';
	var current = '';

	if ((target.tagName == 'INPUT') || (target.tagName == 'TEXTAREA'))
	{
		previous = target.value;
		if ($F('id') == 'mail')
		{
			var participantsLength = participants.options.length;
			var current = null;
			for (var i = 0; i < participantsLength; i++)
			{
				current = participants.options[i];
				if (current.selected)
					target.value += '"' + participantsFilterName(current.text) + '" ' + '<' + current.value + '>, ';
			}
		}
		else
		{
			if (participants.selectedIndex > -1)
			{
				target.value = participants.options[participants.selectedIndex].value.replace('u','');
				window.opener.document.getElementById(target.id + '_desc').value = participantsFilterName(participants.options[participants.selectedIndex].text);
			}
		}
		current = target.value;
	}
	else
	{
		previous = target.innerHTML;
		var options = '';
		var participantsLength = participants.options.length;
		var current = null;
		var insertElement;
		for (var i = 0; i < participantsLength; i++)
		{
			current = participants.options[i];
			if (current.selected)
			{
				/* checa se o elemento que será inserido já existe na select box destino */
				insertElement = true;
				for (var j = 0; j < target.options.length; j++)
					if (target.options[j].value == current.value)
					{
						insertElement = false;
						break;
					}
				if (insertElement)
					options += '<option value="' + current.value + '">' + participantsFilterName(current.text) + '</option>';
			}
		}
		if (options.length > 0)
		{
			setSelectValue(target, target.innerHTML + options);
			/* refaz o link que se perde quando modifica-se o innerHTML da select box */
			target = window.opener.document.getElementById($F('target'));
		}
		current = target.innerHTML;
	}

	/* se o código do desenvolvedor está esperando o evento onchange, dispara o evento */
	if (target.onchange)
		if (current != previous)
			target.onchange();
}

function setSelectValue(obj, value)
{
	/* IE MAGIC */
	if (obj.outerHTML)
	{
		obj.innerHTML = '';
		obj.outerHTML = obj.outerHTML.match(/<select[^>]*>/gi) + value + '</select>';
	}
	else
		obj.innerHTML = value;
}

function participantsRemoveUser(obj)
{
	if (obj.tagName == 'INPUT')
	{
		obj.value = '';
		obj = document.getElementById(obj.id + '_desc');
		if (obj)
			obj.value = '';
	}
	else
	{
		for(var i = 0;i < obj.options.length; i++)
			if(obj.options[i].selected)
				obj.options[i--] = null;
	}
}

function toggleFullSearch()
{
	$('search').value = '';

	if ($('useGlobalSearch').checked)
	{
		globalSearchEnter = true;
		if ($('organizationSectors'))
			$('organizationSectors').hide();
		if ($('globalSearchTitle'))
			$('globalSearchTitle').show();
		setSelectValue($('participants'), '');
		$('search').onkeydown = globalSearchKeyAnalyzer;
		$('globalSearchWarnings').innerHTML = 'Para executar a busca, pressione ENTER.';
	}
	else
	{
		$('globalSearchWarnings').innerHTML = '';
		if ($('globalSearchTitle'))
			$('globalSearchTitle').hide();
		if ($('organizationSectors'))
			$('organizationSectors').show();
		searchParticipants('');
		$('search').onkeydown = searchParticipantsTimer;
	}
	$('search').focus();
}

function checkGlobalSearchShortcuts(e)
{
	var whichCode = (e.which) ? e.which : e.keyCode;
	var handled = false;

	/* ENTER */
	if ((whichCode == 13) && globalSearchEnter)
	{
		performGlobalSearch();
		handled = true;
		globalSearchEnter = false;
	}

	if (handled == false)
	{
		handled = checkShortcuts(e);
		if (handled == true)
			globalSearchEnter = false;
	}

	if (handled == false)
		globalSearchEnter = true;

	return handled;
}

function globalSearchKeyAnalyzer(e)
{
	if (checkGlobalSearchShortcuts((e) ? e : window.event))
		return true;

}

function performGlobalSearch()
{
	function resultPerformGlobalSearch(data)
	{
		setSelectValue($('participants'), data['participants']);
		if (data['warnings'])
			if (data['warnings'].length > 0)
				$('globalSearchWarnings').innerHTML = data['warnings'].join('<br/>');
	}

	$('globalSearchWarnings').innerHTML = '';
	var params = {
		onlyVisibleAccounts: $F('onlyVisibleAccounts'),
		searchTerm: $F('search'),
		entities: $F('entities'),
		id: $F('id'),
		usePreffix: $F('usePreffix'),
		useCCParams: $F('useCCParams')
	};
	callAjax('bo_participants', 'globalSearch', resultPerformGlobalSearch, params);
}

function openParticipantsWindow(target, option)
{
	newWidth   = 500;
	newHeight  = 315;
	newScreenX = screen.width - newWidth;
	newScreenY = 0;
	page = 'index.php?menuaction=workflow.ui_participants.form';
	if (target)
		page += "&target_element=" + target;
	if (option)
		page += "&" + option;

	window.open(page,'','width='+newWidth+',height='+newHeight+',screenX='+newScreenX+',left='+newScreenX+',screenY='+newScreenY+',top='+newScreenY+',toolbar=no,scrollbars=no,resizable=no');
}
