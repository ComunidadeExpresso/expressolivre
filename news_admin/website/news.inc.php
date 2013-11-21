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

	include('setup.inc.php');

	$tpl->set_file(array('news' => 'news.tpl',
			'row' => 'news_row.tpl')
	);

	$db->query("SELECT COUNT(*) FROM phpgw_news WHERE news_status='Active'");
	$db->next_record();
	$total = $db->f(0);

	if (! $oldnews)
	{
		$db->query("SELECT *,account_lid AS submittedby FROM phpgw_news,phpgw_accounts WHERE news_status='Active' "
					. "AND news_submittedby=phpgw_accounts.account_id ORDER BY news_date DESC LIMIT 5");
	}
	else
	{
		$db->query("SELECT *,account_lid AS submittedby FROM phpgw_news,phpgw_accounts WHERE news_status='Active' AND "
					. "news_submittedby=phpgw_accounts.account_id ORDER BY news_date DESC LIMIT 5,$total");
	}

	while ($db->next_record())
	{
		$tpl->set_var('subject',$db->f('news_subject'));
		$tpl->set_var('submitedby','Submitted by ' . $db->f('submittedby') . ' on ' . date("m/d/Y - h:m:s a",$db->f('news_date')));
		$tpl->set_var('content',nl2br($db->f('news_content')));

		$tpl->parse('rows','row',True);
	}

	$tpl->pparse('out','news');

	if ($total > 5 && ! $oldnews)
	{
		echo '<center><a href="index.php?oldnews=True">View news archives</a></center>';
	}
?>
	<p>&nbsp;</p>

	<div align="center"><font size="-1">This page uses the news admin from <a href="http://www.egroupware.org">eGroupWare</a></font></div>
