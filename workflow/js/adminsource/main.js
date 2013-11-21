
var ID_PHP_FOLDER       = 0;
var ID_INCLUDES_FOLDER  = 1;
var ID_TEMPLATES_FOLDER = 2;
var ID_RESOURCES_FOLDER = 3;

var proc_id=0;
var php_toolbar;
var tpl_toolbar;
var inc_toolbar;
var res_toolbar;
var toolbar_name;
var window_list = new Array;

function show_window(titulo,div,width,height)
{
  if (!window_list[div.id]) 
  {
    if(is_ie){
        div.style.height = (height + 10) + 'px';
        div.style.width = (width + 50) + 'px';
    } else {
        div.style.height = height + 'px';
        div.style.width = width + 'px';
    }

    div.style.visibility = "hidden";
    div.style.position = "absolute";
    div.style.zIndex = "10002";
    var wHeight = div.offsetHeight + "px";
    var wWidth =  div.offsetWidth   + "px";

    win = new dJSWin({
         id: 'window_'+div.id,
         content_id: div.id,
         width: wWidth,
         height: wHeight,
         title_color: '#3978d6',
         bg_color: '#eee',
         title: titulo,
         title_text_color: 'white',
         button_x_img: _icon_dir + '/winclose.gif',
         border: true 
	});

    win.draw();
	window_list[div.id] = win;
  } else {
	win = window_list[div.id];
  }

  win.open();
}

function getFolder(id_folder) 
{
	return document.getElementById("content_id_"+id_folder);
}

function createToolBar(folder_id,show_proc_status) 
{
	var table = document.createElement("TABLE");
    var body  = document.createElement("TBODY");
	var tr    = document.createElement("TR");
	var tr2    = document.createElement("TR");
	var td    = new Array(3);
	var td2    = new Array(3);
	var show_proc_status = (show_proc_status == null) ? true : show_proc_status;
	var folder = getFolder(folder_id);

	var fillToolBar = function(data)
	{

		var combo = "";
		combo = '<select id="novoProcesso" onchange="window.location=this.value">';
		for (var i = 0; i < data['other_processes'].length; i++)
			combo += '<option value="' + data['other_processes'][i]['link']  + (data['other_processes'][i]['pid'] == data['proc_id'] ? " selected=\"selected\"" : "")  + '">'+ data['other_processes'][i]['name']  + '</option>';
		combo += '</select>';
		td[0].innerHTML = 'Processo:<br/>' + combo;
		if (show_proc_status)
			td[1].innerHTML = 'Status:<br/><img src='+data['img_validity']+'>&nbsp;<b>' + data['alt_validity'] + '</b>';

		eval(toolbar_name + " = new TMainMenu('"+toolbar_name+"','horizontal')"); 
		//Start - Stop
		if ( data['start_stop_img'].length > 0 ) {
			_start_stop = new TPopMenu((screen.width > 800) ?data['start_stop_desc']:' ',data['start_stop_img'],'a',data['start_stop_link'], data['start_stop_desc']+' Processo');
	    	eval(toolbar_name+'.Add( _start_stop )');
		}
		//Processo
		_processo = new TPopMenu((screen.width > 800) ?'Editar':' ',data['img_change'],'a',data['link_admin_processes'], 'Editar Dados do Processo');
	    eval(toolbar_name+'.Add( _processo )');
		//Atividades
		_atividades = new TPopMenu((screen.width > 800) ?'Atividades':' ',data['img_activity'],'a',data['link_admin_activities'], 'Atividades do Processo');
	    eval(toolbar_name+'.Add( _atividades )');
		//Perfis
		_perfis = new TPopMenu((screen.width > 800) ?'Perfis':' ',data['img_roles'],'a',data['link_admin_roles'], 'Editar Perfis do Processo');
	    eval(toolbar_name+'.Add( _perfis )');
		//Jobs
		_jobs = new TPopMenu((screen.width > 800) ?'Jobs':' ',data['img_job'],'a',data['link_admin_jobs'], 'Administrar Jobs do Processo');
	    eval(toolbar_name+'.Add( _jobs )');
		//Gráfico
		_grafico = new TPopMenu((screen.width > 800) ?'Gráfico':' ',data['img_process'],'a',data['link_graph'], 'Gráfico do Processo');
	    eval(toolbar_name+'.Add( _grafico )');
		//Exportar
		_exportar = new TPopMenu((screen.width > 800) ?'Exportar':' ',data['img_save'],'a',data['link_admin_export'] ,'Exportar Processo');
	    eval(toolbar_name+'.Add( _exportar )');

	    eval("setToolBarStyle( "+toolbar_name+" )");
	    eval(toolbar_name+'.Build(td[2].id)');
		td[2].setAttribute('align','right');
	    eval("document.getElementById("+toolbar_name+"._id).style.visibility = 'visible'");
	};

	for (i = 0; i < td.length; i++ )
	{
		td2[i] = document.createElement("TD");
		td2[i].setAttribute('id','td_tool_bar_'+folder.id+'_'+i);
		td2[i].setAttribute('valign','center');
		tr2.appendChild(td2[i]);	

		td[i] = document.createElement("TD");
		td[i].setAttribute('id','td_main_toolbar_'+folder.id+'_'+i);
		td[i].setAttribute('valign','center');
		tr.appendChild(td[i]);	
	}
	
   	table.setAttribute("id", "wf_toolbar_"+folder.id);
    table.setAttribute("width", "100%");
    table.setAttribute("cellpadding", "5");
    table.setAttribute("cellspacing", "0");

	switch (folder_id) 
	{
		case ID_PHP_FOLDER : 
						toolbar_name = 'php_toolbar';
						break;
		case ID_INCLUDES_FOLDER : 
						toolbar_name = 'inc_toolbar';
						break;
		case ID_TEMPLATES_FOLDER : 
						toolbar_name = 'tpl_toolbar';
						break;
		case ID_RESOURCES_FOLDER : 
						toolbar_name = 'res_toolbar';
						break;

	}

	body.appendChild(tr);
	body.appendChild(tr2);
    table.appendChild(body);
    folder.appendChild(table);

	cExecute ("$this.bo_adminsource.get_toolbar_data", fillToolBar, "proc_id="+proc_id);
}

function init_user_interface() {

	if ((!is_gecko) && (!is_ie6up)) {
		alert('Seu navegador não suporta o módulo de Workflow.\nInstale o Mozilla FireFox 1.0+ ou Internet Explorer 6.0+.');
	} else {
		BordersArray[0] = new setBorderAttributes(0);
		BordersArray[1] = new setBorderAttributes(1);
		BordersArray[2] = new setBorderAttributes(2);
		BordersArray[3] = new setBorderAttributes(3);

		var main_body = document.getElementById("main_body");
		main_body.style.display = '';
		
		if (alternate_border(ID_PHP_FOLDER) == 0) {
			draw_php_folder();
		}
	}
}

function setToolBarStyle(t) {
	t.SetPosition('relative',0,0);
	t.SetCorrection(1,-5);
	t.SetCellSpacing(0);	
	t.SetBackground('whitesmoke','','','');
	t.SetItemText('black','center','','','');
	t.SetItemTextHL('darkblue','center','','','');
	t.SetItemBorder(1,'buttonface','solid');
	t.SetItemBorderHL(1,'black','solid');
	t.SetItemBackgroundHL('white','','','');
	t.SetItemTextClick('white','center','','','');
	t.SetItemBackgroundClick('darkblue','','','');
	t.SetItemBorderClick(1,'black','solid');
	t.SetBorder(0,'navy','solid');
	
	t.SetItemDimension(200,22);		
	t._pop.SetCorrection(4,1);
	t._pop.SetItemDimension(180,22);		
	t._pop.SetPaddings(1);
	t._pop.SetBackground('white','','','');
	t._pop.SetSeparator(150,'left','black','');
	t._pop.SetExpandIcon(true,'>',9);
	t._pop.SetItemBorder(0,'#66CCFF','solid');
	t._pop.SetItemBorderHL(0,'black','solid');
	t._pop.SetItemPaddings(0);
	t._pop.SetItemPaddingsHL(0);
	t._pop.SetItemText('black','','','','');
	t._pop.SetItemTextHL('darkblue','','','','');
	t._pop.SetItemBackground('white','','','');
	t._pop.SetItemBackgroundHL('whitesmoke','','','');
}


function change_folder(folder_id) 
{
	//verifica se a pasta ainda não foi criada
	if (!alternate_border(folder_id)) {
		switch (folder_id) 
		{
			case ID_PHP_FOLDER       :  draw_php_folder();
										break;
			case ID_TEMPLATES_FOLDER : 	draw_templates_folder(); 
										break;
			case ID_RESOURCES_FOLDER :  draw_resources_folder();
										break;
			case ID_INCLUDES_FOLDER :   draw_includes_folder();
										break;
		}
	}
}


Event.observe(window, 'load', function() {
	proc_id = $F('workflowAdminSourceProcessID');
	init_user_interface();
});
