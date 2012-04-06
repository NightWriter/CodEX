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
        $this->data_logs($table,0,$data);//заносим  в таблицу логов
        return $this->db->insert($table,$data);
    }
    function _edit($table,$id,$data){
        if($this->table_data_logs == $table)
            return;
        $this->data_logs($table,$id,$data);//заносим  в таблицу логов
        $this->db->where($this->codexadmin->primary_key,$id);
        return $this->db->update($table,$data);
        /*echo $this->db->last_query();
        exit;*/
    }
    function delete($table,$id){
        if($this->table_data_logs == $table)
            return;
        $this->data_logs($table,$id);//заносим  в таблицу логов
        return $this->db->delete($table, array($this->codexadmin->primary_key => $id));
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
}