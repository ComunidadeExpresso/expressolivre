/* configuração do menu */
var MENU_POS = [
{
	// tamanho dos itens
	'height': 19,
	'width': 255,
	'auto-width': true,
	// offset do menu a partir da origem:
	//  para o nível principal, a origem é o canto superior esquerdo
	//  para outros níveis, a origem é o canto superior esquerdo do menu pai
	'block_top': 186,
	'block_left': 151,
	// offsets de itens no mesmo nível
	'top': 19,
	'left': 0,
	// tempo, em milisegundos, em que o menu permanece visível após o cursor "sair" do menu
	'hide_delay': 300,
	'css' : {
		'outer' : ['menuExternoOut', 'menuExternoOver'],
		'inner' : ['menuInternoOut', 'menuInternoOver']
	}
},
{
	'width': 140,
	'block_top': 10,
	'block_left': 30,
	'css' : {
		'outer' : ['submenuExternoOut', 'submenuExternoOver'],
		'inner' : ['submenuInternoOut', 'submenuInternoOver']
	}
}
];

var menuTimer = null;
var workflowProcessCache = false;

function checkProcessCache()
{
	if (workflowProcessCache == false)
		return;
	resultProcesses(workflowProcessCache);
	workflowProcessCache = false;
}

function resultProcesses(data)
{
	if (_checkError(data))
		return;

	var content_id_1 = $("content_id_1");
	if (content_id_1.style.display == 'none')
	{
		workflowProcessCache = data;
		content_id_1.innerHTML = '<span></span>';
		return;
	}

	if (data.length == 0)
	{
		var parag = document.createElement("P");
		parag.className = "text_dsp";
		parag.innerHTML = "Não existem processos disponíveis";
		content_id_1.appendChild(parag);
	}
	else
	{
		elem = document.getElementById("table_tools");
		if (elem)
			elem.parentNode.removeChild(elem);
		elem = document.getElementById("table_proc");
		if (elem)
			elem.parentNode.removeChild(elem);
		draw_processes_grid(data, 1);
	}
}
function draw_processes_folder()
{
	cExecute("$this.bo_userinterface.processes", resultProcesses);
}

function draw_processes_grid(data, page)
{
	var content_id_1 = document.getElementById("content_id_1");

	if(is_ie){
		content_id_1.style.height = "260px";
	} else {
		content_id_1.style.minHeight = "260px";
	}

	var div_conteiner = document.createElement("DIV");

	div_conteiner.style.paddingRight = '100px';
	content_id_1.appendChild(div_conteiner);

	(function loop(i) {

		if(i < data.length){

			var proc = data[i];
			var proc_name_dsp = proc.wf_procname;

			if (proc_name_dsp.length > 40) {
			    proc_name_dsp = proc_name_dsp.substr(0,40) + "...";
			}

			var div_element = document.createElement("DIV");

			div_element.style.width = '106px';

			if(is_ie) {
				div_element.style.styleFloat = "left";
				div_element.style.height = '150px';
			} else {
				div_element.style.cssFloat = "left";
				div_element.style.height = '100px'
			}

			div_element.style.padding = '7px';
			div_element.style.paddingTop = "25px";
			div_element.style.cursor = 'pointer';

			var index = i;

			div_element.onclick = function() { displayProcessMenu(index); };
			div_element.onmouseover = function() { menuTimer = setTimeout("displayProcessMenu('" + index + "')",400);};
			div_element.onmouseout = function() { if (menuTimer) clearTimeout(menuTimer); };

			var div_proc_img = document.createElement("DIV");
			div_proc_img.style.width = "100%";
			div_proc_img.style.textAlign = 'center';
			div_proc_img.innerHTML = '<img src="' + proc.wf_iconfile + '" id="processImage_' + i + '" width="32" height="32">';

			var div_proc_txt = document.createElement("DIV");
			div_proc_txt.style.width = "100%";
			div_proc_txt.style.textAlign = 'center';
			div_proc_txt.style.paddingTop = '5px';
			div_proc_txt.innerHTML = '<span style="font-size: 11px !important;">'+proc_name_dsp+'</span>';
			div_proc_txt.innerHTML += '<br><span class="version_dsp"> (v' + proc.wf_version + ')</span></p>';
			div_element.appendChild(div_proc_img);
			div_element.appendChild(div_proc_txt);

			div_conteiner.appendChild(div_element);

			createProcessMenu(i, data[i]);

			loop(i+1);
		}
	})(0)

	var div_bottom = document.createElement("DIV");
	div_bottom.style.width = "100%";
	div_bottom.style.clear = 'both';

	div_conteiner.appendChild(div_bottom);
}

function createProcessMenu(index, data)
{
	/* based on the wf_menu_path activity property, generates a javascript object */
	var preFormatedMenu = new Array();
	var currentLevel;
	var urlPreffix = getInstanceURL(0, 0, data['useHTTPS']);
	urlPreffix = urlPreffix.substr(0, urlPreffix.lastIndexOf('/'));

	for (var i = 0; i < data.length; i++)
	{
		var currentMenu = new Array();
		currentMenu['name'] = data[i].wf_name;
		currentMenu['url'] = urlPreffix + '/index.php?menuaction=workflow.run_activity.go&activity_id=' + data[i].wf_activity_id;
		if ((data[i].wf_menu_path != '') && (data[i].wf_menu_path != null))
		{
			var levels = data[i].wf_menu_path.split('/');
			currentLevel = preFormatedMenu;
			for (var j = 0; j < levels.length; j++)
			{
				var currentTitle = levels[j];
				if (currentLevel[currentTitle])
				{
					currentLevel = currentLevel[currentTitle];
					continue;
				}
				else
				{
					currentLevel[currentTitle] = new Array();
					currentLevel = currentLevel[currentTitle];
				}
			}
			currentLevel[currentLevel.length] = currentMenu;
		}
		else
			preFormatedMenu[preFormatedMenu.length] = currentMenu;
	}

	/* generate standard actions menu */
	var graphMenu = new Array();
	graphMenu['name'] = 'Gráfico do Processo';
	graphMenu['url'] = "javascript:draw_process_graph(" + data['wf_p_id'] + ",\\'" + data['wf_procname'] + "\\');";
	preFormatedMenu[preFormatedMenu.length] = graphMenu;
	var aboutMenu = new Array();
	aboutMenu['name'] = 'Sobre o Processo';
	aboutMenu['url'] = "javascript:draw_folder_about(" + data['wf_p_id'] + ");";
	preFormatedMenu[preFormatedMenu.length] = aboutMenu;

	/* recursivamente, constrói o texto que representa o objeto reconhecido pela engine do Tigra menu */
	function generateMenuText(root)
	{
		var output = '';
		for (i in root)
		{
			if (typeof root[i] != "function")
			{
				if (node)
					output += ",\n";
				output += '[';
				var node = root[i];
				if (node['url'])
					output += "'" + node['name'] + "', '" + node['url'] + "', null";
				else
					output += "'" + i + '...'  + "', null, null,\n" + generateMenuText(node);
				output += ']';
			}
		}
		return output;
	}

	/* generate the menu in a way that the Tigra menu engine will understand */
	var menuItens = eval('[' + generateMenuText(preFormatedMenu) + '];');

	var menuDiv = document.createElement("DIV");
	menuDiv.setAttribute("id", "menuDiv_" + index);
	menuDiv.style.display = 'none';
	$("content_id_1").appendChild(menuDiv);

	/* define the menu position */
	var processImage = $("processImage_" + index);
	MENU_POS[0]['block_left'] = Position.cumulativeOffset(processImage)[0] + processImage.getWidth();
	MENU_POS[0]['block_top'] = Position.cumulativeOffset(processImage)[1];

	/* create the menu */
	new menu (menuItens, MENU_POS, menuDiv);
}

function displayProcessMenu(id)
{
	for (var i = 0; i < A_MENUS.length; i++)
		if (i == id){

			var processImage = $("processImage_" + id);
			var posX = Position.cumulativeOffset(processImage)[0] + processImage.getWidth();
			var posY = Position.cumulativeOffset(processImage)[1];

			var firstItem = $("menuDiv_" + id).firstChild;

			var deltaX = (posX - parseFloat(firstItem.style.left));
			var deltaY = (posY - parseFloat(firstItem.style.top));

			if(deltaX != 0){

				var items = $("menuDiv_" + id).childNodes;

				for(j = 0; j < items.length; j++){
					if(items[j].style != null) {
						items[j].style.left = parseFloat(items[j].style.left) + deltaX + 'px';
						items[j].style.top  = parseFloat(items[j].style.top)  + deltaY + 'px';
					}
				}
			}

			$("menuDiv_" + i).show();

		} else
			$("menuDiv_" + i).hide();
}

function draw_process_graph(pid, proc_name)
{
	border_id = create_border("Gráfico - " + proc_name);
	var div = $("content_id_" + border_id);
	div.style.height = '420px';
	var imgPreLoad = new Image();
	imgPreLoad.src = "../index.php?menuaction=workflow.ui_adminactivities.show_graph&p_id=" + pid;
	function displayPanorama()
	{
		var viewer = new experience.panorama.Viewer({
				ImageURL: imgPreLoad.src,
				ImageWidth: imgPreLoad.width,
				ImageHeight: imgPreLoad.height,
				IconDirectory: 'js/experience/icons',
				RenderIn: "content_id_" + border_id
			});
		viewer.show();
	}
	imgPreLoad.onload = displayPanorama;
}

function draw_folder_about(pid) {

	var proc_about_handler = function(data) {
		border_id = create_border("Sobre - " + data['wf_procname']);
		content = document.getElementById("content_id_" + border_id);

		var table_proc = document.createElement("TABLE");
		var tbody_proc = document.createElement("TBODY");
		table_proc.className = "info_table";
		table_proc.width = "90%";
		table_proc.align = "center";
		tr_element = document.createElement("TR");
		tr_element.className = "info_tr_header";
		td_element = document.createElement("TD");
		td_element.innerHTML = "Processo " + data['wf_procname'] + " (v" + data['wf_version'] + ")";
		tr_element.appendChild(td_element);
		tbody_proc.appendChild(tr_element);
		tr_element = document.createElement("TR");
		tr_element.className = "info_tr_simple";
		td_element = document.createElement("TD");
		td_element.innerHTML = data['wf_description'];
		tr_element.appendChild(td_element);
		tbody_proc.appendChild(tr_element);
		table_proc.appendChild(tbody_proc);

		content.innerHTML = "&nbsp;";
		content.appendChild(table_proc);
		content.appendChild(document.createElement("BR"));

		var table_activ = document.createElement("TABLE");
		var tbody_activ = document.createElement("TBODY");
		table_activ.className = "info_table";
		table_activ.width = "90%";
		table_activ.align = "center";
		tr_element = document.createElement("TR");
		tr_element.className = "info_tr_header";
		td_element = document.createElement("TD");
		td_element.colSpan = "2";
		td_element.innerHTML = "Atividades do Processo";
		tr_element.appendChild(td_element);
		tbody_activ.appendChild(tr_element);

		for (ix = 0; ix < data['wf_activities'].length; ix++) {
			tr_element = document.createElement("TR");
			tr_element.className = "info_tr_simple";
			td_element1 = document.createElement("TD");
			td_element1.className = "info_td_activ";
			td_element1.width = "35%";
			td_element1.innerHTML = activity_icon(data['wf_activities'][ix]['wf_type'], data['wf_activities'][ix]['wf_is_interactive']) + " " + data['wf_activities'][ix]['wf_name'];
			td_element2 = document.createElement("TD");
			td_element2.width = "65%";
			td_element2.innerHTML = data['wf_activities'][ix]['wf_description'];
			tr_element.appendChild(td_element1);
			tr_element.appendChild(td_element2);
			tbody_activ.appendChild(tr_element);
		}

		table_activ.appendChild(tbody_activ);
		content.appendChild(table_activ);
		content.appendChild(document.createElement("BR"));
	};
	params = "pid=" + pid;
	cExecute ("$this.bo_userinterface.process_about", proc_about_handler, params);
}
