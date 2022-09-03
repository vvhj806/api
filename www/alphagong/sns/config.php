<?php
#20.06.16 igg add 각 sns 로그인을 위한 변수 설정
#구글, 카카오, 네이버별 각 client _id, client_secret, redirect_uri 상수 설정

#GOOGLE 21.06.03 완료
define('GOOGLE_CLIENT_ID', '345733800096-m160bqr0dvr09r7t76ieu5p1m7ieol01.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'xOnLBOtL1BnUhM9pqEmXo9ok');
define('GOOGLE_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/alphagong/sns/google/call_back.php');

#KAKAO 21.06.02 완료
define('KAKAO_CLIENT_ID', '4ac74d02dc5e5e2d211d87c4d67a33bc');
//define('KAKAO_CLIENT_SECRET', 'YjUtC75FGk7wsfxz4crfDCn5Y1FTYySd');
define('KAKAO_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/alphagong/sns/kakao/call_back.php');

#NAVER 21.06.03 완료
define('NAVER_CLIENT_ID', 'sQpYME2IBK7bWkYYBZLY');
define('NAVER_CLIENT_SECRET', 'yVyjCNrY5D');
define('NAVER_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/alphagong/sns/naver/call_back.php');

#APPLE 21.06.02 완료
define('APPLE_CLIENT_ID', 'Alphagong-iOS.bluevisor.kr');
define('APPLE_CLIENT_SECRET', '738B4R89BJ');
define('APPLE_REDIRECT_URI', 'https://'.$_SERVER["HTTP_HOST"].'/alphagong/sns/apple/call_back.php');
?>