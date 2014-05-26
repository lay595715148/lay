<?php
if(! defined('INIT_LAY')) {
    exit();
}
class Connection {
    public $name;
    public $encoding;
    public $connection;
    public function __construct($name, $protocol = 'mysql', $options = array()) {
        $host = isset($options['host']) ? $options['host'] : 'localhost';
        $username = isset($options['username']) ? $options['username'] : '';
        $password = isset($options['password']) ? $options['password'] : '';
        $newlink = isset($options['new']) ? $options['new'] : false;
        switch ($protocol) {
            case 'mongo':
                $port = isset($options['port']) ? intval($options['port']) : 27017;
                $server = "mongodb://$host:$port";
                $opts = array();
                $this->connection = new MongoClient($server, $opts);
                $this->connection->connect();
                break;
            case 'memcache':
                $port = isset($options['port']) ? intval($options['port']) : 11211;
                $timeout = isset($options['timeout']) ? $options['timeout'] : 1;
                $pool = isset($options['pool']) ? $options['pool'] : false;
                $this->connection = new Memcache();
                if(is_array($pool) && !empty($pool)) {
                    foreach ($pool as $p) {
                        $this->connection->addserver($p['host'], $p['port']);
                    }
                } else {
                    $this->connection->pconnect($host, $port, $timeout);
                }
                break;
            case 'mysql':
            default:
                $port = isset($options['port']) ? intval($options['port']) : 3306;
                $this->connection = mysqli_connect($host . ':' . $port, $username, $password, $newlink);
                break;
        }
        $this->name = $name;
    }
    private static $_Instances = array();
    /**
     * 
     * @param string $name
     * @param array $options
     * @param string $new if new instance of Connection
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
     * 
     * @param string $name
     * @param array $options
     * @param string $new if new instance of Connection
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
    public static function memcache($name, $options = array(), $new = false) {
        if($new) {
            return new Connection($name, 'memcache', $options);
        }
        if(empty(self::$_Instances[$name])) {
            self::$_Instances[$name] = new Connection($name, 'memcache', $options);
        }
        return self::$_Instances[$name];
    }
}
?>
