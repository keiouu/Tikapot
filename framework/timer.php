<?php
/**
 * Tikapot Timer Class
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

/**
 * A simple timer
 */
class Timer
{
	private static /** The current number of timers */ $current_uid = 0;
	private /** This timer's UID */ $uid = 0, 
			/** The start time of the timer */ $start_time = 0, 
			/** The time the timer ended */ $end_time = 0;
	
	/**
	 * Construct a new timer with a given start time
	 * 
	 * @param int $microtime The current time, or null if we should use the current time
	 */
	private function __construct($microtime = NULL) {
		$this->uid = self::$current_uid;
		if ($microtime != NULL)
			$this->start_time = $microtime;
		else
			$this->start_time = microtime(True);
		self::$current_uid++;
	}
	
	/**
	 * Returns the current ping rounded to 4dp
	 * 
	 * @return string Current timer duration
	 */
	public function __toString() {
		return "" . round($this->ping(), 4);
	}
	
	/**
	 * Starts a new timer and returns it
	 * @return Timer A timer object
	 */
	public static function start() {
		return new Timer();
	}
	
	/**
	 * Starts a new timer with a base time and returns it
	 * 
	 * @param  int $microtime Microtime (microtime(true))
	 * @return Timer A timer object
	 */
	public static function startAt($microtime) {
		return new Timer($microtime);
	}
	
	/**
	 * Returns the current time on the timer without ending it
	 * 
	 * @return int The current duration of this timer
	 */
	public function ping() {
		if ($this->end_time > 0) return $this->end_time;
		return microtime(True) - $this->start_time;
	}
	
	/**
	 * Returns the current time on the timer, ending it causing future calls to ping() to return the time at the point of the stop() call.
	 * 
	 * @return int The duration of the timer
	 */
	public function stop() {
		$this->end_time = $this->ping();
		return $this->end_time;
	}
}

