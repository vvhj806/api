<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

 /*
 * mecab php바인딩후 태그와 택스트로 분리하기
 *
 * @param string $str 문자열
 * @param array $code 걸러내고자 하는 코드값 NNG등
 * @return array $mecab_array 배열로 리턴
 */
function incodingMecab($str, $code = array()){
    $mecab_array = array();
    $mecab = new \MeCab\Tagger(['-d', '/usr/local/lib/mecab/dic/mecab-ko-dic']);

    //형태소분석하여 결과값 도출
    $result = $mecab->parse($str);
     
    //결과값에서 줄단위로 분리
    preg_match_all('/[^EOS](.*)\n/', $result, $find_code);
 
    //각줄별로 루프를 돌며 텍스트와 태그(코드)값분리
    for($i=0; $i < count($find_code[0]); $i++)
    {
        preg_match('/(.*)(?=\t)/', $find_code[0][$i], $find_text); // text
        preg_match('/(?<=\t)([^\,]+)/', $find_code[0][$i], $find_tag); // tag
        //걸러내고자하는 코드가 있을시
        if(count($code) > 0)
        {
            //걸러내려는 코드안에 태그가 포함되는지
            if(in_array($find_tag[0],$code)
                //중복되는 텍스트가 있는지
                && in_array($find_text[0],$mecab_array) === false)
            {
                $mecab_array[] = $find_text[0];
                $mecab_array[$i]["code"] = $find_tag[0]; //태그값은 필요 없어 주석
            }
        } else {
            //중복되는 텍스트가 있는지
            if(in_array($find_text[0],$mecab_array) === false)
            {
                $mecab_array[] = $find_text[0];
                $mecab_array[$i]["code"] = $find_tag[0];//태그값은 필요 없어 주석
            }
        }
    }
    //객체를 비움
    //mecab_destroy($mecab);
    return $mecab_array;
}

$str = '안녕하세요 . 저는 경성대학교 소프트웨어학과의 재학 프로트 앤드 , 앤드 , 그리고 시스템 프로그래밍 에 이르기까지 다양한 프로그래밍을 배우고 있는 정현우 입니다 . 현재 내는 인공지능분야에 관심이 많아 텐서플로파이토치 등의 언어를 익히며 이것을 어떻게 접목시킬 수 있을지에 관하여 연구 중입니다 . 저희 다양한 경험과 학습 능력이 본 회사의 큰 힘이 될 수 있을 것이라고 생각합니다 .';
$wordList = incodingMecab($str, array('NNG', 'NNP', 'VV', 'VA'));

//$mecab = new \MeCab\Tagger(['-d', '/usr/local/lib/mecab/dic/mecab-ko-dic']);
//echo $mecab->parse($str);
 
//var_dump($wordList);
$result_question = '';
$sql = 'SELECT * FROM question';
$rst = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($rst)) {
    $unique_point_words = array_unique(explode(',', $row['point_word']));
    $unique_negative_words = array_unique(explode(',', $row['negative_word']));

    $intersect_point_words = array_intersect($wordList, $unique_point_words);
    $intersect_negative_words = array_intersect($wordList, $unique_negative_words);
    
    if (count(array_diff($unique_point_words, $intersect_point_words)) == 0 && count($intersect_negative_words) == 0) {
        $result_question = $row['question'];
        break;
    }
}

echo $result_question;
?>