<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class RadioGroup extends codexForms
{
    function RadioGroup($name,$params) { 
        codexForms::initiate($name,$params);
    }

    function prepForDisplay($value){
        if(!empty($value) || !isset($value))
            return $this->params['list'][$value];
        else
            return "";
    }

	function getHTML()
	{
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .='
            <label for="'.$this->name.'"  class="control-label">
                '.$this->label.'
            </label><div class="controls">
            ';
        
        $html .= $this->getList()."</div>";
        $html .= $this->suffix;
		
		return $html;
	}
	function getList()
	{
		$html = '';
		foreach($this->params['list'] as $k => $v){
            $html .= '<label for="'.$this->name.'" class="radio"><input type="radio" name="'.$this->name.'" value="'.$k.'"';
            if($this->value == $k)
               $html .= ' checked'; 
            $html .= '>'.$v.'</label>'."\n";
        }
        $html .= '';
		return $html;
	}
}

?>
