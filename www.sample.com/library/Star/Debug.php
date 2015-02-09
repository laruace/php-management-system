<?php
/**
 * @package library\Star
 */

/**
 *
 * debug操作类
 *
 * @package library\Star
 * @author zhangqy
 *
 */
class Star_Debug {
	
    /**
     * 输出并中断程序
     * @param unknown $data
     */
    public static function dump($data)
    {
        var_dump($data);
        exit;
    }
    
    /**
     * 返回堆栈详细数据
     * 
     * @return type 
     */
    public static function Trace()
    {
        $trace_info = debug_backtrace();
        array_shift($trace_info);
        $stact_trace = array_map(array('Star_Debug', 'traceMessage'), $trace_info);
        return $stact_trace;   
    }
    
    /**
     * 返回跟踪信息
     * 
     * @param type $trace
     * @return type 
     */
    public static function traceMessage($trace)
    {
        return "{$trace['file']}({$trace['line']}): {$trace['class']}{$trace['type']}{$trace['function']}(" . implode(',', $trace['args']) . ")";
    }
}

?>