function callAjax(action, mode, handler, parameters)
{
	var url = '$this.' + action + '.' + mode;
	if (parameters)
		cExecute(url, handler, $H(parameters).toQueryString());
	else
		cExecute(url, handler);
}

/* check for error on ajax calls */
function checkError(data)
{
	if (data['error'])
	{
		alert(data['error']);
		return true;
	}
	else
		return false;
}

/* construct the processes list (combos) */
function buildProcessesList(data)
{
	if (checkError(data))
		return;

	function createCombo(data, name)
	{
		var output = '<select name="' + name + '" id="' + name + '" onchange="loadActivities();">';
		output += '<option value="-1"></option>';
		for (var i = 0; i < data.length; i++)
			output += '<option value="' + data[i]['wf_p_id'] + '">' + data[i]['wf_name'] + ' (v' + data[i]['wf_version'] + ')</option>';
		output += '</select>';
		return output;
	}

	$('divFrom').innerHTML = '<h3>Origem</h3>' + createCombo(data, 'processFrom');
	$('divTo').innerHTML = '<h3>Destino</h3>' + createCombo(data, 'processTo');
}

/* load the activities of the selected process */
function loadActivities()
{
	function loadActivitiesResult(data)
	{
		if (checkError(data))
			return;

		var divFromActivities;
		var divToActivities;
		if ($('divFromActivities') != null)
		{
			$('divTo').removeChild($('divToActivities'));
			$('divFrom').removeChild($('divWindow'));
		}

		new Insertion.Bottom($('divTo'), '<div id="divToActivities"></div>');
		new Insertion.Bottom($('divFrom'), '<div class="window" id="divWindow" style="position: fixed;"><h3 id="fromTitle">Atividades</h3><div id="divFromActivities" class="activities"></div></div>');
		divToActivities = $('divToActivities');
		divFromActivities = $('divFromActivities');

		/* create the "from" activities list */
		var current;
		for (var i = 0; i < data['from'].length; i++)
		{
			current = data['from'][i];
			divFromActivities.innerHTML += '<h2 class="activity" id="af' + current['wf_activity_id'] + '">' + current['wf_name'] + '</h2>';
		}
		/* make the activities draggable */
		for (var i = 0; i < data['from'].length; i++)
			new Draggable('af' + data['from'][i]['wf_activity_id'], {revert:true});

		/* create the "to" activities list */
		for (var i = 0; i < data['to'].length; i++)
		{
			current = data['to'][i];
			divToActivities.innerHTML += '<div class="window"><h3>' + current['wf_name'] + '</h3><div class="activities" style="min-height: 30px;" id="at' + current['wf_activity_id'] + '"></div></div>';
		}

		/* add a drop zone to the activities div */
		for (var i = 0; i < data['to'].length; i++)
			Droppables.add('at' + data['to'][i]['wf_activity_id'], {accept: 'activity', onDrop: dropActivity});

		/* round the corners of the divs */
		Nifty("div.window h3","top");

		/* add the "Move Instances" button */
		if (!$('moveInstancesButton'))
		{
			new Insertion.Bottom($('mainBody'), '<p id="pInfo">Mova as atividades do processo de origem para as atividades correspondentes do processo de destino</p>')
			var newContent = '<center><table><tr><td><label><input type="checkbox" name="active" id="active" checked="checked"/> Incluir instâncias ativas</label></td></tr>';
			newContent += '<tr><td><label><input type="checkbox" name="completed" id="completed" checked="checked"/> Incluir instâncias finalizadas</label></td></tr>';
			newContent += '<tr><td align="center"><button onclick="moveInstances(); return false;" style="display: none;" id="moveInstancesButton" class="moveInstancesButton">Mover Instâncias</button></td></tr></table></center>';
			new Insertion.Bottom($('mainBody'), newContent)
		}

		/* pre-match some activities (based on their names) */
		autoAssignActivities(data['pre-match']);
	}

	if (($F('processFrom') == -1) || ($F('processTo') == -1))
	{
		if ($('divFromActivities'))
		{
			$('divTo').removeChild($('divToActivities'));
			$('divFrom').removeChild($('divWindow'));
			$('moveInstancesButton').hide();
		}
		return false;
	}
	var params = {
		from: $F('processFrom'),
		to: $F('processTo')
	};

	callAjax('bo_move_instances', 'loadActivities', loadActivitiesResult, params);
}

/* move the instances from on process to another */
function moveInstances()
{
	if ($F('processFrom') == $F('processTo'))
	{
		alert('O processo de origem e destino são o mesmo.');
		return false;
	}

	if ($$('div#divFromActivities h2').length > 0)
	{
		alert('Todas as atividades do processo de origem devem ser mapeadas.');
		return false;
	}

	if (!confirm("Tem certeza que deseja mover as instâncias?"))
		return false;

	function moveInstancesResult(data)
	{
		if (checkError(data))
			return;

		if (data)
			alert("As instâncias foram movidas com sucesso");
	}

	var activityMappings = new Array();
	$A($('divToActivities').childNodes).each(function(toElement)
		{
			if (toElement.childNodes[1].childNodes.length > 0)
			{
				var toID = toElement.childNodes[1].id.substring(2);
				activityMappings[toID] = new Array();
				$A(toElement.childNodes[1].childNodes).each(function (fromElement)
					{
						activityMappings[toID][activityMappings[toID].length] = fromElement.id.substring(2);
					}
				);
				activityMappings[toID] = $H(activityMappings[toID]);
			}
		}
	);

	var params = {
		from: $F('processFrom'),
		to: $F('processTo'),
		activityMappings: JSON.stringify($H(activityMappings)),
		active: $F('active'),
		completed: $F('completed')
	};

	callAjax('bo_move_instances', 'moveInstances', moveInstancesResult, params);
}

/* drag and drop manager */
function dropActivity(dragElement, dropElement)
{
	var parentElement = dragElement.parentNode;
	dropElement.appendChild(dragElement);
	if ($('pInfo'))
	{
		$('mainBody').removeChild($('pInfo'));
	}
	if ((parentElement.childNodes.length == 0) && (parentElement.id == 'divFromActivities'))
	{
		var paragraph = document.createElement('p');
		paragraph.innerHTML = 'sem atividades';
		parentElement.appendChild(paragraph);
		$('moveInstancesButton').show();
	}
}

/* assign pre-matched activities based on their names */
function autoAssignActivities(matches)
{
	for (var i = 0; i < matches.length; i++)
		$('at' + matches[i]['to']).appendChild($('af' + matches[i]['from']));
	if ($('divFromActivities').childNodes.length == 0)
	{
		var paragraph = document.createElement('p');
		paragraph.innerHTML = 'sem atividades';
		$('divFromActivities').appendChild(paragraph);
		$('moveInstancesButton').show();
	}
	else
		$('moveInstancesButton').hide();
}

/* interface start */
window.onload = function()
{
	callAjax('bo_move_instances', 'loadProcesses', buildProcessesList);
};
