<?php
/*
 * Created on 26/03/2007
 */

 /**
  * Result set class for PHP NatAPI
  * @author Everton Flávio Rufino Seára - rufino@celepar.pr.gov.br
  * @version 1.0
  * @package Workflow
  * @subpackage natural
  * @license http://www.gnu.org/copyleft/gpl.html GPL
  */
 class NaturalResultSet
 {
  /**
   * @var int $currRows Linha atual
   * @access private
   */
	private $currRows;

  /**
   * @var array $result Array com o resultado
   * @access private
   */
 	private $result;

 	/** Inicializa o result set
 	 * @return void
 	 * @param array $_result Array com o resultado
 	 * @access public
 	 */
 	function __construct($_result)
 	{
 		$this->result = $_result;
 		$this->currRows = -1;
 	}
 	/**
 	 * Returns the next row if it exists, else, returns false
 	 * @return array next row of data, if it exists
 	 * @access public
 	 */
	public function getNextRow()
	{
		if (count($this->result) > ++$this->currRows){
	 		return $this->result[$this->currRows];
	 	} else {
	 		return false;
	 	}
	}

	/**
	 * Returns the data from a specific field
	 * @param string $name Field name
	 * @return string data
	 * @access public
	 */
	public function getFieldByName($name)
	{
		if (count($this->result) > $this->currRows)
	 		return $this->result[$this->currRows][$name];
	}

	/**
	 * Reset the result set
	 * @return void
	 * @access public
	 */
	public function resetRow()
	{
	 	$this->currRows = -1;
	}

	/**
	 * Record count
	 * @return int Numero de registros
	 * @access public
	 */
	public function recordCount()
	{
	 	return count($this->result);
	}


 }
?>
