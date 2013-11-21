<!-- BEGIN page -->
<html>
<head>
<meta http-equiv="Content-Language" content="pt-BR" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<link rel="apple-touch-icon-precomposed" href="./templates/default/images/favicon.png"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<link rel="apple-touch-icon" href="./templates/default/images/favicon.png" />
<link rel="icon" href="./templates/default/images/favicon.png" type="image/x-ico" />
<meta name="apple-touch-fullscreen" content="YES" />
<script type="text/javascript">

function expand_folders() {
	var box = document.getElementById("personal_folder_box");
	box.style.display = (box.style.display == "none") ? "block" : "none";
}

</script>

<form method="post" action="index.php?menuaction=mobile.ui_home.search" id="form_busca">
	{search}
	
	<div class="menu-contexto">
		&nbsp;&nbsp;{lang_new_mail}:
		<button name="email" class="btn-contexto" type="button" onclick='window.location="index.php?menuaction=mobile.ui_mobilemail.new_msg&type=clk"'>{lang_context_email}</button>
		<button name="contato" class="btn-contexto" type="button" onclick='window.location="index.php?menuaction=mobile.ui_mobilecc.contact_add_edit"'>{lang_context_contact}</button>
	</div>
	
	<dl id="lista_miolo">
		<dd style="margin: 0; padding: 0">
			<div class="resultado-titulo">
				<label>
					<input type="checkbox" name="default_folders" value='1' id="search_default_folders" />
					<span class="email-item">
						{lang_my_mail}
					</span>
				</label>
				<div class="rotulo-complementar">{quota_percent}% [{quota_used}/{quota_limit}]</div>
			</div>
		</dd>
		{default_folders_box}
		<dd>
	    	<div class="limpar_div resultado-titulo">
	    		<input type="checkbox" name="personal_folders" value='1' id="search_personal_folders"/>
		    	<label>
		    		<a href="javascript:expand_folders()" class="email-item" style="font-size:12pt;">
		    			{lang_my_folders}
		    		</a>	
		    	</label>    		
	    	</div>
		</dd>
	  	<span id="personal_folder_box" style="display: none;">
			{personal_folders_box}
	   	</span>		
		<!--<dd>			
			<div class="limpar_div resultado-titulo">
				<label>
					<input type="checkbox" name="calendar_search" value='1' id="search_calendar_search">	
					<a href="index.php?menuaction=mobile.ui_mobilecalendar.index" class="email-item" style="font-size:12pt;">
						{lang_my_commitments}
					</a>
				</label>
			</div>	
		</dd>
			{commitments_box}-->
		<dd>			
    		<div class="limpar_div resultado-titulo">
    			<label>
    				<input type="checkbox" name="contacts_search" value='1' id="search_contacts_search">
    				<a href="index.php?menuaction=mobile.ui_mobilecc.init_cc" class="email-item" style="font-size:12pt;">
    					{lang_my_contacts}
    				</a>
    			</label>
    		</div>
		</dd>
	</dl>
</form>
</body>
</html>
<!-- END page -->
<!-- BEGIN folder_block -->
<a href="index.php?menuaction=mobile.ui_mobilemail.change_folder&folder={folder_id}">
	<dd class="{folder_class}" style="clear:both">
		<div class="nome-item"><span>{folder_name}</span></div>
		<div class="contagem">[{folder_unseen}/{folder_total_msg}]</div> 
	</dd>
</a> 
<!-- END folder_block -->
<!-- BEGIN commitment_block -->
<p class="{commitment_class} espacamento"><strong>{commitment_time}</strong> {commitment_title}</p>
<!-- END commitment_block -->