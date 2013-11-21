<!-- BEGIN setup_demo -->
<form method="POST" action="{action_url}">
<table border="0" width="90%" cellspacing="0" cellpadding="2">
  <tr>
    <td>
	{description}
	<p>
	<input type="checkbox" name="delete_all">{lang_deleteall}
    </td>
  </tr>
  <tr>
    <td align="left" bgcolor="#cccccc">{detailadmin}</td>
  </tr>
  <tr>
    <td>
	<table border="0">
          <tr>
            <td>{adminusername}</td>
            <td><input type="text" name="username" value="expresso-admin"></td>
          </tr>
          <tr>
            <td>{adminfirstname}</td>
            <td><input type="text" name="fname2" value="Admin"></td>
          </tr>
          <tr>
            <td>{adminlastname}</td>
            <td><input type="text" name="lname" value="Expresso"></td>
          </tr>
          <tr>
            <td>{adminpassword}</td>
            <td><input type="password" name="passwd"></td>
          </tr>
          <tr>
            <td>{adminpassword2}</td>
            <td><input type="password" name="passwd2"></td>
          </tr>
          <tr>
            <td><input type="submit" name="submit" value="{lang_submit}"> </td>
            <td><input type="submit" name="cancel" value="{lang_cancel}"> </td>
          </tr>
        </table>
    </td>
  </tr>
</table>
</form>
<!-- END setup_demo -->
