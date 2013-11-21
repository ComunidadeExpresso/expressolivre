<?php
	/**************************************************************************\
	* eGroupWare - Webpage News Admin                                          *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	* This program was sponsered by Golden Glair productions                   *
	* http://www.goldenglair.com                                               *
	\**************************************************************************/

{

 /*
	This hookfile is for generating an app-specific side menu used in the idots 
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */
	$bo   = CreateObject('news_admin.bonews',True);
	$right = PHPGW_ACL_ADD;
	$permited_add = false;
	
	foreach($bo->cats as $cat)
	{
		if($bo->acl->is_permitted($cat['id'],$right))
		{
			$permited_add = true;
			break;
		}
	}

	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');	
	if( $permited_add ) {
		$file = Array( 'read news' => $GLOBALS['phpgw']->link('/news_admin/index.php'),
						'Add New Article' => $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.uinews.add')
		);
		
	} else {
		$file = Array(
		'read news' => $GLOBALS['phpgw']->link('/news_admin/index.php')		
		);
	}
	display_sidebox($appname,$menu_title,$file);
 
	
 	$title = lang('Preferences');
	$file = array(
		'Preferences'     => $GLOBALS['phpgw']->link('/preferences/preferences.php','appname='.$appname),
	);
	display_sidebox($appname,$title,$file);

	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
        $title = lang('Administration');
        $file = Array(
                'News Administration'  => $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.uinews.write_news'),
                'global categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=' . $appname),
                'configure access permissions' => $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.uiacl.acllist'),
                'configure rss exports' => $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.uiexport.exportlist')
        );

		display_sidebox($appname,$title,$file);
	} else if($permited_add){
		 $title = lang('Administration');
	     $file = Array(
	                'News Administration'  => $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.uinews.write_news')
	     );
	     display_sidebox($appname,$title,$file);
	}
	unset($title);
	unset($file);
	unset($bo);
}
?>
