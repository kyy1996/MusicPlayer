<?php

class MusicAPIUtils
{
    var $API_LIST = "http://search.kuwo.cn/r.s?ft=music&itemset=web_2013&client=kt&pn=0&rn=1&rformat=json&encoding=utf8&all=";
    var $API_LRC = "http://newlyric.kuwo.cn/newlyric.lrc?";
    var $API_LRC_RID = "http://player.kuwo.cn/webmusic/st/getNewMuiseByRid?rid=";
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
     * @return bool|array
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
        $url = $this->API_LRC_RID . $music_rid;
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
     * @return bool|array
     */
    public function getLyricRid($music_rid)
    {
        $url = $this->API_LRC_RID . $music_rid;
        $xml = file_get_contents($url);
        //查找逐字歌词
        preg_match("/<lyric_zz>(.+)<\/lyric_zz>/i", $xml, $result);
        $lyricx = true;
        //逐字歌词不存在则查找普通歌词
        if (!@$result[1]) {
            $lyricx = false;
            preg_match("/<lyric>(.+)<\/lyric>/i", $xml, $result);
        }
        if (!@$result[1])
            return false;

        return ["is_lrcx" => $lyricx, "lyric_rid" => $result[1]];
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
        if (!$lyricx) return iconv("UTF-8", $charset . "//IGNORE", $str);

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
        return iconv("UTF-8", $charset . "//IGNORE", $output);
    }
}

$Lyric = new MusicAPIUtils();
$name = @$_REQUEST['name'] ? $_REQUEST['name'] : "刺藤";
$music_rid = $Lyric->getMusicRid($name);
$lyric = $Lyric->getLyricRid($music_rid);
$str_lrc = $Lyric->getLyric($lyric['lyric_rid'], $lyric['is_lrcx']);

header("Content-type:text/html;charset=gb18030");
echo("<pre>$str_lrc</pre>");

$img = $Lyric->getArtistImg($music_rid);
echo("<br>" . "<img src='{$img[0]}'>");
echo("<br>" . "<img src='{$img[1]}'>");
$aac = $Lyric->getPlayUrl($music_rid);
echo("<br><audio src='{$aac}'></audio>");