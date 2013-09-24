<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/*
 *
 * NOTE:
 *   This controller does not generate tables, you must predefine you table.
 *
 */
include("codexcontroller.php");

class Example extends codexController 
{ 
    function Example () 
    { 
        // Load the CodexController
		codexController::codexController();

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
        $rules['textbox_test'] = "trim|required|callback_username_check";

        $config = array(
                    'db_table' => 'example', //The name of the table associated with this controller
                    'form_setup' => $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName('example_form')), //The array that holds our elements
                    'controller_name' => 'Example', //The name of the controller, so that it can be used when generating the URLs
                    'primary_key' => 'my_id', //The name of the controller, so that it can be used when generating the URLs
                    'display_fields'=>array('textbox_test','date_test','related_example'),
                    'rules'=>$rules
                    );
        $this->setConfig($config);
    } 
    function username_check($str)
    {
        if ($str == 'test')
        {
            $this->codexvalidation->set_message('username_check', 'The %s field can not be the word "test"');
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
}
?>
