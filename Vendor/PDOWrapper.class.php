<?php
namespace Peak\Vendor;
use \PDO;			// 引入根空间下的PDO类
use \PDOException;	// 引入根空间下的PDO异常类

/**
 * 封装PDO类为PDOWrapper类
 */
final class PDOWrapper {
	// 数据库配置属性
	private $db_type;		// 数据库类型
	private $db_host;		// 主机名
	private $db_port;		// 端口号
	private $db_user;		// 用户名
	private $db_pass;		// 密码
	private $db_name;		// 数据库名
	private $charset;		// 字符集设置
	private $pdo = NULL;	// 用于保存pdo对象

	/**
	 * 构造函数
	 * 初始化数据库配置信息
	 */
	public function __construct(){
		$this->db_type = $GLOBALS['config']['DB_TYPE'];
		$this->db_host = $GLOBALS['config']['DB_HOST'];
		$this->db_port = $GLOBALS['config']['DB_PORT'];
		$this->db_user = $GLOBALS['config']['DB_USER'];
		$this->db_pass = $GLOBALS['config']['DB_PASS'];
		$this->db_name = $GLOBALS['config']['DB_NAME'];
		$this->charset = $GLOBALS['config']['CHARSET'];
		$this->connectDb();		// 实例化PDO对象，初始化连接操作
		$this->setErrMode();	// 设置PDO的错误模式
	}

	/**
	 * 实例化pdo对象
	 */
	private function connectDb(){
		try{
			// PDO认证
			$dsn = "{$this->db_type}:host={$this->db_host};port={$this->db_port};";
			$dsn .= "dbname={$this->db_name};charset={$this->charset}";
			$this->pdo = new PDO($dsn,$this->db_user,$this->db_pass);
		}catch(PDOException $e){
			// 抛出异常
			$str  = "<h2>实例化PDO对象失败！</h2>";
			$str .= "<br>错误状态码：".$e->getCode();
			$str .= "<br>错误行号：".$e->getLine();
			$str .= "<br>错误文件：".$e->getFile();
			$str .= "<br>错误信息：".$e->getMessage();
			echo $str;
			exit();
		}
	}

	/**
	 * 设置PDO错误模式
	 */
	private function setErrMode(){
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * 抛出SQL语句错误异常
	 * @param  PDOException $e PDO异常类
	 */
	private function showError($e){
			$str  = "<h2>SQL语句出错！</h2>";
			$str .= "<br>错误状态码：".$e->getCode();
			$str .= "<br>错误行号：".$e->getLine();
			$str .= "<br>错误文件：".$e->getFile();
			$str .= "<br>错误信息：".$e->getMessage();
			echo $str;
			exit();
	}

	/**
	 * 执行非查询操作SQL，如：insert、delete、update、set等SQL语句
	 * @param  String $sql SQL语句
	 * @return Int      受影响的行数
	 */
	public function exec($sql){
		try{
			return $this->pdo->exec($sql);
		}catch(PDOException $e){
			// 抛出异常
			$this->showError($e);
		}
	}

	/**
	 * 执行SQL语句，返回一条记录信息
	 * @param  String $sql SQL语句
	 * @return Array     返回一条记录信息
	 */
	public function getOne($sql){
		try{
			// 执行sql语句，并返回结果集对象
			$PDOStatement = $this->pdo->query($sql);
			return $PDOStatement->fetch(PDO::FETCH_ASSOC);
		}catch(PDOexception $e){
			$this->showError($e);
		}
	}

	/**
	 * 执行SQL语句，返回多条记录信息
	 * @param  String $sql SQL语句
	 * @return Array     二维数据信息
	 */
	public function getAll($sql){
		try{
			// 执行sql语句，并返回结果集对象
			$PDOStatement = $this->pdo->query($sql);
			// 获得多条记录，以二维数组信息返回
			return $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOexception $e){
			$this->showError($e);
		}	
	}

	/**
	 * 执行SQL语句，返回总记录数
	 * @param  String $sql SQL语句
	 * @return Int     总记录数
	 */
	public function getCount($sql){
		try{
			// 执行sql语句，并返回结果集对象
			$PDOStatement = $this->pdo->query($sql);
			return $PDOStatement->rowCount();
		}catch(PDOexception $e){
			$this->showError($e);
		}	
	}

	/**
	 * 获取最后一次insert操作产生的id
	 */
	public function getInsertId(){
		try{
			return $this->pdo->lastInsertId();
		}catch(PDOexception $e){
			$this->showError($e);
		}	
	}
}
