<?php
/**
 * passport登录接口
 */
require_once dirname(__FILE__) .'/abstract.php';

class Vas_Passport_Cookie extends Vas_Passport_Abstract
{
    const API_URL = 'http://api.passport.pptv.com/v3/cookies/query.do';
    
    protected $_ppkey = '';
    protected $_ppname = '';
    protected $_udi = '';

    public function __construct($token, $username)
    {
        $this->_result = $this->_httpRequest(self::API_URL, array(
            'token' => $token,
            'username' => $username,
            'from' => $this->_param_from,
            'format' => $this->_param_format
        ));
        if (isset($this->_result->errorCode)) {
            $this->_result->message = $this->_message = urldecode($this->_result->message);
            if ($this->_result->errorCode === 0) {
                $this->_valid = 1;
                $this->_ppkey = $this->_result->result->PPKey;
                $this->_ppname = $this->_result->result->PPName;
                $this->_udi = $this->_result->result->UDI;
            }
        }
    }
    
    public function getPPKey()
    {
        return $this->_ppkey;
    }
    
    public function getPPName()
    {
        return $this->_ppname;
    }
    
    public function getUDI()
    {
        return $this->_udi;
    }
}

