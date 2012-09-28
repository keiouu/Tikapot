<?php
/**
 * An internal Error Handler
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/utils.php");

/**
 * Tikapot Error Handler
 *
 * @internal
 * @param  int $errno   The error number
 * @param  string $errstr  Error string
 * @param  string $errfile The file the error occurred in
 * @param  int $errline The line the error occurred on
 * @return boolean          true or false
 */
function tpErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // TODO - separate these out better
    // TODO - add hidden stack traces?
    switch ($errno) {
	    case E_PARSE:
	    case E_ERROR:
	    case E_USER_ERROR:
	    case E_COMPILE_ERROR:
	    case E_CORE_ERROR:
	    		die(strip_tags("[" . $errfile . ":".$errline."] " . $errstr));
	    		// TODO: full page error
	        break;
	    
	    case E_COMPILE_WARNING:
	    case E_NOTICE:
	    case E_WARNING:
	    case E_DEPRECATED:
	    case E_STRICT:
	    case E_RECOVERABLE_ERROR:
	    case E_USER_NOTICE:
	    case E_USER_WARNING:
	    case E_USER_DEPRECATED:
	    		console_warning(strip_tags("[" . $errfile . ":".$errline."] " . $errstr));
	    	break;
	
	    default:
        	break;
    }

    return true;
}
