  /***************************************************************************\
  * cc_config.js														      *	
  * Written by:                                                               *
  *  - Adriano Pereira da silva - Prognus <adriano@prognus.com.br>            *
  *  - Airton Bordin Junior - Prognus <airton@prognus.com.br>                 *
  *  - http://www.prognus.com.br                                              *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/



	/*
	 * @function cc_attribute_clear
	 * @abstract Elimina as tuplas com campos vazios antes de enviar o formulário via POST
	 * @author Prognus software livre - http://www.prognus.com.br
	 * @param form
	 */
	function cc_attribute_clear(form)
	{
		form.submit();
	}
	
	
	
	/*
	 * @function cc_attribute_add
	 * @abstract Adiciona uma nova tupla de campos ao final da lista de tuplas 
	 * @author Prognus software livre - http://www.prognus.com.br
	 * @param
	 */
	function cc_attribute_add()
	{
		var stringTexto = document.getElementById('textHidden').value;
		var stringMultivalorado = document.getElementById('multitextHidden').value;
		var stringYes = document.getElementById('yesHidden').value;
		var stringNo = document.getElementById('noHidden').value;
		var stringDelete = document.getElementById('deleteHidden').value;
		
		var numeroAtributo = 0;
		var table = document.getElementsByName("cc_newconftable");
		var vars = table[0].getElementsByTagName("TR"); 
		
		var maior = 0;
		for (var i=1; i<vars.length; i++) {
			var tupla = vars[i];
			var linhaIndice = tupla.getElementsByTagName("input")[0];
			var arrayDeTokens = linhaIndice.getAttribute("name").split("_");
			
			numeroAtributo = arrayDeTokens[3];
			numeroAtributo = numeroAtributo.substr(0, numeroAtributo.length -1);
			numeroAtributo = parseInt(numeroAtributo);  
			if (numeroAtributo > maior) { 
				maior = numeroAtributo;
			}
		}
		
		maior++;
		numeroAtributo = maior;
		
		var cc_attribute_name = "newsettings[cc_attribute_name_" + numeroAtributo + "]";
		var cc_attribute_ldapname = "newsettings[cc_attribute_ldapname_" + numeroAtributo + "]";
		var cc_attribute_type        = "newsettings[cc_attribute_type_" + numeroAtributo + "]";
		var cc_attribute_searchable = "newsettings[cc_attribute_searchable_" + numeroAtributo + "]";
		
		var pool = document.getElementById("cc_newconftable");
		
		//LINHA DOS CAMPOS
		var tudo = document.createElement("TR");
	
		//INPUT DO NOME
		var tudoBody1 = document.createElement("TD");
		tudoBody1.innerHTML = "<input type=\"text\" name=\""+cc_attribute_name+"\" value=\"\" style=\"width:170px;\" />";
		tudo.appendChild(tudoBody1);
		
		//INPUT CORRESPONDENTE
		var tudoBody2 = document.createElement("TD");
		tudoBody2.innerHTML = "<input type=\"text\" name=\""+cc_attribute_ldapname+"\" value=\"\" style=\"width:170px;\" />";
		tudo.appendChild(tudoBody2);
		
		//TD SELECT MULTI OR TEXT 
		var tudoBody3 = document.createElement("TD");
			//SELECT MULTI OR TEXT 
			var select1 = document.createElement("SELECT");
			select1.name = cc_attribute_type;
			select1.style.width = "86px";
			select1.style.margin = "0px 0px 0px 8px";
			//OPTION TEXT
			var option1 = document.createElement("OPTION");
			option1.innerHTML = stringTexto;
			option1.value = "text";
			//OPTION MULTI
			var option2 = document.createElement("OPTION");
			option2.innerHTML = stringMultivalorado;
			option2.value = "multivalues";
			//ADD OPTIONs TO SELECT
			select1.appendChild(option1);
			select1.appendChild(option2);
			//ADD SELECT TO TD
			tudoBody3.appendChild(select1);
			//ADD TD TO TR
			tudo.appendChild(tudoBody3);
			
		//TD SELECT YES OR NO 
		var tudoBody4 = document.createElement("TD");
			//SELECT YES OR NO 
			var select2 = document.createElement("SELECT");
			select2.name = cc_attribute_searchable;
			select2.style.margin = "0px 16px";
			//OPTION YES
			var option3 = document.createElement("OPTION");
			option3.innerHTML = stringYes;
			option3.value = "true";
			//OPTION NO
			var option4 = document.createElement("OPTION");
			option4.selected = "selected";
			option4.innerHTML = stringNo;
			option4.value = "false";
			//ADD OPTIONs TO SELECT
			select2.appendChild(option3);
			select2.appendChild(option4);
			//ADD SELECT TO TD
			tudoBody4.appendChild(select2);
			//ADD TD TO TR
			tudo.appendChild(tudoBody4);
		
		//TD IMG 		
		var tudoBody5 = document.createElement("TD");
		tudoBody5.innerHTML = "<img src=\"contactcenter/templates/default/images/cc_x.png\" title=\""+ stringDelete +"\" alt=\""+ stringDelete +"\" style=\"width: 15px; height: 14px; cursor: pointer; position: relative; top: 3px;\" onclick=\"javascript:cc_attribute_delete(this)\">"
		tudo.appendChild(tudoBody5);

		pool.appendChild(tudo);
	}
	
	/*
	 * @function cc_attribute_clear(form)
	 * @abstract Exclui uma tupla e de campos, onde e é uma referência ao elemento HTML (div) container da tupla  
	 * @author Prognus software livre - http://www.prognus.com.br
	 * @param e
	 * 
	 */
	function cc_attribute_delete(e) 
	{
			var tupla = e.parentNode;
			tupla = tupla.parentNode;
			var mom = tupla.parentNode;
			var inputs = tupla.getElementsByTagName("input");

				inputs[0].value = "";
				inputs[1].value = "";
				
				var select = tupla.getElementsByTagName("select");
				select[0].innerHTML = "<option value=\"\" selected=\"selected\"></option>";
				select[1].innerHTML = "<option value=\"\" selected=\"selected\"></option>";		
				
			tupla.style.visible = "hidden";
			tupla.style.display = "none";
		
//		e.parentNode.parentNode.removeChild(e.parentNode);
	}
