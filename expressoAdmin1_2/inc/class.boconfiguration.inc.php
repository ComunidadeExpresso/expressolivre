<?php
require_once 'class.soconfiguration.inc.php';
require_once 'class.ldap_functions.inc.php';
require_once 'class.functions.inc.php';
require_once 'class.db_functions.inc.php';

    class boconfiguration
    {

        var $soConfiguration;
        var $ldapFunctions;
        var $functions;
        var $dbFunctions;

        function boconfiguration()
        {
                    $this->soConfiguration = new soconfiguration();
                    $this->ldapFunctions = new ldap_functions();
                    $this->functions = new functions();
                    $this->dbFunctions = new db_functions();
        }
                   
        /**
         * Busca as organizações do ldap
         * @param string $context
         * @param string $selected
         * @param bool $recursive
         * @return string Ex. <option value='xxx'>xxx</option>
         */
       function getSelectOrganizations($context, $selected='', $recursive = true)
       {
           $s = CreateObject('phpgwapi.sector_search_ldap');
           return ($recursive ?
                        $s->get_organizations($context, $selected, false ,false) :
                        $s->get_sectors($selected, false, false));
       }

       /**
        * Busca usuarios e retorna em options para o select
        * @param <array> $params [filter] = busca, [context] = contexto do ldap
        * @return string Ex. <option value='xxx'>xxx</option>
        */
       function searchUsersForSelect($params)
       {

          $retornoLDAP = $this->ldapFunctions->get_available_users3($params);

          return $retornoLDAP['users'];
       }

       /**
        * Busca Grupos e retorna em options para o select
        * @param <array> $params [filter] = busca, [context] = contexto do ldap
        * @return string Ex. <option value='xxx'>xxx</option>
        */
       function searchGroupsForSelect($params)
       {

          $retornoLDAP = $this->ldapFunctions->get_available_users3($params);

          return $retornoLDAP['groups'];
       }

        /**
        * Busca usuarios e Grupos e retorna em options para o select
        * @param <array> $params [filter] = busca, [context] = contexto do ldap
        * @return string Ex. <option value='xxx'>xxx</option>
        */
       function searchUsersAndGroupsForSelect($params)
       {

          $returnLDAP = $this->ldapFunctions->get_available_users3($params);
          $options = '';
          if($returnLDAP['users2'] != null)
          {
              $options .= '<option value ="-1" disabled>----------------------'.$this->functions->lang('users').'----------------------- </option>';
              $options .=$returnLDAP['users2'];
          }
          if($returnLDAP['groups2'] != null )
          {
              $options .= '<option value ="-1" disabled>----------------------'.$this->functions->lang('groups').'----------------------- </option>';
              $options .= $returnLDAP['groups2'];
          } 
          return $options;
       }

       /**
        * Busca Contas Institucionais e retorna em options para o select
        * @param <array> $params [filter] = busca, [context] = contexto do ldap
        * @return string Ex. <option value='xxx'>xxx</option>
        */
       function searchInstitutionalAccountsForSelect($params)
       {
          $retornoLDAP = $this->ldapFunctions->get_available_shared_account($params);

          return $retornoLDAP;
       }

       /**
        * Cria ou sobrescreve regra de limit de destinatarios por usuario.
        * @param <array> $params
        * @return <array> ['status'] = True ou False, ['msg'] = Mensagem de erro;
        */
       function createLimitRecipientsByUser($params)
       {

           if(!$params['selectUsersInRule'])
                return array('status' => false , 'msg' => 'no user  selected');

           if(!$params['inputTextMaximumRecipientsUserModal'])
               $params['inputTextMaximumRecipientsUserModal'] = 0 ;

            foreach ($params['selectUsersInRule'] as $userInRule)
            {
                //Verifica se este usuario Já tem uma regra
                $userInRuleDB = $this->soConfiguration->getRuleInDb('where configuration_type = \'LimitRecipient\' AND email_user = \''.$userInRule.'\' AND email_user_type = \'U\' ');
                
                if(count($userInRuleDB) > 0)
                {
                     $fields = array(
                            'email_user' => $userInRule,
                            'configuration_type' => 'LimitRecipient',
                            'email_max_recipient' => $params['inputTextMaximumRecipientsUserModal'],
                            'email_user_type' => 'U'
                           );

                      if(!$this->soConfiguration->updatetRuleInDb($userInRuleDB[0]['id'],$fields))
                            return array('status' => false , 'msg' => 'Error on Updating');
                      else
                            $this->dbFunctions->write_log ('Update Limit Recipients By User', $userInRule);

                }
                else
                {
                    $fields = array(
                                'email_user' => $userInRule,
                                'configuration_type' => 'LimitRecipient',
                                'email_max_recipient' => $params['inputTextMaximumRecipientsUserModal'],
                                'email_user_type' => 'U'
                               );

                    if(!$this->soConfiguration->insertRuleInDb($fields))
                            return array('status' => false , 'msg' => 'Error on insert');
                    else
                            $this->dbFunctions->write_log ('Create Limit Recipients By User', $userInRule);
               
                }

            }

           return array('status' => true);
       }

        /**
        * Cria ou sobrescreve regra de limit de destinatarios por Grupo.
        * @param <array> $params
        * @return <array> ['status'] = True ou False, ['msg'] = Mensagem de erro;
        */
       function createLimitRecipientsByGroup($params)
       {

           if(!$params['selectGroupsInRule'])
                return array('status' => false , 'msg' => 'no user  selected');

           if(!$params['inputTextMaximumRecipientsGroupModal'])
               $params['inputTextMaximumRecipientsGroupModal'] = 0 ;

            foreach ($params['selectGroupsInRule'] as $groupInRule)
            {
                //Verifica se este usuario Já tem uma regra
                $groupInRuleDB = $this->soConfiguration->getRuleInDb('where configuration_type = \'LimitRecipient\' AND email_user = \''.$groupInRule.'\' AND email_user_type = \'G\' ');

                if(count($groupInRuleDB) > 0)
                {
                     $fields = array(
                            'email_user' => $groupInRule,
                            'configuration_type' => 'LimitRecipient',
                            'email_max_recipient' => $params['inputTextMaximumRecipientsGroupModal'],
                            'email_user_type' => 'G'
                           );

                      if(!$this->soConfiguration->updatetRuleInDb($groupInRuleDB[0]['id'],$fields))
                            return array('status' => false , 'msg' => 'Error on Updating');
                      else
                            $this->dbFunctions->write_log ('Update Limit Recipients By Group', $groupInRule);

                }
                else
                {
                    $fields = array(
                                'email_user' => $groupInRule,
                                'configuration_type' => 'LimitRecipient',
                                'email_max_recipient' => $params['inputTextMaximumRecipientsGroupModal'],
                                'email_user_type' => 'G'
                               );

                    if(!$this->soConfiguration->insertRuleInDb($fields))
                            return array('status' => false , 'msg' => 'Error on insert');
                    else
                            $this->dbFunctions->write_log ('Create Limit Recipients By Group', $groupInRule);


                }

            }

           return array('status' => true);
       }

       /**
        * Busca todas as regras LimitRecipientsByUser para a tabela
        * @return string Linhas e celulas de tabela
        */
       function getTableRulesLimitRecipientsByUser($edit = false)
       {
           $filter = 'WHERE configuration_type = \'LimitRecipient\' AND email_user_type = \'U\' ORDER BY email_user';
           $fields = array('id','email_user','email_max_recipient');
           $rules = $this->soConfiguration->getRuleInDb($filter, $fields);

           $return = '';
           $zebra = 1;
           foreach ($rules as $rule)
           {
               //Resgata informaçoes do usuario do ldap
               if($zebra == 0)
               {
                    $return.= '<tr bgcolor='.$GLOBALS['phpgw_info']['theme']['row_on'].' >';
                    $zebra = 1;
               }
               else
               {
                   $return.= '<tr bgcolor='.$GLOBALS['phpgw_info']['theme']['row_off'].' >';
                   $zebra = 0;
               }
               $return.= '<td>'.utf8_encode($this->ldapFunctions->get_user_cn_by_uid($rule['email_user'])).'</td>';

               if($rule['email_max_recipient'] > 0)
                    $return.= '<td>'.$rule['email_max_recipient'].'</td>';
               else
                    $return.= '<td>'.$this->functions->make_lang('lang_no_limit').'</td>';
               if($edit == true)
               {
                   $return.= '<td><a href="javascript:editLimitRecipientesByUser(\''.$rule['id'].'\')" >'.$this->functions->make_lang('lang_edit').'</a></td>';
                   $return.= '<td><a href="javascript:removeLimitRecipientsByUser(\''.$rule['id'].'\')" >'.$this->functions->make_lang('lang_remove').'</a></td>';
               }
               else
               {
                   $return.= '<td>'.$this->functions->make_lang('lang_edit').'</td>';
                   $return.= '<td>'.$this->functions->make_lang('lang_remove').'</td>';

               }

                   $return.= '</tr>';
           }
       
           return utf8_decode($return);
       }

        /**
        * Busca todas as regras LimitRecipientsByGroup para a tabela
        * @return string tabela
        */
       function getTableRulesLimitRecipientsByGroup($edit = false)
       {
           $filter = 'WHERE configuration_type = \'LimitRecipient\' AND email_user_type = \'G\' ORDER BY email_user';
           $fields = array('id','email_user','email_max_recipient');
           $rules = $this->soConfiguration->getRuleInDb($filter, $fields);

           $return = '';
           $zebra = 1;
           foreach ($rules as $rule)
           {
               //Resgata informaçoes do usuario do ldap
                if($zebra == 0)
               {
                    $return.= '<tr bgcolor='.$phpgw_info['theme']['row_on'].' >';
                    $zebra = 1;
               }
               else
               {
                   $return.= '<tr bgcolor='.$phpgw_info['theme']['row_off'].' >';
                   $zebra = 0;
               }
               $return.= '<td>'.utf8_encode($this->ldapFunctions->get_group_cn_by_gidnumber($rule['email_user'])).'</td>';

               if($rule['email_max_recipient'] > 0)
                    $return.= '<td>'.$rule['email_max_recipient'].'</td>';
               else
                    $return.= '<td>'.$this->functions->make_lang('lang_no_limit').'</td>';

               if($edit == true)
               {
                   $return.= '<td><a href="javascript:editLimitRecipientesByGroup(\''.$rule['id'].'\')" >'.$this->functions->make_lang('lang_edit').'</a></td>';
                   $return.= '<td><a href="javascript:removeLimitRecipientsByGroup(\''.$rule['id'].'\')" >'.$this->functions->make_lang('lang_remove').'</a></td>';
               }
               else
               {
                   $return.= '<td>'.$this->functions->make_lang('lang_edit').'</td>';
                   $return.= '<td>'.$this->functions->make_lang('lang_remove').'</td>';
      
               }
               $return.= '</tr>';
           }
                     
           return utf8_decode($return);
       }

       /**
        * Remove regra
        * @param array $params [id]
        * @return array [status] = true or false,[msg] = mensagem de erro
        */
       function removeLimitRecipientsByUser($params)
       {

           $rule = $this->soConfiguration->getRuleInDb('Where id = \''.$params['id'].'\'');

          if($this->soConfiguration->removeRuleInDb($params['id']))
          {
            $this->dbFunctions->write_log ('Removed Limit Recipients By User', $rule['0']['email_user']);
            return array('status' => true);
          }
          else
            return array('status' => false , 'msg' => 'Error on remove');
       }

       /**
        * Remove regra
        * @param array $params [id]
        * @return array [status] = true or false,[msg] = mensagem de erro
        */
       function removeLimitRecipientsByGroup($params)
       {

          $rule = $this->soConfiguration->getRuleInDb('Where id = \''.$params['id'].'\'');

          if($this->soConfiguration->removeRuleInDb($params['id']))
          {
            $this->dbFunctions->write_log ('Removed Limit Recipients By Group', $rule['0']['email_user']);
            return array('status' => true);
          }
          else
            return array('status' => false , 'msg' => 'Error on remove');
       }

       /**
        * Remove regra
        * @param array $params [id]
        * @return array [status] = true or false,[msg] = mensagem de erro
        */
       function removeBlockEmailForInstitutionalAcounteExeption($params)
       {
          $recipient = explode(',', $params['recipient']);

          if($params['recipient'] == '*')
              $recipient['1'] = 'A';

          $filter = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user = \''.$recipient['0'].'\' AND email_user_type = \''.$recipient['1'].'\' ';
          $fields = array('id','email_user');
          $rules = $this->soConfiguration->getRuleInDb($filter, $fields);         

          foreach ($rules as $rule)
          {
             if(!$this->soConfiguration->removeRuleInDb($rule['id']))
                  return array('status' => false , 'msg' => 'Error on remove');
             else
                  $this->dbFunctions->write_log ('Removed BlockEmail For Institutional AcounteExeption', $rule['email_user']);
          }
         
            return array('status' => true);
        
       }
       
       /**
        * Carrega as informaçoes da regra para ser editada
        * @param array $params [id] = Id da regra
        * @return array retorna a rule em um array
        */
       function editLimitRecipientesByUser($params)
       {

           $filter = 'WHERE id = \''.$params['id'].'\'';
           $rules = $this->soConfiguration->getRuleInDb($filter);

           if($rules)
           {
              //Resgata informaçoes do usuario do ldap
              $rules[0]['userCn'] = $this->ldapFunctions->get_user_cn_by_uid($rules[0]['email_user']);
              $rules[0]['status'] = true;
              return $rules[0];
           }
           else
              return array('status' => false , 'msg' => 'Error on read rule');
       }

         /**
        * Carrega as informaçoes da regra para ser editada
        * @param array $params [id] = Id da regra
        * @return array retorna a rule em um array
        */
       function editLimitRecipientesByGroup($params)
       {

           $filter = 'WHERE id = \''.$params['id'].'\'';
           $rules = $this->soConfiguration->getRuleInDb($filter);

           if($rules)
           {
              //Resgata informaçoes do usuario do ldap
              $rules[0]['groupCn'] = $this->ldapFunctions->get_group_cn_by_gidnumber($rules[0]['email_user']);
              $rules[0]['status'] = true;
              return $rules[0];
           }
           else
              return array('status' => false , 'msg' => 'Error on read rule');
       }

       /**
        * Cria ou sobrescreve resgras de Institutional Account Exception
        * @param array $params
        * @return array [status] = true or false,[msg] = mensagem de erro
        */
       function createBlockEmailForInstitutionalAcounteExeption($params)
       {
          
           if(!$params['selectUsersOrGroupsInRule'] &&  !$params['inputCheckAllRecipientsInstitutionalAccountRule'])
                return array('status' => false , 'msg' => 'no recipients selecteds');

           if(!$params['selecSendersInRule'] &&  !$params['inputCheckAllSendersInstitutionalAccountRule'])
                return array('status' => false , 'msg' => 'no senders selecteds');

           if($params['inputCheckAllRecipientsInstitutionalAccountRule'] && $params['inputCheckAllSendersInstitutionalAccountRule'])
                return array('status' => false , 'msg' => 'all senders and all recipients selecteds');




           if($params['inputCheckAllRecipientsInstitutionalAccountRule'])
           {

                 //resgata todas as regras de all Recipients
                 $condition = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'A\' ';
                 $fields = array('id');
                 $rules = $this->soConfiguration->getRuleInDb($condition,$fields);

                 //Remove todas as regras de all Recipients
                 foreach ($rules as $rule)
                     $this->soConfiguration->removeRuleInDb($rule['id']);

                 //Insere as novas Regras
                 foreach($params['selecSendersInRule'] as $senderInRule)
                 {
                     $fields = array(
                            'email_user' => '*',
                            'email_recipient' => $senderInRule,
                            'configuration_type' => 'InstitutionalAccountException',
                            'email_user_type' => 'A'
                           );

                      if(!$this->soConfiguration->insertRuleInDb($fields))
                        return array('status' => false , 'msg' => 'Error on insert');
                      else
                        $this->dbFunctions->write_log ('Create BlockEmail For Institutional AcounteExeption', 'All');
                    
                 }

           }
           else 
           {
                 //intera todos os usuarios ou grupos selecionados
                 foreach($params['selectUsersOrGroupsInRule'] as $userOrGroupT)
                 {

                     $userOrGroupA = explode(',', $userOrGroupT);
                     $userOrGroup = $userOrGroupA['0'];

                     if($userOrGroupA['1'] == 'G')
                     {
                      //Caso seja um grupo

                         //resgata todas as regras
                         $condition = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'G\' AND email_user = \''.$userOrGroup.'\' ';
                         $fields = array('id');
                         $rules = $this->soConfiguration->getRuleInDb($condition,$fields);

                         //Remove todas as regras
                         foreach ($rules as $rule)
                             $this->soConfiguration->removeRuleInDb($rule['id']);

                         //Se todos os senders estiverem selecionados
                         if($params['inputCheckAllSendersInstitutionalAccountRule'])
                         {
                             $fields = array(
                                    'email_user' => $userOrGroup,
                                    'email_recipient' => '*',
                                    'configuration_type' => 'InstitutionalAccountException',
                                    'email_user_type' => 'G'
                                   );

                              if(!$this->soConfiguration->insertRuleInDb($fields))
                                return array('status' => false , 'msg' => 'Error on insert');

                         }
                         else
                         {
                             //Insere as novas Regras
                             foreach($params['selecSendersInRule'] as $senderInRule)
                             {
                                 $fields = array(
                                        'email_user' => $userOrGroup,
                                        'email_recipient' => $senderInRule,
                                        'configuration_type' => 'InstitutionalAccountException',
                                        'email_user_type' => 'G'
                                       );

                                  if(!$this->soConfiguration->insertRuleInDb($fields))
                                    return array('status' => false , 'msg' => 'Error on insert');

                             }
                         }
                     }
                     else
                     {
                      //Caso seja um usuario

                         //resgata todas as regras
                         $condition = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'U\' AND email_user = \''.$userOrGroup.'\' ';
                         $fields = array('id');
                         $rules = $this->soConfiguration->getRuleInDb($condition,$fields);

                         //Remove todas as regras
                         foreach ($rules as $rule)
                             $this->soConfiguration->removeRuleInDb($rule['id']);

                         //Se todos os senders estiverem selecionados
                         if($params['inputCheckAllSendersInstitutionalAccountRule'])
                         {
                             $fields = array(
                                    'email_user' => $userOrGroup,
                                    'email_recipient' => '*',
                                    'configuration_type' => 'InstitutionalAccountException',
                                    'email_user_type' => 'U'
                                   );

                              if(!$this->soConfiguration->insertRuleInDb($fields))
                                return array('status' => false , 'msg' => 'Error on insert');

                         }
                         else
                         {
                             //Insere as novas Regras
                             foreach($params['selecSendersInRule'] as $senderInRule)
                             {
                                 $fields = array(
                                        'email_user' => $userOrGroup,
                                        'email_recipient' => $senderInRule,
                                        'configuration_type' => 'InstitutionalAccountException',
                                        'email_user_type' => 'U'
                                       );

                                 if(!$this->soConfiguration->insertRuleInDb($fields))
                                    return array('status' => false , 'msg' => 'Error on insert');



                             }
                         }

                     }

                    $this->dbFunctions->write_log ('Create BlockEmail For Institutional AcounteExeption', $userOrGroup);
                 }
           }
        
           
           return array('status' => true);


       }

       /**
        * Retorna todas as regras BlockEmailForInstitutionalAcounteExeption
        * @return string
        */
       function getOptionsBlockEmailForInstitutionalAcounteExeption()
       {
           $filter = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'U\'  GROUP BY email_user  ORDER BY email_user ';
           $fields = array('email_user');
           $rulesUsers = $this->soConfiguration->getRuleInDb($filter, $fields);

           
           $filter = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'G\' GROUP BY email_user  ORDER BY email_user ';
           $fields = array('email_user');
           $rulesGroups = $this->soConfiguration->getRuleInDb($filter, $fields);

           $filter = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'A\' GROUP BY email_user  ORDER BY email_user ';
           $fields = array('email_user');
           $rulesAll = $this->soConfiguration->getRuleInDb($filter, $fields);

           $return = '';

           if(count($rulesAll) > 0)
           {
             $return .= '<option  value="-1" disabled>---------------------------------'.$this->functions->make_lang('lang_all').'----------------------------------</option>';

             foreach ($rulesAll as $all)
                 $return .= '<option  value="'.$all['email_user'].'" >'.$this->functions->make_lang('lang_all').'</option>';

           }

           if(count($rulesUsers) > 0)
           {
             $return .= '<option  value="-1" disabled>-------------------------------'.$this->functions->make_lang('lang_users').'-------------------------------</option>';

             foreach ($rulesUsers as $user)
                 $return .= '<option  value="'.$user['email_user'].',U" >'.utf8_decode($this->ldapFunctions->get_user_cn_by_uid($user['email_user'])).'</option>';

           }

           if(count($rulesGroups) > 0)
           {
             $return .= '<option  value="-1" disabled>---------------------------------'.$this->functions->make_lang('lang_groups').'--------------------------------</option>';

             foreach ($rulesGroups as $group)
                 $return .= '<option  value="'.$group['email_user'].',G">'.utf8_decode($this->ldapFunctions->get_group_cn_by_gidnumber($group['email_user'])).'</option>';

           }
   

           return $return;
       }

       /**
        * Retorna todos os destinatarios da regra BlockInstitutionalAcounteExeptio
        * @param <type> $params
        * @return string
        */
       function getOptionsSenderInstitutionalAcounteExeption($params)
       {

           $recipient = explode(',', $params['recipient']);
           $filter = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user = \''.$recipient['0'].'\'  ORDER BY email_recipient ';
           $fields = array('email_recipient');
           $rules = $this->soConfiguration->getRuleInDb($filter, $fields);

           $return = '';

           if(count($rules) > 0)
           {
             foreach ($rules as $rule)
             {
                    $return .= '<option  value="'.$rule['email_recipient'].'">'.$rule['email_recipient'].'</option>';
             }
           }

           return $return;
       }
       /**
        * Busca os destinatarios das regras de excessao block institutional account
        * @param <type> $params
        * @return <type>
        */
       function getRecipientsInstitutionalAcounteExeption($params)
       {


           $recipient = explode(',', $params['recipient']);

           $filter = 'WHERE configuration_type = \'InstitutionalAccountException\' AND email_user = \''.$recipient['0'].'\'  ORDER BY email_recipient ';
           $rules = $this->soConfiguration->getRuleInDb($filter);

           $return = '';
           $allSenders = false;
           $optionRecipient = '';

           if(count($rules) > 0)
           {
             foreach ($rules as $rule)
             {
                    if($rule['email_recipient'] == '*')
                    {
                       $allSenders = true;                      
                    }

                    $return .= '<option  value="'.$rule['email_recipient'].'">'.$rule['email_recipient'].'</option>';
             }
           }


           return array('status' => true, 'options' => $return, 'allSender' => $allSenders );
       }

       /**
        * Cria ou atualiza regras globais de bloqueios
        * @param array $params
        * @return array [status] = true or false,[msg] = mensagem de erro
        */
       function saveGlobalConfiguration($params)
       {

           //Valida se maximum sender é do tipo numero
            if(!is_numeric($params['maximumRecipient']) && $params['maximumRecipient'])
                 return array('status' => false , 'msg' => 'maximumRecipient not number');


     
           //Atualiza Regra Bloquei de comunicação de contas institucionais ou compartilhadas
           $filter = 'WHERE config_app = \'expressoMail1_2\' AND config_name = \'expressoMail_block_institutional_comunication\'';
           $fields = array('oid','config_value');
           $rules = $this->soConfiguration->getRuleGlobalInDb($filter, $fields);


           $params['blockComunication'] = (string)$params['blockComunication'];

           //Verifica se ouve alteração
           if($rules[0]['config_value'] != $params['blockComunication'])
           {
               if(count($rules) > 0)
               {
                //Update
                    $fields = array(
                            'config_app' => 'expressoMail1_2',
                            'config_name' => 'expressoMail_block_institutional_comunication',
                            'config_value' => $params['blockComunication'],
                           );

                     if(!$this->soConfiguration->updatetRuleGlobalInDb($rules['0']['oid'], $fields))
                        return array('status' => false , 'msg' => 'Error on update block comunication ');
                     else
                        $this->dbFunctions->write_log ('Block Institutional Comunication Global Update', 'All');
               }
               else
               {
                //Insert
                    $fields = array(
                            'config_app' => 'expressoMail1_2',
                            'config_name' => 'expressoMail_block_institutional_comunication',
                            'config_value' => $params['blockComunication'],
                           );

                     if(!$this->soConfiguration->insertRuleGlobalInDb($fields))
                        return array('status' => false , 'msg' => 'Error on insert block comunication ');
                     else
                        $this->dbFunctions->write_log ('Block Institutional Comunication Global Update', 'All');

               }
           }

           //Atualiza Regra Maximo Destinatarios
           $filter = 'WHERE config_app = \'expressoMail1_2\' AND config_name = \'expressoAdmin_maximum_recipients\'';
           $fields = array('oid','config_value');
           $rules = $this->soConfiguration->getRuleGlobalInDb($filter, $fields);


            if($params['maximumRecipient'] < 1)
                $params['maximumRecipient'] = '0';


           //Verifica se ouve alteração
           if($rules[0]['config_value'] != $params['maximumRecipient'])
           {
               if(count($rules) > 0)
               {
                    //Update
                    $fields = array(
                            'config_app' => 'expressoMail1_2',
                            'config_name' => 'expressoAdmin_maximum_recipients',
                            'config_value' => $params['maximumRecipient'],
                           );

                     if(!$this->soConfiguration->updatetRuleGlobalInDb($rules['0']['oid'], $fields))
                        return array('status' => false , 'msg' => 'Error on update maximum Recipient ');
                     else
                        $this->dbFunctions->write_log ('Maximum RecipientsGlobal Update', 'All');
               }
               else
               {
                    //Insert
                    $fields = array(
                            'config_app' => 'expressoMail1_2',
                            'config_name' => 'expressoAdmin_maximum_recipients',
                            'config_value' => $params['maximumRecipient'],
                           );

                     if(!$this->soConfiguration->insertRuleGlobalInDb($fields))
                        return array('status' => false , 'msg' => 'Error on insert maximum Recipient ');
                     else
                        $this->dbFunctions->write_log ('Maximum RecipientsGlobal Update', 'All');

               }
           }
           return array('status' => true );
       }

       /**
        * Retorna Todas as regras do modulo expressoAdmin
        * @return array 
        */
       function getGlobalConfiguratons()
       {
     
           $filter = 'WHERE config_app = \'expressoMail1_2\' ';
           $fields = array('config_name','config_value');
           $rules = $this->soConfiguration->getRuleGlobalInDb($filter, $fields);

           $config = array();

           foreach ($rules as $rule)
           {
               $config[$rule['config_name']] = $rule['config_value'] ;
           }

           return $config;
       }

    }

?>
