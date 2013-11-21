<?php
	/*************************************************************************************\
	* Expresso Relatório                										         *
	* by Elvio Rufino da Silva (elviosilva@yahoo.com.br, elviosilva@cepromat.mt.gov.br)  *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	*************************************************************************************/

	class uireports
	{
		var $public_functions = array
		(
			'report_config_global'				=> True,
			'get_user_info'						=> True,
			'css'								=> True
		);

		var $nextmatchs;
		var $user;
		var $functions;
		var $current_config;
		var $ldap_functions;
		var $db_functions;
		var $imap_functions;
		
		function uireports()
		{
			$this->user			= CreateObject('reports.user');
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');
			$this->functions	= CreateObject('reports.functions');
			$this->ldap_functions = CreateObject('reports.ldap_functions');
			$this->db_functions = CreateObject('reports.db_functions');
			$this->fpdf = CreateObject('reports.fpdf'); // Class para PDF
						
			$c = CreateObject('phpgwapi.config','reports'); // cria o objeto relatorio no $c
			$c->read_repository(); // na classe config do phpgwapi le os dados da tabela phpgw_config where relatorio, como passagem acima
			$this->current_config = $c->config_data; // carrega os dados em do array no current_config

			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('jscode','cc','reports');
		}

		function report_config_global()
		{
			if ($GLOBALS['phpgw']->acl->check('site_config_access',1,'admin'))
			{
				$GLOBALS['phpgw']->redirect_link('/index.php');
			}

			$t = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir($appname));
			$t->set_unknowns('keep');
			$t->set_file(array('config' => 'config.tpl'));
			$t->set_block('config','header','header');
			$t->set_block('config','body','body');
			$t->set_block('config','footer','footer');

			$c = CreateObject('phpgwapi.config',$config_appname);
			$c->read_repository();

			if ($c->config_data)
			{
				$current_config = $c->config_data;
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$t->set_var('title',lang('Site Configuration'));
			$t->set_var('action_url',$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname));
			$t->set_var('th_bg',     $GLOBALS['phpgw_info']['theme']['th_bg']);
			$t->set_var('th_text',   $GLOBALS['phpgw_info']['theme']['th_text']);
			$t->set_var('row_on',    $GLOBALS['phpgw_info']['theme']['row_on']);
			$t->set_var('row_off',   $GLOBALS['phpgw_info']['theme']['row_off']);
			$t->pparse('out','header');

			$vars = $t->get_undefined('body');

			$GLOBALS['phpgw']->hooks->single('config',$appname);

			foreach($vars as $value)
			{
				$valarray = explode('_',$value);
				$type = array_shift($valarray);
				$newval = implode(' ',$valarray);
				switch ($type)
				{
					case 'lang':
						$t->set_var($value,lang($newval));
						break;
					case 'value':
						$newval = str_replace(' ','_',$newval);
						/* Don't show passwords in the form */
						if(ereg('passwd',$value) || ereg('password',$value) || ereg('root_pw',$value))
						{
							$t->set_var($value,'');
						}
						else
						{
							$t->set_var($value,htmlspecialchars($current_config[$newval]));
						}
						break;
					case 'selected':
						$configs = array();
						$config  = '';
						$newvals = explode(' ',$newval);
						$setting = end($newvals);
						for ($i=0;$i<(count($newvals) - 1); ++$i)
						{
							$configs[] = $newvals[$i];
						}
						$config = implode('_',$configs);
						/* echo $config . '=' . $current_config[$config]; */
						if ($current_config[$config] == $setting)
						{
							$t->set_var($value,' selected');
						}
						else
						{
							$t->set_var($value,'');
						}
						break;
					case 'hook':
						$newval = str_replace(' ','_',$newval);
						if(function_exists($newval))
						{
							$t->set_var($value,$newval($current_config));
						}
						else
						{
							$t->set_var($value,'');
						}
						break;
					default:
					$t->set_var($value,'');
					break;
				}
			}
			$mbstring = ini_get_all("mbstring");
			$t->set_var('mbstring_value',(extension_loaded('mbstring')  || function_exists('dl') && @dl(PHP_SHLIB_PREFIX.'mbstring.'.PHP_SHLIB_SUFFIX)? 
				$mbstring['mbstring.func_overload']['global_value'] != ''? 
					'mbstring.func_overload = '.$mbstring['mbstring.func_overload']['global_value']:'' : ''));

			$t->pfp('out','body');

			$t->set_var('lang_cancel', lang('Back'));
			$t->pfp('out','footer');
		}

		function row_action($action,$type,$account_id)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction' => 'reports.uireports.'.$action.'_'.$type,
				'account_id' => $account_id
			)).'"> '.lang($action).' </a>';
		}

		function css()
		{
			$appCSS = 
			'th.activetab
			{
				color:#000000;
				background-color:#D3DCE3;
				border-top-width : 1px;
				border-top-style : solid;
				border-top-color : Black;
				border-left-width : 1px;
				border-left-style : solid;
				border-left-color : Black;
				border-right-width : 1px;
				border-right-style : solid;
				border-right-color : Black;
				font-size: 12px;
				font-family: Tahoma, Arial, Helvetica, sans-serif;
			}
			
			th.inactivetab
			{
				color:#000000;
				background-color:#E8F0F0;
				border-bottom-width : 1px;
				border-bottom-style : solid;
				border-bottom-color : Black;
				font-size: 12px;
				font-family: Tahoma, Arial, Helvetica, sans-serif;				
			}
			
			.td_left {border-left:1px solid Gray; border-top:1px solid Gray; border-bottom:1px solid Gray;}
			.td_right {border-right:1px solid Gray; border-top:1px solid Gray; border-bottom:1px solid Gray;}
			
			div.activetab{ display:inline; }
			div.inactivetab{ display:none; }';
			
			return $appCSS;
		}

	}
?>
