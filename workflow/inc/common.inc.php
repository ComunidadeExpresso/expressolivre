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

/**
 * Server base path. This constant is somewhat simililar to
 * 'PHPGW_SERVER_ROOT'. Nevertheless, there are cases that we
 * access Workflow directly and it must be defined somewhere.
 * Recently discovered, when we are runnnig jobs HTTP_SERVER_VARS
 * exported by apache are not available.
 * Therefore, we are using this workaround here, setting
 * EGW_SERVER_ROOT based on the location of this file. This constant
 * should be updated properly every time we move this file.
 * @name EGW_SERVER_ROOT
 */
define('EGW_SERVER_ROOT', dirname(dirname(dirname(__FILE__))));
//define('EGW_SERVER_ROOT', $GLOBALS['HTTP_SERVER_VARS']['DOCUMENT_ROOT']);

/**
 * Server include base path. We must define our own constants
 * because there are several cases in which workflow is called
 * directly, thus PHPGW constants are not defined.
 * @name EGW_INC_ROOT
 */
define('EGW_INC_ROOT', EGW_SERVER_ROOT . '/phpgwapi/inc/');

/**
 * Workflow base path.
 * @name WF_SERVER_ROOT
 */
define('WF_SERVER_ROOT', EGW_SERVER_ROOT.'/workflow/');

/**
 * Workflow include path.
 * @name WF_INC_ROOT
 */
define('WF_INC_ROOT', WF_SERVER_ROOT.'/inc/');

/**
 * Workflow lib base dir.
 * @name WF_LIB_ROOT
 */
define('WF_LIB_ROOT', WF_SERVER_ROOT.'/lib/');


if (file_exists(EGW_SERVER_ROOT . '/header.session.inc.php')) {
	require_once EGW_SERVER_ROOT . '/header.session.inc.php';
}


/* assure that the correct encondig will be used (e.g string functions) */
setlocale(LC_CTYPE, 'pt_BR', 'pt_BR.iso-8859-1', 'pt_BR.utf-8');


/* define o umask para a criação de arquivos por parte do Workflow */
umask(007);


/* including common classes */
require_once WF_LIB_ROOT . 'security/Security.php';
require_once WF_LIB_ROOT . 'factory/Factory.php';
require_once WF_LIB_ROOT . 'factory/BaseFactory.php';
require_once WF_LIB_ROOT . 'factory/WorkflowFactory.php';
require_once WF_LIB_ROOT . 'factory/ProcessFactory.php';
require_once WF_INC_ROOT . 'common_functions.inc.php';
?>
