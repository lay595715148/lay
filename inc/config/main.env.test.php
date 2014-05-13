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
        'oauth2' => 'oauth2',
        'sso' => array(
            'name' => 'sso',
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
