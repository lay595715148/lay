<?php
//echo urlencode('%');
echo class_exists('Mongo')?'true':'false';exit;
echo !isset($limit['offset']) ? isset($limit['0']) ? $limit['0'] : 1 : $limit['offset'];exit;
include_once 'src/lay/App.php';
App::start();
?>
