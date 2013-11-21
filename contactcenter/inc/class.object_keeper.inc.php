<?php
  /***************************************************************************\
  * eGroupWare - Object Keeper                                                *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	class object_keeper
	{
		var $repository = array('_USED_' => array());
		var $rep_size = 0;
		var $max_rep_size = 0; 

		var $serial_rep;
		
		function object_keeper ()
		{
			// TODO: get this value from preferences
			$this->max_rep_size = 50000;
		}
		
		function & GetObject ($class_name, $unique_id = '_UNDEF_',
							$p1='_UNDEF_',$p2='_UNDEF_',$p3='_UNDEF_',$p4='_UNDEF_',
	                		$p5='_UNDEF_',$p6='_UNDEF_',$p7='_UNDEF_',$p8='_UNDEF_',
	                		$p9='_UNDEF_',$p10='_UNDEF_',$p11='_UNDEF_',$p12='_UNDEF_',
	                		$p13='_UNDEF_',$p14='_UNDEF_',$p15='_UNDEF_',$p16='_UNDEF_')
		{
			// TODO: error management
			if (is_array($unique_id))
			{
				exit(lang('Object Keeper ERROR: Can\'t use an array as an unique ID!'));
			}
				
			if (is_object($repository[$class_name][$unique_id]))
			{
				$this->repository['_USED_'][$class_name.','.$unique_id]++;
				return ($this->repository[$class_name][$unique_id]);
			}
			
			if ($this->repository_size > $this->max_rep_size)
			{
				arsort($this->repository['_USED_']);
				reset($this->repository['_USED_']);
				$remove_class = explode(',',array_shift($this->repository['_USED_']));
				unset($this->repository[$remove_class[0]]);
			}
			
			$this->repository[$class_name][$unique_id] =& CreateObject($class_name,$p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,$p12,$p13,$p14,$p15,$p16);
			//$this->repository[$class_name]['_INC_'] = '../../'.substr($class_name, 1, strpos($class_name, '.')).substr($class_name, strpos($class_name, '.')+1, strlen($class_name));

			$this->repository['_USED_'][$class_name.','.$unique_id] = 1;
	
			$this->rep_size = strlen(serialize($this->repository));
			
			return $this->repository[$class_name][$unique_id];		
		}
		
		/*!
		
			@function RemoveObject
			@abstract Removes an object with the specified ID from the repository
			@author Raphael Derosso Pereira
			
			@param string $class The class full name
			@param mixed $id The Object's Unique ID
		
		*/
		function RemoveObject($class,$id)
		{
			if ($this->repository[$class][$id])
			{
				unset($this->repository[$class][$id]);
				unset($this->repository['_USED_'][$class.','.$id]);
				return true;
			}
			
			return false;
		}
		/*!
		
			@function __sleep
			@abstract Reserved PHP function to be executed when the Object
				Keeper is serialized
			@author Raphael Derosso Pereira
		
		*/
		function __sleep()
		{
			/* This is a temporary solution to keep the Object Keeper
			 * inside the session. It is here while the method below
			 * isn't implemented.
			 */
			unset($this->repository);
			
			/* This is an attempt to keep the objects serialized in another
			 * array, but the problem is that the php serialize function
			 * doesn't keep track of references, so it's impossible (at least
			 * I didn't find any good way to do it) to manage references. This
			 * doesn't apply only to objects. It applies to any type.
			 * 
			foreach($repository as $class_name => $object)
			{
				foreach(get_object_vars($object) as $attr => $attr_value)
				{
					if (!is_object($attr_value))
					{
						$this->serial_rep[$class_name][$attr] = serialize($attr_value);
					}
				}
			}*/
		}
		
		function __wakeup()
		{
		}
	}
?>
