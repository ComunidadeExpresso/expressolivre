<!-- BEGIN main -->
<center>
<form action="{action_url}" name="mailsettings" method="post">
<br>
<table width="88%" border="0" cellspacing="0" cellpading="0">
	<tr>
		<th id="tab1" class="activetab"  style="width: 29%;" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">Global</a></th>
		<th id="tab2" class="activetab"  style="width: 29%;" onclick="javascript:tab.display(2);"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);">SMTP</a></th>
		<th id="tab3" class="activetab"  style="width: 29%;" onclick="javascript:tab.display(3);"><a href="#" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);">POP3/IMAP</a></th>
		<!-- <th id="tab4" class="activetab" onclick="javascript:tab.display(4);"><a href="#" tabindex="0" accesskey="4" onfocus="tab.display(4);" onclick="tab.display(4); return(false);">extern</a></th> -->
	</tr>
</table>
<br><br>


<!-- The code for Global Tab -->

<div id="tabcontent1" class="inactivetab">
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="row_off">
			<td width="50%" class="td_left">
				{lang_profile_name}:
			</td>
			<td width="50%" class="td_right">
				<input type="text" size="30" name="globalsettings[description]" value="{value_description}">
			</td>
		</tr>
		<tr class="row_on">
			<td width="50%" class="td_left">
				{lang_default_domain}:
			</td>
			<td width="50%" class="td_right">
				<input type="text" size="30" name="globalsettings[defaultDomain]" value="{value_defaultDomain}">
			</td>
		</tr>
		<tr class="row_off">
			<td width="50%" class="td_left">
				{lang_organisation_name}:
			</td>
			<td width="50%" class="td_right">
				<input type="text" size="30" name="globalsettings[organisationName]" value="{value_organisationName}">
			</td>
		</tr>
		<tr class="row_on">
			<td width="50%" class="td_left">
				{lang_user_defined_accounts}:
			</td>
			<td width="50%" class="td_right">
				<input type="checkbox" name="globalsettings[userDefinedAccounts]" {selected_userDefinedAccounts} value="yes">
			</td>
		</tr>
	</table>
</div>


<!-- The code for SMTP Tab -->

<div id="tabcontent2" class="inactivetab">
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_Select_type_of_SMTP_Server}<b>
			</td>
			<td width="50%" align="left" class="td_right">
				<select name="smtpsettings[smtpType]" id="smtpselector" size="1" onchange="javascript:smtp.display(this.value);">
					<option value="1" {selected_smtpType_1}>{lang_smtp_option_1}</option>
					<option value="2" {selected_smtpType_2}>{lang_smtp_option_2}</option>
				</select>
			</td>
		</tr>
	</table>
	
	
	<!-- The code for standard SMTP Server -->
	
	<div id="smtpcontent1" class="inactivetab">
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="50%" class="td_left">{lang_SMTP_server_hostname_or_IP_address}:</td>
				<td width="50%" class="td_right"><input name="smtpsettings[1][smtpServer]" size="40" value="{value_smtpServer}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">{lang_SMTP_server_port}:</td>
				<td class="td_right"><input name="smtpsettings[1][smtpPort]" maxlength="5" size="5" value="{value_smtpPort}"></td>
			</tr>
			
			<tr class="row_off">
				<td class="td_left">{lang_Use_SMTP_auth}:</td>
				<td class="td_right">
					<input type="checkbox" name="smtpsettings[1][smtpAuth]" {selected_smtpAuth} value="yes">
				</td>
			</tr>
		</table>
	</div>
	
	
	<!-- The code for Postfix/LDAP Server -->
	
	<div id="smtpcontent2" class="inactivetab">
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="50%" class="td_left">{lang_SMTP_server_hostname_or_IP_address}:</td>
				<td width="50%" class="td_right"><input name="smtpsettings[2][smtpServer]" size="40" value="{value_smtpServer}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">{lang_SMTP_server_port}:</td>
				<td class="td_right"><input name="smtpsettings[2][smtpPort]" maxlength="5" size="5" value="{value_smtpPort}"></td>
			</tr>
			
			<tr class="row_off">
				<td class="td_left">{lang_Use_SMTP_auth}:</td>
				<td class="td_right">
					<input type="checkbox" name="smtpsettings[2][smtpAuth]" {selected_smtpAuth} value="yes">
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="th">
				<td width="50%" class="td_left">
					<b>{lang_LDAP_settings}<b>
				</td>
				<td class="td_right">
					&nbsp;
				</td>
			</tr>
			<tr class="row_off">
				<td class="td_left">{lang_use_LDAP_defaults}:</td>
				<td class="td_right">
					<input type="checkbox" name="smtpsettings[2][smtpLDAPUseDefault]" {selected_smtpLDAPUseDefault} value="yes">
				</td>
			</tr>
			<tr class="row_on">
				<td width="50%" class="td_left">{lang_LDAP_server_hostname_or_IP_address}:</td>
				<td width="50%" class="td_right"><input name="smtpsettings[2][smtpLDAPServer]" maxlength="80" size="40" value="{value_smtpLDAPServer}"></td>
			</tr>
			
			<tr class="row_off">
				<td class="td_left">{lang_LDAP_server_admin_dn}:</td>
				<td class="td_right"><input name="smtpsettings[2][smtpLDAPAdminDN]" maxlength="200" size="40" value="{value_smtpLDAPAdminDN}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">{lang_LDAP_server_admin_pw}:</td>
				<td class="td_right"><input type="password" name="smtpsettings[2][smtpLDAPAdminPW]" maxlength="30" size="40" value="{value_smtpLDAPAdminPW}"></td>
			</tr>

			<tr class="row_off">
				<td class="td_left">{lang_LDAP_server_base_dn}:</td>
				<td class="td_right"><input name="smtpsettings[2][smtpLDAPBaseDN]" maxlength="200" size="40" value="{value_smtpLDAPBaseDN}"></td>
			</tr>
		</table>
	</div>
</div>


<!-- The code for IMAP/POP3 Tab -->

<div id="tabcontent3" class="inactivetab">
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_select_type_of_imap/pop3_server}</b>
			</td>
			<td width="50%" align="left" class="td_right">
				<select name="imapsettings[imapType]" id="imapselector" size="1" onchange="javascript:imap.display(this.value);">
					<option value="1" {selected_imapType_1}>{lang_imap_option_1}</option>
					<option value="2" {selected_imapType_2}>{lang_imap_option_2}</option>
					<option value="3" {selected_imapType_3}>{lang_imap_option_3}</option>
				</select>
			</td>
		</tr>
	</table>


	<!-- The code for standard POP3 Server -->
	
	<div id="imapcontent1" class="inactivetab">
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="50%" class="td_left">{lang_pop3_server_hostname_or_IP_address}:</td>
				<td width="50%" class="td_right"><input name="imapsettings[1][imapServer]" maxlength="80" size="40" value="{value_imapServer}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">{lang_pop3_server_port}:</td>
				<td class="td_right"><input name="imapsettings[1][imapPort]" maxlength="5" size="5" value="{value_imapPort}"></td>
			</tr>
			
			<tr class="row_off">
				<td class="td_left">{lang_imap_server_logintyp}:</td>
				<td class="td_right">
					<select name="imapsettings[1][imapLoginType]" size="1">
						<option value="standard" {selected_imapLoginType_standard}>{lang_standard}</option>
						<option value="vmailmgr" {selected_imapLoginType_vmailmgr}>{lang_vmailmgr}</option>
					</select>
				</td>

			</tr>

			<tr class="row_on">
				<td class="td_left">{lang_use_tls_encryption}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[1][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr class="row_off">
				<td class="td_left">{lang_use_tls_encryption}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[1][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr class="row_on">
				<td class="td_left">{lang_pre_2001_c_client}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[1][imapoldcclient]" {selected_imapoldcclient} value="yes">
				</td>
			</tr>
		</table>
	</div>
	

	<!-- The code for standard IMAP Server -->
	
	<div id="imapcontent2" class="inactivetab">
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="50%" class="td_left">{lang_imap_server_hostname_or_IP_address}:</td>
				<td width="50%" class="td_right"><input name="imapsettings[2][imapServer]" maxlength="80" size="40" value="{value_imapServer}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">{lang_imap_server_port}:</td>
				<td class="td_right"><input name="imapsettings[2][imapPort]" maxlength="5" size="5" value="{value_imapPort}"></td>
			</tr>
			
			<tr class="row_off">
				<td class="td_left">{lang_imap_server_logintyp}:</td>
				<td class="td_right">
					<select name="imapsettings[2][imapLoginType]" size="1">
						<option value="standard" {selected_imapLoginType_standard}>{lang_standard}</option>
						<option value="vmailmgr" {selected_imapLoginType_vmailmgr}>{lang_vmailmgr}</option>
					</select>
				</td>

			</tr>

			<tr class="row_on">
				<td class="td_left">{lang_use_tls_encryption}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[2][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr class="row_off">
				<td class="td_left">{lang_use_tls_authentication}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[2][imapTLSAuthentication]" {selected_imapTLSAuthentication} value="yes">
				</td>
			</tr>

			<tr class="row_on">
				<td class="td_left">{lang_pre_2001_c_client}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[2][imapoldcclient]" {selected_imapoldcclient} value="yes">
				</td>
			</tr>
		</table>
	</div>
	

	<!-- The code for the Cyrus IMAP Server -->
	
	<div id="imapcontent3" class="inactivetab">
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="row_off">
				<td width="50%" class="td_left">{lang_imap_server_hostname_or_IP_address}:</td>
				<td width="50%" class="td_right"><input name="imapsettings[3][imapServer]" maxlength="80" size="40" value="{value_imapServer}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">{lang_imap_server_port}:</td>
				<td class="td_right"><input name="imapsettings[3][imapPort]" maxlength="5" size="5" value="{value_imapPort}"></td>
			</tr>
			
			<tr class="row_on">
				<td class="td_left">Delimitador das pastas do servidor IMAP: (para pontos nos logins, use / como delimitador.)</td>
				<td class="td_right">
					<select name="imapsettings[3][imapDelimiter]" size="1">
						<option value="." {selected_imapDelimiter_dot}>.</option>
						<option value="/" {selected_imapDelimiter_slash}>/</option>
					</select>
				</td>
			</tr>
			
			<tr class="row_off">
				<td class="td_left">{lang_imap_server_logintyp}:</td>
				<td class="td_right">
					<select name="imapsettings[3][imapLoginType]" size="1">
						<option value="standard" {selected_imapLoginType_standard}>{lang_standard}</option>
						<option value="vmailmgr" {selected_imapLoginType_vmailmgr}>{lang_vmailmgr}</option>
					</select>
				</td>

			</tr>

			<tr class="row_on">
				<td class="td_left">{lang_use_tls_encryption}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[3][imapTLSEncryption]" {selected_imapTLSEncryption} value="yes">
				</td>
			</tr>

			<tr class="row_off">
				<td class="td_left">{lang_use_tls_authentication}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[3][imapTLSAuthentication]" {selected_imapTLSAuthentication} value="yes">
				</td>
			</tr>

			<tr class="row_on">
				<td class="td_left">{lang_pre_2001_c_client}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[3][imapoldcclient]" {selected_imapoldcclient} value="yes">
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="th">
				<td width="50%" class="td_left">
					<b>{lang_cyrus_imap_administration}<b>
				</td>
				<td class="td_right">
					&nbsp;
				</td>
			</tr>
			<tr class="row_off">
				<td class="td_left">{lang_enable_cyrus_imap_administration}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[3][imapEnableCyrusAdmin]" {selected_imapEnableCyrusAdmin} value="yes">
				</td>
			</tr>
			<tr class="row_on">
				<td width="50%" class="td_left">{lang_admin_username}:</td>
				<td width="50%" class="td_right"><input name="imapsettings[3][imapAdminUsername]" maxlength="40" size="40" value="{value_imapAdminUsername}"></td>
			</tr>

			<tr class="row_off">
				<td class="td_left">{lang_admin_password}:</td>
				<td class="td_right"><input type="password" name="imapsettings[3][imapAdminPW]" maxlength="40" size="40" value="{value_imapAdminPW}"></td>
			</tr>
		</table>
		<table>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="th">
				<td width="50%" class="td_left">
					<b>{lang_sieve_settings}<b>
				</td>
				<td class="td_right">
					&nbsp;
				</td>
			</tr>
			<tr class="row_off">
				<td class="td_left">{lang_enable_sieve}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[3][imapEnableSieve]" {selected_imapEnableSieve} value="yes">
				</td>
			</tr>
			<tr class="row_on">
				<td width="50%" class="td_left">{lang_sieve_server_hostname_or_ip_address}:</td>
				<td width="50%" class="td_right"><input name="imapsettings[3][imapSieveServer]" maxlength="80" size="40" value="{value_imapSieveServer}"></td>
			</tr>

			<tr class="row_off">
				<td class="td_left">{lang_sieve_server_port}:</td>
				<td class="td_right"><input name="imapsettings[3][imapSievePort]" maxlength="5" size="5" value="{value_imapSievePort}"></td>
			</tr>
		</table>
		<table>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
		<table width="88%" border="0" cellspacing="0" cellpading="1">
			<tr class="th">
				<td width="50%" class="td_left">
					<b>{lang_spam_settings}<b>
				</td>
				<td class="td_right">
					&nbsp;
				</td>
			</tr>
			<tr class="row_off">
				<td class="td_left">{lang_create_spam_folder}:</td>
				<td class="td_right">
					<input type="checkbox" name="imapsettings[3][imapCreateSpamFolder]" {selected_imapCreateSpamFolder} value="yes">
				</td>
			</tr>
			<tr class="row_on">
				<td width="50%" class="td_left">{lang_cyrus_user_post_spam}:</td>
				<td width="50%" class="td_right"><input name="imapsettings[3][imapCyrusUserPostSpam]" maxlength="80" size="40" value="{value_imapCyrusUserPostSpam}"></td>
			</tr>			
		</table>
		<table>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>

		<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_default_folders}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td class="td_left">{lang_trash_folder}:</td>
			<td class="td_right">
				<input name="imapsettings[3][imapDefaultTrashFolder]" size="40" value={value_imapDefaultTrashFolder}>
			</td>
		</tr>
		<tr class="row_on">
			<td class="td_left">{lang_sent_folder}:</td>
			<td class="td_right">
				<input name="imapsettings[3][imapDefaultSentFolder]" size="40" value={value_imapDefaultSentFolder}>
			</td>
		</tr>
		<tr class="row_off">
			<td class="td_left">{lang_drafts_folder}:</td>
			<td class="td_right">
				<input name="imapsettings[3][imapDefaultDraftsFolder]" size="40" value={value_imapDefaultDraftsFolder}>
			</td>
		</tr>
		<tr class="row_on">
			<td class="td_left">{lang_spam_folder}:</td>
			<td class="td_right">
				<input name="imapsettings[3][imapDefaultSpamFolder]" size="40" value={value_imapDefaultSpamFolder}>
			</td>
		</tr>


		</table>
	</div>
	
	
</div>


<!-- The code for External Tab -->

<!-- <div id="tabcontent4" class="inactivetab">
	<h1>still something todo ...</h1>
	<p>Come back later!!</p>
</div> -->


<br><br>
<table width="90%" border="0" cellspacing="0" cellpading="1">
	<tr>
		<td width="90%" align="left"  class="td_left">
			<a href="{back_url}">{lang_back}</a>
		</td>
		<td width="10%" align="center" class="td_right">
			<a href="javascript:document.mailsettings.submit();">{lang_save}</a>
		</td>
	</tr>
</table>
</form>
</center>
<!-- END main -->

