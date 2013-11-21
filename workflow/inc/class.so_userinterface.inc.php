<?php

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class so_userinterface
{
	/**
	 * @var object database object
	 * @access public
	 */
	var $db;
	/**
	 * @var bool True se o usuário for administrador do expresso.
	 * @access private
	 */
	private $isAdmin;

	/**
	 * @var int ID do usuário logado no Expresso
	 * @access private
	 */
	private $userID;

	/**
	 * @var object Link para a ACL do Workflow.
	 * @access private
	 */
	private $acl;
	
	/**
	 * @var bool indicando se o usuário possui ou não acesso aos dados restritos.
	 * @access private
	 */
	private	$authorized;
	
	/**
	 * Constructor
	 * @access public
	 * @return object
	 */
	function so_userinterface()
	{
		$this->authorized= false;
		$this->userID = $_SESSION['phpgw_info']['workflow']['account_id'];
		$this->isAdmin = $_SESSION['phpgw_info']['workflow']['user_is_admin'];
		$this->acl = Factory::getInstance('workflow_acl');
		$this->db =& Factory::getInstance('WorkflowObjects')->getDBWorkflow()->Link_ID;
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
	}

	/**
	 * Obtain the subnet of an IP Address based on the numer of bits of the subnet
	 * @param string $ip The IP Address
	 * @param int $bits Number of bits of the subnet
	 * @return string The subnet of the IP
	 * @access private
	 */
	private function getSubnet($ip, $bits)
	{
		$octets = explode('.', $ip);
		$output = array();
		for ($i = 0; $i < 4; ++$i)
		{
			if ($bits >= 8)
			{
				$output[] = $octets[$i];
				$bits -= 8;
				continue;
			}
			$output[] = $octets[$i] & bindec(str_repeat('1', $bits) . str_repeat('0',8 - $bits));
			$bits = 0;
		}
		return implode('.', $output);
	}

	/**
	 * Get External Applications
	 * @access public
	 * @return array list of external applications
	 */
	function getExternalApplications()
	{
		/* load the intranet subnetworks */
		$oldDB = $GLOBALS['phpgw']->db;
		$GLOBALS['phpgw']->db = $GLOBALS['ajax']->db;
		$config = &Factory::getInstance('config', 'workflow');
		$configValues = $config->read_repository();
		$submasksString = $configValues['intranet_subnetworks'];
		$GLOBALS['phpgw']->db = $oldDB;

		$userIP = getenv('REMOTE_ADDR');
		if (getenv('HTTP_X_FORWARDED_FOR'))
		{
			$tmpIP = explode(',', getenv('HTTP_X_FORWARDED_FOR'));
			$userIP = $tmpIP[0];
		}

		/* check if the user has access to intranet applications, i.e., is in the intranet */
		$showIntranetApplications = false;
		$submasks = explode(';', $submasksString);
		foreach ($submasks as $submask)
		{
			list($ip,$bits) = explode('/', trim($submask) . '/32', 2);
			if ($this->getSubnet($ip, $bits) == $this->getSubnet($userIP, $bits))
			{
				$showIntranetApplications = true;
				break;
			}
		}

		$preOutput = array();
		$output = array();

		/* select the sites that the user can access */
		$externalApplicationsID = $GLOBALS['ajax']->acl->getUserGroupPermissions('APX', $_SESSION['phpgw_info']['workflow']['account_id']);
		if (!empty($externalApplicationsID))
		{
			$result = Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID->query("SELECT DISTINCT external_application_id, name, address, image, authentication, intranet_only FROM egw_wf_external_application WHERE (external_application_id IN (" . implode(', ', $externalApplicationsID)  . ")) ORDER BY name");
			$preOutput = $result->GetArray(-1);

			/* keep only associative elments and check if the user can access an intranet application */
			for ($i = 0; $i < count($preOutput); ++$i)
			{
				if (($preOutput[$i]['intranet_only'] == '1') && (!$showIntranetApplications))
					continue;

				for ($j = 0; $j < $result->_numOfFields; ++$j)
					unset($preOutput[$i][$j]);
				$output[] = $preOutput[$i];
			}
		}

		return $output;
	}

	/**
	 * Get User Organization ID
	 * @param int $userID User identifier
	 * @return mixed Informações sobre a organização ou false em caso de erro.
	 * @access public
	 */
	function getUserOrganization($userID)
	{
		$query = "SELECT o.organizacao_id, o.nome, o.descricao, o.url_imagem, o.ativa FROM funcionario f, organizacao o WHERE (o.organizacao_id = f.organizacao_id) AND (f.funcionario_id = ?)";
		$result = $this->db->query($query, array((int) $userID));

		$output = $result->fetchRow(DB_FETCHMODE_ASSOC);
		if (!$output)
			return false;

		for ($i = 0; $i < $result->_numOfFields; ++$i)
			unset($output[$i]);

		return $output;
	}

	/**
	 * Get cost center list
	 * @param int $organizationID The organization ID
	 * @return array Lista de centros de custo
	 * @access public
	 */
	function getCostCenters($organizationID)
	{
		$result = $this->db->query("SELECT nm_centro_custo, grupo, descricao FROM centro_custo WHERE (organizacao_id = ?) ORDER BY descricao", array($organizationID));

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Get hierarchical Area
	 * @return array
	 * @access public
	 */
	function getHierarchicalArea($organizationID, $parent = null, $depth = 0)
	{
		if (is_null($parent)){
			$query = "SELECT a.area_id, a.sigla, a.titular_funcionario_id FROM area a";
			$query .=" INNER JOIN area_status a_s ON (a_s.area_status_id = a.area_status_id)";
			$query .=" WHERE (a.superior_area_id IS NULL) AND (a.organizacao_id = ?) AND (a.ativa = 'S') ORDER BY a_s.nivel, a.sigla";
			$result = $this->db->query($query, array($organizationID));
		} else {
			$query = "SELECT a.area_id, a.sigla, a.titular_funcionario_id FROM area a";
			$query .=" INNER JOIN area_status a_s ON (a_s.area_status_id = a.area_status_id)";
			$query .=" WHERE (a.superior_area_id = ?) AND (a.ativa = 'S') ORDER BY a_s.nivel, a.sigla";
			$result = $this->db->query($query, array($parent));
		}

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
		{
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

			$output[$i]['children'] = $this->getHierarchicalArea($organizationID, $output[$i]['area_id'], $depth + 1);
			$output[$i]['depth'] = $depth;
		}

		return $output;
	}

	/**
	 * Get organization area list
	 * @return array
	 * @access public
	 */
	function getAreaList($organizationID)
	{
		$result = $this->db->query("SELECT area_id, sigla FROM area WHERE (organizacao_id = ?) AND (ativa = 'S') ORDER BY sigla", array($organizationID));

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Get organization categories list
	 * @param int $organizationID The organization ID
	 * @return array The categories list
	 * @access public
	 */
	function getCategoriesList($organizationID)
	{
		$output = $this->db->query('SELECT funcionario_categoria_id, descricao FROM funcionario_categoria WHERE (organizacao_id = ?)', array($organizationID))->GetArray();
		$numerOfEmployees = $this->db->GetAssoc('SELECT COALESCE(f.funcionario_categoria_id, 0) AS funcionario_categoria_id, COUNT(*) FROM funcionario f, funcionario_status fs WHERE (f.organizacao_id = ?) AND (f.funcionario_status_id = fs.funcionario_status_id) AND (fs.exibir = \'S\') GROUP BY funcionario_categoria_id', array($organizationID));

		$output[] = array(
			'funcionario_categoria_id' => 0,
			'descricao' => 'Sem Vínculo'
		);

		foreach ($output as &$row)
			$row['contagem'] .= (isset($numerOfEmployees[$row['funcionario_categoria_id']]) ? $numerOfEmployees[$row['funcionario_categoria_id']] : 0);

		return $output;
	}	

	/**
	 * Checa se o usuário possui permissão para visualizar informações restritas.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @return void
	 * @access public
	 */
	public function _checkAccess($organizationID = null)
	{
		/* the user is an administrator */
		if ($this->isAdmin)
			$this->authorized=true;

		if (!is_numeric($organizationID))
			$this->authorized = false;
		else
			$this->authorized = $this->acl->checkUserAccessToResource('ORG', $this->userID, (int) $organizationID, 1);
		
	}

	/**
	 * Get Area Employees
	 * @param int $areaID
	 * @param int $organizationID
	 * @return array
	 * @access public
	 */
	function getAreaEmployees($areaID, $organizationID)
	{
		$organizationID = (int) $organizationID;
		$areaID = (int) $areaID;

		require_once dirname(__FILE__) . '/local/classes/class.wf_orgchart.php';
		$orgchart = new wf_orgchart();
		
		/* gather some info from the area */
		$areaInfo = $this->db->query('SELECT COALESCE(a.titular_funcionario_id, -1) AS titular_funcionario_id, COALESCE(s.funcionario_id, -1) AS substituto_funcionario_id FROM area a LEFT OUTER JOIN substituicao s ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) WHERE (a.organizacao_id = ?) AND (a.area_id = ?)', array($organizationID, $areaID))->GetArray(-1);
		if (empty($areaInfo))
			return false;
		$areaInfo = $areaInfo[0];
		$supervisors = '{' . implode(', ', $areaInfo) . '}';

		/* load the employees from the area */
		$query = "SELECT f.funcionario_id, f.organizacao_id, f.area_id, f.funcao, to_char(f.data_admissao,'DD/MM/YYYY') as data_admissao, COALESCE(f.funcionario_categoria_id, 0) AS funcionario_categoria_id FROM funcionario f, funcionario_status s WHERE ((f.area_id = ?) OR (f.funcionario_id = ANY (?))) AND (f.funcionario_status_id = s.funcionario_status_id) AND (s.exibir = ?)";
		$result = $this->db->query($query, array($areaID, $supervisors, 'S'));

		$employees = $result->GetArray(-1);
		$cachedLDAP = Factory::newInstance('CachedLDAP');
		$cachedLDAP->setOperationMode($cachedLDAP->OPERATION_MODE_NORMAL);
		$categoriesCount = array();

		for ($i = 0; $i < count($employees); ++$i)
		{
			/* remove numeric fields */
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($employees[$i][$j]);
			if (!$this->authorized || !isset($employees[$i]['funcao']))
				$employees[$i]['funcao'] = '';
			else
				$employees[$i]['funcao'] = utf8_encode($employees[$i]['funcao']);	
			if (!$this->authorized || !isset($employees[$i]['data_admissao']))
				$employees[$i]['data_admissao'] = '';
			$employees[$i]['cn'] = '';
			$employees[$i]['telephoneNumber'] = '';
			if (in_array($employees[$i]['funcionario_id'], $areaInfo))
				$employees[$i]['chief'] = ($employees[$i]['funcionario_id'] == $areaInfo['titular_funcionario_id']) ? 1 : 2;

			/* try to find the telephone number */
			$entry = $cachedLDAP->getEntryByID($employees[$i]['funcionario_id']);
			if ($entry)
			{
				$employees[$i]['telephoneNumber'] = is_null($entry['telephonenumber']) ? '' : $entry['telephonenumber'];
				$employees[$i]['cn'] = is_null($entry['cn']) ? '' : $entry['cn'];
				$employees[$i]['uid'] = is_null($entry['uid']) ? '' : $entry['uid'];
				$employees[$i]['uidnumber'] = is_null($entry['uidnumber']) ? '' : $entry['uidnumber'];
				$employees[$i]['removed'] = is_null($entry['last_update']);
			}
			
			/*busca o cargo do funcionario*/
			$employeeInfo = $orgchart->getEmployee($employees[$i]['funcionario_id']);

			$cargo = '';

			if ($this->authorized && !empty($employeeInfo['cargo_id']))
			{
				$jobTitleInfo = $orgchart->getJobTitle($employeeInfo['cargo_id']);
				$cargo = $jobTitleInfo['descricao'];
			}
			$employees[$i]['cargo'] = utf8_encode($cargo);	
			
			/*busca o vínculo do funcionario*/
			$vinculo = '';
			if ($this->authorized && !empty($employeeInfo['funcionario_categoria_id']))
			{
				$categoryInfo = $orgchart->getEmployeeCategory($employeeInfo['funcionario_categoria_id']);
				$vinculo=$categoryInfo['descricao'];
			}			
			$employees[$i]['vinculo'] = utf8_encode($vinculo);

			/* count the number of employees in each category */
			$categoryID = $employees[$i]['funcionario_categoria_id'];
			if (isset($categoriesCount[$categoryID]))
				$categoriesCount[$categoryID]++;
			else
				$categoriesCount[$categoryID] = 1;					
		}
		$usedCategories = array_keys($categoriesCount);
		$availableCategories = $this->getCategoriesList($organizationID);
		$output = array();
		$output['employees'] = $employees;
		$output['categories'] = array();
		foreach ($availableCategories as $category)
		{
			if (!in_array($category['funcionario_categoria_id'], $usedCategories))
				continue;

			$category['contagem'] = $categoriesCount[$category['funcionario_categoria_id']];
			$output['categories'][] = $category;
		}

		usort($output['employees'], create_function('$a,$b', 'return strcasecmp($a[\'cn\'],$b[\'cn\']);'));

		return $output;
	}

	/**
	 * Get employees from a specific category
	 * @param int $categoryID The category ID
	 * @param int $organizationID The organization ID
	 * @return array The list o employees of that category
	 * @access public
	 */
	function getCategoryEmployees($categoryID, $organizationID)
	{
		$organizationID = (int) $organizationID;

		/* load the employees from the area */
		if ($categoryID == 0)
		{
			$query = "SELECT f.funcionario_id, f.organizacao_id, a.area_id, a.sigla AS area FROM funcionario f, funcionario_status s, area a WHERE (f.funcionario_status_id = s.funcionario_status_id) AND (f.area_id = a.area_id) AND (f.funcionario_categoria_id IS NULL) AND (s.exibir = ?) AND (f.organizacao_id = ?)";
			$result = $this->db->query($query, array('S', $organizationID));
		}
		else
		{
			$query = "SELECT f.funcionario_id, f.organizacao_id, a.area_id, a.sigla AS area FROM funcionario f, funcionario_status s, area a WHERE (f.funcionario_status_id = s.funcionario_status_id) AND (f.area_id = a.area_id) AND (f.funcionario_categoria_id = ?) AND (s.exibir = ?) AND (f.organizacao_id = ?)";
			$result = $this->db->query($query, array((int)$categoryID, 'S', $organizationID));
		}

		$employees = $result->GetArray(-1);
		$cachedLDAP = Factory::newInstance('CachedLDAP');
		$cachedLDAP->setOperationMode($cachedLDAP->OPERATION_MODE_NORMAL);

		for ($i = 0; $i < count($employees); ++$i)
		{
			/* remove numeric fields */
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($employees[$i][$j]);

			$employees[$i]['cn'] = '';
			$employees[$i]['telephoneNumber'] = '';

			/* try to find the telephone number */
			$entry = $cachedLDAP->getEntryByID($employees[$i]['funcionario_id']);
			if ($entry)
			{
				$employees[$i]['telephoneNumber'] = is_null($entry['telephonenumber']) ? '' : $entry['telephonenumber'];
				$employees[$i]['cn'] = is_null($entry['cn']) ? '' : $entry['cn'] . (is_null($entry['last_update']) ? ' <font color="red">(inativo)</font>' : '');
			}
		}

		$output = array('employees' => $employees);
		return $output;
	}

	/**
	 * Search Employee by Name
	 * @param int $searchTerm term to search
	 * @param int $organizationID  Id of organization
	 * @return array employee data information
	 * @access public
	 */
	function searchEmployeeByName($searchTerm, $organizationID)
	{
		/* get ldap connection */
		$ldap = &Factory::getInstance('WorkflowObjects')->getLDAP();

		$searchTermExploded = explode(" ", $searchTerm);
		$fullSearch = false;

		if (count($searchTermExploded) > 0){
            $searchTermExploded_count = count($searchTermExploded);
			for ($i=1; $i<$searchTermExploded_count; ++$i) {
				if (strlen($searchTermExploded[$i]) > 2) {
					$fullSearch = true;
				}
			}

			if ($fullSearch){
				$searchTerm = implode("*", $searchTermExploded);
			}
		}

		/* searching employees by name in the ldap server */
		$list = @ldap_search($ldap, Factory::getInstance('WorkflowLDAP')->getLDAPContext(), ('(&(cn=*' . $searchTerm . '*)(phpgwaccounttype=u))'), array('uidNumber', 'cn', 'telephoneNumber'));
		if ($list === false)
			return false;

		/* parsing ldap result */
		$entries = ldap_get_entries($ldap, $list);
		$ldapResult = array();

		for ($i = 0; $i < $entries['count']; ++$i)
			$ldapResult[$entries[$i]['uidnumber'][0]] = array('cn' => $entries[$i]['cn'][0], 'telephoneNumber' => $entries[$i]['telephonenumber'][0]);

		/* no records found. bye. */
		if (count($ldapResult) == 0)
			return array();

		$uids = implode( ',', array_keys( $ldapResult ) );

		/* searching for aditional employee information */
		$query  = "SELECT ";
		$query .= "		f.funcionario_id AS funcionario_id, f.area_id AS area_id, a.sigla AS area ";
		$query .= " FROM ";
		$query .= "			funcionario f ";
		$query .= "		INNER JOIN ";
		$query .= "			area a USING (area_id) ";
		$query .= "		INNER JOIN ";
		$query .= "			funcionario_status s ON ((f.funcionario_status_id = s.funcionario_status_id) AND (s.exibir = 'S')) ";
		$query .= " WHERE ";
		$query .= "		(f.organizacao_id = ?) ";
		$query .= " 	AND ";
		$query .= "		(f.funcionario_id IN ({$uids})) ";

		$result = $this->db->query($query, array($organizationID))->GetArray(-1);
		$employees = array();

		/* filling return array with employee's information */
        $result_count = count($result);
		for ($i = 0; $i < $result_count; ++$i) {
			$employees []= array(
					'area'	 			=> $result[$i]['area'],
					'area_id' 			=> $result[$i]['area_id'],
					'funcionario_id'	=> $result[$i]['funcionario_id'],
					'cn'			 	=> $ldapResult[$result[$i]['funcionario_id']]['cn'],
					'telephoneNumber'	=> empty($ldapResult[$result[$i]['funcionario_id']]['telephoneNumber']) ? '': $ldapResult[$result[$i]['funcionario_id']]['telephoneNumber']
			);
		}

		/* sorting by name (cn) */
        $sort_function = create_function('$a, $b', 'return strcasecmp($a[\'cn\'],$b[\'cn\']);');
        usort($employees, $sort_function );

		return $employees;
	}

	/**
	 * Search Employee by Area
	 * @param int $searchTerm term to search
	 * @param int $organizationID  Id of organization
	 * @return array employee data information
	 * @access public
	 */
	function searchEmployeeByArea($searchTerm, $organizationID)
	{
		/* get ldap connection */
		$ldap = &Factory::getInstance('WorkflowObjects')->getLDAP();

		/* makes no sense search for an area if the string has more than one word */
		if (count(explode(" ", $searchTerm)) > 1)
			return array();

		/* searching for employees in areas that match 'searchTerm' */
		$query  = "SELECT ";
		$query .= "		f.funcionario_id AS funcionario_id, f.area_id AS area_id, a.sigla AS area ";
		$query .= " FROM ";
		$query .= "			funcionario f ";
		$query .= "		INNER JOIN ";
		$query .= "			area a USING (area_id) ";
		$query .= "		INNER JOIN ";
		$query .= "			funcionario_status s ON ((f.funcionario_status_id = s.funcionario_status_id) AND (s.exibir = 'S')) ";
		$query .= " WHERE ";
		$query .= "		(f.organizacao_id = ?) ";
		$query .= " 	AND ";
		$query .= "		(UPPER(a.sigla) LIKE UPPER(?)) ";

		$result = $this->db->query($query, array($organizationID, '%'.$searchTerm.'%'))->GetArray(-1);
	
		/* no records found. bye */
		if (count($result) == 0)
			return array();

		/* creating the ldap query */
		$ldap_query = '(&(|';
        $result_count = count($result);
		for ($i = 0; $i < $result_count; ++$i) {
			$ldap_query .= '(uidNumber=' . $result[$i]['funcionario_id'] . ')';
		}
		$ldap_query .= ')(phpgwAccountType=u))';

		/* executing it */
		$list = @ldap_search($ldap, Factory::getInstance('WorkflowLDAP')->getLDAPContext(), $ldap_query, array('uidNumber', 'cn', 'telephoneNumber'));
		$entries = ldap_get_entries($ldap, $list);

		/* parsing result */
		$ldapResult = array();
		for ($i = 0; $i < $entries['count']; ++$i)
			$ldapResult[$entries[$i]['uidnumber'][0]] = array('cn' => $entries[$i]['cn'][0], 'telephoneNumber' => $entries[$i]['telephonenumber'][0]);

		/* we will need to search into database 'cache' for users deleted in ldap */
		$cachedLDAP = Factory::newInstance( 'CachedLDAP' );
		$cachedLDAP -> setOperationMode( $cachedLDAP -> OPERATION_MODE_DATABASE );

		/* filling return array with employee's information */
		$employees = array();
        $result_count = count($result);
		for ($i = 0; $i < $result_count; ++$i) {

			$employee = array();

			/* user deleted in ldap. Let's try to find him into database 'cache' */
			if (empty($ldapResult[$result[$i]['funcionario_id']]['cn'])) {
				$entry = $cachedLDAP->getEntryByID($result[$i]['funcionario_id']);

				$employee['removed'] = is_null($entry['last_update']);

				if ($entry && !empty($entry['cn']))
					$employee['cn']	= $entry['cn'];
				/* we cant find it anywhere */
				else
					$employee['cn']	= $result[$i]['funcionario_id'];
			}
			else
				$employee['cn']	= $ldapResult[$result[$i]['funcionario_id']]['cn'];

			$employee['area']	 			= $result[$i]['area'];
			$employee['area_id'] 			= $result[$i]['area_id'];
			$employee['funcionario_id'] 	= $result[$i]['funcionario_id'];
			$employee['telephoneNumber']	= empty($ldapResult[$result[$i]['funcionario_id']]['telephoneNumber']) ? '': $ldapResult[$result[$i]['funcionario_id']]['telephoneNumber'];

			$employees []= $employee;
		}

		/* sorting by name (cn) */
        $sort_function = create_function('$a, $b', 'return strcasecmp($a[\'cn\'],$b[\'cn\']);');
        usort($employees, $sort_function );

		return $employees;
	}

	/**
	 * Search Employee by Telephone
	 * @param int $searchTerm term to search
	 * @param int $organizationID  Id of organization
	 * @return array employee data information
	 * @access public
	 */
	function searchEmployeeByTelephone($searchTerm, $organizationID)
	{
		/* we will just excute it if we just get numbers and '-' */
		if (!preg_match('/^[0-9-]+$/', $searchTerm))
			return array();

		/* get ldap connection */
		$ldap = &Factory::getInstance('WorkflowObjects')->getLDAP();

		/* searching employees by telephoneNumber in the ldap server */
		$list = @ldap_search($ldap, Factory::getInstance('WorkflowLDAP')->getLDAPContext(), ('(&(telephoneNumber=*' . $searchTerm . '*)(phpgwaccounttype=u))'), array('uidNumber', 'cn', 'telephoneNumber'));

		if (!$list) return false;

		/* parsing ldap result */
		$entries = ldap_get_entries($ldap, $list);
		$ldapResult = array();

		for ($i = 0; $i < $entries['count']; ++$i)
			$ldapResult[$entries[$i]['uidnumber'][0]] = array('cn' => $entries[$i]['cn'][0], 'telephoneNumber' => $entries[$i]['telephonenumber'][0]);

		/* no records found. bye. */
		if (count($ldapResult) == 0)
			return array();

		$uids = implode( ',', array_keys( $ldapResult ) );

		/* searching for aditional employee information */
		$query  = "SELECT ";
		$query .= "		f.funcionario_id AS funcionario_id, f.area_id AS area_id, a.sigla AS area ";
		$query .= " FROM ";
		$query .= "			funcionario f ";
		$query .= "		INNER JOIN ";
		$query .= "			area a USING (area_id) ";
		$query .= "		INNER JOIN ";
		$query .= "			funcionario_status s ON ((f.funcionario_status_id = s.funcionario_status_id) AND (s.exibir = 'S')) ";
		$query .= " WHERE ";
		$query .= "		(f.organizacao_id = ?) ";
		$query .= " 	AND ";
		$query .= "		(f.funcionario_id IN ({$uids})) ";

		$result = $this->db->query($query, array($organizationID))->GetArray(-1);
		$employees = array();

		/* filling return array with employee's information */
        $result_count = count($result);
		for ($i = 0; $i < $result_count; ++$i) {
			$employees []= array(
					'area'	 			=> $result[$i]['area'],
					'area_id'	 		=> $result[$i]['area_id'],
					'funcionario_id'	=> $result[$i]['funcionario_id'],
					'cn'			 	=> $ldapResult[$result[$i]['funcionario_id']]['cn'],
					'telephoneNumber'	=> empty($ldapResult[$result[$i]['funcionario_id']]['telephoneNumber']) ? '': $ldapResult[$result[$i]['funcionario_id']]['telephoneNumber']
			);
		}

		/* sorting by name (cn) */
        $sort_function = create_function('$a, $b', 'return strcasecmp($a[\'cn\'],$b[\'cn\']);');
        usort($employees, $sort_function );

		return $employees;
	}


	/**
	 * Busca informações sobre um funcionário.
	 * @param array $params Uma array contendo o ID do funcionário cujas informações serão extraídas e de sua organização (Ajax).
	 * @param int $employeeID O ID do funcionário.
	 * @param int $organizationID O ID da organização.
	 * @return array Informações sobre o funcionário.
	 * @access public
	 */
	function getEmployeeInfo($employeeID, $organizationID)
	{
		$SOOrgchart = &Factory::getInstance('so_orgchart');
		$SOOrgchart->setExternalCalls(true);
		$output = $SOOrgchart->getEmployeeInfo($employeeID, $organizationID);
		$SOOrgchart->setExternalCalls(false);

		return $output;
	}

	/**
	 * Busca informações sobre uma área.
	 * @param array $params Uma array contendo o ID da área cujas informações serão extraídas e de sua organização (Ajax).
	 * @param int $areaID O ID da área.
	 * @param int $organizationID O ID da organização.
	 * @return array Informações sobre a área.
	 * @access public
	 */
	function getAreaInfo($areaID, $organizationID)
	{
		$SOOrgchart = &Factory::getInstance('so_orgchart');
		$SOOrgchart->setExternalCalls(true);
		$output = $SOOrgchart->getAreaInfo($areaID, $organizationID);
		$SOOrgchart->setExternalCalls(false);

		return $output;
	}

	/**
	 * Get useful phones list
	 * @param int $organizationID The organization ID
	 * @return array Useful phones list
	 * @access public
	 */
	function getUsefulPhones( $organizationID )
	{
		$result = $this -> db -> query( "SELECT descricao, numero FROM telefone WHERE (organizacao_id = ?) ORDER BY descricao", array( $organizationID ) );

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Get areas with substitute boss
	 * @param int $organizationID The organization ID
	 * @return array areas with substitute boss
	 * @access public
	 */
	function getAreaWithSubtituteBoss( $organizationID )
	{
		$result = $this -> db -> query( "SELECT a.sigla as area, a.titular_funcionario_id as titular, s.funcionario_id as substituto, s.data_inicio, s.data_fim FROM area a INNER JOIN substituicao s ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) WHERE (organizacao_id = ?) ORDER BY area", array( $organizationID ) );

		$cachedLDAP = Factory::newInstance( 'CachedLDAP' );
		$cachedLDAP -> setOperationMode( $cachedLDAP -> OPERATION_MODE_LDAP_DATABASE );

		$output = $result->GetArray(-1);

		for ( $i = 0; $i < count($output); ++$i )
		{
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

			$entry = $cachedLDAP -> getEntryByID( $output[ $i ][ 'titular' ] );
			if ( $entry && ( ! is_null( $entry[ 'cn' ] ) ) )
				$output[ $i ][ 'titular' ] = $entry[ 'cn' ];

			$entry = $cachedLDAP -> getEntryByID( $output[ $i ][ 'substituto' ] );
			if ( $entry && ( ! is_null( $entry[ 'cn' ] ) ) )
				$output[ $i ][ 'substituto' ] = $entry[ 'cn' ];

			$output[$i]['data_inicio'] = implode('/', array_reverse(explode('-', $output[$i]['data_inicio'])));
			$output[$i]['data_fim'] = implode('/', array_reverse(explode('-', $output[$i]['data_fim'])));
		}

		return $output;
	}

	/**
	 * Get manning list
	 * @param int $organizationID The organization ID
	 * @return array The manning list
	 * @access public
	 */
	function getManning( $organizationID )
	{
		$result = $this -> db -> query( 'SELECT localidade_id, descricao FROM localidade WHERE (organizacao_id = ?) ORDER BY descricao', array( $organizationID ) );

		$output = $result->GetArray( -1 );

		for ( $i = 0; $i < count($output); ++$i )
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Get employees from a specific location
	 * @param int $categoryID The category ID
	 * @param int $organizationID The organization ID
	 * @return array The list o employees of that location
	 * @access public
	 */
	function getManningEmployees( $locationID, $organizationID )
	{
		$organizationID = ( int ) $organizationID;
		$locationID = ( int ) $locationID;

		// load the employees from the location
		$query = "SELECT f.funcionario_id, f.organizacao_id, f.area_id, a.sigla AS area,"
			. " COALESCE(f.funcionario_categoria_id, 0) AS funcionario_categoria_id"
			. " FROM funcionario f, funcionario_status s, area a"
			. " WHERE (f.area_id = a.area_id)"
			. " AND (f.funcionario_status_id = s.funcionario_status_id)"
			. " AND (f.organizacao_id = ?)"
			. " AND (f.localidade_id = ?)"
			. " AND (s.exibir = ?)";

		$result = $this -> db -> query( $query, array( $organizationID, $locationID, 'S' ) );

		$employees = $result -> GetArray( -1 );
		$cachedLDAP = Factory::newInstance( 'CachedLDAP' );
		$cachedLDAP -> setOperationMode( $cachedLDAP -> OPERATION_MODE_NORMAL );

		$categoriesCount = array( );

		for ( $i = 0; $i < count( $employees ); ++$i )
		{
			// remove numeric fields
			for ( $j = 0; $j < $result -> _numOfFields; ++$j )
				unset( $employees[ $i ][ $j ] );

			$employees[ $i ][ 'cn' ] = '';
			$employees[ $i ][ 'telephoneNumber' ] = '';

			// try to find the telephone number
			$entry = $cachedLDAP -> getEntryByID( $employees[ $i ][ 'funcionario_id' ] );
			if ( $entry )
			{
				$employees[ $i ][ 'telephoneNumber' ] = is_null( $entry[ 'telephonenumber' ] ) ? '' : $entry[ 'telephonenumber' ];
				$employees[ $i ][ 'cn' ] = is_null( $entry[ 'cn' ] ) ? '' : $entry[ 'cn' ];
				$employees[ $i ][ 'removed' ] = is_null( $entry[ 'last_update' ] );
			}

			// count the number of employees in each category
			$categoryID = $employees[ $i ][ 'funcionario_categoria_id' ];
			if ( isset( $categoriesCount[ $categoryID ] ) )
				$categoriesCount[ $categoryID ]++;
			else
				$categoriesCount[ $categoryID ] = 1;
		}

		$usedCategories = array_keys( $categoriesCount );
		$availableCategories = $this -> getCategoriesList( $organizationID );
		$output = array( );
		$output[ 'employees' ] = $employees;
		$output[ 'categories' ] = array( );
		foreach ( $availableCategories as $category )
		{
			if ( ! in_array( $category[ 'funcionario_categoria_id' ], $usedCategories ) )
				continue;

			$category[ 'contagem' ] = $categoriesCount[ $category[ 'funcionario_categoria_id' ] ];
			$output[ 'categories' ][ ] = $category;
		}

		usort( $output[ 'employees' ], create_function( '$a,$b', 'return strcasecmp($a[\'cn\'],$b[\'cn\']);' ) );

		return $output;
	}

	/**
	 * Return the list of employees in alphabetical order
	 * @param int $organizationID The organization ID
	 * @return array The list o employees
	 * @access public
	 */
	function getAlphabeticalEmployees( $organizationID )
	{
		$organizationID = ( int ) $organizationID;

		// load the employees from the location
		$query = "SELECT f.funcionario_id, f.organizacao_id, f.area_id, a.sigla AS area,"
			. " COALESCE(f.funcionario_categoria_id, 0) AS funcionario_categoria_id"
			. " FROM funcionario f, funcionario_status s, area a"
			. " WHERE (f.area_id = a.area_id)"
			. " AND (f.funcionario_status_id = s.funcionario_status_id)"
			. " AND (f.organizacao_id = ?)"
			. " AND (s.exibir = ?)";

		$result = $this -> db -> query( $query, array( $organizationID, 'S' ) );

		$employees = $result -> GetArray( -1 );

		$cachedLDAP = Factory::newInstance( 'CachedLDAP' );
		$cachedLDAP -> setOperationMode( $cachedLDAP -> OPERATION_MODE_NORMAL );

		$categoriesCount = array( );

		for ( $i = 0; $i < count( $employees ); ++$i )
		{
			// remove numeric fields
			for ( $j = 0; $j < $result -> _numOfFields; ++$j )
				unset( $employees[ $i ][ $j ] );

			$employees[ $i ][ 'cn' ] = '';
			$employees[ $i ][ 'telephoneNumber' ] = '';

			// try to find the telephone number
			$entry = $cachedLDAP -> getEntryByID( $employees[ $i ][ 'funcionario_id' ] );
			if ( $entry )
			{
				$employees[ $i ][ 'telephoneNumber' ] = is_null( $entry[ 'telephonenumber' ] ) ? '' : $entry[ 'telephonenumber' ];
				$employees[ $i ][ 'cn' ] = is_null( $entry[ 'cn' ] ) ? '' : $entry[ 'cn' ];
				$employees[ $i ][ 'removed' ] = is_null( $entry[ 'last_update' ] );
			}

		}

		usort( $employees, create_function( '$a,$b', 'return strcasecmp($a[\'cn\'],$b[\'cn\']);' ) );

		$paging = Factory::newInstance('Paging', 50, $_POST);
		$employees = $paging->restrictItems( $employees );

		// count the number of employees in each category
        $employees_count = count( $employees );
		for ( $i = 0; $i < $employees_count; ++$i )
		{
			$categoryID = $employees[ $i ][ 'funcionario_categoria_id' ];
			if ( isset( $categoriesCount[ $categoryID ] ) )
				$categoriesCount[ $categoryID ]++;
			else
				$categoriesCount[ $categoryID ] = 1;
		}

		$usedCategories = array_keys( $categoriesCount );
		$availableCategories = $this -> getCategoriesList( $organizationID );
		$output = array( );

		$output['employees'] = $employees;
		$output['categories'] = array( );
		$output['paging_links'] = $paging -> commonLinks();

		foreach ( $availableCategories as $category )
		{
			if ( ! in_array( $category[ 'funcionario_categoria_id' ], $usedCategories ) )
				continue;

			$category[ 'contagem' ] = $categoriesCount[ $category[ 'funcionario_categoria_id' ] ];
			$output[ 'categories' ][ ] = $category;
		}

		return $output;
	}
}
?>
