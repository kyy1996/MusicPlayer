<?php

/**
 * Created by PhpStorm.
 * User: alen
 * Date: 16-7-26
 * Time: 下午12:44
 */

require_once "DES.php";

class API
{
    var $API_LIST = "http://search.kuwo.cn/r.s?ft=music&itemset=web_2013&client=kt&pn=0&rn=1&rformat=json&encoding=utf8&all=";
    var $API_LRC = "http://newlyric.kuwo.cn/newlyric.lrc?";
    var $API_MUSIC_INFO = "http://player.kuwo.cn/webmusic/st/getNewMuiseByRid?rid=";
    var $API_SEARCH = 'http://search.kuwo.cn/r.s?ft=music&itemset=web_2013&client=kt&rformat=json&encoding=utf8&all={0}&pn={1}&rn={2}';
    var $API_PLAY = "http://antiserver.kuwo.cn/anti.s?type=convert_url&format=aac|mp3&response=url&rid=";
    var $API_ART_PIC = "http://artistpicserver.kuwo.cn/pic.web?type=big_artist_pic&pictype=url&content=list&&id=0&from=pc&name=";

    function __construct()
    {
        $this->DES = new DES();

    }

    public function getPlayUrl($music_rid)
    {
        $url = $this->API_PLAY . $music_rid;

        return file_get_contents($url);
    }

    public function getSongUrl($music, $mv = false)
    {
        $audio_brs = ['128kmp3', '192kmp3', '320kmp3', '2000kflac'];
        $audio_formats = ['MP3128', 'MP3192', 'MP3H', 'AL'];
        $video_formats = ['MP4L', 'MP4'];
//        $song_formats = explode("|", trim($music['formats']));
        if ($mv) {
            /*if (in_array($audio_formats[3], $song_formats)) $br = $audio_formats[3];
            else if (in_array($audio_formats[2], $song_formats)) $br = $audio_formats[2];
            else if (in_array($audio_formats[1], $song_formats)) $br = $audio_formats[1];
            else $br = $audio_formats[0];*/
            $br = $video_formats[1];
            $url = "user=359307055300426&prod=kwplayer_ar_6.4.8.0&corp=kuwo&source=kwplayer_ar_6.4.8.0_kw.apk&p2p=1&type=convert_mv_url2&rid={$music['music_id']}&quality={$br}&network=WIFI&mode=audition&format=mp4&br=&sig=";
        } else {
            $br = $audio_formats[3];
//            if (in_array($video_formats[1], $song_formats)) $br = $video_formats[1];
            $url = "user=359307055300426&prod=kwplayer_ar_6.4.8.0&corp=kuwo&source=kwplayer_ar_6.4.8.0_kw.apk&p2p=1&type=convert_url2&br={$br}&format=mp3|flac|aac&sig=0&rid={$music['music_id']}&network=WIFI";
        }
        $url = 'http://mobi.kuwo.cn/mobi.s?f=kuwo&q=' . $this->DES->base64_encrypt($url);
        var_dump($url);
        $content = file_get_contents($url);
        if (!$content) return false;

        return $content;
    }

    public function getArtPic($artist_name)
    {
        $url = $this->API_ART_PIC . urlencode($artist_name);
        $content = file_get_contents($url);
        if (!$content) return false;
        $content = trim($content);
        return explode("\n", $content);
    }

    public function getMusic($music_rid)
    {
        $url = $this->API_MUSIC_INFO . $music_rid;
        $content = file_get_contents($url);

        $reg = "/<(\w+)>(.+)<\/\w+>/i";
        preg_match_all($reg, $content, $music);
        if (!$music) return false;
        return array_combine($music[1], $music[2]);
    }

    public function getMusicRid($name)
    {
        $result = $this->getMusicList($name);
        if (@$result['abslist'][0]['MUSICRID'])
            return $result['abslist'][0]['MUSICRID'];
        return false;
    }

    /**
     * 得到歌曲列表
     * @param $name
     * @param int $page
     * @param int $page_max
     * @return array|bool
     */
    public function getMusicList($name, $page = 0, $page_max = 10)
    {
        $url = str_replace('all={0}', "all=" . urlencode($name), $this->API_SEARCH);
        $url = str_replace('{1}', $page, $url);;
        $url = str_replace('{2}', $page_max, $url);
        $result = file_get_contents($url);
        $result = str_replace("'", "\"", $result);
        $list = json_decode($result, true);
        if (!@$list)
            return false;

        return $list;
    }

    public function getArtistImg($music_rid)
    {
        $music = $this->getMusic($music_rid);
        //查找歌手大图
        $img[] = $music['artist_pic240'];
        //查找歌手小图
        $img[] = $music['artist_pic'];
        return $img;
    }

    /**
     * 得到歌词内容
     * 返回原始歌词数据（需要先解码才能使用）
     * @param $rid
     * @return bool|string
     */
    public function getLyric($rid)
    {
        $api = $this->API_LRC;
        $url = $api . $rid;
        return @file_get_contents($url);
    }

    /**
     * 得到歌词Rid， 优先获取lrcx歌词rid
     * @param $music_rid
     * @param bool $lrcx
     * @return array|bool
     */
    public function getLyricRid($music_rid, $lrcx = true)
    {
        $music = $this->getMusic($music_rid);
        $key = $lrcx ? "lyric_zz" : "lyric";
        return ["is_lrcx" => $lrcx, "lyric_rid" => $music[$key]];
    }

    public function xorDecode($str, $key = "yeelion")
    {
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
        return implode("", $output);
    }
}