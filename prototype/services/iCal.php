<?php

//require_once ROOTPATH . '/plugins/icalcreator/iCalUtilityFunctions.class.php';
require_once ROOTPATH . '/plugins/icalcreator/iCalcreator.class.php';
require_once ROOTPATH . '/modules/calendar/constants.php';

use prototype\api\Config as Config;

//TODO:Timeout request
set_time_limit(600);

class iCal implements Formatter {

    static $timezonesMap = array('(GMT-12.00) International Date Line West' => 'Etc/GMT+12', '(GMT-11.00) Midway Island / Samoa' => 'Pacific/Midway', '(GMT-10.00) Hawaii' => 'Pacific/Honolulu', '(GMT-09.00) Alaska' => 'America/Anchorage', '(GMT-08.00) Pacific Time (US & Canada) / Tijuana' => 'America/Los_Angeles', '(GMT-08.00) Tijuana / Baja California' => 'America/Tijuana', '(GMT-07.00) Arizona' => 'America/Phoenix', '(GMT-07.00) Chihuahua / La Paz / Mazatlan - Old' => 'America/Chihuahua', '(GMT-07.00) Mountain Time (US & Canada)' => 'America/Denver', '(GMT-06.00) Central America' => 'America/Guatemala', '(GMT-06.00) Central Time (US & Canada)' => 'America/Chicago', '(GMT-06.00) Guadalajara / Mexico City / Monterrey - Old' => 'America/Mexico_City', '(GMT-06.00) Saskatchewan' => 'America/Regina', '(GMT-05.00) Bogota / Lima / Quito' => 'America/Bogota', '(GMT-05.00) Eastern Time (US & Canada)' => 'America/New_York', '(GMT-05.00) Indiana (East)' => 'America/Indiana/Indianapolis', '(GMT-04.30) Caracas' => 'America/Caracas', '(GMT-04.00) Atlantic Time (Canada)' => 'America/Halifax', '(GMT-04.00) Georgetown' => 'America/Guyana', '(GMT-04.00) Caracas / La Paz' => 'America/La_Paz', '(GMT-04.00) Manaus' => 'America/Manaus', '(GMT-04.00) Santiago' => 'America/Santiago', '(GMT-03.30) Newfoundland' => 'America/St_Johns', '(GMT-03.00) Brasilia' => 'America/Sao_Paulo', 'GMT -0300 (Standard) / GMT -0200 (Daylight)' => 'America/Sao_Paulo', '(GMT-03.00) Buenos Aires / Georgetown' => 'America/Argentina/Buenos_Aires', '(GMT-03.00) Greenland' => 'America/Godthab', '(GMT-03.00) Montevideo' => 'America/Montevideo', '(GMT-02.00) Mid-Atlantic' => 'Atlantic/South_Georgia', '(GMT-01.00) Azores' => 'Atlantic/Azores', '(GMT-01.00) Cape Verde Is.' => 'Atlantic/Cape_Verde', '(GMT) Casablanca' => 'Africa/Casablanca', '(GMT) Greenwich Mean Time - Dublin / Edinburgh / Lisbon / London' => 'Europe/London', '(GMT) Casablanca / Monrovia' => 'Africa/Monrovia', '(GMT+01.00) Amsterdam / Berlin / Bern / Rome / Stockholm / Vienna' => 'Europe/Berlin', '(GMT+01.00) Belgrade / Bratislava / Budapest / Ljubljana / Prague' => 'Europe/Belgrade', '(GMT+01.00) Brussels / Copenhagen / Madrid / Paris' => 'Europe/Brussels', '(GMT+01.00) Sarajevo / Skopje / Warsaw / Zagreb' => 'Europe/Warsaw', '(GMT+01.00) West Central Africa' => 'Africa/Algiers', '(GMT+02.00) Windhoek' => 'Africa/Windhoek', '(GMT+02.00) Amman' => 'Asia/Amman', '(GMT+02.00) Bucharest' => 'Europe/Athens', '(GMT+02.00) Beirut' => 'Asia/Beirut', '(GMT+02.00) Cairo' => 'Africa/Cairo', '(GMT+02.00) Harare / Pretoria' => 'Africa/Harare', '(GMT+02.00) Helsinki / Kyiv / Riga / Sofia / Tallinn / Vilnius' => 'Europe/Helsinki', '(GMT+02.00) Jerusalem' => 'Asia/Jerusalem', '(GMT+02.00) Minsk' => 'Europe/Minsk', '(GMT+03.00) Baghdad' => 'Asia/Baghdad', '(GMT+03.00) Kuwait / Riyadh' => 'Asia/Kuwait', '(GMT+03.00) Moscow / St. Petersburg / Volgograd' => 'Europe/Moscow', '(GMT+03.00) Nairobi' => 'Africa/Nairobi', '(GMT+04.00) Caucasus Standard Time' => 'Asia/Tbilisi', '(GMT+03.30) Tehran' => 'Asia/Tehran', '(GMT+04.00) Abu Dhabi / Muscat' => 'Asia/Muscat', '(GMT+04.00) Baku / Tbilisi / Yerevan' => 'Asia/Baku', '(GMT+04.00) Yerevan' => 'Asia/Yerevan', '(GMT+04.30) Kabul' => 'Asia/Kabul', '(GMT+05.00) Ekaterinburg' => 'Asia/Yekaterinburg', '(GMT+05.00) Islamabad / Karachi / Tashkent' => 'Asia/Karachi', '(GMT+05.00) Tashkent' => 'Asia/Tashkent', '(GMT+05.30) Chennai / Kolkata / Mumbai / New Delhi' => 'Asia/Kolkata', '(GMT+06.00) Sri Jayawardenepura' => 'Asia/Colombo', '(GMT+05.45) Kathmandu' => 'Asia/Katmandu', '(GMT+06.00) Almaty / Novosibirsk' => 'Asia/Novosibirsk', '(GMT+06.00) Astana / Dhaka' => 'Asia/Dhaka', '(GMT+06.30) Rangoon' => 'Asia/Rangoon', '(GMT+07.00) Bangkok / Hanoi / Jakarta' => 'Asia/Bangkok', '(GMT+07.00) Krasnoyarsk' => 'Asia/Krasnoyarsk', '(GMT+08.00) Beijing / Chongqing / Hong Kong / Urumqi' => 'Asia/Hong_Kong', '(GMT+08.00) Irkutsk / Ulaan Bataar' => 'Asia/Irkutsk', '(GMT+08.00) Kuala Lumpur / Singapore' => 'Asia/Kuala_Lumpur', '(GMT+08.00) Perth' => 'Australia/Perth', '(GMT+08.00) Taipei' => 'Asia/Taipei', '(GMT+09.00) Osaka / Sapporo / Tokyo' => 'Asia/Tokyo', '(GMT+09.00) Seoul' => 'Asia/Seoul', '(GMT+09.00) Yakutsk' => 'Asia/Yakutsk', '(GMT+09.30) Adelaide' => 'Australia/Adelaide', '(GMT+09.30) Darwin' => 'Australia/Darwin', '(GMT+10.00) Brisbane' => 'Australia/Brisbane', '(GMT+10.00) Canberra / Melbourne / Sydney' => 'Australia/Sydney', '(GMT+10.00) Guam / Port Moresby' => 'Pacific/Guam', '(GMT+10.00) Hobart' => 'Australia/Hobart', '(GMT+10.00) Vladivostok' => 'Asia/Vladivostok', '(GMT+11.00) Magadan / Solomon Is. / New Caledonia' => 'Asia/Magadan', '(GMT+12.00) Auckland / Wellington' => 'Pacific/Auckland', '(GMT+12.00) Fiji / Kamchatka / Marshall Is.' => 'Pacific/Fiji', '(GMT+13.00) Nuku\'alofa' => 'Pacific/Tongatapu', 'E. South America Standard Time' => 'America/Sao_Paulo', 'E. South America' => 'America/Sao_Paulo');
//    static $timezonesOutlookID = array('Europe/London' => '1' ,'Europe/Brussels' => '3' ,'Europe/Berlin' => '4' ,'America/New_York' => '5' ,'Europe/Belgrade' => '6' ,'Europe/Minsk' => '7' ,'America/Sao_Paulo' => '8' ,'America/Halifax' => '9' ,'America/New_York' => '10' ,'America/Chicago' => '11' ,'America/Denver' => '12' ,'America/Los_Angeles' => '13' ,'America/Anchorage' => '14' ,'Pacific/Honolulu' => '15' ,'Pacific/Midway' => '16' ,'Pacific/Auckland' => '17' ,'Australia/Brisbane' => '18' ,'Australia/Adelaide' => '19' ,'Asia/Tokyo' => '20' ,'Asia/Hong_Kong' => '21' ,'Asia/Bangkok' => '22' ,'Asia/Kolkata' => '23' ,'Asia/Muscat' => '24' ,'Asia/Tehran' => '25' ,'Asia/Baghdad' => '26' ,'Asia/Jerusalem' => '27' ,'America/St_Johns' => '28' ,'Atlantic/Azores' => '29' ,'Atlantic/South_Georgia' => '30' ,'Africa/Casablanca' => '31' ,'America/La_Paz' => '33' ,'America/Indiana/Indianapolis' => '34' ,'America/Bogota' => '35' ,'America/Regina' => '36' ,'America/Mexico_City' => '37' ,'America/Phoenix' => '38' ,'Etc/GMT+12' => '39' ,'Pacific/Fiji' => '40' ,'Asia/Magadan' => '41' ,'Australia/Hobart' => '42' ,'Pacific/Guam' => '43' ,'Australia/Darwin' => '44' ,'Asia/Hong_Kong' => '45' ,'Asia/Novosibirsk' => '46' ,'Asia/Kabul' => '48' ,'Africa/Cairo' => '49' ,'Africa/Harare' => '50' ,'Europe/Moscow' => '51' ,'Australia/Sydney' => '52' ,'Australia/Sydney' => '53' ,'Australia/Adelaide' => '54' ,'Australia/Hobart' => '55' ,'America/Santiago' => '56' ,'Australia/Pert' => '57' ,'America/Tijuana' => '59' ,'Asia/Tbilisi' => '60' ,'Australia/Sydney' => '61' ,'America/Caracas' => '62' ,'Asia/Amman' => '63' ,'Asia/Baku' => '64' ,'Asia/Yerevan' => '65' ,'Europe/Moscow' => '66' ,'America/Argentina/Buenos_Aires' => '67' ,'America/Montevideo' => '72');  
    static $suportedTimzones = array('Africa/Abidjan', 'Africa/Accra', 'Africa/Addis_Ababa', 'Africa/Algiers', 'Africa/Asmara', 'Africa/Asmera', 'Africa/Bamako', 'Africa/Bangui', 'Africa/Banjul', 'Africa/Bissau', 'Africa/Blantyre', 'Africa/Brazzaville', 'Africa/Bujumbura', 'Africa/Cairo', 'Africa/Casablanca', 'Africa/Ceuta', 'Africa/Conakry', 'Africa/Dakar', 'Africa/Dar_es_Salaam', 'Africa/Djibouti', 'Africa/Douala', 'Africa/El_Aaiun', 'Africa/Freetown', 'Africa/Gaborone', 'Africa/Harare', 'Africa/Johannesburg', 'Africa/Kampala', 'Africa/Khartoum', 'Africa/Kigali', 'Africa/Kinshasa', 'Africa/Lagos', 'Africa/Libreville', 'Africa/Lome', 'Africa/Luanda', 'Africa/Lubumbashi', 'Africa/Lusaka', 'Africa/Malabo', 'Africa/Maputo', 'Africa/Maseru', 'Africa/Mbabane', 'Africa/Mogadishu', 'Africa/Monrovia', 'Africa/Nairobi', 'Africa/Ndjamena', 'Africa/Niamey', 'Africa/Nouakchott', 'Africa/Ouagadougou', 'Africa/Porto-Novo', 'Africa/Sao_Tome', 'Africa/Timbuktu', 'Africa/Tripoli', 'Africa/Tunis', 'Africa/Windhoek', 'America/Adak', 'America/Anchorage', 'America/Anguilla', 'America/Antigua', 'America/Araguaina', 'America/Argentina/Buenos_Aires', 'America/Argentina/Catamarca', 'America/Argentina/ComodRivadavia', 'America/Argentina/Cordoba', 'America/Argentina/Jujuy', 'America/Argentina/La_Rioja', 'America/Argentina/Mendoza', 'America/Argentina/Rio_Gallegos', 'America/Argentina/Salta', 'America/Argentina/San_Juan', 'America/Argentina/San_Luis', 'America/Argentina/Tucuman', 'America/Argentina/Ushuaia', 'America/Aruba', 'America/Asuncion', 'America/Atikokan', 'America/Atka', 'America/Bahia', 'America/Barbados', 'America/Belem', 'America/Belize', 'America/Blanc-Sablon', 'America/Boa_Vista', 'America/Bogota', 'America/Boise', 'America/Buenos_Aires', 'America/Cambridge_Bay', 'America/Campo_Grande', 'America/Cancun', 'America/Caracas', 'America/Catamarca', 'America/Cayenne', 'America/Cayman', 'America/Chicago', 'America/Chihuahua', 'America/Coral_Harbour', 'America/Cordoba', 'America/Costa_Rica', 'America/Cuiaba', 'America/Curacao', 'America/Danmarkshavn', 'America/Dawson_Creek', 'America/Dawson', 'America/Denver', 'America/Detroit', 'America/Dominica', 'America/Edmonton', 'America/Eirunepe', 'America/El_Salvador', 'America/Ensenada', 'America/Fort_Wayne', 'America/Fortaleza', 'America/Glace_Bay', 'America/Godthab', 'America/Goose_Bay', 'America/Grand_Turk', 'America/Grenada', 'America/Guadeloupe', 'America/Guatemala', 'America/Guayaquil', 'America/Guyana', 'America/Halifax', 'America/Havana', 'America/Hermosillo', 'America/Indiana/Indianapolis', 'America/Indiana/Knox', 'America/Indiana/Marengo', 'America/Indiana/Petersburg', 'America/Indiana/Tell_City', 'America/Indiana/Vevay', 'America/Indiana/Vincennes', 'America/Indiana/Winamac', 'America/Indianapolis', 'America/Inuvik', 'America/Iqaluit', 'America/Jamaica', 'America/Jujuy', 'America/Juneau', 'America/Kentucky/Louisville', 'America/Kentucky/Monticello', 'America/Knox_IN', 'America/La_Paz', 'America/Lima', 'America/Los_Angeles', 'America/Louisville', 'America/Maceio', 'America/Managua', 'America/Manaus', 'America/Marigot', 'America/Martinique', 'America/Matamoros', 'America/Mazatlan', 'America/Mendoza', 'America/Menominee', 'America/Merida', 'America/Mexico_City', 'America/Miquelon', 'America/Moncton', 'America/Monterrey', 'America/Montevideo', 'America/Montreal', 'America/Montserrat', 'America/Nassau', 'America/New_York', 'America/Nipigon', 'America/Nome', 'America/Noronha', 'America/North_Dakota/Center', 'America/North_Dakota/New_Salem', 'America/Ojinaga', 'America/Panama', 'America/Pangnirtung', 'America/Paramaribo', 'America/Phoenix', 'America/Port_of_Spain', 'America/Port-au-Prince', 'America/Porto_Acre', 'America/Porto_Velho', 'America/Puerto_Rico', 'America/Rainy_River', 'America/Rankin_Inlet', 'America/Recife', 'America/Regina', 'America/Resolute', 'America/Rio_Branco', 'America/Rosario', 'America/Santa_Isabel', 'America/Santarem', 'America/Santiago', 'America/Santo_Domingo', 'America/Sao_Paulo', 'America/Scoresbysund', 'America/Shiprock', 'America/St_Barthelemy', 'America/St_Johns', 'America/St_Kitts', 'America/St_Lucia', 'America/St_Thomas', 'America/St_Vincent', 'America/Swift_Current', 'America/Tegucigalpa', 'America/Thule', 'America/Thunder_Bay', 'America/Tijuana', 'America/Toronto', 'America/Tortola', 'America/Vancouver', 'America/Virgin', 'America/Whitehorse', 'America/Winnipeg', 'America/Yakutat', 'America/Yellowknife', 'Antarctica/Casey', 'Antarctica/Davis', 'Antarctica/DumontDUrville', 'Antarctica/Macquarie', 'Antarctica/Mawson', 'Antarctica/McMurdo', 'Antarctica/Palmer', 'Antarctica/Rothera', 'Antarctica/South_Pole', 'Antarctica/Syowa', 'Antarctica/Vostok', 'Arctic/Longyearbyen', 'Asia/Aden', 'Asia/Almaty', 'Asia/Amman', 'Asia/Anadyr', 'Asia/Aqtau', 'Asia/Aqtobe', 'Asia/Ashgabat', 'Asia/Ashkhabad', 'Asia/Baghdad', 'Asia/Bahrain', 'Asia/Baku', 'Asia/Bangkok', 'Asia/Beirut', 'Asia/Bishkek', 'Asia/Brunei', 'Asia/Calcutta', 'Asia/Choibalsan', 'Asia/Chongqing', 'Asia/Chungking', 'Asia/Colombo', 'Asia/Dacca', 'Asia/Damascus', 'Asia/Dhaka', 'Asia/Dili', 'Asia/Dubai', 'Asia/Dushanbe', 'Asia/Gaza', 'Asia/Harbin', 'Asia/Ho_Chi_Minh', 'Asia/Hong_Kong', 'Asia/Hovd', 'Asia/Irkutsk', 'Asia/Istanbul', 'Asia/Jakarta', 'Asia/Jayapura', 'Asia/Jerusalem', 'Asia/Kabul', 'Asia/Kamchatka', 'Asia/Karachi', 'Asia/Kashgar', 'Asia/Kathmandu', 'Asia/Katmandu', 'Asia/Kolkata', 'Asia/Krasnoyarsk', 'Asia/Kuala_Lumpur', 'Asia/Kuching', 'Asia/Kuwait', 'Asia/Macao', 'Asia/Macau', 'Asia/Magadan', 'Asia/Makassar', 'Asia/Manila', 'Asia/Muscat', 'Asia/Nicosia', 'Asia/Novokuznetsk', 'Asia/Novosibirsk', 'Asia/Omsk', 'Asia/Oral', 'Asia/Phnom_Penh', 'Asia/Pontianak', 'Asia/Pyongyang', 'Asia/Qatar', 'Asia/Qyzylorda', 'Asia/Rangoon', 'Asia/Riyadh', 'Asia/Saigon', 'Asia/Sakhalin', 'Asia/Samarkand', 'Asia/Seoul', 'Asia/Shanghai', 'Asia/Singapore', 'Asia/Taipei', 'Asia/Tashkent', 'Asia/Tbilisi', 'Asia/Tehran', 'Asia/Tel_Aviv', 'Asia/Thimbu', 'Asia/Thimphu', 'Asia/Tokyo', 'Asia/Ujung_Pandang', 'Asia/Ulaanbaatar', 'Asia/Ulan_Bator', 'Asia/Urumqi', 'Asia/Vientiane', 'Asia/Vladivostok', 'Asia/Yakutsk', 'Asia/Yekaterinburg', 'Asia/Yerevan', 'Atlantic/Azores', 'Atlantic/Bermuda', 'Atlantic/Canary', 'Atlantic/Cape_Verde', 'Atlantic/Faeroe', 'Atlantic/Faroe', 'Atlantic/Jan_Mayen', 'Atlantic/Madeira', 'Atlantic/Reykjavik', 'Atlantic/South_Georgia', 'Atlantic/St_Helena', 'Atlantic/Stanley', 'Australia/ACT', 'Australia/Adelaide', 'Australia/Brisbane', 'Australia/Broken_Hill', 'Australia/Canberra', 'Australia/Currie', 'Australia/Darwin', 'Australia/Eucla', 'Australia/Hobart', 'Australia/LHI', 'Australia/Lindeman', 'Australia/Lord_Howe', 'Australia/Melbourne', 'Australia/NSW', 'Australia/North', 'Australia/Perth', 'Australia/Queensland', 'Australia/South', 'Australia/Sydney', 'Australia/Tasmania', 'Australia/Victoria', 'Australia/West', 'Australia/Yancowinna', 'Europe/Amsterdam', 'Europe/Andorra', 'Europe/Athens', 'Europe/Belfast', 'Europe/Belgrade', 'Europe/Berlin', 'Europe/Bratislava', 'Europe/Brussels', 'Europe/Bucharest', 'Europe/Budapest', 'Europe/Chisinau', 'Europe/Copenhagen', 'Europe/Dublin', 'Europe/Gibraltar', 'Europe/Guernsey', 'Europe/Helsinki', 'Europe/Isle_of_Man', 'Europe/Istanbul', 'Europe/Jersey', 'Europe/Kaliningrad', 'Europe/Kiev', 'Europe/Lisbon', 'Europe/Ljubljana', 'Europe/London', 'Europe/Luxembourg', 'Europe/Madrid', 'Europe/Malta', 'Europe/Mariehamn', 'Europe/Minsk', 'Europe/Monaco', 'Europe/Moscow', 'Europe/Nicosia', 'Europe/Oslo', 'Europe/Paris', 'Europe/Podgorica', 'Europe/Prague', 'Europe/Riga', 'Europe/Rome', 'Europe/Samara', 'Europe/San_Marino', 'Europe/Sarajevo', 'Europe/Simferopol', 'Europe/Skopje', 'Europe/Sofia', 'Europe/Stockholm', 'Europe/Tallinn', 'Europe/Tirane', 'Europe/Tiraspol', 'Europe/Uzhgorod', 'Europe/Vaduz', 'Europe/Vatican', 'Europe/Vienna', 'Europe/Vilnius', 'Europe/Volgograd', 'Europe/Warsaw', 'Europe/Zagreb', 'Europe/Zaporozhye', 'Europe/Zurich', 'Indian/Antananarivo', 'Indian/Chagos', 'Indian/Christmas', 'Indian/Cocos', 'Indian/Comoro', 'Indian/Kerguelen', 'Indian/Mahe', 'Indian/Maldives', 'Indian/Mauritius', 'Indian/Mayotte', 'Indian/Reunion', 'Pacific/Apia', 'Pacific/Auckland', 'Pacific/Chatham', 'Pacific/Easter', 'Pacific/Efate', 'Pacific/Enderbury', 'Pacific/Fakaofo', 'Pacific/Fiji', 'Pacific/Funafuti', 'Pacific/Galapagos', 'Pacific/Gambier', 'Pacific/Guadalcanal', 'Pacific/Guam', 'Pacific/Honolulu', 'Pacific/Johnston', 'Pacific/Kiritimati', 'Pacific/Kosrae', 'Pacific/Kwajalein', 'Pacific/Majuro', 'Pacific/Marquesas', 'Pacific/Midway', 'Pacific/Nauru', 'Pacific/Niue', 'Pacific/Norfolk', 'Pacific/Noumea', 'Pacific/Pago_Pago', 'Pacific/Palau', 'Pacific/Pitcairn', 'Pacific/Ponape', 'Pacific/Port_Moresby', 'Pacific/Rarotonga', 'Pacific/Saipan', 'Pacific/Samoa', 'Pacific/Tahiti', 'Pacific/Tarawa', 'Pacific/Tongatapu', 'Pacific/Truk', 'Pacific/Wake', 'Pacific/Wallis', 'Pacific/Yap', 'UTC');

    public function format($data, $params = false) 
    {
		$timezones = array_flip(self::$timezonesMap);
		$sytemTimezone = (date_default_timezone_get()) ? date_default_timezone_get() : 'America/Sao_Paulo';
		$params['defaultTZI'] = self::nomalizeTZID((isset($params['defaultTZI']) && $params['defaultTZI'] != 'null') ? $params['defaultTZI'] : $sytemTimezone );
		$params['X-WR-TIMEZONE'] = isset($timezones[$params['defaultTZI']]) ? $timezones[$params['defaultTZI']] : $params['defaultTZI'];
		
		return (isset($params['compatible']) && $params['compatible']) ?
					array('ical' => $this->createIcal($data, $params) , 'compatible' => $this->createCompatibleIcal($data, $params)) : 
					$this->createIcal($data, $params);

    }

    protected function createIcal($data, $params = false )
    {
    	$ical = new icalCreatorVcalendar();
		$ical->setProperty('method', isset($params['method']) ? $params['method'] : 'PUBLISH' );

		/*
		 * Seta propiedades obrigatorias para alguns softwares (Outlook)
		 */
		$ical->setProperty('x-wr-calname', 'Calendar Expresso');
		$ical->setProperty('X-WR-CALDESC', 'Calendar Expresso');
		$ical->setProperty('X-WR-TIMEZONE', $params['X-WR-TIMEZONE']);

		foreach ($data as $i => $v) {

		    switch ($v['type']) {
			case EVENT_ID:
			    $vevent = $ical->newComponent('vevent');

			    $vevent->setProperty('summary', $v['summary']);
			    $vevent->setProperty('description', isset($v['description']) ? $v['description'] : '');
			    $vevent->setProperty('location', $v['location']);
			    $vevent->setProperty('tranp', (isset($v['tranparent']) && $v['tranparent'] == TRANSP_TRANSPARENT ) ? 'TRANSPARENT' : 'OPAQUE' );

			    $timezone = new DateTimeZone('UTC');
			    $apTimezone = self::nomalizeTZID(( isset($v['timezone']) && $v['timezone'] != 'null' ) ? $v['timezone'] : $params['defaultTZI']);
			    $apTimezoneOBJ = new DateTimeZone($apTimezone);

			    $sTime = new DateTime('@' . (int) ($v['startTime'] / 1000), $timezone);
			    $sTime->setTimezone($apTimezoneOBJ);

                $eTime = new DateTime('@' . (int) ($v['endTime'] / 1000), $timezone);
                $eTime->setTimezone($apTimezoneOBJ);

			    if (( isset($v['repeat']) ) && ( isset($v['repeat']['frequency']) && $v['repeat']['frequency'] && $v['repeat']['frequency'] != 'none' )) 
				    $vevent->setProperty('rrule', $this->formatIcalRepeat($v['repeat']));

                $vevent->setProperty('dtstamp', array('timestamp' => ($v['dtstamp'] / 1000) ));

			    if (isset($v['allDay']) && $v['allDay'] == 1)
                {
                    $vevent->setProperty('dtstart', $sTime->format(DATE_RFC822), array("VALUE" => "DATE"));
                    $vevent->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'TRUE');
                    $vevent->setProperty('dtend', $eTime->format(DATE_RFC822), array("VALUE" => "DATE"));
                    $vevent->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'TRUE');
			    } else
                {
                    $vevent->setProperty('dtstart', $sTime->format(DATE_RFC822), array('TZID' => $apTimezone));
                    $vevent->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'FALSE');
                    $vevent->setProperty('dtend', $eTime->format(DATE_RFC822), array('TZID' => $apTimezone));
                    $vevent->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'FALSE');
			    }
			   		    
			    if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
			    	$participants = $v['participants'];
			    else
					$participants = Controller::find(array('concept' => 'participant'), false, array('filter' => array('=', 'schedulable', $v['id'])));
			    
				if (is_array($participants) && count($participants) > 0)
					foreach ($participants as $ii => $vv) {
					
						if(isset($participants[$ii]['user']) && !is_array($participants[$ii]['user']))
						{
							if ($vv['isExternal'] == 1)
								$participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user'], 'service' => 'PostgreSQL'));
							else
								$participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user']));
						}
					
						if ($participants[$ii]['user']['id'] == Config::me('uidNumber'))
						{
							$alarms = (isset($participants[$ii]['alarms'])) ? $participants[$ii]['alarms'] : Controller::find(array('concept' => 'alarm'), null, array('filter' => array('AND', array('=', 'participant', $vv['id']), array('=', 'schedulable', $v['id']))));
							if(is_array($alarms))
								self::createAlarms($alarms, $vevent);
						}
					
					}
			    
			    if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
					$this->createAttendee($v['participants'], $vevent);

			    if (isset($v['attachments']) && is_array($v['attachments']) && count($v['attachments']) > 0)
					$this->createAttachment($v['attachments'], $vevent);

			    $vevent->setProperty('uid', $v['uid']);

                $timezoneDayligth = Controller::read(array('concept' => 'timezones'), null, array('filter' => array('=', 'tzid', $apTimezone)));

                if(!empty( $timezoneDayligth ) && count( $timezoneDayligth ) > 0){

                    if(array_key_exists(0, $timezoneDayligth))
                        $timezoneDayligth = $timezoneDayligth[0];

                    date_default_timezone_set('UTC');

                    require_once ROOTPATH . '/plugins/when/When.php';

                    $r = new When();

                    $start = new DateTime('1970-01-01 '.$timezoneDayligth['standardDtstart']);

                    $r = new When();
                    $r->recur($start, $timezoneDayligth['standardFrequency'])
                        ->until($start->modify('+1 years'))
                        ->bymonth(array( $timezoneDayligth['standardBymonth'] ))
                        ->byday(array(  $timezoneDayligth['daylightByday'] ));

                   $date = $r->next();

                    $timezone = $ical->newComponent('vtimezone');
                    $timezone->setProperty('tzid',$apTimezone );

                    $standard  = $timezone->newComponent( "standard" );
                    $standard->setProperty( "tzoffsetfrom", $timezoneDayligth['standardFrom'] );
                    $standard->setProperty( "tzoffsetto", $timezoneDayligth['standardTo'] );


                    $standard->setProperty( "dtstart", $date->format(DATE_RFC822), array("VALUE" => "DATE") );

                    $rrule = array(
                        'FREQ' => $timezoneDayligth['standardFrequency'],
                        'BYMONTH' =>  $timezoneDayligth['standardBymonth'],
                        'BYday' => array(preg_replace("/[^0-9]/", "", $timezoneDayligth['standardByday']),  "DAY" => preg_replace("/[^a-zA-Z\s]/", "", $timezoneDayligth['standardByday']))
                    );

                    $standard->setProperty('rrule', $rrule);

                    $daylight  = $timezone->newComponent( "daylight" );

                    $daylight->setProperty( "tzoffsetfrom", $timezoneDayligth['daylightFrom'] );
                    $daylight->setProperty( "tzoffsetto", $timezoneDayligth['daylightTo'] );


                    $start = new DateTime('1970-01-01 '.$timezoneDayligth['daylightDtstart']);

                    $r->recur($start, $timezoneDayligth['daylightFrequency'])
                        ->until($start->modify('+1 years'))
                        ->bymonth(array( $timezoneDayligth['daylightBymonth'] ))
                        ->byday(array(  $timezoneDayligth['daylightByday'] ));

                    $date = $r->next();

                    $daylight->setProperty( "dtstart", $date->format(DATE_RFC822), array("VALUE" => "DATE") );

                    $rrule = array(
                        'FREQ' => $timezoneDayligth['daylightFrequency'],
                        'BYMONTH' =>  $timezoneDayligth['daylightBymonth'],
                        'BYday' => array(preg_replace("/[^0-9]/", "", $timezoneDayligth['daylightByday']),  "DAY" => preg_replace("/[^a-zA-Z\s]/", "", $timezoneDayligth['daylightByday']))
                    );

                    $daylight->setProperty('rrule', $rrule);
                }

			    break;
            case TODO_ID:

                $todo = $ical->newComponent('todo');

                $todo->setProperty('summary', $v['summary']);
                $todo->setProperty('description', isset($v['description']) ? $v['description'] : '');
                $todo->setProperty('priority', $v['priority']);
                $todo->setProperty('percent-complete', $v['percentage']);
                $todo->setProperty('status', $this->_getStatusTodo($v['status']));

                $timezone = new DateTimeZone('UTC');
                $apTimezone = self::nomalizeTZID(( isset($v['timezone']) && $v['timezone'] != 'null' ) ? $v['timezone'] : $params['defaultTZI']);
                $apTimezoneOBJ = new DateTimeZone($apTimezone);

                $sTime = new DateTime('@' . (int) ($v['startTime'] / 1000), $timezone);
                $sTime->setTimezone($apTimezoneOBJ);
                $eTime = new DateTime('@' . (int) ($v['endTime'] / 1000), $timezone);
                $eTime->setTimezone($apTimezoneOBJ);

                $todo->setProperty('dtstamp', array('timestamp' => ($v['dtstamp'] / 1000) ));

                if (isset($v['allDay']) && $v['allDay'] == 1) {
                    $todo->setProperty('dtstart', $sTime->format(DATE_RFC822), array("VALUE" => "DATE"));
                    $todo->setProperty('dtend', $eTime->format(DATE_RFC822), array("VALUE" => "DATE"));
                    //$todo->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'TRUE');
                } else {
                    $todo->setProperty('dtstart', $sTime->format(DATE_RFC822), array('TZID' => $apTimezone));
                    $todo->setProperty('dtend', $eTime->format(DATE_RFC822), array('TZID' => $apTimezone));
                    //$todo->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'FALSE');
                }

                if(isset($v['due']) && $v['due'] != '' && (int)$v['due'] > 0){
                    $dueTime = new DateTime('@' . (int) ($v['due'] / 1000), $timezone);
                    $dueTime->setTimezone($apTimezoneOBJ);

                    $todo->setProperty('due', $dueTime->format(DATE_RFC822), array('TZID' => $apTimezone));
                    $todo->setProperty('dueTime', $dueTime->format(DATE_RFC822), array('TZID' => $apTimezone));
                }                
                       
                if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
                    $participants = $v['participants'];
                else
                    $participants = Controller::find(array('concept' => 'participant'), false, array('filter' => array('=', 'schedulable', $v['id'])));
                
                if (is_array($participants) && count($participants) > 0)
                    foreach ($participants as $ii => $vv) {
                    
                        if(isset($participants[$ii]['user']) && !is_array($participants[$ii]['user']))
                        {
                            if ($vv['isExternal'] == 1)
                                $participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user'], 'service' => 'PostgreSQL'));
                            else
                                $participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user']));
                        }
                    
                        if ($participants[$ii]['user']['id'] == Config::me('uidNumber'))
                        {
                            $alarms = (isset($participants[$ii]['alarms'])) ? $participants[$ii]['alarms'] : Controller::find(array('concept' => 'alarm'), null, array('filter' => array('AND', array('=', 'participant', $vv['id']), array('=', 'schedulable', $v['id']))));
                            if(is_array($alarms))
                                self::createAlarms($alarms, $todo);
                        }
                    
                    }

                if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
                    $this->createAttendee($v['participants'], $todo);

                if (isset($v['attachments']) && is_array($v['attachments']) && count($v['attachments']) > 0)
                    $this->createAttachment($v['attachments'], $todo);

                $todo->setProperty('uid', $v['uid']);  
                
                break;
			default:
			    break;
		    }
		}
		return $ical->createCalendar();
    }

    protected function createCompatibleIcal($data, $params = false )
    {
    	$ical = new icalCreatorVcalendar();
		$ical->setProperty('method', isset($params['method']) ? $params['method'] : 'PUBLISH' );

		/*
		 * Seta propiedades obrigatorias para alguns softwares (Outlook)
		 */
		$ical->setProperty('x-wr-calname', 'Calendar Expresso');
		$ical->setProperty('X-WR-CALDESC', 'Calendar Expresso');
		$ical->setProperty('X-WR-TIMEZONE', $params['X-WR-TIMEZONE']);

		foreach ($data as $i => $v) {

		    switch ($v['type']) {
			case EVENT_ID:
			    $vevent = $ical->newComponent('vevent');

			    $vevent->setProperty('summary', $v['summary']);
			    $vevent->setProperty('description', isset($v['description']) ? $v['description'] : '');
			    $vevent->setProperty('location', $v['location']);
			    $vevent->setProperty('tranp', (isset($v['tranparent']) && $v['tranparent'] == TRANSP_TRANSPARENT ) ? 'TRANSPARENT' : 'OPAQUE' );

                $vevent->setProperty('dtstamp', array('timestamp' => ($v['dtstamp'] / 1000) ));

			    $timezone = new DateTimeZone('UTC');
			    $sTime = new DateTime('@' . (int) ($v['startTime'] / 1000), $timezone);
			    $eTime = new DateTime('@' . (int) ($v['endTime'] / 1000), $timezone);

			    if (( isset($v['repeat']) ) && ( isset($v['repeat']['frequency']) && $v['repeat']['frequency'] && $v['repeat']['frequency'] != 'none' )) 
				    $vevent->setProperty('rrule', $this->formatIcalRepeat($v['repeat']));

			    if (isset($v['allDay']) && $v['allDay'] == 1) {
				$vevent->setProperty('dtstart', $sTime->format(DATE_RFC822), array("VALUE" => "DATE"));
				$vevent->setProperty('dtend', $eTime->format(DATE_RFC822), array("VALUE" => "DATE"));
				$vevent->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'TRUE');
			    } else {
				$vevent->setProperty('dtstart', $sTime->format(DATE_RFC822));
				$vevent->setProperty('dtend', $eTime->format(DATE_RFC822));
				$vevent->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'FALSE');
			    }
			   		    
			    if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
			    	$participants = $v['participants'];
			    else
					$participants = Controller::find(array('concept' => 'participant'), false, array('filter' => array('=', 'schedulable', $v['id'])));
			    
				if (is_array($participants) && count($participants) > 0)
					foreach ($participants as $ii => $vv) {
					
						if(isset($participants[$ii]['user']) && !is_array($participants[$ii]['user']))
						{
							if ($vv['isExternal'] == 1)
								$participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user'], 'service' => 'PostgreSQL'));
							else
								$participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user']));
						}
					
						if ($participants[$ii]['user']['id'] == Config::me('uidNumber'))
						{
							$alarms = (isset($participants[$ii]['alarms'])) ? $participants[$ii]['alarms'] : Controller::find(array('concept' => 'alarm'), null, array('filter' => array('AND', array('=', 'participant', $vv['id']), array('=', 'schedulable', $v['id']))));
							if(is_array($alarms))
								self::createAlarms($alarms, $vevent);
						}
					
					}
			    

			    if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
					$this->createAttendee($v['participants'], $vevent);

			    if (isset($v['attachments']) && is_array($v['attachments']) && count($v['attachments']) > 0)
					$this->createAttachment($v['attachments'], $vevent);

			    $vevent->setProperty('uid', $v['uid']);  
			    
			    break;

			default:
			    break;
		    	
        case TODO_ID:

            $todo = $ical->newComponent('todo');

            $todo->setProperty('summary', $v['summary']);
            $todo->setProperty('description', isset($v['description']) ? $v['description'] : '');
            $todo->setProperty('priority', $v['priority']);
            $todo->setProperty('percent-complete', $v['percentage']);
            $todo->setProperty('status', $this->_getStatusTodo($v['status']));

            $todo->setProperty('dtstamp', array('timestamp' => ($v['dtstamp'] / 1000) ));

            $timezone = new DateTimeZone('UTC');
            $apTimezone = self::nomalizeTZID(( isset($v['timezone']) && $v['timezone'] != 'null' ) ? $v['timezone'] : $params['defaultTZI']);
            $apTimezoneOBJ = new DateTimeZone($apTimezone);

            $sTime = new DateTime('@' . (int) ($v['startTime'] / 1000), $timezone);
            $sTime->setTimezone($apTimezoneOBJ);

            if (isset($v['allDay']) && $v['allDay'] == 1) {
                $todo->setProperty('dtstart', $sTime->format(DATE_RFC822), array("VALUE" => "DATE"));
                //$todo->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'TRUE');
            } else {
                $todo->setProperty('dtstart', $sTime->format(DATE_RFC822), array('TZID' => $apTimezone));
                //$todo->setProperty('X-MICROSOFT-CDO-ALLDAYEVENT', 'FALSE');
            }

            if(isset($v['due']) && $v['due'] != '' && (int)$v['due'] > 0){
                $dueTime = new DateTime('@' . (int) ($v['due'] / 1000), $timezone);
                $dueTime->setTimezone($apTimezoneOBJ);

                $todo->setProperty('due', $dueTime->format(DATE_RFC822), array('TZID' => $apTimezone));
            }
                   
            if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
                $participants = $v['participants'];
            else
                $participants = Controller::find(array('concept' => 'participant'), false, array('filter' => array('=', 'schedulable', $v['id'])));
            
            if (is_array($participants) && count($participants) > 0)
                foreach ($participants as $ii => $vv) {
                
                    if(isset($participants[$ii]['user']) && !is_array($participants[$ii]['user']))
                    {
                        if ($vv['isExternal'] == 1)
                            $participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user'], 'service' => 'PostgreSQL'));
                        else
                            $participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user']));
                    }
                
                    if ($participants[$ii]['user']['id'] == Config::me('uidNumber'))
                    {
                        $alarms = (isset($participants[$ii]['alarms'])) ? $participants[$ii]['alarms'] : Controller::find(array('concept' => 'alarm'), null, array('filter' => array('AND', array('=', 'participant', $vv['id']), array('=', 'schedulable', $v['id']))));
                        if(is_array($alarms))
                            self::createAlarms($alarms, $todo);
                    }
                
                }

            if (isset($v['participants']) && is_array($v['participants']) && count($v['participants']) > 0)
                $this->createAttendee($v['participants'], $todo);

            if (isset($v['attachments']) && is_array($v['attachments']) && count($v['attachments']) > 0)
                $this->createAttachment($v['attachments'], $todo);

            $todo->setProperty('uid', $v['uid']);  
            
            break;

		default:
		    break;
	    }
	}

	
	return $ical->createCalendar();
    }

    protected function formatIcalRepeat($pRepeat)
    {
    	$repeat = array();

		foreach ($pRepeat as $ir => $rv) {
		    if ($rv) {
                if ($ir == 'frequency' && $rv !== 'none')
                    $repeat['FREQ'] = $rv;
                else if ($ir == 'endTime') {
                    $time = new DateTime('@' . (int) ($rv / 1000), new DateTimeZone('UTC'));
                    $time->setTimezone($apTimezoneOBJ);
                    $repeat['until'] = $time->format(DATE_RFC822);
                }else if ($ir == 'count')
                    $repeat[$ir] = $rv;
                else if ($ir == 'interval')
                    $repeat[$ir] = $rv;
                else if ($ir !== 'schedulable' && $ir !== 'id' && $ir !== 'startTime')
                    $repeat[$ir] = explode(',', $rv);
		    }
		}
		return $repeat;
    }

    
    public function createAlarms($alarms, &$vevent)
    {	
	    foreach ($alarms as $va)
	    {
	    	$valarm = new valarm();
	    	$valarm->setProperty('ACTION' , self::codeAlarmAction($va['type']));
	    	
	    	$duration = array();
	    	
	    	switch ($va['unit'])
	    	{
	    		case 'h':
	    			$duration['hour'] = $va['time'];
	    		break;
	    		case 'm':
	    			$duration['min'] = $va['time'];
	    		break;
	    		case 's':
	    			$duration['sec'] = $va['time'];
	    			break;
	    	}
	    	
	    	$valarm->setProperty('trigger' ,$duration);
	    	$vevent->setComponent($valarm);
	    }	
    	
    }

    //Trata a criacao de anexos do ics
    public function createAttachment($attachments, &$vevent) {
	foreach ($attachments as $key => $attachment) {
	    $pParams = array("ENCODING" => "BASE64", "VALUE" => "BINARY",
		"X-FILENAME" => $attachment['name']);

	    $vevent->setProperty("attach", $attachment['source'], $pParams);
	}
    }

    //Trata a criacao de attendees com tratamento de delegate
    public function createAttendee($attendees, &$vevent) {
	$delegate = array();
	foreach ($attendees as $di => $dv) {
	    if (isset($dv['delegatedFrom']) && $dv['delegatedFrom'] != 0) {
		$delegate[$dv['delegatedFrom']] = $dv;
	    }
	}

	foreach ($attendees as $pi => $pv) {
	    $isResponseDelegated = false;
	    if ((isset($pv['delegatedFrom']) && $pv['delegatedFrom'] == 0) || !isset($pv['delegatedFrom']))  {
		if ($pv['isOrganizer'] == 1){
			if($pv['user']['id'] == Config::me('uidNumber'))
				$pv['user']['mail'] = $pv['user']['mailSenderAddress'];
							
			$vevent->setProperty('organizer', $pv['user']['mail'], array('CN' => $pv['user']['name']));
		}else {
		    $pParams = array();
		    $pParams['CN'] = $pv['user']['name'];
		    $pParams['PARTSTAT'] = self::_getStatus($pv['status']);

		    if (isset($pv['id']) && isset($delegate[$pv['id']])) {
			$pParams['PARTSTAT'] = self::_getStatus($delegate[$pv['id']]['status']);
			$pParams['DELEGATED-TO'] = $delegate[$pv['id']]['user']['mail'];
			$pParams['CN'] = $pv['user']['name'];

			$vevent->setProperty('attendee', $pv['user']['mail'], $pParams);

			if ($delegate[$pv['id']]['status'] == STATUS_UNANSWERED) {
			    $pParams['RSVP'] = $pv['receiveNotification'] == 1 ? 'TRUE' : 'FALSE';
			    unset($pParams['PARTSTAT']);
			}else
			    $pParams['PARTSTAT'] = self::_getStatus($delegate[$pv['id']]['status']);

			unset($pParams['DELEGATED-TO']);
			$pParams['DELEGATED-FROM'] = $pv['user']['mail'];

			$vevent->setProperty('attendee', $delegate[$pv['id']]['user']['mail'], $pParams);
			continue;
		    }
		    $pParams['RSVP'] = 'TRUE';

		    $vevent->setProperty('attendee', $pv['user']['mail'], $pParams);

		}
	    }
	    
	    
	}
    }

    public function parse($data, $params = false) {
	Config::regSet('noAlarm', TRUE); //Evita o envio de notificação
	$vcalendar = new icalCreatorVcalendar( );

	$vcalendar->parse(trim($data));
	$vcalendar->sort();

	$return = array();
	$method = $vcalendar->getProperty('method', FALSE, FALSE);
	$params['prodid'] = $vcalendar->getProperty('prodid', false, false);
	$params['X-WR-TIMEZONE'] = ( $xrTimezone = $vcalendar->getProperty('X-WR-TIMEZONE', false, false)) ? self::nomalizeTZID($xrTimezone[1]) : false ;

	while ($component = $vcalendar->getComponent()) {
	    $interation = array();
	    $uid = $component->getProperty('uid', false, false); //Resgata o uid do componente

	    switch (strtoupper($component->objName)) {
		case 'VEVENT':


		    switch ($method) {
			case 'PUBLISH':
			    //Caso o evento não exista o mesmo cria um novo evento, se já existir o mesmo referencia o evento com agenda
                if (!$schedulable = self::_getSchedulable($uid))
				    $interation = self::_makeVEVENT($schedulable, $component, $params);
                else{
                    $links = Controller::read(array('concept' => 'calendarToSchedulable'), array('id'), array('filter' =>
        				array('AND',
        				    array('=', 'calendar', $params['calendar']),
        				    array('=', 'schedulable', $schedulable['id'])
        				)));

                    if(!$links &&  !isset($links[0]))
                        Controller::create(array('concept' => 'calendarToSchedulable'), array('calendar' => $params['calendar'], 'schedulable' => $schedulable['id']));

                    }
			    break;

			case 'REQUEST':
			    $schedulable = self::_getSchedulable($uid);
                $calendar = false;
                $accpeted = true;
			    if ($schedulable) {
                    ///Verifica se o usuario e um participante e se este aceitou o pedido.
                    foreach ($schedulable['participants'] as $value){
                        if ($value['user']['id'] == $params['owner'] &&  ($value['status'] == STATUS_UNANSWERED || $value['status'] == STATUS_DECLINED)){
                            $accpeted  = false;
                        }
                    }
			     //Caso o evento exista
                    if (!($calendar = self::_existInMyCalendars($schedulable['id'], $params['owner'])) || !$accpeted ) {
                        $calendarToSchedulable = array();
                        $calendarToSchedulable['calendar'] = $params['calendar'];
                        $calendarToSchedulable['schedulable'] = $schedulable['id'];
                        $interation['calendarToSchedulable://' . mt_rand() . '(Formatter)'] = $calendarToSchedulable;

                        if (isset($params['status']))
                        {
                            if($params['owner'] != Config::me("uidNumber"))
                            {
                                $user = Controller::Read(array('concept' => 'user'), false, array('filter' => array('=', 'id', $params['owner'])) );
                                $pID = self::_getParticipantByMail($user[0]['mail'], $schedulable['participants']);
                            }
                            else
                            {
                                $pID = self::_getParticipantByMail(Config::me('mail'), $schedulable['participants']);
                            }
                            //caso nõa seja participante adiciona a lista de participantes
                            if(!$pID){
                                $pID =  mt_rand() . '2(Formatter)';
                                $interation['participant://' . $pID]['status'] = $params['status'];
                                $interation['participant://' . $pID]['user'] = $params['owner'];
                                $interation['participant://' . $pID]['isOrganizer'] = '0';
                                $interation['participant://' . $pID]['schedulable'] = $schedulable['id'];
                            }
                            else
                            {
                                $interation['participant://' . $pID]['status'] = $params['status'];
                            }
                        }

                        Config::regSet('noAlarm', FALSE); //reativa notificação
                    } else {

                        if (self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence']){ //Organizador esta requisitando que você atualize o evento


                            $params['calendar'] = $params['calendar'] == 'true' ? $calendar : $params['calendar'];
                            $interation = self::_makeVEVENT($schedulable, $component, $params);
                        }else if ($component->getProperty('sequence', false, false) === $schedulable['sequence']) {
                            //Ler melhor rfc sobre isto 3.2.2.2
                            //Aparentemente é para retornar um ical com o evento atualizado para o attende
                        }

                        if (isset($params['status'])) {
                            if($params['owner'] != Config::me("uidNumber")){
                               $user = Controller::Read(array('concept' => 'user'), false, array('filter' => array('=', 'id', $params['owner'])) );
                                $pID = self::_getParticipantByMail($user[0]['mail'], $schedulable['participants']);
                            }else
                                $pID = self::_getParticipantByMail(Config::me('mail'), $schedulable['participants']);
                            //Verifica a importação de eventos em que não participo
                            if ($pID) {

                                $interation['participant://' . $pID]['status'] = $params['status'];

                            }
                        }
                    }
                } else { // Importar evento
                    $interation = self::_makeVEVENT(array(), $component, $params);

                       if (strpos($params['prodid'], 'kigkonsult.se') !== false) { //envia notificação para fora

                        /* Data de Inicio */
                        $startTime = $component->getProperty('dtstart', false, true);

                        $tzid = isset($startTime['params']['TZID']) ? $startTime['params']['TZID'] : $params['X-WR-TIMEZONE'];

                        /* Tiem zone do evento */
                        if ($tzid)
                        $sc['timezone'] = self::nomalizeTZID($tzid);
                        else
                        $sc['timezone'] = isset($params['calendar_timezone']) ? $params['calendar_timezone'] : 'America/Sao_Paulo';

                        $objTimezone = new DateTimeZone($sc['timezone']);

                        if (isset($startTime['params']['VALUE']) && $startTime['params']['VALUE'] === 'DATE' && isset($params['calendar_timezone'])) {
                        $sc['allDay'] = 1;
                        $sc['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $sc['timezone']) . '000';
                        } elseif (isset($startTime['params']['TZID']) && !isset($startTime['value']['tz']))/* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
                        $sc['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $startTime['params']['TZID']) . '000';
                        else {
                        $sc['startTime'] = self::date2timestamp($startTime['value']) . '000';
                        if (strpos($params['prodid'], 'Outlook') !== false) {
                            //Se o ics veio em utc não aplicar horario de verão
                            $sTime = new DateTime('@' . (int) ($sc['startTime'] / 1000), new DateTimeZone('UTC'));
                            $sTime->setTimezone($objTimezone);
                            if ($sTime->format('I')) //Se o ics veio em utc não aplicar horario de verão
                            $sc['startTime'] = $sc['startTime'] - 3600000;
                        }
                        }


                        /* Data de Termino */
                        $endTime = $component->getProperty('dtend', false, true);

                        if (isset($endTime['params']['VALUE']) && $endTime['params']['VALUE'] === 'DATE')
                        $sc['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $sc['timezone']) . '000';
                        else if (isset($endTime['params']['TZID']) && !isset($endTime['value']['tz'])) /* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
                        $sc['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $endTime['params']['TZID']) . '000';
                        else {
                        $sc['endTime'] = self::date2timestamp($endTime['value']) . '000';
                        if (strpos($params['prodid'], 'Outlook') !== false) {
                            //Se o ics veio em utc não aplicar horario de verão
                            $eTime = new DateTime('@' . (int) ($sc['endTime'] / 1000), new DateTimeZone('UTC'));
                            $eTime->setTimezone($objTimezone);
                            if ($eTime->format('I'))
                            $sc['endTime'] = $sc['endTime'] - 3600000;
                        }
                        }



                        if ($uid = $component->getProperty('uid', false, false))
                        ;
                        $sc['uid'] = $uid;


                        $sc['summary'] = mb_convert_encoding($component->getProperty('summary', false, false), 'UTF-8', 'UTF-8,ISO-8859-1');

                        /* Definindo Description */
                        if ($desc = $component->getProperty('description', false, false))
                        $sc['description'] = mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $desc), 'UTF-8', 'UTF-8,ISO-8859-1');

                        /* Definindo location */
                        if ($location = $component->getProperty('location', false, false))
                        $sc['location'] = mb_convert_encoding($location, 'UTF-8', 'UTF-8,ISO-8859-1');





                        if ($property = $component->getProperty('organizer', FALSE, TRUE)) {
                        $participant = array();
                        $mailUser = trim(str_replace('MAILTO:', '', $property['value']));

                        $participantID = mt_rand() . '2(Formatter)';

                        $participant['isOrganizer'] = '1';

                        $user = null;

                        $participant['isExternal'] = 1;
                        /* Gera um randon id para o contexto formater */
                        $userID = mt_rand() . '4(Formatter)';

                        $user['mail'] = $mailUser;
                        $organizerMail = $mailUser;

                        $user['name'] = ( isset($property['params']['CN']) ) ? $property['params']['CN'] : '';
                        $user['isExternal'] = '1';
                        $participant['user'] = $user;

                        $sc['participants'][] = $participant;
                        }



                        $participant['status'] = isset($params['status']) ? $params['status'] : STATUS_ACCEPTED;
                        $participant['isOrganizer'] = '0';
                        $participant['isExternal'] = 0;

                        $user = false;
                        if($params['owner'] != Config::me("uidNumber"))
                        {
                            $user = Controller::Read(array('concept' => 'user'), false, array('filter' => array('=', 'id', $params['owner'])) );
                        }

                        $participant['user'] = $user ?  array('mail' => $user['mail'], 'name' => $user['name']) : array('mail' => Config::me('mail'), 'name' => Config::me('cn'));

                        $sc['participants'][] = $participant;
                        $sc['type'] = EVENT_ID;



                        $ical['source'] = Controller::format(array('service' => 'iCal'), array($sc), array('method' => 'REPLY'));
                        $ical['type'] = 'application/ics';
                        $ical['name'] = 'outlook.ics';

                        $ical2['source'] = $ical['source'];
                        $ical2['type'] = 'text/calendar; method=REPLY';
                        $ical2['name'] = 'thunderbird.ics';

                        $timezone = new DateTimeZone('UTC');
                        $sTime = new DateTime('@' . (int) ($sc['startTime'] / 1000), $timezone);
                        $eTime = new DateTime('@' . (int) ($sc['endTime'] / 1000), $timezone);

                        if (isset($sc['timezone'])) {
                        $sTime->setTimezone(new DateTimeZone($sc['timezone']));
                        $eTime->setTimezone(new DateTimeZone($sc['timezone']));
                        }

                        $data = array('startDate' => date_format($sTime, 'd/m/Y'),
                        'startTime' => (isset($sc['allDay']) && $sc['allDay'] ) ? '' : date_format($sTime, 'H:i'),
                        'endDate' => date_format($eTime, 'd/m/Y'),
                        'endTime' => isset($sc['allDay']) ? '' : date_format($eTime, 'H:i'),
                        'eventTitle' => $sc['summary'],
                        'eventLocation' => isset($sc['location']) ? $sc['location'] : '',
                        'timezone' => ($sc['timezone']) ? $sc['timezone'] : 'UTC',
                        'participant' => (isset($part['user']['name']) ? $part['user']['name'] : $part['user']['mail']));

                        $subject['notificationType'] = 'Convite Aceito';
                        $subject['eventTitle'] = mb_convert_encoding($sc['summary'], 'ISO-8859-1', 'ISO-8859-1,UTF-8');
                        $subject['startDate'] = date_format($sTime, 'd/m/Y');
                        $subject['startTime'] = ($sc['allDay']) ? '' : date_format($sTime, 'H:i');
                        $subject['endDate'] = date_format($eTime, 'd/m/Y');
                        $subject['endTime'] = ($sc['allDay']) ? '' : date_format($eTime, 'H:i');
                        $subject['participant'] = Config::me('uid');

                        $params['status'] = isset($params['status']) ? $params['status'] : STATUS_ACCEPTED;

                        switch ($params['status']) {
                        case STATUS_ACCEPTED:
                            $tpl = 'notify_accept_body';
                            $subject['notificationType'] = 'Convite Aceito';
                            break;
                        case STATUS_TENTATIVE:
                            $tpl = 'notify_attempt_body';
                            $subject['notificationType'] = 'Convite  aceito provisoriamente';
                            break;
                        case STATUS_CANCELLED:
                            $tpl = 'notify_reject_body';
                            $subject['notificationType'] = 'Convite rejeitado';
                            break;
                        }

                        require_once ROOTPATH . '/api/parseTPL.php';

                        $mail = array();
                        $mail['attachments'][] = $ical;
                        $mail['attachments'][] = $ical2;

                        $mail['isHtml'] = true;
                        $mail['body'] = parseTPL::load_tpl($data, ROOTPATH . '/modules/calendar/templates/' . $tpl . '.tpl');
                        $mail['subject'] = parseTPL::load_tpl($subject, ROOTPATH . '/modules/calendar/templates/notify_subject.tpl');
                        ;
                        $mail['from'] = $user ? '"' . $user['name'] . '" <' . $user['mail'] . '>' : '"' . Config::me('cn') . '" <' . Config::me('mail') . '>';
                        $mail['to'] = $organizerMail;

                        Controller::create(array('service' => 'SMTP'), $mail);
                    }
			    }
			    break;

			case 'REFRESH':
			    break;

			case 'CANCEL':
			    if ($schedulable = self::_getSchedulable($uid))
				$interation['schedulable://' . $schedulable['id']] = false;
			    break;
				
			case 'ADD':
			    break;

			case 'REPLY':
			    if ($schedulable = self::_getSchedulable($uid)) {
				while ($property = $component->getProperty('attendee', FALSE, TRUE))
				    if ($pID = self::_getParticipantByMail(str_replace('MAILTO:', '', $property['value']), $schedulable['participants']))
					$interation['participant://' . $pID] = array('id' => $pID, 'status' => constant('STATUS_' . strtoupper($property['params']['PARTSTAT'])));

				$interation['schedulable://' . $schedulable['id']]['sequence'] = $schedulable['sequence'] + 1;
			    }
			    break;

			case 'COUNTER':
			    if ($params['acceptedSuggestion'] !== 'false') {

				$schedulable = self::_getSchedulable($uid);
				$params['calendar'] = self::_existInMyCalendars($schedulable['id'], $params['owner']);

				$interation = self::_makeCOUNTER($schedulable, $component, $params);
				Config::regSet('noAlarm', FALSE);
			    } else {
				$response = array();
				$response['from'] = $params['from'];
				$response['type'] = 'suggestionResponse';
				$response['status'] = 'DECLINECOUNTER';
				$response['schedulable'] = self::_getSchedulable($uid);

				Controller::create(array('concept' => 'notification'), $response);
			    }
			    break;

			case 'DECLINECOUNTER':
			    break;

			default:

			    $schedulable = self::_getSchedulable($uid);

			    if ($schedulable && ( self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence'])) { //Caso o evento exista
				$interation = self::_makeVEVENT($schedulable, $component, $params);

				if (!self::_existInMyCalendars($schedulable['id'], $params['owner'])) {
				    $calendarToSchedulable = array();
				    $calendarToSchedulable['calendar'] = $params['calendar'];
				    $calendarToSchedulable['schedulable'] = $schedulable['id'];
				    $interation['calendarToSchedulable://' . mt_rand() . '(Formatter)'] = $calendarToSchedulable;
				}
			    }
			    else // Importar evento
				$interation = self::_makeVEVENT(array(), $component, $params);
			    break;
		    }

		    $return[] = $interation;
		    break;
	/***********************************************************************TODO*******************************************************************************/
        case 'VTODO':
        switch ($method) {
            case 'PUBLISH':
                //Caso a tarefa não exista o mesmo cria um novo evento, se já existir o mesmo referencia o evento com agenda
                if (!$schedulable = self::_getSchedulable($uid))
                    $interation = self::_makeVTODO($schedulable, $component, $params);
                else{
                    $links = Controller::read(array('concept' => 'calendarToSchedulable'), array('id'), array('filter' =>
                    array('AND',
                        array('=', 'calendar', $params['calendar']),
                        array('=', 'schedulable', $schedulable['id'])
                    )));

                    if(!$links &&  !isset($links[0]))
                        Controller::create(array('concept' => 'calendarToSchedulable'), array('calendar' => $params['calendar'], 'schedulable' => $schedulable['id']));
                }
                break;

            case 'REQUEST':
                $schedulable = self::_getSchedulable($uid);

                if ($schedulable) { //Caso tarefa exista
                    if (!self::_existInMyCalendars($schedulable['id'], $params['owner'])) {
                        
                        $calendarToSchedulable = array();
                        $calendarToSchedulable['calendar'] = $params['calendar'];
                        $calendarToSchedulable['schedulable'] = $schedulable['id'];
                        $interation['calendarToSchedulable://' . mt_rand() . '(Formatter)'] = $calendarToSchedulable;
        
                        if (isset($params['status'])) {
                            if($params['owner'] != Config::me("uidNumber")){                        
                                $user = Controller::Read(array('concept' => 'user'), false, array('filter' => array('=', 'id', $params['owner'])) );                        
                                $pID = self::_getParticipantByMail($user[0]['mail'], $schedulable['participants']);
                            }else
                                $pID = self::_getParticipantByMail(Config::me('mail'), $schedulable['participants']);
                                $interation['participant://' . $pID]['status'] = $params['status'];
                        }
                        Config::regSet('noAlarm', FALSE); //reativa notificação
                    } else {

                        if (self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence']) //Organizador esta requisitando que você atualize o evento
                            $interation = self::_makeVEVENT($schedulable, $component, $params);
                        else if ($component->getProperty('sequence', false, false) === $schedulable['sequence']) {
                        //Ler melhor rfc sobre isto 3.2.2.2
                        //Aparentemente é para retornar um ical com o evento atualizado para o attende
                        }

                        if (isset($params['status'])) {
                            if($params['owner'] != Config::me("uidNumber")){                        
                               $user = Controller::Read(array('concept' => 'user'), false, array('filter' => array('=', 'id', $params['owner'])) );                     
                                $pID = self::_getParticipantByMail($user[0]['mail'], $schedulable['participants']);
                            }else{
                                $pID = self::_getParticipantByMail(Config::me('mail'), $schedulable['participants']);
                                //Verifica a importação de tarefas em que não participo
                                if ($pID) {
                                    $pID =  mt_rand() . '2(Formatter)';
                                    $interation['participant://' . $pID]['status'] = $params['status'];
                                    $interation['participant://' . $pID]['user'] = $params['owner'];
                                    $interation['participant://' . $pID]['isOrganizer'] = '0';
                                    $interation['participant://' . $pID]['schedulable'] = $schedulable['id'];
                                }else
                                    $interation['participant://' . $pID]['status'] = $params['status'];
                            }
                        }
                    }
		} else { // Importar tarefa
		    $interation = self::_makeVTODO(array(), $component, $params);

		    if (strpos($params['prodid'], 'kigkonsult.se') !== false) { //envia notificação para fora

			/* Data de Inicio */
			$startTime = $component->getProperty('dtstart', false, true);
			$tzid = isset($startTime['params']['TZID']) ? $startTime['params']['TZID'] : $params['X-WR-TIMEZONE'];

			/* Tiem zone do evento */   
			if ($tzid)
			    $sc['timezone'] = self::nomalizeTZID($tzid);
			else
			    $sc['timezone'] = isset($params['calendar_timezone']) ? $params['calendar_timezone'] : 'America/Sao_Paulo';

			$objTimezone = new DateTimeZone($sc['timezone']);

			if (isset($startTime['params']['VALUE']) && $startTime['params']['VALUE'] === 'DATE' && isset($params['calendar_timezone'])) {
			    $sc['allDay'] = 1;
			    $sc['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $sc['timezone']) . '000';
			} elseif (isset($startTime['params']['TZID']) && !isset($startTime['value']['tz']))/* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
			    $sc['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $startTime['params']['TZID']) . '000';
			else {
			    $sc['startTime'] = self::date2timestamp($startTime['value']) . '000';
			    if (strpos($params['prodid'], 'Outlook') !== false) {
				//Se o ics veio em utc não aplicar horario de verão
				$sTime = new DateTime('@' . (int) ($sc['startTime'] / 1000), new DateTimeZone('UTC'));
				$sTime->setTimezone($objTimezone);
				if ($sTime->format('I')) //Se o ics veio em utc não aplicar horario de verão
				$sc['startTime'] = $sc['startTime'] - 3600000;
			    }
			}

			/* Data de Termino */
			$endTime = $component->getProperty('dtend', false, true);

			if (isset($endTime['params']['VALUE']) && $endTime['params']['VALUE'] === 'DATE')
			    $sc['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $sc['timezone']) . '000';
			else if (isset($endTime['params']['TZID']) && !isset($endTime['value']['tz'])) /* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
			    $sc['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $endTime['params']['TZID']) . '000';
			else {
			    $sc['endTime'] = self::date2timestamp($endTime['value']) . '000';
			    if (strpos($params['prodid'], 'Outlook') !== false) {
				//Se o ics veio em utc não aplicar horario de verão
				$eTime = new DateTime('@' . (int) ($sc['endTime'] / 1000), new DateTimeZone('UTC'));
				$eTime->setTimezone($objTimezone);
				if ($eTime->format('I'))
				    $sc['endTime'] = $sc['endTime'] - 3600000;
			    }
			}


			if ($uid = $component->getProperty('uid', false, false))                    
			    $sc['uid'] = $uid;

			$sc['summary'] = mb_convert_encoding($component->getProperty('summary', false, false), 'UTF-8', 'UTF-8,ISO-8859-1');

			/* Definindo Description */
			if ($desc = $component->getProperty('description', false, false))
			    $sc['description'] = mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $desc), 'UTF-8', 'UTF-8,ISO-8859-1');

            if ($priority = $component->getProperty('priority', false, false))
                $sc['priority'] = mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $priority), 'UTF-8', 'UTF-8,ISO-8859-1');

            if ($status = $component->getProperty('status', false, false))
                $sc['status'] = $this->decodeStatusTodo(mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $status), 'UTF-8', 'UTF-8,ISO-8859-1'));

            if ($percentage = $component->getProperty('percent-complete', false, false))
                $sc['percentage'] = mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $percentage), 'UTF-8', 'UTF-8,ISO-8859-1');

			/* Definindo location */
			if ($location = $component->getProperty('location', false, false))
			    $sc['location'] = mb_convert_encoding($location, 'UTF-8', 'UTF-8,ISO-8859-1');



			if ($property = $component->getProperty('organizer', FALSE, TRUE)) {
			    $participant = array();
			    $mailUser = trim(str_replace('MAILTO:', '', $property['value']));

			    $participantID = mt_rand() . '2(Formatter)';

			    $participant['isOrganizer'] = '1';

			    $user = null;

			    $participant['isExternal'] = 1;
			    /* Gera um randon id para o contexto formater */
			    $userID = mt_rand() . '4(Formatter)';

			    $user['mail'] = $mailUser;
			    $organizerMail = $mailUser;

			    $user['name'] = ( isset($property['params']['CN']) ) ? $property['params']['CN'] : '';
			    $user['isExternal'] = '1';
			    $participant['user'] = $user;

			    $sc['participants'][] = $participant;
			}


			$participant['status'] = isset($params['status']) ? $params['status'] : STATUS_ACCEPTED;
			$participant['isOrganizer'] = '0';
			$participant['isExternal'] = 0;
			$participant['user'] = array('mail' => Config::me('mail'), 'name' => Config::me('cn'));
			$sc['participants'][] = $participant;
			$sc['type'] = TODO_ID;


			$ical['source'] = Controller::format(array('service' => 'iCal'), array($sc), array('method' => 'REPLY'));
			$ical['type'] = 'application/ics';
			$ical['name'] = 'outlook.ics';

			$ical2['source'] = $ical['source'];
			$ical2['type'] = 'text/calendar; method=REPLY';
			$ical2['name'] = 'thunderbird.ics';

			$timezone = new DateTimeZone('UTC');
			$sTime = new DateTime('@' . (int) ($sc['startTime'] / 1000), $timezone);
			$eTime = new DateTime('@' . (int) ($sc['endTime'] / 1000), $timezone);

			if (isset($sc['timezone'])) {
			    $sTime->setTimezone(new DateTimeZone($sc['timezone']));
			    $eTime->setTimezone(new DateTimeZone($sc['timezone']));
			}

			$data = array('startDate' => date_format($sTime, 'd/m/Y'),
			'startTime' => (isset($sc['allDay']) && $sc['allDay'] ) ? '' : date_format($sTime, 'H:i'),
			'endDate' => date_format($eTime, 'd/m/Y'),
			'endTime' => isset($sc['allDay']) ? '' : date_format($eTime, 'H:i'),
			'eventTitle' => $sc['summary'],
			'eventLocation' => isset($sc['location']) ? $sc['location'] : '',
			'timezone' => ($sc['timezone']) ? $sc['timezone'] : 'UTC',
			'participant' => (isset($part['user']['name']) ? $part['user']['name'] : $part['user']['mail']));

			$subject['notificationType'] = 'Convite Aceito';
			$subject['eventTitle'] = mb_convert_encoding($sc['summary'], 'ISO-8859-1', 'ISO-8859-1,UTF-8');
			$subject['startDate'] = date_format($sTime, 'd/m/Y');
			$subject['startTime'] = ($sc['allDay']) ? '' : date_format($sTime, 'H:i');
			$subject['endDate'] = date_format($eTime, 'd/m/Y');
			$subject['endTime'] = ($sc['allDay']) ? '' : date_format($eTime, 'H:i');
			$subject['participant'] = Config::me('uid');

			$params['status'] = isset($params['status']) ? $params['status'] : STATUS_ACCEPTED;

			switch ($params['status']) {
			    case STATUS_ACCEPTED:
				$tpl = 'notify_accept_body';
				$subject['notificationType'] = 'Convite Aceito';
				break;
			    case STATUS_TENTATIVE:
				$tpl = 'notify_attempt_body';
				$subject['notificationType'] = 'Convite  aceito provisoriamente';
				break;
			    case STATUS_CANCELLED:
				$tpl = 'notify_reject_body';
				$subject['notificationType'] = 'Convite rejeitado';
				break;
			}

			require_once ROOTPATH . '/api/parseTPL.php';

			$mail = array();
			$mail['attachments'][] = $ical;
			$mail['attachments'][] = $ical2;

			$mail['isHtml'] = true;
			$mail['body'] = parseTPL::load_tpl($data, ROOTPATH . '/modules/calendar/templates/' . $tpl . '.tpl');
			$mail['subject'] = parseTPL::load_tpl($subject, ROOTPATH . '/modules/calendar/templates/notify_subject.tpl');

			$mail['from'] = '"' . Config::me('cn') . '" <' . Config::me('mail') . '>';
			$mail['to'] = $organizerMail;


			Controller::create(array('service' => 'SMTP'), $mail);
		    }
                }
                break;

            case 'REFRESH':
                break;

            case 'CANCEL':
                if ($schedulable = self::_getSchedulable($uid))
		    $interation['schedulable://' . $schedulable['id']] = false;
                break;

            case 'ADD':
                break;

            case 'REPLY':
                if ($schedulable = self::_getSchedulable($uid)) {
		    while ($property = $component->getProperty('attendee', FALSE, TRUE))
			if ($pID = self::_getParticipantByMail(str_replace('MAILTO:', '', $property['value']), $schedulable['participants']))
			    $interation['participant://' . $pID] = array('id' => $pID, 'status' => constant('STATUS_' . strtoupper($property['params']['PARTSTAT'])));

		    $interation['schedulable://' . $schedulable['id']]['sequence'] = $schedulable['sequence'] + 1;
                }
                break;

            case 'COUNTER':
                if ($params['acceptedSuggestion'] !== 'false') {

		    $schedulable = self::_getSchedulable($uid);
		    $params['calendar'] = self::_existInMyCalendars($schedulable['id'], $params['owner']);

		    $interation = self::_makeCOUNTER($schedulable, $component, $params);
		    Config::regSet('noAlarm', FALSE);
                } else {
		    $response = array();
		    $response['from'] = $params['from'];
		    $response['type'] = 'suggestionResponse';
		    $response['status'] = 'DECLINECOUNTER';
		    $response['schedulable'] = self::_getSchedulable($uid);

		    Controller::create(array('concept' => 'notification'), $response);
                }
                break;

            case 'DECLINECOUNTER':
                break;

            default:

                $schedulable = self::_getSchedulable($uid);

                if ($schedulable && ( self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence'])) { //Caso o evento exista
		    $interation = self::_makeVEVENT($schedulable, $component, $params);

		    if (!self::_existInMyCalendars($schedulable['id'], $params['owner'])) {
			$calendarToSchedulable = array();
			$calendarToSchedulable['calendar'] = $params['calendar'];
			$calendarToSchedulable['schedulable'] = $schedulable['id'];
			$interation['calendarToSchedulable://' . mt_rand() . '(Formatter)'] = $calendarToSchedulable;
		    }
                }
                else // Importar evento
                $interation = self::_makeVEVENT(array(), $component, $params);

                break;
            }
    
            $return[] = $interation;
        break;
        /***********************************************************************TODO*******************************************************************************/
        case 'VTIMEZONE':
		break;
	    }
	}
	return $return;
    }

    public function analize($data, $params = false) {
	$vcalendar = new icalCreatorVcalendar( );
	$vcalendar->parse(trim($data));
	$vcalendar->sort();

	$return = array();
	$method = $vcalendar->getProperty('method', FALSE, FALSE);

	while ($component = $vcalendar->getComponent()) {
	    $interation = array();
	    $uid = $component->getProperty('uid', false, false); //Resgata o uid do componente
	    switch (strtoupper($component->objName)) {
		case 'VEVENT':

		    switch ($method) {
			case 'PUBLISH':
			    $interation = array('action' => ICAL_ACTION_IMPORT, 'type' => 'calendarIds');
			    break;

			case 'REQUEST':
			    $schedulable = self::_getSchedulable($uid);
			    if ($schedulable) { //Caso o evento exista
                    $isOrganizer = false;
                    $isParticipant = false;

                    foreach ($schedulable['participants'] as $value){


                        if ($value['user']['id'] == $params['owner']) {
                            $isParticipant = true;
                            if ($value['isOrganizer'])
                                $isOrganizer = true;

                            if (!self::_existInMyCalendars($schedulable['id'], $params['owner']) || $value['status'] == STATUS_UNANSWERED || $value['status'] == STATUS_DECLINED) {
                                $interation = ICAL_ACTION_UPDATE;
                                $interation = ( strrpos($value['acl'], ATTENDEE_ACL_PARTICIPATION_REQUIRED) ) ? ICAL_ACTION_IMPORT_REQUIRED : array('action' => ICAL_ACTION_IMPORT, 'type' => 'calendarIds');
                                break;
                            }
                            else
                            {
                                if (self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence']) //Organizador esta requisitando que você atualize o evento
                                    $interation = ($isOrganizer) ? ICAL_ACTION_ORGANIZER_UPDATE : ICAL_ACTION_UPDATE;
                                else
                                    $interation = ($isOrganizer) ? ICAL_ACTION_ORGANIZER_NONE : ICAL_ACTION_NONE;

                            }
                        }
                      }
                    if (!$isParticipant){
                        if( self::_existInMyCalendars($schedulable['id'], $params['owner']) ){
                            $interation = (self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] ? ICAL_ACTION_UPDATE  :  ICAL_ACTION_NONE);

                        }else{

                            $interation =  self::_checkParticipantByPermissions($schedulable);

                        }

                    }
                }else
                    $interation = array('action' => ICAL_ACTION_IMPORT_REQUIRED, 'type' => 'calendarIds');

                    if(($interation != ICAL_ACTION_NONE) && ($interation != ICAL_ACTION_ORGANIZER_NONE) && ($interation != ICAL_ACTION_ORGANIZER_UPDATE) && ($interation != ICAL_ACTION_NONE) && ($interation != ICAL_ACTION_UPDATE) && (!is_array($interation ) )) {
                        if($params['owner'] != Config::me("uidNumber")){
                            $sig = Controller::find(array('concept' => 'calendarSignature'), array('calendar'), array('filter' => array('AND', array('=', 'user', $params['owner']), array('=', 'isOwner', '1'))));
                            $calendars = array();
                            foreach ($sig as $val)
                            $calendars[] = $val['calendar'];

                            $calendarsPermission = Controller::find(array('concept' => 'calendarToPermission'), array('calendar'), array('filter' => array('AND', array('=', 'user', Config::me("uidNumber")), array('IN', 'calendar', $calendars))));

                            foreach ($calendarsPermission as $val)
                            $ids[] = $val['calendar'];

                            $interation = array('action' => ICAL_ACTION_IMPORT_FROM_PERMISSION ,'calendar' => $ids);
                        }
                    }

			    break;

			case 'REFRESH':
			    break;

			case 'CANCEL':
			    $interation = ICAL_ACTION_DELETE;
			    break;

			case 'ADD':
			    break;

			case 'REPLY':
				if ($schedulable = self::_getSchedulable($uid)) {
					while ($property = $component->getProperty('attendee', FALSE, TRUE))
					    if ($attendee = self::_getParticipantByMail(str_replace('MAILTO:', '', $property['value']), $schedulable['participants'], true))
							$interation = (constant('STATUS_' . strtoupper($property['params']['PARTSTAT'])) == $attendee['status']) ?  ICAL_ACTION_NONE : ICAL_ACTION_REPLY;
			    }else
			    	$interation = ICAL_NOT_FOUND;
			    break;

			case 'COUNTER':
			    $interation = ICAL_ACTION_SUGGESTION;
			    break;

			case 'DECLINECOUNTER':
			    $interation = ICAL_ACTION_NONE;
			    break;

			default:
			    $schedulable = self::_getSchedulable($uid);

			    if ($schedulable && ( self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence'])) //Caso o evento exista
				$interation = ICAL_ACTION_UPDATE;
			    else if ($schedulable)
				$interation = ICAL_ACTION_NONE;
			    else // Importar evento
				$interation = array('action' => ICAL_ACTION_IMPORT, 'type' => 'calendarIds');

			    break;
		    }

		    $return[$uid] = $interation;
		    break;
		case 'VTODO':
		    switch ($method) {
			case 'PUBLISH':
			    $interation = array('action' => ICAL_ACTION_IMPORT, 'type' => 'groupIds');
			    break;

			case 'REQUEST':
			    $schedulable = self::_getSchedulable($uid);
			   
			    if ($schedulable) { //Caso o evento exista
				$isOrganizer = false;
				$isParticipant = false;

				foreach ($schedulable['participants'] as $value)
				    if ($value['user']['id'] == $params['owner']) {
					$isParticipant = true;
					if ($value['isOrganizer'])
					    $isOrganizer = true;

					if (!self::_existInMyCalendars($schedulable['id'], $params['owner'])) {   
					    $interation = array('action' => ICAL_ACTION_IMPORT, 'type' => 'groupIds');
					    break;
					}
				    } else {

                        ///Atualiza o Caldadav mesmo que o expresso não prescisse de atualização, pois os calendarios do caldav são independentes um de cada usuario diferente do expresso que so tem 1 evento e é compartilhado entre os usuarios
                        if (Config::module('useCaldav', 'expressoCalendar')) { //Ignorar Put dos eventos ja vindos do caldav
                            require_once ROOTPATH . '/modules/calendar/interceptors/DAViCalAdapter.php';
                            $calendars = self::schedulable2calendarToObject($schedulable['id'] , isset($params['owner']) ? $params['owner']: false ); //Busca os calendarios do usuario logado que contenham o evento
                            if (is_array($calendars))
                                foreach ($calendars as $calendar)
                                    DAViCalAdapter::putIcal($data, array('uid' => $schedulable['uid'], 'location' => $calendar['calendar_location']));
                        }

					if (self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence']) //Organizador esta requisitando que você atualize o evento
					    $interation = ($isOrganizer) ? ICAL_ACTION_ORGANIZER_UPDATE : ICAL_ACTION_UPDATE;
					else
					    $interation = ($isOrganizer) ? ICAL_ACTION_ORGANIZER_NONE : ICAL_ACTION_NONE;
				    }
				if (!$isParticipant){
				      $interation = self::_checkParticipantByPermissions($schedulable);
				    }
			    }else
				$interation = array('action' => ICAL_ACTION_IMPORT, 'type' => 'groupIds');
			    break;

			case 'REFRESH':
			    break;

			case 'CANCEL':
			    $interation = ICAL_ACTION_DELETE;
			    break;

			case 'ADD':
			    break;

			case 'REPLY':
			    $interation = ICAL_ACTION_REPLY;
			    break;

			case 'COUNTER':
			    $interation = ICAL_ACTION_SUGGESTION;
			    break;

			case 'DECLINECOUNTER':
			    $interation = ICAL_ACTION_NONE;
			    break;

			default:
			    $schedulable = self::_getSchedulable($uid);

			    if ($schedulable && ( self::_getTime($component, 'dtstamp') > $schedulable['dtstamp'] || $component->getProperty('sequence', false, false) > $schedulable['sequence'])) //Caso o evento exista
				$interation = ICAL_ACTION_UPDATE;
			    else if ($schedulable)
				$interation = ICAL_ACTION_NONE;
			    else // Importar evento
				$interation = array('action' => ICAL_ACTION_IMPORT, 'type' => 'groupIds');

			    break;
		    }

		    $return[$uid] = $interation;
		    break;
		case 'VTIMEZONE':
		break;
	    }
	}

	return $return;
    }

    /* Helpers */

    private static function _getTzOffset($rTz, $oTz = null, $time = 'now') {
	if ($oTz === null) {
	    if (!is_string($oTz = date_default_timezone_get())) {
		return false; // A UTC timestamp was returned -- bail out!
	    }
	}
	$origin_dtz = new DateTimeZone(self::nomalizeTZID($oTz));
	$remote_dtz = new DateTimeZone(self::nomalizeTZID($rTz));
	$origin_dt = new DateTime($time, $origin_dtz);
	$remote_dt = new DateTime("now", $remote_dtz);

	$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);



	return $offset;
    }

    private function _getStatus($id) {
	$a = array(
	    STATUS_CONFIRMED => 'ACCEPTED',
	    STATUS_CANCELLED => 'CANCELLED',
	    STATUS_TENTATIVE => 'TENTATIVE',
	    STATUS_UNANSWERED => 'NEEDS-ACTION',
	    STATUS_DELEGATED => 'DELEGATED'
	);

	return isset($a[$id]) ? $a[$id] : 'NEEDS-ACTION';
    }

    public function decodeStatusTodo( $action )
    {
     $a = array(
        'NEED_ACTION' => STATUS_TODO_NEED_ACTION,
        'IN_PROGRESS' => STATUS_TODO_IN_PROGRESS  ,
        'COMPLETED' =>  STATUS_TODO_COMPLETED ,
        'CANCELLED'  => STATUS_TODO_CANCELLED
    );

    return isset($a[$action]) ? $a[$action] : 'STATUS_TODO_NEED_ACTION';
    
    }


    private function _getStatusTodo($id) {
        $a = array(
            STATUS_TODO_NEED_ACTION => 'NEED_ACTION',
            STATUS_TODO_IN_PROGRESS => 'IN_PROGRESS',
            STATUS_TODO_COMPLETED => 'COMPLETED',
            STATUS_TODO_CANCELLED => 'CANCELLED'
        );

    return isset($a[$id]) ? $a[$id] : 'NEED_ACTION';
    }


    private static function _checkParticipantByPermissions($schedulable) {

    	$calendarIds = Controller::find(array('concept' => 'calendarSignature'), array('calendar'), array('filter' => array('AND', array('=','isOwner','0'), array('=', 'user', Config::me("uidNumber")))));

    	if($calendarIds && isset($calendarIds[0])){
    	    $ids = array();
    	    foreach($calendarIds as $value)
    		    array_push($ids, $value['calendar']);

    	    $signaturesOfOwners = Controller::find(array('concept' => 'calendarSignature'), false, array('filter' => array('AND', array('IN', 'calendar', $ids) , array('=','isOwner','1')), 'deepness' => 2 ));
    	    
    	    foreach($signaturesOfOwners as $value){
        		if(self::_getParticipantByMail($value['user']['mail'], $schedulable['participants'])){
        		    $eventoFromCalendar = Controller::read( array( 'concept' => 'calendarToSchedulable') , false, array('filter' => array('AND', array('=','schedulable',$schedulable['id']), array('=','calendar', $value['calendar']['id']))));

        		    return  ($eventoFromCalendar && isset($eventoFromCalendar[0])) ? ICAL_ACTION_NONE_FROM_PERMISSION : array('action' => ICAL_ACTION_IMPORT_FROM_PERMISSION, 'calendar' => array($value['calendar']['id']) );
        		}
    	    }    
    	}
    	return array('action' => ICAL_ACTION_IMPORT, 'type' => 'calendarIds');
    }

    private static function _getParticipantByMail($mail, &$participants, $isFull = false) {
	if ($participants && $participants != '')
	    foreach ($participants as $i => $v)
		if ((is_array($v) && isset($v['user'])) && ($v['user']['mail'] == $mail || (isset($v['user']['mailAlternateAddress']) && in_array($mail, $v['user']['mailAlternateAddress']))))
		      return !!$isFull ? $v : $v['id'];
	return false;
    }

    static private function nomalizeTZID($TZID) {
	if (isset(self::$timezonesMap[$TZID]))
	    return self::$timezonesMap[$TZID];
	else if (in_array($TZID, self::$suportedTimzones))
	    return $TZID;
	else
	    return date_default_timezone_get();
    }

    static private function date2timestamp($datetime, $tz = null) {
	if (!isset($datetime['hour']))
	    $datetime['hour'] = '0';
	if (!isset($datetime['min']))
	    $datetime['min'] = '0';
	if (!isset($datetime['sec']))
	    $datetime['sec'] = '0';

	foreach ($datetime as $dkey => $dvalue)
	    if ('tz' != $dkey)
		$datetime[$dkey] = (integer) $dvalue;

	if ($tz)
	    $datetime['tz'] = $tz;

	$offset = ( isset($datetime['tz']) && ( '' < trim($datetime['tz']))) ? iCalUtilityFunctions::_tz2offset($datetime['tz']) : 0;

	return gmmktime($datetime['hour'], $datetime['min'], ($datetime['sec'] + $offset), $datetime['month'], $datetime['day'], $datetime['year']);
    }

    static private function _makeCOUNTER($schedulable, $component, $params) {
	$interation = array();
	$eventID = isset($schedulable['id']) ? $schedulable['id'] : mt_rand() . '(Formatter)';

	/* Data de Inicio */
	$startTime = $component->getProperty('dtstart', false, true);

	/* Tiem zone do evento */
	if (isset($startTime['params']['TZID']))
	    $schedulable['timezone'] = self::nomalizeTZID($startTime['params']['TZID']);
	else
	    $schedulable['timezone'] = isset($params['calendar_timezone']) ? $params['calendar_timezone'] : 'America/Sao_Paulo';

	$objTimezone = new DateTimeZone($schedulable['timezone']);

	if ($startTime['params']['VALUE'] === 'DATE' && isset($params['calendar_timezone'])) {
	    $schedulable['allDay'] = 1;
	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($startTime['value'])) . '000';
	} elseif (isset($startTime['params']['TZID']) && !isset($startTime['value']['tz'])) {/* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $startTime['params']['TZID'], '@' . self::date2timestamp($startTime['value'])) . '000';
	    $schedulable['allDay'] = 0;
	} else {
	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) . '000';
	    if (strpos($params['prodid'], 'Outlook') !== false) {
		//Se o ics veio em utc não aplicar horario de verão
		$sTime = new DateTime('@' . (int) ($schedulable['startTime'] / 1000), new DateTimeZone('UTC'));
		$sTime->setTimezone($objTimezone);
		if ($sTime->format('I')) //Se o ics veio em utc não aplicar horario de verão
		    $schedulable['startTime'] = $schedulable['startTime'] - 3600000;
	    }
	}

	/* Data de Termino */
	$endTime = $component->getProperty('dtend', false, true);

	if ($endTime['params']['VALUE'] === 'DATE')
	    $schedulable['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($endTime['value'])) . '000';
	else if (isset($endTime['params']['TZID']) && !isset($endTime['value']['tz'])) /* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
	    $schedulable['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $endTime['params']['TZID'], '@' . self::date2timestamp($endTime['value'])) . '000';
	else {
	    $schedulable['endTime'] = self::date2timestamp($endTime['value']) . '000';
	    if (strpos($params['prodid'], 'Outlook') !== false) {
		//Se o ics veio em utc não aplicar horario de verão
		$eTime = new DateTime('@' . (int) ($schedulable['endTime'] / 1000), new DateTimeZone('UTC'));
		$eTime->setTimezone($objTimezone);
		if ($eTime->format('I'))
		    $schedulable['endTime'] = $schedulable['endTime'] - 3600000;
	    }
	}
	unset($schedulable['participants']);
	$interation['schedulable://' . $eventID] = $schedulable;

	return $interation;
    }

    static private function _makeVEVENT($schedulable, $component, $params) {


    	$interation = array();
    	$eventID = isset($schedulable['id']) ? $schedulable['id'] : mt_rand() . '(Formatter)';

    	/* Data de Inicio */
    	$startTime = $component->getProperty('dtstart', false, true);

    	$tzid = (isset($startTime['params']['TZID']) ? $startTime['params']['TZID'] : $params['X-WR-TIMEZONE']);

    	/* Tiem zone do evento */
    	if ($tzid){
    	    $tzid = self::nomalizeTZID($tzid);
    	    $schedulable['timezone'] = $tzid;
    	}else
    	    $schedulable['timezone'] = isset($params['calendar_timezone']) ? $params['calendar_timezone'] : 'America/Sao_Paulo';

    	$objTimezone = new DateTimeZone($schedulable['timezone']);

        if (isset($startTime['params']['VALUE']) && $startTime['params']['VALUE'] === 'DATE' ) {
    	    $schedulable['allDay'] = 1;
    	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($startTime['value'])) . '000';
    	} elseif ($tzid && !isset($startTime['value']['tz'])) {/* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
    	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $tzid, '@' . self::date2timestamp($startTime['value'])) . '000';
    	    $schedulable['allDay'] = 0;
    	} else {
            $schedulable['allDay'] = 0;
            //Sem informação de timezone 
             $schedulable['startTime'] = self::date2timestamp($startTime['value'])  . '000';


            //Tratamento thunderbird
            if ( $component->getProperty('X-MOZ-GENERATION') !== false) 
                 $schedulable['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($startTime['value'])) . '000';
    	    if (strpos($params['prodid'], 'Outlook') !== false) {
    		//Se o ics veio em utc não aplicar horario de verão
    		$sTime = new DateTime('@' . (int) ($schedulable['startTime'] / 1000), new DateTimeZone('UTC'));
    		$sTime->setTimezone($objTimezone);
    		if ($sTime->format('I')) //Se o ics veio em utc não aplicar horario de verão
    		    $schedulable['startTime'] = $schedulable['startTime'] - 3600000;
    	    }
    	}

    	/* Data de Termino */
    	$endTime = $component->getProperty('dtend', false, true);

    	$tzid = isset($endTime['params']['TZID']) ? $endTime['params']['TZID'] : $params['X-WR-TIMEZONE'];
    	
    	if($tzid)
    	    $tzid = self::nomalizeTZID($tzid);

    	if (isset($endTime['params']['VALUE']) && $endTime['params']['VALUE'] === 'DATE')
    	    $schedulable['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($endTime['value'])) . '000';
    	else if ($tzid && !isset($endTime['value']['tz'])) /* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
    	    $schedulable['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $tzid, '@' . self::date2timestamp($endTime['value'])) . '000';
    	else {
            //Não tem informação de timezone 
             $schedulable['endTime'] = self::date2timestamp($endTime['value']) . '000';

            //Tratamento thunderbid 
            if ( $component->getProperty('X-MOZ-GENERATION') !== false) {
                $schedulable['endTime'] = self::date2timestamp($endTime['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($endTime['value'])) . '000';
            }

            //Tratamento par aoutlook
    	    if (strpos($params['prodid'], 'Outlook') !== false) {
    		//Se o ics veio em utc não aplicar horario de verão
    		$eTime = new DateTime('@' . (int) ($schedulable['endTime'] / 1000), new DateTimeZone('UTC'));
    		$eTime->setTimezone($objTimezone);
    		if ($eTime->format('I'))
    		    $schedulable['endTime'] = $schedulable['endTime'] - 3600000;
    	    }
    	}



    	$schedulable['summary'] = mb_convert_encoding($component->getProperty('summary', false, false), 'ISO-8859-1', 'UTF-8,ISO-8859-1');

    	/* Definindo Description */
    	if ($desc = $component->getProperty('description', false, false))
    	    $schedulable['description'] = mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $desc), 'ISO-8859-1', 'UTF-8,ISO-8859-1');

    	/* Definindo location */
    	if ($location = $component->getProperty('location', false, false))
    	    $schedulable['location'] = mb_convert_encoding($location, 'ISO-8859-1', 'UTF-8,ISO-8859-1');



    	/* Definindo Class */
    	$class = $component->getProperty('class', false, false);
    	if ($class && defined(constant(strtoupper('CLASS_' . $class))))
    	    $schedulable['class'] = constant(strtoupper('CLASS_' . $class));
    	else if (!isset($schedulable['class']))
    	    $schedulable['class'] = CLASS_PRIVATE; // padrão classe private

    	/* Definindo RRULE */
    	if ($rrule = $component->getProperty('rrule', false, false)) {
    	    /* Gera um randon id para o contexto formater */
    	    $repeatID = mt_rand() . '3(Formatter)';

    	    $repeat = array();
    	    $repeat['schedulable'] = $eventID;
            $repeat['startTime'] = $schedulable['startTime'];
            foreach ($rrule as $i => $v) {
    		if (strtolower($i) == 'freq')
    		    $repeat['frequency'] = $v;
    		else if (strtolower($i) == 'until')
            {
                $repeat['endTime'] = strtotime($v['year'].'-'.$v['month'].'-'.$v['day'].' '.$v['hour'].':'.$v['min'].':'.$v['sec'].' '.$v['tz']) .'000' ;
            }
    		else
    		    $repeat[strtolower($i)] = $v;
    	    }

    	    $interation['repeat://' . $repeatID] = $repeat;
    	}

    	$schedulable['calendar'] = $params['calendar'];

    	$participantsInEvent = array();

    	//TODO: Participants com delegated nao estao sendo levados em conta
    	while ($property = $component->getProperty('attendee', FALSE, TRUE)) {
    	    $participant = array();

    	    $mailUser = trim(str_replace('MAILTO:', '', $property['value']));

    	    $participantID = ($tpID = self::_getParticipantByMail($mailUser, $schedulable['participants'])) ? $tpID : mt_rand() . '2(Formatter)';
    	    $participant['schedulable'] = $eventID;

    	    if (isset($params['status']) &&  ltrim( substr( $mailUser, 0 , strpos( $mailUser, '@' ) ), '@' )  ==  ltrim( substr( Config::me('mail'), 0, strpos( Config::me('mail'), '@' ) ), '@' ))
    		$participant['status'] = $params['status'];
    	    else
    		$participant['status'] = (isset($property['params']['PARTSTAT']) && constant('STATUS_' . $property['params']['PARTSTAT']) !== null ) ? constant('STATUS_' . $property['params']['PARTSTAT']) : STATUS_UNANSWERED;

    	    $participant['isOrganizer'] = '0';

    	    /* Verifica se este usuario é um usuario interno do ldap */
    	    $intUser = Controller::find(array('concept' => 'user'), array('id', 'isExternal'), array('filter' => array('OR', array('=', 'mail', $mailUser), array('=', 'mailAlternateAddress', $mailUser))));

            $user = null;
    	    if ($intUser && count($intUser) > 0) {
    		$participant['isExternal'] = isset($intUser[0]['isExternal']) ? $intUser[0]['isExternal'] : 0;
    		$participant['user'] = $intUser[0]['id'];
    	    } else {
    		$participant['isExternal'] = 1;
    		/* Gera um randon id para o contexto formater */
    		$userID = mt_rand() . '4(Formatter)';

    		$user['mail'] = $mailUser;
    		$user['isExternal'] = '1';
    		$user['name'] = ( isset($property['params']['CN']) ) ? $property['params']['CN'] : '';
    		$user['participants'] = array($participantID);
    		$participant['user'] = $userID;
    		$interation['user://' . $userID] = $user;
    	    }

    	    $interation['participant://' . $participantID] = $participant;
    	    $schedulable['participants'][] = $participantID;
    	};

    	if ($property = $component->getProperty('organizer', FALSE, TRUE)) {

    	    $mailUser = trim(str_replace('MAILTO:', '', $property['value']));



            if($participant = self::_getParticipantByMail($mailUser, $schedulable['participants'], true)){

                $participantID = $participant['id'];

            }else{

                $participant = array();

                $participantID = mt_rand() . '2(Formatter)';
                $participant['schedulable'] = $eventID;
                $participant['status'] = (isset($property['params']['PARTSTAT']) && constant('STATUS_' . $property['params']['PARTSTAT']) !== null ) ? constant('STATUS_' . $property['params']['PARTSTAT']) : STATUS_UNANSWERED;
                $participant['isOrganizer'] = '1';
                $participant['acl'] = 'rowi';
            }

    	    /* Verifica se este usuario é um usuario interno do ldap */
    	    $intUser = Controller::find(array('concept' => 'user'), array('id', 'isExternal'), array('filter' => array('OR', array('=', 'mail', $mailUser), array('=', 'mailAlternateAddress', $mailUser))));

            $user = null;
    	    if ($intUser && count($intUser) > 0 && $intUser[0]['id']) {
                $participant['isExternal'] = isset($intUser[0]['isExternal']) ? $intUser[0]['isExternal'] : 0;
                $participant['user'] = $intUser[0]['id'];
    	    } else {
                $participant['isExternal'] = 1;
                /* Gera um randon id para o contexto formater */
                $userID = mt_rand() . '4(Formatter)';

                $user['mail'] = $mailUser;
                $user['name'] = ( isset($property['params']['CN']) ) ? $property['params']['CN'] : '';
                $user['participants'] = array($participantID);
                $user['isExternal'] = '1';
                $participant['user'] = $userID;
                $interation['user://' . $userID] = $user;
    	    }

    	    $interation['participant://' . $participantID] = $participant;
    	    $schedulable['participants'][] = $participantID;
    	} else if (!isset($schedulable['participants']) || !is_array($schedulable['participants']) || count($schedulable['participants']) < 1) {//caso não tenha organizador o usuario se torna organizador
    	    $user = Controller::read(array('concept' => 'user', 'id' => $params['owner']), array('mail'));

    	    if (!self::_getParticipantByMail($user['mail'], $schedulable['participants'])) {
        		$participantID = mt_rand() . '2(Formatter)';

        		$participant['schedulable'] = $eventID;
        		$participant['status'] = STATUS_CONFIRMED;
        		$participant['isOrganizer'] = '1';
        		$participant['acl'] = 'rowi';
        		$participant['isExternal'] = 0;
        		$participant['user'] = $params['owner'];
        		$interation['participant://' . $participantID] = $participant;
        		$schedulable['participants'][] = $participantID;
    	    }
    	}
    	
    	$alarms = array();
    	
    	/* Definindo ALARMES */
    	while ($alarmComp = $component->getComponent('valarm'))
    	{
    		$alarm = array();
    		$alarmID = mt_rand() . '6(Formatter)';
    		$action =  $alarmComp->getProperty('action', false, true);
    		$trygger = $alarmComp->getProperty('trigger', false, true);
    		$alarm['type'] = self::decodeAlarmAction($action['value']);

    		 if(isset($trygger['value']['day']))
    		{
    			$alarm['time'] = $trygger['value']['day'];
    			$alarm['unit'] = 'd';
    		}
    		else if(isset($trygger['value']['hour']))
    		{
    			$alarm['time'] = $trygger['value']['hour'];
    			$alarm['unit'] = 'h';
     		}
    		else if(isset($trygger['value']['min']))
    		{
    			$alarm['time'] = $trygger['value']['min'];
    			$alarm['unit'] = 'm';
    		}
    		
    		foreach ($interation as $iint => &$vint)
    		{
    			if(isset($vint['user']) && $vint['user'] == Config::me('uidNumber'))
    			{
    				$alarm['participant'] = str_replace('participant://', '', $iint);	
    				$vint['alarms'][] = $alarmID;
    			}
    		}
    		$alarm['schedulable'] = $eventID;
    				
    		$interation['alarm://' . $alarmID ] = $alarm;
    		
    	}
    	
    	
    	/* Definindo DTSTAMP */
    	if ($dtstamp = self::_getTime($component, 'dtstamp'))
    	    $schedulable['dtstamp'] = $dtstamp;

    	/* Definindo TRANSP */
    	if (($tranp = $component->getProperty('transp', false, true)) && $tranp && is_string($tranp) && strtoupper($tranp) == 'OPAQUE')
    	    $schedulable['transparent'] = 1;

    	/* Definindo last_update */
    	if ($lastUpdate = self::_getTime($component, 'LAST-MODIFIED'))
    	    $schedulable['lastUpdate'] = $lastUpdate;


    	if ($sequence = $component->getProperty('SEQUENCE', false, false))
    	    $schedulable['sequence'] = $sequence;

    	if ($uid = $component->getProperty('uid', false, false))
    	$schedulable['uid'] = $uid;

    	while ($attach = $component->getProperty('ATTACH', FALSE, TRUE)) {

    	    $attachCurrent = array('name' => $attach['params']['X-FILENAME'],
    		'size' => strlen($attach['value']),
    		'type' => self::_getContentType($attach['params']['X-FILENAME'])
    	    );

    	    $ids = Controller::find(array('concept' => 'attachment'), array('id'), array('filter' => array('AND', array('=', 'name', $attachCurrent['name']), array('=', 'size', $attachCurrent['size']), array('=', 'type', $attachCurrent['type']))));

    	    if (!is_array($ids)) {
    		$attachCurrent['source'] = $attach['value'];
    		//insere o anexo no banco e pega id para colcar no relacionamento				
    		$idAttachment = Controller::create(array('concept' => 'attachment'), $attachCurrent);
    	    }else
    		$idAttachment = array('id' => $ids[0]['id']);

    	    $calendarToAttachmentId = mt_rand() . '2(Formatter)';
    	    $calendarToAttachment['attachment'] = $idAttachment['id'];
    	    $calendarToAttachment['schedulable'] = $eventID;
    	    $interation['schedulableToAttachment://' . $calendarToAttachmentId] = $calendarToAttachment;

    	    $schedulable['attachments'][] = $calendarToAttachmentId;
    	}

        $schedulable['type'] = '1';

    	$interation['schedulable://' . $eventID] = $schedulable;

        return $interation;
    }
    
    static private function _makeVTODO($schedulable, $component, $params) {
	$interation = array();
	$todoID = isset($schedulable['id']) ? $schedulable['id'] : mt_rand() . '(Formatter)';

	/* Data de Inicio */
	$startTime = $component->getProperty('dtstart', false, true);

	$tzid = (isset($startTime['params']['TZID']) ? $startTime['params']['TZID'] : $params['X-WR-TIMEZONE']);

	/* Tiem zone do evento */
	if ($tzid){
	    $tzid = self::nomalizeTZID($tzid);
	    $schedulable['timezone'] = $tzid;
	}else
	    $schedulable['timezone'] = isset($params['calendar_timezone']) ? $params['calendar_timezone'] : 'America/Sao_Paulo';

	$objTimezone = new DateTimeZone($schedulable['timezone']);

	if (isset($startTime['params']['VALUE']) && $startTime['params']['VALUE'] === 'DATE' && isset($params['calendar_timezone'])) {
	    $schedulable['allDay'] = 1;
	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($startTime['value'])) . '000';
	} elseif ($tzid && !isset($startTime['value']['tz'])) {/* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) - self::_getTzOffset('UTC', $tzid, '@' . self::date2timestamp($startTime['value'])) . '000';
	    $schedulable['allDay'] = 0;
	} else {
	    $schedulable['startTime'] = self::date2timestamp($startTime['value']) . '000';
	    if (strpos($params['prodid'], 'Outlook') !== false) {
		//Se o ics veio em utc não aplicar horario de verão
		$sTime = new DateTime('@' . (int) ($schedulable['startTime'] / 1000), new DateTimeZone('UTC'));
		$sTime->setTimezone($objTimezone);
		if ($sTime->format('I')) //Se o ics veio em utc não aplicar horario de verão
		    $schedulable['startTime'] = $schedulable['startTime'] - 3600000;
	    }
	}

	/* Data de Termino */
	if($due = $component->getProperty('due', false, true)){

        $tzid = isset($due['params']['TZID']) ? $due['params']['TZID'] : $params['X-WR-TIMEZONE'];

        if($tzid)
            $tzid = self::nomalizeTZID($tzid);

        if (isset($due['params']['VALUE']) && $due['params']['VALUE'] === 'DATE' && isset($params['calendar_timezone']))
            $schedulable['due'] = self::date2timestamp($due['value']) - self::_getTzOffset('UTC', $schedulable['timezone'], '@' . self::date2timestamp($due['value'])) . '000';
        else if ($tzid && !isset($due['value']['tz'])) /* Caso não tenha um tz na data mais exista um parametro TZID deve ser aplicado o timezone do TZID a data */
            $schedulable['due'] = self::date2timestamp($due['value']) - self::_getTzOffset('UTC', $tzid, '@' . self::date2timestamp($due['value'])) . '000';
        else {
            $schedulable['due'] = self::date2timestamp($due['value']) . '000';
            if (strpos($params['prodid'], 'Outlook') !== false) {
            //Se o ics veio em utc não aplicar horario de verão
            $dueTime = new DateTime('@' . (int) ($schedulable['due'] / 1000), new DateTimeZone('UTC'));
            $dueTime->setTimezone($objTimezone);

            if ($dueTime->format('I'))
                $schedulable['due'] = $schedulable['due'] - 3600000;
            }
        }
        $schedulable['endTime'] = $schedulable['due'];
    }else
        $schedulable['endTime'] = $schedulable['startTime'];

	$schedulable['type'] = '2'; //type schedulable
	$schedulable['summary'] = mb_convert_encoding($component->getProperty('summary', false, false), 'ISO-8859-1', 'UTF-8,ISO-8859-1');


	/* Definindo Description */
	if ($desc = $component->getProperty('description', false, false))
	    $schedulable['description'] = mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $desc), 'ISO-8859-1', 'UTF-8,ISO-8859-1');

	/* Definindo Class */
	$class = $component->getProperty('class', false, false);
	if ($class && defined(constant(strtoupper('CLASS_' . $class))))
	    $schedulable['class'] = constant(strtoupper('CLASS_' . $class));
	else if (!isset($schedulable['class']))
	    $schedulable['class'] = CLASS_PRIVATE; // padrão classe private

	$schedulable['calendar'] = $params['calendar'];

	$participantsInTodo = array();

	//TODO: Participants com delegated nao estao sendo levados em conta
	while ($property = $component->getProperty('attendee', FALSE, TRUE)) {
	    $participant = array();

	    $mailUser = trim(str_replace('MAILTO:', '', $property['value']));

	    $participantID = ($tpID = self::_getParticipantByMail($mailUser, $schedulable['participants'])) ? $tpID : mt_rand() . '2(Formatter)';
	    $participant['schedulable'] = $todoID;

	    if (isset($params['status']) && $mailUser == Config::me('mail'))
		$participant['status'] = $params['status'];
	    else
		$participant['status'] = (isset($property['params']['PARTSTAT']) && constant('STATUS_' . $property['params']['PARTSTAT']) !== null ) ? constant('STATUS_' . $property['params']['PARTSTAT']) : STATUS_UNANSWERED;


	    $participant['isOrganizer'] = '0';

	    /* Verifica se este usuario é um usuario interno do ldap */
	    $intUser = Controller::find(array('concept' => 'user'), array('id', 'isExternal'), array('filter' => array('OR', array('=', 'mail', $mailUser), array('=', 'mailAlternateAddress', $mailUser))));

	    $user = null;
	    if ($intUser && count($intUser) > 0) {
		$participant['isExternal'] = isset($intUser[0]['isExternal']) ? $intUser[0]['isExternal'] : 0;
		$participant['user'] = $intUser[0]['id'];
	    } else {
		$participant['isExternal'] = 1;
		/* Gera um randon id para o contexto formater */
		$userID = mt_rand() . '4(Formatter)';

		$user['mail'] = $mailUser;
		$user['isExternal'] = '1';
		$user['name'] = ( isset($property['params']['CN']) ) ? $property['params']['CN'] : '';
		$user['participants'] = array($participantID);
		$participant['user'] = $userID;
		$interation['user://' . $userID] = $user;
	    }

	    $interation['participant://' . $participantID] = $participant;
	    $schedulable['participants'][] = $participantID;
	};

	if ($property = $component->getProperty('organizer', FALSE, TRUE)) {
	    $participant = array();
	    $mailUser = trim(str_replace('MAILTO:', '', $property['value']));

        if($participant = self::_getParticipantByMail($mailUser, $schedulable['participants'], true)){

            $participantID = $participant['id'];

        }else{

            $participant = array();

            $participantID = mt_rand() . '2(Formatter)';
            $participant['schedulable'] = $todoID;
            $participant['status'] = (isset($property['params']['PARTSTAT']) && constant('STATUS_' . $property['params']['PARTSTAT']) !== null ) ? constant('STATUS_' . $property['params']['PARTSTAT']) : STATUS_UNANSWERED;
            $participant['isOrganizer'] = '1';
            $participant['acl'] = 'rowi';
        }

	    /* Verifica se este usuario é um usuario interno do ldap */
	    $intUser = Controller::find(array('concept' => 'user'), array('id', 'isExternal'), array('filter' => array('OR', array('=', 'mail', $mailUser), array('=', 'mailAlternateAddress', $mailUser))));

	    $user = null;
	    if ($intUser && count($intUser) > 0) {
		$participant['isExternal'] = isset($intUser[0]['isExternal']) ? $intUser[0]['isExternal'] : 0;
		$participant['user'] = $intUser[0]['id'];
	    } else {
		$participant['isExternal'] = 1;
		/* Gera um randon id para o contexto formater */
		$userID = mt_rand() . '4(Formatter)';

		$user['mail'] = $mailUser;
		$user['name'] = ( isset($property['params']['CN']) ) ? $property['params']['CN'] : '';
		$user['participants'] = array($participantID);
		$user['isExternal'] = '1';
		$participant['user'] = $userID;
		$interation['user://' . $userID] = $user;
	    }

	    $interation['participant://' . $participantID] = $participant;
	    $schedulable['participants'][] = $participantID;
	    } else if (!isset($schedulable['participants']) || !is_array($schedulable['participants']) || count($schedulable['participants']) < 1) {//caso não tenha organizador o usuario se torna organizador
	    $user = Controller::read(array('concept' => 'user', 'id' => $params['owner']), array('mail'));

	    if (!self::_getParticipantByMail($user['mail'], $schedulable['participants'])) {
		$participantID = mt_rand() . '2(Formatter)';

		$participant['schedulable'] = $todoID;
		$participant['status'] = STATUS_CONFIRMED;
		$participant['isOrganizer'] = '1';
		$participant['acl'] = 'rowi';
		$participant['isExternal'] = 0;
		$participant['user'] = $params['owner'];
		$interation['participant://' . $participantID] = $participant;
		$schedulable['participants'][] = $participantID;
	    }
	}
	
	$alarms = array();
	
	/* Definindo ALARMES */
	while ($alarmComp = $component->getComponent('valarm'))
	{
		$alarm = array();
		$alarmID = mt_rand() . '6(Formatter)';
		$action =  $alarmComp->getProperty('action', false, true);
		$trygger = $alarmComp->getProperty('trigger', false, true);
		$alarm['type'] = self::decodeAlarmAction($action['value']);

		 if(isset($trygger['value']['day']))
		{
			$alarm['time'] = $trygger['value']['day'];
			$alarm['unit'] = 'd';
		}
		else if(isset($trygger['value']['hour']))
		{
			$alarm['time'] = $trygger['value']['hour'];
			$alarm['unit'] = 'h';
 		}
		else if(isset($trygger['value']['min']))
		{
			$alarm['time'] = $trygger['value']['min'];
			$alarm['unit'] = 'm';
		}
		
		foreach ($interation as $iint => &$vint)
		{
			if(isset($vint['user']) && $vint['user'] == Config::me('uidNumber'))
			{
				$alarm['participant'] = str_replace('participant://', '', $iint);	
				$vint['alarms'][] = $alarmID;
			}
		}
		$alarm['schedulable'] = $eventID;
				
		$interation['alarm://' . $alarmID ] = $alarm;
		
	}
	
	
	/* Definindo DTSTAMP */
	if ($dtstamp = self::_getTime($component, 'dtstamp'))
	    $schedulable['dtstamp'] = $dtstamp;

	/* Definindo TRANSP */
	if (($tranp = $component->getProperty('transp', false, true)) && $tranp && is_string($tranp) && strtoupper($tranp) == 'OPAQUE')
	    $schedulable['transparent'] = 1;

	/* Definindo last_update */
	if ($lastUpdate = self::_getTime($component, 'LAST-MODIFIED'))
	    $schedulable['lastUpdate'] = $lastUpdate;

	if ($status = $component->getProperty('status', false, false))
		$schedulable['status'] = self::decodeStatusTodo(mb_convert_encoding(str_ireplace(array('\n', '\t'), array("\n", "\t"), $status), 'UTF-8', 'UTF-8,ISO-8859-1'));

	if ($sequence = $component->getProperty('SEQUENCE', false, false))
	    $schedulable['sequence'] = $sequence;

	if ($uid = $component->getProperty('uid', false, false))
	    ;
	$schedulable['uid'] = $uid;

	while ($attach = $component->getProperty('ATTACH', FALSE, TRUE)) {

	    $attachCurrent = array('name' => $attach['params']['X-FILENAME'],
		'size' => strlen($attach['value']),
		'type' => self::_getContentType($attach['params']['X-FILENAME'])
	    );

	    $ids = Controller::find(array('concept' => 'attachment'), array('id'), array('filter' => array('AND', array('=', 'name', $attachCurrent['name']), array('=', 'size', $attachCurrent['size']), array('=', 'type', $attachCurrent['type']))));

	    if (!is_array($ids)) {
		$attachCurrent['source'] = $attach['value'];
		//insere o anexo no banco e pega id para colcar no relacionamento				
		$idAttachment = Controller::create(array('concept' => 'attachment'), $attachCurrent);
	    }else
		$idAttachment = array('id' => $ids[0]['id']);

	    $calendarToAttachmentId = mt_rand() . '2(Formatter)';
	    $calendarToAttachment['attachment'] = $idAttachment['id'];
	    $calendarToAttachment['schedulable'] = $eventID;
	    $interation['schedulableToAttachment://' . $calendarToAttachmentId] = $calendarToAttachment;

	    $schedulable['attachments'][] = $calendarToAttachmentId;
	}

	$interation['schedulable://' . $todoID] = $schedulable;



    return $interation;
    }

    static private function _getSchedulable($uid) {
	$schedulable = Controller::find(array('concept' => 'schedulable'), false, array('filter' => array('=', 'uid', $uid), 'deepness' => 2));
	return (isset($schedulable[0])) ? $schedulable[0] : false;
    }

    static private function _existInMyCalendars($id, $owner) {
	$sig = Controller::find(array('concept' => 'calendarSignature'), array('user', 'calendar', 'isOwner'), array('filter' => array('AND', array('=', 'isOwner', '1'), array('=', 'user', $owner))));
	$sig2 = Controller::find(array('concept' => 'calendarToPermission'), array('calendar'), array('filter' => array('AND', array('*', 'acl', 'w'), array('=', 'user', $owner))));

	$calendars = array();
	if(is_array($sig))
		foreach ($sig as $val)
		    $calendars[] = $val['calendar'];
	if(is_array($sig2))
		foreach ($sig2 as $val)
		    $calendars[] = $val['calendar'];



	$return = Controller::find(array('concept' => 'calendarToSchedulable'), null, array('filter' => array('AND', array('IN', 'calendar', $calendars), array('=', 'schedulable', $id))));

	return (isset($return[0])) ? $return[0]['calendar'] : false;
    }

    static private function _getTime(&$component, $property) {
	if ($date = $component->getProperty($property, false, true))
	    return (isset($date['params']['TZID']) && !isset($date['value']['tz'])) ? (self::date2timestamp($date['value']) - self::_getTzOffset('UTC', $date['params']['TZID'], '@' . self::date2timestamp($date['value']))) . '000' : self::date2timestamp($date['value']) . '000';

	return false;
    }

    static private function _getContentType($fileName) {
	$strFileType = strtolower(substr($fileName, strrpos($fileName, '.')));

	switch ($strFileType) {
	    case ".asf": return "video/x-ms-asf";
	    case ".avi": return "video/avi";
	    case ".doc": return "application/msword";
	    case ".zip": return "application/zip";
	    case ".xls": return "application/vnd.ms-excel";
	    case ".gif": return "image/gif";
	    case ".bmp": return "image/bmp";
	    case ".jpeg":
	    case ".jpg": return "image/jpeg";
	    case ".wav": return "audio/wav";
	    case ".mp3": return "audio/mpeg3";
	    case ".mpeg":
	    case ".mpg": return "video/mpeg";
	    case ".rtf": return "application/rtf";
	    case ".html":
	    case ".htm": return "text/html";
	    case ".xml": return "text/xml";
	    case ".xsl": return "text/xsl";
	    case ".css": return "text/css";
	    case ".php": return "text/php";
	    case ".asp": return "text/asp";
	    case ".pdf": return "application/pdf";
	    case ".png": return "image/png";
	    case ".txt": return "text/plain";
	    case ".log": return "text/plain";
	    case ".wmv": return "video/x-ms-wmv";
	    case ".sxc": return "application/vnd.sun.xml.calc";
	    case ".odt": return "application/vnd.oasis.opendocument.text";
	    case ".stc": return "application/vnd.sun.xml.calc.template";
	    case ".sxd": return "application/vnd.sun.xml.draw";
	    case ".std": return "application/vnd.sun.xml.draw.template";
	    case ".sxi": return "application/vnd.sun.xml.impress";
	    case ".sti": return "application/vnd.sun.xml.impress.template";
	    case ".sxm": return "application/vnd.sun.xml.math";
	    case ".sxw": return "application/vnd.sun.xml.writer";
	    case ".sxq": return "application/vnd.sun.xml.writer.global";
	    case ".stw": return "application/vnd.sun.xml.writer.template";
	    case ".pps": return "application/vnd.ms-powerpoint";
	    case ".odt": return "application/vnd.oasis.opendocument.text";
	    case ".ott": return "application/vnd.oasis.opendocument.text-template";
	    case ".oth": return "application/vnd.oasis.opendocument.text-web";
	    case ".odm": return "application/vnd.oasis.opendocument.text-master";
	    case ".odg": return "application/vnd.oasis.opendocument.graphics";
	    case ".otg": return "application/vnd.oasis.opendocument.graphics-template";
	    case ".odp": return "application/vnd.oasis.opendocument.presentation";
	    case ".otp": return "application/vnd.oasis.opendocument.presentation-template";
	    case ".ods": return "application/vnd.oasis.opendocument.spreadsheet";
	    case ".ots": return "application/vnd.oasis.opendocument.spreadsheet-template";
	    case ".odc": return "application/vnd.oasis.opendocument.chart";
	    case ".odf": return "application/vnd.oasis.opendocument.formula";
	    case ".odi": return "application/vnd.oasis.opendocument.image";
	    case ".ndl": return "application/vnd.lotus-notes";
	    case ".eml": return "text/plain";
	    case ".ps" : return "application/postscript";
	    default : return "application/octet-stream";
	}
    }
    
    public function codeAlarmAction( $action )
    {
    	switch ($action)
    	{
    		case ALARM_MAIL : 
    				return  'EMAIL';
    				break;
    		case ALARM_ALERT :
    				return  'DISPLAY';
    				break;
    		case 'mail' :
    					return  'EMAIL';
    					break;
    		case 'alert'  :
    					return  'DISPLAY';
    					break;
    	}
    	
    }
    
    public function decodeAlarmAction( $action )
    {
    	switch ( $action )
    	{
    		case 'EMAIL'  :
    			return  'mail';
    			break;
    		case 'DISPLAY' :
    			return  'alert';
    			break;

    	}
    
    }

    private static function schedulable2calendarToObject($Schedulable, $user = false) {
        return Controller::service('PostgreSQL')->execResultSql('SELECT calendar_to_calendar_object.id as calendar_to_calendar_Object , calendar.name as calendar_name ,calendar.location as calendar_location, calendar.id as calendar_id FROM calendar_to_calendar_object , calendar , calendar_signature'
            . ' WHERE calendar_signature.user_uidnumber = ' . $user ? $user : Config::me('uidNumber')
            //      .' AND calendar_signature.is_owner = 1'
            . ' AND calendar_signature.calendar_id = calendar.id'
            . ' AND calendar_to_calendar_object.calendar_id = calendar.id'
            . ' AND calendar_to_calendar_object.calendar_object_id = ' . addslashes($Schedulable));
    }
}

?>
