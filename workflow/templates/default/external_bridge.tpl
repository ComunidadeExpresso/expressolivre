<html>
<head>
<script language="JavaScript">
{literal}
function enviarPost()
{
	document.getElementById("formBridge").submit();
}
{/literal}
</script>
</head>
<body>
<form name="formBridge" id="formBridge" method="POST" action="{$siteAddress}">
{$encodedForm}
</form>
<script language="JavaScript">
enviarPost();
document.write('<p>Se a p�gina n�o for atualizada em alguns instantes, <a href="#" onClick="enviarPost();">clique aqui</a></p>');
</script>
</body>
<noscript>
	<p>Seu navegador n�o suporta JavaScript e, por este motivo, o acesso n�o poder� ser feito.</p>
	<p>Tente acessar diretamente o link a seguir e efetue a autentica��o: <a href="{$siteAddress}">{$siteAddress}</a></p>
</noscript>
</html>
