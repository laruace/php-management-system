<?php
/**
 * @package library\Star\Application\Cache
 */

/**
 * 导入文件
 */
require 'Star/Cache/Interface.php';

/**
 * Redis 缓存类
 * 
 * @package library\Star\Application\Cache
 * @author zhangqinyang
 *
 */
class Star_Cache_Redis implements Star_Cache_Interface {

	/**
	 * redis实例
	 * @var unknown
	 */
	public $redis = null;
	
	/**
	 * 构造方法   实例化$redis变量
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->redis = new Redis();
        $this->redis->connect($config['host'], $config['port']);
	}
	
	/**
	 * 添加redis缓存项
	 * @see Star_Cache_Interface::add()
	 */
	public function add($key, $value, $lefttime = 0)
	{
		if ($lefttime > 0)
        {
            return $this->redis->setex($key, $lefttime, $value);
        } else
        {
            return $this->redis->set($key, $value);
        }
	}
	
	/**
	 * 获取redis缓存项
	 */
	public function get($key)
	{
		return $this->redis->get($key);
	}
	
	/**
	 * 添加redis缓存项
	 * @see Star_Cache_Interface::set()
	 */
	public function set($key, $value, $lefttime = 0)
	{
        if ($lefttime > 0)
        {
            return $this->redis->setex($key, $lefttime, $value);
        } else
        {
            return $this->redis->set($key, $value);
        }
	}
	
	/**
	 * 销毁redis缓存项
	 * @see Star_Cache_Interface::delete()
	 */
	public function delete($key)
	{		
		return $this->redis->del($key);
	}

	/**
	 * 将一个或多个值values插入到列表key的表尾（最右边） ，
	 * 如果key不存在，一个空列表会被创建并执行rPush操作.
	 * 当key存在但不是列表类型时，返回一个错误
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function rPush($key, $value)
	{
		return $this->redis->rPush($key, $value);
	}

	/**
	 *  将一个或多个值values插入到列表key的表头（最左边） ，
	 * 如果key不存在，一个空列表会被创建并执行lPush操作.
	 * 当key存在但不是列表类型时，返回一个错误
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function lPush($key, $value)
	{
		return $this->redis->lPush($key, $value);
	}
	
	/**
	 * 移除并返回列表key的头元素
	 * 当key不存在时，返回nil
	 * @param unknown $key
	 */
	public function lPop($key)
	{
		return $this->redis->lPop($key);
	}

	/**
	 * 移除并返回列表key的尾元素
	 * 当key不存在时，返回nil
	 * @param unknown $key
	 */
	public function rPop($key)
	{
		return $this->redis->rPop($key);
	}

	/**
	 * 返回李彪的大小
	 * 如果列表不存在或为空，则返回0
	 * 如果key不是列表，返回FALSE
	 * @param unknown $key
	 */
	public function lSize($key)
	{
		return $this->redis->lSize($key);
	}

	/**
	 * 返回列表中某个范围的值，相当于mysql里的分页查询
	 * @param unknown $key
	 * @param unknown $start
	 * @param unknown $end
	 */
	public function lRange($key, $start, $end)
	{
		return $this->redis->lRange($key, $start, $end);
	}
}

?>