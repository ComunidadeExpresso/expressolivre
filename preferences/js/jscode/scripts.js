//Funcoes
	function remUser(){
		select = self.document.getElementById('user_list');								
		for(var i = 0;i < select.options.length; i++)				
			if(select.options[i].selected){
				ids = getIds(select.options[i].value);
				for(j = 0; j < ids.length; j++)
					document.getElementById(ids[j]).disabled = true;
				select.options[i--] = null;
			}
		
		if(select.options.length)
			select.options[0].selected = true;			
		
		execAction('LOAD');
	}
	
	function openListUsers(newWidth,newHeight){					
		newScreenX  = screen.width - newWidth;		
		newScreenY  = 0;		
		window.open('preferences/templates/celepar/listUsers.php',"","width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",toolbar=no,scrollbars=yes,resizable=no");
		
	}
											
	function execAction(action){
								
		if(!window.opener)				
			doc = window.document;
		else
			doc = window.opener.document;
		
		select = doc.getElementById('user_list');					
		checkAttr = doc.formAcl.checkAttr;												
		for(i = 0; i < select.length; i++) {					
			if(select.options[i].selected){
				ids = getIds(select.options[i].value);
											
				for(j = 0; j < ids.length; j++){										
					
					if(action == 'SAVE') {
						doc.getElementById(ids[j]).disabled = !checkAttr[j].checked;
					}					
					if(action == 'LOAD') {
						
						checkAttr[j].checked = !doc.getElementById(ids[j]).disabled;
					}
				}													
			}			
		}
		
		if(!select.length)
			for(j = 0; j < checkAttr.length; j++)
				checkAttr[j].disabled = true;
				
		else		
			for(j = 0; j < checkAttr.length; j++)
				checkAttr[j].disabled = false;
			
	}
		
	function getIds(value){
			
		ids = new Array();
		ids[0] = value + '_1]' ;
		ids[1] = value + '_2]' ;
		ids[2] = value + '_4]' ;
		ids[3] = value + '_8]' ;
		ids[4] = value + '_16]';
		
		return ids;
	}

	
 	function optionFinder(oText) {				 		 													

		for(var i = 0;i < select.options.length; i++)				
			select.options[i--] = null;
																							
		for(i = 0; i < users.length; i++)
																							
			if(users[i].text.substring(0 ,oText.value.length).toUpperCase() == oText.value.toUpperCase() ||
				(users[i].text.substring(0 ,3) == '(G)' &&
				users[i].text.substring(4 ,4+oText.value.length).toUpperCase() == oText.value.toUpperCase())) {																					
				sel = select.options;						
				option = new Option(users[i].text,users[i].value);
				option.onclick = users[i].onclick;				
				sel[sel.length] = option;
			 
			}										  		
 	}			 
 
 
 	function adicionaLista() {
		var select = window.document.getElementById('user_list_in');
		var selectOpener = window.opener.document.getElementById('user_list');
		for (i = 0 ; i < select.length ; i++) {				

			if (select.options[i].selected) {
				isSelected = false;

				for(var j = 0;j < selectOpener.options.length; j++) {																			
					if(selectOpener.options[j].value == select.options[i].value){
						isSelected = true;						
						break;	
					}
				}

				if(!isSelected){

					option = window.opener.document.createElement('option');
					option.value =select.options[i].value;
					option.text = select.options[i].text;
					selectOpener.options[selectOpener.options.length] = option;	
					ids = getIds(select.options[i].value);
					for(k = 0; k < ids.length; k++) {															
						el = window.opener.document.createElement('input');
						el.type='hidden';
						el.value ='Y';
						el.name = ids[k];
						el.disabled = true;						
						el.id = ids[k];
						window.opener.document.getElementById("tdHiddens").appendChild(el);
					}										
				}
				
			}
		}
		selectOpener.options[selectOpener.options.length-1].selected = true;
		execAction('LOAD');
		window.close();
 	}

	function FormatTelephoneNumber(event, campo)
	{
		separador1 = '(';
		separador2 = ')';
		separador3 = '-';
		
		vr = campo.value;
		tam = vr.length;

		if ((tam == 1) && (( event.keyCode != 8 ) || ( event.keyCode != 46 )))
			campo.value = '';

		if ((tam == 3) && (( event.keyCode != 8 ) || ( event.keyCode != 46 )))
			campo.value = vr.substr( 0, tam - 1 );
	
		if (( tam <= 1 ) && ( event.keyCode != 8 ) && ( event.keyCode != 46 ))
			campo.value = separador1 + vr;
		
		if (( tam == 3 ) && ( event.keyCode != 8 ) && ( event.keyCode != 46 ))
			campo.value = vr + separador2;
			
		if (( tam == 8 ) && (( event.keyCode != 8 ) && ( event.keyCode != 46 )))
			campo.value = vr + separador3;

		if ((( tam == 9 ) || ( tam == 8 )) && (( event.keyCode == 8 ) || ( event.keyCode == 46 )))
			campo.value = vr.substr( 0, tam - 1 );
	}
	/*Função que padroniza DATA*/
	function formatDate(obj){
	    obj.value = obj.value.replace(/\D/g, "");
	    obj.value = obj.value.replace(/(\d{2})(\d)/, "$1/$2");
	    obj.value = obj.value.replace(/(\d{2})(\d{2})/, "$1/$2");
		obj.value = obj.value.replace(/(\d{2})(\d{2})(\d)/, "$1/$2/$3");
		
	}