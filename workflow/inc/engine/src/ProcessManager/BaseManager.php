<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');

/**
 * This class is derived by all the API classes so they get the
 * database connection and the database methods.
 *
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class BaseManager extends Base {

  /**
   * Constructor
   * 
   * @param object &$db ADOdb
   * @return object BaseManager
   * @access public
   */
  function BaseManager()
  {
    $this->child_name = 'BaseManager';
    parent::Base();
  }

}
?>
