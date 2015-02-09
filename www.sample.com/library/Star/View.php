<?php
/**
 * @package library\Star
 */

/**
 * 导入文件
 */
require 'Star/View/Abstract.php';

/**
 * Start_view
 *
 * @package library\Star
 * @author zhangqy
 *
 */
class Star_View extends Star_View_Abstract{
	
	/**
	 * 构造方法
	 * @param string $application_path
	 * @param unknown $options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
	}
	
	/**
	 * 
	 * @see Star_View_Abstract::run()
	 */
	protected function run()
	{
        
	}
	
    /**
     * 设置配置项
     * 
     * @param array $options 
     */
    public function setOption(array $options) 
    {
        if (!empty($options))
        {
            $methods = get_class_methods($this);
            $methods = array_flip($methods);
            foreach ($options as $method => $option)
            {
                $method = "set" . ucfirst($method);
                if (isset($methods[$method]))
                {
                    call_user_func(array($this, $method), $option);
                }
            }
        }

    }
}

?>