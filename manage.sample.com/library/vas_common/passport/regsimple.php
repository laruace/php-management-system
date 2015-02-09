<?php
/**
 * passport简要注册接口
 */
require_once dirname(__FILE__) .'/abstract.php';

class Vas_Passport_Regsimple extends Vas_Passport_Abstract
{
    const API_URL = 'http://api.passport.pptv.com/v3/register/username_simple.do';
    protected $_username = '';
    protected $_email = '';

    public function __construct($username, $password, $email = '')
    {
        $this->_username = trim($username);
        $this->_email = trim($email);

        // 组合infovalue
        $infovalue = urlencode($username). '&' . urlencode($password);
        $email && $infovalue .= ('&'.urlencode($email));
        $index = '0'.mt_rand(1, 9);
        $infovalue = $this->_encode($infovalue, $index);
        // Request
        $this->_result = $this->_httpRequest(self::API_URL, array(
            'infovalue' => $infovalue,
            'index' => $index,
            'from' => $this->_param_from,
            'format' => $this->_param_format
        ));
        if (isset($this->_result->errorCode)) {
            $this->_result->message = $this->_message = urldecode($this->_result->message);
            if ($this->_result->errorCode === 0) {
                $this->_valid = 1;
            }
        }
    }

    public function getPPInfo()
    {
        return array(
            'username' => $this->_username,
            'email' => $this->_email,
            'facepic' => 'http://face.passport.pplive.com/ppface.jpg',
            'nickname' => '',
            'is_reg' => 1
        );
    }
}

