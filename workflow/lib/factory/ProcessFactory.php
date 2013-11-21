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
 * Specialization of the BaseFactory class.
 * This class is used to instantiate classes for
 * processes. You cannot access this class directly,
 * but requests to the Factory frontend class in
 * 'secured mode' will be forwarded to this class.
 * It should be much more restrictive than its
 * older brother (or sister), WorkflowFactory...
 *
 * @package Factory
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Pedro Eugênio Rocha - pedro.eugenio.rocha@gmail.com
 */
final class ProcessFactory extends BaseFactory {


	/**
	 * @var boolean $_instantiated Attribute that stores whether this
	 *		class was instantiated or not. This is used to limit the
	 * 		instantiation of this class.
	 * @access private
	 * @static
	 */
	private static $_instantiated = false;

	/**
	 * Construct the class. This function will inform which classes
	 * you will be able to instantiate and where to find it.
	 * This function implements a kind of singleton design pattern too.
	 * @access public
	 */
	public function __construct() {

		/* don't let the user to instantiate this class more than once. */
		if (self::$_instantiated)
			throw new Exception("You can't instantiate this class again.");

		/* registering allowed classes */
		$this->registerFileInfo('wf_cached_ldap', 'class.wf_cached_ldap.php', 'inc/local/classes');
		$this->registerFileInfo('wf_crypt', 'class.wf_crypt.php', 'inc/local/classes');
		$this->registerFileInfo('wf_date', 'class.wf_date.php', 'inc/local/classes');
		$this->registerFileInfo('wf_db', 'class.wf_db.php', 'inc/local/classes');
		$this->registerFileInfo('wf_engine', 'class.wf_engine.php', 'inc/local/classes');
		$this->registerFileInfo('wf_fpdf', 'class.wf_fpdf.php', 'inc/local/classes');
		$this->registerFileInfo('wf_instance', 'class.wf_instance.php', 'inc/local/classes');
		$this->registerFileInfo('wf_ldap', 'class.wf_ldap.php', 'inc/local/classes');
		$this->registerFileInfo('wf_location', 'class.wf_location.php', 'inc/local/classes');
		$this->registerFileInfo('wf_log', 'class.wf_log.php', 'inc/local/classes');
		$this->registerFileInfo('wf_mail', 'class.wf_mail.php', 'inc/local/classes');
		$this->registerFileInfo('wf_mem_image', 'class.wf_mem_image.php', 'inc/local/classes');
		$this->registerFileInfo('wf_natural', 'class.wf_natural.php', 'inc/local/classes');
		$this->registerFileInfo('wf_orgchart', 'class.wf_orgchart.php', 'inc/local/classes');
		$this->registerFileInfo('wf_paging', 'class.wf_paging.php', 'inc/local/classes');
		$this->registerFileInfo('wf_phplot', 'class.wf_phplot.php', 'inc/local/classes');
		$this->registerFileInfo('wf_regex', 'class.wf_regex.php', 'inc/local/classes');
		$this->registerFileInfo('wf_role', 'class.wf_role.php', 'inc/local/classes');
		$this->registerFileInfo('wf_string', 'class.wf_string.php', 'inc/local/classes');
		$this->registerFileInfo('wf_type', 'class.wf_type.php', 'inc/local/classes');
		$this->registerFileInfo('wf_url', 'class.wf_url.php', 'inc/local/classes');
		$this->registerFileInfo('wf_util', 'class.wf_util.php', 'inc/local/classes');
		$this->registerFileInfo('wf_workitem', 'class.wf_workitem.php', 'inc/local/classes');
		$this->registerFileInfo('wf_report', 'class.wf_report.php', 'inc/local/classes');

		/* ok. no more instances of this class.. */
		self::$_instantiated = true;
	}
}
?>
