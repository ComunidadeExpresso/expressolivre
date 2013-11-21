function Element(element){
	return document.getElementById(element);
}

function killElement(param){
	var elem = document.getElementById(param);
	if (elem)
		elem.parentNode.removeChild(elem);
}

function load_lang(){

	var handler_load_lang = function(data){
		array_lang = data;
	};
  	cExecute("$this.bo_userinterface.getLang",handler_load_lang);
}

function write_msg(msg) {
	var msg = '<table width=100% class="msg_erro_table"><tr><th width="40%"></th><th noWrap class="msg_erro_th">'+msg+'</th><th width="40%"></th></tr><tbody></tbody><table>';
	var divAppboxHeader = document.getElementById("divAppboxHeader");
	divAppboxHeader.innerHTML = msg;
	setTimeout("Element('divAppboxHeader').innerHTML = '<table align=center border=0 width=100%><tr><td align=center><span class=msg_erro_text>Workflow</span></td></tr></table>'", 5000);
}

function write_errors(msg_errors) {
	alert(msg_errors.replace(/<br \/>/gi, "\n"));
}

function activity_icon(type, is_interactive) {

	switch(type)
	{
		case 'activity':
			ic = "mini_" + ((is_interactive == 'y') ? 'blue_' : '') + "rectangle.gif";
			break;
		case 'switch':
			ic = "mini_" + ((is_interactive == 'y')? 'blue_':'') + "diamond.gif";
			break;
		case 'start':
			ic = "mini_" + ((is_interactive == 'y')? 'blue_':'') + "circle.gif";
			break;
		case 'end':
			ic = "mini_" + ((is_interactive == 'y')? 'blue_':'') + "dbl_circle.gif";
			break;
		case 'split':
			ic = "mini_" + ((is_interactive == 'y')? 'blue_':'') + "triangle.gif";
			break;
		case 'join':
			ic = "mini_" + ((is_interactive == 'y')? 'blue_':'') + "inv_triangle.gif";
			break;
		case 'standalone':
			ic = "mini_" + ((is_interactive == 'y')? 'blue_':'') + "hexagon.gif";
			break;
		default:
			ic = "no-activity.gif";
	}

	var result = '<img src="' + _icon_dir + 'activities/' + ic + '" alt="' + type + '" title="' + type + '" />';
	return result;
}

function get_icon(img_name, title, attributes)
{
	if (attributes == null) {
		attributes = '';
	}
	return "<img " + attributes  + " src='" + _icon_dir + img_name + "' alt='" + title + "' title='" + title + "'>";
}

function get_link(link,text,attributes)
{
	if (attributes == null) {
		attributes = '';
	}

	return "<a href=" + link + " " + attributes  + ">" + text + "</a>";
}

function _checkError(data)
{
	if (data['error'])
	{
		alert(data['error'].replace(/<br \/>/gi, "\n"));
		if (data['url'])
			window.location = data['url'];
		return true;
	}

	return false;
}

function formatDateField(e, obj)
{
	// assuring it works on IE
	var e = window.event || e;
	var code = e.charCode || e.keyCode;
	
	switch (code) {
		case (8): // backspace
		case (9): // tab
		case (35): // end
		case (36): // home
		case (37): // left arrow
		case (39): // right arrow
		case (46): // delete
			return true;
	}

	if (obj.value.length == 2)
		obj.value += '/';
	else if (obj.value.length == 5)
		obj.value += '/';
	else if (obj.value.length >= 10)
		return false;

	// just numbers!
	return ((code >= 48) && (code <= 57))? true : false;
}
