<?php
/**
 * 调用第三方接口
 */

//$r = Api_Call::send('test','get','http://g.pptv.com?a=b&c=c');

echo $r;

class Api_Call
{
	//const LOG_DIR = '/home/pplive/logs/request/';

	const LOG_DIR = './';
	
    /**
     * 参数：
     * $from string 自定义来源，推荐采用应用名
     * $type  string 调用方式  只能是 post 或 get
     * $url  string 调用的url
     * $postdata string 如果$type=POST,post的数据
     *
     * 注：get或post数据 本函数不做任何编码处理，调用如需编码，自行处理
     * 
     * 返回：
     * 调用url直接返回的数据，不做任何处理
     */
    public static function send($from, $type, $url, $postdata = ''){

		// 初始化变量
		list($usec, $sec) = explode(" ", microtime());
		$timestart = $sec;
		$micro1 = $sec + $usec;
		$cip = Api_Call::get_real_ip();
		$sip = $_SERVER["SERVER_ADDR"];
		
		// 发起请求
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
		if ($type == 'post'){
			$log_url = $url;
			$log_data = $postdata;
			curl_setopt($ch, CURLOPT_POST, 1);		
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		} else{
			@list($log_url, $log_data) = explode("?", $url);
		}
		$result = curl_exec($ch);
		curl_close($ch);

		// 记录日志
		list($usec, $sec) = explode(" ", microtime());
		$micro2 = $sec + $usec;
		$timeend = $sec;
		$span = number_format(($micro2 - $micro1) * 1000, 0, '', '');

        $logfile = Api_Call::LOG_DIR . date('Y-m-d', $timestart).'.log';
		$log = $cip.' '.$sip.' '.date('H:i:s',$timestart).' '.date('H:i:s',$timeend).' '.$span.' '.$from.' '.strtoupper($type).' '.$log_url.' '.$log_data."\n";
		file_put_contents($logfile, $log, FILE_APPEND);

		return $result;
	}

	
	public static function get_real_ip()
	{
		$ip=false;
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		  $ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		  $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
		  if($ip){
		   array_unshift($ips, $ip); $ip = FALSE;
		  }
		  for($i = 0; $i < count($ips); $i++){
		   if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])){
			$ip = $ips[$i];
			break;
		   }
		  }
		}
		return($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}
    
}
