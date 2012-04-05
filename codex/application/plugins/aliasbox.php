<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class AliasBox extends codexForms
{
    function AliasBox($name,$params) { 
        codexForms::initiate($name,$params);
    }

    function getHTML()
    {
        $rel = '';
        if(!empty($this->params['table']) && !empty($this->params['field']))
            $rel = $this->params['table'].','.$this->params['field'];
        
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'">
                '.$this->label.'
            </label>';
        $html .= '    <input class="text alias" type="text" rel="'.$rel.'" value="'.$this->value.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'>
        <img src="'.base_url().'codex/images/no.jpg"  id="alias_no" style="display:none" class="alias_img">
        <img src="'.base_url().'codex/images/yes.jpg" id="alias_yes" style="display:none" class="alias_img">
        ';

        $html .= $this->suffix;
        
        return $html;
    }
}
