<html>
<head>
{$css}
{$javaScripts}

<script language="javascript1.2">
{literal}
function initEditor()
{
{/literal}
	editor.setContents("{$fileData}");
{literal}
	editor.keyBinding["c83"] = phpeditor_salvar;
	editor.keyBinding["c70"] = function() { document.getElementById('txtFind').focus(); };
{/literal}
	{if $type == 'php'}
	editor.keyBinding["c89"] = phpeditor_checksyntax;
	{/if}
{literal}
}

function FindInCode()
{
	editor.do_FindNext(document.getElementById('txtFind').value);
}
{/literal}
</script>

<title>{$processNameVersion} - {$fileName}</title>
</head>
<body onload='initEditor();'>
<input type="hidden" value="{$txt_loading}" id="txt_loading">

<script language="javascript1.2">
{literal}
	function mouseover(obj) { obj.style.backgroundColor='#f5f5f5'; 	}
	function mouseout(obj) 	{ obj.style.backgroundColor='#d8d8d8'; 	}
{/literal}
</script>

<table border=0 style="width:2000px;border-top:1px solid;border-left:1px solid;" bgcolor="#d8d8d8">
<tr>
	<td align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<a href="javascript:void(0);" onclick="phpeditor_salvar()"><img border=0 src="workflow/templateFile.php?file=images/helene_save.png" title="Salvar (Ctrl+S)"></a>	
	</td>
	{if $type == 'php'}
	<td align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<a href="javascript:void(0);" onclick="phpeditor_checksyntax()"><img border=0 src="workflow/templateFile.php?file=images/helene_checksyntax.png" title="Check PHP Syntax (Ctrl+Y)"></a>	
	</td>
	{/if}
	<td valign=center align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<input id="txtFind" type=text name=frmFind>
	</td>
	<td align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<a href="javascript:void(0);" onclick="FindInCode()"><img border=0 src="workflow/templateFile.php?file=images/helene_next.png"></a>	
	</td>
	<td align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<a href="javascript:void(0);" onclick="javascript:editor.blur();editor.do_Replace();"><img border=0 src="workflow/templateFile.php?file=images/helene_replace.png" title="Substituir")></a>	
	</td>
	<td align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<a href="javascript:void(0);" onclick="javascript:editor.do_Indent();"><img border=0 src="workflow/templateFile.php?file=images/helene_addindent.png" title="Adiciona Identação"></a>	
	</td>
	<td align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<a href="javascript:void(0);" onclick="javascript:editor.do_Unindent();"><img border=0 src="workflow/templateFile.php?file=images/helene_delindent.png" title="Remove Identação"></a>	
	</td>
	<td align=center onmouseover="mouseover(this);" onmouseout="mouseout(this);" width="25px">
		<a href="javascript:void(0);"><img border=0 src="workflow/templateFile.php?file=images/helene_help.png"></a>	
	</td>
	<td>
	</td>
</tr>
</table>

<iframe id="phpeditor" name="editor" scrolling=no src="workflow/templateFile.php?file={$HTMLFile}" style="width: 2000px; height:78%; border-width:1px;"></iframe>

<table border=0 width=2000px style="border-top:0px solid;border-left:1px solid;border-bottom:1px solid;border-right:1px solid;border-color:black;font-size:11px;">
	<tr>
		<td>
			<div id="info">&nbsp;</div>
		</td>
	</tr>
</table>
<table border=0 width=2000px style="border-top:0px solid;border-left:1px solid;border-bottom:1px solid;border-right:1px solid;border-color:black;">
<tr>
	<td width=5px valign=top>
		<table style="font-size:8px;height:50px;" bgcolor=#d5d5d5 cellpadding=0 cellspacing=0>
		<tr>
			<td valign=top style="height:10px;" bgcolor="#f5f5f5">
				<a href="javascript:messages.scrollwin(-20);"><img border=0 src="workflow/templateFile.php?file=images/up_arrow.png"></a>
			</td>
		</tr>
		<tr>
			<td style="height:28px;">
			</td>
		</tr>
		<tr>
			<td valign=bottom style="height:10px;" bgcolor="#f5f5f5">
				<a href="javascript:messages.scrollwin(20);"><img border=0 src="workflow/templateFile.php?file=images/down_arrow.png"></a>
			</td>
		</tr>
		</table>
	</td>
	<td>
		<iframe name="messages" scrolling=yes src="workflow/templateFile.php?file=editor_messages.html" style="width: 100%; height: 60px; border-width:0px;"></iframe>
	</td>
</tr>
</table>

<form name=frmSend method=POST enctype="multipart/form-data">
	<textarea name='code' style='display:none'></textarea>
	<input type='hidden' name='proc_name' value="{$processName}">
	<input type='hidden' name='file_name' value="{$fileName}">
	<input type='hidden' name='tipo_codigo' value="{$tipoCodigo}">
	<input type='hidden' name='proc_id' value="{$processID}">
	<input type='hidden' name='activity_id' value="{$activityId}">
</form>

</body>
</html>
