<?php
	/*************************************************************************************\
	* Expresso Administração                 						           			 *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  	 *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	\*************************************************************************************/

	class totalsessions
	{
		var $functions;
		var $template;
		var $bo;
		var $public_functions = array(
			'show_total_sessions'	=> True
		);

		function totalsessions()
		{
			$this->functions = createobject('expressoAdmin.functions');
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$tmp = $this->functions->read_acl($account_lid);
			$manager_context = $tmp[0]['context'];
			// Verifica se o administrador tem acesso.
			if (!$this->functions->check_acl($account_lid,'view_global_sessions'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
			
			$this->template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		}

		function show_total_sessions()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = 'ExpressoAdmin - '.lang('Total Sessions');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->template->set_file('template','totalsessions.tpl');
			$this->template->set_block('template','list','list');
			
			$this->template->set_var($this->functions->make_dinamic_lang($this->template, 'list'));

			$total = $this->get_total_sessions();

			$this->template->set_var('back_url', $GLOBALS['phpgw']->link('/expressoAdmin/index.php'));
			$this->template->set_var('total', $total);

			$this->template->pfp('out','list');
		}
		
		function get_total_sessions()
		{
			$values = array();
                        
                        //files memcache
                        switch(ini_get('session.save_handler')){
                            case "memcache" : $mem = new Memcache();
                                $arrayretorno = $mem->getStats();
                                break;
                            case "files":
                                default :

                                    $dir = opendir($path = ini_get('session.save_path'));
                                    $total = 0;
                                               
                                    while ($file = readdir($dir))
                                    {
                                            $session = file_get_contents( $path . '/' . $file, false, null, 0, 50 );
                                            
                                            if( substr($file,0,5) != 'sess_' || 
                                                !$session || // happens if webserver runs multiple user-ids
                                                strstr( $session, 'phpgw_sess' ) === FALSE ) 
                                            {
                                                    continue;
                                            }

                                            ++$total;
                                    }
                                    
                                    closedir($dir);

                                    return $total;
                
                        }
                }
        }