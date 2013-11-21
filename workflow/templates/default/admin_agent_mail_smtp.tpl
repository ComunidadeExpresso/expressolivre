{shared_part}
<tr class="th">
	<th class="th">{lang_configuration_option}</th>
	<th class="th">{lang_configuration_value}</th>
</tr>

<!-- BEGIN block_ag_config_option_input -->
<tr class="{row_class}">
  <td width="20%">{ag_config_label_i}</td>
  <td width="80%"><input type="text" {ag_config_size_i} name="{ag_config_name_i}" value="{ag_config_value_i}" /></td>
</tr>
<!-- END block_ag_config_option_input -->

<!-- BEGIN block_ag_config_option_textarea -->
<tr class="{row_class}">
  <td width="20%">{ag_config_label_t}</td>
  <td width="80%"><textarea name="{ag_config_name_t}" COLS="80" ROWS="5">{ag_config_value_t}</textarea></td>
</tr>
<!-- END block_ag_config_option_textarea -->

<!-- BEGIN block_ag_config_option_select -->
<tr class="{row_class}">
  <td width="20%">{ag_config_label_s}</td>
  <td width="80%"><select name="{ag_config_name_s}" >
<!-- BEGIN block_ag_config_option_select_option -->
	<option value="{ag_config_value_s_key}" {ag_config_value_s_selected}>{ag_config_value_s_value}</option>
<!-- END block_ag_config_option_select_option -->
  </select></td>
</tr>
<!-- END block_ag_config_option_select -->
