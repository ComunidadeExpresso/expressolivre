<!-- BEGIN page -->
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>   
		<title>{website_title} - {lang_login}</title>
		<meta content="text/html;width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0; charset=utf-8;" http-equiv="Content-Type" name="viewport" /> 
		<meta name="format-detection" content="telephone=no" />
		<!-- link href="templates/css/mobile.css" type="text/css" rel="StyleSheet" /-->
		<link type="text/css" rel="stylesheet" href="templates/css/login.css">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
		<link rel="apple-touch-icon" href="./templates/default/images/favicon.png" />
		<link rel="apple-touch-icon-precomposed" href="./templates/default/images/favicon.png"/>
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<link rel="icon" href="./templates/default/images/favicon.png" type="image/x-ico" />
		<meta name="apple-touch-fullscreen" content="YES" /> 

		<script type="text/javascript">

			function validate_login(form)
			{
				document.getElementById("max_resolution").value = ((screen.width >= screen.height) ? screen.width : screen.height);

				return true;
			}

			function messageHidden(Element)
			{
				var _div = document.getElementById(Element);
				
				setTimeout(function()
				{
					_div.style.display = "none";
					
				}, 10000 );
			}

		</script>
	</head>
	<body style="background:url(templates/default/images/back_pagina.jpg) repeat-x #fff;">
		<div id="divSuperior">
			<div style="height: 20px; font-size:small;">
				{message_box}
			</div>
			<div >
				<form name="form_login" method="post" action="./login.php" id="login_form" autocomplete=off onSubmit="return validate_login(this);">
					<input type="hidden" name="max_resolution" id="max_resolution" value="">
					<input type="hidden" name="passwd_type" value="text">
					<input type="hidden" name="account type" value="u">
					<!-- input type="hidden" name="save_login" value="no" -->
					<label>{lang_username}:</label><br/>
					<input id="login" name="login"/><br/>
					<label>{lang_password}:</label><br/>
					<input id="passwd" name="passwd" type="password" autocomplete=off/><br/>
					<input type="checkbox" name="save_login"/>
					<label style="color:#909090;">Mantenha-me conectado</label>
					<div style="margin-top:20px;">
						<button id="formButton" name="submitit" style="margin-top:5px" type="submit">{lang_login}</button>
					</div>
				</form>
			</div>
			<div class="rodape" style="float:left; margin-top: 10px;">
				<a href="{url_expresso}login.php?dont_redirect_if_moble=1">Versão Clássica</a>
			</div>
		</div>
		 
		<div id="divInferior">
			&nbsp;
		</div>
		
	</body>
</html>
<script type="text/javascript">
	document.getElementById('login').focus();
</script>
<!-- END page -->

<!-- BEGIN success_message -->
<div id="success_message">
	<label style="color:red;">{message}</label>
	<script>messageHidden("success_message");</script>
</div>
<!-- END success_message -->

<!-- BEGIN error_message -->
<div id="error_message">
	<label style="color:red;">{message}</label>
	<script>messageHidden("error_message");</script>
</div>
<!-- END error_message -->