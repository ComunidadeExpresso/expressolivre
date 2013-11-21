<!-- BEGIN header -->

<form method="POST" action="{action_url}">
<table border="0" align="center">
<tbody>
	<tr class="th">
		<td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
	</tr>
	<tr>
		<td colspan="2"><b>{error}</b></td>
	</tr>
	<tr>
		<td colspan="2"></td>
	</tr>
<!-- END header -->
<!-- BEGIN body -->

<!-- INICIO BLOCO MAILMAN -->

	<tr class="row_off">
		<td>
			<b>
				Digite o DN usado pelas listas. (Ex.: ou=listas,dc=company,dc=com,dc=br)
			</b>
		</td>
		
		<td>
			<INPUT size="50" name="newsettings[dn_listas]" value="{value_dn_listas}" />
		</td>
	</tr>
	<tr class="row_on">
		<td>
			<b>
				Digite o NOME do grupo de administradores de listas. (Ex.: listadmin)
			</b>
		</td>

		<td>
			<INPUT size="50" name="newsettings[name_listadmin]" value="{value_name_listadmin}" />
		</td>
	</tr>
	<tr class="row_off">
		<td>
			<b>
				Digite o DN do grupo de administradores de listas. (Ex.: ou=grupos,dc=company,dc=com,dc=br)
			</b>
		</td>

		<td>
			<INPUT size="50" name="newsettings[dn_listadmin]" value="{value_dn_listadmin}" />
		</td>
	</tr>
	<tr class="row_on">
		<td>
			<b>
				Digite o dominio usado pelas listas. (Ex.: dominio.com.br)
			</b>
		</td>

		<td>
			<INPUT size="50" name="newsettings[dominio_listas]" value="{value_dominio_listas}" />
		</td>
	</tr>
	<tr class="row_off">
		<td>
			<b>
				Digite a porta utilizada pelo Mailman. (Ex: 80)
			</b>
		</td>

		<td>
			<INPUT size="50" name="newsettings[porta_mailman]" value="{value_porta_mailman}" onkeypress="return soNumero(this, event);"/>
		</td>
	</tr>
	<tr class="row_on">
		<td>
			<b>
				Digite o endereco IP do servidor Mailman. (Ex.: 192.168.0.1)
			</b>
		</td>

		<td>
			<INPUT size="50" name="newsettings[host_mailman]" value="{value_host_mailman}" />
		</td>
	</tr>
	<tr class="row_off">
		<td>
			<b>
				Digite o caminho do programa de sincronizacao do Mailman.
			</b>
		</td>

		<td>
			<INPUT size="50" name="newsettings[url_mailman]" value="{value_url_mailman}" />
		</td>
	</tr>

	<tr class="th">
		<td colspan="2">&nbsp;</td>
	</tr>

	<tr class="row_off">
		<td>
			<b>
				{lang_Show_lists_automatically}:
			</b>
		</td>
		<td>
			<select name="newsettings[mm_ldap_query_automatic]">
				<option value="true" {selected_mm_ldap_query_automatic_true}>Sim</option>
				<option value="false" {selected_mm_ldap_query_automatic_false}>N�o</option>
			</select>
		</td>
	</tr>
<!-- FIM BLOCO MAILMAN -->

<!-- END body -->
<!-- BEGIN footer -->
	<tr>
		<td colspan="2"></td>
	</tr>
	<tr class="th">
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="{lang_submit}">
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	</tr>
</tbody>
</table>
</form>
<!-- END footer -->
