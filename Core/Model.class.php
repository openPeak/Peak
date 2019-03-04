<?php
namespace Peak\Core;
use \Peak\Vendor\PDOWrapper;

/**
 *  模型类基类
 */
class Model {
	// 用于存储封装后的PDO对象属性
	protected $pdo                = NULL;
	// 保存不同模型类对象的数组属性
	protected static $arrModelObj = array();
	// 表名
	protected $table              = '';
	// 字段列表
	protected $fields             = array();  

	/**
	 * 构造方法
	 * @param String $tableName 数据表名称
	 */
	protected function __construct($tableName){
		// 实例化PDO对象
		$this->pdo = new PDOWrapper();
		// 拼接表前缀
		$this->table = $GLOBALS['config']['DB_PREFIX'].$tableName;
		// 获取表的字段列表
		$this->getFields();
	}

	/**
	 * 获取表字段列表
	 */
	private function getFields(){
		$sql = "DESC ". $this->table;
		$result = $this->pdo->getAll($sql);
		
		foreach($result as $v) {
			$this->fields[] = $v['Field'];
			if($v['Key'] == 'PRI') {
				//如果存在主键的话，则将其保存到变量$pk中
				$pk = $v['Field'];
			}
		}
		//如果存在主键，则将其加入到字段列表fields中
		if(isset($pk)) {
			$this->fields['pk'] = $pk;
		}
	}

	/**
	 * 自动插入记录
	 * @param  array $list 关联数组
	 * @return mixed       成功受影响的行数，失败则返回false
	 */
	public function insert($list){
		$field_list = '';  // 字段列表字符串
		$value_list = '';  // 值列表字符串
		foreach($list as $k => $v) {
			if(in_array($k, $this->fields)) {
				$field_list .= "`".$k."`" . ',';
				$value_list .= "'".$v."'" . ',';
			}
		}
		// 去除右边的逗号
		$field_list = rtrim($field_list,',');
		$value_list = rtrim($value_list,',');
		// 构造sql语句
		$sql = "INSERT INTO `{$this->table}` ({$field_list}) VALUES ($value_list)";

		if($this->pdo->exec($sql)) {
			// 插入成功,返回最后插入的记录id
			return $this->pdo->getInsertId();
		}else{
			// 插入失败，返回false
			return false;
		}
	}

	/**
	 * 自动更新记录
	 * 根据主键id值来更新,不传递主键值,更新失败
	 * @param  array $list 	需要更新的关联数组
	 * @return mixed 成功返回受影响的记录行数，失败返回false
	 */
	public function update($list){
		$uplist = ''; // 更新列表字符串
		$where  = 0;  // 更新条件,默认为0
		foreach($list as $k => $v) {
			if(in_array($k, $this->fields)) {
				if($k == $this->fields['pk']) {
					// 是主键列，构造条件
					$where = "`$k`=$v";
				}else{
					// 非主键列，构造更新列表
					$uplist .= "`$k`='$v'".",";
				}
			}
		}
		//去除uplist右边的
		$uplist = rtrim($uplist,',');
		//构造sql语句
		$sql = "UPDATE `{$this->table}` SET {$uplist} WHERE {$where}";
		if($rows = $this->pdo->exec($sql)) {
			// 成功，并返回受影响的记录数
			return $rows;
		}else{
			// 失败，返回false
			return false;
		}
	}

	/**
	 * 自动删除
	 * @param  mixed  $pk 可以为一个整型，也可以为数组
	 * @return mixed      成功返回删除的记录数，失败则返回false
	 */
	public function delete($pk){
		// 条件字符串
		$where = 0; 
		// 判断$pk是数组还是单值，然后构造相应的条件
		if(is_array($pk)) {
			// 数组
			$where = "`{$this->fields['pk']}` in (".implode(',', $pk).")";
		}else{
			// 单值
			$where = "`{$this->fields['pk']}`=$pk";
		}
		// 构造sql语句
		$sql = "DELETE FROM `{$this->table}` WHERE $where";

		if($rows = $this->pdo->exec($sql)) {
			// 成功，并返回受影响的记录数
			return $rows;	
		}else{
			// 失败返回false
			return false;
		}
	}

	/**
	 * 通过主键获取多条记录信息
	 * 不传递参数，默认为获取所有记录信息
	 * @param  mixed  $pk 可以为一个整型，也可以为数组
	 * @return array      二维数组信息
	 */
	public function select($pk=''){
		if($pk == '') {
			$sql = "SELECT * FROM `{$this->table}`";
			return $this->pdo->getAll($sql);
		}
		// 条件字符串
		$where = 0; 
		// 判断$pk是数组还是单值，然后构造相应的条件
		if(is_array($pk)) {
			// 数组
			$where = "`{$this->fields['pk']}` in (".implode(',', $pk).")";
		}else{
			// 单值
			$where = "`{$this->fields['pk']}`=$pk";
		}
		// 构造sql语句
		$sql = "SELECT * FROM `{$this->table}` WHERE $where";
		//返回二维数组信息
		return $this->pdo->getAll($sql);
	}

	/**
	 * 通过主键获取一条信息
	 * @param  int $pk  主键值
	 * @return array   一维数组
	 */
	public function find($pk){
		$sql = "SELECT * FROM `{$this->table}` WHERE `{$this->fields['pk']}`=$pk";
		return $this->pdo->getOne($sql);
	}

	/**
	 * where语句快捷使用
	 * 没有传递参数，默认获得所有记录信息
	 * @param  string $where 限定条件
	 * @return array        二维数组
	 */
	public function where($where="1"){
		$sql = "SELECT * FROM `{$this->table}` WHERE {$where}";
		return $this->pdo->getAll($sql);
	}

	/**
	 * 获取总记录数
	 * @return Int 总记录数
	 */
	public function count(){
		$sql = "SELECT * FROM `{$this->table}`";
		return $this->pdo->getCount($sql);
	}

	/**
	 * 原生SQL非查询操作，如：insert、delete、update、set等SQL语句
	 * @param  String 	$sql SQL语句
	 * @return Int      受影响的行数
	 */
	public function exec($sql){
		return $this->pdo->exec($sql);
	}

	/**
	 * 原生sql语句查询操作
	 * @param  String 	$sql SQL语句
	 * @return array       二维数组
	 */
	public function query($sql){
		return $this->pdo->getAll($sql);
	}

	/**
	 * 执行SQL语句，返回限制条件的总记录数
	 * @param  String $where 限制条件
	 * @return Int           总记录数
	 */
	public function whereCount($where="1"){
		$sql = "SELECT * FROM `{$this->table}` WHERE {$where}";
		return $this->pdo->getCount($sql);
	}

	/**
	 * 实例化不同模型类对象(单例工厂模式)
	 * @return Model 实例化的模型对象
	 */
	public static function getInstance($customTable=''){
		// 获取后期静态绑定类名
		$modelClassName = get_called_class();
		// 判断当前模型类对象是否存在
		if(!isset(self::$arrModelObj["$modelClassName"])){
			// 从含命名空间中的模型名称获取表名
			$prefixModel = substr(substr($modelClassName, strpos($modelClassName,'Model')+6), 0, -5);
			$prefixModel = lcfirst($prefixModel);
			$tableName   = empty($customTable) ? $prefixModel : $customTable;
			// 实例化模型属性，并为表名赋值
			self::$arrModelObj["$modelClassName"] = new $modelClassName("$tableName");
		}
		return self::$arrModelObj["$modelClassName"];
	}
}