<?php
/*
 * Tikapot Forms
 *
 */


require_once(home_dir . "framework/form_fields/init.php");
require_once(home_dir . "framework/form_printers.php");
require_once(home_dir . "framework/fieldset.php");
require_once(home_dir . "framework/utils.php");

class FormException extends Exception {}

class Form
{
	protected $action, $method, $form_id, $fieldsets = array(), $errors = array(), $model = false;
	
	/**
	 * Data can be a model or an array of form fields
	 */
	public function __construct($data, $action = "", $method = "POST", $overrides = "") {
		$this->form_id = (isset($_SESSION["current_form_id"]) ? $_SESSION["current_form_id"] + 1 : 1);
		$_SESSION["current_form_id"] = $this->form_id;
		$this->action = $action;
		$this->method = $method;
		$this->generate_control_block();
		if (is_array($data)) {
			$this->load_fields($data);
		} else {
			$this->load_model($data);
		}
		if ($overrides !== "")
			$this->load_fields($overrides);
	}
	
	public function get_header($action = "", $method = "", $extra = "") {
		$header = '<form';
		$header .= ' id="'.$this->get_form_id().'"';
		$header .= ' action="'.($action === "" ? $this->action : $action).'"';
		$header .= ' method="'.($method === "" ? $this->method : $method).'"';
		if ($this->has_file())
			$header .= ' enctype="multipart/form-data"';
		$header .= $extra . '>';
		return $header;
	}
	
	public function has_file() {
		foreach($this->fieldsets as $name => $fieldset) {
			foreach($fieldset as $field_name => $field) {
				if ($field->get_type() == "file")
					return true;
			}
		}
		return false;
	}
	
	public function get_form_id() {
		return "form_" . $this->form_id;
	}
	
	public function set_fieldsets($fieldsets) {
		$this->fieldsets = $fieldsets;
		$this->generate_control_block();
	}
	
	public function get_fieldsets() {
		return $this->fieldsets;
	}
	
	/**
	 * New-Style Forms (Tikapot 2.0)
	 * Begin a new fieldset
	 * 
	 * @param string $name The name of the fieldset to obtain, or begin
	 * @return Fieldset A Fieldset Object
	 */
	public function fieldset($name) {
		if (isset($this->fieldsets[$name]))
			return $this->fieldsets[$name];
		$fieldset = new Fieldset($name, array());
		$fieldset->set_form($this);
		$this->fieldsets[$name] = $fieldset;
		return $fieldset;
	}
	
	public function get_fieldset($name) {
		if (isset($this->fieldsets[$name]))
			return $this->fieldsets[$name];
		return null;
	}
	
	public function get_field($name) {
		foreach($this->fieldsets as $i => $fields)
			foreach ($fields as $fname => $field)
				if ($fname === $name)
					return $field;
		return null;
	}
	
	public function get_value($name) {
		$field = $this->get_field($name);
		if ($field !== null)
			return $field->get_value();
		return false;
	}
	
	public function __get($name) {
		$field = $this->get_field(starts_with($name, "_") ? substr($name, 1) : $name);
		if (starts_with($name, "_"))
			return $field;
		else
			return $field->get_value();
	}
	
	protected function check_csrf($form_id, $token) {
		return isset($_SESSION[$form_id]) && isset($_SESSION[$form_id]["csrf"]) && $_SESSION[$form_id]["csrf"] == $token;
	}
	
	protected function generate_control_block() {
		$this->fieldsets["control"] = new Fieldset("", array(), "control");
		// CSRF token field
		list($id, $csrf) = $this->generate_csrf_token();
		$this->fieldsets["control"]["formid"] = new HiddenFormField("formid", $id);
		$this->fieldsets["control"]["csrf"] = new HiddenFormField("csrf", $csrf);
	}
	
	protected function generate_csrf_token() {
		$form_key = $this->get_form_id();
		$token = md5(uniqid(rand(), true));
		if (!isset($_SESSION[$form_key]))
			$_SESSION[$form_key] = array();
		$_SESSION[$form_key]["csrf"] = $token;
		return array($form_key, $token);
	}
	
	public function validate($base_id) {
		$result = true;
		foreach($this->fieldsets as $i => $fields) {
			$fid = $fields->get_id($base_id);
			foreach ($fields as $fname => $field)
				$result = $result && $field->validate($fid, $fname);
		}
		return $result;
	}
	
	public function load_post_data($data) {
		if (!isset($data["control_formid"]) || !isset($data["control_csrf"])) {
			console_warning($GLOBALS['i18n']['framework']["formerrctrl"]);
			return false;
		}
		if (!$this->check_csrf($data["control_formid"], $data["control_csrf"])) {
			console_warning($GLOBALS['i18n']['framework']["formerrcsrf"]);
			return false;
		}
	
		// Work out the form key
		$key = $data['control_formid'] . "_";
		$key_len = strlen($key);
		
		// Pre post load event
		foreach($this->fieldsets as $ti => $tfields)
				foreach ($tfields as $tname => $tfield)
					$tfield->pre_postdata_load();
		
		// Re-Construct Data
		foreach($data as $name => $value) {
			if(starts_with($name, "submit") || starts_with($name, "control_"))
				continue;
			$field = $this->get_field(substr($name, $key_len));
			if (!$field) {
				$owned = false;
				foreach($this->fieldsets as $ti => $tfields)
					foreach ($tfields as $tname => $tfield)
						$owned = $owned || $tfield->claim_own($tname, $field, $value);
				if (!$owned) {
					console_warning($GLOBALS['i18n']['framework']["formerrdata"] . $name);
					return false;
				}
			} else {
				$field->set_value($value); 
			}
		}
		
		// Check for uploads
		foreach($this->fieldsets as $i => $fields) {
			foreach ($fields as $name => $field) {
				if ($field->get_type() == "file") {
					$fname = $data['control_formid'] . '_' . $name;
					if (isset($_FILES[$fname]) && strlen($_FILES[$fname]["tmp_name"]) > 0) {
						$field->set_value($_FILES[$fname]);
					}
				}
			}
		}
		
		return $this->validate($data['control_formid']);
	}
	
	public function clear_data() {
		foreach($this->fieldsets as $name => $fieldset) {
			if($name === "control")
				continue;
			foreach($fieldset as $field_name => $field) {
				$field->clear_value();
			}
		}
	}
	
	protected function load_fields($fieldsets) {
		foreach ($fieldsets as $i => $fields) {
			if(!is_array($fields)) {
				$this->fieldsets["".$i] = $fields;
				continue;
			}
			$this->fieldsets["".$i] = new Fieldset();
			foreach ($fields as $name => $field) {
				$this->fieldsets["".$i][$name] = $field;
			}
		}
	}
	
	protected function add_modelfield($fieldset, $name, $field) {
		if (!isset($this->fieldsets[$fieldset]))
			$this->fieldsets[$fieldset] = new Fieldset();
		$this->fieldsets[$fieldset][$name] = $field->get_formfield($name);
	}
	
	public function load_model_data($model) {
		$this->model = $model;
		if ($model->fromDB())
			$this->fieldsets["control"]["modelid"] = new HiddenFormField("modelid", $model->pk);
		$this->fieldsets["control"]["modelct"] = new HiddenFormField("modelct", $model->get_content_type()->pk);
		$fields = $model->get_fields();
		foreach ($fields as $name => $field) {
			$local_field = $this->get_field($name);
			if ($local_field)
				$local_field->set_value($field->get_form_value());
		}
	}
	
	protected function load_model($model) {
		$fields = $model->get_fields();
		foreach ($fields as $name => $field) {
			$this->add_modelfield("0", $name, $field);
		}
		$this->load_model_data($model);
	}
	
	public function save($model = NULL, $request = NULL) {
		if ($model === NULL)
			$model = $this->model;
		if (!$model)
			throw new Exception($GLOBALS['i18n']['framework']["formerrsave"]);
		
		foreach ($this->fieldsets as $fieldset => $fields) {
			if ($fieldset !== "control") {
				foreach ($fields as $name => $field) {
					try {
						$mfield = $model->get_field($name);
						$mfield->set_value($field->get_value());
					} catch(Exception $e) {	}
				}
			}
		}
		if (!$model->save())
			return false;
		return $model;
	}
	
	public function display($printer = null) {
		if ($printer === null)
			$printer = new HTMLFormPrinter();
		$printer->run($this);
	}
	
	/* Emailing functions */
	protected function add_attachment($name, $mime_boundary, $safe_name) {
		if(!isset($_FILES[$name]))
			return $GLOBALS['i18n']['framework']["errorfile"]."<br />";
		$file_path = $_FILES[$name]["tmp_name"];
		if(!is_file($file_path))
			return "";
		
		$fp = @fopen($file_path, "rb");
		$data = @fread($fp, filesize($file_path));
		@fclose($fp);
		$data = chunk_split(base64_encode($data));
		
		$ret = '--'.$mime_boundary."\r\n";
		$ret .= 'Content-Type: application/octet-stream; name="' . basename($file_path) . "\"\r\n";
		$filename = "(" . $safe_name . ") " . $_FILES[$name]["name"];
		$ret .= 'Content-Description: ' . $filename . "\r\n";
		$ret .= 'Content-Disposition: attachment;' . "\r\n";
		$ret .= ' filename="' . $filename . '"; size="' . filesize($file_path) . '";' . "\r\n";
		$ret .= 'Content-Transfer-Encoding: base64' . "\n\n";
		$ret .= $data;
		$ret .= "\n\n";
		return $ret;
	}
	
	public function email($to_address, $from_address, $subject) {
		$message = '<html>
						<head>
							<title>'.$subject.'</title>
						</head>
						<body>';
		
		foreach ($this->fieldsets as $fieldsetname => $fieldset) {
			if ($fieldsetname === "control")
				continue;
			$message .= "\n";
			if (strlen($fieldset->get_legend()) > 0)
				$message .= '<h2>' . email_sanitize($fieldset->get_legend()) . '</h2><br />';
			foreach ($fieldset->get_fields() as $name => $field) {
				if ($field->get_type() !== "file") {
					$message .= '<b>' . email_sanitize($field->get_name()) . ':</b> ' . email_sanitize($field->get_value()) . '<br />';
				}
			}
		}
		
		$message .= '</body></html>';
		
		$headers  = 'From: ' . $from_address . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		
		// Do we need to add attachments?
		if ($this->has_file()) {
    		$mime_boundary = "==Multipart_Boundary_x".md5(time())."x";
			$headers .= 'Content-Type: multipart/mixed;' . "\n";
			$headers .= ' boundary="' . $mime_boundary . '"' . "\n";
			
			// Prepare message
			$message_header = '--' . $mime_boundary . "\n";
			$message_header .= 'Content-Type: text/html; charset="iso-8859-1"' . "\n";
			$message_header .= 'Content-Transfer-Encoding: 7bit' . "\n\n";
			$message = $message_header . $message . "\n\n";
			
			// Add the files
			foreach ($this->fieldsets as $fieldset) {
				foreach ($fieldset->get_fields() as $name => $field) {
					if ($field->get_type() == "file") {
						$base_id = $fieldset->get_id($this->get_form_id());
						$fname = $field->get_field_id($base_id, $name);
						$message .= $this->add_attachment($fname, $mime_boundary, $name);
					}
				}
			}
		} else {
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		}

		return mail($to_address, $subject, $message, $headers);
	}
}

class Form2 extends Form
{
	public function __construct($action = "", $method = "POST", $overrides = "") {
		return parent::__construct(array(), $action, $method, $overrides);
	}
}
?>

