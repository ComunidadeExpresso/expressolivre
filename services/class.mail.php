<?php
/**
*
* Copyright (C) 2011 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*  
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*  
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. 
*
* You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
*  6731, PTI, Bl. 05, Esp. 02, Sl. 10, Foz do Iguaçu - PR - Brasil or at
* e-mail address prognus@prognus.com.br.
*
*
* @package    MailService
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @sponsor    Caixa Econômica Federal
* @version    1.0
* @since      2.4.0
*/

require_once ( dirname(__FILE__).'/../library/mime/mimePart.php' );
require_once ( dirname(__FILE__).'/../library/mime/mime.php' );


class MailService 
{
    static $configuration = array( 'host' => '', 'port' => '', 'username' => '', 'password' => '' , 'auth' => '');
    protected $mail;
    protected $smtpConfig;
    protected $arrayFields = array();
    
	public function __construct( $config = null)
    {
       $this->mail = new Mail_mime();
        
		if( !$config )
		{
			require_once ( ROOT.'/header.inc.php' );
	
			$boemailadmin = CreateObject('emailadmin.bo');
			$emailadmin = $boemailadmin->getProfileList();
			$emailadmin = $boemailadmin->getProfile($emailadmin[0]['profileID']);
			
			self::$configuration['host'] = $emailadmin['smtpServer'];
			self::$configuration['port'] = $emailadmin['smtpPort'];
	    self::$configuration['auth'] = $emailadmin['smtpAuth'] ? true : false;
	    self::$configuration['username'] = $emailadmin['imapAdminUsername'];
	    self::$configuration['password'] = $emailadmin['imapAdminPW'];
		}
		else
			$this->configure( $config );

    }
    
    public function sendMail( $to, $from, $subject, $body, $bcc = false, $cc = false, $isHTML = true )
    {
        
		if( $body )
		{
	    if( $isHTML ) $this->mail->setHTMLBody( $body );
	    else $this->mail->setTXTBody( $body );
		}
	
	if( $subject ) $this->mail->setSubject ( $subject );
		
	if( !$from ) $from = $GLOBALS['phpgw']->preferences->values['email'];
	
        $this->mail->setFrom( $from );
	
        $this->mail->addTo($to);
	
        if( $bcc ) $this->mail->addBcc ($bbc);
		
        if( $cc ) $this->mail->addCc ($cc);
	
        $hdrs = $this->mail->headers($this->arrayFields);
		
        require_once (dirname(__FILE__).'/../library/Mail/Mail.php');
       
        $mail_object =& Mail::factory("smtp", self::$configuration);
	
	$recipients = '';
		
		if( $hdrs["To"] ) 
			$recipients .= $hdrs["To"];
		if( $hdrs["Cc"] && $recipients) 
			$recipients .= ', '.$hdrs["Cc"];
		if($hdrs["Cc"] && !$recipients)
			$recipients = $hdrs["Cc"];
	
	if($hdrs["Bcc"])
	    $arrayBcc = explode(',',$hdrs["Bcc"]);
	
	if($recipients)
		$sent = $mail_object->send($recipients, $hdrs , $this->mail->getMessageBody()); 
		
	if(isset($arrayBcc)){
		foreach ($arrayBcc as $bcc)
			if($bcc)
				$sent = $mail_object->send($bcc, $hdrs , $this->mail->getMessageBody()); 
    }
	if($sent !== true)
		return $sent->message;

		return true;
    }
   
	public function configure( $config )
    {
		if( !$config ) return( false );
	
		foreach( $config as $key => $value )
			if( $value && isset( self::$configuration[$key] ) )
			self::$configuration[$key] = $value;
			}
    
    public function send()
    {
        require_once (dirname(__FILE__).'/../library/Mail/Mail.php');
        $hdrs = $this->mail->headers($this->arrayFields);
        $mail_object =& Mail::factory("smtp", self::$configuration);
	
	$recipients = '';
		
		if( $hdrs["To"] ) 
			$recipients .= $hdrs["To"];
		if( $hdrs["Cc"] && $recipients) 
			$recipients .= ', '.$hdrs["Cc"];
		if($hdrs["Cc"] && !$recipients)
			$recipients = $hdrs["Cc"];
	
	if($hdrs["Bcc"])
	    $arrayBcc = explode(',',$hdrs["Bcc"]);
	
	if($recipients)
		$sent = $mail_object->send($recipients, $hdrs , $this->mail->getMessageBody()); 
		
	if(isset($arrayBcc)){
		foreach ($arrayBcc as $bcc)
			if($bcc)
				$sent = $mail_object->send($bcc, $hdrs , $this->mail->getMessageBody()); 
	}
	if($sent !== true)
		return $sent->message;
	
		return true;
    }

    public function  addStringAttachment($file, $filename, $type, $encoding = 'base64', $disposition = 'attachment')
    {       	
		$this->mail->addAttachment($file, $type, $filename, false, $encoding, $disposition , $charset = '' ,  $language = '' ,  $location = '' ,  $n_encoding = 'quoted-printable', null , '' , 'ISO-8859-1');
    }
    
    public function  addFileAttachment($file, $filename, $type, $encoding = 'base64', $disposition = 'attachment')
    {     
        $this->mail->addAttachment($file, $type, $filename, true, $encoding, $disposition, $charset = '' ,  $language = '' ,  $location = '' ,  $n_encoding = 'quoted-printable' , null , '' , 'ISO-8859-1');
    }
    
    public function addFileImage($file, $c_type='application/octet-stream', $name = '',  $content_id = null)
    {
          $this->mail->addHTMLImage($file, $c_type, $name, true, $content_id );
		}
    
    public function  addStringImage($file, $c_type='application/octet-stream', $name = '',  $content_id = null)
    {       
        $this->mail->addHTMLImage($file, $c_type, $name, false, $content_id );
    }

    public function  getMessage()
    {
         return $this->mail->getMessage(null,null,$this->arrayFields);
    }

    public function addTo($email)
    {
        $this->mail->addTo($email);
    }

    public function addCc($email)
    {
        $this->mail->addCc($email);
    }

    public function addBcc($email)
    {
       $this->mail->addBcc($email);
    }

    public function setSubject($subject)
       {
       $this->mail->setSubject($subject);
        }
        
    public function setFrom($email)
        {
        $this->mail->setFrom($email);
        }

    public function setBodyText($data)
    {
        $this->mail->setTXTBody($data);
    }

    public function setBodyHtml($data)
    {
        $this->mail->setHTMLBody($data);
    }
    
    public function getBodyHtml()
    {
        return $this->mail->getHTMLBody();
    }
         
    public function addHeaderField($field,$value)
    {
	$this->arrayFields[$field] = $value;
    }
    
}

ServiceLocator::register( 'mail', new MailService() );

