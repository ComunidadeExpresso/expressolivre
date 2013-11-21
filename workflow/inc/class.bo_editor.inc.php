<?php
/**************************************************************************\
* eGroupWare                                                 *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once('class.bo_ajaxinterface.inc.php');
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Rodrigo Daniel C de Lira - rodrigo.lira@gmail.com
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 */
class bo_editor extends bo_ajaxinterface
{	
	/**
	 *  Contructor
	 *  @access public
	 *  @return object
	 */
	function bo_editor() {
		parent::bo_ajaxinterface();		
	}
	/**
	 *  Get the source
	 *  @param string $proc_name process name
	 *  @param string $file_name file name
	 *  @param string $type type 
	 *  @access public
	 *  @return string source data
	 */
	function get_source($proc_name, $file_name, $type)
    {
		if ((strpos($file_name,'/') !== false) || (strpos($file_name,'/') !== false))
			exit(0);
		if ((strpos($proc_name,'/') !== false) || (strpos($proc_name,'/') !== false))
			exit(0);

        switch($type)
        {
            case 'atividade':
                $path =  'activities' . SEP . $file_name;
				$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
                break;
            case 'template':
                $path =  'templates' . SEP . $file_name;
				$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
                break;
            case 'include':
                $path = $file_name;
				$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
                break;
            case 'resource':
				$complete_path = GALAXIA_PROCESSES . '/' . $proc_name . '/resources/' . $file_name;
                break;
		}
		if (!file_exists($complete_path))
			exit(0);
        
		if (!$file_size = filesize($complete_path)) return '';
        $fp = fopen($complete_path, 'r');
        $data = fread($fp, $file_size);
        fclose($fp);

		//if ($type != 'template') 
		//{
			$data = str_replace("\r", "", $data);
			$data = str_replace("\\", "\\\\", $data);
			$data = str_replace("\n", "\\n", $data);
			$data = str_replace('"', '" +String.fromCharCode(34)+ "', $data);
			$data = str_replace('\'', '" +String.fromCharCode(39)+ "', $data);
			$data = str_replace('<', '" +String.fromCharCode(60)+ "', $data);
			$data = str_replace('>', '" +String.fromCharCode(62)+ "', $data);
			//$data = addslashes($data);
			$data = str_replace("\t", "\\t", $data);
			//$data = str_replace("\n", "\\n", $data);
		//}
		
        return $data;
    }
	/**
	 *  Save the source
	 *  @param string $proc_name process name
	 *  @param string $file_name file name
	 *  @param string $type type 
	 *  @param string $source 
	 *  @access public
	 *  @return string
	 */
	function save_source($proc_name, $file_name, $type, $source)
    {
		if ((strpos($file_name,'/') !== false) || (strpos($file_name,'/') !== false))
			return 'Não foi possível executar a operação solicitada';
		if ((strpos($proc_name,'/') !== false) || (strpos($proc_name,'/') !== false))
			return 'Não foi possível executar a operação solicitada';

        // in case code was filtered
        //if (!$source) $source = @$GLOBALS['egw_unset_vars']['_POST[source]'];

        switch($type)
        {
            case 'atividade':
                $path =  'activities' . SEP . $file_name;
        		$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
                break;
            case 'template':
                $path = 'templates' . SEP . $file_name;
        		$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
                break;
            case 'include':
                $path = $file_name;
        		$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
                break;
            case 'resource':
				$complete_path = GALAXIA_PROCESSES . '/' . $proc_name . '/resources/' . $file_name;
                break;
        }

        // In case you want to be warned when source code is changed:
        // mail('yourmail@domain.com', 'source changed', "PATH: $complete_path \n\n SOURCE: $source");
        
		$erro = false;
		if ($fp = fopen($complete_path, 'w')) 
		{
        	$erro = !fwrite($fp, $source); 
        	fclose($fp);
		}
		else
		{
			$erro = true;
		}

		return ($erro ? 'Erro ao salvar o arquivo.' : 'Arquivo salvo com sucesso.');
    }

	/**
	 *  Check process
	 * 
	 *  @param integer $pid pid
	 *  @param  object $activity_manager 
	 *  @param string $error_str error string  
	 *  @access public
	 *  @return string
	 */
	function check_process($pid, &$activity_manager, &$error_str)
    {
        $valid = $activity_manager->validate_process_activities($pid);
        if (!$valid)
        {
            $errors = $activity_manager->get_error(true);
            $error_str = '<b>Os seguintes items devem ser corrigidos para ativar o processo:</b>';
            foreach ($errors as $error)
            {
				if (strlen($error) > 0) 
				{
                	$error_str .= '<li>'. $error . '<br/>';
				}
            }
            $error_str .= '</ul></small>';
            return 'n';
        }
        else
        {
            $error_str = '';
            return 'y';
        }
    }
    
	/**
	 *  Save template source
	 * 
	 *  @param array $p process 
	 *  @access public
	 *  @return string
	 */
	function save_template_source($p)
	{
		
		$proc_name = $p['proc_name'];
		$file_name = $p['file_name'];
		$type      = $p['tipo_codigo'];
		$source    = $p['code'];
		$msg       = array();

		$msg[] = $this->save_source($p['proc_name'],$p['file_name'], $p['tipo_codigo'], $p['code']);
		
		return implode('<br />',$msg);
	}
	/**
	 *  Save resource
	 * 
	 *  @param array $p process 
	 *  @access public
	 *  @return string
	 */
	function save_resource($p)
	{
		
		$proc_name = $p['proc_name'];
		$file_name = $p['file_name'];
		$type      = $p['tipo_codigo'];
		$source    = $p['code'];
		$msg       = array();

		$msg[] = $this->save_source($p['proc_name'],$p['file_name'], $p['tipo_codigo'], $p['code']);
		
		return implode('<br />',$msg);
	}
	/**
	 *  Save php souurce
	 * 
	 *  @param array $p process 
	 *  @access public
	 *  @return string
	 */
	function save_php_source($p)
	{
		$proc_name = $p['proc_name'];
		$file_name = $p['file_name'];
		$type      = $p['tipo_codigo'];
		$source    = $p['code'];
		$msg       = array();
		$error_str = '';

		$msg[] = $this->save_source($p['proc_name'],$p['file_name'], $p['tipo_codigo'], $p['code']);

		if ($p['tipo_codigo'] != 'include')
		{
			$activity_manager   = &Factory::newInstance('ActivityManager');

			if ($this->check_process($p['proc_id'], &$activity_manager, &$error_str) == 'n')
				$msg[] = $error_str;
		}

		return implode('<br />',$msg);
	}

	/**
	 *  Check syntax
	 * 
	 *  @param array $p process 
	 *  @access public
	 *  @return string
	 */
	function check_syntax($p) 
	{
		$code   = $p['code'];
		$errors = "Check syntax failed.";
		$fp = fopen('/tmp/check_syn.tmp', 'w'); 
       	if ($fp) {
			fwrite($fp, $code); 
			$errors = `php -l /tmp/check_syn.tmp`;
			$errors = str_replace("in /tmp/check_syn.tmp","",$errors);
			$errors = str_replace("parsing /tmp/check_syn.tmp","parsing file",$errors);
			$errors = str_replace("/tmp/check_syn.tmp","",$errors);
       		fclose($fp);
       		unlink('/tmp/check_syn.tmp');
		}
		return $errors;
	}

}
?>
