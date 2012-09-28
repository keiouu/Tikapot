<?php
/*
 * Tikapot's new tests!
 */

require_once(home_dir . "lib/simpletest/unit_tester.php");

/**
 * Test model for Tikapot, simple key/value
 */
class HardTestModel extends Model
{
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->add_field("key", new CharField(250, ""));
		$this->add_field("value", new CharField(250, ""));
	}
}

/**
 * BlankModel is essentially a non-abstract version of Model.
 * Used for various dynamic modelling techniques
 *
 */
class BlankModel extends Model
{
	/**
	 * __construct override
	 *
	 * @param string $table_name [Optional] The table name to use for this model
	 * @param array $fields The fields for this model
	 */
	public function __construct($table_name = "", $fields = array()) {
		parent::__construct();
		
		foreach ($fields as $name => $obj) {
			$this->add_field($name, $obj);
		}
		
		if ($table_name !== "") {
			$this->set_table_name($table_name);
			$this->create_table();
		}
	}
	
	/**
	 * Returns a new, blank instance of this object
	 *
	 * @return BlankModel a blank model
	 */
	public function new_instance() {
		$object = new BlankModel($this->get_table_name(), $this->get_fields());
		return $object;
	}
}

class Framework_Tests extends UnitTestCase {
	public function testUtils() {
		require_once(home_dir . "framework/utils.php");
		// Run starts_with tests
		$this->assertTrue(starts_with("abcde", "a"));
		$this->assertTrue(starts_with("abcde", "ab"));
		$this->assertFalse(starts_with("abcde", "b"));
		$this->assertFalse(starts_with("abcde", ""));
		$this->assertFalse(starts_with("", "a"));
		// Run ends_with tests
		$this->assertTrue(ends_with("abcde", "e"));
		$this->assertTrue(ends_with("abcde", "de"));
		$this->assertFalse(ends_with("abcde", "ed"));
		$this->assertFalse(ends_with("abcde", ""));
		$this->assertFalse(ends_with("", "a"));
		// Run partition tests
		$this->assertEqual(partition("hey-you", "-"), array("hey", "-", "you"));
		$this->assertEqual(partition("a-b", "-"), array("a", "-", "b"));
		$this->assertEqual(partition("-b", "-"), array("", "-", "b"));
		$this->assertEqual(partition("a-", "-"), array("a", "-", ""));
		$this->assertEqual(partition("a", "-"), array("a", "-", ""));
		// Run get_named_class tests
		$this->assertEqual(get_class(get_named_class("Config")), "Config"); 
		$this->assertEqual(get_named_class("hbbvisvsuvis"), null);
		// Run get_file_extension tests
		$this->assertEqual(get_file_extension("hey.png"), "png");
		$this->assertEqual(get_file_extension(".png"), "png");
		$this->assertEqual(get_file_extension("hey"), "");
		$this->assertEqual(get_file_extension("hey."), "");
		// Run get_file_name tests
		$this->assertEqual(get_file_name("hey.png"), "hey");
		$this->assertEqual(get_file_name(".png"), "");
		$this->assertEqual(get_file_name("hey"), "hey");
		$this->assertEqual(get_file_name("hey."), "hey");
		$this->assertEqual(get_file_name("/../../hey.jpg"), "hey");
		// Test prettify
		$this->assertEqual(prettify("Hey"), "Hey");
		$this->assertEqual(prettify("HeyThere"), "Hey There");
		$this->assertEqual(prettify("Hey_there"), "Hey There");
		$this->assertEqual(prettify("Hey_There"), "Hey There");
		$this->assertEqual(prettify("HeyT"), "Hey T");
		// Test ellipsize
		$this->assertEqual(ellipsize("Hey there, how are you?", 8), "Hey...");
		$this->assertEqual(ellipsize("Hey there, how are you?", 20), "Hey there, how...");
		$this->assertEqual(ellipsize("Heythere", 7), "Heyt...");
		$this->assertEqual(ellipsize("Hey there", 10), "Hey there");
	}
	
	public function testTimer() {
		require_once(home_dir . "framework/timer.php");
		$timer = Timer::start();
		$this->assertTrue($timer);
		$pingtime = $timer->ping();
		$this->assertTrue($timer->ping() > 0);
		$endtime = $timer->stop();
		$this->assertTrue($endtime > $pingtime);
		time_sleep_until(microtime(true) + 0.1);
		$this->assertEqual($endtime, $timer->ping());
	}
	
	function testRequest() {
		require_once(home_dir . "framework/request.php");
		$req = new Request();
		$this->assertEqual($req->get_mime_type("/notafile/"), "text/html");
		$this->assertEqual($req->get_mime_type(home_dir . "tests/randoms/test_mime.txt"), "text/plain");
		$this->assertEqual($req->get_mime_type(home_dir . "tests/randoms/test_mime.css"), "text/css");
		$token1 = $req->get_csrf_token();
		$token2 = $req->get_csrf_token();
		$token3 = $req->get_csrf_token();
		$this->assertTrue($req->validate_csrf_token($token2));
		$this->assertTrue($req->validate_csrf_token($token1));
		$this->assertTrue($req->validate_csrf_token($token3));
		$this->assertEqual($req->create_url("test", "a=b", "?c=3&g=2"), "test?a=b&c=3&g=2");
		$this->assertEqual($req->create_url("test", "a=b", "?a=3&g=2"), "test?a=3&g=2");
	}
	
	function testStructure() {
		require_once(home_dir . "framework/structures/ARadix.php");
		$trie = new ARadix();
		$this->assertFalse($trie->is_regex());
		$this->assertEqual(count($trie->children()), 0);
		$this->assertEqual($trie->size(), 1);
		$addition = new ARadix("posts");
		$rex = new ARadix("(?P<name>\w+)", "299");
		$this->assertTrue($rex->is_regex());
		$addition->add($rex);
		$trie->add($addition);
		$this->assertEqual($trie->size(), 3);
		$addition->add(new ARadix("Test", "100"));
		$trie->add($addition);
		$this->assertEqual($trie->size(), 4);
		$url = array("posts", "Test");
		$this->assertEqual($trie->query($url), array("100", array()));
		$url = array("posts", "sdd");
		$result = $trie->query($url);
		$this->assertEqual($result[0], "299");
		$this->assertTrue(count($result), 2);
		if (count($result) == 2) {
			$this->assertTrue(array_key_exists("name", $result[1]));
			if (array_key_exists("name", $result[1]))
				$this->assertEqual($result[1]["name"], "sdd");
		}
	}
	
	public function print_hello() {
		print 'hello';
	}
	
	public function testSignal() {
		require_once(home_dir . "framework/signal_manager.php");
		$signalManager = new SignalManager();
		$signalManager->register("test");
		$signalManager->hook("test", "print_hello", $this);
		ob_start();
		$signalManager->fire("test");
		$this->assertEqual(ob_get_clean(), "hello");
	}
	
	public function testSession() {
		require_once(home_dir . "framework/session.php");
		$old_session = $_SESSION;
		Session::delete("Test");
		$this->assertEqual(Session::get("Test"), NULL);
		
		$new = Session::store("Test", 2);
		$this->assertEqual(Session::get("Test"), 2);
		$this->assertEqual($new, Session::get("Test"));
		
		$old = Session::store("Test", 5);
		$this->assertEqual(Session::get("Test"), 5);
		$this->assertEqual($old, $new);
		$no = Session::put("Test", 6);
		$this->assertEqual($no, False);
		$this->assertEqual(Session::get("Test"), 5);
		$this->assertTrue(Session::get("b43542y2") == NULL);
		Session::delete("Test");
		$this->assertEqual(Session::get("Test"), NULL);
		$_SESSION = $old_session;
	}
	
	public function testPostgresql() {
		require_once(home_dir . "framework/database.php");
		require_once(home_dir . "framework/model.php");
		require_once(home_dir . "framework/models.php");
		$model = new BlankModel("TestPSQL");
		
		$db = Database::create($model->get_db());
		if ($db->get_type() !== "psql")
			return;
		
		$this->assertTrue($model->table_exists());
		$this->assertEqual($db->get_columns($model->get_table_name()), array("id" => "int8"));
		$db->drop_table($model->get_table_name());
		$this->assertFalse($model->table_exists());
	}
	
	public function testModel() {
		require_once(home_dir . "framework/database.php");
		require_once(home_dir . "framework/model.php");
		require_once(home_dir . "framework/models.php");

		$model = new BlankModel("TestModels");
		$db = Database::create($model->get_db());
		
		$this->assertEqual($model->get_table_name($db), $db->get_prefix() . "TestModels");
		$this->assertTrue($model->table_exists());
		
		// TODO - Tests
		
		// Cleanup
		$db->drop_table($model->get_table_name());
	}
	
	/**
	 * Test FK Fields
	 */
	public function testFKFields() {
		require_once(home_dir . "framework/database.php");
		require_once(home_dir . "framework/model.php");
		require_once(home_dir . "framework/models.php");
		require_once(home_dir . "framework/model_fields/init.php");
		
		// Create a fake config for FK tests
		$config = new Config();
		$config->load_values(array(
			"key" => "fk_test_hey",
			"value" => "hey"
		));
		$config->save();
		
		// Create a linking model for FK tests
		$model = new BlankModel("TestFKModels", array(
			"data" => new FKField("framework.Config"),
		));
		
		// Tests
		$this->assertEqual($model->_data->_appName(), "framework");
		$this->assertEqual($model->_data->_className(), "Config");
		$this->assertFalse(isset($model->_data));
		$this->assertFalse(isset($model->data));		
		$this->assertEqual($config->value, "hey");
		$model->data = $config;
		$this->assertEqual($model->data->pk, $config->pk);
		$this->assertEqual($model->data->value, "hey");
		$model->data = $config;
		$model->save();
		$this->assertEqual($model->data->pk, $config->pk);
		$this->assertEqual($model->data->value, "hey");
		$model->save();
		
		// Cleanup
		$config->delete();
		$db = Database::create($model->get_db());
		$db->drop_table($model->get_table_name());
	}
	
	/**
	 * Test Multi FK Fields
	 */
	public function testMultiFKFields() {
		require_once(home_dir . "framework/database.php");
		require_once(home_dir . "framework/model.php");
		require_once(home_dir . "framework/models.php");
		require_once(home_dir . "framework/model_fields/init.php");
		
		// Create a fake config for Multi FK tests
		$app_config = new App_Config();
		$app_config->load_values(array(
			"app" => "tests",
			"key" => "fk_test_hey!",
			"value" => "hey!"
		));
		$app_config->save();
		
		// Create a fake config for Multi FK tests
		$config = new Config();
		$config->load_values(array(
			"key" => "fk_test_hey",
			"value" => "hey"
		));
		$config->save();
		
		// Create a linking model for FK tests
		$model = new BlankModel("TestMultiFKModels", array(
			"data" => new MultiFKField("framework.Config", "framework.App_Config"),
		));
		$this->assertEqual($model->_data->_appName(), "");
		$this->assertEqual($model->_data->_className(), "");
		$this->assertFalse(isset($model->_data));
		$this->assertFalse(isset($model->data));
		$model->data = $config;
		$model->save();
		$this->assertEqual($model->_data->_appName(), "framework");
		$this->assertEqual($model->_data->_className(), "Config");
		$this->assertEqual($model->data->pk, $config->pk);
		$this->assertEqual($model->data->value, "hey");
		$model->data = $app_config;
		$model->save();
		$this->assertEqual($model->_data->_appName(), "framework");
		$this->assertEqual($model->_data->_className(), "App_Config");
		$this->assertEqual($model->data->pk, $app_config->pk);
		$this->assertEqual($model->data->value, "hey!");
		
		// TODO - searching
		
		// Cleanup
		$app_config->delete();
		$config->delete();
		$db = Database::create($model->get_db());
		$db->drop_table($model->get_table_name());
	}

	/**
	 * Test Model Cache object relations
	 */
	function testModelCache() {
		// This is an interesting test..

		$object1 = new HardTestModel();
		$object1->value = "hello world";
		$object1->save();

		$object2 = HardTestModel::get($object1->pk);

		$object1->value = "goodbye world";

		$this->assertEqual($object2->value, "goodbye world");
	}
}
