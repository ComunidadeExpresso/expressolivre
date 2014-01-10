<!-- BEGIN list -->
<p>
  <table border="0" align="center">
  <!--
  <tr bgcolor="{th_bg}">
   <td>{lang_context}: {context}</td>
  </tr>
  -->
 </table>
 
  <form method="POST" action="{action}">
    <table border="0" align="center">
		<tr bgcolor={row_off}>
			<td>{lang_organizations}:</td>
			<td><select {disabled} name="context">{manager_org}</select></td>
		</tr>
  		<tr>  
   			<td>
   				{lang_sector_name}:
   			</td>
   			<td>
				  <input type="text" {disable} autocomplete="off" name="sector" value={sector}>
        </td> 
          </tr> 
          <tr>   
            <td> 
               {lang_Associated_domain}: 
            </td> 
          <td> 
            <input type="text" {disable} autocomplete="off" name="associated_domain" value={associated_domain}> 
   			</td>
 		</tr>
		{open_comment_cotas} 
                <tr>   
                        <td> 
                                {lang_users_quota}: 
                        </td> 
                        <td> 
                                <input type="text" autocomplete="off" name="users_quota" value={users_quota}> 
                        </td> 
                </tr> 
                 
                <tr>   
                        <td> 
                                {lang_disk_quota}: 
                        </td> 
                        <td> 
                                <input type="text" autocomplete="off" name="disk_quota" value={disk_quota}> 
                        </td> 
                </tr> 
                {close_comment_cotas} 
  		<tr>  
   			<td>
   				{lang_do_not_show_this_sector}:
   			</td>
   			<td>
				<input type="checkbox" name="sector_visible" {sector_visible_checked}>
   			</td>
 		</tr>
 		<tr>
   			<td align="left" colspan="2">
     			<input type="submit" name="button_submit" value={lang_save}>
     			<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
				<!--
     			<input type="hidden" name="context" value="{context}">
				-->
     			<input type="hidden" name="old_sector" value="{old_sector}">
   			</td>
 		</tr>
    </table>
  </form>
 {error_messages}
<!-- END list -->
