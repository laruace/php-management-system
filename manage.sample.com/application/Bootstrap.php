<?php

require 'Star/Application/Bootstrap/Bootstrap.php';

class Bootstrap extends Star_Application_Bootstrap_Bootstrap
{
    /**
     * 权限验证
     */
    protected function _initAuth()
	{
        $admin_service = new AdminService();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();

        //登录页面和验证码不需要权限验证
        if (($controller == 'admin' && ($action == 'login' || $action == 'captcha')) || $controller == 'index' || empty($controller))
        {
            return true;
        }
        
        //验证用户是否登录
        if ($admin_service->checkLogin() == false)
        {
            return header('Location: /admin/login');
        }
        return ;
        if ($admin_service->checkAuth($controller, $action) == false)
        {
            if ($this->request->isAjax())
            {
                echo json_encode(array('err' => '403', 'message' => '对不起，您没有权限。'));
                exit;
            } else {
                echo "对不起，您没有权限。";
                exit;
            }
        }
	}
    
    /**
     *  初始化layout
     */
    protected function _initLayout()
    {
        Star_Layout::startMvc(array(
			'base_path' => APPLICATION_PATH . '/layouts',
			'script_path' => 'default',
		));
    }
	
    /**
     * 设置csrf_token
     */
    protected function _initCsrfToken()
    {
        $csrf_token = AdminService::getCsrfToken();
        $this->view->assign('csrf_token', $csrf_token);
    }
}

