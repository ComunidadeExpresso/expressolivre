<?php
   /**************************************************************************\
   * eGroupWare                                                               *
   * http://www.egroupware.org                                                *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/


   $GLOBALS['phpgw_info'] = array();
   $GLOBALS['phpgw_info']['flags']['currentapp'] = 'about';
   $GLOBALS['phpgw_info']['flags']['disable_Template_class'] = True;
   $GLOBALS['phpgw_info']['flags']['noheader'] = True;
   include('header.inc.php');

   $app = $_GET['app'];
   if ($app)
   {
	  if (!($included = $GLOBALS['phpgw']->hooks->single('about',$app)))
	  {
		 function about_app()
		 {
			global $app;
			$icon = $GLOBALS['phpgw']->common->image($app,array('navbar','nonav'));
			include (PHPGW_INCLUDE_ROOT . "/$app/setup/setup.inc.php");
			$info = $setup_info[$app];
			$info['title'] = $GLOBALS['phpgw_info']['apps'][$app]['title'];
			$other_infos = array(
			   'author'     => lang('Author'),
			   'maintainer' => lang('Maintainer'),
			   'version'    => lang('Version'),
			   'license'    => lang('License')
			);

			$s = "<table width='70%' cellpadding='4'>\n<tr>
				  <td align='left'><img src='$icon' alt=\"$info[title]\" /></td><td align='left'><h2>{$info['title']}</h2></td></tr>";

			   if ($info['description'])
			   {
				  $info['description'] = lang($info['description']);
				  $s .= "<tr><td colspan='2' align='left'>{$info['description']}</td></tr>\n";
				  if ($info['note'])
				  {
					 $info['note'] = lang($info['note']);
					 $s .= "<tr><td colspan='2' align='left'><i>{$info['note']}</i></td></tr>\n";
				  }

			   }
			   

			   foreach ($other_infos as $key => $val)
			   {
				  if (isset($info[$key]))
				  {
					 $s .= "<tr><td width='1%' align='left'>$val</td><td>";
						   $infos = $info[$key];
						   for ($n = 0; is_array($info[$key][$n]) && ($infos = $info[$key][$n]) || !$n; ++$n)
						   {
							  if (!is_array($infos) && isset($info[$key.'_email']))
							  {
								 $infos = array('email' => $info[$key.'_email'],'name' => $infos);
							  }
							  if (is_array($infos))
							  {
								 $names = explode('<br>',$infos['name']);
								 $emails = preg_split('/@|<br>/',$infos['email']);
								 if (count($names) < count($emails)/2)
								 {
									$names = '';
								 }
								 $infos = '';
								 while (list($user,$domain) = $emails)
								 {
									if ($infos) $infos .= '<br>';
									$name = $names ? array_shift($names) : $user;
									$infos .= "<a href='mailto:$user at $domain'><span onClick=\"document.location='mailto:$user'+'@'+'$domain'; return false;\">$name</span></a>";
									array_shift($emails); array_shift($emails);
								 }
							  }
							  $s .= ($n ? '<br>' : '') . $infos;
						   }
						   $s .= "</td></tr>\n";
				  }
			   }

			   if ($info['extra_untranslated'])
			   {
				  $s .= "<tr><td colspan='2' align='left'>{$info['extra_untranslated']}</td></tr>\n";
			   }
			   
			   $s .= "</table>\n";

			return $s;
		 }
		 $api_only = !($included = file_exists(PHPGW_INCLUDE_ROOT . "/$app/setup/setup.inc.php"));
	  }
   }
   else
   {
	  $api_only = True;
   }

   $tpl = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir('phpgwapi'));
   $tpl->set_file(array(
	  'phpgw_about'         => 'about.tpl',
	  'phpgw_about_unknown' => 'about_unknown.tpl'
   ));

   $tpl->set_var('phpgw_logo',$GLOBALS['phpgw']->common->image('phpgwapi','logo.gif'));
   $tpl->set_var('phpgw_version',lang('eGroupWare API version %1',$GLOBALS['phpgw_info']['server']['versions']['phpgwapi']));
   $tpl->set_var('phpgw_message',lang('%1eGroupWare%2 is a multi-user, web-based groupware suite written in %3PHP%4.',
   '<a href="http://www.eGroupWare.org" target="_blank">','</a>','<a href="http://www.php.net" target="_blank">','</a>'));

   if ($included)
   {
	  $tpl->set_var('phpgw_app_about',about_app('',''));
	  //about_app($tpl,"phpgw_app_about");
   }
   else
   {
	  if ($api_only)
	  {
		 $tpl->set_var('phpgw_app_about','');
	  }
	  else
	  {
		 $tpl->set_var('app_header',$app);
		 $tpl->parse('phpgw_app_about','phpgw_about_unknown');
	  }
   }

   $title = isset($GLOBALS['phpgw_info']['apps'][$app]) ? $GLOBALS['phpgw_info']['apps'][$app]['title'] : 'eGroupWare';
   $GLOBALS['phpgw_info']['flags']['app_header'] = lang('About %1',$title);
   $GLOBALS['phpgw']->common->phpgw_header();
   $tpl->pparse('out','phpgw_about');
   $GLOBALS['phpgw']->common->phpgw_footer();
?>
