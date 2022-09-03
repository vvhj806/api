<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

displayError();

/*
$sql = 'SELECT * FROM g5_member LIMIT 1';
$rst = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($rst);
print_r($row);
*/
/*
$access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $applier_idx = isset($_POST['applier_idx']) ? trim($_POST['applier_idx']) : '';
    $count = isset($_POST['count']) ? trim($_POST['count']) : '';
    $question_idx = isset($_POST['question_idx']) ? trim($_POST['question_idx']) : '';
    */
?>

<!--
<form enctype='multipart/form-data' action='/alphagong/interview.php' method='post'>
	<input type='file' name='video'>
    <input type="hidden" name="method" value="uploadVideo">
    <input type="hidden" name="access_token" value="64d716462e0f012b35549052e8e339713f55ce15">
    <input type="hidden" name="applier_idx" value="4619">
    <input type="hidden" name="count" value="1">
    <input type="hidden" name="question_idx" value="1">
	<button>보내기</button>
</form>
-->

<form enctype='multipart/form-data' action='/alphagong/interview.php' method='post'>
	<input type='file' name='thumbnail'>
    <input type="hidden" name="method" value="setProfile">
    <input type="hidden" name="access_token" value="64d716462e0f012b35549052e8e339713f55ce15">
    <input type="hidden" name="applier_idx" value="4619">
	<button>보내기</button>
</form>