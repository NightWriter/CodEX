<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Password extends codexForms
{
    var $hash;
    function Password($name,$params) { 
        codexForms::initiate($name,$params);
        $this->hash = isset($this->params['hash'])? $this->params['hash'] : 'sha1';
    }

    function prepForDb($value){
        if(empty($value))
            return NULL;
        else{
            $str = $this->hash;
            return $str($value);
        }
    }

	function getHTML()
	{
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'" class="control-label">
                '.$this->label.'
            </label><div class="controls">
            <input class="input-xlarge" type="password" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'></div>
        ';

		$html .= $this->suffix;
		
		return $html;
	}
}
?>
