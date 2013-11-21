/*********************** HASH PROTOTYPE **************************/
function Hash(data)	{
	this.last_item = 0;
	this.data = data;
}
Hash.prototype.size = function(){
	var _thisObject = this;		
	var size = 0;
	for (var i in _thisObject.data)
       	if(_thisObject.data[i] != null)
           	size++;    	
   	return size;	
}	
Hash.prototype.seek = function(direction){
	var _thisObject = this;	
	_thisObject.last_item = (direction == "previous" ? 
			(_thisObject.last_item == 0 ? _thisObject.size() - 1 : _thisObject.last_item - 1) : 
			(_thisObject.last_item == _thisObject.size() -1 ? 0	 : _thisObject.last_item + 1));	
	return _thisObject.data[_thisObject.last_item];		
}