<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include_once('relationalcontainer.php');
class ManyToMany extends RelationalContainer
{
    var $primary_key = 'id';
    var $display_field = ''; //The name of the field to show in the <select>
    var $change_field = '';
    var $local_table = '';
    var $foreign_table = '';

    function ManyToMany($name,$params) { 
        RelationalContainer::RelationalContainer($name,$params);
        
        $this->local_table = $this->CI->codexadmin->db_table;
        $this->foreign_table = $name;
        
        if(isset($params['params']['change_field']))
            $this->change_field = $params['params']['change_field'];
            
        $this->primary_key = (isset($this->params['primary_key']))?
                                   $this->params['primary_key'] :
                                   'id';

        $this->display_field = (isset($this->params['display_field']))?
                                   $this->params['display_field'] : 
                                   show_error('A display field must be defined in your YAML file for the ManyToMany plugin ('.$name.')');
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
            $result = $this->CI->codexmodel->get_where($this->foreign_table,array($this->primary_key=>$link[$this->foreign_table.'_id']));
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
        $selected_items = $this->CI->input->post($this->foreign_table.'_selected_values');
        
        if($new_items = $this->CI->input->post($this->foreign_table) AND is_array($new_items) AND count($new_items) > 0){
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
        $this->CI->db->delete($this->getLinkingTableName(),array($this->local_table.'_id'=>$this->CI->input->post($this->CI->codexadmin->primary_key)));
        if($selected_items AND is_array($selected_items))
            foreach($selected_items as $foreign_id){
                $new_linked_items = array();
                $new_linked_items[$this->local_table.'_id'] = $this->CI->input->post($this->CI->codexadmin->primary_key); 
                $new_linked_items[$this->foreign_table.'_id'] = $foreign_id;
                
                if(!empty($this->change_field) && !empty($_POST[$this->change_field][$foreign_id]))
                    $new_linked_items[$this->change_field] = $_POST[$this->change_field][$foreign_id];
                
                $this->CI->db->insert($this->getLinkingTableName(),$new_linked_items);
                if($this->CI->db->affected_rows() == 0)
                    show_error('There was an error inserting new item into the linking table.');
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
    function getPreselectedRecords(){

        if( !$this->CI->db->table_exists($this->getLinkingTableName()) )
            return array();
            
        $list = array();
        $preselected_fields = $this->CI->codexmodel->get_where($this->getLinkingTableName(),array($this->local_table.'_id'=>$this->CI->codexadmin->active_id));
        foreach($preselected_fields->result_array() as $link){
            $this->CI->db->select($this->primary_key);
            $this->CI->db->select($this->display_field);
            $result = $this->CI->codexmodel->get_where($this->foreign_table,array($this->primary_key=>$link[$this->foreign_table.'_id']));
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
}

?>
