<?php


// Configuration
$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_selfregister.php');
$eppnRealm = $uregconf->getString('user.realm');

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('auth');
$as = new SimpleSAML_Auth_Simple($asId);
/* Require the usr to be authentcated. */
$as->requireAuth();

/* Retrieve attributes of the user. */
$attributes = $as->getAttributes();

$formFields = $uregconf->getArray('formFields');
$reviewAttr = $uregconf->getArray('attributes');
$readOnlyFields = array('mail', 'uid');

$formGen = new sspmod_selfregister_XHTML_Form($formFields, 'reviewUser.php');
// $showFields = sspmod_selfregister_Util::genFieldView($reviewAttr);
$showFields = array('uid', 'fname', 'sname', 'mail');
$formGen->fieldsToShow($showFields);
$formGen->setReadOnly($readOnlyFields);

$html = new SimpleSAML_XHTML_Template(
	$config,
	'selfregister:reviewuser.tpl.php',
	'selfregister:formdict');


if(array_key_exists('sender', $_POST)){
	try{
		// Update user object
		// $listValidate = sspmod_selfregister_Util::genFieldView($reviewAttr);
		$validator = new sspmod_selfregister_Registration_Validation(
			$formFields,
			$showFields);
		$validValues = $validator->validateInput();

		// FIXME: Filter password
		$remove = array('userPassword' => NULL);
		$reviewAttr = array_diff_key($reviewAttr, $remove);

		$eppnRealm = $uregconf->getString('user.realm');
		$userInfo = sspmod_selfregister_Util::processInput(
			$validValues,
			$reviewAttr);

		$store = new sspmod_selfregister_Storage_UserCatalogue();
		$uid = $attributes['uid'][0];
		$store->updateUser($uid, $userInfo);

		$values = $validator->getRawInput();
		$html->data['userMessage'] = 'Information updated successfully';

	}catch(sspmod_selfregister_Error_UserException $e){
		// Some user error detected
		$values = $validator->getRawInput();

		$values['mail'] = $attributes['mail'][0];
		$values['uid'] = $attributes['uid'][0];

		$error = $html->t(
			$e->getMesgId(),
			$e->getTrVars()
			);

		$html->data['error'] = htmlspecialchars($error);
	}
}elseif(array_key_exists('logout', $_GET)){
	$as->logout('newUser.php');
 }else{
	// The GET access this endpoint
	$values = sspmod_selfregister_Util::filterAsAttributes($attributes, $reviewAttr);
}

$formGen->setValues($values);
$formHtml = $formGen->genFormHtml();
$html->data['formHtml'] = $formHtml;

$html->show();

?>