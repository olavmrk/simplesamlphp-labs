<?php

class sspmod_selfregister_Storage_LdapMod extends SimpleSAML_Auth_LDAP {

	private $admDn;
	private $admPw;
	private $searchDn;
	private $searchPw;

	public function __construct(
		$hostname,
		$enable_tls = TRUE,
		$debug = FALSE,
		$timeout = 0,
		$admDn = NULL,
		$admPw = NULL,
		$searchDn = NULL,
		$searchPw = NULL
		){
		parent::__construct(
			$hostname,
			$enable_tls,
			$debug,
			$timeout);
		$this->admDn = $admDn;
		$this->admPw = $admPw;
		$this->searchDn = $searchDn;
		$this->searchPw = $searchPw;
	}


	public function addObject($dn, $entry) {
		$result = ldap_add($this->ldap, $dn, $entry);
		if (!$result) {
			$error_msg = ldap_error($this->ldap);
			if($error_msg == 'Invalid syntax') {
				throw new sspmod_selfregister_Error_UserException('ldap_add_invalid_syntax');
			}
			else if($error_msg == 'Already exists') {
				throw new sspmod_selfregister_Error_UserException('id_taken');
			}
			else {
				throw new Exception($error_msg);
			}
		}
	}


	public function deleteObject($dn){
		$result = ldap_delete($this->ldap, $dn);
		// FIXME: Check returncode and make userExeption for no such object --fixed
		if (!$result) {
			$error_msg = ldap_error($this->ldap);
			if($error_msg == 'No such object') {
				throw new sspmod_selfregister_Error_UserException('user_not_exists');
			}
			else{
				throw new Exception($error_msg);
			}
		}
	}


	public function replaceAttribute($dn, $entry){
		$result = @ldap_mod_replace($this->ldap, $dn, $entry);
		if (!$result) {
			$error_msg = ldap_error($this->ldap);
			if($error_msg == 'No such object') {
				throw new sspmod_selfregister_Error_UserException('user_not_exists');
			}
			else if($error_msg == 'Naming violation') {
				throw new sspmod_selfregister_Error_UserException('id_violation');
			}
			else {
				throw new Exception($error_msg);
			}
		}
	}


	public function searchForFirstDn($base, $keyName, $value) {
		// FIXME: escape_filter_value  -- fixed, (I Think)
		$value = $this->ldap_escape($value,true);
		$filter = "($keyName=$value*)";
		$res = ldap_search($this->ldap, $base, $filter);
		if ($res) {
			if (ldap_count_entries($this->ldap, $res) > 0) {
				$entry = ldap_first_entry($this->ldap, $res);
				$dn = ldap_get_dn($this->ldap, $entry);
			}
		}
		return $dn;
	}

	public function adminBindLdap() {
		$result = $this->bind($this->admDn, $this->admPw);
	}

	public function searchOrAdminBindLdap() {
		if(!empty($this->searchDn) && !empty($this->searchPw)) {
			$result = $this->bind($this->searchDn, $this->searchPw);
		}
		if(!$result) {
			$result = $this->adminBindLdap();
		}
	}

	public function ldap_escape($str, $for_dn = false) {

		// see:
		// RFC2254
		// http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
		// http://www-03.ibm.com/systems/i/software/ldap/underdn.html

		if ($for_dn) {
			$metaChars = array(',','=', '+', '<','>',';', '\\', '"', '#');
		}
		else {
			$metaChars = array('*', '(', ')', '\\', chr(0));
		}
		$quotedMetaChars = array();
		foreach ($metaChars as $key => $value) {
			$quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
		}
		$str = str_replace($metaChars,$quotedMetaChars,$str); //replace them
		return $str;
	} 

}


?>
