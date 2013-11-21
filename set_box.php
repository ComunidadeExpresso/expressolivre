<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * The file written by Joseph Engo <jengo@phpgroupware.org>                 *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


	$GLOBALS['phpgw_info']['flags'] = Array(
		'noheader'	=> True,
		'nofooter'	=> True,
		'currentapp'	=> 'home'
	);
	include('header.inc.php');

//	if(basename($HTTP_REFERER) != 'home.php')
//	{
//		Header('Location: '.$GLOBALS['phpgw']->link('/home.php'));
//		$GLOBALS['phpgw']->common->phpgw_exit();
//	}

	@reset($GLOBALS['phpgw_info']['user']['preferences']);
	//_debug_array($GLOBALS['phpgw_info']['user']['preferences']['portal_order']);
//	$GLOBALS['phpgw']->preferences->delete('portal_order');
//	unset($GLOBALS['phpgw_info']['user']['preferences']['portal_order']);
//	$GLOBALS['phpgw']->preferences->save_repository();

	function move_boxes($curr_position,$new_order,$offset,$value_to_check,$max_num)
	{
		//echo "MOVE: $curr_position,$new_order,$offset,$value_to_check,$max_num<br>";
		if(isset($GLOBALS['phpgw_info']['user']['preferences']['portal_order'][$new_order]))
		{
			if($new_order == $max_num)
			{
				if($offset < 0)
				{
					@ksort($GLOBALS['phpgw_info']['user']['preferences']['portal_order']);
				}
				else
				{
					@krsort($GLOBALS['phpgw_info']['user']['preferences']['portal_order']);
				}
				while(list($seq_order,$appid) = each($GLOBALS['phpgw_info']['user']['preferences']['portal_order']))
				{
					if($seq_order != $value_to_check)
					{
						$prev_seq = $seq_order + $offset;
						$GLOBALS['phpgw']->preferences->delete('portal_order',$prev_seq);
						$GLOBALS['phpgw']->preferences->add('portal_order',$prev_seq,$appid);
					}
				}
			}
			else
			{
				$GLOBALS['phpgw']->preferences->delete('portal_order',$curr_position);
				$GLOBALS['phpgw']->preferences->add('portal_order',$curr_position,(int)$GLOBALS['phpgw_info']['user']['preferences']['portal_order'][$new_order]);
			}
		}
		$GLOBALS['phpgw']->preferences->delete('portal_order',$new_order);
		$GLOBALS['phpgw']->preferences->add('portal_order',$new_order,(int)$_GET['app']);
			
		$GLOBALS['phpgw']->preferences->save_repository();
	}

	//error_reporting(E_ALL);
	switch($_GET['control'])
	{
		//print_debug('set_box', $GLOBALS['phpgw_info']['user']['preferences']['portal_order']),'app',5);
		case 'up':
			$curr_position = $GLOBALS['phpgw']->common->find_portal_order((int)$_GET['app']);
			$max_count = count($GLOBALS['phpgw_info']['user']['preferences']['portal_order']) - 1;
			$offset = -1;
			if($curr_position <= 0)
			{
				$new_order = $max_count;
			}
			else
			{
				$new_order = $curr_position + $offset;
			}
			move_boxes($curr_position,$new_order,$offset,0,$max_count);
			break;
		case 'down':
			$curr_position = $GLOBALS['phpgw']->common->find_portal_order((int)$_GET['app']);
			$max_count = count($GLOBALS['phpgw_info']['user']['preferences']['portal_order']) - 1;
			$offset = 1;
			if($curr_position >= $max_count)
			{
				$new_order = 0;
			}
			else
			{
				$new_order = $curr_position + $offset;
			}
			move_boxes($curr_position,$new_order,$offset,$max_count,0);
			break;
		case 'edit':
		case 'question':
		case 'close':
		default:
	}

	header('Location: '.$GLOBALS['phpgw']->link('/home.php'));
	$GLOBALS['phpgw']->common->phpgw_exit();
?>

