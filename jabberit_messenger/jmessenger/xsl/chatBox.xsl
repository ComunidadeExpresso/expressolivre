<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="enabledPopUp" />	
	<xsl:param name="idChatBox" />
	<xsl:param name="jidTo" />
	<xsl:param name="path_jabberit" />

	<xsl:template match="chat_box">
	
			<div>
				<div id="{$idChatBox}" style="height:190px; width:370px; overflow-y:scroll;"></div>
				<div id="{$jidTo}__chatState" style="height:15px; width:365px;"></div>
				<div style="margin:2px;">
					<textarea id="{$jidTo}__sendBox" class="trophyimchatinput" style="padding-left: 78px; height:130px; width:360px;"></textarea>
					<div style="position:relative;margin:-126px 0 0 10px; border-right:1px dotted #000 !important; width:67px; height:120px;">
						<div id="{$jidTo}__photo" style="width:60px ;height:80px ;background-image:url('{$path_jabberit}templates/default/images/photo.png');" />
						<div style="margin: 7px 5px 5px 10px;">
							<input type="button" value="Send"/>
						</div>
						<div id="{$jidTo}__popUp" onclick="loadscript.windowPOPUP('{$jidTo}', true );" style="display:{$enabledPopUp}; cursor:pointer;padding-left:16px; font-size:10px ;height:14px; width:50px; background:url('{$path_jabberit}templates/default/images/icon_up.png') no-repeat;">
							PopUp
						</div>
					</div>
				</div>
				<input id="{$jidTo}__chatStateOnOff" type="hidden" name="chatStateOnOff" value="off"/>
			</div>			
		 
	</xsl:template>
	
</xsl:stylesheet>