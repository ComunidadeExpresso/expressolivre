var inc_sort_field = 'file_name';
var inc_sort_asc   = 1;
var rewrite = false;

function set_rewrite()
{
	rewrite = !rewrite;
}

function show_include_window()
{
	var showHnd = function(data)
	{

		div = document.getElementById('model_include_list');
		if (!div) {

			div = document.createElement("DIV");
			div.style.visibility = "hidden";
			div.style.position   = "absolute";
			div.id 				 = "model_include_list";
		
			tb = document.createElement("TABLE");
			tr = document.createElement("TR");
			td = document.createElement("TD");

			td.innerHTML = '<b>Selecione o modelo desejado:</b><br>';
			se = document.createElement("SELECT");
			se.setAttribute('name','file');
			se.setAttribute('size','10');
			se.setAttribute('id','win_include_model');
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
			it.setAttribute('name','novo_nome');
			it.setAttribute('value','arquivo.php');
			it.setAttribute('id','win_include_name');
			it.style.width = '200px';

			td.appendChild(it);
			tr.appendChild(td);	
			tb.appendChild(tr);

			tr = document.createElement("TR");
			td = document.createElement("TD");

			ch = document.createElement("INPUT");
			ch.setAttribute('type','checkbox');
			ch.setAttribute('name','rewrite');
			ch.setAttribute('onclick','set_rewrite()');
			ch.setAttribute('id','chk_rewrite_file');
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
			bt.setAttribute('onclick','create_new_include()');
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
		show_window('Modelos de Classe',div,250,270);
	};

	cExecute ("$this.bo_adminsource.get_model_files", showHnd, "type=include");
}


function sort_include_list(field)
{
	inc_sort_field = field;
	inc_sort_asc   = (inc_sort_asc == 1) ? 0 : 1;
	redraw_include_list();
}

function createIncludeList(folder)
{
	var table = document.createElement("TABLE");
    var body  = document.createElement("TBODY");
	var tr    = document.createElement("TR");
	var td    = new Array(4);

	var fillIncludeList = function(data) {
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
				td[0].innerHTML = data[i]['file_name'];
				td[0].innerHTML = get_link("javascript:void(0)", data[i]['file_name'],"onclick=\"window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_phpeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type="+data[i]['tipo_codigo']+"&proc_id="+data[i]['proc_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')\"");
					
				td[1].align = 'center';
				td[1].innerHTML = data[i]['tamanho'];	
		
				td[2].align = 'center';
				td[2].innerHTML = data[i]['modificado'];	
	
				td[3].align = 'left';
				td[3].innerHTML = get_link("javascript:void(0)", get_icon('phpedit.png','Editar Include','hspace=1'),"onclick=\"window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_phpeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type="+data[i]['tipo_codigo']+"&proc_id="+data[i]['proc_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')\"") + 
							      get_link(_web_server_url+"/index.php?menuaction=workflow.bo_adminsource.export_file&file_name="+escape(data[i]['file_name'])+"&type="+data[i]['tipo_codigo']+"&proc_id="+proc_id,get_icon('phpexport.png','Exportar Include','hspace=1'),"");
			
				if (data[i]['file_name'] != 'shared.php') {
					td[3].innerHTML += get_link("javascript:void(0)", get_icon('del_template.png','Excluir Include','hspace=1'),"onclick=\"delete_include('"+data[i]['file_name']+"')\""); 
				}
								 	
				for (j = 0; j < td.length; j++) {
					tr.appendChild(td[j]);
				}
	
				body.appendChild(tr);
			}
		}
	};

    table.setAttribute("cellPadding", "2");
    table.className = "table_elements";
    table.id        = 'include_list';

	tr.className = "table_elements_tr_header";

	for (i = 0; i < td.length; i++) { 
		td[i] = document.createElement('TD'); 
		td[i].style.cursor = 'pointer';
	}
	
	icon_order = (inc_sort_asc == 1) ? get_icon('arrow_descendant.gif','Crescente','hspace=1') : get_icon('arrow_ascendant.gif','Decrescente','hspace=1') ;

	td[0].setAttribute('width', '63%');
	td[0].align = 'left';
	td[0].innerHTML = 'Nome do Arquivo';
	td[0].onclick = function() { sort_include_list('file_name'); };
	td[0].innerHTML = ( inc_sort_field == 'file_name' ) ? td[0].innerHTML = '<b>Nome do Arquivo</b>' + icon_order : td[0].innerHTML;
		
	td[1].setAttribute('width', '10%');
	td[1].align = 'center';
	td[1].innerHTML = 'Tamanho';	
	td[1].onclick = function() { sort_include_list('tamanho'); };
	td[1].innerHTML = ( inc_sort_field == 'tamanho' ) ? td[1].innerHTML = '<b>Tamanho</b>' + icon_order : td[1].innerHTML;
	
	td[2].setAttribute('width', '20%');
	td[2].align = 'center';
	td[2].innerHTML = 'Modificado';	
	td[2].onclick = function() { sort_include_list('modificado'); };
	td[2].innerHTML = ( inc_sort_field == 'modificado' ) ? td[2].innerHTML = '<b>Modificado</b>' + icon_order : td[2].innerHTML;

	td[3].setAttribute('width', '7%');
	td[3].align = 'center';
	td[3].innerHTML = 'Ações';	

	for (i = 0; i < td.length; i++) 
		tr.appendChild(td[i]); 

	body.appendChild(tr);
	table.appendChild(body);
	folder.appendChild(table);

	cExecute ("$this.bo_adminsource.get_include_files", fillIncludeList, "proc_id="+proc_id+"&sort="+inc_sort_field+"&order_by="+inc_sort_asc);
}

function redraw_include_list()
{
	var include_folder = getFolder(ID_INCLUDES_FOLDER);
	killElement('include_list');
	createIncludeList(include_folder);
}

function delete_include(file_name)
{
	var deleteHnd = function(data) {
		redraw_include_list();
	};

	if (confirm("Tem certeza que deseja excluir o include "+file_name+"?")) 
	{
		cExecute ("$this.bo_adminsource.delete_file", deleteHnd, "proc_id="+proc_id+"&type=include&file_name="+file_name);
	}
}

var createHnd = function(data) {
	var includes_folder = getFolder(ID_INCLUDES_FOLDER);
		
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
	killElement('include_list');
	createIncludeList(includes_folder);
};


function create_new_include() 
{

	include_model = document.getElementById('win_include_model'); 
	modelo = include_model.value;


	text = document.getElementById('win_include_name'); 
	file_name = text.value;
	if (file_name) {
		if (rewrite) {
			rewrite_file = 1;
		} else {
			rewrite_file = 0;
		}
		cExecute ("$this.bo_adminsource.create_file", createHnd, "rewrite="+rewrite_file+"&modelo="+modelo+"&proc_id="+proc_id+"&type=include&file_name="+file_name);
		win.close();
	} else {
		alert('É necessário informar o nome do arquivo a ser criado.');
		text.focus();
	}
}

function draw_include_toolbar(folder) {
	var toolbar_cell = document.getElementById('td_main_toolbar_'+folder.id+'_1');

    include_tool = new TMainMenu("include_tool",'horizontal');
	include_tool.Add( new TPopMenu('Novo Include',_web_server_url+'/workflow/templateFile.php?file=images/new_template.png','f','show_include_window()', 'Insere Novo Include') );
	
	setToolBarStyle(include_tool);
	//alert(toolbar_cell.innerHTML);
	include_tool.Build(toolbar_cell.id);
	document.getElementById(include_tool._id).style.visibility = 'visible';
}

function draw_includes_folder() {
 
	var includes_folder = getFolder(ID_INCLUDES_FOLDER);
	var toolbar;

	toolbar = document.getElementById("wf_toolbar_"+includes_folder.id);
	if (toolbar == null) 
	{
		createToolBar(ID_INCLUDES_FOLDER,false);
		draw_include_toolbar(includes_folder);
	}

	createIncludeList(includes_folder);
}
