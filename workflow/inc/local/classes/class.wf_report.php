<?php

require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'report'.SEP.'includes'.SEP.'classes'.SEP.'Listagem.class.php');

class wf_report
{

	var $db;
	
	var $listagem;
	/**
	 * Construtor da classe wf_log
	 * @param array/string $logTypes Array ou String com o(s) tipo(s) de log(s) que dever�(�o) ser criado(s)
	 * @return object Objeto do tipo wf_log
	 * @access public
	 */
	public function wf_report()
	{
		$this->db = &Factory::getInstance('WorkflowObjects')->getDBWorkflow()->Link_ID->_connectionID;
	    
		$this->listagem = new Listagem('wf_report','wf_report',$this->db);
		$this->listagem->setUrlBasePath($GLOBALS['phpgw_info']['server']['webserver_url'] . "/workflow/inc/report");
	}
	
	function loadReport($idlisting) {
		$this->listagem->carregarIDListagem($idlisting);
	}
	function setParam($param_name,$value) {
		$this->listagem->setParametro($param_name,$value);
	}
	function getHTML() {
		ob_start();
		$this->listagem->desenhar(); 
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	function getClass() {
		return $this->listagem;
	}
	
}
