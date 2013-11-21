(function()
{
	var divSlider = null;

	function loadImages()
	{
		$.ajax(
		{
			  url: "./phpgwapi/templates/news/list_images.php",
			  type	: "POST",
			  dataType: 'json',
			  success: function(data)
			  {
			  		var _html = "";

			  		for( var i in data )
			  		{
		  				// Template EJS
						_html += new EJS({url: './phpgwapi/templates/news/sliderImages.ejs'}).render(
						{
							'name_image'	: data[i]['nome'],
							'title_image'	: data[i]['titulo'],
							'text_image'	: data[i]['texto'],
							'link_image'	: ( data[i]['link'] ) ? data[i]['link'] : ""
						});
			  		}

			  		var ulSlider = $("<ul>");

			  		ulSlider.html( _html );

			  		divSlider.append(ulSlider);

			  		configSlider();
			  }
		});
	}

	function configSlider()
	{
		divSlider.after('<div id="nav-informacao-login"></div>');
		divSlider.find('ul').show();
		divSlider.find('ul').cycle({
			fx:      'scrollLeft',
			timeout: 6100,
			speed:   1000,
			random:  0,
			pager:'#nav-informacao-login'
		});
		
		$('#nav-informacao-login-control').toggle(function(e) {
			e.preventDefault();
			divSlider.find('ul').cycle('pause');
			$(this).addClass('play');
		},
		
		function() {
			divSlider.find('ul').cycle('resume');
			$(this).removeClass('play');
		});

	}

	function sliderLoad()
	{
		$(document).ready(function()
		{		
			divSlider = $('#informacao-login');
			
			loadImages();
		});
	}

	window.sliderLoad = new sliderLoad;

})();