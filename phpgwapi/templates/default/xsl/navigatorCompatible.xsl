<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="path" />

	<xsl:template match="navigator">


		<div style="font:9pt; margin:2px 5px 2px 5px; text-align:justify;">
			<p>
			Seu navegador é <span style="color:red;font-weight: bold;">incompatível</span> 
			com o serviço de mensagem instantânea do expresso.
			Favor instalar ou atualizar a versão do seu navegador,
			solicite a sua área de suporte local a instalação.
			</p>
		</div>
		
		<div style="height: 285px; font:9pt; margin:0px 5px 2px 5px; text-align: justify; overflow-y:scroll;">
			<p style="font-weight:bold;">:: Navegadores Compatíveis ::</p>
			
			<fieldset style="margin-bottom:10px;">
				<legend><img src="{$path}images/compatible_epiphany.png" align="middle"/><span style="margin:5px;">Epiphany</span></legend>
				<span> Versão : 2.22 ou superior </span><br/>
				<span> Avaliação : <span style="color:red;font-weight:bold;">Ideal para o uso</span></span>
			</fieldset>
			
			<fieldset style="margin-bottom:10px;">
				<legend><img src="{$path}images/compatible_iceweasel.png" align="middle"/><span style="margin:5px;">Iceweasel</span></legend>
				<span> Versão : 3.06 ou superior </span><br/>
				<span> Avaliação : <span style="color:red;font-weight:bold;">Ideal para o uso</span></span>
			</fieldset>
			
			<fieldset style="margin-bottom:10px;">
				<legend><img src="{$path}images/compatible_ie.gif" align="middle" /><span style="margin:5px;">Internet Explorer ( IE )</span></legend>
				<span> Versão : 8.0 </span><br/>
				<span> Avaliação : Recomendado</span>
			</fieldset>
		
			<fieldset>
				<legend><img src="{$path}images/compatible_firefox.gif" align="middle"/><span style="margin:5px;">Mozilla Firefox</span></legend>
				<span> Versão : 3.0 ou superior </span><br/>
				<span> Avaliação : <span style="color:red;font-weight:bold;">Ideal para o uso</span></span>
			</fieldset>
		</div>		
		
	</xsl:template>

</xsl:stylesheet>