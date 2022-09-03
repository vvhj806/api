<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

exit;

#분석지표 계산하는 함수
function calculAnalysis($json) {

  $dialect = $json["dialect"];  //방언빈도
  $quiver = $json["quiver"];   //음성떨림
  $volume = $json["volume"];   //음성크기
  $tone = $json["tone"];   //목소리톤
  $speed = $json["speed"];   //음성속도
  $diction = $json["diction"];   //발음정확도
  $sincerity = $json["sincerity"];   //성싱답변률
  $understanding = $json["understanding"];   //질문이해도
  $eyes = $json["eyes"];   //시선처리
  $smile = $json["smile"];   //긍정적표정
  $mouth_motion = $json["mouth_motion"];   //입움직임
  $blink = $json["blink"];   //눈깜빡임
  $gesture = $json["gesture"];   //제스처빈도
  $head_motion = $json["head_motion"];   //머리움직임
  $foreign = $json["foreign"];   //외국어빈도
  $glow = $json["glow"];   //홍조현상

  if($speed == 1){
  $score_speed = 5;
  } else if($speed == 2 || $speed == 10){
  $score_speed = 6;
  } else if($speed == 3 || $speed == 9){
  $score_speed = 7;
  } else if($speed == 4 || $speed == 8){
  $score_speed = 8;
  } else if($speed == 5 || $speed == 6){
  $score_speed = 10;
  } else if($speed == 7){
  $score_speed = 9;
  }else if($speed == 0){
  $score_speed = 0;
  }

  //-------------------------------------------------------------------

  //[적극성 (목소리떨림, 발음정확도, 음성속도, 긍정적표정, 제스쳐빈도)]
  if($quiver == 0){ //목소리떨림
  $score_quiver = 0;
  }else{
  $score_quiver = 11-$quiver;   
  }
  $score_diction = $diction;    //발음정확도
  //$score_speed = 10;    //음성속도 
  $score_smile = $smile;    //긍정적표정
  $score_gesture = $gesture;    //제스쳐빈도

  $aggressiveness = $score_quiver + $score_diction + $score_speed + $score_smile + $score_gesture;

  //-------------------------------------------------------------------

  //[안정성 (목소리떨림, 음성속도, 홍조현상, 머리움직임, 눈깜빡임)]
  if($quiver == 0){ //목소리떨림
  $score_quiver = 0;
  }else{
  $score_quiver = 11-$quiver;   
  }
  //$score_speed = 10;    //음성속도 

  if($glow == 0){   //홍조현상
  $score_glow = 0; 
  }else{
  $score_glow = 11-$glow; 
  }


  if($head_motion == 0){  //머리움직임
  $score_head_motion = 0; 
  }else{
  $score_head_motion = 11-$head_motion; 
  }

  if($blink == 0){ //눈깜빡임
  $score_blink = 0; 
  }else{
  $score_blink = 11-$blink; 
  }


  $stability = $score_quiver + $score_speed + $score_glow + $score_head_motion + $score_blink;

  //-------------------------------------------------------------------

  //[신뢰성 (목소리떨림, 음성속도, 발음정확도, 성실답변률, 시선처리)]
  if($quiver == 0){ //목소리떨림
  $score_quiver = 0;
  }else{
  $score_quiver = 11-$quiver;  
  }
  //$score_speed = 10;    //음성속도 
  $score_diction = $diction;    //발음정확도
  $score_sincerity = $sincerity;    //성실답변률
  $score_eyes = $eyes;    //시선처리

  $reliability = $score_quiver + $score_speed + $score_diction + $score_sincerity + $score_eyes;

  //-------------------------------------------------------------------

  //[긍정성 (음성크기, 목소리톤, 입움직임, 긍정적표정, 시선처리)]
  $score_volume = $volume;    //음성크기
  $score_tone = $tone;    //목소리톤
  $mouth_motion = $mouth_motion;    //입움직임
  $score_smile = $smile;    //긍정적표정
  $score_eyes = $eyes;    //시선처리

  $affirmative = $score_volume + $score_tone + $mouth_motion + $score_smile + $score_eyes;

  //-------------------------------------------------------------------

  //[대응력 (음성속도, 성실답변률, 질문이해도, 시선처리, 긍정적표정)]
  //$score_speed = 10;    //음성속도
  $score_sincerity = $sincerity;    //성실답변률
  $score_understanding = $understanding;    //질문이해도
  $score_eyes = $eyes;    //시선처리
  $score_smile = $smile;    //긍정적표정

  $alacrity = $score_speed + $score_sincerity + $score_understanding + $score_eyes + $score_smile;

  //-------------------------------------------------------------------

  //[의지력 (성실답변률, 시선처리, 제스쳐빈도, 질문이해도, 입움직임)]
  $score_sincerity = $sincerity;    //성실답변률
  $score_eyes = $eyes;    //시선처리
  $score_gesture = $gesture;    //제스쳐빈도
  $score_understanding = $understanding;    //질문이해도
  $mouth_motion = $mouth_motion;    //입움직임

  $willpower = $score_sincerity + $score_eyes + $score_gesture + $score_understanding + $mouth_motion;

  //-------------------------------------------------------------------

  //[능동성 (음성크기, 성실답변률, 긍정적표정, 입움직임, 답변속도)]
  $score_volume = $volume;    //음성크기
  $score_sincerity = $sincerity;    //성실답변률
  $score_smile = $smile;    //긍정적표정
  $mouth_motion = $mouth_motion;    //입움직임
  //$score_speed = 10;    //음성속도

  $activity = $score_volume + $score_sincerity + $score_smile + $mouth_motion + $score_speed;

  //-------------------------------------------------------------------

  //[매력도 (목소리톤, 음성크기, 제스쳐빈도, 긍정적표정, 홍조현상)]
  $score_tone = $tone;    //목소리톤
  $score_volume = $volume;    //음성크기
  $score_gesture = $gesture;    //제스쳐빈도
  $score_smile = $smile;    //긍정적표정
  $score_glow = $glow;    //홍조현상

  $attraction = $score_tone + $score_volume + $score_gesture + $score_smile + $score_glow;

  //-------------------------------------------------------------------
  $total =  $aggressiveness + $stability + $reliability + $affirmative + $alacrity + $willpower + $activity + $attraction;
  $sum = $total/3.5;

  //-------------------------------------------------------------------

  if($sum >= 80){
  $grade = 'S';
  } else if($sum >= 60){
  $grade = 'A';
  } else if($sum >= 40){
  $grade = 'B';
  } else if($sum >= 20){
  $grade = 'C';
  } else{
  $grade = 'D';
  }
  
  $value = array('aggressiveness' => $aggressiveness,'stability' => $stability, 'reliability' => $reliability, 'affirmative' => $affirmative, 'alacrity' => $alacrity,'willpower' => $willpower,
  'activity' => $activity,'attraction' => $attraction,'sum' => $sum,'grade' => $grade);

  return $value;

}

displayError();
/*
22.02.16 igg 작업
알파공 iv_applier 모든 데이터의 idx 기준으로 iv_result에 데이터가 기존에는 total에 값이 다 있고, 나머지 질문에는 3개씩밖에 없었음
그래서 나머지 질문들의 3개 값고 total 값을 조합하여, 질문별로 값을 만드는 작업임
----------------
result_baisc_score.php 에서 개별 질문값은 만들었고, 여기는 개별 질문값들을 다시 읽어서 평균치를 구해서 total값을 업데이트 해야함
먼저 total에 있는 성별,나이,피부,수염,머리길이,안경착용여부값을 가져오고, 나머지 값들은 기존 값들을 가지고 평균치를 구함, 분석불가는 카운트X
----------------
iv_result 테이블에 analysis 필드 값 계산해서 업데이트하기
*/

$sql = "SELECT * FROM iv_applier ORDER BY idx DESC";
//$sql = "SELECT * FROM iv_applier ORDER BY RAND() LIMIT 1";
$rst = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($rst)) {
    $idx = $row['idx'];
    echo $idx."<br>";
    $sql1 = "SELECT * FROM iv_result_new WHERE applier_idx = '".$idx."' AND process = 2";
    $rst1 = mysqli_query($conn, $sql1);
    while ($row1 = mysqli_fetch_array($rst1)) {
        $idx2 = $row1['idx'];
        $json = json_decode($row1['score'], true);

        $result = calculAnalysis($json);

        $sql02 = "UPDATE iv_result_new SET 
        `analysis` = json_object(
        'aggressiveness','".$result["aggressiveness"]."',
        'stability','".$result["stability"]."',
        'reliability','".$result["reliability"]."',
        'affirmative','".$result["affirmative"]."',
        'alacrity','".$result["alacrity"]."',
        'willpower','".$result["willpower"]."',
        'activity','".$result["activity"]."',
        'attraction','".$result["attraction"]."',
        'sum','".$result["sum"]."',
        'grade','".$result["grade"]."'
        ) WHERE idx = '".$idx2."'";
        mysqli_query($conn, $sql02);
        $rst3 = mysqli_affected_rows($conn);
        if ($rst3 > 0) {
            echo $idx2.'/success<br>';
        } else {
            echo $idx2.'/fail<br>';
        }
    }

 
    
}
