<?php
/**************************************************************************\
* eGroupWare                                                 		       *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once('class.bo_ajaxinterface.inc.php');

/**
 * Invalid file name identifier 
 * @name INVALID_FILE_NAME 
 */
define( INVALID_FILE_NAME   , 0 );
/**
 * Invalid process id identifier 
 * @name INVALID_PROCESS_ID 
 */
define( INVALID_PROCESS_ID  , 1 );
/**
 * File already exists identifier
 * @name FILE_ALREADY_EXISTS 
 */
define( FILE_ALREADY_EXISTS , 2 );
/**
 * File created indentifier 
 * @name FILE_CREATED
 */
define( FILE_CREATED	 	, 3 );
/**
 * File not created identifier 
 * @name FILE_NOT_CREATED
 */
define( FILE_NOT_CREATED 	, 4 );
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Rodrigo Daniel C de Lira - rodrigo.lira@gmail.com
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 */
class bo_adminsource extends bo_ajaxinterface
{

	/**
	* @var array $public_functions Array of public functions 
	* @access public
	*/
	var $public_functions = array('export_file' => true
				      );
	
	/**
	* Construtor
	*
	* @access public 
	*/	
	function bo_adminsource() {
		parent::bo_ajaxinterface();		
		}
	
	/**
	* Assign Unit To File Size
	* @param integer $value value
	* @return string file size 
	* @access public 
	*/
	function _assignUnitToFileSize($value)
	{
		$fileSizeUnit = array();
		$fileSizeUnit[] = 'bytes';
		$fileSizeUnit[] = 'Kb';
		$fileSizeUnit[] = 'Mb';
		$fileSizeUnit[] = 'Gb';

		$unitSelect = 0;
		while ($value > 1024.0)
		{
			$value /= 1024.0;
			++$unitSelect;
		}

		$output = round($value, 1);
		$output .= " " . $fileSizeUnit[$unitSelect];

		return $output;
	}

	/**
	* Get process toolbar data
	* @param  array $p process process data
	* @return array
	* @access public
	*/
	function get_toolbar_data($p)
	{

		$process_manager = &Factory::newInstance('ProcessManager');
		$proc_info       = $process_manager->get_process($p['proc_id']);

		$web_server_url  = $_SESSION['phpgw_info']['workflow']['server']['webserver_url'];
		$img_default_dir = Factory::getInstance('TemplateServer')->generateImageLink('');

		if ($proc_info['wf_is_valid'] == 'y')
		{
			$dot_color = 'green';
			$alt_validity = tra('valid');
		}
		else
		{
			$dot_color = 'red';
			$alt_validity = tra('invalid');
		}

		// if process is active show stop button. Else show start button, but only if it is valid. If it's not valid, don't show any activation or stop button.
		if ($proc_info['wf_is_active'] == 'y')
		{
			$start_stop_link = $web_server_url.'/index.php?menuaction=workflow.ui_adminactivities.form&p_id='. $proc_info['wf_p_id'] .'&deactivate_proc='. $proc_info['wf_p_id'];
			$start_stop_img  = $img_default_dir.'stop.gif';
			$start_stop_desc = 'Parar';
		}
		elseif ($proc_info['wf_is_valid'] == 'y')
		{
			$start_stop_link = $web_server_url.'/index.php?menuaction=workflow.ui_adminactivities.form&p_id='. $proc_info['wf_p_id'] .'&activate_proc='. $proc_info['wf_p_id'];
			$start_stop_img  = $img_default_dir.'refresh2.gif';
			$start_stop_desc = 'Ativar';
		}
		else
		{
			$start_stop_link = '';
			$start_stop_img  = '';
		}

		/* load other processes link */
		$proc_ids = $GLOBALS['ajax']->acl->get_granted_processes($_SESSION['phpgw_info']['workflow']['account_id']);
		if (count($proc_ids))
			$where = ' wf_p_id in ('. implode(',',$proc_ids) .') ';
		else
			$where = ' wf_p_id = -1 ';

		$processesInfo = &$process_manager->list_processes(0, -1, 'wf_name__asc', '', $where);
		$otherProcesses = array();
		foreach ($processesInfo['data'] as $pi)
			$otherProcesses[] = array("name" => $pi['wf_name'] . " (v" . $pi['wf_version'] . ")", "link" => $web_server_url . "/index.php?menuaction=workflow.ui_adminsource.form&p_id=" . $pi['wf_p_id'], "pid" => $pi['wf_p_id']);

		$toolbar_data = array (
			'proc_name'					=> $proc_info['wf_name'],
			'version'					=> $proc_info['wf_version'],
			'img_validity'				=> $img_default_dir.$dot_color.'_dot.gif',
			'alt_validity'				=> $alt_validity,
			'start_stop_link'	  		=> $start_stop_link,
			'start_stop_img'	  		=> $start_stop_img,
			'start_stop_desc'	  		=> $start_stop_desc,
			'link_admin_activities'		=> $web_server_url.'/index.php?menuaction=workflow.ui_adminactivities.form&p_id='. $proc_info['wf_p_id'],
			'img_activity'				=> $img_default_dir.'Activity.gif',
			'link_admin_jobs'		=> $web_server_url.'/index.php?menuaction=workflow.ui_adminjobs.form&p_id='. $proc_info['wf_p_id'],
			'img_job'				=> $img_default_dir.'clock.png',
			'link_admin_processes'		=> $web_server_url.'/index.php?menuaction=workflow.ui_adminprocesses.form&p_id='. $proc_info['wf_p_id'],
			'img_change'				=> $img_default_dir.'change.gif',
			'link_admin_shared_source'	=> $web_server_url.'/index.php?menuaction=workflow.ui_adminsource.form&p_id='. $proc_info['wf_p_id'],
			'img_code'					=> $img_default_dir.'code.gif',
			'link_admin_export'			=> $web_server_url.'/index.php?menuaction=workflow.WorkflowUtils.export&p_id='. $proc_info['wf_p_id'],
			'link_admin_roles'			=> $web_server_url.'/index.php?menuaction=workflow.ui_adminroles.form&p_id='. $proc_info['wf_p_id'],
			'img_roles'					=> $img_default_dir.'roles.png',
			'link_graph'				=> $web_server_url.'/index.php?menuaction=workflow.ui_adminactivities.show_graph&p_id=' . $proc_info['wf_p_id'],
			'img_process'				=> $img_default_dir.'Process.gif',
			'link_save_process'			=> $web_server_url.'/index.php?menuaction=workflow.ui_adminprocesses.save_process&id='. $proc_info['wf_p_id'],
			'img_save'					=> $img_default_dir.'save.png',
			'proc_id'					=> $p['proc_id'],
			'other_processes'			=> $otherProcesses
		);

		return $toolbar_data;
		}


	/**
	* Get process model files
	* @param  array $p process process data
	* @return array
	* @access public
	*/
	function get_model_files($p)
	{
		switch($p['type'])
		{
			case 'include'  : $path = PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'js' . SEP . 'adminsource' . SEP . 'inc';
							  break;
			case 'template' : $path = PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'js' . SEP . 'adminsource' . SEP . 'templates';
							  break;
			case 'js'       : $path = PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'js' . SEP . 'local';
							  break;
		}

		$col_file_name  = array();
		$files          = array();

		if ($handle = opendir($path))
		{
			while (false !== ($file_name = readdir($handle)))
			{
				if (!is_dir($path.SEP.$file_name))
				{
					$files[] = array('file_name'     => $file_name);
					$col_file_name[]  = $file_name;
				}
			}
		}

		array_multisort($col_file_name,SORT_ASC,$files);

		return $files;
	}

	/**
	* Get process php files
	* @param array $p process data
	* @return array
	* @access public
	*/
	function get_php_files($p)
	{
		$process_manager    = &Factory::newInstance('ProcessManager');
		$proc_info          = $process_manager->get_process($p['proc_id']);
		$activity_manager   = &Factory::newInstance('ActivityManager');
		$process_activities = $activity_manager->list_activities($p['proc_id'], 0, -1, 'wf_name__asc', '','',false);
		$path = GALAXIA_PROCESSES . SEP . $proc_info['wf_normalized_name'] . SEP . 'code' . SEP .'activities' . SEP;

		$files = array();

		$col_file_name  = array();
		$col_tamanho    = array();
		$col_modificado = array();

		foreach ($process_activities['data'] as $process_activity)
		{

			$file_name   = $process_activity['wf_normalized_name'].'.php';
			$activity_id = $process_activity['wf_activity_id'];
			$tamanho     = filesize($path.$file_name);
			$modificado  = date('d/m/Y H:i:s', filemtime($path.$file_name) );

			$files[] = array('file_name'     => $file_name,
							 'activity_id'   => $activity_id,
							 'tamanho' 	     => $this->_assignUnitToFileSize($tamanho),
							 'modificado'    => $modificado,
							 'tipo_atividade'=> $process_activity['wf_type'],
							 'interativa'    => $process_activity['wf_is_interactive'],
							 'proc_name'     => $proc_info['wf_normalized_name'],
							 'proc_id'		 => $proc_info['wf_p_id'],
							 'tipo_codigo'   => 'atividade'
			);

			$col_file_name[]  = $file_name;
			$col_tamanho[]    = $tamanho;
			$col_modificado[] = $modificado;
		}

		if (isset($p['sort']))
		{
			$order_by = ($p['order_by'] == 1) ? SORT_ASC : SORT_DESC;

			switch ($p['sort'])
			{
				case 'file_name' :  array_multisort($col_file_name,$order_by,$files);
									break;
				case 'tamanho'   :  array_multisort($col_tamanho,SORT_NUMERIC,$order_by,$files);
									break;
				case 'modificado':  array_multisort($col_modificado,$order_by,$files);
									break;

			}
		}

		return $files;
	}

	/**
	* Delete process file
	* @param array $p process data
	* @return array
	* @access public
	*/
	function delete_file($p)
	{
		if ((strpos($p['file_name'],'/') !== false) || (strpos($p['file_name'],'/') !== false))
			return 'Não foi possível executar a operação solicitada';
		$process_manager = &Factory::newInstance('ProcessManager');
		$proc_info = $process_manager->get_process($p['proc_id']);
		$file_name = $p['file_name'];
		$proc_name = $proc_info['wf_normalized_name'];
		$type      = $p['type'];
		if (strpos($file_name,'/')) return 'Nome de arquivo inválido.';
		if (!strlen($proc_name)) return 'ID de Processo inválido.';

    switch($type)
    {
        case 'atividade':
            $path =  'activities' . SEP . $file_name;
            break;
            case 'template':
            $path =  'templates' . SEP . $file_name;
            break;
        case 'include':
            $path = $file_name;
            break;
    		case 'resource':
				$path = GALAXIA_PROCESSES . '/' . $proc_info['wf_normalized_name'] . '/resources/' . $file_name;
            break;

    }

	   	if ($type == 'resource')
		{
			$complete_path = $path;
		}
		else
		{
			$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
		}

		if (file_exists($complete_path))
		{
			if (unlink($complete_path))
			{
				return 'Arquivo '.$file_name.' excluido com sucesso.';
			}
			else
			{
				return 'Não foi possivel excluir o arquivo '.$file_name.'.';
			}
    }
		else
		{
			return 'O arquivo '.$file_name.' não existe.';
		}
	}
	/**
	* Create process new file
	* @param array $p process
	* @return array
	* @access public
	*/
	function create_file($p)
	{
		$process_manager = &Factory::newInstance('ProcessManager');
		$proc_info = $process_manager->get_process($p['proc_id']);
		$file_name = $p['file_name'];
		$proc_name = $proc_info['wf_normalized_name'];
		$type      = $p['type'];

		if ((strpos($file_name,'/') !== false) || (strpos($file_name,'/') !== false))
		{
			return INVALID_FILE_NAME;
		}

		if (!strlen($proc_name))
		{
			return INVALID_PROCESS_ID;
		}

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

		if (file_exists($complete_path))
		{
			if (!$p['rewrite'])
			{
				return FILE_ALREADY_EXISTS;
			} else {
				unlink($complete_path);
			}
		}

		if ($fp = fopen($complete_path, 'w'))
		{
			$basepath = PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'js'.SEP.'adminsource';
			switch ($type)
			{
			case 'template':  $basepath = $basepath.SEP.'templates';
				break;
			case 'include' :  $basepath = $basepath.SEP.'inc';
				break;
			}

			if ($type == 'template' || $type == 'include')
			{
				if (file_exists($basepath.SEP.$p['modelo']))
				{
					fwrite($fp,file_get_contents($basepath.SEP.$p['modelo']));
				}
			}

			fclose($fp);
			return FILE_CREATED;
		}
		else
		{
			return FILE_NOT_CREATED;
		}
	}

	/**
	* Get process include files
	* @param  array $p process
	* @return array
	* @access public
	*/
	function get_include_files($p)
	{
		$process_manager    = &Factory::newInstance('ProcessManager');
		$proc_info          = $process_manager->get_process($p['proc_id']);
		$path = GALAXIA_PROCESSES . SEP . $proc_info['wf_normalized_name'] . SEP . 'code';


		$col_file_name  = array();
		$col_tamanho    = array();
		$col_modificado = array();
		$files          = array();

		if ($handle = opendir($path))
		{
			while (false !== ($file_name = readdir($handle)))
			{
				if (!is_dir($path.SEP.$file_name))
				{
					$tamanho     = filesize($path.SEP.$file_name);
					$modificado  = date('d/m/Y H:i:s', filemtime($path.SEP.$file_name) );

					$files[] = array('file_name'     => $file_name,
							 'tamanho' 	     => $this->_assignUnitToFileSize($tamanho),
							 'modificado'    => $modificado,
							 'proc_name'     => $proc_info['wf_normalized_name'],
							 'proc_id'		 => $proc_info['wf_p_id'],
							 'tipo_codigo'   => 'include'
					);

					$col_file_name[]  = $file_name;
					$col_tamanho[]    = $tamanho;
					$col_modificado[] = $modificado;
				}
			}
		}


		if (isset($p['sort']))
		{
			$order_by = ($p['order_by'] == 1) ? SORT_ASC : SORT_DESC;

			switch ($p['sort'])
			{
				case 'file_name' :  array_multisort($col_file_name,$order_by,$files);
									break;
				case 'tamanho'   :  array_multisort($col_tamanho,SORT_NUMERIC,$order_by,$files);
									break;
				case 'modificado':  array_multisort($col_modificado,$order_by,$files);
									break;

			}
		}

		return $files;
	}

	/**
	* Get process template files
	* @param array $p process data
	* @return array
	* @access public
	*/
	function get_template_files($p)
	{
		$process_manager    = &Factory::newInstance('ProcessManager');
		$proc_info          = $process_manager->get_process($p['proc_id']);
		$path = GALAXIA_PROCESSES . SEP . $proc_info['wf_normalized_name'] . SEP . 'code' . SEP .'templates';

		$col_file_name  = array();
		$col_tamanho    = array();
		$col_modificado = array();

		if ($handle = opendir($path))
		{
			while (false !== ($file_name = readdir($handle)))
			{
				if (!is_dir($path.SEP.$file_name))
				{
					$tamanho     = filesize($path.SEP.$file_name);
					$modificado  = date('d/m/Y H:i:s', filemtime($path.SEP.$file_name) );

					$files[] = array('file_name'     => $file_name,
							 'tamanho' 	     => $this->_assignUnitToFileSize($tamanho),
							 'modificado'    => $modificado,
							 'proc_name'     => $proc_info['wf_normalized_name'],
							 'proc_id'		 => $proc_info['wf_p_id'],
							 'tipo_codigo'   => 'template'
					);

					$col_file_name[]  = $file_name;
					$col_tamanho[]    = $tamanho;
					$col_modificado[] = $modificado;

				}
			}
		}

		if (isset($p['sort']))
    {
        $order_by = ($p['order_by'] == 1) ? SORT_ASC : SORT_DESC;

        switch ($p['sort'])
        {
            case 'file_name' :  array_multisort($col_file_name,$order_by,$files);
                                break;
            case 'tamanho'   :  array_multisort($col_tamanho,SORT_NUMERIC,$order_by,$files);
                                break;
            case 'modificado':  array_multisort($col_modificado,$order_by,$files);
                                break;

        }
    }

		return $files;
	}

	/**
	* Upload process resource
	*
	* @param array $p process
	* @return array
	* @access public
	*/
	function upload_resource($p)
	{
		$process_manager = &Factory::newInstance('ProcessManager');
		$proc_info = $process_manager->get_process($p['proc_id']);
		$file_name = basename($_FILES['resource_file']['name']);

		$base_path = GALAXIA_PROCESSES . '/' . $proc_info['wf_normalized_name'] . '/resources';
		$path = $base_path . '/' . $file_name;


		if (!strlen($file_name))
			return 'É necessário selecionar um arquivo.';

		if (!is_dir($base_path))
			return 'A pasta de resources ainda não foi criada no servidor.';

		if (file_exists($path))
			return 'O arquivo '.$file_name.' já existe no servidor.';

		if (move_uploaded_file($_FILES['resource_file']['tmp_name'],$path))
			return 'Upload realizado com sucesso.';
		else
			return 'Não foi possível realizar upload do arquivo '.$file_name.'.';
	}

	/**
	* Export process file
	*
	* @access public
	*/
	function export_file()
	{
		if (strpos($_REQUEST['file_name'],'/') !== false)
			return 'Não foi possível executar a operação solicitada';
		$process_manager    = &Factory::newInstance('ProcessManager');
		$proc_info          = $process_manager->get_process($_REQUEST['proc_id']);

		$proc_name = $proc_info['wf_normalized_name'];
		$file_name = $_REQUEST['file_name'];
		$type      = $_REQUEST['type'];

		switch($type)
		{
			case 'atividade':
				$path =  'activities' . SEP . $file_name;
				break;
			case 'template':
				$path =  'templates' . SEP . $file_name;
				break;
			case 'resource':
				$path = 'resources' . SEP . $file_name;
				break;
			case 'include':
				$path = $file_name;
				break;
			default:
				exit;
		}

		if ($type == 'resource')
			$completePath = GALAXIA_PROCESSES . SEP . $proc_name . SEP . $path;
		else
			$completePath = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;

		Factory::getInstance('ResourcesRedirector')->show($completePath, 'application/force-download');
		exit;
	}

	/**
	* Get process resources files
	*
	* @param array $p process
	* @return array
	* @access public
	*/
	function get_resource_files($p)
	{
		$process_manager    = &Factory::newInstance('ProcessManager');
		$proc_info          = $process_manager->get_process($p['proc_id']);

		$path       = GALAXIA_PROCESSES . '/' . $proc_info['wf_normalized_name'] . '/resources';

		if (!is_dir($path))
			mkdir($path, 0770);

		$col_file_name  = array();
		$col_tamanho    = array();
		$col_modificado = array();
		$col_tipo 		= array();

		if ($handle = opendir($path))
		{
			while (false !== ($file_name = readdir($handle)))
			{
				if (!is_dir($path.SEP.$file_name))
				{
					$tipo        = mime_content_type($path.SEP.$file_name);
					$tamanho     = filesize($path.SEP.$file_name);
					$modificado  = date('d/m/Y H:i:s', filemtime($path.SEP.$file_name) );

					$files[] = array(
							 'file_name'     => $file_name,
							 'proc_name'     => $proc_info['wf_normalized_name'],
							 'proc_id'		 => $proc_info['wf_p_id'],
							 'tamanho' 	     => $this->_assignUnitToFileSize($tamanho),
							 'tipo' 	     => $tipo,
							 'modificado'    => $modificado,
							 'tipo_arquivo'  => 'resource'
					);

					$col_file_name[]  = $file_name;
					$col_tamanho[]    = $tamanho;
					$col_modificado[] = $modificado;
					$col_tipo[]       = $tipo;

				}
			}
		}

		if (isset($p['sort']))
		{
			$order_by = ($p['order_by'] == 1) ? SORT_ASC : SORT_DESC;

			switch ($p['sort'])
			{
				case 'file_name' :  array_multisort($col_file_name,$order_by,$files);
									break;
				case 'tamanho'   :  array_multisort($col_tamanho,SORT_NUMERIC,$order_by,$files);
									break;
				case 'modificado':  array_multisort($col_modificado,$order_by,$files);
									break;
				case 'tipo'      :  array_multisort($col_tipo,$order_by,$files);
									break;

			}
		}

		return $files;
	}

}
?>
