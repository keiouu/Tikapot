<?php
/*
 * Tikapot Date Field
 *
 */

require_once(home_dir . "framework/model_fields/modelfield.php");

class DateField extends ModelField
{
	public static $FORMAT = "Y-m-d";
	protected static $db_type = "date";
	private $auto_now_add = False, $auto_now = False;
	
	public function __construct($auto_now_add = False, $auto_now = False, $default = "", $_extra = "") {
		parent::__construct($default, $_extra);
		$this->auto_now_add = $auto_now_add;
		$this->auto_now = $auto_now;
	}
	
	public function get_date() {
		return date(DateField::$FORMAT, strtotime($this->get_value()));
	}
	
	public function get_readable_value() {
		$date1 = new DateTime($this->get_date());
		$date2 = new DateTime("now");
		$interval = $date1->diff($date2);
		$time = "Just Now";
		if ($interval->y > 0)
			$time = $interval->y . " year".($interval->y > 1 ? "s" : "")." ago";
		elseif ($interval->m > 0)
			$time = $interval->m . " month".($interval->m > 1 ? "s" : "")." ago";
		elseif ($interval->d > 0)
			$time = $interval->d . " day".($interval->d > 1 ? "s" : "")." ago";
		return $time;
	}
	
	public function get_formfield($name) {
		return new DateFormField($name, $this->get_value());
	}
	
	/* This recieves pre-save signal from it's model. */
	public function pre_save($model, $update) {
		if ($this->auto_now || (!$update && $this->auto_now_add))
			$this->value = date(static::$FORMAT, time());
	}
	
	public function sql_value($db, $val = NULL) {
		$val = ($val === NULL) ? $this->get_value() : $val;
		return (strlen($val) > 0) ? "'" . $db->escape_string($val) . "'" : "NULL";
	}
	
	public function validate($val = NULL) {
		$val = ($val === NULL) ? $this->get_value() : $val;
		if (strlen($val) == 0)
			return True;
		$regex = "/^(\d{4})(-)(\d{2})(-)(\d{2})$/";
		$valid = preg_match($regex, $val) == 1;
		if (!$valid)
			array_push($this->errors, $GLOBALS['i18n']['framework']["fielderr3"]);
		return $valid;
	}
}

?>

