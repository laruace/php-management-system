<?php
require_once dirname(__FILE__) .'/abstract.php';

class VAS_UR_Ukv extends VAS_UR_Abstract
{
    const API_URL = 'http://user.vas.pptv.com/api/rpc/ukv.php';
    protected $_rpc = null;
    protected $_username = '';

    public function __construct($app, $username)
    {
        parent::__construct($app);
        $this->_rpc = $this->getRpc(self::API_URL);
        $this->_username = $username;
    }

    public function __call($method, $args = array())
    {
        if (!is_array($args)) {
            $args = array($this->_app->app, $this->_username, $args);
        } elseif ($this->_app) {
            $args = array_merge(array($this->_app->app, $this->_username), $args);
        }
        return call_user_func_array(array($this->_rpc, $method), $args);
    }
}