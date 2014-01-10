function DragArea(){

	this.msg_number = false;
	this.subject	= false;
	this.folder		= '';
	this.color_dd	= 'red';
	this.from_list	= false;
	this._mousemove = document.onmousemove;
	this._mouseup = document.onmouseup;
	this.div_dd = document.createElement("DIV");
	this.id_messages_to_drag = new Array();
	this.div_dd.style.position = 'absolute';
	this.div_dd.style.zIndex = '15';
	this.div_dd.style.border = '1px solid black';
	this.div_dd.style.background ="#EEEEEE";
	var value = "8.5";
	this.div_dd.style.opacity = value/10;
	this.div_dd.style.filter = 'alpha(opacity=' + value*10 + ')';	
	this.div_dd.id = 'div_sel_messages';
	this.div_dd.style.padding = "3px";
	this.div_dd.style.width = "0px";
	this.div_dd.style.height = "0px";
	this.div_dd.style.display ='none';
	this.envelope = new Image();
	this.envelope.src = "templates/"+template+"/images/envelope.png";
	document.body.appendChild(this.div_dd);
	
}
/*
DragArea.prototype.makeMenuBox = function(element){

	element.oncontextmenu = function(e)
	{
		return false;
	}
	
	var _this = this;
	
	element.onmousedown = function (e)
	{
		var _button = is_ie ? window.event.button : e.which;

		if(_button == 2 || _button == 3)
		{
			var boxFolder = element.id.substr(1,element.id.indexOf('tree_folders')-1);
			var boxName = element.firstChild.nextSibling.innerHTML;
			ConstructBoxMenu(is_ie ? window.event : e,boxName,boxFolder);
		}
				
		return true;
	};	
	
}
*/

DragArea.prototype.makeDragged = function(element, msg_number, subject, from_list, folder){
	element.oncontextmenu = function(e) {
		return false;
	}
	var _this = this;
	element.onmousedown = function (e){

		var _button = is_ie ? window.event.button : e.which;
	
		if(_button == 2 || _button == 3) {
			var _checkb = Element("check_box_message_"+ msg_number);

			var _checkb = Element(getTabPrefix() + "check_box_message_" + msg_number);

			if(_checkb) {
				if(!_checkb.checked) {
					_checkb.checked = true;
					changeBgColor(is_ie ? window.event : e, msg_number);
				}
				//ConstructRightMenu(is_ie ? window.event : e);
			}			
			return false;
		}

		if(!_this.msg_number) {
			_this.msg_number = msg_number;

			if(!subject)
				_this.subject = get_lang("No Subject");
			//else if(subject.length > 40)
			//	_this.subject = subject.substring(0,40) + '...';				
			else
				_this.subject	 = subject;

			_this.from_list  = from_list;
			_this.div_dd.style.width = "auto";
			_this.div_dd.style.height = "auto";			
			_this.folder = folder;
		}
		return true;
	};	
}
DragArea.prototype.showLayerDrag = function(e){

	var msg_number = _dragArea.from_list ? _dragArea.msg_number : _dragArea.msg_number.replace('_r','');
	var _checkbox_element = Element(getTabPrefix()+"check_box_message_"+msg_number);
	if(_dragArea.from_list) {

		if((_checkbox_element) && (! _checkbox_element.checked)) {
			if (is_ie)
				changeBgColor(window.event,msg_number);
			else
				changeBgColor(e,msg_number);
			_checkbox_element.checked = true;
		}
		id_messages_to_drag = get_selected_messages();
		id_messages_to_drag = id_messages_to_drag ? id_messages_to_drag.split(',') : new Array();
	
		if(id_messages_to_drag.length > 1)
			this.subject = id_messages_to_drag.length +" mensagens selecionadas";
	}

	this.div_dd.innerHTML = "<img align='center' src='"+this.envelope.src+"'>&nbsp;<span id='content_dd'><font color='red' weight='bold'><b>"+this.subject+"</b></span></font>";
	this.div_dd.style.display ='';
}

DragArea.prototype.onSelectStart = function(value){
	if(!value) {
		document.body.onselectstart = function (e){return false;}
		document.body.ondragstart = function (e){return false;}
	}
	else {
		document.body.onselectstart = function (e){return true;}
		document.body.ondragstart = function (e){return true;}
	}
}

DragArea.prototype.mouseMoveDrag = function(e){		
	
	var	e  = is_ie ? window.event : e;
	var	_target = is_ie ? e.srcElement : e.target;
	if(is_ie)
		this.onSelectStart(false);

	this.div_dd.style.left	= e.clientX + 5 + document.body.scrollLeft;
	this.div_dd.style.top 	= e.clientY + 10 + document.body.scrollTop;		

	if(this.div_dd.style.display == 'none')
		this.showLayerDrag(e);
	
	var reg = /^((n|l)(?!root))(.*)tree_folders$/;
	var _color = this.color_dd;

	if(reg.test(_target.parentNode.id) )
		_color ='green';
	else
		_color ='red';
	
	if(this.color_dd != _color) {
		Element('content_dd').innerHTML = "<font color='"+_color+"' weight='bold'><b>"+this.subject+"</b></font>";
		this.color_dd = _color;
	}

	return false;
}	

var _dragArea = new DragArea();

document.onmousemove = function(e) {
	var	_target = is_ie ? window.event.srcElement : e.target;
	if(_dragArea._mousemove)
		_dragArea._mousemove(e);
	
	else if(_dragArea.msg_number && _target.type != 'checkbox') {
		_dragArea.mouseMoveDrag(e);
	}
	
	if (is_ie) 
		window.event.returnValue = true; 
	else{
		if(Element("border_id_0") && Element("border_id_0").className === 'menu-sel'){

			$("#content_id_0, #folderscol, #border_tbody, .whiteSpace, #footer_menu").css({
						   '-webkit-user-select':'none',
						   'user-select':'none'
			});	

		} else {
			e.returnValue = true;
		}
            }
};

document.onmouseup = function(e) {

	var	_event  = is_ie ? window.event : e;
	var	_target = is_ie ? _event.srcElement : _event.target;
	var _button = is_ie ? _event.button : _event.which;
	var _tab_prefix = getTabPrefix();
	var _msg_id;

	if(_button != 2 && _button != 3) {		

		if(Element("div_rightbutton") && Element("div_rightbutton").style.display != 'none')
		{
						
			if(!_target.id.match(/link_rightbutton_(.*)$/)){
			
				var id_messages_to_drag = get_selected_messages();
				id_messages_to_drag = id_messages_to_drag ? id_messages_to_drag.split(',') : id_messages_to_drag;
		
				for(var i = 0; id_messages_to_drag && i < id_messages_to_drag.length; i++) {			
					_msg_id = getMessageIdFromRowId(id_messages_to_drag[i]);
					Element(_tab_prefix+"check_box_message_"+_msg_id).checked = false;
					changeBgColor(_event ,_msg_id);
				}
				//Element("chk_box_select_all_messages").checked = false;
				Element('div_rightbutton').style.display = 'none';
				_dragArea.msg_number = false;
				_dragArea.div_dd.style.display ='none';
				return false;
			}
		}
		if(is_ie)
			_dragArea.onSelectStart(true);
	
		if (Element("div_rightbutton_folder") && Element("div_rightbutton_folder").style.display != 'none')
		{	
		Element('div_rightbutton_folder').style.display = 'none';
		}
	
	}	





	if(_dragArea.msg_number) {

		var reg = /^((n|l)(?!root))(.*)tree_folders$/;
		var new_folder;
		
		if(reg.test(_target.parentNode.id) ){
			new_folder = _target.parentNode.id.substring(1,_target.parentNode.id.length).replace('tree_folders','');			
			new_folder_name = new_folder.replace("INBOX"+cyrus_delimiter, "");
			if(new_folder_name == 'INBOX')
				new_folder_name = get_lang("Inbox");
			if ( _dragArea.from_list )
			{
				if (numBox != 0)
					move_search_msgs("content_id_search_" + numBox, new_folder, new_folder_name);
				else	
					proxy_mensagens.proxy_move_messages("null", 'selected', 0, new_folder, new_folder_name);
			}
			else
			{
				var msg_number = _dragArea.from_list ? _dragArea.msg_number : _dragArea.msg_number.replace('_r','');
				proxy_mensagens.proxy_move_messages("null", msg_number, msg_number + "_r", new_folder, new_folder_name);
			}
		}
		
		else if(_dragArea.from_list && _target.type != 'checkbox' && _dragArea.div_dd.style.display !='none'){
			var id_messages_to_drag = get_selected_messages();
			if(id_messages_to_drag){
				id_messages_to_drag = id_messages_to_drag.split(',');
			
				for(var i = 0; id_messages_to_drag && i < id_messages_to_drag.length; i++) {				
					_msg_id = getMessageIdFromRowId(id_messages_to_drag[i]);
					Element(_tab_prefix+"check_box_message_"+_msg_id).checked = true;
					changeBgColor(_event ,_msg_id);
				}
				//Element("chk_box_select_all_messages").checked = false;			
			}
		}
	
		_dragArea.msg_number = false;
		_dragArea.div_dd.style.display ='none';
		if(is_ie)
			_dragArea.onSelectStart(true);
	}

};
