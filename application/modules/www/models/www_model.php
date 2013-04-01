<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class www_model extends MY_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->table = 'www';
    }
    //
    function get($id=0)
    {
        if(empty($id))
            return false;
            
        return parent::get($id);
    }
    // 
    function get_list($limit=20, $offset=0)
    {
        return parent::get_list($limit,$offset);
    }
}