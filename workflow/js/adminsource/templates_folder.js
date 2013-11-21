var tpl_sort_field = 'file_name';
var tpl_sort_asc   = 1;
var tpl_toolbar;
var tpl_rewrite = false;

function set_tpl_rewrite()
{
	tpl_rewrite = !tpl_rewrite;
}

function show_template_window()
{
	var showHnd = function(data)
	{
		div = document.getElementById('model_template_list');
		if (!div) {
			div = document.createElement("DIV");
			div.style.visibility = "hidden";
			div.style.position   = "absolute";
			div.id 				 = "model_template_list";
		
			tb = document.createElement("TABLE");
			tr = document.createElement("TR");
			td = document.createElement("TD");

			td.innerHTML = '<b>Selecione o modelo desejado:</b><br>';
			se = document.createElement("SELECT");
			se.setAttribute('name','tplfile');
			se.setAttribute('size','10');
			se.setAttribute('id','win_tpl_model');
			se.style.width = '200px';

			for (i = 0; i < data.length; i++) 
			{
				op = document.createElement("OPTION");
				op.setAttribute('value',data[i]['file_name']);
				if (i == 0) 
					op.setAttribute('selected','selected');
				op.innerHTML = data[i]['file_name'];
				se.appendChild(op);
			}

			td.appendChild(se);
			tb.setAttribute('align','center');
			tr.appendChild(td);	
			tb.appendChild(tr);

			tr = document.createElement("TR");
			td = document.createElement("TD");
		
			td.innerHTML = '<b>Nome do arquivo a ser criado:</b><br>';

			it = document.createElement("INPUT");
			it.setAttribute('type','text');
			it.setAttribute('name','tpl_novo_nome');
			it.setAttribute('value','arquivo.tpl');
			it.setAttribute('id','win_tpl_name');
			it.style.width = '200px';

			td.appendChild(it);
			tr.appendChild(td);	
			tb.appendChild(tr);

			tr = document.createElement("TR");
			td = document.createElement("TD");

			ch = document.createElement("INPUT");
			ch.setAttribute('type','checkbox');
			ch.setAttribute('name','tplrewrite');
			ch.setAttribute('onclick','set_tpl_rewrite()');
			td.appendChild(ch);
			td.innerHTML += 'Sobrescreve se já existe';
			td.setAttribute('valign','top');

			tr.appendChild(td);	
			tb.appendChild(tr);

			tr = document.createElement("TR");
			td = document.createElement("TD");
			td.setAttribute('align','center');

			bt = document.createElement("INPUT");
			bt.setAttribute('type','button');
			bt.setAttribute('value','Ok');
			bt.setAttribute('name','Ok');
			bt.setAttribute('onclick','create_new_template()');
			bt.style.width = '70px';
			td.appendChild(bt);

			bt = document.createElement("INPUT");
			bt.setAttribute('type','button');
			bt.setAttribute('value','Cancelar');
			bt.setAttribute('name','Cancelar');
			bt.setAttribute('onclick','win.close()');
			bt.style.width = '70px';
			td.appendChild(bt);

			tr.appendChild(td);	
			tb.appendChild(tr);

			div.appendChild(tb);	

			document.body.appendChild(div);
		}
		show_window('Modelos de Template',div,250,270);
	};

	cExecute ("$this.bo_adminsource.get_model_files", showHnd, "type=template");
}

function sort_tpl_list(field)
{
	var icon_order;

	tpl_sort_field = field;
	tpl_sort_asc   = (tpl_sort_asc == 1) ? 0 : 1;

	redraw_template_list();
}

function createTemplateList(folder)
{
	var table = document.createElement("TABLE");
    var body  = document.createElement("TBODY");
	var tr    = document.createElement("TR");
	var td    = new Array(4);

	var fillTemplateList = function(data) {
		var tr;
		var td;		
		
		if (data != null) {
			for (i = 0; i < data.length; i++) {
				tr = document.createElement("TR");
				td = new Array(5);
	
				for (j = 0; j < td.length; j++) {
					td[j] = document.createElement('TD');
				}
	 
				tr.className = 'table_elements_tr_line';

				if (i % 2) {
                    tr.style.backgroundColor = '#FFFFFF';
                } else {
                    tr.style.backgroundColor = '#F5F5F5';
                }

				td[0].align = 'left';
				td[0].innerHTML = get_link("javascript:void(0)", data[i]['file_name'],"onclick=\"window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_templateeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type="+data[i]['tipo_codigo']+"&proc_id="+data[i]['proc_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')\"");
					
				td[1].align = 'center';
				td[1].innerHTML = data[i]['tamanho'];	
		
				td[2].align = 'center';
				td[2].innerHTML = data[i]['modificado'];	
	
				td[3].align = 'center';
				td[3].innerHTML = get_link("javascript:void(0)", get_icon('phpedit.png','Editar Template','hspace=1'),"onclick=\"window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_templateeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type="+data[i]['tipo_codigo']+"&proc_id="+data[i]['proc_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')\"") + 
							      get_link(_web_server_url+"/index.php?menuaction=workflow.bo_adminsource.export_file&file_name="+escape(data[i]['file_name'])+"&type="+data[i]['tipo_codigo']+"&proc_id="+proc_id,get_icon('phpexport.png','Exportar Template','hspace=1'),"") +
								  get_link("javascript:void(0)", get_icon('del_template.png','Excluir Template','hspace=1'),"onclick=\"delete_template('"+data[i]['file_name']+"')\""); 
								 	
				for (j = 0; j < td.length; j++) {
					tr.appendChild(td[j]);
				}
	
				body.appendChild(tr);
			}
		}
	};

    table.setAttribute("cellPadding", "2");
    table.className = "table_elements";
    table.id        = 'template_list';

	tr.className = "table_elements_tr_header";

	for (i = 0; i < td.length; i++) { 
		td[i] = document.createElement('TD'); 
		td[i].style.cursor = 'pointer';
	}

	icon_order = (tpl_sort_asc == 1) ? get_icon('arrow_descendant.gif','Crescente','hspace=1') : get_icon('arrow_ascendant.gif','Decrescente','hspace=1') ;
	
	td[0].setAttribute('width', '60%');
	td[0].align = 'left';
	td[0].innerHTML = 'Nome do Arquivo';
	td[0].onclick = function() { sort_tpl_list('file_name'); };
	td[0].innerHTML = ( tpl_sort_field == 'file_name' ) ? td[0].innerHTML = '<b>Nome do Arquivo</b>' + icon_order : td[0].innerHTML;
		
	td[1].setAttribute('width', '10%');
	td[1].align = 'center';
	td[1].innerHTML = 'Tamanho';	
	td[1].onclick = function() { sort_tpl_list('tamanho'); };
	td[1].innerHTML = ( tpl_sort_field == 'tamanho' ) ? td[1].innerHTML = '<b>Tamanho</b>' + icon_order : td[1].innerHTML;
	
	td[2].setAttribute('width', '20%');
	td[2].align = 'center';
	td[2].innerHTML = 'Modificado';	
	td[2].onclick = function() { sort_tpl_list('modificado'); };
	td[2].innerHTML = ( tpl_sort_field == 'modificado' ) ? td[2].innerHTML = '<b>Modificado</b>' + icon_order : td[2].innerHTML;

	td[3].setAttribute('width', '10%');
	td[3].align = 'center';
	td[3].innerHTML = 'Ações';	

	for (i = 0; i < td.length; i++) 
		tr.appendChild(td[i]); 

	body.appendChild(tr);
	table.appendChild(body);
	folder.appendChild(table);

	cExecute ("$this.bo_adminsource.get_template_files", fillTemplateList, "proc_id="+proc_id+"&sort="+tpl_sort_field+"&order_by="+tpl_sort_asc);
}

function delete_template(file_name)
{
	var deleteHnd = function(data) {
		var templates_folder = getFolder(ID_TEMPLATES_FOLDER);
		
		alert(data);
		killElement('template_list');
		createTemplateList(templates_folder);
	};

	if (confirm("Tem certeza que deseja excluir o template "+file_name+"?")) 
	{
		cExecute ("$this.bo_adminsource.delete_file", deleteHnd, "proc_id="+proc_id+"&type=template&file_name="+file_name);
	}
}

function redraw_template_list()
{
	var templates_folder = getFolder(ID_TEMPLATES_FOLDER);
		
	killElement('template_list');
	createTemplateList(templates_folder);
}

function create_new_template() 
{
	var createHnd = function(data) {
		var templates_folder = getFolder(ID_TEMPLATES_FOLDER);
	
		switch(data) 
		{
			case 0: alert('Erro: Nome de arquivo inválido.');
					break;
			case 1: alert('Erro: ID de processo inválido.');
					break;
			case 2: alert('Arquivo já existe.');
					break;
			case 3: alert('Arquivo criado com sucesso.');
					break;
			case 4: alert('Não foi possível criar o arquivo.');
					break;
		}

		killElement('template_list');
		createTemplateList(templates_folder);
	};

	tpl_model = document.getElementById('win_tpl_model');
    modelo = tpl_model.value;

	text = document.getElementById('win_tpl_name');
	file_name = text.value;
	if (file_name) {
		if (tpl_rewrite) {
			rewrite_file = 1;
		} else {
			rewrite_file = 0;
		}
		cExecute ("$this.bo_adminsource.create_file", createHnd, "rewrite="+rewrite_file+"&modelo="+modelo+"&proc_id="+proc_id+"&type=template&file_name="+file_name);
		win.close();
	} else {
		alert('É necessário informar o nome do arquivo a ser criado.');
		text.focus();
	}
}

function draw_template_toolbar(folder) {
	var toolbar_cell = document.getElementById('td_main_toolbar_'+folder.id+'_1');

    template_tool = new TMainMenu("template_tool",'horizontal');

	_new_template = new TPopMenu('Novo Template',_icon_dir + '/new_template.png','f','show_template_window()', 'Insere Novo Template');
	template_tool.Add( _new_template );

	//_new_template.Add( new TPopMenu('Em Branco',_icon_dir + '/arrow.gif','f','create_new_template(0)', 'Em Branco') );
	//_new_template.Add( new TPopMenu('Modelo de Consulta',_icon_dir + '/arrow.gif','f','create_new_template(1)', 'Modelo Consulta') );
	//_new_template.Add( new TPopMenu('Modelo de Cadastro',_icon_dir + '/arrow.gif','f','create_new_template(2)', 'Modelo Cadastro') );
	//_new_template.Add( new TPopMenu('Modelo de Visualizar',_icon_dir + '/arrow.gif','f','create_new_template(3)', 'Modelo Visualizar') );
	
	setToolBarStyle(template_tool);
	template_tool.Build(toolbar_cell.id);
	document.getElementById(template_tool._id).style.visibility = 'visible';
}

function draw_templates_folder() {
 
	var templates_folder = getFolder(ID_TEMPLATES_FOLDER);
	var toolbar;

	toolbar = document.getElementById("wf_toolbar_"+templates_folder.id);

	if (toolbar == null) 
	{
		createToolBar(ID_TEMPLATES_FOLDER,false);
		draw_template_toolbar(templates_folder);
	}

	createTemplateList(templates_folder);
}
