{$header}
{* activity errors are displayed here *}
{if count($activityErrors) > 0}
	<div id="wf_run_activity_message">
	{foreach from=$activityErrors item=activityError}
		<p>{$activityError}</p>
	{/foreach}
	</div>
{/if}

<link href="{$CSSLink}"  type="text/css" rel="StyleSheet" media="{$CSSMedia}" />
<div id="wf_run_activity_zone">
	<form method="post" enctype='multipart/form-data' name="workflow_form" id="workflow_form" action="{$actionURL}">
		<div id="wf_activity_playground">
			<div id="wf_activity_template">
				{$activityOutput}
			</div>
		</div>
	</form>
</div>
{$footer}
