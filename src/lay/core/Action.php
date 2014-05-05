<?php
if(! defined('INIT_LAY')) {
    define('INIT_LAY', true);
}

/**
 * <p>基础控制器</p>
 * <p>核心类，继承至此类的对象将会在运行时自动执行初始化onCreate方法</p>
 *
 * @abstract
 *
 *
 */
abstract class Action {
    const ACTION_PROVIDER_CONFIG_TAG = 'action-provider';
    const EVENT_CREATE = 'action_create';
    const EVENT_REQUEST = 'action_request';
    const EVENT_GET = 'action_get';
    const EVENT_POST = 'action_post';
    const EVENT_STOP = 'action_stop';
    const EVENT_DESTROY = 'action_destroy';
    /**
     *
     * @staticvar action instance
     */
    private static $instance = null;
    /**
     * get action instance
     *
     * @param string|array $name
     *            name or config of Action
     * @return Action
     */
    public static function getInstance($name = '') {
        if(self::$instance == null) {
            // 增加provider功能
            $provider = Lay::get(self::ACTION_PROVIDER_CONFIG_TAG);
            if($provider && is_string($provider)) {
                $provider = new $provider();
            }
            if($provider instanceof I_Action_Provider) {
                // 执行provide方法
                self::$instance = $provider->provide($name);
            } else if($provider) {
                Logger::warn('given provider isnot an instance of I_Action_Provider', 'ACTION');
            }
            // 如果没有自定义实现IActionProvider接口的类对象，使用默认的配置项进行实现
            if(! (self::$instance instanceof Action)) {
                $config = Lay::getActionConfig($name);
                if(isset($config['classname']) && class_exists($config['classname'])) {
                    $classname = $config['classname'];
                    self::$instance = new $classname($name);
                    if(! (self::$instance instanceof Action)) {
                        Logger::error('action has been instantiated , but it isnot an instance of Action', 'ACTION');
                    }
                } else {
                    Logger::error('action config has no param "classname" or class is not exists', 'ACTION');
                }
            }
        }
        return self::$instance;
    }

    /**
     *
     * @var array 配置信息数组
     */
    protected $name = '';
    /**
     *
     * @var array 存放配置的Service对象
    */
    protected $services = array();
    /**
     *
     * @var Template 模板引擎对象
    */
    protected $template;
    protected $scope;
    /**
     * 构造方法
     *
     * @param array $config
     */
    protected function __construct($name, $template, $request = array()) {
        if(!is_array($request) || empty($request)) {
            $request = & $_REQUEST;
        }
        
        $this->name = $name;
        $this->template = $template;
        $this->request = & $request;
        //$this->scope = new Scope($request);
        EventEmitter::on(Action::EVENT_CREATE, array($this, 'onCreate'));
        EventEmitter::emit(Action::EVENT_CREATE);
    }
    public function __destruct() {
        EventEmitter::emit(Action::EVENT_DESTROY);
    }
    
    public abstract function onCreate();
    public abstract function onRun();
    public abstract function onRequest();
    public abstract function onGet();
    public abstract function onPost();
    public abstract function onStop();
    public abstract function onDestroy();
    
    /**
     * 初始化
     *
     * @return Action
     */
    public function initialize() { // must return $this
        Debugger::info("initialize", 'ACTION');
        $config = &$this->config;
        $services = &$this->services;
        $template = &$this->template;
        $preface = &$this->preface;

        // 加载配置中的所有preface
        if(is_array($config) && array_key_exists('preface', $config)) {
            $preface = &Preface::getInstance($config['preface']);
            $preface->initialize();
        } else {
            $preface = &Preface::getInstance();
            $preface->initialize();
        }

        // 加载配置中的所有template
        if(is_array($config) && array_key_exists('template', $config)) {
            $template = &Template::getInstance($config['template']);
            $template->preface = $preface;
            $template->initialize();
        } else {
            $template = &Template::getInstance();
            $template->preface = $preface;
            $template->initialize();
        }

        // 加载配置中的所有service
        if(is_array($config) && array_key_exists('services', $config) && $config['services'] && is_array($config['services'])) {
            foreach($config['services'] as $k => $name) {
                $services[$name] = &Service::getInstance($name);
                $services[$name]->initialize();
                $this->{$name.'Service'} = &$services[$name];
            }
        } else {
            //不自动初始化没有配置的service
            //$services[''] = Service::getInstance();
            //$services['']->initialize();
        }
        Debugger::info("initialized", 'ACTION');

        return $this;
    }
    /**
     * 获取某一个Service对象
     *
     * @param string $name
     * @return Service
     */
    protected function service($name) {
        $services = &$this->services;
        if(array_key_exists($name, $services)) {
            return $services[$name];
        } else if(is_string($name) && $name) {
            $services[$name] = &Service::getInstance($name);
            $services[$name]->initialize();
            $this->{$name.'Service'} = &$services[$name];
            return $services[$name];
        } else {
            Debugger::warn('service name is empty or hasnot been autoinstantiated by service name:'.$name, 'SERVICE');
            return $services['demo'];
        }
    }
    /**
     * 默认执行方法
     */
    public function launch() {
    }
    /**
     * 路由执行方法
     *
     * @param string $method
     *            dispatch method,default is empty
     * @param array $params
     *            dispatch method arguments
     * @return Action $this
     */
    public function dispatch($method, $params) { // must return $this
        Debugger::info('dispatch', 'ACTION');
        $dispatchkey = Laywork::get('dispatch-key') || Action::DISPATCH_KEY;
        $dispatchstyle = Laywork::get('dispatch-style') || Action::DISPATCH_STYLE;

        if($method) {
            $dispatcher = $method;
        } else if(is_string($dispatchkey) || is_integer($dispatchkey)) {
            $variable = Scope::parseScope();
            $dispatcher = (array_key_exists($dispatchkey, $variable)) ? $_REQUEST[$dispatchkey] : false;
        } else {
            $ext = pathinfo($_SERVER['PHP_SELF']);
            $dispatcher = $ext['filename'];
        }
        if($dispatcher) {
            $method = str_replace('*', $dispatcher, $dispatchstyle);
        }

        if(method_exists($this, $method) && $method != 'init' && $method != 'tail' && $method != 'dispatch' && substr($method, 0, 2) != '__') {
            $this->$method($params);
        } else {
            $this->launch($params);
        }

        return $this;
    }
    /**
     * 最后执行方法
     *
     * @return Action
     */
    public function tail() { // must return $this
        Debugger::info('tail', 'ACTION');
        extract(pathinfo($_SERVER['PHP_SELF']));

        $extension = isset($extension) ? $extension : '';
        switch($extension) {
        	case 'json':
        	    $this->template->header('Content-Type: application/json');
        	    $this->template->header('Cache-Control: no-store');
        	    $this->template->json();
        	    break;
        	case 'xml':
        	    $this->template->header('Content-Type: text/xml');
        	    $this->template->xml();
        	    break;
        	default:
        	    $this->template->out();
        }
        return $this;
    }
}
?>
