/************************************************************
 * Métodos utilizados pelo componente wf_select_ldap_users. *
 ************************************************************/

/* Método que faz a chamada Ajax para buscar os registros
 * @param String cn Parte do nome a ser procurado no LDAP
 * @param String target id da combo onde os registros serão inseridos
 * @param String opt_id Atributo que será atribuído ao id (value) das options da combo, por padrão é o 'dn'
 * @param String opt_name Atributo que será atribuído ao name (innerHTML) das options da combo, por padrão é o 'cn'
 */
function search_ldap_users_by_cn(cn, target, opt_id, opt_name, handleExpiredSessions, opt_complement, useCCParams)
{  
	// o parâmetro opt_complement foi acrescentado posteriormente a esta função, devido alguns métodos não utilizá-lo é
	// necessário fazer o tratamento do mesmo caso não seja passado.
	if(opt_complement == undefined)
		opt_complement = '';

/* Método que trata o retorno da chamada Ajax. Atribui os valores retornados à combobox */
	function result_search_ldap_users_by_cn(data)
	{
		if (data['error'])
		{
			alert(data['error'].replace(/<br \/>/gi, "\n"));
			if (data['url'])
				if (handleExpiredSessions)
					window.location = data['url'].replace(/\.\./gi, ".");

			return;
		}

		if (data['msg'] != "")
		{
			document.getElementById(data["target"] + "_span").hide();
			document.getElementById(data['target'] + "_img").hide();
			alert(data['msg']);
			return false;
		}
		else
		{
			var container = document.getElementById(data["target"]);
			container.innerHTML = "";
			if(data['values'].length >= 1){
				container.disabled = true;
				fill_combo_employee(data["target"], data["values"]);
				container.disabled = false;
				document.getElementById(data["target"] + "_span").show();
				document.getElementById(data['target'] + "_img").hide();
				return true;
			}
		}

		return false;
	}

	var url = '$this.bo_utils.search_ldap_users_by_cn';
	var param = "cn=" + cn + "&target=" + target + "&id=" + opt_id + "&name=" + opt_name + "&complement=" + opt_complement + "&useCCParams=" + useCCParams;

	document.getElementById(target + "_img").show();

	cExecute(url, result_search_ldap_users_by_cn, param);
}

/* Preenche a combo com os registros recuperados na chamada Ajax */
function fill_combo_employee(target, values)
{
	var container = document.getElementById(target);

	for (var i = 0; i < values.length; i++)
	{
		var option = document.createElement("option");
		option.innerHTML = values[i].name;
		option.value = values[i].id;
		container.appendChild(option);
	}
}
