<?php

/**
 * PPTV统一认证  
 * 
 * @name		PPID
 * @package 	pptv
 * @version 	1.0
 * @author		hfcorriez@gmail.com
 * @copyright 	2005-2011 PPTV.COM
 */
Class PPID {
    const ID_KEY = 'p>c~hf';

    /**
     * 播放ID解密
     * @param string $id_encoded    加密ID
     * @return array                解密后的数组
     */
    static function decode($id_encoded) {
        $str = self::decrypt(base64_decode(str_replace(array('ib', 'ic', 'ia'), array('+', '/', 'i'), $id_encoded)), self::ID_KEY);
        $arr = array('id' => array(4, 'V'), 'set_id' => array(4, 'V'), 'cat_id' => array(2, 'v'), 'src_id' => array(1, 'C'));
        $ret = array();
        $len = 0;
        foreach ($arr as $k => $v) {
            $key = substr($str, $len, $v[0]);
            if (!$key) {
                break;
            }
            $ret[$k] = $str ? array_pop(unpack($v[1], $key)) : '';
            $len += $v[0];
        }
        return $ret;
    }

    /**
     * 播放地址加密
     * @param int $id       频道ID
     * @param int $set_id   剧集ID
     * @param int $cat_id   分类ID
     * @param int $src_id   渠道ID
     * @return string       加密ID      
     */
    static function encode($id, $set_id = 0, $cat_id = 0, $src_id = 0) {
        $str = pack('V', $id) . pack('V', $set_id) . pack('v', $cat_id) . pack('C', $src_id);
        $key = str_replace(array('i', '+', '/', '='), array('ia', 'ib', 'ic', ''), base64_encode(self::encrypt($str, self::ID_KEY)));
        return $key;
    }

    /**
     * PPLive混淆加密算法
     *
     * @param string $str   需要加密的数据
     * @return string $key  密钥
     */
    private static function encrypt($str, $key) {
        $keylen = strlen($key);
        $length = strlen($str);
        $result = '';
        $byte = array();
        $result = substr($str, 0, 1);

        for ($i = 0; $i < $length; $i++)
            $byte[$i] = self::char2byte($str{$i});

        for ($i = 1; $i < $length; $i++) {
            $byte[$i] = ($byte[$i] ^ $byte[$i - 1]) + self::char2byte($key {$i % $keylen});
            $result .= self::byte2char($byte[$i]);
        }
        return $result;
    }

    /**
     * PPLive混淆解密算法
     *
     * @param string $str   需要解密的数据
     * @return string $key  密钥
     */
    private static function decrypt($str, $key) {
        $keylen = strlen($key);
        $length = strlen($str);
        $result = '';
        $byte = array();
        $result = '';

        for ($i = 0; $i < $length; $i++)
            $byte[$i] = self::char2byte($str{$i});

        for ($i = $length - 1; $i > 0; $i--) {
            $byte[$i] = ($byte[$i] - self::char2byte($key {$i % $keylen})) ^ $byte[$i - 1];
            $result = self::byte2char($byte[$i]) . $result;
        }
        $result = substr($str, 0, 1) . $result;
        return $result;
    }

    /**
     * 将单个字节字符数据转换成字节值
     * @param string $char
     * @return int
     */
    private static function char2byte($char) {
        return (int) array_pop(unpack('c', $char));
    }

    /**
     * 将单个字节值转换成字符数据
     * @param int $byte
     * @return string
     */
    private static function byte2char($byte) {
        return pack('c', $byte);
    }

}

?>