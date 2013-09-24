<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class DateTimeFieldInt extends codexForms
{
    function DateTimeFieldInt($name,$params) { 
        codexForms::initiate($name,$params);
    }

    function prepForDb($value)
    {
        return strtotime($value);
    }
    
    function prepForDisplay($value){
        if(!empty($value) AND ($value != 0 AND $value != "0000-00-00 00:00:00")){     

            if(is_numeric($value))
                $value = date('Y-m-d H:i:s',$value);

            $_parts=explode(' ',$value);
            $_date=explode('-',$_parts[0]);
            $_time=explode(':',$_parts[1]);
            if(count($_date) == 3 && count($_time)==3){
                //return date('F j, Y',mktime(0,0,0,$explode[1],$explode[2],$explode[0]));
                $time= mktime($_time[0],$_time[1],$_time[2],$_date[1],$_date[2],$_date[0]);
                return date('Y-m-d H:i',$time);
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
        $CI->codextemplates->cssFromAssets('css-datepicker','themes/ui-lightness/jquery-ui-1.7.2.custom.css');
        $CI->codextemplates->cssFromAssets('css-datetimepicker','ui.timepicker.css');
        $CI->codextemplates->jsFromAssets('js-ui','jquery-ui-1.8.16.custom.min.js');
        $CI->codextemplates->jsFromAssets('js-datepicker','jquery-ui-timepicker-addon.js');

        if(!empty($this->params['default_value']))
            if($this->params['default_value'] == 'today')
                $this->value = date('Y-m-d H:i:s');
        if(!empty($this->value)){
            
            $this->value = date('Y-m-d H:i:s', $this->value);
            
            $_parts=explode(' ',$this->value);
            $_time=explode(':',$_parts[1]);
            $hours= $_time[0];
            $minutes= $_time[1];
        }else{
            $hours=$minutes=0;
        }            
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'"  class="control-label">
                '.$this->label.'
            </label>';
        $html .= '
          <div class="controls">  <input  class="input-xlarge" id="codexdatetimepicker'.$this->name.'" type="text" value="'.$this->prepForDisplay($this->value).'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'></div>
        ';
        $js ="$(document).ready(function() {
                $('#codexdatetimepicker".$this->name."').datetimepicker({
                    dateFormat: 'yy-mm-dd',
                    timeFormat: 'hh:mm:ss',
                    changeMonth: true,
                    changeYear: true ,
                    timeFormat: 'h:m',
                    hour:'".$hours."',
                    minute:'".$minutes."'
                    });
              });";
        $CI->codextemplates->inlineJS('js-'.$this->name.'-init',$js); 
		$html .= $this->suffix;
		
		return $html;
	}
}