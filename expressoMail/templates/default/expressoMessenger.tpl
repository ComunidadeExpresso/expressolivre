<!-- BEGIN bodyMessenger -->
<form method="POST" action="{action_url}">
<table align="center" width="60%" cellspacing="2" style="border: 1px solid #000000;">
	<tr class="th">
		<td colspan="2">
			<div style="margin:5 5 5 0px;text-align:left;">
				<label style="font-size:12px;font-weight:bold;"> {lang_Expresso_Messenger_settings} </label>
			</div>
		</td>
	</tr>

	<tr class="row_off">
		<td>{lang_Domain_Jabber}:</td>
		<td><input name="jabber_domain" value="{value_jabber_domain}" size="60"></td>
	</tr>   
	<tr class="row_on">
		<td>{lang_URL_for_direct_connection} (Ex.: http://server_jabber:5280/http-bind):</td>
		<td><input name="jabber_url_1" value="{value_jabber_url_1}"  size="60"></td>
	</tr>   
	<tr class="row_on">
		<td colspan="2">
			{lang_organizations} :
			&nbsp;
			<select name="organizations_ldap" onchange="javascript:expressoMessenger.getGroups(this);">
				{value_organizationsLdap}
			</select>
			<span id="admin_span_loading" style="color:red;visibility:hidden;">&nbsp;{lang_load}</span>
		</td>
	</tr>
	<tr class="row_off">
		<td colspan="2">
		<table align="center" cellspacing="0">
			<tr>
				<td class="row_off">	
					{lang_groups_ldap} :
					<br/>
					<select name="groups_ldap" size="10" style="width: 300px" multiple></select>
				</td>
				<td class="row_off">
					<input type="button" value="{lang_add}" onclick="javascript:expressoMessenger.add();" />
					<br/>
					<br/>
					<input type="button" value="{lang_remove}" onclick="javascript:expressoMessenger.remove();" />
				</td>
				<td class="row_off">
					{lang_enabled_groups} :
					<br/>
					<select name="groups_expresso_messenger[]" size="10" style="width: 300px" multiple>{value_groups_expresso_messenger}</select>
				</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		  <input type="submit" name="save" value="{lang_save}" onclick="javascript:expressoMessenger.selectAll();">
		  <input type="submit" name="cancel" value="{lang_cancel}">
		  <br>
		</td>
	</tr>
</table>
</form>
<script type="text/javascript" src="./prototype/plugins/jquery/jquery.min.js" language="javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function()
{	
	(function()
	{
		function addGroup()
		{
			$("select[name=groups_ldap] option:selected").each(function()
			{
				var $flag	= false;
				var $value	= $(this).val();
				var $groups = $value.split(";");

				if( $("select[name='groups_expresso_messenger[]']").children("option").length > 0 )
				{
					$("select[name='groups_expresso_messenger[]']").children("option").each(function()
					{
						if( $(this).val() == $value )
						 	_flag = true;
					});

					if( !$flag )
					{
						$("select[name='groups_expresso_messenger[]']").append( new Option( $groups[0], $value ) );
					}
				}
				else
				{
					$("select[name='groups_expresso_messenger[]']").append( new Option( $groups[0], $value ) );
				}
			});
		}

		function removeGroup()
		{
			$("select[name='groups_expresso_messenger[]'] option:selected").each(function()
			{
				$(this).remove();
			});
		}

		function selectAll()
		{
			$("select[name='groups_expresso_messenger[]']").children("option").each(function()
			{
				$(this).attr("selected","selected");
			});	
		}

		function getGroups()
		{
			$.ajax({
				"type"		: "POST",
				"url"		: "./expressoMail/inc/functionsMessenger.inc.php",
				"data"		: { "organization" : $("select[name=organizations_ldap]").val() },
				"dataType"	: "json",
				"success" 	: function(data)
				{
					// Clean Select
					$("select[name=groups_ldap]").empty();

					for( var i in data )
					{
						var $groups = data[i].split(';');
						
						$("select[name=groups_ldap]").append( new Option( $groups[0], data[i] ) );
					}
				}
			});
		}

		function expressoMessenger(){ }

		expressoMessenger.prototype.add			= addGroup;
		expressoMessenger.prototype.remove		= removeGroup;
		expressoMessenger.prototype.getGroups	= getGroups;
		expressoMessenger.prototype.selectAll	= selectAll;
		
		window.expressoMessenger = new expressoMessenger;
	})();
});
</script>
<!-- END bodyMessenger -->