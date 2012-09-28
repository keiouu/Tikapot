<?php
/**
 * The mediamanager adds all media to the page
 * 
 * @author James Thompson
 * @package Tikapot\Framework\Processing\Processors
 */
require_once(home_dir . "framework/processing/post_processor.php");

/**
 * Replace </head> with $request->media's CSS and </body> with it's JS
 */
class Media_Manager_Post_Processor extends Post_Processor
{	
	/**
	 * Take $data and modify it to include the request's media
	 * 
	 * @param Framework\Request $data The request object to work on
	 */
	public function modify($data) {
		if ($data->media->count_files() > 0) {
			$css = '<link rel="stylesheet" href="' . $data->media->build_css() . '" />';
			$js = '<script type="text/javascript" src="' . $data->media->build_js() . '"></script>';
			
			if (strpos($data->output, '<media_manager type="CSS" />') === FALSE) {
				$data->output = preg_replace('/\<(\s*)\/head(\s*)\>/i', $css . '</head>', $data->output);
			} else {
				$data->output = str_replace('<media_manager type="CSS" />', $css, $data->output);
			}
			
			if (strpos($data->output, '<media_manager type="JS" />') === FALSE) {
				$data->output = preg_replace('/\<(\s*)\/body(\s*)\>/i', $js . '</body>', $data->output);
			} else {
				$data->output = str_replace('<media_manager type="JS" />', $js, $data->output);
			}
		}
	}
}
