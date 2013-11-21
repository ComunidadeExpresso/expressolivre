<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="lang_nameChatRoom" />
	<xsl:param name="lang_nickName" />	
	
	<xsl:template match="create_chat_room">
		
		<div style="margin:5px;">
			
			<div style="margin-top:10px;">
				<label><xsl:value-of select="$lang_nickName"/> : </label>
				<span style="position:absolute; left: 85px;">
					<input id="nickName_chatRoom_jabberit" type="text" size="35" maxlength="35"/>
				</span>
			</div>
			
			<div style="margin-top:10px;">
				<label><xsl:value-of select="$lang_nameChatRoom"/> : </label>
				<span style="position:absolute; left: 85px;">
					<input id="name_ChatRoom_jabberit" type="text" size="35" maxlength="35"/>
				</span>
			</div>
		</div>
		
		<div id="buttons_createChatRoom" style="float:left; margin:20px 0px 0px 10px;" />		
		
	</xsl:template>
	
</xsl:stylesheet>