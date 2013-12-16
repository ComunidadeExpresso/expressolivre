<?php
/***************************************************************************************\
* Export EML Format Message Mail														*
* Written by Nilton Neto (Celepar) <niltonneto@celepar.pr.gov.br>						*
* ------------------------------------------------------------------------------------	*
*  This program is free software; you can redistribute it and/or modify it				*
*   under the terms of the GNU General Public License as published by the				*
*  Free Software Foundation; either version 2 of the License, or (at your				*
*  option) any later version.															*
\****************************************************************************************/
// BEGIN CLASS
class ExportEml
{
	var $msg;
	var $folder;
	var $mbox_stream;
	var $tempDir;

	function ExportEml() {
	   
		//TODO: modificar o caminho hardcodificado '/tmp' para o definido na configuracao do expresso
		//$this->tempDir = $GLOBALS['phpgw_info']['server']['temp_dir'];
		$this->tempDir = '/tmp';
	}
	
	function connectImap(){
	
		$username = $_SESSION['phpgw_info']['expressomail']['user']['userid'];
		$password = $_SESSION['phpgw_info']['expressomail']['user']['passwd'];
		$imap_server = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
		$imap_port 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapPort'];
		
		if ($_SESSION['phpgw_info']['expressomail']['email_server']['imapTLSEncryption'] == 'yes')
		{
			$imap_options = '/tls/novalidate-cert';
		}
		else
		{
			$imap_options = '/notls/novalidate-cert';
		}
		$this->mbox_stream = imap_open("{".$imap_server.":".$imap_port.$imap_options."}".$this->folder, $username, $password);
	}
	
	//export message to EML Format
	function parseEml($header, $body)	
	{		
		$sEmailHeader = $header;
		$sEmailBody = $body;
		$sEMail = $sEmailHeader . "\r\n\r\n" . $sEmailBody;		
		return $sEMail;
	}

	// create EML File.
	// Funcao alterada para tratar a exportacao
	// de mensagens arquivadas localmente.
	// Rommel Cysne (rommel.cysne@serpro.gov.br)
	// em 17/12/2008.

    function createFileEml_by_localFolder($sEMLData, $tempDir, $file){ 

        $file = "{$file}.eml";

        $f = fopen($tempDir.'/'.$file,"w");
        if(!$f)
            return False;

        fputs($f,$sEMLData);
        fclose($f);
        
        return $file;
    }

	function createFileEml($sEMLData, $tempDir, $id, $subject=false, $i=false)
    {
        if($id)
        {
            $header    = imap_headerinfo($this->mbox_stream, imap_msgno($this->mbox_stream, $id), 80, 255);
            $subject = $this->decode_subject($header->fetchsubject);
			
            if (strlen($subject) > 60)
                $subject = substr($subject, 0, 59);
 
			//$subject = preg_replace('/\//', '\'', $subject);
			$from = "áàâãäéèêëíìîïóòôõöúùûüç?\"!@#$%¨&*()-=+´`[]{}~^,<>;:/?\\|¹²³£¢¬§ªº° .ÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ";
			$to =   "aaaaaeeeeiiiiooooouuuuc______________________________________________AAAAAEEEEIIIIOOOOOUUUUC";
			$subject = strtr($subject,$from,$to);

			$subject = preg_replace('/[^a-zA-Z0-9_]/i', '_', $subject); 
			$file = $subject."_".$id.".eml"; 
		} else{
			// Se mensagem for arquivada localmente, $subject (assunto da mensagem)
			// sera passado para compor o nome do arquivo .eml;

			if($subject && $i){
				$from = "áàâãäéèêëíìîïóòôõöúùûüç?\"!@#$%¨&*()-=+´`[]{}~^,<>;:/?\\|¹²³£¢¬§ªº° .ÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ";
				$to =   "aaaaaeeeeiiiiooooouuuuc______________________________________________AAAAAEEEEIIIIOOOOOUUUUC";
				$subject = strtr($subject,$from,$to);

				$subject = preg_replace('/[^a-zA-Z0-9_]/i', '_', $subject); 

				// é necessário que a sessão faça parte do nome do arquivo para que o mesmo não venha vazio o.O 
				$file = $subject."_".$i."_".$_SESSION[ 'phpgw_session' ][ 'session_id' ].".eml";  
			} else{
				$file = "email_".$_SESSION[ 'phpgw_session' ][ 'session_id' ].".eml";
	        }    
        }
        
        $f = fopen($tempDir.'/'.$file,"w");
        if(!$f)
            return False;
        
        fputs($f,$sEMLData);
        fclose($f);
        
        return $file;
    } 

	function createFileZip($files, $tempDir){	

		$tmp_zip_filename = "email_".$_SESSION[ 'phpgw_session' ][ 'session_id' ].".zip";
                if (!empty($files))
                {
                    if (is_array($files))
                    {
                        $files_count = count($files);
                        for ($i=0; $i < $files_count; ++$i)
                        {
                            $files[$i] = escapeshellarg($files[$i]);
                        }
                        $files = implode(' ', $files);
                    }
                    else
                    {
                        $files = escapeshellcmd($files);
                    }
                }


$command = "cd " . escapeshellarg($tempDir) . " && nice zip -m9 " . escapeshellarg($tmp_zip_filename) . " " .  $files;


		if(!exec($command)) {
			$command = "cd " .  escapeshellarg($tempDir) . " && rm ".$files." ". escapeshellarg($tmp_zip_filename);
			exec($command);
			return null;
		}

		return $tmp_zip_filename;
				
	}


function export_all($params){

		$this->folder = $params['folder'];
		$this->folder = mb_convert_encoding($this->folder, "UTF7-IMAP","UTF-8");
		$fileNames = "";
		$tempDir = $this->tempDir;
		$this->connectImap();
		
		$msgs = imap_search($this->mbox_stream,"ALL",SE_UID);

		if($msgs){
			foreach($msgs as $nMsgs){

				$header	 	= $this-> getHeader($nMsgs);								 
				$body		= $this-> getBody($nMsgs);		

				$sEMLData 	= $this -> parseEml($header, $body);
				$fileName 	= $this -> CreateFileEml($sEMLData, $tempDir,$nMsgs);
				if(!$fileName)	{
					$error = True;					
					break;
				}
				else
					$fileNames .= "\"".$fileName."\" ";			
				
			}
			
			imap_close($this->mbox_stream);
			
			$nameFileZip = 'False';			
			if($fileNames && !$error) {			
				$nameFileZip = $this -> createFileZip($fileNames, $tempDir);
				if($nameFileZip)			
					$file = $tempDir.'/'.$nameFileZip;
				else {
					$file = false;
				}								
			}
			else 
				$file = false;
		}else{
			$file["empty_folder"] = true;
		}
		return $file;
		
	}

	// Funcao alterada para tratar a exportacao
	// de mensagens arquivadas localmente.
	// Rommel Cysne (rommel.cysne@serpro.gov.br)
	// em 17/12/2008.
	//  
	// Funcao alterada para que, quando houver  
	// apenas um arquivo a ser exportado, 
	// não seja criado em zip 
	//
	// Funcao altarada para exportar uma ou
	// varia mensagens de um pesquisa

	function makeAll($params) {
	//Exporta menssagens selecionadas na pesquisa
	if($params['folder'] === 'false'){
		
		$this->folder = $params['folder'];
		$error = False;
		$fileNames = "";
		
		$sel_msgs = explode(",", $params['msgs_to_export']);
		@reset($sel_msgs);
		$sorted_msgs = array();
		foreach($sel_msgs as $idx => $sel_msg) {
			$sel_msg = explode(";", $sel_msg);
			if(array_key_exists($sel_msg[0], $sorted_msgs)){
				$sorted_msgs[$sel_msg[0]] .= ",".$sel_msg[1];
			}
			else {
				$sorted_msgs[$sel_msg[0]] = $sel_msg[1];
			}
		}
			
		unset($sorted_msgs['']);			

		
		// Verifica se as n mensagens selecionadas
		// se encontram em um mesmo folder
		if (count($sorted_msgs)==1){
			$array_names_keys = array_keys($sorted_msgs);
			$this->folder = mb_convert_encoding($array_names_keys[0], "UTF7-IMAP","UTF-8, ISO-8859-1, UTF7-IMAP");
			$msg_number = explode(',', $sorted_msgs[$array_names_keys[0]]);
			$tempDir = $this->tempDir;
			$this->connectImap();
			
			//verifica se apenas uma mensagem foi selecionada e exportar em .eml			
			if(count($msg_number) == 1){
				$header         = $this->getHeader($msg_number[0]);
				$body           = $this->getBody($msg_number[0]);                       
				$sEMLData       = $this->parseEml($header, $body);                     
				$fileName       = $this->CreateFileEml($sEMLData, $tempDir, $msg_number[0]."_".$_SESSION[ 'phpgw_session' ][ 'session_id' ]);
		
				$header    = imap_headerinfo($this->mbox_stream, imap_msgno($this->mbox_stream, $msg_number[0]), 80, 255);
            	$subject = $this->decode_subject(html_entity_decode($header->fetchsubject));

				imap_close($this->mbox_stream);
				if (!$fileName) {
					return false;
				}else{
					$return = array();
					$return[] = $tempDir.'/'.$fileName;
					$return[] = $subject;
					return $return;
				}
			}
			
			//cria um .zip com as mensagens selecionadas
            $msg_number_count = count($msg_number);
			for($i = 0; $i < $msg_number_count; ++$i)
			{
				$header         = $this-> getHeader($msg_number[$i]);                                                                                   
				$body           = $this-> getBody($msg_number[$i]);                     
				$sEMLData       = $this -> parseEml($header, $body);                   
				$fileName       = $this -> CreateFileEml($sEMLData, $tempDir, $msg_number[$i]);

				if(!$fileName) 
				{
					$error = True;                                 
					break;
				} else{
					$fileNames .= "\"".$fileName."\" ";                     
				}
			}
			imap_close($this->mbox_stream);

			$nameFileZip = 'False';                 
			if($fileNames && !$error) 
			{
				$nameFileZip = $this -> createFileZip($fileNames, $tempDir);
				if($nameFileZip) 
				{               
					$file = $tempDir.'/'.$nameFileZip;
				} else {
					$file = false;
				}                                                               
			}
			else 
			{
				$file = false;
			}

			return $file;			
		
		//exporta mensagens de diferentes pastas
		}else{
			$array_names_keys = array_keys($sorted_msgs);

            $array_names_keys_count = count($array_names_keys);
			for($i = 0; $i < $array_names_keys_count; ++$i){
				$this->folder = mb_convert_encoding($array_names_keys[$i], "UTF7-IMAP","UTF-8, ISO-8859-1, UTF7-IMAP");
				$msg_number = explode(',', $sorted_msgs[$array_names_keys[$i]]);
				$tempDir = $this->tempDir;
				$this->connectImap();

                $msg_number_count = count($msg_number);
				for($j = 0; $j < $msg_number_count; ++$j)
				{
					$header         = $this-> getHeader($msg_number[$j]);                                                                                   
					$body           = $this-> getBody($msg_number[$j]);                     
					$sEMLData       = $this -> parseEml($header, $body);                   
					$fileName       = $this -> CreateFileEml($sEMLData, $tempDir, $msg_number[$j]);

					if(!$fileName) 
					{
						$error = True;                                 
						break;
					} else{
						$fileNames .= "\"".$fileName."\" ";                     
					}
				}
				imap_close($this->mbox_stream);
			}
			$nameFileZip = 'False';                 
			if($fileNames && !$error) 
			{
				$nameFileZip = $this -> createFileZip($fileNames, $tempDir);
				if($nameFileZip) 
				{               
					$file = $tempDir.'/'.$nameFileZip;
				} else {
					$file = false;
				}                                                               
			}
			else 
			{
				$file = false;
			}
			return $file;
		}
	}else{
		// Exportacao de mensagens arquivadas localmente
		if($params['l_msg'] == "t")
		{
    		// Recebe todos os subjects e bodies das mensagens locais selecionadas para exportacao
     		// e gera arrays com os conteudos separados;
      		$array_mesgs = explode('@@',$params['mesgs']);
        	$array_subjects = explode('@@',$params['subjects']);
            $array_ids = explode(',', $params['msgs_to_export']);
			$tempDir = $this->tempDir;
			
			include_once("class.imap_functions.inc.php");
			$imapf = new imap_functions();

			// quando houver apenas um arquivo, exporta o .eml sem coloca-lo em zip 
			if (count($array_ids)==1) 
			{ 
				$sEMLData=$imapf->treat_base64_from_post($array_mesgs[0]); 
				$fileName=$this->CreateFileEml($sEMLData, $tempDir,'',$array_subjects[0],"offline"); 
				return $tempDir.'/'.$fileName; 
			}

			// Para cada mensagem selecionada sera gerado um arquivo .eml cujo titulo sera o assunto (subject) da mesma;
    		foreach($array_ids as $i=>$id) {
				$sEMLData=$imapf->treat_base64_from_post($array_mesgs[$i]);
				$fileName=$this->CreateFileEml($sEMLData, $tempDir,'',$array_subjects[$i],$i);
				if(!$fileName){
					$error = True;
					break;
				} else{
					$fileNames .= "\"".$fileName."\" ";
				}
			}
			$nameFileZip = 'False';
			if($fileNames && !$error) {
				$nameFileZip = $this -> createFileZip($fileNames, $tempDir);
				if($nameFileZip){
					$file = $tempDir.'/'.$nameFileZip;
				} else{
					$file = false;
				}

			} else{
				$file = false;
			}
            return $file;
		
		} else
		// Exportacao de mensagens da caixa de entrada (imap) - processo original do Expresso
		{
			$this-> folder = $params['folder'];
			$this->folder = mb_convert_encoding($this->folder, "UTF7-IMAP","UTF-8, ISO-8859-1, UTF7-IMAP");
			$array_ids = explode(',', $params['msgs_to_export']);
			$error = False;
			$fileNames = "";
			$tempDir = $this->tempDir;
			$this->connectImap();

			// quando houver apenas um arquivo, exporta o .eml sem coloca-lo em zip
			if (count($array_ids)==1)
			{
				$header         = $this->getHeader($array_ids[0]);                                                                                     
				$body           = $this->getBody($array_ids[0]);                       
				$sEMLData       = $this->parseEml($header, $body);                     
				$fileName       = $this->CreateFileEml($sEMLData, $tempDir, $array_ids[0]."_".$_SESSION[ 'phpgw_session' ][ 'session_id' ]);
			
				$header    = imap_headerinfo($this->mbox_stream, imap_msgno($this->mbox_stream, $array_ids[0]), 80, 255);
	            $subject = $this->decode_subject(html_entity_decode($header->fetchsubject));

				imap_close($this->mbox_stream);
				if (!$fileName) {
					return false;
				} else {
					$return = array();
					$return[] = $tempDir.'/'.$fileName;
					$return[] = $subject;
					return $return;
				}
			}

            $array_ids_count = count($array_ids);
			for($i = 0; $i < $array_ids_count; ++$i)
			{
				$header         = $this-> getHeader($array_ids[$i]);                                                                                   
				$body           = $this-> getBody($array_ids[$i]);                     
				$sEMLData       = $this -> parseEml($header, $body);                   
				$fileName       = $this -> CreateFileEml($sEMLData, $tempDir, $array_ids[$i]);

				if(!$fileName) 
				{
					$error = True;                                 
					break;
				} else {
					$fileNames .= "\"".$fileName."\" ";                     
				}
			}
			imap_close($this->mbox_stream);

			$nameFileZip = 'False';                 
			if($fileNames && !$error) 
			{
				$nameFileZip = $this -> createFileZip($fileNames, $tempDir);
				if($nameFileZip) 
				{               
					$file = $tempDir.'/'.$nameFileZip;
					$ret[] = $file;
				    return $ret;  
				} else {
					$file = false;
				}                                                               
			}
			else 
			{
				$file = false;
			}
			return $file;
		}
    }
    }

    function export_eml( $params ){

	return $this->export_msg_data( $params['msgs_to_export'],
				       $params['folder'] );
    }

	function export_msg($params) {
		$this-> folder = $params['folder'];
		$this->folder = mb_convert_encoding($this->folder, "UTF7-IMAP","UTF-8, ISO-8859-1, UTF7-IMAP");
		$array_ids = explode(',', $params['msgs_to_export']);
		$error = False;
		$fileNames = "";
		$tempDir = $this->tempDir;
		$this->connectImap();

		// quando houver apenas um arquivo, exporta o .eml sem coloca-lo em zip
		if (count($array_ids)==1)
		{
			$header         = $this->getHeader($array_ids[0]);                                                                                     
			$body           = $this->getBody($array_ids[0]);                       
			$sEMLData       = $this->parseEml($header, $body);                     
			$fileName       = $this->CreateFileEml($sEMLData, $tempDir, $array_ids[0]."_".$_SESSION[ 'phpgw_session' ][ 'session_id' ]);

			$header    = imap_headerinfo($this->mbox_stream, imap_msgno($this->mbox_stream, $array_ids[0]), 80, 255);
            $subject = $this->decode_subject(html_entity_decode($header->fetchsubject));

			imap_close($this->mbox_stream);
			if (!$fileName) {
				return false;
			} else {
				$return = array();
				$return[] = $tempDir.'/'.$fileName;
				$return[] = $subject;
				return $return;
			}
		}
	}

	//MAILARCHIVER 
	function js_source_var($params) {
		$this-> folder = $params['folder'];
		if(!$this->folder){
		   $aux = explode(';',$params['msgs_to_export']);
		   $this->folder = $aux[0];
		   $id_number = $aux[1];
		}
		else{
			$id_number = $params['msgs_to_export'];
		}
		$this->folder = mb_convert_encoding($this->folder, "UTF7-IMAP","ISO_8859-1");
		$tempDir = ini_get("session.save_path");

		$this->connectImap();
		$header	 	= $this-> getHeader($id_number);
		$body		= $this-> getBody($id_number);
		
		if(!strpos($header,"Date: ")){
			$header = "Date: " . $this->getHeaderInfo($id_number)->Date . "\r\n" .$header ;
		}

		imap_close($this->mbox_stream);

		$input = $header . "\r\n\r\n" . $body;
		$input = preg_replace('/\x1d/', '', $input); //remove special char control detected (hex 1D)
		
		return($input);
	} 
	
    function export_msg_data($id_msg,$folder) {
		$this->folder = $folder;
		$this->folder = mb_convert_encoding($this->folder, "UTF7-IMAP","ISO_8859-1");

		$this->connectImap();
		$header	 	= $this-> getHeader($id_msg);
		$body		= $this-> getBody($id_msg);

		$msg_data = $header ."\r\n\r\n". $body;

		imap_close($this->mbox_stream);
		return $msg_data;
	}

		function export_to_archive($id_msg,$folder) {
		$this->folder = $folder;
 		$this->folder = mb_convert_encoding($this->folder, "UTF7-IMAP","ISO_8859-1");
 		$tempDir = $this->tempDir;
 		                 
 		$this->connectImap(); 
 		$header         = $this-> getHeader($id_msg); 
 		$body           = $this-> getBody($id_msg); 
 		
		$file = tempnam ($tempDir, 'source_#'.$id_msg);
		$file .= '.php';
		$fileName = basename ($file);
		$f = fopen($file, "w");
		fputs($f,$phpheader.$header ."\r\n\r\n". $body);
 		fclose($f); 
		$urlPath = 'tmpLclAtt/' . $fileName;
 		                 
 		imap_close($this->mbox_stream); 
		return "inc/gotodownload.php?idx_file=".$tempDir . '/'.$file."&newfilename=fonte_da_mensagem.txt";
        }
 		                 
	function remove_accents($string) {
		/*
			$array1 = array("á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç" , "?", "\"", "!", "@", "#", "$", "%", "¨", "&", "*", "(", ")", "-", "=", "+", "´", "`", "[", "]", "{", "}", "~", "^", ",", "<", ">", ";", ":", "/", "?", "\\", "|", "¹", "²", "³", "£", "¢", "¬", "§", "ª", "º", "°", "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");
			$array2 = array("a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c" , "" , ""  , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" , "" ,  "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C");
		  	return str_replace( $array1, $array2, $string );
		*/
		return strtr($string,
			"áàâãäéèêëíìîïóòôõöúùûüç?\"'!@#$%¨&*()=+´`[]{}~^,<>;:/?\\|¹²³£¢¬§ªº°ÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ",
			"aaaaaeeeeiiiiooooouuuuc__________________________________________AAAAAEEEEIIIIOOOOOUUUUC");
        }

	function get_attachments_headers( $folder, $id_number ){

	    $this->folder = mb_convert_encoding($folder, "UTF7-IMAP","UTF-8");
		
	    $return_attachments = array();
		
	    include_once("class.attachment.inc.php");

	    $imap_attachment = new attachment();
	    $imap_attachment->setStructureFromMail( $folder, $id_number );
	    $attachments = $imap_attachment->getAttachmentsInfo();

		foreach($attachments as $i => $attachment){

		    $fileContent = $imap_attachment->getAttachment( $attachment['pid'] );
			
		    $headers = "<?php header('Content-Type: {$attachment['type']}');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Expires: 0'); // set expiration time
				      header('Content-Disposition: attachment; filename=\"{$attachment['name']}\"');\n
				      echo '$fileContent';?>";
			
		    $return_attachments[ $attachment['name'] ] = array( "content" => $headers, "pid" => $attachment['pid'] );
			}

	    return( $return_attachments );
			}
			
	function get_attachments_in_array($params) {
		$return_attachments = array();

		$attachments = $this->get_attachments_headers( $params['folder'], $params['num_msg'] );

		if( !empty( $attachments ) )
		{
		    foreach($attachments as $fileNameReal => $attachment){

			    array_push($return_attachments,array('name' => $fileNameReal, 'pid' =>$attachment['pid'], 'contentType' => $this->getFileType( $fileNameReal  ) ));
		}
        }

		return $return_attachments;

	}
	
	private function getFileType($nameFile) {
		$strFileType = strrev(substr(strrev(strtolower($nameFile)),0,4));
		$ContentType = "application/octet-stream";
	   	if ($strFileType == ".asf") 
	   		$ContentType = "video/x-ms-asf";
	   	if ($strFileType == ".avi")
	   		$ContentType = "video/avi";
	   	if ($strFileType == ".doc")
	   		$ContentType = "application/msword";
	   	if ($strFileType == ".zip")
	   		$ContentType = "application/zip";
	   	if ($strFileType == ".xls")
	   		$ContentType = "application/vnd.ms-excel";
	   	if ($strFileType == ".gif")
	   		$ContentType = "image/gif";
		if ($strFileType == ".png")
	   		$ContentType = "image/png";
	   	if ($strFileType == ".jpg" || $strFileType == "jpeg")
	   		$ContentType = "image/jpeg";
	   	if ($strFileType == ".wav")
	   		$ContentType = "audio/wav";
	   	if ($strFileType == ".mp3")
	   		$ContentType = "audio/mpeg3";
	   	if ($strFileType == ".mpg" || $strFileType == "mpeg")
	   		$ContentType = "video/mpeg";
	   	if ($strFileType == ".rtf")
	   		$ContentType = "application/rtf";
	   	if ($strFileType == ".htm" || $strFileType == "html")
	   		$ContentType = "text/html";
	   	if ($strFileType == ".xml") 
	   		$ContentType = "text/xml";
	   	if ($strFileType == ".xsl") 
	   		$ContentType = "text/xsl";
	   	if ($strFileType == ".css") 
	   		$ContentType = "text/css";
	   	if ($strFileType == ".php") 
	   		$ContentType = "text/php";
	   	if ($strFileType == ".asp") 
	   		$ContentType = "text/asp";
	   	if ($strFileType == ".pdf")
	   		$ContentType = "application/pdf";
	   	if ($strFileType == ".txt")
	   		$ContentType = "text/plain";
	   	if ($strFileType == ".log")
	   		$ContentType = "text/plain";
	   	if ($strFileType == ".wmv")
	   		$ContentType = "video/x-ms-wmv";
		if ($strFileType == ".sxc")
			$ContentType = "application/vnd.sun.xml.calc";
		if ($strFileType == ".odt")
			$ContentType = "application/vnd.oasis.opendocument.text";
		if ($strFileType == ".stc")
			$ContentType = "application/vnd.sun.xml.calc.template";
		if ($strFileType == ".sxd")
			$ContentType = "application/vnd.sun.xml.draw";
		if ($strFileType == ".std")
			$ContentType = "application/vnd.sun.xml.draw.template";
		if ($strFileType == ".sxi")
			$ContentType = "application/vnd.sun.xml.impress";
		if ($strFileType == ".sti")
			$ContentType = "application/vnd.sun.xml.impress.template";
		if ($strFileType == ".sxm")
			$ContentType = "application/vnd.sun.xml.math";
		if ($strFileType == ".sxw")
			$ContentType = "application/vnd.sun.xml.writer";
		if ($strFileType == ".sxq")
			$ContentType = "application/vnd.sun.xml.writer.global";
		if ($strFileType == ".stw")
			$ContentType = "application/vnd.sun.xml.writer.template";
		if ($strFileType == ".ps")
			$ContentType = "application/postscript";
		if ($strFileType == ".pps")
			$ContentType = "application/vnd.ms-powerpoint";
		if ($strFileType == ".odt")
			$ContentType = "application/vnd.oasis.opendocument.text";
		if ($strFileType == ".ott")
			$ContentType = "application/vnd.oasis.opendocument.text-template";
		if ($strFileType == ".oth")
			$ContentType = "application/vnd.oasis.opendocument.text-web";
		if ($strFileType == ".odm")
			$ContentType = "application/vnd.oasis.opendocument.text-master";
		if ($strFileType == ".odg")
			$ContentType = "application/vnd.oasis.opendocument.graphics";
		if ($strFileType == ".otg")
			$ContentType = "application/vnd.oasis.opendocument.graphics-template";
		if ($strFileType == ".odp")
			$ContentType = "application/vnd.oasis.opendocument.presentation";
		if ($strFileType == ".otp")
			$ContentType = "application/vnd.oasis.opendocument.presentation-template";
		if ($strFileType == ".ods")
			$ContentType = "application/vnd.oasis.opendocument.spreadsheet";
		if ($strFileType == ".ots")
			$ContentType = "application/vnd.oasis.opendocument.spreadsheet-template";
		if ($strFileType == ".odc")
			$ContentType = "application/vnd.oasis.opendocument.chart";
		if ($strFileType == ".odf")
			$ContentType = "application/vnd.oasis.opendocument.formula";
		if ($strFileType == ".odi")
			$ContentType = "application/vnd.oasis.opendocument.image";
		if ($strFileType == ".ndl")
			$ContentType = "application/vnd.lotus-notes";
	   	if ($strFileType == ".eml")
	   		$ContentType = "text/plain";
		if ($strFileType == ".png")
			$ContentType = "image/png";
		return $ContentType;
	}
	
	function download_all_attachments($params) {
		
		require_once dirname(__FILE__).'/class.attachment.inc.php';
		$atObj = new attachment();
		$atObj->setStructureFromMail($params['folder'],$params['num_msg']);
		$attachments = $atObj->getAttachmentsInfo();
		$id_number = $params['num_msg'];		
		$tempDir = $this->tempDir;
		$tempSubDir = $_SESSION['phpgw_session']['session_id'];
		$fileNames = '';
		exec('mkdir ' . $tempDir . '/'.$tempSubDir.'; cd ' . $tempDir . '/'.$tempSubDir);
		$this-> folder = $params['folder'];
		$this->folder = mb_convert_encoding($this->folder, "UTF7-IMAP","UTF-8");
		
		$fileNames = Array();
		$attachments_count = count($attachments);
		for ($i = 0; $i < $attachments_count; ++$i)
                {
                   $attachments[$i]['name'] = $this->remove_accents($attachments[$i]['name']);
                   $fileNames[$i] = $attachments[$i]['name'];
                }

                $attachments_count = count($attachments);
                for ($i = 0; $i < $attachments_count; ++$i)
                {
                        $fileName = $attachments[$i]['name'];
                        $result = array_keys($fileNames, $fileName);

                        // Detecta duplicatas
                        if (count($result) > 1)
                        {
                            $result_count = count($result);
                            for ($j = 1; $j < $result_count; ++$j)
                            {
                                $replacement = '('.$j.')$0';
                                if (preg_match('/\.\w{2,4}$/', $fileName))
                                {
                                    $fileNames[$result[$j]] = preg_replace('/\.\w{2,4}$/', $replacement, $fileName);
                                }
                                else
                                {
                                    $fileNames[$result[$j]] .= "($j)";
                                }
                                $attachments[$result[$j]]['name'] = $fileNames[$result[$j]];
                            }
                        }
                        // Fim detecta duplicatas

			$f = fopen($tempDir . '/'.$tempSubDir.'/'.$fileName,"wb");
			if(!$f)
				return False;			
			$fileContent = $atObj->getAttachment( $attachments[$i]['pid'] );	
				fputs($f,$fileContent);
				
			fclose($f);
		
		}
		imap_close($this->mbox_stream);
		$nameFileZip = '';
		
		if(!empty($fileNames)) {
			$nameFileZip = $this -> createFileZip($fileNames, $tempDir . '/'.$tempSubDir);						
			if($nameFileZip)
				$file =  $tempDir . '/'.$tempSubDir.'/'.$nameFileZip;
			else {
				$file = false;
			}
		}
		else 
			$file = false;	
		return $file;
	}

	function getHeader($msg_number){			
		return imap_fetchheader($this->mbox_stream, $msg_number, FT_UID);
	}

	function getHeaderInfo($msg_number){			
		$header = imap_headerinfo($this->mbox_stream, imap_msgno($this->mbox_stream, $msg_number), 80, 255);
		return $header;
	}
	
	function getBody($msg_number){
		$header = imap_headerinfo($this->mbox_stream, imap_msgno($this->mbox_stream, $msg_number), 80, 255);
		$body = imap_body($this->mbox_stream, $msg_number, FT_UID);
		if(($header->Unseen == 'U') || ($header->Recent == 'N')){
			imap_clearflag_full($this->mbox_stream, $msg_number, "\\Seen", ST_UID);
		}
		return $body;
	}

	function decode_subject($string){
		if ((strpos(strtolower($string), '=?iso-8859-1') !== false) 
			|| (strpos(strtolower($string), '=?windows-1252') !== false)){
			$elements = imap_mime_header_decode($string);
			foreach ($elements as $el)
				$return .= $el->text;
		}
		else if (strpos(strtolower($string), '=?utf-8') !== false) {
			$elements = imap_mime_header_decode($string);
			foreach ($elements as $el){
	   			$charset = $el->charset;
	   			$text	 = $el->text;
	   			if(!strcasecmp($charset, "utf-8") ||
	       			!strcasecmp($charset, "utf-7"))	{
	     			$text = iconv($charset, "ISO-8859-1", $text);
	       		}
	       		$return .= $text;
	  		}
		}
		else
			$return = $string;

		return $this->remove_accents($return);		
	}
}
// END CLASS
?>
