String.prototype.repeat = function(l)
{
	return new Array(l+1).join(this);
};

var workflowOrgchartAdminEmployeeInfoTimer = null;
var workflowOrgchartAdminAreaInfoTimer = null;

var CadastroAjax = Class.create();

CadastroAjax.prototype =
{
	initialize: function()
	{
		this.name = '';
		this.required = new Array();
		this.tableHeader = new Array();
		this.combo = new Array();
	},

	add: function()
	{
		for (var i = 0; i < this.required.length; i++)
		{
			if ($F(this.required[i]) == '')
			{
				alert("Campo necessário ausente: " + $(this.required[i]).parentNode.parentNode.childNodes[0].childNodes[0].innerHTML);
				$(this.required[i]).focus();
				return;
			}
		}
		var cb = function(data)
		{
			if (!handleError(data))
				return;

			/* update the screen info */
			if (this.name.toLowerCase() == "organization")
				listOrganizations();
			else
			{
				if ($('organizacao_id'))
				{
					var index = $F('organizacao_id');
					if (refreshAreas[index])
						refreshAreas[index]();
				}
			}

			valid.deactivate();
		};

		return this.ajaxAction('add', cb, Form.serialize($('orgchartForm')));
	},

	update: function()
	{
		for (var i = 0; i < this.required.length; i++)
		{
			if ($F(this.required[i]) == '')
			{
				alert("Campo necessário ausente: " + $(this.required[i]).parentNode.parentNode.childNodes[0].childNodes[0].innerHTML);
				$(this.required[i]).focus();
				return;
			}
		}
		var cb = function(data)
		{
			if (!handleError(data))
				return;

			/* update the screen info */
			if (this.name.toLowerCase() == "organization")
				listOrganizations();
			else
			{
				if ($('organizacao_id'))
				{
					var index = $F('organizacao_id');
					if (refreshAreas[index])
						refreshAreas[index]();
				}
			}

			valid.deactivate();
		};

		return this.ajaxAction('update', cb, Form.serialize($('orgchartForm')));
	},

	remove: function(params, linkRemove)
	{

		if (linkRemove.addClassName)
			linkRemove.addClassName('alerta');
		if (confirm("Tem certeza que deseja excluir o registro selecionado?"))
		{
			var cb = function(data)
			{
				if (!handleError(data))
					return;

				/* update the screen info */
				if (this.name.toLowerCase() == "organization")
					listOrganizations();
				else
				{
					var index = $('organizacao_id') ? $F('organizacao_id') : params['organizacao_id'];
					if (refreshAreas[index])
						refreshAreas[index]();
				}

				if ($('lbContent'))
					valid.deactivate();
			};
			return this.ajaxAction('remove', cb, $H(params).toQueryString());
		}
		else
			if (linkRemove.removeClassName)
				linkRemove.removeClassName('alerta');
	},

	list: function(callback, params)
	{
		return this.ajaxAction('list', callback, $H(params).toQueryString());
	},

	generateTable: function(params, displayArea)
	{
		tableHeader = this.tableHeader;
		var tableResult = function(data)
		{
			if (!handleError(data))
				return;

			if (data.length == 0)
			{
				displayArea.innerHTML += '<br/><br/><center><strong>Nenhum registro encontrado</strong></center>';
				return true;
			}

			for (var i = 0; i < data.length; i++)
			{
				data[i]['tr_attributes'] = new Array();
				data[i]['tr_attributes']['class'] = "linha" + i%2;;
				data[i]['tr_attributes']['className'] = "linha" + i%2;;
			}

			var tableAtributes = new Array();
			tableAtributes['id'] = this.name + 'List';
			tableAtributes['class'] = 'organizationList';
			tableAtributes['className'] = 'organizationList';
			displayArea.appendChild(constructTable(tableHeader, data, tableAtributes));
		};
		this.list(tableResult, params);
	},

	generateUpdateTable: function(params, displayArea)
	{
		tableHeader = this.tableHeader;
		if (!tableHeader['actions'])
			tableHeader['actions'] = "Ações";
		name = this.name.charAt(0).capitalize() + this.name.substr(1);
		var tableResult = function(data)
		{
			if (!handleError(data))
				return;

			if (data.length == 0)
			{
				//displayArea.innerHTML += '<br/><br/><center><strong>Nenhum registro encontrado</strong></center>';
				elem = document.createElement('div');
				elem.id = this.name.capitalize() + 'List';
				elem.innerHTML = '<br/><br/><center><strong>Nenhum registro encontrado</strong></center>';
				displayArea.appendChild(elem);
				return true;
			}

			for (var i = 0; i < data.length; i++)
			{
				var dataHash = new Hash();
				for (j in data[i])
					if (typeof data[i][j] != "function")
					{
						dataHash[j] = data[i][j];
						if ( dataHash[j] == null )
							dataHash[j] = '';
					}

				for (j in tableHeader)
					if (typeof tableHeader[j] != "function")
						if (j != 'actions')
							data[i][j] = '<a href="#" onclick="obj' + name + '.fillForm(' + dataHash.customInspect() + '); return false;">' + data[i][j] + '</a>';

				data[i]['tr_attributes'] = new Array();
				data[i]['tr_attributes']['class'] = "linha" + i%2;
				data[i]['tr_attributes']['className'] = "linha" + i%2;
				data[i]['actions'] = '<a href="#" onclick="obj' + name + '.remove(' + dataHash.customInspect() + ' , this.parentNode.parentNode); return false;"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/button_cancel.png" /></a>';
			}

			var tableAtributes = new Array();
			tableAtributes['id'] = this.name + 'List';
			tableAtributes['class'] = 'orgchartUpdateTable';
			tableAtributes['className'] = 'orgchartUpdateTable';
			displayArea.appendChild(constructTable(tableHeader, data, tableAtributes));
		};
		this.list(tableResult, params);
	},

	fillForm: function(dataHash)
	{
		dataHash = $H(dataHash);
		dataHash.each(function(pair)
		{
			var campo = $(pair.key);
			if (campo)
			{
				if ((campo.nodeName == "SELECT") && (pair.value == null))
					campo.value = campo.firstChild.value;
				else
					if (pair.value != null)
						campo.value = pair.value;
			}
			else
				new Insertion.Bottom($('orgchartForm'), '<input type="hidden" name="' + pair.key + '" id="' + pair.key + '" value="' + pair.value + '" />');
		});
		if (!$('updateOrgchart'))
			new Insertion.Bottom($('orgchartForm'), '<input type="hidden" name="updateOrgchart" id="updateOrgchart" value="true" />');
		var saveButton = $('inserir');
		saveButton.innerHTML = "Salvar";
		var name = this.name;
		saveButton.onclick = function(){ eval('obj' + name.charAt(0).capitalize() + name.substr(1) + '.update();')};
		var titleObject = $('modalTitle');
		var title = titleObject.innerHTML.split(' ');
		title[0] = "Atualizar";
		titleObject.innerHTML = title.join(' ');
		$('lightbox').scrollTop = 0;
	},

	generateComboBox: function(params, displayArea, includeNull, name, callback)
	{
		if (!name)
			name = this.combo['id'];
		
		combo = this.combo;

		var comboResult = function(data)
		{
			if (!handleError(data))
				return;

			var newFormat = new Array();
			if (includeNull)
				newFormat['NULL'] = "Nenhum";

			for (var i = 0; i < data.length; i++)
				newFormat[data[i][combo['id']]] = data[i][combo['name']];

			if ((newFormat.length > 0) || includeNull)
			{
				displayArea.innerHTML = constructSelectBox(name, newFormat);
			}
			else
			{
				displayArea.innerHTML = '<i>nenhum registro encontado</i><input type="hidden" id="' + name + '" value=""/>';
			}

			if (callback)
				callback();
		};

		this.list(comboResult, params);
	},

	ajaxAction: function(action, callback, params)
	{
		if (params == "")
			cExecute('$this.bo_orgchart.' + action + this.name.charAt(0).capitalize() + this.name.substr(1), callback);
		else
			cExecute('$this.bo_orgchart.' + action + this.name.charAt(0).capitalize() + this.name.substr(1), callback, params);
	}

};

var CadastroOrganization =
{
	name: 'organization',
	required: new Array('nome', 'descricao', 'ativa'),
	tableHeader: {'nome': 'Organização'},
	combo: {'id': 'organizacao_id', 'name': 'nome'}
};
var objOrganization = new CadastroAjax();
Object.extend(objOrganization, CadastroOrganization);

var CadastroEmployeeStatus =
{
	name: 'employeeStatus',
	required: new Array('descricao', 'exibir'),
	tableHeader: {'descricao': 'Status de Funcionário'},
	combo: {'id': 'funcionario_status_id', 'name': 'descricao'}
};
var objEmployeeStatus = new CadastroAjax();
Object.extend(objEmployeeStatus, CadastroEmployeeStatus);

var CadastroEmployeeCategory =
{
	name: 'employeeCategory',
	required: new Array('organizacao_id', 'descricao'),
	tableHeader: {'descricao': 'Nome'},
	combo: {'id': 'funcionario_categoria_id', 'name': 'descricao'}
};
var objEmployeeCategory = new CadastroAjax();
Object.extend(objEmployeeCategory, CadastroEmployeeCategory);

var CadastroJobTitle =
{
	name: 'jobTitle',
	required: new Array('organizacao_id', 'descricao'),
	tableHeader: {'descricao': 'Nome'},
	combo: {'id': 'cargo_id', 'name': 'descricao'}
};
var objJobTitle = new CadastroAjax();
Object.extend(objJobTitle, CadastroJobTitle);

var CadastroAreaStatus =
{
	name: 'areaStatus',
	required: new Array('organizacao_id', 'descricao', 'nivel'),
	tableHeader: {'descricao': 'Nome', 'nivel': 'Nível'},
	combo: {'id': 'area_status_id', 'name': 'descricao'}
};
var objAreaStatus = new CadastroAjax();
Object.extend(objAreaStatus, CadastroAreaStatus);

var CadastroCostCenter =
{
	name: 'costCenter',
	required: new Array('organizacao_id', 'nm_centro_custo', 'descricao', 'grupo'),
	tableHeader: {'nm_centro_custo': 'Número', 'descricao': 'Nome', 'grupo': 'Grupo'},
	combo: {'id': 'centro_custo_id', 'name': 'descricao'}
};
var objCostCenter = new CadastroAjax();
Object.extend(objCostCenter, CadastroCostCenter);

var CadastroLocal =
{
	name: 'local',
	required: new Array('organizacao_id', 'descricao'),
	tableHeader: {'descricao': 'Localidade'},
	combo: {'id': 'localidade_id', 'name': 'descricao'}
};
var objLocal = new CadastroAjax();
Object.extend(objLocal, CadastroLocal);

var CadastroEmployee =
{
	name: 'employee',
	required: new Array('funcionario_id', 'organizacao_id', 'funcionario_status_id', 'centro_custo_id', 'localidade_id', 'area_id'),
	tableHeader: {'funcionario_id': 'Funcionário'},
	combo: {'id': 'funcionario_id', 'name': 'funcionario_id'}
};
var objEmployee = new CadastroAjax();
Object.extend(objEmployee, CadastroEmployee);

var CadastroArea =
{
	name: 'area',
	required: new Array('organizacao_id', 'area_status_id', 'centro_custo_id', 'superior_area_id', 'sigla', 'descricao', 'ativa'),
	tableHeader: {'sigla': 'Área'},
	combo: {'id': 'area_id', 'name': 'sigla'}
};
var objArea = new CadastroAjax();
Object.extend(objArea, CadastroArea);

var CadastroTelefone =
{
	name: 'telephones',
	required: new Array('organizacao_id', 'descricao', 'numero'),
	tableHeader: {'descricao': 'Descrição', 'numero': 'Telefones'},
	combo: {'id': 'telefone_id', 'name': 'descricao'}
};
var objTelephones = new CadastroAjax();
Object.extend(objTelephones, CadastroTelefone);

var CadastroSubstituto =
{
	name: 'substitution',
	required: new Array('organizacao_id', 'area_id', 'descricao', 'titular_funcionario_id', 'substituto_funcionario_id', 'data_inicio', 'data_fim'),
	tableHeader: {'substituto_funcionario_id_desc': 'Nome', 'data_inicio': 'Data de início', 'data_fim': 'Data de término'},
	combo: {}
};
var objSubstitution = new CadastroAjax();
Object.extend(objSubstitution, CadastroSubstituto);


function createOrganizationLayout(organizationID, organizationDiv)
{
	organizationDiv.innerHTML = '<div id="orgchartMenu_' + organizationID + '"></div>';
	organizationDiv.innerHTML += '<div class="orgchartAreas" id="orgchartAreas_' + organizationID + '"></div>';
	organizationDiv.innerHTML += '<div class="orgchartEmployees" id="orgchartEmployees_' + organizationID + '"></div>';
	organizationDiv.innerHTML += '<div class="orgchartFooter"></div>';
	organizationDiv.innerHTML += '<div id="employeeInfo" class="employeeInfo" style="display: none;"></div>';
	organizationDiv.innerHTML += '<div id="areaInfo" class="employeeInfo" style="display: none;"></div>';

	createOrganizationMenu(organizationID, $('orgchartMenu_' + organizationID));
	loadOrganizationAreas(organizationID, $('orgchartAreas_' + organizationID));
	lb_initialize();
}

function createOrganizationMenu(organizationID, div)
{
	var content  = '<ul class="horizontalMenu">';
		content += '<li style="margin: 5px 5px 0 5px">Atualizar : <select name="atualizar" id="ddlAtualizar" onchange="loadAdds(this.value, ' + organizationID + ');">';
		content += '<option></option>';
		content += '<option value="loadAddEmployeeStatusUI">Status de Funcionário</option>';
		content += '<option value="loadAddEmployeeCategoryUI">Categorias</option>';
		content += '<option value="loadAddJobTitleUI">Cargos</option>';
		content += '<option value="loadAddAreaStatusUI">Status de Área</option>';
		content += '<option value="loadAddCostCenterUI">Centros de Custo</option>';
		content += '<option value="loadAddLocalUI">Localidade</option>';
		content += '<option value="loadAddAreaUI">Áreas</option>';
		content += '<option value="loadAddEmployeeUI">Funcionários</option>';
		content += '<option value="loadAddTelephoneUI">Telefones</option>';
		content += '<option value="loadAddSubstitutionUI">Substituições</option>';
		content += '</select></li>';
		content += '<li><a><input type="text" name="search_term" id="search_term" onkeypress="if (((window.Event) ? event.which : event.keyCode) == 13) $(\'search_span_' + organizationID  + '\').onclick(); return true;" /> <span id="search_span_' + organizationID + '" onclick="tmp = $$(\'div#orgchartAreas_' + organizationID + ' a.destaque\'); if (tmp[0]) tmp[0].removeClassName(\'destaque\'); searchEmployee(' + organizationID + ', $(\'orgchartEmployees_' + organizationID + '\')); return false;">busca</span></a></li>';
		content += '</ul>';
		content += '<br/>';
		content += '<br/>';

	div.innerHTML = content;
}

function loadAdds(eventName, organizationID)
{
	if (eventName == '' || eventName == undefined || eventName == null)
		return false;

	window.settings = { functionName: eventName };

	var b = document.createElement('button');
	    b.className = 'lbOn';

	if (!$('overlay'))
		addLightboxMarkup();
        
	var valid = new lightbox(b);
		valid.activate();

	window[settings.functionName](organizationID);
}

function loadOrganizationAreas(organizationID, div)
{
	var loadOrganizationAreasResult = function(data)
	{
		function recursivePrint(subdata)
		{
			for (var i = 0; i < subdata.length; i++)
			{
				div.innerHTML += '<br />' + '&nbsp;&nbsp;&nbsp;&nbsp;'.repeat(subdata[i]['depth']) + '<a href="javascript:void(0)" id="area_' + subdata[i]['area_id'] + '" onmouseover="getAreaInfoTimer(event, ' + subdata[i]['area_id'] + ', ' + organizationID + '); return false;" onmouseout="hideAreaInfo(); return false;" onclick="tmp = $$(\'div#orgchartAreas_' + organizationID + ' a.destaque\'); if (tmp[0]) tmp[0].removeClassName(\'destaque\'); this.addClassName(\'destaque\'); loadAreaEmployees(' + organizationID + ', ' + subdata[i]['area_id'] + ', $(\'orgchartEmployees_' + organizationID + '\'))">' + subdata[i]['sigla'] + '</a>';
				if (subdata[i]['children'].length > 0)
					recursivePrint(subdata[i]['children']);
			}
		}

		if (!handleError(data))
			return;

		if (data.length == 0)
		{
			div.innerHTML = "<br/><br/><center><strong>Nenhuma área cadastrada.</strong></center><br/><br/>";
			return;
		}

		div.innerHTML = "<center><strong>ÁREAS</strong></center>";
		recursivePrint(data);
		if (refreshEmployees[organizationID])
			refreshEmployees[organizationID]();
	};

	objArea.ajaxAction('listHierarchical', loadOrganizationAreasResult, $H({'organizacao_id': organizationID}).toQueryString());
	refreshAreas[organizationID] = function(){objArea.ajaxAction('listHierarchical', loadOrganizationAreasResult, $H({'organizacao_id': organizationID}).toQueryString());};
}

function searchEmployee(organizationID, div)
{
	var searchEmployeeResult = function(data)
	{
		if (!handleError(data))
			return;

		div.innerHTML = "";
		if (data.length == 0)
		{
			div.innerHTML = "<br/><br/><center><strong>Nenhum funcionário encontrado.</strong></center>";
			return;
		}

		var tableHeader = new Array();
		tableHeader['funcionario_id_desc'] = 'Funcionário';
		tableHeader['area_sigla'] = 'Área';
		tableHeader['uid'] = 'UID';
		tableHeader['actions'] = 'Ações';
		for (var i = 0; i < data.length; i++)
		{
			var dataHash = new Hash();
			for (j in data[i])
				if (typeof data[i][j] != "function")
					dataHash[j] = data[i][j];

			data[i]['tr_attributes'] = new Array();
			data[i]['tr_attributes']['class'] = "linha" + i%2;
			data[i]['tr_attributes']['className'] = "linha" + i%2;
			data[i]['funcionario_id_desc'] = '<a href="javascript:void(0)" class="lbOn" onmouseover="getEmployeeInfoTimer(event, ' + data[i]['funcionario_id'] + ', ' + organizationID + '); return false;" onmouseout="hideEmployeeInfo(); return false;" onclick="loadAddEmployeeUI(' + organizationID + ', function(){objEmployee.fillForm(' + dataHash.customInspect() + ')}); $(\'addEmployeeLink\').parentNode.removeChild($(\'addEmployeeLink\')); return false;">' + data[i]['funcionario_id_desc'] + '</a>' + (data[i]['removed'] ? ' <font color="red">(inativo)</font>' : '');
			data[i]['actions'] = '<a href="#" onclick="objEmployee.remove({\'funcionario_id\': ' + data[i]['funcionario_id'] + ', \'organizacao_id\': ' + organizationID + '} , this.parentNode.parentNode); return false;"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/button_cancel.png" /></a>';
		}

		var tableAtributes = new Array();
		tableAtributes['class'] = 'employeeList';
		tableAtributes['className'] = 'employeeList';
		div.appendChild(constructTable(tableHeader, data, tableAtributes));
		lb_initialize();
	};

	objEmployee.ajaxAction('search', searchEmployeeResult, $H({'organizacao_id': organizationID, 'search_term': $F('search_term')}).toQueryString());
	refreshEmployees[organizationID] = function(){objEmployee.ajaxAction('search', searchEmployeeResult, $H({'organizacao_id': organizationID, 'search_term': $F('search_term')}).toQueryString());};
}

function loadAreaEmployees(organizationID, areaID, div)
{
	var loadAreaEmployeesResult = function(data)
	{
		if (!handleError(data))
			return;

		var areaLink = $('area_' + areaID);
		if (!areaLink.hasClassName('destaque'))
			areaLink.addClassName('destaque');

		div.innerHTML = "";
		if (data.length == 0)
		{
			div.innerHTML = "<br/><br/><center><strong>Nenhum funcionário alocado nesta área.</strong></center>";
			return;
		}

		var tableHeader = new Array();
		tableHeader['funcionario_id_desc'] = 'Funcionário';
		tableHeader['uid'] = 'UID';
		tableHeader['actions'] = 'Ações';

		var complement;
		for (var i = 0; i < data.length; i++)
		{
			var dataHash = new Hash();
			for (j in data[i])
				if (typeof data[i][j] != "function")
					dataHash[j] = data[i][j];

			// are you a chief ('titular' or 'substituto')?
			complement = '';
			if (data[i]['chief'])
				complement = ' <strong>(' + ((data[i]['chief'] == 1) ? 'Titular' : 'Substituto') + ')</strong>';

			data[i]['tr_attributes'] = new Array();
			data[i]['tr_attributes']['class'] = "linha" + i%2;
			data[i]['tr_attributes']['className'] = "linha" + i%2;
			data[i]['funcionario_id_desc'] = '<a href="javascript:void(0)" class="lbOn" onmouseover="getEmployeeInfoTimer(event, ' + data[i]['funcionario_id'] + ', ' + organizationID + '); return false;" onmouseout="hideEmployeeInfo(); return false;" onclick="loadAddEmployeeUI(' + organizationID + ', function(){objEmployee.fillForm(' + dataHash.customInspect() + ')}); $(\'addEmployeeLink\').parentNode.removeChild($(\'addEmployeeLink\')); return false;">' + data[i]['funcionario_id_desc'] + ' ' + complement + '</a>' + (data[i]['removed'] ? ' <font color="red">(inativo)</font>' : '');
			data[i]['actions'] = '<a href="#" onclick="objEmployee.remove({\'funcionario_id\': ' + data[i]['funcionario_id'] + ', \'organizacao_id\': ' + organizationID + '} , this.parentNode.parentNode); return false;"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/button_cancel.png" /></a>';
			window.scrollTo(0,0);
		}

		var tableAtributes = new Array();
		tableAtributes['class'] = 'employeeList';
		tableAtributes['className'] = 'employeeList';
		div.appendChild(constructTable(tableHeader, data, tableAtributes));

		lb_initialize();
	};

	objEmployee.ajaxAction('listArea', loadAreaEmployeesResult, $H({'area_id': areaID, 'organizacao_id': organizationID}).toQueryString());
	refreshEmployees[organizationID] = function(){objEmployee.ajaxAction('listArea', loadAreaEmployeesResult, $H({'area_id': areaID, 'organizacao_id': organizationID}).toQueryString());};
}

function loadAddEmployeeStatusUI(organizationID)
{
	var valoresSimNao = new Array();
	valoresSimNao['S'] = 'Sim';
	valoresSimNao['N'] = 'Não';

	var content;
	content = '<h2 id="modalTitle">Adicionar Status de Funcionário</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += "<table>";
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" size="30" /></td></tr>';
	content += '<tr><td><label for="exibir">Exibir para o usuário</label></td><td>' + constructSelectBox('exibir', valoresSimNao) + '</td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objEmployeeStatus.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	//$('descricao').focus();
	objEmployeeStatus.generateUpdateTable({'organizacao_id': organizationID}, divLB);
}

function loadAddEmployeeCategoryUI(organizationID)
{
	var content;
	content = '<h2 id="modalTitle">Adicionar Categoria</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += '<table>';
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" size="30" /></td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objEmployeeCategory.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	//$('descricao').focus();
	objEmployeeCategory.generateUpdateTable({'organizacao_id': organizationID}, divLB);
}

function loadAddJobTitleUI(organizationID)
{
	var content;
	content = '<h2 id="modalTitle">Adicionar Cargos</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += '<table>';
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" size="30" /></td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objJobTitle.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	//$('descricao').focus();
	objJobTitle.generateUpdateTable({'organizacao_id': organizationID}, divLB);
}

function loadAddAreaStatusUI(organizationID)
{
	var content;
	content = '<h2 id="modalTitle">Adicionar Status de Área</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += '<table>';
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" size="30" /></td></tr>';
	content += '<tr><td><label for="nivel">Nível</label></td><td><input type="text" name="nivel" id="nivel" size="3" /></td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objAreaStatus.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	//$('descricao').focus();
	objAreaStatus.generateUpdateTable({'organizacao_id': organizationID}, divLB);
}

function loadAddCostCenterUI(organizationID)
{
	var content;
	content = '<h2 id="modalTitle">Adicionar Centro de Custo</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += "<table>";
	content += '<tr><td><label for="nm_centro_custo">Número</label></td><td><input type="text" name="nm_centro_custo" id="nm_centro_custo" size="4" /></td></tr>';
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" size="30" /></td></tr>';
	content += '<tr><td><label for="grupo">Grupo</label></td><td><input type="text" name="grupo" id="grupo" size="10" /></td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objCostCenter.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	//$('nm_centro_custo').focus();
	objCostCenter.generateUpdateTable({'organizacao_id': organizationID}, divLB);
}

function loadAddLocalUI(organizationID)
{
	var valoresSimNao = new Array();
	valoresSimNao['S'] = 'Sim';
	valoresSimNao['N'] = 'Não';

	var content;
	content = '<h2 id="modalTitle">Adicionar Localidade</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += "<table>";
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" size="30" /></td></tr>';

	content += '<tr><td><label for="empresa">Empresa</label></td><td><input type="text" name="empresa" id="empresa" size="80" /></td></tr>';
	content += '<tr><td><label for="endereco">Endereço</label></td><td><input type="text" name="endereco" id="endereco" size="80" /></td></tr>';
	content += '<tr><td><label for="complemento">Complemento</label></td><td><input type="text" name="complemento" id="complemento" size="50" /></td></tr>';
	content += '<tr><td><label for="cep">Cep</label></td><td><input type="text" name="cep" id="cep" size="10" /></td></tr>';
	content += '<tr><td><label for="bairro">Bairro</label></td><td><input type="text" name="bairro" id="bairro" size="30" /></td></tr>';
	content += '<tr><td><label for="cidade">Cidade</label></td><td><input type="text" name="cidade" id="cidade" size="50" /></td></tr>';
	content += '<tr><td><label for="uf">UF</label></td><td><input type="text" name="uf" id="uf" size="2" maxlength="2" /></td></tr>';

	content += '<tr><td><label for="centro_custo_id">Centro de Custo</label></td><td id="comboCentroCusto"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="externa">Externa à organização</label></td><td>' + constructSelectBox('externa', valoresSimNao) + '</td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objLocal.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	//$('descricao').focus();
	objCostCenter.generateComboBox({'organizacao_id': organizationID}, $('comboCentroCusto'), true, null,
		function()
		{
			objLocal.generateUpdateTable({'organizacao_id': organizationID}, divLB);
		}
	);
}

function loadAddEmployeeUI(organizationID, callback)
{
	var content;
	content = '<h2 id="modalTitle">Adicionar Funcionário</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += "<table>";
	content += '<tr><td><label for="funcionario_id_desc">Funcionário</label></td><td>';
	content += '<input type="hidden" name="funcionario_id" id="funcionario_id" value="" />';
	content += '<input type="input" name="funcionario_id_desc" id="funcionario_id_desc" value="" readonly="true" size="40" />';
	content += '<a href="javascript:void(0)" onclick="openParticipantsWindow(\'funcionario_id\', \'uid=1&hidegroups=1\');" id="addEmployeeLink"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/add_user.png" /></a>';
	content += '</td></tr>';
	content += '<tr><td><label for="funcionario_status_id">Status</label></td><td id="comboStatus"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="apelido">Apelido</label></td><td><input type="text" size="20" maxlength="20" name="apelido" id="apelido" value=""/></td></tr>';
	content += '<tr><td><label for="funcionario_categoria_id">Categoria</label></td><td id="comboFuncionarioCategoria"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="cargo_id">Cargo</label></td><td id="comboCargo"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="funcao">Função</label></td><td><input type="text" size="50" maxlength="200" name="funcao" id="funcao" value=""/></td></tr>';
	content += '<tr><td><label for="data_admissao">Data de admissão</label></td><td><input type="text" name="data_admissao" id="data_admissao" value="" size="15" onkeypress="return formatDateField(event, this);" /></td></tr>';
	content += '<tr><td><label for="titulo">T&iacute;tulo</label></td><td><input type="text" size="30" name="titulo" id="titulo" value=""/></td></tr>';
	content += '<tr><td><label for="nivel">Nível</label></td><td><input type="text" size="3" name="nivel" id="nivel"/></td></tr>';
	content += '<tr><td><label for="area_id">Área</label></td><td id="comboArea"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="centro_custo_id">Centro de Custo</label></td><td id="comboCentroCusto"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="localidade_id">Localidade</label></td><td id="comboLocalidade"><i>carregando</i></td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objEmployee.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';
	
	var divLB = $('lbContent');
    divLB.innerHTML = content;

    objEmployeeStatus.generateComboBox({'organizacao_id': organizationID}, $('comboStatus'), false, null,
        function()
        {
            objEmployeeCategory.generateComboBox({'organizacao_id': organizationID}, $('comboFuncionarioCategoria'), true, null,
                function()
                {
                    objJobTitle.generateComboBox({'organizacao_id': organizationID}, $('comboCargo'), true, null,
                        function()
                        {
                            objArea.generateComboBox({'organizacao_id': organizationID}, $('comboArea'), false, null,
                                function()
                                {
                                    objCostCenter.generateComboBox({'organizacao_id': organizationID}, $('comboCentroCusto'), true, null,
                                        function()
                                        {
                                            objLocal.generateComboBox({'organizacao_id': organizationID}, $('comboLocalidade'), false, null, callback);
                                        }
                                    )
                                }
                            )
                        }
                    )
                }
            )
        }
    );
}

function loadAddAreaUI(organizationID)
{
	var valoresSimNao = new Array();
	valoresSimNao['S'] = 'Sim';
	valoresSimNao['N'] = 'Não';

	var content;
	content = '<h2 id="modalTitle">Adicionar Área</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += "<table>";
	content += '<tr><td><label for="sigla">Sigla</label></td><td><input type="text" name="sigla" id="sigla" size="15" /></td></tr>';
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" size="30" /></td></tr>';
	content += '<tr><td><label for="titular_funcionario_id">Titular</label></td><td>';
	content += '<input type="hidden" name="titular_funcionario_id" id="titular_funcionario_id" value="" />';
	content += '<input type="input" name="titular_funcionario_id_desc" id="titular_funcionario_id_desc" value="" readonly="true" size="40" />';
	content += '<a href="javascript:void(0)" onclick="openParticipantsWindow(\'titular_funcionario_id\', \'uid=1&hidegroups=1\');"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/add_user.png" /></a>';
	content += ' <a href="javascript:void(0)" onclick="$(\'titular_funcionario_id\').value=\'\'; $(\'titular_funcionario_id_desc\').value=\'\'; return false;"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/delete_user.png" /></a>';
	content += '</td></tr>';
	content += '<tr><td><label for="auxiliar_funcionario_id">Auxiliar Administrativo</label></td><td>';
	content += '<input type="hidden" name="auxiliar_funcionario_id" id="auxiliar_funcionario_id" value="" />';
	content += '<input type="input" name="auxiliar_funcionario_id_desc" id="auxiliar_funcionario_id_desc" value="" readonly="true" size="40" />';
	content += '<a href="javascript:void(0)" onclick="openParticipantsWindow(\'auxiliar_funcionario_id\', \'uid=1&hidegroups=1\');"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/add_user.png" /></a>';
	content += ' <a href="javascript:void(0)" onclick="$(\'auxiliar_funcionario_id\').value=\'\'; $(\'auxiliar_funcionario_id_desc\').value=\'\'; return false;"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/delete_user.png" /></a>';
	content += '</td></tr>';
	content += '<tr><td><label for="area_status_id">Status</label></td><td id="comboStatus"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="superior_area_id">Área Superior</label></td><td id="comboArea"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="centro_custo_id">Centro de Custo</label></td><td id="comboCentroCusto"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="ativa">Ativa</label></td><td>' + constructSelectBox('ativa', valoresSimNao) + '</td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objArea.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	$('sigla').focus();

	objAreaStatus.generateComboBox({'organizacao_id': organizationID}, $('comboStatus'), false, null,
		function()
		{
			objArea.generateComboBox({'organizacao_id': organizationID}, $('comboArea'), true, 'superior_area_id',
				function()
				{
					objCostCenter.generateComboBox({'organizacao_id': organizationID}, $('comboCentroCusto'), false, null,
						function()
						{
							objArea.generateUpdateTable({'organizacao_id': organizationID}, divLB);
						}
					)
				}
			)
		}
	);
}

function loadAddTelephoneUI(organizationID)
{
	var content;
	content = '<h2 id="modalTitle">Adicionar Telefones da Organização</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += '<table>';
	content += '<tr><td><label for="descricao">Descrição</label></td><td><input type="text" name="descricao" id="descricao" size="50" /></td></tr>';
	content += '<tr><td><label for="nivel">Telefones</label></td><td><input type="text" name="numero" id="numero" size="50" /></td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objTelephones.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $( 'lbContent' );
	divLB.innerHTML = content;
	//$( 'descricao' ).focus( );
	objTelephones.generateUpdateTable( { 'organizacao_id' : organizationID }, divLB );
}


function loadAddSubstitutionUI(organizationID)
{
	var area_id = 'combo_area';
	var content;

	content  = '<h2 id="modalTitle">Adicionar Substituição</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += '<input type="hidden" name="organizacao_id" id="organizacao_id" value="' + organizationID + '" />';
	content += "<table>";
	content += '<tr><td><label for="area_id">Sigla</label>';
	content += '<input type="hidden" name="area_id" id="area_id" value="" />';
	content += '</td><td id="comboArea"><i>carregando</i></td></tr>';
	content += '<tr><td><label for="descricao">Nome</label></td><td><input type="text" name="descricao" id="descricao" readonly="true" size="40" /></td></tr>';
	content += '<tr><td><label for="titular_funcionario_id">Titular</label></td><td>';
	content += '<input type="hidden" name="titular_funcionario_id" id="titular_funcionario_id" value="" readonly="true" />';
	content += '<input type="input" name="titular_funcionario_id_desc" id="titular_funcionario_id_desc" value="" readonly="true" size="40" />';
	content += '</td></tr>';
	content += '<tr><td><label for="substituto_funcionario_id">Substituto</label></td><td>';
	content += '<input type="hidden" name="substituto_funcionario_id" id="substituto_funcionario_id" value="" />';
	content += '<input type="input" name="substituto_funcionario_id_desc" id="substituto_funcionario_id_desc" value="" readonly="true" size="40" />';
	content += '<a href="javascript:void(0)" onclick="openParticipantsWindow(\'substituto_funcionario_id\', \'uid=1&hidegroups=1\');"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/add_user.png" /></a>';
	content += ' <a href="javascript:void(0)" onclick="$(\'substituto_funcionario_id\').value=\'\'; $(\'substituto_funcionario_id_desc\').value=\'\'; return false;"><img border="0" width="16" height="16" src="workflow/templateFile.php?file=images/delete_user.png" /></a>';
	content += '</td></tr>';
	content += '<tr><td><label for="data_inicio">Data de início</label></td><td><input type="text" name="data_inicio" id="data_inicio" size="15" onkeypress="return formatDateField(event, this);" /></td></tr>';
	content += '<tr><td><label for="data_fim">Data de término</label></td><td><input type="text" name="data_fim" id="data_fim" size="15" onkeypress="return formatDateField(event, this);"/></td></tr>';
	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objSubstitution.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;

	// function that must be called after loading areas on the combobox
	var areas_callback = function () {

		// every change on the combo box must query for area information
		$(area_id).onchange = function () {
			if ($('SubstitutionList'))
				$('SubstitutionList').remove();
			
			var info_callback = function (data) {

				// just to be sure
				if (!data[0]) {
					alert("Não foi possível encontrar os dados.");
				}

				// we received an empty response. Let's reset the form.
				if ((data[0]['area_id'] == '') || (data[0]['area_id'] == null)) {
					alert('RESET');
					$('area_id').value = '';
					$('descricao').value = '';
					$('titular_funcionario_id').value = '';
					$('titular_funcionario_id_desc').value = '';
				}
				// fill the form
				else {
					$('area_id').value = data[0]['area_id'];
					$('descricao').value = data[0]['descricao'];
					$('titular_funcionario_id').value = data[0]['titular_funcionario_id'];
					$('titular_funcionario_id_desc').value = data[0]['titular_funcionario_id_desc'];
				}

				// get the list of substitutions
				objSubstitution.generateUpdateTable({'organizacao_id': organizationID, 'area_id': $(area_id).value}, divLB);
			}
			objArea.list(info_callback, {'organizacao_id': organizationID, 'area_id': $(area_id).value});

		}
	}
	objArea.generateComboBox({'organizacao_id': organizationID}, $('comboArea'), true, area_id, areas_callback);
}

function getEmployeeInfoTimer(e, employeeID, organizationID)
{
	var div = $('employeeInfo');
	div.style.left = (Event.pointerX(e) + 20) + 'px';
	div.style.top = (Event.pointerY(e) + 14) + 'px';

	if (workflowOrgchartAdminEmployeeInfoTimer != null)
	{
		workflowOrgchartAdminEmployeeInfoTimer = clearTimeout(workflowOrgchartAdminEmployeeInfoTimer);
		workflowOrgchartAdminEmployeeInfoTimer = null;
	}

	workflowOrgchartAdminEmployeeInfoTimer = setTimeout('getEmployeeInfo(' + employeeID + ', ' + organizationID + ' )', 500);
}

function getEmployeeInfo(employeeID, organizationID)
{
	function resultGetEmployeeInfo(data)
	{
		if (workflowOrgchartAdminEmployeeInfoTimer == null)
			return;

		workflowOrgchartAdminEmployeeInfoTimer = clearTimeout(workflowOrgchartAdminEmployeeInfoTimer);
		workflowOrgchartAdminEmployeeInfoTimer = null;

		var content = '';
		content += '<table><tr><td valign="top">';
		content += '<img src="workflow/showUserPicture.php?userID=' + employeeID + '"/>';
		content += '</td><td valign="top" style="padding-left: 12px;">';
		for (var i = 0; i < data['info'].length; i++)
			content += '<strong>' + data['info'][i]['name'] + '</strong>: ' + data['info'][i]['value'] + '<br/>';
		content += '</td></tr></table>';
		var pageYLimit = document.body.scrollTop + document.body.clientHeight;
		var div = $('employeeInfo');
		div.innerHTML = content;

		if ((parseInt(div.style.top.replace(/px/g, '')) + div.getHeight()) > pageYLimit)
			div.style.top = (parseInt(div.style.top.replace(/px/g, '')) - (div.getHeight())) + 'px';

		div.show();
	}
	cExecute('$this.bo_orgchart.getEmployeeInfo', resultGetEmployeeInfo, 'funcionario_id=' + employeeID + '&organizacao_id=' + organizationID);
}

function hideEmployeeInfo()
{
	if (workflowOrgchartAdminEmployeeInfoTimer != null)
	{
		workflowOrgchartAdminEmployeeInfoTimer = clearTimeout(workflowOrgchartAdminEmployeeInfoTimer);
		workflowOrgchartAdminEmployeeInfoTimer = null;
	}
	$('employeeInfo').hide();
}

function getAreaInfoTimer(e, areaID, organizationID)
{
	var div = $('areaInfo');
	div.style.left = (Event.pointerX(e) + 20) + 'px';
	div.style.top = (Event.pointerY(e) + 14) + 'px';

	if (workflowOrgchartAdminAreaInfoTimer != null)
	{
		workflowOrgchartAdminAreaInfoTimer = clearTimeout(workflowOrgchartAdminAreaInfoTimer);
		workflowOrgchartAdminAreaInfoTimer = null;
	}

	workflowOrgchartAdminAreaInfoTimer = setTimeout('getAreaInfo(' + areaID + ', ' + organizationID + ' )', 500);
}

function getAreaInfo(areaID, organizationID)
{
	function resultGetAreaInfo(data)
	{
		if (workflowOrgchartAdminAreaInfoTimer == null)
			return;

		workflowOrgchartAdminAreaInfoTimer = clearTimeout(workflowOrgchartAdminAreaInfoTimer);
		workflowOrgchartAdminAreaInfoTimer = null;

		var content = '';
		content += '<table><tr>';
		content += '<td valign="top" style="padding-left: 12px;">';
		for (var i = 0; i < data['info'].length; i++)
			content += '<strong>' + data['info'][i]['name'] + '</strong>: ' + data['info'][i]['value'] + '<br/>';
		content += '</td></tr></table>';
		var pageYLimit = document.body.scrollTop + document.body.clientHeight;
		var div = $('areaInfo');
		div.innerHTML = content;

		if ((parseInt(div.style.top.replace(/px/g, '')) + div.getHeight()) > pageYLimit)
			div.style.top = (parseInt(div.style.top.replace(/px/g, '')) - (div.getHeight())) + 'px';

		div.show();
	}
	cExecute('$this.bo_orgchart.getAreaInfo', resultGetAreaInfo, 'area_id=' + areaID + '&organizacao_id=' + organizationID);
}

function hideAreaInfo()
{
	if (workflowOrgchartAdminAreaInfoTimer != null)
	{
		workflowOrgchartAdminAreaInfoTimer = clearTimeout(workflowOrgchartAdminAreaInfoTimer);
		workflowOrgchartAdminAreaInfoTimer = null;
	}
	$('areaInfo').hide();
}
