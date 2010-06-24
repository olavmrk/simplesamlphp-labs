<?php
include '/home/thomasg/workspace/simplesamlphp/lib/_autoload.php';

class UserCatalogueTest extends PHPUnit_Framework_TestCase {

	protected $userStore;


	public static function setUpBeforeClass() {
		print __METHOD__ . "--------------------------------------------\n";
	}


	protected function setUp() {
		print __METHOD__ . "-------------------------------------------------------\n";
		$this->userStore = sspmod_selfregister_Storage_UserCatalogue::instantiateStorage();
	}


	protected function tearDown() {
		print __METHOD__ . "----------------------------------------------------\n";
	}


	public static function tearDownAfterClass() {
		print __METHOD__ . "-----------------------------------------\n";
	}


	protected function onNotSuccessfullTest(Exception $e) {
		print __METHOD__ . "------------------------------------------\n";
		throw $e;
	}



	/**
	 * Asset that the generated storage object is of the configured kind
	 * @group catalogue
	 */
	public function testConfigControlledStorageCreation() {
		$this->userStore = sspmod_selfregister_Storage_UserCatalogue::instantiateStorage();
		$this->assertEquals(
			'sspmod_selfregister_Storage_LdapMod',
			get_class($this->userStore)
		);
	}

}

?>