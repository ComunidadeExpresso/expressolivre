<?php
/**
* This plugin get the current template and adds a header and footer located in "$localPath".
* @package Smarty
* @subpackage wf_plugins
* @version:		1.0	
* @author		Everton Flávio Rufino Seára - rufino@celepar.pr.gov.br
* @author		Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
* @param string $tpl Template de entrada
* @param object &$smarty Instância do objeto smarty em uso 
* @return string tpl Template with header and footer
* @access public
*/
function smarty_prefilter_wf_default_template($tpl, &$smarty) {


	// Get the default smarty delimiter comment
	$dl = $smarty->left_delimiter;
	$dr = $smarty->right_delimiter;

	// This first regex just remove the template's comments
	$pattern = "/{$dl}\*[\s\S]*?\*{$dr}/";
	// This second one search by the smarty plugin {wf_default_template}
	$pattern2 = "/{$dl}\s*wf_default_template\s*{$dr}/";

	// Remove the comments
	$subject = preg_replace($pattern, "", $tpl);

	// Search by the plugin and adds the header and footer if it was found
	if (preg_match($pattern2, $subject) > 0)
	{
		/* get the header and footer location */
		$templateServer = &Factory::getInstance('TemplateServer');
		$header = $templateServer->getSystemFile('processes/header.tpl');
		$footer = $templateServer->getSystemFile('processes/footer.tpl');
		$tpl = "{include file='{$header}'}" . $tpl . "{include file='{$footer}'}";
	}

	return $tpl;

}
?>
