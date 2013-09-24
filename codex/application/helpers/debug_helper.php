<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
function dprint($var,$exit = false){
    echo "<pre>"; print_r($var); echo "</pre>";
    if($exit) exit();
}
function dtrace($exit=true){
    echo "<pre>"; print_r(array_slice(debug_backtrace(),1)); echo "</pre>";
    if($exit) exit();
}
?>
