<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
include_once("class.functions.inc.php");
include_once("class.ldap_functions.inc.php");
include_once("class.exporteml.inc.php");

class imap_functions
{
	var $public_functions = array
	(
		'get_range_msgs'				=> True,
		'get_info_msg'					=> True,
		'get_info_msgs'					=> True,
		'get_folders_list'				=> True,
		'import_msgs'					=> True,
		'report_mail_error'             => True,
		'msgs_to_archive'				=> True
	);

	var $ldap;
	var $mbox;
    var $mboxFolder;
	var $imap_port;
	var $has_cid;
	var $imap_options = '';
	var $functions;
	var $prefs;
	var $foldersLimit;
	var $imap_sentfolder;
	var $rawMessage;
	var $folders;
	var $cache = false;
	var $useCache = false;
	var $expirationCache = false;
    var $msgIds = array();// Usado para guardar o messagesIds
        
	function imap_functions (){
		$this->init();
	}

	function init()
	{
		$this->foldersLimit    	= $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['imap_max_folders'] ?  $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['imap_max_folders'] : 20000; //Limit of folders (mailboxes) user can see
	 	$this->username 	   	= $_SESSION['phpgw_info']['expressomail']['user']['userid'];
		$this->password 	   	= $_SESSION['phpgw_info']['expressomail']['user']['passwd'];
		$this->imap_server	   	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
		$this->imap_port	   	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapPort'];
		$this->imap_delimiter  	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'];
		$this->functions	   	= new functions();
		$this->imap_sentfolder	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']   ? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']   : str_replace("*","", $this->functions->getLang("Sent"));
		$this->has_cid			= false;
		$this->prefs 		   	= $_SESSION['phpgw_info']['user']['preferences']['expressoMail'];
		
		//armazena os caminhos das pastas ( sent, spam, drafts, trash )
		$this->folders['sent']    =  empty($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']) ? 'Sent' : $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']; //Variavel folders armazena o caminho /sent
		$this->folders['spam']    =  empty($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']) ? 'Spam' : $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'];
		$this->folders['drafts']  =  empty($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder']) ? 'Drafts' : $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'];
		$this->folders['trash']   =  empty($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']) ? 'Trash' : $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'];

        if(isset($_SESSION['phpgw_info']['expresso']['expressoMail']['expressoMail_enable_memcache']) && $_SESSION['phpgw_info']['expresso']['expressoMail']['expressoMail_enable_memcache'] === 'true')
            $this->useCache = true;
         
        if(isset($_SESSION['phpgw_info']['expresso']['expressoMail']['expressoMail_time_memcache']) && trim($_SESSION['phpgw_info']['expresso']['expressoMail']['expressoMail_time_memcache']) != '')
            $this->expirationCache = $_SESSION['phpgw_info']['expresso']['expressoMail']['expressoMail_time_memcache'];
                
		if( $_SESSION['phpgw_info']['expressomail']['email_server']['imapTLSEncryption'] == 'yes' )
			$this->imap_options = '/tls/novalidate-cert';
		else
			$this->imap_options = '/notls/novalidate-cert';
	}
	
	function mount_url_folder($folders)
	{
		return implode($this->imap_delimiter,$folders);
	}
	
	// BEGIN of functions.
	function open_mbox( $folder = false, $force_die = true)
	{	
			$newFolder = mb_convert_encoding($folder, 'UTF7-IMAP','UTF-8, ISO-8859-1, UTF7-IMAP');
		
			if($newFolder ===  $this->mboxFolder && is_resource( $this->mbox ))
				return $this->mbox;
			
            $this->mboxFolder =  $newFolder;
            $url = '{'.$this->imap_server.":".$this->imap_port.$this->imap_options.'}'.$this->mboxFolder;
            
            if (is_resource($this->mbox))
                 if ($force_die)
                    imap_reopen($this->mbox, $url ) or die(serialize(array('imap_error' => $this->parse_error(imap_last_error()))));
                 else
                    imap_reopen($this->mbox, $url );
            else
                if($force_die)
                    $this->mbox = imap_open( $url , $this->username, $this->password) or die(serialize(array('imap_error' => $this->parse_error(imap_last_error()))));
                else
                    $this->mbox = imap_open( $url , $this->username, $this->password);

            return $this->mbox;
	 }

	/**
	* Move as pastas que vieram do resultado de um Drag & Drop da arvore de pastas do Expresso Mail
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Gustavo Pereira dos Santos Stabelini	
	* @param      array $params Contem dois indices : um contem o caminho atual da pasta, e o outro contem o caminho futuro da pasta
	* @return     boolean
	* @access     public
	*/
	 
	function move_folder($params){
		//preg_match( '/[a-zA-Z0-9]+$/',$params['folder_to_move'], $new_folder);
		$old_folder = mb_convert_encoding($params['folder_to_move'], 'UTF7-IMAP','UTF-8, ISO-8859-1, UTF7-IMAP');
		$new_folder = explode($this->imap_delimiter, $old_folder );
		$to_folder = mb_convert_encoding($params['folder_to'], 'UTF7-IMAP','UTF-8, ISO-8859-1, UTF7-IMAP');
		$mbox = imap_open('{'.$this->imap_server.":".$this->imap_port.$this->imap_options.'}'.$new_folder[0], $this->username, $this->password);
		$result = true;
		if(!imap_renamemailbox($mbox, '{'.$this->imap_server.":".$this->imap_port.$this->imap_options.'}'.$old_folder, '{'.$this->imap_server.":".$this->imap_port.$this->imap_options.'}'.$to_folder.$this->imap_delimiter.$new_folder[count($new_folder)-1])){
			$result = imap_last_error();
		}

		imap_close($mbox); 
		return $result;
	} 
	
	function parse_error($error, $field = ''){
		// This error is returned from Imap.
		if(strstr($error,'Connection refused')) {
			return str_replace("%1", $this->functions->getLang("Mail"), $this->functions->getLang("Connection failed with %1 Server. Try later."));
		}
		else if(strstr($error,'virus')) {
			return str_replace("%1", $this->functions->getLang("Mail"), $this->functions->getLang("Your message was rejected by antivirus. Perhaps your attachment has been infected."));
		}
		else if(strstr($error,'Failed to add recipient:')) {
			preg_match_all('/:\s([\s\.";@!a-z0-9]+)\s\[SMTP:/', $error, $res);
			return  str_replace("%1", $res['1']['0'], $this->functions->getLang("SMTP Error: The following recipient addresses failed: %1"));
		}
		else if(strstr($error,'Recipient address rejected')) {
			return str_replace("%1", $this->functions->getLang("Mail"), $this->functions->getLang("Invalid recipients in the message").'.');
		}
		else if(strstr($error,'Invalid Mail:')) {
			return  str_replace("%1", $field, $this->functions->getLang("The recipients addresses failed %1"));
		}
		else if(strstr($error,'Message file too big')) {
			return ($this->functions->getLang("Message file too big."));
		}
		// This condition verifies if SESSION is expired.
		else if(!count($_SESSION))
			return "nosession";

		return $error;
	}

	function get_range_msgs3($params){

		$return = $this->get_range_msgs2($params);

		$return['folder'] = mb_convert_encoding( $return['folder'], 'ISO-8859-1', 'UTF-8' );

		return $return;
	}
	
	function getMessagesIds($params){
		$folder = $params['folder'];				
		$sort_box_type = $params['sort_box_type']; 
		$search_box_type = $params['search_box_type'];
		$sort_box_reverse = $params['sort_box_reverse'];
		if( !$this->mbox || !is_resource( $this->mbox ) )
            $this->mbox = $this->open_mbox($folder);
		$sort = array();
		if ($sort_box_type != "SORTFROM" && $search_box_type!= "FLAGGED"){
			$imapsort = imap_sort($this->mbox,constant($sort_box_type),$sort_box_reverse,SE_UID,$search_box_type);
			foreach($imapsort as $iuid){
				$sort[$iuid] = $iuid;
			}
		}
		if(empty($sort) or !is_array($sort)){
			$sort = array();
		}
		return $sort;
	}
	
	function get_range_msgs2($params)
	{ 	
		include_once dirname(__FILE__).'/../../prototype/api/controller.php';
        // Free others requests
        session_write_close();
        $folder = $params['folder'];
        $msg_range_begin = $params['msg_range_begin'];
        $msg_range_end = $params['msg_range_end'];
        $sort_box_type		= isset($params['sort_box_type']) ? $params['sort_box_type'] : '';
        $sort_box_reverse	= isset($params['sort_box_reverse']) ? $params['sort_box_reverse'] : '';
        $search_box_type	= (isset($params['search_box_type']) && $params['search_box_type'] != 'ALL' && $params['search_box_type'] != '' )? $params['search_box_type'] : false;

        if( !$this->mbox || !is_resource( $this->mbox ) )
            $this->mbox = $this->open_mbox($folder);

        $return = array();
        $return['folder'] = $folder;
        //Para enviar o offset entre o timezone definido pelo usuário e GMT
        $return['offsetToGMT'] = $this->functions->CalculateDateOffset();


		if(!$search_box_type || $search_box_type == 'UNSEEN' || $search_box_type == 'SEEN')
		{
            $msgs_info = imap_status($this->mbox,"{".$this->imap_server.":".$this->imap_port.$this->imap_options."}".mb_convert_encoding( $folder, 'UTF7-IMAP', 'UTF-8, ISO-8859-1' ) ,SA_ALL);

            $return['tot_unseen'] = ($search_box_type == 'SEEN') ? 0 : ( isset($msgs_info->unseen) ? $msgs_info->unseen : 0 );

            $sort_array_msg = $this->get_msgs($folder, $sort_box_type, $search_box_type, $sort_box_reverse,$msg_range_begin,$msg_range_end);

            $num_msgs = ($search_box_type=="UNSEEN") ? (isset($msgs_info->unseen)?$msgs_info->unseen:0) : (($search_box_type=="SEEN") ? ((isset($msgs_info->messages)?$msgs_info->messages:0) - (isset($msgs_info->unseen)?$msgs_info->unseen:0)) : (isset($msgs_info->messages)?$msgs_info->messages:0));

            $i = 0;

            if( is_array($sort_array_msg) )
            {
                foreach($sort_array_msg as $msg_number => $value)
                {
                    $sample = false;

                    if( (isset($this->prefs['preview_msg_subject']) && ($this->prefs['preview_msg_subject'] === '1')) ||
                        (isset($this->prefs['preview_msg_tip']    ) && ($this->prefs['preview_msg_tip']     === '1')) )
                        $sample = true;
                                
                        $return[$i++] = $this->get_info_head_msg( $msg_number , $sample );					
                }

                if( isset($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_followupflags_and_labels']) && $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_followupflags_and_labels'] == "1")
                {
                    $filter = array('AND', array('=', 'folderName', $folder), array('IN','messageNumber', $sort_array_msg));
                    $followupflagged = Controller::find(
                        array('concept' => 'followupflagged'),
                        false,
                        array('filter' => $filter, 'criteria' => array('deepness' => '2'))
                    );
                    $labeleds = Controller::find(
                        array('concept' => 'labeled'),
                        false,
                        array('filter' => $filter, 'criteria' => array('deepness' => '2'))
                    );
                    
                    $sort_array_msg_count = count($sort_array_msg);
                    
                    for($i=0; $i<$sort_array_msg_count; ++$i)
                    {
                        if(!isset($return[$i]['msg_number']))
                            continue;

                        $numFlags = count($followupflagged);
                        for($ii=0; $ii<$numFlags; ++$ii)
                        {
                            if($return[$i]['msg_number'] == $followupflagged[$ii]['messageNumber'])
                            {
                                $followupflag = Controller::read( array( 'concept' => 'followupflag', 'id' => $followupflagged[$ii]['followupflagId'] ));
                                $return[$i]['followupflagged'] = $followupflagged[$ii];
                                $return[$i]['followupflagged']['followupflag'] = $followupflag;
                                break;
                            }
                        }
                        $numLabels = count($labeleds);
                        
                        for($ii=0; $ii<$numLabels; ++$ii)
                        {
                            if($return[$i]['msg_number'] == $labeleds[$ii]['messageNumber'])
                            {
                                $labels = Controller::read( array( 'concept' => 'label', 'id' =>  $labeleds[$ii]['labelId']));
                                $labels['name'] = utf8_decode($labels['name']);
                                $return[$i]['labels'][$labeleds[$ii]['labelId']] = $labels;
                            }
                        }
                    }
                }
            }
            $return['num_msgs'] =  $num_msgs;   
		}
        else
        {
			$num_msgs = imap_num_msg($this->mbox);
			$sort_array_msg = $this-> get_msgs($folder, $sort_box_type, $search_box_type, $sort_box_reverse,$msg_range_begin,$num_msgs);

			$return['tot_unseen'] = 0;
			$i = 0;

            if(is_array($sort_array_msg))
            {
                foreach($sort_array_msg as $msg_number => $value)
                {
                    $temp = $this->get_info_head_msg($msg_number);
                    if(!$temp)
                        return false;

                    if($temp['Unseen'] == 'U' || $temp['Recent'] == 'N')
                        $return['tot_unseen']++;

                    if($i <= ($msg_range_end-$msg_range_begin))
                        $return[$i] = $temp;

                    ++$i;
                }
            }

            $return['num_msgs'] = count($sort_array_msg)+($msg_range_begin-1);
        }

        $return['messagesIds'] = $this->msgIds;
        return $return;
    }
	
	
	function getMessages($params) {
		$result = array();

		$exporteml = new ExportEml();
		$unseen_msgs = array();
		
		foreach($params['messages'] as $folder => $messages) {
			foreach($messages as $msg_number) {

				$this->mbox = $this->open_mbox($folder);
			
				if (isset($params['details']) && $params['details'] == 'all') {
					$message = $this->get_info_msg(array('msg_number' => $msg_number, 'msg_folder' =>urlencode($folder)));
				} else {
					$message['headers'] = $this->get_info_head_msg($msg_number, true);
					//$message['attachments'] = $exporteml->get_attachments_in_array(array("num_msg" => $msg_number));
				}

				imap_close($this->mbox);
				$this->mbox = false;

				if($msg_info['Unseen'] == "U" || $msg_info['Recent'] == "N") {
					array_push($unseen_msgs,$msg_number);
				}
				
				$result[$folder][] = $message;
			}
			if($unseen_msgs){
				$msgs_list = implode(",",$unseen_msgs);
				$array_msgs = array('folder' => $folder, "msgs_to_set" => $msgs_list, "flag" => "unseen");
				$this->set_messages_flag($array_msgs);
			}
		}
		
		return $result;
	}	

        /**
        *  Decodifica uma string no formato mime RFC2047
        *
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Cristiano Corrêa Schmidt
        * @param      string $string string no formato mime RFC2047
        * @return     string
        * @access     public
        */
        static function decodeMimeString( $string )
        {
          $string =  preg_replace('/\?\=(\s)*\=\?/', '?==?', $string);
          return preg_replace_callback( '/\=\?([^\?]*)\?([qb])\?([^\?]*)\?=/i' ,array( 'self' , 'decodeMimeStringCallback'), $string);
        }
      
        /**
        *  Decodifica os tokens encontrados na função decodeMimeString
        *
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Cristiano Corrêa Schmidt
        * @param      array $mathes array retornado pelo preg_replace_callback da função decodeMimeString
        * @return     string
        * @access     public
        */
        static function decodeMimeStringCallback( $mathes )
        {
           $str = (strtolower($mathes[2]) == 'q') ?  quoted_printable_decode(str_replace('_','=20',$mathes[3])) : base64_decode( $mathes[3]) ;
           return ( strtoupper($mathes[1]) != 'ISO-8859-1') ? mb_convert_encoding($str, 'ISO-8859-1', strtoupper($mathes[1])) : $str;
        }
        
        /**
        *  Formata um mailObject para um array com name e email
        *
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Cristiano Corrêa Schmidt
        * @return     bool
        * @access     public
        */
        static function formatMailObject( $obj )
        {
            $return = array();
            $return['email'] = self::decodeMimeString($obj->mailbox) . ((isset( $obj->host) && ($obj->host != 'unspecified-domain' || $obj->host != '.SYNTAX-ERROR.') )? '@'. $obj->host : '');
            $return['name'] = ( isset( $obj->personal ) && trim($obj->personal) !== '' ) ? self::decodeMimeString($obj->personal) :  $return['email'];
            return $return;
        }
        
        /**
        *   Retorna informações do cabeçario da mensagem e um preview caso appendSample = true
        *   Utiliza memCache caso esta preferencia esteja ativada.
        *
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Cristiano Corrêa Schmidt
        * @return     bool
        * @access     public
        */
	function get_info_head_msg( $msg_number , $appendSample = false )
	{         
        $return = false;
        $cached = false;
        if( $this->useCache === true )
        {
            if( $this->cache === false )
            {
                $this->cache = ServiceLocator::getService( 'memCache' ); //Serviço Cache
                $this->cache->connect( $_SESSION['phpgw_info']['expressomail']['server']['server_memcache'] , $_SESSION['phpgw_info']['expressomail']['server']['port_server_memcache'] );
            }
            
            if( $return = $this->cache->get( 'infoHead://'.$this->username.'://'.$this->mboxFolder.'://'.$msg_number ))
               $cached = true;   
		}

        $header = imap_headerinfo($this->mbox,imap_msgno( $this->mbox, $msg_number )); //Resgata o objeto Header da mensagem , nescessario mesmo com o cache pois as flags podem ser atualizadas por outro cliente de email
        $return['Recent'] = $header->Recent;
        $return['Unseen'] = $header->Unseen;
        $return['Deleted'] = $header->Deleted;
        $return['Flagged'] = $header->Flagged;

        if($header->Answered =='A' && $header->Draft == 'X')
        {
            $return['Forwarded'] = 'F';
        }
        else 
        {
            $return['Answered']	= $header->Answered;
            $return['Draft']	= $header->Draft;
        }    
        
        if( $cached === true ) //Caso a mensagem ja tenha vindo do cache da o return
        {
            if($appendSample !== false && !isset($return['msg_sample'])) //verifica o msg_sample caso seja alterada a preferencia e não esteja em cache carregar
            {
                $return['msg_sample'] = $this->get_msg_sample($msg_number);
                $this->cache->set( 'infoHead://'.$this->username.'://'.$this->mboxFolder.'://'.$msg_number , $return , $this->expirationCache);
            }
           
            return $return;
        }

        $importance = array();
        $mimeHeader = imap_fetchheader( $this->mbox, $msg_number , FT_UID ); //Resgata o Mime Header da mensagem
		
        $mimeBody = imap_body( $this->mbox, $msg_number  , FT_UID|FT_PEEK  ); //Resgata o Mime Body da mensagem sem marcar como lida
        $offsetToGMT =  $this->functions->CalculateDateOffset();
        $return['ContentType'] = $this->getMessageType( $msg_number , $mimeHeader , $mimeBody ); 
        $return['Importance'] = ( preg_match('/importance *: *(.*)\r/i', $mimeHeader , $importance) === 0 ) ? 'Normal' : $importance[1];
        $return['msg_number'] = $msg_number;
        $return['udate'] = $header->udate;
        $return['offsetToGMT'] = $offsetToGMT;
        $return['timestamp'] = $header->udate + $return['offsetToGMT'];
        $return['smalldate'] = (date('d/m/Y') == gmdate( 'd/m/Y', $return['timestamp'] )) ?  gmdate("H:i", $return['timestamp'] ) : gmdate("d/m/Y", $return['timestamp'] );
        $return['Size'] = $header->Size;
        $return['from'] =  (isset( $header->from[0] )) ? self::formatMailObject( $header->from[0] ) : array( 'name' => '' , 'email' => '');
        $return['subject']  =  ( isset($header->subject) && trim($header->subject) !== '' ) ?  self::decodeMimeString($header->subject) : $this->functions->getLang('(no subject)   ');
        $return['attachment'] = ( preg_match('/((Content-Disposition:(.)*([\r\n\s]*filename))|(Content-Type:(.)*([\r\n\s]*name))|(BEGIN:VCALENDAR))/i', $mimeBody) ) ? '1' : '0'; //Verifica se a anexos na mensagem
        $return['reply_toaddress'] = isset($header->reply_toaddress) ? self::decodeMimeString($header->reply_toaddress) : '';
        $return['flag'] = $header->Unseen.
            $header->Recent.
            ($header->Flagged == 'F' || !( preg_match('/importance *: *(.*)\r/i', $mimeHeader , $importance) === 0 )? 'F' : '').
            $header->Draft.
            $header->Answered.
            $header->Deleted.
            ( $return['attachment'] === '1' ? 'T': '' );

        if (!empty($header->to))
        {
				foreach ($header->to as $i => $v)
				{
					$return['to'][$i] = self::formatMailObject( $v );
				}
		} 
		else if (!empty($header->cc))
		{
			foreach ($header->cc as $i => $v)
			{
				$return['to'][$i] = self::formatMailObject( $v );
			}
		}
		else if (!empty($header->bcc))
		{
			foreach ($header->bcc as $i => $v)
			{
				$return['to'][$i] = self::formatMailObject( $v );
			}
		}
		else
		{
            $return['to'] = array( 'name' => '' , 'email' => '');
        }
			
		if (!empty($return['to']))
		{
			foreach ($return['to'] as $i => $v)
			{
				if($v['name'] == 'undisclosed-recipients@' || $v['name'] == '@')
					$return['to'][$i] = $return['from'];
			}
		}	

        if($appendSample !== false)
		{ 
			$return['msg_sample'] = $this->get_msg_sample($msg_number);
		}
        
        if( $this->useCache === true )
        {
            $this->cache->set( 'infoHead://'.$this->username.'://'.$this->mboxFolder.'://'.$msg_number , $return , $this->expirationCache);
        }

        return $return;

	}

	/**
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @param      string $string String a ser decodificada
	* @return     string
	* @todo       Verificar a possibilidade de se utilizar a função iconv_mime_decode, que é capaz de identificar a codificação por si só, mas que pode ser interpretada de forma diversa dependendo da implementação do sistema
	* @todo       Executar testes suficientes para validar a funçao iconv_mime_decode em substituição à este método decode_string
	*/
	function decode_string($string)
	{
        $return = '';
        $decoded = '';
		if ((strpos(strtolower($string), '=?iso-8859-1') !== false) || (strpos(strtolower($string), '=?windows-1252') !== false))
		{
			$tmp = imap_mime_header_decode($string);
			foreach ($tmp as $tmp1)
            {
				$return .= $this->htmlspecialchars_encode($tmp1->text);
            }

            return str_replace("\t", "", $return);
		}
		else if (strpos(strtolower($string), '=?utf-8') !== false)
		{
			$elements = imap_mime_header_decode($string);

            $elements_count = count($elements);
  			for($i = 0;$i < $elements_count;++$i)
  			{
   				$charset = strtolower($elements[$i]->charset);
   				$text = $elements[$i]->text;
   				if(!strcasecmp($charset, "utf-8") || !strcasecmp($charset, "utf-7"))
       				$decoded .= $this->functions->utf8_to_ncr($text);
  				else
  				{
					if( strcasecmp($charset,"default") )
						$decoded .= $this->htmlspecialchars_encode(iconv($charset, "iso-8859-1", $text));
					else
						$decoded .= $this->htmlspecialchars_encode($text);
  				}
  			}

              return str_replace("\t", "", $decoded);
		}
		else if(strpos(strtolower($string), '=?us-ascii') !== false) 
 	   { 
			$retun = ''; 
			$tmp = imap_mime_header_decode($string); 
			foreach ($tmp as $tmp1) 
				$return .= $this->htmlspecialchars_encode(quoted_printable_decode($tmp1->text)); 
 	        
			return str_replace("\t", "", $return); 
 	 
 	    }
        else if( strpos( $string , '=?' ) !== false ) 
            return $this->htmlspecialchars_encode(iconv_mime_decode( $string ));
        

			return $this->htmlspecialchars_encode($string);
	}
	
	/**
	* Função que importa arquivos .eml exportados pelo expresso para a caixa do usuário. Testado apenas
	* com .emls gerados pelo expresso, e o arquivo pode ser um zip contendo vários emls ou um .eml.
	*/
	function import_msgs($params) {		
		if(!$this->mbox)
			$this->mbox = $this->open_mbox();

 		if( preg_match('/local_/',$params["folder"]) ){
			
			// PLEASE, BE CAREFULL!!! YOU SHOULD USE EMAIL CONFIGURATION VALUES (EMAILADMIN MODULE)
			//$tmp_box = mb_convert_encoding('INBOX'.$this->folders['trash'].$this->imap_delimiter.'tmpMoveToLocal', "UTF7-IMAP", "UTF-8");
			$tmp_box = mb_convert_encoding($this->mount_url_folder(array("INBOX",$this->folders['trash'],"tmpMoveToLocal")), "UTF7-IMAP", "UTF-8");
			
			if ( ! imap_createmailbox( $this->mbox,"{".$this -> imap_server."}$tmp_box" ) )
				return $this->functions->getLang( 'Import to Local : fail...' );
			imap_reopen($this->mbox, "{".$this->imap_server.":".$this->imap_port.$this->imap_options."}".$tmp_box);
			$params["folder"] = $tmp_box;
		}
		
		$errors = array();
		$invalid_format = false;
		$filename = $params['FILES'][0]['name'];
		$params["folder"] = mb_convert_encoding($params["folder"], "UTF7-IMAP","ISO-8859-1, UTF-8");
		$quota = imap_get_quotaroot($this->mbox, $params["folder"]);
		
		if((($quota['limit'] - $quota['usage'])*1024) <= $params['FILES'][0]['size']){
			return array( 'error' => $this->functions->getLang("fail in import:").
							" ".$this->functions->getLang("Over quota"));
		}
		
		if(substr($filename,strlen($filename)-4)==".zip") {
			$zip = zip_open($params['FILES'][0]['tmp_name']);
			if ($zip) {
				while ($zip_entry = zip_read($zip)) {

					if (zip_entry_open($zip, $zip_entry, "r")) {
						$email = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

						/*Caso mensagens vierem com quebras de linha diferentes do esperado, como por exemplo,
						mensagens exportadas pelo MailArchiver, é preciso corrigir.*/
						$email = str_replace("\r\n", "\n", $email);
						$email = str_replace("\n", "\r\n", $email);

						$status = @imap_append($this->mbox,
								"{".$this->imap_server.":".$this->imap_port.$this->imap_options."}".$params["folder"],
									$email
									);
						if(!$status)
							array_push($errors,zip_entry_name($zip_entry));
						zip_entry_close($zip_entry);
					}
				}
				zip_close($zip);
			}
			if (isset( $tmp_box ) && ! sizeof( $errors )){
				$mc = imap_check($this->mbox);
				$result = imap_fetch_overview( $this -> mbox, "1:{$mc->Nmsgs}", 0 );
				$ids = array( );
				foreach ($result as $overview)
					$ids[ ] = $overview -> uid;
				return implode( ',', $ids );
			}
		
		}else if(substr($filename,strlen($filename)-4)==".eml") {
			$email = implode("",file($params['FILES'][0]['tmp_name']));

			/*Caso mensagens vierem com quebras de linha diferentes do esperado, como por exemplo,
			mensagens exportadas pelo MailArchiver, é preciso corrigir.*/
			$email = str_replace("\r\n", "\n", $email);
			$email = str_replace("\n", "\r\n", $email);

			$status = imap_append($this->mbox,"{".$this->imap_server.":".$this->imap_port.$this->imap_options."}".$params["folder"],$email);
				
			if(!$status)
				return "Error importing";
			
			if ( isset( $tmp_box ) && ! sizeof( $errors ) ) {
				$mc = imap_check($this->mbox);

				$result = imap_fetch_overview( $this -> mbox, "1:{$mc->Nmsgs}", 0 );

				$ids = array( );
				foreach ($result as $overview)
					$ids[ ] = $overview -> uid;

				return implode( ',', $ids );
			}
		}
		else{
			if ( isset( $tmp_box ) )
				imap_deletemailbox( $this->mbox,"{".$this -> imap_server."}$tmp_box" );

			return array("error" => $this->functions->getLang("wrong file format"));
			$invalid_format = true;
		}

		if(!$invalid_format) {
			if(count($errors)>0) {
				$message = $this->functions->getLang("fail in import:")."\n";
				foreach($errors as $arquivo) {
					$message.=$arquivo."\n";
				}
				return array("error" => $message);
			}
			else
				return $this->functions->getLang("The import was executed successfully.");
		}
	}
        /*
		Remove os anexos de uma mensagem. A estratégia para isso é criar uma mensagem nova sem os anexos, mantendo apenas
		a primeira parte do e-mail, que é o texto, sem anexos.
		O método considera que o email é multpart.
	*/
	function remove_attachments($params) {
		include_once("class.message_components.inc.php");
		if(!$this->mbox || !is_resource($this->mbox))
			$this->mbox = $this->open_mbox($params["folder"]);
		$return["status"] = true;
		$header = "";

		$headertemp = explode("\n",imap_fetchheader($this->mbox, imap_msgno($this->mbox, $params["msg_num"])));
		foreach($headertemp as $head) {//Se eu colocar todo o header do email dá pau no append, então procuro apenas o que interessa.
			$head1 = explode(":",$head);
			if ( (strtoupper($head1[0]) == "TO") ||
					(strtoupper($head1[0]) == "FROM") ||
					(strtoupper($head1[0]) == "SUBJECT") ||
					(strtoupper($head1[0]) == "DATE") )
				$header .= $head."\r\n";
		}

		$msg = new message_components($this->mbox);
		$msg->fetch_structure($params["msg_num"]);/* O fetchbody tava trazendo o email com problemas na acentuação.
							     Então uso essa classe para verificar a codificação e o charset,
							     para que o método decodeBody do expresso possa trazer tudo certinho*/

		$all_body_type = strtolower($msg->file_type[$params["msg_num"]][0]);
		$all_body_encoding = $msg->encoding[$params["msg_num"]][0];
		$all_body_charset = $msg->charset[$params["msg_num"]][0];
		
		if($all_body_type=='multipart/alternative') {
			if(strtolower($msg->file_type[$params["msg_num"]][2]=='text/html') &&
					$msg->pid[$params["msg_num"]][2] == '1.2') {
				$body_part_to_show = '1.2';
				$all_body_type = strtolower($msg->file_type[$params["msg_num"]][2]);
				$all_body_encoding = $msg->encoding[$params["msg_num"]][2];
				$all_body_charset = $msg->charset[$params["msg_num"]][2];
			}
			else {
				$body_part_to_show = '1.1';
				$all_body_type = strtolower($msg->file_type[$params["msg_num"]][1]);
				$all_body_encoding = $msg->encoding[$params["msg_num"]][1];
				$all_body_charset = $msg->charset[$params["msg_num"]][1];
			}
		}
		else
			$body_part_to_show = '1';

		$status = imap_append($this->mbox,
				"{".$this->imap_server.":".$this->imap_port.$this->imap_options."}".$params["folder"],
					$header.
					"Content-Type: ".$all_body_type."; charset = \"".$all_body_charset."\"".
					"\r\n".
					"Content-Transfer-Encoding: ".$all_body_encoding.
					"\r\n".
					"\r\n".
					str_replace("\n","\r\n",preg_replace("/<img[^>]+\>/i", " ", $this->decodeBody(
							imap_fetchbody($this->mbox,imap_msgno($this->mbox, $params["msg_num"]),$body_part_to_show),
							$all_body_encoding, $all_body_charset
							))
					), "\\Seen"); //Append do novo email, só com header e conteúdo sem anexos. //Remove imagens do corpo, pois estas estão na lista de anexo e serão removidas.

		if(!$status)
		{
			$return["status"] = false;
			$return["msg"] = lang("error appending mail on delete attachments");
		}
		else
		{
			$status = imap_status($this->mbox, "{".$this->imap_server.":".$this->imap_port."}".$params['folder'], SA_UIDNEXT);
			$return['msg_no'] = $status->uidnext - 1;
			imap_delete($this->mbox, imap_msgno($this->mbox, $params["msg_num"]));
			imap_expunge($this->mbox);
		}

		return $return;

	}
	
	function msgs_to_archive($params) {
		
		$folder = $params['folder'];
		$all_ids = $this-> get_msgs($folder, 'SORTARRIVAL', false, 0,-1,-1);

		$messages_not_to_copy = explode(",",$params['mails']);
		$ids = array();
		
		$cont = 0;
		
		foreach($all_ids as $each_id=>$value) {
			if(!in_array($each_id,$messages_not_to_copy)) {
				array_push($ids,$each_id);
				++$cont;
			}
			if($cont>=100)
				break;
		}

		if (empty($ids))
			return array();

		$params = array("folder"=>$folder,"msgs_number"=>implode(",",$ids));
		
		
		return $this->get_info_msgs($params);
		
		
	}

/**
	 *
	 * @return
	 * @param $params Object
	 */
	function get_info_msgs($params) {
		include_once("class.exporteml.inc.php");
		
		if(array_key_exists('messages', $params)){
			$sel_msgs = explode(",", $params['messages']);
			@reset($sel_msgs);
			$sorted_msgs = array();
			foreach($sel_msgs as $idx => $sel_msg) {
				$sel_msg = explode(";", $sel_msg);
				if(array_key_exists($sel_msg[0], $sorted_msgs)){
					$sorted_msgs[$sel_msg[0]] .= ",".$sel_msg[1];
				}
				else {
					$sorted_msgs[$sel_msg[0]] = $sel_msg[1];
				}
			}
			unset($sorted_msgs['']);	
		
			$return = array();
			$array_names_keys = array_keys($sorted_msgs);

            $sorted_msgs_count = count($sorted_msgs);
			for($i = 0; $i < $sorted_msgs_count; ++$i){
			
				$new_params = array();
				$attach_params = array();
			
				$new_params["msg_folder"]= $array_names_keys[$i];
				$attach_params["folder"] = $params["folder"];
				$msgs = explode(",",$sorted_msgs[$array_names_keys[$i]]);
				$exporteml = new ExportEml();
				$unseen_msgs = array();
				foreach($msgs as $msg_number) {
					$new_params["msg_number"] = $msg_number;
					//ini_set("display_errors","1");
					$msg_info = $this->get_info_msg($new_params);

					$this->mbox = $this->open_mbox($array_names_keys[$i]); //Não sei porque, mas se não abrir de novo a caixa dá erro.
					$msg_info['header'] = $this->get_info_head_msg($msg_number);

					$attach_params["num_msg"] = $msg_number;
					$msg_info['array_attach'] = $exporteml->get_attachments_in_array($attach_params);
					imap_close($this->mbox);
					$this->mbox=false;
					array_push($return,serialize($msg_info));
				
					if($msg_info['Unseen'] == "U" || $msg_info['Recent'] == "N"){
							array_push($unseen_msgs,$msg_number);
					}
				}
			}
			if($unseen_msgs){
				$msgs_list = implode(",",$unseen_msgs);
				$array_msgs = array('folder' => $new_params["msg_folder"], "msgs_to_set" => $msgs_list, "flag" => "unseen");
				$this->set_messages_flag($array_msgs);
			}
			return $return;
		}else{
		$return = array();
		$new_params = array();
		$attach_params = array();
		$new_params["msg_folder"]=$params["folder"];
		$attach_params["folder"] = $params["folder"];
		$msgs = explode(",",$params["msgs_number"]);
		$exporteml = new ExportEml();
		$unseen_msgs = array();
		foreach($msgs as $msg_number) {
			$new_params["msg_number"] = $msg_number;
			//ini_set("display_errors","1");
			$msg_info = $this->get_info_msg($new_params);

			$this->mbox = $this->open_mbox($params['folder']); //Não sei porque, mas se não abrir de novo a caixa dá erro.
			$msg_info['header'] = $this->get_info_head_msg($msg_number);

			$attach_params["num_msg"] = $msg_number;
			$msg_info['array_attach'] = $exporteml->get_attachments_in_array($attach_params);
			imap_close($this->mbox);
			$this->mbox=false;
			array_push($return,serialize($msg_info));

			if($msg_info['Unseen'] == "U" || $msg_info['Recent'] == "N"){
					array_push($unseen_msgs,$msg_number);
			}
		}
		if($unseen_msgs){
			$msgs_list = implode(",",$unseen_msgs);
			$array_msgs = array('folder' => $new_params["msg_folder"], "msgs_to_set" => $msgs_list, "flag" => "unseen");
			$this->set_messages_flag($array_msgs);
		}					
					
		return $return;
	}
}

	/**
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $msg_number numero da mensagem
	*/
	function getRawHeader($msg_number)
    {
		return imap_fetchheader($this->mbox, $msg_number, FT_UID);
	}
	
	/**
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $msg_number numero da mensagem
	*/
	function getRawBody($msg_number)
    {
		return  imap_body($this->mbox, $msg_number, FT_UID);	
	}

	
	/**
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $msg mensagem
	*/
	function builderMsgHeader($msg)
    {

  
            $fromMail =  str_replace('<','', str_replace('>','',$msg->headers['from']));
            $tosMails =  str_replace('<','', str_replace('>','',$msg->headers['to']));

            $tos = explode(',',$tosMails);
            $to = '';
            foreach ($tos as $value)
            {
                $to .= '<a href="mailto:'.str_replace(' ','',$value).'">'.$value.'</a>, ';
            }

            $header = '
                <table style="margin: 2px; border: 1px solid black; background: none repeat scroll 0% 0% rgb(234, 234, 234);">
                <tbody>
                <tr><td><b>'.$this->functions->getLang('Subject').':</b></td><td>'.$msg->headers['subject'].'</td></tr>
                <tr><td><b>'.$this->functions->getLang('From').':</b></td><td><a href="mailto:'.$fromMail.'">'.$fromMail.'</a></td></tr>
                <tr><td><b>'.$this->functions->getLang('Date').':</b></td><td>'.$msg->headers['date'].'</td></tr>
                <tr><td><b>'.$this->functions->getLang('To').':</b></td><td>'.$to.'</td></tr>
                </tbody>
                </table>
                <br />'
            ;

          return $header;
    }
	
	/**
        * Constroe o corpo da msg direto na variavel de conteudo
        * @param Mail_mimeDecode $structure
        * @param <type> $content Ponteiro para Variavel de conteudo da msg
	*/
        function builderMsgBody($structure , &$content , $printHeader = false)
        {
            if(strtolower($structure->ctype_primary) == 'multipart' && strtolower($structure->ctype_secondary) == 'alternative')
            {
                $numParts = count($structure->parts) - 1;

                for($i = $numParts; $i >= 0; $i--)
                {
                    $part = $structure->parts[$i];

                    switch (strtolower($part->ctype_primary))
                    {
                       case 'text':
                           $disposition = isset($part->disposition) ? strtolower($part->disposition) : '';
                           if($disposition != 'attachment')
                           {
                                if(strtolower($part->ctype_secondary) == 'html')
                                {
                                   if($printHeader)
                                        $content .= $this->builderMsgHeader($part);

                                   $content .= $this->decodeMailPart($part->body,$part->ctype_parameters['charset']);
                                   $i = -1;
                                }

                                if(strtolower($part->ctype_secondary) == 'plain' )
                                {
                                  if($printHeader)
                                      $content .= $this->builderMsgHeader($part);

                                   $content .= '<pre>'. htmlentities($this->decodeMailPart($part->body,$part->ctype_parameters['charset'],false)).'</pre>';
                                   $i = -1;
                                }
                                if(strtolower($part->ctype_secondary) == 'calendar')
                                    $content.= $this->builderMsgCalendar($this->decodeMailPart($part->body, $part->ctype_parameters['charset']));
                           }

                            break;

                       case 'multipart':

                           if(strtolower($part->ctype_secondary) == 'alternative' ) //Unico formato multipart suportado, escapa os outros
                           {
                               if($printHeader)
                                   $content .= $this->builderMsgHeader($part);

                               $this->builderMsgBody($part,$content);

                               $i = -1;
                           }

                           break;

                       case 'message':

                            if(!is_array($part->parts))
                            {
                                $content .= "<hr align='left' width='95%' style='border:1px solid #DCDCDC'>";
                                $content .= '<pre>'. htmlentities($this->decodeMailPart($part->body, $structure->ctype_parameters['charset'],false)).'</pre>';
                                $content .= "<hr align='left' width='95%' style='border:1px solid #DCDCDC'>";
                            }
                            else
                                $this->builderMsgBody($part,$content,true);

                            $i = -1;
                            break;
                    }
                }
            }
            else
            {
                foreach ($structure->parts  as $index => $part)
                {
                   switch (strtolower($part->ctype_primary))
                   {
                       case 'text':
                           $disposition = '';
                           if(isset($part->disposition))
                           $disposition = isset($part->disposition) ? strtolower($part->disposition) : '';
                           if($disposition != 'attachment')
                           {
                                if(strtolower($part->ctype_secondary) == 'html')
                                {
                                   if($printHeader)
                                        $content .= $this->builderMsgHeader($part);

                                   $content .= $this->decodeMailPart($part->body,$part->ctype_parameters['charset']);
                                }

                                if(strtolower($part->ctype_secondary) == 'plain')
                                {
                                  if($printHeader)
                                      $content .= $this->builderMsgHeader($part);

                                   $content .= '<pre>'. htmlentities($this->decodeMailPart($part->body,$part->ctype_parameters['charset'],false)).'</pre>';
                                }
                                if(strtolower($part->ctype_secondary) == 'calendar')
                                    $content .= $this->builderMsgCalendar($part->body);
                        
                           }
                            break;
                       case 'multipart':

                            if($printHeader)
                               $content .= $this->builderMsgHeader($part);

                            $this->builderMsgBody($part,$content);

                            break;
                       case 'message':
                        if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['nested_messages_are_shown'] != '1')
                        {
                            if(!is_array($part->parts))
                            {
                                $content .= "<hr align='left' width='95%' style='border:1px solid #DCDCDC'>";
                                $content .= '<pre>'.  htmlentities($this->decodeMailPart($part->body, $structure->ctype_parameters['charset'],false)).'</pre>';
                                $content .= "<hr align='left' width='95%' style='border:1px solid #DCDCDC'>";
                            }
                            else
                                $this->builderMsgBody($part,$content,true);
                        break;
                 }
               }
            }
        }
        }
	
	
	/**
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $msg_number numero da mensagem
	*/
	function get_msg_sample($msg_number)
	{
                $content = '';
		$return = array(); 

		include_once('class.message_components.inc.php');
		$msg = new message_components($this->mbox);
		$msg->fetch_structure($msg_number);  

		if(!isset($msg->structure[$msg_number]->parts))
		{
                    $content = '';
                    if (strtolower($msg->structure[$msg_number]->subtype) == "plain" || strtolower($msg->structure[$msg_number]->subtype) == "html")
                        $content = $this->decodeBody(imap_body($this->mbox, $msg_number, FT_UID|FT_PEEK), $msg->encoding[$msg_number][0], $msg->charset[$msg_number][0]);
		}
		else
		{
                    foreach($msg->pid[$msg_number] as $values => $msg_part)
                    {
                        $file_type = strtolower($msg->file_type[$msg_number][$values]);
                        if($file_type == "text/plain" || $file_type == "text/html") {
                                $content = $this->decodeBody(imap_fetchbody($this->mbox, $msg_number, $msg_part, FT_UID|FT_PEEK), $msg->encoding[$msg_number][$values], $msg->charset[$msg_number][$values]);
                                break;
                        }
                    }
		}
   
		$tags_replace = array("<br>","<br/>","<br />");
		$content = str_replace($tags_replace," ", nl2br($content));
		$content = $this->html2txt($content);	
		$content != "" ? $return['body'] = " - " . $content: $return['body'] = "";
                $return['body'] = base64_encode(mb_convert_encoding(substr($return['body'], 0, 305),'UTF-8' , 'UTF-8,ISO-8859-1'));
		return $return;
	}
        
    function html2txt($document){
        $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                       '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
                       '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
                       '@<![\s\S]*?--[ \t\n\r]*>@si'         // Strip multi-line comments including CDATA                    
        );
        $text = preg_replace($search, '', $document);
        return html_entity_decode($text);
    } 

    function ope_msg_part($params)
    {
        $return = array();
        require_once dirname(__FILE__).'/class.attachment.inc.php';
	
        $atObj = new attachment();
        $atObj->setStructureFromMail($params['msg_folder'],$params['msg_number']);
        $mbox_stream = $this->open_mbox($params['save_folder']);
        $return['append'] = imap_append($mbox_stream, "{".$this->imap_server.":".$this->imap_port."}".$params['save_folder'], $atObj->getAttachment($params['msg_part']), "\\Seen \\Draft");
        $status = imap_status($mbox_stream, "{".$this->imap_server.":".$this->imap_port."}".$params['save_folder'], SA_UIDNEXT);
	
        $return['msg_folder']  = $params['save_folder'];
        $return['msg_number'] = $status->uidnext - 1;        

        return $return;

    }
	
	function get_info_msg($params)
	{
		if(isset($params['alarm'])){
			$alarm = $params['alarm'];
		}else{
			$alarm = false;
		}
		
		$return = array();
		$msg_number = $params['msg_number'];
		$msg_folder = isset($params['decoded']) ? $params['msg_folder'] : urldecode($params['msg_folder']);
		$params['folder'] = $msg_folder;
		$msg_ids = array_values($this->getMessagesIds($params));
		
		if($msg_ids[0] == $msg_number){
			$return['next_message'] = $msg_ids[1];
			$return['prev_message'] = null;
		}else if($msg_ids[count($msg_ids)-1] == $msg_number){
			$return['prev_message'] = $msg_ids[count($msg_ids)-2];
			$return['next_message'] = null;
		}else{
			for($i = 0; $i < count($msg_ids); ++$i){
				if($msg_ids[$i] == $msg_number){
					$return['prev_message'] = $msg_ids[$i-1];
					$return['next_message'] = $msg_ids[$i+1];
					break;
				}
			}
		}

		if(preg_match('/(.+)(_[a-zA-Z0-9]+)/',$msg_number,$matches)) { //Verifies if it comes from a tab diferent of the main one. 
			$msg_number = $matches[1];
			$plus_id = $matches[2];
		}
		else {
			$plus_id = '';
		}

		$this->mbox = $this->open_mbox($msg_folder);
		
		$header = $this->get_header($msg_number);
		if (!$header) {
			$return['status_get_msg_info'] = "false";
			return $return;
		}

		$header_ = imap_fetchheader($this->mbox, $msg_number, FT_UID);
		$return_get_body = $this->get_body_msg($msg_number, mb_convert_encoding( $msg_folder, "ISO-8859-1", "UTF-8" ));
		$body = $return_get_body['body'];
		if($return_get_body['body']=='isCripted'){
			$exporteml = new ExportEml();
			$return['source']=$exporteml->export_msg_data($msg_number,$msg_folder);
			$return['body'] 		= "";
			$return['attachments'] 	=  "";
			$return['thumbs'] 		=  "";
			$return['signature']	=  "";
			//return $return;
		}else{
	    $return['body'] 		= $body;
	    $return['attachments'] 	= $return_get_body['attachments'];
	    $return['thumbs'] 		= $return_get_body['thumbs'];
	    //$return['signature']	= $return_get_body['signature'];
		}

		$pattern = '/^[ \t]*Disposition-Notification-To:.*/mi';
		if (preg_match($pattern, $header_, $fields))
			$return['DispositionNotificationTo'] = base64_encode(trim(str_ireplace('Disposition-Notification-To:', '', $fields[0]))); 

		$return['Recent']	= $header->Recent;
		$return['Unseen']	= $header->Unseen;
		$return['Deleted']	= $header->Deleted;
		$return['Flagged']	= $header->Flagged;

		if($header->Answered =='A' && $header->Draft == 'X'){
			$return['Forwarded'] = 'F';
		}

		else {
			$return['Answered']	= $header->Answered;
			$return['Draft']	= $header->Draft;
		}

		$return['msg_number'] = $msg_number.$plus_id;
		$return['msg_folder'] = mb_convert_encoding( $msg_folder, "ISO-8859-1", "UTF-8" );
		//isset($params['decoded']) ? mb_convert_encoding( $msg_folder, "ISO-8859-1", "UTF-8" ) : $msg_folder;

		
		
		$msgTimesTamp = $header->udate + $this->functions->CalculateDateOffset(); //Aplica offset do usuario
		$date_msg = gmdate("d/m/Y",$msgTimesTamp);

//      Removido codigo pois a o método send_nofication precisa da data completa.
//		if (date("d/m/Y") == $date_msg)
//			$return['udate'] = gmdate("H:i",$header->udate);
//		else

//      Passa o a data completa para mensagem.		
		$return['udate'] = $header->udate;

		$return['msg_day'] = $date_msg;
		$return['msg_hour'] = gmdate("H:i",$msgTimesTamp);

		if (date("d/m/Y") == $date_msg) //no dia
		{
			$return['fulldate'] = gmdate("d/m/Y H:i",$msgTimesTamp);
			$return['smalldate'] = gmdate("H:i",$msgTimesTamp);
			

				$timestamp_now = strtotime("now");
			//	removido offset nao esta sendo parametrizado
			//	$timestamp_now = strtotime("now") + $offset;
			
			
			$timestamp_msg_time = $msgTimesTamp;
			// $timestamp_now is GMT and $timestamp_msg_time is MailDate TZ. 
			// The variable $timestamp_diff is calculated without MailDate TZ.
			$pdate = date_parse($header->MailDate);
			$timestamp_diff = $timestamp_now - $timestamp_msg_time  + ($pdate['zone']*(-60));

			if (gmdate("H",$timestamp_diff) > 0)
			{
				$return['fulldate'] .= " (" . gmdate("H:i", $timestamp_diff) . ' ' . $this->functions->getLang('hours ago') . ')';
			}
			else
			{
				if (gmdate("i",$timestamp_diff) == 0){
					$return['fulldate'] .= ' ('. $this->functions->getLang('now').')';
				}
				elseif (gmdate("i",$timestamp_diff) == 1){
					$return['fulldate'] .= ' (1 '. $this->functions->getLang('minute ago').')';
				}
				else{
					$return['fulldate'] .= " (" . gmdate("i",$timestamp_diff) .' '. $this->functions->getLang('minutes ago') . ')';
				}
			}
		}
		else{
			$return['fulldate'] = gmdate("d/m/Y H:i",$msgTimesTamp);
			$return['smalldate'] = gmdate("d/m/Y",$msgTimesTamp);
		}

		$from = $header->from;
		$return['from'] = array();
		$return['from']['name'] = isset($sender[0]->personal) ? $this->decode_string($from[0]->personal) : '';
		$return['from']['email'] = $this->decode_string($from[0]->mailbox . "@" . $from[0]->host);
		if ($return['from']['name'])
		{
			if (substr($return['from']['name'], 0, 1) == '"')
				$return['from']['full'] = $return['from']['name'] . ' ' . '&lt;' . $return['from']['email'] . '&gt;';
			else
				$return['from']['full'] = '"' . $return['from']['name'] . '" ' . '&lt;' . $return['from']['email'] . '&gt;';
		}
		else
			$return['from']['full'] = $return['from']['email'];

		// Sender attribute
		$sender = $header->sender;
		$return['sender'] = array();
		$return['sender']['name'] = isset($sender[0]->personal) ? $this->decode_string($sender[0]->personal): '';
		$return['sender']['email'] = $this->decode_string($sender[0]->mailbox . "@" . $sender[0]->host);

		if ($return['sender']['name'])
		{
			if (substr($return['sender']['name'], 0, 1) == '"')
				$return['sender']['full'] = $return['sender']['name'] . ' ' . '&lt;' . $return['sender']['email'] . '&gt;';
			else
				$return['sender']['full'] = '"' . $return['sender']['name'] . '" ' . '&lt;' . $return['sender']['email'] . '&gt;';
		}
		else
			$return['sender']['full'] = $return['sender']['email'];

		if($return['from']['full'] == $return['sender']['full'])
			$return['sender'] = null;
		$to = $header->to;
		$return['toaddress2'] = "";
		if (!empty($to))
		{
			foreach ($to as $tmp)
			{
				if (!empty($tmp->personal))
				{
					$personal_tmp = $this->formatMailObject($tmp);
					$return['toaddress2'] .= '"' . $personal_tmp['name'] . '"';
					$return['toaddress2'] .= " ";
					$return['toaddress2'] .= "&lt;";
					$return['toaddress2'] .= $personal_tmp['email'];
					$return['toaddress2'] .= "&gt;";
					$return['toaddress2'] .= ", ";
				}
				else
				{
					if (isset($tmp->host) && $tmp->host != 'unspecified-domain')
						$return['toaddress2'] .= $tmp->mailbox . "@" . $tmp->host;
					else
						$return['toaddress2'] .= $tmp->mailbox;
					$return['toaddress2'] .= ", ";
				}
			}
			$return['toaddress2'] = $this->del_last_two_caracters($return['toaddress2']);
		}
		else
		{
			$return['toaddress2'] = "";
		}	
		if(isset($header->cc))
		$cc = $header->cc;
		$return['cc'] = "";
		if (!empty($cc))
		{
			foreach ($cc as $tmp_cc)
			{
				if (!empty($tmp_cc->personal))
				{
					$personal_tmp_cc = $this->formatMailObject($tmp_cc);
					$return['cc'] .= '"' . $personal_tmp_cc['name']. '"';
					$return['cc'] .= " ";
					$return['cc'] .= "&lt;";
					$return['cc'] .= $personal_tmp_cc['email'];
					$return['cc'] .= "&gt;";
					$return['cc'] .= ", ";
				}
				else
				{
					if ($tmp_cc->host != 'unspecified-domain')
					$return['cc'] .= $tmp_cc->mailbox . "@" . $tmp_cc->host;
					else
						$return['cc'] .= $tmp_cc->mailbox;
					//$return['cc'] .= $tmp_cc->mailbox . "@" . $tmp_cc->host;
					$return['cc'] .= ", ";
				}
			}
			$return['cc'] = $this->del_last_two_caracters($return['cc']);
		}
		else
		{
			$return['cc'] = "";
		}

		##
		# @AUTHOR Rodrigo Souza dos Santos
		# @DATE 2008/09/12
		# @BRIEF Adding the BCC field.
		##
        if(isset($header->bcc)){        
		$bcc = $header->bcc;
		}
		$return['bcc'] = "";
		if (!empty($bcc))
		{
			foreach ($bcc as $tmp_bcc)
			{
				if (!empty($tmp_bcc->personal))
				{
					$personal_tmp_bcc = $this->formatMailObject($tmp_bcc);
					$return['bcc'] .= '"' . $personal_tmp_bcc['name'] . '"';
					$return['bcc'] .= " ";
					$return['bcc'] .= "&lt;";
					$return['bcc'] .= $personal_tmp_bcc['email'];
					$return['bcc'] .= "&gt;";
					$return['bcc'] .= ", ";
				}
				else
				{
					if ($tmp_bcc->host != 'unspecified-domain')
					$return['bcc'] .= $tmp_bcc->mailbox . "@" . $tmp_bcc->host;
					else
						$return['bcc'] .= $tmp_bcc->mailbox;
					//$return['bcc'] .= $tmp_bcc->mailbox . "@" . $tmp_bcc->host;
					$return['bcc'] .= ", ";
				}
			}
			$return['bcc'] = $this->del_last_two_caracters($return['bcc']);
		}
		else
		{
			$return['bcc'] = "";
		}

		$reply_to = $header->reply_to;
		$return['reply_to'] = "";
		
		if (is_object($reply_to[0]))
		{
			if ($return['from']['email'] != ($reply_to[0]->mailbox."@".$reply_to[0]->host))
			{
				foreach ($reply_to as $tmp_reply_to)
				{
					if (!empty($tmp_reply_to->personal))
					{
						$personal_tmp_reply_to = $this->formatMailObject($tmp_reply_to);
						$return['reply_to'] .= '"' . $personal_tmp_reply_to['name'] . '"';
						$return['reply_to'] .= " ";
						$return['reply_to'] .= "&lt;";
						$return['reply_to'] .= $personal_tmp_reply_to['email'];
						$return['reply_to'] .= "&gt;";
						$return['reply_to'] .= ", ";
					}
					else
					{
						if (isset($tmp_reply_to->host) && $tmp_reply_to->host != 'unspecified-domain')
							$return['reply_to'] .= $tmp_reply_to->mailbox . "@" . $tmp_reply_to->host;
						else
							$return['reply_to'] .= $tmp_reply_to->mailbox;
						$return['reply_to'] .= ", ";
					}
				}
				$return['reply_to'] = $this->del_last_two_caracters($return['reply_to']);
			}
		}
		else
		{
			$return['reply_to'] = "";
		}

		$return['subject'] = ( isset($header->subject) && trim($header->subject) !== '' ) ?  self::decodeMimeString($header->subject) : $this->functions->getLang('(no subject)   ');

		$return['Size'] = $header->Size;
		$return['reply_toaddress'] = $header->reply_toaddress;

		//All this is to help in local messages
                $return['timestamp'] = $header->udate;
		$return['login'] = $_SESSION['phpgw_info']['expressomail']['user']['account_id'];//$GLOBALS['phpgw_info']['user']['account_id'];
		$return['reply_toaddress'] = $header->reply_toaddress;
  		
		if(($return['from']['email'] ==  '@unspecified-domain' || $return['sender']['email'] == null) && $return['msg_folder'] == 'INBOX/Drafts'){
			$return['from']['email'] = "Rascunho";
		}

		if(strpos($return['toaddress2'], 'undisclosed-recipients') !== false){
			$return['toaddress2'] = $this->functions->getLang('without destination');
		}  
		$return['alarm'] = $alarm;

		return $return;
	}

	
	/* 
	* Converte textos utf8 para o padrão html.
	 * Modificado por Cristiano Corrêa Schmidt
 	 * @link http://php.net/manual/en/function.utf8-decode.php 
	* @author     luka8088 <luka8088@gmail.com> 
	*/	
 	static function utf8_to_html ($data) 
	{
 	    return preg_replace("/([\\xC0-\\xF7]{1,1}[\\x80-\\xBF]+)/e", 'self::_utf8_to_html("\\1")', $data); 
 	} 

 	static function _utf8_to_html ($data) 
	{ 
 	    $ret = 0; 
		foreach((str_split(strrev(chr((ord($data{0}) % 252 % 248 % 240 % 224 % 192) + 128) . substr($data, 1)))) as $k => $v) 
			$ret += (ord($v) % 128) * pow(64, $k); 
 		    return html_entity_decode("&#$ret;" , ENT_QUOTES); 
	} 
 	//------------------------------------------------------------------------------// 


		/**
         * Decodifica uma part da mensagem para iso-8859-1
         * @param <type> $part parte do email
         * @param <type> $encode codificação da parte
         * @return <type> string decodificada
		*/
        function decodeMailPart($part, $encode, $html = true)
	{
            switch (strtolower($encode))
            {
                case 'iso-8859-1':
                    return $part;
                    break;
                case 'utf-8':
                    if ($html) return  self::utf8_to_html($part);
                    else       return  utf8_decode ($part);
                    break;
                default:
                    return mb_convert_encoding($part, 'iso-8859-1', $encode ? $encode : null);
                    break;
            }
	}

	
	function get_body_msg($msg_number, $msg_folder)
	{
            /*
             * Requires of librarys
             */
            require_once dirname(__FILE__).'/../../prototype/library/mime/mimePart.php';
            require_once dirname(__FILE__).'/../../prototype/library/mime/mimeDecode.php';
            require_once dirname(__FILE__).'/class.attachment.inc.php';
            //include_once("class.message_components.inc.php");
            //--------------------------------------------------------------------//

            $return = array();

//            $msg = new message_components($this->mbox);
//            $msg->fetch_structure($msg_number);

            $content = '';

            /* 
            * Chamada original  $this->getRawHeader($msg_number)."\r\n".$this->getRawBody($msg_number); 
            * Inserido replace para corrigir um bug que acontece raramente em mensagens vindas do outlook com muitos destinatarios 
            */ 
            $rawMessageData = str_replace("\r\n\t", '', $this->getRawHeader($msg_number))."\r\n".$this->getRawBody($msg_number); 

            $decoder = new Mail_mimeDecode($rawMessageData);

            $params['include_bodies'] = true;
            $params['decode_bodies']  = true;
            $params['decode_headers'] = true;
			if(array_key_exists('nested_messages_are_shown', $_SESSION['phpgw_info']['user']['preferences']['expressoMail']) && ($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['nested_messages_are_shown'] == '1'))
				$params['rfc_822bodies']  = true;
            $structure = $decoder->decode($params);

            /*
             * Inicia Gerenciador de Anexos
             */
            $attachmentManager = new attachment();
            $attachmentManager->setStructure($structure);
            //----------------------------------------------//

            /*
             * Monta informações dos anexos para o cabecalhos
             */
            $attachments = $attachmentManager->getAttachmentsInfo();
            $return['attachments'] = $attachments;
            //----------------------------------------------//

            /*
             * Monta informações das imagens
             */
            $images = $attachmentManager->getEmbeddedImagesInfo();
            //----------------------------------------------//

		if(!$this->has_cid)
		{
                    $return['thumbs']    = $this->get_thumbs($images,$msg_number,$msg_folder);
               // $return['signature'] = $this->get_signature($msg,$msg_number,$msg_folder);
		}

            switch (strtolower($structure->ctype_primary))
		{
			case 'text': 
 		                        if(strtolower($structure->ctype_secondary) == 'x-pkcs7-mime') 
 		                        {
				$return['body']='isCripted';
				return $return;
			}
                        $attachment = array();

                        $msg_subtype = strtolower($structure->ctype_secondary);
                    if(isset($structure->disposition))
                        $disposition = strtolower($structure->disposition);
                    else
                        $disposition = '';

                        if(($msg_subtype == "html" || $msg_subtype == 'plain') && ($disposition != 'attachment'))
			{
                                if(strtolower($msg_subtype) == 'plain')
					{ 
                        if(isset($structure->ctype_parameters['charset']))
                                        $content = $this->decodeMailPart($structure->body, $structure->ctype_parameters['charset'],false);
                        else
                            $content = $this->decodeMailPart($structure->body, null,false);
						$content = str_replace(array('<', '>'), array('#$<$# ', ')#$>$#'), $content); 
						$content = htmlentities( $content ); 
                                        $this->replace_links($content);
						$content = str_replace(array('#$&lt;$#', ')#$&gt;$#'), array('&lt;', '&gt;'), $content); 
						$content = '<pre>' . $content . '</pre>'; 
                                                $return[ 'body' ] = $content;
						return $return; 
					} 
								$content = $this->decodeMailPart($structure->body, $structure->ctype_parameters['charset']);
				}
                    if(strtolower($structure->ctype_secondary) == 'calendar')
                           $content .= $this->builderMsgCalendar($structure->body);

                    break;

               case 'multipart':
                    $this->builderMsgBody($structure , $content);

                    break;

               case 'message':
                    if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['nested_messages_are_shown'] != 1)
                    {
                    if(!is_array($structure->parts))
				{
                        $content .= "<hr align='left' width='95%' style='border:1px solid #DCDCDC'>";
                        $content .= '<pre>'.htmlentities($this->decodeMailPart($structure->body, $structure->ctype_parameters['charset'],false)).'</pre>';
                        $content .= "<hr align='left' width='95%' style='border:1px solid #DCDCDC'>";
						}
                                            else
                        $this->builderMsgBody($structure , $content,true);
                    }
                    break;

            case 'application':
                if(strtolower($structure->ctype_secondary) == 'x-pkcs7-mime')
                {   
                  //  $return['body']='isCripted';
                  // return $return;
				  
				  //TODO: Descartar código após atualização do módulo de segurança da SERPRO
					$rawMessageData2 = $this->extractSignedContents($rawMessageData); 
					if($rawMessageData2 === false){ 
						$return['body']='isCripted'; 
						return $return; 
					} 
					$decoder2 = new Mail_mimeDecode($rawMessageData2); 
 		            $structure2 = $decoder2->decode($params); 
 		            $this-> builderMsgBody($structure2 , $content);  
 		 
 		            $attachmentManager->setStructure($structure2); 
 		            /* 
 		            * Monta informações dos anexos para o cabecarios 
 		            */ 
 		            $attachments = $attachmentManager->getAttachmentsInfo(); 
 	                $return['attachments'] = $attachments; 

 		            //----------------------------------------------// 
 		 
 		            /* 
	                * Monta informações das imagens 
 		            */ 
 		            $images = $attachmentManager->getEmbeddedImagesInfo(); 
 		            //----------------------------------------------// 
 		 
 		            if(!$this->has_cid){ 
 		                $return['thumbs']    = $this->get_thumbs($images,$msg_number,$msg_folder); 
 		                $return['signature'] = $this->get_signature($msg,$msg_number,$msg_folder); 
 		            }
                }
			///////////////////////////////////////////////////////////////////////////////////////////
               default:
                    if(count($attachments) > 0)
                       $content .= '';
                    break;
								} 

		$params = array('folder' => $msg_folder, "msgs_to_set" => $msg_number, "flag" => "seen");
		$this->set_messages_flag($params);
		$content = $this->process_embedded_images($images,$msg_number,$content, $msg_folder);
		$content = $this->replace_special_characters($content);
        $content = $this->replace_email_mailto($content);
              $this->replace_links($content);
		$return['body'] = &$content;
                
		return $return;
	}

	function replace_email_mailto($content)
	{
		$pattern = '/(^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$)/im';
		$replacement = '<a href="mailto:$1">$1</a>';
		return preg_replace($pattern, $replacement, $content);
	}
	
	//TODO: Descartar código após atualização do módulo de segurança da SERPRO
	function extractSignedContents( $data ) 
    { 
		$pipes_desc = array( 
			0 => array('pipe', 'r'), 
			1 => array('pipe', 'w') 
	    ); 
	 
	    $fp = proc_open( 'openssl smime -verify -noverify -nochain', $pipes_desc, $pipes); 
	    if (!is_resource($fp)) { 
			return false; 
	    } 
	 
	    $output = ''; 
	 
 		/* $pipes[0] => writeable handle connected to child stdin 
 		$pipes[1] => readable handle connected to child stdout */ 
 	    fwrite($pipes[0], $data); 
 	    fclose($pipes[0]); 
 	 
 	    while (!feof($pipes[1])) { 
			$output .= fgets($pipes[1], 1024); 
 	    } 
 	    fclose($pipes[1]); 
 	    proc_close($fp); 
 	 
 	    return $output; 
 	}
    ///////////////////////////////////////////////////////////////////////////////////////
    
        function builderMsgCalendar($calendar)
        {
            $icalService = ServiceLocator::getService('ical');

            $codificao =  mb_detect_encoding($calendar.'x', 'UTF-8, ISO-8859-1');
            if($codificao == 'UTF-8')
                $calendar = utf8_decode($calendar);

            if($icalService->setIcal($calendar))
            {
                $content = '';

                switch ($icalService->getMethod()) {

                    case 'REPLY':
                        break;

                    case 'CANCEL':

                          $ical = $icalService->getComponent('vevent');
                          $content.= '<b>'.$this->functions->getLang('Event Calendar').'</b><br /><br />';
                          $content.= '<span style="font-size: 12" >';
                          $content.= '<b><span style="color:red">'.$this->functions->getLang('Your event has been canceled').'</span></b>';
   
                          if($ical['description']['value'])
                              $content.= ' <br /> <br /> '.str_replace('\n','<br />',nl2br($ical['description']['value']));

                          $content.= '<br /><b>* '.$this->functions->getLang('To remove the event from your calendar to import the iCal file attached').'.</b>';
                          $content.= '</span><br /><br />';
                        break;

                    case 'REQUEST':

                        $ical = $icalService->getComponent('vevent');
                        $userTz = in_array( $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['timezone'], timezone_identifiers_list()) ?
                            $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['timezone'] :
                            'America/Sao_Paulo';

                        $timezoneUser = new DateTimeZone($userTz);
                        $icalTz = 'UTC';

                        if( isset($ical['dtstart']['params']) && isset($ical['dtstart']['params']['TZID']) && in_array( $ical['dtstart']['params']['TZID'], timezone_identifiers_list()) )
                            $icalTz = $ical['dtstart']['params']['TZID'];

                        $timezoneIcal = new DateTimeZone($icalTz);
                        $dateStart = new DateTime();
                        $dateEnd = new DateTime();
                        $dateStart->setTimeZone( $timezoneIcal );
                        $dateEnd->setTimeZone( $timezoneIcal );

                        $dateStart->setDate( $ical['dtstart']['value']['year'] ,  $ical['dtstart']['value']['month'] ,  $ical['dtstart']['value']['day']  );
                        $dateStart->setTime( $ical['dtstart']['value']['hour'] ,  $ical['dtstart']['value']['min'] );

                        $dateEnd->setDate( $ical['dtend']['value']['year'] ,  $ical['dtend']['value']['month'] ,  $ical['dtend']['value']['day']  );
                        $dateEnd->setTime( $ical['dtend']['value']['hour'] ,  $ical['dtend']['value']['min'] );

                        if(!isset($ical['dtstart']['params']['TZID']))
                        {
                            $dateEnd->setTimeZone( $timezoneUser );
                            $dateStart->setTimeZone( $timezoneUser );
                        }

                        $content .= '<b>' . $this->functions->getLang('Event Calendar') . '</b><br />' .
                            ' <br /> <b>' . $this->functions->getLang('Title') . ': </b>' . $ical['summary']['value'] .
                            ' <br /> <b>' . $this->functions->getLang('Location') . ': </b>' . $ical['location']['value'] .
                            ' <br /> <b>' . $this->functions->getLang('Details') . ': </b>' . str_ireplace('\n', '<br />', nl2br($ical['description']['value']));
                        $content .= ' <br /> <b>' . $this->functions->getLang('Start') . ':  </b>' . $dateStart->format('d/m/Y - H:i') ;
                        $content .= ' <br /> <b>' . $this->functions->getLang('End') . ': </b>' . $dateEnd->format('d/m/Y - H:i');

                        if ($ical['organizer']['params']['CN'])
                            $content .= ' <br /> <b>' . $this->functions->getLang('Organizer') . ': </b>' . $ical['organizer']['params']['CN'] . ' -  <a href="MAILTO:' . $ical['organizer']['value'] . '">' . $ical['organizer']['value'] . '</a></li>';
                        else
                            $content .= ' <br /> <b>' . $this->functions->getLang('Organizer') . ': </b> <a href="MAILTO:' . $ical['organizer']['value'] . '">' . $ical['organizer']['value'] . '</a>';

                        if ($ical['attendee']) {
                            $att = ' <br /> <b>' . $this->functions->getLang('Participants') . ': </b>';
                            $att .= '<ul> ';
                            foreach ($ical['attendee'] as $attendee) {
                                if ($attendee['params']['CN'])
                                    $att .= '<li>' . $attendee['params']['CN'] . ' -  <a href="MAILTO:' . $attendee['value'] . '">' . $attendee['value'] . '</a></li>';
                                else
                                    $att .= '<li><a href="MAILTO:' . $attendee['value'] . '">' . $attendee['value'] . '</a></li>';
                            }
                            $att .= '</ul> <br />';
                        }
                        $content .= $att;

                        break;
                    default:
                        break;
                }
      
            }
            return $content;
        }
        
	function htmlfilter($body)
	{
		require_once('htmlfilter.inc');

		$tag_list = Array(
				false,
				'blink',
				'object',
				'meta',
				'html',
				'link',
				'frame',
				'iframe',
				'layer',
				'ilayer',
				'plaintext'
		);

		/**
		* A very exclusive set:
		*/
		// $tag_list = Array(true, "b", "a", "i", "img", "strong", "em", "p");


		$rm_tags_with_content = Array(
				'script',
				'style',
				'applet',
				'embed',
				'head',
				'frameset',
				'xml',
				'xmp'
		);

		$self_closing_tags =  Array(
				'img',
				'br',
				'hr',
				'input'
		);

		$force_tag_closing = true;

		$rm_attnames = Array(
    			'/.*/' =>
				Array(
					'/target/i',
					//'/^on.*/i', -> onClick, dos compromissos da agenda.
					'/^dynsrc/i',
					'/^datasrc/i',
					'/^data.*/i',
					'/^lowsrc/i'
				)
		);

		/**
		 * Yeah-yeah, so this looks horrible. Check out htmlfilter.inc for
		 * some idea of what's going on here. :)
		 */

		$bad_attvals = Array(
    		'/.*/' =>
	    	Array(
	    	      '/.*/' =>
		    	      Array(
	    	    	        Array(
            	    	          '/^([\'\"])\s*\S+\s*script\s*:*(.*)([\'\"])/si',
                		          //'/^([\'\"])\s*https*\s*:(.*)([\'\"])/si', -> doclinks notes
                        		  '/^([\'\"])\s*mocha\s*:*(.*)([\'\"])/si',
	                        	  '/^([\'\"])\s*about\s*:(.*)([\'\"])/si'
	    	                      ),
    	    	            Array(
        		   	              '\\1oddjob:\\2\\1',
                		          //'\\1uucp:\\2\\1', -> doclinks notes
                    		      '\\1amaretto:\\2\\1',
                        		  '\\1round:\\2\\1'
                          		)
		                    ),

		          '/^style/i' =>
    		              Array(
        		                Array(
            		                  '/expression/i',
                		              '/behaviou*r/i',
                    		          '/binding/i',
                        		      '/include-source/i',
                            		  '/url\s*\(\s*([\'\"]*)\s*https*:.*([\'\"]*)\s*\)/si',
		                              '/url\s*\(\s*([\'\"]*)\s*\S+\s*script:.*([\'\"]*)\s*\)/si'
    		                         ),
        		                Array(
            		                  'idiocy',
                		              'idiocy',
                    		          'idiocy',
                        		      'idiocy',
                            		  'url(\\1http://securityfocus.com/\\1)',
	                            	  'url(\\1http://securityfocus.com/\\1)'
	    	                         )
    	    	                )
        	    	  )
		    );

		$add_attr_to_tag = Array(
				'/^a$/i' => Array('target' => '"_new"')
		);


		$trusted_body = sanitize($body,
				$tag_list,
				$rm_tags_with_content,
				$self_closing_tags,
				$force_tag_closing,
				$rm_attnames,
				$bad_attvals,
				$add_attr_to_tag
		);

	    return $trusted_body;
	}

	function decodeBody($body, $encoding, $charset=null)
	{

		if ($encoding == 'quoted-printable')
		{
			$body = quoted_printable_decode($body);

			}
    	else if ($encoding == 'base64')
    	{
        	$body = base64_decode($body);
    	}
		// All other encodings are returned raw.
		if (strtolower($charset) == "utf-8")
			return utf8_decode($body);
    	else
			return $body;
	}

				
	/**
	* @license   http://www.gnu.org/copyleft/gpl.html GPL
	* @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $images
	* @param     $msgno
	* @param     $body
	* @param     $msg_folder
	*/			
	function process_embedded_images($images, $msgno, $body, $msg_folder)
	{

            foreach ($images as $image)
		{
                $image['cid'] = preg_replace('/</i', '', $image['cid']);
                $image['cid'] = preg_replace('/>/i', '', $image['cid']);
				
                $body = str_replace("src=\"cid:".$image['cid']."\"", " src=\"./inc/get_archive.php?msgFolder=$msg_folder&msgNumber=$msgno&indexPart=".$image['pid']."\" ", $body);
                $body = str_replace("src='cid:".$image['cid']."'", " src=\"./inc/get_archive.php?msgFolder=$msg_folder&msgNumber=$msgno&indexPart=".$image['pid']."\"", $body);
                $body = str_replace("src=cid:".$image['cid'], " src=\"./inc/get_archive.php?msgFolder=$msg_folder&msgNumber=$msgno&indexPart=".$image['pid']."\"", $body); 
			}
		return $body;
	}
	
    /**
     * Exibe style inline de mensagens vindas do MSO
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Eduardo Alves Pereira
     * @access     public
     * @param      $matches
     * @return     Retorna aspas duplas em ASCII
     */
    function mso_style($matches)
    {
        $matches[2] = str_replace('"', '&quot;', $matches[2]);
        return $matches[1] . $matches[2] . $matches[3];
    }

	function replace_special_characters($body) 
        {
        //Correção para exibir style inline do MSO
        if(preg_match('~Mso~i', $body))
            $body = preg_replace_callback('~(style=\")(.*?)(\">)~i', array('self', 'mso_style'), $body);



            if(trim($body) === '') return;
            
            $body = str_ireplace('POSITION: ABSOLUTE;','', $body);
            $body = str_ireplace('<o:p>&nbsp;</o:p>','<br />', $body);//Qubra de linha do MSO
            $body = preg_replace('/<(meta|base|link|html|\/html)[^>]*>/i', '', $body);


			// Malicious Code Remove
            $dirtyCodePattern = "/(<([\w]+[\w0-9]*)(.*)on(mouse(move|over|down|up)|load|blur|change|error|click|dblclick|focus|key(down|up|press)|select)([\n\ ]*)=([\n\ ]*)[\"'][^>\"']*[\"']([^>]*)>)(.*)(<\/\\2>)?/misU";
            preg_match_all($dirtyCodePattern, $body, $rest, PREG_PATTERN_ORDER);
            foreach ($rest[0] as $i => $val) {
                if (!(preg_match("/javascript:window\.open\(\"([^'\"]*)\/index\.php\?menuaction=calendar\.uicalendar\.set_action\&cal_id=([^;'\"]+);?['\"]/i", $rest[1][$i]) && strtoupper($rest[4][$i]) == "CLICK" )) //Calendar events
                    $body = str_replace($rest[1][$i], "<" . $rest[2][$i] . $rest[3][$i] . $rest[7][$i] . ">", $body);
            }
            
            require_once(dirname(__FILE__).'/../../prototype/library/CssToInlineStyles/css_to_inline_styles.php');
            $cssToInlineStyles = new CSSToInlineStyles($body);
            $cssToInlineStyles->setUseInlineStylesBlock(true);
            $cssToInlineStyles->setCleanup(TRUE);
            $body = $cssToInlineStyles->convert(); //Converte as tag style em inline styles

            ///--------------------------------// 
            // tags to be removed doe to security reasons
            $tag_list = Array(
                'blink', 'object', 'frame', 'iframe',
                'layer', 'ilayer', 'plaintext', 'script',
                'applet', 'embed', 'frameset', 'xml', 'xmp','style','head'
            );

            foreach ($tag_list as $index => $tag) 
                $body = @mb_eregi_replace("<$tag\\b[^>]*>(.*?)</$tag>", '', $body);

            /*
            * Remove deslocamento a esquerda colocado pelo Outlook.
            * Este delocamento faz com que algumas palavras fiquem escondidas atras da barra lateral do expresso. 
            */
            $body = mb_ereg_replace("(<p[^>]*)(text-indent:[^>;]*-[^>;]*;)([^>]*>)", "\\1\\3", $body);
            $body = mb_ereg_replace("(<p[^>]*)(margin-right:[^>;]*-[^>;]*;)([^>]*>)", "\\1\\3", $body);
            $body = mb_ereg_replace("(<p[^>]*)(margin-left:[^>;]*-[^>;]*;)([^>]*>)", "\\1\\3", $body);
            //--------------------------------------------------------------------------------------------// 	
            $body = str_ireplace('position:absolute;', '', $body);

            //Remoção de tags <span></span> para correção de erro no firefox
            //Comentado pois estes replaces geram erros no html da msg, não se pode garantir que o os </span></span> sejam realmente os fechamentos dos <span><span>.
            //Caso realmente haja a nescessidade de remover estes spans deve ser repensado a forma de como faze-lo.
            //		$body = mb_eregi_replace("<span><span>","",$body);
            //		$body = mb_eregi_replace("</span></span>","",$body);
            //Correção para compatibilização com Outlook, ao visualizar a mensagem
            $body = mb_ereg_replace('<!--\[', '<!-- [', $body);
            $body = mb_ereg_replace('&lt;!\[endif\]--&gt;', '<![endif]-->', $body);
            $body  = preg_replace("/<p[^\/>]*>([\s]?)*<\/p[^>]*>/", '', $body); //Remove paragrafos vazios (evita duplo espaçamento em emails do MSO)
           
            return  $body ;
    }
	
	function replace_links_callback($matches)  
	{
        if ($matches[1] == "background-image:url(") {
            return $matches[0];
        }
		
        if($matches[4]) 
            $pref = $matches[4]; 
        else 
            $pref = $matches[4] = 'http';  
			
		$url = isset($matches[6]) ? $matches[5].$matches[6] : $matches[5];

 	    return '<a href="'.$pref.'://'.$url.'" target="_blank">'.$matches[0].'</a>'; 
 	} 


	/**
	* @license   http://www.gnu.org/copyleft/gpl.html GPL
	* @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $body corpo da mensagem
	*/
	function replace_links(&$body)
	{ 
		// Trata urls do tipo aaaa.bbb.empresa  
		// Usadas na intranet. 

		$pattern = '/(background-image\:url\()?(?<=[\s|(<br>)|\n|\r|;])(((http|https|ftp|ftps)?:\/\/((?:[\w]\.?)+(?::[\d]+)?[:\/.\-~&=?%;@#,+\w]*))|((?:www?\.)(?:\w\.?)*(?::\d+)?[\:\/\w.\-~&=?%;@+]*))/i';
       
		$body = preg_replace_callback($pattern,array( &$this, 'replace_links_callback'), $body);

	}

	function get_signature($msg, $msg_number, $msg_folder)
	{
            include_once(dirname( __FILE__ ) ."/../../security/classes/CertificadoB.php");
            include_once("class.db_functions.inc.php");
            foreach ($msg->file_type[$msg_number] as $index => $file_type)
	    {
                $sign = array();
                $temp = $this->get_info_head_msg($msg_number);
                if($temp['ContentType'] =='normal') return $sign;
                $file_type = strtolower($file_type);
                if(strtolower($msg->encoding[$msg_number][$index]) == 'base64')
		{
                    if ($temp['ContentType'] == 'signature')
                    {
                        if(!$this->mbox || !is_resource($this->mbox))
                        $this->mbox = $this->open_mbox($msg_folder);

                        $header = @imap_headerinfo($this->mbox, imap_msgno($this->mbox, $msg_number), 80, 255);

                        $imap_msg	 	= @imap_fetchheader($this->mbox, $msg_number, FT_UID);
                        $imap_msg		.= @imap_body($this->mbox, $msg_number, FT_UID);

                        $certificado = new certificadoB();
                        $validade = $certificado->verificar($imap_msg);
                                        $sign[] = $certificado->msg_sem_assinatura;
                        if ($certificado->apresentado)
			{
                            $from = $header->from;
                            foreach ($from as $id => $object)
                            {
				$fromname = $object->personal;
				$fromaddress = $object->mailbox . "@" . $object->host;
                            }
                            foreach ($certificado->erros_ssl as $item)
                            {
                                $sign[] = $item . "#@#";
                            }

                            if (count($certificado->erros_ssl) < 1)
                            {
                                $check_msg = 'Message untouched';
                                if(strtoupper($fromaddress) == strtoupper($certificado->dados['EMAIL']))
                                {
                                    $check_msg .= ' and authentic###';
                                }
                                else
                                {
                                    $check_msg .= ' with signer different from sender#@#';
                                }
                                $sign[] = $check_msg;
                            }
                                                
                            $sign[] = 'Message signed by: ###' . $certificado->dados['NOME'];
                            $sign[] = 'Certificate email: ###' . $certificado->dados['EMAIL'];
                            $sign[] = 'Mail from: ###' . $fromaddress;
                            $sign[] = 'Certificate Authority: ###' . $certificado->dados['EMISSOR'];
                            $sign[] = 'Validity of certificate: ###' . gmdate('r',openssl_to_timestamp($certificado->dados['FIM_VALIDADE']));
                            $sign[] = 'Message date: ###' . $header->Date;

                            $cert = openssl_x509_parse($certificado->cert_assinante);

                            $sign_alert = array();
                            $sign_alert[] = 'Certificate Owner###:\n';
                            $sign_alert[] = 'Common Name (CN)###  ' . $cert['subject']['CN'] .  '\n';
                            $X = substr($certificado->dados['NASCIMENTO'] ,0,2) . '-' . substr($certificado->dados['NASCIMENTO'] ,2,2) . '-'  . substr($certificado->dados['NASCIMENTO'] ,4,4);
                            $sign_alert[]= 'Organization (O)###  ' . $cert['subject']['O'] .  '\n';
                            $sign_alert[]= 'Organizational Unit (OU)### ' . $cert['subject']['OU'][0] .  '\n';
                            //$sign_alert[] = 'Serial Number### ' . $cert['serialNumber'] . '\n';
                            $sign_alert[] = 'Personal Data###:' . '\n';
                            $sign_alert[] = 'Birthday### ' . $X .  '\n';
                            $sign_alert[]= 'Fiscal Id### ' . $certificado->dados['CPF'] .  '\n';
                            $sign_alert[]= 'Identification### ' . $certificado->dados['RG'] .  '\n\n';
                            $sign_alert[]= 'Certificate Issuer###:\n';
                            $sign_alert[]= 'Common Name (CN)###  ' . $cert['issuer']['CN'] . '\n';
                            $sign_alert[]= 'Organization (O)###  ' . $cert['issuer']['O'] .  '\n';
                            $sign_alert[]= 'Organizational Unit (OU)### ' . $cert['issuer']['OU'][0] .  '\n\n';
                            $sign_alert[]= 'Validity###:\n';
                            $H = data_hora($cert['validFrom']);
                            $X = substr($H,6,2) . '-' . substr($H,4,2) . '-'  . substr($H,0,4);
                            $sign_alert[]= 'Valid From### ' . $X .  '\n';
                            $H = data_hora($cert['validTo']);
                            $X = substr($H,6,2) . '-' . substr($H,4,2) . '-'  . substr($H,0,4);
                            $sign_alert[]= 'Valid Until### ' . $X;
                            $sign[] = $sign_alert;

                            $this->db = new db_functions();
                            
                            // TODO: testar se existe um certificado no banco e verificar qual ï¿½ o mais atual.
                            if(!$certificado->dados['EXPIRADO'] && !$certificado->dados['REVOGADO'] && count($certificado->erros_ssl) < 1)
                                $this->db->insert_certificate(strtolower($certificado->dados['EMAIL']), $certificado->cert_assinante, $certificado->dados['SERIALNUMBER'], $certificado->dados['AUTHORITYKEYIDENTIFIER']);
			}
                        else
                        {
                            $sign[] = "<span style=color:red>" . $this->functions->getLang('Invalid signature') . "</span>";
                            foreach($certificado->erros_ssl as $item)
                                $sign[] = "<span style=color:red>" . $this->functions->getLang($item) . "</span>";
                        }
                    }
		}
            }
            return $sign;
	}

	
	/**
	* @license   http://www.gnu.org/copyleft/gpl.html GPL
	* @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $images
	* @param     $msg_number
	* @param     $msg_folder
	*/
	function get_thumbs($images, $msg_number, $msg_folder)
	{

		if (!count($images)) return '';
               
		foreach ($images as $key => $value) {                    
			$images[$key]['width']  = 160; 
			$images[$key]['height'] = 120; 
			$images[$key]['url']    = "inc/get_archive.php?msgFolder=".$msg_folder."&msgNumber=".$msg_number."&indexPart=".$value['pid']."&image=true"; 
    		}

		return json_encode($images); 
	}

	/*function delete_msg($params)
	{
		$folder = $params['folder'];
		$msgs_to_delete = explode(",",$params['msgs_to_delete']);

		$mbox_stream = $this->open_mbox($folder);

		foreach ($msgs_to_delete as $msg_number){
			imap_delete($mbox_stream, $msg_number, FT_UID);
		}
		imap_close($mbox_stream, CL_EXPUNGE);
		return $params['msgs_to_delete'];
	}*/

	// Novo
	function delete_msgs($params)
	{
		$folder = $params['folder'];
		$folder =  mb_convert_encoding($folder, "UTF7-IMAP","ISO-8859-1");
		$msgs_number = explode(",",$params['msgs_number']);

		if(array_key_exists('border_ID' ,$params))
		$border_ID = $params['border_ID'];
		else
			$border_ID = '';
		$return = array();

		if (array_key_exists('get_previous_msg' , $params) &&  $params['get_previous_msg']){
			$return['previous_msg'] = $this->get_info_previous_msg($params);
			// Fix problem in unserialize function JS.
			$return['previous_msg']['body'] = str_replace(array('{','}'), array('&#123;','&#125;'), $return['previous_msg']['body']);
		}

		//$mbox_stream = $this->open_mbox($folder);
	 	$mbox_stream = @imap_open("{".$this->imap_server.":".$this->imap_port.$this->imap_options."}".$folder, $this->username, $this->password) or die(serialize(array('imap_error' => $this->parse_error(imap_last_error()))));

        foreach ($msgs_number as $msg_number)
		{
			if (imap_delete($mbox_stream, $msg_number, FT_UID));
				$return['msgs_number'][] = $msg_number;
		}

		$return['folder'] = $folder;
		$return['border_ID'] = $border_ID;

		if($mbox_stream)
			imap_close($mbox_stream, CL_EXPUNGE);
					
		$return['status'] = true;

		//Este bloco tem a finalidade de averiguar as permissoes para pastas compartilhadas
        if (substr($folder,0,4) == 'user'){
        	$acl = $this->getacltouser($folder, isset($params['decoded']));
        	/*
			* l - lookup (mailbox is visible to LIST/LSUB commands, SUBSCRIBE mailbox)
			* r - read (SELECT the mailbox, perform STATUS)
			* s - keep seen/unseen information across sessions (set or clear \SEEN flag via STORE, also set \SEEN during APPEND/COPY/        FETCH BODY[...])
			* w - write (set or clear flags other than \SEEN and \DELETED via STORE, also set them during APPEND/COPY)
			* i - insert (perform APPEND, COPY into mailbox)
			* p - post (send mail to submission address for mailbox, not enforced by IMAP4 itself)
			* k - create mailboxes (CREATE new sub-mailboxes in any implementation-defined hierarchy, parent mailbox for the new mailbox name in RENAME)
			* x - delete mailbox (DELETE mailbox, old mailbox name in RENAME)
			* t - delete messages (set or clear \DELETED flag via STORE, set \DELETED flag during APPEND/COPY)
			* e - perform EXPUNGE and expunge as a part of CLOSE
			* a - administer (perform SETACL/DELETEACL/GETACL/LISTRIGHTS)
			* Atributos da ACL para pastas compartilhadas são definidos no arquivo sharemailbox.js, na função setaclfromuser
			* Atributos da ACL para contas compartilhadas são definidos no arquivo shared_accounts.js, na função setaclfromuser
			*/
			$acl_share_delete = (stripos($acl,'t') !== false && stripos($acl,'e') !== false);

			if (!$acl_share_delete) {
				$return['status'] = false;
			}
        }

		return $return;
	}

	function refresh($params)
	{

		$return = array();
		$return['new_msgs'] = 0;
		$folder = $params['folder'];
		$msg_range_begin = $params['msg_range_begin'];
		$msg_range_end = $params['msg_range_end'];
		$msgs_existent = $params['msgs_existent'];
		$sort_box_type = $params['sort_box_type'];
		$sort_box_reverse = $params['sort_box_reverse'];
		$msgs_in_the_server = array();
		$search_box_type = $params['search_box_type'] != "ALL" && $params['search_box_type'] != "" ? $params['search_box_type'] : false;
		$msgs_in_the_server = $this->get_msgs($folder, $sort_box_type, $search_box_type, $sort_box_reverse,$msg_range_begin,$msg_range_end);
		$msgs_in_the_server = array_keys($msgs_in_the_server);
		$num_msgs = (count($msgs_in_the_server) - imap_num_recent($this->mbox));
		$dif = ($params['msg_range_end'] - $params['msg_range_begin']) +1;
		if(!count($msgs_in_the_server)){
			$msg_range_begin -= $dif;
			$msg_range_end -= $dif;
			$msgs_in_the_server = $this->get_msgs($folder, $sort_box_type, $search_box_type, $sort_box_reverse,$msg_range_begin,$msg_range_end);
			$msgs_in_the_server = array_keys($msgs_in_the_server);	
			$num_msgs = NULL;
			$return['msg_range_begin'] = $msg_range_begin;
			$return['msg_range_end'] = $msg_range_end;
		}		
		$return['new_msgs'] = imap_num_recent($this->mbox);

        //Mensagens não lidas para sincronizar emails em diferentes clientes
        $unseens = array();
        $m_search = imap_search($this->mbox, 'UNSEEN');

        if( $m_search && is_array($m_search) )
        {
			foreach( $m_search as $m ) 
			{
				$unseens[] = imap_uid($this->mbox, $m);
			}
        }

        $return['unseens'] = $unseens;
		
		$msgs_in_the_client = explode(",", $msgs_existent);

		$msg_to_insert  = array_diff($msgs_in_the_server, $msgs_in_the_client);

		if(count($msg_to_insert) > 0 && $return['new_msgs'] == 0 && $msgs_in_the_client[0] != ""){
			$aux = 0;
			while(array_key_exists($aux, $msg_to_insert)){
				if($msg_to_insert[$aux] > $msgs_in_the_client[0]){
					$return['new_msgs'] += 1;
				}
				++$aux;
			}
		}else if(count($msg_to_insert) > 0 && $msgs_in_the_server && $msgs_in_the_client[0] != "" && $return['new_msgs'] == 0){
			$aux = 0;
			while(array_key_exists($aux, $msg_to_insert)){
				if($msg_to_insert[$aux] == $msgs_in_the_server[$aux]){
					$return['new_msgs'] += 1;
				}
				++$aux;
			}
		}else if($num_msgs < $msg_range_end && $return['new_msgs'] == 0 && count($msg_to_insert) > 0 && $msg_range_end == $dif){
			$return['tot_msgs'] = $num_msgs;
		}
		
		if(!count($msgs_in_the_server)){
			return Array();
		}	

		$msg_to_delete = array_diff($msgs_in_the_client, $msgs_in_the_server);
		$msgs_to_exec = array();
		foreach($msg_to_insert as $msg_number)
			$msgs_to_exec[] = $msg_number;
		//sort($msgs_to_exec);
		$i = 0;
		foreach($msgs_to_exec as $msg_number)
		{
                    $sample = false;
                    if( (isset($this->prefs['preview_msg_subject']) || ($this->prefs['preview_msg_subject'] === '1')) && (isset($this->prefs['preview_msg_tip']    ) || ($this->prefs['preview_msg_tip']     === '1')) )
                          $sample = true;
                    
                    $return[$i] = $this->get_info_head_msg($msg_number , $sample );
                    
                    //get the next msg number to append this msg in the view in a correct place
                    $msg_key_position = array_search($msg_number, $msgs_in_the_server);
			
                    $return[$i]['msg_key_position'] = $msg_key_position;
                    if($msg_key_position !== false && array_key_exists($msg_key_position + 1,$msgs_in_the_server) !== false)
                        $return[$i]['next_msg_number'] = $msgs_in_the_server[$msg_key_position + 1];
                    else
                        $return[$i]['next_msg_number'] = $msgs_in_the_server[$msg_key_position];

                    $return[$i]['msg_folder'] = $folder;
                    ++$i;
		}
		$return['quota'] = $this->get_quota(array('folder_id' => $folder));
		$return['sort_box_type'] = $params['sort_box_type'];
                if(!$this->mbox || !is_resource($this->mbox))
                    $this->open_mbox($folder);
                
		$return['msgs_to_delete'] = $msg_to_delete;
                $return['offsetToGMT'] = $this->functions->CalculateDateOffset();
		if($this->mbox && is_resource($this->mbox))
			imap_close($this->mbox);

		return $return;
	}

     /**
     * Método que faz a verificação do Content-Type do e-mail e verifica se é um e-mail normal,
     * assinado ou cifrado.
     * @author Mário César Kolling <mario.kolling@serpro.gov.br>
     * @param $headers Uma String contendo os Headers do e-mail retornados pela função imap_imap_fetchheader
     * @param $msg_number O número da mesagem
     * @return Retorna o tipo da mensagem (normal, signature, cipher).
     */
    function getMessageType($msg_number, $headers = false , &$body = false)
    {
        //include_once(dirname( __FILE__ ) ."/../../security/classes/CertificadoB.php");
        $contentType = "normal";
      
        if( !$headers ){ $headers = imap_fetchheader($this->mbox, $msg_number, FT_UID); }

        if( preg_match("/pkcs7-signature/i", $headers) == 1 )
        {
            $contentType = "signature";
        }
        else if( preg_match("/pkcs7-mime/i", $headers) == 1 )
        {
            $contentType = testa_p7m(  $body ? $body :  imap_body($this->mbox, $msg_number , FT_UID )) ;
        }

        return $contentType;
    }
    
		/**
        * Retorna a posição que a pasta esta dentro do array de pastas
        *
        * @license    www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Cristiano Corrêa Schmidt
        * @access     public
		*/
		
	function getFolderPos(&$array , $find)
	{            
		foreach($array as $i => $v)
			if($v['id'] === $find)
				return $i;
		return false;
	}
	
        /**
        * Ordenas as pastas padrões do usuario na ordem INBOX > SENT > DRAFTS > SPAM > TRASH > OTHERS
        *
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Cristiano Corrêa Schmidt
        * @access     public
        */
	function orderDefaultFolders( &$folders , $user)
	{
		$principals = array();
		for($x = 0; $x < 5 ; ++$x)
		{
			switch ($x) {
				case 0:                             
					if( ($p = $this->getFolderPos($folders , $user )) || $p === 0 )
						$principals[] = $folders[$p];
					break;
				case 1:
					if( ($p = $this->getFolderPos($folders , $this->mount_url_folder(array($user , $this->folders['drafts'])) )) || $p === 0 )
						$principals[] = $folders[$p];
					break;
				case 2:
					if( ($p = $this->getFolderPos($folders , $this->mount_url_folder(array($user , $this->folders['sent'])) )) || $p === 0 )
						$principals[] = $folders[$p];
					break;
				case 3:
					if( ($p = $this->getFolderPos($folders , $this->mount_url_folder(array($user , $this->folders['spam'])) )) || $p === 0 )
						$principals[] = $folders[$p];
					break;
				case 4:
					if( ($p = $this->getFolderPos($folders , $this->mount_url_folder(array($user , $this->folders['trash'])) )) || $p === 0  )
						$principals[] = $folders[$p];                                           
					break;
			}
			if($p !== false)
				unset($folders[$p]);
		}
		$folders = array_merge($principals, $folders);
	}
        
        /**
        * Retorna lista de pastas do usuario no padrão que a lib javascript espera.
        *
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Cristiano Corrêa Schmidt
        * @access     public
        */
	function get_folders_list($params = null)
	{
	    $return = $this->getFolders( $params );
	
	    foreach ($return as $i => &$vv)
	    {
            if(!is_array($vv)) continue;

            $vv['folder_id'] = mb_convert_encoding($vv['folder_id'],'ISO-8859-1','UTF7-IMAP');//DECODIFICA ID DAS PASTAS COM ACENTOS
            $vv['folder_name'] = mb_convert_encoding($vv['folder_name'],'ISO-8859-1','UTF7-IMAP');//DECODIFICA NOME DAS PASTAS COM ACENTOS
            $vv['folder_parent'] = mb_convert_encoding($vv['folder_parent'],'ISO-8859-1','UTF7-IMAP');//DECODIFICA NOME DAS PASTAS COM ACENTOS
	    }

	    return ( $return );        
	}
	
	function getFolders($params = null)
	{
		///Define Variaveis
		$prefixShared = 'user'; //Prefixo das pastas compartilhadas
		$uid2cn = (isset($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['uid2cn'])) ? $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['uid2cn'] : false; 
		$mboxStream = $this->open_mbox(); //abre conexão imap
		$currentFolder = isset($params['folder']) ? $params['folder'] : 'INBOX';
		$folders = array();
		$return = array();
		///////////////////////////////////////////////////////////////
                    
		if( isset($params['onload']) && $_SESSION['phpgw_info']['expressomail']['server']['certificado'])
			$this->delete_mailbox(array('del_past' => 'INBOX'.$this->imap_delimiter.'decifradas')); //Deleta Pasta decifradas
		
		session_write_close(); // Free others requests 
		$serverString = "{".$this->imap_server.":".$this->imap_port.$this->imap_options."}"; 
		
		if ( isset($params['noSharedFolders']) )
			$folders_list = array_merge(imap_getmailboxes($mboxStream, $serverString, 'INBOX' ), imap_getmailboxes($mboxStream, $serverString, 'INBOX/*' ) );
		else
			$folders_list = imap_getmailboxes($mboxStream, $serverString, '*' );

		$folders_list = array_slice($folders_list,0,$this->foldersLimit); 

		if (!is_array($folders_list)) return false;
			if($uid2cn)
				$this->ldap = new ldap_functions();
                
		foreach ($folders_list as $i => $v ) //Separando Pastas e informações
		{
			$folderId = substr($v->name,(strpos($v->name , '}') + 1));
			$nameArray = explode($this->imap_delimiter, $folderId);
			$nameCount = count($nameArray);
			$decifrada = mb_convert_encoding('INBOX'.$this->imap_delimiter.'decifradas','UTF7-IMAP','ISO-8859-1'); //Ignorar esta pasta decifrada
			$parent = ($nameCount > 1 && $nameArray[($nameCount - 2)] !== 'INBOX') ? implode($this->imap_delimiter, array_slice($nameArray, 0, ($nameCount - 1))): ''; //Pega folder pai

			if($nameArray[0] === 'user'){

				//variaveis utilizadas para definição das permissões das pastas compartilhadas
				$acl_share_create = 0;
				$acl_share_rename = 0;
				$acl_share_delete = 0;

				//recuperando as permissões (ACLs) aplicadas na pasta
				$imap_getacl = imap_getacl($mboxStream, $folderId);

				//Se existe ACLs aplicadas a respectiva pasta (para o usuario corrente)
				if(isset($imap_getacl[ $this->username ])){
					$aclImap = $imap_getacl[ $this->username ];

					//A partir dos atributos da ACL definir permissões para cada ação (create, delete e rename):
					//http://www.ietf.org/rfc/rfc4314.txt
					$acl_share_create = (stripos($aclImap,'w') !== false && stripos($aclImap,'i') !== false && stripos($aclImap,'k') !== false) ? 1 : 0;
					$acl_share_delete = (stripos($aclImap,'x') !== false && stripos($aclImap,'t') !== false && stripos($aclImap,'e') !== false) ? 1 : 0;

					$acl_share_rename = ($acl_share_create && $acl_share_delete) ? 1 : 0;
				}

				$folders[$prefixShared.$this->imap_delimiter.$nameArray[1]][] = array(
					'id' => $folderId , 
					'stream' => $v->name , 
					'attributes' => $v->attributes , 
					'name' => $nameArray[($nameCount-1)] , 
					'user' => $nameArray[1] ,
					'parent' => $parent ,

					//Acrescentando ACLs configuradas a cada pasta compartilhada
					'acl_share' => array(
					 	'create' => $acl_share_create,
						'rename' => $acl_share_rename,
						'delete' => $acl_share_delete
					)
				);

 			}	
			else if( $folderId !== $decifrada)
			{ 
				//Escapa pasta decifrada
				$folders['INBOX'][strtolower($folderId)] = array(
					'id' => $folderId , 
					'stream' => $v->name , 
					'attributes' => $v->attributes ,
					'name' => $nameArray[($nameCount-1)] , 
					'parent' => $parent 
				);
			}
		}

		unset($folders_list); //destroy array de objetos desnecessarios
		
		ksort($folders['INBOX']);
		
		foreach($folders as $i => $v) //Ordenando e resgatando novas informações
		{
			$this->orderDefaultFolders($folders[$i] , $i);  //Ordenando Pastas Padrões
			
			foreach ($folders[$i] as $ii => $vv)
			{
				$append = array();				
				$append['folder_id'] = $vv['id'];
				$append['folder_name'] = (($uid2cn && isset($vv['user'])) && ($cn = $this->ldap->uid2cn($vv['user']))) ? $cn : $vv['name'];
				$status = imap_status($mboxStream, $vv['stream'], SA_UNSEEN); //Resgata Numero de mensagens não lidas
				$append['folder_unseen'] = isset($status->unseen) ? $status->unseen : 0 ;
				$append['folder_hasChildren'] = (($vv['attributes'] == 32) && ($vv['name'] != 'INBOX')) ? 1 : 0;
				$append['folder_parent'] = $vv['parent'];

				//Preparando o array de retorno para conter as ACLs configuradas para cada pasta
				if(isset($vv['acl_share'])){
					$append['acl_share'] = $vv['acl_share'];
				}
				$return[] = $append;
			}
		}
		
		$quotaInfo =  (!isset($params['noQuotaInfo'])) ? $this->get_quota( array('folder_id' => $currentFolder)) : false; //VERIFICA SE O USUARIO TEM COTA

		return ( ( is_array($quotaInfo) ) ?  array_merge($return, $quotaInfo) : $return );
	}
    

	function create_mailbox($arr)
	{
		$namebox	= $arr['newp'];
		$base_path = $arr['base_path'];
		$mbox_stream = $this->open_mbox();
		$imap_server = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
        /* Quebra nome da pasta quando houver pontos ou barras (possíveis delimitadores do cyrus) */
        $test = preg_split("/\/|\./", $namebox);
		if(count($test) < 1 || $base_path == null || $base_path == "" || $base_path == 'undefined'){
			if($base_path != null || $base_path != "" || $base_path != 'undefined'){
					$namebox = $base_path.$namebox;
			}
			$namebox =  mb_convert_encoding($namebox, "UTF7-IMAP", "UTF-8");
			$result = "Ok";
			
			if(!imap_createmailbox($mbox_stream,"{".$imap_server."}".$namebox))
			{
				$result = imap_last_error();
			}
		}else{
			$child = $base_path.$this->imap_delimiter;
            $test_count = count($test);
			for($i =0; $i < $test_count; ++$i){
				$child .= ($test[$i] ? $test[$i] : $this->functions->getLang("New Folder"));
				$namebox =  mb_convert_encoding($child, "UTF7-IMAP", "UTF-8");
				$result = "Ok";

				if(!imap_createmailbox($mbox_stream,"{".$imap_server."}$namebox"))
				{
					$result = imap_last_error();						
				}
				$child .=$this->imap_delimiter;
			}
		}		
		if($mbox_stream)
			imap_close($mbox_stream);
		return $result;
	}

	function create_extra_mailbox($arr)
	{
		$nameboxs = explode(";",$arr['nw_folders']);
		$result = "";
		$mbox_stream = $this->open_mbox();
		$imap_server = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
		foreach($nameboxs as $key=>$tmp){
			if($tmp != ""){
				if(!imap_createmailbox($mbox_stream,imap_utf7_encode("{".$imap_server."}$tmp"))){
					$result = implode("<br />\n", imap_errors());
					if($mbox_stream)
						imap_close($mbox_stream);
					return $result;
				}
			}
		}
		if($mbox_stream)
			imap_close($mbox_stream);
		return true;
	}

	function delete_mailbox($arr)
	{
		$namebox = $arr['del_past'];
		$imap_server = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
		$mbox_stream = $this->mbox ? $this->mbox : $this->open_mbox();
		//$del_folder = imap_deletemailbox($mbox_stream,"{".$imap_server."}INBOX.$namebox");

		$result = "Ok";
		$namebox = mb_convert_encoding($namebox, "UTF7-IMAP","UTF-8");
		if(!imap_deletemailbox($mbox_stream,"{".$imap_server."}$namebox"))
		{
			$result = imap_last_error();
		}
		/*
		if($mbox_stream)
			imap_close($mbox_stream);
		*/
		return $result;
	}

	function ren_mailbox($arr)
	{
		$namebox = $arr['current'];		
		$path_delimiter = strrpos($namebox,$this->imap_delimiter)+1;
		$base_path = substr($namebox,0,$path_delimiter);
		$rename = preg_split("/\/|\./",substr($arr['rename'], $path_delimiter));
		$new_box = array_shift($rename);
		$subfolders = $rename;
		$imap_server = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
		$mbox_stream = $this->open_mbox();
		$result = "Ok";
		$namebox = mb_convert_encoding($namebox, "UTF7-IMAP","UTF-8");
		$new_box = mb_convert_encoding($base_path.$new_box, "UTF7-IMAP","UTF-8");

		if(!imap_renamemailbox($mbox_stream,"{".$imap_server."}$namebox","{".$imap_server."}$new_box"))
		{
			$result = imap_last_error();
		}
		/*Cria as subpastas*/
		if (is_array($subfolders)){
			$child = $new_box.$this->imap_delimiter;
            $subfolders_count = count($subfolders);
			for($i =0; $i < $subfolders_count; ++$i){
				$child .= ($subfolders[$i] ? $subfolders[$i] : $this->functions->getLang("New Folder"));
				$namebox =  mb_convert_encoding($child, "UTF7-IMAP", "UTF-8");
				$result = "Ok";
				if(!imap_createmailbox($mbox_stream,"{".$imap_server."}$namebox"))
				{
					$result = imap_last_error();						
				}
				$child .=$this->imap_delimiter;
			}			
		}

		if($mbox_stream)
			imap_close($mbox_stream);
		return $result;

	}

	function get_num_msgs($params)
	{
		$folder = $params['folder'];
		if(!$this->mbox || !is_resource($this->mbox)) {
			$this->mbox = $this->open_mbox($folder);
			if(!$this->mbox || !is_resource($this->mbox))
			return imap_last_error();
		}
		$num_msgs = imap_num_msg($this->mbox);
		if($this->mbox && is_resource($this->mbox))
			imap_close($this->mbox);

		return $num_msgs;
	}

	function folder_exists($folder){
		$mbox =  $this->open_mbox();
		$serverString = "{".$this->imap_server.":".$this->imap_port.$this->imap_options."}";		
		$list = imap_getmailboxes($mbox,$serverString, $folder);
		$return = is_array($list);		
		imap_close($mbox);
		return $return;
	}

	/*Método utilizado para retornar dados da mensagem local (desarquivada na pasta lixeira)
	para poder ser anexada à mensagem.*/
    function get_info_msg_archiver($params){
        $folder = "INBOX".$this->imap_delimiter.$this->folders['trash'];
        $mbox_stream = $this->open_mbox($folder);
        $id = $params['idMsgs'];

        $name = imap_headerinfo($mbox_stream, imap_msgno($mbox_stream, $id));
        $return[] =  array(
            'uid' => $id,
            'folder' => "archiver",
            'type' => "imapMSG",
            'name' => base64_encode($name->subject.".eml")
        );

        return json_encode($return);
    }

	function send_mail($params) {
            require_once dirname(__FILE__) . '/../../services/class.servicelocator.php';
            require_once dirname(__FILE__) . '/../../prototype/api/controller.php';
            $mailService = ServiceLocator::getService('mail');

            include_once("class.db_functions.inc.php");
            $db = new db_functions();
            $fromaddress = $params['input_from'] ? explode(';', $params['input_from']) : "";
            $message_attachments_contents = (isset($params['message_attachments_content'])) ? $params['message_attachments_content'] : false;
			
            ##
            # @AUTHOR Rodrigo Souza dos Santos
            # @DATE 2008/09/17$fileName
            # @BRIEF Checks if the user has permission to send an email with the email address used.
            ##
            if (is_array($fromaddress) && ($fromaddress[1] != $_SESSION['phpgw_info']['expressomail']['user']['email'])) {
                $deny = true;
                foreach ($_SESSION['phpgw_info']['expressomail']['user']['shared_mailboxes'] as $key => $val)
                    if (array_key_exists('mail', $val) && $val['mail'][0] == $fromaddress[1])
                        $deny = false and end($_SESSION['phpgw_info']['expressomail']['user']['shared_mailboxes']);

                if ($deny)
                    return "The server denied your request to send a mail, you cannot use this mail address.";
            }

			$params['input_to'] = mb_convert_encoding($params['input_to'], "ISO-8859-1","UTF-8, ISO-8859-1");
			$params['input_cc'] = mb_convert_encoding($params['input_cc'], "ISO-8859-1","UTF-8, ISO-8859-1");
			$params['input_cco'] = mb_convert_encoding($params['input_cco'], "ISO-8859-1","UTF-8, ISO-8859-1");

            if (substr($params['input_to'], -1) == ',')
                $params['input_to'] = substr($params['input_to'], 0, -1);

            if (substr($params['input_cc'], -1) == ',')
                $params['input_cc'] = substr($params['input_cc'], 0, -1);

            if (substr($params['input_cco'], -1) == ',')
                $params['input_cco'] = substr($params['input_cco'], 0, -1);

            /*Wraps the text dividing the emails as from ">,"*/
            $toaddress = $db->getAddrs(preg_split('/>,/',preg_replace('/>,/', '>>,', $params['input_to'])));
            $ccaddress = $db->getAddrs(preg_split('/>,/',preg_replace('/>,/', '>>,', $params['input_cc'])));
            $ccoaddress = $db->getAddrs(preg_split('/>,/',preg_replace('/>,/', '>>,', $params['input_cco'])));

            if ($toaddress["False"] || $ccaddress["False"] || $ccoaddress["False"]) {
                return $this->parse_error("Invalid Mail:", ($toaddress["False"] ? $toaddress["False"] : ($ccaddress["False"] ? $ccaddress["False"] : $ccoaddress["False"])));
            }

            $toaddress = implode(',', $toaddress);
            $ccaddress = implode(',', $ccaddress);
            $ccoaddress = implode(',', $ccoaddress);

            if ($toaddress == "" && $ccaddress == "" && $ccoaddress == "") {
                return $this->parse_error("Invalid Mail:", ($params['input_to'] ? $params['input_to'] : ($params['input_cc'] ? $params['input_cc'] : $params['input_cco'])));
            }

            $toaddress = preg_replace('/<\s+/', '<', $toaddress);
            $toaddress = preg_replace('/\s+>/', '>', $toaddress);

            $ccaddress = preg_replace('/<\s+/', '<', $ccaddress);
            $ccaddress = preg_replace('/\s+>/', '>', $ccaddress);

            $ccoaddress = preg_replace('/<\s+/', '<', $ccoaddress);
            $ccoaddress = preg_replace('/\s+>/', '>', $ccoaddress);

            $replytoaddress = $params['input_reply_to'];
            $subject = mb_convert_encoding($params['input_subject'], "ISO-8859-1","UTF-8, ISO-8859-1");
            $return_receipt = $params['input_return_receipt'];
            $is_important = $params['input_important_message'];
            $encrypt = $params['input_return_cripto'];
            $signed = $params['input_return_digital'];

			$params['attachments'] = mb_convert_encoding($params['attachments'], "UTF7-IMAP","UTF-8, ISO-8859-1, UTF7-IMAP");
            $message_attachments = $params['message_attachments'];



            // Valida numero Maximo de Destinatarios 
            if ($_SESSION['phpgw_info']['expresso']['expressoMail']['expressoAdmin_maximum_recipients'] > 0) {
                $sendersNumber = count(explode(',', $params['input_to']));

                if ($params['input_cc'])
                    $sendersNumber += count(explode(',', $params['input_cc']));
                if ($params['input_cco'])
                    $sendersNumber += count(explode(',', $params['input_cco']));

                $userMaxmimumSenders = $db->getMaximumRecipientsUser($this->username);
                if ($userMaxmimumSenders) {
                    if ($sendersNumber > $userMaxmimumSenders)
                        return $this->functions->getLang('Number of recipients greater than allowed');
                }
                else {
                    $ldap = new ldap_functions();
                    $groupsToUser = $ldap->get_user_groups($this->username);

                    $groupMaxmimumSenders = $db->getMaximumRecipientsGroup($groupsToUser);

                    if ($groupMaxmimumSenders > 0) {
                        if ($sendersNumber > $groupMaxmimumSenders)
                            return $this->functions->getLang('Number of recipients greater than allowed');
                    }
                    else {
                        if ($sendersNumber > $_SESSION['phpgw_info']['expresso']['expressoMail']['expressoAdmin_maximum_recipients'])
                            return $this->functions->getLang('Number of recipients greater than allowed');
                    }
                }
            }
            //Fim Valida numero maximo de destinatarios 
            //Valida envio de email para shared accounts
            if ($_SESSION['phpgw_info']['expresso']['expressoMail']['expressoMail_block_institutional_comunication'] == 'true') {
                $ldap = new ldap_functions();
                $arrayF = explode(';', $params['input_from']);

                /*
                 * Verifica se o remetente n?o ? uma conta compartilhada
                 */
                if (!$ldap->isSharedAccountByMail($arrayF[1])) {
                    $groupsToUser = $ldap->get_user_groups($this->username);
                    $sharedAccounts = $ldap->returnSharedsAccounts($toaddress, $ccaddress, $ccoaddress);

                    /*
                     * Pega o UID do remetente
                     */
                    $uidFrom = $ldap->mail2uid($arrayF[1]);

                    /*
                     * Remove a conta compartilhada caso o uid do remetente exista na conta compartilhada
                     */
                    foreach ($sharedAccounts as $key => $value) {
                        if ($value)
                            $acl = $this->getaclfrombox($value);

                        if (array_key_exists($uidFrom, $acl))
                            unset($sharedAccounts[$key]);
                    }

                    /*
                     * Caso ainda exista contas compartilhadas, verifica se existe alguma exce??o para estas contas
                     */
                    if (count($sharedAccounts) > 0)
                        $accountsBlockeds = $db->validadeSharedAccounts($this->username, $groupsToUser, $sharedAccounts);

                    /*
                     * Retorna as contas compartilhadas bloqueadas
                     */
                    if (count($accountsBlockeds) > 0) {
                        $return = '';

                        foreach ($accountsBlockeds as $accountBlocked)
                            $return.= $accountBlocked . ', ';

                        $return = substr($return, 0, -2);

                        return $this->functions->getLang('you are blocked from sending mail to the following addresses') . ': ' . $return;
                    }
                }
            }
            // Fim Valida envio de email para shared accounts
    //	    TODO - implementar tratamento SMIME no novo serviço de envio de emails e retirar o AND false abaixo
            if ($params['smime'] AND false) {
                $body = $params['smime'];
                $mail->SMIME = true;
                // A MSG assinada deve ser testada neste ponto.
                // Testar o certificado e a integridade da msg....
                include_once(dirname(__FILE__) . "/../../security/classes/CertificadoB.php");
                $erros_acumulados = '';
                $certificado = new certificadoB();
                $validade = $certificado->verificar($body);
                if (!$validade) {
                    foreach ($certificado->erros_ssl as $linha_erro) {
                        $erros_acumulados .= $linha_erro;
                    }
                } else {
                    // Testa o CERTIFICADO: se o CPF  he o do usuario logado, se  pode assinar msgs e se  nao esta expirado...
                    if ($certificado->apresentado) {
                        if ($certificado->dados['EXPIRADO'])
                            $erros_acumulados .='Certificado expirado.';
                        $this->cpf = isset($GLOBALS['phpgw_info']['server']['certificado_atributo_cpf']) && $GLOBALS['phpgw_info']['server']['certificado_atributo_cpf'] != '' ? $_SESSION['phpgw_info']['expressomail']['user'][$GLOBALS['phpgw_info']['server']['certificado_atributo_cpf']] : $this->username;
                        if ($certificado->dados['CPF'] != $this->cpf)
                            $erros_acumulados .=' CPF no certificado diferente do logado no expresso.';
                        if (!($certificado->dados['KEYUSAGE']['digitalSignature'] && $certificado->dados['EXTKEYUSAGE']['emailProtection']))
                            $erros_acumulados .=' Certificado nao permite assinar mensagens.';
                    }
                    else {
                        $$erros_acumulados .= 'Nao foi possivel usar o certificado para assinar a msg';
                    }
                }
                if (!$erros_acumulados == '') {
                    return $erros_acumulados;
                }
            } else {
                //Compatibilização com Outlook, ao encaminhar a mensagem
                $body = mb_ereg_replace('<!--\[', '<!-- [', base64_decode($params['body']));
				$body = str_replace("&lt;","&yzwkx;",$body); //Alterar as Entities padrão das tags < > para compatibilizar com o Expresso
				$body = str_replace("&gt;","&xzwky;",$body);
                $body = str_replace("%nbsp;","&nbsp;",$body);
                //$body = preg_replace("/\n/"," ",$body);
                //$body = preg_replace("/\r/","" ,$body);
                $body = html_entity_decode ( $body, ENT_QUOTES , 'ISO-8859-1' );	
				$body = str_replace("&yzwkx;","&lt;",$body);
				$body = str_replace("&xzwky;","&gt;",$body);
            }

            $attachments = $_FILES;
            $forwarding_attachments = $params['forwarding_attachments'];
            $local_attachments = $params['local_attachments'];
	

            //Test if must be saved in shared folder and change if necessary
            if ($fromaddress[2] == 'y') {
                //build shared folder path
                $newfolder = "user" . $this->imap_delimiter . $fromaddress[3] . $this->imap_delimiter . $this->imap_sentfolder;

                if ($this->folder_exists($newfolder)){
					$has_new_folder = false;
                    $folder = $newfolder;
				}
                else{
					$name_folder = $this->imap_sentfolder;
					$base_path = "user" . $this->imap_delimiter . $fromaddress[3];
					$arr_new_folder['newp'] = $name_folder;
					$arr_new_folder['base_path'] = $base_path;

					$this->create_mailbox($arr_new_folder);	
					$has_new_folder = true;
                    $folder = $newfolder;
				}
            } else {
				$has_new_folder = false;
                $folder = $params['folder'];
            }

            $folder = mb_convert_encoding($folder, 'UTF7-IMAP', 'ISO-8859-1');
            $folder = preg_replace('/INBOX[\/.]/i', 'INBOX' . $this->imap_delimiter, $folder);
            $folder_name = $params['folder_name'];

    //		TODO - tratar assinatura e remover o AND false
            if ($signed && !$params['smime'] AND false) {
                $mail->Mailer = "smime";
                $mail->SignedBody = true;
            }

			$from = $fromaddress ?  ('"' . $fromaddress[0] . '" <' . $fromaddress[1] . '>') : ('"' . $_SESSION['phpgw_info']['expressomail']['user']['firstname'] . ' ' . $_SESSION['phpgw_info']['expressomail']['user']['lastname'] . '" <' . $_SESSION['phpgw_info']['expressomail']['user']['email'] . '>');
			$mailService->setFrom( mb_convert_encoding($from, "ISO-8859-1","UTF-8, ISO-8859-1"));
        
			$mailService->addHeaderField('Reply-To', !!$replytoaddress ? $replytoaddress : $from);

            $bol = $this->add_recipients('to', $toaddress, $mailService);
            if (!$bol) {
                return $this->parse_error("Invalid Mail:", $toaddress);
            }
            $bol = $this->add_recipients('cc', $ccaddress, $mailService);
            if (!$bol) {
                return $this->parse_error("Invalid Mail:", $ccaddress);
            }
            $allow = $_SESSION['phpgw_info']['server']['expressomail']['allow_hidden_copy'];

            if ($allow) {
                //$mailService->addBcc($ccoaddress);
                $bol = $this->add_recipients('cco', $ccoaddress, $mailService);

                if (!$bol) {
                    return $this->parse_error("Invalid Mail:", $ccoaddress);
                }
            }

            //Implementação para o In-Reply-To e References				
            $msg_numb = $params['messageNum'];
            $msg_folder = $params['messageFolder'];
            $this->mbox = $this->open_mbox($msg_folder);

            $header = $this->get_header($msg_numb);
            $header_ = imap_fetchheader($this->mbox, $msg_numb, FT_UID);
            $pattern = '/^[ \t]*Disposition-Notification-To:.*/mi';
			if (preg_match($pattern, $header_, $fields))
				$return['DispositionNotificationTo'] = base64_encode(trim(str_ireplace('Disposition-Notification-To:', '', $fields[0]))); 

            $message_id = $header->message_id;
            $references = array();
            if ($message_id != "") {
                $mailService->addHeaderField('In-Reply-To', $message_id);

                if (isset($header->references)) {
                    array_push($references, $header->references);
                }
                array_push($references, $message_id);
                $mailService->addHeaderField('References', $references);
            }


            $mailService->setSubject($subject);
            $isHTML = ( isset($params['type']) && $params['type'] == 'html' )?  true : false;

    		//	TODO - tratar mensagem criptografada e remover o AND false abaixo
            if (($encrypt && $signed && $params['smime']) || ($encrypt && !$signed) AND false) { // a msg deve ser enviada cifrada...
                $email = $this->add_recipients_cert($toaddress . ',' . $ccaddress . ',' . $ccoaddress);
                $email = explode(",", $email);
                // Deve ser testado se foram obtidos os certificados de todos os destinatarios.
                // Deve ser verificado um numero limite de destinatarios.
                // Deve ser verificado se os certificados sao validos.
                // Se uma das verificacoes falhar, nao enviar o e-mail e avisar o usuario.
                // O array $mail->Certs_crypt soh deve ser preenchido se os certificados passarem nas verificacoes.
                $numero_maximo = $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['num_max_certs_to_cipher'];  // Este valor dever ser configurado pelo administrador do site ....
                $erros_acumulados = "";
                $aux_mails = array();
                $mail_list = array();
                if (count($email) > $numero_maximo) {
                    $erros_acumulados .= "Excedido o numero maximo (" . $numero_maximo . ") de destinatarios para uma msg cifrada...." . chr(0x0A);
                    return $erros_acumulados;
                }
                // adiciona o email do remetente. eh para cifrar a msg para ele tambem. Assim vai poder visualizar a msg na pasta enviados..
                $email[] = $_SESSION['phpgw_info']['expressomail']['user']['email'];
                foreach ($email as $item) {
                    $certificate = $db->get_certificate(strtolower($item));
                    if (!$certificate) {
                        $erros_acumulados .= "Chamada com parametro invalido.  e-Mail nao pode ser vazio." . chr(0x0A);
                        return $erros_acumulados;
                    }

                    if (array_key_exists("dberr1", $certificate)) {

                        $erros_acumulados .= "Ocorreu um erro quando pesquisava certificados dos destinatarios para cifrar a msg." . chr(0x0A);
                        return $erros_acumulados;
                    }
                    if (array_key_exists("dberr2", $certificate)) {
                        $erros_acumulados .= $item . ' : Nao  pode cifrar a msg. Certificado nao localizado.' . chr(0x0A);
                        //continue;
                    }
                    /*  Retirado este teste para evitar mensagem de erro duplicada.
                      if (!array_key_exists("certs", $certificate))
                      {
                      $erros_acumulados .=  $item . ' : Nao  pode cifrar a msg. Certificado nao localizado.' . chr(0x0A);
                      continue;
                      }
                     */
                    include_once(dirname(__FILE__) . "/../../security/classes/CertificadoB.php");

                    foreach ($certificate['certs'] as $registro) {
                        $c1 = new certificadoB();
                        $c1->certificado($registro['chave_publica']);
                        if ($c1->apresentado) {
                            $c2 = new Verifica_Certificado($c1->dados, $registro['chave_publica']);
                            if (!$c1->dados['EXPIRADO'] && !$c2->revogado && $c2->status) {
                                $aux_mails[] = $registro['chave_publica'];
                                $mail_list[] = strtolower($item);
                            } else {
                                if ($c1->dados['EXPIRADO'] || $c2->revogado) {
                                    $db->update_certificate($c1->dados['SERIALNUMBER'], $c1->dados['EMAIL'], $c1->dados['AUTHORITYKEYIDENTIFIER'], $c1->dados['EXPIRADO'], $c2->revogado);
                                }

                                $erros_acumulados .= $item . ':  ' . $c2->msgerro . chr(0x0A);
                                foreach ($c2->erros_ssl as $linha) {
                                    $erros_acumulados .= $linha . chr(0x0A);
                                }
                                $erros_acumulados .= 'Emissor: ' . $c1->dados['EMISSOR'] . chr(0x0A);
                                $erros_acumulados .= $c1->dados['CRLDISTRIBUTIONPOINTS'] . chr(0x0A);
                            }
                        } else {
                            $erros_acumulados .= $item . ' : Nao  pode cifrar a msg. Certificado invalido.' . chr(0x0A);
                        }
                    }
                    if (!(in_array(strtolower($item), $mail_list)) && !empty($erros_acumulados)) {
                        return $erros_acumulados;
                    }
                }

                $mail->Certs_crypt = $aux_mails;
            }

            $attachment = json_decode($params['attachments'],TRUE);
            $message_size_total = 0;
            foreach ($attachment as &$value) 
            {
                if((int)$value > 0) //BD attachment
                {
                     $att = Controller::read(array('id'=> $value , 'concept' => 'mailAttachment'));

                     if($att['disposition'] == 'embedded' && $isHTML) //Caso mensagem em texto simples converter os embedded para attachments
                     {
                         $body = str_replace('"../prototype/getArchive.php?mailAttachment='.$att['id'].'"', '"'.mb_convert_encoding($att['name'], 'ISO-8859-1' , 'UTF-8,ISO-8859-1').'"', $body);
                         $mailService->addStringImage(base64_decode($att['source']), $att['type'], mb_convert_encoding($att['name'], 'ISO-8859-1' , 'UTF-8,ISO-8859-1'));
                     }
                     else
                         $mailService->addStringAttachment(base64_decode($att['source']), mb_convert_encoding($att['name'], 'ISO-8859-1' , 'UTF-8,ISO-8859-1'), $att['type'], 'base64', isset($att['disposition']) ? $att['disposition'] :'attachment' );
                     
                     $message_size_total += $att['size'];
                     unset($att);
                }
                else //message attachment
                {
                    $value = json_decode($value, true);
                    if($value["folder"] == "archiver"){
                        $value['folder'] = "INBOX/Trash";
                    }

                    switch ($value['type']) {
                        case 'imapPart':
                                $att = $this->getForwardingAttachment(mb_convert_encoding($value['folder'] , 'ISO-8859-1' , 'UTF7-IMAP'),$value['uid'], $value['part']);

                                if(strstr($body,'src="./inc/get_archive.php?msgFolder='.$value['folder'].'&msgNumber='.$value['uid'].'&indexPart='.$value['part'].'"') !== false)//Embeded IMG
                                {    
                                    $body = str_ireplace('src="./inc/get_archive.php?msgFolder='.$value['folder'].  '&msgNumber='.$value['uid'].'&indexPart='.$value['part'].'"' , 'src="'.$att['name'].'"', $body);
                                    $mailService->addStringImage($att['source'], $att['type'],mb_convert_encoding($att['name'], 'ISO-8859-1' , 'UTF-8,ISO-8859-1') );
                                }
                                else
                                    $mailService->addStringAttachment($att['source'], mb_convert_encoding($att['name'], 'ISO-8859-1' , 'UTF-8,ISO-8859-1'), $att['type'], 'base64', isset($att['disposition']) ? $att['disposition'] :'attachment' );
                                 
                                $message_size_total += $att['size']; //Adiciona o tamanho do anexo a variavel que controlao tamanho da msg.
                                unset($att);
                            break;
                            case 'imapMSG':
                                $mbox_stream = $this->open_mbox(mb_convert_encoding($value['folder'] , 'ISO-8859-1' , 'UTF7-IMAP'));
                                $rawmsg = $this->getRawHeader($value['uid']) . "\r\n\r\n" . $this->getRawBody($value['uid']);
                                
                                $mailService->addStringAttachment($rawmsg, mb_convert_encoding(base64_decode($value['name']), 'ISO-8859-1' , 'UTF-8,ISO-8859-1'), 'message/rfc822', '7bit', 'attachment' );
                                /*envia o anexo para o email*/
                                $message_size_total += mb_strlen($rawmsg); //Adiciona o tamanho do anexo a variavel que controlao tamanho da msg.
                                unset($rawmsg);
                            break;

                        default:
                            break;
                    }
                }
            }
	    
            $message_size_total += strlen($params['body']);   /* Tamanho do corpo da mensagem. */        

            ////////////////////////////////////////////////////////////////////////////////////////////////////	
            /**
             * Faz a validação pelo tamanho máximo de mensagem permitido para o usuário. Se o usuário não estiver em nenhuma regra, usa o tamanho padrão.
             */
            $default_max_size_rule = $db->get_default_max_size_rule();
            if (!$default_max_size_rule) {
                $default_max_size_rule = str_replace("M", "", ini_get('upload_max_filesize')) * 1024 * 1024; /* hack para não bloquear o envio de email quando não for configurado um tamanho padrão */
            } else {
                foreach ($default_max_size_rule as $i => $value) {
                    $default_max_size_rule = $value['config_value'];
                }
            }

            $default_max_size_rule = $default_max_size_rule * 1024 * 1024;    /* Tamanho da regra padrão, em bytes */
            $id_user = $_SESSION['phpgw_info']['expressomail']['user']['userid'];


            $ldap = new ldap_functions();
            $groups_user = $ldap->get_user_groups($id_user);

            $size_rule_by_group = array();
            foreach ($groups_user as $k => $value_) {
                $rule_in_group = $db->get_rule_by_user_in_groups($k);
                if ($rule_in_group != "")
                    array_push($size_rule_by_group, $rule_in_group);
            }

            $n_rule_groups = 0;
            $maior_valor_regra_grupo = 0;
            foreach ($size_rule_by_group as $i => $value) {
                if (is_array($value[0])) {
                    ++$n_rule_groups;
                    if ($value[0]['email_max_recipient'] > $maior_valor_regra_grupo)
                        $maior_valor_regra_grupo = $value[0]['email_max_recipient'];
                }
            }

            if ($default_max_size_rule) {
                $size_rule = $db->get_rule_by_user($_SESSION['phpgw_info']['expressomail']['user']['userid']);

                if (!$size_rule && $n_rule_groups == 0) /* O usuário não está em nenhuma regra por usuário nem por grupo. Vai usar a regra padrão. */ {
                    if ($message_size_total > $default_max_size_rule)
                        return $this->functions->getLang("Message size greateruler than allowed (Default rule)")." (".$default_max_size_rule / 1024 / 1024 ." Mb)";
                }

                else {
                    if (count($size_rule) > 0) /* Verifica se existe regra por usuário. Se houver, ela vai se sobresair das regras por grupo. */ {
                        $regra_mais_permissiva = 0;
                        foreach ($size_rule as $i => $value) {
                            if ($regra_mais_permissiva < $value['email_max_recipient'])
                                $regra_mais_permissiva = $value['email_max_recipient'];
                        }
                        $regra_mais_permissiva = $regra_mais_permissiva * 1024 * 1024;
                        if ($message_size_total > $regra_mais_permissiva)
                            return $this->functions->getLang("Message size greater than allowed (Rule By User)");
                    }
                    else /* Regra por grupo */ {
                        $maior_valor_regra_grupo = $maior_valor_regra_grupo * 1024 * 1024;
                        if ($message_size_total > $maior_valor_regra_grupo)
                            return $this->functions->getLang("Message size greater than allowed (Rule By Group)");
                    }
                }
            }
            /**
             * Fim da validação do tamanho da regra do tamanho de mensagem.
             */
            ////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($isHTML)
            {
                $this->rfc2397ToEmbeddedAttachment($mailService , $body);

                $defaultStyle = '';
                if(isset($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['font_family_editor']) && $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['font_family_editor'])
                    $defaultStyle .= ' font-family:'.$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['font_family_editor'] .';';
                
                if(isset($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['font_size_editor']) && $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['font_size_editor'])
                    $defaultStyle .= ' font-size:'.$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['font_size_editor'].';';
    
                $body = '<span class="'.$defaultStyle.'">'.$body.'</span>';
                $mailService->setBodyHtml($body);
            }    
            else
                $mailService->setBodyText(mb_convert_encoding($body, 'ISO-8859-1' , 'UTF-8,ISO-8859-1' ));

            if ($is_important)
                $mailService->addHeaderField('Importance', 'High');

            if ($return_receipt)
                $mailService->addHeaderField('Disposition-Notification-To', $_SESSION['phpgw_info']['expressomail']['user']['email']);

            $mailService->addHeaderField('Date', date("r"));

            if ($folder != 'null') {
                $mbox_stream = $this->open_mbox($folder);
                @imap_append($mbox_stream, "{" . $this->imap_server . ":" . $this->imap_port . "}" . $folder, $mailService->getMessage(), "\\Seen");
            }

            $sent = $mailService->send();

            if ($sent !== true) {
                return $this->parse_error($sent);
            } else {
                if ($signed && !$params['smime']) {
                    return $sent;
                }
                if ($_SESSION['phpgw_info']['server']['expressomail']['expressoMail_enable_log_messages'] == "True") {
                    $userid = $_SESSION['phpgw_info']['expressomail']['user']['userid'];
                    $userip = $_SESSION['phpgw_info']['expressomail']['user']['session_ip'];
                    $now = date("d/m/y H:i:s");
                    $addrs = $toaddress . $ccaddress . $ccoaddress;
                    $sent = trim($sent);
                    error_log("$now - $userip - $sent [$subject] - $userid => $addrs\r\n", 3, "/home/expressolivre/mail_senders.log");
                }
                if($params['uids_save'] )
					$this->delete_msgs(array('folder'=> $params['save_folder'] , 'msgs_number' => $params['uids_save']));
                       
                //return array("success" => true, "folder" => $folder_list);
				return array("success" => true, "load" => $has_new_folder);
                
            }
    }
	
	
	function add_recipients_cert($full_address)
	{
		$result = "";
		$parse_address = imap_rfc822_parse_adrlist($full_address, "");
		foreach ($parse_address as $val)
		{
			//echo "<script language=\"javascript\">javascript:alert('".$val->mailbox."@".$val->host."');</script>";
			if ($val->mailbox == "INVALID_ADDRESS")
				continue;
			if ($val->mailbox == "UNEXPECTED_DATA_AFTER_ADDRESS")
				continue;
			if (empty($val->personal))
				$result .= $val->mailbox."@".$val->host . ",";
			else
				$result .= $val->mailbox."@".$val->host . ",";
		}

		return substr($result,0,-1);
	}

	function add_recipients($recipient_type, $full_address, $mail, $mobile = false)
	{
		//remove a comma if is given two unexpected commas
		$full_address = preg_replace("/, ?,/",",",$full_address);
		$parse_address = imap_rfc822_parse_adrlist($full_address, "");

		$bolean = true;		
		foreach ($parse_address as $val)
		{
			//echo "<script language=\"javascript\">javascript:alert('".$val->mailbox."@".$val->host."');</script>";
			if ($val->mailbox == "INVALID_ADDRESS")
				continue;
			switch($recipient_type)
			{
				case "to":
					if($mobile){
						$mail->AddAddress($val->mailbox."@".$val->host, $val->personal);
					}else{
						$mail->AddTo( ($val->personal ? "\"$val->personal\" <$val->mailbox@$val->host>" : "$val->mailbox@$val->host"));
					} 
					break;
				case "cc":
					if($mobile){
						$mail->AddCC($val->mailbox."@".$val->host, $val->personal);
					}else{
						$mail->AddCC( ($val->personal ? "\"$val->personal\" <$val->mailbox@$val->host>" : "$val->mailbox@$val->host"));
					}
					break;
				case "cco":
					$mail->AddBcc(($val->personal ? "\"$val->personal\" <$val->mailbox@$val->host>" : "$val->mailbox@$val->host"));
					break;
			}
			if($val->mailbox == "UNEXPECTED_DATA_AFTER_ADDRESS"){
				$bolean = false;
			}
		}
		return $bolean;
	}
        
        function getForwardingAttachment($folder, $uid, $part, $rfc_822bodies = true , $info = true )
        {
            include_once dirname(__FILE__).'/class.attachment.inc.php';
            $attachment = new attachment();
            $attachment->decodeConf['rfc_822bodies'] = $rfc_822bodies; //Forçar a não decodificação de mensagens em anexo.
			$folder = urldecode($folder);
    		$attachment->setStructureFromMail($folder, $uid);
            
            if($info === true)
            {
                $return = $attachment->getAttachmentInfo($part);
                $return['source'] = $attachment->getAttachment($part);
                return $return;
            }
            return $attachment->getAttachment($part);
        }
            
	function del_last_caracter($string)
	{
		$string = substr($string,0,(strlen($string) - 1));
		return $string;
	}

	function del_last_two_caracters($string)
	{
		$string = substr($string,0,(strlen($string) - 2));
		return $string;
	}

	function messages_sort($sort_box_type,$sort_box_reverse, $search_box_type,$offsetBegin,$offsetEnd,$folder)
	{
		$sort = array();
		if ($sort_box_type != "SORTFROM" && $search_box_type!= "FLAGGED"){
			$imapsort = imap_sort($this->mbox,constant($sort_box_type),$sort_box_reverse,SE_UID,$search_box_type);
			foreach($imapsort as $iuid){
				$sort[$iuid] = $iuid;
			}
			if ($offsetBegin == -1 && $offsetEnd ==-1 )
				$slice_array = false;
			else
				$slice_array = true;
		}
		else
		{
			if ($offsetBegin > $offsetEnd) {$temp=$offsetEnd; $offsetEnd=$offsetBegin; $offsetBegin=$temp;}
			$num_msgs = imap_num_msg($this->mbox);
			if ($offsetEnd >  $num_msgs) {$offsetEnd = $num_msgs;}
			$slice_array = true;
            $from_to_sent = $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['from_to_sent'];  
			$dates = array();
			for ($i=$num_msgs; $i>0; $i--)
			{
				if ($sort_box_type == "SORTARRIVAL" && $sort_box_reverse && count($sort) >= $offsetEnd)
					break;
				$iuid = @imap_uid($this->mbox,$i);
				$header = $this->get_header($iuid);
				
				// List UNSEEN messages.
				if($search_box_type == "UNSEEN" &&  (!trim($header->Recent) && !trim($header->Unseen))){
					continue;
				}
				// List SEEN messages.
				elseif($search_box_type == "SEEN" && (trim($header->Recent) || trim($header->Unseen))){
					continue;
				}
				// List ANSWERED messages.
				elseif($search_box_type == "ANSWERED" && !trim($header->Answered)){
					continue;
				}
				// List FLAGGED messages.
				elseif($search_box_type == "FLAGGED" && !trim($header->Flagged)){
					continue;
				}

				if($sort_box_type=='SORTFROM') {
					if ($this->prefs['save_in_folder'] == $folder && $from_to_sent)
						$tmp = self::formatMailObject($header->to[0]);
					else
						$tmp = self::formatMailObject($header->from[0]);
					$sort[$iuid] = ($tmp['name']) ? $tmp['name'] : $tmp['email'];	
				}
				else if($sort_box_type=='SORTSUBJECT') {
					$sort[$iuid] = $header->subject;
				}
				else if($sort_box_type=='SORTSIZE') {
					$sort[$iuid] = $header->Size;
				}
				else {
					$sort[$iuid] = $header->udate;
				}
				$dates[$iuid] = $header->udate;
			}
			$keys = array_keys($sort);
			
			//Applies the strtolower() function in each element of $sort array name
			$sort_lowercase = array_map('strtolower',$sort);
			array_multisort($sort_lowercase, SORT_ASC,SORT_STRING,$sort,$keys, SORT_NUMERIC, SORT_DESC, $dates, SORT_DESC);
			$sort = array_combine($keys, $sort);
			if ($sort_box_reverse)
				$sort = array_reverse($sort,true);
		}
		if(empty($sort) or !is_array($sort)){
			$sort = array();
		}

        $this->msgIds = $sort;

		if ($slice_array)
			$sort = array_slice($sort,$offsetBegin-1,$offsetEnd-($offsetBegin-1),true);
		return $sort;

	}

	function move_delete_search_messages($params){
		$move = false;
		$msg_no_move = "";
	
		$params['selected_messages'] = urldecode($params['selected_messages_move']);
		$params['new_folder'] = urldecode($params['new_folder_move']);
		$params['new_folder_name'] = urldecode($params['new_folder_name_move']);
		$sel_msgs = explode(",", $params['selected_messages']);
		@reset($sel_msgs);
		$sorted_msgs = array();
		foreach($sel_msgs as $idx => $sel_msg) {
			$sel_msg = explode(";", $sel_msg);
			 if(array_key_exists($sel_msg[0], $sorted_msgs)){
			 	$sorted_msgs[$sel_msg[0]] .= ",".$sel_msg[1];
			 }
			 else {
				$sorted_msgs[$sel_msg[0]] = $sel_msg[1];
			 }
		}		
		@ksort($sorted_msgs);
		$last_return = false;
		foreach($sorted_msgs as $folder => $msgs_number) {
			$params['msgs_number'] = $msgs_number;
			$params['folder'] = $folder;
				
			$last_return = $this->move_messages($params);
			
			if($last_return['status']){
				$move = true;
			}else{
				$msg_no_move =  $params['msgs_number'];
			}
		}
		$sel_msgs = null;		
		$params['selected_messages'] = urldecode($params['selected_messages_delete']);
		$params['new_folder'] = urldecode($params['new_folder_delete']);
		$params['new_folder_name'] = urldecode($params['new_folder_name_delete']);
		$sel_msgs = explode(",", $params['selected_messages']);
		@reset($sel_msgs);
		$sorted_msgs = array();
		foreach($sel_msgs as $idx => $sel_msg) {
			$sel_msg = explode(";", $sel_msg);
			 if(array_key_exists($sel_msg[0], $sorted_msgs)){
			 	$sorted_msgs[$sel_msg[0]] .= ",".$sel_msg[1];
			 }
			 else {
				$sorted_msgs[$sel_msg[0]] = $sel_msg[1];
			 }
		}
		@ksort($sorted_msgs);
		$last_return = false;
		foreach($sorted_msgs as $folder => $msgs_number) {
			$params['msgs_number'] = $msgs_number;
			$params['folder'] = $folder;
		
			$params['folder'] = $params['new_folder_delete'];
			$last_return = $this->delete_msgs($params);
			$last_return['deleted'] = true;
			if($last_return['status']){
				$move = true;
			}else{
				$msg_no_move =  $params['msgs_number'];
			}
		
		}
	
		if($move)
			$last_return['move'] = true;
			
		if($msg_no_move != "")
			$last_return['no_move'] = $msg_no_move;
		
		return $last_return;
	}

	function move_search_messages($params){
		$params['selected_messages'] = str_replace('/',$this->imap_delimiter,urldecode($params['selected_messages']));
		$params['new_folder'] = str_replace('/',$this->imap_delimiter,urldecode($params['new_folder']));
		$params['new_folder_name'] = urldecode($params['new_folder_name']);
		$sel_msgs = explode(",", $params['selected_messages']);
		$move = false;
		$msg_no_move = "";
		
		@reset($sel_msgs);
		$sorted_msgs = array();
		foreach($sel_msgs as $idx => $sel_msg) {
			$sel_msg = explode(";", $sel_msg);
			 if(array_key_exists($sel_msg[0], $sorted_msgs)){
			 	$sorted_msgs[$sel_msg[0]] .= ",".$sel_msg[1];
			 }
			 else {
				$sorted_msgs[$sel_msg[0]] = $sel_msg[1];
			 }
		}
		@ksort($sorted_msgs);
		$last_return = false;
		foreach($sorted_msgs as $folder => $msgs_number) {
			$params['msgs_number'] = $msgs_number;
			$params['folder'] = $folder;
			
		if($params['delete'] === 'true'){
			$params['folder'] = $params['new_folder'];
			$last_return = $this->delete_msgs($params);
				$last_return['deleted'] = true;
			
			if($last_return['status']){
				$move = true;
			}else{
				$msg_no_move =  $params['msgs_number'];
			}
			
		}else{
				$last_return = $this->move_messages($params);
				
				if($last_return['status']){
					$move = true;
				}else{
					$msg_no_move =  $params['msgs_number'];
			}
		}
		}
		
		if($move)
			$last_return['move'] = true;
			
		if($msg_no_move != "")
			$last_return['no_move'] = $msg_no_move;
			
		return $last_return;
	}

    function verifyShareFolder($params){
        $folder = $params['folder'];

         if (substr($folder,0,4) == 'user'){
            $acl = $this->getacltouser($folder, isset($params['decoded']));

            $acl_share_delete = (stripos($acl,'t') !== false && stripos($acl,'e') !== false);

            if (!$acl_share_delete) {
                $return['status'] = false;
                return $return;
            }
        }
    }
	function move_messages($params)
	{
		$folder = $params['folder'];
                $newmailbox = mb_convert_encoding($params['new_folder'], "UTF7-IMAP", ( isset($params['decoded']) ? "" : "ISO-8859-1, " )."UTF-8, UTF7-IMAP" );
		$new_folder_name = isset($params['decoded']) ? mb_convert_encoding($params['new_folder_name'], "ISO-8859-1", "UTF-8" ) : $params['new_folder_name'];
                $msgs_number = $params['msgs_number'];
		$return = array('msgs_number' => $msgs_number,
						'folder' => $folder,
						'new_folder_name' => $new_folder_name,
						'border_ID' => $params['border_ID'],
						'status' => true); //Status foi adicionado para validar as permissoes ACL

		//Este bloco tem a finalidade de averiguar as permissoes para pastas compartilhadas
        if (substr($folder,0,4) == 'user'){
        	$acl = $this->getacltouser($folder, isset($params['decoded']));

        	/*
			* l - lookup (mailbox is visible to LIST/LSUB commands, SUBSCRIBE mailbox)
			* r - read (SELECT the mailbox, perform STATUS)
			* s - keep seen/unseen information across sessions (set or clear \SEEN flag via STORE, also set \SEEN during APPEND/COPY/        FETCH BODY[...])
			* w - write (set or clear flags other than \SEEN and \DELETED via STORE, also set them during APPEND/COPY)
			* i - insert (perform APPEND, COPY into mailbox)
			* p - post (send mail to submission address for mailbox, not enforced by IMAP4 itself)
			* k - create mailboxes (CREATE new sub-mailboxes in any implementation-defined hierarchy, parent mailbox for the new mailbox name in RENAME)
			* x - delete mailbox (DELETE mailbox, old mailbox name in RENAME)
			* t - delete messages (set or clear \DELETED flag via STORE, set \DELETED flag during APPEND/COPY)
			* e - perform EXPUNGE and expunge as a part of CLOSE
			* a - administer (perform SETACL/DELETEACL/GETACL/LISTRIGHTS)
			* Os Atributos da ACL para pastas compartilhadas são definidos no arquivo sharemailbox.js, na função setaclfromuser
			* Os Atributos da ACL para contas compartilhadas são definidos no arquivo shared_accounts.js, na função setaclfromuser
			*/
			$acl_share_delete = (stripos($acl,'t') !== false && stripos($acl,'e') !== false);

			if (!$acl_share_delete) {
				$return['status'] = false;
				return $return;
			}
        }
        //Este bloco tem a finalidade de transformar o CPF das pastas compartilhadas em common name
        if(array_key_exists('uid2cn', $_SESSION['phpgw_info']['user']['preferences']['expressoMail'])){
        if ($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['uid2cn']){
            if (substr($new_folder_name,0,4) == 'user'){
                $this->ldap = new ldap_functions();
                $tmp_folder_name = explode($this->imap_delimiter, $new_folder_name);
                $return['new_folder_name'] = array_pop($tmp_folder_name);
                if( $cn = $this->ldap->uid2cn($return['new_folder_name']))
                {
                    $return['new_folder_name'] = $cn;
                }
            }
        }
		}

		// Caso estejamos no box principal, nao eh necessario pegar a informacao da mensagem anterior.
		if (($params['get_previous_msg']) && ($params['border_ID'] != 'null') && ($params['border_ID'] != ''))
		{
			$return['previous_msg'] = $this->get_info_previous_msg($params);
			// Fix problem in unserialize function JS.
			if(array_key_exists('body', $return['previous_msg']))
			$return['previous_msg']['body'] = str_replace(array('{','}'), array('&#123;','&#125;'), $return['previous_msg']['body']);
		}

		$mbox_stream = $this->open_mbox($folder);
		if(imap_mail_move($mbox_stream, $msgs_number, $newmailbox, CP_UID)) {
			imap_expunge($mbox_stream);
			if($mbox_stream)
				imap_close($mbox_stream);
			return $return;
		}else {
			if(strstr(imap_last_error(),'Over quota')) {
				$accountID	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapAdminUsername'];
				$pass		= $_SESSION['phpgw_info']['expressomail']['email_server']['imapAdminPW'];
				$userID 	= $_SESSION['phpgw_info']['expressomail']['user']['userid'];
				$server 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
				$mbox		= @imap_open("{".$this->imap_server.":".$this->imap_port.$this->imap_options."}INBOX", $accountID, $pass) or die(serialize(array('imap_error' => $this->parse_error(imap_last_error()))));
				if(!$mbox)
					return imap_last_error();
				$quota 	= imap_get_quotaroot($mbox_stream, "INBOX");
				if(! imap_set_quota($mbox, "user".$this->imap_delimiter.$userID, 2.1 * $quota['usage'])) {
					if($mbox_stream)
						imap_close($mbox_stream);
					if($mbox)
						imap_close($mbox);
					return "move_messages(): Error setting quota for MOVE or DELETE!! ". "user".$this->imap_delimiter.$userID." line ".__LINE__."\n";
				}
				if(imap_mail_move($mbox_stream, $msgs_number, $newmailbox, CP_UID)) {
					imap_expunge($mbox_stream);
					if($mbox_stream)
						imap_close($mbox_stream);
					// return to original quota limit.
					if(!imap_set_quota($mbox, "user".$this->imap_delimiter.$userID, $quota['limit'])) {
						if($mbox)
							imap_close($mbox);
						return "move_messages(): Error setting quota for MOVE or DELETE!! line ".__LINE__."\n";
					}
					return $return;
				}
				else {
					if($mbox_stream)
						imap_close($mbox_stream);
					if(!imap_set_quota($mbox, "user".$this->imap_delimiter.$userID, $quota['limit'])) {
						if($mbox)
							imap_close($mbox);
						return "move_messages(): Error setting quota for MOVE or DELETE!! line ".__LINE__."\n";
					}
					return imap_last_error();
				}

			}
			else {
				if($mbox_stream)
					imap_close($mbox_stream);
				
				$msg_error = "move_messages() line ".__LINE__.": ". imap_last_error()." folder:".$newmailbox;
				trigger_error($msg_error);
				return $msg_error;
			}
		}
	}
	
	function set_messages_flag_from_search($params){
		$error = False;
		$fileNames = "";
		
		$sel_msgs = explode(",", $params['msg_to_flag']);
		@reset($sel_msgs);
		$sorted_msgs = array();
		foreach($sel_msgs as $idx => $sel_msg) {
			$sel_msg = explode(";", $sel_msg);
			if(array_key_exists($sel_msg[0], $sorted_msgs)){
				$sorted_msgs[$sel_msg[0]] .= ",".$sel_msg[1];
			}
			else {
				$sorted_msgs[$sel_msg[0]] = $sel_msg[1];
			}
		}
		unset($sorted_msgs['']);			
		$array_names_keys = array_keys($sorted_msgs);	
		// Verifica se as n mensagens selecionadas
		// se encontram em um mesmo folder
		if (count($sorted_msgs)==1){
			$param['folder'] = $array_names_keys[0];
			$param['msgs_to_set'] = $sorted_msgs[$array_names_keys[0]];
			$param['flag'] = $params['flag'];
			$returns[0] = $this->set_messages_flag($param);
			return $returns;
		}else{
            $array_names_keys_count = count($array_names_keys);
			for($i = 0; $i < $array_names_keys_count; ++$i){
				$param['folder'] = $array_names_keys[$i];
				$param['msgs_to_set'] = $sorted_msgs[$array_names_keys[$i]];
				$param['flag'] = $params['flag'];
				$returns[$i] = $this->set_messages_flag($param);
		}
	}
	return $returns;
}
    function verify_disposition_notification($msg){
        $header = imap_fetchheader($this->mbox, $msg, FT_UID);
        $pattern = '/^[ \t]*Disposition-Notification-To:.*/mi';
        if (preg_match($pattern, $header, $fields))
            return true;
        else
            return false;
    }

	function set_messages_flag($params)
	{		
		$folder = ( isset($params['decoded']) ) ? $params['folder'] : mb_convert_encoding($params['folder'], "UTF7-IMAP", "ISO-8859-1, UTF-8, UTF7-IMAP");
		$msgs_to_set = $params['msgs_to_set'];
		$flag = $params['flag'];
		$return = array();
		$return["msgs_to_set"] = $msgs_to_set;
		$return["flag"] = $flag;
		$return["msgs_not_to_set"] = "";
			
		$this->mbox = $this->open_mbox($folder);
			
		if ($flag == "unseen"){
			$return["msgs_to_set"] = "";
			$msgs = explode(",",$msgs_to_set);
			foreach($msgs as $men){
				if (imap_clearflag_full($this->mbox, $men, "\\Seen", ST_UID))
					$return["msgs_to_set"] .= $men.",";
				else
					$return["msgs_not_to_set"] .= $men.",";
			}
			$return["status"] = true;
		}elseif ($flag == "seen"){
			$return["msgs_to_set"] = "";
			$msgs = explode(",",$msgs_to_set);
			foreach($msgs as $men){

                if($this->verify_disposition_notification($men)){

                    if(!array_key_exists('disposition_notification_to', $return))
                        $return['disposition_notification_to'] = array();

                    $return["disposition_notification_to"][] = $men;
                }else{
                    if (imap_setflag_full($this->mbox, $men, "\\Seen", ST_UID))
                        $return["msgs_to_set"] .= $men.",";
                    else
                        $return["msgs_not_to_set"] .= $men.",";
                }
			}
			$return["status"] = true;
		}elseif ($flag == "answered"){
			$return["status"] = imap_setflag_full($this->mbox, $msgs_to_set, "\\Answered", ST_UID);
			imap_clearflag_full($this->mbox, $msgs_to_set, "\\Draft", ST_UID);
		}
		elseif ($flag == "forwarded")
			$return["status"] = imap_setflag_full($this->mbox, $msgs_to_set, "\\Answered \\Draft", ST_UID);
		elseif ($flag == "flagged")
			$return["status"] = imap_setflag_full($this->mbox, $msgs_to_set, "\\Flagged", ST_UID);
		elseif ($flag == "unflagged") {
			$flag_importance = false;
			$msgs_number = explode(",",$msgs_to_set);
			$unflagged_msgs = "";
			foreach($msgs_number as $msg_number) {
				preg_match('/importance *: *(.*)\r/i',
					imap_fetchheader($this->mbox, imap_msgno($this->mbox, $msg_number))
					,$importance);
				if(strtolower($importance[1])=="high" && $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_important_flag']) {
					$flag_importance=true;
				}
				else {
					$unflagged_msgs.=$msg_number.",";
				}
			}

			if($unflagged_msgs!="") {
				imap_clearflag_full($this->mbox,substr($unflagged_msgs,0,strlen($unflagged_msgs)-1), "\\Flagged", ST_UID);
				$return["msgs_unflageds"] = substr($unflagged_msgs,0,strlen($unflagged_msgs)-1);
			}
			else {
				$return["msgs_unflageds"] = false;
			}

			if($flag_importance && $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_important_flag']) {
				$return["status"] = false;
				$return["msg"] = $this->functions->getLang("At least one of selected message cant be marked as normal");
			}
			else {
				$return["status"] = true;
			}
		}
		
		if(($flag == "seen") || ($flag == "unseen")){
			if ($return["msgs_not_to_set"] != ""){
				$return["msgs_not_to_set"] = substr($return["msgs_not_to_set"], 0, -1);
				$return["status"] = false;
			}
			if($return["msgs_to_set"] != ""){
				$return["msgs_to_set"] = substr($return["msgs_to_set"], 0, -1);
			}
		}
		if($this->mbox && is_resource($this->mbox))
			imap_close($this->mbox);		
		return $return;
	}

	function get_file_type($file_name)
	{
		$file_name = strtolower($file_name);
	   	$strFileType = strrev(substr(strrev($file_name),0,4));
	   	if ($strFileType == ".eml")
   			return "message/rfc822";
	   	if ($strFileType == ".asf")
   			return "video/x-ms-asf";
	   	if ($strFileType == ".avi")
   			return "video/avi";
	   	if ($strFileType == ".doc")
   			return "application/msword";
	   	if ($strFileType == ".zip")
   			return "application/zip";
	   	if ($strFileType == ".xls")
   			return "application/vnd.ms-excel";
	   	if ($strFileType == ".gif")
   			return "image/gif";
	   	if ($strFileType == ".jpg" || $strFileType == "jpeg")
   			return "image/jpeg";
   		if ($strFileType == ".png")
   			return "image/png";
	   	if ($strFileType == ".wav")
   			return "audio/wav";
	   	if ($strFileType == ".mp3")
   			return "audio/mpeg3";
	   	if ($strFileType == ".mpg" || $strFileType == "mpeg")
   			return "video/mpeg";
	   	if ($strFileType == ".rtf")
   			return "application/rtf";
	   	if ($strFileType == ".htm" || $strFileType == "html")
   			return "text/html";
	   	if ($strFileType == ".xml")
   			return "text/xml";
	   	if ($strFileType == ".xsl")
   			return "text/xsl";
	   	if ($strFileType == ".css")
   			return "text/css";
	   	if ($strFileType == ".php")
   			return "text/php";
	   	if ($strFileType == ".asp")
   			return "text/asp";
	   	if ($strFileType == ".pdf")
   			return "application/pdf";
	   	if ($strFileType == ".txt")
   			return "text/plain";
	   	if ($strFileType == ".wmv")
   			return "video/x-ms-wmv";
		if ($strFileType == ".sxc")
			return "application/vnd.sun.xml.calc";
		if ($strFileType == ".stc")
			return "application/vnd.sun.xml.calc.template";
		if ($strFileType == ".sxd")
			return "application/vnd.sun.xml.draw";
		if ($strFileType == ".std")
			return "application/vnd.sun.xml.draw.template";
		if ($strFileType == ".sxi")
			return "application/vnd.sun.xml.impress";
		if ($strFileType == ".sti")
			return "application/vnd.sun.xml.impress.template";
		if ($strFileType == ".sxm")
			return "application/vnd.sun.xml.math";
		if ($strFileType == ".sxw")
			return "application/vnd.sun.xml.writer";
		if ($strFileType == ".sxq")
			return "application/vnd.sun.xml.writer.global";
		if ($strFileType == ".stw")
			return "application/vnd.sun.xml.writer.template";


   		return "application/octet-stream";
	}

	function htmlspecialchars_encode($str)
	{
		return  str_replace( array('&', '"','\'','<','>','{','}'), array('&amp;','&quot;','&#039;','&lt;','&gt;','&#123;','&#125;'), $str);
	}
	function htmlspecialchars_decode($str)
	{
		return  str_replace( array('&amp;','&quot;','&#039;','&lt;','&gt;','&#123;','&#125;'), array('&', '"','\'','<','>','{','}'), $str);
	}

	function get_msgs($folder, $sort_box_type, $search_box_type, $sort_box_reverse,$offsetBegin = 0,$offsetEnd = 0)
	{
		if(!$this->mbox || !is_resource($this->mbox))
			$this->mbox = $this->open_mbox($folder);

		if($offsetEnd == 0 && $offsetBegin == 0){
			$offsetEnd = imap_num_msg($this->mbox);
			$offsetBegin = 1;
		}
		return $this->messages_sort($sort_box_type,$sort_box_reverse, $search_box_type,$offsetBegin,$offsetEnd,$folder);
	}

	function get_info_next_msg($params)
	{
		$msg_number = $params['msg_number'];
		$folder = $params['msg_folder'];
		$sort_box_type = $params['sort_box_type'];
		$sort_box_reverse = $params['sort_box_reverse'];
		$reuse_border = $params['reuse_border'];
		$search_box_type = $params['search_box_type'] != "ALL" && $params['search_box_type'] != "" ? $params['search_box_type'] : false;
		$sort_array_msg = $this -> get_msgs($folder, $sort_box_type, $search_box_type, $sort_box_reverse);

		$success = false;
		if (is_array($sort_array_msg))
		{
			foreach ($sort_array_msg as $i => $value){
				if ($value == $msg_number)
				{
					$success = true;
					break;
				}
			}
		}

		if (! $success || $i >= sizeof($sort_array_msg)-1)
		{
			$params['status'] = 'false';
			$params['command_to_exec'] = "delete_border('". $reuse_border ."');";
			return $params;
		}

		$params = array();
		$params['msg_number'] = $sort_array_msg[($i+1)];
		$params['msg_folder'] = $folder;

		$return = $this->get_info_msg($params);
		$return["reuse_border"] = $reuse_border;
		return $return;
	}

	function get_info_previous_msg($params)
	{
		$msg_number = $params['msgs_number'];
		$folder = $params['folder'];
		$sort_box_type = $params['sort_box_type'];
		$sort_box_reverse = $params['sort_box_reverse'];
		$reuse_border = $params['reuse_border'];
		$search_box_type = $params['search_box_type'] != "ALL" && $params['search_box_type'] != "" ? $params['search_box_type'] : false;
		$sort_array_msg = $this -> get_msgs($folder, $sort_box_type, $search_box_type, $sort_box_reverse);

		$success = false;
		if (is_array($sort_array_msg))
		{
			foreach ($sort_array_msg as $i => $value){
				if ($value == $msg_number)
				{
					$success = true;
					break;
				}
			}
		}
		if (! $success || $i == 0)
		{
			$params['status'] = 'false';
			$params['command_to_exec'] = "delete_border('". $reuse_border ."');";
			return $params;
		}

		$params = array();
		$params['msg_number'] = $sort_array_msg[($i-1)];
		$params['msg_folder'] = $folder;

		$return = $this->get_info_msg($params);
		$return["reuse_border"] = $reuse_border;
		return $return;
	}

	// This function updates the values: quota, paging and new messages menu.
	function get_menu_values($params){
		$return_array = array();
		$return_array = $this->get_quota($params);

		$mbox_stream = $this->open_mbox($params['folder']);
		$return_array['num_msgs'] = imap_num_msg($mbox_stream);
		if($mbox_stream)
			imap_close($mbox_stream);

		return $return_array;
	}

	function get_quota($params){

		$folder_id = str_replace('/',$this->imap_delimiter,$params['folder_id']);
		$folder_id = mb_convert_encoding($folder_id, "UTF7-IMAP","UTF-8, ISO-8859-1, UTF7-IMAP");
		if(!$this->mbox || !is_resource($this->mbox))
			$this->mbox = $this->open_mbox();

		$quota = imap_get_quotaroot($this->mbox, $folder_id);
		if($this->mbox && is_resource($this->mbox))
			imap_close($this->mbox);

		if (!$quota){
			return array(
				'quota_percent' => 0,
				'quota_used' => 0,
				'quota_limit' =>  0
			);
		}

		if(count($quota) && $quota['limit']) {
			$quota_limit = $quota['limit'];
			$quota_used  = $quota['usage'];
			if($quota_used >= $quota_limit)
			{
				$quotaPercent = 100;
			}
			else
			{
			$quotaPercent = ($quota_used / $quota_limit)*100;
			$quotaPercent = (($quotaPercent)* 100 + .5 )* .01;
			}
			return array(
				'quota_percent' => floor($quotaPercent),
				'quota_used' => $quota_used,
				'quota_limit' =>  $quota_limit
			);
		}
		else
			return array();
	}

	function send_notification($params)
	{
		$mailService = ServiceLocator::getService('mail'); 
		$body = lang("Your message: %1",$params['subject']) . '<br>';
		$body .= lang("Received in: %1",date("d/m/Y H:i",$params['date'])) . '<br>';
		$body .= lang("Has been read by: %1 &lt; %2 &gt; at %3", $_SESSION['phpgw_info']['expressomail']['user']['fullname'], $_SESSION['phpgw_info']['expressomail']['user']['email'], date("d/m/Y H:i"));
		return $mailService->sendMail(base64_decode($params['notificationto']), 
 							   $_SESSION['phpgw_info']['expressomail']['user']['email'], 
 							   $this->htmlspecialchars_decode(lang("Read receipt: %1",$params['subject'])), 
 							   $body); 

	}

	function empty_folder($params)
	{
		$folder = (isset($params['shared']) ? $params['shared'] : 'INBOX') . $this->imap_delimiter . $_SESSION['phpgw_info']['expressomail']['email_server'][$params['clean_folder']];
		$mbox_stream = $this->open_mbox($folder);
		$return = imap_delete($mbox_stream,'1:*');
		if (!$return)
			$return = imap_errors();
		if($mbox_stream)
			imap_close($mbox_stream, CL_EXPUNGE);
		return $return;
	}

	function search($params)
	{
		include("class.imap_attachment.inc.php");
		$imap_attachment = new imap_attachment();
		$criteria = $params['criteria'];
		$return = array();
		$folders = $this->get_folders_list();

		$j = 0;
		foreach($folders as $folder)
		{
			$mbox_stream = $this->open_mbox($folder);
			$messages = imap_search($mbox_stream, $criteria, SE_UID);

			if ($messages == '')
				continue;

			$i = 0;
			$return[$j] = array();
			$return[$j]['folder_name'] = $folder['name'];

			foreach($messages as $msg_number)
			{
				$header = $this->get_header($msg_number);
				if (!is_object($header))
					return false;

				$return[$j][$i]['msg_folder']	= $folder['name'];
				$return[$j][$i]['msg_number']	= $msg_number;
				$return[$j][$i]['Recent']		= $header->Recent;
				$return[$j][$i]['Unseen']		= $header->Unseen;
				$return[$j][$i]['Answered']	= $header->Answered;
				$return[$j][$i]['Deleted']		= $header->Deleted;
				$return[$j][$i]['Draft']		= $header->Draft;
				$return[$j][$i]['Flagged']		= $header->Flagged;

				$date_msg = gmdate("d/m/Y",$header->udate);
				if (gmdate("d/m/Y") == $date_msg)
					$return[$j][$i]['udate'] = gmdate("H:i",$header->udate);
				else
					$return[$j][$i]['udate'] = $date_msg;

				$fromaddress = imap_mime_header_decode($header->fromaddress);
				$return[$j][$i]['fromaddress'] = '';
				foreach ($fromaddress as $tmp)
					$return[$j][$i]['fromaddress'] .= $this->replace_maior_menor($tmp->text);

				$from = $header->from;
				$return[$j][$i]['from'] = array();
				$tmp = imap_mime_header_decode($from[0]->personal);
				$return[$j][$i]['from']['name'] = $tmp[0]->text;
				$return[$j][$i]['from']['email'] = $from[0]->mailbox . "@" . $from[0]->host;
				$return[$j][$i]['from']['full'] ='"' . $return[$j][$i]['from']['name'] . '" ' . '<' . $return[$j][$i]['from']['email'] . '>';

				$to = $header->to;
				$return[$j][$i]['to'] = array();
				$tmp = imap_mime_header_decode($to[0]->personal);
				$return[$j][$i]['to']['name'] = $tmp[0]->text;
				$return[$j][$i]['to']['email'] = $to[0]->mailbox . "@" . $to[0]->host;
				$return[$j][$i]['to']['full'] ='"' . $return[$i]['to']['name'] . '" ' . '<' . $return[$i]['to']['email'] . '>';

				$subject = imap_mime_header_decode($header->fetchsubject);
				$return[$j][$i]['subject'] = '';
				foreach ($subject as $tmp)
					$return[$j][$i]['subject'] .= $tmp->text;

				$return[$j][$i]['Size'] = $header->Size;
				$return[$j][$i]['reply_toaddress'] = $header->reply_toaddress;

				$return[$j][$i]['attachment'] = array();
				$return[$j][$i]['attachment'] = $imap_attachment->get_attachment_headerinfo($mbox_stream, $msg_number);

				++$i;
			}
			++$j;
			if($mbox_stream)
				imap_close($mbox_stream);
		}

		return $return;
	}


	function mobile_search($params)
	{
		include("class.imap_attachment.inc.php");
		$imap_attachment = new imap_attachment();
		$criterias = array ("TO","SUBJECT","FROM","CC");
		$return = array();
		if(!isset($params['folder'])) {
			$folder_params = array("noSharedFolders"=>1);
			if(isset($params['folderType']))
				$folder_params['folderType'] = $params['folderType'];
			$folders = $this->get_folders_list($folder_params);
		}
		else
			$folders = array(0=>array('folder_id'=>$params['folder']));
		$num_msgs = 0;
		$max_msgs = $params['max_msgs'] + 1; //get one more because mobile paginate
		$return["msgs"] = array();
		
		//get max_msgs of each folder order by date and later order all messages together and retur only max_msgs msgs
		foreach($folders as $id =>$folder)
		{
			if(strpos($folder['folder_id'],'user')===false && is_array($folder)) {
				foreach($criterias as $criteria_fixed)
				{
					$_filter = $criteria_fixed . ' "'.$params['filter'].'"';

					$mbox_stream = $this->open_mbox($folder['folder_id']);

					$messages = imap_sort($mbox_stream,SORTARRIVAL,1,SE_UID,$_filter);
					
					if ($messages == ''){
						if($mbox_stream)
							imap_close($mbox_stream);
						continue;	
					}
					
					foreach($messages as $msg_number)
					{
						$temp = $this->get_info_head_msg($msg_number);
						if(!$temp)
							return false;
						$temp['msg_folder'] = $folder['folder_id'];
						$return["msgs"][$num_msgs] = $temp;
						++$num_msgs;
					}

					if($mbox_stream)
						imap_close($mbox_stream);
				}
			}
		}

		if(!function_exists("cmp_date")) {
			function cmp_date($obj1, $obj2){
		    if($obj1['timestamp'] == $obj2['timestamp']) return 0;
		    return ($obj1['timestamp'] < $obj2['timestamp']) ? 1 : -1;
			}
		}
		usort($return["msgs"], "cmp_date");
		$return["has_more_msg"] = (sizeof($return["msgs"]) > $max_msgs);
		$return["msgs"] = array_slice($return["msgs"], 0, $max_msgs);
		$return["msgs"]['num_msgs'] = $num_msgs;
		
		return $return;
	}

	function delete_and_show_previous_message($params)
	{
		$return = $this->get_info_previous_msg($params);

		$params_tmp1 = array();
		$params_tmp1['msgs_to_delete'] = $params['msg_number'];
		$params_tmp1['folder'] = $params['msg_folder'];
		$return_tmp1 = $this->delete_msg($params_tmp1);

		$return['msg_number_deleted'] = $return_tmp1;

		return $return;
	}


	function automatic_trash_cleanness($params)
	{
		$before_date = date("m/d/Y", strtotime("-".$params['before_date']." day"));
		$criteria =  'BEFORE "'.$before_date.'"';
		//$mbox_stream = $this->open_mbox('INBOX'.$this->folders['trash']);
		$mbox_stream = $this->open_mbox($this->mount_url_folder(array("INBOX",$this->folders['trash'])));
		
		// Free others requests 
                session_write_close(); 
		$messages = imap_search($mbox_stream, $criteria, SE_UID);
		if (is_array($messages)){
			foreach ($messages as $msg_number){
				imap_delete($mbox_stream, $msg_number, FT_UID);
			}
		}
		if($mbox_stream)
			imap_close($mbox_stream, CL_EXPUNGE);
		return $messages;
	}
// 	Fix the search problem with special characters!!!!
	function remove_accents($string) {
		return strtr($string,
	 	"?Ó??ó?Ý?úÁÀÃÂÄÇÉÈÊËÍÌ?ÎÏÑÕÔÓÒÖÚÙ?ÛÜ?áàãâäçéèêëíì?îïñóòõôöúù?ûüýÿ",
	 	"SOZsozYYuAAAAACEEEEIIIIINOOOOOUUUUUsaaaaaceeeeiiiiinooooouuuuuyy");
	}

        function make_search_date($date,$before = false){

            //TODO: Adaptar a data de acordo com o locale do sistema.
            list($day,$month,$year) = explode("/", $date);
            $before?$day=(int)$day+1:$day=(int)$day;
            $timestamp = mktime(0,0,0,(int)$month,$day,(int)$year);
            $search_date = date('d-M-Y',$timestamp);
            return $search_date;

        }

	function search_msg( $params = false )
	{
		include '../prototype/api/controller.php';
		if(strpos($params['condition'],"#")===false)
		{ //local messages
			$search=false;
		}
		else
		{
			$search = explode(",",$params['condition']);
		}

		$params['page'] = $params['page'] * 1;

	    if( is_array($search) )
	    {
			$search = array_unique($search); // Remove duplicated folders
			$search_criteria = '';
			$search_result_number = $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'];
			foreach($search as $tmp)
			{
				$tmp1 = explode("##",$tmp);
				$sum = 0;
				$name_box = $tmp1[0];
				unset($filter);
				foreach($tmp1 as $index => $criteria)
				{
					if ($index != 0 && strlen($criteria) != 0)
					{
						$filter_array = explode("<=>",html_entity_decode(rawurldecode($criteria)));
						$filter .= " ".$filter_array[0];
						if (strlen($filter_array[1]) != 0)
						{
							if ( trim($filter_array[0]) != 'BEFORE' &&
								 trim($filter_array[0]) != 'SINCE' &&
								 trim($filter_array[0]) != 'ON')
							{
							    $filter .= '"'.$filter_array[1].'"';
							}else if(trim($filter_array[0]) == 'BEFORE' ){
                                                            $filter .= '"'.$this->make_search_date($filter_array[1],true).'"';
							}else{
                                                            $filter .= '"'.$this->make_search_date($filter_array[1]).'"';
                                                        }
						}
					}
				}
				
				$name_box = mb_convert_encoding(utf8_decode($name_box), "UTF7-IMAP", "ISO-8859-1" );
				$filter = $this->remove_accents($filter);

				//Este bloco tem a finalidade de transformar o login (quando numerico) das pastas compartilhadas em common name
				if ($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['uid2cn'] && substr($name_box,0,4) == 'user')
				{
					$folder_name = explode($this->imap_delimiter,$name_box);
					$this->ldap = new ldap_functions();
					
					if ($cn = $this->ldap->uid2cn($folder_name[1]))
					{
						$folder_name[1] = $cn;
					}
					$folder_name = implode($this->imap_delimiter,$folder_name);
				}
				else
					$folder_name = mb_convert_encoding(utf8_decode($name_box), "UTF7-IMAP", "ISO-8859-1" );
				
	
			        $this->open_mbox($name_box);

				if (preg_match("/^.?\bALL\b/", $filter))
				{ 
					// Quick Search, note: this ALL isn't the same ALL from imap_search
					$all_criterias = array ("TO","SUBJECT","FROM","CC");
					    
					foreach($all_criterias as $criteria_fixed)
					{
						$_filter = $criteria_fixed . substr($filter,4);
						
						$search_criteria = imap_search($this->mbox, $_filter, SE_UID);
						
						if(is_array($search_criteria))
						{
							foreach($search_criteria as $new_search)
							{
								$elem = $this->get_info_head_msg($new_search);
								$elem['udate']       = gmdate('d/m/Y', $elem['udate'] + $this->functions->CalculateDateOffset()); 
								$elem['boxname'] = mb_convert_encoding( $name_box, "ISO-8859-1", "UTF7-IMAP" ); 
								$elem['uid'] = $new_search;
                                                                /* compare dates in ordering */
								$elem['udatecomp'] = substr ($elem['udate'], -4) ."-". substr ($elem['udate'], 3, 2) ."-". substr ($elem['udate'], 0, 2);
								$retorno[] = $elem; 
							}
						}
					}
				}
				else{
					$search_criteria = imap_search($this->mbox, $filter, SE_UID);
                                    if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_important_flag'])
                                    {
                                        if((!strpos($filter,"FLAGGED") === false) || (!strpos($filter,"UNFLAGGED") === false))
                                        {
                                            $num_msgs = imap_num_msg($this->mbox);
                                            $flagged_msgs = array();
                                            for ($i=$num_msgs; $i>0; $i--)
                                            {
                                                $iuid = @imap_uid($this->mbox,$i);
                                                $header = $this->get_header($iuid);
                                                if(trim($header->Flagged))
                                                {
                                                        $flagged_msgs[$i] = $iuid;
                                                }
                                            }
						if((count($flagged_msgs) >0) && (strpos($filter,"UNFLAGGED") === false))
                                            {
                                                    $arry_diff = is_array($search_criteria) ? array_diff($flagged_msgs,$search_criteria):$flagged_msgs;
                                                    foreach($arry_diff as $msg)
                                            {
							$search_criteria[] = $msg;
                                            }
                                        }
						else if((count($flagged_msgs) >0) && (is_array($search_criteria)) && (!strpos($filter,"UNFLAGGED") === false))
                                        {
                                                    $search_criteria = array_diff($search_criteria,$flagged_msgs);
                                        }
                                    }
                                    }

                                    if( is_array( $search_criteria) )
                                    {
                                        foreach($search_criteria as $new_search)
                                        {
										
                                            $elem = $this->get_info_head_msg( $new_search );
                                            $elem['udate']       = gmdate('d/m/Y', $elem['udate'] + $this->functions->CalculateDateOffset()); 
											$elem['boxname'] = mb_convert_encoding( $name_box, "ISO-8859-1", "UTF7-IMAP" ); 
                                            $elem['uid'] = $new_search;
                                            /* compare dates in ordering */
                                            $elem['udatecomp'] = substr ($elem['udate'], -4) ."-". substr ($elem['udate'], 3, 2) ."-". substr ($elem['udate'], 0, 2);
                                            if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_followupflags_and_labels'] == "1")
                                            {
                                                $filter = array('AND', array('=', 'folderName', $name_box), array('=','messageNumber', $new_search));
                                                $followupflagged = Controller::find(
                                                    array('concept' => 'followupflagged'),
                                                    false,
                                                    array('filter' => $filter, 'criteria' => array('deepness' => '2'))
                                                );

                                                if(isset($followupflagged[0]['followupflagId']))
                                                {
                                                    $followupflag = Controller::read( array( 'concept' => 'followupflag', 'id' => $followupflagged[0]['followupflagId'] ));
                                                    $followupflagged[0]['followupflag'] = $followupflag;
                                                    $elem['followupflagged'] = $followupflagged[0];

                                                }
                                                $labeleds = Controller::find(
                                                    array('concept' => 'labeled'),
                                                    false,
                                                    array('filter' => $filter, 'criteria' => array('deepness' => '2'))
                                                );
                                                foreach ($labeleds as $e){
                                                    $labels = Controller::read( array( 'concept' => 'label', 'id' =>  $e['labelId']));
                                                    $elem['labels'][$e['labelId']] = $labels;
                                                }
                                            }
                                            $retorno[] = $elem;
                                        }
                                    }
				}
			}
		}
		
            imap_close($this->mbox);
	    $num_msgs = count($retorno);
	    /* Comparison functions, descendent is ascendent with parms inverted */
	    function SORTDATE($a, $b){ return ($a['udatecomp'] < $b['udatecomp']); }
	    function SORTDATE_REVERSE($b, $a) { return SORTDATE($a,$b); }

	    function SORTWHO($a, $b) { return (strtoupper($a['from']) > strtoupper($b['from'])); }
	    function SORTWHO_REVERSE($b, $a) { return SORTWHO($a,$b); }

	    function SORTSUBJECT($a, $b) { return (strtoupper($a['subject']) > strtoupper($b['subject'])); }
	    function SORTSUBJECT_REVERSE($b, $a) { return SORTSUBJECT($a,$b); }

	    function SORTBOX($a, $b) { return ($a['boxname'] > $b['boxname']); }
	    function SORTBOX_REVERSE($b, $a) { return SORTBOX($a,$b); }

	    function SORTSIZE($a, $b) { return ($a['size'] > $b['size']); }
	    function SORTSIZE_REVERSE($b, $a) { return SORTSIZE($a,$b); }

	    usort( $retorno, $params['sort_type']);
	    $pageret = array_slice( $retorno, $params['page'] * $this->prefs['max_email_per_page'], $this->prefs['max_email_per_page']);
	    
	    $arrayRetorno['num_msgs']	=  $num_msgs;
	    $arrayRetorno['data']		=  $pageret;
	    $arrayRetorno['currentTab'] =  $params['current_tab'];
        return ($pageret) ? $arrayRetorno : 'none';
	}

	function size_msg($size){
		$var = floor($size/1024);
		if($var >= 1){
			return $var." kb";
		}else{
			return $size ." b";
		}
	}
	
	function ob_array($the_object)
	{
	   $the_array=array();
	   if(!is_scalar($the_object))
	   {
	       foreach($the_object as $id => $object)
	       {
	           if(is_scalar($object))
	           {
	               $the_array[$id]=$object;
	           }
	           else
	           {
	               $the_array[$id]=$this->ob_array($object);
	           }
	       }
	       return $the_array;
	   }
	   else
	   {
	       return $the_object;
	   }
	}

	function getacl()
	{
		$this->ldap = new ldap_functions();
		$mbox_stream = $this->open_mbox();
		$mbox_acl = imap_getacl($mbox_stream, 'INBOX');

		$oldAcls = array('d' , 'c' , 'a');
		$newAcls = array('xte','ik', '');

		$return = array();
		foreach ($mbox_acl as $user => $acl)
		{
			if($user == $this->username) 
				continue;

			//Compatibiliza acls no padrão antigo para o novo
			$acl = str_replace($oldAcls, $oldAcls, $acl);

			$return[$user] = array(
					'cn' => $this->ldap->uid2cn($user) ,
					'acls' => $acl
					);
		}
		return $return;
	}

	function setacl($params)
	{
		$old_users = $this->getacl();
		$new_users = unserialize($params['acls']);

		$mbox_stream = $this->open_mbox();
		$serverString = "{".$this->imap_server.":".$this->imap_port.$this->imap_options."}";
		$mailboxes_list = imap_getmailboxes($mbox_stream, $serverString, "user".$this->imap_delimiter.$this->username."*");                

		foreach ($new_users as $user => $value) {
			if(isset($old_users[$user]) && $value['acls'] == $old_users[$user]['acls'])
			{
				unset($old_users[$user]);
				unset($new_users[$user]);
			}
		}

		foreach ($new_users as $user => $value)
		{
	        if (is_array($mailboxes_list))
	        {
	            foreach ($mailboxes_list as $key => $val)
	            {
	                $folder = str_replace($serverString, "", imap_utf7_decode($val->name));
	                //$folder = str_replace("&-", "&", $folder);			
	                $trashFolder = explode($this->imap_delimiter,$folder);
	                $acls = ($trashFolder[count($trashFolder) - 1] == "Trash") ? $value['acls']."i" : $value['acls'];
	                $folder = imap_utf7_encode($folder);	                
	                imap_setacl ($mbox_stream, $folder, "$user", $acls);
	            }
	        }
	        if(isset($old_users[$user]))
	        	unset($old_users[$user]);
		}

		foreach ($old_users as $user => $value)
		{
	        if (is_array($mailboxes_list))
	        {
	            foreach ($mailboxes_list as $key => $val)
	            {
	                $folder = str_replace($serverString, "", imap_utf7_decode($val->name));
	                //$folder = str_replace("&-", "&", $folder);
					$folder = imap_utf7_encode($folder);
	                imap_setacl ($mbox_stream, $folder, "$user", "");

	            }
	        }
		}
		

		return true;
	}


	function getacltouser($user, $decode = false)
	{
		$return = array();
		$mbox_stream = $this->open_mbox('INBOX');
		
		if( $decode )
		    $user = mb_convert_encoding($user, 'UTF7-IMAP','UTF-8, ISO-8859-1, UTF7-IMAP');
		
		//Alterado, antes era 'imap_getacl($mbox_stream, 'user'.$this->imap_delimiter.$user);
		//Afim de tratar as pastas compartilhadas, verificandos as permissoes de operacao sobre as mesmas
		//No caso de se tratar da caixa do proprio usuario logado, utiliza a sintaxe abaixo
		if(substr($user,0,5) != 'user'.$this->imap_delimiter)
			$mbox_acl = imap_getacl($mbox_stream, 'user'.$this->imap_delimiter.$user);
		else
		  	$mbox_acl = imap_getacl($mbox_stream, $user);
		
		return (isset($mbox_acl[$this->username])) ? $mbox_acl[$this->username] : '';
               
	}

	function download_attachment($msg,$msgno)
	{
		$array_parts_attachments = array();
		//$array_parts_attachments['names'] = '';
		include_once("class.imap_attachment.inc.php");
		$imap_attachment = new imap_attachment();

		if (count($msg->fname[$msgno]) > 0)
		{
			$i = 0;
			foreach ($msg->fname[$msgno] as $index=>$fname)
			{
				$array_parts_attachments[$i]['pid'] = $msg->pid[$msgno][$index];
				$array_parts_attachments[$i]['name'] = $imap_attachment->flat_mime_decode($this->decode_string($fname));
				$array_parts_attachments[$i]['name'] = $array_parts_attachments[$i]['name'] ? $array_parts_attachments[$i]['name'] : "attachment.bin";
				$array_parts_attachments[$i]['encoding'] = $msg->encoding[$msgno][$index];
				//$array_parts_attachments['names'] .= $array_parts_attachments[$i]['name'] . ', ';
				$array_parts_attachments[$i]['fsize'] = $msg->fsize[$msgno][$index];
				++$i;
			}
		}
		//$array_parts_attachments['names'] = substr($array_parts_attachments['names'],0,(strlen($array_parts_attachments['names']) - 2));
		return $array_parts_attachments;
	}

	
	/**
	* @license   http://www.gnu.org/copyleft/gpl.html GPL
	* @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param     $params
	*/
	function spam($params)
	{
		
		$mbox_stream = $this->open_mbox($params['folder']);
		$msgs_number = explode(',',$params['msgs_number']);

                $user = Array();

                if(substr($params['folder'], 0, 4) == 'user')
                {
                    $ldapObject = new ldap_functions();

                    $folderArray = Array();
                    $folderArray = explode($this->imap_delimiter, $params['folder']);

                    $user['name'] = $folderArray[1];
                    $user['email'] = $ldapObject->getMailByUid($user['name']);
                
                }
                else
                {
                    $user['name'] = $this->username;
                    $user['email'] = $_SESSION['phpgw_info']['expressomail']['user']['email'];
                }

		foreach($msgs_number as $msg_number)
                {
			$imap_msg_number = imap_msgno($mbox_stream, $msg_number);
			$header = imap_fetchheader($mbox_stream, $imap_msg_number);
			$body = imap_body($mbox_stream, $imap_msg_number);
			$msg = $header . $body;
			strtok($user['email'], '@');
			$domain = strtok('@');

           

			//Encontrar a assinatura do dspam no cabecalho
			$v = explode("\r\n", $header);
			foreach ($v as $linha){
				if (preg_match('/^Message-ID/i', $linha)) {
					$args = explode(" ", $linha);
					$msg_id = "'$args[1]'";
				} elseif (preg_match('/^X-DSPAM-Signature/i', $linha)) {
					$args = explode(" ",$linha);
					$signature = $args[1];
				}
			}

			// Seleciona qual comando a ser executado
			switch($params['spam']){
				case 'true':  $cmd = $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_spam']; break;
				case 'false': $cmd = $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_ham']; break;
			}

                      
			$tags = array('##EMAIL##', '##USERNAME##', '##DOMAIN##', '##SIGNATURE##', '##MSGID##');
			$cmd = str_replace($tags, array($user['email'], $user['name'], $domain, $signature, $msg_id), $cmd);
                        
 			system($cmd);
		}

		imap_close($mbox_stream);
		return false;
	}
	
	
/**
* Descrição do método
*
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     
* @sponsor    Caixa Econômica Federal
* @author     
* @param      <tipo> <$msg_number> <Número da mensagem>
* @return     <cabeçalho da mensagem>
* @access     <public>
*/	
	function get_header($msg_number)
	{
                $header = @imap_headerinfo($this->mbox, imap_msgno($this->mbox, $msg_number), 80, 255);
		if (!is_object($header))
			return false;

		if($header->Flagged != "F" ) {
			$flag = preg_match('/importance *: *(.*)\r/i',
						imap_fetchheader($this->mbox, imap_msgno($this->mbox, $msg_number))
						,$importance);
			$header->Flagged = $flag==0?false:strtolower($importance[1])=="high"?"F":false;
		}

		return $header;
	}

//Por Bruno Costa(bruno.vieira-costa@serpro.gov.br - Insere emails no imap a partir do fonte do mesmo. Se o argumento timestamp for passado ele utiliza do script python
///expressoMail/imap.py para inserir uma msg com o horário correto pois isso não é porssível com a função imap_append do php.


    function insert_email($source,$folder,$timestamp,$flags){

        $username = $_SESSION['phpgw_info']['expressomail']['user']['userid'];
        $password = $_SESSION['phpgw_info']['expressomail']['user']['passwd'];
        $imap_server = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
        $imap_port 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapPort'];
        $imap_options = '/notls/novalidate-cert';

        $folder = mb_convert_encoding( $folder, "UTF7-IMAP","ISO-8859-1");
        $mbox_stream = imap_open("{".$imap_server.":".$imap_port.$imap_options."}".$folder, $username, $password);
        
        if(imap_last_error() === 'Mailbox already exists')
            imap_createmailbox($mbox_stream,imap_utf7_encode("{".$imap_server."}".$folder));

        if($timestamp){
            if(version_compare(PHP_VERSION, '5.3.2', '>=')){
                $return['append'] = imap_append($mbox_stream, "{".$imap_server.":".$imap_port."}".mb_convert_encoding($folder, "UTF7-IMAP","ISO_8859-1"), $source,'',date('d-M-Y H:i:s O',$timestamp));
            }else{
				$pdate = date_parse(date('r')); // pega a data atual do servidor (TODO: pegar a data da mensagem local) 
				$timestamp += $pdate['zone']*(60); //converte a data da mensagem para o fuso horário GMT 0. Isto é feito devido ao Expresso Mail armazenar a data no fuso horário GMT 0 e para exibi-la converte ela para o fuso horário local. 
				/* TODO: o diretorio /tmp deve ser substituido pelo diretorio temporario configurado no setup */ 
				$file = "/tmp/sess_".$_SESSION[ 'phpgw_session' ][ 'session_id' ]; 
			
	    		$f = fopen($file,"w");
	        	fputs($f,base64_encode($source));
	            fclose($f);
	            $command = "python ".dirname(__FILE__)."/../imap.py \"$imap_server\" \"$imap_port\" \"$username\" \"$password\" \"$timestamp\" \"$folder\" \"$file\"";
	            $return['command']= exec($command);
	        }
        }else{
            $return['append'] = imap_append($mbox_stream, "{".$imap_server.":".$imap_port."}".$folder, $source, "\\Seen");
        }

        if (!empty($return['command']))
        {
            list ($result, $msg) = explode(':',$return['command']);
            if (strtoupper($result) === 'NO')
            {
                $return['error'] = $msg;
                return $return;
            }
        }

        $status = imap_status($mbox_stream, "{".$this->imap_server.":".$this->imap_port."}".$folder, SA_UIDNEXT);
			
        $return['msg_no'] = $status->uidnext - 1;
		
        $return['error'] = '';
		if(imap_last_error() && imap_last_error() != "SECURITY PROBLEM: insecure server advertised AUTH=PLAIN")
            $return['error'] = imap_last_error();

		if(!$return['error'] && $flags != '' ){
			$flags_array=explode(':',$flags);
			//"Answered","Draft","Flagged","Unseen"
			$flags_fixed = "";
			if($flags_array[0] == 'A')
			    $flags_fixed.="\\Answered ";
			if($flags_array[1] == 'X')
			    $flags_fixed.="\\Draft ";
			if($flags_array[2] == 'F')
			    $flags_fixed.="\\Flagged ";
			if($flags_array[3] != 'U')
			    $flags_fixed.="\\Seen ";
			if($flags_array[4] == 'F')
			    $flags_fixed.="\\Answered \\Draft ";
			imap_setflag_full($mbox_stream, $return['msg_no'], $flags_fixed, ST_UID);
		}
	
        //Ignorando erro de AUTH=Plain
        if($return['error'] === 'SECURITY PROBLEM: insecure server advertised AUTH=PLAIN')
            $return['error'] = false;
                                
        if($mbox_stream)
            imap_close($mbox_stream);
        return $return;
    }

        function show_decript($params,$dec=0){ 
        $source = $params['source'];
	 
        if ($dec == 0) 
        { 
            $source = str_replace(" ", "+", $source,$i); 
 		        if (version_compare(PHP_VERSION, '5.2.0', '>=')){ 
 		            if(!$source = base64_decode($source,true)) 
                    return "error ".$source."Espaï¿?os ".$i; 
 		 
 		        } 
 		        else { 
 		            if(!$source = base64_decode($source)) 
                    return "error ".$source."Espaï¿?os ".$i; 
            } 
        }

        $insert = $this->insert_email($source,'INBOX'.$this->imap_delimiter.'decifradas');

		$get['msg_number'] = $insert['msg_no'];
		$get['msg_folder'] = 'INBOX'.$this->imap_delimiter.'decifradas';
		$return = $this->get_info_msg($get);
		$get['msg_number'] = $params['ID'];
		$get['msg_folder'] = $params['folder'];
		$tmp = $this->get_info_msg($get);
		if(!$tmp['status_get_msg_info'])
		{
			$return['msg_day']=$tmp['msg_day'];
			$return['msg_hour']=$tmp['msg_hour'];
			$return['fulldate']=$tmp['fulldate'];
			$return['smalldate']=$tmp['smalldate'];
		}
		else
		{
			$return['msg_day']='';
			$return['msg_hour']='';
			$return['fulldate']='';
			$return['smalldate']='';
		}
        $return['msg_no'] =$insert['msg_no'];
        $return['error'] = $insert['error'];
        $return['folder'] = $params['folder'];
        //$return['acls'] = $insert['acls'];
        $return['original_ID'] =  $params['ID'];

        return $return;

    }

//Por Bruno Costa(bruno.vieira-costa@serpro.gov.br - Trata fontes de emails enviados via POST para o servidor por um xmlhttprequest, as partes codificados com
//Base64 os "+" são substituidos por " " no envio e essa função arruma esse efeito.

    function treat_base64_from_post($source){
    	$source = preg_replace('/(?<!\r)\n/', "\r\n", $source);

        $offset = 0;
        do
        {
            if($inicio = strpos($source, 'Content-Transfer-Encoding: base64', $offset))
            {
                    $inicio = strpos($source, "\n\r", $inicio);
                    $fim = strpos($source, '--', $inicio);
                    if(!$fim)
                            $fim = strpos($source,"\n\r", $inicio);
                    $length = $fim-$inicio;
                    $parte = substr( $source,$inicio,$length-1);
                    $parte = str_replace(" ", "+", $parte);
                    $source = substr_replace($source, $parte, $inicio, $length-1);
            }
            if($offset > $inicio)
            $offset=FALSE;
            else
            $offset = $inicio;
        }
        while($offset);
        return $source;
    }

	//Por Bruno Costa(bruno.vieira-costa@serpro.gov.br - Recebe os fontes dos emails a serem desarquivados, separa e envia cada um para função insert_mail.

    function unarchive_mail($params)
    {	
        $dest_folder = urldecode($params['folder']);
        $sources = explode("#@#@#@",$params['source']);
        $timestamps = explode("#@#@#@",$params['timestamp']);
        $flags = explode("#@#@#@",$params['flags']);
        $ids = explode("#@#@#@",$params['id']);
        $return = array();
        $archived = array();
        $error = array();

        foreach($sources as $index=>$src)
        {
            if($src!="")
            {
                //If it is a MailArchiver incomming data
                if($params['madata'])
                    $sourcedec = utf8_decode($src);
                //Default data
                else
                    $sourcedec = $src;

		 		$source = $this->treat_base64_from_post($sourcedec);

				$insert = $this->insert_email($source,$dest_folder,$timestamps[$index],$flags[$index]);

                $return['idsMsg'] = $insert['msg_no'];
                if($insert['error'])
                {
                    $error[] = $ids[$index];
                }
                else 
                {
                    $archived[] = $ids[$index];
                }
            }else{
                $error[] = $ids[$index];
            }
		}
        
        if (!empty($error))
        {
            $return['error'] = $error;
        }
        if (!empty($archived))
        {
            $return['archived'] = $archived;
        }
        
        return $return;
    }

    function download_all_local_attachments($params)
    {
        $source = $params['source'];
        $source = $this->treat_base64_from_post($source);
        $insert = $this->insert_email($source,'INBOX'.$this->imap_delimiter.'decifradas');
        $exporteml = new ExportEml();
        $params['num_msg']=$insert['msg_no'];
        $params['folder']='INBOX'.$this->imap_delimiter.'decifradas';
        return $exporteml->download_all_attachments($params);
    }
	
	/** 
	 * Método que envia um email reportando um erro no email do usuário 
	 * @license http://www.gnu.org/copyleft/gpl.html GPL 
	 * @author Prognus Software Livre (http://www.prognus.com.br) 
	 */  
 	function report_mail_error($params) 
 	{        
 		$params = $params['params']; 
 		$array_params = explode(";;", $params); 
 		$id_msg   = $array_params[0]; 
 		$msg_user = $array_params[1];
 		$msg_folder = $array_params[2];
 		
 		if($msg_user == '') 
			$msg_user = "Sem mensagem!"; 
 		         
		$toname = $_SESSION['phpgw_info']['expressomail']['user']['fullname']; 
 		 
 		$exporteml = new ExportEml(); 
 		$mail_content = $exporteml->export_msg_data($id_msg, $msg_folder); 
 		$this->open_mbox($msg_folder);  
		$title = "Erro de email reportado"; 
		$body  = "<body>O usu&aacute;rio <strong>$toname</strong> reportou um erro na tentativa de acesso ao conte&uacute;do do email.<br><br>Segue em anexo o fonte da mensagem" .                           " reportada.<br><br><hr><strong><u>Mensagem do usu&aacute;rio:</strong></u><br><br><br>" . 
 		                "$msg_user</body><br><br><hr>"; 
						
 		require_once dirname(__FILE__) . '/../../services/class.servicelocator.php';
 		$mailService = ServiceLocator::getService('mail');      
 		$mailService->addStringAttachment($mail_content, 'report.eml', 'application/text'); 
 		$mailService->sendMail($_SESSION['phpgw_info']['expressomail']['server']['sugestoes_email_to'], $GLOBALS['phpgw_info']['user']['email'], $title, $body); 
	} 
	
	function array_msort($array, $cols)
	{
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		}
		$params = array();
		foreach ($cols as $col => $order) {
			$params[] =& $colarr[$col];
			$params = array_merge($params, (array)$order);
		}
		call_user_func_array('array_multisort', $params);
		$ret = array();
		$keys = array();
		$first = true;
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				if ($first) { $keys[$k] = substr($k,1); }
				$k = $keys[$k];
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
			$first = false;
		}
		
		return $ret;

	}
        
	function parseCriteriaSearchMail($search)
	{
		$criteria = '';
		$searchArray = explode(' ', $search);

		foreach ($searchArray as $v)
			if(trim($v) !== '' )
				$criteria .= 'TEXT "'.$v.'" ' ;
	   
		return $criteria;
	}
        
	function quickSearchMail( $params )
	{		
		include '../prototype/api/controller.php';			
		set_time_limit(270); //Aumenta o tempo limit da requisição, em algumas buscas o imap demora para retornar o resultado.
		$return = array();
		$return['folder'] = $params['folder'];
		if(!is_array($params['folder']))
			$params['folder'] = array( $params['folder'] );
		
		if(!isset($params['sortType']))
			$params['sortType'] = 'SORTDATE_REVERSE';
				
		$params['search'] = mb_convert_encoding($params['search'], 'UTF-8',mb_detect_encoding($params['search'].'x', 'UTF-8, ISO-8859-1'));

		$i = 0;		
		if(!isset($params['page'])) $params['page'] = 0;
		$end = ($this->prefs['max_email_per_page'] * ((int)$params['page'] + 1));	
		$ini = $end - $this->prefs['max_email_per_page'] ;
		$count = 0;
		
		if (!preg_match('/KEYWORD/i', $params['search'])){
			$search = $this->parseCriteriaSearchMail($params['search']);
		} else {
			$search = $params['search'];
		}
	
		foreach ($params['folder'] as $folder) 
		{
			$imap = $this->open_mbox( $folder ) ;
			$msgIds = imap_sort( $imap , SORTDATE , 1 , SE_UID , $search ,'UTF-8');
						
			$count += count($msgIds);  
			
			foreach ($msgIds as $ii => $v)
			{	
				$msg = imap_headerinfo ( $imap,  imap_msgno($imap, $v) );

				$return['msgs'][$i]['from'] = '';
				
				if(isset($msg->from[0]))
				{
					$from = self::formatMailObject( $msg->from[0] );
					$return['msgs'][$i]['from'] 	= mb_convert_encoding($from['name'] ? $from['name'] : $from['email'], 'UTF-8');
				}
				else
					$return['msgs'][$i]['from'] 	= ''; 
				
				$return['msgs'][$i]['subject'] = ' ';
				
				$subject = imap_mime_header_decode($msg->subject);
				foreach ($subject as $tmp)
					$return['msgs'][$i]['subject'] .= mb_convert_encoding($tmp->text, 'UTF-8', 'UTF-8 , ISO-8859-1');


                if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_followupflags_and_labels'] == "1")
                {

                    $filter = array('AND', array('=', 'folderName', $folder), array('=','messageNumber', $v));
                    $followupflagged = Controller::find(
                        array('concept' => 'followupflagged' , 'folder' => $folder ),
                        false,
                        array('filter' => $filter, 'criteria' => array('deepness' => '2'))
                    );

                    if(isset($followupflagged[0]['followupflagId']))
                    {
                        $followupflag = Controller::read( array( 'concept' => 'followupflag', 'id' => $followupflagged[0]['followupflagId'] ));
                        $followupflagged[0]['followupflag'] = $followupflag;
                        $return['msgs'][$i]['followupflagged'] = $followupflagged[0];

                    }
                    $labeleds = Controller::find(
                        array('concept' => 'labeled'),
                        false,
                        array('filter' => $filter, 'criteria' => array('deepness' => '2'))
                    );
                    if(is_array($labeleds))
                    foreach ($labeleds as $e){
                        $labels = Controller::read( array( 'concept' => 'label', 'id' =>  $e['labelId']));
                        $return['msgs'][$i]['labels'][$e['labelId']] = $labels;
                    }
                }

				$mimeBody = imap_body( $this->mbox, $v  , FT_UID|FT_PEEK  );
				$return['msgs'][$i]['flag'] = ' ';
				$return['msgs'][$i]['flag'] .= $msg->Unseen ? $msg->Unseen : '';
				$return['msgs'][$i]['flag'] .= $msg->Recent ? $msg->Recent : '';
				$return['msgs'][$i]['flag'] .= $msg->Draft ? $msg->Draft : '';	
				$return['msgs'][$i]['flag'] .= $msg->Answered ? $msg->Answered : '';	
				$return['msgs'][$i]['flag'] .= $msg->Deleted ? $msg->Deleted : '';	
				$return['msgs'][$i]['flag'] .= ( preg_match('/((Content-Disposition:(.)*([\r\n\s]*filename))|(Content-Type:(.)*([\r\n\s]*name)))/i', $mimeBody) ) ? 'T': '';
				
				$header = imap_fetchheader( $imap, $v , FT_UID ); // Necessario para recuperar se a mensagem é importante ou não.
				$importante = array();
				
				if($msg->Flagged != 'F')
					$return['msgs'][$i]['flag'] .= ( preg_match('/importance *: *(.*)\r/i', $header , $importante) === 0 ) ? '' : 'F';
				else
					$return['msgs'][$i]['flag'] .= $msg->Flagged ? $msg->Flagged : '';	
					
				$return['msgs'][$i]['udate'] = gmdate("d/m/Y",$msg->udate + $this->functions->CalculateDateOffset()); 
				$return['msgs'][$i]['udatecomp'] = substr ($return['msgs'][$i]['udate'], -4) ."-". substr ($return['msgs'][$i]['udate'], 3, 2) ."-". substr ($return['msgs'][$i]['udate'], 0, 2);
			    $return['msgs'][$i]['date'] =   $msg->udate;
				$return['msgs'][$i]['size'] =  $msg->Size;
				$return['msgs'][$i]['boxname'] = $folder;
				$return['msgs'][$i]['uid'] = $v;
				++$i;
			} 	
		}
		
		$return['num_msgs'] = $count;
		
		if(!isset($return['msgs']))
			$return['msgs'] = array();
		
		define('SORTBOX', 69);
		define('SORTWHO', 2);
		define('SORTBOX_REVERSE', 69);
		define('SORTWHO_REVERSE', 2);
		define('SORTDATE_REVERSE', 0);
		define('SORTSUBJECT_REVERSE', 3);
		define('SORTSIZE_REVERSE', 6);
		
		switch (constant( $params['sortType'] )){
			case 0 : $sA = 'date'; break;
			case 2 : $sA = 'from'; break;
			case 69 : $sA = 'boxname'; break;
			case 3 : $sA = 'subject'; break;
			case 6 : $sA = 'size'; break;
	} 
	
			
		if($params['sortType'] !== 'SORTDATE_REVERSE')
		if(strpos($params['sortType'],'REVERSE') !== false)
				$return['msgs'] = $this->array_msort($return['msgs'] , array( $sA => SORT_DESC));
			else
				$return['msgs'] = $this->array_msort($return['msgs'] , array( $sA => SORT_ASC));
		
		$k = -1;
		$nMsgs = array();
		
		foreach ($return['msgs'] as $v)
		{		
			++$k;
			if($k < $ini || $k >= $end ) continue;			
			$nMsgs[] = $v;
		}
		$return['msgs'] = $nMsgs;

		$return = json_encode($return);
		$return = base64_encode($return);
        
		return $return;
	}
	
    function get_quota_folders(){ 

	    // Additional Imap Class for not-implemented functions into PHP-IMAP extension. 
	    include_once("class.imapfp.inc.php");            
	    $imapfp = new imapfp(); 

	    if(!$imapfp->open($this->imap_server,$this->imap_port)) 
		    return $imapfp->get_error();             
	    if (!$imapfp->login( $this->username,$this->password )) 
		    return $imapfp->get_error(); 

	    $response_array = $imapfp->get_mailboxes_size(); 
	    if ($imapfp->error) 
		    return $imapfp->get_error(); 

	    $data = array(); 
	    $quota_root = $this->get_quota(array('folder_id' => "INBOX")); 
	    $data["quota_root"] = $quota_root; 

	    foreach ($response_array as $idx=>$line) { 
		    $line2 = str_replace('"', "", $line); 
		    $line2 = str_replace(" /vendor/cmu/cyrus-imapd/size (value.shared ",";",str_replace("* ANNOTATION ","",$line2)); 
		    list($folder,$size) = explode(";",$line2); 
		    $quota_used = str_replace(")","",$size); 
		    $quotaPercent = (($quota_used / 1024) / $data["quota_root"]["quota_limit"])*100; 
		    $folder = mb_convert_encoding($folder, "ISO-8859-1", "UTF7-IMAP"); 
		    if(!preg_match('/user\\'.$this->imap_delimiter.$this->username.'\\'.$this->imap_delimiter.'/i',$folder)){ 
			    $folder = $this->functions->getLang("Inbox"); 
		    } 
		    else 
			    $folder = preg_replace('/user\\'.$this->imap_delimiter.$this->username.'\\'.$this->imap_delimiter.'/i','', $folder); 

		    $data[$folder] = array("quota_percent" => sprintf("%.1f",round($quotaPercent,1)), "quota_used" => $quota_used); 
	    } 
	    $imapfp->close(); 
	    return $data; 
    }  
    
    function getaclfrombox($mail)
	{
			$mailArray = explode('@', $mail);
			$boxacl = $mailArray[0];
			$return = array();

			if(!$this->mbox)
				 $this->open_mbox();

			$mbox_acl = imap_getacl($this->mbox, 'user' . $this->imap_delimiter . $boxacl);

			foreach ($mbox_acl as $user => $acl)
			{
					if ($user != $boxacl )
						$return[$user] = $acl;
			}
			return $return;
	}
		
		
	function searchSieveRule( $params )
	{
		$imap = $this->open_mbox( $params['folder']['criteria'] ? $params['folder']['criteria'] : 'INBOX' );
		$msgs = imap_sort( $imap , SORTDATE , 0 , SE_UID);
	
		$rr = array();

        if(isset($params['from']))  $rr['from'] = array();
        if(isset($params['to']))  $rr['to'] = array();
        if(isset($params['subject'])) $rr['subject'] = array();
        if(isset($params['body'])) $rr['body'] = array();
        if(isset($params['size'])) $rr['size'] = array();

        //$params['search'] = mb_convert_encoding($params['search'], 'UTF-8',mb_detect_encoding($params['search'].'x', 'UTF-8, ISO-8859-1'));

		foreach ($msgs as $i => $v)
		{
			
			$msg = imap_headerinfo ( $imap,   imap_msgno($imap, $v)  );	
			
			if(isset($params['from']))
			{
				$from['from'] = array();
				$from['from']['name'] = $this->decode_string($msg->from[0]->personal);
				$from['from']['email'] = $this->decode_string($msg->from[0]->mailbox . "@" . $msg->from[0]->host);
				if ($from['from']['name'])
				{
					if (substr($from['from']['name'], 0, 1) == '"')
						$from['from']['full'] = $from['from']['name'] . ' ' . '<' . $from['from']['email'] . '>';
					else
						$from['from']['full'] = '"' . $from['from']['name'] . '" ' . '<' . $from['from']['email'] . '>';
				}
				else
					$from['from']['full'] = $from['from']['email'];

				if($this->filterCheck( $from['from']['full'] , $params['from']['criteria'] , $params['from']['filter'] ))
					$rr['from'][] = $v;
			}
			
			if(isset($params['to']))
			{
				$tos = $msg->to;
				$val = '';
				foreach( $tos as $to)
				{
					$tmp = imap_mime_header_decode($to->personal);
					$val .= '"' . $tmp[0]->text . '" ' . '<' .  $to->mailbox . "@" . $to->host . '>';
					
				}				
				if($this->filterCheck( $val , $params['to']['criteria'] , $params['to']['filter'] ))
					$rr['to'][] = $v;
				
				$tos = $msg->cc;
				$val = '';
				foreach( $tos as $to)
				{
					$tmp = imap_mime_header_decode($to->personal);
					$val .= '"' . $tmp[0]->text . '" ' . '<' .  $to->mailbox . "@" . $to->host . '>';
					
				}

				if($this->filterCheck( $val , $params['to']['criteria'] , $params['to']['filter'] ))
					$rr['to'][] = $v;
			}
			
			if(isset($params['subject']))
			{		
				$ss = '';
				$subject = imap_mime_header_decode($msg->subject);
				foreach ($subject as $tmp)
					$ss .= ($tmp->charset == "default") ? $tmp->text : utf8_encode($tmp->text);
				
				if($this->filterCheck($ss , $params['subject']['criteria'] , $params['subject']['filter'] ))
				$rr['subject'][] = $v;
			}
			
			if(isset($params['body']))
			{			
				$this->mbox = $this->open_mbox( $params['folder']['criteria'] ? $params['folder']['criteria'] : 'INBOX' );
				$b = $this->get_body_msg( $v , $params['folder']['criteria'] ? $params['folder']['criteria'] : 'INBOX' );

				if( $this->filterCheck( mb_convert_encoding(html_entity_decode($b['body']), 'UTF-8',mb_detect_encoding(html_entity_decode($b['body']).'x', 'UTF-8, ISO-8859-1')) , $params['body']['criteria'] , $params['body']['filter'] ))
					$rr['body'][] = $v;

				unset($b);
			}
			
			if(isset($params['size']))
			{
				if( $this->filterCheck( $msg->Size , $params['size']['criteria'] , $params['size']['filter'] ))
					$rr['size'][] = $v;
			}
		}
		
		$rrr = array();
		$init = true;
		foreach ($rr as $i => $v)
		{
			if(count($rrr) == 0 && $init === true)
				$rrr = $v;
			else if($params['isExact'] == 'yes')
                $rrr = array_intersect($rrr , $v);
			else
				$rrr =  array_unique(array_merge($rrr , $v));
		}

//		if($params['page'] && $params['rows'])
//		{
//		
//			$end = ( $params['rows'] * $params['page'] );	
//			$ini = $end -  $params['rows'] ;
//		
//			//Pegando os do range da paginação			
//			$k = -1;
//			$r = array();
//			foreach ($rrr as $v)
//			{		
//				++$k;
//				if( $k < $ini || $k >= $end ) continue;			
//				$r[] = $v;
//			}
//			//////////////////////////////////////
//		}
//		else
			$r = $rrr;		
					
		return $r ;
	}
	
	function filterCheck( $val , $crit ,$fil )
	{		
		switch ( $fil ) {
			case '=' : //Igual
				if( $val == $crit ) return true; else return false;	break;
			case '*' : //Existe
				if( strpos( $val , $crit ) !== false ) return true; else return false; break;
			case '!*' : //Não existe
				if( strpos( $val , $crit ) === false ) return true; else return false; break;
			case '^' : //Começa com
				if( substr ($val , 0 , strlen($crit) ) == $crit ) return true; else return false; break;	
			case '$' : //Termina com
				if( substr ($val , 0 , -(strlen($crit)) ) == $crit ) return true; else return false; break;	
			case '>' : //Maior que
				if( $val  > (int)($crit * 1024) ) return true; else return false; break;	
			case '<' : //Menor que
				if( $val  < (int)($crit * 1024) ) return true; else return false; break;	
		}
	}
	
	
	
	/**
	* Método que aplica a ação do filtro nas mensagens da caixa de entrada
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <Array> <$msgs> <Mensagens da caixa de entrada>
	* @param      <Array> <$proc> <ações do filtro>
	* @return     <Regras do usuário em Array>
	* @access public
	*/
	function apliSieveFilter($msgs , $proc)
	{
		$ret = array();
		foreach ($msgs as $i => $msg)
		{
			switch($proc['type']){
				case 'fileinto':
					$imap = $this->open_mbox( 'INBOX' );
					if($proc['keep'] === true)
						$ret[$msg][] = imap_mail_copy($imap,$msg,$proc['value'], CP_UID);
					else
					{
						/* Está sempre copiando a mensagem para a pasta destino */
					    //$ret[$msg][] = imap_mail_move($imap,$msg,$proc['parameter'], CP_UID);
						$ret[$msg][] = imap_mail_move($imap,$msg,$proc['parameter'], CP_UID);						
						imap_expunge($imap);
					}
					break;
				case 'redirect':											
					foreach($msgs as $msg)
					{				
						$info = $this->get_info_msg(array('msg_folder' => 'INBOX','msg_number' => $msg));
						Controller::create( array( 'service' => 'SMTP' ), array( 'body' => $info['body'],
																			  'isHtml' => true,
																			  'subject' => $info['subject'],
																			  'from' => $info['from']['full'],
																			  'to' => $proc['parameter'])
										);
						
						if($proc['keep'] !== true)
							$this->delete_msgs(array('msgs_number' => $msg , 'folder' => 'INBOX'));
					}	
					break;
				
				case 'setflag':
					foreach($msgs as $msg)
						$ret[$msg][] = $this->set_messages_flag( array( 'folder' => 'INBOX' , 'msgs_to_set' => $msg , 'flag' => $proc['parameter']) );
					break;
			}
		}
		return $ret;
	}

   /**
    * Método que convert imagens no formato rfc2397 para Embedded Attachment
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
    * @sponsor     Caixa Econômica Federal
    * @author     Cristiano Corrêa Schmidt
    * @param      <MailService> <$mailService> <Referencia objeto MailService>
    * @param      <String> <$body> <Referencia Corpo do email>
    * @return     <void>
    * @access     public
    */
   function rfc2397ToEmbeddedAttachment( &$mailService , &$body )
   { 
       $matches = array();
       preg_match_all("/src=[\'|\"]+data:([^,]*);base64,([a-zA-Z0-9\+\/\=]+)[\'|\"]+/i", $body, $matches,  PREG_SET_ORDER); //Resgata imagens em rfc2397       
       
       foreach ($matches as $i => &$v)
       {
            $ext = explode(';', $v[1]); //quebra todos os parametros em um array.
            $name = 'EmbeddedImage' . substr(str_shuffle(md5(time())), 0, 7) . '.' . $this->mimeToExtension($v[1]);
            $mailService->addStringImage(base64_decode($v[2]), $ext[0], $name );
            $body = str_replace($v[0], 'src="' . $name . '"', $body);
       }
   }

   /**
    * Método que retorna a extensão do arquivo atraves do mime type
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
    * @sponsor     Caixa Econômica Federal
    * @author     Cristiano Corrêa Schmidt
    * @param      <String> <$mimeType> <Mime Type do arquivo>
    * @return     <String> <extensão>
    * @access     public
    */
   function mimeToExtension($mimeType)
   {
       switch ( $mimeType ) 
       {   
           case 'image/bmp' : 
           return 'bmp';
           case 'image/cgm' :
               return 'cgm';
           case 'image/vnd.djvu' : 
               return 'djv';
           case 'image/gif' :
               return 'gif';
           case 'image/x-icon' :
               return 'ico';
           case 'image/ief' :
               return 'ief';
           case 'image/jpeg' :
               return 'jpg';
           case 'image/x-macpaint' :
               return 'mac';
           case 'image/pict' :
               return 'pct';
           case 'image/png' :
               return 'png';
           case 'image/x-quicktime' :
               return 'qti';
           case 'image/x-rgb' :
               return 'rgb';
           case 'image/tiff' :
               return 'tif';
           default:
               return '';
       }
       
   }
	
	
	/**
	* Método que retorna as mensagens com a flag $FilteredMessage que representa as mensagens filtradas que devem ser alertadas para o usuário
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Marcieli <marcieli@prognus.com.br>	
	* @author	  Marcos <marcosw@prognus.com.br>	
	* @param      <Array> <$paramFolders> <Pastas onde devem ser buscadas as mensagens>
	* @return     <Mensagens encontradas com a flag $FilteredMessage>
	* @access     <public>
	*/
	function getFlaggedAlertMessages($paramFolders) {
		
		$folders = explode(",", $paramFolders['folders']);
	
		$messages = array();
		$result   = array();
		$label    = '$FilteredMessage';
		
		foreach ($folders as $folder) {
			$this->mbox = $this->open_mbox($folder);
			/* Não deletadas, não lidas e com a flag */
			$messages = imap_search($this->mbox, 'UNDELETED UNSEEN KEYWORD "$FilteredMessage"', SE_UID);
			if(is_array($messages))
				foreach ($messages as $k => $m) {
					$headers = imap_fetch_overview($this->mbox, $m, FT_UID);

					$date = explode(" ", $headers[0]->date);
					$result[$m."_".$folder] = array (
						'udate'      => $headers[0]->udate,
						'from'       => $this->decodeMimeString($headers[0]->from),
						'subject'    =>	$this->decodeMimeString($headers[0]->subject),
						'msg_number' => $m,
						'msg_folder' => $folder
					);
				}
		}
		$result_final = array();
		foreach ($result as $r){
			$result_final[] = $r;
		}

		return $result_final;
	}
	
	/**
	* Esta função é chamada ao clicar sobre uma mensagem listada nos alertas de Filtro por Remetente
	* remove a flag e chama a função que recupera os dados da mensagem, para serem utilizados na abertura da aba de leitura da msg
	*/
	function open_flagged_msg($params){
		$message_number = $params['msg_number'];
		$message_folder = $params['msg_folder'];
		$alarm = $params['alarm'];
		if ($message_folder && $message_number) {
			$this->mbox = $this->open_mbox($message_folder);
			imap_clearflag_full($this->mbox, $message_number, '$FilteredMessage', ST_UID);
		}
		$r = $this->get_info_msg(array('msg_number' => $message_number, 'msg_folder' =>urlencode($message_folder), 'alarm' => ($alarm)));

		return $r;
	}
	
	/**
	* Remove a flag que caracteriza uma mensagem como alertada por Filtro por Remetente.
	* se houver o parametro msg_number, então remove a flag de uma msg especifica
	* se não houver o parametro msg_number, mas sim o from, então remove a flag de todas as msgs da pasta (parametro from), 
	* e que o remetente for o from.
	*/
	function removeFlagMessagesFilter($params){
		$message_number = $params['msg_number'];
		$folder = $params['folder'];

		if(isset($message_number)){
			if(isset($folder)){
				$message_number = explode(',', $message_number);
				$this->mbox = $this->open_mbox($folder);
				foreach ($message_number as $k => $m) {			
						imap_clearflag_full($this->mbox, $m, '$FilteredMessage', ST_UID);
					}
			}
		}
		else{
			$from = $params['from'];
			if(isset($folder) && isset($from)){
				$this->mbox = $this->open_mbox($folder);
				$messages = imap_search($this->mbox, 'UNDELETED UNSEEN KEYWORD "$FilteredMessage"', SE_UID);
			}
			if(is_array($messages)){
				foreach ($messages as $k => $m) {
					$headers = imap_fetch_overview($this->mbox, $m, FT_UID);
					if(strpos($headers[0]->from, $from) > 0){
						imap_clearflag_full($this->mbox, $m, '$FilteredMessage', ST_UID);
					}
				}
			}
		}
		
		return array('status' => "success"); 
	}

	//MailArchiver -> get offsettogmt as a global javascript variable, invoked at "main.js", init()
    function get_offset_gmt(){
        return($this->functions->CalculateDateOffset());
    }

    //MailArchiver -> get message flags only, invoked at archive operation
    function get_msg_flags($args){  
        $msg_folder = $args['folder'];
        $msg_n = $args['msg_number'];
        $arr_msg = explode(",", $msg_n);
       
        for($i=0; $i<count($arr_msg); ++$i){
                        
            if(!$this->mbox || !is_resource($this->mbox))
                $this->mbox = $this->open_mbox($msg_folder);
        
            if(!is_resource($this->mbox))
                return(false);
                       
            $msgno_imap = imap_msgno($this->mbox, $msg_n);          
        	$header = @imap_headerinfo($this->mbox, $msgno_imap, 80, 255);
                
            if (!is_object($header))
                return false;

            $taglist[$i]["msgid"] = $msg_n;
            $taglist[$i]["unseen"] = $header->Unseen;
            $taglist[$i]["recent"] = $header->Recent;
            $taglist[$i]["flagged"] = $header->Flagged;
            $taglist[$i]["draft"] = $header->Draft;
            $taglist[$i]["answered"] = $header->Answered;
            $taglist[$i]["deleted"] = $header->Deleted;
        
            if($header->Answered =='A' && $header->Draft == 'X')
                $taglist[$i]['forwarded'] = 'F';
            else
                $taglist[$i]['forwarded'] = ' ';
        }

		return $taglist;        
    }    
}
?>
