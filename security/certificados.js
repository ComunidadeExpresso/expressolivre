
var ua = navigator.userAgent.toLowerCase();
if (ua.indexOf(" chrome/") >= 0 || ua.indexOf(" firefox/") >= 0 || ua.indexOf(' gecko/') >= 0) {
	var StringMaker = function () {
		this.str = "";
		this.length = 0;
		this.append = function (s) {
			this.str += s;
			this.length += s.length;
		}
		this.prepend = function (s) {
			this.str = s + this.str;
			this.length += s.length;
		}
		this.toString = function () {
			return this.str;
		}
	}
} else {
	var StringMaker = function () {
		this.parts = [];
		this.length = 0;
		this.append = function (s) {
			this.parts.push(s);
			this.length += s.length;
		}
		this.prepend = function (s) {
			this.parts.unshift(s);
			this.length += s.length;
		}
		this.toString = function () {
			return this.parts.join('');
		}
	}
}


var num = 0;
var titulo = '';

// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function encode64(input) {
	var output = new StringMaker();
	var chr1, chr2, chr3;
	var enc1, enc2, enc3, enc4;
	var i = 0;

	while (i < input.length) {
		chr1 = input.charCodeAt(i++);
		chr2 = input.charCodeAt(i++);
		chr3 = input.charCodeAt(i++);

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2)) {
			enc3 = enc4 = 64;
		} else if (isNaN(chr3)) {
			enc4 = 64;
		}

		output.append(keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4));
   }

   return output.toString();
}

function decode64(input) {
	var output = new StringMaker();
	var chr1, chr2, chr3;
	var enc1, enc2, enc3, enc4;
	var i = 0;

	// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

	while (i < input.length) {
		enc1 = keyStr.indexOf(input.charAt(i++));
		enc2 = keyStr.indexOf(input.charAt(i++));
		enc3 = keyStr.indexOf(input.charAt(i++));
		enc4 = keyStr.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output.append(String.fromCharCode(chr1));

		if (enc3 != 64) {
			output.append(String.fromCharCode(chr2));
		}
		if (enc4 != 64) {
			output.append(String.fromCharCode(chr3));
		}
	}

	return output.toString();
}

function msgWin(msg,w,h,params)
{
    var winl = (screen.width - w ) / 2;
    var wint = (screen.height - h) / 2;
    var parm = "width=" + w + ",height=" + h + ",top=" + wint + ",left=" + winl + params;
    win3 = window.open("","12345" + num,parm);
    num = num + 1;
    win3.document.writeln(msg);
    win3.focus();
}

function Remover(valor,valor2)
{
    var resp = confirm("Remover certificado n. " + valor + " do arquivo " + valor2 + " ?");
    if (resp){
        document.getElementById('msgs').innerHTML = '';
        Remover_Certificado(valor,valor2);
        Lista_de_Certificados();
        }
}

function Submete_Cas(id,msg)
{
    if(document.getElementById('file').value != ''){
        var resp = confirm(msg);
        if (resp){
            document.getElementById(id).submit();
            }
        else{
            document.getElementById('file').value = '';
        }
     }
     else{
         alert('Selecione um arquivo valido( .pem, .der, .cer .pfx,  .p7b');
     }
}

function Salvar_arq(id,msg)
{
    var resp = confirm(msg);
    if (resp){
        document.getElementById(id).submit();
        }
}

function Remover_Certificado(valor,valor2)
{
    //verifica se o browser tem suporte a ajax11
    try
    {
        ajax1 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
        try
        {
            ajax1 = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch(ex)
        {
            try
            {
                ajax1 = new XMLHttpRequest();
            }
            catch(exc)
            {
                alert("Esse browser não tem recursos para uso do ajax1");
                ajax1 = null;
            }
        }
    }

    //se tiver suporte ajax1
    if(ajax1)
    {
        ajax1.open("POST", "manut_certs.php", true);
        ajax1.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        ajax1.onreadystatechange = function()
        {
            if(ajax1.readyState == 4 )
            {
                if(ajax1.responseXML)
                {
                    var arqx = processa_remover_certificado(ajax1.responseXML);
                }
                else
                {
                    //caso não seja um arquivo XML emite a mensagem abaixo
                    //alert('Nao foi possivel remover o certificado(M01).');
                }
            }
        }
        //passa o código do certificado desejado...
        var params = "id=" + valor + '&arquivo=' + valor2;
        ajax1.send(params);
    }
}

function processa_remover_certificado(obj)
{
    var dataArray   = obj.getElementsByTagName("certificados");
    //total de elementos contidos na tag
    if(dataArray.length > 0)
        {
            if (navigator.userAgent.match('MSIE'))
                {
                    var conteudo = dataArray[0].text;
                }
                else
                {
                    var conteudo = dataArray[0].textContent;
                }
            if(conteudo.substr(0,2) == 'OK')
                {
                    alert('Certificado removido de ' + conteudo.substr(2) + '.');
                    var path3 = conteudo.substr(2);
                }
            else
                {
                    alert('Certificado nao removido de ' + conteudo.substr(0,4));
                    var path3 = conteudo.substr(2);
                }
        }
    else
        {
            //caso o XML volte vazio, printa a mensagem abaixo
            alert('Nao foi possivel remover o certificado(M02).');
            var path3 = '';
        }
    return path3;
}



function Um_Certificado(valor,valor2)
{	
    if(valor.length < 2) {
        return;
    }
    //verifica se o browser tem suporte a ajax3
    try
    {
        ajax3 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
        try
        {
            ajax3 = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch(ex)
        {
            try
            {
                ajax3 = new XMLHttpRequest();
            }
            catch(exc)
            {
                alert("Esse browser não tem recursos para uso do ajax3");
                ajax3 = null;
            }
        }
    }

    //se tiver suporte ajax3
    if(ajax3)
    {
        titulo = valor;
        ajax3.open("POST", "certs_xml.php", true);
        ajax3.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        ajax3.onreadystatechange = function()
        {
            //após ser processado - chama função processa_um_certificado que vai varrer os dados
            if(ajax3.readyState == 4 )
            {
                if(ajax3.responseXML)
                {
                    processa_um_certificado(ajax3.responseXML);
                }
                else
                {
                    //caso não seja um arquivo XML emite a mensagem abaixo
                    msg = 'N&atilde;o foi poss&iacute;vel obter os dados solicitados(1).' + '<br/><br/><div align="center"><INPUT type="button" value="Fechar" onClick="window.close()">';
                    parms = ",scrollbars=1";
                    msgWin(msg,450,200,parms)
                }
            }
        }
        //passa o código do certificado desejado...
        var params = "id="+valor + '&arquivo=' + valor2;
        ajax3.send(params);
    }
}
      
function processa_um_certificado(obj)
{
    // Tab com tags que deve exibir sho o nome....
    var TBtags = new Array( "EMISSOR_CAMINHO_COMPLETO", "SUBJECT" , "CRLDISTRIBUTIONPOINTS" , "KEYUSAGE" , "EXTKEYUSAGE" );
    //pega a tag certificado
    var dataArray   = obj.getElementsByTagName("certificado");
    //total de elementos contidos na tag
    if(dataArray.length > 0)
    {
        var novo = "<head><title>" + titulo + "</title></head><body><font size=2><h3 align=center ><font color=#0000EE>Dados do certificado</font></h3>";
        //percorre o arquivo XML para extrair os dados
        for(var i = 0 ; i < dataArray.length ; i++)
        {
            var item = dataArray[i];
            var cc    =  item.getElementsByTagName("*");
            len = 0;
            for(var j = 0 ; j < cc.length ; j++)
            {
                //contéudo dos campos no arquivo XML
                var tag = cc[j].nodeName
                if(tag == "CA") continue; // skipa tag CA ....
                var xflag = 0;
                if(tag.substr(0,3) == "oid") xflag = 1; 
                for(var iz = 0;iz< TBtags.length;iz++)
                {
                    if(TBtags[iz] == tag) xflag = 1;
                }
                // Testa se deve exibir o valor da tag..
                if(xflag ==1 )
                {
                    var nome    =   "<b>" +tag +":</b> ";
                }
                else
                {
                    // Testa o browser para usar a propriedade  correta ...
                    if (navigator.userAgent.match('MSIE'))
                    {
                        var conteudo = cc[j].text;
                    }
                    else
                    {
                        var conteudo = cc[j].textContent;
                    }
										
                    if(conteudo == "1")
                    {
                        conteudo = "Sim.";
                    }
											
                    if(conteudo == "")
                    {
                        conteudo = "N&atilde;o.";
                    }
											
                    switch(tag)
                    {
											
                        case "INICIO_VALIDADE":
                        {
                            var aux = conteudo.substr(0,4) + "/" + conteudo.substr(4,2) + "/" + conteudo.substr(6,2)  + "  -  " + conteudo.substr(8,2)  + ":" + conteudo.substr(10,2)  + ":" + conteudo.substr(12,2)  + " GMT";
                            conteudo = aux;
                            break;
                        }
                        case "FIM_VALIDADE":
                        {
                            var aux = conteudo.substr(0,4) + "/" + conteudo.substr(4,2) + "/" + conteudo.substr(6,2)  + "  -  " + conteudo.substr(8,2)  + ":" + conteudo.substr(10,2)  + ":" + conteudo.substr(12,2)  + " GMT";
                            conteudo = aux;
                            break;
                        }
                        default:
                        {
                            break;
                        }
                    }
                    var nome    =  "<b>" + tag +":</b> " + conteudo;
                }
                novo = novo + nome + '<br/>';
                len = len + 24;
            }
        }
        msg = novo + '<br/><br/><div align="center"><INPUT type="button" value="Fechar" onClick="window.close()"></font></body>';
    }
    else
    {
        //caso o XML volte vazio, printa a mensagem abaixo
        msg = 'N&atilde;o foi poss&iacute;vel obter dados do certificado(2).' + '<br/><br/><div align="center"><INPUT type="button" value="Fechar" onClick="window.close()"></font></body>';
        len =200;
    }
    parms = ",scrollbars=1";
    num = num + 1;
    msgWin(msg,450,len,parms)
}

function Lista_de_Certificados()
{
    //document.getElementById('titulo1').innerHTML  = 'Certificados em ' + path4;
    try
    {
        ajax4 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
        try
        {
            ajax4 = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch(ex)
        {
            try
            {
                ajax4 = new XMLHttpRequest();
            }
            catch(exc)
            {
                alert("Esse browser não tem recursos para uso do ajax4");
                ajax4 = null;
            }
        }
    }

    if(ajax4)
    {
        ajax4.open("POST", "certs_xml.php", true);
        ajax4.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        ajax4.onreadystatechange = function()
        {
            if(ajax4.readyState == 4 )
            {
                if(ajax4.responseXML)
                {
                    Processa_Lista_de_Certificados(ajax4.responseXML);
                    Lista_Arvore();
                }
                else
                {
                    //caso não seja um arquivo XML emite a mensagem abaixo
                    alert("Ocorreu um erro acessando os dados solicitados.Er-001 - " + ajax4.readyState);
                }
            }
        }
        document.getElementById('xdiv1').innerHTML = '<br/><font color="#000066"><b> Carregando ...</b></font>';
        document.getElementById('xdiv2').innerHTML = '<br/><font color="#000066"><b> Carregando ...</b></font>';
        //document.getElementById('path3x').value = path4;
        //passa o código do certificado desejado...
        //var params = "id=A&path3=" + path4;
        var params = "id=A";
        ajax4.send(params);
    }
}

function Processa_Lista_de_Certificados(obj)
{
    //pega a tag certificado
    var dataArray   = obj.getElementsByTagName("cert");
    if(dataArray.length > 0)
    {
        var novo = '<table border="1" width="100%" ><tr><th>Item</th><th>Remover</th><th>Autoridade Certificadora</th><th>Validade</th></tr>';
        //percorre o arquivo XML para extrair os dados
        var conteudo = '';
        for(var i = 0 ; i < dataArray.length ; i++)
        {
            var item = dataArray[i];
            var cc    =  item.getElementsByTagName("*");
                    // Testa o browser para usar a propriedade  correta ...
                //var aux = path5.split('/');
                var aux = 'todos.cer';
                //aux_novo =  '<a href="javascript:Remover(\''+ cc[0].textContent + '\',\'' + aux[aux.length-1] + '\')" style="text-decoration: none;margin:0 0 0 20" ><img src="delete.gif" style="border:none" title="Clique para remover este certificado..."/></a>';
                aux_novo =  '<a href="javascript:Remover(\''+ cc[0].textContent + '\',\'' + aux + '\')" style="text-decoration: none;margin:0 0 0 20" ><img src="delete.gif" style="border:none" title="Clique para remover este certificado..."/></a>';
                //novo = novo + aux_novo + conteudo + '<br/>';
                    if (navigator.userAgent.match('MSIE'))
                    {
                        var conteudo1 = cc[1].text;
                        if (conteudo1.indexOf('DUPLICADO') != -1)
                            {
                                var conteudo1 = '<font color="#FF0000"><b>' + conteudo + '</b></font>';
                            }
                        var conteudo = '<tr><td><font size="1">' + cc[0].text + '</font></td><td><font size="1">' + aux_novo + '</font> </td><td><font size="1">' + conteudo1  + ' </font></td><td><font size="1">' + cc[2].text + '</font></td></tr>';
                    }
                    else
                    {
                        if ( cc[2].textContent.indexOf('DUPLICADO') != -1)
                            {
                                var conteudo2 = '<font size="1" color="#FF0000"><b>' + cc[2].textContent + '</b></font>';
                            }
                        else
                            {
                                var conteudo2 = '<font size="1">' +  cc[2].textContent + '</font>';
                            }
                        var conteudo = '<tr><td><font size="1">' + cc[0].textContent + '</font></td><td><font size="1">' + aux_novo + ' </font></td><td><font size="1">' + cc[1].textContent  + ' </font></td><td>' + conteudo2 + '</td></tr>';
                        //var conteudo = cc[0].textContent + ' - ' + cc[1].textContent  + ' # ' + cc[2].textContent;
                    }

                    if (conteudo.indexOf('DUPLICADO') != -1)
                    {
                        var conteudo = '<font color="#FF0000"><b>' + conteudo + '</b></font>';
                    }


                    novo = novo + conteudo;

        }
        msg = novo + '</table>';
    }
    else
    {
        //caso o XML volte vazio, printa a mensagem abaixo
        msg = 'N&atilde;o foi poss&iacute;vel obter dados dos certificados.V02';
    }
    document.getElementById('xdiv1').innerHTML = msg;
}

function Lista_Arvore(path3)
{
    //document.getElementById('titulo1').innerHTML  = 'Certificados em ' + path3;
    //verifica se o browser tem suporte a ajax5
    try
    {
        ajax5 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
        try
        {
            ajax5 = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch(ex)
        {
            try
            {
                ajax5 = new XMLHttpRequest();
            }
            catch(exc)
            {
                alert("Esse browser não tem recursos para uso do ajax5");
                ajax5 = null;
            }
        }
    }
    //se tiver suporte ajax5
    if(ajax5)
    {
        ajax5.open("POST", "gera-arvore.php", true);
        ajax5.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        ajax5.onreadystatechange = function()
        {
            if(ajax5.readyState == 4 )
            {
                if(ajax5.responseXML)
                {
                    Processa_Lista_Arvore(ajax5.responseXML);
                }
                else
                {
                    //caso não seja um arquivo XML emite a mensagem abaixo
                    alert("Erro lendo Certificdos das Cas...")
                }
            }
        }
        //passa o código do certificado desejado...
        var params = "path3=" + path3;
        ajax5.send(params);
    }
}

function Processa_Lista_Arvore(obj)
{
    var dataArray   = obj.getElementsByTagName("certificados");
    if(dataArray[0].textContent)
        {
            document.getElementById('xdiv2').innerHTML = decode64(dataArray[0].textContent);
        }
    else
        {
             document.getElementById('xdiv2').innerHTML = 'N&atilde;o foi poss&iacute;vel obter dados dos certificados.V01';
        }
}
