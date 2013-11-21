<input type="hidden" id="{modal_id}_title" value="{lang_criation_of_institutional_accounts}" />
<input type="hidden" id="{modal_id}_height" value="503" />
<input type="hidden" id="{modal_id}_width" value="930" />
<input type="hidden" id="{modal_id}_close_action" value="close_lightbox()" />
<input type="hidden" id="{modal_id}_create_action" value="create_institutional_accounts()" />
<input type="hidden" id="{modal_id}_save_action" value="save_institutional_accounts()" />
<input type="hidden" id="{modal_id}_onload_action" value="set_onload({manager_context})" />

<form enctype="multipart/form-data" name="institutional_accounts_form_template" method="post">
<input type="hidden" id="anchor" name="anchor" />

<table border="0" width="80%" cellspacing="4">
	<tr>
		<td width="35%" bgcolor="#DDDDDD">
			{lang_search_organization}:<br />
			<input type="text" id="organization_search"  size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org');" onBlur="javascript:sinc_combos_org(context.value);" />
			<br />
			{lang_organization}:<br />
			<select id="ea_combo_org" name="context" onchange="javascript:sinc_combos_org(this.value);javascript:get_associated_domain(this.value);">{manager_organizations}</select><br />
			
			<input type="hidden" id="associated_domain" name="associated_domain" />

			{lang_full_name}: <font color="blue">{lang_eg}: Setor Diser</font><br />
			<input id="cn" name="cn" size="35" autocomplete="off" /><br />
							
			{lang_mail}: <font color="blue">{lang_eg}: diser@celepar.pr.gov.br</font><br />
			<input id="mail" name="mail" onKeyUp='javascript:emailSugestion_expressoadmin2(this)' size="35" autocomplete="off" /><br />
						
			{lang_description}:<br />
			<input id="desc" name="desc" size="60" autocomplete="off" /><br />

			{lang_is_account_active}: <input type="checkbox" id="accountStatus" name="accountStatus" checked /><br />
			{lang_omit_account_from_the_catalog}: <input type="checkbox" id="phpgwAccountVisible" name="phpgwAccountVisible" /><br />
							
			<b>{lang_owners}:</b><br />
			<select id="ea_select_owners" name="owners[]" style="width:400px; height:200px" multiple size="13"></select>
		</td>
						
		<td width="10%" valign="middle" align="center" bgcolor="#DDDDDD">
			<button type="button" onClick="javascript:add_user();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle;" />&nbsp;{lang_add_owner}</button>
			<br /><br />
			<button type="button" onClick="javascript:remove_user();"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;" />&nbsp;{lang_remove_owner}</button>
		</td>
						
		<td width="35%" valign="bottom" bgcolor="#DDDDDD">
			{lang_search_organization}:<br />
			<input type="text" id="organization_search"  size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_available_users');" onBlur="javascript:get_available_users(org_context.value);" />
			<br />
							
			{lang_organizations}:<br />
			<select name="org_context" id="ea_combo_org_available_users" onchange="javascript:get_available_users(this.value);">{all_organizations}</select>
			<br />
			<br /><br />
							
			{lang_search_user}:<br />
			<input id="ea_input_searchUser" size="35" autocomplete="off" onkeyup="javascript:optionFinderTimeout(this,event)" /><br />
							
			<font color="red"><span id="ea_span_searching">&nbsp;</span></font>
			<br />
			<b>{lang_users}:</b><br />
			<select id="ea_select_available_users" style="width:400px; height:200px" multiple size="13"></select>
		</td>
	</tr>
</table>

</form>