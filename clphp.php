<?php
//PHPWORK
session_start();
//类库文件检测标志
//请在引入该库的脚本中判断该值
define("PHPFLAG", true);
//当前版本：1.0.0
define("version", "1.0.0");

//加载其他第三方类，请把文件放到该文件同一目录下的lib文件夹下
$libdir = "/lib";

if(file_exists($libdir)){
	$dm = opendir($libdir);
	while($d = readdir($dm)){
		if($d != "." && $d != ".."){
			require dirname(__FILE__) . "/" . $libdir . "/" . $d;
		}
	}
}

//-----------------------------------
// 配置信息类
// 请根据实际情况修改该类的具体属性
// 或者调用设置方法设置参数
//-----------------------------------

class CONFIG{
	/*
	 *数据库配置
	 */
	
	//数据库服务器
	public static $db_host = "";
	//数据库名称
	public static $db_name = "";
	//连接用户名
	public static $db_user = "";
	//密码
	public static $db_pass = "";
	//设置数据库参数
	public static function setdb($host, $name, $user, $pass){
		self::$db_host = $host;
		self::$db_name = $name;
		self::$db_user = $user;
		self::$db_pass = $pass;
	}
	
	/*
	 *验证码设置 
	 */
	//验证码字符表
	public static $cc_code = "abcdefghijklmnopqrstuvwxyz0123456789";
	//验证码字体
	public static $cc_font = "font.ttf";
	//验证码生成的会话名称
	public static $cc_session = "chkimg";
	
	//设置验证码参数
	public static function setChkCode($codes, $font, $sessname){
		self::$cc_code = $codes;
		self::$cc_font = $font;
		self::$cc_session = $sessname;
	}
}

//------------------------------------


//一般工具类
class UTIL{
	//获取ip
	public static function getip(){
		return $_SERVER["REMOTE_ADDR"];
	}
	
	//获取ip归属地
	public static function getiparea($ip){
		if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/i", $ip)){
			return false;
		}
		$str = WEB::get("http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip);
		$obj = json_decode($str);
		if($obj->code == 0){
			return $obj->data;
		}else{
			return false;
		}
	}
	
	//生成密码
	public static function crypto($src){
		return sha1(base64_encode($src));
	}
	
	//获取文件扩展名
	public static function getext($file){
		if(preg_match("/.*\.([a-z0-9_\$]*)$/i", $file, $res)){
			return $res[1];
		}else{
			return false;
		}
	}
	
	//转换文件名
	//文件名为md5+原扩展名
	public static function getmdfile($file){
		$fname = md5_file($file);
		$ext = self::getext($file);
		return $fname . "." . $ext;
	}
	
	//生成验证码
	//参数：
	// 验证码长度
	// 验证码字符表，默认abcdefghijklmnopqrstuvwxyz0123456789，不区分大小写
	// 验证方式为先把验证码转换成小写，再进行md5
	public static function genchkimg($len){
		
		//该方法直接修改header，输出图片
		header("content-type: image/jpeg");
		
		$tblen = strlen(CONFIG::$cc_code);
		$chkcode = "";
		for($i=0;$i<$len;$i++){
			$pos = rand(0, $tblen - 1);
			$chkcode .= substr(CONFIG::$cc_code, $pos, 1);
		}
		
		$height = 50;
		$width = $len * 30;
		
		$img = imagecreatetruecolor($width, $height);
		$background = imagecolorallocate($img, rand(240,255), rand(240,255), rand(240,255));
		//填充白色背景色
		imagefill($img, 0, 0, $background);
		
		for($i=0;$i<$len;$i++){
			$color = imagecolorallocate($img, rand(0, 60), rand(0, 60), rand(0, 60));
			$angle = rand(-15, 15);
			
			$str = substr($chkcode, $i, 1);
			$x = rand($i * 30 + 15, ($i + 1) * 30 - 15);
			$y = rand(35, 45);
			
			imagettftext($img, 20, $angle, $x, $y, $color, CONFIG::$cc_font, $str);
		}
		
		imagejpeg($img);
		imagedestroy($img);
		$_SESSION[CONFIG::$cc_session] = md5(strtolower($chkcode));
	}
	
	//跳转地址
	// 参数：要跳转的地址
	//注意！！
	//调用此函数之前不能输出任何信息
	public static function gotourl($url){
		header("Location: " . $url);
	}
}

//网络辅助类
class WEB{
	//get请求地址
	public static function get($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	
	//post请求
	public static function post($url, $arr){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
}

//日志记录类
class LOG{
	private static $log_file = "";
	
	//类型常量定义
	const ERROR = 1;
	const WARN = 2;
	const NORMAL = 3;
	const NOTICE = 4;
	
	//设置日志文件
	public static function setfile($file){
		if(file_exists($file)){
			self::$log_file = $file;
		}else{
			file_put_contents($file, "-- Log start - " . date("Y-m-d H:i:s") . " --");
			self::$log_file = $file;
		}
	}
	
	//记录日志
	public static function set($type, $code, $msg){
		if($type == 1 || $type == 2 || $type == 3 || $type == 4){
			switch($type){
				case 1:
					$tstr = "ERROR";
					break;
				case 2:
					$tstr = "WARNING";
					break;
				case 3:
					$tstr = "NORMAL";
					break;
				case 4:
					$tstr = "NOTICE";
					break;
				default:
					$tstr = "NOTICE";
			}
			
			$str = sprintf($tstr . "(%s)[%s]: %s\n", $type, $code, date("Y-m-d H:i:s"), $msg);
			file_put_contents(self::$log_file, $str, FILE_APPEND);
			return true;
		}else{
			return false;
		}
	}
}

//模板工具类
class template{
	private $template = "";
	
	// 构造函数，打开模板文件
	function __construct($file){
		$this->template = file_get_contents($file);
	}
	
	public function set($key, $value = ""){
		//如果不是数组，检测是否符合复杂表达式
		if(preg_match_all('/\{\$' . $key . '\{(.*)\}\}/ims', $this->template, $m)){
			//如果符合复杂表达式，且值为数组
			if(is_array($value)){
				for($i = 0;$i < count($m[1]);$i++){
					$res = $m[1][$i];
					foreach($value as $k => $v){
						$res = preg_replace('/\{\$' . $key . '\.' . $k . '\}/ims', $v, $res);
					}
					$this->template = preg_replace('/\{\$' . $key . '\{.*\}\}/ims', $res, $this->template);
				}
			}else{
				$this->template = preg_replace('/\{\$' . $key . '\{.*\}\}/ims', $value, $this->template);
			}
		}else{
			//如果非复杂表达式，则直接替换模板
			$this->template = str_replace('{$' . $key . '}', $value, $this->template);
		}
	}
	
	public function put(){
		echo $this->template;
	}
}

//数据库操作类
class FPDO{
	//查询数据库
	//用法：
	// FPDO::select("select * from user where id=:id", array(":id" => "123"));
	//返回值： 查询结果合集
	public static function select($sql, $param=null){
		$pdo = new PDO("mysql:host=" . CONFIG::$db_host . 
			";dbname=" . CONFIG::$db_name . 
			";charset=utf8", CONFIG::$db_user, CONFIG::$db_pass);
		$pdo -> query("set names utf8");
		
		$ds = $pdo -> prepare($sql);
		
		if(isset($arr)){
			$ds -> execute($param);
		}else{
			$ds -> execute();
		}
		return $ds -> fetchall();
	}
	
	//插入数据
	//用法：
	// FPDO::insert("test", array("user" => "asd", "sex" => 1))
	//返回值： 成功true  失败false
	public static function insert($table, $arr){
		$column = implode("`,`", $arr);
		$columnarr = array();
		foreach($arr as $k => $a){
			array_push($columnarr, ":" . $k);
			$param[":" . $k] = $a;
		}
		$columnstr = implode(",", $columnarr);
		$pdo = new PDO("mysql:host=" . CONFIG::$db_host . 
			";dbname=" . CONFIG::$db_name . 
			";charset=utf8", CONFIG::$db_user, CONFIG::$db_pass);
		$pdo -> query("set names utf8");
		
		$ds = $pdo -> prepare("insert into " . $table . "(`" . $column . ") values (" . $columnstr . ")");

		$res = $ds -> execute($param);
		return $res;
	}
	
	
	//执行数据库
	//用法：
	// FPDO::exec("update user set username=:u where id=:id", array(":u" => "123", ":id" => 23))
	public static function exec($sql, $arr=null){
		$pdo = new PDO("mysql:host=" . CONFIG::$db_host . 
			";dbname=" . CONFIG::$db_name . 
			";charset=utf8", CONFIG::$db_user, CONFIG::$db_pass);
		$pdo -> query("set names utf8");
		
		$ds = $pdo -> prepare($sql);
		if(isset($arr))
			$res = $ds -> execute($arr);
		else
			$res = $ds -> execute();
		return $res;
	}
	
	//获取最后插入的id
	// 用法：FPDO::lastins();
	// 返回值：最后插入行的id
	public static function lastins(){
		return PDO::lastInsertId();
	}
	
	
	//分页查询
	//用法：
	// FPDO::selectpage("select * from user", array(1, 10))
	//第二个参数是一个数组，声明翻页的选项
	//array(第几页，每页多少个)
	public static function selectpage($sql, $page, $param=null){
		$pdo = new PDO("mysql:host=" . CONFIG::$db_host . 
			";dbname=" . CONFIG::$db_name . 
			";charset=utf8", CONFIG::$db_user, CONFIG::$db_pass);
		$pdo -> query("set names utf8");
		
		$pagestr = ($page[0] - 1) * $page[1];
		$ds = $pdo -> prepare($sql . " limit" . $pagestr . "," . $page[1]);
		
		if(isset($arr)){
			$ds -> execute($param);
		}else{
			$ds -> execute();
		}
		return $ds -> fetchall();
	}
	
	//判断某条件的数据是否存在，并返回数量
	//用法：
	// FPDO::getrows()
	public static function getrows($table, $where, $arr){
		$pdo = new PDO("mysql:host=" . CONFIG::$db_host . 
			";dbname=" . CONFIG::$db_name . 
			";charset=utf8", CONFIG::$db_user, CONFIG::$db_pass);
		$pdo -> query("set names utf8");
		
		$ds = $pdo -> prepare("select count(*) as c from `$table` where $where");
		
		if(isset($arr)){
			$ds -> execute($param);
		}else{
			$ds -> execute();
		}
		
		$res = $ds -> fetch();
		return $res["c"];
	}
	
	//获取键值对的数据库表
	//用法：
	// $content = FPDO::getkey("keyword", "baseinfo");
	//参数：
	//第一个参数是键名，必须
	//第二个参数是表明，必须
	//第三个参数是键的数据库列名，可选，默认为name
	//第四个参数是值的数据库列名，可选，默认为content
	public static function getkey($key, $table, $def_key = "name", $def_value = "content"){
		$pdo = new PDO("mysql:host=" . CONFIG::$db_host . 
			";dbname=" . CONFIG::$db_name . 
			";charset=utf8", CONFIG::$db_user, CONFIG::$db_pass);
		$pdo -> query("set names utf8");
		
		$ds = $pdo -> prepare("select $def_value from $table where $def_key=:k");
		$ds -> execute(array(":k" => $key));
		
		$res = $ds -> fetch();
		return $res?$res[$def_value]:false;
	}
}
?>