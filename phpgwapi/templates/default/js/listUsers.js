//Funcoes
	
	function remUser(){
		primary_group  = self.document.getElementById('primary_group_list');
		select = self.document.getElementById('user_list');							
			
		for(var i = 0;i < select.options.length; i++) {
			if(select.options[i].selected) {	
				if(primary_group != null) {		
					for(var j = 0;j < primary_group.options.length; j++) {
						if(primary_group.options[j].value == select.options[i].value) {
							primary_group.options[j] = null;
							if(primary_group.options.length > 0)
								primary_group.options[0].selected = true;
							
							break;
						}
					}	
				}	
				select.options[i--] = null;							
			}
		}
		
		if(select.options.length)
			select.options[0].selected = true;					
	}
	
	function remUserAcl(){
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
	
	function openListUsers(newWidth,newHeight,currentApp,type){			
		
		newScreenX  = screen.width - newWidth;		
		newScreenY  = 0;

		window.open('phpgwapi/templates/default/listUsers.php?'+(type == 'g' ? 'type=g&' : '')+'currentApp='+currentApp,"","width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",status=1,toolbar=no,scrollbars=yes,resizable=no");
		
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
 
 
 	function addUser() {
		var select = window.document.getElementById('user_list_in');
		var selectOpener = window.opener.document.getElementById('user_list');
	
		var primary_group  = window.opener.document.getElementById('primary_group_list');

		if(document.all)
			primary_group  = window.opener.document.all['primary_group_list'];
		
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
					if(primary_group != null) {
						primary_group.options[primary_group.options.length] = window.opener.document.createElement('option');
						primary_group.options[primary_group.options.length - 1].value =select.options[i].value;
						primary_group.options[primary_group.options.length - 1].text = select.options[i].text;
					}
					
				}
				
			}
		}
		
		selectOpener.options[selectOpener.options.length-1].selected = true;
 	}				
 	
 	function addUserAcl() {
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
					if( option.value.charAt(0) == 'g' )
						option.text = "(G) "+option.text;
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
 	} 	