<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Date extends codexForms
{
    var $_params = array(); // параметры вывода плагина
    
    function Date($name,$params) {
        $this->_params = $params; // заполнение параметров значениями из yml-файла
        codexForms::initiate($name,$params);
    }

    function prepForDisplay($value){
        // если время хранится в формате integer
        if(!empty($this->_params['params']['type']) && $this->_params['params']['type'] == 'int_time')
        {
            // мы должны переконвертировать число секунд с начала эпохи Unix в стандартное время        
            $value = date('Y-m-d',$value);
        }
        // дальнейший вывод - стандартный и не модифицировался.
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

    function prepForDB($value){
       
        // значит, время хранится в базе данных в формате integer
        if(!empty($this->_params['params']['type']) && $this->_params['params']['type'] == 'int_time')
        {
            // мы должны переконвертировать введенную дату в число секунд с начала эпохи Unix.        
            $value = strtotime($value);
        }
        // в любом случае, возвращаем value
        return $value;
    }
    
    function getHTML()
    {
        $CI = &get_instance();
        //$CI->codextemplates->cssFromAssets('css-datepicker','ui.datepicker.css');
        $CI->codextemplates->cssFromAssets('css-datepicker','themes/ui-lightness/jquery-ui-1.7.2.custom.css');
        //$CI->codextemplates->jsFromAssets('js-datepicker','ui.datepicker.js');
        $CI->codextemplates->jsFromAssets('js-ui','jquery-ui-1.8.16.custom.min.js');
        
        // значит, время хранится в базе данных в формате integer
        if(!empty($this->_params['params']['type']) && $this->_params['params']['type'] == 'int_time')
            $this->value = date('Y-m-d',$this->value);
        
        if(!empty($this->params['default_value']))
            if($this->params['default_value'] == 'today')
                $this->value = date('Y-m-d');
                
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'"  class="control-label">
                '.$this->label.'
            </label>';
        $html .= '
          <div class="controls">  <input  class="input-xlarge" id="codexdatepicker'.$this->name.'" type="text" value="'.$this->value.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'></div>
        ';
        $js ="$(document).ready(function() {
                $('#codexdatepicker".$this->name."').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true 
                    });
              });";
        $CI->codextemplates->inlineJS('js-'.$this->name.'-init',$js); 
        $html .= $this->suffix;
        
        return $html;
    }
}