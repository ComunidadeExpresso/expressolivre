function formatCalendarInput(campo, e)
{
	var key = '';
	var valor = '';
	var valorLimpo = '';
	var len = 0;
	var strCheck = '0123456789';

	/* permite algumas teclas */
	var whichCode = (e.which) ? e.which : e.keyCode;
	if (whichCode == 13)
		return true;  // Enter
	if (whichCode == 8)
		return true;  // Delete
	if (whichCode == 0)
		return true;  // Tab

	key = String.fromCharCode(whichCode);
	if (strCheck.indexOf(key) == -1)
		return false;  // não é um caractere válido

	valor = campo.value;
	len = valor.length;

	for (var i = 0; i < len; i++)
		if (strCheck.indexOf(valor.charAt(i)) != -1)
			valorLimpo += valor.charAt(i);

	valorLimpo += key;
	var valorNovo = '';

	for (var i = 0; i < 8; i++){

		valorNovo += valorLimpo.charAt(i);
		if ((i == 1) || (i == 3))
			valorNovo += '/';
	}

	campo.value = valorNovo;
	return false;
}
