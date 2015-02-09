<?php
class IkanInput
{
    /**
     * 当前页
     * @var int
     */
    public $nowPage = 1;

    /**
     * 每页数据
     * @var int
     */
    public $pageSize = 20;

    /**
     * 地区码
     * @var int
     */
    public $areaCode = 0;

    /**
     * 分类ID
     * @var int
     */
    public $cataId = -1;

    /**
     * 年代
     * @var int
     */
    public $year = -1;

    /**
     * 排序方式:
     * 0 :热度
     * 1 :更新时间
     * 2 :权重
     * 3 :名称
     * 4 :评分
     * 5 :总人气
     * 6 :7天的人气
     * 7 :30天的人气
     * 8 :上升最快(播放次数差)
     * 9 :上升最快(排名差)
     * 10:评论数11:直播在线人数12: 顶踩比率
     * @var int
     */
    public $sortType = 0;

    /**
     * 地区
     * @var string
     */
    public $areas = '';

    /**
     * 是否vip
     * -1表示不过滤，0表示只返回非VIP内容，1表示只返回VIP内容
     * @var int
     */
    public $vip = -1;

    /**
     * @var string
     */
    public $source = '';

    /**
     * 1=剧场版,2=OVA,3=完结,4=连载,5=新番
     * @var int
     */
    public $videoStatus = 0;

    /**
     * 0=常规,1=高清,2=蓝光,3=抢先版,4=片花
     * @var int
     */
    public $videoType = -1;

    /**
     * 视频类型，0=直播,3=点播，4=二代直播,21=剧集,22=合集 以“,”隔开，如：“0,4”
     * @var string
     */
    public $vt = '';

    /**
     * 内容类型，0=正片，1=预告，2=花絮（99为非正片）
     * @var int
     */
    public $conType = -1;

    /**
     * 版权 （1：有版权）
     * @var int
     */
    public $copyright = -1;

    /**
     * 我方是否独家(0:否，1：是)
     * @var int
     */
    public $pptvOnly = 0;

    /**
     * 最后更新时间(0:最近24h，1：最近一周 ，2：最近一个月， 3：最近3个月)
     * @var int
     */
    public $update = -1;

    /**
     * 百科类型（动作，爱情...）
     * @var string
     */
    public $bkClass = '';

    /**
     * 付费信息
     * @var int
     */
    public $payInfo = -1;

    /**
     * 码流率
     * @var int
     */
    public $ft = -1;

    /**
     * 1表示VIP用户，0表示普通用户，对VIP用户使用强制地区屏蔽
     * @var int
     */
    public $userLevel = 0;

    public $coolUser = 0;
}
?>