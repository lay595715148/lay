<?php

/**
 * 模板引擎基础类
 * @author Lay Li
 */
namespace lay\core;

use lay\App;
use lay\util\Logger;
use lay\entity\Response;
use lay\entity\Lister;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 模板引擎基础类
 * 
 * @author Lay Li
 */
class Template extends AbstractTemplate {
    const TEMPLATE_PROVIDER_CONFIG_TAG = 'template-provider';
    /**
     * Template instance
     * 
     * @staticvar Template
     */
    private static $instance = null;
    /**
     * get Template instance
     * 
     * @param $name name
     *            of Template
     * @param $config default
     *            is empty
     * @return Template
     */
    public static function getInstance($name = '') {
        if(self::$instance == null) {
            // 使用默认的配置项进行实现
            if(! (self::$instance instanceof Template)) {
                $config = App::getTemplateConfig($name);
                $config = is_array($name) ? $name : App::getTemplateConfig($name);
                $classname = isset($config['classname']) ? $config['classname'] : 'DemoTemplate';
                if(isset($config['classname'])) {
                    self::$instance = new $classname($config);
                }
                if(! (self::$instance instanceof Template)) {
                    Logger::warn('template has been instantiated by default DemoTemplate', 'TEMPLATE');
                    self::$instance = new DemoTemplate($config);
                }
            }
        }
        return self::$instance;
    }
    /**
     * Action对象
     * 
     * @var Action $action
     */
    public $action;
    /**
     * the language
     * 
     * @var string $lan
     */
    protected $lan = '';
    /**
     * the theme
     * 
     * @var string $theme
     */
    protected $theme = '';
    /**
     * 输出变量内容数组
     * 
     * @var array $vars
     */
    protected $vars = array();
    /**
     * resources
     * 
     * @var array $res
     */
    protected $res = array();
    /**
     * HTTP headers
     * 
     * @var array $headers
     */
    protected $headers = array();
    /**
     * HTML metas
     * 
     * @var array $metas
     */
    protected $metas = array();
    /**
     * HTML scripts
     * 
     * @var array $jses
     */
    protected $jses = array();
    /**
     * HTML scripts in the end
     * 
     * @var array $javascript
     */
    protected $javascript = array();
    /**
     * HTML css links
     * 
     * @var array $csses
     */
    protected $csses = array();
    /**
     * file path
     * 
     * @var string $file
     */
    protected $file;
    /**
     * 构造方法
     * 
     * @param array $config
     *            配置信息数组
     */
    public function __construct() {
        $lans = App::get('languages');
        $lan = App::get('language');
        $this->lan = in_array($lan, (array)$lans) ? $lan : 'zh-cn';
        $this->theme(App::get('theme'));
    }
    /**
     * push header for output
     * 
     * @param string $header
     *            http header string
     */
    public function header($header) {
        $this->headers[] = $header;
    }
    /**
     * set title ,if $append equal false, then reset title;if $append equal 1 or true,
     * then append end position; other append start position
     * 
     * @param string $str
     *            title
     * @param boolean $append
     *            if append
     */
    public function title($str, $append = false) {
        $vars = &$this->vars;
        $title = isset($vars['title']) ? $vars['title'] : false;
        if(! $title || $append === false) {
            $vars['title'] = $str;
        } else if($append && $append === 1) {
            $vars['title'] = $title . $str;
        } else {
            $vars['title'] = $str . $title;
        }
    }
    /**
     * push variables with a name
     * 
     * @param string $name
     *            name of variable
     * @param mixed $value
     *            value of variable
     */
    public function push($name, $value = null) {
        if(is_string($name) || is_numeric($name)) {
            $this->vars[$name] = is_null($value) ? '' : $value;
        } else if(is_array($name)) {
            foreach($name as $n => $val) {
                $this->push($n, $val);
            }
        } else {
            $this->vars[] = is_null($value) ? '' : $value;
        }
    }
    /**
     * set language
     * 
     * @param string $lan
     *            language
     */
    public function language($lan = 'zh-cn') {
        $supports = App::get('languages');
        $support = App::get('language');
        $support = $support ? $support : 'zh-cn';
        $this->lan = in_array($lan, (array)$supports) ? $lan : $support;
    }
    /**
     * set skin theme temporarily
     * 
     * @param string $theme
     *            theme name
     */
    public function theme($theme = '') {
        $themes = array_keys((array)App::get('themes'));
        if(in_array($theme, $themes)) {
            $this->theme = $theme;
            if($theme != App::get('theme')) {
                App::set('theme', $theme);
            }
        }
    }
    /**
     * set include file path
     * 
     * @param string $filepath
     *            file path
     */
    public function file($filepath) {
        $_ROOTPATH = App::$_RootPath;
        if(strpos($filepath, $_ROOTPATH) === 0) {
            $this->file = $filepath;
        } else {
            $themes = App::get('themes');
            $theme = App::get('theme');
            if($themes && $theme && in_array($theme, array_keys((array)$themes))) {
                $dir = App::get('themes.'.$theme.'.dir');
                if(strpos($dir, $_ROOTPATH) === 0) {
                    $this->file = realpath($dir . DIRECTORY_SEPARATOR . $filepath);
                } else {
                    $this->file = realpath($_ROOTPATH . $dir . DIRECTORY_SEPARATOR . $filepath);
                }
            } else {
                $this->file = realpath($_ROOTPATH . DIRECTORY_SEPARATOR . $filepath);
            }
        }
    }
    /**
     * set meta infomation
     * 
     * @param array $meta
     *            array for html meta tag
     */
    public function meta($meta) {
        $metas = &$this->metas;
        if(is_array($meta)) {
            foreach($meta as $i => $m) {
                $metas[] = $m;
            }
        } else {
            $metas[] = $meta;
        }
    }
    /**
     * set include js path
     * 
     * @param string $js
     *            javascript file src path in html tag script
     */
    public function js($js) {
        $jses = &$this->jses;
        if(is_array($js)) {
            foreach($js as $i => $j) {
                $jses[] = $j;
            }
        } else {
            $jses[] = $js;
        }
    }
    /**
     * set include js path,those will echo in end of document
     * 
     * @param string $js
     *            javascript file src path in html tag script
     */
    public function javascript($js) {
        $javascript = &$this->javascript;
        if(is_array($js)) {
            foreach($js as $i => $j) {
                $javascript[] = $j;
            }
        } else {
            $javascript[] = $js;
        }
    }
    /**
     * set include css path
     * 
     * @param string $css
     *            css file link path
     */
    public function css($css) {
        $csses = &$this->csses;
        if(is_array($css)) {
            foreach($css as $i => $c) {
                $csses[] = $c;
            }
        } else {
            $csses[] = $css;
        }
    }
    /**
     * get template headers,
     * return the point of template headers
     * 
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
     * 
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
        $lan = &$this->lan;
        $headers = &$this->headers;
        $templateVars = &$this->vars;
        $res = &$this->res;
        $templateVars = array_diff_key($templateVars, array(
                'title' => 1
        ));
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
        $lan = &$this->lan;
        $headers = &$this->headers;
        $templateVars = &$this->vars;
        $res = &$this->res;
        $templateVars = array_diff_key($templateVars, array(
                'title' => 1
        ));
        foreach($headers as $header) {
            @header($header);
        }
        echo Util::array2XML($templateVars);
    }
    /**
     * output as template
     * 
     * @return void
     */
    public function out() {
        $this->display();
    }
    /**
     * output as template
     * 
     * @return void
     */
    public function display() {
        Logger::info('display', 'TEMPLATE');
        $lan = &$this->lan;
        $theme = &$this->theme;
        $vars = &$this->vars;
        $file = &$this->file;
        $metas = &$this->metas;
        $jses = &$this->jses;
        $javascript = &$this->javascript;
        $csses = &$this->csses;
        $headers = &$this->headers;
        $res = &$this->res;
        
        extract($vars);
        foreach($headers as $header) {
            @header($header);
        }
        if(file_exists($file)) {
            include ($file);
        }
    }
}
?>
