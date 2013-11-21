<?php
function wf_debug($name = null)
{
	/* variable used to avoid the insertion of the same javascript file */
	static $workflowFunctionsWf_debug = false;

	/* load the backtrace */
	$backTrace = debug_backtrace();

	/* if no name is supplied, then use de name of the file from which the wf_debug was called */
	if (is_null($name))
		$name = substr(strrchr($backTrace[0]['file'], '/'), 1);

	/* prepare the backtrace array */
	$importantBacktrace = array();
    $backTrace_count = count($backTrace);
	for ($i = 0; $i < $backTrace_count; ++$i)
	{
		if (strpos($backTrace[$i]['file'], 'class.run_activity.inc.php') !== false)
			break;
		$importantBacktrace[] = $backTrace[$i];
	}
	$importantBacktrace = array_reverse($importantBacktrace);

	/* initialize the $output variable */
	$output = '';

	/* if it's the first time the function is called, then insert the javascript file reference */
	if (!$workflowFunctionsWf_debug)
	{
		$output = '<script language="javascript1.2" src="workflow/js/jscode/debug.js"></script>';
		$workflowFunctionsWf_debug = true;
	}

	/* start the visible output */
	$output .= '<table style="border: 1px solid" width="100%">';
	$output .= '<tr style="cursor: pointer; background-color: #000000; color: #FFFFFF" onclick="toggleTableDisplay(this.parentNode);"><th colspan="2">Debug: ' . $name . '</th></tr>';

	/* start the backtrace section */
	$output .= '<tr>';
	$output .= '<td>Backtrace</td>';
	$output .= '<td>';

	$i = 1;
	$output .= '<table>';
	foreach ($importantBacktrace as $call)
	{
		/* show some general information */
		$output .= '<tr' . ((($i % 2) == 1) ? ' bgcolor="#FFFFFF"' : '') . '><td><font color="red"><strong>' . $i++ . '- </strong></font></td><td>';
		$output .= '<strong>Arquivo:</strong> ' . $call['file'] . '<br/>';
		$output .= '<strong>Linha:</strong> ' . $call['line'] . '<br/>';

		/* show different information for methods and functions */
		if (isset($call['class']))
			$output .= '<strong>Método:</strong> ' . $call['class'] . $call['type'] . $call['function'] . '()<br/>';
		else
			$output .= '<strong>Função:</strong> ' . $call['function'] . '()<br/>';

		/* show the parameters used in the call */
		$output .= '<strong>Parâmetros:</strong> ';
		if (empty($call['args']))
		{
			$output .= '<i>nenhum</i><br/>';
		}
		else
		{
			$output .= '<br/>';
			$j = 1;
			foreach ($call['args'] as $arg)
			{
				if (is_array($arg))
					$arg = '<i>array</i>';
				else
					if (is_object($arg))
						$arg = '<i>object</i>';
				$output .= '&nbsp;&nbsp;&nbsp;<strong>#' . $j++ . ':</strong> ' . $arg . '<br/>';
			}
		}

		$output .= '</td></tr>';
	}
	$output .= '</table>';
	$output .= '</td></tr>';

	/* start the $_REQUEST section */
	$randomName = mt_rand() . '_' . mt_rand(); /* unique preffix */
	$formInput = '';
	$output .= '<tr><td>$_REQUEST</td><td>';
	$output .= '<form method="post">';
	$output .= '<table><tr><th style="font-size: 12px">Propriedade</th><th style="font-size: 12px">Valor</th></tr>';
	foreach ($_REQUEST as $key => $value)
	{
		/* highlight the 'action' and 'params' elements */
		$showKey = $key;
		if (($key == 'action') || ($key == 'params'))
			$showKey = '<font color="red"><strong>' . $key . '</strong></font>';
		/* generate the table row */
		$output .= '<tr>';
		$output .= '<td>' . $showKey . '</td>';
		$output .= '<td id="' . $randomName . '_' . $key . '_td">' . $value . '</td>';

		/* allow the developer to edit the parameters */
		$output .= '<td><a href="#" onclick="' . "editField('{$randomName}', '{$key}'); return false;" . '">editar</a></td>';
		$output .= '</tr>';

		/* generate the form that can be submited at any time */
		$formInput .= '<input type="hidden" id="' . $randomName . '_' . $key . '" name="' . $key . '" value="' . $value . '"/>';
	}
	$output .= '<tr><td colspan="3"><strong>Submeter formulário novamente: </strong>' . $formInput . '<input type="submit" value="enviar"/></td></tr>';
	$output .= '</table>';
	$output .= '</form>';
	$output .= '</td></tr>';

	/* check if exists a database connection */
	$env = wf_get_env();
	$output .= '<tr><td>Banco de Dados</td><td>' . (($env['dao']->Link_ID === 0) ? '<font color="red">não conectado</font>' : '<font color="green">conectado</font>') . '</td></tr>';

	/* end the table */
	$output .= '</table><br/>';

	/* send the output */
	echo $output;
}
?>
