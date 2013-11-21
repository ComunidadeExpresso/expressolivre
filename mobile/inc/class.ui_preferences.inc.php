<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	class ui_preferences
	{
		var $public_functions = array(
			'index'           => true,
			'set_preferences' => true,
		);
		
		function index()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Preferences').' - '.lang('Mobile');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$GLOBALS['phpgw']->template->set_file(array('pref' => 'preferences.tpl'));

			/* Get Saved Preferences */
			$prefs = $GLOBALS['phpgw']->preferences->read();
			
			if($prefs['mobile']['download_attach']==1)
				$GLOBALS['phpgw']->template->set_var('download_attach_option_Yes_selected', 'selected');
			else
				$GLOBALS['phpgw']->template->set_var('download_attach_option_No_selected', 'selected');
			
			$max_page = isset($prefs['mobile']['max_message_per_page'])?
					$prefs['mobile']['max_message_per_page']:10;
			
			$max_page = "max_message_per_page_".$max_page."_selected";

			
			$GLOBALS['phpgw']->template->set_var($max_page,"selected");
			
			/* Translate the fields */
			$this->translate('pref');

			$GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/index.php', 'menuaction=mobile.ui_preferences.set_preferences'));

			$GLOBALS['phpgw']->template->pparse('out', 'pref');
		}
			
		function translate($handle)
		{
			$vars = $GLOBALS['phpgw']->template->get_undefined($handle);
			foreach($vars as $name => $value)
			{
				if (preg_match('/^lang_/', $name) !== false)
				{
					$GLOBALS['phpgw']->template->set_var($name, lang(str_replace('_',' ',substr($name, 5))));
				}
				else {
					$GLOBALS['phpgw']->template->set_var($name, " ");
				}
			}
		}
		
		function set_preferences()
		{
			if ($_POST['save'])
			{
				$GLOBALS['phpgw']->preferences->read();
				
				if($_POST['download_attach']==0)
					$GLOBALS['phpgw']->preferences->delete('mobile', 'download_attach');
				else
					$GLOBALS['phpgw']->preferences->add('mobile', 'download_attach', '1');
				
				$GLOBALS['phpgw']->preferences->add('mobile', 'max_message_per_page', $_POST['max_message_per_page']);
				
				$GLOBALS['phpgw']->preferences->save_repository();
			}

			header('Location: '.$GLOBALS['phpgw']->link('/preferences/index.php'));
		}

	}
?>
