<!-- BEGIN list -->
<form method="POST" action="{action}">
<p>
  <table border="0" width="55%" align="center">
  <tr>
	<td align="center" "left" width="50%">
		{lang_context}: <font color="blue">{context_display}</font>
	</td> 
  </tr>
  <tr>
   <td align="left">
		{input_add}
		<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
    </form>
   </td>
  </tr>
 </table>
 
 <table border="0" width="70%" align="center"> 
  <tr bgcolor="{th_bg}">
   <td>{lang_name}</td>
   <td>{lang_inactives}</td>
   <td>{lang_ver_cota}</td> 
   <td>{lang_add_sub_sectors}</td>
   <td>{lang_edit}</td>
   <td>{lang_delete}</td>
  </tr>
  {rows}
 </table>
<!-- END list -->

<!-- BEGIN row -->
 <tr bgcolor="{tr_color}">
  <td>{sector_name}</td>
  <td width="13%">{inactives_link}</td>  
  <td width="13%">{cota_link}</td>  
  <td width="12%">{add_link}</td> 
  <td width="5%">{edit_link}</td>
  <td width="5%">{delete_link}</td>
 </tr>
<!-- END row -->

<!-- BEGIN row_empty -->
   <tr>
    <td colspan="5" align="center">{message}</td>
   </tr>
	<tr>
		<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
	</tr>
<!-- END row_empty -->
