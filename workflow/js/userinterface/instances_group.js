
function group_instances()
{
	var instances = function(data)
	{
		if (_checkError(data))
			return;

		draw_instances_group(data.processes);
	};

	var params = "sort=" + workflowInstancesParams['sort'] + "&pid=0" + "&active=" + workflowInstancesParams['active'] + "&group_instances=1";
	cExecute ("$this.bo_userinterface.instances", instances, params);
}

function draw_instances_group(data)
{
	$('content_id_2').innerHTML = '';

	var content_id_2 = document.getElementById("content_id_2");
	var table_element = document.createElement("TABLE");
	var tbody_element = document.createElement("TBODY");
	table_element.setAttribute("id", "table_tools_instances_group");
	table_element.setAttribute("width", "auto");
	tr_element = document.createElement("TR");
	td_element1 = document.createElement("TD");
	td_element1.setAttribute("id", "td_tools_instances_group_1");
	td_element1.setAttribute("width", "270");
	tr_element.appendChild(td_element1);
	td_element2 = document.createElement("TD");
	td_element2.setAttribute("id", "td_tools_instances_group_2");
	td_element2.setAttribute("valign", "middle");
	tr_element.appendChild(td_element2);			
	td_element3 = document.createElement("TD");
	td_element3.setAttribute("id", "td_tools_instances_group_3");			
	tr_element.appendChild(td_element3);
	tbody_element.appendChild(tr_element);
	table_element.appendChild(tbody_element);
	content_id_2.appendChild(table_element);	

	construct_menu_instances_group(td_element1.id);
	
	//Construindo o cabeçalho da lista
	var table_element = document.createElement("TABLE");
	var tbody_element = document.createElement("TBODY");
			
	table_element.setAttribute("id", "table_elements_instances_group");
	table_element.className = "table_elements";
	table_element.setAttribute("cellPadding", "2");
		
	tbody_element.setAttribute("id", "tbody_elements_instances_group");
			
	tr_element = document.createElement("TR");
	tr_element.className = "table_elements_tr_header";
	
	td_element1 = document.createElement("TD");
	td_element1.setAttribute("width", "50%");
	td_element1.align = "left";
	td_element1.innerHTML = "Processo";
		
	td_element2 = document.createElement("TD");
	td_element2.setAttribute("width", "50%");
	td_element2.align = "left";
	td_element2.innerHTML = "Quantidade";	

	tr_element.appendChild(td_element1);
	tr_element.appendChild(td_element2);
	tbody_element.appendChild(tr_element);
	table_element.appendChild(tbody_element);
	content_id_2.appendChild(table_element);
	
	//inserindo elementos na lista
	for (var i=0; i<(data.length); i++){
		tr_element = construct_instances_group_list(data[i]);
		tbody_element.appendChild(tr_element);
	}	
}

function construct_menu_instances_group(id) {
	var max_length = 0;
	mmain_instances_group = new TMainMenu("mmain_instances_group",'horizontal');
	_group = new TPopMenu("Desagrupar",_icon_dir + "/ungroup.png",'f',"javascript:ungroup_instances();","");
	
	mmain_instances_group.Add(_group);
	ConfigMenuStyle_instances(mmain_instances_group);	
	mmain_instances_group.Build(id);
	document.getElementById(mmain_instances_group._id).style.visibility='visible';
}

function construct_instances_group_list(data){

	var tr_element = document.createElement("TR");
	tr_element.className = 'table_elements_tr_line';
	tr_element.style.cursor = "pointer";
	tr_element.onclick = function() { filter_activity_instances(data.pid); };

	td_element1 = document.createElement("TD");
	td_element1.setAttribute("width", "50%");
	td_element1.align = "left";
	td_element1.innerHTML = data.name;

	td_element2 = document.createElement("TD");
	td_element2.setAttribute("width", "50%");
	td_element2.align = "left";
	td_element2.innerHTML = data.total;

	tr_element.appendChild(td_element1);
	tr_element.appendChild(td_element2);	

	return tr_element;
}

function ungroup_instances(){
	killElement("table_tools_instances_group");
	killElement("table_elements_instances_group");
	pid = 0;
	draw_instances_folder();
}

function filter_activity_instances(code) {
	killElement("table_tools_instances_group");
	killElement("table_elements_instances_group");
	filterProcess(code);
}