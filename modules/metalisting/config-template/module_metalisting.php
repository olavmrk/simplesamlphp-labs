<?php
/* 
 * Configuration for the kalmarlist module.
 * 
 * $Id: $
 */

$config = array (

	'allowedTags' => array('prod', 'test'),
	'defaultTag' => 'prod',
	
	'dirs' => array(
		'prod' => 'metadata-kalmar/metadata-kalmar-consuming',
		'test' => 'metadata-kalmar/metadata-kalmar-consuming-test',
	)

);

