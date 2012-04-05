<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Date extends codexForms
{
    function Date($name,$params) { 
        codexForms::initiate($name,$params);
    }

    function prepForDisplay($value){
        if(!empty($value) AND ($value != 0 AND $value != "0000-00-00")){
            $explode = explode('-',$value);
            
            $_explode = explode(' ',$explode[2]);
            
            if(sizeof($_explode) == 1){
                return date('F j, Y',mktime(0,0,0,$explode[1],$explode[2],$explode[0]));
            }else{
                return $value;
            }
        } 
        else
            return '';
    }

	function getHTML()
	{
        $CI = &get_instance();
        $CI->codextemplates->cssFromAssets('css-datepicker','ui.datepicker.css');
        $CI->codextemplates->jsFromAssets('js-datepicker','ui.datepicker.js');

        if(!empty($this->params['default_value']))
            if($this->params['default_value'] == 'today')
                $this->value = date('Y-m-d');
                
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'">
                '.$this->label.'
            </label>';
        $html .= '
            <input class="text" id="codexdatepicker'.$this->name.'" type="text" value="'.$this->value.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'>
        ';
        $js ="$(document).ready(function() {
                $('#codexdatepicker".$this->name."').datepicker({dateFormat: 'yy-mm-dd'});
              });";
        $CI->codextemplates->inlineJS('js-'.$this->name.'-init',$js); 
		$html .= $this->suffix;
		
		return $html;
	}
}

?>
