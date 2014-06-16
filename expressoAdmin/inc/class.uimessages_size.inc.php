<?php
	/***********************************************************************************\
	* Expresso Administração															*
	* by Prognus Software Livre (prognus@prognus.com.br, airton@prognus.com.br)      	*
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\***********************************************************************************/

	if( !defined('PHPGW_API_INC') )
		define('PHPGW_API_INC','../phpgwapi/inc');

	class uimessages_size
	{
		var $public_functions = array
		(
			'index' => True
		);
		
		var $functions;
        var $current_config;
		var $bo;

		/**
         * Construtor
         */
		function uimessages_size()
		{		
			$this->bo = CreateObject('expressoAdmin.bomessages_size');
			if (function_exists('CreateObject'))
			{
				$this->functions = CreateObject('expressoAdmin.functions');
				
				if(!@is_object($GLOBALS['phpgw']->js))
				{
					$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
				}
				$GLOBALS['phpgw']->js->validate_file('jscode','connector','expressoAdmin'); #diretorio, arquivo.js, aplicacao
				$GLOBALS['phpgw']->js->validate_file('jscode','finder','expressoAdmin');
				$GLOBALS['phpgw']->js->validate_file('jscode','messages_size','expressoAdmin');
				$GLOBALS['phpgw']->js->validate_file('modal','modal','expressoAdmin');
				$GLOBALS['phpgw']->js->validate_file('jscode','expressoadmin','expressoAdmin');
                                $c = CreateObject('phpgwapi.config','expressoAdmin');
                                $c->read_repository();
                                $this->current_config = $c->config_data;
			}
		}

		
		/**
		 * @abstract Cria a página principal da funcionalidade.
         */
		function index()
		{
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(
						Array(
								'messages_size'			=> 'messages_size.tpl',
								'messages_size_modal'	=> 'messages_size_modal.tpl'
							)
						);
			$p->set_block('messages_size','body');
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
			$p->set_var($this->functions->make_dinamic_lang($p, 'messages_size_modal'));
		
		
			/* Início da verificação ACL */
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($account_lid);
			$contexts = $acl['contexts'];
			$context_display = "";

			if( isset($acl['contexts_display']) )
			{
				foreach( $acl['contexts_display'] as $index=>$tmp_context )
				{
					$context_display .= '<br />'.$tmp_context;
				}
			}
			
			if (!$this->functions->check_acl($account_lid,'messages_size')) {
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
			/* Fim da verificação ACL */
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Messages Size');
			$GLOBALS['phpgw']->common->phpgw_header();

			/* Begin: set modal */	 
			$functions = CreateObject('expressoAdmin.functions');
			
			$combo_manager_org = "";

			foreach ($contexts as $index=>$context)
			{
				$combo_manager_org .= $this->functions->get_organizations($context);
			}
			
			$combo_all_orgs = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context']);
			
			$p->set_var('manager_organizations', $combo_manager_org);
			$p->set_var('all_organizations', $combo_all_orgs);
			
			$modal_id = 'messages_size_modal';
			$p->set_var('modal_id', $modal_id);
                        

			$messages_size_modal_tpl = $p->fp('out','messages_size_modal');
            /* End: set modal */
			
			$var = Array(
				'th_bg'						=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'					=> $GLOBALS['phpgw']->link('/expressoAdmin/index.php'),
				'context_display'			=> $context_display,
				'messages_size_modal' => $messages_size_modal_tpl,
				'onclick_create_messages_size' => "modal(\"$modal_id\",\"create\")"
			);
			$p->set_var($var);
			
			$default_value = '';
			$rules = '';
            $all_rules = $this->bo->get_all_rules();

            $all_rules_count = count($all_rules);
			for ($i = 0; $i<$all_rules_count; ++$i)
			{	
				/* Verificação para não listar a regra default */
				if($all_rules[$i]['email_recipient'] != 'default') 
				{
					$name_link = (string)str_replace(" ", "%", $all_rules[$i]['email_recipient']);
					$rules .= "<tr class='normal' onMouseOver=this.className='selected' onMouseOut=this.className='normal'><td onClick=javascript:edit_messages_size('" . $name_link . "')>" . $all_rules[$i]['email_recipient'] . "</td><td onClick=edit_messages_size('$name_link')>" . $all_rules[$i]['email_max_recipient'] . " MB</td><td align='center' onClick=delete_messages_size('$name_link')><img HEIGHT='16' WIDTH='16' src=./expressoAdmin/templates/default/images/delete.png></td></tr>";
				}
			}
	
			$default_value = $this->bo->get_default_rule();
				
			$p->set_var('list_rules', $rules);
			$p->set_var('default_value', $default_value);
			$p->pfp('out','body');
		}
	} // end class uimessages_size
?>
