<?php
/**
 * @package library\Star\Model
 */

/**
 * API MODEL
 * 
 * @package library\Star\Model
 * @author qinyang.zhang  2013/08/26
 * 
 */
Class Star_Model_Api
{    
    protected $server_name = '';

    public function __construct($options = array()) {
        
        if ($options)
        {
            $this->setOptions(($options));
        }
    }
    
    /**
     * 配置API
     * 
     * @param type $options 
     */
    public function setOptions($options)
    {
        if (is_array($options))
        {
            $methods = get_class_methods(__CLASS__);
            foreach ($options as $key => $option)
            {
                $method = 'set' . ucfirst($key);
                if (in_array($method, $methods))
                {
                    $this->$method($option);
                }
            }
        }
    }
    
    /**
     * api返回数据
     * 
     * @param type $script_name
     * @param type $params
     * @param type $method
     * @param type $cookie
     * @param type $protocol
     * @return type 
     */
    public function api($script_name, $params, $method = 'get', $cookie = array(), $protocol = 'http', $timeout = 30)
    {
        $query_string = $this->getQueryString($params);
        $cookie_string = $this->getCookieString($cookie);
        
        if (strcmp($protocol . "://", substr($script_name, 0, strlen($protocol . "://"))) !== 0)
        {
            $url = $protocol . "://" . $this->getServerName() . $script_name;
        } else {
            $url = $script_name;
        }
        
        $ch = curl_init();
	    if ('GET' == strtoupper($method))
	    {
		    curl_setopt($ch, CURLOPT_URL, "$url?$query_string");
	    }
	    else 
        {
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
	    }
        
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

	    if (!empty($cookie_string))
	    {
	    	curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
	    }
	    
	    if ('https' == $protocol)
	    {
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    }
	
	    $rs = curl_exec($ch);
	    $err = curl_error($ch);

	    if (false === $rs || !empty($err))
	    {
		    $errno = curl_errno($ch);
		    $info = curl_getinfo($ch);
		    curl_close($ch);
            
	        $message = array(
	        	'result' => false,
	        	'errno' => $errno,
	            'msg' => $err,
	        	'info' => $info,
	        );
            
            $stact_trace = Star_Debug::Trace(); //返回堆栈详细信息
            $stact_trace = implode("\n", $stact_trace);
            Star_Log::log($url . "?" . $query_string . "\n" . $err . "\n" . $stact_trace, 'query_error');
            
            return ;
	    }
	    
       	curl_close($ch);
        return json_decode($rs, true);
    }
    
    /**
     * 返回请求参数
     * 
     * @param type $params
     * @return type 
     */
    protected function getQueryString($params)
    {
        if (is_string($params))
			return $params;
			
		$query_string = array();
	    foreach ((array) $params as $key => $value)
	    {   
	        array_push($query_string, rawurlencode($key) . '=' . rawurlencode($value));
	    }   
	    $query_string = join('&', $query_string);
	    return $query_string;
    }
    
    /**
     * 返回cookie参数
     * 
     * @param $params
     * @return type 
     */
    protected function getCookieString($params)
    {
        if (is_string($params))
			return $params;
			
		$cookie_string = array();
	    foreach ($params as $key => $value)
	    {   
	        array_push($cookie_string, $key . '=' . $value);
	    }   
	    $cookie_string = join('; ', $cookie_string);
	    return $cookie_string;
    }

    /**
     * 设置server_name
     * 
     * @param type $server_name 
     */
    public function setServerName($server_name)
    {
        $this->server_name = $server_name;
    }
    
    /**
     * 返回server_name
     * 
     * @return type 
     */
    public function getServerName()
    {
        if (empty($this->server_name))
        {
            $this->server_name = Star_Config::get('resources.api.server_name');
        }

        return $this->server_name;
    }
}

?>