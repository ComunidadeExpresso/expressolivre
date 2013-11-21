<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'ProcessManager'.SEP.'BaseManager.php');
/**
 * Adds, removes, modifies and lists processes.
 * Most of the methods acts directly in database level, bypassing Project object methods
 *
 * @todo Fix multiple non checked fopen ==> infinite loops in case of problems with filesystem
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class ProcessManager extends BaseManager {

	/**
	 * @var resource $parser xml parser
	 * @access public
	 */
	var $parser;

	/**
	 * @var array $tree data struture
	 * @access public
	 */
	var $tree;

	/**
	 * @var $current current element
	 * @access public
	 */
	var $current;

	/**
	 * @var $buffer buffer for data
	 * @access public
	 */
	var $buffer;

	/**
	 * @var object $Process Process
	 * @access public
	 */
	var $Process;

	/**
	 * @var object $activity_manager Activity Manager
	 * @access public
	 */
	var $activity_manager;

	/**
	 * @var object $jobManager Job Manager object
	 * @access public
	 */
	var $jobManager;

	/**
	 * @var object $role_manager Role Manager
	 * @access public
	 */
	var $role_manager;

	/**
	 * Constructor
	 *
	 * @param object &$db ADOdb
	 * @return object ProcessManager
	 * @access public
	 */
	function ProcessManager()
	{
		parent::BaseManager();
		$this->child_name = 'ProcessManager';
		// $this->activity_manager is not set here to avoid objects loading object A loading object B loading object A, etc
		//$this->role_manager will only be loaded when needed as well
	}

	/**
	 * Collect errors from all linked objects which could have been used by this object.
	 * Each child class should instantiate this function with her linked objetcs, calling get_error(true)
	 *
	 * @param bool $debug False by default, if true debug messages can be added to 'normal' messages
	 * @param string $prefix Appended to the debug message
	 * @return void
	 * @access public
	 */
	function collect_errors($debug=false, $prefix = '')
	{
		parent::collect_errors($debug, $prefix);
		if (isset($this->activity_manager)) $this->error[] = $this->activity_manager->get_error(false, $debug, $prefix);
		if (isset($this->role_manager)) $this->error[] = $this->role_manager->get_error(false, $debug, $prefix);
	}

	/**
	 * Activates a process
	 *
	 * @param int $pId Process id
	 * @return void
	 * @access public
	 */
	function activate_process($pId)
	{
		$query = 'update '.GALAXIA_TABLE_PREFIX.'processes set wf_is_active=? where wf_p_id=?';
		$this->query($query, array('y',$pId));
		$msg = sprintf(tra('Process %d has been activated'),$pId);
		$this->error[] = $msg;
	}

	/**
	 * Deactivates a process
	 *
	 * @param int $pId Process id
	 * @return void
	 * @access public
	 */
	function deactivate_process($pId)
	{
		$query = 'update '.GALAXIA_TABLE_PREFIX.'processes set wf_is_active=? where wf_p_id=?';
		$this->query($query, array('n',$pId));
		$msg = sprintf(tra('Process %d has been deactivated'),$pId);
		$this->error[] = $msg;
	}

	/**
	 * Creates an XML representation of a process
	 *
	 * @param int $pId Process id
	 * @return string
	 * @access public
	 */
	function serialize_process($pId)
	{
		if (!(isset($this->activity_manager)))  $this->activity_manager = &Factory::newInstance('ActivityManager');
		// <process>
		$out = '<process>'."\n";
		//we retrieve config values with the others process data
		$proc_info =& $this->get_process($pId, true);
		$wf_procname = $proc_info['wf_normalized_name'];
		$out.= '  <name>'.htmlspecialchars($proc_info['wf_name']).'</name>'."\n";
		$out.= '  <isValid>'.htmlspecialchars($proc_info['wf_is_valid']).'</isValid>'."\n";
		$out.= '  <version>'.htmlspecialchars($proc_info['wf_version']).'</version>'."\n";
		$out.= '  <isActive>'.htmlspecialchars($proc_info['wf_is_active']).'</isActive>'."\n";
		$out.='   <description>'.htmlspecialchars($proc_info['wf_description']).'</description>'."\n";
		$out.= '  <lastModif>'.date("d/m/Y [h:i:s]",$proc_info['wf_last_modif']).'</lastModif>'."\n";

		//Shared code
		$out.= '  <sharedCode><![CDATA[';
		$fp=fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."shared.php","r");
		while(!feof($fp)) {
			$line=fread($fp,8192);
			$out.=$line;
		}
		fclose($fp);
		$out.= '  ]]></sharedCode>'."\n";

		//Loop on config values
		$out.='  <configs>'."\n";
		foreach($proc_info['config'] as $res) {
			$name = $res['wf_config_name'];
			$value_int = $res['wf_config_value_int'];
			$value = $res['wf_config_value'];
			$out.='    <config>'."\n";
			$out.='      <wf_config_name>'.htmlspecialchars($name).'</wf_config_name>'."\n";
			$out.='      <wf_config_value>'.htmlspecialchars($value).'</wf_config_value>'."\n";
			$out.='      <wf_config_value_int>'.htmlspecialchars($value_int).'</wf_config_value_int>'."\n";
			$out.='    </config>'."\n";
		}
		$out.='  </configs>'."\n";

		// Now loop over activities
		$query = "select * from ".GALAXIA_TABLE_PREFIX."activities where wf_p_id=$pId";
		$result = $this->query($query);
		$out.='  <activities>'."\n";
		while($res = $result->fetchRow()) {
			$name = $res['wf_normalized_name'];
			$out.='    <activity>'."\n";
			$out.='      <name>'.htmlspecialchars($res['wf_name']).'</name>'."\n";
			$out.='      <type>'.htmlspecialchars($res['wf_type']).'</type>'."\n";
			$out.='      <description>'.htmlspecialchars($res['wf_description']).'</description>'."\n";
			$out.='      <lastModif>'.date("d/m/Y [h:i:s]",$res['wf_last_modif']).'</lastModif>'."\n";
			$out.='      <isInteractive>'.$res['wf_is_interactive'].'</isInteractive>'."\n";
			$out.='      <isAutoRouted>'.$res['wf_is_autorouted'].'</isAutoRouted>'."\n";
			$out.='      <roles>'."\n";
			//loop on activity roles
			$actid = $res['wf_activity_id'];
			$roles =& $this->activity_manager->get_activity_roles($actid);
			foreach($roles as $role) {
				if ($role['wf_readonly'])
				{
					$out.='        <role readonly="true">'.htmlspecialchars($role['wf_name']).'</role>'."\n";
				}
				else
				{
					$out.='        <role>'.htmlspecialchars($role['wf_name']).'</role>'."\n";
				}
			}
			$out.='      </roles>'."\n";
			$out.='      <agents>'."\n";
			//loop on activity agents
			$agents =& $this->activity_manager->get_activity_agents($actid);
			foreach($agents as $agent) {
				$out.='        <agent>'."\n";
				$out.='           <agent_type>'.htmlspecialchars($agent['wf_agent_type']).'</agent_type>'."\n";
				//loop on agent datas
				$agent_data =& $this->activity_manager->get_activity_agent_data($actid,$agent['wf_agent_type']);
				$out.='           <agent_datas>'."\n";
				foreach($agent_data as $key => $value)
				{
					if (!($key=='wf_agent_id'))
					{
						$out.='               <agent_data>'."\n";
						$out.='                   <name>'.htmlspecialchars($key).'</name>'."\n";
						$out.='                   <value>'.htmlspecialchars($value).'</value>'."\n";
						$out.='               </agent_data>'."\n";
					}
				}
				$out.='           </agent_datas>'."\n";
				$out.='        </agent>'."\n";
			}
			$out.='      </agents>'."\n";

			//the code
			$out.='      <code><![CDATA[';
			$fp=fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."activities".SEP."$name.php","r");
			while(!feof($fp)) {
				$line=fread($fp,8192);
				$out.=$line;
			}
			fclose($fp);
			$out.='      ]]></code>';
			if($res['wf_is_interactive']=='y') {
				$out.='      <template><![CDATA[';
				$fp=fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."templates".SEP."$name.tpl","r");
				while(!feof($fp)) {
					$line=fread($fp,8192);
					$out.=$line;
				}
				fclose($fp);
				$out.='      ]]></template>';
			}
			$out.='    </activity>'."\n";
		}
		$out.='  </activities>'."\n";
		$out.='  <transitions>'."\n";
		//loop on transitions
		$transitions = $this->activity_manager->get_process_transitions($pId);
		foreach($transitions as $tran) {
			$out.='     <transition>'."\n";
			$out.='       <from>'.htmlspecialchars($tran['wf_act_from_name']).'</from>'."\n";
			$out.='       <to>'.htmlspecialchars($tran['wf_act_to_name']).'</to>'."\n";
			$out.='     </transition>'."\n";
		}
		$out.='  </transitions>'."\n";
		$out.= '</process>'."\n";
		//$fp = fopen(GALAXIA_PROCESSES."/$wf_procname/$wf_procname.xml","w");
		//fwrite($fp,$out);
		//fclose($fp);
		return $out;
	}

	/**
	 * Creates  a process PHP data structure from its XML representation
	 *
	 * @param string &$xml XML document
	 * @return array Process data structure
	 * @access public
	 */
	function unserialize_process(&$xml)
	{
		// Create SAX parser assign this object as base for handlers
		// handlers are private methods defined below.
		// keep contexts and parse
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
		//xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE, 1);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, '_start_element_handler', '_end_element_handler');
		xml_set_character_data_handler($this->parser, '_data_handler');
		$aux=Array(
			'name'=>'root',
			'children'=>Array(),
			'parent' => 0,
			'data'=>'',
			'attribs'	=> Array(),
		);
		$this->tree[0]=$aux;
		$this->current=0;


		if (!xml_parse($this->parser, $xml, true)) {
			$error = sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->parser)),
				xml_get_current_line_number($this->parser));
			trigger_error($error,E_USER_WARNING);
			$this->error[] = $error;
		}
		xml_parser_free($this->parser);
		// Now that we have the tree we can do interesting things

		$process=Array();
		$activities=Array();
		$transitions=Array();
        $tree_count = count($this->tree[1]['children']);
		for($i=0;$i<$tree_count;++$i) {
			// Process attributes
			$z=$this->tree[1]['children'][$i];
			$name = trim($this->tree[$z]['name']);

			//config values
			if ($name=='configs') {
                $tree_z_count = count($this->tree[$z]['children']);
				for($j=0;$j<$tree_z_count;++$j) {
					$z2 = $this->tree[$z]['children'][$j];
					// this is a config $name = $this->tree[$z2]['name'];
					$aux = Array();
					if($this->tree[$z2]['name']=='config') {
                        $tree_z2_count = count($this->tree[$z2]['children']);
						for($k=0;$k<$tree_z2_count;++$k) {
							$z3 = $this->tree[$z2]['children'][$k];
							$name = trim($this->tree[$z3]['name']);
							$value= trim($this->tree[$z3]['data']);
							$aux[$name]=$value;
						}
						$configs[]=$aux;
					}
				}
			}
			//activities
			elseif($name=='activities') {
                $tree_z_count = count($this->tree[$z]['children']);
				for($j=0;$j<$tree_z_count;++$j) {
					$z2 = $this->tree[$z]['children'][$j];
					// this is an activity $name = $this->tree[$z2]['name'];
					$aux = Array();
					if($this->tree[$z2]['name']=='activity') {
                        $tree_z2_count = count($this->tree[$z2]['children']);
						for($k=0;$k<$tree_z2_count;++$k) {
							$z3 = $this->curre[$z2]['children'][$k];
							$name = trim($this->tree[$z3]['name']);
							$value= trim($this->tree[$z3]['data']);
							if($name=='roles') {
								$roles=Array();
                                $tree_z3_count = count($this->tree[$z3]['children']);
								for($l=0;$l<$tree_z3_count;++$l) {
									$z4 = $this->tree[$z3]['children'][$l];
									$name = trim($this->tree[$z4]['name']);
									$data = trim($this->tree[$z4]['data']);
									$attribs = $this->tree[$z4]['attribs'];
									$readonly = false;
									if ( (isset($attribs['readonly'])) && ($attribs['readonly']))
									{
										//role in read-only
										$readonly = true;
									}
									$roles[]=array(
										'name' 	=> $data,
										'readonly'	=> $readonly,
									);
								}
							}
							elseif ($name=='agents')
							{
								$agents=Array();
                                $tree_z3_count = count($this->tree[$z3]['children']);
								for($l=0;$l<$tree_z3_count;$l++)
								{
									$z4 = $this->tree[$z3]['children'][$l];
									//$name is agent
									$name = trim($this->tree[$z4]['name']);
									if ($name = 'agent')
									{
										$agent = array();
                                        $tree_z4_count = count($this->tree[$z4]['children']);
										for($m=0;$m<$tree_z4_count;++$m)
										{
											$z5 = $this->tree[$z4]['children'][$m];
											//$name is agent_type or agent_datas
											$name = trim($this->tree[$z5]['name']);
											// data will be the agent_type or an array for agent_datas
											$data = trim($this->tree[$z5]['data']);
											if ($name=='agent_type')
											{
												$agent['wf_agent_type']=$data;
											}
											elseif ($name=='agent_datas')
											{
                                                $tree_z5_count = count($this->tree[$z5]['children']);
												for($n=0;$n<$tree_z5_count;++$n)
												{
													$z6 = $this->tree[$z5]['children'][$n];
													//$name is agent_data $val is an array
													$name = trim($this->tree[$z6]['name']);
													$val = trim($this->tree[$z6]['data']);
													if ($name=='agent_data')
													{
                                                        $tree_z6_count = count($this->tree[$z6]['children']);
														for($o=0;$o<$tree_z6_count;++$o)
														{
															$z7 = $this->tree[$z6]['children'][$o];
															//$name is agent_data $val is 'name' or 'value'
															$name = trim($this->tree[$z7]['name']);
															$content = trim($this->tree[$z7]['data']);
															//echo "<br>z7 name $name content: $content";
															if ($name=='name')
															{
																$agent_data_name = $content;
															}
															elseif ($name=='value')
															{
																$agent_data_value =& $content;
															}
														}
														//echo "<br>associate $agent_data_name to $agent_data_value <hr>";
														$agent[$agent_data_name] = $agent_data_value;
													}
												}
											}
										}
										$agents[]=$agent;
									}
								}
							} else {
								$aux[$name]=$value;
								//print("$name:$value<br/>");
							}
						}
						$aux['agents']=$agents;
						$aux['roles']=$roles;
						$activities[]=$aux;
					}
				}
			} elseif($name=='transitions') {
                $tree_z_count = count($this->tree[$z]['children']);
				for($j=0;$j<$tree_z_count;++$j) {
					$z2 = $this->tree[$z]['children'][$j];
					// this is an activity $name = $this->tree[$z2]['name'];
					$aux=Array();
					if($this->tree[$z2]['name']=='transition') {
                        $tree_z2_count = count($this->tree[$z2]['children']);
						for($k=0;$k<$tree_z2_count;++$k) {
							$z3 = $this->tree[$z2]['children'][$k];
							$name = trim($this->tree[$z3]['name']);
							$value= trim($this->tree[$z3]['data']);
							if($name == 'from' || $name == 'to') {
								$aux[$name]=$value;
							}
						}
					}
					$transitions[] = $aux;
				}
			} else {
				$value = trim($this->tree[$z]['data']);
				//print("$name is $value<br/>");
				$process[$name]=$value;
			}
		}
		$process['configs']=$configs;
		$process['activities']=$activities;
		$process['transitions']=$transitions;
		return $process;
	}

	/**
	 * Creates a process from the process data structure, if you want to convert an XML to a process then use first unserialize_process and then this method.
	 *
	 * @access public
	 * @param string &$data
	 * @return bool
	 */
	function import_process(&$data)
	{
		//Now the show begins
		if (!(isset($this->activity_manager)))  $this->activity_manager = &Factory::newInstance('ActivityManager');
		if (!(isset($this->role_manager))) $this->role_manager = &Factory::newInstance('RoleManager');
		if (!isset($this->jobManager))
			$this->jobManager = &Factory::newInstance('JobManager');

		// First create the process. Always inactive and inactive first.
		$vars = Array(
			'wf_name' => trim($data['name']),
			'wf_version' => $data['version'],
			'wf_description' => $data['description'],
			'wf_last_modif' => $data['lastModif'],
			'wf_is_active' => false,
			'wf_is_valid' => false,
			'config' => $data['configs'],
		);

		if (empty($vars['wf_name']))
		{
			$msg = tra('invalid name specified');
			$this->error[] = $msg;
			return false;
		}

		if (preg_match('/^[0-9]{1,2}\.[0-9]{1,2}$/', $vars['wf_version']) === false)
		{
			$msg = tra('invalid version specified');
			$this->error[] = $msg;
			return false;
		}

		if ($this->process_name_exists($vars['wf_name'], $vars['wf_version']))
		{
			$msg = sprintf(tra('Process %s %s already exists, the import process was aborted'),$vars['wf_name'],$vars['wf_version']);
			$this->error[] = $msg;
			return false;
		}
		$pid = $this->replace_process(0,$vars,false);
		//Put the shared code
		$proc_info = $this->get_process($pid);
		$wf_procname = $proc_info['wf_normalized_name'];
		$fp = fopen(GALAXIA_PROCESSES.SEP.$wf_procname.SEP.'code'.SEP.'shared.php',"w");
		fwrite($fp, $data['sharedCode']);
		fclose($fp);
		$actids = Array();

		// Foreach activity create activities
		foreach($data['activities'] as $activity) {

			$vars = Array(
				'wf_name' => $activity['name'],
				'wf_description' => $activity['description'],
				'wf_type' => $activity['type'],
				'wf_menu_path' => $activity['menuPath'],
				'wf_last_modif' => $activity['lastModif'],
				'wf_is_interactive' => $activity['isInteractive'],
				'wf_is_autorouted' => $activity['isAutoRouted']
			);
			$actname=$this->activity_manager->_normalize_name($activity['name']);
			$actid = $this->activity_manager->replace_activity($pid,0,$vars);

			$fp = fopen(GALAXIA_PROCESSES.SEP.$wf_procname.SEP.'code'.SEP.'activities'.SEP.$actname.'.php',"w");
			fwrite($fp, $activity['code']);
			fclose($fp);
			if($activity['isInteractive']=='y') {
				$fp = fopen(GALAXIA_PROCESSES.SEP.$wf_procname.SEP.'code'.SEP.'templates'.SEP.$actname.'.tpl',"w");
				fwrite($fp,$activity['template']);
				fclose($fp);
			}
			$actids[$activity['name']] = $this->activity_manager->_get_activity_id_by_name($pid, $activity['name']);
			$actname = $this->activity_manager->_normalize_name($activity['name']);
			$now = date("U");
			//roles
			if( is_array($activity['roles']) && count($activity['roles']) > 0 )
			{
				foreach($activity['roles'] as $role)
				{
					$rolename = $role['name'];
					$vars = Array(
						'wf_name' => $rolename,
						'wf_description' => $rolename,
						'wf_last_modif' => $now,
					);
					if(!$this->role_manager->role_name_exists($pid,$rolename)) {
						$rid=$this->role_manager->replace_role($pid,0,$vars);
					} else {
						$rid = $this->role_manager->get_role_id($pid,$rolename);
					}
					if($actid && $rid) {
						$this->activity_manager->add_activity_role($actid,$rid,$role['readonly']);
					}
				}
			}
			//agents
			if( is_array($activity['agents']) && count($activity['agents']) > 0 )
			{
				foreach($activity['agents'] as $agent)
				{
					if (empty($agent['wf_agent_type']))
					{
						$this->error[] = lang('empty agent type');
					}
					else
					{
						//create a new agent of the same type for the new activity
						$agentid = $this->activity_manager->add_activity_agent($actid,$agent['wf_agent_type']);
						//save values of this new agent
						$bindvars = Array();
						$query = 'update '.GALAXIA_TABLE_PREFIX.'agent_'.$agent['wf_agent_type'].'
							set ';
						//we wont need the old type anymore
						unset($agent['wf_agent_type']);
						$countfields = 0;
						foreach ($agent as $key => $value)
						{
							if ($key)
							{
								++$countfields;
								$query .= "$key = ? ,";
								$bindvars[] = $value;
							}
						}
						$query = substr($query,'0',-1);
						$query .= ' where wf_agent_id = ?';
						$bindvars[] = $agentid;
						if ($countfields) $this->query($query, $bindvars);
					}
				}
			}
		}
		//transitions
		foreach($data['transitions'] as $tran)
		{
			$this->activity_manager->add_transition($pid,$actids[$tran['from']],$actids[$tran['to']]);
		}

		foreach ($data['jobs'] as $job)
		{
			$this->jobManager->replaceJob($pid, 0, $job);
		}

		// create a graph for the new process
		$this->activity_manager->build_process_graph($pid);
		//Test the final process
		$this->activity_manager->validate_process_activities($pid);

		$msg = sprintf(tra('Process %s %s imported'),$proc_info['wf_name'],$proc_info['wf_version']);
		$this->error[] = $msg;
		return true;
	}

	/**
	 * Creates a new process based on an existing process changing the process version.
	 * By default the process is created as an unactive process and the version is by default a minor version of the process
	 *
	 * @param int $pId Process id
	 * @param bool $minor Process previous version
	 * @return int Process id
	 * @access public
	 */
	function new_process_version($pId, $minor=true)
	{
		if (!(isset($this->activity_manager)))  $this->activity_manager = &Factory::newInstance('ActivityManager');
		$oldpid = $pId;
		//retrieve process info with config rows
		$proc_info = $this->get_process($pId, true);
		if(!($proc_info) || (count($proc_info)==0)) return false;
		$name = $proc_info['wf_name'];

		// Now update the version
		$version = $this->_new_version($proc_info['wf_version'],$minor);
		while($this->getOne('select count(*) from '.GALAXIA_TABLE_PREFIX.'processes where wf_name=? and wf_version=?',array($name,$version)))
		{
			$version = $this->_new_version($version,$minor);
		}
		$oldname = $proc_info['wf_normalized_name'];

		// Make new versions unactive
		$proc_info['wf_version'] = $version;
		$proc_info['wf_is_active'] = 'n';
		// create a new process, but don't create start/end activities
		$pid = $this->replace_process(0, $proc_info, false);
		if (!pid) return false;

		//Since we are copying a process we should copy
		//the old directory structure to the new directory
		//oldname was saved a few lines before
		$newname = $this->_get_normalized_name($pid);
		$this->_rec_copy(GALAXIA_PROCESSES.SEP.$oldname.SEP.'code',GALAXIA_PROCESSES.SEP.$newname.SEP.'code');
		// And here copy all the activities & so
		$query = 'select * from '.GALAXIA_TABLE_PREFIX.'activities where wf_p_id=?';
		$result = $this->query($query, array($oldpid));
		$newaid = array();
		while($res = $result->fetchRow()) {
			$oldaid = $res['wf_activity_id'];
			// the false tell the am not to create activities source files
			$newaid[$oldaid] = $this->activity_manager->replace_activity($pid,0,$res, false);
		}
		// create transitions
		$query = 'select * from '.GALAXIA_TABLE_PREFIX.'transitions where wf_p_id=?';
		$result = $this->query($query, array($oldpid));

		/* create the jobs */
		$query = "INSERT INTO " . GALAXIA_TABLE_PREFIX . "jobs (wf_process_id, name, description, time_start, interval_value, interval_unity, date_type, week_days, month_offset, active) (SELECT {$pid}, name, description, time_start, interval_value, interval_unity, date_type, week_days, month_offset, FALSE FROM " . GALAXIA_TABLE_PREFIX . "jobs WHERE wf_process_id = ?)";
		$this->query($query, array($oldpid));

		while($res = $result->fetchRow()) {
			if (empty($newaid[$res['wf_act_from_id']]) || empty($newaid[$res['wf_act_to_id']])) {
				continue;
			}
			$this->activity_manager->add_transition($pid,$newaid[$res['wf_act_from_id']],$newaid[$res['wf_act_to_id']]);
		}
		// create roles
		if (!(isset($this->role_manager))) $this->role_manager = &Factory::newInstance('RoleManager');
		$query = 'select * from '.GALAXIA_TABLE_PREFIX.'roles where wf_p_id=?';
		$result = $this->query($query, array($oldpid));
		$newrid = array();
		while($res = $result->fetchRow()) {
			if(!$this->role_manager->role_name_exists($pid,$res['wf_name'])) {
				$rid=$this->role_manager->replace_role($pid,0,$res);
			} else {
				$rid = $this->role_manager->get_role_id($pid,$res['wf_name']);
			}
			$newrid[$res['wf_role_id']] = $rid;
		}
		// map users to roles
		if (count($newrid) > 0) {
			$query = 'select * from '.GALAXIA_TABLE_PREFIX.'user_roles where wf_p_id=?';
			$result = $this->query($query, array($oldpid));
			while($res = $result->fetchRow()) {
				if (empty($newrid[$res['wf_role_id']])) {
					continue;
				}
				$this->role_manager->map_user_to_role($pid,$res['wf_user'],$newrid[$res['wf_role_id']], $res['wf_account_type']);
			}
		}
		// add roles to activities
		if (count($newaid) > 0 && count($newrid ) > 0) {
			$query = 'select * from '.GALAXIA_TABLE_PREFIX.'activity_roles where wf_activity_id in (' . join(', ',array_keys($newaid)) . ')';
			$result = $this->query($query);
			while($res = $result->fetchRow()) {
				if (empty($newaid[$res['wf_activity_id']]) || empty($newrid[$res['wf_role_id']])) {
					continue;
				}
				$this->activity_manager->add_activity_role($newaid[$res['wf_activity_id']],$newrid[$res['wf_role_id']], $res['wf_readonly']);
			}
		}

		//create agents
		//get the list of agents used by the old process
		$query = 'select gaa.* from '.GALAXIA_TABLE_PREFIX.'activity_agents gaa
			INNER JOIN '.GALAXIA_TABLE_PREFIX.'activities gac ON gaa.wf_activity_id = gac.wf_activity_id
			where gac.wf_p_id=?';
		$result = $this->query($query, array($oldpid));
		if (!(empty($result)))
		{
			while ($res = $result->fetchRow())
			{
				//create a new agent of the same type for the new activity
				$agentid = $this->activity_manager->add_activity_agent($newaid[$res['wf_activity_id']],$res['wf_agent_type']);
				//save values of this new agents, taking the old ones, we make a simple copy
				$old_activity_agent_data =& $this->activity_manager->get_activity_agent_data($res['wf_activity_id'],$res['wf_agent_type']);
				//we wont need the old id and type
				unset($old_activity_agent_data['wf_agent_id']);
				unset($old_activity_agent_data['wf_agent_type']);
				$bindvars = Array();
				$query = 'update '.GALAXIA_TABLE_PREFIX.'agent_'.$res['wf_agent_type'].'
					set ';
				$countfields = 0;
				foreach ($old_activity_agent_data as $key => $value)
				{
					if ($key)
					{
						++$countfields;
						$query .= "$key = ? ,";
						$bindvars[] = $value;
					}
				}
				$query = substr($query,'0',-1);
				$query .= ' where wf_agent_id = ?';
				$bindvars[] = $agentid;
				if ($countfields) $this->query($query, $bindvars);
			}
		}

		// create a graph for the new process
		$this->activity_manager->build_process_graph($pid);

		return $pid;
	}

	/**
	 * This function can be used to check if a process name exists, note that this is NOT used by replace_process since that function can be used to
	 * create new versions of an existing process. The application must use this method to ensure that processes have unique names.
	 *
	 * @param string $name Process name
	 * @param string $version Process version
	 * @return bool
	 * @access public
	 */
	function process_name_exists($name,$version)
	{
		$name = addslashes($this->_normalize_name($name,$version));
		return $this->getOne('select count(*) from '.GALAXIA_TABLE_PREFIX.'processes where wf_normalized_name=?',array($name));
	}


	/**
	 * Gets a process by pId. Fields are returned as an associative array.
	 * If withConfig is set (false by default), the configuration options are returned as well the ['config'] key is then an array containing the config data with type distinction
	 *
	 * @param int $pId Process id
	 * @param bool $withConfig Configuration options
	 * @return bool
	 * @access public
	 */
	function get_process($pId, $withConfig=false)
	{
		$query = 'select * from '.GALAXIA_TABLE_PREFIX.'processes where wf_p_id=?';
		$result = $this->query($query, array($pId));
		if((empty($result)) || (!$result->numRows())) return false;
		$res = $result->fetchRow();
		if ($withConfig)
		{
			// by setting true we force this function to keep type distinction on config values
			$res['config'] = $this->getConfigValues($res['wf_p_id'], true);
		}
		return $res;
	}

	/**
	 * Lists all processes
	 *
	 * @param int $offset Resultset starting row
	 * @param int	$maxRecords Max number of resulting rows
	 * @param string $sort_mode Sorting mode
	 * @param string $find Search query string
	 * @param string $where Condition query string
	 * @return bool
	 * @access public
	 */
	function list_processes($offset,$maxRecords,$sort_mode,$find='',$where='')
	{
		if(!empty($sort_mode))
			$sort_mode = $this->convert_sortmode($sort_mode);
		if($find) {
			$findesc = '%'.$find.'%';
			$mid=' where ((wf_name like ?) or (wf_description like ?))';
			$bindvars = array($findesc,$findesc);
		} else {
			$mid='';
			$bindvars = array();
		}
		if($where) {
			if($mid) {
				$mid.= " and ($where) ";
			} else {
				$mid.= " where ($where) ";
			}
		}
		$query = 'select * from '.GALAXIA_TABLE_PREFIX."processes $mid";
		$query_cant = 'select count(*) from '.GALAXIA_TABLE_PREFIX."processes $mid";
		$result = $this->query($query,$bindvars,$maxRecords,$offset, true, $sort_mode);
		$cant = $this->getOne($query_cant,$bindvars);
		$ret = Array();
		if (isset($result))
		{
			while($res = $result->fetchRow())
			{
				$ret[] = $res;
			}
		}
		$retval = Array();
		$retval['data'] = $ret;
		$retval['cant'] = $cant;
		return $retval;
	}

  /*!
   Marks a process as an invalid process
   */
	function invalidate_process($pid)
	{
		$query = 'update '.GALAXIA_TABLE_PREFIX.'processes set wf_is_valid=? where wf_p_id=?';
		$this->query($query, array('n',$pid));
	}

	/**
	 * Removes a process by pId
	 *
	 * @param int $pId Process id
	 * @return bool
	 * @access public
	 */
	function remove_process($pId)
	{
		if (!(isset($this->activity_manager)))  $this->activity_manager = &Factory::newInstance('ActivityManager');
		if (!isset($this->jobManager))
			$this->jobManager = &Factory::newInstance('JobManager');
		$this->deactivate_process($pId);
		$name = $this->_get_normalized_name($pId);

		// start a transaction
		$this->db->StartTrans();
		$this->jobManager->removeJobsByProcessID($pId);

		// Remove process activities
		$query = 'select wf_activity_id from '.GALAXIA_TABLE_PREFIX.'activities where wf_p_id=?';
		$result = $this->query($query, array($pId));
		while($res = $result->fetchRow()) {
			//we add a false parameter to prevent the ActivityManager from opening a new transaction
			$this->activity_manager->remove_activity($pId,$res['wf_activity_id'], false);
		}

		// Remove process roles
		$query = 'delete from '.GALAXIA_TABLE_PREFIX.'roles where wf_p_id=?';
		$this->query($query, array($pId));
		$query = 'delete from '.GALAXIA_TABLE_PREFIX.'user_roles where wf_p_id=?';
		$this->query($query, array($pId));

		// Remove process instances
		$query = 'delete from '.GALAXIA_TABLE_PREFIX.'instances where wf_p_id=?';
		$this->query($query, array($pId));

		// Remove the directory structure
		if (!empty($name) && is_dir(GALAXIA_PROCESSES.SEP.$name)) {
			$this->_remove_directory(GALAXIA_PROCESSES.SEP.$name,true);
		}
		if (GALAXIA_TEMPLATES && !empty($name) && is_dir(GALAXIA_TEMPLATES.SEP.$name)) {
			$this->_remove_directory(GALAXIA_TEMPLATES.SEP.$name,true);
		}

		// Remove configuration data
		$query = 'delete from '.GALAXIA_TABLE_PREFIX.'process_config where wf_p_id=?';
		$this->query($query, array($pId));

		// And finally remove the proc
		$query = 'delete from '.GALAXIA_TABLE_PREFIX.'processes where wf_p_id=?';
		$this->query($query, array($pId));
		$msg = sprintf(tra('Process %s removed'),$name);
		$this->error[] = $msg;

		// perform commit (return true) or Rollback (return false)
		return $this->db->CompleteTrans();

	}

	/**
	 * Updates or inserts a new process in the database, $vars is an associative array containing the fields to update or to insert as needed.
	 * Configuration options should be in an array associated with the 'config' key
	 * this config array should contain 'wf_config_name', 'wf_config_value' and 'wf_config_value_int' keys.
	 * $pId is the processI. If $pId is 0 then we create a new process, else we are in edit mode.
	 * if $create is true start and end activities will be created (when importing use $create=false)
	 *
	 * @param int $pId Process id, if 0 then we create a new process, else we are in edit mode
	 * @param array &$vars Associative containing the fields to update or to insert as needed
	 * @param bool $create If true, start and end activities will be created (when importing use $create=false).
	 * @return int Process id
	 * @access public
	 */
	function replace_process($pId, &$vars, $create = true)
	{
		if (!(isset($this->activity_manager)))  $this->activity_manager = &Factory::newInstance('ActivityManager');
		$TABLE_NAME = GALAXIA_TABLE_PREFIX.'processes';
		$now = date("U");
		$vars['wf_last_modif']=$now;
		$vars['wf_normalized_name'] = $this->_normalize_name($vars['wf_name'],$vars['wf_version']);
		$config_array = array();

		foreach($vars as $key=>$value)
		{
			if ($key=='config')
			{
				$config_array_init =& $value;
				// rebuild a nice config_array with type of config and value
				if( is_array($config_array_init) && count($config_array_init) > 0 )
				{
					foreach($config_array_init as $config)
					{
						if (isset($config['wf_config_value_int']) && (!($config['wf_config_value_int']=='')))
						{
							$config_array[$config['wf_config_name']] = array('int' => $config['wf_config_value_int']);
						}
						else
						{
							if (isset($config['wf_config_value']))
							{
								$config_array[$config['wf_config_name']] = array('text' => $config['wf_config_value']);
							}
						}
					}
				}
				//no need to keep it in the vars array, this array is used in queries
				unset($vars['config']);
			}
			else // not config, it's just process's fields values
			{
				$vars[$key]=addslashes($value);
			}
		}

		if($pId) {
			// update mode
			$old_proc = $this->get_process($pId);
			$first = true;
			$query ="update $TABLE_NAME set";
			foreach($vars as $key=>$value) {
				if(!$first) $query.= ',';
				if(!is_numeric($value)||strstr($value,'.')) $value="'".$value."'";
				$query.= " $key=$value ";
				$first = false;
			}
			$query .= " where wf_p_id=$pId ";
			$this->query($query);

			//set config values
			$this->setConfigValues($pId,$config_array);

			// Note that if the name is being changed then
			// the directory has to be renamed!
			$oldname = $old_proc['wf_normalized_name'];
			$newname = $vars['wf_normalized_name'];
			if ($newname != $oldname) {
				rename(GALAXIA_PROCESSES.SEP."$oldname",GALAXIA_PROCESSES.SEP."$newname");
			}
			$msg = sprintf(tra('Process %s has been updated'),$vars['wf_name']);
			$this->error[] = $msg;
		} else {
			unset($vars['wf_p_id']);
			// insert mode
			$name = $this->_normalize_name($vars['wf_name'],$vars['wf_version']);
			$this->_create_directory_structure($name);
			$first = true;
			$query = "insert into $TABLE_NAME(";
			foreach(array_keys($vars) as $key) {
				if(!$first) $query.= ',';
				$query.= "$key";
				$first = false;
			}
			$query .=") values(";
			$first = true;
			foreach(array_values($vars) as $value) {
				if(!$first) $query.= ',';
				if(!is_numeric($value)||strstr($value,'.')) $value="'".$value."'";
				$query.= "$value";
				$first = false;
			}
			$query .=")";
			$this->query($query);
			//FIXME: this query seems to be quite sure to get a result, I would prefer something
			// more sure to get the right result everytime
			$pId = $this->getOne("select max(wf_p_id) from $TABLE_NAME where wf_last_modif=$now");

			//set config values
			$this->setConfigValues($pId,$config_array);

			// Now automatically add a start and end activity
			// unless importing ($create = false)
			if($create) {
				$vars1 = Array(
					'wf_name' => 'start',
					'wf_description' => 'default start activity',
					'wf_type' => 'start',
					'wf_is_interactive' => 'y',
					'wf_is_autorouted' => 'y'
				);
				$vars2 = Array(
					'wf_name' => 'end',
					'wf_description' => 'default end activity',
					'wf_type' => 'end',
					'wf_is_interactive' => 'n',
					'wf_is_autorouted' => 'y'
				);

				$this->activity_manager->replace_activity($pId,0,$vars1);
				$this->activity_manager->replace_activity($pId,0,$vars2);
			}
			$msg = sprintf(tra('Process %s has been created'),$vars['wf_name']);
			$this->error[] = $msg;
		}
		// Get the id
		return $pId;
	}

	/**
	 * Gets the normalized name of a process by pid
	 *
	 * @param int $pId Process id
	 * @access private
	 * @return string
	 */
	function _get_normalized_name($pId)
	{
		$info = $this->get_process($pId);
		return $info['wf_normalized_name'];
	}

	/**
	 * Normalizes a process name
	 *
	 * @param string $name Process name to be normalized
	 * @param string $version Process version
	 * @access private
	 * @return string Process normalized name
	 */
	function _normalize_name($name, $version)
	{
		$name = $name.'_'.$version;
		$name = str_replace(" ","_",$name);
		$name = preg_replace("/[^0-9A-Za-z\_]/",'',$name);
		return $name;
	}

	/**
	 * Generates a new minor version number
	 *
	 * @param string $version Current process version
	 * @param bool $minor Generate minor version
	 * @access private
	 * @return string
	 */
	function _new_version($version,$minor=true)
	{
		$parts = explode('.',$version);
		if($minor) {
			$parts[count($parts)-1]++;
		} else {
			$parts[0]++;
            $parts_count = count($parts);
			for ($i = 1; $i < $parts_count; ++$i) {
				$parts[$i] = 0;
			}
		}
		return implode('.',$parts);
	}

	/**
	 * Creates directory structure for process
	 *
	 * @param string $name Dir name in process repository
	 * @access private
	 * @return bool
	 */
	function _create_directory_structure($name)
	{
		$path = GALAXIA_PROCESSES.SEP.$name;
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."resources";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."graph";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."code";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."code".SEP."activities";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."code".SEP."templates";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."code".SEP."jobs";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."smarty";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."smarty".SEP."cache";
		if (!file_exists($path)) mkdir($path,0770);
		$path = GALAXIA_PROCESSES.SEP.$name.SEP."smarty".SEP."compiled";
		if (!file_exists($path)) mkdir($path,0770);
		if (GALAXIA_TEMPLATES) {
			$path = GALAXIA_TEMPLATES.SEP.$name;
			if (!file_exists($path)) mkdir($path,0770);
		}
		// Create shared file
		$file = GALAXIA_PROCESSES.SEP.$name.SEP."code".SEP."shared.php";
		if (!file_exists($file))
		{
			$fp = fopen(GALAXIA_PROCESSES.SEP.$name.SEP."code".SEP."shared.php","w");
			if (!fp) return false;
			fwrite($fp,'<'.'?'.'php'."\n".'?'.'>');
			fclose($fp);
		}
	}

	/**
	 * Removes a directory recursively
	 *
	 * @param string $dir Dir name to be erased
	 * @param bool $rec Recursive mode
	 * @access private
	 * @return void
	 */
	function _remove_directory($dir,$rec=false)
	{
		// Prevent a disaster
		if(trim($dir) == SEP || trim($dir)=='.' || trim($dir)=='templates' || trim($dir)=='templates'.SEP) return false;
		$h = opendir($dir);
		while(($file = readdir($h)) != false) {
			if(is_file($dir.SEP.$file)) {
				@unlink($dir.SEP.$file);
			} else {
				if($rec && $file != '.' && $file != '..') {
					$this->_remove_directory($dir.SEP.$file, true);
				}
			}
		}
		closedir($h);
		@rmdir($dir);
		@unlink($dir);
	}

	/**
	 * Copies a directory recursively
	 *
	 * @param string $dir1 Dir name to be copied
	 * @param string $dir2 Generated destination dir
	 * @access private
	 * @return void
	 */
	function _rec_copy($dir1,$dir2)
	{
		@mkdir($dir2,0777);
		$h = opendir($dir1);
		while(($file = readdir($h)) !== false) {
			if(is_file($dir1.SEP.$file)) {
				copy($dir1.SEP.$file,$dir2.SEP.$file);
			} else {
				if($file != '.' && $file != '..') {
					$this->_rec_copy($dir1.SEP.$file, $dir2.SEP.$file);
				}
			}
		}
		closedir($h);
	}

	/**
	 * XML parser start element handler
	 *
	 * @param resource $parser Parser handle
	 * @param string $element XML tag
	 * @param array $attribs XML tag attributes
	 * @access private
	 * @return void
	 */
	function _start_element_handler($parser, $element, $attribs)
	{
		$aux=Array('name'=>$element,
			'data'=>'',
			'parent' => $this->current,
			'children'=>Array(),
			'attribs' => $attribs);

		$i = count($this->tree);
		$this->tree[$i] = $aux;

		$this->tree[$this->current]['children'][]=$i;
		$this->current=$i;
	}

	/**
	 * XML parser end element handler
	 *
	 * @param resource $parser Parser handle
	 * @param string $element XML tag
	 * @param array $attribs XML tag attributes
	 * @access private
	 * @return void
	 */
	function _end_element_handler($parser, $element)
	{
		//when a tag ends put text
		$this->tree[$this->current]['data']=$this->buffer;
		$this->buffer='';
		$this->current=$this->tree[$this->current]['parent'];
	}

	/**
	 * XML parser element data handler
	 *
	 * @param resource $parser Parser handle
	 * @param string $element XML tag
	 * @param string $data XML tag content
	 * @access private
	 * @return void
	 */
	function _data_handler($parser, $data)
	{
		$this->buffer .= $data;
	}

	/**
	 * This getConfigValues differs from the Process::getConfigValues because requires only process id.
	 * This method gets the items defined in process_config table for this process, in fact this admin function bypass
	 * the process behaviour and is just showing you the basic content of the table.
	 * All config items are returned as a function result.
	 *
	 * @param int $pId Process id
	 * @param bool $distinct_types If the distinct_type is set the returned array will follow the format:
	 * * 0	=>('wf_config_name'=> 'foo')
	 *  		=>('wf_config_value'=>'bar')
	 *  		=>('wf_config_vale_int'=>null)
	 * * 1 	=>('wf_config_name' => 'toto')
	 *  		=>('wf_config_value'=>'')
	 *  		=>('wf_config_vale_int'=>15)
	 * if set to false (default) the result array will be (note that this is the default result if having just the $pId):
	 * * 'foo'=>'bar'
	 * * 'toto'=>15
	 * @param bool $askProcessObject If the askProcessObject is set to true (false by default) then the ProcessManager will load a process
	 * object to run directly Process->getConfigValues($config_ask_array) this let you use this ProcessManager
	 * getConfigValues the same way you would use $process->getConfigValues, with initialisation of default values.
	 * you should then call this function this way: $conf_result=$pm->getConfigValues($pId,true,true,$my_conf_array)
	 * @param array $config_array
	 * @access public
	 * @return array
	 */
	function getConfigValues($pId, $distinct_types=false, $askProcessObject=false, $config_array=array())
	{
		if (!$askProcessObject)
		{
			$query = 'select * from '.GALAXIA_TABLE_PREFIX.'process_config where wf_p_id=?';
			$result = $this->query($query, array($pId));
			$result_array=array();
			while($res = $result->fetchRow())
			{
				if ( (!$distinct_types) )
				{// we want a simple array
					if ($res['wf_config_value_int']==null)
					{
						$result_array[$res['wf_config_name']] = $res['wf_config_value'];
					}
					else
					{
						$result_array[$res['wf_config_name']] = $res['wf_config_value_int'];
					}
				}
				else
				{// build a more complex result array, which is just the table rows
					$result_array[] = $res;
				}
			}
		}
		else //we'll load a Process object and let him work for us
		{
			//Warning: this means you have to include the Process.php from the API
			$this->Process = &Factory::newInstance('Process');
			$this->Process->getProcess($pId);
			$result_array = $this->Process->getConfigValues($config_array);
			unset ($this->Process);
		}
		return $result_array;
	}

	/**
	 * Calls a process object to save his new config values by taking a process Id as first argument and simply call
	 * this process's setConfigValues method. We let the process define the better way to store the data given as second arg.
	 *
	 * @param int $pId Process id
	 * @param array &$config_array
	 * @return void
	 * @access public
	 */
	function setConfigValues($pId, &$config_array)
	{
		//Warning: this means you have to include the Process.php from the API
		$this->Process = &Factory::newInstance('Process');
		$this->Process->getProcess($pId);
		$this->Process->setConfigValues($config_array);
		unset ($this->Process);
	}

	/**
	 * Gets available agents list
	 *
	 * @return array
	 * @access public
	 */
	function get_agents()
	{
		return galaxia_get_agents_list();
	}

	/**
	 * Gets the view activity id avaible for a given process
	 *
	 * @param int $pId Process Id
	 * @return bool False if no view activity is avaible for the process, return the activity id if there is one
	 * @access public
	 */
	function get_process_view_activity($pId)
	{
		$mid = 'where gp.wf_p_id=? and ga.wf_type=?';
		$bindvars = array($pId,'view');
		$query = 'select ga.wf_activity_id
			from '.GALAXIA_TABLE_PREFIX.'processes gp
			INNER JOIN '.GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
			$mid";
		$result = $this->query($query,$bindvars);
		$ret = Array();
		$retval = false;
		if (!(empty($result)))
		{
			while($res = $result->fetchRow())
			{
				$retval = $res['wf_activity_id'];
			}
		}
		return $retval;
	}

}


?>
