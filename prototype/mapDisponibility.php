<?php

	/**
    * Recupera os eventos de um usuário para uso no mapa de disponibilidade
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
    * @sponsor    Caixa Econômica Federal
    * @author     Adriano Coutinho da Silva
    * @return     Array de objetos com os eventos mergedos indexados por id de usuário
    * @access     public
    */

	//TODO -  Teste Remover comentario
	$data = $_GET;

	require_once "api/controller.php";

	if(isset($data)){
		
		$users = $data['attendees'];
		
		$disponibility = array();
		
		foreach($users as $key => $value){
		/*	

			 SELECT * FROM  
				calendar_object as co inner join
				calendar_to_calendar_object as ctco on
				ctco.calendar_object_id = co.id
				WHERE (range_start >=  1331434800000 AND range_end <= 1332039600000) AND 
				ctco.calendar_id IN(5)
			
			 SELECT * FROM  
				calendar_object WHERE (range_start >=  1331434800000 AND range_end <= 1332039600000 AND id IN 
				( SELECT calendar_object_id from calendar_to_calendar_object where calendar_id IN (5)  ))
		*/
			
			
			$sql = 'SELECT calendar_object.range_start as "startTime" , calendar_object.range_end as "endTime", calendar_object.allday as "allDay", calendar_object.tzid as "timezone" FROM calendar_object WHERE ('
			.'((range_start >=  '.$data['startTime'].' AND range_start <= '.$data['endTime'].')'
			.' OR (range_end >=  '.$data['startTime'].' AND range_end <= '.$data['endTime'].')'
			.' OR (range_start <=  '.$data['startTime'].' AND range_end >= '.$data['endTime'].') )'
			.' AND transp = 0  AND id IN ( SELECT calendar_object_id from calendar_to_calendar_object where '
			.'calendar_id IN (SELECT calendar_id FROM calendar_signature WHERE (user_uidnumber = '. $value['id'] .'  AND is_owner = 1 ))  )'
			.'AND (calendar_object.id NOT IN (SELECT calendar_object_activity_id FROM calendar_task_to_activity_object)'
			.'OR calendar_object.type_id = 1))';

			$result = Controller::service('PostgreSQL')->execResultSql($sql);
		
			if(!count($result))
				continue;		
			
			$disponibilyUser = array();
			
			$startTime = new DateTime('now', new DateTimeZone($data['timezone']));
			$endTime = new DateTime('now', new DateTimeZone($data['timezone']));
			
			foreach($result as $ke => $va){

				$startTime->setTimestamp((int) ($va['startTime'] / 1000));
				$endTime->setTimestamp((int) ($va['endTime'] / 1000));
				
				array_push($disponibilyUser, array('startTime' => ($startTime->format('U') + ( $startTime->format('O') * (36) )).'000', 'endTime' => ($endTime->format('U') + ( $startTime->format('O') * (36) )).'000', 'allDay' => $va['allDay']));

			/* 
			*
			* A implementação abaixo une eventos que convergem os horários
			*
			
				if(count($disponibilyUser) == 0){
					array_push($disponibilyUser, array('startTime' => $va['startTime'], 'endTime' => $va['endTime']));
					continue;
				}

				$action = 'new';
				$position = '0';
				
				foreach($disponibilyUser as $k => $v){
					
					if($v['startTime'] >= $va['startTime']){
						
						if($v['startTime'] > $va['endTime'])
							continue;
						
						else if(($v['endTime'] <= $va['endTime'])){
							$action = 'delete';

						}else if(($v['endTime'] > $va['endTime'])){

							$action = 'afterUpdate';
					

						}
					}else if($v['startTime'] <= $va['startTime']){
						
						if($v['endTime'] < $va['startTime'])
							continue;
						
						else if($v['endTime'] >= $va['endTime']){
						
							$action = 'update';

						
						}else
							$action = 'afterUpdate';
					
					}

					$position = $k;
				}
			
				switch($action){
					case 'new':
						array_push($disponibilyUser, array('startTime' => $va['startTime'], 'endTime' => $va['endTime']));
					break;
					
					case 'update':

						$disponibilyUser[$position]['startTime'] = $v['startTime'];
						$disponibilyUser[$position]['endTime'] = $v['endTime'];
					
					break;
					
					case 'beforeUpdate':
					
						$disponibilyUser[$position]['startTime'] = $v['startTime'];
					
					break;
					
					case 'afterUpdate':
					
						$disponibilyUser[$position]['endTime'] = $v['endTime'];
					
					break;
					
					case 'delete':
					break;
				}
	*/		
			}

		$disponibility[$value['id']] = $disponibilyUser;

		 unset($disponibilyUser);
	
		}	
		echo json_encode($disponibility);

	}else{

		return json_encode(array('false'));
	}

?>