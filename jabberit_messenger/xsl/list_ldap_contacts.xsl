<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="lang_addContact" />
	<xsl:param name="lang_empty" />
	<xsl:param name="lang_error" />
	<xsl:param name="lang_many_results" />
	
	<xsl:template match="/">

		<xsl:if test="error">
			<label style="color:red;"><xsl:value-of select="$lang_error" /></label>
		</xsl:if>
		
		<xsl:if test="empty">
			<label style="color:red;"><xsl:value-of select="$lang_empty" /></label>
		</xsl:if>
		
		<xsl:if test="manyresults">
			<label style="color:red;"><xsl:value-of select="$lang_many_results" /></label>
		</xsl:if>

		<xsl:if test="uids">
			<xsl:apply-templates select="uids/*" mode="uids" />
		</xsl:if>
		
	</xsl:template>

	<xsl:template match="*" mode="uids">
		<xsl:for-each select="data">
			<xsl:sort select="cn"/>
			<span value="{mail};{uid}" jid="{jid}" ou="{ou}" photo="{photo}">
				<span id="{mail};{uid}" style="display:none;"><xsl:value-of select="cn" /></span>
				<b><xsl:value-of select="name(..)"/></b>
				<br/><xsl:value-of select="cn" />
				<br/>
				<br/>
				<label id="__label__{mail};{uid}" style="color:blue;cursor:pointer;"><xsl:value-of select="$lang_addContact" /></label>
				<br/>
			</span>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>
