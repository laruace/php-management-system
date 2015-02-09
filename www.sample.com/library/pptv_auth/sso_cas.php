<?php
/**
 * 
 * 域账户SSO
 * @author leihuang
 *
 */

//加载 域登录包
include_once(dirname(__FILE__).'/CAS.php');

//加载 域登录包
include_once(dirname(__FILE__).'/ldapDomain.php');

class sso_cas
{
	//服务地址
	//protected $_cas_host = '183.129.205.105';
    protected $_cas_host = 'sso-cas.pplive.cn';
	
	//服务目录
	protected $_cas_context = '/cas';
	
	//服务端口
	protected $_cas_port = 443; //8443
	
	//证书路径
	protected $_cas_server_ca_cert_path = '/usr/local/share/ca-certificates/3some-cacert.crt';
	
	protected $_cas_real_hosts = array();
	
	function __construct(){
		
		$cas_url = 'https://'.$this->_cas_host;
		if ($this->_cas_port != '443'){
			$cas_url = $cas_url.':'.$this->_cas_port;
		}
		$cas_url = $cas_url.$this->_cas_context;
        
		session_name('sess_'.preg_replace('/[^a-z0-9-]/i', '_', basename($_SERVER['SCRIPT_NAME'])));
    }
    
    /**
     * 
     * 公用方法 加载
     * @param $start_session bool 是否由 PHPCAS来管理SESSION
     */
    private function loadCas($start_session = true)
    {
    	//启用调试
		//phpCAS::setDebug();
		
		//初始化phpcas
		phpCAS::client(SAML_VERSION_1_1, $this->_cas_host, $this->_cas_port, $this->_cas_context, $start_session);
		
		//证书验证，为了快速调试，可以不启用验证
		//phpCAS::setCasServerCACert($this->_cas_server_ca_cert_path);
		
		//对于快速测试，您可以禁用CAS服务器的SSL验证。
		//此设置不推荐用于生产。 
		//验证CAS服务器CAS协议的安全性是至关重要的！ 
		phpCAS::setNoCasServerValidation();

		//拒绝服务攻击
		phpCAS::handleLogoutRequests(true, $this->_cas_real_hosts);

		//包含此文件的任何页面上的CAS认证
		phpCAS::forceAuthentication();
    }
   
	/**
	 * 
	 * 登录
	 */
	public function login($start_session = true)
    {
    	//初始化SSO
    	$this->loadCas($start_session);

		//登录信息
		$user_infor = phpCAS::getAttributes();
		
		return $user_infor;
    }
    
	/**
	 * 
	 * 退出
	 */
	public function logout($start_session = true)
    {
    	//初始化SSO
        $this->loadCas($start_session);
		
        //退出
		phpCAS::logout();
		 		
    }
    
	/**
	 * 
	 * 只是验证不登录
	 */
	public function verify($user_name,$user_pwd)
    {
    	if(!$user_name || !$user_pwd)
    	{
    		return false;
    	}
    	$ldap = ldapDomain::ldap_verify($user_name,$user_pwd);
		
    	return $ldap;
    }
}

?>