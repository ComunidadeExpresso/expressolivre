<?php
include_once ROOTPATH."/../expressoMail/inc/class.imap_functions.inc.php";

use prototype\api\Config as Config;

class Helpers {    

/**
 * LABEL
 */ 
	//labeled:after.find
	public function deepnessLabeleds( &$uri , &$result , &$criteria , $original ){                

		if(isset($original['criteria']['deepness']))
		{
			foreach ($result as $i => $v)
			{
				if(isset($v['labelId']))
				{
					$labels = Controller::find( array( 'concept' => 'label' ) , false, array( 'filter' => array('=', 'id'  ,  $v['labelId']) ));     
					$result[$i]['label'] = $labels[0];
				}              

			}
		}

		return $result;
	}
	
	/**
	 * Método a ser chamado na exclusão de um label, procedendo com a
	 * desvinculação deste label com todas as mensagens a que estava vinculado
	 */
	//label:before.delete
	public function clearAllLabeledsOfLabel( &$uri , &$result , &$criteria , $original ){
		
		$labeleds = Controller::find( array( 'concept' => 'labeled' ) , false, array( 'filter' => array('=', 'labelId'  ,  $uri['id']) ));
		if (empty($labeleds))
			return;
		
		$labeledsIds = array();
		foreach ($labeleds as $e) {
				$labeledsIds[] = $e['id'];
		}
		Controller::delete( array( 'concept' => 'labeled', 'service' => 'Imap' ), false, array( 'filter' => array( 'IN', 'id', $labeledsIds )) );

		//return $result;
	}
	/**
	 * Método a ser chamado ao listar os labeleds, uma vez que as funções de IMAP do PHP não são capazes de 
	 * obter a lista de todos os labels e nem são capazes de obter os labels de uma dada mensagem
	 */
	//labeled:before.find
	public function makeLabelListFilter( &$uri , &$result , &$criteria , $original ){

		if (!isset($criteria['filter']) || !self::in_arrayr('labelId', $criteria['filter'])) {
			$labels = Controller::find( array( 'concept' => 'label' ) );

			$list = array();
			if(is_array($labels))
			foreach ($labels as $label)
				$list[] = $label['id'];
				
			$filter = array( 'IN' , 'labelId' , $list );
			
			if (isset($criteria['filter']) && $criteria['filter']!=NULL && count($criteria['filter']) != 0)
				$criteria['filter'] = array( 'AND', $criteria['filter'], $filter );
			else
				$criteria['filter'] =  $filter;
		}
	}
	
	public static function in_arrayr($needle, $haystack) {
		//if(!is_array($haystack)) return false;
		
		foreach ($haystack as $v) {
				if ($needle == $v) return true;
				elseif (is_array($v)) return self::in_arrayr($needle, $v);
		}
		return false;
	} 
	
/**
 * FOLLOWUPFLAG
 */ 
	//followupflagged:after.find
	public function deepnessFollowupflaggeds( &$uri , &$result , &$criteria , $original ){                

		if(isset($original['criteria']['deepness']))
		{
			foreach ($result as $i => $v)
			{
				if(isset($v['followupflagId']))
				{
					$followupflags = Controller::find(array('concept' => 'followupflag'), false, array('filter' => array('=', 'id', $v['followupflagId']) ));
					
					/**
					 * Devido a inconsistencias na chamada do interceptor addDefaultElementsFilter (followupflag:before.find)
					 * os followupflag defaults são inseridos no retorno, por isso é necessário procurar o objeto correto
					 * através do id
					 */
					foreach ($followupflags as $followupflag) {
						if ($v['followupflagId'] == $followupflag['id']) {
							$result[$i]['followupflag'] = $followupflag;
							break;
						}
					}
				}
				
				if(isset($v['messageNumber']) && isset($v['folderName']))
				{
					$details = $original['criteria']['deepness'] == '1' ? 'headers' : 'all';
					
					$imapService = new imap_functions();
					$message = $imapService->getMessages(array('messages' => array($v['folderName'] => array($v['messageNumber'])), 'details' => $details));
					$result[$i]['message'] = $message[$v['folderName']][0];				
				}     
			}
		}

		return $result;
	}	
	
	//followupflagged:PostgreSQL.before.create
	public function letFollowupflaggedKeepUnique (&$uri , &$params , &$criteria , $original ){

		if (isset($params['folderName']) && isset($params['messageNumber'])) {
			$filter = array ('AND', array('=', 'folderName', $params['folderName']), array('=', 'messageNumber', $params['messageNumber']));

			$imap_result = Controller::find( 
				array('concept' => 'message'), 
				array('messageId'), 
				array('filter' => $filter)
			);

			if($imap_result) {
				$params['messageId'] = $imap_result[0]['messageId'];
			}
		}

		$filter = array('=', 'messageId', $params['messageId']);
		Controller::delete(array('concept' => 'followupflagged', 'service' => 'PostgreSQL'), null, array('filter' => $filter));

	}
	
	//followupflag:before.find 
	public function addDefaultElementsFilter (&$uri , &$params , &$criteria , $original ){ 
		//if (!self::in_arrayr('id', $criteria['filter'])) { 
			$defaultsFilter = array( 'IN' , 'id' , array('1','2','3','4','5','6') ); 

			if (isset($criteria['filter']) && $criteria['filter']!=NULL && count($criteria['filter']) != 0) 
				$criteria['filter'] = array( 'OR', $criteria['filter'], $defaultsFilter ); 
			else 
				$criteria['filter'] =  $defaultsFilter; 
		//} 
	}

	//Remove as dependencias de uma followupflag 
	public function clearAllFlaggedOfFollowupflag( &$uri , &$params , &$criteria , $original ){
		//remove apenas se vier o id da Followupflag
		if(isset($uri['id'])) {
			$result = Controller::find(array('concept' => 'followupflagged'), null , array('filter' => array('=' , 'followupflagId' , $uri['id'])));
			foreach ($result as $flagged) {
				Controller::delete(array('concept' => 'followupflagged', 'id' => $flagged['id']), false, false);
			}

			Controller::deleteALL(array('concept' => 'followupflagged'), null , array('filter' => array('=' , 'followupflagId' , $uri['id'])));
		}
	}

	//followupflagged:PostgreSQL.after.delete
	public function doImapDelete( &$uri , &$params , &$criteria , $original ) {

		$imap_uri = $uri;
		$imap_uri['service'] = 'Imap';
		
		$result = Controller::delete($imap_uri, $params, $criteria);
		
		return $result;

	}
	
	//followupflagged:Imap.before.delete
	public function getReferenceToImapDelete( &$uri , &$params , &$criteria , $original ) {

		if (isset($uri['service']) && $uri['service'] == 'Imap' && $uri['id'] /*&& !$criteria*/) {
			$db_uri = $uri;
			$db_uri['service'] = 'PostgreSQL';
			$flagged = Controller::read($db_uri, array('messageId'), false);
			if ($flagged) {
				if (!$criteria) 
					$criteria = array();
				$criteria['filter'] = array('=', 'messageId', $flagged['messageId']);
			} else {
				return false;
			}
		}

	}
		
	//followupflagged:PostgreSQL.before.create
	public function doImapCreate( &$uri , &$params , &$criteria , $original ) {	
	
		$imap_uri = $uri;
		$imap_uri['service'] = 'Imap';
		
		if(empty($params['messageId'])) 
		    throw new Exception('#FollowupflagMessageIdError');
		else 
		    $params = Controller::create($imap_uri, $params);

		if (!$params)
		    throw new Exception('#FollowupflagLimitError');
	}
	
	//followupflagged:PostgreSQL.after.read
	public function doImapRead( &$uri , &$result , &$criteria , $original ) {
		/**
		 * Se a busca for apenas no banco de dados, pula-se todas as verificações 
		 * deste interceptor.
		 */
		
	   
	    
	    
		if ($original['URI']['service'] == 'PostgreSQL') return $result;
		
		if ($result) {
			$imap_uri = $uri;
			$imap_uri['service'] = 'Imap';
			$imap_result = Controller::find( 
				$imap_uri, 
				false, 
				array('filter' => array('=', 'messageId', $result['messageId']))
			);
		}

		/**
		 * Faz a consistência do banco com o imap
		 */
		if (count($imap_result) < 1) {
			$r = Controller::delete($uri, null, $criteria);
			return false;
		} else {
			$imap_result = $imap_result[0];
		}
		
		/**
		 * Faz a consistência do banco com o imap
		 */
		if ($imap_result['messageId'] !== $result['messageId']) {
			$n = $imap_result;
			$n['followupflagId']  = 1;
			$n['backgroundColor'] = '#FF2016';
			$n['id'] = Controller::create(array('concept' => 'followupflagged'), $n);
			$result = $imap_result + $n;
		}
		
		$result = $result + $imap_result;
		
		return $result;
	}
	
	
	/**
	 * Método a ser chamado ao listar os sinalizadores, uma vez que as funções de IMAP do PHP não são capazes de 
	 * obter a lista de todos os sinalizadores e nem são capazes de obter os sinalizadores de uma dada mensagem
	 */
	//followupflagged:PostgreSQL.after.find
	public function doImapFind( &$uri , &$result , &$criteria , $original ){
	    
	  	$imap_uri = $uri;
		$imap_uri['service'] = 'Imap';
		$imap_criteria = $original['criteria'];
		
		if (self::in_arrayr('alarmDeadline', $original['criteria']) || 
			self::in_arrayr('doneDeadline', $original['criteria']) || 
			self::in_arrayr('followupflagId', $original['criteria'])) 
		{
			if (empty($result)) return $result;
			
			$idList = array();
			foreach($result as $r) {
				$idList[] = $r['messageId'];
			}
			
			$imap_criteria['filter'] = array('IN', 'messageId', $idList);
		}
		$imap_result = Controller::find($imap_uri, false, $imap_criteria);

		/**
		 * Mescla os dados vindos do PostgreSQL com os dados vindos do Imap
		 */
		$merge_result = array ();
		if(is_array($imap_result))
		foreach ($imap_result as $j => $ir) {
		
			foreach($result as $k => $r) {
			
				if ($r['messageId'] == $ir['messageId']) {
					if (!empty($r['messageId']))
						$merge_result[] = $r + $ir;
					
					unset($result[$k]);
					unset($imap_result[$j]);
					
					break;
				}
			}
		}
		
		/**
		 * Faz a consistência do banco com o imap
		 */
		/*
		if ($result) {
			$idList = array();
			foreach ($result as $ir) {
				$idList[] = $ir['messageId'];
			}
			$filter = array('IN', 'messageId', $idList);
			Controller::delete(array('concept' => 'followupflagged'), null , array('filter' => $filter));
		}
		*/
		
		/**
		 * Faz a consistência do banco com o imap
		 */
		if ($imap_result) {
			foreach ($imap_result as $ir ) {
				$n = $ir;
				$n['followupflagId']  = 1;
				$n['backgroundColor'] = '#FF2016';
				$n['id'] = Controller::create(array('concept' => 'followupflagged'), $n);
				$merge_result[] = $n;
			}
		}

		return $merge_result;
	}

	//followupflagged:PostgreSQL.before.find
	public function reFilterFind (&$uri , &$params , &$criteria , $original ){
		/**
		 * Se o filtro incluir atributos da mensagem que o banco de dados não conhece,  
		 * deve-se obter os messageId dos itens do resultado e passá-los num novo filtro
		 * que o banco conheça
		 */
		
		$folder = isset($uri['folder']) ? $uri['folder'] : 'INBOX';

		if (self::in_arrayr('messageNumber', $criteria) || self::in_arrayr('folderName', $criteria)) {
			$result = Controller::find(array('concept' => 'message' , 'folder' =>  $folder), array('messageId'), array('filter' => $criteria['filter']));
			$idList = array();
			if(is_array($result))
				foreach ($result as $message)
					$idList[] = $message['messageId'];
				
			$filter = array( 'IN' , 'messageId' , $idList );

			$criteria['filter'] =  $filter;
		}	
	}	

	//label:before.create 
	//label:before.update
	public function verifyNameLabel(&$uri , &$params , &$criteria , $original){ 
			$labels = Controller::find( array('concept' => 'label'), false, array('filter' => array('i=', 'name', $params['name'])));

			if (!empty($labels)){
				foreach ($labels as $i => $v){
					if(!isset($params['id']) || $v['id'] != $params['id']){
						throw new Exception('#LabelNameError'); 
					}
				}
			} 
	}

	//label:before.create
	public function validateNumberSlots(&$uri , &$params , &$criteria , $original){
	
			$used = Controller::read( array( 'concept' => 'label', 'id' => '1' ), array( 'id' ), array( 'uid' => Config::me('uidNumber') ) );

			if( !isset( $used['id'] ) ) 
			{ 
				$params['id'] = '1'; 
				return; 
			} 

			$slot = Controller::service('PostgreSQL')->execSql( 'SELECT label.slot + 1 as id FROM expressomail_label as label, phpgw_config as config WHERE label.user_id = '.Config::me('uidNumber').' AND config.config_name = \'expressoMail_limit_labels\' AND label.slot <= config.config_value::integer AND ( SELECT count(slot) FROM expressomail_label WHERE slot = label.slot + 1 AND user_id = '.Config::me('uidNumber').' ) = 0 limit 1', true ); 

			if( empty( $slot ) ) 
			{ 
				throw new Exception('#LabelSlotError'); 
			} 

			$params['id'] = $slot['id']; 
	}
}

?>
