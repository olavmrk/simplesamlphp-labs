<?php
/**
 * @param array &$links  The links on the frontpage, split into sections.
 */
function kalmarlist_hook_frontpage(&$links) {
	assert('is_array($links)');
	assert('array_key_exists("links", $links)');

	$links['federation'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('kalmarlist/'),
		'text' => array('en' => 'Kalmar entities', 'no' => 'Kalmar medlemmer'),
	);
	$links['federation'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('kalmarlist/index.php?extended=1'),
		'text' => array('en' => 'Kalmar entities (more info)', 'no' => 'Kalmar medlemmer (mer info)'),
	);

}
?>