<?php
require_once 'common.inc.php';

$GLOBALS['phpgw_info']['flags']['currentapp'] = "workflow";
if (!isset($_SESSION['phpgw_info']['workflow']['server']))
{
	$_SESSION['phpgw_info']['workflow'] = array();

	$membership = $GLOBALS['phpgw']->accounts->membership();
	$_SESSION['phpgw_info']['workflow']['user_groups'] = array();
	foreach($membership as $idx => $group)
		$_SESSION['phpgw_info']['workflow']['user_groups'][] = $group['account_id'];

	$_SESSION['phpgw_info']['workflow']['account_id'] = $GLOBALS['phpgw_info']['user']['account_id'];
	$_SESSION['phpgw_info']['workflow']['phpgw_api_inc'] = dirname(__FILE__) . '/../../phpgwapi/inc';
	$_SESSION['phpgw_info']['workflow']['server']['db_name'] = $GLOBALS['phpgw_info']['server']['db_name'];
	$_SESSION['phpgw_info']['workflow']['server']['db_host'] = $GLOBALS['phpgw_info']['server']['db_host'];
	$_SESSION['phpgw_info']['workflow']['server']['db_port'] = $GLOBALS['phpgw_info']['server']['db_port'];
	$_SESSION['phpgw_info']['workflow']['server']['db_user'] = $GLOBALS['phpgw_info']['server']['db_user'];
	$_SESSION['phpgw_info']['workflow']['server']['db_pass'] = $GLOBALS['phpgw_info']['server']['db_pass'];
	$_SESSION['phpgw_info']['workflow']['server']['db_type'] = $GLOBALS['phpgw_info']['server']['db_type'];
	$_SESSION['phpgw_info']['workflow']['server']['webserver_url'] = $GLOBALS['phpgw_info']['server']['webserver_url'];
}

$title = lang("External Applications");
$bo	= Factory::getInstance('bo_userinterface');
$externals 	= $bo -> externals();
$extra_data = '';
$next_br = "0";
$extra_data = "<div style='width:100%'><table width='100%' cellpadding='0' cellspacing='0'>";
foreach($externals as $idx => $external){
	if($next_br == 0){
		$extra_data .= "<tr>";
	}
	$extra_data .=  "<td align=center style='valign:top;width:10em;padding:2px'><a target='_blank' href='".$external["wf_ext_link"]."' nowrap><img width='32px' height='32px' align='center' src='".$external["image"]."'/><br>".$external["name"]."</a></td>";
	++$next_br;
	if($next_br == 3){
		$extra_data .= "</tr>";
		$next_br = 0;
	}
}
if($next_br == 3){
	$extra_data .= "</tr>";
}
$extra_data .= "</table></div>";
$portalbox = Factory::getInstance('listbox',
	Array(
		'title'     => $title,
		'primary'   => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
		'secondary' => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
		'tertiary'  => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
		'width'     => '100%',
		'outerborderwidth' => '0',
		'header_background_image' => $GLOBALS['phpgw']->common->image('phpgwapi/templates/phpgw_website','bg_filler')
	)
);

echo "\n".'<!-- BEGIN Workflow info -->'."\n".$portalbox->draw($extra_data).'<!-- END Workflow info -->'."\n";
?>
