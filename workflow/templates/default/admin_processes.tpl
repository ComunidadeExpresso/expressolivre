{processes_css}
<script language="javascript1.2" src="workflow/js/jscode/prototype.js"></script>
<script language="javascript1.2" src="workflow/js/jscode/adminInterface.js"></script>
<div style="color:red; text-align:center">{message}</div>
<div>
	<div>
		{proc_bar}
	</div>
	<div>
		{errors}
	</div>
</div>

<form action="{form_action}" method="post">
<input type="hidden" name="version" value="{version}" />
<input type="hidden" name="p_id" value="{p_id}" />
<input type="hidden" name="search_str" value="{search_str}" />
<input type="hidden" name="start" value="0" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />

<br/>
<table id="processesPropertiesTable" style="border: 1px solid black;width:100%; margin-bottom:10px">
	<tr>
		<td colspan="2" style="font-size: 120%; font-weight:bold">
			{lang_Add_or_edit_a_process} - <a id="toggleLink" href="javascript:toggleTableVisibility('processesPropertiesTable')">Contrair</a>
		</td>
	</tr>
	<tr>
	</tr>
	<tr class="row_on">
		<td>{txt_Process_Name}</td>
		<td><input type="text" maxlength="80" name="name" value="{name}" /> ver:{version}</td>
	</tr>
	<tr class="row_off">
	 	<td>{lang_Description}</td>
	 	<td><textarea rows="5" cols="60" name="description">{description}</textarea></td>
	</tr>
	<tr class="row_on">
		<td>{lang_is_active}?</td>
		<td><input type="checkbox" name="isActive" {is_active} /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Config_values}</td>
		<td>
		<table width="100%">
			<tr class="head">
				<td>
					{txt_consult_site_config_with_link}
				</td>
			</tr>
	<!-- BEGIN block_config_table -->
			<!-- BEGIN block_config_table_empty -->
			<tr bgcolor="{color_line}">
				<td colspan="2">
					{config_empty}
				</td>
			</tr>
			<!-- END block_config_table_empty -->
			<!-- BEGIN block_config_table_title -->
			<tr bgcolor="{color_line}">
				<td colspan="2">
					{config_name_trad}
				</td>
			</tr>
			<!-- END block_config_table_title -->
			<!-- BEGIN block_config_table_yesno -->
			<tr bgcolor="{color_line}">
				<td>
					{config_name_trad}
				</td>
				<td>
					<select name="config_yesno[{config_name}]">
                                        <option value="default" {config_default_selected}>{lang_Use_default}</option>
                                        <option value="yes" {config_yes_selected}>{lang_yes}</option>
					<option value="no" {config_no_selected}>{lang_no}</option>
                                        </select>
				</td>
			</tr>
			<!-- END block_config_table_yesno -->
			<!-- BEGIN block_config_table_password -->
			<tr bgcolor="{color_line}">
				<td>
					{config_name_trad}
				</td>
				<td>
				<table>
				<tr><td>
					<input type="password" maxlength="80" name="config_value[{config_name}]" value="{config_value}" />
				</td><td>
					<label><input type="checkbox" name="config_use_default[{config_name}]" {config_use_default_checked} />{txt_Use_Default}</label>
				</td></tr>
				</table>
				</td>
			</tr>
			<!-- END block_config_table_password -->
			<!-- BEGIN block_config_table_text -->
			<tr bgcolor="{color_line}">
				<td>
					{config_name_trad}
				</td>
				<td>
				<table>
				<tr><td>
					<input type="text" maxlength="80" name="config_value[{config_name}]" value="{config_value}" />
				</td><td>
					<label><input type="checkbox" name="config_use_default[{config_name}]" {config_use_default_checked} />{txt_Use_Default}</label>
				</td></tr>
				</table>
				</td>
			</tr>
			<!-- END block_config_table_text -->
			<!-- BEGIN block_config_table_select -->
			<tr bgcolor="{color_line}">
				<td>
					{config_name_trad}
				</td>
				<td>
					<select name="config_value[{config_name}]">
						<option value="default" {config_default_selected}>{lang_Use_default}</option>
						<!-- BEGIN block_config_table_select_option -->
						<option value="{config_option_value}" {config_option_selected} >{config_option_name}</option>
						<!-- END block_config_table_select_option -->
					</select>
				</td>
			</tr>
			<!-- END block_config_table_select -->
	<!-- END block_config_table -->
		</table>
		</td>
	</tr>
	<tr class="th">
		<td>&nbsp;</td>
		<td><input type="submit" name="save" value="{btn_update_create}" /></td>
	</tr>
</table>
</form>

<form enctype="multipart/form-data" action="{form_action}" method="post">
<table style="border: 1px solid black;width:100%; margin-bottom:10px">
<tr class="th">
	<td colspan="2" style="font-size: 120%; font-weight:bold">
		{lang_Or_import_a_process}
	</td>
</tr>
<tr>
  <td style="width: 200px">
	  {lang_Upload_file}:
  </td>
  <td>
	  <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="16" name="userfile1" type="file" /><input style="font-size:9px;" type="submit" name="upload" value="{lang_upload}" />
  </td>
</tr>
<tr>
	<td colspan="2">
		<label><input type="checkbox" name="customImport" id="customImport" onclick="$('newNameRow').style.display = $('newVersionRow').style.display = (this.checked ? '' : 'none');"/>{lang_customize_the_process}</label>
	</td>
</tr>
<tr id="newNameRow" style="display: none;">
	<td>
		{lang_process_name}
	</td>
	<td>
		<input type="text" name="newName" maxlength="80" /> <i>({lang_if_left_blank,_the_original_value_will_be_used})</i>
	</td>
</tr>
<tr id="newVersionRow" style="display: none;">
	<td>
		{lang_process_version}
	</td>
	<td>
		<input type="text" name="newVersion" maxlength="5" size="5"/> <i>({lang_if_left_blank,_the_original_value_will_be_used})</i>
	</td>
</tr>
</table>
</form>

<div style="border: 1px solid black">
<form action="{form_action}" method="post">
<input type="hidden" name="search_str" value="{search_str}" />
<input type="hidden" name="start" value="0" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
<div class="th" style="font-weight:bold; font-size:120%; margin-bottom:4px">{list_processes}</div>
<table width="100%" cellpadding="0" cellspacing="0">
	<tr class="th">
		<td style="text-align:center">
			{lang_Status}:
				<select name="filter_active" onChange="this.form.submit();">
					<option value="" {filter_active_selected_all}>{lang_All}</option>
					<option value="y" {filter_active_selected_y}>{lang_Active}</option>
					<option value="n" {filter_active_selected_n}>{lang_Inactive}</option>
				</select>
			<input size="20" type="text" name="search_str" value="{search_str}" /> <input type="submit" name="filter" value="{lang_Search}">
		</td>
	</tr>
</table>
</form>
<table style="border: 0px ;width:100%;">
	<tr><td colspan="5">
        <table style="border: 0px;width:100%; margin:0 auto">
		<tr class="th" style="font-weight:bold">
                	{left}
	        	<td><div align="center">{lang_showing}</div></td>
	                {right}
        	</tr>
	</table>
<form action="{form_action}" method="post">
<input type="hidden" name="search_str" value="{search_str}" />
<input type="hidden" name="start" value="0" />
<input type="hidden" name="sort" value="{sort}" />
<input type="hidden" name="order" value="{order}" />
	</td></tr>
	<tr class="th" style="font-weight:bold">
		<td>{header_wf_name}</td>
		<td>{header_wf_version}</td>
		<td>{header_wf_is_active}</td>
		<td>{header_wf_is_valid}</td>
		<td>{lang_Action}</td>
	</tr>

<!-- BEGIN block_items -->
<tr bgcolor="{color_line}">
	<td width="40%">
	  <a href="{href_item_name}">{item_name}</a>
	</td>
	<td style="text-align:right;">
	  {item_version}
	</td>
	<td style="text-align:center;">
		{img_active}
	</td>
	<td style="text-align:center;">
		{img_valid}
	</td>
	<td style="text-align:right;">
	  <table><tr>
	  <td><a href="{href_item_minor}"><img src="{img_new}" alt="{lang_New_minor_version}" title="{lang_New_minor_version}"/>{lang_New_minor}</a></td>
	  <td><a href="{href_item_major}"><img src="{img_new}" alt="{lang_New_major_version}" title="{lang_New_major_version}"/>{lang_New_major}</a></td>
	  <td><a href="{href_item_activities}"><img src="{img_activities}" alt="{lang_Activities}" title="{lang_Activities}"/>{lang_Activities}</a></td>
	  <td><a href="{href_item_code}"><img src="{img_code}" alt="{lang_Code}" title="{lang_Code}"/>{lang_Code}</a></td>
	  <td><a href="{href_item_roles}"><img src="{img_roles}" alt="{lang_Roles}" title="{lang_Roles}"/>{lang_Roles}</a></td>
	  <td><a href="{href_item_jobs}"><img src="{img_jobs}" alt="{lang_Jobs}" title="{lang_Jobs}"/>{lang_Jobs}</a></td>
	  <td><a href="{href_item_export}"><img src="{img_export}" alt="{lang_Export}" title="{lang_Export}"/>{lang_Export}</a></td>
		<td><input type="checkbox" name="process[{item_wf_p_id}]" /></td>
	</tr></table>
	</td>
</tr>
<!-- END block_items -->
<tr class="th">
	<td colspan="5" style="text-align:right">
		<input type="submit" onclick="return confirm('Tem certeza que deseja excluir os processos selecionados?');" name="delete" value="{lang_Delete_selected}">
	</td>
</form>
</tr>
</table>
</div>
<script language="javascript">
if ({p_id} == 0)
	toggleTableVisibility('processesPropertiesTable');
</script>
