<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'ProcessManager.php');

	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_processmanager extends ProcessManager
	{
		/**
		 * @var array $workflow_acl
		 * @access public
		 */
		var $workflow_acl;
		/**
		 * @var array $not_export_attributes
		 * @access public
		 */
		var $not_export_attributes = array(
			'database_user',
			'database_password'
		);
	   /**
		 * Constructor
		 * @access public
		 * @return object
		 */
		function workflow_processmanager()
		{
			parent::ProcessManager();
			$this->workflow_acl = Factory::getInstance('workflow_acl');

			/* allow regular users to see the process graph */
			if ($_GET['menuaction'] == "workflow.ui_adminactivities.show_graph")
				return;

			if (isset($_GET['p_id']))
			{
				if (!($this->workflow_acl->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']) || $this->workflow_acl->check_process_access($GLOBALS['phpgw_info']['user']['account_id'], (int) $_GET['p_id'])))
                {
                    $GLOBALS['phpgw']->common->phpgw_header();
                    echo parse_navbar();
                    echo lang('access not permitted');
                    $GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_adminprocesses');
                    $GLOBALS['phpgw']->log->commit();
                    $GLOBALS['phpgw']->common->phpgw_exit();
                }
            }
		}
		/**
		 * Import process data
		 * @param $data
		 * @access public
		 * @return bool
		 */
		function import_process(&$data)
		{
			if (!(isset($this->activity_manager)))  $this->activity_manager = &Factory::newInstance('ActivityManager');

			if (parent::import_process($data))
			{
				$proc_name = $this->_normalize_name($data['name'],$data['version']);
				foreach($data['templates'] as $tpl) 
				{
					$full_fname = GALAXIA_PROCESSES.SEP.$proc_name.SEP.'code'.SEP.'templates'.SEP.$tpl['name'];
					if (file_exists($full_fname)) unlink($full_fname);
					
					$fp = fopen($full_fname,"w");
			        fwrite($fp, $tpl['code']);
					fclose($fp);
				}

				foreach($data['includes'] as $inc) 
				{
					$full_fname = GALAXIA_PROCESSES.SEP.$proc_name.SEP.'code'.SEP.$inc['name'];
					if (file_exists($full_fname)) unlink($full_fname);
					
					$fp = fopen($full_fname,"w");
			        fwrite($fp, $inc['code']);
					fclose($fp);
				}

				//create resource dir if needed
				$resource_dir = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'resources';
				if (count($data['resources']))
					if (!is_dir($resource_dir))
						mkdir($resource_dir, 0770);

				if (is_array($data['resources']))
				{
					foreach($data['resources'] as $res)
					{
						$full_fname = $resource_dir . SEP . $res['name'];
						if (file_exists($full_fname)) unlink($full_fname);
						$fp = fopen($full_fname,"w");
						fwrite($fp, base64_decode($res['bindata']));
						fclose($fp);
					}
				}

				return true;
			} else {
				return false;
			}
		}

		/** 
    	* Creates an XML representation of a process.
		* Original from ProcessManager Class
		* Modified to support includes, resources and variable templates
		* @param $pId process id
		* @access public
		* @return void
  		*/
		function serialize_process($pId)
	  	{
			if (!(isset($this->activity_manager)))  $this->activity_manager = &Factory::newInstance('ActivityManager');
			if (!isset($this->jobManager))
				$this->jobManager = &Factory::newInstance('JobManager');
		
			//if (!(isset($this->activity_manager)))  $this->activity_manager = new ActivityManager($this->db);
			// <process>
			$out = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
			$out.= '<process>'."\n";
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
			  if (array_search($name,$this->not_export_attributes) === false) 
			  {
			  	$out.='    <config>'."\n";
			  	$out.='      <wf_config_name>'.htmlspecialchars($name).'</wf_config_name>'."\n";
			  	$out.='      <wf_config_value>'.htmlspecialchars($value).'</wf_config_value>'."\n";
			  	$out.='      <wf_config_value_int>'.htmlspecialchars($value_int).'</wf_config_value_int>'."\n";
			  	$out.='    </config>'."\n";
			  }
			}
			$out.='  </configs>'."\n";

			// Now loop over activities
			
			$act_list = $this->activity_manager->list_activities($pId, 0, -1, 'wf_name__asc', '','',false);
			$out.='  <activities>'."\n";
			foreach($act_list['data'] as $res) {      
			  $name = $res['wf_normalized_name'];
			  $out.='    <activity>'."\n";
			  $out.='      <name>'.htmlspecialchars($res['wf_name']).'</name>'."\n";
			  $out.='      <type>'.htmlspecialchars($res['wf_type']).'</type>'."\n";
			  $out.='      <description>'.htmlspecialchars($res['wf_description']).'</description>'."\n";
			  $out.='      <menuPath>'.htmlspecialchars($res['wf_menu_path']).'</menuPath>'."\n";
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
			  $out.='    </activity>'."\n";    
			}
			$out.='  </activities>'."\n";
			
			//export all templates
			$base_path = GALAXIA_PROCESSES.SEP.$wf_procname.SEP.'code'.SEP.'templates';	
			$handle = opendir($base_path); 
			$out.='  <templates>'."\n";
			while (false !== ($name = readdir($handle)))
			{
				if (is_dir($base_path.SEP.$name))
					continue;
				if (substr($name, -4) != ".tpl")
					continue;
				$out.='    <template>'."\n";
				$out.='      <name>'.htmlspecialchars($name).'</name>'."\n";
				//the code
				$out.='      <code><![CDATA[';
				$fp=fopen($base_path.SEP.$name,'r');
				while(!feof($fp)) {
					$line=fread($fp,8192);
					$out.=$line;
				}
				fclose($fp);
				$out.='      ]]></code>';
				$out.='    </template>'."\n";    
			}
			$out.='  </templates>'."\n";

			//export all includes 
			$base_path = GALAXIA_PROCESSES.SEP.$wf_procname.SEP.'code';	
			$handle = opendir($base_path); 
			$out.='  <includes>'."\n";
			while (false !== ($name = readdir($handle)))
			{
				if (is_dir($base_path.SEP.$name)) /* ignore directories */
					continue;
				if ($name == 'shared.php') /* shared.php was saved before */
					continue;
				if (substr($name, -4) != ".php")
					continue;
				$out.='    <include>'."\n";
				$out.='      <name>'.htmlspecialchars($name).'</name>'."\n";
				//the code
				$out.='      <code><![CDATA[';
				$fp=fopen($base_path.SEP.$name,'r');
				while(!feof($fp))
				{
					$line=fread($fp,8192);
					$out.=$line;
				}
				fclose($fp);
				$out.='      ]]></code>';
				$out.='    </include>'."\n";    
			}
			$out.='  </includes>'."\n";

			$jobList = $this->jobManager->getJobsByProcessID($pId);
			$out .= "  <jobs>\n";
			foreach ($jobList as $job)
			{
				$out .= "    <job>\n";
				$out .= "      <name>" . htmlspecialchars($job['name']) . "</name>\n";
				$out .= "      <description>" . htmlspecialchars($job['description']) . "</description>\n";
				$out .= "      <timeStart>" . htmlspecialchars($job['time_start']) . "</timeStart>\n";
				$out .= "      <intervalValue>" . htmlspecialchars($job['interval_value']) . "</intervalValue>\n";
				$out .= "      <intervalUnity>" . htmlspecialchars($job['interval_unity']) . "</intervalUnity>\n";
				$out .= "      <dateType>" . htmlspecialchars($job['date_type']) . "</dateType>\n";
				$out .= "      <weekDays>" . htmlspecialchars($job['week_days']) . "</weekDays>\n";
				$out .= "      <monthOffset>" . htmlspecialchars($job['month_offset']) . "</monthOffset>\n";
				$out .= "      <active>f</active>\n";
				$out .= "      <fileContents><![CDATA[" . file_get_contents($this->jobManager->getJobFile($job['job_id'])) . "]]></fileContents>\n";
				$out .= "    </job>\n";
			}
			$out .= "  </jobs>\n";

			//export all resources 
			$base_path = GALAXIA_PROCESSES . SEP . $wf_procname . SEP . 'resources';
			$handle = opendir($base_path); 
			$out.='  <resources>'."\n";
			while (false !== ($name = readdir($handle)))
			{ 
				if (is_dir($base_path.SEP.$name))
					continue;
				if (substr($name, -4) == ".swp")
					continue;
				$out.='    <resource>'."\n";
				$out.='      <name>'.htmlspecialchars($name).'</name>'."\n";
				//the code
				$out.='      <bindata><![CDATA[';
				$fp=fopen($base_path.SEP.$name,'r');
				//while(!feof($fp)) {
				$line=fread($fp,filesize($base_path.SEP.$name));
				$out.=chunk_split(base64_encode($line));
				//}
				fclose($fp);
				$out.=' ]]></bindata>';
				$out.='    </resource>'."\n";
			}
			$out.='  </resources>'."\n";


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
		 * Unserialize process data  
		 * @access public
		 * @return void
		 */
		function unserialize_process(&$xml) 
		{
			// Create SAX parser assign this object as base for handlers
			// handlers are private methods defined below.
			// keep contexts and parse
			$this->parser = xml_parser_create("ISO-8859-1"); 
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
			$jobs = array();
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
                    $tree_z_count = count($this->tree[$z2]['children']);
					for($k=0;$k<$tree_z_count;++$k) {
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
					  $z3 = $this->tree[$z2]['children'][$k];
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
						for($l=0;$l<$tree_z3_count;++$l)
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
			  } elseif($name=='includes') {
                $tree_z_count = count($this->tree[$z]['children']);
				for($j=0;$j<$tree_z_count;++$j) {
				  $z2 = $this->tree[$z]['children'][$j];
				  // this is an activity $name = $this->tree[$z2]['name'];
				  $aux=Array();
				  if($this->tree[$z2]['name']=='include') {
                    $tree_z2_count = count($this->tree[$z2]['children']);
					for($k=0;$k<$tree_z2_count;++$k) {
					  $z3 = $this->tree[$z2]['children'][$k];
					  $name = trim($this->tree[$z3]['name']);
					  $value= trim($this->tree[$z3]['data']);
					  $aux[$name]=$value;
					}
				  }
				  $includes[] = $aux;
				}
			  } elseif($name=='templates') {
                $tree_z_count = count($this->tree[$z]['children']);
				for($j=0;$j<$tree_z_count;++$j) {
				  $z2 = $this->tree[$z]['children'][$j];
				  // this is an activity $name = $this->tree[$z2]['name'];
				  $aux=Array();
				  if($this->tree[$z2]['name']=='template') {
                    $tree_z2_count = count($this->tree[$z2]['children']);
					for($k=0;$k<$tree_z2_count;++$k) {
					  $z3 = $this->tree[$z2]['children'][$k];
					  $name = trim($this->tree[$z3]['name']);
					  $value= trim($this->tree[$z3]['data']);
					  $aux[$name]=$value;
					}
				  }
				  $templates[] = $aux;
				}
			  } elseif($name=='resources') {
                $tree_z_count = count($this->tree[$z]['children']);
				for($j=0;$j<$tree_z_count;++$j) {
				  $z2 = $this->tree[$z]['children'][$j];
				  // this is an activity $name = $this->tree[$z2]['name'];
				  $aux=Array();
				  if($this->tree[$z2]['name']=='resource') {
                    $tree_z2_count = count($this->tree[$z2]['children']);
					for($k=0;$k<$tree_z2_count;++$k) {
					  $z3 = $this->tree[$z2]['children'][$k];
					  $name = trim($this->tree[$z3]['name']);
					  $value= trim($this->tree[$z3]['data']);
					  $aux[$name]=$value;
					}
				  }
				  $resources[] = $aux;
				}
			  }
			  elseif ($name == 'jobs'){
                  $tree_z_count = count($this->tree[$z]['children']);
                  for ($j = 0; $j < $tree_z_count; ++$j)
                  {
                    $job = array();
                            $jobIndex = $this->tree[$z]['children'][$j];
                    if($this->tree[$jobIndex]['name'] == 'job')
                    {
                      $tree_jobIndex_count = count($this->tree[$jobIndex]['children']);
                      for ($k = 0; $k < $tree_jobIndex_count; ++$k)
                      {
                        $propertyIndex = $this->tree[$jobIndex]['children'][$k];
                        $job[trim($this->tree[$propertyIndex]['name'])] = trim($this->tree[$propertyIndex]['data']);
                      }
                    }
                    $jobs[] = $job;
                  }
			  }
			  else {
				$value = trim($this->tree[$z]['data']);
				//print("$name is $value<br/>");
				$process[$name]=$value;
			  }
			}

			$process['configs']		= $configs;
			$process['activities']	= $activities;
			$process['transitions']	= $transitions;
			$process['resources']	= $resources;
			$process['includes']	= $includes;
			$process['templates']	= $templates;
			$process['jobs']	= $jobs;

			return $process;
		  }
		  
		/**
		 * Creates a new process PHP data structure from its XML representation
		 * unserial 
		 * @access public
		 * @return void
		 */
		  function new_process_version($pId, $minor=true)
  		  {
		  	 $new_id = parent::new_process_version($pId,$minor);

			 //copy resource dir too
			 $old_name = GALAXIA_PROCESSES . SEP . $this->_get_normalized_name($pId) . SEP . 'resources';
			 $new_name = GALAXIA_PROCESSES . SEP . $this->_get_normalized_name($new_id) . SEP . 'resources';
			 if (is_dir($old_name))
 			 	$this->_rec_copy($old_name,$new_name);

			 $this->workflow_acl->add_process_admins($new_id, array( $GLOBALS['phpgw_info']['user']['account_id'] ));

			 return $new_id;
		  }
		/**
		 * Remove process
		 * @param int $pId process id 
		 * @access public
		 * @return string 
		 */
		  function remove_process($pId)
		  {
			$result = parent::remove_process($pId);
			$this->workflow_acl->del_process($pId);
			return $result;
		  }
		/**
		 * Replace_process
		 * 
		 * @param int      $pid     process id 
		 * @param array    $vars 
		 * @param boolean  $create
		 *   
		 * @access public
		 * @return int new id
		 */
		  function replace_process($pId, &$vars, $create = true)
          {
            $id = parent::replace_process($pId, $vars, $create);

            if (!$pId)
            {
				$this->workflow_acl->add_process_admins($id, array( $GLOBALS['phpgw_info']['user']['account_id'] ) );
            }

			return $id;
          }	
	}
?>
