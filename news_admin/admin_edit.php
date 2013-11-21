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


	$phpgw_info = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'              => 'news_admin',
		'enable_nextmatchs_class' => True
	);
	if($submit)
	{
		$GLOBALS['phpgw_info']['flags']['noheader'] = True;
		$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
	}
	include('../header.inc.php');

	$GLOBALS['phpgw']->sbox = CreateObject('phpgwapi.sbox');

	if($submit)
	{
		// Its possiable that this could get messed up becuase of there timezone offset
		if($date_ap == 'pm')
		{
			$date_hour = $date_hour + 12;
		}
		$date = mktime($date_hour,$date_min,$date_sec,$date_month,$date_day,$date_year);
		$GLOBALS['phpgw']->db->query("UPDATE phpgw_news SET news_subject='" . addslashes($subject) . "',"
			. "news_content='" . addslashes($content) . "',news_status='$status',news_date='$date' "
			. "WHERE news_id='$news_id'",__LINE__,__FILE__);
		Header('Location: ' . $GLOBALS['phpgw']->link('/news_admin/index.php'));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'form' => 'form.tpl',
		'row'  => 'form_row.tpl'
	));

	$GLOBALS['phpgw']->db->query("select * from phpgw_news where news_id='$news_id'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();

	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$GLOBALS['phpgw']->template->set_var('bgcolor',$GLOBALS['phpgw_info']['theme']['bgcolor']);

	$GLOBALS['phpgw']->template->set_var('lang_header',lang('Edit news item'));
	$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/news_admin/edit.php','news_id=' . $GLOBALS['phpgw']->db->f('news_id')));
	$GLOBALS['phpgw']->template->set_var('form_button','<input type="submit" name="submit" value="' . lang("Edit") . '">');

	$GLOBALS['phpgw']->template->set_var('tr_color',$GLOBALS['phpgw']->nextmatchs->alternate_row_color());
	$GLOBALS['phpgw']->template->set_var('label',lang('subject') . ':');
	$GLOBALS['phpgw']->template->set_var('value','<input name="subject" size="60" value="' . $GLOBALS['phpgw']->db->f('news_subject') . '">');
	$GLOBALS['phpgw']->template->parse('rows','row',True);

	$GLOBALS['phpgw']->template->set_var('tr_color',$GLOBALS['phpgw']->nextmatchs->alternate_row_color());
	$GLOBALS['phpgw']->template->set_var('label',lang('Content') . ':');
	$GLOBALS['phpgw']->template->set_var('value','<textarea cols="60" rows="6" name="content" wrap="virtual">' . stripslashes($GLOBALS['phpgw']->db->f('news_content')) . '</textarea>');
	$GLOBALS['phpgw']->template->parse('rows','row',True);

	$GLOBALS['phpgw']->template->set_var('tr_color',$GLOBALS['phpgw']->nextmatchs->alternate_row_color());
	$GLOBALS['phpgw']->template->set_var('label',lang('Status') . ':');
	$s[$GLOBALS['phpgw']->db->f('news_status')] = ' selected';
	$GLOBALS['phpgw']->template->set_var("value",'<select name="status"><option value="Active"' . $s['Active'] . '>'
		. lang('active') . '</option><option value="Disabled"' . $s['Disabled'] . '>'
		. lang('Disabled') . '</option></select>');
	$GLOBALS['phpgw']->template->parse('rows','row',True);

	$GLOBALS['phpgw']->template->set_var('tr_color',$GLOBALS['phpgw']->nextmatchs->alternate_row_color());
	$GLOBALS['phpgw']->template->set_var('label',lang('Date') . ':');

	$d_html = $GLOBALS['phpgw']->common->dateformatorder($GLOBALS['phpgw']->sbox->getYears('date_year', date('Y',$GLOBALS['phpgw']->db->f('news_date'))),
		$GLOBALS['phpgw']->sbox->getMonthText('date_month', date('m',$GLOBALS['phpgw']->db->f('news_date'))),
		$GLOBALS['phpgw']->sbox->getDays('date_day', date('d',$GLOBALS['phpgw']->db->f('news_date')))
	);
	$d_html .= " - ";
	$d_html .= $GLOBALS['phpgw']->sbox->full_time('date_hour',$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('news_date'),'h'),
		'date_min',$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('news_date'),'i'),
		'date_sec',$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('news_date'),'s'),
		'date_ap',$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('news_date'),'a')
	);
	$GLOBALS['phpgw']->template->set_var('value',$d_html);

	$h = '<select name="status"><option value="Active"' . $s['Active'] . '>'
		. lang('Active') . '</option><option value="Disabled"' . $s['Disabled'] . '>'
		. lang('Disabled') . '</option></select>';
	$GLOBALS['phpgw']->template->parse('rows','row',True);

	$GLOBALS['phpgw']->template->pparse('out','form');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
