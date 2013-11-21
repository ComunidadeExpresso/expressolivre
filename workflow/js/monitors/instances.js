var workflowMonitorUserMapping;
var workflowMonitorActivityMapping;
var sortColumn = 2;
var order = 'A';
var workflowMonitorInstancesParams = null;
var completedSortColumn = 1;
var completedOrder = 'A';

var workflowMonitorCurrentList = '';

/* retorno do Ajax para listagem de instâncias */
function instanceList(data)
{
	if (handleError(data))
	{
		/* salva algumas informações vindas da chamada Ajax */
		workflowMonitorInstancesParams = data['params'];
		workflowMonitorUserMapping = data['userMapping'];
		workflowMonitorActivityMapping = data['activityMapping'];


		if (!workflowMonitorInstancesParams['filters'])
		{
			$('divInstance').innerHTML = '';
		}
		else
		{
			var objectRemovalList = new Array();
			objectRemovalList[0] = $('monitorMessage');
			objectRemovalList[1] = $('instancesTable');
			objectRemovalList[2] = $('pagingTop');
			objectRemovalList[3] = $('pagingBottom');
			for (var i = 0; i < objectRemovalList.length; i++)
				if (objectRemovalList[i])
					objectRemovalList[i].remove();
		}

		if (data['data'].length == 0)
		{
			var divInstance = $('divInstance');
			if (workflowMonitorInstancesParams['filters'])
			{
				new Insertion.Bottom(divInstance, '<p id="monitorMessage" style="clear: both;">Nenhuma instância satisfaz o critério de filtragem utilizado</p>');
				$('instanceCount').innerHTML = '0';
			}
			else
				divInstance.innerHTML = '<p>Este processo não possui instâncias ativas</p>';
		}
		else
			drawInstancesList(data);
	}
}

/* retorno do Ajax para listagem de usuários */
function userList(data)
{
	if (handleError(data))
	{
		if (data['data'].length == 0)
		{
			var parag = document.createElement("P");
			parag.className = "text_dsp";
			parag.innerHTML = "Não existem usuários";
			divInstance.appendChild(parag);
		}
		else
		{
			var aid = data['params']['aid'];
			var pid = data['params']['pid'];
			var uid = data['params']['uid'];
			var iid = data['params']['iid'];
			var message = document.getElementById("nu_" + iid);
			var td = message.parentNode;
			td.innerHTML = constructSelectBox("nu_" + iid, data['data'], uid);
			td.innerHTML += "<button onclick=\"updateUser(" + iid + ", " + aid + ", " + pid + "); return false;\" class=\"ok\">OK</button>";
		}
	}
}

/* retorno do Ajax para listagem de atividades */
function activityList(data)
{
	if (handleError(data))
	{
		if (data['data'].length == 0)
		{
			var parag = document.createElement("P");
			parag.className = "text_dsp";
			parag.innerHTML = "Não existem atividades";
			divInstance.appendChild(parag);
		}
		else
		{
			var aid = data['params']['aid'];
			var pid = data['params']['pid'];
			var iid = data['params']['iid'];
			var message = document.getElementById("na_" + iid);
			var td = message.parentNode;
			td.innerHTML = constructSelectBox("na_" + iid, data['data'], aid);
			td.innerHTML += "<button onclick=\"updateActivity(" + iid + ", " + pid + "); return false;\" class=\"ok\">OK</button>";
		}
	}
}

/**** LISTA DE INSTÂNCIAS DE UM PROCESSO ****/
function callInstanceList(pid, srt, p_page, p_filters)
{
	workflowMonitorCurrentList = 'active';
	var params = 'pid=' + pid;

	if (srt == null)
		srt = sortColumn;

	params += '&srt=' + srt;

	if ((srt == sortColumn) && (p_page == null))
		order = (order == 'A') ? 'D' : 'A';
	params += '&ord=' + ((order == 'A') ? '__ASC' : '__DESC');

	if (p_page)
		params += '&p_page=' + p_page;

	sortColumn = srt;

	if (p_filters)
		params += '&filters=' + p_filters;

	cExecute('$this.bo_monitors.listInstances', instanceList, params);
}

function resultCompletedInstanceList(data)
{
	if (handleError(data))
	{
		/* salva algumas informações vindas da chamada Ajax */
		workflowMonitorInstancesParams = data['params'];
		workflowMonitorUserMapping = data['userMapping'];

		if (!workflowMonitorInstancesParams['filters'])
		{
			$('divInstance').innerHTML = '';
		}
		else
		{
			var objectRemovalList = new Array();
			objectRemovalList[0] = $('monitorMessage');
			objectRemovalList[1] = $('instancesTable');
			objectRemovalList[2] = $('pagingTop');
			objectRemovalList[3] = $('pagingBottom');
			for (var i = 0; i < objectRemovalList.length; i++)
				if (objectRemovalList[i])
					objectRemovalList[i].remove();
		}

		if (data['data'].length == 0)
		{
			var divInstance = $('divInstance');
			if (workflowMonitorInstancesParams['filters'])
			{
				$('instanceCount').innerHTML = '0';
				new Insertion.Bottom(divInstance, '<p id="monitorMessage" style="clear: both;">Nenhuma instância satisfaz o critério de filtragem utilizado</p>');
			}
			else
				divInstance.innerHTML = '<p>Este processo não possui instâncias finalizadas</p>';
		}
		else
			drawCompletedInstancesList(data);
	}
}

function drawCompletedInstancesList(data)
{
	var instances = data['data'];
	var pid = data['params']['pid'];
	var pagingData = data['pagingData'];

	var divInstance = $('divInstance');

	var menuCreated = false;
	var content = '';

	if (divInstance.innerHTML == '')
	{
		menuCreated = true;
		content += '<h2>Instâncias Finalizadas</h2>';
		content += '<div id="monitorMenu">';
		content += '<ul class="horizontalMenu" id="filterMenu" align="center">';
		content += '<li><a href="javascript:addFilterSelection(true);"><img src="workflow/templateFile.php?file=images/filter_add.png"/>&nbsp;Adicionar Filtro</a></li>';
		content += '<li><a href="javascript:filterInstances(true);"><img src="workflow/templateFile.php?file=images/filter.png"/>&nbsp;Filtrar</a></li>';
		if (permissions[pid]['bits'][IP_REMOVE_COMPLETED_INSTANCES])
			content += '<li><a href="javascript:void(0)" onclick="removeCompletedInstances();"><img src="workflow/templateFile.php?file=images/del_template.png"/>&nbsp;Remover Instâncias</a></li>';
		content += '</ul>';
		content += '<span style="float: left; font-size: 11px !important; height: 2.5em; line-height: 2.5em;">&nbsp;&nbsp;&nbsp;<strong>Total de Instâncias:</strong> <span id="instanceCount">' + data['instanceCount'] + '</span></span>';
		content += '<br/><br/><br/>';
		content += '</div>';
	}
	else
	{
		$('instanceCount').innerHTML = data['instanceCount'];
	}

	var pagingDataCount = pagingData.length;
	var pagingLinks = '';
	for (var i = 0; i < pagingDataCount; i++)
	{
		if (pagingData[i].do_link == true)
			pagingLinks += '<a href="javascript:monitorPaginateInstances(' + pagingData[i].p_page + ');">' + pagingData[i].name + '</a>&nbsp;';
		else
			pagingLinks += '<strong>' + pagingData[i].name + '</strong>&nbsp;';
	}

	content += '<div align="right" id="pagingTop">' + pagingLinks + '</div>';
	content += '<table width="100%" align="center" border="1" class="content_table" id="instancesTable" style="clear: both;">';
	content += '<tr><th><span onClick="monitorSortInstances(1);" style="cursor:pointer;">ID</span></th><th><span onClick="monitorSortInstances(2);" style="cursor:pointer;">Identificador</span></th><th><span onClick="monitorSortInstances(3);" style="cursor:pointer;">Proprietário</span></th><th><span onClick="monitorSortInstances(4);" style="cursor:pointer;">Pri.</span></th><th><span onClick="monitorSortInstances(5);" style="cursor:pointer;">Data Início</span></th><th><span onClick="monitorSortInstances(6);" style="cursor:pointer;">Data Fim</span></th><th><span onClick="monitorSortInstances(7);" style="cursor:pointer;">Status</span></th><th>Ações</th></tr>';

	/*** gera a lista de instâncias ****/
	var instanceCount = instances.length;
	var ownerName = '';
	for (var i = 0; i < instanceCount; i++)
	{
		if (workflowMonitorUserMapping[instances[i]['wf_owner']])
			ownerName = workflowMonitorUserMapping[instances[i]['wf_owner']];
		else
			ownerName = 'ID: ' + instances[i]['wf_owner'];

		if (!instances[i]['wf_instance_name'])
			instances[i]['wf_instance_name'] = '';

		content += '<tr>';

		/* id da instância */
		content += '<td>' + instances[i]['wf_instance_id'] + '</td>';

		/* identificador da instância */
		content += '<td>' + instances[i]['wf_instance_name'] + '</td>';

		/* proprietário da instância */
		content += '<td>' + ownerName + '</td>';

		/* prioridade da instância */
		content += '<td>' + instances[i]['wf_priority'] + '</td>';

		/* data início da instância */
		content += '<td>' + instances[i]['wf_started'] + '</td>';

		/* data fim da instância */
		content += '<td>' + instances[i]['wf_ended'] + '</td>';

		/* status da instância */
		content += '<td>' + statusQuickTranslation[instances[i]['wf_status']] + '</td>';

		/* ações da instância */
		content += '<td><a href="#" onclick="workflowInboxActionView(' + instances[i]['wf_instance_id'] + ', null); return false;">visualizar</a></td>';

		content += '</tr>';
	}

	content += '</table>';
	content += '<div align="right" id="pagingBottom">' + pagingLinks + '</div>';
	new Insertion.Bottom(divInstance, content);
	if (menuCreated)
		lb_initialize();
}

function callCompletedInstanceList(pid, sort, p_page, p_filters)
{
	workflowMonitorCurrentList = 'completed';
	var params = 'pid=' + pid;

	if (sort == null)
		sort = 1;

	params += '&sort=' + sort;

	if ((sort == completedSortColumn) && (p_page == null))
		completedOrder = (completedOrder == 'A') ? 'D' : 'A';
	params += '&ord=' + ((completedOrder == 'A') ? '__ASC' : '__DESC');

	if (p_page)
		params += '&p_page=' + p_page;

	completedSortColumn = sort;

	if (p_filters)
		params += '&filters=' + p_filters;

	cExecute('$this.bo_monitors.listCompletedInstances', resultCompletedInstanceList, params);
}

function drawInstancesList(data)
{
	var instances = data['data'];
	var pid = data['params']['pid'];
	var pagingData = data['pagingData'];

	var divInstance = $('divInstance');

	var menuCreated = false;
	var content = '';

	if (divInstance.innerHTML == '')
	{
		menuCreated = true;
		content += '<h2>Instâncias Ativas</h2>';
		content += '<div id="monitorMenu">';
		content += '<ul class="horizontalMenu" id="filterMenu" align="center">';
		content += '<li><a href="javascript:addFilterSelection(false);"><img src="workflow/templateFile.php?file=images/filter_add.png"/>&nbsp;Adicionar Filtro</a></li>';
		content += '<li><a href="javascript:filterInstances(false);"><img src="workflow/templateFile.php?file=images/filter.png"/>&nbsp;Filtrar</a></li>';
		if (permissions[pid]['bits'][IP_SEND_EMAILS])
			content += '<li><a href="javascript:void(0)" onclick="sendMailConfig();" class="lbOn"><img src="workflow/templateFile.php?file=images/mail_new.png"/>&nbsp;Enviar E-mail</a></li>';
		content += '</ul>';
		content += '<span style="float: left; font-size: 11px !important; height: 2.5em; line-height: 2.5em;">&nbsp;&nbsp;&nbsp;<strong>Total de Instâncias:</strong> <span id="instanceCount">' + data['instanceCount'] + '</span></span>';
		content += '<br/><br/><br/>';
		content += '</div>';
	}
	else
	{
		$('instanceCount').innerHTML = data['instanceCount'];
	}

	var pagingDataCount = pagingData.length;
	var pagingLinks = '';
	for (var i = 0; i < pagingDataCount; i++)
	{
		if (pagingData[i].do_link == true)
			pagingLinks += '<a href="javascript:monitorPaginateInstances(' + pagingData[i].p_page + ');">' + pagingData[i].name + '</a>&nbsp;';
		else
			pagingLinks += '<strong>' + pagingData[i].name + '</strong>&nbsp;';
	}

	content += '<div align="right" id="pagingTop">' + pagingLinks + '</div>';
	content += '<table width="100%" align="center" border="1" class="content_table" id="instancesTable" style="clear: both;">';
	content += '<tr><th><span onClick="monitorSortInstances(1);" style="cursor:pointer;">ID</span></th><th><span onClick="monitorSortInstances(2);" style="cursor:pointer;">Atividade</span></th><th><span onClick="monitorSortInstances(3);" style="cursor:pointer;">Identificador</span></th><th><span onClick="monitorSortInstances(4);" style="cursor:pointer;">Pri.</span></th><th><span onClick="monitorSortInstances(5);" style="cursor:pointer;">Usuário</span></th><th><span onClick="monitorSortInstances(6);" style="cursor:pointer;">Status</span></th><th>Ações</th></tr>';

	/*** gera a lista de instâncias ****/
	var instanceCount = instances.length;
	for (var i = 0; i < instanceCount; i++)
	{
		content += '<tr>';
		/* id da instância */
		content += '<td>' + instances[i]['wf_instance_id'] + '</td>';

		/* atividade da instância */
		content += '<td>';
		if (permissions[pid]['bits'][IP_CHANGE_ACTIVITY])
			content += "<a href=\"\" onclick=\"clickActivity(this, " + instances[i]['wf_p_id']  + ", " + instances[i]['wf_activity_id']  + ", " + instances[i]['wf_instance_id'] + "); return false;\">" + workflowMonitorActivityMapping[instances[i]['wf_activity_id']] + "</a>";
		else
			content += workflowMonitorActivityMapping[instances[i]['wf_activity_id']];
		content += '</td>';

		/* nome da instância */
		if (!instances[i]['wf_instance_name'])
			instances[i]['wf_instance_name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		content += '<td>';
		if (permissions[pid]['bits'][IP_CHANGE_NAME])
			content += "<a href=\"\" onclick=\"clickName(this, " + instances[i]['wf_instance_id'] + ", " + instances[i]['wf_p_id'] + "); return false;\">" + instances[i]['wf_instance_name']  + "</a>";
		else
			content += instances[i]['wf_instance_name'];
		content += '</td>';

		/* prioridade da instância */
		content += '<td>';
		if (permissions[pid]['bits'][IP_CHANGE_PRIORITY])
			content += "<a href=\"\" onclick=\"clickPriority(this, " + instances[i]['wf_instance_id']  + ", " + instances[i]['wf_p_id'] + "); return false;\">" + instances[i]['wf_priority'] + "</a>";
		else
			content += instances[i]['wf_priority'];
		content += '</td>';

		/* usuário da instância */
		if (workflowMonitorUserMapping[instances[i]['wf_user']])
			userName = workflowMonitorUserMapping[instances[i]['wf_user']];
		else
			userName = 'ID: ' + instances[i]['wf_user'];
		content += '<td>';
		if (permissions[pid]['bits'][IP_CHANGE_USER])
			content += "<a href=\"\" onclick=\"clickUser(this, " + instances[i]['wf_p_id']  + ", " + instances[i]['wf_activity_id']  + ", " + instances[i]['wf_instance_id'] + ", '" + ((instances[i]['wf_user'] == '*') ? '-1' : instances[i]['wf_user']) + "'); return false;\">" + userName + "</a>";
		else
			content += userName;
		content += '</td>';

		/* staus da instância */
		content += '<td>';
		if (permissions[pid]['bits'][IP_CHANGE_STATUS])
			content += "<a href=\"\" onclick=\"clickStatus(this, " + instances[i]['wf_instance_id'] + ", " + instances[i]['wf_p_id'] + ", '" + instances[i]['wf_status'] + "'); return false;\">" + statusQuickTranslation[instances[i]['wf_status']] + "</a>";
		else
			content += statusQuickTranslation[instances[i]['wf_status']];
		content += '</td>';

		instances[i]['wf_actions'] = '';
		/* ações da instância */
		if ((permissions[pid]['bits'][IP_VIEW_PROPERTIES]) || (permissions[pid]['bits'][IP_CHANGE_PROPERTIES]))
			instances[i]['wf_actions'] += '<a href="#" onclick="editProperties(' + instances[i]['wf_instance_id'] + ', ' + instances[i]['wf_p_id'] + '); return false;">propriedades</a>&nbsp;';

		instances[i]['wf_actions'] += '<a href="#" onclick="workflowInboxActionView(' + instances[i]['wf_instance_id'] + ', null); return false;">visualizar</a>';

		content += '<td>';
		if (!instances[i]['wf_actions'])
			content += "&nbsp;";
		else
			content += instances[i]['wf_actions'];
		content += '</td>';

		content += '</tr>';
	}

	content += '</table>';
	content += '<div align="right" id="pagingBottom">' + pagingLinks + '</div>';
	new Insertion.Bottom(divInstance, content);
	if (menuCreated)
		lb_initialize();
}

/**** AÇÕES DE ATUALIZAÇÃO DA INSTÂNCIA ****/
function clickPriority(link, iid, pid)
{
	var td = link.parentNode;
	var previousValue = link.innerHTML;
	var items = new Array();
	for (var i = 0; i < 5; i++)
	{
		items[i] = new Array();
		items[i]['id'] = i;
		items[i]['name'] = i;
	}
	td.innerHTML = constructSelectBox('np_' + iid, items, link.innerHTML);
	td.innerHTML += "<button onclick=\"updatePriority(" + iid + ", " + pid + "); return false;\" class=\"ok\">OK</button>";
}

function updatePriority(iid, pid)
{
	var selectBox = document.getElementById('np_' + iid);
	var np = selectBox.value;
	cExecute ("$this.bo_monitors.updatePriority", updatePriorityResult, 'iid=' + iid + '&pid=' + pid + '&np=' + np);
	selectBox.parentNode.innerHTML = "<p id=\"np_" + iid  + "\" class=\"ajax_message\"> (atualizando) </p>";
}

function updatePriorityResult(data)
{
	if (handleError(data))
	{
		var message = document.getElementById('np_' + data['iid']);
		message.parentNode.innerHTML = "<a href=\"\" onclick=\"clickPriority(this, " + data['iid']  + ", " + data['pid']  + "); return false;\">" + data['np'] + "</a>";
	}
}

function clickUser(link, pid, aid, iid, uid)
{
	link.parentNode.innerHTML = "<p id=\"nu_" + iid + "\" class=\"ajax_message\"> (carregando lista) </p>";
	cExecute ("$this.bo_monitors.listUsers", userList, 'pid=' + pid + '&aid=' + aid + '&iid=' + iid  + '&uid=' + uid);
}

function updateUser(iid, aid, pid)
{
	var selectBox = document.getElementById("nu_" + iid);
	var uid = selectBox.value;
	cExecute ("$this.bo_monitors.updateUser", updateUserResult, 'iid=' + iid + '&user=' + uid + '&aid=' + aid + '&pid=' + pid);
	selectBox.parentNode.innerHTML = "<p id=\"nu_" + iid + "\" class=\"ajax_message\"> (atualizando) </p>";
}

function updateUserResult(data)
{
	if (handleError(data))
	{
		var message = document.getElementById('nu_' + data['iid']);
		message.parentNode.innerHTML = "<a href=\"\" onclick=\"clickUser(this, " + data['pid']  + ", " + data['aid']  + ", " + data['iid'] + ", " + data['user']  + "); return false;\">" + data['fullname'] + "</a>";
	}
}

function clickActivity(link, pid, aid, iid)
{
	link.parentNode.innerHTML = "<p id=\"na_" + iid + "\" class=\"ajax_message\"> (carregando lista) </p>";
	cExecute ("$this.bo_monitors.listActivities", activityList, 'pid=' + pid + '&aid=' + aid + '&iid=' + iid);
}

function updateActivity(iid, pid)
{
	var selectBox = document.getElementById("na_" + iid);
	var aid = selectBox.value;
	cExecute ("$this.bo_monitors.updateActivity", updateActivityResult, 'iid=' + iid + '&aid=' + aid + '&pid=' + pid);
	selectBox.parentNode.innerHTML = "<p id=\"na_" + iid + "\" class=\"ajax_message\"> (atualizando) </p>";
}

function updateActivityResult(data)
{
	if (handleError(data))
	{
		var message = document.getElementById('na_' + data['iid']);
		message.parentNode.innerHTML = "<a href=\"\" onclick=\"clickActivity(this, " + data['pid']  + ", " + data['aid']  + ", " + data['iid'] + "); return false;\">" + data['name'] + "</a>";
	}
}

function clickStatus(link, iid, pid, selected)
{
	var td = link.parentNode;
	td.innerHTML = constructSelectBox('np_' + iid, statusCorrelation, selected);
	td.innerHTML += "<button onclick=\"updateStatus(" + iid + ", " + pid + "); return false;\" class=\"ok\">OK</button>";
}

function updateStatus(iid, pid)
{
	var selectBox = document.getElementById('np_' + iid);
	cExecute ("$this.bo_monitors.updateStatus", updateStatusResult, 'iid=' + iid + '&pid=' + pid + '&ns=' + selectBox.value);
	selectBox.parentNode.innerHTML = "<p id=\"np_" + iid  + "\" class=\"ajax_message\"> (atualizando) </p>";
}

function updateStatusResult(data)
{
	if (handleError(data))
	{
		var message = document.getElementById('np_' + data['iid']);
		if (data['ns'] == 'aborted')
			message.parentNode.parentNode.remove();
		else
			message.parentNode.innerHTML = "<a href=\"\" onclick=\"clickStatus(this, " + data['iid'] + ", " + data['pid'] + ", '" + data['ns'] + "'); return false;\">" + statusQuickTranslation[data['ns']] + "</a>";
	}
}

function clickName(link, iid, pid)
{
	var td = link.parentNode;
	if (link.innerHTML == "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;")
		link.innerHTML = "";
	td.innerHTML = '<input type="text" value="' + link.innerHTML + '" id="nn_' + iid + '"/>';
	td.innerHTML += "<button onclick=\"updateName(" + iid + ", " + pid + "); return false;\" class=\"ok\">OK</button>";
	td.childNodes[0].focus();
	td.childNodes[0].select();
}

function updateName(iid, pid)
{
	var text = document.getElementById("nn_" + iid);
	cExecute ("$this.bo_monitors.updateName", updateNameResult, 'iid=' + iid + '&pid=' + pid + '&nn=' + escape(text.value));
	text.parentNode.innerHTML = "<p id=\"nn_" + iid + "\" class=\"ajax_message\"> (atualizando) </p>";
}

function updateNameResult(data)
{
	if (handleError(data))
	{
		var message = document.getElementById('nn_' + data['iid']);
		if (data['nn'] == "")
			data['nn'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		message.parentNode.innerHTML = "<a href=\"\" onclick=\"clickName(this, " + data['iid'] + ", " + data['pid'] + "); return false;\">" + data['nn'] + "</a>";
	}
}

function editProperties(iid, pid)
{
	var border_id = create_border("Propriedades - ID: " + iid);
	elem = $("content_id_" + border_id);
	loadProperties(iid, pid, elem);
}

function monitorPaginateInstances(p_page)
{
	if (workflowMonitorCurrentList == 'active')
		callInstanceList(workflowMonitorInstancesParams['pid'], workflowMonitorInstancesParams['str'], p_page, workflowMonitorInstancesParams['filters']);
	else
		callCompletedInstanceList(workflowMonitorInstancesParams['pid'], workflowMonitorInstancesParams['str'], p_page, workflowMonitorInstancesParams['filters']);
}

function monitorSortInstances(sort)
{
	if (workflowMonitorCurrentList == 'active')
		callInstanceList(workflowMonitorInstancesParams['pid'], sort, null, workflowMonitorInstancesParams['filters']);
	else
		callCompletedInstanceList(workflowMonitorInstancesParams['pid'], sort, null, workflowMonitorInstancesParams['filters']);
}

function loadInstances(pid)
{
	for (var i = 0; i < filters.length; i++)
		filters[i] = null;
	filters = new Array();
	callInstanceList(pid, null, 0);
}

function loadCompletedInstances(pid)
{
	for (var i = 0; i < filters.length; i++)
		filters[i] = null;
	filters = new Array();
	callCompletedInstanceList(pid, null, 0);
}

function loadInconsistentInstances(pid)
{
	cExecute('$this.bo_monitors.loadInconsistentInstances', resultInconsistentInstances, 'pid=' + pid);
}

function resultInconsistentInstances(data)
{
	var instances = data['instances'];
	var names = data['names'];
	var divInstance = $('divInstance');
	var content = '';

	content += '<h2>Instâncias Inconsistentes</h2>';

	for (var i = 0; i < instances.length; i++)
	{
		content += '<br/><br/><h2 style="text-align: left;">' + instances[i]['name'] + '</h2>';
		content += '<p style="text-align: left;">' + instances[i]['description'] + '</p>';
		var instanceList = instances[i]['items'];
		if (instanceList.length == 0)
		{
			content += '<p style="font-weight: bold; font-size: 120%;">Nenhuma ocorrência encontrada.</p>';
			continue;
		}
		content += '<table width="100%" align="center" border="1" class="content_table" id="instancesTable" style="clear: both;">';
		content += '<tr><th>ID</th><th>Atividade</th><th>Identificador</th><th>Pri.</th><th>Usuário</th><th>Status</th></tr>';
		for (var j = 0; j < instanceList.length; j++)
		{
			if (!instanceList[j]['wf_instance_name'])
				instanceList[j]['wf_instance_name'] = '';
			content += '<tr>';
			content += '<td>' + instanceList[j]['wf_instance_id'] + '</td>';
			content += '<td>' + instanceList[j]['wf_activity_name'] + '</td>';
			content += '<td>' + instanceList[j]['wf_instance_name'] + '</td>';
			content += '<td>' + instanceList[j]['wf_priority'] + '</td>';
			content += '<td>' + names[instanceList[j]['wf_user']] + '</td>';
			content += '<td>' + statusQuickTranslation[instanceList[j]['wf_status']] + '</td>';
			content += '</tr>';
		}
		content += '</table>';
	}

	divInstance.innerHTML = content;
}
