<?php
if(!class_exists('ldapDomain')){

	class ldapDomain {

		// ldap连接
		public static $_ldapConnection = null;
		public static $_ldapServer = 'synacast.local';

		// 将任意的变量 以字符串形式写入文件
		public static function write($mix , $file='log', $path='C:/log_php/'){
			if(!file_exists($path)){
				@mkdir($path, 0755, true);
			}
			$name = date('Y-m-d').$file;
			$mix = "\r\n\r\n********************************************\r\n".self::toString($mix);
			if($fp = @fopen($path.$name , 'a')) {
				@flock($fp, 2);

				fwrite($fp, $mix);

				fclose($fp);
			}
		}


		// 将任意的变量转化成 字符串
		public static function toString($mix, $LINE="\r\n", $SPACE="\t", $deep = 1){
			$res = '';
			$pre = str_repeat($SPACE, $deep);
			if(is_array($mix)){
				$res .= 'array('.$LINE;
				foreach ($mix as $key => $value) {
					$res .=  $pre . $key . ' => '. self::toString( $value , $LINE, $SPACE, $deep+1).$LINE;
				}
				$res .= str_repeat($SPACE, $deep-1).')';

			}else if(is_object($mix)){

				$class = get_class($mix);

				if(class_exists($class) ){
					$res .=  str_replace("\n", "\r\n".$pre, ReflectionClass::export($class, true)) . $LINE;
				}

			}else if (is_bool($mix)) {

				$res .=  $mix === true ? 'true' : 'false';

			}else if (is_numeric($mix) || is_string($mix)) {
				if($mix === '' ) $res .=  '\'\'';
				else $res .=  $mix ;

			}else if (is_null($mix)) {

				$res .=  'null';

			}else{
				$res .=  '[not support type]';
			}

			return $res;
		}

		// 获得客户端 ip
		public static function getip(){
			$ip = '';
			$cip = getenv('HTTP_CLIENT_IP');
			$xip = getenv('HTTP_X_FORWARDED_FOR');
			$rip = getenv('REMOTE_ADDR');
			$srip = $_SERVER['REMOTE_ADDR'];
			if($cip && strcasecmp($cip, 'unknown')) {
				$ip = $cip;
			} elseif($xip && strcasecmp($xip, 'unknown')) {
				$ip = $xip;
			} elseif($rip && strcasecmp($rip, 'unknown')) {
				$ip = $rip;
			} elseif($srip && strcasecmp($srip, 'unknown')) {
				$ip = $srip;
			}
			preg_match("/[\d\.]{7,15}/", $ip, $match);
			$ip = $match[0] ? $match[0] : 'unknown';
			return $ip;
		}


		public static function _ldapConnectin(){
			if(self::$_ldapConnection === null){
				//建立连接
				self::$_ldapConnection = @ldap_connect(self::$_ldapServer);

				if(!self::$_ldapConnection) return null; // 连接失败

				// 设置参数
				ldap_set_option(self::$_ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option(self::$_ldapConnection, LDAP_OPT_REFERRALS, 0);
			}
			return self::$_ldapConnection;
		}


		public static function ldap( $user, $password ){
			$user = $user.'@'.self::$_ldapServer;
			$conn = self::_ldapConnectin();

			if(!$conn) return false;
			return @ldap_bind($conn, $user, $password);
		}
		
		public static function ldap_verify($user, $password){
			//self::write('auth start');
			$conn = self::_ldapConnectin();
			if(!$conn) return false;
			//self::write('connect success');
			$bind = @ldap_bind($conn, $user.'@'.self::$_ldapServer, $password);
			if($bind){
				//self::write('bind success');
				$filter = "(&(objectCategory=person)(sAMAccountName=$user))";
				$result = @ldap_search($conn, 'dc=synacast,dc=local', $filter );
				if(!$result) return false;
				$entries = @ldap_get_entries($conn, $result);
				//$entries = @self::iconv('utf-8','gbk', $entries);
				if($entries > 0){
					list($name, ) = @explode (' ', $entries[0]['displayname'][0]);
					return array(
						'UserName' => $user,
						'RealName' => $name,
						//'department' => $entries[0]['department'][0],
						'Email'	=> $entries[0]['mail'][0],
						//'title' => $entries[0]['title'][0],
					);
				}
			}

			return false;
		}


		public static function iconv($from, $to, $mix ){
			if(is_string($mix)){
				return iconv($from, $to, $mix );
			}elseif(is_array($mix)){
				$new = array();
				foreach($mix as $key => $item){
					$new[$key] = self::iconv($from, $to, $item);
				}
				return $new;
			}else{
				return $mix;
			}
		}

	}



}
?>