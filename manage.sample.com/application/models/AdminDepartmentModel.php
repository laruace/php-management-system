<?php
/**
 * admin_department Model
 * 
 * @author QinYang Zhang 
 */
class AdminDepartmentModel extends Star_Model_Abstract {
    
    protected $_name = 'admin_department';
            
    protected $_primary = 'department_id';

    /**
     * 返回所有部门
     * 
     * @return type 
     */
    public function getAllDepartment()
    {
        return $this->fetchAll('1 = 1', '*', $this->getTableName(), 'sort ASC department_id DESC');
    }

    /**
     * 返回部门列表
     * 
     * @param type $page
     * @param type $page_size
     * @param type $params
     * @return type 
     */
    public function getDepartmentByPage($page, $page_size, $params = array())
    {
        $select = $this->select();
        $select->from($this->getTableName())->order('sort ASC')->limitPage($page, $page_size);
        return $this->fetchAll($select);
    }
    
    /**
     * 返回部门数量
     * 
     * @param type $params
     * @return type 
     */
    public function getDepartmentCount($params = array())
    {
        $select = $this->select();
        $select->from($this->getTableName(), 'count(1)');
        return $this->fetchOne($select);
    }
    
}

?>
