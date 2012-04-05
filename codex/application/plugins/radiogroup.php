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
            <label for="'.$this->name.'">
                '.$this->label.'
            </label>
            ';
        $html .= '<ul class="radio-group">';
        $html .= $this->getList();
        $html .= '</ul>';
        $html .= $this->suffix;
		
		return $html;
	}
	function getList()
	{
		$html = '';
		foreach($this->params['list'] as $k => $v){
            $html .= '<li><input type="radio" name="'.$this->name.'" value="'.$k.'"';
            if($this->value == $k)
               $html .= ' checked'; 
            $html .= '><label for="'.$this->name.'">'.$v.'</label><div class="clear"></div></li>'."\n";
        }
        $html .= '';
		return $html;
	}
}

?>
