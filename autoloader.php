<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
function autoloadClass($class){
    spl_autoload(strtolower($class));
}
$realpath = realpath($_SERVER["DOCUMENT_ROOT"]).'/zombie';
set_include_path($realpath."/models/db" . PATH_SEPARATOR . $realpath."/models/custom");
spl_autoload_extensions('.class.php,.php');
spl_autoload_register('autoloadClass');
$connection = new DBConnect();