<?php
/**************************************************************************\
* eGroupWare                                                 			   *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
 */
class ajax 
{
	/**
     * Constructor
     * 
     * @return object
     * @access public
     */
	function ajax() {
	}
	
    /**
     * Get Last Ajax response
     * @return array session last ajax response
     * @access public
     */
	function getLastAjaxResponse()
    {
		$result = $_SESSION['response'];
		$_SESSION['response'] = null;
		
		return $result;
	}
}
?>
