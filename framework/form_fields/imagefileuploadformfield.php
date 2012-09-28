<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/fileuploadformfield.php");

/**
 * An Image file upload form field, for uploading images.
 * Alias for: new FileUploadFormField($name, $location, array("jpg", "jpeg", "png", "bmp", "gif"), ...);
 */
class ImageFileUploadFormField extends FileUploadFormField
{
	/**
	 * Construct a file upload form field
	 * @param string $name          The name of the field
	 * @param string $location      The location to save files too
	 * @param array $types         An array containing allowed file types
	 * @param string $initial_value The initial value of the form field
	 * @param array  $options       Metadata
	 */
	public function __construct($name, $location, $types = array("jpg", "jpeg", "png", "bmp", "gif"), $initial_value = "", $options = array()) {
		parent::__construct($name, $location, $types, $initial_value, $options);
	}
}