<?php
/*
 * Tikapot File Field
 *
 */

require_once(home_dir . "framework/model_fields/charfield.php");
require_once(home_dir . "framework/form_fields/init.php");
require_once(home_dir . "framework/utils.php");

class FileField extends CharField
{
	private $location, $extensions;
	
	public function __construct($location, $extensions = array(), $_extra = "") {
		if (!is_array($extensions))
			throw new Exception($GLOBALS['i18n']['framework']["fielderr15"]);
		$this->location = $location;
		$this->extensions = $extensions;
		parent::__construct(strlen($location) + 500, "", $_extra);
	}
	
	public function __toString() {
		return $this->get_value();
	}
	
	public function get_value() {
		return basename(parent::get_value());
	}
	
	public function get_filename() {
		return $this->location . basename($this->get_value());
	}
	
	public function get_full_filename() {
		return $this->value;
	}
	
	public function get_form_value() {
		return $this->get_value();
	}
	
	public function get_location() {
		return $this->location;
	}
	
	public function get_extensions() {
		return $this->extensions;
	}
	
	public function get_extension() {
		return get_file_extension($this->value);
	}
	
	public function get_formfield($name) {
		return new FileUploadFormField($name, $this->location, $this->extensions);
	}
}

class ImageField extends FileField
{
	public function __construct($location, $extensions = array("jpg", "jpeg", "png", "bmp", "gif"), $_extra = "") {
		parent::__construct($location, $extensions, $_extra);
	}
	
	public function get_formfield($name) {
		return new ImageFileUploadFormField($name, $this->get_location(), $this->get_extensions());
	}
}

?>
