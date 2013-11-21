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

/**
 * This class is temporary. We just need to use it until all the
 * processes are updated and using our new static factory. The
 * only purpose of this class is to forward the calls to our
 * frontend factory class. Formally, it's an Adapter Design Pattern.
 *
 * @package Factory
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Pedro Eugênio Rocha - pedro.eugenio.rocha@gmail.com
 */
class ProcessWrapperFactory
{

	/**
	 * Just forward the call.
	 *
	 * @access public
	 * @return object
	 */
	public function &getInstance() {

		$args = func_get_args();

		/**
		 * To save memory and processing, we store caches of objects instantiated
		 * by the factory. When we use this beautiful function (call_user_func_array),
		 * it makes an unexpected copy of the object. Of course we don't want it.
		 * For now, we will keep doing copies.
		 */
		return call_user_func_array(array(Factory, "getInstance"), $args);

	}


	/**
	 * Just forward the call.
	 *
	 * @access public
	 * @return object
	 */
	public function &newInstance() {

		$args = func_get_args();

		/* read the comment above */
		return call_user_func_array(array(Factory, "newInstance"), $args);

	}
}
?>
