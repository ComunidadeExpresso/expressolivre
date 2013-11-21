{* define the variables depending on the event (completed or cancelled) *}
{if $activityEvent == 'completed'}
	{assign var="eventImage" value="apply.png"}
	{assign var="eventTitle" value="Atividade Completada"}
{else}
	{assign var="eventImage" value="button_cancel.png"}
	{assign var="eventTitle" value="Atividade Cancelada"}
{/if}

{$header}

{* activity errors are displayed here *}
{if count($activityErrors) > 0}
	<div id="wf_run_activity_message">
	{foreach from=$activityErrors item=activityError}
		<p>{$activityError}</p>
	{/foreach}
	</div>
{/if}

<br/>
<div id="activityCompleteMessage" align="center">{$activityCompleteMessage}</div>
<br/><br/>

<table style="border: 1px solid black;margin:0 auto;width:300px;" align="center">
<tr class="th">
	<td colspan="2" style="font-size: 18px; font-weight:bold; text-align:center; font-family: Verdana, Arial, Helvetica, sans-serif;">
		<img align="middle" border="0" src="workflow/templateFile.php?file=images/{$eventImage}">
        &nbsp;&nbsp;&nbsp;
		{$eventTitle}
	</td>
</tr>
<tr class="row_on">
	<td class="th">Processo:</td>
	<td>{$processName} {$processVersion}</td>
</tr>
<tr class="row_off">
	<td class="th">Atividade:</td>
	<td>{$activityName}</td>
</tr>
<tr class="row_on">
	<td class="th">Ir Para:</td>
	<td>{html_options name="redirectionMenu" id="redirectionMenu" options=$activityList onchange="redirectMenu();"}</td>
</tr>
</table>
<br />
<br/>
<table style="border: 0px;margin:0 auto; width:600px" align="center">
	<tr>
		<td align="center">
			<a href="workflow/index.php?start_tab=0">
				<img border=0 src="workflow/templateFile.php?file=images/inbox.png" alt="Atividades Pendentes" title="Atividades Pendentes">
				<br/>
				Tarefas Pendentes
			</a>
		</td>
		<td align="center">
			<a href="workflow/index.php?start_tab=1">
				<img border=0 src="workflow/templateFile.php?file=images/process.png" alt="Lista de processos" title="Lista de processos">
				<br/>
				Processos
			</a>
		</td>
		<td align="center">
			<a href="workflow/index.php?start_tab=2">
				<img border=0 src="workflow/templateFile.php?file=images/goto.png" alt="Acompanhamento das atividades" title="Acompanhamento das atividades">
				<br/>
				Acompanhamento
			</a>
		</td>
		<td align="center">
			<a href="workflow/index.php?start_tab=3">
				<img border=0 src="workflow/templateFile.php?file=images/network.png" alt="Aplicações Externas" title="Aplicações Externas">
				<br/>
				Aplicações Externas
			</a>
		</td>
		<td align="center">
			<a href="workflow/index.php?start_tab=4">
				<img border=0 src="workflow/templateFile.php?file=images/organograma.png" alt="Organograma" title="Organograma">
				<br/>
				Organograma
			</a>
		</td>
	</tr>
</table>
<br/>
<br/>

<script language="javascript1.2">
{literal}
function redirectMenu()
{
	var element = document.getElementById("redirectionMenu");
	var activityID = element.options[element.options.selectedIndex].value;
	location.href = "{/literal}{$activityBaseURL}{literal}/index.php?menuaction=workflow.run_activity.go&activity_id=" + activityID;
}
{/literal}
</script>
