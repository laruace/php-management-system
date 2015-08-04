<?php
/**
 * @package library\Star\Model\Mysqli
 */

/**
 * 导入文件
 */
require_once 'Star/Model/Interface.php';
require_once 'Star/Model/Pdo/Mysql/Select.php';

/**
 * 数据模型 基类
 * 
 * @package library\Star\Model\Pdo\Mysql
 * @author zhangqinyang
 *
 */
class Star_Model_Pdo_Mysql_Abstract implements Star_Model_Interface
{
	protected $db;
	
	protected $default_charset = 'utf8';
	
    protected $slow_query_log = false; //默认关闭慢查询日志

    protected $slow_query_time = 2; //慢查询默认时间

    protected $statement = null;
	
    /**
     * 构造方法
     * @param unknown $config
     */
	public function __construct($config)
	{
		$this->init($config);
	}
    
	/**
	 * 初始化
	 * @param unknown $config
	 */
    protected function init($config)
    {
        if (isset($config['slow_query_log']))
        {
            $this->slow_query_log = $config['slow_query_log'];
        }
        
        if (isset($config['slow_query_time']) && !empty($config['slow_query_time']))
        {
            $this->slow_query_time = $config['slow_query_time'];
        }
        
        $this->connect($config);
    }

    /**
     * 连接mysql数据库
     * @see Star_Model_Interface::connect()
     */
    public function connect($db)
	{
        if (!extension_loaded('pdo_mysql'))
        {
            throw new Star_Exception('The PDO_Mysql extension is required for this adapter but the extension is not loaded');
        }
        
		extract($db);
        !isset($port) && $port = 3306; 
		$this->db = new PDO("mysql:host={$host};port={$port};dbname={$dbname};", $username, $password, array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->default_charset,
            PDO::ATTR_ERRMODE =>  PDO::ERRMODE_EXCEPTION,
        ));
        
		if ($this->db->errorCode() !== '00000')
		{
			throw new Star_Exception('PDO Mysql connet error:(' . $this->db->errorCode() . ')' . $this->db->errorInfo(), 500);
		}
	}
	
	/**
	 * 开始事务执行
	 */
	public function beginTransaction()
	{
		$this->db->beginTransaction();
	}
	
	/**
	 * 插入数据
	 * @param $data
	 */
	public function insert($table, Array $data)
	{
		$columns = array_keys($data); //表字段
		$columns = $this->quoteIdentifier($columns);
        $count = count($data);
        $params = array_values($data);
		$sql = 'INSERT INTO ' . $this->quoteIdentifier($table) . '(' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, $count, '?')) . ')';
		$this->_query($sql, $params);
		return $this->db->lastInsertId();
	}
	
	/**
	 * 更新数据
	 *
	 * @param $where
	 * @param $data
	 */
	public function update($table, $where, Array $data, $quote_indentifier = true)
	{
		$params = array();
		foreach ($data as $column => &$value)
		{
            if ($quote_indentifier === true)
            {
                $params[] = $value;
            }
            $value = $this->quoteIdentifier($column) . ' = ' .  ($quote_indentifier === true ? '?' : $value);
		}
		
		$sql = 'UPDATE ' . $this->quoteIdentifier($table) . ' SET ' . implode(',', $data) . ' WHERE ' . $where;
		$stmt = $this->_query($sql, $params);
		return $stmt->rowCount();
		
	}
	
	/**
	 * 删除数据
	 * @param $where
	 */
	public function delete($table, $where)
	{
		$sql = 'DELETE FROM ' . $table . ' WHERE ' . $where;
		$stmt = $this->_query($sql);
		return $stmt->rowCount();
	}
    
    /**
     * 根据条件生成SQL
     * 
     * @param type $where
     * @param type $conditions
     * @param type $table
     * @param type $order
     * @param type $page
     * @param type $page_size
     * @return type 
     */
    protected function getSql($where, $conditions, $table, $order = null, $page = 1, $page_size = 1)
    {
        $select = $this->select();
        $select->from($table, $conditions)->where($where);
        
        if ($order != null)
        {
            $select->order($order);
        }
        
        if ($page != null && $page_size != null)
        {
            $select->limitPage($page, $page_size);
        }
        
        return $select->assemble();
    }


    /**
	 * 返回结果集
	 * @param $select
	 */
	public function fetchAll($where, $conditions = null, $table = null, $order = null, $page = null, $page_size = null)
	{
		$stmt = $this->_fetch($where, $conditions, $table, $order, $page, $page_size);
		return $stmt->fetchAll();
	}
	
	/**
	 * 返回结果
	 * @param $id
	 */
	public function fetchOne($where, $conditions = null, $table = null, $order = null)
	{
		if ($where instanceof Star_Model_Pdo_Mysql_Select)
		{
			$where->limit(1);
		}
        
        $stmt = $this->_fetch($where, $conditions, $table, $order);
		return $stmt->fetchColumn();
	}
	
	/**
	 * 返回一行结果集
	 * @param $select
	 */
	public function fetchRow($where, $conditions = null , $table = null)
	{
		if ($where instanceof Star_Model_Pdo_Mysql_Select)
		{
			$where->limit(1);
		} 
        $stmt = $this->_fetch($where, $conditions, $table);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function fetchCol($where, $conditions = null , $table = null)
	{  
		$stmt = $this->_fetch($where, $conditions, $table);
        $data = array();
		while ($rs = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$data[] = $rs[0];
		}
		return $data;
	}
    
    public function query($sql)
    {
        $stmt = $this->_query($sql);
        if ($this->isSelect($sql) == true)
        {
            return $stmt->fetchAll();
        } else {
            return $stmt->rowCount();
        }
    }
    
    /**
     * 判断是否是查询sql
     * 
     * @param type $sql 
     */
    public function isSelect($sql)
    {
        $sql = trim($sql);
        if (strtoupper(substr($sql, 0, 6)) == 'SELECT')
        {
            return true;
        } else
        {
            return false;
        }
    }


    /**
	 * 返回影响行数
	 */
	public function rowCount()
	{
		return $this->db->affected_rows;
	}
	
    public function _fetch($where, $conditions = null, $table = null, $order = null, $page = null, $page_size = null)
    {
        if ($where instanceof Star_Model_Pdo_Mysql_Select)
		{
			$sql_info = $where->assemble();
		} else {
            $sql_info = $this->getSql($where, $conditions, $table, $order, $page, $page_size);
        }
        
        $sql = $sql_info['sql'];
        $params = $sql_info['params'];
		$stmt = $this->_query($sql, $params);
        return $stmt;
    }
    
	/**
	 * sql query
	 * @param $sql
	 */
	public function _query($sql, $params = array())
	{
        if ($this->slow_query_log == true)
        {
            $start_time = time();
        }

		$stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        if ($stmt->errorCode() !== '00000')
        {
            $error_info = $stmt->errorInfo();
            $sql = $this->getCompeleSql($sql, $params);
            throw new Star_Exception("SQL: ". $sql . " \nError Message:" . $error_info[2], 500);
        }

        if($this->slow_query_log == true)
        {
            $end_time = time();

            $time = $end_time - $start_time;

            if ($time >= $this->slow_query_time) //慢查询日志
            {
                $stact_trace = Star_Debug::Trace(); //返回堆栈详细信息
                $stact_trace = implode("\n", $stact_trace);
                $sql = $this->getCompeleSql($sql, $params);
                Star_Log::log("Query_time: {$time}s     Slow query: {$sql} \nStack trace:\n{$stact_trace}", 'slow_query'); //记录慢查询日志
            }
        }
        return $stmt;
	}
    
    /**
     * 返回完整的执行SQL语句
     * 
     * @param type $sql
     * @param array $params
     * @return type
     */
    protected function getCompeleSql($sql, Array $params)
    {
        $sql_len = strlen($sql);
        $identifers = array();
        for ($i = 0; $i < $sql_len; $i++)
        {
            if ($sql[$i] == '?')
            {
                $identifers[] = $i;
            }
        }
        
        if (!empty($identifers) && count($params) == count($identifers))
        {
            $params = array_reverse($params);
            $identifers = array_reverse($identifers);
            foreach ($identifers as $key => $value)
            {
                $sql = substr_replace($sql, is_numeric($params[$key]) ? $params[$key] : '"'. $this->disposeQuote($params[$key]) . '"', $value, 1);
            }
            
        }
        return $sql;
    }
    
    public function select()
	{
		return new Star_Model_Pdo_Mysql_Select();
	}
	
	public function close()
	{
		$this->db = null;
	}
	
	/**
	 * 字符串引号
	 *
	 * @param $identifier
	 * @param $auto_quote
	 */
	public function quoteIdentifier($identifier, $auto_quote = false)
	{
		if (is_array($identifier))
		{
			foreach ($identifier as &$value)
			{
				if ($auto_quote == true)
				{
					$value = "'" . addslashes($value) . "'";
				} else
				{
					$value = '`' . $value . '`';
				}
			}
		} else
		{
			if ($auto_quote == true)
			{
				$identifier = "'" . addslashes($identifier) . "'";
			} else
			{
				$identifier = '`' . $identifier . '`';
			}
		}
		
		return $identifier;
	}
	
    /**
     * 数据去除反斜杠
     * @param type $data
     * @return $data
     */
	protected function deepStripslashes($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $value)
			{
				$data[$key] = $this->deepStripslashes($value);
			}
		} else if (is_string($data))
		{
			$data = stripcslashes($data);
		}
		
		return $data;
	}
	
	/**
	 * 提交事务
	 * @return boolean
	 */
	public function commit()
	{
		return $this->db->commit();
	}
	
	/**
	 * mysql操作回滚
	 * @return boolean
	 */
	public function rollback()
	{
		return $this->db->rollback();
	}

}
?>