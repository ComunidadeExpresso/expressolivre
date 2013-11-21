<!-- BEGIN portal_box -->
<div style="border: 1px solid #adc9d8;border-top:0px">
<table border="0" cellpadding="0" cellspacing="0" width="{outer_width}"  height="100%"> 
 <tr nowrap align="center">
  <td style="height:19px;border-top:0px;border-bottom:1px solid #d6e2ee;padding:1px;vertical-align:top;background-repeat:repeat-x;background: #fff url(phpgwapi/templates/default/images/bgBlockTitle.png)" align="left" nowrap>&nbsp;<font size="2" color="#003366">{title}</font></td>
 </tr>
 <tr>
  <td style='background: #f4f8fb;border-top:0px solid #adc9d8'>
   <table border="0" cellpadding="0" height="100%" cellspacing="0" width="{inner_width}">
    {row}
   </table>
  </td>
 </tr>
</table>
</div>
<!-- END portal_box -->
<!-- BEGIN portal_row -->
    <tr>
	  <td >
		{output}
	  </td>
    </tr>
<!-- END portal_row -->
<!-- BEGIN portal_listbox_header -->
	<tr>
	 <td>
	  <ul>
<!-- END portal_listbox_header -->
<!-- BEGIN portal_listbox_link -->
<li><a href="{link}">{text}</a></li>
<!-- END portal_listbox_link -->
<!-- BEGIN portal_listbox_footer -->
	  </ul>
	 </td>
	</tr>
<!-- END portal_listbox_footer -->
<!-- BEGIN portal_control -->
  <td valign="middle" align="right" nowrap="nowrap">{control_link}</td>
<!-- END portal_control -->
<!-- BEGIN link_field -->
   {link_field_data}
<!-- END link_field -->
