<?php
/**
 * admin_menu Model
 * 
 * @author QinYang Zhang 
 */
class AdminMenuModel extends Star_Model_Abstract {
    
    protected $_name = 'admin_menu';
            
    protected $_primary = 'menu_id';

    /**
     * 返回所有菜单
     * 
     * @return type 
     */
    public function getAllMenu()
    {
        $select = $this->select();
        $select->from($this->getTableName())->order('parent_id ASC, sort ASC');
        return $this->fetchAll($select);
    }
    
    /**
     * 返回顶级菜单
     * 
     * @return type 
     */
    public function getTopMenu()
    {
        $select = $this->select();
        $select->from($this->getTableName())->where('parent_id = 0')->order('sort ASC');
        return $this->fetchAll($select);
    }
    
    /**
     * 返回菜单列表
     * 
     * @param type $page
     * @param type $page_size
     * @param type $params
     * @return type 
     */
    public function getMenuByPage($page, $page_size, $params = array())
    {
        $select = $this->select();
        $select->from($this->getTableName())->order('sort ASC')->limitPage($page, $page_size)->order('menu_id DESC');
        if (isset($params['top_id']) && $params['top_id'])
        {
            $select->where('top_id = ?', (int) $params['top_id']);
        }
        
        if (isset($params['is_show']))
        {
            $select->where('is_show = ?', (int) $params['is_show']);
        }
        return $this->fetchAll($select);
    }
    
    /**
     * 返回菜单数量
     * 
     * @param type $params
     * @return type 
     */
    public function getMenuCount($params = array())
    {
        $select = $this->select();
        $select->from($this->getTableName(), 'count(1)');
        if (isset($params['top_id']) && $params['top_id'])
        {
            $select->where('top_id = ?', (int) $params['top_id']);
        }
        
        if (isset($params['is_show']))
        {
            $select->where('is_show = ?', (int) $params['is_show']);
        }
        return $this->fetchOne($select);
    }
    
    /**
     * 返回子菜单
     * 
     * @param type $menu_id
     * @return type 
     */
    public function getMenuByParent($menu_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())->order('sort ASC')->where('parent_id = ?', (int) $menu_id);
        return $this->fetchAll($select);
    }
    
    /**
     * 返回一级菜单所有子菜单
     * 
     * @param type $menu_id
     * @return type 
     */
    public function getMenuByTop($menu_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())->where('top_id = ?', (int) $menu_id);
        return $this->fetchAll($select);
    }
}

?>
