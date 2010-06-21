<?php

interface iUserCatalogue {

	public function addUser($userInfo);
	public function updateUser($uid, $userInfo);
	public function changeUserPassword($uid, $newPlainPassword);
	public function isRegistered($searchKeyName, $value);
	public function isValidPassword($uid, $plainPassword);
	public function delUser($uid);
}


class sspmod_selfregister_Storage_UserCatalogue implements iUserCatalogue {

	// LDAP tree modification object
	private $ldap = NULL;
	// Ldap configuration from authentication sources
	private $lc = NULL;
	// Selfregister module config
	private $rc = NULL;
	// LDAP config from selfregister config
	private $rlc = NULL;


	/* $ldapConf The authsource file array
	 */
	public function __construct(){
		$this->rc = SimpleSAML_Configuration::getConfig('module_selfregister.php');
		$this->rlc = SimpleSAML_Configuration::loadFromArray(
			$this->rc->getArray('ldap'));

		$auth = $this->rc->getString('auth');
		$authsources = SimpleSAML_Configuration::getConfig('authsources.php');
		$ldapConf = $authsources->getArray($auth);
		$this->lc = SimpleSAML_Configuration::loadFromArray($ldapConf);

		$hostname = $this->lc->getString('hostname');
		$enableTLS = $this->lc->getBoolean('enable_tls', FALSE);
		$debug = $this->lc->getBoolean('debug', FALSE);
		$timeout = $this->lc->getInteger('timeout', 0);

		$this->ldap = new sspmod_selfregister_Storage_LdapMod(
			$hostname,
			$enableTLS,
			$debug,
			$timeout);
	}


	public function addUser($userInfo){

		$uid = $userInfo['uid'];
		$dn = $this->makeDn($uid);
		$entry = $this->makeNewEntry($userInfo);

		$admDn = $this->rlc->getString('admin.dn');
		$admPw = $this->rlc->getString('admin.pw');
		$this->ldap->bind($admDn, $admPw);

		$base = $this->lc->getString('search.base');
		// FIXME: Use errorcode from ldap_add instead
		if($this->ldap->searchfordn($base, 'uid', $uid, TRUE) ){
			throw new sspmod_selfregister_Error_UserException('uid_taken');
		}else{
			$this->ldap->addObject($dn, $entry);
		}
	}


	public function delUser($uid) {
		$dn = $this->makeDn($uid);

		//FIXME: adminBindLdap()
		$admDn = $this->rlc->getString('admin.dn');
		$admPw = $this->rlc->getString('admin.pw');
		$this->ldap->bind($admDn, $admPw);

		$this->ldap->deleteObject($dn);
	}


	public function updateUser($uid, $userInfo) {
		$dn = $this->makeDn($uid);

		$admDn = $this->rlc->getString('admin.dn');
		$admPw = $this->rlc->getString('admin.pw');
		$this->ldap->bind($admDn, $admPw);

		$base = $this->lc->getString('search.base');
		if($this->ldap->searchfordn($base, 'uid', $uid, TRUE) ){
			// User found in the catalog
			$this->ldap->replaceAttribute($dn, $userInfo);
		}else{
			// User not found
			throw new sspmod_selfregister_Error_UserException('uid_not_found', $uid);
		}
	}




	public function changeUserPassword($uid, $newPlainPassword) {
		$dn = $this->makeDn($uid);

		$admDn = $this->rlc->getString('admin.dn');
		$admPw = $this->rlc->getString('admin.pw');
		$this->ldap->bind($admDn, $admPw);

		$base = $this->lc->getString('search.base');
		if($this->ldap->searchfordn($base, 'uid', $uid, TRUE) ){
			// User found in the catalog
			$pwHash = $this->ssha1_crypt($newPlainPassword);
			$entry = array('userPassword' => $pwHash);
			$this->ldap->replaceAttribute($dn, $entry);
		}else{
			// User not found in LDAP
			throw new sspmod_selfregister_Error_UserException(
				'uid_not_found',
				$uid,
				'',
				'User not found:'.$uid);
		}
	}



	public function isRegistered($searchKeyName, $value){
		$base = $this->lc->getString('search.base');
		// FIXME: Bind as search or admin user to make sure we have rights for searching
		return (bool)$this->ldap->searchForFirstDn(
			$base, $searchKeyName, $value, TRUE
			);
	}


	public function getUser($keyName, $value) {
		$base = $this->lc->getString('search.base');
		$userObjectDn = $this->ldap->searchForFirstDn($base, $keyName, $value);
		$userObject = $this->ldap->getAttributes($userObjectDn);

		//For simplicity, this only return first value of mutivalued attributes
		$user = array();
		foreach ($userObject as $attrName => $values) {
			if ($attrName == 'objectClass') {
			} else {
				$user[$attrName] = $values[0];
			}
		}
		return $user;
	}


	public function isValidPassword($uid, $plainPassword) {
		$dn = $this->makeDn($uid);
		return $this->ldap->bind($dn, $plainPassword);
	}


	private function makeDn($rdn){
		$pattern = $this->lc->getString('dnpattern');
		$dn = str_replace('%username%', $rdn, $pattern);
		return $dn;
	}


	private function makeNewEntry($userInfo){
		$attr = $this->rc->getArray('attributes');

		$entry = array();
		$entry['objectClass'] = $this->rlc->getArray('objectClass');

		foreach($attr as $attrName => $fieldName){
			switch ($attrName){
			case "userPassword":
				$entry[$attrName] = $this->ssha1_crypt($userInfo[$attrName]);
				break;
			default:
				$entry[$attrName] = $userInfo[$attrName];
			}
		}
		return $entry;
	}



	// Make salted md5 hash of password
	private function smd5_crypt ($plainPassword) {
		$salt = '';
		while(strlen($salt)<8) $salt.=chr(rand(64,126));
		$smd5 = md5($plainPassword.$salt, TRUE);
		$return = "{SMD5}".base64_encode($smd5.$salt);
		return $return;
	}



	// Make salted sha1 hash of password
	private function ssha1_crypt ($plainPassword) {
		$salt = '';
		while(strlen($salt)<8) $salt.=chr(rand(64,126));
		$ssha1 = sha1($plainPassword.$salt, TRUE);
		$return = "{SSHA}".base64_encode($ssha1.$salt);
		return $return;
	}

}

?>