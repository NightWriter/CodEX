<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class SessionData extends codexForms { 
    function SessionData ($name,$params) { 
        codexForms::initiate($name,$params);
    }

    function getHTML(){
        $CI = &get_instance();
        $html ='
            <input type="hidden" value="'.$CI->codexsession->userdata($this->params['item']).'" name="'.$this->name.'" '.$this->getAttributes($this->attributes).'>
        ';
		return $html;
    }
}
