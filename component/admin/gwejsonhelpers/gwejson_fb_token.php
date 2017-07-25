<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
defined('JPATH_BASE') or die;

function ProcessJsonRequest(&$requestObject, $returnData)
{
	// Disable error/notices other wise JSON call can fail.
	ini_set('display_errors', 0);

	if (!isset($requestObject->ShortLifeToken))
	{
		PlgSystemGwejson::throwerror('There was an error - no Short Life Token was found?');
	}

	// Include JEvents defines
	include_once JPATH_ADMINISTRATOR . '/components/com_jevents/jevents.defines.php';

	// Include Facebook SDK
	require_once JPATH_ADMINISTRATOR . '/components/com_jevents/vendor/autoload.php';

	$params = JComponentHelper::getParams('com_jevents');

	$app_id     = $requestObject->AppID;
	$app_secret = $requestObject->AppSecret;
	$app_short_token = $requestObject->ShortLifeToken;

	$longLifeTokenUrl = "https://graph.facebook.com/oauth/access_token?client_id=" . $app_id . "&client_secret=" . $app_secret . "&grant_type=fb_exchange_token&fb_exchange_token=" . $app_short_token;

	$resp = json_decode(file_get_contents($longLifeTokenUrl));

	$requestObject->data    = new stdClass;
	$requestObject->LongLifeToken = $resp->access_token;

	$neverExpireTokenUrl =  "https://graph.facebook.com/oauth/access_token?client_id=" . $app_id . "&client_secret=" . $app_secret . "&grant_type=fb_exchange_token&fb_exchange_token=" . $resp->access_token;

	$NEresp = json_decode(file_get_contents($neverExpireTokenUrl));

	$requestObject->NeverExpireToken = $NEresp->access_token;


	return $requestObject;
}

//Skip token check since we are just fetching an access token from facebook.
function gwejson_skiptoken() {
	return true;
}