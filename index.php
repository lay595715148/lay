<?php 
$st = date('Y-m-d H:i:s').'.'.floor(microtime()*1000);
include_once __DIR__.'/bootstrap.php';
Logger::debug(array($st, date('Y-m-d H:i:s').'.'.floor(microtime() * 1000)));
?>
