<?php
/**
 *
 * @param array &$links  The links on the frontpage, split into sections.
 */
function selfregister_hook_frontpage(&$links) {
	assert('is_array($links)');
	assert('array_key_exists("links", $links)');

	$links['auth'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('selfregister/newUser.php'),
		'text' => '{selfregister:formdict:link_newuser}',
	);
	$links['auth'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('selfregister/reviewUser.php'),
		'text' => '{selfregister:formdict:link_review}',
	);
	$links['auth'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('selfregister/lostPassword.php'),
		'text' => '{selfregister:formdict:link_lostpw}',
	);
	$links['auth'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('selfregister/changePassword.php'),
		'text' => '{selfregister:formdict:link_changepw}',
	);

}
