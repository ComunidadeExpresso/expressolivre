<!-- BEGIN page -->

<script type="text/javascript">

function mobile_add_contact(_add_to) {
	document.getElementById("menuaction").value = "mobile.ui_mobilemail.init_schedule";
	document.getElementById('add_to').value = _add_to;
	
	document.getElementById('mail_form').submit();
}

function add_file() {
	var anexo_box = document.getElementById("anexo_box");
	var anexo_element = document.createElement("div");
	anexo_box.innerHTML += "<input name=\"FILES[]\" type=\"file\">";
}

function save_msg_as_draft() {
	document.getElementById('menuaction').value = "mobile.ui_mobilemail.save_draft";
	document.getElementById('mail_form').submit();
}

</script>
	
<div class="menu-contexto">
	<span><a href="{href_back}">{lang_back}</a></span> <span class="titulo-secao">{action_msg}</span>
</div>

<form method="POST" action="index.php" enctype="multipart/form-data" id="mail_form">
	<input type="hidden" id="menuaction" name="menuaction" value="mobile.ui_mobilemail.send_mail">
	<input type="hidden" id="reply_from" name="reply_from" value='{from}' />
	<input type="hidden" id="reply_msg_number" name="reply_msg_number" value='{msg_number}' />
	<input type="hidden" id="folder" name="folder" value='{msg_folder}' />
	<input type="hidden" name="type" value='{type}' />
	<input type="hidden" name="add_to" id="add_to" value='' />
	
	<div id="div_campos" style="background:#D4E7F0;">
		<div class="campos-email">
			<a href="javascript:mobile_add_contact('to');" class="email-campos">{lang_to}:</a> 
			<input id="input_to" name="input_to" value='{input_to}' "{read_only}" />
		</div>
		<div class="campos-email">
			<a href="javascript:mobile_add_contact('cc');" class="email-campos">{lang_cc}:</a>
			<input id="input_cc" name="input_cc" value='{input_cc}' "{read_only}" />
		</div>
		<div class="campos-email">
			<span class="assunto">{lang_subject}:</span> 
			<input id="input_subject" name="input_subject" value='{subject}' />
		</div>
		<div class="campos-email" >
			<div style="display:{show_forward_attachment};">
				<span class="assunto">{lang_forward_attachment}:</span>
				{forwarding_attachments}
			</div>
			<div id="anexo_box">
				<span class="assunto">{lang_attachment}: <a href="javascript:add_file();" style="float:none;">({lang_more_attachment})</a> </span>
				<input name="FILES[]" type="file">
			</div>
	</div>
				
	<div id="corpo_mensagem" class="limpar_div" style="background:#EFF8FB; display:table; width:100%" >
		<div id="text_area"><textarea id="body" wrap="virtual" name="body" cols="5" rows="5">{body_value}</textarea></div>
		<div class="limpar_div" style="display:{visible_important} !important;">&nbsp;<input type="checkbox" name="check_important" {check_important} />{lang_mark_as_important}</div>
		<div class="limpar_div">&nbsp;<input type="checkbox" name="check_read_confirmation" {check_read_confirmation} />{lang_read_confirmation}</div>
		<div class="limpar_div" style="display:{show_check_add_history}">&nbsp;<input type="checkbox" name="check_add_history" {check_add_history} />{lang_add_history}</div>
	</div>
	
	<div id="operacao_lista">
		<div class="margin-rodape">
			<button onclick="location.href='{href_back}'" id="cancel" class="btn-generico" >{lang_cancel}</button>
			<button id="save_draft" class="btn-generico" onclick="save_msg_as_draft()" >{lang_save_draft}</button>
			<button name="action" id="reply_send" class="btn-generico" onclick="document.getElementById('mail_form').submit()">{lang_send}</button>
		</div>	
	</div>

</form>
<!-- END page -->

<!-- BEGIN forward_attach_block -->
	<p><input type="checkbox" name="forward_attachments[]" value="{value_forward_attach}" checked/>{label_forward_attach}</p>
<!-- END forward_attach_block -->