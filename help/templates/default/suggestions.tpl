<html>
<head>
<title>{title_suggestions}</title>
		<script language="Javascript">
		function send()	{
			if(document.doit.body.value ==""){
				alert('Não existe nenhum texto digitado.');
				return false;
			}
			document.doit.submit();
		}
		</script>		
<link rel='stylesheet' type='text/css' href='./templates/{template_set}/css/index.css'/>
</head>
<body style="margin:0px;padding:0px;">
<table width='100%'>
<tr><td align='left' background="./templates/{template_set}/images/fundo_topo.jpg">
<img src="./templates/{template_set}/images/topo.jpg" style="overflow:hidden;z-index:-1;">
<span class='titulo'>{lang_suggestions}</span>
</td></tr></table>
<div align='center'>
<p align="center">
<font face="Arial" size="2"><b>{txt_desc}</font></p>
<form name="doit" action="enviasugestao.php" method="POST">
<textarea name="body" cols="40" rows="15" wrap></textarea>
<br>
<br>
<input type="button" name="bt_send" value="{txt_send}" onClick="javascript:send()">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" name="bt_cancel" value="{txt_cancel}" onClick="javascript:window.close()">
</form>
</div>
</body>
</html>