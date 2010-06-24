<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_selfregister.php');
$formFields = $uregconf->getArray('formFields');
$storeConf = SimpleSAML_Configuration::loadFromArray(
	sspmod_selfregister_Storage_UserCatalogue::getSelectedStorageConfig() );
$user_id_param = $storeConf->getString('user.id.param', 'uid');

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('auth');
$as = new SimpleSAML_Auth_Simple($asId);
$as->requireAuth();
$attributes = $as->getAttributes();

$formGen = new sspmod_selfregister_XHTML_Form($formFields, 'changePassword.php');
$fields = array('pw1', 'pw2');
$formGen->fieldsToShow($fields);

$html = new SimpleSAML_XHTML_Template(
	$config,
	'selfregister:change_pw.tpl.php',
	'selfregister:selfregister');

if(array_key_exists('sender', $_REQUEST)) {
	// Stage 2: Form submitted
	try{
		$validator = new sspmod_selfregister_Registration_Validation(
			$formFields,
			$fields );
		$validValues = $validator->validateInput();
		$newPw = sspmod_selfregister_Util::validatePassword($validValues);
		$store = sspmod_selfregister_Storage_UserCatalogue::instantiateStorage();
		$store->changeUserPassword($attributes[$user_id_param][0], $newPw);
		$html->data['userMessage'] = 'message_chpw';

	} catch(sspmod_selfregister_Error_UserException $e) {
		$error = $html->t(
			$e->getMesgId(),
			$e->getTrVars()
			);
		$html->data['error'] = htmlspecialchars($error);
	}
} elseif(array_key_exists('logout', $_GET)) {
	$as->logout('index.php');
}

$formGen->setSubmitter('submit_change');
$html->data['formHtml'] = $formGen->genFormHtml();
$html->data['uid'] = $attributes[$user_id_param][0];
$html->show();

?>
