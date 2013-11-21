var externals = function(data)
{
	if (_checkError(data))
		return;

	var content_id_3 = document.getElementById("content_id_3");
	if (data.length == 0)
	{
		var parag = document.createElement("P");
		parag.className = "text_dsp";
		parag.innerHTML = "Não existem aplicações externas";
		content_id_3.appendChild(parag);
	}
	else
	{
		elem = document.getElementById("table_ext");
		if (elem) {
			elem.parentNode.removeChild(elem);
		}

		draw_externals_grid(data, 3);
	}
};

function draw_externals_folder()
{
	cExecute ("$this.bo_userinterface.externals", externals, "");
}

function draw_externals_grid(data, page)
{

	var content_id_3 = document.getElementById("content_id_3");

	// altura mínima do conteiner
	if(is_ie){
		content_id_3.style.height = "260px";
	} else {
		content_id_3.style.minHeight = "260px";
	}

	(function loop(i) {

		if(i < data.length){

			var external_link = data[i].wf_ext_link;
			var ext = data[i];
			var ext_name_dsp = ext.name;

			if (ext_name_dsp.length > 40) {
			    ext_name_dsp = ext_name_dsp.substr(0,40) + "...";
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

			div_element.style.padding    = '7px';
			div_element.style.paddingTop = "25px";

			div_element.style.cursor = 'pointer';
			div_element.onclick = function() { var external_window = window.open(external_link,'extwindow'); external_window.opener = null;};


			var div_ext_img = document.createElement("DIV");
			div_ext_img.style.width = "100%";
			div_ext_img.style.textAlign = 'center';
			div_ext_img.innerHTML = "<img src ='" + ext.image + "' width='32' height='32'>";

			var div_ext_txt = document.createElement("DIV");
			div_ext_txt.style.width = "100%";
			div_ext_txt.style.textAlign = 'center';
			div_ext_txt.style.paddingTop = '5px';
			div_ext_txt.innerHTML = '<span style="font-size: 11px !important;">'+ext_name_dsp+'</span>';

			div_element.appendChild(div_ext_img);
			div_element.appendChild(div_ext_txt);

			content_id_3.appendChild(div_element);

			loop(i+1);
		}
	})(0)

	var div_bottom = document.createElement("DIV");
	div_bottom.style.width = "100%";
	div_bottom.style.clear = 'both';

	content_id_3.appendChild(div_bottom);

}
