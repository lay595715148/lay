<?php
namespace lay\core;

use \lay\App;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * <p>基础数据模型</p>
 * <p>核心类，继承至此类的对象将会拥有setter和getter方法和build方法</p>
 *
 * @abstract
 *
 * @author Lay Li
 */
abstract class Bean extends AbstractBean {
    const PROPETYPE_S_STRING = 'string';
    const PROPETYPE_STRING = 1;
    const PROPETYPE_S_NUMBER = 'number';
    const PROPETYPE_NUMBER = 2;
    const PROPETYPE_S_INTEGER = 'integer';
    const PROPETYPE_INTEGER = 3;
    const PROPETYPE_S_BOOLEAN = 'boolean';
    const PROPETYPE_BOOLEAN = 4;
    const PROPETYPE_S_DATETIME = 'datetime';
    const PROPETYPE_DATETIME = 5;
    const PROPETYPE_S_DATE = 'date';
    const PROPETYPE_DATE = 6;
    const PROPETYPE_S_TIME = 'time';
    const PROPETYPE_TIME = 7;
    const PROPETYPE_S_FLOAT = 'float';
    const PROPETYPE_FLOAT = 8;
    const PROPETYPE_S_DOUBLE = 'double';
    const PROPETYPE_DOUBLE = 9;
    const PROPETYPE_S_ARRAY = 'array';
    const PROPETYPE_ARRAY = 10;
    const PROPETYPE_S_DATEFORMAT = 'dataformat';
    const PROPETYPE_S_OTHER = 'other';
    /**
     * class properties and default value.
     * please don't modify in all methods except for '__construct','__set','__get' and so on.
     * example: array('id'=>0,'name'=>'')
     */
    protected $properties = array();
    /**
     * 构造方法
     *
     * @param array $properties            
     */
    public function __construct($properties) {
        if(is_array($properties)) {
            $this->properties = $properties;
        }
    }
    /**
     * isset property
     *
     * @param string $name            
     * @return bool
     */
    public function __isset($name) {
        return isset($this->properties[$name]);
    }
    /**
     * unset property
     *
     * @param string $name            
     * @return void
     */
    public function __unset($name) {
        unset($this->properties[$name]);
    }
    /**
     * magic setter,set value to class property
     *
     * @param string $name            
     * @param mixed $value            
     * @return void
     */
    public function __set($name, $value) {
        $propetypes = $this->rules();
        $properties = &$this->properties;
        
        if(array_key_exists($name, $properties)) {
            if(! empty($propetypes) && array_key_exists($name, $propetypes)) {
                switch($propetypes[$name]) {
                    case Model::PROPETYPE_STRING:
                    case Model::PROPETYPE_S_STRING:
                        $properties[$name] = strval($value);
                        break;
                    case Model::PROPETYPE_NUMBER:
                    case Model::PROPETYPE_S_NUMBER:
                        $properties[$name] = 0 + $value;
                        break;
                    case Model::PROPETYPE_INTEGER:
                    case Model::PROPETYPE_S_INTEGER:
                        $properties[$name] = intval($value);
                        break;
                    case Model::PROPETYPE_BOOLEAN:
                    case Model::PROPETYPE_S_BOOLEAN:
                        $properties[$name] = boolval($value);
                        break;
                    case Model::PROPETYPE_DATETIME:
                    case Model::PROPETYPE_S_DATETIME:
                        if(is_numeric($value)) {
                            $properties[$name] = date('Y-m-d H:i:s', intval($value));
                        } else if(is_string($value)) {
                            $properties[$name] = date('Y-m-d H:i:s', strtotime($value));
                        }
                        break;
                    case Model::PROPETYPE_DATE:
                    case Model::PROPETYPE_S_DATE:
                        if(is_numeric($value)) {
                            $properties[$name] = date('Y-m-d', intval($value));
                        } else if(is_string($value)) {
                            $properties[$name] = date('Y-m-d', strtotime($value));
                        }
                        break;
                    case Model::PROPETYPE_TIME:
                    case Model::PROPETYPE_S_TIME:
                        if(is_numeric($value)) {
                            $properties[$name] = date('H:i:s', intval($value));
                        } else if(is_string($value)) {
                            $properties[$name] = date('H:i:s', strtotime($value));
                        }
                        break;
                    case Model::PROPETYPE_FLOAT:
                    case Model::PROPETYPE_S_FLOAT:
                        $properties[$name] = floatval($value);
                        break;
                    case Model::PROPETYPE_DOUBLE:
                    case Model::PROPETYPE_S_DOUBLE:
                        $properties[$name] = doubleval($value);
                        break;
                    case Model::PROPETYPE_ARRAY:
                    case Model::PROPETYPE_S_ARRAY:
                        if(is_array($value)) {
                            $properties[$name] = $value;
                        } else {
                            Logger::error('invalid value,property:' . $name . '\'s value must be an array in class:' . get_class($this), 'MODEL');
                        }
                        break;
                    default:
                        if(is_array($propetypes[$name])) {
                            if(array_key_exists(Model::PROPETYPE_S_DATEFORMAT, $propetypes[$name])) {
                                // 自定义日期格式
                                $dateformart = $propetypes[$name][Model::PROPETYPE_S_DATEFORMAT];
                                if(is_numeric($value)) {
                                    $properties[$name] = date($dateformart, intval($value));
                                } else if(is_string($value)) {
                                    $properties[$name] = date($dateformart, strtotime($value));
                                }
                            } else if(array_key_exists(Model::PROPETYPE_S_OTHER, $propetypes[$name])) {
                                // other
                                $properties[$name] = $this->otherFormat($value, $propetypes[$name]);
                            } else {
                                // enum
                                $key = array_search($value, $propetypes[$name]);
                                if($key !== false) {
                                    $properties[$name] = $propetypes[$name][$key];
                                } else {
                                    Logger::error('invalid value,it is not in class:' . get_class($this) . ' $propetypes', 'MODEL');
                                }
                            }
                        } else {
                            $properties[$name] = $value;
                        }
                        break;
                }
            } else {
                $properties[$name] = $value;
            }
        } else {
            Logger::error('There is no property:' . $name . ' in class:' . get_class($this), 'MODEL');
        }
    }
    /**
     * please implement this method in sub class
     *
     * @return mixed
     */
    protected function otherFormat($value, $propertype) {
        return $value;
    }
    /**
     * magic setter,get value of class property
     *
     * @param string $name            
     * @return mixed void
     */
    public function &__get($name) {
        $properties = &$this->properties;
        
        if(array_key_exists($name, $properties)) {
            return $properties[$name];
        } else {
            Logger::error('There is no property:' . $name . ' in class:' . get_class($this), 'MODEL');
        }
    }
    /**
     * magic call method,auto call setter or getter
     *
     * @param string $method            
     * @param array $arguments            
     * @return mixed void
     */
    public function __call($method, $arguments) {
        if(method_exists($this, $method)) {
            return call_user_func_array(array(
                    $this,
                    $method
            ), $arguments);
        } else {
            $keys = array_keys($this->properties);
            $lower = array(); // setter和getter方法中不区分大小写时使用
            foreach($keys as $i => $key) {
                $lower[$i] = strtolower($key);
            }
            
            if(strtolower(substr($method, 0, 3)) === 'get') {
                $proper = strtolower(substr($method, 3));
                $index = array_search($proper, $lower);
                if($index !== null) {
                    return $this->{$keys[$index]};
                } else {
                    return $this->{$proper};
                }
            } else if(strtolower(substr($method, 0, 3)) === 'set') {
                $proper = strtolower(substr($method, 3));
                $index = array_search($proper, $lower);
                if($index !== null) {
                    $this->{$keys[$index]} = $arguments[0];
                } else {
                    $this->{$proper} = $arguments[0];
                }
            } else {
                Logger::error('There is no method:' . $method . '( ) in class:' . get_class($this), 'MODEL');
            }
        }
    }
    public function __toString() {
        return serialize($this);
    }
    /**
     * 字段属性规则，过滤属性，子类需要实现此方法
     *
     * class property types.
     * string[1,'string'],number[2,'number'],integer[3,'integer'],boolean[4,'boolean'],datetime[5,'datetime'],
     * date[6,'date'],time[7,'time'],float[8,'float'],double[9,'double'],enum[array(1,2,3)],dateformat[array('dateformat'=>'Y-m-d')],other[array('other'=>...)]...
     * default nothing to do
     * example: array('id'=>'integer','name'=>0)
     *
     * @return array
     */
    protected function rules() {
        return array();
    }
    
    /**
     * return array values of class properties
     *
     * @return array
     */
    public function toProperties() {
        return array_keys($this->properties);
    }
    /**
     * empty this object
     *
     * @return Bean
     */
    public function distinct() {
        $propetypes = $this->rules();
        $properties = &$this->properties;
        foreach($this->properties as $name => $v) {
            if(! empty($propetypes) && array_key_exists($name, $propetypes)) {
                switch($propetypes[$name]) {
                    case Model::PROPETYPE_STRING:
                    case Model::PROPETYPE_S_STRING:
                        $properties[$name] = '';
                        break;
                    case Model::PROPETYPE_NUMBER:
                    case Model::PROPETYPE_S_NUMBER:
                        $properties[$name] = 0;
                        break;
                    case Model::PROPETYPE_INTEGER:
                    case Model::PROPETYPE_S_INTEGER:
                        $properties[$name] = 0;
                        break;
                    case Model::PROPETYPE_BOOLEAN:
                    case Model::PROPETYPE_S_BOOLEAN:
                        $properties[$name] = false;
                        break;
                    case Model::PROPETYPE_DATETIME:
                    case Model::PROPETYPE_S_DATETIME:
                        $properties[$name] = '0000-00-00 00:00:00';
                        break;
                    case Model::PROPETYPE_DATE:
                    case Model::PROPETYPE_S_DATE:
                        $properties[$name] = '0000-00-00';
                        break;
                    case Model::PROPETYPE_TIME:
                    case Model::PROPETYPE_S_TIME:
                        $properties[$name] = '00:00:00';
                        break;
                    case Model::PROPETYPE_FLOAT:
                    case Model::PROPETYPE_S_FLOAT:
                        $properties[$name] = 0.0;
                        break;
                    case Model::PROPETYPE_DOUBLE:
                    case Model::PROPETYPE_S_DOUBLE:
                        $properties[$name] = 0.0;
                        break;
                    case Model::PROPETYPE_ARRAY:
                    case Model::PROPETYPE_S_ARRAY:
                        $properties[$name] = array();
                        break;
                    default:
                        if(is_array($propetypes[$name])) {
                            if(array_key_exists(Model::PROPETYPE_S_DATEFORMAT, $propetypes[$name])) {
                                // 自定义日期格式
                                $dateformart = $propetypes[$name][Model::PROPETYPE_S_DATEFORMAT];
                                $properties[$name] = date($dateformart, 0);
                            } else if(array_key_exists(Model::PROPETYPE_S_OTHER, $propetypes[$name])) {
                                // other
                                $properties[$name] = $this->otherFormat('', $propetypes[$name]);
                            } else {
                                // enum
                                $properties[$name] = array_shift(array_values($propetypes[$name]));
                            }
                        } else {
                            $properties[$name] = '';
                        }
                        break;
                }
            } else {
                if(is_string($v)) {
                    $properties[$name] = '';
                } else if(is_double($v)) {
                    $properties[$name] = 0.0;
                } else if(is_int($v)) {
                    $properties[$name] = 0;
                } else if(is_numeric($v)) {
                    $properties[$name] = 0;
                } else if(is_bool($v)) {
                    $properties[$name] = false;
                } else if(is_array($v)) {
                    $properties[$name] = array();
                } else if(is_object($v) || is_resource($v)) {
                    $properties[$name] = '';
                } else {
                    $properties[$name] = '';
                }
            }
        }
        return $this;
    }
    /**
     * return array values of class properties
     *
     * @return array
     */
    public function toArray() {
        return $this->properties;
    }
    /**
     * return array values of class properties
     *
     * @return array
     */
    public function toObject() {
        $o = new stdClass();
        foreach($this->properties as $k => $v) {
            $o->{$k} = $v;
        }
        return $o;
    }
    
    /**
     * read values from variables(super global varibles or user-defined variables) then auto inject to this.
     * default read from $_REQUEST
     *
     * @param integer|array $scope            
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
}
?>
