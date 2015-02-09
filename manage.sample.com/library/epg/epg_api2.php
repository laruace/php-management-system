<?php

/**
 * epg_api.php
 * EPG API接口
 * @todo 结构优化
 *
 * @author      hfcorriez <hfcorriez@gmail.com>
 * @version     $Id: api.php v 0.2 2011-4-11 16:13:38 hfcorriez $
 * @copyright   Copyright (c) 2007-2010 PPLive Inc.
 * @example
 * #hosts
 * 180.153.106.26		epg-webapi.pplive.com epg-search.pplive.com
 *
 */
error_reporting(E_ALL & ~E_NOTICE);

if (!defined('EPG_API_COMMON_DIR')) {
    define('EPG_API_COMMON_DIR', dirname(__FILE__) . '/common');
}
if (!defined('EPG_API_USER_IP')) {
    define('EPG_API_USER_IP', Epg_Api::get_ip());
}
if (!defined('EPG_API_AREA_CODE')) {
    define('EPG_API_AREA_CODE', 0);
}
define('EPG_API_VERSION', '0.2');
define('EPG_API_START_TIME', time());
define('EPG_API_TODAY_TIME', strtotime('Y-m-d 00:00:00'));

class Epg_Api {
    #RPC接口地址

    private static $rpc_interface = array(
        'search' => 'http://list.epg.pplive.com/search_rpc.jsp',
        'list' => 'http://client-play.pptv.com/ikan_list_rpc.jsp',
        'catalog' => 'http://webapi.epg.pptv.com/xml_ikan_cata.jsp',
        'play' => 'http://webapi.epg.pptv.com/api.jsp',
        'liveepg' => 'http://tvepg.synacast.com/xihttp_phprpc_service.jsp',
        'search_tips' => 'http://client-searchtips.pplive.cn/c3_tips_rpc.jsp',
    );
    //地区码
    private static $ipgroup = array(
        'P' => 2,
        'Q' => 4,
        'R' => 62,
        'T' => 34,
        'U' => 333,
        'W' => 601,
        'H' => 5,
        'E' => 9999,
        'J' => 1028,
        'M' => 1002,
        'N' => 64,
        'O' => 1,
        'A' => 6,
        'C' => 58,
        'D' => 53,
        'X' => 63,
        'Y' => 1001,
        'Z' => 999,
    );

    #RPC对象
    private static $rpc = array();
    private static $param = array();

    #结果模型
    private static $result = array(
        //分类模型
        'catalog' => array(
            'id',
            'title',
            'pid',
            'type',
            'rank',
            'display' => 'visiable'
        ),
        //视频数据模型
        'play' => array(
            'error_code' => 'errorCode',
            'channel' => 'ikanChannel',
        ),
        //频道模型
        'channel' => array(
            'id' => array('channelID', 'id'),
            'title' => array('title', 'name'),
            'cat_id' => 'cataID',
            'vip',
            'fb' => array('forbidden', 'fbs'),
            'status',
            'rid',
            'filename' => 'fileName',
            'type' => 'vt',
            'parent_type',
            'pid',
            'hot',
            //'views' => 'rhot',
            'views' => array('playCount', 'rhot', 'views'),
            'episode_count' => 'cnt',
            'update_time' => 'updateTime',
            'duration',
            'rank',
            'capture_file' => 'picUrl',
            //'latest_episode' => 'latestEpisode',
            'episode_title' => 'epTitle',
            'cat_ids' => 'cataIds',
            'video_type' => 'videoType',
            'order_type',
            'commend_type' => 'recType',
            'playlink',
            'baike',
            'episodes',
        ),
        //百科信息模型
        'baike' => array(
            'id' => array('bkID', 'baikeID'),
            'type' => array('bkType', 'baikeType'),
            'episode_id' => 'subID',
            'title',
            'title_en' => 'titleEn',
            'year' => 'years',
            'cover' => array('coverPic', 'cover'),
            'directors',
            'actors',
            'plot' => 'description',
            'areas',
            'classes',
            'score',
            'languages'
        ),
        //分集模型
        'episode' => array(
            'id',
            'title' => array('epTitle', 'title'),
            'rid',
            'filename' => 'fileName',
            'capture_file' => 'picUrl',
            'fb' => 'forbidden',
            'rank'
        ),
        //列表模型
        'list' => array(
            'total',
            'channels' => 'channels'
        ),
        //搜索模型
        'search' => array(
            'best_channel' => 'bestVideo',
            'best_people' => 'peopleBean',
            'best_type' => 'bestType',
            'channels' => 'videos',
            'types' => 'typeCount',
            'total' => 'total',
        ),
        //标识模型
        'titles' => array(
            'id',
            'title'
        ),
        //种类数量模型
        'types' => array(
            'type',
            'count'
        ),
        //人物模型
        'people' => array(
            'id',
            'cover' => 'coverPic',
            'name' => 'title',
            'gender',
            'profession' => 'prof',
            'birthday' => 'birthdate',
            'origin' => 'birthPlace',
            'episodes'
        ),
        'liveepg' => array(
            'icon',
            'list',
        ),
        'liveepg_list' => array(
            'play_time' => 'playTime',
            'title',
        ),
        'top' => array(
            'channel' => 'ikanChannel',
            'episodes' => 'list',
            'views' => 'count',
            'trend'
        ),
        'keyword' => array(
            'create_time' => 'createTime',
            'count',
            'keyword' => 'keyWord',
        )
    );

    #输入模型
    private static $input = array(
        //搜索模型
        'search' => array(
            'keyword',
            'ip',
            'page' => 'nowPage',
            'size' => 'pageSize',
            'area_code' => 'areaCode',
            'areas',
            'year',
            'baike_type' => 'baikeType',
            'sort_type' => 'sortType',
            'video_type' => 'videoType',
            'language' => 'videoLanguage',
            'duration',
            'vip',
        ),
        //列表模型
        'list' => array(
            'page' => 'nowPage',
            'size' => 'pageSize',
            'area_code' => 'areaCode',
            'cat_id' => 'cataId',
            'year',
            'sort_type' => 'sortType',
            'areas',
            'vip',
            'source',
            'status' => 'videoStatus'
        ),
        'play' => array(
            'area_code', 'id', 'set_id', 'src_id'
        ),
        'related' => array(
            'area_code', 'id', 'cat_id', 'count'
        ),
        'top' => array(
            'area_code', 'cat_id', 'type', 'count'
        ),
        'episode' => array(
            'area_code', 'id', 'type', 'src_id'
        ),
        'liveepg' => array(
            'id', 'time', 'start'
        ),
        'search_tips' => array(
            'keyword', 'area_code', 'vip'
        )
    );
    #默认数据
    private static $input_default = array(
        'search' => array(
            'keyword' => '',
            'ip' => EPG_API_USER_IP,
            'page' => 1,
            'size' => 20,
            'area_code' => EPG_API_AREA_CODE,
            'areas' => '',
            'year' => -1,
            'baike_type' => -2,
            'sort_type' => -1,
            'video_type' => -1,
            'language' => -1,
            'duration' => -1,
            'vip' => 0
        ),
        'list' => array(
            'page' => 1,
            'size' => 20,
            'area_code' => EPG_API_AREA_CODE,
            'cat_id' => -1,
            'year' => -1,
            'sort_type' => 0,
            'areas' => '',
            'vip' => 0,
            'source' => '',
            'status' => 0
        ),
        'play' => array(
            'area_code' => EPG_API_AREA_CODE, 'id' => 0, 'set_id' => 0, 'src_id' => 0
        ),
        'related' => array(
            'area_code' => EPG_API_AREA_CODE, 'id' => 0, 'cat_id' => -1, 'count' => 10
        ),
        'top' => array(
            'area_code' => EPG_API_AREA_CODE, 'cat_id' => -1, 'type' => 0, 'count' => 10
        ),
        'episode' => array(
            'area_code' => EPG_API_AREA_CODE, 'id' => 0, 'type' => 0, 'src_id' => 0
        ),
        'liveepg' => array(
            'id', 'time' => 86400, 'start' => EPG_API_TODAY_TIME
        ),
        'search_tips' => array(
            'area_code' => EPG_API_AREA_CODE, 'keyword' => '', 'vip' => 0
        )
    );

    private function __construct() {

    }

    /**
     * @return Epg_Api
     */
    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    public static function set_interface($interface = array()) {
        self::$rpc_interface = array_merge(self::$rpc_interface, $interface);
    }

    /**
     * 获取播放数据
     * @param <type> $param
     * Array(
     *      area_code => 屏蔽码
     *      id => 节目ID
     *      set_id => 父级ID
     *      src_id => 来源ID
     * )
     * @return array            播放数据
     */
    public function get_play($param) {
        self::parse_input('play', $param, false);
        $data = self::rpc('play')->play((int) $param['area_code'], (int) $param['id'], (int) $param['set_id'], (int) $param['src_id']);
 
        self::parse_result_play($data);
        return $data;
    }

    /**
     * 获取播放关联推荐数据
     * @param array $param
     * Array(
     *      area_code => 屏蔽码
     *      id => 节目ID
     *      cat_id => 分类ID
     *      count => 数量
     * )
     * @return array            播放数据
     */
    public function get_related($param) {
        self::parse_input('related', $param, false);

        $data = self::rpc('play')->getCommend((int) $param['area_code'], (int) $param['id'], (int) $param['cat_id'], (int) $param['count']);

        self::parse_result_related($data);
        return $data;
    }

    /**
     * 获取排行榜
     * @param array $param
     * Array(
     *      area_code => 屏蔽码
     *      cat_id => 分类ID
     * )
     * @return array            播放数据
     */
    public function get_top($param) {
        self::parse_input('top', $param, false);

        $data = self::rpc('play')->top((int) $param['area_code'], (int) $param['cat_id'], (int) $param['type'], (int) $param['count']);

        self::parse_result_top($data);
        return $data;
    }

    /**
     * 分集数据
     * @param array $param
     * Array(
     *      area_code => 屏蔽码
     *      id => 节目ID
     *      type => 节目类型，21（剧集）|22（合集）
     *      src_id => 来源ID
     * )
     * @return array            分集数据
     */
    public function get_episode($param) {
        self::parse_input('episode', $param, false);

        $data = self::rpc('play')->getList((int) $param['area_code'], (int) $param['id'], (int) $param['type'], (int) $param['src_id']);

        self::parse_result_array('episode', $data);
        return $data;
    }

    /**
     * 获取分类数据
     * @param int $id           当前分类ID
     * @return array            分类数据
     */
    public function get_tree($id = 0, $catalog = false) {
        if (!class_exists('Tree', false)) {
            require EPG_API_COMMON_DIR . '/tree.php';
        }

        if (!$catalog) {
            $catalog = self::get_catalog();
        }
        #order by `pid` asc, `pri` desc
        uasort($catalog, create_function('$a, $b', 'return strcasecmp(str_pad($a["pid"], 5, "0", STR_PAD_LEFT) . str_pad(pow(10, 10) - $a["rank"], 10, "0", STR_PAD_LEFT), str_pad($b["pid"], 5, "0", STR_PAD_LEFT) . str_pad(pow(10, 10) - $b["rank"], 10, "0", STR_PAD_LEFT));'));

        return Tree::instance()->set_array($catalog)->mark($id)->get_all($id);
    }

    /**
     * 获取分类
     * @return array    分类数据
     */
    public function get_catalog() {
        #fetch xml as string.
        $xml = @file_get_contents(self::$rpc_interface['catalog'], null, stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 3))));
        if (!$xml) {
            return false;
        }
        #parse xml
        $catalog = array();
        $xml = simplexml_load_string($xml);
        if (count($xml->Catalogs->cl) > 0) {
            foreach ($xml->Catalogs->cl as $cl) {
                if ((int) $cl ['visible'] == 0) {
                    continue;
                }
                $cat = array();
                $cat ['id'] = (int) $cl ['id'];
                $cat ['pid'] = (int) $cl['pid'];
                $cat ['title'] = (string) $cl ['nm'];
                $cat ['rank'] = (int) $cl ['rank'];
                $cat ['display'] = (int) $cl ['visible'];
                $catalog[] = $cat;
            }
        }
        return $catalog;
    }

    /**
     * 获取直播节目单
     * 返回传入时间前后12个小时的节目单数据
     *
     * @param array $param
     * Array(
     *      id => mixed 数组或者单ID
     *      time => 时间戳
     * )
     * @return <type>
     */
    public function get_liveepg($param) {
        self::parse_input('liveepg', $param, false);

        $data = self::rpc('liveepg')->getLiveEpg($param['id'], (int) $param['start'], (int) $param['time']);

        if (is_array($param['id'])) {
            self::parse_result_array('liveepg', $data);
        } else {
            self::parse_result_liveepg($data);
        }
        return $data;
    }

    /**
     * 获取索引数据
     * @param array $param      参数列表请见Input模型
     * @return array            索引页数据
     */
    public function get_list($param) {
        self::parse_input('list', $param);

        $data = self::rpc('list')->search($param);
        self::parse_result_list($data);

        return $data;
    }

    /**
     * 获取搜索提示
     * @param array $param
     * Array(
     *      keyword => 关键字
     *      area_code => 屏蔽码
     *      vip => VIP等级
     * )
     * @return <type>
     */
    public function get_search_tips($param) {
        self::parse_input('search_tips', $param, false);

        return self::rpc('search_tips')->gettips($param['keyword'], (int) $param['area_code'], (int) $param['vip']);
    }

    /**
     * 获取搜索数据
     * @param array $param      参数列表请见Input模型
     * @return array            索引页数据
     */
    public function get_search($param) {
        self::parse_input('search', $param);
        $data = self::rpc('search')->search($param);
        self::parse_result_search($data);

        //补充episode
        if ($data['best_type'] == 0 && $data['best_channel'] && empty($data['best_channel']['episodes'])) {
            $param = array(
                'area_code' => $param->areaCode,
                'id' => $data['best_channel']['id'],
                'type' => $data['best_channel']['type'],
                'src_id' => 0
            );
            $data['best_channel']['episodes'] = self::get_episode($param);
        }

        return $data;
    }

    /**
     * 获取大家都在搜
     * @param array $param      参数列表请见Input模型
     * @return array            索引页数据
     */
    public function get_search_keywords() {
        $data = self::rpc('search')->getkeywords();
        $data = $data['KeyWordCount'];
        self::parse_result_array('keyword', $data);
        return $data;
    }

    /**
     * 解析list模型数据
     */
    private static function parse_result_list(&$data) {
        self::parse_result('list', $data);
        self::parse_result_array('channel2', $data['channels']);
    }

    /**
     * 解析search模型数据
     */
    private static function parse_result_search(&$data) {
        $episode = $data['subsetVideos'];

        self::parse_result('search', $data);
        self::parse_result_array('channel2', $data['channels']);
        if ($data['best_channel'] || $data['best_people']) {
            if ($data['best_type'] == 0) {
                $data['best_channel']['baike'] = $data['best_channel'];
                $data['best_channel']['episodes'] = $episode;
                self::parse_result_channel($data['best_channel']);
            } elseif ($data['best_type'] == 1) {
                $data['best_people']['episodes'] = $episode;
                self::parse_result('people', $data['best_people']);
                self::parse_result_array('episode', $data['best_people']['episodes']);
            }
        }
    }

    /**
     * 解析related模型下的play数据
     */
    private static function parse_result_channel2(&$data) {
        $copy = $data;
        $data['baike'] = $copy;
        self::parse_result_channel($data);
    }

    /**
     * 解析直播节目单
     */
    private static function parse_result_liveepg(&$data) {
        self::parse_result('liveepg', $data);
        self::parse_result_array('liveepg_list', $data['list']);
    }

    /**
     * 解析相关推荐
     */
    private static function parse_result_related(&$data) {
        self::parse_result_array('channel2', $data);
    }

    /**
     * 解析排行榜
     */
    private static function parse_result_top(&$data) {
        self::parse_result_array('item4top', $data);
    }

    private static function parse_result_item4top(&$data) {
        $copy = $data;
        self::parse_result_channel2($data);
        $data['trend'] = $copy['trend'];
        $data['views'] = $copy['count'];
    }

    /**
     * 解析play模型数据
     */
    private static function parse_result_play(&$data) {
        $data['ikanChannel']['parent_type'] = $data['pvt'];
        $data['ikanChannel']['order_type'] = $data['orderType'];
        $data['ikanChannel']['baike'] = $data['bppInfo'];
        $data['ikanChannel']['playlink'] = $data['playlink'];
        $data['ikanChannel']['episodes'] = $data['list'];
        self::parse_result('play', $data);
        self::parse_result_channel($data['channel']);
        if (in_array($data['channel']['type'], array(21, 22))) {
            if (!$data['channel']['episode_count'])
                $data['channel']['episode_count'] = count($data['episodes']);
        }
    }

    /**
     * 解析channel模型数据
     */
    private static function parse_result_channel(&$data) {
        self::parse_result('channel', $data);
        self::parse_result_baike($data['baike']);
        //check episodes
        if ($data['episodes']) {
            self::parse_result_array('episode', $data['episodes']);
        }
        //self::parse_result('episode', $data['latest_episode']);
        if (!is_array($data['cat_ids'])) {
            $data['cat_ids'] = $data['cat_ids'] ? explode(',', $data['cat_ids']) : array();
        }
        if (!$data['cat_id'] && $data['cat_ids']) {
            $data['cat_id'] = $data['cat_ids'][0];
        }
        if ($data['cat_id'] && !$data['cat_ids']) {
            $data['cat_ids'] = array($data['cat_id']);
        }
        if ($data['fb'] && !is_array($data['fb'])) {
            if (strpos($data['fb'], ',')) {
                $data['fb'] = explode(',', trim($data['fb'], ','));
            } elseif (strpos($data['fb'], '|')) {
                $data['fb'] = explode('|', trim($data['fb'], '|'));
            } else {
                $data['fb'] = array(trim($data['fb']));
            }
            foreach ($data['fb'] as $k => $fb) {
                unset($data['fb'][$k]);
                $data['fb'][strtoupper($fb)] = self::$ipgroup[strtoupper($fb)];
            }
        }
        //增加标题提取
        $data['base_title'] = $data['title'];
        if (substr($data['title'], -1) == ')') {
            preg_match('/\(.*?\)$/', $data['title'], $match);
            $data['base_title'] = str_replace($match[0], '', $data['title']);
            $data['tip'] = substr($match[0], 1, -1);
        }
    }

    /**
     * 解析baike模型数据
     */
    private static function parse_result_baike(&$data) {
        self::parse_result('baike', $data);
        foreach (array('classes', 'directors', 'actors') as $key) {
            self::parse_result_array('titles', $data[$key]);
        }
    }

    /**
     * 解析输入模型
     * @param mixed $model      模型或者模型名称
     * @param array $data       要解析的数据
     */
    private static function parse_input($model, &$data, $object = true) {
        $ret = array();
        $model_key = strtolower($model);
        if (!is_array($model)) {
            $model = self::$input[$model_key];
        }
        //set default data
        $data = array_merge(self::$input_default[$model_key], is_array($data) ? $data : array());
        //parse to data by model
        foreach ($model as $a => $b) {
            is_int($a) && $a = $b;
            $ret[$b] = $data[$a];
        }
        if ($object)
            $data = (object) $ret;
        self::$param = $data;
        unset($ret);
    }

    /**
     * 按照ArrayList方式解析模型
     * @param mixed $model      模型或者模型名称
     * @param array $data       要解析的模型数据
     * @return array            最终数据
     */
    private static function parse_result_array($model, &$data) {
        if (!$data || !is_array($data)) {
            return $data;
        }
        $method = '';
        if (is_string($model) && $model) {
            $method = 'parse_result_' . $model;
            if (!method_exists(__CLASS__, $method)) {
                $method = '';
            }
        }
        foreach ($data as $k => $v) {
            if ($method) {
                //use meothod to parse if exists
                self::$method($data[$k]);
            } else {
                //use default parse model
                self::parse_result($model, $data[$k]);
            }
        }
    }

    /**
     * 解析模型
     * @param mixed $model      模型或者模型名称
     * @param array $data       要解析的模型数据
     * @return array            最终数据
     */
    private static function parse_result($model, &$data) {
        $ret = array();
        if (!is_array($model)) {
            $model = self::$result[strtolower($model)];
        }
        if ($data && is_array($data)) {
            //parse to data by model
            foreach ($model as $a => $b) {
                is_int($a) && $a = $b;
                if (!is_array($b)) {
                    $ret[$a] = $data[$b];
                } else {
                    foreach ($b as $key) {
                        if (isset($data[$key])) {
                            $ret[$a] = $data[$key];
                            break;
                        }
                    }
                    if (!isset($ret[$a]))
                        $ret[$a] = null;
                }
            }
        }
        $data = $ret;
        unset($ret);
    }

    /**
     * 获取一个类型的RPC实例
     * @param string $key           RPC的配置KEY
     * @return Object               RPC对象
     */
    private static function rpc($key) {
        if (!class_exists('RPC', false)) {
            require EPG_API_COMMON_DIR . '/rpc.php';
        }
        if (!isset(self::$rpc[$key])) {
            $rpc_conf = array(
                'uri' => self::$rpc_interface[$key],
                'charset' => 'utf-8',
                'timeout' => 5,
            );
            self::$rpc[$key] = RPC::instance($rpc_conf);
        }
        return self::$rpc[$key];
    }

    /**
     * 获取用户IP
     */
    public static function get_ip() {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = $_SERVER ['REMOTE_ADDR'];
        }
        return $ip;
    }

}

?>