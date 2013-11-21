/**
 * Função:     submeter(acao, prefixo)
 * Data:       12/12/2008
 * Descrição:  Função utilizada para validar os campos obrigatórios de um formulário
 			Foi criada no intuito de agiizar o desenvolvimento de formulários de pesquisa
 			Exige que os campos possuam um prefixo passado por parametro e o prefixo deve possuir 4 caracteres
 			Exige que o formulario possua o nome frm
 			Trabalha validando o preenchimento dos campos onde há o prefixo passado 
 			e submete o frm em caso de sucesso, caso contrario alerta e muda a cor de fundo dos campos
 * Chamada:	onClick="submeter('pesquisar','pesq');"
 *     
 */
 
 
 /**
 * Criado em: 04/03/2009
 * 
 *	
 * 	Função. valida(form,acao,submete,obrigatorio);
 * 
 * 	@param form = objeto forumlario que deseja validar;
 * 	@param acao = valor que deseja que seja setado no input de ação
 * 	@param submete = valor (true ou false) para dizer se o script vai submeter o formulario
 * 	@param obrigatorio = campo opcional que altera o default de 'nao obrigatorios' com o valor (_not)
 * 						para 'obrigatorios' com o valor passado
 * 
 * 
 * Script tem como funcionalidade validar todos os inputs do formulario
 * 
 *  - Compativel com: Firefox 3, Internet Explorer 7, Konqueror 3.5, Chrome 1.0, Opera 9
 * 
 * 	NOTAS IMPORTANTES
 * 
 * 	- para o script identificar a acao do form, o input tipo hidden 'acao' deve possuir o um id com o prefixo do form
 *    em que ele está, da seguinte maneira 'id_form'+'_acao'. Ex: <input type="hidden" id="frm1_acao" name="frm1_acao"> ;
 * 
 *  - para o script identificar os blocos de checkboxes, os checkboxes devem estar dentro de um <div> identificado da seguinte forma:
 * 	  o id do div deve ser composto de 'id_form'+_div_ckbox+'id_qualquer'. Ex: <div id="frm1_div_ckbox_10">
 * 	  os checkboxes devem estar dentro do div da seguinte maneira:
 * 	  Ex: <div id="frm1_div_ckbox_10">
 * 			<input type="checkbox" id="id_qualquer" value="1">
 * 			<input type="checkbox" id="id_qualquer2" value="2">
 * 			<input type="checkbox" id="id_qualquer3" value="3">
 *		  </div> 
 *
 *   - para o script identificar os blocos de radio, os radios devem estar dentro de um <div> identificado da seguinte forma:
 * 	  o id do div deve ser composto de 'id_form'+_div_radio+'id_qualquer'. Ex: <div id="frm1_div_radio_11">
 * 	  os checkboxes devem estar dentro do div da seguinte maneira:
 * 	  Ex: <div id="frm1_div_radio_11">
 * 			<input type="radio" name="rad1" id="id_qualquer1" value="1" >
 * 			<input type="radio" name="rad1" id="id_qualquer2" value="2">
 * 			<input type="radio" name="rad1" id="id_qualquer3" value="3">
 *		  </div> 
 * 
 * 
 * 
 * 
 */


function submeter(acao, prefixo)
{
    var pode_enviar = true;
    var achou = false;
    for ( var i = 0; i < document.frm.elements.length; i++ ) {
        obj = document.frm.elements[i];
        if (obj.name.substring(0,4) == prefixo && obj.getAttribute('obrigatorio') == 1) {
            if (obj.type == 'text' || obj.type == 'textarea' || obj.type == 'select') {
                obj.style.background = "#FFFFFF";
                if (obj.value == '') {
                    obj.style.background = "#FDF3D9";
                    
                    pode_enviar = false;
                }
            }
        }
    }
    if (pode_enviar == true) {
        $id('acao').value = acao;
        $id('frm').submit();
    } else {
        cria_msg_html('div_msg','Existem campos obrigatórios não preenchidos.');
        return;
    }
}

/* funcao de validar */ 
function valida(form,acao,submete,obrigatorio){
   
    /* declaração de variaveis */
    var erro = 0;
    var x = 0 ;
    var debug = 1;
    
    ckbox_a_lista = new Array ;
    ckbox_a_div = new Array;
    ckbox_a_error = new Array;
    
    /* variavel que seta o sufixo para nao obrigatorio */
    var n_obri = 'not_';
    
    limpaCores();
    
    / * alerta de erro se não tiver form informado */
      if(form.length == 0){
        alert('ERRO: form inválido informado');
        return false;
    }
    
    for (i=0; i<form.elements.length ;i++){
    
        /* todos os checkboxes de listagem */
        if(form.elements[i].type == 'checkbox' || form.elements[i].type == 'radio'){
          
            if( form.elements[i].className.substr(0,11) == 'obrigatorio'){
              
                if(!ckbox_a_lista[form.elements[i].name.substr(0,form.elements[i].name.length-2)]){
	            
                    ckbox_a_lista[form.elements[i].name.substr(0,form.elements[i].name.length-2)] = 0;
	               
	            }
                
                if(document.getElementById( form.elements[i].id ).checked == true){
                
                    ckbox_a_lista[form.elements[i].name.substr(0,form.elements[i].name.length-2)] = 1;
                   
                }

            }
              
        }
           
        /* todos os inputs text */
        if(form.elements[i].type == 'text'){
          
            if(debug == 1){
                / * alerta de erro: se o elemento nao possuir um id ele grita * /
                if(!form.elements[i].id){
                    alert('Para validação input '+form.elements[i].name+' precisa possuir um id');
                }
            }
              
            /*so valida se tiver id*/
            if(form.elements[i].id){
                /* verificando se o parametro de string obrigatorio foi passado */
                if(obrigatorio){
                    /* verificando se é obrigatorio a partir da string de obrigatorio setada*/              
                    if( (form.elements[i].id.substr(0,obrigatorio.length)) == obrigatorio){
                          
                        if(form.elements[i].value.length < 1){
                            muda_cor(form.elements[i].id);
                            erro = 1;
                        }else{
                            remove_cor(form.elements[i].id);
                        }    
                    }
                               
                }else{

                    /* verificando se é obrigatorio */
                    if( (form.elements[i].id.substr(0,n_obri.length)) != n_obri){
                                              
                        if(form.elements[i].value.length < 1){
                            muda_cor(form.elements[i].id);
                            erro = 1;
                        }else{
                            remove_cor(form.elements[i].id);
                        }
                    }
                }
            }        
        } 
          

        /* todos os selects */
        if(form.elements[i].type == 'select-one'){
            
            if(debug == 1){
                / * alerta de erro: se o elemento nao possuir um id ele grita * /
                if(!form.elements[i].id){
                    alert('Para validação input '+form.elements[i].name+' precisa possuir um id');
                }
            }
            
            /*so valida se tiver id*/
            if(form.elements[i].id){
                  
                /* verificando se o parametro de string obrigatorio foi passado */
                if(obrigatorio){
                    
                    /* verificando se é obrigatorio a partir da string de obrigatorio setada*/      
                    if( (form.elements[i].id.substr(0,obrigatorio.length)) == obrigatorio){
                          
                        if(form.elements[i].value == 0 || form.elements[i].value == ""){
                            muda_cor(form.elements[i].id);
                            erro = 1;
                        }else{
                            remove_cor(form.elements[i].id);
                        }    
                    }
                               
                }else{
                    
                    /* verificando se é obrigatorio */
                    if( (form.elements[i].id.substr(0,n_obri.length)) != n_obri){
                        if(form.elements[i].value == 0 || form.elements[i].value == ""){
                            muda_cor(form.elements[i].id);
                            erro = 1;
                        }else{
                            remove_cor(form.elements[i].id);
                        }               
                    }
                }
            }
        }
          
        /* todos os inputs textarea */
        if(form.elements[i].type == 'textarea'){
            if(debug == 1){
                / * alerta de erro: se o elemento nao possuir um id ele grita * /
                if(!form.elements[i].id){
                    alert('Para validação input '+form.elements[i].name+' precisa possuir um id');
                }
            }
            
            /*so valida se tiver id*/
            if(form.elements[i].id){
                  
                if(obrigatorio){
                    
                    /* verificando se é obrigatorio a partir da string de obrigatorio setada*/              
                    if( (form.elements[i].id.substr(0,obrigatorio.length)) == obrigatorio){
                        
                        if(form.elements[i].value.length < 1){
                            muda_cor(form.elements[i].id);
                            erro = 1;
                        }else{
                            remove_cor(form.elements[i].id);
                        }    
                    }
                               
                }else{
                    
                    /* verificando se é obrigatorio */
                    if( (form.elements[i].id.substr(0,n_obri.length)) != n_obri){
                        if(form.elements[i].value.length < 1){
                            muda_cor(form.elements[i].id);
                            erro = 1;
                        }else{
                            remove_cor(form.elements[i].id);
                        }
                    }
                }
            }               
        }
    } 
      
     // olhá só isso
    for(i_a in ckbox_a_lista){
      
        //alert('KEY' + i_a + ' - VALOR ' + ckbox_a_lista[i_a]);
      
        if(ckbox_a_lista[i_a] == 0){
            
            g=0;
            while(document.getElementById('td_'+i_a+'_'+g)){
                muda_cor('td_'+i_a+'_'+g)
                g++;
            }
            erro = 1;
        
        }else{
        
            if(ckbox_a_lista[i_a] == 1){        
            
		        g=0;
	            while(document.getElementById('td_'+i_a+'_'+g)){
	                remove_cor('td_'+i_a+'_'+g)
	                g++;
	            }
            
            }
	        
        }
    }
      

    / * checkboxes e radios * /
      
    // capturando todos os divs
    ckbox_a_div = document.getElementsByTagName('div');
      
    for(i=0;i<ckbox_a_div.length;i++){
    
        / * checkboxes e radios* /
        
        /* verificando se são obrigatórios e se tem parametro de obrigatorio */
        if(obrigatorio){
    
            // verificando se é o div a ser checado
            if( ckbox_a_div[i].id.substr(0, (11 + form.id.length + obrigatorio.length )) == obrigatorio+"_"+form.id+"_div_ckbox" || ckbox_a_div[i].id.substr(0, (11 + form.id.length + obrigatorio.length )) == obrigatorio+"_"+form.id+"_div_radio") {
                
                if((ckbox_a_div[i].id.substr(0,obrigatorio.length)) == obrigatorio){
                            
                    /* verificando se os checkboxes são do mesmo form */    
                    if( ckbox_a_div[i].id.substr((obrigatorio.length+1),form.id.length) == form.id){
                            
                        ckbox_a_child = document.getElementById(ckbox_a_div[i].id).childNodes;
                        ckbox_a_error[ckbox_a_div[i].id] = 0;
                              
                            for(j=0;j<ckbox_a_child.length;j++){
                                  
                                if(ckbox_a_child[j].nodeName == "INPUT"){
                                  
                                    //CHECKBOX
                                    if(ckbox_a_child[j].getAttribute("type") == "checkbox"){
                                        if(document.getElementById(ckbox_a_child[j].id).checked == true){
                                            ckbox_a_error[ckbox_a_div[i].id] = 1;
                                            break;            
                                        }    
                                    }
                                    
                                    //RADIO BUTTON
                                    if(ckbox_a_child[j].getAttribute("type") == "radio"){
                                        if(document.getElementById(ckbox_a_child[j].id).checked == true){
                                            ckbox_a_error[ckbox_a_div[i].id] = 1;
                                            break;            
                                        }    
                                    }
                                      
                                }else{
                                  
                                    /* Se o CHILD não for do tipo INPUT, ele busca RECURSIVAMENTE ate achar o INPUT */
                                    if(ckbox_a_child[j].nodeName == "TABLE"){
                                           
                                        if(ckbox_a_child[j].hasChildNodes()){
                                                
                                            for(t=0;t<ckbox_a_child[j].childNodes.length;t++){
                                                    
                                                if(ckbox_a_child[j].childNodes[t].nodeName == "TBODY"){
                                                    
                                                    for(q=0;q<ckbox_a_child[j].childNodes[t].childNodes.length;q++){
                                                        
                                                        if(ckbox_a_child[j].childNodes[t].childNodes[q].nodeName == "TR"){
                                                                    
                                                            for(w=0;w<ckbox_a_child[j].childNodes[t].childNodes[q].childNodes.length;w++){
                                                                    
                                                                if(ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].nodeName == "TD"){
                                                                        
                                                                    for(s=0;s<ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].childNodes.length;s++){
                                                                           
                                                                        if(ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].childNodes[s].nodeName == "INPUT"){
                                                                           
                                                                            a_tmp = ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].childNodes[s];  
                                                                               
                                                                            //CHECKBOX E RADIO
                                                                            if(a_tmp.getAttribute("type") == "checkbox" || a_tmp.getAttribute("type") == "radio"){
                                                                                   
                                                                                if(document.getElementById(a_tmp.id).checked == true){
                                                                                    ckbox_a_error[ckbox_a_div[i].id] = 1;
                                                                                }   
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }                          
                                    }
                                }
                            }
                              
                            if(ckbox_a_error[ckbox_a_div[i].id] == 0){
                                muda_cor(ckbox_a_div[i].id)
                                erro = 1;
                            }else{
                                remove_cor(ckbox_a_div[i].id);
                            }
                            
                            x ++ ;
                          
                        }
                    }
                }
                
            }else{
                
                if( ckbox_a_div[i].id.substr(0, (10 + form.id.length )) == form.name+"_div_ckbox" || ckbox_a_div[i].id.substr(0, (10 + form.id.length  )) == form.name+"_div_radio") {
                
                    if((ckbox_a_div[i].id.substr(0,n_obri.length)) != n_obri){
                        
                        /* verificando se os checkboxes são do mesmo form */    
                        if( ckbox_a_div[i].id.substr(0,form.id.length) == form.id){
                            
                            ckbox_a_child = document.getElementById(ckbox_a_div[i].id).childNodes;
                            ckbox_a_error[ckbox_a_div[i].id] = 0;
                            
                            for(j=0;j<ckbox_a_child.length;j++){
                              
                                if(ckbox_a_child[j].nodeName == "INPUT"){
                                    
                                    //CHECKBOX
                                    if(ckbox_a_child[j].getAttribute("type") == "checkbox" || ckbox_a_child[j].getAttribute("type") == "radio"){
                                        
                                        if(document.getElementById(ckbox_a_child[j].id).checked == true){
                                            ckbox_a_error[ckbox_a_div[i].id] = 1;
                                            break;          
                                        }   
                                    }
                                    /*  //RADIO BUTTON
                                    if(ckbox_a_child[j].getAttribute("type") == "radio"){
                                        if(document.getElementById(ckbox_a_child[j].id).checked == true){
                                            ckbox_a_error[ckbox_a_div[i].id] = 1;
                                            break;          
                                        }   
                                    }*/
                                    
                                }else{
                                  
                                    if(ckbox_a_child[j].nodeName == "TABLE"){
                                           
                                        if(ckbox_a_child[j].hasChildNodes()){
                                            
                                            for(t=0;t<ckbox_a_child[j].childNodes.length;t++){
                                                
                                                if(ckbox_a_child[j].childNodes[t].nodeName == "TBODY"){
                                                
                                                    for(q=0;q<ckbox_a_child[j].childNodes[t].childNodes.length;q++){
                                                    
                                                        if(ckbox_a_child[j].childNodes[t].childNodes[q].nodeName == "TR"){
                                                                
                                                            for(w=0;w<ckbox_a_child[j].childNodes[t].childNodes[q].childNodes.length;w++){
                                                                
                                                                if(ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].nodeName == "TD"){
                                                                    
                                                                    for(s=0;s<ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].childNodes.length;s++){
                                                                       
                                                                        if(ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].childNodes[s].nodeName == "INPUT"){
                                                                       
                                                                            a_tmp = ckbox_a_child[j].childNodes[t].childNodes[q].childNodes[w].childNodes[s];  
                                                                           
                                                                            //CHECKBOX E RADIO
                                                                            if(a_tmp.getAttribute("type") == "checkbox" || a_tmp.getAttribute("type") == "radio"){
                                                                               
                                                                                if(document.getElementById(a_tmp.id).checked == true){
                                                                                    ckbox_a_error[ckbox_a_div[i].id] = 1;
                                                                                }   
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }                       
                                    }
                                }
                            }

                            if(ckbox_a_error[ckbox_a_div[i].id] == 0){
                                muda_cor(ckbox_a_div[i].id)
                                erro = 1;
                            }else{
                                remove_cor(ckbox_a_div[i].id);
                            }
                            
                            x ++ ;
                            
                        }
                    }
                }
            }
        }
      
        if(erro == 1){
          
            if(!document.getElementById("mess")){
                criarDiv('mess', '<table height=\"32\" width=\"100%\" valign=\"middle\"><tr onclick=\"removeDiv(\'mess\');\"><td class="\msg\" width=\"96%\" heigth=\"100%\">&nbsp;<font color=\"#CD0000\">Existem campos obrigatórios não preenchidos!</font></td><td width=\"4%\"><img width=\"15\" height=\"15\" src=\"images/X.jpg\"></img></td></tr></table>', '100%', '34', '0', '0');
                alinhaDiv('mess');
            }
          
            id_interval = setInterval("alinhaDiv('mess')",500);
                    
            fade(0,'mess',80);
            
            return false;
          
        }else{
          
            if(document.getElementById("mess")){
                removeDiv('mess');
            }
          
            / * alerta de erro se nao encontrar input de acao * /
            if(document.getElementById(form.id+'_acao').length == 0){
                alert('ERRO: erro ao definir ação. Input '+form.id+'_acao não existe');
            }
          
            document.getElementById(form.id+'_acao').value = acao;
          
            /* se o parametro for true é para submeter o form */
            if(submete == true || submete == 1){
                form.submit();
            }else{
                return true;
            }
        }
}

function diferencaEntreDatas(dataMaior,dataMenor) {
    Data = dataMaior.replace( " ", "" );
    dia1 = Data.substring( 0, 2 );
    dia1 = parseInt(dia1,10)+0;
    mes1 = Data.substring(3,5);
    mes1 = parseInt(mes1,10)-1;
    ano1 = Data.substring(6,10);
    ano1 = parseInt(ano1,10)+0;

    Data = dataMenor.replace( " ", "" );
    dia2 = Data.substring( 0, 2 );
    dia2 = parseInt(dia2,10)+0;
    mes2 = Data.substring(3,5);
    mes2 = parseInt(mes2,10)-1;
    ano2 = Data.substring(6,10);
    ano2 = parseInt(ano2,10)+0;

    return ( (Date.UTC(ano1,mes1,dia1,0,0,0)-Date.UTC(ano2,mes2,dia2,0,0,0)) / 86400000);
}


function validarPeriodo(dataInicial, dataFinal) {
    diferenca = diferencaEntreDatas(dataFinal.value,dataInicial.value); 
    var array_tmp = dataInicial.value.split('/'); 
    var dia_ini = array_tmp[0]; 
    var mes_ini = array_tmp[1]; 
    var ano_ini = parseInt(array_tmp[2]); 
    
    if (dataFinal.value.length == 10) {
        array_tmp = dataFinal.value.split('/'); 
        var dia_fim = array_tmp[0]; 
        var mes_fim = array_tmp[1]; 
        var ano_fim = parseInt(array_tmp[2]); 
        // diferença de um ano 
        var ano = diferencaEntreDatas(dataFinal.value,dia_fim+'/'+mes_fim+'/'+(ano_fim-1)); 
    
        if((ano_ini!=ano_fim && ano_fim<ano_ini) || (ano_ini==ano_fim && mes_ini>mes_fim) || (ano_ini==ano_fim && mes_ini==mes_fim && dia_ini>dia_fim)){
            dataFinal.value=""; 
            return false;
        }
    }
    return true;
}