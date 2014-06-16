<!-- BEGIN body -->
<p>
  <div align="center">
   <table border="0" width="90%">
    <tr>
	 	<td align="left" width="25%">
	  		<form name="form" method="POST" action="{add_action}">
	  			<input type="submit" value="{lang_create_computers}" style="{add_computers_disabled}">
				<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
	  		</form>
	 	</td>
	 	<td align="center" "left" width="50%">
			{lang_context}: <font color="blue">{context_display}</font>
	 	</td>
     	<td align="right" "left" width="25%">
     		<form method="POST" action="{accounts_url}">
     			{lang_search}:
     			<input type="text" name="query" autocomplete="off" value="{query}">
      		</form>
     	</td>
    </tr>
   </table>
  </div>
 
 <div align="center">
  <table border="0" width="90%">
   <tr bgcolor="{th_bg}">
	<td width="20%" align="center">{lang_computer_uid}</td>
	<td width="60%" align="center">{lang_description}</td>
    <td width="6%" align="center">{lang_edit}</td>
    <td width="7%" align="center">{lang_delete}</td>
   </tr>
   {rows}
	<tr>
		<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
	</tr>
  </table>
 </div>
<!-- END body -->

<!-- BEGIN row -->
   <tr bgcolor="{tr_color}">
    <td width="20%">{row_cn}</td>
    <td width="60%">{row_description}</td>
    <td width="6%" align="center">{edit_link}</td>
    <td width="7%" align="center">{delete_link}</td>
   </tr>
<!-- END row -->

<!-- BEGIN row_empty -->
   <tr>
    <td colspan="5" align="center"><font color="red"><b>{message}</b></font></td>
   </tr>
<!-- END row_empty -->