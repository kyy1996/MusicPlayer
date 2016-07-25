<?php

class MusicAPIUtils
{
    var $API_LIST = "http://search.kuwo.cn/r.s?ft=music&itemset=web_2013&client=kt&pn=0&rn=5&rformat=json&encoding=utf8&all=";
    var $API_LRC = "http://newlyric.kuwo.cn/newlyric.lrc?";
    var $API_LRC_RID = "http://player.kuwo.cn/webmusic/st/getNewMuiseByRid?rid=";

    function __construct()
    {
    }

    public function getMusicRid($name)
    {
        $result = $this->getMusicList($name);
        if (@$result[0]['MUSICRID'])
            return $result[0]['MUSICRID'];
        return false;
    }

    /**
     * 得到歌曲列表
     * @param $name
     * @return bool|array
     */
    public function getMusicList($name)
    {
        $url = $this->API_LIST . urlencode($name);
        $result = file_get_contents($url);
        $result = str_replace("'", "\"", $result);
        $list = json_decode($result, true);
        if (!@$list['abslist'] || !@$list['abslist'][0]) {
            return false;
        }

        return $list['abslist'];
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
//        preg_match("/<lyric_zz>(.+)<\/lyric_zz>/i", $xml, $result);
        $lyricx = true;
        //逐字歌词不存在则查找普通歌词
        $lyricx = false;
//        if (!@$result[1]) {
        $lyricx = false;
        preg_match("/<lyric>(.+)<\/lyric>/i", $xml, $result);
//        }
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
$lyric = $Lyric->getLyricRid($Lyric->getMusicRid($name));
$str_lrc = $Lyric->getLyric($lyric['lyric'], $lyric['is_lrcx']);

echo("<pre>");

header("Content-type:text/html;charset=gb18030");
echo($str_lrc);