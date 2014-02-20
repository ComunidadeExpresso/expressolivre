<!-- BEGIN list -->
<table border="0" width="90%" align="center">
	<tr>
		<td valign="top">
			{rows}
		</td>
		<td valign="top">
			<form action="{form_action}" method="POST" name="app_form">
				<input type="hidden" name="gidnumber"			value="{gidnumber}">
				<input type="hidden" name="old_cn"				value="{cn}">
				<input type="hidden" name="defaultDomain"		value="{defaultDomain}">
				<input type="hidden" name="ldap_context"		value="{ldap_context}">
				<input type="hidden" name="ufn_ldap_context"	value="{ufn_ldap_context}">
				<input type="hidden" name="manager_context"		value="{manager_context}">

				<table border="0" width=100% cellspacing="4">
					<tr>
						<td colspan="3" align="right" bgcolor="{color_bg1}">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
							<input type="button" value="{lang_save}" onClick="javascript:validate_fields('{type}','{restrictionsOnGroup}');">
						</td>
					</tr>

					<tr>
						<td width="40%" bgcolor="#DDDDDD">
							{lang_search_organization}:<br />
							<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_info');" onBlur="javascript:sinc_combos_org(context.value); get_available_sambadomains(context.value, '{type}')">
							<br />
							{lang_group_organization}:<br />
							<select id="ea_combo_org_info" name="context" onchange="javascript:sinc_combos_org(this.value); get_available_sambadomains(this.value, '{type}')">{combo_manager_org}</select><br />
							{lang_group_name}: <font color="blue">Ex: grupo-celepar-rh</font><br />
							<!--<input name="cn" size="35" value="{cn}" autocomplete="off" onKeyUp="javascript:groupEmailSuggestion('{concatenateDomain}','{type}')"><br />-->
							<input name="cn" size="35" value="{cn}" autocomplete="off"><br />
							{lang_email}:<br />
							<input name="email" size="60" value="{email}" {disable_email_groups} autocomplete="off"><br />
							{lang_description}:<br />
							<input name="description" size="60" value="{description}" autocomplete="off"><br />
							<div id="ea_div_display_samba_options" style={display_samba_options}>
								<table border="0">
									<tr bgcolor={row_on}>
										<td>{lang_use_samba_attributes}:</td>
										<td>						
											<input type="checkbox" {use_attrs_samba_checked} name="use_attrs_samba" onChange="javascript:use_samba_attrs(this.checked)">
										</td>
									</tr>
									<tr>
										<td>{lang_domain}:</td>
										<td>
											<select {disabled_samba} name="sambasid" id="ea_combo_sambadomains">
												{sambadomainname_options}
											</select>
										</td>
									</tr>
								</table>
							</div>
							{lang_do_not_show_this_group}? <input type="checkbox" {phpgwaccountvisible_checked} name="phpgwaccountvisible"><br />
							
							<b>{lang_group_users} (<font color=red>{user_count}</font>):</b><br />
							<select id="ea_select_usersInGroup" name="members[]" style="width: 400px" multiple size="13">{ea_select_usersInGroup}</select>
							<button type="button" onClick="javascript:popup_group_info();">{lang_text}</button>
						</td>
						
						<td width="20%" valign="middle" align="center" bgcolor="#DDDDDD">
							<br /><br /><br /><br /><br /><br />
							<button type="button" onClick="javascript:add_user2group();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle;">&nbsp;{lang_add_user}</button>
							<br /><br />
							<button type="button" onClick="javascript:remove_user2group();"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;">&nbsp;{lang_remove_user}</button>
						</td>
						
						<td width="40%" valign="bottom" bgcolor="#DDDDDD">
							{lang_search_organization}:<br />
							<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_groups');" onBlur="javascript:get_available_users(org_context.value);">
							<br />
						
							{lang_organizations}:<br />
							<select name="org_context" id="ea_combo_org_groups" onchange="javascript:get_available_users(this.value);">{combo_all_orgs}</select>
							
							<br />
							<br /><br />
							
							{lang_search_user}:<br />
							<input id="ea_input_searchUser" size="35" autocomplete="off" onkeyup="javascript:optionFinderTimeout(this, event)"><br />
							
							<font color="red"><span id="ea_span_searching">&nbsp;</span></font>
							<br />
							<b>{lang_users}:</b><br />
							<select id="ea_select_available_users" style="width: 400px" multiple size="13"></select>
						</td>
					</tr>
					
					<tr>
        				<td colspan="3">
        					{lang_applications}:
        					<br />
        					<table width="100%" border="0" cols="6">
								{apps}
							</table>
						</td>
					</tr>

					<tr>
						<td colspan="3" align="left" bgcolor="{color_bg1}">
							<input type="button" value="{lang_save}" onClick="javascript:validate_fields('{type}','{restrictionsOnGroup}');">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>
<!-- END list -->
