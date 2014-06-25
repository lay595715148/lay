<?php
/**
 * Memcache Store
 * @author Lay Li
 */
namespace lay\store;

use lay\App;
use lay\core\Connection;
use lay\core\Store;
use lay\core\Expireable;
use Memcache;
use Exception;
use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * Memcache Store
 * @author Lay Li
 */
class MemcacheStore extends Store {
    /**
     * 构造方法
     * @param ModelExpire $model 模型对象
     * @param string $name 名称
     * @throws Exception
     */
    public function __construct($model, $name = 'memcache') {
        if(is_string($name)) {
            $config = App::get('stores.'.$name);
        } else if(is_array($name)) {
            $config = $name;
        }
        if(is_subclass_of($model, 'lay\core\Expireable')) {
            parent::__construct($name, $model, $config);
        } else {
            Logger::error('error Expireable instance!');
        }
    }

    /**
     * 数据库连接对象
     * @var Connection
     */
    protected $connection;
    /**
     * 数据访问对象
     * @var Memcache
     */
    protected $link;
    /**
     * 模型对象
     * @var ModelExpire
     */
    protected $model;
    /**
     * 连接Mongo数据库
     * @return boolean
     */
    public function connect() {
        try {
            $this->connection = Connection::memcache($this->name, $this->config);
            $this->link = $this->connection->link;
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString());
            return false;
        }
        return true;
    }
    /**
     * 切换Memcache
     *
     * @param string $name
     *            名称
     * @return boolean
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $this->connection = Connection::memcache($name, $config);
            $this->link = $this->connection->link;
            return true;
        } else {
            return $this->connect();
        }
    }
    /**
     * do database querying
     *
     * @param mixed $sql
     *            SQL或其他查询结构
     * @param string $encoding
     *            编码
     * @param boolean $showinfo
     *            是否记录查询信息
     */
    public function query($sql, $encoding = '', $showinfo = false) {
        return false;
    }
    /**
     * select by id
     *
     * @param int|string $id
     *            the ID
     * @return array
     */
    public function get($id) {
        // TODO relations
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        if(! $link) {
            $this->connect();
        }
        if($model) {
            $table = $model->table();
            $pk = $model->primary();
            $key = $table.'.'.$pk.'.'.$id;
            $result = $this->link->get($key);
            $result = json_decode($result, true);
        } else {
            $result = $this->link->get($id);
        }
        return $this->toOne();
    }
    /**
     * delete by id
     *
     * @param int|string $id
     *            the ID
     * @return boolean
     */
    public function del($id) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        if(! $link) {
            $this->connect();
        }
        if($model) {
            $table = $model->table();
            $pk = $model->primary();
            $key = $table.'.'.$pk.'.'.$id;
        } else {
            $key = $id;
        }
        return $this->link->delete($key);
    }
    /**
     * always has primary key
     *
     * @param array $info
     *            information array
     * @return boolean
     */
    public function add(array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        if(! $link) {
            $this->connect();
        }
        
        if($model) {
            $table = $model->table();
            $columns = $model->columns();
            $pk = $model->primary();
            if(array_key_exists($pk, $info)) {
                $key = $table.'.'.$pk.'.'.$info[$pk];
            } else {
                $k = array_search($pk, $columns);
                if($k !== false && array_key_exists($k, $info)) {
                    $key = $table.'.'.$pk.'.'.$info[$k];
                }
            }
            if($key) {
                // Model, Expireable
                $m = clone $model;
                $m->distinct()->build($info);
                $result = $this->link->set($key, json_encode($m->toData()), 0, $m->getLifetime());
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }
    /**
     * update by primary id
     *
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     * @return boolean
     */
    public function upd($id, array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        if(! $link) {
            $this->connect();
        }
        
        if($model) {
            $table = $model->table();
            $columns = $model->columns();
            $pk = $model->primary();
            $key = $table.'.'.$pk.'.'.$id;
            $m = clone $model;
            $m->distinct()->build($info)->build(array($pk => $id));
            $lifetime = $m->getLifetime();
            $result = $this->link->set($key, json_encode($m->toData()), 0, $lifetime);
        } else {
            $key = $id;
            if(array_key_exists('lifetime', $info)) {
                $lifetime = $info['lifetime'];
                unset($info['lifetime']);
                $result = $this->link->set($key, json_encode((array)$info), 0, $lifetime);
            } else {
                $result = $this->link->set($key, json_encode((array)$info));
            }
        }
        return $result;
    }
    /**
     * 条件下记录数
     *
     * @param array $info
     *            information array
     * @return int
     */
    public function count(array $info = array()) {
        return false;
    }
    /**
     * close connection
     */
    public function close() {
        if($this->link)
            $this->link->close();
    }
    /**
     * 返回单一数据
     *
     * @return mixed
     */
    public function toScalar() {
        $row = array_values($this->toOne(true));
        return $row[0];
    }
    /**
     * 返回单条数据
     *
     * @param boolean $origin
     *            是否数据库原始数据
     * @return mixed
     */
    public function toOne($origin = false) {
        $row = array();
        $result = $this->result;
        $model = $this->model;
        if(!$result) {
            //
        } else {
            if($origin) {
                $row = (array)$result;
            } else {
                $obj = clone $model;
                $obj->distinct()->build($result);
                $row = $obj->toArray();
            }
        }
        return $row;
    }
}
?>
