<!-- BEGIN navbar_header -->
<div align="center" id="hiddenButton" style="position:absolute">
</div>
<div align="center" id="extraButton" style="position:absolute">
<table><tr>{app_extra_icons_icon}</tr></table>
</div>
{app_extra_icons_div}
<script language="Javascript">	 
	function showBar(){
		bar = document.getElementById("toolbar");	
		bar.style.visibility = "";
		bar.style.position ="static";
		but = document.getElementById("hiddenButton");		
		but.style.visibility = "";
		but.style.position = "absolute";		
		but.style.top = "55px";		
		but.style.left = "2px";		
		title = "{hide_bar_txt}";
		extra = document.getElementById("extraButton");
		extra.style.visibility = "hidden";		
		but.innerHTML="<a title='"+title+"' onClick='javascript:changeBar()'><img src='{img_root}/up.button.png'></a>";
		var neverExpires = new Date("January 01, 2100 00:00:00");
 		document.cookie = "showHeader=true"+
 						  ";expires=" + neverExpires.toGMTString()+
 						  ";path=/";
	}

	function hideBar(){
		bar = document.getElementById("toolbar");	
		bar.style.position ="absolute";
		bar.style.visibility = "hidden";
		but = document.getElementById("hiddenButton");		
		but.style.visibility = "hidden";
		title = "{show_bar_txt}";
		extra = document.getElementById("extraButton");
		extra.style.visibility = ""
		extra.style.top = "-11px";		
		extra.style.left = "-10px";		
		var neverExpires = new Date("January 01, 2100 00:00:00");
 		document.cookie = "showHeader=false"+
 						  ";expires=" + neverExpires.toGMTString()+
 						  ";path=/";
	}
	function changeBar(){
		bar = document.getElementById("toolbar");			
		if(bar.style.visibility == "hidden")
			showBar();		
		else
			hideBar();
	}
	function initBar(val){

		if(val == 'true')
			showBar();		
		else
			hideBar();		
	}	
</script>

<div  id="toolbar" style="visibility:hidden;position:absolute">
<table border="0" width="100%" cellpadding=0 cellspacing=0><tr>
	<td background="{img_root}/fundo_topo.gif"></td>
	<td align="center" background="{img_root}/fundo_topo.gif">
	<table width="auto" border="0" cellpadding="0" cellspacing="0">{app_icons}</table>
	</td><td style="padding-left:0px;padding-right:25px" align="right" background="{img_root}/fundo_topo.gif" nowrap>
<a name="0"><img src="{img_root}/logo_expresso.png?1"></a></td></tr></table>

</div>
<div id ="divStatusBar">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td width="30%" align="left" id="user_info" nowrap>{user_info}{frontend_name}</td>
  <td width="30%" id="admin_info" nowrap>{current_users}</td>
  <td style="padding-right:10px" width="*" align="right" valign="center" nowrap="true">
  		<a href="{dir_root}/preferences" title="{title_my_preferences}" alt="{title_my_preferences}" onmouseover="javascript:self.status='{title_my_preferences}'" onmouseout="javascript:self.status=''"><img height="15px" src="{dir_root}/phpgwapi/templates/celepar/images/preferences.png"><font size="-1">{my_preferences}</font></a>
  		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
  		<a href="#" title="{title_suggestions}" alt="{title_suggestions}" onmouseover="javascript:self.status='{title_suggestions}'" onmouseout="javascript:self.status=''" onclick="javascript:openWindow(400,550,'{dir_root}/help/enviasugestao.php')"><img src="{dir_root}/phpgwapi/templates/celepar/images/criticas.jpg"><font size="-1">{suggestions}</font></a>
  		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  		<a href="#" title="{title_help}" alt="{title_help}" onmouseover="javascript:self.status='{title_help}'" onmouseout="javascript:self.status=''" onclick="javascript:openWindow(480,510,'{dir_root}/help')"><img src="{dir_root}/phpgwapi/templates/celepar/images/ajuda.jpg"><font size="-1">{help}</font></a>
  </td>
 </tr>
</table>
</div>
<script language="Javascript">
 function openWindow(newWidth,newHeight,link)
  {			
		
	newScreenX  = screen.width - newWidth;	
	newScreenY  = 0;		
	Window1=window.open(link,'',"width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",toolbar=no,scrollbars=yes,resizable=no");
				
  }	
</script>
<!-- END navbar_header -->
<!-- BEGIN appbox -->	
	<div id="divSubContainer">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
		{sideboxcolstart}
<!-- END appbox -->
<!-- BEGIN sidebox_hide_header -->
	<script language="javascript">
		new ypSlideOutMenu("menu2", "right", 0, 165, 160, 200)
	</script>

	<div id="sideboxdragarea" style="position:absolute;left:0px;top:175px">
	<a href="#" {show_menu_event}="ypSlideOutMenu.showMenu('menu2')" onmouseover="//ypSlideOutMenu.showMenu('menu2')" title="{lang_show_menu}"><img src="{img_root}/dragarea_right.png" /></a>
	</div>
	<div id="menu2Container">
	<div id="menu2Content" style="position: relative; left: 0; text-align: left;">
		<table cellspacing="0" cellpadding="0" border="0">
		 <tr><td>
		  
		<div style="background-color:#ffffff;border: #9c9c9c 1px solid;padding:5px;">
<!-- END sidebox_hide_header -->
<!-- BEGIN sidebox_hide_footer -->
</div>
</td><td style="padding-top:10px" valign="top">
<a href="#" onClick="ypSlideOutMenu.hide('menu2')" ><img src="{img_root}/dragarea_left.png" align="right" /></a>
</td></tr></table>
</div>
</div>
<script language="Javascript">
	initBar(GetCookie("showHeader"));
</script>
<!-- END sidebox_hide_footer -->





<!-- BEGIN navbar_footer -->	
		{sideboxcolend}
		<!-- End Sidebox Column -->
		<!-- Applicationbox Column -->
		<td id="tdAppbox" valign="top" {remove_padding}>
		<div id="divAppboxHeader">{current_app_title}</div>
		<div id="divAppbox">
		<table id="tableDivAppbox" width="98%" cellpadding="0" cellspacing="0">
		<tr><td>
<!-- END navbar_footer -->
<!-- BEGIN extra_blocks_header -->
<div class="divSidebox">
	<div class="divSideboxHeader"><span>{lang_title}</span></div>
	<div>
		<table width="100%" cellspacing="0" cellpadding="0" border=0>
<!-- END extra_blocks_header -->
<!-- BEGIN extra_blocks_footer -->
	</table>	
		</div>
		</div>
		<div class="sideboxSpace"></div>
<!-- END extra_blocks_footer -->
<!-- BEGIN extra_block_row -->
<tr class="divSideboxEntry">
<td width="15" align="center" valign="middle" class="textSidebox">{icon_or_star}</td><td class="textSidebox"><a class="textSidebox" href="{item_link}"{target}>{lang_item}</a></td></tr>
<!-- END extra_block_row -->
<!-- BEGIN extra_block_spacer -->
<tr class="divSideboxEntry"> 
	<td colspan="2" height="8" class="textSidebox">&nbsp;</td>
</tr>
<!-- END extra_block_spacer -->
