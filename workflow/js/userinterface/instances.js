/* armazena os parâmetro passados para a construção da interface */
var workflowInstancesParams;

/* armazena os nomes dos usuários que possuem as instâncias */
var workflowInstancesUserNames;

/* armazena informações dos processos */
var workflowInstancesProcessesInfo;

/* armazena os nomes das atividades */
var workflowInstancesActivityNames;

/* armazena a lista de processos cujas instâncias o usuário pode acessar */
var workflowInstancesProcesses;

/* array que relaciona um status a uma imagem */
var workflowInstancesStatusImages = Array();
workflowInstancesStatusImages['active'] = 'i_active.png';
workflowInstancesStatusImages['exception'] = 'i_waiting.png';
workflowInstancesStatusImages['completed'] = 'i_completed.png';
workflowInstancesStatusImages['aborted'] = 'i_aborted.png';

/* array que relaciona um status a um texto */
var workflowInstancesStatusText = Array();
workflowInstancesStatusText['active'] = 'Ativa';
workflowInstancesStatusText['exception'] = 'Exceção';
workflowInstancesStatusText['completed'] = 'Completa';
workflowInstancesStatusText['aborted'] = 'Abortada';

/**
 * Recebe os dados do Ajax e chama os métodos para construção da interface
 * @param array data Os dados retornados por Ajax
 * @return void
 */
function instances(data)
{
	if (_checkError(data))
		return;

	workflowInboxLightVersion = data['light'];
	workflowInstancesProcessesInfo = data['processesInfo'];
	workflowInstancesParams = data['params'];
	workflowInstancesActivityNames = data['activityNames'];
	workflowInstancesUserNames = data['userNames'];
	workflowInstancesProcesses = data['processes'];

	var information = $('workflowInstancesInformation');
	if (information)
		information.remove();

	var currentInstancesMenu = $('table_tools_instances');
	var currentInstancesElements = $('table_elements_instances');
	if (currentInstancesElements)
		currentInstancesElements.remove();

	if (currentInstancesMenu)
		currentInstancesMenu.remove();
	createInstancesMenu(data['instances'].length);
	if (data['instances'].length > 0)
	{
		createInstances(data['instances'], data['paging_links']);
	}
	else
	{
		var pagingContainer = $('td_tools_instances_3');
		if (pagingContainer)
			pagingContainer.innerHTML = '';
		if (workflowInstancesParams['active'] == '1')
			$('content_id_2').innerHTML += '<p class="text_dsp" id="workflowInstancesInformation">Nenhum registro ativo foi encontrado.</p>';
		else
			$('content_id_2').innerHTML += '<p class="text_dsp" id="workflowInstancesInformation">Nenhum registro encerrado foi encontrado.</p>';
	}
}

/**
 * Busca os dados, por Ajax, para a construção da interface
 * @param int p_page O número da página (quando houver paginação) que está sendo exibida
 * @param string sort A ordenação selecionada
 * @param int pid O ID do processo que se quer filtrar (ao utilizar 0 (zero), todos os processos serão exibidos
 * @param char active Indica se estão sendo exibidas as instâncias ativas ('1') ou inativas ('0')
 * @return void
 */
function draw_instances_folder(p_page, sort, pid, active)
{
	var p_page = (p_page == null) ? 0 : p_page;
	var sort = (sort == null) ? 0 : sort;
	var pid = (pid == null) ? 0 : pid;
	var active = (active == null) ? '1' : active;
	var params = 'sort=' + sort + '&pid=' + pid +'&active=' + active + '&p_page=' + p_page;
	cExecute('$this.bo_userinterface.instances', instances, params);
}

/**
 * Cria o menu da interface
 * @param int count O número de instâncias listadas
 * @return void
 */
function createInstancesMenu(count)
{
	var content = '<div id="instancesExtraContent"></div>';
	content += '<table id="table_tools_instances" width="100%">';
	content += '<td id="td_tools_instances_1" width="370">';
	content += '<ul class="horizontalMenu">';
	content += '<li><a href="javascript:workflowInstancesAlternate()">' + ((workflowInboxLightVersion) ? '' : '<img src="templateFile.php?file=images/alternate.png"/>&nbsp;') + 'Alternar</a></li>';
	if (count > 0)
	{
		content += '<li><a href="javascript:group_instances()">' + ((workflowInboxLightVersion) ? '' : '<img src="templateFile.php?file=images/group.png"/>&nbsp;') + 'Agrupar</a></li>';
		content += '<li id="instancesProcessFilterButton"><a href="javascript:showInstancesProcessFilter()">' + ((workflowInboxLightVersion) ? '' : '<img src="templateFile.php?file=images/filter.png"/>&nbsp;') + 'Filtrar por Processo...</a></li>';
	}
	content += '</ul>';
	content += '</td>';
	content += '<td id="td_tools_instances_2">';
	content += '</td>';
	content += '<td id="td_tools_instances_3" align="right"></td>';
	content += '</tr>';
	content += '</table>';

	$('content_id_2').innerHTML = content;
}

/**
 * Cria a tabela das instâncias
 * @param array data As instâncias que serão listadas
 * @param array paging Dados da paginação
 * @return void
 */
function createInstances(data, paging)
{
	var content = '';
	content += '<table id="table_elements_instances" cellpadding="2" class="inboxElements">';
	content += '<tr>';
	if (workflowInstancesParams['active'] == '0')
	{
		content += '<th width="13%" align="left">' + createInstancesSortingHeaders('Início Processo', 'wf_started') + '</th>';
		content += '<th width="13%" align="left">' + createInstancesSortingHeaders('Fim do Processo', 'wf_ended') + '</th>';
		content += '<th width="40%" align="left">' + createInstancesSortingHeaders('Processo', 'wf_procname') + '</th>';
		content += '<th width="22%" align="left">' + createInstancesSortingHeaders('Identificador', 'insname') + '</th>';
		content += '<th width="7%" align="left">Situação</th>';
		content += '<th width="5%" align="left">Ações</th>';
	}
	if (workflowInstancesParams['active'] == '1')
	{
		content += '<th width="15%" align="left">' + createInstancesSortingHeaders('Início da Atividade', 'wf_act_started') + '</th>';
		content += '<th width="20%" align="left">' + createInstancesSortingHeaders('Processo', 'wf_procname') + '</th>';
		content += '<th width="12%" align="left">' + createInstancesSortingHeaders('Identificador', 'insname') + '</th>';
		content += '<th width="21%" align="left">' + createInstancesSortingHeaders('Atividade', 'wf_name') + '</th>';
		content += '<th width="7%" align="left">Situação</th>';
		content += '<th width="20%" align="left">Atribuído a</th>';
		content += '<th width="5%" align="left">Ações</th>';
	}
	content += '</tr>';

	var instancesLimit = data.length;
	var current;
	for (var i = 0; i < instancesLimit; i++)
	{
		current = data[i];
		content += '<tr>';
		if (workflowInstancesParams['active'] == '0')
		{
			content += '<td>' + current['wf_started'] + '</td>';
			content += '<td>' + current['wf_ended'] + '</td>';
			if (current['viewRunAction'])
				content += '<td onclick="toggleHiddenView(\'instances\', ' + current['wf_instance_id'] + ', 0, ' + current['viewRunAction']['viewActivityID'] + ', ' + workflowInstancesProcessesInfo[current['wf_p_id']]['useHTTPS'] + ');" style="cursor: pointer;">';
			else
				content += '<td>';
			content += ((workflowInboxLightVersion) ? workflowInboxPriority[current['wf_priority']] : ('<img src="templateFile.php?file=images/pr' + current['wf_priority'] + '.png"/>&nbsp;'));
			content += workflowInstancesProcessesInfo[current['wf_p_id']]['name'] + '</td>';
			content += '<td>' + current['insname'] + '</td>';
			content += '<td align="center">' + ((workflowInboxLightVersion) ? workflowInstancesStatusText[current['wf_status']] : '<img src="templateFile.php?file=images/actions/' + workflowInstancesStatusImages[current['wf_status']] + '" alt="' + workflowInstancesStatusText[current['wf_status']] + '" title="' + workflowInstancesStatusText[current['wf_status']] + '"/>') + '</td>';
			content += '<td><a href="javascript:workflowInboxActionView(' + current['wf_instance_id'] + ', ' + current['wf_activity_id'] + ');">' + ((workflowInboxLightVersion) ? 'Visualizar' : '<img src="templateFile.php?file=images/actions/view.png" alt="visualizar" title="visualizar"/>') + '</a>';
		}
		if (workflowInstancesParams['active'] == '1')
		{
			content += '<td>' + current['wf_act_started'] + '</td>';

			if (current['viewRunAction'])
				content += '<td onclick="toggleHiddenView(\'instances\', ' + current['wf_instance_id'] + ', ' + current['wf_activity_id'] + ', ' + current['viewRunAction']['viewActivityID'] + ', ' + workflowInstancesProcessesInfo[current['wf_p_id']]['useHTTPS'] + ');" style="cursor: pointer;">' + workflowInstancesProcessesInfo[current['wf_p_id']]['name'] + '</td>';
			else
				content += '<td>' + workflowInstancesProcessesInfo[current['wf_p_id']]['name'] + '</td>';
			content += '<td>' + current['insname'] + '</td>';
			content += '<td>' + ((workflowInboxLightVersion) ? workflowInboxPriority[current['wf_priority']] : ('<img src="templateFile.php?file=images/pr' + current['wf_priority'] + '.png"/>&nbsp;'));
			content += workflowInstancesActivityNames[current['wf_activity_id']] + '</td>';
			content += '<td align="center">' + ((workflowInboxLightVersion) ? workflowInstancesStatusText[current['wf_status']] : '<img src="templateFile.php?file=images/actions/' + workflowInstancesStatusImages[current['wf_status']] + '" alt="' + workflowInstancesStatusText[current['wf_status']] + '" title="' + workflowInstancesStatusText[current['wf_status']] + '"/>') + '</td>';
			content += '<td>' + workflowInstancesUserNames[current['wf_user']] + '</td>';
			content += '<td><a href="javascript:workflowInboxActionView(' + current['wf_instance_id'] + ', ' + current['wf_activity_id'] + ');">' + ((workflowInboxLightVersion) ? 'Visualizar' : '<img src="templateFile.php?file=images/actions/view.png" alt="visualizar" title="visualizar"/>') + '</a>';
		}

		content += '</tr>';
		if (current['viewRunAction'])
			if (workflowInstancesParams['active'] == '0')
				content += constructHiddenView('instances', 6, current['wf_instance_id'], 0, current['viewRunAction']['height']);
			else
				content += constructHiddenView('instances', 7, current['wf_instance_id'],  current['wf_activity_id'], current['viewRunAction']['height']);
	}

	$('content_id_2').innerHTML += content;
	$('td_tools_instances_3').innerHTML = createInstancePagingLinks(paging);
}

/**
 * Cria os links de paginação
 * @param array paging Dados da paginação
 * @return string O código XHTML dos links de paginação
 */
function createInstancePagingLinks(pagingData)
{
	var output = '';
	if (pagingData)
	{
		var pagingSize = pagingData.length;
		for (var i = 0; i < pagingSize; i++)
		{
			if (pagingData[i].do_link == true)
				output += '<a href="javascript:draw_instances_folder(' + pagingData[i].p_page + ', \'' + workflowInstancesParams['sort'] + '\', ' + workflowInstancesParams['pid'] + ', \'' + workflowInstancesParams['active'] + '\');">' + pagingData[i].name + '</a>&nbsp;';
			else
				output += '<strong>' + pagingData[i].name + '</strong>&nbsp;';
		}
	}

	return output;
}

/**
 * Cria os links para ordenação das instâncias
 * @param string O texto do link
 * @param string A ordenação esperada
 * @return string O link criado
 */
function createInstancesSortingHeaders(text, expectedSort)
{
	var currentSort = workflowInstancesParams['sort'].split('__');
	var theSame = (expectedSort == currentSort[0]);
	direction = false;

	var output = '';
	if (theSame)
	{
		output += '<strong>';
		direction = (currentSort[1] == 'ASC');
	}

	output += '<a href="javascript:sortInstances(\'' + expectedSort + '__' + (direction ? 'DESC' : 'ASC') + '\');">' + text + '</a>';

	if (theSame)
		output += '<img src="templateFile.php?file=images/arrow_' + (direction ? 'ascendant' : 'descendant') + '.gif"/></strong>';

	return output;
}

/**
 * Alterna entre a exibição de instâncias ativas e finalizadas
 * @return void
 */
function workflowInstancesAlternate()
{
	if (workflowInstancesParams['active'] == '1')
		draw_instances_folder(0, workflowInstancesParams['sort'], workflowInstancesParams['pid'], '0');
	else
		draw_instances_folder(0, workflowInstancesParams['sort'], workflowInstancesParams['pid'], '1');
}

/**
 * Ordena as instâncias de acordo com o critério informado
 * @param string sort O campo/sentido utilizado na ordenação
 * @return void
 */
function sortInstances(sort)
{
	draw_instances_folder(workflowInstancesParams['p_page'], sort, workflowInstancesParams['pid'], workflowInstancesParams['active']);
}

/**
 * Cria e exibe o menu listando os processos das instâncias que o usuário iniciou
 * @return void
 */
function showInstancesProcessFilter()
{
	/* se o menu já existe, apenas o exibe */
	if ($('instancesProcessFilter'))
	{
		$('instancesExtraContent').style.display = '';
		return;
	}

	/* coleta informações sobre posicionamento */
	var li = $('instancesProcessFilterButton');
	var offset = Position.cumulativeOffset(li);
	var height = li.getHeight() - 1;

	/* cria as opções do menu */
	var content = '<ul id="instancesProcessFilter" onmouseover="$(\'instancesExtraContent\').style.display = \'\'" onmouseout="$(\'instancesExtraContent\').style.display = \'none\'" class="submenu" style="top: ' + (offset[1] + height) + 'px; left: ' + offset[0] + 'px;">';
	/* insere manualmente a linha para exibir todos os processos */
	content += '<li><a href="javascript:$(\'instancesExtraContent\').style.display = \'none\';filterProcess(0)">Todos</a></li>';
	var size = workflowInstancesProcesses.length;
	for (var i = 0; i < size; i++)
		content += '<li><a href="javascript:$(\'instancesExtraContent\').style.display = \'none\';filterProcess(' + workflowInstancesProcesses[i].pid +')">' + workflowInstancesProcesses[i].name + '</a></li>';
	content += '</ul>';

	/* insere o novo conteúdo */
	$('instancesExtraContent').innerHTML += content;
}

function filterProcess(pid)
{
	draw_instances_folder(workflowInstancesParams['p_page'], workflowInstancesParams['sort'], pid, workflowInstancesParams['active']);
}

function ConfigMenuStyle_instances(m, max)
{
	m.SetPosition('relative',0,0);
	m.SetCorrection(1,-5);
	m.SetCellSpacing(0);
	m.SetBackground('whitesmoke','','','');
	m.SetItemText('black','center','','','');
	m.SetItemBorder(1,'buttonface','solid');
	m.SetItemTextHL('darkblue','center','','','');
	m.SetItemBackgroundHL('white','','','');
	m.SetItemBorderHL(1,'black','solid');
	m.SetItemTextClick('white','center','','','');
	m.SetItemBackgroundClick('darkblue','','','');
	m.SetItemBorderClick(1,'black','solid');
	m.SetBorder(0,'navy','solid');

	m._pop.SetCorrection(4,1);
	m._pop.SetItemDimension(max * 7 + 30,22);
	m._pop.SetPaddings(1);
	m._pop.SetBackground('white','','','');
	m._pop.SetSeparator(150,'left','black','');
	m._pop.SetExpandIcon(true,'>',9);
	m._pop.SetItemBorder(0,'#66CCFF','solid');
	m._pop.SetItemBorderHL(0,'black','solid');
	m._pop.SetItemPaddings(0);
	m._pop.SetItemPaddingsHL(0);
	m._pop.SetItemText('black','','','','');
	m._pop.SetItemTextHL('darkblue','','','','');
	m._pop.SetItemBackground('white','','','');
	m._pop.SetItemBackgroundHL('whitesmoke','','','');
}
