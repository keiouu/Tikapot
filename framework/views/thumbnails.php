<?php
/**
 * Tikapot Thumbnail Generator View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/views/html.php");
require_once(home_dir . "framework/utils.php");

/**
 * A Thumbnail Generator View
 */
class ThumbnailGeneratorView extends View
{
	private static $valid_extensions = array("jpg", "jpeg", "png");
	private $last_error = "";

	public function image_error($text, $width = 0, $height = 0) {
		// Output a header specifying the error
		header("TPIMAGEERR: " . $text);
		
		// Output white image
		$img = imagecreatetruecolor($width, $height);
		imagefilledrectangle($img, 0, 0, $width, $height, imagecolorallocate($img, 255, 255, 255));
		header('Content-type: image/jpeg');
		imagejpeg($img);
		imagedestroy($img);

		return false;
	}

	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		// We must be given a few things.
		// First: An image
		// Second: A width or height
		// Third (optionally): A width or height
		if (!isset($request->get['image']) || (!isset($request->get['width']) && !isset($request->get['height']))) {
			return $this->image_error('You did not specify an image or a width/height! Please load with ?image=x&[width|height]=y');
		}

		$width = isset($request->get['width']) && is_numeric($request->get['width']) ? intval($request->get['width']) : NULL;
		$height = isset($request->get['height']) && is_numeric($request->get['height']) ? intval($request->get['height']) : NULL;

		// Prevent DDOS attacks by resizing images to 99999999999px
		if (($width !== null && $width > 2500) || ($height !== null && $height > 2500)) {
			return $this->image_error('Error: width and height must be below 2500!');
		}

		$image = $request->get['image'];

		if (strpos($image, media_url) === 0) {
			$image = substr($image, strlen(media_url));
		}

		if (strpos($image, media_dir) !== 0) {
			$image = media_dir . $image;
		}

		// We must verify this is a valid image, and is within media_dir. It should not contain two periods, so as to prevent against
		// attack.
		if (!$this->valid_image($image)) {
			return $this->image_error($this->last_error, $width, $height);
		}

		$ext = get_file_extension($request->get['image']);
		$ext = $ext == false ? substr($image, -3) : $ext;

		// Generate cache key and check cache, make sure the request is valid & safe first though.
		$cache_key = media_dir . "cache/" . md5($request->get['image']) . ($width !== null ? '_w' . $width : '') . ($height !== null ? '_h' . $height : '') . "." . $ext;
		if (file_exists($cache_key) && filemtime($cache_key) > filemtime($image)) {
			// Output from cache
			switch ($ext) {
				case "jpg":
				case "jpeg":
					$im = imagecreatefromjpeg($cache_key);
					if ($im) {
						header("Content-Type: image/jpeg");
						imagejpeg($im);
						return;
					}
					break;
				case "png":
					$im = imagecreatefrompng($cache_key);
					if ($im) {
						header("Content-Type: image/png");
						imagepng($im);
						return;
					}
					break;

			}
		}

		// Get us some info.
		list($o_width, $o_height, $o_type, $o_attr) = getimagesize($image);

		// Right! We have an image. Lets specify both of our sizes
		if ($width === NULL) {
			$ratio = $height / $o_height;
			$width = $o_width * $ratio;
		}
		if ($height === NULL) {
			$ratio = $width / $o_width;
			$height = $o_height * $ratio;
		}

		$im = NULL;
		switch ($ext) {
			case "jpg":
			case "jpeg":
				$im = imagecreatefromjpeg($image);
				break;
			case "png":
				$im = imagecreatefrompng($image);
				break;

		}

		if (!$im) {
			return $this->image_error("Error: Could not create image!", $width, $height);
		}

		$n_im = imagecreatetruecolor($width, $height);
		imagecopyresampled($n_im, $im, 0, 0, 0, 0, $width, $height, $o_width, $o_height);
		switch ($ext) {
			case "jpg":
			case "jpeg":
				header("Content-Type: image/jpeg");
				imagejpeg($n_im, $cache_key);
				imagejpeg($n_im);
				break;
			case "png":
				header("Content-Type: image/png");
				imagepng($n_im, $cache_key);
				imagepng($n_im);
				break;

		}
      	imagedestroy($im);
      	imagedestroy($n_im);
	}

	/**
	 * Verifies the validity of a given URL as an image link
	 * 
	 * @param  string $image URL to image
	 * @return boolean        True if the image is valid, otherwise, false
	 */
	private function valid_image($image) {
		if (strpos($image, "..") !== FALSE) {
			$this->last_error = "Image URL must not have two periods!";
			return false;
		}

		if (!file_exists($image)) {
			$this->last_error = "Could not find image at " . htmlentities($image);
			return false;
		}

		$ext = get_file_extension($image);
		$ext = $ext == false ? substr($image, -3) : $ext;
		if (!in_array($ext, ThumbnailGeneratorView::$valid_extensions)) {
			$this->last_error = htmlentities($ext) . " is not a valid extension!";
			return false;
		}

		return true;
	}
}

