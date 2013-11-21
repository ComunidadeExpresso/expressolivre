<style type="text/css">
#link a:hover { 
	background-color:#ffffcc; 
} 
.style2 {
	font-size: 16px;
	font-family: Arial, Helvetica, sans-serif;
}
.style3 {color: #999999}
</style>
<!-- BEGIN list -->
<p>
<div align="center">
   <table border="0" width="90%">
    <tr>
	 	<td align="left">
			<div align="center" class="style2">{lang_reports_title3}:<br>
			  <br>&nbsp;<font color="blue">{context_display}</font><br><br>
	 	    </div></td>
 	 </tr>
    <tr>
      <td align="left"><form method="POST" action="./index.php?menuaction=reports.uireports_cota.report_cota_group">
  {lang_search}:
      <select name="organizacaodn">
     			    {group_name}
	  </select>
      &nbsp;&nbsp;&nbsp;<input name="Enviar" type="submit" value="{lang_send}">
      &nbsp;&nbsp;&nbsp;<input name="button" type="button" onClick="document.location.href='{back_url}'" value="{lang_back}">
            </form>
	  </td>
     </tr>
   </table>
</div>
 
 <div align="center">
  <table border="0" width="90%">
   <tr>
	<form  name="org" method="POST" action="./index.php?menuaction=reports.uireports_cota.report_cota_group_setor_print">
    	<td width="89%">
			<div align="center">
				<font color="#0000FF"><strong><font size="3" face="Arial, Helvetica, sans-serif">
    		   		<div onClick="document.org.submit()"><a href="#">{organizacao}</a></div>
    			</font></strong></font>
			</div>
		</td>
        <td width="9%">
			<div align="right">
   		   		  <div style="float:left" onClick="document.org.submit()" align="right">
				  	<font color="#0000FF">
						<strong>
							<font size="2" face="Arial, Helvetica, sans-serif">
								<a href="#">{all_user}</a>
							</font>
						</strong>
					</font>
				</div>
					<strong>
						<font size="2" face="Arial, Helvetica, sans-serif">
							{total_user}
						</font>
					</strong>
			</div>
		</td>
        <td width="2%">
			<input type="hidden" name="setor" value="{organizacao}">
			<input type="hidden" name="organizacao" value="{organizacao}">
			<input type="hidden" name="setordn" value="{organizacaodn}">
			<input type="hidden" name="organizacaodn" value="{organizacaodn}">
		</td>
	</form>										
   </tr>
   <tr bgcolor="{th_bg}">
     <td colspan="3">{lang_groups_names}</td>
   </tr>

   {rows}

  </table>
 </div>
<!-- END list -->

<!-- BEGIN row -->
   <tr bgcolor="{tr_color}">
<form name="{formname}" method="POST" action="./index.php?menuaction=reports.uireports_cota.report_cota_group_setor_print">
    <td width="30%">	  <div onClick="{formsubmit}">
			<a href="#">{sector_name}</a>
			<input type="hidden" name="setor" value="{sector_name}">
			<input type="hidden" name="organizacao" value="{organizacao}">
			<input type="hidden" name="setordn" value="{sector_namedn}">
			<input type="hidden" name="organizacaodn" value="{organizacaodn}">
			<input type="hidden" name="sectornamedncompleto" value="{sector_namedn_completo}">			
		</div>
	</td>
</form>
   </tr>
<!-- END row -->

<!-- BEGIN row_empty -->
	<tr>
    	<td colspan="5" align="center"><font color="red"><b>{message}</b></font></td>
	</tr>
<!-- END row_empty -->
