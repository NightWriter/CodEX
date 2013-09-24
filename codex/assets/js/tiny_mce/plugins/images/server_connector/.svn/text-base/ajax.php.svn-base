<?php
require_once 'JsHttpRequest.php';
require_once 'tinyimages.php';

error_reporting(E_ALL ^ E_DEPRECATED);
$JsHttpRequest = new JsHttpRequest('utf-8');

if(!isset($_REQUEST['m'])) {
    $GLOBALS['_RESULT'] = array( 'error' => 'Не задан метод');
    exit();
}
list($module, $method) = explode('->',$_REQUEST['m']);
if(empty($method)) {
    list($module, $method) = explode('-%3E',$_REQUEST['m']);
}
$method = 'ajax'.$method;

$timgs = new tinyimages();

$GLOBALS['_RESULT'] = $timgs->$method($_REQUEST);
exit();