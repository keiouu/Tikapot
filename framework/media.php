<?php
/*
 * Tikapot Media File Handler
 *
 */

require_once(home_dir . "framework/utils.php");
require_once(home_dir . "framework/config_manager.php");
require_once(home_dir . "framework/processing/processors/mediamanager_post_processor.php");
require_once(home_dir . "lib/jsmin.php");
require_once(home_dir . "lib/lessphp/lessc.inc.php");


class MediaManager
{
	private static $_processor_registered = false;
	private $media_files = array(), $media_key = "", $media_dir = "", $media_url = "";
	
	public function __construct($media_key = "style", $dir_override = "", $url_override = "") {
		$this->media_key = $media_key;
		$this->media_dir = strlen($dir_override) > 0 ? $dir_override : media_dir;
		$this->media_url = strlen($url_override) > 0 ? $url_override : media_url;
	}
	
	/**
	 * Enable the MediaManager Post Processor
	 */
	public function enable_processor() {
		if (!MediaManager::$_processor_registered) {
			new Media_Manager_Post_Processor();
			MediaManager::$_processor_registered = true;
		}
	}
	
	public function get_media_dir() {
		return $this->media_dir;
	}
	
	public function get_media_url() {
		return $this->media_url;
	}
	
	public function add_file($file) {
		$ext = get_file_extension($file);
		if ($ext == "less")
			$ext = "css";
		
		if (!isset($this->media_files[$ext]))
			$this->media_files[$ext] = array();
		
		if (!in_array($file, $this->media_files[$ext])) {
			$this->media_files[$ext][] = $file;
		}
	}
	
	public function add_media($type, $data) {
		$ext = $type;
		if ($ext == "less")
			$ext = "css";
		if (!isset($this->media_files[$ext]))
			$this->media_files[$ext] = array();
		$this->media_files[$ext][] = array("type" => $type, "data" => $data);
	}
	
	public function count_files() {
		return count($this->media_files);
	}
	
	/**
	 * @todo Separate minified files with a header
	 * @todo Dont re-minify *.min.* files
	 */
	public function build($ext) {
		if (!file_exists($this->get_media_dir() . "cache/") || !is_dir($this->get_media_dir() . "cache/")) {
			if (!mkdir($this->get_media_dir() . "cache/", "0744")) {
				console_error($GLOBALS['i18n']['framework']['media_error']);
				return;
			}
		}
		
		$data = "";
		$less = new lessc();
		
		// Decide what to do with it..
		if (isset($this->media_files[$ext])) {
			foreach ($this->media_files[$ext] as $file) {
				// Is this is static Media, work with it
				if (is_array($file)) {
					$act_ext = $file['type'];
					$cdata = $file['data'];
					if ($act_ext == "less") {
						$function = "compile";
						$cData = $cdata;
					} else {
						$cdata = $cdata;
					}
				} else {
					// If it's a file, grab and parse
					$act_ext = get_file_extension($file);
					if ($act_ext == "less") {
						$function = "compileFile";
						$cData = $file;
					} else {
						$cdata = file_get_contents($file);
					}
				}

				if ($act_ext == "less") {
					// Parse it!
					try {
						$data .= $less->$function($cData). "\n";
					} catch (exception $e) {
						console_error("[less parser] " . $e->getMessage());
					}
				} else {
					$data .= $cdata . "\n";
				}
			}
		}
		
		if ($data === "") {
			// Nothing to do!
			return;
		}
		
		$hash = md5($data);
		
		$filename = $this->get_media_dir() . "cache/" . $this->media_key . "_" . $hash . "." . $ext;
		$fileurl  = $this->get_media_url() . "cache/" . $this->media_key . "_" . $hash . "." . $ext;
		if (file_exists($filename)) {
			return $fileurl;
		}
		
		$data = str_replace('{{home_url}}', home_url, $data);
		$data = str_replace('{{media_url}}', media_url, $data);
		
		if (!ConfigManager::get("dev_mode", false)) { // Dont minify in dev mode
			// Minify
			switch ($ext) {
				case "css":
					$data = preg_replace('/\n\s*\n/',"\n", $data);
					$data = preg_replace('!/\*.*?\*/!s','', $data);
					$data = preg_replace('/[\n\t]/',' ', $data);
					$data = preg_replace('/ +/',' ', $data);
					$data = preg_replace('/ ?([,:;{}]) ?/','$1',$data);
					break;
				case "js":
					$data = JSMin::minify($data);
					break;
			}
		}

		// Write
		if (file_put_contents($filename, trim($data)) === FALSE) {
			console_error($GLOBALS['i18n']['framework']['media_error_write']);
		}
		
		return $fileurl;
	}
	
	public function build_css() {
		return $this->build("css");
	}
	
	public function build_js() {
		return $this->build("js");
	}
}

?>
