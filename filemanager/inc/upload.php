<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
require_once '../../header.session.inc.php';

$post_limit = error_get_last( );

if ( preg_match( '/POST Content-Length of (\d+) bytes exceeds the limit of \d+ bytes/i', $post_limit[ 'message' ], $matches ) )
{
	$_SESSION['response'] =  serialize( array(
		 'postsize' => $matches[ 1 ],
		 'max_postsize' => ini_get( 'post_max_size' )
	) );

	exit;
}

/* This single file is used to increase upload_max_filesize and post_max_size using .htaccess*/
if(!isset($GLOBALS['phpgw_info'])){
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'filemanager',
		'nonavbar'   => true,
		'noheader'   => true
	);
}
require_once '../../header.inc.php';

$bo = CreateObject('filemanager.bofilemanager');
$c	= CreateObject('phpgwapi.config','filemanager');
$c->read_repository();

$current_config			= $c->config_data;
$upload_max_size		= $current_config['filemanager_Max_file_size'];
$path					= $_POST['path'];
$notifUser				= $_POST['notifTo'];
$show_upload_boxes		= count($_FILES['upload_file']['name']);
$filesUpload			= $_FILES['upload_file'];

function create_summaryImage($file)
{
	list($width, $height,$image_type) = getimagesize($file);

	switch($image_type)
	{
		case 1:
			$image_big = imagecreatefromgif($file);
			break;
		case 2:
			$image_big = imagecreatefromjpeg($file);
			break;
		case 3:
			$image_big = imagecreatefrompng($file);
			break;
		default:
			return false;
	}

	$max_resolution = 48;

	if ($width > $height)
	{
		$new_width = $max_resolution;
		$new_height = $height*($new_width/$width);
	}
	else 
	{
		$new_height = $max_resolution;
		$new_width = $width*($new_height/$height);
	}
	
	$image_new = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($image_new, $image_big, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	ob_start();
	imagepng($image_new);
	$content = ob_get_clean();
	return $content;
}

/* Its much faster test all files only one time */
if(strlen($current_config['filemanager_antivirus_command']) > 0)
{
	$command = "nice -n19 ".$current_config['filemanager_antivirus_command'];
	for($i = 0; $i != $show_upload_boxes; ++$i)
	{
		$command .= " ".$_FILES['upload_file']['tmp_name'][$i];
	}
	
	$return = 0;
	
	exec("bash -c ".escapeshellcmd(escapeshellarg($command)),$output,$return);
	
	if ($return == 1)
	{
		$_SESSION['response'] = serialize(array(0 => lang('Error:').lang('One of the files sent was considered infected')));
		return;
	}
}

if( $path != '/' )
{
	$return = array( );
	for( $i = 0; $i != $show_upload_boxes; ++$i)
	{
		if ( $_FILES['upload_file']['error'][$i] !== 0 )
		{
			$return[] = array( 
					"file"		=> $_FILES['upload_file']['name'][$i] ,
					"filesize"	=> 'filesize #' . $_FILES['upload_file']['error'][$i]
			 );
			continue;
		}
		elseif ( $_FILES['upload_file']['size'][$i] > ($upload_max_size*1024*1024) )
		{
			$return[] = array( 
								"file"		=> $_FILES['upload_file']['name'][$i] ,
								"size"		=> $_FILES['upload_file']['size'][$i] ,
								"size_max"	=> ($upload_max_size*1024*1024) 
			 );
			continue;
		}
		elseif( $_FILES['upload_file']['size'][$i] > 0 )
		{
			$badchar = $bo->bad_chars( $_FILES['upload_file']['name'][$i], True, True );

			if( $badchar )
			{
				$return[] = array(
								"file"		=> $_FILES['upload_file']['name'][$i],
								"badchar"	=> lang('File names cannot contain "%1"', $badchar)
				);					
				continue;
			}

			# Check to see if the file exists in the database, and get its info at the same time
			$ls_array = $bo->vfs->ls(array(
						'string'=> $path . '/' . $_FILES['upload_file']['name'][$i],
						'relatives'	=> array(RELATIVE_NONE),
						'checksubdirs'	=> False,
						'nofiles'	=> True
			));

			$fileinfo = $ls_array[0];

			if( $fileinfo['name'] )
			{
				if( $fileinfo['mime_type'] == 'Directory' )
				{
					$return[] = array(
										"file"		=> $_FILES['upload_file']['name'][$i],
										"directory"	=> lang('Cannot replace %1 because it is a directory', $fileinfo['name'] )
					);
					continue;
				}
			}

			if($fileinfo['name'] && $fileinfo['deleteable'] != 'N')
			{
				$FILE_ORIGINAL = $_FILES['upload_file']['name'][$i];
				$_FILES['upload_file']['name'][$i] = date('Ymd-H:i')."-".$_FILES['upload_file']['name'][$i];
				$tmp_arr=array(
					'from'	=> $_FILES['upload_file']['tmp_name'][$i],
					'to'	=> $_FILES['upload_file']['name'][$i],
					'relatives'	=> array(RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)

				);
				$bo->vfs->cp($tmp_arr);
				$tmp_arr=array(
						'string'		=> $_FILES['upload_file']['name'][$i],
						'relatives'		=> array(RELATIVE_ALL),
						'attributes'	=> array(
						'owner_id' 		=> $bo->userinfo['username'],
						'modifiedby_id' => $bo->userinfo['username'],
						'size' 			=> $_FILES['upload_file']['size'][$i],
						'mime_type'		=> $_FILES['upload_file']['type'][$i],
						'deleteable' 	=> 'Y',
						'comment'		=> stripslashes($_POST['upload_comment'][$i])
					)
				);
				$bo->vfs->set_attributes($tmp_arr);

				$return[] = array(
								"file"		=> $FILE_ORIGINAL,									
								"undefined"	=> lang( "There is a file %1, that was not replaced", $FILE_ORIGINAL )
				);
			}
			else
			{
				if ($bo->vfs->cp(array(
					'from'=> $_FILES['upload_file']['tmp_name'][$i],
					'to'=> $_FILES['upload_file']['name'][$i],
					'relatives'	=> array(RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)
				)))
				{
					$bo->vfs->set_attributes(array(
							'string'		=> $_FILES['upload_file']['name'][$i],
							'relatives'	=> array(RELATIVE_ALL),
							'attributes'	=> array(
							'mime_type'	=> $_FILES['upload_file']['type'][$i],
							'comment'	=> stripslashes($_POST['upload_comment'][$i])
							)
					));
				}
				else
				{
					$return[] = array ( 
										"file"		=> $_FILES['upload_file']['name'][$i],
										"sendFile"	=> lang('It was not possible to send your file')
					);
				}
			}
		}
		elseif( $_FILES['upload_file']['name'][$i] )
		{
			$bo->vfs->touch(array(
				'string'=> $_FILES['upload_file']['name'][$i],
				'relatives'	=> array(RELATIVE_ALL)
			));

			$bo->vfs->set_attributes(array(
					'string'		=> $_FILES['upload_file']['name'][$i],
					'relatives'		=> array(RELATIVE_ALL),
					'attributes'	=> array(
					'mime_type'	=> $_FILES['upload_file']['type'][$i],
					'comment'	=> stripslashes($_POST['upload_comment'][$i])
					)
			));
		}

		if ( !(strpos(strtoupper($_FILES['upload_file']['type'][$i]),'IMAGE') === FALSE ) )
		{
			$content = create_summaryImage($_FILES['upload_file']['tmp_name'][$i]);
			if ($content)
			{
				$bo->vfs->set_summary(array(
					'string'=> $_FILES['upload_file']['name'][$i],
					'relatives' => array(RELATIVE_ALL),
					'summary'=> $content
				));
			}

		}
	}
}

if( count($notifUser) > 0 )
{
	define('PHPGW_INCLUDE_ROOT','../../');
	define('PHPGW_API_INC','../../phpgwapi/inc');
	include_once(PHPGW_API_INC.'/class.phpmailer.inc.php');
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$boemailadmin = CreateObject('emailadmin.bo');
	$emailadmin_profile = $boemailadmin->getProfileList();
	$emailadmin = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
	$mail->Host = $emailadmin['smtpServer'];
	$mail->Port = $emailadmin['smtpPort'];
	$mail->From = $GLOBALS['phpgw']->preferences->values['email'];
	$mail->FromName = $GLOBALS['phpgw_info']['user']['fullname'];
	$mail->IsHTML(true);
	
	foreach( $notifUser as $userMail )
	{
		$mail->AddAddress($userMail);
		$mail->Subject = lang("Filemanager notification");
		
		$body  = "<div style='font-size: 9pt !important;'>"; 
		$body .= lang("The user %1 sent the following files", "<span style='font-weight: bold;'>" . $GLOBALS['phpgw_info']['user']['fullname'] . "</span>") . "<br/><br/>";

		foreach( $filesUpload['name'] as $key => $name ) 
			$body .= "<div style='font-weight: bold;'> - " . $name ." ( " . $filesUpload['type'][$key] . " )</div>";

		$body  .= "<div style='margin-top:25px;'>".lang("To view the files %1", "<a href='../filemanager/index.php'>".lang("Click here")."</a>")."</div>";
		$body  .= "</div>";
		
		$mail->Body = $body;
		
		if( !$mail->Send() )
		{
			$return[] = $mail->ErrorInfo;
		}
	}
	
	unset( $filesUpload );
	
}

$_SESSION['response'] = ( count($return) > 0 ) ? serialize($return) : serialize( array( 0 => 'Ok' ) ); 

?>