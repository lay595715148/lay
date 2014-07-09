<?php
namespace lay\core;

use lay\App;
use lay\util\Logger;
class FilterChain {
    private static $_Instance;
    public static function getInstance() {
        if(empty(self::$_Instance)) {
            self::$_Instance = new FilterChain();
        }
        return self::$_Instance;
    }
    private $filters = array();
    private $current;
    private function __construct() {
    }
    public function initilize($configs = array()) {
        $configs = is_array($configs) && !empty($configs)?  :App::get('filters', array());
        foreach ($configs as $config) {
            $classname = $config['classname'];
            $priority = floatval($config['priority']);
            if(class_exists($classname) && is_subclass_of($classname, 'lay\core\Filter')) {
                $filter = new $classname();
                $filter->initilize($config);
                $this->filters[$priority] = $filter;
            }
        }
        $this->sort();
        $this->reset();
        return $this;
    }
    public function doFilter($action) {
        $filter = $this->current();
        $next = $this->next();
        if($filter && is_a($filter, 'lay\core\Filter')) {
            $filter->doFilter($action, $this);
        }
    }
    private function next() {
        return next($this->filters);
    }
    private function current() {
        return current($this->filters);
    }
    private function reset() {
        return reset($this->filters);
    }
    private function sort() {
        return ksort($this->filters, SORT_DESC);
    }
}
?>
