<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include_once("dropdown.php");

class DbTextOne extends DropDown{
    var $primary_key = 'id';
    var $table = '';
    var $field = '';
    var $where_fields = array();
    var $where_values = array();
    
    function DbTextOne($name,$params){
        DropDown::DropDown($name,$params);
        if(isset($this->params['primary_key']))
            $this->primary_key = $this->params['primary_key'];

        if(isset($this->params['table']))
            $this->table = $this->params['table'];
        else
            show_error($this->lang->line('codexforms_dbdropdown_table_not_defined'));
        
        if(isset($this->params['where_fields']))
            $this->where_fields = explode(',',$this->params['where_fields']);
        if(isset($this->params['where_values']))
            $this->where_values = explode(',',$this->params['where_values']);
        
        if(isset($this->params['field']))
            $this->field = $this->params['field'];
        else
            show_error($this->lang->line('codexforms_dbdropdown_field_not_defined'));
    }
    function prepForDisplay($value){
        if(empty($value))
            return '';
        $CI = &get_instance();
        $CI->db->select($this->field);
        $CI->db->where($this->primary_key,$value);
        
        for($i=0;$i<=sizeof($this->where_fields);$i++)
            if(isset($this->where_fields[$i]) && $this->where_values[$i])
                $CI->db->where($this->where_fields[$i],$this->where_values[$i]);
        
        $query = $CI->db->get($this->table);

        $result = $query->result_array();
        if(count($result) == 0)
            return '';
        else
            return $result[0][$this->field];
    }

    function getList(){
        $CI = &get_instance();
        if(!isset($CI->db)) $CI->load->database();

        $field = $this->params['field'];
        $table = $this->params['table'];
        
        $CI->db->select($this->primary_key);
        $CI->db->select($field);
        //
        $CI->db->where($this->primary_key,$this->value);
        
        for($i=0;$i<=sizeof($this->where_fields);$i++)
            if(isset($this->where_fields[$i]) && $this->where_values[$i])
                $CI->db->where($this->where_fields[$i],$this->where_values[$i]);
                
        $html = '';
        
        $result = $CI->db->get($table);
        foreach($result->result_array() as $row){
            if($row[$this->primary_key] == $this->value)
                $html .= "<option value=\"".$row[$this->primary_key]."\" selected>".$row[$field]."</option>\n";
            else
                $html .= "<option value=\"".$row[$this->primary_key]."\">".$row[$field]."</option>\n";
        }

        return $html;
    }
}

?>