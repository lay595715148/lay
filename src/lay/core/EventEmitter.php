<?php
if(! defined('INIT_LAY'))
    exit();

class EventEmitter implements I_EventEmitter {
    protected static $_EventStack = array();
    private static $_Instance;
    public static function getInstance() {
        if(!self::$_Instance) {
            self::$_Instance = new EventEmitter();
        }
        return self::$_Instance;
    }
    private function __construct() {
    }
    
    /**
     *
     * @param int|string $eventid            
     */
    public static function emit($eventid) {
        if(!self::$_Instance) {
            $classname = Lay::get('register_eventemitter');
            if($classname) {
                try {
                    self::$_Instance = new $classname();
                } catch (Exception $e) {
                    //
                }
            }
        }
        self::getInstance()->trigger($eventid);
    }
    public static function on($eventid, $func, array $params = array(), $level = 0) {
        if(!self::$_Instance) {
            $classname = Lay::get('register_eventemitter');
            if($classname) {
                try {
                    self::$_Instance = new $classname();
                } catch (Exception $e) {
                    //
                }
            }
        }
        self::getInstance()->register($eventid, $func, $params, $level);
    }
    /**
     * 实现事件触发
     * @see I_EventEmitter::trigger()
     */
    public function trigger($eventid) {
        if(! isset(self::$_EventStack[$eventid])) {
            return;
        }
        foreach(self::$_EventStack[$eventid] as $level => $events) {
            foreach($events as $key => $e) {
                if(is_callable($e['func'])) {
                    call_user_func_array($e['func'], $e['params']);
                } else {
                    throw new Exception("Function not defined for EVENTS[$eventid][$level][$key]");
                }
            }
        }
    }
    /**
     * 注册事件
     * @see I_EventEmitter::register()
     */
    public function register($eventid, $func, array $params = array(), $level = 0) {
        // initialize
        if(! isset(self::$_EventStack[$eventid])) {
            self::$_EventStack[$eventid] = array();
        }
        $level = abs(intval($level));
        self::$_EventStack[$eventid][$level][] = array(
                'func' => $func,
                'params' => $params
        );
        return true;
    }
}
?>
