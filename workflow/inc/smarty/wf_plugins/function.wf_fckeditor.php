<?php
/**
* Este plugin insere o editor FCKeditor para edição de texto rico (RTF). 
* Requires PHP >= 4.3.0
* @package Smarty
* @subpackage wf_plugins
* @version 1.0
* @author  gazoot (gazoot care of gmail dot com)
* @author  Sidnei Augusto Drovetto Jr. - drovetto@gmail.com (revision)
* @param object &$smarty Instância do objeto smarty em uso 
* @param array $params Parameters array. (Default values for optional parameters are taken from fckeditor.js)<br>
*  All other parameters used in the function will be put into the<br>
*  configuration section,CustomConfigurationsPath is useful for example.
* - name Editor instance name (form field name)
* - value optional data that control will start with, default is taken from the javascript file
* - width optional width (css units)
* - height optional height (css units)
* - toolbar_set optional what toolbar to use from configuration
* - check_browser optional check the browser compatibility when rendering the editor
* - display_errors optional show error messages on errors while rendering the editor
* @link http://wiki.fckeditor.net/Developer%27s_Guide/Configuration/Configurations_File for more configuration info.
* @return string $out codigo que insere o FCKeditor
* @access public
*/
function smarty_function_wf_fckeditor($params, &$smarty)
{
    /* check for missing parameters */
	if(!isset($params['name']) || empty($params['name']))
		$smarty->trigger_error('wf_fckeditor: required parameter "name" missing');

    /* convert the parameters from our naming convention to the one used by the FCKEditor */
    $paramsConversion = array(
        'name' =>'InstanceName',
        'value' => 'Value',
        'width' => 'Width',
        'height' => 'Height',
        'toolbar_set' => 'ToolbarSet',
        'check_browser' => 'CheckBrowser',
        'display_errors' => 'DisplayErrors'
    );

    $paramsCopy = $params;
    foreach ($paramsConversion as $before => $after)
    {
        if (isset($params[$before]))
        {
            unset($params[$before]);
            $params[$after] = $paramsCopy[$before];
        }
    }

	static $base_arguments = array();
	static $config_arguments = array();

	// Test if editor has been loaded before
	if(!count($base_arguments)) $init = TRUE;
	else $init = FALSE;

	// BasePath must be specified once.
    $base_arguments['BasePath'] = $GLOBALS['phpgw_info']['server']['webserver_url'] . SEP . 'library' . SEP . 'ckeditor' . SEP;

	$base_arguments['InstanceName'] = $params['InstanceName'];

	if(isset($params['Value'])) $base_arguments['Value'] = $params['Value'];
	if(isset($params['Width'])) $base_arguments['Width'] = $params['Width'];
	if(isset($params['Height'])) $base_arguments['Height'] = $params['Height'];
	if(isset($params['ToolbarSet'])) $base_arguments['ToolbarSet'] = $params['ToolbarSet'];
	if(isset($params['CheckBrowser'])) $base_arguments['CheckBrowser'] = $params['CheckBrowser'];
    if(isset($params['DisplayErrors'])) $base_arguments['DisplayErrors'] = $params['DisplayErrors'];

	// Use all other parameters for the config array (replace if needed)
	$other_arguments = array_diff_assoc($params, $base_arguments);
	$config_arguments = array_merge($config_arguments, $other_arguments);

	$out = '';

	if($init)
	{
		$out .= '<script type="text/javascript" src="' . $base_arguments['BasePath'] . 'ckeditor.js"></script>';
	}
	$out .= '<textarea cols="80" id="'.$base_arguments['InstanceName'].'" name="'.$base_arguments['InstanceName'].'" rows="10">'.$base_arguments['Value'].'</textarea>';

	$out .= '<script type="text/javascript">'.
				'CKEDITOR.replace( \''.$base_arguments['InstanceName'].'\',{'.
					'removePlugins : \'elementspath\','.
					'skin : \'office2003\','.
					'toolbar : \'Full\''.
					'}'.
				');';

	foreach($base_arguments as $key => $value)
	{
		if(!is_bool($value))
		{
			// Fix newlines, javascript cannot handle multiple line strings very well.
			$value = '"' . preg_replace("/[\r\n]+/", '" + $0"', addslashes($value)) . '"';
		}
		$out .= "CKEDITOR.$key = $value; ";
	}

	foreach($config_arguments as $key => $value)
	{
		if(!is_bool($value))
		{
			$value = '"' . preg_replace("/[\r\n]+/", '" + $0"', addslashes($value)) . '"';
		}
		$out .= "CKEDITOR.config[\"$key\"] = $value; ";
	}

	$out .= "</script>\n";

	return $out;
}
?>
