<?php

class AdminController extends Star_Controller_Action
{
    public function init()
	{
		
	}
    
    /**
     * 管理员登录 
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $admin_service = new AdminService();
        
        //用户已登录跳转到后台首页
        if ($admin_service->checkLogin() == true)
        {
            return $this->redirect('/admin');
        }
        
        if ($request->isPost())
        {
            $username = $request->getParam('username');
            $password = $request->getParam('password');
            $captcha = $request->getParam('captcha');
            
            if (empty($username))
            {
                return $this->showJson(5, '请输入用户名');
            }
            
            if (empty($password) || strlen($password) < 6)
            {
                return $this->showJson(6, '请输入6位以上密码');
            }
            
            $admin = $admin_service->getAdminByUsername($username);
            
            if (empty($admin))
            {
                return $this->showJson(1, '账号不存在，或者密码错误');
            }
            
            //当天密码错误超过5次，需要输入验证码
            if (Star_Date::getDate() == $admin['error_date'] && $admin['error_times'] > 5)
            {
                if (empty($captcha))
                {
                    return $this->showJson(3, '请输入验证码');
                }
                
                if ($admin_service->checkCaptcha($captcha) == false)
                {
                    return $this->showJson(4, '验证码错误');
                }
            }
            
            //验证密码是否正确
            if ($admin['password'] == Password::Encryption($username, $password))
            {
                $admin_service->adminLogin($username);
            }  else {
                $admin_data = array();
                if (Star_Date::getDate() == $admin['error_date'])
                {
                    $admin_data = array(
                        'error_date' => Star_Date::getDate(),
                        'error_times' => 'error_times + 1'
                    );
                } else {
                    $admin_data = array(
                        'error_date' => Star_Date::getDate(),
                        'error_times' => 1
                    );
                }
                
                $admin_service->updateAdmin($admin['admin_id'], $admin_data, false);
                return $this->showJson(2, '账号不存在，或密码错误');
            }
            return $this->showJson(0, '登录成功');
        }
    }
	
    /**
     * 后台首页 
     */
	public function indexAction()
	{
        $admin_service = new AdminService();
        $admin = $admin_service->checkLogin();
        $admin_id = $admin['admin_id'];
        $department_id = $admin['department_id'];
        //管理员菜单列表
        $admin_menus = $admin_service->getAdminMenus($admin_id, $department_id);
        //一级菜单
        $top_menus = $admin_service->getTopMenu();
        $this->view->assign(array(
            'menus' => json_encode($admin_menus),
            'top_menus' => json_encode($top_menus),
            'admin' => $admin,
        ));
	}
	
    public function captchaAction()
    {
        Star_Http_Response::setBrownerCache(10);
        $this->setNoRender();
        $admin_service = new AdminService();
        $admin_service->showCaptcha();
    }
    
    /**
     * 后台中心页 
     */
	public function centerAction()
	{
		  
	}
    
    /**
     * 后台欢迎页 
     */
    public function welcomeAction()
    {
        
    }
    
    /**
     * 退出 
     */
    public function loginoutAction()
    {
        $admin_service = new AdminService();
        $admin_service->loginOut();
        include_once 'pptv_auth/sso_cas.php';
        $pptv_auth = new sso_cas();
        $pptv_auth->logout();
    }
}

?>