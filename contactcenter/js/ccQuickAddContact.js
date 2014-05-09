	function cQuickAddContact ()
	{
		this.arrayWin = new Array();
		this.el;
		this._nFields = Element('ccQAnFields').value;
	}

	
	cQuickAddContact.prototype.showList = function(id){
		_this = this;
		var handler = function (responseText) {
			var contacts = unserialize(responseText);
			var title = contacts['names_ordered'];			
			el = document.createElement("DIV");									
			el.style.visibility = "hidden";									
			el.style.position = "absolute";
			el.style.left = "0px";
			el.style.top = "0px";
			el.style.width = "0px";
			wHeight = Element('ccQAWinHeight').value;
			el.style.height = wHeight + 'px';
			el.className = "div_cc_rectQuickAddContact";
			el.id = id+':cc_rectQuickAddContact';																							
			document.body.appendChild(el);																
			el.innerHTML = "";								

			var fieldsTop = 10;
			var fieldsSpace = 30;
			fields = new Array(Element('cc_qa_alias').value, Element('cc_qa_given_names').value, Element('cc_qa_family_names').value, Element('cc_qa_phone').value, Element('cc_qa_email').value);
			
			for (i=0; i<fields.length; i++) {
				var contact = contacts[i] != null ? contacts[i] : '';
				el.innerHTML += '<span id="ccQuickAddCT' + i + id + '" style="position: absolute; top: ' +  (fieldsTop+i*fieldsSpace) + 'px; left: 5px; width: 100px; text-align: right; border: 0px solid #999;">' + fields[i] + '</span>';
				if (i == 0)
				{
					el.innerHTML += '<input id="ccQuickAddCI' + i + id + '" type="text" value="' + contact + '" maxlength="30" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 110px; width: 135px;">';
				}
				else if (i == 4)
				{
					el.innerHTML += '<input id="ccQuickAddCI' + i + id + '" type="text" value="' + contact + '" maxlength="100" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 110px; width: 135px;">';
				}
				else
				{
					el.innerHTML += '<input id="ccQuickAddCI' + i + id + '" type="text" value="' + contact + '" maxlength="50" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 110px; width: 135px;">';
				}				
			}


			el.innerHTML +='<div id="ccQAFuncitons" style="border: 0px solid black; width: 220px; height: 20px">' +
				'<input title="' + Element('cc_qa_save').value + '"  type="button" onclick="ccQuickAddContact.send(\'' + id + '\');" value="' + Element('cc_qa_save').value + '" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 75px; width: 60px" />' +
				'<input title="' + Element('cc_qa_close').value + '" type="button" onclick="ccQuickAddContact.fechar(\'' + id + '\');" value="' + Element('cc_qa_close').value + '" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 140px; width: 60px" />' +
				'</div>';
			el.innerHTML +=	"<br />";
								
			_this.showWindow(el);
		}
		
		div = document.getElementById(id+':cc_rectQuickAddContact');
				
		if(div)
			this.showWindow(div);
		else {
			Connector.newRequest('get_catalog_add_contact', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_catalog_add_contact', 'POST', handler, 'id='+id);
		}
	}
	
	cQuickAddContact.prototype.showWindow = function (div)
	{						
		if(! this.arrayWin[div.id]) {

			win = new dJSWin({			
				id: 'ccQuickAddContact_'+div.id,
				content_id: div.id,
				width: '255px',
				height: wHeight+'px',
				title_color: '#3978d6',
				bg_color: '#eee',
				title: Element('ccQATitle').value,						
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
	
	cQuickAddContact.prototype.send = function (id)
	{
		var _this = this;
		div = document.getElementById(id+':cc_rectQuickAddContact');
		win = this.arrayWin[div.id];

		var handler = function (responseText)
		{
			Element('cc_debug').innerHTML = responseText;

			var data = unserialize(responseText);

			if (!data || typeof(data) != 'object')
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}
			else if (data['status'] == 'alreadyExists')
			{
				showMessage(data['msg']);
				return;
			}
			else if (data['status'] != 'ok')
			{
				return;
			}
			
			win.close();

			if (_this.afterSave)
			{
				switch (typeof(_this.afterSave))
				{
					case 'function':
						_this.afterSave();
						break;

					case 'string':
						eval(_this.afterSave);
						break;
				}
			}
		}
	
		var sdata = new Array();
		
		for (var f = 0; f < 5; f++){
			sdata[f] = document.getElementById('ccQuickAddCI' + f + id).value;
		}
		
		if(!$.trim(sdata[1])){ 
 	        alert(get_lang("The name field is required!"));
 	        return false; 
 	    }
		
		//Utiliza expressão regular para validar email
		/*  
 	      Expressão regular modificada para aceitar emails de email fora do padrão nome@provedor.com.br.  
 	      Aceita casos de domínios internos como c0000@mail.empresa 
 	    */ 
 	    var reEmail = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)+$/;

		if(sdata[4] !== "" && !reEmail.test(sdata[4])){
			alert(get_lang("The email address '") + sdata[4] + get_lang("' não é válido! \n Por favor informe um endereço válido."));
			return false;
		}
				
		var sdata = 'add='+escape(serialize(sdata));

		Connector.newRequest('cQuickAdd.Send', CC_url+'quick_add', 'POST', handler, sdata);
	}
	
	cQuickAddContact.prototype.fechar = function(id) {
	
		div = document.getElementById(id+':cc_rectQuickAddContact');
		win = this.arrayWin[div.id];
		win.close();
	}
	
	
/* Build the Object */
	var ccQuickAddContact ;
	var cQuickAddContact_pre_load = document.body.onload;

	if (is_ie)
	{ 
		document.body.onload = function (e) 
		{ 
			cQuickAddContact_pre_load();
			ccQuickAddContact = new cQuickAddContact();
			
		};
	}
	else
	{
		ccQuickAddContact = new cQuickAddContact();
	}
