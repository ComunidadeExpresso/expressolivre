 	function emQuickAddTelephone ()	{
 		var div_user_info = Element("user_info");
 		// User without telephone and no permission to edit it. Abort.
 		if(preferences.blockpersonaldata && !preferences.telephone_number){
 			return;
 		} 		
 		if(div_user_info) {
 			
 			var span  = document.createElement("SPAN");
 			span.title = preferences.blockpersonaldata ? get_lang("You can't modify your Commercial Telephone.") : get_lang("Update my telephone");
 			span.style.cursor = "pointer";
 			span.style.marginLeft = "10px";
 			span.style.fontWeight = "bold";
 			span.style.color  = "YELLOW";
 			span.style.textDecoration = 'underline';
 			span.innerHTML = preferences.telephone_number ?  preferences.telephone_number : get_lang('Update my telephone');
 			span.onclick= function() { 
 				if(preferences.blockpersonaldata) 
 					alert(get_lang("You can't modify your Commercial Telephone."));
 				else 
 					QuickAddTelephone.update_telephonenumber(this.id);
 			}

 			span.id = "span_telephonenumber";
 			div_user_info.appendChild(span);
 		}
	}

 	emQuickAddTelephone.prototype.FormatTelephoneNumber = function (event, campo)	{
 		event = is_ie ? window.event : event; 		
 		var code = (event.keyCode ? event.keyCode :event.which); 		
		separador1 = '(';
		separador2 = ')';
		separador3 = '-';		
		vr = campo.value;
		tam = vr.length;
		if ((tam == 1) && (( code != 8 ) || ( code != 46 )))
			campo.value = '';
		if ((tam == 3) && (( code != 8 ) || ( code != 46 )))
			campo.value = vr.substr( 0, tam - 1 );	
		if (( tam <= 1 ) && ( code != 8 ) && ( code != 46 ))
			campo.value = separador1 + vr;		
		if (( tam == 3 ) && ( code != 8 ) && ( code != 46 ))
			campo.value = vr + separador2;			
		if (( tam == 8 ) && (( code != 8 ) && ( code != 46 )))
			campo.value = vr + separador3;
		if ((( tam == 9 ) || ( tam == 8 )) && (( code == 8 ) || ( code == 46 )))
			campo.value = vr.substr( 0, tam - 1 );
	}

 	emQuickAddTelephone.prototype.update_telephonenumber = function (spanID){
		var span = document.getElementById(spanID);
		var input  = document.createElement("INPUT");
		input.type  = "text";
		input.value = (span.innerHTML == get_lang('Update my telephone') ? '(00)0000-0000' : span.innerHTML);
		input.oldvalue = span.innerHTML;
		input.autocomplete="off";
		input.name = "telephone_number";
		input.style.marginLeft = "10px";
		input.size = "12";
		input.maxLength = "13";
		input.id = "input_telephonenumber";
		input.onkeyup = function(event) {
			event = is_ie ? window.event : event;
			var code = (event.keyCode ? event.keyCode :event.which);
			if(code == '13'){
				if(input.oldvalue != input.value && input.value != '' && input.value !='(00)0000-0000')
					QuickAddTelephone.save_telephonenumber(this);
				else
					QuickAddTelephone.load_telephonenumber(this);
			}else if(code == '27'){
				QuickAddTelephone.load_telephonenumber(this);					
			}else {
				QuickAddTelephone.FormatTelephoneNumber(event, this);
			} 
		}; 
		input.onblur = function() { 
			QuickAddTelephone.load_telephonenumber(this);
		};
		span.parentNode.replaceChild(input, span);
		input.focus();
	}
		
 	emQuickAddTelephone.prototype.load_telephonenumber = function(input){
		var span  = document.createElement("SPAN");
		span.title= get_lang("Update my telephone");
		span.style.cursor = "pointer";
		span.style.fontWeight = "bold";
		span.style.marginLeft = "10px";
		span.style.color  = "YELLOW";
		span.style.textDecoration = 'underline';
		span.innerHTML = input.oldvalue;
		span.onclick= function() { QuickAddTelephone.update_telephonenumber(this.id)};			
		span.id = "span_telephonenumber";
		input.parentNode.replaceChild(span, input);	
	}
 	
 	emQuickAddTelephone.prototype.save_telephonenumber = function(input){
		var handler_save = function(data){			
			if(data && data['error']){
				alert(data['error']);
				return;
			}
			else
				write_msg(get_lang("Telephone number updated with success."));
			input.oldvalue = input.value;
			QuickAddTelephone.load_telephonenumber(input);
		}
		cExecute ("$this.ldap_functions.save_telephoneNumber&number="+input.value+"&id="+input.id, handler_save);
	}	

/* Build the Object */
	var QuickAddTelephone;
	QuickAddTelephone = new emQuickAddTelephone();
