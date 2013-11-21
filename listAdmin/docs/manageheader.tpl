<!-- BEGIN manageheader -->

<script language="JavaScript" type="text/javascript">
<!--
  {js_default_db_ports}
  function setDefaultDBPort(selectBox,portField)
  {
	//alert("select: " + selectBox + "; portField: " + portField);
    if(selectBox.selectedIndex != -1 && selectBox.options[selectBox.selectedIndex].value)
	{
		//alert("value = " + selectBox.options[selectBox.selectedIndex].value);
		portField.value = default_db_ports[selectBox.options[selectBox.selectedIndex].value];
	}
    return false;
  }

/***** INICIO BLOCO MAILMAN *****/

	function ocultar(zdiv)
	{
		var xdiv = document.getElementById(zdiv);
		if(xdiv.id == "certificado") {
			xdiv.style.display='none';
			var xdiv = document.getElementById('cert_0');
			xdiv.checked = true;
		}
		if(xdiv.id == "conf_mailman") {
			xdiv.style.display='none';
			document.getElementById('use_mail_0').checked = true;
		}
		if(xdiv.id == "badlogin") {
			xdiv.style.display='none';
			var xdiv = document.getElementById('badlogintxt');
			xdiv.value='0';
		}
	}

	function exibir(zdiv)
	{
		var xdiv = document.getElementById(zdiv);
		xdiv.style.display='';
	}

/***** FIM BLOCO MAILMAN *****/

//-->
</script>

<table border="0" width="90%" cellspacing="0" cellpadding="0" align="center">
<tbody><tr><td>

{detected}

	<tr class="th">
    <th colspan="2">{lang_settings}</th>
  </tr>
   <form name="domain_settings" action="manageheader.php" method="post">
    <input type="hidden" name="setting[write_config]" value="true">
  <tr>
    <td colspan="2"><b>{lang_serverroot}</b>
      <br><input type="text" name="setting[server_root]" size="80" value="{server_root}">
    </td>
  </tr>
  <tr>
    <td colspan="2"><b>{lang_includeroot}</b><br><input type="text" name="setting[include_root]" size="80" value="{include_root}"></td>
  </tr>
  <tr>
    <td colspan="2"><b>{lang_adminuser}</b><br><input type="text" name="setting[HEADER_ADMIN_USER]" size="30" value="{header_admin_user}"></td>
  </tr>
  <tr>
    <td colspan="2"><b>{lang_adminpass}</b><br><input type="password" name="setting[HEADER_ADMIN_PASSWORD]" size="30" value="{header_admin_password}"><input type="hidden" name="setting[HEADER_ADMIN_PASS]" value="{header_admin_pass}"></td>
  </tr>
  <tr>
    <td colspan="2"><b>{lang_setup_acl}</b><br><input type="text" name="setting[setup_acl]" size="30" value="{setup_acl}"></td>
  </tr>
  <tr>
    <td><b>{lang_persist}</b><br>
      <select type="checkbox" name="setting[db_persistent]">
        <option value="True"{db_persistent_yes}>{lang_Yes}</option>
        <option value="False"{db_persistent_no}>{lang_No}</option>
      </select>
    </td>
    <td>{lang_persistdescr}</td>
  </tr>
  <tr>
    <td><b>{lang_sesstype}</b><br>
      <select name="setting[sessions_type]">
{session_options}
      </select>
    </td>
    <td>{lang_sesstypedescr}</td>
  </tr>
  <tr>
    <td><b>{lang_enablemcrypt}</b><br>
      <select name="setting[enable_mcrypt]">
        <option value="True"{mcrypt_enabled_yes}>{lang_Yes}</option>
        <option value="False"{mcrypt_enabled_no}>{lang_No}</option>
      </select>
    </td>
    <td>{lang_mcrypt_warning}</td>
  </tr>
  <tr>
    <td><b>{lang_mcryptversion}</b><br><input type="text" name="setting[mcrypt_version]" value="{mcrypt}"></td>
    <td>{lang_mcryptversiondescr}</td>
  </tr>
  <tr>
    <td><b>{lang_mcryptiv}</b><br><input type="text" name="setting[mcrypt_iv]" value="{mcrypt_iv}" size="30"></td>
    <td>{lang_mcryptivdescr}</td>
  </tr>
  <tr>
    <td><b>{lang_domselect}</b><br>
      <select name="setting[domain_selectbox]">
        <option value="True"{domain_selectbox_yes}>{lang_Yes}</option>
        <option value="False"{domain_selectbox_no}>{lang_No}</option>
      </select></td><td>&nbsp;
    </td>
  </tr>
{domains}{comment_l}
  <tr class="th">
    <td colspan="2"><input type="submit" name="adddomain" value="{lang_adddomain}"></td>
  </tr>{comment_r}
  
	<!-- INICIO configurações exclusivas para o ExpressoLivre -->
	<tr><td><br></td></tr>
	<th colspan="2" class="th">ExpressoLivre</th>
	
	<tr><td colspan="2"><b>Usar HTTPS?</b></td></tr>
  	<tr><td colspan="2">
	  	<font color='red'>Obs.: Apenas use https no site, caso o apache esteja configurado para isto. A porta 443 DEVE estar acessível.</font><br>
		<INPUT type="radio"{use_https_0} name="setting[use_https]" value="0">NÃO Usar HTTPS no site.<BR>
		<INPUT type="radio"{use_https_1} name="setting[use_https]" value="1">Usar HTTPS apenas no Login.<BR>
		<INPUT type="radio"{use_https_2} name="setting[use_https]" value="2">Usar HTTPS no Site inteiro.<BR>	
  	</td></tr>

<!-- INICIO BLOCO MAILMAN -->

	<tr><td colspan="2">
	<fieldset><legend>Mailman</legend>
	<table>
		<tr><td colspan="2"><b>Usar Mailman?</b></td></tr>
		<tr><td colspan="2">
			<INPUT id="use_mail_0" type="radio" {use_mailman_0} name="setting[use_mailman]" value="0" onclick="javascript:ocultar('conf_mailman')" />NAO usar listas no Mailman.<BR>
			<INPUT id="use_mail_1" type="radio" {use_mailman_1} name="setting[use_mailman]" value="1" onclick="javascript:exibir('conf_mailman')" />Usar listas no Mailman.<BR>
		</td></tr>
	</table>
	<table id="conf_mailman" {div_mailman} >
		<tr><td colspan="2"><b>Digite o DN usado pelas listas. (Ex.: ou=listas,dc=company,dc=com,dc=br)</b></td></tr>
		<tr><td colspan="2">
			<INPUT size="50" name="setting[dn_listas]" value="{dn_listas}" />
		</td></tr>
		<tr><td colspan="2"><b>Digite o dominio usado pelas listas. (Ex.: dominio.com.br)</b></td></tr>
		<tr><td colspan="2">
			<INPUT size="50" name="setting[dominio_listas]" value="{dominio_listas}" />
		</td></tr>
		<tr><td colspan="2"><b>Digite a porta utilizada pelo Mailman. (Ex: 80)</b></td></tr>
		<tr><td colspan="2">
			<INPUT size="50" name="setting[porta_mailman]" value="{porta_mailman}" onkeypress="return soNumero(this, event);"/>
		</td></tr>
		<tr><td colspan="2"><b>Digite o endereco IP do servidor Mailman. (Ex.: 192.168.0.1)</b></td></tr>
		<tr><td colspan="2">
			<INPUT size="50" name="setting[host_mailman]" value="{host_mailman}" />
		</td></tr>
		<tr><td colspan="2"><b>Digite o caminho do programa de sincronizacao do Mailman.</b></td></tr>
		<tr><td colspan="2">
			<INPUT size="50" name="setting[url_mailman]" value="{url_mailman}" />
		</td></tr>
	</table>

<!-- FIM BLOCO MAILMAN -->

	<tr><td colspan="2"><b>Digite os endereços de emails, separados por vírgula, que devem receber as sugestões enviados pelos usuários.</b></td></tr>
	<tr><td colspan="2"><INPUT size="50" name="setting[sugestoes_email_to]" value="{sugestoes_email_to}"></td></tr>
	
	<tr><td colspan="2"><b>Digite parte do seu domínio. Esta parte de domínio será concatenado a organização do usuário para formar o domínio do usuário. Ex.: usuario@organizacao.dominio -> joao@celepar.pr.gov.br, o pr.gov.br é a parte do domínio.</b></td></tr>
	<tr><td colspan="2"><INPUT size="50" name="setting[domain_name]" value="{domain_name}"></td></tr>
	
	<th colspan="2" class="th">&nbsp;</th>
  	<!-- FIM configurações exclusivas para o ExpressoLivre -->

  <tr>
    <td colspan="2">{errors}</td>
  </tr>
{formend}
  <tr>
    <td colspan="3">
 <form action="index.php" method="post">
  <br>{lang_finaldescr}<br>
  <input type="hidden" name="FormLogout"  value="header">
  <input type="hidden" name="ConfigLogin" value="Login">
  <input type="hidden" name="FormUser"    value="{FormUser}">
  <input type="hidden" name="FormPW"      value="{FormPW}">
  <input type="hidden" name="FormDomain"  value="{FormDomain}">
  <input type="submit" name="junk"        value="{lang_continue}">
 </form>
    </td>
  </tr>
  <tr class="banner">
    <td colspan="3">&nbsp;</td>
  </tr>
</table>
</body>
</html>
<!-- END manageheader -->

<!-- BEGIN domain -->
  <tr class="th">
    <td>{lang_domain}:</td>&nbsp;<td><input name="domains[{db_domain}]" value="{db_domain}">&nbsp;&nbsp;<input type="checkbox" name="deletedomain[{db_domain}]">&nbsp;<font color="fefefe">{lang_delete}</font></td>
  </tr>
  <tr>
    <td><b>{lang_dbtype}</b><br>
      <select name="setting_{db_domain}[db_type]" onchange="setDefaultDBPort(this,this.form['setting_{db_domain}[db_port]']);">
{dbtype_options}
      </select>
    </td>
    <td>{lang_whichdb}</td>
  </tr>
  <tr>
    <td><b>{lang_dbhost}</b><br><input type="text" name="setting_{db_domain}[db_host]" value="{db_host}"></td><td>{lang_dbhostdescr}</td>
  </tr>
  <tr>
  <tr>
    <td><b>{lang_dbport}</b><br><input type="text" name="setting_{db_domain}[db_port]" value="{db_port}"></td><td>{lang_dbportdescr}</td>
  </tr>
  <tr>
    <td><b>{lang_dbname}</b><br><input type="text" name="setting_{db_domain}[db_name]" value="{db_name}"></td><td>{lang_dbnamedescr}</td>
  </tr>
  <tr>
    <td><b>{lang_dbuser}</b><br><input type="text" name="setting_{db_domain}[db_user]" value="{db_user}"></td><td>{lang_dbuserdescr}</td>
  </tr>
  <tr>
    <td><b>{lang_dbpass}</b><br><input type="password" name="setting_{db_domain}[db_pass]" value="{db_pass}"></td><td>{lang_dbpassdescr}</td>
  </tr>
  <tr>
    <td><b>{lang_configuser}</b><br><input type="text" name="setting_{db_domain}[config_user]" value="{config_user}"></td>
  </tr>
  <tr>
    <td><b>{lang_configpass}</b><br><input type="password" name="setting_{db_domain}[config_pass]" value="{config_pass}"><input type="hidden" name="setting_{db_domain}[config_password]" value="{config_password}"></td>
    <td>{lang_passforconfig}</td>
  </tr>
<!-- END domain -->

</td></tr>
</tbody>

</table>
