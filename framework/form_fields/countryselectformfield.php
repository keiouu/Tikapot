<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/selectformfield.php");
require_once(home_dir . "framework/model_fields/countryfield.php");

class CountrySelectFormField extends SelectFormField
{
	/**
	 * Construct
	 * 
	 * @param string $name          The field's name
	 * @param string $initial_value Our initial value
	 * @param array  $options       An array of options (e.g. helptext)
	 */
	public function __construct($name, $initial_value = "GB", $options = array()) {
		parent::__construct($name, CountryChoiceField::get_countries(), $initial_value, $options);
	}
}
