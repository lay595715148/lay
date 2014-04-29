<?php 
$st = date('Y-m-d H:i:s').'.'.floor(microtime()*1000);
include_once __DIR__.'/bootstrap.example.php';
Laywork::start();
Debugger::debug(array($st, date('Y-m-d H:i:s').'.'.floor(microtime() * 1000)));
//echo '<pre>';print_r(get_included_files());echo '</pre>';
?>
