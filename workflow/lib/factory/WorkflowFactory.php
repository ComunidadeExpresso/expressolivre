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
 * This class is used to instantiate classes by
 * the workflow module (not by processes). You
 * cannot access this class directly, but requests
 * to the Factory frontend class in 'unsecured mode'
 * will be forwarded to this class.
 *
 * @package Factory
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Pedro Eugênio Rocha - pedro.eugenio.rocha@gmail.com
 */
final class WorkflowFactory extends BaseFactory {


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
		$this->registerFileInfo('WorkflowObjects', 'class.WorkflowObjects.inc.php', 'inc');
		$this->registerFileInfo('WorkflowWatcher', 'class.WorkflowWatcher.inc.php', 'inc');
		$this->registerFileInfo('WorkflowLDAP', 'class.WorkflowLDAP.inc.php', 'inc');
		$this->registerFileInfo('WorkflowSecurity', 'class.WorkflowSecurity.inc.php', 'inc');
		$this->registerFileInfo('WorkflowMacro', 'class.WorkflowMacro.inc.php', 'inc');
		$this->registerFileInfo('WorkflowJobManager', 'class.WorkflowJobManager.inc.php', 'inc');
		$this->registerFileInfo('SecurityUtils', 'class.utils.security.php', 'inc');
		$this->registerFileInfo('ResourcesRedirector', 'class.ResourcesRedirector.inc.php', 'inc');
		$this->registerFileInfo('TemplateServer', 'class.TemplateServer.inc.php', 'inc');
		$this->registerFileInfo('CachedLDAP', 'class.CachedLDAP.inc.php', 'inc');
		$this->registerFileInfo('BrowserInfo', 'class.BrowserInfo.inc.php', 'inc');
		$this->registerFileInfo('JobScheduler', 'class.JobScheduler.inc.php', 'inc');
		$this->registerFileInfo('JobRunner', 'class.JobRunner.inc.php', 'inc');
		$this->registerFileInfo('Thread', 'class.Thread.inc.php', 'inc');
		$this->registerFileInfo('Paging', 'class.Paging.inc.php', 'inc');
		$this->registerFileInfo('Logger', 'class.Logger.inc.php', 'inc');
		$this->registerFileInfo('FsUtils', 'class.fsutils.inc.php', 'inc');
		$this->registerFileInfo('UserPictureProvider', 'class.UserPictureProvider.inc.php', 'inc');
		$this->registerFileInfo('powergraphic', 'class.powergraphic.inc.php', 'inc');

		$this->registerFileInfo('run_activity', 'class.run_activity.inc.php', 'inc');
		$this->registerFileInfo('process_smarty', 'class.process_smarty.inc.php', 'inc');
		$this->registerFileInfo('workflow_smarty', 'class.workflow_smarty.inc.php', 'inc');
		$this->registerFileInfo('workflow_acl', 'class.workflow_acl.inc.php', 'inc');
		$this->registerFileInfo('workflow_process', 'class.workflow_process.inc.php', 'inc');
		$this->registerFileInfo('workflow_processmanager', 'class.workflow_processmanager.inc.php', 'inc');
		$this->registerFileInfo('workflow_wfruntime', 'class.workflow_wfruntime.inc.php', 'inc');
		$this->registerFileInfo('workflow_gui', 'class.workflow_gui.inc.php', 'inc');
		$this->registerFileInfo('workflow_rolemanager', 'class.workflow_rolemanager.inc.php', 'inc');
		$this->registerFileInfo('workflow_baseactivity', 'class.workflow_baseactivity.inc.php', 'inc');
		$this->registerFileInfo('workflow_activitymanager', 'class.workflow_activitymanager.inc.php', 'inc');
		$this->registerFileInfo('workflow_instance', 'class.workflow_instance.inc.php', 'inc');

		$this->registerFileInfo('bo_monitors', 'class.bo_monitors.inc.php', 'inc');
		$this->registerFileInfo('bo_adminaccess', 'class.bo_adminaccess.inc.php', 'inc');
		$this->registerFileInfo('bo_userinterface', 'class.bo_userinterface.inc.php', 'inc');
		$this->registerFileInfo('bo_participants', 'class.bo_participants.inc.php', 'inc');
		$this->registerFileInfo('bo_agent_mail_smtp', 'class.bo_agent_mail_smtp.inc.php', 'inc');
		$this->registerFileInfo('bo_editor', 'class.bo_editor.inc.php', 'inc');
		$this->registerFileInfo('bo_utils', 'class.bo_utils.inc.php', 'inc');

		$this->registerFileInfo('so_agent_mail_smtp', 'class.so_agent_mail_smtp.inc.php', 'inc');
		$this->registerFileInfo('so_external_applications', 'class.so_external_applications.inc.php', 'inc');
		$this->registerFileInfo('so_adminaccess', 'class.so_adminaccess.inc.php', 'inc');
		$this->registerFileInfo('so_orgchart', 'class.so_orgchart.inc.php', 'inc');
		$this->registerFileInfo('so_userinterface', 'class.so_userinterface.inc.php', 'inc');
		$this->registerFileInfo('so_adminjobs', 'class.so_adminjobs.inc.php', 'inc');
		$this->registerFileInfo('so_move_instances', 'class.so_move_instances.inc.php', 'inc');

		/* job classes */
		$this->registerFileInfo('AbsoluteDate', 'class.AbsoluteDate.inc.php', 'inc/jobs');
		$this->registerFileInfo('RelativeDate', 'class.RelativeDate.inc.php', 'inc/jobs');
		$this->registerFileInfo('WeekDate', 'class.WeekDate.inc.php', 'inc/jobs');

		/* galaxia engine classes */
		$this->registerFileInfo('GUI', 'GUI.php', 'inc/engine/src/GUI');
		$this->registerFileInfo('ProcessManager', 'ProcessManager.php', 'inc/engine/src/ProcessManager');
		$this->registerFileInfo('ActivityManager', 'ActivityManager.php', 'inc/engine/src/ProcessManager');
		$this->registerFileInfo('InstanceManager', 'InstanceManager.php', 'inc/engine/src/ProcessManager');
		$this->registerFileInfo('RoleManager', 'RoleManager.php', 'inc/engine/src/ProcessManager');
		$this->registerFileInfo('JobManager', 'JobManager.php', 'inc/engine/src/ProcessManager');
		$this->registerFileInfo('Process_GraphViz', 'GraphViz.php', 'inc/engine/src/ProcessManager');
		$this->registerFileInfo('ProcessMonitor', 'ProcessMonitor.php', 'inc/engine/src/ProcessMonitor');
		$this->registerFileInfo('Process', 'Process.php', 'inc/engine/src/API');
		$this->registerFileInfo('Instance', 'Instance.php', 'inc/engine/src/API');
		$this->registerFileInfo('Start', 'Start.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('End', 'End.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('Join', 'Join.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('Split', 'Split.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('Standalone', 'Standalone.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('View', 'View.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('SwitchActivity', 'SwitchActivity.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('Activity', 'Activity.php', 'inc/engine/src/API/activities');
		$this->registerFileInfo('BaseActivity', 'BaseActivity.php', 'inc/engine/src/API');

		$this->registerFileInfo('ajax_ldap', 'class.ajax_ldap.inc.php', 'inc/engine');
		$this->registerFileInfo('ajax_config', 'class.ajax_config.inc.php', 'inc/engine');
		$this->registerFileInfo('WfRuntime', 'WfRuntime.php', 'inc/engine/src/common');
		$this->registerFileInfo('WfSecurity', 'WfSecurity.php', 'inc/engine/src/common');

		/* nano classes */
		$this->registerFileInfo('Services_JSON', 'JSON.php', 'inc/nano');
		$this->registerFileInfo('NanoRequest', 'NanoRequest.class.php', 'inc/nano');
		$this->registerFileInfo('NanoController', 'NanoController.class.php', 'inc/nano');
		$this->registerFileInfo('NanoJsonConverter', 'NanoJsonConverter.class.php', 'inc/nano');
		$this->registerFileInfo('NanoSanitizer', 'NanoSanitizer.class.php', 'inc/nano');

		/* natural classes */
		$this->registerFileInfo('PosString', 'pos_string.php', 'inc/natural');
		$this->registerFileInfo('NatType', 'nat_types.php', 'inc/natural');
		$this->registerFileInfo('NaturalResultSet', 'class.natural_resultset.php', 'inc/natural');

		/* registering egw external classes */
		$this->registerFileInfo('db', 'class.db.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('acl', 'class.acl.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('accounts', 'class.accounts.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('config', 'class.config.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('common', 'class.common.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('sessions', 'class.sessions.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('nextmatchs', 'class.nextmatchs.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('categories', 'class.categories.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('listbox', 'class.listbox.inc.php', '', EGW_INC_ROOT);
		$this->registerFileInfo('phpmailer', 'class.phpmailer.inc.php', '', EGW_INC_ROOT);

		/**
		 * It can cause some troubles. A class named 'bo' must be instantiated by a
		 * Factory::getInstance('bo') call, that isn't really intuitive.. Something to
		 * think about...
		 */
		$this->registerFileInfo('bo', 'class.bo.inc.php', 'emailadmin/inc', EGW_SERVER_ROOT);


		/**
		 * TODO - This is a veeery big workaround to maintain compatibility with
		 * processes that uses the old non-static factory. So, we made this wrapper
		 * (adapter) that just calls the new and cute static factory class in the
		 * right way. It should be removed as soon as possible.
		*/
		$this->registerFileInfo('ProcessWrapperFactory', 'ProcessWrapperFactory.php', 'lib/factory/');


		/**
		 * TODO - This is another ATR - Alternative Technical Resource (common known
		 * as workaround) to allow instantiation of "wf" classes. Although these classes
		 * should have not been instanciated by the workflow module, some of them are
		 * instantiated by run_activity during every execution. =(
		 * In a new version of MVC these objects must be created by the Processes.
		 */
		$this->registerFileInfo('wf_natural', 'class.wf_natural.php', 'inc/local/classes');
		$this->registerFileInfo('wf_db', 'class.wf_db.php', 'inc/local/classes');


		/* ok. no more instances of this class.. */
		self::$_instantiated = true;
	}
}

?>
