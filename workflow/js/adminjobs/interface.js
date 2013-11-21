function addEventWatchers()
{
	$('repeatJob').observe('click', clickRepeatJob);
	for (var i = 0; i < 3; i++)
		$('dateType_' + i).observe('click', clickRepeatJob);
}

function getSelectedDateType()
{
	for (var i = 0; i < 3; i++)
		if ($F('dateType_' + i) != null)
			return i;

}

function clickRepeatJob(event)
{
	for (var i = 0; i < 3; i++)
	{
		$('repeatDate_' + i).hide();
		var obj = $('date_' + i);
		if (obj)
			obj.hide();
	}

	var selectedDateType = getSelectedDateType();
	var obj = $('date_' + selectedDateType);
	if (obj)
		obj.show();

	if ($F('repeatJob') == 'on')
		$('repeatDate_' + selectedDateType).show();
}

function loadJobList()
{
	$('jobList').innerHTML = '<img src="workflow/templateFile.php?file=images/loading.gif"/> Carregando lista de jobs ...';

	cExecute("$this.bo_adminjobs.loadJobs", resultLoadJobList, "processID=" + $F('processID'));
}

function resultLoadJobList(data)
{
	if (checkError(data))
		return;

	var content = '';
	if (data.length > 0)
	{
		content += '<table class="jobList">';
		for (var i = 0; i < data.length; i++)
		{
			var dataHash = new Hash();
			for (j in data[i])
				if (typeof data[i][j] != 'function')
					dataHash[j] = data[i][j];

			content += '<tr id="job_' + data[i]['job_id'] + '">';
			content += '<td class="jobListIcon"><a href="javascript:void(0)" onclick="if (confirm(\'Tem certeza que deseja ' + ((data[i]['active'] == 't') ? 'desativar' : 'ativar') + ' o Job?\')) toggleActive(' + data[i]['job_id'] + '); return false;" title="Clique aqui para ' + ((data[i]['active'] == 't') ? 'desativar' : 'ativar') + ' o Job"><img src="workflow/templateFile.php?file=images/' + ((data[i]['active'] == 't') ? 'apply.png' : 'button_cancel.png') + '"/></a></td>';
			content += '<td>';
			content += '<h1>' + data[i]['name'] + '</h1>';
			content += '<h3>' + getRepetitionSummary(data[i]) + '</h3>';
			content += '</td>';

			/* ações */
			content += '<td class="jobListActions">';
			content += '<a href="javascript:void(0)" onclick="if (confirm(\'Tem certeza que deseja executar este Job\')) runJob(' + data[i]['job_id'] + '); return false;"><img src="workflow/templateFile.php?file=images/process.png" title="Executar Job" alt="Executar Job"/></a>';
			content += '<a href="javascript:void(0)" onclick="editJob(' + dataHash.customInspect() + ');"><img src="workflow/templateFile.php?file=images/edit.png" title="Editar Job" alt="Editar Job"/></a>';
			content += '<a href="javascript:void(0)" onclick="loadLogs(' + data[i]['job_id'] + ', 0); return false;"><img src="workflow/templateFile.php?file=images/log.png"/ title="Ver Logs" alt="Ver Logs"></a>';
			content += '&nbsp;&nbsp;&nbsp;';
			content += '<a href="javascript:void(0)" onclick="if (confirm(\'Tem certeza que deseja remover este Job?\')) removeJob(' + data[i]['job_id'] + '); return false;"><img src="workflow/templateFile.php?file=images/remove.png" title="Excluir Job" alt="Excluir Job"/></a>';
			content += '</td>';

			content += '</tr>';
		}
		content += '</table><br/>';
	}
	else
	{
		content += '<br/><br/><br/>';
		content += '<center><b>Não existem Jobs cadastrados neste Processo</b></center>';
		content += '<br/><br/><br/>';
	}
	content += '<ul class="horizontalMenu"><li><a href="javascript:void(0)" onclick="newJob(); return false;"><img src="workflow/templateFile.php?file=images/new_job.png" width="20"> Novo</a></li></ul>';
	$('jobList').innerHTML = content;
}
