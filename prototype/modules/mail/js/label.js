function msgCallback(msg){
	switch(msg){
		case '#LabelNameError': 
			alert('_[[There is already a bookmark with this name]].');
			break;
		case '#LabelSlotError':
			alert('_[[Reached limit markers created]].');
			break;	
		default:
			alert('_[[An error occurred while saving the Marker]]');
	}
}
function configureLabel(event, ui){
	var idmarkernow = $(this).find('[name="labelItemId"]').val();
	winElement.find('.edit-label').val(idmarkernow);
	winElement.find('.input-nome').val($(this).find("span").text());
	
	var colorpicker = winElement.find('.lebals-colorpicker');
	colorpicker.find('input[name="backgroundColor"]').val(rgb2hex($(this).css("backgroundColor")))
	.css("background-color", $(this).css("backgroundColor")).focus().end()
	.find('input[name="fontColor"]').val(rgb2hex($(this).css("color"))).css("background-color", $(this).css("color")).focus().end()
	.find('input[name="borderColor"]').val(rgb2hex($(this).css("border-left-color"))).css("background-color", $(this).css("border-left-color")).focus().end()
	.find('.preview-label-outer').css({
		'background-color':$(this).css("backgroundColor"),
		'color':$(this).css("color"),
		'border-color':$(this).css("border-left-color")
	})		
	winElement.find(".preview-label-inner").text($(this).text());
}

function editLabel(data){	
	var conteudoSpan = $(this).parent().find(".text-list").text();
	var span = $(this).parent().find(".text-list");
	$(this).parent().trigger('click').unbind('click', configureLabel).find("span.text-list")
	.html('<input name="edit-value-list" class="edit-value-list" type="text" maxlength="18" value="'+conteudoSpan+'">').find("input[name='edit-value-list']").keydown(function(event){
		event.stopPropagation();
		$("input.edit-value-list").keyup(function () {
			var value = $(this).val();
			winElement.find('.input-nome').val(value);
		}).keyup();
		if(event.keyCode == 13){
			event.preventDefault();
			var nameLabel = $(".label-list").find(".edit-value-list").val();
			$(span).html(conteudoSpan)
			.parent().find('.edit').css("display","").parents("li").click(configureLabel);
			save_editLabel(nameLabel, data.applyToSelectedMessages);
		}else if( event.keyCode == 27){
			$(this).trigger("focusout");
		}
	}).focusout(function(){
		$(span).html(conteudoSpan)
		.parent().find('.edit').css("display","").parents("li").click(configureLabel);
	}).focus();
	$(this).hide();
}

function deleteLabel(event){
	var id = $(this).parents(".label-item").attr("class").match(/label-item-([\d]+[()a-zA-Z]*)/)[1];
	var nameLabel = winElement.find(".input-nome").val();
	confirmDelete(id, nameLabel);
	event.stopImmediatePropagation();
}

function colors_suggestions(){
	return [
				{name:'Padrão', border:'#3366cc', font:'#ffffff', background:'#3366cc'},
				{name:'Coala', border:'#123456', font:'#ffffff', background:'#385c80'},
				{name:'Tomate', border:'#d5130b', font:'#111111', background:'#e36d76'},
				{name:'Limão', border:'#32ed21', font:'#1f3f1c', background:'#b2f1ac'},
				{name:'Alto contraste', border:'#000000', font:'#ffffff', background:'#222222'}
			]		
}

function rgb2hex(rgb){	
	if(!!(rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/))){
		return "#" +
		("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
	}else{
		return rgb;
	}
}

function returnLabels(msgsReference){
	var folderIndex = {};
   for (var i=0; i < msgsReference.length; i++) {       
       if( folderIndex[ msgsReference[i].folderName] ){        
           folderIndex[ msgsReference[i].folderName].push( msgsReference[i].messageNumber );          
       }else{
           folderIndex[ msgsReference[i].folderName] = [ msgsReference[i].messageNumber ];
       }      
   }
   var labels = []; 
   for(var folder in folderIndex){
   		var currentLabes = DataLayer.get('labeled',{ filter:[
               'AND',
               ['=', 'folderName', folder ],
               ['IN', 'messageNumber', folderIndex[ folder ] ]
               ], criteria: {deepness: '2'}});

   		if( $.isArray( currentLabes ) )
			labels = labels.concat( currentLabes );
   }
   var labelsIndex = {};
   $.each( labels, function(i, e){
       if( labelsIndex[ e.messageNumber ] ){
           labelsIndex[ e.messageNumber ][ 'labels' ].push( e.label );
       }else{
           labelsIndex[ e.messageNumber ] = e;
           labelsIndex[ e.messageNumber ][ 'labels' ] = [];
           labelsIndex[ e.messageNumber ][ 'labels' ].push( e.label );
       }
   });
   return labelsIndex;
}


function updateMessageLabels(msgsReference){
	var labelsIndex = returnLabels(msgsReference);
   $.each(labelsIndex,function(index,e){
           updateLabelsColumn({msg_number: e.messageNumber, boxname: e.folderName,labels: e.labels, forceIcon: true});
   });
}

function confirmDelete(id,nameLabel){
	$.Zebra_Dialog('Deseja excluir esse marcador <strong>'+nameLabel+'</strong> das conversas que está relacionado e excluí-lo sem remover as conversas?', {
		'type':     'question',
		'custom_class': (is_ie ? 'configure-zebra-dialog custom-zebra-filter' : 'custom-zebra-filter'),
		'title':    'Atenção',
		'buttons': ['Sim','Não'],		
		'overlay_opacity': '0.5',
		'onClose':  function(caption) {
			if(caption == 'Sim'){
				delete_label(id);
			}
		}
	});	
	if(is_ie)
		$(".ZebraDialogOverlay").css("z-index","1006");
}		
	
function save_editLabel(nameLabel, apply){	

	var lengthSpace = $.trim(nameLabel);
	
	if( lengthSpace.length >= 2){	
		var qtdLi = $(".label-list-container ul").find("li").not(".empty-item").length;	
		// salvar marcador
		if(qtdLi > 0 ){
			$(".save-label").button("disable");
			var labelEdited = {
				id : winElement.find(".edit-label").val(),
				uid: User.me.id,
				name : winElement.find('.input-nome').val().trim(),
				backgroundColor : winElement.find("input[name='backgroundColor']").val(),
				fontColor : winElement.find("input[name='fontColor']").val(),
				borderColor : winElement.find("input[name='borderColor']").val()
			}
			DataLayer.put('label', labelEdited.id, labelEdited);
			DataLayer.commit(false, false, function(data){
				var hasLabel = true;
				$.each(data, function(index, value) {
					
					hasLabel = typeof value == 'object' ? false : value;
					
				});
					
					if(!hasLabel){
						$(".label-list-container .label-list li").each(function(){
							var regex_match_2 = $(this).attr("class").match(/label-item-([\d]+[()a-zA-Z]*)/);
							
							 if(regex_match_2 && regex_match_2[1] && regex_match_2[1] == labelEdited.id){
							   $(this).html("<input type='hidden' name='labelItemId' class='id-item-list' value='"+labelEdited.id+"'>"+"<span class='text-list'>"+labelEdited.name+"</span><div class='button close tiny' style='float: right'></div><div class='button edit tiny' style='float: right'></div>").css({
								"background" : labelEdited.backgroundColor,
								"color" : labelEdited.fontColor, 
								"border-color" : labelEdited.borderColor	
							   });
							   $(this).trigger('click');
							 }	
						});
						
						$("#MyMarckersList .label-list li").each(function(){
							var regex_match_2 = $(this).attr("class").match(/label-item-([\d]+[()a-zA-Z]*)/);
							
							if(regex_match_2 && regex_match_2[1] && regex_match_2[1] == labelEdited.id){
								$(this).html("<input type='hidden' name='labelItemId' class='id-item-list' value='"+labelEdited.id+"'>"+"<span>"+labelEdited.name+"</span>");
								$(this).find(".square-color").css('background', labelEdited.backgroundColor);
							}	
						});
					
						winElement.find(".insert-label").val("");
						$.Watermark.ShowAll();
						
						$(".label-list-container .label-list li").find('.button').button()
							.filter('.edit').button({icons:{primary:'ui-icon-pencil'}, text:false}).end()
							.filter('.close').button({icons:{primary:'ui-icon-close'}, text:false});
							
						winElement.find('.edit').unbind("click").click(editLabel);				
						winElement.find('.close').click(deleteLabel);
                        if( preferences['use_followupflags_and_labels'] == "1" )
						    draw_tree_labels();
						var msgsReference = DataLayer.get('labeled', {filter: ['=', 'labelId', ''+labelEdited.id]}, true);
						updateMessageLabels(msgsReference);
				
						/**
						* Atualiza menu remove o menu presente em cada mensagem listada em uma pasta 
						* e carrega novamente para que os dados sejam atualizados
						*/
						$.contextMenu( 'destroy', ".table_box tbody tr");
						loadMenu();
					}else{
						msgCallback(hasLabel);
					}
					
					$(".add-label-button").empty()
					.addClass("ui-icon ui-icon-plus");
					//winElement.find('.input-nome').val("").focus;
			});
		// criar marcador
		} else {
			var nameLabel = winElement.find(".input-nome").val();
			new_label(nameLabel, false, apply);				
			$.Watermark.ShowAll();		
			$(".save-label").button("disable");	
		}
		//$(".label-list-container .label-list").find(".label-item-"+labelEdited.id).trigger("click");
	} else {
		alert("_[[Minimum 2 characters]]");
	}	
}

function delete_label(id){
	$(".label-list li").each(function () {
		var regex_match = $(this).attr("class").match(/label-item-([\d]+[()a-zA-Z]*)/);
		if (regex_match && regex_match[1] && regex_match[1] == id) {	
			$(this).remove();
		}
	});
	
	var msgsReference = DataLayer.get('labeled', {filter: ['=', 'labelId', ''+id]}, true);
		
	DataLayer.remove('label', id);

	DataLayer.commit(false, false, function(data){
		$.contextMenu( 'destroy', ".table_box tbody tr");
		loadMenu();
		updateMessageLabels(msgsReference);	
	});
	winElement.find(".label-list li:first").trigger("click");
	
	if($(".label-list li").length == 0){
		winElement.find(".label-list-container").html('<ul class="label-list"><li class="empty-item">'+'_[[No label found]]'+'.</li></ul>');
		$(".save-label").button("disable");	
	}
	var qtdLi = $(".label-list-container ul").find("li").not(".empty-item").length;
	if (qtdLi == 0){
		$(".my-labels").hide();
		$(".label-list-container ul").find(".empty-item").css("display","");
		winElement.find(".buttons .save-label .ui-button-text").text('Criar');
		$(".delete-label").button("disable");	
		$(".save-label").button("disable");	
				
		winElement.find('.input-nome').val("");
		winElement.find(".preview-label-inner").text("");
	
		var colorpicker = winElement.find('.lebals-colorpicker');
		colorpicker.find('input[name="backgroundColor"]').val("#ebebeb")
		.css("background-color", "#ebebeb").focus().end()
		.find('input[name="fontColor"]').val("#000000").css("background-color", "#000000").focus().end()
		.find('input[name="borderColor"]').val("#000000").css("background-color", "#000000").focus().end()
		.find('.preview-label-outer').css({
			'background-color':"#ebebeb",
			'color':"#000000",
			'border-color':"#000000"
		});
		$(".input-nome").keyup(function () {
			var value = $(this).val();
			winElement.find('.preview-label-inner').text(value);
		}).keyup();				
	}	
}

function new_label(nameLabel, isNew, apply){
	var labelCreated = {
		uid: User.me.id,
		name : nameLabel.trim(),
		backgroundColor : !!isNew ? '#ebebeb' : winElement.find("input[name='backgroundColor']").val(),
		fontColor : !!isNew ? '#000000' : winElement.find("input[name='fontColor']").val(),
		borderColor :!!isNew ? '#000000' : winElement.find("input[name='borderColor']").val()
	}
	DataLayer.put('label', labelCreated);
	
	$(".add-label-button").removeClass("ui-icon ui-icon-plus")
	.html('<img alt="Carregando" title="Carregando" style="margin-left:10px;" src="../prototype/modules/mail/img/loader.gif" />');
	var hasLabel = true;
	var labelId;
	DataLayer.commit(false, false, function(data){
		$.each(data, function(index, value) {
			if(typeof value == 'object'){
				hasLabel = false;
				labelId = value.id;
			}else{
				hasLabel = value;
			}
		});
	
		if(!hasLabel){
			newLabel = {
				id: labelId, 
				name : nameLabel.trim(),
				uid: User.me.id,
				bgColor : !!isNew ? '#ebebeb' : winElement.find("input[name='backgroundColor']").val(),
				fontColor : !!isNew ? '#000000' : winElement.find("input[name='fontColor']").val(),
				borderColor : !!isNew ? '#000000' : winElement.find("input[name='borderColor']").val()
			};
		
			/** Marca as mensagens selecionadas com o marcador criado*/
			if(apply){
				labeledMessages(newLabel.id)
			}
			
			$(".label-list-container ul").find(".empty-item").css("display","none");
			$(".label-list-container ul")
			.prepend(DataLayer.render("../prototype/modules/mail/templates/label_listitem.ejs", newLabel))
			.find("li:first")
			.fadeIn("slow").click(configureLabel);
            if( preferences['use_followupflags_and_labels'] == "1" )
			    draw_tree_labels();

			$(".label-list-container .label-list li").not(".empty-item").click(function(){
				$(".label-list-container .label-list li").find("img").remove();
				$(".label-list-container .label-list li.label-item").removeClass("selected");
				$(this).prepend("<img src='../prototype/modules/mail/img/triangle.png' style='margin: 0 5px 0 -5px;'>");
				$(this).addClass("selected");
			});
			
			$(".label-list-container .label-list li").find('.button').button()
			.filter('.edit').button({icons:{primary:'ui-icon-pencil'}, text:false}).end()
			.filter('.close').button({icons:{primary:'ui-icon-close'}, text:false});
			
			//posiciona para edição o label inserido			
			winElement.find("ul.label-list li:first").trigger("click");			
			winElement.find(".buttons .save-label .ui-button-text").text('Salvar');
			$(".delete-label").button("enable");
			
			winElement.find('.edit').unbind("click").click(editLabel);				
			winElement.find('.close').click(deleteLabel);
			
			$(".my-labels").show();
			
			$.contextMenu( 'destroy', ".table_box tbody tr");
			loadMenu();
			
			$(".add-label-button").empty()
			.addClass("ui-icon ui-icon-plus");
		}else{
			$(".add-label-button").empty()
			.addClass("ui-icon ui-icon-plus");
			msgCallback(hasLabel);
		}
	});
}

function SortByName(a, b){
    var aName = a.name.toLowerCase();
    var bName = b.name.toLowerCase();
    return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
}

//Reduz a quantidade de requests
function orderLabel(labels){

    if(labels == "")
        return labels;

    if(!$.isArray( labels )){
        var array = [];

        for(var i in labels){

            array[ array.length ] = labels[ i ];

        }

        labels = array;

    }

    return labels.sort(SortByName);
}

function init_label(data){

	winElement = data.window;
	
	//TODO Mudar quando API abstrair atualizações no cache
	var labels = DataLayer.get('label');

    labels = orderLabel( labels );
	
	if(labels){
		Label_List = winElement
		.find(".label-list-container").html(DataLayer.render("../prototype/modules/mail/templates/label_list.ejs", {labels: labels}));
		
	}else{
		//Exibe a mensagem informando o usuário de que não há nenhum marcador cadastrado.
		Label_List = winElement
		.find(".label-list-container").html('<ul class="label-list"><li class="empty-item">'+'_[[No labels found.]]'+'.</li></ul>');
		
	}
	Label_List.end()
	.find('.button').button()
	.filter('.edit').button({icons:{primary:'ui-icon-pencil'}, text:false}).end()
	.filter('.close').button({icons:{primary:'ui-icon-close'}, text:false}); 	
	
	winElement.find('.edit').click(editLabel);
	winElement.find('.close').click(deleteLabel);
	
	//marca 'd agua
	winElement.find(".insert-label").Watermark(winElement.find(".insert-label").val());
	
	/*
	$("input.insert-label").keyup(function () {
		$.Watermark.HideAll();
		var value = $(this).val();
		winElement.find('.input-nome').val(value);
	}).keyup();
	*/
	if (!(labels)){
		winElement.find(".buttons .save-label .ui-button-text").text('Criar');
		$(".delete-label").button("disable");
	}else{
		winElement.find(".label-list li:first").addClass("selected").prepend("<img src='../prototype/modules/mail/img/triangle.png' style='margin: 0 5px 0 -5px;'>");
	}
	$(".save-label").button("disable");
	/**
	* seta a ação de click para os marcadores listados na tela
	*/
	winElement.find(".label-list li").not(".empty-item").click(configureLabel);
	
	if (data.selectedItem)
		winElement.find(".label-list li.label-item-"+data.selectedItem).trigger("click");
	else
		winElement.find(".label-list li:first").trigger("click");
		
	var dataColorPicker = {
		colorsSuggestions: colors_suggestions()
	};
	
	winElement.find('select.color-suggestions').change(function() {
		$(".save-label").button("enable");
		var colorpicker = winElement.find('.lebals-colorpicker');
		var colors;
		if(colors = dataColorPicker.colorsSuggestions[$(this).val()]) {	
			colorpicker
			.find('input[name="fontColor"]').val(colors.font).focus().end()	
			.find('input[name="backgroundColor"]').val(colors.background).focus().end()
			.find('input[name="borderColor"]').val(colors.border).focus().end()

			.find('.preview-label-outer').css({
				'background-color':dataColorPicker.colorsSuggestions[$(this).val()].background,
				'border-color':dataColorPicker.colorsSuggestions[$(this).val()].border,
				'color':dataColorPicker.colorsSuggestions[$(this).val()].font 
			});
		}					
	});
	var colorpickerPreviewChange = function(color) {
			
		var pickedup = winElement.find('.colorwell-selected').val(color).css('background-color', color);

		$(".save-label").button("enable");
		
		var colorpicker = winElement.find('.lebals-colorpicker');			

		if (pickedup.is('input[name="backgroundColor"]')) {
			colorpicker.find('.preview-label-outer').css('background-color',color);
		} else if (pickedup.is('input[name="fontColor"]')) {
			colorpicker.find('.preview-label-outer').css('color',color);
		} else if (pickedup.is('input[name="borderColor"]')) {
			colorpicker.find('.preview-label-outer').css('border-color',color);
		}		
	} 
		
	var f = $.farbtastic(winElement.find('.colorpicker'), colorpickerPreviewChange);
	var selected;
				
	winElement.find('.colorwell').each(function () {
		f.linkTo(this);
	})
	.focus(function() {
		if (selected) {
			$(selected).removeClass('colorwell-selected');
		}
		$(selected = this).addClass('colorwell-selected');
		f.linkTo(this, colorpickerPreviewChange);
		f.linkTo(colorpickerPreviewChange);
	});
	
	winElement.find(".add-label-button").click(function (event) {
		$.Watermark.HideAll();
		var nameLabel = winElement.find(".insert-label").val();
		
		var lengthSpace = $.trim(nameLabel);
		
		if(lengthSpace.length >= 2){
			new_label(nameLabel, true, data.applyToSelectedMessages);
			$.Watermark.ShowAll();
			winElement.find(".insert-label").val("");
			} else {
				alert("_[[Minimum 2 characters]]");
			}
		event.stopImmediatePropagation();
	});
	
	winElement.find('.insert-label').keydown(function(event, ui) {
		if (event.keyCode == 13)
			winElement.find(".add-label-button").trigger('click');
	});
	
	//excluir marcador {deve ser para o botão grande na janela de edição e nao o pequeno da lista}
	winElement.find(".buttons .delete-label").click(function(event){
		var id = winElement.find(".edit-label").val();		
		var nameLabel = winElement.find(".input-nome").val();	
		confirmDelete(id, nameLabel);
		event.stopImmediatePropagation();
	});
	
	$(".label-list-container .label-list li").not(".empty-item").click(function(){
		$(".label-list-container .label-list li").find("img").remove();
		$(".label-list-container .label-list li.label-item").removeClass("selected");
		$(this).prepend("<img src='../prototype/modules/mail/img/triangle.png' style='margin: 0 5px 0 -4px;'>");
		$(this).addClass("selected");
	});
		
	//salvar/criar marcador
	winElement.find(".buttons .save-label").click(function(){
		var nameLabel = winElement.find(".input-nome").val();
		save_editLabel(nameLabel, data.applyToSelectedMessages);
	});	
	//desfazer marcador
	winElement.find(".buttons .undo-label").click(function(event){
		var edit = winElement.find(".edit-label").val();		
		$(".label-list-container .label-list li").each(function(){
			var regex_match_3 = $(this).attr("class").match(/label-item-([\d]+[()a-zA-Z]*)/);
			if(regex_match_3 && regex_match_3[1] && regex_match_3[1] == edit){
				$(this).trigger("click");
			}
		});
	});
	
	//fechar
	$(".button-close-window .close-window").click(function(){
		$(".label-configure-win").dialog("close");
	});
	
	winElement.find(':input').change(function(event){
		if (event.keyCode != '27' && event.keyCode != '13' && !$(event.target).is(".edit-value-list") && !$(event.target).is(".insert-label") )
		winElement.find(".save-label").button("enable");
	}).keydown(function(event){
		if (event.keyCode != '27' && event.keyCode != '13' && !$(event.target).is(".edit-value-list") && !$(event.target).is(".insert-label"))
		winElement.find(".save-label").button("enable");
	});	

	$(".input-nome").keyup(function () {
				var value = $(this).val();
				winElement.find('.preview-label-inner').text(value);
	}).keyup();	
}
