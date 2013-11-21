<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


	if ($GLOBALS['phpgw']->acl->check('changepassword',1))
	{
		$file['Change your Password'] = $GLOBALS['phpgw']->link('/preferences/changepassword.php');
	}
        if (isset($GLOBALS['phpgw_info']['server']['certificado']))
            {
                if ($GLOBALS['phpgw_info']['server']['certificado'])
                {
                        $file['Register Digital Certificate'] = $GLOBALS['phpgw']->link('/preferences/handlecertificate.php');
                }
            }
	$file['change your settings'] = $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=preferences');
	
	$file['Change your Personal Data'] = $GLOBALS['phpgw']->link('/preferences/changepersonaldata.php');

	display_section('preferences',$file);

?>
