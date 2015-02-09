<?php
/**
 * passport扩展登录接口
 * passportstdby.pptv.com
 */
require_once dirname(__FILE__) .'/abstract.php';

class Vas_Passport_Exlogin extends Vas_Passport_Abstract
{
    const API_URL = 'http://api.passport.pptv.com/v3/login/ex_login.do';
    const API_URL_DR = 'http://passportstdby.pptv.com/v3/login/ex_login.do';
    protected $_token = '';
    protected $_userprofile = null;
    protected $_vipinfo = null;
    protected $_accountinfo = null;

    public function __construct($username, $password)
    {
        $quest_param = array(
            'username' => $username,
            'password' => $password,
            'uid' => md5($username),
            'from' => $this->_param_from,
            'format' => $this->_param_format
        );
        try {
            $this->_result = $this->_httpRequest(self::API_URL, $quest_param, 1);
            if (empty($this->_result)) {
                throw new Exception('cannot login');
            }
        } catch (Exception $e) {
            $this->_result = $this->_httpRequest(self::API_URL_DR, $quest_param, 1);
        }
        $this->_result = $this->_decode($this->_result);
        if ($this->_param_format == 'json') {
            $this->_result = json_decode($this->_result);
        }
        if (isset($this->_result->errorCode)) {
            $this->_result->message = $this->_message = urldecode($this->_result->message);
            if ($this->_result->errorCode < 5 && $this->_result->result->token) {
                $this->_valid = 1;
                $this->_token = $this->_result->result->token;
                $this->_userprofile = $this->_result->result->userprofile;
                $this->_vipinfo = $this->_result->result->vipinfo;
                $this->_accountinfo = $this->_result->result->accountinfo;
            }
        }
    }
    
    public function getToken()
    {
        return urldecode($this->_token);
    }
    
    public function getUserprofile()
    {
        return $this->_userprofile;
    }
    
    public function getVipinfo()
    {
        return $this->_vipinfo;
    }
    
    public function getAccountinfo()
    {
        return $this->_accountinfo;
    }

    public function getUsername()
    {
        return empty($this->_userprofile->username) ? '' : $this->_userprofile->username;
    }

    public function getPPInfo()
    {
        return array(
            'username' => $this->getUsername(),
            'email' => $this->_accountinfo->mail,
            'vip' => empty($this->_vipinfo->isvalid) ? 0 : 1,
            'mobile' => $this->_accountinfo->phonenum,
            'gender' => $this->_userprofile->gender,
            'area' => $this->_userprofile->province .','. $this->_userprofile->city,
            'facepic' => $this->_userprofile->facepic,
            'birth' => $this->_userprofile->birthday,
            'nickname' => $this->_userprofile->nickname,
            'is_login' => 1
        );
    }
}

