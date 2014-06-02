<?php
/*
 * Copyright 2005 - 2013  Zarafa B.V.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation with the following additional
 * term according to sec. 7:
 *
 * According to sec. 7 of the GNU Affero General Public License, version
 * 3, the terms of the AGPL are supplemented with the following terms:
 *
 * "Zarafa" is a registered trademark of Zarafa B.V. The licensing of
 * the Program under the AGPL does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks remain
 * entirely with us.
 *
 * However, if you propagate an unmodified version of the Program you are
 * allowed to use the term "Zarafa" to indicate that you distribute the
 * Program. Furthermore you may use our trademarks where it is necessary
 * to indicate the intended purpose of a product or service provided you
 * use it in accordance with honest practices in industrial or commercial
 * matters.  If you want to propagate modified versions of the Program
 * under the name "Zarafa" or "Zarafa Server", you may only do so if you
 * have a written permission by Zarafa B.V. (to acquire a permission
 * please contact Zarafa at trademark@zarafa.com).
 *
 * The interactive user interface of the software displays an attribution
 * notice containing the term "Zarafa" and/or the logo of Zarafa.
 * Interactive user interfaces of unmodified and modified versions must
 * display Appropriate Legal Notices according to sec. 5 of the GNU
 * Affero General Public License, version 3, when you propagate
 * unmodified or modified versions of the Program. In accordance with
 * sec. 7 b) of the GNU Affero General Public License, version 3, these
 * Appropriate Legal Notices must retain the logo of Zarafa or display
 * the words "Initial Development by Zarafa" if the display of the logo
 * is not reasonably feasible for technical reasons. The use of the logo
 * of Zarafa in Legal Notices is allowed for unmodified and modified
 * versions of the software.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


/**
 * Defines a base exception class for all custom exceptions, so every exceptions that
 * is thrown/caught by this application should extend this base class and make use of it.
 * it removes some peculiarities between different versions of PHP and exception handling.
 *
 * Some basic function of Exception class
 * getMessage()- message of exception
 * getCode() - code of exception
 * getFile() - source filename
 * getLine() - source line
 * getTrace() - n array of the backtrace()
 * getTraceAsString() - formated string of trace
 */
class BaseException extends Exception
{
    /**
     * Reference of previous exception, only used for PHP < 5.3
     * can't use $previous here as its a private variable of parent class
     */
    private $_previous = null;

    /**
     * Base name of the file, so we don't have to use static path of the file
     */
    private $baseFile = null;

    /**
     * Flag to check if exception is already handled or not
     */
    public $isHandled = false;

    /**
     * The exception message to show at client side.
     */
    public $displayMessage = null;

    /**
     * Construct the exception
     *
     * @param  string $errorMessage
     * @param  int $code
     * @param  Exception $previous
     * @param  string $displayMessage
     * @return void
     */
    public function __construct($errorMessage, $code = 0, Exception $previous = null, $displayMessage = null) {
        // assign display message
        $this->displayMessage = $displayMessage;

        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($errorMessage, (int) $code);

            // set previous exception
            if(!is_null($previous)) {
                $this->_previous = $previous;
            }
        } else {
            parent::__construct($errorMessage, (int) $code, $previous);
        }
    }

    /**
     * Overloading of final methods to get rid of incompatibilities between different PHP versions.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if ('getprevious' == strtolower($method)) {
            return $this->_getPrevious();
        }

        return null;
    }

    /**
     * @return string returns file name and line number combined where exception occured.
     */
    public function getFileLine()
    {
        return $this->getBaseFile() . ':' . $this->getLine();
    }

    /**
     * @return string returns message that should be sent to client to display
     */
    public function getDisplayMessage()
    {
        if(!is_null($this->displayMessage)) {
            return $this->displayMessage;
        }

        return $this->getMessage();
    }

    /**
     * Function sets display message of an exception that will be sent to the client side
     * to show it to user.
     * @param string $message display message.
     */
    public function setDisplayMessage($message)
    {
        $this->displayMessage = $message;
    }

    /**
     * Function sets a flag in exception class to indicate that exception is already handled
     * so if it is caught again in the top level of function stack then we have to silently
     * ignore it.
     */
    public function setHandled()
    {
        $this->isHandled = true;
    }

    /**
     * @return string returns base path of the file where exception occured.
     */
    public function getBaseFile()
    {
        if(is_null($this->baseFile)) {
            $this->baseFile = basename(parent::getFile());
        }

        return $this->baseFile;
    }

    /**
     * Function will check for PHP version if it is greater than 5.3 then we can use its default implementation
     * otherwise we have to use our own implementation of chanining functionality.
     *
     * @return Exception returns previous exception
     */
    public function _getPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            return $this->_previous;
        } else {
            return parent::getPrevious();
        }
    }

    /**
     * String representation of the exception, also handles previous exception.
     *
     * @return string
     */
    public function __toString()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if (($e = $this->getPrevious()) !== null) {
                return $e->__toString()
                        . "\n\nNext "
                        . parent::__toString();
            }
        }

        return parent::__toString();
    }

    /**
     * Name of the class of exception.
     *
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    // @TODO getTrace and getTraceAsString
}
?>