<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class codexForms
{
	var $html = "";
    var $objects = array();
    var $CI;
    var $prefix="";
    var $suffix="";
    var $validation_errors;
    var $name,$label,$value,$attributes,$params,$element_name; 
	
    function codexForms(){
        $this->CI = &get_instance();    
        if(!isset($this->CI->codexmessages)) $this->CI->load->library('codexmessages');
        $this->CI->lang->load('codexforms');
	}

    /*
     * The function sets up the variables for the specific elements. Isn't called from outside,
     *    rather it is called from every element.
     */
	function initiate($name,$params)
	{
        $this->name =       $name; 
        
        if(!array_key_exists('label',$params)){
                if(function_exists('humanize'))
                    $this->label = humanize($name);
                else
                    $this->label = ucfirst($name);
        }
        else{
            $this->label = $params['label'];
        }
        $this->value =      (isset($params['value']))? $params['value'] : ""; 
        $this->element_name=(isset($params['element_name']))? $params['element_name'] : $this->name; 
        $this->attributes = (isset($params['attributes']))? $params['attributes'] : array(); 
        $this->params =     (isset($params['params']))? $params['params'] :array();
    }
    /*
     * This function is called from outside, contain the configuration of the form. It
     *     creates each element and assigns its prefix, suffix, and wrappers
     */
	function setup($elements,$prefix = "",$suffix = "")
    { 
        if(!is_array($elements))
            show_error($this->CI->lang->line('codexforms_first_param_not_array'));

        $this->prefix = ($prefix == "")? '<div class="control-group">' : $prefix;
        $this->suffix = ($suffix == "")? '</div>' : $suffix;

        foreach($elements as $name => $params){
            if(!class_exists($params['class'])){

                $this->loadPlugin($params['class']);
            }
            $this->objects[$name] = new $params['class']($name,$params);
            $this->objects[$name]->setWrappers($this->prefix,$this->suffix);
        }
	}
    function loadPlugin($file_name){
        if(file_exists(BASEPATH.'plugins'.DIRECTORY_SEPARATOR.strtolower($file_name).'.php'))
            $file = BASEPATH.'plugins'.DIRECTORY_SEPARATOR.strtolower($file_name).'.php';

        else if(file_exists(APPPATH.'plugins'.DIRECTORY_SEPARATOR.strtolower($file_name).'.php'))
            $file = APPPATH.'plugins'.DIRECTORY_SEPARATOR.strtolower($file_name).'.php';
        else
            show_error($this->CI->lang->line('codexforms_could_not_load_plugin'));

        require($file);
    }
    /*
     * This function returns the attributes like  this: name="att" name2="att2"
     */
    function getAttributes($attributes){
        $html = "";

        if($attributes == null)
            $attributes = array();
        foreach($attributes as $att_name => $att_val){
            $html .= " ".$att_name.'="'.$att_val.'"';
        }
        return $html;
    }
    /*
     * Given an array, this function will set the value of every element,
     *     used when we want to repopulate the form from $_POST or something.
     */
    function populateWithArrayInfo($array){
        $this->iterate("setValue",$array);
    }
    /*
     * This is similar to populateWithArrayInfo() but it takes a $table
     *     and an id and then pulls a row from the db.
     */
    function populateWithDbInfo($table,$id){
        $query = $this->CI->db->get_where($table,array($this->CI->codexadmin->primary_key=>$id));
        $result = $query->result_array();
        $this->iterate('setValue',$result[0]);
    }
    /*
     * This sets the provided wrappers. Called automatically from setup() 
     * on every element
     */
    function setWrappers($pre,$suff){
        $this->prefix = $pre;
        $this->suffix = $suff;
    }
    /*
     * Simply sets the value of the element, giving it an initial value
     */
    function setValue($val){
        $this->value = $val;
    }
    function clearSetup(){
        $this->objects = array();
    }
    function clearValues(){
        $this->value = '';
    }
    /*
     * This goes through all the elements and polymorphically calls each
     *    elements getHTML() function.
     */
	function getHTML() {
        $html = "";
        
        foreach($this->objects as $name => $object){
            // если нужно переопределить форму и взять обратботку некоторых полей
            if(!empty($object->params['no_display_in_form'])) continue;
            
			$html .= $object->getHTML();
        }
        return $html;
    }

    function getSetupForTable($table,$name_prefix='',$name_suffix=''){
        $fields = $this->CI->db->field_data($table);
        $form_setup = array();
        foreach($fields as $field){

            $pre = $name_prefix;
            $suff = $name_suffix;

            $field_name = (function_exists('humanize'))? humanize($field->name) : ucfirst(strtolower($field->name));

            if($field->name == $this->CI->codexadmin->primary_key) continue;

            if($field->type == 'blob'){
                $form_setup[$field->name] = array('class' => 'TextArea','label'=>$field_name);
                $form_setup[$field->name]['element_name'] = $pre.$field->name.$suff;
            }elseif($field->type == 'date' OR $field->type == 'datetime'){
                $form_setup[$field->name] = array('class' => 'Date','label'=>$field_name);
                $form_setup[$field->name]['element_name'] = $pre.$field->name.$suff;
            }else{
                $form_setup[$field->name] = array('class' => 'TextBox','label'=>$field_name);
                $form_setup[$field->name]['element_name'] = $pre.$field->name.$suff;
            }

        }
        return $form_setup;
    }
    function getHTMLForTable($table,$name_prefix='',$name_suffix=''){
        $form_setup = $this->getSetupForTable($table,$name_prefix,$name_suffix);
        $this->setup($form_setup);
        return $this->getHTML();
    }
    /*
     * This function gets the messages associated with the form element
     *    it is called from. In html.
     */
    function getMessage($name){
        if(count($this->validation_errors) == 0)
            return FALSE;
        else{
            $messages = stripslashes($this->validation_errors[$name]);
            return $messages;
        }
    }
    function setValidationErrors($error){
        if(!is_array($error))
            $error = array();
        $this->iterate('setErrors',$error);
    }

    function setErrors($error){
        $this->validation_errors[$this->name] = $error;
    }
    function prepForDb($value){
        // Stripslashes 
        if (get_magic_quotes_gpc()) { 
            $value = stripslashes($value); 
        } 
        // Quote if not integer 
        if (!is_numeric($value)) { 
            if(!is_array($value))
                $value = mysql_real_escape_string($value); 
            else $value = null;
        }
        return $value; 
    }

    function prepForDisplay($value){
        return stripslashes(htmlspecialchars($value));
    }

    function getDisplayName(){
        if(isset($this->params['display_name']))
            return $this->params['display_name'];
        return $this->name;
    }

    function getFieldName(){
        return $this->name;
    }

    function prepForDelete($value){
        return;
    }

    function getIterateNames(){
        return NULL;
    }

    function iterate ($func_name,$param_array=array()){ 
        $return_array = array();
        foreach($this->objects as $name=>$obj){
            if(is_array($obj->getIterateNames())){
                foreach($obj->getIterateNames() as $field){
                    $val = (isset($param_array[$field]))? $param_array[$field] : '';
                    $return_array[$field] = $obj->$func_name($val,$field);
                }

            }
            else{
                $value = (isset($param_array[$name]))? $param_array[$name] : '';
                $return_array[$name] = $this->objects[$name]->$func_name($value,$name);
            }
        }
        // var_dump($return_array);
        return $return_array;
    }
    function callFuncOnObject($object_name,$func,$params=''){
        if(array_key_exists($object_name,$this->objects))
            return $this->objects[$object_name]->$func($params);
        else{
            foreach($this->objects as $name=>$obj){
                if(is_array($obj->getIterateNames())){
                    foreach($obj->getIterateNames() as $field){
                        $val = (isset($param_array[$field]))? $param_array[$field] : '';
                        $return_array[$field] = $obj->$func($val,$field);
                    }

                }
            }
        }
    }

    function preInsertHook(){}
    function postInsertHook(){}
    function preEditHook(){}
    function postEditHook(){}
    function multiUploadFile($value){
    }
    
    /**
    * Function returns only realy isset fields.
    * Can be used to separate additional params in $_POST etc.
    * 
    */
    function getRealIssetFields()
    {
        $fields = array();
        foreach($this->objects as $obj)
        {
            $fields[] = $obj->element_name;
        }
        return $fields;
    }
    function prepereSearch($value = 0)
    {
        return false;
    }
}