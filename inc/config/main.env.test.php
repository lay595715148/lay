<?php
return array(
    'logger' => false,
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
            'classname' => 'Index'
        ),
        '/test' => array(
            'classname' => 'Index'
        ),
        '/index' => array(
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
    )
);
?>
