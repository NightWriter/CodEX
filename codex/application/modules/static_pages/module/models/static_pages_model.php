<?php
  
class Static_pages_model extends CI_Model
{
    private $t_static_pages = 'static_pages';
    
    function __construct()                        
    {
        parent::__construct();
    }
    /**
    * получаем объект
    * 
    * @param object $row
    * @param boolean $return_state
    * @return object OR false
    */
    function get_row($row=null,$return_state=FALSE)
    {
        if(!empty($row) && $row->num_rows == 1)
            return ($return_state) ? TRUE : $row->row();
        return FALSE;
    }
    
    function get_rows($rows=null)
    {
        if(!empty($rows) && $rows->num_rows > 0)
            return $rows->result();
        return array();
    }
    /**
    * получаем статическую страницу по индификатору
    * 
    * @param string $ind
    */
    function get_static_page($ind='')
    {
        $this->db->where('ind',$ind);
        return $this->get_row($this->db->get($this->t_static_pages,1));
    }
}