<?php
defined('VAS_ROOT') || define('VAS_ROOT', dirname(__FILE__));
require_once VAS_ROOT .'/passport.php';

class Vas_User
{
    public static function getUserRpc($app = '')
    {
        require_once dirname(__FILE__) .'/user/rpc/user.php';
        return new VAS_UR_User($app);
    }

    public static function getMessageRpc($app)
    {
        require_once dirname(__FILE__) .'/user/rpc/message.php';
        return new VAS_UR_Message($app);
    }

    public static function getUsermsgRpc($app, $username)
    {
        require_once dirname(__FILE__) .'/user/rpc/usermsg.php';
        return new VAS_UR_Usermsg($app, $username);
    }

    public static function getTaskRpc($app, $username)
    {
        require_once dirname(__FILE__) .'/user/rpc/task.php';
        return new VAS_UR_Task($app, $username);
    }

    public static function getUkvRpc($app, $username)
    {
        require_once dirname(__FILE__) .'/user/rpc/ukv.php';
        return new VAS_UR_Ukv($app, $username);
    }
    
    public static function appCheckLogin($pp_key, $udi=null)
    {
        return Vas_Passport::appCheckLogin($pp_key, $udi);
    }

    /**
     * 检查用户登录状态，但不同步入库
     * 只进行passport同步检测
     */
    public static function ckLogin()
    {
        $user = Vas_Passport::getCookPP();
        
        if (empty($user['username'])) {
            return false;
        }
        
        $user['pptvname'] = $user['username']; // passport用户名
        $user['union'] = 0; // 是否联合登录用户
        $user['hasBind'] = 0; // 是否已绑定
        
        // 登录绑定信息
        if (preg_match('/^_.+@\w+$/', $user['username'])) {
            $user['union'] = 1;
            
            $loginname = self::getLoginName($user['username']);
            if ($loginname) {
                $user['username'] = $loginname;
                $user['hasBind'] = 1;
            }
        }
        
        return $user;
    }
    
    // 根据pptv的username检测vas中绑定的loginname，如无则再检测pptv那边是否有绑定
    public static function getLoginName($username)
    {
        $loginname = self::getUserRpc()->getLoginName($username);
        
        return $loginname;
    }

    /**
     * 退出登录
     */
    public static function logout()
    {
        Vas_Passport::logout();
    }

    /**
     * 直接生成用户id，用于混服用户
     */
    public static function genUserid($username)
    {
        return self::getUserRpc()->genUserid($username);
    }

    /**
     * 绑定账号
     */
    public static function addBind($username, $idx, $bindid, $bindname)
    {
        if (!$username || !$idx || !$bindid || !$bindname) {
            return false;
        }
        return self::getUserRpc()->addBind($username, $idx, $bindid, $bindname);
    }

    /**
     * 获取用户APP信息
     */
    public static function getUserappinfo($app, $username)
    {
        $data = self::getUserRpc($app)->getUserappinfo($username);
        if (empty($data['userid'])) {
            return array();
        }
        return $data;
    }

    /**
     * 存在则获取用户信息返回，不存在则进行注册
     * 加参数isUnion，标识 是否qq登录
     */
    //public static function syncLogin($app, $isUnion = false)
    public static function syncLogin($app)
    {
        // 如果passport有登录状态，进行同步
        $ppuser = self::ckLogin();
        if (!$ppuser || empty($ppuser['username'])) {
            return false;
        }
        
        $ppuser['is_reg'] = self::getRegType($ppuser['username']);
        
        $data = self::getUserRpc($app)->syncLogin($ppuser);
        if ($data == false || empty($data['userid'])) {
            return false;
        }
        
        empty($data['facepic']) && $data['facepic'] = $ppuser['facepic'];
        empty($data['pptvname']) && $data['pptvname'] = $ppuser['pptvname'];
        empty($data['union']) && $data['union'] = $ppuser['union'];
        empty($data['hasBind']) && $data['hasBind'] = $ppuser['hasBind'];
        
        return $data;
    }
    
    // 获取注册方式， 1 直接注册 2 QQ联合登录
    public static function getRegType($username)
    {
        if (preg_match('/^_.+@\w+$/', $username)) {
            $ext = substr(strstr($username, '@'), 1);
            
            $cfg = array('qq' => 2, 'sina' => 3);
            
            return isset($cfg[$ext]) ? $cfg[$ext] : 9;
        } else {
            return 0;
        }
    }
    
    /**
     * 纯粹根据appid和用户名获取用户信息，未做缓存，不操作cookie数据
     */
    public static function getUserInfo($username, $app = 'game')
    {
        require_once 'phprpc/phprpc_client.php';
        $rpc = new PHPRPC_Client('http://user.vas.pptv.com/api/rpc/user.php');
        
        $data = $rpc->getUserinfo($username, $app);
        
        if ($data == false || empty($data['userid'])) {
            return false;
        }
        
        return $data;
    }

    /**
     * 登录操作
     */
    public static function doLogin($app, $username, $password, &$message = '')
    {
        if (!$username || !$password) {
            $message = '用户名和密码不能为空';
            return false;
        }
        // passport登录
        $exlogin = Vas_Passport::exlogin($username, $password);
        if ($exlogin->isValid()) {
            // rpc进行登录操作
            return self::getUserRpc($app)->getUserapp($exlogin->getPPInfo(), $message);
        } else {
            $message = $exlogin->getMessage();
            return false;
        }
    }

    /**
     * 注册操作
     * 注册成功后再执行一次登录，用于写cookie(passport机制如此)
     */
    public static function doReg($app, $username, $password, $reginfo = array(), &$message = '')
    {
        // 基础验证
        if (!$username || !$password) {
            $message = '用户名和密码不能为空';
            return false;
        }
        // 基本验证：用户名6-30位内，字母、数字和下划线组成，数字账号：6-30位，且不可为1开头的纯11位数字
        if (!preg_match('/^[\w]{6,30}$/', $username) || preg_match('/^1\d{10}$/', $username)) {
            $message = '账号格式错误';
            return false;
        }
        // pptv注册
        $email = empty($reginfo['email']) ? '' : $reginfo['email'];
        $regsimple = Vas_Passport::regsimple($username, $password, $email);
        if ($regsimple->isValid()) {
            // rpc进行注册操作
            $ppuser = $regsimple->getPPInfo();
            empty($reginfo['realname']) || $ppuser['realname'] = $reginfo['realname'];
            empty($reginfo['idcard']) || $ppuser['idcard'] = $reginfo['idcard'];
            $data = self::getUserRpc($app)->getUserapp($ppuser, $message);
            if ($data == false) {
                return false;
            }
            // 登录设cookie
            Vas_Passport::login($username, $password);
            return $data;
        } else {
            $message = $regsimple->getMessage();
            return false;
        }
    }
}