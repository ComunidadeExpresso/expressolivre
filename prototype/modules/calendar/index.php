<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<script type='text/javascript' src='js/debug.js'></script>
<!--<link rel='stylesheet' type='text/css' href="../../../templates/jquery-ui/redmond/jquery-ui.css"/>-->

<!--<link rel='stylesheet' type='text/css' href='../fullcalendar/fullcalendar.css' />-->
<link rel="stylesheet" type="text/css" href="css/reset.css" />
<link rel='stylesheet' type='text/css' href='../../plugins/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='../../plugins/fullcalendar/fullcalendar.print.css' media='print' />
<link rel='stylesheet' type='text/css' href='../../plugins/jquery/jquery-ui.css'/>
<link rel='stylesheet' type='text/css' href='../../plugins/icalendar/jquery.icalendar.css'/>
<link rel="stylesheet" type="text/css" href="../../plugins/fgmenu/fg.menu.css" media="screen"/>
<!--<link type="text/css" href="../../fgmenu/theme/ui.all.css" media="screen" rel="stylesheet" />-->

<link rel='stylesheet' type='text/css' href='../../plugins/fileupload/jquery.fileupload-ui.css'/>
<link rel="stylesheet" type='text/css' href="../../plugins/jquery.pagination/pagination.css" />

<!-- JPicker -->
<link rel="Stylesheet" type="text/css" href="../../plugins/jpicker/css/jPicker-1.1.6.min.css" />
<link rel="Stylesheet" type="text/css" href="../../plugins/jpicker/jPicker.css" />

<link rel="Stylesheet" type="text/css" href="../../plugins/farbtastic/farbtastic.css" />
<link rel="Stylesheet" type="text/css" href="../../plugins/timepicker/jquery-ui-timepicker-addon.css" />
<link rel="stylesheet" type="text/css" href='../../plugins/zebradialog/css/zebra_dialog.css'></link>

<link rel="stylesheet" type="text/css" href="css/layout.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />

<script type='text/javascript' src='../../plugins/datejs/date-pt-BR.js'></script>
<script type='text/javascript' src='../../plugins/jquery/jquery.min.js'></script>
<script type='text/javascript' src='../../plugins/icalendar/jquery.icalendar.js'></script>
<script type='text/javascript' src='../../plugins/jquery/jquery-ui.custom.min.js'></script>
<script type='text/javascript' src="../../plugins/jquery/i18n/jquery.ui.datepicker-pt-BR.js"></script>
<script type='text/javascript' src="../../plugins/timepicker/jquery-ui-timepicker-addon.js"></script>
<script type='text/javascript' src="../../plugins/timepicker/localization/jquery-ui-timepicker-pt-BR.js"></script>
<script type='text/javascript' src='../../plugins/json2/json2.js'></script>
<script type='text/javascript' src='../../plugins/store/jquery.store.js'></script>
<script type='text/javascript' src='../../plugins/fileupload/jquery.fileupload.js'></script>
<script type='text/javascript' src='../../plugins/fileupload/jquery.fileupload-ui.js'></script>
<script type='text/javascript' src='../../plugins/fileupload/jquery.iframe-transport.js'></script>
<script type='text/javascript' src='../../plugins/store/jquery.store.js'></script>
<script type="text/javascript" src="../../plugins/jquery.pagination/jquery.pagination.js"></script>
<script type='text/javascript' src='../../plugins/mask/jquery.maskedinput.js'></script>
<script type='text/javascript' src='../../plugins/alphanumeric/jquery.alphanumeric.js'></script>
<script type='text/javascript' src='../../plugins/watermark/jquery.watermarkinput.js'></script>
<script type='text/javascript' src='../../plugins/encoder/encoder.js'></script>
<script type='text/javascript' src='../../api/datalayer.js'></script>

<!-- Datejs -->    
<!-- <script type='text/javascript' src='../../datejs/core.js'></script> -->
<!-- <script type='text/javascript' src='../../plugins/datejs/date-pt-BR.js'></script>-->
<!-- <script type='text/javascript' src='../../plugins/datejs/globalization/pt-BR.js'></script> -->
<script type='text/javascript' src='../../plugins/datejs/sugarpak.js'></script>
<script type='text/javascript' src='../../plugins/datejs/parser.js'></script>

<!--  <script type='text/javascript' src='../../datejs/time.js'></script>  -->
<script type='text/javascript' src='../../plugins/dateFormat/dateFormat.js'></script>

<!--<script type='text/javascript' src='../fullcalendar/fullcalendar.min.js'></script>-->
<script type='text/javascript' src='../../plugins/fullcalendar/fullcalendar.js'></script>

<script type='text/javascript' src='../../plugins/jquery.dateFormat/jquery.dateFormat.js'></script>

<script type='text/javascript' src='../../plugins/zebradialog/javascript/zebra_dialog.js'></script>
<script type='text/javascript' src='../../plugins/scrollto/jquery.scrollTo.js'></script>
<!-- <script type='text/javascript' src='../../plugins/view/jquerymx-1.0.custom.min.js'></script> -->
<script type='text/javascript' src='../../plugins/ejs/ejs.js'></script>

<script type="text/javascript" src="../../plugins/fgmenu/fg.menu.js"></script>

<script type="text/javascript" src="../../plugins/qtip/jquery.qtip-1.0.0-rc3.min.js"></script>

<!-- JPicker -->
<script type="text/javascript" src="../../plugins/jpicker/jpicker-1.1.6.min.js"></script>
<script type="text/javascript" src="../../plugins/farbtastic/farbtastic.js"></script>

<script type='text/javascript' src='js/base64.js'></script>
<script type='text/javascript' src='js/helpers.js'></script>
<script type='text/javascript' src='js/calendar.codecs.js'></script>
<script type='text/javascript' src='js/I18n.js'></script>
<script type="text/javascript" src="js/init.js"></script>

</head>

<body>
	<div id="wrap" class="expresso-calendar-container" style="text-align:left;">
		<div class="block-horizontal-toolbox">
			<a class="button config-menu main-config-menu" href="#"></a>
			<div class="main-config-menu-content hidden"> 
				<ul>
					<li><a href="#" onclick="add_tab_preferences();" class="menu-command configurations">Prefer&ecirc;ncias</a></l
					<li><a href="#" onclick="add_tab_configure_calendar();" class="menu-command configurations">Configura&ccedil;&atilde;o de agendas</a></li>
					<li><a href="#" onclick="show_modal_import_export(0);" class="menu-pass-through">Importar</a></li>
					<li><a href="#" onclick="show_modal_import_export(1);" class="menu-pass-through">Exportar</a></li>
				</ul>
			</div>		
			<fieldset class="search-field main-search ui-corner-all">
				<span class="ui-icon ui-icon-search"></span>
				<input class="search" type="text" />
			</fieldset>
		</div>
		
		<div class="block-vertical-toolbox">
			<a class="button add add-event" href="#">Adicionar evento</a>
			<!--<a class="button add" href="#" onclick="add_events_list();">Lista de Eventos</a>-->
			<div class="mini-calendar"></div>

			<div class="calendars-list"></div> 

			<div id="trash" class="ui-corner-all empty hidden"  align="middle" valign="middle"><label>Lixeira</label></div>
		</div>

		<div id="tabs"> 
			<ul>
				<li><a href="#calendar">Agenda</a></li>
				<li><a href="#tab_events_list_" onclick="add_events_list();">Lista de Eventos</a></li>
				<!--<li><a href="#sandbox2">Sandbox</a></li>-->
			</ul>
			<div id="calendar"> </div>
			<div id="tab_events_list_"> </div>
			<div id="sandbox2">	</div>
		</div>
		
		<div id="sandbox" class="expresso-calendar-container hidden"> </div>
		<div id="div-import-export-calendar" class="expresso-calendar-container"> </div>
		<div id="div-alarm" class="expresso-alarm-container"> </div>
	</div>
</body>

</html>

