<?php

class sspmod_selfregister_Storage_LdapMod extends SimpleSAML_Auth_LDAP {

	public function __construct(
		$hostname,
		$enable_tls = TRUE,
		$debug = FALSE,
		$timeout = 0){
		parent::__construct(
			$hostname,
			$enable_tls,
			$debug,
			$timeout);
	}


	public function addObject($dn, $entry) {
		$result = ldap_add($this->ldap, $dn, $entry);
		if (!$result)
			throw new Exception(ldap_error($this->ldap));
	}


	public function deleteObject($dn){
		$result = ldap_delete($this->ldap, $dn);
		// FIXME: Check returncode and make userExeption for no such object
		if (!$result)
			throw new Exception(ldap_error($this->ldap));
	}


	public function replaceAttribute($dn, $entry){
		$result = ldap_mod_replace($this->ldap, $dn, $entry);
		if (!$result) {
			throw new Exception(ldap_error($this->ldap));
		}
	}


	public function searchForFirstDn($base, $keyName, $value) {
		// FIXME: escape_filter_value
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

}


?>