<?php
class ui_home
{
	var $imap_functions;
	var $db;
	//var $bocalendar;
	var $bo_mobilemail;
	var $common;
	var $template;
    	
	var $public_functions = array(
		'dicas' => true,
		'index' => true,
		'search' => true,
		'change_template' => true
	);
		
	function ui_home()
	{
		$this->template = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
		$this->common	= CreateObject('mobile.common_functions');
		$this->imap_functions	= CreateObject('expressoMail1_2.imap_functions');
		$this->db	= CreateObject('phpgwapi.db');
		//$this->bocalendar = CreateObject('calendar.bocalendar');
		$this->bo_mobilemail = CreateObject('mobile.bo_mobilemail');
	}
		
	function change_template($params)
	{
		$GLOBALS['phpgw']->session->appsession('mobile.layout','mobile',$params['template']);
		header("location: index.php?menuaction=mobile.ui_mobilemail.change_folder&folder=0");
	}

	function dicas()
	{
		$browser = CreateObject('phpgwapi.browser');

		$this->template->set_file(array('dicas' => 'dicas.tpl'));
		$plataform = ( ( $browser->get_platform() ) === "Android" ) ? "android" : "iphone";
		$this->template->set_block('dicas',$plataform);
		$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out', $plataform));
	}
	
	function index($params)
	{
		$this->template->set_file(array('home_index' => 'home_index.tpl'));
		$this->template->set_file(array('home_search_bar' => 'search_bar.tpl'));
				
		$this->template->set_block('home_index','page');	
		$this->template->set_block('home_index','folder_block');
		$this->template->set_block('home_index','commitment_block');	
		$this->template->set_block('home_search_bar','search_bar');

		if( isset($_SESSION['mobile']['displayIOS']) && $_SESSION['mobile']['displayIOS'] == "true" )
			$this->template->set_var('display_IOS', "none");
		else
			$this->template->set_var('display_IOS', "block");
		
		//langs
		$this->template->set_var('lang_context_email', lang("context email"));
		$this->template->set_var('lang_context_contact', lang("context contact"));
		$this->template->set_var('lang_context_commitment', lang("context commitment"));
		$this->template->set_var('lang_search', lang("search"));
		$this->template->set_var('lang_my_mail', lang("my mail"));
		$this->template->set_var('lang_my_folders', lang("my folders"));
		$this->template->set_var('lang_my_commitments', lang("my commitments"));
		$this->template->set_var('lang_my_contacts', lang("my contacts"));
		$this->template->set_var('lang_new_mail', lang("new mail"));
		$this->template->set_var('lang_mark_as_read', lang("mark as read"));
		$this->template->set_var('lang_selected', lang("selected"));
		
		$accountId = $GLOBALS['phpgw_info']['user']['account_id'];
		
		//pegando as pastas
		$default_folders = $this->imap_functions->get_folders_list(array('noSharedFolders' => true, 'folderType' => 'default', 'noQuotaInfo' => true));
		$total_quota = $this->imap_functions->get_quota(array());

		$this->set_folder_block($default_folders, "default_folders_box");
		
		$personal_folders = $this->imap_functions->get_folders_list(array('noSharedFolders' => true, 'folderType' => 'personal', 'noQuotaInfo' => true));
		$this->set_folder_block($personal_folders, "personal_folders_box", sizeof($default_folders));
		
		$this->template->set_var('quota_percent', $total_quota["quota_percent"]);
		$this->template->set_var('quota_used', $this->common->borkb($total_quota["quota_used"]*1024));
		$this->template->set_var('quota_limit', $this->common->borkb($total_quota["quota_limit"]*1024));
		/*
		//pegando os eventos do dia atual
		$year	= $this->bocalendar->year;
		$month	= $this->bocalendar->month;
		$day	= $this->bocalendar->day;
		
		$tstart = mktime(0,0,0,$month,$day,$year);
		
		$tstop = $tstart + 86400; //(24horas*60min*60seg*1dia)
		$this->bocalendar->so->owner = $accountId;
		$this->bocalendar->so->open_box($accountId);
		$this->bocalendar->store_to_cache( array(
			'owner'	=> $accountId,
			'syear'  => date('Y',$tstart),
			'smonth' => date('m',$tstart),
			'sday'   => date('d',$tstart),
			'eyear'  => date('Y',$tstop),
			'emonth' => date('m',$tstop),
			'eday'   => date('d',$tstop)
		) );
		
		$events = $this->bocalendar->cached_events;
		
		foreach($events[$year.$this->common->complete_string($month,2,"R","0").$this->common->complete_string($day,2,"R","0")] as $index=>$event)
		{
			$this->template->set_var('commitment_class', (($index%2==0) ? "fundo-azul-alinha" : "fundo-branco-alinha") );
			$this->template->set_var('commitment_time', $this->common->complete_string($event["start"]["hour"],2,"R","0") .":". $this->common->complete_string($event["start"]["min"],2,"R","0") );
			$this->template->set_var('commitment_title', $event["title"] );
			
			$this->template->parse('commitments_box', 'commitment_block' ,true);
		}
		
		*/
		
		if($GLOBALS['phpgw']->session->appsession('mobile.layout','mobile')=="mini_desktop") {
			$GLOBALS['phpgw_info']['mobiletemplate']->set_home($this->template->fp('out', 'page'));
		} else {
			$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg($params["error_message"]);
			$GLOBALS['phpgw_info']['mobiletemplate']->set_success_msg($params["success_message"]);
			$this->template->set_var('search',$this->template->fp('out','search_bar'));
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out', 'page'));
		}
	}
		
	/**
	 * 
	 *
	 * $index_increment utilizado para quando passar o array das pastas pessoais somar com a quantidade de pastas default.
	 */
	function set_folder_block($folders, $box_target, $index_increment = 0) {
		foreach($folders as $index=>$folder) 
		{
			$this->template->set_var('folder_class', (($index%2==0) ? "par" : "par1") );
			$this->template->set_var('folder_id', $index + $index_increment );
			
			$translated_folder_name = $this->bo_mobilemail->get_translate_default_folder_name_from_id($folder["folder_id"]);
			
			$this->template->set_var('folder_name', (($translated_folder_name == "") ? $folder["folder_name"] : $translated_folder_name) );
			$this->template->set_var('folder_unseen', $folder["folder_unseen"] );
			
			$this->imap_functions->open_mbox($folder["folder_id"],true);
			$this->template->set_var('folder_total_msg', $this->imap_functions->get_num_msgs(array('folder' => $folder["folder_id"] ) ) );
			
			$this->template->parse($box_target, 'folder_block' ,true);
		}
	}
		
    function search($params) {
    	
    	if($_SERVER["HTTP_REFERER"] && strpos($_SERVER["HTTP_REFERER"], "ui_home.index")) {
    		if(!$params["default_folders"] &&
					 !$params["personal_folders"] &&
					 !$params["calendar_search"] &&
					 !$params["contacts_search"]) {
					 	
						header('Location: index.php?menuaction=menuaction=mobile.ui_home.index&error_message='.lang("need choose one option"));
					 }
    	}
    		
				
			include_once "class.ui_mobilecc.inc.php";
			$ui_mobilemail = CreateObject("mobile.ui_mobilemail");//Necessário para lista de emails, que é uma função estática

			$p = $this->template;
			$p->set_file(array('home_search' => 'home_search.tpl'));
			$p->set_file(array('home_search_bar' => 'search_bar.tpl'));
			
			//Langs gerais da página
			$p->set_block('home_search','main');
			$p->set_block("home_search","row_events");
			$p->set_block("home_search","no_events");
			$p->set_block('home_search_bar','search_bar');
			
			if( isset($_SESSION['mobile']['displayIOS']) && $_SESSION['mobile']['displayIOS'] == "true" )
				$p->set_var('display_IOS', "none");
			else
				$p->set_var('display_IOS', "block");
				
			$p->set_var('search_param',$params['name']);
			$p->set_var('lang_back',lang('back'));
			$p->set_var('href_back',$GLOBALS['phpgw_info']['mobiletemplate']->get_back_link());
			$p->set_var('lang_new_message',ucfirst(lang('new message')));
			$p->set_var('lang_search',lang('search'));
			$p->set_var('lang_search_return',lang('search return'));
			$p->set_var('lang_your_search_was_by',ucfirst(lang('your search was by')));
			$p->set_var('lang_emails',ucfirst(lang('e-mails')));
			$p->set_var('lang_contacts',ucfirst(lang('contacts')));
			$p->set_var('lang_calendar',ucfirst(lang('calendar')));
			$p->set_var('lang_events',lang('events'));
			$p->set_var('default_folders',$params['default_folders']);
			$p->set_var('personal_folders',$params['personal_folders']);
			$p->set_var('folder_to_search',$params['folder_to_search']);
			$p->set_var('contacts_search',$params['contacts_search']);	
			$p->set_var('catalog_to_search',$params['catalog_to_search']);
			$p->set_var('calendar_search',$params['calendar_search']);
			$p->set_var('lang_more',lang("more"));	
			$p->set_var('lang_messages',lang("messages"));
			$p->set_var('show_more_contacts',"none");
			$p->set_var('show_more_messages',"none");
			$p->set_var('show_more_events',"none");
			$p->set_var('contacts_request_from',
				isset($params["request_from"])?
				$params["request_from"]:"none");
				
			if($GLOBALS['phpgw']->session->appsession('mobile.layout','mobile')!="mini_desktop") {
				$p->set_var('search',$p->fp('out','search_bar'));
			}

			if(!$params['name'] || trim($params['name']) == "" || strlen($params['name']) < 5 ) {
				$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg(lang("search word need not be empty and has more then four char"));
			} else {
				//E-mails
				$no_mail_search=false;
				if((!isset($params['folder_to_search'])) || ($params['folder_to_search']==="")) {
					if($params['default_folders']==="1") {
						if($params['personal_folders']!=="1")
							$mail_params['folderType'] = 'default';
					}
					else {
						if($params['personal_folders']==="1")
							$mail_params['folderType'] = 'personal';
						else
							$no_mail_search = true;
					}
				}
				else {
					$mail_params['folder'] = $params['folder_to_search'];
				}

				if(!$no_mail_search) {
					$imap_functions = CreateObject('expressoMail1_2.imap_functions');
					$mail_params['filter'] = $params["name"];
					$mail_params['max_msgs'] = isset($params['max_msgs'])?$params['max_msgs']:10;
				
					$p->set_var('next_max_msgs',$mail_params['max_msgs']+10);
					$p->set_var('max_msgs',$mail_params['max_msgs']);
					
					$messages = $imap_functions->mobile_search($mail_params);
					if($messages["has_more_msg"])
						$p->set_var('show_more_messages',"block");
					else
						$p->set_var('show_more_messages',"none");
					$p->set_var('mails',$ui_mobilemail->print_mails_list($messages['msgs']));
				}
				else {
					$p->set_var('show_mails',"none");
				}
			
				//Agenda
				if($params["calendar_search"]==="1") {
				
					$bo_calendar = CreateObject('calendar.bocalendar',1);
					$functions = CreateObject('mobile.common_functions');
				
					$max_events = isset($params['max_events'])?$params['max_events']:10;
					$p->set_var('next_max_events',$max_events+10);
					$p->set_var('max_events',$max_events);
				
					$event_ids = $bo_calendar->search_keywords($params['name']);
				
					$bg = "fundo-azul-alinha";
					if(!empty($event_ids)) {
						$total_events_search = count($event_ids);
						$event_ids = array_slice($event_ids,0,$max_events,true);
						if($total_events_search>count($event_ids))
							$p->set_var('show_more_events',"block");
						foreach($event_ids as $key => $id)
						{
							$event = $bo_calendar->read_entry($id);
						
							if(!$bo_calendar->check_perms(PHPGW_ACL_READ,$event))
							{
								continue;
							}
	
							$p->set_var("bg",$bg=="fundo-azul-alinha"?$bg="fundo-branco-alinha":$bg="fundo-azul-alinha");
							$p->set_var("date",$functions->complete_string($event["start"]["mday"],2,"R","0")."/".
												$functions->complete_string($event["start"]["month"],2,"R","0")."/".
												$event["start"]["year"]." ".
												$functions->complete_string($event["start"]["hour"],2,"R","0").":".
												$functions->complete_string($event["start"]["min"],2,"R","0"));
							$p->set_var("title",$event["title"],40);
							$p->fp("calendar_results","row_events",True);
				
						}
					}
					else {
						$p->set_var("lang_no_results",lang("no results found"));
						$p->parse("calendar_results","no_events");
					}
				}
				else {
					$p->set_var("show_calendar","none");
				}
			
				//Contatos			
				if(($params["contacts_search"]==="1") || 
						(isset($params["catalog_to_search"]) && $params["catalog_to_search"]!=="")) {

					$bo_cc =  CreateObject('mobile.bo_mobilecc');
				
					if(isset($params["catalog_to_search"]) && $params["catalog_to_search"]!=="")
						$catalogs = array(0=>array("catalog"=>$params["catalog_to_search"],
												"label" => $params["catalog_to_search"]));
					else 
						$catalogs = $bo_cc->get_all_catalogs();
	
					$max_contacts = isset($params['max_contacts'])?$params['max_contacts']:10;
					$contacts_result = array();
					$p->set_var('next_max_contacts',$max_contacts+10);
					$p->set_var('max_contacts',$max_contacts);
				
				
					foreach($catalogs as $catalog) {
						if($catalog['catalog']==="bo_group_manager")
							continue;
						if(count($contacts_result)>=$max_contacts) {
							$bo_cc->set_catalog($catalog['catalog']);
							$partial_result = $bo_cc->search("%".$params['name']."%","1");
							if(count($partial_result)>1) {
								$p->set_var('show_more_contacts',"block");
							}
							break;
						}
						$max_to_search = $max_contacts - count($contacts_result);//Só posso pedir no máximo o número máximo a ser visto menos o que já foi achado.
						$bo_cc->set_catalog($catalog['catalog']);
						$partial_result = $bo_cc->search("%".$params['name']."%",$max_to_search);
						if($partial_result["has_more"]) {
							$p->set_var('show_more_contacts',"block");
							unset($partial_result["has_more"]);
						}
					
						$contacts_result = array_merge($contacts_result,$partial_result);
					
					}
				
					if(isset($params['request_from'])  && $params['request_from']!="none") //Buscas feitas requisitadas por outros modulos
						$request_from = $params['request_from'];
					else
						$request_from = null;
					$p->set_var('contacts',ui_mobilecc::print_contacts($contacts_result,false));
				}
				else {
					$p->set_var('show_contacts',"none");
				}
			}
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($p->fp('out','main'));

		}
		
		
	}
?>
