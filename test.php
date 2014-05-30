<?php
//echo urlencode('%');
echo '<pre>';$ret = preg_match_all('/(#[^#]{1,}#)/', 'aaa#gas#aa#s#', $matches);print_r($ret);print_r($matches);echo '</pre>';
echo '<pre>';print_r(get_extension_funcs('http'));echo '</pre>';
echo '<pre>';print_r(get_loaded_extensions());echo '</pre>';
echo function_exists('http_get_request_headers')?'true':'false';
echo class_exists('Mongo')?'true':'false';exit;
echo !isset($limit['offset']) ? isset($limit['0']) ? $limit['0'] : 1 : $limit['offset'];exit;
include_once 'src/lay/App.php';
App::start();
?>
