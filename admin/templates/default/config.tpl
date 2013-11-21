<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table align="center" width="85%" callspacing="0">
   <tr class="th">
    <td colspan="2">&nbsp;<b>{title}</b></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
	<tr class="row_off">
    <td>{lang_Time_for_expire_inatives_accounts(0 for never expires)}:</td>
    <td><input size="8" name="newsettings[time_to_account_expires]" value="{value_time_to_account_expires}"></td>
   </tr>
   <tr class="row_on">
    <td>{lang_Timeout_for_sessions_in_seconds_(default_14400_=_4_hours)}:</td>
    <td><input size="8" name="newsettings[sessions_timeout]" value="{value_sessions_timeout}"></td>
   </tr>

   <tr class="row_off">
    <td>{lang_Timeout_for_application_session_data_in_seconds_(default_86400_=_1_day)}:</td>
    <td><input size="8" name="newsettings[sessions_app_timeout]" value="{value_sessions_app_timeout}"></td>
   </tr>

   <tr class="row_on">
    <td>{lang_Would_you_like_to_show_each_application's_upgrade_status_?}:</td><td>
     <select name="newsettings[checkappversions]">
      <option value="">{lang_No}</option>
      <option value="Admin"{selected_checkappversions_Admin}>{lang_Admins}</option>
      <option value="All"{selected_checkappversions_All}>{lang_All_Users}</option>
     </select>
    </td>
   </tr>
   <tr class="row_off">
    <td>{lang_Would_you_like_to_automaticaly_load_new_langfiles_(at_login-time)_?}:</td>
    <td>
     <select name="newsettings[disable_autoload_langfiles]">
      <option value="">{lang_Yes}</option>
      <option value="True"{selected_disable_autoload_langfiles_True}>{lang_No}</option>
     </select>
    </td>
   </tr>
   <tr class="row_on"> 
  <td>{lang_enable_stylesheet_packer_?}:</td><td> 
  <select name="newsettings[csspacker]"> 
  <option value="False"{selected_csspacker_False}>{lang_No}</option> 
  <option value="True"{selected_csspacker_True}>{lang_Yes}</option> 
  </select> 
    </td> 
    </tr> 
    <tr class="row_off">
    <td>{lang_Should_the_login_page_include_a_language_selectbox_(useful_for_demo-sites)_?}:</td>
    <td>
     <select name="newsettings[login_show_language_selection]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_login_show_language_selection_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>
    <tr class="th">
    <td colspan="2">&nbsp;<b>{lang_appearance}</b></td>
   </tr>
 <tr class="row_off">
    <td>{lang_HTML_code_of_the_organization_login}</td>
    <td><textarea cols="50" rows="2" name="newsettings[login_logo_file]">{value_login_logo_file}</textarea></td>
   </tr>

<tr class="th">
    <td>{lang_personal_contact_type}:</td>
    <td>
     <select name="newsettings[personal_contact_type]">
      <option value="">{lang_default}</option>
      <option value="True"{selected_personal_contact_type_True}>{lang_advanced}</option>
     </select>
    </td>
   </tr>

   <tr class="th">
    <td colspan="2">&nbsp;<b>{lang_security}</b></td>
   </tr>
   <tr class="row_on"> 
  	<td>{lang_enable_virtual_keyboard_on_login}:</td> 
	<td>
	   <select name="newsettings[login_virtual_keyboard]"> 
	   <option value="">{lang_No}</option> 
	   <option value="True"{selected_login_virtual_keyboard_True}>{lang_Yes}</option> 
	   </select> 
	</td> 
   </tr>
<tr class="row_off">
    <td valign="top">
Nome, um ponto-e-virgula como separador, e o caminho completo para os drivers dos tokens 
que ser&atilde;o suportados (At&eacute; 10):<br/>
<blockquote><p>
   Ex:  ePass2000Lx;/usr/lib/libepsng_p11.so<br/>
	ePass2000Win;c:/windows/system32/ngp11v211.dll<br/> 
       <p>Estes s&atilde;o os drivers para o token ePass2000, utilizado no Serpro,
        nos ambientes Linux e Windows respectivamente.
		</p></blockquote>
        <p><b>Aten&ccedil;&atilde;o</b>: O caminho no ambiente Windows n&atilde;o deve usar contra barra.</p>         
         </td>
    <td>
	<input name="newsettings[test_token11]" value="{value_test_token11}"><br/>
	<input name="newsettings[test_token21]" value="{value_test_token21}"><br/>
	<input name="newsettings[test_token31]" value="{value_test_token31}"><br/>
	<input name="newsettings[test_token41]" value="{value_test_token41}"><br/>
	<input name="newsettings[test_token51]" value="{value_test_token51}"><br/>
	<input name="newsettings[test_token61]" value="{value_test_token61}"><br/>
	<input name="newsettings[test_token71]" value="{value_test_token71}"><br/>
	<input name="newsettings[test_token81]" value="{value_test_token81}"><br/>
	<input name="newsettings[test_token91]" value="{value_test_token91}"><br/>
	<input name="newsettings[test_token101]" value="{value_test_token101}"><br/>
    </td>
   </tr>

   <tr class="row_on">
    <td>{lang_Use_cookies_to_pass_sessionid}:</td>
    <td>
     <select name="newsettings[usecookies]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_usecookies_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>

   <tr class="row_off">
    <td>{lang_check_ip_address_of_all_sessions}:</td>
    <td>
     <select name="newsettings[sessions_checkip]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_sessions_checkip_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>

   <tr class="row_on">
    <td>{lang_Deny_all_users_access_to_grant_other_users_access_to_their_entries_?}:</td>
    <td>
     <select name="newsettings[deny_user_grants_access]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_deny_user_grants_access_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>

<tr class="row_off">
    <td>{lang_How_many_days_the_password_should_be_kept(empty to disable this)_?}:</td>
    <td>
     <input name="newsettings[max_pwd_age]" value="{value_max_pwd_age}" size="5">
    </td>
   </tr>

   <tr class="row_on">
    <td>{lang_How_many_days_should_entries_stay_in_the_access_log,_before_they_get_deleted_(default_90)_?}:</td>
    <td>
     <input name="newsettings[max_access_log_age]" value="{value_max_access_log_age}" size="5">
    </td>
   </tr>

   <tr class="row_off">
    <td>{lang_After_how_many_unsuccessful_attempts_to_login,_an_account_should_be_blocked_(default_3)_?}:</td>
    <td>
     <input name="newsettings[num_unsuccessful_id]" value="{value_num_unsuccessful_id}" size="5">
    </td>
   </tr>
   
   <tr class="row_on">
    <td>{lang_After_how_many_unsuccessful_attempts_to_login,_an_IP_should_be_blocked_(default_3)_?}:</td>
    <td>
     <input name="newsettings[num_unsuccessful_ip]" value="{value_num_unsuccessful_ip}" size="5">
    </td>
   </tr>
   
   <tr class="row_off">
    <td>{lang_How_many_minutes_should_an_account_or_IP_be_blocked_(default_30)_?}:</td>
    <td>
     <input name="newsettings[block_time]" value="{value_block_time}" size="5">
    </td>
   </tr>
   
   <tr class="row_on">
    <td>{lang_How_many_letters_the_user_password_must_contain_(default_3)_?}:</td>
    <td>
     <input name="newsettings[num_letters_userpass]" value="{value_num_letters_userpass}" size="5">
    </td>
   </tr>
   
   <tr class="row_off">
    <td>{lang_How_many_special_letters_the_user_password_must_contain_(default_0)_?}:</td>
    <td>
     <input name="newsettings[num_special_letters_userpass]" value="{value_num_special_letters_userpass}" size="5">
    </td>
   </tr>
      
   <tr class="row_on">
    <td>{lang_Admin_email_addresses_(comma-separated)_to_be_notified_about_the_blocking_(empty_for_no_notify)}:</td>
    <td>
     <input name="newsettings[admin_mails]" value="{value_admin_mails}" size="40">
    </td>
   </tr>

   <!--tr class="th">
    <td colspan="2">&nbsp;<b>{lang_VoIP_settings}</b></td>
   </tr>

   <tr class="row_off">
    <td>{lang_Enter_your_VoIP_server_address}:</td>
    <td><input name="newsettings[voip_server]" value="{value_voip_server}"  size="40"></td>
   </tr>   
   <tr class="row_on">
    <td>{lang_Enter_your_VoIP_server_url} (Ex.: /telefoniaip/servicos/voip.php):</td>
    <td><input name="newsettings[voip_url]" value="{value_voip_url}"  size="40"></td>
   </tr>   
   <tr class="row_off">
    <td>{lang_Enter_your_VoIP_server_port}:</td>
    <td><input name="newsettings[voip_port]" value="{value_voip_port}"></td>
   </tr-->   
    <tr class="row_off">
    <td >
		{lang_use_agree_term}:
     </td>
	<td>
     <select name="newsettings[use_agree_term]">
         <option value="">{lang_No}</option>
         <option value="True"{selected_use_agree_term_True}>{lang_Yes}</option>
     </select>					
	</td>
   </tr>
   
  <tr class="row_on">
    <td colspan="2">{lang_agree_term}: <br />
								{agree_term_input} <br />
	</td>
   </tr>

   
<!-- END body -->

<!-- BEGIN footer -->
  <!--tr class="th">
    <td colspan="2">
&nbsp;
    </td>
  </tr-->
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
		  <br>
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
