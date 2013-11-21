<br>

   <center>{messages}</center>

   <form method="POST" action="{form_action}">
    <table border="0">
     <tr>
       <td>
        {lang_commercial_telephonenumber}:
       </td>
       <td>
        <input type="input" autocomplete="off" name="telephonenumber" size=12 value="{telephonenumber}" maxlength="13">
       </td>
     </tr>
     <tr>
     	<td colspan="2" width="60px">
     		<p style="text-align:justify; width:350px;"><b><font color='red'>{lang_ps_commercial_telephonenumber}</font></b></p>
     	</td>
     </tr>
     <tr><td colspan=2 height="20px"></td></tr>
     <tr>
       <td>
        {lang_mobile_telephonenumber}:
       </td>
       <td>
        <input type="input" autocomplete="off" name="mobile" size=12 value="{mobile}" maxlength="13">
       </td>
     </tr>
     <tr>
       <td>
        {lang_homephone_telephonenumber}:
       </td>
       <td>
        <input type="input" autocomplete="off" name="homephone" size=12 value="{homephone}" maxlength="13">
       </td>
     </tr>
	 <tr>
       <td>{lang_birthday}:</td>
			<td>
				<input style="text-align: center;" title="Data de Nascimento" name="datanascimento" maxlength="10" size="12" type="text" value="{datanascimento}" onkeyup="formatDate(this)";>				
			</td>
       
       <td>       
     </tr>
	<tr><td colspan=2 height="20px"></td></tr>
     
     <tr>
       <td colspan="3">
        <table cellspacing="0"><tr><br>
         <td><input type="submit" name="change" value="{lang_change}"></td>
		 <td>&nbsp;&nbsp;</td>
         <td><input type="submit" name="cancel" value="{lang_cancel}"></td>
        </tr></table>
       </td>
     </tr>
    </table>
   </form>
   <br>
   <pre>{sql_message}</pre>
