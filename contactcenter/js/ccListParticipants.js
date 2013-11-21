  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	function cListParticipants ()
	{
		this.arrayWin = new Array();
		this.el;
	}

	
cListParticipants.prototype.showList = function(id, contact, email, title, account_type){
	_this = this;
	
	id = id;

	div = document.getElementById(id+':cc_rectParticipants');
	var el = document.createElement("DIV");									
	el.style.visibility = "hidden";									
	el.style.position = "absolute";
	el.style.left = "0px";
	el.style.top = "0px";
	el.style.width = "0px";
	el.style.height = "0px";									
	el.className = "div_cc_rectParticipants";
	el.id = id+':cc_rectParticipants';
	
	if(is_ie) {
		el.style.width= "auto";
		el.style.overflowY = "auto";								
		el.style.overflowX = "hidden";
	}													
		else {									
		el.style.overflow = "-moz-scrollbars-vertical";
	}
		
	if (title) {
		document.body.appendChild(el);
		var names = contact.split(",");
		var email = email.split(",");
		el.innerHTML = "";								
		el.innerHTML = "<br>&nbsp;&nbsp;<b><font color='BLUE'>"+title+"</font></b>"+
		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>&nbsp;&nbsp;&nbsp;&nbsp;<u>"+
		Element('cc_participants').value+"</u>&nbsp;&nbsp;&nbsp;<br><br>";
							
		if(names.length) {
			for (var d = 0; d < (names.length-1); d++) {																																					
				var email_valido = email[d] != 'null' ? email[d] : "não informado"																																				
				el.innerHTML +=	
				"<font color='DARKBLUE'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					"\"" + names[d]+ "\"" +
					" &lt;"+email_valido+
					"&gt;</font>&nbsp;&nbsp;&nbsp;<br>";																	
			}
		}
		else {
			el.innerHTML +=	"<font color='DARKBLUE'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
				"&lt;"+Element('cc_empty').value+"&gt;&nbsp;&nbsp;</font><br>";		
		}
			
		el.innerHTML +=	"<br>";
		
		if(div)
			this.showWindow(div);
		else						
			_this.showWindow(el);
	} else {
			var handler = function (responseText) {
				var contacts = unserialize(responseText);
				var title = contacts.names_ordered;	
				document.body.appendChild(el);				
				el.innerHTML = "";	
				el.innerHTML = "<br>&nbsp;&nbsp;<b><font color='BLUE' nowrap>"+title+"</font></b>"+
				"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>&nbsp;&nbsp;&nbsp;&nbsp;<u>"+
				Element('cc_participants').value+"</u>&nbsp;&nbsp;&nbsp;<br><br>";				
				if(contacts.size > 0) {
						el.innerHTML +=	contacts.inner_html;
				}
				else {
					el.innerHTML +=	"<font color='DARKBLUE'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					"&lt;"+Element('cc_empty').value+"&gt;&nbsp;&nbsp;</font><br>";		
				}
				el.innerHTML +=	"<br>";
								
				_this.showWindow(el);
			}		

			if(div)
				this.showWindow(div);
			else
				Connector.newRequest('get_catalog_participants_'+account_type , '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_catalog_participants_'+account_type, 'POST', handler, 'id='+id);
		}
	}
	
	cListParticipants.prototype.showWindow = function (div)
	{						
		if(! div) {
			alert('Essa lista não possui nenhum participante.');
			return;
		}
							
		if(! this.arrayWin[div.id]) {
			div.style.width = "auto";
			div.style.height = "250px";
			var title = 'Listar Partipantes';		
			var wHeight = div.offsetHeight + "px";
			var wWidth =  div.offsetWidth   + "px";
			if(is_ie) {
				div.style.width = wWidth;
			}			 
			else {
				div.style.width = div.offsetWidth - 5;
			}

			win = new dJSWin({			
				id: 'ccListParticipants_'+div.id,
				content_id: div.id,
				width: wWidth,
				height: wHeight,
				title_color: '#3978d6',
				bg_color: '#eee',
				title: title,						
				title_text_color: 'white',
				button_x_img: Element('cc_phpgw_img_dir').value+'/winclose.gif',
				border: true });
			
			this.arrayWin[div.id] = win;
			win.draw();			
		}
		else {
			win = this.arrayWin[div.id];
		}			
							
		win.open();				
	}
	
/* Build the Object */
	var ccListParticipants ;
	var cListParticipants_pre_load = document.body.onload;

	if (is_ie)
	{ 
		document.body.onload = function (e) 
		{ 
			cListParticipants_pre_load();
			ccListParticipants = new cListParticipants();
			
		};
	}
	else
	{
		ccListParticipants = new cListParticipants();
	}
