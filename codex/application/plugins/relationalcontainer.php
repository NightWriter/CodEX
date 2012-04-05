<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include_once('formcontainer.php');
class RelationalContainer extends FormContainer
{
    var $CI;
    var $preselected_rows = array();
    var $initial_iteration = 0;

    function RelationalContainer($name,$params) { 
        $this->CI = &get_instance();

        $params['params']['form'] = $this->getFormConfig($name);
        
        FormContainer::FormContainer($name,$params);
    }
    function getFormConfig($field){
        if(file_exists($this->CI->codexadmin->getDefinitionFileName($field))){
            $config = $this->CI->spyc->YAMLLOAD($this->CI->codexadmin->getDefinitionFileName($field));
            $config = $config['form_setup'];
            foreach($config as $name=>$val){
                $config[$name]['element_name'] = $field.'[{num}]['.$name.']';
            }
        }
        else{
            $config = $this->CI->codexforms->getSetupForTable($field,$field.'[{num}][',']');
        }
        return $config;
    }
    function getIterateNames(){
        return $this->name;
    }

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getDisplayName(){
        return $this->name;
    }

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getFieldName(){
        return NULL;
    }

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getFieldList(){
        if(array_key_exists('table_fields',$this->params)){
            return explode(',',$this->params['table_fields']);
        }
        else{
            return $this->CI->db->list_fields($this->foreign_table);
        }
    }

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function setupJS(){

        $js = <<<EOT
        var {$this->name}iter = {$this->initial_iteration};
        $(document).ready(function() {  
            //Append the "Add New" link
            $('.{$this->name}-relational').append('<div style="width:50%;margin-bottom:15px"><span class="{$this->name}-relational-anchor codex-relational-anchor">Add new</span></div>');

            $('.{$this->name}-relational-anchor').bind('click',function(){
                //If the button is clicked for the first time, clear the divs contents
                if({$this->name}iter == 0){
                    $('.{$this->name}-relational-form').slideUp('fast').empty();
                }
 
                var html = $('.{$this->name}manytomany-hidden').html();
                html = html.replace(/\{num\}/g,{$this->name}iter);
                $('.{$this->name}-relational-form').append(html).slideDown();
                {$this->name}iter++;
 
                return false;
            });

            //Take the selected elements from the left, and add them to the right
            $('.relational-move').bind('click',function(){
                $("select[name='{$this->element_name}_null\[\]'] option:selected").each(function(){
                    $("select[name='{$this->element_name}_selected_values\[\]']").append('<option value="'+$(this).attr('value')+'">'+$(this).html()+'</option>');
                    $(this).remove();
                });

                return false;
            });

            //Take the selected elements from the left, and add them to the right
            $('.relational-move-back').bind('click',function(){
                $("select[name='{$this->element_name}_selected_values\[\]'] option:selected").each(function(){
                    $("select[name='{$this->element_name}_null\[\]']").append('<option value="'+$(this).attr('value')+'">'+$(this).html()+'</option>');
                    $(this).remove();
                });

                return false;
            });

            $("form").submit(function(){
                $("select[name='{$this->element_name}_null\[\]']").children().each(function(){
                    $(this).attr('selected','true');
                });
                $("select[name='{$this->element_name}_selected_values\[\]']").children().each(function(){
                    $(this).attr('selected','true');
                });
            });
        });
EOT;
        $this->CI->codextemplates->inlineJS($this->name.'relational-plugin',$js);


        $js = <<<EOT
        $(document).ready(function() {  

            $('.relational-form-close').livequery('click',function(){
                $(this).parent().slideUp('fast',function(){\$(this).remove();});
            });
        });
EOT;
        $this->CI->codextemplates->inlineJS('relational-plugin',$js);
    }

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getKeyFromIDInArray($key,$value,$records){
        foreach($records as $k=>$v){
            if($v[$key] == $value)
                return $k;
        }
        return NULL;
    }
    function getRecordFromIDInArray($key,$value,$records){
        foreach($records as $k=>$v){
            if($v[$key] == $value)
                return $v;
        }
        return NULL;
    }
	function getHTML()
    {
        $all_foreign_records = $this->getAllForeignRecords();
        $this->getPreselectedRecords();

        foreach($all_foreign_records as $key=>$row){
            if($this->isSelected($row[$this->primary_key])){
                unset($all_foreign_records[$key]);
            }
        }


        if(isset($_POST[$this->element_name.'_selected_values'])){
            foreach($_POST[$this->element_name.'_selected_values'] as $id){
                //the record exists and is previously preselected and remains here
                if($row = $this->getRecordFromIDInArray($this->primary_key,$id,$this->preselected_rows))
                    continue;
                //the record is selected now, but wasn't selected before
                else if($row = $this->getRecordFromIDInArray($this->primary_key,$id,$all_foreign_records)){
                    $this->preselected_rows[] = $row; 
                    unset($all_foreign_records[$this->getKeyFromIDInArray($this->primary_key,$id,$all_foreign_records)]);
                }
            }
        }
        if(isset($_POST[$this->element_name.'_null'])){
            foreach($_POST[$this->element_name.'_null'] as $id){
                //selected, but changed to not selected
                if($row = $this->getRecordFromIDInArray($this->primary_key,$id,$this->preselected_rows)){
                    $all_foreign_records[] = $row;
                    unset($this->preselected_rows[$this->getKeyFromIDInArray($this->primary_key,$id,$this->preselected_rows)]);
                }
                else if($row = $this->getRecordFromIDInArray($this->primary_key,$id,$all_foreign_records))
                    continue;
            }
        }


        $html = "";

        $html .= $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= ' <label for="'.$this->name.'">
                    '.$this->label.'
                   </label>';

        $html .='<div class="'.$this->name.'-relational codex-relational">
                   <select class="codex-relational-left" name="'.$this->element_name.'_null[]" multiple>'."\n";

        foreach($all_foreign_records as $row){
                $display_field = '';
                
                if(is_array($this->display_field))
                {
                    foreach($this->display_field as $df)
                    {
                        $display_field .= $row[$df].' ';
                    }
                }else{
                    $display_field = $row[$this->display_field];
                }
                $display_field = trim($display_field);
                
                $html .= '<option name="'.$this->name.'" value="'.$row[$this->primary_key].'">'.$display_field.'</option>'."\n";
        }

        $html .='  </select>
                   <select class="codex-relational-right" name="'.$this->element_name.'_selected_values[]" multiple>';

        foreach($this->preselected_rows as $row){
                $display_field = '';
                
                if(is_array($this->display_field))
                {
                    foreach($this->display_field as $df)
                    {
                        $display_field .= $row[$df].' ';
                    }
                }else{
                    $display_field = $row[$this->display_field];
                }
                $display_field = trim($display_field);
            $html .= '<option name="'.$this->name.'" value="'.$row[$this->primary_key].'">'.$display_field.'</option>'."\n";
        }

        $html .='  </select>
                   <div class="codex-relational-move">
                     <a class="relational-move" href="#">
                        <img src="'.$this->CI->config->item('codex_asset_folder').'images/relational_move.png">
                      </a>
                      <a class="relational-move-back" href="#">
                        <img src="'.$this->CI->config->item('codex_asset_folder').'images/relational_move_back.png">
                      </a>
                   </div>
                   <div class="clear"></div><table border=0 cellspacing=0 cellpadding=0>';
                   
        if(!empty($this->change_field)){
            foreach($this->preselected_rows as $row){
                    $display_field = '';
                
                    if(is_array($this->display_field))
                    {
                        foreach($this->display_field as $df)
                        {
                            $display_field .= $row[$df].' ';
                        }
                    }else{
                        $display_field = $row[$this->display_field];
                    }
                    $display_field = trim($display_field);
                $html .= '<tr id="change_'.$row[$this->primary_key].'"><td>'.$display_field.':</td><td><input type="text" name="'.$this->change_field.'['.$row[$this->primary_key].']" value="'.$row[$this->change_field].'"></td></tr>'."\n";
            }
        }
        $html .= '</table><div class="clear"></div>
                   
                   <input type="hidden" name="'.$this->name.'" value="">
                   <div class="'.$this->name.'-relational-form codex-relational-form">';
        if(isset($_POST[$this->name]) AND is_array($_POST[$this->name])){
            foreach($_POST[$this->name] as $row){
                $this->initial_iteration++;
                $html .= '
                    <div ="'.$this->name.'-form-extra">
                        <div class="relational-form-close">X</div>
                        '.$this->setupExtraHTML($row,$this->initial_iteration).'
                    </div>';
            }
        }
        else
            $html .= 'Click on the Add new button to add new items to '.$this->label;

        $html .= '
                   </div>
                 </div> 
                   ';
        $html .= $this->suffix;
        $extra_html = '
                 <div class="'.$this->name.'manytomany-hidden manytomany-hidden">
                    <div class="'.$this->name.'-form-extra">
                        <div class="relational-form-close">X</div>
                        '.$this->setupExtraHTML().'
                    </div>
                 </div>';  
        $this->CI->codextemplates->set('extra-form-html',$extra_html);
        $this->setupJS();
		
		return $html;
	}

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function setValue($new_val){
        $this->value = $new_val;
    }


    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function setupExtraHTML($row = array(),$iteration=-1){
        if(count($row) > 0)
            $this->form->populateWithArrayInfo($row);
        else{
            $this->form->iterate('clearValues');
        }
        $html = $this->form->getHTML();
        if($iteration == -1)
            return $html;
        else
            return str_replace('{num}',$iteration,$html);
    }

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getAllForeignRecords(){
        
        if(!empty($this->params['order_by']) && is_array($this->params['order_by']))
        {
            foreach($this->params['order_by'] as $k=>$v)
            {
                if(is_array($v))
                foreach($v as $_k=>$_v)
                    $this->CI->db->order_by($_k,$_v);
            }
        }
        
        $query = $this->CI->db->get($this->foreign_table);
        return $query->result_array();
    }

    function isSelected($val){
        $selected = false;
        foreach($this->preselected_rows as $preselected_row){
            if($val == $preselected_row[$this->primary_key])
                $selected = true;
        }
        return $selected;
    }
}

?>
