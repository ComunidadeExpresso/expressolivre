<!-- BEGIN list -->
<form method="POST" action="{action}">
<p>
  <table border="0" width="75%" align="center">
  <tr>
   <td align="left">
		{input_add}
		<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
    </form>
   </td>
  </tr>
 </table>
 
 <table border="0" width="75%" align="center">
  <tr bgcolor="{th_bg}">
   <td width="30%">{lang_samba_domains_name}</td>
   <td width="70%">{lang_sambaSID}</td>
   <td width="1%">{lang_delete}</td>
  </tr>
  {rows}
 </table>
<!-- END list -->

<!-- BEGIN row -->
 <tr bgcolor="{tr_color}">
  <td width="30%">{sambadomainname}</td>
  <td width="70%">{sambaSID}</td>
  <td width="1%">{delete_link}</td>
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