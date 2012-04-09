<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class {alias}_model extends CI_Model {

    var $table_{alias} = '{alias}';
    
    function __construct()
    {
        parent::__construct();
    }
    //
    function get($id=0)
    {
        if(empty($id))
            return false;
            
        $this->db->where('id',$id);
        $row = $this->db->get($this->table_{alias},1);
        if($row->num_rows == 1)
            return $row->row();
        return false;
    }
    // 
    function get_list($limit=20, $per_page=0)
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `'.$this->table_{alias}.'`.* 
                    FROM `'.$this->table_{alias}.'` 
                    LIMIT '.$per_page.', '.$limit;
        
        $rows = $this->db->query($sql);
        if($rows->num_rows > 0)
        {
            $sql = "SELECT FOUND_ROWS() as `total`";
            return array($rows->result_array(),$this->db->query($sql)->row()->total);
        }
        return array(array(),0);
    }
}