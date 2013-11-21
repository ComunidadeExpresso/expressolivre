{$header}
<input type="hidden" value="{$txt_loading}" id="txt_loading">
<input type="hidden" value="{$processID}" id="processID">
<input type="hidden" name="jobID" id="jobID" value=""/>
{$css}
{$javaScripts}
<ul class="horizontalMenu">
	<li style="height: 2.5em;"><center>Processo<br/><strong>{$processNameVersion}</strong>&nbsp;&nbsp;&nbsp;</center></li>
	<li><a href="index.php?menuaction=workflow.ui_adminprocesses.form&p_id={$processID}"><img src="workflow/templateFile.php?file=images/change.gif"/>&nbsp;&nbsp;Editar</a></li>
	<li><a href="index.php?menuaction=workflow.ui_adminactivities.form&p_id={$processID}"><img src="workflow/templateFile.php?file=images/Activity.gif"/>&nbsp;&nbsp;Atividades</a></li>
	<li><a href="index.php?menuaction=workflow.ui_adminsource.form&p_id={$processID}"><img src="workflow/templateFile.php?file=images/code.png"/>&nbsp;&nbsp;Código</a></li>
	<li><a href="index.php?menuaction=workflow.ui_adminroles.form&p_id={$processID}"><img src="workflow/templateFile.php?file=images/roles.png"/>&nbsp;&nbsp;Perfis</a></li>
	<li><a href="index.php?menuaction=workflow.ui_adminactivities.show_graph&p_id={$processID}"><img src="workflow/templateFile.php?file=images/Process.gif"/>&nbsp;&nbsp;Gráfico</a></li>
	<li><a href="index.php?menuaction=workflow.WorkflowUtils.export&p_id={$processID}"><img src="workflow/templateFile.php?file=images/save.png"/>&nbsp;&nbsp;Exportar</a></li>
</ul>

<br/><br/><br/>
<div id="mainBody" style="width:99.5%; clear: both;">
<div id="jobList"></div>
<div id="jobResult" style="display: none;"></div>
<div id="jobForm" style="display: none;">
	<table>
		<tr>
			<td>
				<label for="name">Nome</label>
			</td>
			<td>
				<input type="text" id="name" name="name" size="49" maxlength="100"/>
			</td>
		</tr>
		<tr>
			<td>
				<label for="_description">Descrição</label>
			</td>
			<td>
				<textarea name="_description" id="_description" cols="40" rows="5"></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<label><input type="checkbox" id="active" />Ativo</label>
			</td>
		</tr>
		<tr>
			<td>
				<label for="startDate">Data de Início</label>
			</td>
			<td>
				{wf_calendar name="startDate" default=true}
			</td>
		</tr>
		<tr>
			<td>
				<label for="executionTime">Hora de Execução</label>
			</td>
			<td>
				{html_select_time use_24_hours=true display_seconds=false prefix="executionTime_" hour_extra='id="executionTime_Hour"' minute_extra='id="executionTime_Minute"'}
			</td>
		</tr>
		<tr>
			<td>
				<label>Tipo de Data</label>
			</td>
			<td>
				<table>
					<tr>
						<td>
							<label><input type="radio" name="dateType" id="dateType_0" checked="checked" value="0"/>Data Absoluta</label>
						</td>
						<td>
							<label><input type="radio" name="dateType" id="dateType_1" value="1"/>Dias da Semana</label>
						</td>
						<td>
							<label><input type="radio" name="dateType" id="dateType_2" value="2"/>Data Relativa do Mês</label>
						</td>
					</tr>
					<tr>
						<td style="color: gray;"><i>Ideal para Jobs que possuem um intervalo de execução bem definido. e.g.: executar o Job a partir do dia 07/05/2008 e repetir a cada 3 dias.</i></td>
						<td style="color: gray;"><i>Para Jobs que têm sua execução baseada em dias da semana. e.g.: a partir do dia 14/04/2008, executar o Job às segundas, quartas e sextas com repetição a cada duas semanas.</i></td>
						<td style="color: gray;"><i>Para Jobs que devem ser executados em relação ao número de dias para o fim do mês. e.g.: executar o Job a cada 6 meses, no último dia do mês.</i></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="date_1" style="display: none;">
			<td colspan="2">
				Executar o job nos seguintes dias:<br/>
				<table width="100%">
					<tr>
						<td><label><input type="checkbox" id="weekDateDay_0" value="1"/> Domingo</label></td>
						<td><label><input type="checkbox" id="weekDateDay_1" value="2"/> Segunda-feira</label></td>
						<td><label><input type="checkbox" id="weekDateDay_2" value="4"/> Terça-feira</label></td>
						<td><label><input type="checkbox" id="weekDateDay_3" value="8"/> Quarta-feira</label></td>
						<td><label><input type="checkbox" id="weekDateDay_4" value="16"/> Quinta-feira</label></td>
						<td><label><input type="checkbox" id="weekDateDay_5" value="32"/> Sexta-feira</label></td>
						<td><label><input type="checkbox" id="weekDateDay_6" value="64"/> Sábado</label></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="date_2" style="display: none;">
			<td colspan="2">
				Executar quando faltar <input type="text" size="1" name="relativeDateMonthOffset" id="relativeDateMonthOffset" value="1"/> dias para o próximo mês.
				<font color="gray">(e.g.: utilize 1 para o última dia do mês; 2 para o penúltimo dia do mês e assim por diante)</font><br/>
			</td>
		</tr>
		<tr>
			<td colspan="2"><label><input type="checkbox" id="repeatJob" /><strong>Repetição do Job</strong></label></td>
		</tr>
		<tr id="repeatDate_1"><td colspan="2">Repetir o job a cada <input type="text" size="1" name="weekDateIntervalValue" id="weekDateIntervalValue" value="1"/> semanas.</td></tr>
		<tr id="repeatDate_2"><td colspan="2">Repetir o job a cada <input type="text" size="1" name="relativeDateIntervalValue" id="relativeDateIntervalValue" value="1"/> meses.</td></tr>
		<tr id="repeatDate_0" style="display: none;">
			<td colspan="2">
				Repetir o job a cada <input type="text" size="3" name="absoluteDateIntervalValue" id="absoluteDateIntervalValue" value="1"/>
				<select name="absoluteDateIntervalUnity" id="absoluteDateIntervalUnity">
					<option value="5">minutos</option>
					<option value="4">horas</option>
					<option value="3" selected>dias</option>
					<option value="1">meses</option>
					<option value="0">anos</option>
				</select>
			</td>
		</tr>
		<tr id="actions">
			<td colspan="2">
				<button onclick="Effect.BlindUp('jobForm'); Effect.BlindDown('jobList'); return false;">Voltar</button>
				<button onclick="saveJob(); return false;">Salvar</button>
			</td>
		</tr>
		<tr id="saving" style="display: none;">
			<td colspan="2">
				<img src="workflow/templateFile.php?file=images/loading.gif"/> Salvando o Job ...
			</td>
		</tr>
	</table>
</div>
<div id="logList" style="display: none;"></div>
</div>
{literal}
<script type="text/javascript" language="javascript">
window.onload = function(){ addEventWatchers(); loadJobList(); };
</script>
{/literal}
{$footer}
