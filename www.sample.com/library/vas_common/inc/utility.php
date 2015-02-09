<?php
class Vas_Inc_Utility
{
    /**
     * 获取redis缓存
     */
    public static function getRedis() {
        static $redis = null;
        if (null === $redis) {
            require_once dirname(__FILE__) .'/redis.php';
            require_once dirname(dirname(__FILE__)) .'/config/server.php';
            $redis = new Vas_Inc_Redis($redis_user);
        }
        return $redis;
    }

    public static function cacheGet($key, $json = false)
    {
        $data = self::getRedis()->get($key);
        return $json ? json_decode($data, true) : $data;
    }
    public static function cacheSet($key, $data, $json = false)
    {
        if (!empty($data)) {
            $json && $data = json_encode($data);
            self::getRedis()->set($key, $data, 1300000);
        }
    }
    public static function cacheDel($key)
    {
        self::getRedis()->delete($key);
    }

    /**
     * 获取IP
     * 如果有多个IP，只获取一个
     */
    public static function getIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }
        if (true == ($p = strpos($ip, ','))) {
            $ip = substr($ip, 0, $p);
        }
        return $ip;
    }
}
