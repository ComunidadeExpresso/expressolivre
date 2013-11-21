<form action="termo_aceite.php" method="post">
<input type="hidden" name="pass" value="1" />
<table border="0" width="100%" cellspacing="0" cellpadding="0">
 <tr>
 <td  height="3"><img src="../phpgwapi/templates/default/images/spacer.gif" alt="spacer" height="3" /></td>
</tr>
 <tr>
  <td align="left">

{accept_term}

  </td>
 </tr>
 <tr>
 <td  height="3" colspan="2"><img src="../phpgwapi/templates/default/images/spacer.gif" alt="spacer" height="3" /></td>
</tr>
 <tr>
 <td  height="3" align="center">{do you agree with the terms?} <input type="submit" name="submit" value="{Yes}" onClick="document.location.href='{url_accept}'"/>
      <input type="button" name="cancel" value="{No}" onClick="document.location.href='{url_dont_accept}'"/></td>
</tr> 
</table>
</form>