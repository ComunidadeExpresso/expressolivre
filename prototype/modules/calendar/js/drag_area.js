/* não permite que os elementos da nova agenda sejam selecionados e arrastados */
// ie
document.onselectstart = function() {return false;}
// Mozilla Firefox
document.onmousedown = function(event) {
	// 'escapar' os campos onde o clique é bloqueado.
	if (event && event.target.nodeName != 'INPUT' && event.target.nodeName != 'TEXTAREA' && event.target.nodeName != 'SELECT' && event.target.nodeName != 'OPTION'){
		if (typeof event.preventDefault != 'undefined') {
			event.preventDefault();
		}
	}
}