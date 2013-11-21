var DateUnity =
	{
		YEAR: 0,
		MONTH: 1,
		WEEK: 2,
		DAY: 3,
		HOUR: 4,
		MINUTE: 5,
		NONE: 6
	};

var DateType =
	{
		ABSOLUTE_DATE: 0,
		WEEK_DATE: 1,
		RELATIVE_DATE: 2
	};

var WeekDays =
	{
		SUNDAY: 1,
		MONDAY: 2,
		TUESDAY: 4,
		WEDNESDAY: 8,
		THURSDAY: 16,
		FRIDAY: 32,
		SATURDAY: 64
	};

var JobStatus =
	{
		JOB_SUCCESS: 0,
		JOB_FAIL: 1,
		FAIL: 2,
		ERROR: 3,
		UNKNOWN: 4
	};

function getRepetitionSummary(jobInfo)
{
	switch (parseInt(jobInfo['date_type']))
	{
		case DateType.ABSOLUTE_DATE:
			return getRepetitionSummaryAbsoluteDate(jobInfo);

		case DateType.WEEK_DATE:
			return getRepetitionSummaryWeekDate(jobInfo);

		case DateType.RELATIVE_DATE:
			return getRepetitionSummaryRelativeDate(jobInfo);
	}
}

function getRepetitionSummaryAbsoluteDate(jobInfo)
{
	var output = 'Executado';

	if (parseInt(jobInfo['interval_unity']) == DateUnity.NONE)
	{
		var data = jobInfo['time_start'].split(' ')[0].split('-');
		output += ' no dia ' + data[2] + '/' + data[1] + '/' + data[0];
	}
	else
	{
		output += ' uma vez';
		var plural = (jobInfo['interval_value'] > 1);
		if ((parseInt(jobInfo['interval_unity']) == DateUnity.HOUR) && (jobInfo['interval_value'] == 2))
			output += ' a cada duas';
		else
			output += plural ? ' a cada ' + jobInfo['interval_value'] : ' por';
		switch (parseInt(jobInfo['interval_unity']))
		{
			case DateUnity.YEAR:
				output += plural ? ' anos' : ' ano';
				break;

			case DateUnity.MONTH:
				output += plural ? ' meses' : ' mês';
				break;

			case DateUnity.DAY:
				output += plural ? ' dias' : ' dia';
				break;

			case DateUnity.HOUR:
				output += plural ? ' horas' : ' hora';
				break;

			case DateUnity.MINUTE:
				output += plural ? ' minutos' : ' minuto';
				break;
		}
	}
	return output;
}

function getRepetitionSummaryWeekDate(jobInfo)
{
	var output = 'Executado';

	function numberToWeekDays(num, plural)
	{
		var output = '';
		if (num & WeekDays.SUNDAY)
			output += 'domingo' + (plural ? 's ' : ' ');

		if (plural)
			output = (output == '') ? 'às;' : 'aos;' + output;

		if (num & WeekDays.MONDAY)
			output += 'segunda' + (plural ? 's ' : ' ');

		if (num & WeekDays.TUESDAY)
			output += 'terça' + (plural ? 's ' : ' ');

		if (num & WeekDays.WEDNESDAY)
			output += 'quarta' + (plural ? 's ' : ' ');

		if (num & WeekDays.THURSDAY)
			output += 'quinta' + (plural ? 's ' : ' ');

		if (num & WeekDays.FRIDAY)
			output += 'sexta' + (plural ? 's ' : ' ');

		if (num & WeekDays.SATURDAY)
			output += 'sábado' + (plural ? 's ' : ' ');

		output = output.substr(0, output.length-1).replace(/ /g, ', ').replace(/;/g, ' ');

		var lastComma = output.lastIndexOf(', ');
		if (lastComma != -1)
			output = output.substr(0, lastComma) + ' e' + output.substr(lastComma + 1);

		return output;

	}

	var plural = (parseInt(jobInfo['interval_unity']) == DateUnity.WEEK);
	switch (parseInt(jobInfo['week_days']))
	{
		case 0:
			return 'Não é executado';
			break;

		case 62:
			output += plural ? ' nos dias da semana' : ' nos dias';
			break;

		case 65:
			output += plural ? ' nos finais de semana' : ' no fim de semana';
			break;

		case 127:
			output += ' todos os dias da semana';
			break;

		default:
			output += ' ' + numberToWeekDays(jobInfo['week_days'], plural);
	}

	if (parseInt(jobInfo['interval_unity']) == DateUnity.NONE)
	{
		var data = jobInfo['time_start'].split(' ')[0].split('-');
		output += ' da semana do dia ' + data[2] + '/' + data[1] + '/' + data[0];
		return output;
	}

	switch (parseInt(jobInfo['interval_value']))
	{
		case 1:
			break;

		case 2:
			output += ' a cada duas semanas';
			break;

		default:
			output += ' a cada ' + jobInfo['interval_value'] + ' semanas';
	}

	return output;
}

function getRepetitionSummaryRelativeDate(jobInfo)
{
	var output = 'Executado';

	switch (parseInt(jobInfo['month_offset']))
	{
		case 1:
			output += ' no último dia do mês';
			break;

		case 2:
			output += ' no penúltimo dia do mês';
			break;

		case 3:
			output += ' no antepenúltimo dia do mês';
			break;

		default:
			output += ' quando faltar ' + jobInfo['month_offset'] + ' dias para o fim do mês';
	}

	if (parseInt(jobInfo['interval_unity']) == DateUnity.NONE)
	{
		var data = jobInfo['time_start'].split(' ')[0].split('-');
		output += ' ' + data[1] + '/' + data[0];
		return output;
	}

	if (jobInfo['interval_value'] == 1)
		return output;

	switch (parseInt(jobInfo['interval_value']))
	{
		case 2:
			output += '. Repete bimestralmente';
			break;

		case 3:
			output += '. Repete trimestralmente';
			break;

		case 6:
			output += '. Repete semestralmente';
			break;

		case 12:
			output += '. Repete anualmente';
			break;

		default:
			output += '. Repete a cada ' + jobInfo['interval_value'] + ' meses';
	}

	return output;
}

function checkError(data)
{
	if (data)
	{
		if (data['error'])
		{
			if (typeof(data['error']) == 'string')
				alert(data['error']);
			else
				if (typeof(data['error']) == 'object')
					alert(data['error'].join("\n"));
				return true;
			}
		}

	return false;
}
