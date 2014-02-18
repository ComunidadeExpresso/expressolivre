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


	class ui_data
	{
		var $public_functions = array(
			'data_manager' => true,
                        'advanced_searh_fields' => true 
		);

		var $bo;
		var $so_group;
		var $typeContact;
		var $preferences;

		var $page_info = array(
			'n_cards'          => 20,
			'n_pages'          => false,
			'actual_letter'    => 'A',
			'actual_page'      => 1,
			'actual_entries'   => false,
			'changed'          => false,
			'catalogs'         => false,
			'actual_catalog'   => false
		);

		/*!

			@function ui_data
			@abstract The constructor. Sets the initial parameters and loads
				the data saved in the session
			@author Raphael Derosso Pereira

		*/
		function ui_data()
		{
			$temp = $GLOBALS['phpgw']->session->appsession('ui_data.page_info','contactcenter');
			$temp2 = $GLOBALS['phpgw']->session->appsession('ui_data.all_entries','contactcenter');

			$this->bo = CreateObject('contactcenter.bo_contactcenter');

			if ($temp)
			{
				$this->page_info = $temp;
			}

			if ($temp2)
			{
				$this->all_entries = $temp2;
			}

			if (!$this->page_info['actual_catalog'])
			{
				$catalogs = $this->bo->get_catalog_tree();
				$this->page_info['actual_catalog'] = $catalogs[0];
			}

			$this->page_info['actual_catalog'] = $this->bo->set_catalog($this->page_info['actual_catalog']);


			if($this->page_info['actual_catalog']['class'] == 'bo_group_manager')
				$this -> typeContact = 'groups';
/**rev 104**/
			else if($this->page_info['actual_catalog']['class'] == 'bo_shared_group_manager')
				$this -> typeContact = 'shared_groups';
			else if($this->page_info['actual_catalog']['class'] == 'bo_shared_people_manager')
				$this -> typeContact = 'shared_contacts';
/******/
			else
				$this -> typeContact = 'contacts';
			$this->preferences = $_SESSION['phpgw_info']['user']['preferences']['contactcenter'];
		}

		/*!

			@function index
			@abstract Builds the Main Page
			@author Raphael Derosso Pereira
			@author Jonas Goes

		*/
		function index()
		{
			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			
			$GLOBALS['phpgw']->js->validate_file('venus','table');
			$GLOBALS['phpgw']->js->validate_file('venus','shapes');
			$GLOBALS['phpgw']->js->validate_file('venus','jsStructUtil');
			$GLOBALS['phpgw']->js->validate_file('venus','cssUtil');

//			$GLOBALS['phpgw']->js->set_onload('setTimeout(\'updateCards()\',1000)');
			$GLOBALS['phpgw']->common->phpgw_header();

			$GLOBALS['phpgw']->template->set_file(array('index' => 'index.tpl'));
			$GLOBALS['phpgw']->template->set_var('cc_root_dir', $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/');
			$GLOBALS['phpgw']->template->set_var('cc_msg_not_informed', lang('Not informed'));
			/* Quick Add */
			$GLOBALS['phpgw']->template->set_var('cc_qa_alias',lang('Alias').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_given_names',lang('Given Names').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_family_names',lang('Family Names').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_phone',lang('Phone').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_email',lang('Email').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_save',lang('Save'));
			$GLOBALS['phpgw']->template->set_var('cc_qa_clear',lang('Clear'));
			$GLOBALS['phpgw']->template->set_var('cc_qa_close',lang('Close'));
			/* End Quick Add */

			/* Advanced Search */
			
			$GLOBALS['phpgw']->template->set_var('cc_corporate',lang('Corporate'));
			$GLOBALS['phpgw']->template->set_var('cc_cs_title',lang('Advanced Search'));
			$GLOBALS['phpgw']->template->set_var('cc_catalogues',lang('Catalogues'));
			
			
			
			/* End of Advanced Search*/

			$cc_css_file = $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/styles/cc.css';
			$cc_card_image_file = $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/templates/default/images/card.png';
			$GLOBALS['phpgw']->template->set_var('cc_css',$cc_css_file);
			$GLOBALS['phpgw']->template->set_var('cc_dtree_css', $cc_dtree_file);
			$GLOBALS['phpgw']->template->set_var('cc_card_image',$cc_card_image_file);

			$GLOBALS['phpgw']->template->set_var('cc_personal',lang('Personal'));

/***rev 104***/
			//$GLOBALS['phpgw']->template->set_var('cc_full_add',lang('Full Add'));
			$GLOBALS['phpgw']->template->set_var('cc_full_add_button',lang('Full Add'));
/******/
                        $GLOBALS['phpgw']->template->set_var('cc_full_add_button_sh',lang('Full Add Shared'));
			$GLOBALS['phpgw']->template->set_var('cc_reset',lang('Reset'));

			$GLOBALS['phpgw']->template->set_var('cc_personal_data',lang('Personal Data'));
			$GLOBALS['phpgw']->template->set_var('cc_addresses',lang('Addresses'));
			$GLOBALS['phpgw']->template->set_var('cc_connections',lang('Connections'));
			$GLOBALS['phpgw']->template->set_var('cc_relations',lang('Relations'));

			$GLOBALS['phpgw']->template->set_var('cc_quick_add',lang('Quick Add'));
			$GLOBALS['phpgw']->template->set_var('cc_catalogs',lang('Catalogues'));
			$GLOBALS['phpgw']->template->set_var('cc_group_add',lang('Group Add'));

			/* Panel */
			$GLOBALS['phpgw']->template->set_var('cc_panel_new',lang('New').'...');
			$GLOBALS['phpgw']->template->set_var('cc_panel_search',lang('Search').'...');
			$GLOBALS['phpgw']->template->set_var('cc_panel_table',lang('Table View'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_cards',lang('Cards View'));
			$GLOBALS['phpgw']->template->set_var('cc_btn_import_export', lang('Import/Export'));
			$GLOBALS['phpgw']->template->set_var('cc_btn_new', lang("New..."));

			$GLOBALS['phpgw']->template->set_var('cc_panel_search_found',lang('Showing found entries'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_first_page',lang('First Page'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_previous_page',lang('Previous Page'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_next_page',lang('Next Page'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_last_page',lang('Last Page'));
			$GLOBALS['phpgw']->template->set_var('cc_all',lang('all'));
			/* End Panel */

			/* Messages */
/**rev 104**/
			$GLOBALS['phpgw']->template->set_var('cc_msg_not_allowed',lang('Not Allowed'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_unavailable',lang('Unavailable function'));
/*****/


			$GLOBALS['phpgw']->template->set_var('cc_msg_name_mandatory',lang('Name is mandatory'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_err_invalid_serch',lang('The query should not have the characters {%,?}'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_tel_or_mail_required',lang('Tel or email is required'));

			$GLOBALS['phpgw']->template->set_var('cc_msg_no_cards',lang('No Cards'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_err_no_room',lang('No Room for Cards! Increase your browser area.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_new',lang('New from same Company'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_edit',lang('Edit Contact'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_remove',lang('Remove Contact'));
			$GLOBALS['phpgw']->template->set_var('cc_send_mail',lang('Send Mail'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_group_edit',lang('Edit Group'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_group_remove',lang('Remove Group'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_group_remove_confirm',lang('Confirm Removal of this Group?'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_remove_confirm',lang('Confirm Removal of this Contact?'));
			$GLOBALS['phpgw']->template->set_var('cc_participants',lang('Participants'));
			$GLOBALS['phpgw']->template->set_var('cc_empty',lang('Empty'));
			/* End Messages */

			$GLOBALS['phpgw']->template->set_var('cc_results',lang('Results'));
			$GLOBALS['phpgw']->template->set_var('cc_is_my',lang('Is My'));
			$GLOBALS['phpgw']->template->set_var('cc_ie_personal',lang('Import/Export pesonal contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_btn_search',lang('Search'));
			$GLOBALS['phpgw']->template->set_var('cc_add_relation',lang('Add Relation'));
			$GLOBALS['phpgw']->template->set_var('cc_del_relation',lang('Remove Selected Relations'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_group',lang('Group'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_contact_full',lang('Contact [Full]'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_contact_sh',lang('Contact [Shared]'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_contact_qa',lang('Contact [Quick Add]'));
			$GLOBALS['phpgw']->template->set_var('cc_contact_title',lang('Contact Center').' - '.lang('Contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_window_views_title',lang('Contact Center').' - '.lang('Views'));
			$GLOBALS['phpgw']->template->set_var('phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');

			$GLOBALS['phpgw']->template->set_var('cc_msg_import_contacts', lang('Import Contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_export_contacts', lang('Export Contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_expresso_info_csv', lang('The Expresso supports the contacts importation in the CSV file format.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_choose_file_type', lang('Select the file type'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_outlook_express', lang('Outlook Express'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_outlook2k', lang('Outlook 2000'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_expresso_default', lang('Expresso (default)'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_choose_contacts_file', lang('Select the file that contains the contacts to be imported:'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_close_win', lang('Close'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_close', lang('Close'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_ie_personal', lang('Import / Export personal Contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_import_fail', lang('The importation has failed. Verify the file format.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_importing_contacts', lang('Importing Contacts...'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_import_finished', lang('The importation has finished.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_new', lang(' new'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_failure', lang(' failed'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_exists', lang(' were existent'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_show_more_info', lang('show more info'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_clean', lang('Clean'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_invalid_csv', lang('Select a valid CSV file to import your contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_export_csv', lang('Select the format type that you want to export your contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_automatic', lang('Automatic'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_export_error', lang('An error has occurred while the exportation.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_new_email', lang('New Email'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_main', lang('Main'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_alternative', lang('Alternative'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_select_email', lang('Select E-Mail'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_new_phone', lang('New Telephone'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_home', lang('Home Phone'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_cellphone', lang('Cellphone'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_corporative_cellphone', lang('Corporative Cellphone'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_corporative_fax', lang('Corporative Fax'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_corporative_pager', lang('Corporative Pager'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_work', lang('Work'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_fax', lang('Fax'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_pager', lang('Pager'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_choose_phone', lang('Select the telephone'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_warn_firefox', lang('Warning: Too old version of Firefox'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_firefox_half1', lang('For this application work correctly</u>'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_firefox_half2', lang('it\'s necessary to update your Firefox Browser for a new version (version > 1.5) Install now clicking in the link bellow, or if you want to update it later'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_click_close', lang('click Close'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_install_now', lang('Install Now'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_install_new_firefox', lang('Install a new Firefox version'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_moz_thunderbird', lang('Export as Mozilla Thunderbird CSV.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_outlook_express_pt', lang('Export as Outlook Express (Portuguese) CSV.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_outlook_express_en', lang('Export as Outlook Express (English) CSV.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_outlook_2k_pt', lang('Export as Outlook 2000 (Portuguese) CSV.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_outlook_2k_en', lang('Export as Outlook 2000 (English) CSV.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_outlook_2003', lang('Export as Outlook 2003 CSV.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_expresso_default_csv', lang('Export as Expresso (Default) CSV.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_copy_to_catalog', lang('Copy to personal catalog.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_add_contact_to_group', lang('You did not add any contact for this group.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_fill_field_name', lang('Fill the field Full Name'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_show_extra_detail', lang("Show extra contact's details."));

			$select_groups = '';
			
			$this->so_group = CreateObject('contactcenter.so_group');
			
			$array_groups = $this->so_group->selectGroupsOwnerCanImportContacts($GLOBALS['phpgw_info']['user']['account_id']);
			
			$select_groups = "<select id='id_group'>";//<option value=0>Selecione um grupo...</option></select>";
			$select_groups .= "<option value=0 selected>Nenhum...</option>";
			foreach ($array_groups as $group){
				$select_groups .= "<option value='".$group['id_group']."'>".$group['title']."</option>";						
			} 					
			$select_groups .= "</select>";
			
			$GLOBALS['phpgw']->template->set_var('cc_select_groups',$select_groups);
			
			if($GLOBALS['phpgw_info']['server']['personal_contact_type']=='True'){
				$GLOBALS['phpgw']->template->set_var('cc_contact_type', 'advanced');
			}else{
				$GLOBALS['phpgw']->template->set_var('cc_contact_type', 'default');
			}
                        
                        /*
                         * Monta Contactcenter Busca Avancada
                         */
                        $c = CreateObject('phpgwapi.config','contactcenter');
                        $c->read_repository();
                        $current_config = $c->config_data;

                        $arraySearch = array();
                        foreach ($current_config as $index => $value)
                        {

                            if(substr($index, 0, 24) == 'cc_attribute_searchable_')
                            {
                                if($value == 'true')
                                {
                                    $v = substr($index, 24, strlen($index));
                                    $arraySearch[] = $v;
                                }
                            }
                        }
                        $advanceSearchArray = array();
                        foreach ($arraySearch as $value)
                        {
                            foreach ($current_config as $index => $value2)
                            {
                                if($value == substr($index, 22, strlen($index)) && substr($index, 0, 22) == 'cc_attribute_ldapname_')
                                {
                                    foreach ($current_config as $index2 => $value3)
                                    {
                                        if($value == substr($index2, 18, strlen($index2)) && substr($index2, 0, 18) == 'cc_attribute_name_')
                                             $advanceSearchArray[$value2] =  $value3;
                                    }
                                }

                            }
                        }

                        $advanceSearch = array();
                        foreach ($advanceSearchArray as $index => $value)
                            $advanceSearch[] ='"'.$index.'":"'.$value.'"';

                        $advancedFields =  "{".implode(',',$advanceSearch)."}";


                        $GLOBALS['phpgw']->template->set_var('cc_config_advanced_search', $advancedFields);

                        
                        
			$GLOBALS['phpgw']->template->parse('out','index');

			$api = CreateObject('contactcenter.ui_api');
			$main = $api->get_full_add();
			$main .= $api->get_search_obj();
			$main .= $api->get_quick_add_plugin();
			$main .= $api->get_add_group();
			$main .= $api->get_contact_details();
			$main .= $GLOBALS['phpgw']->template->get_var('out');

			echo $main;
		}


		/*!

			@function data_manager
			@abstract Calls the right method and passes to it the right
				parameters
			@author Raphael Derosso Pereira

		*/
		function data_manager()
		{
			$GLOBALS['phpgw']->template->set_file(array('index' => 'index.tpl'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_not_informed', lang('Not informed'));
			
			switch($_GET['method'])
			{
				/* Cards Methods */
				case 'set_n_cards':
					return $this->set_n_cards((int)$_GET['ncards']);

				case 'get_cards_data':

					$ids = false;
					// To support ldap catalogs using accentuation
					if ($_POST['letter'] == 'search' && isset($_POST['data']))
					{
						$ids = utf8_decode($this->search($_POST['data']));
					}

					if(isset($_SESSION['ids']))
						$ids = $_SESSION['ids'];

					return $this->get_cards_data($_POST['letter'], $_POST['page'], $ids);

				case 'get_cards_data_get':
					return $this->get_cards_data($_GET['letter'], $_GET['page'], unserialize(str_replace('\\"','"',$_GET['ids'])));


				case 'get_photo':
					return $this->get_photo($_GET['id']);

				case 'get_init_values':
					echo serialize(array("visible_all_ldap" => $this->get_visible_all_ldap(), "preferences"	=> $this->preferences));
					return;

				/* Catalog Methods */
				case 'set_catalog':
					return $this->set_catalog($_GET['catalog']);

				case 'get_catalog_tree':
					echo serialize($this->get_catalog_tree($_GET['level']));
					return;

				case 'get_actual_catalog':
					echo serialize($this->get_actual_catalog());
					return;

				case 'get_catalog_participants_list':
					echo serialize($this->get_catalog_participants_list($_POST['id']));
					return;
/**rev 104**/
				case 'get_catalog_participants_group':
					echo serialize($this->get_catalog_participants_group($_POST['id']));
					return;
/***/
				case 'get_catalog_add_contact':
					// To support ldap catalogs with accentuation
					echo serialize($this->get_catalog_add_contact($_POST['id']));
					return;

				/* Full Add Methods */
				case 'get_full_data':
					//return $this->get_full_data($_GET['id']);
/**rev 104**/
					return $this->get_full_data($_GET['id'],$_GET['catalog']);
/****/

				case 'get_group':
					return $this->get_group_data($_GET['id'],isset($_GET['shared_from'])?$_GET['shared_from']:null);

				case 'get_contact_full_add_const':
					return $this->get_contact_full_add_const();

				case 'post_full_add':
					return $this->post_full_add();

				case 'post_full_add_shared' :
					return $this->post_full_add_shared();

				case 'post_photo':
					return $this->post_photo((int) $_GET['id'] ? (int) $_GET['id'] : '_new_');

				case 'get_states':
					return $this->get_states($_GET['country']);

				case 'get_cities':
					return $this->get_cities($_GET['country'], $_GET['state'] ? $_GET['state'] : null);


				/* Other Methods */
				case 'quick_add':
					return $this->quick_add($_POST['add']);

				case 'add_group':
					return $this->add_group($_POST['add']);

				case 'remove_entry':
					return $this->remove_entry((int)$_GET['remove']);

				case 'remove_all_entries':
					return $this->remove_all_entries();

				case 'remove_group':

					return $this->remove_group((int)$_GET['remove']);

				case 'search':
					$ids = false;
					$ids = $this->search($_GET['data']);
					$data = unserialize($_GET['data']);
                                        $dontPaginate = false;
                                        if (isset($data['ccAddGroup'])){
                                            $dontPaginate = true;
                                        }
					return $this->get_cards_data('search', '1', $ids, $dontPaginate);

				case 'email_win':
					$GLOBALS['phpgw']->common->phpgw_header();
					$api = CreateObject('contactcenter.ui_api');
					$win = $api->get_email_win();
					$win .= $api->get_quick_add_plugin();
					$win .= '<input id="QAbutton" type="button" value="QuickAdd" />'
						.'<br><input type="button" value="EmailWin" onclick="ccEmailWin.open()" />'
						.'<script type="text/javascript">'
						.'	ccQuickAdd.associateAsButton(Element("QAbutton"));'
						.'</script>';
					echo $win;
					return;

				/* Information Gathering */
				case 'get_multiple_entries':
					echo serialize($this->get_multiple_entries(str_replace('\\"','"',$_POST['data'])));
					return;

				case 'get_all_entries':
					echo serialize($this->get_all_entries(str_replace('\\"','"',$_POST['data'])));
					return;

				case 'import_contacts':
					return $this->import_contacts($_GET['typeImport'],$_GET['id_group']);

				case 'export_contacts':
					return $this->export_contacts($_POST['typeExport']);
				
				case 'get_qtds_compartilhado':
					return $this->get_qtds_compartilhado();
				case 'get_list_owners_perms_add':
					echo $this->get_list_owners_perms_add();
					return;
				case 'get_contact_details':
					return $this->get_contact_details($_GET['id']);
			}
		}

		/*!

			@function set_n_cards
			@abstract Informs the class the number of cards the page can show
			@author Raphael Derosso Pereira

			@param integer $n_cards The number of cards

		*/
		function set_n_cards($n_cards)
		{
			if (is_int($n_cards))
			{
				$this->page_info['n_cards'] = $n_cards;
				echo 1;
			}

			$this->save_session();
		}

		/*!

			@function set_catalog
			@abstract Sets the current catalog selected by the user
			@author Raphael Derosso Pereira

			@param string $id_catalog The sequence of IDs to reach the catalog
				separated by commas

		*/
		function set_catalog($id_catalog, $echo=true)
		{
			$id_catalog = str_replace('\\"', '"', $id_catalog);
			$temp = $this->bo->set_catalog($id_catalog);

                        if(!$this->bo->catalog->src_info) {
                            $ldap = CreateObject('contactcenter.bo_ldap_manager');
                            $this->bo->catalog->src_info = $ldap->srcs[1];
                        }

                        $resetCC_actual_letter = 0;
                        if($this->bo->catalog->src_info['visible'] == "false")
                        {
                            $resetCC_actual_letter = 1;
                        }

			if ($temp)
			{
                                unset($this->page_info['actual_letter']);
				$this->page_info['changed'] = true;
				$this->page_info['actual_entries'] = false;
				$this->page_info['actual_catalog'] = $temp;
				$this->save_session();

				$catalog_info = $this->bo->get_branch_by_level($this->bo->catalog_level[0]);

				if ($catalog_info['class'] === 'bo_global_ldap_catalog' ||
				    $catalog_info['class'] === 'bo_catalog_group_catalog')
				{
					$perms = 1;
				}
				else
				{
					$perms = 15;
				}

                                if ($echo)
                                {
                                    echo serialize(array(
                                            'status' => 'ok',
                                            'catalog' => $catalog_info['class'],
                                            'external' => $catalog_info['external']?true:false,
                                            'resetCC_actual_letter' => $resetCC_actual_letter,
                                            'perms'  => $perms
                                    ));
                                }
                                else
                                    {
                                        return serialize(array(
                                            'status' => 'ok',
                                            'catalog' => $catalog_info['class'],
                                            'external' => $catalog_info['external']?true:false,
                                            'resetCC_actual_letter' => $resetCC_actual_letter,
                                            'perms'  => $perms
                                        ));
                                    }

				return;
			}

			echo serialize(array(
				'status' => 'ok',
                                'resetCC_actual_letter' => $resetCC_actual_letter,
				'perms'  => 0
			));
		}


		/*!

			@function get_catalog_tree
			@abstract Returns the JS serialized array to used as the tree
				level
			@author Raphael Derosso Pereira
            @author Mário César Kolling (error messages and timeout)

			@param (string) $level The level to be taken

		*/
		function get_catalog_tree($level)
		{
			if ($level === '0')
			{
				$folderImageDir = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/dftree/images/';

				$parent = '0';

				if (!($tree = $this->bo->get_catalog_tree($level)))
				{
					return array(
						'msg'    => lang('Couldn\'t get the Catalogue Tree. Please contact the Administrator.'),
						'status' => 'fatal'
					);
				}
			}
			else
			{
				$last_dot = strrpos($level,'.');
				$parent = substr($level, 0, $last_dot);
				$child = substr($level, $last_dot+1, strlen($level));
				if (!($tree[$child] = $this->bo->get_catalog_tree($level)))
				{
					return array(
						'msg'    => lang('Couldn\'t get the Catalogue Tree. Please contact the Administrator.'),
						'status' => 'fatal'
					);
				}
				// Deals with timeout and returns the generated message to the browser
				else if (!empty($tree[$child]['timeout']) && !empty($tree[$child]['msg']))
				{
					$tmp = array(
						'msg'    => $tree[$child]['msg'],
						'status' => 'fatal'
					);
					unset($tree[$child]);
					return $tmp;
				}
			}

			$folderImageDir = $GLOBALS['phpgw']->common->image('contactcenter','globalcatalog-mini.png');
			$folderImageDir = substr($folderImageDir, 0, strpos($folderImageDir, 'globalcatalog-mini.png'));

			// Deals with error messages from the server and returns them to the browser
			if ($tree['msg'])
			{
				$msg = $tree['msg'];
				unset($tree['msg']);
			}

			$tree_js = $this->convert_tree($tree, $folderImageDir, $parent);

			// Return status = ok, or else return generated message to the browser
			if (!$msg)
			{
				return array(
					'data' => $tree_js,
					'msg'  => lang('Catalog Tree Successfully taken!'),
					'status' => 'ok'
				);
			}
			else
			{
				return array(
					'data' => $tree_js,
					'msg'  => $msg,
					'status' => 'error'
				);
			}
		}

		/*!

			@function get_actual_catalog
			@abstract Returns the actual selected Catalog
			@author Raphael Derosso Pereira

		*/
		function get_actual_catalog()
		{
			$level = $this->bo->get_level_by_branch($this->bo->get_actual_catalog(), $this->bo->tree['branches'], '0');

			if ($level)
			{
				return array(
					'status' => 'ok',
					'data'   => $level
				);
			}

			return array(
				'status' => 'fatal',
				'msg'    => lang('Couldn\'t get the actual catalog.'),
			);
		}

		function get_qtds_compartilhado() {
			$so_contact = CreateObject('contactcenter.so_contact',  $GLOBALS['phpgw_info']['user']['account_id']);
            $relacionados = $so_contact->get_relations();

			$perms_relacao = array();

        	foreach($relacionados as $uid_relacionado => $tipo_relacionamento) {
				$aclTemp = CreateObject("phpgwapi.acl",$uid_relacionado);
            	$aclTemp->read();
                $perms_relacao[$uid_relacionado] = $aclTemp->get_specific_rights($GLOBALS['phpgw_info']['user']['account_id'],'contactcenter'); //Preciso verificar as permissões que o contato relacionado deu para o atual
			}

			$validos = array();
			$count = 0;
			foreach($perms_relacao as $uid_relacionado => $val){
				if ($perms_relacao[$uid_relacionado]&2)
				{
					$validos[$uid_relacionado] = $perms_relacao[$uid_relacionado];
                    ++$count;
				}
			}
			echo serialize(array(0=>$count));
		}

		/*!

			@function get_cards_data
			@abstract Returns the information that is placed on the cards
			@author Raphael Derosso Pereira

			@param string $letter The first letter to be searched
			@param (int)  $page The page to be taken
			@param (str)  $ids The ids to be taken in case of search

			TODO: This function is not well done. It must be rewritten
				using the new array 'msg','status','data' schema.
		*/
		function get_cards_data($letter, $page, $ids, $dontPaginate = false)
		{
			if( $ids )
				$_SESSION['ids'] = $ids;

			// It's an external catalog?
			$external = $this->bo->is_external($this->page_info['actual_catalog']);
			//echo $page."\n";
			if ($letter !== 'search' and ($letter != $this->page_info['actual_letter'] or
			    ($letter == $this->page_info['actual_letter'] and $page == $this->page_info['actual_page']) or
			    $this->page_info['changed']))
			{
				unset($ids);
				$this->page_info['changed'] = false;

				switch ($this->page_info['actual_catalog']['class'])
				{
/**rev 104**/
					case 'bo_shared_people_manager':
/****/
					case 'bo_people_catalog':
						$field_name = 'id_contact';

						if ($letter !== 'number')
						{
							$find_restric[0] = array(
								0 => array(
									'field' => 'contact.names_ordered',
									'type'  => 'iLIKE',
									'value' => $letter !== 'all' ? $letter.'%' : '%'
/**rev 104**/
	/*							),
								1 => array(
									'field' => 'contact.id_owner',
									'type'  => '=',
									'value' => $GLOBALS['phpgw_info']['user']['account_id']
	*/
								)
							);

							//Tratamento de permissão de escrita no compartilhamento de catalogo
                                			$so_contact = CreateObject('contactcenter.so_contact',  $GLOBALS['phpgw_info']['user']['account_id']);
                                			$relacionados = $so_contact->get_relations();

							$perms_relacao = array();

                        				foreach($relacionados as $uid_relacionado => $tipo_relacionamento) {
								$aclTemp = CreateObject("phpgwapi.acl",$uid_relacionado);
                                				$aclTemp->read();
                                				$perms_relacao[$uid_relacionado] = $aclTemp->get_specific_rights($GLOBALS['phpgw_info']['user']['account_id'],'contactcenter'); //Preciso verificar as permissões que o contato relacionado deu para o atual
							}

							$validos = array();
							$count = 0;
							foreach($perms_relacao as $uid_relacionado => $val){
								if ($perms_relacao[$uid_relacionado]&2)
								{
									$validos[$uid_relacionado] = $perms_relacao[$uid_relacionado];
                                    ++$count;
								}
							}
							$prop_names = array();
							if($validos) {
                	                        		$filtro = "(|";
                        	                		foreach($validos as $i => $prop) {
                                	                		$filtro .= "(uidNumber=".$i.")";
                                        			}
                                       	 			$filtro .= ")";

                                        			if(!$this->bo->catalog->src_info) {
                                                			$ldaps = CreateObject('contactcenter.bo_ldap_manager');
                                                			$this->bo->catalog->src_info = $ldaps->srcs[1];
                                        			}
                                        			$s = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], false);
                                        			$n=$this->bo->catalog->src_info['dn'];
                                        			$apenasThese = array("cn","uidnumber","uid");
                                        			$r = ldap_search($s,$n, $filtro,$apenasThese);
                                        			$infos = ldap_get_entries($s, $r);
                                        			ldap_close($s);
                                        			for($z = 0; $z < $infos['count']; ++$z) {
                                                			$prop_names[$infos[$z]['uidnumber'][0]] = array("cn" => $infos[$z]['cn'][0], "uid" => $infos[$z]['uid'][0]);
                                        			}
                                			}
							//--------------------------------------------------------------------------------
							if($this->page_info['actual_catalog']['class'] == 'bo_people_catalog')
							{
								$find_restric[0][1] = array(
										'field' => 'contact.id_owner',
										'type'  => '=',
										'value' => $GLOBALS['phpgw_info']['user']['account_id']
								);
							}
/****/

						}
						else
						{
							$find_restric[0] = array(
								0 => array(
									'type'  => 'branch',
									'value' => 'OR',
									'sub_branch' => array(
										0 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '0%'
										),
										1 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '1%'
										),
										2 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '2%'
										),
										3 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '3%'
										),
										4 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '4%'
										),
										5 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '5%'
										),
										6 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '6%'
										),
										7 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '7%'
										),
										8 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '8%'
										),
										9 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '9%'
										),
									),
/**rev 104**/
/*								),
								1 => array(
									'field' => 'contact.id_owner',
									'type'  => '=',
									'value' => $GLOBALS['phpgw_info']['user']['account_id']
								),
*/
								)
							);
						}

						if($this->page_info['actual_catalog']['class'] == 'bo_people_catalog'){								
							$find_restric[0][1]	= array(
								'field' => 'contact.id_owner',
								'type'  => '=',
								'value' => $GLOBALS['phpgw_info']['user']['account_id']
							);
						}
/*****/

						$find_field[0] = array('contact.id_contact','contact.names_ordered');

						$find_other[0] = array(
/**rev 104**/
							//'offset' => (($page-1)*$this->page_info['n_cards']),
							//'limit'  => $this->page_info['n_cards'],
/*****/
							'order'  => 'contact.names_ordered'
						);

						break;

                                        case 'bo_catalog_group_catalog':
					case 'bo_global_ldap_catalog':

						$field_name = 'id_contact';

						if ($letter !== 'number')
						{
							$find_restric[0] = array(
								0 => array(
									'field' => 'contact.names_ordered',
									'type'  => 'iLIKE',
									'value' => $letter !== 'all' ? $letter.'%' : '%'
								),
								/*
								 * Restrict the returned contacts in a "first letter" search
/**rev 104
								 * to objectClass = phpgwAccount, must have attibute phpgwAccountStatus,
								 * phpgwAccountVisible != -1
								 */
								1 => array(
									'field' => 'contact.object_class',
									'type'  => '=',
									'value' => 'phpgwAccount'
/**rev 104**/
								//),/*
								),
/****/
								2 => array(
									'field' => 'contact.account_status',
									'type'  => 'iLIKE',
									'value' => '%'
/**rev 104**/
								//),*/
								//2 => array(
								),
								3 => array(
/*****/
									'field' => 'contact.account_visible',
									'type'  => '!=',
									'value' => '-1'
/**rev 104**/
	/*							),
								3 => array(
									'field' => 'contact.object_class',
									'type'  => '=',
									'value' => 'inetOrgPerson'
								),
	*/
								)
/*****/

							);
							// If not external catalog get only phpgwAccountType = u ou l
							if (!$external)
							{
								$find_restric[0][5] =  array(
										'type'  => 'branch',
										'value' => 'OR',
										'sub_branch' => array(
											0 => array(
											'field' => 'contact.account_type',
											'type'  => '=',
											'value' => 'u'
											),
											1 => array(
											'field' => 'contact.account_type',
											'type'  => '=',
											'value' => 'i'
/**rev 104**/
											),
											2 => array(
											'field' => 'contact.account_type',
											'type'  => '=',
/****/
											'value' => 'l'
/**rev 104**/
											),
											3 => array(
											'field' => 'contact.account_type',
											'type'  => '=',
											'value' => 'g'
/***/
											),
											4 => array(
											'field' => 'contact.account_type',
											'type'  => '=',
											'value' => 's'
											)
										)
									);
							}
						}
						else
						{
							$find_restric[0] = array(
								/*
								 * Restrict the returned contacts in a "first number" search
/**rev 104
								 * to objectClass = phpgwAccount, must have attibute phpgwAccountStatus,
								 * phpgwAccountVisible != -1
								 */
								0 => array(
									'field' => 'contact.object_class',
									'type'  => '=',
									'value' => 'phpgwAccount'
/**rev 104**/
								//),/*
								),
/****/
								1 => array(
									'field' => 'contact.account_status',
									'type'  => 'iLIKE',
									'value' => '%'
/**rev 104**/
								//),*/
								//1 => array(
								),
								2 => array(
/*****/
									'field' => 'contact.account_visible',
									'type'  => '!=',
									'value' => '-1'
								),
/**rev 104**/
	/*							2 => array(
									'field' => 'contact.object_class',
									'type'  => '=',
									'value' => 'inetOrgPerson'
								),
	*/
/****/
								3 => array(
									'type'  => 'branch',
									'value' => 'OR',
									'sub_branch' => array(
										0 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '0%'
										),
										1 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '1%'
										),
										2 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '2%'
										),
										3 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '3%'
										),
										4 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '4%'
										),
										5 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '5%'
										),
										6 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '6%'
										),
										7 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '7%'
										),
										8 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '8%'
										),
										9 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '9%'
										),
									),
								),
							);
							// If not external catalog get only phpgwAccountType = u ou l
							if (!$external)
							{
								$find_restric[0][5] =  array(
									'type'  => 'branch',
									'value' => 'OR',
									'sub_branch' => array(
										0 => array(
										'field' => 'contact.account_type',
										'type'  => '=',
										'value' => 'u'
										),
										1 => array(
										'field' => 'contact.account_type',
										'type'  => '=',
/**rev 104**/
										//'value' => 'g'
										//);
										//1 => array(
										'value' => 'i'
										),
										2 => array(
/****/
										'field' => 'contact.account_type',
										'type'  => '=',
										'value' => 'l'
/**rev 104**/
										),
										3 => array(
										'field' => 'contact.account_type',
										'type'  => '=',
										'value' => 'g'
										),
/****/
										4 => array(
										'field' => 'contact.account_type',
										'type'  => '=',
										'value' => 's'
										)
									)
								);
							}
						}

						if (!$external)
						{
							// Get only this attributes: dn, cn, phpgwAccountType, objectClass, phpgwAccountStatus, phpghAccountVisible
							// for non-external catalogs, used to restrict the attributes used in filters
							$find_field[0] = array('contact.id_contact','contact.names_ordered','contact.account_type',
								'contact.object_class','contact.account_visible');
						}
						else
						{
							// Get only this attributes: dn, cn for external catalogs,
							// used to restrict the attributes used in filters
							$find_field[0] = array('contact.id_contact','contact.names_ordered');
						}

						$find_other[0] = array(
							//'offset' => (($page-1)*$this->page_info['n_cards']),
							//'limit'  => $this->page_info['n_cards'],
							'order'  => 'contact.names_ordered'
						);

						break;

					case 'bo_company_manager':
						$field_name = 'id_company';

						$find_field[0] = array('company.id_company','company.company_name');

						$find_other[0] = array(
							//'offset' => (($page-1)*$this->page_info['n_cards']),
							//'limit'  => $this->page_info['n_cards'],
							'order'  => 'company.company_name'
						);

						$find_restric[0] = array(
							0 => array(
								'field' => 'company.company_name',
								'type'  => 'iLIKE',
								'value' => $letter !== 'all' ? $letter.'%' : '%'
							)
						);

						break;

					case 'bo_group_manager':
/**rev 104**/
					case 'bo_shared_group_manager':
/****/

						$field_name = 'id_group';

						if ($letter !== 'number')	{

							$find_restric[0] = array(
								0 => array(
									'field' => 'group.title',
									'type'  => 'iLIKE',
									'value' => $letter !== 'all' ? $letter.'%' : '%'
								)
							);
						}
						 else {

							$find_restric[0] = array(
								0 => array(
											'field' => 'group.title',
											'type'  => 'LIKE',
											'value' => '0%'
								)
							);
						}
/**rev 104**/
						if($this->page_info['actual_catalog']['class'] == 'bo_group_manager'){
/****/
							array_push($find_restric[0],  array(
											'field' => 'group.owner',
											'type'  => '=',
											'value' => $GLOBALS['phpgw_info']['user']['account_id']
									)
							);
						}

						$find_field[0] = array('group.id_group','group.title','group.short_name');
						$find_other[0] = array(
							'order'  => 'group.title'
						);
						break;

					/*case 'bo_catalog_group_catalog':
						$this->page_info['actual_entries'] = false;

						$this->page_info['actual_letter'] = $letter;
						$this->page_info['actual_page'] = 0;

						$this->save_session();
						$final[0] = 0;
						$final[1] = $this->typeContact;
						echo serialize($final);
						return;*/

				}

				if(!$this->bo->catalog->src_info) {
				    $ldaps = CreateObject('contactcenter.bo_ldap_manager');
				    $this->bo->catalog->src_info = $ldaps->srcs[1];
				}
				$recursive = $this->bo->catalog->src_info['recursive'];

				$result = $this->bo->find($find_field[0],$find_restric[0],$find_other[0],false,($recursive == "true") ? true : '');
				$n_entries = count($result);

				if ($n_entries)
				{
					//echo 'N_entries: '.$n_entries.'<br>';
					$this->page_info['n_pages'] = $dontPaginate ? 1 : ceil($n_entries/$this->page_info['n_cards']);
				}
				else
				{
					$this->page_info['n_pages'] = 0;
				}

				if (!$result)
				{
					$this->page_info['actual_entries'] = false;

					$this->page_info['actual_letter'] = $letter;
					$this->page_info['actual_page'] = 0;

					$this->save_session();
					$final[0] = 0;
					$final[1] = $this->typeContact;
					echo serialize($final);
					return;
				}
				else
				{
					unset($this->page_info['actual_entries']);
					if(is_array($result)) {
						foreach ($result as $id => $value)
						{
							if($this->page_info['actual_catalog']['class'] != 'bo_shared_people_manager' && $this->page_info['actual_catalog']['class'] != 'bo_shared_group_manager')
								$this->page_info['actual_entries'][] = $value[$field_name];
							else
								$this->page_info['actual_entries'][] = array(0=>$value[$field_name],1=>$value['perms'],2=>$value['owner']);
						}
					}
				}
			}
			else if ($letter === 'search')
			{
				//if (!$ids and $this->page_info['actual_letter'] !== 'search')
				if (!$ids)
				{
/**rev 104**/
					//error_log('!$ids e $this->page_info[\'actual_letter\'] != search');
/*****/
					$this->page_info['actual_entries'] = false;

					$this->page_info['actual_letter'] = $letter;
					$this->page_info['actual_page'] = 0;

					$this->save_session();
					$final[0] = 0;
					$final[1] = $this -> typeContact;
					echo serialize($final);
					return;
				}
				else if ($ids['error'])
				{
					$this->page_info['actual_entries'] = false;
					$this->page_info['actual_letter'] = $letter;
					$this->page_info['actual_page'] = 0;

					$this->save_session();
					$final[0] = 0;
					$final[1] = $this -> typeContact;
					$final['error'] = $ids['error'];
					echo serialize($final);
					return;
				}
				else if ($ids)
				{
					$this->page_info['actual_letter']  = $letter;
					$this->page_info['actual_entries'] = $ids;
					$this->page_info['n_pages'] = $dontPaginate ? 1 : ceil(count($this->page_info['actual_entries'])/$this->page_info['n_cards']);
				}
			}
			else
			{
				unset($ids);
			}

			if ($this->page_info['actual_entries'])
			{
				if ($page >= $this->page_info['n_pages'])
				{
					$page = $this->page_info['n_pages'];
				}

				$final = array(
					0 => (int)$this->page_info['n_pages'],
					1 => (int)$page,
					2 => array(
						0 => 'cc_company',
						1 => 'cc_name',
						2 => 'cc_title',
						3 => 'cc_phone',
						4 => 'cc_mail',
						5 => 'cc_alias',
						6 => 'cc_id',
						7 => 'cc_forwarding_address',
						8 => 'cc_empNumber',
						9 => 'cc_department',
						10 => 'cc_mobile'
					)
				);

				//verifica se esta habilitado a opcao de exibir os detalhes extras
				$objconfig = CreateObject('phpgwapi.config', 'contactcenter');
				$config = $objconfig->read_repository();
				
				//echo 'Page: '.$page.'<br>';
				$n_entries = count($this->page_info['actual_entries']);
                                $n_cards = $dontPaginate? $n_entries : $this->page_info['n_cards'];
				$id_i = (($page-1)*$n_cards);
				$id_f = $id_i + $n_cards;

				//echo 'ID_I: '.$id_i.'<br>';
				//echo 'ID_F: '.$id_f.'<br>';
				///---------------- Correção Temporária PHP5 -----------------------///
				$ids = array();
/**rev 104**/
				$perms = array();
				$owners = array();
/****/
				$array_temp = array();

				foreach($this->page_info['actual_entries'] as $key=>$tmp){
					$array_temp[] = $tmp;
				}

				for($i = $id_i; $i < $id_f and $i < $n_entries; ++$i)
				{
/**rev 104**/
					if($this->page_info['actual_catalog']['class'] != 'bo_shared_people_manager' && $this->page_info['actual_catalog']['class'] != 'bo_shared_group_manager')
					{
/****/
						$ids[] = $array_temp[$i];
/**rev 104**/
					}else {
						$ids[] = $array_temp[$i][0];
						$perms[] = $array_temp[$i][1];
						$owners[] = $array_temp[$i][2];
/****/
					}
				}

/**rev 104**/
				// Carrega o nome completo dos donos dos objetos (contatos e grupos);
				$owner_names = array();

				if($owners) {
					$filter = "(|";
					foreach($owners as $i => $owner) {
						$filter .= "(uidNumber=".$owner.")";
					}
					$filter .= ")";

					if(!$this->bo->catalog->src_info) {
						$ldap = CreateObject('contactcenter.bo_ldap_manager');
						$this->bo->catalog->src_info = $ldap->srcs[1];
					}
					$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], false);
					$dn=$this->bo->catalog->src_info['dn'];
					$justThese = array("cn","uidnumber","uid");
					$sr = ldap_search($ds,$dn, $filter,$justThese);
					$info = ldap_get_entries($ds, $sr);
					ldap_close($ds);
					for($z = 0; $z < $info['count']; ++$z) {
						$owner_names[$info[$z]['uidnumber'][0]] = array("cn" => $info[$z]['cn'][0], "uid" => $info[$z]['uid'][0]);
					}

					//SAMPLE : dc=teste, dc=diretorio, dc=empresa
					if(!count($owner_names)){
						$dns = explode(',', $dn);
						for($i = count($dns); $i > 0; $i--){
							$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], false);
							//verifica se veio a base como parametro, remoção de erros gerados por paramentro null
							//if(isset($dns[$i])){
								$sr = ldap_search($ds,$dns[$i], $filter,$justThese);
								$info = ldap_get_entries($ds, $sr);
								ldap_close($ds);
								for($z = 0; $z < $info['count']; ++$z) {
									$owner_names[$info[$z]['uidnumber'][0]] = array("cn" => $info[$z]['cn'][0], "uid" => $info[$z]['uid'][0]);
								}	
								if(count($owner_names)){
									$i = 0;
								}
						//	}
						}
					}
				}

/*****/


				/// Original
				//for($i = $id_i; $i < $id_f and $i < $n_entries; ++$i)
				//{
				//	$ids[] = $this->page_info['actual_entries'][$i];
				//}
				///

				$fields = $this->bo->catalog->get_fields(false);
/**rev 104**/
				//if( $this->typeContact == 'groups') {
				if( $this->typeContact == 'groups' || $this->typeContact == 'shared_groups') {
/****/
					$final = array(
						0 => (int)$this->page_info['n_pages'],
						1 => (int)$page,
						2 => array(
							0 => 'cc_title',
							1 => 'cc_short_name',
							2 => 'cc_id',
							3 => 'cc_contacts'
						)
					);

					$groups = $this->bo->catalog->get_multiple_entries($ids,$fields);

					$i = 0;
					// contatos do grupo
					$boGroups = CreateObject('contactcenter.bo_group');
					$contacts = array();
					foreach($groups as $group)		{

						$final[3][$i][0] = $group['title'] ? $group['title'] : 'none';
						$final[3][$i][1] = $group['short_name'] ? $group['short_name'] : 'none';
						$final[3][$i][2] = $group['id_group'] ? $group['id_group'] : 'none';
						$contacts = $boGroups -> get_contacts_by_group($group['id_group']);
						$final[3][$i][3] = $contacts;
						$final[3][$i][4] = $perms[$i];
						if($this->typeContact == 'shared_groups'){
							$final[3][$i][5] = lang('Shared').": ".$owner_names[$owners[$i]]['cn'];
							$final[3][$i][6] = $owner_names[$owners[$i]]['uid'];
							$final[3][$i][7] = $owners[$i]; //uidNumber
						}
                        ++$i;
					}

					$this->page_info['actual_letter'] = $letter;
					$this->page_info['actual_page'] = $page;


					$lnk_compose = "location.href=('../expressoMail1_2/index.php?to=";

					$final[5] = '<span class="link"  onclick="'.$lnk_compose;
/**rev 104**/
					//$final[10] = 'groups';
					$final[10] = $this->typeContact;
/******/
					$this->save_session();
					echo serialize($final);
					return;
				}
/**rev 104**/
				$final[10] = $this -> typeContact;
/*****/

				$fields['photo'] = true;
				$fields['names_ordered'] = true;
				$fields['alias'] = true;
				$fields['account_type'] = true;
				$fields['companies'] = 'default';
				$fields['connections'] = 'default';

/**rev 104**/
				// ?aqui alterar a chamada desse método para receber o base dn?
				//$contacts = &$this->bo->catalog->get_multiple_entries($ids,$fields);

				// ?aqui alterar a chamada desse método para receber o base dn?
				if($external)
				{
					$contacts = $this->bo->catalog->get_multiple_entries($ids,$fields,false,true);
				} else{
					$contacts = $this->bo->catalog->get_multiple_entries($ids,$fields);
				}
/*******/
				if (!is_array($contacts) or !count($contacts))
				{
					$final[0] = 0;
					$final[1] = $this -> typeContact;
					echo serialize($final);
					return;
				}

				$i = 0;
				if (!is_array($this->preferences))
				{
					$this->preferences['personCardEmail'] = 1;
					$this->preferences['personCardPhone'] = 2;
				}
				foreach($contacts as $index => $contact)
				{
					/*
					 * TODO: Os timeouts de conexão foram retirados, ver se será necessário retornar essa funcionalidade, e,
					 * neste caso, terminar a implementação das mensagens de retorno.
					 */
					if ($index !== 'error'){
						$final[3][$i][0] = $contact['companies']['company1']['company_name']?$contact['companies']['company1']['company_name']:'none';

/**rev 104**/
						//$final[3][$i][1] = $contact['names_ordered'] ? $contact['names_ordered'] : 'none';

						if($this->page_info['actual_catalog']['class']!='bo_global_ldap_catalog'){
							$final[3][$i][1] = $contact['names_ordered'] ? urldecode(is_array($contact['names_ordered']) ? $contact['names_ordered'][0] : $contact['names_ordered'])  : 'none';
						}
						else {
							$contact['names_ordered'][0] = urldecode($contact['names_ordered'][0]);
							$final[3][$i][1] = $contact['names_ordered'] ? $contact['names_ordered']  : 'none';
						}

/********/

						$final[3][$i][2] = $contact['companies']['company1']['title']? $contact['companies']['company1']['title']:'none';

						//Para exibir a matricula do empregado
						$final[3][$i][8] = $contact['companies']['company1']['empNumber']?$contact['companies']['company1']['empNumber']:'none';
						//Para exibir o setor/lotacao do empregado
						$final[3][$i][9] = $contact['companies']['company1']['department']?$contact['companies']['company1']['department']:'none';
						//Para exibir o celular empresarial do empregado
						$final[3][$i][10] = $contact['companies']['company1']['celPhone']?$contact['companies']['company1']['celPhone']:'none';

						//Para exibir o celular empresarial do empregado
						if ($this->preferences['voip_enabled'] && !$external && $final[3][$i][10] != 'none')
                                                            $final[3][$i][10] = "<a title=\"".lang("Call Mobile")."\" href=\"#\" onclick=\"connectVoip('".$final[3][$i][10]."', 'mob')\">".$final[3][$i][10]."</a>";

						if ($contact['connections'])
						{
							$default_email_found = false;
							$default_phone_found = false;
							foreach($contact['connections'] as $conn_info)
							{
								if ($conn_info['id_type'] == $this->preferences['personCardEmail'] and !$default_email_found)
								{
									if ($conn_info['connection_is_default'])
									{
										$default_email_found = true;
									}
									$final[3][$i][4] = $conn_info['connection_value'] ? $conn_info['connection_value'] : 'none';
                                                                        $final[3][$i][13] = $conn_info['id_connection'];
								}
								else if ($conn_info['id_type'] == $this->preferences['personCardPhone'] and !$default_phone_found)
								{
									if ($conn_info['connection_is_default'])
									{
										$default_phone_found = true;
									}
									if($final[3][$i][4] == 'none' or !$final[3][$i][4]){
										$final[3][$i][4] = lang('Not informed');
										$final[3][$i][13] = $conn_info['id_connection'];
									}
/**rev 104**/
									//if ($_SESSION['phpgw_info']['user']['preferences']['contactcenter']['voip_enabled'] && !$external){
									//	$conn_info['connection_value'] = "<a title=\"".lang("Call Extension")."\" href=\"#\" onclick=\"connectVoip('".$conn_info['connection_value']."', 'ramal')\">".$conn_info['connection_value']."</a>";

									if (!($this->preferences['telephone_number'] == $conn_info['connection_value']) && $this->preferences['contactcenter']['voip_enabled'] && $conn_info['connection_value'] && preg_match('/^\([0-9]{2}\)[0-9]{4}\-[0-9]{4}$/',$conn_info['connection_value'])==1 && !$external){
										$conn_info['connection_value'] = "<a title=\"".lang("Call Extension")."\" href=\"#\" onclick=\"connectVoip('".$conn_info['connection_value']."', 'com')\">".$conn_info['connection_value']."</a>";
 									
/*****/
									}
									$final[3][$i][3] = $conn_info['connection_value'] ? $conn_info['connection_value'] : 'none';
								}
								if($final[3][$i][4] == 'none' or !$final[3][$i][4]){
									$final[3][$i][4] = lang('Not informed');
									$final[3][$i][13] = $conn_info['id_connection'];
								
								}
							}
						}

						if (!$final[3][$i][3])
						{
							$final[3][$i][3] = 'none';
						}

						if($final[3][$i][4] == 'none' or !$final[3][$i][4]){
									$final[3][$i][4] = lang('Not informed');
									$final[3][$i][13] = $conn_info['id_connection'];
								
						}

						$final[3][$i][5] = $contact['alias']? urldecode( $contact['alias'] ):'none';
						$final[3][$i][6] = utf8_decode($ids[$i]);

/**rev 104**/
	/*				//	If contact is a public list, then load the forwarding addresses.
						if($contact['account_type'][0] == 'l')
							$final[3][$i][7] = array();
	*/

						//If contact is a public list or a group, then load the forwarding addresses.
						if($contact['account_type'][0] == 'l' || $contact['account_type'][0] == 'g')
							$final[3][$i][7] = ($contact['account_type'][0] == 'l' ? 'list' : 'group');
							
						if($this->page_info['actual_catalog']['class']=='bo_shared_people_manager') {
							$final[3][$i][11] = $perms[$i];
							$final[3][$i][12] = lang('Shared').": ".$owner_names[$owners[$i]]['cn'];
						}

						$final[4][$i] = $contact['photo'] ? 1  : 0;



                        ++$i;
					}
					else
					{
						// coloca mensagem de erro no vetor que retorna para o browser
					}
				}
				$lnk_compose = "location.href=('../expressoMail1_2/index.php?to=";
				$final[5] = '<span class="link" onclick="'.$lnk_compose;
				$final[6] = $prop_names;
				$final[7] = $validos;
				$final[8] = $this->page_info['actual_catalog']['class'];
				$final[9] = $count;
                                $final[11] = $this->bo->catalog_level;
				$final[12] = isset($config['cc_allow_details'])? $config['cc_allow_details'] : false;
				$final[13] = '<span class="" ';
				$this->page_info['actual_letter'] = $letter;
				$this->page_info['actual_page'] = $page;

				$this->save_session();
				echo serialize($final);
				return;
			}

			$this->page_info['actual_letter'] = $letter;
			$this->page_info['actual_page'] = $page;

			$this->save_session();

			$final[0] = 0;
			$final[1] = $this -> typeContact;
			echo serialize($final);
		}
		
		
		function get_list_owners_perms_add(){
			$acl = CreateObject('phpgwapi.acl');
			$find_result = $acl->get_rights_and_owners($GLOBALS['phpgw_info']['user']['account_id'],'contactcenter');
			if($find_result){
				$owner_names = array();
				$filter = "(|";
				foreach($find_result as $owner) {
					if(($owner['acl_rights'] & PHPGW_ACL_ADD) == PHPGW_ACL_ADD){
						$filter .= "(uidNumber=".$owner['acl_account'].")";
					}
				}
				if(!$this->bo->catalog->src_info) {
					$ldap = CreateObject('contactcenter.bo_ldap_manager');
					$this->bo->catalog->src_info = $ldap->srcs[1];
				}
				$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], false);
				$filter .= ")";
				$dn=$this->bo->catalog->src_info['dn'];
				$justThese = array("cn","uidnumber","uid");
				$sr = ldap_search($ds,$dn, $filter,$justThese);
				$info = ldap_get_entries($ds, $sr);
				for($z = 0; $z < $info['count']; ++$z) {
					$owner_names[$info[$z]['uidnumber'][0]] = array("cn" => $info[$z]['cn'][0], "uid" => $info[$z]['uid'][0]);
				}
				ldap_close($ds);
				if(!count($owner_names)){
						$dns = explode(',', $dn);
						for($i = count($dns); $i > 0; $i--){
							$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], false);
							$sr = ldap_search($ds,$dns[$i], $filter,$justThese);
							$info = ldap_get_entries($ds, $sr);
							ldap_close($ds);
							for($z = 0; $z < $info['count']; ++$z) {
								$owner_names[$info[$z]['uidnumber'][0]] = array("cn" => $info[$z]['cn'][0], "uid" => $info[$z]['uid'][0]);
							}
							if(count($owner_names)){
								$i = 0;
							}
						}
					}
			}
			echo serialize ($owner_names);	
		}

		function get_visible_all_ldap()
		{
			$bo = CreateObject('contactcenter.bo_ldap_manager');
			$ldap_query = $bo->srcs;
			return $ldap_query[1]['visible'];
		}


		/*!

			@function get_group_data
			@abstract Returns all the information of a given Group
			@author Nilton Emilio Buhrer Neto

			@param (integer) $id The id to get information

		*/
		function get_group_data($id,$shared_from=null)
		{
			$this->bo->catalog = CreateObject('contactcenter.bo_group_manager');
			$fields = $this->bo->catalog->get_fields(true);
			$data = $this->bo->catalog->get_single_entry($id,$fields);
			
			if($id) {			
				// get All Contacts by group.
				$data['contact_in_list'] = $this->bo->catalog->get_contacts_by_group($id);								
			}
			
			$boGroup = CreateObject('contactcenter.bo_group');

			$all_contacts = $boGroup->get_all_contacts(false,$shared_from);
			
			$contact_options = "";
			if(count($all_contacts)) {					
				foreach($all_contacts as $idx => $contact) {				
					$contact_options .= "<OPTION value='".$contact['id_connection']."'>".$contact['names_ordered'];
					if (isset($contact['connection_value']))
						$contact_options .= " (".$contact['connection_value'].")</OPTION>";
					else
						$contact_options .= " (". $contact['phone'].")</OPTION>";
				}
			}
			$data['contact_list'] = $contact_options;
			$data['result'] = 'ok';								
			echo serialize($data);			
		}		
		
		/*!

			@function get_full_data
			@abstract Returns all the information of a given Entry
			@author Raphael Derosso Pereira

			@param (integer) $id The id to get information

		*/
/**rev 104**/
		//function get_full_data($id)
		function get_full_data($id,$catalog='bo_people_catalog')
		{
			$dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];
/**rev 104**/
			//$this->bo->catalog = CreateObject('contactcenter.bo_people_catalog');
			$this->bo->catalog = CreateObject('contactcenter.'.$catalog);
/****/
			$fields = $this->bo->catalog->get_fields(true);
			$fields['photo'] = false;
			$entry = $this->bo->catalog->get_single_entry($id,$fields);

			if (is_bool($entry['given_names']))
			{
				$data['result'] = 'false';
				echo serialize($data);
				return;
			}

			$date = explode('-', $entry['birthdate']);
			$j = 0;
			for ($i = 0; $i < 5; $i+=2)
			{
				switch($dateformat{$i})
				{
					case 'Y':
						$birthdate[$j] = $date[0];
						break;

					case 'm':
					case 'M':
						$birthdate[$j] = $date[1];
						break;

					case 'd':
						$birthdate[$j] = $date[2];
				}
                ++$j;
			}
			$datecount = 0;

			$data['result'] = 'ok';
			$data['cc_full_add_contact_id'] = $id;

			/* Personal Data */
			$data['personal']['cc_pd_photo'] = '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_photo&id='.$id;
			$data['personal']['cc_pd_alias'] = $entry['alias'];
			$data['personal']['cc_pd_given_names'] = $entry['given_names'];
			$data['personal']['cc_pd_family_names'] = $entry['family_names'];
			$data['personal']['cc_pd_full_name'] = $entry['names_ordered'];
			$data['personal']['cc_pd_suffix'] = $entry['id_suffix'];
			$data['personal']['cc_pd_birthdate_0'] = $birthdate[0];
			$data['personal']['cc_pd_birthdate_1'] = $birthdate[1];
			$data['personal']['cc_pd_birthdate_2'] = $birthdate[2];
			//$data['personal']['cc_pd_sex'] = $entry['sex'] === 'M' ? 1 : ($entry['sex'] === 'F' ? 2 : 0);
			$data['personal']['cc_pd_prefix'] = $entry['id_prefix'];
			$data['personal']['cc_pd_gpg_finger_print'] = $entry['pgp_key'];
			$data['personal']['cc_pd_notes'] = $entry['notes'];

			/* Addresses */
			if (is_array($entry['addresses']))
			{
				$data['addresses'] = $entry['addresses'];
			}

			/* Connections */
			if (is_array($entry['connections']))
			{
				$data['connections'] = array();
				foreach ($entry['connections'] as $connection)
				{
					$type = $connection['id_type'];
					$i = count($data['connections'][$type]);
					$data['connections'][$type][$i]['id'] = $connection['id_connection'];
					$data['connections'][$type][$i]['name'] = $connection['connection_name'];
					$data['connections'][$type][$i]['value'] = $connection['connection_value'];
					$data['connections'][$type][$i]['is_default'] = $connection['connection_is_default'];
				}
			}
//			print_r($data);
//OBSERVAR cc_department
			/*Corporative*/
			if($GLOBALS['phpgw_info']['server']['personal_contact_type']=='True'){
				$data['personal']['cc_job_title'] = $entry['job_title'];
				$data['personal']['cc_department'] = $entry['department'];
				$data['personal']['cc_name_corporate'] = $entry['corporate_name'];
				$data['personal']['cc_web_page'] = $entry['web_page'];
			}
			


			/* Relations */
                        /* Groups */
			/*
			 * Criado uma estrutura no data que conterá os grupos do
			 * contato. O formato é:
			 * data['groups'] = array(
			 * 						'id_group' => array(
			 * 										'id_group', 'title', 'short_name'
			 * 										)
			 * 						);
			 */
			$boGroup = CreateObject('contactcenter.bo_group');
			$groups = $boGroup->get_contact_groups($id);
			
			$i = 0;
			$data['groups'] = array();
			if(is_array($groups))
			foreach($groups as $group)
			{
				$idGroup = $group['id_group'];
				$data['groups'][$idGroup] = array(
					'title' 		=> $group['title'],
					'id_group' 		=> $idGroup,
					'short_name' 	=> $group['short_name']
				);
                ++$i;
			}

			echo serialize($data);
		}

		/*!

			@function get_contact_full_add_const
			@abstract Returns all the constant fields in Contact Full Add Window to the JS
			@author Raphael Derosso Pereira
		*/
		function get_contact_full_add_const()
		{
			$data = array();
			$boPeopleCatalog = CreateObject('contactcenter.bo_people_catalog');
			$predata[] = $boPeopleCatalog -> get_all_prefixes();
			$predata[] = $boPeopleCatalog -> get_all_suffixes();
			$predata[] = $boPeopleCatalog -> get_all_addresses_types();
			$predata[] = $boPeopleCatalog -> get_all_countries();
			$predata[] = $boPeopleCatalog -> get_all_connections_types();
			$boGroup = CreateObject('contactcenter.bo_group'); 
            //$predata[] = $boGroup->get_groups_by_user();
			//$predata[] = $this->bo->catalog->get_all_relations_types();

			if ($this->typeContact == 'shared_contacts') {  
 		                                $so_contact = CreateObject('contactcenter.so_contact',  $GLOBALS['phpgw_info']['user']['account_id']); 
 		                $relacionados = $so_contact->get_relations(); 
 		                         
 		                foreach($relacionados as $uid_relacionado => $tipo_relacionamento) {     
 	                                        $predata[] = $boGroup->get_groups_by_user($uid_relacionado);                                             
 		                                }        
 		                        } else { 
 		                                                $predata[] = $boGroup->get_groups_by_user(); 
 		                                        }
			
			
			$i = 0;
			foreach($predata as $data_)
			{
				if ($data_)
				{
					$data[$i] = $data_;
				}

                ++$i;
			}

			if (count($data))
			{
				echo serialize($data);
				return;
			}

			echo 0;
		}

		/*!

			@function quick_add
			@abstract Adds a new Contact using the Quick Add interface
			@author Raphael Derosso Pereira

			@param string $sdata Serialized data
		*/
		function quick_add($sdata, $echo=true)
		{

			$sdata = str_replace('\\"', '"', $sdata);
			$new_array = unserialize($sdata);
			$tdata = array();

			foreach($new_array as $tmp)
				$tdata[] = $tmp;

			if (!$tdata)
			{
                            if ($echo)
                            {
				echo serialize(array(
					'msg'    => lang('Problems on adding your Contact. Invalid Data came from client. No Contact added!'),
					'status' => 'abort'
				));
                            
				return;
                            }
                            else
                                {
                                    return serialize(array(
					'msg'    => lang('Problems on adding your Contact. Invalid Data came from client. No Contact added!'),
					'status' => 'abort'
                                    ));
                                }
			}
			$data['alias'] = addslashes($tdata[0]);
			$data['given_names'] = addslashes($tdata[1]);
			$data['family_names'] = addslashes($tdata[2]);
			$data['connections']['default_phone']['connection_name'] = lang('Main');
			$data['connections']['default_phone']['connection_value'] = $tdata[3];
			$data['connections']['default_email']['connection_name'] = lang('Main');
			$data['connections']['default_email']['connection_value'] = $tdata[4];
			$data['is_quick_add'] = true;
			$boPeople = CreateObject('contactcenter.bo_people_catalog');

			$result = $boPeople->quick_add($data);
			if ($result)
			{
                            $this->page_info['changed'] = true;

                            if ($echo)
                            {
                                echo serialize(array(
                                    'msg'    => lang('Entry added with success!'),
                                    'status' => 'ok'
                                ));
                            }
                            else
                                {
                                    return serialize(array(
                                        'msg'    => lang('Entry added with success!'),
                                        'status' => 'ok',
                                        'conn'   => $boPeople->get_connections($result)
                                    ));
                                }
			}
			else
			{
                            if ($echo)
                            {
                                echo serialize(array(
                                    'msg'    => lang('Problems on adding your Contact. No Contact added!'),
                                    'status' => 'error'
                                ));
                            }
                            else
                                {
                                    echo serialize(array(
                                        'msg'    => lang('Problems on adding your Contact. No Contact added!'),
                                        'status' => 'error'
                                    ));
                                }
			}

			$this->save_session();

		}

		/*!

			@function add_group
			@abstract Adds a new Group using the Add Group interface
			@author Nilton Emilio Buhrer Neto

			@param string $sdata Serialized data
		*/
		function add_group($sdata)
		{
			$sdata = str_replace('\\"', '"', $sdata);
			$tdata = unserialize($sdata);
			$new_tdata = array();

			if (!$tdata)
			{
				echo serialize(array(
					'msg'    => lang('Problems on adding your Contact. Invalid Data came from client. No Contact added!'),
					'status' => 'abort'
				));

				return;
			}

			foreach($tdata as $tmp)
				$new_tdata[] = $tmp;

			$data['title'] = $new_tdata[0];
			$data['contact_in_list'] = $new_tdata[1];
			$data['id_group'] = $new_tdata[2];
                        $acumulatedErrors = '';

                        $actualCatalog = $this->get_actual_catalog();
                        $data_count = count($data['contact_in_list']);
                        for ($i = 0; $i < $data_count; ++$i)
                        {
                            if (preg_match('/ldap:.*:.*/', $data['contact_in_list'][$i])) // from ldap
                            {
                                list(, $level, $dn) = explode(':', $data['contact_in_list'][$i]);

                                // pesquisa os dados, insere no catálogo e modifica
                                // a entrada em $data['contact_in_list'][$i]
                                $set_catalog = $this->set_catalog($level, false);
                                $contact_data = unserialize($this->get_catalog_add_contact(utf8_encode($dn), false));
                                $tmp_contact[] = $contact_data[0][0];
                                $tmp_contact[] = $contact_data[1][0];
                                $tmp_contact[] = $contact_data[2][0];
                                $tmp_contact[] = $contact_data[3][0];
                                $tmp_contact[] = $contact_data[4][0];

                                // Determinar o id_connection
                                $id_contact = unserialize($this->quick_add(serialize($tmp_contact), false));

                                switch ($id_contact['status'])
                                {
                                    case 'ok' :
                                        foreach ($id_contact['conn'] as $connection)
                                        {
                                            if ($connection['id_type'] == 1)
                                            {
                                                $data['contact_in_list'][$i] = $connection['id_connection'];
                                            }
                                        }
                                        break;

                                    case 'alreadyExists': // if e-mail exists get their id_connection from people_catalog
                                        $data['contact_in_list'][$i] = $id_contact['id_connection'];

                                    default:
                                        $acumulatedErrors += $id_contact['msg']."\n";
                                }
                                unset($tmp_contact);

                            }
                        }
                        $set_catalog = $this->set_catalog($actualCatalog['data'], false); // retorna ao catálogo original.
			$boGroup = CreateObject('contactcenter.bo_group_manager');
			$id = $boGroup -> add_group($data);

			if ($id)
			{
				$this->page_info['changed'] = true;

				echo serialize(array(
					'msg'    => lang('Entry added with success!'),
					'status' => 'ok'
				));
			}
			else
			{
				echo serialize(array(
					'msg'    => lang("Problems on adding your Group. Be sure that a group with this name do not exists").
                                            "\n".$acumulatedErrors,
					'status' => 'error'
				));
			}

			$this->save_session();
		}

		/*!

			@function remove_group
			@abstract Removes a group if the user has the right to do it
			@author Nilton Emilio Buhrer Neto
			@param (integer) $id The id to be removed

		*/
		function remove_group($id)
		{
				$soGroup = CreateObject('contactcenter.so_group');
				$data = array ('id_group' => $id);
				if($soGroup -> delete($data)) {
					echo serialize(array(
						'msg'    => lang('Removed Entry ID '.$id.'!'),
						'status' => 'ok'
					));
				}
				else {
					echo serialize(array(
						'msg'    => lang("Could\'nt remove group with id: %1", $id),
						'status' => 'error'
					));
				}

			$this->save_session();
		}


		function remove_all_entries (){

			$error = false;
			$this->all_entries = $this->bo->catalog->get_all_entries_ids();

			foreach($this->all_entries as $index => $id) {
				$result = $this->bo->catalog->remove_single_entry($id);
				if(!$result) {
					$error = true;
					break;
				}
			}

			if(!$error) {
				echo serialize(array(
					'msg'    => lang('Removed Entry ID '.$id.'!'),
					'status' => 'ok'
				));
			}
			else {
				echo serialize(array(
					'msg'    => lang('Couldn\'t remove this entry. Inform the Site Admin!'),
					'status' => 'fail'
				));
			}

			$this->save_session();
		}

		/*!

			@function remove_entry
			@abstract Removes an entry if the user has the right to do it
			@author Raphael Derosso Pereira

			@param (integer) $id The id to be removed

		*/
		function remove_entry ($id)
		{
			if (!is_int($id))
			{
				echo lang('Couldn\'t remove entry! Problem passing data to the server. Please inform admin!');
				return;
			}

			$this->page_info['changed'] = true;
			$result = $this->bo->catalog->remove_single_entry($id);

			if ($result)
			{
				if ($pos = array_search($id, $this->page_info['actual_entries']))
				{
					unset($this->page_info['actual_entries'][$pos]);
				}

				$temp = false;
				reset($this->page_info['actual_entries']);
				foreach($this->page_info['actual_entries'] as $t)
				{
					$temp[] = $t;
				}

				$this->page_info['actual_entries'] = $temp;

				echo serialize(array(
					'msg'    => lang('Removed Entry ID '.$id.'!'),
					'status' => 'ok'
				));
			}
			else
			{
				echo serialize(array(
					'msg'    => lang('Couldn\'t remove this entry. Inform the Site Admin!'),
					'status' => 'fail'
				));
			}

			$this->save_session();
		}


		/*!

			@function post_full_add
			@abstract Saves all the information altered/entered in the Full Add
				window
			@author Raphael Derosso Pereira

		*/
		function post_full_add()
		{
			$data =  $_POST['data'];
			// Exceptions!!! utf8 special chars.
			$data = preg_replace("/\%u2(\d+)(\d+)(\d+)/","-",$data);
			$data = unserialize(str_replace('\\"', '"', $data));
			$this -> bo -> catalog = CreateObject('contactcenter.bo_people_catalog');

			if (!is_array($data))
			{
				echo serialize(array(
					'msg' => lang('<p>Some problem receiving data from browser. This is probably a bug in ContactCenter<br>'.
				                  'Please go to eGroupWare Bug Reporting page and report this bug.<br>'.
						          'Sorry for the inconvenient!<br><br>'.
						          '<b><i>ContactCenter Developer Team</i></b></p>'),
					'status' => 'fatal'
				));
				return;
			}
//			print_r($data);
//			echo '<br><br>';

			$replacer = $data['commercialAnd'];
			unset($data['commercialAnd']);
			if (!is_string($replacer) or strpos($replacer, "'") or strpos($replacer, '"'))
			{
				echo serialize(array(
					'msg' => lang('Invalid \'&\' replacer! This may be an attempt to bypass Security! Action aborted!'),
					'status' => 'fatal'
				));

				return;
			}

			if ($data['id_contact'])
			{
				$id = $data['id_contact'];
				$id_photo = $id;
				unset($data['id_contact']);
			}
			else
			{
				$id_photo = '_new_';
			}

			/*
			 * Process Photo, if available
			 */
			$sleep_count = 0;
			$photo_ok = $GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter');
			while($photo_ok[0]{0} !== 'o' and $photo_ok[1]{0} === 'y')
			{
				sleep(1);
				$photo_ok = $GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter');
                ++$sleep_count;

				if ($sleep_count > 35)
				{
					// TODO
					return;
				}
			}
			$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('wait', 'n'));

			if (isset($this->page_info['photos'][$id_photo]))
			{
				if (array_search($this->page_info['photos'][$id_photo]['status'], array('changed', 'sync')) === false)
				{
					echo serialize(array(
						'msg' => $this->page_info['photos'][$id_photo]['msg'],
						'status' => $this->page_info['photos'][$id_photo]['status']
					));

					return;
				}

				$data['photo'] = $this->page_info['photos'][$id_photo]['content'];
				unset($this->page_info['photos'][$id_photo]);
				$this->save_session();
			}

			/*
			 * Arrange Date so it gets inserted correctly
			 */

			$dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];

			$j = 0;
			for ($i = 0; $i < 5; $i+=2)
			{
				switch($dateformat{$i})
				{
					case 'Y':
						$date[$j]['size'] = 4;
						$date[$j]['digit'] = 'Y';
						break;

					case 'm':
					case 'M':
						$date[$j]['size'] = 2;
						$date[$j]['digit'] = 'M';
						break;

					case 'd':
						$date[$j]['size'] = 2;
						$date[$j]['digit'] = 'D';
				}
                ++$j;
			}
			$datecount = 0;

			/* Verify Data and performs insertion/update */
			foreach($data as $field => $value)
			{
				$dataConn = $data['connections'];
				$aa = count($dataConn);

				for($i = 0; $i < $aa; ++$i )
				{
				    if($dataConn['connection'.$i]['connection_is_default'] == "TRUE")
				    {
					$email = $dataConn['connection'.$i]['connection_value'];
				    }
				}

				switch($field)
				{
					case 'names_ordered':
						$data[$field] = addslashes(rawurldecode($value));
					case 'corporate_name':
					case 'job_title':
					case 'department':
					case 'web_page':
					case 'alias':
					case 'given_names':
					case 'family_names':
					case 'pgp_key':
					case 'notes':
						$data[$field] = addslashes(rawurldecode($data[$field]));
						break;

					case 'id_status':
					case 'id_prefix':
					case 'id_suffix':
						if ($data[$field] == 0)
						{
							unset($data[$field]);
						}
						break;

					case 'birthdate_0':
					case 'birthdate_1':
					case 'birthdate_2':
					case 'birthdate':
					
						if($field == 'birthdate'){
							$array_birth = explode("/",$data[$field]);							
							$date['value'][2] = $array_birth[2];
							$date['value'][1] = $array_birth[1];
							$date['value'][0] = $array_birth[0];						
						}else{					
							switch($date[$datecount]['digit'])
							{
								case 'Y':
									$date['value'][2] = (int) $data[$field];
									break;
	
								case 'M':
									$date['value'][0] = (int) $data[$field];
									break;
	
								case 'D':
									$date['value'][1] = (int) $data[$field];
									break;
							}
							unset($data[$field]);
						}

                        ++$datecount;

						if ($datecount != 3)
						{
							break;
						}

						if($date['value'][0] =='' && $date['value'][1] =='' && $date['value'][2] ==''){
							$data['birthdate'] = null;
							break;
						}
						if (!checkdate($date['value'][0], $date['value'][1], $date['value'][2]))
						{
							echo serialize(array(
								'msg' => lang('Invalid Date'),
								'status' => 'invalid_data'
							));
							return;
						}
						if( $date['value'][2] != "" && $date['value'][0] != "" && $date['value'][1] != ""){
							$data['birthdate'] = $date['value'][2].'-'.$date['value'][0].'-'.$date['value'][1];
						}
						break;

					case 'sex':
						if ($data[$field] !== 'M' and $data[$field] !== 'F')
						{
							echo serialize(array(
								'msg' => lang('Invalid Sex'),
								'status' => 'invalid_data'
							));
							return;
						}
						break;


					case 'addresses':
						/* Insert new cities/states */
						if (isset($value['new_states']))
						{
							foreach($value['new_states'] as $type => $state_info)
							{
								$index = 'address'.$type;

								$id_state = $this->bo->catalog->add_state($state_info);
								$data['addresses'][$index]['id_state'] = $id_state;

								if ($value['new_cities'][$type])
								{
									$data[$field]['new_cities'][$type]['id_state'] = $id_state; 
								}
							}

							unset($data['addresses']['new_states']);
						}

						if (isset($value['new_cities']))
						{
							foreach($value['new_cities'] as $type => $city_info)
							{
								$index = 'address'.$type;

								$id_city = $this->bo->catalog->add_city($city_info);
								$data['addresses'][$index]['id_city'] = $id_city;
							}

							unset($data['addresses']['new_cities']);
						}

					break;

					case 'birthdate':
					case 'connections':
					case 'photo':
						/* Does nothing... */
						break;
					case 'groups':
						$groups = $data['groups'];
						unset($data['groups']);
						break;
					default:
						echo serialize(array(
							'msg' => lang('Invalid field: ').$field,
							'status' => 'invalid_data'
						));
						return;
				}
			}

			if (!is_null($id) and $id !== '')
			{
				$id = $this->bo->catalog->update_single_info($id, $data); 
				$result = array(
					'msg' => lang('Updated Successfully!'),
					'status' => 'ok'
				);
			}
			else
			{
				$id = $this->bo->catalog->add_single_entry($data); 
				$result = array(
					'msg' => lang('Entry Added Successfully!'),
					'status' => 'ok'
				);
			}

			if (!($id))
			{
				$result = array(
					'msg' => lang('Some problem occured when trying to insert/update contact information.<br>'.
				                   'Report the problem to the Administrator.'),
					'status' => 'fail'
				);
			}
                        else
			{
				if (isset($old_connections))
					$this->bo->catalog->update_contact_groups($id, $groups, $old_connections);
				else
					$this->bo->catalog->update_contact_groups($id, $groups);
			}

			echo serialize($result);
		}


		function post_full_add_shared()
		{
			$data =  $_POST['data'];
			// Exceptions!!! utf8 special chars.
			$data = preg_replace("/\%u2(\d+)(\d+)(\d+)/","-",$data);
			$data = unserialize(str_replace('\\"', '"', $data));
			$this -> bo -> catalog = CreateObject('contactcenter.bo_shared_people_manager');

			if (!is_array($data))
			{
				echo serialize(array(
					'msg' => lang('<p>Some problem receiving data from browser. This is probably a bug in ContactCenter<br>'.
				                  'Please go to eGroupWare Bug Reporting page and report this bug.<br>'.
						          'Sorry for the inconvenient!<br><br>'.
						          '<b><i>ContactCenter Developer Team</i></b></p>'),
					'status' => 'fatal'
				));
				return;
			}
//			print_r($data);
//			echo '<br><br>';

			$replacer = $data['commercialAnd'];
			unset($data['commercialAnd']);
			if (!is_string($replacer) or strpos($replacer, "'") or strpos($replacer, '"'))
			{
				echo serialize(array(
					'msg' => lang('Invalid \'&\' replacer! This may be an attempt to bypass Security! Action aborted!'),
					'status' => 'fatal'
				));

				return;
			}

			if ($data['id_contact'])
			{
				$id = $data['id_contact'];
				$id_photo = $id;
				unset($data['id_contact']);
			}
			else
			{
				$id_photo = '_new_';
			}

			if ($data['owner'])
			{
				$owner = $data['owner'];
				unset($data['owner']);
			}
			/*
			 * Process Photo, if available
			 */
			$sleep_count = 0;
			$photo_ok = $GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter');
			while($photo_ok[0]{0} !== 'o' and $photo_ok[1]{0} === 'y')
			{
				sleep(1);
				$photo_ok = $GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter');
                ++$sleep_count;

				if ($sleep_count > 35)
				{
					// TODO
					return;
				}
			}
			$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('wait', 'n'));

			if (isset($this->page_info['photos'][$id_photo]))
			{
				if (array_search($this->page_info['photos'][$id_photo]['status'], array('changed', 'sync')) === false)
				{
					echo serialize(array(
						'msg' => $this->page_info['photos'][$id_photo]['msg'],
						'status' => $this->page_info['photos'][$id_photo]['status']
					));

					return;
				}

				$data['photo'] = $this->page_info['photos'][$id_photo]['content'];
				unset($this->page_info['photos'][$id_photo]);
				$this->save_session();
			}

			/*
			 * Arrange Date so it gets inserted correctly
			 */

			$dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];

			$j = 0;
			for ($i = 0; $i < 5; $i+=2)
			{
				switch($dateformat{$i})
				{
					case 'Y':
						$date[$j]['size'] = 4;
						$date[$j]['digit'] = 'Y';
						break;

					case 'm':
					case 'M':
						$date[$j]['size'] = 2;
						$date[$j]['digit'] = 'M';
						break;

					case 'd':
						$date[$j]['size'] = 2;
						$date[$j]['digit'] = 'D';
				}
                ++$j;
			}
			$datecount = 0;

			/* Verify Data and performs insertion/update */
			foreach($data as $field => &$value)
			{

				$dataConn = $data['connections'];
				$aa = count($dataConn);

				for($i = 0; $i < $aa; ++$i )
				{
				    if($dataConn['connection'.$i]['connection_is_default'] == "TRUE")
				    {
					$email = $dataConn['connection'.$i]['connection_value'];
				    }
				}

				if ($value == '' or is_null($value))
				{
					unset($data[$field]);
					continue;
				}

				switch($field)
				{
					case 'corporate_name':
					case 'job_title':
					case 'department':
					case 'web_page':
					case 'alias':
					case 'given_names':
					case 'family_names':
					case 'groups': 
 	                    $groups = $data['groups']; 
 	                    unset($data['groups']); 
 	                    break;
					case 'names_ordered':
					case 'pgp_key':
					case 'notes':
					case 'photo':
						$data[$field] = urldecode( $value ); 
						break;

					case 'id_status':
					case 'id_prefix':
					case 'id_suffix':
						if ($data[$field] == 0)
						{
							unset($data[$field]);
						}
						break;

					case 'birthdate_0':
					case 'birthdate_1':
					case 'birthdate_2':

						switch($date[$datecount]['digit'])
						{
							case 'Y':
								$date['value'][2] = (int) $data[$field];
								break;

							case 'M':
								$date['value'][0] = (int) $data[$field];
								break;

							case 'D':
								$date['value'][1] = (int) $data[$field];
								break;
						}
						unset($data[$field]);
                        ++$datecount;

						if ($datecount != 3)
						{
							break;
						}

						if (!checkdate($date['value'][0], $date['value'][1], $date['value'][2]))
						{
							echo serialize(array(
								'msg' => lang('Invalid Date'),
								'status' => 'invalid_data'
							));
							return;
						}

						$data['birthdate'] = $date['value'][2].'-'.$date['value'][0].'-'.$date['value'][1];
						break;

					case 'sex':
						if ($data[$field] !== 'M' and $data[$field] !== 'F')
						{
							echo serialize(array(
								'msg' => lang('Invalid Sex'),
								'status' => 'invalid_data'
							));
							return;
						}
						break;


					case 'addresses':
						/* Insert new cities/states */
						if (isset($value['new_states']))
						{
							foreach($value['new_states'] as $type => $state_info)
							{
								$index = 'address'.$type;

								$id_state = $this->bo->catalog->add_state($state_info);
								$data['addresses'][$index]['id_state'] = $id_state;

								if ($value['new_cities'][$type])
								{
									$value['new_cities'][$type]['id_state'] = $id_state;
								}
							}

							unset($data['addresses']['new_states']);
						}

						if (isset($value['new_cities']))
						{
							foreach($value['new_cities'] as $type => $city_info)
							{
								$index = 'address'.$type;

								$id_city = $this->bo->catalog->add_city($city_info);
								$data['addresses'][$index]['id_city'] = $id_city;
							}

							unset($data['addresses']['new_cities']);
						}

					break;

					case 'connections':
						/* Does nothing... */
						$aaaa = 1111;
						break;

					default:
						echo serialize(array(
							'msg' => lang('Invalid field: ').$field,
							'status' => 'invalid_data'
						));
						return;
				}
			}

			//$code = '$id = $this->bo->catalog->';

			if (!is_null($id) and $id !== '')
			{
				//$code .= $code.'update_single_info($id, $data);';
				$id = $this->bo->catalog->update_single_info($id, $data);
				if(!$id){
				$result = array(
						'msg' => lang('Some problem occured when trying to insert/update contact information.<br>'.
				        'Report the problem to the Administrator.'),
						'status' => 'fail'
				);
			}
				else{
				$result = array(
						'msg' => lang('Updated Successfully!'),
					'status' => 'ok'
				);
			}
			}
			else
			{
				//$code .= 'add_single_entry($data,'.$owner.');';
				$id = $this->bo->catalog->add_single_entry($data,$owner);
				if(!$id){
				$result = array(
					'msg' => lang('Some problem occured when trying to insert/update contact information.<br>'.
				                   'Report the problem to the Administrator.'),
					'status' => 'fail'
				);
			}
				else{
					$result = array(
						'msg' => lang('Entry Added Successfully!'),
						'status' => 'ok'
					);
				}
			}
			echo serialize($result);
		}
		/*!

			@function post_photo
			@abstract Wrapper to post a photo without reload a page.
			@author Raphael Derosso Pereira

		*/
		function post_photo($id)
		{
			//print_r($_FILES);
			$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('wait', 'y'));

			if (!is_array($_FILES) and is_array(!$_FILES['cc_pd_photo']))
			{
				$this->page_info['photos'][$id]['status'] = 'no_upload';
				$this->page_info['photos'][$id]['msg'] = lang('No Photos uploaded to Server.');

				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			if (!function_exists('imagecreate'))
			{
				$this->page_info['photos'][$id]['status'] = 'no_GD_lib';
				$this->page_info['photos'][$id]['msg'] = lang('Cannot manipulate Image. No Image added. Please, if you want to use images, ask the Administrator to install GD library.');

				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			// TODO: Get Max Size from preferences!
			if ($_FILES['cc_pd_photo']['size'] > 1000000)
			{
				$this->page_info['photos'][$id]['status'] = 'too_large';
				$this->page_info['photos'][$id]['msg'] = lang('Image too large! ContactCenter limits the image size to 1 Mb');

				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			if ($_FILES['cc_pd_photo']['error'])
			{
				$this->page_info['photos'][$id]['status'] = 'error';
				$this->page_info['photos'][$id]['msg'] = lang('Some Error occured while processed the Image. Contact the Administrator. The error code was: ').$_FILES['cc_pd_photo']['error'];

				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			switch($_FILES['cc_pd_photo']['type'])
			{
				case 'image/jpeg':
				case 'image/pjpeg':
					$src_img = imagecreatefromjpeg($_FILES['cc_pd_photo']['tmp_name']);
					if ($src_img == '')
					{
						$bogus = true;
					}
					break;

				case 'image/png':
				case 'image/x-png':
					$src_img = imagecreatefrompng($_FILES['cc_pd_photo']['tmp_name']);
					if ($src_img == '')
					{
						$bogus = true;
					}
					break;

				case 'image/gif':
					$src_img = imagecreatefromgif($_FILES['cc_pd_photo']['tmp_name']);
					if ($src_img == '')
					{
						$bogus = true;
					}
					break;

				default:

					$this->page_info['photos'][$id]['status'] = 'invalid_image';
					$this->page_info['photos'][$id]['msg'] = lang('The file must be an JPEG, PNG or GIF Image.');

					$this->save_session();
					$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
					return;
			}

			if ($bogus)
			{
					$this->page_info['photos'][$id]['status'] = 'invalid_file';
					$this->page_info['photos'][$id]['msg'] = lang('Couldn\'t open Image. It may be corrupted or internal library doesn\'t support this format.');

					$this->save_session();
					$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
					return;
			}

			$img_size = getimagesize($_FILES['cc_pd_photo']['tmp_name']);
			$dst_img = imagecreatetruecolor(60, 80);

			if (!imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, 60, 80, $img_size[0], $img_size[1]))
			{
				$this->page_info['photos'][$id]['status'] = 'invalid_file';
				$this->page_info['photos'][$id]['msg'] = lang('Couldn\'t open Image. It may be corrupted or internal library doesn\'t support this format.');

				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			ob_start();
			imagepng($dst_img);
			$this->page_info['photos'][$id]['content'] = ob_get_contents();
			ob_end_clean();

			$this->page_info['photos'][$id]['status'] = 'changed';
			$this->page_info['photos'][$id]['msg'] = lang('Photo Successfully Updated!');

			$this->save_session();

			$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));

			imagedestroy($src_img);
			imagedestroy($dst_img);
			echo 'ok';
			return;
		}


		/*!

			@function get_photo
			@abstract Returns the photo to the browser
			@author Raphael Derosso Pereira

		*/
		function get_photo($id)
		{
			$fields = $this->bo->catalog->get_fields(false);
			$fields['photo'] = true;

			$contact = $this->bo->catalog->get_single_entry($id, $fields);

			if (!$contact['photo'])
			{
				header('Content-type: image/png');
				echo file_get_contents(PHPGW_INCLUDE_ROOT.'/contactcenter/templates/default/images/photo_celepar.png');
				return;
			}

			header('Content-type: image/jpeg');
			$photo = imagecreatefromstring ($contact['photo']);
			$width = imagesx($photo);
			$height = imagesy($photo);
			$twidth = 70;
			$theight = 90;
			$small_photo = imagecreatetruecolor ($twidth, $theight);
			imagecopyresampled($small_photo, $photo, 0, 0, 0, 0,$twidth, $theight, $width, $height);
			imagejpeg($small_photo,"",100);
			return;
		}

		/*!

			@function get_states
			@abstract Echos a serialized array containing all the states for the given country
			@author Raphael Derosso Pereira

			@params $id_country The ID of the Country that contains the requested states

		*/
		function get_states($id_country)
		{
			$states = $this->bo->catalog->get_all_states($id_country);

			if (!$states)
			{
				$result = array(
					'msg'    => lang('No States found for this Country.'),
					'status' => 'empty'
				);

				echo serialize($result);
				return;
			}

			$result = array(
				'msg'    => lang('States Successfully retrieved!'),
				'status' => 'ok'
			);

			foreach ($states as $state_info)
			{
				$result['data'][$state_info['id_state']] = $state_info['name'];

				if ($state_info['symbol'])
				{
					$result['data'][$state_info['id_state']] .= ', '.$state_info['symbol'];
				}
			}

			echo serialize($result);
		}

		/*!

			@function get_cities
			@abstract Echos a serialized array containing all the cities of a given state
			@author Raphael Derosso Pereira

			@param $id_country The ID of the Country that has the specified Cities (in case the
				Country doesn't have any States)
			@param $id_state The ID of the State that has the Cities requested

		*/
		function get_cities($id_country, $id_state=false)
		{
			$cities = $this->bo->catalog->get_all_cities($id_country, $id_state);

			if (!$cities)
			{
				$result = array(
					'msg'    => lang('No Cities found for this State.'),
					'status' => 'empty'
				);

				echo serialize($result);
				return;
			}

			$result = array(
				'msg'    => lang('Cities Successfully retrieved!'),
				'status' => 'ok'
			);

			foreach ($cities as $city_info)
			{
				$result['data'][$city_info['id_city']] = $city_info['name'];
			}

			echo serialize($result);
		}

		//Traduz o campo na busca completa por entradas no catálogo do usuário.
		function aux_full_search ($field,$isldap) {
			$retorno = '';
			if($isldap) {
				switch($field) {
					case 'mail':
						$retorno = 'contact.connection.mail';
						break;
					case 'phone':
						$retorno = 'contact.connection.phone';
						break;
				}
			}
			else {
				switch($field) {
					case 'corporate':
						$retorno = 'contact.corporate_name';
						break;
					case 'mail':
					case 'phone':
						$retorno = 'contact.contact_connection.connection.connection_value';
						break;
				}
			}
			return $retorno;
		}

		/*!

			@function search
			@abstract Echos a serialized array containing the IDs
				of the entries that matches the search argument
			@author Raphael Derosso Pereira
			@author Mário César Kolling (external catalogs)

			@param string $str_data A serialized array with two informations:
				$data = array(
					'search_for' => (string),
					'recursive'  => (boolean),
				);

		*/
		// SERPRO
		function search($str_data)
		{
			$data = unserialize($str_data);
			// It's an external catalog?
			$external = $this->bo->is_external($this->page_info['actual_catalog']);
			$full_search = isset($data['full_search'])?$data['full_search']:false;
			
			if (!is_array($data) || (!$data['search_for'] && !$full_search) || !is_array($data['fields']))
			{
			//	echo serialize(array(
			//		'msg'    => lang('Invalid parameters'),
			//		'status' => 'abort'
			//	));

			//	return array('error' => lang('Invalid parameters'));
				$rules = array(
					0 => array(
						'field' => $data['fields']['search'],
						'type'  => 'LIKE',
						'value' => '%'
					)
				);
			}


			/*
			 * TODO: look into the database to discover the database's encoding and convert the search_for field accordingly
			 */
			// Support search parameters with accentuation
			if ($this->page_info['actual_catalog']['class'] != 'bo_people_catalog' &&
/**rev 104**/
				//$this->page_info['actual_catalog']['class'] != 'bo_group_manager')
				$this->page_info['actual_catalog']['class'] != 'bo_group_manager' &&
				$this->page_info['actual_catalog']['class'] != 'bo_shared_people_manager' &&
				$this->page_info['actual_catalog']['class'] != 'bo_shared_group_manager')
/****/
			{

				$data['search_for'] = $data['search_for'];
			}

			$rules  = array();

			if ($data['search_for'] === '*')
			{
				$rules = array(
					0 => array(
						'field' => $data['fields']['search'],
						'type'  => 'LIKE',
						'value' => '%'
					)
				);
			}
			else
			{
				$names = explode(' ', $data['search_for']);

				if (!is_array($names))
				{
					if(!$full_search) {
						echo serialize(array(
							'msg'    => lang('Invalid Search Parameter'),
							'status' => 'abort'
						));
						exit;
					}
					else 
						$names = array();

				}

				if (!$external && $this->page_info['actual_catalog']['class'] != 'bo_people_catalog' &&
/**rev 104**/
					//$this->page_info['actual_catalog']['class'] != 'bo_group_manager')
					$this->page_info['actual_catalog']['class'] != 'bo_group_manager' &&
					$this->page_info['actual_catalog']['class'] != 'bo_shared_people_manager' &&
					$this->page_info['actual_catalog']['class'] != 'bo_shared_group_manager' )
/*****/
				{
					/*
					 * Restrict the returned contacts search to objectClass = phpgwAccount,
					 * must have attibute phpgwAccountStatus, phpgwAccountVisible != -1
					 */
					
					$rules = array(
						0 => array(
							'field' => 'contact.object_class',
							'type'  => '=',
							'value' => 'phpgwAccount'
						),
						1 => array(
							'field' => 'contact.account_status',
							'type'  => 'iLIKE',
							'value' => '%'
						),
/**rev 104**/
						///
						//1 => array(
						2 => array(
/*****/
							'field' => 'contact.account_visible',
							'type'  => '!=',
							'value' => '-1'
/**rev 104**/
	/*					),
						2 => array(
							'field' => 'contact.object_class',
							'type'  => '=',
							'value' => 'inetOrgPerson'
/****/
						),
					);

                                        if($full_search) {
                                            foreach($full_search as $field => $value) {
                                                    if(trim($value)!='')
                                                            array_push($rules,array(
                                                                                            'field' => $this->aux_full_search($field,true),
                                                                                            'type' => 'LIKE',
                                                                                            'value' => '*'.$value.'*'
                                                                                            ));
                                            }
                                        }

				}
				else if(!$external && $full_search) {
					
					foreach($full_search as $field => $value) {
						if(trim($value)!='')
							array_push($rules,array(
											'field' => $this->aux_full_search($field,false),
											'type' => 'iLIKE',
											'value' => '%'.$value.'%'
											));
					}
				
				}

				foreach ($names as $name)
				{
					if ($name != '')
					{
						array_push($rules, array(
							'field' => $data['fields']['search'],
							//'type'  => 'iLIKE',
							//'value' => '%'.$name.'%'
							'type'  => 'LIKE and ~=', 
 	                        'value' => $name 
						));
					}
				}
			}

			if ($external || $this->page_info['actual_catalog']['class'] == 'bo_people_catalog' ||
/**rev 104**/
				//$this->page_info['actual_catalog']['class'] == 'bo_group_manager')
				$this->page_info['actual_catalog']['class'] == 'bo_group_manager' ||
				$this->page_info['actual_catalog']['class'] == 'bo_shared_people_manager' ||
				$this->page_info['actual_catalog']['class'] == 'bo_shared_group_manager')


/***/
			{
				// Get only this attributes: dn, cn for external catalogs,
				// used to restrict the attributes used in filters
				$ids = $this->bo->find(array($data['fields']['id'], $data['fields']['search']), $rules, array('order' => $data['fields']['search'], 'sort' => 'ASC', 'customFilter' => $data['custom_filter'], 'CN' => $data['CN'], 'exact' => $data['exact']), $data['search_for'] != null);	}
			else
			{
				// Get only this attributes: dn, cn, phpgwAccountType, objectClass, phpgwAccountStatus, phpghAccountVisible
				// for non-external catalogs, used to restrict the attributes used in filters
				$ids = $this->bo->find(array(
					$data['fields']['id'],
					$data['fields']['search'],
					'contact.object_class',
					//'contact.account_status',
					'contact.account_visible',
					'contact.connection.mail',
					'contact.connection.phone'
					), $rules, array('order' => $data['fields']['search'], 'sort' => 'ASC', 'customFilter' => $data['custom_filter'], 'CN' => $data['CN'], 'exact' => $data['exact']), $data['search_for_area'], $data['search_for'] != null ); }

			if (!is_array($ids) || !count($ids))
			{
				$this->last_search_ids = null;
				$this->save_session();
				return null;
			}

			$id_field = substr($data['fields']['id'], strrpos($data['fields']['id'], '.')+1);

			$ids_f = array();

			foreach ($ids as $e_info)
			{
/**rev 104**/
				//$ids_f[] = $e_info[$id_field];
				if($this->page_info['actual_catalog']['class'] != 'bo_shared_people_manager' && $this->page_info['actual_catalog']['class'] != 'bo_shared_group_manager')
				{
					$ids_f[] = $e_info[$id_field];
				} else{
					$ids_f[] = array(0=>$e_info[$id_field],1=>$e_info['perms'],2=>$e_info['owner']);
				}
/****/
			}

			return $ids_f;
		}

		// CELEPAR
		/*
        function search($str_data)
        {
            $data = unserialize($str_data);

            if (!is_array($data) || !$data['search_for'] || !is_array($data['fields']))
            {
                echo serialize(array(
                    'msg'    => lang('Invalid parameters'),
                    'status' => 'abort'
                ));

                return;
            }

            $rules  = array();

            if ($data['search_for'] === '*')
            {
                $rules = array(
                    0 => array(
                        'field' => $data['fields']['search'],
                        'type'  => 'LIKE',
                        'value' => '%'
                    )
                );
            }
            else
            {
                $names = explode(' ', $data['search_for']);

                if (!is_array($names))
                {
                    echo serialize(array(
                        'msg'    => lang('Invalid Search Parameter'),
                        'status' => 'abort'
                    ));

                    return;
                }

                foreach ($names as $name)
                {
                    if ($name != '')
                    {
                        array_push($rules, array(
                            'field' => $data['fields']['search'],
                            'type'  => 'iLIKE',
                            'value' => '%'.$name.'%'
                        ));
                    }
                }
            }



            //$catalog = $this->bo->get_branch_by_level($this->bo->catalog_level[0]);

            //if ($catalog['class'] === 'bo_people_catalog')
            //{
            //    array_push($rules, array(
            //        'field' => 'contact.id_owner',
            //        'type'  => '=',
            //        'value' => $GLOBALS['phpgw_info']['user']['account_id']
            //    ));
            //}


            $ids = $this->bo->find(array($data['fields']['id'], $data['fields']['search']), $rules, array('order' => $data['fields']['search'], 'sort' => 'ASC'));

            if (!is_array($ids) || !count($ids))
            {
                echo serialize(array(
                    'msg'    => lang('No Entries Found!'),
                    'status' => 'empty'
                ));

                return;
            }
            $id_field = substr($data['fields']['id'], strrpos($data['fields']['id'], '.')+1);

            $ids_f = array();
            foreach ($ids as $e_info)
            {
                $ids_f[] = $e_info[$id_field];
            }

            echo serialize(array(
                'data'   => $ids_f,
                'msg'    => lang('Found %1 Entries', count($ids)),
                'status' => 'ok'
            ));

			return;
        }*/
		/*!

			@function get_multiple_entries
			@abstract Returns an array containing the specifiend data in the default
				CC UI format
			@author Raphael Derosso Pereira

			@param array str_data A serialized array containing the ID's of the entries
				to be taken, the fields to be taken and the rules to be used on the
				retrieval:
				$data = array(
					'ids'    => array(...),
					'fields' => array(...),
					'rules'  => array(...)
				);

		*/
		function get_multiple_entries($str_data)
		{
			$data = unserialize($str_data);

			if (!is_array($data) or !count($data) or !count($data['fields']) or !count($data['ids']))
			{
				return array(
					'msg'    => lang('Invalid Parameters'),
					'status' => 'abort'
				);
			}

			$entries = $this->bo->catalog->get_multiple_entries($data['ids'], $data['fields']);

			if (!is_array($entries) or !count($entries))
			{
				return array(
					'msg'    => lang('No Entries Found!'),
					'status' => 'empty'
				);
			}

			return array(
				'msg'    => lang('Found %1 Entries!', count($entries)),
				'status' => 'ok',
				'data'   => $entries
			);
		}

		/*

			@function get_all_entries
			@abstract Returns the specified fields for all catalog's entries
				in the default CC UI format
			@author Raphael Derosso Pereira

			@params array str_data A serialized array containing the fields to
				be grabbed, the maximum number of entries to be returned and a
				boolean specifying if the calls refers to a new grab or to an
				unfinished one.

		*/
		function get_all_entries($str_data)
		{
			$data = unserialize($str_data);

			if (!is_array($data) or
			    !count($data) or
				!count($data['fields']) or
				!$data['maxlength'] or
				(!$data['new'] and !$data['offset']))
			{
				return array(
					'msg'    => lang('Invalid Parameters'),
					'status' => 'abort'
				);
			}

			if ($data['new'])
			{
				$this->all_entries = $this->bo->catalog->get_all_entries_ids();

				$this->save_session();

				if (!is_array($this->all_entries) or !count($this->all_entries))
				{
					return array(
						'msg'    => lang('No Entries Found!'),
						'status' => 'empty'
					);
				}

				$data['offset'] = 0;
			}

			if ($data['maxlength'] != -1)
			{
				$result = $this->bo->catalog->get_multiple_entries(array_slice($this->all_entries, $data['offset'], $data['maxlength']), $data['fields']);
			}
			else
			{
				$result = $this->bo->catalog->get_multiple_entries($this->all_entries, $data['fields']);
			}

			$jsCode = array();
			$count = 0;
			foreach ($result as $each)
			{
				if (!is_array($each))
				{
					continue;
				}

				if($this-> typeContact == 'groups') {

					foreach ($each as $field => $value)	{

						if ($field === 'title')	{
							$optionName = '\\"'.$value.'\\"';

						}
						else if ($field === 'short_name')	{

							$jsCode[] = '_this.entries.options[_this.entries.options.length] = new Option("'.$optionName.' ('.$value.')", "'.$count.'");';
                            ++$count;
						}
					}
				}

				else  {
					foreach ($each as $field => $value)	{
						if ($field === 'names_ordered')	{
							 if(is_array($value))
                             	$value = $value[0];
							$name = '\\"'.$value.'\\"';
						}
						else if ($field === 'connections')	{

							foreach ($value as $connection)		{
								if ($connection['id_type'] == $this->preferences['personCardEmail'])	{
									$jsCode[] = '_this.entries.options[_this.entries.options.length] = new Option("'.$name.' <'.$connection['connection_value'].'>", "'.$count.'");';
                                    ++$count;
								}
							}
						}
					}
				}
			}

			$jsCodeFinal = implode("\n", $jsCode);

			$nEntries = count($result);

			if (!$nEntries)
			{
				return array(
					'msg'    => lang('Error while getting user information...'),
					'status' => 'abort'
				);
			}

			return array(
				'msg'      => lang('Found %1 Entries!', $nEntries),
				'status'   => 'ok',
				'typeContact'   => $this -> typeContact,
				'final'    => $nEntries + $data['offset'] < count($this->all_entries) ? false : true,
				'offset'   => $data['offset'] + $nEntries,
				'data'     => $jsCodeFinal
			);
		}

		/*********************************************************************\
		 *                      Auxiliar Methods                             *
		\*********************************************************************/

		/*!

			@function save_session
			@abstract Saves the data on the session
			@author Raphael Derosso Pereira

		*/
		function save_session()
		{
			$GLOBALS['phpgw']->session->appsession('ui_data.page_info','contactcenter',$this->page_info);
			$GLOBALS['phpgw']->session->appsession('ui_data.all_entries','contactcenter',$this->all_entries);
		}

		/*!

			@function convert_tree
			@abstract Converts the tree array in the BO format to a JS tree array compatible
				with the one available in eGW
			@author Raphael Derosso Pereira

			@param (array)  $tree    The tree in the BO format
			@param (string) $name    The tree name
			@param (string) $iconDir The dir where the icons are
			@param (string) $parent  The parent
		*/

		function convert_tree($tree, &$iconDir, $parent='0')
		{
//			echo "Entrou<br>\tPai: $parent <br>";
			$rtree = array();

			if ($parent === '0')
			{
//				echo 'Root!<br>';
				$rtree['0'] = array(
					'type'       => 'catalog_group',
					'id'         => '0',
					'pid'        => 'none',
					'caption'    => lang('Catalogues'),
					'class'      => 'bo_catalog_group_catalog',
					'class_args' => array('_ROOT_', '$this', '$this->get_branch_by_level($this->catalog_level[0])')
				);
			}

			foreach($tree as $id => $value)
			{
//				echo 'ID: '.$id.'<br>';
				$rtree[$parent.'.'.$id] = array(
					'type'    => $value['type'],
					'id'      => $parent.'.'.$id,
					'pid'     => $parent,
					'caption' => $value['name']
				);

				switch($value['type'])
				{
					case 'catalog_group':
					case 'mixed_catalog_group':
						$rtree = $rtree + $this->convert_tree($value['sub_branch'],$iconDir,$parent.'.'.$id);
						break;
				}
			}

			if (count($rtree))
			{
				return $rtree;
			}
		}

		function get_catalog_add_contact($id, $echo=true){

			$array_participants = array();
			if(!$this->bo->catalog->src_info) {
				$ldap = CreateObject('contactcenter.bo_ldap_manager');
				$this->bo->catalog->src_info = $ldap->srcs[1];
			}

			$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], true);
			$dn=$this->bo->catalog->src_info['dn'];
			$justThese = array("givenname","givenname","sn","telephonenumber","mail");
			$sr = ldap_read($ds,$id, "objectClass=*",$justThese);
			$info = ldap_get_entries($ds, $sr);
			for($z = 0; $z < 5; ++$z) {
				$participant = $info[0][$justThese[$z]];
				$participant[0] = utf8_decode($participant[0]);
				array_push($array_participants, $participant);
			}

			ldap_close($ds);
                        if ($echo)
                        {
                            echo serialize($array_participants);
                        }
                        else
                            {
                                return serialize($array_participants);
                            }
		}

		function get_catalog_participants_group($id)
		{
			if(!$this->bo->catalog->src_info) {
				$ldap = CreateObject('contactcenter.bo_ldap_manager');
				$this->bo->catalog->src_info = $ldap->srcs[1];
			}
			$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], true);
			$justThese = array("description","memberuid");
			$sr = ldap_read($ds,$id, "objectClass=*",$justThese);
			$info = ldap_get_entries($ds, $sr);
			$member_uids = $info[0]['memberuid'];
			$contact['names_ordered'] = $info[0]['description'];
			$filter = "";
            $member_uids_count = count($member_uids);
			for($z = 0; $z < $member_uids_count; ++$z) {
				if($member_uids[$z])
					$filter.="(uid=".$member_uids[$z].")";
			}
			$array_participants = array();
			if($filter) {
				$filter = "(|".$filter.")";
				$valarray = explode(',',$id);
				array_shift($valarray);
				$dn = implode(',',$valarray);
				$justThese = array("cn","mail");
				$sr = ldap_search($ds,$dn, $filter,$justThese);
				$info = ldap_get_entries($ds, $sr);
				for($z = 0; $z < $info['count']; ++$z) {
					$participant =  '<font color=\'DARKBLUE\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;'.$info[$z]['cn'][0].'&quot; &lt;'.$info[$z]['mail'][0].'&gt;</font><br>';
				    $array_emails[$info[$z]['mail'][0]] = null;
					array_push($array_participants, $participant);
				}
				ldap_close($ds);
			}
			sort($array_participants);
			$innerHTML = '';
			foreach($array_participants as $index => $participant){
				$innerHTML .= $participant;
			}
			$return = array('size' => count($array_participants), 'names_ordered'=> $contact['names_ordered'], 'inner_html' => $innerHTML);
			echo serialize($return);
		}

		function get_catalog_participants_list($id)
		{

			$fields = $this->bo->catalog->get_fields(false);
			$fields['names_ordered'] = true;
			$fields['mail_forwarding_address'] = true;
			$contact = $this->bo->catalog->get_single_entry($id,$fields);

			$array_participants = array();
			$array_emails = array();

			$filter = null;
			for($z = 0; $z < $contact['mail_forwarding_address']['count']; ++$z) {
					if(strstr($contact['mail_forwarding_address'][$z],'@')) {
						$filter.="(mail=".$contact['mail_forwarding_address'][$z].")";
						$array_emails[$contact['mail_forwarding_address'][$z]] = "<font color=black>".$contact['mail_forwarding_address'][$z]."</font>";
					}
					else
						$array_participants[$z] = "<font color=red>".$contact['mail_forwarding_address'][$z]."</font>";
			}

			if($filter) {
				$filter = "(|".$filter.")";
				if(!$this->bo->catalog->src_info) {
					$ldap = CreateObject('contactcenter.bo_ldap_manager');
					$this->bo->catalog->src_info = $ldap->srcs[1];
				}
				$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], true);
				$dn=$this->bo->catalog->src_info['dn'];
				$justThese = array("cn","mail");
				$sr = ldap_search($ds,$dn, $filter,$justThese);
				$info = ldap_get_entries($ds, $sr);
				for($z = 0; $z < $info['count']; ++$z) {
					$participant =  '<font color=\'DARKBLUE\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;'.$info[$z]['cn'][0].'&quot; &lt;'.$info[$z]['mail'][0].'&gt;</font><br>';
					$array_emails[$info[$z]['mail'][0]] = null;
					array_push($array_participants, $participant);
				}

				foreach($array_emails as $index => $email)
					if($email)
						array_push($array_participants, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$email."<br>");

				ldap_close($ds);
			}
			sort($array_participants);
			$innerHTML = '';
			foreach($array_participants as $index => $participant){
				$innerHTML .= $participant;
			}
			$return = array('size' => count($array_participants), 'names_ordered'=> $contact['names_ordered'], 'inner_html' => $innerHTML);
			echo serialize($return);
		}

		function export_contacts($typeExport){

			$boGroup = CreateObject('contactcenter.bo_group');
			$contacts = $boGroup->get_all_contacts();
			$streamBuffer = '';
			$stramArray = Array();

			if(!count($contacts))
				echo null;

			switch($typeExport) {

				case 'outlook_en':
					$streamBuffer = "Name;E-mail Address;Notes;Mobile Phone;Pager;Company;".
							"Job Title;Home Phone;Home Fax;Business Phone;Business Fax\r\n";							
					foreach($contacts as $index => $object){
						$streamBuffer.= "\"".$object[ 'names_ordered'] . "\";"
							. "\"".$object[ 'main-mail' ] . "\";"
							. "\"".str_replace("\r\n\x0a","\t",$object[ 'notes' ]) . "\";"
							. "\"".$object[ 'mobile' ] . "\";"
							. "\"".$object[ 'business-pager' ] . "\";"
							. "\"".$object[ 'corporate_name' ] . "\";"
							. "\"".$object[ 'job_title' ] . "\";"
							. "\"".$object[ 'home-phone' ] . "\";"
							. "\"".$object[ 'home-fax' ] . "\";"
							. "\"".$object[ 'business-phone' ] . "\";"
							. "\"".$object[ 'business-fax' ] . "\"\r\n";
					}
					break;

				case 'outlook_pt-BR':
					$streamBuffer = "Nome;Apelido;End. de email;Endereço residencial;"
									."Cidade do endereço residencial;CEP do endereço residencial;Estado;País/região do endereço residencial;"
									."Telefone residencial;Fax residencial;Telefone celular;Página pessoal da Web;Rua do endereço comercial;"
									."Cidade do endereço comercial;CEP do endereço comercial;Estado do endereço comercial;"
									."País/região do endereço comercial;Página comercial da Web;Telefone comercial;Fax comercial;Pager;Empresa;"
									."Cargo;Departamento;End. comercial;Observações\r\n";

					foreach($contacts as $index => $object){
						$streamBuffer.= "\"".$object[ 'names_ordered'] . "\";"
							. "\"".$object[ 'alias' ] . "\";"
							. "\"".$object[ 'main-mail' ] . "\";"
							. "\"".$object[ 'home-address' ] . "\";"
							. "\"".$object[ 'home-city_name' ] . "\";"
							. "\"".$object[ 'home-postal_code' ] . "\";"
							. "\"".$object[ 'home-state_name' ] . "\";"
							. "\"".$object[ 'home-id_country' ] . "\";"
							. "\"".$object[ 'home-phone' ] . "\";"
							. "\"".$object[ 'home-fax' ] . "\";"
							. "\"".$object[ 'mobile' ] . "\";"
							. "\"".$object[ 'web_page' ] . "\";"
							. "\"".$object[ 'business-address' ] . "\";"
							. "\"".$object[ 'business-city_name' ] . "\";"
							. "\"".$object[ 'business-postal_code' ] . "\";"
							. "\"".$object[ 'business-state_name' ] . "\";"
							. "\"".$object[ 'business-id_country' ] . "\";"
							. "\"".$object[ 'web_page' ] . "\";"
							. "\"".$object[ 'business-phone' ] . "\";"
							. "\"".$object[ 'business-fax' ] . "\";"
							. "\"".$object[ 'business-pager' ] . "\";"
							. "\"".$object[ 'corporate_name' ] . "\";"
							. "\"".$object[ 'job_title' ] . "\";"
							. "\"".$object[ 'department' ] . "\";"
							."\"\";"
							. "\"".str_replace("\r\n\x0a","\t",$object[ 'notes' ]) . "\"\r\n";
					}

				break;

				case 'outlook2000_pt-BR':
					$streamBuffer = "\"Tratamento\",\"Nome\",\"Segundo Nome\",\"Sobrenome\",\"Sufixo\",".
					"\"Empresa\",\"Departamento\",\"Cargo\",\"Rua do endereço comercial\",\"Rua do endereço comercial 2\",".
					"\"Rua do endereço comercial 3\",\"Cidade do endereço comercial\",\"Estado do endereço comercial\",".
					"\"CEP do endereço comercial\",\"País do endereço comercial\",\"Endereço residencial\",\"Rua residencial 2\",".
					"\"Rua residencial 3\",\"Cidade do endereço residencial\",\"Estado\",\"CEP do endereço residencial\",\"País do endereço residencial\",".
					"\"Outro endereço\",\"Outro endereço 2\",\"Outro endereço 3\",\"Cidade\",\"Estado\",\"CEP\",\"País\",".
					"\"Telefone do assistente\",\"Fax comercial\",\"Telefone comercial\",\"Telefone comercial 2\",\"Retorno de chamada\",".
					"\"Telefone do carro\",\"Telefone principal da empresa\",\"Fax residencial\",\"Telefone residencial\",".
					"\"Telefone residencial 2\",\"ISDN\",\"Telefone celular\",\"Outro fax\",\"Outro telefone\",\"Pager\",\"Telefone principal\",".
					"\"Radiofone\",\"Telefone TTY/TDD\",\"Telex\",\"Aniversário\",\"Anotações\",\"Caixa postal\",\"Categorias\",".
					"\"Código da empresa\",\"Código do governo\",\"Cônjuge\",\"Conta\",\"Endereço de correio eletrônico\",".
					"\"Nome de exibição do correio eletr.\",\"Endereço de correio eletrônico 2\",".
					"\"Nome de exibição do correio eletr.2\",\"Endereço de correio eletrônico 3\",".
					"\"Nome de exibição do correio eletr.3\",\"Datas especiais\",\"Disponibilidade da Internet\",".
					"\"Filhos\",\"Hobby\",\"Idioma\",\"Indicação\",\"Informações para cobrança\",\"Iniciais\",\"Local\",".
					"\"Nome do assistente\",\"Nome do gerenciador\",\"Página da Web\",\"Palavras-chave\",\"Particular\",\"Personalizado 1\",\"Personalizado 2\",".
					"\"Personalizado 3\",\"Personalizado 4\",\"Prioridade\",\"Profissão\",\"Quilometragem\",\"Sala\",\"Sensibilidade\",\"Servidor de diretório\",".
					"\"Sexo\"\r\n";



					foreach($contacts as $index => $object){
	                        $streamBuffer .= "\"".$object[ 'alias' ] . "\","	                            
								. "\"".$object[ 'names_ordered' ] . "\","
								.",,,"
								. "\"".$object[ 'corporate_name' ] . "\","
								. "\"".$object[ 'department' ] . "\","
								. "\"".$object[ 'job_title' ] . "\","
								. "\"".$object[ 'business-address' ] . "\","
								. "\"".$object[ 'business-address-2' ] . "\","
								.","
								. "\"".$object[ 'business-city_name' ] . "\","
								. "\"".$object[ 'business-state' ] . "\","
								. "\"".$object[ 'business-postal_code' ] . "\","
								. "\"".$object[ 'business-id_country' ] . "\","
								. "\"".$object[ 'home-address' ] . "\","
								. "\"".$object[ 'home-address-2' ] . "\","
								.","
								. "\"".$object[ 'home-city_name' ] . "\","
								. "\"".$object[ 'home-state_name' ] . "\","
								. "\"".$object[ 'home-postal_code' ] . "\","
								. "\"".$object[ 'home-id_country' ] . "\","
								.",,,,,,,,"
								. "\"".$object[ 'business-fax' ] . "\","
								. "\"".$object[ 'business-phone' ] . "\","
								. "\"".$object[ 'business-mobile' ] . "\","
								.",,,"
								. "\"".$object[ 'home-fax' ] . "\","
								. "\"".$object[ 'home-phone' ] . "\","
								.",,"
								. "\"".$object[ 'mobile' ] . "\","
								.",,"
								. "\"".$object[ 'home-pager' ] . "\","
								. "\"".$object[ 'business-phone' ] . "\","
								.",,,"
								. "\"".$object[ 'birthdate' ] . "\","
								. "\"".str_replace("\r\n\x0a","\t",$object[ 'notes' ]) . "\","
								.",,,,,,"
								. "\"".$object[ 'main-mail' ] . "\","
								.","
								. "\"".$object[ 'alternative-mail' ] . "\","
								.",,,,,,,,,,,,,,"
								. "\"".$object[ 'web_page' ] . "\","
								.",,,,,,,,,,,,"
								. "\"".$object[ 'sex' ] . "\"\r\n";								
	                    }
				break;

				case 'outlook2000_en':
					$streamBuffer = "Title,First Name,Middle Name,Last Name,Suffix,Company,Department,Job Title,".
					"Business Street,Business Street 2,Business Street 3,Business City,Business State,Business Postal Code,".
					"Business Country,Home Street,Home Street 2,Home Street 3,Home City,Home State,Home Postal Code,Home Country,".
					"Other Street,Other Street 2,Other Street 3,Other City,Other State,Other Postal Code,Other Country,".
					"Assistant's Phone,Business Fax,Business Phone,Business Phone 2,Callback,Car Phone,Company Main Phone,Home Fax,".
					"Home Phone,Home Phone 2,ISDN,Mobile Phone,Other Fax,Other Phone,Pager,Primary Phone,Radio Phone,TTY/TDD Phone,Telex,".
					"Account,Anniversary,Assistant's Name,Billing Information,Birthday,Categories,Children,Directory Server,E-mail Address,".
					"E-mail Type,E-mail Display Name,E-mail 2 Address,E-mail 2 Type,E-mail 2 Display Name,E-mail 3 Address,E-mail 3 Type,E-mail 3 Display Name,".
					"Gender,Government ID Number,Hobby,Initials,Internet Free Busy,Keywords,Language,Location,Manager's Name,Mileage,Notes,".
					"Office Location,Organizational ID Number,PO Box,Priority,Private,Profession,Referred By,Sensitivity,Spouse,User 1,User 2,User 3,User 4,Web Page\r\n";

				foreach($contacts as $index => $object)
                                {
                                        if( array_key_exists("phone", $object) )
                                           $phone = $object['phone'];
                                        else
                                           $phone = $object['business-phone'];

						$streamBuffer.= "\"".$object[ 'alias' ] . "\","
							. "\"".$object[ 'names_ordered'] . "\","
							.",,,"
							. "\"".$object[ 'corporate_name' ] . "\","
							. "\"".$object[ 'department' ] . "\","
							. "\"".$object[ 'job_title' ] . "\","
							. "\"".$object[ 'business-address' ] . "\","
							. "\"".$object[ 'business-address-2' ] . "\","
							.","
							. "\"".$object[ 'business-city_name' ] . "\","
							. "\"".$object[ 'business-state' ] . "\","
							. "\"".$object[ 'business-postal_code' ] . "\","
							. "\"".$object[ 'business-id_country' ] . "\","
							. "\"".$object[ 'home-address' ] . "\","
							. "\"".$object[ 'home-address-2' ] . "\","
							.","
							. "\"".$object[ 'home-city_name' ] . "\","
							. "\"".$object[ 'home-state_name' ] . "\","
							. "\"".$object[ 'home-postal_code' ] . "\","
							. "\"".$object[ 'home-id_country' ] . "\","
							.",,,,,,,,"
							. "\"".$object[ 'business-fax' ] . "\","
                                                        . "\"".$phone . "\","
							. "\"".$object[ 'business-mobile' ] . "\","
							.",,,"
							. "\"".$object[ 'home-fax' ] . "\","
							. "\"".$object[ 'home-phone' ] . "\","
							.",,"
							. "\"".$object[ 'mobile' ] . "\","
							.",,"
							. "\"".$object[ 'business-pager' ] . "\","
							. "\"".$object[ 'home-pager' ] . "\","
							.",,,,,,,,"
							. "\"".$object[ 'birthdate' ] . "\","
							.",,,"
							. "\"".$object[ 'main-mail' ] . "\","
							.",,"
							. "\"".$object[ 'alternative-mail' ] . "\","
							.",,,,,,,,,,,,,,,"
							. "\"".str_replace("\r\n\x0a","\t",$object[ 'notes' ]) . "\","
							.",,,,,,,,,,,,,"
							. "\"".$object[ 'web_page' ] . "\"\r\n";

					}
				break;

				case 'thunderbird':
					$streamBuffer = "First Name,Last Name,Display Name,Nickname,Primary Email,Secondary Email,"
						."Screen Name,Work Phone,Home Phone,Fax Number,Pager Number,Mobile Number,Home Address,"
						."Home Address 2,Home City,Home State,Home ZipCode,Home Country,Work Address,Work Address 2,"
						."Work City,Work State,Work ZipCode,Work Country,Job Title,Department,Organization,Web Page 1,"
						."Web Page 2,Birth Year,Birth Month,Birth Day,Custom 1,Custom 2,Custom 3,Custom 4,Notes,\r\n";

					foreach($contacts as $index => $object){
						$array_birth = explode("-",$object[ 'birthdate' ]);

						$stramArray[0] = "\"".$object[ 'names_ordered'] . "\"";
						$stramArray[1] = "";
						$stramArray[2] = "\"".$object[ 'names_ordered'] . "\"";
						$stramArray[3] = "\"".$object[ 'alias' ] . "\"";
						$stramArray[4] = "\"".$object[ 'main-mail' ] . "\"";
						$stramArray[5] = "\"".$object[ 'alternative-mail' ] . "\"";
						$stramArray[6] = "";
						$stramArray[7] = "\"".$object[ 'business-phone' ] . "\"";
						$stramArray[8] = "\"".$object[ 'home-phone' ] . "\"";
						$stramArray[9] = "\"".$object[ 'business-fax' ] . "\"";
						$stramArray[10] = "\"".$object[ 'business-pager' ] . "\"";
						$stramArray[11] = "\"".$object[ 'mobile' ] . "\"";
						$stramArray[12] = "\"".$object[ 'home-address' ] . "\"";
						$stramArray[13] = "\"".$object[ 'home-address-2' ] . "\"";
						$stramArray[14] = "\"".$object[ 'home-city_name' ] . "\"";
						$stramArray[15] = "\"".$object[ 'home-state_name' ] . "\"";
						$stramArray[16] = "\"".$object[ 'home-postal_code' ] . "\"";
						$stramArray[17] = "\"".$object[ 'home-id_country' ] . "\"";
						$stramArray[18] = "\"".$object[ 'business-address' ] . "\"";
						$stramArray[19] = "\"".$object[ 'business-address-2' ] . "\"";
						$stramArray[20] = "\"".$object[ 'business-city_name' ] . "\"";
						$stramArray[21] = "\"".$object[ 'business-state_name' ] . "\"";
						$stramArray[22] = "\"".$object[ 'business-postal_code' ] . "\"";
						$stramArray[23] = "\"".$object[ 'business-id_country' ] . "\"";
						$stramArray[24] = "\"".$object[ 'job_title' ] . "\"";
						$stramArray[25] = "\"".$object[ 'department' ] . "\"";
						$stramArray[26] = "\"".$object[ 'corporate_name' ] . "\"";
						$stramArray[27] = "";
						$stramArray[28] = "";
						$stramArray[29] = "\"".$array_birth[0] . "\"";
						$stramArray[30] = "\"".$array_birth[1] . "\"";
						$stramArray[31] = "\"".$array_birth[2] . "\"";
						$stramArray[32] = "";
						$stramArray[33] = "";
						$stramArray[34] = "";
						$stramArray[35] = "";
						$stramArray[36] = "";
						$stramArray[37] = "\"".str_replace("\r\n\x0a","\t",$object[ 'notes' ]) . "\"\r\n";
						
						if($object[ 'phone' ]){
							if(!$object[ 'home-phone' ])
								$stramArray[8] = "\"".$object[ 'phone' ]. "\"";
							else if(!$object[ 'mobile' ])
								$stramArray[11] = "\"".$object[ 'phone' ]. "\"";
							else if(!$object[ 'business-phone' ])
								$stramArray[7] = "\"".$object[ 'phone' ]. "\"";
						}
						
						$streamBuffer .= implode("," , $stramArray);
					}
				break;

				case 'expresso':
	                    $streamBuffer = 'Nome,Apelido,E-mail Principal,E-mail Alternativo,Celular,'
	                        . 'Telefone Comercial,Endereço Comercial,Complemento End. Comercial,CEP Comercial,Cidade End. Comercial,Estado End. Comercial,País End. Comercial,'
	                        . 'Telefone Residencial,Endereço Residencial,Complemento End. Residencial,CEP Residencial,Cidade End. Residencial,Estado End. Residencial,País End. Residencial,'
	                        . 'Aniversário,Sexo,Assinatura GPG,Notas,Página Web,Empresa,Cargo,Departamento,Fax Comercial,Pager Comercial,Celular Comercial,Fax,Pager,Endereço Comercial 2,Endereço Residencial 2'
							. "\r\n";

	                    foreach($contacts as $index => $object){
								$stramArray[0] = "\"".$object[ 'names_ordered'] . "\"";
								$stramArray[1] = "\"".$object[ 'alias' ] . "\"";
								$stramArray[2] = "\"".$object[ 'main-mail' ] . "\"";
								$stramArray[3] = "\"".$object[ 'alternative-mail' ] . "\"";
								$stramArray[4] = "\"".$object[ 'mobile' ] . "\"";
								$stramArray[5] = "\"".$object[ 'business-phone' ] . "\"";
								$stramArray[6] = "\"".$object[ 'business-address' ] . "\"";
								$stramArray[7] = "\"".$object[ 'business-complement' ] . "\"";
								$stramArray[8] = "\"".$object[ 'business-postal_code' ] . "\"";
								$stramArray[9] = "\"".$object[ 'business-city_name' ] . "\"";
								$stramArray[10] = "\"".$object[ 'business-state_name' ] . "\"";
								$stramArray[11] = "\"".$object[ 'business-id_country' ] . "\"";
								$stramArray[12] = "\"".$object[ 'home-phone' ] . "\"";
								$stramArray[13] = "\"".$object[ 'home-address' ] . "\""; 
								$stramArray[14] = "\"".$object[ 'home-complement' ] . "\"";
								$stramArray[15] = "\"".$object[ 'home-postal_code' ] . "\"";
								$stramArray[16] = "\"".$object[ 'home-city_name' ] . "\"";
								$stramArray[17] = "\"".$object[ 'home-state_name' ] . "\"";
								$stramArray[18] = "\"".$object[ 'home-id_country' ] . "\"";
								$stramArray[19] = "\"".$object[ 'birthdate' ] . "\"";
								$stramArray[20] = "\"".$object[ 'sex' ] . "\"";
								$stramArray[21] = "\"".$object[ 'pgp_key' ] . "\"";
								$stramArray[22] = "\"".str_replace("\r\n\x0a","\t",$object[ 'notes' ]) . "\"";
								$stramArray[23] = "\"".$object[ 'web_page' ] . "\"";
								$stramArray[24] = "\"".$object[ 'corporate_name' ] . "\"";
								$stramArray[25] = "\"".$object[ 'job_title' ] . "\"";
								$stramArray[26] = "\"".$object[ 'department' ] . "\"";
								$stramArray[27] = "\"".$object[ 'business-fax' ] . "\"";
								$stramArray[28] = "\"".$object[ 'business-pager' ] . "\"";
								$stramArray[29] = "\"".$object[ 'business-mobile' ] . "\"";
								$stramArray[30] = "\"".$object[ 'home-fax' ] . "\"";
								$stramArray[31] = "\"".$object[ 'home-pager' ] . "\"";
								$stramArray[32] = "\"".$object[ 'business-address-2' ] . "\"";
								$stramArray[33] = "\"".$object[ 'home-address-2' ] . "\"\r\n";
								
								if($object[ 'phone' ]){
									if(!$object[ 'home-phone' ])
										$stramArray[12] = "\"".$object[ 'phone' ]. "\"";
									else if(!$object[ 'mobile' ])
										$stramArray[4] = "\"".$object[ 'phone' ]. "\"";
									else if(!$object[ 'business-phone' ])
										$stramArray[5] = "\"".$object[ 'phone' ]. "\"";
								}
						
								$streamBuffer .= implode("," , $stramArray);
	                    }
	                break;
					
					case 'outlook2003':
					$streamBuffer = "\"Tratamento\",\"Primeiro nome\",\"Segundo Nome\",\"Sobrenome\",\"Sufixo\",".
					"\"Empresa\",\"Departamento\",\"Cargo\",\"Business Street\",\"Rua do endereço comercial 2\",".
					"\"Rua do endereço comercial 3\",\"Business City\",\"Business State\",".
					"\"Business Postal Code\",\"Business Country\",\"Endereço residencial\",\"Endereço residencial 2\",".
					"\"Endereço residencial 3\",\"Cidade do endereço residencial\",\"Estado\",\"CEP do endereço residencial\",\"País do endereço residencial\",".
					"\"Outro endereço\",\"Outro endereço 2\",\"Outro endereço 3\",\"Cidade\",\"Estado\",\"CEP\",\"País\",".
					"\"Telefone do assistente\",\"Fax comercial\",\"Telefone comercial\",\"Telefone comercial 2\",\"Retorno de chamada\",".
					"\"Telefone do carro\",\"Telefone principal da empresa\",\"Fax residencial\",\"Telefone residencial\",".
					"\"Telefone residencial 2\",\"ISDN\",\"Telefone celular\",\"Outro fax\",\"Outro telefone\",\"Pager\",\"Telefone principal\",".
					"\"Radiofone\",\"Telefone TTY/TDD\",\"Telex\",\"Anotações\",\"Birthday\",\"Caixa postal de outro endereço\",\"Caixa postal do endereço comercial\",".
					"\"Caixa postal do endereço residencial\",\"Categorias\",\"Código da empresa\",\"Código do governo\",\"Conta\",\"Datas especiais\",\"Disponibilidade da internet\",\"E-mail Address\",".
					"\"Tipo de email\",\"Nome para exibição do email\",\"Endereço de email 2\",".
					"\"Tipo de email 2\",\"Nome para exibição do email 2\",".
					"\"Endereço de email 3\",\"Tipo de email 3\",\"Nome para exibição de email 3\",".
					"\"Filhos\",\"Hobby\",\"Idioma\",\"Indicação\",\"Informações para cobrança\",\"Iniciais\",\"Local\",".
					"\"Nome do assistente\",\"Nome do gerenciador\",\"Página da Web\",\"Palavras-chave\",\"Particular\",\"Personalizado 1\",\"Personalizado 2\",".
					"\"Personalizado 3\",\"Personalizado 4\",\"Prioridade\",\"Profissão\",\"Quilometragem\",\"Sala\",\"Sensibilidade\",\"Servidor de diretório\",".
					"\"Sexo\",\"Spouse\"\r\n";
					foreach($contacts as $index => $object){
						$stramArray[0] = "\"".$object[ 'alias' ] . "\"";
						$stramArray[1] = "\"".$object[ 'names_ordered'] . "\"";
						$stramArray[2] = "";
						$stramArray[3] = "";
						$stramArray[4] = "";												
						$stramArray[5] = "\"".$object[ 'corporate_name' ] . "\"";			
						$stramArray[6] = "\"".$object[ 'department' ] . "\"";
						$stramArray[7] = "\"".$object[ 'job_title' ] . "\"";				
						$stramArray[8] = "\"".$object[ 'business-address' ] . "\"";
						$stramArray[9] = "\"".$object[ 'business-address-2' ] . "\"";
						$stramArray[10] = "";
						$stramArray[11] = "\"".$object[ 'business-city_name' ] . "\"";		
						$stramArray[12] = "\"".$object[ 'business-state_name' ] . "\"";		
						$stramArray[13] = "\"".$object[ 'business-postal_code' ] . "\"";	
						$stramArray[14] = "\"".$object[ 'business-id_country' ] . "\"";		
						$stramArray[15] = "\"".$object[ 'home-address' ] . "\"";			
						$stramArray[16] = "\"".$object[ 'business-address-2' ] . "\"";		
						$stramArray[17] = "";												
						$stramArray[18] = "\"".$object[ 'home-city_name' ] . "\"";
						$stramArray[19] = "\"".$object[ 'home-state_name' ] . "\"";
						$stramArray[20] = "\"".$object[ 'home-postal_code' ] . "\"";
						$stramArray[21] = "\"".$object[ 'home-id_country' ] . "\"";
						$stramArray[22] = "";
						$stramArray[23] = "";
						$stramArray[24] = "";
						$stramArray[25] = "\"".$object[ 'home-city_name' ] . "\"";
						$stramArray[26] = "\"".$object[ 'home-state_name' ] . "\"";
						$stramArray[27] = "\"".$object[ 'home-postal_code' ] . "\"";
						$stramArray[28] = "\"".$object[ 'home-id_country' ] . "\"";
						$stramArray[29] = "";
						$stramArray[30] = "\"".$object[ 'business-fax' ] . "\"";
						$stramArray[31] = "\"".$object[ 'business-phone' ] . "\"";
						$stramArray[32] = "\"".$object[ 'mobile' ] . "\"";
						$stramArray[33] = "";
						$stramArray[34] = "";
						$stramArray[35] = "";
						$stramArray[36] = "\"".$object[ 'home-fax' ] . "\"";
						$stramArray[37] = "\"".$object[ 'home-phone' ] . "\"";
						$stramArray[38] = "";
						$stramArray[39] = "";
						$stramArray[40] = "\"".$object[ 'mobile' ] . "\"";
						$stramArray[41] = "";
						$stramArray[42] = "";
						$stramArray[43] = "\"".$object[ 'home-pager' ] . "\"";
						$stramArray[44] = "\"".$object[ 'phone' ] . "\"";
						$stramArray[45] = "";
						$stramArray[46] = "";
						$stramArray[47] = "";
						$stramArray[48] = "\"".str_replace("\r\n\x0a","\t",$object[ 'notes' ]) . "\"";
						$stramArray[49] = "\"".$object[ 'birthdate' ] . "\"";
						$stramArray[50] = "";
						$stramArray[51] = "";
						$stramArray[52] = "";
						$stramArray[53] = "";
						$stramArray[54] = "";
						$stramArray[55] = "";
						$stramArray[56] = "";
						$stramArray[57] = "";
						$stramArray[58] = "";
						$stramArray[59] = "\"".$object[ 'main-mail' ] . "\"";
						$stramArray[60] = "";
						$stramArray[61] = "";
						$stramArray[62] = "\"".$object[ 'alternative-mail' ] . "\"";
						$stramArray[63] = "";
						$stramArray[64] = "";
						$stramArray[65] = "";
						$stramArray[66] = "";
						$stramArray[67] = "";
						$stramArray[68] = "";
						$stramArray[69] = "";
						$stramArray[70] = "";
						$stramArray[71] = "";
						$stramArray[72] = "";
						$stramArray[73] = "";
						$stramArray[74] = "";
						$stramArray[75] = "";
						$stramArray[76] = "";
						$stramArray[77] = "\"".$object[ 'web_page' ] . "\"";
						$stramArray[78] = "";
						$stramArray[79] = "";
						$stramArray[80] = "";
						$stramArray[81] = "";
						$stramArray[82] = "";
						$stramArray[83] = "";
						$stramArray[84] = "";
						$stramArray[85] = "";
						$stramArray[86] = "";
						$stramArray[87] = "";
						$stramArray[88] = "";
						$stramArray[89] = "";
						$stramArray[90] = "\"".$object[ 'sex' ] . "\"";
						$stramArray[91] = "\r\n";
						
						$streamBuffer .= implode("," , $stramArray);
	                    }
	                break;

			}

			$file = "contacts_".md5(microtime()).".swp";
			$tempDir = $GLOBALS['phpgw_info']['server']['temp_dir'];
			$f = fopen($tempDir.'/'.$file,"w");
			if(!$f)
				echo null;

			fputs($f,$streamBuffer);
			fclose($f);

			echo $file;
		}

		// Get the csv field and put into array, from php.net
		function parse_line($input_text, $delimiter = ',', $text_qualifier = '"') {
  			$text = trim($input_text);
  			  if(is_string($delimiter) && is_string($text_qualifier)) {
       			 $re_d = '\x' . dechex(ord($delimiter));            //format for regexp
        		$re_tq = '\x' . dechex(ord($text_qualifier));    //format for regexp

        		$fields = array();
        		$field_num = 0;
        		while(strlen($text) > 0) {
            		if($text{0} == $text_qualifier) {
                		preg_match('/^' . $re_tq . '((?:[^' . $re_tq . ']|(?<=\x5c)' . $re_tq . ')*)' . $re_tq . $re_d . '?(.*)$/', $text, $matches);

        		        $value = str_replace('\\' . $text_qualifier, $text_qualifier, $matches[1]);
             			$text = trim($matches[2]);

                		$fields[++$field_num] = $value;
            		} else {
                		preg_match('/^([^' . $re_d . ']*)' . $re_d . '?(.*)$/', $text, $matches);

        		        $value = $matches[1];
               			$text = trim($matches[2]);

              		  	$fields[++$field_num] = $value;
            	}
        	}
        		return $fields;
		    } else
       			return false;
		}

		//funcao alterada para importar outros campos alem de nome, telefone e email, de arquivo csv (Outlook 2000)
		//em 08/04/2009 - Rommel Cysne (rommel.cysne@serpro.gov.br);
		//Foi adicionada uma funcao (escapesheelcmd()) nas variaveis para que caracteres especiais sejam ignorados
		//durante a importacao dos contatos; o processo estava travando por causa de caracteres em campos como nome,
		//sobrenome, notas e e-mail;
		//em 19/06/2009 - Rommel Cysne (rommel.cysne@serpro.gov.br);
		function import_contacts($typeImport, $id_group=false)
		{
			$this->so_group = CreateObject('contactcenter.so_group');
			if($file = $_SESSION['contactcenter']['importCSV']) 
			{
				unset($_SESSION['contactcenter']['importCSV']);
				$len = filesize($file);
				$count = 0;
				$return = array('error' => false, '_new' => 0, '_existing' => 0, '_failure' => 0);
   				$handle = @fopen($file, "r") or die(serialize($return['error'] = true));

				$input_header = fgets($handle);
				if ($typeImport == 'outlook')
					$delim = ';';
				else if ($typeImport == 'auto' || $typeImport== 'thunderbird')
				$delim = strstr($input_header,',') ? ',' : ';';
				else
					$delim = ',';
				$csv_header = $this->parse_line($input_header,$delim);
				$firstContact = fgets($handle);
				preg_match("/\"(.+)\"[,;]/sU",$firstContact,$matches); // yahoo csv
   				rewind($handle);

   				$header = @fgetcsv($handle, $len, $delim) or die(serialize($return['error'] = true));
   				if(count($header)  < 2 || count($header) > 100) {
   					$return['error'] = true;
   					$return['sizeheader'] = count($header);
   					echo serialize($return);
   					return;
   				}

   				if ($matches[0][strlen($matches[0])-1] == ';')
					$delim = ';';

				$boGroup = CreateObject('contactcenter.bo_group');
				$boPeople = CreateObject('contactcenter.bo_people_catalog');
					switch($typeImport){
						case 'outlook2000':
							$name_pos=1;
							$name2_pos=2;
							$name3_pos=3;
							$corporate_street_pos=8;
							$cep_pos=13;
							$corporate_street_2_pos=22;
							$fax_pos=30;
							$phone_pos=31;
							$home_phone_pos=37;
							$personal_cell_pos=40;
							$pager_pos=43;
							$birth_pos=48;
							$notes_pos=49;
							$email_pos=56;
							$aditionalEmail_pos=59;

							break;
						case 'outlook':
							$name_pos=0;
							$email_pos=2;
							$phone_pos=8;
							$home_phone_pos=9;
							$personal_cell_pos=10;
							$corporate_street_pos=12;
							$cep_pos=14;
							$corporate_phone_pos=18;
							$fax_pos=19;
							$pager_pos=20;
							$notes_pos=25;
							break;
						case 'thunderbird':
							$name_pos=2;
							$alias_pos=3;
							//MAILS
							$email_pos=4;
							$aditionalEmail_pos=5;
							
							//PHONES
							$corporate_phone_pos=7; 
							$main_phone_pos=8;
							$home_phone_pos=8;
							$fax_pos=9;
							$pager_pos=10;
							$personal_cell_pos=11;
							
							//ADRESS
							$street_pos=12;
							$street_2_pos = 13;
							$city_pos=14;
							$state_pos=15;
							$cep_pos=16;
							$country_pos=17;
							
							//CORPORATE ADRESS
							$corporate_street_pos=18;
							$corporate_city_pos=20;
							$corporate_state_pos=21;
							$corporate_cep_pos=22;
							$corporate_country_pos=23;
							
							//CORPORATE DETAILS
							$job_title_pos=24;
							$department_pos=25;
							$corporate_name_pos=26;
							
							$notes_pos=36;
							break;
						case 'outlook2003':
							$alias_pos=0;
							$name_pos=1;
							//CORPORATE DETAILS
							$corporate_name_pos=5;
							$department_pos=6;
							$job_title_pos=7;
							//CORPORATE ADRESS
							$corporate_comp_pos=7;
							$corporate_street_pos=8;
							$street_2_pos = 9;	
							$corporate_city_pos=11;
							$corporate_state_pos=12;
							$corporate_cep_pos=13;
							$corporate_country_pos=14;
							//HOME ADRESS
							$street_pos=13;
							$comp_pos=14;
							$cep_pos=15;
							$city_pos=16;
							$state_pos=17;
							$country_pos=18;
							//PHONE's
							$corporate_phone_pos=31;
							$fax_pos=36;
							$home_phone_pos=37;
							$pager_pos=43;
							$main_phone_pos=44;
							//OTHER's
							$notes_pos=48;
							$birth_pos=49;
							//EMAIL's
							$email_pos=59;
							$aditionalEmail_pos=62;
							break;
						case 'expresso':
							$name_pos=0;
							$alias_pos=1;
							$email_pos=2;
							$aditionalEmail_pos=3;
							$personal_cell_pos=4;
							$corporate_phone_pos=5;
							$corporate_street_pos=6;
							$corporate_comp_pos=7;
							$corporate_cep_pos=8;
							$corporate_city_pos=9;
							$corporate_state_pos=10;
							$corporate_country_pos=11;
							$home_phone_pos=12;
							$street_pos=13;
							$comp_pos=14;
							$cep_pos=15;
							$city_pos=16;
							$state_pos=17;
							$country_pos=18;
							$birth_pos=19;
							$sex_pos = 20;
							$pgp_key_pos = 21;
							$notes_pos=22;
							$web_page_pos=23;
							$corporate_name_pos=24;
							$job_title_pos=25;
							$department_pos=26;
							$corporate_fax_pos=27;
							$corporate_pager_pos=28;
							$corporate_cell_pos=29;
							$fax_pos=30;
							$pager_pos=31;
							$corporate_street_2_pos = 32;
							$street_2_pos = 33;						
							break;
					default:
							foreach($csv_header as $index => $fieldName)
							{
								switch($fieldName){
								case 'Name':
								case 'Nome':
								case 'First Name':
									$name_pos = $index;
									break;
								case 'Second name':
								case 'Segundo nome':
									$name2_pos = $index;
									break;
								case 'Sobrenome':
								case 'Surname':
									$name3_pos = $index;
									break;
								case 'Business Street':
								case 'Rua do endereço comercial':
									$corporate_street_pos = $index;
									break;
								case 'Rua do endereço comercial 2':
								case 'Outro endereço':
									$corporate_street_2_pos = $index;
									break;
								case 'Business Postal Code':
								case 'CEP do endereço comercial':
									$cep_pos = $index;
									break;
								case 'Business Fax':
								case 'Fax comercial':
								case 'Fax':
									$fax_pos = $index;
									break;
								case 'Home Phone':
								case 'Telefone residencial':
									$home_phone_pos = $index;
									break;
								case 'Mobile phone':
								case 'Telefone celular':
									$personal_cell_pos = $index;
									break;
								case 'Pager':
									$pager_pos = $index;
									break;
								case 'Phone':
								case 'Business Phone':
								case 'Telefone':
								case 'Telefone principal':
								case 'Telefone comercial':
                                                                case 'Telefone Comercial':                                                                    
									$phone_pos = $index;
									break;
								case 'Aniversário':
								case 'Birthdate':
									$birth_pos = $index;
								case 'Anotações':
								case 'Notes':
									$notes_pos = $index;
								case 'E-mail':
								case 'Email':
								case 'E-mail Address':
								case 'Endereço de correio eletrônico':
								case 'End. de email':
									$email_pos = $index;
									break;
								case 'Endereço de correio eletrônico 2':
									$aditionalEmail_pos = $index;
									break;
								}
							}
							break;
				}


		
				while (($data = fgetcsv($handle, $len, $delim))) 
				{
					foreach ($header as $key=>$heading)
               			$row[$heading]=(isset($data[$key])) ? $data[$key] : '';

						$sdata = array();
						$full_name               = addslashes(trim($row[$header[$name_pos]]));
						$email		             = addslashes(trim($row[$header[$email_pos]]));
						$phone		             = addslashes(trim($row[$header[$main_phone_pos]]));
						$name2	               	 = addslashes(trim($row[$header[$name2_pos]]));
						$name3		             = addslashes(trim($row[$header[$name3_pos]]));
						$birth		             = addslashes(trim($row[$header[$birth_pos]]));
						$notes 					 = addslashes(trim($row[$header[$notes_pos]]));
						$altEmail			 	 = addslashes(trim($row[$header[$altEmail_pos]]));
						$sdata['alias']          = addslashes(trim($row[$header[$alias_pos]]));
						$sdata['corporate_name'] = addslashes(trim($row[$header[$corporate_name_pos]]));
						$sdata['job_title']      = addslashes(trim($row[$header[$job_title_pos]]));
						$sdata['department']     = addslashes(trim($row[$header[$department_pos]]));
						$sdata['web_page']		 = addslashes(trim($row[$header[$web_page_pos]]));
						$sdata['sex']         	 = addslashes(trim($row[$header[$sex_pos]]));
						$sdata['pgp_key']      	 = addslashes(trim($row[$header[$pgp_key_pos]]));
						
					$array_name = explode(' ', str_replace('"','',(str_replace('\'','',$full_name))));
					$sdata['given_names'] = addslashes($array_name[0]);
					$array_name[0] = null;
					$sdata['family_names'] = trim(implode(' ',$array_name));
					if($sdata['family_names'] == '')
					{
						$sdata['family_names'] = addslashes($name2) . " " . addslashes($name3);
					}

						$sdata['connections']['default_email']['connection_name'] = lang('Main');
						$sdata['connections']['default_email']['connection_value'] = addslashes($email);
						$sdata['connections']['aditional_email']['connection_name'] = "Alternativo";
						$sdata['connections']['aditional_email']['connection_value'] = trim($row[$header[$aditionalEmail_pos]]);

						$sdata['connections']['default_phone']['connection_name'] = lang('Main');
                                                $sdata['connections']['default_phone']['connection_value'] = $phone;

						if( trim($row[$header[$home_phone_pos]]) != "" )
                                                {
                                                    $sdata['connections']['aditional_phone']['home_phone']['connection_name'] = 'Casa';
                                                    $sdata['connections']['aditional_phone']['home_phone']['connection_value'] = trim($row[$header[$home_phone_pos]]);
                                                }

                                                if( trim($row[$header[$personal_cell_pos]]) != "" )
                                                {
                                                    $sdata['connections']['aditional_phone']['cellphone']['connection_name'] = 'Celular';
                                                    $sdata['connections']['aditional_phone']['cellphone']['connection_value'] = trim($row[$header[$personal_cell_pos]]);
                                                }

                                                if( trim($row[$header[$corporate_phone_pos]]) != "")
                                                {
                                                    $sdata['connections']['aditional_phone']['corporate_phone']['connection_name'] = 'Trabalho';
                                                    $sdata['connections']['aditional_phone']['corporate_phone']['connection_value'] = trim($row[$header[$corporate_phone_pos]]);
                                                }

                                                if( trim($row[$header[$fax_pos]]) != "" )
                                                {
                                                    $sdata['connections']['aditional_phone']['fax']['connection_name'] = 'Fax';
                                                    $sdata['connections']['aditional_phone']['fax']['connection_value'] = trim($row[$header[$fax_pos]]);
                                                }

                                                if ($GLOBALS['phpgw_info']['server']['personal_contact_type'] == 'True')
                                                {
                                                    if( trim($row[$header[$pager_pos]]) != "" )
                                                    {
                                                        $sdata['connections']['aditional_phone']['pager']['connection_name'] = 'Pager';
                                                        $sdata['connections']['aditional_phone']['pager']['connection_value'] = trim($row[$header[$pager_pos]]);
                                                    }

                                                    if( trim($row[$header[$corporate_fax_pos]]) != "" )
                                                    {
                                                        $sdata['connections']['aditional_phone']['corporate_fax']['connection_name'] = 'Fax Corporativo';
                                                        $sdata['connections']['aditional_phone']['corporate_fax']['connection_value'] = trim($row[$header[$corporate_fax_pos]]);
                                                    }

                                                    if( trim($row[$header[$corporate_cell_pos]]) != "" )
                                                    {
                                                        $sdata['connections']['aditional_phone']['corporate_cell']['connection_name'] = 'Celular Corporativo';
                                                        $sdata['connections']['aditional_phone']['corporate_cell']['connection_value'] = trim($row[$header[$corporate_cell_pos]]);
                                                    }

                                                    if( trim($row[$header[$corporate_pager_pos]]) != "" )
                                                    {
                                                        $sdata['connections']['aditional_phone']['corporate_pager']['connection_name'] = 'Pager Corporativo';
                                                        $sdata['connections']['aditional_phone']['corporate_pager']['connection_value'] = trim($row[$header[$corporate_pager_pos]]);
                                                    }
                                                }

						$sdata['addresses']['address_corporative']['address1'] = trim($row[$header[$corporate_street_pos]]);
						$sdata['addresses']['address_corporative']['address2'] = trim($row[$header[$corporate_street_2_pos]]);
						$sdata['addresses']['address_corporative']['complement'] = trim($row[$header[$corporate_comp_pos]]);
						$sdata['addresses']['address_corporative']['postal_code'] = trim($row[$header[$corporate_cep_pos]]);
						$sdata['addresses']['address_corporative']['id_country'] = "BR";
						$sdata['addresses']['address_corporative']['id_state'] = trim($row[$header[$corporate_state_pos]]);
						$sdata['addresses']['address_corporative']['id_city'] = trim($row[$header[$corporate_city_pos]]);


						$sdata['addresses']['address_personal']['address1'] = trim($row[$header[$street_pos]]);
						$sdata['addresses']['address_personal']['address2'] = trim($row[$header[$street_2_pos]]);
						$sdata['addresses']['address_personal']['complement'] = trim($row[$header[$comp_pos]]);
						$sdata['addresses']['address_personal']['postal_code'] = trim($row[$header[$cep_pos]]);
						$sdata['addresses']['address_personal']['id_country'] = "BR";
						$sdata['addresses']['address_personal']['id_state'] = trim($row[$header[$state_pos]]);
						$sdata['addresses']['address_personal']['id_city'] =  trim($row[$header[$city_pos]]);

					if(trim($birth)) 
					{
						$array_birth = explode("/",trim($birth));
						$sdata['birthdate'] = date('Y-m-d', mktime(0,0,0,$array_birth[1],$array_birth[0],$array_birth[2]));
					}

					$sdata['notes'] = addslashes($notes);
					//$sdata['is_quick_add'] = true;
					$sdata['connections']['default_phone']['connection_value'] = $phone;
					if(!$phone){

					}
					// 	verifica se email já existe!
					$email = addslashes($email);
					$contact = false;
					$contact = $boGroup->verify_contact($email, $full_name, $phone);
					if(!$sdata['given_names'] && $email)
					{
							$a_email = explode("@",$email);
							$sdata['given_names'] = addslashes($a_email[0]);
					}

					$line_iteration = $return['_failure'] + $return['_existing'] + $return['_new'];

					if($contact[0] != null)
					{
						$return['_existing']++;
					}
					else if((!preg_match('/^[a-zA-Z0-9][_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*$/i', $email)) && $email) {
						$return['_failure']++;
						$return['_failure_status'] .= "Line: " . ($line_iteration + 2) . ", Invalid E-Mail address: " . $email ."<br>";
					}
					else if (!$sdata['given_names'] || !$boPeople ->quick_add($sdata)){
						$return['_failure']++;
						$return['_failure_status'] .= "Line: " . ($line_iteration + 2) . ", Invalid Name: " . $sdata['given_names'] ."<br>";
					}
					else{
							if($id_group != 0){
								$this->so_group->add_user_by_name($id_group,$full_name);
							}
							$return['_new']++;
		   				}
       			}
   				fclose($handle);
				unlink($file);
			}
			else
				$return['error'] = true;

			echo serialize($return);
		}
		
		function get_contact_details($id)
		{
			$data = array();
			
			if(!$this->bo->catalog->src_info) {
				$ldap = CreateObject('contactcenter.bo_ldap_manager');
				$this->bo->catalog->src_info = $ldap->srcs[1];
			}
			//$id = urlencode($id);
			$ds = $GLOBALS['phpgw']->common->ldapConnect($this->bo->catalog->src_info['host'], $this->bo->catalog->src_info['acc'], $this->bo->catalog->src_info['pw'], true);				
			$dn=$this->bo->catalog->src_info['dn'];

			//buscar os atributos do ldap
			$configobj = CreateObject('phpgwapi.config', 'contactcenter');
			$prefs = $configobj->read_repository();
			$attr_names = array();
			$attr_types = array();
			$justThese = array();
			foreach ($prefs as $pref_key => $pref_value)
			{
				if (stripos($pref_key, "cc_attribute_name_") !== false)
				{
					$num = substr($pref_key, strlen("cc_attribute_name_"));
					$attr_names[] = $prefs["cc_attribute_name_$num"];
					$attr_types[] = $prefs["cc_attribute_type_$num"];
					$justThese[] = strtolower($prefs["cc_attribute_ldapname_$num"]);
				}
			}
			


			$sr = ldap_read($ds,utf8_encode($id), "objectClass=*",$justThese);							
			$info = ldap_get_entries($ds, $sr);
			if ($info)
			{
                $justThese_count = count($justThese);
				for ($i = 0; $i < $justThese_count; ++$i)
				{
					$attr = array();
					$attr['name'] = $attr_names[$i];
					if ($attr_types[$i] == 'multivalues')
					{
						$attr['type'] = 'multivalues';
						$attr['value'] = array();
						for ($j = 0; $j < $info[0][$justThese[$i]]['count']; ++$j)
							$attr['value'][] = utf8_decode($info[0][$justThese[$i]][$j]);
					}
					else
					{
						$attr['type'] = 'text';
						$attr['value'] = utf8_decode($info[0][$justThese[$i]][0]);
					}
					$data[] = $attr;
				}
			}
			else
				$data = 'error';
			ldap_close($ds);
			
			echo serialize($data);
		}
		
	}

?>
