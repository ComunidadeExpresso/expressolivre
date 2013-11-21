<?php
/**************************************************************************\
* eGroupWare Workflow - Mail SMTP Agent Connector - business layer         *
* ------------------------------------------------------------------------ *
* This program is free software; you can redistribute it and/or modify it  *
* under the terms of the GNU General Public License as published           *
* by the Free Software Foundation; either version 2 of the License, or     *
* any later version.                                                       *
\**************************************************************************/

require_once(dirname(__FILE__) . SEP . 'class.bo_agent.inc.php');

//some define for the send mode
if (!defined('_SMTP_MAIL_AGENT_SND_COMP')) 		define('_SMTP_MAIL_AGENT_SND_COMP'    , 0);
if (!defined('_SMTP_MAIL_AGENT_SND_POST')) 		define('_SMTP_MAIL_AGENT_SND_POST'	  , 1);
if (!defined('_SMTP_MAIL_AGENT_SND_AUTO_PRE')) 	define('_SMTP_MAIL_AGENT_SND_AUTO_PRE', 2);
if (!defined('_SMTP_MAIL_AGENT_SND_AUTO_POS')) 	define('_SMTP_MAIL_AGENT_SND_AUTO_POS', 3);
/**
 * This class connects the workflow agent to the egroupware phpmailer and emailadmin
 * This let the workflow activities send emails. It contains some logic to replace
 * known tokens by workflow information (user, owner, activity name, etc...)
 * 
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author regis.leroy@glconseil.com
 */	
class bo_agent_mail_smtp extends bo_agent
{
	/**
	 * @var array $public_functions Public functions
	 * @access public
	 */
	var $public_functions = array(
		'bo_agent_mail_smtp'			=> true,
		'load'							=> true,
		'save'							=> true,
		'getAdminActivityOptions'		=> true,
		'decode_fields_in_final_array' 	=> true,
	);
	/**
	 * @var object $mail the phpmailer object used at runtime to send email
	 * @access public
	 */
	var $mail = null;	
	/**
	 * @var object $bo_emailadmin object the emailadmin bo object to retriev egroupware mail configuration
	 * @access public
	 */
	var $bo_emailadmin = null;		
	/**
	 * @var integer $profileID profile ID
	 * @access public
	 */
	var $profileID;
	// some maybe usefull egroupware or engine objects. Vars usefull to create only the first time
	// to avoid multiple SQL queries
	/**
	 * @var object $role_manager Role manager
	 * @access public
	 */
	var $role_manager;
	/**
	 * @var object $account The account
	 * @access public
	 */
	var $account;
	/**
	 * @var string $process_name The process name 
	 * @access public
	 */
	var $process_name = '';
	/**
	 * @var string $process_version The process version 
	 * @access public
	 */
	var $process_version = '';
	/**
	 * @var integer $process_id The process id 
	 * @access public
	 */
	var $process_id = '';
	/**
	 * @var object $activity_id The activity id
	 * @access public
	 */
	var $activity_id = '';
	/**
	 * @var integer $instance_id The instance id
	 * @access public
	 */
	var $instance_id = '';
	/**
	 * @var array $final_array array containing part or this->fields recomputed to handle real email address and real values
	 * @access public
	 */
	var $final_array = Array();	
	/**
	 * @var boolean $debugmode can be usefull to test mails building without sending them
	 * @access public
	 */
	var $debugmode = false;
	
	/**
	 * Contructor
	 * @access public
	 * @return object
	 */
	function bo_agent_mail_smtp()
	{
		parent::bo_agent();
		$this->so_agent = Factory::getInstance('so_agent_mail_smtp');
		$this->bo_emailadmin = Factory::getInstance('bo');
		//the showProcessConfigurationFields is not done here, quite harder to build
		$this->ProcessConfigurationFieldsdefault = array(
			'mail_smtp_profile' 		=> false,
			'mail_smtp_signature'		=> lang('Mail automatically sent by Mail SMTP Agent for eGroupware\'s Workflow'),
			'mail_smtp_local_link_prefix'	=> '',
			'mail_smtp_debug'		=> false,
		);
		
		$this->title = lang('Mail Smtp Agent');
		$this->description = lang('This agent gives the activity the possibility to send an SMTP message (mail)');
		$this->help = lang('Use <a href="%1">EmailAdmin</a> to create mail profiles', $GLOBALS['phpgw']->link('/index.php',array('menuaction' => 'emailadmin.ui.listProfiles')));
		$this->help .= "<br />\n".lang('Mails can be sent at the begining or at the end of the activity, For interactive activities only it can be sent after completion.');
		$this->help .= "<br />\n".lang('Be carefull with interactive activity, end and start of theses activities are multiple.');
		$this->help .= "<br />\n".lang('You can use special values with this mail agent:');
		$this->help .= "<ul>\n";
		$this->help .=  '<li>'.lang('<strong>%user%</strong> is the instance user email')."\n";
		$this->help .=  '<li>'.lang('<strong>%owner%</strong> is the instance owner email')."\n";
		$this->help .=  '<li>'.lang('<strong>%roles%</strong> are the emails of all users mapped to any role on this activity')."\n";
		$this->help .=  '<li>'.lang('<strong>%role_XX%</strong> are all the emails of all users mapped to the role XX')."\n";
		$this->help .=  '<li>'.lang('<strong>%user_XX%</strong> is the email of the acount XX')."\n";
		$this->help .=  '<li>'.lang('<strong>%property_XX%</strong> is the content of the instance\'s property XX')."\n";
		$this->help .=  '<li>'.lang('<strong>%signature%</strong> is the agent signature defined in the process configuration')."\n";
		$this->help .=  '<li>'.lang('see as well <strong>%instance_name%</strong>, <strong>%activity_name%</strong>, <strong>%process_name%</strong>,<strong>%process_version%</strong>, <strong>%instance_id%</strong>, <strong>%activity_id%</strong> and <strong>%process_id%</strong>')."\n";
		$this->help .=  '<li>'.lang('finally you have links with <strong>%link_XX|YY%</strong> syntax, XX is the address part, YY the text part.')."\n";
		$this->help .= lang('Link addresses are considered local if not containing <strong>http://</strong>. They will get appended the configured local prefix and scanned by egroupware link engine');
		$this->help .= "</ul>\n";
		$this->fields = array(
			'wf_to'		=> array(
				'type'		=> 'text',
				'label'		=> lang('To:'),
				'size'		=> 255,
				'value'		=> '',
				),
			'wf_cc'		=> array(
				'type'		=> 'text',
				'label'		=> lang('Cc:'),
				'size'		=> 255,
				'value'		=> '',
				),
			'wf_bcc'	=> array(
				'type'		=> 'text',
				'label'		=> lang('Bcc:'),
				'size'		=> 255,
				'value'		=> '',
				),
			'wf_from'	=> array(
				'type'		=> 'text',
				'label'		=> lang('From:'),
				'size'		=> 255,
				'value'		=> '',
				),
			'wf_replyto'	=> array(
				'type'		=> 'text',
				'label'		=> lang('ReplyTo:'),
				'size'		=> 255,
				'value'		=> '',
				),
			'wf_subject'	=> array(
				'type'		=> 'text',
				'label'		=> lang('Subject:'),
				'size'		=> 255,
				'value'		=> '',
				),
			'wf_message'	=> array(
				'type'		=> 'textarea',
				'label'		=> lang('Message:'),
				'value'		=> '',
				),
			'wf_send_mode'	=> array(
				'type'	=> 'select',
				'label'	=> lang('When to send the Message:'),
				'value' => '',
				'values'=>  array(
					_SMTP_MAIL_AGENT_SND_COMP	=> lang('send after interactive activity is completed'),
					/*_SMTP_MAIL_AGENT_SND_POST	=> lang("send when wf_agent_mail_smtp['submit_send'] is posted"),*/
					_SMTP_MAIL_AGENT_SND_AUTO_PRE	=> lang("send when the activity is starting"),
					_SMTP_MAIL_AGENT_SND_AUTO_POS	=> lang("send when the activity is ending"),
					),
				),
		);
		
	}

   /**
	* Factory: Load the agent values stored somewhere in the agent object and retain the agent id
	* @param int $agent_id is the agent id
	* @param bool $really_load boolean, true by default, if false the data wont be loaded from database and
	* the only thing done by this function is storing the agent_id (usefull if you know you wont need actual data)
	* @return bool  false if the agent cannot be loaded, true else
	* @access public
	*/
	function load($agent_id, $really_load=true)
	{
		//read values from the so_object
		if ($really_load)
		{
			$values =& $this->so_agent->read($agent_id);
			foreach($values as $key => $value)
			{
				//load only known fields
				if (isset($this->fields[$key]))
				{
					$this->fields[$key]['value'] = $value;
					//echo "<br> DEBUG loading value $value for $key";
				}
			}
		}
		//store the id
		$this->agent_id = $agent_id;
	}

	/**
	 * Save the agent
	 * @return bool false if the agent cannot be saved, true else
	 * @access public
	 */
	function save()
	{
		//make a simplified version of $this->fields with just values
		$simplefields = Array();
		foreach ($this->fields as $field => $arrayfield)
		{
			$simplefields[$field] = $arrayfield['value'];
		}
		return $this->so_agent->save($this->agent_id, $simplefields);
	}
	
	/**
	 * Lists activity level options avaible for the agent
	 * @return array an associative array which can be empty
	 * @access public
	 */
	function getAdminActivityOptions ()
	{
		return $this->fields;
	}
	
	/**
	 * This function tell the engine which process level options have to be set
	 * for the agent. Theses options will be initialized for all processes by the engine
	 * and can be different for each process.
	 * @return array an array which can be empty
	 * @access public
	 */
	function listProcessConfigurationFields()
	{
		$profile_list = $this->bo_emailadmin->getProfileList();
		foreach($profile_list as $profile)
		{
			$my_profile_list[$profile['profileID']] = $profile['description'];
		}
		$this->showProcessConfigurationFields = array(
			'Mail SMTP Agent' 		=> 'title',
			'mail_smtp_profile' 		=> $my_profile_list,
			'mail_smtp_signature'		=> 'text',
			'mail_smtp_local_link_prefix'	=> 'text',
			'mail_smtp_debug'		=> 'yesno',
		);
		return $this->showProcessConfigurationFields;
	}

	/**
	 * Return the SMTP config values stored by the emailadmin egw application
	 * @return array an associative array containing the'emailConfigValid' token at true if it was ok, and at false else
	 * @access public
	 */
	function getSMTPConfiguration()
	{
		$data =Array();
		$this->profileID = $this->conf['mail_smtp_profile'];
		$data['emailConfigValid'] = true;
		//code inspired by felamimail bo_preferences
		$profileData = $this->bo_emailadmin->getProfile($this->profileID);
		if(!is_array($profileData))
		{
			$data['emailConfigValid'] = false;
			return $data;
		}
		elseif ($this->profileID != $profileData['profileID'])
		{
			$this->profileID = $profileData['profileID'];
		}
		
		// set values to the global values
		$data['defaultDomain']		= $profileData['defaultDomain'];
		$data['smtpServerAddress']	= $profileData['smtpServer'];
		$data['smtpPort']		= $profileData['smtpPort'];
		$data['smtpAuth']		= $profileData['smtpAuth'];
		$data['smtpType']               = $profileData['smtpType'];
		$useremail = $this->bo_emailadmin->getAccountEmailAddress($GLOBALS['phpgw_info']['user']['userid'], $this->profileID);
		$data['emailAddress']           = $useremail[0]['address'];
		return $data;
	}
	
	/**
	 * Initialize objects we will need for the mailing and retrieve the conf 
	 * @return void
	 * @access public
	 */
	function init()
	{
		$this->mail = Factory::getInstance('phpmailer');
		//set the $this->conf
		$this->getProcessConfigurationFields($this->activity->getProcessId());
		if ($this->conf['mail_smtp_debug']) $this->debugmode = true;
		
	}
	
	/**
	 * Says that we send email on POSTed forms
	 * @return bool true if the conf says that we send email on POSTed forms, else false.
	 * @access public
	 */
	function sendOnPosted()
	{
		return ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_POST);
	}
	
	/**
	 * If this activity is defined as an activity sending the email when starting we'll send it now
	 * WARNING : on interactive queries the user code is parsed several times and this function is called
	 * each time you reach the begining of the code, this means at least the first time when you show the form
	 * and every time you loop on the form + the last time when you complete the code (if the user did not cancel).
	 * @return bool true if everything was ok, false if something went wrong
	 * @access public
	 */
	function send_start()
	{
		if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_AUTO_PRE)
		{
			if ($this->debugmode) $this->error[] = 'Sending at the start of the activity';
			if (!($this->prepare_mail())) return false;
			return $this->send();
		}
		else
		{
			if ($this->debugmode) $this->error[] = 'Not sending at the start of the activity';
			return true;
		}
	}

	/**
	 * If this activity is defined as an activity sending the email when finishing the code we'll send it now
	 * WARNING : on interactive queries the user code is parsed several times and this function is called
	 * each time you reach the end of the code without completing, this means at least the first time
	 * and every time you loop on the form.
	 * @return bool true if everything was ok, false if something went wrong
	 * @access public
	 */
	function send_end()
	{
		if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_AUTO_POS)
		{
			if ($this->debugmode) $this->error[] = 'Sending at the end of the activity';
			if (!($this->prepare_mail())) return false;
			return $this->send();
		}
		else
		{
			if ($this->debugmode) $this->error[] = 'Not sending at the end of the activity';
			return true;
		}
	}

	/**
	 * If this activity is defined as an activity sending the email when the user post a command for it we'll send it now
	 * @return bool true if everything was ok, false if something went wrong
	 * @access public
	 */
	function send_post()
	{
		if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_POST)
		{
			if ($this->debugmode) $this->error[] = 'Sending at POST in the activity';
			if (!($this->prepare_mail())) return false;
			return $this->send();
		}
		else
		{
			if ($this->debugmode) $this->error[] = 'Not sending at POST in the activity';
			return true;
		}
	}
	
	/**
	 * If this activity is defined as an activity sending the email when completing we'll send it now
	 * @return bool true if everything was ok, false if something went wrong
	 * @access public
	 */
	function send_completed()
	{
		if ($this->fields['wf_send_mode']['value']== _SMTP_MAIL_AGENT_SND_COMP)
		{
			if ($this->debugmode) $this->error[] = 'Sending when completing activity';
			if (!($this->prepare_mail())) return false;
			return $this->send();
		}
		else
		{
			if ($this->debugmode) $this->error[] = 'Not Sending when completing activity';
			return true;
		}
	}

	/**
	 * Buid the email fields
	 * @return boolean true ok false error 
	 * @access public
	 */
	function prepare_mail()
	{
		$userLang = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];
		$langFile = PHPGW_SERVER_ROOT."/phpgwapi/setup/phpmailer.lang-$userLang.php";
		if(file_exists($langFile))
		{
			$this->mail->SetLanguage($userLang, PHPGW_SERVER_ROOT."/phpgwapi/setup/");
		}
		else
		{
			$this->mail->SetLanguage("en", PHPGW_SERVER_ROOT."/phpgwapi/setup/");
		}
		$this->mail->PluginDir = PHPGW_SERVER_ROOT."/phpgwapi/inc/";
		$this->mail->IsSMTP();
		
		//SMTP Conf
		$smtpconf =& $this->getSMTPConfiguration();
		if (!($smtpconf['emailConfigValid']))
		{
			$this->error[] = lang('The SMTP configuration cannot be loaded by the mail_smtp workflow agent');
			return false;
		}
		$this->mail->Host 	= $smtpconf['smtpServerAddress'];
		$this->mail->Port	= $smtpconf['smtpPort'];
		//SMTP Auth?
		if ($smtpconf['smtpAuth'])
		{
			$this->mail->SMTPAuth	= true;
			$this->mail->Username	= $GLOBALS['phpgw_info']['user']['userid'];
			$this->mail->Password	= $GLOBALS['phpgw_info']['user']['passwd'];
		}
		
		$this->mail->Encoding = '8bit';
		//TODO: handle Charset
		//$this->mail->CharSet	= $this->displayCharset;
		$this->mail->AddCustomHeader("X-Mailer: Egroupware Workflow");
		$this->mail->WordWrap = 76;
		//we need HTMl for handling nicely links
		$this->mail->IsHTML(true);
		//compute $this->final_fields if not done already
		if (!( $this->decode_fields_in_final_fields($smtpconf['defaultDomain']) ))
		{
			$this->error[] = lang('We were not able to build the message');
			return false;
		}

		$process = Factory::getInstance('workflow_process');
		$process->getProcess($this->process_id);
		$this->process_name = $process->getName();
		$this->process_version = $process->getVersion();
		unset ($process);
		
		$this->mail->From 	= $this->mail->EncodeHeader($this->final_fields['wf_from']);
		//$this->mail->FromName 	= $this->activity->getName();
		$this->mail->FromName 	= $this->process_name;
		$this->mail->Subject 	= $this->mail->EncodeHeader($this->final_fields['wf_subject']);
		$this->mail->Body    	= str_replace("\n",'<br />',html_entity_decode($this->final_fields['wf_message']));
		// if you need compatibility to older email clients (e.g. mutt), uncomment the line below
		//$this->mail->AltBody	= $this->final_fields['wf_message'];
		$this->mail->ClearAllRecipients();
		foreach ($this->final_fields['wf_to'] as $email)
		{
			if (!(empty($email))) $this->mail->AddAddress($email);
		}
		foreach ($this->final_fields['wf_cc'] as $email)
		{
			if (!(empty($email))) $this->mail->AddCC($email);
		}
		foreach ($this->final_fields['wf_bcc'] as $email)
		{
			if (!(empty($email))) $this->mail->AddBCC($email);
		}
		$email = $this->final_fields['wf_replyto'];
		if (!(empty($email))) $this->mail->AddReplyTo($email);
		return true;
	}
	
	/**
	  * This function is used to decode admin instructions about the final value or the activity fields.
	  * 
	  *  i.e.: decoding %user% in toto@foo.com for example
	  *	If you call this function twice the final result will NOT be recalculated. except with the $force 
	  *	parameter. This is done so that you can call this function sooner than the engine and add or remove
	  *	emails from final fields. The engine will not recompute automatically theses fields if you done it already.
	  * @param string $defaultDomain is the default mail Domain, used with empty domains
	  * @param bool $force is falmse by default, if true the final are recalculated even if they are already there
	  * @return bool true/false and set the $this->final_fields array containing the fields with the 'real' final value and for 
	  * the wf_to, wf_bcc and wf_cc fields you'll have arrays with email values.
	  * @access public
	 */
	function decode_fields_in_final_fields($defaultDomain, $force=false)
	{
		if ($force || (!(isset($this->final_fields['calculated']))) )
		{
			$res = Array();
			$result = Array();
			$address_array = Array();
			$email_list = Array();
			foreach ($this->fields as $key => $value)
			{
				$res[$key] =& $this->replace_tokens($value['value']);
				//for all adresse fields we make an email array to detect repetitions
				if (($key=='wf_to') || ($key=='wf_cc') || ($key=='wf_bcc'))
				{
					 //_debug_array($res[$key]);//DEBUG
					 //clean ',,' or ', ,'  or starting or ending by ','
					 $res[$key] = $this->cleanup_adress_string($res[$key]);
					//_debug_array($res[$key]);//DEBUG 
					
					//warning, need to handle < and > as valid chars for emails
					$address_array  = imap_rfc822_parse_adrlist(str_replace('&gt;','>',str_replace('&lt;','<',$res[$key])),'');
					//_debug_array($address_array);//DEBUG
					if (is_array($address_array) && (!(empty($address_array))))
					{
						foreach ($address_array as $val)
						{
							//we retain this email is used in To or Bcc or Cc
							//and we affect this email only the first time
							//first detect errors
							if ($val->host == '.SYNTAX-ERROR.')
							{
								$this->error[] = lang("at least one email address cannot be validated.");
								if ($this->debugmode)
								{
									$this->error[] = $res[$key];
								}
								return false;
							}
							//detect empty domains
							if (empty($val->host))
							{ 
								$val->host = $defaultDomain;
							}
							//build email adress
							$his_email = $val->mailbox.'@'. $val->host;
							if (!isset($email_list[$his_email]))
							{
								$email_list[$his_email] = $key;
								$result[$key][]= $his_email;
							}
						}
					}
					else
					{
						$result[$key] = Array();
					}
				}
				elseif ( ($key=='wf_from') || ($key=='wf_replyto'))
				{
					//warning, need to handle < and > as valid chars for emails
					$result[$key] = str_replace('&gt;','>',str_replace('&lt;','<',$res[$key]));
				}
				else
				{
					$result[$key] = $res[$key];
				}
			}
			$this->final_fields =& $result;
			$this->final_fields['calculated']=true;
		}
		return true;
	}

	/**
	 * This function will clean ',,' or ', ,'  or starting or ending by ',' in the email address string list.
	 * @param string $address_string is the string we should clean
	 * @return string the cleaned up string
	 * @access public
	 */
	function cleanup_adress_string($address_string)
	{
		//in PHP5 we could ve been using the count parameter to stop recursivity
		$new = str_replace(array(', ,' , ',,'),array(',', ','),trim(trim($address_string),','));
		if ($new == $address_string)
		{
			//it did nothing, lets stop recursivity
			return $new;
		}
		{
			//we made sime changes, lets verify the new string is syntaxically correct, recursivity
			return $this->cleanup_adress_string($new);
		}
	}

	/**
	 * This function is used to find and replace tokens in the fields
	 * @param string $string is the string to analyse
     * @return string the modified string
     * @access public
	 */
	function replace_tokens(&$string)
	{
		//first we need to escape the \% before the analysis
		$string = str_replace('\%','&workflowpourcent;',$string);
		$matches = Array();
		preg_match_all("/%([^%]+)%/",$string, $matches);
		$final = $string;
		if ($this->activity_id =='') $this->activity_id = $this->activity->getActivityId();
		if ($this->instance_id =='') $this->instance_id = $this->instance->getInstanceId();
		if ($this->process_id =='') $this->process_id = $this->activity->getProcessId();
		foreach($matches[1] as $key => $value)
		{
			//$value is our %token%
			switch($value)
			{
				case 'signature':
							$matches[1][$key] = $this->conf['mail_smtp_signature'];
							break;
				case 'instance_name' :
					$matches[1][$key] = $this->instance->getName();
					break;
				case 'activity_name' :
					$matches[1][$key] = $this->activity->getName();
					break;
				case 'process_name' :
					if ($this->process_name=='')
					{
						$process = Factory::getInstance('workflow_process');
						$process->getProcess($this->process_id);
						$this->process_name = $process->getName();
						$this->process_version = $process->getVersion();
						unset ($process);
					}
					$matches[1][$key] = $this->process_name;
					break;
				case 'process_version' :
					if ($this->process_version=='')
					{
						$process = Factory::getInstance('workflow_process');
						$process->getProcess($this->process_id);
						$this->process_name = $process->getName();
						$this->process_version = $process->getVersion();
						unset ($process);
					}
					$matches[1][$key] = $this->process_version;
					break;
				case 'process_id' :
					$matches[1][$key] = $this->process_id;
					break;
				case 'instance_id' :
					$matches[1][$key] = $this->instance_id;
					break;
				case 'activity_id' :
					$matches[1][$key] = $this->activity_id;
					break;
				case 'user' :
					//the current instance/activity user which is in fact running
					//this class actually
					$matches[1][$key] = $GLOBALS['phpgw_info']['user']['email'];
					break;
				case 'owner' :
					//the owner of the instance
					if (!is_object($this->account))
					{
						$this->account = Factory::getInstance('accounts');
					}
					$ask_user = $this->instance->getOwner();
					$matches[1][$key] = $this->account->id2name($ask_user, 'account_email');
					break;
				case 'roles' :
					//all users having at least one role on this activity
					if (!is_object($this->role_manager))
					{
						$this->role_manager = Factory::getInstance('workflow_rolemanager');
					}
					if (!is_object($this->account))
					{
						$this->account = Factory::getInstance('accounts');
					}
					$my_subset = array('wf_activity_name' => $this->activity->getName());
					$listing =& $this->role_manager->list_mapped_users($this->instance->getProcessId(),true, $my_subset);
					$matches[1][$key] = '';
					foreach ($listing as $user_id => $user_name)
					{
						$user_email = $this->account->id2name($user_id);
						if ($matches[1][$key] == '')
						{
							$matches[1][$key] = $this->account->id2name($user_id, 'account_email');
						}
						else
						{
							$matches[1][$key] .= ', '.$this->account->id2name($user_id, 'account_email');
						}
					}
					break;
				default:
					//Now we need to handle role_foo or property_bar or user_foobar
					$matches2 = Array();
					//echo "<br>2nd analysis on ".$value;
					preg_match_all("/([^_]+)([_])([A-z0-9\|:\/\.\?\=\'\&\; ]*)/",$value, $matches2);
					$first_part = $matches2[1][0];
					$second_part = $matches2[3][0];
					switch ($first_part)
					{
						case 'user' :
							//we retrieve the asked user email
							if (!is_object($this->account))
							{
								$this->account = Factory::getInstance('accounts');
							}
							$ask_user = $this->account->name2id($second_part);
							$matches[1][$key] = $this->account->id2name($ask_user, 'account_email');
							break;
						case 'property' :
							//we take the content of the given property on the instance
							$matches[1][$key] = $this->instance->get($second_part);
							break;
						case 'role' :
							//all user mapped to this role
							if (!is_object($this->role_manager))
							{
								$this->role_manager = Factory::getInstance('workflow_rolemanager');
							}
							if (!is_object($this->account))
							{
								$this->account = Factory::getInstance('accounts');
							}
							$my_subset = array('wf_role_name' => $second_part);
							$listing =& $this->role_manager->list_mapped_users($this->instance->getProcessId(),true, $my_subset);
							$matches[1][$key] = '';
							foreach ($listing as $user_id => $user_name)
							{
								$user_email = $this->account->id2name($user_id);
								if ($matches[1][$key] == '')
								{
									$matches[1][$key] = $this->account->id2name($user_id, 'account_email');
								}
								else
								{
									$matches[1][$key] .= ', '.$this->account->id2name($user_id, 'account_email');
								}
							}
							break;
						case 'link' :
							//we want a link
							//the HTML characters are escaped, so we need this function
							//and we now some usefull links:
							//$second_part should be in this form link adress|text
							$matches3 = Array();
							//echo "<br>3rd analysis on ".$second_part;
							preg_match_all("/([^\|]+)([\|])([A-z0-9 \'\&\;]*)/",$second_part, $matches3);
							$link_part = $matches3[1][0];
							$text_part = $matches3[3][0];
							//need something in the text
							if (empty($text_part)) $text_part=$link_part;
							//and something in the link
							switch ($link_part)
							{
								default:
									//now it can be an external or local link
									if (substr($link_part,0,7)=='http://')
									{//external link
										$my_link = $link_part;
									}
									else
									{//local link
										$my_link = $this->conf['mail_smtp_local_link_prefix'].$GLOBALS['phpgw']->link($link_part);
									}
							}
							$matches[1][$key] = '<a href="'.$my_link.'">'.$text_part.'</a>';
							break;
						
						default:
							$matches[1][$key] = '';
					}
			}
			$final = str_replace($matches[0][$key],$matches[1][$key],$final);
		}
		//now get back the % escaped before the analysis
		$final = str_replace('&workflowpourcent;','%',$final);
		return $final;
	}
	
	/**
	 * This function is used to send mail
	 * @return bool true ok false error
	 * @access public
	 */
	function Send()
	{
		//$this->mail->SMTPDebug = 10;
		if (!($this->debugmode))
		{
			if(!$this->mail->Send())
			{
				if ($this->mail->ErrorInfo != $this->mail->Lang("data_not_accepted"))
				{
					$this->error[] = $this->mail->ErrorInfo;
					return false;
				}
			}
		}
		else
		{
			//_debug_array($this->mail);
			$this->error[] = 'DEBUG mode: '.lang('if not in debug mail_smtp agent would have sent this email:');
			$this->error[] = 'DEBUG mode: Host:'.$this->mail->Host;
			$this->error[] = 'DEBUG mode: Port:'.$this->mail->Port;
			$this->error[] = 'DEBUG mode: From:'.htmlentities($this->mail->From);
			$this->error[] = 'DEBUG mode: FromName:'.htmlentities($this->mail->FromName);
			$msg = 'DEBUG mode: ReplyTo:';
			foreach ($this->mail->ReplyTo as $address)
			{
				$msg .= htmlentities($address[0]);
			}
			$this->error[] = $msg;
			$msg = 'DEBUG mode: To:';
			foreach ($this->mail->to as $address)
			{
				$msg .= htmlentities($address[0]);
			}
			$this->error[] = $msg;
			$msg = 'DEBUG mode: Cc:';
			foreach ($this->mail->cc as $address)
			{
				$msg .= htmlentities($address[0]);
			}
			$this->error[] = $msg;
			$msg = 'DEBUG mode: Bcc:';
			foreach ($this->mail->bcc as $address)
			{
				$msg .= ' '.htmlentities($address[0]);
			}
			$this->error[] = $msg;
			$this->error[] = 'DEBUG mode: Subject:'.htmlentities($this->mail->Subject);
			//$this->error[] = 'DEBUG mode: AltBody:'.htmlentities($this->mail->AltBody);
			$this->error[] = 'DEBUG mode: Body (hmtl):'.$this->mail->Body;
		}
		return true;
	}
	
}
?>
