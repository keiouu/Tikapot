<?php
/**
 * Tikapot Signal Manager
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

/**
 * Signal Exception, occurs when something goes wrong in the signal manager
 */
class SignalException extends Exception {}

/**
 * A collator and signaler.
 * Accepts registrations and signups of signals and fires them when appropriate.
 */
class SignalManager
{
	private static /** Contains all signals */ $signals = array();
	
	/**
	 * Register a list of signals.
	 * Usage: SignalManager::register("signal1", "signal2");
	 * 
	 * @return null
	 */
	public static function register() {
		$arg_list = func_get_args();
   		foreach ($arg_list as $signal) {
			if (isset(SignalManager::$signals[$signal]))
				throw new SignalException($GLOBALS['i18n']['framework']["sigerr1"] . " " . $signal);
			SignalManager::$signals[$signal] = array();
		}
	}
	
	/**
	 * Hook a function with a given weighting
	 * 
	 * @param  string  $signal    The name of the signal to hook
	 * @param  mixed  $function  A string or anonymous function to call
	 * @param  Object  $obj       An object on which the function resides (if any)
	 * @param  integer $weighting Lower values will be called last, higher values called first.
	 * @return null
	 */
	public static function hook($signal, $function, $obj = Null, $weighting = 50) {
		if (!isset(SignalManager::$signals[$signal]))
			throw new SignalException($GLOBALS['i18n']['framework']["sigerr2"] . " " . $signal);
			
		SignalManager::$signals[$signal][] = array($obj, $function, $weighting);
	}
	
	/**
	 * Fire off a signal
	 * 
	 * @param  string  $signal    The name of the signal to fire
	 * @param  Object  $obj       An object on which the function resides (if any)
	 * @return null
	 */
	public static function fire($signal, $obj = Null) {
		if (!isset(SignalManager::$signals[$signal]))
			throw new SignalException($GLOBALS['i18n']['framework']["sigerr2"] . " " . $signal);
		
		// Sort array by weighting
		usort(SignalManager::$signals[$signal], create_function('$a,$b', 'return $a[2] < $b[2];'));
		
		foreach (SignalManager::$signals[$signal] as $array) {
			list($object, $function, $weighting) = $array;
			if ($object)
				if(method_exists($object, $function))
					call_user_func_array(array($object, $function), array($obj));
				else
					throw new SignalException($GLOBALS['i18n']['framework']["sigerr3"] . " " . $function);
			else
				$function($obj);
		}
	}
}
