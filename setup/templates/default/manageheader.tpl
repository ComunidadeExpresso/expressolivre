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

  function ocultar(zdiv)
  {
	var xdiv = document.getElementById(zdiv);
	if(xdiv.id == "certificado") {
		xdiv.style.display='none';
		document.getElementById('cert_0').checked = true;
	}
	if(xdiv.id == "criptografiax") {
		document.getElementById("atributo_cpf").style.display='none';
	}
	if( xdiv.id == "certificado" || xdiv.id == "criptografia") {
		var xdiv = document.getElementById('criptografia');
		document.getElementById('cripto_0').checked = true;
                document.getElementById("atributo_cpf").style.display='none';
	}
	if(xdiv.id == "certificado" || xdiv.id == "criptografia" ) {
		var xdiv = document.getElementById('criptografiax');
		document.getElementById('maxcerttxt').value = 0;
		//document.getElementById('atributoexpiracaotxt').value = '';
		//document.getElementById('atributousuarios').value = '';
	}
	if(xdiv.id == "badlogin") {
		document.getElementById('badlogintxt').value='0';
	}
	xdiv.style.display='none';	
  }

  function exibir(zdiv)
  {
	var xdiv = document.getElementById(zdiv);
	if(zdiv == "criptografiax") {
		document.getElementById("atributo_cpf").style.display='';
	}
	if(xdiv.id == "cripto_options") {
		document.getElementById('maxcerttxt').value = '10';
		//document.getElementById('atributoexpiracaotxt').value = 'phpgwlastpasswdchange';
		//document.getElementById('atributousuarios').value = '';
	}
	if(xdiv.id == "badlogin") {
		document.getElementById('badlogintxt').value= '2';
	}
	xdiv.style.display='';
  }   
  
function getEvent(e)
// Retorna um dicionario com o objeto evento e o codigo da tecla pressionada
{
  var d
  var keycode
  var evento
  if (window.event)
    d = { e: window.event, keycode: window.event.keyCode }
  else
  {
    if (e)
      d = { e: e, keycode: e.which }
    else
      return null
  }
  return d
}

function soNumero(myfield, e)
// Permite a digitacao de apenas numeros em campos de formularios
// Utilizacao: <input type="text" onkeypress="return soNumero(this, event);">
{

  var d = getEvent(e);
  var e = d['e'];
  var keycode = d['keycode'];
  if (e == null) return true;
  // Tecla de funcao (Ctrl, Alt), deixa passar
  if (e.ctrlKey || e.metaKey || keycode < 32)
    return true;
  else
    return (keycode > 47 && keycode < 58); // false se tecla nao for numerica
}

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
  
	<!-- INICIO configuracoes exclusivas para o ExpressoLivre -->
	<tr><td><br></td></tr>
	<th colspan="2" class="th">ExpressoLivre</th>
	</td></tr>
	
	<tr><td colspan="2">
	<fieldset><legend>HTTPS</legend>
	<table>
	<tr><td colspan="2"><b>Usar HTTPS?</b></td></tr>
  	<tr><td colspan="2">
	  	<font color='red'>Obs.: Apenas use https no site, caso o apache esteja configurado para isto. A porta 443 DEVE estar liberada.</font><br>
		<INPUT type="radio"{use_https_0} name="setting[use_https]" value="0" onclick="javascript:ocultar('certificado')">N&Atilde;O usar HTTPS no site.<BR>
		<INPUT type="radio"{use_https_1} name="setting[use_https]" value="1" onclick="javascript:exibir('certificado')" >Usar HTTPS apenas no Login.<BR>
		<INPUT type="radio"{use_https_2} name="setting[use_https]" value="2" onclick="javascript:exibir('certificado')" >Usar HTTPS no Site inteiro.<BR>	
  	</td></tr>
	<tr><td colspan="2">
           <div id="certificado" {div_cert}>
	<table>
	<tr><td colspan="2"><b>Usar Certificado Digital (para identificar o usuario no processo de login)?</b></td></tr>
  	<tr><td colspan="2">
	        <font color='red'>Obs.: Para habilitar este item o uso do HTTPS deve ter sido habilitado.</font><br>
		<INPUT id="cert_0" type="radio" {certificado_0} name="setting[certificado]" onclick="javascript:ocultar('criptografiax')" value="0" >N&Atilde;O Usar Certificado Digital.<BR>
		
		<INPUT id="cert_1" type="radio" {certificado_1} name="setting[certificado]" onclick="javascript:exibir('criptografiax')" value="1">Usar Certificado Digital.<BR>
               <div id="atributo_cpf" {div_atributo_cpf}>
                <b>Nome do atributo , no ldap, para identificar CPF do proprietario do certificado digital</b><BR>
                <INPUT type="text" maxlength="50" size="40" name="setting[certificado_atributo_cpf]" id="certificado_atributo_cpf" value="{certificado_atributo_cpf}" ><BR>
               </div>
  	</td></tr>
	</table>
	</div>

	</td></tr>
	</table>
	</fieldset>
	</td></tr>
	<tr><td colspan="2"><div id="criptografiax" {div_criptox} >
	<fieldset><legend>Criptografia e Assinatura Digital</legend>
	<table>
	<tr><td colspan="2">
	<b>Habilitar Assinar/Criptografar digitalmente?</b>
	 <br><font color='red'>Obs.: Para habilitar este item o uso de HTTPS e Certificado Digital devem ter sido habilitados.</font>
	</td></tr>
	<tr><td colspan="2">       
		<INPUT id='cripto_0' onclick="javascript:ocultar('cripto_options')" type="radio" {use_assinar_criptografar_0} name="setting[use_assinar_criptografar]" value="0"  />N&Atilde;O habilitar.<BR>
		<div id="criptografia" ><INPUT id='cripto_1' onclick="javascript:exibir('cripto_options')" type="radio" {use_assinar_criptografar_1} name="setting[use_assinar_criptografar]" value="1" />Habilitar.</div><BR>
	</td></tr>
	<tr><td colspan="2">
	<div id="cripto_options" {cripto_options}>
		<table>
			<tr><td colspan="2">
				<b>Numero maximo de destinatarios para uma mensagem cifrada<br><INPUT type="text" maxlength="2" size="3" name="setting[num_max_certs_to_cipher]" id="maxcerttxt" value="{num_max_certs_to_cipher}" onkeypress="return soNumero(this, event);"></td></tr>
		</table>		
  	</div>
        </td></tr>
	</table>
	</fieldset></div>
   </td></tr>
   <tr><td colspan="2">
	<fieldset><legend> Contrôle da senha</legend>
	<table>
			<tr><td nowrap>
				<b>Nome do atributo , no ldap, para controle de expiracao da senhas</b>
			</td></tr>
			<tr><td>
				<INPUT type="text" maxlength="50" size="40" name="setting[atributoexpiracao]" id="atributoexpiracaotxt" value="{atributoexpiracao}" >
			</td></tr>
			<tr><td nowrap>
				<b>Classe ldap utilizada para identificar os usuarios</b>
			</td></tr>
			<tr><td>
				<INPUT type="text" maxlength="50" size="40" name="setting[atributousuarios]" id="atributousuarios" value="{atributousuarios}" >
			</td></tr>
</table>
</fieldset>
</td></tr>
   <tr><td colspan="2">
	<fieldset><legend> Anti-Robo</legend>
	<table>
	<tr><td colspan="2"><b>Usar Anti-Robo(CAPTCHA) ?</b></td></tr>
  	<tr><td colspan="2">
		<INPUT type="radio" {captcha_0} name="setting[captcha]" value="0" onclick="javascript:ocultar('badlogin')">N&Atilde;O Usar Anti-Robo.<BR>
		<INPUT type="radio" {captcha_1} name="setting[captcha]" value="1" onclick="javascript:exibir('badlogin')" >Usar Anti-Robo.<BR>
  	</td></tr>		
	<tr><td colspan="2">
	<div id="badlogin" {div_badlogin}>
	<table>
	<tr><td colspan="2"><b>Numero de falhas no login, antes de exibir o codigo do Anti-robo ?</b></td></tr>
  	<tr><td colspan="2">
		<INPUT type="text" maxlength="2" size="3" name="setting[num_badlogin]" id="badlogintxt" value="{num_badlogin}" onkeypress="return soNumero(this, event);">
  	</td></tr>
	</table>
	</div>

	</td></tr>
	</table>
	</fieldset>
	</td></tr>
	<tr><td colspan="2"><b>Digite os enderecos de emails, separados por virgula, que devem receber as sugestoes enviadas pelos usuarios.</b></td></tr>
	<tr><td colspan="2"><INPUT size="100" name="setting[sugestoes_email_to]" value="{sugestoes_email_to}"></td></tr>
	
	<tr><td colspan="2"><b>Digite parte do seu dominio. Esta parte de dominio sera concatenada a organizacao do usuario para formar o dominio do usuario. Ex.: usuario@organizacao.dominio -> joao@serpro.gov.br, o gov.br a parte do dominio.</b></td></tr>
	<tr><td colspan="2"><INPUT size="50" name="setting[domain_name]" value="{domain_name}"></td></tr>
	
	<th colspan="2" class="th">&nbsp;</th>
  	<!-- FIM configuracoes exclusivas para o ExpressoLivre -->

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
