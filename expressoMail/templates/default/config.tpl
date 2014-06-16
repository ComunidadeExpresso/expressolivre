<!-- BEGIN header -->
<script type="text/javascript"> 
function valida(pForm) 
{ 
        if((!document.getElementsByName('newsettings[expressoMail_Max_attachment_size]').item(0).value) || (!pForm.php_upload_limit.value)) 
                return ExpressoLivre.form(pForm); 
 
        if (parseInt(document.getElementsByName('newsettings[expressoMail_Max_attachment_size]').item(0).value) > parseInt(pForm.php_upload_limit.value)) 
        { 
                alert(pForm.valida_alert.value); 
                return false; 
        }
        
        if(isNaN(document.getElementsByName('newsettings[expressoMail_time_memcache]').item(0).value))
        {
				alert(pForm.valida_alert_MemCache.value);
				return false;
        } 
        else 
                return ExpressoLivre.form(pForm); 
} 
</script> 
<form method="POST" action="{action_url}" onsubmit="return valida( this );"> 
<input type="hidden" name="php_upload_limit" value="{php_upload_limit}" /> 
<table border="0" align="center">
   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
	<tr bgcolor="{th_err}">
		<td colspan="2"><b>{error}</b></td>
	</tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr bgcolor="{row_on}">
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr bgcolor="{row_off}">
    <td colspan="2"><b>{lang_ExpressoMail_settings}</b></td>
   </tr>
   <tr class="{row_on}">
    <td>{lang_Would_you_like_to_use_expresso_offline?}:</td>
    <td>
     <select name="newsettings[enable_expresso_offline]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_enable_expresso_offline_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>
   <tr bgcolor="{row_off}">
    <td>{lang_Do_you_want_to_enable_expressoMail_log?}</td>
    <td>
     <select name="newsettings[expressoMail_enable_log_messages]">
      <option value=""{selected_expressoMail_enable_log_messages_False}>{lang_No}</option>
      <option value="True"{selected_expressoMail_enable_log_messages_True}>{lang_Yes}</option>
     </select>&nbsp;&nbsp;&nbsp;path: /home/expressolivre/
    </td>
   </tr>
   <tr bgcolor="{row_on}">
   <td>{lang_Do_you_want_to_cache_php_requests_in_javascript?}</td>
   <td>
   <select name="newsettings[expressoMail_enable_cache]">
   <option value=""{selected_expressoMail_enable_cache_False}>{lang_No}</option>
   <option value="True"{selected_expressoMail_enable_cache_True}>{lang_Yes}</option>
   </select>
   </td>
   </tr>
   <tr bgcolor="{row_off}">
   <td>{lang_Do_you_want_to_use_the_spam_filter?}</td>
   <td>
   <select name="newsettings[expressoMail_use_spam_filter]">
   <option value=""{selected_expressoMail_use_spam_filter_False}>{lang_No}</option>
   <option value="True"{selected_expressoMail_use_spam_filter_True}>{lang_Yes}</option>
   </select>
    </td>
   </tr>
   <tr bgcolor="{row_on}">
    <td>{lang_Command_for_spam}</td>
    <td>
    <input type="text" name="newsettings[expressoMail_command_for_spam]" value="{value_expressoMail_command_for_spam}" size="60" /> 
    </td>
	</tr>
    <tr bgcolor="{row_off}">
    <td>{lang_Command_for_unmark_spam}</td>
    <td>
    <input size="60" name="newsettings[expressoMail_command_for_ham]" value="{value_expressoMail_command_for_ham}"> 
    </td>
    </tr>
    <tr bgcolor="{row_on}">
    <td>{lang_reliable_domains}</td>
    <td>
    <input size="60" name="newsettings[expressoMail_notification_domains]" value="{value_expressoMail_notification_domains}">
    </td>
    </tr>
    <tr bgcolor="{row_off}">
    <td>{lang_Allowed_domains_for_sieve_forwarding} ({lang_Comma_separated})</td>
    <td>
    <input size="60" name="newsettings[expressoMail_sieve_forward_domains]" value="{value_expressoMail_sieve_forward_domains}">
    </td>
    </tr>
    <tr bgcolor="{row_on}">
    <td>{lang_Number_of_dynamic_contacts}</td>
    <td>
    <input size="1" name="newsettings[expressoMail_Number_of_dynamic_contacts]" value="{value_expressoMail_Number_of_dynamic_contacts}"> 
    </td>
    </tr>
    <tr bgcolor="{row_on}">
    <td>{lang_imap_max_folders}:</td>
    <td>
    <input size="2" name="newsettings[expressoMail_imap_max_folders]" value="{value_expressoMail_imap_max_folders}">
    </td>
    </tr>
    <tr bgcolor="{row_off}">
    <td>{lang_Max_attachment_size}</td>
    <td>
    <input size="2" name="newsettings[expressoMail_Max_attachment_size]" value="{value_expressoMail_Max_attachment_size}">&nbsp;Mb<span style='position:relative; left:20px;'>Max: {php_upload_limit}Mb.</span> 
    <span style="color: red;margin-left: 30px"> *Valor 0 ou vazio = desativa a funcionalidade	</span>
    <input type="hidden" name="valida_alert" value="{lang_Value_exceeds_the_PHP_upload_limit_for_this_server}" /> 
    </td>
    </tr>
	<tr bgcolor="{row_on}"> 
 	    <td>{lang_allow_hidden_copy}</td> 
 	    <td> 
 	   <select name="newsettings[allow_hidden_copy]"> 
 	   <option value=""{selected_allow_hidden_copy_False}>{lang_No}</option> 
 	   <option value="True"{selected_allow_hidden_copy_True}>{lang_Yes}</option> 
 	   </select> 
 	    </td> 
	</tr>
    
    <tr bgcolor="{row_off}">
    <td>{lang_Do_you_want_to_use_x_origin_in_source_menssage?}</td>
    <td>
    <select name="newsettings[expressoMail_use_x_origin]">
    <option value=""{selected_expressoMail_use_x_origin_False}>{lang_No}</option>
    <option value="True"{selected_expressoMail_use_x_origin_True}>{lang_Yes}</option>
    </select>
    </td>
    </tr>
	 <tr bgcolor="{row_on}">
		<td>{lang_Number_max_of_labels}</td>
		<td>
		<input type="text" name="newsettings[expressoMail_limit_labels]" value="{value_expressoMail_limit_labels}" size="3" />
		<span style="color: red;margin-left: 30px"> *{lang_Minimum number of labels allowed} {min_labels}</span>		
		</td>
	</tr>
    <tr bgcolor="{th_bg}"> 
	        <td colspan="2"> 
	            &nbsp; 
	        </td> 
	    </tr> 
	    <tr bgcolor="{row_on}"> 
	        <td colspan="2"> 
	            <label style="font-weight:bold;">{lang_Share_folders}</label> 
	        </td> 
	    </tr> 
	   <tr bgcolor="{row_off}"> 
	     <td>{lang_Do_you_wish_enable_autosearch?}</td> 
	     <td> 
	       <select id="usersAutoSearch" name="newsettings[expressoMail_users_auto_search]"> 
	            <option value="false" {selected_expressoMail_users_auto_search_false}>{lang_No}</option> 
	            <option value="true" {selected_expressoMail_users_auto_search_true}>{lang_Yes}</option> 
	       </select> 
	     </td> 
	   </tr> 
   	<tr bgcolor="{row_on}"> 
       <td>{lang_Minimum_number_of_characters_to_start_the_search_for_participants}</td> 
       <td> 
          <input type="text" id="minNum" value="{value_expressoMail_min_num_characters}" name="newsettings[expressoMail_min_num_characters]" size=2 maxlength=2 /> 
	       </td> 
	   </tr> 

  <tr bgcolor="{th_bg}"> 
    <td colspan="2"> 
        &nbsp; 
    </td> 
  </tr>
  
  <tr bgcolor="{row_on}"> 
    <td colspan="2"> 
        <label style="font-weight:bold;">MemCache</label> 
    </td> 
  </tr>
  
    <tr bgcolor="{row_off}">
        <td>{lang_Using_cache_for_list_of_messages}</td>
        <td>
            <select id="useCache" name="newsettings[expressoMail_enable_memcache]">
                <option value="false" {selected_expressoMail_enable_memcache_false}>{lang_No}</option>
                <option value="true" {selected_expressoMail_enable_memcache_true}>{lang_Yes}</option>
            </select>
        </td>
    </tr>
    <tr bgcolor="{row_on}">
        <td>{lang_Maximum_time_for_the_list_of_messages_keep_in_cache} </td>
        <td>
            <input size="5" name="newsettings[expressoMail_time_memcache]" value="{value_expressoMail_time_memcache}">
            <span>s</span>
            <input type="hidden" name="valida_alert_MemCache" value="{lang_The field should only contain numbers}" /> 
        </td>
    </tr>

<tr bgcolor="{th_bg}"> 
    <td colspan="2"> 
        &nbsp; 
    </td> 
  </tr>
      
  <tr bgcolor="{row_on}"> 
    <td colspan="2"> 
        <label style="font-weight:bold;">{lang_Quick_add_widget_for_telephone_number}</label> 
    </td> 
  </tr>
  
    <tr bgcolor="{row_off}">
        <td>{lang_Enable_quick_add_for_user}</td>
        <td>
            <select id="use_quickadd" name="newsettings[expressoMail_enable_quickadd_telephonenumber]">
                <option value="false" {selected_expressoMail_enable_quickadd_telephonenumber_false}>{lang_No}</option>
                <option value="true" {selected_expressoMail_enable_quickadd_telephonenumber_true}>{lang_Yes}</option>
            </select>
        </td>
    </tr>
	
	<tr bgcolor="{th_bg}"> 
		<td colspan="2"> 
			&nbsp; 
		</td> 
	</tr>
	<tr bgcolor="{row_on}"> 
		<td colspan="2"> 
			<label style="font-weight:bold;">{lang_Identifier_of_the_recipient_of_a_message}</label> 
		</td> 
	</tr>
	<tr bgcolor="{row_off}">
        <td>{lang_LDAP_attribute_used_to_replacement}</td>
        <td>
            <select id="identifier_recipient" name="newsettings[expressoMail_ldap_identifier_recipient]">
				{rows_ldap_identifier}
            </select>
        </td>
    </tr> 
	
	<!-- <tr bgcolor="{row_off}">
        <td>{lang_LDAP_attribute_used_to_replacement}</td>
        <td>
            <input type="text" id="identifier_recipient " value="{value_expressoMail_ldap_identifier_recipient}" name="newsettings[expressoMail_ldap_identifier_recipient]" size=10 maxlength=10 />
        </td>
    </tr> -->
    <!--tr bgcolor="{row_on}">
        <td>{lang_Days_interval_to_show_balloon_for_user}</td>
        <td>
            <input size="5" name="newsettings[expressoMail_quickadd_days_expire_balloon]" value="{value_expressoMail_quickadd_days_expire_balloon}">
            <span>{lang_day(s)}</span>
            <input type="hidden" name="valida_alert_days_expire_balloon" value="{lang_The field should only contain numbers}" /> 
        </td>
    </tr-->    

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
