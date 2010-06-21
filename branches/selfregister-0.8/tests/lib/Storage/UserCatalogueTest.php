<?php
require '/home/thomasg/Uninett/workspace/simplesamlphp/lib/_autoload.php';

class UserCatalogueTest extends PHPUnit_Framework_TestCase {

	protected $userStore;

	protected $dummyUser = array(
		'uid' => 'unittester',
		'userPassword' => 'tull1',
		'givenName' => 'Unit',
		'sn' => 'test user',
		'cn' => 'Unit test user',
		'mail' => 'bogus@ikke.no',
		'eduPersonPrincipalName' => 'unittester@ikke.no'
		);


	protected function setUp() {
		print "\nSetup -----------------------------------------------\n";
		$this->userStore = new sspmod_selfregister_Storage_UserCatalogue();
	}


	public function testAddUser() {
		print "\nIs user registrated-----------------------------------\n";
		$this->assertFalse($this->userStore->isRegistered('uid', $this->dummyUser['uid']));
		print "\nAdd user----------------------------------------------\n";
		$this->userStore->addUser($this->dummyUser);
		// Assert that object exists in catalogue
		print "\nIs user registrated-----------------------------------\n";
		$this->assertTrue($this->userStore->isRegistered('uid', $this->dummyUser['uid']));
		// expect User Exception
		// Try second add
	}


	/**
	 * @depends testAddUser
	 */
	public function testGetUser() {
		$fetchedUserInfo = $this->userStore->getUser('uid', $this->dummyUser['uid']);
		$this->assertEquals($this->dummyUser['mail'], $fetchedUserInfo['mail']);
		$firstOfManyWithSameMailAddress = $this->userStore->getUser('mail', 'zograff@hotmail.com');
		var_dump($firstOfManyWithSameMailAddress);
		//FIXME: Test gracefull failure when catalogue is inaccessible.
		//FIXME: Test user not found.
	}



	/**
	 * @depends testAddUser
	 */
	public function testPasswordChange() {
		//FIXME: Test user not found
		//FIXME: Test wrong old password given
		//FIXME: Test good old password given
		$this->userStore->userSelfChangePassword('unittester', 'tull1', 'tull2');
		// assert bind with old password
		$this->assertFalse($this->userStore->isValidPassword('unittester', 'tull1'));
		// assert bind with new password
		$this->assertTrue($this->userStore->isValidPassword('unittester', 'tull2'));
		//FIXME: changeUserPassword
		$this->userStore->changeUserPassword('unittester', 'tull3');
		$this->assertFalse($this->userStore->isValidPassword('unittester', 'tull2'));
		$this->assertTrue($this->userStore->isValidPassword('unittester', 'tull3'));
	}


	/**
	 * @depends testAddUser
	 */
	public function testDelUser() {
		$this->assertTrue(
			$this->userStore->isRegistered('uid', $this->dummyUser['uid'])
			);
		$this->userStore->delUser(
			$this->dummyUser['uid']
			);
		$this->assertFalse(
			$this->userStore->isRegistered('uid', $this->dummyUser['uid'])
			);
	}

}

?>