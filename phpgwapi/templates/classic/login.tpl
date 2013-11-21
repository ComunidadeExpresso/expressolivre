<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<!-- BEGIN login_form -->
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset={charset}">
<META name="AUTHOR" content="dGroupWare http://www.eGroupWare.org">
<META NAME="description" CONTENT="{website_title} login screen, working environment powered by eGroupWare">
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<META NAME="keywords" CONTENT="{website_title} login screen, eGroupWare, groupware, groupware suite">
<link href="phpgwapi/templates/{template}/login.css" rel="stylesheet" type="text/css">
<TITLE>{website_title} - {lang_login}</TITLE>
<script language="Javascript">
<!--

	function setLogin(){
		if( document.form_login.organization != null)
			document.form_login.login.value = document.form_login.organization.value+'-'+document.form_login.user.value;
		else
			document.form_login.login.value = document.form_login.user.value;			
	}
	
	function getLogin(){
		var cookie = '{cookie}';
		if( document.form_login.organization != null)
			document.form_login.user.value= cookie.substring(cookie.indexOf('-')+1,cookie.length);
		else	
			document.form_login.user.value= cookie;

		if(document.form_login.user.value == '') {
			if(document.form_login.organization != null)
				document.form_login.organization.focus();
			else
				document.form_login.user.focus();
		}
		else
			document.form_login.passwd.focus();
	}
	
 	function openWindow(newWidth,newHeight,link) {					
		newScreenX  = screen.width - newWidth;	
		newScreenY  = 0;		
		Window1=window.open(link,'',"width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",toolbar=no,scrollbars=yes,resizable=no");				
	}	
	-->
</script>
</HEAD>
<body scroll="no"  style="overflow:hidden" onLoad="javascript:getLogin()" bgcolor="#ffffff">
<table border="0" height="100%" width="100%">
<tr><td align="center" valign="center">
<table align="center" border="1" bordercolor="#cfcfcf" cellpadding="0" cellspacing="0" width="100%"  height="100%">
<tbody><tr>
    <td  height="100%">
<table cellpadding="0" cellspacing="0" width="100%"  height="100%">
	<tbody><tr>			
    <td bgcolor="#e8eef7"  height="100%">
    <table cellpadding="0" height="100%" cellspacing="0" width="100%" border="0"><tr><td width="60%">&nbsp;</td><td>
	<div align="center" class="msgInicial">{cd}<br>{lang_message}<br>{website_title}{frontend_name}<br><br></div>
        <table border="0" bordercolor="#cfcfcf" cellpadding="0" cellspacing="0" width="300">
          <tbody><tr>
				<FORM name="form_login" method="post" action="{login_url}" {autocomplete}>
				<input type="hidden" name="passwd_type" value="text">
				<input type="hidden" name="account type" value="u">
				<input type="hidden" name="login">
				<tr><td width="60%">&nbsp;</td><td>				          
            	<td bgcolor="#f7f7f7" style="border:1px solid black">
					<table border="0" cellpadding="0" cellspacing="0" class="tableLogin">
						<tr><td width="96" colspan="3">&nbsp;</td></tr>	
						<!-- BEGIN language_select -->
						<tr>
							<td width="96" class="loginLabel" align="right">{lang_language}:&nbsp;</td>
							<td width="96">{select_language}</td>
						</tr>
						<!-- END language_select -->
						{select_organization}
					    <tr><td width="96" class="loginLabel" align="right">{lang_username}:&nbsp;</td>
						<td width="96"><input name="user" size="15"></td>
						<td width="43" rowspan="3" colspan=2><img src="phpgwapi/templates/{template}/images/icon_login.gif" width="43" height="64"></td>
					  </tr>
					  <tr>
						<td width="96" class="loginLabel"  align="right">{lang_password}:&nbsp;</td>
						<td width="135"><input name="passwd" type="password" size="15"></td>

					  </tr>
					  <tr>
						<td width="66">&nbsp;</td>
						<td width="135">
						  <input type="submit" value="{lang_login}" name="submitit" class=button onClick="javascript:setLogin()">
						</td>
						</tr>
					  <tr>	
				  		<td width="66">
			  				<div style="margin-left:10px;display:{display_help}">
			  					<a title="{lang_help}" target="help" href="./help.php?lang={lang}"><font size="-1">{lang_help}</font></a>
			  				</div>
          				</td>
						<td width="135">&nbsp;</td>
						<td>&nbsp;</td>		
				  	   </tr>		
					</table>              
              </td>
              </form>
          </tr>
        </tbody></table>      
      </td</tr></table>
      </div>
      </td>		
    <td valign="bottom" bgcolor="#e8eef7"><div align="left"><img src="phpgwapi/templates/{template}/images/logo_expresso_1.gif" alt=""></div></td>
	</tr>
	<tr>		
    <td background="phpgwapi/templates/{template}/images/logo_expresso_4.gif"><div align="right"><img src="phpgwapi/templates/{template}/images/logo_expresso_3.gif" alt="" height="46" width="485"></div></td>		
    <td background="phpgwapi/templates/{template}/images/logo_expresso_5.gif"><div align="left"><img src="phpgwapi/templates/{template}/images/logo_expresso_2.gif" alt="" height="46" width="285"></div></td>
	</tr>	
	<tr>	
    <td bgcolor="#f7f7f7" valign="top"><div align="right">
		<table border="0" cellpadding="0" cellspacing="0" width="300px">
			<tbody>
          		<tr> 
            		<td>{logo_config}
						<br><a style="text-decoration:none" title="Projeto Expresso Livre" target="_blank" href="http://www.expressolivre.org/"><font color="#9a9a9a" face="Verdana, Arial, Helvetica, sans-serif" size="1">Expresso Livre</font></a> {version}
						<br><a style="text-decoration:none" title="eGroupWare" target="_blank" href="http://www.egroupware.org/"><font color="#9a9a9a" face="Verdana, Arial, Helvetica, sans-serif" size="1">Powered by eGroupWare</font></a>
					</td>
          		</tr>
        	</tbody>
        </table>
     </td>
	 <td bgcolor="#f7f7f7" height="100%"></td>
	</tr>	
</tbody></table>
</td></tr></tbody></table>
</td></tr></table>
</body></html>
