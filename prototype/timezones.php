<?php

$zones = timezone_identifiers_list();
$Time = new DateTime('now', new DateTimeZone('UTC'));
$timezone = array();

        
foreach ($zones as $zone) 
{
    $timezone['timezones'][$zone] = $Time->setTimezone(new DateTimeZone($zone))->format('O');
}

$localtime = localtime(time(), true);
$timezone['isDaylightSaving'] =  !!$localtime['tm_isdst'] ? 1 : 0;

echo json_encode($timezone);

?>