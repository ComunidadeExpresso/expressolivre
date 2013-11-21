<?php
	/**************************************************************************\
	* eGroupWare - Filemanager Preferences                                     *
	* http://egroupware.org                                                    *
	* Modified by Pim Snel <pim@egroupware.org>                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option)                                                                 *
	\**************************************************************************/

	//ExecMethod('filemanager.bofilemanager.check_set_default_prefs');

	create_section('Display attributes');

	$size=array(
		"10"=>"10",
		"25"=>"25",
		"50"=>"50",
		"100"=>"100",
		"200"=>"200"
	);
	create_select_box('Number of files per pager','files_per_page',$size);

	$file_attributes = Array(
		'name' => 'File Name',
		'mime_type' => 'MIME Type',
		'size' => 'Size',
		'created' => 'Created',
		'modified' => 'Modified',
		'owner' => 'Owner',
		'createdby_id' => 'Created by',
		'modifiedby_id' => 'Created by',
		'modifiedby_id' => 'Modified by',
		'app' => 'Application',
		'comment' => 'Comment',
		'version' => 'Version'
	);

	while (list ($key, $value) = each ($file_attributes))
	{
		create_check_box($value,$key);
	}
	
	create_section('Other settings');

	$other_checkboxes = array (
		"viewtextplain" => "Unknown MIME-type defaults to text/plain when viewing"
	);

	while (list ($key, $value) = each ($other_checkboxes))
	{
		create_check_box($value,$key);
	}

	$type=array(
		"portrait"=>lang("portrait"),
		"landscape"=>lang("landscape")
	);
	create_select_box('Disposition of pdf output','pdf_type',$type);

	$paper=array(
		"letter"=>lang("letter"),
		"a4"=>"A4",
		"US Legal"=>"US Legal"
	);
	create_select_box('Type of pdf paper','pdf_paper_type',$paper);


