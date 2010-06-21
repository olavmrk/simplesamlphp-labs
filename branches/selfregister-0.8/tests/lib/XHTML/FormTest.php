<?php

require '/home/thomasg/workspace/simplesamlphp/lib/_autoload.php';

class FormTest extends PHPUnit_Framework_TestCase {

	protected $emptyFormHtml = '<form action="?" method="post"><br /><input type="submit" name="sender" value="Submit" /><br /></form>';

	protected $fieldsDef = array(
		'uid' => array(
			control_type => 'text',
			),
		'givenName' => array(
			control_type => 'text',
			),
		'sn' => array(
			control_type => 'text',
			),
		'cn' => array(
			control_type => 'text',
			),
		'mail' => array(
			control_type => 'text',
			),
		'eduPersonPrincipalName' => array(
			control_type => 'text',
			),
		'userPassword' => array(
			control_type => 'text',
			),
		);


	public function testEmptyForm() {
		$formGen = new sspmod_selfregister_XHTML_Form();
		$formHtml = $formGen->genFormHtml();
		$this->assertEquals($this->emptyFormHtml, $formHtml);
	}


	public function testTranlateSelectedTags() {
		$formGen = new sspmod_selfregister_XHTML_Form($this->fieldsDef);
		$formGen->fieldsToShow(
			array('uid',
				  'givenName',
				  'sn',
				  'cn',
				  'mail',
				  'eduPersonPrincipalName',
				  'userPassword')
			);
		$formHtml = $formGen->genFormHtml();
		$this->assertNotContains('not translated', $formHtml);
	}


}



?>