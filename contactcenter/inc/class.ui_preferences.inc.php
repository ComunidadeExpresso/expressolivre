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

		function translate($handle)
		{
			$vars = $GLOBALS['phpgw']->template->get_undefined($handle);
			foreach($vars as $name => $value)
			{
				if (preg_match('/^lang_/', $name) !== false)
				{
					$GLOBALS['phpgw']->template->set_var($name, lang(str_replace('_',' ',substr($name, 5))));
				}
			}
		}
		
		function set_preferences()
		{
			if ($_POST['save'])
			{
				$GLOBALS['phpgw']->preferences->read();
				
				$GLOBALS['phpgw']->preferences->delete('contactcenter', 'personCardEmail');
				$GLOBALS['phpgw']->preferences->delete('contactcenter', 'personCardPhone');

				$GLOBALS['phpgw']->preferences->delete('contactcenter', 'displayConnector');
				$GLOBALS['phpgw']->preferences->delete('contactcenter', 'displayConnectorDefault');
				
				$GLOBALS['phpgw']->preferences->add('contactcenter', 'personCardEmail', $_POST['personCardEmail']);
				$GLOBALS['phpgw']->preferences->add('contactcenter', 'personCardPhone', $_POST['personCardPhone']);
				
				$GLOBALS['phpgw']->preferences->add('contactcenter', 'displayConnectorDefault', '1');

				if($_POST['displayConnector'])
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'displayConnector', '1');
				}
				else
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'displayConnector', '0');
				}
				
				if($_POST['empNum'])
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'empNum', '1');
				}
				else
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'empNum', '0');
				}
				
				if($_POST['cell'])
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'cell', '1');
				}
				else
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'cell', '0');
				}
				
				if($_POST['department'])
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'department', '1');
				}
				else
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'department', '0');
				}

				$GLOBALS['phpgw']->preferences->save_repository();
			}

			header('Location: '.$GLOBALS['phpgw']->link('/preferences/index.php'));
		}

		function get_preferences()
		{
			$prefs = $GLOBALS['phpgw']->preferences->read();

			if (!$prefs['contactcenter']['displayConnectorDefault'] and !$prefs['contactcenter']['displayConnector'])
			{
				$prefs['contactcenter']['displayConnector'] = true;
			}
			
			return $prefs['contactcenter'];
		}
	}
?>
