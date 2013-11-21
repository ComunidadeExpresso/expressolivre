/* armazena os par�metro passados para a constru��o da caixa de entrada */
var workflowInboxParams;

/* um digest (MD5) das inst�ncias exibidas (para saber quando ocorreu a �ltima atualiza��o */
var workflowInstancesDigest = null;

/* armazena os nomes dos usu�rios que possuem as inst�ncias */
var workflowInboxUserNames;

/* armazena informa��es dos processos */
var workflowInboxProcessesInfo;

/* armazena os nomes das atividades */
var workflowInboxActivityNames;

/* armazena os conjuntos de a��es */
var workflowInboxActions;

/* armazena a lista de processos cujas inst�ncias o usu�rio pode acessar */
var workflowInboxProcesses;

/* indica se o usu�rio utiliza a vers�o leve da interface */
var workflowInboxLightVersion;

/* indica se a interface est� configurada para auto atualiza��o */
var workflowInboxAutoRefresh = true;

/* armazena o tempo entre cada atualiza��o, em milisegundos */
var workflowInboxRefreshTimeInterval = 120000;

/* armazena a refer�ncia do "interval" utilizado para atualiza��o */
var workflowInboxRefreshInterval = null;

/* armazena a fun��o (e par�metros) que deve ser chamada para a atualiza��o */
var workflowInboxRefreshFunction = '';

/* n�mero de atividades view abertas na interface (usado para evitar atualiza��o no caso de alguma view estar aberta) */
var workflowInboxOpenedViewActivities = 0;


/**
 * Recria os headers da caixa de entrada sem a necessidade de
 * recarregar todos os dados. � utilizado para o caso do resultado
 * ser igual ao conjunto de dados mostrados
 * @params string sortParam Nome da coluna do banco que � o par�metro order by
 * @params object Paging Objeto de pagina��o
 * @return null
 * @access public
 */
function redrawInboxHeaders(sortParam, paging)
{
	workflowInboxParams['sort'] = sortParam;

	content = '<th width="13%" align="left">' + createSortingHeaders('Data', 'wf_act_started') + '</th>';
	content += '<th width="20%" align="left">' + createSortingHeaders('Processo', 'wf_procname') + '</th>';
	content += '<th width="10%" align="left">' + createSortingHeaders('Identificador', 'insname') + '</th>';
	content += '<th width="3%" align="left">' + createSortingHeaders('P', 'wf_priority') + '</th>';
	content += '<th width="20%" align="left">' + createSortingHeaders('Atividade', 'wf_name') + '</th>';
	content += '<th width="20%" align="left">Atribu�do a</th>';
	content += '<th width="7%" align="left">A��es</th>';

	$('table_elements_inbox').firstChild.firstChild.innerHTML = content;
	$('td_tools_inbox_3').innerHTML = createPagingLinks(paging);

	return;
}

/**
 * Recebe os dados do Ajax e chama os m�todos para constru��o da interface
 * @param array data Os dados retornados por Ajax
 * @return void
 */
function inbox(data)
{
	if (_checkError(data))
		return;

	if (workflowInstancesDigest == data['instancesDigest']){
		if(workflowInboxParams && (workflowInboxParams['sort'] != data['sort_param'])){
			redrawInboxHeaders(data['sort_param'], data['paging_links']);
		}
		return;
	}

	workflowInstancesDigest = data['instancesDigest'];

	var currentSearchField = '';
	var busca = $('busca');
	if (busca)
		currentSearchField = busca.value;
	var flagSearchPerformed = false;

	if (data['params']['search_term'])
		if (data['params']['search_term'] != '')
			flagSearchPerformed = true;

	workflowInboxUserNames = data['userNames'];
	workflowInboxProcessesInfo = data['processesInfo'];
	workflowInboxActivityNames = data['activityNames'];
	workflowInboxProcesses = data['processes'];
	workflowInboxActions = data['actions'];
	workflowInboxParams = data['params'];
	workflowInboxLightVersion = data['light'];

	var information = $('workflowInboxInformation');
	if (information)
		information.remove();

	var currentInboxMenu = $('table_tools_inbox');
	var currentInboxElements = $('table_elements_inbox');
	if (currentInboxElements)
		currentInboxElements.remove();

	if (data['instances'].length > 0)
	{
		if (!currentInboxMenu)
			createInboxMenu();
		createInbox(data['instances'], data['paging_links']);
	}
	else
	{
		if ((!flagSearchPerformed) && currentInboxMenu)
			currentInboxMenu.remove();
		var pagingContainer = $('td_tools_inbox_3');
		if (pagingContainer)
			pagingContainer.innerHTML = '';
		$('content_id_0').innerHTML += '<p class="text_dsp" id="workflowInboxInformation">N�o existem atividades a serem executadas</p>';
	}

	busca = $('busca');
	if (busca)
	{
		if (flagSearchPerformed)
			busca.value = data['params']['search_term'];
		else
			if (currentSearchField != '')
				busca.value = currentSearchField;
		busca.focus();
	}
}

/**
 * Cria o menu da interface
 * @return void
 */
function createInboxMenu()
{
	var content = '<div id="extraContent"></div>';
	content += '<table id="table_tools_inbox" width="100%">';
	content += '<td id="td_tools_inbox_1" width="470">';
	content += '<ul class="horizontalMenu">';
	content += '<li><a href="javascript:group_inbox()">' + ((workflowInboxLightVersion) ? '' : '<img src="templateFile.php?file=images/group.png"/>&nbsp;') + 'Agrupar</a></li>';
	content += '<li id="processFilterButton"><a href="javascript:showProcessFilter()">' + ((workflowInboxLightVersion) ? '' : '<img src="templateFile.php?file=images/filter.png"/>&nbsp;') + 'Filtrar por Processo...</a></li>';
	content += '<li><a href="doc/manual_do_usuario.pdf">' + ((workflowInboxLightVersion) ? '' : '<img src="templateFile.php?file=images/help.png"/>&nbsp;') + 'Ajuda</a></li>';
	content += '<li id="refreshButton"><a href="javascript:workflowInboxRefreshNow()" id="refreshLink"' + (((!workflowInboxAutoRefresh) && workflowInboxLightVersion) ? ' style="color: gray !important"' : '') + '>' + ((workflowInboxLightVersion) ? '' : '<img id="reloadImage" src="templateFile.php?file=images/reload' + ((workflowInboxAutoRefresh) ? '' : '_bw') + '.png"/>&nbsp;') + 'Atualizar</a><a href="javascript:showRefreshMenu()" style="padding: 0px;"><img src="templateFile.php?file=images/arrow_ascendant.gif" style="padding-top: 11px"/></a></li>';
	content += '</ul>';
	content += '</td>';
	content += '<td id="td_tools_inbox_2" valign="middle" align="left" width="270">';
	content += '&nbsp;Busca: <input type="text" size="15" id="busca" name="busca" onkeypress="if (((window.Event) ? event.which : event.keyCode) == 13) $(\'searchInboxButton\').onclick(); return true;"/>&nbsp;<a href="#" id="searchInboxButton" onclick="searchInbox($F(\'busca\')); $(\'show_all\').show(); return false;">filtrar</a>&nbsp;&nbsp;<a href="#" id="show_all" onclick="$(\'busca\').value = \'\'; searchInbox(\'\'); this.hide(); return false;" style="display: none;">todos</a>';
	content += '</td>';
	content += '<td id="td_tools_inbox_3" align="right"></td>';
	content += '</tr>';
	content += '</table>';

	$('content_id_0').innerHTML = content;
}

/**
 * Cria a tabela das inst�ncias
 * @param array data As inst�ncias que ser�o listadas
 * @param array paging Dados da pagina��o
 * @return void
 */
function createInbox(data, paging)
{
	var content = '';
	content += '<table id="table_elements_inbox" cellpadding="2" class="inboxElements">';
	content += '<tr>';
	content += '<th width="13%" align="left">' + createSortingHeaders('Data', 'wf_act_started') + '</th>';
	content += '<th width="20%" align="left">' + createSortingHeaders('Processo', 'wf_procname') + '</th>';
	content += '<th width="10%" align="left">' + createSortingHeaders('Identificador', 'insname') + '</th>';
	content += '<th width="3%" align="left">' + createSortingHeaders('P', 'wf_priority') + '</th>';
	content += '<th width="20%" align="left">' + createSortingHeaders('Atividade', 'wf_name') + '</th>';
	content += '<th width="20%" align="left">Atribu�do a</th>';
	content += '<th width="7%" align="left">A��es</th>';
	content += '</tr>';

	var inboxLimit = data.length;
	var current;
	for (var i = 0; i < inboxLimit; i++)
	{
		current = data[i];
		content += '<tr>';
		content += '<td>' + current['wf_act_started'] + '</td>';
		if (current['viewRunAction'])
			content += '<td onclick="toggleHiddenView(\'inbox\', ' + current['wf_instance_id'] + ', ' + current['wf_activity_id'] + ', ' + current['viewRunAction']['viewActivityID'] + ', ' + workflowInboxProcessesInfo[current['wf_p_id']]['useHTTPS'] + ');" style="cursor: pointer;">' + workflowInboxProcessesInfo[current['wf_p_id']]['name'] + '</td>';
		else
			content += '<td>' + workflowInboxProcessesInfo[current['wf_p_id']]['name'] + '</td>';
		content += '<td>' + current['insname'] + '</td>';

		content += '<td>';
		content += ((workflowInboxLightVersion) ? workflowInboxPriority[current['wf_priority']] : ('<img src="templateFile.php?file=images/pr' + current['wf_priority'] + '.png"/>&nbsp;'));
		if (current['wf_status'] != 'active')
			content += ((workflowInboxLightVersion) ? '<font color="red">Exc.</font>' : '<img src="templateFile.php?file=images/exception.png"/></td>') + '&nbsp;';
		content += '<td>'+workflowInboxActivityNames[current['wf_activity_id']] + '</td>';

		content += '<td>' + workflowInboxUserNames[current['wf_user']] + '</td>';
		content += '<td>' + constructActions(current['wf_instance_id'], current['wf_activity_id'], current['wf_p_id'], current['wf_actions']) + '</td>';
		content += '</tr>';
		if (current['viewRunAction'])
			content += constructHiddenView('inbox', 6, current['wf_instance_id'], current['wf_activity_id'], current['viewRunAction']['height']);
	}

	$('content_id_0').innerHTML += content;
	$('td_tools_inbox_3').innerHTML = createPagingLinks(paging);
}

/**
 * Cria os links para ordena��o das inst�ncias
 * @param string O texto do link
 * @param string A ordena��o esperada
 * @return string O link criado
 */
function createSortingHeaders(text, expectedSort)
{
	workflowInboxParams['sort'] = workflowInboxParams['sort'].split(',').shift();
	var currentSort = workflowInboxParams['sort'].split('__');
	var theSame = (expectedSort == currentSort[0]);
	direction = false;

	var output = '';
	if (theSame)
	{
		output += '<strong>';
		direction = (currentSort[1] == 'ASC');
	}

	output += '<a href="javascript:sortInbox(\'' + expectedSort + '__' + (direction ? 'DESC' : 'ASC') + '\');">' + text + '</a>';

	if (theSame)
		output += '<img src="templateFile.php?file=images/arrow_' + (direction ? 'ascendant' : 'descendant') + '.gif"/></strong>';

	return output;
}

/**
 * Cria e exibe o menu listando os processos das inst�ncias que o usu�rio pode ver
 * @return void
 */
function showProcessFilter()
{
	hideExtraContents();
	/* se o menu j� existe, apenas o exibe */
	if ($('processFilter'))
	{
		$('divProcessFilter').style.display = '';
		$('extraContent').style.display = '';
		return;
	}

	/* coleta informa��es sobre posicionamento */
	var li = $('processFilterButton');
	var offset = Position.cumulativeOffset(li);
	var height = li.getHeight() - 1;

	/* cria as op��es do menu */
	var content = '<div id="divProcessFilter"><ul id="processFilter" onmouseover="$(\'divProcessFilter\').style.display = \'\'; $(\'extraContent\').style.display = \'\'" onmouseout="$(\'divProcessFilter\').style.display = \'none\'; $(\'extraContent\').style.display = \'none\'" class="submenu" style="top: ' + (offset[1] + height) + 'px; left: ' + offset[0] + 'px;">';
	/* insere manualmente a linha para exibir todos os processos */
	content += '<li><a href="javascript:$(\'divProcessFilter\').style.display = \'none\';filterInbox(0)">Todos</a></li>';
	var size = workflowInboxProcesses.length;
	for (var i = 0; i < size; i++)
		content += '<li><a href="javascript:$(\'divProcessFilter\').style.display = \'none\';filterInbox(' + workflowInboxProcesses[i].pid +')">' + workflowInboxProcesses[i].name + '</a></li>';
	content += '</ul>';

	/* insere o novo conte�do */
	var extraContent = $('extraContent');
	extraContent.innerHTML += content;
	extraContent.style.display = '';
}

/**
 * Cria e exibe o menu de atualiza��o da interface de Tarefas Pendentes
 * @return void
 */
function showRefreshMenu()
{
	hideExtraContents();

	/* se o menu j� existe, apenas o exibe */
	if ($('refreshMenu'))
	{
		$('divRefreshMenu').style.display = '';
		$('extraContent').style.display = '';
		return;
	}

	/* coleta informa��es sobre posicionamento */
	var li = $('refreshButton');
	var offset = Position.cumulativeOffset(li);
	var height = li.getHeight() - 1;

	/* cria as op��es do menu */
	var content = '<div id="divRefreshMenu"><ul id="refreshMenu" onmouseover="$(\'divRefreshMenu\').style.display = \'\'; $(\'extraContent\').style.display = \'\'" onmouseout="$(\'divRefreshMenu\').style.display = \'none\'; $(\'extraContent\').style.display = \'none\'" class="submenu" style="top: ' + (offset[1] + height) + 'px; left: ' + offset[0] + 'px;">';
	/* insere manualmente a linha para exibir todos os processos */
	content += '<li><a href="javascript:$(\'divRefreshMenu\').style.display = \'none\';workflowInboxStopAutoRefresh()">Interromper Atualiza��o Autom�tica</a></li>';
	content += '<li><a href="javascript:$(\'divRefreshMenu\').style.display = \'none\';workflowInboxStartAutoRefresh()">Ativar Atualiza��o Autom�tica</a></li>';
	content += '<li><a href="javascript:$(\'divRefreshMenu\').style.display = \'none\';workflowInboxRefreshNow();">Atualizar Agora</a></li>';

	/* insere o novo conte�do */
	var extraContent = $('extraContent');
	extraContent.innerHTML += content;
	extraContent.style.display = '';
}

/**
 * Oculta todos os elementos dentro do div 'extraContent'
 * @return void
 */
function hideExtraContents()
{
	$A($('extraContent').childNodes).each(function(item)
		{
			item.style.display = 'none';
		});
}

/**
 * Constr�i o menu de a��es de acordo com a permiss�o do usu�rio
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @param int processID O ID do processo
 * @param int actionID O ID do conjunto de a��es
 * @return string O c�digo XHTML do menu de a��es
 */
function constructActions(instanceID, activityID, processID, actionID)
{
	var actions = workflowInboxActions[actionID];
	var content = '';

	var instanceURL = getInstanceURL(instanceID, activityID, workflowInboxProcessesInfo[processID]['useHTTPS']);

	if (workflowInboxLightVersion)
	{
		if (actions[0]['value'])
			content += '<a href="' + instanceURL + '">Exec.</a>';
		else
			content += 'Exec';
		content += '&nbsp;<a href="javascript:constructMoreActions(' + instanceID + ', ' + activityID + ', ' + actionID + ');">Mais</a>';
	}
	else
	{
		content += '<a href="' + instanceURL + '"><img src="templateFile.php?file=images/actions/' + ((actions[0].value) ? '' : 'no_') + 'run.png" alt="' + actions[0].text + '" title="' + actions[0].text + '"/></a>&nbsp;';
		for (var i = 0; i < actions.length; i++)
			if (actions[i].name == 'view')
			content += '<a href="javascript:workflowInboxAction' + actions[i].name.charAt(0).capitalize() + actions[i].name.substr(1) + '(' + instanceID + ', ' + activityID + ');"><img src="templateFile.php?file=images/actions/' + ((actions[i].value) ? '' : 'no_') + actions[i].name + '.png" alt="' + actions[i].text + '" title="' + actions[i].text + '"/></a>&nbsp;';
		content += '<a href="javascript:constructMoreActions(' + instanceID + ', ' + activityID + ', ' + actionID + ');"><img src="templateFile.php?file=images/more_down.png" alt="Mais A��es" title="Mais A��es"/></a>';
	}
	content += '<div id="advancedActionsMenu_' + instanceID + '_' + activityID + '" onmouseout="this.hide();" onmouseover="this.show();" style="display: none; width: 150px;" class="advancedActions"></div>';

	return content;
}

/**
 * Constr�i o menu de a��es avan�adas de acordo com a permiss�o do usu�rio
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @param int actionID O ID do conjunto de a��es
 * @return string O c�digo XHTML do menu de a��es avan�adas
 */
function constructMoreActions(instanceID, activityID, actionID)
{
	var content = '';
	var div = $('advancedActionsMenu_' + instanceID + '_' + activityID);

	if (!div.innerHTML)
	{
		var actions = workflowInboxActions[actionID];
		if (workflowInboxLightVersion)
		{
			for (var i = 0; i < actions.length; i++)
				if ((actions[i].name != 'run') && (actions[i].name != 'viewrun'))
					if (actions[i].value)
						content += '<a href="javascript:workflowInboxAction' + actions[i].name.charAt(0).capitalize() + actions[i].name.substr(1) + '(' + instanceID + ', ' + activityID + ');">&nbsp;' + actions[i].text + '</a><br/>';
					else
						content += '&nbsp;' + actions[i].text + '<br/>';
		}
		else
		{
			for (var i = 0; i < actions.length; i++)
				if ((actions[i].name != 'run') && (actions[i].name != 'viewrun') && (actions[i].name != 'view'))
					if (actions[i].value)
						content += '<a href="javascript:workflowInboxAction' + actions[i].name.charAt(0).capitalize() + actions[i].name.substr(1) + '(' + instanceID + ', ' + activityID + ');"><img src="templateFile.php?file=images/actions/' + actions[i].name + '.png"/>&nbsp;' + actions[i].text + '</a><br/>';
					else
						content += '<img src="templateFile.php?file=images/actions/no_' + actions[i].name + '.png"/>&nbsp;' + actions[i].text + '<br/>';
		}
		div.innerHTML = content;
	}

	var offset = Position.cumulativeOffset(div.parentNode);
	div.style.top = (offset[1] + 20) + 'px';
	div.style.left = (offset[0] - 80) + 'px';

	if (div.visible())
		div.hide();
	else
		div.show();
}

/**
 * Cria os links de pagina��o
 * @param array paging Dados da pagina��o
 * @return string O c�digo XHTML dos links de pagina��o
 */
function createPagingLinks(pagingData)
{
	var output = '';
	if (pagingData)
	{
		var pagingSize = pagingData.length;
		for (var i = 0; i < pagingSize; i++)
		{
			if (pagingData[i].do_link == true)
				output += '<a href="javascript:draw_inbox_folder(' + pagingData[i].p_page + ', \'' + workflowInboxParams['sort'] + '\', ' + workflowInboxParams['pid'] + ', \'' + workflowInboxParams['search_term'] + '\');">' + pagingData[i].name + '</a>&nbsp;';
			else
				output += '<strong>' + pagingData[i].name + '</strong>&nbsp;';
		}
	}

	return output;
}

/**
 * Ordena as inst�ncias da interface
 * @param string sort A ordena��o selecionada
 * @return void
 */
function sortInbox(sort)
{
	draw_inbox_folder(0, sort, workflowInboxParams['pid'], workflowInboxParams['search_term']);
}

/**
 * Faz uma busca nas inst�ncias da interface
 * @param string search_term A string que ser� procurada
 * @return void
 */
function searchInbox(search_term)
{
	draw_inbox_folder(0, workflowInboxParams['sort'], workflowInboxParams['pid'], escape(search_term));
}

/**
 * Filtra a interface para exibir somente as inst�ncias de um determinado processo
 * @param int pid O ID do processo que se quer filtrar (ao utilizar 0 (zero), todos os processos ser�o exibidos
 * @return void
 */
function filterInbox(pid)
{
	draw_inbox_folder(null, workflowInboxParams['sort'], pid, workflowInboxParams['search_term']);
}

/**
 * Busca os dados, por Ajax, para a reconstru��o da interface
 * @param int p_page O n�mero da p�gina (quando houver pagina��o) que est� sendo exibida
 * @param string sort A ordena��o selecionada
 * @param int pid O ID do processo que se quer filtrar (ao utilizar 0 (zero), todos os processos ser�o exibidos
 * @param string search_term A string que ser� procurada
 * @return void
 */
function draw_inbox_folder(p_page, sort, pid, search_term)
{
	var p_page = (p_page == null) ? 0 : p_page;
	var sort = (sort == null) ? 0 : sort;
	var pid = (pid == null) ? 0 : pid;
	var search_term = (search_term == null) ? '' : search_term;

	workflowInboxRefreshFunction = 'cExecute("$this.bo_userinterface.inbox", inbox, "sort=' + sort + '&pid=' + pid + '&p_page=' + p_page + '&search_term=' + search_term + '")';
	if (workflowInboxAutoRefresh)
		workflowInboxStartRefreshInterval();

	cExecute("$this.bo_userinterface.inbox", inbox, "sort=" + sort + "&pid=" + pid + "&p_page=" + p_page + "&search_term=" + search_term);
}

/**
 * Atualiza a lista de inst�ncias
 * @return void
 */
function workflowInboxRefreshNow()
{
	if (workflowInboxRefreshFunction != '')
		eval(workflowInboxRefreshFunction);
	else
		draw_inbox_folder();
}

/**
 * P�ra o "interval" que chama a fun��o de atualiza��o
 * @return void
 */
function workflowInboxStopRefreshInterval()
{
	if (workflowInboxRefreshInterval)
	{
		clearInterval(workflowInboxRefreshInterval);
		workflowInboxRefreshInterval = null;
	}
}

/**
 * Inicia o "interval" que chama a fun��o de atualiza��o
 * @return void
 */
function workflowInboxStartRefreshInterval()
{
	workflowInboxStopRefreshInterval();
	workflowInboxRefreshInterval = setInterval('workflowInboxRefresh()', workflowInboxRefreshTimeInterval);
}

/**
 * P�ra a atualiza��o autom�tica
 * @return void
 */
function workflowInboxStopAutoRefresh()
{
	workflowInboxAutoRefresh = false;
	workflowInboxStopRefreshInterval();
	if (workflowInboxLightVersion)
		$('refreshLink').style.setProperty('color', 'gray', 'important');
	else
		$('reloadImage').src = 'templateFile.php?file=images/reload_bw.png';
}

/**
 * Inicia a atualiza��o autom�tica
 * @return void
 */
function workflowInboxStartAutoRefresh()
{
	workflowInboxAutoRefresh = true;
	workflowInboxStartRefreshInterval();
	if (workflowInboxLightVersion)
		$('refreshLink').style.setProperty('color', 'black', 'important');
	else
		$('reloadImage').src = 'templateFile.php?file=images/reload.png';
}

/**
 * Fun��o que � chamada pelo "interval" para atualizar, automaticamente, a lista de inst�ncias
 * @return void
 */
function workflowInboxRefresh()
{
	/* verifica se a aba aberta � a de "Tarefas Pendentes" */
	if (tabStack[tabStack.length - 1] != 0)
		return;

	if ($('divProgressBar').style.visibility == 'visible')
		return;

	if (workflowInboxOpenedViewActivities > 0)
		return;

	/* atualiza a lista de inst�ncias */
	workflowInboxRefreshNow();
}
