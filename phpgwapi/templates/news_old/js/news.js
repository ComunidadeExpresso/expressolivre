/****************************************************************************************\
|************************ 	LOAD NEWS FROM NEWSADMIN MODULE		*************************|
\****************************************************************************************/
function News(data){	
	this.data = new Hash(data);
	this.delay = 5000;
	this.timeout = 0;
}

News.prototype.update = function(direction) {	
	if(this.data.size() == 0) {
		document.getElementById("news").style.visibility ="hidden";
		return true;
	}	
	var item = this.data.seek(direction);	
	document.getElementById("news_subject").innerHTML = item.subject;
	document.getElementById("news_content").innerHTML = item.content;
}

News.prototype.start = function(delay){
	if(delay)
		this.delay = delay;
	
	this.update('next');
	this.timeout = setTimeout("news.start()",this.delay);
}

News.prototype.pause = function(){	
	if(this.timeout){
		document.getElementById("img_player_pause").style.display = 'none';
		document.getElementById("img_player_resume").style.display = '';
		clearTimeout(this.timeout);
		this.timeout = 0;
	}
	else{
		document.getElementById("img_player_pause").style.display = '';
		document.getElementById("img_player_resume").style.display = 'none';
		this.start();
	}
}