<?php
/**
 * Hook to add the modinfo module to the frontpage.
 *
 * @param array &$links  The links on the frontpage, split into sections.
 */
function virtualorg_hook_frontpage(&$links) {
	assert('is_array($links)');
	assert('array_key_exists("links", $links)');

	$links['links']['virtualorg'] = array(
		'href' => SimpleSAML_Module::getModuleURL('virtualorg/index.php'),
		'text' => array('en' => 'Virtual Organization', 'no' => 'Virtuelle organisasjoner'),
		'shorttext' => array('en' => 'Virtual Organization', 'no' => 'Virtuelle organisasjoner'),
	);

}
?>