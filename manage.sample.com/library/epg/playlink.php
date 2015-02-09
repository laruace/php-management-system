<?php

class PlayLink
{
    const KEY = 'pplive';
    const OSKEY = 'kioe257ds';

    /**
     * 新版播放串
     * @param int $chid         频道ID
     * @param int $cid          分类ID
     * @param int $episode      是否分集
     * @param int $start_chid   开始播放的频道ID
     * @return string   播放串
     */
    public static function build_playlink($chid, $cid = 0, $episode = 0, $start_chid = 0, $download = false, $download_name = '')
    {
        if (!$chid)
            return false;
        $param ['a'] = $chid;
        $param ['b'] = $cid;
        $param ['c'] = $episode;
        $param ['d'] = $start_chid;
        if ($download) {
            $param['e'] = $download ? 1 : 0;
            $param['f'] = $download_name;
        }

        return 'pptv://' . self::_uri_encode(http_build_query($param));
    }

    /**
     * 生成IKAN播放串
     * @param int $id           频道ID
     * @param string $rid       资源ID
     * @return string           播放串
     */
    public static function build_ikan_playlink($id, $rid)
    {
        $str = 'jump.synacast.com||' . str_replace('+', '%20', urlencode(iconv('utf-8', 'gbk', $rid))) . '@@@' . $id;
        return self::_uri_encode($str, 1);
    }

    /**
     * 生成BOX播放串
     * @param string $rid       资源ID
     * @return string           播放串
     */
    public static function build_box_playlink($rid)
    {
        $str = 'jump.synacast.com||' . str_replace('+', '%20', urlencode(iconv('utf-8', 'gbk', $rid)));
        return self::_uri_encode($str, 1);
    }

    /**
     * @param int $channel_id
     * @return string
     */
    function build_ipad_url($channel_id){
        return 'http://web-play.pptv.com/web-m3u8-'. $channel_id .'.m3u8';
    }

    /**
     * 解析PPTV播放串
     * @param string $str   播放串
     * @return array        解析后数组
     */
    public static function parse_pptv($str, $array = true)
    {
        if (substr($str, 0, 7) == 'pptv://')
            $str = substr($str, 7);

        $parsed = self::_uri_decode($str);
        if (!$array)
            return $parsed;

        parse_str($parsed, $arr);
        return $arr;
    }

    /**
     * 解析IKAN播放串
     * @param string $str   播放串
     * @return array        解析后的数组
     */
    public static function parse_ikan($str, $array = true)
    {
        $str = self::_uri_decode($str, 1);
        if (!$str)
            return false;

        if (!$array)
            return $str;

        $pos_1 = strpos($str, '||');
        $pos_2 = strpos($str, '@@@');
        $param = array();
        $param['server'] = substr($str, 0, $pos_1);
        $param['mp4'] = iconv('gbk', 'utf-8', urldecode(substr($str, $pos_1 + 2, $pos_2 - $pos_1 - 2)));
        $param['id'] = substr($str, $pos_2 + 3);
        return $param;
    }

    /**
     * 解码synacast播放串
     * @param string $str
     * @return array
     */
    public static function parse_synacast($str, $array = true)
    {
        if (substr($str, 0, 11) == 'synacast://')
            $str = substr($str, 11);

        $parsed = self::_uri_decode($str);
        if (!$array)
            return $parsed;

        parse_str($parsed, $arr);
        foreach ($arr as $k => $v) {
            unset($arr[$k]);
            $arr[strtolower($k)] = $k == 'name' ? iconv('gbk', 'utf-8', $v) : $v;
        }
        return $arr;
    }

    /**
     * 播放串解析
     *
     * @param string $str
     * @param int $type 1=encode||2=decode
     * @return string
     */
    public static function _uri_encode($str, $type = 0)
    {
        if ($type == 1) {
            $key = self::OSKEY;
            $keylen = strlen($key);
        } else {
            $key = self::KEY;
            $keylen = strlen($key);
        }

        $length = strlen($str);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= chr(ord($str {$i}) + ord($key {$i % $keylen}));
        }
        return base64_encode($result);
    }

    public static function _uri_decode($str, $type = 0)
    {
        if ($type == 1) {
            $key = self::OSKEY;
            $keylen = strlen($key);
        } else {
            $key = self::KEY;
            $keylen = strlen($key);
        }

        $tmp = base64_decode($str);
        $length = strlen($tmp);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= chr(ord($tmp {$i}) - ord($key {$i % $keylen}));
        }
        return $result;
    }

}

?>