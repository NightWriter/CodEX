<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include('codexcontroller.php');

class Dictionaries extends codexController
{
    function __construct()
    {
        parent::__construct();
    }
    //
    function check_alias()
    {
        $name          = $this->input->post('name');
        $dictionary_id = $this->input->post('dictionary_id');
        
        if(preg_match('/[^a-zA-Z0-9_]+/u',$name))
            exit('0');
        
        $name = strip_tags(trim($name));
        if(empty($name))
            exit('0');
            
        $this->db->where('alias_table',$name);
        $row = $this->db->get('dictionaries',1);
        if($row->num_rows == 0)
        {
            echo 1;
        }else{
            if($row->row()->id == $dictionary_id)
                echo 1;
            else
                echo 0;
        }
    }
    //
    function build()
    {
        $data           = array();
        $alias          = strip_tags(trim($this->input->post('alias')));
        $title          = strip_tags(trim($this->input->post('title')));
        $value          = $this->input->post('value');
        $dictionary_id  = $this->input->post('dictionary_id');
        $dictionary     = NULL;
        
        $this->db->where('alias_table',$alias);
        $row = $this->db->get('dictionaries',1);
        if($row->num_rows == 1)
        {
            $dictionary = $row->row();
            if( $dictionary_id != $dictionary->id )
                $data['errors'][] = 'Alias "'.$alias.'" exists';
        }
        if(preg_match('/[^a-zA-Z0-9_]+/u',$alias))
            $data['errors'][] = 'Alias должен содержать только латинские буквы и цифры';
        
        if(empty($title))
            $data['errors'][] = 'Title empty';
        
        if(!is_array($value))
            $data['errors'][] = 'Ошибка данных';
            
        $insert_data = array();
        if(is_array($value))
        {
            foreach($value as  $item)
            {
                $item = strip_tags(trim($item));
                if(empty($item)) continue;
                
                $insert_data[] = $item;
            }
        }
        if(empty($insert_data))
            $data['errors'][] = 'Не введенны значения';
        
        if(empty($data['errors']))
        {
            if(empty($dictionary_id))
            {
                // create table and insert data
                $this->db->set('alias_table',$alias);
                $this->db->set('desc',$title);
                $this->db->insert('dictionaries');
                //
                $redirect_id = $this->db->insert_id();
            }else{
                $this->db->set('alias_table',$alias);
                $this->db->set('desc',$title);
                $this->db->where('id',$dictionary->id);
                $this->db->update('dictionaries');
                
                $redirect_id = $dictionary->id;
                
                $sql = 'DROP TABLE  `'.$dictionary->alias_table.'` ';
                $this->db->query($sql);
            }
            
            $sql = 'CREATE TABLE  `'.$alias.'` (
                         `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                         `value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                        PRIMARY KEY (  `id` )
                        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
                        ';
            if($this->db->query($sql))
            {
                foreach($insert_data as $item)
                {
                    $this->db->set('value',$item);
                    $this->db->insert($alias);
                }
            }
            redirect('dictionaries/index/'.$redirect_id);
        }else{
            $this->index($data);
        }
    }
    //
    function index($data = array())
    {
        $dictionary_id = $this->uri->segment(3);
        if(is_numeric($dictionary_id))
            $data = array();
        
        $data['view'] = 'build_dictionaries';
        $data['title'] = 'Dictionaries';
        
        $rows = $this->db->get('dictionaries');
        if($rows->num_rows > 0)
            $data['dictionaries'] = $rows->result();
        
        $dictionary_id = ((!empty($dictionary_id))?$dictionary_id:trim($this->input->post('dictionary_id')));
        if( !empty($dictionary_id) && is_numeric($dictionary_id) )
        {
            $this->db->where('id',$dictionary_id);
            $row = $this->db->get('dictionaries',1);
            if($row->num_rows == 1)
            {
                $data['dictionary'] = $row->row();
                
                $rows = $this->db->get($data['dictionary']->alias_table);
                if($rows->num_rows > 0)
                    $data['dictionary_values'] = $rows->result();
            }
            
        }
        
        $this->_view($data);
    }
}