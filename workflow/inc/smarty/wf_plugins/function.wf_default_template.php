<?php
/**
* This plugin does not perform any work. It just exists to "say" to the system that an action
* should be executed. When this plugin is called,a header and footer are added into the template.
* The action is performed by: prefilter.wf_default_template.php
* In a template prefilter, the templates are ran through before they are compiled.
* @package Smarty
* @subpackage wf_plugins
* @version:		1.0
* @author:		Everton Flávio Rufino Seára - rufino@celepar.pr.gov.br
*				Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
* @param array null
* @param object &$smarty null
* @return void
* @access public
*/
function smarty_function_wf_default_template($params, &$smarty)
{
	return;
}
?>
