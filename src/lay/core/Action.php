<?php
if(!defined('INIT_LAY')) {
    exit();
}

/**
 * <p>基础控制器</p>
 * <p>核心类，继承至此类的对象将会在运行时自动执行初始化onCreate方法</p>
 *
 * @abstract
 *
 *
 */
abstract class Action extends AbstractAction {
    const ACTION_PROVIDER_CONFIG_TAG = 'action-provider';
    const EVENT_CREATE = 'action_create';
    const EVENT_REQUEST = 'action_request';
    const EVENT_GET = 'action_get';
    const EVENT_POST = 'action_post';
    const EVENT_STOP = 'action_stop';
    const EVENT_DESTROY = 'action_destroy';
    const HOOK_CREATE = 'hook_action_create';
    const HOOK_STOP = 'hook_action_stop';
    const HOOK_DESTROY = 'hook_action_destroy';
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
    public static function getInstance($name = '', $params = array()) {
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
                        self::$instance = null;
                        Logger::error('action has been instantiated , but it isnot an instance of Action', 'ACTION');
                    }
                } else {
                    self::$instance = null;
                    Logger::error('action config has no param "classname" or class is not exists', 'ACTION');
                }
            }
        }
        return self::$instance;
    }
    public static function getInstanceByClassname($classname, $name, $params = array()) {
        if(self::$instance == null) {
            if($classname && class_exists($classname)) {
                self::$instance = new $classname($name);
                if(! (self::$instance instanceof Action)) {
                    self::$instance = null;
                    Logger::error('action has been instantiated , but it isnot an instance of Action', 'ACTION');
                }
            } else {
                self::$instance = null;
                Logger::error('param $classname is empty or class is not exists by autoload', 'ACTION');
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
    public function __construct($name, $template) {
        $this->name = $name;
        $this->template = is_a($template, 'Template') ? $template : new Template();
        $this->scope = new Scope();
        PluginManager::exec(self::HOOK_CREATE, array($this));
        EventEmitter::on(self::EVENT_CREATE, array($this, 'onCreate'));
        EventEmitter::emit(self::EVENT_CREATE);
    }
    /**
     * 
     * @return Template
     */
    public function getTemplate() {
        return $this->template;
    }
    /**
     * 
     * @return Scope
     */
    public function getScope() {
        return $this->scope;
    }
    /**
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    public function __destruct() {
        PluginManager::exec(self::HOOK_DESTROY, array($this));
        EventEmitter::emit(Action::EVENT_DESTROY);
    }
    public function onCreate() {
        
    }
    public function onRequest() {
        
    }
    public function onGet() {
        
    }
    public function onPost() {
        
    }
    public function onStop() {
        
    }
    public function onDestroy() {
        
    }
}
?>
