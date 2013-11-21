<?php
  /**************************************************************************\
  * eGroupWare - Admin config                                                *
  * Written by Miles Lott <milosch@phpwhere.org>                             *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

require_once('prototype/api/config.php');
use prototype\api\Config as Config;

	class uiconfig
	{
		var $public_functions = array('index' => True);

		function index()
		{
			if ($GLOBALS['phpgw']->acl->check('site_config_access',1,'admin'))
			{
				$GLOBALS['phpgw']->redirect_link('/index.php');
			}

			if(get_magic_quotes_gpc() && is_array($_POST['newsettings']))
			{
				$_POST['newsettings'] = array_map("stripslashes", $_POST['newsettings']);
			}
			
			switch($_GET['appname'])
			{
				case 'admin':
				case 'addressbook':
				case 'calendar':
				case 'email':
				case 'instant_messenger':
				case 'jabberit_messenger':
				case 'nntp':
					/*
					Other special apps can go here for now, e.g.:
					case 'bogusappname':
					*/
					$appname = $_GET['appname'];
					$config_appname = 'phpgwapi';
					break;
				case 'phpgwapi':
				case '':
					/* This keeps the admin from getting into what is a setup-only config */
					$GLOBALS['phpgw']->redirect_link('/admin/index.php');
					break;
				default:
					$appname = $_GET['appname'];
					$config_appname = (isset($_GET['config']) && $_GET['config']) ? $_GET['config'] : $appname ;
					break;
			}

			$t = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir($appname));
			$t->set_unknowns('keep');
			$template_file = $config_appname == "migra" ? 'migra.tpl' : 'config.tpl';	
			$t->set_file(array('config' => $template_file));
			$t->set_block('config','header','header');
			$t->set_block('config','body','body');
			$t->set_block('config','footer','footer');

			$c = CreateObject('phpgwapi.config',$config_appname);
			$c->read_repository();

			if ($c->config_data)
			{
				$current_config = $c->config_data;
			}
	
		if($appname === "expressoCalendar"){

            if($config_appname == 'expressoCalendar' )
            {
                $t->set_var('action_url',$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname));

            };

            if($_POST['newsettings']['expressoCalendar_autoImportCalendars'] == 'true')
            {
                $db = $GLOBALS['phpgw']->db;
                $calendars = array();
                $db->query('SELECT calendar_signature.user_uidnumber as "user",calendar.id as "calendar" FROM calendar,calendar_signature WHERE calendar.id = calendar_signature.calendar_id AND calendar.type = 0 AND calendar_signature.is_owner = 1 AND (SELECT id from module_preference WHERE user_uidnumber = calendar_signature.user_uidnumber AND module_preference.module = \'expressoCalendar\' AND module_preference.name = \'dafaultImportCalendar\'  ) IS NULL');
                while( $db->next_record() )
                {
                    $calendars[] = $db->row();
                }

                foreach($calendars as $v)
                {
                    $db->query('INSERT INTO module_preference ("user_uidnumber","value","name","module") VALUES ( \''.$v['user'].'\' , \''.$v['calendar'].'\',\'dafaultImportCalendar\' , \'expressoCalendar\')');
                }
            }

			if (isset($_POST['migration']) && ($_POST['migration']  == "true")){
			
				require_once dirname(__FILE__ )."/../../expressoCalendar/inc/class.ui_migration.inc.php";

				$migratrion = new Migra();
				$migratrion->calendar();
			}

		}

			if ($_POST['cancel'] || $_POST['submit'] && $GLOBALS['phpgw']->acl->check('site_config_access',2,'admin'))
			{
				$GLOBALS['phpgw']->redirect_link('/admin/index.php');
			}

			if ($_POST['submit'])
			{
				/* Load hook file with functions to validate each config (one/none/all) */
				$GLOBALS['phpgw']->hooks->single('config_validate',$appname);
				
				if (!isset($_POST['newsettings']['cc_allow_details'])) {
					$_POST['newsettings']['cc_allow_details'] = "false";
				}

				foreach($_POST['newsettings'] as $key => $config)
				{
					
					if ($config)  
					{																						  // Código adicionado	
						if($GLOBALS['phpgw_info']['server']['found_validation_hook'] && (function_exists($key) || function_exists(substr($key,0,strrpos($key,'_')))) )
						{
							if(function_exists(substr($key,0,strrpos($key,'_'))))
                        	{
                                call_user_func(substr($key,0,strrpos($key,'_')), $config);
                        	} 
                        	else 
                        	{
                                call_user_func($key,&$config);
                        	}

                        	if($GLOBALS['config_error'])
							{
								$errors = lang($GLOBALS['config_error']) . '&nbsp;';
								$GLOBALS['config_error'] = False;
							}
							else
							{
								$c->config_data[$key] = $config;
							}
						}
						else
						{
							$c->config_data[$key] = $config;
						}
					}
					else
					{
						/* don't erase passwords, since we also don't print them */
						if(!preg_match('/passwd/',$key) && !preg_match('/password/',$key) && !preg_match('/root_pw/',$key) && !preg_match('/pw/',$key))
						{
							unset($c->config_data[$key]);
						}
					}
				}
				if($GLOBALS['phpgw_info']['server']['found_validation_hook'] && function_exists('final_validation'))
				{
					final_validation($c->config_data);
					if($GLOBALS['config_error'])
					{
						$errors .= lang($GLOBALS['config_error']) . '&nbsp;';
						$GLOBALS['config_error'] = False;
					}
					unset($GLOBALS['phpgw_info']['server']['found_validation_hook']);
				}

				$c->save_repository();

				if(!$errors)
				{
					$GLOBALS['phpgw']->redirect_link('/admin/index.php');
				}
			}

			if($errors)
			{
				$t->set_var('error',lang('Error') . ': ' . $errors);
				$t->set_var('th_err','#FF8888');
				unset($errors);
				unset($GLOBALS['config_error']);
			}
			else
			{
				$t->set_var('error','');
				$t->set_var('th_err',$GLOBALS['phpgw_info']['theme']['th_bg']);
			}

			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('jscode','openwindow','admin');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			
			$campos = '';
			$checked_box = array();
			$i = 0;
			
			if($appname=="contactcenter") 
			{
				
				foreach ($c->config_data as $key => $config) 
				{        	
					$parts = explode('_', $key);
                	if (is_numeric($parts[3]) && $parts[1]=='attribute')
                	{
                        $fields[$parts[3]][$key] = $config;
                	}
                	
                	
                	if($key == 'cc_allow_details') {
                		$checkedCoisa = 'value="details"';
              			if ($config=='true') {
              				$checkedCoisa = 'value="details" checked="checked"'; 
              			}
                	}			
				}
	
				$campos_vazio = true;
				$campos = "
						<input type=\"hidden\" id=\"textHidden\" value=\"".lang('Text')."\" />
						<input type=\"hidden\" id=\"multitextHidden\" value=\"".lang('Multivalued')."\" />
						<input type=\"hidden\" id=\"yesHidden\" value=\"".lang('Yes')."\" />
						<input type=\"hidden\" id=\"noHidden\" value=\"".lang('No')."\" />
						<input type=\"hidden\" id=\"deleteHidden\" value=\"".lang('Delete')."\" />
						<table id=\"cc_newconf\" name=\"cc_newconf\" class=\"cc_attribute\">
						   <tbody id=\"cc_newconftable\" name=\"cc_newconftable\">
							<tr>
								<td align=\"center\" style=\"width:170px;\">" . lang('Name')               . "</td>
								<td align=\"center\" style=\"width:170px;\">" . lang('Corresponding LDAP') . "</td>
								<td align=\"center\" style=\"width:100px; margin: 0px 0px 0px 8px;\">" . lang('Type')               . "</td>
								<td align=\"center\" style=\"width:80px; margin: 0px 16px;\">" . lang('Searchable')         . "</td>
								<td align=\"center\" style=\"width: 15px;\">
							</tr>";
                
        		foreach ($fields as $i => $line) 
        		{
					if ($line["cc_attribute_name_$i"] != '' && $line["cc_attribute_ldapname_$i"] != '') {
						$campos_vazio  = false;
                        $selectedText  = $line["cc_attribute_type_$i"] == 'text' ? 'selected="selected"' : '';
                        $selectedTMult = $line["cc_attribute_type_$i"] == 'multivalues' ? 'selected="selected"' : '';
						$selectedYes   = $line["cc_attribute_searchable_$i"] == 'true'  ? 'selected="selected"' : '';
						$selectedNo    = $line["cc_attribute_searchable_$i"] == 'false' ? 'selected="selected"' : '';

						
                		$campos = $campos . "
								<tr>
									<td><input type=\"text\" name=\"newsettings[cc_attribute_name_" . $i . "]\" value=\"".$line["cc_attribute_name_$i"]."\" style=\"width:170px;\"></input></td>
									<td><input type=\"text\" name=\"newsettings[cc_attribute_ldapname_" . $i . "]\" value=\"".$line["cc_attribute_ldapname_$i"]."\" style=\"width:170px;\"></input></td>
									<td><select name=\"newsettings[cc_attribute_type_" . $i . "]\" style=\"width:86px; margin: 0px 0px 0px 8px;\">
											<option value=\"text\" $selectedText>" . lang('Text') . "</option>
											<option value=\"multivalues\" $selectedTMult>" . lang('Multivalued') ."</option>
									</select></td>
									<td><select name=\"newsettings[cc_attribute_searchable_$i]\" style=\"margin: 0px 16px;\">
											<option value=\"true\" $selectedYes>" . lang('Yes') . "</option>
											<option value=\"false\" $selectedNo>" . lang('No') . "</option>
									</select></td>
									<td><img src=\"contactcenter/templates/default/images/cc_x.png\" alt=\"". lang('Delete') . "\" title=\"". lang('Delete') ."\" style=\"width: 15px; height: 14px; cursor: pointer; position: relative; top: 3px;\" onclick=\"javascript:cc_attribute_delete(this)\"></img></td>  
								</tr>";
                	}
        		} 
        	
			    if ($campos_vazio)
        		{
					$campos .= "<tr>
									<td><input type=\"text\" name=\"newsettings[cc_attribute_name_0]\" value=\"\" style=\"width:170px;\"/> </td>
									<td><input type=\"text\" name=\"newsettings[cc_attribute_ldapname_0]\" value=\"\" style=\"width:170px;\"/> </td>
									<td><select name=\"newsettings[cc_attribute_type_0]\" style=\"width:86px; margin: 0px 0px 0px 8px;\">
                                        <option value=\"text\">" . lang('Text') . "</option>
                                        <option value=\"multivalues\">" . lang('Multivalued') . "</option>
									</select></td>
									<td><select name=\"newsettings[cc_attribute_searchable_0]\" style=\"margin: 0px 16px;\">
                                        <option value=\"true\">" . lang('Yes') . "</option>
                                        <option value=\"false\" selected=\"selected\">" . lang('No') . "</option>
									</select></td>
									<td><img src=\"contactcenter/templates/default/images/cc_x.png\" alt=\"". lang('Delete') ."\" title=\"". lang('Delete') ."\" style=\"width: 15px; height: 14px; cursor: pointer; position: relative; top: 3px;\" onclick=\"javascript:cc_attribute_delete(this)\"/></td>
								</tr>";				
        		}
        		$campos = $campos . "</tbody></table>";
        		
        		$t->set_var('lang_add_button', lang('Add'));
				$t->set_var('lang_cc_Set_details_attributes',   lang('Details on the Global Catalog Address'));
        		$t->set_var('lang_cc_Allow_view_details_label', lang('Enable display of contact details for the Global Catalog'));				
        		$t->set_var('attribute_fields', $campos);      		
        		$t->set_var('cc_config_js', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_config.js');
        		$t->set_var('cc_allow_view_details_value', $checkedCoisa);
			} 
        	
			
			if($appname=="expressoAdmin1_2") {
				/* Varre a pasta inc do admin do expresso procurando scripts de geração de login automático
				   (classes com nomes iniciados pela string 'login', procedida da string '_' mais o nome
				   do algoritmo.
				*/
				
				$dir = $GLOBALS['phpgw']->common->get_app_dir($appname) . "/inc";
				$options = ' ';
				if (is_dir($dir))
				{
					if ($dh = opendir($dir))
					{
						while (($file = readdir($dh)) !== false)
						{
							$temp = explode(".",$file);
							if( (substr($temp[1],0,5) =='login') && ($temp[0] == 'class') )
							{
								$options .= "<option value='".$temp[1]."'";
								if($current_config['expressoAdmin_loginGenScript'] == $temp[1])
									$options .= " selected";
								$options .= ">" . ucwords(str_replace("_"," ",substr($temp[1],6))) . "</option>";
							}				
						}
						closedir($dh);
					}
				}
				
				$t->set_var('rows_login_generator',$options);
			}		
			
			if($appname=="admin") {							
				/*
				 * New CKEditor to agree term
				 */
				$content = isset($GLOBALS['phpgw_info']['server']['agree_term']) ? $GLOBALS['phpgw_info']['server']['agree_term'] : '';
				$ckeditor = '<script type="text/javascript" src="./library/ckeditor/ckeditor.js"></script>
							<textarea cols="80" id="newsettings[agree_term]" name="newsettings[agree_term]" rows="10">' . $content . '</textarea>
							<script type="text/javascript"> CKEDITOR.replace( \'newsettings[agree_term]\',{
								removePlugins : \'elementspath\',
								skin : \'office2003\',
								toolbar : [["Source","Preview","-","Cut","Copy","Paste","-","Print",
								"Undo","Redo","-","Find","Replace","-","SelectAll" ],
								["Table","HorizontalRule","SpecialChar","PageBreak","-","Bold",
								"Italic","Underline","Strike","-","Subscript","Superscript",
								"NumberedList","BulletedList","-","Outdent","Indent","Blockquote",
								"JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock",
								"Link", "TextColor","BGColor","Maximize"],
								["Styles","Format","Font","FontSize"]]
							});</script>';
				$t->set_var('agree_term_input',$ckeditor);
			}
			$t->set_var('title',lang('Site Configuration'));
			$t->set_var('action_url',$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname));
			$t->set_var('th_bg',     $GLOBALS['phpgw_info']['theme']['th_bg']);
			$t->set_var('th_text',   $GLOBALS['phpgw_info']['theme']['th_text']);
			$t->set_var('row_on',    $GLOBALS['phpgw_info']['theme']['row_on']);
			$t->set_var('row_off',   $GLOBALS['phpgw_info']['theme']['row_off']);
			$t->set_var('php_upload_limit',str_replace('M','',ini_get('upload_max_filesize')));
			$t->pparse('out','header');

			$vars = $t->get_undefined('body');

			$GLOBALS['phpgw']->hooks->single('config',$appname);
			
			/* Seta o valor padrão para a configuração de número máximo de marcadores */
			$current_config['expressoMail_limit_labels'] = (isset($current_config['expressoMail_limit_labels']) && !!$current_config['expressoMail_limit_labels'] ) ? $current_config['expressoMail_limit_labels'] : 20;
			//Pegar os todos os Atributos LDAP mapeados no arquivo user.ini
			$map = Config::get('user', 'OpenLDAP.mapping');
			$validate = false;	
			$options = "<option value=''>".lang('None')."</option>";
			foreach($map as $value){
				$options .= "<option value='".$value."'";
				if($current_config['expressoMail_ldap_identifier_recipient'] == $value){
					$validate = true;
					$options .= " selected='selected'";
				}
				$options .= ">". $value . "</option>";
			}

			if(!$validate){
				// Limpa Atributo LDAP do banco de dados caso a atribuição não exista mais. 	
				$db = '';
				$db = $db ? $db : $GLOBALS['phpgw']->db;	// this is to allow setup to set the db
				$db->query("DELETE FROM phpgw_config WHERE config_app = '".$appname."' AND config_name = 'expressoMail_ldap_identifier_recipient'");
			}
			/* Recupera o número mínimo de marcadores que pode ser definido */
			$db = '';
			$db = $db ? $db : $GLOBALS['phpgw']->db;	// this is to allow setup to set the db
			$db->query("SELECT max(slot) as slot from expressomail_label",__LINE__,__FILE__);
			while( $db->next_record() )
			{
				$cont_labels = $db->f('slot');
			}

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
						if(preg_match('/passwd/',$value) || preg_match('/password/',$value) || preg_match('/root_pw/',$value))
						{
							$t->set_var($value,'');
						}
						else
						{
							$t->set_var($value,htmlspecialchars($current_config[$newval]));
						}
						break;
					/*
					case 'checked':
						$newval = str_replace(' ','_',$newval);
						if ($current_config[$newval])
						{
							$t->set_var($value,' checked');
						}
						else
						{
							$t->set_var($value,'');
						}
						break;
					*/
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

			$t->set_var('min_labels',$cont_labels);
			
			$t->set_var('rows_ldap_identifier',$options);
			$t->pfp('out','body');

			$t->set_var('lang_submit', $GLOBALS['phpgw']->acl->check('site_config_access',2,'admin') ? lang('Cancel') : lang('Save'));
			$t->set_var('lang_cancel', lang('Cancel'));
			$t->pfp('out','footer');
		}
	}
?>
