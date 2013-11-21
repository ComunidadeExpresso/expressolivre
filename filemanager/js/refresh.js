var refresh_time = 18000;

setTimeout("pooling_refresh();",refresh_time);


function pooling_refresh(){
	var handler = function(data){
	}
	var filename=document.getElementsByName('edit_file');
	cExecute_("/index.php?menuaction=filemanager.vfs_functions.touch&file="+base64_encode(filename[0].value),handler);
	setTimeout("pooling_refresh();",refresh_time);
}

