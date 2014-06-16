$(document).ready(function(){
	$.ajax({
		url:		"./phpgwapi/templates/news/list_images.php",
		type:		"POST",
		dataType:	'json',
		success:	function(data) {
			
			if ( data == null ) return;
			
			var _html = "";
			var length = 0;
			for( var i in data ) {
				// Template EJS
				_html += new EJS({url: './phpgwapi/templates/news/sliderImages.ejs'}).render({
					'name_image'	: data[i]['name'],
					'title_image'	: data[i]['title'],
					'text_image'	: data[i]['text'],
					'link_image'	: ( data[i]['link'] ) ? data[i]['link'] : ""
				});
				length++;
			}
			$('#information-login')
				.append('<ul id="slider-dock">'+_html+'</ul>')
				.after('<div id="nav-information-login"></div>');
			
			if ( length < 2 ) return;
			
			$('#slider-dock').cycle({
				fx:			'scrollLeft',
				timeout:	6100,
				speed:		2000,
				random:		1,
				pager:		'#nav-information-login'
			});
			
			$('#nav-information-login').append('<a id="nav-information-login-control" href="#" class="play-pause"></a>');
			
			$('#nav-information-login-control').click(function(e) {
				$('#slider-dock').cycle($(this).hasClass('play')?'resume':'pause');
				$(this).toggleClass('play');
			});
		}
	});
});