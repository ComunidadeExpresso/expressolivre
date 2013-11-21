<?php
require_once 'common.inc.php';

require_once GALAXIA_LIBRARY . '/src/ProcessManager/JobManager.php';
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class WorkflowJobManager extends JobManager
{
	/**
	 * Construtor da classe WorkflowJobManager
	 * @access public
	 * @return object
	 */
	public function WorkflowJobManager()
	{
		parent::JobManager(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
	}
}
?>
