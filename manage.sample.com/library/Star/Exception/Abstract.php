<?php
/**
 * @package library\Star\Exception
 */

/**
 *
 * 异常 抽象类
 * 
 * @package library\Star\Exception
 * @author zhangqy
 *
 */
abstract class Star_Exception_Abstract extends Exception {
	
	/**
	 * 构造方法
	 * @param unknown $message
	 * @param number $code
	 * @param string $previous
	 */
	public function __construct($message, $code = 0, $previous = NULL)
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($message, (int) $code);
            $this->_previous = $previous;
        } else {
            parent::__construct($message, (int) $code, $previous);
        }
	}
	
	/**
	 * 
	 * @param unknown $method
	 * @param unknown $args
	 * @return NULL
	 */
	public function __call($method, $args)
	{
		return null;
	}
    
	/**
	 * 返回异常链中的前一个异常并字符串化
	 * @see Exception::__toString()
	 */
    public function __toString()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if (null !== ($e = $this->getPrevious())) {
                return $e->__toString()
                       . "\n\nNext "
                       . parent::__toString();
            }
        }
        return parent::__toString();
    }
    
    /**
     * 返回异常链中的前一个异常
     * @return string
     */
    protected function _getPrevious()
    {
        return $this->_previous;
    }

}

?>