<?php
/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once 'common.inc.php';
require_once 'class.so_adminaccess.inc.php';

/**
 * Implementa métodos para checar direitos de acesso ao workflow
 *
 * @package Workflow
 * @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
*/
class workflow_acl extends so_adminaccess
{
	/**
	* Construtor da classe workflow_acl
	* @return object
	* @access public
	*/
	function workflow_acl()
	{
		parent::so_adminaccess(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
	}

}

?>
