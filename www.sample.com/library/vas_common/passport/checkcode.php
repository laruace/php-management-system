<?php
/**
 * passport验证码接口
 */
require_once dirname(__FILE__) .'/abstract.php';

class Vas_Passport_Checkcode extends Vas_Passport_Abstract
{
    const API_GUID_URL = 'http://api.passport.pptv.com/v3/checkcode/guid.do';
    const API_CODE_URL = 'http://api.passport.pptv.com/v3/checkcode/image.do';
    const API_CHECK_URL = 'http://api.passport.pptv.com/v3/checkcode/validate.do';
    
    
    public function guid()
    {
        $guid = $this->_httpRequest(self::API_GUID_URL, array(
            'from' => $this->_param_from,
            'format' => $this->_param_format
        ));
        return ($guid->errorCode === 0) ? $guid->result : '';
    }
    
    public function codeurl($guid)
    {
        return self::API_CODE_URL .'?' . http_build_query(array(
            'guid' => $guid,
            'from' => $this->_param_from
        ));
    }
    
    public function codeimg($guid) {
        header('Pragma: no-cache');
        header('Cache-Control: no-cache');
        header('Content-Type: image/jpeg');
        echo $this->_httpRequest($this->codeurl($guid), array(), 1);
    }
    
    public function check($guid, $code)
    {
        $check = $this->_httpRequest(self::API_CHECK_URL, array(
            'guid' => $guid,
            'checkcode' => $code,
            'from' => $this->_param_from,
            'format' => $this->_param_format
        ));
        if ($check->errorCode === 0) {
            return true;
        } else {
            return false;
        }
    }
}

