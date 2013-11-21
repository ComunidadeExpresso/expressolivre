var res_sort_field = 'file_name';
var res_sort_asc   = 1;

function sort_resource_list(field)
{
	res_sort_field = field;
	res_sort_asc   = (res_sort_asc == 1) ? 0 : 1;

	redraw_resource_list();
}

function createResourcesList(folder)
{
	var table = document.createElement("TABLE");
    var body  = document.createElement("TBODY");
	var tr    = document.createElement("TR");
	var td    = new Array(5);

	var fillResourcesList = function(data) {
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
	
				file_name = data[i]['file_name'];

				td[0].align = 'left';
				if ((file_name.indexOf('.css') > 0) || (file_name.indexOf('.js') > 0))
					td[0].innerHTML = get_link("javascript:void(0)", data[i]['file_name'],"onclick=\"window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_resourceeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type=resource&proc_id="+data[i]['proc_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')\"");
				else
					if ((file_name.indexOf('.jpg') > 0) || (file_name.indexOf('.png') > 0) || (file_name.indexOf('.gif') > 0))
						td[0].innerHTML = '<a rel="lightbox" href="workflow/redirect.php?pid=' + data[i]['proc_id'] + '&file=' + data[i]['file_name'] + '" title="Arquivo: ' + data[i]['file_name'] + '">' + data[i]['file_name'] + '</a>';
					else
						td[0].innerHTML = data[i]['file_name'];
					
				td[1].align = 'center';
				td[1].innerHTML = data[i]['tipo'];	
	
				td[2].align = 'center';
				td[2].innerHTML = data[i]['tamanho'];	
		
				td[3].align = 'center';
				td[3].innerHTML = data[i]['modificado'];	
	
				td[4].align = 'center';
			
				if ((file_name.indexOf('.css') > 0) || (file_name.indexOf('.js') > 0)) {
					edit_button = get_link("javascript:void(0)", get_icon('phpedit.png','Editar Resource','hspace=1'),"onclick=\"window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_resourceeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type=resource&proc_id="+data[i]['proc_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')\"");
				} else {
					edit_button = get_icon('phpeditpb.png','Editar Resource','hspace=1');
				}
				
				td[4].innerHTML = edit_button +  
								  get_link(_web_server_url+"/index.php?menuaction=workflow.bo_adminsource.export_file&file_name="+escape(data[i]['file_name'])+"&type="+data[i]['tipo_arquivo']+"&proc_id="+proc_id,get_icon('phpexport.png','Exportar Resource','hspace=1'),"") +
								  get_link("javascript:void(0)", get_icon('del_template.png','Excluir Resource','hspace=1'),"onclick=\"delete_resource('"+data[i]['file_name']+"')\""); 
	
				for (j = 0; j < td.length; j++) {
					tr.appendChild(td[j]);
				}
	
				body.appendChild(tr);
			}
		}
		initLightbox();
	};

    table.setAttribute("cellPadding", "2");
    table.className = "table_elements";
    table.id        = 'resource_list';

	tr.className = "table_elements_tr_header";

	for (i = 0; i < td.length; i++) { 
		td[i] = document.createElement('TD'); 
		td[i].style.cursor = 'pointer';
	}
	
	icon_order = (res_sort_asc == 1) ? get_icon('arrow_descendant.gif','Crescente','hspace=1') : get_icon('arrow_ascendant.gif','Decrescente','hspace=1') ;

	td[0].setAttribute('width', '50%');
	td[0].align = 'left';
	td[0].innerHTML = 'Nome do Arquivo';
	td[0].onclick = function() { sort_resource_list('file_name'); };
	td[0].innerHTML = ( res_sort_field == 'file_name' ) ? td[0].innerHTML = '<b>Nome do Arquivo</b>' + icon_order : td[0].innerHTML;
		
	td[1].setAttribute('width', '10%');
	td[1].align = 'center';
	td[1].innerHTML = 'Tipo';	
	td[1].onclick = function() { sort_resource_list('tipo'); };
	td[1].innerHTML = ( res_sort_field == 'tipo' ) ? td[1].innerHTML = '<b>Tipo</b>' + icon_order : td[1].innerHTML;

	td[2].setAttribute('width', '10%');
	td[2].align = 'center';
	td[2].innerHTML = 'Tamanho';	
	td[2].onclick = function() { sort_resource_list('tamanho'); };
	td[2].innerHTML = ( res_sort_field == 'tamanho' ) ? td[2].innerHTML = '<b>Tamanho</b>' + icon_order : td[2].innerHTML;
	
	td[3].setAttribute('width', '20%');
	td[3].align = 'center';
	td[3].innerHTML = 'Modificado';	
	td[3].onclick = function() { sort_resource_list('modificado'); };
	td[3].innerHTML = ( res_sort_field == 'modificado' ) ? td[3].innerHTML = '<b>Modificado</b>' + icon_order : td[3].innerHTML;

	td[4].setAttribute('width', '10%');
	td[4].align = 'center';
	td[4].innerHTML = 'Ações';	

	for (i = 0; i < td.length; i++) 
		tr.appendChild(td[i]); 

	body.appendChild(tr);
	table.appendChild(body);
	folder.appendChild(table);

	cExecute ("$this.bo_adminsource.get_resource_files", fillResourcesList, "proc_id="+proc_id+"&sort="+res_sort_field+"&order_by="+res_sort_asc);
}

function new_resource()
{
	var createHnd = function(data) {
		var resources_folder = getFolder(ID_RESOURCES_FOLDER);
		
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
		killElement('resource_list');
		createResourcesList(resources_folder);
	};

	file_name = prompt('Informe o nome do arquivo:','arquivo.js');
	if (file_name.length > 0) {
		cExecute ("$this.bo_adminsource.create_file", createHnd, "rewrite=0&proc_id="+proc_id+"&type=resource&file_name="+file_name);
	}
}

function delete_resource(file_name)
{
	var deleteHnd = function(data) {
		//alert(data);
		redraw_resource_list();
	};

	if (confirm("Tem certeza que deseja excluir o resource "+file_name+"?")) 
	{
		cExecute ("$this.bo_adminsource.delete_file", deleteHnd, "proc_id="+proc_id+"&type=resource&file_name="+file_name);
	}
}

function redraw_resource_list()
{
	var resources_folder = getFolder(ID_RESOURCES_FOLDER);
	frmUpload.reset();
	killElement('resource_list');
	createResourcesList(resources_folder);
}


function upload_resource() 
{
	var uploadHnd = function(data) {
		var resources_folder = getFolder(ID_RESOURCES_FOLDER);
		alert(data);
		redraw_resource_list();
	};

	cExecuteFormData("$this.bo_adminsource.upload_resource",frmUpload,uploadHnd);
}

function toggle_upload_row(folder_id)
{
	var row = document.getElementById('td_tool_bar_'+folder_id+'_0').parentNode;
	row.style.display = (row.style.display == 'none') ? '' : 'none';
}

function draw_resources_toolbar(folder) {
	var toolbar_row_0 = document.getElementById('td_tool_bar_'+folder.id+'_0').parentNode;
	var toolbar_cell_1 = document.getElementById('td_main_toolbar_'+folder.id+'_1');

	//toolbar_cell_0.setAttribute('width','10px');
	 toolbar_row_0.style.display = "none";

    resource_tool = new TMainMenu("resource_tool",'horizontal');
	resource_tool.Add( new TPopMenu('Novo',_web_server_url+'/workflow/templateFile.php?file=images/new_template.png','f','new_resource()', 'Novo Resource') );
	resource_tool.Add( new TPopMenu('Importar',_web_server_url+'/workflow/templateFile.php?file=images/up_resource.png','f','toggle_upload_row(\'' + folder.id + '\')', 'Upload Resource') );
	setToolBarStyle(resource_tool);
	
	resource_tool.Build(toolbar_cell_1.id);
	document.getElementById(resource_tool._id).style.visibility = 'visible';

	/* the use of DOM in this part, is required by MSIE */
	while (toolbar_row_0.childNodes.length > 0)
		toolbar_row_0.removeChild(toolbar_row_0.firstChild);
	var td = document.createElement('TD');
	td.colSpan = 3;
	td.id = 'td_tool_bar_' + folder.id + '_0';
	td.innerHTML = '<form name=frmUpload method=POST enctype="multipart/form-data"><input type="hidden" name="MAX_FILE_SIZE" value="3000000"><input type=hidden name=proc_id value='+proc_id+'><input type=file name=resource_file><button onclick="upload_resource(); return false;">Upload</button>';
	toolbar_row_0.appendChild(td);
}

function draw_resources_folder() {
 
	var resources_folder = getFolder(ID_RESOURCES_FOLDER);
	var toolbar;

	toolbar = document.getElementById("wf_toolbar_"+resources_folder.id);
	if (toolbar == null) 
	{
		createToolBar(ID_RESOURCES_FOLDER,false);
		draw_resources_toolbar(resources_folder);
	}

	createResourcesList(resources_folder);
}
