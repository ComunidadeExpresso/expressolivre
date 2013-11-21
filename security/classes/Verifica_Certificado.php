<?php
require_once('Verifica_Certificado_conf.php');
require_once('funcoes_auxiliares.php');

class Verifica_Certificado 
{
	public $msgerro = '';
	public $revogado = false;
	public $status = false;
	public $certificado = '';
	public $CRL = '';
        public $erros_ssl = array();
	public $arquivos_para_deletar = array();
	
	public function __construct($parametro1,$parametro2)
	{	
		//include('Verifica_Certificado_conf.php');
	        # $parametro1 = array com dados do certificado... 
		# $parametro2 = certificado no formato PEM ...
		$this->status = false;
		if(!verificaopenssl())
		{
			$this->msgerro = 'MSG005i - Modulo openssl nao disponivel no PHP.';
			return false;
		}
		If(!$parametro2)
		 {
			$this->msgerro = 'MSG005 - Certificado nao informado.';
			return false;		
		 }
		$this->certificado = $parametro2; 
		//Pode existir mais de um local para obter a CRL. Vai tabalhar com o primeiro ....
		$aux = explode('/',$parametro1['CRLDISTRIBUTIONPOINTS'][0]);
		$this->CRL = $aux[count($aux)-1];	
		if(!$this->cmd_openssl_verify($this->certificado))
		 {
			$this->msgerro = 'MSG010 - Erro verificando Expiracao/CAs do certificado.';
			return false;		 
		 }
                if($GLOBALS['CRLs'] != '')
                 {
                  if($this->Testa_se_Certificado_Revogado(trim($GLOBALS['CRLs'].$this->CRL),$parametro1['SERIALNUMBER']))
                   {
                        $this->msgerro = 'MSG011 - Ocorreu erro validando o certificado.';
                        return false;
                   }
                 }
		$this->status = true;
	}
	
  public function __destruct()
  {
		#Remover arquivos temporarios.....	
		deleta_arquivos_temporarios(&$this->arquivos_para_deletar);
  }
  
   private function cmd_openssl_verify($ZXZ)
	{
		$this->erros_ssl = array();
		if(!is_file($GLOBALS['CAs']))
		{
			# Se arquivo com cas nao existe, assume certificado invalido..
			$this->erros_ssl[]= 'Autoridade certificadora desconhecida.(CA-01)';  //'Arquivo CAs n�o localizado.';
			return false;
		}
		$arq = gera_nome_arquivo_temporario(&$this->arquivos_para_deletar);
		if(!$arq)
		 {
			#Nao foi possivel gerar path(nome) para arquivo temporario...
			$this->erros_ssl[]= 'Autoridade certificadora desconhecida.(CA-02)';  //'Arquivo CAs n�o localizado.';
			return false;
		 }
		$ret = grava_arquivo($arq,$this->certificado);
		$saida = array();
		$w = exec('openssl verify -CAfile '.$GLOBALS['CAs'].' '.$arq,$saida);
		deleta_arquivos_temporarios(&$this->arquivos_para_deletar);
		$this->arquivos_para_deletar = array();
		//echo 'w= '.$w.'<br>';
		//echo '<pre>';
		//print_r($saida);
		//echo '</pre>';
		$aux = explode(' ',$w);	
		if($aux[1] != 'OK')   // OK no primeiro item do array significa que o comando executaou OK... mas tem erro no certificado...
		{
			foreach($saida as $item)
			{
			        $aux = explode(':',$item);
				if(isset($aux[1]))
				{
					$this->erros_ssl[] = trim($aux[1]);
				}
			}			
			return false;
		}
		return true;
	}
	
	private function Testa_se_Certificado_Revogado($pcrl,$serial)
	{
		$this->erros_ssl = array();
		if(!file_exists($pcrl))
			{
				# Se arquivo com crls nao existe, assume certificado revogado..
				$this->erros_ssl[]= 'Couldn\'t verify if certificate was revoked.(CD-01)';  //'Arquivo CRL nao localizado.';
				return true;
			}	
			if(!is_file($pcrl))
			{
				# Se nao for um arquivo, assume certificado revogado..
				$this->erros_ssl[]= 'Couldn\'t verify if certificate was revoked.(CD-01)';  //'Arquivo CRL nao localizado.';
				return true;
			}
		$crl = file_get_contents($pcrl,true);
		$cert_data = Crl_parseASN($crl);
                # testa se crl expirada....	
		if(gmdate('YmdHis') >= data_hora($cert_data[1][0][1][4][1]))
		{
			# Se crl expirada, assume certificado revogado..
			$this->erros_ssl[]= 'Couldn\'t verify if certificate was revoked.(CD-02)';  //'Arquivo CRL expirado.';
			return true;
		}
		if (!is_array($cert_data) || ($cert_data[0] == 'UNKNOWN')) 
		{
			# Se o primeiro parametro aponta uma crl invalida retorna como certificado revogado...
			$this->erros_ssl[]='Couldn\'t verify if certificate was revoked.(CD-03)'; //Arquivo CRL invalido.
			return true;
		}
		foreach($cert_data[1][0][1][5][1] as $item)
		{
			if($serial === $item[1][0][1])
			{
			#Certificado esta revogado mesmo !!!!! 
			$this->erros_ssl[]='REVOKED Certificate.';
			$this->revogado = true;
			return true;				
			}
		}
		return false;
	}	
 }	
?>
