<?php
/*
 * Tikapot Example App Tests Page
 *
 */

$title = $request->i18n['example']['tp_test'] . " | Tikapot";
include("includes/header.php");

require_once(home_dir . "lib/simpletest/simpletest.php");

class TikaDisplay extends HtmlReporter {
    public function paintHeader($test_name) { $this->sendNoCacheHeaders(); }
    public function paintFooter($test_name) {
        $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
        print "<div style=\"";
        print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
        print "\">";
        print $this->getTestCaseProgress() . "/" . ($this->getTestCaseCount()-1) . " ";
        print $request->i18n['example']['test_1'] . ":\n";
        print "<strong>" . $this->getPassCount() . "</strong> ".$request->i18n['example']['test_2'].", ";
        print "<strong>" . $this->getFailCount() . "</strong> " . $request->i18n['example']['test_3'] . " ";
        print "<strong>" . $this->getExceptionCount() . "</strong> ".$request->i18n['example']['test_4'].".";
        print "</div>\n";
    }
}
?>

<h1><?php echo $request->i18n['example']['tp_test2']; ?></h1>
<a href="<?php echo home_url; ?>"><?php echo $request->i18n['example']['home']; ?></a>
<div style="width: 100%; height: 40px; border-bottom: 1px #555 dotted;"></div>

<?php

include(home_dir . "tests/init.php");
$tests = new AllTests();
$tests->run(new TikaDisplay());

include("includes/footer.php");
?>

