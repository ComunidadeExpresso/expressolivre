<!-- BEGIN mobile_home -->
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>   
	    <title>{global_title}</title>
		<meta content="text/html;width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0; charset=utf-8;" http-equiv="Content-Type" name="viewport" /> 
		<meta name="format-detection" content="telephone=no" />
		<link href="templates/css/mobile.css" type="text/css" rel="StyleSheet" />
	</head>	
	<body>
		<div class="topo" style="height:20pt;">
			<div style="position:absolute; float:left; width:50%;">
				<div style="position:relative; float:left;">
					<h1 onclick="document.location='index.php?menuaction=mobile.ui_home.index'">{global_title}</h1>
				</div>
			</div>
			
			<div style="position:relative; float:right; width:50%; margin:2px;">
				<div style="{style_1}">
					<h1 onclick="document.location='index.php?menuaction=mobile.ui_home.dicas'">{lang_tips}</h1>
				</div>
				
				<div style="{style_2}">
					<h1 onclick="document.location='{href_logout}'">{lang_logout}</h1>
				</div>
			</div>
			
		</div>

		{message_box}		
		<!-- BEGIN mobile_home_content -->
		{content}
		<!-- END mobile_home_content -->
		
		<div id="menu_rodape">
			<div class="margin-geral">
				<p><a href="{href_back}" id="menu_rodape_voltar">{lang_back}</a></p>
				<p><a href="{href_home}">{lang_home}</a></p>
				<p><a href="{href_email}">{lang_email}</a></p>
				<p><a href="{href_cc}">{lang_contacts}</a></p>
				<p><a href="{href_calendar}">{lang_calendar}</a></p>
			</div>
		</div>
		<div class="rodape">Projeto ExpressoLivre 2004 - 2010 :: Licen&ccedil;a de Software</div>
		<div class="rodape center"><a href="index.php?menuaction=mobile.ui_home.change_template&template=mini_desktop">Mini Desktop</a></div>
	</body>
</html>
<!-- END mobile_home -->
<!-- BEGIN success_message -->
<div class="bg-neutro">
	<div class="aviso-positivo">
		<strong>{message}</strong>
	</div>
</div>
<!-- END success_message -->
<!-- BEGIN error_message -->
<div class="bg-neutro">
	<div class="aviso-negativo">
		<strong>{message}</strong>
	</div>
</div>
<!-- END error_message -->