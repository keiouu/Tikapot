<?php
/*
 * Tikapot's Modified Version of a Radix Data Structure
 *
 * Its called an "a" Radix due to its ability to
 * return both a value, and arguments for that value 
 * that it picks up along the way.
 *
 * Used for URL handling and regex url argument extraction
 *
 */
 
require_once(home_dir . "framework/utils.php");

class NotFoundException extends Exception {}
class InvalidTrieQueryException extends Exception {}
class ZeroQueryExcpetion extends Exception {}

class ARadix
{
	private $value, $res, $branches = array();
	
	public function __construct($val = "root", $res = NULL) {
		$this->value = $val;
		$this->res = $res;
	}
	
	public function is_root() {
		return $this->value == "root";
	}
	
	public function get() {
		return $this->value;
	}
	
	public function get_resource() {
		return $this->res;
	}
	
	public function set_resource($res) {
		$this->res = $res;
	}
	
	public function size() {
		$size = 1;
		foreach ($this->children() as $val => $child)
			$size += $child->size();
		return $size;
	}
	
	public function children() {
		return $this->branches;
	}
	
	public function add($trie) {
		$key = $trie->get();
		if (isset($this->branches[$key])) {
			foreach ($trie->children() as $child) {
				$this->branches[$key]->add($child);
			}
			if ($trie->get_resource() !== null)
				$this->branches[$key]->set_resource($trie->get_resource());
		} else {
			$this->branches[$key] = $trie;
		}
	}
	
	public function match($value) {
		if (preg_match("/" . $this->value . "/", $value, $matches))
			return $matches;
		return false;
	}
	
	public function is_regex() {
		return starts_with($this->value, "(") && ends_with($this->value, ")");
	}
	
	/**
	 * Get one (or all) branches whose key matches $q
	 */
	public function get_branch($q, $multipleMatch = false) {
		$objects = array();
		if (isset($this->branches[$q])) {
			$objects[] = $this->branches[$q];
			if (!$multipleMatch)
				return $objects[0];
		}
		foreach ($this->children() as $val => $branch) {
			if ($branch->is_regex()) {
				if ($branch->match($q)) {
					$objects[] = $branch;
					if (!$multipleMatch)
						return $branch;
				}
			}
		}
		if (count($objects) > 0)
			return $objects;
		return null;
	}
	
	public function has_branch($q) {
		return $this->get_branch($q) !== null;
	}
	
	/*
	 * $query = array("posts", "(?P<name>\w+)");
	 * returns array(View, [args])
	 */
	public function query($query, $args = array()) {
		if (!is_array($query)) throw new InvalidTrieQueryException();
		$next = $query[0];
		
		// Five cases here:
		//    - There is no next node
		//    - It is a regex branch
		//    - This is the final node ($query has one element)
		//    - There is a next node
		//    - There are multiple possible next nodes (regex and hardcoded)
		//      hardcoded take preference, unless it does not have the required
		//      child elements where the regex branch does
		
		$branch = $this->get_branch($next);
		if ($branch === null) {
			// Case 1: There is no next node
			throw new NotFoundException();
		}
		
		if ($branch->is_regex()) {
			// Case 2: It is a regex branch
			$args = array_merge_recursive($args, $branch->match($next));
		}
		
		// Case 3: This is the final node
		if (count($query) == 1) {
			return array($branch->get_resource(), $args);
		}
		
		// Case 4: There is a next node
		if (count($query) > 1) {
			try {
				$result = $branch->query(array_slice($query, 1), $args);
			} catch (NotFoundException $e) {
				$branches = $this->get_branch($next, true);
				if (count($branches) <= 1)
					throw new NotFoundException();
				// Case 5 - There are multiple possible next nodes
				foreach ($branches as $branch) {
					$branch_args = $args;
					if ($branch->is_regex())
						$branch_args = array_merge_recursive($branch_args, $branch->match($next));
					try {
						$result = $branch->query(array_slice($query, 1), $branch_args);
						break;
					} catch (NotFoundException $e) {
						$result = null;
					}
				}
			}
			if ($result == null)
				throw new NotFoundException();
			return $result;
		}
		
		// Query somehow hit length 0
		throw new ZeroQueryExcpetion();
	}
	
	public function print_radix($depth = 0) {
		for ($i = 0; $i < $depth; $i++)
			print "\t";
		print "ARadix (";
		print $this->get();
		if ($this->res)
			print " (".$GLOBALS['i18n']['framework']["hasres"].")";
		foreach ($this->children() as $val => $branch) {
			print "\n";
			$branch->print_radix($depth + 1);
		}
		if (count($this->children()) > 0) {
			print "\n";
			for ($i = 0; $i < $depth; $i++)
				print "\t";
		}
		print ")";
		if ($this->is_root())
			print "\n";
	}
}






?>
