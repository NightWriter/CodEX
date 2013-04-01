<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class ReadOnly extends codexForms
{
    function ReadOnly($name,$params) { 
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
            </label>';
        $html .= '    <div class="controls"><input class="input-xlarge " type="text" readonly="readonly" value="'.$this->value.'" '.$this->getAttributes($this->attributes).'></div>
        ';

        $html .= $this->suffix;
        
        return $html;
    }
}