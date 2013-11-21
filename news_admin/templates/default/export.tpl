<center>
  <table border="0" cellspacing="2" cellpadding="2" width="80%">
   <tr>
    <td colspan="6" align="center" bgcolor="#c9c9c9"><b>{title}<b/></td>
   </tr>
   <tr>
    <td colspan="6" align="left">
     <table border="0" width="100%">
      <tr>
	   {left}
       <td align="center">{lang_showing}</td>
	   {right}
      </tr>
     </table>
    </td>
   </tr>
   <tr>
    <td>&nbsp;</td>
    <td colspan="6" align="right">
	<form method="POST"><input type="text" name="query" value="{query}" />&nbsp;<input type="submit" name="btnSearch" value="{lang_search}" /></form>
	</td>
   </tr>
 </table>
 <form method="POST">
  <table border="0" cellspacing="2" cellpadding="2" width="80%">
   <tr bgcolor="{th_bg}" valign="middle" align="center">
    <td>{sort_cat}</td>
	<td>{lang_configuration}</td>
   </tr>
   <!-- BEGIN cat_list -->
   <tr valign="top" align="center" bgcolor="{tr_color}">
	<td><b>{catname}</b><br /><br />
	 <input type="hidden" name="catids[]" value="{catid}" />
	 {lang_type}
	 <select name="inputconfig[{catid}][type]">{typeselectlist}</select>
	 <br /><br />
	 {lang_item}
	 <select name="inputconfig[{catid}][itemsyntax]">{itemsyntaxselectlist}</select>
	</td>
	<td>
	 <table>
	  <!-- BEGIN config -->
	  <tr>
	   <td>{setting}</td>
	   <td>{value}</td>
	  </tr>
	   <!-- END config -->
	 </table>
	</td>
   </tr>
   <!-- END cat_list -->
   <tr>
    <td colspan="3" align="center">
     <input type="submit" name="btnSave" value="{lang_save}"> &nbsp;
     <input type="submit" name="btnDone" value="{lang_done}">
    </td>
   </tr>
  </table>
 </form>
</center>
