<?php

/**
 * 基础数据类，继承此类时需要在构造方法中传递以属性名对应默认属性值的数组给受保护的$properties
 *
 * @abstract
 * @author Lay Li
 */
namespace lay\core;

use lay\App;
use stdClass;
use lay\util\Util;
use Iterator;
use ArrayAccess;
use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 基础数据类，继承此类时需要在构造方法中传递以属性名对应默认属性值的数组给受保护的$properties
 *
 * @abstract
 *
 * @author Lay Li
 */
abstract class Bean extends AbstractBean implements Iterator, ArrayAccess {
    /**
     * 定义字符串类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_STRING = 'string';
    /**
     * 定义字符串类型的属性值
     *
     * @var int
     */
    const PROPETYPE_STRING = 1;
    /**
     * 定义数值类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_NUMBER = 'number';
    /**
     * 定义数值类型的属性值
     *
     * @var int
     */
    const PROPETYPE_NUMBER = 2;
    /**
     * 定义整数类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_INTEGER = 'integer';
    /**
     * 定义整数类型的属性值
     *
     * @var int
     */
    const PROPETYPE_INTEGER = 3;
    /**
     * 定义布尔类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_BOOLEAN = 'boolean';
    /**
     * 定义布尔类型的属性值
     *
     * @var int
     */
    const PROPETYPE_BOOLEAN = 4;
    /**
     * 定义日期时间类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_DATETIME = 'datetime';
    /**
     * 定义日期时间类型的属性值
     *
     * @var int
     */
    const PROPETYPE_DATETIME = 5;
    /**
     * 定义日期类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_DATE = 'date';
    /**
     * 定义日期类型的属性值
     *
     * @var int
     */
    const PROPETYPE_DATE = 6;
    /**
     * 定义时间类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_TIME = 'time';
    /**
     * 定义时间类型的属性值
     *
     * @var int
     */
    const PROPETYPE_TIME = 7;
    /**
     * 定义浮点数类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_FLOAT = 'float';
    /**
     * 定义浮点数类型的属性值
     *
     * @var int
     */
    const PROPETYPE_FLOAT = 8;
    /**
     * 定义double类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_DOUBLE = 'double';
    /**
     * 定义double类型的属性值
     *
     * @var int
     */
    const PROPETYPE_DOUBLE = 9;
    /**
     * 定义数组类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_ARRAY = 'array';
    /**
     * 定义数组类型的属性值
     *
     * @var int
     */
    const PROPETYPE_ARRAY = 10;
    /**
     * 定义数组类型的属性值
     *
     * @var int
     */
    const PROPETYPE_S_PURE_ARRAY = 'pure_array';
    /**
     * 定义数组类型的属性值
     *
     * @var int
     */
    const PROPETYPE_PURE_ARRAY = 11;
    /**
     * 定义特定格式类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_DATEFORMAT = 'dateformat';
    /**
     * 定义其他类型的属性值
     *
     * @var string
     */
    const PROPETYPE_S_OTHER = 'other';
    /**
     * 构造方法
     *
     * @param array $properties
     *            属性名对默认属性值的数组
     */
    public function __construct() {
    }
    /**
     * 检测属性是否设置
     *
     * @param string $name
     *            属性名
     * @return boolean
     */
    public function __isset($name) {
        //$properties = $this->properties();
        return array_key_exists($name, $this->properties());
    }
    /**
     * 将某个属性去除
     *
     * @param string $name
     *            属性名
     * @return void
     */
    public function __unset($name) {
        return false;
    }
    /**
     * 设置对象属性值的魔术方法
     *
     * @param string $name
     *            属性名
     * @param mixed $value
     *            属性值
     * @return void
     */
    public function __set($name, $value) {
        $propetypes = $this->rules();
        //$properties = $this->properties();
        if(isset($this->$name)) {
            if(! empty($propetypes) && array_key_exists($name, $propetypes)) {
                switch($propetypes[$name]) {
                    case Bean::PROPETYPE_STRING:
                    case Bean::PROPETYPE_S_STRING:
                        $this->$name = strval($value);
                        break;
                    case Bean::PROPETYPE_NUMBER:
                    case Bean::PROPETYPE_S_NUMBER:
                        $this->$name = 0 + $value;
                        break;
                    case Bean::PROPETYPE_INTEGER:
                    case Bean::PROPETYPE_S_INTEGER:
                        $this->$name = intval($value);
                        break;
                    case Bean::PROPETYPE_BOOLEAN:
                    case Bean::PROPETYPE_S_BOOLEAN:
                        $this->$name = $value ? true : false;
                        break;
                    case Bean::PROPETYPE_DATETIME:
                    case Bean::PROPETYPE_S_DATETIME:
                        if(is_numeric($value)) {
                            $this->$name = date('Y-m-d H:i:s', intval($value));
                        } else if(is_string($value)) {
                            $this->$name = date('Y-m-d H:i:s', strtotime($value));
                        }
                        break;
                    case Bean::PROPETYPE_DATE:
                    case Bean::PROPETYPE_S_DATE:
                        if(is_numeric($value)) {
                            $this->$name = date('Y-m-d', intval($value));
                        } else if(is_string($value)) {
                            $this->$name = date('Y-m-d', strtotime($value));
                        }
                        break;
                    case Bean::PROPETYPE_TIME:
                    case Bean::PROPETYPE_S_TIME:
                        if(is_numeric($value)) {
                            $this->$name = date('H:i:s', intval($value));
                        } else if(is_string($value)) {
                            $this->$name = date('H:i:s', strtotime($value));
                        }
                        break;
                    case Bean::PROPETYPE_FLOAT:
                    case Bean::PROPETYPE_S_FLOAT:
                        $this->$name = floatval($value);
                        break;
                    case Bean::PROPETYPE_DOUBLE:
                    case Bean::PROPETYPE_S_DOUBLE:
                        $this->$name = doubleval($value);
                        break;
                    case Bean::PROPETYPE_ARRAY:
                    case Bean::PROPETYPE_S_ARRAY:
                        if(is_array($value)) {
                            $this->$name = $value;
                        } else {
                            Logger::error('invalid value,property:' . $name . '\'s value must be an array in class:' . get_class($this), 'MODEL');
                        }
                        break;
                    case Bean::PROPETYPE_PURE_ARRAY:
                    case Bean::PROPETYPE_S_PURE_ARRAY:
                        if(is_array($value)) {
                            $this->{$name} = Util::toPureArray($value);
                        } else {
                            Logger::error('invalid value,property:' . $name . '\'s value must be an pure array in class:' . get_class($this), 'MODEL');
                        }
                        break;
                    default:
                        if(is_array($propetypes[$name])) {
                            if(array_key_exists(Bean::PROPETYPE_S_DATEFORMAT, $propetypes[$name])) {
                                // 自定义日期格式
                                $dateformart = $propetypes[$name][Bean::PROPETYPE_S_DATEFORMAT];
                                if(is_numeric($value)) {
                                    $this->$name = date($dateformart, intval($value));
                                } else if(is_string($value)) {
                                    $this->$name = date($dateformart, strtotime($value));
                                }
                            } else if(array_key_exists(Bean::PROPETYPE_S_OTHER, $propetypes[$name])) {
                                // other
                                $this->$name = $this->otherFormat($value, $propetypes[$name][Bean::PROPETYPE_S_OTHER]);
                            } else {
                                // enum
                                $key = array_search($value, $propetypes[$name]);
                                if($key !== false) {
                                    $this->$name = $propetypes[$name][$key];
                                } else {
                                    Logger::error('invalid value,it is not in class:' . get_class($this) . ' $propetypes', 'MODEL');
                                }
                            }
                        } else {
                            $this->$name = $value;
                        }
                        break;
                }
            } else {
                $this->$name = $value;
            }
        } else {
            Logger::error('There is no property:' . $name . ' in class:' . get_class($this), 'MODEL');
        }
    }
    /**
     * 获取对象属性值的魔术方法
     *
     * @see \lay\core\AbstractObject::__get()
     * @param string $name
     *            属性名
     * @return mixed
     */
    public function &__get($name) {
        if(isset($this->$name)) {
            return $this->$name;
        } else {
            Logger::error('There is no property:' . $name . ' in class:' . get_class($this), 'MODEL');
        }
    }
    /**
     * 其他类型属性赋值时调用的方法
     *
     * @param mixed $value
     *            值
     * @param mixed $propertype
     *            定义为其他类型的规则类型
     * @return mixed
     */
    protected function otherFormat($value, $propertype) {
        return $value;
    }
    /**
     * 魔术方法，实现属性的set和get方法
     *
     * @param string $method
     *            方法名
     * @param array $arguments
     *            参数数组
     * @return mixed
     */
    public function __call($method, $arguments) {
        if(method_exists($this, $method)) {
            return call_user_func_array(array(
                    $this,
                    $method
            ), $arguments);
        } else {
            if(strtolower(substr($method, 0, 3)) === 'get') {
                $proper = lcfirst(substr($method, 3));
                return $this->{$proper};
            } else if(strtolower(substr($method, 0, 3)) === 'set') {
                $proper = lcfirst(substr($method, 3));
                $this->{$proper} = $arguments[0];
            } else {
                Logger::error('There is no method:' . $method . '( ) in class:' . get_class($this), 'MODEL');
            }
        }
    }
    /**
     * 返回序列化后的字符串
     *
     * @return string
     */
    public function __toString() {
        return serialize($this);
    }
    /**
     * 属性类型规则，过滤属性值，子类需要重写此方法
     *
     * 属性类型如下：
     * 字符串：1或string；数值：2或number；整数：3或integer；布尔：4或boolean；日期时间：5或datetime，（格式为：Y-m-d H:i:s）；
     * 日期：6或date，（格式为：Y-m-d）；时间：7或time，（格式为：H:i:s）；浮点数值：8或float；双精度数值：9或double；数组：10或array；
     * 枚举：使用纯数组，如array(1,3,5)；特定日期时间：代有dateformat键名的数组，如array('dateformat'=>'Y-m-d')；
     * 其他：代有dateformat键名的数组，如array('other'=>'other type')，（如果是其他类型则会在赋值过程中使用otherFormat()方法处理）；
     * 如果某个属性没有设置规则或非以上规则，则属性赋值时不做任何处理。
     * 例子: array('id'=>'integer','name'=>0)
     *
     * @return array
     */
    protected function rules() {
        return array();
    }
    
    /**
     * 清空对象所有属性值
     *
     * @see \lay\core\AbstractBean::distinct()
     * @return Bean
     */
    public function distinct() {
        $propetypes = $this->rules();
        $properties = $this->properties();
        foreach ($properties as $name => $v) {
            $this->$name = $v;
        }
        return $this;
    }
    
    /**
     * 返回对象属性名对属性值的数组
     *
     * @return array
     */
    public function toArray() {
        $a = array();
        $properties = $this->properties();
        foreach($properties as $key => $def) {
            $val = $this->$key;
            if(is_a($val, 'lay\core\Bean')) {
                $a[$key] = $val->toArray();
            } else if(is_array($val)) {
                $a[$key] = $this->_toArray($val);
            } else {
                $a[$key] = $val;
            }
        }
        return $a;
    }
    /**
     * 返回对象转换为stdClass后的对象
     *
     * @see \lay\core\AbstractBean::toObject()
     * @return stdClass
     */
    public function toStdClass() {
        $o = new stdClass();
        $properties = $this->properties();
        foreach($properties as $key => $def) {
            $val = $this->$key;
            if(is_a($val, 'lay\core\Bean')) {
                $o->{$key} = $val->toStdClass();
            } else if(is_array($val)) {
                $o->{$key} = $this->_toStdClass($val);
            } else {
                $o->{$key} = $val;
            }
        }
        return $o;
    }
    /**
     * 将数据中包含Bean的子对象转换成stdClass
     *
     * @param mixed $var            
     * @return mixed
     */
     protected function _toArray($var) {
        if(is_array($var)) {
            foreach($var as $k => $v) {
                $var[$k] = $this->_toArray($v);
            }
            return $var;
        } else if(is_a($var, 'lay\core\Bean')) {
            return $var->toArray();
        } else {
            return $var;
        }
    }
    /**
     * 将数据中包含Bean的子对象转换成stdClass
     *
     * @param mixed $var            
     * @return mixed
     */
    protected function _toStdClass($var) {
        if(is_array($var)) {
            foreach($var as $k => $v) {
                $var[$k] = $this->_toStdClass($v);
            }
            return $var;
        } else if(is_a($var, 'lay\core\Bean')) {
            return $var->toStdClass();
        } else {
            return $var;
        }
    }
    
    /**
     * 将数组中的数据注入到对象中
     *
     * @see \lay\core\AbstractBean::build()
     * @param array $data
     *            数组数据
     * @return Bean
     */
    public function build($data) {
        if(is_array($data)) {
            foreach($this->toArray() as $k => $v) {
                if(array_key_exists($k, $data)) {
                    $this->$k = $data[$k];
                }
            }
        }
        return $this;
    }
    /**
     * (non-PHPdoc)
     * 
     * @see Iterator::current()
     */
    public function current() {
        return current($this);
    }
    /**
     * (non-PHPdoc)
     * 
     * @see Iterator::next()
     */
    public function next() {
        return next($this);
    }
    /**
     * (non-PHPdoc)
     * 
     * @see Iterator::key()
     */
    public function key() {
        return key($this);
    }
    /**
     * (non-PHPdoc)
     * 
     * @see Iterator::valid()
     */
    public function valid() {
        return key($this) !== null;
    }
    /**
     * (non-PHPdoc)
     * 
     * @see Iterator::rewind()
     */
    public function rewind() {
        return reset($this);
    }
    public function offsetExists ($index) {
        return isset($this->$index);
    }

    public function offsetGet ($index) {
        return $this->$index;
    }

    public function offsetSet ($index, $value) {
        $this->$index = $value;
    }

    public function offsetUnset ($index) {
        return false;
    }
    /**
     * json serialize function
     * 
     * @return stdClass
     */
    public function jsonSerialize() {
        return $this->toStdClass();
    }
}
?>
