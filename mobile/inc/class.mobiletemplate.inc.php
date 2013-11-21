<?php 
//include_once(PHPGW_INCLUDE_ROOT.'/mobile/inc/class.ui_mobile.inc.php');
	class mobiletemplate
	{
		private $template;
		private $href_back;
		
		/*
		* @function mobiletemplate
	 	* @abstract Construtor da classe, que monta o template home
	 	* @author Nilton Emilio Buhrer Neto <niltonneto@celepar.pr.gov.br>
	 	*/		
		function mobiletemplate()
		{
			$this->template = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
			
			$template = $GLOBALS['phpgw']->session->appsession('mobile.layout','mobile');
			
			if( $template == "mini_desktop" )
			{
				$url_expresso = $this->getUrlExpresso() . "index.php";
				
				$this->template->set_file(array('home_t' => 'pc_template.tpl'));
				$this->template->set_file(array('home_t_search_bar' => 'search_bar.tpl'));
				$this->template->set_block('home_t_search_bar','search_bar');
				$this->template->set_var('search',$this->template->fp('out','search_bar'));
				$this->template->set_var('url_expresso',$url_expresso);
				$this->template->set_var('lang_mini_mobile', lang('mini mobile'));
				$this->template->set_var('lang_search_error_message',lang("need choose one option"));
				$this->template->set_var('lang_search_error_message_four_digits',lang("search word need not be empty and has more then four char"));
			}
			else
			{
				$this->template->set_file(array('home_t' => 'template.tpl'));
				$this->template->set_var('lang_mini_desktop', lang('mini desktop'));

			}
			
			$this->template->set_block('home_t', 'mobile_home');
			$this->template->set_block('home_t','success_message');
			$this->template->set_block('home_t','error_message');
		}

		/*
		* @function set_content
	 	* @abstract Carrega o atributo "content" do template principal
	 	* @author Nilton Emilio Buhrer Neto <niltonneto@celepar.pr.gov.br>
	 	*/		
		public function set_content($content){
			$pre_content = $this->template->get_var("content");
			$this->template->set_var("content", $pre_content.$content);
		}
		
		/*
		* @function set_home
	 	* @abstract Carrega o atributo "home" do template principal para PC.
	 	* @author Diógenes Ribeiro Duarte <diogenes.duarte@prodeb.ba.gov.br>
	 	*/	
		public function set_home($content) {
			$pre_content = $this->template->get_var("home");
			$this->template->set_var("home", $pre_content.$content);
		}
		
		/*
		* @function set_msg
	 	* @abstract Seta a mensagem de sucesso ou error a depender do tipo.
	 	* @author Thiago Antonius
	 	*/		
		public function set_msg($msg, $type){
			if(isset($msg) && trim($msg)!="") {
				$this->template->set_var("message", $msg);
				$this->template->parse("message_box", $type."_message", true);
			}
		}
		
		/*
		* @function set_success_msg
	 	* @abstract Seta a mensagem de sucesso.
	 	* @author Thiago Antonius
	 	*/		
		public function set_success_msg($msg){
			$this->set_msg($msg, "success");
		}

			/*
		* @function set_error_msg
	 	* @abstract Seta a mensagem de erro
	 	* @author Thiago Antonius
	 	*/		
		public function set_error_msg($msg){
			$this->set_msg($msg, "error");
		}		

		/*
		* @function print_all
	 	* @abstract Imprime toda tela do Expresso Mini
	 	* @author Nilton Emilio Buhrer Neto <niltonneto@celepar.pr.gov.br>
	 	*/
		public function print_page($class, $method )
		{
			if( $GLOBALS['phpgw']->session->appsession('mobile.layout','mobile') == "mini_desktop" )
			{
				//force to don'	t call ui_home.index in a mini desktop version
				if($class == "ui_home" && $method == "index") {
					$class = "ui_mobilemail";
					$method = "change_folder";
					$_REQUEST["folder"] = 0;
				}
			}
			
			//need be called before invoke the action
			$this->print_header();
			$this->init_mobile();

			if(!($class == 'ui_home' && $method == 'index')) $this->print_navbar();
			$filename = 'inc/class.'.$class.'.inc.php';
			include_once($filename);
			$obj = new $class();
			$obj->$method($_REQUEST);

			if($GLOBALS['phpgw']->session->appsession('mobile.layout','mobile') == "mini_desktop") {
				$ui_home = CreateObject('mobile.ui_home');
				$ui_home->index($_REQUEST);
			}
			
			$this->template->pfp('out', 'mobile_home');
		}

		public function init_mobile()
		{
			$ui_mobilemail = CreateObject('mobile.ui_mobilemail');
			$obj = createobject("expressoMail1_2.functions");
	      	// setting timezone preference
    	  	$zones = $obj->getTimezones();
      		$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['timezone'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['timezone'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['timezone'] : sprintf("%s", array_search("America/Sao_Paulo", $zones));
			
			$this->template->set_var('global_title', lang('expresso mini'));
			$this->template->set_var('style_1','position:absolute; float: left;');
			$this->template->set_var('style_2','position:relative; float: right; display: block');
			$this->template->set_var('lang_tips', lang('Tips'));
			$this->template->set_var('lang_logout', lang('logout'));
			$this->template->set_var('lang_search', lang('search'));
			
			if( isset($_SESSION['mobile']['displayIOS']) && $_SESSION['mobile']['displayIOS'] == "true" )
			{
				$this->template->set_var('display_IOS', "none");
			}
			else
			{
				$this->template->set_var('display_IOS', "block");
			}

			if( isset($_COOKIE['lem']) && isset($_COOKIE['pem']) )
			{
				$this->template->set_var('href_logout', 'login.php?cd=logout_mobile');
			}
			else
			{
				$this->template->set_var('href_logout', 'login.php?cd=1');
			}
		}
		
		/*
		* @function print_navbar
	 	* @abstract Imprime o início da tela do Expresso Mini => barra de navegação
	 	* @author Mário César Kolling <mario.kolling@serpro.gov.br>
	 	*/
		private function print_navbar(){
			$this->template->set_var('lang_back', lang('Back'));
			$this->template->set_var('lang_home', lang('home'));
			$this->template->set_var('lang_email', lang('E-mail'));
			$this->template->set_var('lang_contacts', lang('Contact Center'));
			$this->template->set_var('lang_calendar', lang('Calendar'));
			$this->template->set_var('href_cc', 'index.php?menuaction=mobile.ui_mobilecc.init_cc');
			$this->template->set_var('href_email', "index.php?menuaction=mobile.ui_mobilemail.change_folder&folder=0");
			$this->template->set_var('href_calendar', "index.php?menuaction=mobile.ui_mobilecalendar.index");
			$this->template->set_var('href_home', "index.php?menuaction=mobile.ui_home.index");
		}

		private function process_back_link() {
			$trace = $GLOBALS['phpgw']->session->appsession('mobile.trace_urls','mobile');

			$trace_idx = 0;

			if($trace) {
				$trace_idx = sizeof($trace)-1;

				if($_REQUEST["is_back_link"])
					$trace_idx = $trace_idx - 2;

				if($trace_idx < 0) $trace_idx = 0;

				$url = $trace[$trace_idx]["url"];
				$params = $trace[$trace_idx]["request"];

				$this->href_back = $this->build_url($url, $params);
			} else {
				$trace = array();
				$this->href_back = '';
			}
			
			if(!$_REQUEST["is_back_link"]) {
				$current_url = $_SERVER["SCRIPT_NAME"];
				$current_params = $_REQUEST;

				unset($current_params['sessionid']);
				unset($current_params['domain']);
				unset($current_params['last_loginid']);
				unset($current_params['last_domain']);
				unset($current_params['kp3']);
				unset($current_params['showHeader']);

				$built_current_url = $this->build_url($current_url, $current_params);
				
				if( (preg_match('/menuaction/i',$built_current_url) 
				&& !preg_match('/mobile\.ui_mobilemail\.(send_mail|save_draft|delete_msg)/i',$built_current_url) )
				&& !$current_params["ignore_trace_url"] ) {

					//se der reload não vai gravar a url e vai sobreescrever o link do botão voltar para não voltar para a mesma página
					if(str_replace("&is_back_link=true","",$built_current_url) != str_replace("&is_back_link=true","",$this->href_back))
						$trace[sizeof($trace)] = array("url" => $current_url, "request" => $current_params);
					else {
						if($trace_idx >= 1) $trace_idx--;
						$this->href_back = $this->build_url($trace[$trace_idx]["url"], $trace[$trace_idx]["request"]);
					}
				}
			} else {
				if(sizeof($trace) > 1)
					unset($trace[sizeof($trace)-1]);
			}
			
			if(sizeof($trace) > 0)
				$GLOBALS['phpgw']->session->appsession('mobile.trace_urls','mobile',$trace);
			else
				$GLOBALS['phpgw']->session->appsession('mobile.trace_urls','mobile',null);
			
			$this->template->set_var('href_back', $this->href_back);
		}
		
		private function build_url($url, $params) {
			$query_string = "";
			
			foreach ($params as $key => $value) {
				$query_string .= "&".$key."=".$value;
			}

			if($query_string != "")
				$url .= "?".substr($query_string,1);
			
			if(!$params["is_back_link"])
				$url .= "&is_back_link=true";
			
			return $url;
		}
		
		private function getUrlExpresso()
		{
			$url_expresso = $GLOBALS['phpgw_info']['server']['webserver_url'];
			$url_expresso = ( !empty($url_expresso) ) ? $url_expresso : '/';
	
			if(strrpos($url_expresso,'/') === false || strrpos($url_expresso,'/') != (strlen($url_expresso)-1))
			{
				$url_expresso .= '/';
			}

			return $url_expresso;
		}

		function get_back_link(){
			return $this->href_back;
		}

		/*
		* @function print_header
	 	* @abstract Imprime o início da tela do Expresso Mini => headers html
	 	* @author Mário César Kolling <mario.kolling@serpro.gov.br>
	 	*/		
		private function print_header(){
			$GLOBALS['phpgw']->accounts->read_repository();
			$var  = Array('title' => lang("expresso mini"));
			$this->template->set_var($var);
			$this->process_back_link();
		}

	}
?>
