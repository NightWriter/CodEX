<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include_once("dropdown.php");

class DbDropDown extends DropDown{
    var $primary_key = 'id';
    var $table = '';
    var $field = '';
    var $where_fields = array();
    var $where_values = array();
    
    function DbDropDown($name,$params){
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
        
        if(!empty($this->_params['params']['multiple']))
            $CI->db->where_in($this->primary_key,unserialize($value));
        else
            $CI->db->where($this->primary_key,$value);
        
        for($i=0;$i<=sizeof($this->where_fields);$i++)
            if(isset($this->where_fields[$i]) && isset($this->where_values[$i]))
                $CI->db->where($this->where_fields[$i],$this->where_values[$i]);
                
        $query = $CI->db->get($this->table);
        
        $result = $query->result_array();
        
        // для редактирования при просмотре списка
        if(!empty($this->_params['params']['edit_ajax'])){
            $html = '<select onChange="$.post(\''.site_url($CI->controller_link.'/change_select').'\',{primary_key:\''.$CI->codexadmin->primary_key.'\',primary_value:'.$CI->codexadmin->active_id.',value:this.value,field:\''.$this->name.'\',table:\''.$CI->table.'\'})">';
            $this->value = $value;
            $html .= $this->getList();
            $html .= '</select>';
            return $html;
        }
        
        if(count($result) == 0)
            return '';
        elseif(!empty($this->_params['params']['multiple'])){
            $temp = '';
            foreach($result as $key=>$val)
                $temp .= $val[$this->field].',';
            return rtrim($temp,',');
        }else
            return $result[0][$this->field];
    }
    //
    /*function prepForDB($value){
        if(isset($this->_params['params']['multiple']))
            return serialize($value);
        return $value;//(nl2br($value));
    }*/
    //
    function getList($value=array()){
        $CI = &get_instance();
        if(!isset($CI->db)) $CI->load->database();

        $html = "<option value =\"\"></option>";
        $field = $this->params['field'];
        $table = $this->params['table'];
        
        /*if(isset($this->params['where_fields']))
            $this->where_fields = explode(',',$this->params['where_fields']);
        if(isset($this->params['where_values']))
            $this->where_values = explode(',',$this->params['where_values']);*/
            
        $CI->db->select($this->primary_key);
        $CI->db->select($field);
        
        for($i=0;$i<=sizeof($this->where_fields);$i++)
            if(isset($this->where_fields[$i]) && isset($this->where_values[$i]))
                $CI->db->where($this->where_fields[$i],$this->where_values[$i]);
            
        //$result = $CI->db->query("SELECT ".$this->primary_key.",$field FROM $table");
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
