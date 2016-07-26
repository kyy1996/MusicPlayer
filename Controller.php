<?php
require("Lib/API.php");
/*require("Lib/MV.php");
require("Lib/Lyric.php");*/
error_reporting(E_ALL);
ini_set("display_errors", "on");
//$Lyric = new Lyric();

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

class Controller
{
    /**
     * @var API API
     */
    var $API;

    /**
     * @var ReflectionObject
     */
    private $Reflection;

    /**
     * @var ReflectionMethod[]
     */
    private $Methods;

    function __construct()
    {
        $this->API = new API();
        $this->Reflection = new ReflectionObject($this->API);
        $this->Methods = $this->Reflection->getMethods();
    }

    public function getArtistImg($music_rid)
    {
        $return = $this->$this->API->getArtistImg($music_rid);
        $this->success($return);
    }

    public function getLyric($music_rid)
    {
        $l_rid = $this->API->getLyricRid($music_rid, true);
        $lrc = $this->API->getLyric($l_rid);
        if ($lrc) $this->error("Error load lrc");
        $this->success($lrc);
    }

    public function success($data = "", $url = "")
    {
        $status = 1;
        $return = compact($status, $data, $url);

        $this->ajaxReturn($return);
    }

    public function error($data = "", $url = "")
    {
        $status = 0;
        $return = compact($status, $data, $url);

        $this->ajaxReturn($return);
    }

    private function ajaxReturn($data)
    {
        echo(json_encode($data));
        exit();
    }
}

$controller = new Controller();

$ref = new ReflectionObject($controller);
var_dump($ref->getMethods());