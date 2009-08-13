<?php

/*
 * OAuth section
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/oauth/libextinc/OAuth.php');
$oauthconfig = SimpleSAML_Configuration::getConfig('module_oauth.php');

$store = new sspmod_oauth_OAuthStore();
$server = new sspmod_oauth_OAuthServer($store);

$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
$plaintext_method = new OAuthSignatureMethod_PLAINTEXT();

$server->add_signature_method($hmac_method);
$server->add_signature_method($plaintext_method);

$req = OAuthRequest::from_request();
list($consumer, $token) = $server->verify_request($req);

$data = $store->getAuthorizedData($token->key);
/*
 * -----------
 */




$metaconfig = SimpleSAML_Configuration::getConfig('module_virtualorg.php');

$vos = new sspmod_virtualorg_VOStorage();

#if (!isset($_REQUEST['method'])) throw new Exception('Method parameter not provided');

#if ($method === 'memberOf') {
	$memberof = $vos->getVOmemberships($data['eduPersonPrincipalName'][0]);
	echo json_encode($memberof); exit;
#}



