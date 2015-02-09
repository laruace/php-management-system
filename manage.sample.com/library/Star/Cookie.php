<?php
/**
 * @package library\Star
 */

/**
 *
 * COOKIE操作类
 * 
 * @package library\Star
 * @author zhangqy
 *
 */
class Star_Cookie {
	
    protected static $domain;

    /**
     * 获取cookie
     * @param unknown $name
     * @return unknown
     */
    public static function get($name)
    {
        return $_COOKIE[$name];
    }
    
    /**
     * 设置cookie
     * @param unknown $name
     * @param unknown $value
     * @param number $expire
     * @param string $path
     * @param string $domain
     */
    public static function set($name, $value , $expire = 0, $path = '/', $domain = '', $secure = false, $http_only = false)
    {
        empty($domain) && $domain = self::$domain;
        setcookie($name, $value, $expire, $path, $domain, $secure, $http_only);
    }
    
    /**
     * 设置cookie域
     * @param unknown $domain
     */
    public static function setDomain($domain)
    {
        self::$domain = $domain;
    }
}

?>