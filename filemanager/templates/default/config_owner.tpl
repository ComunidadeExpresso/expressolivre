<!-- BEGIN body -->
<script src='{path_filemanager}/inc/load_lang.php'></script>
<script src='{path_filemanager}/js/connector.js'></script>
<script src='{path_filemanager}/js/common_functions.js'></script>
<script src='{path_filemanager}/js/config.js'></script>

<center>
	<div style="width:800px; height: 300px;border:1px solid #000;">
		<br/>
		<div style=" height:35px;text-align:left; margin-left:20px;">
			<img src="{path_filemanager}/images/conference.png"/>
			<label style="font-size:12px;font-weight:bold; margin-left:10px;"> {lang_permissions_groups_users} </label>
		</div>
		<br/>
		<div style="width:300px;text-align:left; border:0px solid #000; position:absolute; float:left; margin:10px;">
			<label style="font-size:9pt !important;">:: {lang_Search_Folders}</label>
			<br/>
			<br/>
			<div style="width:500px;">
				<label>{lang_search}</label>
				<font color="red"><span id="span_searching1">&nbsp;</span></font>
				<br/>
				<input onkeyup="searchDirOrUser(this, event, 'dir');" size="30" type="text" value="" />
				<br clear="all">	
				<br clear="all">	
				<label>{lang_directory}</label>
				<br/>
				<select id="search1" size="3" style="width: 300px;"></select>
			</div>
		</div>

		<div style="width:300px;text-align:left; border:0px solid #000; position: relative;  float:right; margin:10px;">
			<label style="font-size:9pt !important;">:: {lang_Search_Users}</label>
			<br/>
			<br/>
			<div style="width:500px;">
				<label>{lang_search}</label>
				<font color="red"><span id="span_searching2">&nbsp;</span></font>
				<br/>
				<input onkeyup="searchDirOrUser(this, event, 'user' );" size="30" type="text" value="" />
				<br clear="all">	
				<br clear="all">	
				<label>{lang_directory}</label>
				<br/>
				<select id="search2" size="3" style="width: 300px;"></select>
			</div>
		</div>
		<br clear="all" />	
		<br clear="all" />	
		<div style="width: 400px;">
			<label style="margin:3px;"><input name="checkAttr" id="egw_read" type="checkbox"/>{lang_Read}</label>
			<label style="margin:3px;"><input name="checkAttr" id="egw_add" type="checkbox">{lang_Add}</label>
			<label style="margin:3px;"><input name="checkAttr" id="egw_edit" type="checkbox">{lang_Edit}</label>
			<label style="margin:3px;"><input name="checkAttr" id="egw_delete" type="checkbox">{lang_Delete}</label>
			<label style="margin:3px;"><input name="checkAttr" id="egw_private" type="checkbox">{lang_private}</label>
		</div>
		<br clear="all" />	
		<br clear="all" />	
		<div style="width: 330px;">
			<input onclick="set_owner()" type="button" value="{lang_setowner}" />
			<input onclick="set_permission()" type="button" value="{lang_setperm}" />
			<input type="button" onclick="javascript:history.back();" value="{lang_back}" name="back" />
		</div>
	</div>
</center>
<br/>

<!-- END body -->

