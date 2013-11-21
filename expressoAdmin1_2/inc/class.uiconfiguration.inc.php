<?php

    class uiconfiguration
    {

         var $public_functions = array
		(
			'index' => True
		);

	var $functions;
        var $boconfiguration;

        function uiconfiguration()
        {

  

          if (function_exists('CreateObject'))
                {
                        $this->functions = CreateObject('expressoAdmin1_2.functions');
                        $this->boconfiguration = CreateObject('expressoAdmin1_2.boconfiguration');

                        if(!@is_object($GLOBALS['phpgw']->js))
                        {
                                $GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
                        }
                        $GLOBALS['phpgw']->js->validate_file('jscode','connector','expressoAdmin1_2');#diretorio, arquivo.js, aplicacao
                        $GLOBALS['phpgw']->js->validate_file('jscode','finder','expressoAdmin1_2');
			$GLOBALS['phpgw']->js->validate_file('jscode','configurations','expressoAdmin1_2');
			$GLOBALS['phpgw']->js->validate_file('modal','modal','expressoAdmin1_2');
                        $GLOBALS['phpgw']->js->validate_file('jscode','expressoadmin','expressoAdmin1_2');
                }       
        }


        function index()
        { 


            /* Begin:  Check manager access */
            $account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
            $acl = $this->functions->read_acl($account_lid);
            $contexts = $acl['contexts'];
            foreach ($acl['contexts_display'] as $index=>$tmp_context) {
                    $context_display .= '<br>'.$tmp_context;
            }

          
            if (!$this->functions->check_acl($account_lid,'configurations')) {
                    $GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
            }
            /* End: Check manager access */


            unset($GLOBALS['phpgw_info']['flags']['noheader']);
            unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
            $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Configurations');
            $GLOBALS['phpgw']->common->phpgw_header();


            $template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);

            $template->set_file(
              Array(
                'configurationTPL' => 'configuration.tpl',
                'limitByUserModal' => 'configuration_limit_by_user_modal.tpl', 
                'limitByGroupModal' => 'configuration_limit_by_group_modal.tpl',
                'blockEmailForInstitutionalAccountModal' => 'configuration_block_email_for_institutional_account.tpl'
                 )
            );

             $template->set_var($this->functions->make_dinamic_lang($template, 'limitByUserModal'));
             $template->set_var($this->functions->make_dinamic_lang($template, 'limitByGroupModal'));
             $template->set_var($this->functions->make_dinamic_lang($template, 'blockEmailForInstitutionalAccountModal'));
             $template->set_block('configurationTPL','body');

             $configsGlobais = $this->boconfiguration->getGlobalConfiguratons();

             foreach ($contexts as $index=>$context)
                $comboOrganizations .= $this->boconfiguration->getSelectOrganizations($context);

             if($configsGlobais['expressoMail_block_institutional_comunication'] == 'true')
                $template->set_var('checkedBlockCommunication', 'checked = "true"');
             
             $template->set_var('valueMaximumRecipient', $configsGlobais['expressoAdmin_maximum_recipients']);
             $template->set_var('optionsOrganizations', $comboOrganizations);

             if($this->functions->check_acl($account_lid,'edit_and_remove_maximum_number_of_recipients_by_user'))
                $template->set_var('tableLimitRecipientsByUser', $this->boconfiguration->getTableRulesLimitRecipientsByUser(true));
             else
                 $template->set_var('tableLimitRecipientsByUser', $this->boconfiguration->getTableRulesLimitRecipientsByUser());

             if($this->functions->check_acl($account_lid,'edit_and_remove_maximum_number_of_recipients_by_group'))
                $template->set_var('tableLimitRecipientsByGroup', $this->boconfiguration->getTableRulesLimitRecipientsByGroup(true));
             else
                $template->set_var('tableLimitRecipientsByGroup', $this->boconfiguration->getTableRulesLimitRecipientsByGroup());

             $template->set_var('optionsBlockEmailForInstitutionalAcounteExeption', $this->boconfiguration->getOptionsBlockEmailForInstitutionalAcounteExeption());

             $template->set_var($this->functions->make_dinamic_lang($template, 'body'));

            //Inicio validando acls
            if(!$this->functions->check_acl($account_lid,'add_blocking_sending_email_to_shared_accounts_exception'))
                 $template->set_var('acl_add_exception_for_the_blocking', 'disabled = true');
            else
                  $template->set_var('acl_add_exception_for_the_blocking', '');

            if(!$this->functions->check_acl($account_lid,'active_blocking_sending_email_to_shared_accounts'))
                 $template->set_var('acl_inputCheckAllUserBlockCommunication', 'disabled = true');
            else
                  $template->set_var('acl_inputCheckAllUserBlockCommunication', '');
            
            if(!$this->functions->check_acl($account_lid,'edit_and_remove_blocking_sending_email_to_shared_accounts_exception'))
                 $template->set_var('acl_edit_and_remove_blocking_sending_email', 'disabled = true');
            else
                  $template->set_var('acl_edit_and_remove_blocking_sending_email', '');
            
            if(!$this->functions->check_acl($account_lid,'add_maximum_number_of_recipients_by_group'))
                 $template->set_var('acl_Limit_by_group', 'disabled = true');
            else
                  $template->set_var('acl_Limit_by_group', '');
            
            if(!$this->functions->check_acl($account_lid,'add_maximum_number_of_recipients_by_user'))
                 $template->set_var('acl_Limit_by_user', 'disabled = true');
            else
                  $template->set_var('acl_Limit_by_user', '');

            if(!$this->functions->check_acl($account_lid,'edit_maximum_number_of_recipients_generally'))
                 $template->set_var('acl_inputTextMaximumRecipientGenerally', 'disabled = true');
            else
                  $template->set_var('acl_inputTextMaximumRecipientGenerally', '');

            //Fim validando acls


             $limitByUserModalId = 'limitByUserModal';
             $template->set_var('limitByUserModalId', $limitByUserModalId);
             $limitByUserModalTpl = $template->fp('out','limitByUserModal');

             $limitByGroupModalId = 'limitByGroupModal';
             $template->set_var('limitByGroupModalId', $limitByGroupModalId);
             $limitByGroupModalTpl = $template->fp('out','limitByGroupModal');

             $blockEmailForInstitutionalAccountId = 'blockEmailForInstitutionalAccountModal';
             $template->set_var('blockEmailForInstitutionalAccountId', $blockEmailForInstitutionalAccountId);
             $blockEmailForInstitutionalAccountModalTpl = $template->fp('out','blockEmailForInstitutionalAccountModal');
             
             

             $var = Array(
                'th_bg'						=> $GLOBALS['phpgw_info']['theme']['th_bg'],
                'back_url'					=> $GLOBALS['phpgw']->link('/expressoAdmin1_2/index.php'),
                'context_display'                               => $context_display,
                'limitByUserModal' => $limitByUserModalTpl,
                'onclickLimitByUserModal' => "modal(\"$limitByUserModalId\",\"create\")",
                'limitByGroupModal' => $limitByGroupModalTpl,
                'onclickLimitByGroupModal' => "modal(\"$limitByGroupModalId\",\"create\")",
                'blockEmailForInstitutionalAccountModal' => $blockEmailForInstitutionalAccountModalTpl,
                'onclickBlockEmailForInstitutionalAccountModal' => "modal(\"$blockEmailForInstitutionalAccountId\",\"create\")",
		      );

             $template->set_var($var);

       
             $template->pfp('out','body');

        }


        

    }


?>
