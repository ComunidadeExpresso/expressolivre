<!-- BEGIN body -->
<table border="0" width="90%" align="center">
	<tr>
		<td valign="top">
			{rows}
		</td>
		<td valign="top">
			<form action="{form_action}" method="POST" name="app_form">
				<input type="hidden" name="uidnumber" value="{uidnumber}">
				<input type="hidden" name="old_uid" value="{uid}">
				<input type="hidden" name="ldap_context" value="{ldap_context}">
				<input type="hidden" name="manager_context" value="{manager_context}">
				<input type="hidden" name="restrictionsOnEmailLists" value="{restrictionsOnEmailLists}">
				<input type="hidden" name="defaultDomain" value="{defaultDomain}">

				<table border="0" width=100% cellspacing="4">
					<tr>
						<td colspan="3" align="right" bgcolor="{color_bg1}">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
							<input type="button" value="{lang_save}" onClick="javascript:validate_fields('{type}');">
						</td>
					</tr>
					
					<tr>
						<td width="40%" bgcolor="#DDDDDD">
							{lang_search_organization}:<br />
							<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_info');" onBlur="javascript:sinc_combos_org(context.value, ea_check_allUsers.checked);">
							<br />
							{lang_maillist_organization}:<br />
							<select id="ea_combo_org_info" name="context" onchange="javascript:sinc_combos_org(this.value, ea_check_allUsers.checked);">{combo_manager_org}</select><br />
							
							{lang_maillist_login}: <font color="blue">Ex: lista-celepar-rh</font><br />
							<input id="ea_maillist_uid" name="uid" size="35" value="{uid}" autocomplete="off" onblur="javascript:emailSuggestion_maillist();"><br />
							
							{lang_maillist_mail}: <font color="blue">Ex: lista-celepar-rh@celepar.pr.gov.br</font>
							<input id="ea_maillist_mail" name="mail" size="35" value="{mail}" autocomplete="off" style="display: block">
							
							{lang_maillist_name}:<br />
							<input name="cn" size="60" value="{cn}" autocomplete="off"><br />

							{lang_description}:<br />
							<input name="description" size="60" value="{description}" autocomplete="off"><br />

							{lang_email_list_is_active}: <input type="checkbox" {accountStatus_checked} name="accountStatus"><br />
							{lang_do_not_show_this_email_list}??: <input type="checkbox" {phpgwAccountVisible_checked} name="phpgwAccountVisible"><br />
							
							<b>{lang_maillist_users} (<font color=red>{user_count}</font>):</b><br />
							<select id="ea_select_usersInMaillist" name="mailForwardingAddress[]" style="width:400px; height:200px" multiple size="13">{ea_select_usersInMaillist}</select>
						</td>
						
						<td width="20%" valign="middle" align="center" bgcolor="#DDDDDD">
							<br /><br /><br /><br /><br /><br />
							<button type="button" onClick="javascript:add_user2maillist();"><img src="expressoAdmin/templates/default/images/add.png" style="vertical-align: middle;" >&nbsp;{lang_add_user}</button>
							<br /><br />
							<button type="button" onClick="javascript:remove_user2maillist();"><img src="expressoAdmin/templates/default/images/rem.png" style="vertical-align: middle;" >&nbsp;{lang_remove_user}</button>
						</td>
						
						<td width="40%" valign="bottom" bgcolor="#DDDDDD">
							{lang_search_organization}:<br />
							<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_maillists');" onBlur="javascript:get_available_users(org_context.value, ea_check_allUsers.checked);">
							<br />
							
							{lang_organizations}:<br />
							<select name="org_context" id="ea_combo_org_maillists" onchange="javascript:get_available_users(this.value, ea_check_allUsers.checked);">{combo_all_orgs}</select>
							<br />
							<br /><br />
							
							{lang_search_user}:<br />
							<input id="ea_input_searchUser" size="35" autocomplete="off" onkeyup="javascript:optionFinderTimeout(this,event)"><br />
							
							<font color="red"><span id="ea_span_searching">&nbsp;</span></font>
							<br />
							<b>{lang_users}:</b><br />
							<select id="ea_select_available_users" style="width:400px; height:200px" multiple size="13"></select>
                            <br/><br/>  
                              <b>{lang_external_user}:</b>  
                              <br/>  
                              <input id="ea_input_externalUser" size="35" type="text">  
                              <input id="input_user" value="Adicionar" onclick="javascript:validateEmail();" type="button">  
                              <br/> 
						</td>
					</tr>
					
					<tr>
						<td colspan="3" align="left" bgcolor="{color_bg1}">
							<input type="button" value="{lang_save}" onClick="javascript:validate_fields('{type}');">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>
<!-- END body -->
