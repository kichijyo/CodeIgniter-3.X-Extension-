<?php
/**
 * CI仅仅是做了数据库封装，但是其并没有做模型封装，我们可以把ORM模型建立出来，放在application的core中。
 * CI is just doing database encapsulation, but it is not model encapsulation. 
 * We can build the ORM model and put it in the core of the application.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends \CI_Model {
	
	public function __construct(){
		parent::__construct();
		set_error_handler([$this,"errorHandler"]);
		spl_autoload_register([$this,"myload"]);
		$this->load->database();
	}
	
	private function myload($className){
		if(file_exists($file = APPPATH . 'models' . DIRECTORY_SEPARATOR . $className . ".php")){
			require_once $file;
			return;
		}
	}
	
	private function getInstance($modelClass){
		return (new ReflectionClass($modelClass))->newInstanceWithoutConstructor();
	}
	
	public final function errorHandler($errorNo,$errorMsg,$file,$line){
		$msg = "ErrorNo:" . $errorNo . "\n"
				. "ErrorMsg:" . $errorMsg . "\n"
						. "ErrorFile:" . $file . "\n"
								. "ErrorLine:" . $line . "\n";
								exit($msg);
	}
	
	private function getFields(){
		$properties = (new ReflectionClass(get_class($this)))->getProperties();
		foreach ($properties as $property){
			$fields[] = $property->name;
		}
		
		foreach($fields as $key){
			if($key <> "tableName")
				$data['fields'][$key] = $this->$key;
				else
					$data[$key]= $this->$key;
		}
		return $data;
	}
	
	/**
	 * 增加自身数据
	 * Increase your own data
	 * @return number
	 */
	public final function add(){
		$data = $this->getFields();
		unset($data['fields']['id']);
		$this->db->insert($data['tableName'],$data['fields']);
		if($this->db->affected_rows())
			return $this->db->insert_id();
			return -1;
	}
	/**
	 * 删除指定ID数据
	 * Delete specified ID data
	 * @param int $id
	 * @return boolean
	 */
	public final function del($id){
		$data = $this->getFields();
		$id = is_integer($id) ? intval($id) : exit("ID must be integer.");
		$this->db->delete($data['tableName'],["id" => $id]);
		if($this->db->affected_rows())
			return true;
			return false;
	}
	/**
	 * 修改自身数据
	 * Modify your own data
	 * @return boolean
	 */
	final public function modify(){
		$data = $this->getFields();
		$this->db->update($data['tableName'],$data['fields'],["id" => $data['fields']['id']]);
		if($this->db->affected_rows())
			return true;
			return false;
	}
	/**
	 * 查找指定ID数据
	 * Find the specified ID data
	 * @param int $id
	 * @return Object
	 */
	final public function find($id){
		$data = $this->getFields();
		$id = is_integer($id) ? intval($id) : exit("ID must be integer.");
		$query = $this->db->get_where($data['tableName'],["id" => $id]);
		$obj = $query->custom_row_object(0,get_class($this));
		return $obj;
	}
	/**
	 * 销毁自身数据
	 * Destroy its own data
	 * @return boolean
	 */
	public final function destroy() {
		$data = $this->getFields();
		$this->db->delete($data['tableName'],["id" => $data['fields']['id']]);
		if($this->db->affected_rows())
			return true;
			return false;
	}
	/**
	 * 使用特质SQL进行查询
	 * Query using trait SQL
	 * @param string $sql
	 * @return array
	 */
	public final function query($sql){
		$query = $this->db->query($sql);
		foreach($query->custom_result_object(get_class($this)) as $obj){
			$data[] = $obj;
		}
		$query->free_result();
		return $data;
	}
	/**
	 *使用特质SQL进行执行
	 *Execute with trait SQL
	 * @param string $sql
	 * @return bool
	 */
	public final function execute($sql){
		$this->db->query($sql);
		if($this->db->affected_rows())
			return true;
			return false;
	}
	/**
	 * 获取全部记录数
	 * Get all records numbers
	 * @return int
	 */
	public final function getCountAll(){
		$data = $this->getFields();
		return $this->db->count_all($data['tableName']);
	}
	
	/**
	 * select设计
	 * Select design
	 * @param String $select
	 * @return MY_Model
	 */
	public final function select(String $select){
		$this->db->select($select);
		return $this;
	}
	/**
	 * from设计
	 * From design
	 * @param String $modelClass
	 * @return MY_Model
	 */
	final public function from(String $modelClass){
		$model = $this->getInstance($modelClass);
		$this->db->from($model->tableName);
		return $this;
	}
	/**
	 * join设计
	 * Join design
	 * @param string $modelClass
	 * @param string $condition,如：'comments.id = blogs.id'
	 * @param string $joinType,如left，right，outer，inner，left outer 和 right outer
	 * @return MY_Model
	 */
	public final function join($modelClass,$condition,$joinType = null){
		$model = $this->getInstance($modelClass);
		$this->db->join($model->tableName,$condition,$joinType);
		return $this;
	}
	
	/**
	 * where设计
	 * Where design
	 * @param string $where
	 * @return MY_Model
	 */
	public final function where(string $where){
		$this->db->where($where);
		return $this;
	}
	/**
	 * where in设计
	 * Where in design
	 * @param string $field
	 * @param array $data
	 * @return MY_Model
	 */
	public final function where_in($field,$data){
		$this->db->where_in($field, $data);
		return $this;
	}
	/**
	 * like设计
	 * Like design
	 * @param string $key
	 * @param string $value
	 * @return MY_Model
	 */
	final public function like($key,$value){
		$this->db->like($key,$value);
		return $this;
	}
	/**
	 * order by设计
	 * Order by design
	 * @param string $field
	 * @param string $sorted
	 * @return MY_Model
	 */
	public final function orderBy($field,$sorted){
		$this->db->order_by($field,$sorted);
		return $this;
	}
	/**
	 * limit设计
	 * Limit design
	 * @param int $nums
	 * @param number $offset
	 * @return MY_Model
	 */
	public final function limit($nums,$offset = 0){
		$this->db->limit($nums,$offset);
		return $this;
	}
	/**
	 * get设计，获取QueryBuilder资源
	 * Get design, get QueryBuilder resources
	 * @param string $modelClass
	 * @param int $offset
	 * @param int $count
	 * @return resource
	 */
	public final function get($modelClass = null,$offset = null,$count = null){
		$tableName = null;
		if(!is_null($modelClass)){
			$model = $this->getInstance($modelClass);
			$tableName = $model->tableName;
		}
		return $this->db->get($tableName,$offset,$count);
	}
	/**
	 * getDataSet设计，获取数据集
	 * 此处的设计为了满足ORM，剥离数据层操作用意，注意用法：getDataSet(null,$expectModelClass)，null的占位用意。
	 * getDataSet design, get the data set
	 * The design here is to meet the ORM, stripping the data layer operation intention, 
	 * pay attention to usage: getDataSet (null, $expectModelClass), null placeholder intention.
	 * @param string $modelClass
	 * @param string $expectModelClass
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	public final function getDataSet($modelClass = null,$expectModelClass = null,$offset = null,$count = null){
		$tableName = null;
		if(!is_null($modelClass)){
			$model = $this->getInstance($modelClass);
			$tableName = $model->tableName;
		}
		$query = $this->db->get($tableName,$offset,$count);
		return $this->fetch($query, $expectModelClass);
	}
	private function fetch($query,$expectModelClass){
		if(is_null($expectModelClass))
			$expectModelClass = stdClass::class;
			foreach($query->custom_result_object($expectModelClass) as $obj){
				$data[] = $obj;
			}
			return $data;
	}
	
	/*****************************************************************************
	 *
	 * 	关联关系处理
	 * 	Association processing
	 *
	 *****************************************************************************/
	
	/**
	 * 设置一对一
	 * Set one to one
	 * @param string $modelClass
	 * @param string $masterKey
	 * @param string $foreignKey
	 * @return Object
	 */
	public final function hasOne($modelClass,$masterKey,$foreignKey){
		$model = $this->getInstance($modelClass);
		$query = $model->select("*")->from($model->tableName)->where("`$foreignKey` = " . $this->$masterKey)->limit(1)->get();
		return $query->custom_row_object(0,$modelClass);
	}
	/**
	 *  设置一对一从属
	 *  Set one-to-one slaves
	 * @param string $modelClass
	 * @param string $masterKey
	 * @param string $foreignKey
	 * @return Object
	 */
	public final function belongsTo($modelClass,$masterKey,$foreignKey) {
		$model = $this->getInstance($modelClass);
		$query = $model->select("*")->from($model->tableName)->where("`$masterKey` = " . $this->$foreignKey)->limit(1)->get();
		return $query->custom_row_object(0,$modelClass);
	}
	/**
	 * 设置一对多
	 * Set one-to-many
	 * @param string $modelClass
	 * @param string $masterKey
	 * @param string $foreignKey
	 * @return array
	 */
	public final function hasMany($modelClass,$masterKey,$foreignKey){
		$model = $this->getInstance($modelClass);
		$query = $model->select("*")->from($model->tableName)->where("`$foreignKey` = " . $this->$masterKey)->get();
		foreach($query->custom_result_object($modelClass) as $obj){
			$data[] = $obj;
		}
		$query->free_result();
		return $data;
	}
	/**
	 * 设置多对多
	 * 之所以使用中间表名，是为了省去中间表模型的用意。
	 * Set many to many
	 * The reason for using the intermediate table name is to save the intention of the intermediate table model.
	 * @param string $pivotTableName
	 * @param string $thatTableName
	 * @param string $theMaster
	 * @param string $theForeign
	 * @param string $thatMaster
	 * @param string $thatForeign
	 * @return array
	 */
	public final function belongsToMany($pivotTableName,$thatModelClass,$theMaster,$theForeign,$thatMaster,$thatForeign){
		$model = $this->getInstance($thatModelClass);
		$data = $this->getFields();
		$sql = "select {$model->tableName}.* from {$data["tableName"]},$pivotTableName,{$model->tableName} where {$data["tableName"]}.$theMaster = {$this->$theMaster} and {$model->tableName}.$thatMaster=$pivotTableName.$thatForeign and $pivotTableName.$theForeign = {$data["tableName"]}.$theMaster;";
		$query = $this->db->query($sql);
		foreach($query->custom_result_object($thatModelClass) as $obj){
			$result[] = $obj;
		}
		$query->free_result();
		return $result;
	}
}
