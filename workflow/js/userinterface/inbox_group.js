function group_inbox()
{
	var inbox_group = function(data)
	{
		if (_checkError(data))
			return;
		draw_inbox_group(data);
	};

	/* interrompe a tualização automática das tarefas pendentes (já que o usuário está na interface agrupada) */
	workflowInboxStopRefreshInterval();
	/* indica que na próxima chamada ajax que traz as instâncias, a interface de tarefas pendentes deverá ser reconstruída */
	workflowInstancesDigest = null;

	cExecute ("$this.bo_userinterface.inbox_group", inbox_group);
}

function draw_inbox_group(data) {
	
	killElement("table_tools_inbox");
	killElement("table_elements_inbox");

	var content_id_0 = document.getElementById("content_id_0");
	var table_element = document.createElement("TABLE");
	var tbody_element = document.createElement("TBODY");
	table_element.setAttribute("id", "table_tools_inbox_group");
	table_element.setAttribute("width", "auto");
	tr_element = document.createElement("TR");
	td_element1 = document.createElement("TD");
	td_element1.setAttribute("id", "td_tools_inbox_group_1");
	td_element1.setAttribute("width", "270");
	tr_element.appendChild(td_element1);
	td_element2 = document.createElement("TD");
	td_element2.setAttribute("id", "td_tools_inbox_group_2");
	td_element2.setAttribute("valign", "middle");
	tr_element.appendChild(td_element2);			
	td_element3 = document.createElement("TD");
	td_element3.setAttribute("id", "td_tools_inbox_group_3");			
	tr_element.appendChild(td_element3);
	tbody_element.appendChild(tr_element);
	table_element.appendChild(tbody_element);
	content_id_0.appendChild(table_element);	

	construct_menu_inbox_group(td_element1.id);
	
	//Construindo o cabeçalho da lista
	var table_element = document.createElement("TABLE");
	var tbody_element = document.createElement("TBODY");
			
	table_element.setAttribute("id", "table_elements_inbox_group");
	table_element.className = "table_elements";
	table_element.setAttribute("cellPadding", "2");
		
	tbody_element.setAttribute("id", "tbody_elements_inbox_group");
			
	tr_element = document.createElement("TR");
	tr_element.className = "table_elements_tr_header";
	
	td_element1 = document.createElement("TD");
	td_element1.setAttribute("width", "33%");
	td_element1.align = "left";
	td_element1.innerHTML = "Processo";
		
	td_element2 = document.createElement("TD");
	td_element2.setAttribute("width", "33%");
	td_element2.align = "left";
	td_element2.innerHTML = "Atividade";	
	
	td_element3 = document.createElement("TD");
	td_element3.setAttribute("width", "33%");
	td_element3.align = "left";
	td_element3.innerHTML = "Quantidade";	

	tr_element.appendChild(td_element1);
	tr_element.appendChild(td_element2);
	tr_element.appendChild(td_element3);	
	tbody_element.appendChild(tr_element);
	table_element.appendChild(tbody_element);
	content_id_0.appendChild(table_element);
	
	//inserindo elementos na lista
	for (var i=0; i<(data.length); i++){
		tr_element = construct_inbox_group_list(data[i]);
		tbody_element.appendChild(tr_element);
	}	
}

function construct_menu_inbox_group(id) {
	var max_length = 0;
	mmain_inbox_group = new TMainMenu("mmain_inbox_group",'horizontal');
	_group = new TPopMenu("Desagrupar",_icon_dir + "/ungroup.png",'f',"javascript:ungroup_inbox();","");
	
	mmain_inbox_group.Add(_group);
	ConfigMenuStyle_inbox(mmain_inbox_group);	
	mmain_inbox_group.Build(id);
	document.getElementById(mmain_inbox_group._id).style.visibility='visible';
}

function construct_inbox_group_list(data){

	var tr_element = document.createElement("TR");
	tr_element.className = 'table_elements_tr_line';
	tr_element.style.cursor = "pointer";
	tr_element.onclick = function() { filter_activity_inbox(data.wf_p_id); };

	td_element1 = document.createElement("TD");
	td_element1.setAttribute("width", "33%");
	td_element1.align = "left";
	td_element1.innerHTML = data.wf_procname + " (v" + data.wf_version +")";

	td_element2 = document.createElement("TD");
	td_element2.setAttribute("width", "33%");
	td_element2.align = "left";
	td_element2.innerHTML = data.wf_name;

	td_element3 = document.createElement("TD");
	td_element3.setAttribute("width", "33%");
	td_element3.align = "left";
	td_element3.innerHTML = data.wf_instances;

	tr_element.appendChild(td_element1);
	tr_element.appendChild(td_element2);
	tr_element.appendChild(td_element3);	

	return tr_element;
}

function ungroup_inbox(){
	killElement("table_tools_inbox_group");
	killElement("table_elements_inbox_group");
	draw_inbox_folder();
}

function filter_activity_inbox(proc) {
	killElement("table_tools_inbox_group");
	killElement("table_elements_inbox_group");
	filterInbox(proc);
}

function ConfigMenuStyle_inbox(m, max)
{
	m.SetPosition('relative',0,0);
	m.SetCorrection(1,-5);
	m.SetCellSpacing(0);
	m.SetBackground('whitesmoke','','','');
	m.SetItemText('black','center','','','');
	m.SetItemBorder(1,'buttonface','solid');
	m.SetItemTextHL('darkblue','center','','','');
	m.SetItemBackgroundHL('white','','','');
	m.SetItemBorderHL(1,'black','solid');
	m.SetItemTextClick('white','center','','','');
	m.SetItemBackgroundClick('darkblue','','','');
	m.SetItemBorderClick(1,'black','solid');
	m.SetBorder(0,'navy','solid');

	m._pop.SetCorrection(4,1);
	m._pop.SetItemDimension(max * 7 + 30,22);
	m._pop.SetPaddings(1);
	m._pop.SetBackground('white','','','');
	m._pop.SetSeparator(150,'left','black','');
	m._pop.SetExpandIcon(true,'>',9);
	m._pop.SetItemBorder(0,'#66CCFF','solid');
	m._pop.SetItemBorderHL(0,'black','solid');
	m._pop.SetItemPaddings(0);
	m._pop.SetItemPaddingsHL(0);
	m._pop.SetItemText('black','','','','');
	m._pop.SetItemTextHL('darkblue','','','','');
	m._pop.SetItemBackground('white','','','');
	m._pop.SetItemBackgroundHL('whitesmoke','','','');
}
