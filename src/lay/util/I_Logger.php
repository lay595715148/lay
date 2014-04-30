<?php
interface I_Logger {
    /**
     * log debugger infomation
     *
     * @param string $msg
     *            the message
     * @param int $lv
     *            the debug level
     * @param string $tag
     *            the tag
     * @return void
     */
    public function log($msg, $lv = 1, $tag = '');
    /**
     * print out debugger infomation
     *
     * @param string $msg
     *            the message
     * @param int $lv
     *            the debug level
     * @param string $tag
     *            the tag
     * @return void
     */
    public function out($msg, $lv = 1, $tag = '');
    /**
     * multily print out debugger infomation
     *
     * @param string $msg
     *            the message
     * @param int $lv
     *            the debug level
     * @param string $tag
     *            the tag
     * @return void
     */
    public function pre($msg, $lv = 1, $tag = '');
}
?>
