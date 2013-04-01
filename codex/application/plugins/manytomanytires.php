<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include_once('relationalcontainer.php');
class ManyToManyTires extends RelationalContainer
{
    var $primary_key = 'id';
    var $display_field = ''; //The name of the field to show in the <select>
    var $change_field = '';
    var $local_table = '';
    var $foreign_table = '';
    var $required_table_name = '';
    var $far_key = '';
    
    function ManyToManyTires($name,$params) {
        RelationalContainer::RelationalContainer($name,$params);
        $this->local_table = $this->CI->codexadmin->db_table;
        $this->foreign_table = $name;
        if(isset($params['params']['table_name']) && $params['params']['table_name'] != '')
        {
            $this->required_table_name = $params['params']['table_name'];
        }
        if(isset($params['params']['far_key']))
            $this->far_key = $params['params']['far_key'];
        if(isset($params['params']['change_field']))
            $this->change_field = $params['params']['change_field'];
            
        $this->primary_key = (isset($this->params['primary_key']))?
                                   $this->params['primary_key'] :
                                   'id';

        $this->display_field = (isset($this->params['display_field']))?
                                   $this->params['display_field'] : 
                                   show_error('A display field must be defined in your YAML file for the ManyToMany plugin ('.$name.')');
        $this->can_add_new= (isset($this->params['can_add_new'])? $this->params['can_add_new']: true);

    }
	
    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function prepForDisplay($value){
        $query = $this->CI->codexmodel->get_where( $this->getLinkingTableName(),
                                           array($this->local_table.'_id'=>$this->CI->codexadmin->active_id) );
        $list = array();
        foreach($query->result_array() as $link){
            $far_key = $link[$this->foreign_table.'_id'];      
            $result = $this->CI->codexmodel->get_where($this->foreign_table,array($this->primary_key=>$far_key));
            if($result->num_rows() > 0){
                $result = $result->result_array();
                
                $display_field = '';
                
                if(is_array($this->display_field))
                {
                    foreach($this->display_field as $df)
                    {
                        $display_field .= $result[0][$df].' ';
                    }
                }else{
                    $display_field = $result[0][$this->display_field];
                }
                $display_field = trim($display_field);
                
                if(!empty($this->change_field))
                    $list[] = $display_field. '(' .$link[$this->change_field]. ')';
                else
                    $list[] = $display_field;
            }
        }
        return implode(', ',$list);
    }
    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function prepForDb($data,$field){
        return NULL;
    }
    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function prepForDelete(){
        $this->CI->db->delete($this->getLinkingTableName(),array($this->local_table.'_id'=>$this->CI->codexadmin->active_id));
    }
    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function postInsertHook($data){
        $tables = array($this->foreign_table, 'factory_tires');
        foreach($tables as $table)
        {
            $is_factory = $table == 'factory_tires' ? 1 : 0;
            $selected_items = $this->CI->input->post($table.'_selected_values');
            $new_items = $this->CI->input->post($table);

            if(is_array($new_items) AND count($new_items) > 0){
                foreach($new_items as $row){

                    $row = $this->form->iterate('prepForDb',$row);
                    foreach($row as $name=>$value)
                        if($value === NULL) unset($row[$name]);
                
                    $this->CI->db->insert($this->foreign_table,$row);
                    if($this->CI->db->affected_rows() == 0)
                        show_error('There was an error inserting new item into foreign table.');
                    else
                        $selected_items[] = $this->CI->db->insert_id();
                }
            }
            $linking_table = $this->getLinkingTableName();
            $this->CI->db->delete($linking_table,array($this->local_table.'_id'=>$this->CI->input->post($this->CI->codexadmin->primary_key), $linking_table.'.is_factory' => $is_factory));
            if($selected_items AND is_array($selected_items))
                foreach($selected_items as $foreign_id){
                    $new_linked_items = array();
                    $new_linked_items[$this->local_table.'_id'] = $this->CI->input->post($this->CI->codexadmin->primary_key); 
                    $far_key = !empty($this->far_key)?$this->far_key:$this->foreign_table.'_id';
                    $new_linked_items[$far_key] = $foreign_id;
                    $new_linked_items['is_factory'] = $is_factory; // тип шин - фабричные или нет
                    if(!empty($this->change_field) && !empty($_POST[$this->change_field][$foreign_id]))
                        $new_linked_items[$this->change_field] = $_POST[$this->change_field][$foreign_id];
                    $this->CI->db->insert($this->getLinkingTableName(),$new_linked_items);
                    if($this->CI->db->affected_rows() == 0)
                        show_error('There was an error inserting new item into the linking table.');
                }
        }
    }
    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function postEditHook ($data){ 
        $this->prepForDelete();
        $this->postInsertHook($data);
    }

    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getLinkingTableName(){
        if(!empty($this->required_table_name))
        {
            return $this->required_table_name;
        }
        if(strcmp($this->local_table,$this->foreign_table) < 0)
            return $this->local_table.'_'.$this->foreign_table;
        else
            return $this->foreign_table.'_'.$this->local_table;
    }


    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getPreselectedRecords($is_factory = 1){

        if( !$this->CI->db->table_exists($this->getLinkingTableName()) )
            return array();
            
        $list = array();
        $linking_table_name = $this->getLinkingTableName(); // связующая таблица
        $preselected_fields = $this->CI->codexmodel->get_where($linking_table_name,array($this->local_table.'_id'=>$this->CI->codexadmin->active_id, $linking_table_name.'.is_factory' => $is_factory));
        foreach($preselected_fields->result_array() as $link){
            
            //$this->CI->db->select($this->primary_key);
            //$this->CI->db->select($this->display_field);
            
            $far_key = !empty($this->far_key)?$link[$this->far_key]:$link[$this->foreign_table.'_id'];            
            $result = $this->CI->codexmodel->get_where($this->foreign_table,array($this->primary_key=>$far_key));
            if($result->num_rows() > 0){
                $result = $result->result_array();
                
                if(!empty($this->change_field))
                    $list[] = array_merge($result[0],array($this->change_field=>$link[$this->change_field]));
                else
                    $list[] = $result[0];
            }
        }
        
        $this->preselected_rows =  $list;

    }
    // а тут нам нужно вывести 2 одинаковых таблицы для шин. 
    function getHTML()
    {
       $html = $this->htmlMultiselectForming('tires', 0, 'Matching tires');
       $html .= '<hr>';
       $html .= $this->htmlMultiselectForming('factory_tires', 1, 'Factory tires');
        
       $this->setupJS('tires');
       $this->setupJS('factory_tires');
        
       return $html;
    }
    
    function htmlMultiselectForming($name = 'tires', $is_factory = 1, $label)
    {
        $all_foreign_records = $this->getAllForeignRecords();
        $this->getPreselectedRecords($is_factory);

        foreach($all_foreign_records as $key=>$row){
            if($this->isSelected($row[$this->primary_key])){
                unset($all_foreign_records[$key]);
            }
        }


        if(isset($_POST[$name.'_selected_values'])){
            foreach($_POST[$name.'_selected_values'] as $id){
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
        if(isset($_POST[$name.'_null'])){
            foreach($_POST[$name.'_null'] as $id){
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

        if($this->getMessage($name))
            $html .= '<div class="failure">'.$this->getMessage($name).'</div>';

        $html .= ' <label for="'.$name.'"  class="control-label" >
                    '.$label.'
                   </label>';

        $html .='<div class="controls"><div class="'.$name.'-relational codex-relational"  style="float:left">
                   <select style="float:left;width:270px;" class="codex-relational-left" name="'.$name.'_null[]" multiple></div>'."\n";

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
                // т.к. это шины, то после названия покажем еще размерность и тип
                $additional_string = "(";
                $additional_string .= $row['front_width'].'/'.$row['front_height_to_width'].'R'.$row['front_rim_size'];
                // если передние и задние комплекты колес отличаются
                if($row['front_width'] != $row['back_width'] || $row['front_height_to_width'] != $row['back_height_to_width'] || $row['front_rim_size'] != $row['back_rim_size'])
                    $additional_string .= ' - '.$row['back_width'].'/'.$row['back_height_to_width'].'R'.$row['back_rim_size'];
                $additional_string .= ")";
                $additional_string .= '('.$row['type'].')';
                $display_field .= $additional_string;
                $html .= '<option name="'.$name.'" value="'.$row[$this->primary_key].'">'.$display_field.'</option>'."\n";
        }

        $html .='  </select>
        <div class="codex-relational-move" style="float:left;margin:5px 20px">
                     <button class="btn relational-move" href="#">
                        <i class="icon-chevron-right"></i>
                      </button>
                     <button class="btn relational-move-back" style="display: block; margin-top:5px" href="#">
                        <i class="icon-chevron-left"></i>
                      </button>
                   </div>
                   <select class="codex-relational-right" style="float:left; width:270px" name="'.$name.'_selected_values[]" multiple>';

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
                
                // т.к. это шины, то после названия покажем еще размерность и тип
                $additional_string = "(";
                $additional_string .= $row['front_width'].'/'.$row['front_height_to_width'].'R'.$row['front_rim_size'];
                // если передние и задние комплекты колес отличаются
                if($row['front_width'] != $row['back_width'] || $row['front_height_to_width'] != $row['back_height_to_width'] || $row['front_rim_size'] != $row['back_rim_size'])
                    $additional_string .= ' - '.$row['back_width'].'/'.$row['back_height_to_width'].'R'.$row['back_rim_size'];
                $additional_string .= ")";
                $additional_string .= '('.$row['type'].')';
                $display_field .= $additional_string;
                
            $html .= '<option name="'.$name.'" value="'.$row[$this->primary_key].'">'.$display_field.'</option>'."\n";
        }

        $html .='  </select></div>
                   <div style="clear:both"><table border=0 cellspacing=0 cellpadding=0>';
                   
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
        $html .= '</table></div><div class="clear"></div>
                   
                   <input type="hidden" name="'.$name.'" value="">
                   <div class="'.$name.'-relational-form codex-relational-form">';
        
        if(!empty($this->can_add_new)){
            if(isset($_POST[$name]) AND is_array($_POST[$name])){
                foreach($_POST[$name] as $row){
                    $this->initial_iteration++;
                    $html .= '
                        <div ="'.$name.'-form-extra">
                            <div class="relational-form-close">X</div>
                            '.$this->setupExtraHTML($row,$this->initial_iteration).'
                        </div>';
                }
            }
            else
                $html .= 'Click on the Add new button to add new items to '.$this->label;
            // extra html
            $extra_html = '
                 <div class="'.$name.'manytomany-hidden manytomany-hidden" style="display:none">
                    <div class="'.$name.'-form-extra">
                        <div class="relational-form-close">X</div>
                        '.$this->setupExtraHTML().'
                    </div>
                 </div>';  
            $this->CI->codextemplates->set('extra-form-html',$extra_html);     
        }
        

        $html .= '
                   </div>
                 </div> 
                   ';
        $html .= $this->suffix;
        
        return $html;
    }
    
    function setupJS($name){

        $append_button=empty($this->can_add_new) ? '' : <<<EOT
        //Append the "Add New" link
            $('.{$name}-relational').append('<div style="width:50%;margin-bottom:15px"><span class="{$name}-relational-anchor codex-relational-anchor">Add new</span></div>');
            
            $('.relational-form-close').live('click',function(){
                $(this).parent().slideUp('fast',function(){\$(this).remove();});
            });
EOT;
        
        
        $js = <<<EOT
        var {$name}iter = {$this->initial_iteration};
        $(document).ready(function() {  

            {$append_button}
            
            $('.{$name}-relational-anchor').bind('click',function(){
                //If the button is clicked for the first time, clear the divs contents
                if({$name}iter == 0){
                    $('.{$name}-relational-form').slideUp('fast').empty();
                }
 
                var html = $('.{$name}manytomany-hidden').html();
                html = html.replace(/\{num\}/g,{$name}iter);
                $('.{$name}-relational-form').append(html).slideDown();
                {$name}iter++;
 
                return false;
            });

            //Take the selected elements from the left, and add them to the right
            $('.relational-move').bind('click',function(){
                $("select[name='{$name}_null\[\]'] option:selected").each(function(){
                    $("select[name='{$name}_selected_values\[\]']").append('<option value="'+$(this).attr('value')+'">'+$(this).html()+'</option>');
                    $(this).remove();
                });

                return false;
            });

            //Take the selected elements from the left, and add them to the right
            $('.relational-move-back').bind('click',function(){
                $("select[name='{$name}_selected_values\[\]'] option:selected").each(function(){
                    $("select[name='{$name}_null\[\]']").append('<option value="'+$(this).attr('value')+'">'+$(this).html()+'</option>');
                    $(this).remove();
                });

                return false;
            });

            $("form").submit(function(){
                $("select[name='{$name}_null\[\]']").children().each(function(){
                    $(this).attr('selected','true');
                });
                $("select[name='{$name}_selected_values\[\]']").children().each(function(){
                    $(this).attr('selected','true');
                });
            });
        });
EOT;
        $this->CI->codextemplates->inlineJS($name.'relational-plugin',$js);

    }
}

?>
