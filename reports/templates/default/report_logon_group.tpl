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

<script type="text/javascript">
function valida(theForm){
	var digits="0123456789";
	var temp; 
	var ok = true;

	for (var i=0;i<theForm.nacesso.value.length;i++){
		temp=theForm.nacesso.value.substring(i,i+1);
		if (digits.indexOf(temp)==-1){
			alert("O campo deve ser preenchido apenas com números!");
			theForm.nacesso.focus();
			ok = false;
			return(false);
			break;
		}
	}

	if (theForm.nacesso.value==0){
		return true;	
	}else{
		if(theForm.nacesso.value < 7){
			alert("O mínimo para pesquisar e 7 (sete) dias sem acesso.");
			return false;
		}	
	}
} 
</script>

<p>
<div align="center">
   <table border="0" width="90%">
    <tr>
	 	<td align="left">
			<div align="center" class="style2">{lang_reports_title5}:<br><br>&nbsp;<font color="blue">{context_display}</font><br><br>
	 	    </div></td>
 	 </tr>
    <tr>
      <td align="left">
	  <form method="POST" action="./index.php?menuaction=reports.uireports_logon.report_logon_group">
  {lang_search}:
      <select name="organizacaodn">
     			    {group_name}
	  </select>
      &nbsp;&nbsp;&nbsp;<input name="Enviar" type="submit" value="Enviar">
      &nbsp;&nbsp;&nbsp;<input name="button" type="button" onClick="document.location.href='{back_url}'" value="{lang_back}">
            </form>
	  </td>
     </tr>
   </table>
</div>
 
 <div align="center">
  <table border="0" width="90%">
	<form  name="org" method="POST" action="./index.php?menuaction=reports.uireports_logon.report_logon_group_setor_print"  onSubmit="javascript:return valida(this);">
   <tr bgcolor="{th_bg}">
     <td colspan="3">
		 <div id="div_nacesso1" style="position: absolute; visibility: hidden; width: 264px; height: 226px; top: 144px; left: 380px; background-color: rgb(238, 238, 238); 	border: 2px solid #999999; z-index: 62; cursor: auto;">
			<span style="position: relative; width:257px; font-weight: bold; color: #0000FF; z-index: 1; background-color:#CCCCCC" class="undefined">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Números de dias sem acesso&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="./phpgwapi/images/winclose.gif" style="" onClick="document.getElementById('div_nacesso1').style.display = 'none';">
			</span>
			<span style="position: absolute; top: 25px; left: 5px; text-align: left; border: 0px solid rgb(153, 153, 153); width: 252px; height: 125px;" id="texto1">
				<font color="#FF0000"><strong>Atenção:<br>
			<br> (0) - Zero ou Nulo Mostra todos os usuários.<br><br> (999) - Mostra todos os usuários que nunca acessaram.</strong></font>			</span>
			<span style="position: absolute; top: 140px; left: 15px; text-align: left; border: 0px solid rgb(153, 153, 153);" id="texto1">
				<font color="#000000"><strong>Número de dias:</strong></font>
				<input name="nacesso" type="text" id="nacesso" style="position: absolute; top: -3px; left: 119px; width: 50px;" value="0" maxlength="3">				
			</span>
			<input type="submit" style="position: absolute; top: 193px; left: 56px; width: 60px;" value="Enviar" title="Enviar">
			<input type="button" style="position: absolute; top: 192px; left: 152px; width: 60px;" value="Fechar" onclick="document.getElementById('div_nacesso1').style.display = 'none';" title="Fechar">
		 </div>
	 </td>
   </tr>
   <tr>
    	<td width="89%">
			<div align="center">
				<font color="#0000FF"><strong><font size="3" face="Arial, Helvetica, sans-serif">
    		   		<div onClick="document.getElementById('div_nacesso1').style.display = 'block';document.getElementById('div_nacesso1').style.visibility = 'visible';"><a href="#">{organizacao}</a></div>
    			</font></strong></font>
			</div>
		</td>
        <td width="9%">
			<div align="right">
   		   		  <div style="float:left" onClick="document.getElementById('div_nacesso1').style.display = 'block';document.getElementById('div_nacesso1').style.visibility = 'visible';" align="right">
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
   </tr>
	</form>										
   <tr bgcolor="{th_bg}">
     <td colspan="3">{lang_groups_names}</td>
   </tr>

   {rows}

  </table>
 </div>
<!-- END list -->

<!-- BEGIN row -->
   <tr bgcolor="{tr_color}">
<form name="{formname}" method="POST" action="./index.php?menuaction=reports.uireports_logon.report_logon_group_setor_print" onSubmit="javascript:return valida(this);">
    <td width="30%">
		 <div id="{div_nacesso}" style="position: absolute; visibility: hidden; width: 264px; height: 226px; top: 144px; left: 380px; background-color: rgb(238, 238, 238); 	border: 2px solid #999999; z-index: 62; cursor: auto;">
			<span style="position: relative; width:257px; font-weight: bold; color: #0000FF; z-index: 1; background-color:#CCCCCC" class="undefined">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Números de dias sem acesso&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="./phpgwapi/images/winclose.gif" style="" onClick="document.getElementById('{div_nacesso}').style.display = 'none';">
			</span>
			<span style="position: absolute; top: 25px; left: 5px; text-align: left; border: 0px solid rgb(153, 153, 153); width: 252px; height: 125px;" id="texto1">
				<font color="#FF0000"><strong>Atenção:<br>
			<br> (0) - Zero ou Nulo Mostra todos os usuários.<br><br> (999) - Mostra todos os usuários que nunca acessaram.</strong></font>			</span>
			<span style="position: absolute; top: 140px; left: 15px; text-align: left; border: 0px solid rgb(153, 153, 153);" id="texto1">
				<font color="#000000"><strong>Número de dias:</strong></font>
				<input name="nacesso" type="text" id="nacesso" style="position: absolute; top: -3px; left: 119px; width: 50px;" value="0" maxlength="3">				
			</span>
			<input type="submit" style="position: absolute; top: 193px; left: 56px; width: 60px;" value="Enviar" title="Enviar">
			<input type="button" style="position: absolute; top: 192px; left: 152px; width: 60px;" value="Fechar" onclick="document.getElementById('{div_nacesso}').style.display = 'none';" title="Fechar">
			<input type="hidden" name="setor" value="{sector_name}">
			<input type="hidden" name="organizacao" value="{organizacao}">
			<input type="hidden" name="setordn" value="{sector_namedn}">
			<input type="hidden" name="organizacaodn" value="{organizacaodn}">
			<input type="hidden" name="sectornamedncompleto" value="{sector_namedn_completo}">
		 </div>

		<div onClick="document.getElementById('{div_nacesso}').style.display = 'block';document.getElementById('{div_nacesso}').style.visibility = 'visible';">
			<a href="#">{sector_name}</a>
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
