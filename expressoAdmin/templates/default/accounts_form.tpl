<!-- BEGIN main -->

<script type="text/javascript">
var tab = new Tabs(7,'activetab','inactivetab','tab','tabcontent','','','tabpage');
function initAll()
{
	tab.init();
}
{alert_warning};
</script>

<center>
<form action="{action}" enctype="multipart/form-data" name="users_form" method="post">
<input type="hidden" name="uidnumber" 			value="{uidnumber}">
<input type="hidden" name="photo_exist"  		value="{photo_exist}">
<input type="hidden" name="user_context"		value="{user_context}">
<input type="hidden" name="departmentnumber"	value="{departmentnumber}">
<input type="hidden" name="userSamba"			value="{userSamba}">
<input type="hidden" name="defaultLogonScript"	value="{defaultLogonScript}">
<input type="hidden" name="imapDelimiter"		value="{imapDelimiter}">
<input type="hidden" name="minimumSizeLogin"	value="{minimumSizeLogin}">
<input type="hidden" name="defaultDomain"		value="{defaultDomain}">
<input type="hidden" name="ldap_context"		value="{ldap_context}">
<input type="hidden" name="associated_domain" 	value="{associated_domain}"> 

<br />
<table width="90%" border="0" cellspacing="0" cellpading="0">
	<tr>
		<th id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">{lang_general_information}</a></th>
		<th id="tab2" class="activetab" style="display:{display_corporative_information}"onclick="javascript:tab.display(2);"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);">{lang_corporative_information}</a></th>
		<th id="tab3" class="activetab" style="display:{display_emailconfig}"	onclick="javascript:tab.display(3);"><a href="#" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);">{lang_email_config}</a></th>
		<th id="tab4" class="activetab" style="display:{display_groups}"		onclick="javascript:tab.display(4);"><a href="#" tabindex="0" accesskey="4" onfocus="tab.display(4);" onclick="tab.display(4); return(false);">{lang_groups_permission}</a></th>
		<th id="tab5" class="activetab" style="display:{display_emaillists}"	onclick="javascript:tab.display(5);"><a href="#" tabindex="0" accesskey="5" onfocus="tab.display(5);" onclick="tab.display(5); return(false);">{lang_email_lists}</a></th>
		<th id="tab6" class="activetab" style="display:{display_applications}"	onclick="javascript:tab.display(6);"><a href="#" tabindex="0" accesskey="6" onfocus="tab.display(6);" onclick="tab.display(6); return(false);">{lang_aplication_permission}</a></th>
		<th id="tab7" class="activetab" style="display:{display_samba_suport}"	onclick="javascript:tab.display(7);"><a href="#" tabindex="0" accesskey="7" onfocus="tab.display(7);" onclick="tab.display(7); return(false);">{lang_samba_config}</a></th>
	</tr>
</table>
<br />

<!-- The code for General Information Tab -->
<div id="tabcontent1" class="inactivetab">
	<table width="90%" border="0" cellspacing="4">
		<tr bgcolor={row_on}>
			<td>{lang_search_organization}:</td>
			<td><input type="text" id="organization_search" {disabled} autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_info');" onBlur="javascript:sinc_combos_org(context.value); get_associated_domain(context.value); get_available_groups(context.value); get_available_maillists(context.value); get_available_sambadomains(context.value, '{type}')"></td>
		</tr>

		<tr bgcolor={row_off}>
			<td>{lang_organizations}:</td>
			<td><select {disabled} id="ea_combo_org_info" name="context" onchange="javascript:sinc_combos_org(this.value); get_associated_domain(this.value); get_available_groups(this.value); get_available_maillists(this.value); get_available_sambadomains(this.value, '{type}')">{sectors}</select></td>
		</tr>
							
		<tr bgcolor={row_on}>
			<td>{lang_login_id}:</td>
			<td>
				<table border="0" style="{display_input_account_lid}"><tr><td>
					<input type="text" name="uid" id="uid" value="{uid}"autocomplete="off" {disabled} {input_uid_disabled} size=20 onKeyUp="javascript:emailSuggestion_expressoadmin('{use_suggestion_in_logon_script}','{concatenateDomain}')" onblur="users_form.mail.value=users_form.mail1.value;"></input>
				</td>{comment_button}<td><input type="button" value="{lang_generate_login}" onclick="javascript:generate_login(document.getElementById('givenname').value,document.getElementById('sn').value)" ></td>{end_comment_button}</tr></table>
				<spam style="{display_spam_uid}"><font size="3">{uid}</font></spam>
			</td>
			<td>{lang_email}:</td>
			<td><input type="text" name="mail1" id="mail1" autocomplete="off" {disabled} size=42 value="{mail1}" onblur="users_form.mail.value=this.value;"></td>
		</tr>
		
		<tr bgcolor={row_off}>
			<td width="15%">{lang_firstname}:</td>
			<td width="35%"><input type="text" name="givenname" id="givenname" value="{givenname}" autocomplete="off" {disabled} size=42></input>&nbsp;</td>
			<td width="15%">{lang_lastname}:</td>
			<td width="35%"><input type="text" name="sn" id="sn" value="{sn}" autocomplete="off" {disabled} size=42></input>&nbsp;</td>
		</tr>

		<tr bgcolor={row_on}>
			<td width="15%">{lang_password}:</td>
			<td width="35%"><input type="password" name="password1" {disabled_password} size=20></input>&nbsp;</td>
			<td width="15%">{lang_re-password}:</td>
			<td width="35%"><input type="password" name="password2" {disabled_password} size=20></input>&nbsp;</td>
		</tr>
				
		<tr bgcolor={row_on} style='display:{display_tr_default_password}'>
			<td width="15%">&nbsp;</td>
			<td width="35%">
				<input type='button' value='{lang_set_default_users_password}' onclick="javascript:set_user_default_password();">
				<input type='button' value='{lang_return_user_password}' onclick="javascript:return_user_password();">
			</td>
		</tr>

		<tr bgcolor={row_off}>
			<td width="15%">{lang_phone}:</td>
			<td width="35%"><input type="text" name="telephonenumber" id="telephonenumber" value="{telephonenumber}" autocomplete="off" {disable_phonenumber} size=20 maxlength=13 onkeyUp="FormataValor(event, this);"></input>&nbsp;</td>
			<td>&nbsp;</td>
			<td rowspan="4" width="35%" style="display:{display_picture}">
				<img align="center" src="{photo_bin}" id="ea_img_photo" border="0">
				<input type="file" id="ea_input_photo" name="photo" {disabled_edit_photo} size=20><br />
				<input type="checkbox" {disabled_delete_photo} {disabled_edit_photo} name="delete_photo" value="1">{lang_delete_photo}
			</td>
		</tr>

		<tr bgcolor={row_on}>
			<td colspan="2">{lang_password_expired}:</td>
			<td><input onclick="javascript:set_changepassword();" type="checkbox" {passwd_expired_checked} {disabled} name="passwd_expired" id="passwd_expired" value="1"></td>
		</tr>

		<tr bgcolor={row_off}>
			<td colspan="2">{lang_change_password}:</td>
			<td><input type="checkbox" {changepassword_checked} {disabled} name="changepassword" id="changepassword" value="1"></td>
		</tr>

		<tr bgcolor={row_on}>
			<td colspan="2">{lang_account_active}:</td>
			<td><input type="checkbox" {phpgwaccountstatus_checked} {disabled} name="phpgwaccountstatus" id="phpgwaccountstatus" value="1"></td>
		</tr>

		<tr bgcolor={row_off}>
			<td colspan="2">{lang_do_not_show_this_account_in_the_contact_center}:</td>
			<td><input type="checkbox" {phpgwaccountvisible_checked} {disabled} name="phpgwaccountvisible" id="phpgwaccountvisible" value="1"></td>
		</tr>		
		{start_coment_expired}
		<tr bgcolor={row_on}>
					<td colspan="2">{lang_account_expired}:</td>
					<td><input type="radio" {disabled} name="phpgwaccountexpired" id="phpgwaccountexpired" value="1" />{lang_yes}
					<input type="radio" checked="checked" {disabled} name="phpgwaccountexpired" id="phpgwaccountexpired" value="2" />{lang_no}</td>
				</tr>		{end_coment_expired}
		<tr bgcolor={row_on} style="display:{display_access_log_button}">
			<td><input type='button' {disabled} {disabled_access_button} value='{lang_show_access_logs}' onclick="document.location.href='./index.php?menuaction=expressoAdmin.uiaccounts.show_access_log&account_id={uidnumber}';"></td>
		</tr>
	</table>
</div>

<!-- The code for Corporative Information -->
<div id="tabcontent2" class="inactivetab">
	<table width="60%" border="0" cellspacing="4" cellpading="0">
		<tr bgcolor={row_on}>
			<td>{lang_employee_number}:</td>
			<td><input type="text" name="corporative_information_employeenumber" autocomplete="off" value="{corporative_information_employeenumber}" size="30"></td>
		</tr>
		<tr bgcolor={row_off}>
			<td>{lang_cpf}:</td>
			<td><input type="text" name="corporative_information_cpf" autocomplete="off" value="{corporative_information_cpf}" size="30" maxlength=14 onKeyUp="FormataCPF(event, this)"></td>
		</tr>
		<tr bgcolor={row_on}>
			<td>{lang_rg}:</td>
			<td><input type="text" name="corporative_information_rg" autocomplete="off" value="{corporative_information_rg}" size="30"></td>
		</tr>
		<tr bgcolor={row_off}>
			<td>{lang_rguf}:</td>
			<td><input type="text" name="corporative_information_rguf" autocomplete="off" value="{corporative_information_rguf}" size="30"></td>
		</tr>
		<tr bgcolor={row_on}>
			<td>{lang_description}:</td>
			<td><input type="text" name="corporative_information_description" autocomplete="off" value="{corporative_information_description}" size="90"></td>
		</tr>
	</table>
</div>

<!-- The code for Email Config -->
<div id="tabcontent3" class="inactivetab">
	<table width="60%" border="0" cellspacing="4" cellpading="0">
		<tr bgcolor={row_off}>
			<td>{lang_active_email_account}:</td>
			<td><input type="checkbox" {accountstatus_checked} {disabled} name="accountstatus" id="accountstatus" value="1"></td>
		</tr>
	
		<tr bgcolor={row_on}>
			<td>{lang_email}:</td>
			<td><input type="text" name="mail"  id="mail" autocomplete="off" value="{mail1}" {disabled} size=30 onblur="users_form.mail1.value=this.value"></td>
		</tr>

		<tr bgcolor={row_off}>
			<td>{lang_alias_email}:</td>
			<td id="td_input_mailalternateaddress">
				{input_mailalternateaddress_fields}
				<!--<input type="text" name="mailalternateaddress[]" id="mailalternateaddress" autocomplete="off" value="{mailalternateaddress}" {disabled} size=30>-->
				<span style="cursor:pointer" onclick="javascript:add_input_mailalternateaddress();"> +</span>
			</td>
		</tr>

		<tr bgcolor={row_on}>
			<td>{lang_forwarding_email}:</td>
			<td id="td_input_mailforwardingaddress">
				{input_mailforwardingaddress_fields}
				<!--<input type="text" name="mailforwardingaddress[]" id="mailforwardingaddress" autocomplete="off" value="{mailforwardingaddress}" {disabled} size=30>-->
				<span style="cursor:pointer" onclick="javascript:add_input_mailforwardingaddress();"> +</span>
			</td>
		</tr>

		<tr bgcolor={row_off}>
			<td>{lang_only_forwarding}:</td>
			<td><input type="checkbox" {deliverymode_checked} {disabled} name="deliverymode" id="deliverymode" value="1"></td>
		</tr>

		<tr bgcolor={row_on}>
			<td>{lang_email_quota_in_MB}:</td>
			<td><input type="text" name="mailquota" autocomplete="off" value="{mailquota}" {changequote_disabled} {disabled} size=10></td>
		</tr>
		
		<tr bgcolor={row_off}  style="display:{display_quota_used}">
			<td>{lang_quota_used_in_mb}:</td>
			<td><input type="text" name="mailquota_used" id="mailquota_used" value="{mailquota_used}" disabled size=10></td>
		</tr>

		<tr bgcolor={row_on} style="display:{display_empty_user_inbox}">
			<td><input type='button' {disabled} {disabled_empty_inbox} value='{lang_empty_user_inbox}' onclick="javascript:empty_inbox(uid.value);"></td>
		</tr>

	</table>
</div>

<!-- The code for Group -->
<div id="tabcontent4" class="inactivetab">
	<table width="60%" border="0" cellspacing="4" cellpading="0">
		<tr bgcolor={row_on}>
			
			<td valign="bottom">
				<table width="100%" border="0">
					<tr>
						<td width="40%">
							<br />{lang_user_groups}<br />
							<select id="ea_select_user_groups" size="13" style="width: 400px" multiple name="groups[]">{ea_select_user_groups_options}</select>
						</td>
					</tr>
				</table>
			</td>

			<td width="20%" align="center">
				<button type="button" {disable_group} onClick="javascript:add_user2group();"><img src="expressoAdmin/templates/default/images/add.png" style="vertical-align: middle;" >&nbsp;{lang_add}</button>
				<br /><br />
				<button type="button" {disable_group} onClick="javascript:remove_user2group();"><img src="expressoAdmin/templates/default/images/rem.png" style="vertical-align: middle;" >&nbsp;{lang_remove}</button>
			</td>

			<td>
				<table width="100%" border="0">
					<tr>
						<td>
							{lang_search_organization}:
							<br />
							<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_groups');" onblur="javascript:get_available_groups(ea_combo_org_groups.value);">
							<br />
							{lang_organizations}:
							<br />
							<select {disable_group} id="ea_combo_org_groups" name="ea_combo_org_groups" onchange="javascript:get_available_groups(this.value);">{combo_organizations}</select>
						</td>
					</tr>

					<tr>
						<td>
							{lang_search_for_group}:<br />
							<input {disable_group} id="ea_input_searchGroup" size="35" autocomplete="off" onkeyup="javascript:optionFinderTimeout_group(this, event)"><br />
							<font color="red"><span id="ea_span_searching_group">&nbsp;<span></font>
							<br />
						</td>
					</tr>
					
					<tr>
						<td width="40%">
							{lang_available_groups}<br />
							<select {disable_group} id="ea_select_available_groups" size="13" style="width:400px" multiple></select>
						</td>
					</tr>
				</table>
			</td>		     			     
		</tr>
		
		<tr height="30" bgcolor="{row_off}">
			<td colspan="4" align="left">
				{lang_primary_group}:
				<select id="ea_combo_primary_user_group" name="gidnumber" {disabled_samba}>{ea_combo_primary_user_group_options}</select>
			</td>
		</tr>
	</table>
</div>

<!-- The code for lists Email -->
<div id="tabcontent5" class="inactivetab">
	<table width="60%" border="0" cellspacing="4" cellpading="0">
		<tr bgcolor={row_on}>
		
			<td valign="bottom">
				<table width="100%" border="0">
					<tr>
						<td width="40%">
							<br />{lang_the_user_is_part_of_this_email_lists}:
							<select id="ea_select_user_maillists" size="13" style="width: 400px" multiple name="maillists[]">{ea_select_user_maillists_options}</select>
						</td>
					</tr>
				</table>
			</td>
			
			<td width="20%" align="center">
				<button type="button" {disabled} onClick="javascript:add_user2maillist();"><img src="expressoAdmin/templates/default/images/add.png" style="vertical-align: middle;" >&nbsp;{lang_add}</button>
				<br /><br />
				<button type="button" {disabled} onClick="javascript:remove_user2maillist();"><img src="expressoAdmin/templates/default/images/rem.png" style="vertical-align: middle;" >&nbsp;{lang_remove}</button>
			</td>
			
			<td>
				<table width="100%" border="0">
					<tr>
						<td>
							{lang_search_organization}:
							<br />
							<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_maillists');" onblur="javascript:get_available_maillists(ea_combo_org_maillists.value);">
							<br />
							{lang_organizations}:
							<br />
							<select {disabled} id="ea_combo_org_maillists" name="ea_combo_org_maillists" onchange="javascript:get_available_maillists(this.value);">{combo_all_orgs}</select>
						</td>
					</tr>
					<tr>
						<td>
							{lang_search_email_list}:<br />
							<input {disabled} id="ea_input_searchMailList" size="35" autocomplete="off" onkeyup="javascript:optionFinderTimeout_maillist(this, event)"><br />
							<font color="red"><span id="ea_span_searching_maillist">&nbsp;<span></font>
							<br />
						</td>
					</tr>
					<tr>
						<td width="40%">
							{lang_available_mail_lists}:<br />
							<select id="ea_select_available_maillists" size="13" style="width: 400px" multiple {disabled}>{account_lists}</select>
						</td>
					</tr>
				</table>
			</td>
			
		</tr>
	</table>
</div>

<!-- The code for Apps Tab -->
<div id="tabcontent6" class="inactivetab">
	<table id="ea_table_apps" width="80%" border="0" cellspacing="2" cellpading="0">
		{apps}
	</table>
</div>

<!-- The code for SAMBA -->
<!--<div id="tabcontent7" class="inactivetab" style="{display_samba_suport}">-->
<div id="tabcontent7" class="inactivetab">
	<table width="60%" border="0" cellspacing="4" cellpading="0">
		<tr bgcolor={row_on}>
			<td>{lang_use_samba_attributes}:</td>
			<td><input {use_attrs_samba_checked} {disabled_samba} name="use_attrs_samba" type="checkbox" id="use_attrs_samba" onChange="javascript:use_samba_attrs(this.checked)"></td>
		</tr>
		<tr bgcolor={row_off}>
			<td>{lang_account_type}:</td>
			<td>
				<select {disabled_samba} name="sambaacctflags">
					<option value="[U          ]" {active_user_selected}>{lang_active_user}</option>
					<option value="[DU         ]" {desactive_user_selected}>{lang_desactive_user}</option>
				</select>
			</td>
		</tr>
		<tr bgcolor={row_on}>
			<td>{lang_domain}:</td>
			<td>
				<select {disabled_samba} name="sambadomain" id="ea_combo_sambadomains">
					{sambadomainname_options}
				</select>
			</td>
		</tr>
		<tr bgcolor={row_off}>
			<td>{lang_logon_script}:</td>
			<td><input {disabled_samba} type="text" name="sambalogonscript" autocomplete="off" value="{sambalogonscript}" size="30"></td>
		</tr>
		<tr bgcolor={row_on}>
			<td>{lang_home_directory}:</td>
			<td>
				<input {disabled_samba} type="text" name="sambahomedirectory" autocomplete="off" value="{sambahomedirectory}" size="30">
			</td>
		</tr>
	</table>
</div>
<!-- End Tabs -->

<br /><br />
<table width="90%" border="0" cellspacing="0" cellpading="1">
	<tr>	
		<td width="90%" align="left"  class="td_left" bgcolor="{color_bg1}">
			<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
		</td>
		<td width="10%" align="right" class="td_right" bgcolor="{color_bg1}">
			<input type="button" value="{lang_save}" onClick="javascript:validate_fields('{type}');">
		</td>
	</tr>
</table>

<script type="text/javascript">
tab.display(1);
// Note: The "change_password" field must be disabled and checked when the "passwd_expired" field will be enabled.  
set_changepassword(); 
</script>

</form>
</center>
<!-- END main -->
