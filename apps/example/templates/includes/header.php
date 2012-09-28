<?php
// Get Media Manager to handle our CSS
include_once(home_dir . "framework/media.php");
$request->media->add_file(home_dir . "apps/example/media/css/style.css");
$request->media->enable_processor();
?>

<!doctype html> 
<html lang="en">
<head> 
  <meta charset="utf-8">
 
  <title><?php print $title; ?></title> 
  <meta name="description" content="Tikapot"> 
  <meta name="author" content="Tikapot">
</head> 
 
<body>
	<div id="content">
