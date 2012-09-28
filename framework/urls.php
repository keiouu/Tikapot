<?php
/**
 * Tikapot Urls
 * 
 * Perhaps error views could be added here too
 * but for now that is not the responsibility
 * of this project
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/views/init.php");

new Default403();
new Default404();
new Default500();
new i18nJSView("/tikapot/i18n.js");
new CaptchaView("/tikapot/api/captcha/");
new CaptchaVerificationView("/tikapot/api/captcha/verify/");

if (debug) {
	new UpgradeView("/tikapot/upgrade/");
}
