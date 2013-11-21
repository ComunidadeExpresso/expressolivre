<?php



class SMTP {
	
	var $smtp;
	var $config;
	var $mail;
	
	public function SMTP(){
		
		@session_start();
		$_SESSION['rootPath'] = ROOTPATH .'/../';
		
		require_once ( ROOTPATH.'/../library/mime/mimePart.php' );
		require_once ( ROOTPATH.'/../library/mime/mime.php' );
		require_once ( ROOTPATH.'/../library/Mail/Mail.php');
	
	}
	
	public function find     ( $uri, $justthese = false, $criteria = false ){}

    public function read     ( $uri, $justthese = false/*, $criteria = false*/ ){}

//---------------

    public function deleteAll( $uri, $justthese = false, $criteria = false ){} 

    public function delete   ( $uri, $justthese = false/*, $criteria = false*/ ){}// avaliar

//---------------

    public function replace  ( $uri, $data, $criteria = false ){}

    public function update   ( $uri, $data/*, $criteria = false*/ ){}

//---------------

    public function create   ( $uri, $data/*, $criteria = false*/ )
	{
        
        $this->mail = new Mail_mime(array('html_charset' => 'UTF-8'));
        
		if( isset($data['body']) )
		{
			if( isset($data['isHtml']) && $data['isHtml'] == true ) $this->mail->setHTMLBody( $data['body'] );
			else $this->mail->setTXTBody( $data['body'] );
		}

		if( isset($data['subject']) ) $this->mail->setSubject ( mb_convert_encoding($data['subject'] , 'ISO-8859-1' , 'UTF-8,ISO-8859-1') );
        
        if( isset($data['attachments']) ){
            
            foreach ($data['attachments'] as $attachment)
            {
                if(!isset($attachment['encode']))
                    $attachment['encode'] = 'base64';
                if(!isset($attachment['disposition']))
                    $attachment['disposition'] = 'attachment';
 
               	$this->mail->addAttachment($attachment['source'], $attachment['type'], $attachment['name'], false, $attachment['encode'], $attachment['disposition'] , '' ,   '' ,   '' , 'base64');
            }
            
        }

		if( !isset($data['from']) ) $data['from'] = $this->config['email'];

		$this->mail->setFrom( $data['from'] );

		$this->mail->addTo($data['to']);

		if( isset($data['bcc']) ) $this->mail->addBcc ($data['bcc']);

		if( isset($data['cc']) ) $this->mail->addCc ($data['cc']);

		if(!isset($data['headersFields']))
			$data['headersFields'] = array();
	
        
		$hdrs = $this->mail->headers($data['headersFields']);

		$recipients = '';

		if( isset($hdrs["To"]) ) 
			$recipients .= $hdrs["To"];
		if( isset($hdrs["Cc"]) && $recipients) 
			$recipients .= ', '.$hdrs["Cc"];
		if( isset($hdrs["Cc"]) && !$recipients)
			$recipients = $hdrs["Cc"];

		if( isset($hdrs["Bcc"]))
			$arrayBcc = explode(',',$hdrs["Bcc"]);
        
		if($recipients)
				$sent = $this->smtp->send($recipients, $hdrs , $this->mail->getMessageBody()); 

		if(isset($arrayBcc)){
			foreach ($arrayBcc as $bcc)
				if($bcc)
					$sent = $this->smtp->send($bcc, $hdrs , $this->mail->getMessageBody()); 
		}
		if($sent !== true)
			return $sent->message;
	       
	     return true;
	}
//---------------

    public function open     ( $config )
	{
		$this->config = $config;
		$this->smtp =& Mail::factory( "smtp" , $config );
		
	}

    public function close(){}
    
    public function begin(){}
     
    public function commit(){}
    
    public function rollback(){}

    public function setup(){}

    public function teardown(){}
	
}

?>
