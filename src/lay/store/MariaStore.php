<?php
/**
 * 操作maria数据库类
 * 
 * @author Lay Li
 */
namespace lay\store;

use lay\App;
use lay\core\Connection;
use lay\core\Criteria;
use lay\core\Store;
use lay\util\Logger;
use Exception;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 操作maria数据库类
 * 
 * @author Lay Li
 */
class MariaStore extends MysqlStore {
    /**
     * 构造方法
     * 
     * @param Model $model Model
     * @param string $name 名称
     */
    public function __construct($model, $name = 'maria') {
        parent::__construct($name, $model, $config);
    }
    /**
     * 执行连接maria数据库
     */
    public function connect() {
        try {
            $this->connection = Connection::maria($this->name, $this->config);
            $this->link = $this->connection->link;
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString(), 'MARIA');
            return false;
        }
        return mysqli_select_db($this->link, $this->schema);
    }
    /**
     * 切换maria数据库
     *
     * @param string $name
     *            名称
     * @return boolean
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $schema = isset($config['schema']) && is_string($config['schema']) ? $config['schema'] : '';
            $this->connection = Connection::maria($name, $config);
            $this->link = $this->connection->link;
            return mysqli_select_db($this->link, $schema);
        } else {
            return $this->connect();
        }
    }
}
?>
