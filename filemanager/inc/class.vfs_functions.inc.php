<?php
	/**************************************************************************\
	* -------------------------------------------------------------------------*
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/* $Id: class.vfs_functions.inc.php 2009-11-11 amuller $ */

	class vfs_functions
	{
		var $public_functions = array(
			'touch' => True,
			'setRestricted'=>True,
			'editComment'=> True,
			'rename'=>True,
			'delete'=>True,
			'archive'=>True,
			'unarchive'=>True,
			'copyto'=>True,
			'moveto'=>True,
			'summary' => True
		);

		//keep
		var $bo;
		var $dispath;
		var $cwd;
		var $lesspath;
		var $dispsep;

		var $target;

		var $prefs;//array

		var $current_config;
		// this ones must be checked thorougly;
		var $fileman = Array();
		//var $fileman;
		var $path;
		var $file;
		var $debug = false;
		var $now;

		function vfs_functions()
		{
			$this->now = date('Y-m-d H:i:s');

			$this->bo = CreateObject('filemanager.bofilemanager');

			$c = CreateObject('phpgwapi.config','filemanager');
			$c->read_repository();
			$this->current_config = $c->config_data;


			// here local vars are created from the HTTP vars
			@reset($GLOBALS['HTTP_POST_VARS']);
			while( list($name,) = @each($GLOBALS['HTTP_POST_VARS']) )
			{
				$this->$name = base64_decode($GLOBALS['HTTP_POST_VARS'][$name]);
			}

			@reset($GLOBALS['HTTP_GET_VARS']);
			while(list($name,) = @each($GLOBALS['HTTP_GET_VARS']) )
			{
				$$name = $GLOBALS['HTTP_GET_VARS'][$name];
				$this->$name = $GLOBALS['HTTP_GET_VARS'][$name];
			}

			$to_decode = array
			(
				'op'		=> array('op' => ''),
				'path'	=> array('path' => ''),
				'file'		=> array('file' => ''),
				'sortby'	=> array('sortby' => ''),
				'messages'	=> array('messages'	=> ''),
				'comment'	=> array('comment' => ''),
				'from'	=> array('from' => ''),
				'fileman'	=> array('fileman' => ''),
				'to'	=> array('to' => '')
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
									$temp[$varkey] = stripslashes(base64_decode($varvalue));
								}
								else
								{
									$temp[stripslashes(base64_decode($varkey))] = $varvalue;
								}
							}
							$this->$var = $temp;
						}
						elseif(isset($$var))
						{
							$this->$var = stripslashes(base64_decode($$var));
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
		
		function convertDateForm($pDate)
		{
			/**
			 * Recebe a data no formato	 : aaaa-mm-dd
			 * Retorna a data no formato : dd/mm/aaaa
			 **/
		
			$cDate = date_parse($pDate);
			$day   = ( strlen($cDate['day']) > 1 ) ? $cDate['day'] : "0".$cDate['day'];
			$month = ( strlen($cDate['month']) > 1 ) ? $cDate['month'] : "0".$cDate['month'];
			$year  = $cDate['year'];
		
			return $day."/".$month."/".$year;
		}
		
		// String format is YYYY-MM-DD HH:MM
		function dateString2timeStamp($string)
		{
			/**
			 * Recebe a data no formato	 : aaaa-mm-dd
			 * Retorna a data no formato : dd/mm/aaaa
			 **/
		
			$cDate = date_parse($string);
			$day   = ( strlen($cDate['day']) > 1 ) ? $cDate['day'] : "0".$cDate['day'];
			$month = ( strlen($cDate['month']) > 1 ) ? $cDate['month'] : "0".$cDate['month'];
			$year  = $cDate['year'];
		
			return $day."/".$month."/".$year;
		}
		
		function verifyLock($file,$relative){
			$ls_array = $this->bo->vfs->ls(array(
				'string'        => $file,
				'relatives'     => array($relative),
				'checksubdirs'  => False,
				'nofiles'       => True
			));
			$timestamp = $this->dateString2timeStamp($ls_array[0]['modified']);
			if (time() - $timestamp < 60 && $ls_array[0]['modifiedby_id'] != $GLOBALS['phpgw_info']['user']['account_id']) // recently than last minute: someone is editing
			{
				$this->messages[]=lang('Error:').lang('This file is being edited right now by:').$GLOBALS['phpgw']->accounts->id2name($ls_array[0]['modifiedby_id']);
				return False;

			}
			else
				return True;
		}

		function setRestricted()
		{
			$GLOBALS['phpgw_info']['flags'] = array
				(
					'currentapp'		=> 'filemanager',
					'noheader'		=> True,
					'nonavbar'		=> True,
					'nofooter'		=> True,
					'noappheader'	=> True,
					'enable_browser_class'  => True
				);

			if ($this->file)
			{
				$filename=$this->path.'/'.$this->file;
				if(!$this->verifyLock($filename,RELATIVE_NONE))
				{
					echo "False";
					return False;
				}
				
				$ls_array = $this->bo->vfs->ls(array(
					'string'        => $filename,
					'relatives'     => array(RELATIVE_NONE),
					'checksubdirs'  => False,
					'nofiles'       => True
				));
				if (intval($ls_array[0]['type']) == 0)
					$type = 1;
				else
					$type = 0;

				if($this->bo->vfs->set_attributes (array(
					'string'        => $filename,
					'relatives'     => RELATIVE_NONE,
					'attributes'    => array('type' => $type)
					))
				)
				{
					echo "True|".$this->file;
				}
				else
				{
					echo "False";
				}
			}

		}
		function touch(){
			if($this->file)
				if ($this->bo->vfs->touch(array('string'=> $this->file,'relatives' => array(RELATIVE_ALL))))
				{
					echo "True";
					return True;
				}
				else
					return False;

		}
		function summary()
		{
			header('Content-Type: image/png');
			$expires = 60*60*24*15;
			header("Cache-Control: maxage=".$expires);
			header("Pragma: public");
			header("Expires: ".gmdate('D, d M Y H:i:s', time()+$expires));
			if($this->file)
			{	
				$content = $this->bo->vfs->summary(array('path' => $this->path,
					'string' => $this->file
				));
				if (strlen($content) < 1)
				{
					$filename = './filemanager/templates/default/images/error.png';
					$handle = fopen($filename,'rb');
					$content = fread($handle,filesize($filename));
					fclose($handle);
				}
				echo $content;
			}
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	
		function delete(){
			foreach($this->fileman as $filename)
			{
				if($this->verifyLock($filename,RELATIVE_ALL) && $this->bo->vfs->rm(array(
					'string' => $this->path.'/'.$filename,
					'relatives' => array (RELATIVE_NONE)
				)))
				{
					echo $filename."|";
				}
				else
				{
					echo "False|".$filename;
					return False;
				}
			}
		}
		function archive(){
			foreach($this->fileman as $filename)
			{
				if(!$this->verifyLock($filename,RELATIVE_ALL))
				{
					echo "locked|".$filename;
					return False;
				}
				$command .= " ".escapeshellarg($filename);
			}
			$zipFileName=$GLOBALS['phpgw_info']['user']['account_lid'].date("Y-m-d,H:i:s").".zip";
			$zipFilePath=ini_get("session.save_path")."/".$zipFileName;
			$command = $zipFilePath.$command;

			if (strlen($this->pswd) > 0){
				$command = " -P ".(base64_decode($this->pswd) ^ $_SESSION['phpgw_info']['filemanager']['user']['sec_key'])." ".$command;
			}

			exec("cd ".$this->bo->vfs->basedir.$this->path.";".escapeshellcmd("nice -n19 zip -9 ".$command),$output,$return_var);
			exec("history -c"); // privacy is good, we dont want get passwords!
                        if ($return_var > 1){
				echo "False|".$return_var;
				return false;
			}

			$this->bo->vfs->cp(array(
				'from'=> $zipFilePath,
				'to'=> $zipFileName,
				'relatives'     => array(RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)
			));
			$this->bo->vfs->set_attributes(array(
				'string'=> $zipFileName,
				'relatives'     => array(RELATIVE_ALL),
				'attributes'=> array(
					'mime_type' => "application/zip"
				)
			));
			exec("rm -f ".escapeshellcmd(escapeshellarg($zipFilePath)));
			$this->delete();
		}
		function unarchive(){
			$command = escapeshellarg($this->file);
			if (strlen($this->pswd) > 0){
				$command = " -P ".(base64_decode($this->pswd) ^ $_SESSION['phpgw_info']['filemanager']['user']['sec_key'])." ".$command;
			}

			exec("cd ".escapeshellarg($this->bo->vfs->basedir.$this->path).";".escapeshellcmd("nice -n19 unzip ".$command),$output, $return_var);
			exec("history -c"); // privacy is good, we dont want get passwords!

			if ($return_var == 9 || $return_var == 5 || $return_var == 82){
				echo "wpasswd|";
				return false;
			}else if($return_var > 1){
				echo "False|";
			}

			$this->fileman[] = $this->file;
			$this->delete();
			$this->bo->vfs->update_real(array(
					'string'        => $this->path,
					'relatives'     => array(RELATIVE_NONE)
				));
		}
		function editComment()
		{
				if($badchar = $this->bo->bad_chars($this->comment, False, True))
				{
					echo "False|badchar|".$badchar;
					return False;
				}

				if ($this->bo->vfs->set_attributes(array(
					'string'        => $this->file,
					'relatives'     => array(RELATIVE_ALL),
					'attributes'    => array(
					'comment' => stripslashes($this->comment)
					)
				)))
				{
					echo "True|".$this->file;
					return True;
				}
		}
		# Handle Moving Files and Directories
		function moveto()
		{
			if(!$this->to)
			{
				echo "NODIR|";
				return;
			}
			else
			{
				while(list($num, $file) = each($this->fileman))
				{
					if($this->bo->vfs->mv(array(
						'from'	=> $this->from . '/' . $file,
						'to'	=> $this->to . '/' . $file,
						'relatives'	=> array(RELATIVE_NONE, RELATIVE_NONE)
					)))
						++$moved;
					else
						++$error;
				}
			}
			if($error > 0){
				echo "SOMEERROR|".$error;
				return;
			}
			if($moved > 0)
				echo "MOVED|".$moved;
		}

		// Handle Copying of Files and Directories
		function copyto()
		{
			if(!$this->to)
			{
				echo "NODIR|";
				return;
			}
			else
			{
				while(list($num, $file) = each($this->fileman))
				{
					if($this->bo->vfs->cp(array(
						'from'	=> $this->from . '/' . $file,
						'to'	=> $this->to . '/' . $file,
						'relatives'	=> array(RELATIVE_NONE, RELATIVE_NONE)
					)))
						++$copied;
					else
						++$error;
				}
			}
			if($error > 0){
				echo "SOMEERROR|".$error;
				return;
			}
			if($copied > 0)
				echo "COPIED|".$copied;
		}

		# Handle Renaming Files and Directories
		function rename()
		{
			$_return = array();
			
			if ( $this->file )
			{
				if( $badchar = $this->bo->bad_chars($this->to, True, True) )
				{
					$_return[] = array( "error" => $badchar);	
				}
				if(preg_match('/\//', $this->to) || preg_match('/\\\\/', $this->to))
				{
					$_return[] = array( "error"=> "slashes");
				}
				elseif(!$this->verifyLock($this->file,RELATIVE_CURRENT))
				{
					$_return[] = array( "error" => "editing" );
				}
				elseif ($this->bo->vfs->mv(array(
						'from'  => $this->path.'/'.$this->file,
						'to'    => $this->path.'/'.$this->to,
						'relatives' => array(RELATIVE_NONE,RELATIVE_NONE)
				)))
				{
					$_return[] = array( "true" => lang('renamed %1 to %2', $this->file ,$this->to ) );
					
					// Get Type Mime Type
					$mimeType = $this->bo->vfs->get_ext_mime_type(array ('string' => $this->to )); 
					
					$this->bo->vfs->set_attributes( array(
															'string'		=> $this->to,
															'relatives'	=> array(RELATIVE_ALL),
															'attributes'	=> array('mime_type' => $mimeType)
					 ));
				}
				else
				{
					$_return[] = array( "error"	=> $this->file . " " . $this->to );	
				}
			}
			else
			{
				$_return[] = array("error" => "whitout file ");
			}
			
			echo serialize( $_return );
		}
	}