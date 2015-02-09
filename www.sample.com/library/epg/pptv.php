<?php

/**
 * 格式化日期
 * @param string $date          日期格式
 * @param int $type             类型
 * @return string               格式后的日期
 */
function format_date($date, $type = 1) {
    $ndate = '';
    if (is_numeric($date)) {
        $ndate = date('Y-m-d', $date);
    } else {
        switch ($type) {
            case 1:
                $ndate = str_replace('-', '/', substr($date, 2, 9));
                break;
        }
    }
    return $ndate;
}

/**
 * 格式化分数
 * @param string $score         分数
 * @param int $id               类型
 * @param string $ext           扩展
 * @return string               格式后的分数
 */
function format_score($score, $id = 1, $ext = '') {
    if (!$score) {
        return '';
    }
    $t1 = $t2 = '';
    switch ($id) {
        case 1 :
            $t1 = 'strong';
            $t2 = 'sup';
            break;
        case 2 :
            $t2 = 'span';
            break;
        default :
            $t2 = 'sup';
            break;
    }
    $sc = explode('.', $score);
    if (!isset($sc [1])) {
        $sc [1] = 0;
    }
    if ($t1)
        $t1 = "<{$t1}>{$sc[0]}</{$t1}>";
    if ($t2)
        $t2 = "<{$t2}>.{$sc[1]}</{$t2}>";
    return $t1 . $t2 . $ext;
}

function format_reci($title, $link = '') {
    if ($link) {
        return $link;
    }
    return 'http://search.pptv.com/s_video/q_' . urlencode($title);
}

/**
 * 格式化视频时间
 * @param int $second           秒数
 * @param int $type             类型
 * @param string $ext           扩展文字
 * @return string               格式后的秒数
 */
function format_duration($second, $type = 1, $ext = '') {
    $min = floor($second / 60);
    $sec = $second % 60;
    return str_pad($min, 2, '0', STR_PAD_LEFT) . ':' . str_pad($sec, 2, '0', STR_PAD_LEFT) . $ext;
}

/**
 * 去除标题的脏字符
 * @param string $title
 * @return string
 */
function format_title($title) {
    $trips = array('_box', '_mobile');
    foreach ($trips as $trip) {
        if (strpos($title, $trip) === false)
            continue;
        $title = str_replace(array('_box', '_mobile'), '', $title);
    }
    return $title;
}

/**
 * 格式化名称数组
 * @param string $names
 * @param string $url
 * @param string $join
 * @return string
 */
function format_names($names, $url_format = '', $default = '', $join = '&nbsp;') {
    if (is_array($names)) {
        if ($url_format) {
            $arr = array();
            if (is_array($names)) {
                foreach ($names as $nm) {
                    if (is_string($nm)) {
                        $tmp = $nm;
                        $nm = array();
                        $nm['title'] = $tmp;
                        $nm['id'] = urlencode($tmp);
                    }
                    $arr[] = '<a href="' . str_replace(array('{id}', '{title}'), array($nm['id'], $nm['title']), $url_format) . '">' . $nm['title'] . '</a>';
                }
            }
        } else {
            foreach ($names as $item) {
                if (is_array($item)) {
                    $arr[] = $item['title'];
                } else {
                    $arr[] = $item;
                }
            }
        }
        return join($join, $arr);
    } else {
        return (string) $names;
    }
}

/**
 * 格式化地区
 * @param type $areas
 * @param type $type
 * @param type $cat_id
 * @return string 
 */
function format_areas($areas, $type, $cat_id, $attr = '') {
    $data = false;
    if ($areas) {
        foreach ($areas as $area) {
            if ($area_id = get_area(false, $area)) {
                $data .= '<a href="' . list_build_url('type', $type, 'cat_id', $cat_id, 'area', $area_id) . '" target="_list" ' . $attr . '>' . $area . '</a> ';
            } else {
                $data .= '<a href="http://search.pptv.com/s_video/q_' . $area . '" target="_list" ' . $attr . '>' . $area . '</a> ';
            }
        }
    }
    return $data;
}

/**
 * 格式化时间戳
 * @param int $updatetime       时间戳
 * @param int $type             类型
 * @return string               时间格式
 */
function format_updatetime($updatetime, $type = 0) {
    if (strlen($updatetime) === 13)
        $updatetime = substr($updatetime, 0, 10);
    switch ($type) {
        default:
            $ret = date('Y-m-d', $updatetime);
    }
    return $ret;
}

/**
 * 格式化访问量
 * @param int $views
 * @return string               格式后的访问量
 */
function format_views($views) {
    $txt = '';
    if ($views < 10000)
        $txt = $views;
    elseif ($views > 999999)
        $txt = round($views / 10000) . '万';
    else
        $txt = number_format(round($views / 10000, 2), 2) . '万';
    return $txt;
}

/**
 * 格式化简介
 * @param string $str               简介
 * @return string                   格式后的简介
 */
function format_plot($str) {
    return str_replace(array("\n", "\t"), array("<br />", "&nbsp;&nbsp;&nbsp;&nbsp;"), $str);
}

/**
 * 获取BK封面图片缩略
 */
function get_cover($path, $size = 96) {
    $allow_sizes = array(60, 75, 90, 96, 120, 150, 180, 225);
    if (!$path || !in_array($size, $allow_sizes))
        return 'http://static.vas.pptv.com/vas/old/v_20121211153547/images/1717wan/96X128.jpg';
    $min = 5;
    $max = 8;
    $i = $min + hexdec(substr(md5($path), -1)) % ($max - $min + 1);
    return 'http://img' . $i . '.pplive.cn/sp' . $size . '/' . $path;
}

/**
 * 获取截图
 * @param string $md5               MD5值
 * @param int $size                 尺寸
 * @param boolean $empty            是否允许为空
 * @return string                   图片地址
 */
function get_capture($md5, $size = 128, $file = '1.jpg') {
    $allow_sizes = array(80, 100, 120, 128, 160, 200, 240, 300);
    if (!$md5 || !in_array($size, $allow_sizes))
        return 'http://static.vas.pptv.com/vas/old/v_20121211153547/images/1717wan/128X96.jpg';
    $md5 = trim(strtolower($md5));
    if (!$file)
        $file = '1.jpg';
    return 'http://v.img.pplive.cn/sp' . $size . '/' . substr($md5, 0, 2) . '/' . substr($md5, 2, 2) . '/' . $md5 . '/' . $file;
}

/**
 * 获取直播截图
 * @param int $id           频道ID
 * @param int $width        宽度（按照4:3比例处理高度）
 * @return string           URL
 */
function get_live_capture($id, $width = 0) {
    $rate = 0.75;
    if (!$width) {
        $width = 120;
    }
    $height = round($width * $rate);
    return "http://pic.pplive.com/capture_{$id}_{$width}_{$height}.html";
}

/**
 * 获取IKan类型
 */
function get_type($id, $t = false) {
    $type ['movie'] = '电影';
    $type ['tv'] = '电视剧';
    $type ['show'] = '综艺';
    $type ['cartoon'] = '动漫';
    $type ['people'] = '人物';
    $type ['picture'] = '图片';
    $type ['setpic'] = '图集';
    $type ['set'] = '集';
    $type ['tvs'] = '电视台';
    $type ['sport'] = '体育';
    $type ['info'] = '资讯';
    $type ['news'] = '新闻';
    $type ['game'] = '游戏';
    if ($t == 1)
        $type = array_values($type);
    elseif ($t == 2)
        $type = array_keys($type);

    if ($id !== false)
        return $type [$id];
    else
        return $type;
}

/**
 * 获取枚举值
 * @param int $id           枚举ID
 * @param string $type      枚举类型
 * @param string $default   枚举默认值
 * @return string           枚举值
 */
function get_enum($id = null, $type = null, $default = null) {
    if ($id === null || $type === null)
        return;
    switch ($type) {
        case 'video_type':
            $enum = array(
                1 => '高清',
                2 => '蓝光',
                3 => '抢先版',
                4 => '片花'
            );
            $default === null && $default = '';
            break;
        case 'director':
            $enum = array(
                2 => '制作人',
                3 => '监督',
                9 => '赛事类别',
            );
            $default === null && $default = '导演';
            break;
        case 'actor':
            $enum = array(
                2 => '主持人',
                3 => '声优',
                9 => '参赛选手',
            );
            $default = '演员';
            break;
        case 'area':
            $enum = array(
                9 => '参赛地点',
            );
            $default === null && $default = '地点';
            break;
        case 'time':
            $enum = array(
                9 => '比赛时间',
            );
            $default === null && $default = '地点';
    }

    if ($enum[$id]) {
        return $enum[$id];
    }
    return $default;
}

/**
 * 根据频道ID获取类型
 * @param sting $id 频道ID
 * @return string   类型
 */
function get_id_type($id) {
    $type = '';
    $id = (int) $id;
    if ($id >= 1855 && $id <= 100000) {
        $type = 'live';
    } elseif ($id > 100000 && $id <= 950000) {
        $type = 'vod';
    } elseif ($id > 950000 && $id <= 10000000) {
        $type = 'set';
    } elseif ($id > 10000000 && $id <= 10050000) {
        $type = 'flv';
    } elseif ($id > 10050000) {
        $type = 'os';
    }
    return $type;
}

function show_id_decode($id_encoded) {
    $str = pplive_decrypt2(base64_decode(str_replace(array('ib', 'ic', 'ia'), array('+', '/', 'i'), $id_encoded)), ID_KEY);
    $arr = array('id' => array(4, 'V'), 'set_id' => array(4, 'V'), 'cat_id' => array(2, 'v'), 'src_id' => array(1, 'C'));
    $ret = array();
    $len = 0;
    foreach ($arr as $k => $v) {
        $key = substr($str, $len, $v[0]);
        if (!$key) {
            break;
        }
        $ret[$k] = $str ? array_pop(unpack($v[1], $key)) : '';
        $len += $v[0];
    }
    return $ret;
}

function show_id_encode($id, $setid = 0, $catid = 0, $key = 0) {
    $str = pack('V', $id) . pack('V', $setid) . pack('v', $catid) . pack('C', $key);
    $key = str_replace(array('i', '+', '/', '='), array('ia', 'ib', 'ic', ''), base64_encode(pplive_encrypt2($str, ID_KEY)));
    return $key;
}

function build_url() {
    $args = func_get_args();
    $dom = $args[0];
    $req = $args[1];
    unset($args[0], $args[1]);
    $args = array_values($args);

    $opt = array();
    if (!empty($args)) {
        for ($i = 0; $i < count($args); $i += 2) {
            $opt [$args [$i]] = $args [$i + 1] === null ? '' : $args [$i + 1];
        }
    }

    return domain_select($dom) . '/' . $req . (empty($opt) ? '' : '?' . http_build_query($opt));
}

/**
 * 生成搜索URL
 * @return string
 */
function search_build_url() {
    $args = func_get_args();
    $opt = search_get_opt();
    unset($opt['page']);
    for ($i = 0; $i < count($args); $i += 2) {
        if ($args [$i + 1] !== '')
            $opt [$args [$i]] = $args [$i + 1];
        else
            unset($opt [$args [$i]]);
    }

    $req_uri = '/s_video/' . str_replace(array('=', '&'), array('_', '_'), http_build_query($opt));

    return domain_select('search') . $req_uri;
}

/**
 * 生成播放页URL
 * @return string
 */
function show_build_url($id, $setid = 0, $catid = 0, $key = 0) {
    return domain_select('v') . '/show/' . show_id_encode($id, $setid, $catid, $key) . '.html';
}

function show_page_build_url($id, $src_id = 0) {
    return domain_select('v') . '/show_page/' . show_page_id_encode($id, $src_id) . '.html';
    //return str_replace('/show/', '/show_page/', show_build_url($id, $setid, $catid, $key));
}

function show_page_id_encode($id, $src_id = 0) {
    return bin2hex(pplive_encrypt2((pack('V', $id) . pack('C', $src_id)), ID_KEY));
}

function show_page_id_decode($id_encoded) {
    $str = pplive_decrypt2(hex2bin($id_encoded), ID_KEY);
    $arr = array('id' => array(4, 'V'), 'src_id' => array(1, 'C'));
    $ret = array();
    $len = 0;
    foreach ($arr as $k => $v) {
        $key = substr($str, $len, $v[0]);
        if (!$key) {
            break;
        }
        $ret[$k] = $str ? array_pop(unpack($v[1], $key)) : '';
        $len += $v[0];
    }
    return $ret;
}

/**
 * 生成列表页URL
 * @return strign
 */
function list_build_url() {
    $opt = list_get_opt();
    $opt['page'] = '';
    $args = func_get_args();
    if (is_array($args[0])) {
        $tmp = $args[0];
        $args = array();
        foreach ($tmp as $k => $v) {
            $args[count($args)] = $k;
            $args[count($args)] = $v;
        }
    }
    for ($i = 0; $i < count($args); $i += 2) {
        if ($args [$i + 1] !== '' && in_array($args[$i], array_keys($opt)))
            $opt [$args [$i]] = $args [$i + 1];
        else
            $opt [$args [$i]] = '';
    }
    /**
     * 定制规则结束
     */
    $tmp = array();
    foreach ($opt as $k => $v) {
        $tmp[] = $v;
    }
    return domain_select('list') . '/sort_list/' . rtrim(join('-', $tmp), '-') . '.html';
}

/**
 * 域名选择
 * @param string $domain    域名
 * @param string $suffix    域名后缀
 * @return string
 */
function domain_select($domain = 'v', $suffix = '.pptv.com') {
    static $prefix = array();
    !$domain && $domain == 'v';
    $key = $domain . $suffix;
    if (!$prefix[$key]) {
        $http_host = $_SERVER['HTTP_HOST'];
        preg_match('/\-([a-zA-Z]+)' . str_replace('.', '\\.', $suffix) . '/', $http_host, $match);
        $prefix[$key] = $http_host == $domain . $suffix ? '' : 'http://' . $domain . ($match[1] ? '-' . $match[1] : '') . $suffix;
    }
    return $prefix[$key];
}

/**
 * 重新生成URL
 * @return string
 */
function list_rebuild_url() {
    $opt = list_get_opt();
    foreach ($opt as &$row) {
        $row = '';
    }
    $args = func_get_args();
    if (is_array($args)) {
        for ($i = 0; $i < count($args); $i += 2) {
            if ($args [$i + 1] !== '' && in_array($args[$i], array_keys($opt)))
                $opt [$args [$i]] = $args [$i + 1];
            else
                $opt [$args [$i]] = '';
        }
    }
    return list_build_url($opt);
}

/**
 * 获取列表页选项
 * @staticvar string $opt
 * @param <type> $id
 * @param <type> $default
 * @return <type>
 */
function list_get_opt($id = false, $default = null) {
    static $opt = null;
    if ($opt === null) {
        $key = array('type', 'cat_id', 'area', 'year', 'status', 'sort', 'layout', 'page');
        $val = array_slice(explode('-', pg('param')), 0, count($key));
        $opt = array();
        foreach ($key as $i => $k) {
            if ($val[$i] !== '' && is_string($val[$i]))
                $opt[$k] = (int) $val[$i];
            else
                $opt[$k] = null;
        }
    }

    return $id !== false ? (isset($opt[$id]) ? $opt[$id] : $default) : $opt;
}

/**
 * 获取搜索选项
 * @staticvar string $opt
 * @param <type> $id
 * @param <type> $default
 * @return <type>
 */
function search_get_opt($id = false, $default = null) {
    static $opt = null;
    if ($opt === null) {
        $opt = array();
        if (pg('param')) {
            $default_opt = array('q', 'type', 'sort', 'layout', 'page');
            $ar = explode('_', pg('param'));
            $cu = '';
            for ($i = 0; $i < count($ar); $i++) {
                $v = $ar[$i];
                if (in_array($v, $default_opt)) {
                    $opt[$v] = '';
                    $cu = $v;
                    continue;
                }
                $opt[$cu] .= $opt[$cu] === '' ? $v : '_' . $v;
            }
        }
    }

    foreach ($opt as $k => $v) {
        if (is_numeric($v) && $k != 'q')
            $opt[$k] = (int) $v;
        if ($v == '' && $k != 'q')
            unset($opt[$k]);
        if ($k == 'q')
            $opt[$k] = urldecode($v);
    }

    return $id !== false ? (isset($opt[$id]) ? $opt[$id] : $default) : $opt;
}

/**
 * 获取排行榜名称
 * @param string $key
 * @return array
 */
function get_topname($key = false) {
    $opt = array(
        'all' => array(
            'all' => '全平台',
            'vod' => '点播',
            'live' => '直播',
        ),
        'movie' => array(
            'all' => '电影',
            'huayu' => '华语',
            'haiwai' => '海外',
            'dongzuo' => '动作',
            'xiju' => '喜剧',
        ),
        'tv' => array(
            'all' => '电视剧',
            'neidi' => '内地',
            'gangtai' => '港台',
            'rihan' => '日韩',
            'oumei' => '欧美',
        ),
        'cartoon' => array(
            'all' => '动漫',
            'xinfan' => '新番',
            'lianzai' => '连载',
            'wanjie' => '完结',
            'shaoer' => '少儿',
        ),
        'show' => array(
            'all' => '娱乐',
            'dalu' => '大陆',
            'gangtai' => '港台',
            'rihan' => '日韩',
            'oumei' => '欧美',
            'yinyue' => '音乐',
        ),
        'game' => array(
            'all' => '游戏',
        ),
        'sport' => array(
            'all' => '体育',
        ),
        'news' => array(
            'all' => '资讯',
        ),
    );

    if ($key !== false) {
        list($type, $sub) = explode('.', $key);
        if (!$sub)
            $sub = 'all';
        return $opt[$type][$sub];
    }else
        return $opt;
}

function get_catalog($key = false, $type = 0) {
    $opt = array(
        'movie' => '电影',
        'tv' => '电视剧',
        'cartoon' => '动漫',
        'show' => '娱乐',
        'news' => '资讯',
        'game' => '游戏',
        'sport' => '体育',
        'live' => '电视台',
    );

    if ($type == 1)
        $opt = array_values($opt);
    elseif ($t == 2)
        $opt = array_keys($opt);

    if ($key !== false) {
        return $opt[$key];
    } else {
        return $opt;
    }
}

function get_livename($key) {
    $opt = array(
        'sport' => '体育',
        'game' => '游戏',
        'tv' => '电视剧',
        'show' => '综艺',
    );

    if ($key !== false) {
        return $opt[$key];
    } else {
        return $opt;
    }
}

function strip_title($title) {
    $title = preg_replace(array("/\(.*?\)$/", "/《.*?》$/", "/（.*?）$/"), "", $title);
    return $title;
}

function get_ico_index($type = false) {
    $arr = array(
        1 => 2,
        2 => 3,
        3 => 4,
        4 => 6,
        5 => 7,
        6 => 5,
        7 => 8,
        8 => 1
    );
    return $type ? $arr[$type] : $arr;
}

function get_area($type = false, $id = false) {
    $data = array(5 => '大陆', 6 => '香港', 8 => '日本', 12 => '韩国', 16 => '台湾', 4 => '美国', 10 => '英国', 9 => '法国', 0 => '其它');
    if ($type) {
        switch ($type) {
            case 1:
                $keys = array(5, 6, 8, 12, 4, 10, 9, 0);
                break;
            case 2:
                $keys = array(5, 6, 16, 8, 12, 4, 0);
                break;
            case 3:
                $keys = array(5, 8, 0);
                break;
            case 4:
                $keys = array(5, 16, 6, 8, 12, 4, 0);
                break;
        }
        if ($keys) {
            $new_data = array();
            foreach ($keys as $key) {
                $new_data[$key] = $data[$key];
            }
            $data = $new_data;
        }
    }

    if ($id) {
        $data = array_flip($data);
        return $data[$id];
    } else {
        return $data;
    }
}

function week2text($key = false, $format = 0) {
    if ($format = 1)
        $week = array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
    else
        $week = array('星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日');

    if ($key === false)
        return $week;
    else
        return $week[$key - 1];
}

function share_url($app, $link, $title, $pic = '') {
    $title = urlencode($title);
    $link = urlencode($link);
    $url = '';
    switch ($app) {
        case 'renren' :
            $url = "http://share.renren.com/share/buttonshare.do?link={$link}&title={$title}";
            break;
        case 'douban' :
            $url = "http://www.douban.com/recommend/?url={$link}&title={$title}";
            break;
        case 'kaixin' :
            $url = "http://www.kaixin001.com/repaste/share.php?rurl={$link}&rtitle={$title}&rcontent={$link}";
            break;
        case 'weibo' :
            $source = urlencode('PPLive网络电视');
            $sourceurl = urlencode('http://www.pptv.com');
            $url = "http://v.t.sina.com.cn/share/share.php?c=spr_web_bd_pplive_weibo&url={$link}&title={$title}&source={$source}&sourceUrl={$sourceurl}&content=utf-8&pic={$pic}&appkey=1938876518";
            break;
        case 'bai' :
            $url = "http://bai.sohu.com/share/blank/add.do?link={$link}";
            break;
        case 'jianghu' :
            $url = 'http://share.jianghu.taobao.com/share/addShare.htm?url=' . $link;
            break;
        case 'qzone' :
            $url = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . $link;
            break;
        case '163' :
            $url = "http://t.163.com/article/user/checkLogin.do?info={$title} {$link}&link={$link}";
            break;
        case '51' :
            $url = "http://share.51.com/share/out_share_video.php?vaddr={$link}&title={$title}&charset=utf-8&from=pptv";
            break;
        case 'tqq':
            $url = "http://v.t.qq.com/share/share.php?url={$link}&appkey=10030a2eee6c466d8b87d6ea67f70108&site=http://www.pptv.com&pic=&title={$title}";
            break;
    }
    return $url;
}

?>