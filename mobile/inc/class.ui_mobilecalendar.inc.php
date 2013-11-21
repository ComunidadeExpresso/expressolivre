<?php
    class ui_mobilecalendar {
		
		var $bocalendar;
		var $cat;
		var $link_tpl;		
		var $common;
		var $template;		
		var $daysOfWeek;
		var $shortDaysOfWeek;
		
		var $public_functions = array(
			'index' => true
		);
		
		function ui_mobilecalendar() {
			$this->template = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
			$this->common	= CreateObject('mobile.common_functions');
			$this->bocalendar = CreateObject('calendar.bocalendar',1);			
			$this->cat = &$this->bo->cat;
			$this->daysOfWeek = array(lang('Sunday'), lang('Monday'),lang('Tuesday'),lang('Wednesday'),lang('Thursday'),lang('Friday'),lang('Saturday'));
			$this->shortDaysOfWeek = array(lang('short Sunday'), lang('short Monday'),lang('short Tuesday'),lang('short Wednesday'),lang('short Thursday'),lang('short Friday'),lang('short Saturday'));
			
			$GLOBALS['phpgw']->nextmatchs = CreateObject('phpgwapi.nextmatchs');
		}
		
		function index($params) {
			$this->template->set_file(array(
				'calendar' => 'calendar.tpl',
				'home_search_bar' => 'search_bar.tpl'
			));
			$actual_max_results = $_GET["results"]?$_GET["results"]:10;
			$this->template->set_block("calendar","page");
			$this->template->set_block('calendar','event_block');
			$this->template->set_block('calendar','day_event_block');
			$this->template->set_block('calendar','no_event_block');
			$this->template->set_block('calendar','type_option_block');
			$this->template->set_block('calendar','bar_block');
			$this->template->set_block('home_search_bar','search_bar');
			$this->template->set_var('lang_back',lang("back"));
			$this->template->set_var('href_back',$GLOBALS['phpgw_info']['mobiletemplate']->get_back_link());
			$this->template->set_var('lang_calendar',lang("Calendar"));
			$this->template->set_var('lang_search',lang("search"));
			$this->template->set_var('lang_more',lang("more"));
			$this->template->set_var('lang_events',lang("events"));
			$this->template->set_var('type',$params['type']);
			$this->template->set_var('dia',$params['dia']);
			$this->template->set_var('mes',$params['mes']);
			$this->template->set_var('ano',$params['ano']);
			$this->template->set_var('next_max_results',$actual_max_results+10);
			
			if($GLOBALS['phpgw']->session->appsession('mobile.layout','mobile')!="mini_desktop")
				$this->template->set_var('search',$this->template->fp('out','search_bar'));
			
			if(!function_exists("get_events")){
				function get_events($self, $accountId, $begin, $end) {
					$self->bocalendar->so->owner = $accountId;
					$self->bocalendar->so->open_box($accountId);
					$self->bocalendar->store_to_cache(
						array(
							'owner'	=> $accountId,
							'syear'  => date('Y',$begin),
							'smonth' => date('m',$begin),
							'sday'   => date('d',$begin),
							'eyear'  => date('Y',$end),
							'emonth' => date('m',$end),
							'eday'   => date('d',$end)
						)
					);
					
					$events = $self->bocalendar->cached_events;
					ksort($events);
				
					return $events;
				}
			}
			
			if(!function_exists("print_events")){	
				function print_events($self, $events) {
					foreach($events as $index=>$event)
					{
						$self->template->set_var('dd_class', (($index%2==0) ? "dt-azul" : "dt-branco") );	
						$vars = $self->bocalendar->event2array($event);
						$data = array (
							"title_field"		=> $vars['title']['field'],
							"title_data"		=> $vars['title']['data'],
							"startdate_data" 	=> substr($vars['startdate']['data'],13, 17),
							"enddate_data" 		=> substr($vars['enddate']['data'],13, 17),
							"location_field"		=> $vars['location']['field'],
							"location_data"	=> $vars['location']['data'] ? $vars['location']['data'] : "-",
							"description_field"		=> $vars['description']['field'],
							"description_data"	=> $vars['description']['data'] ? $vars['description']['data'] : "-"
						);
			
		    	 	$self->template->set_var($data);
						$self->template->parse('events_box','event_block',true);
					}				
				}
			}	
			
			if(!function_exists("print_events_header")){	
				function print_events_header($self, $day, $month, $year) {
					$day_of_week = $self->daysOfWeek[$GLOBALS['phpgw']->datetime->day_of_week($year,$month,$day)];
					$self->template->set_var("day",$day_of_week." - ".$day."/".$month."/".$year);
					$self->template->parse('events_box','day_event_block',true);
				}
			}
				
			$type = $_GET["type"];
			if(!$type) $type = "dia";
			$types = array("dia" => lang("view day"),"semana" => lang("view week"),"calendario" => lang("view calendar")); 
			
			foreach($types as $value=>$label)	{
				$this->template->set_var("value",$value);
				$this->template->set_var("label",$label);
				
				if($type == $value)
					$this->template->set_var("selected", "selected");
				else
					$this->template->set_var("selected", null);
					
				$this->template->parse('type_option_box','type_option_block',true);
			}
			
			$accountId = $GLOBALS['phpgw_info']['user']['account_id'];

			$day = $_GET["dia"];
			if(!$day) $day = $this->bocalendar->day;

			$month = $_GET["mes"];
			if(!$month) $month = $this->bocalendar->month;
			
			$year = $_GET["ano"];
			if(!$year) $year = $this->bocalendar->year;
			
			$this->template->set_var("lang_today", lang("today"));
			$this->template->set_var("today_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=dia&dia");
			$show_more_button = false;
			if($type==="semana") {
				$tstart = $GLOBALS['phpgw']->datetime->get_weekday_start($year, $month, $day) + $GLOBALS['phpgw']->datetime->tz_offset;
				$tstart -= $GLOBALS['phpgw']->datetime->tz_offset;
				$tstop = $tstart + 604800;
				$events = get_events($this, $accountId, $tstart, $tstop);
				$current_day_of_week = $GLOBALS['phpgw']->datetime->day_of_week($year, $month, $day);
				
				//descobrindo a semana anterior e a p´roxima
				$last_week = date("d-m-Y", strtotime("-7 day", mktime(0,0,0,$month,$day,$year) ) );
				$last_week = preg_split('/-/',$last_week);
				$next_week = date("d-m-Y", strtotime("+7 day", mktime(0,0,0,$month,$day,$year) ) );
				$next_week = preg_split('/-/',$next_week);
				
				//descobrind o primeiro dia da semana e o último
				$first_week_day = date("d-m-Y", strtotime("-".$current_day_of_week." day", mktime(0,0,0,$month,$day,$year) ) );
				$first_week_day = preg_split('/-/',$first_week_day);
				$last_week_day = date("d-m-Y", strtotime("+".(6-$current_day_of_week)." day", mktime(0,0,0,$month,$day,$year) ) );
				$last_week_day = preg_split('/-/',$last_week_day);
				
				//definindo a barra de navegação do calandário
				$this->template->set_var("before_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=semana&dia=".$last_week[0]."&mes=".$last_week[1]."&ano=".$last_week[2]);
				$this->template->set_var("current_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=semana&dia=".$day."&mes=".$month."&ano=".$year);
				$this->template->set_var("next_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=semana&dia=".$next_week[0]."&mes=".$next_week[1]."&ano=".$next_week[2]);
				$this->template->set_var("current_label", $first_week_day[0]."/".$first_week_day[1]."/".$first_week_day[2]." à ".$last_week_day[0]."/".$last_week_day[1]."/".$last_week_day[2]);
				$this->template->parse('events_box','bar_block',true);
				
				$total_events = 0; 
				if(!empty($events)) {
					foreach($events as $index=>$event)	{
						if($total_events>=$actual_max_results) {
							$show_more_button = true;
							break;
						}
						$event_year  = (int) substr($index,0,4);
						$event_month = (int) substr($index,4,2);
						$event_day   = (int) substr($index,6,2);
						$complete_day = $event_year.$this->common->complete_string($event_month,2,"R","0").$this->common->complete_string($event_day,2,"R","0");

						//Se a quantidade de eventos for maior que o máximo pego nessa interação, o botão de mais deve aparecer
						if(count($events[$complete_day])>$actual_max_results-$total_events) 
							$show_more_button = true;
						
						//Pego os eventos até completar o máximo da paginação
						$events_to_print = array_slice($events[$complete_day],0,$actual_max_results-$total_events);
						$total_events += count($events_to_print);
						
						print_events_header($this, $event_day, $event_month, $event_year);
						print_events($this, $events_to_print);
					}		
				} else {
					$this->template->set_var("msg_no_event", lang("Dont have event that week"));
					$this->template->parse('events_box','no_event_block',true);
				}				
						
			} elseif($type==="calendario") {
				$day_of_week_first_day = $GLOBALS['phpgw']->datetime->day_of_week($year, $month, "01");
				$last_day_of_month = date('t', mktime(0,0,0,$month,"01",$year));
				$last_day_of_before_month = date('t', mktime(0,0,0,$month-1,"01",$year));
				$last_month_year = date("m-Y", strtotime("-1 month", mktime(0,0,0,$month,"01",$year) ) );
				$last_month_year = preg_split('/-/',$last_month_year);
				$next_month_year = date("m-Y", strtotime("+1 month", mktime(0,0,0,$month,"01",$year) ) );
				$next_month_year = preg_split('/-/',$next_month_year);
				$today = date("d-m-Y");
				
				$days_of_calendar = array();
				
				//identificando os dias do mês anterior
				for($i = 0; $i < $day_of_week_first_day; ++$i) {
					$days_of_calendar[$i] = array("day" => ($last_day_of_before_month-$day_of_week_first_day+$i+1), "month" => $last_month_year[0], "year" => $last_month_year[1], "other_month" => true);					
				}
				
				//adicionando os dias do mês especificado
				for($i = 0; $i < ($last_day_of_month ); ++$i) {
					$days_of_calendar[$i+$day_of_week_first_day] = array("day" => $i+1, "month" => $month, "year" => $year, "other_month" => false);
				}
				
				//pegando todos os eventos a partir o menor dia visível do mês anterior
				$tstart = mktime(0,0,0,$last_month_year[0],($last_day_of_before_month-$day_of_week_first_day),$last_month_year[1]);
				$tstart -= $GLOBALS['phpgw']->datetime->tz_offset;
				$tstop = $tstart + (86400*sizeof($days_of_calendar)); //(24horas*60min*60seg*total de dias exibidos)
				$events = get_events($this, $accountId, $tstart, $tstop);
				
				//definindo os blocos a serem utilizados
				$this->template->set_block('calendar','calendar_header_begin_block');
				$this->template->set_block('calendar','calendar_header_end_block');
				$this->template->set_block('calendar','calendar_header_block');
				$this->template->set_block('calendar','calendar_day_block');
				$this->template->set_block('calendar','calendar_day_begin_block');
				$this->template->set_block('calendar','calendar_day_end_block');
				
				//definindo a barra de navegação do calandário
				$this->template->set_var("before_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=calendario&mes=".$last_month_year[0]."&ano=".$last_month_year[1]);
				$this->template->set_var("current_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=calendario&mes=".$month."&ano=".$year);
				$this->template->set_var("next_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=calendario&mes=".$next_month_year[0]."&ano=".$next_month_year[1]);
				$this->template->set_var("current_label", $month." / ".$year);
				$this->template->parse('events_box','bar_block',true);
				
				for($i = 0; $i < 7; ++$i) {
					$this->template->set_var("week_day", $this->shortDaysOfWeek[$i]);
					$this->template->parse('week_day_box','calendar_header_block',true);
				}
								
				$this->template->parse('calendar_box','calendar_header_begin_block',true);
				$this->template->parse('calendar_box','calendar_day_begin_block',true);
				
				foreach($days_of_calendar as $index=>$value) {
					$extra_class = "";
					$calendar_day = $value["day"];

					if($today === $value["day"]."-".$value["month"]."-".$value["year"])
						$extra_class .= " hoje";

					if($value["other_month"])
						$extra_class .= " outro_mes";
					
					$key = $value["year"].$this->common->complete_string($value["month"],2,"R","0").$this->common->complete_string($value["day"],2,"R","0");
					
					if( array_key_exists($key, $events ) ) {
						$this->template->set_var("qtd_commitment", sizeof($events[$key]));
					} else {
						$this->template->set_var("qtd_commitment", null);
					}

					$this->template->set_var("calendar_day", $calendar_day);
					
					$this->template->set_var("calendar_day_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=dia&dia=".$value["day"]."&mes=".$value["month"]."&ano=".$value["year"]);
					$this->template->set_var("extra_class", $extra_class);					
					$this->template->parse('calendar_box','calendar_day_block',true);
					
					if(($index+1)%7==0) {
						$this->template->parse('calendar_box','calendar_day_end_block',true);
						$this->template->parse('calendar_box','calendar_day_begin_block',true);
					}
				}
				$this->template->parse('calendar_box','calendar_day_end_block',true);
				$this->template->parse('calendar_box','calendar_header_end_block',true);
			
			} else 	{
				$tstart = mktime(0,0,0,$month,$day,$year);
				$tstart -= $GLOBALS['phpgw']->datetime->tz_offset;
				$tstop = $tstart + 86400; //(24horas*60min*60seg*1dia)
				$events = get_events($this, $accountId, $tstart, $tstop);
				
				$day = $this->common->complete_string($day,2,"R","0");
				$month = $this->common->complete_string($month,2,"R","0");
				
				//descobrind o primeiro dia da semana e o último
				$before_day = date("d-m-Y", strtotime("-1 day", mktime(0,0,0,$month,$day,$year) ) );
				$before_day = preg_split('/-/',$before_day);
				$next_day = date("d-m-Y", strtotime("+1 day", mktime(0,0,0,$month,$day,$year) ) );
				$next_day = preg_split('/-/',$next_day);				
				
				//definindo a barra de navegação do calandário
				$this->template->set_var("before_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=dia&dia=".$before_day[0]."&mes=".$before_day[1]."&ano=".$before_day[2]);
				$this->template->set_var("current_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=dia&dia=".$day."&mes=".$month."&ano=".$year);
				$this->template->set_var("next_link", "index.php?menuaction=mobile.ui_mobilecalendar.index&type=dia&dia=".$next_day[0]."&mes=".$next_day[1]."&ano=".$next_day[2]);
				$this->template->set_var("current_label", $this->daysOfWeek[$GLOBALS['phpgw']->datetime->day_of_week($year,$month,$day)]." - ".$day."/".$month."/".$year);
				$this->template->parse('events_box','bar_block',true);				

				if(!empty($events[$year.$month.$day])) {
					print_events($this, array_slice($events[$year.$month.$day],0,$actual_max_results,true));
					if(count($events[$year.$month.$day])>$actual_max_results) 
							$show_more_button = true;
				}
				else {
					$this->template->set_var("msg_no_event", lang("Dont have event that day"));
					$this->template->parse('events_box','no_event_block',true);
				}
			}
			if($show_more_button)
				$this->template->set_var('show_more','block');
			else
				$this->template->set_var('show_more','none');
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out','page'));
		}
	}
	
?>
