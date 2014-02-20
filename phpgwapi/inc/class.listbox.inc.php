<?php
  /**************************************************************************\
  * eGroupWare API - Link box generator                                      *
  * http://www.egroupware.org/api                                            *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * Creates listboxes using templates                                        *
  * Copyright (C) 2000, 2001 Mark Peters                                     *
  * -------------------------------------------------------------------------*
  * This library is part of the eGroupWare API                               *
  * http://www.egroupware.org/api                                            * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/


	CreateObject('phpgwapi.portalbox');

	class listbox extends portalbox
	{
		/*
		 Set up the Object. You will notice, we have not reserved
		 memory space for variables. In this circumstance it is not necessary.
		 */

		/*
		 This is the constructor for the listbox. The only thing this does
		 is to call the constructor of the parent class. Why? Well, whilst
		 PHP manages a certain part of OO, one of the bits it falls down on
		 (at the moment) is constructors within sub-classes. So, to
		 be sure that the sub-class is instantiated with the constructor of
		 the parent class, I simply call the parent constructor. Of course,
		 if I then wanted to override any of the values, I could easily do so.
		*/
		function listbox($param)
		{
			$this->setvar('classname','listbox');
			$this->setvar('outerwidth',300);
			$this->setvar('innerwidth',300);
			$this->setvar('width',300);

			@reset($param);
			while(list($key,$value) = each($param))
			{
				if($key != 'title' && $key != 'primary' && $key != 'secondary' && $key != 'tertiary')
				{
//echo 'Setting '.$key.':'.$value."<br />\n";
					$this->setvar($key,$value);
				}
			}
			$this->portalbox($param['title'], $param['primary'], $param['secondary'], $param['tertiary']);
			$this->start_template();
		}

		/*
		 This is the only method within the class. Quite simply, as you can see
		 it draws the table(s), placing the required data in the appropriate place.
		*/
		function draw($extra_data='')
		{
			if(count($this->data))
			{
                $this->p->parse('row','portal_listbox_header',True);
                $data_count = count($this->data);
				for ($x = 0; $x < $data_count; ++$x)
				{
					$var = Array(
						'text'	=> $this->data[$x]['text'],
						'link'	=> $this->data[$x]['link']
					);
					$this->p->set_var($var);
					$this->p->parse('row','portal_listbox_link',True);
				}
				$this->p->parse('row','portal_listbox_footer',True);
			}
			$this->set_internal($extra_data);
			return $this->draw_box();
		}
	}
