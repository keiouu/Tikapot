<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/charformfield.php");

/**
 * Captcha Form Field, provides a CAPTCHA image
 */
class CaptchaFormField extends CharFormField
{
	private /** Location of the CAPTCHA image */ $image_location,
			/** URL of the CAPTCHA image */ $image_url,
			/** CAPTCHA image width */ $width,
			/** CAPTCHA image height */ $height;
	
	/**
	 * Construct
	 * 
	 * @param string $name          The field's name
	 * @param string $initial_value Our initial value
	 * @param array  $options       An array of options (e.g. helptext)
	 */
	public function __construct($name, $initval = "", $options = array()) {
		parent::__construct($name, $initval, $options);
		$this->width = isset($options['width']) ? $options['width'] : 200;
		$this->height = isset($options['height']) ? $options['height'] : 70;
		$this->options['placeholder'] = $GLOBALS['i18n']['framework']["captchaplaceholder"];
	}
	
	/**
	 * Get a unique CAPTCHA roken
	 * @param  integer $length The length of the token (in characters)
	 * @return string          A token
	 */
	public static function get_token($length = 7) { 
		$rand_src = array(array(48,57), array(97,122)); 
		srand((double) microtime() * 245167413); 
		$random_string = ""; 
		for($i = 0; $i < $length; $i++){ 
			$i1 = rand(0, sizeof($rand_src) - 1); 
			$random_string .= chr(rand($rand_src[$i1][0], $rand_src[$i1][1])); 
		} 
		return $random_string; 
	}
	
	/**
	 * Validate this field
	 * @param  string $base_id   The forms base ID
	 * @param  string $safe_name The field's printable name
	 * @return boolean            True if we validate, false if not
	 */
	public function validate($base_id, $safe_name) {
		$id = $this->get_field_id($base_id, $safe_name);
		if(isset($_SESSION["captcha"][$id]) && $this->get_value() == $_SESSION["captcha"][$id])
			return true;
		$this->set_error($GLOBALS['i18n']['framework']["captchaerr"]);
		return false;
	}
	
	/**
	 * Get the image HTML
	 * @param  string $base_id   Form's Base ID
	 * @param  string $safe_name The safe name of this field
	 * @return string            The HTML for this form field
	 */
	public function get_image($base_id, $safe_name) {
		$id = $this->get_field_id($base_id, $safe_name);
		if(!isset($_SESSION["captcha"]) || !is_array($_SESSION["captcha"]))
			$_SESSION["captcha"] = array();
		if (!isset($_SESSION["captcha"][$id]))
			$_SESSION["captcha"][$id] = CaptchaFormField::get_token(7);
		return '<img src="'.home_url.'tikapot/api/captcha/?sesid='.$id.'&width='.$this->width.'&height='.$this->height.'" alt="CAPTCHA image" class="captchaimg" />';
	}
	
	/**
	 * Get the raw input html (the text field)
	 * @param  string $base_id   Form's Base ID
	 * @param  string $safe_name The safe name of this field
	 * @return string            HTML text field for this element
	 */
	public function get_raw_input($base_id, $safe_name) {
		return parent::get_input($base_id, $safe_name);
	}
	
	/**
	 * Get the HTML <input /> element for this field_id
	 * 
	 * @param  string $base_id   Our form's base ID
	 * @param  string $safe_name Our html-safe name
	 * @param  string $classes   CSS classes to use
	 * @return string            HTML <input /> element
	 */
	public function get_input($base_id, $safe_name, $classes = "") {
		// Return an image
		return $this->get_image($base_id, $safe_name) . $this->get_raw_input($base_id, $safe_name) ;
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return "captchafield";
	}
}