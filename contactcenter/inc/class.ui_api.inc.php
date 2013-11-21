<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/*
	 * This is the Main ContactCenter API for other eGroupWare applications
	 *
	 */
  
	class ui_api
	{
		var $commons;
		var $commons_loaded = false;
		
		function ui_api()
		{
                        $preferences = ExecMethod('contactcenter.ui_preferences.get_preferences');

			if (!is_array($preferences))
			{
				$preferences['personCardEmail'] = 1;
			}
			$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);

			$template->set_file(array('api' => 'api_common.tpl'));
			/* Style Sheets */
			$template->set_var('cc_css', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/styles/cc_api.css');
			$template->set_var('cc_dtree_css', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dftree/dftree.css');

			$template->set_var('cc_email_id_type', $preferences['personCardEmail']);
			
			
			/* Messages */
			$template->set_var('cc_msg_err_invalid_catalog',lang('Unavailable or empty Catalog'));
			$template->set_var('cc_msg_err_contacting_server',lang('Couldn\'t contact server or server response is invalid. Contact Admin.'));
			$template->set_var('cc_msg_err_timeout',lang('Operation Timed Out.'));
			$template->set_var('cc_msg_err_serialize_data_unknown',lang('Data to be serialized is of unknown type!'));
			$template->set_var('cc_msg_err_shared',lang('No shared catalog that has permission to add.'));
			$template->set_var('cc_msg_err_duplicate_group',lang('This group name already exists, please enter another name.'));
			/* End Messages */

			if ($preferences['displayConnector'])
			{
				$template->set_var('cc_connector_visible', 'true');
			}
			else
			{
				$template->set_var('cc_connector_visible', 'false');
			}
			$template->set_var('cc_loading_1', lang('Contacting Server...'));
			$template->set_var('cc_loading_2', lang('Server Contacted. Waiting for response...'));
			$template->set_var('cc_loading_3', lang('Processing Information...'));
			$template->set_var('cc_loading_image', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/templates/default/images/loading_back.png');
			$template->set_var('cc_server_root', $GLOBALS['phpgw_info']['server']['webserver_url']);
			$template->set_var('cc_phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');

			/* JS Files */
			$template->set_var('cc_js_aux', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/ccAux.js');
			$template->set_var('cc_js_connector', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/connector.js');
			$template->set_var('cc_js_wz_dragdrop', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/wz_dragdrop/wz_dragdrop.js');
			$template->set_var('cc_js_dtree', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dftree/dftree.js');
			$template->set_var('cc_js_dtabs', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dTabs/dTabs.js');
			$template->set_var('cc_js_djswin', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dJSWin/dJSWin.js');
			$template->set_var('cc_js_catalog_tree', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_tree.js');
			
			$template->parse('out','api');

			$this->commons = $template->get_var('out');
			$this->commons_loaded = false;			
			}
		
		/* DEPRECTED! Use get_email_win() instead */
		function ui_get_email_win()
		{
			return $this->get_email_win();
		}

		function get_email_win()
		{
			//$search = $this->ui_get_search_win();
			
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template', $template_dir);

			$template->set_file(array('email_win' => 'email_win.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			//$template->set_var('cc_search_win', $search);
			$template->set_var('cc_js_search', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_search.js');
			
			$template->set_var('cc_email_id_type', $search);
			
			$template->set_var('cc_email_win_title',lang('Contact Center').' - '.lang('Catalog Entries Emails'));
			$template->set_var('cc_email_status',lang('Status').':');
			$template->set_var('cc_email_search_text',lang('Search').'...');
			
			$template->set_var('cc_choose_catalogue',lang('Choose a catalogue').'...');
			$template->set_var('cc_choose_ordinance',lang('Choose a ordinance').'...');
			
			$template->set_var('cc_btn_to_add',lang('To').' >>');
			$template->set_var('cc_btn_to_del','<< '.lang('To'));
			$template->set_var('cc_btn_cc_add',lang('Cc').' >>');
			$template->set_var('cc_btn_cc_del','<< '.lang('Cc'));
			$template->set_var('cc_btn_cco_add',lang('Bcc').' >>');
			$template->set_var('cc_btn_cco_del','<< '.lang('Bcc'));
			$template->set_var('cc_btn_new',lang('New').'...');
			$template->set_var('cc_btn_details',lang('Details').'...');
			$template->set_var('cc_btn_update',lang('Update'));
			$template->set_var('cc_btn_ok',lang('Ok'));
			$template->set_var('cc_btn_cancel',lang('Cancel'));
			
			$template->set_var('cc_label_to',lang('To').':');
			$template->set_var('cc_label_cc',lang('Cc').':');
			$template->set_var('cc_label_cco',lang('Bcc').':');
			$template->set_var('cc_label_entries',lang('Entries').':');
			$template->set_var('cc_label_catalogues',lang('Catalogues').':');
			$template->set_var('cc_label_catalogue_type',lang('Type Of Catalogue').':');
			$template->set_var('cc_label_ordinance_type',lang('Type Of Ordinace').':');
			
			$template->set_var('phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');
			
			$template->set_var('cc_js_email_win', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_email_win.js');
			
			$template->parse('out','email_win');

			return $template->get_var('out');
		}

		function ui_get_search_win()
		{
			return $this->get_search_win();
		}

		function get_search_win()
		{
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('search' => 'search_win.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			$template->set_var('cc_search_title',lang('Contact Center - Search for Catalog Entries'));
			$template->set_var('cc_search_minimize',lang('Minimize'));
			$template->set_var('cc_search_close',lang('Close'));
			$template->set_var('cc_search_catalogues',lang('Catalogues'));
			$template->set_var('cc_search_for',lang('Search for...'));
			$template->set_var('cc_search_recursive',lang('Recursive Search?'));
			$template->set_var('cc_search_go',lang('Go'));
			$template->set_var('cc_search_cancel',lang('Cancel'));
			
			$template->set_var('cc_js_search_win', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_search_win.js');
			
			$template->set_var('phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');
			
			$template->parse('out','search');

			return $template->get_var('out');
		}
		
		function get_contact_details()
		{
			$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$template->set_file(array('details' => 'contact_details.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			//$template->set_var('ccContactDetails.js', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/ccContactDetails.js');
			$template->set_var('cc_contact_details_title',lang("Contact's details"));
			$template->set_var('cc_contact_details_close',lang("Close"));
			$template->set_var('cc_contact_details_no_fields',lang("The extra details are not configured."));
			$template->set_var('cc_contact_details_attr_name',lang("Attribute name"));
			$template->set_var('cc_contact_details_attr_value',lang("Value"));
			$template->parse('out','details');
			return $template->get_var('out');
		}

		function get_search_obj()
		{
			return "\n".'<script type="text/javascript" src="'.$GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_search.js'.'"></script>'."\n";
		}

		function ui_get_full_add()
		{
			return $this->get_full_add();
		}

		function get_full_add()
		{
			$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
            if($GLOBALS['phpgw_info']['server']['personal_contact_type']=='True'){
				$template->set_file(array('full_add' => 'corporative_full_add.tpl'));
				$template->set_var('cc_contact_type','advanced');
			}else{
				$template->set_file(array('full_add' => 'full_add.tpl'));
				$template->set_var('cc_contact_type','default');
			}
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			$template->set_var('cc_contact_title',lang('Contact Center').' - '.lang('Contacts'));

			/* Messages */
			$template->set_var('cc_msg_err_empty_field',lang('field is empty'));
			$template->set_var('cc_msg_type_state',lang('Type new state here').'...');
			$template->set_var('cc_msg_type_city',lang('Type new city here').'...');
			/* End Messages */
			
			/* Contact */
			$template->set_var('cc_contact_save',lang('Save'));
			$template->set_var('cc_contact_cancel',lang('Cancel'));
			$template->set_var('cc_contact_reset',lang('Reset'));

			$template->set_var('cc_contact_personal',lang('Personal'));
			$template->set_var('cc_contact_addrs',lang('Addresses'));
			$template->set_var('cc_contact_corporative',lang('Corporative'));
			$template->set_var('cc_contact_conns',lang('Connections'));
			$template->set_var('cc_contact_grps', lang('Groups'));
			$template->set_var('emails_telephones', lang("E-Mails and Telephones"));
			$template->set_var('cc_default', lang("Default"));

                        $template->set_var('cc_contact_shared',lang('Add to catalog'));
                        $template->set_var('cc_contact_shared_types',lang('Choose in wich catalog you would like to add...'));

			/* End Contact */
			
			/* Contact - Personal Data */
			$dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];
		
			$j = 0;
			for ($i = 0; $i < 5; $i+=2)
			{
				switch($dateformat{$i})
				{
					case 'Y':
						$date[$j]['size'] = 4;
						$date[$j]['name'] = lang('Year');
						break;

					case 'm':
					case 'M':
						$date[$j]['size'] = 2;
						$date[$j]['name'] = lang('Month');
						break;

					case 'd':
						$date[$j]['size'] = 2;
						$date[$j]['name'] = lang('Day');
				}
				++$j;
			}
			
			$template->set_var('cc_pd_birth_size_0', "{$date[0]['size']}");
			$template->set_var('cc_pd_birth_size_1', "{$date[1]['size']}");
			$template->set_var('cc_pd_birth_size_2', "{$date[2]['size']}");
			
			$template->set_var('cc_pd_birthdate_0', "{$date[0]['name']}");
			$template->set_var('cc_pd_birthdate_1', "{$date[1]['name']}");
			$template->set_var('cc_pd_birthdate_2', "{$date[2]['name']}");

			$template->set_var('cc_pd_select_photo_b', lang('Browse').'...');
			$template->set_var('cc_form_photo_src', 'photo_form.html');
			
			$template->set_var('cc_pd_select_photo',lang('Select Photo'));
			$template->set_var('cc_pd_alias',lang('Alias'));
			$template->set_var('cc_pd_given_names',lang('Given Names'));
			$template->set_var('cc_pd_family_names',lang('Family Names'));
			$template->set_var('cc_pd_full_name',lang('Full Name'));
			$template->set_var('cc_pd_birthdate',lang('Birthdate'));
			$template->set_var('cc_pd_gpg_finger_print',lang('GPG Finger Print'));
			$template->set_var('cc_pd_suffix',lang('Suffix'));
			$template->set_var('cc_pd_choose_suffix',lang('Choose Suffix...'));
			$template->set_var('cc_pd_prefix',lang('Prefix'));
			$template->set_var('cc_pd_choose_prefix',lang('Choose Prefix...'));
			$template->set_var('cc_pd_notes',lang('Notes'));
			$template->set_var('cc_pd_sex',lang('Sex'));
			$template->set_var('cc_pd_choose_sex',lang('Choose Sex ...'));
			$template->set_var('cc_pd_male',lang('Male'));
			$template->set_var('cc_pd_female',lang('Female'));
			/* End Contact - Personal Data */
			
			/* Contact - Addresses */
			$template->set_var('cc_addr_types',lang('Type of Address'));
			$template->set_var('cc_addr_choose_types',lang('Choose Type of Address').'...');
			$template->set_var('cc_addr_countries',lang('Country'));
			$template->set_var('cc_addr_choose_countries',lang('Choose Country').'...');
			$template->set_var('cc_addr_states',lang('State'));
			$template->set_var('cc_addr_states_new',lang('New State').'...');
			$template->set_var('cc_addr_states_nostate',lang('No State'));
			$template->set_var('cc_addr_choose_states',lang('Choose State').'...');
			$template->set_var('cc_addr_cities',lang('City'));
			$template->set_var('cc_addr_cities_new',lang('New City').'...');
			$template->set_var('cc_addr_choose_cities',lang('Choose City').'...');
			$template->set_var('cc_addr_1',lang('Address 1'));
			$template->set_var('cc_addr_2',lang('Address 2'));
			$template->set_var('cc_addr_complement',lang('Complement'));
			$template->set_var('cc_addr_other',lang('Address Other'));
			$template->set_var('cc_addr_postal_code',lang('Postal Code'));
			$template->set_var('cc_addr_po_box',lang('PO Box'));
			$template->set_var('cc_addr_is_default',lang('Is Default?'));
			$template->set_var('cc_addr_yes',lang('Yes'));
			$template->set_var('cc_addr_no',lang('No'));
			$template->set_var('cc_available',lang('Available'));
			/* End Contact - Addresses */
			
			
			/* Contact - Connections */
			$template->set_var('cc_conn_type',lang('Type of Connection'));
			$template->set_var('cc_conn_name',lang('Connection Name'));
			$template->set_var('cc_conn_value',lang('Connection Value'));
			
			$template->set_var('cc_new_same_type',lang('New from the same Type').'...');
			
			$template->set_var('cc_conn_type_none',lang('Choose Type of Connection').'...');
			/* End Contact - Connections */

			/* Contact - Groups */
			$template->set_var('cc_grps_available', lang('Groups available'));
			$template->set_var('cc_grps_selected', lang('Selected groups'));
			$template->set_var('txt_add', lang('Add'));
			$template->set_var('txt_rem', lang('Remove'));
			/* End Contact - Groups */

			/* Corporate - contact */
			$template->set_var('cc_name_corporate',lang('Corporate'));
			$template->set_var('cc_job_title',lang('Job Title'));
			$template->set_var('cc_department',lang('Department'));
			$template->set_var('cc_web_page',lang('Web Page'));

			$template->set_var('email', lang("E-Mail"));
			$template->set_var('telephone', lang("Telephone"));
			$template->set_var('choose_email_telephone', lang("Choose 'E-Mail' or 'Telephone'"));

			$template->parse('out_full', 'full_add');

			return $template->get_var('out_full');
		}

		function get_quick_add_plugin()
		{
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('quickAdd' => 'quickAddPlugin.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			$template->set_var('ccQAPluginFile', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/ccQuickAdd-plugin.js');

			/* Fields Insertion */

			// TODO: get this from preferences
			$nFields = 5;
			$fields = array(
				lang('Alias'), 
				lang('Given Names'), 
				lang('Family Names'), 
				lang('Phone'),
				lang('Email')
			);
			
			$template->set_var('ccQAnFields', $nFields);
			
			$fieldsHTML = '';
			$fieldsTop = 10;
			$fieldsSpace = 30;
			for ($i = 0; $i < $nFields; ++$i)
			{
				$fieldsHTML .= '<span id="ccQuickAddT'.$i.'" style="position: absolute; top: '.($fieldsTop+$i*$fieldsSpace).'px; left: 5px; width: 100px; text-align: right; border: 0px solid #999;">'.$fields[$i].':</span>'."\n";
				if ($i == 0){
					$fieldsHTML .= '<input id="ccQuickAddI'.$i.'" type="text" value="" maxlength="30" style="position: absolute; top: '.($fieldsTop+$i*$fieldsSpace).'px; left: 110px; width: 135px;">'."\n";
				}
				else if ($i == 4){
					$fieldsHTML .= '<input id="ccQuickAddI'.$i.'" type="text" value="" maxlength="100" style="position: absolute; top: '.($fieldsTop+$i*$fieldsSpace).'px; left: 110px; width: 135px; text-transform:lowercase;">'."\n";
				}else if ($i == 3){
					$fieldsHTML .= '<input id="ccQuickAddI'.$i.'" type="text" value="" onkeyup="formatPhone(this)" maxlength="30" style="position: absolute; top: '.($fieldsTop+$i*$fieldsSpace).'px; left: 110px; width: 135px;">'."\n";
				}
				else {
					$fieldsHTML .= '<input id="ccQuickAddI'.$i.'" type="text" value="" maxlength="50" style="position: absolute; top: '.($fieldsTop+$i*$fieldsSpace).'px; left: 110px; width: 135px;">'."\n";
				}
			}

			$template->set_var('ccQAFields', $fieldsHTML);
			$template->set_var('ccQAWinHeight', ($i+1)*$fieldsSpace+$fieldsTop);
			$template->set_var('ccQAFunctionsTop', ($fieldsTop+$i*$fieldsSpace).'px');
			
			/* Images Dir */
			$template->set_var('ccQACardImgRoot', $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/templates/default/images/');
			
			/* Texts */
			$template->set_var('ccQATitle', lang('Contact Center').' - '.lang('Quick Add'));
			$template->set_var('ccQASave', lang('Save'));
			$template->set_var('ccQAClear', lang('Reset'));
			$template->set_var('ccQACancel', lang('Cancel'));
			
			$template->parse('out_QA', 'quickAdd');

			return $template->get_var('out_QA');
		}

                function parseGroupOption($level, $name, $selected=false){

                    $selected = $selected?'selected="selected"':'';
                    $optionTemplate = '<option value="{level}" {selected}>{value}</option>';

                    $optionTemplate = preg_replace('/\{level\}/', $level, $optionTemplate);
                    $optionTemplate = preg_replace('/\{selected\}/', $selected, $optionTemplate);
                    $optionTemplate = preg_replace('/\{value\}/', $name, $optionTemplate);

                    return $optionTemplate;

                }

                function getCatalogItens($level='0', $parentName=false){
                    $bo = CreateObject('contactcenter.bo_contactcenter');

                    $options = '';

                    $catalogTree = $bo->get_catalog_tree('0');
                    foreach ($catalogTree as $id => $catalog)
                    {
                        if ($level.'.'.$id !== '0.1')
                        {
                            $option = $level.'.'.$id === '0.0'?
                                $this->parseGroupOption($level.'.'.$id, $catalog['name'], true):
                                $this->parseGroupOption($level.'.'.$id, $catalog['name']);
                            $options .= $option;
                        }
                    }
                    return $options;
                }
		
		function get_add_group()
		{
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('addGroup' => 'addGroup.tpl'));

                        $catalogOptions = $this->getCatalogItens();
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				
				$template->set_var('cc_api', '');
			}

                        $template->set_var('ccAGSourceSelectContent', $catalogOptions);
                        $template->set_var('ccAGSearch', lang('Search'));
			
			$template->set_var('ccAGFile', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/ccAddGroup.js');			
			$template->set_var('ccAGWinHeightIE', '340');
			$template->set_var('ccAGWinHeightMO', '300');
			
			/* Images Dir */
			$template->set_var('ccAGCardImgRoot', $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/templates/default/images/');
			
			/* Texts */
			$template->set_var('ccAGTitle', lang('Contact Center').' - '.lang('Group Add'));
			$template->set_var('ccAGSave', lang('Save'));
			$template->set_var('ccAGClear', lang('Reset'));
			$template->set_var('ccAGCancel', lang('Cancel'));
			$title = '<input type="text" id="title" name="title" value="'.$group['title'].'" size="53" maxlength="50"/>';			
				
			$template->set_var('title', $title);												
			// set content and label variables.
			$template->set_var(array(
						'msg_text' 	=> lang($status),						
						'txt_title' 	=> lang('Contact Group List'),						
						'txt_cancel'=> lang('cancel'),
						'txt_add'=> lang('Add'),						
						'txt_rem'=> lang('Remove'),
						'txt_personal_contacts' => lang('Personal Contacts'),
						'txt_contacts_in_list' => lang("Participants"),
						'txt_name'	=> lang('Full Name')));
						
			$template->parse('out_AG', 'addGroup');
			
			return $template->get_var('out_AG');
		}
	}
?>
