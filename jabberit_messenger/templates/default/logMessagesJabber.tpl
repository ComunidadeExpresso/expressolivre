<!-- BEGIN log_message -->

<form method="POST" action="{action_url}">
	
	<div style="margin:10px;">
		<label>{label_user}:</label>
		<input type="text" size="40" maxlength="50" name="txtUser" value="{value_txtUser}"/>
		<input type="submit" value="{label_view}" />
		<br/>
		<br/>
	</div>
	
	<span style="margin-left:93%;">{label_page}{value_page}</span>
	
	<div style="margin:10px;width:100%;"> 
		<table align="center" border="0" style="width:100%;">
			<tr>
				<td align="left" style="width:40%;" class="row_on"> {label_user} </td>
				<td align="center" style="width:10%" class="row_on"> {label_total}</td>
				<td align="center" style="width:20%;" class="row_on"> {label_first_message} </td>
				<td align="center" style="width:20%" class="row_on"> {label_last_message} </td>
				<td align="center" style="width:10%;" class="row_on"> {label_view}</td>
			</tr>
				{value_messages}			
		</table>
	</div>
	
	<div>
		<div style="margin:10px; position:relative; float:left;">
			<input type="button" onClick="document.location.href='{action_url_back}'" value="{label_back}"/>
		</div>
		<div style="margin:10px; position:relative; float:right;">
			{bt_previous}
			{bt_next}
		</div>
		<input type="hidden" name="button_previous" value="{value_previous}" />
		<input type="hidden" name="button_next" value="{value_next}" />
	</div>
</form>

<!-- END log_message -->

<!-- BEGIN log_message_date -->

	<span style="margin-left:93%;">{label_page}{value_page}</span>
	
	<div style="margin:10px;width:100%;"> 
		<table align="center" border="0" style="width:100%;">
			<tr>
				<td align="left" style="width:30%;" class="row_on"> {label_user_1} </td>
				<td align="left" style="width:30%" class="row_on"> {label_user_2} </td>
				<td align="center" style="width:10%;" class="row_on"> {label_total} </td>
				<td align="center" style="width:10%" class="row_on"> {label_first_message} </td>
				<td align="center" style="width:10%" class="row_on"> {label_last_message} </td>
				<td align="center" style="width:10%;" class="row_on"> {label_view} </td>
			</tr>
				{value_messages}			
		</table>
	</div>
	
	<div>
		<form method="POST" action="{action_url_back}">
			<div style="margin:10px; position:relative; float:left;">
				<input type="submit" value="{label_back}"/>
				<input type="hidden" name="txtUser" value="{value_txtUser}" />
				<input type="hidden" name="pg1_next" value="{value_pg1_next}">
				<input type="hidden" name="pg1_previous" value="{value_pg1_previous}">
			</div>
		</form>
		
		<form method="POST" action="{action_url}">
			<div style="margin:10px; position:relative; float:right;">
				{bt_previous}
				{bt_next}
			</div>
			<input type="hidden" name="button_previous" value="{value_previous}" />
			<input type="hidden" name="button_next" value="{value_next}" />
			<input type="hidden" name="user" value="{value_user}" />
			<input type="hidden" name="first_message" value="{value_first_message}" />
			<input type="hidden" name="last_message" value="{value_last_message}" />
			<input type="hidden" name="pg1_next" value="{value_pg1_next}">
			<input type="hidden" name="pg1_previous" value="{value_pg1_previous}">
		</form>
		
	</div>

<!-- END log_message_date -->

<!-- BEGIN log_message_complete -->

	<span style="margin-left:93%;">{label_page}{value_page}</span>
	
	<div style="margin:10px;width:100%;"> 
		<table align="center" border="0" style="width:100%;">
			<tr>
				<td align="left" style="width:25%;" class="row_on"> {label_user_1} </td>
				<td align="left" style="width:25%" class="row_on"> {label_user_2} </td>
				<td align="left" style="width:40%;" class="row_on"> {label_body} </td>
				<td align="center" style="width:10%" class="row_on"> {label_date} </td>
			</tr>
				{value_messages}			
		</table>
	</div>
	
	<div>
		<form method="POST" action="{action_url_back}">
			<div style="margin:10px; position:relative; float:left;">
				<input type="submit" value="{label_back}"/>
				<input type="hidden" name="user" value="{value_user}" />
				<input type="hidden" name="first_message" value="{value_first_message}" />
				<input type="hidden" name="last_message" value="{value_last_message}" />
				<input type="hidden" name="pg1_next" value="{value_pg1_next}">
				<input type="hidden" name="pg1_previous" value="{value_pg1_previous}">
				<input type="hidden" name="pg2_next" value="{value_pg2_next}">
				<input type="hidden" name="pg2_previous" value="{value_pg2_previous}">
				<input type="hidden" name="txtUser" value="{value_txtUser}" />
			</div>
		</form>
	
		<form method="POST" action="{action_url}">
			<div style="margin:10px; position:relative; float:right;">
				{bt_previous}
				{bt_next}
			</div>
			<input type="hidden" name="button_previous" value="{value_previous}" />
			<input type="hidden" name="button_next" value="{value_next}" />
			<input type="hidden" name="dtfirst" value="{value_dtfirst}" />
			<input type="hidden" name="dtlast" value="{value_dtlast}" />
			<input type="hidden" name="first_message" value="{value_first_message}" />
			<input type="hidden" name="last_message" value="{value_last_message}" />
			<input type="hidden" name="txtUser" value="{value_txtUser}" />			
			<input type="hidden" name="user" value="{value_user}" />
			<input type="hidden" name="user1" value="{value_user1}" />
			<input type="hidden" name="user2" value="{value_user2}" />
			<input type="hidden" name="pg1_next" value="{value_pg1_next}">
			<input type="hidden" name="pg1_previous" value="{value_pg1_previous}">
			<input type="hidden" name="pg2_next" value="{value_pg2_next}">
			<input type="hidden" name="pg2_previous" value="{value_pg2_previous}">
		</form>
	</div>

<!-- END log_message_complete -->