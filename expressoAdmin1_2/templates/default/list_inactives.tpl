<!-- BEGIN list -->
 <table border="0" width="55%" align="center">
  <tr bgcolor="{th_bg}">
   <td>{lang_idusuario}</td>
   <td>{lang_login}</td>
   <td>{lang_ultimo_login}</td>
  </tr>
  {rows}
 </table>
<!-- END list -->

<!-- BEGIN row -->
 <tr bgcolor="{tr_color}">
  <td width="10%">{id}</td>
  <td width="70%">{login}</td>
  <td width="20%">{data_ultimo_login}</td>
 </tr>
<!-- END row -->

<!-- BEGIN row_empty -->
   <tr>
    <td colspan="3" align="center">{message}</td>
   </tr>
	<tr>
		<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
	</tr>
<!-- END row_empty -->
