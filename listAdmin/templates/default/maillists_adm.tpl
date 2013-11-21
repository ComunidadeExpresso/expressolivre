<table border="0" width="90%" align="center">
	<tr>
		<td valign="top">
			{rows}
		</td>
		<td valign="top">
			<table border="0" width=100% cellspacing="4">
			<form action="{form_action}" method="POST" name="app_form">
					<input type="hidden" name="uidnumber" value="{uidnumber}">
					<input type="hidden" name="old_uid" value="{uid}">
					<input type="hidden" name="ldap_context" value="{ldap_context}">
					<input type="hidden" name="tipo" value="{type}">
					<input type="hidden" name="manager_context" value="{manager_context}">
					<input type="hidden" name="restrictionsOnEmailLists" value="{restrictionsOnEmailLists}">

				<tr>
					<td colspan="3" align="right" bgcolor="{color_bg1}">
						<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'" />
						<!--<input type="button" value="{lang_save}" onClick="javascript:validate_fields('{type}');" {desabilitado} />-->
						<input type="button" value="{lang_save}" onClick="javascript:save_adm();" {desabilitado} />
					</td>
				</tr>

				<tr style="display:none;">
					<td width="25%" bgcolor="#DDDDDD">
						Organização da Lista:<br />
						<select name="context" onchange="javascript:sinc_combos_org(this.value, ea_check_allUsers.checked);">{combo_org}</select><br />
					</td>
					<td width="25%" bgcolor="#DDDDDD"></td>
					<td width="25%" bgcolor="#DDDDDD"></td>
				</tr>
				<tr>
					<td width="25%" bgcolor="#DDDDDD">
						{lang_maillist_uid}:<br />
						<input name="uid" size="35" value="{uid}" autocomplete="off" readonly /><br />
					</td>
					<td width="25%" bgcolor="#DDDDDD"></td>
					<td width="25%" bgcolor="#DDDDDD">
						<p style="display:none;">Senha: <font color="blue"><br /> </font></p>
						<input name="listPass" size="35" type="password" value="{listPass}" autocomplete="off" style="display:none;" />
					</td>
				</tr>
				<tr>	
					<td width="25%" bgcolor="#DDDDDD">					
						{lang_maillist_mail}:<br />
						<input name="mail" size="60" value="{mail}" autocomplete="off" readonly /><br />

						{lang_maillist_description}:<br />
						<input name="description" size="60" value="{description}" autocomplete="off" readonly /><br />
						Lista de E-mail está ativa: <input type="checkbox" {accountStatus_checked} name="accountStatus" {desabilitado} /><br />
						Ocultar Lista de E-mail ??: <input type="checkbox" {phpgwAccountVisible_checked} name="phpgwAccountVisible" {desabilitado} /><br />
						<b>{lang_maillist_adm}:</b><br />
						<select id="ea_select_ADM_Maillist" name="members[]" style="width:400px; height:150px" multiple size="13">{ea_select_ADM_Maillist}
						</select>
					</td>

					<td valign="middle" align="center" bgcolor="#DDDDDD">
						<button type="button" onClick="javascript:add_user2adm_maillist();" {desabilitad} ><img src="listAdmin/templates/default/images/add.png" style="vertical-align: middle;" >&nbsp;{lang_add_user}</button>
						<br /><br />
						<button type="button" onClick="javascript:remove_user2adm_maillist();" {desabilitad} ><img src="listAdmin/templates/default/images/rem.png" style="vertical-align: middle;" >&nbsp;{lang_rem_user}</button>
					</td>

					<td valign="bottom" bgcolor="#DDDDDD">
						<p style="display: none;" >Organizações:</p><br />
						<select name="org_context" id="ea_combo_org_maillists" onchange="//javascript:get_available_users_only(this.value, ea_check_allUsers.checked);" disabled style="display:none;" >{combo_org}
						</select>

						<br />
<!--						<input type="checkbox" name="ea_check_allUsers" id="ea_check_allUsers" onclick="javascript:get_available_users_only(org_context.value, this.checked);" />{lang_all_users}.-->
						<br />

						Procurar usuário:<br />
						<input id="ea_input_searchUser" size="35" autocomplete="off" onkeyup="//javascript:optionFinderTimeout(this)" {somente_leitur} />

						<!-- botao de pesquisa manual -->
						<input type="button" id="search_user" value="Pesquisar" onClick="javascript:search_users();" {desabilitad} /><br />

						<!-- <font color="red"><span id="ea_span_searching">&nbsp;</span></font> -->
						<br />
						 <b>Usuários:</b>
						 <select id="ea_select_available_users" style="width:400px; height:150px" multiple size="13">
						</select>

					</td>
				</tr>
				<tr>
					<td colspan="3" align="left" bgcolor="{color_bg1}">
						<!--<input type="button" value="{lang_save}" onClick="javascript:validate_fields('{type}');" {desabilitad} />-->
						<input type="button" value="{lang_save}" onClick="javascript:save_adm();" {desabilitad} />
						<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'" />
					</td>
				</tr>
			</form>
			</table>
		</td>
	</tr>
</table>
