<?php
class MY_Model extends CI_Model
{
    var $table = '';
    
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
        $row = $this->db->get($this->table,1);
        if($row->num_rows == 1)
            return $row->row();
        return false;
    }
    // 
    function get_list($limit=20, $offset=0)
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `'.$this->table.'`.* 
                    FROM `'.$this->table.'` 
                    LIMIT '.$offset.', '.$limit;
        
        $rows = $this->db->query($sql);
        if($rows->num_rows > 0)
        {
            $sql = "SELECT FOUND_ROWS() as `total`";
            return array($rows->result_array(),$this->db->query($sql)->row()->total);
        }
        return array(array(),0);
    }
}