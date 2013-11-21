countFiles = 1;
function copy_manager(manager)
{
	if (!(new_manager = prompt(get_lang('Type new managers login') + ':',"")))
	{
		return;
	}

	var handler_copy_manager = function(data)
	{
		if (data)
			location.reload();
		else
			alert(get_lang('Error at copy manager') + '.');
	}
	cExecute ('$this.db_functions.copy_manager&manager='+manager+'&new_manager='+new_manager, handler_copy_manager);
}

function add_input_context()
{
	var new_context_value = document.getElementById("ea_select_contexts").value;
	children = document.getElementById("td_input_context").getElementsByTagName("input");
	
	for (var i=0; i<children.length; i++)
	{
		if (new_context_value.indexOf(children[i].value) != -1)
		{
			document.getElementById("ea_spam_warn").innerHTML = get_lang('Context already added or redundant') + '.';
			setTimeout("document.getElementById(\"ea_spam_warn\").innerHTML = '&nbsp;'", 4000);
			return;
		}
		
		if (children[i].value.indexOf(new_context_value) != -1)
		{
			children[i].parentNode.parentNode.removeChild(children[i].parentNode);
			--i;
			document.getElementById("ea_spam_warn").innerHTML = get_lang('Removed context redundant') + '.';
			setTimeout("document.getElementById(\"ea_spam_warn\").innerHTML = '&nbsp;'", 4000);
		}
	}

	var div = document.createElement("DIV");
	
	var input = document.createElement("INPUT");
	input.size = 60;
	input.disabled = true;
	input.value = document.getElementById("ea_select_contexts").value;
	
	var span = document.createElement("SPAN");
	span.innerHTML = " -";
	span.style.cursor = "pointer";
	span.onclick = function(){ this.parentNode.parentNode.removeChild(this.parentNode); };
	
	div.appendChild(input);
	div.appendChild(span);
	document.getElementById("td_input_context").appendChild(div);
}

function validade_managers_data(type)
{
	var contexts = '';
	var input_context_fields = document.getElementById('td_input_context').getElementsByTagName("input");
	for (var i=0; i<input_context_fields.length; i++)
	{
		if ((input_context_fields[i].nodeName === 'INPUT') && (input_context_fields[i].value != ''))
		{
			contexts += input_context_fields[i].value + '%';
		}
	}
	
	//Salvo parao Post
	document.managers_form.context.value = contexts.substring(0,contexts.length-1);
	contexts = 	encodeURIComponent(contexts.substring(0,contexts.length-1));

	var handler_validade = function(data)
	{
		if (data.status == 'false')
		{
			alert(data.msg);
			return;
		}
		else
		{
		var old_url_context = document.createElement("INPUT");
		old_url_context.type = "hidden";
		old_url_context.name = "old_url_context";
		old_url_context.value = $("input[name=old_url_context_aux]").val();
		
		$(document.forms[0]).find("div").append(old_url_context);
		
			if (type == 'add')
				cExecuteForm ("$this.manager.create", document.forms[0], handler_createsave_manager);
			else
				cExecuteForm ("$this.manager.save", document.forms[0], handler_createsave_manager);
		}
	};
	
	cExecute ('$this.manager.validate&contexts='+contexts+'&manager_lid='+document.managers_form.ea_select_manager.value+'&type='+type, handler_validade);
}
function handler_createsave_manager(data){
	return_handler_createsave_manager(data);
}
function return_handler_createsave_manager(data)
{
	if (data.status == 'false')
	{
		alert(data.msg);
	}
	else
	{
		if (data.type == 'create')
			alert(get_lang('User successful created') + '.');
		else
			alert(get_lang('Manager successful saved') + '.');
	}
	location.href="./index.php?menuaction=expressoAdmin1_2.uimanagers.list_managers";
	return;
}

var searchTimeout;
function search_manager(manager_lid, event)
{
// 	clearTimeout(searchTimeout);
// 	
// 	var spam = document.getElementById('ea_span_searching_manager');
// 	if (manager_lid.length <= 3)
// 		spam.innerHTML = get_lang('Type more') + ' ' + (4 - manager_lid.length) + ' ' + 'letters' + '.';
// 	else
// 	{
// 		spam.innerHTML = get_lang('Searching') + '...';
// 		searchTimeout = setTimeout("search_user('"+manager_lid+"')",750);
// 	}
	if( event && event.keyCode !== 13 )
	    return( true );
	
	search_user( manager_lid );

	return( false );
}

function search_user(search)
{
// 	var handler_search_manager = function(data)
// 	{
// 		var spam = document.getElementById('ea_span_searching_manager');
// 		select_available_users = document.getElementById('ea_select_managers');
// 		
// 		if (data.status == 'false')
// 		{
// 			spam.innerHTML = data.msg;
// 			// Necessario, pois o IE6 tem um bug que retira o primeiro options se o innerHTML estiver vazio.
// 			select_available_users.innerHTML = '#';
// 			select_available_users.outerHTML = select_available_users.outerHTML;
// 			return;
// 		}
// 		
// 		spam.innerHTML = '';
// 		// Necessario, pois o IE6 tem um bug que retira o primeiro options se o innerHTML estiver vazio.
// 		select_available_users.innerHTML = '#' + data;
// 		select_available_users.outerHTML = select_available_users.outerHTML;
// 	}
// 	
// 	cExecute ('$this.ldap_functions.search_user&search='+search, handler_search_manager);

	var url = '$this.ldap_functions.search_user&search='+search;
	
// 	var fillHandler = function( data )
// 	{
// 	    Element( 'ea_select_managers' ).innerHTML = data;
// 
// 	    return( data !== "" );
// 	}
// 
// 	userFinder( search, fillHandler, url, 'ea_span_searching_manager' );
	
	optionFind( "manager_lid", 'ea_select_managers', url,  false, 'ea_span_searching_manager' );
}

function select_all_acls(parent)
{
	var acls = document.getElementById(parent).getElementsByTagName("input");
	for (var i=0; i<acls.length; i++)
	{
		acls[i].checked = true;
	}

}
