<!--
	ContactCenter Preferences TPL File
	Copyright (C) 2004 - Raphael Derosso Pereira 
	(raphaelpereira@users.sourceforge.net)
	
	This file is licensed under the terms of th GNU GPL
	version 2 or above
-->

<form method="POST" action="{form_action}">
<div id="cc_pref_cards" style="width: 100%; border: 0px solid black">
	<p align="center" style="font-weight: bold; font-size: medium; border: 0px solid black">{lang_Cards_Visualization_Preferences}</p>
	<table align="center" style="width: 400px">
		<tr class="th">
			<td style="text-align: center; font-weight: bold">{lang_Option}</td>
			<td style="text-align: center; font-weight: bold">{lang_Value}</td>
		</tr>
		<tr class="row_off">
			<td>{lang_Default_Person_Email_Type}</td>
			<td>
				<select name="personCardEmail" style="width: 200px">
					{personCardEmail}
				</select>
			</td>
		</tr>
		<tr class="row_on">
			<td>{lang_Default_Person_Telephone_Type}</td>
			<td>
				<select name="personCardPhone" style="width: 200px">
					{personCardPhone}
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align: right;">
				<input type="submit" name="save" value="{lang_Save}">
				<input type="button" name="cancel" value="{lang_Cancel}" onclick="window.back()">
			</td>
		</tr>
	</table>
	<br>
	<p align="center" style="font-weight: bold; font-size: medium; border: 0px solid black">{lang_Connector_Setup}</p>
	<table align="center" style="width: 400px">
		<tr class="th">
			<td style="text-align: center; font-weight: bold; width: 200px">{lang_Option}</td>
			<td style="text-align: center; font-weight: bold; width: 200px">{lang_Value}</td>
		</tr>
		<tr class="row_off">
			<td>{lang_Display_Connector_Client-Server_Status_Information?}</td>
			<td align="center">
				<input type="checkbox" name="displayConnector" {displayConnector} />
			</td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align: right;">
				<input type="submit" name="save" value="{lang_Save}">
				<input type="button" name="cancel" value="{lang_Cancel}" onclick="window.back()">
			</td>
		</tr>
	</table>
	<br>
	<p align="center" style="font-weight: bold; font-size: medium; border: 0px solid black">Preferências de Visualização</p>
	<table align="center" style="width: 400px">
		<tr class="th">
			<td style="text-align: center; font-weight: bold; width: 200px">Campo</td>
			<td style="text-align: center; font-weight: bold; width: 200px">{lang_Value}</td>
		</tr>
		<tr class="row_off">
			<td>Matrícula</td>
			<td align="center">
				<input type="checkbox" name="empNum" {empNum} />
			</td>
		</tr>
		<tr class="row_off">
			<td>Celular</td>
			<td align="center">
				<input type="checkbox" name="cell" {cell} />
			</td>
		</tr>
		<tr class="row_off">
			<td>Setor</td>
			<td align="center">
				<input type="checkbox" name="department" {department} />
			</td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align: right;">
				<input type="submit" name="save" value="{lang_Save}">
				<input type="button" name="cancel" value="{lang_Cancel}" onclick="window.back()">
			</td>
		</tr>
	</table>
</div>
</form>
