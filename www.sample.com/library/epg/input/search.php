<?php
class SearchInputBean
{
    public $keyword = '';
    public $ip = '';
    public $nowPage = 1;
    public $pageSize = 20;
    
    // 地区码，用于地域屏蔽
    public $areaCode = 0;
    
    // 地区过滤，空为不过滤
    public $areas = '';
    
    // 年份过滤，-1为不过滤
    public $year = -1;
    
    // 百科类型过滤，-2为不过滤
    public $baikeType = 12;
    
    // 排序 ，-1/默认：多维度，0：热度，1:创建时间、>=2:评分
    public $sortType = 0;
    
    // 视频类型过滤，0：常规，1：高清；2：蓝光；3：抢先版 ，-1不过滤
    public $videoType = -1;
    
    // 视频语言过滤，0：国语，1：粤语；2：英语；3：韩语；4：日语；5：俄语；6：德语；7：法语，-1不过滤
    public $videoLanguage = -1;
    
    // 片长，无用
    public $duration = -1;
    
    // 无用
    public $vip = 0;
    
    // 是否高亮
    public $highlight = 0;
    
    // 最大高亮长度
    public $maxHighlightLen = 100;
    
    // 高亮标识
    public $preTag = '';
    
    // 高亮标识
    public $postTag = '';
    
    // 使用来源, 1: web, 2: client
    public $source = 0;
    
    // 安全认证
    public $auth = '';
    
    // 是否显示纠错提示，只对合作接口生效
    public $showsuggest = '';
    
    // 版本
    public $ver = '';
    
    // 是否人物id搜索，是的话keyword为id
    public $isPeople = 0;
    
    // 无用
    public $excludePay = 0;
    
    // 真实用户，1为真实用户，使用强制地域屏蔽
    public $coolUser = 0;
    
    // 正片过滤，0为只正片，1为只非正片，-1为不过滤
    public $contentType = -1;
    
    // vt过滤，-1为不过滤
    public $vt = -1;
    
    // 0为普通，1为搜索榜单
    public $customized = 0;
}