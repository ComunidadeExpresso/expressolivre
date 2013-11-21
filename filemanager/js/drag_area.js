/*var agt		= navigator.userAgent.toLowerCase();
var is_ie	= ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));

function DragArea()
{
	this.pressed = 0;
	this.resizing = 0;
	this.dragEl;
	this.endEvent;
	this.operation;
}

document.onmousemove = function(event) {
	getPointer(event);
	if (_dragArea.operation == 'drag')
		_dragArea.dragObj(event);
}
document.onmousedown = function(event) {_dragArea.pressed = 1; };
document.onmouseup = function(event) { _dragArea.pressed = 0 };

counter = 0;
DragArea.prototype.dragObj = function(e){
        var _event  = is_ie ? window.event : e;
	var _target = is_ie ? _event.srcElement : _event.target;

	if (this.pressed)
	{
		var sign = document.getElementById('dragSign');
		if (sign == null)
		{
			check(this.dragEl);
			sign = document.createElement('SPAN');
			sign.className = 'dragSign';
			sign.id = 'dragSign';
			document.body.appendChild(sign);
		}
		sign.innerHTML = get_lang("Drag and drop to move");
		sign.style.left = (_event.clientX + 2) + "px";
		sign.style.top = (_event.clientY + 2) + "px";
		sign.style.position = 'absolute';
	}
	else{
		this.operation = "";
		var sign = document.getElementById('dragSign');
		if (sign == null)
			return;
		sign.parentNode.removeChild(sign);
		if(_target.tagName == 'SPAN'){
			var filesUrl = toolbar.getCheckedFiles();
			var path = _target.id.replace(/main$/g,'').substr(1);
			if (filesUrl.length > 1 && path.length > 1)
				move_to(path,filesUrl);
		}
	}
}

var _dragArea = new DragArea();*/