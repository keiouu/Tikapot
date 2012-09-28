<?php
/**
 * Tikapot Template Date Tag
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/template_tags/tag.php");

/**
 * A Date Template tag. Usage: {% date "dd mm yyyy" %}
 */
class DateTag extends TplTag
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
		preg_match_all('/{% date "(?P<var>[\s[:punct:]\w]+?)" %}/', $page, $matches, PREG_SET_ORDER);
		foreach ($matches as $val) {
			$date = date($val['var']);
			$page = preg_replace('/{% date "'.$val['var'].'" %}/', $date, $page);
		}
		return $page;
	}
}

