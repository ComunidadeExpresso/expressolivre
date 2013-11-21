<?php

require_once 'api/controller.php';

$method = isset($_REQUEST['analize']) && $_REQUEST['analize'] ? 'analize' : 'parse';

$file = false;

if (isset($_FILES['files']))
    $file = file_get_contents($_FILES['files']['tmp_name'][0], $_FILES['files']['size'][0]);
else if (isset($_REQUEST['data']))
    $file = $_REQUEST['data'];
else if (isset($_FILES['data']))
    $file = file_get_contents($_FILES['data']['tmp_name']);

if ($file)
    $args = Controller::call($method, array('service' => $_REQUEST['type']), $file, $_REQUEST['params']
    );
else
    $args = array('Error' => 'No source file');

if (isset($args[0]) && empty($args[0]))
    echo json_encode($args);
else {
    if (!isset($_REQUEST['readable']) || !$_REQUEST['readable']) {
	require_once 'Sync.php';
	$args = toUtf8($args);
	echo json_encode($args);
    }else
	print_r(serialize($args));
}

function srtToUtf8($data) {
    return mb_convert_encoding($data, 'UTF-8', 'UTF-8 , ISO-8859-1');
}

function toUtf8($data) {
    if (is_array($data)) {
	$return = array();
	foreach ($data as $i => $v)
	    $return[srtToUtf8($i)] = (is_array($v)) ? toUtf8($v) : srtToUtf8($v);

	return $return;
    }else
	return srtToUtf8($data);
}

?>
