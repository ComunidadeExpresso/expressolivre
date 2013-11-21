<style type="text/css">
	.letter_box,.letter_box_active {
		background-color: #D3DCE3;
		width: 25px;
		border: 1px solid #D3DCE3;
		text-align: center;
		cursor: pointer;
		cusror: hand;
	}
	.letter_box_active {
		font-weight: bold;
		background-color: #E8F0F0;
	}
	.letter_box_active,.letter_box:hover {
		border: 1px solid black;
		background-color: #E8F0F0;
	}
</style>

<div align="center">
<table border="0" width="80%">
	<tr>
		<td align="right" colspan="5">
			<form method="POST" action="{accounts_url}">
				<table width="100%"><tr>
					<td>{lang_group} {group}</td>
					<td align="right">
						{query_type}
						<input type="text" name="query">
						<input type="submit" name="search" value="{lang_search}">
					</td>
				</tr></table>
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="5">
			<table width="100%"><tr>
<!-- BEGIN letter_search -->
				<td class="{class}" onclick="location.href='{link}';">{letter}</td>
<!-- END letter_search -->
			</tr></table>
		</td>
	</tr>
	<tr>
		{left_next_matchs}
		<td align="center">{lang_showing}</td>
		{right_next_matchs}
	</tr>
</table>
</div>
 <div align="center">
  <table border="0" width="80%">
   <tr class="th">
    <td width="20%">{lang_loginid}</td>
    <td width="20%">{lang_lastname}</td>
    <td width="20%">{lang_firstname}</td>
    <td>{lang_email}</td>
    <td width="5%">{lang_edit}</td>
    <td width="5%">{lang_delete}</td>
    <td width="5%">{lang_view}</td>
   </tr>

 <!-- BEGIN row -->
   <tr class="{class}">
    <td>{account_lid}</td>
    <td>{account_lastname}</td>
    <td>{account_firstname}</td>
    <td>{account_email}</td>
    <td>{row_edit}</td>
    <td>{row_delete}</td>
    <td>{row_view}</td>
   </tr>
<!-- END row -->

  </table>
 </div>

  <div align="center">
   <table border="0" width="80%">
    <tr>
	 <td align="left">
	  <form method="POST" action="{new_action}">
	   {input_add}
	  </form>
	 </td>
    </tr>
   </table>
  </div>


<!-- BEGIN row_empty -->
   <tr>
    <td colspan="5" align="center">{message}</td>
   </tr>
<!-- END row_empty -->
