<?php

/**
 * PPTV统一认证  
 * 
 * @name		PPAuth
 * @package 	pptv
 * @subpackage  ppauth
 * @version 	1.0
 * @author		hfcorriez@gmail.com
 * @copyright 	2010 PPTV.COM   
 *
 */

/**
 * 
 *  PPAuth统一认证类
 * 
 * 例子:
 *  <code>
 *  $ppa = new PPAuth(true);
 *  if( $ppa->Login( 'hfcorriez', '******' ) ){
 *  echo '登录成功';
 *  }
 * 
 *  define ( 'PPAUTH_USE_PPTV', true );
 *  define ( 'PPAUTH_USE_SESSION', false );
 *  #define ( 'PPAUTH_FUNC_ISLOGIN', 'isMyLogin' );
 *  #define ( 'PPAUTH_FUNC_LOGIN', 'MyLogin' );
 *

 *  $pa = new PPAuth ( );
 *  $ret = $pa->Login ( '飞扬老大', '123456' );
 *  echo "<pre>";
 *  var_dump ( $pa->Logged (), $pa->Error() );
 *  echo "</pre>";

 *  function MyLogin($user) {
 *      echo "调用" . __FUNCTION__;
 *      $_SESSION ['myuser'] = $user;
 *  }

 *  function isMyLogin() {
 *      echo "调用" . __FUNCTION__;
 *      return $_SESSION ['myuser'];
 *  }
 *  </code>
 *                    
 * 
 * @tutorial 
 * 
 * 支持定义的参数：(如同时启用，按优先级来检测登录)
 *    PPAUTH_USE_PPTV        是否启用PPTV.COM同步认证，检测登录优先级最低
 *  PPAUTH_USE_SESSION    是否启用SESSION认证，检测登录第二优先级
 *  PPAUTH_FUNC_ISLOGIN    检测登录的钩子函数（需要返回一个布尔值)，检测登录优先级最高
 *  PPAUTH_FUNC_LOGIN    登录钩子函数(默认传以用户数组作为参数) 
 *  PPAUTH_FUNC_LOGOUT    退出方法 
 * 
 * 常用方法：
 *    Register            注册用户信息，三个参数为必填项，返回一个布尔值
 *    Login                用户登录，二个参数为必填项，返回一个用户数组
 *    Error                获取出错信息，返回出错信息
 *    Logged                判断用户是否登录 ，返回一个用户数组
 *  Logout                注销登录状态，不返回值
 * 
 * 用户信息数组：（注意区分大小写）
 *  VIP                 是否为VIP（整型）
 *  Mail                用户邮箱
 *  Mobile              手机号码
 *  MobileBound         手机号码是否绑定
 *  UserName            用户名
 *  PPUID               GUID
 *  PPNum                
 *  UserType            用户类型
 *  TimeStamp           登录时间
 *  ExpireTime          过期时间
 *  UserProfile         用户信息
 *  Sex                 性别
 *  Point               积分
 *  Credit              信用
 *  GradeName           等级名称
 *  NextGradeName       下一级名称
 *  LessCredit          离下级所需积分
 *  WhereFrom           来自
 *  ProgramNum          频道数量
 *  MessageCount        纸条数量
 *  FacePic             头像地址（需要加上：http://face.passport.pptv.com/）
 *  PEOnTime            在线时间
 *  Birthday            生日
 *  Blog                博客地址
 *  PersonalText        个人说明
 *  Favorite            爱好 
 * 
 */

class PPAuth {

    //错误信息
    protected $_error;
    //是否同意登录
    protected $_is_pptv_login;
    //内置登录方式
    protected $_is_login_session = null;
    //检测登录函数
    protected $_function_is_login = null;
    //是否通过格式验证
    protected $_validate_stauts = null;
    //格式验证错误
    protected $_validate_error = array();
    //第一次错误
    protected $_validate_firt_error = null;
    //接口消息
    protected $_interface_msg = null;
    //接口消息
    protected $_interface_code = null;

    //登录API
    const LOGIN_API_URL = 'http://passport.pptv.com/WanLogin1.do';

    // 登录类别
    const LOGIN_FROM = 'flowers';

    //注册API
    const REGISTER_API_URL = 'http://passport.pptv.com/HFormReg.do';

    //3DES加密KEY
    const TRIPLEDES_KEY = 'DAEFE3161F3578E0DFDFABD28C9E2F567A27EE5F2F8A2C9B';

    //3DES偏移向量
    const TRIPLEDES_IV = '0102030405060708';

    /**
     * 初始化
     * 	是否统一登录
     * @param string $Is_PPTV_Login
     */
    function __construct($Is_PPTV_Login = false, $Is_Login_Session = false) {
        $this->_is_pptv_login = defined('PPAUTH_USE_PPTV') ? PPAUTH_USE_PPTV : $Is_PPTV_Login;
        $this->_is_login_session = defined('PPAUTH_USE_SESSION') ? PPAUTH_USE_SESSION : $Is_Login_Session;
    }

    /**
     * 注册
     *
     * @param string $username 用户名
     * @param string $email 邮箱
     * @param string $password 密码
     * @return bool 注册结果
     */
    public function Register($username, $email, $password) {
        $conditions = array(
            $username, '用户名', array('username'),
            $username, '用户名', array('ps_length', 6, 16),
            $password, '密码', array('length', 6),
            $email, '邮件地址', array('email')
        );
        $interface_msg = array(0 => '', 1 => '注册失败，用户名不能为空', 2 => '注册失败，用户名为字母，汉字，数字和下划线（6－20个字节以内）', 3 => '注册失败，对不起，该用户名已被注册', 4 => '注册失败，用户名中使用了系统禁止的文字', 5 => '注册失败，密码不能为空', 6 => '注册失败，密码至少6个字符，且不能有空', 7 => '注册失败，邮箱不能为空', 8 => '注册失败，此邮箱已被注册，请换其他邮箱注册', 9 => '注册失败, 验证信息为空');
        $this->Validate_Array($conditions);
        if ($this->_validate_stauts === false)
            return false;
        $post ['UserName'] = $username;
        $post ['PassWord'] = $password;
        $post ['UserMail'] = $email;
        $post ['ReturnURL'] = '';
        $post ['RandomKey'] = rand(1000000, 9999999);
        $post ['ValidateKey'] = substr(md5($post ['PassWord'] . $post ['RandomKey'] . $post ['UserMail'] . $post ['ReturnURL'] . $post ['UserName']), 13, 10);
        $xml = $this->Http_Reqeust_Xml(self::REGISTER_API_URL, $post);
        if (!$xml || !($xml instanceof SimpleXMLElement)) {
            $this->_interface_code = -1;
            $this->_interface_msg = '接口验证错误';
            return false;
        }
        $this->_interface_code = (int) $xml->State;
        $this->_interface_msg = $interface_msg [$this->_interface_code];
        if ((int) $xml->State !== 0) {
            return false;
        }
        return true;
    }

    /**
     * 登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool 登录结果
     */
    public function Login($username, $password) {
        $conditions = array(
            $username, '用户名', array('username'),
            $username, '用户名', array('ps_length', 4, 20),
            $password, '密码', array('length', 4)
        );
        $interface_msg = array(0 => '', 1 => '用户名为空或者用户不存在', 2 => '密码为空或者密码错误', 3 => '该用户已锁定', 4 => '该用户已过期', 5 => '该用户已删除');
        $this->Validate_Array($conditions);
        if ($this->_validate_stauts === false)
            return false;
        $post['username'] = $username;
        $post['password'] = $password;
        $post['from'] = self::LOGIN_FROM;
        $xml = $this->Http_Reqeust_Xml(self::LOGIN_API_URL, $post);
        if (!$xml || !($xml instanceof SimpleXMLElement)) {
            $this->_interface_code = -1;
            $this->_interface_msg = '接口验证错误';
            return false;
        }
        $this->_interface_code = (int) $xml->State;
        $this->_interface_msg = $interface_msg [$this->_interface_code];
        if ((int) $xml->State !== 0) {
            return false;
        }
        $user = $this->Login_Data_Filter($xml);
        if ($this->_is_pptv_login) {
            $this->Login_PPTV($user);
        }
        if ($this->_is_login_session) {
            $this->Login_Session($user);
        }
        if (defined('PPAUTH_FUNC_LOGIN')) {
            call_user_func(PPAUTH_FUNC_LOGIN, $user);
        }
        return $user;
    }

    /**
     * 检查是否登录并返回用户信息
     *
     * @return mixed 用户信息
     */
    public function Logged($call_function = false) {
        $call_function = defined('PPAUTH_FUNC_ISLOGIN') ? PPAUTH_FUNC_ISLOGIN : $call_function;
        if (is_string($call_function) && $call_function != '') {
            return call_user_func($call_function);
        }
        if ($this->_is_login_session) {
            return $this->Logged_Session();
        }
        if ($this->_is_pptv_login) {
            return $this->Logged_PPTV();
        }
        return false;
    }

    /**
     * 检查是否登录并返回用户信息
     *
     * @return mixed 用户信息
     */
    public function Logout($call_function = false) {
        $call_function = defined('PPAUTH_FUNC_LOGOUT') ? PPAUTH_FUNC_LOGOUT : $call_function;
        if (is_string($call_function) && $call_function != '') {
            call_user_func($call_function);
        }
        if ($this->_is_login_session) {
            $this->Logout_Session();
        }
        if ($this->_is_pptv_login) {
            $this->Logout_PPTV();
        }
    }

    /**
     * 获取错误信息
     *
     * @return string 信息文本
     */
    public function Error() {
        return $this->_interface_msg ? $this->_interface_msg : $this->_validate_first_error;
    }


	/**
	 * 获取错误信息的key
	 */
	public function getCode() {
		return $this->_interface_code ? $this->_interface_code : '-1';
	}


    /**
     * 保存用户Session
     *
     * @param array $user 用户数据
     */
    public function Login_Session($user) {
        $_SESSION ['PP_Login_User'] = $user;
    }

    /**
     * 检查是否登录SESSION信息
     *
     * @return array 用户SESSION数据
     */
    public function Logged_Session() {
        return $_SESSION ['PP_Login_User'];
    }

    /**
     * 注销SESSION登录状态
     *
     */
    public function Logout_Session() {
        $_SESSION ['PP_Login_User'] = null;
    }

    /**
     * 登录PPTV
     *
     * @param array $user
     * @return string
     */
    public function Login_PPTV($user) {
        foreach (array('PPUID', 'Mail', 'UserName', 'PPNum', 'UserType', 'TimeStamp', 'ExpireTime', 'UserProfile') as $key) {
            $ppkey_arr [$key] = $user [$key];
        }
        $ppkey_arr ['Result'] = 0;
        /**
         * 0728新增VIP和手机号
         */
        $ppkey_arr['VIP'] = $user['VIP'];
        $ppkey_arr['Mobile'] = $user['Mobile'];
        $ppkey_arr['MobileBound'] = $user['MobileBound'];
        /**
         * 0728结束
         */
        $ppkey_arr ['MD5'] = base64_encode(mhash(MHASH_SHA1, join('$', $ppkey_arr)));
        $ppkey = base64_encode($this->tripleDESEnCrypt(join('$', $ppkey_arr)));
        $ppname_arr ['UserName'] = urlencode($user ['UserName']);
        $ppname_arr ['PPUID'] = strtoupper(str_replace('-', '', $user ['PPUID']));
        $ppname = join('$', $ppname_arr);
        foreach (array('Sex', 'Point', 'Credit', 'GradeName', 'NextGradeName', 'LessCredit', 'WhereFrom', 'ProgramNum', 'MessageCount', 'FacePic', 'Mail', 'PEOnTime', 'Birthday', 'Blog', 'PersonalText', 'Favorite') as $key) {
            $udi_arr [$key] = urlencode($user [$key]);
        }
        $udi = join('$', $udi_arr);
        setcookie('PPKey', $ppkey, time () + 86400, '/', '.pptv.com');
		setcookie('PWD', $user['PWD'], time () + 86400, '/', '.pptv.com');
        setrawcookie('PPName', $ppname, time () + 86400, '/', '.pptv.com');
        setrawcookie('UDI', $udi, time () + 86400, '/', '.pptv.com');
    }

    /**
     * 检查PPTV是否登录
     *
     * @return array 用户数据
     */
    public function Logged_PPTV() {
        $user = array();
        if (isset($_COOKIE ['PPKey']) && ($ppkey = $_COOKIE ['PPKey']) && function_exists('mcrypt_decrypt')) {
            $ppkey_string = $this->tripleDESDeCrypt(base64_decode($ppkey));
            if ($ppkey_string) {
                list ( $user ['GUID'], $user ['Mail'], $user ['UserName'], $user ['PPNum'], $user ['UserType'], $user ['TimeStamp'], $user ['ExpireTime'], $user ['UserProfile'], $user ['Result'], $user['VIP'], $user['Mobile'], $user['MobileBound'], $user ['MD5'] ) = explode('$', $ppkey_string);
                $tmp = explode('$', $_COOKIE ['UDI']);
                $user ['Sex'] = $tmp [0];
                $user ['Point'] = $tmp [1];
                $user ['Credit'] = $tmp [2];
                $user ['GradeName'] = $tmp [3];
                $user ['NextGradeName'] = $tmp [4];
                $user ['LessCredit'] = $tmp [5];
                $user ['WhereFrom'] = $tmp [6];
                $user ['ProgramNum'] = $tmp [7];
                $user ['MessageCount'] = $tmp [8];
                $user ['FacePic'] = $tmp [9];
                $user ['PEOnTime'] = $tmp [11];
                $user ['Birthday'] = $tmp [12];
                $user ['Blog'] = $tmp [13];
                $user ['PersonalText'] = $tmp [14];
                $user ['Favorite'] = $tmp [15];
            }
        }
        return $user;
    }

    /**
     * 退出PPTV的登录状态
     *
     */
    public function Logout_PPTV() {
        setcookie('PPKey', null, time () - 3600, '/', '.pptv.com');
        setcookie('UDI', null, time () - 3600, '/', '.pptv.com');
        setcookie('PPName', null, time () - 3600, '/', '.pptv.com');
		setcookie('PWD', null, time () - 3600, '/', '.pptv.com');
    }

    /**
     * 处理参数错误的key
     *
     * @param array $user
     * @return array
     */
    private function Login_Data_Filter($user) {
        $newUser = array();
        foreach ($user as $key => $value) {
            switch ($key) {
                case 'WherFrom' :
                    $newKey = 'WhereFrom';
                    break;
                case 'PersonalTexte' :
                    $newKey = 'PersonalText';
                    break;
                case 'PEOntime' :
                    $newKey = 'PEOnTime';
                    break;
                default :
                    $newKey = $key;
            }
            $newUser [$newKey] = is_numeric($value) ? (int) $value : (string) $value;
        }
        return $newUser;
    }

    /**
     * 验证数组算法
     *
     * @param array $conditions
     */
    private function Validate_Array($conditions) {
        $num = count($conditions);
        for ($i = 0; $i < $num; $i += 3) {
            $validate = $this->Validate($conditions [$i], $conditions [$i + 2]);
            if (!$validate) {
                $errorMsg = $conditions [$i + 1] . $this->ValidateError($conditions [$i], $conditions [$i + 2]);
                if ($this->_validate_first_error === null) {
                    $this->_validate_first_error = $errorMsg;
                }
                if ($this->_validate_stauts === null) {
                    $this->_validate_stauts = false;
                }
                $this->_validate_error [] = $errorMsg;
            }
        }
    }

    /**
     * 验证
     * 	支持格式：
     * 1 Validate($val, [conditions...]);
     * 2 Validate($val, array([conditions...]));
     * 3 Validate(array($val, [conditions...]))
     * 
     * @return bool 验证是否成功
     */
    private function Validate() {
        $args = func_get_args ();
        if (is_array($args [0]))
            $args = $args [0];
        if (is_array($args [1]))
            $args = array_merge(array($args [0]), $args [1]);
        $method = $args [1];
        switch ($method) {
            case 'equal' :
                $validate = ($args [0] == $args [2]);
                break;
            case 'email' :
                $validate = preg_match('/^\w+([.]\w+)*[@]\w+([.]\w+)*[.][a-zA-Z]{2,4}$/', $args [0]);
                break;
            case 'username' :
                $validate = preg_match('/^\D[\x{4e00}-\x{9fa5}\w]+$/ui', $args [0]);
                break;
            case 'require' :
                $validate = !empty($args [0]);
                break;
            case 'range' :
                if ($args [2] === null)
                    $validate = ($args [0] <= $args [3]);
                elseif ($args [3] === null || !isset($args [3]))
                    $validate = ($args [0] >= $args [2]);
                else
                    $validate = (($args [0] >= $args [2]) && ($args [1] <= $args [3]));
                break;
            case 'length' :  //字节长
                $length = strlen($args [0]);
                if ($args [2] === null)
                    $validate = ($length <= $args [3]);
                elseif (!isset($args [3]) || $args [3] === null)
                    $validate = ($length >= $args [2]);
                else
                    $validate = (($length >= $args [2]) && ($length <= $args [3]));
                break;
            case 'char_length': //字长
                $length = 0;
                for ($i = 0; $i < strlen($args [0]); ++$i) {
                    if ((ord($args [0][$i]) & 0xC0) != 0x80) {
                        ++$length;
                    }
                }
                if ($args [2] === null)
                    $validate = ($length <= $args [3]);
                elseif ($args [3] === null || !isset($args [3]))
                    $validate = ($length >= $args [2]);
                else
                    $validate = (($length >= $args [2]) && ($length <= $args [3]));
                break;
            case 'ps_length': //占位符长度
                $length = (strlen($args [0]) + mb_strlen($args [0], 'utf-8')) / 2;
                if ($args [2] === null)
                    $validate = ($length <= $args [3]);
                elseif ($args [3] === null || !isset($args [3]))
                    $validate = ($length >= $args [2]);
                else
                    $validate = (($length >= $args [2]) && ($length <= $args [3]));
                break;
            default :
                $validate = preg_match($method, $args [0]);
        }
        return $validate;
    }

    /**
     * 验证错误消息
     * 	格式与验证主函数相同
     *
     * @return string
     */
    private function ValidateError() {
        $args = func_get_args ();
        if (is_array($args [0]))
            $args = $args [0];
        if (is_array($args [1]))
            $args = array_merge(array($args [0]), $args [1]);
        $method = $args [1];
        switch ($method) {
            case 'equal' :
                $validate = '必须相等';
                break;
            case 'require' :
                $validate = '不能为空';
                break;
            case 'range' :
                if ($args [2] === null)
                    $validate = '最大为' . $args [3];
                elseif ($args [3] === null || !isset($args [3]))
                    $validate = "最小为{$args[2]}";
                else
                    $validate = "在{$args[2]}和{$args[3]}之间";
                break;
            case 'char_length' :
            case 'ps_length' :
            case 'length' :
                $length = strlen($args [1]);
                if ($args [2] === null)
                    $validate = "长度最大为 {$args[3]}";
                elseif ($args [3] === null || !isset($args [3]))
                    $validate = "长度最小为{$args[2]}";
                else
                    $validate = "长度在{$args[2]}位和{$args[3]}位之间";
                break;
            default :
                $validate = '格式错误';
        }
        return $validate;
    }

    /**
     * CURL请求XML，并返回simlexml对象
     *
     * @param string $url 请求地址
     * @param array $fileds POST数组
     * @return simplexml 对象
     */
    private function Http_Reqeust_Xml($url, $fileds) {
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileds);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $content = trim(curl_exec($ch));
        $header = curl_getinfo($ch);
        curl_close($ch);
        if ($header['http_code'] != 200) {
            $this->_interface_msg = '服务器响应错误';
            return false;
        }
        if (!$content) {
            $this->_interface_msg = '接口响应错误';
            return false;
        }
        return simplexml_load_string($content);
    }

    private function tripleDESEnCrypt($input) {
        $key = pack('H48', self::TRIPLEDES_KEY);
        $iv = pack('H16', self::TRIPLEDES_IV);
        return mcrypt_encrypt(MCRYPT_3DES, $key, $this->PaddingPKCS7($input), MCRYPT_MODE_CBC, $iv);
    }

    private function tripleDESDeCrypt($input) {
        $key = pack('H48', self::TRIPLEDES_KEY);
        $iv = pack('H16', self::TRIPLEDES_IV);
        $result = mcrypt_decrypt(MCRYPT_3DES, $key, $input, MCRYPT_MODE_CBC, $iv);
        $end = ord(substr($result, - 1));
        $out = substr($result, 0, - $end);
        return $out;
    }

    private function PaddingPKCS7($input) {
        $srcdata = $input;
        $block_size = mcrypt_get_block_size('tripledes', 'ecb');
        $padding_char = $block_size - (strlen($input) % $block_size);
        $srcdata .= str_repeat(chr($padding_char), $padding_char);
        return $srcdata;
    }

}

?>