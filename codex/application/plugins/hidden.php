<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Hidden extends codexForms
{
    function Hidden($name,$params) { 
        codexForms::initiate($name,$params);
        $CI = &get_instance();
        if(empty($this->value)) show_error($CI->lang->line('codexforms_hidden_value_not_defined'));
    }

    function prepForDb($value){
        return $this->value;
    }

	function getHTML()
	{
         return '<input type="hidden" value="'.$this->value.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'> ';
	}
}

?>
