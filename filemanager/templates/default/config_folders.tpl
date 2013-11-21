<!-- BEGIN body -->
<script src='{path_filemanager}/inc/load_lang.php'></script>
<script src='{path_filemanager}/js/connector.js'></script>
<script src='{path_filemanager}/js/common_functions.js'></script>
<script src='{path_filemanager}/js/config.js'></script>

<center>
	<div style="width:755px;border:1px solid #000;">
		<br/>
		<div style="width:660px;text-align:left; border:0px solid #000;">
			<div style=" height:35px;text-align: bottom;">
				<img src="{path_filemanager}/images/gerenciamento.gif"/>
				<label style="font-size:12px;font-weight:bold;"> {lang_Folder_ Management} </label>
			</div>
			<div style="margin:8px 0px; position:relative;height:5px;">	
				<label id="result_folders" style="font-size:10pt; color:red; font-weight:bold;"></label>
			</div>	
			<br clear="all">
			<label>{lang_search} .:</label>
			<input onkeyup="searchDirOrUser(this,event, 'dir');" size="30" type="text" value=""/>
			<font color="red">
				<span id="span_searching1"/>
			</font>
		</div>
		<br/>
		<br/>
		<div style="width:660px; text-align:left; align:">
			<div style="position:relative; float:left; width:300px;">
				<label>:: Resultado da Busca </label><br/>
				<select name="search1" id="search1" size="5" style="width:500px;"></select>
			</div>
		</div>

		<br/>

		<br clear="all">
		<br clear="all">

		<div style="width:700px; margin:10px;">
			<input onclick="delete_folder()" type="button" value="{lang_remove}" />
			<input onclick="reconstruct_folder()" type="button" value="{lang_reconstruct}" />
			<input onclick="rename_folder()" type="button" value="{lang_rename}" />
			<input onclick="create_folder()" type="button" value="{lang_create}" />
			<br/>	
			<br/>
			<input type="button" onclick="javascript:history.back();" value="{lang_back}" name="back" />
		</div>	

	</div>
</center>

<!-- END body -->