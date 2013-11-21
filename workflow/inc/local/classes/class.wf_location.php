<?php
/**
 * Class for getting city/state information
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 */

class wf_location
{  /**
	* @var object $db banco de dados
	* @access private
	*/
	var $db;
   /**
	* @var array $cityInfo Armazena informações sobre a cidade
	* @access public
	*/
	var $cityInfo;
   /**
	* @var array $stateInfo Armazena as informações sobre o Estado
	* @access public
	*/
	var $stateInfo;
   /**
	* @var array $citiesFromState Armazena as cidades de um estado
	* @access public
	*/
	var $citiesFromState;

	/**
	 * Inicializa os arrays da classe
	 * return void
	 * @access private
	 */
	function initialize()
	{
		$this->db = &Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID;
		$this->db->setFetchMode(ADODB_FETCH_ASSOC);
		$this->cityInfo = array();
		$this->stateInfo = array();
		$this->citiesFromState = array();
	}

	/**
	 * Construtor da classe
	 * return object
	 * @access public
	 */
	function wf_location()
	{
		$this->initialize();
	}

	/**
	 * Busca as informações da cidade pelo numero id passado
	 * @param int $city_id Numero ID da cidade
	 * @return mixed (array ou boolean)
	 * @access public
	 */
	function getCityById($city_id)
	{
		if (!is_numeric($city_id))
			return false;

		$city_id = (int) $city_id;
		if (isset($this->cityInfo[$city_id]))
			return $this->cityInfo[$city_id];

		$sql =
		"SELECT
			c.id_city AS id_city,
			c.city_name AS city_name,
			c.is_district,
			s.id_state AS id_state,
			s.state_name AS state_name,
			s.state_symbol AS state_symbol
		FROM
			phpgw_cc_state s,
			phpgw_cc_city c
		WHERE
			c.id_state = s.id_state AND
			s.id_country = 'BR' AND
			c.id_city = ?";

		$result = $this->db->query($sql, array($city_id));
		$output = $result->fetchRow();

		$this->cityInfo[$city_id] = $output;
		return $output;
	}

   /**
	* Busca as informações do estado pelo id passado
	* @param int $state_id Numero ID do estado
	* @return mixed (array ou boolean)
	* @access public
	*/
	function getStateById($state_id)
	{
		if (!is_numeric($state_id))
			return false;

		$state_id = (int) $state_id;
		if (isset($this->stateInfo[$state_id]))
			return $this->stateInfo[$state_id];

		$sql =
		"SELECT
			id_state,
			state_name,
			state_symbol
		FROM
			phpgw_cc_state
		WHERE
			id_country = 'BR' AND
			id_state = ?";

		$result = $this->db->query($sql, array($state_id));
		$output = $result->fetchRow();

		$this->stateInfo[$city_id] = $output;
		return $output;
	}
    /**
	 * Busca as cidades de um estado
	 * @param int $state_id Numero ID do estado
	 * @param bool $include_districts True, busca cidades e distritos. False, busca apenas cidades.
	 * @return mixed (array ou boolean)
	 * @access public
	 */
	function getCitiesFromState($state_id, $include_districts = true)
	{
		if (!is_numeric($state_id) || !is_bool($include_districts))
			return false;

		$state_id = (int) $state_id;
		if (isset($this->citiesFromState[$state_id]))
			return $this->citiesFromState[$state_id];

		$where = "";
		if(!$include_districts){
			$where = " AND c.is_district = 'F' ";
		}

		$sql =
		"SELECT
			c.id_city AS id_city,
			c.city_name AS city_name,
			c.is_district
		FROM
			phpgw_cc_state s,
			phpgw_cc_city c
		WHERE
			c.id_state = s.id_state AND
			s.id_country = 'BR' AND
			c.id_state = ? " . $where . "
		ORDER BY
			city_name";

		$result = $this->db->query($sql, array($state_id));
		$output = array();
		if (is_object($result)) {
		while ($row = $result->fetchRow())
			$output[] = $row;

		$this->citiesFromState[$state_id] = $output;
		}
		return $output;
	}
    /**
	 * Busca as cidades por parte do nome, sem considerar maiúsculas e/ou minúsculas e nem acentuação (retorna 10 resultados)
	 * @param string Parte do nome da cidade
	 * @param int $state_id Numero ID do estado
	 * @param bool $include_districts True, busca cidades e distritos. False, busca apenas cidades.
	 * @return mixed (array ou boolean)
	 * @access public
	 */
	function getCitiesByKey($key, $state_id = 0, $include_districts = true, $hasAccent = true)
	{
		if (!is_string($key) || !is_numeric($state_id) || !is_bool($include_districts))
			return false;

		$where = "";
		if($state_id > 0){
			$where = " AND c.id_state = " . $state_id;
		}

		if(!$include_districts){
			$where = " AND c.is_district = 'F' ";
		}

		$city_name = 'c.city_name AS city_name,';

		if(!$hasAccent)
		{
			$city_name = 'TO_ASCII(c.city_name) AS city_name,';
		}

		$sql =
		"SELECT
			c.id_city AS id_city,
			$city_name
			s.id_state AS id_state,
			s.state_name AS state_name,
			s.state_symbol AS state_symbol
		FROM
			phpgw_cc_state s,
			phpgw_cc_city c
		WHERE
			c.id_state = s.id_state AND
			s.id_country = 'BR' AND
			TO_ASCII(c.city_name) ILIKE TO_ASCII('" . pg_escape_string($key) . "%')
			" . $where . "
		ORDER BY
			city_name
		LIMIT 10";

		$result = $this->db->query($sql);
		$output = array();
		while ($row = $result->fetchRow())
			$output[] = $row;

		return $output;
	}
	/**
	 * Busca os estados brasileiros
	 * @return mixed (array ou boolean)
	 * @access public
	 */
	function getStates()
	{
		$sql =
		"SELECT
			id_state,
			state_name
		FROM
			phpgw_cc_state
		WHERE
			id_country = 'BR'
		ORDER BY
			state_name";

		$result = $this->db->query($sql);
		$output = array();
		while ($row = $result->fetchRow())
			$output[] = $row;

		return $output;
	}
}
?>
