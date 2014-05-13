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
        $port = isset($options['port']) ? $options['port'] : 3306;
        $username = isset($options['username']) ? $options['username'] : '';
        $password = isset($options['password']) ? $options['password'] : '';
        $newlink = isset($options['new']) ? $options['new'] : false;
        switch ($protocol) {
            case 'monogo':
                $this->connection = new MongoClient('mongodb://'. $host . ':' . $port, $options);
                break;
            case 'mysql':
            default:
                $this->connection = mysql_connect($host . ':' . $port, $username, $password, $newlink);
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
}
?>
