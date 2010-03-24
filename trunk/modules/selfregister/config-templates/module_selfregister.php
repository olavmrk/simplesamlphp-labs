<?php
  /*
   * The configuration of wikiplex
   *
   *
   */

$config = array (

	/* The authentication source that should be used. */
	'auth' => 'selfregister-ldap',

	// Realm for eduPersonPrincipalName
	'user.realm' => 'example.org',

	// Usen in mail and on pages
	'system.name' => 'Selfregister module',

	// Mailtoken valid for 5 days
	'mailtoken.lifetime' => (3600*24*5),
	'mail.from'     => 'Example <na@example.org>',
	'mail.replyto'  => 'Example <na@example.org>',
	'mail.subject'  => 'Example - email verification',


	// Db backend
	'storage.backend' => 'LdapMod',

	// LDAP backend configuration
	// This is configured in authsources.php
	'ldap' => array(
		'admin.dn' => 'cn=admin,dc=example,dc=org',
		'admin.pw' => 'xyz',

		// LDAP objectClass'es
		'objectClass' => array(
			'inetOrgPerson',
			'organizationalPerson',
			'person',
			'top',
			'eduPerson',
			'norEduPerson'
			),
		), // end Ldap config

	// AWS SimpleDB configuration

	// SQL backend configuration

	// Password policy enforcer
	// Inspiration and backgroud
	// http://www.hq.nasa.gov/office/ospp/securityguide/V1comput/Password.htm


	// Db field names to web field names mapping
	// Registated attributes
	'attributes'  => array(
		'uid' => 'uid',
		'givenName' => 'givenName',
		'sn' => 'sn',
		// Will be a combination for givenName and sn.
		'cn' => 'cn',
		'mail' => 'mail',
		// uid and appended realm
		'eduPersonPrincipalName' => 'eduPersonPrincipalName',
		// Set from password walidataion and encryption
		'userPassword' => 'userPassword'),

	// Is it a good solution to indicat read only values here?

	// Web fields specification
	// This controlls the order of the fields
	'formFields' => array(
		/*
		'testint' => array(
			'validate' => array(
				'filter'    => FILTER_VALIDATE_INT,
				'flags'     => FILTER_REQUIRE_ARRAY,
				'options'   => array('min_range' => 1, 'max_range' => 10)
				),
			'layout' => array(),
			), */
		// First name (ldap: givenName)
		'givenName' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'text'),
			), // end givenName
		// Surname (ldap: sn)
		'sn' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'text',
				),
			), // end ename
		'mail' => array(
			'validate' => FILTER_VALIDATE_EMAIL,
			'layout' => array(
				'control_type' => 'text',
				),
			), // end mail
		'uid' => array(
			'validate' => array(
				'filter'  => FILTER_VALIDATE_REGEXP,
				'options' => array("regexp"=>"/^[a-z]{1}[a-z0-9\-]{2,15}$/")
				),
			'layout' => array(
				'control_type' => 'text',
				),
			), // end uid
		// Common name: read only
		'cn' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'text',
				),
			), // end cn
		// eduPersonPrincipalName
		'eduPersonPrincipalName' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'text',
				),
			), // end eduPersonPrincipalName
		'userPassword' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'password',
				),
			), // end pw1
		'pw1' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'password',
				),
		'pw2' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'password',
				),
			), // end pw2
		// Old password when change, or password given on time when changing
		'oncepw' => array(
			'validate' => FILTER_DEFAULT,
			'layout' => array(
				'control_type' => 'password',
				),
			), // end oncepw
		),

);