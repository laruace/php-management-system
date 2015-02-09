<?php
require_once dirname(__FILE__) .'/abstract.php';

class VAS_UR_User extends VAS_UR_Abstract
{
    const API_URL = 'http://user.vas.pptv.com/api/rpc/user.php';
    protected $_rpc = null;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->_rpc = $this->getRpc(self::API_URL);
    }

    /**
     * 获取用户APP信息
     */
    public function getUserappinfo($username)
    {
        $key = md5($this->_app->app .'_u_'. $username);
        $data = Vas_Inc_Utility::cacheGet($key, true);
        if (!empty($data['username'])) {
            return $data;
        } else {
            return $this->_rpc->getUserappinfo($this->_app->app, $username);
        }
    }

    /**
     * 同步登录
     * 先从redis里获取数据，如果不存在则进行rpc登录和注册
     */
    public function syncLogin($ppuser, &$message = '')
    {
        $key = md5($this->_app->app .'_u_'. $ppuser['username']);
        $data = Vas_Inc_Utility::cacheGet($key, true);
        if (!empty($data['userid']) && !empty($data['username'])) {
            return $data;
        } else {
            // 进行rpc处理
            return $this->getUserapp($ppuser, $message);
        }
    }

    public function getUserapp($ppuser, &$message = '')
    {
        $result = $this->_rpc->syncLogin($this->_app, $ppuser, $_COOKIE);
        
        if ($result['status']) {
            return $result['userapp'];
        } else {
            $message = $result['message'];
            return false;
        }
    }

    public function __call($method, $args = array())
    {
        if (!is_array($args)) {
            $args = $this->_app ? array($this->_app, $args) : array($args);
        } elseif ($this->_app) {
            array_unshift($args, $this->_app);
        }
        return call_user_func_array(array($this->_rpc, $method), $args);
    }
}