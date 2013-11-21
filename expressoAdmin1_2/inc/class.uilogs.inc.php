<?php
	/************************************************************************************\
	* Expresso Administração                 										     *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  	 *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		 	 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	\************************************************************************************/

	class uilogs
	{
		var $public_functions = array
		(
			'list_logs'	=> True,
			'view_log'	=> True
		);

		var $functions;
		var $nextmatchs;

		function uilogs()
		{
			$this->functions = createobject('expressoAdmin1_2.functions');
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');
		}

		function list_logs()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$tmp = $this->functions->read_acl($account_lid);
			$manager_context = $tmp[0]['context'];
			
			// Verifica se o administrador tem acesso.
			if (!$this->functions->check_acl($account_lid,'view_logs'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
			}
						
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Logs');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('logs' => 'logs.tpl'));
			$p->set_block('logs','list','list');
			$p->set_block('logs','row','row');
			$p->set_block('logs','row_empty','row_empty');

			//Administrador realizou uma pesquisa no log
			if (($_POST['query_manager_lid'] != '') || ($_POST['query_action'] != '') || ($_POST['query_date'] != '') || ($_POST['query_hour'] != '') || ($_POST['query_other'] != ''))
			{
				$query = "SELECT manager,date,userinfo,action FROM phpgw_expressoadmin_log WHERE"; 
				
				if ($_POST['query_manager_lid'] != '')
				{
					$query .= " manager LIKE '%".$_POST['query_manager_lid']."%'";
				}
				
				if ($_POST['query_date'] != '')
				{
					if ($_POST['query_manager_lid'] != '')
						$query .= " AND";
					
					if ($_POST['query_hour'] != '')
						$query .= " date > TO_TIMESTAMP('".$_POST['query_date'].$_POST['query_hour']."','DD/MM/YYYYHH24:MI') AND date < TO_TIMESTAMP('".$_POST['query_date'].$_POST['query_hour']."','DD/MM/YYYYHH24:MI') + INTERVAL '1 minute'";
					else
						$query .= " date > TO_TIMESTAMP('".$_POST['query_date']."','DD/MM/YYYY') AND date < TO_TIMESTAMP('".$_POST['query_date']."','DD/MM/YYYY') + INTERVAL '1 day'";
				}
				
				if ($_POST['query_action'])
				{
					if (($_POST['query_manager_lid'] != '') || ($_POST['query_date'] != ''))
						$query .= " AND";
					
					$query .= " action LIKE '%".$_POST['query_action']."%'";
				}

				if ($_POST['query_other'])
				{
					if (($_POST['query_manager_lid'] != '') || ($_POST['query_date'] != '') || ($_POST['query_action'] != ''))
						$query .= " AND";
					
					$query .= " userinfo LIKE '%" . $_POST['query_other'] . "%'";
					$query .= "OR action LIKE '%" . $_POST['query_other'] . "%'";
					$query .= "OR manager LIKE '%" . $_POST['query_other'] . "%'";
				}
				
 				$query .= " ORDER by date DESC"; 
 				
				$GLOBALS['phpgw']->db->query($query);
				while($GLOBALS['phpgw']->db->next_record())
				{
					$logs[] = $GLOBALS['phpgw']->db->row();
				}
			}
			
			$var = Array(
				'bg_color'			=> $GLOBALS['phpgw_info']['theme']['bg_color'],
				'th_bg'				=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'			=> $GLOBALS['phpgw']->link('/expressoAdmin1_2/index.php'),
				'search_action'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uilogs.list_logs'),
				
				'query_manager_lid'	=> $_POST['query_manager_lid'],
				'query_action'		=> $_POST['query_action'],
				'query_date'		=> $_POST['query_date'],
				'query_hour'		=> $_POST['query_hour'],
				'query_other'		=> $_POST['query_other'],
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));

			if ((!count($logs)) && (($_POST['query_manager_lid'] != '') || ($_POST['query_date'] != '') || ($_POST['query_hour'] != '')))
			{
				$p->set_var('message',lang('No matches found'));
			}
			else if (count($logs))
			{
				foreach ($logs as $log)
				{
					//_debug_array($log);
					$this->nextmatchs->template_alternate_row_color($p);
					
					//Date treatment
					$a_date = preg_split('/ /', $log['date']);
					$a_day = preg_split('/-/', $a_date[0]);
					$a_day_tmp = array_reverse($a_day);
					$a_day = join($a_day_tmp, "/");
					$a_hour = preg_split('/\./', $a_date[1]);
					
					$var = array(
						'row_date' 			=> $a_day . '  ' . $a_hour[0],						
						'row_manager_lid'	=> $log['manager'],
						'row_action'		=> lang($log['action']),
						'row_about'			=> $log['userinfo']
					);
					$p->set_var($var);
					$p->set_var('row_view',$this->row_action('view','log',$log['date']));
					$p->parse('rows','row',True);
				}
			}
			$p->parse('rows','row_empty',True);
			$p->pfp('out','list');
		}
				
		function row_action($action,$type,$date)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction' => 'expressoAdmin1_2.uilogs.'.$action.'_'.$type,
				'date' => $date
			)).'"> '.lang($action).' </a>';
		}		
	}		
?>
