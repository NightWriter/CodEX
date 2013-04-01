<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class TextBox extends codexForms
{
    function TextBox($name,$params) { 
        codexForms::initiate($name,$params);
    }

	function getHTML()
	{
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';
        $html .= '
            <label class="control-label" for="'.$this->element_name.'">
                '.$this->label.'
            </label><div class="controls">';
        $html .= '    <input class="input-xlarge '.(!empty($this->attributes['class'])? $this->attributes['class']:'').'" type="text" value="'.$this->value.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'></div>
        ';

		$html .= $this->suffix;
		
		return $html;
	}
}

?>
