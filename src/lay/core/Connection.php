<?php
/**
 * 数据库连接管理类
 * 
 * @author Lay Li
 */
namespace lay\core;

use MongoClient;
use Memcache;
use Mongo;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 数据库连接管理类
 * 
 * @author Lay Li
 */
class Connection {
    /**
     * 名称
     * 
     * @var string
     */
    public $name;
    /**
     * 名称
     * 
     * @var string
     */
    public $encoding;
    /**
     * 数据库连接句柄
     * 
     * @var mixed
     */
    public $link;
    /**
     * 构造方法
     * 
     * @param string $name 名称
     * @param string $protocol 协议名，即数据库标识符
     * @param array $options 可选项
     */
    public function __construct($name, $protocol = 'mysql', $options = array()) {
        $host = isset($options['host']) ? $options['host'] : 'localhost';
        $username = isset($options['username']) ? $options['username'] : '';
        $password = isset($options['password']) ? $options['password'] : '';
        $newlink = isset($options['new']) ? $options['new'] : false;
        switch($protocol) {
            case 'mongo':
                $port = isset($options['port']) ? intval($options['port']) : 27017;
                $server = "mongodb://$host:$port";
                $opts = array();
                if(class_exists('MongoClient', false)) {
                    $this->link = new MongoClient($server, $opts);
                } else {
                    $this->link = new Mongo($server, $opts);
                }
                $this->link->connect();
                break;
            case 'memcache':
                $port = isset($options['port']) ? intval($options['port']) : 11211;
                $timeout = isset($options['timeout']) ? $options['timeout'] : 1;
                $pool = isset($options['pool']) ? $options['pool'] : false;
                $this->link = new Memcache();
                if(is_array($pool) && ! empty($pool)) {
                    foreach($pool as $p) {
                        $this->link->addserver($p['host'], $p['port']);
                    }
                } else {
                    $this->link->pconnect($host, $port, $timeout);
                }
                break;
            case 'mysql':
            case 'maria':
            default:
                $port = isset($options['port']) ? intval($options['port']) : 3306;
                $this->link = mysqli_connect($host . ':' . $port, $username, $password, $newlink);
                break;
        }
        $this->name = $name;
    }
    /**
     * 数据库连接数组池
     *
     * @var array
     */
    private static $_Instances = array();
    /**
     * 获取mysql连接
     * 
     * @param string $name
     *            名称
     * @param array $options
     *            options
     * @param string $new
     *            if new instance of Connection
     * @return Connection
     */
    public static function mysql($name, $options = array(), $new = false) {
        if($new) {
            return new Connection($name, 'mysql', $options);
        }
        if(empty(self::$_Instances[$name])) {
            self::$_Instances[$name] = new Connection($name, 'mysql', $options);
        }
        return self::$_Instances[$name];
    }
    /**
     * 获取mongodb连接
     * 
     * @param string $name
     *            名称
     * @param array $options
     *            options
     * @param string $new
     *            if new instance of Connection
     * @return Connection
     */
    public static function mongo($name, $options = array(), $new = false) {
        if($new) {
            return new Connection($name, 'mongo', $options);
        }
        if(empty(self::$_Instances[$name])) {
            self::$_Instances[$name] = new Connection($name, 'mongo', $options);
        }
        return self::$_Instances[$name];
    }
    /**
     * 获取memcache连接
     * 
     * @param string $name
     *            名称
     * @param array $options
     *            options
     * @param string $new
     *            if new instance of Connection
     * @return Connection
     */
    public static function memcache($name, $options = array(), $new = false) {
        if($new) {
            return new Connection($name, 'memcache', $options);
        }
        if(empty(self::$_Instances[$name])) {
            self::$_Instances[$name] = new Connection($name, 'memcache', $options);
        }
        return self::$_Instances[$name];
    }
    /**
     * 获取maria连接
     * 
     * @param string $name
     *            名称
     * @param array $options
     *            options
     * @param string $new
     *            if new instance of Connection
     * @return Connection
     */
    public static function maria($name, $options = array(), $new = false) {
        if($new) {
            return new Connection($name, 'maria', $options);
        }
        if(empty(self::$_Instances[$name])) {
            self::$_Instances[$name] = new Connection($name, 'maria', $options);
        }
        return self::$_Instances[$name];
    }
}
?>
