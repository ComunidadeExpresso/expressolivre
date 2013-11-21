	<center>
		<table border="0" cellspacing="1" cellpadding="2">
<!-- BEGIN search -->
			<tr>
				<td colspan="5" align="left" valign="top"><form name="form1" method="post" onSubmit="return on_submit()" action="{action_nurl}">&nbsp;					
					<input type="radio" onclick="javascript:changeElement();" name="typesearch" value="g" {type_search_g_checked}/>{lang_group_name}&nbsp;
					<input type="radio" onclick="javascript:changeElement();" name="typesearch" value="c" {type_search_c_checked}/>{lang_cat_name}&nbsp;
					<input type="radio" onclick="javascript:changeElement();" name="typesearch" value="a" {type_search_a_checked}/>{lang_cat_all}<br>
					<div id="filterByGroup" style="display:none"><br>
				    	{lang_Search_for}&nbsp;<input type="text" id="search_group" size=30 autocomplete="off" onkeyup="javascript:search_object(this,'cal_span_searching','groupsfound','g')"/><br>
			            &nbsp;<font color="red"><span id="cal_span_searching">&nbsp;</span></font><br/>
			            <input type="hidden" id="group" name="group"/>
			            <select id="groupsfound" style="width: 300px" size="4"></select>&nbsp;<input type="submit" value="{lang_search}">
					</div>
					<div id="filterByName" style="display:none"><br>
					<input id="query" type="text" name="query" value="">&nbsp;<input type="submit" value="{lang_search}">
					</form>
				</td>
			</tr>
<!-- END search -->
		
			<tr>
				<td colspan="6" align="left">
					<table border="0" width="100%">
						<tr>
						{left}
							<td align="center">{lang_showing}</td>
						{right}
						</tr>
					</table>
				</td>
			</tr>
			<tr class="th">
				<td width="20%">{sort_name}</td>
				<td width="32%">{sort_description}</td>
				<td width="1%" align="center">{lang_icon}</td>
				<td width="8%" align="center">{lang_permission}</td>
				<td width="8%" align="center">{lang_sub}</td>
				<td width="8%" align="center">{lang_edit}</td>
				<td width="8%" align="center">{lang_delete}</td>
			</tr>

<!-- BEGIN cat_list -->

			<tr bgcolor="{tr_color}" {color}>
				<td>{name}</td>
				<td>{descr}</td>
				<td align="center">{icon}</td>
				<td align="center">{permission}</td>
				<td align="center">{add_sub}</a></td>
				<td align="center">{edit}</a></td>
				<td align="center">{delete}</a></td>  
			</tr>

<!-- END cat_list -->

			<tr valign="bottom" height="50">
			<form method="POST" action="{action_url}">
<!-- BEGIN add -->
				<td><input type="submit" name="add" value="{lang_add}"> &nbsp;
<!-- END add -->
				<input type="submit" name="done" value="{lang_cancel}"></td>
				<td colspan="5">&nbsp;</td>
			</form>
			</tr>
		</table>
	</center>
{scripts}
<input type="hidden" id="txt_loading" value="{lang_Loading}">
<input type="hidden" id="txt_searching" value="{lang_Searching}">
<input type="hidden" id="txt_typemoreletters" value="{lang_typemoreletters}">
<script language="JavaScript" type="text/javascript">
	function on_submit(){
		var select_group = document.getElementById("groupsfound");
		if(document.getElementById("query").value == "" && select_group.value == ""){
			return false;
		}
		else if(select_group.options.length == 0)
			return true;			
		for(j in select_group.options){
			if(select_group.selectedIndex == j)
				document.getElementById("group").value =  select_group.options[j].value+"."+select_group.options[j].text;
		}
		return true;		
	}
	function changeElement(){
		var types =	document.form1.typesearch;
		for(j in types){
			if(types[j].checked){
				if(types[j].value == 'a'){				
					location.href = location.href.toString();
				}
				document.getElementById('filterByGroup').style.display 	= (types[j].value == 'g' ? '' : 'none');
				document.getElementById('filterByName').style.display	= (types[j].value == 'g' || types[j].value == 'a' ? 'none' : '');
			}
		}
	}
</script>