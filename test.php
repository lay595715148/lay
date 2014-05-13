<?php
//echo urlencode('%');
$from = 1;
$limit = array();
echo !isset($limit['offset']) ? isset($limit['0']) ? $limit['0'] : 1 : $limit['offset'];exit;
include_once 'src/lay/App.php';
App::start();
?>