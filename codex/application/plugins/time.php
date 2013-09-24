<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/* 
 * Right now, this class is identical to TextBox, however, I plan on adding a js calendar once I settle on one.
 */
class Time extends codexForms
{
	function Time($name,$params)
	{
        codexForms::initiate($name,$params);
    }

    function prepForDisplay($value){
        if($value == "0000-00-00 00:00:00" OR $value == 0 OR empty($value) OR !isset($value))
            return "";
        return date("F j, Y, g:i a",$value);   
    }

    function prepForDb($value){
        if(in_array('on_insert',$this->params)){
           if($value == "0" || $value == "" || $value == 0){
                return mktime();
           }
            else
                return NULL;
        }
        if(in_array('on_update',$this->params) AND $value != ""){
           if($value !== "" || $value !== 0)
                return mktime();
            else
                return NULL;
        }
        return '';
    }

	function getHTML()
	{
        $html = $this->getMessage($this->name);
        $html .= '
            <input type="hidden" value="'.$this->value.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'>';
		
		return $html;
	}
}
?>
