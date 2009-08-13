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

$id = $_REQUEST['id'];

if (isset($_POST['type']) && $_POST['type'] === 'createnew') {
	
	if (!isset($_POST['name'])) throw new Exception('VO name not provided.');
	$name = $_POST['name'];
	
	if (!isset($_POST['descr'])) throw new Exception('VO descr not provided.');
	$descr = $_POST['descr'];
	
	$secret = SimpleSAML_Utilities::generateID();
	
	$vos->addVO($id, $name, $descr, $userid, $secret);
}

$vometa = $vos->getVO($id);
$vomembers = $vos->getVOmembers($id);

#echo('<pre>'); print_r($vometa); exit;


// $vos->addVO('foobar', 'GEANT3 JRA2', 'Identity Federation group', 'andreas@solweb.no', 'secret123');
// $vos->addMembership('foobar', 'andreas@solweb.no', json_encode($attributes));
// 
// $attributes['o'] = array('GEANT', 'JRA3T2');
// $vos->updateMembership('foobar', 'andreas@solweb.no', json_encode($attributes));

// $volist = $vos->getVOlist($userid);

// $vomembership = $vos->getVOmembership('foobar', 'andreas@solweb.no');

$token = sha1($metaconfig->getValue('secret')  . '|' . $id . '|' . $vometa['secret']);

$registerURL = SimpleSAML_Module::getModuleURL('virtualorg/register.php?id=' . $id . '&amp;token=' . $token);


$template = new SimpleSAML_XHTML_Template($config, 'virtualorg:vo.tpl.php');
$template->data['vometa'] = $vometa;
$template->data['vomembers'] = $vomembers;
$template->data['registerURL'] = $registerURL;
$template->show();
