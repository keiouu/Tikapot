<?php
/**
 * Tikapot Key Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/bigintfield.php");

/**
 * A key field, similar to a primary key field but without the primary
 */
class KeyField extends BigIntField
{
	/**
	 * Is this a pk field?.
	 * 
	 * @return boolean True
	 */
	public function is_pk_field() {
		return true;
	}
}

