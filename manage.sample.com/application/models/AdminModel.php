<?php
/**
 * admin Model
 * 
 * @author QinYang Zhang 
 */
class AdminModel extends Star_Model_Abstract {
    
    protected $_name = 'admin';
            
    protected $_primary = 'admin_id';
    
    /**
     * 根据用户名返回管理员信息
     * 
     * @param type $username
     * @return type 
     */
    public function getAdminByUsername($username)
    {
        $select = $this->select();
        $select->from($this->getTableName())->where('username = "?"', $username);
        return $this->fetchRow($select);
    }
    
    /**
     * 根据ID返回管理员信息
     * 
     * @param type $admin_id
     * @return type 
     */
    public function getAdminById($admin_id)
    {
        return $this->getPk($admin_id);
    }
    
    /**
     * 返回管理员列表
     * 
     * @param type $page
     * @param type $page_size
     * @param type $params
     * @return type 
     */
    public function getAdminByPage($page, $page_size, $params = array())
    {
        $select = $this->select();
        $select->from($this->getTableName())->limitPage($page, $page_size);
        if (isset($params['admin_name']) && $params['admin_name'])
        {
            $select->where('admin_name = ?', $params['admin_name']);
        }
        
        if (isset($params['department_id']) && $params['department_id'])
        {
            $select->where('department_id = ?', $params['department_id']);
        }
        
        if (isset($params['username']) && $params['username'])
        {
            $select->where('username = ?', $params['username']);
        }
        return $this->fetchAll($select);
    }
    
    /**
     * 返回管理员数量
     * 
     * @param type $params
     * @return type 
     */
    public function getAdminCount($params)
    {
        $select = $this->select();
        $select->from($this->getTableName(), 'count(1)');
        if (isset($params['admin_name']) && $params['admin_name'])
        {
            $select->where('admin_name = ?', $params['admin_name']);
        }
        
        if (isset($params['department_id']) && $params['department_id'])
        {
            $select->where('department_id = ?', $params['department_id']);
        }
        
        if (isset($params['username']) && $params['username'])
        {
            $select->where('username = ?', $params['username']);
        }
        return $this->fetchOne($select);
    }
    
}

?>
