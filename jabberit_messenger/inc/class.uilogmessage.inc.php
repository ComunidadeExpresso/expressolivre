<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

require_once "class.bologmessage.inc.php";

class uilogmessage
{
	private $bo;
	
	public $public_functions = array(
		'getLog'  => True,
		'getMessageUser' => True,
		'getMessageUserComplete' => True,		
		'getMessageUserDate' => True
	);
	
	function __construct()
	{
		$this->bo = new bologmessage(); 
	}
	
	private function formatDate($pDate)
	{
		$newDt = date_parse($pDate);
		
		// Hour
		$newDt['hour'] = ( strlen($newDt['hour']) == 1 ) ? "0".$newDt['hour'] : $newDt['hour'];
		$newDt['minute'] = ( strlen($newDt['minute']) == 1 ) ? "0".$newDt['minute'] : $newDt['minute'];
		$newDt['second'] = ( strlen($newDt['second']) == 1 ) ? "0".$newDt['second'] : $newDt['second']; 
		
		// Date
		$newDt['day'] = ( strlen($newDt['day']) == 1 ) ? "0".$newDt['day'] : $newDt['day'];
		$newDt['month'] = ( strlen($newDt['month']) == 1 ) ? "0".$newDt['month'] : $newDt['month'];
		
		return $newDt['hour'].":".$newDt['minute'].":".$newDt['second'] . " - " . $newDt['day']."/".$newDt['month']."/".$newDt['year'];
	}
	
	public function getLog()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Log de Mensagens Instântaneas - BanderSnatch';	


		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger' => 'logMessagesJabber.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','log_message');	
		$GLOBALS['phpgw']->template->set_var(array(
													'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uilogmessage.getMessageUser'),
													'action_url_back' => './admin',
													'bt_previous' => '<input type="submit" name="bt_previous" value="Anterior" />',
													'bt_next' => '<input type="submit" name="bt_next" value="Proximo" />',													
													'label_back' => lang("Back"),
													'label_first_message' => lang("First Message"),
													'label_last_message' => lang("Last Message"),
													'label_page' => "",
													'label_total' => lang("Total"),
													'label_user' => lang("User"),
													'label_view' => lang("View"),
													'value_messages' => "",
													'value_next' => 30,
													'value_page' => "",
													'value_previous' => 0,
													'value_txtUser' => "" 													
												));
	
		$GLOBALS['phpgw']->template->pparse('out','log_message');
	}
	
	public function getMessageUser()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Log de Mensagens Instântaneas - BanderSnatch';	

		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
		
		$value_messages = "";

		if(trim($_REQUEST['txtUser']))
		{
			$limitPrevious 	= 0;
			$limitNext 		= 30;

			if( $_REQUEST['bt_next'] )
			{
				$limitPrevious	= $_REQUEST['button_previous'] + 30;
				$limitNext		= 30;		
			}
			else if ( $_REQUEST['bt_previous'] )
			{
				if( $_REQUEST['button_previous'] != 0 )
				{
					$limitPrevious	= $_REQUEST['button_previous'] - 30;
					$limitNext	 	= 30;
				}
				else
				{
					$limitPrevious	= 0;
					$limitNext	 	= 30;
				}
			}
			
			if($_REQUEST['pg1_next'] || $_REQUEST['pg1_previous'])
			{
 				$limitPrevious	= $_REQUEST['pg1_previous'];
				$limitNext 		= $_REQUEST['pg1_next'];
			}
			
			$data = $this->bo->getMessageUser($_REQUEST['txtUser'], $limitPrevious, $limitNext );
			
			foreach($data as $key=>$value)
			{
				$className = (($key % 2 ) == 0 ) ? "row_off" : "row_on";
				
				$value_messages .= "<tr>";
				$value_messages .= "<td align='left' style='width:40%;' class='".$className."'>". $value['message_from']."</td>";
				$value_messages .= "<td align='center' style='width:10%;' class='".$className."'>".$value['total_messages']."</td>";
				$value_messages .= "<td align='center' style='width:20%;' class='".$className."'>".$this->formatDate($value['first_message'])."</td>";
				$value_messages .= "<td align='center' style='width:20%;' class='".$className."'>".$this->formatDate($value['last_message'])."</td>";
				$value_messages .= "<td align='center' style='width:10%;' class='".$className."'><a href='index.php?menuaction=jabberit_messenger.uilogmessage.getMessageUserDate&txtUser=".$_REQUEST['txtUser']."&user=".$value['message_from']."&first_message=".$value['first_message']."&last_message=".$value['last_message']."&pg1_previous=".$limitPrevious."&pg1_next=".$limitNext."'>".lang("View")."</a></td>";
				$value_messages .= "</tr>";
			}
		}
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger' => 'logMessagesJabber.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','log_message');	
		$GLOBALS['phpgw']->template->set_var(array(
													'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uilogmessage.getMessageUser'),
													'action_url_back' => './admin',
													'bt_previous' => ( $limitPrevious == 0 ) ? '<input type="submit" name="bt_previous" value="Anterior" disabled="disabled" style="color:#cecece !important"/>' : '<input type="submit" name="bt_previous" value="Anterior"/>',
													'bt_next' => ( count($data) < 30 ) ? '<input type="submit" name="bt_next" value="Proximo" disabled="disabled" style="color:#cecece !important"/>': '<input type="submit" name="bt_next" value="Proximo"/>',													
													'label_back' => lang("Back"),
													'label_first_message' => lang("First Message"),
													'label_last_message' => lang("Last Message"),
													'label_page' => ( count($data) > 0 ) ? lang("Page") . " : " : "",
													'label_total' => lang("Total"),													
													'label_user' => lang("User"),
													'label_view' => lang("View"),													 
													'value_messages' => $value_messages,
													'value_next' => $limitNext,
													'value_page' => ( count($data) > 0 ) ? (( $limitPrevious / 30 ) + 1) : "",													
													'value_previous' => $limitPrevious,
													'value_txtUser' => $_REQUEST['txtUser']
												));
	
		$GLOBALS['phpgw']->template->pparse('out','log_message');
	}
	
	public function getMessageUserDate()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Log de Mensagens Instântaneas - BanderSnatch';	


		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
		
		$user 			= $_REQUEST['user'];
		$firstDate		= $_REQUEST['first_message'];
		$lastDate		= $_REQUEST['last_message'];
		$value_messages = "";		
		
		$limitPrevious 	= 0;
		$limitNext 		= 30;
		
		if( $_REQUEST['bt_next'] )
		{
			$limitPrevious	= $_REQUEST['button_previous'] + 30;
			$limitNext		= 30;		
		}
		else if ( $_REQUEST['bt_previous'] )
		{
			if( $_REQUEST['button_previous'] != 0 )
			{
				$limitPrevious	= $_REQUEST['button_previous'] - 30;
				$limitNext	 	= 30;
			}
			else
			{
				$limitPrevious	= 0;
				$limitNext	 	= 30;
			}
		}
		
		if($_REQUEST['pg2_next'] || $_REQUEST['pg2_previous'])
		{
 			$limitPrevious	= $_REQUEST['pg2_previous'];
			$limitNext 		= $_REQUEST['pg2_next'];
		}

		$data = $this->bo->getMessageUserDate($user, $firstDate, $lastDate, $limitPrevious, $limitNext );
		
		if(count($data) > 0 )
		{
			foreach($data as $key=>$value)
			{
				$className = (($key % 2 ) == 0 ) ? "row_off" : "row_on";
				
				$value_messages .= "<tr>";
				$value_messages .= "<td align='left' style='width:30%;' class='".$className."'> ".$value['message_from']." </td>";
				$value_messages .= "<td align='left' style='width:30%;' class='".$className."'> ".$value['message_to']." </td>";
				$value_messages .= "<td align='center' style='width:10%;' class='".$className."'> ".$value['total_messages']." </td>";
				$value_messages .= "<td align='center' style='width:10%;' class='".$className."'> ".$this->formatDate($value['first_message'])." </td>";
				$value_messages .= "<td align='center' style='width:10%;' class='".$className."'> ".$this->formatDate($value['last_message'])." </td>";
				$value_messages .= "<td align='center' style='width:10%;' class='".$className."'><a href='index.php?menuaction=jabberit_messenger.uilogmessage.getMessageUserComplete&txtUser=".$_REQUEST['txtUser']."&user=".$user."&user1=".$value['message_from']."&user2=".$value['message_to']."&dtfirst=".$value['first_message']."&dtlast=".$value['last_message']."&first_message=".$_REQUEST['first_message']."&last_message=".$_REQUEST['last_message']."&pg1_previous=".$_REQUEST['pg1_previous']."&pg1_next=".$_REQUEST['pg1_next']."&pg2_previous=".$limitPrevious."&pg2_next=".$limitNext."'>".lang("View")."</a></td>";
				$value_messages .= "</tr>";
			}
		}
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger' => 'logMessagesJabber.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','log_message_date');	
		$GLOBALS['phpgw']->template->set_var(array(
													'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uilogmessage.getMessageUserDate'),
													'action_url_back' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uilogmessage.getMessageUser'),
													'bt_previous' => ( $limitPrevious == 0 ) ? '<input type="submit" value="Anterior" name="bt_previous" disabled="disabled" style="color:#cecece !important"/>' : '<input type="submit" value="Anterior" name="bt_previous" />',
													'bt_next' => ( count($data) < 30 ) ? '<input type="submit" value="Próxima" name="bt_next" disabled="disabled" style="color:#cecece !important"/>' : '<input type="submit" value="Próxima" name="bt_next" />',
													'label_back' => lang("Back"),
													'label_first_message' => lang("First Message"),
													'label_last_message' => lang("Last Message"),
													'label_next' => lang("Next"),
													'label_page' => ( count($data) > 0 ) ? lang("Page") . " : " : "",													
													'label_previous' => lang("Previous"),
													'label_total' => lang("Total"),	
													'label_user_1' => lang("From"),
													'label_user_2' => lang("To"),
													'label_view' => lang("View"),
													'value_first_message' => $_REQUEST['first_message'],
													'value_last_message' => $_REQUEST['last_message'],												
													'value_messages' => $value_messages,
													'value_next' => $limitNext,
													'value_page' => ( count($data) > 0 ) ? (( $limitPrevious / 30 ) + 1) : "",
													'value_pg1_next' => $_REQUEST['pg1_next'],
													'value_pg1_previous' => $_REQUEST['pg1_previous'],
													'value_previous' => $limitPrevious,
													'value_txtUser' => $_REQUEST['txtUser'],
													'value_user' => $_REQUEST['user'] 
												));
	
		$GLOBALS['phpgw']->template->pparse('out','log_message_date');
	}
	
	public function getMessageUserComplete()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Log de Mensagens Instântaneas - BanderSnatch';	


		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$user1	= $_REQUEST['user1'];
		$user2	= $_REQUEST['user2'];
		$firstDate	= $_REQUEST['dtfirst'];
		$lastDate	= $_REQUEST['dtlast'];
		$value_messages	= "";
		
		$limitPrevious 	= 0;
		$limitNext 		= 30;
		
		if( $_REQUEST['bt_next'] )
		{
			$limitPrevious	= $_REQUEST['button_previous'] + 30;
			$limitNext		= 30;		
		}
		else if ( $_REQUEST['bt_previous'] )
		{
			if( $_REQUEST['button_previous'] != 0 )
			{
				$limitPrevious	= $_REQUEST['button_previous'] - 30;
				$limitNext	 	= 30;
			}
			else
			{
				$limitPrevious	= 0;
				$limitNext	 	= 30;
			}
		}
		
		$data = $this->bo->getMessageUserComplete( $user1, $user2, $firstDate, $lastDate, $limitPrevious, $limitNext );
		
		if( count($data) > 0 )
		{
			foreach($data as $key=>$value)
			{
				$className = (($key % 2 ) == 0 ) ? "row_off" : "row_on";	
				
				$value_messages .= "<tr>";
				$value_messages .= "<td align='left' style='width:25%;' class='".$className."'> ".$value['message_from']." </td>";
				$value_messages .= "<td align='left' style='width:25%;' class='".$className."'> ".$value['message_to']." </td>";
				$value_messages .= "<td align='left' style='width:40%;' class='".$className."'> ".utf8_decode($value['message_body'])." </td>";
				$value_messages .= "<td align='center' style='width:10%' class='".$className."'> ".$this->formatDate($value['message_timestamp'])." </td>";
				$value_messages .= "</tr>";
			}
		}
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger' => 'logMessagesJabber.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','log_message_complete');	
		$GLOBALS['phpgw']->template->set_var(array(
													'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uilogmessage.getMessageUserComplete'),
													'action_url_back' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uilogmessage.getMessageUserDate'),
													'bt_previous' => ( $limitPrevious == 0 ) ? '<input type="submit" value="Anterior" name="bt_previous" disabled="disabled" style="color:#cecece !important"/>' : '<input type="submit" value="Anterior" name="bt_previous"/>',
													'bt_next' => ( count($data) < 30 ) ? '<input type="submit" value="Próximo" name="bt_next" disabled="disabled" style="color:#cecece !important"/>' : '<input type="submit" value="Próximo" name="bt_next" />',
													'label_back' => lang("Back"),													
													'label_body' => lang("Content"),
													'label_date' =>	lang("Date and Hour"),
													'label_next' => lang("Next"),
													'label_page' => (count($data) > 0 ) ? lang("Page") . " : " : "",													
													'label_previous' => lang("Previous"),
													'label_user_1' => lang("From"),
													'label_user_2' => lang("To"),												
													'value_first_message' => $_REQUEST['first_message'],
													'value_last_message' => $_REQUEST['last_message'],
													'value_messages' => $value_messages,
													'value_next' => $limitNext,
													'value_page' => (count($data) > 0 ) ? (( $limitPrevious / 30 ) + 1) : "",													
													'value_previous' => $limitPrevious,
													'value_pg1_next' => $_REQUEST['pg1_next'],
													'value_pg1_previous' => $_REQUEST['pg1_previous'],
													'value_pg2_next' => $_REQUEST['pg2_next'],
													'value_pg2_previous' => $_REQUEST['pg2_previous'],
													'value_txtUser' => $_REQUEST['txtUser'],
													'value_user' => $_REQUEST['user'],
													'value_user1' => $_REQUEST['user1'],
													'value_user2' => $_REQUEST['user2'],
													'value_dtfirst' => $_REQUEST['dtfirst'],
													'value_dtlast' => $_REQUEST['dtlast']
												));
	
		$GLOBALS['phpgw']->template->pparse('out','log_message_complete');
	}		
}

?>
