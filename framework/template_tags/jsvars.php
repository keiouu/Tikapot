<?php
/**
 * Tikapot Template JS Vars Tag
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/template_tags/tag.php");

/**
 * Add Tikapot's Global Variables to the page. Usage: {% jsvars %}
 */
class JSVarTag extends TplTag
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
		$js = '<script type="text/javascript">
			var tp_home_url = \''.home_url.'\';
			var tp_media_url = \''.media_url.'\';
			var tp_project_name = \''.project_name.'\';
			var tp_site_logo = \''.site_logo.'\';
			var tp_site_url = \''.site_url.'\';
		</script>';
		$page = preg_replace('/{% jsvars %}/', $js, $page);
		return $page;
	}
}

