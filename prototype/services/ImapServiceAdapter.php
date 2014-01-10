<?php
/**
 *
 * Copyright (C) 2012 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY FUNAMBOL, FUNAMBOL DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT  OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program; if not, see www.gnu.org/licenses or write to
 * the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301 USA.
 *
 * This code is based on the OpenXchange Connector and on the Prognus pSync
 * Connector both developed by the community and licensed under the GPL
 * version 2 or above as published by the Free Software Foundation.
 *
 * You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
 * 6731, PTI, Edifício do Saber, 3º floor, room 306, Foz do Iguaçu - PR - Brasil or at
 * e-mail address prognus@prognus.com.br.
 *
 * Classe de abstração que faz uma adaptação para manipulação de informações
 * no IMAP a partir de vários métodos.
 *
 * @package    Prototype
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 * @version    2.4
 * @sponsor    Caixa Econômica Federal
 * @since      Arquivo disponibilizado na versão 2.4
 */

include_once ROOTPATH."/../expressoMail/inc/class.imap_functions.inc.php";

use prototype\api\Config as Config;

/**
 *
 * @package    Prototype (Mail)
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 * @version    2.4
 * @sponsor    Caixa Econômica Federal
 * @since      Classe disponibilizada na versão 2.4
 */
class ImapServiceAdapter extends imap_functions/* implements Service*/
{
    public function open( $config )
    {
		$this->init();
    }

//     public function connect( $config )
//     {
// 			$this->init();
//     }
	
    public function find( $URI, $justthese = false, $criteria = false )
	{ 

		$context = isset($justthese['context']) ? $justthese['context'] : '' ;

		switch( $URI['concept'] )
		{
			case 'folder':
			{
				$result = $this->getFolders();

				foreach ($result as $res) {

					//monta o array padrao
					$array = array(
							'id' => mb_convert_encoding( $res['folder_id'], 'UTF-8', 'UTF7-IMAP' ),
							'commonName' => mb_convert_encoding( $res['folder_name'], 'UTF-8' , 'UTF7-IMAP' ),
							'parentFolder' => mb_convert_encoding( $res['folder_parent'], 'UTF-8' , 'UTF7-IMAP' ),
							'messageCount' => array('unseen' => isset($res['folder_unseen']) ? $res['folder_unseen'] : null, 'total' => null)
					);

					//se existir compartilhamento para pasta compartilhada
					//adicionar array de permissoes
					if(isset($res['acl_share'])){
						$array['acl_share'] = $res['acl_share'];
					}
					$response[] = $array;
				}
				return $response;
			}
			case 'message':
			{
				//begin: for grid	
				$page  = isset($criteria['page']) ? $criteria['page'] : 1 ; //{1}    get the requested page
				$limit = isset($criteria['rows']) ? $criteria['rows'] : 10 ; //{10}   get how many rows we want to have into the grid
				$sidx  = isset($criteria['sidx']) ? $criteria['sidx'] : 0; //{id}   get index row - i.e. user click to sort
				$sord  = isset($criteria['sord']) ? $criteria['sord'] : ''; //{desc} get the direction

				$filter =  isset($criteria['filter']) ? $criteria['filter'] : '';

				if( !$sidx ) $sidx = 1;

				$folder_name =  isset($URI['folder']) ?  $URI['folder'] : str_replace( '.', $this->imap_delimiter, isset($context['folder']) ?  $context['folder'] : 'INBOX');
			
				$count = imap_num_msg( $this->open_mbox( $folder_name ) );

				$total_pages = $count > 0 ? ceil( $count/$limit ) : 0;

				if( $page > $total_pages )
					$page = $total_pages;

				$start = $limit * $page - $limit;
				// do not put $limit*($page - 1)
				//end: for grid
				
				
				/**
				 * Trata o caso específico de retorno do atributo messageId
				 *
				 * TODO - refazer todo a operação find do conceito message, uma vez que esta 
				 * foi desenvolvida quando a nova API ainda era muito imatura e se encontra 
				 * muito acoplada à estrutura de retorno esperada pelo plugin jqGrid
				 */
				if ( $justthese ) 
				{
					if (isset($justthese[0]) && $justthese[0] == 'messageId') {
						$map = array(
							'folderName' => array(),
							'messageNumber' => array()
						);
						
						self::parseFilter($criteria["filter"], $map);
						
						if (count($map['folderName']) == 0) {
							$folders = $this->get_folders_list();
							foreach ($folders as $folder)
								if (isset($folder['folder_id'])) 
									$map['folderName'][] = $folder['folder_id'];
						}
						
						$result = array();
						foreach ($map['folderName'] as $folder) {
							$this->mbox = $this->open_mbox($folder);

							/**
							 * Se não foi passado messageNumber no filtro, 
							 * busca todas as mensagens de cada pasta
							 */
							$messages = empty($map['messageNumber']) ? '1:*' : implode(',', $map['messageNumber']);
							$sequenceType = empty($map['messageNumber']) ? 0 : FT_UID;

							$headers = imap_fetch_overview($this->mbox, $messages, $sequenceType);
							foreach ($headers as $h) {
								if(isset($h->message_id ))
									$result[] = array ( 'messageId' => $h->message_id );
							}
	
						}
						return $result;
					}
				}
				
				if( $filter )
				{
					if( $filter[0] !== 'msgNumber' )
					{
                        for( $i = 0; $i < count($filter); ++$i )
                        {
                            if( count( $filter[$i] ) === 4 )
                                array_shift( $filter[$i] ) ;

                            $criteria[ $filter[$i][0] ] = array( 'criteria' => $filter[$i][2], 'filter' => $filter[$i][1] );
                        }
                        return $this->searchSieveRule($criteria);
					}

					$msgNumber = array();

					for( $i = $start; $i < $start + $limit && isset( $filter[2][$i] ); ++$i )
					  $msgNumber[] = $filter[2][$i];

					if( empty( $msgNumber ) )
					    return( false );

					$result = $this->get_info_msgs( array( 'folder' => $folder_name, 
									   'msgs_number' => implode( ',', $msgNumber ) ) );

					foreach( $result as $i => $val )
					$result[$i] = unserialize( $val );

				}
				else
				{
					$result = $this->get_range_msgs2( 
						array( 
							'folder' => $folder_name, //INBOX
							'msg_range_begin' => $start + 1, //??
							'msg_range_end' => $start + $limit, //$limit = $_GET['rows']; // get how many rows we want to have into the grid
							'sort_box_type' => 'SORTARRIVAL', 
							'search_box_type' => 'ALL',
							'sort_box_reverse' => 1 
						) 
					);
				}
				//return var_export($result);

				if($filter){
					$total_pages = count($filter[2]) > 0 ? ceil( count($filter[2])/$limit ) : 0;
					$response = array( "page" => $page, "total" => $total_pages, "records" =>  count($filter[2]) );	
				}else{
					$response = array( "page" => $page, "total" =>  $total_pages, "records" =>  $count );	
				}

				for ($i=0; $i<count($result); ++$i)
				{
					$flags_enum = array('Unseen'=> 1,  'Answered'=> 1, 'Forwarded'=> 1, 'Flagged'=> 1, 'Recent'=> 1, 'Draft'=> 1 ); 

					foreach ($flags_enum as $key => $flag)
					{
						if ( !isset($result[$i][$key]) || !trim($result[$i][$key]) || trim($result[$i][$key]) == '')  
							$flags_enum[$key] = 0; 

						unset($result[$i][$flag]);
					}

					if (array_key_exists($i, $result))
					{
						$response["rows"][$i] = $result[$i];
						$response["rows"][$i]['timestamp'] = $result[$i]['udate'] * 1000;
						$response["rows"][$i]['flags'] = implode(',', $flags_enum);
						$response["rows"][$i]['size'] = $response["rows"][$i]['Size'];
						$response["rows"][$i]['folder'] = $folder_name; 
						//$response["rows"][$i]['udate'] = ( $result[$i]['udate'] + $this->functions->CalculateDateOffset()  * 1000 );
						unset($response["rows"][$i]['Size']);
					}
				 }

				return $this->to_utf8($response);
			}
			
			/**
			 * Filtros suportados:
			 * - ['=', 'folderName', $X]
			 * - [
			 * 		'AND',
			 * 		[
			 * 			'AND',
			 * 			['=', 'folderName', $X],
			 * 			['IN', 'messageNumber', $Ys]
			 * 		],
			 * 		['IN', 'labelId', $Zs]
			 * ]
			 * - ['=', 'labelId', $X]
			 * - [
			 * 		'AND',
			 * 		['=', 'folderName', $X],
			 * 		['=', 'labelId', $Y]
			 * ]
			 * - ['IN', 'labelId', $Ys]
			 * - [
			 * 		'AND',
			 * 		['=', 'folderName', $X],
			 * 		['IN', 'labelId', $Ys]
			 * ]			
			 */
			case 'labeled':
			{
				$result = array ( );
				if (isset($criteria["filter"]) && is_array($criteria['filter'])) {
					//TODO - melhorar o tratamento do filter com a lista de todos os labelIds dado pelo interceptor
					$map = array(
						'id' => array(),
						'folderName' => array(),
						'messageNumber' => array(),
						'labelId' => array()
					);
					
					self::parseFilter($criteria["filter"], $map);
					
					if (count($map['folderName']) == 0) {
						$folders = $this->get_folders_list();
						foreach ($folders as $folder)
							if (isset($folder['folder_id'])) 
								$map['folderName'][] = $folder['folder_id'];
					}

					foreach ($map['folderName'] as $folder) {
						$this->mbox = $this->open_mbox($folder);
						
						foreach ($map['labelId'] as $label) {
							$messagesLabeleds = imap_search($this->mbox, 'UNDELETED KEYWORD "$Label'.$label.'"', SE_UID);
							
							if(is_array($messagesLabeleds))
							foreach ($messagesLabeleds as $messageLabeled) {
								if (count($map['messageNumber']) > 0 && !in_array($messageLabeled, $map['messageNumber']))
									continue;
									
								$result[] = array (
									'id' => $folder . '/' . $messageLabeled . '#' . $label, 
									'folderName' => $folder,
									'messageNumber' => $messageLabeled,
									'labelId' => $label
								);
							}
						}

					}
				}
				
				return $result;
			}
			
			case 'followupflagged':
			{					
				$result = array ( );

				$map = array(
					//'id' => array(),
					'folderName' => array(),
					'messageNumber' => array(),
					'messageId' => array()
				);
				
				self::parseFilter($criteria["filter"], $map);
	
				if (empty($map['folderName'])) {
					$folders = $this->get_folders_list();
					foreach ($folders as $folder)
						if (isset($folder['folder_id'])) 
							$map['folderName'][] = $folder['folder_id'];
				}
				
				$messagesIds = $map['messageId'];

				foreach ($map['folderName'] as $folder) {
					$messages = array();
					
					$this->mbox = $this->open_mbox($folder);

					/**
					 * Se é uma busca por messageId
					 */
					if (!empty($map['messageId'])) {
							
						foreach ($messagesIds as $k => $v) {

							$r = imap_search($this->mbox, 'ALL KEYWORD "$Followupflagged" TEXT "Message-Id: '.$v.'"', SE_UID);

							if ($r) {

								$messages = array_merge($messages, $r);
								unset($messagesIds[$k]);
								
							}
						}

					/**
					 * Se é uma busca por messageNumber.
					 * Lembrando que, neste caso, só deve ser suportada uma única pasta no filtro.
					 */
					} else {
						$messages = imap_search($this->mbox, 'ALL KEYWORD "$Followupflagged"', SE_UID);
					}

					/**
					 * Se é uma busca por messageId, deve ser comparado com os messageNumbers 
					 * passados no filtro, se houverem.
					 */
					if (!empty($map['messageNumber']) && is_array($messages)) {
												
						foreach ($messages as $k => $m)
							if (!in_array($m, $map['messageNumber']))
								unset($messages[$k]);
					}

					/**
					 * Adicionar demais atributos às mensagens para retorno
					 */
					if(is_array($messages))
					foreach ($messages as $k => $m) {
						$headers = imap_fetch_overview($this->mbox, $m, FT_UID);

						$result[] = array (
							'messageId' => $headers[0]->message_id,
							'messageNumber' => $m,
							'folderName' => $folder
						);
					}

					
					/**
					 * Se é uma busca por messageId e todos os messageIds foram econstrados:
					 * Stop searching in all folders
					 */
					if (!empty($map['messageId']) && empty($messagesIds))
						break;
				}
				

				return $result;
				
			} //CASE 'followupflag'
		}
    }

    public function read( $URI, $justthese = false )
    {

		switch( $URI['concept'] )
		{
			case 'message':
			{	
				return $this->to_utf8( 
					$this->get_info_msg( array('msg_number'=>$URI['id'],
					'msg_folder'=>str_replace( '.', $this->imap_delimiter, $justthese['context']['folder'] ) ,
					'decoded' => true ) ) 
				);
			}
			case 'labeled':
			{
				/**
				 * id looks like 'folder/subfolder/subsubfolder/65#13', meaning messageId#labelId
				 */
				list($messageId, $labelId) = explode('#', $URI['id']);
				$folderName = basename($messageId);
				$messageNumber = dirname($messageId);
				
				$result = array();

				if ($folderName && $messageNumber && $labelId) {
					$this->mbox = $this->open_mbox($folderName);
					$messagesLabeleds = imap_search($this->mbox, 'UNDELETED KEYWORD "$Label'.$labelId.'"', SE_UID);
					
					if (in_array($messageNumber, $messagesLabeleds)) {
						$result = array (
							'id' => $URI['id'], 
							'folderName' => $folderName,
							'messageNumber' => $messageNumber,
							'labelId' => $labelId
						);
					}

				}
				
				return $result;
			}
			
			case 'followupflagged':
			{
			
				/** 
				 * identifica se o formato de ID é "folder/subfolder/subsubfolder/<messageNumber>" ou "<message-id>"
				 */
				$folderName = $messageNumber = false;
				if(!($messageHasId = preg_match('/<.*>/', $URI['id']))) {
					$folderName = dirname($URI['id']);
					$messageNumber = basename($URI['id']);
				}

				$result = array();
				if ($folderName && $messageNumber) {

					$this->mbox = $this->open_mbox($folderName);
					$r = imap_search($this->mbox, 'ALL KEYWORD "$Followupflagged"', SE_UID);

					if (in_array($messageNumber, $r)) {
						$headers = imap_fetch_overview($this->mbox, $messageNumber, FT_UID);
							
						$result = array (
							'messageId' => $headers[0]->message_id,
							'messageNumber' => $messageNumber,
							'folderName' => $folderName
						);
					}
				
				} else {
					/**
					 * Busca pela mensagem com o messageId dado. Se uma pasta foi passada, busca nela,
					 * senão busca em todas.
					 */
					
					$folders = array ();
					if ($folderName) {
						$folders = array ($folderName);
					} else {
						$folder_list = $this->get_folders_list();
						foreach ($folder_list as $folder)
							if (isset($folder['folder_id'])) 
								$folders[] = $folder['folder_id'];
					}
					
					foreach ($folders as $folder) {
						
						$this->mbox = $this->open_mbox($folder);
						
						if ($messages = imap_search($this->mbox, 'ALL KEYWORD "$Followupflagged" TEXT "Message-Id: '.$URI['id'].'"', SE_UID)) {
				
							$result = array (
								'messageId' => $URI['id'],
								'messageNumber' => $messages[0],
								'folderName' => $folder
							);
							
							/**
							 * Stop searching in all folders
							 */
							break;
						}
	
					}
				}
				
				
				return $result;
			}
		}
    }

    public function create($URI, &$data)
    {               
		switch( $URI['concept'] )
		{
			case 'labeled':
			{
				if (isset($data['folderName']) && isset($data['messageNumber']) && isset($data['labelId'])) {
					$this->mbox = $this->open_mbox($data['folderName']);
					imap_setflag_full($this->mbox, $data['messageNumber'], '$Label' . $data['labelId'], ST_UID);

					return array ('id' => $data['folderName'].'/'.$data['messageNumber'].'#'.$data['labelId']);
				}
				return array ();
			}
			case 'followupflagged':
			{
				//deve ser gravado primeiro no imap, obtido o message-id e, depois gravado no banco
				
				if (isset($data['folderName']) && isset($data['messageNumber'])) {
					
					$this->mbox = $this->open_mbox($data['folderName']);
					$s = imap_setflag_full($this->mbox, $data['messageNumber'], '$Followupflagged', ST_UID);
					
					$headers = imap_fetch_overview($this->mbox, $data['messageNumber'], FT_UID);
					
					$data['messageId'] = $headers[0]->message_id;
					
					/*
					 * TODO
					 * Verificar erro ao tentar setar uma flag com o limite de flags atingido
					 * onde o status retornado pelo imap_setflag_full é true mesmo não sendo possível
					 * a inserção da flag.
					 */

					return (($s) && (imap_last_error() != 'Too many user flags in mailbox')) ? $data : array();

				} else if (isset($data['messageId'])) {
					/**
					 * Busca pela mensagem com o messageId dado. Se uma pasta foi passada, busca nela,
					 * senão busca em todas.
					 */
					$folders = array ();
					if (isset($data['folderName'])) {
						$folders = array ($data['folderName']);
					} else {
						$folder_list = $this->get_folders_list();
						foreach ($folder_list as $folder)
							if (isset($folder['folder_id'])) 
								$folders[] = $folder['folder_id'];
					}
					
					foreach ($folders as $folder) {
						
						$this->mbox = $this->open_mbox($folder);
						if ($messages = imap_search($this->mbox, 'ALL TEXT "Message-Id: '.$data['messageId'].'"', SE_UID)) {
							
							$s = imap_setflag_full($this->mbox, $messages[0], '$Followupflagged', ST_UID);
						
							/**
							 * Stop searching in all folders
							 */
							return $data;
						}
						
					}
				}
				return array ();
			}
			
			case 'message':
			{
				require_once ROOTPATH.'/library/uuid/class.uuid.php';
				
				$GLOBALS['phpgw_info']['flags'] = array( 'noheader' => true, 'nonavbar' => true,'currentapp' => 'expressoMail','enable_nextmatchs_class' => True );
				$return = array();

				require_once dirname(__FILE__) . '/../../services/class.servicelocator.php';
				$mailService = ServiceLocator::getService('mail');

				$msg_uid = $data['msg_id'];
				$body = $data['body'];
				$body = str_replace("&lt;","&yzwkx;",$body); //Alterar as Entities padrão das tags < > para compatibilizar com o Expresso
				$body = str_replace("&gt;","&xzwky;",$body);
				$body = str_replace("%nbsp;","&nbsp;",$body);
				$body = html_entity_decode ( $body, ENT_QUOTES , 'ISO-8859-1' );		
				$body = str_replace("&yzwkx;","&lt;",$body);
				$body = str_replace("&xzwky;","&gt;",$body);				

				$folder = mb_convert_encoding($data['folder'], "UTF7-IMAP","ISO-8859-1, UTF-8");
				$folder = @preg_replace('/INBOX[\/.]/i', "INBOX".$this->imap_delimiter, $folder);

				/**
				* Gera e preenche o field Message-Id do header
				*/
				$mailService->addHeaderField('Message-Id', UUID::generate( UUID::UUID_RANDOM, UUID::FMT_STRING ) . '@Draft');
                $mailService->addHeaderField('Reply-To', mb_convert_encoding(($data['input_reply_to']), 'ISO-8859-1', 'UTF-8,ISO-8859-1'));
                $mailService->addHeaderField('Date', date("d-M-Y H:i:s"));
				$mailService->addTo(mb_convert_encoding(($data['input_to']), 'ISO-8859-1', 'UTF-8,ISO-8859-1')); 
				$mailService->addCc( mb_convert_encoding(($data['input_cc']), 'ISO-8859-1', 'UTF-8,ISO-8859-1')); 
				$mailService->addBcc(mb_convert_encoding(($data['input_cco']), 'ISO-8859-1', 'UTF-8,ISO-8859-1')); 
				$mailService->setSubject(mb_convert_encoding(($data['input_subject']), 'ISO-8859-1', 'UTF-8,ISO-8859-1')); 
				
				if(isset($data['input_important_message']))
					$mailService->addHeaderField('Importance','High');

				if(isset($data['input_return_receipt']))
					$mailService->addHeaderField('Disposition-Notification-To', Config::me('mail'));

				$this->rfc2397ToEmbeddedAttachment($mailService , $body);

				$isHTML = ( isset($data['type']) && $data['type'] == 'html' )?  true : false;

				if (!$body) $body = ' ';


				$mbox_stream = $this->open_mbox($folder);

				$attachment = json_decode($data['attachments'],TRUE);

                if(!empty($attachment))
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

						unset($att);
					}
					else
					{
						$value = json_decode($value, true);
						
						switch ($value['type']) {
							case 'imapPart':
								$att = $this->getForwardingAttachment($value['folder'],$value['uid'], $value['part']);
								if(strstr($body,'src="./inc/get_archive.php?msgFolder='.$value['folder'].'&msgNumber='.$value['uid'].'&indexPart='.$value['part'].'"') !== false)//Embeded IMG
								{    
									$body = str_ireplace('src="./inc/get_archive.php?msgFolder='.$value['folder'].'&msgNumber='.$value['uid'].'&indexPart='.$value['part'].'"' , 'src="'.$att['name'].'"', $body);
									$mailService->addStringImage($att['source'], $att['type'], mb_convert_encoding($att['name'], 'ISO-8859-1' , 'UTF-8,ISO-8859-1'));
								}
								else
									$mailService->addStringAttachment($att['source'], mb_convert_encoding($att['name'], 'ISO-8859-1' , 'UTF-8,ISO-8859-1'), $att['type'], 'base64', isset($att['disposition']) ? $att['disposition'] :'attachment' );
								unset($att);
								break;
							case 'imapMSG':
								$mbox_stream = $this->open_mbox($value['folder']);
								$rawmsg = $this->getRawHeader($value['uid']) . "\r\n\r\n" . $this->getRawBody($value['uid']);
								$mailService->addStringAttachment($rawmsg, mb_convert_encoding(base64_decode($value['name']), 'ISO-8859-1' , 'UTF-8,ISO-8859-1'), 'message/rfc822', '7bit', 'attachment' );
								unset($rawmsg);
								break;

							default:
							break;
						}
					}

				}

				if($isHTML) $mailService->setBodyHtml($body); else $mailService->setBodyText(mb_convert_encoding($body, 'ISO-8859-1' , 'UTF-8,ISO-8859-1' ));

				if(imap_append($mbox_stream, "{".$this->imap_server.":".$this->imap_port."}".$folder, $mailService->getMessage(), "\\Seen \\Draft"))
				{
					$status = imap_status($mbox_stream, "{".$this->imap_server.":".$this->imap_port."}".$folder, SA_UIDNEXT);
					$return['id'] = $status->uidnext - 1;

					if($data['uidsSave'] )
						$this->delete_msgs(array('folder'=> $folder , 'msgs_number' => $data['uidsSave']));
				}

				return $return;
			}
		}
	}

    public function delete( $URI, $justthese = false, $criteria = false )
    {
		switch( $URI['concept'] )
		{
			case 'labeled':
			{
				list($messageId, $labelId) = explode('#', $URI['id']);
				$folderName = dirname($messageId);
				$messageNumber = basename($messageId);

				if ($folderName && $messageNumber && $labelId) {
					$this->mbox = $this->open_mbox($folderName);
					imap_clearflag_full($this->mbox, $messageNumber, '$Label' . $labelId, ST_UID);

				}
			}
			case 'followupflagged':
			{
				$map = array(
					'folderName' => array(),
					'messageNumber' => array(),
					'messageId' => array()
				);
				
				self::parseFilter($criteria["filter"], $map);
				
				if (!$map['folderName']) {
					$folders = array ();
					
					$folder_list = $this->get_folders_list();
					foreach ($folder_list as $folder)
						if (isset($folder['folder_id'])) 
							$folders[] = $folder['folder_id'];
					$map['folderName'] = $folders;
				}

				$messagesIds = $map['messageId'];

				foreach ($map['folderName'] as $folder) {
					$messages = array();
					
					$this->mbox = $this->open_mbox($folder);
				
					/**
					 * Se é uma busca por messageId
					 */
					if (!empty($map['messageId'])) {
							
						foreach ($messagesIds as $k => $v) {
							$r = imap_search($this->mbox, 'ALL KEYWORD "$Followupflagged" TEXT "Message-Id: '.$v.'"', SE_UID);

							if ($r) {
								$messages = $messages + $r;
								unset($messagesIds[$k]);	
							}
						}

					/**
					 * Se é uma busca por messageNumber.
					 * Lembrando que, neste caso, só deve ser suportada uma única pasta no filtro.
					 */
					} else {
						$messages = imap_search($this->mbox, 'ALL KEYWORD "$Followupflagged"', SE_UID);
					}

					/**
					 * Se é uma busca por messageId, deve ser comparado com os messageNumbers 
					 * passados no filtro, se houverem.
					 */
					if (!empty($map['messageNumber'])) {
						foreach ($messages as $k => $m)
							if (!in_array($m, $map['messageNumber']))
								unset($messages[$k]);
					}

					$s = true;
					foreach ($messages as $k => $m) {						
						$s = imap_clearflag_full($this->mbox, $m, '$Followupflagged', ST_UID) && $s;
					}

					/**
					 * Se é uma busca por messageId e todos os messageIds foram econstrados:
					 * Stop searching in all folders
					 */
					if (!empty($map['messageId']) && empty($messagesIds))
						break;
				}
				
				return $s;
			}
		}

		//TODO - return
	}

    public function deleteAll( $URI, $justthese = false, $criteria = false )
    {
		$op = $criteria['filter'][0];
		$ids = $criteria['filter'][2];
		if($op == 'IN'){
			foreach ($ids as $id){
				self::delete( array( 'concept' => $URI['concept'], 'id' => $id), false, false);
			}
		}

		/**
		 * TODO - implementar a deleção de todos os followupflaggeds conforme filtro
		 */
	}

    public function update( $URI, $data, $criteria = false )
    {
		/**
		 * Os únicos atributos da sinalização presentes no IMAP são folderName, messageNumber e messageId,
		 * porém a operação de update desses atributos não faz sentido para o usuário da DataLayer,
		 * pois na prática elas são executadas através das operações de CREATE e DELETE.
		 * Assim, para os conceitos "labeled" e "followupflagged", só faz sentido o update de 
		 * atributos gravados no banco de dados e nunca no IMAP.
		 */
	}

//     public function retrieve( $concept, $id, $parents, $justthese = false, $criteria = false )
//     {
// 			return $this->read( array( 'id' => $id, 
// 			    'concept' => $concept, 
// 			    'context' => $parents ), $justthese );
//     }

    public function replace( $URI, $data, $criteria = false )
    {}

    public function close()
    {}

    public function setup()
    {}

    public function commit( $uri )
    { return( true ); }

    public function rollback( $uri )
    {}

    public function begin( $uri )
    {}


    public function teardown()
    {}

    function to_utf8($in) 
    { 
		if (is_array($in)) { 
			foreach ($in as $key => $value) { 
				$out[$this->to_utf8($key)] = $this->to_utf8($value); 
			} 
		} elseif(is_string($in)) { 
				return mb_convert_encoding( $in , 'UTF-8' , 'UTF-8 , ISO-8859-1' ); 
		} else { 
			return $in; 
		} 
		return $out; 
    }
	
	    
    private static function parseFilter($filter ,&$map){
		
		if( !is_array( $filter ) || count($filter) <= 0) return null;
					
		$op = array_shift( $filter );
		switch(strtolower($op))
		{
			case 'and': {
				foreach ($filter as $term)
					self::parseFilter($term ,$map);
				return;
			}
			case 'in': {
				if(is_array($map[$filter[0]]) && is_array($filter[1]))
					$map[$filter[0]] = array_unique(array_merge($map[$filter[0]], $filter[1]));
				return;
			}
			case '=': {
				$map[$filter[0]][] = $filter[1];
			}
		}
	}

}
