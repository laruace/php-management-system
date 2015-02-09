<?php
/**
 * @package library\Star\Controller\Front
 */
/**
 * 导入文件
 */

/**
 * controller front类
 * 
 * @package library\Star\Controller\Front
 * @author zhangqinyang
 */
class Star_Controller_Front{

    protected $display_exceptions = false; //默认不显示异常
    protected $request; //Star_Http_Request
	protected $response; //Star_Http_Response
    protected $view; //Star_View
    protected $controller_directory; //controller目录
    protected $module_valid = false; //是否配置module
    protected $defualt_module = 'default'; //默认module
    protected $module_controller_directoryectory_name = 'controllers'; //modules controller目录名
    protected $module_name = ''; //module名称
    protected $module_controller_directory = array(); //module controller目录
    protected $default_controller_name = 'index';
    protected $default_action_name = 'index';
    protected $module_key = 'module';
    protected $controller_key = 'controller';
    protected $action_key = 'action';
    protected $url_delimiter = '/';
    	
	public function __construct(Star_Http_Request $request, Star_Http_Response $response, $options = array())
	{
		$this->request = $request;
        $this->response = $response;
        $this->setOptions($options);
        $params = $this->route();
        $this->setRequestParam($params);
	}
    
    /**
     * 设置options
     * 
     * @param type $options
     * @return \Star_Controller_Front 
     */
    public function setOptions($options = array())
    {
        if (!empty($options))
        {
            $this->options = $options;
            $methods = get_class_methods($this);
            $methods = array_flip($methods);
            foreach ($options as $key => $option)
            {
                $method = 'set' . ucfirst($key);
                if (array_key_exists($method, $methods))
                {
                    $this->$method($option);
                }
            }
        }
        return $this;
    }
    
    /**
     * 设置view
     * 
     * @param Star_View $view
     * @return $this 
     */
    public function setView(Star_View $view)
    {
        $this->view = $view;
        return $this;
    }
    
    /**
     * module是否存在
     * 
     * @param type $module
     * @return boolean 
     */
    public function isValidModule($module)
    {
        if (empty($module) || !is_string($module))
        {
            return false;
        }
        
        if ($this->getModuleControllerDirectory($module))
        {
            return true;
        } else
        {
            return false;
        }
    }
    
    /**
     * 添加 module controller路径
     * 
     * @param type $directory
     * @param type $module
     * @return type 
     */
    public function addModuleControllerDirectory($directory, $module)
    {
        if ($module === null)
        {
            $module = $this->defualt_module;
        }
        return $this->module_controller_directory[$module] = $directory;
    }
    
    /**
     * 设置module路径
     * 
     * @param type $directory
     * @return \Star_Controller_Front 
     */
    public function setModuleDirectory($path)
    {
        if (is_string($path))
        {
            try{
                $dir = new DirectoryIterator($path);
            } catch(Exception $e) {
                throw new Star_Exception("Directory $path not readable", 500);
            }

            foreach ($dir as $file) 
            {
                if ($file->isDot() || !$file->isDir()) {
                    continue;
                }
                $module    = $file->getFilename();
                $directory = $file->getPathname() . DIRECTORY_SEPARATOR . $this->getModuleControllerDirectoryName();
                $this->addModuleControllerDirectory($directory, $module);
            }
        } else if (is_array($path)){
            foreach ($path as $module => $directory)
            {
                $this->addModuleControllerDirectory($directory, $module);
            }
        }
        return $this;
    }
    
    /**
     * 设置module controller目录名
     * 
     * @param type $name 
     */
    public function setModuleControllerDirectoryName($name = 'controllers')
    {
        $this->module_controller_directoryectory_name = $name;
    }
    
    /**
     * 返回module controller d
     * 
     * @return type 
     */
    public function getModuleControllerDirectoryName()
    {
        return $this->module_controller_directoryectory_name;
    }
    
    /**
     * 返回module controller目录
     * 
     * @param type $module
     * @return type 
     */
    public function getModuleControllerDirectory($module=null)
    {
        if ($module == null)
        {
            return $this->module_controller_directory;
        } 
        if (isset($this->module_controller_directory[$module]))
        {
            return $this->module_controller_directory[$module];
        }
        
        return null;
    }

    /**
     * 返回module name
     * 
     * @return type 
     */
    public function getModuleName()
    {
        return $this->module_name;
    }
    
    /**
     * 返回controller key
     * 
     * @return type 
     */
    public function getControllerKey()
    {
        return $this->controller_key;
    }
    
    /**
     * 返回action key
     * 
     * @return type 
     */
    public function getActionKey()
    {
        return $this->action_key;
    }

    /**
     * 设置默认controller
     * 
     * @param type $controller 
     */
	public function setDefaultControllerName($controller_name = 'index')
	{
        $this->default_controller_name = $controller_name;
        return $this;
	}
	
    /**
     * 设置默认action
     * 
     * @param type $action 
     */
	public function setDefaultActionName($action_name = 'index')
	{
        $this->default_action_name = $action_name;
        
        return $this;
	}
    
    /**
     * 设置module name
     * 
     * @param type $module_name
     * @return \Star_Controller_Front 
     */
    public function setModuleName($module_name)
    {
        !empty($module_name) && $this->module_name = $module_name;
        return $this;
    }
    
    /**
     * 设置controller key
     * 
     * @param type $controller_key
     * @return \Star_Controller_Front 
     */
    public function setControllerKey($controller_key)
    {
        //如果controller_key不为空，则修改controller_key
        if ($controller_key)
        {
            $this->controller_key = trim($controller_key);
        }
        
        return $this;
    }
    
    /**
     * 设置action key
     * 
     * @param type $action_key
     * @return \Star_Controller_Front 
     */
    public function setActionKey($action_key)
    {
        $action_key && $this->action_key = trim($action_key);
        
        return $this;
    }
    
    /**
     * 消息派遣 调用控制器
     * 
     * @return type 
     */
	public function dispatch()
	{
        header('Cache-Control: no-cache');
		header('Content-Type: text/html; charset=' . $this->view->getEncoding());
		ob_start();
        
        try{
            $class_name = $this->getControllerClass($this->request);
            $this->loadClass($class_name);
            $action_name = $this->request->getActionName();
            if (empty($action_name))
            {
                $action_name = $this->default_action_name;
                $this->request->setActionName($action_name);
            }

            $dir = dirname($this->getControllerDirectory()) . DIRECTORY_SEPARATOR . 'views';
            //设置view
            $this->view->setBasePath($dir)
                       ->setController($this->request->getControllerName())
                       ->setScriptName($this->request->getActionName())
                       ->setAction($this->request->getActionName());
            $action = strtolower($action_name) . ucfirst($this->action_key);
            $controller = new $class_name($this->request, $this->response, $this->view);            
            $controller->dispatch($action); //执行action
            call_user_func(array('Star_Model_Abstract', 'Close')); //主动关闭数据库链接
        } catch (Exception $e)
        {
            call_user_func(array('Star_Model_Abstract', 'Close')); //主动关闭数据库链接
            return $this->handleException($e);
        }
        
		ob_end_flush();
	}
    
    public function loadClass($controller)
    {
        $file_path = Star_Loader::getFilePath(array($this->getControllerDirectory(), $controller));

        if (Star_Loader::isExist($file_path) == false)
        {
            throw new Star_Exception("{$file_path} not found!", 404);
        }

        //文件是否可读
        if (!Star_Loader::isReadable($file_path))
        {
            throw new Star_Exception("Connot load controller calss {$controller} from file {$file_path}", 500);
        }
        
        require 'Star/Controller/Action.php';
        require $file_path;
        
        //类是否存在
        if (!class_exists($controller, false))
        {
            throw new Star_Exception("Invalid controller class ({$controller})", 404);
        }
    }
    
    /**
     * 返回controller类
     * 
     * @param type $request
     * @return type
     * @throws Star_Exception 
     */
    public function getControllerClass(Star_Http_Request $request)
    {
        $controller_name = $request->getControllerName();
        if (empty($controller_name))
        {
            $controller_name = $this->default_controller_name;
            $request->setControllerName($controller_name);
        }
        
        $module_name = $request->getModuleName();
        if (empty($module_name))
        {
            $module_name = $this->defualt_module;
        }
        
        if ($this->isValidModule($module_name))
        {
            $this->module_name = $module_name;
        } else{
            //TODO
        }
        
        $controller = ucfirst($controller_name) . ucfirst($this->controller_key);
        return $controller;
    }
    
    /**
     * 设置controller目录
     * 
     * @param type $path
     * @return \Star_Application 
     */
    public function setControllerDirectory($path)
    {
        $this->controller_directory = $path;
        
        return $this;
    }
    
    /**
     * 返回controller目录
     * 
     * @return type 
     */
    public function getControllerDirectory()
    {
        if ($this->controller_directory == null)
        {
            $module = $this->request->getModuleName();

            if ($this->isValidModule($module))
            {
                $this->controller_directory = $this->getModuleControllerDirectory($module);
            } else {
                $directory_name = Star_Loader::getLoadTypeByKey($this->getControllerkey());
                $this->controller_directory = Star_Loader::getModuleDirect($directory_name);
            }
        }
        
        return $this->controller_directory;
    }
    
    /**
     * 设置是否显示异常
     * 
     * @param type $flag
     * @return type 
     */
    protected function setDisplayExceptions($flag = false)
    {
        return $this->display_exceptions = $flag;
    }
    
    /**
     * 返回异常显示状态
     * 
     * @return type 
     */
    protected function getDisPlayExceptions()
    {
        return $this->display_exceptions;
    }

    /**
     * 路由
     * 
     * @param type $url 
     */
	protected function route()
	{
        $path = $this->request->getPathInfo();
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        $params = array();
		if (!empty($path))
		{	
            $value = array();
			$path = explode($this->url_delimiter, $path);
			if (!empty($path))
			{
                if ($this->isValidModule($path[0]))
                {
                    $module_name = array_shift($path);
                    $value[$this->module_key] = $module_name;
                }
                
                if (count($path) && !empty($path[0]))
                {
                    $controller_name = array_shift($path);
                    $value[$this->controller_key] = $controller_name;
                }
                
                 if (count($path) && !empty($path[0]))
                {
                    $action_name = array_shift($path);
                    $value[$this->action_key] = $action_name;
                }
                
				if (!empty($path))
                {
                    $count = count($path);
                    for ($i =0; $i<$count; $i = $i+2)
                    {
                        $params[$path[$i]] = $path[$i+1];
                    }
                }
                $params = $value + $params;
			}
		}
        return $params;
	}
    
    /**
     * 设置request params
     * 
     * @param type $params 
     */
    public function setRequestParam($params)
    {
        if (is_array($params))
        {
            foreach ($params as $key => $value)
            {
                switch ($key)
                {
                    case $this->module_key:
                        $this->request->setModuleName($value);
                        break;
                    case $this->controller_key:
                        $this->request->setControllerName($value);
                        break;
                    case $this->action_key:
                        $this->request->setActionName($value);
                        break;
                    default :
                        $this->request->setParam($key, $value);
                        break;
                }
            }
        }
    }

    /**
     * 处理异常
     * 
     * @param type $e
     * @return type 
     */
    public function handleException($e)
    {
        if ($e->getCode() == 404)
        {
            return header('Location: /404.html');
        }

        if ($this->getDisPlayExceptions() == true)
        {
            echo $e->__toString();
        }else{
            call_user_func(array('Star_Log', 'log'), $e->__toString());
        }
        
        if ($e->getCode() == 500)
        {
            //
        }
    }
}

?>