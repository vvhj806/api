<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/function.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/db_config.php");
$date = date("Y-m-d", time());
$nowDateMonth = date("m");
$nowDateYear = date("Y");

$getLog = $_GET['company'];
$company = ['스윗솔루션', '건우테크'];
if (!$getLog) {
    print_r('company 값이 없습니다.');
    return;
} else {
    if (!in_array($getLog, $company)) {
        print_r('유효한 company 값이 아닙니다. 값을 확인해주세요');
        return;
    }
}
header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
header( "Expires: 0" );
header( "Cache-Control: must-revalidate, post-check=0,pre-check=0" );
header( "Pragma: public" );
header( "Content-Disposition: attachment; filename=login_log_".$getLog.".xls" );

if ($getLog == '스윗솔루션') {
    $arr = ['c_idx' => 85, 'Order' => '20220526000960', 'name' => '하이버프 인터뷰', 'UniqueNum' => '9864', 'BusinessNum' => '449-87-02242'];
} else if($getLog == '건우테크'){
    $arr = ['c_idx' => 97, 'Order' => '20220706000167', 'name' => '하이버프 인터뷰', 'UniqueNum' => '7876', 'BusinessNum' => '605-81-96649'];
}


if ($date > $nowDateYear . "-" . $nowDateMonth . "-05") {
    $sql = "select m_id as 'ID', DATE_FORMAT(date, '%Y-%m-%d') as 'Login', logout_date as 'Logout', COUNT(*) as count from login_log where c_idx='".$arr['c_idx']."' and `date` > LAST_DAY(now() - interval 1 MONTH) + INTERVAL 1 DAY order by `date` desc";
} else {
    $sql = "select m_id as 'ID', DATE_FORMAT(date, '%Y-%m-%d') as 'Login', logout_date as 'Logout', COUNT(*) as count from login_log 
    where c_idx='".$arr['c_idx']."' and `date` >= LAST_DAY(now() - interval 2 MONTH) + INTERVAL 1 DAY order by `date` desc";
}

$rst = mysqli_query($conn_iv_15, $sql);
print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");

echo "
<table  border='1'>
<tr>
    <td>주문번호</td>
    <td>상품명</td>
    <td>수요기업 고유번호</td>
    <td>수요기업 사업자번호</td>
    <td>수요기업 이용ID</td>
    <td>접속일자</td>
    <td>월 접속 횟수</td>
</tr>
";

while ($row = mysqli_fetch_array($rst)) {
    echo "<tr>
    <td style='mso-number-format:\@;'>".$arr['Order']."</td>
    <td>".$arr['name']."</td>
    <td>".$arr['UniqueNum']."</td>
    <td>".$arr['BusinessNum']."</td>
    <td>" . $row['ID'] . "</td>
    <td>" . $row['Login'] . "</td>
    <td>" . $row['count'] . "</td>
    </tr>";
}

echo "
    </table>
    ";
