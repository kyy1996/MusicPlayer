<?php
require_once("Lib/API.php");
/*require("Lib/MV.php");
require("Lib/Lyric.php");*/
error_reporting(E_ALL);
ini_set("display_errors", "on");

/*$Lyric = new Lyric();

$API = new API();
$lrcx = !!@$_REQUEST['lrcx'];
$name = $_REQUEST['name'];
$music_rid = $API->getMusicRid($name);
$lyric = $API->getLyricRid($music_rid, $lrcx);
$str_lrc = $API->getLyric($lyric['lyric_rid']);
$str_lrc = $Lyric->decodeLyric($str_lrc, $lrcx);
echo("<pre>$str_lrc</pre>");

$img = $API->getArtistImg($music_rid);
echo("<br>" . "<img src='{$img[0]}'>");
echo("<br>" . "<img src='{$img[1]}'>");
$aac = $API->getPlayUrl($music_rid);
echo("<br><audio autoplay='autoplay' controls='controls' src='{$aac}'></audio>");
echo("{$music_rid}\n");
$MV = new MV();
$mv = $MV->getMV("MUSIC_4224167")['url'];
$mv = $MV->getMV("MUSIC_751532")['url'];
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

    public function getLyric($music_rid, $lrcx = true)
    {
        $l_rid = $this->API->getLyricRid($music_rid, !!$lrcx);
        $lrc = $this->API->getLyric($l_rid);
        if ($lrc) $this->error("Error load lrc");
        $this->success($lrc);
    }

    public function getMv($music_rid)
    {
        /*$music = $this->API->getMusic($music_rid);
        $url = $this->API->getSongUrl($music, true);*/
        $MV = new MV();
        $url = $MV->getMV($music_rid);
        if ($url)
            $this->success($url);
        else
            $this->error("Error getting MV url");
    }

    public function getMusicRid($name)
    {
        $music = $this->API->getMusicRid($name);

        if ($music)
            $this->success($music);
        else
            $this->error("Error getting music info");
    }

    public function getMusicList($name)
    {
        $list = $this->API->getMusicList($name);
        var_dump($list);
        $this->success($list);
    }

    public function success($data = "", $url = "")
    {
        $status = 1;
        $return = compact("status", "data", "url");
        $this->ajaxReturn($return);
    }

    public function error($data = "", $url = "")
    {
        $status = 0;
        $return = compact("status", "data", "url");

        $this->ajaxReturn($return);
    }

    private function ajaxReturn($data)
    {
        header("Content-Type:text/html;charset=utf-8");
        echo(json_encode($data));
        exit();
    }

    function __call($name, $arguments)
    {
        $this->error("Call an undefined method {$name}.");
    }
}

ob_start();
$controller = new Controller();
$action = "get" . ($_REQUEST['action']);
$param = $_REQUEST;
array_shift($param);

try {
    call_user_func_array([$controller, $action], $param);
} catch (Exception $e) {
    $controller->error($e->getMessage());
}