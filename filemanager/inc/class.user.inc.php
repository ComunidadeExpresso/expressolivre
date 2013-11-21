<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
	class user
	{
		var $public_functions = array(
			'save_preferences' => True,
			'get_preferences' => True,
			'card'	=>	True
		);

		var $bo;
		var $user_id;

		var $target;

		var $prefs;//array

		var $current_config;
		// this ones must be checked thorougly;
		var $fileman = Array();
		var $path;
		var $file;
		var $debug = false;
		var $now;

		function user()
		{
			$this->now = date('Y-m-d');

			$this->bo = CreateObject('filemanager.bofilemanager');

			$c = CreateObject('phpgwapi.config','filemanager');
			$c->read_repository();
			$this->current_config = $c->config_data;
			$this->user_id = $GLOBALS['phpgw_info']['user']['account_id'];

			// here local vars are created from the HTTP vars
			@reset($GLOBALS['HTTP_POST_VARS']);
			while(list($name,) = @each($GLOBALS['HTTP_POST_VARS']))
			{
				$this->$name = $GLOBALS['HTTP_POST_VARS'][$name];
			}

			@reset($GLOBALS['HTTP_GET_VARS']);
			while(list($name,) = @each($GLOBALS['HTTP_GET_VARS']))
			{
				$$name = $GLOBALS['HTTP_GET_VARS'][$name];
				$this->$name = $GLOBALS['HTTP_GET_VARS'][$name];

			}

			$to_decode = array
			(
				'preferences'	=> array('preferences' => '')
			);

			reset($to_decode);
			while(list($var, $conditions) = each($to_decode))
			{
				while(list($condvar, $condvalue) = each($conditions))
				{
					if(isset($$condvar) && ($condvar == $var || $$condvar == $condvalue))
					{
						if(is_array($$var))
						{
							$temp = array();
							while(list($varkey, $varvalue) = each($$var))
							{
								if(is_int($varkey))
								{
									$temp[$varkey] = stripslashes(base64_decode(urldecode(($varvalue))));
								}
								else
								{
									$temp[stripslashes(base64_decode(urldecode(($varkey))))] = $varvalue;
								}
							}
							$this->$var = $temp;
						}
						elseif(isset($$var))
						{
							$this->$var = stripslashes(base64_decode(urldecode($$var)));
						}
					}
				}
			}

			// get appl. and user prefs
			$pref = CreateObject('phpgwapi.preferences', $this->bo->userinfo['username']);
			$pref->read_repository();
			$pref->save_repository(True);
			$pref_array = $pref->read_repository();
			$this->prefs = $pref_array[$this->bo->appname];

			//always show name

			$this->prefs[name] =1;		
			
		}

		function card(){
			header('Content-Type: text/html');
			$expires = 60*60*24*10; /* 10 days */
			header("Cache-Control: maxage=".$expires);
			header("Pragma: public");
			header("Expires: ".gmdate('D, d M Y H:i:s', time()+$expires));
			$account_info = $GLOBALS['phpgw']->accounts->get_list('accounts',0,1,1,base64_decode($this->lid),1,'exact');
			echo $account_info[0]['account_firstname'].' '.$account_info[0]['account_lastname']."<br>";
			echo '<a target="_blank" href="../expressoMail1_2/index.php?to='
				.$account_info[0]['account_email'].'">'.$account_info[0]['account_email'];
			/*
			// TODO: PHOTO, ONLY FOR FOOL LDAP
			if (isset($GLOBALS['phpgw_info']['server']['ldap_root_pw'])){
			}
			*/
		}
		function get_preferences(){
			 echo(serialize($_SESSION['phpgw_info']['user']['preferences']['filemanager']));
		}
		function save_preferences(){

			$_SESSION['phpgw_info']['user']['preferences']['filemanager'] = unserialize($this->preferences);
			/* See if preferences exists or not */
			$query = "SELECT count(preference_owner) FROM phpgw_preferences WHERE preference_app = 'filemanager' AND preference_owner = ".$this->user_id." LIMIT 1";
			if ($GLOBALS['phpgw']->db->query($query) && $GLOBALS['phpgw']->db->next_record())
				$val = $GLOBALS['phpgw']->db->row();
			else
			{
				echo $GLOBALS['phpgw']->db->error;
				return false;
			}

			$string_serial = addslashes($this->preferences);
			if ($val['count'] == '1')
			{
				$query = "UPDATE phpgw_preferences set preference_value = '".$string_serial.
					"' where preference_app = 'filemanager'".
					" and preference_owner = '".$this->user_id."'";
				if (!$GLOBALS['phpgw']->db->query($query)){
					echo $GLOBALS['phpgw']->db->error;
					return false;
				}
				else{
					echo "True";
					return;
				}
			}
			else
			{
				/*preferences does not exist*/
				$query = "INSERT INTO phpgw_preferences values (".$this->user_id.",'filemanager','".$string_serial."')";
				if (!$GLOBALS['phpgw']->db->query($query)){
					echo $GLOBALS['phpgw']->db->error;
					return false;
				}
				else{
					echo "True";
					return;
				}

			}
		}

	}
?>
