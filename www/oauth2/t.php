<?php
// include our OAuth2 Server object
require_once __DIR__.'/server.php';


echo $aa;

// create test clients in memory
$clients = array('TestClient' => array('client_secret' => 'TestSecret'));

// create a storage object
$storage = new OAuth2\Storage\Memory(array('client_credentials' => $clients));

// create the grant type
$grantType = new OAuth2\GrantType\ClientCredentials($storage);

// add the grant type to your OAuth server
$server->addGrantType($grantType);