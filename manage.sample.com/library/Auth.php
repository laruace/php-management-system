<?php
/**
 * 注册、登录类
 *
 * @package Module
 * @author  PennyPan
 * @version $Id: logging.php 2014-04-10 $
 */

require_once 'vas_common/user.php';

class Auth
{
    public static function checklogin($app=null)
    {
        return Vas_User::syncLogin($app);
    }
}
