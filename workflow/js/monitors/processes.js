/* retorno do Ajax para listagem de processos */
function processList(data)
{
	if (handleError(data))
	{
		var divProcess = document.getElementById("divProcess");
		divProcess.innerHTML = '';
		if (data['data'].length == 0)
		{
			var parag = document.createElement("P");
			parag.className = "text_dsp";
			parag.innerHTML = "N�o existem processos";
			divProcess.appendChild(parag);
		}
		else
		{
			permissions = data['permissions'];
			drawProcessesList(data);
		}
	}
}

/* chama o m�todo Ajax que lista os processos */
function listProcesses()
{
	cExecute("$this.bo_monitors.listProcesses", processList);
}

/* desenha os processos na interface */
function drawProcessesList(data)
{
	var processes = data['data'];
	var divProcess = document.getElementById("divProcess");
	var tableHeader = new Array();
	var tableAtributes = new Array();

	var content = '<table width="100%" align="center" border="1" class="content_table">';
	content += '<tr><th>ID</th><th>Nome</th></tr>';
	var processCount = processes.length;
	for (var i = 0; i < processCount; i++)
	{
		content += '<tr id="p_' + processes[i]['wf_p_id'] + '"><td>' + processes[i]['wf_p_id'] + '</td><td><a href="" onclick="drawProcessOptions(' + processes[i]['wf_p_id'] + '); return false;">' + processes[i]['wf_name'] + ' v' + processes[i]['wf_version']  + '</a></td></tr>';
	}
	content += '</table>';
	divProcess.innerHTML = content;

}

/* desenha as op��es do processo */
function drawProcessOptions(pid)
{
	$("divInstance").innerHTML = '';

	/* marca o processo selecionado */
	var tr = document.getElementById('p_' + pid);
	var table = tr.parentNode;
	var newClass;
	$A(table.childNodes).each(function(row)
		{
			Element.extend(row).removeClassName('selected')
		});
	Element.extend(tr).addClassName('selected');

	/* constr�i a tabela de a��es */
	var content = '<table width="100%" align="center" border="1" class="content_table">';
	content += '<tr><th>Op��es</th></tr>';

	content += '<tr><td><a href="" onclick="loadInstances(' + pid + '); return false;">Inst�ncias Ativas</a></td></tr>';
	content += '<tr><td><a href="" onclick="loadCompletedInstances(' + pid + '); return false;">Inst�ncias Finalizadas</a></td></tr>';
	content += '<tr><td><a href="" onclick="loadInconsistentInstances(' + pid + '); return false;">Inst�ncias Inconsistentes</a></td></tr>';

	if (permissions[pid]['bits'][IP_VIEW_STATISTICS])
		content += '<tr><td><a href="" onclick="clickShowStatistics(this, ' + pid + '); return false;">Estat�sticas</a></td></tr>';

	if (permissions[pid]['bits'][IP_REPLACE_USER])
		content += '<tr><td><a href="" onclick="clickReplaceUser(this, ' + pid + '); return false;">Substituir Usu�rio</a></td></tr>';

	content += '</table>';
	$('divOptions').innerHTML = content;
}

function clickReplaceUser(link, pid)
{
	function resultClickReplaceUser(data)
	{
		$('MonitorLoading').remove();
		var divInstance = $('divInstance');
		if (handleError(data))
		{
			var users = data['users'];
			if (users.length > 0)
			{
				var content = '<table width="100%">';
				content += '<tr valign="top"><td>';
				content += '<h2>Usu�rios</h2><table><tr><td><label>Usu�rio Antigo</label></td>';
				content += '<td><select id="oldUser">';
				for (var i = 0; i < users.length; i++)
					content += '<option value="' + users[i]['id'] + '">' + users[i]['name'] + '</option>';
				content += '</select></td></tr>';
				content += '<tr><td><label>Novo Usu�rio</label></td>';
				content += '<td><input type="hidden" name="newUser" id="newUser" value="" />';
				content += '<input type="input" name="newUser_desc" id="newUser_desc" value="" readonly="true" size="32" />';
				content += '<a href="javascript:void(0)" onclick="openParticipantsWindow(\'newUser\', \'uid=1&hidegroups=1\');"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/add_user.png"/></a></td></tr>';
				content += '<tr><td colspan="2"><button onclick="clickLoadActivities(' + pid + ');return false;">Pr�ximo >></button></td></tr></table>';
				content += '</td>';
				content += '<td id="tdActivities"></td>';
				content += '<td id="tdRoles"></td>';
				content += '</tr></table>';
				divInstance.innerHTML += content;
			}
			else
				divInstance.innerHTML = '<p class="text_dsp">Este processo n�o possui inst�ncias ativas</p>';
		}
	}

	$('divInstance').innerHTML = '<h2>Substituir Usu�rio</h2><div id="MonitorLoading"><img src="workflow/templateFile.php?file=images/loading.gif"/> Carregando ...</div>';

	var params = 'pid=' + pid;
	cExecute("$this.bo_monitors.getUsersInInstances", resultClickReplaceUser, params);
}

function clickLoadActivities(pid)
{
	function resultClickLoadActivities(data)
	{
		$('MonitorLoading').remove();
		var tdActivities = $('tdActivities');
		if (handleError(data))
		{
			var activities = data['activities'];
			if (activities.length > 0)
			{
				var content = '<table>';
				content += '<tr><td><label>Atividade</label></td>';
				content += '<td><select id="activity">';
				content += '<option value="0">Todas</option>';
				for (var i = 0; i < activities.length; i++)
					content += '<option value="' + activities[i]['id'] + '">' + activities[i]['name'] + '</option>';
				content += '</select></td></tr>';
				content += '<tr><td colspan="2"><button onclick="clickCheckUserRoles(' + pid + ');return false;">Pr�ximo >></button></td></tr></table>';
				tdActivities.innerHTML += content;
			}
			else
				tdActivities.innerHTML = '<p class="text_dsp">Este processo n�o possui inst�ncias ativas com o usu�rio selecionado</p>';
		}
	}

	var newUser = $F('newUser');
	if (!newUser)
	{
		alert('Selecione o novo usu�rio');
		return;
	}
	$('tdActivities').innerHTML = '<h2>Ativitidades</h2><div id="MonitorLoading"><img src="workflow/templateFile.php?file=images/loading.gif"/> Carregando ...</div>';
	var params = 'pid=' + pid + '&user=' + $F('oldUser');
	cExecute("$this.bo_monitors.getUserActivities", resultClickLoadActivities, params);
}

function resultClickCheckUserRoles(data)
{
	$('MonitorLoading').remove();
	var tdRoles = $('tdRoles');
	if (handleError(data))
	{
		var roles = data['roles'];
		if (roles.length > 0)
		{
			var content = '<p style="width: 250px">O usu�rio selecionado n�o pode acessar as atividades abaixo porque n�o est� mapeado nos perfis necess�rios. Por favor, fa�a as corre��es.</p>';
			content += '<table>';
			for (var i = 0; i < roles.length; i++)
			{
				content += '<tr valign="middle"><td>' + roles[i]['name'] + '</td>';
				content += '<td><select id="activity_' + roles[i]['id'] + '">';
				for (var j = 0; j < roles[i]['possibleRoles'].length; j++)
					content += '<option value="' + roles[i]['possibleRoles'][j]['id'] + '">' + roles[i]['possibleRoles'][j]['name'] + '</option>';
				content += '</select></td><td>&nbsp;<a href="javascript:clickAddUserToRole(' + data['pid'] + ', $F(\'activity_' + roles[i]['id'] + '\'))"><img border="0" width="13" height="13" src="workflow/templateFile.php?file=images/add.png"/></a></td>';
				content += '</tr>';
			}
			content += '</table>';
			tdRoles.innerHTML += content;
		}
		else
		{
			var content = '<p class="text_dsp">O usu�rio selecionado j� se encontra nos perfis necess�rios</p>';
			content += '<br/><center><button onclick="clickReplaceUserAction(' + data['pid'] + ');return false;">Concluir</button></center>';
			tdRoles.innerHTML = content;
		}
	}
}

function clickCheckUserRoles(pid)
{
	if (!$F('newUser'))
	{
		alert('Selecione o novo usu�rio');
		return;
	}
	$('tdRoles').innerHTML = '<h2>Perfis</h2><div id="MonitorLoading"><img src="workflow/templateFile.php?file=images/loading.gif"/> Carregando ...</div>';
	var params = 'pid=' + pid + '&oldUser=' + $F('oldUser') + '&newUser=' + $F('newUser') + '&activity=' + $F('activity');
	cExecute("$this.bo_monitors.checkUserRoles", resultClickCheckUserRoles, params);
}

function clickAddUserToRole(pid, role)
{
	$('tdRoles').innerHTML = '<h2>Perfis</h2><div id="MonitorLoading"><img src="workflow/templateFile.php?file=images/loading.gif"/> Carregando ...</div>';
	var params = 'pid=' + pid + '&oldUser=' + $F('oldUser') + '&newUser=' + $F('newUser') + '&activity=' + $F('activity') + '&role=' + role;
	cExecute("$this.bo_monitors.addUserToRole", resultClickCheckUserRoles, params);
}

function clickReplaceUserAction(pid)
{
	function resultClickReplaceUserAction(data)
	{
		$('MonitorLoading').remove();
		if (handleError(data))
		{
			var content = '<p>' + data['OKCount'] + ' inst�ncias tiveram seu usu�rio substitu�do.</p>';
			if (data['errorCount'] > 0)
				content += '<p>' + data['errorCount'] + ' apresentaram problema na atualiza��o. Por favor, tente novamente.</p>';
			$('divInstance').innerHTML += content;
		}
	}

	var params = 'pid=' + pid + '&oldUser=' + $F('oldUser') + '&newUser=' + $F('newUser') + '&activity=' + $F('activity');
	$('divInstance').innerHTML = '<h2>Substituir Usu�rio</h2><div id="MonitorLoading"><img src="workflow/templateFile.php?file=images/loading.gif"/> Carregando ...</div>';
	cExecute("$this.bo_monitors.replaceUser", resultClickReplaceUserAction, params);
}

function clickShowStatistics(link, pid)
{
	function resultShowStatistics(data)
	{
		killElement('loading_image_statistics');
		var divInstance = $('divInstance');
		if (handleError(data))
		{
			if (data.length > 0)
			{
				var content = '<h2>Estat�sticas</h2>';
				content += '<table>';
				for (var i = 0; i < data.length; i++)
					content += '<tr><td align="center"><img src="' + data[i] + '"/></tr></td>';
				content += '</table>';
				divInstance.innerHTML = content;
			}
			else
				divInstance.innerHTML = '<p class="text_dsp">Este processo n�o possui estat�sticas</p>';
		}
	}

	var image = document.createElement("IMG");
	image.setAttribute('src', 'workflow/templateFile.php?file=images/loading.gif');
	image.setAttribute('height', '11');
	image.setAttribute('id', 'loading_image_statistics');
	link.parentNode.appendChild(image);
	cExecute ("$this.bo_monitors.showStatistics", resultShowStatistics, 'pid=' + pid);
}
