<?php
/**
 * @package library\Star\Application\Cache
 */

/**
 * 导入文件
 */
require 'Star/Cache/Interface.php';


/**
 * Memcached 缓存类
 *
 * @package library\Star\Application\Cache
 * @author zhangqinyang
 * 
 */
class Star_Cache_Memcached implements Star_Cache_Interface {

	/**
	 * memcached实例
	 * @var unknown
	 */
	public $memcached = null;
	
	/**
	 * 构造方法   实例化$memcached变量
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->memcached = new Memcached();
		
		if ($config['multi_cache'] == true)
		{
			$this->memcached->addServers($config['server']);
			$this->memcached->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
			$this->memcached->setOption(Memcached::OPT_HASH, Memcached::HASH_CRC);
		} else
		{
			$this->memcached->addServer($config['host'], $config['port']);
		}
	}
	
	/**
	 * 根据key所在服务器映射出该服务器信息
	 * @param unknown $key
	 */
	protected function getServerByKey($key)
	{
		return $this->memcached->getServerBykey($key);
	}
	
	/**
	 * 添加memcached缓存项
	 * @see Star_Cache_Interface::add()
	 */
	public function add($key, $value, $lefttime = 0)
	{
		if ($lefttime == 0)
		{
			return $this->memcached->add($key, $value);
		} else
		{
			return $this->memcached->add($key, $value,  $lefttime);
		}
	}
	
	/**
	 * 获取memcached缓存项
	 * @see Star_Cache_Interface::get()
	 */
	public function get($key)
	{
		return $this->memcached->get($key);
	}
	
	/**
	 * 添加memcached缓存项
	 * @see Star_Cache_Interface::set()
	 */
	public function set($key, $value, $lefttime = 0)
	{
		if ($lefttime == 0)
		{
			return $this->memcached->set($key, $value);
		} else
		{
			return $this->memcached->set($key, $value, $lefttime);
		}
	}
	
	/**
	 * 销毁memcached缓存项
	 * @see Star_Cache_Interface::delete()
	 */
	public function delete($key)
	{
		return $this->memcached->delete($key);
	}
	
	/**
	 * 
	 */
    public function colse()
    {
      
    }
}

?>