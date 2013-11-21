<?php
	/**************************************************************************\
	* eGroupWare - Webpage news admin                                          *
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


	$showevents = (int)$GLOBALS['phpgw_info']['user']['preferences']['news_admin']['homeShowLatest'];
	if($showevents > 0)
	{
		$GLOBALS['phpgw']->translation->add_app('news_admin');
		$title = lang('News Admin');
		$portalbox = CreateObject('phpgwapi.listbox',array(
			'title'     => $title,
			'primary'   => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
			'secondary' => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
			'tertiary'  => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
			'width'     => '100%',
			'outerborderwidth' => '0',
			'header_background_image' => $GLOBALS['phpgw']->common->image('phpgwapi/templates/default','bg_filler')
		));

		$latestcount = (int)$GLOBALS['phpgw_info']['user']['preferences']['news_admin']['homeShowLatestCount'];
		if($latestcount<=0) 
		{
			$latestcount = 10;
		}
		print_debug("showing $latestcount news items");
		$app_id = $GLOBALS['phpgw']->applications->name2id('news_admin');
		$GLOBALS['portal_order'][] = $app_id;

		$news = CreateObject('news_admin.uinews');

		$newslist = $news->bo->get_newslist('all',0,'','',$latestcount,True);

		$image_path = $GLOBALS['phpgw']->common->get_image_path('news_admin');

		if(is_array($newslist))
		{
			foreach($newslist as $newsitem)
			{
				$text = $newsitem['subject'];
				if($showevents == 1)
				{
					$text .= ' - ' . lang('Submitted by') . ' ' . $GLOBALS['phpgw']->common->grab_owner_name($newsitem['submittedby']) . ' ' . lang('on') . ' ' . $GLOBALS['phpgw']->common->show_date($newsitem['date']);
				}
				$portalbox->data[] = array(
					'text' => $text,
					'link' => $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.uinews.read_news&news_id=' . $newsitem['id'])
				);
			}
			unset($text);
		}
		else
		{
			$portalbox->data[] = array('text' => lang('no news'));
		}

		$GLOBALS['portal_order'][] = $app_id;
		$var = Array(
				'up'    => Array('url'  => '/set_box.php', 'app'        => $app_id),
				'down'  => Array('url'  => '/set_box.php', 'app'        => $app_id),
				'close' => Array('url'  => '/set_box.php', 'app'        => $app_id),
				'question'      => Array('url'  => '/set_box.php', 'app'        => $app_id),
				'edit'  => Array('url'  => '/set_box.php', 'app'        => $app_id)
		);

		while(list($key,$value) = each($var))
		{
			$portalbox->set_controls($key,$value);
		}

		$tmp = "\r\n"
			. '<!-- start News Admin -->' . "\r\n"
			. $portalbox->draw()
			. '<!-- end News Admin -->'. "\r\n";
		print $tmp;
	}
?>
