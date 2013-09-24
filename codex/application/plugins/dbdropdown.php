<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include_once("dropdown.php");

class DbDropDown extends DropDown{
    var $primary_key = 'id';
    var $table = '';
    var $field = '';
    var $where_fields = array();
    var $where_values = array();
    var $field2='';
    private $order_by = '';
    private $order_type = '';
    
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
        if(isset($this->params['field2']))
            $this->field2 = $this->params['field2'];
            
        if(isset($this->params['order_by']))
            $this->order_by = $this->params['order_by'];
        if(isset($this->params['order_type']))
            $this->order_type = $this->params['order_type'];
        
    }
    function prepForDisplay($value){
        if(empty($value))
            return '';
        
        $CI = &get_instance();
        
        $CI->db->select($this->field);
        $CI->db->select($this->field2);
        
        if(!empty($this->_params['params']['multiple']))
            $CI->db->where_in($this->primary_key,unserialize($value));
        else
            $CI->db->where($this->primary_key,$value);
        
        for($i=0;$i<=sizeof($this->where_fields);$i++)
        {
            if(isset($this->where_fields[$i]) && isset($this->where_values[$i])){
                if(preg_match('#(.*)>>>(.*)#',$this->where_values[$i],$matches)){
                    switch($matches[1]){
                        case 'from_session':
                            $CI->db->where($this->where_fields[$i],$CI->codexsession->userdata($matches[2]));
                        break;
                        default:
                            $CI->db->where($this->where_fields[$i],$this->where_values[$i]);
                    }
                }else{
                    $CI->db->where($this->where_fields[$i],$this->where_values[$i]);    
                }
                
            }       
        } 
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
                $temp .= $val[$this->field].((!empty($this->field2) && !empty($val[$this->field2]))?' ('.$val[$this->field2].')':'').',';
            return rtrim($temp,',');
        }else
            return $result[0][$this->field].((!empty($this->field2) && !empty($result[0][$this->field2]))?' ('.$result[0][$this->field2].')':'');
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
        $field2=null;
        if (isset($this->params['field2'] )) $field2 = $this->params['field2']; 
        /*if(isset($this->params['where_fields']))
            $this->where_fields = explode(',',$this->params['where_fields']);
        if(isset($this->params['where_values']))
            $this->where_values = explode(',',$this->params['where_values']);*/
            
        $CI->db->select($this->primary_key);
        $CI->db->select($field);
        if ($field2) $CI->db->select($field2); 
        
        if(!empty($this->order_by))
            $CI->db->order_by($this->order_by,$this->order_type);
        else
            $CI->db->order_by($field);
            
        for($i=0;$i<=sizeof($this->where_fields);$i++)
            if(isset($this->where_fields[$i]) && isset($this->where_values[$i])){
                if(preg_match('#(.*)>>>(.*)#',$this->where_values[$i],$matches)){
                    switch($matches[1]){
                        case 'from_session':
                            $CI->db->where($this->where_fields[$i],$CI->codexsession->userdata($matches[2]));
                        break;
                        default:
                            $CI->db->where($this->where_fields[$i],$this->where_values[$i]);
                    }
                }else{
                    $CI->db->where($this->where_fields[$i],$this->where_values[$i]);    
                }
                
            }   
        //$result = $CI->db->query("SELECT ".$this->primary_key.",$field FROM $table");
        $result = $CI->db->get($table);
                                                       
        foreach($result->result_array() as $row){
            if($row[$this->primary_key] == $this->value){
                $html .= "<option value=\"".$row[$this->primary_key]."\" selected title=\"".$row[$field]."\">".($row[$field]);
                if ($field2) $html .=" (".$row[$field2].")";
                $html .="</option>\n";
            } else if(!empty($this->_params['params']['multiple']) && !empty($this->value) && in_array($row[$this->primary_key],unserialize($this->value)))
            {
              
              $html .= "<option value=\"".$row[$this->primary_key]."\" selected title=\"".$row[$field]."\">".($row[$field]);
                if ($field2) $html .=" (".$row[$field2].")";
                $html .="</option>\n";
            } else
                {$html .= "<option value=\"".$row[$this->primary_key]."\" title=\"".$row[$field]."\">".$row[$field];
                 if ($field2) $html .=" (".$row[$field2].")";
                $html .="</option>\n";
                }
        }

        return $html;
    }
}