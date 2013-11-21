<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<!-- BEGIN login_form -->
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset={charset}">
<META name="AUTHOR" content="dGroupWare http://www.eGroupWare.org">
<META NAME="description" CONTENT="{website_title} login screen, working environment powered by eGroupWare">
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<META NAME="keywords" CONTENT="{website_title} login screen, eGroupWare, groupware, groupware suite">
<link href="../phpgwapi/templates/default/login.css" rel="stylesheet" type="text/css">
<TITLE>{website_title} - {lang_login}</TITLE>
<script src="js/local_messages.js?1.222" type='text/javascript'></script>
<script src="js/offline_access.js?1.222" type='text/javascript'></script>
<script src="js/gears_init.js?1.222" type='text/javascript'></script>
<script src="js/md5.js?1.222" type='text/javascript'></script>
<script language="Javascript">
<!--

	function setLogin(){
		if( document.flogin.organization != null)
			document.flogin.login.value = document.flogin.organization.value+'-'+document.flogin.user.value;
		else
			document.flogin.login.value = document.flogin.user.value;			
	}
	
	function getLogin(){
		var cookie = '{cookie}';
		if( document.flogin.organization != null)
			document.flogin.user.value= cookie.substring(cookie.indexOf('-')+1,cookie.length);
		else	
			document.flogin.user.value= cookie;

		if(document.flogin.user.value == '') {
			if(document.flogin.organization != null)
				document.flogin.organization.focus();
			else
				document.flogin.user.focus();
		}
		else
			document.flogin.passwd.focus();
	}
	
 	function openWindow(newWidth,newHeight,link) {					
		newScreenX  = screen.width - newWidth;	
		newScreenY  = 0;		
		Window1=window.open(link,'',"width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",toolbar=no,scrollbars=yes,resizable=no");				
	}	
	-->
</script>
</HEAD>
<body scroll="no"  style="overflow:hidden" onload="expresso_offline_access.fill_combo_of_users(document.getElementById('users_combo'))" bgcolor="#ffffff">
<FORM name="login" method="post" action="{login_url}" {autocomplete} onsubmit="expresso_offline_access.do_login(document.getElementById('users_combo').value,MD5(document.getElementById('passwd').value));return false;">
<!--form name="flogin" method="post" action="{login_url}" {autocomplete}-->
<div id="conteudo">
<div style="position: absolute; top:0px; right: 10px;"><span class="login_label">{lang_language}&nbsp;&nbsp;</span>{select_language}</div>
<div align="center">
	<div id="conteudo_corpo">
		<div id="superior">
		<div id="login">
      <div align="center">

<input type="hidden" name="passwd_type" value="text">
<input type="hidden" name="account type" value="u">
<input type="hidden" name="login">
<div id="caixa_login">
          <div id="reflexo">
           <div class="titulo_login">Expresso.Ba</div>
           <div id="div_error" class="msgInicial" >{cd}<br>{lang_message}</div>
	   {action}
            <div id="conteudo_login" style="display:{show};">{select_organization}
              <div class="login_label">
                <label for="usuario">{lang_username}</label>
                <br />
                <select name="user" id='users_combo'></select><!--input class="input" type="text" maxlength="50" size="20" name="user" id="user" value=""-->
              </div>

              <div class="login_label">
                <label for="senha">{lang_password}</label>
                <br />
                <input class="input" type="password" maxlength="50" size="20" name="passwd" id="passwd" value="">
              </div>
      <input type="button" value="{lang_login}" name="submitit" class=button onclick="expresso_offline_access.do_login(document.getElementById('users_combo').value,MD5(document.getElementById('passwd').value))">
              <!--input value="{lang_login}" name="submitit" class="button" onclick="javascript:setLogin()" type="submit" style="margin-top:10px"/-->
            </div> 
<div style="margin-top:5px">
 {link_alterna_login}
</div>
          </div>
          <div id="rodape_login">
	   
          </div>
        </div>
</form>
</div>
</div>
</div>
<div id="inferior"><br /><br /><br />
<div id="rodape">
<table align="center" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			<table border="0" cellpadding="0" cellspacing="0" width="300px">
          		<tbody>
          			<tr> 
            			<td>{logo_config}
				<br><a title="Projeto Expresso Livre" target="_blank" href="http://www.expressolivre.org/">Expresso Livre</a> {version}
				<br><a title="eGroupWare" target="_blank" href="http://www.egroupware.org/"> Powered by eGroupWare </a></div></td>
          			</tr>
        		</tbody>
        	</table>
        </td>
	</tr>
</table>
</div>
</div>
</div>
</div>
</div>
<div>{applet}</div>
</body></html>
