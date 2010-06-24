<?php
include '/home/thomasg/workspace/simplesamlphp/lib/_autoload.php';

class LdapModTest extends PHPUnit_Framework_TestCase {

	protected $userStore;

	protected $authSourceConfig = array(
		'ldap:LDAP',
		'hostname' => 'localhost',
		'enable_tls' => FALSE,
		'debug' => TRUE,
		'timeout' => 0,
		'attributes' => NULL,
		'dnpattern' => 'uid=%username%,ou=people,dc=sirannon,dc=uninett',
		'search.enable' => FALSE,
		'search.base' => 'ou=people,dc=sirannon,dc=uninett',
		'search.attributes' => array('uid', 'mail'),
		'search.username' => NULL,
		'search.password' => NULL,
		'priv.read' => FALSE,
		'priv.username' => NULL,
		'priv.password' => NULL,
	);


	protected $ldapWriteConfig = array(
		'admin.dn' => 'cn=admin,dc=sirannon,dc=uninett',
		'admin.pw' => '1234',
		'user.id.param' => 'uid',
		'psw.encrypt' => 'sha1',
		'objectClass' => array(
			'inetOrgPerson',
			'organizationalPerson',
			'person',
			'top',
			'eduPerson',
			'norEduPerson'
			),
	);


	protected $attributes = array(
		'uid' => 'uid',
		'givenName' => 'givenName',
		'sn' => 'sn',
		'cn' => 'cn',
		'mail' => 'mail',
		'eduPersonPrincipalName' => 'eduPersonPrincipalName',
		'userPassword' => 'userPassword',
	);


	protected $dummyUser = array(
		'uid' => 'unittester',
		'userPassword' => 'tull1',
		'givenName' => 'Unit',
		'sn' => 'test user',
		'cn' => 'Unit test user',
		'mail' => 'bogus@ikke.no',
		'eduPersonPrincipalName' => 'unittester@ikke.no'
		);


	private function initLdapUidDn() {
		$this->userStore = new sspmod_selfregister_Storage_LdapMod(
			$this->authSourceConfig,
			$this->ldapWriteConfig,
			$this->attributes
		);
	}



	private function initLdapMailDn() {
		$this->authSourceConfig['dnpattern'] = 'mail=%username%,ou=people,dc=sirannon,dc=uninett';
		$this->ldapWriteConfig['user.id.param'] = 'mail';
		$this->userStore = new sspmod_selfregister_Storage_LdapMod(
			$this->authSourceConfig,
			$this->ldapWriteConfig,
			$this->attributes
		);
	}


	private function addUser() {
		if(! $this->userStore->isRegistered('uid', $this->dummyUser['uid']) ) {
			$this->userStore->addUser($this->dummyUser);
		}
	}



	private function removeUser() {
		if($this->userStore->isRegistered('uid', $this->dummyUser['uid']) ) {
			$this->userStore->delUser($this->dummyUser['uid']);
		}
	}


	private function removeMailDnUser() {
		if($this->userStore->isRegistered('mail', $this->dummyUser['mail']) ) {
			$this->userStore->delUser($this->dummyUser['mail']);
		}
	}



	/**
	 * @group uid
	 */
	public function testCreateLdapUidDn() {
		$this->initLdapUidDn();
	}


	/**
	 * @group uid
	 */
	public function testUidDnAddUser() {
		$this->initLdapUidDn();

		print "\nIs user registrated-----------------------------------\n";
		$this->assertFalse(
			$this->userStore->isRegistered('uid', $this->dummyUser['uid'])
		);
		print "\nAdd user----------------------------------------------\n";
		$this->userStore->addUser($this->dummyUser);
		// Assert that object exists in catalogue
		print "\nIs user registrated-----------------------------------\n";
		$this->assertTrue(
			$this->userStore->isRegistered('uid', $this->dummyUser['uid'])
		);

		$this->removeUser();
	}


	/**
	 * @group uid
	 */
	public function testUidDnGetUser() {
		$this->initLdapUidDn();
		$this->addUser();

		$fetchedUserInfo = $this->userStore->findAndGetUser(
			'uid', $this->dummyUser['uid']
		);
		$this->assertEquals(
			$this->dummyUser['mail'], $fetchedUserInfo['mail']
		);

		$this->removeUser();
		//		$firstOfManyWithSameMailAddress = $this->userStore->findAndGetUser(
		//	'mail', 'zograff@hotmail.com'
		//);
		//		var_dump($firstOfManyWithSameMailAddress);
		//FIXME: Test gracefull failure when catalogue is inaccessible.
		//FIXME: Test user not found.
	}



	/**
	 * @group uid
	 */
	public function testUidDnPasswordChange() {
		$this->initLdapUidDn();
		$this->addUser();

		$this->userStore->changeUserPassword('unittester', 'tull2');
		// assert bind with old password
		$this->assertFalse(
			$this->userStore->isValidPassword('unittester', 'tull1')
		);
		// assert bind with new password
		$this->assertTrue(
			$this->userStore->isValidPassword('unittester', 'tull2')
		);

		$this->removeUser();
	}


	/**
	 * @group uid
	 */
	public function testUidDnDelUser() {
		$this->initLdapUidDn();
		$this->addUser();

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


	/**
	 * @group uid
	 */
	public function testUidDnUpdateUser() {
		$this->initLdapUidDn();
		$this->addUser();

		$change = array('mail' => 'trikken@bongo.pongo');
		$this->userStore->updateUser($this->dummyUser['uid'], $change);

		$fetchedUserInfo = $this->userStore->findAndGetUser(
			'uid', $this->dummyUser['uid']
		);
		$this->assertEquals(
			$change['mail'], $fetchedUserInfo['mail']
		);

		$this->removeUser();
	}



	// Changed config to dn vith mail

	/**
	 * @group mail
	 */
	public function testCreateLdapMailDn() {
		$this->initLdapMailDn();
	}


	/**
	 * @group mail
	 * @group on
	 */
	public function testMailDnAddUser() {
		$this->initLdapMailDn();
		$this->removeMailDnUser();

		print "\nIs user registrated-----------------------------------\n";
		$this->assertFalse(
			$this->userStore->isRegistered('mail', $this->dummyUser['mail'])
		);
		print "\nAdd user----------------------------------------------\n";
		$this->userStore->addUser($this->dummyUser);
		// Assert that object exists in catalogue
		print "\nIs user registrated-----------------------------------\n";
		$this->assertTrue(
			$this->userStore->isRegistered('mail', $this->dummyUser['mail'])
		);

		$this->removeMailDnUser();
	}


	/**
	 * @group mail
	 */
	public function testMailDnGetUser() {
		$this->initLdapMailDn();
		$this->addUser();

		$fetchedUserInfo = $this->userStore->findAndGetUser(
			'mail', $this->dummyUser['mail']
		);
		$this->assertEquals(
			$this->dummyUser['mail'], $fetchedUserInfo['mail']
		);

		$this->removeMailDnUser();
		//		$firstOfManyWithSameMailAddress = $this->userStore->findAndGetUser(
		//	'mail', 'zograff@hotmail.com'
		//);
		//		var_dump($firstOfManyWithSameMailAddress);
		//FIXME: Test gracefull failure when catalogue is inaccessible.
		//FIXME: Test user not found.
	}



	/**
	 * @group mail
	 */
	public function testMailDnPasswordChange() {
		$this->initLdapMailDn();
		$this->addUser();

		$this->userStore->changeUserPassword($this->dummyUser['mail'], 'tull2');
		// assert bind with old password
		$this->assertFalse(
			$this->userStore->isValidPassword($this->dummyUser['mail'], 'tull1')
		);
		// assert bind with new password
		$this->assertTrue(
			$this->userStore->isValidPassword($this->dummyUser['mail'], 'tull2')
		);

		$this->removeMailDnUser();
	}


	/**
	 * @group mail
	 */
	public function testMailDnDelUser() {
		$this->initLdapMailDn();
		$this->addUser();

		$this->assertTrue(
			$this->userStore->isRegistered('mail', $this->dummyUser['mail'])
			);
		$this->userStore->delUser(
			$this->dummyUser['mail']
			);
		$this->assertFalse(
			$this->userStore->isRegistered('mail', $this->dummyUser['mail'])
			);
	}


	/**
	 * @group mail
	 */
	public function testMailDnUpdateUser() {
		$this->initLdapMailDn();
		$this->addUser();

		$change = array('uid' => 'torepaaspore');
		$this->userStore->updateUser($this->dummyUser['mail'], $change);

		$fetchedUserInfo = $this->userStore->findAndGetUser(
			'mail', $this->dummyUser['mail']
		);
		$this->assertEquals(
			$change['uid'], $fetchedUserInfo['uid']
		);

		$this->removeMailDnUser();
	}


}

?>