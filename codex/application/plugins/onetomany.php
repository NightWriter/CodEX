<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include_once('relationalcontainer.php');
class OneToMany extends RelationalContainer
{
    var $primary_key = 'id';
    var $display_field = ''; //The name of the field to show in the <select>
    var $local_table = '';
    var $foreign_table = '';

    function OneToMany($name,$params) { 
        RelationalContainer::RelationalContainer($name,$params);

        $this->local_table = $this->CI->codexadmin->db_table;
        $this->foreign_table = $name;

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
        $query = $this->CI->codexmodel->get_where($this->foreign_table,array($this->local_table.'_id'=>$this->CI->codexadmin->active_id));
        $list = array();
        foreach($query->result() as $link){
            $list[] = $link->{$this->display_field};
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
        $this->CI->db->update($this->foreign_table,array($this->local_table.'_id'=>-1),array($this->local_table.'_id'=>$this->CI->input->post($this->CI->codexadmin->primary_key)));
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
                $row[$this->local_table.'_id'] = $this->CI->codexadmin->active_id;
                $this->CI->db->insert($this->foreign_table,$row);
                if($this->CI->db->affected_rows() == 0)
                    show_error('There was an error inserting new item into foreign table.');
                else
                    $selected_items[] = $this->CI->db->insert_id();
            }
        }

        if($selected_items AND is_array($selected_items))
            foreach($selected_items as $foreign_id){
                $this->CI->db->update($this->foreign_table,array($this->local_table.'_id'=>$this->CI->codexadmin->active_id),
                                                           array('id'=>$foreign_id));
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
        return $this->foreign_table;
    }


    /*
     * REQUIRES: 
     * MODIFIES: 
     * EFFECTS:  
     */
    function getPreselectedRecords(){
        $list = array();
        $preselected_fields = $this->CI->codexmodel->get_where($this->getLinkingTableName(),array($this->local_table.'_id'=>$this->CI->codexadmin->active_id));
        $this->preselected_rows = $preselected_fields->result_array();
    }
}

?>
