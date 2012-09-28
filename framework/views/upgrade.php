<?php
/**
 * Tikapot upgrade View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/models.php");
require_once(home_dir . "framework/database.php");

/**
 * This upgrades the Tikapot database.
 * It is only available in debug mode by default
 */
class UpgradeView extends BasicHTMLView
{
	/**
	 * Construct
	 * 
	 * @param string $url    The URL for this view
	 * @param string $title  The title of this view
	 * @param string $style  Any extra CSS to send to the page
	 * @param string $script Any Javascript to send to the page
	 * @param string $meta   MetaData for the page
	 */
	public function __construct($url, $title = null, $style = "", $script = "", $meta = "") {
		parent::__construct($url, ($title == null ? $GLOBALS['i18n']['framework']['upgrade'] . " | Tikapot" : $title), $style, $script, $meta);
	}
	
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		$db = Database::create();
		$objects = ContentType::objects()->all();
		print "<p>Found ".count($objects)." models.<br />";
		print $GLOBALS['i18n']['framework']['upgrading'] . "...</p><ul>";
	
		// Check Content Type
		$object = new ContentType();
		$columns = $db->get_columns($object->get_table_name());
		if (!isset($columns['version'])) {
			$object->upgrade($db, "1.0", "1.1");
			
			// Get new content types as they wouldnt have been created at model creation
			global $app_paths;
			foreach ($app_paths as $app_path) {
				$path = home_dir . $app_path . '/';
				if ($handle = opendir($path)) {
					while (($entry = readdir($handle))  !== false) {
						if ($entry !== "." && $entry !== "..") {
							$file = $path . $entry . "/models.php";
							if (is_file($file)) {
								include_once($file);
							}
						}
					}
				}
			}
			foreach (get_declared_classes() as $c) {
				try {
					if ($c != "IntermediateModel" && is_subclass_of($c, 'Model')) {
						$reflector = new ReflectionClass($c);
						if (!$reflector->isAbstract())
							ContentType::of(new $c());
					}
				} catch (Exception $e) {}
			}
			
			print "<li>" . $GLOBALS['i18n']['framework']['upgraded'] . " ContentType</li>";
		}
		
		// Check Models
		foreach ($objects as $object) {
			$model = $object->obtain();
			if ($model !== null) {
				$version = $model->get_version();
				if ($object->version != $version) {
					$model->upgrade($db, "".$object->version, "".$version);
					print "<li>" . $GLOBALS['i18n']['framework']['upgraded'] . " ".get_class($model)."</li>";
				}
			}
		}
		print "</ul><p>".$GLOBALS['i18n']['framework']['finished']."!</p>";
	}
}
