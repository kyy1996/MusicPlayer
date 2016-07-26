<?php
require("Lib/API.php");
/*require("Lib/MV.php");
require("Lib/Lyric.php");*/
error_reporting(E_ALL);
ini_set("display_errors", "on");
$API = new API();
//$Lyric = new Lyric();
$name = @$_REQUEST['name'] ? $_REQUEST['name'] : "刺藤";
/*
$lrcx = !!@$_REQUEST['lrcx'];
$music_rid = $API->getMusicRid($name);
$lyric = $API->getLyricRid($music_rid, $lrcx);
$str_lrc = $API->getLyric($lyric['lyric_rid']);
$str_lrc = $Lyric->decodeLyric($str_lrc);
echo("<pre>$str_lrc</pre>");

$img = $API->getArtistImg($music_rid);
echo("<br>" . "<img src='{$img[0]}'>");
echo("<br>" . "<img src='{$img[1]}'>");
$aac = $API->getPlayUrl($music_rid);
echo("<br><audio autoplay='autoplay' controls='controls' src='{$aac}'></audio>");
echo("{$music_rid}\n");

$MV = new MV();
$mv = $MV->getMV($music_rid)['url'];
echo("<video controls='controls' autoplay='autoplay' src='{$mv}'></video>");*/