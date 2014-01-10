<!-- BEGIN body -->
<table width="90%" border="0" cellspacing="10" cellpadding="0" align="center"> 
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/configurations.png'>
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin.uiconfiguration.index">{lang_configurations}</a>
  		</td>
  	</tr>
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/user.png'>
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin.uiaccounts.list_users">{lang_user_accounts}</a>
  		</td>
  	</tr> 
	<tr>
		<td width="1%" align="center">
			<img HEIGHT='24' WIDTH='24' src='./templates/default/images/institutional_account.png'>
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin.institutional_accounts.index">{lang_institutional_accounts}</a>
  		</td>
  	</tr>
<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/mail_share.png'>
  		</td>
  		<td>
  			<a href="../index.php?menuaction=expressoAdmin.uishared_accounts.index">{lang_shared_accounts}</a>
  		</td>
  	</tr>
        <tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/calendar.png'>
  		</td>
  		<td>
                    <a href="#" onclick="assing_calendar_user()">{lang_assing_calendar}</a>
  		</td>
  	</tr>  
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/group.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.uigroups.list_groups">{lang_user_groups}</a>
		</td>
	</tr> 
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/mail_list.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.uimaillists.list_maillists">{lang_email_lists}</a>
		</td>
	</tr> 
	<tr style={display_samba_suport}>
		<td width="1%" align="center">
			<img src='./templates/default/images/computer.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.uicomputers.list_computers">{lang_computers}</a>
		</td>
	</tr> 
	<tr style={display_samba_suport}>
		<td width="1%" align="center">
			<img src='./templates/default/images/samba.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.uidomains.list_domains">{lang_sambadomains}</a>
		</td>
	</tr> 
    
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/message_size.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.uimessages_size.index">{lang_messages_size}</a>
		</td>
	</tr>
	
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/sectors.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.uisectors.list_sectors">{lang_organizations}</a>
		</td>
	</tr> 
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/sessions.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.totalsessions.show_total_sessions">{lang_show_sessions}</a>
		</td>
	</tr>
	<tr>
		<td width="1%" align="center">
			<img src='./templates/default/images/logs.png'>
		</td>
		<td>
			<a href="../index.php?menuaction=expressoAdmin.uilogs.list_logs">{lang_logs}</a>
		</td>
	</tr>
</table>
                <div class="hidden" id="assingCalendar"/>
<!-- END body -->
