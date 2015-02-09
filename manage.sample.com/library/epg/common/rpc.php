<?php

/**
 * rpc.php
 * 依赖PHPRPC函数库
 *
 * @author      hfcorriez <hfcorriez@gmail.com>
 * @version     $Id: rpc.php v 0.1 2011-3-19 15:51:11 hfcorriez $
 * @copyright   Copyright (c) 2007-2010 PPLive Inc.
 *
 *
 */
if (!defined('PHPRPC_ENTRY')) {
    define('PHPRPC_ENTRY', 'phprpc/phprpc_client.php');
}

class RPC {

    private static $instance = array();
    private $rpc = null;
    private $rpc_error = '';
    private $rpc_conf = array();

    private function __construct($conf) {
        if (!class_exists('PHPRPC_Client'))
            include(PHPRPC_ENTRY);

        if (!class_exists('PHPRPC_Client'))
            return false;

        $this->rpc_conf = $conf;

        $this->rpc = new PHPRPC_Client ( );
        $this->rpc->setProxy(NULL);
        @$this->rpc->useService($conf['uri']);
        $this->rpc->setKeyLength(0);
        $this->rpc->setEncryptMode(0);
        $this->rpc->setCharset($conf['charset'] ? $conf['charset'] : 'utf-8');
        $this->rpc->setTimeout($conf['timeout'] ? $conf['timeout'] : 5);
    }

    /**
     * RPC是否错误
     * @return boolean      true|false
     */
    public function get_error() {
        return $this->rpc_error;
    }

    /**
     * 获取所有的远程方法
     * @return array        远程方法
     */
    public function get_functions() {
        $url = $this->rpc_conf['uri'] . '?phprpc_functions';
        $file = @file_get_contents($url);
        $match = array();
        preg_match("/\"(.*)\"/", $file, $match);
        $functions = unserialize(base64_decode($match [1]));
        return $functions;
    }

    /**
     * 检查RPC是否正常
     *
     * @param object $result
     */
    private function rpc_check($result) {
        if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
            $this->rpc_error = (array) $result;
            return false;
        }
        return true;
    }

    /**
     * 调用PHPRPC方法
     * @return mixed
     */
    public function __call($name, $arguments) {
        if (!$this->rpc)
            return false;

        $this->rpc_error = false;
        $result = call_user_func_array(array($this->rpc, $name), $arguments);
        $ret = false;
        if ($result && $this->rpc_check($result)) {
            $ret = self::rpc_obj2array($result);
        }
        return $ret;
    }

    /**
     * 将对象转换成数据
     * @param mixed $data
     * @return mixed
     */
    static function rpc_obj2array($data) {
        if (is_object($data))
            $data = get_object_vars($data);
        return is_array($data) ? array_map(array(__CLASS__, __FUNCTION__), $data) : $data;
    }

    /**
     * 生成一个实例
     * @param array $conf       配置数组
     * @return object           对象
     */
    static public function instance($conf) {
        $key = md5(serialize($conf));
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new self($conf);
        }
        return self::$instance[$key];
    }

}

?>
