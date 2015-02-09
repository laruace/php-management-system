<?php
require_once 'phprpc/phprpc_client.php';
require_once 'epg/playlink.php';
require_once 'epg/id.php';
require_once 'epg/input/play.php';
require_once 'epg/input/recommend.php';
require_once 'epg/pptv.php';

class Video{
    public function __construct()
    {

    }

    public function getPlay($id, $type = 'vod')
    {
//    $cache = getCacheStorage();
//    $key   = '1717_vedio_'. $type .'_' . $id;
//    $play  = unserialize($cache->get($key));
//    if (empty($play)) {

        $config =array(

            'rpc' => array(
                'list' => 'http://list.epg.idc.pplive.cn/ikan_list_rpc.jsp',
                'catalog' => 'http://webapi.epg.idc.pplive.cn/api.jsp',
                'play' => 'http://webapi.epg.idc.pplive.cn/api.jsp',
                'recommend' => 'http://recommend.pptv.com/recommend_rpc.jsp',
                //'search' => 'http://list.epg.pplive.com/search_rpc.jsp'
                'search' => 'http://172.16.6.44:8080/search_rpc.jsp',
            ),
            'top_cid' => '7',
            'page_size' => 40,

            'default_sort' => 1
        );

        // 视频信息
        $rpc   = new PHPRPC_Client($config['rpc']['play']);
        $input = new PlayInput();
        $input->channelID = $id;
        $data = $rpc->play($input);
        if(empty($data->ikanChannel)) return;
        $play = array(
            'channelID' => $data->ikanChannel->channelID,
            'pid' => $data->pid,
            'title' => $data->ikanChannel->title,
            'description' => $data->bppInfo->description,
            'seriesList' => $data->seriesList,
            'list' => $data->list,
            'cataIDs' => $data->ikanChannel->cataIDs,
            'rid' => $data->ikanChannel->rid,
            'playCount' => $data->ikanChannel->playCount,
            'duration' => $data->ikanChannel->duration,
            'picUrl' => $data->ikanChannel->picUrl,
            'baikeID' => $data->bppInfo->bkID,
            'img' => get_capture($data->ikanChannel->rid, 120, $data->ikanChannel->picUrl),
        );
        // 相关和推荐视频
        if ($type == 'vod') {
            $rpc = new PHPRPC_Client($config['rpc']['recommend']);
            $recommend_request = new RecommendRequest();
            $recommend_request->currentVideo = $id;
            $recommend_request->numOfResults = 20;
            $recommend = $rpc->recommend($recommend_request);
            if (!empty($recommend->items)) {
                $play['recommend'] = array();
                foreach ($recommend->items as $item) {
                    $play['recommend'][] = array(
                        'id' => $item->id,
                        'title' => $item->title,
                        'capture' => 'http://s'. mt_rand(1, 4) .'.pplive.cn/v/cap/'. $item->id .'/h120.jpg'
                    );
                }
            }
        }

        // 放入缓存
//        $cache->set($key, serialize($play), TIME_OUT * 10);
//    }
        return $play;
    }

    public static function playFormat($play,$video_config = array(),$ad_time = '30'){


        if(isset($play['list']) && count($play['list']) ){
            //多个视频
            foreach($play['list'] as $list){
                $pl =  PlayLink::build_playlink($list->id);
                $vid =  PPID::encode($list->id, 0, empty($play['cataIDs'][0]) ? 0: $play['cataIDs'][0]) ;
                $ipadurl = PlayLink::build_ipad_url($list->id);
                $video_config['playList'][] = array(
                    'pl' => $pl,
                    'title' => $list->title,
                    'link' => 'http://v.pptv.com/show/' .$vid. '.html',
                    'swf' => 'http://player.pptv.com/v/'. $vid .'.swf',
                    'ipadurl' => $ipadurl,
                    'rid' => $list->rid,
                    "mn" => 9999,
                    "live1Str" => null
                );
            }
            //合集title
            $video_config['title'] = $play['title'];
        }else{
            //单个视频
            $pl =  PlayLink::build_playlink($play['channelID']);
            $vid =  PPID::encode($play['channelID'], 0, empty($play['cataIDs'][0]) ? 0: $play['cataIDs'][0]) ;
            $ipadurl = PlayLink::build_ipad_url($play['channelID']);
            $video_config['playList'][] = array(
                'pl' => $pl,
                'title' => $play['title'],
                'link' => 'http://v.pptv.com/show/' .$vid. '.html',
                'swf' => 'http://player.pptv.com/v/'. $vid .'.swf',
                'ipadurl' => $ipadurl,
                'rid' => $play['rid'],
                "mn" => 9999,
                "live1Str" => null
            );
        }

        $video_config['ctx'] = 'subject=1717wan&maxc=1&maxl='.$ad_time; // 接入广告配置
        return $video_config;
    }
}

