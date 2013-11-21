<!-- BEGIN file_edit_header -->
<!-- END file_edit_header -->

<!-- BEGIN column -->
<!-- END column -->

<!-- BEGIN row -->
{refresh_script}
{preview_content}<br/>
<form name="edit_form" method="post" action="{form_action}">
	<input type="hidden" name="edit" value="1" />
	<input type="hidden" name="edit_file" value="{edit_file}" />
	{filemans_hidden}
	{fck_edit}
	<table>
	<tr>
{buttonSave} {buttonDone} {buttonCancel}
</tr>
</table>
</form>
<!-- END row -->

<!-- BEGIN file_edit_footer -->
<!-- END file_edit_footer -->

