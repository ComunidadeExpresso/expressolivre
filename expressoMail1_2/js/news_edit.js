function loadXMLDoc(fname)
{
	var xmlDoc;
	// code for IE
	if (window.ActiveXObject)
	{
		xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	}
	// code for Mozilla, Firefox, Opera, etc.
	else if (document.implementation 
			&& document.implementation.createDocument)
	{
		xmlDoc=document.implementation.createDocument("","",null);
	}
	else
	{
		alert('Your browser cannot handle this script');
	}
	xmlDoc.async=false;
	xmlDoc.load(fname);
	return(xmlDoc);
}


function cnews_edit()
{
	this.arrayWin = new Array();
	this.rssXml;
}

cnews_edit.prototype.read_rss = function(val)
{
	Element("border_id_0").innerHTML = get_lang('News')+'&nbsp;&nbsp;<font face="Verdana" size="1" color="#505050">[<span id="new_m">&nbsp;</span> / <span id="tot_m"></span>]</font>';

	Element("tot_m").innerHTML = 0;
	Element('content_id_0').innerHTML = '';
	current_folder = "NEWS/"+val;
	openTab.imapBox[0] = "NEWS/"+val;
	this.rssXml = loadXMLDoc('controller.php?action=$this.rss.getContent&url='+escape(val));
	var xsl = loadXMLDoc('stylesheet/news_read.xsl');

	document.getElementById("content_id_0").innerHTML= '<tr class="message_header">'+
		'<td width="1%"><input type="checkbox" id="chk_box_select_all_messages" class="checkbox"/></td>'+
		'<td width="2%"/><td>   </td><td>   </td><td>  </td>'+
		'<td width="16%" align="left">'+get_lang('Who')+'</td>'+
		'<td width="50%" align="left">'+get_lang('subject')+'</td>'+
		'<td width="17%" align="center">'+get_lang('date')+'</td>'+
		'<td width="14%" align="center">'+get_lang('size')+'</td></tr>';
	// code for IE
	if (window.ActiveXObject)
	{
		ex=this.rssXml.transformNode(xsl);
		document.getElementById("content_id_0").innerHTML=ex;
	}
	// code for Mozilla, Firefox, Opera, etc.
	else if (document.implementation && document.implementation.createDocument)
	{
		xsltProcessor=new XSLTProcessor();
		xsltProcessor.importStylesheet(xsl);
		resultDocument = xsltProcessor.transformToFragment(this.rssXml,document);
		document.getElementById("content_id_0").appendChild(resultDocument);
		resizeWindow();
	}

	update_menu();
	var box = Element("tbody_box");
        if(box.childNodes.length > 1)
		updateBoxBgColor(box.childNodes);
}

cnews_edit.prototype.read_item = function(item_number){
	try {
		var description = this.rssXml.getElementsByTagName('item')[item_number-1].getElementsByTagName('description')[0].firstChild.nodeValue;
	}catch(e){
		return;
	}
	var title = this.rssXml.getElementsByTagName('item')[item_number-1].getElementsByTagName('title')[0].firstChild.nodeValue;
	var pubDate = this.rssXml.getElementsByTagName('item')[item_number-1].getElementsByTagName('pubDate')[0].firstChild.nodeValue;
	var link = this.rssXml.getElementsByTagName('item')[item_number-1].getElementsByTagName('link')[0].firstChild.nodeValue;
	var owner = this.rssXml.getElementsByTagName('item')[item_number-1].getElementsByTagName('owner')[0];
	if (currentTab.toString().indexOf('news_') != -1)
		delete_border(currentTab);
	var border_id = create_border(title, 'news_'+item_number);
	if(!border_id)
		return false;

	  openTab.type[border_id] = 2;

	var toolbarCode = "<div style='background-color: #FFF; font-size: larger;'>"+
	"<table class='table_message' style='width:100%;'><tr><td>"+(owner != undefined?owner.firstChild.nodeValue:get_lang('nobody'))+", "+pubDate+"</td>"+
	'<td width="30%" align="left">Marcar como: <span class="message_options">'+get_lang('unseen')+'</span></td>'+
	'<td nowrap="true" width="30%" align="right"><a target="_blank" href="'+link+'" style class="message_options">'+get_lang('Complete news')+'</a>';
	if (owner != undefined)
		toolbarCode += '<span>&nbsp;|&nbsp;</span><span class="message_options">'+get_lang('forward')+'</span><span>&nbsp;|&nbsp;</span>'+
				'<span class="message_options">Responder</span></td>';
	toolbarCode += '<td nowrap="true" width="40px" align="right">'+
	( item_number == 1 ?  '<img style="cursor: default;" src="./templates/default/images/up.gray.button.png">' :
	'<img onclick="news_edit.read_item('+(parseInt(item_number)-1)+')" style="cursor: default;" src="./templates/default/images/up.button.png">')+
	'<span>&nbsp;</span>'+
	(this.rssXml.getElementsByTagName('item')[parseInt(item_number)] == undefined ? '<img style="cursor: default;" src="./templates/default/images/down.gray.button.png">' :
	'<img onclick="news_edit.read_item('+(parseInt(item_number)+1)+')" style="cursor: pointer;" src="./templates/default/images/down.button.png">')+
	"</td></tr></table><h2>"+title+"</h2><br>"+description+"</div>";
	Element('content_id_'+border_id).innerHTML = toolbarCode;
	resizeWindow();
}


cnews_edit.prototype.makeWindow = function(options)
{
	_this = this;

	var el = document.createElement("DIV");
	el.style.visibility = "hidden";
	el.style.position = "absolute";
	el.style.left = "0px";
	el.style.top = "0px";
	el.style.width = "0px";
	el.style.height = "0px";
	el.id = 'dJSWin_newswin';
	document.body.appendChild(el);
	el.innerHTML = "<table border=0><tbody><tr>"+
		'<td valign="bottom"><input type="text" id="rssEnter" size="40"><input value="'+get_lang('subscribe')+
		'" onclick="news_edit.subscribe();" type="button">'+
		'<br>Enter de url of RSS service<br>'+
		'</td></tr>'+
		'<tr><td id="serv_table"></td></tr>'+
		"</tbody></table><br>";


	var butt = Element('dJSWin_wfolders_bok')
		if (!butt){
			butt = document.createElement('INPUT');
			butt.id = 'dJSWin_wfolders_bok';
			butt.type = 'button';
			butt.value = get_lang('Close');
			el.appendChild(butt);
		}
	butt.onclick = function ()
	{
		news_edit.arrayWin[el.id].close();
	}


		_this.showWindow(el);
	}

	cnews_edit.prototype.showWindow = function (div)
	{
		if(! div) {
			alert(get_lang('This list has no participants'));
			return;
		}

		if(! this.arrayWin[div.id])
		{
			div.style.height = "280px";
			div.style.width = "340px";
			var title = ":: "+get_lang("News edit")+" ::";
			var wHeight = div.offsetHeight + "px";
			var wWidth =  div.offsetWidth   + "px";
			div.style.width = div.offsetWidth - 5;

			win = new dJSWin({
				id: 'win_'+div.id,
				content_id: div.id,
				width: wWidth,
				height: wHeight,
				title_color: '#3978d6',
				bg_color: '#eee',
				title: title,
				title_text_color: 'white',
				button_x_img: '../phpgwapi/images/winclose.gif',
				border: true });
			this.arrayWin[div.id] = win;
			win.draw();
		}
		else {
			win = this.arrayWin[div.id];
		}
		win.open();
		var handlerChannel = function(data){
			document.getElementById("serv_table").innerHTML = '';
			for(i=0; i < data.length; i++)
				document.getElementById("serv_table").innerHTML += "<div><span>"+data[i].name+
				"</span><span></span><img onclick='news_edit.unsubscribe(\""+data[i].rss_url+"\",this)' src='../phpgwapi/templates/default/images/foldertree_trash.png'/></div>";
		}
		cExecute('$this.rss.getChannels',handlerChannel);
	}
	cnews_edit.prototype.unsubscribe = function(url,el){
		var rem_handler = function (data){
			if (data != "Success")
				if (data == "Error")
					alert(get_lang("Database Error"));
				else
					alert(get_lang("Invalid entry"));
			else
			{
				var pnode = el.parentNode;
				pnode.parentNode.removeChild(pnode);
			}
		};

		cExecute('$this.rss.removeChannel&url='+escape(url),rem_handler);
	}
	cnews_edit.prototype.subscribe = function(){
		var val = Element('rssEnter').value;
		var rssXml = loadXMLDoc('controller.php?action=$this.rss.getContent&url='+escape(val));
		var xsl = loadXMLDoc('stylesheet/news_add.xsl');
		newTableEl = document.getElementById("serv_table");
		// code for IE
		if (window.ActiveXObject)
		{
			ex=rssXml.transformNode(xsl);
			newTableEl.innerHTML='<div id="'+escape(val)+'">'+ex+'</div>';
		}
		// code for Mozilla, Firefox, Opera, etc.
		else if (document.implementation && document.implementation.createDocument)
		{
			xsltProcessor=new XSLTProcessor();
				xsltProcessor.importStylesheet(xsl);
			resultDocument = xsltProcessor.transformToFragment(rssXml,document);
			var ndiv = document.createElement('DIV');
			ndiv.id = escape(val);
			ndiv.appendChild(resultDocument);
			newTableEl.appendChild(ndiv);
		}
		var add_handler = function (data){
			if (data != "Success")
				if (data == "Error")
					alert(get_lang("Database Error"));
				else
					alert(get_lang("Invalid entry"));
		};
		if(!ndiv)
			var ndiv = document.getElementById(escape(val));
		var name = ndiv.childNodes[1].innerHTML;
		cExecute('$this.rss.addChannel&url='+escape(val)+'&name='+name,add_handler);

	};

/* Build the Object */
var news_edit;
news_edit = new cnews_edit();
