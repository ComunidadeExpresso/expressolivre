<?php

require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'class.Logger.inc.php');

/**
 * Geração de logs.
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Guilherme Striquer Bisotto - gbisotto@celepar.pr.gov.br
 * @package Workflow
 * @subpackage local
 */
class wf_log extends Logger
{

	/**
	 * Construtor da classe wf_log
	 * @param array/string $logTypes Array ou String com o(s) tipo(s) de log(s) que deverá(ão) ser criado(s)
	 * @return object Objeto do tipo wf_log
	 * @access public
	 */
	public function wf_log($logTypes = 'file')
	{
		$processName = $GLOBALS['workflow']['wf_normalized_name'];
		if(empty($processName))
			throw new Exception(lang('Cannot find out the process name'));

		$processId = $GLOBALS['workflow']['wf_process_id'];
		if(empty($processId))
			throw new Exception('Cannot fint out the process id ');

		parent::Logger($logTypes, $processId, $processName);
	}
}
?>
