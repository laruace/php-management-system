<?php
class RecommendRequest
{
    // username，uid
    // 如果用户登录的话，为用户名；未登录的话为计算出来的uid
    public $userId = '';
    
    // 客户端的id会有冲突，客户端id加上这个新id，可以比较好的区分唯一用户
    public $userId2 = '';
    
    // 数量
    public $numOfResults = 10;
    
    // 表明调用者的身份，便于分渠道统计，同时对不同caller，也有不同算法考虑
    public $caller = 'detail_page';
    
    // apikey，如果打算公开此服务，需要apikey鉴定身份。目前不考虑
    public $apiKey = '';
    
    // 空字符串表示不考虑地域屏蔽
    public $area = '';
    
    // 是否包含vip视频
    public $includeVip = true;
    
    // 历史浏览影片id
    public $viewedVideoIds = '';
    
    // 当前观看影片
    public $currentVideo = 0;
    
    // no use now
    public $doc = -1;
    
    // for debug
    public $trackId = 0;
    
    // source
    public $src = 50;
    
    // whether is real user
    public $coolUser = 0;
}
?>