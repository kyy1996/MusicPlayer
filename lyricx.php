<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<pre>
<samp>
    <?php
    require("Lib/API.php");
    require("Lib/MV.php");

    error_reporting(E_ALL);
    ini_set("display_errors", "on");
    $Lyric = new API();
    $name = @$_REQUEST['name'] ? $_REQUEST['name'] : "刺藤";
    $lrcx = !!@$_REQUEST['lrcx'];
    $music_rid = $Lyric->getMusicRid($name);
    $lyric = $Lyric->getLyricRid($music_rid, $lrcx);
    $str_lrc = $Lyric->getLyric($lyric['lyric_rid'], $lyric['is_lrcx']);
    /*echo("<pre>$str_lrc</pre>");

    $img = $Lyric->getArtistImg($music_rid);
    echo("<br>" . "<img src='{$img[0]}'>");
    echo("<br>" . "<img src='{$img[1]}'>");
    $aac = $Lyric->getPlayUrl($music_rid);
    echo("<br><audio autoplay='autoplay' controls='controls' src='{$aac}'></audio>");*/
    echo("{$music_rid}\n");

    $MV = new MV();
    $mv = $MV->getMV($music_rid)['url'];
    echo("<video controls='controls' autoplay='autoplay' src='{$mv}'></video>");
    ?>
</samp></pre>
</body>
</html>