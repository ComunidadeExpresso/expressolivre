<?php
/*
 * Definitions
 */
if(!isset($GLOBALS['phpgw_info']))
{
    $GLOBALS['phpgw_info']['flags'] = array(
            'currentapp' => 'expressoMail',
            'nonavbar'   => true,
            'noheader'   => true
    );
}

$GLOBALS['phpgw_info']['server']['download_temp_dir'] = '/tmp';
//-----------------------//

//-----------------------//

/*
 * Get variables
 */
if(array_key_exists('newFilename', $_GET ) && urldecode($_GET['newFilename']) !== 'application.octet-stream')
	$newFilename = urldecode($_GET['newFilename']);
else
	$newFilename = null;
if(array_key_exists('idx_file', $_GET ))
	$indexFile = $_GET['idx_file'];
else
	$indexFile = null;

$indexPart = $_GET['indexPart'];
$msgNumber = $_GET['msgNumber'];
$msgFolder = $_GET['msgFolder'];
if(array_key_exists('fileType', $_GET ))
	$ContentType = $_GET['fileType'];
else
	$ContentType = null;
if(array_key_exists('image', $_GET ))
	$image = $_GET['image'];
else
	$image = null;
//----------------------------------------------//

/*
 * Requires
 */
//if( $indexFile )
    require_once '../../header.inc.php';
//else
 //   require_once '../../header.session.inc.php';


/*
 * Functions
 */

function unhtmlentities($string)
{
    $string = utf8_encode($string);
    
    static $trans_tbl;
 
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'code2utf(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'code2utf(\\1)', $string);

    if (!isset($trans_tbl))
        {
        $trans_tbl = array();
       
        foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
            $trans_tbl[$key] = utf8_encode($val);
        }
    return strtr($string, $trans_tbl);
}

function code2utf($num)
{
    if ($num < 128) return chr($num);
    if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    return '';
}

//-----------------------//

/*
 * Main
 */


if($msgNumber != 'null' && $indexPart !== null && $msgFolder)
{
    require_once dirname(__FILE__).'/class.attachment.inc.php';

    $attachment = new attachment();
    $attachment->setStructureFromMail($msgFolder, $msgNumber);
    $fileContent = $attachment->getAttachment($indexPart);
    $info = $attachment->getAttachmentInfo($indexPart);
    $filename = $newFilename ? $newFilename : $info['name'];       
    $filename = unhtmlentities($filename);
}
else
{
    if( $image && $_SESSION['phpgw_info']['expressomail']['contact_photo'] )
                                {
	$image = "thumbnail";

	$photo = array( 'width' => 60, 'height' => 80, 'quality' => 100 );

	$fileContent = $_SESSION['phpgw_info']['expressomail']['contact_photo'][0];
	unset( $_SESSION['phpgw_info']['expressomail']['contact_photo'] );
                }

    $filename = $indexFile;
}

$filename = $filename 	? $filename 	: "attachment.bin";
$newFilename = $newFilename ? $newFilename 	: $filename;
$newFilename = unhtmlentities($newFilename);
$disposition = $image ? "inline" : "attachment; filename=\"". addslashes($newFilename)."\"";

if( !$ContentType || $ContentType == 'application/octet-stream')
{
    
    if( strstr($_SERVER['HTTP_USER_AGENT'],'MSIE') && $disposition != 'inline' )
        $ContentType = 'application-download';
                else
                {
        function guessContentType( $fileName )
        {
            $strFileType = strtolower(substr ( $fileName , strrpos($fileName, '.') ));
                         
	    switch( $strFileType )
	    {
		case ".asf": return "video/x-ms-asf";
		case ".avi": return "video/avi";
		case ".doc": return "application/msword";
		case ".zip": return "application/zip";
		case ".xls": return "application/vnd.ms-excel";
		case ".gif": return "image/gif";
                case ".bmp": return "image/bmp";
		case ".jpeg":
		case ".jpg": return "image/jpeg";
		case ".wav": return "audio/wav";
		case ".mp3": return "audio/mpeg3";
		case ".mpeg":
		case ".mpg": return "video/mpeg";
		case ".rtf": return "application/rtf";
		case ".html":
		case ".htm": return "text/html";
		case ".xml": return "text/xml";
		case ".xsl": return "text/xsl";
		case ".css": return "text/css";
		case ".php": return "text/php";
		case ".asp": return "text/asp";
		case ".pdf": return "application/pdf";
		case ".png": return "image/png";
		case ".txt": return "text/plain";
		case ".log": return "text/plain";
		case ".wmv": return "video/x-ms-wmv";
		case ".sxc": return "application/vnd.sun.xml.calc";
		case ".odt": return "application/vnd.oasis.opendocument.text";
		case ".stc": return "application/vnd.sun.xml.calc.template";
		case ".sxd": return "application/vnd.sun.xml.draw";
		case ".std": return "application/vnd.sun.xml.draw.template";
		case ".sxi": return "application/vnd.sun.xml.impress";
		case ".sti": return "application/vnd.sun.xml.impress.template";
		case ".sxm": return "application/vnd.sun.xml.math";
		case ".sxw": return "application/vnd.sun.xml.writer";
		case ".sxq": return "application/vnd.sun.xml.writer.global";
		case ".stw": return "application/vnd.sun.xml.writer.template";
		case ".pps": return "application/vnd.ms-powerpoint";
		case ".odt": return "application/vnd.oasis.opendocument.text";
		case ".ott": return "application/vnd.oasis.opendocument.text-template";
		case ".oth": return "application/vnd.oasis.opendocument.text-web";
		case ".odm": return "application/vnd.oasis.opendocument.text-master";
		case ".odg": return "application/vnd.oasis.opendocument.graphics";
		case ".otg": return "application/vnd.oasis.opendocument.graphics-template";
		case ".odp": return "application/vnd.oasis.opendocument.presentation";
		case ".otp": return "application/vnd.oasis.opendocument.presentation-template";
		case ".ods": return "application/vnd.oasis.opendocument.spreadsheet";
		case ".ots": return "application/vnd.oasis.opendocument.spreadsheet-template";
		case ".odc": return "application/vnd.oasis.opendocument.chart";
		case ".odf": return "application/vnd.oasis.opendocument.formula";
		case ".odi": return "application/vnd.oasis.opendocument.image";
		case ".ndl": return "application/vnd.lotus-notes";
		case ".eml": return "text/plain";
		case ".ps" : return "application/postscript";
		default    : return "application/octet-stream";
                        }
                }

        $ContentType = guessContentType( $filename );
        }
}

header("Content-Type: $ContentType");
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");
header("Expires: 0"); // set expiration time
header("Content-Disposition: $disposition");

if( $fileContent )
{
    header("Content-Length: ". mb_strlen($fileContent));
    
    if( isset($info["encoding"]) )
    {
        header("Content-transfer-encoding: ".$info["encoding"] );
    //    header("Content-encoding: ".$info["encoding"] );
    } 
    
    if( $image === 'thumbnail'  && (strtolower(substr ( $info['name'] , (strrpos($info['name'], '.')+1))) !== 'bmp') )
    {
	$pic = imagecreatefromstring( $fileContent );

	if( $pic )
	{
	    $width = imagesx($pic);
	    $height = imagesy($pic);

	    if(!isset($photo))
			$photo = array( 'width' => 160, 'height' => max( ( 160 * $height / $width ), 1 ) );

	    $thumb = imagecreatetruecolor( $photo['width'], $photo['height'] );

	    if(array_key_exists('quality', $photo)  && $photo['quality'] )
			imagecopyresampled($thumb, $pic, 0, 0, 0, 0, $photo['width'], $photo['height'], $width, $height);
	    else
		imagecopyresized($thumb, $pic, 0, 0, 0, 0, $photo['width'], $photo['height'], $width, $height); # resize image into thumb

	    imagejpeg( $thumb,"", 100 ); # Thumbnail as JPEG
	}
    }
    else
	echo $fileContent;
}
else
{   
    /**
    * Delete Diretorio
    * @param string $dir 
    */
    function cleanup( $dir )
    {
    if ( is_dir( $dir ) )
    {
        $files = scandir($dir);

        foreach( $files as $file )
            if( $file != "." && $file != ".." )
                cleanup( $dir.'/'.$file );

        reset( $files ); //?
        
        if(!rmdir( $dir ))
            return;
    }
    else
    {
        if(!unlink( $dir ))
        return;
    }

    }

    $tempDir = $GLOBALS['phpgw_info']['server']['download_temp_dir'];

    header("Content-Length: ". filesize($filename));
    header("Content-encoding: text/plain" ); 
    
    if (preg_match("#^".$tempDir."/(".$GLOBALS['phpgw']->session->sessionid."/)*[A-z0-9_]+_".$GLOBALS['phpgw']->session->sessionid."[A-z0-9]*(\.[A-z]{3,4})?$#",$filename))
    {
        session_write_close();
        set_time_limit(0);
        ob_clean();
        flush();
        readfile($filename);

        //removendo pelo php, garante a suportabilidade cross-platform
	cleanup(  $filename  );

    }
    else
    {
	    if (preg_match("#^".dirname( __FILE__ ) . '/../tmpLclAtt'."/source_#",$filename)) {
		    readfile($filename);
	    }
    }
}
        
//-----------------------//

?>
