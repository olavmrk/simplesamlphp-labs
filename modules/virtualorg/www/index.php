<?php

/* Load simpleSAMLphp, configuration and metadata */
$config = SimpleSAML_Configuration::getInstance();
$session = SimpleSAML_Session::getInstance();

$metaconfig = SimpleSAML_Configuration::getConfig('module_virtualorg.php');

$authsource = $metaconfig->getValue('auth', 'login-admin');
$useridattr = $metaconfig->getValue('useridattr', 'eduPersonPrincipalName');

if ($session->isValid($authsource)) {
	$attributes = $session->getAttributes();
	// Check if userid exists
	if (!isset($attributes[$useridattr])) 
		throw new Exception('User ID is missing');
	$userid = $attributes[$useridattr][0];
} else {
	SimpleSAML_Auth_Default::initLogin($authsource, SimpleSAML_Utilities::selfURL());
}


$vos = new sspmod_virtualorg_VOStorage();

// $vos->addVO('foobar', 'GEANT3 JRA2', 'Identity Federation group', 'andreas@solweb.no', 'secret123');
// $vos->addMembership('foobar', 'andreas@solweb.no', json_encode($attributes));
// 
// $attributes['o'] = array('GEANT', 'JRA3T2');
// $vos->updateMembership('foobar', 'andreas@solweb.no', json_encode($attributes));

$volist = $vos->getVOlist($userid);

// $vomembership = $vos->getVOmembership('foobar', 'andreas@solweb.no');




$template = new SimpleSAML_XHTML_Template($config, 'virtualorg:volist.php');
$template->data['volist'] = $volist;
$template->data['userid'] = $userid;
$template->show();
