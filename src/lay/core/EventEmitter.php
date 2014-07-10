<?php
/**
 * 事件触发管理类
 *
 * @author Lay Li
 */
namespace lay\core;

use Exception;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 事件触发管理类
 *
 * @author Lay Li
 */
class EventEmitter extends AbstractSingleton {
    const L_LOW = 2;
    const L_MIDDLE = 1;
    const L_HIGH = 0;
    /**
     * 事件集合
     *
     * @var array
     */
    protected static $_EventStack = array();
    /**
     * 获取EventEmitter实例
     *
     * @return EventEmitter
     */
    public static function getInstance() {
        return parent::getInstance();
    }
    
    /**
     * 触发事件
     *
     * @param string $eventid
     *            事件名
     * @param array $params
     *            参数数组
     */
    public static function emit($eventid, array $params = array()) {
        self::getInstance()->trigger($eventid, $params);
    }
    /**
     * 注册事件
     *
     * @param string $eventid
     *            事件名
     * @param callable $func
     *            可执行函数或方法
     * @param number $level
     *            层级
     */
    public static function on($eventid, $func, $level = 0, $param = array()) {
        self::getInstance()->register($eventid, $func, $level, $param);
    }
    /**
     * 返回已经触发的事件名数组
     *
     * @return array
     */
    public static function emittedEvents() {
        return self::getInstance()->getEmittedEvents();
    }
    /**
     * 已经触发的事件名数组
     *
     * @var array
     */
    private $emittedEvents = array();
    /**
     * 返回已经触发的事件名数组
     *
     * @return array
     */
    public function getEmittedEvents() {
        return $this->emittedEvents;
    }
    /**
     * 触发事件，实现事件触发过程
     *
     * @param string $eventid
     *            事件名
     * @param array $params
     *            参数数组
     * @throws Exception
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
                } else if(is_array($func)) {
                    if(is_callable($func[0])) {
                        call_user_func_array($func[0], array_merge((array)$params, $func[1]));
                    }
                } else {
                    throw new Exception("Function not defined for EVENTS[$eventid][$level][$key]");
                }
            }
        }
    }
    /**
     * 注册事件
     *
     * @param string $eventid
     *            事件名
     * @param callable $func
     *            可执行函数或方法
     * @param number $level
     *            层级
     * @return boolean
     */
    public function register($eventid, $func, $level = 0, $param = array()) {
        // initialize
        if(! isset(self::$_EventStack[$eventid])) {
            self::$_EventStack[$eventid] = array();
        }
        $level = abs(intval($level));
        self::$_EventStack[$eventid][$level][] = array($func, (array)$param);
        ksort(self::$_EventStack[$eventid]);
        return true;
    }
}
?>
