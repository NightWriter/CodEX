<?php if (!defined('BASEPATH')) exit('No direct script access allowed');  

class CodexCrumbs{
    var $crumbs    = array();
    var $selected  = '';

    function CodexCrumbs(){
        $this->crumbs = array();
    }

    function setSeparator($new_val){
        $this->separator = $new_val;
    }
    function setPrefix($prefix){
        $this->prefix = $prefix;
    }
    function setSuffix($suffix){
        $this->suffix = $suffix;
    }

    function add($link,$value=""){
        if($value === "")
            $this->crumbs[$link] = '';
        else
            $this->crumbs[$value] = $link;
    }
    function setSelected($val){
        $this->selected = $val;
    }
    function getSelected(){
        return $this->selected;
    }   
    function get(){
        return $this->crumbs;
    }
}

?>
