<?php
date_default_timezone_set("Asia/Seoul");
$conn = mysqli_connect("14.63.226.91", "interview", "Comcomcom12!@", "interview"); //알파공
mysqli_query($conn, "set names utf8;");

$conn_iv = mysqli_connect("14.63.216.99", "interview", "Buffbuff7878!@", "interview"); //인터뷰
mysqli_query($conn_iv, "set names utf8;");


$conn_iv_20_webtest = mysqli_connect("14.63.226.99", "masterInterview", "Buffbuff7878!@", "test_interview"); //인터뷰2.0 webtest 22.06.02 ms code_igniter_test => new_interview 로 변경
mysqli_query($conn_iv_20_webtest, "set names utf8;");

$conn_iv_20 = mysqli_connect("172.27.0.215", "masterInterview", "Buffbuff7878!@", "new_interview"); //인터뷰2.0 real
mysqli_query($conn_iv_20, "set names utf8;");

$conn_iv_15 = mysqli_connect("14.63.216.99", "interview", "Buffbuff7878!@", "interview_manager"); //인터뷰1.5 manager
mysqli_query($conn_iv_15, "set names utf8;");
?>