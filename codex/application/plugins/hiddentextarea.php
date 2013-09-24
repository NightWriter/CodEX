<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
class hiddenTextarea extends codexForms
{
    var $_params = array();
    function hiddenTextarea($name,$params) { 
        $params['attributes']['cols'] = (isset($params['attributes']['cols']))? $params['attributes']['cols'] : 40 ;
        $params['attributes']['rows'] = (isset($params['attributes']['rows']))? $params['attributes']['rows'] : 10 ;
        codexForms::initiate($name,$params);
        $this->_params = $params;
    }

    function prepForDB($value){
        if(isset($this->_params['params']['serialize']))
            return serialize($value);
        return $value;//(nl2br($value));
    }

    function prepForDisplay($value){
        $value = (stripslashes(strip_tags($value,'<br /><br>')));
        if (function_exists('mb_strlen') && mb_strlen($value,"utf8")>100)
            $value = (mb_substr($value,0,100,"utf8").'...');
        
        return $value;
    }

	function getHTML()
	{
        $CI = &get_instance();
        $html = "";
        if(($CI->codexadmin->active_id))
        {
            $html .= $this->prefix;
            
            if(isset($this->_params['params']['serialize'])){
                $this->value = unserialize($this->value);
                $temp = '';
                if(is_array($this->value))
                    foreach($this->value as $key=>$val){
                        $temp .= $key.': '.$val.'<br />'."\n";
                    }
                $this->value = $temp;
                unset($temp);
            }
            if($this->getMessage($this->name))
                $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

            /*$html .= '
                <label for="'.$this->element_name.'">
                    '.$this->label.'
                </label>
                <textarea id="'.$this->name.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'>'.$this->br2nl(stripslashes(strip_tags($this->value))).'</textarea>
            ';*/ 
            if(!isset($this->attributes['class'])){
                $this->attributes['class']='input-xlarge';
            }
            if(isset($this->_params['params']['serialize'])){
                $html .= '
                <table border=0>
                    <tr>
                        <td> 
                            <label for="'.$this->element_name.'"  class="control-label">
                                '.$this->label.'
                            </label>
                        </td>
                        <td>'.$this->value.'</td>
                    </tr>
                </table>';
            }else{
               $html .= '
                <label for="'.$this->element_name.'" class="control-label">
                    '.$this->label.'
                </label><div class="controls">
                <textarea id="'.$this->name.'" name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'>'.$this->value.'</textarea></div>';
            }
		    $html .= $this->suffix;
		}
		return $html;
	}
    function br2nl($text)
    {
        return  preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $text);
    }
}
?>
