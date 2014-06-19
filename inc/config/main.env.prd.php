<?php
use lay\util\Logger;
use config\C;
use config\P;

return array(
    'logger' => array(Logger::L_NONE, Logger::L_WARN & Logger::L_ERROR & Logger::L_LOG, 0),//0x01 | 0x02 | 0x10 | 0x20 | 0x21
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
    'routers' => array_merge(C::$routers, P::$routers),
    'actions' => array_merge(C::$actions, P::$actions),
    'plugins' => array_merge(C::$plugins, P::$plugins),
    'stores' => array_merge(C::$stores, P::$stores)
);
?>
