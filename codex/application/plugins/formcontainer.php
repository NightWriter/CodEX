<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class FormContainer extends CodexForms
{
    var $form;
    var $objects;
    var $fields;
    /*
     * Ok so I want to make names like textbox,checkbox, and date into
     * fieldset_name[textbox], fieldset_name[checkbox] and fieldset_name[date]
     * so that they can be passed into this plugin as an array...
     */
    function FormContainer($name,$params) { 
        codexForms::initiate($name,$params);
        $this->form = new codexforms();
        $this->fields = array_keys($this->params['form']);
        $this->form->setup($this->params['form']);
    }
    function setErrors($error,$field){
        if(!empty($error))
            $this->form->callFuncOnObject($field,'setErrors',$error);
    }
    function setValue($value,$field){ 
        $this->form->callFuncOnObject($field,'setValue',$value);  
    }
    function getFieldName(){ 
        return $this->fields; 
    }
    function getIterateNames(){ 
        return $this->fields; 
    }
    function getDisplayName($value,$field){ 
        return $this->form->callFuncOnObject($field,'getDisplayName',$value); 
    }
    function prepForDisplay($value,$field){ 
        return $this->form->callFuncOnObject($field,'prepForDisplay',$value); 
    }

    function prepForDb($data,$field){
        return $this->form->callFuncOnObject($field,'prepForDb',$data);  
    }
}

?>
