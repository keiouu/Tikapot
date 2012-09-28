<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/passwordformfield.php");

/**
 * A password form field (type=password)
 */
class PasswordWithStrengthFormField extends PasswordFormField
{

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return "passwordfield password-strength-field";
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
		$id = $base_id . '_' . $safe_name;
		return '<span class="password-strength-field-container">' . parent::get_input($base_id, $safe_name) . '<span style="display: none;" id="'.$id.'_message"></span></span>
		<script type="text/javascript">
			var strength_descs = new Array();
			strength_descs[0] = "'.$GLOBALS['i18n']['framework']['password_strength_0'].'";
			strength_descs[1] = "'.$GLOBALS['i18n']['framework']['password_strength_1'].'";
			strength_descs[2] = "'.$GLOBALS['i18n']['framework']['password_strength_2'].'";
			strength_descs[3] = "'.$GLOBALS['i18n']['framework']['password_strength_3'].'";
			strength_descs[4] = "'.$GLOBALS['i18n']['framework']['password_strength_4'].'";
			strength_descs[5] = "'.$GLOBALS['i18n']['framework']['password_strength_5'].'";
		
			document.getElementById("'.$id.'").onkeydown = function() {
				var span = document.getElementById("'.$id.'_message");
				span.style.display = "inline-block";
				
				var strength = 0;
				if (this.value.length > 6) strength++;
				if (this.value.length > 10) strength++;
				if (this.value.match(/.[^,!,$,#,%,@,&,(,),_,-,~,*,?]/)) strength++;
				if (this.value.match(/[a-z]/) && this.value.match(/[A-Z]/)) strength++;
				if (this.value.match(/\d+/)) strength++;
				
				span.innerHTML = strength_descs[strength];
			};
		</script>';
	}
}
