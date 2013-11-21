<html>
<head>
</head>
<body>
{$header}
{$css}
{$javaScripts}

<div class="invisiblePrint">
	<h1>Configurar Impressão</h1>
	<label><input type="checkbox" checked id="employeesVisibility" /> Exibir funcionários</label>
	<br/>
	<label><input type="checkbox" id="photoVisibility" /> Exibir foto </label>	
	<br/>
	<label><input type="checkbox" checked id="groupByArea" /> Agrupar por área</label>
	<br/>
	<label><input type="checkbox" checked id="highlightSupervisor" /> Ressaltar titular</label>
	<br/>
	<label><input type="checkbox" checked id="orgchartPathVisibility" /> Exibir "caminho completo" da área</label>
	<br/><br/>
	<button id="printButton">Imprimir</button>
</div>


{if !empty($organizationName)}
<h1 class="organizationName">{$organizationName}</h1>
{/if}

<div id="areas_content" />

{$footer}
</body>
</html>
<script language="javascript">
var areas = {$areasJson};
</script>
