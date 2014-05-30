<?php
namespace lay\store;

use lay\App;
use lay\core\Connection;
use lay\core\Store;
use lay\core\I_Expireable;
use Exception;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * Memcache Store
 * @author Lay Li
 */
class MemcacheStore extends Store {
    public function __construct($model, $name = 'memcache') {
        if(is_string($name)) {
            $config = App::get('stores.'.$name);
        } else if(is_array($name)) {
            $config = $name;
        }
        if(is_subclass_of($model, 'lay\core\I_Expireable')) {
            parent::__construct($name, $model, $config);
        } else {
            throw new Exception('error I_Expireable instance!');
        }
    }

    /**
     *
     * @var Connection
     */
    private $connection;
    /**
     * 
     * @var Memcache
     */
    protected $link;
    /**
     * 
     * @var ModelExpire
     */
    protected $model;
    /**
     * 连接Mongo数据库
     */
    public function connect() {
        try {
            $this->connection = Connection::memcache($this->name, $this->config);
            $this->link = $this->connection->connection;
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
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $this->connection = Connection::memcache($name, $config);
            $this->link = $this->connection->connection;
        } else {
            $this->connect();
        }
    }
    /**
     * do database querying
     *
     * @param fixed $sql
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
     */
    public function get($id) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        $key = $table.'.'.$pk.'.'.$id;
        $result = $this->link->get($key);
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * delete by id
     *
     * @param int|string $id
     *            the ID
     */
    public function del($id) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        $key = $table.'.'.$pk.'.'.$id;
        return $this->link->delete($key);
    }
    /**
     * always has primary key
     *
     * @param array $info
     *            information array
     */
    public function add(array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $columns = $model->columns();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        if(array_key_exists($pk, $info)) {
            $key = $table.'.'.$pk.'.'.$info[$pk];
        } else {
            $k = array_search($pk, $columns);
            if($k !== false && array_key_exists($columns[$k], $info)) {
                $key = $table.'.'.$pk.'.'.$info[$columns[$k]];
            }
        }
        if($key) {
            // Model, I_Expireable
            $m = clone $this->model;
            $m->distinct()->build($info);
            $result = $this->link->set($key, json_encode($m->toData()), 0, $m->getLifetime());
            return $result;
        } else {
            return false;
        }
    }
    /**
     *
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     */
    public function upd($id, array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $columns = $model->columns();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        //$result = $this->get($id);
        $key = $table.'.'.$pk.'.'.$id;
        $m = clone $this->model;
        $m->distinct()->build($info)->build(array($pk => $id));
        $lifetime = $m->getLifetime();
        $result = $this->link->set($key, json_encode($m->toData()), 0, $lifetime);
        return $result;
    }
    /**
     *
     * @param array $info
     *            information array
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
}
?>
