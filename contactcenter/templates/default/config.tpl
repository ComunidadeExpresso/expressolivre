<!-- BEGIN header -->
<script type="text/javascript" src="{cc_config_js}"></script>
<!-- Foi inclu�do essa parte no evento do form (onsubmit....) e foi inclu�do a linha de cima para usar o arquivo .js -->
<form  onsubmit="javascript:cc_attribute_clear(this);" method="POST" action="{action_url}">
<table border="0" align="center">
	<tr class="th">
		<td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
	</tr>
	<tr>
   		<td></td>
	</tr>
	<tr>
		<td colspan="2"><b>{error}</b></td>
	</tr>
   <tr>
   	<td></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
	<tr class="th">
		<td colspan="2" align="center"><b>{lang_ContactCenter_Global_Catalogue_Setup}</b></td>
	</tr>
	<tr>
   		<td></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Select_where_your_Global_Catalogue_is}:</td>
		<td>
			<select name="newsettings[cc_global_source0]">
				<option value="sql" {selected_cc_global_source0_sql}>SQL</option>
				<option value="ldap" {selected_cc_global_source0_ldap}>LDAP</option>
			</select>
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Catalog_Name}:</td>
		<td><input name="newsettings[cc_catalog_name]" value="{value_cc_catalog_name}" size="40" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_LDAP_Host}:</td>
		<td><input name="newsettings[cc_ldap_host0]" value="{value_cc_ldap_host0}" size="40" /></td>
	</tr>
	<tr class="row_on">
		<td>{lang_LDAP_Context}:</td>
		<td><input name="newsettings[cc_ldap_context0]" value="{value_cc_ldap_context0}" size="40" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Account_DN_to_be_used_when_browsing_LDAP}:</td>
		<td><input name="newsettings[cc_ldap_browse_dn0]" value="{value_cc_ldap_browse_dn0}" size="40" /></td>
	</tr>
	<tr class="row_on">
		<td>{lang_Password_for_the_account_above_(if_any)}:</td>
		<td><input name="newsettings[cc_ldap_pw0]" type="password" value="" size="40" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Open_automatic_contact} :</td>
		<td>
			<select name="newsettings[cc_ldap_query_automatic]">
				<option value="true" {selected_cc_ldap_query_automatic_true}>Sim</option>	
				<option value="false" {selected_cc_ldap_query_automatic_false}>N�o</option>							
			</select>
		</td>
	</tr>
	<tr class="row_off">
		<td>{lang_Minimal_character_to_name_search}:</td>
		<td>
			<select name="newsettings[cc_ldap_min]">
				<option value="2" {selected_cc_ldap_min_2}>2</option>
				<option value="3" {selected_cc_ldap_min_3}>3</option>
				<option value="4" {selected_cc_ldap_min_4}>4</option>
			</select>
		</td>
	</tr>
	<tr class="row_on">
		<td>Montar DN do contatos dinamicamente?</td>
		<td>
			<select name="newsettings[cc_ldap_subLevels]">
				<option value="true" {selected_cc_ldap_subLevels_true}>Sim</option>
				<option value="false" {selected_cc_ldap_subLevels_false}>N�o</option>
			</select>
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Recursive_search_on_button_All}?</td>
		<td>
			<select name="newsettings[cc_ldap_recursive]">
				<option value="false" {selected_cc_ldap_recursive_false}>N�o</option>
				<option value="true" {selected_cc_ldap_recursive_true}>Sim</option>
			</select>
		</td>
	</tr>
	<tr class="row_on"> 
 	    <td>{lang_LDAP_max_results}:</td> 
 	    <td><input name="newsettings[cc_ldap_max_results]" value="{value_cc_ldap_max_results}" size="40" /></td> 
 	</tr> 
	
	<tr class="th">
        <td colspan="2">&nbsp;<b>{lang_cc_Set_details_attributes}</b></td>
	</tr>
	<tr>
                <td colspan="2" id="cc_attribute_fields">
                        <div>
                                <input id="config_cc_allow_details" type="checkbox" name="newsettings[cc_allow_details]" {cc_allow_view_details_value} value="details"/> 
                                <label for="config_cc_allow_details" >{lang_cc_Allow_view_details_label}</label>
                       
                        </div>
 						
 						<div align="right" id="cc_attribute_box_adder">
                                <input type="button" onclick="javascript:cc_attribute_add();" name="addattribute" value="{lang_add_button}" /> 
                        </div>
 						
 						<br />
							{attribute_fields}                        
				</td>
	</tr>
  <!-- <tr class="row_on">
  	<td>{lang_objectClass_to_be_used_as_a_Contact}:</td>
  	<td>
  		<select name="newsettings[cc_ldap_objectclass0]">
			<option value="op_iop" {selected_cc_ldap_objectclass0}>organizationalPerson+inetOrgPerson</option>
      		<option value="custom" {selected_cc_ldap_objectclass0}>{lang_Custom} {lang_(Not_implemented_yet)}</option>
      	</select>
  </tr>
	<tr class="row_off">
		<td>{lang_Custom_objectClass_name}:<br>
			<span style="font-size: 10px;">{lang_(if_your_LDAP_contact_has_multiple_objectClass_attributes,_enter_just_one!)}</span></td>
		<td><input name="newsettings[cc_ldap_custom_objectclass0]" type="text" value="{value_cc_ldap_custom_objectclass0}" size="40"></td>
	</tr>
	
	<tr class="th"><td colspan="2" align="center"><b>{lang_Custom_LDAP_objectClass_Fields_association}</b></td></tr>
	
	<tr class="row_on" style="font-weight: bold;">
	<td>{lang_ContactCenter_Field}</td><td>{lang_LDAP_field}</td>
	</tr>
	<tr class="row_off">
		<td>{lang_Contact_Photo}:</td>
		<td><input type="text" size="40" name="newsettings[cc_ldap_ass_photo0]" value="{value_cc_ldap_ass_photo0}"></td>
	</tr>
	<tr>
		<td>{lang_Contact_Alias}:</td>
		<td><input type="text" size="40" name="newsettings[cc_ldap_ass_alias0]" value="{value_cc_ldap_ass_alias0}"></td>
	</tr>
	<tr>
		<td>{lang_Contact_Prefix}:</td>
		<td><input type="text" size="40" name="newsettings[cc_ldap_ass_prefix0]" value="{value_cc_ldap_ass_prefix0}"></td>
	</tr>
	<tr>
		<td>{lang_Contact_Given_Names}:</td>
		<td><input type="text" size="40" name="newsettings[cc_ldap_ass_given0]" value="{value_cc_ldap_ass_given0}"></td>
	</tr>
	<tr>
		<td>{lang_Contact_Family_Names}:</td>
		<td><input type="text" size="40" name="newsettings[cc_ldap_ass_family0]" value="{value_cc_ldap_ass_family0}"></td>
	</tr>
	<tr>
		<td>{lang_Contact_Full_Name}:</td>
		<td><input type="text" size="40" name="newsettings[cc_ldap_ass_fulln0]" value="{value_cc_ldap_ass_fulln0}"></td>
	</tr>-->

	
<!-- END body -->
<!-- BEGIN footer -->
	<tr class="th">
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="{lang_submit}">
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	</tr>
</table>
</form>
<!-- END footer -->
