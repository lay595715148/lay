<?php
/**
 * 统一入口文件
 * @author liaiyong
 */
define('INIT_LAY', true);//标记

//require_once __DIR__.'/lib/index.php';
require_once __DIR__.'/src/lay/App.php';

App::start();
?>
