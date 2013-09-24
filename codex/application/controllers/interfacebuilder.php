<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/*
 *
 * NOTE:
 *   This controller does not generate tables, you must predefine you table.
 *
 */
include("codexcontroller.php");

class InterfaceBuilder extends codexController 
{ 
    function InterfaceBuilder () 
    { 
        // Load the CodexController
		codexController::codexController();

        // We include the spyc library
        // So that we can define our form
        // in a YAML file as opposed to 
        // hand-writing the array in $config
        $this->load->library('spyc');                

        // To make the field names more human
        // friendly
        $this->load->helper('inflector');


        /*
         * ===================================
         * ADDING VALIDATION
         * ===================================
         *
         * If you would like to add form
         * validation, then define your 
         * rules in the following format:
         *
         *      $rules['field_name'] = "rules";
         *
         * then in your $config array below:
         *
         *      'rules' => $rules,
         *
         * ===================================
         */

        $rules['textbox_test'] = "trim|required";
        $config = array(
                    'db_table' => 'example', //The name of the table associated with this controller
                    'form_setup' => $this->spyc->YAMLLOAD('definitions/example_form.yml'), //The array that holds our elements
                    'controller_name' => 'Example', //The name of the controller, so that it can be used when generating the URLs
                    'primary_key' => 'my_id', //The name of the controller, so that it can be used when generating the URLs
                    'display_fields'=>array('textbox_test','related_example','dbdropdown_test','date_test','sessiondata_test'),
                    'rules'=>$rules
                    );
        $this->setConfig($config);
    } 
}
?>
