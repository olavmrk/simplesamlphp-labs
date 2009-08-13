<?php

/**
 * VOStorage
 *
 * @author Andreas Åkre Solberg <andreas@uninett.no>, UNINETT AS.
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_virtualorg_VOStorage {
	
	private $db;
	
	function __construct() {
		if ($this->db = new SQLiteDatabase('/tmp/sqllite')) {
			$q = @$this->db->query('SELECT id FROM vo');
			if ($q === false) {
				$this->db->queryExec('
		DROP TABLE vo; CREATE TABLE vo (
			id text, 
			name text,
			descr text,
			owner text,
			secret text,
			PRIMARY KEY (id)
		);
		DROP TABLE membership; CREATE TABLE membership (
			vo text REFERENCES vo (id) ON DELETE CASCADE,
			userid text,
			attributes text,
			PRIMARY KEY (vo,userid)
		);
		');
			} 

		} else {
		    throw new Exception('Error creating SQL lite database.');
		}
	}

	function getVOlist($owner = NULL) {
		$where = '';
		if (isset($owner)) {
			$where = "WHERE owner = '$owner'";
		}
		$query = "SELECT * FROM vo " . $where;
		$results = $this->db->arrayQuery($query, SQLITE_ASSOC);
		return $results;
	}

	function getVO($vo = NULL) {
		$query = "SELECT * FROM vo WHERE id = '$vo'";
		$results = $this->db->arrayQuery($query, SQLITE_ASSOC);
		return $results[0];
	}
		
	function addVO($id, $name, $descr, $owner, $secret) {
		$query = "INSERT INTO vo (id,name,descr,owner,secret) VALUES
			('$id', '$name', '$descr', '$owner', '$secret')";
		$results = $this->db->queryExec($query);
		return $results;
	}

	function getVOmembers($vo) {
		$query = "SELECT * FROM membership WHERE vo = '" . addslashes($vo) . "'";
		$results = $this->db->arrayQuery($query, SQLITE_ASSOC);
		return $results;	
	}

	function getVOmembership($vo, $userid) {
		$query = "
			SELECT * FROM membership 
			WHERE 
				vo = '" . addslashes($vo) . "' AND 
				userid = '" . addslashes($userid) . "'";

		$results = $this->db->arrayQuery($query, SQLITE_ASSOC);
		return $results[0];	
	}
	
	function getVOmemberships($userid) {
		$query = "
			SELECT * FROM membership 
			WHERE 
				userid = '" . addslashes($userid) . "'";

		$results = $this->db->arrayQuery($query, SQLITE_ASSOC);
		return $results;	
	}
	
	function addMembership($vo, $userid, $attributes) {
		$query = "INSERT INTO membership (vo,userid,attributes) VALUES
			('$vo', '$userid', '$attributes')";
		$results = $this->db->queryExec($query);
		return $results;
	}

	function updateMembership($vo, $userid, $attributes) {
		$query = "UPDATE membership SET attributes='$attributes' 
			WHERE 
				vo = '" . addslashes($vo) . "' AND 
				userid = '" . addslashes($userid) . "'";
		$results = $this->db->queryExec($query);
		return $results;
	}
	
	
}

