<?php

/**
 * Created by PhpStorm.
 * User: alen
 * Date: 16-7-26
 * Time: 上午10:31
 */
require "API.php";

class Lyric
{
    private $API;

    function __construct()
    {
        $this->API = new API();
    }

    /**
     * 解码歌词
     * @param $str
     * @param bool $lyricx
     * @param string $key
     * @return bool|string
     */
    public function decodeLyric($str, $lyricx = true, $key = "yeelion")
    {
        if (substr($str, 0, 10) != "tp=content") return false;

        //开始解码
        $charset = "gb18030";
        $index = strpos($str, "\r\n\r\n");
        $str = substr($str, $index + 4);
        $str = zlib_decode($str);
        //如果不是lrcx则跳过解密，直接输出
        if (!$lyricx) return iconv($charset . "//IGNORE", "UTF-8", $str);

        //是lrcx格式歌词需要先解密
        $str = base64_decode($str);

        $output = $this->API->xorDecode($str, $key);
        $output = iconv($charset, "UTF-8//IGNORE", $output);
        return $output;
    }
}