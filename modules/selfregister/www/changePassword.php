<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_selfregister.php');
$formFields = $uregconf->getArray('formFields');

$fields = array('uid', 'oncepw', 'pw1', 'pw2');

if(array_key_exists('sender', $_REQUEST)){
	// Stage 2: Form submitted
	try{

		$validator = new sspmod_selfregister_Registration_Validation(
			$formFields,
			$fields );
		$validValues = $validator->validateInput();
		$newPw = sspmod_selfregister_Util::validatePassword($validValues);

		$store = new sspmod_selfregister_Storage_UserCatalogue();

		if( $store->isValidPassword($validValues['uid'], $validValues['oncepw']) ) {
			$store->changeUserPassword($validValues['uid'], $newPw);
		} else {
			// wrong password given
			throw new sspmod_selfregister_Error_UserException('wrong_pw');
		}

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'selfregister:changepwcomplete.php',
			'selfregister:selfregister');
		$html->show();

	}catch(sspmod_selfregister_Error_UserException $e){
		$formGen = new sspmod_selfregister_XHTML_Form($formFields, 'changePassword.php');
		$values = $validator->getRawInput();
		$formGen->setValues($values);
		$formGen->fieldsToShow($fields);
		$formHtml = $formGen->genFormHtml();

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'selfregister:change_pw.tpl.php',
			'selfregister:selfregister');
		$html->data['formHtml'] = $formHtml;

		$error = $html->t(
			$e->getMesgId(),
			$e->getTrVars()
			);
		$html->data['error'] = htmlspecialchars($error);

		$html->show();
	}
}else{
	// Stage 1: First access to page
	$formGen = new sspmod_selfregister_XHTML_Form($formFields, 'changePassword.php');
	$formGen->fieldsToShow($fields);
	$formHtml = $formGen->genFormHtml();

	$html = new SimpleSAML_XHTML_Template(
		$config,
		'selfregister:change_pw.tpl.php',
		'selfregister:selfregister');
	$html->data['formHtml'] = $formHtml;
	$html->show();
}

?>