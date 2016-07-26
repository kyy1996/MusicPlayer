<?php

/**
 * Created by PhpStorm.
 * User: alen
 * Date: 16-7-26
 * Time: 上午10:31
 */
class Lyric
{
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


        $key = unpack("C*", $key);
        $str = unpack("C*", $str);
        $str_len = count($str);
        $key_len = count($key);

        $output = [];
        $i = 1;
        while ($i <= $str_len) {
            $j = 1;
            while ($j <= $key_len && $i <= $str_len) {
                $output[] = pack("C*", $str[$i] ^ $key[$j]);
                $i++;
                $j++;
            }
        }
        $output = implode("", $output);
        $output = iconv($charset, "UTF-8//IGNORE", $output);
        return $output;
    }
}