<?php

include_once('common.php');

$key = $_GET['key'];


$newKey = base64url_encode(opensslEncrypt(json_encode($key)));

header('Location: https://interview.highbuff.com/report/detail2/'.$newKey.'?gs=m');
