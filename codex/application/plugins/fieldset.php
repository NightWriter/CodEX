<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include_once('formcontainer.php');
class FieldSet extends FormContainer
{
    function FieldSet($name,$params) { 
        FormContainer::FormContainer($name,$params);
    }
	function getHTML()
	{
        $html = '';
        $html .= '<fieldset>';
        $html .= '<legend>'.$this->label.'</legend>';
        $html .= $this->form->getHTML();
        $html .= '</fieldset>';
		return $html;
	}
}

?>
