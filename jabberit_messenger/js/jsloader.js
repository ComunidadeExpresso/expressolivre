function JsLoader()
{
	this._loadedJS = [];
	this._loadedCss = [];
	
	this.load = function(pFilesArray)
	{
		for(var i = 0; i < pFilesArray.length; i++)
		{
			var pFiles = pFilesArray[i];
			
			if( pFiles.indexOf(".js") > -1 )
			{
				var js = pFiles.toLowerCase();
				
				if(!this._loadedJS[js])
				{
					document.getElementsByTagName("head")[0].appendChild(this.loadJavaScript(js));
					this._loadedJS[js] = true;
				}
			}
			else if( pFiles.indexOf(".css") > -1 )
			{
				var css = pFiles.toLowerCase();
				
				if(!this._loadedCss[css])
				{
					document.getElementsByTagName("head")[0].appendChild(this.loadStyleSheet(css));
					this._loadedCss[css] = true;
				}
			}
		}
	};
	
	this.loadJavaScript = function(pJs)
	{
		var _script = document.createElement("script");
		_script.type = "text/javascript";
		_script.src  = pJs;
		
		return _script;
	};

	this.loadStyleSheet=function(pCss)
	{
		var _style = document.createElement("link");
		_style.rel = "stylesheet";
		_style.type = "text/css";
		_style.href = pCss;
		return _style;
	};
}

JSLoader = new JsLoader();