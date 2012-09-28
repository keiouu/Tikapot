<?php
/**
 * Tikapot captcha View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/form_fields/formfields.php");

/**
 * Generates a CAPTCHA image
 */
class CaptchaView extends View
{
	/**
	 * Setup this view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return boolean	True if we can proceed with render, false if not
	 */
	public function setup($request, $args) {
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-type: image/png');
		$this->width = isset($request->get['width']) ? $request->get['width'] : 200;
		$this->height = isset($request->get['height']) ? $request->get['height'] : 70;
		$this->font_size = isset($request->get['font_size']) ? $request->get['font_size'] : 31;
		return true;
	}
	
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		if (isset($request->get['regen']) && $request->get['regen'] == TRUE) {
			$_SESSION["captcha"][$request->get['sesid']] = CaptchaFormField::get_token(7);
		}
		
		$font = font_dir . "captcha.ttf";
		$word = $_SESSION["captcha"][$request->get['sesid']];
		$dimensions = @imageTTFBbox($this->font_size, 0, $font, $word);
		$letter_spacing = 9;
		$max_width = abs($dimensions[2] - $dimensions[0]) + (count(str_split($word)) * $letter_spacing) + 20;
		
		// Create an image
		$image = @imagecreatetruecolor($max_width > $this->width ? $max_width : $this->width, $this->height) or die();
		$background = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $background);
		$height = abs($dimensions[5] - $dimensions[1]);
		$current_x = 5;
		foreach (str_split($word) as $letter) {
			$textcolor = imagecolorallocate($image, rand(20, 200), rand(20, 200), rand(20, 200));
			$rot = rand(-20, 20);
			imagettftext($image, $this->font_size, $rot, $current_x, $height, $textcolor, $font, $letter);
			$letter_dimensions = @imageTTFBbox($this->font_size, $rot, $font, $letter);
			$current_x += abs($letter_dimensions[2] - $letter_dimensions[0]) + $letter_spacing;
		}
		imagepng($image, NULL);
		imagedestroy($image);
	}
}

/**
 * Verifies a CAPTCHA image
 */
class CaptchaVerificationView extends View
{
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		$ses_key = $request->get['sesid'];
		$captcha_key = $request->get['key'];
		if ($_SESSION["captcha"][$request->get['sesid']] == $captcha_key)
			print '1';
		else
			print '0';
	}
}
