/* permission index */
var IP_CHANGE_PRIORITY = 0;
var IP_CHANGE_USER = 1;
var IP_CHANGE_STATUS = 2;
var IP_CHANGE_NAME = 3;
var IP_CHANGE_ACTIVITY = 4;
var IP_VIEW_PROPERTIES = 5;
var IP_CHANGE_PROPERTIES = 6;
var IP_VIEW_STATISTICS = 7;
var IP_REMOVE_COMPLETED_INSTANCES = 8;
var IP_REPLACE_USER = 9;
var IP_SEND_EMAILS = 10;

/* general use variables */
var permissions;
var statusCorrelation = new Array();
var statusQuickTranslation = new Array();
for (var k = 0; k < 4; k++)
	statusCorrelation[k] = new Array();
statusCorrelation[0]['id'] = 'completed';
statusCorrelation[0]['name'] = 'completada';
statusCorrelation[1]['id'] = 'active';
statusCorrelation[1]['name'] = 'ativa';
statusCorrelation[2]['id'] = 'aborted';
statusCorrelation[2]['name'] = 'abortada';
statusCorrelation[3]['id'] = 'exception';
statusCorrelation[3]['name'] = 'em exceção';

for (var k = 0; k < 4; k++)
	statusQuickTranslation[statusCorrelation[k]['id']] = statusCorrelation[k]['name'];

/* constrói tabelas */
function constructTable(header, content, atributes)
{
	/*** constrói a tabela ***/
	var table = document.createElement("TABLE");
	/* configura a tabela */
	if (atributes)
		for (var i = 0; i < atributes.length; i++)
			table.setAttribute(atributes[i].key, atributes[i].value);
	var tbody = document.createElement("TBODY");
	var tr;
	var td;

	/* cabeçalho */
	tr = document.createElement("TR");
	for (var j = 0; j < header.length; j++)
	{
		td = document.createElement("TH");
		td.innerHTML = header[j].name;
		tr.appendChild(td);
	}
	tbody.appendChild(tr);
	/* elementos da tabela */
	for (var i = 0; i < content.length; i++)
	{
		tr = document.createElement("TR");
		if (content[i]['id'])
			tr.setAttribute('id', content[i]['id']);
		for (var j = 0; j < header.length; j++)
		{
			td = document.createElement("TD");
			td.innerHTML = content[i][header[j].id];
			tr.appendChild(td);
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

	output = '<select name=\"' + name + '\" id=\"' + name  + '\">';
	for (var i = 0; i < items.length; i++)
	{
		output += '<option value=\"' + items[i]['id']  + '\"';
		output += ((items[i]['id'] == selected) ? ' selected' : '') + '>';
		output += items[i]['name'] + '</option>';
	}
	output += '</select>';

	return output;
}

/* constrói o esqueleto da interface */
function buildProcessInterface()
{
	var divContent = document.getElementById('content_id_0');
	var center = document.createElement("CENTER");
	var table = document.createElement("TABLE");
	var tbody = document.createElement("TBODY");
	var tr = document.createElement("TR");
	var td1 = document.createElement("TD");
	var td2 = document.createElement("TD");
	var div = document.createElement("DIV");
	var h2 = document.createElement("H2");
	
	table.setAttribute('class', 'container');
	table.setAttribute('className', 'container'); /* required for IE */
	h2.innerHTML = "Processos";
	td1.setAttribute('id', 'divProcess');
	td2.setAttribute('id', 'divOptions');
	div.setAttribute('id', 'divInstance');
	tr.appendChild(td1);
	tr.appendChild(td2);
	tbody.appendChild(tr);
	table.appendChild(tbody);
	center.appendChild(h2);
	center.appendChild(table);
	center.appendChild(div);

	divContent.appendChild(center);
}

/* gerencia possíveis erros oriundos do método chamado pelo Ajax */
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

/* gerencia a mudança de aba */
function changeFolder(index)
{
	if (alternate_border(index) == 0)
	{
		switch (index)
		{
			case 0:
				buildProcessInterface();
				listProcesses();
				break;
		}
	}
}

/* inicia a interface */
function initMonitoringInterface()
{
	initBorders(1);

	var main_body = document.getElementById("main_body");
	main_body.style.display = '';

	changeFolder(0);
}

Event.observe(window, 'load', function() {
	initMonitoringInterface();
});
