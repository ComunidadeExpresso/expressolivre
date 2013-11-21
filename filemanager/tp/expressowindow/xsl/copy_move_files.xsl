<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="action" />
	<xsl:param name="img" />
	<xsl:param name="img_1" />
	<xsl:param name="height" />
	<xsl:param name="width" />
	
	<xsl:template match="files">
		<div style="margin:5px;">
			<span style="margin-left:5px;"><img src="{$img}" style="margin-right:5px;"/><xsl:value-of select="$action"/></span>
			<div style="width:{($width)-30}px; height:{($height)-70}px; padding:2px !important; border:1px solid #000; overflow-y: auto;">
				<xsl:for-each select="links/lk">
						<xsl:sort select="."/>
						<img src="{$img_1}" style="margin-right:2px;"/>
						<span style="margin-left:2px;">
							<a href="{@function}">
								<xsl:value-of select="."/>
							</a>
						</span>
						<br/>
				</xsl:for-each>
			</div>
		</div>
	</xsl:template>
	
</xsl:stylesheet>