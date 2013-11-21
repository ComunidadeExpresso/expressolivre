function constructTable(header, content, atributes)
{
	/*** constrói a tabela ***/
	var table = document.createElement("TABLE");

	table.style.marginTop = "10px";

	/* configura a tabela */
	if (atributes)
		for (i in atributes)
			if (typeof atributes[i] != "function")
				table.setAttribute(i, atributes[i]);

	var tbody = document.createElement("TBODY");
	var tr;
	var td;

	/* cabeçalho */
	tr = document.createElement("TR");
	tr.className = 'message_header';
	for (i in header)
	{
		if (typeof header[i] != "function")
		{
			td = document.createElement("TH");
			td.innerHTML = header[i];
			td.className = 'message_header';
			tr.appendChild(td);
		}
	}
	tbody.appendChild(tr);

	/* elementos da tabela */
	for (var i = 0; i < content.length; i++)
	{
		/* atributos da linha (TR) */
		tr = document.createElement("TR");
		if (content[i]['tr_attributes'])
			for (j in content[i]['tr_attributes'])
				if (typeof content[i]['tr_attributes'][j] != "function")
					tr.setAttribute(j, content[i]['tr_attributes'][j]);

		/* dados da tabela */
		for (j in header)
		{
			if (typeof header[j] != "function")
			{
				td = document.createElement("TD");
				td.innerHTML = content[i][j];
				tr.appendChild(td);
			}
		}
		tbody.appendChild(tr);
	}
	table.appendChild(tbody);

	return table;
}

/* constrói select boxes */
function constructSelectBox(name, items, selected)
{
	var output = '';

	output = '<select name="' + name + '" id="' + name  + '">';
	for (i in items)
		if (typeof items[i] != "function")
			output += '<option value="' + i + '"' +  ((i == selected) ? ' selected' : '') + '>' + items[i] + '</option>';
	output += '</select>';

	return output;
}

/* gerencia possíveis erros oriundos do método chamado via Ajax */
function handleError(data)
{
	if (typeof(data) == "string")
	{
		write_errors(data);
		return false;
	}
	else
		return true;
}
