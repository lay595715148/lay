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
    
    /**
     *
     * @param int|string $eventid            
     */
    public static function emit($eventid, array $params = array()) {
        if(!self::$_Instance) {
            $classname = App::get('register_eventemitter');
            if($classname) {
                try {
                    self::$_Instance = new $classname();
                } catch (Exception $e) {
                    //
                }
            }
        }
        self::getInstance()->trigger($eventid, $params);
    }
    public static function on($eventid, $func, $level = 0) {
        if(!self::$_Instance) {
            $classname = App::get('register_eventemitter');
            if($classname) {
                try {
                    self::$_Instance = new $classname();
                } catch (Exception $e) {
                    //
                }
            }
        }
        self::getInstance()->register($eventid, $func, $level);
    }
    public static function emittedEvents() {
        return self::getInstance()->getEmittedEvents();
    }
    private $emittedEvents = array();
    private function __construct() {
    }
    public function getEmittedEvents() {
        return $this->emittedEvents;
    }
    /**
     * 实现事件触发
     * @see I_EventEmitter::trigger()
     */
    public function trigger($eventid, array $params = array()) {
        $this->emittedEvents[] = $eventid;
        if(! isset(self::$_EventStack[$eventid])) {
            return;
        }
        foreach(self::$_EventStack[$eventid] as $level => $events) {
            foreach($events as $key => $func) {
                if(is_callable($func)) {
                    call_user_func_array($func, (array)$params);
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
    public function register($eventid, $func, $level = 0) {
        // initialize
        if(! isset(self::$_EventStack[$eventid])) {
            self::$_EventStack[$eventid] = array();
        }
        $level = abs(intval($level));
        self::$_EventStack[$eventid][$level][] = $func;
        return true;
    }
}
?>
