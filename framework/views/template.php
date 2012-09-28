<?php
/**
 * Tikapot Template View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/tpcache.php");

/**
 * A Template View uses Tikapot's templating engine to render pages
 */
class TemplateView extends View
{
	protected static 	/** Global tags which any view may use */ $global_tags = array(),
						/** Global variables any views may use */ $global_vars = array();

	protected 			/** The title of the page */ $title,
						/** Custom tags for this view */ $custom_tags,
						/** Custom variables for this view */ $custom_vars,
						/** The max time we are allowed to cache for */ $cache_time,
						/** Should we use the cache? */ $use_cache,
						/** The cache key */ $cache_key;
	
	/**
	 * Construct
	 * 
	 * @param string  $url        The URL of this view
	 * @param string  $page       Template filename
	 * @param string  $title      The page title
	 * @param integer $cache_time The max time we can cache
	 */
	public function __construct($url, $page, $title = "", $cache_time = -1) {
		parent::__construct($url, $page);
		
		$this->custom_tags = array();
		foreach (TemplateView::$global_tags as $tag) {
			$this->custom_tags[] = $tag;
		}
		
		$this->custom_vars = array();
		foreach (TemplateView::$global_vars as $name => $var) {
			$this->custom_vars[$name] = $var;
		}
		
		$this->set_title($title);
		$this->cache_time = $cache_time;
		$this->use_cache = false;
	}
	
	/**
	 * Set the title of the page
	 * 
	 * @param string $title The title to use
	 */
	public function set_title($title) {
		$this->title = $title;
		$this->register_var("title", $this->title);
	}
	
	/**
	 * We have the custom tags here so people can override tags
	 * for specific views if they wish
	 *
	 * @param TplTag $tag A tag to use for this view
	 */
	public function register_tag($tag) {
		$this->custom_tags[] = $tag;
	}
	
	/**
	 * Register a global TplTag for use by all template views
	 *
	 * @param TplTag $tag A tag to use for all views
	 */
	public static function register_global_tag($tag) {
		TemplateView::$global_tags[] = $tag;
	}
	
	/**
	 * Register a variable for use by this view
	 * 
	 * @param  string $name  A variable name
	 * @param  string $value A variable value
	 * @return null
	 */
	public function register_var($name, $value) {
		$this->custom_vars[$name] = $value;
	}
	
	/**
	 * Register a variable for use by any view
	 * 
	 * @param  string $name  A variable name
	 * @param  string $value A variable value
	 * @return null
	 */
	public static function register_global_var($name, $value) {
		TemplateView::$global_vars[$name] = $value;
	}
	
	/**
	 * Pre-Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function pre_render($request, $args) {
		/* Check the file exists */
		if (!file_exists($this->page)) {
			throw new Exception($GLOBALS['i18n']['framework']['file_not_found'] . $this->page);
		}
		
		/* Can we use the cache? */
		if ($this->cache_time > -1) {
			$this->cache_key = "tpl-cache-" . md5($this->page . $request->get_full_path(true));
			$this->use_cache = TPCache::get($this->cache_key);
			if ($this->use_cache !== false)
				return;
		}
		
		/* Capture Input */
		ob_start();
	}
	
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		if ($this->use_cache === false) {
			include($this->page);
		}
	}
	
	/**
	 * Post-Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function post_render($request, $args) {
		if ($this->use_cache !== false) {
			return $this->use_cache;
		}
		
		$tpl_output = ob_get_clean();
		
		// Do we want to set an app (for local i18n etc)
		$local_app = "";
		$scan = $this->_parser_scan_for($request, $args, $tpl_output, '/{% set_app \"(?P<app>[[:punct:]\w]+)\" %}/', $this->page);
		if (isset($scan['app']))
			$local_app = $scan['app'];
		$result = $this->parse_page($request, $args, $tpl_output, $local_app, $this->page);
		if ($this->cache_time > -1) {
			TPCache::set($this->cache_key, $result, $this->cache_time);
		}
		return $result;
	}
	
	/**
	 * Find the location of a template given it's parent
	 *
	 * @internal
	 * @param string $parent The location of the file requesting the template
	 * @param string $name The name of the template we want to find
	 * @return string|null The location of the template or null if not found
	 */
	protected function _find_template($parent, $name) {
		chdir(home_dir);
		$parent_location = dirname($parent);
		if (file_exists($parent_location . "/" . $name))
			return $parent_location . "/" . $name;
		if (file_exists($name))
			return $name;
		return null;
	}
	
	/**
	 * Scan for regex in template tree
	 * 
	 * @internal
	 * @param  Tikapot\Framework\Request $request 	The Request object for this view chain
	 * @param  array $args    						Arguments sent to this view
	 * @param  string $template          The current template output
	 * @param  string $regex             The regex to search for
	 * @param  string $template_location The filename of the template
	 * @return boolean|string            The string we found, or false
	 */
	protected function _parser_scan_for($request, $args, $template, $regex, $template_location = "") {
		preg_match('/{% extends \"(?P<page>[[:punct:]\w]+)\" %}/', $template, $matches);
		if (isset($matches['page'])) {
			$location = $this->_find_template($template_location, $matches['page']);
			if ($location == null) {
				console_warning($GLOBALS['i18n']['framework']['page_not_found'] . $matches['page']);
				return false;
			}
			$parent = file_get_contents($location);
			$scan = $this->_parser_scan_for($request, $args, $parent, $regex, $location);
			if ($scan !== false)
				return $scan;
		}
		if (preg_match($regex, $template, $matches))
			return $matches;
		return false;
	}
	
	/**
	 * Parse a page, parses the template and returns it's output
	 * 
	 * @internal
	 * @param  Tikapot\Framework\Request $request 	The Request object for this view chain
	 * @param  array $args    						Arguments sent to this view
	 * @param  string $template          The current template output
	 * @param  string $local_app         The name of the local application
	 * @param  string $template_location The filename of the template file
	 * @return string                    The parsed template
	 */
	public function parse_page($request, $args, $template, $local_app, $template_location = "") {
		// Do we extend anything?
		preg_match('/{% extends \"(?P<page>[[:punct:]\w]+)\" %}/', $template, $matches);
		
		if (isset($matches['page'])) {
			$parent_location = $this->_find_template($template_location, $matches['page']);
			if ($parent_location == null) {
				console_warning($GLOBALS['i18n']['framework']['page_not_found'] . $matches['page']);
			} else {
				$parent_name = $matches['page'];
				ob_start();
				include($parent_location);
				$parent = ob_get_clean();
				preg_match('/{% extends \"(?P<page>[[:punct:]\w]+)\" %}/', $parent, $matches);
				if (isset($matches['page']))
					$recurse_mode = true;
			}
		}
		
		if (isset($parent)) {
			// Check blocks
			preg_match_all('/{% block (?P<block>[[:punct:]\w]+) %}(?P<content>[\S\s]*?)({% endblock \\1 %})/', $template, $matches, PREG_SET_ORDER);
			
			foreach($matches as $val) {
				$blk_content = $val['content'];
				if (preg_match('/{% block.parent %}/', $blk_content)) {
					preg_match('/{% block '.$val['block'].' %}(?P<content>[\S\s]*?)({% endblock '.$val['block'].' %})/', $parent, $old_block);
					if (isset($old_block['content']))
						$blk_content = preg_replace("/{% block.parent %}/", $old_block['content'], $blk_content);
				}
				
				if (isset($recurse_mode))
					$blk_content = '{% block '.$val['block'].' %}'.$blk_content.'{% endblock '.$val['block'].' %}';
					
				if (strpos($parent, '{% block '.$val['block'].' %}')) {
					$parent = preg_replace('/{% block '.$val['block'].' %}([\S\s]*?){% endblock '.$val['block'].' %}/', $blk_content, $parent);
				} else {
					// Add it!
					$parent .= $blk_content;
				}
				
				$template = preg_replace('/{% block '.$val['block'].' %}([\S\s]*?){% endblock '.$val['block'].' %}/', $blk_content, $template);
			}
			
			// Now do deprecated blocks ... for now ...
			preg_match_all('/{% block (?P<block>[[:punct:]\w]+) %}(?P<content>[\S\s]*?){% endblock %}/', $template, $matches, PREG_SET_ORDER);
			foreach($matches as $val) {
				$blk_content = $val['content'];
				if (preg_match('/{% block.parent %}/', $blk_content)) {
					preg_match('/{% block '.$val['block'].' %}(?P<content>[\S\s]*?){% endblock %}/', $page, $old_block);
					if (isset($old_block['content']))
						$blk_content = preg_replace("/{% block.parent %}/", $old_block['content'], $blk_content);
				}
				
				if (isset($recurse_mode))
					$blk_content = '{% block '.$val['block'].' %}'.$blk_content.'{% endblock '.$val['block'].' %}';
					
				if (strpos($parent, '{% block '.$val['block'].' %}')) {
					$parent = preg_replace('/{% block '.$val['block'].' %}([\S\s]*?){% endblock %}/', $blk_content, $parent);
				} else {
					// Add it!
					$parent .= $blk_content;
				}
			}
			
			// Now parent should have all desired elements from template
			$template = $parent;
		
			if (isset($recurse_mode))
				return $this->parse_page($request, $args, $template, $local_app, $parent_location);
		}
		
		// Do we include anything?
		preg_match_all('/{% include \"(?P<page>[[:punct:]\w]+)\" %}/', $template, $matches, PREG_SET_ORDER);
		foreach($matches as $val) {
			ob_start();
			include(home_dir . $val['page']);
			$include = ob_get_clean();
			$inc_page = str_replace("/", "\\/", $val['page']);
			$template = preg_replace('/{% include "'.$inc_page.'" %}/', $include, $template);
		}
		
		// Check vars
		foreach ($this->custom_vars as $name => $val) {
			$template = str_replace("{{{$name}}}", $val, $template);
		}
		foreach ($request->safe_vals as $name => $val) {
			$template = str_replace("{{{$name}}}", $val, $template);
		}
		
		// Check i18n
		preg_match_all('/{% (?P<reach>[[:punct:]\w]*?)i18n "(?P<var>[[:punct:]\w\s]+?)" %}/', $template, $matches, PREG_SET_ORDER);
		foreach($matches as $val) {
			$replace = isset($request->i18n[$val['var']]) ? $request->i18n[$val['var']] : "";
			if (isset($val['reach'])) {
				$reach = substr($val['reach'], 0, -1);
				if ($reach == "local" && $local_app !== "")
					$replace = isset($request->i18n[$local_app][$val['var']]) ? $request->i18n[$local_app][$val['var']] : "";
			}
			// Ensure any tags in the i18n string are taken care of
			foreach ($request->safe_vals as $tag_name => $tag_val)
				$replace = str_replace("{{{$tag_name}}}", $tag_val, $replace);
			$reach = isset($val['reach']) ? $val['reach'] : "";
			$template = preg_replace('/{% '.$reach.'i18n "'.$val['var'].'" %}/', $replace, $template);
		}
		
		// Here is a good place to run any custom tags
		foreach ($this->custom_tags as $tag) {
			$template = $tag->render($request, $args, $template, $local_app);
		}
		
		// Cleanup
		$template = preg_replace('/{% comment %}([\S\s]*?){% endcomment %}/', '', $template);
		$template = preg_replace('/{% set_app \"([[:punct:]\w]+)\" %}/', '', $template);
		$template = preg_replace('/{% block ([[:punct:]\w]+) %}/', '', $template);
		$template = preg_replace('/{% endblock %}/', '', $template);
		$template = preg_replace('/{% endblock ([[:punct:]\w]+) %}/', '', $template);
	
		// Finished!
		return $template;
	}
}

require_once(home_dir . "framework/template_tags/init.php");
