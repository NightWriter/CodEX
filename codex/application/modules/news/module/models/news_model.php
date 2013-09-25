<?php
  
class News_model extends CI_model
{
    private $t_news = 'news';
    private $t_news_categories = 'news_categories';
    
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
    * сохраняем статью
    * 
    * @param int $id
    * @param array $data
    */
    function save($id=0,$data=array())
    {
        $this->db->where('id',$id);
        return $this->db->update($this->t_news,$data);
    }
    /**
    * получаем выбранную стать
    * 
    * @param int $filter['id']
    * @param string $filter['alais']
    * @return object OR false
    */
    function get_one_news($filter=array())
    {
        if(!empty($filter['id']))
            $this->db->where('id',$filter['id']);
        if(!empty($filter['alias']))
            $this->db->where('alias',$filter['alias']);
            
        return $this->get_row($this->db->get($this->t_news,1));
    }
    /**
    * получаем статьи например в выбранной категории
    * 
    * @param int $filter['category_id']
    * @return array
    */
    function get_news($filter=array())
    {
        $cnt_all = '';
        if(!empty($filter['all_cnt']))
        {
            $cnt_all = 'SQL_CALC_FOUND_ROWS ';
        }
        $this->db->select($cnt_all . $this->t_news.'.*', FALSE);
        
        if(!empty($filter['category_id']))
            $this->db->where('category_id',$filter['category_id']);
        
        if(!empty($filter['order_by']))
            $this->db->order_by($this->t_news.'.'.$filter['order_by'],(!empty($filter['order_type']) ? $filter['order_type'] : 'ASC'));
        else
            $this->db->order_by($this->t_news.'.date_cr','DESC');
            
        if (!empty($filter['limit']))
            $this->db->limit($filter['limit'],(empty($filter['offset']) ? 0 : $filter['offset']));
            
        $rows = $this->get_rows($this->db->get($this->t_news));
        
        if(!empty($filter['all_cnt']))
        {
            $sql = "SELECT FOUND_ROWS() as `total`";
            return array($rows, $this->db->query($sql)->row()->total);
        }
        return $rows;
    }
    /**
    * получаем список категорий статических страниц
    * 
    * @param boolean $filter['in_main']
    * @return array
    */
    function get_categories($filter=array())
    {
        if(!empty($filter['in_main']))
            $this->db->where('in_main',1);
        return $this->get_rows($this->db->get($this->t_news_categories));
    }
    /**
    * получаем категорию статических страниц
    * 
    * @param string $filter['alias']
    * @return object OR false
    */
    function get_category($filter=array())
    {
        if(!empty($filter['alias']))
            $this->db->where('alias',$filter['alias']);
        return $this->get_row($this->db->get($this->t_news_categories,1));
    }
}