<?php
date_default_timezone_set("Asia/Seoul");
// $connect = mysqli_connect("14.63.226.99", "masterInterview", "Buffbuff7878!@", "test_interview");    //webtest
$connect = mysqli_connect("172.27.0.215", "masterInterview", "Buffbuff7878!@", "new_interview");        //2.0
mysqli_query($connect, "set names utf8;");

echo '[Worknet Tag]<br>';

$tagArr = [1, 2, 3, 4, 5, 6, 7, 8];

$sql = "SELECT idx, com_name FROM iv_company WHERE worknet_tag_stat = 0";
$rst = mysqli_query($connect, $sql);
while ($row = mysqli_fetch_array($rst)) {
    print_r($row);
    echo '<br>';

    $random = array_rand($tagArr);
    $random = (int)$random + 1;
    echo $random;
    echo '<br><br>';

    $sql2 = "INSERT INTO iv_company_tag (com_idx, config_tag_idx) VALUES ('" . $row['idx'] . "', '" . $random . "')";
    $rst2 = mysqli_query($connect, $sql2);

    $sql3 = "UPDATE iv_company SET worknet_tag_stat = 1 WHERE idx = '".$row['idx']."'";
    $rst3 = mysqli_query($connect, $sql3);
}
