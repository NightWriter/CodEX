<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include_once('relationalcontainer.php');
class ManyToMany extends RelationalContainer
{
    var $primary_key = 'id';
    var $display_field = ''; //The name of the field to show in the <select>
    var $change_field = '';
    var $local_table = '';
    var $foreign_table = '';
    var $required_table_name = '';
    var $far_key = '';
    var $local_key = '';
    
    function ManyToMany($name,$params)
    {
        RelationalContainer::RelationalContainer($name,$params);
        $this->local_table = $this->CI->codexadmin->db_table;
        $this->foreign_table = $name;
        if(isset($params['params']['table_name']) && $params['params']['table_name'] != '')
        {
            $this->required_table_name = $params['params']['table_name'];
        }
        if(isset($params['params']['far_key']))
            $this->far_key = $params['params']['far_key'];
        if(isset($params['params']['local_key']))
            $this->local_key = $params['params']['local_key'];
        if(isset($params['params']['change_field']))
            $this->change_field = $params['params']['change_field'];
            
        $this->primary_key = (isset($this->params['primary_key']))?
                                   $this->params['primary_key'] :
                                   'id';

        $this->display_field = (isset($this->params['display_field']))?
                                   $this->params['display_field'] : 
                                   show_error('A display field must be defined in your YAML file for the ManyToMany plugin ('.$name.')');
    }
    /**
    * Вернет имя поля, если такое задано.
    * В противном случае - имя таблицы
    * 
    */
    function getDisplayName()
    {
        return !empty($this->display_name) ? $this->display_name : parent::getDisplayName();
    }
    
    function prepereSearch($value = 0)
    {
        if(empty($value))
            return;
        $linking_table = $this->getLinkingTableName();
        $this->CI->db->join($linking_table, $linking_table.'.'.$this->local_table.'_id = '.$this->local_table.'.id', 'left');
        $this->CI->db->join($this->foreign_table, $this->foreign_table.'.id = '.$linking_table.'.'.$this->foreign_table.'_id','left');
        $this->CI->db->where($this->foreign_table.'.'.$this->display_field, $value);
 
    }
    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function prepForDisplay($value){
        $local_key = !empty($this->local_key)?$this->local_key:$this->local_table.'_id';            
    
        $query = $this->CI->codexmodel->get_where( $this->getLinkingTableName(),
                                           array($local_key=>$this->CI->codexadmin->active_id) );
        $list = array();

        foreach($query->result_array() as $link){

            $far_key = !empty($this->far_key)?$this->far_key:$link[$this->foreign_table.'_id'];            
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
        $local_key = !empty($this->local_key)?$this->local_key:$this->local_table.'_id';
        $this->CI->db->delete($this->getLinkingTableName(),array($local_key=>$this->CI->codexadmin->active_id));
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
        $local_key = !empty($this->local_key)?$this->local_key:$this->local_table.'_id';
        $this->CI->db->delete($this->getLinkingTableName(),array($local_key=>$this->CI->input->post($this->CI->codexadmin->primary_key)));
        if($selected_items AND is_array($selected_items))
            foreach($selected_items as $foreign_id){
                $new_linked_items = array();
                $new_linked_items[$local_key] = $this->CI->input->post($this->CI->codexadmin->primary_key); 
                $far_key = !empty($this->far_key)?$this->far_key:$this->foreign_table.'_id';
                $new_linked_items[$far_key] = $foreign_id;
                
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
    function getPreselectedRecords(){

        if( !$this->CI->db->table_exists($this->getLinkingTableName()) )
            return array();
            
        $list = array();
        $result = $this->CI->db->query("DESCRIBE ".$this->getLinkingTableName());
        $fields = $result->result_array();
        $continue = false;
        $local_key = !empty($this->local_key)?$this->local_key:$this->local_table.'_id';
        foreach ($fields as $field){
            if ($field['Field'] == $local_key)
                $continue = true;
        }
        if (!$continue)
            return array();
        $local_key = !empty($this->local_key)?$this->local_key:$this->local_table.'_id';
        $preselected_fields = $this->CI->codexmodel->get_where($this->getLinkingTableName(),array($local_key=>$this->CI->codexadmin->active_id));

        foreach($preselected_fields->result_array() as $link){
            $this->CI->db->select($this->primary_key);
            $this->CI->db->select($this->display_field);
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
}