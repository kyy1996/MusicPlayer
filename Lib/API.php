<?php

/**
 * Created by PhpStorm.
 * User: alen
 * Date: 16-7-26
 * Time: 下午12:44
 */
class API
{
    var $API_LIST = "http://search.kuwo.cn/r.s?ft=music&itemset=web_2013&client=kt&pn=0&rn=1&rformat=json&encoding=utf8&all=";
    var $API_LRC = "http://newlyric.kuwo.cn/newlyric.lrc?";
    var $API_MUSIC_INFO = "http://player.kuwo.cn/webmusic/st/getNewMuiseByRid?rid=";
    var $API_SEARCH = 'http://search.kuwo.cn/r.s?ft=music&itemset=web_2013&client=kt&rformat=json&encoding=utf8&all={0}&pn={1}&rn={2}';
    var $API_PLAY = "http://antiserver.kuwo.cn/anti.s?type=convert_url&format=aac|mp3&response=url&rid=";

    function __construct()
    {
    }

    public function getPlayUrl($music_rid)
    {
        $url = $this->API_PLAY . $music_rid;

        return file_get_contents($url);
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
        if (!@$list) {
            return false;
        }

        return $list;
    }

    public function getArtistImg($music_rid)
    {
        $url = $this->API_MUSIC_INFO . $music_rid;
        $xml = file_get_contents($url);

        //查找歌手大图
        preg_match("/<artist_pic240>(.+)<\/artist_pic240>/i", $xml, $result);
        $img[] = $result[1];
        //查找歌手小图
        preg_match("/<artist_pic>(.+)<\/artist_pic>/i", $xml, $result);
        $img[] = $result[1];
        return $img;
    }

    /**
     * 得到歌词内容
     * @param $rid
     * @param bool $is_lrcx
     * @return bool|string
     */
    public function getLyric($rid, $is_lrcx = true)
    {
        $api = $this->API_LRC;
        $url = $api . $rid;
        $content = file_get_contents($url);
        if ($lyric = $this->decodeLyric($content, $is_lrcx))
            return $lyric;
        return false;
    }

    /**
     * 得到歌词Rid， 优先获取lrcx歌词rid
     * @param $music_rid
     * @param bool $lrcx
     * @return array|bool
     */
    public function getLyricRid($music_rid, $lrcx = true)
    {
        $url = $this->API_MUSIC_INFO . $music_rid;
        $xml = file_get_contents($url);
        if ($lrcx) {
            //查找逐字歌词
            preg_match("/<lyric_zz>(.+)<\/lyric_zz>/i", $xml, $result);
        } else {
            //逐字歌词不存在则查找普通歌词
            preg_match("/<lyric>(.+)<\/lyric>/i", $xml, $result);
        }
        if (!@$result[1])
            return false;

        return ["is_lrcx" => $lrcx, "lyric_rid" => $result[1]];
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