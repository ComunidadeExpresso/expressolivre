<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xml:lang="{lang_code}" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Expresso Setup - {lang_setup} {configdomain}</title>
		<meta http-equiv="content-type" content="text/html; charset={charset}" />
		<meta name="keywords" content="ExpressoLivre" />
		<meta name="description" content="expressolivre" />
		<meta name="language" content="{lang_code}" />
		<meta name="author" content="expressolivre http://www.expressolivre.org" />
		<meta name="robots" content="none" />
		<link rel="icon" href="../phpgwapi/templates/default/images/favicon.ico" type="image/x-ico" />
		<link rel="shortcut icon" href="../phpgwapi/templates/default/images/favicon.ico" />
		<link href="templates/default/css/default.css" type="text/css" rel="stylesheet" />
		<!--
		{css}
		-->

		<style type="text/css">
			<!--
			.row_on { color: #000000; background-color: #f9fbff; }
			.row_off { color: #000000; background-color: #e8f0f0;}
			.th 
			{ 
			  color: black; 
			  background-color: #dcdcdc; 
			}

			-->	
		</style>

	</head>
	<body>

<div id="divMain">
	<div id="divAppIconBar">
<table width='100%'>
<tr><td align='left' background="./templates/default/images/fundo_topo.jpg"><img src="./templates/default/images/topo.jpg" style="overflow:hidden;z-index:-1;"></td></tr></table>
	</div>
	<div id="divSubContainer">
		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<!-- sidebox column -->
				<td id="tdSidebox" valign="top">
					
					
					<div class="divSidebox">
						<div class="divSideboxHeader"><span>{main_menu}</span></div>
						<div>
							<table width="100%" cellspacing="0" cellpadding="0">
					
								<tr class="divSideboxEntry">
									<td width="20" align="center" valign="middle" class="textSidebox"><img src="templates/default/images/orange-ball.png" alt="ball" /></td><td class="textSidebox"><a class="textsidebox" href="../home.php">{user_login}</a></td>
								</tr>
<!-- BEGIN loged_in -->
								<tr class="divSideboxEntry">
									<td width="20" align="center" valign="middle" class="textSidebox"><img src="templates/default/images/orange-ball.png" alt="ball" /></td><td class="textSidebox">{check_install}</td>
								</tr>

								<tr class="divSideboxEntry">
									<td width="20" align="center" valign="middle" class="textSidebox">{indeximg}</td><td class="textSidebox">{indexbutton}</td>
								</tr>

								<tr class="divSideboxEntry">
									<td width="20" align="center" valign="middle" class="textSidebox"><img src="templates/default/images/orange-ball.png" alt="ball" /></td><td class="textSidebox">{logoutbutton}</td>
								</tr>
<!-- END loged_in -->
							</table>
						</div>
					</div>
					<div class="sideboxSpace"></div>

				</td>
				<!-- end sidebox column -->

				<!-- applicationbox column -->
				<td id="tdAppbox" valign="top">
				<div id="divAppboxHeader">{lang_setup} {configdomain}</div>
				<div id="divAppbox">
