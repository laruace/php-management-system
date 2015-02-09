<?php
require_once VAS_ROOT .'/inc/utility.php';

class VAS_UR_Abstract
{
    protected $_app = null;

    public function __construct($app)
    {
        if (!empty($app)) {
            $this->setApp($app);
        }
    }

    public function setApp($app)
    {
        if ($app instanceof Vas_UI_App) {
            $this->_app = $app;
        } else {
            require_once dirname(__FILE__) .'/../input/app.php';
            $this->_app = new Vas_UI_App();
            if (is_array($app)) {
                foreach ($app as $k=>$v) {
                    property_exists($this->_app, $k) && $this->_app->$k = $v;
                }
            } elseif (is_string($app)) {
                $this->_app->app = $app;
            }
            // ip
            $this->_app->ip = Vas_Inc_Utility::getIp();
            // cookie处理，从cookie里提取cid等
            if (!empty($_COOKIE['vas_ch'])) {
                require_once 'vas_common/crypt.php';
                $appex = json_decode(base32_decode($_COOKIE['vas_ch']), true);
                foreach ($this->_app as $k=>$v) {
                    if ($k != 'app' && $k != 'ext') {
                        if (!empty($appex[$k]) && !$v) {
                            $this->_app->$k = $appex[$k];
                        }
                    } elseif ($k == 'ext') {
                        $this->_app->ext = array_merge($appex, $this->_app->ext);
                    }
                }
            }
        }
    }

    public function getRpc($url)
    {
        require_once 'phprpc/phprpc_client.php';
        return new PHPRPC_Client($url);
    }
}