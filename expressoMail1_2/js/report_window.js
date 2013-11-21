/**************************************************************************\
 Relatório de problema no email pelo usuário. 
\**************************************************************************/

	function report_window()
	{
		this.arrayW = new Array();
	}

	/* Propriedades da tela */
	report_window.prototype.make_report_window = function(ID_msg){
		_this = this;
		var title = get_lang("More information about the problem (optional)");
		
		tree 				  = document.createElement("DIV");
		tree.style.visibility = "hidden";
		tree.style.position   = "absolute";
		tree.style.left 	  = "0px";
		tree.style.top 		  = "0px";
		tree.style.width 	  = "0px";
		tree.style.height 	  = "0px";									
		tree.id				  = "window"; 
			document.body.appendChild(tree);

		var msg_title = document.createElement("DIV");
			msg_title.id = "div_title";
			msg_title.style.position = "absolute";		
			msg_title.style.left = "5px";
			msg_title.style.top = "-8px"		
			msg_title.style.width = "240px";
			msg_title.style.height = "350px";
			msg_title.innerHTML = "<br><b><font color='BLACK' nowrap>"+title+"</font></b>";
				tree.appendChild(msg_title);
		
		var text_area_div = document.createElement("DIV");
			text_area_div.id = "div_text_area";
		var	text_area = document.createElement("TEXTAREA");
			text_area.id = "text_area";
			text_area.style.position = "absolute";		
			text_area.style.left = "5px";
			text_area.style.top = "45px"		
			text_area.style.width = "240px";
			text_area.style.height = "130px";
				text_area_div.appendChild(text_area);
				tree.appendChild(text_area_div);
		
		var msg_confirmation = document.createElement("DIV");
			msg_confirmation.id = "div_msg_confirmation";
			msg_confirmation.style.position = "absolute";		
			msg_confirmation.style.left = "5px";
			msg_confirmation.style.top = "170px"		
			msg_confirmation.style.width = "240px";
			msg_confirmation.style.height = "350px";
			msg_confirm = get_lang("Attention! The information contained in the e-mail will be sent to the support team");
			msg_confirmation.innerHTML = "<br><b><font color='BLACK' nowrap>"+msg_confirm+"</font></b>";
				tree.appendChild(msg_confirmation);
		
		
		var div_buttons = document.createElement("DIV");
			div_buttons.id = "div_buttons_report";
			div_buttons.style.position = "absolute";		
			div_buttons.style.left = "50px";
			div_buttons.style.top = "160px"		
			div_buttons.style.width = "130px";
			div_buttons.style.height = "190px";
			div_buttons.innerHTML = "<table border='0' cellpading='0' cellspacing='0'>"+
									"<tr><td><br><br><br><br><br></td></tr>"+
									"<tr>" + 
									"<td><input type='button' value='"+get_lang('Report error')+"' onclick='report_error()'></td>" +
									"<td><input type='button' value='"+get_lang('Cancel')+"' onclick='report_wind.close_win()'></td>" +
									"</tr>"+
									"</table>";
			tree.appendChild(div_buttons);		
		/* Mostra a tela*/	
		_this.showWindow(tree);
	}

	
	/* Mostra a tela para o usuário */
	report_window.prototype.showWindow = function (div){
		if(! div) {
			return;
		}
		
		if(! this.arrayW[div.id]) {
			div.style.width  = "250px";
			div.style.height = "260px";
			div.style.zIndex = "10000";			
			var title = get_lang("Email report error");
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
			
			this.arrayW[div.id] = win;
			win.draw();
		}
		else {
			win = this.arrayW[div.id];
		}
		win.open();
	}
	
	/* Fecha a janela do report error */
	report_window.prototype.close_win = function(){
	
		this.arrayW['window'].close();
		return false;
	}

	/* Função que envia a mensagem do usuário para o servidor */	
	function report_error() {		
		msg_user   = document.getElementById('text_area').value;
		msg_folder = get_current_folder();
		cExecute ("$this.imap_functions.report_mail_error&params="+ID_msg+";;"+msg_user+";;"+msg_folder, handler_report_error);		
	}

	/* Retorno da chamada ao servidor */
	function handler_report_error(data) {
		report_wind.close_win();
		document.getElementById('text_area').value = "";
	}
	
	
	/* Build the Object */ 
	var report_wind;
	report_wind = new report_window();
