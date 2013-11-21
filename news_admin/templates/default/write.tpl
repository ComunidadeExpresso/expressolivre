<!-- BEGIN list -->
 {_category}  &nbsp; &nbsp; <a href="{link_add}">{lang_add}</a>
 <center>{message}</center><p>
  <h2 align="center">{cat_name}
    </h2>
     <table align="center" border="0" width="85%">
      <tr>
	   {left}
       <td align="center">{lang_showing}</td>
	   {right}
      </tr>
     </table>
<table align="center" width="85%" cellspacing="0" style="{ border: 1px solid #000000; }">
  <tr class="th">
   <td width="12%">{header_date}</td>
   <td>{header_subject}</td>
   <td width="15%" align="center">{header_status}</td>
   <td width="5%" align="center">{header_view}</td>
   <td width="5%" align="center">{header_edit}</td>
   <td width="5%" align="center">{header_delete}</td>
  </tr>

  {rows}

 </table>
<!-- END list -->

<!-- BEGIN row -->
  <tr bgcolor="{tr_color}">
   <td>{row_date}</td>
   <td>{row_subject}&nbsp;</td>
   <td align="center">{row_status}</td>
   <td align="center">{row_view}</td>
   <td align="center">{row_edit}</td>
   <td align="center">{row_delete}</td>
  </tr>
<!-- END row -->

<!-- BEGIN row_empty -->
  <tr bgcolor="{tr_color}">
   <td colspan="6" align="center">{row_message}</td>
  </tr>
<!-- END row_empty -->

<!-- BEGIN category -->
<form method="POST">
 <select name="inputread" onChange="location.href=this.value">{readable}</select>
</form>
<!-- END category -->
