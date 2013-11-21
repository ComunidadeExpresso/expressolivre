<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="lang_1" />
	<xsl:param name="lang_2" />
	<xsl:param name="lang_3" />
	<xsl:param name="lang_4" />
		
	<xsl:template match="notification_new_users">
		
		<fieldset style="margin:3px; padding:5px;border:1px solid #cecece;">

			<legend><xsl:value-of select="$lang_1"/></legend>
			<label><xsl:value-of select="$lang_2"/></label>
			
			<div style="border:1px solid #cecece; margin-top: 10px; height:210px; overflow-y:auto;">
				<table style="width: 100%;">
					<xsl:for-each select="user">
						<xsl:sort select="jid"/>
						<tr id="itenContactNotification_{jid}" subscription="{status}">
							<td align="left" style="width: 60%;">
								<xsl:value-of select="jid" />
							</td>
							
							<td align="center" style="width: 20%;">
								<div style="cursor:pointer; color:green;" onclick="loadscript.setAutorization('{jid}');">
									<xsl:value-of select="$lang_3"/>
								</div>
							</td>
							
							<td align="center" style="width: 20%;">
								<div style="cursor:pointer; color:red;" onclick="loadscript.removeContact('{jid}');">
									<xsl:value-of select="$lang_4"/>
								</div>
							</td>
							
						</tr>
					</xsl:for-each>
				</table>
			</div>
		
		</fieldset>
		
	</xsl:template>
	
</xsl:stylesheet>