<?php
/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once('class.bo_ajaxinterface.inc.php');
require_once(PHPGW_API_INC . SEP . 'common_functions.inc.php');

/**
 * Implementa métodos para administração do controle de acesso ao workflow.
 * Controla administradores de processos e administradores de organograma
 *
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Rodrigo Daniel C de Lira - rodrigo.lira@gmail.com
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class bo_adminaccess extends bo_ajaxinterface
{
	/**
	* @var object $so Objeto para acesso à camada de dados
	* @access public
	*/
	var $so;

	/**
	* Construtor
	*
	* @return object
	* @access public
	*/
	function bo_adminaccess()
	{
		parent::bo_ajaxinterface();

		if (!Factory::getInstance('workflow_acl')->checkWorkflowAdmin($_SESSION['phpgw_info']['workflow']['account_id']))
			exit(serialize(array('error' => 'Você não tem permissão para executar esta operação.')));

		$this->so = &Factory::getInstance('so_adminaccess', Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
	}

	/**
	* Obtém a lista de processos (consulta ao engine)
	*
	* @return array process data
	* @access public
	*/
	function get_all_processes()
	{
		$proc_mng = &Factory::newInstance('ProcessManager');
		$proc_list = $proc_mng->list_processes(0,-1,'wf_name__ASC');

		$proc_data = array();

		foreach($proc_list['data'] as $p)
		{
			$proc_data[] = array(
				'proc_st_name' => $p['wf_name'] . ' ' . $p['wf_version'],
				'proc_in_id'   => $p['wf_p_id']
			);
		}

		@sort($proc_data);
		@reset($proc_data);

		return $proc_data;
	}

	/**
	* Obtém a lista de administradores de um organograma
	*
	* @param array $p processo
	* @return array ids dos admins do organograma
	* @access public
	*/
	function get_organogram_admins($p)
	{
		$admins_id = $this->so->get_organogram_admins_id($p['org_id']);
		return $this->so->getUserNames($admins_id);
	}
	
	/**
	* Retorna o nivel administrativo do organograma do processo
	*
	* @param array $p processo
	* @return array o nivel administrativo do monitor do processo
	* @access public
	*/
	function get_organogram_admin_level($p)
	{
		return $this->so->getUserAdminLevel('ORG', $p['uid'], $p['pid']);
	}

	/**
	* Obtém a lista de administradores de um processo
	*
	* @param array $p processo
	* @return array lista de administradores de um processo
	* @access public
	*/
	function get_process_admins($p)
	{
		$admins_id = $this->so->get_process_admins_id($p['proc_id']);
		return $this->so->getUserNames($admins_id);
	}

	/**
	* Remove um administrador de organograma
	*
	* @param array $p processo
	* @return array lista de administradores de um organograma
	* @access public
	*/
	function del_organogram_admin($p)
	{
		$this->so->del_organogram_admin($p['org_id'],$p['admin_id']);
        return $this->get_organogram_admins($p);
	}

	/**
	* Remove um administrador de um processo
	*
	* @param array $p processo
	* @return array lista de administradores de um processo
	* @access public
	*/
	function del_process_admin($p)
	{
		$this->so->del_process_admin($p['proc_id'],$p['admin_id']);
        return $this->get_process_admins($p);
	}

	/**
	* Insere administradores de organograma
	*
	* @param array $p processo
	* @return array administradores do organograma
	* @access public
	*/
	function add_organogram_admins($p)
	{
		$ids = explode('.', str_replace('u','',$p['user_ids'] ) );
		$this->so->add_organogram_admins($p['org_id'],$ids);
		return $this->get_organogram_admins($p);
	}

	/**
	* Insere administradores de processo
	*
	* @param array $p processo
	* @return array administradores do processo
	* @access public
	*/
	function add_process_admins($p)
	{
		$ids = explode('.', str_replace('g', '', str_replace('u','',$p['user_ids'])));
		$this->so->add_process_admins($p['proc_id'],$ids);
		return $this->get_process_admins($p);
	}
    /**
	* Retorna os monitores do processo
	*
	* @param array $p processo
	* @return array monitores do processo
	* @access public
	*/
	function get_monitor_admins($p)
	{
		$admins_id = $this->so->getResourceAdmins('MON', $p['proc_id']);
		return $this->so->getUserNames($admins_id);
	}
    /**
	* Adiciona monitores do processo
	*
	* @param array $p processo
	* @return array monitores do processo
	* @access public
	*/
	function add_monitor_admins($p)
	{
		$ids = explode('.', str_replace('u','',$p['user_ids'] ) );
		$this->so->addAdmin('MON', $ids, $p['proc_id']);
		return $this->get_monitor_admins($p);
	}
	/**
	* Remove monitores do processo
	*
	* @param array $p
	* @return array monitores do processo
	* @access public
	*/
	function del_monitor_admin($p)
	{
		$this->so->removeAdmin('MON', $p['admin_id'], $p['proc_id']);
        return $this->get_monitor_admins($p);
	}

	/**
	* Retorna o nivel administrativo do monitor do processo
	*
	* @param array $p processo
	* @return array o nivel administrativo do monitor do processo
	* @access public
	*/
	function get_monitor_admin_level($p)
	{
		return $this->so->getUserAdminLevel('MON', $p['uid'], $p['pid']);
	}
	/**
	* Seta o nivel administrativo do monitor
	*
	* @param array $p processo
	* @return arraym
	* @access public
	*/
	function set_monitor_admin_level($p)
	{
		$np = explode('_', $p['np']);
		$levels = array();
		foreach ($np as $pair)
		{
			list($key, $value) = explode('=', $pair, 2);
			$levels[$key] = ($value == '1') ? true : false;
		}

		$this->so->setAdminLevel('MON', $p['uid'], $p['pid'], $levels);
		return null;
	}
	
	/**
	* Seta o nivel administrativo do organograma
	*
	* @param array $p processo
	* @return arraym
	* @access public
	*/
	function set_organogram_admin_level($p)
	{
		$np = explode('_', $p['np']);
		$levels = array();
		foreach ($np as $pair)
		{
			list($key, $value) = explode('=', $pair, 2);
			$levels[$key] = ($value == '1') ? true : false;
		}
		

		$result = $this->so->setAdminLevel('ORG', $p['uid'], $p['pid'], $levels);
		
		if (!$result) {
			return "Não atualizou as permissões.";
		}
		
	}

	/**
	* Busca os usuários/grupos que possuem acesso a uma determinada aplicação externa
	*
	* @param array $params Lista de parâmetros vindas do Ajax
	* @return array lista dos usuarios com direito de accesso
	* @access public
	*/
	function getExternalApplicationAdmins($params)
	{
		$admins_id = $this->so->getResourceAdmins('APX', $params['external_application_id']);
		return $this->so->getUserNames($admins_id);
	}

	/**
	* Remove o acesso de um usuário/grupo a uma aplicação externa
	*
	* @param array $params Lista de parâmetros vindas do Ajax
	* @return array lista de administradores de uma aplicacao externa
	* @access public
	*/
	function deleteExternalApplicationAdmin($params)
	{
		$this->so->removeAdmin('APX', $params['admin_id'], $params['external_application_id']);
        return $this->getExternalApplicationAdmins($params);
	}

	/**
	* Dá acesso para um usuário/grupo a uma aplicação externa
	*
	* @param array $params Lista de parâmetros vindas do Ajax
	* @return array lista de administradores de uma aplicacao externa
	* @access public
	*/
	function addExternalApplicationAdmins($params)
	{
		$ids = explode('.', str_replace('g', '', str_replace('u', '', $params['user_ids'])));
		$this->so->addAdmin('APX', $ids, $params['external_application_id']);
		return $this->getExternalApplicationAdmins($params);
	}

	/**
	* Busca os usuários/grupos que podem administrar o módulo Workflow
	*
	* @param array $params Lista de parâmetros advindos do Ajax
	* @return array Lista dos usuários com direito de administrar o módulo Workflow
	* @access public
	*/
	function getWorkflowAdministrators()
	{
		$admins_id = $this->so->getResourceAdmins('ADM', 0);
		return $this->so->getUserNames($admins_id);
	}

	/**
	* Dá privilégio de administrar o módulo Workflow para um usuário/grupo
	*
	* @param array $params Lista de parâmetros advindos do Ajax
	* @return array Lista dos usuários com direito de administrar o módulo Workflow
	* @access public
	*/
	function addWorkflowAdministrators($params)
	{
		$ids = explode('.', str_replace('g', '', str_replace('u', '', $params['user_ids'])));
		$this->so->addAdmin('ADM', $ids, 0);
		return $this->getWorkflowAdministrators($params);
	}

	/**
	* Remove o privilégio de administrar o módulo Workflow de um usuário/grupo
	*
	* @param array $params Lista de parâmetros advindos do Ajax
	* @return array Lista dos usuários com direito de administrar o módulo Workflow
	* @access public
	*/
	function deleteWorkflowAdministrators($params)
	{
		$this->so->removeAdmin('ADM', $params['admin_id'], 0);
        return $this->getWorkflowAdministrators($params);
	}

	/**
	* Busca os usuários/grupos que podem criar/administrar processos de Workflow
	*
	* @param array $params Lista de parâmetros vindas do Ajax
	* @return array Lista dos usuarios com direito de criar/administrar processos
	* @access public
	*/
	function getDevelopmentAdministrators()
	{
		$GLOBALS['phpgw']->db = $GLOBALS['ajax']->db;
		$acl = &Factory::getInstance('acl');
		$output = $this->so->getUserNames($acl->get_ids_for_location('admin_workflow', 1, 'workflow'));
		unset($GLOBALS['phpgw']->db);
		return $output;
	}

	/**
	* Dá privilégio de criar/administrar processos de Workflow para um usuário/grupo
	*
	* @param array $params Lista de parâmetros vindas do Ajax
	* @return array lista dos usuarios com direito de criar/administrar processos
	* @access public
	*/
	function addDevelopmentAdministrators($params)
	{
		$ids = explode('.', str_replace('g', '', str_replace('u', '', $params['user_ids'])));
		$GLOBALS['phpgw']->db = $GLOBALS['ajax']->db;
		$acl = &Factory::getInstance('acl');
		foreach ($ids as $id)
			$acl->add_repository('workflow', 'admin_workflow', $id, 1);

		unset($GLOBALS['phpgw']->db);
		return $this->getDevelopmentAdministrators($params);
	}

	/**
	* Remove o privilégio de criar/administrar processos de Workflow de um usuário/grupo
	*
	* @param array $params Lista de parâmetros vindas do Ajax
	* @return array Lista dos usuarios com direito de criar/administrar processos
	* @access public
	*/
	function deleteDevelopmentAdministrators($params)
	{
		$GLOBALS['phpgw']->db = $GLOBALS['ajax']->db;
		$acl = &Factory::getInstance('acl');
		$acl->delete_repository('workflow', 'admin_workflow', (int) $params['admin_id']);
		unset($GLOBALS['phpgw']->db);

		return $this->getDevelopmentAdministrators($params);
	}

	/**
	* Retorna os recursos disponiveis ao Administrador
	*
	* @param string $type type of resource
	* @param integer $rid resource id
	* @return array array de recursos permitidos
	* @access public
	*/
	function getResourceAdmins($type, $rid)
	{
		return $this->so->getResourceAdmins($type, $rid);
	}

	/**
	* Verifica se o usuario tem acesso a um tipo de recurso
	*
	* @param string $type type of resource
	* @param integer $uid user id
	* @return bool accesso permitido false nao
	* @access public
	*/
	function checkUserAccessToType($type, $uid)
	{
		return $this->so->checkUserAccessToType($type, $uid);
	}

	/**
	* Verifica se o usuário tem direito de acesso a um recurso
	*
	* @param string $type tipo de recurso
	* @param integer $uid id do usuário
	* @param integer $rid id do recurso
	* @return bool true accesso permitido false nao
	* @access public
	*/
	function checkUserAccessToResource($type, $uid, $rid, $requiredLevel = null)
	{
		return $this->so->checkUserAccessToResource($type, $uid, $rid, $requiredLevel);
	}

	/**
	* Retorna as permissoes do usúario
	*
	* @param string $type tipo do usuario
	* @param integer $uid id do usuário
	* @param integer $rid id do recurso
	* @return array array com as permissoes do usuario
	* @access public
	*/
	function getUserPermissions($type, $uid)
	{
		return $this->so->getUserPermissions($type, $uid);
	}

	/**
	* Informa o nível de administração do usuário
	* @param string $type O tipo do recurso
	* @param int $uid O ID do usuário
	* @param int $numvalue O ID do recurso
	* @return array As permissões do usuário (em forma de número e de bits)
	* @access public
	*/
	function getUserAdminLevel($type, $uid, $rid)
	{
		return $this->so->getUserAdminLevel($type, $uid, $rid);
	}

	/**
	* Informa o nível de administração do usuário (incluindo herança por grupo)
	* @param string $type O tipo do recurso
	* @param int $uid O ID do usuário
	* @param int $numvalue O ID do recurso
	* @return array As permissões do usuário, inclusive herança por grupo. As permissões são retornadas em forma de número e de bits
	* @access public
	*/
	function getUserGroupAdminLevel($type, $uid, $numvalue)
	{
		return $this->so->getUserGroupAdminLevel($type, $uid, $rid);
	}
}

?>
