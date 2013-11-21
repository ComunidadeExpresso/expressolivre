<!-- BEGIN list -->
<p>
<br>
  <form method="POST" action="{action}">
    <table border="0" align="center">
  		
		<tr bgcolor={row_on}>
			<td>{lang_organizations}:</td>
			<td><select name="context">{organizations}</select></td>
		</tr>
  		
  		<tr bgcolor={row_off}>
   			<td>{lang_samba_domain_name}:</td>
   			<td><input type="text" autocomplete="off" name="sambadomainname" value="{sambadomainname}"></td>
		</tr>
  		
  		<tr bgcolor={row_on}>
   			<td>{lang_samba_domain_sid}:</td>
   			<td><input type="text" autocomplete="off" size=45 name="sambasid" value="{sambasid}"></td>
   		</tr>
   		
   		<tr bgcolor="{color_bg1}">
   			<td colspan="2" align="center">
     			<input type="submit" name="button_submit" value={lang_save}>
     			<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
   			</td>
 		</tr>
 		
    </table>
  </form>
 {error_messages}
<!-- END list -->
