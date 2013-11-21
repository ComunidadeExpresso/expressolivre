<!-- BEGIN body -->
<div style="display:none" id="{limitByUserModalId}">{limitByUserModal}</div>
<div style="display:none" id="{limitByGroupModalId}">{limitByGroupModal}</div>
<div style="display:none" id="{blockEmailForInstitutionalAccountId}">{blockEmailForInstitutionalAccountModal}</div>
<input type="hidden" id="txt_loading" value="{lang_Loading}" />
<input type="hidden" id="txt_searching" value="{lang_Searching}" />
<input type="hidden" id="txt_users" value="{lang_Users}" />
<input type="hidden" id="txt_groups" value="{lang_Groups}" />

<center>
<br />
<table width="90%" border="0" cellspacing="0" cellpading="0">
    <tr class="th">
        <td colspan="2">&nbsp;<b>{lang_Blocking_sending_email_to_shared_accounts} </b></td>
    </tr>
</table>
<br />
<!-- The code for Email Tab -->

    <table width="60%" border="0" cellspacing="0" cellpading="0">
        <tr>
            <td>
                <input type="checkbox" id="inputCheckAllUserBlockCommunication" {checkedBlockCommunication} {acl_inputCheckAllUserBlockCommunication} />
            </td>
            <td>
                {lang_Enable_blocking_sending_email_to_shared_accounts_(departments)}
            </td>
        </tr>
    </table>
    <br />
    <table width="60%" border="0" cellspacing="0" cellpading="0">
        <tr>
            <td><input type="button" value="{lang_Add_exception_for_the_blocking}" onclick='{onclickBlockEmailForInstitutionalAccountModal}' {acl_add_exception_for_the_blocking} /></td>
        </tr>
    </table>
    <br />
    <table width="60%" border="0" cellspacing="0" cellpading="0">
        <tr>
            <td width="50%">
                {lang_Find_senders} : <input type="text" id="inputTextFindRecipient" onkeyup="javascript:finderRecipientInstitutionalAcounteExeption(this)"/>
            </td>

        </tr>
    </table>
    <table width="60%" border="0" cellspacing="0" cellpading="0">
        <tr>
             <td>  {lang_Senders} :</td>
             <td>  {lang_Recipients} :</td>
        </tr>
        <tr>
            <td> <select id="inputSelectRecipients" style="width:250px; height:100px" size="13" onchange="javascript:getOptionsSendersInstitutionalAcounteExeption();">{optionsBlockEmailForInstitutionalAcounteExeption}</select> </td>

            <td> <select id="inputSelectSenders" style="width:250px; height:100px" disabled="true" size="13"></select> </td>
        </tr>
    </table>
    <table width="60%" border="0" cellspacing="0" cellpading="0">
        <tr>
            <td><input type="button" value="{lang_Edit_exception}" onclick="javascript:editBlockInstitutionalAccountExeption()" {acl_edit_and_remove_blocking_sending_email}/> &ensp;<input type="button" value="{lang_Remove_exception}" onclick="javascript:removeBlockInstitutionalAccountExeption()" {acl_edit_and_remove_blocking_sending_email}/></td>
            
        </tr>
    </table>
    <br />
<table width="90%" border="0" cellspacing="0" cellpading="0">
    <tr class="th">
        <td colspan="2">&nbsp;<b>{lang_Amount_limit_of_recipients}</b></td>
    </tr>
</table>
    <br />
    <table width="60%" border="0" cellspacing="0" cellpading="0">
        <tr>
            <td>
                {lang_General_amount_limit_of_recipients} :
                <input type="text" id="inputTextMaximumRecipientGenerally"  size="5" value="{valueMaximumRecipient}" {acl_inputTextMaximumRecipientGenerally} />
                       &nbsp;<span style="color: red">*0 = {lang_disable_and_sets_unlimited_recipients}.</span>
            </td>
        </tr>
    </table>
    <br />
   <table width="60%" border="0" cellspacing="0" cellpading="0">
        <tr>
             <td><input type="button" value="{lang_Limit_by_user}" onClick='{onclickLimitByUserModal}' {acl_Limit_by_user} /></td>
        </tr>
    </table>

    <div id ="tableLimitSendersByUser" style="width: 60%">
        <table width="80%" border="0" cellspacing="0" cellpading="0" align="left">
                           <tr class="th">
                            <td width="40%">
                                {lang_Recipients}
                            </td>
                             <td width="40%">
                               {lang_Maximum_number_of_recipients}
                            </td>
                             <td width="10%">
                               {lang_edit}
                            </td >
                            <td width="10%">
                                {lang_remove}
                            </td>
                           </tr>
         {tableLimitRecipientsByUser}
        </table>
        &nbsp;
    </div>
  
    
    <table width="60%" border="0" cellspacing="0" cellpading="0" style="margin-top: 10px">
        <tr>
            <td><input type="button" value="{lang_Limit_by_group}" onclick='{onclickLimitByGroupModal}' {acl_Limit_by_group}/></td>
        </tr>
    </table>
    
     <div id="tableLimitSendersByGroup" style="width: 60%">
         <table width="80%" border="0" cellspacing="0" cellpading="0" align="left">
                           <tr class="th">
                            <td width="40%">
                                {lang_Recipients}
                            </td>
                             <td width="40%">
                                {lang_Maximum_number_of_recipients}
                            </td>
                             <td width="10%">
                                {lang_edit}
                            </td >
                            <td width="10%">
                                {lang_remove}
                            </td>
                           </tr>
         {tableLimitRecipientsByGroup}
         </table>
        &nbsp;
    </div>
    <br />
    <br />
    <br />

    <table width="auto" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
            <td>
                <input type="button" value="{lang_save}" onclick="javascript:saveGlobalSettings()"/>
            </td>
            <td>
                <input type="button" value="{lang_back}" onclick="document.location.href='{back_url}'"/>
            </td>
        </tr>
    </table>
</center>
<br />
<!-- END body -->
