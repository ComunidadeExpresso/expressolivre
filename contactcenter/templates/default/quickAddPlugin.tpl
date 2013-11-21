<!--

	eGroupWare - Contact Center - Quick Add Plugin Interface Template
	Copyright (C) 2004 - Raphael Derosso Pereira
	(raphaelpereira@users.sourceforge.net)

	This file is licensed under the terms of th GNU GPL
	version 2 or above
	
-->

{cc_api}

<div id="ccQuickAddContent" style="position: absolute; visibility: hidden">
	<input id="ccQAnFields" type="hidden" value="{ccQAnFields}" />
	<input id="ccQATitle" type="hidden" value="{ccQATitle}" />
	<input id="ccQAWinHeight" type="hidden" value="{ccQAWinHeight}" />
	
	<div style="border: 0px solid #999;">
		
		{ccQAFields}

	</div>
	<div id="ccQAFuncitons" style="border: 0px solid black; width: 220px; height: 20px">
		<input title="{ccQASave}" type="button" onclick="ccQuickAdd.send();" value="{ccQASave}" style="position: absolute; top: {ccQAFunctionsTop}; left: 55px; width: 60px" />
		<input title="{ccQAClear}" type="button" onclick="ccQuickAdd.clear();" value="{ccQAClear}" style="position: absolute; top: {ccQAFunctionsTop}; left: 120px; width: 60px" />
		<input title="{ccQACancel}" type="button" onclick="ccQuickAdd.clear(); ccQuickAdd.window.close();" value="{ccQACancel}" style="position: absolute; top: {ccQAFunctionsTop}; left: 185px; width: 60px" />
	</div>
</div>

<script type="text/javascript" src="{ccQAPluginFile}"></script>
