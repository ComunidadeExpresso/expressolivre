<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="idChatRoom" />
	<xsl:param name="jidTo" />
	<xsl:param name="lang_Send" />
	<xsl:param name="lang_Leave_ChatRoom" />
	<xsl:param name="path_jabberit" />

	<xsl:template match="chat_room">
	
			<div>
				<div id="{$idChatRoom}" style="height:300px; width:328px; overflow-y:scroll;"></div>
				<div id="{$idChatRoom}__participants" style="float:right; position: absolute; top:0px; left:330px; width:150px; height:380px; overflow-y: auto;"></div>
				<div style="margin:2px;">
					<textarea id="{$jidTo}__sendRoomChat" style="height:80px; width:325px;"></textarea>
					<div style="margin: 5px;">
						<input type="button" value="{$lang_Send}" style="margin-right: 5px;"/>
						<input type="button" value="{$lang_Leave_ChatRoom}"/>
					</div>
					
					
				</div>
			</div>			
		 
	</xsl:template>
	
</xsl:stylesheet>