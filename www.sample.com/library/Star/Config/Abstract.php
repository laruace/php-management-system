<?php
/**
 * @package library\Star\Application\Config
 */

/**
 *
 * 配置文件类抽象类
 * 
 * 处理.Ini配置文件
 *
 * @package library\Star\Application\Config
 * @author zhangqy
 *
 */
Abstract class Star_Config_Abstract {
	
	protected $file_name;
	
	protected $environment;
	
	public function __construct($file_name, $environment = '')
	{
		$this->file_name = $file_name;
		$this->environment = $environment;
        
	}
	
	/**
	 * 返回配置项
	 */
	public function loadConfig()
	{
		$options = $this->parseConfig();
		return (array) $options;
	}
    
	/**
	 * config子类必须实现此方法,可在子类此方法中添加子类配置
	 */
    public abstract function parseConfig();
	
}

?>