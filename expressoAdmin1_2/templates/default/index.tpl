<!-- BEGIN body -->
<table width="90%" border="0" cellspacing="10" cellpadding="0" align="center"> 
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/configurations.png' alt="{lang_configurations}" />
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin1_2.uiconfiguration.index">{lang_configurations}</a>
  		</td>
  	</tr>
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/user.png' alt="lang_user_accounts" />
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin1_2.uiaccounts.list_users">{lang_user_accounts}</a>
  		</td>
  	</tr> 
	<tr>
		<td width="1%" align="center">
			<img height='24' width='24' src='./templates/default/images/institutional_account.png' alt="{lang_institutional_accounts}" />
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin1_2.institutional_accounts.index">{lang_institutional_accounts}</a>
  		</td>
  	</tr>
<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/mail_share.png' alt="{lang_shared_accounts}" />
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin1_2.uishared_accounts.index">{lang_shared_accounts}</a>
  		</td>
  	</tr>
        <tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/calendar.png' alt="{lang_assing_calendar}" />
  		</td>
  		<td>
                    <a href="#" onclick="assing_calendar_user()">{lang_assing_calendar}</a>
  		</td>
  	</tr>  
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/group.png' alt="{lang_user_groups}" />
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.uigroups.list_groups">{lang_user_groups}</a>
		</td>
	</tr> 
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/mail_list.png' alt="{lang_email_lists}" />
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists">{lang_email_lists}</a>
		</td>
	</tr> 
	<tr style={display_samba_suport}>
		<td width="1%" align="center">
			<img src='./templates/default/images/computer.png' alt="{lang_computers}"/>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.uicomputers.list_computers">{lang_computers}</a>
		</td>
	</tr> 
	<tr style={display_samba_suport}>
		<td width="1%" align="center">
			<img src='./templates/default/images/samba.png' alt="{lang_sambadomains}" />
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.uidomains.list_domains">{lang_sambadomains}</a>
		</td>
	</tr> 
    
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/message_size.png' alt="{lang_messages_size}"/>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.uimessages_size.index">{lang_messages_size}</a>
		</td>
	</tr>
	
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/sectors.png' alt="{lang_organizations}" />
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.uisectors.list_sectors">{lang_organizations}</a>
		</td>
	</tr> 
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/sessions.png' alt="{lang_show_sessions}" />
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.totalsessions.show_total_sessions">{lang_show_sessions}</a>
		</td>
	</tr>
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/logs.png' alt="{lang_logs}"/>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin1_2.uilogs.list_logs">{lang_logs}</a>
		</td>
	</tr>
</table>
                <div class="hidden" id="assingCalendar"/>
<!-- END body -->
