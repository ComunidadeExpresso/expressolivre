<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="path" />
	<xsl:param name="lang1" />
	<xsl:param name="lang2" />
	<xsl:param name="lang3" />
	<xsl:param name="lang4" />	
	<xsl:param name="lang5" />	
	<xsl:param name="lang8" />
	<xsl:param name="lang9" />
	<xsl:param name="lang10" />
	<xsl:param name="lang11" />
	<xsl:param name="lang12" />
	<xsl:param name="lang13" />
	<xsl:param name="lang14" />
	<xsl:param name="langYes" />
	<xsl:param name="langNo" />		
	
	<xsl:template match="preferences">
		<fieldset style="margin:3px; padding:5px;border:1px solid #cecece;">
			<legend><xsl:value-of select="$lang1"/></legend>
			<fieldset style="height:60px;margin-top:6px;padding:5px; border:1px solid #cecece;">
				<legend><xsl:value-of select="$lang2"/></legend>	
				<br/>
				<label><xsl:value-of select="$lang3"/> .: </label>
				<select id="openWindowJabberit">
					<option value="true"><xsl:value-of select="$langYes"/></option>
					<option value="false"><xsl:value-of select="$langNo"/></option>					
				</select>
			</fieldset>
			
			<fieldset style="height:60px;margin-top:6px;padding:5px; border:1px solid #cecece;">
				<legend><xsl:value-of select="$lang10"/></legend>			
				<br/>
				<label><xsl:value-of select="$lang11"/><input id="flagAwayIM" type="text" size="2" maxlength="2" style="margin-left:4px; margin-right:4px;" onclick="this.select();" /><xsl:value-of select="$lang12"/></label>
			</fieldset>
			
			<fieldset style="height:60px;margin-top:6px;padding:5px; border:1px solid #cecece;">
				<legend><xsl:value-of select="$lang13"/></legend>			
				<br/>
				<label><xsl:value-of select="$lang14"/> .: </label>
				<select id="showContactsOfflineJabberit">
					<option value="true"><xsl:value-of select="$langYes"/></option>
					<option value="false"><xsl:value-of select="$langNo"/></option>					
				</select>
			</fieldset>
			
		</fieldset>

		<div id="buttons_preferences_jabberit" style="margin:5px;padding:5px;cellpadding:5px;"/>
		
	</xsl:template>

</xsl:stylesheet>
