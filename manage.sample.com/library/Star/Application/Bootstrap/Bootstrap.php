<?php
/**
 * @package library\Star\Application\Bootstrap
 */

/**
 * 导入文件
 */
require 'Star/Application/Bootstrap/Abstract.php';

/**
 * bootstrap 类
 * 
 * 用于应用启动初始化 
 * @package library\Star\Application\Bootstrap 
 * @author zhangqinyang
 * 
 */
class Star_Application_Bootstrap_Bootstrap extends Star_Application_Bootstrap_Abstract
{
    /**
	 * 构造方法
	 */
	public final function __construct($application)
	{
		parent::__construct($application);
	}
	
	/**
	 * 设置配置项
	 *
	 * @param array $options
	 */
	public function setOptions($options = null)
	{
		if (!empty($options))
        {
            $options = array_change_key_case($options, CASE_LOWER);
            foreach ($options as $key => $value)
            {
                if ($key == 'resources')
                {
                    $methods = get_class_methods($this);
                    $methods = array_flip($methods);
                    foreach ($value as $resource => $resource_options)
                    {
                        $method = 'set' . ucfirst($resource);
                        if (array_key_exists($method, $methods))
                        {
                            $this->$method($resource_options);
                        }
                    }
                }
            }
        }
	}
    
    public function run()
    {
        $this->dispatch();
    }
}

?>