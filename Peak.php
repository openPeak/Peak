<?php
namespace Peak;
final class Peak {
	/**
	 * 框架初始化
	 */
	public static function run(){
		self::initCharset();		//初始化字符集设置
		self::initConst();			//初始化常量目录设置
		self::initConfig();			//初始化配置信息
		self::initErrorCatch();		//初始化异常捕获模式
		self::initAutoLoad();		//初始化类的自动加载
		self::initDispatch();		//初始化请求分发
	}

	/**
	 * 初始化字符集设置,开启session
	 */
	private static function initCharset(){
		// 字符集设置
		header("content-type:text/html;charset=utf-8");
		// 格式化时区
		date_default_timezone_set("Asia/Shanghai");
		// session开启
		session_start();
	}

	/**
	 * 初始化常量目录设置
	 */
	private static function initConst(){
		define("DS"        , DIRECTORY_SEPARATOR);//分隔符
		define("ROOT"      , getcwd().DS);//网站根目录(当前index.php所在文件项目路径)D:\phpStudy\WWW\produce\sharebook\
		define("PLATFORM"  , isset($_GET['p']) ? ucfirst($_GET['p']) : "Home");//平台名称
		define("CONTROLLER", isset($_GET['c']) ? ucfirst($_GET['c']) : "Index");//控制器名称
		define("ACTION"    , isset($_GET['a']) ? $_GET['a'] : "index");//操作方法名称
		define("APP_PATH"  , ROOT.PLATFORM.DS);//平台目录
		define("WWW_PATH"  , str_replace('\\',DS,realpath(dirname(__FILE__).'/../')).DS); //定义站点目录(站点目录,框架与项目文件的平级) D:\phpStudy\WWW\produce
		define("PEAK"      , WWW_PATH."Peak".DS);//框架根目录
		define("CORE"      , PEAK."Core".DS);	//框架核心类目录
		define("VENDOR"    , PEAK."Vendor".DS);//第三方类库目录
		define("TEMPLATE"  , PEAK."Template".DS);//框架模板目录
		define("CONFIG"    , ROOT."Conf".DS);	//配置文件目录
		define("RUNTIME"   , ROOT."Runtime".DS);//框架运行临时文件目录
		define("CACHE"     , RUNTIME."Cache");//缓存目录
		define("UPLOAD"    , ROOT."Upload".DS);//上传文件目录
		define("PLATFORM_CONFIG" , APP_PATH."Conf".DS);	//平台配置文件目录
		define("CONTROLLER_PATH" , APP_PATH."Controller".DS);//平台控制器目录
		define("MODEL_PATH"      , APP_PATH."Model".DS);//平台模型目录
		define("VIEW_PATH"       , APP_PATH."View".DS);	//平台视图文件目录
		define("PUBLIC_PATH"     , APP_PATH."Public".DS);	//平台静态资源文件目录
		define("__PLATFORM__"    , $_SERVER['PHP_SELF']."?p=".PLATFORM); //当前平台URL路径
		define("__CONTROLLER__"  , __PLATFORM__."&c=".CONTROLLER); //当前控制器URL路径
		define("__ACTION__"      , __CONTROLLER__."&a=".ACTION); //当前操作方法URL路径
	}

	/**
	 * 初始化配置信息
	 * 平台配置优先级高于根目录下的总配置信息
	 */
	private static function initConfig(){
		//加载平台配置信息
		//$config   = include(PLATFORM_CONFIG."config.php");
		//加载总配置信息
		$config = include(CONFIG."config.php");
		$GLOBALS['config'] = $config;
	}

	/**
	 * 初始化类的自动加载
	 */
	private static function initAutoLoad(){
		spl_autoload_register(function($className){
			//将命名空间转换成真实的文件路径
			//如： 空间中的类名： \Home\Controller\IndexController
			//	   真实的类文件：	./Home/Controller/IndexController.class.php
			if(strpos($className,'Admin') !== false || strpos($className,'Home') !== false){//如果包含平台目录，就表示加载平台控制器
				$fileName = ROOT.str_replace("\\", DS, $className).".class.php";
			}else{//否则为加载其他类
				$fileName = WWW_PATH.str_replace("\\", DS, $className).".class.php";
			}

			echo $fileName."<br/>";

			//如果类文件存在，则包含
			if(file_exists($fileName))	require_once($fileName);
		});
	}
	
	/**
	 * 初始化请求分发
	 */
	private static function initDispatch(){
		//构建控制器类名： \Home\Controller\IndexController
		$className     = DS.PLATFORM.DS."Controller".DS.CONTROLLER."Controller";
		$controllerObj = new $className();
		$actionName    = ACTION."Action";
		$controllerObj->$actionName();
	}

	/**
	 * 初始化异常捕获模式
	 */
	private static function initErrorCatch(){
		error_reporting(0);
		// 设置错误模式（开发 or 生产）模式
		if($GLOBALS['config']['WWW_DEBUG'] == false){//生产模式
			register_shutdown_function(function(){
				if ($error = error_get_last()) {
					echo "<h2>系统错误，请联系管理员</h2>";
					// 写入错误信息，到错误日志
					$time = date("Y-m-d H:i:s");
					$type = $error['type'];
					$msg = $error['message'] . '  in ' . $error['file'] . ' on line ' . $error['line'];
					$log = "[$time] [Type:$type] ：$msg"."\r\n";
					$file = fopen("D:/phpStudy/PHPTutorial/WWW/produce/sharebook/Runtime/logs/error.log","a");
					fwrite($file,$log);
					fclose($file);
				}  
			});
		}else{//开发模式
			// 错误信息获取（php脚本结束前最后调用的函数,捕获PHP的错误：Fatal Error、Parse Error等）
			register_shutdown_function(function(){
				if ($error = error_get_last()) {
					echo "<h1 style='font-size:80px;margin:1px;'>error</h1>";
					echo "<div style='margin:35px;'>";
					echo "<b style='font-size:20px;'>错误信息：</b><br>";
					echo "<span style='font-size:30px;margin:25px;'>".$error['message']."</span><br/>";
					echo "<b style='font-size:20px;'>错误位置：</b><br>";
					echo "<span style='font-size:20px;margin:20px;'>".$error['file']."&nbsp;&nbsp;&nbsp;&nbsp; LINE: " . $error['line']."</span></b>";
					echo "</div>";
				}  
			});
		}
	}
}