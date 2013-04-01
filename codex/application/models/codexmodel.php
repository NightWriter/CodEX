<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class CodexModel extends CI_Model{
    
    var $table_data_logs = 'admin_data_logs';

    function CodexModel(){
        parent::__construct();
    }
    //
    function get_redirect_link()
    {
        $user_level = $this->codexsession->userdata('user_level');
        
        $this->db->select('access.link');
        $this->db->join('access',
                        'access.id=access_access_level.access_id');
        
        $this->db->where('access_level_id',$user_level);
        
        $rows = $this->db->get('access_access_level',1);
        
        if($rows->num_rows == 1)
        {
            return $rows->row()->link;
        }
        return false;
    }
    //
    function check_access($link='')
    {
        $link = trim($link,'/');
        if(empty($link))
            $link = '?'.htmlspecialchars($_SERVER['QUERY_STRING']);
        if(empty($link))
            return false;
        
        $user_level = $this->codexsession->userdata('user_level');
        
        $this->db->select('access.id');
        $this->db->join('access',
                        'access.id=access_access_level.access_id');
        
        $this->db->where('access_level_id',$user_level);
        $this->db->where('access.link',$link);
        
        $rows = $this->db->get('access_access_level');
        
        if($rows->num_rows == 1)
            return true;
        
        return false;
    }
    // фиксируем все изменения
    function data_logs($table='',$id=0,$data=array())
    {
        if($this->db->table_exists($this->table_data_logs)){
            foreach($data as $key=>$val)
                $this->db->select($key);
                
            $this->db->where($this->codexadmin->primary_key,$id);

            $row = $this->db->get($table,1);
          
            if(sizeof($data) == 0)
                foreach($row->list_fields() as $field)
                    $data[$field] = '';
            $before = array();
            if($row->num_rows == 1){
                foreach($data as $key=>$val)
                    $before[$key] = $row->row()->$key;
            }
            
                $insert = array(    
                                  'date' => date('Y-m-d H:i:s'),
                                  'user_id' => $this->codexsession->userdata('user_id'),
                                  'table' => $table,
                                  'id_records' => $id,
                                  'before' => serialize($before),
                                  'after' => serialize($data)
                                );
                $this->db->insert($this->table_data_logs,$insert);
        }
    }
    //
    function _add($table,$data){
        if($this->table_data_logs == $table)
            return;
        
        foreach($data as $k=>$v)
        {
            if(!is_array($v))
            {
                
                $this->data_logs($table,0,$data);//заносим  в таблицу логов
                $res = $this->db->insert($table,$data);
                // ВАЖНО!! РАБОТАЕТ ТОЛЬКО ПРИ ОТКЛЮЧЕННОМ ДЕБАГЕ В config/database.php
                // 1452 - код ошибки вставки с неустановленным ключем.
                if($this->db->_error_number())
                {
                    if($this->db->_error_number() == DATABASE_INSERT_FOREIGN_KEY_ERROR)
                    {
                        $tables_russian_names = array();
                        $related_tables = $this->get_related_tables($table);
                        // так как имя каждой таблицы совпадает с одноименным yml-файлом, считаем из него заголовок
                        foreach($related_tables as $related_table)
                        {
                            $temp_table_yml_file = $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName($related_table));
                            $tables_russian_names[] = $temp_table_yml_file['page_header'];
                        }
                        $error_message = 'Обязательна установленная связь с таблицами '.implode(',',$tables_russian_names);
                        throw new Exception($error_message);
                    }
                    else
                    {
                         throw new Exception($this->db->_error_message());
                    }
                }
                return $res;
            }
            else
            {
                $this->data_logs($table,0,$v);//заносим  в таблицу логов
                $this->db->insert($table,$v);

            }
        }
        return;
    }
    function _edit($table,$id,$data){
        if($this->table_data_logs == $table)
            return;
        $this->data_logs($table,$id,$data);//заносим  в таблицу логов
        $this->db->where($this->codexadmin->primary_key,$id);
        $res = $this->db->update($table,$data);
        
        
        // ВАЖНО!! РАБОТАЕТ ТОЛЬКО ПРИ ОТКЛЮЧЕННОМ ДЕБАГЕ В config/database.php
        // 1452 - код ошибки вставки с неустановленным ключем.

        if(empty($res) && $this->db->_error_number() == DATABASE_INSERT_FOREIGN_KEY_ERROR)
        {
            $tables_russian_names = array();
            $related_tables = $this->get_related_tables($table);
            // так как имя каждой таблицы совпадает с одноименным yml-файлом, считаем из него заголовок
            foreach($related_tables as $related_table)
            {
                $temp_table_yml_file = $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName($related_table));
                $tables_russian_names[] = $temp_table_yml_file['page_header'];
            }
            $error_message = 'Обязательна установленная связь с таблицами '.implode(',',$tables_russian_names);
            throw new Exception($error_message);
        }
        else if(empty($res))
        {
             throw new Exception($this->db->_error_message());
        }
        return $res;
        /*echo $this->db->last_query();
        exit;*/
    }
    function delete($table,$id){
        if($this->table_data_logs == $table)
            return;
        $this->data_logs($table,$id);//заносим  в таблицу логов
        $res = $this->db->delete($table, array($this->codexadmin->primary_key => $id));
        if($this->db->_error_number())
        {
            if($this->db->_error_number() == DATABASE_DELETE_FOREIGN_KEY_ERROR)
            {
                $tables_russian_names = array();
                $related_tables = $this->get_related_tables($table);
                // так как имя каждой таблицы совпадает с одноименным yml-файлом, считаем из него заголовок
                foreach($related_tables as $related_table)
                {
                    $temp_table_yml_file = $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName($related_table));
                    $tables_russian_names[] = $temp_table_yml_file['page_header'];
                }
                $error_message = 'Нельзя удалить записть, пока существует связь с таблицами '.implode(',',$tables_russian_names);
                throw new Exception($error_message);
            }
            else
            {
                throw new Exception($this->db->_error_message());
            }
        }
        return $res;
    }
    //
    function copy_row($table,$id){
        if($this->table_data_logs == $table)
            return;
        $this->db->where($this->codexadmin->primary_key, $id);
        $row = $this->db->get($table,1);
        if($row->num_rows == 1){
            $insert = array();
            foreach($row->row() as $key=>$val)
                if($key != $this->codexadmin->primary_key)
                    $insert[$key] = $val;
        
            return $this->db->insert($table,$insert);
        }
        return;
    }
    //
    function get_where($table,$data){
        if(strcmp(CI_VERSION,'1.6.0') < 0)
            return $this->db->getwhere($table,$data);
        else
            return $this->db->get_where($table,$data);
    }
    function get_users()
    {
        $this->db->select('date_cr');
        $rows = $this->db->get('USERS');
        if($rows->num_rows > 0)
            return $rows->result();
        return array();
    }
    
    /**
    * Получить связанные с помощью внешних ключей таблицы
    * 
    * @param string $tablename имя таблицы
    * @return array список таблиц
    */
    function get_related_tables($tablename)
    {
        // получим список связей для переданной таблицы в текущей базе данных
        $sql = "SELECT * 
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA =  '{$this->db->database}'
                    AND TABLE_NAME =  '{$tablename}'
                    AND CONSTRAINT_NAME <>  'PRIMARY'
                    AND REFERENCED_TABLE_NAME IS NOT NULL 
        ";
        $tables = $this->db->query($sql)->result_array();
        $result = array();
        foreach($tables as $table)
            $result[] = $table['REFERENCED_TABLE_NAME'];
        return $result;
    }
}