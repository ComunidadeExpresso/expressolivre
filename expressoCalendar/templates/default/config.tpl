<!-- BEGIN header -->
<form method="POST" action="{action_url}">
    <table border="0" align="center">
        <!-- END header -->
        <!-- BEGIN body -->
        <tr class="th">
            <td colspan="2" align="center"><b>{lang_expressoCalendar_Setup}</b></td>
        </tr>

        <tr><td></td></tr>

        <tr class="row_off">
            <td>{lang_Auto_import_calendars_to_receive_an_internal_event}:</td>
            <td>
                <select name="newsettings[expressoCalendar_autoImportCalendars]">
                    <option value="false" {selected_expressoCalendar_autoImportCalendars_false}>{lang_no}</option>
                    <option value="true"  {selected_expressoCalendar_autoImportCalendars_true}>{lang_yes}</option>
                </select>
            </td>
        </tr>
        <!-- END body -->
        <!-- BEGIN footer -->
        <tr class="th">
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" name="submit" value="{lang_submit}">
                <input type="submit" name="cancel" value="{lang_cancel}">
            </td>
        </tr>
    </table>
</form>
<!-- END footer -->
