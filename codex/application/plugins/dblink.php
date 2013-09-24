<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class DbLink extends codexForms
{
    var $_params = array();
    
    function DbLink($name,$params) { 
        codexForms::initiate($name,$params);
        $this->_params = $params;
    }

    function prepForDisplay($value){
        $CI = & get_instance();
        $str = '';
        $CI->db->select($this->_params['params']['fields']);
        $CI->db->where($this->_params['params']['primary_key'],$value);
        $row = $CI->db->get($this->_params['params']['table'],1);
        
        if($row->num_rows == 1){
            $fields = explode(',',$this->_params['params']['fields']);
            foreach($fields as $field)
                $str .= $row->row()->$field . ' ';
        }
        return '<a href="'.str_replace('{num}',$value,$this->_params['params']['link']).'">'.$str.'</a>';
    }

    function getHTML()
    {
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label class="control-label" for="'.$this->element_name.'">
                '.$this->label.'
            </label>';
        $html .= '<div class="controls">
            <input class="text" id="codexdatepicker'.$this->name.'" type="text" value="'.$this->value.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'>
            </div>';
        $html .= $this->suffix;
        
        return $html;
    }
}

?>