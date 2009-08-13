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

if (!isset($_REQUEST['id'])) throw new Exception('VO ID not provided.');
if (!isset($_REQUEST['userid'])) throw new Exception('User ID not provided.');

$id = $_REQUEST['id'];
$edituser = $_REQUEST['userid'];

$vometa = $vos->getVO($id);

if ($vometa['owner'] !== $userid) throw new Exception('You are not the owner of this VO and cannot edit attributes.');

$vomembership = $vos->getVOmembership($id, $edituser);
$voattributes = json_decode($vomembership['attributes'], TRUE);

if (isset($_POST['type']) && $_POST['type'] === 'edit') {
	
	foreach($_POST AS $k => $v) {
		if (preg_match('/attribute_(.*)/', $k, $matches)) {
			$voattributes[$matches[1]] = explode(',', $_POST[$k]);
		}
	}
	
	$vos->updateMembership($id, $edituser, json_encode($voattributes));
	
}


$fixedAttributes = array('mail', 'o', 'displayName');



#echo('<pre>'); print_r($vomembership); exit;


$template = new SimpleSAML_XHTML_Template($config, 'virtualorg:attributes.tpl.php');
$template->data['id'] = $id;
$template->data['edituser'] = $edituser;
$template->data['vomembership'] = $vomembership;
$template->data['voattributes'] = $voattributes;
$template->data['fixedAttributes'] = $fixedAttributes;
$template->show();
