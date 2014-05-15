<?php
return array(
    'logger' => array(0x01 | 0x02 | 0x10 | 0x20 | 0x21, false),
    'code' => array(
        '404' => '/404.html'
    ),
    'routers' => array(
        array(
            'rule' => '/^\/u\/(?P<id>[0-9]+)$/',
            'name' => 'user',
            'classname' => 'Index'
        )
    ),
    'actions' => array(
        '/' => array(
            'classname' => 'DemoAction'
        ),
        '/test' => array(
            'classname' => 'Index'
        ),
        '/index' => array(
            'classname' => 'Index'
        ),
        '/index.php' => array(
            'classname' => 'Index'
        ),
        '/bootstrap.php' => array(
            'classname' => 'cms\CMS'
        )
    ),
    'plugins' => array(
        'oauth2' => array(
                'name' => 'oauth2'
        ),
        'sso' => array(
            'name' => 'sso',
            //if undefined this always be loaded,if empty this always be not loaded,
            'open' => '1',
            //if date/datetime this would be loaded when it's that date or datetime
            'start' => '2014-5-13',
            //if this is a date, this would be the end date or datetime when this plugin wouldn't be loaded
            'end' => '2014-5-19',
            //action name or action classname.if undefined this always be loaded.if empty this always be not loaded
            'action' => '/',
            //action name or action classname.if undefined this always be loaded.if empty this always be not loaded
            'service' => 'DemoService',
            //action name or action classname.if undefined this always be loaded.if empty this always be not loaded
            'store' => 'DemoStore',
            //if plugin's class name is not the combination of plugin's name and "Plugin",please config this anyway
            'classname' => 'SSO'
        )
    ),
    'stores' => array(
        'default' => array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'root',
            'password' => 'yuiopas',
            'schema' => 'laysoft'
        )
    )
);
?>
