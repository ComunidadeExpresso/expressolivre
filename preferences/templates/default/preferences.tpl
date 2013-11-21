<center><b>{messages}</b></center>
<!-- BEGIN form -->
<form method="POST" action="{action_url}" {validateForm}>
<table width="100%" cellpadding=0 cellspacing=0  class="prefTable"> 
<tr><td colspan="3">{tabs}</td></tr>  
  <!-- BEGIN list -->
  <tbody>
 <tr bgcolor="{row_off}"><td colspan="3">&nbsp;</td></tr>
 {rows}
 <!-- END list -->
  <tr height="30" valign="bottom">
  <td align="left">
   <input type="submit" name="submit" value="{lang_submit}"> &nbsp;
   <input type="submit" name="cancel" value="{lang_cancel}">
  </td>
  <td align="right">&nbsp; {help_button}</td>
 </tr>
 </tbody>
</table>

</form>
<!-- END form -->

<!-- BEGIN script -->
<script language="JavaScript" type="text/javascript">
{script_code}
</script>
<!-- END script -->

<!-- BEGIN row -->
 <tr id="{row_id}" bgcolor="{tr_color}" {row_visibility}>
  <td style="width:35%">{row_name}</td> 
  <td style="width:65%">{row_value}</td> 
 </tr>
<!-- END row -->

<!-- BEGIN help_row -->
  <tr bgcolor="{tr_color}">
  <td><b>{row_name}<b></td>
  <td>{row_value}</td>
 </tr>
 <tr bgcolor="{tr_color}">
  <td colspan="2">{help_value}</td>
 </tr>
<!-- END help_row -->
