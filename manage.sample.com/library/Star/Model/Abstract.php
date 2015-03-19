<?php
/**
 * @package library\Star\Model
 */

/**
 *
 * @package library\Star\Model
 * @author qinyang.zhang 2010/05/27
 */
abstract class Star_Model_Abstract
{
	const ADAPTER = 'db';
	
	const SLAVE_ADAPTER = 'slave_db';
	
	protected $_primary = null; //主键
	
	protected static $_prefix = null; //表前缀
	
	protected static $_config = null; //配置
	
	protected static $_db = null;
	
	protected $_name = null; //表名称
	
	protected static $_default_db = null;
	
	protected $_support_db = array('mysqli', 'mysql');
	
	public function __construct($config = array())
	{
		if (is_string($config))
		{
			$config = array(self::ADAPTER => $config);
		}
		
		if (!empty($config) && is_array($config))
		{
			$this->setOptions($config);
		}

		//$this->_setup();
	}
	
	private function _setup()
	{
		$this->setAdapter($this->getSlaveDbOption());

		if (self::$_db === null)
		{
            if (self::$_default_db == null)
            {
                $config = self::$_config;
                $this->setDefaultAdapter($config);
            }
			self::$_db = & self::$_default_db;
		}
	}
    
    /**
     * 设置表前缀
     * @param $prefix
     * @return \Star_Model_Abstract 
     */
    protected function setPrefix($prefix)
    {
        self::$_prefix = $prefix;
    }

    /**
     * 返回表前缀
     * 
     * @return type 
     */
    public function getPrefix()
    {
        return self::$_prefix;
    }


    /**
     * 返回从库配置
     * 
     * @return array 
     */
    protected function getSlaveDbOption()
	{
        $option = array();
		$config = self::$_config;

        if (!isset($config[self::SLAVE_ADAPTER]))
        {
            return $option;
        }
		
		$slave_options = $config[self::SLAVE_ADAPTER];

		if ($config['multi_slave_db'] == true && !empty($slave_options))
		{
			$option = $slave_options[array_rand($slave_options)];
		} else
		{
			$option = $slave_options;
		}

		return $option;
	}
	
	/**
     * 插入数据
     * 
     * @param array $data
     * @param type $table
     * @return type 
     */
	public function insert(array $data, $table = null)
	{
        empty($table) && $table = $this->getTableName();
		return $this->getDefaultAdapter()->insert($this->getTableName(), $data);
	}

	/**
     * 更新数据
     * 
     * @param type $where
     * @param array $data
     * @param type $table
     * @param type $quote_indentifier
     * @return type 
     */
	public function update($where, Array $data, $table = null, $quote_indentifier = true)
	{
        empty($table) && $table = $this->getTableName();
		$where = $this->setWhere($where);
		return $this->getDefaultAdapter()->update($table, $where, $data, $quote_indentifier);
	}
	
	/**
     * 删除数据
     * 
     * @param type $where
     * @param type $table
     * @return type 
     */
	public function delete($where, $table = null)
	{
        empty($table) && $table = $this->getTableName();
		$where = $this->setWhere($where);
		return $this->getDefaultAdapter()->delete($table, $where);
	}
	
	public function query($sql)
	{
        if ($this->getAdapter()->isSelect() == true)
        {
            return $this->getAdapter()->query($sql);
        } else{
            return $this->getDefaultAdapter()->query($sql);
        }
	}
    
    /**
     * 返回相对应主键信息
     * 
     * @param type $pk_id
     * @param type $table
     * @return type 
     */
    public function getPk($pk_id, $table = null)
    {
        empty($table) && $table = $this->getTableName();
        $where = $this->setWhere($pk_id);
        return $this->getAdapter()->fetchRow($where, '*', $table);
    }
	
    /**
     *
     * @param type $where
     * @param null $conditions
     * @param null $table
     * @param null $order
     * @return type 
     */
	public function fetchOne($where, $conditions = '*', $table = null, $order = null)
	{
		return $this->getAdapter()->fetchOne($where, $conditions, $table, $order);
	}
	
    /**
     *
     * @param type $where
     * @param null $conditions
     * @param null $table
     * @param null $order
     * @param null $page
     * @param null $page_size
     * @return type 
     */
	public function fetchAll($where, $conditions = '*', $table = null, $order = null, $page = null, $page_size = null)
	{
		return $this->getAdapter()->fetchAll($where, $conditions, $table, $order, $page, $page_size);
	}
	
    /**
     *
     * @param type $where
     * @param null $conditions
     * @param null $table
     * @return type 
     */
	public function fetchRow($where, $conditions = '*' , $table = null)
	{
		return $this->getAdapter()->fetchRow($where, $conditions , $table);
	}
	
    /**
     *
     * @param type $where
     * @param null $conditions
     * @param null $table
     * @return type 
     */
	public function fetchCol($where, $conditions = '*', $table = null, $order = null, $page = null, $page_size = null)
	{
		$data = $this->getAdapter()->fetchCol($where, $conditions = '*', $table = null, $order = null, $page = null, $page_size = null);
		return $data;
	}
	
	public function select()
	{ 
		return $this->getAdapter()->select();
	}
	
	/**
     * 返回表名
     * 
     * @param type $name
     * @return type 
     */
	public function getTableName($name='')
	{
		$prefix = $this->getPrefix();
        return empty($name) ? $prefix . $this->_name : $prefix . $name;
	}
	
    /**
     * 关闭数据库连接 
     */
	public static function Close()
	{
		if (self::$_db !== null && self::$_db !== self::$_default_db)
		{
			self::$_db->close();
		}

		if (self::$_default_db !== null)
		{
			self::$_default_db->close();
		}
	}
	
    /**
     * 设置适配器
     * 
     * @param type $db
     * @return null|\adapter 
     */
	protected function setupAdapter($db)
	{
		if ($db == null || !is_array($db))
		{
			return null;
		}

		if (is_array($db) && in_array(strtolower($db['adapter']), $this->_support_db))
		{
			$adapter = ucfirst($db['adapter']);
            require_once "Star/Model/{$adapter}/Abstract.php";
			$adapter = 'Star_Model_' . $adapter . '_Abstract';
			return new $adapter($db['params']);
		}
	}
	
	public function setAdapter($db)
	{
		if (empty($db) || !is_array($db))
		{
			return $db;
		}
		
		return self::$_db = self::setupAdapter($db);
	}
	
	public function getAdapter()
	{
        if (self::$_db == null)
        {
            $this->_setup();
        }
        
		return self::$_db;
	}
	
	public function setOptions(Array $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				CASE self::ADAPTER:
					$this->setDefaultAdapter($value);
					break;
				CASE self::SLAVE_ADAPTER :
					$this->setAdapter($value);
					break;
                default :
                    break;
			}
		}
	}
	
	public static function setting($db)
	{
        //表前缀
        if (isset($db['prefix']))
        {
            self::$_prefix = $db['prefix'];
        }
        
		self::$_config = $db;
	}
	
	public function getDefaultAdapter()
	{
        if (self::$_default_db == null)
        {
            $config = self::$_config;
            $this->setDefaultAdapter($config);
        }
        
		return self::$_default_db; 
	}
	
	/**
	 * 设置默认适配器
	 *
	 * @param  $db
	 */
	public function setDefaultAdapter($db)
	{
        if (self::$_default_db == null)
        {
            self::$_default_db = self::setupAdapter($db);
        }
		return self::$_default_db;
	}
	
	/**
     * object to array
     * return array
     */
    public function objectToArray($object, $fields = array(), $flag=false)
    {
    	$data = get_object_vars($object);
    	$arr = array();
    	foreach ($data as $key => $value)
    	{
    		if (strtoupper($key)==strtoupper($this->_primary))
    		{
    			continue;
    		}
    		if (!empty($fields))
    		{
    			if (in_array($key, $fields))
    			{
    				$arr[$key] = $value;
    			}
    		} else
    		{
    			$arr[$key] = $value;
    		}
    	}
    	unset($data);
    	if ($flag == true)
    	{
    		$arr = array_change_key_case($arr, CASE_UPPER);
    	}
    	return $arr;
    }
    
    private function setWhere($where)
    {
    	if (is_numeric($where))
    	{
    		$where = $this->_primary . ' = ' . $where;
    	}
    	
    	if (is_array($where))
    	{
    		$where = $this->_primary . ' IN (' . implode(',', $where) . ')';
    	}
    	
    	return $where;
    }
    
    /**
	 * 开始事务执行
	 */
	public function beginTransaction()
	{
		$this->getDefaultAdapter()->beginTransaction();
	}
    
    /**
	 * mysql操作回滚
	 * @return boolean
	 */
	public function rollback()
	{
		return $this->getDefaultAdapter()->rollback();
	}
    
    /**
	 * 提交事务
	 * @return boolean
	 */
	public function commit()
	{
		return $this->getDefaultAdapter()->commit();
	}
    
    /**
     * 返回分表表名
     * 
     * @param type $hash
     * @return string 
     */
    public function getHashTableName($hash = null)
    {
        $table = $this->getTableName();
        
        if (empty($hash))
        {
            $hash = date('Ym', $_SERVER['REQUEST_TIME']);
        }
        
        $hash_table = $table . '_' . $hash;
        return $hash_table;
    }
}

?>