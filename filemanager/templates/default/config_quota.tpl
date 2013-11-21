<!-- BEGIN body -->
<script src='{path_filemanager}/inc/load_lang.php'></script>
<script src='{path_filemanager}/js/connector.js'></script>
<script src='{path_filemanager}/js/common_functions.js'></script>
<script src='{path_filemanager}/js/config.js'></script>

<center>
	<div style="width:700px;border:1px solid #000;">
		<br/>
		<div style="width:660px;text-align:left; border:0px solid #000;">
			<div style=" height:35px;text-align: bottom;">
				<img src="{path_filemanager}/images/button_createdir.png"/>
				<label style="font-size:12px;font-weight:bold;"> {lang_Management_Quota} </label>
			</div>
			<div style="margin:8px 0px; position:relative;height:5px;">	
				<label id="result_folders" style="font-size:10pt; color:red; font-weight:bold;"></label>
			</div>	
			<br clear="all">
			<label>{lang_search} .:</label>
			<input onkeyup="searchDirOrUser(this, event, 'dir');" size="30" type="text" value=""/>
			<font color="red">
				<span id="span_searching1"/>
			</font>
		</div>
		<br/>
		<br/>
		<div style="width:660px; text-align:left; align:">
			<div style="position:relative; float:left; width:350px;">
				<label style>:: Resultado da Busca</label> 
				<span style="margin-left:45px;">
					<label>Quota ::</label>
					<input id="quota_size" size="10" type="text" />
					<span style="margin-left:2px;">mb</span>
				</span>	
			</div>
			</br>
			<select id="search1" onclick="load_quota(this)" size="3" style="width: 400px;"></select>
		</div>
		<div style="width:700px; margin:10px;">
			<input onclick="save_quota()" type="button" value="{lang_save}" />
			<input type="button" onclick="javascript:history.back();" value="{lang_back}" name="back" />
		</div>

	</div>
</center>
<!-- END body -->

