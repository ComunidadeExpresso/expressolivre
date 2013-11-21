function editJob(jobObjectJason)
{
	clearForm();
	$('saving').hide();
	$('actions').show();
	Effect.BlindUp('jobList');
	var jobObject = $H(jobObjectJason);
	loadJobInfo(jobObject);
	Effect.BlindDown('jobForm');
}

function clearForm()
{
	var dateObject = new Date();
	$('jobID').value = '';
	$('name').value = '';
	$('_description').value = '';
	$('active').checked = true;
	$('startDate').value = ((dateObject.getDate() < 10) ? '0' + dateObject.getDate() : dateObject.getDate()) + '/' + ((dateObject.getMonth() < 10) ? '0' + (dateObject.getMonth()+1) : (dateObject.getMonth() + 1)) + '/' + dateObject.getFullYear();
	$('executionTime_Hour').value = ((dateObject.getHours() < 10) ? '0' + dateObject.getHours() : dateObject.getHours());
	$('executionTime_Minute').value = ((dateObject.getMinutes() < 10) ? '0' + dateObject.getMinutes() : dateObject.getMinutes());
	$('repeatJob').checked = false;
	$('dateType_0').checked = true;
	$('absoluteDateIntervalValue').value = 1;
	$('absoluteDateIntervalUnity').value = DateUnity.DAY;
	$('weekDateIntervalValue').value = 1;
	for (var i = 0; i < 7; i++)
		$('weekDateDay_' + i).checked = false;
	$('relativeDateIntervalValue').value = 1;
	$('relativeDateMonthOffset').value = 1;
}

function loadJobInfo(jobObject)
{
	$('jobID').value = jobObject['job_id'];
	$('name').value = jobObject['name'];
	$('_description').value = jobObject['description'];
	$('active').checked = (jobObject['active'] == 't')
	var dateTime = jobObject['time_start'].split(' ');
	var datePart = dateTime[0].split('-');
	var timePart = dateTime[1].split(':');
	$('startDate').value = datePart[2] + '/' + datePart[1] + '/' + datePart[0];
	$('executionTime_Hour').value = timePart[0];
	$('executionTime_Minute').value = timePart[1];
	$('repeatJob').checked = (parseInt(jobObject['interval_unity']) != DateUnity.NONE);

	$('dateType_' + jobObject['date_type']).checked = true;
	if (jobObject['interval_value'] == 0)
		jobObject['interval_value'] = 1;
	if (parseInt(jobObject['interval_unity']) == DateUnity.NONE)
		jobObject['interval_unity'] = DateUnity.DAY;

	switch (parseInt(jobObject['date_type']))
	{
		case DateType.ABSOLUTE_DATE:
			loadJobInfoAbsoluteDate(jobObject);
			break;

		case DateType.WEEK_DATE:
			loadJobInfoWeekDate(jobObject);
			break;

		case DateType.RELATIVE_DATE:
			loadJobInfoRelativeDate(jobObject);
			break;
	}
	clickRepeatJob();
}

function loadJobInfoAbsoluteDate(jobObject)
{
	$('absoluteDateIntervalValue').value = jobObject['interval_value'];
	$('absoluteDateIntervalUnity').value = jobObject['interval_unity'];
}

function loadJobInfoWeekDate(jobObject)
{
	$('weekDateIntervalValue').value = jobObject['interval_value'];
	var weekDays = parseInt(jobObject['week_days']);
	for (var i = 0; i < 7; i++)
		if (weekDays & Math.pow(2, i))
			$('weekDateDay_' + i).checked = true;
}

function loadJobInfoRelativeDate(jobObject)
{
	$('relativeDateIntervalValue').value = jobObject['interval_value'];
	$('relativeDateMonthOffset').value = jobObject['month_offset'];
}

function saveJob()
{
	function resultSaveJob(data)
	{
		if (!checkError(data))
		{
			resultLoadJobList(data);
			Effect.BlindUp('jobForm');
			Effect.BlindDown('jobList');
		}
		else
		{
			$('saving').hide();
			$('actions').show();
		}
	}

	var startDate = $F('startDate').split('/');
	var intervalValue;
	var intervalUnity;
	if ($F('repeatJob') != 'on')
	{
		intervalValue = 0;
		intervalUnity = DateUnity.NONE;
	}
	else
	{
		switch (getSelectedDateType())
		{
			case DateType.ABSOLUTE_DATE:
				intervalValue = $F('absoluteDateIntervalValue');
				intervalUnity = $F('absoluteDateIntervalUnity');
				break;

			case DateType.WEEK_DATE:
				intervalValue = $F('weekDateIntervalValue');
				intervalUnity = DateUnity.WEEK;
				break;

			case DateType.RELATIVE_DATE:
				intervalValue = $F('relativeDateIntervalValue');
				intervalUnity = DateUnity.MONTH;
				break;
		}
	}
	var weekDays = 0;
	var currentWeekDay;
	for (var i = 0; i < 7; i++)
	{
		currentWeekDay = $F('weekDateDay_' + i);
		if (currentWeekDay != null)
			weekDays = weekDays | currentWeekDay;
	}

	var params =
	{
		jobID: $F('jobID'),
		processID: $F('processID'),
		name: escape($F('name')),
		description : escape($F('_description')),
		timeStart: escape(startDate[2] + '-' + startDate[1] + '-' + startDate[0] + ' ' + $F('executionTime_Hour') + ':' + $F('executionTime_Minute') + ':00'),
		intervalValue: intervalValue,
		intervalUnity: intervalUnity,
		dateType: getSelectedDateType(),
		weekDays: weekDays,
		monthOffset: $F('relativeDateMonthOffset'),
		active: ($F('active') == 'on')
	};

	$('actions').hide();
	$('saving').show();

	cExecute('$this.bo_adminjobs.saveJob', resultSaveJob, $H(params).toQueryString());
}

function removeJob(jobID)
{
	function resultRemoveJob(data)
	{
		if (checkError(data))
			return;
		Effect.Fade('job_' + jobID);
	}

	cExecute('$this.bo_adminjobs.removeJob', resultRemoveJob, 'jobID=' + jobID);
}

function newJob()
{
	clearForm();
	clickRepeatJob();
	$('saving').hide();
	$('actions').show();
	Effect.BlindUp('jobList');
	Effect.BlindDown('jobForm');
}

function loadLogs(jobID, p_page)
{
	function resultLoadLogs(data)
	{
		if (checkError(data))
			return;
		var content = '<h2>Job: ' + $('job_' + jobID).childNodes[1].firstChild.innerHTML + '</h2>';
		content += '<button onclick="Effect.BlindUp(\'logList\'); Effect.BlindDown(\'jobList\'); return false;">Voltar</button><br/><br/>';

		if (data['logs'].length > 0)
		{
			var pagingLinks = '';
			if (data['pagingLinks'])
			{
				var pagingSize = data['pagingLinks'].length;
				for (var i = 0; i < pagingSize; i++)
				{
					if (data['pagingLinks'][i]['do_link'] == true)
						pagingLinks += '<a href="javascript:loadLogs(' + jobID + ', ' + data['pagingLinks'][i]['p_page'] + ');">' + data['pagingLinks'][i]['name'] + '</a>&nbsp;';
					else
						pagingLinks += '<strong>' + data['pagingLinks'][i]['name'] + '</strong>&nbsp;';
				}
			}
			content += pagingLinks;

			var trClasses = new Array();
			trClasses[JobStatus.JOB_SUCCESS] = 'success';
			trClasses[JobStatus.JOB_FAIL] = 'fail';
			trClasses[JobStatus.FAIL] = 'fail';
			trClasses[JobStatus.ERROR] = 'error';
			trClasses[JobStatus.UNKNOWN] = 'unknown';

			content += '<table class="logList" id="logListTable">';
			var currentDate;
			var currentLog;
			for (var i = 0; i < data['logs'].length; i++)
			{
				currentLog = data['logs'][i];
				currentDate = currentLog['human_date_time'] ? currentLog['human_date_time'] : currentLog['date_time'];
				content += '<tr class="' + trClasses[parseInt(currentLog['status'])] + '">';
				content += '<td class="logListIcon">&nbsp;</td>'
				content += '<td class="logDate" title="' + currentLog['date_time'] + '"><center>' + currentDate.replace(/ /, '<br/>') + '</center></td>';
				content += '<td class="logResult">' + currentLog['result'] + '</td>';
				content += '</tr>';
			}
			content += '</table>';
		}
		else
		{
			content += '<center>Este Job ainda não foi executado e, portanto, não possui entradas de log.</center><br/>';
		}

		content += '<br/><button onclick="Effect.BlindUp(\'logList\'); Effect.BlindDown(\'jobList\'); return false;">Voltar</button>';

		$('logList').innerHTML = content;
		if ($('jobList').visible())
			Effect.BlindUp('jobList');
		Effect.BlindDown('logList');
	}

	if (!p_page)
		p_page = 0;

	if ($('logList').visible())
	{
		function recall()
		{
			loadLogs(jobID, p_page);
		}

		new Effect.BlindUp($('logList'), {afterFinish: recall});
		return;
	}

	cExecute('$this.bo_adminjobs.loadLogs', resultLoadLogs, 'jobID=' + jobID + '&p_page=' + p_page);
}

function toggleActive(jobID)
{
	function resultToggleActive(data)
	{
		if (!checkError(data))
		{
			Effect.BlindUp('jobList', {
					afterFinish: function()
					{
						resultLoadJobList(data);
						Effect.BlindDown('jobList');
					}
				});
		}
	}

	cExecute('$this.bo_adminjobs.toggleActive', resultToggleActive, 'jobID=' + jobID + '&processID=' + $F('processID'));
}

function runJob(jobID)
{
	function resultRunJob(data)
	{
		if (checkError(data))
			return;
		var content = '<h2>Job: ' + $('job_' + jobID).childNodes[1].firstChild.innerHTML + '</h2>';
		if (data['output']['messages'])
		{
			content += '<table class="jobResult">';
			content += '<tr><th>Mensagens</th></tr>';
			content += '<tr><td><pre>' + data['output']['messages'].join("\n\n") + '</pre></td></tr>';
			content += '</table><br/><br/>';
		}

		if (data['output']['default'])
		{
			content += '<table class="jobResult">';
			content += '<tr><th>Saída Default</th></tr>';
			content += '<tr><td><pre>' + data['output']['default'] + '</pre></td></tr>';
			content += '</table><br/><br/>';
		}

		if (data['output']['error'])
		{
			content += '<table class="jobResult">';
			content += '<tr><th>Saída de Erro</th></tr>';
			content += '<tr><td><pre>' + data['output']['error'] + '</pre></td></tr>';
			content += '</table><br/><br/>';
		}

		if (!data['output']['default'] && !data['output']['error'])
			content += '<i>Nenhuma saída foi produzida pelo job</i><br/><br/>';
		content += '<button onclick="Effect.BlindUp(\'jobResult\'); Effect.BlindDown(\'jobList\'); return false;">Voltar</button>';

		$('jobResult').innerHTML = content;
		Effect.BlindUp('jobList');
		Effect.BlindDown('jobResult');
	}

	cExecute('$this.bo_adminjobs.runJob', resultRunJob, 'jobID=' + jobID);
}
