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

require_once dirname(__FILE__) . SEP . 'class.ui_phpeditor.inc.php';
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Rodrigo Daniel C Lira - rodrigo.lira@gmail.com
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 */
class ui_templateeditor extends ui_phpeditor
{
	protected function loadVariables()
	{
		$this->HTMLFile = 'tpleditor.html';
		$this->type = 'tpl';
	}
}
?>
