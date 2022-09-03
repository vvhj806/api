<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <title>Document</title>
</head>

<body>
    <?php
    include_once($_SERVER["DOCUMENT_ROOT"] . "/db_config.php");
    include_once($_SERVER["DOCUMENT_ROOT"] . "/function.php");
    include_once("report_function.php");

    $getWordStt = getWordStt($conn, 6062);
    // print_r($getWordStt['wordList']);
    // echo '<br><br>';
    ?>

    <div class="chart-area" style="margin-top:30px;">
        <div id="container" style="width:50%; height:auto;"></div>
    </div>
</body>

</html>

<script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-tag-cloud.min.js"></script>

<script>
    let aSpeech = [];
    aSpeech = '<?= json_encode($getWordStt['wordList'], true) ?>';
    // console.log(JSON.parse(aSpeech));
    
    $(document).ready(function() {
        createWordcloud(JSON.parse(aSpeech));
    });

    function createWordcloud(data) {
        let content = new Array();
        for (key in data) {
            if (key != '하다') {
                content.push({
                    "x": key,
                    "value": data[key]
                })
            }
        }

        anychart.onDocumentReady(function() {
            var chart = anychart.tagCloud(content);
            chart.angles([0]);
            chart.container("container");
            chart.draw();
        });
    }
</script>