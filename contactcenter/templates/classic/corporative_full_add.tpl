{cc_api}

<!-- JS MESSAGES -->
<!--input id="cc_msg_err_empty_field" type="hidden" value="{cc_msg_err_empty_field}"-->
<!--input id="cc_msg_err_empty_field_of_contact" type="hidden" value="{cc_msg_err_empty_field_of_contact}"-->
<!--input id="cc_msg_type_state" type="hidden" value="{cc_msg_type_state}"-->
<!--input id="cc_msg_type_city" type="hidden" value="{cc_msg_type_city}"-->
<!-- END JS MESSAGES -->


<!-- WINDOW CONTACT -->
<iframe id="cc_photo_frame" style="position: absolute; top: 0px; left: 0px; visibility:hidden"></iframe>
<input id="cc_contact_title" type="hidden" value="{cc_contact_title}">
<input id="cc_contact_personal" type="hidden" value="{cc_contact_personal}">
<input id="cc_contact_addrs" type="hidden" value="{cc_contact_addrs}">
<input id="cc_contact_corporative" type="hidden" value="{cc_contact_corporative}">
<input id="cc_contact_conns" type="hidden" value="{emails_telephones}">
<input id="cc_pd_full_name" name="{cc_pd_full_name}" type="hidden">
<input id="cc_pd_sex" name="{cc_pd_sex}" type="hidden">
<input id="cc_addr_other" name="{cc_addr_other}" type="hidden">
<input id="cc_addr_po_box" name="{cc_addr_po_box}" type="hidden">
<input id="cc_contact_type" type="hidden" value="{cc_contact_type}">
<!-- _PERSONAL DATA -->
<div id="cc_contact_tab_0" class="row_off div_cc_contact_tab" style="left:-498;top:-347;position: absolute;visibility: hidden;height:197px">
	<form id="cc_full_add_form_personal">
	<input id="cc_full_add_contact_id" type="text" style="display: none">
	<table align="center" width="498px" height="197px" class="row_off" border="0">
		<tr class="row_off">
			<td align="right">{cc_pd_select_photo}:</td>
			<td align="left" colspan="2">
				<!-- Mozilla Method -->
				<input id="cc_pd_select_photo" type="file" accept="image/gif,image/jpeg,image/png" name="cc_pd_photo" onchange="Element('cc_pd_photo').src = 'file://'+Element('cc_pd_select_photo').value">
				<!-- IE Method -->
				<input id="cc_pd_select_photo_t" type="text" name="cc_pd_select_photo_t" readonly>
				<input id="cc_pd_select_photo_b" type="button" style="width: 60px" value="{cc_pd_select_photo_b}" onclick="Element('cc_photo_frame').contentWindow.document.all['cc_photo_input'].click();">
			</td>
			<td align="center" colspan="2" rowspan="3"><img id="cc_pd_photo" src="templates/default/images/photo.png" border="0" width="60px" height="80px"></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_pd_alias}:</td>
			<td align="left" colspan="2"><input id="cc_pd_alias" name="{cc_pd_alias}" type="text" style="width:175px;z-index:-1" value="" maxlength="30"></td>
		</tr>
		<tr style="display:none" class="row_off">
			<td align="right">{cc_pd_prefix}:</td>
			<td align="left" colspan="2"><select id="cc_pd_prefix" name="{cc_pd_prefix}" style="width: 175px;"><option value='0'>{cc_pd_choose_prefix}</option></select></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_pd_given_names}:</td>
			<td align="left" colspan="2"><input id="cc_pd_given_names" name="{cc_pd_given_names}" type="text" style="width: 175px;" value="" maxlength="100"></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_pd_family_names}:</td>
			<td align="left"><input id="cc_pd_family_names" name="{cc_pd_family_names}" type="text" style="width: 175px;" value="" maxlength="100"></td>
			<td align="right">{cc_pd_birthdate}:</td>
			<td align="left">
				<input id="cc_pd_birthdate_0" style="text-align: center;" title="{cc_pd_birthdate_0}" name="{cc_pd_birthdate_0}" type="text" maxlength="{cc_pd_birth_size_0}" size="{cc_pd_birth_size_0}">
				<input id="cc_pd_birthdate_1" style="text-align: center;" title="{cc_pd_birthdate_1}" name="{cc_pd_birthdate_1}" type="text" maxlength="{cc_pd_birth_size_1}" size="{cc_pd_birth_size_1}">
				<input id="cc_pd_birthdate_2" style="text-align: center;" title="{cc_pd_birthdate_2}" name="{cc_pd_birthdate_2}" type="text" maxlength="{cc_pd_birth_size_2}" size="{cc_pd_birth_size_2}">
			</td>
		</tr>
		<tr style="display:none" class="row_off">
			<td align="right">{cc_pd_suffix}:</td>
			<td align="left" colspan="3"><select id="cc_pd_suffix" name="{cc_pd_suffix}" style="width: 175px;"><option value="0">{cc_pd_choose_suffix}</option></select></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_pd_gpg_finger_print}:</td>
			<td colspan="3" align="left"><input id="cc_pd_gpg_finger_print" name="{cc_pd_gpg_finger_print}" type="text" style="width: 350px;" value="" maxlength=""></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_pd_notes}:</td>
			<td colspan="3" align="left"><textarea id="cc_pd_notes" name="{cc_pd_notes}" style="width: 350px; height: 180px;"></textarea></td>
		</tr>
	</table>
	</form>
</div>

<!-- _ADDRESSES -->
<div id="cc_contact_tab_1" class="row_off div_cc_contact_tab" style="position: absolute; visibility: hidden;height:197px;">
	<form id="cc_full_add_form_addrs">
	<table align="center" width="498px" height="197px" border="0">
		<tr class="row_off">
			<td align="right">{cc_addr_types}:</td>
			<td align="left" colspan="3">
				<select id="cc_addr_types" name="{cc_addr_types}" style="width: 200px;" onchange="updateAddressFields()">
					<option value="_NONE_">{cc_addr_choose_types}</option>
				</select>
			</td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_countries}:</td>
			<td align="left" colspan="3">
				<select id="cc_addr_countries" name="{cc_addr_countries}" style="width: 200px;" onchange="updateAddrStates()">
					<option value="_NONE_">{cc_addr_choose_countries}</option>
					{cc_addr_country_list}
				</select>
			</td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_states}:</td>
			<td align="left" colspan="3">
				<select id="cc_addr_states" name="{cc_addr_states}" style="width: 200px;" onchange="updateAddrCities();">
					<option value="_NONE_">{cc_addr_choose_states}</option>
				</select>
				<input id="cc_addr_states_new" style="display:none;width: 150px;" type="text" onmouseover="updateAddrNewStateOnMouseOver();" onmouseout="updateAddrNewStateOnMouseOut();">
			</td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_cities}:</td>
			<td align="left" colspan="3">
				<select id="cc_addr_cities" style="width: 200px;" onchange="updateAddrFillingFields();">
					<option value="_NONE_">{cc_addr_choose_cities}</option>
				</select>
				<input id="cc_addr_cities_new" style="display:none;width: 150px;" type="text" onmouseover="updateAddrNewCityOnMouseOver();" onmouseout="updateAddrNewCityOnMouseOut();">
			</td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_1}:</td>
			<td align="left"><input id="cc_addr_1" name="{cc_addr_1}" style="width: 200px;" type="text" name="" value="" maxlength="60"></td>
			<td align="right">{cc_addr_complement}:</td>
			<td align="left"><input id="cc_addr_complement" name="{cc_addr_complement}" style="width: 100px;" type="text" name="" value="" maxlength="30"></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_2}:</td>
			<td align="left"><input id="cc_addr_2" name="{cc_addr_2}" style="width: 200px;" type="text" name="" value="" maxlength="60"></td>
			<td align="right">{cc_addr_postal_code}:</td>
			<td align="left"><input id="cc_addr_postal_code" name="{cc_addr_postal_code}"style="width: 70px;" type="text" name="" value="" maxlength="15"></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_is_default}:</td>
			<td colspan="3" align="left"><input id="cc_addr_is_default" type="checkbox" name=""></td>
		</tr>
		<tr style="visibility: hidden; position: absolute;">
			<td><input id="cc_addr_id" type="hidden"></td>
		</tr>
	</table>
	</form>
</div>

<!-- _CONNECTIONS -->
<div id="cc_contact_tab_2" class="row_off div_cc_contact_tab" style="position: absolute; visibility: hidden;height:327px;width:498px">
	<table align="left" width="100%"  height="327px" border=0>
	<tbody>
		<tr class="th" width="100%" align="center" height="10px">
			<td width="60px" noWrap><input type="radio"  name="cc_conn_type" id="cc_conn_type_1" value="Email" onclick="javascript:updateConnFields();">{email}</td>
			<td width="60px" noWrap><input type="radio" name="cc_conn_type" id="cc_conn_type_2" value="Telefone" onclick="javascript:updateConnFields();">{telephone}</td>
			<td width="*" align="left"><select style="width:160px" id="cc_conn_type_sel" onchange="javascript:connAddNewLine();"><option value="-1">{choose_email_telephone}</option></select></td>
		</tr>
		<tr class="row_off">			
			<td valign="top" colspan="4" width="100%" style="border: 0px solid black" cellpadding="0" cellspacing="0">
				<table align="left" width="100%" style="border: 0px solid black">
				<tbody id="cc_conn">&nbsp;
					<!-- Code inside here is inserted dynamically -->
				</tbody>				
				</table>
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td align="center" colspan="4">&nbsp;</td>
		</tr>
	</tbody>
	</table>
	<div style="z-index:10000;position: absolute;display:none; top: 310px; left: 78px;" id="div_cc_conn_is_default"> {cc_default} &nbsp;<select id="cc_email_default" name="cc_email_default" disabled style="display:none"></select><select id="cc_phone_default" name="cc_phone_default" disabled style="display:none"></select></div>
</div>

<!-- _Corporative -->
<div id="cc_contact_tab_3" class="row_off div_cc_contact_tab" style="position: absolute; visibility: hidden;height:165px;width:498px">
	<form id="cc_full_add_form_corporative">
	<table align="left" width="100%"  height="165px" border=0>
	    <tr class="row_off">
			<td align="right">{cc_name_corporate}:</td>
			<td align="left" colspan="2" style="width: 300px;"><input id="cc_name_corporate" name="{cc_name_corporate}" style="width: 200px;" type="text" name="" value="" maxlength="100"></td>				
		</tr>
		<tr class="row_on">
			<td align="right">{cc_job_title}:</td>
			<td align="left" colspan="2" style="width: 300px;"><input id="cc_job_title" name="{cc_job_title}" style="width: 200px;" type="text" name="" value="" maxlength="40"></td>			
		</tr>
		<tr class="row_off">
			<td align="right">{cc_department}:</td>
			<td align="left" colspan="2" style="width: 300px;"><input id="cc_department" name="{cc_department}" style="width: 200px;" type="text" name="" value="" maxlength="30"></td>			
		</tr>
		<tr class="row_on">
			<td align="right">{cc_web_page}:</td>
			<td align="left" colspan="2" style="width: 300px;"><input id="cc_web_page" name="{cc_web_page}" style="width: 200px;" type="text" name="" value="" maxlength="100"></td>			
		</tr>
		
	<tbody>
		<tr>
			<td align="center" colspan="4">&nbsp;</td>
		</tr>
	</tbody>
	</table>
	</form>	
</div>

<!-- _BOTTOM BUTTONS -->
<div align="center" id="cc_contact_tab_buttons" style="position: absolute; visibility: hidden; top: 390px; left: 0px; width: 498px; height: 32px; border: 0px solid black">
	<table class="row_off" align="center" width="498px" cellpadding="2" cellspacing="0" border="0">
		<tr class="row_off" id="cc_contact_sharing">
			<td >{cc_contact_shared}: </td>
			<td colspan="2">
					<select id="cc_contact_shared_types" name="{cc_contact_shared}" style="width: 390px;">
                                       		<option value="_NONE_">{cc_contact_shared_types}</option>
                                	</select>
			</td>
		</tr>
	</table>
	<table class="row_off" align="center" width="498px" cellpadding="2" cellspacing="0" border="0">
		<tr>
			<td align="center">
				<input id="cc_contact_save" style="width: 100px;" type="button" value="{cc_contact_save}" onclick="javascript:postFullAdd();">
				<input id="cc_contact_reset" style="width: 100px;" type="button" value="{cc_contact_reset}" onclick="javascript:resetFullAdd();">
				<input id="cc_contact_cancel" style="width: 100px;" type="button" value="{cc_contact_cancel}" onclick="javascript:closeFullAdd();">
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
<!--
//	Overloading some methods for fix cursor problem in Firefox.
	if(!is_ie) { 
		dJSWin.prototype.close = function() {		
			dJSWin.state = 0;
			dd.elements[this.title.id].hide();
			if ( dd_div = document.getElementById('divScrollMain'))	
				Element("divScrollMain").style.overflow = 'auto';	
		}
		dJSWin.prototype.open = function() {
			this.moveTo(window.innerWidth/2 + window.pageXOffset - dd.elements[this.title.id].w/2,
			    window.innerHeight/2 + window.pageYOffset - dd.elements[this.clientArea.id].h/2);
			dd.elements[this.title.id].maximizeZ();
			dd.elements[this.title.id].show();
			if ( dd_div = document.getElementById('divScrollMain'))
				dd_div.style.overflow = 'hidden';
		}
	}	
		
	var fullAdd_onload = document.body.onload;
	var tabs;
	var fullAddWin;
	var photo_frame, photo_form, photo_input;	

	__f = function(e)
	{
		
		tabs = new dTabsManager({'id': 'cc_contact_tab', 'width': '500px'});
		
		tabs.addTab({'id': 'cc_contact_tab_0', 
					 'name': Element('cc_contact_personal').value, 
					 'selectedClass': 'tab_box_active', 
					 'unselectedClass': 'tab_box'});
					 
		tabs.addTab({'id': 'cc_contact_tab_2', 
					 'name': Element('cc_contact_conns').value, 
					 'selectedClass': 'tab_box_active', 
					 'unselectedClass': 'tab_box'});

		tabs.addTab({'id': 'cc_contact_tab_1', 
					 'name': Element('cc_contact_addrs').value, 
					 'selectedClass': 'tab_box_active', 
					 'unselectedClass': 'tab_box'});
					 					 
		tabs.addTab({'id': 'cc_contact_tab_3', 
					 'name': Element('cc_contact_corporative').value, 
					 'selectedClass': 'tab_box_active', 
					 'unselectedClass': 'tab_box'});
		
		fullAddWin = new dJSWin({'id': 'cc_full_add_window',
		                         'content_id': 'cc_contact_tab',
					 'win_class': 'row_off',
					 'width': '500px',
					 'height': '420px',
					 'title_color': '#3978d6',
					 'title': Element('cc_contact_title').value,
					 'title_text_color': 'white',
					 'button_x_img': Element('cc_phpgw_img_dir').value+'/winclose.gif',
					 'include_contents': new Array('cc_contact_tab_0', 'cc_contact_tab_1', 'cc_contact_tab_2','cc_contact_tab_3','cc_contact_tab_buttons'),
					 'border': true});

		fullAddWin.draw();		
		 				
		if (is_ie)
		{
			Element('cc_photo_frame').src = 'cc_photo_frame.html';
			Element('cc_pd_select_photo').style.display='none';
			fullAddWin.open();
			tabs._showTab('cc_contact_tab_0');
			fullAddWin.close();
		}
		else
		{
			Element('cc_pd_select_photo_t').style.display='none';
			Element('cc_pd_select_photo_b').style.display='none';			
		}
		
	};

	if (is_ie) // || is_moz1_6)
	{
			
		document.body.onload = function(e) { setTimeout('__f()', 10); fullAdd_onload ? setTimeout('fullAdd_onload()'): false;};
	}
	else
	{
//		__f();
	}

//-->
</script>
<!-- END WINDOW CONTACT -->










<!-- RELATIONS 
<div id="cc_contact_tab_3" class="row_off div_cc_contact_tab">
	<table align="center" width="500px" height="100%" cellpadding="2" cellspacing="0" border="0">
		<tr class="row_off">
			<td align="right"><input style="width: 240px;" type="text"></td>
			<td align="left"><input style="width: 150px;" type="button" value="{cc_btn_search}"></td>
		</tr>
		<tr class="row_on">
			<td align="left">{cc_results}:</td>
			<td align="left">{cc_is_my}:</td>
		</tr>
		<tr class="row_off">
			<td align="right"><select style="width: 240px; height: 150px;" multiple></select></td>
			<td align="left"><select id="cc_rels_type" style="width: 240px; height: 150px;" multiple></select></td>
		</tr>
		<tr class="row_on">
			<td align="right"><input style="width: 150px;" type="button" value="{cc_add_relation}"></td>
			<td align="left"><input style="width: 150px;" type="button" value="{cc_del_relation}"></td>
		</tr>
		<tr class="row_off">
			<td align="center" colspan="2"><select style="width: 480px; height: 120px;" multiple></select></td>
		</tr>
	</table>
</div>
-->
