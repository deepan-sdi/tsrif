<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
error_reporting(E_ALL);
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

define('SITE_IMAGE_PATH', '/Front/img/');
define('SITE_SCRIPT_PATH', '/Front/js/');
define('SITE_STYLE_PATH', '/Front/css/');

// Config vars
if($_SERVER['HTTP_HOST'] == 'snapstatelocal.com') {
	define('HOST', 'mongodb://localhost:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', 'snapstate');
	define('DATABASE', 'snapstate');
	define('ADMIN_USER_ID', '5267a1b88ead0eab15000000');
	define('USER_GROUP_ID', '5270988f8ead0e2648000000');
	define('CONTRIBUTOR_GROUP_ID', '52abfbd68ead0efa37010000');
	define('FB_RETURN_URL', 'http://snapstatelocal.com/?fblogin=1');
	define('TEMP_FB_APPID', '353997868079462');
	define('ACTIVATION_URL', 'http://snapstatelocal.com/activate/');
	define('REDIRECT_URL', 'http://snapstatelocal.com');
	define('PERPAGE', '3');
	define('MAILER', '0');
	define('DOMAINPATH', 'http://snapstatelocal.com');
	define('ADMIN_EMAIL', 'info@snapstate.com');
	define('FB_INVITATION_MSG', "Friends, join in Snapstate.com now and get more benifits - Snap yourself into state.");
} else {
	define('HOST', 'mongodb://localhost:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', '1et2s!tp3831!o');
	define('DATABASE', 'snapstate');
	define('ADMIN_USER_ID', '526f3f745a41226975cd9676');
	define('USER_GROUP_ID', '5270b3cf5a41220c46808733');
	define('CONTRIBUTOR_GROUP_ID', '5270b3db5a4122e748808733');
	define('FB_RETURN_URL', 'http://snapstate.sdiphp.com');
	define('TEMP_FB_APPID', '578844945519464');
	define('ACTIVATION_URL', 'http://snapstate.sdiphp.com/activate/');
	define('REDIRECT_URL', 'http://snapstate.sdiphp.com');
	define('PERPAGE', '18');
	define('MAILER', '1');
	define('DOMAINPATH', 'http://snapstate.sdiphp.com');
	define('ADMIN_EMAIL', 'info@snapstate.com');
	define('FB_INVITATION_MSG', "Friends, join in Snapstate.com now and get more benifits - Snap yourself into state.");
}

define('SMTP_USERNAME_DEMO', "snapstate123@gmail.com");
define('SMTP_PASSWORD_DEMO', "developer");

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
	define('PROTOCOL', 'https');
} else {
	define('PROTOCOL', 'http');
}
/************************************
*	Method: connect     	         
*  Purpose: To connect with MongoDB 
***********************************/

function connect()
{
	//$conn = new \Mongo(HOST, array("username" => USERNAME, "password" => PASSWORD, "db" => DATABASE));
	$conn = new \Mongo(HOST);
	return $conn;
}
function getAppID() {
	$conn		= connect();
	$collection	= $conn->snapstate->site_settings;
	$cursor		= $collection->find();
	while($cursor->hasNext())
	{
		$resultArray	= $cursor->getNext();
	}
	$appID	= '';
	if(is_array($resultArray) && count($resultArray) > 0) {
		$appID	= $resultArray['fb_app_id'];
	}
	return $appID;
}
$appID	= getAppID();
$appID	= (trim($appID) != '') ? $appID : TEMP_FB_APPID;
define('FB_APPID', $appID);

//	SMTP Mailer
include "class.phpmailer.php";

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
