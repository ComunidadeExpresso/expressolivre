<?php
require_once('funcoes_auxiliares.php');
require_once('Verifica_Certificado_conf.php');
require_once('Verifica_Certificado.php');

//  Pre-requisito: A extensao openssl deve estar disponivel no PHP ....
class certificadoB
{
  public $dados = array();        # Area para armazenar os dados recuperados do certificado.
  public $apresentado = false;    # Deve ser testado para verificar se no certificado processado foi localizado o CPF
  public $erros_ssl = array();
  public $cert_assinante = '';
  public $msg_sem_assinatura = '';  #conteï¿½do da mensagem sem assinatura, que retorna da funï¿½ï¿½o verify do openssl
  public $arquivos_para_deletar = array();

  public function __construct()
  {
    if(!extension_loaded('openssl'))
    {
      #PHP sem suporte ao openssl.....
      return false;
    }
    $this->dados = array();
    $this->dados_xml = '';
    $this->apresentado = false;
    $this->erros_ssl = array();
    $this->cert_assinante = '';
    $this->msg_sem_assinatura = '';
    $this->arquivos_para_deletar = array();
  }

public function __destruct()
  {
    #Remover arquivos temporarios.....
    deleta_arquivos_temporarios($this->arquivos_para_deletar);
  }

# Recupera dados de um certificado no formato pem
  function certificado($certificado_pem)
  {
      if (!$certificado_pem)
    {
        $this->apresentado = False;
        return False;    # Sem parametro ...
    }
   $this->dados = recupera_dados_do_ceritificado_digital($certificado_pem);
   $this->dados_xml = gera_xml_com_dados_do_certificado($this->dados);
     # Certificado foi processado, as informacoes obtidas estao em $this->dados.
  $this->apresentado = true;
  }

# Encriptar senha ......
	public function encriptar_senha($s,$c)
	{
		if($s == '')	return false;
		if($c == '')	return false;
		$senha_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar);
		//echo $senha_arquivo_temporario.'<br>';
		if(!grava_arquivo($senha_arquivo_temporario,$s))
		{
			return false;
		}
		$cert_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar);
		//echo $cert_arquivo_temporario.'<br>';
		if(!grava_arquivo($cert_arquivo_temporario,$c))
		{
			return false;
		}
		$senha_criptografada_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar);
		//echo $senha_criptografada_arquivo_temporario.'<br>';

		$w = exec('openssl rsautl -in ' . $senha_arquivo_temporario . ' -out ' . $senha_criptografada_arquivo_temporario . ' -inkey ' . $cert_arquivo_temporario . ' -certin -pkcs -keyform PEM -encrypt',$saida);

		if(!file_exists($senha_criptografada_arquivo_temporario))
		{
			deleta_arquivos_temporarios($this->arquivos_para_deletar);
			$this->arquivos_para_deletar = array();
			return false;
		}

                # Recupera a senha criptada, binario.....
		$retorno = file_get_contents($senha_criptografada_arquivo_temporario);

		deleta_arquivos_temporarios($this->arquivos_para_deletar);
		$this->arquivos_para_deletar = array();

		if(strlen($retorno) != 128) return false;

		return base64_encode($retorno);
	}


# Encriptar uma msg($m) para o(s) destinatario(s) $c(array com certificados)com os headers passados em $h.
	public function encriptar($m,$c,$h)
	{
		if($m == '')	return false;
		if(!is_array($c))	return false;
		$aux = count($c);
		if($aux < 1)	return false;
		if(!is_array($h) )	return false;
		# Tem de verificar todos os certificados que serao utilizados para criptografar a msg..
		# Inserir a rotina aqui....
		$m_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar);
		//echo $m_arquivo_temporario.'<br>';
		if(!grava_arquivo($m_arquivo_temporario,$m))
		{
			return false;
		}
		$enc_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar);
		//echo $enc_arquivo_temporario.'<br>';

		// LIMPA ERROS ... Pode ser um problema para outras aplicacoes que usam openssl(fonte de erros unica).
		while ($erro = openssl_error_string()); //  Limpa buffer de erros anteriores......
		$this->erros_ssl = array();
		$resultado = openssl_pkcs7_encrypt($m_arquivo_temporario,$enc_arquivo_temporario,$c,$h,$flags=0,$cipherid=OPENSSL_CIPHER_DES);
		//$resultado = openssl_pkcs7_encrypt($m_arquivo_temporario,$enc_arquivo_temporario,$c,$h);
		if(!$resultado)
		{
			// Guarda msgs de erro ...
			while ($erro = openssl_error_string())
			{
				$this->erros_ssl[] = $erro;
			}
			deleta_arquivos_temporarios($this->arquivos_para_deletar);
			$this->arquivos_para_deletar = array();
			return false;
		}
                # Recupera a msg criptada......
		$retorno = file_get_contents($enc_arquivo_temporario);
		deleta_arquivos_temporarios($this->arquivos_para_deletar);
		$this->arquivos_para_deletar = array();
		return $retorno;
	}


# Verifica uma msg($m) assinada...
	public function verificar($m)
	{
		if($m == '')
                {
                    $this->erros_ssl[] = 'Não foi possível verificar a assinatura gerada. Contate o administrador';
                    return false;
                }
		if(!$m_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar))
                    {
                        $this->erros_ssl[] = 'Não foi possível verificar a assinatura gerada. Contate o administrador';
                        $this->arquivos_para_deletar = array();
                        return false;
                    }
		if(!grava_arquivo($m_arquivo_temporario,$m))
		{
                        $this->erros_ssl[] = 'Não foi possível verificar a assinatura gerada. Contate o administrador';
			deleta_arquivos_temporarios($this->arquivos_para_deletar);
			$this->arquivos_para_deletar = array();
			return false;
		}
		if(!$vrf_cert_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar))
                    {
                        $this->erros_ssl[] = 'Não foi possível verificar a assinatura gerada. Contate o administrador';
                        $this->arquivos_para_deletar = array();
                        return false;
                    }
		if(!$vrf_msg_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar))
                    {
                        $this->erros_ssl[] = 'Não foi possível verificar a assinatura gerada. Contate o administrador';
                        $this->arquivos_para_deletar = array();
                        return false;
                    }
                $this->erros_ssl = array();
		while ($erro = openssl_error_string()); //  Limpa buffer de erros anteriores......
                $resultado = openssl_pkcs7_verify($m_arquivo_temporario,0, $vrf_cert_arquivo_temporario,array($GLOBALS['CAs']),$GLOBALS['CAs'],$vrf_msg_arquivo_temporario);
		if($resultado === -1)
		{
                    $this->erros_ssl[] = 'Erro verificando assinatura. Contate o administrador';
                    while ($erro = openssl_error_string())
			{
				$this->erros_ssl[] = $erro;
			}
                    $retorno = false;
                }
                if($resultado === False)
                    {
                        while ($erro = openssl_error_string())
                            {
                                if(substr($erro, 0,20) === 'error:21075075:PKCS7') 
                                    {
                                        $this->erros_ssl = array();
                                        while ($erro = openssl_error_string()); //  Limpa buffer de erros anteriores......
                                        $resultado = openssl_pkcs7_verify($m_arquivo_temporario,PKCS7_NOVERIFY, $vrf_cert_arquivo_temporario,array($GLOBALS['CAs']),$GLOBALS['CAs'],$vrf_msg_arquivo_temporario);
                                        break;
                                    }
                                $this->erros_ssl[] = $erro;
                            }
                    }
	        $retorno = true;

                if($resultado === False)
		{
		        # Indica ocorrencia de erro ...
                        $this->erros_ssl[] = 'Erro. ';
		        $retorno = false;
			// Guarda msgs de erro ...
			while ($erro = openssl_error_string())
			{
				$this->erros_ssl[] = $erro;
			}
			If(file_exists($vrf_msg_arquivo_temporario))
				{
					$this->msg_sem_assinatura =  file_get_contents($vrf_msg_arquivo_temporario);
				}
			else
				{
					$this->msg_sem_assinatura =  'Nao pode exibir a msg(CD-1)';
                                        $this->erros_ssl[] = 'Nao pode exibir a msg(CD-1)';
				}
			# Reexecuta o comando com um nivel menor de criticas.
			$this->cert_assinante = $this->extrai_certificado_da_msg_assinada($m);
			if(!$this->cert_assinante)
			{
				# Se nao foi possivel obter o certificado retorna com falso .....
				If(file_exists($vrf_msg_arquivo_temporario))
					{
						$this->msg_sem_assinatura =  file_get_contents($vrf_msg_arquivo_temporario);
					}
				else
					{
						$this->msg_sem_assinatura =  'Nao pode exibir a msg(CD-2)';
                                                $this->erros_ssl[] = 'Nao pode exibir a msg(CD-2)';
					}
				deleta_arquivos_temporarios($this->arquivos_para_deletar);
				$this->arquivos_para_deletar = array();
				return false;
			}
		}
		else
		{
			# Guarda o certificado usado para assinar a msg
			$this->cert_assinante  =   file_get_contents($vrf_cert_arquivo_temporario);
                 }
		If(file_exists($vrf_msg_arquivo_temporario))  $this->msg_sem_assinatura =  file_get_contents($vrf_msg_arquivo_temporario);
		$this->certificado($this->cert_assinante);
		if (!$this->apresentado)
		{
			$this->erros_ssl[] = 'Certificado da Msg nao pode ser tratado.';
			$retorno = false;
		}
		else
		{
			while ($erro = openssl_error_string()); //  Limpa buffer de erros anteriores......
			# Certificado poderia ter assinado um email??
			if(!($this->dados['KEYUSAGE']['digitalSignature']))
			{
				$this->erros_ssl[] = 'Certificado nao poderia ter sido utilizado para assinar email.';
				while ($erro = openssl_error_string())
					{
						$this->erros_ssl[] = $erro;
					}
				$retorno = false;
			}
			$revogado = new Verifica_Certificado($this->dados,$this->cert_assinante);
			$this->dados['REVOGADO'] = false;
  			if(!$revogado->status){
   				$this->erros_ssl[] = $revogado->msgerro;
				foreach ($revogado->erros_ssl as $item)
					{
						$this->erros_ssl[] = $item;
					}
				if ($revogado->revogado)
					$this->dados['REVOGADO'] = true;
   				$retorno  = false;
   			}
		}
		deleta_arquivos_temporarios($this->arquivos_para_deletar);
		$this->arquivos_para_deletar = array();
		return $retorno;
	}

	public function extrai_certificado_da_msg_assinada($m)
	{
	        $retorno = false;
	        if(!$m)
		{
			return $retorno;
		}
		$m_arquivo_temporario = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar);
		if(!grava_arquivo($m_arquivo_temporario,$m))
		{
			deleta_arquivos_temporarios($this->arquivos_para_deletar);
			$this->arquivos_para_deletar = array();
			return $retorno;
		}
		$w='';
		$saida = array();
		$w = exec('cat ' . $m_arquivo_temporario . ' | openssl smime -pk7out | openssl pkcs7 -print_certs',$saida);
		if(!$w=='')
		{
			deleta_arquivos_temporarios($this->arquivos_para_deletar);
			$this->arquivos_para_deletar = array();
			return $retorno;
		}
		$aux1 = '';
                // gera uma unica string com o conteudo retornado pelo comando...
		foreach($saida as $linha)
		{
			$aux1 .= $linha.chr(0x0A);
		}
                // cria um array com os certificados retornados..
		$aux2 = explode('-----BEGIN CERTIFICATE-----',$aux1);
		array_shift($aux2);
		// isolando certificados..
		$aux5 = array();
		foreach($aux2 as $item)
		{
			$aux3 = explode('-----END CERTIFICATE-----',$item);
			$aux4 = '-----BEGIN CERTIFICATE-----' . $aux3[0] . '-----END CERTIFICATE-----';
			$aux5[] = $aux4;
		}
		// Testa qual dos certificados nao he uma CA ....
		foreach($aux5 as $item)
		{
			$Dados_cert = recupera_dados_do_ceritificado_digital($item);
			if(!$Dados_cert['CA'])
				{
					$retorno = $item;
					break;
				}
		}
		deleta_arquivos_temporarios($this->arquivos_para_deletar);
		$this->arquivos_para_deletar = array();
		return $retorno;
	}
}
?>
