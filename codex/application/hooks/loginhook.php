<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class AuthHook {
    var $CI;
    
    function AuthHook()
    {
        $this->CI =& get_instance();
    }
    function auth()
    {
        if($this->CI->uri->segment(1) != 'login'){
            if(!$this->CI->codexlogin->isLoggedIn()){
               $this->CI->codexsession->set_userdata('page_referer',$this->CI->uri->uri_string());
               redirect('login');
            }
        }
    }
    function setPreDefinedAccessLevels(){
        $this->CI->codexlogin->setPreDefinedAccessLevels();
    }
}
?>
