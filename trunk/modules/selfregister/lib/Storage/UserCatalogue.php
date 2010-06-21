<?php

interface iUserCatalogue {

	public function addUser($userInfo);
	public function updateUser($olduserInfo, $userInfo);
	public function changeUserPassword($userInfo, $newPlainPassword);
	public function isRegistered($searchKeyName, $value);
	public function isValidPassword($userInfo, $plainPassword);
	public function delUser($userInfo);
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

		$searchDd = $this->lc->getString('search.username', NULL);
		$searchPw = $this->lc->getString('search.password', NULL);

		$admDn = $this->rlc->getString('admin.dn', NULL);
		$admPw = $this->rlc->getString('admin.pw', NULL);

		$this->ldap = new sspmod_selfregister_Storage_LdapMod(
			$hostname,
			$enableTLS,
			$debug,
			$timeout,
			$admDn,
			$admPw,
			$searchDd,
			$searchPw
		);
	}


	public function addUser($userInfo){
		$dn = $this->makeDn($userInfo);
		$entry = $this->makeNewEntry($userInfo);
		$this->ldap->adminBindLdap();
		// FIXME: Use errorcode from ldap_add instead --fixed
		$this->ldap->addObject($dn, $entry);
	}


	public function delUser($userInfo) {
		$dn = $this->makeDn($userInfo);

		//FIXME: adminBindLdap() --fixed
		$this->ldap->adminBindLdap();
		$this->ldap->deleteObject($dn);
	}

	public function updateUser($olduserInfo, $userInfo) {
		$dn = $this->makeDn($olduserInfo);
		$this->ldap->adminBindLdap();
		$this->ldap->replaceAttribute($dn, $userInfo);
	}




	public function changeUserPassword($userInfo, $newPlainPassword) {
		$dn = $this->makeDn($userInfo);
		$this->ldap->adminBindLdap();
		$base = $this->lc->getString('search.base');
		$pw = $this->encrypt_pass($newPlainPassword);
		$entry = array('userPassword' => $pw);
		$this->ldap->replaceAttribute($dn, $entry);
	}



	public function isRegistered($searchKeyName, $value){
		$base = $this->lc->getString('search.base');
		// FIXME: Bind as search or admin user to make sure we have rights for searching --fixed
		$this->ldap->searchOrAdminBindLdap();
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


	public function isValidPassword($userInfo, $plainPassword) {
		$dn = $this->makeDn($userInfo);
		return $this->ldap->bind($dn, $plainPassword);
	}


	private function makeDn($userinfo){
		$searchEnable = $this->lc->getBoolean('search.enable', TRUE);
		if(!$searchEnable) {
			$dnpattern = $this->lc->getString('dnpattern');
			$user_id_param = $this->rc->getString('user.id.param', 'uid');
			$rdn = $userinfo[$user_id_param];
			if(is_array($rdn)) {
				$rdn = $rdn[0];
			}
			$dn = str_replace('%username%', $rdn, $dnpattern);
		}
		else {
			$hookfile = SimpleSAML_Module::getModuleDir('selfregister') . '/hooks/hook_attributes.php';
			include_once($hookfile);
			$dn = get_dn_hook($this->lc, $this->rc, $userinfo);
		}
		return $dn;
	}


	private function makeNewEntry($userInfo){
		$attr = $this->rc->getArray('attributes');
		$entry = array();
		$entry['objectClass'] = $this->rlc->getArray('objectClass');

		foreach($attr as $attrName => $fieldName){
			switch ($attrName){
				case "userPassword":
					$entry[$attrName] = $this->encrypt_pass($userInfo[$attrName]);
					break;
				default:
					$entry[$attrName] = $userInfo[$attrName];
				}
		}
		return $entry;
	}


	private function encrypt_pass($plainPassword) {
		$psw_encrypt = $this->rc->getString('psw.encrypt', 'sha1');
		
		if($psw_encrypt == 'sha1') {
			$pw = $this->ssha1_crypt($plainPassword);
		}
		else if($psw_encrypt == 'md5') {
			$pw = $this->smd5_crypt($plainPassword);
		}
		else {
			$pw = $plainPassword;
		}
		return $pw;
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
