<?php
//echo urlencode('%');
include_once 'src/lay/Lay.php';
Lay::start();
class TEST {
    public static function run() {
        Lay::loadClass('V2_Web', __DIR__.'/src/web');
        cms\CMS::test();echo '<br>';Web::test();echo '<br>';V2_Web::test();
    }
}
?>