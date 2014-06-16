	function emQuickCatalogSearch ()
	{
		this.arrayWin = new Array();
		this.el;
		this.cc_contacts = new Array();
		this.cc_groups  = new Array();
	}
       
	$(document).ready(function(){
		$('#combobox option').each(function(){
			if($(this).val() == preferences.catalog_search){
				$(this).attr('selected',true);
			}
		});
	});	   
	
	/* showlist */	
	emQuickCatalogSearch.prototype.showList = function(data, begin, end, ID, field)
	{
		data_  = data;	
		id_    = ID;
        field_ = field;
        begin_ = begin;
        end_   = end;
		content = $("#content_id_"+ID);
		
		keypress_read(data);
		configure_slider(data);
		create_list(data, true);
		details_results(data);

        this.showWindow();	
	}
     
	 
    /* Leitura das teclas de atalho */    
    function keypress_read(data) {	
		$("#dialog-modal").unbind('keydown').keydown(function (e) {
			var keyCode = e.keyCode;
			arrow = {left: 37, up: 38, right: 39, down: 40, enter: 13, esc: 27, space: 32};
			var slider = $( "#slider" );
			switch (keyCode) 
			{
				case arrow.enter:					
					if(!search_focus){
						i = $(".ui-selected:last").attr("value") + 1;
						if($("#actions_"+i).clone().find(".ui-button-text").text() == '+')
							add_contact_field(document.getElementById("contact_"+i).innerHTML, document.getElementById("actions_"+i), "contact_"+i);
						else{
							remove_contact_field(document.getElementById("contact_"+i).innerHTML, document.getElementById("actions_"+i), "contact_"+i);
						}
						document.getElementById("busca").value = "";
						//Evita que o campo "para" receba o enter
						e.preventDefault();
						$("#dialog-modal").dialog("close");
						$('.to').blur().focus();
					}
					if (is_webkit){
						//Posiciona o cursor no final do texto no Chrome
						var txt_area = document.getElementById('to_'+id_);
						var len = txt_area.value.length;
						txt_area.setSelectionRange(len, len);
					}
					break;
				case arrow.left:
					if(!search_focus){
						slider.slider("value", $( "#slider" ).slider( "value" ) - 1);
						create_list(data_, false);	
					}
					break;
				case arrow.right:
					if(!search_focus){
						if((($( "#slider" ).slider( "value" ))) == Math.ceil(data_.length/6))
							break;
						slider.slider( "value", $( "#slider" ).slider( "value" ) + 1);					
						create_list(data_, false);							
					}
					break;
				case arrow.up:
					if(!search_focus){
						show_detail(data_, "up");
					}
					break;
				case arrow.down:
					if(!search_focus){
						show_detail(data_, "down");
					}
					break;
				case arrow.space:

					if(!search_focus){
						i = $(".ui-selected:last").attr("value") + 1;
						if($("#actions_"+i).clone().find(".ui-button-text").text() == '+')
						{
							add_contact_field(document.getElementById("contact_"+i).innerHTML, document.getElementById("actions_"+i), "contact_"+i);
						}else{
							remove_contact_field(document.getElementById("contact_"+i).innerHTML, document.getElementById("actions_"+i), "contact_"+i);
						}
						document.getElementById("busca").value = "";
					}
					break;
			}
		});	
	}
	
	
	/* Mostra os detalhes do contato selecionado */
	function show_detail(data_, direction) {
		id_contact = $(".ui-selected:last").attr("value");
		if(direction == "down") {
			if((id_contact == (((((parseInt($( "#slider" ).slider( "value" )) - 1)*6))+6)-1)) || id_contact == data_.length - 1 || data_.length == 1)
				return;	
			var line_contact = $('li.ui-selected');
			$('li.ui-selected').next().addClass("ui-selected"); 
			id_contact++;
		}
		else {
			if(id_contact == (($( "#slider" ).slider( "value" ) -1)*6)) 
				return;
			var line_contact = $('li.ui-selected');
			$('li.ui-selected').prev().addClass("ui-selected"); 
			id_contact--;
		}					
		line_contact.removeClass("ui-selected").addClass("ui-selectee");
		var details = $("#detalhes_contato").empty();    
		switch(data_['type_catalog']) {
			case 'G':
				if(data_['extra_ldap_fields'] != undefined){
					if(data_['extra_ldap_fields'].length != 0)
						var extra_fields = (data_['extra_ldap_fields']).split("#");   
					else
						var extra_fields = [];  
				}else
					var extra_fields = [];  
				if(preferences.default_fields_quick_search != 0) {
					details.append('<p><label class="attribute">' + utf8_decoder(data_[id_contact].cn[0]) + "</label>" + '<span class="value">' + (data_[id_contact].mail ? data_[id_contact].mail[0] : "") + '</span></p>');
					if((data_[id_contact].telephonenumber ? data_[id_contact].telephonenumber[0] : ""))
						details.append('<p><label class="attribute">Telefone </label>' + '<span class="value">' + (data_[id_contact].telephonenumber ? data_[id_contact].telephonenumber[0] : "") + '</span></p>');           
				}
				for (var i=0; i<=extra_fields.length-1; i++)
				{
					var campo = extra_fields[i].substr(extra_fields[i].indexOf("|")+1);    
					var valor = extra_fields[i].substr(0, (extra_fields[i].indexOf("|")));                                          
					if(data_[id_contact][valor.toLowerCase()] != undefined) {
						var tmp_campo = '<p><label class="attribute">' + campo + "</label>" ;
						for(j=0; j<data_[id_contact][valor.toLowerCase()].length; j++) {
							details.append(tmp_campo + '<span class="value">' + data_[id_contact][valor.toLowerCase()][j] + "</span></p>");
							tmp_campo = "";
						}
					}
				}
				if (data_[id_contact].vacationactive == "TRUE"){
					details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
					if ( data_[id_contact].vacationinfo ){
						details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data_[id_contact].vacationinfo +'</span></div></div>');
						if ( $('.outoffice-inner').height() > $('.outoffice').height() ){						
							var link = $('<a>').html('Ver mais');
							link.attr('id','more');
							link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
							details.append(link);
						}
					}
				}
				break;
			case 'P':
				if(utf8_decoder(data_[id_contact].cn) == 'undefined')
					details.append('<p><label class="attribute">' + utf8_decoder(data_[id_contact].title) + '</label>' + '<span class="value">' + data_[id_contact].short_name + '</span></p>');
				else
					details.append('<p><label class="attribute">' + utf8_decoder(data_[id_contact].cn) + '</label>' + '<span class="value">' + data_[id_contact].mail + '</span></p>');
                if(data_[id_contact].telephonenumber != null)
                    details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + data_[id_contact].telephonenumber + '</span></p>');
				if (data_[id_contact].vacationactive == "TRUE"){
					details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
					if (data_[id_contact].vacationinfo){
						details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data_[id_contact].vacationinfo +'</span></div></div>');
						if ( $('.outoffice-inner').height() > $('.outoffice').height() ){
							var link = $('<a>').html('Ver mais');
							link.attr('id','more');
							link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
							details.append(link);
						}
					}
				}	
				break;
			default:
				if(data_['extra_ldap_fields'] != undefined){
					if(data_['extra_ldap_fields'].length != 0)
						var extra_fields = (data_['extra_ldap_fields']).split("#");   
					else
						var extra_fields = [];   
				}else{
					var extra_fields = [];   
				}
                if(preferences.default_fields_quick_search != 0 || data_[id_contact].type_contact == "P" || data_[id_contact].type_contact == "G") {
					if(utf8_decoder(data_[id_contact].cn) == 'undefined')
						details.append('<p><label class="attribute">' + utf8_decoder(data_[id_contact].title) + '</label>' + '<span class="value">' + data_[id_contact].short_name + '</span></p>');
					else
						details.append('<p><label class="attribute">' + utf8_decoder(data_[id_contact].cn) + '</label>' + '<span class="value">' + data_[id_contact].mail + '</span></p>');
					if(data_[id_contact].telephonenumber != null)
						details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + data_[id_contact].telephonenumber + '</span></p>');
				}	
				if(data_[id_contact].mail[0] == 'undefined')
					break;
				if(data_[id_contact].mail[0].length > 1) {
					for (var i=0; i<=extra_fields.length-1; i++) {
						var campo = extra_fields[i].substr(extra_fields[i].indexOf("|")+1);    
						var valor = extra_fields[i].substr(0, (extra_fields[i].indexOf("|")));                                          
						if(data_[id_contact][valor.toLowerCase()] != undefined) {
							var tmp_campo = '<p><label class="attribute">' + campo + "</label>" ;
							for(j=0; j<data_[id_contact][valor.toLowerCase()].length; j++) {
								details.append(tmp_campo + '<span class="value">' + data_[id_contact][valor.toLowerCase()][j] + "</span></p>");
								tmp_campo = "";
							}
						}
					}
				}
				if (data_[id_contact].vacationactive == "TRUE"){
					details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
					if (data_[id_contact].vacationinfo){
						details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data_[id_contact].vacationinfo +'</span></div></div>');
						if ( $('.outoffice-inner').height() > $('.outoffice').height() ){
							var link = $('<a>').html('Ver mais');
							link.attr('id','more');
							link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
							details.append(link);
						}
					}	
				}				
			break;
		}				
	}

			
	/*
	* MÈtodo que cria a lista de contatos
	*/
	function create_list(data, begin, uiValue) {
		var sliderValue = 1;
		if(typeof($( "#slider" ).slider( "value" )) != "object")
			sliderValue = $( "#slider" ).slider( "value" );
		if(uiValue)
			sliderValue = uiValue;
		$("#detalhes_contato").empty();
		var paginas = Math.ceil(data.length/6); 
		$("#title_usuarios").html( get_lang("Results") );
        var selectable = $("#selectable");
		selectable.empty();
		var acento = data.search_for;
        var Ul = document.createElement("ul");
		var caracteresInvalidos = '‡ËÏÚ˘‚ÍÓÙ˚‰ÎÔˆ¸·ÈÌÛ˙„ı¿»Ã“Ÿ¬ Œ‘€ƒÀœ÷‹¡…Õ”⁄√’';
		var caracteresValidos =   'aeiouaeiouaeiouaeiouaoAEIOUAEIOUAEIOUAEIOUAO';	
		
		var i = new Number();
		var j = new Number();
		var cString = new String();
		var varRes = '';

		for (i = 0; i < data.search_for.length; i++) {
			cString = data.search_for.substring(i, i + 1);
			for (j = 0; j < caracteresInvalidos.length; j++) {
				if (caracteresInvalidos.substring(j, j + 1) == cString){
					cString = caracteresValidos.substring(j, j + 1);
				}
			}
			varRes += cString;
		}
		data.search_for = varRes;
		
		for (i=((sliderValue -1)*6)+1; i<(((sliderValue -1)*6)+1)+6; i++) {
			var Op = document.createElement("li");
			if(i > data.length)
				break;
			if (data['type_catalog'] != 'G' && (data[i-1].mail == null || data[i-1].mail == ""))
				data[i-1].mail = get_lang("No mail"); 
				
			if(i == (((sliderValue -1)*6)+1)) {
				Op.setAttribute("class", "ui-selected");
				Op.setAttribute("className", "ui-selected");
			}	
			else { 	
				Op.setAttribute("class", "ui-selectee");
				Op.setAttribute("className", "ui-selectee");
			}
			var contact_name = utf8_decoder(data[i-1].cn);
			if(contact_name == 'undefined'){
				contact_name = utf8_decoder(data[i-1].title);
				if(contact_name != 'undefined'){
					data[i-1].mail = data[i-1].short_name;
				}
			}
			var exist = "add_contact_field";
			var signal = "+";
			var btnClass = "add";
			emails_adicionados = "";
			var emailList = content.find(field_).filter("input").parent().find("div input");
			//	var array = content.find(".to-tr").find(".box");
			$.each(emailList, function(index, value){
				emails_adicionados += $(value).val() + ",";
			});
			emails_adicionados = emails_adicionados.split(",");
			for(aux=0; aux<emails_adicionados.length -1; aux++) {
				if(emails_adicionados[aux].match(/<([^<]*)>[\s]*$/)){
					if(emails_adicionados[aux].match(/<([^<]*)>[\s]*$/)[1].toLowerCase() == (utf8_decoder(data[i-1].mail)).toLowerCase()){
						emails_adicionados[aux] = "%";
						exist = "remove_contact_field";
						signal = "x";
						btnClass = "remove";
					}
				}else{
					if(emails_adicionados[aux].toLowerCase() == (utf8_decoder(data[i-1].mail)).toLowerCase()){
						emails_adicionados[aux] = "%";
						exist = "remove_contact_field";
						signal = "x";
						btnClass = "remove";
					}
				}
			}
			
			if(contact_name.toLowerCase().indexOf(acento.toLowerCase()) != -1 && acento.indexOf(" ") == -1 && acento.indexOf("@") == -1)									
				contact_name = contact_name.substring(0, contact_name.toLowerCase().indexOf(acento.toLowerCase())) + "<u>" + contact_name.substr(contact_name.toLowerCase().indexOf(acento.toLowerCase()), acento.length) + "</u>" + contact_name.substring(contact_name.toLowerCase().indexOf(acento.toLowerCase()) + acento.length);
			else if(contact_name.toLowerCase().indexOf(data.search_for.toLowerCase()) != -1 && data.search_for.indexOf(" ") == -1 && data.search_for.indexOf("@") == -1)									
				contact_name = contact_name.substring(0, contact_name.toLowerCase().indexOf(data.search_for.toLowerCase())) + "<u>" + contact_name.substr(contact_name.toLowerCase().indexOf(data.search_for.toLowerCase()), data.search_for.length) + "</u>" + contact_name.substring(contact_name.toLowerCase().indexOf(data.search_for.toLowerCase()) + data.search_for.length);
			var vacationImg = '';
			if (data[i-1].vacationactive == "TRUE")
				vacationImg = '<img src="templates/default/images/mala-small.png"/>';	
			var line = '<span class="menu-control"><button class="'+btnClass+'" id="actions_'+i+'" onClick="'+exist+'(document.getElementById(\'contact_' + i + '\').innerHTML, this, \'contact_' + i + '\')">'+ signal +'</button></span><div id="contact_' + i + '" onDblClick="'+exist+'(this.innerHTML, document.getElementById(\'actions_'+i+'\'), \'contact_' + i + '\')"><div class="name_contact"><strong class="name">' + contact_name +' '+ vacationImg +'</strong><em class="email">' + ((utf8_decoder(data[i-1].mail) != undefined)? (utf8_decoder(data[i-1].mail) == 'undefined' ? get_lang("No mail") : utf8_decoder(data[i-1].mail)) : get_lang("No mail")) + '</em></div></div>';
			Op.innerHTML = line;
            $(Op).val(i - 1);
            selectable.append(Op);
			$("#contact_"+i).click(function(){
				document.getElementById("amount-text").focus();
			});
        }
		selectable.append(Ul);
		var first_contact;
		begin ? first_contact = 0 : first_contact = ((sliderValue* 6) -6);			
		var details = $("#detalhes_contato").empty();   
		
		switch (data['type_catalog']) {
			case 'G':
				if(data['extra_ldap_fields']){
					if(data['extra_ldap_fields'].length != 0)
						var extra_fields = (data['extra_ldap_fields']).split("#");   
					else
						var extra_fields = [];   
				}else
					var extra_fields = [];   
				if(preferences.default_fields_quick_search != 0) {  				
					details.append('<p><label class="attribute">' + utf8_decoder(data[first_contact].cn[0]) + "</label>" + '<span class="value">' + (data[first_contact].mail ? data[first_contact].mail[0] : "") + "</span></p>");
					if(data[first_contact].telephonenumber)
						details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + (data[first_contact].telephonenumber ? data[first_contact].telephonenumber[0] : "") + '</span></p>');
				} 
				for (var i=0; i<=extra_fields.length-1; i++) {
					var campo = extra_fields[i].substr(extra_fields[i].indexOf("|")+1);    
					var valor = extra_fields[i].substr(0, (extra_fields[i].indexOf("|")));                                          
					if(data[0][valor.toLowerCase()] != undefined)
					{
						var tmp_campo = '<p><label class="attribute">' + campo + "</label>" ;
						for(j=0; j<data[first_contact][valor.toLowerCase()].length; j++) {
							details.append(tmp_campo + '<span class="value">' + utf8_decoder(data[first_contact][valor.toLowerCase()][j]) + "</span></p>");
							tmp_campo = "";
						}
					}
				}
				if (data[first_contact].vacationactive == "TRUE"){
					details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
					if ( data[first_contact].vacationinfo ){
						details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data[first_contact].vacationinfo +'</span></div></div>');
						if ( $('.outoffice-inner').height() > $('.outoffice').height() ){						
							var link = $('<a>').html('Ver mais');
							link.attr('id','more');
							link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
							details.append(link);
						}	
					}	
				}							
				break;
			case 'P':
				if(utf8_decoder(data[first_contact].cn) != 'undefined')
					details.append('<p><label class="attribute">' + utf8_decoder(data[first_contact].cn) + '</label>' + '<span class="value">' + data[first_contact].mail + '</span></p>');
				else
					details.append('<p><label class="attribute">' + utf8_decoder(data[first_contact].title) + '</label>' + '<span class="value">' + data[first_contact].short_name + '</span></p>');
                if(data[first_contact].telephonenumber != null)
					details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + data[first_contact].telephonenumber  + '</span></p>');
				if (data[first_contact].vacationactive == "TRUE"){
					details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
					if ( data[first_contact].vacationinfo ){
						details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data[first_contact].vacationinfo +'</span></div></div>');
						if ( $('.outoffice-inner').height() > $('.outoffice').height() ){
							var link = $('<a>').html('Ver mais');
							link.attr('id','more');
							link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
							details.append(link);
						}	
					}
				}				
				break;
			default:	
				if(data['extra_ldap_fields']){
					if(data['extra_ldap_fields'].length != 0)
						var extra_fields = (data['extra_ldap_fields']).split("#");   
					else
						var extra_fields = [];   
				}else
					var extra_fields = [];   
				if(preferences.default_fields_quick_search != 0 || data[first_contact].type_contact == "P" || data[first_contact].type_contact == "G") {
					if(utf8_decoder(data[first_contact].cn) != 'undefined')
						details.append('<p><label class="attribute">' + utf8_decoder(data[first_contact].cn) + '</label>' + '<span class="value">' + data[first_contact].mail + '</span></p>');
					else
						details.append('<p><label class="attribute">' + utf8_decoder(data[first_contact].title) + '</label>' + '<span class="value">' + data[first_contact].short_name + '</span></p>');
					if(data[first_contact].telephonenumber != null)
						details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + data[first_contact].telephonenumber + "</span></p>");
				}
				if( data[first_contact].type_contact == "G") {
					if(data[first_contact].mail[0].length > 1) {
						for (var i=0; i<=extra_fields.length-1; i++) {
							var campo = extra_fields[i].substr(extra_fields[i].indexOf("|")+1);    
							var valor = extra_fields[i].substr(0, (extra_fields[i].indexOf("|")));                                          
							if(data[first_contact][valor.toLowerCase()] != undefined) {
								var tmp_campo = '<p><label class="attribute">' + campo + "</label>" ;
								for(j=0; j<data[first_contact][valor.toLowerCase()].length; j++) {
									details.append(tmp_campo + '<span class="value">' + utf8_decoder(data[first_contact][valor.toLowerCase()][j]) + "</span></p>");
									tmp_campo = "";
								}	
							}	
						}
					}
				}
				if (data[first_contact].vacationactive == "TRUE"){
					details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
					if ( data[first_contact].vacationinfo ){
						details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data[first_contact].vacationinfo +'</span></div></div>');
						if ( $('.outoffice-inner').height() > $('.outoffice').height() ){						
							var link = $('<a>').html('Ver mais');
							link.attr('id','more');
							link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
							details.append(link);
						}	
					}
				}				
				break;
		}
		var string_results = sliderValue + " " + get_lang("of") + " " + paginas + " (" + data.length + " " + get_lang("Results") + ")";
		$( "#amount-text" ).val( string_results ).attr('readonly', true);
		$("button").button();
		//$("button.remove").button({icons:{primary:"ui-icon-close"}, text: false});
		//$("button.add").button({icons:{primary:"ui-icon-plus"}, text: false});
		if(!is_ie)
			document.getElementById("amount-text").focus();	
		$("#selectable li:first").focus();
		removeFocus();
	}	
		
		
	/* MÈtodo que configura os detalhes do resultado da busca */
	function details_results(data) {
		$(function() {
			$( "#selectable" ).selectable({
				stop: function() {
					var details = $("#detalhes_contato").empty();                                 
                    $( ".ui-selected", this ).each(function() {
						if(data.length > 0){
							switch(data['type_catalog']) {
								case 'G':
									if(data['extra_ldap_fields'] != undefined){
										if(data['extra_ldap_fields'].length != 0)
											var extra_fields = (data['extra_ldap_fields']).split("#");   
										else
											var extra_fields = [];   
									}else
										var extra_fields = [];   
									if(preferences.default_fields_quick_search != 0 && data[this.value] ) { 
 	                                    details.append('<p><label class="attribute">' + utf8_decoder(data[this.value].cn[0]) + "</label>" + '<span class="value">' + (data[this.value].mail ? data[this.value].mail[0] : "")+ "</span></p>");
 										if(data[this.value].telephonenumber )
											details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + (data[this.value].telephonenumber ? data[this.value].telephonenumber[0] : "")+ "</span></p>");
									}
									for (var i=0; i<=extra_fields.length-1; i++) {
										var campo = extra_fields[i].substr(extra_fields[i].indexOf("|")+1);    
										var valor = extra_fields[i].substr(0, (extra_fields[i].indexOf("|")));                                          
										if( data[this.value] && data[this.value][valor.toLowerCase()] != undefined) {
											var tmp_campo = '<p><label class="attribute">' + campo + "</label>" ;
											for(j=0; j<data[this.value][valor.toLowerCase()].length; j++) {
												details.append(tmp_campo + '<span class="value">' + utf8_decoder(data[this.value][valor.toLowerCase()][j]) + "</span></p>");
												tmp_campo = "";
											}
										}
									}
									if (data[this.value].vacationactive == "TRUE"){
										details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
										if ( data[this.value].vacationinfo ){
											details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data[this.value].vacationinfo +'</span></div></div>');
											if ( $('.outoffice-inner').height() > $('.outoffice').height() ){											
												var link = $('<a>').html('Ver mais');
												link.attr('id','more');
												link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
												details.append(link);
											}
										}
									}									
									break;
								case 'P':
									if(utf8_decoder(data[this.value].cn) == 'undefined')
										details.append('<p><label class="attribute">' + utf8_decoder(data[this.value].title) + "</label>" + '<span class="value">' + data[this.value].short_name + "</span></p>");
									else
										details.append('<p><label class="attribute">' + utf8_decoder(data[this.value].cn) + "</label>" + '<span class="value">' + data[this.value].mail + "</span></p>");
									if(data[this.value].telephonenumber != null)
										details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + data[this.value].telephonenumber + "</span></p>");
									if (data[this.value].vacationactive == "TRUE"){
										details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
										if ( data[this.value].vacationinfo ){
											details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data[this.value].vacationinfo +'</span></div></div>');
											if ( $('.outoffice-inner').height() > $('.outoffice').height() ){
												var link = $('<a>').html('Ver mais');
												link.attr('id','more');
												link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
												details.append(link);
											}	
										}
									}									
									break;
								default:
									if(this.value == undefined)
										break;
										
									if(data['extra_ldap_fields'].length != 0)
										var extra_fields = (data['extra_ldap_fields']).split("#");   
									else
										var extra_fields = [];     
									if(preferences.default_fields_quick_search != 0 || data[this.value].type_contact == "P" || data[this.value].type_contact == "G") {
										if(utf8_decoder(data[this.value].cn) == 'undefined')
											details.append('<p><label class="attribute">' + utf8_decoder(data[this.value].title) + "</label>" + '<span class="value">' + data[this.value].short_name + "</span></p>");
										else
											details.append('<p><label class="attribute">' + utf8_decoder(data[this.value].cn) + "</label>" + '<span class="value">' + data[this.value].mail + "</span></p>");
										if(data[this.value].telephonenumber != null)
											details.append('<p><label class="attribute">Telefone</label>' + '<span class="value">' + data[this.value].telephonenumber + "</span></p>");
									}
									if(data[this.value].mail[0] == 'undefined')
											break;
									if(data[this.value].mail[0] != get_lang("No mail")) {
										if(data[this.value].mail[0].length > 1) {
											for (var i=0; i<=extra_fields.length-1; i++) {
												var campo = extra_fields[i].substr(extra_fields[i].indexOf("|")+1);    
												var valor = extra_fields[i].substr(0, (extra_fields[i].indexOf("|")));                                          
												if(data[this.value][valor.toLowerCase()] != undefined) {
													var tmp_campo = '<p><label class="attribute">' + campo + "</label>" ;
													for(j=0; j<data[this.value][valor.toLowerCase()].length; j++) {
														details.append(tmp_campo + '<span class="value">' + utf8_decoder(data[this.value][valor.toLowerCase()][j]) + "</span></p>");
														tmp_campo = "";
													}
												}
											}
										}
									}
									if (data[this.value].vacationactive == "TRUE"){
										details.append('<p><img src="templates/default/images/mala-big.png"/><span class="title-outoffice">_[[Filter "Out of Office" active.]]</span></p>');	
										if ( data[this.value].vacationinfo ){
											details.append('<div class="outoffice"><div class="outoffice-inner"><span>'+ data[this.value].vacationinfo +'</span></div></div>')
											if ( $('.outoffice-inner').height() > $('.outoffice').height() ){
												var link = $('<a>').html('Ver mais');
												link.attr('id','more');
												link.bind('click',function(){$('.outoffice').animate({height: $('.outoffice-inner').height(),width: '-=15px'},800); $('#more').unbind('click').hide();});
												details.append(link);
											}
										}	
									}									
									break;
							}
						}
					});
				}
            });
        });
    }
     
	 
    /* 
    * MÈtodo que configura o slider e os resultados 
    */
    function configure_slider(data) {	
        var paginas = (Math.ceil(data.length/6)); 
        $("#title_usuarios").html( get_lang("Results") );
        $(function() {
            $( "#slider" ).slider({
				value:1,
				min: 1,
				max: paginas,
				step: 1,
				slide: function( event, ui ) {
					create_list(data, false, ui.value); 
					var string_results = ui.value + " " + get_lang("of") + " " + paginas + " (" + data.length + " " + get_lang("Results") + ")"; 		
					$( "#amount-text" ).val( string_results );
				}
            });
			$("#selectable li:first button:first").focus();
		});
    }
        
	var search_focus = false;
		
	function setFocus() {search_focus = true;return;}
	
	function removeFocus() {search_focus = false;return;}
		
    function checkEnter(e) {
		var kC = window.event ? event.keyCode :
        e && e.keyCode ? e.keyCode :
        e && e.which ? e.which : null;
        if (kC) 
			return kC == 13;
        else
			return false;
	}
        
		
    /* 
     * Faz a busca direto da tela modal  
     */
    function buscaContato(param) {
		$("#busca, #detalhes_contato, #selectable").empty();
		if(param.length == 0) {
            alert(get_lang("Please enter a parameter to search"));
            return;
        }            
		if(param.length < preferences.search_characters_number) {
            alert(get_lang("parameter must be at least") + " " +  preferences.search_characters_number + " " + get_lang("characters"));
            return false;
        }
        var catalog = $("#combobox").val();
        var handler_emQuickSearch = function(data) {
            data_ = data;
			if(data_.length > 0) {     
				configure_slider(data_);
				create_list(data, true);
				details_results(data_);
            }
            else {
				$("#detalhes_contato, #selectable").empty();
				var selectable = $("#selectable");
				var Ul = document.createElement("ul");
				Ul.onclick = function(){
				};
				var Op = document.createElement("li");
				Op.innerHTML = '<span class="menu-control"></span><div id="no_results"><div class="name_contact"><strong class="name">_[[No Results Found]]</strong></div></div>';
				selectable.append(Op);
				selectable.append(Ul);
				var string_results = "0 " + get_lang("of") + " 0 (0 "  + get_lang("Results") + ")"; 		
				$( "#amount-text" ).val( string_results );
				details_results(data);
                configure_slider(data); 
            }
        }
        cExecute ("$this.ldap_functions.quicksearchcontact&search_for="+param+"&field=TO&ID=0&catalog="+catalog, handler_emQuickSearch);
    }
		
	function verifyEmails(emailAux, contato) {	
		var contact_id = contato.split("_");		
		if(contact_id[1] != 0)
		{
			var ini = parseInt((contact_id[1]/6).toFixed(0));
			if(ini != 0 && ini != 1){
				ini = ini * 6;
				var fim = ini+1;
			}
			else{
				ini = 1;
				var fim = ini;
			}
			fim = fim +5;
			var contact2 = "";
			for(aux = ini; aux <= fim; aux++){
				if(contact_id[1] != aux){
					if($("#"+contact_id[0]+"_"+aux).find('.email').text() == emailAux){
						contact2 = contact_id[0]+"_"+aux;
						var button = document.getElementById("actions_"+aux);
						var div = document.getElementById(contact2);
						if($("#actions_"+aux).find('.ui-button-text').text() == '+'){
							button.onclick = function(){
								remove_contact_field(div.innerHTML, button, contact2);
							};
							div.ondblclick = function(){
								remove_contact_field(div.innerHTML, button, contact2);
							};
							$("#actions_"+aux).find('.ui-button-text').text('x');
						}
						else{
							button.onclick = function(){
								add_contact_field(div.innerHTML, button, contact2);
							};
							div.ondblclick = function(){
								add_contact_field(div.innerHTML, button, contact2);
							};
							$("#actions_"+aux).find('.ui-button-text').text('+');
						}
					}
				}
			}
		}
	}
    /* 
    * MÈtodo que adiciona o contato selecionado no campo do email (To, CC, CCo) 
    */
    function add_contact_field(contact, button, divs) {
	
		var nome = $(contact).clone().find('.name').text();
		var email = $(contact).clone().find('.email').text();
		if(email == get_lang("No mail")){
			alert(get_lang("It is not possible to add this contact as a recipient because it does not have email"));
			if(!is_ie)
				document.getElementById("amount-text").focus();
			return;
		}
		verifyEmails(email, divs);
		final_contact = "\""+nome +"\" <"+email+"> ";

		final_contact = final_contact.replace(/\/n/, ""); 
		var index = parseInt(divs.split("_")[1])-1;
        if(typeof(data_[index].isExternal) != 'undefined' &&  data_[index].isExternal == true)
            draw_email_box(final_contact, content.find(field_).filter("textarea:first"));
        else if(data_[index].type_contact == "G"){
			var ldap_id = preferences.expressoMail_ldap_identifier_recipient;
			if(ldap_id){
				draw_email_box(
					(data_[index][ldap_id.toLowerCase()] ? data_[index][ldap_id.toLowerCase()][0] : final_contact)
					, content.find(field_).filter("textarea:first")
				);
			}else{
				draw_email_box(final_contact, content.find(field_).filter("textarea:first"));
			}
		}else{
			draw_email_box((data_[index].id_contact ? data_[index].id_contact : data_[index].id), content.find(field_).filter("textarea:first"), (data_[index].id_contact ? true : "G"));
		}
		content.find(field_).filter("textarea:first").val("");
		button.onclick = function(){
			remove_contact_field(document.getElementById(divs).innerHTML, button, divs);
		};
		var div = document.getElementById(divs);
		div.ondblclick = function(){
			remove_contact_field(div.innerHTML, button, divs);
		};
		button.innerHTML = '<span class="ui-button-text" style="">x</span>';
    }
		
		
	/* 
    * MÈtodo que remove o contato selecionado no campo do email (To, CC, CCo) 
    */
	function remove_contact_field(contact, button, divs) {
		var email = $(contact).clone().find('.email').text();
		var array = content.find(field_).parent().find("div input");
		$.each(array, function(index, value){
			var validated_email = $(value).val();
			if(validated_email.match(/<([^<]*)>[\s]*$/)){
				if(validated_email.match(/<([^<]*)>[\s]*$/)[1].toLowerCase() == email.toLowerCase()) {
					$(value).parent().remove();
				}
			}
		});
	
		button.onclick = function(){
			add_contact_field(document.getElementById(divs).innerHTML, button, divs);
		};
		var div = document.getElementById(divs);
		div.ondblclick = function(){
			add_contact_field(div.innerHTML, button, divs);
		};
		button.innerHTML = '<span class="ui-button-text" style="">+</span>';
	}
        
	 
	/* Decoder utf8 */	
    function utf8_decoder ( str_data ) {
        var tmp_arr = [], i = 0, ac = 0, c1 = 0, c2 = 0, c3 = 0;     
        str_data += '';    
        while ( i < str_data.length ) {
            c1 = str_data.charCodeAt(i);
            if (c1 < 128) {
				tmp_arr[ac++] = String.fromCharCode(c1);
                i++;
            } else if ((c1 > 191) && (c1 < 224)) {
                        c2 = str_data.charCodeAt(i+1);
                        tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
                        i += 2;
                      } else {
                                c2 = str_data.charCodeAt(i+1);
                                c3 = str_data.charCodeAt(i+2);
                                tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                                i += 3;
                             }
        }
        return tmp_arr.join('');
    }
        
    
	/* Mostra tela de help */	
    function show_help() {
		$(function() {
			$( "#dialog-modal_help" ).dialog({
						resizable: false,
						width: 540,
						modal: true,
						closeOnEscape: true,
						close : function (event, ui) {
									$( "#amount-text" ).focus();
									event.stopPropagation();
                                    if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
								},
                        open: function(event, ui) 
                        {
                            if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
                        }
			});
		});
	}	
	
	$("#dialog-modal_help").on("dialogclose", function(){
		if(!is_ie)
			$( "#amount-text" ).focus();
	});

          
	emQuickCatalogSearch.prototype.showWindow = function () {
		$(this).bind('keydown');
		$(function() {
        $( "#dialog:ui-dialog" ).dialog("close").dialog( "destroy" );                    
        $( "#dialog-modal" ).dialog({
			resizable: false,
//			height: "auto",
			width: 795,
			modal: true,
			position: 'bottom',
			closeOnEscape: true,
			close: function (event, ui) {
				event.stopPropagation();
				$(this).unbind('keydown');
				if (is_ie) { 
					var range= content.find(field_).createTextRange(); 
					range.collapse(false); 
					range.select(); 
				} else { 
					content.find(field_).focus();    
				} 
                if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
			},
			open: function () {
				removeFocus();
				$("#selectable li:first").focus();
				document.getElementById("amount-text").focus();	
                if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
			},
			focus: function (event, ui) {
				//$(this).unbind('keydown');
			},
			beforeClose: function (event, ui) {
				$(this).unbind('keydown');
			},
			buttons:[
						{
							text: "Fechar",
							click: function(){
												$("#detalhes_contato, #selectable, #busca").empty();
												$(this).dialog("close");
												if (is_ie) { 
													var range= content.find(field_).createTextRange(); 
													range.collapse(false); 
													range.select(); 
												} else { 
													content.find(field_).focus();    
												} 
											 },
							style: "margin-top: -2.1em" 
						}
					]
			});
        });
		$(".ui-dialog .ui-dialog-titlebar")
		.append('<a href="#" class="ui-dialog-titlebar-minimize ui-corner-all" role="button"><span class="ui-icon ui-icon-minusthick">minimize</span></a>')
		.find('.ui-dialog-titlebar-minimize').click(function() {
			$(".ui-dialog-buttonpane, .ui-dialog-content").toggle();
			$(".ui-icon-minusthick, .ui-icon-newwin").toggleClass('ui-icon-minusthick').toggleClass('ui-icon-newwin');
		});
		$("#dialog-modal .ui-icon-search").click(function (){buscaContato($('#busca').val());});						
		$("#dialog-modal button, input.button").button();
}

	
	/* Build the Object */
	//QuickCatalogSearch;
	QuickCatalogSearch = new emQuickCatalogSearch();
