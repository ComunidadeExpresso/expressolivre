<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name = "lang_delete" />
	<xsl:param name = "lang_send_notification_email_to" />
	<xsl:param name = "value_email" />
	
	<xsl:template match="send_notification">
	
		<div style="margin:4px;">
			<xsl:choose>
				<xsl:when test="$value_email">
					<input type="hidden" name="notifTo[]" size="38" value="{$value_email}" />
				</xsl:when>
				
				<xsl:otherwise>
					<label>
						<xsl:value-of select="$lang_send_notification_email_to" />
					</label>
					<br/>
					<input type="text" name="notifTo[]" size="38"/>
					<span style="color:red; cursor:pointer; margin-left:2px;" onclick="removeInput(this);">
						<xsl:value-of select="$lang_delete" />
					</span>
				</xsl:otherwise>
			</xsl:choose>
		</div>
		
	</xsl:template>
	
</xsl:stylesheet>