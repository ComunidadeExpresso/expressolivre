<!-- BEGIN form -->
<input type="hidden" id="txt_loading" value="{lang_Loading}">
<input type="hidden" id="txt_searching" value="{lang_Searching}">
<input type="hidden" id="txt_multipleGroup" value="{lang_Groups}">
<input type="hidden" id="txt_typemoreletters" value="{lang_typemoreletters}">
<br>
<center>
{message}<br>
<table border="0" width="80%" cellspacing="2" cellpadding="2"> 
<form name="edit_cat" action="{actionurl}" method="POST">
{hidden_vars}
	<tr class="th">
		<td colspan="2">{lang_parent}</td>
		<td><select name="new_parent"><option value="">{lang_none}</option>{category_list}</select></td>
	</tr>
	<tr class="row_on">
		<td colspan="2">{lang_name}</font></td>
		<td><input name="cat_name" size="50" value="{cat_name}"></td>
	</tr>
	<tr class="row_off">
		<td colspan="2">{lang_descr}</td>
		<td colspan="2"><textarea name="cat_description" rows="4" cols="50" wrap="virtual">{cat_description}</textarea></td>
	</tr>
        <tr class="row_on">
         	<td colspan="2">{lang_Search_for}</td>
            <td valign="center" colspan="2"><input type="text" id="search_group" size=30 autocomplete="off" onkeyup="javascript:search_object(this,'cal_span_searching','groupsfound','g')"/>
            &nbsp;<font color="red"><span id="cal_span_searching">&nbsp;</span></font><br/>
            <select multiple id="groupsfound" style="width: 300px" size="4"></select>
            <button type="button" onClick="javascript:add('groupsfound','td_group');"><img src="{template_set}/images/add.png" style="vertical-align: bottom;"/></button></td>
        </tr>
	<tr class="row_off">
		<td colspan="2">{lang_Owner}</td>
		<td id="td_group" id="namegroup" valign="center" colspan="2">
			<div>
			<label id="{cat_id_group}" style="font-weight:bold">{category_namegroup}</label>
			<button valign="top" type="button" onClick="javascript:remove({cat_id_group});">
			<img src="{template_set}/images/delete.png" style="vertical-align: middle;">
			</button>
			</div>
		</td>
	</tr>

	<tr class="row_on">
		<td colspan="2">{lang_color}</td>
		<td colspan="2">{color}</td>
	</tr>
	<tr class="row_off">
		<td colspan="2">{lang_icon}</td>
		<td colspan="2">{select_icon} {icon}</td>
	</tr>
<!-- BEGIN data_row -->
	<tr class="{class}">
		<td colspan="2">{lang_data}</td>
		<td>{td_data}</td>
	</tr>
	<!-- END data_row -->
	<tr valign="bottom" height="50">
	<input type="hidden" name="cat_id" value="{cat_id}">
	<input type="hidden" name="old_parent" value="{cat_parent}">
	<input type="hidden" id="idgroup" name="idgroup" value="{cat_id_group}">
	<td><input onclick="return verifyCatOwners('td_group')" type="submit" name="save" value="{lang_save}"></td>
		<td><form method="POST" action="{cancel_url}"><input type="submit" name="cancel" value="{lang_cancel}"></form></td>
		<td align="right">{delete}</td>
	</tr>
</table>
</form>
</center>
<!-- END form -->
<script language="JavaScript" type="text/javascript">
function remove(to){
	var to_el = document.getElementById(to);
	var gId = to_el.id;
	document.getElementById('idgroup').value = document.getElementById('idgroup').value.replace(gId,'');
	document.getElementById(to).parentNode.innerHTML = '';
} 
function show_button(id){
	document.getElementById("bt_rem_"+id).style.visibility = 
		(document.getElementById(id).value != '-1'  ? 'visible' : 'hidden');
}
function add(from, to){
	var sel_from = document.getElementById(from);
	to_el = document.getElementById(to);
	for (i = 0 ; i < sel_from.length; i++){
		if (sel_from[i].selected) {
			var div_el = document.createElement('DIV');
			el_name = document.createElement('LABEL');
			el_name.innerHTML = sel_from.options[i].text;
			el_name.id = sel_from[i].value;
			document.getElementById('idgroup').value += ","+sel_from[i].value;
			el_name.style.fontWeight = "bold";
			el_name.innerHTML += '<button valign="top" type="button" onClick="javascript:remove('+sel_from[i].value+');"><img src="{template_set}/images/delete.png" style="vertical-align: middle;"></button>';
			div_el.appendChild(el_name);
			to_el.appendChild(div_el);
		}
	}
}
function verifyCatOwners(field){
	var sel_ = document.getElementById(field);
	var groups = document.getElementById('idgroup').value;
	if (groups.indexOf(',') == 0)
		document.getElementById('idgroup').value = groups.substr(1,groups.length);
	if (document.getElementById('idgroup').value.indexOf(',') != -1)
	{
		var add_mult = confirm("{lang_add_multiple_categories}");
		if (!add_mult)
			return false;
	}
}
</script>
{scripts}
