<?php
// include our OAuth2 Server object
require_once __DIR__.'/server.php';

/*
1) 앱에서 아이디/패스워드 입력

2) 아이디/패스워드가 유효하다면 인증서버로 아이디, APP ID(token) 전달

2-1) 인증서버에 아이디+APP ID 정보가 등록되어있지않다면(=최초로그인시)
 - client_id 는 아이디 client_secret은 APP ID로 oauth_clients 테이블에 INSERT 동작

2-2) 인증서버에 아이디+APP ID가 등록되어있다면
 - access_token 생성하여 전달

나머지 게시판 작업
공지사항에 index 추가
*/

//22.02.08 igg POST로 들어오는 id, secret 값 체크
$request = OAuth2\Request::createFromGlobals();
$grant_type = isset($request->request['grant_type']) ? $request->request['grant_type'] : ''; 

if ($grant_type == 'client_credentials'){
    $client_id = isset($request->request['client_id']) ? $request->request['client_id'] : '';
    $client_secret = isset($request->request['client_secret']) ? $request->request['client_secret'] : '';
    $client_scope = isset($request->request['client_scope']) ? $request->request['client_scope'] : '';
    $user_id = isset($request->request['user_id']) ? $request->request['user_id'] : '';

    if($client_id == '' || $client_secret == '' || $client_scope == '') {
        echo json_encode(array('error' => 'invalid_request', 'error_description' => 'Invaild client POST information'));
        return;
    }

    if ($storage->checkClientCredentials($client_id, $client_secret) === false) { //client_id가 등록안되있으면 계정 등록 후 accesstoken 발급
        $storage->setClientDetails($client_id, $client_secret, null, null, $client_scope, $user_id);
        $server->handleTokenRequest($request)->send();
    } else { //등록되있는 계정이라면 get access token 하여 유효한지 확인함
        $result = $storage->getClientAccessToken($client_id); //get access_token + check access_token 
        if (isset($result['error'])) { //error or expired
            if ($result['error'] == 'Invalid access token') { //expired token
                //$access_token = $storage->generateAccessTokenPublic();
                $expires = strtotime("+86400 seconds"); //expire 1day
                $access_token = $storage->updateAccessToken($result['access_token'], $client_id, $user_id, $expires, $client_scope);
                if (!$access_token) { //update fail
                    echo json_encode(array('error' => 'Invalid execute query'));
                } else {
                    echo json_encode(array('access_token' => $access_token, 'check' => 1)); //access_token 넘겨주는 곳 새로 업데이트
                }
            } else { //execute query error
                echo json_encode(array('error' => 'Invalid execute query'));
            }
        } else { //success access token
            echo json_encode(array('access_token' => $result['access_token'], 'check' => 2)); //access_token 넘겨주는 곳 기존에 있던 토큰
        }
    }
} else if ($grant_type == 'token_credentials'){
    $access_token = isset($request->request['access_token']) ? $request->request['access_token'] : '';

    if ($access_token == '') {
        echo json_encode(array('error' => 'invalid_request', 'error_description' => 'Invaild POST access_token'));
        return;
    }
    
    $user_id = $storage->getUserId($access_token);
    if ($user_id === false || $user_id == '') { //client id not found 
        echo json_encode(array('error' => 'invalid_request', 'error_description' => 'user_id is not found'));
    } else {
        echo json_encode(array('user_id' => $user_id));
    }
} else if ($grant_type == 'token_refresh_credentials'){
    $access_token = isset($request->request['access_token']) ? $request->request['access_token'] : '';

    if ($access_token == '') {
        echo json_encode(array('error' => 'invalid_request', 'error_description' => 'Invaild POST access_token'));
        return;
    }
    
    $access_token = $storage->updateAccessTokenSingle($access_token);
    if (!$access_token) { //update fail
        echo json_encode(array('error' => 'Invalid execute query'));
    } else {
        echo json_encode(array('access_token' => $access_token));
    }
} else if ($grant_type == 'unset_credentials'){
    $access_token = isset($request->request['access_token']) ? $request->request['access_token'] : '';

    if ($access_token == '') {
        echo json_encode(array('error' => 'invalid_request', 'error_description' => 'Invaild POST access_token'));
        return;
    }
    
    $result = $storage->deleteAccessToken($access_token);
    if ($result) { // success
        echo json_encode(array('result' => $result));
    } else { //fail
        echo json_encode(array('error' => 'invalid execute query'));
    }
} else {
    // Handle a request for an OAuth2.0 Access Token and send the response to the client
    $server->handleTokenRequest($request)->send();
}
