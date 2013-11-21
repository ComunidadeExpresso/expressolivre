countFiles = 0;
function validate_fields(type)
{
	document.forms[0].uid.value = document.forms[0].uid.value.toLowerCase();
	document.forms[0].old_uid.value = document.forms[0].old_uid.value.toLowerCase();
	
	if (document.forms[0].uid.value == ''){
		alert('Campo LOGIN da lista está vazio.');
		return;
	}

/*	if (document.forms[0].cn.value == ''){
		alert('Campo NOME da lista está vazio.');
		return;
	}*/
	
	if (document.forms[0].restrictionsOnEmailLists.value == 'true')
	{
		uid_tmp = document.forms[0].uid.value.split("-");
		if ((uid_tmp.length < 3) || (uid_tmp[0] != 'lista')){
			alert(
				'O campo LOGIN da lista está incompleto.\n' +
				'O nome da lista deve ser formado assim:\n' +
				'lista-ORGANIZACAO-NOME_DA_LISTA.\n' +
				'Ex: lista-serpro-rh.');
			return;
		}
	}
		
	if (document.forms[0].uid.value.split(" ").length > 1){
		alert('Campo LOGIN comtém espaços.');
		document.forms[0].uid.focus();
		return;
	}

	//Verifica se a pagina de origem e a de edicao ou criacao de lista, pois o atributo listPass e usado apenas nestas paginas;
	if((type == 'edit_maillist') || (type == 'create_maillist')) {
		if (document.forms[0].listPass.value == ''){
			alert('Campo SENHA da lista está vazio.');
			document.forms[0].listPass.focus();
			return;
		}
	}
	
/*	if (document.forms[0].mail.value == ''){
		alert('Campo E-MAIL da lista está vazio.');
		document.forms[0].mail.focus();
		return;
	}
	var reEmail = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if(!reEmail.test(document.forms[0].mail.value)){
		alert("Campo E-mail não é válido.");
		return false;
	}
*/
// Alteracao para saber de onde vem a chamada do save() - se da pagina de edicao de lista ou da pagina de administrador da lista
	if((type == 'edit_maillist') || (type == 'create_maillist')) { // pagina de edicao da lista
		select_userInMaillist = document.getElementById('ea_select_usersInMaillist');
		if (select_userInMaillist.options.length == 0){
			alert('Nenhum usuário faz parte da lista.');
			return;
		}
		
	/*}else if(type == 'adm_maillist') { // pagina de administador da lista
		select_ADM_Maillist = document.getElementById('ea_select_ADM_Maillist');
		if (select_ADM_Maillist.options.length == 0){
			alert('Nenhum usuário faz parte da lista.');
			return;
		}
	*/	
	}

/* Codigo original preservado, em caso de necessidade

	select_userInMaillist = document.getElementById('ea_select_usersInMaillist');
	if (select_userInMaillist.options.length == 0){
		alert('Nenhum usuário faz parte da lista.');
		return;
	}

*/
	var handler_validate_fields = function(data)
	{
		if (!data.status)
			alert(data.msg);
		else
		{
			if (type == 'create_maillist')
				cExecuteForm ("$this.maillist.create", document.forms[0], handler_create);
			else if (type == 'edit_maillist')
				cExecuteForm ("$this.maillist.save", document.forms[0], handler_save);
			//else if (type == 'adm_maillist')
			//	cExecuteForm ("$this.maillist.save_adm", document.forms[0], handler_save);
		}
	}

// Alteracao semelhante a descrita acima
	if((type == 'edit_maillist') || (type == 'create_maillist')) {
		for(var i=0; i<select_userInMaillist.options.length; i++)
			select_userInMaillist.options[i].selected = true;

	/*}else if(type == 'adm_maillist') {
		for(var i=0; i<select_ADM_Maillist.options.length; i++)
			select_ADM_Maillist.options[i].selected = true;
*/
	}

/* Codigo original preservado, em caso de necessidade

	// Needed select all options from select
	for(var i=0; i<select_userInMaillist.options.length; i++)
		select_userInMaillist.options[i].selected = true;
*/

	// O UID da lista foi alterado ou é uma nova lista.
	if ((document.forms[0].old_uid.value != document.forms[0].uid.value) || (type == 'create_maillist')){
		cExecute ('$this.maillist.validate_fields&uid='+document.forms[0].uid.value, handler_validate_fields);
	}
	else if (type == 'edit_maillist')
	{
		cExecuteForm ("$this.maillist.save", document.forms[0], handler_save);
	}
	//else if (type == 'adm_maillist')
	//{
	//	cExecuteForm ("$this.maillist.save_adm", document.forms[0], handler_save);
	//}
}

// HANDLER CREATE
// É necessário 2 funcões de retorno por causa do cExecuteForm.
function handler_create(data)
{
	return_handler_create(data);
}
function return_handler_create(data)
{
	if (!data.status)
		alert(data.msg);
	else{
		//Rotina alterada em 08/10/2007, para contemplar a sincronização Mailman/RHDS
		//Faz a chamada do script que atualiza as listas no Mailman/RHDS
		window.setTimeout("cExecute('$this.maillist.synchronize_mailman&uid='+document.forms[0].uid.value+'&op=1', handler_sync_mailman)", 10);
		//A nova lista foi criada com sucesso
		//alert('Lista de emails criada com êxito!');
	}
	return;
}

//Manipulador ascrescentado em 08/10/2007: para tratar da sincronização em tempo real do Mailman com o RHDS
function handler_sync_mailman(data){
	//Converte os dados do vetor oriundo de um script PHP para um vetor JavaScript
	var dados_serializados = eval(data);
	return_handler_sync_mailman(dados_serializados);
}

function return_handler_sync_mailman(data){
	//Depois de processar a sincronização do Mailman com o RHDS, redireciona o usuário para a tela inicial
	//Mesmo que não tenha tido êxito, segue o comportamento default da aplicação, redirecionando o usuário
	//EM CASO DE ERRO NA SINCRONIZAÇÃO: CONFERIR O LOG "expresso_sincronizacao_mailmanrhds.log" em "/tmp"
	//alert("Retorno do socket:\nSTATUS=" + data['status'] +'\nMSG=' + data['msg']);
	alert('Lista de emails criada com êxito!');
	location.href="./index.php?menuaction=listAdmin.uimaillists.list_maillists";		
//	alert('Teste debug!'+ data["msg"]);
}

// HANDLER SAVE
// É necessário 2 funcões de retorno por causa do cExecuteForm.
function handler_save(data)
{
	return_handler_save(data);
}
function return_handler_save(data)
{
	if (!data.status)
		alert(data.msg);
	else{

		//Alterado em 28/04/2008. Linha abaixo foi incluida para sincronizacao do Mailman com o RHDS, apos alteracao de uma
		//caracteristica nas abas Editar e Adm, em uma lista existente;
		window.setTimeout("cExecute('$this.maillist.synchronize_mailman&uid='+document.forms[0].uid.value+'&op=1', handler_sync_mailman)", 10);
	//	alert('Lista de emails salva com êxito!!');
	//	location.href="./index.php?menuaction=listAdmin.uimaillists.list_maillists";
	}
	return;
}

function save_adm()
{
	select_users_ADM_Maillist = document.getElementById('ea_select_ADM_Maillist');
	for(var i=0; i<select_users_ADM_Maillist.options.length; i++)
		select_users_ADM_Maillist.options[i].selected = true;

	cExecuteForm ("$this.maillist.save_adm", document.forms[0], handler_save_adm);
}
function handler_save_adm(data)
{
	return_handler_save_adm(data);
}

function return_handler_save_adm(data)
{
	if (!data.status)
		alert(data.msg);
	else

		//Alterado em 28/04/2008. Linha abaixo foi incluida para sincronizacao do Mailman com o RHDS, apos alteracao de uma
		//caracteristica na aba ADM, em uma lista existente;
		window.setTimeout("cExecute('$this.maillist.synchronize_mailman&uid='+document.forms[0].uid.value+'&op=1', handler_sync_mailman)", 10);
	//	alert('Sending Control List salva com êxito!!');
	//	location.href="./index.php?menuaction=listAdmin.uimaillists.list_maillists";
	return;
}

function save_scl()
{
	select_users_SCL_Maillist = document.getElementById('ea_select_users_SCL_Maillist');
	// Needed select all options from select
	for(var i=0; i<select_users_SCL_Maillist.options.length; i++)
		select_users_SCL_Maillist.options[i].selected = true;

	cExecuteForm ("$this.maillist.save_scl", document.forms[0], handler_save_scl);
}
function handler_save_scl(data)
{
	return_handler_save_scl(data);
}

function return_handler_save_scl(data)
{
	if (!data.status)
		alert(data.msg);
	else

		//Alterado em 28/04/2008. Linha abaixo foi incluida para sincronizacao do Mailman com o RHDS, apos alteracao de uma
		//caracteristica na aba SCL, em uma lista existente;
		window.setTimeout("cExecute('$this.maillist.synchronize_mailman&uid='+document.forms[0].uid.value+'&op=1', handler_sync_mailman)", 10);
	//	alert('Sending Control List salva com êxito!!');
	//	location.href="./index.php?menuaction=listAdmin.uimaillists.list_maillists";
	return;
}

function sinc_combos_org(context, recursive)
{
	combo_org_maillists = document.getElementById('ea_combo_org_maillists');

	for (i=0; i<combo_org_maillists.length; i++)
	{
		if (combo_org_maillists.options[i].value == context)
		{
			combo_org_maillists.options[i].selected = true;
			get_available_users(context, recursive);
			break;
		}
	}
}

function get_available_users(context, recursive)
{
	var handler_get_available_users = function(data)
	{
		select_available_users = document.getElementById('ea_select_available_users');
		
		//Limpa o select
		for(var i=0; i<select_available_users.options.length; i++)
		{
			select_available_users.options[i] = null;
			i--;
		}

		if ((data) && (data.length > 0))
		{
			// Necessario, pois o IE6 tem um bug que retira o primeiro options se o innerHTML estiver vazio.
			select_available_users.innerHTML = 'lixo' + data;
			select_available_users.outerHTML = select_available_users.outerHTML;
			
			select_available_users.disabled = false;
			select_available_users_clone = document.getElementById('ea_select_available_users').cloneNode(true);
			document.getElementById('ea_input_searchUser').value = '';
		}
	}
	
	//Impede chamada recursiva na raiz das organizações
	if ((recursive) && (document.forms[0].ldap_context.value == document.getElementById('ea_combo_org_maillists').value))
	{
		alert('Nao é possível selecionar todos os usuários da organização raiz.')
		document.getElementById('ea_check_allUsers').checked = false;
		
		// Limpa select
		select_available_users = document.getElementById('ea_select_available_users');
		select_available_users.innerHTML = 'lixo';
		select_available_users.outerHTML = select_available_users.outerHTML;
		return;
	}
		cExecute ('$this.ldap_functions.get_available_users_and_maillist&context='+context+'&recursive='+recursive+'&denied_uidnumber='+document.forms[0].uidnumber.value, handler_get_available_users);
}


function get_available_users_only(context, recursive) // Funcao que busca os usuarios apenas, sem as listas
{

	itemBusca = document.getElementById('ea_input_searchUser').value;


	var handler_get_available_users = function(data)
	{
		select_available_users = document.getElementById('ea_select_available_users');
		
		//Limpa o select
		for(var i=0; i<select_available_users.options.length; i++)
		{
			select_available_users.options[i] = null;
			i--;
		}

		if ((data) && (data.length > 0))
		{
			// Necessario, pois o IE6 tem um bug que retira o primeiro options se o innerHTML estiver vazio.
			select_available_users.innerHTML = 'lixo' + data;
			select_available_users.outerHTML = select_available_users.outerHTML;
			
			select_available_users.disabled = false;
//			select_available_users_clone = document.getElementById('ea_select_available_users').cloneNode(true);
			document.getElementById('ea_input_searchUser').value = '';
		}
	}

	//Impede chamada recursiva na raiz das organizações
	if ((recursive) && (document.forms[0].ldap_context.value == document.getElementById('ea_combo_org_maillists').value))
	{
		alert('Nao é possível selecionar todos os usuários da organização raiz.')
		document.getElementById('ea_check_allUsers').checked = false;
		
		// Limpa select
		select_available_users = document.getElementById('ea_select_available_users');
		select_available_users.innerHTML = 'lixo';
		select_available_users.outerHTML = select_available_users.outerHTML;
		return;
	}

		cExecute ('$this.ldap_functions.get_available_users_only&context='+context+'&filtro='+itemBusca+'&recursive='+recursive+'&denied_uidnumber='+document.forms[0].uidnumber.value, handler_get_available_users);
}


function search_users() // Funcao que busca apenas os usuarios, sem as listas
{

//	dnSearch = document.getElementById("ea_combo_org_maillists").value; //Recebe o dn para busca do usuario;
	users = document.getElementById('ea_input_searchUser').value; //Recebe qual usuario deve ser buscado;
	tipoTpl = document.forms[0].tipo.value; //Recebe a opcao de administracao de lista a partir da qual esta sendo feita a busca (Edit, Scl ou Adm);

	//Exige que o argumento de busca tenha, no minimo, 4 caracteres;
	if(users.length < 4) {

		alert('Argumento de busca deve ter no mínimo 4 caracteres.');
		return;
	}

	var handler_search_users = function(data)
	{

		select_available_users = document.getElementById('ea_select_available_users');

		//Limpa o select
		for(var i=0; i<select_available_users.options.length; i++)
		{
			select_available_users.options[i] = null;
			i--;
		}

		if ((data) && (data.length > 0))
		{
			// Necessario, pois o IE6 tem um bug que retira o primeiro options se o innerHTML estiver vazio.
			select_available_users.innerHTML = 'lixo' + data;
			select_available_users.outerHTML = select_available_users.outerHTML;

			select_available_users.disabled = false;
			select_available_users_clone = document.getElementById('ea_select_available_users').cloneNode(true);
			document.getElementById('ea_input_searchUser').value = '';
		}
	}

	cExecute ('$this.ldap_functions.search_users_only&&filtro='+users+'&tipo='+tipoTpl+'&denied_uidnumber='+document.forms[0].uidnumber.value, handler_search_users);

}

function LTrim(value)
{
	var w_space = String.fromCharCode(32);
	var strTemp = "";
	var iTemp = 0;

	var v_length = value ? value.length : 0;
	if(v_length < 1)
		return "";

	while(iTemp < v_length){
		if(value && value.charAt(iTemp) != w_space){
			strTemp = value.substring(iTemp,v_length);
			break;
		}
		iTemp++;
	}
	return strTemp;
}

function validateEmail()
{

	externalEmail = document.getElementById('ea_input_externalUser');
	if( externalEmail.value )
	{
		//var element = arguments[0];
		var validate = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		if(this.LTrim(externalEmail.value) != "" && externalEmail.value != "")
		{
			if(!validate.test(externalEmail.value))
			{
				alert('Email address is not valid.');
				externalEmail.focus();
				return false;
			}else {
				this.add_externalUser2maillist(externalEmail.value);
			}
		}
	}
}

function add_externalUser2maillist(mailAddress)
{
	input_externalUsers = mailAddress; //document.getElementById('ea_input_externalUser').value;
	select_usersInMaillist = document.getElementById('ea_select_usersInMaillist');

	var count_externalUsers = input_externalUsers.length;
	var count_usersInMaillist = select_usersInMaillist.options.length;
	var new_options = '';

	var teste = ''; //Variavel que ira receber mensagem de alerta ao usuario;
	var alerta = new Boolean(0); //Variavel que sera usada para verificar se o alerta ao usuario sera exibido ou nao;
	teste += "Usuário(os) já pertence(m) à lista:\n"; //Inicio da mensagem de alerta ao usuario;


	//Laco abaixo compara se o valor escolhido em select_available_users ja existe em select_usersInMaillist
	//se existir, adiciona o valor em teste e muda a variavel alerta para true; teste sera exibido em tela
	//apenas de alerta  true; ver if no fim da funcao;
	for(j = 0; j < count_usersInMaillist; j++)
	{
		var tmp = select_usersInMaillist.options[j].text

		if(tmp.match(input_externalUsers))
		{
			teste += input_externalUsers + "\n";
			alerta = new Boolean(1);
		}
	}

	if(alerta != true)
	{
		if(document.all)
		{
			if ( (select_usersInMaillist.innerHTML.indexOf('value='+input_externalUsers)) == '-1' )
			{
				new_options +=  "<option value="
							+ input_externalUsers
							+ ">"
							+ input_extenalUsers
							+ "</options>";
			}
		}
		else
		{		
			if ( (select_usersInMaillist.innerHTML.indexOf('value="'+input_externalUsers+'"')) == '-1' )
			{
				new_options +=  "<option value="
							+ input_externalUsers
							+ ">"
							+ input_externalUsers
							+ "</options>";
			}
		}
	}

	//Se alerta for true, exibe na tela o valor de teste;
	if(alerta == true)
	{
		alert(teste);
	}


	if (new_options != '')
	{
		select_usersInMaillist.innerHTML = 'lixo' + new_options + select_usersInMaillist.innerHTML;
		select_usersInMaillist.outerHTML = select_usersInMaillist.outerHTML;
	}

	document.getElementById('ea_input_externalUser').value = '';

}



function add_user2maillist()
{
	select_available_users = document.getElementById('ea_select_available_users');
	select_usersInMaillist = document.getElementById('ea_select_usersInMaillist');

	var count_available_users = select_available_users.length;
	var count_usersInMailList = select_usersInMaillist.options.length;
	var new_options = '';

	var teste = ''; //Variavel que ira receber mensagem de alerta ao usuario;
	var alerta = new Boolean(0); //Variavel que sera usada para verificar se o alerta ao usuario sera exibido ou nao;
	teste += "Usuário(os) já pertence(m) à lista:\n"; //Inicio da mensagem de alerta ao usuario;

	for (i = 0 ; i < count_available_users ; i++)
	{
		if (select_available_users.options[i].selected)
		{


			//Laco abaixo compara se o valor escolhido em select_available_users ja existe em select_usersInMaillist
			//se existir, adiciona o valor em teste e muda a variavel alerta para true; teste sera exibido em tela
			//apenas de alerta  true; ver if no fim da funcao;
			for(j = 0; j < count_usersInMailList; j++)
			{
				if(select_usersInMaillist.options[j].text == select_available_users.options[i].text)
				{
					teste += select_available_users.options[i].text + "\n";
					alerta = new Boolean(1);
				}
			}


			if(document.all)
			{
				if ( (select_usersInMaillist.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
				{
					new_options +=  "<option value="
								+ select_available_users.options[i].value
								+ ">"
								+ select_available_users.options[i].text
								+ "</options>";
				}
			}
			else
			{		
				if ( (select_usersInMaillist.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
				{
					new_options +=  "<option value="
								+ select_available_users.options[i].value
								+ ">"
								+ select_available_users.options[i].text
								+ "</options>";
				}
			}
		}
	}

	//Se alerta for true, exibe na tela o valor de teste;
	if(alerta == true)
	{
		alert(teste);
	}


	if (new_options != '')
	{
		select_usersInMaillist.innerHTML = 'lixo' + new_options + select_usersInMaillist.innerHTML;
		select_usersInMaillist.outerHTML = select_usersInMaillist.outerHTML;
	}
}

function remove_user2maillist()
{
	select_usersInMaillist = document.getElementById('ea_select_usersInMaillist');
	
	for(var i = 0;i < select_usersInMaillist.options.length; i++)
		if(select_usersInMaillist.options[i].selected)
			select_usersInMaillist.options[i--] = null;
}

function add_user2adm_maillist()
{
	select_available_users = document.getElementById('ea_select_available_users');
	select_ADM_Maillist = document.getElementById('ea_select_ADM_Maillist');

	var count_available_users = select_available_users.length;
	var count_ADM_MailList = select_ADM_Maillist.options.length;
	var new_options = '';


	var teste = ''; //Variavel que ira receber mensagem de alerta ao usuario;
	var alerta = new Boolean(0); //Variavel que sera usada para verificar se o alerta ao usuario sera exibido ou nao;
	teste += "Usuário(os) já pertence(m) à lista:\n"; //Inicio da mensagem de alerta ao usuario;


	for (i = 0 ; i < count_available_users ; i++)
	{
		if (select_available_users.options[i].selected)
		{


			//Laco abaixo compara se o valor escolhido em select_available_users ja existe em select_ADM_Maillist
			//se existir, adiciona o valor em teste e muda a variavel alerta para true; teste sera exibido em tela
			//apenas de alerta  true; ver if no fim da funcao;
			for(j = 0; j < count_ADM_MailList; j++)
			{
				if(select_ADM_Maillist.options[j].text == select_available_users.options[i].text)
				{
					teste += select_available_users.options[i].text + "\n";
					alerta = new Boolean(1);
				}
			}


			if(document.all)
			{
				if ( (select_ADM_Maillist.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
				{
					new_options +=  "<option value="
								+ select_available_users.options[i].value
								+ ">"
								+ select_available_users.options[i].text
								+ "</options>";
				}
			}
			else
			{		
				if ( (select_ADM_Maillist.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
				{
					new_options +=  "<option value="
								+ select_available_users.options[i].value
								+ ">"
								+ select_available_users.options[i].text
								+ "</options>";
				}
			}
		}
	}


	//Se alerta for true, exibe na tela o valor de teste;
	if(alerta == true)
	{
		alert(teste);
	}

	if (new_options != '')
	{
		select_ADM_Maillist.innerHTML = 'lixo' + new_options + select_ADM_Maillist.innerHTML;
		select_ADM_Maillist.outerHTML = select_ADM_Maillist.outerHTML;
	//	select_AdminInMaillist.outerHTML = select_AdminInMaillist.outerHTML;
	}
}

function remove_user2adm_maillist()
{
	select_ADM_Maillist = document.getElementById('ea_select_ADM_Maillist');
	
	for(var i = 0;i < select_ADM_Maillist.options.length; i++)
		if(select_ADM_Maillist.options[i].selected)
			select_ADM_Maillist.options[i--] = null;
}


function add_user2scl_maillist()
{
	select_available_users = document.getElementById('ea_select_available_users');
	select_usersInMaillist = document.getElementById('ea_select_users_SCL_Maillist');

	var count_available_users = select_available_users.length;
	var count_usersInMailList = select_usersInMaillist.options.length;
	var new_options = '';


	var teste = ''; //Variavel que ira receber mensagem de alerta ao usuario;
	var alerta = new Boolean(0); //Variavel que sera usada para verificar se o alerta ao usuario sera exibido ou nao;
	teste += "Usuário(os) já pertence(m) à lista:\n"; //Inicio da mensagem de alerta ao usuario;


	for (i = 0 ; i < count_available_users ; i++)
	{
		if (select_available_users.options[i].selected)
		{


			//Laco abaixo compara se o valor escolhido em select_available_users ja existe em select_usersInMaillist
			//se existir, adiciona o valor em teste e muda a variavel alerta para true; teste sera exibido em tela
			//apenas de alerta  true; ver if no fim da funcao;
			for(j = 0; j < count_usersInMailList; j++)
			{
				if(select_usersInMaillist.options[j].text == select_available_users.options[i].text)
				{
					teste += select_available_users.options[i].text + "\n";
					alerta = new Boolean(1);
				}
			}


			if(document.all)
			{
				if ( (select_usersInMaillist.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
				{
					new_options +=  "<option value="
								+ select_available_users.options[i].value
								+ ">"
								+ select_available_users.options[i].text
								+ "</options>";
				}
			}
			else
			{		
				if ( (select_usersInMaillist.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
				{
					new_options +=  "<option value="
								+ select_available_users.options[i].value
								+ ">"
								+ select_available_users.options[i].text
								+ "</options>";
				}
			}
		}
	}


	//Se alerta for true, exibe na tela o valor de teste;
	if(alerta == true)
	{
		alert(teste);
	}

	if (new_options != '')
	{
		select_usersInMaillist.innerHTML = '#' + new_options + select_usersInMaillist.innerHTML;
		select_usersInMaillist.outerHTML = select_usersInMaillist.outerHTML;
	}
}

function remove_user2scl_maillist()
{
	select_usersInMaillist = document.getElementById('ea_select_users_SCL_Maillist');
	
	for(var i = 0;i < select_usersInMaillist.options.length; i++)
		if(select_usersInMaillist.options[i].selected)
			select_usersInMaillist.options[i--] = null;
}


// Variaveis Locais 
if (document.getElementById('ea_select_available_users'))
{
	var select_available_users  = document.getElementById('ea_select_available_users');
	var select_available_users_clone = select_available_users.cloneNode(true);
}
else
{
	var select_available_users  = '';
	var select_available_users_clone = '';
}
var finderTimeout = '';

// Funcoes											
function optionFinderTimeout(obj)
{
	clearTimeout(finderTimeout);	
	var oWait = document.getElementById("ea_span_searching");
	oWait.innerHTML = 'Buscando...';
	finderTimeout = setTimeout("optionFinder('"+obj.id+"')",500);
}
function optionFinder(id) {
	var oWait = document.getElementById("ea_span_searching");
	var oText = document.getElementById(id);
		
	//Limpa todo o select
if(oText.length < 4) {
	var select_available_users_tmp = document.getElementById('ea_select_available_users');

	for(var i = 0;i < select_available_users_tmp.options.length; i++)
		select_available_users_tmp.options[i--] = null;
}
if(oText.length >= 4) {

get_available_users(oText.value);

                        var select_available_users_tmp = document.getElementById('ea_select_available_users');
                        for(var i = 0;i < select_available_users_tmp.options.length; i++)
                                select_available_users_tmp.options[i--] = null;
	var RegExp_name = new RegExp("\\b"+oText.value, "i");
	
	//Inclui usuário começando com a pesquisa
/*	for(i = 0; i < select_available_users_clone.length; i++){
		if ( RegExp_name.test(select_available_users_clone[i].text) || (select_available_users_clone[i].value == -1) )
		{
			sel = select_available_users_tmp.options;
			option = new Option(select_available_users_clone[i].text,select_available_users_clone[i].value);

			if (select_available_users_clone[i].value == -1)
				option.disabled = true;

			sel[sel.length] = option;
		}
	}*/
	oWait.innerHTML = '&nbsp;';
}
}			

function delete_maillist(uid, uidnumber)
{
	if (confirm("Realmente deletar Lista " + uid + " ??"))
	{
		var handler_delete_maillist = function(data)
		{
			if (!data.status)
				alert(data.msg);
			else
				//alert('Lista de email deletada com êxito!!');
				window.setTimeout("cExecute('$this.maillist.synchronize_mailman&uid=" + uid + "&op=0', handler_delete_maillist_sync)", 10);
			
//			location.href="./index.php?menuaction=listAdmin.uimaillists.list_maillists";
	//		return;
		}
		cExecute ('$this.maillist.delete&uidnumber='+uidnumber, handler_delete_maillist);
	}
}

function handler_delete_maillist_sync(data){
	//Converte os dados do vetor oriundo de um script PHP para um vetor JavaScript
	var dados_serializados = eval(data);
	return_handler_delete_maillist_sync(dados_serializados);
}

function return_handler_delete_maillist_sync(data){
	//Depois de processar a sincronização do Mailman com o RHDS, redireciona o usuário para a tela inicial
	//Mesmo que não tenha tido êxito, segue o comportamento default da aplicação, redirecionando o usuário
	//EM CASO DE ERRO NA SINCRONIZAÇÃO: CONFERIR O LOG "expresso_sincronizacao_mailmanrhds.log" em "/tmp"
	//alert("Retorno do socket:\nSTATUS=" + data['status'] +'\nMSG=' + data['msg']);
	alert('Lista de email deletada com êxito!!');
	location.href="./index.php?menuaction=listAdmin.uimaillists.list_maillists";		
	
}


