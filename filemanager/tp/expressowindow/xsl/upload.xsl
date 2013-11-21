<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="change_upload_boxes" />
	<xsl:param name="form_action" />
	<xsl:param name="height"/>
	<xsl:param name="lang_advanced_upload" />
	<xsl:param name="lang_click_here"/>
	<xsl:param name="lang_comment" /> 
	<xsl:param name="lang_delete" /> 	
	<xsl:param name="lang_file" />
	<xsl:param name="lang_more_files" />
	<xsl:param name="lang_send_email" />
	<xsl:param name="lang_upload" />
	<xsl:param name="max_size" />
	<xsl:param name="num_upload_boxes" />
	<xsl:param name="path" />
	<xsl:param name="path_filemanager" />
	<xsl:param name="width" />
	
	<xsl:template match="upload_files">
			 
			<form id="form_up" method="post" action="{$form_action}" enctype="multipart/form-data">
			
				<div style="border-bottom:1px solid #000; margin: 4px;">
						<xsl:value-of select="$lang_more_files" />
						<a href="javascript:void();" onclick="addNewInput()">
							- <span style="color:red;"><xsl:value-of select="$lang_click_here" /></span>
							<img src="{$path_filemanager}templates/default/images/attach.gif" style="margin-right:3px;"/> +
						</a>
				</div>
				
				<div style="height:{$height}px; margin: 4px; overflow-y:auto; overflow-x:hidden;">	
					<div id="sendNotifcation" style="margin: 4px; overflow-y:auto;"/>
					<span style="margin-right:{($width)-250}px;">
						<label style="margin-left:1px;"><xsl:value-of select="$lang_file"/></label>
						<label style="margin-left:210px;"><xsl:value-of select="$lang_comment"/></label>
					</span>
					<br/>
					<div id="uploadOption">
						<input type="hidden" name="uploadprocess" value="true" />
						<input type="hidden" name="path" value="{$path}" />
					</div>	
					<div> 
						<div></div>
					 	<input maxlength="255" name="upload_file[]" type="file" style="margin-right:5px" />
						<input name="upload_comment[]" type="text" style="margin-right:2px;" />						
						<span style="color:red; cursor:pointer;" onclick="removeInput(this);"><xsl:value-of select="$lang_delete"/></span>				
					</div>
					
				</div>
				
				<div  id="upload_files" style="border-top:1px solid #000; margin: 4px;">
					
					<div style="margin: 10 0 5 0;">
						<input value="{$lang_upload}" type="button" onclick="sendFiles();" style="margin-right:5px;"/>
						<input value="{$lang_advanced_upload}" type="button" onclick="newAdvancedUpload();"/>
					</div>

					<div style="margin-top:-25px; position: absolute; right: 5px; width:60px; cursor:pointer;">
						<img src="{$path_filemanager}templates/default/images/email.png" alt="{$lang_send_email}" title="{$lang_send_email}" onclick="sendNotification();"/>
						<br/>
						<label><xsl:value-of select="$lang_send_email"/></label>
					</div>						
					
					<div style="font-weight:bold; color: #f00; font-size:10pt; margin-top: 15px;">
						<xsl:value-of select="$max_size"/>
					</div>
					
				</div>
			</form>			
		
	</xsl:template>
	
</xsl:stylesheet>	