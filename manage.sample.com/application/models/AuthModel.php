<?php
/**
 * Auth Model
 * 
 * @author QinYang Zhang 
 */
class AuthModel extends Star_Model_Abstract {
    
    protected $_name = 'auth';

    protected $_primary = 'auth_id';

    /**
     * 返回权限信息
     * 
     * @param type $auth_id
     * @return type 
     */
    public function getAuthById($auth_id)
    {
        return $this->getPk($auth_id);
    }

    /**
     * 返回管理员权限
     * 
     * @param type $admin_id
     * @return type 
     */
    public function getAuthByAdmin($admin_id)
    {
        $select = $this->select();
        $select->from($this->getTableName() . ' AS a')
               ->where('admin_id = ?', (int) $admin_id)
               ->joinInner($this->getTableName('admin_menu') . ' AS m', 'a.menu_id = m.menu_id', array('menu_name', 'controller', 'action', 'is_show', 'parent_id', 'top_id', 'menu_level', 'sort'));
        return $this->fetchAll($select);
    }
    
    /**
     * 返回部门权限
     * 
     * @param type $department_id
     * @return type 
     */
    public function getAuthByDepartment($department_id)
    {
        $select = $this->select();
        $select->from($this->getTableName() . ' AS a')
               ->where('department_id = ?', (int) $department_id)
               ->joinInner($this->getTableName('admin_menu') . ' AS m', 'a.menu_id = m.menu_id', array('menu_name', 'controller', 'action', 'is_show', 'parent_id', 'top_id', 'menu_level', 'sort'));
        return $this->fetchAll($select);
    }
    
    /**
     * 返回部门权限菜单ＩＤ
     * 
     * @param type $department_id
     * @return type 
     */
    public function getAuthMenuIdsByDepartment($department_id)
    {
        $select = $this->select();
        $select->from($this->getTableName('admin_menu'), array('menu_id'))->where('department_id = ?', $department_id);
        return $this->fetchCol($select);
    }
    
    /**
     * 返回管理员权限ID
     * 
     * @param type $admin_id
     * @return type 
     */
    public function getAuthMenuIdsByAdmin($admin_id)
    {
        $select = $this->select();
        $select->from($this->getTableName(), array('menu_id'))->where('admin_id = ?', $admin_id);
        return $this->fetchCol($select);
    }
}

?>