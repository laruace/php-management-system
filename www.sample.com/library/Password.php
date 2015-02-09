<?php
/**
 * @package library
 */

/**
 * 密码类
 * 
 * @package library
 * @author zqy
 * @version 1.0
 */
class Password
{
	public static function Encryption($username, $password, $key = 'JXO2fa4*!3@#)$')
    {
        return md5(sha1($password) . '!@431R!' . sha1($username) . md5(sha1($key) .  '*F*jfka fa#'));
    }
}

