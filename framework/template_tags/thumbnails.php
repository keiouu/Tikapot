<?php
/**
 * Tikapot Template Thumbnail Tag
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/template_tags/tag.php");
require_once(home_dir . "framework/utils.php");

/**
 * A Thumbnail Template tag. Usage: {% thumbnail "filename" width height %}
 * If either width or height is "auto" it will be calculated automatically
 */
class ThumbnailTag extends TplTag
{
	/**
	 * Render the tag
	 *
	 * @internal
	 * @param  Framework\Request $request   Request Object
	 * @param  Array $args      The arguments passed by the render manager
	 * @param  string $page      The current HTML of the page
	 * @param  string $local_app The name of the application this page belongs too (if any)
	 * @return string            The resulting page
	 */
	public function render($request, $args, $page, $local_app = '') {
		preg_match_all('/{% thumbnail "(?P<filename>.+?)" (?P<width>[\d\w]+?) (?P<height>[\d\w]+?) %}/', $page, $matches, PREG_SET_ORDER);
		foreach ($matches as $val) {
			$page = str_replace('{% thumbnail "'.$val['filename'].'" '.$val['width'].' '.$val['height'].' %}', $this->process($val['filename'], $val['width'], $val['height']), $page);
		}
		return $page;
	}

	/**
	 * Process an image tag
	 * 
	 * @param  string 	$filename 	Filename
	 * @param  int 		$width    	Width
	 * @param  int 		$height   	Height
	 * @return string        		Replacement URL
	 */
	private function process($filename, $width, $height) {
		$ext = get_file_extension($filename);
		$cache_key = "cache/" . md5($filename) . ($width != 'auto' ? '_w' . $width : '') . ($height != 'auto' ? '_h' . $height : '') . "." . $ext;

		$realFilename = $filename;
		if (strpos($realFilename, media_url) === 0) {
			$realFilename = substr($realFilename, strlen(media_url));
		}

		if (strpos($realFilename, media_dir) !== 0) {
			$realFilename = media_dir . $realFilename;
		}

		if (file_exists(media_dir . $cache_key) && filemtime(media_dir . $cache_key) > filemtime($realFilename)) {
			return media_url . $cache_key;
		}
		return home_url . "tikapot/api/thumbnail/?image=" . $filename . ($width != 'auto' ? '&width=' . $width : '') . ($height != 'auto' ? '&height=' . $height : '');
	}
}

