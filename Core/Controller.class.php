<?php
namespace Peak\Core;
use \Peak\Vendor\Smarty;

/**
 *  控制器基类 抽象类
 */
abstract class Controller {
	
	/**
	 * 用于存储封装后的smarty对象
	 * @var smarty
	 * @access protected
	 */
	protected $smarty = NULL;

	/**
	 * 构造方法
	 */
	public function __construct(){
		$this->initSmarty();	// 实例化Smarty对象，初始化赋值操作
	}

	/**
	 * 实例化Smarty模板对象,进行默认配置
	 */
	private function initSmarty(){
		// 实例化对象
		$smarty = new \Peak\Vendor\Smarty();
		// Smarty配置
		$smarty->left_delimiter = isset($GLOBALS['config']['LEFT_DELIMITER']) ? $GLOBALS['config']['LEFT_DELIMITER'] : "{";		// 左定界符
		$smarty->right_delimiter = isset($GLOBALS['config']['RIGHT_DELIMITER']) ? $GLOBALS['config']['RIGHT_DELIMITER'] : "}";		// 右定界符
		// 视图文件目录
		$smarty->setTemplateDir(VIEW_PATH);
		// 设置编译缓存目录
		$smarty->setCompileDir(CACHE);
		// 给smarty成员属性赋值
		$this->smarty = $smarty;
	}

	/**
	 * 封装assign,快捷赋值
	 * @param  String $name  名称
	 * @param  string $value 值
	 */
	protected function assign($name,$value=""){
		$this->smarty->assign($name,$value);
	}

	/**
	 * 封装dispaly,快捷显示模板
	 * @param  string $path 模板文件路径
	 * 不传递时,默认为当前控制器下的当前操作方法名
	 */
	protected function display($path = ""){
		if($path == ""){
			$path =  CONTROLLER.DS.ACTION.".html";
		}else{
			$path = VIEW_PATH.$path.".html";
		}
		$this->smarty->display($path);
	}

	/**
	 * 跳转函数
	 * @param  String  $url     链接地址
	 * @param  String  $message 提示信息
	 * @param  integer $time    等待时间
	 */
	protected function jump($message,$url,$time = 5){
		$this->assign("url",$url);
		$this->assign("message",$message);
		$this->assign("time",$time);
		$this->smarty->display(TEMPLATE."jump.html");
		// 中断程序执行
		exit;
	}
}