var php_sort_field = 'file_name';
var php_sort_asc   = 1;
var php_toolbar;

function sort_file_list(field)
{
	php_sort_field = field;
	php_sort_asc   = (php_sort_asc == 1) ? 0 : 1;

	redraw_php_folder();
}

function createFileList(folder)
{
	var table = document.createElement("TABLE");
    var body  = document.createElement("TBODY");
	var tr    = document.createElement("TR");
	var td    = new Array(5);

	var fillFileList = function(data) {
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
					td[0].innerHTML = get_link("javascript:void(0)", data[i]['file_name'],"onclick=window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_phpeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type="+data[i]['tipo_codigo']+"&proc_id="+data[i]['proc_id']+"&activity_id="+data[i]['activity_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')");
						
					td[1].align = 'center';
					td[1].innerHTML = activity_icon(data[i]['tipo_atividade'], data[i]['interativa']);	
					
					td[2].align = 'center';
					td[2].innerHTML = data[i]['tamanho'];	
			
					td[3].align = 'center';
					td[3].innerHTML = data[i]['modificado'];	

					td[4].align = 'center';
					td[4].innerHTML = get_link("javascript:void(0)", get_icon('phpedit.png','Editar','hspace=1'),"onclick=window.open('"+_web_server_url+"/index.php?menuaction=workflow.ui_phpeditor.form&proc_name="+data[i]['proc_name']+"&file_name="+data[i]['file_name']+"&type="+data[i]['tipo_codigo']+"&proc_id="+data[i]['proc_id']+"&activity_id="+data[i]['activity_id']+"','','width=850,height=680,screenX=100,left=10,screenY=100,top=10,toolbar=no,scrollbars=yes,resizable=yes')") + 
									  get_link(_web_server_url+"/index.php?menuaction=workflow.bo_adminsource.export_file&file_name="+data[i]['file_name']+"&type="+data[i]['tipo_codigo']+"&proc_id="+proc_id,get_icon('phpexport.png','Exportar','hspace=1'),""); 
										

					for (j = 0; j < td.length; j++) {
						tr.appendChild(td[j]);
					}

					body.appendChild(tr);
				}
		}
	};

	table.id = 'php_files';
    table.setAttribute("cellPadding", "2");
    table.className = "table_elements";

	tr.className = "table_elements_tr_header";


	for (i = 0; i < td.length; i++) { 
		td[i] = document.createElement('TD'); 
		td[i].style.cursor = 'pointer';
	}


	icon_order = (php_sort_asc == 1) ? get_icon('arrow_descendant.gif','Crescente','hspace=1') : get_icon('arrow_ascendant.gif','Decrescente','hspace=1') ;

	
	td[0].setAttribute('width', '50%');
	td[0].align = 'left';
	td[0].innerHTML = 'Nome do Arquivo';
	td[0].onclick = function() { sort_file_list('file_name'); };
	td[0].innerHTML = ( php_sort_field == 'file_name' ) ? td[0].innerHTML = '<b>Nome do Arquivo</b>' + icon_order : td[0].innerHTML;
		
	td[1].setAttribute('width', '10%');
	td[1].align = 'center';
	td[1].innerHTML = 'Tipo';	
	
	td[2].setAttribute('width', '10%');
	td[2].align = 'center';
	td[2].innerHTML = 'Tamanho';
	td[2].onclick = function() { sort_file_list('tamanho'); };
	td[2].innerHTML = ( php_sort_field == 'tamanho' ) ? td[2].innerHTML = '<b>Tamanho</b>' + icon_order : td[2].innerHTML;

	
	td[3].setAttribute('width', '20%');
	td[3].align = 'center';
	td[3].innerHTML = 'Modificado';
	td[3].onclick = function() { sort_file_list('modificado'); };
	td[3].innerHTML = ( php_sort_field == 'modificado' ) ? td[3].innerHTML = '<b>Modificado</b>' + icon_order : td[3].innerHTML;

	td[4].setAttribute('width', '10%');
	td[4].align = 'center';
	td[4].innerHTML = 'Ações';	

	for (i = 0; i < td.length; i++) 
		tr.appendChild(td[i]); 

	body.appendChild(tr);
	table.appendChild(body);
	folder.appendChild(table);

	cExecute ("$this.bo_adminsource.get_php_files", fillFileList, "proc_id="+proc_id+"&sort="+php_sort_field+"&order_by="+php_sort_asc);
}

function redraw_php_folder() {
	var php_folder = getFolder(ID_PHP_FOLDER);
	
	killElement('php_files');
	createFileList(php_folder);
}

function draw_php_folder() {
 
	var php_folder = getFolder(ID_PHP_FOLDER);

	php_toolbar = document.getElementById("wf_toolbar_"+php_folder.id);
	if (php_toolbar == null) 
	{
		createToolBar(ID_PHP_FOLDER);
	}

	createFileList(php_folder);
}
