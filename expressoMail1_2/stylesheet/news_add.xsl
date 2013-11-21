<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="rss/channel">
	<span><xsl:value-of select = "title" /></span><span> - <xsl:value-of select="count(item)"/> news</span>
	<img src='../phpgwapi/templates/default/images/foldertree_trash.png'>
		<xsl:attribute name="onclick">
			news_edit.unsubscribe('<xsl:value-of select="link"/>',this);
		</xsl:attribute>
	</img>
</xsl:template>
</xsl:stylesheet>
