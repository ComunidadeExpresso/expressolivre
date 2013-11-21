<?php
class Migra{
		public $local;
		public $login; 
		public $senha; 
		public $banco;
		public $banco2;		
		public $porta;

		public $current_base;
		public $new_base;
		
		/*
		*	TODO - Implementar:
		*	Repetição
		*	Alarm
		*	ACL
		*/
	
		function __construct()
		{
			include_once dirname(__FILE__ ).'/../../header.inc.php';

			if (is_array($_SESSION['phpgw_info']['expresso']['server']))
				$GLOBALS['phpgw_info']['server'] = $_SESSION['phpgw_info']['expresso']['server'];
			else
			$_SESSION['phpgw_info']['expresso']['server'] = $GLOBALS['phpgw_info']['server'];
					
			$local = $_SESSION['phpgw_info']['expresso']['server']['db_host'];
			$login = $_SESSION['phpgw_info']['expresso']['server']['db_user'];
			$senha = $_SESSION['phpgw_info']['expresso']['server']['db_pass'];
			$banco = $_SESSION['phpgw_info']['expresso']['server']['db_name'];
			$porta = $_SESSION['phpgw_info']['expresso']['server']['db_port'];
			$banco2 = $banco;

			$this->current_base = pg_connect("host='$local' port='$porta' dbname='$banco' user='$login' password='$senha'");
			$this->new_base = pg_connect("host='$local' port='$porta' dbname='$banco2' user='$login' password='$senha'");
		}
		
		//Verifica e/ou cria agenda
		function as_calendar($uid){
			$sql = "select (calendar_id) FROM calendar_signature where user_uidnumber = ".$uid." and is_owner = 1";		
			$result = pg_query($this->new_base ,$sql);
			if (!$line = pg_fetch_assoc($result)) {
					$last_resource = pg_query($this->new_base, "insert into calendar (name, location, tzid) values('Calendar' , 'Calendar', 'America/Sao_Paulo' ) RETURNING id");
					$last = pg_fetch_assoc($last_resource);
					
					$id_resource =  pg_query($this->new_base, "insert into calendar_signature(user_uidnumber, calendar_id, is_owner, font_color, background_color, border_color) values(".$uid.", ".$last['id'].", 1, 'FFFFFF', '3366CC', '3366CC') RETURNING id");
					$calendar = pg_fetch_assoc($id_resource);
					return $calendar['id'];
			}else{
				$result = pg_query($this->new_base ,$sql);
				while($evento = pg_fetch_array($result)){
					return $evento['calendar_id'];
				}
			}
		}
		
		//Usuarios de um evento
		function as_user($cal_id, $calendar_object ,$owner){
			$sql_select_user = "select * from phpgw_cal_user where cal_id = ".$cal_id;			
			$result = pg_query($this->current_base ,$sql_select_user);
				
			while($evento = pg_fetch_array($result)){
				$sql_insert = "insert into calendar_participant (user_info_id, object_id, is_organizer, acl ,is_external, participant_status_id) values ( ";
				$sql_insert .= $evento['cal_login'].", ";
				$sql_insert .= $calendar_object.", ";
				if ($owner == $evento['cal_login']){
					$sql_insert .= "1, ";
					$sql_insert .= '\'rowi\', ';
				}else{
					$sql_insert .= "0, ";
					$sql_insert .= '\'r\', ';
				}
				$sql_insert .= "0, ";
				
				if($evento['cal_status'] == 'A')
					$sql_insert .= "1) ";
				else if ($evento['cal_status'] == 'U')
					$sql_insert .= "4) ";
				else if ($evento['cal_status'] == 'R')
					$sql_insert .= "3) ";
				else if ($evento['cal_status'] == 'T')
					$sql_insert .= "2) ";				
				pg_query($this->new_base ,$sql_insert);

				$Id_Calendar_User = $this->as_calendar($evento['cal_login']);
				$sql_insert_relcao = 'insert into calendar_to_calendar_object (calendar_id, calendar_object_id) values ( ';
				$sql_insert_relcao .= $Id_Calendar_User.', ';
				$sql_insert_relcao .= $calendar_object.' )';
				pg_query($this->new_base ,$sql_insert_relcao);
				
			}
		}

                function decode_days_repeat($hex){
                    $bin = str_split( decbin($hex) );

                    $decoded = array('SU','MO','TU','WE','TH','FR','SA');
                    $returns = array();
                    
                    foreach($bin as $key => $value)
                        if((int)$value == 1)
                            $returns[] = $decoded[$key];

                    return implode(',', $returns);
                }
                
		//Repetição de um evento
		function as_repeat($cal_id, $calendar_object ,$startTime){
			$sql_select_repeat = "select * from phpgw_cal_repeats where cal_id = ".$cal_id;			
			$result = pg_query($this->current_base ,$sql_select_repeat);
				
			while($repeat = pg_fetch_array($result)){
				$sql_insert = "insert into calendar_repeat (frequency, dtstart, object_id, until, byday, bymonthday, byyearday, interval) values ( ";
				$type = '';
                                $weeklyDays = '';
                                $byMonthDay = '';
                                $byYearDay = '';
				switch($repeat['recur_type']){
					case 1:
						$type = 'daily';
					break;
					case 2:
						$type = 'weekly';
                                                $weeklyDays = $this->decode_days_repeat($repeat['recur_data']);
					break;
					case 3:
					case 4:
                                                $type = 'monthly';
                                                $day = new DateTime('@' . (int)$startTime, new DateTimeZone('UTC'));
                                                $byMonthDay = date_format($day, 'j');        
					break;
					case 5:
						$type = 'yearly';
                                                $day = new DateTime('@' . (int)$startTime, new DateTimeZone('UTC'));
                                                $byYearDay = (1 + date_format($day, 'z'));    
					break;
				}
				
				$sql_insert .= "'".$type."', ";
				$sql_insert .= "'".$startTime."000', ";
				$sql_insert .= "'".$calendar_object."', ";  
				
                                $sql_insert .= ($repeat['recur_enddate']) == 0 ? ("'0', ") : ("'".$repeat['recur_enddate']."000',");
				$sql_insert .= "'".$weeklyDays."', ";
                                $sql_insert .= "'".$byMonthDay."', ";
                                $sql_insert .= "'".$byYearDay."', ";
                                $sql_insert .= "'".$repeat['recur_interval']."') RETURNING id";
					
				$result = pg_query($this->new_base ,$sql_insert);
				$repeatEvent = pg_fetch_assoc($result);

				if($repeat['recur_exception'] != ''){

					$ocurrences = explode(',', $repeat['recur_exception']);

					foreach($ocurrences as $value){
						$sql_insert_excepetions = 'insert into calendar_repeat_occurrence (occurrence, exception, repeat_id) values ( ';
						$sql_insert_excepetions .= "'".$value."', ";
						$sql_insert_excepetions .= "'1', ";
						$sql_insert_excepetions .= "'".$repeatEvent['id']."' )";
						pg_query($this->new_base ,$sql_insert_excepetions);
					}
				}

				
				
			}
		}
		
		//Alarmes de um evento
		function as_alarm($cal_id, $event_id, $date_ini){
			$sql_select_evento = "select * from phpgw_async where id like '%".$cal_id."%'";			
			$result = pg_query($this->current_base ,$sql_select_evento);
				
			while($evento = pg_fetch_array($result)){
                                
                                $data = unserialize($evento['data']);
                            
                                $attendee = pg_query($this->new_base , "select id from calendar_participant where (user_info_id = '".$data['owner']."' AND object_id = '".$event_id."' )");
                                $attendee = pg_fetch_array($attendee);

                                $offset = ($date_ini - $evento['next']);
                                
                                if($offset < 0)
                                    continue;

				$sql_insert = "insert into calendar_alarm (action_id, unit, alarm_offset, time, participant_id, object_id, sent) values ( ";
				//action_id
				$sql_insert .= "1, ";
				//unit
				$sql_insert .= "'m', ";
                                //offset
				$sql_insert .= "'".$offset."', ";
				//time
				$sql_insert .= "'".($offset / 60)."', ";
                                //participant
				$sql_insert .= $attendee['id'] .", ";
				//object_id
				$sql_insert .= $event_id.", ";
				//sent
				if($date_ini > time())
					$sql_insert .= "0 ) ";
				else
					$sql_insert .= "1 ) ";
				
				pg_query($this->new_base ,$sql_insert);		
			}
		}
		
		//Usuarios externos de um evento
		function as_user_external($cal_id, $calendar_object ,$owner, $ex_participante){
		
			$participants_ex = base64_decode($ex_participante);
			//Participantes externos serializados
			try {
				$participants_ex = unserialize($participants_ex);
				foreach ($participants_ex as $ex){
				
					$sql_insert = "insert into calendar_ex_participant (name, mail, owner) values ( ";
					if(array_key_exists('cn', $ex)) 
						$sql_insert .= "'".addslashes($ex['cn'])."', ";
					else
						$sql_insert .= "'', ";
					$sql_insert .= "'".addslashes($ex['mail'])."', ";
					$sql_insert .= $owner.") RETURNING id";
					
					$result = pg_query($this->new_base ,$sql_insert);
					
					$id_external = pg_fetch_assoc($result);	
					$sql_insert = "insert into calendar_participant (user_info_id, object_id, is_organizer, is_external, participant_status_id) values ( ";
					$sql_insert .=  $id_external['id'].", ";
					$sql_insert .=  $calendar_object.", ";
					$sql_insert .=  "0, ";
					$sql_insert .=  "1, ";
					$sql_insert .=  "4 ) ";
						
					pg_query($this->new_base ,$sql_insert);
				}
			//participantes externos normais
			} catch (Exception $e) {
				$participants_ex = preg_split('/,/', $ex_participante);
				
				foreach ($participants_ex as $ex){
					$sql_insert = "insert into calendar_ex_participant (mail, owner) values ( ";
					$sql_insert .= "'".$ex."', ";
					$sql_insert .= $owner.") RETURNING id";
					
					$result = pg_query($this->new_base ,$sql_insert);
					
					$id_external = pg_fetch_assoc($result);
					
					
					$sql_insert = "insert into calendar_participant (user_info_id, object_id, is_organizer, is_external, participant_status_id) values ( ";
					$sql_insert .=  $id_external['id'].", ";
					$sql_insert .=  $calendar_object.", ";
					$sql_insert .=  "0, ";
					$sql_insert .=  "1, ";
					$sql_insert .=  "4 ) ";
					pg_query($this->new_base ,$sql_insert);
					
				}			
			}		
		}
		
		//
		function calendar(){
			$sql = "SELECT * FROM phpgw_cal";			
			//Todo
			//Implementar Repetição

			$result = pg_query($this->current_base, $sql);
			while($evento = pg_fetch_array($result)){
			
				$sql_insert = "insert into calendar_object
				(
					type_id, cal_uid, dtstamp, 
					dtstart, description, dtend, 
					location, class_id, last_update,
					range_end, range_start,
					summary, allday, repeat, tzid			
				)		
						
			values(";			
				//remove lixos
				if($evento['owner'] == 0)
					continue;
				if($evento['mdatetime'] == "" or $evento['mdatetime'] == null)
					continue;
				if($evento['datetime'] == "" or $evento['datetime'] == null)
					continue;
				if($evento['edatetime'] == "" or $evento['edatetime'] == null)
					continue;
				//type_id
				$sql_insert .= "1, ";
				//cal_uid
				$sql_insert .= "'".mt_rand()."@Expresso', ";
				//$sql_insert .= $this->as_calendar($evento['owner']).", ";
				//dtstamp	
				$sql_insert .= $evento['mdatetime']."000, ";
				//dtstart
				$sql_insert .= $evento['datetime']."000, ";
				//description
				if ($evento['description'] == "" or $evento['description'] == null)
					$sql_insert .= "'', ";
				else
					$sql_insert .= "'".addslashes($evento['description'])."', ";
				//dtend
				$sql_insert .= $evento['edatetime']."000, ";
				//location
				if ($evento['location'] == "" or $evento['location'] == null)
					$sql_insert .= "'', ";
				else
					$sql_insert .= "'".addslashes($evento['location'])."', ";
				//class_id
				if($evento['is_public'] == 1){
					if($evento['cal_type'] == 'e')
						$sql_insert .= "1, ";
					else
						$sql_insert .= "3, ";
				}else
					$sql_insert .= "2, ";
				//last_update	
				$sql_insert .= $evento['last_update'].", "; 
				//range_end
				$sql_insert .= "'".$evento['edatetime']."000', ";				
				//calendar_id
				//$sql_insert .=  $this->as_calendar($evento['owner']).", ";
				//range_start
				$sql_insert .= "'".$evento['datetime']."000', ";
				//summary
				if ($evento['title'] == "" or $evento['title'] == null)
					$sql_insert .= "'', ";
				else
					$sql_insert .= "'".addslashes($evento['title'])."', ";
				//allday
				$sql_insert .= "0, ";
				//repeat
				$sql_insert .= "0, ";
				//tzid
				$sql_insert .= "'America/Sao_Paulo'";
				
				$sql_insert .= 	" ) RETURNING id";			
								
				$last_resource = pg_query($this->new_base, $sql_insert);
				$calendar = pg_fetch_assoc($last_resource);
				
				//participantes de um evento
				$this->as_user($evento['cal_id'] ,$calendar['id'], $evento['owner']);
				
				//participantes externos de um evento
				if($evento['ex_participants'] != "" and $evento['ex_participants'] != 'YTowOnt9')
					$this->as_user_external($evento['cal_id'] ,$calendar['id'], $evento['owner'], $evento['ex_participants']);	
				
				//Alarmes de eventos
				$this->as_alarm($evento['cal_id'], $calendar['id'], $evento['datetime']) ;
                                
                                //Repetição de um evento
                                $this->as_repeat($evento['cal_id'] ,$calendar['id'], $evento['datetime']);
				
			}
			return 'sucesss';
		}
	}
?>
