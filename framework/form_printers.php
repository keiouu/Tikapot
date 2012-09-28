<?php
/**
 * Tikapot Forms
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */


require_once(home_dir . "framework/form_fields/init.php");

/**
 * A form printer prints a form
 */
abstract class FormPrinter
{
	/**
	 * Run this printer (prints the form)
	 * 
	 * @param  TIkapot\Framework\Form $form   The Form object to print
	 * @param  string $action The form action
	 * @param  string $method The form method (GET|POST)
	 * @param  string $submit_text The text to use on the submit button
	 * @return null
	 */
	public abstract function run($form, $action = "", $method = "", $submit_text = "");
}

/**
 * Prints out standard HTML forms
 */
class HTMLFormPrinter extends FormPrinter
{
	/**
	 * Run this printer (prints the form)
	 * 
	 * @param  TIkapot\Framework\Form $form   The Form object to print
	 * @param  string $action The form action
	 * @param  string $method The form method (GET|POST)
	 * @param  string $submit_text The text to use on the submit button
	 * @return null
	 */
	public function run($form, $action = "", $method = "", $submit_text = "") {
		print $form->get_header($action, $method);
		$formid = $form->get_form_id();
		foreach ($form->get_fieldsets() as $fieldset) {
			print '<fieldset>';
			if ($fieldset->get_legend() !== "")
				print '<legend>' . $fieldset->get_legend() . '</legend>';
			foreach ($fieldset->get_fields() as $name => $field) {
				print $field->get_label($fieldset->get_id($formid), $name);
				print $field->get_input($fieldset->get_id($formid), $name);
				print $field->get_error_html($fieldset->get_id($formid), $name);
			}
			print '</fieldset>';
		}
		print '<fieldset>';
		print '<input type="submit" name="submit" value="'.(strlen($submit_text) > 0 ? $submit_text : $GLOBALS['i18n']['framework']["submit"]).'" />';
		print '</fieldset>';
		print '</form>';
	}
}

/**
 * Prints out forms, but uses a table
 */
class TableFormPrinter extends FormPrinter
{
	/**
	 * Run this printer (prints the form)
	 * 
	 * @param  TIkapot\Framework\Form $form   The Form object to print
	 * @param  string $action The form action
	 * @param  string $method The form method (GET|POST)
	 * @param  string $submit_text The text to use on the submit button
	 * @return null
	 */
	public function run($form, $action = "", $method = "", $submit_text = "") {
		print $form->get_header($action, $method);
		$formid = $form->get_form_id();
		foreach ($form->get_fieldsets() as $fieldset) {
			print '<fieldset><table style="width: auto;">';
			if ($fieldset->get_legend() !== "")
				print '<legend>' . $fieldset->get_legend() . '</legend>';
			$fid = $fieldset->get_id($formid);
			foreach ($fieldset->get_fields() as $name => $field) {
				if ($field->get_type() == "hidden") {
					print $field->get_input($fid, $name);
				} else {
					print '<tr>';
					print '<td>'.$field->get_label($fid, $name).'</td>';
					print '<td>'.$field->get_input($fid, $name).'</td>';
					if (strlen($field->get_error()) > 0)
						print '<td>'.$field->get_error_html($fid, $name).'</td>';
					print '</tr>';
				}
			}
			print '</table></fieldset>';
		}
		print '<fieldset>';
		print '<input type="submit" name="submit" value="'.(strlen($submit_text) > 0 ? $submit_text : $GLOBALS['i18n']['framework']["submit"]).'" />';
		print '</fieldset>';
		print '</form>';
	}
}

