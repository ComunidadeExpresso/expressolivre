var tabStack = new Array();

function initBorders(num)
{
	for (ix=0; ix < num; ix++)
	{
		BordersArray[ix] = new setBorderAttributes(ix);
	}
}

function setBorderAttributes(ID)
{
	this.border_id = "border_id_"+ID;
	this.sequence = ID; 
}

function alternate_border(ID)
{
	if (!document.getElementById("border_id_"+ID))
		return;
	tabStack.push(ID);
	var len = BordersArray.length;
	for (var i=0; i < len; i++)
	{
		m = document.getElementById(BordersArray[i].border_id);
		m.className = 'menu';
		c = document.getElementById("content_id_"+BordersArray[i].sequence);
		if (is_ie) {
			c.className = "conteudo_div_ie";
		}
		c.style.display = 'none';
	}
	
	m = document.getElementById("border_id_"+ID);
	m.className = 'menu-sel';
	c = document.getElementById("content_id_"+ID);
	c.style.display = '';
		
	return c.childNodes.length;
}

function create_border(borderTitle)
{
	var ID = (BordersArray[(BordersArray.length-1)].sequence + 1);
	
	td = document.createElement("TD");
	td.id="border_id_" + ID;
	td.setAttribute("width", "auto");
	td.className = "menu";
	td.setAttribute("align", "right");
	td.onclick = function(){alternate_border(ID);};
	td.setAttribute("noWrap","true");
	
	borderTitle = borderTitle ?  borderTitle : "Sem assunto";
	td.title = borderTitle;

	if (borderTitle.length > 30){
		borderTitle = borderTitle.substring(0,30) + "...";
	}
	
	td.innerHTML = "&nbsp;&nbsp;" + borderTitle + " <img style='cursor:pointer' onclick=delete_border('" + ID + "') src='" + _icon_dir + "/close_button.gif'>";
		
	bb = document.getElementById("border_blank");
	parent_bb = bb.parentNode; //Pego o tbody
	parent_bb.insertBefore(td, bb);
	
	BordersArray[BordersArray.length] = new setBorderAttributes(ID);
	
	var div = document.createElement("DIV");
	div.id = "content_id_" + ID;
	if (is_ie) {
		div.className = "conteudo_div_ie";
	} else {
		div.className = "conteudo";
	}
	div.style.display='';

	document.getElementById("main_body").appendChild(div);
	alternate_border(ID);
	
	return ID;
}

function delete_border(ID)
{
	for (i=0;i<BordersArray.length;i++)
	{
		m = document.getElementById(BordersArray[i].border_id);
		if (m.className == 'menu-sel')
			border_selected = BordersArray[i].border_id;
	}
	
	if ('border_id_' + ID == border_selected)
	{
		var previousTab;
		var nextTab = -1;
		while (tabStack.length > 0)
		{
			previousTab = tabStack.pop();
			if (previousTab != ID)
				if (document.getElementById('content_id_' + previousTab))
				{
					nextTab = previousTab;
					break;
				}
		}
		if (nextTab == -1)
			nextTab = (BordersArray[i-2].sequence == ID) ? 0 : BordersArray[i-2].sequence;
		this.alternate_border(nextTab);
	}

	// Remove TD, title
	border = document.getElementById('border_id_' + ID);
	border.parentNode.removeChild(border);
	// Remove Div Content
	content = document.getElementById('content_id_' + ID);
	content.parentNode.removeChild(content);
	
	var new_BordersArray = new Array();
	j = 0;
	for (i=0;i<BordersArray.length;i++)
	{
		if (document.getElementById(BordersArray[i].border_id) != null){
			new_BordersArray[j] = BordersArray[i];
			j++;	
		}
	}
	BordersArray = new_BordersArray;	
}
