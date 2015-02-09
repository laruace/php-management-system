<?php
/**
 * @package library\Star
 */

/**
 * 导入文件
 */
require 'Star/Loader.php';
require 'Star/Layout.php';
require 'Star/Config.php';
require 'Star/Model/Abstract.php';

/**
 * 应用基类
 * 
 * @package library\Star
 * @author zqy
 *
 */
class Star_Application {
	
	protected $application_path; //app路径

	protected $bootstrap = null; //应用boostrap
	
    protected $options = array();


    /**
     * 构造方法
     * 
     * @param type $application_env 配置变量
     * @param type $application_path app路径
     * @param type $config_file 配置文件路径
     * @param type $library_path  类库路径
     */
	public function __construct($application_env, $application_path, $config_file, $library_path = '')
	{
		$this->application_path = $application_path;
		$this->setAutoload($library_path);
		$star_config = new Star_Config($config_file, $application_env);
		$options = $star_config->loadConfig();
		$this->setOptions($options);
	}

    /**
     * run application 
     */
	public function run()
	{
		$this->bootstrap->run();
	}
	
	/**
	 *  设置参数
	 *
	 * @param array $options
	 */
	protected function setOptions(array $options)
	{
        $this->options = $options;
        
        if (isset($options['phpSetting']) && !empty($options['phpSetting']))
        {
            $this->setPhpSettings($options['phpSetting']);
        }
        
        if (isset($options['bootstrap']) && !empty($options['bootstrap']))
        {
            $this->setBootstrap($options['bootstrap']);
        }

        if (isset($options['includePaths']) && !empty($options['includePaths']))
        {
            $this->setIncludePaths($options['includePaths']);
        }
        
        $this->initBootstrap();
	}
    
    /**
     * 返回options
     * 
     * @return type 
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * 初始化Bootstrap 
     */
    public function initBootstrap()
    {
        if ($this->bootstrap == null)
        {
            require 'Star/Application/Bootstrap/Bootstrap.php';
            $this->bootstrap = new Star_Application_Bootstrap_Bootstrap($this);
        }
    }


    /**
     * 设置Bootstarp
     * 
     * @param type $bootstrap_path
     * @param string $class
     * @throws Star_Exception 
     */
	public function setBootstrap($options)
	{
        $bootstrap_path = isset($options['path']) ? $options['path'] : '';
        $class = isset($options['class']) && !empty($options['class']) ? $options['class'] : 'Bootstrap';
        
        if (!file_exists($bootstrap_path))
        {
            throw new Star_Exception('Not found Bootstrap file:' . $bootstrap_path);
        }
        
        require $bootstrap_path;
        
        if (!class_exists($class, false))
        {
            throw new Star_Exception('bootstrap object ' . $class . ' not found in:' . $bootstrap_path);
        }
		
		$this->bootstrap = new $class($this);
	}
	
	/**
	 * 设置PHP配置项
	 *
	 * @param array $options
	 */
	public function setPhpSettings(array $options)
	{
		foreach ($options as $key => $value)
		{
			if (is_scalar($value))
			{
				ini_set($key, $value);
			} else if (is_array($value))
			{
				$this->setPhpSetting($value);
			}
		}
	}
	
    /**
     * 执行bootstrap
     * 
     * @param type $resource
     * @return \Star_Application 
     */
	public function bootstrap($resource = null)
	{
        if ($this->bootstrap !=null)
        {
            $this->bootstrap->bootstrap($resource);
        }
		return $this;
	}
	
    /**
     * 返回bootstrap
     * 
     * @return type 
     */
	public function getBootstrap()
	{
		return $this->bootstrap;
	}
	
	/**
	 * 设置导入文件目录
	 * 	 * @param array $options
	 */
	public function setIncludePaths(array $options)
	{
		foreach ($options as $value)
		{
			if (is_string($value) && is_dir($value))
			{
				set_include_path($value);
			} else if (is_array($value))
			{
				$this->setIncludePaths($value);
			}
		}
	}
	
    /**
     * 返回Application path
     * 
     * @return type 
     */
    public function getApplicationPath()
    {
        return $this->application_path;
    }
    
	/**
	 * 设置自动加载
	 */
	public function setAutoload($library_path)
	{
		$star_autoload = new Star_Loader();
		$star_autoload->setApplicationPath($this->application_path)->setLibraryPath($library_path);
		spl_autoload_register(array($star_autoload, 'autoload'));
		return $this;
	}

    /**
     * 设置默认controller_name
     * 
     * @param type $controller_name
     * @return \Star_Application 
     */
    public function setDefaultControllerName($controller_name)
    {
        $this->bootstrap->front->setDefaultControllerName($controller_name);
        return $this;
    }
    
    /**
     * 设置默认action_name 初始是index
     * 
     * @param type $action_name
     * @return \Star_Application 
     */
    public function setDefaultActionName($action_name)
    {
        $this->bootstrap->front->setDefaultActionName($action_name);
        return $this;
    }
    
    /**
     * 是否显示异常
     * 
     * @param type $flag
     * @return type 
     */
    protected function setDisplayException($flag = false)
    {
        return $this->display_exceptions = $flag;
    }
}