<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<!-- BEGIN login_form -->
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset={charset}">
<META name="AUTHOR" content="dGroupWare http://www.eGroupWare.org">
<META NAME="description" CONTENT="{website_title} login screen, working environment powered by eGroupWare">
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<META NAME="keywords" CONTENT="{website_title} login screen, eGroupWare, groupware, groupware suite">
<link href="../phpgwapi/templates/celepar/login.css" rel="stylesheet" type="text/css">
<TITLE>{website_title} - {lang_login}</TITLE>
<script src="js/local_messages.js?1.222" type='text/javascript'></script>
<script src="js/offline_access.js?1.222" type='text/javascript'></script>
<script src="js/gears_init.js?1.222" type='text/javascript'></script>
<script src="js/md5.js?1.222" type='text/javascript'></script>
<script language="Javascript">
<!--
	
	function submitIt(event){
		/*if(event.keyCode == 13)
			document.login.submit();*/		
	}

	function setLogin(){
		if( document.login.organization != null)
			document.login.login.value = document.login.organization.value+'-'+document.login.user.value;
		else
			document.login.login.value = document.login.user.value;			
	}
	
	function getLogin(){
		var cookie = '{cookie}';
		if( document.login.organization != null)
			document.login.user.value= cookie.substring(cookie.indexOf('-')+1,cookie.length);
		else	
			document.login.user.value= cookie;

		if(document.login.user.value == '') {
			if(document.login.organization != null)
				document.login.organization.focus();
			else
				document.login.user.focus();
		}
		else
			document.login.passwd.focus();
	}
	
 	function openWindow(newWidth,newHeight,link) {					
		newScreenX  = screen.width - newWidth;	
		newScreenY  = 0;		
		Window1=window.open(link,'',"width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",toolbar=no,scrollbars=yes,resizable=no");				
	}
	
	-->
</script>
</HEAD>
<body scroll="no"  style="overflow:hidden"  bgcolor="#ffffff" onload="expresso_offline_access.fill_combo_of_users(document.getElementById('users_combo'))">
<table border="0" height="100%" width="100%">
<tr><td align="center" valign="center">
<table align="center" border="1" bordercolor="#cfcfcf" cellpadding="0" cellspacing="0" width="100%"  height="100%">
<tbody><tr>
    <td  height="100%">
<table cellpadding="0" cellspacing="0" width="100%"  height="100%">
	<tbody><tr>			
    <td bgcolor="#E8EEF7"  height="100%">
    <table cellpadding="0" height="100%" cellspacing="0" width="100%" border="0"><tr><td width="60%">&nbsp;</td><td>
	<div align="center" id='div_error' class="msgInicial">{cd}<br>{lang_message}<br>{website_title} - 01<br><br></div>
        <table border="0" bordercolor="#cfcfcf" cellpadding="0" cellspacing="0" width="300">
          <tbody><tr>
				<FORM name="login" method="post" action="{login_url}" {autocomplete} onsubmit="expresso_offline_access.do_login(document.getElementById('users_combo').value,MD5(document.getElementById('passwd').value));return false;">
            	<td bgcolor="#f7f7f7" style="border:1px solid black">
					<table  border="0" cellpadding="0" cellspacing="0" class="tableLogin">
						<tr><td height="2px">&nbsp;</td></tr>
					  <tr>
						<td width="96" class="loginLabel">{lang_username}:&nbsp;</td>
						<td width="105"><select name="user" id='users_combo'></select><!--input name="user" size="20"--></td>
						<td width="43" rowspan="3"><img src="../phpgwapi/templates/celepar/images/icon_login.gif" width="43" height="64"></td>
					  </tr>
					  <tr>
						<td width="66" class="loginLabel">{lang_password}:&nbsp;</td>
						<td width="135"><input name="passwd" type="password" size="20" id="passwd"></td>
						<td>&nbsp;</td>		
					  </tr>
					  <tr>
						<td width="66">&nbsp;</td>
						<td width="135">
						  <input type="button" value="{lang_login}" name="submitit" class=button onclick="expresso_offline_access.do_login(document.getElementById('users_combo').value,MD5(document.getElementById('passwd').value))">
						</td>
						</tr>
					  <tr>	
				  		<td width="66">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
    <td valign="bottom" bgcolor="#e8eef7"><div align="left"><img src="../phpgwapi/templates/celepar/images/logo_expresso_1.gif" alt=""></div></td>
	</tr>
	<tr>		
    <td background="../phpgwapi/templates/celepar/images/logo_expresso_4.gif"><div align="right"><img src="../phpgwapi/templates/celepar/images/logo_expresso_3.gif" alt="" height="46" width="485"></div></td>		
    <td background="../phpgwapi/templates/celepar/images/logo_expresso_5.gif"><div align="left"><img src="../phpgwapi/templates/celepar/images/logo_expresso_2.gif" alt="" height="46" width="285"></div></td>
	</tr>	
	<tr>	
<style type="text/css">
<!--
.style2 {font-size: 10px}
-->
</style>
    <td bgcolor="#f7f7f7" valign="top"><div align="right">
        <table border="0" cellpadding="0" cellspacing="0" width="300px">
          <tbody><tr> 
            <td><a title="Governo do Paran&aacute;" href="http://www.pr.gov.br" target="_blank"><img border=0 src="../phpgwapi/templates/celepar/images/logo_governo.gif"/></a></td>
            <td><div align="center"><a title="Celepar Inform&aacute;tica do Paran&aacute;" target="_blank" href="http://www.celepar.pr.gov.br/"><img border=0 src="../phpgwapi/templates/celepar/images/logo_celepar.gif"></a><a title="eGroupWare" style="text-decoration:none" target="_blank" href="http://www.egroupware.org/"><font color="#9a9a9a" face="Verdana, Arial, Helvetica, sans-serif" size="1"><br>Powered by eGroupWare {version}</a></font></div></td>
          </tr>
        </tbody></table><br>
      </div></td>
	 <td bgcolor="#f7f7f7" height="100%"></td>
	</tr>	
</tbody></table>
</td></tr></tbody></table>
</td></tr></table>
</body></html>
