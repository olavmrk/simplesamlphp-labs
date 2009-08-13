<?php
/**
 * Hook to add the modinfo module to the frontpage.
 *
 * @param array &$links  The links on the frontpage, split into sections.
 */
function virtualorgclient_hook_frontpage(&$links) {
	assert('is_array($links)');
	assert('array_key_exists("links", $links)');

	$links['links']['virtualorgclient'] = array(
		'href' => SimpleSAML_Module::getModuleURL('virtualorgclient/index.php'),
		'text' => array('en' => 'Virtual Organization Client Test', 'no' => 'Virtuelle organisasjon klienttest'),
		'shorttext' => array('en' => 'Virtual Organization Client Test', 'no' => 'Virtuelle organisasjoner klienttest'),
	);

}
?>