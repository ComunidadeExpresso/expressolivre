<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center">
   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr bgcolor="{row_on}">
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr bgcolor="{row_off}">
    <td colspan="2"><b>{lang_ExpressoMail_settings}</b></td>
   </tr>
	<tr class="{row_off}">
    <td>{lang_Would_you_like_to_use_local_messages_?}:</td>
    <td>
     <select name="newsettings[enable_local_messages]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_enable_local_messages_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>
   <tr bgcolor="{row_on}">
    <td>{lang_Do_you_want_to_log_the_sent_messages?}</td>
    <td>
     <select name="newsettings[expressoMail_enable_log_messages]">
      <option value=""{selected_expressoMail_enable_log_messages_False}>{lang_No}</option>
      <option value="True"{selected_expressoMail_enable_log_messages_True}>{lang_Yes}</option>
     </select>&nbsp;&nbsp;&nbsp;path: /home/expresso/mail_senders.log
    </td>
   </tr>
   <tr bgcolor="{row_off}">
   <td>{lang_Do_you_want_to_cache_php_requests_in_javascript?}</td>
   <td>
   <select name="newsettings[expressoMail_enable_cache]">
   <option value=""{selected_expressoMail_enable_cache_False}>{lang_No}</option>
   <option value="True"{selected_expressoMail_enable_cache_True}>{lang_Yes}</option>
   </select>
   </td>
   </tr>
   <tr bgcolor="{row_on}">
   <td>{lang_Do_you_want_to_use_important_flag_in_email_editor?}</td>
   <td>
   <select name="newsettings[expressoMail_enable_important_flag]">
   <option value=""{selected_expressoMail_enable_important_flag_False}>{lang_No}</option>  
   <option value="True"{selected_expressoMail_enable_important_flag_True}>{lang_Yes}</option>
   </select>
   </td>
   </tr>
   <tr bgcolor="{row_off}">
   <td>{lang_Do_you_want_to_use_remove_attachments_function?}</td>
   <td>
   <select name="newsettings[expressoMail_remove_attachments_function]">
   <option value=""{selected_expressoMail_remove_attachments_function_False}>{lang_No}</option>
   <option value="True"{selected_expressoMail_remove_attachments_function_True}>{lang_Yes}</option>
   </select>
   </td>
   </tr>
   <tr bgcolor="{row_on}">
   <td>{lang_Do_you_want_to_use_the_spam_filter?}</td>
   <td>
   <select name="newsettings[expressoMail_use_spam_filter]">
   <option value=""{selected_expressoMail_use_spam_filter_False}>{lang_No}</option>
   <option value="True"{selected_expressoMail_use_spam_filter_True}>{lang_Yes}</option>
   </select>
    </td>
   </tr>
   <tr bgcolor="{row_off}">
    <td>{lang_Command_for_spam}</td>
    <td>
    <input type="text" name="newsettings[expressoMail_command_for_spam]" value="{value_expressoMail_command_for_spam}" size="60" /> 
    </td>
    <tr bgcolor="{row_on}">
    <td>{lang_Command_for_unmark_spam}</td>
    <td>
    <input size="60" name="newsettings[expressoMail_command_for_ham]" value="{value_expressoMail_command_for_ham}"> 
    </td>
    </tr>
    <tr bgcolor="{row_off}">
    <td>{lang_Always_confirm_notification_to_these_domains}</td>
    <td>
    <input size="60" name="newsettings[expressoMail_notification_domains]" value="{value_expressoMail_notification_domains}">
    </td>
    </tr>
    <tr bgcolor="{row_on}">
    <td>{lang_Allowed_domains_for_sieve_forwarding} ({lang_Comma_separated})</td>
    <td>
    <input size="60" name="newsettings[expressoMail_sieve_forward_domains]" value="{value_expressoMail_sieve_forward_domains}">
    </td>
    </tr>
    <tr bgcolor="{row_off}">
    <td>{lang_Number_of_dynamic_contacts}</td>
    <td>
    <input size="1" name="newsettings[expressoMail_Number_of_dynamic_contacts]" value="{value_expressoMail_Number_of_dynamic_contacts}"> 
    </td>
    </tr>
    <tr bgcolor="{row_off}">
        <td>{lang_gears_firefox_windows_url}</td>
        <td>
        <input size="60" name="newsettings[expressoMail_gears_firefox_windows]" value="{value_expressoMail_gears_firefox_windows}">
        </td>
    </tr>
    <tr bgcolor="{row_on}">
        <td>{lang_gears_firefox_linux_url}</td>
        <td>
        <input size="60" name="newsettings[expressoMail_gears_firefox_linux]" value="{value_expressoMail_gears_firefox_linux}">
        </td>
    </tr>
   <tr bgcolor="{row_off}">
        <td>{lang_gears_ie_url}</td>
        <td>
        <input size="60" name="newsettings[expressoMail_gears_ie]" value="{value_expressoMail_gears_ie}">
        </td>
    </tr>
<!-- END body -->
<!-- BEGIN footer -->
  <tr bgcolor="{th_bg}">
    <td colspan="2">
&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
