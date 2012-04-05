<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class codexAdmin {

    var $CI;
    var $db_table;
    var $form_setup;
    var $controller_name;
    var $form_prefix;
    var $form_suffix;
    var $messages;
    var $rules;
    var $active_id;
    var $primary_key;
    var $asset_folder;
    var $display_fields;
    var $state = '';
    var $params = array();

    /*
     * Constructor:
     *      Loads the codexadmin language file.
     */
    function codexAdmin(){
        $this->CI = &get_instance();
        $this->CI->lang->load('codexadmin');
    }

    /*
     * Initializes all the parameters with default values.
     * Throws an error on missing required arguments.
     */
    function _initialize($params){
        
        $this->db_table        = (isset($params['db_table']))? $params['db_table'] : show_error($this->CI->lang->line('codex_table_not_defined'));
        $this->form_setup      = (isset($params['form_setup']))? $params['form_setup'] : show_error($this->CI->lang->line('codex_form_not_defined'));
        $this->controller_name = (isset($params['controller_name']))? $params['controller_name'] : show_error($this->CI->lang->line('codex_controller_not_defined'));
        $this->form_prefix     = (isset($params['form_prefix']))? $params['form_prefix'] : '<div class="form-element">';
        $this->form_suffix     = (isset($params['form_suffix']))? $params['form_suffix'] : '<div class="clear"></div></div>';
        $this->rules           = (isset($params['rules']))? $params['rules'] : array();
        $this->primary_key     = (isset($params['primary_key']))? $params['primary_key'] : 'id';

        $this->CI->codexforms->setup($this->form_setup,$this->form_prefix,$this->form_suffix);

        if(isset($params['display_fields'])){
            if(!is_array($params['display_fields']))
                $params['display_fields'] = explode(',',$params['display_fields']);
                
            foreach($params['display_fields'] as $plugin_name){
                $this->display_fields[$plugin_name] = $this->CI->codexforms->callFuncOnObject($plugin_name,'getDisplayName');
            }
        }
        else{
            $this->display_fields = $this->CI->codexforms->iterate('getDisplayName');
        }

        $defaults = array(
        	'add_success'           => $this->CI->lang->line('codexadmin_add_success'),
        	'add_failure'           => $this->CI->lang->line('codexadmin_add_failure'),
        	'edit_success'          => $this->CI->lang->line('codexadmin_edit_success'),
        	'edit_no_change'        => $this->CI->lang->line('codexadmin_edit_no_change'),
        	'edit_failure'          => $this->CI->lang->line('codexadmin_edit_failure'),
        	'delete_success'        => $this->CI->lang->line('codexadmin_delete_success'),
        	'delete_confirm'        => $this->CI->lang->line('codexadmin_delete_confirm'),
        	'delete_confirm_link'   => $this->CI->lang->line('codexadmin_delete_confirm_link'),
        	'delete_failure'        => $this->CI->lang->line('codexadmin_delete_failure'),
        	'upload_success'        => $this->CI->lang->line('codexadmin_upload_success'),
        	'upload_failure'        => $this->CI->lang->line('codexadmin_upload_failure'),
        	'upload_remove_success' => $this->CI->lang->line('codexadmin_upload_remove_success'),
        	'upload_remove_failure' => $this->CI->lang->line('codexadmin_upload_remove_failure'),
        );
		
        if(array_key_exists('messages',$params))
            $messages = $params['messages'];
        else
            $messages = array();

        $this->messages['add_success'] =             (isset($messages['add_success']   ))?        $messages['add_success']   :        $defaults['add_success'];
        $this->messages['add_failure'] =             (isset($messages['add_failure']   ))?        $messages['add_failure']   :        $defaults['add_failure'];
        $this->messages['edit_success'] =            (isset($messages['edit_success']  ))?        $messages['edit_success']  :        $defaults['edit_success'];
        $this->messages['edit_no_change'] =          (isset($messages['edit_no_change']))?        $messages['edit_no_change']:        $defaults['edit_no_change'];
        $this->messages['edit_failure'] =            (isset($messages['edit_failure']  ))?        $messages['edit_failure']  :        $defaults['edit_failure'];
        $this->messages['delete_success'] =          (isset($messages['delete_success']))?        $messages['delete_success']:        $defaults['delete_success'];
        $this->messages['delete_confirm'] =          (isset($messages['delete_confirm']))?        $messages['delete_confirm']:        $defaults['delete_confirm'];
        $this->messages['delete_confirm_link'] =     (isset($messages['delete_confirm_link']))?   $messages['delete_confirm_link']:   $defaults['delete_confirm_link'];
        $this->messages['delete_failure'] =          (isset($messages['delete_failure']))?        $messages['delete_failure']:        $defaults['delete_failure'];
        $this->messages['upload_success'] =          (isset($messages['upload_success']))?        $messages['upload_success']:        $defaults['upload_success'];
        $this->messages['upload_failure'] =          (isset($messages['upload_failure']))?        $messages['upload_failure']:        $defaults['upload_failure'];
        $this->messages['upload_remove_success'] =   (isset($messages['upload_remove_success']))? $messages['upload_remove_success']: $defaults['upload_remove_success'];
        $this->messages['upload_remove_failure'] =   (isset($messages['upload_remove_failure']))? $messages['upload_remove_failure']: $defaults['upload_remove_failure'];

        $this->asset_folder = $this->CI->config->item('codex_asset_folder');
        $this->params = $params;
    }

    /*
     * This matches the form_id flashdata item to a specified identifier
     */
    function matchFormAgainstId($id){
        return ($this->CI->codexsession->flashdata('form_id') == $id);
    }
    /*
     * This method is the same as insertNewItem but is for edits. Also, it has the ability
     * to pull stuff from the database and fill the form with it.
     */
    function set_rules($rules){
        $this->rules = $rules;
    }
    function get_rules(){
        return $this->rules;
    }
    function get_message($key){
        return $this->messages[$key];
    }
    function getDefinitionFileName($file_name){ 
 		        return APPPATH.$this->CI->config->item('codex_definitions_dir').$file_name.'.yml'; 
 	} 
}
?>
