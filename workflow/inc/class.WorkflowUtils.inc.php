<?php
	require_once 'common.inc.php';

	//set here the global DEBUG level which is actually 0 (nothing) or 1 (all)
	if (!defined('_DEBUG')) define('_DEBUG', 0);
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class WorkflowUtils
	{
		/**
		 * @var array $public_functions Array of public functions
		 * @access public
		 */
		var $public_functions = array(
			'export'	=> true,
		);
		/**
		 * @var object $t the template 
		 * @access public
		 */
		var $t;		
		/**
		 * @var int wf_p_id  
		 * @access public
		 */
		var $wf_p_id;
		/**
		 * @var array $message message array 
		 * @access public
		 */
		var $message = array();

		//TODO: when migration to bo_workflow_forms will be closed erase theses vars--------------
		//nextmatchs (max number of rows per page) and associated vars
		
		/**
		 * @var int $nextmatchs 
		 * @access public
		 */
		var $nextmatchs;
		
		/**
		 * @var int $start actual starting row number
		 * @access public
		 */
		var $start; 
		/**
		 * @var int $total_records total number of rows 
		 * @access public
		 */
		var $total_records; 
		/**
		 * @var array $message message array column used for order 
		 * @access public
		 */
		var $order; 
		/**
		 * @var string $sort ASC or DESC 
		 * @access public
		 */
		var $sort; 
		/**
		 * @var array $sort_mode combination of order and sort   
		 * @access public
		 */
		var $sort_mode; 
		/**
		 * @var array $search_str  
		 * @access public
		 */
		var $search_str;
		//------------------------------------------------------------------------------------------
		/**
		 * @var array $stats
		 * @access public
		 */
		var $stats;
		/**
		 * @var array $wheres 
		 * @access public
		 */
		var $wheres = array();

		/**
		 * Constructor of workflow class
		 *  
		 * @access public
		 * @return void
		 */
		function WorkflowUtils()
		{
			// check version
			if (alessthanb($GLOBALS['phpgw_info']['apps']['workflow']['version'], '1.2.01.006'))
			{
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
				die("Please upgrade this application to be able to use it");
			}

			$this->t		=& $GLOBALS['phpgw']->template;
			$this->wf_p_id		= (int)get_var('p_id', 'any', 0);
			$this->start		= (int)get_var('start', 'any', 0);
			$this->search_str	= get_var('find', 'any', '');
			$this->nextmatchs	= Factory::getInstance('nextmatchs');
		}

		/**
		 * Fill the process bar
		 *
		 * @param array $proc_info
		 * @access public
		 * @return string
		 */
		function fill_proc_bar($proc_info)
		{
			//echo "proc_info: <pre>";print_r($proc_info);echo "</pre>";
			$this->t->set_file('proc_bar_tpl', 'proc_bar.tpl');
			$templateServer = &Factory::getInstance('TemplateServer');

			if ($proc_info['wf_is_valid'] == 'y')
			{
				$dot_color = 'green';
				$alt_validity = lang('valid');
			}
			else
			{
				$dot_color = 'red';
				$alt_validity = lang('invalid');
			}

			// if process is active show stop button. Else show start button, but only if it is valid. If it's not valid, don't show any activation or stop button.
			if ($proc_info['wf_is_active'] == 'y')
			{
				$start_stop = '<td><a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&p_id='. $proc_info['wf_p_id'] .'&deactivate_proc='. $proc_info['wf_p_id']) .'"><img border ="0" src="'. $templateServer->generateImageLink('stop.gif') .'" alt="'. lang('stop') .'" title="'. lang('stop') .'" />'.lang('stop').'</a></td>';
			}
			elseif ($proc_info['wf_is_valid'] == 'y')
			{
				$start_stop = '<td><a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&p_id='. $proc_info['wf_p_id'] .'&activate_proc='. $proc_info['wf_p_id']) .'"><img border ="0" src="'. $templateServer->generateImageLink('refresh2.gif') .'" alt="'. lang('activate') .'" title="'. lang('activate') .'" />'.lang('activate').'</a></td>';
			}
			else
			{
				$start_stop = '';
			}
			$this->t->set_var(array(
				'proc_name'				=> $proc_info['wf_name'],
				'version'				=> $proc_info['wf_version'],
				'img_validity'			=> $templateServer->generateImageLink($dot_color.'_dot.gif'),
				'alt_validity'			=> $alt_validity,
				'start_stop'			=> $start_stop,
				'link_admin_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&p_id='. $proc_info['wf_p_id']),
				'img_activity'			=> $templateServer->generateImageLink('Activity.gif'),
				'link_admin_jobs'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminjobs.form&p_id='. $proc_info['wf_p_id']),
				'img_job'			=> $templateServer->generateImageLink('clock.png'),
				'link_admin_processes'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&p_id='. $proc_info['wf_p_id']),
				'img_change'			=> $templateServer->generateImageLink('change.gif'),
				'link_admin_shared_source'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&p_id='. $proc_info['wf_p_id']),
				'img_code'			=> $templateServer->generateImageLink('code.png'),
				'link_admin_export'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.WorkflowUtils.export&p_id='. $proc_info['wf_p_id']),
				'link_admin_roles'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form&p_id='. $proc_info['wf_p_id']),
				'img_roles'			=> $templateServer->generateImageLink('roles.png'),
				'link_graph'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.show_graph&p_id=' . $proc_info['wf_p_id']),
				'img_process'			=> $templateServer->generateImageLink('Process.gif'),
				'link_save_process'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.save_process&id='. $proc_info['wf_p_id']),
				'img_save'			=> $templateServer->generateImageLink('save.png')
			));

			$this->translate_template('proc_bar_tpl');
			return $this->t->parse('proc_bar', 'proc_bar_tpl');
		}
		/**
		 * Select activity representation icon
		 *
		 * @var string $type type of activity
		 * @var bool   $interactive interactive?
		 * @access public
		 * @return void
		 */
		function act_icon($type, $interactive)
		{
			switch($type)
			{
				case 'activity':
					$ic = "mini_".(($interactive == 'y')? 'blue_':'')."rectangle.gif";
					break;
				case 'switch':
					$ic = "mini_".(($interactive == 'y')? 'blue_':'')."diamond.gif";
					break;
				case 'start':
					$ic="mini_".(($interactive == 'y')? 'blue_':'')."circle.gif";
					break;
				case 'end':
					$ic="mini_".(($interactive == 'y')? 'blue_':'')."dbl_circle.gif";
					break;
				case 'split':
					$ic="mini_".(($interactive == 'y')? 'blue_':'')."triangle.gif";
					break;
				case 'join':
					$ic="mini_".(($interactive == 'y')? 'blue_':'')."inv_triangle.gif";
					break;
				case 'standalone':
					$ic="mini_".(($interactive == 'y')? 'blue_':'')."hexagon.gif";
					break;
				case 'view':
					$ic="mini_blue_eyes.gif";
					break;
				default:
					$ic="no-activity.gif";
			}
			return '<img src="'. Factory::getInstance('TemplateServer')->generateImageLink($ic) .'" alt="'. lang($type) .'" title="'. lang($type) .'" />';
		}

		/**
		 * Translate template file
		 * @param string $template_name template name
		 * @return void
		 * @access public
		 */
		function translate_template($template_name)
		{
			$undef = $this->t->get_undefined($template_name);
			if ($undef != False)
			{
				foreach ($undef as $value)
				{
					$valarray = explode('_', $value);
					$type = array_shift($valarray);
					$newval = implode(' ', $valarray);
					if ($type == 'lang')
					{
						$this->t->set_var($value, lang($newval));
					}
				}
			}
		}
		/**
		 * Show errors
		 * @param object $activity_manager
		 * @param string $error_str destination string to place errors
		 * @return bool
		 * @access public 
		 */
		function show_errors(&$activity_manager, &$error_str)
		{
			$valid = $activity_manager->validate_process_activities($this->wf_p_id);
			$errors = $activity_manager->get_error(true);
			$warnings = $activity_manager->get_warning(true);
			$tmp = array();

			/* remove empty errors from the error list */
			foreach ($errors as $index => $error)
				if (trim($error) == '')
					unset($errors[$index]);

			if ((count($warnings) > 0) || (count($errors) > 0))
			{
				$error_str = '';
				$output = 'y';
				if (count($errors) > 0)
				{
					$error_str = '<b>' . lang('The following items must be corrected to be able to activate this process').':</b><br/><small><ul>';
					foreach ($errors as $error)
					{
						$error_str .= '<li>'. $error . '<br/>';
					}
					$error_str .= '</ul></small>';
					$output = 'n';
				}

				if (count($warnings) > 0)
				{
					if ($error_str != '')
						$error_str .= "<br />";

					$error_str .= '<b>' . lang('warnings in this process').':</b><br/><small><ul>';
					foreach ($warnings as $warning)
						if (trim($warning) != '')
							$error_str .= '<li>'. $warning . '<br/>';

					$error_str .= '</ul></small>';
				}
				return $output;
			}
			else
			{
				$error_str = '';
				return 'y';
			}
		}
		
		/**
		 * Get source code
		 * @param string $proc_name process name 
		 * @param string $act_name activity name
		 * @param string $type actyvity type
		 * @access public
		 * @return string source code dat
		 */
		function get_source($proc_name, $act_name, $type)
		{
			switch($type)
			{
				case 'code':
					$path =  'activities' . SEP . $act_name . '.php';
					break;
				case 'template':
					$path = 'templates' . SEP . $act_name . '.tpl';
					break;
				default:
					$path = 'shared.php';
					break;
			}
			$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
												if (!$file_size = filesize($complete_path)) return '';
			$fp = fopen($complete_path, 'r');
			$data = fread($fp, $file_size);
			fclose($fp);
			return $data;
		}
		/**
		 * Save the source of process
		 * 
		 * @param string $proc_name process name
		 * @param string $act_name activity name
		 * @param string $type type of activity
		 * @param string $source source code of activity
		 * @return void
		 * @access public
		 */
		function save_source($proc_name, $act_name, $type, $source)
		{
			// in case code was filtered
			if (!$source) $source = @$GLOBALS['egw_unset_vars']['_POST[source]'];

			switch($type)
			{
				case 'code':
					$path =  'activities' . SEP . $act_name . '.php';
					break;
				case 'template':
					$path = 'templates' . SEP . $act_name . '.tpl';
					break;
				default:
					$path = 'shared.php';
					break;
			}
			$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
			// In case you want to be warned when source code is changed:
			// mail('yourmail@domain.com', 'source changed', "PATH: $complete_path \n\n SOURCE: $source");
			$fp = fopen($complete_path, 'w');
			fwrite($fp, $source);
			fclose($fp);
		}

		/**
		 * Export process to a xml file to be downloaded 
		 * @access public
		 * @return void
		 */
		function export()
		{
			$this->process_manager	= Factory::getInstance('workflow_processmanager');

			// retrieve process info
			$proc_info = $this->process_manager->get_process($this->wf_p_id);
			$filename = $proc_info['wf_normalized_name'].'.xml';
			$out = $this->process_manager->serialize_process($this->wf_p_id);
			$mimetype = 'application/xml';
			// MSIE5 and Opera show allways the document if they recognise. But we want to oblige them do download it, so we use the mimetype x-download:
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5') || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera 7'))
				$mimetype = 'application/x-download';
			// Show appropiate header for a file to be downloaded:
			header("content-disposition: attachment; filename=$filename");
			header("content-type: $mimetype");
			header('content-length: ' . strlen($out));
			echo $out;
		}
		
		/**
		* Get the href link for the css file, searching for themes specifics stylesheet if any
		* @param string $css_name is the name of the css file, without the .css extension
		* @param bool $print_mode is false by default, if true '_print.css' is appended to the name if this css print file exists
		* @return string a string containing the link to a css file that you can use in a href, you'll have at least a link
		* to a non-existent css in template/default/css/
		* @access public
		*/
		function get_css_link($css_name, $print_mode = false)
		{
			$file = "css/$css_name" . (($print_mode !== false) ? '_print' : '') . '.css';
			return Factory::getInstance('TemplateServer')->getWebFile($file);
		}

		/**
		 * Return a given duration in human readable form, usefull for workitems duration
		 * @param int $to given duration
		 * @return string given duration in human readable form
		 * @access public
		 */
		function time_diff($to) {
			$days = (int)($to/(24*3600));
			$to = $to - ($days*(24*3600));
			$hours = (int)($to/3600);
			$to = $to - ($hours*3600);
			$min = date("i", $to);
			$to = $to - ($min*60);			
			$sec = date("s", $to);

			return lang('%1 days, %2:%3:%4',$days,$hours,$min,$sec);
		}

	}
?>
