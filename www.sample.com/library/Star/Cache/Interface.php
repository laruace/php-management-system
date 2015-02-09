<?php
/**
 * @package library\Star\Application\Cache
 */

/**
 * 缓存  接口
 * @package library\Star\Application\Cache
 * @author zhangqinyang
 *
 */
interface Star_Cache_Interface {
	
	/**
	 * 添加缓存项
	 * @param unknown $key
	 * @param unknown $value
	 * @param number $lefttime
	 */
	public function add($key, $value, $lefttime = 0);
	
	/**
	 * 获取缓存项
	 * @param unknown $key
	 */
	public function get($key);
	
	/**
	 * 添加缓存项
	 * @param unknown $key
	 * @param unknown $value
	 * @param number $lefttime
	 */
	public function set($key, $value, $lefttime = 0);
	
	/**
	 * 销毁缓存项
	 * @param unknown $key
	 */
	public function delete($key);
}

?>