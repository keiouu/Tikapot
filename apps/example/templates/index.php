<?php
/*
 * Tikapot Example App Index Page
 *
 */

$title = $request->i18n['example']['tp_home'] . " | Tikapot";

include("includes/header.php");
?>
<img src="<?php echo home_url; ?>apps/example/media/images/logo.png" alt="Tikapot Logo" />
<h1><?php echo $request->i18n['example']['welcometp']; ?></h1>
<p><?php echo $request->i18n['example']['welcometp_desc']; ?></p>
<ul class="menu">
<li><a href="http://www.tikapot.com/"><?php echo $request->i18n['example']['tpsite']; ?></a></li>
<!--<li><a href="<?php echo home_url; ?>test/"><?php echo $request->i18n['example']['tptests']; ?></a></li>-->
</ul>

<?php
include("includes/footer.php");
?>

