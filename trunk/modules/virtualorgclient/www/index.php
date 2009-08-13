<?php

/* Load simpleSAMLphp, configuration and metadata */
$config = SimpleSAML_Configuration::getInstance();
$oauthconfig = SimpleSAML_Configuration::getConfig('module_oauth.php');

require_once($config->resolvePath('modules/oauth/libextinc/OAuth.php'));


$session = SimpleSAML_Session::getInstance();



$userid = NULL;
$voreceived = FALSE;
$vomemberships = NULL;
$accessTokenKey = NULL;

if (isset($_REQUEST['login'])) {
	$authsource = 'saml2';
	$useridattr = 'eduPersonPrincipalName';

	if ($session->isValid($authsource)) {
		$attributes = $session->getAttributes();
		// Check if userid exists
		if (!isset($attributes[$useridattr])) 
			throw new Exception('User ID is missing');
		$userid = $attributes[$useridattr][0];
	} else {
		SimpleSAML_Auth_Default::initLogin($authsource, SimpleSAML_Utilities::selfURL());
	}
}


# module.php/virtualorg/data_oauth_json.php

$baseurl = 'http://dev.andreas.feide.no/simplesaml/';
$key = 'key';
$secret = 'secret';


$consumer = new sspmod_oauth_Consumer($key, $secret);


if (isset($_REQUEST['step']) && $_REQUEST['step'] == '1') {
 
	$oauthsess = SimpleSAML_Utilities::generateID();

	// Get the request token
	$requestToken = $consumer->getRequestToken($baseurl . '/module.php/oauth/requestToken.php');
	
	#print_r($requestToken); exit;
	
	$session->setData('oauthSess', $oauthsess, serialize($requestToken));

	
#	echo "Got a request token from the OAuth service provider [" . $requestToken->key . "] with the secret [" . $requestToken->secret . "]\n";

	$callback = SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURLNoQuery(), array(
		'step' => '2',
		'oauthsess' => $oauthsess,
	));

	// Authorize the request token
	$url = $consumer->getAuthorizeRequest($baseurl . '/module.php/oauth/authorize.php', $requestToken, TRUE, $callback);
#	echo('Go to this URL to authenticate/authorize the request: ' . $url . "\n");

} elseif (isset($_REQUEST['step']) && $_REQUEST['step'] == '2') {

	$requestToken = unserialize($session->getData('oauthSess', $_REQUEST['oauthsess']));
	
#	print_r($requestToken); exit;

	// Replace the request token with an access token
	$accessToken = $consumer->getAccessToken( $baseurl . '/module.php/oauth/accessToken.php', $requestToken);
	
	$session->setData('accessToken',  'accesstoken', serialize($accessToken));
	SimpleSAML_Utilities::redirect('index.php?step=3'); exit;
	
} 

if ($adata = $session->getData('accessToken',  'accesstoken')) {
	$accessToken = unserialize($adata);

	$vomemberships = $consumer->getUserInfo($baseurl . '/module.php/virtualorg/data_oauth_json.php?method=memberOf', $accessToken);
	$voreceived = TRUE;
	$accessTokenKey = $accessToken->key;
	
	# echo('<pre>'); print_r($vomemberships); exit;
}

	

$template = new SimpleSAML_XHTML_Template($config, 'virtualorgclient:client.tpl.php');
$template->data['vomemberships'] = $vomemberships;
$template->data['voreceived'] = $voreceived;
$template->data['accessToken'] =  $accessTokenKey;
$template->data['userid'] =  $userid;
$template->show();
