<?php
/**
 * @param array &$links  The links on the frontpage, split into sections.
 */
function metalisting_hook_frontpage(&$links) {
	assert('is_array($links)');
	assert('array_key_exists("links", $links)');

	$links['federation'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('metalisting/'),
		'text' => array('en' => 'Federation entity listing', 'no' => 'Liste over føderasjonsmedlemmer'),
	);
	$links['federation'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('metalisting/index.php?extended=1'),
		'text' => array('en' => 'Federation entity listing (extended)', 'no' => 'Liste over føderasjonsmedlemmer (mer info)'),
	);

}
?>