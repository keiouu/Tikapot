<?php
/**
 * Tikapot Profiler
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/signal_manager.php");
require_once(home_dir . "framework/models.php");
require_once(home_dir . "framework/model_fields/init.php");

/**
 * A hook to add database queries to the profiler
 * 
 * @internal 
 * @param  array $dbargs The arguments given by the database driver
 * @return null
 */
function profiler_db_query_hook($dbargs) {
	list ($query, $args) = $dbargs;
	ProfileData::$db_total_queries++;
	if (!isset(ProfileData::$db_queries[$query]))
		ProfileData::$db_queries[$query] = 0;
	ProfileData::$db_queries[$query]++;
}

if (debug) {
	SignalManager::hook("on_db_query", "profiler_db_query_hook");
}

/**
 * A block of ProfileData contains data for the profiler to use
 *
 * @internal
 */
class ProfileData
{
	public static 	/** The total number of queries sent to the database */ $db_total_queries = 0,
					/** Each query sent to the database                  */ $db_queries = array();

	private /** The name of this profiler block */ $name,
			/** The time this block started     */ $start_time = 0,
			/** The time this block ended       */ $end_time = 0;
	
	/**
	 * Construct
	 *
	 * @param $name The name of this profiler block
	 */
	public function __construct($name) {
		$this->name = $name;
	}
	
	/**
	 * Returns the start time for this block
	 *
	 * @return float The start time of this block
	 */
	public function get_start_time() {
		return $this->start_time;
	}
	
	/**
	 * Returns the end time for this block
	 *
	 * @return float The end time of this block
	 */
	public function get_end_time() {
		return $this->end_time;
	}
	
	/**
	 * Returns the duration for this block
	 *
	 * @return float The duration of this block
	 */
	public function get_duration() {
		return $this->end_time - $this->start_time;
	}
	
	/**
	 * Causes this block's start time to be set to the current microtime
	 */
	public function start() {
		$this->start_time = microtime(True);
	}
	
	/**
	 * Stop's this block counting, sets the end time
	 *
	 * @return float The duration of this block
	 */
	public function stop() {
		if ($this->end_time === 0)
			$this->end_time = microtime(True);
		return $this->get_duration();
	}
	  
	/**
	 * To String
	 *
	 * @return string The duration of the block followed by 's'
	 */
	public function __toString() {
		$string = $this->name . ": ";
		if ($this->end_time === 0)
			return $string . $GLOBALS['i18n']['framework']['profiler_unclosed'];
		return $string . $this->get_duration() . ' ' . $GLOBALS['i18n']['framework']['profiler_seconds'];
	}
}

/**
 * Tikapot Profiler
 *
 * Usage:
 *   $id = Profiler::start("random name");
 *      // code you would like to profile
 *   Profiler::end("same random name", $id);
 *
 */
abstract class Profiler
{
	private static /** Every block registered to the Profiler */ $blocks = array();
 
	/**
	 * Start a profiler block
	 *
	 * @static
	 * @param string $block The name of the block
	 * @return integer A unique id for your block (to be used with "end")
	 */
	public static function start($block) {
		if (!isset(Profiler::$blocks[$block]))
			Profiler::$blocks[$block] = array();
		$block_obj = new ProfileData($block);
		$id = count(Profiler::$blocks[$block]);
		Profiler::$blocks[$block][$id] = $block_obj;
		$block_obj->start();
		return $id;
	}
	
	/**
	 * End a profiler block
	 *
	 * @static
	 * @param string $block The name of the block to end
	 * @param integer $id The id of the block to end (optional)
	 * @return float The duration of this block
	 */
	public static function end($block, $id = 0) {
		$block_obj = Profiler::$blocks[$block][$id];
		return $block_obj->stop();
	}
	
	/**
	 * Get all blocks
	 *
	 * @static
 	 * @return array The blocks of the profiler
	 */
	public static function get_blocks() {
		return Profiler::$blocks;
	}
	
	/**
	 * Get the number of times a given block was called
	 *
	 * @static
	 * @param string $block The name of the block
	 * @return integer The number of times a given block was called
	 */
	public static function get_call_count($block) {
		return count(Profiler::$blocks[$block]);
	}
	
	/**
	 * Returns the total time spent in a given block
	 *
	 * @static
	 * @param string $block The name of the block
	 * @return integer The total duration of a block, for all call counts
	 */
	public static function get_total($block) {
		$total = 0;
		foreach (Profiler::$blocks[$block] as $id => $obj)
			if ($obj->get_end_time() > 0)
				$total += $obj->get_duration();
		return $total;
	}
	
	/**
	 * Returns the average time spent in a given block
	 *
	 * @static
	 * @param string $block The name of the block
	 * @return integer The average duration of a block
	 */
	public static function get_average($block) {
		return Profiler::get_total($block) / Profiler::get_call_count($block);
	}
}
?>

