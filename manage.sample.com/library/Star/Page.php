<?php
/**
 * @package library
 */

/**
 * 分页类
 * 
 * @package library
 * @author zqy
 * @version 1.0
 */
class Star_Page
{
	private $page = 1; //当前页面

	private $page_size = 10;
	
	private $total;
	
	private $tag = false; //是否加标签，如果是则链接加上标签
	
	private $tag_head = '['; //标签前面部分
	
	private $tag_foot = ']'; //标签后面部分
	
	private $tag_position = true; //默认为外标签， false则为内标签
	
	private $show_page_size = 5; //显示页面数量,只支持奇数，如果为复数则显示页面数+1
	
	private $last_page; //总共多少页面
	
	private $link; //链接地址
	
	private $language = 'zh'; //显示语言,只支持中英文

	protected static $instance = null;
	
	private $show_language = array(
		'zh' => array('first_page'=>'首页', 'last_page'=>'尾页', 'pre_page'=>'上一页', 'next_page'=>'下一页'), 
		'en' => array('first_page'=>'Frist_page', 'last_page'=>'Last_page', 'pre_page'=>'Pre_page', 'next_page'=>'Next_page')
	);
	
	private $support = array('zh', 'en');
	
	public function __construct()
	{

	}

	public function init($page_array = array())
	{
		self::$instance->page             = isset($page_array['page']) ? $page_array['page'] : self::$instance->page;
		self::$instance->show_page_size = isset($page_array['show_page_size']) ? $page_array['show_page_size'] : self::$instance->show_page_size;
		self::$instance->tag              = isset($page_array['tag']) ? $page_array['tag'] : self::$instance->tag;
		self::$instance->last_page        = isset($page_array['last_page']) ? $page_array['last_page'] : 1;
		self::$instance->link             = isset($page_array['link']) ? $page_array['link'] : '';
		self::$instance->total            = isset($page_array['total']) ? $page_array['total'] : 1;
		self::$instance->page_size      = isset($page_array['page_size']) ? $page_array['page_size'] : self::$instance->page_size;
		self::$instance->language         = isset($page_array['language']) ? $page_array['language'] : self::$instance->language;
		self::$instance->tag_head         = isset($page_array['tag_head']) ? $page_array['tag_head'] : self::$instance->tag_head;
		self::$instance->tag_foot         = isset($page_array['tag_foot']) ? $page_array['tag_foot'] : self::$instance->tag_foot;
		self::$instance->tag_position     = isset($page_array['tag_position']) ? $page_array['tag_postion'] : self::$instance->tag_position;
		if (!isset($page_array['last_page']) && isset($page_array['total']))
		{
			self::$instance->last_page = self::lazy();
		}
		self::$instance->page             = self::$instance->page<1 ? 1 : self::$instance->page;
		self::$instance->page             = self::$instance->page > self::$instance->last_page ? self::$instance->last_page : self::$instance->page;
		if (!isset($page_array['link']))
		{
			self::$instance->setLink();
		}
		if (!isset($page_array['language']))
		{
			self::$instance->confirmLanguage();
		}
		unset($page_array);
	}
	
	public function __destruct()
	{
		unset(self::$instance->english, self::$instance->chinese, self::$instance->support);
	}

	/**
	 * 设置分页参数
	 * @param unknown $page
	 * @param unknown $page_size
	 * @param unknown $total
	 * @return Ambigous <unknown, number>
	 */
	public static function setPage($page, $page_size, $total)
	{
		$last_page = ceil($total/$page_size);
		$page = $page<1 ? 1 : $page;
		$page = $page>$last_page ? $last_page : $page;
		return $page;
	}
	
	/**
	 * 显示分页
	 * @param array $page_info
	 * @return void|Ambigous <string, mixed>
	 */
	public static function show(array $page_info)
	{
		if (self::$instance == null)
		{
			self::$instance = new self();
		}

		self::$instance->init($page_info);
	
		$page = self::$instance->page;
		
		$last_page = self::$instance->last_page;
		
		//总页数为1不显示分页
		if ($last_page <= 1)
		{
			return ;
		}

		$average = self::$instance->average();
		$show_page_start = $page - $average;
		$show_page_end   = $page + $average;
		if ($show_page_start > 0 || $show_page_end <= self::$instance->last_page)
		{
			if ($show_page_start < 0 && $show_page_end < self::$instance->last_page)
			{
				$show_page_end   = (1 - $show_page_start) + $show_page_end;
				$show_page_start = 1;
			}
			if ($show_page_end > self::$instance->last_page && $show_page_start > 0)
			{
				$show_page_start = $show_page_start - ($show_page_end - self::$instance->last_page);
				$show_page_end   = self::$instance->last_page;
			}
		}
		$show_page_start = $show_page_start<1 ? 1 : $show_page_start;
		$show_page_end   = $show_page_end>self::$instance->last_page ? self::$instance->last_page : $show_page_end;
		$result  = self::$instance->getFirstPage();
		$result .= self::$instance->getPrePage();
		for ($i=$show_page_start; $i<=$show_page_end; $i++)
		{
			$result .= self::produceLink($i, $i);
		}
		$result .= self::$instance->getNextPage();
		$result .= self::$instance->getLastPage();
		return $result;
	}
	
	private function lazy()
	{
		$last_page = ceil(self::$instance->total/self::$instance->page_size);
		return $last_page;
	}
	
	/**
	 * 确认是否支持设置语言，不支持则显示中文
	 */
	private function confirmLanguage()
	{
		if (!in_array(self::$instance->language, self::$instance->support))
		{
			self::$instance->language = 'zh';
		}
		return self::$instance->language;
	}
		
	/**
	 * 当前页面前后显示几个分页链接
	 */
	private function average()
	{
		return floor(self::$instance->show_page_size/2);
	}
	
	/**
	 * 生成链接
	 * @param int $page
	 * @param string $page_name
	 * @return string
	 */
	private function produceLink($page, $page_name)
	{
		if (is_numeric($page_name) && $page==self::$instance->page)
		{
			return self::$instance->currentPage($page);
		}
		//标签为true,且为标签在链接内部
		if (self::$instance->tag == true && self::$instance->tag_position == false)
		{
			$link = self::$instance->innerTag($page_name);
		} else
		{
			$link = self::$instance->withoutTag($page_name);
		}
		//标签为true,且标签在链接外部
		if (self::$instance->tag == true && self::$instance->tag_position == true)
		{
			$link = self::$instance->outerTag($page_name);
		}
		$link = str_replace(array('page=?', 'Page=?'), array("page=$page", "Page=$page"), $link);
		return $link;
	}
	
	/**
	 * 当前页面信息
	 * @param string $page_name
	 */
	private function currentPage($page_name)
	{
		if (self::$instance->tag == true)
		{
			$link = "<span>" . self::$instance->tag_head . $page_name . self::$instance->tag_foot . "</span>";
		} else
		{
			$link = "<span>" . $page_name . "</span>";
		}
		return $link;
	}
	
	/**
	 * 不添加标签
	 * @param string $page_name
	 */
	private function withoutTag($page_name)
	{
		$link = "<a href='" . self::$instance->link . "'>". $page_name ."</a>";
		return $link;
	}
	
	/**
	 * 外标签
	 * @param $page_name
	 */
	private function outerTag($page_name)
	{
		$link = self::$instance->withoutTag($page_name);
		$link = self::$instance->tag_head . $link . self::$instance->tag_foot;
		return $link;
	}
	
	/**
	 * 内标签
	 * @param $page_name
	 */
	private function innerTag($page_name)
	{
		$link = "<a href='" . self::$instance->link . "'>". self::$instance->tag_head . $page_name . self::$instance->tag_foot ."</a>";
		return $link;
	}
	
	/**
	 * 获取首页信息
	 */
	private function getFirstPage()
	{
		$page_name = self::$instance->show_language[self::$instance->language]['first_page'];
		$page      = 1;
		return self::$instance->produceLink($page, $page_name);
	}
	
	/**
	 * 获取尾页信息
	 */
	private function getLastPage()
	{
		$page_name = self::$instance->show_language[self::$instance->language]['last_page'];
		$page      = self::$instance->last_page;
		return self::$instance->produceLink($page, $page_name);
	}
	
	/**
	 * 获取上一页信息
	 */
	private function getPrePage()
	{
		$page_name = self::$instance->show_language[self::$instance->language]['pre_page'];
		$page      = self::$instance->page - 1;
		//当前页面为第一页,上一页无链接
		if (self::$instance->page == 1)
		{
			return self::$instance->currentPage($page_name);
		}
		return self::$instance->produceLink($page, $page_name);
	}
	
	/**
	 * 获取下一页信息
	 */
	private function getNextPage()
	{
		$page_name = self::$instance->show_language[self::$instance->language]['next_page'];
		$page      = self::$instance->page + 1;
		//当前页面为最后一页,下一页无链接
		if (self::$instance->page ==   self::$instance->last_page)
		{
			return self::$instance->currentPage($page_name);
		}
		return self::$instance->produceLink($page, $page_name);
	}
	
	private function setLink()
	{
		$uri = $_SERVER['REQUEST_URI'];
		
		$query_string = str_replace($_SERVER['PHP_SELF'], '', $_SERVER['QUERY_STRING']);
		
		//判断是否含有参数
		if (strpos($uri, '?'))
		{
			if (strpos($uri, 'page='))
			{
				$uri = preg_replace("/page=\d+/", 'page=?', $uri);
				$uri = preg_replace("/Page=\d+/", 'Page=?', $uri);
			} else
			{
				$uri = $uri . '&page=?';
			}
		} else
		{
			$uri = $uri . '?page=?';
		}
		return self::$instance->link = $uri;
	}
}

