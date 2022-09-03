
<p>프로필 이미지 (시작)</p>
<form enctype='multipart/form-data' action='interview_profile.php' method='post'>
    method : <input type="text" name="method" value="start"><br>
    <!--<input type="hidden" name="access_token" value="64d716462e0f012b35549052e8e339713f55ce15">-->
    applier_idx : <input type="text" name="applier_idx" value="869"><br>
	<button>보내기</button>
</form>

<p>프로필 이미지 (촬영 및 앨범 선택 완료)</p>
<form enctype='multipart/form-data' action='interview_profile.php' method='post'>
	<input type='file' name='thumbnail'><br>
    method : <input type="text" name="method" value="profile"><br>
    <!--<input type="hidden" name="access_token" value="64d716462e0f012b35549052e8e339713f55ce15">-->
    applier_idx : <input type="text" name="applier_idx" value="114"><br>
	<button>보내기</button>
</form>

<p>프로필 이미지 (저장된 프로필 호출)</p>
<form enctype='multipart/form-data' action='interview_profile.php' method='post'>
	method : <input type="text" name="method" value="exist"><br>
    applier_idx : <input type="text" name="applier_idx" value="114"><br>
	<button>보내기</button>
</form>

<p>프로필 이미지 (선택완료)</p>
<form enctype='multipart/form-data' action='interview_profile.php' method='post'>
	method : <input type="text" name="method" value="complete"><br>
    applier_idx : <input type="text" name="applier_idx" value="114"><br>
	file_idx : <input type="text" name="file_idx" value="342"><br>
	<button>보내기</button>
</form>

<p>음성 테스트</p>
<form enctype='multipart/form-data' action='interview_audio.php' method='post'>
	applier_idx : <input type="text" name="applier_idx" value="114"><br>
	audio : <input type="text" name="audio" value="1"><br>
	<button>보내기</button>
</form>

<p>셀프 타이머</p>
<form enctype='multipart/form-data' action='interview_timer.php' method='post'>
	applier_idx : <input type="text" name="applier_idx" value="114"><br>
	answer_time : <input type="text" name="answer_time" value="30"><br>
	<button>보내기</button>
</form>

<p>인터뷰 시작</p>
<form enctype='multipart/form-data' action='interview.php' method='post'>
	method : <input type="text" name="method" value="question"><br>
	applier_idx : <input type="text" name="applier_idx" value="114"><br>
	<button>보내기</button>
</form>

<p>답변완료 및 인터뷰 종료</p>
<form enctype='multipart/form-data' action='interview.php' method='post'>
	<input type='file' name='video_blob'><br>
    method : <input type="text" name="method" value="upload"><br>
    applier_idx : <input type="text" name="applier_idx" value="114"><br>
	count : <input type="text" name="count" value="1"><br>
	q_idx : <input type="text" name="q_idx" value="1"><br>
	<button>보내기</button>
</form>

<p>인터뷰 완료</p>
<form enctype='multipart/form-data' action='interview.php' method='post'>
	method : <input type="text" name="method" value="end"><br>
    applier_idx : <input type="text" name="applier_idx" value="936"><br>
	<button>보내기</button>
</form>


<p>인터뷰 URL 테스트 start</p>
<form enctype='multipart/form-data' action='link_applicant.php' method='post'>
	type : <input type="text" name="type" value="start"><br>
    ap_idx : <input type="text" name="ap_idx" value="1995"><br>
	<button>보내기</button>
</form>

<p>인터뷰 URL 테스트 start ms</p>
<form enctype='multipart/form-data' action='link_applicant_ms.php' method='post'>
	type : <input type="text" name="type" value="start"><br>
    ap_idx : <input type="text" name="ap_idx" value="2172"><br>
	<button>보내기</button>
</form>

<p>인터뷰 URL 테스트 end</p>
<form enctype='multipart/form-data' action='link_applicant.php' method='post'>
	type : <input type="text" name="type" value="end"><br>
    ap_idx : <input type="text" name="ap_idx" value="979"><br>
	<button>보내기</button>
</form>

<p>인터뷰 URL 테스트 ap_update</p>
<form enctype='multipart/form-data' action='link_applicant.php' method='post'>
	type : <input type="text" name="type" value="apupdate"><br>
    ap_idx : <input type="text" name="ap_idx" value="965"><br>
	ap_idx : <input type="text" name="old_ap_idx" value="1959"><br>
	<button>보내기</button>
</form>

<p>인터뷰 URL 테스트 재응시</p>
<form enctype='multipart/form-data' action='link_applicant.php' method='post'>
	type : <input type="text" name="type" value="restart"><br>
    ap_idx : <input type="text" name="ap_idx" value="1013"><br>
	ag_req_reason : <input type="text" name="ag_req_reason" value="test"><br>
	com_idx : <input type="text" name="com_idx" value="110"><br>
	<button>보내기</button>
</form>

<p>인터뷰 URL 테스트 reload</p>
<form enctype='multipart/form-data' action='link_applicant.php' method='post'>
	type : <input type="text" name="type" value="reload"><br>
    ap_idx : <input type="text" name="ap_idx" value="2011"><br>
	
	<button>보내기</button>
</form>


<p>인터뷰 상호응답형</p>
<form enctype='multipart/form-data' action='link_applicant.php' method='post'>
	type : <input type="text" name="type" value="restart"><br>
    ap_idx : <input type="text" name="ap_idx" value="1013"><br>
	ag_req_reason : <input type="text" name="ag_req_reason" value="test"><br>
	com_idx : <input type="text" name="com_idx" value="110"><br>
	<button>보내기</button>
</form>

<?php 
//phpinfo();
include_once('common.php');



echo base64url_encode(opensslEncrypt(json_encode('9058')));

//echo setEncrypt222('1502', "bluevisorencrypt");
//echo '<br>MW1QWjVGd0FpNFBGRzgybllFejFhQT09';
//https://interview.highbuff.com//report/detail/E10skJ5ztIg6YSl8m0ZAmcW-iZoqRfwKvbDMdCsW2uGzreQSVzg-CmM4Vxu6ZCj_


//https://interview.highbuff.com/company/itv_view.php?index=dmp0Mjl0Zk51bmRSRUR4N0k5Uy9jQT09
// echo '<br>adfadf'. setDecrypt222('MW1QWjVGd0FpNFBGRzgybllFejFhQT09', "bluevisorencrypt");
//echo base64_decode('MW1QWjVGd0FpNFBGRzgybllFejFhQT09');
 //$key = substr(hash('sha256', 'bluevisorencrypt', true), 0, 32);
 //$iv = substr(hash('sha256', 'bluevisorencrypt', true), 0, 16);
 //echo openssl_decrypt(base64_decode('MW1QWjVGd0FpNFBGRzgybllFejFhQT09'), "AES-256-CBC", $key, false, $iv);


//echo '<br>TV131946vEbnNR/kFGHYyErF/QENOu5W+a8UaYfiBwx13IXu4X8=';

// $kakao = 'TV131946vEbnNR/kFGHYyErF/QENOu5W+a8UaYfiBwx13IXu4X8=';
// $key = substr($kakao, 0, 8);
//             $data = substr($kakao, 8);
//             $data = str_replace(" ", "+", $data);

// $method = "aes-256-cbc";
// $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
// $hash_key = substr(hash('sha256', $key, true), 0, 32);
// $decrypted_data = explode("||#", openssl_decrypt(base64_decode($data), $method, $hash_key, OPENSSL_RAW_DATA, $iv)); //0:사용자인덱스, 1:템플릿코드

// $idx = $decrypted_data[0];
// $code = $decrypted_data[1];
// echo '<br>'.$idx;
//https://interview.highbuff.com/linkInterview?kakao=TV131946vEbnNR/kFGHYyErF/QENOu5W+a8UaYfiBwx13IXu4X8=
//https://interview.highbuff.com/linkInterview?kakao=TXczQVhkdjVMdU5mVlBEMGVod3p4QT09
?>