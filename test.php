<?php
//echo urlencode('%');
$from = 1;
print_r(($from ? ' FROM ' : ' ') . 'lay_demo');exit();
include_once 'src/lay/App.php';
App::start();
?>