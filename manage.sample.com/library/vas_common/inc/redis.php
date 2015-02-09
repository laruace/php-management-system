<?php
/**
 * Redis封装
 */
class Vas_Inc_Redis
{
    protected $_master = null;
    protected $_slave = null;
    protected $_config = array(
        'master' => array(),
        'slave' => array()
    );
    protected $_always_master = 0;


    public function __construct(array $config)
    {
        if (!extension_loaded('redis')) {
            throw new Exception('Redis扩展不存在');
        }
        $this->_config['master'] = isset($config['master']) ? $config['master'] : $config;
        $this->_config['slave'] = isset($config['slave']) ? $config['slave'] : array();
    }


    /**
     * 获取主连接
     * @return SF_Redis_Conn
     */
    public function getMaster()
    {
        if (null === $this->_master) {
            $this->_master = new Vas_Inc_Redis_Conn($this->_config['master']);
        }
        return $this->_master;
    }

    /**
     * 获取从连接
     * @return SF_Redis_Conn
     */
    public function getSlave()
    {
        if (empty($this->_config['slave'])) {
            return $this->getMaster();
        }
        if (null === $this->_slave) {
            if (!empty($this->_config['slave']['host'])) {
                $config = $this->_config['slave'];
            } else {
                $count = count($this->_config['slave']);
                $idx = mt_rand(0, $count-1);
                $config = $this->_config['slave'][$idx];
            }
            $this->_slave = new Vas_Inc_Redis_Conn($config);
        }
        return $this->_slave;
    }

    /**
     * 写主
     */
    public function set($key, $value, $expire = null)
    {
        return $this->getMaster()->set($key, $value, $expire);
    }

    /**
     * 读从
     */
    public function get($key)
    {
        return $this->getSlave()->get($key);
    }

    /**
     * 删主
     */
    public function del($key)
    {
        return $this->getMaster()->delete($key);
    }

    /**
     * 其它主
     */
    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->getMaster(), $method), $args);
    }
}


class Vas_Inc_Redis_Conn
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 6379;
    const DEFAULT_PERSISTENT = false;
    const DEFAULT_TIMEOUT = 0;
    protected $_config = array(
        'host' => self::DEFAULT_HOST,
        'port' => self::DEFAULT_PORT,
        'persistent' => self::DEFAULT_PERSISTENT,
        'timeout' => self::DEFAULT_TIMEOUT
    );
    protected $_redis = null;

    public function __construct(array $config)
    {
        $this->_config = array_merge($this->_config, $config);
        $this->_redis = new Redis;
        $connect_type = $this->_config['persistent'] ? 'pconnect' : 'connect';
        $this->_redis->$connect_type($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
    }

    public function set($key, $value, $expire = null)
    {
        if ($expire) {
            return $this->_redis->setex($key, $expire, $value);
        } else {
            return $this->_redis->set($key, $value);
        }
    }


    public function get($key)
    {
        return $this->_redis->get($key);
    }


    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->_redis, $method), $args);
    }
}
