<?php
/**
 * @package library\Star
 */

/**
 * 加载类
 *
 * @package library\Star
 * @author zhangqy
 *
 */
class Star_Loader {

	protected static $app_path = null;
	protected static $library_path = null;
    protected static $autoload_types = array(
		'service' => 'services',
		'controller' => 'controllers',
		'model' => 'models',
	);
	
	public function __construct()
	{
		
	}
	
	public function setApplicationPath($app_path)
	{
		self::$app_path = $app_path;
		return $this;
	}
	
    /**
     * 加载文件
     * 
     * @param type $file_name
     * @param type $dir_path
     * @param type $is_once
     * @return type 
     */
	public function loadFile($file_name, $dir_path, $is_once = false)
	{
		$file_path = self::getFilePath(array($dir_path, $file_name));
		
		if (!is_file($file_path))
		{
			trigger_error(__METHOD__ . ' not found file ' . $file_path, E_USER_ERROR);
            
            return ;
		}
		
		if ($is_once == true)
		{
			require_once $file_path;
		} else
		{
			require $file_path;
		}
	}
	
	public function loadClass($class, $dir_path)
	{
		return $class;
	}
	
    /**
     * 添加允许自动加载类别
     * 
     * @param type $key
     * @param type $value
     * @return type 
     */
	public static function addAutoloadType($key, $value)
	{    
		return self::$autoload_types[$key] = $value;
	}
	
    public static function getLoadTypeByKey($key)
    {
        $key = strtolower($key);
        return self::$autoload_types[$key];
    }
    
	/**
	 * 自动加载
	 *
	 * @param $class_name
	 */
	public function autoload($class_name)
	{
		if ($this->isClassLoader($class_name) == true)
		{
			return ;
		}

		if (strtolower(substr($class_name, 0, 4)) == 'star')
		{
			$class_path = self::getClassPath($class_name);
			
			if ($class_path !== false)
			{
                if (!file_exists($class_path))
                {
                    trigger_error('Not found file ' . $class_path, E_USER_ERROR);
                    return;
                }
                
				require $class_path;
                return ;
			}
		}
        
        $autoload_types = self::$autoload_types;
        
        if (!empty($autoload_types) && is_array($autoload_types))
		{
			foreach ($autoload_types as $key => $dir_name)
			{
				$class_type = strtolower(substr($class_name, - strlen($key)));
				
				if ($key == strtolower($class_type))
				{
					$dir_path = self::getDirPath(array(self::$app_path, $dir_name));
                    
					return self::loadFile($class_name, $dir_path);
				}
			}
		}
        
        $library_class_path = self::getFilePath(array(self::$library_path, $class_name));
        //library目录下，则自动加载
        if (file_exists($library_class_path))
        {
            require $library_class_path;
            return ;
        }
	}
	
    /**
     * 根据类名返回自动加载地址
     * 
     * @param type $class
     * @return string 
     */
	public function getClassPath($class)
	{
		$segments = explode('_', $class);
		
		if (count($segments) < 2)
		{
			return false;
		}
		
		$library_path = self::getLibraryPath(); //返回Star框架路径
		$class_path =self::getFilePath($segments); //返回类路径
		if (!empty($library_path))
		{
			$file_path = self::getDirPath(array($library_path, $class_path));
		} else
		{
			$file_path = $class_path;
		}

		return $file_path;
	}
	
	protected function loadFramework($class_name)
	{
		
	}
	
	public function __call($calss_name, $args)
	{
		return ;
	}
	
	/**
	 * 判断类是否加载
	 *
	 * @param $class_name
	 * @return bool
	 */
	protected function isClassLoader($class_name)
	{
		return class_exists($class_name);
	}
	
	public static function isReadable($path)
	{
		if (is_readable($path))
		{
			return true;
		} else
		{
			return false;
		}
	}
	
	public function setLibraryPath($path)
	{
		if (!empty($path))
		{
			self::$library_path = $path;
		}
		
		return $this;
	}
	
	public function getLibraryPath()
	{
		return self::$library_path;
	}
    
    public static function isExist($file_path)
    {
        return file_exists($file_path) ? true : false;
    }


    /**
     * 返回文件路径
     * 
     * @param array $segments 路径片段
     * @param postfix 后缀
     * @return type  
     */
    public static function getFilePath(array $segments, $postfix = '.php', $dir_separator = DIRECTORY_SEPARATOR)
    {
        $path = implode($dir_separator, $segments);
        return $path . $postfix;
    }
    
    /**
     * 返回文件目录
     * 
     * @param array $segments
     * @return type 
     */
    public static function getDirPath(array $segments, $dir_separator = DIRECTORY_SEPARATOR)
    {
        $path = implode($dir_separator, $segments);
        return $path;
    }
    
    /**
     * 返回模块目录
     * 
     * @param type $directory_name
     * @return type 
     */
    public static function getModuleDirect($directory_name)
    {
        return self::getDirPath(array(self::$app_path, $directory_name));
    }
}

?>