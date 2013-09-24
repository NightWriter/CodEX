<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include('codexcontroller.php');

class Dictionaries extends codexController
{
    function __construct()
    {
        parent::__construct();
    }
    //
    function _check_perms(&$data)
    {
        if(!file_exists('./codex/application/definitions/'))
            $data['messages']['failure'][] = $this->lang->line('codex_no_directory').' ./codex/application/definitions';
        if(!is_writable('./codex/application/definitions/'))
            $data['messages']['failure'][] = $this->lang->line('codex_write_permission').' ./codex/application/definitions';
    }
    //
    function check_alias()
    {
        $name          = $this->input->post('name');
        
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
            echo 0;
        }
    }
    //
    function build()
    {
        $data           = array();
        
        $this->_check_perms($data);
        
        $alias          = strip_tags(trim($this->input->post('alias')));
        $title          = strip_tags(trim($this->input->post('title')));
        $value          = $this->input->post('value');
        
        $this->db->where('alias_table',$alias);
        $row = $this->db->get('dictionaries',1);
        if($row->num_rows == 1)
        {
            $data['messages']['failure'][] = $this->lang->line('codex_alias').' "'.$alias.'" '.$this->lang->line('codex_exists');
        }
        if(preg_match('/[^a-zA-Z0-9_]+/u',$alias))
            $data['messages']['failure'][] = $this->lang->line('codex_alias').': '.$this->lang->line('codex_contain_letters_numbers');
        
        if(empty($title))
            $data['messages']['failure'][] = $this->lang->line('codex_title_empty');
        
        if(!is_array($value))
            $data['messages']['failure'][] = $this->lang->line('codex_data_error');
            
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
            $data['messages']['failure'][] = $this->lang->line('codex_not_input_values');
        
        if(empty($data['messages']))
        {
            // create table and insert data
            $this->db->set('alias_table',$alias);
            $this->db->set('desc',$title);
            $this->db->insert('dictionaries');
            $dictionaries_id = $this->db->insert_id();
            //
            
            $sql = 'CREATE TABLE  `'.$alias.'` (
                         `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                         `dictionaries_id` INT UNSIGNED NOT NULL ,
                         `value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                        PRIMARY KEY (  `id` )
                        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
                        ';
            if($this->db->query($sql))
            {
                foreach($insert_data as $item)
                {
                    $this->db->set('dictionaries_id',$dictionaries_id);
                    $this->db->set('value',$item);
                    $this->db->insert($alias);
                }
            }
            ///
            $yml = 'page_header: \''.$title.'\'
groups: \'Dictionaries\'
form_setup:
    value:
        class: TextBox';
/*        
создаём *.yml файл для автоматической генерации в админке
*/
        $fp = fopen('./codex/application/definitions/'.$alias.'.yml','w');
        fwrite($fp,$yml);
        fclose($fp);
            ///
            redirect('dictionaries/index/');
        }else{
            $this->index($data);
        }
    }
    //
    function index($data = array())
    {
        $this->_check_perms($data);
        
        $data['view'] = 'build_dictionaries';
        $data['title'] = 'Dictionaries';
        
        $this->_view($data);
    }
}