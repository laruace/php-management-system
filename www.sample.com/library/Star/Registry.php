<?php
/**
 * @package library\Star
 */

/**
 * 
 * 全局变量
 * 
 * @package library\Star
 * @author zhangqy
 *
 */
class Star_Registry {

	private static $registry = null;
	
	/**
	 * 设置全局变量
	 * @param unknown $key
	 * @param unknown $value
	 */
	public static function set($key, $value)
	{
		self::$registry[$key] = $value;
	}
	
	/**
	 * 获取全局变量
	 * @param string $key
	 * @return Ambigous <unknown, NULL, unknown>
	 */
	public static function get($key = '')
	{
		$options = self::$registry;
		
		return !empty($key) ? $options[$key] : $options;
	}
	
	/**
	 * 删除一个全局变量
	 * @param string $key
	 */
	public static function delete($key = '')
	{
		if (!empty($key))
		{
			unset(self::$registry[$key]);
		}
	}
	
	/**
	 * 删除全部全局变量
	 * @param unknown $key
	 */
    public static function isRegistry($key)
    {
        return isset(self::$registry[$key]);
    }


    public static function destroy()
	{
		self::$registry = null;
	}
}

?>