<?php
#20.06.16 igg add 각 sns 로그인을 위한 변수 설정
#구글, 카카오, 네이버별 각 client _id, client_secret, redirect_uri 상수 설정

#GOOGLE 21.06.03 완료
define('GOOGLE_CLIENT_ID', '265440924719-5a242gqtatmolvgino9l4vtr7jpnnq2u.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'lXPgmBWQO3QUuLcoddTK9ifp');
define('GOOGLE_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/lib/sns/google/call_back.php');

#KAKAO 21.06.02 완료
define('KAKAO_CLIENT_ID', 'c622851e95a98fbe13ba6a94d0598a5b');
//define('KAKAO_CLIENT_SECRET', 'YjUtC75FGk7wsfxz4crfDCn5Y1FTYySd');
define('KAKAO_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/lib/sns/kakao/call_back.php');

#NAVER 21.06.03 완료
define('NAVER_CLIENT_ID', 'xgw7omXoMTrWdMLU9cw2');
define('NAVER_CLIENT_SECRET', 'Xd1WE28MgA');
define('NAVER_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/lib/sns/naver/call_back.php');

#APPLE 21.06.02 완료
define('APPLE_CLIENT_ID', 'interview.bluevisor.com');
define('APPLE_CLIENT_SECRET', '6LFD4FPL6S');
define('APPLE_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/lib/sns/apple/call_back.php');
//define('APPLE_REDIRECT_URI_US', 'https://'.$_SERVER["HTTP_HOST"].'/lib/sns/apple/call_back_us.php');

?>