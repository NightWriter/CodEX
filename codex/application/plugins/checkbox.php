<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/*
 * =======================================================
 *
 *                  CodeExtinguisher by jTaby
 *                      CheckBox Plugin
 *                  -------------------------
 *
 *                  YAML Configuration Options:
 *
 *      +=============+================+================+
 *      |  Required?  |      Name      |     Value      |
 *      +=============+================+================+
 *      |     Yes     |      class     |    Checkbox    |
 *      |     No      |      Value     | Default Value  |
 *      |     No      |    Attributes  |  Default Atts  |
 *      |     No      |      Label     |     Label      |
 *      +-------------+----------------+----------------+
 *
 *                           Example:
 *
 *      field_name:
 *          class: CheckBox
 *          value: true
 *          label: This is a sample Label
 *
 * 
 * =======================================================
 */
class CheckBox extends codexForms
{
    function CheckBox($name,$params) { 
        codexForms::initiate($name,$params);
    }

    function prepForDb ($value){ 
        if(array_key_exists($this->name,$_POST))
            return '1';
        else{
            return '';
        }
    }

    function in_arrayr($needle, $haystack) {
            foreach ($haystack as $v) {
                    if ($needle == $v) return true;
                    elseif (is_array($v)) return $this->in_arrayr($needle, $v);
            }
            return false;
    }

    function prepForDisplay($value){
        
        $CI = & get_instance();
        
        if($value == 'y' || $value == 1)
            return '<a href="'.site_url($CI->controller_link.'&update_object=change_checkbox').'" class="checkbox_updated" data-primary_key=\''.$CI->codexadmin->primary_key.'\' data-primary_value=\''.$CI->codexadmin->active_id.'\' data-value=\'0\' data-field=\''.$this->name.'\' data-table=\''.$CI->table.'\' "><img src="'.base_url().'codex/images/status_1.png" id="'.$this->name.$CI->codexadmin->active_id.'" border="0" /></a>';
        else 
            return '<a href="'.site_url($CI->controller_link.'&update_object=change_checkbox').'" class="checkbox_updated" data-primary_key=\''.$CI->codexadmin->primary_key.'\' data-primary_value=\''.$CI->codexadmin->active_id.'\' data-value=\'1\' data-field=\''.$this->name.'\' data-table=\''.$CI->table.'\' "><img src="'.base_url().'codex/images/status_0.png" id="'.$this->name.$CI->codexadmin->active_id.'" border="0" /></a>';
    }

	function getHTML()
	{
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'"  class="control-label">
                '.$this->label.'
            </label><div class="controls">
            <input type="checkbox" value="1" 
        '; 
        if($this->value == 'y' || $this->value == 1) 
            $html .= 'checked'; 

        $html.='
            name="'.$this->element_name.'" '.$this->getAttributes($this->attributes).'></div>
        ';

		$html .= $this->suffix;
		
		return $html;
	}
}
?>
