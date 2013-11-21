<?php
$BALANCEID = '';
if(isset($_COOKIE['BALANCEID'])) {
    $BALANCEID = $_COOKIE['BALANCEID'];
}
echo "BALANCEID:$BALANCEID\n";

$sessionid = '';
if(isset($_COOKIE['sessionid'])) {
    $sessionid = $_COOKIE['sessionid'];
}
echo "sessionid:$sessionid\n";

$session_save_path = ini_get('session.save_path');
$session_save_path = trim($session_save_path);
$last_char = $session_save_path[strlen($session_save_path)-1];
if(($last_char != '/') && ($last_char != '\\')) {
    $session_save_path .= '/';
}
$session_file = $session_save_path . 'sess_' . $_COOKIE['sessionid'];
echo "session_file:$session_file\n";

$user_id = '';
if(is_file($session_file)) {

    $session_contents = file_get_contents($session_file);
    $session_contents_array = explode('|', $session_contents);

    for($k = 0; $k < count($session_contents_array); $k++) {
        $ss = unserialize($session_contents_array[$k]);
        if(($ss['session_lid']) && ($ss['session_id']) && ($ss['session_id'] == $_COOKIE['sessionid'])) {
            $user_id = $ss['session_lid'];
            break;
        }
    }
}
echo "user_id:$user_id\n";
?>
