<?php

class AtividadeController extends Controller
{
	function __default()
	{
		if ($this->model->defaultAction())
		{
			/* acao OK */
		}
		else
		{
			/* acao erro */
		};
	}
}

?>
