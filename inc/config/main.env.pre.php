<?php
use lay\util\Logger;
use config\C;
use config\Q;

return array(
    'logger' => array(Logger::L_NONE, true, 0),//0x01 | 0x02 | 0x10 | 0x20 | 0x21
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
    'routers' => array_merge(C::$routers, Q::$routers),
    'actions' => array_merge(C::$actions, Q::$actions),
    'plugins' => array_merge(C::$plugins, Q::$plugins),
    'stores' => array_merge(C::$stores, Q::$stores)
);
?>
