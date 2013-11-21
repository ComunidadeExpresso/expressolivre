var ID_CONTROL_FOLDER   = 0;
var window_list = new Array;

/* permissions index */
var permissionList = new Array();
for (var i = 0; i < 11; i++)
	permissionList[i] = new Array();
permissionList[0]['name'] = "Alterar prioridade da instância";
permissionList[0]['value'] = 0;
permissionList[1]['name'] = "Alterar usuário da instância";
permissionList[1]['value'] = 1;
permissionList[2]['name'] = "Alterar status da instância";
permissionList[2]['value'] = 2;
permissionList[3]['name'] = "Alterar identificador da instância";
permissionList[3]['value'] = 3;
permissionList[4]['name'] = "Alterar a atividade da instância";
permissionList[4]['value'] = 4;
permissionList[5]['name'] = "Visualizar as propriedades da instância";
permissionList[5]['value'] = 5;
permissionList[6]['name'] = "Editar as propriedades da instância";
permissionList[6]['value'] = 6;
permissionList[7]['name'] = "Visualizar estatísticas";
permissionList[7]['value'] = 7;
permissionList[8]['name'] = "Remover instâncias finalizadas";
permissionList[8]['value'] = 8;
permissionList[9]['name'] = "Substituir usuário";
permissionList[9]['value'] = 9;
permissionList[10]['name'] = "Disparar e-mails";
permissionList[10]['value'] = 10;

var permissionListORG = new Array();
for (var i = 0; i < 2; i++)
	permissionListORG[i] = new Array();
permissionListORG[0]['name'] = "Administrar Organograma";
permissionListORG[0]['value'] = 0;
permissionListORG[1]['name'] = "Visualizar Informações Restritas";
permissionListORG[1]['value'] = 1;

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

function init_user_interface() {

	if ((!is_gecko) && (!is_ie6up)) {
		alert('Seu navegador não suporta o módulo de Workflow.\nInstale o Mozilla FireFox 1.0+ ou Internet Explorer 6.0+.');
	} else {
		BordersArray[0] = new setBorderAttributes(0);
		BordersArray[1] = new setBorderAttributes(1);
		BordersArray[2] = new setBorderAttributes(2);
		BordersArray[3] = new setBorderAttributes(3);
		BordersArray[4] = new setBorderAttributes(4);
		BordersArray[5] = new setBorderAttributes(5);

		var main_body = document.getElementById("main_body");
		main_body.style.display = '';

		if (alternate_border(ID_CONTROL_FOLDER) == 0) {
			draw_control_folder();
		}
	}
}

Event.observe(window, 'load', function() {
	init_user_interface();
});
