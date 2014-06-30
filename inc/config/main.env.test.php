<?php
use lay\util\Logger;
use config\C;
use config\T;

return array(
    'logger' => array(Logger::L_NONE, false, 0),//0x01 | 0x02 | 0x10 | 0x20 | 0x21
    'appname' => 'lay',
    'code' => array(
        '404' => '/404.html'
    ),
    'language' => 'en-us',
    'languages' => array(
        'zh-cn', 'en-us'
    ),
    'theme' => 'code',
    'themes' => array(
        'code' => array(
            'dir' => '/web/template/code'
        )
    ),
    'routers' => array_merge(C::$routers, T::$routers),
    'actions' => array_merge(C::$actions, T::$actions),
    'plugins' => array_merge(C::$plugins, T::$plugins),
    'stores' => array_merge(C::$stores, T::$stores)
);
?>
