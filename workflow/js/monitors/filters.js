var filters = new Array();
var completedFilters = new Array();

var Filter = Class.create();

Filter.prototype =
{
	initialize: function()
	{
		this.index = null;
		this.id = '';
	},

	generateHTML: function()
	{
	},

	remove: function()
	{
		var div = $('divFilterSelection_' + this.index);
		if (div)
			div.remove();
		div = $('filter_' + this.index)
		if (div)
			div.remove();
	},

	removeButtonHTML: function()
	{
		var output = '';
		output = '<a href="javascript:removeFilter(' + this.index + ')"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/button_cancel.png"/></a>';
		return output;
	},

	setup: function()
	{
	},

	serialize: function(data)
	{
		return JSON.stringify(data);
	},

	ajaxData: function()
	{
		return '';
	}
};

var FilterActivityDate =
{
	id: 'activityDate',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'A <strong>data de chegada na atividade</strong> atual é <select id="' + this.id + '_' + this.index + '_operator">';
		output += '<option value="EQ">Igual a</option>';
		output += '<option value="LT">Antes de</option>';
		output += '<option value="GT">Depois de</option>';
		output += '</select>';
		output += ' <input type="text" id="' + this.id + '_' + this.index + '_date" name="' + this.id + '_' + this.index + '_date" value="" size="10" maxlength="10" onkeypress="return(formatCalendarInput(this, event))"/>';
		output += '<button id="' + this.id + '_' + this.index + '_date-trigger">...</button>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	setup: function()
	{
		Calendar.setup({"inputField":this.id + '_' + this.index + '_date',"button":this.id + '_' + this.index + '_date-trigger',"singleClick":true,"name":this.id + '_' + this.index + '_date',"default":true});
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				operator: $F(this.id + '_' + this.index + '_operator'),
				date: $F(this.id + '_' + this.index + '_date')
			};
		return this.serialize(data);
	}
};

var FilterInstanceName =
{
	id: 'instanceName',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'O <strong>identificador da instância</strong> contém: <input type="text" id="' + this.id + '_' + this.index + '_name">';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				name: $F(this.id + '_' + this.index + '_name')
			};
		return this.serialize(data);
	}
};

var FilterInstanceID =
{
	id: 'instanceID',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'O <strong>ID da instância</strong> é <select id="' + this.id + '_' + this.index + '_operator">';
		output += '<option value="EQ">Igual a</option>';
		output += '<option value="LT">Menor que</option>';
		output += '<option value="GT">Maior que</option>';
		output += '</select>';
		output += ' <input type="text" id="' + this.id + '_' + this.index + '_number" size="5"/>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				operator: $F(this.id + '_' + this.index + '_operator'),
				number: $F(this.id + '_' + this.index + '_number')
			};
		return this.serialize(data);
	}
};

var FilterInstancePriority =
{
	id: 'instancePriority',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'A <strong>prioridade da instância</strong> é <select id="' + this.id + '_' + this.index + '_operator">';
		output += '<option value="EQ">Igual a</option>';
		output += '<option value="LT">Menor que</option>';
		output += '<option value="GT">Maior que</option>';
		output += '</select>';
		output += ' <select id="' + this.id + '_' + this.index + '_priority">';
		for (var i = 0; i < 5; i++)
			output += '<option value="' + i + '">' + i + '</option>';
		output += '</select>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				operator: $F(this.id + '_' + this.index + '_operator'),
				priority: $F(this.id + '_' + this.index + '_priority')
			};
		return this.serialize(data);
	}
};

var FilterInstanceDate =
{
	id: 'instanceDate',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'A <strong>data de criação da instância</strong> é <select id="' + this.id + '_' + this.index + '_operator">';
		output += '<option value="EQ">Igual a</option>';
		output += '<option value="LT">Antes de</option>';
		output += '<option value="GT">Depois de</option>';
		output += '</select>';
		output += ' <input type="text" id="' + this.id + '_' + this.index + '_date" name="' + this.id + '_' + this.index + '_date" value="" size="10" maxlength="10" onkeypress="return(formatCalendarInput(this, event))"/>';
		output += '<button id="' + this.id + '_' + this.index + '_date-trigger">...</button>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	setup: function()
	{
		Calendar.setup({"inputField":this.id + '_' + this.index + '_date',"button":this.id + '_' + this.index + '_date-trigger',"singleClick":true,"name":this.id + '_' + this.index + '_date',"default":true});
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				operator: $F(this.id + '_' + this.index + '_operator'),
				date: $F(this.id + '_' + this.index + '_date')
			};
		return this.serialize(data);
	}
};

var FilterInstanceEndDate =
{
	id: 'instanceEndDate',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'A <strong>data de finalização da instância</strong> é <select id="' + this.id + '_' + this.index + '_operator">';
		output += '<option value="EQ">Igual a</option>';
		output += '<option value="LT">Antes de</option>';
		output += '<option value="GT">Depois de</option>';
		output += '</select>';
		output += ' <input type="text" id="' + this.id + '_' + this.index + '_date" name="' + this.id + '_' + this.index + '_date" value="" size="10" maxlength="10" onkeypress="return(formatCalendarInput(this, event))"/>';
		output += '<button id="' + this.id + '_' + this.index + '_date-trigger">...</button>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	setup: function()
	{
		Calendar.setup({"inputField":this.id + '_' + this.index + '_date',"button":this.id + '_' + this.index + '_date-trigger',"singleClick":true,"name":this.id + '_' + this.index + '_date',"default":true});
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				operator: $F(this.id + '_' + this.index + '_operator'),
				date: $F(this.id + '_' + this.index + '_date')
			};
		return this.serialize(data);
	}
};

var FilterInstanceStatus =
{
	id: 'instanceStatus',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'O <strong>status da instância</strong> é: ';
		output += ' <select id="' + this.id + '_' + this.index + '_status">';
		for (var i = 0; i < statusCorrelation.length; i++)
			output += '<option value="' + statusCorrelation[i]['id'] + '">' + statusCorrelation[i]['name'] + '</option>';
		output += '</select>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				status: $F(this.id + '_' + this.index + '_status')
			};
		return this.serialize(data);
	}
};

var FilterInstanceActivity =
{
	id: 'instanceActivity',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'A <strong>instância encontra-se na atividade</strong>: ';
		output += '<span id="dummy_' + this.index + '"></span>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	setup: function()
	{
		var filterIndex = this.index;
		var filterID = this.id;
		function resultInstanceActivitySetup(data)
		{
			var content = '';
			content += ' <select id="' + filterID + '_' + filterIndex + '_activity">';
			for (var i = 0; i < data['data'].length; i++)
				content += '<option value="' + data['data'][i]['id'] + '">' + data['data'][i]['name'] + '</option>';
			content += '</select>';

			new Insertion.Before($('dummy_' + filterIndex), content);
			$('dummy_' + filterIndex).remove();
		}
		cExecute('$this.bo_monitors.listActivities', resultInstanceActivitySetup, 'pid=' + workflowMonitorInstancesParams['pid']);
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				activity: $F(this.id + '_' + this.index + '_activity')
			};
		return this.serialize(data);
	}
};

var FilterInstanceUser =
{
	id: 'instanceUser',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'A <strong>instância encontra-se com o usuário</strong>: ';
		output += '<span id="dummy_' + this.index + '"></span>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	setup: function()
	{
		var filterIndex = this.index;
		var filterID = this.id;
		function resultInstanceUserSetup(data)
		{
			var content = '';
			content += ' <select id="' + filterID + '_' + filterIndex + '_user">';
			for (var i = 0; i < data['users'].length; i++)
				content += '<option value="' + data['users'][i]['id'] + '">' + data['users'][i]['name'] + '</option>';
			content += '</select>';

			new Insertion.Before($('dummy_' + filterIndex), content);
			$('dummy_' + filterIndex).remove();
		}
		cExecute("$this.bo_monitors.getUsersInInstances", resultInstanceUserSetup, 'pid=' + workflowMonitorInstancesParams['pid']);
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				user: $F(this.id + '_' + this.index + '_user')
			};
		return this.serialize(data);
	}
};

var FilterInstanceOwner =
{
	id: 'instanceOwner',
	generateHTML: function()
	{
		var output = '';
		output += '<div id="filter_' + this.index + '">';
		output += 'O <strong> proprietário da instância</strong> é: ';
		output += '<span id="dummy_' + this.index + '"></span>';
		output += '&nbsp;' + this.removeButtonHTML();
		output += '</div>';
		return output;
	},

	setup: function()
	{
		var filterIndex = this.index;
		var filterID = this.id;
		function resultInstanceOwnerSetup(data)
		{
			var content = '';
			content += ' <select id="' + filterID + '_' + filterIndex + '_owners">';
			for (var i = 0; i < data['owners'].length; i++)
				content += '<option value="' + data['owners'][i]['id'] + '">' + (data['owners'][i]['name'] ? data['owners'][i]['name'] : 'ID: ' + data['owners'][i]['id']) + '</option>';
			content += '</select>';

			new Insertion.Before($('dummy_' + filterIndex), content);
			$('dummy_' + filterIndex).remove();
		}
		cExecute("$this.bo_monitors.getInstancesOwners", resultInstanceOwnerSetup, 'pid=' + workflowMonitorInstancesParams['pid'] + '&currentList=' + workflowMonitorCurrentList);
	},

	ajaxData: function()
	{
		var data =
			{
				id: this.id,
				owner: $F(this.id + '_' + this.index + '_owners')
			};
		return this.serialize(data);
	}
};

function addFilterSelection(onlyCompletedInstances)
{
	var content = '';
	var index = filters.length;

	var objFilter = new Filter();
	objFilter.index = index;
	filters[index] = objFilter;

	content += '<div id="divFilterSelection_' + index + '">';
	content += '<select id="filterSelect_' + index + '">';
	if (!onlyCompletedInstances)
		content += '<option value="instanceActivity">Atividade da Instância</option>';
	content += '<option value="instanceDate">Data da Instância</option>';
	if (onlyCompletedInstances)
		content += '<option value="instanceEndDate">Data de Finalização da Instância</option>';
	if (!onlyCompletedInstances)
		content += '<option value="activityDate">Data na Atividade</option>';
	content += '<option value="instanceID">ID da Instância</option>';
	content += '<option value="instanceName">Identificador</option>';
	content += '<option value="instancePriority">Prioridade da Instância</option>';
	content += '<option value="instanceOwner">Proprietário da Instância</option>';
	content += '<option value="instanceStatus">Status da Instância</option>';
	if (!onlyCompletedInstances)
		content += '<option value="instanceUser">Usuário da Instância</option>';
	content += '</select>';
	content += '&nbsp;<a href="javascript:addFilter(' + index + ')"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/apply.png"/></a>';
	content += '&nbsp;' + objFilter.removeButtonHTML();
	content += '</div>';
	new Insertion.Before($('filterMenu'), content)
}

function addFilter(index)
{
	var div = $('divFilterSelection_' + index);
	var selectBox = $('filterSelect_' + index);
	var selected = $F(selectBox);
	var content = '';

	switch (selected)
	{
		case 'activityDate':
			Object.extend(filters[index], FilterActivityDate);
			break;

		case 'instanceName':
			Object.extend(filters[index], FilterInstanceName);
			break;

		case 'instanceID':
			Object.extend(filters[index], FilterInstanceID);
			break;

		case 'instancePriority':
			Object.extend(filters[index], FilterInstancePriority);
			break;

		case 'instanceDate':
			Object.extend(filters[index], FilterInstanceDate);
			break;

		case 'instanceEndDate':
			Object.extend(filters[index], FilterInstanceEndDate);
			break;

		case 'instanceStatus':
			Object.extend(filters[index], FilterInstanceStatus);
			break;

		case 'instanceActivity':
			Object.extend(filters[index], FilterInstanceActivity);
			break;

		case 'instanceUser':
			Object.extend(filters[index], FilterInstanceUser);
			break;

		case 'instanceOwner':
			Object.extend(filters[index], FilterInstanceOwner);
			break;
	}
	content += filters[index].generateHTML();
	new Insertion.Before(div, content);

	filters[index].setup();
	div.remove();
}

function removeFilter(index)
{
	filters[index].remove();
	filters[index] = null;
}

function filterInstances(completedInstances)
{
	var ajaxData = new Array();
	var currentData = '';
	for (var i = 0; i < filters.length; i++)
	{
		/* ignora se o filtro foi removido */
		if (filters[i] == null)
			continue;

		/* verifica se os critérios foram satisfeitos */
		currentData = filters[i].ajaxData();
		if (currentData == '')
			continue;

		/* filtro com JSON */
		ajaxData[ajaxData.length] = filters[i].ajaxData();
	}

	if (completedInstances)
	{
		if (ajaxData.length > 0)
			callCompletedInstanceList(workflowMonitorInstancesParams['pid'], workflowMonitorInstancesParams['str'], 0, JSON.stringify(ajaxData));
		else
			callCompletedInstanceList(workflowMonitorInstancesParams['pid'], workflowMonitorInstancesParams['str'], 0);
	}
	else
	{
		if (ajaxData.length > 0)
			callInstanceList(workflowMonitorInstancesParams['pid'], workflowMonitorInstancesParams['str'], 0, JSON.stringify(ajaxData));
		else
			callInstanceList(workflowMonitorInstancesParams['pid'], workflowMonitorInstancesParams['str'], 0);
	}
}
