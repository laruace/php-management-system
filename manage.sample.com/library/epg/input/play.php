<?php
class PlayInput
{
    //地区码
    public $areaCode = 0;

    //频道ID
    public $channelID = 0;

    // set channel ID
    public $setID = 0;

    //渠道号
    public $sourceID = 0;

    //是否返回分集，默认1=返回
    public $showList = 1;

    //是否返回系列平级推荐，默认0=不返回
    public $showSeriesList = 0;

    //是否返回预告，花絮， 相关资讯，默认0=不返回
    public $showFeatureList = 0;

    //是否显示打分Map
    public $showStarMap = 0;

    // 是否需要百科介绍全文
    public $showDesc = 0;

    // 封面图列表
    public $showCoverList = 0;

    // 1表示VIP用户，0表示普通用户，对VIP用户使用强制地区屏蔽
    public $userLevel = 0;
}
?>