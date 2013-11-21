<?php
/**
* Provê métodos que acessam informações relacionadas aos workitems.
* @author Anderson Tadayuki Saikawa - asaikawa@celepar.pr.gov.br
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow
* @subpackage local
*/
class wf_workitem
{
	/**
	* @var object $db objeto do banco de dados
	* @access private
	*/
	private $db;

	/**
	* @var int $processID o ID do processo onde a classe está sendo utilizada
	* @access private
	*/
	private $processID;

	/**
	* Construtor do wf_workitem.
	* @return object
	* @access public
	*/
	public function wf_workitem()
	{
		$this->db = &Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
		$this->processID = (int) $GLOBALS['workflow']['wf_runtime']->activity->getProcessId();
	}

	/**
	* Busca workitems de uma instância pelo seu nome (identificador).
	* @param int $instanceName O nome da instância.
	* @param mixed $activities Uma lista de IDs de atividades das quais se quer os workitems (também pode ser um valor inteiro).
	* @return array Array onde cada elemento corresponde aos workitens de cada instâncias que satisfaz o critério de seleção.
	* @access public
	*/
	public function getWorkitemsByInstanceName($instanceName, $activities = null)
	{
		$output = array();

		if (is_numeric($activities))
			$activities = array((int) $activities);

		/* build the SQL query */
		$query  = 'SELECT w.wf_instance_id, w.wf_item_id, w.wf_order_id, w.wf_activity_id, w.wf_started, w.wf_ended, w.wf_user ';
		$query .= 'FROM egw_wf_workitems w ';
		$query .= 'INNER JOIN egw_wf_instances i ON w.wf_instance_id = i.wf_instance_id ';
		$query .= 'WHERE (i.wf_p_id = ?) AND (UPPER(i.wf_name) = UPPER(?)) ';

		$values = array($this->processID, $instanceName);

		/* if there are activities, add a condition */
		if(is_array($activities) && (count($activities) > 0))
		{
			$query .= 'AND (w.wf_activity_id = ANY (?))';
			$values[] = '{' . implode(', ', $activities) . '}';
		}

		$result = $this->db->query($query, $values);
		if ($result !== false)
		{
			while (($row = $result->fetchRow()))
				$output[$row['wf_instance_id']][] = $row;
			$output = array_values($output);
		}

		return $output;
	}
}
?>
