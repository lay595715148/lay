<?php
namespace lay\core;

use lay\App;
use lay\util\Logger;
use lay\entity\Response;
use lay\entity\Lister;

if(! defined('INIT_LAY'))
    exit();


/**
 * 模板引擎基础类
 * @abstract
 */
class Template extends AbstractTemplate {
    const TEMPLATE_PROVIDER_CONFIG_TAG = 'template-provider';
    /**
     * Template instance
     * @staticvar Template
     */
    private static $instance = null;
    /**
     * get Template instance 
     * @param $name name of Template
     * @param $config default is empty
     * @return Template
     */
    public static function getInstance($name = '') {
        if(self::$instance == null) {
            //使用默认的配置项进行实现
            if(!(self::$instance instanceof Template)) {
                $config = App::getTemplateConfig($name);
                $config = is_array($name)?$name:App::getTemplateConfig($name);
                $classname = isset($config['classname'])?$config['classname']:'DemoTemplate';
                if(isset($config['classname'])) {
                    self::$instance = new $classname($config);
                }
                if(!(self::$instance instanceof Template)) {
                    Logger::warn('template has been instantiated by default DemoTemplate', 'TEMPLATE');
                    self::$instance = new DemoTemplate($config);
                }
            }
        }
        return self::$instance;
    }
    /**
     * Action对象
     * @var Action $action
     */
    public $action;
    /**
     * 输出变量内容数组
     * @var array $vars
     */
    protected $vars = array();
    /**
     * HTTP headers
     * @var array $headers
     */
    protected $headers = array();
    /**
     * HTML metas
     * @var array $metas
     */
    protected $metas = array();
    /**
     * HTML scripts
     * @var array $jses
     */
    protected $jses = array();
    /**
     * HTML scripts in the end
     * @var array $javascript
     */
    protected $javascript = array();
    /**
     * HTML css links
     * @var array $csses
     */
    protected $csses = array();
    /**
     * file path
     * @var string $file
     */
    protected $file;
    /**
     * 构造方法
     * @param array $config 配置信息数组
     */
    public function __construct() {
    }
    /**
     * push header for output
     * @param string $header http header string
     */
    public function header($header) {
        $this->headers[] = $header;
    }
    /**
     * set title ,if $append equal false, then reset title;if $append equal 1 or true,
     * then append end position; other append start position
     * @param string $str title
     * @param boolean $append if append
     */
    public function title($str, $append = false) {
        $vars  = &$this->vars;
        $title = isset($vars['title'])?$vars['title']:false;
        if(!$title || $append === false) {
            $vars['title'] = $str;
        } else if($append && $append === 1) {
            $vars['title'] = $title.$str;
        } else {
            $vars['title'] = $str.$title;
        }
    }
    /**
     * push variables with a name
     * @param string $name name of variable
     * @param mixed $value value of variable
     */
    public function push($name, $value = null) {
        if(is_string($name) || is_numeric($name)) {
            $this->vars[$name] = is_null($value) ? '' : $value;
        } else if(is_array($name)) {
            foreach ($name as $n => $val) {
                $this->push($n, $val);
            }
        } else {
            $this->vars[] = is_null($value) ? '' : $value;;
        }
    }
    /**
     * set include file path
     * @param string $filepath file path
     */
    public function file($filepath) {
        $_ROOTPATH = App::$_RootPath;
        $filepath = realpath($filepath);
        if(strpos($filepath, $_ROOTPATH) === 0) {
            $this->file = $filepath;
        } else {
            $this->file = realpath($_ROOTPATH . $filepath);
        }
    }
    /**
     * set include theme template file path
     * @param string $filepath template file path, relative template theme directory
     */
    public function plate($filepath) {
        $_ROOTPATH = App::$_RootPath;
        $filepath = realpath($filepath);
        if(strpos($filepath, $_ROOTPATH) === 0) {
            $this->file = $filepath;
        } else {
            $themes = App::get('themes');
            $theme = App::get('theme');
            if($themes && $theme && array_key_exists($theme, $themes)) {
                if(!isset($themes[$theme]['dir'])) {
                    $themes[$theme]['dir'] = '';
                }
                $this->file = realpath($_ROOTPATH.$themes[$theme]['dir'].$filepath);
            } else {
                $this->file = realpath($_ROOTPATH.$filepath);
            }
        }
    }
    /**
     * set meta infomation
     * @param array $meta array for html meta tag
     */
    public function meta($meta) {
        $metas = &$this->metas;
        if(is_array($meta)) {
            foreach($meta as $i=>$m) {
                $metas[] = $m;
            }
        } else {
            $metas[] = $meta;
        }
    }
    /**
     * set include js path
     * @param string $js javascript file src path in html tag script
     */
    public function js($js) {
        $jses   = &$this->jses;
        if(is_array($js)) {
            foreach($js as $i=>$j) {
                $jses[] = $j;
            }
        } else {
            $jses[] = $js;
        }
    }
    /**
     * set include js path,those will echo in end of document
     * @param string $js javascript file src path in html tag script
     */
    public function javascript($js) {
        $javascript   = &$this->javascript;
        if(is_array($js)) {
            foreach($js as $i=>$j) {
                $javascript[] = $j;
            }
        } else {
            $javascript[] = $js;
        }
    }
    /**
     * set include css path
     * @param string $css css file link path
     */
    public function css($css) {
        $csses   = &$this->csses;
        if(is_array($css)) {
            foreach($css as $i=>$c) {
                $csses[] = $c;
            }
        } else {
            $csses[] = $css;
        }
    }
    /**
     * get template headers,
     * return the point of template headers
     * @return array
     */
    public function headers() {
        Logger::info('headers', 'TEMPLATE');
        $headers = &$this->headers;
        return $headers;
    }
    /**
     * get template variables,
     * return the point of template variables
     * @return array
     */
    public function vars() {
        Logger::info('variable', 'TEMPLATE');
        $templateVars = &$this->vars;
        return $templateVars;
    }
    /**
     * output as json string
     */
    public function json() {
        Logger::info('json', 'TEMPLATE');
        $headers      = &$this->headers;
        $templateVars = &$this->vars;
        $templateVars = array_diff_key($templateVars,array('title'=>1));
        foreach($headers as $header) {
            @header($header);
        }
        echo json_encode($templateVars);
    }
    /**
     * output as xml string
     */
    public function xml() {
        Logger::info('xml', 'TEMPLATE');
        $headers      = &$this->headers;
        $templateVars = &$this->vars;
        $templateVars = array_diff_key($templateVars,array('title'=>1));
        foreach($headers as $header) {
            @header($header);
        }
        echo Util::array2XML($templateVars);
    }
    /**
     * output as template
     * @return void
     */
    public function out() {
        $this->display();
    }
    /**
     * output as template
     * @return void
     */
    public function display() {
        Logger::info('display', 'TEMPLATE');
        $templateVars = &$this->vars;
        $templateFile = &$this->file;
        $metas        = &$this->metas;
        $jses         = &$this->jses;
        $javascript   = &$this->javascript;
        $csses        = &$this->csses;
        $headers      = &$this->headers;

        extract($templateVars);
        foreach($headers as $header) {
            @header($header);
        }
        if(file_exists($templateFile)) {
            include($templateFile);
        }
    }
}
?>
