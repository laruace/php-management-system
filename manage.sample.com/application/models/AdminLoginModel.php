<?php
/**
 * admin_login Model
 * 
 * @author QinYang Zhang 
 */
class AdminLoginModel extends Star_Model_Abstract {
    
    protected $_name = 'admin_login';
            
    protected $_primary = 'login_id';

    /**
     * 返回登录日志列表
     * 
     * @param type $page
     * @param type $page_size
     * @param type $params
     * @return type 
     */
    public function getLoginLogByPage($page, $page_size, Array $params)
    {
        $select = $this->select();
        $select->from($this->getTableName() . ' AS l')
               ->joinLeft($this->getTableName('admin') . ' AS a', 'a.admin_id = l.admin_id', array('admin_name', 'username'))
               ->limitPage($page, $page_size)->order('login_id DESC');
        if (isset($params['start_time']) && $params['start_time'])
        {
            $select->where('l.add_time >= ?', $params['start_time']);
        }
        
        if (isset($params['end_time']) && $params['end_time'])
        {
            $select->where('l.add_time <= ?', $params['end_time']);
        }
        
        if (isset($params['username']) && $params['username'])
        {
            $select->where('a.username = "?"', $params['username']);
        }
        return $this->fetchAll($select);
    }
    
    /**
     * 返回登录日志数
     * 
     * @param type $params
     * @return type 
     */
    public function getLoginLogCount(Array $params)
    {
        $select = $this->select();
        $select->from($this->getTableName() . ' AS l', 'count(1) total')->joinLeft($this->getTableName('admin') . ' as a', 'a.admin_id = l.admin_id');
        if (isset($params['start_time']) && $params['start_time'])
        {
            $select->where('l.add_time >= ?', $params['start_time']);
        }
        
        if (isset($params['end_time']) && $params['end_time'])
        {
            $select->where('l.add_time <= ?', $params['end_time']);
        }
        
        if (isset($params['username']) && $params['username'])
        {
            $select->where('a.username = "?"', $params['username']);
        }
        return $this->fetchOne($select);
    }
    
}

?>
