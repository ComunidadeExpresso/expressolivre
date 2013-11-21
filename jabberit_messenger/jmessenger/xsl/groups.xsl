<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="nameGroup" />
	<xsl:param name="path_jabberit" />
	
	<xsl:template match="group">

			<div style="margin-bottom: 3px;">
				<span  onclick="loadscript.groupsHidden(this);" style="background: url('{$path_jabberit}/templates/default/images/arrow_down.gif') no-repeat center left; font-weight:bold; padding-left: 16px; cursor: pointer;">
					<xsl:value-of select="$nameGroup"/>
				</span>
			</div>
			
	</xsl:template>
	
</xsl:stylesheet>