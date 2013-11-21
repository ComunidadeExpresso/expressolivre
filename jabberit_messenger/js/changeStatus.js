(function()
{
	// Path JabberIt 
	var path_jabberit = null;
	
	function getStatus(pStatus, pElement)
	{
    	var element = pElement;
		var status = pStatus;

		var url_img = path_jabberit + 'templates/default/images/' + status + '.gif';
		element.style.backgroundImage = 'url(' + url_img +')';
	}

	function setPath()
	{
		if( arguments.length > 0 && path_jabberit == null )
			path_jabberit = arguments[0];
	}
	
	function changeStatus(){}

	changeStatus.prototype.get			= getStatus;
	changeStatus.prototype.setpath		= setPath;
	
	window.changestatus = new changeStatus;

})();	