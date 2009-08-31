<?php

$config = SimpleSAML_Configuration::getInstance();
$session = SimpleSAML_Session::getInstance();

$kconfig = SimpleSAML_Configuration::getConfig('module_metalisting.php');

$tag = $kconfig->getString('defaultTags', 'prod');
$allowedTags = $kconfig->getArray('allowedTags');
if (isset($_REQUEST['set'])) {
	if (in_array($_REQUEST['set'], $allowedTags)) $tag = $_REQUEST['set'];
}
$kdconfig = $kconfig->getConfigItem('dirs');
$dir = $kdconfig->getString($tag);

// echo('<pre>');
// print_r($tag);
// print_r($allowedTags);
// print_r($dir);
// exit;

$mh = new SimpleSAML_Metadata_MetaDataStorageHandlerSerialize(array('directory' => $dir));


$metaentries = array();

$metaentries['remote']['saml20-idp-remote'] = $mh->getMetadataSet('saml20-idp-remote');
$metaentries['remote']['saml20-sp-remote'] = $mh->getMetadataSet('saml20-sp-remote');

// echo('<pre>');
// print_r($mentries);


$t = new SimpleSAML_XHTML_Template($config, 'metalisting:metalisting.tpl.php');
$t->data['header'] = 'Federation entities';
$t->data['metaentries'] = $metaentries;
$t->data['extended'] = (isset($_REQUEST['extended']));
$t->show();

exit;






?>