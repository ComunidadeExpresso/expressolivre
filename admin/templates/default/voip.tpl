<!-- BEGIN voip_page -->
<form method="POST" action="{action_url}">
<table align="center" width="60%" cellspacing="2" style="border: 1px solid #000000;">
	<tr class="th">
		<td colspan="2">&nbsp;<b>{lang_VoIP_settings}</b></td>
	</tr>

	<tr class="row_off">
		<td>{lang_Enter_your_VoIP_server_address}:</td>
		<td><input name="voip_server" value="{value_voip_server}"  size="40"></td>
	</tr>   
	<tr class="row_on">
		<td>{lang_Enter_your_VoIP_server_url} (Ex.: /telefoniaip/servicos/voip.php):</td>
		<td><input name="voip_url" value="{value_voip_url}"  size="40"></td>
	</tr>   
	<tr class="row_off">
		<td>{lang_Enter_your_VoIP_server_port}:</td>
		<td><input name="voip_port" value="{value_voip_port}"></td>
	</tr>
	<tr class="row_on">
		<td>{lang_Email_Voip}</td>
		<td><input name="voip_email_redirect" value="{value_voip_email_redirect}" size="50" maxlength="50" onblur="javascript:voip.validateEmail(this);"></td>
	</tr>
	<tr class="th">
		<td colspan="2">&nbsp;<b>{lang_groups_ldap}</b></td>	
	</tr>
	<tr class="row_on">
		<td colspan="2">
			{lang_organizations} :
			&nbsp;
			<select id="admin_organizations_ldap" name="organizations" onchange="javascript:voip.search(this);">
				{ous_ldap}
			</select>
			<span id="admin_span_loading" style="color:red;visibility:hidden;">&nbsp;{lang_load}</span>
		</td>
	</tr>
	<tr class="row_off">
		<td colspan="2">
		<table align="center" cellspacing="0">
			<tr>
				<td class="row_off">	
					{lang_grupos_ldap} :
					<br/>
					<select id="groups_ldap" size="10" style="width: 300px" multiple></select>
				</td>
				<td class="row_off">
					<input type="button" value="Adicionar" onclick="javascript:voip.add();" />
					<br/>
					<br/>
					<input type="button" value="Remover" onclick="javascript:voip.remove();" />
				</td>
				<td class="row_off">
					{lang_grupos_liberados} :
					<br/>
					<select id="groups_voip" size="10" style="width: 300px" multiple name="voip_groups[]">{groups_voip}</select>
				</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		  <input type="submit" name="save" value="{lang_save}" onclick="javascript:voip.select_();">
		  <input type="submit" name="cancel" value="{lang_cancel}">
		  <br>
		</td>
	</tr>
</table>
</form>
<!-- END voip_page -->
