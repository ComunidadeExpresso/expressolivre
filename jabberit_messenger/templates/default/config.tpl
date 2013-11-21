<!-- BEGIN header -->
<script type="text/javascript" src="jabberit_messenger/js/connector.js"></script>
<script type="text/javascript" src="phpgwapi/js/x_tools/xtools.js"></script>
<script type="text/javascript" src="jabberit_messenger/controller.php?act=j.setup"></script>
<form method="POST" action="{action_url}">
<table border="0" align="center">
	<tr bgcolor="{th_bg}">
		<td colspan="2"><font color="{th_text}">&nbsp;<b>{title} - Jetti Applet</b></font></td>
	</tr>
<!-- END header -->

<!-- BEGIN body -->
	<tr bgcolor="{row_on}">
		<td colspan="2">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="2">&nbsp;<b>:: Configuração do Servidor Jabber com Jeti Applet ::</b></td>
	</tr>

	<tr bgcolor="{row_on}">
		<td id="JETTI_name_jabberit__label">Digite o nome do Domínio Jabber</td>
		<td>&nbsp;<input type="text" id="JETTI_name_jabberit" name="newsettings[name_jabberit]" value="{value_name_jabberit}" size="41" maxlength="47"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td id="JETTI_ip_server_jabberit__label">Digite o Ip(s) do Servidor Jabber</td>		
		<td>&nbsp;<input type="text" id="JETTI_ip_server_jabberit" name="newsettings[ip_server_jabberit]" value="{value_ip_server_jabberit}" size="41" maxlength="47"></td>
	</tr>
	
	<tr bgcolor="{row_on}">
		<td id="JETTI_port_1_jabberit__label">Porta do servidor Jabber</td>
		<td>&nbsp;<select id="JETTI_port_1_jabberit" name="newsettings[port_1_jabberit]">
				<option value="false" {selected_port_1_jabberit_false}>Não Segura ( Sem SSL )</option>
				<option value="true" {selected_port_1_jabberit_true}>Segura ( Com SSL )</option>
			</select>
		</td> 
	</tr>

	<tr bgcolor="{row_off}">		   
		<td id="JETTI_port_2_jabberit__label">Mudança de porta</td>
		<td>&nbsp;<input type="text" id="JETTI_port_2_jabberit" name="newsettings[port_2_jabberit]" value="{value_port_2_jabberit}"></td>
	</tr>
   
	<tr bgcolor="{row_on}">
		<td id="JETTI_resource_jabberit__label">Digite Nome da Conexão</td>
		<td>&nbsp;<input type="text" id="JETTI_resource_jabberit" name="newsettings[resource_jabberit]" value="{value_resource_jabberit}"></td>
	</tr>
   
	<tr bgcolor="{row_off}">
		<td colspan="2">&nbsp;<b>:: Configurando o Nome da Empresa ::</b></td>
	</tr>
	
	<tr bgcolor="{row_on}">
		<td id="JETTI_name_company_applet_jabberit__label">Digite o Nome da sua Empresa</td> 
		<td>&nbsp;<input type="text" id="JETTI_name_company_applet_jabberit" name="newsettings[name_company_applet_jabberit]" value="{value_name_company_applet_jabberit}"></td>	
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="2">&nbsp;<b>:: Configuração do Servidor Ldap ::</b></td>
	</tr>

	<tr bgcolor="{row_on}">
		<td id="JETTI_server_ldap_jabberit__label">Servidor Ldap</td>
		<td>&nbsp;<input type="text" id="JETTI_server_ldap_jabberit" name="newsettings[server_ldap_jabberit]" value="{value_server_ldap_jabberit}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td id="JETTI_context_ldap_jabberit__label">Contexto</td>
		<td>&nbsp;<input type="text" id="JETTI_context_ldap_jabberit" name="newsettings[context_ldap_jabberit]" value="{value_context_ldap_jabberit}" size="30"></td>
	</tr>

	<tr bgcolor="{row_on}">
		<td id="JETTI_anonymous_bind_jabberit__label">Anonimous Bind</td>
		<td>&nbsp;<select id="JETTI_anonymous_bind_jabberit" name="newsettings[anonymous_bind_jabberit]">
				<option value="false" {selected_anonymous_bind_jabberit_false}>Não</option>
				<option value="true" {selected_anonymous_bind_jabberit_true}>Sim</option>
			</select>
		</td>
	</tr>	

	<tr bgcolor="{row_off}">
		<td id="JETTI_user_ldap_jabberit__label">Usuário Ldap</td>
		<td>&nbsp;<input type="text" id="JETTI_user_ldap_jabberit" name="newsettings[user_ldap_jabberit]" value="{value_user_ldap_jabberit}" size="30"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td id="JETTI_password_ldap_jabberit__label">Password</td>
		<td>&nbsp;<input type="password" id="JETTI_password_ldap_jabberit" name="newsettings[password_ldap_jabberit]" value="{value_password_ldap_jabberit}"></td>
	</tr>

<!-- END body -->

<!-- BEGIN footer -->
	<tr>
		<td colspan="2" align="center">
			<input type="button" value="{lang_submit}" onclick="constructScript.sendf(document);">
			<!--
			No MSIE utilizar um botão do tipo submit não permite que seja gravado o arquivo e configuração.
			Por esse motivo o envio do formulário está sendo feito através do javascript.
			NÃO REMOVA...			 
			-->
			<input type="submit" name="submit" style="display: none">
			<input type="button" name="cancel" value="{lang_cancel}" onclick="document.location.href='./index.php?menuaction=jabberit_messenger.uiconfig.configServer'">
		</td>
	</tr>
</table>
</form>
<!-- END footer -->