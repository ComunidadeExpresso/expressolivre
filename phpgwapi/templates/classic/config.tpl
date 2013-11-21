<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center" width="85%">
   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{lang_Path_information}</b></font></td>
   </tr>
   
   </tr>
   <tr bgcolor="{row_off}">
    <td>{lang_Enter_the_full_path_for_temporary_files.<br>Examples:_/tmp,_C:\TEMP}:</td>
    <td><input name="newsettings[temp_dir]" value="{value_temp_dir}" size="40"></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Enter_the_full_path_for_users_and_group_files.<br>Examples:_/files,_E:\FILES}:</td>
    <td><input name="newsettings[files_dir]" value="{value_files_dir}" size="40"></td>
   </tr>
   
   <tr bgcolor="{row_off}">
    <td>{lang_Enter_the_location_of_eGroupWare's_URL.<br>Example:_http://www.domain.com/egroupware_&nbsp;_or_&nbsp;_/egroupware<br><b>No_trailing_slash</b>}:</td>
    <td><input name="newsettings[webserver_url]" value="{value_webserver_url}" size="40"></td>
   </tr>

   <tr bgcolor="{row_on}">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{lang_Host_information}</b></font></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Enter_the_hostname_of_the_machine_on_which_this_server_is_running}:</td>
    <td><input name="newsettings[hostname]" value="{value_hostname}"></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Enter_your_default_FTP_server}:</td>
    <td><input name="newsettings[default_ftp_server]" value="{value_default_ftp_server}"></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Attempt_to_use_correct_mimetype_for_FTP_instead_of_default_'application/octet-stream'}:</td>
    <td>
     <select name="newsettings[ftp_use_mime]">
      <option value="">{lang_No}</option>
      <option value="True"{selected_ftp_use_mime_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Datetime_port.<br>If_using_port_13,_please_set_firewall_rules_appropriately_before_submitting_this_page.<br>(Port:_13_/_Host:_129.6.15.28)}</td>
    <td>
      <select name="newsettings[daytime_port]">
       <option value="00"{selected_daytime_port_00}>{lang_00_(disable)}</option>
       <option value="13"{selected_daytime_port_13}>{lang_13_(ntp)}</option>
       <option value="80"{selected_daytime_port_80}>{lang_80_(http)}</option>
      </select>
    </td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Enter_your_HTTP_proxy_server}:</td>
    <td><input name="newsettings[httpproxy_server]" value="{value_httpproxy_server}"></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Enter_your_HTTP_proxy_server_port}:</td>
    <td><input name="newsettings[httpproxy_port]" value="{value_httpproxy_port}"></td>
   </tr>

   <tr bgcolor="{row_on}">
    <td>{lang_Enter_the_site_username_for_peer_servers}.</td>
    <td><input name="newsettings[site_username]" value="{value_site_username}"></td>
   </tr>

   <tr bgcolor="{row_off}">
    <td>{lang_Enter_the_site_password_for_peer_servers}.</td>
    <td><input type="password" name="newsettings[site_password]" value="{value_site_password}"></td>
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
      <input type="submit" name="submit" value="Submit">
      <input type="submit" name="cancel" value="Cancel">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
