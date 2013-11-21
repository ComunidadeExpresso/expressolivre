/*  Script contendo funcoes de manipulacao de cookies.
	// baseado em script de Bill Dortch, hIdaho Design <bdortch@hidaho.com> e
	// adaptado por Jorge Kinoshita.
*/
	function getCookieVal (offset) {
	  var endstr = document.cookie.indexOf (";", offset);
	  if (endstr == -1)
	    endstr = document.cookie.length;
	  return unescape(document.cookie.substring(offset, endstr));
	}
	
	function FixCookieDate (date) {
	  var base = new Date(0);
	  var skew = base.getTime(); // dawn of (Unix) time - should be 0
	  if (skew > 0)  // Except on the Mac - ahead of its time
	    date.setTime (date.getTime() - skew);
	}
	//
	//  Function to return the value of the cookie specified by "name".
	//    name - String object containing the cookie name.
	//    returns - String object containing the cookie value, or null if
	//      the cookie does not exist.
	
	function GetCookie (name) {
	  var arg = name + "=";
	  var alen = arg.length;
	  var clen = document.cookie.length;
	  var i = 0;
	  while (i < clen) {
	    var j = i + alen;
	    if (document.cookie.substring(i, j) == arg)
	      return getCookieVal (j);
	    i = document.cookie.indexOf(" ", i) + 1;
	    if (i == 0) break; 
	  }

	  return 'true';
	}
		
	function SetCookie (name,value,expires,path,domain,secure) {
	  document.cookie = name + "=" + escape (value) +
    	((expires) ? "; expires=" + expdate.toGMTString() : "") +
	    ((path) ? "; path=" + path : "") +
    	((domain) ? "; domain=" + domain : "") +
	    ((secure) ? "; secure" : "");
	}	
	
	var expdate = new Date ();
	FixCookieDate (expdate); // Correct for Mac date bug - call only once for given Date object!
	expdate.setTime (expdate.getTime() + (30 * 24 * 60 * 60 * 1000)); // Valid for 30 days.
