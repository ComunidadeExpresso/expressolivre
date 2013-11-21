var vazio = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

function expandInput(name, iid, pid)
{
	var input = document.getElementById(name);
	var td = input.parentNode;
	var value = input.value;

	td.innerHTML = '<textarea id="' + name  + '" cols="80" rows="10"/>' + value  + '</textarea><button onclick="updateProperty(\'' + name + '\', ' + iid + ', ' + pid + '); return false;">OK</button>';
}

function loadProperties(iid, pid, div)
{
	function loadPropertiesResult(data)
	{
		if (handleError(data))
		{
			var canChangeProperties = permissions[pid]['bits'][IP_CHANGE_PROPERTIES];
			var content = '<table width="100%" align="center" border="1" class="content_table" id="tabela_propriedades">';
			content += '<tr><th>Nome</th><th>Valor</th><th>Ações</th></tr>';

			var row;
			var propertiesCount = data.length;
			for (var i = 0; i < propertiesCount; i++)
			{
				row = data[i];
				content += '<tr>';

				/* nome */
				content += '<td>' + row['name'] + '</td>';

				/* valor */
				if (row['value'] == '')
					row['value'] = vazio;
				content += '<td>';

				if (canChangeProperties)
				{
					if (row['complete'] == 1)
						content += '<a href="#" onclick="clickProperty(this, ' + data['params']['iid'] + ', ' + data['params']['pid'] + '); return false;">' + row['value'] + '</a>';
					else
						content += '<a href="#" onclick="clickLargeProperty(this, ' + data['params']['iid'] + ', ' + data['params']['pid'] + ', \'' + row['name'] + '\'); return false;">' + row['value'] + '</a>';
				}
				else
					content += row['value'];

				content += '</td>';

				/* ações */
				content += '<td>';
				if (canChangeProperties)
					content += '<a href="#" onclick="removeProperty(this, ' + data['params']['iid'] + ', ' + data['params']['pid'] + '); return false;">remover</a>';
				else
					content += '<i>nenhuma</i>';
				content += '</td>';

				content += '</tr>';
			}

			content += '</table>';

			/* adiciona o botão para a criação de novas propriedades */
			if (canChangeProperties)
				content = '<table width="75%" align="center"><tr><td><a href="#" onclick="addProperty(' + data['params']['iid'] + ', ' + data['params']['pid'] + '); return false;">Adicionar Propriedade</a></td></tr><tr><td>' + content + '</td></tr></table>';
			div.innerHTML = content;
		}
	}
	cExecute("$this.bo_monitors.listInstanceProperties", loadPropertiesResult, 'iid=' + iid + '&pid=' + pid);
}

function clickProperty(link, iid, pid)
{
	var value = link.innerHTML;
	var td = link.parentNode;
	var name = td.parentNode.childNodes[0].innerHTML;
	var minimumSize = 7;
	value = value.replace(/"/g, "&quot;");
	if (value == vazio)
		value = "";

	td.innerHTML = '<input type="text" id="' + name  + '" value="' + value + '" size="' + ((value.length > minimumSize) ? value.length : minimumSize) + '" /><button onclick="updateProperty(\'' + name + '\', ' + iid + ', ' + pid + '); return false;">OK</button><button onclick="expandInput(\'' + name + '\', ' + iid + ', ' + pid + '); return false;">+</button>';
}

function clickLargeProperty(link, iid, pid, name)
{
	var td = link.parentNode;
	var name = td.parentNode.childNodes[0].innerHTML;

	function largePropertyResult(data)
	{
		if (handleError(data))
		{
			data['value'] = data['value'].replace(/"/g, "&quot;");
			td.innerHTML = '<textarea id="' + name  + '" cols="80" rows="10"/>' + data['value']  + '</textarea><button onclick="updateProperty(\'' + name + '\', ' + iid + ', ' + pid + '); return false;">OK</button>';
		}
	}

	cExecute ("$this.bo_monitors.getCompletePropertyValue", largePropertyResult, 'iid=' + iid + '&pid=' + pid + '&name=' + name);
}

function updateProperty(name, iid, pid)
{
	function updatePropertyResult(data)
	{
		if (handleError(data))
		{
			data['value'] = data['value'].replace(/"/g, "&quot;");
			if (data['value'] == "")
				data['value'] = vazio;
			if (data['complete'] == 1)
				document.getElementById(name).parentNode.innerHTML = '<a href="javacript:void(0)" onclick="clickProperty(this, ' + iid + ', ' + pid + '); return false;">' + data['value'] + '</a>';
			else
				document.getElementById(name).parentNode.innerHTML = '<a href="javacript:void(0)" onclick="clickLargeProperty(this, ' + iid + ', ' + pid + '); return false;">' + data['value'] + '</a>';
		}
	}

	var value = document.getElementById(name).value;
	if (value == vazio)
		value = "";
	cExecute ("$this.bo_monitors.updateProperty", updatePropertyResult, 'iid=' + iid + '&pid=' + pid + '&name=' + name + '&value=' + escape(value));
}

function addProperty(iid, pid)
{
	var novaPropriedade = prompt("Qual o nome da nova propriedade?");
	if (novaPropriedade)
	{
		novaPropriedade = novaPropriedade.replace(/^[ 	]+/g, "");
		novaPropriedade = novaPropriedade.replace(/[ 	]+$/g, "");
		novaPropriedade = novaPropriedade.replace(/ /g, "_");
		novaPropriedade = novaPropriedade.replace(/[^0-9A-Za-z\_]/g, "");
		if (novaPropriedade.length < 1)
		{
			alert("Nome inválido, tente outro.");
			return;
		}

		var tabela = $("tabela_propriedades");
		for (var i = 1; i < tabela.childNodes[0].childNodes.length; i++)
		{
			if (tabela.childNodes[0].childNodes[i].childNodes[0].innerHTML == novaPropriedade)
			{
				alert("Já existe uma propriedade com este nome.\nModifique a existente ou escolha outro nome.");
				return;
			}
		}
		var tr = document.createElement("TR");
		var td01 = document.createElement("TD");
		var td02 = document.createElement("TD");
		var td03 = document.createElement("TD");
		td01.innerHTML = novaPropriedade;
		td02.innerHTML = '<input type="text" id="' + novaPropriedade  + '" value="" size="27" /><button onclick="updateProperty(\'' + novaPropriedade + '\', ' + iid + ', ' + pid + '); return false;">OK</button><button onclick="expandInput(\'' + novaPropriedade + '\', ' + iid + ', ' + pid + '); return false;">+</button>';
		td03.innerHTML = '<a href="#" onclick="removeProperty(this, ' + iid + ', ' + pid + '); return false;">remover</a>';
		tr.appendChild(td01);
		tr.appendChild(td02);
		tr.appendChild(td03);
		tabela.childNodes[0].appendChild(tr);
	}
}

function removeProperty(link, iid, pid)
{
	function removePropertyResult(data)
	{
		if (handleError(data))
		{
			var tr = link.parentNode.parentNode;
			tr.parentNode.removeChild(tr);
		}
	}

	var name = link.parentNode.parentNode.childNodes[0].innerHTML;
	if (confirm("Tem certeza que deseja remover a propriedade \"" + name + "\"?"))
		cExecute ("$this.bo_monitors.removeProperty", removePropertyResult, 'iid=' + iid + '&pid=' + pid + '&name=' + name);
}
