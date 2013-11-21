/*  Script utilizado para detectar o browser

	Variaveis Globais no Expresso:

	OS 		-> retorna o Sistema Operacional
	browser -> retorna o nome do Browser
	version -> retorna a versão do Browser
	isExplorer -> retorna true se Browser for Internet Explorer
*/
	var detect = navigator.userAgent.toLowerCase();
	var OS,browser,version,total,thestring;
	var isExplorer = false;

	if (checkIt('konqueror'))
	{
		browser = "Konqueror";
		OS = "Linux";
	}
	else if (checkIt('safari')) browser = "Safari"
	else if (checkIt('omniweb')) browser = "OmniWeb"
	else if (checkIt('opera')) browser = "Opera"
	else if (checkIt('webtv')) browser = "WebTV";
	else if (checkIt('icab')) browser = "iCab";
	else if (checkIt('msie')) {browser = "Internet Explorer";isExplorer=true;}
	else if (!checkIt('compatible'))
	{
		browser = "Netscape Navigator"
		version = detect.charAt(8);
	}
	else browser = "An unknown browser";
	
	if (!version) version = detect.charAt(place + thestring.length);
	
	if (!OS)
	{
		if (checkIt('linux')) OS = "Linux";
		else if (checkIt('x11')) OS = "Unix";
		else if (checkIt('mac')) OS = "Mac"
		else if (checkIt('win')) OS = "Windows"
		else OS = "an unknown operating system";
	}
	
	function checkIt(string)
	{
		place = detect.indexOf(string) + 1;
		thestring = string;
		return place;
	}
