<?php
/**
 * Insere o código javascript necessário para abrir uma nova janela 
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros (vazio)
 * @param object &$smarty Instância do objeto smarty em uso 
 * @return string $output código do script
 * @access public 
 */
function smarty_function_wf_window_open_init($params, &$smarty)
{
$output = <<<EOF
	<script language="javascript1.2">
	function wf_open_window(url, name, width, height, position, features) {
		if (position == 'right') {
			newScreenX = screen.width - width;
			newScreenY = 0;
		} else {
			if (position == 'center') {
				newScreenX = (screen.width / 2) - (width / 2);
				newScreenY = 0;
			} else {
				newScreenX = 1;
				newScreenY = 0;
			}
		}
		
		name = window.open(url, '', 'width='+width+',height='+height+',' +
				'screenX='+newScreenX+',left='+newScreenX+',screenY='+newScreenY+',' + 
						'top='+newScreenY+','+features);	
	}
	</script>
EOF;
	return $output;
}
?>
