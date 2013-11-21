<!-- BEGIN page -->

<script type="text/javascript">

function delete_msg() {
			if ( confirm( '{lang_confirm_delete_message}' ) )
				document.location.href = 'index.php?menuaction=mobile.ui_mobilemail.delete_msg&msg_number={msg_number}&msg_folder={msg_folder}';
}

</script>

<div class="menu-contexto">
	<span><a href="{href_back}">{lang_back}</a><span class="titulo-secao">{lang_reading_message}</span>
</div>

<div id="campos-correspondencia">
	<div class="bloco">
    <div class="container">
      <div id="block-label">{lang_from}:</div>
      <div id="block-dados">{from}</div>
	  </div>
    <div class="container">
      <div id="block-label">{lang_to}:</div>
      <div id="block-dados">{to}</div>
	  </div>	  
    <div class="container">
      <div id="block-label">{lang_cc}:</div>
      <div id="block-dados">{cc}</div>
	  </div>	
    <div class="container">
      <div id="block-label">{lang_subject}:</div>
      <div id="block-dados">{subject}{attachment_alert_box}<span class="btn-anexo">{date} - {size}</span></div>
	  </div>		  
	</div>	  	
</div>

<div id="corpo_mensagem">
	{body}

	<p>{attachment_message}</p>
</div>

<div id="operacao_lista">
	<div class="margin-rodape">
		{operation_box}
	</div>
</div>

<!-- END page -->

<!-- BEGIN attachment_alert_block -->
<img src="templates/{theme}/images/anexo.png" align="top" />
<!-- END attachment_alert_block -->

<!-- BEGIN operation_block -->
<button onclick="{operation_link}" id="{operation_id}" class="btn-generico" style="margin-top: 1px">{lang_operation}</button>
<!-- END operation_block -->
