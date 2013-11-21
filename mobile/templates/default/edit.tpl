<!-- BEGIN edit_entry -->
<style type="text/css">
div#tipDiv {
  position:absolute; visibility:hidden; left:0; top:0; z-index:10000;
  background-color:#EFEFEF; border:1px solid #337;
  width:220px; padding:3px;
  color:#000; font-size:11px; line-height:1.2;
  cursor: default;
}
</style>
<script language="JavaScript">
	self.name="first_Window";
	function accounts_popup()
	{
		Window1=window.open('{accounts_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>
<center>
<font color="#000000" face="{font}">

<form action="{action_url}" method="post" name="app_form">
{common_hidden}
<table border="0" width="100%">
 <tr>
  <td colspan="2">
   <center><font size="+1"><b>{errormsg}</b></font></center>
  </td>
 </tr>
{row}
<tr>
<td colspan="2">
{row_owner}
</td>
</tr>
 <tr>
  <td>
  <table><tr valign="top">
  <td>
  <div style="padding-top:15px; padding-right: 2px">
  	<input style="font-size:10px" type="submit" value="{submit_button}"></div></form>
  </td>
  <td>{cancel_button}</td>
  </tr></table>
  </td>
  <td align="right">{delete_button}</td>
 </tr>
</table>
</font>
<a href="index.php?menuaction=mobile.ui_mobilecalendar.add_participants">{lang_add_participants}</a>
</center>
<!-- END edit_entry -->
<!-- BEGIN list -->
 <tr bgcolor="{tr_color}">
  <td valign="top" width="40%">&nbsp;<font size='1'><b>{field}:</b></font></td>
  <td valign="top" width="60%">{data}</td>
 </tr>
<!-- END list -->
<!-- BEGIN hr -->
 <tr bgcolor="{tr_color}">
  <td colspan="2">
   {hr_text}
  </td>
 </tr>
<!-- END hr -->
