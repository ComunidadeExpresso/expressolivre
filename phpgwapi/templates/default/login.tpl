<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<!-- BEGIN login_form -->
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset={charset}">
<META name="AUTHOR" content="dGroupWare http://www.eGroupWare.org">
<META NAME="description" CONTENT="{website_title} login screen, working environment powered by eGroupWare">
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<META NAME="keywords" CONTENT="{website_title} login screen, eGroupWare, groupware, groupware suite">
{login_css}
<TITLE>{website_title} - {lang_login}</TITLE>
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
		Window1=window.open('{dir_root}'+link,'',"width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",toolbar=no,scrollbars=yes,resizable=no");
	}
        var opened = false, vkb = null, text = null; 
        var userstr = navigator.userAgent.toLowerCase(); 
        var safari = (userstr.indexOf('applewebkit') != -1); 
        var gecko  = (userstr.indexOf('gecko') != -1) && !safari; 
        function loadvkbd(){ 
                if(typeof(VKeyboard) == 'function') 
                        keyb_change(); 
                else 
                { 
                        vkbdscript=document.createElement('SCRIPT'); 
                        vkbdscript.src="phpgwapi/js/jscode/vkboards.js"; 
                        vkbdscript.onload = keyb_change; 
                        if(gecko || window.opera || safari) 
                                vkbdscript.onload = keyb_change; 
                        else 
                                setTimeout('keyb_change()',3000);  
                        document.body.appendChild(vkbdscript); 
                } 
        } 
 
   var opened = false, vkb = null, text = null; 
 
   function keyb_change() 
   { 
     opened = !opened; 
 
     if(opened && !vkb) 
     { 
       vkb = new VKeyboard("keyboard",    // container's id 
                           keyb_callback, // reference to the callback function 
                           false,          // create the arrow keys or not? (this and the following params are optional) 
                           false,          // create up and down arrow keys?  
                           false,         // reserved 
                           false,          // create the numpad or not? 
                           "",            // font name ("" == system default) 
                           "14px",        // font size in px 
                           "#FFF",        // font color 
                           "#F00",        // font color for the dead keys 
                           "#83a6ce",        // keyboard base background color 
                           "#28599e",        // keys' background color 
                           "#DDD",        // background color of switched/selected item 
                           "#777",        // border color 
                           "#CCC",        // border/font color of "inactive" key (key with no value/disabled) 
                           "#83a6ce",        // background color of "inactive" key (key with no value/disabled) 
                           "#F77",        // border color of the language selector's cell 
                           true,          // show key flash on click? (false by default) 
                           "#CC3300",     // font color during flash 
                           "#FF9966",     // key background color during flash 
                           "#CC3300",     // key border color during flash 
                           false,         // embed VKeyboard into the page? 
                           true,          // use 1-pixel gap between the keys? 
                           0);            // index(0-based) of the initial layout 
     } 
     else 
       vkb.Show(opened); 
 
     text = document.getElementById("passwd"); 
     text.focus(); 
     if (!(gecko || window.opera || safari)) 
     { 
        document.getElementById('keyboard').style.left = "0px"; 
        document.getElementById('rodape').style.zIndex="-100"; 
     } 
 
   } 
   // Callback function: 
   function keyb_callback(ch) 
   { 
     var val = text.value; 
 
     switch(ch) 
     { 
       case "BackSpace": 
         var min = (val.charCodeAt(val.length - 1) == 10) ? 2 : 1; 
         text.value = val.substr(0, val.length - min); 
         break; 
 
       case "Enter": 
         document.getElementById('loginForm').submit(); 
         break; 
 
       default: 
         text.value += ch; 
     } 
   } 
 function setRange(ctrl, start, end){ 
 } 	
	-->
</script>
</HEAD>
<body scroll="no"  style="overflow:hidden" onLoad="javascript:getLogin()" bgcolor="#ffffff">
<form id="loginForm" name="flogin" method="post" action="{login_url}" {autocomplete}>
<div id="conteudo">
<div style="position: absolute; top:0px; right: 10px;"><span class="login_label">{lang_language}&nbsp;&nbsp;</span>{select_language}</div>
<div align="center">
	<div id="conteudo_corpo">
		<div id="superior">
		<div id="login">
      <div align="center">
<input type="hidden" name="certificado" value="">
<input type="hidden" name="passwd_type" value="text">
<input type="hidden" name="account type" value="u">
<input type="hidden" name="login">
<div id="caixa_login">
          <div id="reflexo">
           <div class="titulo_login">Expresso Livre</div>
           <div id="mensagem" class="msgInicial" >{cd}<br>{lang_message}</div>
	   {action}
            <div id="conteudo_login" style="display:{show};">{select_organization}
              <div class="login_label">
                <label for="usuario">{lang_username}</label>
                <br />
                <input class="input" type="text" maxlength="70" size="20" name="user" id="user" value="">
              </div>

              <div class="login_label">
                <label for="senha">{lang_password}</label>
                <br />
                <input class="input" type="password" maxlength="50" size="20" name="passwd" id="passwd" value="">
		<div id="keyboard"></div>
              </div>
		{captcha}
      
              <input value="{lang_login}" name="submitit" class="button" onclick="javascript:setLogin()" type="submit" style="margin-top:10px"/>
	      <img style="display:{show_kbd};" src="phpgwapi/templates/default/images/keyboard.png" alt="virtualkeyboard" title="virtualkeyboard" onclick="loadvkbd()" /> 
            </div> 
			<div id="alterna_login">
			 {link_alterna_login}
			</div>
          </div>
          <div id="rodape_login">
	    	<div class="ajuda" style="display:{display_help}">
				<img src="./phpgwapi/templates/{template}/images/help.png"/>
				<a title="{lang_help}" target="help" href="./help.php?lang={lang}">{lang_help}</a>
			</div>
          </div>
        </div>
</form>
</div>
</div>
</div>
<div id="inferior"><br />
<div id="rodape">
<table align="center" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			<table border="0" cellpadding="0" cellspacing="0" width="300px">
          		<tbody>
          			<tr> 
            			<td>{logo_config}
				<br><a title="Projeto Expresso Livre" target="_blank" href="http://www.expressolivre.org/">Expresso Livre</a> {version}
                                {ultima_rev}
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
