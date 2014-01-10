  /******************************************************************************\
  * eGroupWare - Contacts Center												*
  * http://www.egroupware.org													*
  * Written by:																	*
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>			*
  *  - Jonas Goes <jqhcb@users.sourceforge.net>									*
  *  sponsored by Thyamad - http://www.thyamad.com								*
  * -------------------------------------------------------------------------	*
  *  This program is free software; you can redistribute it and/or modify it	*
  *  under the terms of the GNU General Public License as published by the		*
  *  Free Software Foundation; either version 2 of the License, or (at your		*
  *  option) any later version.													*
  \******************************************************************************/

/***********************************************\
*                      TODO                     *
\***********************************************/

/*
 * function setHeightSpace ()
 *
 */

/***********************************************\
*                   CONSTANTS                   *
\***********************************************/

var CC_STATUS_FULL_ADD = 2;
var CC_STATUS_QUICK_ADD = 1;

var CC_card_image_width = 245;
var CC_card_image_height = 130;
var CC_card_extra = 16;


/***********************************************\
*               GLOBALS VARIABLES               *
\***********************************************/

var owners = new Array();
var flag_compartilhado = false;
var qtd_compartilhado = 0;

var CC_visual = 'table';
/* Cards Variables */
var CC_actual_letter = 'a';
var CC_last_letter = 'a';
var CC_actual_page = 1;
var CC_npages = 0;
var CC_max_cards = new Array();
var CC_conn_count=0;

var CC_old_icon_w = 0;
var CC_old_icon_h = 0;

/* Tabs Variables */
var CC_last_tab = 0;

/* Pseudo-Semafores */
var CC_tree_available = false;
var CC_full_add_const = false;
var CC_full_add_photo = false;

var CC_last_height = window.innerHeight;
var CC_last_width = window.innerWidth;

/* Contact Full Info */
var CC_contact_full_info;
var CC_br_index;

/* Addresses Variables */
var CC_addr_last_selected = 0;

/* Connections Variables */
var CC_conn_last_selected = 0;
var not_informed_text;
/* Grupos inicialmente selecionados */
var CC_initial_selected_grps = new Array();



/***********************************************\
 *           FULL ADD/EDIT FUNCTIONS           *
\***********************************************/

function createPhotoFrame()
{
	photo_frame = document.createElement('iframe');
	document.body.appendChild(photo_frame);

	if (is_ie)
	{
		photo_form  = photo_frame.contentWindow.document.createElement('form');
		photo_input = photo_frame.contentWindow.document.createElement('input');
	}
	else
	{
		 photo_form  = photo_frame.contentDocument.createElement('form');
		 photo_input = photo_frame.contentDocument.createElement('input');
	}

	photo_frame.id = 'cc_photo_frame';
	photo_frame.style.position = 'absolute';
	//photo_frame.style.visibility = 'hidden';
	photo_frame.style.top = '600px';
	photo_frame.style.left = '0px';

	photo_form.id = 'cc_photo_form';
	photo_form.method = 'POST';
	photo_form.enctype = 'multipart/form-data';

	photo_input.id = 'cc_photo_input';
	photo_input.type = 'file';

	if (is_ie)
	{
		photo_frame.contentWindow.document.body.appendChild(photo_form);
	}
	else
	{
		photo_frame.contentDocument.body.appendChild(photo_form);
	}
	photo_form.appendChild(photo_input);

}

/********* Full Add Auxiliar Functions ****************/
function selectOption (id, option)
{
	var obj = Element(id);
	var max = obj.options.length;

	if (option == undefined)
	{
		obj.selectedIndex = 0;
	}
	else
	{
		for (var i = 0; i < max; i++)
		{
			if (obj.options[i].value == option)
			{
				obj.selectedIndex = i;
				break;
			}
		}
	}
}

function selectRadio (id, index)
{
	var obj = Element(id);
	var max = obj.options.length;
	for (var i = 0; i < max; i++)
	{
		i == index ? obj.options[i].checked = true : obj.options[i].checked = false;
	}
}

function clearSelectBox(obj, startIndex)
{
	var nOptions = obj.options.length;

	for (var i = nOptions - 1; i >= startIndex; i--)
	{
		obj.removeChild(obj.options[i]);
	}
}
/********** Open/Close FullAdd *************/
function openFullAdd(){
	// Build the FullAdd Window.
	if(!fullAddWin && !is_ie)
		__f();

	resetFullAdd();
	populateFullAddConst();
	fullAddWin.open();
	tabs._showTab('cc_contact_tab_0');
	Element('cc_full_add_window_clientArea').style.background = '#EEE';
	Element("cc_conn_type_1").checked = false;
	Element("cc_conn_type_2").checked = false;
	Element("cc_conn_type_sel").disabled = true;
	Element("cc_conn_type_sel").selectedIndex = 0;
	Element("cc_contact_sharing").style.display = 'none';
}

function openFullAddShared(){

	if (flag_compartilhado)
	{
		if(!fullAddWin && !is_ie)
			__f();

		resetFullAdd();
		populateFullAddConst();
		fullAddWin.open();
		tabs._showTab('cc_contact_tab_0');
		Element("cc_conn_type_1").checked = false;
		Element("cc_conn_type_2").checked = false;
		Element("cc_conn_type_sel").disabled = true;
		Element("cc_conn_type_sel").selectedIndex = 0;
		Element("cc_contact_sharing").align = 'center';
		Element("cc_contact_sharing").style.display = 'block';
		Element("cc_contact_shared_types").disabled = true;
		populateSharingSelect();
	} else
	{
		if(qtd_compartilhado != 0)
		{
			ccTree.select(0.2);
			ccTree.setCatalog(0.2);
			if(!fullAddWin && !is_ie)
				__f();
			resetFullAdd();
			populateFullAddConst();
			fullAddWin.open();
			
			tabs._showTab('cc_contact_tab_0');
			Element("cc_conn_type_1").checked = false;
			Element("cc_conn_type_2").checked = false;
			Element("cc_conn_type_sel").disabled = true;
			Element("cc_conn_type_sel").selectedIndex = 0;
			Element("cc_contact_sharing").aling = 'center';
			Element("cc_contact_sharing").style.display = 'block';
			Element("cc_contact_shared_types").disabled = true;
			populateSharingSelect();
		} else
			showMessage(Element('cc_msg_err_shared').value);
	}
	if(Element('cc_full_add_window_clientArea'))
		Element('cc_full_add_window_clientArea').style.background = '#EEE';
}

function closeFullAdd(){
	fullAddWin.close();
}
/******** Contact details ***********/
function openContactDetails(id){
	// Build the ContactDetails Window.
	if((typeof(contactdetailsWin) == 'undefined') && !is_ie) 
		__cdWin();

	contactdetailsWin.open();
	
	populateContactDetails(id);
}

function populateContactDetails(id)
{
	var handler = function(responseText)
	{
		var fieldsDiv = Element('id_cc_contact_details_fields');
		var data = unserialize(responseText);
		//alert(responseText);
		fieldsDiv.innerHTML = "";
		if (data && data.length > 0)
		{
			//fieldsDiv.innerHTML = "";
			var table = document.createElement("table");
			table.border=0;
			//table.style.borderBottom = '1px solid #999';
			//table.cellSpacing = '0';
			table.width = '480px';
			var attr_name_size = '50%';
			var attr_value_size = '50%';
			for(i = 0; i < data.length; i++)
			{
				var row = table.insertRow(i);
				if ((i % 2) == 0)
					row.setAttribute('class', 'row_off');
				else
					row.setAttribute('class', 'row_on');
				//row.style.borderBottom = '1px solid #999';
				attr_name = row.insertCell(0);
				attr_value = row.insertCell(1);
				attr_name.style.width = attr_name_size;
				attr_value.style.width = attr_value_size;
				attr_name.innerHTML = data[i]['name'];
				if (data[i]['type'] == 'text')
					attr_value.innerHTML = data[i]['value'];
				else
				{
					var multivalue_div = document.createElement("div");
					multivalue_div.style.overflow = 'auto';
					multivalue_div.style.height = '100px';
					multivalue_div.style.border = '1px solid #999';
					//multivalue_div.style.backgroundColor = 'transparent';
					for (j = 0; j < data[i]['value'].length; j++)
					{
						multivalue_div.appendChild(document.createTextNode(data[i]['value'][j]));
						multivalue_div.appendChild(document.createElement("br"));
					}
					attr_value.appendChild(multivalue_div);
				}	
			}
			fieldsDiv.appendChild(table);
		}
		else
			fieldsDiv.innerHTML = Element('cc_contact_details_no_fields').value; 
	};
	Connector.newRequest('populateContactDetails', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_contact_details&id=' + id, 'GET', handler);
}

function closeContactDetails(){
	contactdetailsWin.close();
}
/********** New Contact *************/
function newContact(){
	openFullAdd();
}
function newSharedContact(){
	openFullAddShared();
}
/************ Edit Contact *************/
function editContact (id){
	openFullAdd();
	populateFullEdit(id,'bo_people_catalog');
}
function editSharedContact (id){
	openFullAdd();
	populateFullEdit(id,'bo_shared_people_manager');
}
/************ Edit Group *************/
function editGroup(id){
	populateEditGroup(id);
	ccAddGroup.window.open();
}

function editSharedGroup(id,shared){
	populateEditSharedGroup(id, shared);	
}

/*
	Updates all the constant fields in the
	full add window, like Prefixes, Suffixes,
	Countries and Types
*/

function populateSharingSelect()
{
        var handler = function(responseText)
        {
        	var data = unserialize(responseText);
			var sharers = Element('cc_contact_shared_types');

	        if (typeof(data) != 'object')
            {
                showMessage(Element('cc_msg_err_contacting_server').value);
                fullAddWin.close();
				return;
            }else{
				sharers.disabled = false;
				j = 1;
				for (var i in data)
				{
						sharers.options[j] = new Option(data[i]['cn'], i);
						owners[j] = i;
						j++;
				}
				return;
	         }
        };
	Connector.newRequest('populateSharingSelect', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_list_owners_perms_add', 'POST', handler);
}

function populateFullAddConst()
{
	CC_full_add_const = false;

	setTimeout('populateFullAddConstAsync()', 10);
}

function populateFullAddConstAsync()
{
	var handler = function(responseText)
	{
		//Element('cc_debug').innerHTML = responseText;
		var data = unserialize(responseText);
		var i = 1;
		var j;

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		/* Populate Prefixes */
		for (j in data[0])
		{
			Element('cc_pd_prefix').options[i] = new Option(data[0][j], j);
			i++;
		}

		/* Populate Suffixes */
		i = 1;
		for (j in data[1])
		{
			Element('cc_pd_suffix').options[i] = new Option(data[1][j], j);
			i++;
		}

		/* Populate Addresses Types */
		i = 1;
		for (j in data[2])
		{
			Element('cc_addr_types').options[i] = new Option(data[2][j], j);
			i++;
		}

		/* Populate Countries */
		i = 1;
		for (j in data[3])
		{
			Element('cc_addr_countries').options[i] = new Option(data[3][j], j);

			if (j == 'BR' || j == 'br')
			{
				CC_br_index = i;
			}

			i++;
		}

		/* Populate Connection Types */
		/*
		 * Código não funcional com o expresso.
		 */
		/*i = 1;
		for (j in data[4])
		{
			Element('cc_conn_type').options[i] = new Option(data[4][j], j);
			i++;
		}*/
		
		/* Populate Relations Types */
		/*
		 * Código conflitante com a modificação de seleção de grupos durante
		 * a criação de um novo contato. Também foi verificado que este código não
		 * é funcional.
		 */
		/*
		i = 0;
		for (j in data[5])
		{
			Element('cc_rels_type').options[i] = new Option(data[5][j], j);
			i++;
		}*/
		
		/* Populate available groups */
		i = 0;
		var grupos = data[5];
		for (var grupo in grupos)
		{
			Element('id_grps_available').options[i] = new Option(grupos[grupo]['title'], grupos[grupo]['id_group']);
			i++;
		}

		CC_full_add_const = true;

	};

	Connector.newRequest('populateFullAddConst', CC_url+'get_contact_full_add_const', 'GET', handler);
}

/*
 * Função que faz a seleção do grupo.
 * Autor: Luiz Carlos Viana Melo - Prognus
 */
function selectGroup()
{
	grps_avail = Element('id_grps_available');
	grps_selec = Element('id_grps_selected');
	
	for (i = 0; i < grps_avail.length; i++)
	{
		if (grps_avail.options[i].selected) {
			isSelected = false;

			for(var j = 0;j < grps_selec.options.length; j++) {																			
				if(grps_selec.options[j].value === grps_avail.options[i].value){
					isSelected = true;
					break;	
				}
			}

			if(!isSelected){

				option = document.createElement('option');
				option.value = grps_avail.options[i].value;
				option.text = grps_avail.options[i].text;
				option.selected = false;
				grps_selec.options[grps_selec.options.length] = option;
										
			}
											
		}
	}
	
	for (j =0; j < grps_avail.options.length; j++)
		grps_avail.options[j].selected = false;
}

/*
 * Função que remove um grupo selecionado.
 * Autor: Luiz Carlos Viana Melo - Prognus
 */
function deselectGroup()
{
	grps_selec = Element('id_grps_selected');

	for(var i = 0;i < grps_selec.options.length; i++)				
		if(grps_selec.options[i].selected)
			grps_selec.options[i--] = null;
}

function populateFullEdit (id,catalog)
{
	var handler = function(responseText)
	{
		//Element('cc_debug').innerHTML = responseText;
		var data = unserialize(responseText);

		if (typeof(data) != 'object' || data['result'] != 'ok')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		resetFullAdd();

		CC_contact_full_info = data;
		Element('cc_full_add_contact_id').value = data['cc_full_add_contact_id'];
		populatePersonalData(data['personal']);
		populateContactGroups(data['groups']);
		//populateRelations(data['relations']);
	};
	Connector.newRequest('populateFullEdit', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_full_data&id=' + id + "&catalog="+catalog, 'GET', handler);
}

/*
 * Função que preenche a lista de grupos a qual o contato pertence.
 * Autor: Luiz Carlos Viana Melo - Prognus
 */
function populateContactGroups(groupsData)
{
	groups_selected = Element('id_grps_selected');
	var i = 0;
	CC_initial_selected_grps = new Array();
	for (var group in groupsData)
	{
		var id_group = groupsData[group]['id_group'];
		option = document.createElement('option');
		option.value = id_group;
		option.text = groupsData[group]['title'];
		option.selected = false;
		groups_selected.options[i++] = option;
		CC_initial_selected_grps[id_group] = new Array();
		CC_initial_selected_grps[id_group]['id_group'] = id_group;
		CC_initial_selected_grps[id_group]['title'] = groupsData[group]['title'];
		CC_initial_selected_grps[id_group]['short_name'] = groupsData[group]['short_name'];
	}
}

function populateEditGroup (id)
{
	populateEditSharedGroup(id,false);
}

function populateEditSharedGroup(id,shared) {
	var handler = function(responseText)
	{			
		var data = unserialize(responseText);

		Element('group_id').value = data['id_group'];								
		var options_contact_list = Element('span_contact_list');
		var select_contact_list = '<select id="contact_list" multiple name="contact_list[]" style="width:280px" size="10">';
		select_contact_list += data['contact_list'] + "</select>";
		options_contact_list.innerHTML = select_contact_list;
		
		if(data['id_group']) {
			Element('title').value =  data['title'];	
			if(data['contact_in_list']) {					
				for(i = 0; i < data['contact_in_list'].length; i++) {				
					option = document.createElement('option');
					option.value = data['contact_in_list'][i]['id_connection'];
					option.text = data['contact_in_list'][i]['names_ordered']+' ('+data['contact_in_list'][i]['connection_value']+')';				
					Element('contact_in_list').options[Element('contact_in_list').options.length] = option;
				}
			}		
			
			Element('title').value =  data['title'];
		}
				
		if (typeof(data) != 'object' || data['result'] != 'ok')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}
                ccAddGroup.setSelectedSourceLevel(ccTree.actualLevel);
		ccAddGroup.openEditWindow();
	};
		
	id = typeof(id) == 'undefined' ? id = 0 :  id;
	
	ccAddGroup.clear(true);			
	if(!shared)
		Connector.newRequest('populateEditGroup', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_group&id='+id, 'GET', handler);
	else
		Connector.newRequest('populateEditGroup', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_group&id='+id+'&shared_from='+shared, 'GET', handler);
}



function resetFullAdd()
{
	/* Groups */
	gprs_selected = Element('id_grps_selected');
	if(gprs_selected != null){
		for (j =0; j < gprs_selected.options.length; j++) {
			gprs_selected.options[j].selected = false;
			gprs_selected.options[j--] = null;
		}
	}
	/* Clear information container */
	CC_contact_full_info = new Array();

	/* Clear Fields */
	Element('cc_full_add_form_personal').reset();
	Element('cc_full_add_form_addrs').reset();
	if(Element('cc_contact_type').value=='advanced')
		Element('cc_full_add_form_corporative').reset();
	/* Personal Data */
	Element('cc_full_add_contact_id').value = null;
	Element('cc_pd_photo').src = 'templates/default/images/photo.png';

	/* Addresses */
	resetAddressFields();

	/* Connections */
	CC_conn_last_selected = '_NONE_';
	Element("cc_phone_default").options.selectedIndex = '-1';
	Element("cc_email_default").options.selectedIndex = '-1';
	Element("div_cc_conn_is_default").style.display = 'none';
	clearConn();
}

function postFullAdd()
{
	if (!checkFullAdd())
	{
		return false;
	}
	//Force emails to Lower Case
	txtField0 = Element("cc_conn_value_0");

	if (txtField0 != null && (txtField0.value.length > 0)) {
		txtField0.value = txtField0.value.toLowerCase();
	}
	txtField1 = Element("cc_conn_value_1");

	if (txtField1 != null && (txtField1.value.length > 0)) {
		txtField1.value = txtField1.value.toLowerCase();
	}
	/* First thing: Send Photo */
	if (Element('cc_pd_select_photo').value != '' && !is_ie)
	{
		var nodes;
		var form, frame, old_frame;

		CC_full_add_photo = false;

		old_frame = Element('cc_photo_frame');
		if (!old_frame)
		{
			frame = document.createElement('iframe');
		}
		else
		{
			frame = old_frame;
		}

		frame.id = 'cc_photo_frame';
		frame.style.visibility = 'hidden';
		frame.style.top = '0px';
		frame.style.left = '0';
		frame.style.position = 'absolute';
		document.body.appendChild(frame);

		form = frame.contentDocument.createElement('form');

		var id_contact = Element('cc_full_add_contact_id').value;
		form.id = 'cc_form_photo';
		form.method = 'POST';
		form.enctype = 'multipart/form-data';
		form.action = 'http://'+ document.domain + Element('cc_root_dir').value+'../index.php?menuaction=contactcenter.ui_data.data_manager&method=post_photo&id='+(id_contact != '' && id_contact != 'null' ? id_contact : '');

		var input_clone = Element('cc_pd_select_photo').cloneNode(false);
		form.appendChild(input_clone);

		frame.contentDocument.body.appendChild(form);
		form.submit();

		CC_full_add_photo = true;
	}
	else if (Element('cc_pd_select_photo_t').value != '' && is_ie)
	{
		CC_full_add_photo = false;

		var frame = Element('cc_photo_frame');
		var form = frame.contentWindow.document.all['cc_photo_form'];
		var id_contact = Element('cc_full_add_contact_id').value;
		form.action = 'http://'+ document.domain + Element('cc_root_dir').value+'../index.php?menuaction=contactcenter.ui_data.data_manager&method=post_photo&id='+(id_contact != '' && id_contact != 'null' ? id_contact : '');

		form.submit();

		setTimeout('Element(\'cc_photo_frame\').src = \'cc_photo_frame.html\'', 1000);
		CC_full_add_photo = true;
	}
	if (Element('cc_contact_sharing').style.display == 'none')
    	setTimeout('postFullAddInfo()', 100);
	else
		setTimeout('postFullAddInfoShared()', 100);

	updateCards();
}

function postFullAddInfo()
{
	var handler = function (responseText)
	{
		var data = unserialize(responseText);

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			return;
		}

		fullAddWin.close();
		updateCards();
	};

	Connector.newRequest('postFullAddInfo', CC_url+'post_full_add', 'POST', handler, getFullAddData());
}

function postFullAddInfoShared()
{
	var handler = function (responseText)
	{
		var data = unserialize(responseText);
		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			return;
		}

		fullAddWin.close();
		updateCards();
	};
	Connector.newRequest('postFullAddInfoShared', CC_url+'post_full_add_shared', 'POST', handler, getFullAddData());
}

function getFullAddData()
{
	var data = new Array();
	var empty = true;
	var replacer = '__##AND##__';

	data['commercialAnd'] = replacer;

	if (Element('cc_full_add_contact_id').value != '' && Element('cc_full_add_contact_id').value != 'null')
	{
		data['id_contact'] = replaceComAnd(Element('cc_full_add_contact_id').value, replacer);
		data.length++;
	}

	/* Owner do contato (Para o caso de adicao de contato compartilhado) */
	if (Element('cc_contact_sharing').style.display == 'block')
	{
		var index = Element('cc_contact_shared_types').selectedIndex;
		data['owner'] = replaceComAnd(owners[index], replacer);
		data.length++;
	}

	/* Status: Full Added */
	data['id_status'] = CC_STATUS_FULL_ADD;

	/* Personal Data */
	data['alias']         = replaceComAnd(Element('cc_pd_alias').value, replacer);
	data['id_prefix']     = replaceComAnd(Element('cc_pd_prefix').value, replacer);
	data['given_names']   = replaceComAnd(Element('cc_pd_given_names').value, replacer);
	data['family_names']  = replaceComAnd(Element('cc_pd_family_names').value, replacer);
	data['names_ordered'] = replaceComAnd(data['given_names']+" "+data['family_names'], replacer);
	data['id_suffix']     = replaceComAnd(Element('cc_pd_suffix').value, replacer);;
	data['birthdate_0']   = replaceComAnd(Element('cc_pd_birthdate_0').value, replacer);
	data['birthdate_1']   = replaceComAnd(Element('cc_pd_birthdate_1').value, replacer);
	data['birthdate_2']   = replaceComAnd(Element('cc_pd_birthdate_2').value, replacer);
//	data['sex']           = Element('cc_pd_sex').value == 1 ? 'M' : Element('cc_pd_sex').value == 2 ? 'F' : null;
	data['pgp_key']       = replaceComAnd(Element('cc_pd_gpg_finger_print').value, replacer);
	data['notes']         = replaceComAnd(Element('cc_pd_notes').value, replacer);

	data.length += 14;
	//corporative

	if (document.getElementById('cc_contact_type').value == 'advanced') {
		data['corporate_name'] = replaceComAnd(document.getElementById('cc_name_corporate').value, replacer);
		data['job_title'] = replaceComAnd(document.getElementById('cc_job_title').value, replacer);
		data['department'] = replaceComAnd(document.getElementById('cc_department').value, replacer);
		data['web_page'] = replaceComAnd(document.getElementById('cc_web_page').value, replacer);
		data.length += 18;
	}

	/* Addresses */
	saveAddressFields();
	data['addresses'] = CC_contact_full_info['addresses'];

	/* Connection */
	saveConnFields();

	if (CC_contact_full_info['connections'])
	{
		var connNumber = 0;
		for (var type in CC_contact_full_info['connections'])
		{
			if (type == 'length')
			{
				continue;
			}

			if (typeof(data['connections']) != 'object')
			{
				data['connections'] = new Array();
			}

			for (var i in CC_contact_full_info['connections'][type])
			{
				if (i == 'length')
				{
					continue;
				}

				if (typeof(data['connections']['connection'+connNumber]) != 'object')
				{
					data['connections']['connection'+connNumber] = new Array(5);
				}

				data['connections']['connection'+connNumber]['id_connection'] = CC_contact_full_info['connections'][type][i]['id'];
				data['connections']['connection'+connNumber]['id_typeof_connection'] = type;
				data['connections']['connection'+connNumber]['connection_name'] = CC_contact_full_info['connections'][type][i]['name'];
				data['connections']['connection'+connNumber]['connection_value'] = CC_contact_full_info['connections'][type][i]['value'];
				if(Element("cc_"+(type == 1 ? 'email' : 'phone')+"_default").value) {
					if(Element("cc_"+(type == 1 ? 'email' : 'phone')+"_default").value == CC_contact_full_info['connections'][type][i]['name']){
						data['connections']['connection'+connNumber]['connection_is_default']  = 'TRUE';
					}
					else
						data['connections']['connection'+connNumber]['connection_is_default']  = 'FALSE';
				}

//				data['connections']['connection'+connNumber].length = 5;

				empty = false;
				connNumber++;
				data['connections'].length++;
			}

		}

		if (!empty)
		{
			data.length++;
			empty = true;
		}
	}

	if (CC_contact_full_info['removed_conns'])
	{
		empty = false;

		if (typeof(data['connections']) != 'object')
		{
			data['connections'] = new Array();
			data.length++;
		}

		data['connections']['removed_conns'] = CC_contact_full_info['removed_conns'];
		data['connections'].length++;
	}

	data['groups'] = getAddedRemovedGroups();
	
	var serial = serialize(data);
	return 'data=' + escape(serialize(data));
}

/*
 * Função que retorna os grupos que foram anteriormente selecionados, adicionados ou removidos pelo
 * usuário. O formato retornado é um array contendo:
 * ['previous_selected'] {
 * 	[id_group] {
 * 		'id_group' 		=> o ID do grupo
 * 		'title'			=> o título do grupo
 * 	}
 * ['added'] {
 * 	[id_group] {
 * 		'id_group' 		=> o ID do grupo
 * 		'title'			=> o título do grupo
 * 	}
 * },
 * ['removed'] {
 * 	[id_group] {
 * 		'id_group' 		=> o ID do grupo
 * 		'title'			=> o título do grupo
 * 	}
 * }
 * Autor: Luiz Carlos Viana Melo - Prognus
 */
function getAddedRemovedGroups()
{
	var selected_groups = getSelectedGroups();
	var added_groups = diffContactIDArray(selected_groups, CC_initial_selected_grps);
	var removed_groups = diffContactIDArray(CC_initial_selected_grps, selected_groups);
	var groups = new Array();
	groups['previous_selected'] = CC_initial_selected_grps;
	groups['added'] = added_groups;
	groups['removed'] = removed_groups;
	return groups;
}

/*
 * Função que retorna os grupos que foram selecionados pelo usuário. O formato retornado é:
 * [id_group] {
 * 	'id_group' 		=> o ID do grupo
 * 	'title'			=> o título do grupo
 * }
 * Autor: Luiz Carlos Viana Melo - Prognus
 */
function getSelectedGroups()
{
	var gprs_selected = Element('id_grps_selected');
	var data = new Array();
	if(gprs_selected != null){
		for(i = 0; i < gprs_selected.options.length; i++)
		{
			var id_group = gprs_selected.options[i].value;
			data[id_group] = new Array();
			data[id_group]['id_group'] = id_group;
			data[id_group]['title'] = gprs_selected.options[i].text;
		}
	}
	return data;
}

/*
 * Função que retorna a diferença entre 2 arrays com ID dos contatos.
 * Autor: Luiz Carlos Viana Melo - Prognus
 */
function diffContactIDArray(array1, array2)
{
	var diff = new Array();
	for (var group in array1)
	{
		if (!array2[group])
			diff.push(array1[group]);
	}
	return diff;
}

function checkFullAdd()
{

	/* Checa se o listbox esta desativado ou é nulo, ou seja, não existe catálogos compartilhados com o user atual */

	if (!(Element('cc_contact_sharing').style.display == 'none' ))
	{
		if (Element('cc_contact_shared_types').disabled == true)
		{
			showMessage('Nenhum catálogo compartilhado existente');
			return false;
		}
		if (Element('cc_contact_shared_types').selectedIndex == 0)
		{
			showMessage('Nenhum catálogo selecionado');
			return false;
		}
	}

	/* Check Personal Data */

	if ($.trim(Element('cc_pd_given_names').value) == '')
	{
		showMessage(Element('cc_msg_err_empty_field').value + " => " + Element('cc_pd_given_names').name);
		return false;
	}

	/* Check Addresses */

	/* Check Connections */

	saveConnFields();

	var comp = /^[a-zA-Z\d(-)\.@_ -]{0,200}$/;
    haveConnections = false;
    if (CC_contact_full_info['connections']){       
        for (var type in CC_contact_full_info['connections']){
            haveConnections = true;
			var reEmail = /^[a-zA-Z0-9][_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*$/;
            for (var i in CC_contact_full_info['connections'][type]){
				if(type == 1){
					if(!reEmail.test(CC_contact_full_info['connections'][type][i]['value'])){
						showMessage('Endereço para conexão de ' + CC_contact_full_info['connections'][type][i]['name'] + ', não é válido');
						return false;
					}
				}
				else{
                if((CC_contact_full_info['connections'][type][i]['value'].length < 4) ||
                    (!comp.test(CC_contact_full_info['connections'][type][i]['value']))){
                    showMessage('Endereço para conexão de ' + CC_contact_full_info['connections'][type][i]['name'] + ', não é válido');
                    return false;
                }
            }
            }
            var _options_default = Element("cc_"+(type == 1 ? 'email' : 'phone')+"_default");
            if(_options_default.value == '-1') {
                alert("É necessário escolher um "+ (type == 1 ? 'E-mail' : 'Telefone')+" como padrão!");
                return false;
            }
        }
    }

    /* Check Relations */

    return true;

}

/********* Personal Data Functions *********/
/*
 * data[0] => cc_pd_select_photo
 * data[1] => cc_pd_alias
 * data[2] => cc_pd_given_names
 * data[3] => cc_pd_family_names
 * data[4] => cc_pd_full_name
 * data[5] => cc_pd_suffix
 * data[6] => cc_pd_birthdate
 * data[7] => cc_pd_sex SELECT
 * data[8] => cc_pd_prefix
 * data[9] => cc_pd_gpg_finger_print
 * data[10] => cc_pd_notes
 */

function populatePersonalData (data)
{
	for (i in data)
	{
		switch(i)
		{
			case 'cc_pd_suffix':
			case 'cc_pd_sex':
			case 'cc_pd_prefix':
				selectOption(i, data[i]);
				break;

			case 'cc_pd_photo':
				if (data[i])
				{
					//Codigo para exibicao da imagem do contato no IE
					//Douglas Lopes Gomes - Prognus Software Livre
					if (Element(i)[1] && Element(i)[1].src){ //Se o navegador éo IE 
						Element(i)[1].src = data[i] + '&'+ Math.random();
					} else { //Se o navegador não é o IE
					Element(i).src =  data[i] + '&'+ Math.random();
					}
				}
				break;

			default:
				Element(i).value = data[i] == undefined ? '' : unescape(data[i]);
		}
	}

	return;
}

/********* End Personal Data Functions *********/


/********* Addresses Functions *********/
function resetAddressFields()
{
	Element('cc_addr_types').selectedIndex = 0;

	Element('cc_addr_countries').selectedIndex = 0;
	Element('cc_addr_countries').disabled = true;

	Element('cc_addr_states').selectedIndex = 0;
	Element('cc_addr_states').disabled = true;
	Element('cc_addr_states_new').disabled = true;
	Element('cc_addr_states_new').readonly = true;
	Element('cc_addr_states_new').value = '';

	Element('cc_addr_cities').selectedIndex = 0;
	Element('cc_addr_cities').disabled = true;
	Element('cc_addr_cities_new').disabled = true;
	Element('cc_addr_cities_new').readonly = true;
	Element('cc_addr_cities_new').value = '';

	Element('cc_addr_id').value = '';

	resetAddrFillingFields();
}

function resetAddrFillingFields()
{
	Element('cc_addr_1').value = '';
	Element('cc_addr_2').value = '';
	Element('cc_addr_other').value = '';
	Element('cc_addr_complement').value = '';
	Element('cc_addr_postal_code').value = '';
	Element('cc_addr_po_box').value = '';
	Element('cc_addr_is_default').checked = false;
}

function disableAddrFillingFields()
{
	Element('cc_addr_1').readonly = true;
	Element('cc_addr_1').disabled = true;
	Element('cc_addr_2').readonly = true;
	Element('cc_addr_2').disabled = true;
	Element('cc_addr_other').readonly = true;
	Element('cc_addr_other').disabled = true;
	Element('cc_addr_complement').readonly = true;
	Element('cc_addr_complement').disabled = true;
	Element('cc_addr_postal_code').readonly = true;
	Element('cc_addr_postal_code').disabled = true;
	Element('cc_addr_po_box').readonly = true;
	Element('cc_addr_po_box').disabled = true;
	Element('cc_addr_is_default').readonly = true;
	Element('cc_addr_is_default').disabled = true;
}

function updateAddressFields()
{
	var type = Element('cc_addr_types');
	var oldSelected = type.value;

	saveAddressFields();

	if (oldSelected == '_NONE_')
	{
		resetAddressFields();
		return true;
	}

	CC_addr_last_selected = type.selectedIndex;

	Element('cc_addr_countries').disabled = false;

	var data = CC_contact_full_info['addresses'];
	var addrIndex  = 'address'+Element('cc_addr_types').value;

	if (typeof(data) != 'object' || typeof(data[addrIndex]) != 'object')
	{
		resetAddressFields();
		Element('cc_addr_countries').disabled = false;
		Element('cc_addr_countries').selectedIndex = CC_br_index;
		type.value = oldSelected;
		updateAddrStates();
		return true;
	}

	var addrTypeID = Element('cc_addr_types').value;

	data = CC_contact_full_info['addresses'][addrIndex];

	Element('cc_addr_id').value          			 = data['id_address']		? data['id_address']			: '';
	Element('cc_addr_1').value            			= data['address1']			? data['address1']				: '';
	Element('cc_addr_2').value            			= data['address2']			? data['address2']				: '';
	Element('cc_addr_complement').value   = data['complement']		? data['complement']		: '';
	Element('cc_addr_other').value				= data['address_other']	? data['address_other']	: '';
	Element('cc_addr_postal_code').value	= data['postal_code']		? data['postal_code']		: '';
	Element('cc_addr_po_box').value       		= data['po_box'] 				? data['po_box']             	: '';
	Element('cc_addr_is_default').checked 	= data['address_is_default'] == '1' ? true: false;

	Element('cc_addr_countries').value    = data['id_country'];
	updateAddrStates();
}

function updateAddrStates()
{
	var states = Element('cc_addr_states');
	if (Element('cc_addr_countries').value == '_NONE_')
	{
		states.disabled = true;
		states.selectedIndex = 0;
		clearSelectBox(states, 4);
		updateAddrCities();
		return;
	}

	updateAddrFillingFields();
	populateStates();
}

function populateStates()
{
	var states = Element('cc_addr_states');
	var cities = Element('cc_addr_cities');
	var handler = function (responseText)
	{
		var data = unserialize(responseText);

		clearSelectBox(states, 1);

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);

			return;
		}

		if (data['status'] == 'empty')
		{
			states.disabled = true;
			cities.disabled = true;
			states.selectedIndex = 0;
			cities.selectedIndex = 0;
			return;
		}
		else if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			states.disabled = true;
			states.selectedIndex = 0;
			updateAddrCities();
			return;
		}
		states.disabled = false;
		var i = 1;
		/*
		for (var j in data['data'])
		{
			states.options[i] = new Option(data['data'][j], j);
			if(i == 1) data['data'] = data['data'].sort();
			i++;
		}*/
		jQuery.each(data['data'],function(index,value){
			if (value != undefined){
				states.options[i] = new Option(value, index);
				if(i == 1) data['data'] = data['data'].sort();
				i++;
			}			
		});
		states.disabled = false;
		states.selectedIndex = 0;

		data = CC_contact_full_info['addresses'];
		var addrIndex = 'address'+Element('cc_addr_types').value;
		if (data && data[addrIndex])
		{
			states.value = data[addrIndex]['id_state'];
			if (states.value == '_NEW_')
			{
				if (CC_contact_full_info['addresses']['new_states'][addrIndex])
				{
					Element('cc_addr_states_new').value = CC_contact_full_info['addresses']['new_states'][addrIndex];
				}
				updateAddrNewStateOnMouseOut();
			}
			updateAddrCities();
		}
	};

	Connector.newRequest('populateStates', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_states&country='+Element('cc_addr_countries').value, 'GET', handler);
}

function updateAddrCities()
{
	var states = Element('cc_addr_states');
	var cities = Element('cc_addr_cities');
	var newState = Element('cc_addr_states_new');
	var requestStr;

	switch (states.value)
	{
		case '_NONE_':
			newState.readonly = true;
			newState.disabled = true;
			newState.value = '';

			cities.disabled = true;
			cities.selectedIndex = 0;
			updateAddrFillingFields();
			return;

		case '_NEW_':

			newState.readonly = false;
			newState.disabled = false;
			updateAddrNewStateOnMouseOut();

			cities.disabled = false;
			clearSelectBox(cities, 3);
			cities.selectedIndex = 1;
			updateAddrFillingFields();
			return;

		case '_SEP_': return;

		case '_NOSTATE_':
			clearSelectBox(cities, 3);

			cities.disabled = false;
			cities.selectedIndex = 0;

			requestStr = 'country='+Element('cc_addr_countries').value;
			break;

		default:
			requestStr = 'country='+Element('cc_addr_countries').value+'&state='+states.value;
	}

	newState.readonly = true;
	newState.disabled = true;
	newState.value = '';

	populateCities(requestStr);
}

function populateCities(requestStr)
{
	var cities = Element('cc_addr_cities');

	var handler = function (responseText)
	{
		var data = unserialize(responseText);

		clearSelectBox(cities, 1);

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);

			return;
		}

		if (data['status'] == 'empty')
		{
			cities.disabled = true;
			cities.selectedIndex = 0;
			return;
		}
		else if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			cities.disabled = true;
			cities.selectedIndex = 0;
			updateAddrFillingFields();
			return;
		}
		cities.disabled = false;
		var i = 1;
		/*
		for (var j in data['data'])
		{
			cities.options[i] = new Option(data['data'][j], j);
			i++;
		}*/
		jQuery.each(data['data'],function(index,value){
			if (value != undefined){
				cities.options[i] = new Option(value,index);
				i++;
			}
		});
		cities.disabled = false;
		cities.selectedIndex = 0;

		data = CC_contact_full_info['addresses'];
		var addrIndex = 'address'+Element('cc_addr_types').value;
		if (data && data[addrIndex])
		{
			cities.value = data[addrIndex]['id_city'];

			if (cities.value == '_NEW_')
			{
				if (CC_contact_full_info['addresses']['new_cities'][addrIndex])
				{
					Element('cc_addr_cities_new').value = CC_contact_full_info['addresses']['new_cities'][addrIndex];
				}
				updateAddrNewCityOnMouseOut();
			}
		}
	};

	Connector.newRequest('populateCities', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_cities&'+requestStr, 'GET', handler);
}

function updateAddrNewStateOnMouseOver ()
{
	if (Element('cc_addr_states_new').value == Element('cc_msg_type_state').value && Element('cc_addr_states').selectedIndex == 1)
	{
		Element('cc_addr_states_new').value = '';
	}
}

function updateAddrNewStateOnMouseOut ()
{
	if (Element('cc_addr_states_new').value.length == 0 && Element('cc_addr_states').selectedIndex == 1)
	{
		Element('cc_addr_states_new').value = Element('cc_msg_type_state').value;
	}
}

function updateAddrFillingFields()
{
	var countries = Element('cc_addr_countries');
	var cities = Element('cc_addr_cities');
	var newCity = Element('cc_addr_cities_new');

	if (countries.value == '_NONE_')
	{
		newCity.readonly = true;
		newCity.disabled = true;
		newCity.value = '';
		disableAddrFillingFields();
		return;
	}

	Element('cc_addr_1').readonly = false;
	Element('cc_addr_1').disabled = false;

	Element('cc_addr_2').readonly = false;
	Element('cc_addr_2').disabled = false;

	Element('cc_addr_other').readonly = false;
	Element('cc_addr_other').disabled = false;

	Element('cc_addr_complement').readonly = false;
	Element('cc_addr_complement').disabled = false;

	Element('cc_addr_postal_code').readonly = false;
	Element('cc_addr_postal_code').disabled = false;

	Element('cc_addr_po_box').readonly = false;
	Element('cc_addr_po_box').disabled = false;

	Element('cc_addr_is_default').readonly = false;
	Element('cc_addr_is_default').disabled = false;

	switch (cities.value)
	{
		case '_NONE_':
			newCity.readonly = true;
			newCity.disabled = true;
			newCity.value = '';

			//resetAddrFillingFields();

			return;

		case '_NEW_':

			newCity.readonly = false;
			newCity.disabled = false;
			updateAddrNewCityOnMouseOut();

			break;

		case '_SEP_': return;

		default:
			newCity.readonly = true;
			newCity.disabled = true;
			newCity.value = '';
	}
}

function updateAddrNewCityOnMouseOver ()
{
	if (Element('cc_addr_cities_new').value == Element('cc_msg_type_city').value && Element('cc_addr_cities').selectedIndex == 1)
	{
		Element('cc_addr_cities_new').value = '';
	}
}

function updateAddrNewCityOnMouseOut ()
{
	if (Element('cc_addr_cities_new').value.length == 0 && Element('cc_addr_cities').selectedIndex == 1)
	{
		Element('cc_addr_cities_new').value = Element('cc_msg_type_city').value;
	}
}

function saveAddressFields ()
{
	var lastIndex = CC_addr_last_selected;

	if (lastIndex == 0)
	{
		return true;
	}

	var addrFields = new Array('cc_addr_1',
	                           'cc_addr_2',
							   'cc_addr_complement',
							   'cc_addr_other',
							   'cc_addr_postal_code',
							   'cc_addr_po_box',
							   'cc_addr_countries',
							   'cc_addr_states',
							   'cc_addr_cities');

	var empty = true;

	for (var i = 0; i < 8; i++)
	{
		var field = Element(addrFields[i]);
		if (field.value && field.value != '_NONE_' && field.value != '_SEP_')
		{
			empty = false;
		}
	}

	if (empty)
	{
		return true;
	}

	if (!CC_contact_full_info['addresses'])
	{
		CC_contact_full_info['addresses'] = new Array();
	}

	var addrInfo = CC_contact_full_info['addresses']['address'+Element('cc_addr_types').options[lastIndex].value];

	if (!addrInfo)
	{
		addrInfo = new Array();
	}

	addrInfo['id_address'] = Element('cc_addr_id').value;

	switch(Element('cc_addr_countries').value)
	{
		case '_SEP_':
		case '_NONE_':
			addrInfo['id_country'] = false;
			break;

		default:
			addrInfo['id_country'] = Element('cc_addr_countries').value;

	}

	switch(Element('cc_addr_states').value)
	{
		case '_SEP_':
		case '_NONE_':
		case '_NEW_':
		case '_NOSTATE_':
			addrInfo['id_state'] = false;
			break;

		default:
			addrInfo['id_state'] = Element('cc_addr_states').value;

	}

	switch(Element('cc_addr_cities').value)
	{
		case '_SEP_':
		case '_NONE_':
		case '_NEW_':
			addrInfo['id_city'] = false;
			break;

		default:
			addrInfo['id_city'] = Element('cc_addr_cities').value;

	}

	addrInfo['id_typeof_address']  = Element('cc_addr_types').options[lastIndex].value;
	addrInfo['address1']           = Element('cc_addr_1').value ? Element('cc_addr_1').value : false;
	addrInfo['address2']           = Element('cc_addr_2').value ? Element('cc_addr_2').value : false;
	addrInfo['complement']         = Element('cc_addr_complement').value ? Element('cc_addr_complement').value : false;
	addrInfo['address_other']      = Element('cc_addr_other').value ? Element('cc_addr_other').value : false;
	addrInfo['postal_code']        = Element('cc_addr_postal_code').value ? Element('cc_addr_postal_code').value : false;
	addrInfo['po_box']             = Element('cc_addr_po_box').value ? Element('cc_addr_po_box').value : false;
	addrInfo['address_is_default'] = Element('cc_addr_is_default').checked ? '1' : '0';

	CC_contact_full_info['addresses']['address'+Element('cc_addr_types').options[lastIndex].value] = addrInfo;

	if (Element('cc_addr_cities').value == '_NEW_' &&
	    Element('cc_msg_type_city').value !=  Element('cc_addr_cities_new').value &&
		Element('cc_addr_cities_new').value != '')
	{
		var addrRootInfo = CC_contact_full_info['addresses']['new_cities'];

		if (!addrRootInfo)
		{
			addrRootInfo = new Array();
		}

		var i = addrRootInfo.length;
		addrRootInfo[addrInfo['id_typeof_address']] = new Array();
		addrRootInfo[addrInfo['id_typeof_address']]['id_country'] = Element('cc_addr_countries').value;
		addrRootInfo[addrInfo['id_typeof_address']]['id_state']   = Element('cc_addr_states').value.charAt(0) != '_' ? Element('cc_addr_states').value : null;
		addrRootInfo[addrInfo['id_typeof_address']]['city_name']  = Element('cc_addr_cities_new').value;
		CC_contact_full_info['addresses']['new_cities'] = addrRootInfo;
	}

	if (Element('cc_addr_states').value == '_NEW_' &&
	    Element('cc_msg_type_state').value !=  Element('cc_addr_states_new').value &&
		Element('cc_addr_states_new').value != '')
	{
		var addrRootInfo = CC_contact_full_info['addresses']['new_states'];

		if (!addrRootInfo)
		{
			addrRootInfo = new Array();
		}

		var i = addrRootInfo.length;
		addrRootInfo[addrInfo['id_typeof_address']] = new Array();
		addrRootInfo[addrInfo['id_typeof_address']]['id_country'] = Element('cc_addr_countries').value;
		addrRootInfo[addrInfo['id_typeof_address']]['state_name'] = Element('cc_addr_states_new').value;
		CC_contact_full_info['addresses']['new_states'] = addrRootInfo;
	}

	return true;
}


/********* End Addresses Functions *********/



/********* Begin Connections Functions ************/
function connGetHTMLLine ()
{
	var _label = (CC_contact_full_info['connections']
		&& typeof(CC_contact_full_info['connections'][CC_conn_last_selected])!= 'undefined'
		&& typeof(CC_contact_full_info['connections'][CC_conn_last_selected][CC_conn_count]) != 'undefined'
		? CC_contact_full_info['connections'][CC_conn_last_selected][CC_conn_count]['name']
		: Element("cc_conn_type_sel").value);

	var cc_conn_default = Element("cc_phone_default").style.display == '' ? Element("cc_phone_default") : Element("cc_email_default");
	cc_conn_default.disabled = false;
	var idx_conn = 0;
	for(idx_conn; idx_conn < cc_conn_default.options.length; idx_conn++)
		if(cc_conn_default.options[idx_conn].value == _label)
			break;

	if(idx_conn == cc_conn_default.options.length)
		cc_conn_default.options[idx_conn] = new Option (_label,_label, false,false);

	if (!document.all)
	{
		if (Element("cc_conn_type_1").checked)
		{
			return '<td style="position: absolute; left: 0; top: 0; z-index: -1; visibility: hidden"><input id="cc_conn_id_' + CC_conn_count + '" type="hidden" value="_NEW_"><input id="cc_conn_is_default_' + CC_conn_count + '" type="hidden" value="false"></td>'+
			//'<td style="width: 30px;" align="right"><input name="cc_conn_is_default" id="cc_conn_is_default_'+ CC_conn_count +'" type="radio"></td>'+
			'<td style="width: 10px;" align="right"><input id="cc_conn_name_'+CC_conn_count+'" type="hidden"><td style="width: 100px; padding-left: 55px;" align="left"><span style="width: 150px;" id="cc_conn_label_'+CC_conn_count+'">'+_label+':'+'</span></td>' +
			'<td align="left"><input id="cc_conn_value_'+ CC_conn_count +'" style="width: 150px; text-transform:lowercase;" maxlength="100" type="text">&nbsp;' +
			'<img align="top" alt="X" title="X" src="templates/default/images/x.png" style="width:18px; height:18px; cursor:pointer;" onclick="javascript:removeConnField(\'cc_conn_tr_' + CC_conn_count + '\')"></td>';
		}
		else if (Element("cc_conn_type_2").checked)
		{
			return '<td style="position: absolute; left: 0; top: 0; z-index: -1; visibility: hidden"><input id="cc_conn_id_' + CC_conn_count + '" type="hidden" value="_NEW_"><input id="cc_conn_is_default_' + CC_conn_count + '" type="hidden" value="false"></td>'+
			//'<td style="width: 30px;" align="right"><input name="cc_conn_is_default" id="cc_conn_is_default_'+ CC_conn_count +'" type="radio"></td>'+
			'<td style="width: 10px;" align="right"><input id="cc_conn_name_'+CC_conn_count+'" type="hidden"><td style="width: 100px; padding-left: 55px;" align="left"><span style="width: 150px;" id="cc_conn_label_'+CC_conn_count+'">'+_label+':'+'</span></td>' +
			'<td align="left"><input id="cc_conn_value_'+ CC_conn_count +'" style="width: 150px; text-transform:lowercase;" maxlength="30" type="text" onkeyup="formatPhone(this);">&nbsp;' +
			'<img align="top" alt="X" title="X" src="templates/default/images/x.png" style="width:18px; height:18px; cursor:pointer;" onclick="javascript:removeConnField(\'cc_conn_tr_' + CC_conn_count + '\')"></td>';
		}
	}
	else
	{
		var tds = new Array();
		var inputs = new Array();
		var img = document.createElement('img');

		for (var i = 0; i < 4; i++)
		{
			tds[i] = document.createElement('td');
		}

		tds[0].style.position = 'absolute';
		tds[0].style.visibility = 'hidden';
		tds[0].style.zIndex = '-1';

		var remove_id = 'cc_conn_tr_'+CC_conn_count;
		img.alt = 'X';
		img.src = 'templates/default/images/x.png';
		img.style.width = '18px';
		img.style.height = '18px';
		img.style.cursor = 'pointer';
		img.align = 'top';
		img.onclick = function(e){ removeConnField(remove_id);};

		for (var i = 0; i < 3; i++)
		{
			inputs[i] = document.createElement('input');
		}

		inputs[0].id = 'cc_conn_id_'+CC_conn_count;
		inputs[0].type = 'hidden';
		inputs[0].value = '_NEW_';

		inputs[1].id = 'cc_conn_name_'+CC_conn_count;
		inputs[1].type = 'hidden';

		inputs[2].id = 'cc_conn_value_'+CC_conn_count;
		inputs[2].type = 'text';
		inputs[2].style.width = '150px';

		var _span = document.createElement("SPAN");
		_span.style.width = "100px";
		_span.id = "cc_conn_label_"+CC_conn_count;
		_span.innerHTML = _label + ':';
		tds[0].appendChild(inputs[0]);
		tds[1].width = '40px';
		tds[1].appendChild(inputs[1]);
		tds[1].align = 'left';
		tds[1].style.padding = "0px 0px 0px 75px";
		tds[1].appendChild(_span);
		tds[2].appendChild(inputs[2]);
		tds[2].align = 'left';
		tds[2].innerHTML +="&nbsp;";
		tds[2].appendChild(img);

		return tds;
	}
}

function connAddNewLine ()
{

	var _emptyLine = (!CC_contact_full_info['connections']
		|| typeof(CC_contact_full_info['connections'][CC_conn_last_selected]) == 'undefined'
		|| typeof(CC_contact_full_info['connections'][CC_conn_last_selected][CC_conn_count]) == 'undefined');

	if(_emptyLine) {

		if(Element("cc_conn_type_sel").value == '-1'){
				return false;
		}

		for(k = 0; k < CC_conn_count; k++) {
			if(Element("cc_conn_name_"+k) && Element("cc_conn_name_"+k).value != "" && Element("cc_conn_name_"+k).value == Element("cc_conn_type_sel").value) {
				alert('Você já possui uma entrada para o tipo "'+Element("cc_conn_type_sel").value+'"!');
				Element("cc_conn_type_sel").options.selectedIndex = 0;
				return false;
			}
		}
	}
	if (!document.all)
	{
		var obj = addHTMLCode('cc_conn', 'cc_conn_tr_'+CC_conn_count, connGetHTMLLine(),'tr');
	}
	else
	{
		var tds = connGetHTMLLine();
		var tr = document.createElement('tr');
		var tbody = Element('cc_conn');

		tr.id = 'cc_conn_tr_'+CC_conn_count;
		tbody.appendChild(tr);

		for (var i = 0; i < 4; i++)
		{
			tr.appendChild(tds[i]);
		}
	}
	Element("cc_conn_name_"+CC_conn_count).value = Element("cc_conn_type_sel").value;
	Element("cc_conn_type_sel").options.selectedIndex = 0;
	CC_conn_count++;

	return CC_conn_count;
}

function connRemoveLine(id)
{
	var p = Element(id).parentNode;
	var cc_conn_default = Element("cc_phone_default").style.display == '' ? Element("cc_phone_default") : Element("cc_email_default");
	var _label = Element("cc_conn_label_"+(id.substring(11,13))).innerHTML;
	for(var i = 0;i < cc_conn_default.options.length; i++) {
		if(cc_conn_default.options[i].value == _label) {
			cc_conn_default.options[i] = null;
			break;
		}
	}
	if(cc_conn_default.options.length == 1)
		cc_conn_default.disabled = true;

	removeHTMLCode(id);

	return;
	connRefreshClass(p.childNodes);
}

function connRefreshClass(Nodes)
{
	for (var i = 2; i < Nodes.length; i++)
	{
		Nodes.item(i).className = i % 2 ? 'row_on' : 'row_off';
	}
}

function clearConn()
{
	var connParent = Element('cc_conn').childNodes;
	var i;

	for (i = connParent.length - 1; i >= 0; i--)
	{
		if (connParent[i].id)
		{
			connRemoveLine(connParent[i].id);
		}
	}

	CC_conn_count = 0;
}

function removeConnField(id)
{
	var count = id.substring(id.lastIndexOf('_')+1);
	if (Element('cc_conn_id_'+count).value != '_NEW_')
	{
		if (typeof(CC_contact_full_info['removed_conns']) != 'object')
		{
			CC_contact_full_info['removed_conns'] = new Array();
		}

		CC_contact_full_info['removed_conns'][CC_contact_full_info['removed_conns'].length] = Element('cc_conn_id_'+count).value;
	}

	connRemoveLine(id);
}

function emailTolower(obj){
	document.getElementById(obj).value = $.trim(document.getElementById(obj).value.toLowerCase());

}

function updateConnFields()
{

	var connID;
	var i;
	var cc_conn_type_sel = Element("cc_conn_type_sel");
	var cc_phone_default = Element("cc_phone_default");
	var cc_email_default = Element("cc_email_default");
	var div_cc_conn_is_default = Element("div_cc_conn_is_default");
	var cc_conn_is_default = '';
	var selected_index = '';

	cc_conn_type_sel.disabled = false;
	div_cc_conn_is_default.style.display = "";

	for(var i = 0;i < cc_conn_type_sel.options.length; i++)
		cc_conn_type_sel.options[i--] = null;

	if(Element('cc_conn_type_1').checked) {
	    var lang_new_email = Element('cc_msg_new_email').value;
	    var lang_main = Element('cc_msg_main').value;
	    var lang_alternative = Element('cc_msg_alternative').value;
	    cc_conn_type_sel[0] = new Option(lang_new_email,'-1');
	    cc_conn_type_sel[1] = new Option(lang_main,lang_main);
	    cc_conn_type_sel[2] = new Option(lang_alternative,lang_alternative);
		connID = 1;
		selected_index = cc_email_default.options.selectedIndex;
		for(var i = 0;i < cc_email_default.options.length; i++) {
			cc_email_default.options[i--] = null;
		}

		var lang_select_email = Element('cc_msg_select_email').value;
		cc_email_default.options[0] = new Option(lang_select_email,'-1');
		cc_phone_default.style.display = 'none';
		cc_email_default.style.display = '';
		cc_conn_is_default = cc_email_default;
	}
	else if(Element('cc_conn_type_2').checked) {
	    var lang_new_telephone = Element('cc_msg_new_phone').value;
	    var lang_home = Element('cc_msg_home').value;
	    var lang_cellphone = Element('cc_msg_cellphone').value;
	    var lang_work = Element('cc_msg_work').value;
	    var lang_fax = Element('cc_msg_fax').value;
	    var lang_pager = Element('cc_msg_pager').value;
		var lang_corporative_cellphone = Element('cc_msg_corporative_cellphone').value;
		var lang_corporative_fax = Element('cc_msg_corporative_fax').value;
		var lang_corporative_pager = Element('cc_msg_corporative_pager').value;

	    cc_conn_type_sel[0] = new Option(lang_new_telephone,'-1');
	    cc_conn_type_sel[1] = new Option(lang_home,lang_home);
	    cc_conn_type_sel[2] = new Option(lang_cellphone,lang_cellphone);
	    cc_conn_type_sel[3] = new Option(lang_work,lang_work);
	    cc_conn_type_sel[4] = new Option(lang_fax,lang_fax);
	    if (document.getElementById('cc_contact_type').value == 'advanced') {
			cc_conn_type_sel[5] = new Option(lang_pager, lang_pager);
			cc_conn_type_sel[6] = new Option(lang_corporative_cellphone, lang_corporative_cellphone);
			cc_conn_type_sel[7] = new Option(lang_corporative_fax, lang_corporative_fax);
			cc_conn_type_sel[8] = new Option(lang_corporative_pager, lang_corporative_pager);
		}

		connID = 2;
		selected_index = cc_phone_default.options.selectedIndex;
		for(var i = 0;i < cc_phone_default.options.length; i++) {
			cc_phone_default.options[i--] = null;
		}

		var lang_choose_phone = Element('cc_msg_choose_phone').value;
		cc_phone_default.options[0] = new Option(lang_choose_phone,'-1');
		cc_email_default.style.display = 'none';
		cc_phone_default.style.display = '';
		cc_conn_is_default = cc_phone_default;
	}

	Element("cc_conn_type_sel").options.selectedIndex = 0;
	/* First save the data */
	saveConnFields();

	CC_conn_last_selected = connID;

	clearConn();

	if (connID == '_NONE_')
	{	cc_conn_is_default.disabled = true;
		return;
	}

	/* If no data already available, return */
	if (!CC_contact_full_info['connections'])
	{
		cc_conn_is_default.disabled = true;
		return;
	}
	cc_conn_is_default.disabled = (!CC_contact_full_info['connections'][connID] || CC_contact_full_info['connections'][connID].length == 0);
	/* Put the information that's already available */
	for (i in CC_contact_full_info['connections'][connID])
	{
		var num = connAddNewLine();
		Element('cc_conn_id_'+i).value = CC_contact_full_info['connections'][connID][i]['id'];
		Element('cc_conn_name_'+i).value = CC_contact_full_info['connections'][connID][i]['name'];
		Element('cc_conn_value_'+i).value = CC_contact_full_info['connections'][connID][i]['value'];

		if(!selected_index || (selected_index == '-1' && CC_contact_full_info['connections'][connID][i]['is_default'])){
			for(var j = 0;j < cc_conn_is_default.options.length; j++){
				if(cc_conn_is_default.options[j].value == CC_contact_full_info['connections'][connID][i]['name']) {
					selected_index = j;
					break;
				}
			}
		}
	}
	if(cc_conn_is_default.options.length > selected_index)
		cc_conn_is_default.options.selectedIndex = (selected_index == "-1" ? 0 : selected_index);
}

function saveConnFields()
{
	if (CC_conn_last_selected != 0 && CC_conn_last_selected != '_NONE_')
	{
		var nodes = Element('cc_conn').childNodes;
		var k = 0;

		if (typeof(CC_contact_full_info['connections']) != 'object' || CC_contact_full_info['connections'] == null)
		{
			CC_contact_full_info['connections'] = new Array();
			CC_contact_full_info['connections'][CC_conn_last_selected] = new Array();
		}
		else if (typeof(CC_contact_full_info['connections'][CC_conn_last_selected]) != 'object')
		{
			CC_contact_full_info['connections'][CC_conn_last_selected] = new Array();
		}
		else
		{
			delete CC_contact_full_info['connections'][CC_conn_last_selected];
			CC_contact_full_info['connections'][CC_conn_last_selected] = new Array();
		}

		for (var i = 0; i < nodes.length; i++)
		{
			if (nodes[i].id)
			{
				var subNodes = nodes[i].childNodes;
				var found = false;

				for (var j = 0; j < subNodes.length; j++)
				{
					if (subNodes[j].childNodes.length > 0 &&
					    subNodes[j].childNodes[0].id)
					{
						/* Check for the Connection Info array */
						if (typeof(CC_contact_full_info['connections'][CC_conn_last_selected][k]) != 'object')
						{
							CC_contact_full_info['connections'][CC_conn_last_selected][k] = new Array();
						}

					    if (subNodes[j].childNodes[0].id.indexOf('cc_conn_name') != -1)
						{
							if (subNodes[j].childNodes[0].value)
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['name'] = subNodes[j].childNodes[0].value;
							}
							else
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['name'] = '';
							}
						}
						else if (subNodes[j].childNodes[0].id.indexOf('cc_conn_value') != -1)
						{
							if (subNodes[j].childNodes[0].value)
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['value'] = subNodes[j].childNodes[0].value;
							}
							else
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['value'] = '';
							}
						}
						else if (subNodes[j].childNodes[0].id.indexOf('cc_conn_id') != -1)
						{
							CC_contact_full_info['connections'][CC_conn_last_selected][k]['id'] = subNodes[j].childNodes[0].value;
						}

						found = true;
					}
				}

				if (found)
				{
					k++;
				}
			}
		}

		if (CC_contact_full_info['connections'].length == 0)
		{
			delete CC_contact_full_info['connections'];
		}

		if (CC_contact_full_info['connections'][CC_conn_last_selected].length == 0)
		{
			delete CC_contact_full_info['connections'][CC_conn_last_selected];
		}

	}

	return;
}

/***********************************************\
*               VIEW CARDS FUNCTIONS            *
\***********************************************/
function removeAllEntries()
{
	var handler = function (responseText)
	{
		var data = unserialize(responseText);
		if (typeof(data) != 'object') {
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}
		if (data['status'] != 'ok')	{
			showMessage(data['msg']);
			return;
		}
		setTimeout('updateCards()',80);
	};
	var number = randomString().toLowerCase();
	var result = '';

	if(!is_ie)
		result = prompt("Essa operação removerá TODOS os seus \ncontatos pessoais,  e  NÃO  PODERÁ  ser \ndesfeita. Digite o código abaixo:\n\tCódigo de confirmação: "+number);
	else
		result = prompt("Essa operação removerá TODOS os seus contatos pessoais,  e  NÃO  PODERÁ  ser desfeita. Digite o seguinte código de confirmação: "+number,"");

	if(result) {
		if(result.toLowerCase() == number)
			Connector.newRequest('removeAllEntries', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=remove_all_entries', 'GET', handler);
		else
			alert('Código Incorreto');
	}
}

function removeEntry(id, type)
{
	var question = showMessage(type == 'groups' ? Element('cc_msg_group_remove_confirm').value: Element('cc_msg_card_remove_confirm').value, 'confirm');

	if (!question)
	{
		return;
	}

	var handler = function (responseText)
	{
		var data = unserialize(responseText);

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			return;
		}

		setTimeout('updateCards()',80);;
	};

	typeArg = (type == 'groups' ? 'group' : 'entry');

	Connector.newRequest('removeEntry', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=remove_'+typeArg+'&remove=' + id, 'GET', handler);
}

function updateCards()
{
	setHeightSpace();
	setMaxCards(getMaxCards());
	showCards(getActualLetter(), getActualPage());
}


window.onresize = function ()
{
	updateCards();
}


function setHeightSpace ()
{
	/*
	var w_height = 0;
	var w_extra = 200;

	if (document.body.clientHeight)
	{
		w_height = parseInt(document.body.clientHeight);
	}
	else
	{
		w_height = 500;
	}
	if (w_height < 500)
	{
		w_height = 500;
	}
	Element('cc_card_space').style.height = (w_height - w_extra) + 'px';
	*/
}

function selectLetter (letter_id)
{
	for (var i = 0; i < 28; i++)
	{
		if ( i == letter_id )
		{
			Element('cc_letter_' + i).className = 'letter_box_active';
		}
		else
		{
			Element('cc_letter_' + i).className = 'letter_box';
		}
	}
}

function clearLetterSelection()
{
	for (var i = 0; i < 28; i++)
	{
		Element('cc_letter_' + i).className = 'letter_box';
	}
}

function getActualPage ()
{
	return CC_actual_page;
}

function getActualLetter ()
{
	return CC_actual_letter;
}

function getFirstPage ()
{
	return 1;
}

function getPreviousPage ()
{
	if ( CC_actual_page > 1 )
	{
		return CC_actual_page - 1;
	}
	else
	{
		return 1;
	}
}

function getNextPage ()
{
	if ( CC_actual_page < CC_npages )
	{
		return CC_actual_page + 1;
	}
	else
	{
		return CC_npages;
	}
}

function getLastPage ()
{
	return CC_npages;
}

function setPages (npages, actual_page, showing_page)
{
	var html_pages = '';
	var n_lines = 0;
	var page_count = 0;

	if (CC_npages == 0)
	{
		html_pages = '';
	}
	else
	{
		var page = 1;
		if (showing_page > 10 || (!showing_page && actual_page > 10))
		{
			var final_page = showing_page? showing_page-11 : actual_page-11;
			if (final_page < 1)
			{
				final_page = 1;
			}

			html_pages += '<a href="javascript:setPages('+npages+', '+ actual_page +', '+ final_page +')">...</a> ';

			page = showing_page ? showing_page : actual_page;
		}

		for (; page <= npages; page++)
		{
			if (page_count > 10)
			{
				html_pages += '<a href="javascript:setPages('+npages+', '+ actual_page +', '+ page +');">...</a>';
				break;
			}
			if ( page == actual_page )
			{
				html_pages += '<b>'+page+'</b>';
			}
			else
			{
				html_pages += '<a href="javascript:showCards(\'' + CC_actual_letter + '\',' + page + ')">' + page + '</a>';
			}
			html_pages += '&nbsp;';
			page_count++;
		}
	}

	if (actual_page <= 1)
	{
		Element('cc_panel_arrow_first').onclick = '';
		Element('cc_panel_arrow_previous').onclick = '';
		Element('cc_panel_arrow_first').style.cursor = 'auto';
		Element('cc_panel_arrow_previous').style.cursor = 'auto';
	}
	else
	{
		Element('cc_panel_arrow_first').onclick = function (event) { showCards(getActualLetter(), getFirstPage()); };
		Element('cc_panel_arrow_previous').onclick = function (event) { showCards(getActualLetter(), getPreviousPage()); };
		if (is_mozilla)
		{
			Element('cc_panel_arrow_first').style.cursor = 'pointer';
			Element('cc_panel_arrow_previous').style.cursor = 'pointer';
		}
		Element('cc_panel_arrow_first').style.cursor = 'hand';
		Element('cc_panel_arrow_previous').style.cursor = 'hand';
	}

	if (actual_page == CC_npages)
	{
		Element('cc_panel_arrow_next').onclick = '';
		Element('cc_panel_arrow_last').onclick = '';
		Element('cc_panel_arrow_next').style.cursor = 'auto';
		Element('cc_panel_arrow_last').style.cursor = 'auto';
	}
	else
	{
		Element('cc_panel_arrow_next').onclick = function (event) { showCards(getActualLetter(), getNextPage()); };
		Element('cc_panel_arrow_last').onclick = function (event) { showCards(getActualLetter(), getLastPage()); };
		if (is_mozilla)
		{
			Element('cc_panel_arrow_next').style.cursor = 'pointer';
			Element('cc_panel_arrow_last').style.cursor = 'pointer';
		}
		Element('cc_panel_arrow_next').style.cursor = 'hand';
		Element('cc_panel_arrow_last').style.cursor = 'hand';
	}

	Element('cc_panel_pages').innerHTML = html_pages;
}

function populateCards(data, type)
{
	if (data[3].length >= 100 )
	{
		alert("Critério de pesquisa muito abrangente, achados " + data[3].length + " resultados");
		for (i = 0; i < (Math.sqrt(data[3].length)-1); i++)
			for (j = 0; j < 3; j++)
				document.getElementById("cc_card:"+j+":"+i).innerHTML = '';
			return false;
	}

	if(type == 'groups' || type =='shared_groups')
		return populateGroupsInCards(data,type);

	var pos = 0;
	var ncards = data[3].length;

	if (typeof(data[3]) == 'object' && ncards > 0)
	{
		for (var i = 0; i < CC_max_cards[1]; i++)
		{
			for (var j = 0; j < CC_max_cards[0]; j++)
			{
				id = 'cc_card:'+j+':'+i;

				for (var k = 0; k < data[2].length; k++)
				{
					if(!(ccTree.catalog_perms & 2))
					{
						switch(data[2][k])
						{
							case 'cc_mail' :

								if(data[3][pos][k] === 'none')
									data[3][pos][k] = not_informed_text;
								break;
							case 'cc_phone' :

								if(data[3][pos][k] === 'none')
									data[3][pos][k] = not_informed_text;
								break;
						}

					}


					/*if(data[2][k] ==  'cc_mail' && data[3][pos][k] == 'none' && !(ccTree.catalog_perms & 2) ) {
						Element(id).style.display = 'none';
						continue;
					}*/

					if(data[3][pos][k] != 'none')
					{
						data[3][pos][k] = unescape(data[3][pos][k]);
						switch (data[2][k])
						{
							case 'cc_name':
								if (data[3][pos][k].length > 50)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 50);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = data[3][pos][k];
								}
								if(data[3][pos][12])
									Element(id+':'+data[2][k]).innerHTML += "<br><span style='margin-left:30px'><font size='-2' color='#808080'><i>"+data[3][pos][12]+"</i></font></span>";	
								break;

							case 'cc_mail':
								if (data[3][pos][k].length > (CC_visual == 'table'  ? 50 : 20))
								{
									Element(id+':'+data[2][k]).innerHTML = data[5] + data[3][pos][k] + '\')">'+ adjustString(data[3][pos][k], (CC_visual == 'table'  ? 50 : 20))+'</span>';
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									if(data[3][pos][k] != not_informed_text)
									Element(id+':'+data[2][k]).innerHTML = data[5] + data[3][pos][k] + '\')">'+ data[3][pos][k]+'</span>';
									else
										Element(id+':'+data[2][k]).innerHTML = data[13] + data[3][pos][k] + '\')">'+ data[3][pos][k]+'</span>';
								}
								break;

							case 'cc_phone':
								if (data[3][pos][k].length > 20)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
								}
								if(data[3][pos][k] != " ")
								Element(id+':cc_phone').innerHTML = (data[3][pos][k].length < 23) ? data[3][pos][k]:data[3][pos][k].substr(0,22)+"<br>"+data[3][pos][k].substr(22,data[3][pos][k].length);
								else
									Element(id+':cc_phone').innerHTML = not_informed_text;
								break;

							case 'cc_title':
								if(preferences.departmentShow && ccTree.catalog_perms == 1){
									if(data[3][pos][k] == " " || data[3][pos][k] == "" || data[3][pos][k] == "undefined"){
										Element(id+':'+data[2][k]).innerHTML = not_informed_text;
									}
									else if (data[3][pos][k].length > 15)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 15);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = data[3][pos][k];
								}
								break;
								}else{
									break;
								}
							case 'cc_id':
								var id_contact = data[3][pos][k];
								Element(id+':'+data[2][k]).value = data[3][pos][k];
								Element(id+':cc_photo').src = '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_photo' + (data[4][pos] != 0 ? '&id='+data[3][pos][k] : '');
								if(ccTree.catalog_perms == 1)
								{
									Element(id+':cc_icon_data').innerHTML =  '<span title="'+Element('cc_msg_copy_to_catalog').value+'" id="' + id + ':ccQuickAdd" onmouseout="window.status=\'\';" onclick="ccQuickAddContact.showList(\''+ Element(id+':cc_id').value + '\');return true;" style="cursor: pointer; cursor: hand; z-index: 1"><img src="templates/default/images/address-conduit-16.png" align="center"></span>';
									if (data[12] == true || data[12] == 'true')
										Element(id+':cc_icon_data').innerHTML += "  |  " + '<span title="'+ Element('cc_msg_show_extra_detail').value+'" id="' + id + ':ccContactDetails" onclick="javascript:openContactDetails(\'' + Element(id+':cc_id').value + '\');" style="cursor: pointer; cursor: hand; z-index: 1"><img src="templates/default/images/addressbook-mini.png" align="center"></span>';
								}
								break;

							case 'cc_forwarding_address':
								var account_type = data[3][pos][k];

								if( !account_type)
									break;
								else
									if (account_type == 'list' || account_type == 'group')
										icon = '<img src="templates/default/images/people-mini.png" align="center">';
                                    else
										icon = '';
                                                                            
                                Element(id+':cc_icon_group').innerHTML =  '<span title="'+Element('cc_participants').value+'"  onmouseout="window.status=\'\';" onclick="ccListParticipants.showList(\''+ Element(id+':cc_id').value + '\',null,null,null,\''+account_type+'\');return true;" style="cursor: pointer; cursor: hand; z-index: 1">'+icon+'&nbsp;&nbsp;</span>';
                                break;


							//Para tratar tamanho do campo "celular" do empregado
							case 'cc_mobile':
								if(preferences.cellShow && ccTree.catalog_perms == 1){
								if (data[3][pos][k].length > 20)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
								}
								Element(id+':cc_mobile').innerHTML = data[3][pos][k];
								break;
								}else{
									break;
								}		
							//Para tratar tamanho do campo "matricula" do empregado
							case 'cc_empNumber':
								if(preferences.empNumShow && ccTree.catalog_perms == 1){
								if (data[3][pos][k].length > 20)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
								}
								Element(id+':cc_empNumber').innerHTML = data[3][pos][k];
								break;
								}else{
									break;
								}
							//Para tratar tamanho do campo "departamento" do empregado
							case 'cc_department':
								if (data[3][pos][k].length > 15)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 15);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 15);
								}
								Element(id+':cc_department').innerHTML = data[3][pos][k];
								break;

							default:
								if (data[3][pos][k].length > 10)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 10);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									if (Element(id+':'+data[2][k]) == null) alert('É nulo');
									Element(id+':'+data[2][k]).innerHTML = data[3][pos][k];
								}
						}
					}else{
						data[3][pos][k] = unescape(data[3][pos][k]);
						switch (data[2][k])
						{
							case 'cc_mail':
								Element(id+':'+data[2][k]).innerHTML = data[13] + not_informed_text + '\')">'+ not_informed_text +'</span>';
								break;

							case 'cc_phone':
								Element(id+':cc_phone').innerHTML = not_informed_text;
								break;
							case 'cc_empNumber':
								if(preferences.empNumShow && ccTree.catalog_perms == 1){
									var cc_empNumberTD = Element(id+':cc_empNumber').parentNode;
									if(cc_empNumberTD.tagName != "DIV")
										//cc_empNumberTD.parentNode.removeChild(cc_empNumberTD);
										Element(id+':cc_empNumber').innerHTML = not_informed_text;
									else
										cc_empNumberTD.removeChild(Element(id+':cc_empNumber'));
								}
								break;
							case 'cc_mobile':
								if(preferences.cellShow && ccTree.catalog_perms == 1){
									var cc_mobileTD = Element(id+':cc_mobile').parentNode;
									if(cc_mobileTD.tagName != "DIV")
										//cc_mobileTD.parentNode.removeChild(cc_mobileTD);
										Element(id+':cc_mobile').innerHTML = not_informed_text;
									else
										cc_mobileTD.removeChild(Element(id+':cc_mobile'));
								}
								break;
							case 'cc_title':
								if(preferences.departmentShow && ccTree.catalog_perms == 1){
									var cc_titleTD = Element(id+':cc_title').parentNode;
									if(cc_titleTD.tagName != "DIV")
										Element(id+':cc_title').innerHTML = not_informed_text;
									else
										cc_titleTD.removeChild(Element(id+':cc_title'));
								}
								break;
						}
					}
				}
				
				
				if (type == "shared_contacts") {
					if (data[3][pos][11] & 4) 
						eval("document.getElementById(id + ':cc_card_edit').onclick = function(){editSharedContact(Element('"+id+"' + ':cc_id').value);};");
					else 
						document.getElementById(id + ':cc_card_edit').onclick = function(){
							alert(Element('cc_msg_not_allowed').value);
						};
					if (data[3][pos][11] & 8) 
						eval("document.getElementById(id + ':cc_card_remove').onclick = function(){removeEntry(Element('" + id + "' + ':cc_id').value);};");
					else 
						document.getElementById(id + ':cc_card_remove').onclick = function(){
							alert(Element('cc_msg_not_allowed').value);
						};
				}

				if (--ncards == 0)
				{
					j = CC_max_cards[0];
					i = CC_max_cards[1];
				}

				pos++;
			}
		}
	}
	deleteBlankFields("cc_name_empNumber");
	deleteBlankFields("cc_name_mobile");
	deleteBlankFields("cc_name_title");
}
function deleteBlankFields(field){
		var saia = true;
		var empNumbers = document.getElementsByName(field);
		for(var i = 0; i < empNumbers.length; i++){
			if(empNumbers[i].getElementsByTagName("SPAN")[0].innerHTML != not_informed_text){
				saia = false;
				i = empNumbers.length;
			}
		}
		if(saia){
			for(var i = 0; i < empNumbers.length; i++){
				empNumbers[i].style.display = "none";
			}
		}
}
function populateGroupsInCards(data,type)
{
	var pos = 0;
	var contacts = data[5];
	var ncards = data[3].length;

	if (typeof(data[3]) == 'object' && ncards > 0)
	{
		for (var i = 0; i < CC_max_cards[1]; i++)
		{
			for (var j = 0; j < CC_max_cards[0]; j++)
			{
				id = 'cc_card:'+j+':'+i;

				for (var k = 0; k < data[2].length; k++)
				{

					if(data[3][pos][k] != 'none')
					{

						switch (data[2][k])
						{
							case 'cc_title':
								if (data[3][pos][k].length > (CC_visual == 'table'  ? 50 : 20))
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], (CC_visual == 'table'  ? 50 : 20));
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = data[3][pos][k];
								}
								if(data[3][pos][5])
									Element(id+':'+data[2][k]).innerHTML += "<br><span style='margin-left:30px'><font size='-2' color='#808080'><i>"+data[3][pos][5]+"</i></font></span>";
								break;

							case 'cc_short_name':
								if (data[3][pos][k].length > (CC_visual == 'table'  ? 50 : 20))
								{
									Element(id+':'+data[2][k]).innerHTML = data[5] + ''+data[3][pos][k]+'::'+data[3][pos][6] +'\')">'+adjustString(data[3][pos][k], (CC_visual == 'table'  ? 50 : 20))+'</span>';
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = data[5] + ''+data[3][pos][k]+(data[3][pos][6] ? '::'+data[3][pos][6] : "") + '\')">'+data[3][pos][k]+'</span>';
								}
								break;

							case 'cc_contacts':

								var id_group = data[3][pos][k-1];
								var title = data[3][pos][k-3];
								contacts = data[3][pos][k];
								var contact = "";
								var email = "";

								for (var d = 0; d < contacts.length; d++) {
									contact += contacts[d]['names_ordered']+ ",";
									email += contacts[d]['connection_value']+",";
								}

								Element(id+':cc_participantes').innerHTML = '<span title="Ver Participantes" id="' + id + ':ccQuickAdd" onmouseout="window.status=\'\';" onclick="ccListParticipants.showList(\''+ Element(id+':cc_id').value+'value\', \''+contact+'\', \''+email+'\', \''+title+'\', \''+id_group+'\');return true;" style="cursor: pointer; cursor: hand; z-index: 1"><img title="Ver participantes" align="center" src="templates/default/images/people-mini.png">&nbsp;&nbsp</span>';

								break;

							case 'cc_id':
								var id_contact = data[3][pos][k];
								Element(id+':'+data[2][k]).value = data[3][pos][k];

								break;

						}
					}
				}

				if (type == "shared_groups") {
					
					if (data[3][pos][4] & 4)
						eval("document.getElementById(id + ':cc_card_edit').onclick = function(){editSharedGroup(Element('"+id+"' + ':cc_id').value,"+data[3][pos][7]+");};");
					else 
						document.getElementById(id + ':cc_card_edit').onclick = function(){	alert(Element('cc_msg_not_allowed').value); };
										
					if (data[3][pos][4] & 8) 
						eval("document.getElementById(id + ':cc_card_remove').onclick = function(){removeEntry(Element('" + id + "' + ':cc_id').value,'groups');};");
					else 
						document.getElementById(id + ':cc_card_remove').onclick = function(){
							alert(Element('cc_msg_not_allowed').value);
						};
				}				

				if (--ncards == 0)
				{
					j = CC_max_cards[0];
					i = CC_max_cards[1];
				}

				pos++;
			}
		}
	}
}

function adjustString (str, max_chars)
{
	if (str.length > max_chars)
	{
		return str.substr(0,max_chars) + '...';
	}
	else
	{
		return str;
	}
}

function setMaxCards (maxcards)
{
	CC_max_cards = maxcards;
	ncards = maxcards[0] * maxcards[1];

	var handler = function (responseText)
	{
		showMessage('ok');
	};

	Connector.newRequest('setMaxCards', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=set_n_cards&ncards=' + ncards, 'GET');
}

function getMaxCards ()
{
	var coord = new Array();

	card_space_width = parseInt(Element('cc_main').offsetWidth) - parseInt(Element('cc_left').offsetWidth) - parseInt(CC_card_extra);
	card_space_height = parseInt(Element('cc_card_space').offsetHeight) - parseInt(CC_card_extra);

	card_width = CC_card_image_width + CC_card_extra;
	card_height = CC_card_image_height + CC_card_extra;

	ncols = parseInt(card_space_width / card_width);
	nlines = parseInt(card_space_height / card_height);

	coord[0] = ncols;
	coord[1] = 10;

	return coord;
}

function getCardHTML (id, type)
{
		if(type == 'groups' || type == 'shared_groups') {
			html_card = '<td id="' + id + '" style="width: ' + CC_card_image_width + 'px; height: ' + CC_card_image_height + '">' +
            '<div style="border: 0px solid #999; position: relative;">' +
				'<img src="templates/default/images/card.png" border="0" width="' + CC_card_image_width +'" height="' + CC_card_image_height + '"i ondblclick="editContact(Element(\'' + id + ':cc_id\').value);">' +
				'<img title="'+Element('cc_msg_group_edit').value+'" id="' + id + ':cc_card_edit" style="position: absolute; top: 35px; left: 222px; width: 18px; height: 18px; cursor: pointer; cursor: hand; z-index: 1" onclick="editGroup(Element(\'' + id + ':cc_id\').value);" onmouseover="resizeIcon(\''+id+':cc_card_edit\',0)" onmouseout="resizeIcon(\''+id+':cc_card_edit\',1)" src="templates/default/images/cc_card_edit.png">' +
				'<img title="'+Element('cc_msg_group_remove').value+'" id="' + id + ':cc_card_remove" style="position: absolute; top: 78px; left: 223px; width: 15px; height: 14px; cursor: pointer; cursor: hand; z-index: 1" onclick="removeEntry(Element(\'' + id + ':cc_id\').value,\'groups\');" onmouseover="resizeIcon(\''+id+':cc_card_remove\',0)" onmouseout="resizeIcon(\''+id+':cc_card_remove\',1)" src="templates/default/images/cc_x.png">' +
				'<span id="' + id + ':cc_title" style="position: absolute; top: 30px; left: 75px; width: 135px; border: 0px solid #999; font-weight: bold; font-size: 10px; text-align: center; height: 10px;" onmouseover="//Element(\''+id+':cc_name_full\').style.visibility=\'visible\'" onmouseout="//Element(\''+id+':cc_name_full\').style.visibility=\'hidden\'"></span>' +
				'<span id="' + id + ':cc_participantes" style="cursor: pointer; cursor: hand; z-index: 1;position: absolute; top: 15px; left: 15px"></span>' +
				'<span onMouseOver="this.title = \''+Element('cc_send_mail').value+'\'" id="' + id + ':cc_short_name" style="position: absolute; top: 105px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 10px; text-align: center; height: 10px;"></span>' +
				'<input id="' + id + ':cc_id" type="hidden">' +
			'</div>' + '</td>';

		}
		else {
			html_card = '<td id="' + id + '" style="width: ' + CC_card_image_width + 'px; height: ' + CC_card_image_height + '">' +
				'<div style="border: 0px solid #999; position: relative;">' +
					'<img src="templates/default/images/card.png" border="0" width="' + CC_card_image_width +'" height="' + CC_card_image_height + '"i ondblclick="editContact(Element(\'' + id + ':cc_id\').value);">' +
						( ccTree.catalog_perms == 1 ?
						'<span id="' + id + ':cc_icon_data" style="position: absolute; top: 35px; left: 222px; width: 18px; height: 18px; cursor: pointer; cursor: hand; z-index: 1"></span>':'') +
						(ccTree.catalog_perms & 2 ?
						'<img title="'+Element('cc_msg_card_edit').value+'" id="' + id + ':cc_card_edit" style="position: absolute; top: 35px; left: 222px; width: 18px; height: 18px; cursor: pointer; cursor: hand; z-index: 1" onclick="editContact(Element(\'' + id + ':cc_id\').value);" onmouseover="resizeIcon(\''+id+':cc_card_edit\',0)" onmouseout="resizeIcon(\''+id+':cc_card_edit\',1)" src="templates/default/images/cc_card_edit.png">' +
						'<img title="'+Element('cc_msg_card_remove').value+'" id="' + id + ':cc_card_remove" style="position: absolute; top: 78px; left: 223px; width: 15px; height: 14px; cursor: pointer; cursor: hand; z-index: 1" onclick="removeEntry(Element(\'' + id + ':cc_id\').value);" onmouseover="resizeIcon(\''+id+':cc_card_remove\',0)" onmouseout="resizeIcon(\''+id+':cc_card_remove\',1)" src="templates/default/images/cc_x.png">' : '') +
						'<img id="' + id + ':cc_photo" style="position: absolute; top: 15px; left: 7px;" src="" border="0" ondblclick="editContact(Element(\'' + id + ':cc_id\').value);">' +
						'<span id="' + id + ':cc_company" style="position: absolute; top: 5px; left: 75px; width: 135px; border: 0px solid #999; font-weight: bold; font-size: 10px; text-align: center; height: 10px;" onmouseover="//Element(\''+id+':cc_company_full\').style.visibility=\'visible\'" onmouseout="//Element(\''+id+':cc_company_full\').style.visibility=\'hidden\'"></span>' +
						'<span style="cursor: pointer; cursor: hand; z-index: 1;position: absolute; top: 100px; left: 35px"  valign="bottom" id="' + id + ':cc_icon_group">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>'+
						//Para exibir a matricula do empregado nos cartoes
						'<span id="' + id + ':cc_empNumber" style="position: absolute; top: 20px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 9px; text-align: center; height: 10px;"></span>' +
						'<span id="' + id + ':cc_name" style="position: absolute; top: 30px; left: 75px; width: 135px; border: 0px solid #999; font-weight: bold; font-size: 10px; text-align: center; height: 10px;" onmouseover="//Element(\''+id+':cc_name_full\').style.visibility=\'visible\'" onmouseout="//Element(\''+id+':cc_name_full\').style.visibility=\'hidden\'"></span>' +
						'<span id="' + id + ':cc_title" style="position: absolute; top: 90px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 12px; text-align: center; height: 10px;"></span>' +
						//Para exibir o setor/lotacao do empregado nos cartoes
						'<span id="' + id + ':cc_department" style="position: absolute; top: 60px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 10px; text-align: center; height: 10px;"></span>' +
						'<span id="' + id + ':cc_phone" style="position: absolute; top: 75px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 10px; text-align: center; height: 10px;"></span>' +
						//Para exibir o celular empresarial do empregado na tabela
						'<span id="' + id + ':cc_mobile" style="position: absolute; top: 90px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 10px; text-align: center; height: 10px;"></span>' +
						'<span id="' + id + ':cc_mail" style="position: absolute; top: 105px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 10px; text-align: center; height: 10px;"></span>' +
						'<span id="' + id + ':cc_alias" style="position: absolute; top: 95px; left: 10px; width: 60px; border: 0px solid #999; font-weight: normal; font-size: 9px; text-align: center; height: 10px;"></span>' +
					'<input id="' + id + ':cc_id" type="hidden">' +
				'</div>' + '</td>';
		}

	return html_card;
}

function getTableHTML (id, type)
{
			var bg = "";
			if(!is_ie)
				bg = "this.style.background=\'\'";
			else
				bg = "this.style.background=\'#EEEEEE\'";
			if(type == 'groups' || type == 'shared_groups') {
				html_card = '<tr width="40%" id="' + id + '" onmouseout="'+bg+'" onmouseover="this.style.background=\'LIGHTYELLOW\'" bgcolor="EEEEEE"><td width="auto" style="font-weight: normal; font-size: 10px; text-align: left; height: 10px;">' +
					'<span id="' + id + ':cc_participantes" style="cursor: pointer; cursor: hand; z-index: 1"></span>' +
					'<span id="' + id + ':cc_title"></span></td>' +
					'<td width="40%" style="solid #999; font-weight: normal; font-size: 10px; text-align: left; height: 10px"><span id="' + id + ':cc_short_name"></span></td>' +
					'<td align="center" width="10%">'+
					'<img  title="'+Element('cc_msg_group_edit').value+'" id="' + id + ':cc_card_edit" style=" cursor: pointer; cursor: hand; z-index: 1;width: 18px; height: 18px;"  onclick="editGroup(Element(\'' + id + ':cc_id\').value);" src="templates/default/images/cc_card_edit.png">' +
					'&nbsp;&nbsp;|&nbsp;&nbsp;'+
					'<img  title="'+Element('cc_msg_group_remove').value+'" id="' + id + ':cc_card_remove" style="width: 15px; height: 14px; cursor: pointer; cursor: hand; z-index: 1" onclick="removeEntry(Element(\'' + id + ':cc_id\').value,\'groups\');" src="templates/default/images/cc_x.png">'  +
					'<input id="' + id + ':cc_id" type="hidden">'+
					'</td></tr>';
            }
            else {
				html_card = '<tr  style="height:20px" id="' + id + '" onmouseout="'+bg+'" onmouseover="this.style.background=\'LIGHTYELLOW\'" bgcolor="EEEEEE">' +
					//Para exibir a matricula do empregado na tabela
					(preferences.empNumShow && ccTree.catalog_perms == 1 ? '<td align="center" width="10%" name="cc_name_empNumber" nowrap><span style="solid #999; font-weight: normal; font-size: 10px;height: 10px" id="' + id + ':cc_empNumber"></span></td>' : '') +
					'<td width="auto" style="font-weight: normal; font-size: 10px; text-align: left; height: 10px;"><span valign="bottom" id="' + id + ':cc_icon_group">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span id="' + id + ':cc_name"></span></td>' +
					'<td width="20%" align="center" name="cc_name_send_mail" style="solid #999; font-weight: normal; font-size: 10px; height: 10px"><span onMouseOver="this.title = \''+Element('cc_send_mail').value+' => '+'\'+document.getElementById(\''+id + ':cc_name\').innerHTML" id="' + id + ':cc_mail"></span></td>' +
					'<td width="20%" align="center" name="cc_name_phone" nowrap><span style="solid #999; font-weight: normal; font-size: 10px;height: 10px" id="' + id + ':cc_phone"></span></td>' +
					//Para exibir o celular empresarial do empregado na tabela
					(preferences.cellShow && ccTree.catalog_perms == 1 ?	'<td align="center" name="cc_name_mobile" nowrap><span style="solid #999; font-weight: normal; font-size: 10px;height: 10px" id="' + id + ':cc_mobile"></span></td>' : '') + 
					//Para exibir o setor/lotacao do empregado na tabela
					(preferences.departmentShow && ccTree.catalog_perms == 1 ? '<td align="center" name="cc_name_title" nowrap><span style="solid #999; font-weight: normal; font-size: 10px;height: 10px" id="' + id + ':cc_title"></span></td>' : '') +
					( ccTree.catalog_perms == 1 ?
					'<td align="center" width="10%"><span valign="bottom" id="' + id + ':cc_icon_data"></span></td>':'') +					
					(ccTree.catalog_perms & 2 ?
					'<td align="center" width="10%" >'+
					'<img  title="'+Element('cc_msg_card_edit').value+'" id="' + id + ':cc_card_edit" style=" cursor: pointer; cursor: hand; z-index: 1;width: 18px; height: 18px;"  onclick="editContact(Element(\'' + id + ':cc_id\').value);" src="templates/default/images/cc_card_edit.png">' +
					'&nbsp;&nbsp;|&nbsp;&nbsp;'+
					'<img title="'+Element('cc_msg_card_remove').value+'" id="' + id + ':cc_card_remove" style="width: 15px; height: 14px; cursor: pointer; cursor: hand; z-index: 1" onclick="removeEntry(Element(\'' + id + ':cc_id\').value);" src="templates/default/images/cc_x.png">' : '') +
					'<input id="' + id + ':cc_id" type="hidden">'+
					'<input type="hidden" id="' + id + ':cc_photo">' +
					//'<span id="' + id + ':cc_mobile" style="display:none"></span>' +
					'<span id="' + id + ':cc_alias" style="display:none"></span>' +
					// Esse campo é necessário se o contato possui dados no campo cc_company
					'<span id="' + id + ':cc_company" style="display:none"></span>' +
					'</td></tr>';
			}

	return html_card;
}

function drawTable(ncards, type)
{
	var pos;
	this.not_informed_text = Element("cc_msg_not_informed").value;
	html_cards = '<div id="divScrollMain" style="overflow:auto;z-index:1"><table width="100%" border="0" cellpadding="0" cellspacing="3">';
	
	if (ncards > 0)
	{

		for (var i = 0; i < CC_max_cards[1]; i++)
		{
			html_cards += '';
			for (var j = 0; j < CC_max_cards[0]; j++)
			{
				html_cards += getTableHTML('cc_card:' + j + ':' + i, type);
				if (--ncards == 0)
				{
					j = CC_max_cards[0];
					i = CC_max_cards[1];
				}
			}
			html_cards += '';
		}
		if((ccTree.catalog_perms & 2) && type != 'groups' && type !='shared_contacts' && type !='shared_groups')
			html_cards += '<tr><td colspan=4 align="right"><button id="cc_button_tools" value="" type="button" onclick="javascript:removeAllEntries()">Remover Todos</button></td></tr>';
	}
	else if (CC_max_cards != 0)
	{
		html_cards += '<tr><td  align="center">' + Element('cc_msg_no_cards').value + '</td></tr>';
	}
	else
	{
		html_cards += '<tr><td  align="center">' + Element('cc_msg_err_no_room').value + '</td></tr>';
	}

	html_cards += '</table></div>';

	Element('cc_card_space').innerHTML = html_cards;
}

function drawCards(ncards, type)
{
	var pos;
	html_cards = '<div id="divScrollMain" style="overflow:auto;z-index:1">';
	html_cards += '<table  border="0" cellpadding="0" cellspacing="' + CC_card_extra + '">';

	if (ncards > 0)
	{
		for (var i = 0; i < CC_max_cards[1]; i++)
		{
			html_cards += '<tr>';
			for (var j = 0; j < CC_max_cards[0]; j++)
			{
				html_cards += getCardHTML('cc_card:' + j + ':' + i, type);
				if (--ncards == 0)
				{
					j = CC_max_cards[0];
					i = CC_max_cards[1];
				}
			}
			html_cards += '</tr>';
		}
		if((ccTree.catalog_perms & 2) && type != 'groups' && type !='shared_contacts' && type !='shared_groups')
			html_cards += '<tr><td colspan=3 align="right"><button id="cc_button_tools" value="" type="button" onclick="javascript:removeAllEntries()">Remover Todos</button></td></tr>';
	}
	else if (CC_max_cards != 0)
	{
		html_cards += '<tr><td>' + Element('cc_msg_no_cards').value + '</td></tr>';
	}
	else
	{
		html_cards += '<tr><td>' + Element('cc_msg_err_no_room').value + '</td></tr>';
	}

	html_cards += '</table></div>';

	Element('cc_card_space').innerHTML = html_cards;
}

function showCards (letter,page, ids)
{
	this.not_informed_text = Element("cc_msg_not_informed").value;
	
	var data  = new Array();
	flag_compartilhado = false;
	if ( letter != CC_actual_letter )
	{
		CC_actual_page = '1';
	}
	else
	{
		CC_actual_page = page;
	}

	CC_actual_letter = letter;

	if (CC_max_cards[0] == 0)
	{

		if(CC_visual == 'cards')
			drawCards(0);
		else if(CC_visual == 'table')
			drawTable(0);

		setPages(0,0);
		return;
	}

	var handler = function (responseText)
	{
		var data = new Array();
		data = unserialize(responseText);
		if (data[0] == '0')
		{
			Element('cc_type_contact').value = data[1];
			CC_npages = 0;
			CC_actual_page = 1;
			if(CC_visual == 'cards')
				drawCards(0);
			else if(CC_visual == 'table')
				drawTable(0);
			setPages(0,0);
			return;
		}
		else
		Element('cc_type_contact').value = data[10];

//		Element('cc_debug').innerHTML = responseText;

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		if (typeof(data[3]) == 'object')
		{
			if (data[8] == 'bo_shared_people_manager')
			{
				flag_compartilhado = true;
			}
			else
			{
				flag_compartilhado = false;
			}
			qtd_compartilhado = data[9];
			CC_npages = parseInt(data[0]);
			CC_actual_page = parseInt(data[1]);
			if(CC_visual == 'cards')
				drawCards(data[3].length, data[10]);
			else if(CC_visual == 'table')
				drawTable(data[3].length, data[10]);
			resizeWindow();
			populateCards(data, data[10]);
			setPages(data[0], data[1]);

		}
		else if (data['error'])
		{
			showMessage(data['error']);
		}
		else
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}
	};

	var info = "letter="+letter+"&page="+CC_actual_page+"&ids="+ids;
	Connector.newRequest('showCards', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_cards_data', 'POST', handler, info);
}


function clearCards()
{
	clearLetterSelection();
	setHeightSpace();
	setMaxCards(getMaxCards());

	if(CC_visual == 'cards')
		drawCards(0);
	else if(CC_visual == 'table')
		drawTable(0);

	setPages(0,0);
	return;
}

/***********************************************\
*        COMMON ENTRY FUNCTIONS                *
\***********************************************/

function ccChangeVisualization(type)
{
	var table_h = Element('cc_panel_table');
	var cards_h = Element('cc_panel_cards');

	switch (type)
	{
		case 'cards':
			cards_h.style.display = 'none';
			table_h.style.display = 'inline';
			break;

		case 'table':
			table_h.style.display = 'none';
			cards_h.style.display = 'inline';
			break;
	}

	CC_visual = type;
	showCards(getActualLetter(), getActualPage());
}

function ccSearchUpdate()
{
	Element('cc_panel_letters').style.display = 'none';
	Element('cc_panel_search').style.display  = 'inline';

	if(CC_visual == 'cards')
		drawCards(0);
	else if(CC_visual == 'table')
		drawTable(0);

	if (CC_actual_letter != 'search')
	{
		CC_last_letter = CC_actual_letter;
	}
}

function ccSearchHidePanel()
{
	Element('cc_panel_search').style.display  = 'none';
	Element('cc_panel_letters').style.display = 'inline';
	if (CC_actual_letter == 'search')
	{
		CC_actual_letter = CC_last_letter;
	}
}

function ccSearchHide()
{
	Element('cc_panel_search').style.display  = 'none';
	Element('cc_panel_letters').style.display = 'inline';
	clearCards();
}

/***********************************************\
*               QUICK ADD FUNCTIONS             *
\***********************************************/


function resetQuickAdd ()
{
	Element('cc_qa_alias').value = '';
	Element('cc_qa_given_names').value = '';
	Element('cc_qa_family_names').value = '';
	Element('cc_qa_phone').value = '';
	Element('cc_qa_email').value = '';
}

function getQuickAdd ()
{
	var data = new Array();
	data[0] = Element('cc_qa_alias').value;
	data[1] = Element('cc_qa_given_names').value;
	data[2] = Element('cc_qa_family_names').value;
	data[3] = Element('cc_qa_phone').value;
	data[4] = Element('cc_qa_email').value;

	return data;
}

function sendQuickAdd ()
{
	var data = getQuickAdd();

	var str = serialize(data);

	if (!str)
	{
		return false;
	}

	var handler = function (responseText)
	{
		setTimeout('updateCards()',100);;
	}

	resetQuickAdd();

	Connector.newRequest('quickAdd', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=quick_add', 'POST', handler, 'add='+escape(str));
}

	
function connectVoip (phoneUser, typePhone){
	var handler_voip = function (responseText){
		if(!responseText) {
			alert("Erro conectando servidor VoIP.");
		}
		else{
		    data = unserialize(responseText);
			alert("Requisitando chamada para o ramal: "+data);
        }
	}
	Connector.newRequest('voip', "../../expressoMail/controller.php?action=expressoMail.functions.callVoipconnect&to="+phoneUser+"&typePhone="+typePhone, 'POST', handler_voip);
	}
