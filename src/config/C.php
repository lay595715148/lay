<?php
/**
 * 
 */
namespace config;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * configuration
 * @author Lay Li
 */
class C {
    public static $routers = array(
        array(
            'host' => 'web.lay.laysoft.cn',//多个用|做分隔
            'ip' => '127.0.0.1',//多个用|做分隔
            'port' => 80,//多个用|做分隔
            'rule' => '/^\/u\/(?P<id>[0-9]+)$/',
            'name' => 'user',
            'classname' => 'web\Index'
        )
    );
    public static $actions = array(
        '/' => array(
            'host' => 'web.lay.laysoft.cn',//多个用|做分隔
            'ip' => '127.0.0.1',//多个用|做分隔
            'port' => 80,//多个用|做分隔
            'classname' => 'web\Index'
        ),
        '/test' => array(
            'classname' => 'demo\action\DemoAction'
        ),
        '/index' => array(
            'classname' => 'web\Index'
        ),
        '/index.php' => array(
            'classname' => 'web\Index'
        ),
        '/bootstrap.php' => array(
            'classname' => 'cms\CMS'
        )
    );
    public static $services = array(
        
    );
    public static $stores = array(
        'default' => array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'root',
            'password' => 'yuiopas',
            'schema' => 'laysoft'
        ),
        'mongo' => array(
            'host' => '127.0.0.1',
            'port' => 27017,
            'username' => 'lay',
            'password' => '123456',
            'schema' => 'laysoft'
        )
    );
    public static $plugins = array(
        'http' => array(
            'name' => 'http',
            'classname' => 'plugin\http\HttpPlugin'
        ),
        'json' => array(
            'name' => 'json',
            'classname' => 'plugin\json\JsonPlugin',
            'host' => 'lay.laysoft.cn'//多个用|做分隔
        ),
        'oauth2' => array(
            'name' => 'oauth2',
            'classname' => 'plugin\oauth2\Oauth2Plugin',
            'host' => 'web.lay.laysoft.cn',//多个用|做分隔
            'ip' => '127.0.0.1',//多个用|做分隔
            'port' => 80//多个用|做分隔
        ),
        'sso' => array(
            'name' => 'sso',
            //if undefined this always be loaded,if empty this always be not loaded,
            'open' => '1',
            //if date/datetime this would be loaded when it's that date or datetime
            'start' => '2014-5-13',
            //if this is a date, this would be the end date or datetime when this plugin wouldn't be loaded
            'end' => '2015-5-19',
            //action name or action classname.if undefined this always be loaded.if empty this always be not loaded
            //'action' => '/',
            //service name or service classname.if undefined this always be loaded.if empty this always be not loaded
            'service' => 'demo\service\DemoService',
            //store name or store classname.if undefined this always be loaded.if empty this always be not loaded
            'store' => 'demo\store\DemoStore',
            //if plugin's class name is not the combination of plugin's name and "Plugin",please config this anyway
            'classname' => 'plugin\sso\SSOPlugin'
        )
    );
}
?>
