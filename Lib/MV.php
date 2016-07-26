<?php

/**
 * Created by PhpStorm.
 * User: alen
 * Date: 16-7-26
 * Time: 下午12:34
 */
require("DES.php");

class MV
{
    /**
     * @var DES DES
     */
    var $DES;

    public function __construct()
    {
        $this->DES = new DES();
    }

    /**
     * @param $music_rid
     * @return array|bool
     */
    public function getMV($music_rid)
    {
        $audio_brs = ['128kmp3', '192kmp3', '320kmp3', '2000kflac'];
        $audio_formats = ['MP3128', 'MP3192', 'MP3H', 'AL'];
        $video_formats = ['MP4L', 'MP4'];
        $br = $video_formats[1];
        $music_rid = str_replace("MUSIC_", "", $music_rid);
        $url = "user=359307055300426&prod=kwplayer_ar_6.4.8.0&corp=kuwo&source=kwplayer_ar_6.4.8.0_kw.apk&p2p=1&type=convert_mv_url2&rid={$music_rid}&quality={$br}&network=WIFI&mode=audition&format=mp4&br=&sig=";
        $url = 'http://mobi.kuwo.cn/mobi.s?f=kuwo&q=' . $this->DES->base64_encrypt($url);

        $content = @file_get_contents($url);
        if (!$content) return false;
        $preg = "/(\w+)=(.+)\n/i";
        preg_match_all($preg, $content, $mv);
        $mv = array_combine($mv[1], $mv[2]);
        return $mv;
    }
}