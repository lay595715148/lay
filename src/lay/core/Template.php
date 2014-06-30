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
use lay\util\Util;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 模板引擎基础类
 *
 * @author Lay Li
 */
class Template extends AbstractTemplate {
    /**
     * Action对象
     *
     * @var Action $action
     */
    public $action;
    /**
     * HttpRequest对象
     *
     * @var HttpRequest $request
     */
    protected $request;
    /**
     * HttpReponse对象
     *
     * @var HttpReponse $response
     */
    protected $response;
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
     * template files directory
     *
     * @var string $dir
     */
    protected $dir;
    /**
     * file path
     *
     * @var string $file
     */
    protected $file;
    /**
     * redirect url
     *
     * @var string $redirect
     */
    protected $redirect;
    /**
     * 构造方法
     *
     * @param Action $action
     *            配置信息数组
     */
    public function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
        $this->language();//初始化语言
        $this->directory(App::$_RootPath);//初始化目录
        $this->theme(App::get('theme'));//初始化主题皮肤
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
            if(array_key_exists($name, $this->vars)) {
                Logger::warn($name . ' has been defined in template variables', 'TEMPLATE');
            }
            if(is_a($value, 'lay\core\Bean')) {
                $this->vars[$name] = $value->toArray();
            } else if(is_object($value)) {
                $this->vars[$name] = get_object_vars($value);
            } else {
                $this->vars[$name] = is_null($value) ? '' : $value;
            }
        } else if(is_array($name)) {
            foreach($name as $n => $val) {
                $this->push($n, $val);
            }
        } else if(is_a($name, 'Iterator')) {
            $this->push(iterator_to_array($name));
        } else if(is_object($name)) {
            $this->push(get_object_vars($name));
        } else {
            $this->vars[] = is_null($value) ? '' : $value;
        }
    }
    public function distinct() {
        $this->vars = array();
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
            $this->directory(App::get('themes.' . $this->theme . '.dir', ''));
        }
    }
    /**
     * set template dir
     * @param string $dir
     */
    public function directory($dir) {
        $_ROOTPATH = App::$_RootPath;
        if(strpos($dir, $_ROOTPATH) === 0) {
            $this->dir = realpath($dir);
        } else {
            $this->dir = realpath($_ROOTPATH . DIRECTORY_SEPARATOR . $dir);
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
            $this->file = realpath($filepath);
        } else {
            $dir = $this->dir;
            if(strpos($filepath, $dir) === 0) {
                $this->file = realpath($filepath);
            } else {
                $this->file = realpath($dir . DIRECTORY_SEPARATOR . $filepath);
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
        return $this->vars;
    }
    public function redirect($url, array $params = array()) {
        $this->redirect = $url . ($params ? '?' . http_build_query($params) : '');
    }
    /**
     * output as json string
     */
    public function json() {
        Logger::info('json', 'TEMPLATE');
        if($this->redirect) {
            $this->response->redirect($this->redirect);
        }
        
        $this->response->setContentType('application/json');
        foreach($this->headers as $header) {
            $this->response->setHeader($header);
        }
        if(version_compare(phpversion(), '5.4.0') > 0) {
            $this->response->setData(json_encode($this->vars, JSON_PRETTY_PRINT));
        } else {
            $this->response->setData(json_encode($this->vars));
        }
        
        if(Logger::hasOutput()) {
            echo $this->response->getData();
        } else {
            $this->response->send();
        }
    }
    /**
     * output as xml string
     */
    public function xml() {
        Logger::info('xml', 'TEMPLATE');
        if($this->redirect) {
            $this->response->redirect($this->redirect);
        }
        
        $this->response->setContentType('text/xml');
        foreach($this->headers as $header) {
            $this->response->setHeader($header);
        }
        $this->response->setData(Util::array2XML($this->vars));
        
        if(Logger::hasOutput()) {
            echo $this->response->getData();
        } else {
            $this->response->send();
        }
    }
    /**
     * output as template
     *
     * @return void
     */
    public function out() {
        Logger::info('out', 'TEMPLATE');
        ob_start();
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
        include ($file);
        $results = ob_get_contents();
        ob_end_clean();
        
        return $results;
    }
    /**
     * output as template
     *
     * @return void
     */
    public function display($filepath = '') {
        Logger::info('display', 'TEMPLATE');
        if($filepath) {
            $this->file($filepath);
        }
        if($this->redirect) {
            $this->response->redirect($this->redirect);
        }
        
        ob_start();
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
        if($file && is_file($file)) {
            include ($file);
        }
        $results = ob_get_contents();
        ob_end_clean();
        
        $this->response->setContentType('text/html');
        foreach($this->headers as $header) {
            $this->response->setHeader($header);
        }
        $this->response->setData($results);
        
        if(Logger::hasOutput()) {
            echo $this->response->getData();
        } else {
            $this->response->send();
        }
    }
}
?>
