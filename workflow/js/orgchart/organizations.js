function createMenu()
{
	var content = '<ul class="horizontalMenu">';
	content += '<li><a href="#" onclick="loadAddOrganizationUI(); return false;" class="lbOn">Organizações</a></li>';
	content += '</ul>';
	content += '<br/>';
	content += '<br/>';
	$('content_id_0').innerHTML = content;
}

function listOrganizations()
{
	var listOrganizationsResult = function(data)
	{
		var tmp = $('organizationList');
		if (tmp)
			tmp.parentNode.removeChild(tmp);

		for (var i = 0; i < data.length; i++)
		{
			data[i]['nome'] = '<a href="#" onclick="clickLoadOrganization(this, ' + data[i]['organizacao_id'] + '); return false;">' + data[i]['nome'] + '</a>';
			data[i]['tr_attributes'] = new Array();
			data[i]['tr_attributes']['class'] = "linha" + i%2;;
			data[i]['tr_attributes']['className'] = "linha" + i%2;;
		}

		var tableHeader = new Array();
		tableHeader['nome'] = 'Organização';
		tableHeader['descricao'] = 'Descrição';

		var tableAtributes = new Array();
		tableAtributes['id'] = "organizationList";
		tableAtributes['class'] = 'organizationList';
		tableAtributes['className'] = 'organizationList';

		var table = $('organizationList');
		if (table)
			table.parentNode.removeChild(table);

		$('content_id_0').appendChild(constructTable(tableHeader, data, tableAtributes));
	};

	objOrganization.list(listOrganizationsResult, {});
}

function loadAddOrganizationUI()
{
	var valoresSimNao = new Array();
	valoresSimNao['S'] = 'Sim';
	valoresSimNao['N'] = 'Não';

	var content;
	content = '<h2 id="modalTitle">Adicionar Organização</h2>';
	content += '<form name="orgchartForm" id="orgchartForm">';
	content += "<table>";
	content += '<tr><td><label for="nome">Nome</label></td><td><input type="text" name="nome" id="nome" size="50" /></td></tr>';
	content += '<tr><td><label for="descricao">Descrição</label></td><td><textarea name="descricao" id="descricao" cols="40" rows="5"></textarea></td></tr>';
	content += '<tr><td><label for="ativa">Ativa</label></td><td>' + constructSelectBox('ativa', valoresSimNao) + '</td></tr>';
	content += '<tr><td><label for="url_imagem">Imagem</label></td><td><input tyle="text" name="url_imagem" id="url_imagem" size="50" /></td></tr>';

	content += '<tr><td><label for="sitio">S&iacute;tio</label></td><td><input tyle="text" name="sitio" id="sitio" size="80" /></td></tr>';

	content += "</table>";
	content += '</form>';
	content += '<button id="inserir" onclick="objOrganization.add(); return false;">Inserir</button>';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	var divLB = $('lbContent');
	divLB.innerHTML = content;
	$('nome').focus();
	objOrganization.generateUpdateTable({}, divLB);
}

function clickLoadOrganization(link, organizationID)
{
	if ($('orgchartMenu_' + organizationID))
		return;
	refreshAreas[organizationID] = null;
	refreshEmployees[organizationID] = null;
	var border_id = create_border(link.innerHTML);
	var divNewOrganization = $('content_id_' + border_id);
	createOrganizationLayout(organizationID, divNewOrganization);
}
