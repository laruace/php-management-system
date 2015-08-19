<?php

class SystemController extends Star_Controller_Action
{
    public function init()
	{
		
	}
	
    /**
     * 部门管理 
     */
    public function departmentmanageAction()
    {
        $request = $this->getRequest();
        $page = (int) $request->getParam('page');
        $page_size = 20;
        $params = array();
        $admin_service = new AdminService();
        $department_data = $admin_service->getDepartmentByPage($page, $page_size, $params);
        $page = $department_data['page'];
        $department_list = $department_data['department_list'];
        $this->view->assign(array(
            'page' => $page,
            'department_list' => $department_list,
        ));
    }
    
    /**
     * 添加部门 
     */
    public function adddepartmentAction()
    {
        $request = $this->getRequest();
        $admin_service = new AdminService();
        if ($request->isPost())
        {
            $department_name = Star_String::escape($request->getParam('department_name'));
            if (empty($department_name))
            {
                return $this->showWarning('对不起，部门名称不能为空。');
            }
            
            $department_data = array(
                'department_name' => $department_name,
                'sort' => (int) $request->getParam('sort'),
                'is_show' => (int) $request->getParam('is_show'),
                'add_time' => time(),
                'update_time' => time(),
            );
            $department_id = $admin_service->insertDepartment($department_data);
            if ($department_id)
            {
                $menu_ids = $request->getParam('menu_ids');
                if (!empty($menu_ids))
                {
                    $menu_ids = array_unique($menu_ids);
                    foreach ($menu_ids as $menu_id)
                    {
                        $auth_data = array(
                            'menu_id' => (int) $menu_id,
                            'department_id' => $department_id,
                            'admin_id' => 0,
                            'add_time' => time(),
                            'update_time' => time(),
                        );
                        //添加权限
                        $admin_service->insertAuth($auth_data);
                    }
                }
                return $this->showMessage('恭喜您，添加部门成功。', '/system/departmentmanage');
            } else
            {
                return $this->showWarning('对不起，添加部门失败。');
            }
        }
        $menus = $admin_service->getAllSortMenu();
        $this->view->assign('menus', $menus);
        $this->view->assign('department', array());
        $this->render('department_info');
    }
    
    /**
     * 修改部门 
     */
    public function editdepartmentAction()
    {
        $request = $this->getRequest();
        $department_id = (int) $request->getParam('department_id');
        $admin_service = new AdminService();
        $department_info = $admin_service->getDepartmentById($department_id);

        if (empty($department_info))
        {
            return $this->showWarning('对不起，部门不存在。', '/system/departmentmanage');
        }
        if ($request->isPost())
        {
            $department_name = Star_String::escape($request->getParam('department_name'));
            
            if (empty($department_name))
            {
                return $this->showWarning('对不起，部门名称不能为空。');
            }
            $department_data = array(
                'department_name' => $department_name,
                'sort' => (int) $request->getParam('sort'),
                'is_show' => (int) $request->getParam('is_show'),
                'update_time' => time(),
            );
            $rs = $admin_service->updateDepartment($department_id, $department_data);
            if ($rs)
            {
                $admin_service->deleteAuthByDepartment($department_id); //删除部门权限
                $menu_ids = $request->getParam('menu_ids');
                if (!empty($menu_ids))
                {
                    $menu_ids = array_unique($menu_ids);
                    foreach ($menu_ids as $menu_id)
                    {
                        $auth_data = array(
                            'menu_id' => (int) $menu_id,
                            'department_id' => $department_id,
                            'admin_id' => 0,
                            'add_time' => time(),
                            'update_time' => time(),
                        );
                        //添加权限
                        $admin_service->insertAuth($auth_data);
                    }
                }
                return $this->showMessage('恭喜您，成功修改部门。');
            } else
            {
                return $this->showWarning('对不起，修改部门失败。');
            }
        }
        
        $menus = $admin_service->getAllSortMenu(); //返回所有菜单
        $auth_options = $admin_service->getDepartmentAuthOption($department_id); //返回部门所有权限
        $this->view->assign(array(
            'department' => $department_info, //部门信息
            'menus' => $menus, //所有菜单
            'auth_options' => $auth_options, //部门权限信息
        ));
        return $this->render('department_info');
    }

    /**
     * 删除部门 
     */
    public function deletedepartmentAction()
    {
        $request = $this->getRequest();
        $department_id = (int) $request->getParam('department_id');
        $admin_service = new AdminService();
        $department_info = $admin_service->getDepartmentById($department_id);
        
        if (empty($department_info))
        {
            return $this->showWarning('对不起，部门不存在。');
        }
        $rs = $admin_service->deleteDepartment($department_id);
        if ($rs)
        {
            return $this->showMessage('恭喜您，删除成功。', '/system/departmentmanage');
        } else
        {
            return $this->showWarning('对不起，删除失败。');
        }
        
    }
    
    /**
     * 管理员管理 
     */
	public function adminmanageAction()
    {
        $request = $this->getRequest();
        $page = (int) $request->getParam('page');
        $page_size = 20;
        $params = array(
            'department_id' => (int) $request->getParam('department_id'),
            'admin_name' => Star_String::escape($request->getParam('admin_name')),
        );
        $admin_service = new AdminService();
        $admin_data = $admin_service->getAdminByPage($page, $page_size, $params);
        $departments = $admin_service->getDepartmentOption();
        $page = $admin_data['page'];
        $admin_list = $admin_data['admin_list'];
        
        if (!empty($admin_list))
        {
            foreach ($admin_list as & $admin)
            {
                $department_id = $admin['department_id'];
                $department_name = isset($departments[$department_id]) ? $departments[$department_id] : '';
                $admin['department_name'] = $department_name;
            }
        }
        
        $this->view->assign($params);
        $this->view->assign(array(
            'admin_list' => $admin_list,
            'page' => $page,
            'departments' => $departments,
        ));
        
    }
    
    /**
     * 添加管理员 
     */
    public function addadminAction()
    {
        $request = $this->getRequest();
        $admin_service = new AdminService();
        if ($request->isPost())
        {
            $username = Star_String::escape($request->getParam('username'));
            $admin_name = Star_String::escape($request->getParam('admin_name'));
            $department_id = (int) $request->getParam('department_id');
            $password = $request->getParam('password');
            
            if (empty($password))
            {
                return $this->showWarning('对不起，密码不能为空。');
            }
            
            if (Star_String::strLength($password) < 6)
            {
                return $this->showWarning('对不起，密码不能少于6个字符。');
            }
            
            if (empty($username))
            {
                return $this->showWarning('对不起，用户名不能为空。');
            }
            
            if ($admin_service->getAdminByUsername($username))
            {
                return $this->showWarning('对不起，用户名已经存在了。');
            }
            $admin_data = array(
                'department_id' => $department_id,
                'username' => $username,
                'admin_name' => $admin_name ? $admin_name : $username,
                'password' => Password::Encryption($username, $password),
                'error_times' => 0,
                'error_date' => Star_Date::getDate(),
                'last_login' => time(),
                'add_time' => time(),
                'update_time' => time(),
            );
            $admin_id = $admin_service->insertAdmin($admin_data);
            if ($admin_id)
            {
                $menu_ids = $request->getParam('menu_ids');
                if (!empty($menu_ids))
                {
                    $menu_ids = array_unique($menu_ids);
                    foreach ($menu_ids as $menu_id)
                    {
                        $auth_data = array(
                            'menu_id' => (int) $menu_id,
                            'department_id' => 0,
                            'admin_id' => $admin_id,
                            'add_time' => time(),
                            'update_time' => time(),
                        );
                        //添加权限
                        $admin_service->insertAuth($auth_data);
                    }
                }
                return $this->showMessage('恭喜您，成功添加管理员。', '/system/adminmanage');
            } else{
                return $this->showWarning('很遗憾，添加管理员失败。');
            }
        }
        $departments = $admin_service->getDepartmentOption();
        $this->view->assign(array(
            'departments' => $departments,
            'admin' => array(),
        ));
        $this->render('admininfo');
    }
    
    /**
     * 编辑管理员
     */
    public function editadminAction()
    {
        $request = $this->getRequest();
        $admin_service = new AdminService();
        $admin_id = (int) $request->getParam('admin_id');
        $admin_info = $admin_service->getAdminById($admin_id);
        if (empty($admin_info))
        {
            return $this->showWarning('对不起，管理员不存在', '/system/adminmanage');
        }
        
        if ($request->isPost())
        {
            $username = Star_String::escape($request->getParam('username'));
            $admin_name = Star_String::escape($request->getParam('admin_name'));
            $department_id = (int) $request->getParam('department_id');
            $password = $request->getParam('password');
            
            if ($password && Star_String::strLength($password) < 6)
            {
                return $this->showWarning('对不起，密码不能少于6个字符。');
            }
            
            if (empty($username))
            {
                return $this->showWarning('对不起，用户名不能为空。');
            }
            
            $admin_data = array(
                'admin_name' => $admin_name,
                'department_id' => $department_id,
                'update_time' => time(),
            );
            $password && $admin_data['password'] = Password::Encryption($username, $password);
            $rs = $admin_service->updateAdmin($admin_id, $admin_data);
            if ($rs)
            {
                $admin_service->deleteAuth('admin_id = ' . (int) $admin_info['admin_id']); //删除用户权限
                $menu_ids = $request->getParam('menu_ids');
                if (!empty($menu_ids))
                {
                    $menu_ids = array_unique($menu_ids);
                    foreach ($menu_ids as $menu_id)
                    {
                        $auth_data = array(
                            'menu_id' => (int) $menu_id,
                            'department_id' => 0,
                            'admin_id' => $admin_id,
                            'add_time' => time(),
                            'update_time' => time(),
                        );
                        //添加权限
                        $admin_service->insertAuth($auth_data);
                    }
                }
                return $this->showWarning('恭喜您，修改成功。', '/system/adminmanage');
            } else {
                return $this->showWarning('很遗憾，修改失败。');
            }
        }
        $departments = $admin_service->getDepartmentOption();
        $menus = $admin_service->getAllSortMenu(); //返回所有菜单
        $department_auth_options = $admin_service->getDepartmentAuthOption($admin_info['department_id']); //返回部门所有权限
        $admin_auth_options = $admin_service->getAdminAuthOption($admin_info['admin_id']);
        $this->view->assign(array(
            'admin' => $admin_info,
            'departments' => $departments,
            'menus' => $menus,
            'department_auth_options' => $department_auth_options,
            'admin_auth_options' => $admin_auth_options, 
        ));
        $this->render('admininfo');
    }
    
    /**
     * 删除管理员
     * 
     * @return type 
     */
    public function deleteadminAction()
    {
        $request = $this->getRequest();
        $admin_id = (int) $request->getParam('admin_id');
        $admin_service = new AdminService();
        $admin_info = $admin_service->getAdminById($admin_id);
        if (empty($admin_info))
        {
            return $this->showWarning('对不起，管理员不存在,无法删除。', '/system/adminmanage');
        }
        $rs = $admin_service->deleteAdmin($admin_id);
        if ($rs)
        {
            return $this->showMessage('恭喜您，删除成功。', '/system/adminmanage');
        } else {
            return $this->showWarning('很遗憾，删除失败。', '/system/adminmanage');
        }
    }
    
    public function menumanageAction()
    {
        $request = $this->getRequest();
        $page = (int) $request->getParam('page');
        $page_size = 20;
        $params = array(
            'top_id' => (int) $request->getParam('top_id'),
            'menu_name' => Star_String::escape($request->getParam('menu_name')),
        );
        $admin_service = new AdminService();
        $menu_data = $admin_service->getMenuByPage($page, $page_size, $params);
        $page_info = $menu_data['page'];
        $menu_list = $menu_data['menu_list'];
        $top_menus = $admin_service->getTopMenuOption();
        if ($menu_list)
        {
            foreach ($menu_list as &$menu)
            {
                $top_id = $menu['top_id'];
                $first_menu_name = $top_menus[$top_id];
                $menu['first_menu_name'] = $first_menu_name;
            }
        }
        $this->view->assign($params);
        $this->view->assign(array(
            'page' => $page_info,
            'menu_list' => $menu_list,
            'top_menus' => $top_menus,
        ));
    }
    
    /**
     * 返回子菜单接口
     * 
     * @return type 
     */
    public function submenuAction()
    {
        $request = $this->getRequest();
        $menu_id = (int) $request->getParam('menu_id');
        $admin_service = new AdminService();
        $menus = $admin_service->getMenuByParent($menu_id);
        return $this->showJson(0, $menus);
    }
    
    /**
     * 添加菜单 
     */
    public function addmenuAction()
    {
        $request = $this->getRequest();
        $admin_service = new AdminService();
        if ($request->isPost())
        {
            $top_id = (int) $request->getParam('top_id');
            $parent_id = (int) $request->getParam('parent_id');
            $menu_name = Star_String::escape($request->getParam('menu_name'));
            $sort = (int) $request->getParam('sort');
            $is_show = (int) $request->getParam('is_show');
            $controller = $request->getParam('controller');
            $action = $request->getParam('action');
            
            if (empty($menu_name))
            {
                return $this->showWarning('对不起，菜单名称不能为空。');
            }
            
            if ($parent_id == 0) //一级菜单
            {
                $top_id = 0;
                $controller = '';
                $action = '';
                $menu_level = 1;
                $parent_id = 0;
                $is_show = 1;
            }
            
            if ($parent_id && $parent_id == $top_id) //二级菜单
            {
                $menu_info = $admin_service->getMenuByid($parent_id);
                if (!$menu_info)
                {
                    return $this->showWarning('对不起，上级菜单不存在，请确认数据是否有误。');
                }
                
                if ($menu_info['menu_level'] != 1)
                {
                    return $this->showWarning('对不起，上级菜单不是为一级菜单。');
                }
                $is_show = 1;
                $controller = '';
                $action = 0;
                $menu_level = 2;
            }
            
            if ($parent_id && $parent_id != $top_id) //三级菜单
            {
                if (empty($controller))
                {
                    return $this->showWarning('对不起，Controller不能为空。');
                }
                
                if (empty($action))
                {
                    return $this->showWarning('对不起，Action不能为空。');
                }
                $menu_level = 3;
            }
            
            $menu_data = array(
                'controller' => $controller,
                'action' => $action,
                'menu_name' => $menu_name,
                'top_id' => $top_id,
                'parent_id' => $parent_id,
                'menu_level' => $menu_level,
                'sort' => $sort,
                'is_show' => $is_show,
                'add_time' => time(),
                'update_time' => time(),
            );
            
            $menu_id = $admin_service->insertMenu($menu_data);
            if ($menu_id)
            {
                return $this->showMessage('恭喜您，成功添加菜单。');
            } else {
                return $this->showMessage('很遗憾添加菜单失败。');
            }
        }
        $menus = $admin_service->getTopMenu();
        $this->view->assign(array(
            'menus' => $menus,
            'menu' => array(),
        ));
        $this->render('menuinfo');
    }
    
    /**
     * 修改菜单
     * 
     * @return type 
     */
    public function editmenuAction()
    {
        $second_menus = array();
        $request = $this->getRequest();
        $menu_id = (int) $request->getParam('menu_id');
        $admin_service = new AdminService();
        $menu_info = $admin_service->getMenuById($menu_id);

        if (empty($menu_info))
        {
            return $this->showWarning('对不起，菜单不存在。', '/system/menumanage');
        }
        
        if ($request->isPost())
        {
            $parent_id = $menu_info['parent_id'];
            $top_id = $menu_info['top_id'];
            $menu_name = Star_String::escape($request->getParam('menu_name'));
            $sort = (int) $request->getParam('sort');
            $is_show = (int) $request->getParam('is_show');
            $controller = $request->getParam('controller');
            $action = $request->getParam('action');
            
            if (empty($menu_name))
            {
                return $this->showWarning('对不起，菜单名称不能为空。');
            }
            
            if ($parent_id == 0) //一级菜单
            {
                $controller = '';
                $action = '';
                $is_show = 1;
            }
            
            if ($parent_id && $parent_id == $top_id) //二级菜单
            {
                $menu_info = $admin_service->getMenuByid($parent_id);
                if (!$menu_info)
                {
                    return $this->showWarning('对不起，上级菜单不存在，请确认数据是否有误。');
                }
                
                if ($menu_info['menu_level'] != 1)
                {
                    return $this->showWarning('对不起，上级菜单不是为一级菜单。');
                }
                $is_show = 1;
                $controller = '';
                $action = 0;
            }
            
            if ($parent_id && $parent_id != $top_id) //三级菜单
            {
                if (empty($controller))
                {
                    return $this->showWarning('对不起，Controller不能为空。');
                }
                
                if (empty($action))
                {
                    return $this->showWarning('对不起，Action不能为空。');
                }
            }
            
            $menu_data = array(
                'controller' => $controller,
                'action' => $action,
                'menu_name' => $menu_name,
                'sort' => $sort,
                'is_show' => $is_show,
                'update_time' => time(),
            );

            $rs = $admin_service->updateMenu($menu_id, $menu_data);
            if ($rs)
            {
                return $this->showMessage('恭喜您，修改成功。', '/system/menumanage');
            } else
            {
                return $this->showWarning('很遗憾，修改失败。');
            }
        }
        
        $top_id = $menu_info['menu_level'] == 1 ? $menu_info['menu_id'] : $menu_info['top_id'];
        $second_menus = $admin_service->getMenuByParent($top_id);
        
        $menus = $admin_service->getTopMenu();
        $this->view->assign(array(
            'menu' => $menu_info,
            'menus' => $menus,
            'second_menus' => $second_menus,
        ));
        $this->render('menuinfo');
    }
    
    /**
     * 删除菜单
     * 
     * @return type 
     */
    public function deletemenuAction()
    {
        $request = $this->getRequest();
        $menu_id = (int) $request->getParam('menu_id');
        $admin_service = new AdminService();
        $menu_info = $admin_service->getMenuById($menu_id);
        if (empty($menu_info))
        {
            return $this->showWarning('对不起，菜单不存在。', '/system/menumanage');
        }
        $rs = $admin_service->deleteMenu($menu_id);
        if ($rs)
        {
            return $this->showMessage('恭喜您，删除成功。', '/system/menumanage');
        } else {
            return $this->showWarning('很遗憾，删除失败。', '/system/menumanage');
        }
        
    }
    
    /**
     * 登录日志 
     */
    public function loginlogAction()
    {
        $request = $this->getRequest();
        $admin_service = new AdminService();
        $page = (int) $request->getParam('page');
        $username = Star_String::escape($request->getParam('username'));
        $start_date = trim($request->getParam('start_date'));
        $end_date = trim($request->getParam('end_date'));
        $start_date = Star_Date::isDate($start_date) == false? date('Y-m-01') : $start_date;
        $end_date = Star_Date::isDate($end_date) == false ? date('Y-m-d'): $end_date;
        $start_time = Star_Date::dateToTime($start_date);
        $end_time = Star_Date::dateToTime($end_date, false);
        $page_size = 20;
        $params = array(
            'username' => $username,
            'start_time' => $start_time,
            'end_time' => $end_time,
        );
        $login_data = $admin_service->getLoginLogByPage($page, $page_size, $params);
        $this->view->assign(array(
            'page' => $login_data['page'],
            'login_logs' => $login_data['login_logs'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'username' => $username,
        ));
    }
}

?>