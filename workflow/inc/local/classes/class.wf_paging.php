<?php
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'class.Paging.inc.php');
/**
* Class for generating paging links
* @author Sidnei Augusto Drovetto Junior
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow 
* @subpackage local  
*/
class wf_paging extends Paging
{
	
	/**
	 * @var boolean $flagUseDatabase Indica se a base de dados ser� utilizada
	 * @access public
	 */
	var $flagUseDatabase;
	/**
	 * @var boolean $flagEnableSorting Habilita o sort
	 * @access public
	 */
	var $flagEnableSorting;
	/**
	 * @var array $storage Armazena os dados da consulta
	 * @access public
	 */
	var $storage;
	/**
	 * @var array $titles Armazena o titulo dos links
	 * @access public
	 */
	var $titles;
	/**
	 * @var array $generatedTitles Armazena os titulos dos links gerados
	 * @access public
	 */
	var $generatedTitles ;
	/**
	 * Inicializa os atributos da classe com valores padr�es
	 * @return void
	 * @access private
	 */
	function initialize()
	{
		parent::initialize();

		$this->storage = array();
		$this->titles = null;
		$this->generatedTitles = null;
	}
	/**
	 * Utilizado para configurar a pagina��o
	 * @param int $pItemsPerPage Numero de Itens por p�gina
	 * @param array $request requisi��o
	 * @return void
	 * @access public
	 */
	function configure($pItemsPerPage, $request = null)
	{
		$this->itemsPerPage = $pItemsPerPage;
		$this->originalRequest = $request;

		$this->initialize();
	}
	/**
	 * Construtor da classe wf_paging
	 * @return object
	 * @access public
	 */
	function wf_paging()
	{
	}
	/**
	 * Utilizado para habilitar o flag que indica para fazer o sort
	 * @param boolean $value (true ou false)
	 * @access public
	 */
	function enableSorting($value)
	{
		$this->flagEnableSorting = $value;
	}
	/**
	 * Utilizado para habilitar o flag que indica o uso de uma database
	 * @param boolean $value (true ou false)
	 * @access public
	 */
	function useDatabase($value)
	{
		$this->flagUseDatabase = $value;
	}
	/**
	 * Realiza a pagina��o do resultado de uma consulta.
	 * @param object $db  banco de dados da pesquisa
	 * @param string $sql consulta em sql
	 * @param array $values Valores que ser�o associados � query atrav�s de bind (opcional)
	 * @return mixed (array ou boolean)
	 * @access public
	 */
	function restrictDBItems($db, $sql, $values = false)
	{
		if (!$this->flagUseDatabase)
			return false;

		/* adiciona ordena��o (se requisitado) */
		if ($this->flagEnableSorting)
			if (isset($this->storage['s_co']))
				$sql .= " ORDER BY " . $this->storage['s_co'] . " " . (($this->storage['s_so'] == 0) ? "DESC" : "ASC");

		/* utilliza diretamente o objeto ADOdb */
		$adoDB = &$db->Link_ID;

		/* executa a consulta (com possibilidade de passar vari�veis por bind */
		$resultSet = $adoDB->query($sql, $values);

		/* faz a contagem de registros e extrai a quantidade desejada */
		$this->itemsCount = $resultSet->RecordCount();
		$this->items = $adoDB->_rs2rs($resultSet, $this->itemsPerPage, $this->nextItem, true)->GetArray();

		return parent::restrictItems($this->items, $this->itemsCount);
	}

	/**
	 * Utilizado para fazer o parse numa requisi��o
	 * @param array $request requisi��o
	 * @return void
	 * @access public
	 */
	function parseRequest($request)
	{
		parent::parseRequest($request);
		$this->newRequest = $this->_cleanRequest(array("p_page", "s_co", "s_so"), $request);
	}

	/**
	 * Utilizado para determinar os parametros (links) para o sort  
	 * @param array $ptitles
	 * @return void
	 * @access public
	 */
	function setSortingTitles($pTitles)
	{
		$this->titles = $pTitles;
		$this->generateSortingTitles();
	}
    /**
	 * Utilizado para buscar os links poss�ves para o sort  
	 * @return void
	 * @access public
	 */
	function getSortingTitles()
	{
		return $this->generatedTitles;
	}
	
	/**
	 * Utilizado para gerar os parametros (links) poss�ves para o sort  
	 * @return boolean
	 * @access public
	 */ 
	function generateSortingTitles()
	{
		if (is_null($this->titles))
			return false;

		if (!empty($this->newRequest))
			$charSeparator = "&amp;";
		else
			$charSeparator = "";
		
		$requestSco = (isset($this->originalRequest['s_co'])) ? $this->originalRequest['s_co'] : 0;
		$requestSco = (is_numeric($requestSco)) ? (int) $requestSco : 0;
		$requestSso = (isset($this->originalRequest['s_so'])) ? $this->originalRequest['s_so'] : 0;
		$requestSso = (is_numeric($requestSso)) ? (int) $requestSso : 0;
		if ($requestSso != 1)
			$requestSso = 0;

		$allowedIds = array();
		$this->generatedTitles = array();
		foreach ($this->titles as $title)
		{
			$sco = $title['id'];
			$allowedIds[] = $sco;
			$sso = 0;
			$arrow = "";
			if ($sco == $requestSco)
			{
				if ($requestSso == 0)
				{
					$sso = 1;
					$arrow = "&nbsp;&nbsp;&uarr;";
				}
				else
				{
					$sso = 0;
					$arrow = "&nbsp;&nbsp;&darr;";
				}
			}
			$title['link'] = $this->baseLink . "?" . $this->newRequest . $charSeparator . "s_co=$sco&amp;s_so=$sso";
			$title['original_name'] = $title['name'];
			$title['name'] .= $arrow;
			$this->generatedTitles[] = $title;
		}
		
		$allowedIds = array_values(array_unique($allowedIds));
		$this->storage['s_co'] = (in_array($requestSco, $allowedIds)) ? $requestSco : $allowedIds[0];
		$this->storage['s_so'] = $requestSso;
	}

/**
 * Retorna o resultado da pagina��o
 * @return array
 * @access public 
 *  */
	function paginationResult()
	{
		$output = parent::paginationResult();
		if (isset($this->originalRequest['s_co']) && isset($this->originalRequest['s_so'])){
            $output_count = count($output);
			for ($i = 0; $i < $output_count; ++$i)
				$output[$i]['link'] .= "&amp;s_co=" . $this->storage['s_co'] . "&amp;s_so=" . $this->storage['s_so'];
        }
		return $output;
	}
}
?>
