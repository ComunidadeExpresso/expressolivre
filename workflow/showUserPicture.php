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
require_once 'inc/common.inc.php';
if (empty($_SESSION))
	exit(0);
Factory::getInstance('UserPictureProvider')->serve();
?>
