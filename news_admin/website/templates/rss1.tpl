<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns="http://purl.org/rss/1.0/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
>
	<channel rdf:about="{link}">
		<title>{title}</title>
		<link>{link}</link>
		<description>{description}</description>

		<image rdf:resource="{img_url}" />
		
		<items>
			<rdf:Seq>
				<!-- BEGIN seq -->
					<rdf:li rdf:resource="{item_link}"/>
				<!-- END seq -->
			</rdf:Seq>
		</items>
	</channel>
	
	<image rdf:resource="{img_url}" />
		<title>{img_title}</title>
		<link>{img_link}</link>
		<url>{img_url}</url>
	</image>
<!-- BEGIN item -->
	<item>
		<title>{subject}</title>
		<link>{item_link}</link>
		<description>{teaser}</description>
	</item>
<!-- END item -->
</rdf:RDF>
