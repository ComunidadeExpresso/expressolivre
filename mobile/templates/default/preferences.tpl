<form method="POST" action="{form_action}">
<div id="cc_pref_cards" style="width: 100%; border: 0px solid black">
	<table align="center" style="width: 400px">
		<tr class="th">
			<td style="text-align: center; font-weight: bold">{lang_Option}</td>
			<td style="text-align: center; font-weight: bold">{lang_Value}</td>
		</tr>
		<tr class="row_off">
			<td width="70%">{lang_download_attachs}</td>
			<td>
				<select name="download_attach" style="width: 100px">
				<option {download_attach_option_No_selected} value="0">{lang_No}</option>
				<option {download_attach_option_Yes_selected} value="1">{lang_Yes}</option>
				</select>
			</td>
		</tr>
		<tr class="row_on">
			<td width="70%">{lang_What_is_the_maximum_number_of_messages_per_page?}</td>
			<td>
				<select name="max_message_per_page" style="width: 100px">
				<option {max_message_per_page_5_selected} value="5">5</option>
				<option {max_message_per_page_10_selected} value="10">10</option>
				<option {max_message_per_page_15_selected} value="15">15</option>
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
</div>
</form>
