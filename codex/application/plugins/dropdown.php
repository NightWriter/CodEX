<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
class DropDown extends codexForms
{
    var $_params = array();
    function DropDown($name,$params) { 
        codexForms::initiate($name,$params);
        if(array_key_exists('enum',$this->params)){
            $this->params['list'] = $this->params['enum'];
        }
        $this->_params = $params;
    }

    function prepForDisplay($value){
        if($value != "" && isset($this->params['list'][$value]))
            return $this->params['list'][$value];
        return '';
    }

	function getList()
	{
		$html = "<option value =\"\"></option>";
		foreach($this->params['list'] as $k => $v)
		{
			if($k == $this->value)
				$html .= "<option value=\"$k\" selected>$v</option>\n";
			else
				$html .= "<option value=\"$k\">$v</option>\n";
		}
		return $html;
	}
    //
	function prepForDB($value){
        if(isset($this->_params['params']['multiple']))
            return serialize($value);
        return $value;//(nl2br($value));
    }
    //
	function getHTML()
	{
        $html = "";
        $html .= $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'" class="control-label">
                '.$this->label.'
            </label>';
        $html .= '<select name="'.$this->element_name.((isset($this->params['multiple']) AND $this->params['multiple'] == true)?'[]':'').'" ';

        if(isset($this->params['multiple']) AND $this->params['multiple'] == true)
            $html .= "multiple ";

        $html .=$this->getAttributes($this->attributes).'>';

        $html .= $this->getList();

        $html .= '</select>';
        $html .= $this->suffix;
		
		return $html;
	}
}
?>
