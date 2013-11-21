// Inicializa palheta de cores;
function cColorPalette(){
	this.editor;
	this.div;
	this.parentDiv;
	this.colors = new Array(	new Array("#FFFFFF","#FFCCCC","#FFCC99","#FFFF99","#FFFFCC","#99FF99","#99FFFF","#CCFFFF","#CCCCFF","#FFCCFF"),
						new Array("#CCCCCC","#FF6666","#FF9966","#FFFF66","#FFFF33","#66FF99","#33FFFF","#66FFFF","#9999FF","#FF99FF"),
						new Array("#C0C0C0","#FF0000","#FF9900","#FFCC66","#FFFF00","#33FF33","#66CCCC","#33CCFF","#6666CC","#CC66CC"),
						new Array("#999999","#CC0000","#ff6600","#FFCC33","#FFCC00","#33CC00","#00CCCC","#3366FF","#6633FF","#CC33CC"),
						new Array("#666666","#990000","#CC6600","#CC9933","#999900","#009900","#339999","#3333FF","#6600CC","#993399"),
						new Array("#333333","#660000","#993300","#996633","#666600","#006600","#336666","#000099","#333399","#663366"),
						new Array("#000000","#330000","#663300","#663333","#333300","#003300","#003333","#000066","#330099","#330033"));
	this.buildPalette();
}

// funçoes
cColorPalette.prototype.changeFontColor = function (color){	
	var mainField = this.editor.contentWindow;
	mainField.document.execCommand("forecolor", false, color);	
	document.getElementById("palettecolor").style.visibility="hidden";
	mainField.focus();
}

cColorPalette.prototype.repos = function (intElemScrollTop)
{
    if ( Element("forecolor") )
    {
        var new_pos = findPosY(Element("forecolor")) - intElemScrollTop + 20;
        this.div.style.top = new_pos;
    }
}

cColorPalette.prototype.loadPalette = function (id)
{
	this.parentDiv = document.getElementById("body_position_"+id);
	this.editor = document.getElementById("body_"+id);
	if(this.div.parentNode)
		this.div.parentNode.removeChild(this.div);	

	this.parentDiv.appendChild(this.div);	
	this.div.style.position = "absolute";

	if(is_ie)
	{
		this.div.style.top = findPosY(Element("forecolor")) - 100;
		this.div.style.left = findPosX(Element("forecolor"))- 200;
	}
	else
	{
		this.div.style.top = ColorPalette.repos(Element("div_message_scroll_"+id).scrollTop);
		this.div.style.left = findPosY(Element("forecolor"))+ 227;
	}
}

cColorPalette.prototype.buildPalette = function (){
	
  	this.div = document.createElement("DIV");
	this.div.style.visibility="hidden";
  	this.div.id= "palettecolor";
  	this.div.style.top = "0px";
  	this.div.style.left = "0px";
  	this.div.style.width	= "auto";
  	this.div.style.height= "auto";
  	var t1 = document.createElement("TABLE");
 	var tb1 = document.createElement("TBODY");
 	t1.appendChild(tb1);
  	t1.border ="1px";
	t1.cellPadding ="0px";
	t1.cellSpacing ="0px";
 	t1.style.width = "100px";
 	t1.bgcolor = "WHITE";
  	this.div.appendChild(t1);
  	_this = this;
  	for( i = 0; i < 10; i++) {
  		var _tr = document.createElement("TR");
		tb1.appendChild(_tr);		
  		for( j = 0; j < 7; j++) {
			var _td = document.createElement("TD");
  			_td.style.background =  this.colors[j][i];
	  		_td.unselectable = "on";
  			_td.style.width = "15px";
  			_td.style.height = "15px";
  			_td.title =   this.colors[j][i];
  			_td.id =   this.colors[j][i];
  			_td.className = "unsel_color";
	  		_td.onclick= function(){ document.getElementById("palettecolor").style.visibility="hidden";ColorPalette.changeFontColor(this.id);};
	  		_td.onmouseover = function(){ this.className = "sel_color";};
	  		_td.onmouseout = function(){ this.className = "unsel_color";}
			var p = new Image();
  			p.style.width='1px';
  			p.style.height='1px';
			_td.appendChild(p);
	  		_tr.appendChild(_td);
  		}
	}
}

/* Build the Object */
var ColorPalette = new cColorPalette();
