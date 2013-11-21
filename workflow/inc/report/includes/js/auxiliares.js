/**
 * Fun��o:     	$id(nomeCampo)
 * Autor:      	Ricardo Andre Pikussa
 * Data:		12/12/2008
 * Descri��o:	Fun��o utilizada para retornar as propriedades de um campo via getElementById(nomeCampo) 
 * Chamada:		$id('nomeCampo'); ou $id('nomeCampo').value; 
 *     
 */
function $id(nomeCampo)
{
    return document.getElementById(nomeCampo);
}

function trim(str){
    return str.replace(/^\s+|\s+$/g,"");
}

//gerencia o efeito amarelo que aparece nas mensagens ao usuario
var tempo_fade;
function fade(percent,container,topercent) {
	if(!topercent){
		topercent = 100;
	}
	
    clearTimeout(tempo_fade);
    
    if($id(container)){
    
	    $id(container).style.backgroundColor = "rgb(100%, 100%, "+percent+"%)";
	    
	    percent += 10;
	    
	    if(percent<=topercent){
	        tempo_fade = setTimeout("fade("+percent+",'"+container+"',"+topercent+")", 100/*ms*/);
	    }
	
	    if(percent > topercent){
	    	$id(container).style.backgroundColor = "#FFFFC0";
	    	return true;	
	    }
    }
    
}

//gera a mensagem ao usuario
function cria_msg_html(container,mensagem){
    $id(container).innerHTML = "";
    if(mensagem!=""){
    	$id(container).innerHTML = mensagem;
        fade(0,container);
    }
    return;
}

// funcao para mudar a cor de background de um campos
function muda_cor(id_campo){
	document.getElementById(id_campo).style.backgroundColor = "#FFFFC0";
}
// funcao para remover a cor de background de um campos
function remove_cor(id_campo){
    
    if(document.getElementById(id_campo).readOnly == true){
     
        document.getElementById(id_campo).style.backgroundColor = "#F0F0F0";
      
    }else{

	   document.getElementById(id_campo).style.backgroundColor = "";
	   
	}
}

//limpa todos os backgrounds dos inputs
function limpaCores(){
	
	for(i=0;i<document.forms.length;i++){
		
		for (j=0; j<document.forms[i].elements.length ;j++){
			
			/* limpando os inputs txt */
			if(document.forms[i].elements[j].type == 'text'){
				if(document.forms[i].elements[j].id){
					remove_cor(document.forms[i].elements[j].id);
				}
			}
			/* limpando os selects */
			if(document.forms[i].elements[j].type == 'select-one'){
				if(document.forms[i].elements[j].id){
					remove_cor(document.forms[i].elements[j].id);
				}
			}
			/* limpando os textarea */
			if(document.forms[i].elements[j].type == 'textarea'){
				if(document.forms[i].elements[j].id){
					remove_cor(document.forms[i].elements[j].id);
				}
			}
			
		}
		
		/* limpando os checkboxes e radios*/
	ckbox_a_div = document.getElementsByTagName('div');
		
		for(x=0;x<ckbox_a_div.length;x++){
		
			if(ckbox_a_div[x].id){
				remove_cor(ckbox_a_div[x].id);
			}
		}
	}
}

// funcao para criar um div dinamico
function criarDiv(id, html, width, height, left, top, classe) {
	
   var newdiv = document.createElement('div');
   newdiv.setAttribute('id', id);
   
   if (width) {
       newdiv.style.width = width;
   }
   
   if (height) {
       newdiv.style.height = height;
   }
   
   if ((left || top) || (left && top)) {
       newdiv.style.position = "absolute";
       
       if (left) {
           newdiv.style.left = left;
       }
       
       if (top) {
           newdiv.style.top = top;
       }
   }
   
   newdiv.style.color = "#643E41";
   newdiv.style.fontFamily = "Verdana";
   newdiv.style.fontSize = "12px";
   newdiv.style.fontWeight = "bold";
   newdiv.style.verticalAlign = "middle";
   
   if (classe) {
   		newdiv.setAttribute('class',classe);
   		alert(classe);
   }
   
   
   if (html) {
       newdiv.innerHTML = html;
   } else {
       //newdiv.innerHTML = "ops...";
   }
   
   document.body.appendChild(newdiv);

} 

// Fun��o para criar o div de mensagem padr�o
function criaAlerta(mensagem){
    
    if(!document.getElementById("mess")){
                
        criarDiv('mess', '<table height=\"32\" width=\"100%\" valign=\"middle\"><tr onclick=\"removeDiv(\'mess\');\"><td class="\msg\" width=\"96%\" heigth=\"100%\">&nbsp;<font color=\"#CD0000\">' + mensagem + '</font></td><td width=\"4%\"><img width=\"15\" height=\"15\" src=\"images/X.jpg\"></img></td></tr></table>', '100%', '34', '0', '0');
        alinhaDiv('mess');
        id_interval = setInterval("alinhaDiv('mess')",500);
        fade(0,'mess',80);
                
    }else{
    
        fade(0,'mess',80);
    }
}
// Fun��o para remover div de alerta
function removeAlerta(){
    removeDiv('mess');
}

// funcao para remover um div dinamico
function removeDiv(id){
	
	tmp = document.getElementById(id);
	if(tmp){
		document.body.removeChild(tmp);
	}
}

// funcao para alinhar um div ao topo
var id_interval ;

function alinhaDiv(id){
	if(document.getElementById(id)){
				
		if(navigator.appName == "Microsoft Internet Explorer"){
			var new_top = document.body.scrollTop;
		}else{
			var new_top = window.pageYOffset;
		}
		new_top = parseFloat(new_top);
		
		b = new_top; 
	
		document.getElementById(id).style.marginTop = b+'px';
		
	}else{
		clearInterval(id_interval);
	}
}

//funcao para alinhar um div a direita
var id_interval_dir ;

function alinhaDivDir(id){
	
	var tamanho_util = getWidth();
	
	document.getElementById(id).style.display = '';
	
	if(document.getElementById(id).style.display == ''){
		
		document.getElementById(id).style.display = 'none';
		document.getElementById(id).style.marginLeft = '0px';
				
		if(navigator.appName == "Microsoft Internet Explorer"){
			var new_dir = document.body.scrollLeft;
		}else{
			var new_dir = window.pageXOffset;
		}
		new_dir = parseFloat(new_dir);
		tamanho_util = parseFloat(tamanho_util);
		
		b = new_dir+tamanho_util-360 ;
		
		document.getElementById(id).style.marginLeft = b+'px';
		
		document.getElementById(id).style.display = '';
		
	}
}

function retiraAspas (evtKeyPress) {
    
	var nTecla=0;
	if (document.all) {
	
		nTecla = evtKeyPress.keyCode;

	} else {
		nTecla = evtKeyPress.which;
	}
	if (nTecla == 34 || nTecla == 39) {
		return false;
	} else {
		return true;
	}
}

// Fun��o para alterar o style width de um selectbox
function expandirSelect(sel,width) {
    
    if(width){
        sel.style.width = width+'px';  
    }else{
        sel.style.width = 'auto';
    }
}

//Detect if the browser is IE or not.
//If it is not IE, we assume that the browser is NS.
var IE = document.all?true:false;

//If NS -- that is, !IE -- then set up for mouse capture
if (!IE) document.captureEvents(Event.MOUSEMOVE);

//Set-up to use getMouseXY function onMouseMove
//document.onmousemove = getMouseXY;

//Temporary variables to hold mouse x-y pos.s
var tempX = 0;
var tempY = 0;

// Fun��o principal para pegar posi��o Y do mouse
function getMouseY(e) {
	
	if (IE) { // grab the x-y pos.s if browser is IE
		
		tempY = event.clientY + document.body.scrollTop;
		
	}else{  // grab the x-y pos.s if browser is NS

		tempY = e.pageY;
	}  
	// catch possible negative values in NS4

	if (tempY < 0){tempY = 0;}  
	// show the position values in the form named Show
	// in the text fields named MouseX and MouseY
	//document.frm.MouseX.value = tempX
	//document.frm.MouseY.value = tempY
	
	return tempY;
}

// Fun��o principal para pegar posi��o X do mouse
function getMouseX(e) {
	
	if (IE) { // grab the x-y pos.s if browser is IE
		
		tempX = event.clientX + document.body.scrollLeft;

	}else{  // grab the x-y pos.s if browser is NS

		tempX = e.pageX;
	}  
	// catch possible negative values in NS4
	if (tempX < 0){tempX = 0;}
	// show the position values in the form named Show
	// in the text fields named MouseX and MouseY
	//document.frm.MouseX.value = tempX
	//document.frm.MouseY.value = tempY
	
	return tempX;
}

function ListagemExibeOcultaColuna(id_listagem, id_campo){

    var i = 0;
    var continua = true;
    
    while(continua == true){

        if(document.getElementById('td_'+id_campo+'_'+i)){

            if(document.getElementById(id_listagem + '_ck_visivel_' + id_campo).checked == true){
                
        		document.getElementById('td_'+id_campo+'_'+i).style.display = '';
                    
        	}else{
                
        		document.getElementById('td_'+id_campo+'_'+i).style.display = 'none';
        	}

            i++;
            
        }else{
            
            continua = false;
        }
    }

    if(document.getElementById('td_tit_'+id_campo)){

        if(document.getElementById(id_listagem+'_ck_visivel_'+id_campo).checked == true){
            
            document.getElementById('td_tit_'+id_campo).style.display = '';
                
        }else{
            
            document.getElementById('td_tit_'+id_campo).style.display = 'none';
        }
    }

    if(document.getElementById('total_'+id_campo)){

        if(document.getElementById(id_listagem+'_ck_visivel_'+id_campo).checked == true){
            
            document.getElementById('total_'+id_campo).style.display = '';
                
        }else{
            
            document.getElementById('total_'+id_campo).style.display = 'none';
        }
    }
    
    i = 1;
    continua = true;

    while(continua == true){

        if(document.getElementById('subtotal_'+id_campo+'_'+i)){

            if(document.getElementById(id_listagem+ '_ck_visivel_'+id_campo).checked == true){
                
                document.getElementById('subtotal_'+id_campo+'_'+i).style.display = '';
                    
            }else{
                
                document.getElementById('subtotal_'+id_campo+'_'+i).style.display = 'none';
            }

            i++;
            
        }else{
            
            if (i == 1) {
            	i++;
            } else {
              continua = false;
            }
        }
    }


    document.getElementById(id_listagem+'_div_visivel').style.display = ''; 
    document.getElementById(id_listagem+'_div_visivel_load').style.display = 'none';
    
    //setTimeout("alinhaDivDir('"+id_listagem+"_div_visivel')",1000);

}

function getWidth(){

	if(window.innerWidth){
		//alert('window.width='+window.innerWidth);
		return window.innerWidth; /* For non-IE */
	}
	if(document.documentElement.clientWidth){
		//alert('document.element='+document.documentElement.clientWidth);
		return document.documentElement.clientWidth; /* IE 6+ (Standards Compilant Mode) */
	}
	if(document.body.clientWidth){
		//alert('document.body='+document.body.clientWidth);
		return document.body.clientWidth; /* IE 4 Compatible */
	}
	if(window.screen.width){
		//alert('screen.width='+window.screen.width);
		return window.screen.width; /* Others (It is not browser window size, but screen size) */
	}
	
	return false;

}

function ListagemShowHide(event,id_listagem,id){

	//alinhaDivDir(id_listagem + '_div_visivel');
	 
	var y = getMouseY(event);
	//var x = getMouseX(event);
	
    if(document.getElementById(id_listagem+'_div_visivel').style.display == 'none'){
    	
    	id_interval_dir = setInterval("alinhaDivDir('"+id_listagem+"_div_visivel')",100);
    	//id_interval_dir2 = setInterval("alinhaDivDir('"+id_listagem+"_div_visivel_load')",100);
    	
    	document.getElementById(id_listagem+'_div_visivel').style.display = '';

        document.getElementById(id_listagem+'_div_visivel').style.marginTop = parseInt(y) - 200 + 'px';
        document.getElementById(id_listagem+'_div_visivel_load').style.marginTop = parseInt(y) -200 + 'px';
        
        //document.getElementById(id_listagem+'_div_visivel').style.marginLeft = '2%'; //parseInt(x) -260 + 'px';
        //document.getElementById(id_listagem+'_div_visivel_load').style.marginLeft = '2%'; //parseInt(x) -260 + 'px';
        
    	//document.getElementById(id).setAttribute("src","./images/icones/menosTransparente.gif");
        
    }else{
    	
    	//alert('limpou intervalo');
    	clearInterval(id_interval_dir);
    	//clearInterval(id_interval_dir2);
    	
    	document.getElementById(id_listagem+'_div_visivel').style.display = 'none';
    	//document.getElementById(id).setAttribute("src","./images/icones/maisTransparente.gif");
    	
    }
}

function ListagemSelecionachecks(check,qtdtotal,prefixo){
	
	for (var i=0;i<qtdtotal;i++)  {
		if(document.getElementById(prefixo+i)){
			document.getElementById(prefixo+i).checked=check;
		}
	}
}

//FUN��ES DA COMBO ESPECIAL.
function CE_pesquisar(idcodigo,idnome,funcao) {
    var nome = document.getElementById(idnome);
    var codigo = document.getElementById(idcodigo);
    var botao = document.getElementById("bt_pesquisar_" + idcodigo);
    var palavra = nome.value;
    botao.disabled = true;
    if (botao.value == 'Pesquisar') {
        if(palavra != ""){
            if(palavra.length>2){
                xajax_CE_pesquisar_combo(idcodigo,idnome,funcao,nome.value);
                document.getElementById('div_img_pesquisa_' +idcodigo).style.display = "inline";
            }else{
            	criaAlerta("Digite no m�nimo 3 caracteres para procurar.");
            }
        }else{
        	criaAlerta("Digite no m�nimo 3 caracteres para procurar.");
            document.getElementById('result_pesq_' + idcodigo).innerHTML = "";
        }
    } else {
        CE_bloquear(false,idcodigo,idnome);
    }
}
function CE_selecionar(selecao,idcodigo,idnome){
    var nome = document.getElementById(idnome);
    var codigo = document.getElementById(idcodigo);
    var botao = document.getElementById("bt_pesquisar_" + idcodigo);
    var funcao_selecionar = document.getElementById(idcodigo + "_funcao_selecionar");
    var funcao_limpar = document.getElementById(idcodigo + "_funcao_limpar");
    botao.disabled = false;
    if(selecao !="|"){
        var valor = selecao.split("|");
        codigo.value=valor[0];
        nome.value=valor[1];
        CE_bloquear(true,idcodigo,idnome);
        if (funcao_selecionar.value != "") {
        	eval(funcao_selecionar.value);
        }
        document.getElementById('result_pesq_' + idcodigo).innerHTML = "";
    }else{
        return false;
    }
}

function CE_bloquear(bloqueio,idcodigo,idnome) {
    var nome = document.getElementById(idnome);
    var codigo = document.getElementById(idcodigo);
    var botao = document.getElementById("bt_pesquisar_" + idcodigo);
    var funcao_limpar = document.getElementById(idcodigo + "_funcao_limpar");
    if (bloqueio) {
        nome.style.background = "#F0F0F0";
        nome.readOnly = true;
        botao.value = 'Limpar';
        botao.disabled = false;
    } else {
        nome.style.background = "#FFFFFF";
        codigo.value="";
        nome.value="";
        nome.readOnly = false;
        botao.value = 'Pesquisar'; 
        botao.disabled = false;
        if (funcao_limpar.value != "") {
        	eval(funcao_limpar.value);
        }
    }
}

// Fun��es e vari�veis utilizadas para cria��o do Help Comment
var help_offsetTop  = 0; 
var help_offsetLeft = 0;

var lastObj     = false;
var helpCommentDiv = false;

var MSIE = false;
if(navigator.userAgent.indexOf('MSIE')>=0 && navigator.userAgent.indexOf('Opera')<0)MSIE=true;

function mostrarHelpComment(inputObj,msg,lado,titulo){

    if(!helpCommentDiv){
    
        criarHelpComment(msg,inputObj,lado,titulo);

    }else{

    	if(helpCommentDiv.style.display=='block'){
        
            fecharHelpComment();

            if(lastObj == inputObj){

            	return false;
                
            }else{
                
            	criarHelpComment(msg,inputObj,lado,titulo);
            }
        }
    }
}
function escreveConteudo(msg,titulo){


    var calTable = document.createElement('TABLE');
    calTable.width = '100%';
    calTable.cellSpacing = '0';
    helpCommentDiv.appendChild(calTable);

    var calTBody = document.createElement('TBODY');
    calTable.appendChild(calTBody);

    if(titulo){

        var row = calTBody.insertRow(-1);
        
       // var cell = row.insertCell(-1);
        //cell.align = 'right';
        //cell.width = '15';
        //cell.className = 'cell-topo-mensagem';
        
        var cell = row.insertCell(-1);
        cell.innerHTML = titulo;
        cell.className = 'cell-topo-mensagem';
        
        //var cell = row.insertCell(-1);
        //cell.width = '15';
        //cell.align = 'right';
        //cell.innerHTML = '<img OnMouseOver="document.body.style.cursor=\'pointer\';" OnMouseOut="document.body.style.cursor=\'default\';" OnClick="fecharHelpComment(); document.body.style.cursor=\'default\';" src="images/fileclose.gif">';
        //cell.className = 'cell-topo-mensagem';
    }
    
    var row = calTBody.insertRow(-1);
    var cell = row.insertCell(-1);

    //if(titulo){
    //	cell.colSpan = '2';
    //}
    
    cell.innerHTML = msg
    cell.className = 'cell-mensagem';

}

function getTopPos(inputObj){

    var returnValue = inputObj.offsetTop + inputObj.offsetHeight;
    while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetTop;
    return returnValue + help_offsetTop;
}

function getleftPos(inputObj){

    var returnValue = inputObj.offsetLeft;
    while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetLeft;
    return returnValue + help_offsetLeft;
}

function fecharHelpComment(){

	if(document.getElementById('helpComment')){

		document.body.removeChild(helpCommentDiv);
        helpCommentDiv = false;
        document.body.removeChild(helpCommentDivSeta);
    }
}

function criarHelpComment(msg,inputObj,lado,titulo){

    lastObj = inputObj;
    
    // Configura��o do lado que vai ser desenhado
    if(lado == ''){
    	alert('Erro ao selecionar lado');
    	return false;
    }
    
    if(lado == 'D'){
    	
    	var divLeftPos = 32;
    	var divTopPos  = 21;
    	
    	var divSetaLeftPos = 20;
    	var divSetaTopPos  = 15;
    	
    	if(titulo){
    		var imgSeta = '<img src="images/hbseta_t.gif">';
    	}else{
    		var imgSeta = '<img src="images/hbseta.gif">';
    	}
    }
    
    if(lado == 'E'){
    	
    	if (MSIE == true){
    		
    		var divLeftPos = -314;
	    	var divTopPos  = 21 ;
	    	
    	}else{
    	
	    	var divLeftPos = -318;
	    	var divTopPos  = 21 ;
    	}
    	
    	var divSetaLeftPos = -15;
    	var divSetaTopPos  = 15 ;
    	
    	if(titulo){
    		divLeftPos = divLeftPos + 1;
    		var imgSeta = '<img src="images/hbseta2_t.gif">';
    	}else{
    		var imgSeta = '<img src="images/hbseta2.gif">';
    	}
    }
    
    // Div principal
    helpCommentDiv = document.createElement('DIV');
    helpCommentDiv.id = 'helpComment';
    helpCommentDiv.style.zIndex = 1000;

    document.body.appendChild(helpCommentDiv);

    helpCommentDiv.style.visibility = 'visible';   
    helpCommentDiv.style.display = 'block';
    helpCommentDiv.style.left = getleftPos(inputObj) + divLeftPos + 'px';
    helpCommentDiv.style.top = getTopPos(inputObj) - divTopPos + 'px';

    escreveConteudo(msg,titulo);

    // Div da seta
    helpCommentDivSeta = document.createElement('DIV');
    helpCommentDivSeta.id = 'helpCommentSeta';
    helpCommentDivSeta.style.zIndex = 2000;
    helpCommentDivSeta.innerHTML = imgSeta;

    document.body.appendChild(helpCommentDivSeta);

    helpCommentDivSeta.style.left = getleftPos(inputObj)  + divSetaLeftPos + 'px';
    helpCommentDivSeta.style.top = getTopPos(inputObj) - divSetaTopPos + 'px';
    
}
