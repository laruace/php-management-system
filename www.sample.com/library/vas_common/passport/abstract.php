<?php

abstract class Vas_Passport_Abstract
{
    const TDES_KEY = '29028A7698EF4C6D3D252F02F4F79D5815389DF18525D326';
    const TDES_VI = '70706C6976656F6B';
    
    protected $_url = '';
    protected $_param_from = 'vas';
    protected $_param_format = 'json';
    protected $_result = null;
    protected $_valid = 0;
    protected $_message = '未知错误!';
    
    public function getResult()
    {
        return $this->_result;
    }
    
    public function isValid()
    {
        return (int)$this->_valid;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    public function getUrl()
    {
        return $this->_url;
    }
    
    public function setApp($app)
    {
        $app && $this->_param_from .= ('_'. $app);
    }
    
    public function setFormat($format)
    {
        $this->_param_format = $format;
    }
    

    protected function _httpRequest($url, $data, $raw = 0)
    {
        $url .= '?' . http_build_query($data);
        $this->_url = $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $data = curl_exec($ch);
        
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
 
        if ($errno > 0) {
            return false;
        }
        
        if ($raw) {
            // raw=1表示返回的是原始值
            return $data;
        } elseif ($this->_param_format == 'xml') {
            // @TODO xml解码
        } else {
            // 默认用json
            return json_decode($data);
        }
    }
    
    protected function _decode($str)
    {
        return pplive_3des_decrypt(base64_decode($str), self::TDES_KEY, self::TDES_VI);
    }
    
    protected function _encode($str, $index)
    {
        return base64_encode(pplive_3des_encrypt($str, getStaticKey($index), self::TDES_VI));
    }
}