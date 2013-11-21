 <center>{errors}</center>

 <form method="POST" name="admin" action="{form_action}">
 <input type="hidden" name="news[id]" value="{value_id}">
  <table width="100%" align="center" border="1" cellspacing="0" style="{ border: 1px solid #000000; }">
   <tr class="th">
    <td colspan="2"><b>{lang_header}<b></td>
   </tr>

   <tr class="row_on">
    <td width="10%">{label_subject}&nbsp;</td>
    <td>{value_subject}&nbsp;</td>
   </tr>

	<!--
   <tr class="row_off">
    <td>{label_teaser}&nbsp;</td>
    <td>{value_teaser}&nbsp;</td>
   </tr>
	-->

   <tr class="row_on">
    <td>{label_content}&nbsp;</td>
    <td>{value_content}&nbsp;</td>
   </tr>

   <tr class="row_off">
    <td>{label_category}&nbsp;</td>
    <td>{value_category}&nbsp;</td>
   </tr>

   <tr class="row_on">
    <td>{label_visible}&nbsp;</td>
    <td>
	 <select id="from" onChange="toggle()" name="from">
	  {select_from}
	 </select>
	 <span id="visible">{value_begin_d}&nbsp;
       {value_begin_m}&nbsp;
       {value_begin_y}
	  <select id="until" onChange="toggle()" name="until">
	   {select_until}
	  </select>
	  <span id="end">{value_end_d}&nbsp;
       {value_end_m}&nbsp;
       {value_end_y}
	  </span>
	 </span>
    </td>
   </tr>
	
   <tr class="row_off">
    <td>{label_is_html}&nbsp;</td>
    <td>{value_is_html}&nbsp;</td>
   </tr>

   <tr class="th">
    <td colspan="2" align="right">
     {form_button}
     {done_button}
    </td>
   </tr>
  </table>
 </form>
 <br>
	<script language="Javascript">
	// <!-- start Javascript	
	function toggle()
	{
	myspan = document.getElementById('visible')
	if (document.getElementById('from').value == '0.5')
	{
		myspan.style.display = 'inline';
	}
	else
	{
		myspan.style.display = 'none';
	}
	myspan = document.getElementById('end')
	if (document.getElementById('until').value == '0.5')
	{
		myspan.style.display = 'inline';
	}
	else
	{
		myspan.style.display = 'none';
	}
	}
	toggle();
	// end Javascript -->
	</script>