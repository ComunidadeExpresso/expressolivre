
/* ************************************************************************************************
Função:     formatar(campo,mascara)
Autor:      Alex Nunes Wzorek
Data:       26/08/2008
Descrição:  Função utilizada para formatar máscaras genéricas em campos text
          @ - numeros
          # - demais caracteres
          OnKeyUp="formatar(this,'###-@@/@@@@')"    
**************************************************************************************************/ 

function formatar(campo, mask){
    if(navigator.appName == 'Konqueror') {
        if(window.event && window.event.toString()  == "[object KeyboardEvent]"){
            return true;
        }
    }
    //verifica se mascara é para somente números
    if(mask == '@'){
        campo.value = campo.value.replace(/\D/g,"");
        return;
        /*if(!parseInt(campo.value))
             campo.value = '';
        else
            campo.value = parseInt(campo.value);
        return '';*/
    }
    
    if(campo.value.length > mask.length){
            campo.value = campo.value.substring(0,mask.length);
    }
    else {
            var i = campo.value.length -1;
            var texto = mask.substring(i);
            if(texto.substring(0,1) != '#'){
                    for(j=i;j<=mask.length;j++) {
                            if(mask.substring(j,j+1) == '@'){
                                    var valida = campo.value.substring(j,j+1);
                                    if((valida != 0 && valida != 1 && valida != 2 && valida != 3 && valida != 4 && valida != 5 && valida != 6 && valida != 7 && valida != 8 && valida != 9) || valida==" ")
                                    campo.value = campo.value.substr(0,i);
                                    break;
                            }
                            else {
                                    if(mask.substring(j,j+1) == '#' || mask.substring(j,j+1) == campo.value.substring(j))	
                                            break;
                                    var saida = '#';
                                    if (mask.substring(j,j+1) != saida)
                                    {	
                                            campo.value = campo.value.substring(0,j) + mask.substring(j,j+1) + campo.value.substring(j);
                                    }
                            }
                    }
            }
    }
}


/* ************************************************************************************************
Função:     revalidar(campo,mascara,validacao)
Autor:      Alex Nunes Wzorek
Data:       26/08/2008
Descrição:  Função utilizada para atribuir a máscara e validar campos específicos. 
          Chama a função formatar(), para evitar erros no caso do usuário colar um valor (ctrl+v)
          para o campo text.
          OnBlur="formatar(this,'@@@.@@@.@@@-@@','cpf')"    
**************************************************************************************************/ 

function revalidar(campo,mask,validacao){
    campo.style.background = "#FFFFFF";
        if(mask != ''){
            var aux = campo.value;
            campo.value = '';
            if(mask == '@'){
                // Alterado por Ricardo - 19/09/2008
                //if(parseInt(aux))
                //    campo.value = parseInt(aux);
                campo.value = aux.replace(/\D/g,"");
            }
            else{
                for(i=0;i<aux.length;i++){
                        campo.value += aux.substr(i,1);
                        formatar(campo,mask);
                }
            }
        }
	switch (validacao) {
		case 'cnpj':
			var valor = campo.value;
				
			valor = valor.replace (".","").replace (".","").replace ("-","").replace ("/","");
			
			var a = [];
			var b = new Number;
			var c = [6,5,4,3,2,9,8,7,6,5,4,3,2];
			for (i=0; i<12; i++){
				a[i] = valor.charAt(i);
				b += a[i] * c[i+1];
			}
			if ((x = b % 11) < 2) { a[12] = 0 } else { a[12] = 11-x }
			b = 0;
			for (y=0; y<13; y++) {
				b += (a[y] * c[y]);
			}
			if ((x = b % 11) < 2) { a[13] = 0; } else { a[13] = 11-x; }
			if ((valor.charAt(12) != a[12]) || (valor.charAt(13) != a[13])){
				campo.style.background = "#FFFFC0";
				campo.value = '';
				return false;	
			}
			break; 
		
        case 'email':

			if (trim(campo.value) != ""){
			    var vemail =/^[\w-]+(\.[\w-]+)*@(([\w-]{2,63}\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/;
			    if(!vemail.test(campo.value)){
					campo.style.background = "#FFFFC0";
					campo.value = '';
					return false;	
			    }
			}    
			break;
                        

		case 'cpf':
		
			var valor = campo.value.replace('.','').replace('.','').replace('-','');
			
			if(valor.length != 11 || valor == "00000000000" || valor == "11111111111" ||
				valor == "22222222222" || valor == "33333333333" || valor == "44444444444" ||
				valor == "55555555555" || valor == "66666666666" || valor == "77777777777" ||
				valor == "88888888888" || valor == "99999999999"){
				campo.style.background = "#FFFFC0";
				campo.value = '';
				return false;
				break;
			}
			
			soma = 0;
			for(i = 0; i < 9; i++)
				soma += parseInt(valor.charAt(i)) * (10 - i);
			resto = 11 - (soma % 11);
			if(resto == 10 || resto == 11)
				resto = 0;
			if(resto != parseInt(valor.charAt(9))){
				campo.style.background = "#FFFFC0";
				campo.value = '';
				return false;	
			}
			soma = 0;
			for(i = 0; i < 10; i ++)
				soma += parseInt(valor.charAt(i)) * (11 - i);
			resto = 11 - (soma % 11);
			if(resto == 10 || resto == 11)
				resto = 0;
			if(resto != parseInt(valor.charAt(10))){
				campo.style.background = "#FFFFC0";
				campo.value = '';
				return false;	
			}
			break
		
		case 'data':
		
			var tam = campo.length;
			var dia = campo.value.substr(0,2);
			var mes = campo.value.substr (3,2);
			var ano = campo.value.substr (6,4);
			var array_campo = campo.value.split('/');
			
			if (array_campo.length < 3) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			} else {
				if (array_campo[2].length < 4) {
					campo.value = '';
					campo.style.background = "#FFFFC0";
					return false;
				}
			}
			if (dia > 31 || dia == 0) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			}
			if (mes < 1 || mes > 12) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			}

            var date = new Date();
            if(ano < (date.getFullYear() - 15) || ano > (date.getFullYear() +15)){
				campo.style.background = "#FFFFC0";
				return false;	
                        }
                        if(mes == 1 || mes == 3 || mes == 5 || mes == 7 || mes == 8 || mes == 10 || mes == 12){
                                if  (dia > 31){
						campo.style.background = "#FFFFC0";
						campo.value = '';
						return false;	
       	            	        }
                        }
                        if(mes == 4 || mes == 6 || mes == 9 || mes == 11){
                                if  (dia > 30){
						campo.style.background = "#FFFFC0";
						campo.value = '';
						return false;	
				}
                        }
                        //verifica bisexto
                        if(mes == 2 && ( dia > 29 || ( dia > 28 && (parseInt(ano / 4) != ano / 4)))){
				campo.style.background = "#FFFFC0";
				campo.value = '';
				return false;	
                        }
                        if(mes > 12 || mes < 1){
				campo.style.background = "#FFFFC0";
				campo.value = '';
				return false;	
                        }
            
			break;
			
		case 'hora':
			
			var tam = campo.length;
			var hora = campo.value.substr(0,2);
			var min = campo.value.substr (3,2);
			var array_campo = campo.value.split(':');
			
			if (array_campo.length < 2) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			} else {
				if (array_campo[0].length < 2 || array_campo[1].length < 2) {
					campo.value = '';
					campo.style.background = "#FFFFC0";
					return false;
				}
			}
			if (hora > 23 || hora < 0 ) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			}
			if (min < 0 || min > 59) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			}
			
			break;
		
		case 'tempo':
			
			var tam = campo.length;
			var hora = campo.value.substr(0,2);
			var min = campo.value.substr (3,2);
			var array_campo = campo.value.split(':');
			
			if (array_campo.length < 2) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			} else {
				if (array_campo[0].length < 2 || array_campo[1].length < 2) {
					campo.value = '';
					campo.style.background = "#FFFFC0";
					return false;
				}
			}
			if (min < 0 || min > 59) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			}
			
			break;
			
		case 'mesAno':

			var tam = campo.length;
			var mes = campo.value.substr (0,2);
			var ano = campo.value.substr (3,4);
			var array_campo = campo.value.split('/');

			if (array_campo.length < 2) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			} else {
				if (array_campo[1].length < 4) {
					campo.value = '';
					campo.style.background = "#FFFFC0";
					return false;
				}
			}
			if (mes < 1 || mes > 12) {
				campo.value = '';
				campo.style.background = "#FFFFC0";
				return false;
			}

			var date = new Date();
			if(ano < (date.getFullYear() - 15) || ano > (date.getFullYear() +15)){
				campo.style.background = "#FFFFC0";
				return false;	
			}

			break;
	}
	campo.style.background = "";
	return true;
}

/* ************************************************************************************************
Função:     moeda(campo,casas)
Autor:      Alex Nunes Wzorek
Data:       26/08/2008
Descrição:  Função utilizada para formatar valores monetários com separador de milhar (.) e decimal (,). 
          O segundo parametro é o número de casas decimais desejado
          OnKeyUp="moeda(this,2)"    
**************************************************************************************************/ 

function moeda(campo,casas){
        if(navigator.appName == 'Konqueror') {
            if(window.event.toString()  == "[object KeyboardEvent]"){
                return true;
            }
        }
	var valida = campo.value.substring(campo.value.length-1);	
	if((valida != 0 && valida != 1 && valida != 2 && valida != 3 && valida != 4 && valida != 5 && valida != 6 && valida != 7 && valida != 8 && valida != 9) || valida == ' '){
		campo.value = campo.value.substr(0,campo.value.length-1);
	}
	campo.value = campo.value.replace('.','').replace(',','.');
	if(parseFloat(campo.value) < 1){
		campo.value = parseFloat(campo.value.replace('0.',''));
	}
	var zeros = '';
	if(campo.value.length <= casas){
		for(i=0;i<casas-campo.value.length;i++){
			zeros += '0';
		}
		campo.value = '0,' + zeros + campo.value;
	}
	else{
		if(campo.value.length > (casas+4)){
			var decimal = campo.value.substr(campo.value.length-(casas));
			var inteiro = campo.value.substr(0,campo.value.length-casas).replace(/\./g,'');
			var milhar = '';
			for(i=0;i<inteiro.length;){
				i+=3;
				if((inteiro.substr(inteiro.length-(i+1),4).length == 4) && ((inteiro.length-(i+1)) >= 0)){
					milhar =  inteiro.substr(inteiro.length-i,3) + '.' + milhar;
				}
				else{
                                        milhar = inteiro.substr(0,(inteiro.length-(i+1))+4) + '.' + milhar; 
				}
			}
			if(milhar != '')
				campo.value = milhar.substr(0,milhar.length-1) + ',' +decimal;
		}
		else{
			campo.value = campo.value.substr(0,campo.value.length -casas).replace('.','') + ',' + campo.value.substr(campo.value.length -casas);
		}	
	}
}

/* ************************************************************************************************
Função:     revalidarMoeda(campo,casas)
Autor:      Alex Nunes Wzorek
Data:       26/08/2008
Descrição:  Função utilizada para formatar valores monetários com separador de milhar (.) e decimal (,). 
          Chama a função moeda, para formatar valores que possam ter sido colados (ctrl+v) pelo usuario.
          OnBlur="moeda(this,2)"    
**************************************************************************************************/

function revalidarMoeda(campo,casas){
	aux = campo.value;
	campo.value = '';
	for(j=0;j<aux.length;j++){
         	campo.value += aux.substr(j,1);
		moeda(campo,casas);
	}
}

/* ************************************************************************************************
Função:     milhar(campo)
Autor:      Ricardo Andre Pikussa
Data:       26/08/2008
Descrição:  Função utilizada para formatar valores numerico com separador de milhar (.). 
         	OnKeyUp="milhar(this);"    
**************************************************************************************************/ 

function milhar(campo){
    if(navigator.appName == 'Konqueror') {
        if(window.event.toString()  == "[object KeyboardEvent]"){
            return true;
        }
    }
	var valida = campo.value.substring(campo.value.length-1);	
	if((valida != 0 && valida != 1 && valida != 2 && valida != 3 && valida != 4 && valida != 5 && valida != 6 && valida != 7 && valida != 8 && valida != 9) || valida == ' '){
		campo.value = campo.value.substr(0,campo.value.length-1);
	}
	campo.value = campo.value.replace('.','').replace(',','.');
	if(parseFloat(campo.value) < 1){
		campo.value = parseFloat(campo.value.replace('0.',''));
	}
		if(campo.value.length >= 3){
		var decimal = campo.value.substr(campo.value.length);
		var inteiro = campo.value.substr(0,campo.value.length).replace(/\./g,'');
		var milhar = '';
		for(i=0;i<inteiro.length;){
			i+=3;
			if((inteiro.substr(inteiro.length-(i+1),4).length == 4) && ((inteiro.length-(i+1)) >= 0)){
				milhar =  inteiro.substr(inteiro.length-i,3) + '.' + milhar;
			} else {
				milhar = inteiro.substr(0,(inteiro.length-(i+1))+4) + '.' + milhar; 
			}
		}
		if(milhar != '')
			campo.value = milhar.substr(0,milhar.length-1) + decimal;
	} else {
		campo.value = campo.value.substr(0,campo.value.length).replace('.','') + campo.value.substr(campo.value.length);
	}	
}

/**
 * Função:     	revalidarMilhar(campo)
 * Autor:      	Ricardo Andre Pikussa
 * Data:       	26/08/2008
 * Descrição:  	Função utilizada para formatar valores numerricos com separador de milhar (.). 
          		Chama a função milhar, para formatar valores que possam ter sido colados (ctrl+v) pelo usuario.
 * Chamada:		OnBlur="revalidarMilhar(this);"    
 */
function revalidarMilhar(campo){
	aux = campo.value;
	campo.value = '';
	for(j=0;j<aux.length;j++){
        campo.value += aux.substr(j,1);
		milhar(campo);
	}
}

/**
 * Função:     retiraCaracteres(campo,upper)
 * Autor:      Ricardo Andre Pikussa
 * Data:       12/12/2008
 * Descrição:  Função utilizada para retiraar caracteres indesejados em campos text
	Se preciso ela tambem passa os caracteres para caixa alta 
 * Chamada:		onBlur="retiraCaracteres(this);"
 */
function retiraCaracteres(campo, upper) 
{
	campo.value = campo.value.replace(/[\'°ºª]/g,"");
	if (upper == 'true') {
		campo.value = campo.value.toUpperCase();
	}
}

function somenteNumero(campo){
	
    var digits = "0123456789.";
    var campo_temp 
    
    for (var i=0;i<campo.value.length;i++){
    
    	campo_temp=campo.value.substring(i,i+1)
    	
    	if (digits.indexOf(campo_temp)==-1){
    		
    		campo.value = campo.value.substring(0,i);
    		break;
    	}
    }
}

function revalidaSomenteNumero(campo,casas){

	var digits = "0123456789.";
	var campo_temp; 
	    
	for (var i=0;i<campo.value.length;i++){
	      
		campo_temp = campo.value.substring(i,i+1)       
	      
		if (digits.indexOf(campo_temp)==-1){
			campo.value = campo.value.substring(0,i);
            break;
		}
	}
	
	valor = campo.value;
	
	if(valor.length > 0){
	
		valor = parseFloat(valor);
		campo.value = valor.toFixed(casas);
	}
}

