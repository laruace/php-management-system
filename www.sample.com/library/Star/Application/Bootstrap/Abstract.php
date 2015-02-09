<?php
/**
 * @package library\Star\Application\Bootstrap
 */

/**
 * @package library\Star\Application\Bootstrap
 * @author zhangqinyang
 */
require 'Star/Controller/Front.php';
require 'Star/View.php';
require 'Star/Http/Request.php';
require 'Star/Http/Response.php';

abstract class Star_Application_Bootstrap_Abstract
{
    protected $application = null; //Star_Application
    protected $class_resources = null;
	protected $container = null;
    protected $view = null; //Star_View
    protected $request = null; //Star_Http_Request
    public $front = null; //Star_Controller_Front

    /**
	 * 构造方法
	 */
	public function __construct($application)
	{
        $this->initRequest();
        $this->initResponse();
        $this->setApplication($application);
        $options = $application->getOptions();
        $this->setOptions($options);
        $this->initView();
        $this->initFrontController();
	}

    /**
     * 设置Application
     * 
     * @param Star_Application $application
     * @return \Star_Application_Bootstrap_Abstract
     * @throws Star_Exception 
     */
    public function setApplication($application)
    {
        if ($application instanceof Star_Application)
        {
            $this->application = $application;
        } else
        {
            throw new Star_Exception('Invalid application provided to bootstrap constructor (received "' . get_class($application) . '" instance)');
        }
        return $this;
    }
    
    /**
     * 返回application
     * @return type 
     */
    public function getApplication()
    {
        return $this->application;
    }


    /**
	 * 设置配置项
	 *
	 * @param array $options
	 */
	public function setOptions($option = null)
	{
		
	}
	
	/**
	 * 
	 */
	public function getClassResources()
	{
		if ($this->class_resources === null)
		{
			if (version_compare(PHP_VERSION, '5.2.6') === -1)
			{
                $class = new ReflectionObject($this);
                $class_methods = $class->getMethods();
                $methods  = array();
                foreach ($class_methods as $method)
                {
                    $methods[] = $method->getName();
                }
            } else
            {
                $methods = get_class_methods($this);
            }
            
			$this->class_resources = array();
			foreach ($methods as $method)
			{
				if (strpos($method, '_init') === 0)
				{
					$this->class_resources[] = $method;
				}
			}
		}
		
		return $this->class_resources;
	}
	
	/**
	 * 
	 * @param string $resource
	 * @return Star_Application_Bootstrap_Abstract
	 */
	final public function bootstrap($resource = null)
	{
		if ($resource === null)
		{
			$class_resources = $this->getClassResources();
			foreach ($class_resources as $method)
			{
				$this->$method();
			}
		}
		
		return $this;
	}
    
    /**
     * 初始化view 
     */
    protected function initView()
    {
        if ($this->view == null || ($this->view instanceof Star_View) == false)
        {
            $this->view = new Star_View(array());
        }
    }
    
    /**
     * 消息派遣 
     */
    public function dispatch()
    {
        $this->front->setView($this->view)->dispatch();
    }
    
    /**
     * 初始化request 
     */
	protected function initRequest()
	{
		$request = new Star_Http_Request();
		$this->request = $request;
	}
    
    /**
     * 初始化Response 
     */
    protected function initResponse()
    {
		$response = new Star_Http_Response();
		$this->response = $response;
    }
    
    /**
     * 初始化frontCrontroller 
     */
    protected function initFrontController()
    {
        if ($this->front instanceof Star_Controller_Front == false)
        {
            $front = new Star_Controller_Front($this->request, $this->response);
            $this->front = $front;
        }
    }

    /**
     * 设置view
     * 
     * @param type $application_path
     * @param type $options
     * @return \Star_Application 
     */
	protected function setView($options)
	{
		$this->view = new Star_View($options);
		return $this;
	}
    
    /**
     * 设置缓存
     * 
     * @param array $options 
     */
	public function setCache(array $options)
	{
        require 'Star/Cache.php';
        Star_Cache::initInstance($options);
	}
    
    /**
     * 设置DB
     * 
     * @param type $options 
     */
    public function setDb($options)
    {
        call_user_func(array('Star_Model_Abstract', 'setting'), $options);
        return $this;
    }
    
    /**
     * 设置FrontController
     * 
     * @param type $options 
     */
    public function setFrontController($options)
    {
        $this->front = new Star_Controller_Front($this->request, $this->response, $options);
        return $this;
    }
}

?>