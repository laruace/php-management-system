<?php
/**
 * passport登录接口
 */
require_once dirname(__FILE__) .'/abstract.php';

class Vas_Passport_Login extends Vas_Passport_Abstract
{
    const API_URL = 'http://api.passport.pptv.com/v3/login/login.do';
    const API_URL_DR = 'http://passportstdby.pptv.com/v3/login/login.do';
    protected $_token = '';

    public function __construct($username, $password)
    {
        $quest_param = array(
            'username' => $username,
            'password' => $password,
            'from' => $this->_param_from,
            'format' => $this->_param_format
        );
        try {
            $this->_result = $this->_httpRequest(self::API_URL, $quest_param);
            if (empty($this->_result)) {
                throw new Exception('cannot login');
            }
        } catch (Exception $e) {
            $this->_result = $this->_httpRequest(self::API_URL_DR, $quest_param);
        }
        if (isset($this->_result->errorCode)) {
            $this->_result->message = $this->_message = urldecode($this->_result->message);
            if ($this->_result->errorCode === 0 && $this->_result->result->token) {
                $this->_valid = 1;
                $this->_token = $this->_result->result->token;
            }
        }
    }
    
    public function getToken()
    {
        return urldecode($this->_token);
    }

    public function getUsername()
    {
        return empty($this->_result->result->username) ? '' : $this->_result->result->username;
    }
}

