<!-- BEGIN mobile_home -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{global_title}</title>
		<meta content="text/html; width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0; charset=utf-8" http-equiv="Content-Type" name="viewport" />
		<meta name="format-detection" content="telephone=no" />
		<link href="templates/css/desktop.css" type="text/css" rel="StyleSheet" />
		<!--[if lte IE 6]>
			<link href="templates/css/ie6.css" type="text/css" rel="StyleSheet">
		<![endif]-->
		
		<script type="text/javascript">
			function validate_desktop_search() {
				var default_folders = document.getElementById("hidden_default_folders");
				var personal_folders = document.getElementById("hidden_personal_folders");
				var calendar_search = document.getElementById("hidden_calendar_search");
				var contacts_search = document.getElementById("hidden_contacts_search");
				
				//pegando os valores do checkbox e injetando dentro do formulário
				default_folders.value = (document.getElementById("search_default_folders").checked) ? "1" : ""; 
				personal_folders.value = (document.getElementById("search_personal_folders").checked) ? "1" : ""; 
				calendar_search.value = (document.getElementById("search_calendar_search").checked) ? "1" : ""; 
				contacts_search.value = (document.getElementById("search_contacts_search").checked) ? "1" : "";

				var error_message = "";

				//verificando se vai ser possível realizar a consulta
 				if(default_folders.value == "" && personal_folders.value == "" && calendar_search.value == "" && contacts_search.value == "") {
					error_message = "<p>{lang_search_error_message}</p>";
				}
				
				search_name = document.getElementById("search_name");
				
				if( search_name.value == "" || search_name.value.length < 5 ) {
					error_message += "<p>{lang_search_error_message_four_digits}</p>";
				}
				
				if(error_message != "") {
					show_error_message(error_message);
					return false;
				} else {
					return true;
				}
			}
			
			function show_error_message(message) {
				if( document.getElementById("box_aviso_negativo") ) {
					document.getElementById("box_aviso_negativo").innerHTML = '<div class="aviso-negativo"><strong>'+message+'</strong></div>';
				} else { 
					var targetElement = document.getElementById("topo_box");
					var newElement = document.createElement('div');
					newElement.className = "bg-neutro";
					newElement.id = "box_aviso_negativo";
					newElement.innerHTML = '<div class="aviso-negativo"><strong>'+message+'</strong></div>';

					var parent = document.getElementById("topo_box").parentNode;

					if(parent.lastchild == targetElement) {
						parent.appendChild(newElement);
					} else {
						parent.insertBefore(newElement, targetElement.nextSibling);
					}
				}
			}
		
		</script>
		
	</head>
	<body>
		<div id="global">
			<div id="topo_box" class="topo">
				<h1><a href="index.php?menuaction=mobile.ui_mobilemail.change_folder&folder=0" class="title">{global_title}</a></h1>
				<span><a href="{href_logout}">{lang_logout}</a></span>
			</div>
			
			{message_box}
			
			<form method="post" action="index.php?menuaction=mobile.ui_home.search" id="form_busca" onsubmit="return validate_desktop_search()">
				<input type="hidden" name="default_folders" id="hidden_default_folders" value=''/>
				<input type="hidden" name="personal_folders" id="hidden_personal_folders" value=''/>
				<input type="hidden" name="calendar_search" id="hidden_calendar_search" value=''/>
				<input type="hidden" name="contacts_search" id="hidden_contacts_search" value=''/>
				{search}
			</form>
			<div id="navegacao">
				{home}
			</div><!-- INÍCIO CONTEÚDO -->
			<div id="conteudo">
				{content}
			</div>
		</div>
		<div class="rodape">
			<p>Projeto ExpressoLivre 2004 - 2010 :: Licen&ccedil;a de Software</p>
			<p><a href="index.php?menuaction=mobile.ui_home.change_template&template=mini_mobile">{lang_mini_mobile}</a></p>
			<p><a href="{url_expresso}?dont_redirect_if_moble=1">Versão Clássica</a></p>
		</div>
	</body>
</html>
<!-- END mobile_home -->
<!-- BEGIN success_message -->
<div class="bg-neutro" id="box_aviso_positivo">
	<div class="aviso-positivo">
		<strong>{message}</strong>
	</div>
</div>
<!-- END success_message -->
<!-- BEGIN error_message -->
<div class="bg-neutro" id="box_aviso_negativo">
	<div class="aviso-negativo">
		<strong>{message}</strong>
	</div>
</div>
<!-- END error_message -->