<?php
/**
 * Tikapot Template Tag base
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/template_tags/tag.php");
require_once(home_dir . "framework/template_tags/date.php");
require_once(home_dir . "framework/template_tags/jsvars.php");

DateTag::register_global();
JSVarTag::register_global();
