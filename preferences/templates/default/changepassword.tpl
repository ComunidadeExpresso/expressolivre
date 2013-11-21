<br>
   <center>{messages}</center>
   <form method="POST" action="{form_action}">
    <table border="0">
     <tr>
       <td>
        {lang_enter_actual_password}
       </td>
       <td>
        <input type="password" name="a_passwd" style="overflow:auto !important">
       </td>
     </tr>
     <tr>
       <td>
        {lang_enter_password}
       </td>
       <td>
        <input type="password" name="n_passwd" style="overflow:auto !important">
       </td>
     </tr>
     <tr>
       <td>
        {lang_reenter_password}
       </td>
       <td>
        <input type="password" name="n_passwd_2" style="overflow:auto !important">
       </td>
     </tr>
     <tr>
       <td>
        &nbsp;
       </td>
       <td>
	   <br/>
        <input type="submit" name="change" value="{lang_change}">
        &nbsp;
        <input type="submit" name="cancel" value="{lang_cancel}">
       </td>
     </tr>
    </table>
   </form>
   <br>
   <pre>{sql_message}</pre>
