
	function charCounter(form)
	{
		if (form.value.length >= 300)
		{
			alert(get_lang("You have exceeded the number of allowed characters"));
			return false;
		}
	}

	function cfilter(){

		this.criticalError = false;
		this.mode_in   = "";
		this.rulest		 = new Array;
		this.rulesVoip	 = new Array;
		this.out_officeR = "";
		this.out_officeF = false;
		this.email_deny  = new Array;
		this.ac_form     = "";
		this.ind		 = "";
		this.email_deny  = new Array;
		this.email_ld	 = "";
		this.values = new Array("",
					",checkBox1",
                                        ",select_size=1",
                                        ",checkBox1,select_size=1",
                                        ",select_rules=1",
                                        ",checkBox1,select_rules=1",
                                        ",select_size=1,select_rules=1",
                                        ",checkBox1,select_size=1,select_rules=1",
                                        ",checkBox2",
                                        ",checkBox1,checkBox2",
                                        ",checkBox2,select_size=1",
                                        ",ckeckBox1,checkBox2,select_size=1",
                                        ",checkBox2,select_rules=1",
                                        ",checkBox1,checkBox2,select_rules=1",
                                        ",checkBox2,select_size=1,select_rules=1",
                                        ",checkBox1,checkBox2,select_size=1,select_rules=1");
		
	}

	cfilter.prototype.load_rules = function(posHandler, param)
	{
		var _this = this;
		var cont1 = parseInt(0);
		var cont2 = parseInt(0);

		if( _this.rulest.length == 0 )
		{
			var handler_sieve = function(data)
			{
				if (data.toString().indexOf('Error:') == 0) 
				{
					_this.criticalError = true;
					alert(get_lang('The filters service is out of service, try again later...'));
				}
				else
				try{
				if(data.rule.length > 0){
					for(var i=0 ; i < data.rule.length; i++)
					{
						var fields = data.rule[i].split("&&");
						if( fields[6] == 'notify' && fields[7] == preferences.voip_email_redirect )
							_this.rulesVoip[cont1++] = data.rule[i];
						else
							_this.rulest[cont2++] = data.rule[i];
					}
				}
				_this.out_officeR = data.vacation[0];
				_this.out_officeR = _this.out_officeR ? trim(_this.out_officeR.toString().replace("\n","")) : "";
				if(data.mode.length > 0){_this.mode_in = data.mode[0];}
				}
				catch(e){
					_this.criticalError = true;
					alert(get_lang('The filters service is out of service, try again later...'));
				}
				if (typeof(posHandler) == 'function')  
					posHandler(param);  
			}
			if(Element('form_status') != null)
				Element('form_status').innerHTML = "<span style='background:#cc4444;'>&nbsp;&nbsp;<font color='WHITE'>Aguarde...</font>&nbsp;</span>";
			cExecute("$this.ScriptS.init_a",handler_sieve);
			_this.get_email();
	    }
	}
	
	cfilter.prototype.form_m = function(){
		Element('form_body').innerHTML = "";
		Element('form_buttons').style.display = '';
		Element('Edit_form_buttons').style.display = 'none';
		filters.mount_list();
		this.ac_form = "";		
	}
	
	cfilter.prototype.form_out = function(){
		Element('form_body').innerHTML = "";
		Element('form_body').innerHTML = this.forms_();
		Element('div_vacation').style.display = "";
                Element('Edit_form_buttons').style.display = 'block';
		this.ac_form = "old_out";		
		this.r_rules_out();		
	}
	
	cfilter.prototype.form_r = function(pos,type)
	{
		Element('form_body').innerHTML = "";
		Element('form_body').innerHTML = this.forms_();
		Element('Edit_form_buttons').style.display = '';
		Element('form_buttons').style.display = 'none';
		this.ind = pos;		
		
		if( type === 'voip')
		{
			this.ac_form = "old_voip";
			Element('div_voipFilter').style.display = "";
			this.r_rules_form(pos, type);	
		}
		else
		{
			this.ac_form = "old_rule";
			Element('div_rule').style.display = "";	
			this.r_rules_form(pos, type);
		}
	}
	
	cfilter.prototype.get_email = function()
	{
		var _this = this;
		var handler_get_email = function(data)
		{
			_this.email_ld = data ? data : "";
		}
		cExecute("$this.user.get_email",handler_get_email);
	}
	
	cfilter.prototype.sel_boxes = function()
	{
		var nm_folders = tree_folders.getNodesList(cyrus_delimiter);
		if(document.getElementById("select_mailboxes") != null){
			var sel_nm = document.getElementById("select_mailboxes");
			if(sel_nm.length > 0 ){
				for(var i=0; i < sel_nm.options.length; i++){
					sel_nm.options[i] = null;
					i--;
				}
			}
			for(var i=0; i < nm_folders.length; i++){
				if(nm_folders[i].id != "root" && !proxy_mensagens.is_local_folder(nm_folders[i].id)){
					var opt = new Option(nm_folders[i].caption,nm_folders[i].id,false,true);
					sel_nm[sel_nm.length] = opt;
				}
			}
			sel_nm[0].selected = true;
		}
	}

	cfilter.prototype.box_select = function(param){
		var aux = this.BoxSelection[param].split(",");
		var ele1 = document.getElementById(aux[0]);
		var ele2 = document.getElementById(aux[1]);
		var noption = "";
		if(param == 0 || param == 1){
			if(ele1.selectedIndex != -1){
				noption = new Option(ele1.value,ele1.value,false,false);
				ele2.options[ele2.length] = noption;
				ele1.options[ele1.selectedIndex] = null;
				ele1.selectedIndex = 0;
			}
		}
	}

	cfilter.prototype.r_rules_form = function(ind, type)
	{
		var fields = new Array;
		var _this = this;
		
		// hide buttons
		Element('form_buttons').style.display = 'none';
		
		if( type === 'voip')
		{
			fields = _this.rulesVoip[ind].split("&&");
			document.getElementById("field9").value = fields[5];			
		}
		else
		{
			this.sel_boxes();
			if(this.rulest.length == 0){
				return false;
			}
			fields = _this.rulest[ind].split("&&");
			document.getElementById("field1").value = fields[3];
			document.getElementById("field2").value = fields[4];
			document.getElementById("field3").value = fields[5];
			document.getElementById("field4").value = fields[11] == 0 ? "" : fields[11];
			
			switch(fields[6]){
				case "folder":
					document.getElementById("radio1").checked  = true;
					var name_mb = fields[7];
					var sel_mb = document.getElementById("select_mailboxes");
					for(var i=0; i < sel_mb.options.length; i++){if((sel_mb.options[i].value) ==  name_mb){sel_mb.options[i].selected = true;}}
					break;
				case "address":
					document.getElementById("radio2").checked   = true;
					document.getElementById("field5").value 	= fields[7];
					break;
				case "reject":
					 document.getElementById("radio3").checked  = true;
					 var text0 = fields[7].split("\\n");				 	
					 for(var i=0; i < text0.length; i++){document.getElementById("field6").value += text0[i] + "\n";}
					 break;
				case "discard":
					document.getElementById("radio4").checked 	= true;
					break;
                        	case "flagged":
                                	document.getElementById("radio5").checked       = true;
                                	break;
			}
			var mark_values = this.values[fields[8]].split(",");
			for(var i=0; i < mark_values.length; i++){
				if( mark_values[i] == "checkBox1" || mark_values[i] == "checkBox2"){
					document.getElementById(mark_values[i]).checked = true;
				}
				if( mark_values[i] == "checkBox2"){
					document.getElementById(mark_values[i]).checked = true;
				}
				if( mark_values[i] == "select_size=1" || mark_values[i] == "select_rules=1"){
					var mark_val = mark_values[i].split("=");
					document.getElementById(mark_val[0]).options[mark_val[1]].selected = true;
				}
			}
		}
	}

	cfilter.prototype.r_rules_out = function(){
	
		var _this = this;
		if(_this.out_officeR.length == 0){
			return false;
		}
		
		// hide buttons
		Element('form_buttons').style.display = 'none';
	
		var aux = _this.out_officeR.split("&&");
		var days   = aux[1];
		var emails = aux[2];
		var mens   = aux[3];		
		var p_emails = new Array;
		var d_emails = new Array;
				
		var p_aux = emails.split(", ");
		for(var i=0; i < p_aux.length; i++){
			p_emails[i] = p_aux[i].substr(0,(p_aux[i].length - 1));
			p_emails[i] = p_emails[i].substr(1,(p_aux[i].length));
		}
		for(var i=0; i < _this.email_ld.length; i++){
			d_emails[i] = _this.email_ld[i];
		}

		diff = function(vet, comp){
			var sel1 = new Array;
			for(var i=0; i < vet.length; i++){
				for(var j=0; j < comp.length; j++){
					if(vet[i] == comp[j]){
						comp.splice(j,1);
						j--;
					}
				}
			}
		};	
		diff(p_emails,d_emails);
		var text    = mens.split("\\n");
		for(var i=0; i < text.length; i++){document.getElementById("field8").value += text[i] + " ";}
		for(var i=0; i < _this.email_ld.length; i++){
			d_emails[i] = _this.email_ld[i];
		}

	}

	/*
	 * Corrige bug 65, solução: desabilitar radio3 e field6 e desmarcar radio3 (ação de rejeição)
	 * quando a caixa de seleção para manter o e-mail na caixa de entrada do usuário for selecionada
	 */
	cfilter.prototype.disable_radio =  function()
	{
		radio4 = Element('radio4');
		radio3 = Element('radio3');
		cb2 = Element('checkBox2');
		field6 = Element('field6');

		if (cb2.checked)
		{
			radio3.disabled = true;
			radio4.disabled = true;
			field6.disabled = true;

			if (radio3.checked)
			{
				radio3.checked = false;
			}
		}
		else
		{
			radio3.disabled = false;
			radio4.disabled = true;
			field6.disabled = false;
		}

	}

	cfilter.prototype.forms_ = function()
	{
			 var form = "";
				 form = "<div id='div_rule' style='display:none'><table id='table_rule' border='0' cellpading='0' cellspacing='0' width='100%'>"+
						"<tr><td colspan='2'><input type='checkBox' id='checkBox1' name='checkb'>"+get_lang('Also check message against next rule') + "</td></tr>"+
						//"<tr><td colspan='2'><input type='checkBox' id='checkBox2' onclick='filter.disable_radio3();' name='checkb'>"+get_lang('Keep a copy of the message at your Inbox')+ "</td><tr>"+
						"<tr><td colspan='2'><hr size='1' width='100%'></td></tr><tr>"+
					 "<tr><td colspan='2'><b>"+get_lang("Criteria")+":</b></td></tr><tr>"+
					 "<td rowspan='4' width='20%'>"+get_lang('Find items')+":<br /><select id='select_rules' name='select_rules'>"+
					 "<option value='1'>"+get_lang("If any criterion is met")+"</option><option value='0'>"+get_lang("If all criteria is met")+"</option>"+
					 "</select></td><td>"+get_lang('The field \"%1\" of the message it contains',get_lang('From'))+".: <input type='text' id='field1' name='field1' size='35' maxlength='200'></td>"+
						"</tr><tr><td>"+get_lang('The field \"%1\" of the message it contains', get_lang('To'))+".: <input type='text' id='field2' name='field2' size='35' maxlength='200'></td>"+
						"</tr><tr><td>"+get_lang('The field \"%1\" of the message it contains', get_lang('Subject'))+".: <input type='text' id='field3' name='field3' size='35' maxlength='200'></td>"+
						"</tr><tr><td>"+get_lang('The size of the message is')+".: &nbsp<select id='select_size' name='select_size'>"+
						"<option value='0'>"+get_lang("Less than")+"</option><option value='1'>"+get_lang("Greater than")+"</option>"+
						"</select>&nbsp;<input type='text' id='field4' name='field4' size='8' maxlength='8'> Kb</td>"+
						"</tr><tr><td colspan='2'><hr size='1' width='100%'><b>"+get_lang("Action")+":</b></td></tr>"+
						"</table><table id='table_rule1' border='0' cellpading='0' cellspacing='0' width='100%'>"+
						"<tr><td colspan='2'><input type='checkBox' id='checkBox2' onclick='filter.disable_radio();' name='checkb'>"+get_lang('Keep a copy of the message at your Inbox')+ "</td><tr>"+
						"<tr><td width='50%'><input type='radio' id='radio1' name='radio' value='folder'>"+get_lang('Store at')+".:</td>"+
						"<td width='50%'><select id='select_mailboxes' name='select_mailboxes'></select></td>"+
						"</tr><tr><td width='50%'><input type='radio' id='radio2' name='radio' value='address'>"+get_lang('Forward to the address')+".:</td>"+
						"<td width='50%'><input type='text' id='field5' name='field5' size='35' maxlength='70'></td>"+
						"</tr><tr><td width='50%'><input type='radio' id='radio3' name='radio' value='reject'>"+get_lang('Send a rejection message')+".:</td>"+
						"<td width='50%'><textarea id='field6' onkeypress='return charCounter(this);' name='field6' rows='3' cols='25'></textarea></td>"+
						"</tr><tr id='tr_radio4'><td colspan='2'><input type='radio' id='radio4' name='radio' value='discard'>"+get_lang('Erase the message')+"</td>"+
                                                "</tr><tr id='tr_radio5'><td colspan='2'><input type='radio' id='radio5' name='radio' value='important'>"+get_lang('Flag as important')+"</td>"+
						"</tr></table></div><div id='div_vacation' style='display:none'>"+
						"<table id='table_vacation' border='0' cellpading='0' cellspacing='0' width='100%'>"+
 						"<tr><td colspan='3'><br /><b>"+get_lang('out office')+"</b></td></tr>"+
						//"<tr><td colspan='3'><br />"+get_lang('Subject')+".: <input type='text' id='field7' name='field7' size='35' maxlength='200'/></td></tr>"+
						"<tr><td colspan='3'><br />"+get_lang('With the following message')+".:</td>"+
	 					"</tr><tr><td colspan='3'><textarea id='field8' rows='8' cols='50'></textarea></td></tr></table></div>" +
						"<div id='div_voipFilter' style='display:none'>" +
						"<br/><table id='table_voipFilter'>" +
						"<tr><td>"+get_lang("Type the subject of the message for receiving a phone warning")+" .:"+
						"</td></tr><tr><td width='100%'><input type='text' id='field9' size='50' maxlength='200'>" +
						"</td></tr><br/></table></div>";// +
				 		//"<span align='right'><input type='button' value="+get_lang("Back")+" onclick='filter.form_m()'></span>"+
				 		//"<span align='right'><input type='button' value="+get_lang("Save")+" onclick='filter.saved_rules()'></span>";
		return form;
	}

	cfilter.prototype.enabled_disabled = function(param){

		// Rules
		if(Element("rule_0") != null){
			for(var i=0; i < this.rulest.length; i++){
				if(Element("rule_"+i).checked){
					var aux_rul = this.rulest[i].split("&&");
					if(aux_rul[2] != param){
						aux_rul[2] = param;					
						var rl = "";
						for(var j=0; j < aux_rul.length; j++){
							rl += aux_rul[j] + "&&";
						}
						rl = rl.substr(0,(rl.length - 2));
						this.rulest[i] = rl;					
					}
				}
			}
		}
		// Out Office
		if(Element("out_0") != null){
			if(Element("out_0").checked){
				var aux_out = this.out_officeR.split("&&");
				if(param == "ENABLED")
					aux_out[4] = "on ";
				else
					aux_out[4] = "off";
				var out = "";
				for(var i=0; i < aux_out.length; i++){
					out += aux_out[i] + "&&";
				}				
				out = out.substr(0,(out.length - 2));
				this.out_officeR = out;
			}
		}

		// Voip
		if(Element("voip_rule_0") != null){
			for(var i=0; i < this.rulesVoip.length; i++){
				if(Element("voip_rule_"+i).checked){
					var aux_rul = this.rulesVoip[i].split("&&");
					if(aux_rul[2] != param){
						aux_rul[2] = param;					
						var rl = "";
						for(var j=0; j < aux_rul.length; j++){
							rl += aux_rul[j] + "&&";
						}
						rl = rl.substr(0,(rl.length - 2));
						this.rulesVoip[i] = rl;					
					}
				}
			}
		}
		
		this.reload_rules();
	}

	cfilter.prototype.new_rule = function(email){
	
		var createFilter = function (param)
		{ 
			if (filter.criticalError){ 
				alert(get_lang('The filters service is out of service, try again later...'));
				return false; 
			} 
			if(filter.rulest.length > 0){ 
				var blockedReg = new RegExp('#rule&&[0-9]+&&ENABLED&&'+param+'&&&&&&discard&&&&0&&&&&&0'); 
				for(var i=0 ; i < filter.rulest.length; i++){ 
					if(blockedReg.test(filter.rulest[i])){ 
						alert(get_lang("Sender blocked")+"!"); 
						return false; 
					} 
				} 
			}                        
		
			
			var div = document.createElement("div");
				div.innerHTML = '<p style="margin:10px 5px 5px 5px;">' +
								'<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+
									get_lang("Do you want to block this e-mail?")+
								'</p>';
	
			$(div).dialog(
			{
				resizable: false,
				title: get_lang('Block Sender'),
				position: 'center',
				width: 350,
				height:140,
				modal: true,
				buttons: [
							{
								text: get_lang("Yes"),
								click: function()
								{
									new_r = "&&ENABLED&&" + param + "&&&&&&discard&&&&0&&&&&&0"; 
							          
									if (filter.e_newrule(new_r))
									{ 
										filter.email_deny.push(new_r); 
										write_msg(get_lang("The sender was blocked"));                                                   
									}
									else
									{
										write_msg(get_lang("You have reached the maximum number of rules"));
									}
									
									$( this ).dialog( "close" );
								},
								style: "margin-top: -2.1em" 
							},
							{
								text: get_lang("No"),
								click: function()
								{
									$( this ).dialog( "close" );
								},
								style: "margin-top: -2.1em" 
							},
						],
                open: function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
                },
                close: function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
                }
			});
		}; 
		
		if ( filter.rulest.length > 0 ) 
			createFilter(email); 
		else 
			this.load_rules( createFilter, email ); 
	}

	cfilter.prototype.e_newrule = function(nw_rule)
	{

		var new_rl = "";
		if (this.rulest.length >= rules_limit)
			return false;
			
		if(this.rulest.length == 0){
			new_rl = "#rule&&1" + nw_rule;
		}else{
			var aux = this.rulest[this.rulest.length -1 ].split("&&");
			new_rl = "#rule&&" + (parseInt(aux[1]) + parseInt(2)) + nw_rule;
		}
		this.rulest.push(new_rl);
		this.saved_all();
		return true;
	}
	
	cfilter.prototype.delete_r = function(){
		// rule
		var _this = this;
		if(Element("rule_0") != null){
			for(var i=0; i < _this.rulest.length; i++){
				if(Element("rule_"+i).checked){_this.rulest[i] = "delete";}
			}
			for(var i=0; i < _this.rulest.length; i++){
				if(_this.rulest[i] == "delete"){_this.rulest.splice(i,1);i--;}
			}
		}
	
		if(_this.rulest.length > 0){
			var cont = parseInt(1);
			for(var i=0; i < _this.rulest.length ; i++){
				var n_rulest = _this.rulest[i].split("&&");
				n_rulest[1] = cont;
				cont = cont + parseInt(2);
				var aux = "";
				for(var j=0; j < n_rulest.length; j++){aux += n_rulest[j] + "&&";}
				aux = aux.substr(0,(aux.length - 2));
				_this.rulest[i] = aux;
			}
		} 
		// out office
		if(Element("out_0") != null){
			if(Element("out_0").checked){
				_this.out_officeR ='';
				_this.out_officeF = false;
				//Save outoffice in prefs:
                                connector.loadScript("preferences");
					prefe.save("outoffice", _this.out_officeF);
			}
		}
		
		// Voip
		if(Element("voip_rule_0") != null){
			for(var i=0; i < _this.rulesVoip.length; i++){
				if(Element("voip_rule_"+i).checked){_this.rulesVoip[i] = "delete";}
			}
			for(var i=0; i < _this.rulesVoip.length; i++){
				if(_this.rulesVoip[i] == "delete"){_this.rulesVoip.splice(i,1);i--;}
			}
		}
	
		if(_this.rulesVoip.length > 0){
			var cont = parseInt(1);
			for(var i=0; i < _this.rulesVoip.length ; i++){
				var n_rulest = _this.rulesVoip[i].split("&&");
				n_rulest[1] = cont;
				cont = cont + parseInt(2);
				var aux = "";
				for(var j=0; j < n_rulest.length; j++){aux += n_rulest[j] + "&&";}
				aux = aux.substr(0,(aux.length - 2));
				_this.rulesVoip[i] = aux;
			}
		}
                
		_this.reload_rules();
	}
	
	cfilter.prototype.reload_rules = function()
	{
		this.saved_all();
		Element('form_body').innerHTML = "";
		this.load_rules();

		if(this.out_officeF)
		{
			write_msg(get_lang("Attention, you are in out of office mode."), true);
		}else{
			clean_msg();
			this.out_officeF = false;
		}
		filters.mount_list();
	}

        cfilter.prototype.forwardAddressValidation = function(addr) {
            if (addr == "")
            {
                return true;
            }
            domains = sieve_forward_domains.replace(/\s/g, "").replace(/\./g, "\\.").replace(/,/g, "|");
            domainRegexp = new RegExp("(" + domains +")$");
            return domainRegexp.test(addr);
        }

	cfilter.prototype.saved_rules = function(){

		var mount_rule = "";
		var form = this.ac_form.split("_");
		var n_rule = "";

		if(form[1] == "rule")
		{
			mount_rule = "#rule&&";
			if(form[0] == "new")
			{
				n_rule = "1&&";
				if(this.rulest.length > 0)
				{
					aux = this.rulest[this.rulest.length - parseInt(1)].split("&&");
					n_rule = (parseInt(aux[1]) + parseInt(2)) + "&&";
				}
				mount_rule += n_rule + "ENABLED&&";
			}
			else
			{
				n_rule = this.rulest[this.ind].split("&&");
				mount_rule += n_rule[1] + "&&";
				mount_rule += n_rule[2] + "&&";
			}

			if(LTrim(Element("field1").value) == "" && LTrim(Element("field2").value) == "" && LTrim(Element("field3").value) == "" && LTrim(Element("field4").value).length == 0)
			{
				alert(get_lang("Define some criterion to the fields Sender, To and Subject with more than 3 characters!"));
				return false;
			}
			if((LTrim(Element("field1").value).length <= 3) && LTrim(Element("field1").value) != ""){
				alert(get_lang("Define some criterion to the fields Sender, To and Subject with more than 3 characters!"));
				return false;
			}if((LTrim(Element("field2").value).length <= 3) && LTrim(Element("field2").value) != ""){
				alert(get_lang("Define some criterion to the fields Sender, To and Subject with more than 3 characters!"));
				return false;
			}if((LTrim(Element("field3").value).length <= 3) && LTrim(Element("field3").value) != ""){
				alert(get_lang("Define some criterion to the fields Sender, To and Subject with more than 3 characters!"));
				return false;
			}
			
			if (LTrim(Element("field4").value).length > 0){
				var isNumero = /^\d+$/.test(Element("field4").value);
				if(!isNumero){
					alert(get_lang("Enter a numerical value to the message size!"));
					return false;				
				}
			}
			for(var i=1; i < 4; i++){mount_rule += LTrim(Element("field"+i).value) + "&&";} 
			var v_checked = false;
			if(Element("radio1").checked){
				if( /[^\x00-\x80]/.test( Element("select_mailboxes").value ) ) {
					alert( get_lang ( 'The selected folder cotain any accented character. The filter dont work with accented folders. Please, rename the folder or choose another folder.' ) );
					return false;
				}
				
				mount_rule += "folder&&";
				var sel_nameBox = Element("select_mailboxes");
				for(var i=0; i < sel_nameBox.options.length; i++){if(sel_nameBox.options[i].selected == true){mount_rule += sel_nameBox.options[i].value + "&&";}}
				v_checked = true;
			}
			if(Element("radio2").checked){
                            if (this.forwardAddressValidation(Element("field5").value)){
				mount_rule += "address&&";
				if(Element("field5").value == ""){
					alert(get_lang("Inform a forwarding e-mail!"));
					return false;
				}else{
                                    if(validateEmail(Element("field5").value)){
						mount_rule += Element("field5").value + "&&";
					}else{
						alert(get_lang("Inform a valid e-mail!"));
						return false;
					}
				}
				v_checked = true;
                            }
                            else
                                {
                                    alert(get_lang("You can't forward e-mails to this domain: %1", Element("field5").value.split("@")[1]));
                                    return false;
                                }
			}
			if(Element("radio3").checked){
				mount_rule += "reject&&";
				if(Element("field6").value == ""){
					alert(get_lang("Inform a text for rejection!"));
					return false;
				}else{
					mount_rule += Element("field6").value + "&&";
				}
				v_checked = true;
			}				
			if(Element("radio4").checked){
				mount_rule += "discard&&&&";
				v_checked = true;
			}
                        if(Element("radio5").checked){
                                mount_rule += "flagged&&&&";
                                v_checked = true;
                        }
			if(!v_checked){
				alert(get_lang("No option marked!"));
				return false;
			}
			var opts = "";
			if(Element("checkBox1").checked == true){
                            opts += ",checkBox1";
                        }
			if(Element("checkBox2").checked == true){
                            opts += ",checkBox2";
                        }
			if(Element("select_size").options[1].selected == true){
                            opts += ",select_size=1";
                        }
			if(Element("select_rules").options[1].selected == true){
                            opts += ",select_rules=1";
                        }
			for(var i=0; i < this.values.length; i++)
                        {
                            if(this.values[i] == opts)
                            {
                                mount_rule += i + "&&";
                            }
                        }
			mount_rule += "&&&&";
			if(LTrim(Element("field4").value) != "" && LTrim(Element("field4").value) >= 0){
				mount_rule += LTrim(Element("field4").value);
			}
			if(form[0] == "new")
			{
				this.rulest[this.rulest.length] = mount_rule;
			}
			else
				this.rulest[this.ind] = mount_rule;
		}
		else if(form[1] == "out")
		{
			mount_rule = "";
			var fld_emails = this.email_ld; // Get first email of list!
			var fld_men	   = Element("field8");
			mount_rule = "#vacation&&";
			mount_rule += "1&&";
			mount_rule += "\"" + fld_emails + "\", ";			
			mount_rule = mount_rule.substr(0,(mount_rule.length - 2));
			mount_rule += "&&";
			mount_rule += fld_men.value + "&&on";
			if(LTrim(fld_men.value) == ""){
				alert(get_lang("Message required"));
				return false;
			}
			else if(fld_men.value.length > 10000){
				alert(get_lang("Your message have %1 characters, the message needs to have less then 10000 characters",fld_men.value.length));
				return false;
			}
			this.out_officeR = mount_rule;
		}
		else if(form[1] == "voip")
		{
			if( Element("field9").value != "" && LTrim(Element("field9").value) != "" )
			{
				mount_rule = "#rule&&";
				if(form[0] == "new")
				{
					n_rule = "1&&";
					if( this.rulesVoip.length > 0 )
					{
						aux = this.rulesVoip[this.rulesVoip.length - parseInt(1)].split("&&");
						n_rule = (parseInt(aux[1]) + parseInt(2)) + "&&";
					}
					mount_rule += n_rule + "ENABLED&&";
				}
				else
				{
					n_rule = this.rulesVoip[this.ind].split("&&");
					mount_rule += n_rule[1] + "&&";
					mount_rule += n_rule[2] + "&&";
				}

				if ( Element("field9").value.indexOf("#") >= 0 )
				{
					alert(get_lang('Caracter "#" is not allowed!'));
					return false;
				}
				
				mount_rule = mount_rule + "&&&&" + Element("field9").value + "&&notify&&" + preferences.voip_email_redirect + "&&8&&&&&&0";

				if( form[0] == "new" )
				{
					this.rulesVoip[this.rulesVoip.length] = mount_rule;
				}
				else
				{
					this.rulesVoip[this.ind] = mount_rule;
				}
			}
			else
			{
				alert( 'CAMPOS EM BRANCO !!' );
				return false;
			}
		}
		
		Element('form_buttons').style.display = '';
		Element('Edit_form_buttons').style.display = 'none';
		this.reload_rules();
	}

	cfilter.prototype.close_frm= function()
	{
		filters.filter_Sh['window_ffilter_ccform'].close();
	}
	
	cfilter.prototype.saved_all = function()
	{
		var aux_rul = "";
		var _this = this;
		var cont = 0;
		
		// Regras Gerais
		if(_this.rulest.length > 0)
		{
			for(var i=0; i < _this.rulest.length; i++)
			{
				var fieldsNormal = _this.rulest[i].split("&&");				
				aux_rul += "_begin_##";
				for(var j=0 ; j < fieldsNormal.length; j++)
				{
					aux_rul += url_encode(fieldsNormal[j]) + "##";
					cont = parseInt(fieldsNormal[1]);					
				}	
				aux_rul += "_end_\n";
			}
		}
	
		// Voip
		if(_this.rulesVoip.length > 0)
		{
			for(var i=0; i < _this.rulesVoip.length; i++)
			{
				var fieldsVoip = _this.rulesVoip[i].split("&&");
				aux_rul += "_begin_##";
				for(var j=0 ; j < fieldsVoip.length; j++)
				{
					if(j == 1)
					{
						if( cont == 0 )
							cont = parseInt(1);
						else
							cont = parseInt(cont) + parseInt(2);
						aux_rul += cont;
						aux_rul += "##";
					}
					else
						aux_rul += url_encode(fieldsVoip[j]) + "##";
				}
				aux_rul += "_end_\n";
			}
		}
		
		// Fora do Escritório
		if(_this.out_officeR.length > 0)
		{
				var aux = _this.out_officeR.split("&&");				
				aux_rul += "_begin_##";
				for(var j=0 ; j < aux.length; j++)
				{
					aux_rul += url_encode(aux[j]) + "##";					
				}	
				aux_rul += "_end_\n";
				_this.out_officeF = (aux[4].replace("\n","") === "off") ? false : true;
				//Save outoffice in prefs:
				connector.loadScript("preferences");
				if(typeof(prefe) == 'undefined')
					setTimeout("filter.saved_all();",500);
				else
					prefe.save("outoffice", _this.out_officeF);
		}
		
		var h_filter = function(data)
		{
			if(data != "Ok"){alert("Erro : \n" + get_lang(data));}
		}
		var args   = "$this.ScriptS.rec_rules";
		var params = "arfilter="+aux_rul;
		if(!_this.criticalError)
			cExecute(args,h_filter,params);
	}

// build object
   var filter;
   filter = new cfilter();
