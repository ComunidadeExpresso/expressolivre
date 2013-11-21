<?php
/**************************************************************************\
* eGroupWare                                                 			   *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once('class.bo_ajaxinterface.inc.php');

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
 * @author Sidnei Augusto Drovetto - drovetto@gmail.com
 */
class bo_userinterface extends bo_ajaxinterface
{
	/**
	 * @var object Acesso à camada Model
	 * @access public
	 */
	var $so;

	/**
	 * @var array Informações sobre a organização do usuário
	 * @access private
	 */
	private $organizationInfo;

	/**
	 * Construtor da classe bo_userinterface
	 * @access public
	 * @return object
	 */
	function bo_userinterface()
	{
		parent::bo_ajaxinterface();
		$this->so = &Factory::getInstance('so_userinterface');
		$GLOBALS['ajax']->gui = &Factory::newInstance('GUI');
	}

	/**
	 * Retorna os processos do usuário
	 * @access public
	 * @return mixed retorna uma string com uma mensagem de erro ou um array com dados dos processos
	 */
	function processes()
	{
		$account_id = $_SESSION['phpgw_info']['workflow']['account_id'];
		$result = $GLOBALS['ajax']->gui->gui_list_user_activities($account_id, '0', '-1', "wf_menu_path__ASC, ga.wf_name__ASC", '', '', '', true, true, true, '');

		$errorMessage = $GLOBALS['ajax']->gui->get_error(false);
		if (!empty($errorMessage))
		{
			$this->disconnect_all();
			return array('error' => $errorMessage);
		}

		$recset = array();
		$webserver_url = $_SESSION['phpgw_info']['workflow']['server']['webserver_url'];

		$templateServer =& Factory::getInstance('TemplateServer');
		foreach ($result['data'] as $line)
		{
			/* don't include activities whose menu_path is equal to ! */
			if ($line['wf_menu_path'] === '!')
				continue;

			if (file_exists(GALAXIA_PROCESSES . '/' . $line['wf_normalized_name'] . '/resources/icon.png'))
				$iconweb = $webserver_url . '/workflow/redirect.php?pid=' . $line['wf_p_id'] . '&file=/icon.png';
			else
				$iconweb = $templateServer->generateImageLink('navbar.png');
			$procname_ver = $line['wf_normalized_name'];
			if (!isset($recset[$procname_ver]))
			{
				$recset[$procname_ver]['wf_p_id'] = $line['wf_p_id'];
				$recset[$procname_ver]['wf_procname'] = $line['wf_procname'];
				$recset[$procname_ver]['wf_version'] = $line['wf_version'];
				$recset[$procname_ver]['wf_is_active'] = $line['wf_is_active'];
				$recset[$procname_ver]['wf_iconfile'] = $iconweb;
				if ($_SESSION['phpgw_info']['workflow']['server']['use_https'] > 0)
				{
					$GLOBALS['ajax']->gui->wf_security->loadConfigValues($line['wf_p_id']);
					$recset[$procname_ver]['useHTTPS'] = $GLOBALS['ajax']->gui->wf_security->processesConfig[$line['wf_p_id']]['execute_activities_using_secure_connection'];
				}
				else
					$recset[$procname_ver]['useHTTPS'] = 0;
			}
			if (!is_dir(GALAXIA_PROCESSES . '/' . $line['wf_normalized_name']))
			{
				$recset[$procname_ver]['wf_iconfile'] = $templateServer->generateImageLink('navbar_nocode.png');
			}
			else
			{
				$recset[$procname_ver][] = array('wf_activity_id' 	=> $line['wf_activity_id'],
												'wf_name' 			=> $line['wf_name'],
												'wf_menu_path' 		=> $line['wf_menu_path'],
												'wf_type' 			=> $line['wf_type'],
												'wf_is_autorouted' 	=> $line['wf_is_autorouted'],
												'wf_is_interactive' => $line['wf_is_interactive']);
			}
		}

		$recset = array_values($recset);
		usort($recset, create_function('$a,$b', 'return strcasecmp($a[\'wf_procname\'] . $a[\'wf_version\'],$b[\'wf_procname\'] . $b[\'wf_version\']);'));

		$this->disconnect_all();
		return $recset;
	}

	/**
	 * Informacoes sobre o processo
	 * @param $params parametros
	 * @return array
	 * @access public
	 */
	function process_about($params)
	{
		$pid = $params['pid'];
		$result = array();

		$process = &Factory::newInstance('Process');
		$process->getProcess($pid);
		$result['wf_procname'] = $process->name;
		$result['wf_version'] = $process->version;
		$result['wf_description'] = $process->description;

		$activ_manager = &Factory::newInstance('ActivityManager');
		$result['wf_activities'] = $activ_manager->get_process_activities($pid);

		$this->disconnect_all();

		return $result;
	}

	/**
	 * Fornece os dados para a contrução da interface de Tarefas Pendentes
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return array Contendo os dados para construção da interface ou uma mensagem de erro
	 * @access public
	 */
	function inbox($params)
	{
		$preferences = $_SESSION['phpgw_info']['workflow']['user']['preferences'];

		/* initialize Paging Class */
		$itemsPerPage = isset($preferences['ui_items_per_page']) ? $preferences['ui_items_per_page'] : 15;
		$lightVersion = ((isset($preferences['use_light_interface']) ? $preferences['use_light_interface'] : 0) == 1);
		$paging = Factory::newInstance('Paging', $itemsPerPage, $_POST);

		/* define the sorting */
		$sort = 'wf_act_started__DESC';
		if ($params['sort'])
			$sort = $params['sort'];
		else
			if (isset($preferences['inbox_sort']))
				$sort = $preferences['inbox_sort'];

		/* make sure that the sorting parameter is one of the expected values */
		$sortFields = explode('__', strtolower($sort));
		if (count($sortFields) != 2)
			$sort = 'wf_act_started__DESC';
		else
			if (!(in_array($sortFields[0], array('wf_act_started', 'wf_procname', 'wf_name', 'insname', 'wf_priority')) && in_array($sortFields[1], array('desc', 'asc'))))
				$sort = 'wf_act_started__DESC';
			elseif(strpos($sort, 'wf_act_started') === false){
				$sort = $sort.', wf_act_started__ASC';
			}

		$params['sort'] = $sort;

		/* get other parameters */
		$pid = (int) $params['pid'];
		$search_term = $params['search_term'];
		$account_id = $_SESSION['phpgw_info']['workflow']['account_id'];
		$result = $GLOBALS['ajax']->gui->gui_list_user_instances($account_id, 0, -1, $sort, $search_term, "ga.wf_is_interactive = 'y'", false, $pid, true, false, true, false, false, false);

		$errorMessage = $GLOBALS['ajax']->gui->get_error(false);
		if (!empty($errorMessage))
		{
			$this->disconnect_all();
			return array('error' => $errorMessage);
		}

		$output = array();
		$output['sort_param'] = str_replace(', wf_act_started__ASC', '', $sort);
		$output['instances'] = array();
		$output['processes'] = array();
		$list_process = array();
		$actionKeys = array(
			'run',
			'viewrun',
			'view',
			'send',
			'release',
			'grab',
			'exception',
			'resume',
			'abort',
			'monitor');

		foreach ($result['data'] as $row)
		{
			/* don't show the instance if the user can't run it */
			if (($row['wf_user'] != $account_id) && ($row['wf_user'] != '*'))
				continue;
			if (($row['wf_status'] == 'active') || ($row['wf_status'] == 'exception'))
			{
				$availableActions = $GLOBALS['ajax']->gui->getUserActions(
							$account_id,
							$row['wf_instance_id'],
							$row['wf_activity_id'],
							$row['wf_readonly'],
							$row['wf_p_id'],
							$row['wf_type'],
							$row['wf_is_interactive'],
							$row['wf_is_autorouted'],
							$row['wf_act_status'],
							$row['wf_owner'],
							$row['wf_status'],
							$row['wf_user']);

				$row['viewRunAction'] = false;
				if (isset($availableActions['viewrun']))
					$row['viewRunAction'] = array('viewActivityID' => $availableActions['viewrun']['link'], 'height' => $availableActions['viewrun']['iframe_height']);

				foreach ($actionKeys as $key)
					$availableActions[$key] = (isset($availableActions[$key]));

				if ($GLOBALS['ajax']->gui->wf_security->processesConfig[$row['wf_p_id']]['disable_advanced_actions'] == 1)
				{
					$availableActions['release'] = false;
					$availableActions['grab'] = false;
					$availableActions['exception'] = false;
					$availableActions['resume'] = false;
					$availableActions['abort'] = false;
					$availableActions['monitor'] = false;
				}

				/* define the advanced actions for javascript usage */
				$actionsArray = array();
				$actionsArray[] = array('name' => 'run', 'value' => $availableActions['run'], 'text' => 'Executar');
				$actionsArray[] = array('name' => 'view', 'value' => $availableActions['view'], 'text' => 'Visualizar');
				$actionsArray[] = array('name' => 'send', 'value' => $availableActions['send'], 'text' => 'Enviar');
				$actionsArray[] = array('name' => 'viewrun', 'value' => $availableActions['viewrun'], 'text' => '');
				$actionsArray[] = ($row['wf_user'] == '*') ?
					array('name' => 'grab', 'value' => $availableActions['grab'], 'text' => 'Capturar') :
					array('name' => 'release', 'value' => $availableActions['release'], 'text' => 'Liberar Acesso');
				$actionsArray[] = ($row['wf_status'] == 'active') ?
					array('name' => 'exception', 'value' => $availableActions['exception'], 'text' => 'Colocar em Exceção') :
					array('name' => 'resume', 'value' => $availableActions['resume'], 'text' => 'Retirar de Exceção');
				$actionsArray[] = array('name' => 'abort', 'value' => $availableActions['abort'], 'text' => 'Abortar');

				$row['wf_actions'] = $actionsArray;

				$row['wf_started'] = date('d/m/Y H:i', $row['wf_started']);
				$row['wf_act_started'] = date('d/m/Y H:i', $row['wf_act_started']);

				$row['wf_user_fullname'] = '';
				if ($row['wf_user'] == '*')
					$row['wf_user_fullname'] = '*';
				else
					if ($row['wf_user'] != '')
						$row['wf_user_fullname'] = Factory::getInstance('WorkflowLDAP')->getName($row['wf_user']);

				/* unset unneeded information */
				unset($row['wf_ended'], $row['wf_owner'], $row['wf_category'], $row['wf_act_status'], $row['wf_started'], $row['wf_type'], $row['wf_is_interactive'], $row['wf_is_autorouted'], $row['wf_normalized_name'], $row['wf_readonly']);
				$output['instances'][] = $row;
			}
		}

		/* paginate the results */
		$output['instances'] = $paging->restrictItems($output['instances']);
		$output['paging_links'] = $paging->commonLinks();

		/* only save different actions set */
		$actions = array_values(array_map(create_function('$a', 'return unserialize($a);'), array_unique(array_map(create_function('$a', 'return serialize($a[\'wf_actions\']);'), $output['instances']))));

		$actionsArray = array();
		$userNames = array();
		$processesInfo = array();
		$activityNames = array();
		foreach ($output['instances'] as $key => $value)
		{
			$userNames[$value['wf_user']] = $value['wf_user_fullname'];
			unset($output['instances'][$key]['wf_user_fullname']);

			$processesInfo[$value['wf_p_id']] = array(
				'name' => $value['wf_procname'] . ' (v' . $value['wf_version'] . ')',
				'useHTTPS' => (($_SESSION['phpgw_info']['workflow']['server']['use_https'] > 0) ? $GLOBALS['ajax']->gui->wf_security->processesConfig[$value['wf_p_id']]['execute_activities_using_secure_connection'] : 0)
			);
			unset($output['instances'][$key]['wf_procname']);
			unset($output['instances'][$key]['wf_version']);

			$activityNames[$value['wf_activity_id']] = $value['wf_name'];
			unset($output['instances'][$key]['wf_name']);

			$output['instances'][$key]['wf_actions'] = array_search($value['wf_actions'], $actions);
			if (is_null($value['insname']))
				$output['instances'][$key]['insname'] = '';
		}

		$output['userNames'] = $userNames;
		$output['processesInfo'] = $processesInfo;
		$output['activityNames'] = $activityNames;
		$output['actions'] = $actions;

		/* load all the activities that have at least one instance */
		$allActivities = $GLOBALS['ajax']->gui->gui_list_user_activities($account_id, 0, -1, "ga.wf_name__ASC", '', "gia.wf_user = '$account_id' OR gia.wf_user = '*'", true);
		foreach ($allActivities['data'] as $activity)
			$list_process[$activity['wf_procname'] . " (v" . $activity['wf_version'] . ")"] = $activity['wf_p_id'];

		$this->disconnect_all();

		foreach ($list_process as $processName => $processId)
			$output['processes'][] = array('name' => $processName, 'pid' => $processId);

		/* some extra params */
		$output['params'] = $params;
		$output['light'] = $lightVersion;
		$output['instancesDigest'] = md5(serialize($output['instances']));

		return $output;
	}

	/**
	 * Fornece os dados para a contrução da interface de Tarefas Pendentes (quando os dados estão agrupados)
	 * @return array Contendo os dados para construção da interface ou uma mensagem de erro
	 * @access public
	 */
	function inbox_group()
	{
		$account_id = $_SESSION['phpgw_info']['workflow']['account_id'];
		$result = $GLOBALS['ajax']->gui->gui_list_user_instances($account_id, 0, -1, 'wf_procname__ASC,wf_name__ASC', '', "ga.wf_is_interactive = 'y'", false, 0, true, false, true, false, false, false);

		$output = array();
		foreach ($result['data'] as $data)
		{
			if (($data['wf_user'] != $account_id) && ($data['wf_user'] != "*"))
				continue;
			if (isset($output[$data['wf_activity_id']]))
				$output[$data['wf_activity_id']]['wf_instances']++;
			else
				$output[$data['wf_activity_id']] = array('wf_p_id' => $data['wf_p_id'], 'wf_procname' => $data['wf_procname'], 'wf_version' => $data['wf_version'], 'wf_name' => $data['wf_name'], 'wf_instances' => 1);
		}

		$errorMessage = $GLOBALS['ajax']->gui->get_error(false);
		if (!empty($errorMessage))
		{
			$this->disconnect_all();
			return array('error' => $errorMessage);
		}

		$this->disconnect_all();
		return array_values($output);
	}

	/**
	 * Envia uma instância para próxima atividade
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return mixed Array contendo uma mensagem de erro ou um booleano (true) informando que a ação foi feita com sucesso
	 * @access public
	 */
	function inboxActionSend($params)
	{
		$instanceID = (int) $params['instanceID'];
		$activityID = (int) $params['activityID'];
		$result = true;

		if (!$GLOBALS['ajax']->gui->gui_send_instance($activityID, $instanceID))
			$result = array('error' => $GLOBALS['ajax']->gui->get_error(false) . "<br />Você não está autorizado a enviar esta instância.");

		$this->disconnect_all();

		return $result;
	}

	/**
	 * Libera uma instância (atribui a instância para *)
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return mixed Array contendo uma mensagem de erro ou um booleano (true) informando que a ação foi feita com sucesso
	 * @access public
	 */
	function inboxActionRelease($params)
	{
		$instanceID = (int) $params['instanceID'];
		$activityID = (int) $params['activityID'];
		$result = true;

		if (!$GLOBALS['ajax']->gui->gui_release_instance($activityID, $instanceID))
			$result = array('error' => $GLOBALS['ajax']->gui->get_error(false) . "<br />Você não está autorizado a liberar esta instância.");

		$this->disconnect_all();

		return $result;
	}

	/**
	 * Captura uma instância (atribui a instância para o usuário atual)
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return mixed Array contendo uma mensagem de erro ou um booleano (true) informando que a ação foi feita com sucesso
	 * @access public
	 */
	function inboxActionGrab($params)
	{
		$instanceID = (int) $params['instanceID'];
		$activityID = (int) $params['activityID'];
		$result = true;

		if (!$GLOBALS['ajax']->gui->gui_grab_instance($activityID, $instanceID))
			$result = array('error' => $GLOBALS['ajax']->gui->get_error(false) . "<br />Você não tem permissão para capturar esta instância.");

		$this->disconnect_all();

		return $result;
	}

	/**
	 * Transforma a instância em exceção
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return mixed Array contendo uma mensagem de erro ou um booleano (true) informando que a ação foi feita com sucesso
	 * @access public
	 */
	function inboxActionException($params)
	{
		$instanceID = (int) $params['instanceID'];
		$activityID = (int) $params['activityID'];
		$result = true;

		if (!$GLOBALS['ajax']->gui->gui_exception_instance($activityID, $instanceID))
			$result = array('error' => $GLOBALS['ajax']->gui->get_error(false) . "<br />Você não tem permissão para transformar esta instância em exceção.");

		$this->disconnect_all();

		return $result;
	}

	/**
	 * Retira uma instância em exceção
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return mixed Array contendo uma mensagem de erro ou um booleano (true) informando que a ação foi feita com sucesso
	 * @access public
	 */
	function inboxActionResume($params)
	{
		$instanceID = (int) $params['instanceID'];
		$activityID = (int) $params['activityID'];
		$result = true;

		if (!$GLOBALS['ajax']->gui->gui_resume_instance($activityID, $instanceID))
			$result = array('error' => $GLOBALS['ajax']->gui->get_error(false) . "<br />Você não tem permissão para retirar de exceção esta instância.");

		$this->disconnect_all();

		return $result;
	}

	/**
	 * Aborta uma instância
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return mixed Array contendo uma mensagem de erro ou um booleano (true) informando que a ação foi feita com sucesso
	 * @access public
	 */
	function inboxActionAbort($params)
	{
		$instanceID = (int) $params['instanceID'];
		$activityID = (int) $params['activityID'];
		$result = true;

		if (!$GLOBALS['ajax']->gui->gui_abort_instance($activityID, $instanceID))
			$result = array('error' => $GLOBALS['ajax']->gui->get_error(false) . "<br />Você não tem permissão para abortar esta instância.");

		$this->disconnect_all();

		return $result;
	}

	/**
	 * Visualiza dados de uma instância
	 * @param $params Parâmetros advindos da chamada Ajax
	 * @return mixed Array contendo uma mensagem de erro ou um booleano (true) informando que a ação foi feita com sucesso
	 * @access public
	 */
	function inboxActionView($params)
	{
		$instanceID = $params['instanceID'];
		$result = $GLOBALS['ajax']->gui->wf_security->checkUserAction(0, $instanceID, 'view');

		$errorMessage = $GLOBALS['ajax']->gui->get_error(false);
		if (!empty($errorMessage))
		{
			$this->disconnect_all();
			return array('error' => $errorMessage);
		}

		$instance = &Factory::newInstance('Instance');
		$instance->getInstance($instanceID);

		$process = &Factory::newInstance('Process');
		$process->getProcess($instance->pId);

		$result = array(
			'wf_status' => $instance->status,
			'wf_p_id' => $instance->pId,
			'wf_procname' => $process->name,
			'wf_version' => $process->version,
			'wf_instance_id' => $instance->instanceId,
			'wf_priority' => $instance->priority,
			'wf_owner' => Factory::getInstance('WorkflowLDAP')->getName($instance->owner),
			'wf_next_activity' => $instance->nextActivity,
			'wf_next_user' => Factory::getInstance('WorkflowLDAP')->getName($instance->nextUser),
			'wf_name' => $instance->name,
			'wf_category' => $instance->category,
			'wf_started' => date('d/m/Y H:i', $instance->started)
		);

		$viewActivityID = $GLOBALS['ajax']->gui->gui_get_process_view_activity($instance->pId);
		if ($viewActivityID !== false)
			if ($GLOBALS['ajax']->gui->wf_security->checkUserAction($viewActivityID, $instanceID, 'viewrun'))
				$result['viewRunAction'] = array('viewActivityID' => $viewActivityID, 'height' => $GLOBALS['ajax']->gui->wf_security->processesConfig[$instance->pId]['iframe_view_height'], 'useHTTPS' => (($_SESSION['phpgw_info']['workflow']['server']['use_https'] > 0) ? $GLOBALS['ajax']->gui->wf_security->processesConfig[$instance->pId]['execute_activities_using_secure_connection'] : 0));

    	if ($instance->ended > 0)
    		$result['wf_ended'] = date('d/m/Y H:i', $instance->ended);
    	else
    		$result['wf_ended'] = "";

		$ldap = &Factory::getInstance('WorkflowLDAP');
		foreach ($instance->workitems as $line)
		{
    		$line['wf_duration'] = $this->time_diff($line['wf_ended']-$line['wf_started']);
    		$line['wf_started'] = date('d/m/Y H:i', $line['wf_started']);
    		$line['wf_ended'] = date('d/m/Y H:i', $line['wf_ended']);
    		$line['wf_user'] = $ldap->getName($line['wf_user']);
    		$result['wf_workitems'][] = $line;
    	}

		foreach ($instance->activities as $line)
		{
    		$line['wf_started'] = date('d/m/Y H:i', $line['wf_started']);
			if ($line['wf_ended'] > 0)
				$line['wf_ended'] = date('d/m/Y H:i', $line['wf_ended']);
			else
				$line['wf_ended'] = "";
    		$line['wf_user'] = $ldap->getName($line['wf_user']);
    		$result['wf_activities'][] = $line;
    	}

    	$show_properties = false;

		$current_user_id = $_SESSION['phpgw_info']['workflow']['account_id'];

		/* check if the current user is a process admin */
    	$is_admin_process = $GLOBALS['ajax']->acl->check_process_access($current_user_id, $instance->pId);
    	$is_admin_workflow = $_SESSION['phpgw_info']['workflow']['user_is_admin'];
		$show_properties = ($is_admin_process || $is_admin_workflow);

		if($show_properties)
		{
			foreach ($instance->properties as $key => $value)
			{
    			$result['wf_properties']['keys'][] = $key;
    			$result['wf_properties']['values'][] = wordwrap(htmlspecialchars($value), 80, "<br>", 1);
    		}
    	}

		$this->disconnect_all();
		return $result;
	}

	/**
	 * Retorna os idiomas  
	 * @return array  
	 * @access public
	 */	
	function getLang(){
			
		$keys = array();
		$values = array();
		$langs = array();
		foreach($_SESSION['phpgw_info']['workflow']['lang'] as $key => $value) {
			$keys[] 	= $key;
			$values[] 	= $value;
		}
		array_push($langs,$keys,$values);
		return $langs;
	}
	
	/**
	 * Return  a given duration in human readable form, usefull for workitems duration 
	 * 
	 *  @param $to
	 *  @return string a given duration in human readable form, usefull for workitems duration
	 */
	function time_diff($to) {
		$days = (int)($to/(24*3600));
		$to = $to - ($days*(24*3600));
		$hours = (int)($to/3600);
		$to = $to - ($hours*3600);
		$min = date("i", $to);
		$to = $to - ($min*60);			
		$sec = date("s", $to);

		return tra('%1 days, %2:%3:%4',$days,$hours,$min,$sec);
	}
	
	/**
	 *  Instances
	 *  @param $params
	 *  @return string a given duration in human readable form, usefull for workitems duration
	 *  @access public
	 */
	function instances($params)
	{
		$preferences = $_SESSION['phpgw_info']['workflow']['user']['preferences'];

		$lightVersion = ((isset($preferences['use_light_interface']) ? $preferences['use_light_interface'] : 0) == 1);

		/* get some parameters */
		$userID = $_SESSION['phpgw_info']['workflow']['account_id'];
		$pid = (int) $params['pid'];
		$active = ($params['active'] == 1);

		$defaultSorting = ($active ? 'wf_act_started__DESC' : 'wf_started__DESC');
		$availableSortings = $active ?
			array('wf_act_started', 'wf_procname', 'wf_name', 'insname') :
			array('wf_started', 'wf_ended', 'wf_procname', 'insname');

		/* define the sorting */
		$sort = $defaultSorting;
		if ($params['sort'])
			$sort = $params['sort'];
		/* make sure that the sorting parameter is one of the expected values */
		$sortFields = explode('__', strtolower($sort));
		if (count($sortFields) != 2)
			$sort = $defaultSorting;
		else
			if (!(in_array($sortFields[0], $availableSortings) && in_array($sortFields[1], array('desc', 'asc'))))
				$sort = $defaultSorting;
		$params['sort'] = $sort;


		/* retrieve the results */
		$result = $GLOBALS['ajax']->gui->gui_list_instances_by_owner($userID, 0, -1, $sort, '', '', false, 0, $active, !$active, $active, !$active);

		$errorMessage = $GLOBALS['ajax']->gui->get_error(false);
		if (!empty($errorMessage))
		{
			$this->disconnect_all();
			return array('error' => $errorMessage);
		}

		$output['params'] = $params;
		$output['instances'] = array();
		$output['processes'] = array();
		$list_process = array();
		$cod_process = array();

		$ldap = &Factory::getInstance('WorkflowLDAP');
		$viewActivitiesID = array();
		foreach ($result['data'] as $row)
		{
			if (($pid == 0) || ($row['wf_p_id'] == $pid))
			{
				$row['wf_started'] = date('d/m/Y H:i', $row['wf_started']);
				if ($row['wf_ended'])
					$row['wf_ended'] = date('d/m/Y H:i', $row['wf_ended']);

				if ($row['wf_act_started'])
					$row['wf_act_started'] = date('d/m/Y H:i', $row['wf_act_started']);

				$row['wf_user_fullname'] = '';
				if ($row['wf_user'] != '*' && $row['wf_user'] != '')
					$row['wf_user_fullname'] = $ldap->getName($row['wf_user']);

				/* load information about the view activity */
				if (!isset($viewActivitiesID[$row['wf_p_id']]))
					$viewActivitiesID[$row['wf_p_id']] = $GLOBALS['ajax']->gui->gui_get_process_view_activity($row['wf_p_id']);
				if ($viewActivitiesID[$row['wf_p_id']] !== false)
					if ($GLOBALS['ajax']->gui->wf_security->checkUserAction($viewActivitiesID[$row['wf_p_id']], $row['wf_instance_id'], 'viewrun'))
						$row['viewRunAction'] = array('viewActivityID' => $viewActivitiesID[$row['wf_p_id']], 'height' => $GLOBALS['ajax']->gui->wf_security->processesConfig[$instance->pId]['iframe_view_height']);

				$output['instances'][] = $row;
			}
			$processNameAndVersion = $row['wf_procname'] . " (v" . $row['wf_version'] . ")";
			if (!isset($list_process[$processNameAndVersion]))
				$list_process[$processNameAndVersion] = array('total' => 1, 'pid' => $row['wf_p_id'], 'name' => $processNameAndVersion);
			else
				$list_process[$processNameAndVersion]['total']++;
		}

		ksort($list_process);
		$output['processes'] = array_values($list_process);

		$userNames = array();
		$processesInfo = array();
		$activityNames = array();
		foreach ($output['instances'] as $key => $value)
		{
			$userNames[$value['wf_user']] = $value['wf_user_fullname'];
			unset($output['instances'][$key]['wf_user_fullname']);

			$processesInfo[$value['wf_p_id']] = array(
				'name' => $value['wf_procname'] . ' (v' . $value['wf_version'] . ')',
				'useHTTPS' => (($_SESSION['phpgw_info']['workflow']['server']['use_https'] > 0) ? $GLOBALS['ajax']->gui->wf_security->processesConfig[$value['wf_p_id']]['execute_activities_using_secure_connection'] : 0)
			);

			if ($active)
			{
				$activityNames[$value['wf_activity_id']] = $value['wf_name'];
				unset($output['instances'][$key]['wf_name']);
			}

			if (is_null($value['insname']))
				$output['instances'][$key]['insname'] = '';
		}

		$output['userNames'] = $userNames;
		$output['processesInfo'] = $processesInfo;
		$output['activityNames'] = $activityNames;

		$output['light'] = $lightVersion;

		if (!isset($params['group_instances']))
		{
			/* paginate the result */
			$itemsPerPage = isset($_SESSION['phpgw_info']['workflow']['user']['preferences']['ui_items_per_page']) ? $_SESSION['phpgw_info']['workflow']['user']['preferences']['ui_items_per_page'] : 15;
			$paging = Factory::newInstance('Paging', $itemsPerPage, $_POST);
			$output['instances'] = $paging->restrictItems($output['instances']);
			$output['paging_links'] = $paging->commonLinks();
		}
		else
			unset($output['instances']);

		$this->disconnect_all();

		return $output;
	}

	/**
	 * Aplicacoes externas do usuario
	 * @return array
	 * @access public
	 */
	function externals()
	{
		$webserver_url = $_SESSION['phpgw_info']['workflow']['server']['webserver_url'];
		$templateServer = &Factory::getInstance('TemplateServer');

		/* load the sites that the user can access */
		$allowedSites = $this->so->getExternalApplications();

		/* prepare the data for the javascript */
		$output = array();
		foreach ($allowedSites as $row)
		{
			if ($row['image'] == "")
				$row['image'] = $templateServer->generateImageLink('navbar.png');
			else
				$row['image'] = $webserver_url . '/workflow/redirect.php?file=/external_applications/' . $row['image'];

			if ($row['authentication'] == 1)
				$row['wf_ext_link'] = (($_SESSION['phpgw_info']['workflow']['server']['use_https'] > 0) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "$webserver_url/index.php?menuaction=workflow.external_bridge.render&site=" . $row['external_application_id'];
			else
				$row['wf_ext_link'] = $row['address'];

			$output[] = $row;
		}

		return $output;
	}

	/**
	 * Verifica se o usuário tem acesso ao Organograma (e se o mesmo está ativo)
	 * @return mixed true em caso de sucesso ou uma array contendo mensagens sobre o problema (não cadastrado ou organograma não ativo)
	 * @access private
	 */
	private function checkOrgchartAccess()
	{
		$this->organizationInfo = $this->so->getUserOrganization($_SESSION['phpgw_info']['workflow']['account_id']);
		if ($this->organizationInfo === false)
			return array('warning' => 'Você não está cadastrado em nenhuma organização');

		if ($this->organizationInfo['ativa'] == 'N')
			return array('warning' => 'Organograma indisponível');

		return true;
	}

	/**
	 * Organograma
	 * @return array com as areas da organizacao 
	 * @access public
	 */
	function orgchart()
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		$this->organizationInfo['areas'] = $this->getHierarchicalArea();
		return $this->organizationInfo;
	}

	/**
	 * Retorna a lista de centros de custo
	 * @return array Lista de centros de custo
	 * @access public
	 */
	function getCostCenters()
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		return $this->so->getCostCenters($this->organizationInfo['organizacao_id']);
	}

	/**
	 * Get the hierarchical Area
	 * @return array 
	 * @access public
	 */
	function getHierarchicalArea()
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		return $this->so->getHierarchicalArea($this->organizationInfo['organizacao_id']);
	}

	/**
	 * Retorna a lista de areas
	 * @return array lista de areas
	 * @access public
	 */
	function getAreaList()
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		$areas = $this->so->getAreaList($this->organizationInfo['organizacao_id']);
        $areas_count = count($areas);
		for ($i = 0; $i < $areas_count; ++$i)
		{
			$areas[$i]['children'] = false;
			$areas[$i]['depth'] = 1;
		}

		return $areas;
	}

	/**
	 * Retorna a lista de categorias
	 * @return array lista de categorias
	 * @access public
	 */
	function getCategoriesList()
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		return $this->so->getCategoriesList($this->organizationInfo['organizacao_id']);
	}


	/**
	 * Return the area of employee
	 * @param $params parameters
	 * @access public
	 * @return array array of employees
	 */
	function getAreaEmployees($params)
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		$employees = $this->so->getAreaEmployees((int) $params['areaID'], $this->organizationInfo['organizacao_id']);

		if ($employees === false)
			return array('error' => 'Área não encontrada.');

		return $employees;
	}

	/**
	 * Return the area of employee
	 * @param $params parameters
	 * @access public
	 * @return array array of employees
	 */
	function getCategoryEmployees($params)
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		$employees = $this->so->getCategoryEmployees((int) $params['categoryID'], $this->organizationInfo['organizacao_id']);

		if ($employees === false)
			return array('error' => 'Categoria não encontrada.');

		usort($employees['employees'], create_function('$a,$b', 'return strcasecmp($a[\'cn\'],$b[\'cn\']);'));

		return $employees;
	}

	/**
	 * Search Employee
	 * @param $params
	 * @access public
	 * @return array search result
	 */
	function searchEmployee($params)
	{
		if (!preg_match('/^([[:alnum:] -]+)$/', $params['searchTerm']))
			return array('error' => 'Parâmetro de busca inválido');

		if (strlen(str_replace(' ', '', $params['searchTerm'])) < 2)
			return array('error' => 'Utilize ao menos duas letras em sua busca');

		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		$result = array();

		/* do the search */
		$result['bytelephone'] = $this->so->searchEmployeeByTelephone($params['searchTerm'], $this->organizationInfo['organizacao_id']);
		$result['employees'] = $this->so->searchEmployeeByName($params['searchTerm'], $this->organizationInfo['organizacao_id']);
		$result['bygroup'] = $this->so->searchEmployeeByArea($params['searchTerm'], $this->organizationInfo['organizacao_id']);

		$this->disconnect_all();

		/* if all searches returned false */
		if (!is_array($result['employees']) and
			!is_array($result['bygroup']) and
			!is_array($result['bytelephone']))
			return array('error' => 'O sistema de busca não pode ser utilizado para sua organização');

		return $result;
	}

	/**
	 * Busca informações sobre um funcionário.
	 * @param array $params Uma array contendo o ID do funcionário cujas informações serão extraídas.
	 * @return array Informações sobre o funcionário.
	 * @access public
	 */
	function getEmployeeInfo($params)
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		$result = $this->so->getEmployeeInfo((int) $params['funcionario_id'], $this->organizationInfo['organizacao_id']);
		if (is_array($result['info']))
		{
			foreach ($result['info'] as $key => $value)
			{
				if ( $value['name'] == 'UIDNumber' )
				{
					unset($result['info'][$key]);
				}
			}
			$result['info'] = array_values($result['info']);
		}
		return $result;
	}

	/**
	 * Busca informações sobre uma área.
	 * @param array $params Uma array contendo o ID da área cujas informações serão extraídas.
	 * @return array Informações sobre a área.
	 * @access public
	 */
	function getAreaInfo($params)
	{
		/* check for access */
		if (($checkWarnings = $this->checkOrgchartAccess()) !== true)
			return $checkWarnings;

		return $this->so->getAreaInfo((int) $params['area_id'], $this->organizationInfo['organizacao_id']);
	}

	/**
	 * Retorna a lista de telefones úteis da organização
	 * @return array Lista de telefones
	 * @access public
	 */
	function getUsefulPhones( )
	{
		/* check for access */
		if ( ($checkWarnings = $this->checkOrgchartAccess( ) ) !== true )
			return $checkWarnings;

		return $this -> so -> getUsefulPhones( $this -> organizationInfo[ 'organizacao_id' ] );
	}

	/**
	 * Retorna a lista as áreas com substituição de chefia
	 * @return array Lista das áreas com substituição de chefia
	 * @access public
	 */
	function getAreaWithSubtituteBoss( )
	{
		/* check for access */
		if ( ($checkWarnings = $this->checkOrgchartAccess( ) ) !== true )
			return $checkWarnings;

		return $this -> so -> getAreaWithSubtituteBoss( $this -> organizationInfo[ 'organizacao_id' ] );
	}

	/**
	 * Retorna a lista de localidades
	 * @return array lista de localidades
	 * @access public
	 */
	function getManning( )
	{
		/* check for access */
		if ( ( $checkWarnings = $this->checkOrgchartAccess( ) ) !== true )
			return $checkWarnings;

		return $this -> so -> getManning( $this -> organizationInfo[ 'organizacao_id' ] );
	}

	/**
	 * Return the employees of a manning
	 * @param $params parameters
	 * @access public
	 * @return array array of employees
	 */
	function getManningEmployees( $params )
	{
		/* check for access */
		if ( ( $checkWarnings = $this -> checkOrgchartAccess( ) ) !== true )
			return $checkWarnings;

		$employees = $this -> so -> getManningEmployees( ( int ) $params[ 'locationID' ], $this -> organizationInfo[ 'organizacao_id' ] );

		if ( $employees === false )
			return array( 'error' => 'Localidade não encontrada.' );

		return $employees;
	}

	/**
	 * Return the list of employees in alphabetical order
	 * @access public
	 * @return array array of employees
	 */
	function getAlphabeticalEmployees( )
	{
		/* check for access */
		if ( ( $checkWarnings = $this -> checkOrgchartAccess( ) ) !== true )
			return $checkWarnings;

		$employees = $this -> so -> getAlphabeticalEmployees( $this -> organizationInfo[ 'organizacao_id' ] );

		if ( $employees === false )
			return array( 'error' => 'Localidade não encontrada.' );

		return $employees;
	}

	function callVoipConnect($params)
	{
		$cachedLDAP = Factory::newInstance('CachedLDAP');
		$cachedLDAP->setOperationMode($cachedLDAP->OPERATION_MODE_LDAP);

		$entry = $cachedLDAP->getEntryByID( $_SESSION['phpgw_info']['workflow']['account_id'] );
		if ( $entry && ! is_null($entry['telephonenumber']) )
			$fromNumber = $entry['telephonenumber'];

		if ( $fromNumber == false )
			return false;

		$toNumber	= $params['to'];

		$voipServer	= $_SESSION['phpgw_info']['workflow']['server']['voip_server'];
		$voipUrl	= $_SESSION['phpgw_info']['workflow']['server']['voip_url'];
		$voipPort	= $_SESSION['phpgw_info']['workflow']['server']['voip_port'];

		if(!$voipServer || !$voipUrl || !$voipPort)
			return false;

		$url		= "http://".$voipServer.":".$voipPort.$voipUrl."?magic=1333&acao=liga&ramal=".$fromNumber."&numero=".$toNumber;			
		$sMethod = 'GET ';
		$crlf = "\r\n";
		$sRequest = " HTTP/1.1" . $crlf;
		$sRequest .= "Host: localhost" . $crlf;
		$sRequest .= "Accept: */* " . $crlf;
		$sRequest .= "Connection: Close" . $crlf . $crlf;            
		$sRequest = $sMethod . $url . $sRequest;    
		$sockHttp = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);            
		if (!$sockHttp)  {
			return false;
		}
		$resSocketConnect = socket_connect($sockHttp, $voipServer, $voipPort);
		if (!$resSocketConnect) {
			return false;
		}
		$resSocketWrite = socket_write($sockHttp, $sRequest, strlen($sRequest));
		if (!$resSocketWrite) {
			return false;
		}    
		$sResponse = '';    
		while ($sRead = socket_read($sockHttp, 512)) {
			$sResponse .= $sRead;
		}            

		socket_close($sockHttp);            
		$pos = strpos($sResponse, $crlf . $crlf);
		return substr($sResponse, $pos + 2 * strlen($crlf));									
	}

	function isVoipEnabled( )
	{
		$voip_enabled = false;
		$voip_groups = array();
		if ( $_SESSION['phpgw_info']['workflow']['voip_groups'] )
		{
			foreach ( explode(",",$_SESSION['phpgw_info']['workflow']['voip_groups']) as $i => $voip_group )
			{
				$a_voip = explode(";",$voip_group);
				$voip_groups[] = $a_voip[1];
			}

			foreach($_SESSION['phpgw_info']['workflow']['user_groups'] as $idx => $group)
			{
				if(array_search($group,$voip_groups) !== FALSE)
				{
					$voip_enabled = true;
					break;
				}
			}
		}

		return ( $voip_enabled ) ? 'VoipIsEnabled' : 'VoipIsDisabled';
	}
}
?>
