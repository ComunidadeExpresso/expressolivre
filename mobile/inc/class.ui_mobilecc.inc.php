<?php
	class ui_mobilecc{
		
		var $nextmatchs;
		var $bo;
		var $page_info = array (
				'actual_catalog' => false,
				'actual_letter' => null,
				'actual_max_contacts' => null,
				'request_from' => null
			);
		
		var $public_functions = array(
			'index' => true,
			'change_max_results' => true,
			'change_catalog' => true,
			'delete_contacts' => true,
			'change_letter' => true,
			'choose_contact' => true,
			'init_cc' => true,
			'contact_view' => true,
			'contact_add_edit' => true,
			'contact_add' => true,
			'contact_edit' => true,
			'getPhoto'  => true
		);
		var $template;
		
		/**
		 * Construtor...
		 * 
		 */
		public function ui_mobilecc() {
			$this->template = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
			$this->bo = CreateObject('mobile.bo_mobilecc');
			$page_info = $GLOBALS['phpgw']->session->appsession('mobilecc.page_info','mobile');
			
			if($page_info) {
				$this->page_info = $page_info;
			}
			else {
				$this->set_page_info_to_default();
			}
		}
		
		private function set_page_info_to_default() { //Valores default para iniciar o módulo de contatos
			$this->page_info['actual_catalog'] = 'bo_people_catalog';
			$this->page_info['actual_letter'] = 'a';
			$this->page_info['actual_max_contacts'] = 10;
			$this->page_info['request_from'] = null;
		}
		
		private function save_session() {
			$GLOBALS['phpgw']->session->appsession('mobilecc.page_info','mobile',$this->page_info);
		}
		
		public function index($params) {
			$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg($params["error_message"]);
			$GLOBALS['phpgw_info']['mobiletemplate']->set_success_msg($params["success_message"]);
			$this->contacts_list();
		}
		
		public function change_max_results($params) {
			$this->page_info['actual_max_contacts'] = $params['results'];
			$this->save_session();
			$this->contacts_list();
		}
		
		public function change_letter($params)
		{
			if( $params['letter'] )
			{
				$this->page_info['actual_letter'] = $params['letter'];
			}
			$this->page_info['actual_max_contacts'] = 10;
			$this->save_session();
			$this->contacts_list($params);
		}
		
		public function change_catalog($params)
		{
			$this->page_info['actual_catalog'] = $params['catalog'];
			$this->page_info['actual_letter'] = 'a';
			$this->page_info['actual_max_contacts'] = 10;
			$this->save_session();
			$this->contacts_list($params);
		}
		
		/**
		 * Função de inicio do módulo para escolha de um contato para outro módulo.
		 * 
		 * @return 
		 * @param $params Object
		 */
		public function choose_contact($params) {
			$this->set_page_info_to_default();
			$this->page_info['request_from'] = $params['request_from']; //Para escolher contato vindo de outro modulo, mudo apenas o request_from
			$this->save_session();
			$this->contacts_list();
		}
		
		/**
		 * Função de inicio do módulo de cc
		 * 
		 * @return 
		 * @param $params Object
		 */
		public function init_cc($params) {
			$this->set_page_info_to_default();
			$this->save_session();
			$this->contacts_list($params);
		}
		
		/**
		 * Monta a lista de contatos na tela, de acordo com a busca. Se não foi feita
		 * busca, mostra apenas o formulário para pesquisa.
		 * 
		 * @return 
		 */
		
		function contacts_list($params) {
			
			$this->template->set_file(
				Array(
					'contacts_list' => 'cc_main.tpl',
					'home_search_bar' => 'search_bar.tpl'
				)
			);
			$this->template->set_block("contacts_list","catalog_row");
			$this->template->set_block("contacts_list","main_body");
			$this->template->set_block("contacts_list","pagging_block");
			$this->template->set_block('home_search_bar','search_bar');

			//Langs gerais da página
			$this->template->set_var("lang_back",lang("back"));
			$this->template->set_var('href_back',$GLOBALS['phpgw_info']['mobiletemplate']->get_back_link());
			$this->template->set_var("selecteds",ucfirst(lang("selecteds")));
			$this->template->set_var("lang_more",lang("more"));
			$this->template->set_var("lang_search",lang("search"));
			$this->template->set_var("lang_contacts",ucfirst(lang("contacts")));
			$this->template->set_var("actual_catalog",$this->page_info["actual_catalog"]);
			$this->template->set_var("next_max_results",$this->page_info["actual_max_contacts"]+10);

			$show_checkbox = true;
			if(strpos($this->page_info["actual_catalog"],"ldap")===false) {
				$this->template->set_var("show_actions", "block");
			} else {
				$this->template->set_var("show_actions", "none");
				$show_checkbox = false;
			}

			$this->template->set_var("show_add_button",$this->page_info["actual_catalog"] == "bo_group_manager" ? "none" : "");
			
			$this->template->set_var("contacts_request_from",
										$this->page_info["request_from"]==null?
										"none":$this->page_info["request_from"]);
			
			if($GLOBALS['phpgw']->session->appsession('mobile.layout','mobile')!="mini_desktop")
				$this->template->set_var('search',$this->template->fp('out','search_bar'));
			
			//Combo de catálogos
			$catalogs = $this->bo->get_all_catalogs();

			foreach($catalogs as $catalog)
			{
				$this->template->set_var("catalog_value",$catalog["catalog"]);
				$this->template->set_var("catalog_name",$catalog["label"]);
				
				if( $this->page_info['actual_catalog'] == $catalog['catalog'] )
					$this->template->set_var("selected","selected");
				else
					$this->template->set_var("selected"," ");
				
				$this->template->fp("catalogs","catalog_row",true);
			}

			$catalog = ( isset($params['catalog']) )  ? $params['catalog'] : $this->page_info['actual_catalog'];
			
			if( strpos( $catalog, "bo_global_ldap_catalog") !== false )
			{
				$dn			= $GLOBALS['phpgw_info']['user']['account_dn'];
				$dn			= substr( $dn, strpos($dn, "ou=" ) );
				$exploded	= explode("#", $catalog );
				$catalog 	= 'bo_people_catalog';
				
				if( count($exploded) > 2 )
				{
					$exploded[2]	= $dn;
					$actual_catalog = implode("#", $exploded);
					$catalog = $actual_catalog;
				}
			}
			
			$this->bo->set_catalog($catalog);
 			$contacts = $this->bo->search($this->page_info["actual_letter"]."%", $this->page_info["actual_max_contacts"]);
 			
			//Letras da paginação
			$max_letters = 5;
			if ( in_array($this->page_info['actual_letter'],
						  range("a","c"))){ //Letras de A à C iniciam sempre com A		
  	
				$this->template->set_var('show_back','none');
				$this->template->set_var('show_next','inline');
				$first_letter = "a";
				$this->template->set_var('href_next',"index.php?menuaction=mobile.".
										 "ui_mobilecc.change_letter&letter=f");
			}
			else if ( in_array($this->page_info['actual_letter'],
						  range("x","z"))) { //Letras de X à Z terminam sempre no Z
				$this->template->set_var('show_back','inline');
				$this->template->set_var('show_next','none');
				$first_letter = "v";
				$this->template->set_var('href_back',"index.php?menuaction=mobile.".
										 "ui_mobilecc.change_letter&letter=u");
				}
			else { //Letras do meio
				$this->template->set_var('show_back','inline');
				$this->template->set_var('show_next','inline');
				
				$first_letter = chr(ord($this->page_info["actual_letter"])-3);//Inicio 3 letras antes
				$last_letter = chr(ord($first_letter)+($max_letters+1));//A ultima é a máxima quantidade de letras mais 1 do next_letter
				
				$this->template->set_var('href_back',"index.php?menuaction=mobile.".
										 "ui_mobilecc.change_letter&letter=".$first_letter);
				$this->template->set_var('href_next',"index.php?menuaction=mobile.".
										 "ui_mobilecc.change_letter&letter=".$last_letter);
				++$first_letter;
			}
			
			for($i=1;$i<=$max_letters;++$i) { //Roda as letras
					$this->template->set_var("href","index.php?menuaction=mobile.".
											"ui_mobilecc.change_letter&letter=".$first_letter);
					$this->template->set_var("letter",strtoupper($first_letter));
					if($first_letter===$this->page_info["actual_letter"])
						$this->template->set_var("class_button","letter-contact-selected");
					else
						$this->template->set_var("class_button","btn_off");
					$this->template->set_var("letter",strtoupper($first_letter));
					$this->template->fp("pagging_letters","pagging_block",true);
					++$first_letter;
			}
			

			if($contacts['has_more'])
				$this->template->set_var("show_more","block");
			else
				$this->template->set_var("show_more","none");
			unset($contacts['has_more']);
						
			$this->template->set_var("contacts",$this->print_contacts($contacts,$show_checkbox,$this->page_info['request_from']));
			

			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out','main_body'));
		}
		
		/**
		 * Remove os contatos selecionados
		 * 
		 * @return 
		 * @param $contacts Object
		 * @param $show_checkbox Object[optional]
		 */
		
		function delete_contacts($params)
		{
			$this->bo->set_catalog($params['catalog']);

			if (!is_array($params['contacts']) ){
				header("Location: index.php?menuaction=mobile.ui_mobilecc.index&error_message=".lang("please select one contact"));
			}else{

				$status = $this->bo->remove_multiple_entries($params['contacts']);
				
				$type = $this->page_info['actual_catalog']!=='bo_group_manager'?"contacts":"groups";
				
				if($status['success'])
					header("Location: index.php?menuaction=mobile.ui_mobilecc.index&success_message=".lang("selected $type were removed successfully"));
				else
					header("Location: index.php?menuaction=mobile.ui_mobilecc.index&error_message=".lang("one or more $type couldnt be removed"));
			}
		}
		
		static function print_contacts($contacts,$show_checkbox=false,$request_from = null) {
			$functions = CreateObject('mobile.common_functions');
			$p = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
					$p->set_file(
						Array(
							'cc_t' => 'contacts_list.tpl'
						)
					);
			$p->set_block('cc_t', 'rows_contacts');
			$p->set_block('cc_t', 'row_contacts');
			$p->set_block('cc_t', 'row_groups');
			$p->set_block('cc_t', 'no_contacts');

			$bg = "bg-azul";
			if(!empty($contacts)) {
				foreach($contacts as $id => $contact) {

					$p->set_var('show_check',$show_checkbox?"inline":"none");
					$p->set_var('bg',$bg=="bg-azul"?$bg="bg-branco":$bg="bg-azul");
					if($show_checkbox)
						$p->set_var("details","email-corpo");
					else
						$p->set_var("details","limpar_div margin-geral");	
					if($contact["catalog"]!=="bo_group_manager") {	//Contatos		
						$id=strpos($contact["catalog"],"ldap")===false?$contact["id_contact"]:$id;

                        $mail = '&nbsp;'; $tel = '&nbsp;';
                        foreach($contact['connections'] as $key => $conn) {

                            $test = false;
							if ($conn['connection_is_default']) {
                                $test = true;
							}
                           
                            if (is_array($conn)){
                                $test = true;
							}else{
								$test = false;
							}
                            if ( $test) {
                            	if ( ($conn['id_type'] == 1) )
                                    $mail = $conn['connection_value'];
								else if ( ($conn['id_type'] == 2) )
                                    $tel = $conn['connection_value'];
								
								if (($conn['id_type'] == null) || ($conn['id_type'] == '_NONE_')){
									if ( ($conn['type'] == 'email') )
                                    	$mail = $conn['connection_value'];
									else if ( ($conn['type'] == 'phone') )
                                    	$tel = $conn['connection_value'];	
									
								}
							}
						}
						

						
                        $cn = is_array($contact["names_ordered"])?$contact["names_ordered"][0]:$contact["names_ordered"];
                        $vtel = ($tel==null || $tel=='&nbsp;')?"none":"inline";
						
						if(($mail=='&nbsp;' || $mail==null) && isset($request_from))//Se vier de outro módulo e não possuir e-mail, não mostre.
							continue;

						$p->set_var('show_tel',$vtel);
						$p->set_var('email',$mail);
						$p->set_var('tel',$tel);
						$p->set_var('contact_id',$id);
						$p->set_var('lang_tel',lang("tel"));
						$p->set_var('contact_name',$functions->strach_string($cn,17));

						$block = "row_contacts";
					}
					else { //Grupos
						$id=$contact["id_group"];
						$mail = $cn = $contact["title"];
						$p->set_var('group_id',$contact["id_group"]);
						$p->set_var('group_name',$contact["title"]);
						$block = "row_groups";
					}
					
					if($request_from==null) {
						$p->set_var('lang_see_details',lang("details"));
						$cat_encode = urlencode($contact["catalog"]);
						$p->set_var('href_details',"ui_mobilecc.contact_view&id=$id&catalog=".urlencode($contact["catalog"]));
					}
					else {
						$p->set_var('lang_see_details',lang("select"));
						$p->set_var("href_details","ui_mobilemail.add_recipient&mail=$mail&cn=$cn");
					}
					
					$p->fp('rows',$block,True);

				}
				
			}
			else {
				$p->set_var("lang_no_results",lang("no results found"));
				$p->parse("rows","no_contacts");
			}
			return $p->fp('out','rows_contacts');
		}

		/**
		 * Show details from contact selected
		 * 
		 * @param $id int
		 * @param $catalog String
		 * @return $contact
		 */
		function contact_view($params)
		{

			if ( empty($params['id']) || empty($params['catalog']) )
			{
				header('Location: ../mobile/index.php?menuaction=mobile.ui_mobilecc.init_cc');
			}
			
			$this->template->set_file(
				Array(
					'cc_v' => 'contact_view.tpl'
				)
			);

			if ( isset($params['success'])){
				$GLOBALS['phpgw_info']['mobiletemplate']->set_success_msg(lang('contact save successfully'));
			}

			$this->template->set_block('cc_v','body');
			$this->template->set_block('cc_v','people');
			$this->template->set_block('cc_v','people_ldap');
			$this->template->set_block('cc_v','group');
			$this->template->set_block('cc_v','group_row');
			$this->template->set_block('cc_v','buttom');
			$this->template->set_block('cc_v','buttom_use_contact');
			$this->template->set_block('cc_v','row_view_operacao');

			$this->template->set_var('title_view_contact',lang("title view contact"));
			$email_to = "";

			switch ($params['catalog'])
			{

				case 'bo_shared_people_manager';
				case 'bo_people_catalog':

					$this->template->set_var('lang_contact_title',lang("context contact"));
					$this->bo->set_catalog($params['catalog']);

					$result = $this->bo->bo->get_single_entry($params['id'], array("given_names"=>true,"names_ordered"=>true,"alias"=>true,"family_names"=>true,"companies"=>true,"relations"=>true,"connections"=>true));

					asort($result['connections']);

					$this->template->set_var('photo', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_photo&id='.$params['id']);
					$this->template->set_var('id',$params['id']);
					$this->template->set_var('catalog',$params['catalog']);
		
					$this->template->set_var('cc_name',$result['names_ordered']);
					$this->template->set_var('lang_title_alias',lang("Alias"));
					$this->template->set_var('lang_alias',$result['alias']);
					
					$this->template->set_var('lang_title_name',lang("Name"));
					$this->template->set_var('lang_name',$result['given_names']);
					
					$this->template->set_var('lang_title_lastname',lang("Family Names"));
					$this->template->set_var('lang_lastname',$result['family_names']);
					
					$var_phone = "";
					$var_email = "";
					foreach($result['connections'] as $conn):
						if ( $conn['id_type'] == 1 ){
							if ( !empty($var_email) )
								$var_email .= ' | ';
							$var_email .= $conn['connection_value'];
							
							if ( empty($email_to) )
								$email_to = $var_email;
							
						}else if ($conn['id_type'] == 2){
							if ( !empty($var_phone))
								$var_phone .= ' | ';
							$var_phone .= $conn['connection_value'];
						}

						if (($conn['id_type'] == null) || ($conn['id_type'] == '_NONE_')){
							if ( $conn['type'] == 'email' ){
								if ( !empty($var_email) )
									$var_email .= ' | ';
								$var_email .= $conn['connection_value'];
								
								if ( empty($email_to) )
									$email_to = $var_email;
								
							}else if ($conn['type'] == 'phone'){
								if ( !empty($var_phone))
									$var_phone .= ' | ';
								$var_phone .= $conn['connection_value'];
							}
						}
					endforeach;
					
					$this->template->set_var('lang_title_email',lang("Email"));
					$this->template->set_var('lang_email',$var_email);
					
					$this->template->set_var('lang_title_phone',lang("Phone"));
					$this->template->set_var('lang_phone',$var_phone);
	
					$this->template->set_var('lang_edit',lang("edit"));
					
					$this->template->parse("row_body","people");
					
					if ($params['catalog'] == 'bo_people_catalog')
						$this->template->parse("buttom_editar","buttom");
							
					break;
							
				case 'bo_group_manager':
					
					$this->template->set_var('lang_contact_title',lang("context group"));
					$this->bo->set_catalog($params['catalog']);
					$result = $this->bo->bo->get_single_entry($params['id'], array("id_group"=>true,"title"=>true,"short_name"=>true));
					$data   = $this->bo->bo->get_contacts_by_group($params['id']);

					$email_to = '<'.$result['short_name'].'>';
					
					$this->template->set_var('title_view_contact', $result['title']);
					$this->template->set_var('email_to', $email_to);

					$this->template->set_var('lang_title_name',lang("Name"));
					$this->template->set_var('lang_title_email',lang("Email"));
					
					foreach($data as $dados){
						$this->template->set_var('lang_name', $dados['names_ordered']);
						$this->template->set_var('lang_email', $dados['connection_value']);
						$this->template->set_var('bg',$bg=="bg-azul"?$bg="bg-branco":$bg="bg-azul");
						
						$this->template->set_var('href_details',"ui_mobilecc.contact_view&id=".$dados['id_contact']."&catalog=bo_people_catalog");
						
						$this->template->fp('group_rows','group_row',True);
					}

					$this->template->set_var('email_to', $email_to);
					$this->template->parse("buttom_use","buttom_use_contact");
					
					$this->template->parse("row_body","group");
					
					break;
						
				default:		
					if( strpos($params['catalog'],'bo_global_ldap_catalog#') === false )
					{ 
						header('Location: ../mobile/index.php?menuaction=mobile.ui_mobilecc.init_cc');
					}
					else
					{
						$this->bo->set_catalog($params['catalog']);
						$fields = $this->bo->bo->get_fields(true);
						$result = $this->bo->bo->get_single_entry($params['id'], $fields);
						                                                                        
						// SessionStart
						session_start();
						$_SESSION['phpgw_info']['mobile']['photoCatalog'][$params['id']] = $result['photo'];
						session_write_close();
						
						$this->template->set_var('photo', '../index.php?menuaction=mobile.ui_mobilecc.getPhoto&id=' . $params["id"]);
						
						$this->template->set_var('cc_name',$result['names_ordered'][0]);
						
						$this->template->set_var('lang_title_name',lang("Name"));
						$this->template->set_var('lang_name',$result['given_names'][0]);
															
						$this->template->set_var('lang_title_lastname',lang("Family Names"));
						$this->template->set_var('lang_lastname',$result['family_names'][0]);
															
						$var_phone = "";
						$var_email = "";
						foreach($result['connections'] as $conn)
						{
							if ( $conn['id_type'] == 1 )
							{
								if ( !empty($var_email) )
									$var_email .= ' | ';

								$var_email .= $conn['connection_value'];
																			
								if ( empty($email_to) )
									$email_to = $var_email;
							}
							else if ($conn['id_type'] == 2)
							{
								if ( !empty($var_phone))
									$var_phone .= ' | ';

								$var_phone .= $conn['connection_value'];
							}
							
							if (($conn['id_type'] == null) || ($conn['id_type'] == '_NONE_'))
							{
								if ( $conn['type'] == 'email' )
								{
									if ( !empty($var_email) )
										$var_email .= ' | ';
									
									$var_email .= $conn['connection_value'];
																					
									if ( empty($email_to) )
										$email_to = $var_email;
								}
								else if ($conn['type'] == 'phone')
								{
									if ( !empty($var_phone) )
										$var_phone .= ' | ';
									
									$var_phone .= $conn['connection_value'];
								}
							}
						}
												
						$this->template->set_var('email_to', $email_to);
															
						$this->template->set_var('lang_title_email',lang("Email"));
						$this->template->set_var('lang_email',$var_email);
															
						$this->template->set_var('lang_title_phone',lang("Phone"));
						$this->template->set_var('lang_phone',$var_phone);
						
						$this->template->parse("row_body","people_ldap");
						
						}
						
						break;
			}

			if ( !empty($email_to))
                        {
				$this->template->set_var('email_to', $email_to);
				$this->template->parse("buttom_use","buttom_use_contact");
				$this->template->parse("row_operacao","row_view_operacao");
			}
			else if ($params['catalog'] == 'bo_people_catalog')
			{
				$this->template->parse("row_operacao","row_view_operacao");
			}

			$linkBack = explode("&", $GLOBALS['phpgw_info']['mobiletemplate']->get_back_link() );
			$link = $linkBack[0]."&catalog=".$params['catalog']."&".$linkBack[2];
			
			$this->template->set_var('lang_back',lang("back"));
			
			$this->template->set_var('href_back',$link);
			
			$this->template->set_var('lang_use_contact',lang("use contact"));
			$this->template->set_var('lang_selecteds',lang("selecteds"));

			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out','body'));
		}
                
		function getPhoto()
		{
			$id = $_GET['id'];
			
			session_start();
			
			if( isset( $_SESSION['phpgw_info']['mobile']['photoCatalog'][$id] ) )
			{
				$photo = imagecreatefromstring($_SESSION['phpgw_info']['mobile']['photoCatalog'][$id]);
				
				header("Content-Type: image/jpeg");
				$width = imagesx($photo);
				$height = imagesy($photo);
				$twidth = 70;
				$theight = 90;
				$small_photo = imagecreatetruecolor ($twidth, $theight);
				imagecopyresampled($small_photo, $photo, 0, 0, 0, 0,$twidth, $theight, $width, $height);
				imagejpeg($small_photo,'',100);
				
				unset( $_SESSION['phpgw_info']['mobile']['photoCatalog'][$id] );
			}    
			else
			{
				header('Content-type: image/png');
				echo file_get_contents(PHPGW_INCLUDE_ROOT.'/contactcenter/templates/default/images/photo_celepar.png');
			}
			
			session_write_close();
			
			return;
		}

		/**
		 * View Add/Edit contact
		 * 
		 * @param $id int
		 * @param $catalog String
		 * @return $contact
		 */
		function contact_add_edit($params) {

			$this->template->set_file(
				Array(
					'cc_e' => 'contact_add_edit.tpl'
				)
			);

			$this->template->set_block('cc_e','body');

			$view = false;
			if ( isset($params['erro'])){
				$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg($params['erro']);
				
				$result['alias'] 			= $params['alias'];
				$result['given_names'] 		= $params['given_names'];
				$result['family_names'] 	= $params['family_names'];
				$result['names_ordered']	= $params['names_ordered'];
				$var_phone 					= $params['phone'];
				$var_email 					= $params['email'];
				$var_connection_email 		= $params['id_connection_email'];
				$var_connection_phone 		= $params['id_connection_phone'];
				$view = true;
				
			}

			if ( empty($params['id']) ){
				$title_contact = "title add contact";
				$form_action = "index.php?menuaction=mobile.ui_mobilecc.contact_add";
				$confirm = lang("confirm add");
				$params['catalog'] = 'bo_people_catalog';
			}else{
				$title_contact = "title edit contact";
				$form_action = "index.php?menuaction=mobile.ui_mobilecc.contact_edit";
				$confirm = lang("confirm edit");
				
				$view = true;

				if ( !isset($params['erro']))
				{
					$this->bo->set_catalog($params['catalog']);
					$result = $this->bo->bo->get_single_entry($params['id'], array("given_names"=>true,"names_ordered"=>true,"alias"=>true,"family_names"=>true,"companies"=>true,"relations"=>true,"connections"=>true));

					$var_phone = "";
					$var_email = "";
					foreach($result['connections'] as $conn):
						if ( $conn['id_type'] == 1 ){
							if ( (empty($var_email)) && ($conn['connection_is_default']) ){
								$var_email = $conn['connection_value'];
								$var_connection_email = $conn['id_connection'];
							}
						}else if ($conn['id_type'] == 2){
							if ( (empty($var_phone)) &&  ($conn['connection_is_default'])){
								$var_phone = $conn['connection_value'];
								$var_connection_phone = $conn['id_connection'];
							}
						}
					endforeach;
				}

								
			}
			
			if ($view){
					$this->template->set_var('lang_alias',$result['alias']);
					$this->template->set_var('lang_name',$result['given_names']);
					$this->template->set_var('lang_lastname',$result['family_names']);
					
					$this->template->set_var('var_connection_email', $var_connection_email);
					$this->template->set_var('lang_email',$var_email);
					
					$this->template->set_var('var_connection_phone', $var_connection_phone);
					$this->template->set_var('lang_phone',$var_phone);

			}
			
			$this->template->set_var('cc_name',$result['names_ordered']);
			$this->template->set_var('lang_title_alias',lang("Alias"));
			$this->template->set_var('lang_title_name',lang("Name"));
			$this->template->set_var('lang_title_lastname',lang("Family Names"));
			$this->template->set_var('lang_title_email',lang("Email"));
			$this->template->set_var('lang_title_phone',lang("Phone"));

			$this->template->set_var('catalog', $params['catalog']);
				
			$this->template->set_var('lang_title_add_edit',lang($title_contact));
			$this->template->set_var('form_action', $form_action);
			
			$this->template->set_var('lang_contact_title',lang("context contact"));
			$this->template->set_var('lang_back',lang("back"));
			$this->template->set_var('href_back',$GLOBALS['phpgw_info']['mobiletemplate']->get_back_link());
			$this->template->set_var('lang_cancel',lang("cancel"));
			$this->template->set_var('lang_confirm', $confirm);
			$this->template->set_var('lang_selecteds',lang("selecteds"));
			$this->template->set_var('id',$params['id']);
			
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out','body'));
		}
		
		/**
		 * Add contact
		 * 
		 * @param $id int
		 * @param $catalog String
		 * @return $contact
		 */
		function contact_add($params) {
			
			$data['alias'] = $params['alias'];
			$data['given_names'] = $params['given_names'];
			$data['family_names'] = $params['family_names'];
			$data['names_ordered'] = $data['given_names'] . ' ' . $data['family_names'];
			$data['is_quick_add'] = true;

			$answer = $this->verifyData($params);
			
			if (!empty($answer)){
				$retorno = 'alias='.$data['alias'] . '&given_names='.$data['given_names'] . '&family_names='.$data['family_names'] . '&names_ordered='.$data['given_names'] . ' ' . $data['family_names'];
				$retorno .= '&id_connection_email='.$params['id_connection_email'] . '&id_connection_phone='.$params['id_connection_phone'];
				$retorno .= '&email='.$params['email'] . '&phone='.$params['phone'];
				header('Location: ../mobile/index.php?menuaction=mobile.ui_mobilecc.contact_add_edit&erro='.$answer.'&'.$retorno);
			}
			else
			{
				$this->bo->set_catalog($params['catalog']);
	
				if ( !empty($params['email']) ){
					$data['connections']['default_email']['connection_value'] = $params['email'];
				}
				
				if ( !empty($params['phone']) ){
					$data['connections']['default_phone']['connection_value'] = $params['phone'];
				}
				$this->bo->set_catalog($params['catalog']);
				$contact_id = $this->bo->bo->quick_add($data);
	
				header('Location: ../mobile/index.php?menuaction=mobile.ui_mobilecc.contact_view&id='.$contact_id.'&catalog='.$params['catalog'] . '&success=1');
			}
		}

		/**
		 * Edit contact
		 * 
		 * @param $id int
		 * @param $catalog String
		 * @return $contact
		 */
		function contact_edit($params) {
			
			$data['alias'] = $params['alias'];
			$data['given_names'] = $params['given_names'];
			$data['family_names'] = $params['family_names'];
			$data['names_ordered'] = $data['given_names'] . ' ' . $data['family_names'];
			
			$cont = 0;

			$answer = $this->verifyData($params);

			if (!empty($answer)){
				$retorno = '&catalog=' . $params['catalog'] . '&id='. $params['id'] .'&alias='.$data['alias'] . '&given_names='.$data['given_names'] . '&family_names='.$data['family_names'] . '&names_ordered='.$data['given_names'] . ' ' . $data['family_names'];
				$retorno .= '&id_connection_email='.$params['id_connection_email'] . '&id_connection_phone='.$params['id_connection_phone'];
				$retorno .= '&email='.$params['email'] . '&phone='.$params['phone'];
				header('Location: ../mobile/index.php?menuaction=mobile.ui_mobilecc.contact_add_edit&erro='.$answer.'&'.$retorno);
			}
			else
			{
				$this->bo->set_catalog($params['catalog']);
				$types = $this->bo->bo->get_all_connections_types();
	
				if ( !empty($params['email']) || !empty($params['id_connection_email']) ){
					++$cont;
				
					if (empty($params['id_connection_email'])){
						$data['connections']['connection' . $cont]['connection_is_default'] = true;
						$data['connections']['connection' . $cont]['connection_name'] = $types[1];
					}
	
					$data['connections']['connection' . $cont]['id_connection'] = $params['id_connection_email'];
					$data['connections']['connection' . $cont]['id_typeof_connection'] = 1;
					$data['connections']['connection' . $cont]['connection_value'] = $params['email'];
	
				}
	
				if ( !empty($params['phone']) || !empty($params['id_connection_phone']) ){
					++$cont;
	
					if (empty($params['id_connection_phone'])){
						$data['connections']['connection' . $cont]['connection_is_default'] = true;
						$data['connections']['connection' . $cont]['connection_name'] = $types[2];
					}
	
					$data['connections']['connection' . $cont]['id_connection'] = $params['id_connection_phone'];
					$data['connections']['connection' . $cont]['id_typeof_connection'] = 2;
					$data['connections']['connection' . $cont]['connection_value'] = $params['phone'];
	
				}
	
				$contact_id = $this->bo->bo->update_single_info($params['id'], $data);

				header('Location: ../mobile/index.php?menuaction=mobile.ui_mobilecc.contact_view&id='.$contact_id.'&catalog='.$params['catalog'].'&success=1');
				
			}
			
		}

		/**
		 * Validate data when register contact
		 * Validate E-mail, Phone and Name(NotEmpty)
		 * 
		 * @param $data Array
		 * @return Boolean
		 */
		static function verifyData($data){ 

			$valid = '';
			$field = false;

			// Verify if phone is valid
			if ( !empty($data['phone']) && empty($valid) ){
				
				$field = true;
				
				$pattern = "#^(?:(?:\(?\+?(?P<country>\d{2,4})\)?\s*)?\(?(?P<city>\d{2,3})\)?\s*)?(?P<n1>\d{3,4})[-\s.]?(?P<n2>\d{4})$#";

				if (!preg_match($pattern, $data['phone'])){
					$valid = lang('invalid field phone');
				}

			}

			// Verify if e-mail is valid
			if ( !empty($data['email']) ){

				$field = true;
				
				$pattern = "^[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\-]+\.[a-z]{2,4}$";

				if (!preg_match("/$pattern/i", $data['email'])){
					$valid = lang('invalid field e-mail');
				}

			}

			// Verify if exist e-mail or phone
			if (!$field)
				$valid = lang('Tel or email is required');
			
			// Verify if name is empty
			if ( empty($data['given_names']) )
				$valid = lang('Name is mandatory');
			
			return $valid;
			
		}
	}
?>