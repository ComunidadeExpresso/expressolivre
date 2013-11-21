activityStatusTranslation = new Array();
activityStatusTranslation['running'] = 'Em execução';
activityStatusTranslation['completed'] = 'Completada';

instanceStatusTranslation = new Array();
instanceStatusTranslation['completed'] = 'Completada';
instanceStatusTranslation['active'] = 'Ativa';
instanceStatusTranslation['aborted'] = 'Abortada';
instanceStatusTranslation['exception'] = 'Em exceção';

workflowInboxPriority = new Array();
workflowInboxPriority[0] = '<font color="#dedede"><b>&bull;</b></font>&nbsp;';
workflowInboxPriority[1] = '<font color="#7ec65b"><b>&bull;</b></font>&nbsp;';
workflowInboxPriority[2] = '<font color="#efea4e"><b>&bull;</b></font>&nbsp;';
workflowInboxPriority[3] = '<font color="#fc9e34"><b>&bull;</b></font>&nbsp;';
workflowInboxPriority[4] = '<font color="#e31b23"><b>&bull;</b></font>&nbsp;';

var workflowCommonExpressoIndexPath = null;

/* abre uma nova aba para mostrar os dados da instancia */
function drawViewInstance(data)
{
	var borderID = create_border("Visualizar Instância");

	var content = '<br/>';
	content += '<table class="info_table" width="90%" align="center">';
	content += '<tr class="info_tr_header"><td colspan="5">Instância de ' + data['wf_procname'] + '(v' + data['wf_version'] + ')</td></tr>';
	content += '<tr class="info_tr_sub_header">';
	content += '<td width="19%">Criado em</td>';
	content += '<td width="19%">Finalizado em</td>';
	content += '<td width="10%">Prioridade</td>';
	content += '<td width="13%">Situação</td>';
	content += '<td width="39%">Proprietário</td>';
	content += '</tr>';
	content += '<tr class="info_tr_simple">';
	content += '<td>' + data['wf_started'] + '</td>';
	content += '<td>' + data['wf_ended'] + '</td>';
	content += '<td align="center">' + data['wf_priority'] + '</td>';
	content += '<td>' + instanceStatusTranslation[data['wf_status']] + '</td>';
	content += '<td>' + data['wf_owner'] + '</td>';
	content += '</tr>';
	content += '</table>';

	content += '<br/>';
	content += '<table class="info_table" width="90%" align="center">';
	content += '<tr class="info_tr_header"><td colspan="4">Histórico</td></tr>';
	content += '<tr class="info_tr_sub_header">';
	content += '<td width="30%">Atividade</td>';
	content += '<td width="20%">Iniciado em</td>';
	content += '<td width="20%">Duração</td>';
	content += '<td width="30%">Usuário</td>';
	content += '</tr>';

	var current;
	var workitemCount = data['wf_workitems'].length;
	for (var i = 0; i < workitemCount; i++)
	{
		current = data['wf_workitems'][i];
		content += '<tr class="info_tr_simple">';
		content += '<td>' + activity_icon(current['wf_type'], current['wf_is_interactive']) + " " + current['wf_name'];
		content += '<td>' + current['wf_started'] + '</td>';
		content += '<td>' + current['wf_duration'] + '</td>';
		content += '<td>' + current['wf_user'] + '</td>';
		content += '</tr>';
	}
	content += '</table>';

	if ((data['wf_status'] == 'active') || (data['wf_status'] == 'exception'))
	{
		content += '<br/>';
		content += '<table class="info_table" width="90%" align="center">';
		content += '<tr class="info_tr_header"><td colspan="4">Atividades em Andamento</td></tr>';
		content += '<tr class="info_tr_sub_header">';
		content += '<td width="30%">Nome</td>';
		content += '<td width="20%">Iniciado em</td>';
		content += '<td width="20%">Situação</td>';
		content += '<td width="30%">Usuário</td>';
		content += '</tr>';

		var current;
		var activityCount = data['wf_activities'].length;
		for (var i = 0; i < activityCount; i++)
		{
			current = data['wf_activities'][i];
			content += '<tr class="info_tr_simple">';
			content += '<td>' + activity_icon(current['wf_type'], current['wf_is_interactive']) + " " + current['wf_name'];
			content += '<td>' + current['wf_started'] + '</td>';
			content += '<td>' + activityStatusTranslation[current['wf_status']] + '</td>';
			content += '<td>' + ((current['wf_user']) ? current['wf_user'] : '*') + '</td>';
			content += '</tr>';
		}

		content += '</table>';
	}

	if(data['wf_properties'] != null)
	{
		content += '<br/>';
		content += '<table class="info_table" width="90%" align="center">';
		content += '<tr class="info_tr_header"><td colspan="2">Propriedades</td></tr>';
		content += '<tr class="info_tr_sub_header">';
		content += '<td width="30%">Nome</td>';
		content += '<td width="70%">Valor</td>';
		content += '</tr>';

		var propertiesCount = data['wf_properties']['keys'].length;
		for (var i = 0; i < propertiesCount; i++)
		{
			content += '<tr class="info_tr_simple">';
			content += '<td>' + data['wf_properties']['keys'][i] + '</td>';
			content += '<td>' + data['wf_properties']['values'][i] + '</td>';
			content += '</tr>';
		}
		content += '</table>';
	}

	if (data['viewRunAction'])
	{
		var viewActivity = data['viewRunAction'];
		content += '<br/>';
		content += '<table class="info_table" width="90%" align="center">';
		content += '<tr class="info_tr_header"><td>Atividade View do Processo</td></tr>';
		content += '<tr class="info_tr_simple">';
		content += '<td width="100%"><iframe src="' + getInstanceURL(data['wf_instance_id'], viewActivity['viewActivityID'], viewActivity['useHTTPS']) + '" width="100%" ' + ((viewActivity['height'] > 0) ? ' height="' + viewActivity['height'] + '"' : '') + '></iframe></td>';
		content += '</tr>';
		content += '</table>';
	}

	$('content_id_' + borderID).innerHTML = content;
}

/**
 * Constrói um iframe oculto para os processos que possuem sua própria atividade do tipo view
 * @param string preffix O prefixo usado para nomear o iframe oculto
 * @param int numberOfColumns A quantidade de colunas da tabela
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @param int height A altura do iframe
 * @return string O código XHTML do iframe
 */
function constructHiddenView(preffix, numberOfColumns, instanceID, activityID, height)
{
	var output = '';
	output += '<tr class="table_elements_tr_line" id="' + preffix + '_hiddenView_' + instanceID + '_' + activityID + '" style="display: none;">';
	output += '<td colspan="' + numberOfColumns + '" align="left"><iframe width="100%" ' + ((height > 0) ? ' height="' + height + '"' : '') + '></iframe>';
	output += '</td></tr>';
	return output;
}

/**
 * Exibe ou oculta o iframe da atividade View
 * @param string preffix O prefixo usado para nomear o iframe oculto
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @param int viewActivityID O ID da atividade View
 * @return void
 */
function toggleHiddenView(preffix, instanceID, activityID, viewActivityID, useHTTPS)
{
	var tr = $(preffix + '_hiddenView_' + instanceID + '_' + activityID);
	var iframe = tr.childNodes[0].childNodes[0];

	function openView(obj)
	{
		workflowInboxOpenedViewActivities++;
		if (obj.element.src == '')
			obj.element.src = getInstanceURL(instanceID, viewActivityID, useHTTPS);
	}

	function closeView(obj)
	{
		workflowInboxOpenedViewActivities--;
		if (workflowInboxOpenedViewActivities < 0)
			workflowInboxOpenedViewActivities = 0;
		tr.hide();
	}

	if (tr.visible())
	{
		new Effect.BlindUp(iframe, {duration: 0.2, afterFinish: closeView});
	}
	else
	{
		tr.show();
		new Effect.BlindDown(iframe, {duration: 0.2, afterFinish: openView});
	}
}

function getInstanceURL(instanceID, activityID, useHTTPS)
{
	if (workflowCommonExpressoIndexPath == null)
	{
		workflowCommonExpressoIndexPath = $A(document.getElementsByTagName("script")).findAll(
			function(s)
			{
				return (s.src && s.src.match(/\/workflow\/js\/userinterface\/common_functions\.js(\?.*)?$/));
			}).first().src;
		/* pega só até o /workflow/ */
		workflowCommonExpressoIndexPath = workflowCommonExpressoIndexPath.replace(/\/workflow\/js\/userinterface\/common_functions\.js(\?.*)?$/, '');

		/* se não possuir o endereço completo, tenta montá-lo utilizando a informação da página atual (IE MAGIC) */
		if (workflowCommonExpressoIndexPath.match(/^https?:\/\//) == null)
			workflowCommonExpressoIndexPath = location.href.substr(0, location.href.indexOf('/', location.href.indexOf('//') + 2)) + workflowCommonExpressoIndexPath;
	}
	var output = instanceURL = workflowCommonExpressoIndexPath + '/index.php?menuaction=workflow.run_activity.go&iid=' + instanceID + '&activity_id=' + activityID;
	if (useHTTPS == 1)
		output = output.replace(/http:/, 'https:');
	return output;
}
