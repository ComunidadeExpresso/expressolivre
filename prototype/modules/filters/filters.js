/* 
 * Retorna as regras por remetente e que o usuário deseja ser avisado 
 */
function getFromAlertRules() {
    fromRules = [];
    if(preferences['use_alert_filter_criteria'] == "1")
    {
        var filters = DataLayer.get("filter");
        var alertMessage;
        var active;
        for (var index in filters) {
            alertMessage = filters[index]['alertMessage'];
            active = filters[index]['enabled'];
            for(var criterias in filters[index]['actions']) {
                if (filters[index]['actions'][criterias]['type'] == 'fileinto' && alertMessage == 'true' && active == 'true')
                    fromRules[fromRules.length] = filters[index]['actions'][criterias]['parameter'];
            }
        }

    }
    return fromRules;
}

/* 
 * Verifica se a regra Fora de escritório está ativa
 */
function outOfficeVerify(force) {
	if(force)
        DataLayer.remove('filter', false);

	var filters = DataLayer.get("filter");
	var outoffice_active = false;
	outoffice = false;
	for(var index in filters){
		if(filters[index].id == "vacation")
			outoffice = true;
		if(filters[index].id == "vacation" && filters[index].enabled.toString() == "true"){
			write_msg(get_lang("Attention, you are in out of office mode."), true);
			outoffice_active = true;
			break;
		}
	}
	if(!outoffice_active && old_msg == get_lang("Attention, you are in out of office mode."))
		clean_msg();
}

/* 
 * Valida os campos do formulário da tela de filtros para salvar.
 */
valid_save = function(){
	var accord = list_container.find(".rule-details-container").accordion({autoHeight: false});
	if(list_container.find('[name="name"]').attr("disabled") != "disabled")
	{
		if($.trim(list_container.find('[name="name"]').val()) == ""){
			$.Zebra_Dialog(get_lang("No name filled"),{
                'overlay_opacity': '0.5',
                'custom_class': 'custom-zebra-filter',
				'onClose':  function(caption) {
					list_container.find('[name="name"]').focus();
				}
			});
			accord.accordion('activate',0);
			return false;
		}else if($.trim(list_container.find('[name="name"]').val()) == "vacation"){
			$.Zebra_Dialog(get_lang("Invalid name, 'vacation' is a reserved word"),{
                'overlay_opacity': '0.5',
                'custom_class': 'custom-zebra-filter',
				'onClose':  function(caption) {
					list_container.find('[name="name"]').val("").focus();
				}
			});
			accord.accordion('activate',0);
			return false;
		}else{
			filter_list = DataLayer.get("filter", true);
			var error = false;
			$.each(filter_list, function(index, value){
				if(filter_list[index].name == list_container.find('[name="name"]').val()){
					$.Zebra_Dialog(get_lang("There is already a filter with this name"),{
                        'overlay_opacity': '0.5',
                        'custom_class': 'custom-zebra-filter',
						'onClose':  function(caption) {
							list_container.find('[name="name"]').val("").focus();
						}
					});
					accord.accordion('activate',0);
					error = true;
					return;
				}
			});
			if(error){
				return false;
			}
		}
	}
	var criteria = "";
	var criteria_list = list_container.find('[name="criteriaValue[]"]');
	var er_num = /^[0-9]+$/;

	if($(".sizeRule").val() != "" && er_num.test($(".sizeRule").val()) == false) {
		$.Zebra_Dialog(get_lang("Size rule must be a number"),{
            'overlay_opacity': '0.5',
            'custom_class': 'custom-zebra-filter',
			'onClose':  function(caption) {
				list_container.find('[name="actionType[]"]:checked').parent().find("input:text, textarea").focus();
			}
		});
		accord.accordion('activate',0);
		return false;
	}
	$.each(criteria_list, function(index, value){
		if(($(criteria_list[index]).val() != "" && index != 5) || ($(criteria_list[index]).is(':checked'))){
			criteria += $.trim($(criteria_list[index]).val());
		}
	});
	if(criteria == ""){
		$.Zebra_Dialog(get_lang("No criteria filled"),{
            'overlay_opacity': '0.5',
            'custom_class': 'custom-zebra-filter',
			'onClose':  function(caption) {
				list_container.find('[name="criteriaValue[]"]:first').focus();
			}
		});
		accord.accordion('activate',0);
		return false;
	}
	if(list_container.find('[name="actionType[]"]:checked').parent().find("input:text, textarea").length){
		if($.trim(list_container.find('[name="actionType[]"]:checked').parent().find("input:text, textarea").val()) == ""){
			$.Zebra_Dialog(get_lang("Fill the action value"),{
                'overlay_opacity': '0.5',
                'custom_class': 'custom-zebra-filter',
				'onClose':  function(caption) {
					list_container.find('[name="actionType[]"]:checked').parent().find("input:text, textarea").focus();
				}
			});
			return false;
		}
		var er_mail = RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
		if(er_mail.test($.trim(list_container.find('[value="redirect"]:checked').parent().find("input:text").val())) == false && list_container.find('[name="actionType[]"]:checked').val() == 'redirect'){
			$.Zebra_Dialog(get_lang("Invalid mail"),{
                'overlay_opacity': '0.5',
                'custom_class': 'custom-zebra-filter',
				'onClose':  function(caption) {
					list_container.find('[name="actionType[]"]:checked').parent().find("input:text, textarea").focus();
				}
			});
			return false;
		}
	}
	/*Validação ao salvar filtro com alerta*/
	var criteria_operator_list = list_container.find('[name="criteriaOperator[]"]');
	if(list_container.find('.alertMessage').is(':checked')){
		if($(criteria_list[0]).val() != "" && $('.select-folderlist').find('[type="radio"]').is(':checked') && $(criteria_operator_list[0]).find('option:selected').val() != "!*"){
			var hasValue = false;
			$.each(criteria_list, function(index, value){
				if(($(criteria_list[index]).val() != "" && index != 0 && index != 5) || ($(criteria_list[index]).is(':checked'))){
					if($('.fields-isexact').find(':checked').val() == 'or'){
						hasValue = true;
					}
				}
			});
			if(hasValue){
				$.Zebra_Dialog('<strong>'+get_lang('Filter with alert')+'</strong><br />'+get_lang('If more than one criterion for the filter, the "Meeting all the criteria" must be selected'),{
                    'overlay_opacity': '0.5',
                    'custom_class': 'custom-zebra-filter'
                });
				return false;
			} 
		}else{
			$.Zebra_Dialog('<strong>'+get_lang('Filter with alert')+'</strong><br />'+get_lang('The filter should be set as a criteria "Sender" and action "Archive folder"'),{
                'overlay_opacity': '0.5',
                'custom_class': 'custom-zebra-filter'
            });
			return false;
		}
	}
	return true;
};

function urlencode (str) {
    str = (str + '').toString();
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
    replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
}

bytes2Size = function(bytes) {
	var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
	if (bytes === 0) return 'n/a';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	var size = (i<2) ? Math.round((bytes / Math.pow(1024, i))) : Math.round((bytes / Math.pow(1024, i)) * 100)/100;
	return  size + ' ' + sizes[i];
};

flags2Class = function(cellvalue) {
	var flags_enum = cellvalue.split(',');
	var classes = '';
	for (var i=0; i<flags_enum.length; i++){
		classes += '<span class="icon-'+flags_enum[i].toLowerCase()+'"> </span>';
	}
	return classes;
};

date2Time = function (timestamp) {
    var date = new Date();
    if( typeof timestamp === "string" )
        timestamp = parseInt( timestamp, 10 );
    if ((date.getTime() - timestamp) < (24*60*60*1000)) {
        return '<span class="timable" title="'+timestamp+'"> </span>';
    } else {
        date = new Date(timestamp);
        var b = date.toISOString().split("T")[0].split("-");
        var c = b[2] + "/" + b[1] + "/" + b[0];
        return '<span class="datable">' + c + '</span>';
    }
};

keys = function( object ){

  var array = [];

  for( var key in object )
       array[ array.length ] = key;

  return( array );

}

/* 
 * Preenche o formulário de filtros com as informações originais para edição
 */
showDetails = function( filter ){
    form.get(0).reset();

    nameObj.val( filter.name );
	nameObj.attr("disabled", "disabled");
    for( var i = 0; i < filter.criteria.length; i++ ){
		if(filter.criteria[i].field == 'hasAttachment') {
			$(".hasAttachment").attr("checked", "True");
			continue;
		}
		criterias.filter( '[value="' + filter.criteria[i].field + '"]' )
		.siblings( '[name="criteriaOperator[]"]' ).val( filter.criteria[i].operator ).end()
		.siblings( '[name="criteriaValue[]"]' ).val( filter.criteria[i].value );
    }
    var first_fileinto_action = true;
    for( var i = 0; i < filter.actions.length; i++ ){
		if(filter.actions[i].type == "redirect")
			actions.siblings('[name="addressRedirect"]').val(filter.actions[i].parameter);
		if(filter.actions[i].type == "reject")
        {
            actions.siblings('[name="messageReject"]').val(filter.actions[i].parameter);
            $('.ui-widget-content .fileintoInbox ').attr('checked', false).parent().addClass(' hidden');
        }
		if((filter.actions[i].type == "fileinto" && first_fileinto_action) || filter.actions[i].type == "setflag"){
			actions.parent().find('[value="'+filter.actions[i].parameter+'"]').attr("selected", "selected");
//			if(filter.actions[i].type == "fileinto")
//				first_fileinto_action =false;
		}
		/*A condição abaixo é executada quando uma segunda action do tipo "fileinto" for encontrada*/
		if(filter.actions[i].type == "fileinto" && !first_fileinto_action){
			$('.fileintoInbox').attr('checked', 'True');
		}else{
            first_fileinto_action =false;
            actions.filter( '[value="' + filter.actions[i].type + '"]' ).attr("checked", "True");
            actions.filter( '[value="' + filter.actions[i].type + '"]' ).val( filter.actions[i].type )
            .siblings( '[name="actionParameter[]"]' ).val( filter.actions[i].parameter );
        }
    }	
	isExact.filter('[value="'+(filter.isExact != "false"? "and" : "or")+'"]').attr("checked", "True");

	if (filter.alertMessage == 'true') $('.alertMessage').attr('checked', 'True');
	if (filter.verifyNextRule == 'true') $('.verifyNextRule').attr('checked', 'True');
}


DataLayer.codec( "filter", "detail", {

  decoder: function( form ){
	  if( form.vacation )
	  return {
		criteria: [{ value: "vacation", operator: "", field: "vacation" }],
		actions: [{ parameter: form.vacation, type: "vacation" }],
		id: "vacation",
		name: "vacation",
		isExact: false,
		applyMessages : "",
		enabled : true
      }

	var apply_messages_ = keys(selectedMessages);

	action = '';

    return {
		name: form.name ? form.name.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : nameObj.val().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'),
		isExact: ( form.isExact === "and" ),
		criteria: $.map( form.criteriaOperator || [], function( criteriaOperator, i ){
			return (!form.criteriaValue[i]) ? null:
			{
					value:  form.criteriaValue[i].replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'),
					operator: criteriaOperator,
					field:  form.criteriaType[i].replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
			};
		}),
		actions: $.map( form.actionType || [], function( type, i ){
			var the_parameter = form.actionParameter[i];

			!the_parameter ? the_parameter = form.actionParameter[i+1] : "";

			if (type == 'alertMessage') { 
				//if(!$('[value="alertMessage"]').parent().hasClass("hidden")){
					type = 'addflag';
					the_parameter = '$FilteredMessage';
				//}else{
				//	return;
				//}
			} 
			if (type == 'fileinto') { 
				the_parameter = form.valueFileInto;
			}
			if (type == 'reject') {
				the_parameter = form.messageReject;
			}
			if (type == 'fileintoInbox') {
				if(!$('[value="fileintoInbox"]').parent().hasClass("hidden")){
					type = 'fileinto';
					the_parameter = 'INBOX';
				}else{
					return;
				}
			}
			if (type == 'fileintoTrash') {
				type = 'fileinto';
				the_parameter = 'INBOX' + cyrus_delimiter + 'Trash';
			}
			if (type == 'redirect') {
				the_parameter = form.addressRedirect;
			}
			if (type == 'setflag') {
				the_parameter = form.valueSetFlag;
				action = form.valueSetFlag;
			}
			return (!type) ? null:
			      {parameter: the_parameter, type: type };

		}),
		enabled : true,
		alertMessage: $('.alertMessage').is(':checked'),
		verifyNextRule: $('.verifyNextRule').is(':checked'),
		//applyMessages: allMessages? !$.isEmptyObject( selectedMessages ) ?
		//keys( selectedMessages ) : allMessages : ""
		applyMessages: apply_messages_ 
    };
  },

  encoder: function( filters ){
	var rules = [];

	for( var id in filters )
	      rules[ rules.length ] = filters[id];

	return { rules: rules };

  }

});

/* Codec do datalayer */
DataLayer.codec( "folder", "select", {

  decoder:function(){

  },
  encoder:function( data ){

      var tree1 = [], tree2 = [], tree3 = [];

      for (var i=0; i<data.length; i++) {

	      if (/^INBOX/.test(data[i].id)) {
		      if (!unorphanize(tree1, data[i])) {
			      data[i].children = [];
			      tree1.push(data[i]);
		      }
	      }
	      else if (/^user/.test(data[i].id)) {
		      if (!unorphanize(tree2, data[i])) {
			      data[i].children = [];
			      tree2.push(data[i]);
		      }
	      }
	      else if (/^local_messages/.test(data[i].id)) {
		      if (!unorphanize(tree3, data[i])) {
			      data[i].children = [];
			      tree3.push(data[i]);
		      }
	      }

      }

      return {folders: [ tree1, tree2, tree3 ]};

  }

});

if(preferences['use_alert_filter_criteria'] == "1")
{
    fromRules = getFromAlertRules();
}

var BASE_PATH = '../prototype/';

DataLayer.basePath = BASE_PATH + "REST.php?q=";
DataLayer.dispatchPath = BASE_PATH;

var selectedMessages = {};
var allMessages = false;

/* 
 * Bloqueia usuário por email - utilizado na tela de mensagens do ExpressoMail
 */
function block_user_email(email) {
	delete selectedMessages;
	var idd = urlencode(email);
	/** TODO - Mudar quando API abstrair atualizações no cache */
	DataLayer.remove('filter', false);
	var filters = DataLayer.get('filter');
	for(var index in filters){
		if(filters[index].name == email) {
			if(confirm(get_lang("This user is already blocked. Would you like to unlock it?"))) {
				DataLayer.remove( 'filter', filters[index].id );
				DataLayer.commit("filter", false, function(){
					$.Zebra_Dialog(get_lang("Lock User") + " " + email + " " + get_lang("removed"),{
                        'overlay_opacity': '0.5',
                        'custom_class': 'custom-zebra-filter'
                    });
					list_filters();
					getFromAlertRules();
				});
				return true;
			}else
				return true;
		}
	}
	if(confirm(get_lang("Want to block the user") + " " + email + "?")){
        var has_folder_spam = false;
        var folder_spam = "INBOX"+cyrus_delimiter+"Spam";
        $.each(cp_tree1, function(index, value){
            if(value.id == folder_spam){
                has_folder_spam = true;
                return;
            }
        });
        if(!has_folder_spam){
            create_new_folder("Spam","INBOX");
        }
		DataLayer.put( 'filter', idd,
					{ name: email, isExact: false,
						criteria:{ 0: {value: email, operator: "=", field: "from"} },
						actions: { 0: {parameter: folder_spam, type: "fileinto"}},
						enabled: true,
						id: email,
						block: true
					});
		DataLayer.commit("filter", false, function(){
			$.Zebra_Dialog(get_lang("User") + " " + email + " " + get_lang("blocked"),{
                'overlay_opacity': '0.5',
                'custom_class': 'custom-zebra-filter'
            });
			list_filters();
			getFromAlertRules();
		});

	}
}


DataLayer.codec( 'message', 'jqGrid', {

      encoder: function( data ){

	  return( data );

      }
});

DataLayer.codec( 'message', 'jqGridSearch', {

      encoder: function( data ){

	  return( data );

      }
});

/* Gerencia o grid das mensagens da caixa de entrada 
 * Mostra o grid de mensagens para aplicar a regra nas mensagens da caixa de entrada.
 */
function showGridMessages(thiss) {
	var criteria_list = list_container.find('[name="criteriaValue[]"]');
	var criteria_operator_list = list_container.find('[name="criteriaOperator[]"]');
	var criteria_value = "";
	var criteria = "";
	$.each(criteria_list, function(index, value){
		criteria += $.trim($(criteria_list[index]).val()) + ",";
	});
	var criteria_ = criteria.split(",");
	var criteria_operator  = "";
	$.each(criteria_operator_list, function(index, value){
		criteria_operator += $.trim($(criteria_list[index]).val()) != "" ? index + "," : ",";
		criteria_value    += $.trim($(criteria_operator_list[index]).val()) + ",";
	});
	var criteria_operator_  = criteria_operator.split(",");
	var criteria_value_     = criteria_value.split(",");
	var options = ["from", "to", "subject", "body", "size"];
	var criterias_ = new Array();

    var isExact = (list_container.find('[name="isExact"]:checked').val() == 'and') ? 'yes' : 'no';

	for (i in criteria_)
		if(criteria_[i] != "")
		    criterias_.push( criterias_.length ?  {"0": "OR", "1": options[i], "2": criteria_value_[i], "3": criteria_[i]} : {"0": options[i], "1": criteria_value_[i], "2": criteria_[i]} );

	showGridButton = thiss.parent();

	showGridButton.siblings("#filtergrid-container").block({
							message: '<div id="loading-content"><div class="image"></div></div>',
							css: {
								backgroundImage: 'url('+BASE_PATH+'modules/attach_message/images/loading.gif)',
								backgroundRepeat: 'no-repeat',
								backgroundPosition: 'center',
								backgroundColor: 'transparent',
								width: '32px',
								height: '32px',
								border:'none'
							},
							overlayCSS: {
								backgroundColor: '#CCC',
								opacity:         0.5
							}
						});

	var data = DataLayer.get( 'message', { filter: criterias_, criteria: { isExact: isExact , properties: { context: { folder: 'INBOX' } } } }, true );
	if(DataLayer.criterias['message:jqGrid']){
		delete DataLayer.criterias['message:jqGrid'];	
	}

	DataLayer.register( 'criterias', 'message:jqGrid', function( crit ){

		    crit.properties = { context: { folder: 'INBOX' } };

		    return { filter: [ "msgNumber", "IN", data ], criteria: crit };
	});

	allMessages = data;

	if (typeof grid !== "undefined") {
		grid.jqGrid( 'setGridParam', { url: 'message:jqGrid', postData: data } ).trigger( 'reloadGrid' );
		//return;
	}
	grid = showGridButton.siblings("#filtergrid-container").removeClass('empty-container')
	.html('<table class="hidden fields-search-messages-grid" id="fields-search-messages-grid"><tr><td/></tr></table><div class="hidden fields-search-messages-grid-pager" id="fields-search-messages-grid-pager"></div>')
	.children(".fields-search-messages-grid, .fields-search-messages-grid-pager").removeClass('hidden').filter(".fields-search-messages-grid").trigger( 'reloadGrid' ).jqGrid({
		url: 'message:jqGrid',
		datatype: "json",
		mtype: 'GET',
		colNames:['#', 'De', 'Assunto', 'Data', 'Tamanho'],
		colModel:[
			{name:'msg_number',index:'msg_number', width:45, hidden:true, sortable:false},
			{name:'from.email',index:'msg_number', width:130, sortable:false},
			{name:'subject',index:'subject', width:250, sortable:false},
			{name:'timestamp',index:'timestamp', width:65, align:"center", sortable:false, formatter:date2Time},
			{name:'size',index:'size', width:50, align:"right", sortable:false, formatter:bytes2Size}
		],
		jsonReader : {
			root:"rows",
			page: "page",
			total: "total",
			records: "records",
			repeatitems: false,
			id: "0"
		},
		rowNum:10,
		//rowList:[10,25,50],
		rowList:[10],
		pager: '#fields-search-messages-grid-pager',
		sortname: 'id',
		viewrecords: true,
		sortorder: "desc",
		multiselect: true,
		autowidth: true,
		reloadAfterEdit: true,
		reloadAfterSubmit: true,
		height:200,
		loadComplete: function(data) {
			// aplica o contador
			jQuery('.timable').each(function (i) {
				jQuery(this).countdown({
					since: new Date(parseInt(this.title)),
					significant: 1,
					layout: 'h&aacute; {d<}{dn} {dl} {d>}{h<}{hn} {hl} {h>}{m<}{mn} {ml} {m>}{s<}{sn} {sl}{s>}',
					description: ' atr&aacute;s'
				});
			});
		},
		onSelectRow: function (id, selected) {
			if( selected )
			{
				selectedMessages[ id ] = true;
			}
			else
			{
				delete selectedMessages[ id ];
			}
		},
		onSelectAll: function (id, selected) {
			for (i in id) {
				if(selected)
					selectedMessages[id[i]] = true;
				else
					delete selectedMessages[id[i]];
			}
		},
		caption: 'Mensagens que atendem aos crit&eacute;rios'
	}); // end jqGrid
}

/* Gerencia a listagem de filtros do usuário */
/* 
 * Lista os filtros para o usuário
 */
function list_filters(html) { 
	outOfficeVerify();
	for (var index in selectedMessages) 
	{
		delete selectedMessages[index];
	}
 
	initialize_container(html); // Inicializa o container do diálogo de filtros 
 
	list_container = create_filter_dialog(); // Cria a estrutura básica do diálogo 
	
	var list = DataLayer.render( BASE_PATH + 'modules/filters/filter-list.ejs', DataLayer.get("filter:detail", true));
	list_container.html(list).find(".button").button();
    list_container.find(".alldelete").button("disable");
    list_container.find(".alldisable").button("disable");
    list_container.find(".allenable").button("disable");
    
	list_container.find(".rule-list").selectable({
		selecting: function(event, ui) {
			$(ui.selecting).find(':checkbox').attr('checked', true);
		},
		unselecting: function(event, ui) {
			$(ui.unselecting).find(':checkbox').attr('checked', false);
		}
	});

    list_container.find(".rule-list li").click(function(){
        if(list_container.find(".select").children("input:checked").length){
            list_container.find(".alldelete").button("enable");
            list_container.find(".alldisable").button("enable");
            list_container.find(".allenable").button("enable");
        } else {
            list_container.find(".alldelete").button("disable");
            list_container.find(".alldisable").button("disable");
            list_container.find(".allenable").button("disable");
        }
    });

	list_container.find( ".menu-control .button" ).filter(".update").button({
		icons: {
			primary: "ui-icon-pencil"
		},
		text: false
	}).click(function(){
		var id = $(this).parents("li.rule").find('.id').val();
		var filters = DataLayer.get( 'filter', true);
		for(var i =0; i < filters.length; i++){
			if(filters[i].id == id){
				filters = filters[i];
				break;
			}
		}
		DataLayer.render( BASE_PATH + 'modules/filters/edit-filter.ejs', {folders : DataLayer.get("folder", true), delimiter: cyrus_delimiter},function(html){
			var details_container = $(".expresso-window-filters").html(html);
			if(filters.name != "vacation"){
				form = container.find("form");
				criterias = details_container.find( 'fieldset input[name="criteriaType[]"]' );
				actions = details_container.find( 'fieldset input[name="actionType[]"]' );
				nameObj = details_container.find( 'input[name="name"]' );
				isExact = details_container.find( 'input[name="isExact"]' );
				showDetails(filters);
				var accord = list_container.find(".rule-details-container").accordion({autoHeight: false});
				list_container.find(".button").button().filter(".forth").click(function(){
					accord.accordion('activate',1);
				}).end().filter(".forth2").click(function(){
					accord.accordion('activate',2);
				}).end().filter(".back").click(function(){
					accord.accordion('activate',0);
				}).end().filter(".back2").click(function(){
					accord.accordion('activate',1);
				}).end().filter(".cancel").click(function(){
					DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
				}).end().filter(".submit").click(function(){
					if(nameObj)
						nameObj.attr("disabled", "false");
					if(valid_save())
						$(this).submit();
					else
						return;
					DataLayer.commit( 'filter', false,function(data){
						if(filters['alertMessage'] == "true"){
							if(filters['alertMessage'] != $('.alertMessage').is(':checked').toString()){
								/**RETIRA FLAG*/
								removeMessagesFlag(filters['id']);
							}
						}
						if($('#gbox_fields-search-messages-grid').length > 0 && action != '') {
							for (var index in selectedMessages) {
								set_message_flag(index, action, false);
							}
						}
						DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
						getFromAlertRules();
						if (get_current_folder() == "INBOX")
							cExecute ("$this.imap_functions.get_range_msgs2&folder=INBOX&msg_range_begin=1&msg_range_end="+preferences.max_email_per_page+"&sort_box_type=SORTARRIVAL&search_box_type=ALL&sort_box_reverse=1", handler_draw_box);
					});

				}).end().filter('.button.search').click(function() {
					/* Valida se o usuário preencheu as informações */
					if(valid_save())
						showGridMessages($(this));
					else
						return;
				})
			}else{
				list_container.find(".vacation-details-container").removeClass("hidden");
				list_container.find(".rule-details-container").addClass("hidden");
				list_container.find(".button").button().filter(".back").click(function(){
					DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
				}).end().filter(".submit").click(function(){
					if(list_container.find(".vacation-details-container .filter-textarea").val().length <= 0){
						$.Zebra_Dialog(get_lang('Write a message'),{
                            'overlay_opacity': '0.5',
                            'custom_class': 'custom-zebra-filter'
                        });
						list_container.find(".vacation-details-container .filter-textarea").focus();
					}else{
						$(this).submit();
						DataLayer.commit( 'filter',false,function(){
							DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
							getFromAlertRules();
						});
					}
				});
				details_container.find('[name="vacation"]').val(filters.actions[0].parameter);
			}
		});
	}).end()
	.filter(".enable").button({
		icons: {
			primary: "ui-icon-circle-close"
		},
		text: false
	}).click(function(){
		var id = $(this).parents("li.rule").find('.id').val();
		DataLayer.put( 'filter', id, { enabled: false  });
		/**RETIRA FLAG*/
		removeMessagesFlag(id);
		DataLayer.commit("filter", false,function(){
			DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
			getFromAlertRules();
		});
	}).end()

	.filter(".disable").button({
		icons: {
			primary: "ui-icon-circle-check"
		},
		text: false
	}).click(function(){
		var id = $(this).parents("li.rule").find('.id').val();
		DataLayer.put( 'filter', id, { enabled: true });
		DataLayer.commit("filter", false,function(){
			DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
			getFromAlertRules();
		});
	}).end()

	.filter(".close").button({
		icons: {
			primary: "ui-icon-close"
		},
		text: false
	}).click(function(event){
		var filter_name = $(event.target).parents("li.rule").find('.id').val();
		var filter_screen_name = $(event.target).parents("li.rule").find('.title').html();
		$.Zebra_Dialog(get_lang("Are you sure to delete the filter") + ": <strong>"+filter_screen_name+"</strong>",{
			'type':     'question',
			'title':    get_lang("Exclusion Confirmation"),
			'buttons':  [get_lang('Yes'), get_lang('No')],
            'overlay_opacity': '0.5',
            'custom_class': 'custom-zebra-filter',
			'onClose':  function(caption) {
				if(caption == get_lang('Yes')){
				/** TODO - Mudar quando API abstrair atualizações no cache */
					DataLayer.remove('filter', false);
					DataLayer.get('filter');
					/**RETIRA FLAG*/
					removeMessagesFlag(filter_name);
					DataLayer.remove( 'filter', filter_name);

					DataLayer.commit("filter", false,function(){
						DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
						getFromAlertRules();
					});
				}else{
					$(event.target).removeClass("ui-selected").parent().removeClass("ui-selected");
				}
			}
		});
	}).end().removeClass("ui-button-icon-only");

	$(".button.allenable").click(function(){
        container.find('.rule-list').find('.rule').find('.select').find(':checked').parents('.rule').find('input.id').each(function(i,o){
			DataLayer.put( 'filter', o.value, { enabled: true  });
		});
		DataLayer.commit("filter", false, function(){
			DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
			getFromAlertRules();
		});
	});

	$(".button.alldisable").click(function(){
        container.find('.rule-list').find('.rule').find('.select').find(':checked').parents('.rule').find('input.id').each(function(i,o){
			DataLayer.put( 'filter', o.value, { enabled: false  });
			/**RETIRA FLAG*/
			removeMessagesFlag(urlencode(o.innerHTML));
		});
		DataLayer.commit("filter", false, function(){
			DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
			getFromAlertRules();
		});
	});

	$(".button.alldelete").click(function(){
		$.Zebra_Dialog(get_lang("Are you sure to delete the filters?"),{
			'type':     'question',
			'title':    get_lang("Exclusion Confirmation"),
			'buttons':  [get_lang('Yes'), get_lang('No')],
            'overlay_opacity': '0.5',
            'custom_class': 'custom-zebra-filter',
			'onClose':  function(caption) {
				if(caption == get_lang('Yes')){
				/** TODO - Mudar quando API abstrair atualizações no cache */
					DataLayer.remove('filter', false);
					DataLayer.get('filter');
					container.find('.rule-list').find('.rule').find('.select').find(':checked').parents('.rule').find('input.id').each(function(i,o){
						/**RETIRA FLAG*/
						removeMessagesFlag(urlencode(o.innerHTML));
						//filter_name = urlencode(o.innerHTML.replace(".", "_"));
						filter_name = o.value;
						DataLayer.remove( 'filter', filter_name );
					});
					DataLayer.commit("filter", false, function(){
						DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
						getFromAlertRules();
					});
				}else{
					return true;
				}
			}
		});
	});

	var aa = 0;

	list_container.parent().find(".button.add:first").click(function(){
		render_new_rule(); // Renderiza a tela de criação de nova regra de filtragem. 
	});
	list_container.parent().find(".button.add.vacation").click(function(){

		list_container.parent().find(".dialog-head-buttonpane").addClass("hidden");
		DataLayer.render( BASE_PATH + 'modules/filters/edit-filter.ejs', {folders : DataLayer.get("folder", true), delimiter: cyrus_delimiter},function(html){
			list_container.html(html);
			list_container.find(".vacation-details-container").removeClass("hidden");
			list_container.find(".rule-details-container").addClass("hidden");
			list_container.find(".button").button().filter(".back").click(function(){
				DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
			}).end().filter(".submit").click(function(){
				if(list_container.find(".vacation-details-container .filter-textarea").val().length <= 0){
						$.Zebra_Dialog(get_lang('Write a message'),{
                            'overlay_opacity': '0.5',
                            'custom_class': 'custom-zebra-filter'
                        });
						list_container.find(".vacation-details-container .filter-textarea").focus();
				}else{
					$(this).submit();
					DataLayer.commit( 'filter',false,function(){
						DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {},list_filters);
						getFromAlertRules();
					});
				}
			});
		});
	});
}

/* 
	Inicializa o container do diálogo de gerenciamento de filtros. 
*/ 
function initialize_container (html) { 
	if(html) 
	{ 
		if(!$(".filters-windows").length)  
		{ 
			container = $('.expressomail-module-container').append("<div class='filters-windows'></div>").find(".filters-windows").html(html).find(".expresso-window-container"); 
		} 
	} 
} 
 
/* 
	Cria a estrutura básica do diálogo de gerenciamento de filtros. 
*/ 
function create_filter_dialog () { 
	var dialog = $(".expresso-window-filters").dialog( 
	{ 
		title: get_lang('Filters'), 
		width: 700, 
		modal: true, 
		resizable: false, 
		// closeOnEscape: false, 
		// close: function(event, ui) 
		// { 
		// 	event.stopPropagation(); 
		// 	if(list_container.find(".cancel").length) list_container.find(".cancel").trigger('click'); 
		// 	$(".dialog-head-buttonpane").hide(); 
		// }, 
		// open: function() 
		// { 
		// 	$(".ui-dialog .ui-dialog-titlebar").append('<a href="#" class="ui-dialog-titlebar-minimize ui-corner-all" role="button"><span class="ui-icon ui-icon-minusthick">minimize</span></a>').find('.ui-dialog-titlebar-minimize').click(function() 
		// 	{ 
		// 		$(".ui-dialog-buttonpane, .ui-dialog-content").toggle(); 
		// 		$(".ui-icon-minusthick, .ui-icon-newwin").toggleClass('ui-icon-minusthick').toggleClass('ui-icon-newwin'); 
		// 	}); 
		// 	$(".dialog-head-buttonpane").show(); 
		// }, 
		autoOpen: false, 
		buttons: [ 
		{ 
			text: get_lang("Close"), 
			click: function() 
			{ 
				$(this).dialog("destroy"); 
			} 
		}] 
	}); 
 
	//$(".ui-dialog-titlebar").find("span").

	$(".ui-dialog-titlebar").after("<div class='dialog-head-buttonpane ui-dialog-buttonpane ui-widget-content ui-helper-clearfix' style='background-color: rgb(224, 238, 238); '><div class='ui-dialog-buttonset header-buttonpane'></div></div>"); 
	$(".dialog-head-buttonpane").css("padding", "5px"). 
	find(".header-buttonpane").html("<a href='#' class='button add' title='" + get_lang("Add new rule") + "'>" + get_lang("New rule") + "</a>" + (!outoffice ? "<a href='#' class='button add vacation' title='" + get_lang("Add rule out of office") + "'>" + get_lang("Out of office") + "</a>" : "")).find(".button").button(); 
	$(".ui-dialog-buttonpane.ui-widget-content").css("background-color", "#E0EEEE"); 
 
	return dialog; 
} 
 
/* 
	Renderiza a tela de adição de uma nova regra de filtragem,  
	dentro do diálogo de gerenciamento de filtros. 
*/ 
function render_new_rule (from, subject) { 
	list_container.parent().find(".dialog-head-buttonpane").addClass("hidden"); 
	var data = { 
		folders: DataLayer.get("folder", true), 
		delimiter: cyrus_delimiter, 
		from: from, 
		subject: subject ? html_entities(subject) : subject 
	} 
 
	DataLayer.render(BASE_PATH + 'modules/filters/edit-filter.ejs', data, function(html) 
	{ 
		list_container.html(html); 
		var accord = list_container.find(".rule-details-container").accordion( 
		{ 
			autoHeight: false 
		}); 
		list_container.find(".button").button().filter(".forth").click(function() 
		{ 
			accord.accordion('activate', 1); 
		}).end().filter(".forth2").click(function() 
		{ 
			accord.accordion('activate', 2); 
		}).end().filter(".back").click(function() 
		{ 
			accord.accordion('activate', 0); 
		}).end().filter(".back2").click(function() 
		{ 
			accord.accordion('activate', 1); 
		}).end().filter(".cancel").click(function() 
		{ 
			DataLayer.render(BASE_PATH + 'modules/filters/init.ejs', {}, list_filters); 
		}).end().filter(".submit").click(function() 
		{ 
			if(valid_save()) $(this).submit(); 
			else return; 
			DataLayer.commit('filter', false, function() 
			{ 
				if($('#gbox_fields-search-messages-grid').length > 0 && action != '') 
				{ 
					for(var index in selectedMessages) 
					{ 
						set_message_flag(index, action, false); 
					} 
				} 
				DataLayer.render(BASE_PATH + 'modules/filters/init.ejs', {}, list_filters); 
				getFromAlertRules(); 
			}); 
		}).end().filter('.button.search').click(function() 
		{ 
			if(valid_save()) showGridMessages($(this)); 
			else return; 
		}); // end function click 
	}); // end DataLayer.render 
}

/* Inicializa os filtros e chama o list_filters 
 * Inicia a funcionalidade de filtros de mensagens
 */
function  init_filters(){
	var html = DataLayer.render( BASE_PATH + 'modules/filters/init.ejs', {});
	list_filters(html);
}

/*
 * Ao excluir filtro, desabilitar filtro ou retirar a ação Alerta de um filtro
 * esta função é chamada para retirar as flags que caracterizam uma mensagem como alertada pelos Filtros por Remetente 
 */
function removeMessagesFlag(id){
	var filters_c = DataLayer.get('filter', urlencode(id));
	var folder = '';
	var from = '';	
	if(filters_c['actions'])
	for(var i=0; i < filters_c['actions'].length; i++){
		if(filters_c['actions'][i].type == 'fileinto'){
			folder = filters_c['actions'][i].parameter;
		}
	}

    if(preferences['use_alert_filter_criteria'] == "1")
    {
        $.each(fromRules, function(index, value) {
            if(value == folder){
                for(var i=0; i < filters_c['criteria'].length; i++){
                    if(filters_c['criteria'][i].field == 'from'){
                        from = filters_c['criteria'][i].value;
                    }
                }
                cExecute ("$this.imap_functions.removeFlagMessagesFilter&folder="+folder+"&from="+from, function(){});
                return false;
            }
        });
    }
}