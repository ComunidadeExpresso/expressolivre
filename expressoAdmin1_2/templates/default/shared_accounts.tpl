<!-- BEGIN body -->
	<link rel="stylesheet" type="text/css" href="./expressoAdmin1_2/templates/default/shared_accounts.css"/>
        
        
        <link rel="Stylesheet" type="text/css" href="prototype/plugins/jquery/jquery-ui.css" />					
    
        <script type="text/javascript" src="prototype/plugins/jquery/jquery.min.js"></script>

        <script type="text/javascript" src="prototype/plugins/jquery/jquery-ui.min.js"></script>
        <script type="text/javascript" src="prototype/plugins/jquery/jquery-ui.custom.min.js"></script>
        <script src="prototype/plugins/json2/json2.js" language="javascript"></script>
        <script src="prototype/plugins/store/jquery.store.js" language="javascript"></script>

        <script src="prototype/api/datalayer.js" language="javascript"></script>	

        <script language="javascript">DataLayer.dispatchPath = "prototype/";</script>
        <script type="text/javascript" src="prototype/plugins/datejs/date-pt-BR.js"></script>
        <script type="text/javascript" src="prototype/plugins/datejs/sugarpak.js"></script>
        <script type="text/javascript" src="prototype/plugins/datejs/parser.js"></script>

        <script type="text/javascript" src="prototype/modules/calendar/js/timezone.js"></script>	

        <script type="text/javascript" src="prototype/modules/calendar/js/calendar.codecs.js"></script>

	<div style="display:none" id="{modal_id}">{shared_accounts_modal}</div>

	<div align="center">
		<table border="0" width="90%">
			<tr>
				<td align="left" width="25%">
					<input type="button" value="{lang_create_shared_account}" "{create_share:_account_disabled}" onClick='{onclick_create_shared_account}'>
					<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
				</td>
				<td align="center" "left" width="50%">
					{lang_contexts}: <font color="blue">{context_display}</font>
				</td>
				<td align="right" "left" width="25%">
						{lang_to_search}:
						<input type="text" onKeyUp="javascript:get_shared_accounts_timeOut(this.value, event)" id="ea_shared_account_search" autocomplete="off" value="{query}">
				</td>
			</tr>
		</table>
	</div>
 
	<div align="center" id="shared_accounts_content">
		<table border="0" width="90%">
			<tr bgcolor="{th_bg}">
				<td width="30%">{lang_full_name}</td>
				<td width="30%">{lang_display_name}</td>
				<td width="30%">{lang_mail}</td>
				<td width="5%" align="center">{lang_remove}</td>
			</tr>
		</table>
	</div>	

	<div align="center">
		<table border="0" width="90%">
			<tr>
				<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
			</tr>
		</table>
	</div>

<!-- END body -->
