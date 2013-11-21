function change_folder(index)
{
	if (alternate_border(index) == 0)
	{
		switch (index)
		{
			case 0:
				draw_administration_control();
				break;

			case 1:
				draw_development_control();
				break;

			case 2:
				draw_organogram_control();
				break;

			case 3:
				draw_process_control();
				break;

			case 4:
				draw_monitor_control();
				break;

			case 5:
				draw_external_application_control();
				break;
		}
	}
}

function setSelectValue(obj, value)
{
	/* IE MAGIC */
	if (obj.outerHTML)
	{
		obj.innerHTML = '';
		obj.outerHTML = obj.outerHTML.match(/<select[^>]*>/gi) + value + '</select>';
	}
	else
		obj.innerHTML = value;
}

function del_option_values(element)
{
	for (var i = 0; i < element.options.length; i++)
       	element.options[i--] = null;
}

function del_selected_reg(id_name)
{
	element = document.getElementById(id_name);
	for(var i = 0;i < element.options.length; i++)
    	if (element.options[i].selected)
		{
       		element.options[i] = null;
			if (element.options[i])
				element.options[i].selected = true;
			else
				if (i > 0)
					element.options[--i].selected = true;

			break;
		}
}

function del_organogram_admin()
{
	var hndDelAdmin = function(data)
	{
		refresh_org_admins(data);
	};

	if (confirm('Tem certeza que deseja excluir o registro selecionado?'))
	{
		var sel_org = document.getElementById('sel_org');
		var sel_org_admin = document.getElementById('sel_org_admin');
		cExecute ("$this.bo_adminaccess.del_organogram_admin", hndDelAdmin, "org_id="+sel_org.value+"&admin_id="+sel_org_admin.value);
	}
}

function del_process_admin()
{
	var hndDelProc = function(data)
	{
		refresh_proc_admins(data);
	};

	if (confirm('Tem certeza que deseja excluir o registro selecionado?'))
	{
		var sel_proc = document.getElementById('sel_proc');
		var sel_proc_admin = document.getElementById('sel_proc_admin');
		cExecute ("$this.bo_adminaccess.del_process_admin", hndDelProc, "proc_id="+sel_proc.value+"&admin_id="+sel_proc_admin.value);
	}
}

function del_monitor_admin()
{
	var hndDelProc = function(data)
	{
		refresh_mon_admins(data);
	};

	if (confirm('Tem certeza que deseja excluir o registro selecionado?'))
	{
		var sel_mon = document.getElementById('sel_mon');
		var sel_mon_admin = document.getElementById('sel_mon_admin');
		cExecute ("$this.bo_adminaccess.del_monitor_admin", hndDelProc, "proc_id="+sel_mon.value+"&admin_id="+sel_mon_admin.value);
	}
}

function refresh_org_admins(data)
{
	var se = document.getElementById('sel_org_admin');
	
	del_option_values(se);

	for (i = 0; i < data.length; i++) 
	{
		op = document.createElement("OPTION");
		op.setAttribute('value',data[i]['uidnumber']);
		op.innerHTML = data[i]['cn'];
		se.appendChild(op);
	}
}

function refresh_proc_admins(data)
{
	var se = document.getElementById('sel_proc_admin');
	
	del_option_values(se);

	for (i = 0; i < data.length; i++) 
	{
		op = document.createElement("OPTION");
		op.setAttribute('value',data[i]['uidnumber']);
		op.innerHTML = data[i]['cn'];
		se.appendChild(op);
	}
}

function refresh_mon_admins(data)
{
	/* remove information about the user levels */
	var divUserLevels = document.getElementById("userLevel");
	divUserLevels.innerHTML = "";

	var se = document.getElementById('sel_mon_admin');
	
	del_option_values(se);

	for (i = 0; i < data.length; i++) 
	{
		op = document.createElement("OPTION");
		op.setAttribute('value',data[i]['uidnumber']);
		op.innerHTML = data[i]['cn'];
		se.appendChild(op);
	}
}

function add_organogram_admins()
{
	var hndAddAdmin = function(data)
	{
		refresh_org_admins(data);
		
		se = document.getElementById('sel_org_user');
		del_option_values(se); 
	};

	var sel_org = document.getElementById('sel_org');
	if (sel_org.value == -1) {
		alert('É necessário selecionar uma organização.');
		return;
	}

	element = document.getElementById('sel_org_user');
	if (element.options.length == 0) return;

	ids = '';
	for(var i = 0;i < element.options.length; i++)
	{
		if (i) {
			ids += '.'+element.options[i].value;
		} else {
			ids = element.options[i].value;
		}
	}

	cExecute ("$this.bo_adminaccess.add_organogram_admins", hndAddAdmin, "org_id="+sel_org.value+"&user_ids="+ids);
}

function add_process_admins()
{
	var hndAddAdmin = function(data)
	{
		refresh_proc_admins(data);
		
		var se = document.getElementById('sel_proc_user');
		del_option_values(se); 
	};

	var sel_proc = document.getElementById('sel_proc');
	if (sel_proc.value == -1) {
		alert('É necessário selecionar um processo.');
		return;
	}

	var element = document.getElementById('sel_proc_user');
	if (element.options.length == 0) return;

	ids = '';
	for(var i = 0;i < element.options.length; i++)
	{
		if (i) {
			ids += '.'+element.options[i].value;
		} else {
			ids = element.options[i].value;
		}
	}

	cExecute ("$this.bo_adminaccess.add_process_admins", hndAddAdmin, "proc_id="+sel_proc.value+"&user_ids="+ids);
}

function add_monitor_admins()
{
	var hndAddAdmin = function(data)
	{
		refresh_mon_admins(data);
		
		var se = document.getElementById('sel_mon_user');
		del_option_values(se); 
	};

	var sel_mon = document.getElementById('sel_mon');
	if (sel_mon.value == -1) {
		alert('É necessário selecionar um processo.');
		return;
	}

	var element = document.getElementById('sel_mon_user');
	if (element.options.length == 0) return;

	ids = '';
	for(var i = 0;i < element.options.length; i++)
	{
		if (i) {
			ids += '.'+element.options[i].value;
		} else {
			ids = element.options[i].value;
		}
	}

	cExecute ("$this.bo_adminaccess.add_monitor_admins", hndAddAdmin, "proc_id="+sel_mon.value+"&user_ids="+ids);
}

function fillOrgAdmins()
{
	var hndAdminList = function(data)
	{
		refresh_org_admins(data);
	};

	var sel_org = document.getElementById('sel_org');
	cExecute ("$this.bo_adminaccess.get_organogram_admins", hndAdminList, "org_id="+sel_org.value);
}

function fillProcAdmins()
{
	var hndAdminList = function(data)
	{
		refresh_proc_admins(data);
	};

	var sel_proc = document.getElementById('sel_proc');
	cExecute ("$this.bo_adminaccess.get_process_admins", hndAdminList, "proc_id="+sel_proc.value);
}

function fillMonAdmins()
{
	var hndAdminList = function(data)
	{
		refresh_mon_admins(data);
	};

	var sel_mon = document.getElementById('sel_mon');
	cExecute ("$this.bo_adminaccess.get_monitor_admins", hndAdminList, "proc_id="+sel_mon.value);
}

function draw_organogram_control()
{
	var fillOrgs = function(data)
	{
		se = document.getElementById('sel_org');

		op = document.createElement("OPTION");
		op.setAttribute('value',-1);
		se.appendChild(op);

		for (i = 0; i < data.length; i++) 
		{
			op = document.createElement("OPTION");
			op.setAttribute('value',data[i]['organizacao_id']);
			op.innerHTML = data[i]['nome'];
			se.appendChild(op);
		}
	};


	var control_folder = getFolder(2);
	var input_width = '300px';
	var input_height = '150px';

	control_folder.appendChild(document.createElement('BR'));
	control_folder.appendChild(document.createElement('BR'));

	tb_out = document.createElement("TABLE");
	to_out = document.createElement("TBODY");
	tr_out = document.createElement("TR");
	td_out = document.createElement("TD");

	tb = document.createElement("TABLE");
	to = document.createElement("TBODY");
	tr = document.createElement("TR");
	td = document.createElement("TD");

	td.innerHTML = 'Organização:<br>';
	se = document.createElement("SELECT");
	se.setAttribute('name','sel_org');
	se.setAttribute('id','sel_org');
	se.onchange = function() { fillOrgAdmins();  };
	se.style.width = input_width;

	td.appendChild(se);
	tr.appendChild(td);	

	td = document.createElement("TD");
	tr.appendChild(td);
	td = document.createElement("TD");
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	td = document.createElement("TD");
	td.setAttribute("colSpan", 3);
	td.innerHTML = "&nbsp;";
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	td = document.createElement("TD");

	td.innerHTML = 'Administradores do organograma:<br>';
	se = document.createElement("SELECT");
	se.setAttribute('size','10');
	se.setAttribute('id','sel_org_admin');
	se.onchange = function() { loadLevelORG();  };
	se.style.width = input_width;
	se.style.height = input_height;

	td.appendChild(se);
	tr.appendChild(td);	
	to.appendChild(tr);

	td = document.createElement('TD');
	td.setAttribute('align','center');
	li = document.createElement('A');
	li.setAttribute('href',"javascript:add_organogram_admins()");
	im = new Image();
	im.src = _icon_dir+'/add_org_admin.png';
	li.appendChild(im);
	td.appendChild(li);
	td.appendChild(document.createElement('BR'));
	li = document.createElement('A');
	li.setAttribute('href',"javascript:add_organogram_admins()");
	li.innerHTML = 'Adicionar';
	td.appendChild(li);
	tr.appendChild(td);
	
	td = document.createElement("TD");
	td.innerHTML = 'Usuários para adicionar:<br>';
	td.setAttribute('valign','top');
	se = document.createElement("SELECT");
	se.setAttribute('name','file');
	se.setAttribute('id','sel_org_user');
	se.setAttribute('size','10');
	se.style.width  = input_width;
	se.style.height = input_height;

	td.appendChild(se);
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	
	td = document.createElement("TD");
	td.setAttribute('align','right');
	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Remover');
	bt.setAttribute('value','Remover');
	bt.onclick = function() { del_organogram_admin(); };
	td.appendChild(bt);
	tr.appendChild(td);
	
	td = document.createElement("TD");
	tr.appendChild(td);
	
	td = document.createElement("TD");
	td.setAttribute('align','right');
	
	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Selecionar');
	bt.setAttribute('value','Selecionar');
	bt.onclick = function() { openParticipantsWindow('sel_org_user', 'hidegroups=1'); };
	td.appendChild(bt);

	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Remover');
	bt.setAttribute('value','Remover');
	bt.onclick = function() { del_selected_reg('sel_org_user'); };
	td.appendChild(bt);
	tr.appendChild(td);

	to.appendChild(tr);
	tb.appendChild(to);
	
	/* include the cell that will hold the user level interface */
	tr = document.createElement("TR");
	td = document.createElement("TD");
	td.setAttribute("id", "userLevelORG");
	td.setAttribute("colSpan", 3);
	tr.appendChild(td);
	to.appendChild(tr);
	tb.appendChild(to);

	tb_out.setAttribute('align','center');
	td_out.appendChild(tb);
	tr_out.appendChild(td_out);
	to_out.appendChild(tr_out);
	tb_out.appendChild(to_out);

	control_folder.appendChild(tb_out);	

	cExecute ("$this.bo_orgchart.listOrganization", fillOrgs, "");
}

function draw_process_control()
{
	var fillProcs = function(data)
	{
		var se = document.getElementById('sel_proc');

		var op = document.createElement("OPTION");
		op.setAttribute('value',-1);
		se.appendChild(op);

		for (i = 0; i < data.length; i++) 
		{
			op = document.createElement("OPTION");
			op.setAttribute('value',data[i]['proc_in_id']);
			op.innerHTML = data[i]['proc_st_name'];
			se.appendChild(op);
		}
	};


	var control_folder = getFolder(3);
	var input_width = '300px';
	var input_height = '150px';

	control_folder.appendChild(document.createElement('BR'));
	control_folder.appendChild(document.createElement('BR'));

	tb_out = document.createElement("TABLE");
	to_out = document.createElement("TBODY");
	tr_out = document.createElement("TR");
	td_out = document.createElement("TD");

	tb = document.createElement("TABLE");
	to = document.createElement("TBODY");
	tr = document.createElement("TR");
	td = document.createElement("TD");

	td.innerHTML = 'Processo:<br>';
	se = document.createElement("SELECT");
	se.setAttribute('name','sel_proc');
	se.setAttribute('id','sel_proc');
	se.onchange = function() { fillProcAdmins();  };
	se.style.width = input_width;

	td.appendChild(se);
	tr.appendChild(td);	

	td = document.createElement("TD");
	tr.appendChild(td);
	td = document.createElement("TD");
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	td = document.createElement("TD");
	td.setAttribute("colSpan", 3);
	td.innerHTML = "&nbsp;";
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	td = document.createElement("TD");

	td.innerHTML = 'Administradores do processo:<br>';
	se = document.createElement("SELECT");
	se.setAttribute('size','10');
	se.setAttribute('id','sel_proc_admin');
	se.style.width = input_width;
	se.style.height = input_height;

	td.appendChild(se);
	tr.appendChild(td);	
	to.appendChild(tr);

	td = document.createElement('TD');
	td.setAttribute('align','center');
	li = document.createElement('A');
	li.setAttribute('href',"javascript:add_process_admins()");
	im = new Image();
	im.src = _icon_dir+'/add_org_admin.png';
	li.appendChild(im);
	td.appendChild(li);
	td.appendChild(document.createElement('BR'));
	li = document.createElement('A');
	li.setAttribute('href',"javascript:add_process_admins()");
	li.innerHTML = 'Adicionar';
	td.appendChild(li);
	tr.appendChild(td);
	
	td = document.createElement("TD");
	td.innerHTML = 'Usuários para adicionar:<br>';
	td.setAttribute('valign','top');
	se = document.createElement("SELECT");
	se.setAttribute('name','file');
	se.setAttribute('id','sel_proc_user');
	se.setAttribute('size','10');
	se.style.width  = input_width;
	se.style.height = input_height;

	td.appendChild(se);
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	
	td = document.createElement("TD");
	td.setAttribute('align','right');
	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Remover');
	bt.setAttribute('value','Remover');
	bt.onclick = function() { del_process_admin(); };
	td.appendChild(bt);
	tr.appendChild(td);
	
	td = document.createElement("TD");
	tr.appendChild(td);
	
	td = document.createElement("TD");
	td.setAttribute('align','right');
	
	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Selecionar');
	bt.setAttribute('value','Selecionar');
	bt.onclick = function() { openParticipantsWindow('sel_proc_user', ''); };
	td.appendChild(bt);

	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Remover');
	bt.setAttribute('value','Remover');
	bt.onclick = function() { del_selected_reg('sel_proc_user'); };
	td.appendChild(bt);
	tr.appendChild(td);

	to.appendChild(tr);
	tb.appendChild(to);

	tb_out.setAttribute('align','center');
	td_out.appendChild(tb);
	tr_out.appendChild(td_out);
	to_out.appendChild(tr_out);
	tb_out.appendChild(to_out);

	control_folder.appendChild(tb_out);	

	cExecute ("$this.bo_adminaccess.get_all_processes", fillProcs, "");
}

function draw_monitor_control()
{
	var fillProcsMon = function(data)
	{
		var se = document.getElementById('sel_mon');

		var op = document.createElement("OPTION");
		op.setAttribute('value',-1);
		se.appendChild(op);

		for (i = 0; i < data.length; i++) 
		{
			op = document.createElement("OPTION");
			op.setAttribute('value',data[i]['proc_in_id']);
			op.innerHTML = data[i]['proc_st_name'];
			se.appendChild(op);
		}
	};


	var control_folder = getFolder(4);
	var input_width = '300px';
	var input_height = '150px';

	control_folder.appendChild(document.createElement('BR'));
	control_folder.appendChild(document.createElement('BR'));

	tb_out = document.createElement("TABLE");
	to_out = document.createElement("TBODY");
	tr_out = document.createElement("TR");
	td_out = document.createElement("TD");

	tb = document.createElement("TABLE");
	to = document.createElement("TBODY");
	tr = document.createElement("TR");
	td = document.createElement("TD");

	td.innerHTML = 'Processo:<br>';
	se = document.createElement("SELECT");
	se.setAttribute('name','sel_mon');
	se.setAttribute('id','sel_mon');
	se.onchange = function() { fillMonAdmins();  };
	se.style.width = input_width;

	td.appendChild(se);
	tr.appendChild(td);	

	td = document.createElement("TD");
	tr.appendChild(td);
	td = document.createElement("TD");
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	td = document.createElement("TD");
	td.setAttribute("colSpan", 3);
	td.innerHTML = "&nbsp;";
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	td = document.createElement("TD");

	td.innerHTML = 'Usuários monitores:<br>';
	se = document.createElement("SELECT");
	se.setAttribute('size','10');
	se.setAttribute('id','sel_mon_admin');
	se.onchange = function() { loadLevel();  };
	se.style.width = input_width;
	se.style.height = input_height;

	td.appendChild(se);
	tr.appendChild(td);	
	to.appendChild(tr);

	td = document.createElement('TD');
	td.setAttribute('align','center');
	li = document.createElement('A');
	li.setAttribute('href',"javascript:add_monitor_admins()");
	im = new Image();
	im.src = _icon_dir+'/add_org_admin.png';
	li.appendChild(im);
	td.appendChild(li);
	td.appendChild(document.createElement('BR'));
	li = document.createElement('A');
	li.setAttribute('href',"javascript:add_monitor_admins()");
	li.innerHTML = 'Adicionar';
	td.appendChild(li);
	tr.appendChild(td);
	
	td = document.createElement("TD");
	td.innerHTML = 'Usuários para adicionar:<br>';
	td.setAttribute('valign','top');
	se = document.createElement("SELECT");
	se.setAttribute('name','file');
	se.setAttribute('id','sel_mon_user');
	se.setAttribute('size','10');
	se.style.width  = input_width;
	se.style.height = input_height;

	td.appendChild(se);
	tr.appendChild(td);
	to.appendChild(tr);

	tr = document.createElement("TR");
	
	td = document.createElement("TD");
	td.setAttribute('align','right');
	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Remover');
	bt.setAttribute('value','Remover');
	bt.onclick = function() { del_monitor_admin(); };
	td.appendChild(bt);
	tr.appendChild(td);
	
	td = document.createElement("TD");
	tr.appendChild(td);
	
	td = document.createElement("TD");
	td.setAttribute('align','right');
	
	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Selecionar');
	bt.setAttribute('value','Selecionar');
	bt.onclick = function() { openParticipantsWindow('sel_mon_user', ''); };
	td.appendChild(bt);

	bt = document.createElement("INPUT");
	bt.setAttribute('type','button');
	bt.setAttribute('name','Remover');
	bt.setAttribute('value','Remover');
	bt.onclick = function() { del_selected_reg('sel_mon_user'); };
	td.appendChild(bt);
	tr.appendChild(td);

	to.appendChild(tr);

	/* include the cell that will hold the user level interface */
	tr = document.createElement("TR");
	td = document.createElement("TD");
	td.setAttribute("id", "userLevel");
	td.setAttribute("colSpan", 3);
	tr.appendChild(td);
	to.appendChild(tr);
	tb.appendChild(to);

	tb_out.setAttribute('align','center');
	td_out.appendChild(tb);
	tr_out.appendChild(td_out);
	to_out.appendChild(tr_out);
	tb_out.appendChild(to_out);

	control_folder.appendChild(tb_out);	

	cExecute ("$this.bo_adminaccess.get_all_processes", fillProcsMon, "");
}

function draw_external_application_control()
{
	function fillExternalApplications(data)
	{
		var content = '<option value="-1"></option>';
		for (i = 0; i < data.length; i++)
			content += '<option value="' + data[i]['external_application_id'] + '">' + data[i]['name'] + '</option>';
		setSelectValue(document.getElementById('sel_exa'), content);
	}

	var inputWidth = '300px';
	var inputHeight = '150px';

	var content = '<br/><br/>';
	content += '<table align="center"><tr><td>';
	content += '<table>';
	content += '<tr><td>Aplicações Externas:<br/><select name="sel_exa" id="sel_exa" onchange="fillExternalApplicationAdmins();" style="width: ' + inputWidth + ';"></select></td><td></td><td></td></tr>';
	content += '<tr><td colspan="3">&nbsp;</td></tr>';
	content += '<tr>';
	content += '<td>Usuários com acesso:<br/><select id="sel_exa_admin" size="10" style="width: ' + inputWidth + '; height: ' + inputHeight + ';"/></td>';
	content += '<td align="center"><a href="javascript:addExternalApplicationAdmins()"><img src="' + _icon_dir + 'add_org_admin.png"/></a><br/><a href="javascript:addExternalApplicationAdmins();">Adicionar</a></td>';
	content += '<td valign="top">Usuários para adicionar:<br/><select id="sel_exa_user" name="file" size="10" style="width: ' + inputWidth + '; height: ' + inputHeight + ';"/></td>';
	content += '</tr>';
	content += '<tr>';
	content += '<td align="right"><input type="button" name="Remover" value="Remover" onclick="deleteExternalApplicationAdmin();"/></td>';
	content += '<td></td>';
	content += '<td align="right"><input type="button" name="Selecionar" value="Selecionar" onclick="openParticipantsWindow(\'sel_exa_user\', \'\')"/><input type="button" name="Remover" value="Remover" onclick="del_selected_reg(\'sel_exa_user\');"/></td>';
	content += '</tr>';
	content += '<tr><td colspan="3"></td></tr>';
	content += '</table></td></tr></table>';

	getFolder(5).innerHTML = content;
	cExecute ("$this.bo_external_applications.getExternalApplications", fillExternalApplications, "");
}

function fillExternalApplicationAdmins()
{
	cExecute ("$this.bo_adminaccess.getExternalApplicationAdmins", refreshExternalApplicationAdmins, "external_application_id=" + document.getElementById('sel_exa').value);
}

function deleteExternalApplicationAdmin()
{
	if (confirm('Tem certeza que deseja excluir o registro selecionado?'))
		cExecute ("$this.bo_adminaccess.deleteExternalApplicationAdmin", refreshExternalApplicationAdmins, "external_application_id=" + document.getElementById('sel_exa').value + "&admin_id=" + document.getElementById('sel_exa_admin').value);
}

function addExternalApplicationAdmins()
{
	var sel_exa = document.getElementById('sel_exa');
	if (sel_exa.value == -1)
	{
		alert('É necessário selecionar uma Aplicação Externa.');
		return;
	}

	var element = document.getElementById('sel_exa_user');
	if (element.options.length == 0)
		return;

	var ids = '';
	for(var i = 0; i < element.options.length; i++)
		ids += ((i == 0) ? '' : '.') + element.options[i].value;

	cExecute("$this.bo_adminaccess.addExternalApplicationAdmins", function(data){refreshExternalApplicationAdmins(data); document.getElementById('sel_exa_user').innerHTML = '';}, "external_application_id="+sel_exa.value+"&user_ids="+ids);
}

function refreshExternalApplicationAdmins(data)
{
	var content = '';
	for (var i = 0; i < data.length; i++)
		content += '<option value="' + data[i]['uidnumber'] + '">' + data[i]['cn'] + '</option>';
	setSelectValue(document.getElementById('sel_exa_admin'), content);
}

function draw_administration_control()
{
	var inputWidth = '300px';
	var inputHeight = '150px';

	var content = '<br/><br/>';
	content += '<table align="center"><tr><td>';
	content += '<table>';
	content += '<tr>';
	content += '<td>Administradores do Módulo:<br/><select id="selectWorkflowAdmins" size="10" style="width: ' + inputWidth + '; height: ' + inputHeight + ';"/></td>';
	content += '<td align="center"><a href="javascript:addWorkflowAdministrators()"><img src="' + _icon_dir + 'add_org_admin.png"/></a><br/><a href="javascript:addWorkflowAdministrators();">Adicionar</a></td>';
	content += '<td valign="top">Usuários para adicionar:<br/><select id="selectWorkflowUsers" name="file" size="10" style="width: ' + inputWidth + '; height: ' + inputHeight + ';"/></td>';
	content += '</tr>';
	content += '<tr>';
	content += '<td align="right"><input type="button" name="Remover" value="Remover" onclick="deleteWorkflowAdministrators();"/></td>';
	content += '<td></td>';
	content += '<td align="right"><input type="button" name="Selecionar" value="Selecionar" onclick="openParticipantsWindow(\'selectWorkflowUsers\', \'\')"/><input type="button" name="Remover" value="Remover" onclick="del_selected_reg(\'selectWorkflowUsers\');"/></td>';
	content += '</tr>';
	content += '<tr><td colspan="3"></td></tr>';
	content += '</table></td></tr></table>';

	getFolder(0).innerHTML = content;
	cExecute ("$this.bo_adminaccess.getWorkflowAdministrators", refreshWorkflowAdministrators, "");
}

function refreshWorkflowAdministrators(data)
{
	var content = '';
	for (var i = 0; i < data.length; i++)
		content += '<option value="' + data[i]['uidnumber'] + '">' + data[i]['cn'] + '</option>';
	setSelectValue(document.getElementById('selectWorkflowAdmins'), content);
}

function addWorkflowAdministrators()
{
	var element = document.getElementById('selectWorkflowUsers');
	if (element.options.length == 0)
		return;

	var ids = '';
	for(var i = 0; i < element.options.length; i++)
		ids += ((i == 0) ? '' : '.') + element.options[i].value;

	cExecute("$this.bo_adminaccess.addWorkflowAdministrators", function(data){refreshWorkflowAdministrators(data); document.getElementById('selectWorkflowUsers').innerHTML = '';}, "user_ids="+ids);
}

function deleteWorkflowAdministrators()
{
	if (confirm('Tem certeza que deseja excluir o registro selecionado?'))
		cExecute("$this.bo_adminaccess.deleteWorkflowAdministrators", refreshWorkflowAdministrators, "admin_id=" + document.getElementById('selectWorkflowAdmins').value);
}

function draw_development_control()
{
	var inputWidth = '300px';
	var inputHeight = '150px';

	var content = '<br/><br/>';
	content += '<table align="center"><tr><td>';
	content += '<table>';
	content += '<tr>';
	content += '<td>Desenvolvedores de processos:<br/><select id="selectDevelopmentAdmins" size="10" style="width: ' + inputWidth + '; height: ' + inputHeight + ';"/></td>';
	content += '<td align="center"><a href="javascript:addDevelopmentAdministrators()"><img src="' + _icon_dir + 'add_org_admin.png"/></a><br/><a href="javascript:addDevelopmentAdministrators();">Adicionar</a></td>';
	content += '<td valign="top">Usuários para adicionar:<br/><select id="selectDevelopmentUsers" name="file" size="10" style="width: ' + inputWidth + '; height: ' + inputHeight + ';"/></td>';
	content += '</tr>';
	content += '<tr>';
	content += '<td align="right"><input type="button" name="Remover" value="Remover" onclick="deleteDevelopmentAdministrators();"/></td>';
	content += '<td></td>';
	content += '<td align="right"><input type="button" name="Selecionar" value="Selecionar" onclick="openParticipantsWindow(\'selectDevelopmentUsers\', \'\')"/><input type="button" name="Remover" value="Remover" onclick="del_selected_reg(\'selectDevelopmentUsers\');"/></td>';
	content += '</tr>';
	content += '<tr><td colspan="3"></td></tr>';
	content += '</table></td></tr></table>';

	getFolder(1).innerHTML = content;
	cExecute ("$this.bo_adminaccess.getDevelopmentAdministrators", refreshDevelopmentAdministrators, "");
}

function refreshDevelopmentAdministrators(data)
{
	var content = '';
	for (var i = 0; i < data.length; i++)
		content += '<option value="' + data[i]['uidnumber'] + '">' + data[i]['cn'] + '</option>';
	setSelectValue(document.getElementById('selectDevelopmentAdmins'), content);
}

function addDevelopmentAdministrators()
{
	var element = document.getElementById('selectDevelopmentUsers');
	if (element.options.length == 0)
		return;

	var ids = '';
	for(var i = 0; i < element.options.length; i++)
		ids += ((i == 0) ? '' : '.') + element.options[i].value;

	cExecute("$this.bo_adminaccess.addDevelopmentAdministrators", function(data){refreshDevelopmentAdministrators(data); document.getElementById('selectDevelopmentUsers').innerHTML = '';}, "user_ids="+ids);
}

function deleteDevelopmentAdministrators()
{
	if (confirm('Tem certeza que deseja excluir o registro selecionado?'))
		cExecute("$this.bo_adminaccess.deleteDevelopmentAdministrators", refreshDevelopmentAdministrators, "admin_id=" + document.getElementById('selectDevelopmentAdmins').value);
}

/* construct the user level interface */
function loadLevel()
{
	/* required parameters */
	var pid = document.getElementById('sel_mon').value;
	var uid = document.getElementById('sel_mon_admin').value;

	var loadLevelHandler = function(data)
	{
		var userLevelContainer = document.getElementById("userLevel");
		userLevelContainer.innerHTML = "";

		/* checkboxes creation */
		var checkBoxesPerRow = 2;
		var tb_out = document.createElement("TABLE");
		var to_out = document.createElement("TBODY");
		var tr = document.createElement("TR");
		var td = document.createElement("TD");
		td.innerHTML = "Permissões de Acesso:";
		tr.appendChild(td);
		to_out.appendChild(tr);
		var table = document.createElement("TABLE");
		var tbody = document.createElement("TBODY");
		table.style.border='1px solid gray';
		tr = null;
		td = null;
		for (var i = 0; i < permissionList.length; i++)
		{
			/* create the checkbox and the label */
			var checkBox = document.createElement("INPUT");
			checkBox.setAttribute("id", "cb_" + permissionList[i]['value']);
			checkBox.setAttribute("type", "checkbox");
			if (data['bits'][permissionList[i]['value']])
				checkBox.defaultChecked = true;

			var label = "<label for=\"cb_" + permissionList[i]['value']  + "\">" + permissionList[i]['name']  + "</label>";
			/* if necessary, start a new row */
			if (!tr || (tr.childNodes.length == 2*checkBoxesPerRow))
				tr = document.createElement("TR");
			td = document.createElement("TD");
			td.innerHTML = label;
			tr.appendChild(td);
			td = document.createElement("TD");
			td.appendChild(checkBox);
			tr.appendChild(td);

			/* check if the row is "complete" */
			if (tr.childNodes.length == 2*checkBoxesPerRow)
			{
				tbody.appendChild(tr);
			}
			else
			{
				if (i == (permissionList.length - 1))
				{
					tr.appendChild(document.createElement("TD"));
					tr.appendChild(document.createElement("TD"));
					tbody.appendChild(tr);
				}
			}
		}
		tr = document.createElement("TR");
		td = document.createElement("TD");
		td.colSpan = 4;
		td.align = 'center';
		var toggleButton = document.createElement("BUTTON");
		toggleButton.onclick = toggleCheckboxes;
		toggleButton.innerHTML = 'Marcar/Desmarcar Tudo';

		td.appendChild(toggleButton);
		tr.appendChild(td);
		tbody.appendChild(tr);
		table.appendChild(tbody);

		/* submit button */
		var button = document.createElement("BUTTON");
		button.onclick = function() { changeUserLevel(); };
		button.innerHTML = "Salvar";

		tr = document.createElement("TR");
		td = document.createElement("TD");
		td.appendChild(table);
		tr.appendChild(td);
		to_out.appendChild(tr);

		tr = document.createElement("TR");
		td = document.createElement("TD");
		td.setAttribute("align", "right");
		td.appendChild(button);
		tr.appendChild(td);
		to_out.appendChild(tr);

		tb_out.appendChild(to_out);
		userLevelContainer.appendChild(tb_out);
	};

	cExecute ("$this.bo_adminaccess.get_monitor_admin_level", loadLevelHandler, "pid=" + pid + "&uid=" + uid);
}

/* construct the user level interface */
function loadLevelORG()
{
	/* required parameters */
	var pid = document.getElementById('sel_org').value;
	var uid = document.getElementById('sel_org_admin').value;

	var loadLevelHandler = function(data)
	{
		var userLevelContainer = document.getElementById("userLevelORG");
		userLevelContainer.innerHTML = "";

		/* checkboxes creation */
		var checkBoxesPerRow = 2;
		var tb_out = document.createElement("TABLE");
		var to_out = document.createElement("TBODY");
		var tr = document.createElement("TR");
		var td = document.createElement("TD");
		td.innerHTML = "Permissões de Acesso:";
		tr.appendChild(td);
		to_out.appendChild(tr);
		var table = document.createElement("TABLE");
		var tbody = document.createElement("TBODY");
		table.style.border='1px solid gray';
		tr = null;
		td = null;
		for (var i = 0; i < permissionListORG.length; i++)
		{
			/* create the checkbox and the label */
			var checkBox = document.createElement("INPUT");
			checkBox.setAttribute("id", "cb_" + permissionListORG[i]['value']);
			checkBox.setAttribute("type", "checkbox");
			if (data['bits'][permissionListORG[i]['value']])
				checkBox.defaultChecked = true;

			var label = "<label for=\"cb_" + permissionListORG[i]['value']  + "\">" + permissionListORG[i]['name']  + "</label>";
			/* if necessary, start a new row */
			if (!tr || (tr.childNodes.length == 2*checkBoxesPerRow))
				tr = document.createElement("TR");
			td = document.createElement("TD");
			td.innerHTML = label;
			tr.appendChild(td);
			td = document.createElement("TD");
			td.appendChild(checkBox);
			tr.appendChild(td);

			/* check if the row is "complete" */
			if (tr.childNodes.length == 2*checkBoxesPerRow)
			{
				tbody.appendChild(tr);
			}
			else
			{
				if (i == (permissionListORG.length - 1))
				{
					tr.appendChild(document.createElement("TD"));
					tr.appendChild(document.createElement("TD"));
					tbody.appendChild(tr);
				}
			}
		}
		tr = document.createElement("TR");
		td = document.createElement("TD");
		td.colSpan = 4;
		td.align = 'center';
		var toggleButton = document.createElement("BUTTON");
		toggleButton.onclick = toggleCheckboxesORG;
		toggleButton.innerHTML = 'Marcar/Desmarcar Tudo';

		td.appendChild(toggleButton);
		tr.appendChild(td);
		tbody.appendChild(tr);
		table.appendChild(tbody);

		/* submit button */
		var button = document.createElement("BUTTON");
		button.onclick = function() { changeUserLevelORG(); };
		button.innerHTML = "Salvar";

		tr = document.createElement("TR");
		td = document.createElement("TD");
		td.appendChild(table);
		tr.appendChild(td);
		to_out.appendChild(tr);

		tr = document.createElement("TR");
		td = document.createElement("TD");
		td.setAttribute("align", "right");
		td.appendChild(button);
		tr.appendChild(td);
		to_out.appendChild(tr);

		tb_out.appendChild(to_out);
		userLevelContainer.appendChild(tb_out);
	};

	cExecute ("$this.bo_adminaccess.get_organogram_admin_level", loadLevelHandler, "pid=" + pid + "&uid=" + uid);
}

/* change the user level */
function changeUserLevel()
{
	/* required parameters */
	var pid = document.getElementById('sel_mon').value;
	var uid = document.getElementById('sel_mon_admin').value;

	/* check for error (ajax callback) */
	var changeUserLevelHandler = function(data)
	{
		if (typeof(data) == "string")
			write_errors(data);
		else
			write_msg('As permissões foram salvas');
	};
	
	/* generate the new permission string */
	var newPermission = "";
	for (var i = 0; i < permissionList.length; i++)
	{
		var cb = document.getElementById("cb_" + permissionList[i]['value']);
		newPermission += permissionList[i]['value'] + "=" + ((cb.checked) ? "1" : "0") + "_";
	}
	newPermission = newPermission.substring(0, newPermission.length-1);
	
	/* call ajax */
	cExecute ("$this.bo_adminaccess.set_monitor_admin_level", changeUserLevelHandler, "pid=" + pid + "&uid=" + uid + "&np=" + newPermission);
}

/* change the user level */
function changeUserLevelORG()
{
	/* required parameters */
	var pid = document.getElementById('sel_org').value;
	var uid = document.getElementById('sel_org_admin').value;

	/* check for error (ajax callback) */
	var changeUserLevelHandler = function(data)
	{
		if (typeof(data) == "string")
			write_errors(data);
		else
			write_msg('As permissões foram salvas');
	};
	
	/* generate the new permission string */
	var newPermission = "";
	for (var i = 0; i < permissionListORG.length; i++)
	{
		var cb = document.getElementById("cb_" + permissionListORG[i]['value']);
		newPermission += permissionListORG[i]['value'] + "=" + ((cb.checked) ? "1" : "0") + "_";
	}
	newPermission = newPermission.substring(0, newPermission.length-1);
	
	/* call ajax */
	cExecute ("$this.bo_adminaccess.set_organogram_admin_level", changeUserLevelHandler, "pid=" + pid + "&uid=" + uid + "&np=" + newPermission);
}

function toggleCheckboxes()
{
	var value = !document.getElementById('cb_' + permissionList[0]['value']).checked;
	for (var i = 0; i < permissionList.length; i++)
		document.getElementById('cb_' + permissionList[i]['value']).checked = value;
}

function toggleCheckboxesORG()
{
	var value = !document.getElementById('cb_' + permissionListORG[0]['value']).checked;
	for (var i = 0; i < permissionListORG.length; i++)
		document.getElementById('cb_' + permissionListORG[i]['value']).checked = value;
}

function draw_control_folder()
{
	change_folder(0);
}

function delUsers()
{
    target = window.document.getElementById('user_list');

    for(var i = 0; i < target.options.length; i++)
    {
        if(target.options[i].selected)
        {
            target.options[i--] = null;
        }
    }
}
