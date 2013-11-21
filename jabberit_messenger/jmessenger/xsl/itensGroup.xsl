<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="divDisplay" />
	<xsl:param name="id" />
	<xsl:param name="jid"/>
	<xsl:param name="nameContact" />
	<xsl:param name="path_jabberit" />
	<xsl:param name="presence" />
	<xsl:param name="spanDisplay"/>
	<xsl:param name="status"/>
	<xsl:param name="statusColor" />
	<xsl:param name="subscription" />
	<xsl:param name="resource" />
	
	<xsl:template match="itens_group">

			<div id="{$id}" subscription="{$subscription}" resource="{$resource}" onmousedown="loadscript.actionButton(event,'{$jid}' );" style="background: url('{$path_jabberit}templates/default/images/{$presence}.gif') no-repeat center left; padding-left: 20px; margin:2px 0px 0px 10px; cursor:pointer; font-weight:normal; display:{$divDisplay};">
				<xsl:value-of select="$nameContact"/>
			</div>
			<span id="span_show_{$id}" style="margin:2px 0px 0px 10px; font-size: 10px; font-style:italic; display:{$spanDisplay}; color:{$statusColor};"><xsl:value-of select="$status"/></span>
			
	</xsl:template>
	
</xsl:stylesheet>