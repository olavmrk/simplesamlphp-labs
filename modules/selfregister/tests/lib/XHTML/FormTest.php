<?php

require '/home/thomasg/Uninett/workspace/simplesamlphp/lib/_autoload.php';

class FormTest extends PHPUnit_Framework_TestCase {

	protected $emptyFormHtml = '<form action="?" method="post"><br /><input type="submit" name="sender" value="Submit" /><br /></form>';

	public function testEmptyForm() {
		$formGen = new sspmod_selfregister_XHTML_Form();
		$formHtml = $formGen->genFormHtml();
		$this->assertEquals($this->emptyFormHtml, $formHtml);
	}

}



?>