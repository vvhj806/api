<?php
$dsn      = 'mysql:dbname=my_oauth2_db;host=localhost';
$username = 'oauth';
$password = 'Comcomcom12!@';

date_default_timezone_set('Asia/Seoul');

// error reporting (this is a demo, after all!)
ini_set('display_errors',1);error_reporting(E_ALL);

// Autoloading (composer is preferred, but for this example let's just do this)
require_once(__DIR__.'/src/OAuth2/Autoloader.php');
OAuth2\Autoloader::register();

// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

// Pass a storage object or array of storage objects to the OAuth2 server class
//$server = new OAuth2\Server($storage);
$server = new OAuth2\Server($storage, array(
    'access_lifetime' => 86400 //1 day
));

// Add the "Client Credentials" grant type (it is the simplest of the grant types)
$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

// Add the "Authorization Code" grant type (this is where the oauth magic happens)
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));


//print_r($server);

//
//// create some users in memory
//$users = array('bshaffer' => array('password' => 'brent123', 'first_name' => 'Brent', 'last_name' => 'Shaffer'));
//
//// create a storage object
//$storage = new OAuth2\Storage\Memory(array('user_credentials' => $users));
//
//// create the grant type
//$grantType = new OAuth2\GrantType\UserCredentials($storage);
//
//// add the grant type to your OAuth server
//$server->addGrantType($grantType);
//
//$server->addGrantType(new OAuth2\GrantType\RefreshToken($storage, array(
//    'always_issue_new_refresh_token' => true,
//    'refresh_token_lifetime'         => 2419200,
//)));