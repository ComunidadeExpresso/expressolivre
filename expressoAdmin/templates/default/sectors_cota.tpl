<!-- BEGIN list -->
<p>
  <table border="0" align="center">
  <tr bgcolor="{th_bg}">
   <td>{lang_context}: {context}</td>
  </tr>
 </table>
 
  <table border="0" align="center">
  		<tr>  
   			<td>
   				{lang_sector_name}:
   			</td>
   			<td>
				{sector}
   			</td>
 		</tr>
  		<tr>  
   			<td>
   				{lang_users_cota}:
   			</td>
   			<td>
				{users_cota}
   			</td>
 		</tr>
  		<tr>  
   			<td>
   				{lang_disk_cota}:
   			</td>
   			<td>
				{disk_cota}
   			</td>
 		</tr>
  		<tr>  
   			<td>
   				{lang_user_number}
   			</td>
   			<td>
				{actual_users}
   			</td>
 		</tr>
  		<tr>  
   			<td>
   				{lang_disk_used}
   			</td>
   			<td>
				{actual_disk}
   			</td>
 		</tr>
 		<tr>
   			<td align="left" colspan="2">
     			<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
     			<input type="hidden" name="context" value={context}>
   			</td>
 		</tr>


  </table>
<!-- END list -->