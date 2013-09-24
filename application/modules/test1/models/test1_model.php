<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class test1_model extends MY_Model {

    function __construct()
    {
        parent::__construct();
        
        $this->table = 'test1';
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