<?php
/**
 * Admin Service
 * 
 * @author QinYang Zhang
 * @date 2014-05-09
 */
class AdminService {
    
    protected $admin_model;
    
    protected $department_model;
    
    protected $admin_menu_model;
    
    protected $auth_model;
    
    protected $token = '^(*f3&@@6<.gs'; //管理员登陆验签串
    
    protected $auth_token = 'AJklvjq2falk*#'; //权限验签串

    /**
     * 构造函数 
     */
    public function __construct() {
        $this->admin_model = new AdminModel();
        $this->admin_menu_model = new AdminMenuModel();
        $this->department_model = new AdminDepartmentModel();
        $this->auth_model = new AuthModel();
    }
    
    public function adminLogin($username, $admin_name = '')
    {
        $admin_info = $this->admin_model->getAdminByUsername($username);
        $auth_list = array();
        if (empty($admin_info))
        {
            $now_time = time();
            $password = Password::Encryption($username, mt_rand(100000, 9999999999)); //密码加密
            $admin_data = array(
                'username' => $username,
                'admin_name' => $admin_name,
                'password' => $password,
                'department_id' => 0,
                'last_login' => $now_time,
                'error_times' => 0,
                'error_date' => Star_Date::getDate(),
                'add_time' => $now_time,
                'update_time' => $now_time,
            );
            $admin_id = $this->admin_model->insert($admin_data);
            $admin_info = $this->admin_model->getAdminById($admin_id);
        } else {
            $admin_data = array(
                'last_login' => time(),
            );
            $admin_id = $admin_info['admin_id'];
            $this->updateAdmin($admin_info['admin_id'], $admin_data);
        }
        
        $admin_login_data = array(
            'admin_id' => $admin_id,
            'login_ip' => ip2long(Star_Http_Request::getIp()),
            'add_time' => time(),
        );
        $admin_login_model = new AdminLoginModel();
        $admin_login_model->insert($admin_login_data); //添加登录记录
        
        //返回部门权限
        $department_auth = $this->auth_model->getAuthByDepartment($admin_info['department_id']);
        //返回管理员权限
        $admin_auth = $this->auth_model->getAuthByAdmin($admin_info['admin_id']);
        
        if ($department_auth)
        {
            foreach ($department_auth as $auth)
            {
                $menu_id = $auth['menu_id'];
                $controller = $auth['controller'];
                $action = $auth['action'];
                $auth_list[$menu_id] = $this->getUrl($controller, $action);
            }
        }
        
        if ($admin_auth)
        {
            foreach ($admin_auth as $auth)
            {
                $menu_id = $auth['menu_id'];
                $controller = $auth['controller'];
                $action = $auth['action'];
                $auth_list[$menu_id] = $this->getUrl($controller, $action);
            }
        }
        
        $auth_token = $this->makeAuthSign($auth_list);
        $token = $this->makeLoginSign($admin_info['admin_id'], $admin_info['username'], $admin_info['department_id'], $admin_info['last_login'], $auth_token);
        $admin_data = array(
            'admin_id' => $admin_info['admin_id'],
            'department_id' => $admin_info['department_id'],
            'username' => $admin_info['username'],
            'last_login' => $admin_info['last_login'],
            'token' => $token,
            'auth_token' => $auth_token,
            'admin_name' => $admin_info['admin_name'],
        );
        $this->setLoginInfo($admin_data); //设置用户登录信息
        Star_Cookie::set('auth', base64_encode(json_encode($auth_list)), 0, '/', '', false, true); //设置用户权限信息
    }
    
    /**
     * 返回菜单链接地址
     * 
     * @param type $controller
     * @param type $action 
     */
    protected function getUrl($controller, $action)
    {
        return strtolower('/' . $controller . '/' . $action);
    }
    
    public function loginOut()
    {
        Star_Cookie::set('admin', '');
        Star_Cookie::set('auth', '');
    }

    /**
     * 验证管理员是否登录
     * 
     * @return boolean 
     */
    public function checkLogin()
    {
        $admin = $this->getLoginInfo();
        if (empty($admin))
        {
            return false;
        }

        //验签错误
        if ($admin['token'] != $this->makeLoginSign($admin['admin_id'], $admin['username'], $admin['department_id'], $admin['last_login'], $admin['auth_token']))
        {
            return false;
        }
        return $admin;
    }
    
    /**
     * 返回管理登录信息
     * 
     * @return boolean 
     */
    public function getLoginInfo()
    {
        $admin = Star_Cookie::get('admin');
        if (empty($admin))
        {
            return false;
        }
        
        $admin = json_decode(base64_decode($admin, true) ,true);
        return $admin;
    }
    
    /**
     * 设置登录信息
     * 
     * @param type $admin_data 
     */
    public function setLoginInfo($admin_data)
    {
        $admin_data = base64_encode(json_encode($admin_data));
        Star_Cookie::set('admin', $admin_data, 0, '/', '', false, true);
    }


    /**
     * 验证是否权限
     * 
     * @param type $controller
     * @param type $action
     * @return boolean 
     */
    public function checkAuth($controller, $action)
    {
        $controller = strtolower($controller);
        if ($controller == 'admin' || $controller == 'index')
        {
            return true;
        }
        $admin = $this->getLoginInfo();
        $auth_list = Star_Cookie::get('auth');
        if (empty($auth_list) || empty($admin))
        {
            return false;
        }
        
        $auth_list = json_decode(base64_decode($auth_list, true), true);

        //检验验签是否正确
        if ($admin['auth_token'] != $this->makeAuthSign($auth_list))
        {
            return false;
        }
        
        if ($auth_list)
        {
            $controller_action = $this->getUrl($controller, $action);
            foreach ($auth_list as $auth)
            {
                if (strtolower($auth) == $controller_action)
                {
                    return true;
                }
            }
        }
        
        return false;
    }


    /**
     * 生成权限验签串
     * 
     * @param type $auth_list
     * @return type 
     */
    protected function makeAuthSign($auth_list)
    {
        $auth_list = (array) $auth_list;
        ksort($auth_list);
        return sha1(md5(implode('', $auth_list) . $this->auth_token));
    }


    /**
     * 生成登陆验签串
     * 
     * @param type $username
     * @param type $department_id
     * @param type $timestamp
     * @return type 
     */
    protected function makeLoginSign($admin_id, $username, $department_id, $timestamp, $auth_token)
    {
        $ip = Star_Http_Request::getIp();
        $http_agent = Star_Http_Request::getHttpAgent();
        $tmpArr = array($admin_id, $username, $department_id, $timestamp, $ip, $http_agent, $this->token, $auth_token);
        sort($tmpArr, SORT_STRING);
        $signature = md5(sha1(implode('', $tmpArr)));
        return $signature;
    }
    
    /**
     * 添加管理员
     * 
     * @param type $data
     * @return type 
     */
    public function insertAdmin($data)
    {
        return $this->admin_model->insert($data);
    }
    
    /**
     * 更新管理员
     * 
     * @param type $admin_id
     * @param type $admin_data
     * @param type $quote_indentifier
     * @return type 
     */
    public function updateAdmin($admin_id, $admin_data, $quote_indentifier = true)
    {
        return $this->admin_model->update($admin_id, $admin_data, '', $quote_indentifier);
    }
    
    /**
     * 删除管理员
     * 
     * @param type $admin_id
     * @return type 
     */
    public function deleteAdmin($admin_id)
    {
        $rs = $this->admin_model->delete($admin_id);
        if ($rs)
        {
            $this->auth_model->delete('admin_id = ' . (int) $admin_id);
        }
        return $rs;
    }
    
    /**
     * 返回用户信息
     * 
     * @param type $admin_id
     * @return type 
     */
    public function getAdminById($admin_id)
    {
        return $this->admin_model->getAdminById($admin_id);
    }
    
    /**
     * 添加部门
     * 
     * @param type $data
     * @return type 
     */
    public function insertDepartment($data)
    {
        return $this->department_model->insert($data);
    }
    
    /**
     * 更新部门
     * 
     * @param type $department_id
     * @param type $department_data
     * @return type 
     */
    public function updateDepartment($department_id, $department_data)
    {
        return $this->department_model->update($department_id, $department_data);
    }
    
    /**
     * 删除部门
     * 
     * @param type $department_id
     * @return type 
     */
    public function deleteDepartment($department_id)
    {
        $rs = $this->department_model->delete($department_id);
        if ($rs == true)
        {
            $this->auth_model->delete('department_id = ' . (int) $department_id);
        }
        return $rs;
    }
    
    /**
     * 返回部门信息
     * 
     * @param type $department_id
     * @return type 
     */
    public function getDepartmentById($department_id)
    {
        return $this->department_model->getPk($department_id);
    }
    
    /**
     * 返回所有部门
     * 
     * @return type 
     */
    public function getAllDepartment()
    {
        return $this->department_model->getAllDepartment();
    }
    
    /**
     * 返回所有部门option
     * 
     * @return type 
     */
    public function getDepartmentOption()
    {
        $departments = array();
        $department_list = $this->department_model->getAllDepartment();
        
        if (!empty($department_list))
        {
            foreach ((array) $department_list as $department)
            {
                $department_id = $department['department_id'];
                $department_name = $department['department_name'];
                $departments[$department_id] = $department_name;
            }
        }
        
        return $departments;
    }
    
    /**
     * 返回部门列表
     * 
     * @param type $page
     * @param type $page_size
     * @param array $params
     * @return type 
     */
    public function getDepartmentByPage($page, $page_size, Array $params)
    {
        $total = $this->department_model->getDepartmentCount($params);
        $page = Page::setPage($page, $page_size, $total);
        $page_info = array('page' => $page, 'page_size' => $page_size, 'total' => $total);
        $department_list = $this->department_model->getDepartmentByPage($page, $page_size, $params);
        $page_data = Page::show($page_info);
        return array('page' => $page_data, 'total' => $total, 'department_list' => $department_list);
    }
    
    /**
     * 插入菜单
     * 
     * @param type $data
     * @return type 
     */
    public function insertMenu($data)
    {
        return $this->admin_menu_model->insert($data);
    }
    
    /**
     * 更新菜单
     * 
     * @param type $menu_id
     * @param type $menu_data
     * @return type 
     */
    public function updateMenu($menu_id, $menu_data)
    {
        return $this->admin_menu_model->update($menu_id, $menu_data);
    }
    
    /**
     * 删除菜单
     * 
     * @param type $menu_id
     * @return type 
     */
    public function deleteMenu($menu_id)
    {
        $menu_info = $this->admin_menu_model->getPk($menu_id);
        $rs = $this->admin_menu_model->delete($menu_id);
        if ($rs)
        {
            switch ($menu_info['menu_level'])
            {
                case 1:
                    $sub_menus = $this->admin_menu_model->getMenuByTop($menu_id);
                    $menu_ids = array();
                    if ($sub_menus)
                    {
                        foreach ($sub_menus as $menu)
                        {
                            $menu_ids[] = $menu['menu_id'];
                        }
                        $this->admin_menu_model->delete('top_id = ' . (int) $menu_id);
                        $this->auth_model->delete('menu_id in ('.  implode(',', $menu_ids).')');
                    }
                    break;
                case 2: //二级菜单
                    $sub_menus = $this->admin_menu_model->getMenuByParent($menu_id);
                    $menu_ids = array();
                    if ($sub_menus)
                    {
                        foreach ($sub_menus as $menu)
                        {
                            $menu_ids[] = $menu['menu_id'];
                        }
                        $this->admin_menu_model->delete('parent_id = ' . (int) $menu_id);
                        $this->auth_model->delete('menu_id in ('.  implode(',', $menu_ids).')');
                    }
                    break;
                case 3:
                    $this->auth_model->delete('menu_id = ' . (int) $menu_id);
                    break;
            }
                
        }
        return $rs;
    }
    
        /**
     * 返回管理员列表
     * 
     * @param type $page
     * @param type $page_size
     * @param array $params
     * @return type 
     */
    public function getAdminByPage($page, $page_size, Array $params)
    {
        $total = $this->admin_model->getAdminCount($page, $page_size, $params);
        $page = Page::setPage($page, $page_size, $total);
        $page_info = compact('total', 'page', 'page_size');
        $page_data = Page::show($page_info);
        $admin_list = $this->admin_model->getAdminByPage($page, $page_size, $params);
        return array('page' => $page_data, 'total' => $total, 'admin_list' => $admin_list);
    }
    
    /**
     * 返回所有菜单
     * 
     * @return type 
     */
    public function getAllMenu()
    {
        $menu_list = $this->admin_menu_model->getAllMenu();
        $menus = array();
        foreach ($menu_list as $menu)
        {
            $menu_id = $menu['menu_id'];
            $parent_id = $menu['parent_id'];
            $top_id = $menu['top_id'];
            $menu_level = $menu['menu_level'];
            //一级菜单
            if ($menu_level == 1)
            {
                $menus[$menu_id]['menu_info'] = $menu;
            } else if ($menu_level == 2) //二级菜单
            {
                $menus[$top_id]['sub_menus'][$menu_id]['menu_info'] = $menu;
            } else { //三级菜单
                $menus[$top_id]['sub_menus'][$parent_id]['sub_menus'][$menu_id] = $menu;
            }
        }
        return $menus;
    }
    
    /**
     * 返回一级分类排序菜单
     * 
     * @return type 
     */
    public function getAllSortMenu()
    {
        $menus = $this->getAllMenu();
        return $this->menuSort($menus);
    }


    /**
     * 返回菜单列表
     * 
     * @param type $page
     * @param type $page_size
     * @param array $params
     * @return type 
     */
    public function getMenuByPage($page, $page_size, Array $params)
    {
        $total = $this->admin_menu_model->getMenuCount($params);
        $page = Page::setPage($page, $page_size, $total);
        $menu_list = $this->admin_menu_model->getMenuByPage($page, $page_size, $params);
        $page_info = compact('page', 'page_size', 'total');
        $page_data = Page::show($page_info);
        return array('page' => $page_data, 'total' => $total, 'menu_list' => $menu_list);
    }
    
    /**
     * 根据ID返回菜单信息
     * 
     * @param type $menu_id
     * @return type 
     */
    public function getMenuById($menu_id)
    {
        return $this->admin_menu_model->getPk($menu_id);
    }

    /**
     * 返回一级菜单
     * 
     * @return type 
     */
    public function getTopMenu()
    {
        return $this->admin_menu_model->getTopMenu();
    }
    
    /**
     * 返回顶级菜单option
     * 
     * @return type 
     */
    public function getTopMenuOption()
    {
        $menu_list = $this->admin_menu_model->getTopMenu();
        $menus = array();
        if ($menu_list)
        {
            foreach ($menu_list as $menu)
            {
                $menu_id = $menu['menu_id'];
                $menu_name = $menu['menu_name'];
                $menus[$menu_id] = $menu_name;
            }
        }
        return $menus;
    }

        /**
     * 根据用户名返回用户信息
     * 
     * @param type $username
     * @return type 
     */
    public function getAdminByUsername($username)
    {
        return $this->admin_model->getAdminByUsername($username);
    }
    
    /**
     * 返回子菜单
     * 
     * @param type $menu_id
     * @return type 
     */
    public function getMenuByParent($menu_id)
    {
        return $this->admin_menu_model->getMenuByParent($menu_id);
    }
    
    /**
     * 添加权限
     * 
     * @param type $auth_data
     * @return type 
     */
    public function insertAuth($auth_data)
    {
        return $this->auth_model->insert($auth_data);
    }
    
    /**
     * 删除权限
     * 
     * @param type $where
     * @return type 
     */
    public function deleteAuth($where)
    {
        return $this->auth_model->delete($where);
    }
    
    /**
     * 删除部门权限
     * 
     * @param type $department_id
     * @return type 
     */
    public function deleteAuthByDepartment($department_id)
    {
        return $this->auth_model->delete('department_id = ' . (int) $department_id);
    }
    
    /**
     * 返回部门权限
     * 
     * @param type $department_id
     * @return type 
     */
    public function getAuthByDepartment($department_id)
    {
        return $this->auth_model->getAuthByDepartment($department_id);
    }
    
    /**
     * 返回部门权限option
     * 
     * @param type $department_id
     * @return array 
     */
    public function getDepartmentAuthOption($department_id)
    {
        $auth_list = $this->auth_model->getAuthByDepartment($department_id);
        $auth_options = array();
        if (!empty($auth_list))
        {
            foreach ($auth_list as $auth)
            {
                $menu_id = $auth['menu_id'];
                $menu_name = $auth['menu_name'];
                $auth_options[$menu_id] = $menu_name;
            }
        }
        return $auth_options;
    }
    
    /**
     * 返回管理员权限
     * 
     * @param type $admin_id
     * @return array 
     */
    public function getAuthByAdmin($admin_id)
    {
        return $this->auth_model->getAuthByAdmin($admin_id);
    }
    
    /**
     * 返回管理员权限option
     * 
     * @param type $admin_id
     * @return array 
     */
    public function getAdminAuthOption($admin_id)
    {
        $auth_list = $this->auth_model->getAuthByAdmin($admin_id);
        $auth_options = array();
        if (!empty($auth_list))
        {
            foreach ($auth_list as $auth)
            {
                $menu_id = $auth['menu_id'];
                $menu_name = $auth['menu_name'];
                $auth_options[$menu_id] = $menu_name;
            }
        }
        return $auth_options;
    }
    
    /**
     * 管理员权限
     * 
     * @param type $admin_id
     * @param type $department_id
     * @return type 
     */
    public function getAdminMenus($admin_id, $department_id)
    {
        $all_menus = $this->getAllMenu();
        $department_auth = $this->auth_model->getAuthByDepartment($department_id);
        $admin_auth = $this->auth_model->getAuthByAdmin($admin_id);
        $admin_menus = array();
        //部门权限
        if ($department_auth)
        {
            foreach ($department_auth as $auth)
            {
                if ($auth['is_show'] == 1)
                {
                    $menu_id = $auth['menu_id'];
                    $top_id = $auth['top_id'];
                    $parent_id = $auth['parent_id'];
                    $menu_level = $auth['menu_level'];
                    if ($menu_level == 3)
                    {
                        $admin_menus[$top_id]['menu_info'] = $all_menus[$top_id]['menu_info']; //一级菜单信息
                        $admin_menus[$top_id]['sub_menus'][$parent_id]['menu_info'] = $all_menus[$top_id]['sub_menus'][$parent_id]['menu_info']; //二级菜单信息
                        $admin_menus[$top_id]['sub_menus'][$parent_id]['sub_menus'][$menu_id] = $auth; //三级菜单信息
                    }
                }
            }
        }
        
        //用户权限
        if ($admin_auth)
        {
            foreach ($admin_auth as $auth)
            {
                if ($auth['is_show'] == 1)
                {
                    $menu_id = $auth['menu_id'];
                    $top_id = $auth['top_id'];
                    $parent_id = $auth['parent_id'];
                    $menu_level = $auth['menu_level'];
                    if ($menu_level == 3)
                    {
                        $admin_menus[$top_id]['menu_info'] = $all_menus[$top_id]['menu_info']; //一级菜单信息
                        $admin_menus[$top_id]['sub_menus'][$parent_id]['menu_info'] = $all_menus[$top_id]['sub_menus'][$parent_id]['menu_info']; //二级菜单信息
                        $admin_menus[$top_id]['sub_menus'][$parent_id]['sub_menus'][$menu_id] = $auth; //三级菜单信息
                    }
                }
            }
        }
        return $admin_menus;
        
    }
    
    /**
     * 菜单排序
     * 
     * @param type $menus
     * @return type 
     */
    public function menuSort($menus)
    {
        $top_menus = $this->admin_menu_model->getTopMenu();
        $sort_menus = array();
        if ($menus)
        {
            foreach ($top_menus as $menu)
            {
                $menu_id = $menu['menu_id'];
                if (isset($menus[$menu_id]))
                {
                    $sort_menus[] = $menus[$menu_id];
                }
            }
        }

        return $sort_menus;
    }
    
    /**
     * 加密验证码
     * 
     * @param type $captcha
     * @return type 
     */
    protected function encryptionCaptcha($captcha, $time)
    {
        $captcha = md5(md5($captcha . sha1('J(*f2') . $time));
        return $captcha;
    }

    /**
     * 显示验证码 
     */
    public function showCaptcha()
    {
		$now = time();
        $captcha_obj = new Captcha();
        $captcha_obj->doImg();
        $captcha = $captcha_obj->getCode();
        $capcha = $this->encryptionCaptcha($captcha, $now);
        Star_Cookie::set('captcha', $capcha, $now + 30);
		Star_Cookie::set('ct', $now, $now + 30);
    }
    
    /**
     * 检验验证码
     * 
     * @param type $captcha
     * @return boolean 
     */
    public function checkCaptcha($captcha)
    {
		$captcha_time = Star_Cookie::get('ct');
        if ($this->encryptionCaptcha($captcha, $captcha_time) == Star_Cookie::get('captcha') && time() - $captcha_time <20)
        {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 登录日志列表
     * 
     * @param type $page
     * @param type $page_size
     * @param array $params
     * @return type 
     */
    public function getLoginLogByPage($page, $page_size, Array $params)
    {
        $admin_login_model = new AdminLoginModel();
        $total = $admin_login_model->getLoginLogCount($params);
        $page = Page::setPage($page, $page_size, $total);
        $page_info = array('total' => $total, 'page' => $page, 'page_size' => $page_size);
        $page = Page::show($page_info);
        $login_logs = $admin_login_model->getLoginLogByPage($page, $page_size, $params);
        return array('page' => $page, 'total' => $total, 'login_logs' => $login_logs);
    }
}

?>