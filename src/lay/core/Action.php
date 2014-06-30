<?php
/**
 * 基础控制器；
 * 核心类，继承至此类的对象将会在运行时触发一系列事件方法（onCreate,onRequest,onGet,onPost,onStop,onDestory等）
 *
 * @abstract
 * @author Lay Li
 */
namespace lay\core;

use lay\App;
use lay\util\Scope;
use lay\util\Logger;
use HttpRequest;
use HttpResponse;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 基础控制器；
 * 核心类，继承至此类的对象将会在运行时触发一系列事件方法（onCreate,onRequest,onGet,onPost,onStop,onDestory等）
 *
 * @abstract
 * @author Lay Li
 */
abstract class Action extends AbstractAction {
    /**
     * 事件常量，创建时
     *
     * @var string
     */
    const E_CREATE = 'action_create';
    /**
     * 事件常量，RTEQUEST
     *
     * @var string
     */
    const E_REQUEST = 'action_request';
    /**
     * 事件常量，GET时
     *
     * @var string
     */
    const E_GET = 'action_get';
    /**
     * 事件常量，POST时
     *
     * @var string
     */
    const E_POST = 'action_post';
    /**
     * 事件常量，结束时
     *
     * @var string
     */
    const E_STOP = 'action_stop';
    /**
     * 事件常量，摧毁时
     *
     * @var string
     */
    const E_DESTROY = 'action_destroy';
    /**
     * 钩子常量，创建时
     *
     * @var string
     */
    const H_CREATE = 'hook_action_create';
    /**
     * 钩子常量，结束时
     *
     * @var string
     */
    const H_STOP = 'hook_action_stop';
    /**
     * Action实例
     * @var Action
     */
    private static $instance = null;
    /**
     * 获取一个Action实例
     * @param string $name 名称
     * @param string $classname 类名
     * @return Action
     */
    public static function getInstance($name, $classname = '') {
        if(self::$instance == null) {
            // 使用默认的配置项进行实现
            if(! (self::$instance instanceof Action)) {
                if(!$classname || !class_exists($classname)) {
                    $config = App::getActionConfig($name);
                    $classname = $config ? $config['classname'] : '';
                }
                if(class_exists($classname)) {
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

    /**
     * Action名称
     * @var string
     */
    protected $name = '';
    /**
     * HttpRequest
     * @var HttpRequest
     */
    protected $request;
    /**
     * HttpResponse
     * @var HttpResponse
     */
    protected $response;
    /**
     * 存放业务逻辑对象的数组
     * @var array
    */
    protected $services = array();
    /**
     * 模板引擎对象
     * @var Template
    */
    protected $template;
    /**
     * 构造方法
     *
     * @param string $name 名称
     * @param Template $template 模板引擎对象
     */
    public function __construct($name) {
        $this->name = $name;
        $this->request = new HttpRequest();
        $this->response = new HttpResponse();
        $this->template = new Template($this->request, $this->response);
        EventEmitter::on(self::E_CREATE, array($this, 'onCreate'), 1);
        PluginManager::exec(self::H_CREATE, array($this));
        EventEmitter::emit(self::E_CREATE, array($this));
    }
    /**
     * 返回HttpRequest
     * @return HttpRequest
     */
    public function getRequest() {
        return $this->request;
    }
    /**
     * 返回HttpReponse
     * @return HttpReponse
     */
     public function getResponse() {
        return $this->response;
    }
    /**
     * 返回模板引擎对象
     * @return Template
     */
    public function getTemplate() {
        return $this->template;
    }
    /**
     * 返回名称
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    /**
     * 析构方法
     */
    public function __destruct() {
        //触发Action的摧毁事件
        EventEmitter::emit(Action::E_DESTROY, array($this));
    }
    /**
     * 获取Service对象
     * @param string $classname
     * @return Service
     */
    public function service($classname) {
        if(!array_key_exists($classname, $this->services)) {
            $this->services[$classname] = Service::getInstance($classname);
        }
        return $this->services[$classname];
    }
    /**
     * 创建事件触发方法
     * @see \lay\core\AbstractAction::onCreate()
     */
    public function onCreate() {
        
    }
    /**
     * REQUEST事件触发方法
     * @see \lay\core\AbstractAction::onRequest()
     */
    public function onRequest() {
        
    }
    /**
     * GET事件触发方法
     * @see \lay\core\AbstractAction::onGet()
     */
    public function onGet() {
        
    }
    /**
     * POST事件触发方法
     * @see \lay\core\AbstractAction::onPost()
     */
    public function onPost() {
        
    }
    /**
     * 结束事件触发方法
     * @see \lay\core\AbstractAction::onStop()
     */
    public function onStop() {
        
    }
    /**
     * 摧毁事件触发方法
     * @see \lay\core\AbstractAction::onDestroy()
     */
    public function onDestroy() {
        
    }
}
?>
