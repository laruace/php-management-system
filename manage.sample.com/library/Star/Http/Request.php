<?php
/**
 * @package library\Star\Http
 */

/**
 * 导入文件
 */
require 'Star/Http/Abstract.php';

/**
 * request 类
 * 
 * @package library\Star\Http
 * @author zhangqy
 *
 */
class Star_Http_Request extends Star_Http_Abstract
{
    protected $params = array();
    
    /**
     * 构造方法
     */
    public function __construct()
	{
		$this->params = array_merge($_POST, $_GET);
	}
	
    /**
     * 是否是POST请求
     * 
     * @return boolean 
     */
	public function isPost()
	{
		if ('POST' == $this->getMethod())
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * 获取request的访问路径
	 * @return Ambigous <string, unknown, mixed>
	 */
	public function getPathInfo()
	{
		$path = '';
		
		if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL']) //是否重定向
		{
			$path = $_SERVER['REDIRECT_URL'];
            if ($_SERVER['DOCUMENT_ROOT'] != dirname($_SERVER['SCRIPT_FILENAME'])) 
            {
                $path = str_replace(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])) , '', $path); //去除目录路径
            }
            
		} else if ($_SERVER['REQUEST_URI'])
		{
            $url_info = parse_url($_SERVER['REQUEST_URI']);
            if ($_SERVER['PHP_SELF'] == $url_info['path'])
            {
                $path = str_replace($_SERVER['SCRIPT_NAME'], '', $url_info['path']);
            } else {
                $path = str_replace(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])) , '', $url_info['path']); //去除目录路径
            }
            $path = ltrim($path, '\\/');
		}
		return $path;
		
	}
    
   /**
     * 判断是否是缓存数据
     * @return boolean
     */
    public static function isCache()
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && time() < strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
        {
            return true;
        }
        return false;
    }
    
	/**
     * 是否是GET请求
     * 
     * @return boolean 
     */
	public function isGet()
	{
		if ('GET' == $this->getMethod())
		{
			return true;
		}
		
		return false;
	}
	
    /**
     * 是否是ajax请求， 只针对jQuery框架有效
     * 
     * @return boolean 
     */
	public function isAjax()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
		{
			return true;
		}
		
		return false;
	}
	
    /**
     * 返回所有请求参数
     * 
     * @return type 
     */
	public function getParams()
	{
		return $this->params;
	}
	
    /**
     * 通过key返回参数
     * 
     * @param type $key
     * @return type 
     */
	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : '';
	}
	
    /**
     * 通过key设置参数
     * 
     * @param type $key
     * @param type $value 
     */
	public function setParam($key, $value)
	{
		$this->params[$key] = $value;
	}
	
    /**
     * 获取请求方法
     * 
     * @return type 
     */
	protected function getMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}
	
    /**
     * 返回请求HOST
     * 
     * @return type 
     */
	public static function getHost()
	{
		return $_SERVER['HTTP_HOST'];
	}
    
    /**
     * 返回用户访问IP 
     */
    public static function getIp()
    {
        $realip = '';
        if (isset($_SERVER)){
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")){
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }
    
    /**
     * 返回用户浏览器信息
     * 
     * @return type 
     */
    public static function getHttpAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
}

?>