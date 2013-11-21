<?php

class SqlParser {
    
    var $_sql;
    var $_bloqueios = array(
        "insert",
        "update",
        "delete",
        //" into [temp]", //DESSA FORMA A EXPRESSÃO INTO É BLOQUEADA MAS SE FOR ACOMPANHADA DA PALAVRA TEMP ENTÃO SERÁ LIBERADA.
        "drop ",
        "alter ",
        "database",
        "create ",
        "disable ",
        "begin",
        "rollback",
        "commit",
        "declare",
        " abort",
        "copy",
        "close",
        "execute",
        "grant",
        "revoke",
        "reset",
        "vacuum",
        "truncate",
        "cascade",
		" overlay",
		"prepare",
		"dblink",
		"dblink_exec");
    
    var $_erro = "";
	
    function SqlParser($sql) {
    	$this->setSql($sql); 
    }
    
    protected function procuraPalavra($texto,$palavra,$msgerro) {
        $achou = "";
        $palavratemchaves = stristr(strtolower($palavra),"[");
        if ($palavratemchaves) {
        	$liberado = str_replace(" ","",str_replace("]","",str_replace("[","",$palavra)));
        	$arr = explode("[",$palavra);
        	$bloqueado = $arr[0];
        	$txtsemespacos = strtolower(str_replace(" ","",$texto));

        	$achouliberado = stristr($txtsemespacos,$liberado);
        	$achoubloqueio = stristr($texto,$bloqueado);
        	
        	if ($achoubloqueio != "" && $achouliberado != "") {
        		$achou = "";
        	} else {
        		if ($achoubloqueio) {
        			$achou = $achoubloqueio;
        		}
        	}
        } else {
        	$achou = stristr(strtolower($texto),$palavra);	
        }
        $palavratemparenteses = stristr(strtolower($palavra),"(");
        
        if ($palavratemparenteses) {
        	$achou = stristr(strtolower(str_replace(" ","",$texto)),$palavra);
        }
        if ($achou!="") {
            throw new Exception($msgerro);
        }
    }
    
    public function bloquearPalavra($palavra,$bloquear = true) {
        if ($bloquear) {
    	   array_push($this->_bloqueios,$palavra);
        } else {
            $arr = array();
        	foreach($this->_bloqueios as $a) {
        		if ($a != $palavra) {
                    array_push($arr,$palavra);
                }
        	}
            $this->_bloqueios = $arr;
        }
    }
    
    function verificaSql() {
        $ret = false;
        try {
            $select = $this->_sql;
            foreach ($this->_bloqueios as $palavra) {
            	$arr = explode("[",$palavra);
            	$palavrabloqueada = $arr[0];
            	$this->procuraPalavra($select,$palavra,"Por questões de segurança, a palavra: '" . trim(strtoupper($palavrabloqueada)) . "' não é permitida.");
            }
           
            $ret = true;
            
        } catch (Exception $e) {
            $this->_erro = $e->getMessage();
            $ret = false;
        }
        return $ret;
    }
    
    function getErro() {
    	return $this->_erro;
    }
    
    public function setSql($value) {
        $this->_sql = $value;
    }
    
    public function getSql() {
    	return $this->_sql;
    }
    
    protected function getQuantidadeIfs($texto) {
        $qtd = 0;
        for ($i = 0;  $i <= strlen($texto); ++$i) {
            if (strtolower(substr($texto,$i,3)) == "#if") {
                $qtd = $qtd + 1;
            }
        }
        return $qtd;
    }
    
    protected function numerarCondicao($texto,$num) {
        $qtd = 0;
        for ($i = 0;  $i <= strlen($texto); ++$i) {
            if (strtolower(substr($texto,$i,3)) == "#if") {
                $qtd = $qtd + 1;
                $condicoesabertas = 1;
                $condicoesfechadas = 0;
                if ($qtd == $num) {
                    for ($j = $i+1;  $j <= strlen($texto); ++$j) {
                        
                        if ((strtolower(substr($texto,$j,5)) == "#/if#")) {
                            $condicoesfechadas = $condicoesfechadas + 1;
                        }
                        
                        if ((strtolower(substr($texto,$j,3)) == "#if")) {
                            $condicoesabertas = $condicoesabertas + 1;
                        }
    
                        if ($condicoesfechadas == $condicoesabertas) {
                            $condicao = substr($texto,$i,$j-$i+5);
                            $condicao = "#co$num " . substr($condicao,4,strlen($condicao)-9) . "#/co$num#";
                            $prefixo = substr($texto,0,$i);
                            $sufixo = substr($texto,$j+5,strlen($texto));
                            return $prefixo . $condicao . $sufixo;
                        }
                    }
                }
                
            } 
        }
    }
    
    protected function validarCondicao($texto,$num) {
        
        try {
        
            $inicond = stripos($texto,"#co$num");
            $endcond = stripos($texto,"#/co$num#");
            $condicao = substr($texto,$inicond,$endcond-$inicond+6);
            $conteudo = "";
            
            $fincond = stripos(substr($condicao,1,strlen($condicao)),"#");
            $condeval = substr($condicao,strlen("#co$num"),$fincond-strlen("#co$num")+1);  
            $condconcat = substr($condicao,$fincond+2,strlen($condicao)-$fincond-strlen("#/co$num#")-2);
            if ($condeval != "") {
                $fcondicao = ' if (' . trim($condeval) . ') { $ret = true; } else { $ret = false; }';
                
              //  echo $fcondicao . "<br>";
            
                if ($this->verificaSintaxe($fcondicao) === false) {
                    throw new Exception("Impossível validar condição: ( $condeval )");
                }
                
                
                eval($fcondicao);
                
                $prefixo = substr($texto,0,$inicond);
                $sufixo = substr($texto,$endcond+strlen("#/co$num#"),strlen($texto));
                
                if ($ret) {
                    $conteudo = $condconcat;
                }
                
                $novotexto = $prefixo . $conteudo . $sufixo;
                $ret = $novotexto;
            }
        
        } catch (Exception $e) {
            $this->_erro = $e->getMessage();
            $ret = false;
        }

        return $ret; 
    }
    
    function verificaCondicoes() {
        $ret = true;
        $texto = $this->getSql();
        $novotexto = $this->getSql();
        $qtdifs = $this->getQuantidadeIfs($texto);
        for ($i = $qtdifs;  $i >= 1; $i--) {
           $ftexto = $this->numerarCondicao($novotexto,$i);
           $novotexto = $ftexto;
        }
        for ($i = $qtdifs;  $i >= 1; $i--) {
           $novotexto = $this->validarCondicao($novotexto,$i);
           if ($novotexto == false) {
           	  $ret = false;
           }
        }
        $this->setSql($novotexto);

        return $ret;
    }
    
    protected function verificaSintaxe($code) {
        return @eval('return true;' . $code);
    }
    
}

?>