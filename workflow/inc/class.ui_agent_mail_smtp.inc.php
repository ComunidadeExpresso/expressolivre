<?php
	/**************************************************************************\
* eGroupWare Workflow - Mail SMTP Agent Connector - interface layer        *
* ------------------------------------------------------------------------ *
* This program is free software; you can redistribute it and/or modify it  *
* under the terms of the GNU General Public License as published           *
* by the Free Software Foundation; either version 2 of the License, or     *
* any later version.                                                       *
\**************************************************************************/

require_once(dirname(__FILE__) . SEP . 'class.ui_agent.inc.php');
/**
 * Mail-SMTP Agent : interface layer. 
 * This class connects the workflow agents calls to the mail_smtp agent business layer
 * 
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author regis.leroy@glconseil.com
 */		
class ui_agent_mail_smtp extends ui_agent
{
	/**
	 * Constructor
	 * @access public
	 * @return object
	 */	
	function ui_agent_mail_smtp()
	{
		parent::ui_agent();
		$this->agent_type = 'mail_smtp';
		$this->bo_agent = Factory::getInstance('bo_agent_mail_smtp');
	}

	/**
	 * Show Admin Activity Options
	 * @param string $template_block_name
	 * @access public
	 * @return void  
	 */
	function showAdminActivityOptions ($template_block_name)
	{
		$admin_name = 'admin_agent_'.$this->agent_type;
		$this->t->set_file($admin_name, $admin_name . '.tpl');
		$this->t->set_block($admin_name, 'block_ag_config_option_input', 'ag_option_input');
		$this->t->set_block($admin_name, 'block_ag_config_option_textarea', 'ag_option_textarea');
		$this->t->set_block($admin_name, 'block_ag_config_option_select_option', 'ag_option_select_option');
		$this->t->set_block($admin_name, 'block_ag_config_option_select', 'ag_option_select');
		$options =& $this->bo_agent->getAdminActivityOptions();
		foreach ($options as $option_name => $option_conf)
		{
			if ($option_conf['type'] == 'text')
			{
				$size = $option_conf['size'];
				if ( (!($size)) || ($size > 80)) $size = 80;
				$this->t->set_var(array(
					'ag_config_name_i'	=> "wf_agent[".$this->agent_type."][".$option_name."]",
					'ag_config_label_i'	=> $option_conf['label'],
					'ag_config_value_i'	=> $option_conf['value'],
					'ag_config_size_i'	=> 'size="'.$size.'"',
				));
				$this->t->parse('ag_option_input','block_ag_config_option_input',true);
			}
			if ($option_conf['type'] == 'textarea')
			{
				$this->t->set_var(array(
					'ag_config_name_t'	=> "wf_agent[".$this->agent_type."][".$option_name."]",
					'ag_config_label_t'	=> $option_conf['label'],
					'ag_config_value_t'	=> $option_conf['value'],
				));
				$this->t->parse('ag_option_textarea','block_ag_config_option_textarea',true);
			}
			if ($option_conf['type'] == 'select')
			{	
				$this->t->set_var(array(
					'ag_config_name_s'	=> "wf_agent[".$this->agent_type."][".$option_name."]",
					'ag_config_label_s'	=> $option_conf['label'],
				));
				foreach($option_conf['values'] as $key => $value)
				{
					$this->t->set_var(array(
						'ag_config_value_s_key'		=> $key,
						'ag_config_value_s_value'	=> $value,
						'ag_config_value_s_selected'	=> ($option_conf['value']==$key)? 'selected': '',
					));
					$this->t->parse('ag_option_select_option','block_ag_config_option_select_option',true);
				}
				$this->t->parse('ag_option_select','block_ag_config_option_select',true);
			}
		}
		//show the shared part handled by parent object
		parent::showAdminActivityOptions('shared_part');
		$this->translate_template($admin_name);
		$this->t->parse($template_block_name, $admin_name);
	}
	
	/**
	 * Function called by the running object (run_activity) after the activity_pre code
	 * and before the user code. This code is runned only if the $GLOBALS['workflow']['__leave_activity']
	 * IS NOT set (i.e.: the user is not cancelling his form in case of interactive activity)
	 * WARNING : on interactive queries the user code is parsed several times and this function is called
	 * each time you reach the begining of the code, this means at least the first time when you show the form
	 * and every time you loop on the form + the last time when you complete the code (if the user did not cancel).
	 * @return bool true or false, if false the $this->error array should contains error messages
	 * @access public
	 */
	function run_activity_pre()
	{
		//load agent data from database
		$this->bo_agent->init();
		
		//this will send an email only if the configuration says to do so
		if (!($this->bo_agent->send_start()))
		{
			$this->error[] = lang('Smtp Agent has detected some errors when sending email at the beginning of the activity');
			$ok = false;
		}
		else
		{
			$ok = true;
		}
		$this->error[] = $this->bo_agent->get_error();
		if ($this->bo_agent->debugmode) echo '<br />START: Mail agent in DEBUG mode:'.implode('<br />',$this->error);
		return $ok;
	}
	
	/**
	 * Function called by the running object (run_activity) after the activity_pre code
	 * and before the user code. This code is runned only if the $GLOBALS['workflow']['__leave_activity']
	 * IS set (i.e.: the user is cancelling his form in case of interactive activity)
	 * @return bool true or false, if false the $this->error array should contains error messages
	 * @access public
	 */
	function run_leaving_activity_pre()
	{
		//actually we never send emails when cancelling
		return true;
	}
	
	/**
	 * Function called by the running object (run_activity) after the user code
	 * and after the activity_pos code. This code is runned only if the $GLOBALS['__activity_completed']
	 * IS set (i.e.: the user has completing the activity)
	 * @return bool true or false, if false the $this->error array should contains error messages
	 * @access public
	 */
	function run_activity_completed_pos()
	{
		//this will send an email only if the configuration says to do so
		if (!($this->bo_agent->send_completed()))
		{
			$this->error[] = lang('Smtp Agent has detected some errors when sending email after completion of the activity');
			$ok = false;
		}
		else
		{
			$ok = true;
		}
		$this->error[] = $this->bo_agent->get_error();
		if ($this->bo_agent->debugmode) echo '<br />COMPLETED: Mail agent in DEBUG mode:'.implode('<br />',$this->error);
		return $ok;
	}
	
	/**
	 * Function called by the running object (run_activity) after the user code
	 * and after the activity_pos code. This code is runned only if the $GLOBALS['__activity_completed']
	 * IS NOT set (i.e.: the user is not yet completing the activity)
	 * WARNING : on interactive queries the user code is parsed several times and this function is called
	 * each time you reach the end of the code without completing, this means at least the first time
	 * and every time you loop on the form.
	 * This function can call two types of mail sending
	 * * sending email on POST queries (usefull for interactive forms), retrieving POSTed values
	 * * sending email at each reach of the end of the code (usefull for automatic activities which 
	 * completes only after execution of user code (sending after completion is not possible). And we
	 * musn't retrieve POSTed values in this case because it can concerns previous non-automatic activities
	 * @return bool true or false, if false the $this->error array should contains error messages
	 * @access public
	 */
	function run_activity_pos()
	{
		if ($this->bo_agent->sendOnPosted())
		{//First case, POSTed emails, we will try to see if there are some POSTed infos
			//form settings POSTED with wf_agent_mail_smtp['xxx'] values
			$this->retrieve_form_settings();
			if (!(isset($this->agent_values['submit_send'])))
			{
				return true;
			}
			else
			{
				//erase agent data with the POSTed values
				$this->bo_agent->set($this->agent_values);
				
				//this will send an email only if the configuration says to do so
				if (!($this->bo_agent->send_post()))
				{
					$this->error[] = lang('Smtp Agent has detected some errors when sending email on demand whith this activity');
					$ok = false;
				}
				else
				{
					$ok = true;
				}
				$this->error[] = $this->bo_agent->get_error();
				if ($this->bo_agent->debugmode) echo '<br />POST: Mail agent in DEBUG mode:'.implode('<br />',$this->error);
				return $ok;
			}
		}
		else
		{//Second case , not about POSTed values, the bo_agent will see himself he he need
		// to do something on end of the user code
			//this will send an email only if the configuration says to do so
			if (!($this->bo_agent->send_end()))
			{
				$this->error[] = lang('Smtp Agent has detected some errors when sending email at the end of this activity');
				$ok = false;
			}
			else
			{
				$ok = true;
			}
			$this->error[] = $this->bo_agent->get_error();
			if ($this->bo_agent->debugmode) echo '<br />END: Mail agent in DEBUG mode:'.implode('<br />',$this->error);
				return $ok;
			}
		}
		
	}
?>
