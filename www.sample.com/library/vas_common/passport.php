<?php
/**
 * passport接口
 */
require_once dirname(__FILE__) .'/crypt.php';

class Vas_Passport
{
    const COOK_KEY = 'DAEFE3161F3578E0DFDFABD28C9E2F567A27EE5F2F8A2C9B';
    const COOK_VI = '0102030405060708';
    const COOK_TIMEOUT = 259200; // 登录态保留时间更改为3天

    /**
     * 获取Token
     */
    public static function getToken($username = '', $password = '',$return_user=false)
    {
        $token = empty($_COOKIE['ppToken']) ? '' : $_COOKIE['ppToken'];
        if (!$token) {
            require_once dirname(__FILE__) .'/passport/login.php';
            $login = new Vas_Passport_Login($username, $password);
            $token = $login->getToken();
            $username_s = $login->getUsername();
            
        }else{
            $user_info = self::getCookPP();
            if(!empty($user_info)){
                $username_s = isset($user_info['username'])? $user_info['username'] :'';
            }
        }
        $username = !empty($username_s) ? $username_s : '';
       
        if($return_user == false){
            return $token;
        }else{
             return array('token'=>$token,'username'=>$username);
        }
        
    }

    /**
     * 简单登录
     */
    public static function login($username, $password, $timeout = 0)
    {
        require_once dirname(__FILE__) .'/passport/login.php';
        $login = new Vas_Passport_Login($username, $password);
        self::setLoginCookie($login->getToken(), $login->getUsername(), $timeout);
        return $login;
    }
    
    /**
     * 复杂登录
     */
    public static function exlogin($username, $password, $timeout = 0)
    {
        require_once dirname(__FILE__) .'/passport/exlogin.php';
        $exlogin = new Vas_Passport_Exlogin($username, $password);
        //self::setLoginCookie($exlogin->getToken(), $exlogin->getUsername(), $timeout);
        //改为登陆账号获取PPkey等 ；解决邮箱注册的pptv账号，重新新绑定的邮箱不能登录问题
        self::setLoginCookie($exlogin->getToken(), $username, $timeout);
        return $exlogin;
    }

    public static function setLoginCookie($token, $username, $timeout = 0)
    {
        $timeout = $timeout ? (int)$timeout : self::COOK_TIMEOUT;
        require_once dirname(__FILE__) .'/passport/cookie.php';
        setcookie('ppToken', $token, time() + 1209600, '/', '.pptv.com');
        $cookie = new Vas_Passport_Cookie($token, $username);
        if ($cookie->isValid()) {
            setcookie('PPKey', urldecode($cookie->getPPKey()), time() + $timeout, '/', '.pptv.com');
            setcookie('PPName', urldecode($cookie->getPPName()), time() + $timeout, '/', '.pptv.com');
            setcookie('UDI', urldecode($cookie->getUDI()), time() + $timeout, '/', '.pptv.com');
            
            // 用户名保留90天
            setcookie('PPVasUid', $username, time() + 7776000, '/', '.pptv.com');
        }
    }

    public static function regsimple($username, $password, $email = '')
    {
        require_once dirname(__FILE__) .'/passport/regsimple.php';
        $regsimple = new Vas_Passport_Regsimple($username, $password, $email);
        return $regsimple;
    }
    
    
    public static function logout()
    {
        setcookie('ppToken', null, time() - 3600, '/', '.pptv.com');
        setcookie('PPKey', null, time() - 3600, '/', '.pptv.com');
        setcookie('UDI', null, time() - 3600, '/', '.pptv.com');
        setcookie('PPName', null, time() - 3600, '/', '.pptv.com');
        setcookie('PWD', null, time() - 3600, '/', '.pptv.com');
    }
    
    public static function password($username, $oldpassword, $newpassword, $checkcode, $guid, $token)
    {
        require_once dirname(__FILE__) .'/passport/password.php';
        return new Vas_Passport_Password($username, $oldpassword, $newpassword, $checkcode, $guid, $token);
    }

    public static function checkcode()
    {
        require_once dirname(__FILE__) .'/passport/checkcode.php';
        return new Vas_Passport_Checkcode();
    }
    
    
    /**
     * PPKey : f758abc4-ac10-44d2-afe5-e94f70fdac6a$shuky2000@163.com$shukyoo$0$00$1356580002906$1356608802906$$0$1$13817976740$1$mQQkBHiTJKnGiKNUig6ZEu8zh3w=
     *
     * UDI = 性别 + “$” + 用户积点 + “$” + 用户经验值 + “$” + URLEncode(用户等级名称) + “$” + URLEncode(下一等级名称) + “$” + URLEncode(下一等级相差经验值) + “$” + URLEncode(省市) + “$” + 用户一天的节目订阅数 + “$” + 未读的小纸条数 + “$”+用户头像 + “$” + URLEncode(用户Email) + “$” + URLEncode(在线时间) + URLEncode(生日) + “$” + URLEncode(Blog地址) + “$” + URLEncode(签名档) + “$” + URLEncode(节目类型) + "$" + URLEncode(昵称) +"$" + VIPTag
     *
     * UDI : 1$1253$1065$PP小编$PP记者$436$上海·浦东新区$0$0$f/7/5/f758abc4-ac10-44d2-afe5-e94f70fdac6a.jpg?135087697$shuky2000@163.com$0分钟$1984年12月13日$$$$shukyyang$1
     */
    public static function getCookPP()
    {
        $user = array();
        if (isset($_COOKIE['PPKey']) && function_exists('mcrypt_decrypt')) {
            $ppkey = pplive_3des_decrypt(base64_decode($_COOKIE['PPKey']), self::COOK_KEY, self::COOK_VI);
            
            if ($ppkey) {
                $ppkey = explode('$', $ppkey);
                $user['ppuid'] = $ppkey[0];
                $user['email'] = $ppkey[1];
                $user['username'] = $ppkey[2];
                $user['vip'] = isset($ppkey[9]) ? $ppkey[9] : 0;
                $user['mobile'] = isset($ppkey[10]) ? $ppkey[10] : '';
                 
                // 扩展用户信息
                if (!empty($_COOKIE['UDI'])) {
                    $udi = explode('$', $_COOKIE['UDI']);
                    $user['gender'] = $udi[0];
                    $user['area'] = str_replace('·', ',', $udi[6]);
                    $user['facepic'] = empty($udi[9]) ? 'http://face.passport.pplive.com/ppface.jpg' : ('http://face.passport.pplive.com/'. $udi[9]);
                    $user['birth'] = str_replace(array('年','月'), '-', $udi[12]);
                    $user['nickname'] = empty($udi[16]) ? $user['username'] : $udi[16];
                } else {
                    $user['gender'] = 0;
                    $user['area'] = '';
                    $user['facepic'] = 'http://face.passport.pplive.com/ppface.jpg';
                    $user['birth'] = '';
                    $user['nickname'] = $user['username'];
                }
            }
        }
        return $user;
    }
    
    public static function appCheckLogin($pp_key, $udi=null)
    {
        $user = array();
        if (isset($pp_key) && function_exists('mcrypt_decrypt')) {
            $ppkey = pplive_3des_decrypt(base64_decode($pp_key), self::COOK_KEY, self::COOK_VI);
            
            if ($ppkey) {
                $ppkey = explode('$', $ppkey);
                $user['ppuid'] = $ppkey[0];
                $user['email'] = $ppkey[1];
                $user['username'] = $ppkey[2];
                $user['vip'] = isset($ppkey[9]) ? $ppkey[9] : 0;
                $user['mobile'] = isset($ppkey[10]) ? $ppkey[10] : '';
                 
                // 扩展用户信息
                if (!empty($udi)) {
                    $udi = explode('$', $udi);
                    $user['gender'] = $udi[0];
                    $user['area'] = str_replace('·', ',', $udi[6]);
                    $user['facepic'] = empty($udi[9]) ? 'http://face.passport.pplive.com/ppface.jpg' : ('http://face.passport.pplive.com/'. $udi[9]);
                    $user['birth'] = str_replace(array('年','月'), '-', $udi[12]);
                    $user['nickname'] = empty($udi[16]) ? $user['username'] : $udi[16];
                } else {
                    $user['gender'] = 0;
                    $user['area'] = '';
                    $user['facepic'] = 'http://face.passport.pplive.com/ppface.jpg';
                    $user['birth'] = '';
                    $user['nickname'] = $user['username'];
                }
            }
        }
        return $user;
    }

    public static function getCookToken()
    {
        return empty($_COOKIE['ppToken']) ? '' : $_COOKIE['ppToken'];
    }
    
    //通过pptoken 获取passport登陆信息  适合手机sdk 之间调用
    public static function getPPinfoByToken($pptoken,$username)
    {
        require_once dirname(__FILE__) .'/passport/cookie.php';
        $ppcook = new Vas_Passport_Cookie($pptoken, $username);
        $ppkeys = $ppcook->getPPKey();
        $udi = $ppcook->getUDI();
        
        $user = array();
        if (isset($ppkeys) && function_exists('mcrypt_decrypt')) {
            $ppkey = pplive_3des_decrypt(base64_decode(urldecode($ppkeys)), self::COOK_KEY, self::COOK_VI);
            if ($ppkey) {
                $ppkey = explode('$', $ppkey);
                $user['ppuid'] = $ppkey[0];
                $user['email'] = $ppkey[1];
                $user['username'] = $ppkey[2];
                $user['vip'] = isset($ppkey[9]) ? $ppkey[9] : 0;
                $user['mobile'] = isset($ppkey[10]) ? $ppkey[10] : '';
    
                // 扩展用户信息
                if (!empty($udi)) {
                    $udi = explode('$', urldecode($udi));
                    $user['gender'] = $udi[0];
                    $user['area'] = str_replace('·', ',', $udi[6]);
                    $user['facepic'] = empty($udi[9]) ? 'http://face.passport.pplive.com/ppface.jpg' : ('http://face.passport.pplive.com/'. $udi[9]);
                    $user['birth'] = str_replace(array('年','月'), '-', $udi[12]);
                    $user['nickname'] = empty($udi[16]) ? $user['username'] : $udi[16];
                } else {
                    $user['gender'] = 0;
                    $user['area'] = '';
                    $user['facepic'] = 'http://face.passport.pplive.com/ppface.jpg';
                    $user['birth'] = '';
                    $user['nickname'] = $user['username'];
                }
            }
        }
        return $user;
    }
    
    // 第三方用户绑定登录帐号，todo: 独立到passport目录下去
    public static function bindLoginName($username, $loginName, $loginPwd)
    {
        $index = '0'. mt_rand(1, 9);
        $infoValue = base64_encode(pplive_3des_encrypt(urlencode($loginName). '&'. urlencode($loginPwd), getStaticKey($index), '70706C6976656F6B'));
        
        $params = array (
            'username' => $username,
            'index' => $index,
            'infoValue' => $infoValue,
            'token' => self::getCookToken(),
            'from' => 'vas',
            'format' => 'json',
        );
        
        $url = 'http://api.passport.pptv.com/v3/thirduser/bind.do?'. http_build_query($params);
        
        $res = self::curlRequest($url);
        $data = json_decode(urldecode($res), true);
        
        $result = array('status' => false, 'message' => '');
        if (isset($data['errorCode'])) {
            if ($data['errorCode'] == 0) {
                $result['status'] = true;
                $result['message'] = '绑定成功';
            } else {
                $result['message'] = $data['message'];
            }
        } else {
            $result['message'] = '绑定接口请求失败';
        }
        
        return $result;
    }
    
    // 获取passport那边的用户基本信息，包含了绑定后的登录帐号
    public static function getUserBaseInfo($username)
    {
        $url = 'http://api.passport.pptv.com/v3/query/nonauthuserprofile.do?username='. urlencode($username). '&format=json';
        
        $res = self::curlRequest($url);
        
        $data = json_decode(urldecode($res), true);
        if (isset($data['errorCode']) && $data['errorCode'] == 0) {
            return $data['result'];
        } else {
            return false;
        }
    }
    
    public static function curlRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $res = trim(curl_exec($ch));
        curl_close($ch);
        
        return $res;
    }

}
