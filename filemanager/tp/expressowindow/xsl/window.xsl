<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name = "id_window" />
	<xsl:param name = "left" />	
	<xsl:param name = "height" />
	<xsl:param name = "onclick" />
	<xsl:param name = "title" />
	<xsl:param name = "top" />
	<xsl:param name = "width" />
	<xsl:param name = "zindex" />
	
	<xsl:template match="window_main">
			
			<!-- Principal -->
			<div id="{$id_window}__main" style="z-index: {($zindex)+1}; width: {$width}px;" class="x-window">
				<div class="x-window-tl">
					<div class="x-window-tr">
						<div class="x-window-tc">
							<div id="{$id_window}__draggable" style="-moz-user-select: none; width: {($width)-31}px;" class="x-window-header x-unselectable x-window-draggable">
								<xsl:value-of select="$title"/>
							</div>
							<div class="x-tool x-tool-close" onclick="{$onclick}"></div>
						</div>
					</div>
				</div>
				<div class="x-window-bwrap">
					<div class="x-window-ml">
						<div class="x-window-mr">
							<div class="x-window-mc">
								<div style="width: {($width)-14}px; height: {($height)-32}px;" class="x-window-body">
									<div style="width: {($width)-14}px;" class="x-tab-panel x-tab-panel-noborder">
										
										<!-- Conteudo -->
										<div class="x-tab-panel-bwrap">
											<div style="width: {($width)-14}px; height: {$height}px;" class="x-tab-panel-body x-tab-panel-body-noborder x-tab-panel-body-top">
												<div style="width: {($width)-14}px;" class="x-panel x-panel-noborder">
													<div class="x-panel-bwrap">
														<div style="width: {($width)-14}px; height: {($height)-34}px;" class="x-panel-body x-panel-body-noheader x-panel-body-noborder">
															<div id="{$id_window}__content">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="x-window-bl x-panel-nofooter">
						<div class="x-window-br">
							<div class="x-window-bc"></div>
						</div>
					</div>
				</div>
			</div>
			
	</xsl:template>
	
</xsl:stylesheet>