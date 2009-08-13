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
if (!isset($_REQUEST['token'])) throw new Exception('VO register token not provided.');

$id = $_REQUEST['id'];

# echo ('<pre>'); print_r($attributes); exit;

$insertAttributes = array(
	'displayName' => $attributes['cn'],
	'mail' => $attributes['mail'],
	'o' => $attributes['o'],
);



$vometa = $vos->getVO($id);
$token = sha1($metaconfig->getValue('secret')  . '|' . $id . '|' . $vometa['secret']);
if ($_REQUEST['token'] !== $token) throw new Exception('VO Registration token was invalid.');

$membership = $vos->getVOmembership($id, $userid);
if (!empty($membership)) {
	throw new Exception('You are already member of this virtual organization');
} 


$vos->addMembership($id, $userid, json_encode($insertAttributes));

echo('You [' . $userid . '] are successfully registered as a member of the virtual organization [' . $id . ']');
exit;





