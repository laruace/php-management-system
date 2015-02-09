<?php
require_once dirname(__FILE__) .'/abstract.php';

class VAS_UR_Message extends VAS_UR_Abstract
{
    const API_URL = 'http://user.vas.pptv.com/api/rpc/message.php';
    protected $_rpc = null;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->_rpc = $this->getRpc(self::API_URL);
    }

    public function __call($method, $args = array())
    {
        if (!is_array($args)) {
            $args = $this->_app ? array($this->_app->app, $args) : array($args);
        } elseif ($this->_app) {
            array_unshift($args, $this->_app->app);
        }
        return call_user_func_array(array($this->_rpc, $method), $args);
    }
}