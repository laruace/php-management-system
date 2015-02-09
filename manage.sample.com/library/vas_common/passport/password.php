<?php
/**
 * passport修改密码接口
 */
require_once dirname(__FILE__) .'/abstract.php';

class Vas_Passport_Password extends Vas_Passport_Abstract
{
    const API_URL = 'http://api.passport.pptv.com/v3/update/password.do';

    public function __construct($username, $oldpassword, $newpassword, $checkcode, $guid, $token)
    {
        $this->_result = $this->_httpRequest(self::API_URL, array(
            'username' => $username,
            'oldpassword' => $oldpassword,
            'newpassword' => $newpassword,
            'checkcode' => $checkcode,
            'guid' => $guid,
            'token' => $token,
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
}

