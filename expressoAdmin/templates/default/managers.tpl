<!-- BEGIN body -->
<p>
  <table border="0" width="90%" align="center">
  <tr>
   <td align="right">
    <form method="POST" action="{action}">
    	<input type="submit" value="{lang_add_manager}" />
    </form>
   </td>
  </tr>
 </table>
 
 <table border="0" width="90%" align="center">
  <tr bgcolor="{th_bg}">
   <td width="20%" align="center"><B>{lang_manager_lid}</B></td>
   <td width="30%" align="center"><B>{lang_manager_name}</B></td>
   <td width="30%" align="center"><B>{lang_context}</B></td>
   <td width="5%"  align="center"><B>{lang_edit}</B></td>
   <td width="5%"  align="center"><B>{lang_delete}</B></td>
   <td width="5%"  align="center"><B>{lang_copy}</B></td>
  </tr>
  {rows}
 </table>
<!-- END body -->

<!-- BEGIN row -->
 <tr bgcolor="{tr_color}">
  <td width="20%">{manager_lid}</td>
  <td width="30%">{manager_cn}</td>
  <td width="30%">{context}</td>
  <td width="5%" align="center">{link_edit}</td>
  <td width="5%" align="center">{link_delete}</td>
  <td width="5%" align="center">{link_copy}</td>
 </tr>
<!-- END row -->

<!-- BEGIN row_empty -->
   <tr>
    <td colspan="5" align="center">{message}</td>
   </tr>
<!-- END row_empty -->
