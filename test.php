<?php
//echo urlencode('%');
$from = 1;
$limit = array();
$str = 'O:9:"DemoModel":1:{s:13:"*properties";a:4:{s:2:"id";i:0;s:4:"name";s:0:"";s:8:"datetime";s:19:"0000-00-00 00:00:00";s:4:"type";i:0;}}';
var_dump(unserialize($str));
print_r($str);exit();
echo !isset($limit['offset']) ? isset($limit['0']) ? $limit['0'] : 1 : $limit['offset'];exit;
include_once 'src/lay/App.php';
App::start();
?>