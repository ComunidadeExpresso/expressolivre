<?php
/**
* Classe que manipula e gerencia toda a parte de anexos.
*
* @package    ExpressoMail
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @author     Cristiano Correa
*/

class attachment
{
    /*
     * Globals variables
     */
    var $mbox;
    var $imap_port;
    var $has_cid;
    var $imap_options;
    var $imap_sentfolder;
    var $msgNumber;
    var $folder;
    var $structure;
    var $decodeConf;
    //------------------------------------------//

    /**
     * Constructor
	 * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @param string $folder Imap folder name
     * @param integer $msgNumber Imap menssagem number
     */
    function attachment()
    {
        /*
         * Requires
         */
            require_once dirname(__FILE__).'/../../library/mime/mimePart.php';
            require_once dirname(__FILE__).'/../../library/mime/mimeDecode.php';
        //----------------------------------------------------------//

        $this->decodeConf['include_bodies'] = true;
        $this->decodeConf['decode_bodies']  = true;
        $this->decodeConf['decode_headers'] = true;
	
	if( array_key_exists('nested_messages_are_shown', $_SESSION['phpgw_info']['user']['preferences']['expressoMail']) && ($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['nested_messages_are_shown'] == '1'))
	    $this->decodeConf['rfc_822bodies']  = true;
    }
    //----------------------------------------------------------------------------//



    /**
     * Open mail from Imap and parse structure
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @return string  menssagem
     */
    public function setStructureFromMail($folder,$msgNumber)
    {
        $this->folder       = mb_convert_encoding($folder, 'UTF7-IMAP',mb_detect_encoding($folder.'x', 'UTF-8, ISO-8859-1'));
        $this->msgNumber    = $msgNumber;
        $this->username     = $_SESSION['phpgw_info']['expressomail']['user']['userid'];
        $this->password     = $_SESSION['phpgw_info']['expressomail']['user']['passwd'];
        $this->imap_server  = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
        $this->imap_port    = $_SESSION['phpgw_info']['expressomail']['email_server']['imapPort'];
        $this->imap_delimiter = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'];
        $this->imap_sentfolder = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']   ? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']   : str_replace("*","", $this->functions->getLang("Sent"));
        $this->has_cid = false;

        if ($_SESSION['phpgw_info']['expressomail']['email_server']['imapTLSEncryption'] == 'yes')
            $this->imap_options = '/tls/novalidate-cert';
        else
            $this->imap_options = '/notls/novalidate-cert';

	$this->mbox = @imap_open("{".$this->imap_server.":".$this->imap_port.$this->imap_options."}".$this->folder , $this->username, $this->password) or die('Error');

        $rawMessageData = $this->_getRaw();
        $decoder = new Mail_mimeDecode($rawMessageData);
        $this->structure = $decoder->decode($this->decodeConf);

		//TODO: Descartar código após atualização do módulo de segurança da SERPRO 
		if($this->isSignedMenssage()) 
			$this->convertSignedMenssage($rawMessageData); 
		//////////////////////////////////////////////////////
			
			
        /*
         * Clean memory and close imap connection
         */
        $rawMessageData = null;
        $decoder = null;
        @imap_close($this->mbox);
        //-----------------------------------------//
    }

	//TODO: Descartar código após atualização do módulo de segurança da SERPRO 
	private function isSignedMenssage() 
 	{ 
		if(strtolower($this->structure->ctype_primary) == 'application' && strtolower($this->structure->ctype_secondary) == 'x-pkcs7-mime' ) 
			return true; 
		else 
			return false;      
	} 
    //////////////////////////////////////////////////////////////////////////

   //TODO: Descartar código após atualização do módulo de segurança da SERPRO 
   private function convertSignedMenssage($rawMessageData) 
	{ 
		$decoder = new Mail_mimeDecode($this->extractSignedContents($rawMessageData)); 
		$this->structure = $decoder->decode($this->decodeConf);          
	} 
	///////////////////////////////////////////////////////////////////////////
	
	//TODO: Descartar código após atualização do módulo de segurança da SERPRO 
	private function extractSignedContents( $data ) 
 		    { 
 		        $pipes_desc = array( 
 		            0 => array('pipe', 'r'), 
 		            1 => array('pipe', 'w') 
 		        ); 
 		 
 		        $fp = proc_open( 'openssl smime -verify -noverify -nochain', $pipes_desc, $pipes); 
 		        if (!is_resource($fp)) { 
 		           return false; 
 		        } 
 		 
 		        $output = ''; 
 		 
 		        /* $pipes[0] => writeable handle connected to child stdin 
 		           $pipes[1] => readable handle connected to child stdout */ 
 		        fwrite($pipes[0], $data); 
 		        fclose($pipes[0]); 
 		 
 		        while (!feof($pipes[1])) { 
 		            $output .= fgets($pipes[1], 1024); 
 		        } 
 		        fclose($pipes[1]); 
 		        proc_close($fp); 
 		 
 		        return $output; 
 		    } 
	/////////////////////////////////////////////////////////////////////////////////////
	
    /**
     * Set Stucture from Mail_mimeDecode Structure
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @param Mail_mimeDecode $structure
     */
    public function setStructure($structure)
    {
          $this->structure = $structure;
    }

    /**
     *  Set Stucture from raw mail code
	 * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @param Mail_mimeDecode $structure
     */
    public function setStructureFromRawMail($rawMail)
    {
        $decoder = new Mail_mimeDecode($rawMail);
        $this->structure = $decoder->decode($this->decodeConf);
		
		//TODO: Descartar código após atualização do módulo de segurança da SERPRO 
		if($this->isSignedMenssage()) 
			$this->convertSignedMenssage($rawMail);  
		//////////////////////////////////////////////////////////////////////////
    }

    /**
     * Returns Attachment Decoded
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @param string $partNumber Index part
     * @return string Attachment Decoded
     */
    public function getAttachment($partNumber)
    {
       $partContent = '';
       $this->_getPartContent($this->structure, $partNumber, $partContent);
       return $partContent;
    }

     /**
     * Returns EmbeddedImages Infos
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @return array EmbeddedImages
     */
    public function getEmbeddedImagesInfo()
    {
        $imagesEmbedded = array();
        $this->_getEmbeddedImagesInfo($this->structure,$imagesEmbedded);
        return $imagesEmbedded;
    }

    /**
     * Returns Attachments Infos
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @return array
     */
    public function getAttachmentsInfo()
    {
        $attachments = array();
        $this->_getAttachmentsInfo($this->structure,$attachments);
        return $attachments;
    }

    /**
     * Returns Attachment Info
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @param string $partNumber Index part
     * @return array
     */
    public function getAttachmentInfo($partNumber)
    {
        $attachment = array();
        $this->_getPartInfo($this->structure,$partNumber,$attachment);
        return $attachment[0];
    }

    /**
     * returns the source code menssagem
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @return string  menssagem
     */
    private function _getRaw()
    {
			/* 
 	         * Chamada original imap_fetchheader($this->mbox, $this->msgNumber, FT_UID)."\r\n".imap_body($this->mbox, $this->msgNumber, FT_UID); 
 	         * Inserido replace para corrigir um bug que acontece raramente em mensagens vindas do outlook com muitos destinatarios 
 	         */ 
 	        return str_replace("\r\n\t", '', imap_fetchheader($this->mbox, $this->msgNumber, FT_UID))."\r\n".imap_body($this->mbox, $this->msgNumber, FT_UID);
    }

    /**
     * Returns content from the searched
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	 * @param pointer $structure Structure object
     * @param string $soughtIndex
     * @param pointer $body Content
     * @param string $pIndex
     */
    private function _getPartContent(&$structure, $soughtIndex,&$body,$pIndex = '0')
    {
        if($structure->parts)
        {
            foreach ($structure->parts  as $index => $part)
            {
                if(strtolower($part->ctype_primary) == 'multipart')
                        $this->_getPartContent($part,$soughtIndex,$body,$pIndex.'.'.$index);
                else
                {
                    if(strtolower($part->ctype_primary) == 'message' && is_array($part->parts) && $this->decodeConf['rfc_822bodies'] !== true )
                            $this->_getPartContent($part,$soughtIndex,$body,$pIndex.'.'.$index);
                    else
                    {
                        $currentIndex = $pIndex.'.'.$index;
                        if($currentIndex === $soughtIndex)
                        {
                            $body = $part->body;
                            break;
                        }
                    }
                }
            }
        }
        else if($soughtIndex == '0')
           $body = $structure->body;
    }

	/**
     * @license   http://www.gnu.org/copyleft/gpl.html GPL
	 * @author    Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     */
    private function _getPartInfo(&$structure, $soughtIndex,&$info,$pIndex = '0')
    {

        if($structure->parts)
        {
            foreach ($structure->parts  as $index => $part)
            {
                if(strtolower($part->ctype_primary) == 'multipart')
                    $this->_getPartInfo($part,$soughtIndex,$info,$pIndex.'.'.$index);
                else
                {
                    if(strtolower($part->ctype_primary) == 'message' && is_array($part->parts) && $this->decodeConf['rfc_822bodies'] !== true)
                         $this->_getPartInfo($part,$soughtIndex,$info,$pIndex.'.'.$index);
                    else
                    {
                        $currentIndex = $pIndex.'.'.$index;
                        if($currentIndex === $soughtIndex)
                        {
							$this->_pushAttachmentsInfo($part,$info,$currentIndex);
                            break;
                        }
                    }
                }
            }
        }else if($soughtIndex == '0')
			$this->_pushAttachmentsInfo($structure,$info);
    }



     /**
     * Write in $attachments, array with the information of attachments
     * @param <type> $structure
     * @param <type> $attachments
     */
    private function _getAttachmentsInfo($structure, &$attachments, $pIndex = '0')
    {

        if(isset($structure->parts))
        {
            foreach ($structure->parts  as $index => $part)
            {
                if(strtolower($part->ctype_primary) == 'multipart')
                    $this->_getAttachmentsInfo($part,$attachments,$pIndex.'.'.$index);
                else
                {
                    if(strtolower($part->ctype_primary) == 'message' && is_array($part->parts) && $this->decodeConf['rfc_822bodies'] !== true)
                       $this->_getAttachmentsInfo($part,$attachments,$pIndex.'.'.$index);
                    else
                    {
                        if(!$part->headers['content-transfer-encoding']) //Caso não esteja especificado a codificação 
 		                    $part->headers['content-transfer-encoding'] = mb_detect_encoding ($part->body,array('BASE64','Quoted-Printable','7bit','8bit','ASCII')); 
						if($part->headers['content-transfer-encoding'] === ('ASCII' || false)) //Caso a codificação retorne ascii ou false especifica como base64
							$part->headers['content-transfer-encoding'] = 'base64';
						
							$this->_pushAttachmentsInfo($part,$attachments,$pIndex.'.'.$index);
                     
                    }
                }
            }
        }
        else
			$this->_pushAttachmentsInfo($structure,$attachments);

    }

	
	/**
	* Write in $$attachments, array with the information of attachment 
 	* @param <type> $structure 
 	* @param <type> $attachments 
 	* @param <type> $pIndex 
	*/	
 	private function _pushAttachmentsInfo(&$structure, &$attachments, $pIndex = '0') 
 	{ 
			$name = '';                                
 		        if((isset($structure->d_parameters['filename']))&& ($structure->d_parameters['filename'])) $name = $structure->d_parameters['filename']; 
 		        else if((isset($structure->ctype_parameters['name']))&&($structure->ctype_parameters['name'])) $name = $structure->ctype_parameters['name'];
 		        else if(strtolower($structure->ctype_primary) == 'text' &&  strtolower($structure->ctype_secondary) == 'calendar') $name = 'calendar.ics'; 
 		 
                            //Attachments com nomes grandes são quebrados em varias partes VER RFC2231
                            if( !$name && isset($structure->disposition) && (strtolower($structure->disposition) === 'attachment' || strtolower($structure->ctype_primary) == 'image' ||  strtolower($structure->ctype_primary.'/'.$structure->ctype_secondary) == 'application/octet-stream'))
					foreach ($structure->d_parameters as $i => $v)
						if(strpos($i , 'filename') !== false)
							$name .= urldecode(str_ireplace(array('ISO-8859-1','UTF-8','US-ASCII'),'',$v));
			     if( !$name && isset($structure->disposition) && (strtolower($structure->disposition) === 'attachment' || strtolower($structure->ctype_primary) == 'image' ||  strtolower($structure->ctype_primary.'/'.$structure->ctype_secondary) == 'application/octet-stream') )
					foreach ($structure->ctype_parameters as $i => $v)
						if(strpos($i , 'name') !== false)
							$name .= urldecode(str_ireplace(array('ISO-8859-1','UTF-8','US-ASCII'),'',$v));		
			   ////////////////////////////////////////////////////////////////////////////////////
						

                        if($structure->ctype_primary == 'message') {
                                $attach = new attachment();
                                $attach->setStructureFromRawMail($structure->body);

                                if (!$name)
                                        $name = (isset($attach->structure->headers['subject'])) ? $attach->structure->headers['subject'] : 'no title';

                                if (!preg_match("/\.eml$/", $name))
                                        $name .= '.eml';
                        }		                               

                        if(!$name && strtolower($structure->ctype_primary) == 'image')
                        {
                            if(strlen($structure->ctype_secondary) === 3)
                                $ext = strtolower($structure->ctype_secondary);

                            $name = 'Embedded-Image.'.$ext;
                        }
				
 		        if($name) 
 		        { 
 		            $codificao =  mb_detect_encoding($name.'x', 'UTF-8, ISO-8859-1'); 
 		            if($codificao == 'UTF-8') $name = utf8_decode($name); 
 		             
 		            $definition['pid'] = $pIndex; 
 		            $definition['name'] = addslashes(mb_convert_encoding($name, "ISO-8859-1")); 
 		            $definition['encoding'] = $structure->headers['content-transfer-encoding']; 
                            $definition['type'] = strtolower($structure->ctype_primary).'/'.strtolower($structure->ctype_secondary);                                       
 		            $definition['fsize'] = mb_strlen($structure->body, $structure->headers['content-transfer-encoding']); 
 		 
 		            array_push($attachments, $definition); 
 		        }     
	}
	
	
	
	
	
	
    /**
     * Write in $images, array with the information of Embedded Images
     * @param <type> $structure
     * @param <type> $attachments
     */
     private function _getEmbeddedImagesInfo($structure, &$images, $pIndex = '0')
     {
		if(isset($structure->parts))
        foreach ($structure->parts  as $index => $part)
        {
            if(strtolower($part->ctype_primary) == 'multipart')
                    $this->_getEmbeddedImagesInfo($part,$images,$pIndex.'.'.$index);
            else
            {
                if(strtolower($part->ctype_primary) == 'message' && is_array($part->parts) && $this->decodeConf['rfc_822bodies'] !== true)
                        $this->_getEmbeddedImagesInfo($part,$images,$pIndex.'.'.$index);
                else
                {
                    if(is_array($part->ctype_parameters) && !array_key_exists('name', $part->ctype_parameters))
                        if(isset($part->d_parameters['filename']))
                         $name = $part->d_parameters['filename'];
                    else
                          $name = null;
                    else
                         $name = $part->ctype_parameters['name'];

                     //Attachments com nomes grandes são quebrados em varias partes VER RFC2231
                        if( !$name && isset($part->disposition) && (strtolower($part->disposition) === 'attachment' || strtolower($part->ctype_primary) == 'image' ||  strtolower($part->ctype_primary.'/'.$part->ctype_secondary) == 'application/octet-stream') )
                                    foreach ($part->d_parameters as $i => $v)
                                            if(strpos($i , 'filename') !== false)
                                                    $name .= urldecode(str_ireplace(array('ISO-8859-1','UTF-8','US-ASCII'),'',$v));
                         if( !$name && isset($part->disposition) && (strtolower($part->disposition) === 'attachment' || strtolower($part->ctype_primary) == 'image' ||  strtolower($part->ctype_primary.'/'.$part->ctype_secondary) == 'application/octet-stream') )
                                    foreach ($part->ctype_parameters as $i => $v)
                                            if(strpos($i , 'name') !== false)
                                                    $name .= urldecode(str_ireplace(array('ISO-8859-1','UTF-8','US-ASCII'),'',$v));		
                       ////////////////////////////////////////////////////////////////////////////////////
                    
                    if($name && (strlen($name) - (strrpos($name, '.')+1) === 3 )) 
                        $ext = strtolower(substr ( $name , (strrpos($name, '.')+1) ));		
                    else if(strlen($structure->ctype_secondary) === 3)
                        $ext = strtolower($structure->ctype_secondary);
                    
                    if(!$name && strtolower($structure->ctype_primary) == 'image') $name = 'Embedded-Image.'.$ext;
					
                    $ctype = strtolower($part->ctype_primary.'/'.$part->ctype_secondary);
        
                    if(strtolower($part->ctype_primary) == 'image' ||  ($ctype == 'application/octet-stream' && ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'bmp' || $ext == 'tif')))
                    {
                        $definition['pid'] = $pIndex.'.'.$index;
                        $definition['name'] = addslashes($name);
                        $definition['type'] = $ctype;
                        $definition['encoding'] = isset($part->headers['content-transfer-encoding']) ? $part->headers['content-transfer-encoding'] : 'base64';
                        $definition['cid'] = isset($part->headers['content-id']) ? $part->headers['content-id'] : '';
                        array_push($images, $definition);
                    }
                }
            }
        }
    }

}

?>
