<?php
/** 
* Download.class.php 
*/
/** 
* Download de Arquivos
*  
* Formul�rios
* <code>
* include '../lib/config.php';
* include '../includes/classes/Download.class.php';
* 
* $down = new Download('my_files/', 'true'); 
* $down-> doDownload('tazmania2.jpg',true);
*
* if($down->getSysError() != ''){
*    echo $down->getSysDebug();
*    echo    $down->getSysError(); 
* }
*
* </code>
* 
* 
* 
* 
*/
class Download {
    
    public  $sysError = '';
    public  $sysDebug = '';
    
    private $_pathFolder = '';
    private $_pathBase = "";
    private $_file = "";
    private $_fileExtension = '';
    private $_fileMime = '';
    
    private $_fileSize = 0;
    private $_forceDownload = true;
    
    /** 
    * � Construtor do metodo: 
    * <code> 
    * $down = $down = new Download('my_files/', 'true'); 
    * </code> 
    *�
    * @param string $path Pasta de onde ser� feito Donwload  
    * @param string $base 'true'/'false' caminho true (/var/www/) caminho false
    * (/var/www/html/site.../)
    */
    
    function Download($path, $base) {
        
        $this->sysDebug .= '<b>Download</b><br>';
        
        try{
            //pastas que s�o permitidas fazer download
            $arrFolders = array();
            $arrFolders[] = '/';
            
            
            if(trim($path) != '' && in_array(trim($path), $arrFolders)){
                $this->_pathFolder = trim($path);
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pasta: </b> ' . $this->_pathFolder . '<br>';
            }/* else {
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pasta Invalida: </b> [' . $path . ']<br>';
                throw new exception(' caminho [' . $path . '] � invalido.');
            }
            */
            if(trim($base) != '' && (trim($base) == 'true' || trim($base) == 'false') ){
                if(trim($base) == 'true'){
                    $this->_pathBase = '';
                } else {
                    $this->_pathBase = _SITEDIR_;
                }
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Base: </b> ' . $this->_pathBase . '<br>';
            } else {
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Base Invalida: </b> [' . $base . ']<br>';
                throw new exception('Base [' . $base . '] � invalido.');
            }
            
            /*if(!is_dir($this->_pathBase . $this->_pathFolder)){
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Caminho n�o corresponde a uma pasta valida: </b> [' . $this->_pathBase . $this->_pathFolder . ']<br>';
                throw new exception(' Caminho n�o corresponde a uma pasta valida: </b> [' . $this->_pathBase . $this->_pathFolder . '].');
            } */
            
        } catch (exception $e) {
            
            $this->sysError = '<b>Erro: </b>' . $e->getMessage() . '<br>';
            $this->sysDebug .= '<b>Thows Construtor Exit</b> <br>';

            $fp = fopen ("/tmp/logDownloadsIndevidos","a+");
            $writeDebug = "Data : " . date('d/m/Y H:m:s') . 
                          " \nPagina : " . $_SERVER['PHP_SELF'] . 
                          " \nIP : " . $_SERVER['REMOTE_ADDR'] . 
                          " \nUsuoid : " . $_SESSION['usuario']['oid'] .
                          "\n" . $this->sysDebug . " \n\n";
            $writeDebug = strip_tags(html_entity_decode(str_ireplace('<br>', "\n", $writeDebug), ENT_NOQUOTES, 'ISO8859-1'));
            fwrite($fp, $writeDebug );
            fclose($fp);

        }
        
    }

    /** 
    * � Meodo de download de arquivos
    *  <code> 
    * $down-> doDownload('file.ext');
    * </code> 
    *�
    * @param string $file Arquivo que ser� feito download
    */
    function doDownload($file, $forceDownload = true){
        try{
            
            $this->_forceDownload = $forceDownload;
            
            if(trim($file) != ''){
                $this->_file = trim($file);
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;File Name: </b> [' . $this->_file . ']<br>';
            } else {
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Base Invalida: </b> [' . $file . ']<br>';
                throw new exception(' Arquivo [' . $file . '] � invalido.');
            }
            
            
            
            preg_match('/\.([^\.]*$)/', $this->_file, $extension);
            $this->_fileExtension = strtolower(substr($extension[0], 1, strlen($extension[0])));
            $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Formato do Arquivo: </b> [' . $this->_fileExtension . ']<br>';
            
            if($this->_fileExtension == 'php'){
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Formato de Arquivo invalido: </b> [' . $this->_fileExtension . ']<br>';
                throw new exception('Arquivo invalido.');
            }
            
            if(strstr( $this->_pathBase . $this->_pathFolder . $this->_file, '../' )){
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pasta contem caracteres invalidos: </b> [' . $this->_pathBase . $this->_pathFolder . $this->_file . ']<br>';
                throw new exception(' caminho [' . $this->_pathBase . $this->_pathFolder . $this->_file . '] � invalido.');
            }
            
            if(!file_exists($this->_pathBase . $this->_pathFolder . $this->_file)){
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Arquivo n�o encontrado: </b> [' . $this->_pathBase . $this->_pathFolder . $this->_file . ']<br>';
                throw new exception('Arquivo n�o encontrado.');
            }
            
            $sizeFile = filesize($this->_pathBase . $this->_pathFolder . $this->_file);
            if($sizeFile > 0 ){
                $this->_fileSize = $sizeFile;   
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Arquivo com tamanho </b> [' . $this->_fileSize . '] bytes<br>';
            } else {
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Arquivo com tamanho 0 bytes: </b><br>';
                throw new exception('Arquivo truncado.');
            }
            
            $arrExtensions = array();
            $arrExtensions[] = 'jpg';
            $arrExtensions[] = 'jpeg';
            $arrExtensions[] = 'jpe';
            $arrExtensions[] = 'gif';
            $arrExtensions[] = 'png';
            $arrExtensions[] = 'bmp';
            $arrExtensions[] = 'tiff';
            $arrExtensions[] = 'tif';
            $arrExtensions[] = 'doc';
            $arrExtensions[] = 'docx';
            $arrExtensions[] = 'xls';
            $arrExtensions[] = 'xlt';
            $arrExtensions[] = 'xlm';
            $arrExtensions[] = 'xld';
            $arrExtensions[] = 'xla';
            $arrExtensions[] = 'xlc';
            $arrExtensions[] = 'xlw';
            $arrExtensions[] = 'xll';
            $arrExtensions[] = 'ppt';
            $arrExtensions[] = 'pps';
            $arrExtensions[] = 'txt';
            $arrExtensions[] = 'zip';
            $arrExtensions[] = 'rar';
            $arrExtensions[] = 'rtf';
            $arrExtensions[] = 'pdf';
            $arrExtensions[] = 'odt';
            $arrExtensions[] = 'xml';
            $arrExtensions[] = 'html';
            $arrExtensions[] = 'htm';
            $arrExtensions[] = 'csv';
            $arrExtensions[] = 'eml';
            
            if(! in_array($this->_fileExtension, $arrExtensions) ){
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Extens�o de arquivo n�o permitida</b><br>';
                throw new exception('Extens�o invalida [' . $this->_fileExtension . '].');
            }
           
            switch($this->_fileExtension) {
                case 'jpg': case 'jpeg': case 'jpe':
                    $this->_fileMime = 'image/jpeg';                         break;
                case 'gif':
                    $this->_fileMime = 'image/gif';                          break;
                case 'png':
                    $this->_fileMime = 'image/png';                          break;
                case 'bmp':
                    $this->_fileMime = 'image/bmp';                          break;
                case 'js' :
                    $this->_fileMime = 'application/x-javascript';           break;
                case 'json' :
                    $this->_fileMime = 'application/json';                   break;
                case 'tiff' :case 'tif' :
                    $this->_fileMime = 'image/tiff';                         break;
                case 'css' :
                    $this->_fileMime = 'text/css';                           break;
                case 'xml' :
                    $this->_fileMime = 'application/xml';                    break;
                case 'doc' :  case 'docx' :
                    $this->_fileMime = 'application/msword';                 break;
                case 'xls': case 'xlt': case 'xlm': case 'xld': case 'xla': case 'xlc': case 'xlw' : case 'xll' :
                    $this->_fileMime = 'application/vnd.ms-excel';           break;
                case 'ppt': case 'pps' :
                    $this->_fileMime = 'application/vnd.ms-powerpoint';      break;
                case 'rtf' :
                    $this->_fileMime = 'application/rtf';                    break;
                case 'pdf' :
                    $this->_fileMime = 'application/pdf';                    break;
                case 'html': case 'htm': case 'php' :
                    $this->_fileMime = 'text/html';                          break;
                case 'txt' :
                    $this->_fileMime = 'text/plain';                         break;
                case 'mpeg': case 'mpg': case 'mpe' :
                    $this->_fileMime = 'video/mpeg';                         break;
                case 'mp3' :
                    $this->_fileMime = 'audio/mpeg3';                        break;
                case 'wav' :
                    $this->_fileMime = 'audio/wav';                          break;
                case 'aiff':  case 'aif' :
                    $this->_fileMime = 'audio/aiff';                         break;
                case 'avi' :
                    $this->_fileMime = 'video/msvideo';                      break;
                case 'wmv' :
                    $this->_fileMime = 'video/x-ms-wmv';                     break;
                case 'mov' :
                    $this->_fileMime = 'video/quicktime';                    break;
                case 'zip' :
                    $this->_fileMime = 'application/zip';                    break;
                case 'tar' :
                    $this->_fileMime = 'application/x-tar';                  break;
                case 'swf' :
                    $this->_fileMime = 'application/x-shockwave-flash';      break;
                default : 
                    $this->_fileMime = 'application/octet-stream'; 
            }
            if ($this->_fileMime == 'application/octet-stream') {
               $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mime Type Default: </b> [' . $this->_fileMime . ']<br>';
            } else {
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mime Type Identificado: </b> [' . $this->_fileMime . ']<br>';;
            }
            
            if($forceDownload === true){
                $this->_forceDownload = "Content-Disposition: attachment; filename=" . $this->_file;
                $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Download: </b> [' . $this->_forceDownload . ']<br>';
            } else {
                 $this->_forceDownload = "Content-Disposition: inline; filename=" . $this->_file;
                 $this->sysDebug .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Download: </b> [' . $this->_forceDownload . ']<br>';
            }

            header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT;");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT;");
            header("cache-control: post-check=0, pre-check=0, false;"); 
            header("Pragma: public;");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0;");
            header("Cache-Control: private, false;");
            header("Content-Transfer-Encoding: binary;");
            header("Content-Length: ". $this->_fileSize  . ';');
            header($this->_forceDownload . ';');
            header('Content-type: ' . $this->_fileMime . ';');
            
            
            readfile($this->_pathBase . $this->_pathFolder . $this->_file);
                
            /*
            //header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
            //header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            //header("cache-control: post-check=0, pre-check=0", false); 
            header("Pragma: public");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ". $this->_fileSize );
            header($this->_forceDownload);
            header('Content-type: ' . $this->_fileMime . ';');
            //header('Content-Description: ' . $this->_file . ';');
            */
        } catch (exception $e) {
            $this->sysError = '<b>Erro: </b>' . $e->getMessage() . '<br>';
            $this->sysDebug .= '<b>Thows UPLOAD Exit</b> <br>';
 
            $fp = fopen ("/tmp/logDownloadsIndevidos","a+");
            $writeDebug = "Data : " . date('d/m/Y H:m:s') . 
                          " \nPagina : " . $_SERVER['PHP_SELF'] . 
                          " \nIP : " . $_SERVER['REMOTE_ADDR'] . 
                          " \nUsuoid : " . $_SESSION['usuario']['oid'] .
                          "\n" . $this->sysDebug . " \n\n";
            $writeDebug = strip_tags(html_entity_decode(str_ireplace('<br>', "\n", $writeDebug), ENT_NOQUOTES, 'ISO8859-1'));
            fwrite($fp, $writeDebug );
            fclose($fp);
            
        }
        
        
    }
    /*
    * �Get susError (Mensagem de erro)
    * <code> 
    *   $down-> getSysError();
    * </code> 
    *�
    */
    function getSysError(){
        $ret = '';
        if(isset($this->sysError{0})){
            $ret = '<font color="#FF0000">' . $this->sysError . '</font>';
        }
        return $ret;
    }
    /**
    *  �Get sysDebug (Mensagem do Debug) 
    * <code>   
    *    $down- > getSysDebug();
    * </code> 
    *�
    */
    function getSysDebug(){
        return '<pre>' . $this->sysDebug . '</pre>'; 
    }
}
?>